<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$userId = $_SESSION['user_id'];

// ── Fetch student skills ──────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT u.first_name, sp.skills
    FROM users u
    LEFT JOIN student_profiles sp ON sp.user_id = u.id
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$student = $stmt->fetch();

$studentSkills = [];
if (!empty($student['skills'])) {
    $studentSkills = array_filter(array_map('strtolower', array_map('trim', explode(',', $student['skills']))));
}

// ── Fetch all active offers — map columns to what HTML expects ────────────
$stmt = $pdo->prepare("
    SELECT
        io.id,
        io.title,
        io.description,
        io.field        AS domain,
        io.field        AS sector,
        io.location     AS wilaya,
        io.location     AS country,
        io.duration     AS duration_months,
        io.skills       AS required_skills,
        io.status,
        io.created_at,
        cp.company_name,
        cp.country      AS company_country,
        cp.sector       AS company_sector,
        0               AS is_paid,
        0               AS salary,
        'onsite'        AS work_type,
        0               AS match_percent
    FROM internship_offers io
    JOIN company_profiles cp ON cp.user_id = io.company_id
    WHERE io.status = 'active'
    ORDER BY io.created_at DESC
");
$stmt->execute();
$offers = $stmt->fetchAll();

// ── Applied offer IDs ─────────────────────────────────────────────────────
$stmt2 = $pdo->prepare("SELECT offer_id FROM applications WHERE student_id = ?");
$stmt2->execute([$userId]);
$appliedIds = array_column($stmt2->fetchAll(), 'offer_id');

// ── Saved offer IDs ───────────────────────────────────────────────────────
$stmt3 = $pdo->prepare("SELECT offer_id FROM saved_offers WHERE student_id = ?");
$stmt3->execute([$userId]);
$savedIds = array_column($stmt3->fetchAll(), 'offer_id');

// ── Compute match % per offer ─────────────────────────────────────────────
foreach ($offers as &$o) {
    $required = [];
    if (!empty($o['required_skills'])) {
        $required = array_filter(array_map('strtolower', array_map('trim', explode(',', $o['required_skills']))));
    }

    if (empty($required) || empty($studentSkills)) {
        $o['match_percent'] = 0;
    } else {
        $matched = 0;
        foreach ($required as $skill) {
            foreach ($studentSkills as $ss) {
                if (str_contains($ss, $skill) || str_contains($skill, $ss)) { $matched++; break; }
            }
        }
        $o['match_percent'] = (int)round(($matched / count($required)) * 100);
    }
}
unset($o);

// Sort by match % descending
usort($offers, fn($a, $b) => ($b['match_percent'] ?? 0) - ($a['match_percent'] ?? 0));

echo json_encode([
    'success'      => true,
    'internships'  => $offers,
    'applied_ids'  => $appliedIds,
    'saved_ids'    => $savedIds,
    'student_name' => $student['first_name'] ?? 'Student',
]);
