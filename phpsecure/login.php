<?php
session_start();
header('Content-Type: application/json');

require 'db.php';

// ── Read JSON body ──────────────────────────────────────────────────────────
$data     = json_decode(file_get_contents('php://input'), true);
$email    = isset($data['email'])    ? trim($data['email'])    : '';
$password = isset($data['password']) ? $data['password']       : '';
$role     = isset($data['role'])     ? strtolower(trim($data['role'])) : 'student';

// ── Basic validation ────────────────────────────────────────────────────────
if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}
$allowed_roles = ['student', 'company', 'admin'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role selected.']);
    exit;
}

// ── Fetch user ──────────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id, email, password, role, first_name FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Unified error message – never reveal which field is wrong
$authError = ['success' => false, 'message' => 'Incorrect email, password, or role.'];

if (!$user || $user['role'] !== $role) {
    echo json_encode($authError);
    exit;
}
if (!password_verify($password, $user['password'])) {
    echo json_encode($authError);
    exit;
}

// ── Generate 6-digit OTP and store in DB ────────────────────────────────────
$otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes

$stmt = $pdo->prepare("UPDATE users SET two_fa_code = ?, two_fa_expires = ? WHERE id = ?");
$stmt->execute([$otp, $expires, $user['id']]);

// ── Store partial auth in session (not fully logged in yet) ─────────────────
session_regenerate_id(true);
$_SESSION['2fa_user_id'] = $user['id'];
$_SESSION['2fa_role']    = $user['role'];

// ── Send OTP via email ──────────────────────────────────────────────────────
$to      = $user['email'];
$subject = 'internLink – Your verification code';
$name    = htmlspecialchars($user['first_name'] ?? 'User');
$message = "Hello $name,\n\n"
         . "Your internLink verification code is: $otp\n\n"
         . "This code expires in 10 minutes.\n"
         . "If you did not request this, ignore this email.\n\n"
         . "— The internLink Team";
$headers = "From: noreply@internlink.com\r\nX-Mailer: PHP/" . phpversion();

// mail() is for production. For local dev, log to file instead.
if (!mail($to, $subject, $message, $headers)) {
    // Fallback: log OTP to file (dev only – REMOVE in production)
    file_put_contents('otp_dev.log', date('Y-m-d H:i:s') . " | $email | OTP: $otp\n", FILE_APPEND);
}

echo json_encode([
    'success'  => true,
    'requires_2fa' => true,
    'message'  => '2FA code sent to your email.',
]);
