<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth_student.php'; // FIX: use student-specific auth guard

$userId = $_SESSION['user_id'];

if (!isset($_FILES['cv'])) {
    echo json_encode(['success' => false, 'message' => 'No file received.']);
    exit;
}

$file = $_FILES['cv'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error.']);
    exit;
}

// FIX: validate MIME type using mime_content_type not just $_FILES['type']
// $_FILES['type'] is sent by the browser and can be faked
$mime = mime_content_type($file['tmp_name']);
if ($mime !== 'application/pdf') {
    echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed.']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File must be under 5MB.']);
    exit;
}

$uploadDir = __DIR__ . '/uploads/cv/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// FIX: use a fixed safe filename per user — prevents path traversal
$filename = 'cv_student_' . $userId . '.pdf';
$dest     = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
    exit;
}

$cvPath = 'uploads/cv/' . $filename;

try {
    $chk = $pdo->prepare("SELECT id FROM student_profiles WHERE user_id = ?");
    $chk->execute([$userId]);
    if ($chk->fetch()) {
        $pdo->prepare("UPDATE student_profiles SET cv_path = ? WHERE user_id = ?")
            ->execute([$cvPath, $userId]);
    } else {
        $pdo->prepare("INSERT INTO student_profiles (user_id, cv_path) VALUES (?, ?)")
            ->execute([$userId, $cvPath]);
    }
} catch (PDOException $e) {
    error_log('upload_cv DB error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'CV uploaded successfully.', 'path' => $cvPath]);
