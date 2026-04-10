<?php
// ─────────────────────────────────────────────
//  auth_student.php — internLink
//  Include at the top of every student PHP file.
//  Blocks anyone who is not a logged-in student.
// ─────────────────────────────────────────────
error_reporting(0);
ini_set('display_errors', 0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
// ini_set('session.cookie_secure', 1); // uncomment when HTTPS is enabled
ini_set('session.cookie_path', '/');

session_save_path(sys_get_temp_dir());
session_name('internlink_session');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    } else {
        header('Location: /internlink/html/login.html');
    }
    exit;
}
