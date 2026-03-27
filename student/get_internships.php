<?php
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'auth.php';

$userId = $_SESSION['user_id'];

// ── Fetch student skills ────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT skills, first_name FROM users u LEFT JOIN student_profiles sp ON sp.user_id = u.id WHERE u.id = ?");
$stmt->execute([$userId]);
$student = $stmt->fetch();

$studentSkills = [];
if (!empty($student['skills'])) {
    $studentSkills = array_map('strtolower', array_map('trim', explode(',', $student['skills'])));
    $studentSkills = array_filter($studentSkills);
}

// ── Fetch all active internships ────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT i.*, u.first_name AS company_name, cp.wilaya, cp.sector AS domain
    FROM internships i
    JOIN users u ON u.id = i.company_id
    LEFT JOIN company_profiles cp ON cp.user_id = i.company_id
    WHERE i.is_active = 1
      AND (i.deadline IS NULL OR i.deadline >= CURDATE())
    ORDER BY i.created_at DESC
");
$stmt->execute();
$internships = $stmt->fetchAll();

// ── Fetch applied internship IDs ────────────────────────────────────────────
$stmt2 = $pdo->prepare("SELECT internship_id FROM applications WHERE student_id = ?");
$stmt2->execute([$userId]);
$appliedIds = array_column($stmt2->fetchAll(), 'internship_id');

// ── Compute match % for each internship ────────────────────────────────────
foreach ($internships as &$i) {
    $required = [];
    if (!empty($i['required_skills'])) {
        $required = array_map('strtolower', array_map('trim', explode(',', $i['required_skills'])));
        $required = array_filter($required);
    }

    if (empty($required)) {
        $i['match_percent'] = 0;
    } else {
        // Count how many required skills the student has
        $matched = 0;
        foreach ($required as $skill) {
            foreach ($studentSkills as $sSkill) {
                // Partial match: skill contained in student skill or vice versa
                if (str_contains($sSkill, $skill) || str_contains($skill, $sSkill)) {
                    $matched++;
                    break;
                }
            }
        }
        // Match % = (matched / total required) * 100
        $i['match_percent'] = (int) round(($matched / count($required)) * 100);
    }
}
unset($i);

// Sort by match descending by default
usort($internships, fn($a, $b) => $b['match_percent'] - $a['match_percent']);

echo json_encode([
    'success'      => true,
    'internships'  => $internships,
    'applied_ids'  => $appliedIds,
    'student_name' => $student['first_name'] ?? 'Student',
]);
