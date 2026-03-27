<?php
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'auth.php';

$userId = $_SESSION['user_id'];
$data   = json_decode(file_get_contents('php://input'), true);
$iid    = (int)($data['internship_id'] ?? 0);
$action = $data['action'] ?? 'save';

if (!$iid) { echo json_encode(['success'=>false,'message'=>'Invalid internship.']); exit; }

if ($action === 'save') {
    // Ignore duplicate
    $pdo->prepare("INSERT IGNORE INTO saved_internships (student_id, internship_id, saved_at) VALUES (?,?,NOW())")->execute([$userId, $iid]);
} else {
    $pdo->prepare("DELETE FROM saved_internships WHERE student_id=? AND internship_id=?")->execute([$userId, $iid]);
}

echo json_encode(['success' => true]);
