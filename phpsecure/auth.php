<?php
session_start();

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Usage: include this file at the top of any protected page, e.g.:
//   require 'auth.php';
// It will automatically redirect unauthenticated users to login.html.
// After including it, $_SESSION['user_id'], ['email'], and ['role'] are available.
