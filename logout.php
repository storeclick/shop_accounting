<?php
/**
 * خروج از سیستم
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

require_once 'config/config.php';

// ثبت خروج در لاگ
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (user_id, activity, ip_address, user_agent) 
            VALUES (?, 'خروج از سیستم', ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
}

// حذف توکن "مرا به خاطر بسپار"
if (isset($_COOKIE['remember_token'])) {
    try {
        $stmt = $pdo->prepare("
            DELETE FROM user_tokens 
            WHERE token = ?
        ");
        $stmt->execute([$_COOKIE['remember_token']]);
    } catch(PDOException $e) {
        error_log($e->getMessage());
    }
    
    setcookie('remember_token', '', time() - 3600, '/');
}

// پاک کردن تمام متغیرهای سشن
$_SESSION = array();

// حذف کوکی سشن
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// نابود کردن سشن
session_destroy();

// انتقال به صفحه ورود
header('Location: login.php');
exit;