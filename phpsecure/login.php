<?php
session_start();
header('Content-Type: application/json');

require 'db.php';

// Read POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';
$role = isset($data['role']) ? $data['role'] : 'student';

// Basic validation
if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Fetch user from DB securely
$stmt = $pdo->prepare("SELECT id, email, password, role FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== $role) {
    echo json_encode(['success' => false, 'message' => 'Incorrect email, password, or role.']);
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect email, password, or role.']);
    exit;
}

// Login successful: set session
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Login successful.',
    'role' => $user['role'],
    'redirect' => 'index.html'
]);
