<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth.php';

$counts = [];
$queries = [
    'total_users'        => "SELECT COUNT(*) FROM users",
    'total_students'     => "SELECT COUNT(*) FROM users WHERE role='student'",
    'total_companies'    => "SELECT COUNT(*) FROM users WHERE role='company'",
    'verified_companies' => "SELECT COUNT(*) FROM company_profiles WHERE is_verified=1",
    'pending_companies'  => "SELECT COUNT(*) FROM company_profiles WHERE COALESCE(is_verified,0)=0",
    'total_internships'  => "SELECT COUNT(*) FROM internship_offers",
    'active_internships' => "SELECT COUNT(*) FROM internship_offers WHERE status='active'",
    'total_applications' => "SELECT COUNT(*) FROM applications",
    'pending_apps'       => "SELECT COUNT(*) FROM applications WHERE status='waiting'",
    'accepted_apps'      => "SELECT COUNT(*) FROM applications WHERE status='accepted'",
    'rejected_apps'      => "SELECT COUNT(*) FROM applications WHERE status='rejected'",
];

foreach ($queries as $key => $sql) {
    try { $counts[$key] = (int) $pdo->query($sql)->fetchColumn(); }
    catch (PDOException $e) { $counts[$key] = 0; }
}

// Recent users
$recent_users = $pdo->query("
    SELECT id, first_name, last_name, email, role, created_at
    FROM users ORDER BY created_at DESC LIMIT 7
")->fetchAll();

// Recent applications — using internship_offers + offer_id
try {
    $recent_apps = $pdo->query("
        SELECT a.id, a.status, a.applied_at,
               CONCAT(u.first_name,' ',u.last_name) AS student_name,
               io.title AS internship_title,
               cp.company_name
        FROM applications a
        JOIN users u              ON u.id  = a.student_id
        JOIN internship_offers io ON io.id = a.offer_id
        JOIN company_profiles cp  ON cp.user_id = io.company_id
        ORDER BY a.applied_at DESC LIMIT 7
    ")->fetchAll();
} catch (PDOException $e) {
    $recent_apps = [];
}

echo json_encode([
    'success'      => true,
    'counts'       => $counts,
    'recent_users' => $recent_users,
    'recent_apps'  => $recent_apps,
]);
