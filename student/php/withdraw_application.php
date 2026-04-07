<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth_student.php'; // FIX: use student-specific auth guard

$userId = $_SESSION['user_id'];
$data   = json_decode(file_get_contents('php://input'), true) ?? [];
$appId  = (int)($data['application_id'] ?? $_POST['application_id'] ?? 0);

if (!$appId) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID.']);
    exit;
}

try {
    // FIX: verify ownership before deleting — student can only withdraw their own application
    $stmt = $pdo->prepare("
        SELECT id FROM applications
        WHERE id = ? AND student_id = ? AND status IN ('waiting', 'pending')
    ");
    $stmt->execute([$appId, $userId]);

    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Application not found or cannot be withdrawn.']);
        exit;
    }

    $pdo->prepare("DELETE FROM applications WHERE id = ? AND student_id = ?")
        ->execute([$appId, $userId]);

    echo json_encode(['success' => true, 'message' => 'Application withdrawn.']);
} catch (PDOException $e) {
    error_log('withdraw_application error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
}
