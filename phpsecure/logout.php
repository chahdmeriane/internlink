<?php
session_start();

// حذف كل بيانات الجلسة
$_SESSION = [];

// تدمير الجلسة بالكامل
session_destroy();

// الرجوع لصفحة تسجيل الدخول
header("Location: login.html");
exit;
?>