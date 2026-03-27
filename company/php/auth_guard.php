<?php
// ─────────────────────────────────────────────
//  auth_guard.php  —  internLink
//  Include at the TOP of every protected PHP
//  file. Checks that the user is logged in
//  and is a company account.
//
//  Usage:
//    require_once __DIR__ . '/auth_guard.php';
// ─────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['user_role'])) {
    // Not logged in
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        // AJAX request — return JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated.', 'redirect' => '../../login.html']);
    } else {
        header('Location: ../../login.html');
    }
    exit;
}

if ($_SESSION['user_role'] !== 'company') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Access denied.']);
    } else {
        header('Location: ../../login.html');
    }
    exit;
}

// ── Convenience variable ──────────────────────
$companyUserId = (int) $_SESSION['user_id'];
