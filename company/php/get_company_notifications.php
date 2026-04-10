<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth_guard.php';

// Mark all as read if requested
if (!empty($_GET['mark_read'])) {
    try {
        $pdo->prepare("UPDATE applications SET is_read_company = 1 WHERE offer_id IN (SELECT id FROM internship_offers WHERE company_id = ?)")
            ->execute([$companyUserId]);
    } catch (PDOException $e) { /* column may not exist yet */ }
    echo json_encode(['success' => true]);
    exit;
}

// Fetch recent applications as notifications
try {
    $stmt = $pdo->prepare("
        SELECT
            a.id,
            a.status,
            a.applied_at,
            a.match_percent,
            u.first_name,
            u.last_name,
            u.email        AS student_email,
            sp.university,
            o.title        AS offer_title
        FROM applications a
        JOIN users u ON u.id = a.student_id
        LEFT JOIN student_profiles sp ON sp.user_id = u.id
        JOIN internship_offers o ON o.id = a.offer_id
        WHERE o.company_id = ?
        ORDER BY a.applied_at DESC
        LIMIT 30
    ");
    $stmt->execute([$companyUserId]);
    $apps = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('get_company_notifications error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

$notifications = [];
foreach ($apps as $app) {
    $name = $app['first_name'] . ' ' . $app['last_name'];
    $icon = '👤';
    $title = 'New Application';
    $msg = $name . ' applied to "' . $app['offer_title'] . '"';
    if ($app['match_percent']) {
        $msg .= ' — ' . $app['match_percent'] . '% match';
    }
    if ($app['university']) {
        $msg .= ' · ' . $app['university'];
    }

    $notifications[] = [
        'id'      => 'app_' . $app['id'],
        'icon'    => $icon,
        'title'   => $title,
        'message' => $msg,
        'time'    => $app['applied_at'],
        'is_read' => false,
        'link'    => 'company_applications.html',
    ];
}

echo json_encode([
    'success'       => true,
    'notifications' => $notifications,
    'count'         => count($notifications),
]);
