<?php
// ── withdraw_application.php ────────────────────────────────────────────────
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'auth.php';

$userId = $_SESSION['user_id'];
$data   = json_decode(file_get_contents('php://input'), true);
$appId  = (int)($data['application_id'] ?? 0);

// Verify this application belongs to this student and is still pending
$stmt = $pdo->prepare("SELECT id FROM applications WHERE id=? AND student_id=? AND status='pending'");
$stmt->execute([$appId, $userId]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Application not found or cannot be withdrawn.']);
    exit;
}

$pdo->prepare("DELETE FROM applications WHERE id=? AND student_id=?")->execute([$appId, $userId]);
echo json_encode(['success' => true, 'message' => 'Application withdrawn.']);
