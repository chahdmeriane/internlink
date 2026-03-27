<?php
// ── company_dashboard_data.php ───────────────────────────────────────────────
// Returns all data needed to populate the company dashboard in one request.
// Response: { success, company, stats, recent_applications, active_offers, activity }

require 'company_auth.php';
require 'db.php';
header('Content-Type: application/json');

$uid = (int) $_SESSION['user_id'];

// ── 1. Company name for the welcome banner ──────────────────────────────────
$stmt = $pdo->prepare("
    SELECT u.first_name, cp.company_name, cp.avatar_path
    FROM users u
    LEFT JOIN company_profiles cp ON cp.user_id = u.id
    WHERE u.id = ?
");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// ── 2. Stats ────────────────────────────────────────────────────────────────
// Total active offers
$stmt = $pdo->prepare("SELECT COUNT(*) FROM internship_offers WHERE company_id = ? AND status = 'active'");
$stmt->execute([$uid]);
$activeOffers = (int) $stmt->fetchColumn();

// Total applications across all offers
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN internship_offers o ON o.id = a.offer_id
    WHERE o.company_id = ?
");
$stmt->execute([$uid]);
$totalApps = (int) $stmt->fetchColumn();

// Pending (waiting) applications
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN internship_offers o ON o.id = a.offer_id
    WHERE o.company_id = ? AND a.status = 'waiting'
");
$stmt->execute([$uid]);
$pending = (int) $stmt->fetchColumn();

// Accepted applications
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM applications a
    JOIN internship_offers o ON o.id = a.offer_id
    WHERE o.company_id = ? AND a.status = 'accepted'
");
$stmt->execute([$uid]);
$accepted = (int) $stmt->fetchColumn();

// ── 3. Recent applications (last 5) ─────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.status,
        a.applied_at,
        CONCAT(u.first_name, ' ', u.last_name) AS student_name,
        o.title AS offer_title
    FROM applications a
    JOIN internship_offers o ON o.id = a.offer_id
    JOIN users u             ON u.id = a.student_id
    WHERE o.company_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->execute([$uid]);
$recentApps = $stmt->fetchAll();

// ── 4. Active offers with applicant counts (top 4) ──────────────────────────
$stmt = $pdo->prepare("
    SELECT
        o.id, o.title, o.field, o.location, o.duration,
        COUNT(a.id) AS applicant_count
    FROM internship_offers o
    LEFT JOIN applications a ON a.offer_id = o.id
    WHERE o.company_id = ? AND o.status = 'active'
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 4
");
$stmt->execute([$uid]);
$activeOffersList = $stmt->fetchAll();

// ── 5. Activity feed (last 5 events) ────────────────────────────────────────
// Mix of: new applications and status changes (accepted/rejected)
$stmt = $pdo->prepare("
    SELECT
        a.id,
        a.status,
        a.applied_at,
        CONCAT(u.first_name, ' ', u.last_name) AS student_name,
        o.title AS offer_title
    FROM applications a
    JOIN internship_offers o ON o.id = a.offer_id
    JOIN users u             ON u.id = a.student_id
    WHERE o.company_id = ?
    ORDER BY a.applied_at DESC
    LIMIT 5
");
$stmt->execute([$uid]);
$activity = $stmt->fetchAll();

// ── 6. Profile completion score ─────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT company_name, country, sector, description, avatar_path, phone, linkedin
    FROM company_profiles
    WHERE user_id = ?
");
$stmt->execute([$uid]);
$cp = $stmt->fetch() ?: [];

$profileFields = [
    'company_name_country' => !empty($cp['company_name']) && !empty($cp['country']),
    'sector'               => !empty($cp['sector']),
    'description'          => !empty($cp['description']),
    'avatar'               => !empty($cp['avatar_path']),
];
$done  = count(array_filter($profileFields));
$total = count($profileFields);
$pct   = (int) round(($done / $total) * 100);

// ── Response ─────────────────────────────────────────────────────────────────
echo json_encode([
    'success' => true,
    'company' => [
        'name'        => $user['company_name'] ?: $user['first_name'],
        'avatar_path' => $user['avatar_path'] ?? null,
    ],
    'stats' => [
        'active_offers' => $activeOffers,
        'total_apps'    => $totalApps,
        'pending'       => $pending,
        'accepted'      => $accepted,
    ],
    'recent_applications' => $recentApps,
    'active_offers'       => $activeOffersList,
    'activity'            => $activity,
    'profile_completion'  => [
        'pct'    => $pct,
        'fields' => $profileFields,
    ],
]);
