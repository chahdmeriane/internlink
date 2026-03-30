<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$status = $_GET['status'] ?? 'all';
$search = trim($_GET['q']  ?? '');
$page   = max(1,(int)($_GET['page'] ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

$where  = ['1=1'];
$params = [];

if ($status !== 'all') {
    $where[]  = 'a.status = ?';
    $params[] = $status;
}
if ($search) {
    $where[]  = '(io.title LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)';
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like]);
}

$whereSQL = implode(' AND ', $where);

$cntStmt = $pdo->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN internship_offers io ON io.id = a.offer_id
    JOIN users u ON u.id = a.student_id
    WHERE $whereSQL
");
$cntStmt->execute($params);
$total = (int)$cntStmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT a.id, a.status, a.applied_at, a.match_percent,
           a.cover_letter,
           io.title AS internship_title,
           CONCAT(u.first_name,' ',u.last_name) AS student_name,
           u.email AS student_email,
           cp.company_name AS company_display
    FROM applications a
    JOIN internship_offers io ON io.id = a.offer_id
    JOIN users u              ON u.id  = a.student_id
    JOIN company_profiles cp  ON cp.user_id = io.company_id
    WHERE $whereSQL
    ORDER BY a.applied_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$apps = $stmt->fetchAll();

echo json_encode([
    'success'      => true,
    'applications' => $apps,
    'total'        => $total,
    'page'         => $page,
    'pages'        => (int)ceil($total / $limit),
]);
