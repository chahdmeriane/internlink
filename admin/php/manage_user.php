<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth.php'; // admin only

$data   = json_decode(file_get_contents('php://input'), true);
$action = $data['action']  ?? '';
$userId = (int)($data['user_id'] ?? 0);

if (!$userId || !in_array($action, ['delete', 'ban', 'unban'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

// Prevent admin from acting on themselves
if ($userId === (int) $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot modify your own account.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $target = $stmt->fetch();
} catch (PDOException $e) {
    error_log('manage_user fetch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

if (!$target) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

if ($target['role'] === 'admin' && $action === 'delete') {
    echo json_encode(['success' => false, 'message' => 'Cannot delete another admin.']);
    exit;
}

try {
    switch ($action) {
        case 'delete':
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'User deleted.']);
            break;

        // FIX: use a proper is_banned column instead of mangling the email
        // Run this SQL once on your DB: ALTER TABLE users ADD COLUMN is_banned TINYINT(1) NOT NULL DEFAULT 0;
        case 'ban':
            $pdo->prepare("UPDATE users SET is_banned = 1 WHERE id = ?")->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'User banned.']);
            break;

        case 'unban':
            $pdo->prepare("UPDATE users SET is_banned = 0 WHERE id = ?")->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'User unbanned.']);
            break;
    }
} catch (PDOException $e) {
    error_log('manage_user action error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
}
