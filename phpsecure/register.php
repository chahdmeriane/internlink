<?php
error_reporting(0);
ini_set('display_errors', 0);

// ── Security headers ──────────────────────────────────────────────────────────
header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// ── Session ───────────────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_path', '/');
session_save_path(sys_get_temp_dir());
session_name('internlink_session');
session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/send_otp.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// FIX: sanitize all text inputs
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

$type      = trim($_POST['type']       ?? '');
$firstName = clean($_POST['firstName'] ?? '');
$lastName  = clean($_POST['lastName']  ?? '');
$email     = strtolower(trim($_POST['email'] ?? ''));
$password  = $_POST['password'] ?? '';

if (!$type || !$firstName || !$lastName || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!in_array($type, ['student', 'company'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid account type.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}
if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one uppercase letter.']);
    exit;
}
if (!preg_match('/[0-9]/', $password)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one number.']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        exit;
    }
} catch (PDOException $e) {
    error_log('Register check error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$firstName, $lastName, $email, $hashedPassword, $type]);
    $userId = (int) $pdo->lastInsertId();

    if ($type === 'student') {
        $stmt = $pdo->prepare('INSERT INTO student_profiles (user_id, university, field_of_study, year, city, country, skills, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            clean($_POST['university'] ?? ''),
            clean($_POST['field']      ?? ''),
            clean($_POST['year']       ?? ''),
            clean($_POST['wilaya']     ?? ''),
            clean($_POST['country']    ?? ''),
            clean($_POST['skills']     ?? ''),
            clean($_POST['bio']        ?? ''),
        ]);
    } else {
        $companyName = clean($_POST['companyName'] ?? '');
        if (!$companyName) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Company name is required.']);
            exit;
        }
        $stmt = $pdo->prepare('INSERT INTO company_profiles (user_id, company_name, sector, country) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $userId,
            $companyName,
            clean($_POST['sector']  ?? ''),
            clean($_POST['country'] ?? ''),
        ]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Register transaction error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    exit;
}

// ── Send email verification OTP ───────────────────────────────────────────────
// Account is created — now send OTP to verify their email before letting them in
$user = ['id' => $userId, 'email' => $email, 'first_name' => $firstName];

$_SESSION['2fa_pending_user_id'] = $userId;
$_SESSION['2fa_context']         = 'registration'; // tells verify_2fa.php this is a new registration

$sent = sendOtp($pdo, $user);

if (!$sent) {
    // Account created but email failed — let them in anyway, just skip OTP
    unset($_SESSION['2fa_pending_user_id'], $_SESSION['2fa_context']);
    session_regenerate_id(true);
    $_SESSION['user_id']    = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_name']  = $firstName . ' ' . $lastName;
    $_SESSION['user_role']  = $type;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    $base = '/internlink';
    $redirect = $type === 'student'
        ? $base . '/student/html/student_dashboard.html'
        : $base . '/company/html/company_dashboard.html';

    echo json_encode([
        'success'      => true,
        'requires_otp' => false,
        'redirect'     => $redirect,
        'message'      => 'Account created successfully!',
    ]);
    exit;
}

// OTP sent — ask frontend to show verification screen
echo json_encode([
    'success'      => true,
    'requires_otp' => true,
    'message'      => 'Account created! Please check your email for a verification code.',
]);
