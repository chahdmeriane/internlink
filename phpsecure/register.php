<?php
session_start();
header('Content-Type: application/json');

require 'db.php';

// قراءة البيانات القادمة من fetch()
$data = json_decode(file_get_contents("php://input"), true);

// المتغيرات
$firstName = trim($data['firstName'] ?? '');
$lastName  = trim($data['lastName'] ?? '');
$email     = trim($data['email'] ?? '');
$password  = $data['password'] ?? '';
$role      = $data['type'] ?? 'student';

// تحقق من الحقول
if (!$firstName || !$lastName || !$email || !$password) {
    echo json_encode([
        "success" => false,
        "message" => "Please fill in all required fields."
    ]);
    exit;
}

// تحقق من صحة البريد
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email address."
    ]);
    exit;
}

// تحقق من طول كلمة السر (حماية إضافية)
if (strlen($password) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters."
    ]);
    exit;
}

// تحقق إذا البريد موجود مسبقاً
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    echo json_encode([
        "success" => false,
        "message" => "This email is already registered."
    ]);
    exit;
}

// تشفير كلمة السر
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// إدخال المستخدم في قاعدة البيانات
$stmt = $pdo->prepare("
    INSERT INTO users (first_name, last_name, email, password, role)
    VALUES (?, ?, ?, ?, ?)
");

$success = $stmt->execute([
    $firstName,
    $lastName,
    $email,
    $hashedPassword,
    $role
]);

// النتيجة
if ($success) {
    echo json_encode([
        "success" => true,
        "message" => "Account created successfully!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Something went wrong. Try again."
    ]);
}
?>