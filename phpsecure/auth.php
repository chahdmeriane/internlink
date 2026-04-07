<?php
error_reporting(0);
ini_set('display_errors', 0);

// FIX: harden session cookie before starting the session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
// ini_set('session.cookie_secure', 1); // uncomment when HTTPS is enabled
ini_set('session.cookie_path', '/');

session_save_path(sys_get_temp_dir());
session_name('internlink_session');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FIX: check login + role in one place, return JSON for API requests
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (str_contains($accept, 'application/json') || !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authorised.']);
    } else {
        header('Location: /internlink/html/login.html');
    }
    exit;
}
