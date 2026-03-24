<?php
session_start();

// إذا المستخدم غير مسجل دخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>
\\hada connectih b dashboreds mbed 
