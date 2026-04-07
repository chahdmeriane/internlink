<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth.php'; // admin only

$data   = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$id     = (int)($data['id'] ?? 0);

if (!$id || !in_array($action, ['delete', 'activate', 'deactivate'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

try {
    // FIX: was querying wrong table "internships" — correct table is "internship_offers"
    $stmt = $pdo->prepare("SELECT id FROM internship_offers WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Internship not found.']);
        exit;
    }

    switch ($action) {
        case 'delete':
            $pdo->prepare("DELETE FROM internship_offers WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Internship deleted.']);
            break;

        case 'activate':
            $pdo->prepare("UPDATE internship_offers SET status = 'active' WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Internship activated.']);
            break;

        case 'deactivate':
            $pdo->prepare("UPDATE internship_offers SET status = 'closed' WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Internship deactivated.']);
            break;
    }
} catch (PDOException $e) {
    error_log('manage_internship error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
}
