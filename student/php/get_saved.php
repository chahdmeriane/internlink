<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth_student.php'; // FIX: use student-specific auth guard

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT offer_id FROM saved_offers WHERE student_id = ?");
    $stmt->execute([$userId]);
    $ids = array_column($stmt->fetchAll(), 'offer_id');
} catch (PDOException $e) {
    error_log('get_saved error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

echo json_encode(['success' => true, 'ids' => $ids]);
