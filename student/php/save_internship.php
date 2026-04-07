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

$offerId = (int)($data['internship_id'] ?? $data['offer_id'] ?? $_POST['internship_id'] ?? 0);
$action  = $data['action'] ?? $_POST['action'] ?? 'save';

if (!$offerId) {
    echo json_encode(['success' => false, 'message' => 'Invalid offer.']);
    exit;
}

// FIX: validate action value
if (!in_array($action, ['save', 'unsave'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

try {
    if ($action === 'save') {
        $pdo->prepare("INSERT IGNORE INTO saved_offers (student_id, offer_id, saved_at) VALUES (?, ?, NOW())")
            ->execute([$userId, $offerId]);
    } else {
        $pdo->prepare("DELETE FROM saved_offers WHERE student_id = ? AND offer_id = ?")
            ->execute([$userId, $offerId]);
    }
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log('save_internship error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
}
