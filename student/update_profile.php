<?php
session_start();
header('Content-Type: application/json');
require 'db.php';
require 'auth.php';

$userId = $_SESSION['user_id'];
$data   = json_decode(file_get_contents('php://input'), true);

// ── Allowed fields ──────────────────────────────────────────────────────────
$allowed = ['phone','university','field_of_study','academic_year','wilaya','bio','skills','linkedin','github'];

// Update users table (name only)
if (isset($data['firstName']) || isset($data['lastName'])) {
    $fn = trim($data['firstName'] ?? '');
    $ln = trim($data['lastName']  ?? '');
    if ($fn && $ln) {
        $pdo->prepare("UPDATE users SET first_name=?, last_name=? WHERE id=?")->execute([$fn, $ln, $userId]);
    }
}

// ── Upsert student_profiles ─────────────────────────────────────────────────
// Check if profile row exists
$exists = $pdo->prepare("SELECT id FROM student_profiles WHERE user_id=?")->execute([$userId]);
$row    = $pdo->prepare("SELECT id FROM student_profiles WHERE user_id=?")->execute([$userId]) ? $pdo->query("SELECT id FROM student_profiles WHERE user_id=$userId")->fetch() : null;

// Build set clause only from allowed + provided fields
$sets   = [];
$params = [];
foreach ($allowed as $field) {
    $key = lcfirst(str_replace('_', '', ucwords($field, '_'))); // e.g. field_of_study → fieldOfStudy
    // Accept both snake_case and camelCase from client
    $val = $data[$field] ?? $data[$key] ?? null;
    if ($val !== null) {
        $sets[]   = "$field = ?";
        $params[] = trim($val);
    }
}

if (!empty($sets)) {
    // Check existence cleanly
    $chk = $pdo->prepare("SELECT id FROM student_profiles WHERE user_id=?");
    $chk->execute([$userId]);
    $exists = $chk->fetch();

    if ($exists) {
        $params[] = $userId;
        $pdo->prepare("UPDATE student_profiles SET " . implode(', ', $sets) . " WHERE user_id=?")->execute($params);
    } else {
        $params[] = $userId;
        $pdo->prepare("INSERT INTO student_profiles (" . implode(',', array_map(fn($s) => explode(' =', $s)[0], $sets)) . ", user_id) VALUES (" . implode(',', array_fill(0, count($sets)+1, '?')) . ")")->execute($params);
    }
}

echo json_encode(['success' => true, 'message' => 'Profile updated.']);
