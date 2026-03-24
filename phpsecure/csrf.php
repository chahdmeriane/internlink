<?php
// Only start session if one isn't already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate token once per session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Embed token as hidden input in any HTML form
function csrf_input() {
    $token = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Validate token on POST — call at top of any PHP form handler
function check_csrf() {
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed.']);
        exit;
    }
}
