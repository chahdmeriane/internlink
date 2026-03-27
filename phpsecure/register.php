<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

// Read fields from POST (FormData)
$firstName   = trim($_POST['firstName']   ?? '');
$lastName    = trim($_POST['lastName']    ?? '');
$email       = trim($_POST['email']       ?? '');
$password    = $_POST['password']         ?? '';
$role        = strtolower(trim($_POST['type'] ?? 'student'));
$wilaya      = trim($_POST['wilaya']      ?? '');
$country     = trim($_POST['country']     ?? '');
$companyName = trim($_POST['companyName'] ?? '');
$sector      = trim($_POST['sector']      ?? '');
$university  = trim($_POST['university']  ?? '');
$field       = trim($_POST['field']       ?? '');
$year        = trim($_POST['year']        ?? '');
$skills      = trim($_POST['skills']      ?? '');
$bio         = trim($_POST['bio']         ?? '');

// Validate required fields
if (!$firstName || !$lastName || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}

// Validate role
$allowed_roles = ['student', 'company'];
if (!in_array($role, $allowed_roles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid account type selected.']);
    exit;
}

// Check for duplicate email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
    exit;
}

// Insert user
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
if (!$stmt->execute([$firstName, $lastName, $email, $hashedPassword, $role])) {
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
    exit;
}
$newUserId = (int) $pdo->lastInsertId();

// Create profile row
if ($role === 'student') {
    $pdo->prepare("INSERT IGNORE INTO student_profiles (user_id, university, field_of_study, year, city, skills, bio) VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([$newUserId, $university, $field, $year, $wilaya, $skills, $bio]);
} elseif ($role === 'company') {
    $pdo->prepare("INSERT IGNORE INTO company_profiles (user_id, company_name, sector, country) VALUES (?, ?, ?, ?)")
        ->execute([$newUserId, $companyName, $sector, $country]);
}

// Success
echo json_encode([
    'success'  => true,
    'message'  => 'Account created successfully!',
    'redirect' => 'login.html',
]);
