<?php
session_start();

// إذا لم يوجد CSRF token في الجلسة، أنشئ واحد جديد
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// دالة لإدراج الـ token في أي form
function csrf_input() {
    $token = $_SESSION['csrf_token'];
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// دالة للتحقق من الـ token عند استلام POST
function check_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF validation failed.');
    }
}
?>