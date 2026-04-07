<?php
// ─────────────────────────────────────────────
//  logout.php — internLink
//  Destroys the session fully and redirects.
// ─────────────────────────────────────────────
error_reporting(0);
ini_set('display_errors', 0);

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_path', '/');
session_save_path(sys_get_temp_dir());
session_name('internlink_session');
session_start();

// Wipe all session variables
session_unset();

// FIX: expire the cookie in the browser so it is actually deleted immediately.
// session_destroy() only removes server-side data — the cookie stays without this.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: /internlink/html/index.html');
exit;
