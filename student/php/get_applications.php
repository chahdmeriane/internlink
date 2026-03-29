<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
        a.id, a.status, a.cover_letter, a.match_percent,
        a.applied_at,
        io.title,
        io.location,
        io.duration,
        io.field      AS domain,
        io.skills     AS required_skills,
        cp.company_name,
        cp.country,
        cp.sector
    FROM applications a
    JOIN internship_offers io ON io.id = a.offer_id
    JOIN company_profiles  cp ON cp.user_id = io.company_id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$userId]);
$apps = $stmt->fetchAll();

$nameStmt = $pdo->prepare("SELECT first_name FROM users WHERE id = ?");
$nameStmt->execute([$userId]);
$name = $nameStmt->fetchColumn();

echo json_encode([
    'success'      => true,
    'applications' => $apps,
    'student_name' => $name,
]);
