<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth_guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$appId  = !empty($_POST['id'])     ? (int) $_POST['id'] : 0;
$status = trim($_POST['status']    ?? '');

if (!$appId) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID.']);
    exit;
}

if (!in_array($status, ['waiting', 'accepted', 'rejected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
    exit;
}

try {
    // Verify the application belongs to this company via internship_offers
    $stmt = $pdo->prepare("
        SELECT a.id FROM applications a
        JOIN internship_offers io ON io.id = a.offer_id
        WHERE a.id = ? AND io.company_id = ?
    ");
    $stmt->execute([$appId, $companyUserId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Application not found or access denied.']);
        exit;
    }

    $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?')
        ->execute([$status, $appId]);

    echo json_encode([
        'success' => true,
        'message' => 'Application status updated.',
        'status'  => $status,
    ]);
} catch (PDOException $e) {
    error_log('set_application_status error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
}
