<?php
error_reporting(0);
ini_set('display_errors', 0);

// ── Security headers ──────────────────────────────────────────────────────────
header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// ── Session hardening ─────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
// ini_set('session.cookie_secure', 1); // uncomment when HTTPS is enabled
ini_set('session.cookie_path', '/');
session_save_path(sys_get_temp_dir());
session_name('internlink_session');
session_start();

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// ── Must have a pending login in session ──────────────────────────────────────
if (empty($_SESSION['2fa_pending_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit;
}
$pendingUserId = (int) $_SESSION['2fa_pending_user_id'];

// ── Rate limiting — max 5 wrong OTPs then 10-minute lockout ──────────────────
$attemptsKey  = '2fa_attempts';
$lockoutUntil = '2fa_lockout_until';

if (!empty($_SESSION[$lockoutUntil]) && time() < $_SESSION[$lockoutUntil]) {
    $wait = $_SESSION[$lockoutUntil] - time();
    echo json_encode(['success' => false, 'message' => "Too many attempts. Try again in {$wait} seconds."]);
    exit;
}

// Accept both JSON body and form POST
$raw  = file_get_contents('php://input');
$body = $raw ? (json_decode($raw, true) ?? []) : [];
$otp  = trim($body['otp'] ?? $_POST['otp'] ?? '');

if (strlen($otp) !== 6 || !ctype_digit($otp)) {
    echo json_encode(['success' => false, 'message' => 'Invalid code format.']);
    exit;
}

// ── Check OTP against the specific pending user only ─────────────────────────
try {
    $stmt = $pdo->prepare(
        'SELECT * FROM users WHERE id = ? AND two_fa_code = ? AND two_fa_expires > NOW()'
    );
    $stmt->execute([$pendingUserId, $otp]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('verify_2fa DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

if (!$user) {
    // Wrong or expired OTP — increment failure counter
    $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;
    if ($_SESSION[$attemptsKey] >= 5) {
        $_SESSION[$lockoutUntil] = time() + 600;
        $_SESSION[$attemptsKey]  = 0;
        echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Try again in 600 seconds.']);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Invalid or expired code. Please try again.']);
    exit;
}

// ── Clear OTP from DB so it cannot be reused ──────────────────────────────────
try {
    $pdo->prepare('UPDATE users SET two_fa_code = NULL, two_fa_expires = NULL WHERE id = ?')
        ->execute([$user['id']]);
} catch (PDOException $e) {
    error_log('verify_2fa clear OTP error: ' . $e->getMessage());
}

// ── Clean up 2FA session keys ─────────────────────────────────────────────────
unset($_SESSION['2fa_pending_user_id'], $_SESSION[$attemptsKey], $_SESSION[$lockoutUntil]);

// ── Create full authenticated session ────────────────────────────────────────
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_role']  = $user['role'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ── Regenerate session ID AFTER writing data to prevent fixation ──────────────
session_regenerate_id(true);

// ── Redirect based on role ────────────────────────────────────────────────────
$base = '/internlink';
$redirectMap = [
    'company' => $base . '/company/html/company_dashboard.html',
    'student' => $base . '/student/html/student_dashboard.html',
    'admin'   => $base . '/admin/html/admin_dashboard.html',
];
$redirect = $redirectMap[$user['role']] ?? $base . '/html/index.html';

echo json_encode([
    'success'  => true,
    'message'  => 'Verified successfully!',
    'redirect' => $redirect,
]);
