<?php
// ── auth.php — Session guard ────────────────────────────────────────────────
// Include at the top of every protected page/endpoint.
// Redirects unauthenticated users to login.html.
// After including: $_SESSION['user_id'], ['email'], ['role'] are available.

session_start();

if (!isset($_SESSION['user_id'])) {
    // For API endpoints return JSON, for pages redirect
    if (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated.', 'redirect' => 'login.html']);
        exit;
    }
    header('Location: login.html');
    exit;
}
