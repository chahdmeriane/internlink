<?php
error_reporting(0);
ini_set('display_errors', 0);

// ── Security headers ──────────────────────────────────────────────────────────
header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ── Session hardening ─────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
// ini_set('session.cookie_secure', 1); // uncomment when HTTPS is enabled
ini_set('session.cookie_path', '/');
session_save_path(sys_get_temp_dir());
session_name('internlink_session');
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/send_otp.php'; // handles OTP generation + emailing

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email    = strtolower(trim($_POST['email']    ?? ''));
$password = $_POST['password'] ?? '';
$role     = trim($_POST['role'] ?? '');

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit;
}

// ── Brute-force protection ────────────────────────────────────────────────────
$ip           = $_SERVER['REMOTE_ADDR'];
$lockoutKey   = 'login_attempts_'     . md5($ip);
$lockoutUntil = 'login_lockout_until_' . md5($ip);

if (!empty($_SESSION[$lockoutUntil]) && time() < $_SESSION[$lockoutUntil]) {
    $wait = $_SESSION[$lockoutUntil] - time();
    echo json_encode(['success' => false, 'message' => "Too many failed attempts. Try again in {$wait} seconds."]);
    exit;
}

// ── Fetch user ────────────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Login DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION[$lockoutKey] = ($_SESSION[$lockoutKey] ?? 0) + 1;
    if ($_SESSION[$lockoutKey] >= 5) {
        $_SESSION[$lockoutUntil] = time() + 300;
        $_SESSION[$lockoutKey]   = 0;
        echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Try again in 300 seconds.']);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Incorrect email or password.']);
    exit;
}

// Reset lockout counter on success
unset($_SESSION[$lockoutKey], $_SESSION[$lockoutUntil]);

// ── Check if banned ───────────────────────────────────────────────────────────
if (!empty($user['is_banned'])) {
    echo json_encode(['success' => false, 'message' => 'Your account has been suspended. Please contact support.']);
    exit;
}

// ── Role check ────────────────────────────────────────────────────────────────
if ($user['role'] !== 'admin' && !empty($role) && $user['role'] !== $role) {
    echo json_encode(['success' => false, 'message' => 'Incorrect email or password.']);
    exit;
}

// ── 2FA — generate OTP, save to DB, send email ───────────────────────────────
// Store the pending user ID in session BEFORE sending email
// so verify_2fa.php knows which user to check
$_SESSION['2fa_pending_user_id'] = $user['id'];

$sent = sendOtp($pdo, $user);

if (!$sent) {
    // Email failed — clear pending session and tell the user
    unset($_SESSION['2fa_pending_user_id']);
    echo json_encode([
        'success' => false,
        'message' => 'Could not send verification email. Please check your email address or try again later.',
    ]);
    exit;
}

// Email sent successfully — tell the frontend to show the OTP screen
echo json_encode([
    'success'      => true,
    'requires_2fa' => true,
    'message'      => 'A 6-digit code has been sent to your email.',
]);
