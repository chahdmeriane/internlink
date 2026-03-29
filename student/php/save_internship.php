<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$userId = $_SESSION['user_id'];
$data   = json_decode(file_get_contents('php://input'), true) ?? [];

$offerId = (int)($data['internship_id'] ?? $data['offer_id'] ?? $_POST['internship_id'] ?? 0);
$action  = $data['action'] ?? $_POST['action'] ?? 'save';

if (!$offerId) {
    echo json_encode(['success' => false, 'message' => 'Invalid offer.']);
    exit;
}

if ($action === 'save') {
    $pdo->prepare("INSERT IGNORE INTO saved_offers (student_id, offer_id, saved_at) VALUES (?, ?, NOW())")
        ->execute([$userId, $offerId]);
} else {
    $pdo->prepare("DELETE FROM saved_offers WHERE student_id = ? AND offer_id = ?")
        ->execute([$userId, $offerId]);
}

echo json_encode(['success' => true]);
