<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth_student.php'; // FIX: use student-specific auth guard

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT u.id, u.first_name, u.last_name, u.email, u.role,
               sp.university, sp.field_of_study,
               sp.year        AS academic_year,
               sp.city        AS wilaya,
               sp.country, sp.bio, sp.skills,
               NULL           AS phone,
               NULL           AS linkedin,
               NULL           AS github,
               NULL           AS cv_path
        FROM users u
        LEFT JOIN student_profiles sp ON sp.user_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();
} catch (PDOException $e) {
    error_log('get_profile fetch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

if (!$profile) {
    echo json_encode(['success' => false, 'message' => 'Profile not found.']);
    exit;
}

try {
    $stmt2 = $pdo->prepare("
        SELECT
            COUNT(*)                  AS applications,
            SUM(status = 'accepted')  AS accepted,
            AVG(match_percent)        AS avg_match
        FROM applications
        WHERE student_id = ?
    ");
    $stmt2->execute([$userId]);
    $stats = $stmt2->fetch();
} catch (PDOException $e) {
    error_log('get_profile stats error: ' . $e->getMessage());
    $stats = [];
}

// Build CV info if a CV path exists
$cv = null;
if (!empty($profile['cv_path'])) {
    $filename = basename($profile['cv_path']);
    $fullPath = __DIR__ . '/uploads/cv/' . $filename;
    $uploadedAt = file_exists($fullPath)
        ? date('d M Y', filemtime($fullPath))
        : 'Unknown date';
    $cv = [
        'filename'    => $filename,
        'path'        => $profile['cv_path'],
        'uploaded_at' => $uploadedAt,
    ];
}

echo json_encode([
    'success' => true,
    'profile' => $profile,
    'stats'   => [
        'applications' => (int)($stats['applications'] ?? 0),
        'accepted'     => (int)($stats['accepted']     ?? 0),
        'avg_match'    => $stats['avg_match'] ? (int) round($stats['avg_match']) : null,
    ],
    'cv' => $cv,
]);
