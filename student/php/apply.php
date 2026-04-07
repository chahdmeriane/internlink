<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth_student.php'; // FIX: use student-specific auth guard

$userId = $_SESSION['user_id'];

// Accept both JSON and FormData
$raw         = file_get_contents('php://input');
$body        = $raw ? (json_decode($raw, true) ?? []) : [];
$offerId     = (int)($body['internship_id'] ?? $body['offer_id'] ?? $_POST['internship_id'] ?? $_POST['offer_id'] ?? 0);
$coverLetter = trim($body['cover_letter'] ?? $_POST['cover_letter'] ?? '');

// FIX: sanitize cover letter input
$coverLetter = htmlspecialchars($coverLetter, ENT_QUOTES, 'UTF-8');

if (!$offerId) {
    echo json_encode(['success' => false, 'message' => 'Invalid offer.']);
    exit;
}

// Verify offer is active
try {
    $stmt = $pdo->prepare("SELECT id, skills FROM internship_offers WHERE id = ? AND status = 'active'");
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch();
} catch (PDOException $e) {
    error_log('apply.php offer fetch error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

if (!$offer) {
    echo json_encode(['success' => false, 'message' => 'Offer not found or no longer active.']);
    exit;
}

// Duplicate check
try {
    $dup = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND offer_id = ?");
    $dup->execute([$userId, $offerId]);
    if ($dup->fetch()) {
        echo json_encode(['success' => false, 'message' => 'You have already applied to this offer.']);
        exit;
    }
} catch (PDOException $e) {
    error_log('apply.php duplicate check error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
    exit;
}

// Compute match %
try {
    $skillStmt = $pdo->prepare("SELECT skills FROM student_profiles WHERE user_id = ?");
    $skillStmt->execute([$userId]);
    $sp = $skillStmt->fetch();
} catch (PDOException $e) {
    $sp = [];
}

$studentSkills = array_filter(array_map('strtolower', array_map('trim', explode(',', $sp['skills'] ?? ''))));
$required      = array_filter(array_map('strtolower', array_map('trim', explode(',', $offer['skills'] ?? ''))));

$matchPct = 0;
if (!empty($required) && !empty($studentSkills)) {
    $matched = 0;
    foreach ($required as $skill) {
        foreach ($studentSkills as $ss) {
            if (str_contains($ss, $skill) || str_contains($skill, $ss)) { $matched++; break; }
        }
    }
    $matchPct = (int) round(($matched / count($required)) * 100);
}

// Insert application
try {
    $pdo->prepare("
        INSERT INTO applications (student_id, offer_id, cover_letter, match_percent, status, applied_at)
        VALUES (?, ?, ?, ?, 'waiting', NOW())
    ")->execute([$userId, $offerId, $coverLetter ?: null, $matchPct]);
} catch (PDOException $e) {
    error_log('apply.php insert error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to submit application. Please try again.']);
    exit;
}

echo json_encode([
    'success'       => true,
    'message'       => 'Application submitted successfully!',
    'match_percent' => $matchPct,
]);
