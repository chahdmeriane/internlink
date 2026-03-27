<?php
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'auth.php'; // redirects to login if not authenticated

$userId = $_SESSION['user_id'];

// ── Fetch student profile ───────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT u.id, u.first_name, u.last_name, u.email, u.role,
           sp.phone, sp.university, sp.field_of_study, sp.academic_year,
           sp.wilaya, sp.bio, sp.skills, sp.linkedin, sp.github, sp.cv_path
    FROM users u
    LEFT JOIN student_profiles sp ON sp.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->execute([$userId]);
$profile = $stmt->fetch();

if (!$profile) {
    echo json_encode(['success' => false, 'message' => 'Profile not found.']);
    exit;
}

// ── Application stats ───────────────────────────────────────────────────────
$stmt2 = $pdo->prepare("
    SELECT
        COUNT(*) AS applications,
        SUM(status = 'accepted') AS accepted,
        AVG(match_percent) AS avg_match
    FROM applications
    WHERE student_id = ?
");
$stmt2->execute([$userId]);
$stats = $stmt2->fetch();

// ── Current CV ──────────────────────────────────────────────────────────────
$cv = null;
if ($profile['cv_path']) {
    $cv = [
        'filename'    => basename($profile['cv_path']),
        'uploaded_at' => date('d/m/Y', filemtime(__DIR__ . '/uploads/cv/' . basename($profile['cv_path'])) ?: time()),
    ];
}

// Add cv_path to profile so completion can check it
$profile['cv_path'] = $profile['cv_path'] ?? null;

echo json_encode([
    'success' => true,
    'profile' => $profile,
    'stats'   => [
        'applications' => (int)($stats['applications'] ?? 0),
        'accepted'     => (int)($stats['accepted'] ?? 0),
        'avg_match'    => $stats['avg_match'] ? round($stats['avg_match']) : null,
    ],
    'cv' => $cv,
]);
