<?php
// ── get_saved.php ────────────────────────────────────────────────────────────
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'auth.php';

$userId = $_SESSION['user_id'];
$stmt   = $pdo->prepare("SELECT internship_id FROM saved_internships WHERE student_id=?");
$stmt->execute([$userId]);
$ids    = array_column($stmt->fetchAll(), 'internship_id');

echo json_encode(['success' => true, 'ids' => $ids]);
