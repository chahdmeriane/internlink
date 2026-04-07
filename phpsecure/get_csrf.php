<?php
// ─────────────────────────────────────────────
//  get_csrf.php — internLink
//  Called by login.html and register.html on
//  page load to get a CSRF token.
//  Your JS stores it and appends it to every
//  POST request as csrf_token.
// ─────────────────────────────────────────────
error_reporting(0);
ini_set('display_errors', 0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_path', '/');
session_save_path(sys_get_temp_dir());
session_name('internlink_session');
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header('Content-Type: application/json');
echo json_encode(['token' => $_SESSION['csrf_token']]);
