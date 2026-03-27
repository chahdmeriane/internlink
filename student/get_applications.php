<?php
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'auth.php';

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
        a.id, a.status, a.cover_letter, a.match_percent,
        a.applied_at, a.viewed_at, a.decided_at, a.feedback,
        i.title, i.duration_months, i.required_skills,
        u.first_name AS company_name,
        cp.wilaya
    FROM applications a
    JOIN internships i ON i.id = a.internship_id
    JOIN users u ON u.id = i.company_id
    LEFT JOIN company_profiles cp ON cp.user_id = i.company_id
    WHERE a.student_id = ?
    ORDER BY a.applied_at DESC
");
$stmt->execute([$userId]);
$apps = $stmt->fetchAll();

// Fetch student name
$nameStmt = $pdo->prepare("SELECT first_name FROM users WHERE id=?");
$nameStmt->execute([$userId]);
$name = $nameStmt->fetchColumn();

echo json_encode([
    'success'      => true,
    'applications' => $apps,
    'student_name' => $name,
]);
