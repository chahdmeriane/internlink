<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../../phpsecure/db.php';
require_once __DIR__ . '/../../phpsecure/auth_student.php'; // FIX: use student-specific auth guard

$userId = $_SESSION['user_id'];
$data   = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    exit;
}

// FIX: sanitize all text inputs to prevent stored XSS
function clean(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

// Allowed student_profiles columns
$allowed = ['university', 'field_of_study', 'country', 'bio', 'skills'];

// Map frontend keys -> real column names
$colMap = [
    'academic_year' => 'year',
    'wilaya'        => 'city',
    'year'          => 'year',
    'city'          => 'city',
];

// Update first_name / last_name in users table
if (isset($data['firstName']) || isset($data['lastName'])) {
    $fn = clean($data['firstName'] ?? '');
    $ln = clean($data['lastName']  ?? '');
    if ($fn && $ln) {
        try {
            $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?")
                ->execute([$fn, $ln, $userId]);
        } catch (PDOException $e) {
            error_log('update_profile name error: ' . $e->getMessage());
        }
    }
}

// Build SET clause from allowed + provided fields
$sets   = [];
$params = [];

foreach ($allowed as $field) {
    $camel = lcfirst(str_replace('_', '', ucwords($field, '_')));
    $val   = $data[$field] ?? $data[$camel] ?? null;
    if ($val !== null) {
        $sets[]   = "$field = ?";
        $params[] = clean((string) $val);
    }
}

foreach ($colMap as $frontendKey => $realCol) {
    $camel = lcfirst(str_replace('_', '', ucwords($frontendKey, '_')));
    $val   = $data[$frontendKey] ?? $data[$camel] ?? null;
    if ($val !== null) {
        $alreadySet = false;
        foreach ($sets as $s) {
            if (strpos($s, "$realCol ") === 0) { $alreadySet = true; break; }
        }
        if (!$alreadySet) {
            $sets[]   = "$realCol = ?";
            $params[] = clean((string) $val);
        }
    }
}

if (!empty($sets)) {
    try {
        $chk = $pdo->prepare("SELECT id FROM student_profiles WHERE user_id = ?");
        $chk->execute([$userId]);
        $exists = $chk->fetch();

        if ($exists) {
            $params[] = $userId;
            $pdo->prepare("UPDATE student_profiles SET " . implode(', ', $sets) . " WHERE user_id = ?")
                ->execute($params);
        } else {
            $cols    = array_map(fn($s) => trim(explode(' =', $s)[0]), $sets);
            $cols[]  = 'user_id';
            $params[] = $userId;
            $pdo->prepare(
                "INSERT INTO student_profiles (" . implode(',', $cols) . ") VALUES (" .
                implode(',', array_fill(0, count($cols), '?')) . ")"
            )->execute($params);
        }
    } catch (PDOException $e) {
        error_log('update_profile DB error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
        exit;
    }
}

echo json_encode(['success' => true, 'message' => 'Profile updated.']);
