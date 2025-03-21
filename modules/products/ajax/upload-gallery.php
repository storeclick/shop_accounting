<?php
/**
 * تنظیمات اصلی برنامه فروشگاهی
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

// اضافه کردن اتولودر composer
require_once __DIR__ . '/../vendor/autoload.php';

use Morilog\Jalali\Jalalian;

// محاسبه زمان اجرای برنامه
define('START_TIME', microtime(true));

// تنظیمات منطقه‌ای
date_default_timezone_set('Asia/Tehran');
setlocale(LC_ALL, 'fa_IR.utf8');

// تنظیمات خطایابی
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// نسخه برنامه
define('APP_VERSION', '1.0.0');
define('APP_VERSION_DATE', '۱۴۰۳/۰۱/۰۱');

// مسیرها
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/shop_accounting');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('UPLOADS_URL', BASE_URL . '/uploads');
define('LOGS_PATH', BASE_PATH . '/logs');
define('CACHE_PATH', BASE_PATH . '/cache');

// تنظیمات کش
define('CACHE_ENABLED', false); // فعلا غیرفعال می‌کنیم
define('CACHE_TIME', 3600); // یک ساعت

// ساخت پوشه‌های مورد نیاز
foreach ([LOGS_PATH, UPLOADS_PATH, CACHE_PATH] as $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');
define('DB_NAME', 'shop_accounting');
define('DB_USER', 'root');
define('DB_PASS', '');

// تنظیمات برنامه
define('SITE_TITLE', 'سیستم حسابداری فروشگاه');
define('ADMIN_EMAIL', 'admin@example.com');
define('SUPPORT_PHONE', '09123456789');

// تنظیمات آپلود
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// شروع سشن
session_start();

// لود کردن توابع
require_once BASE_PATH . '/includes/functions.php';

// اتصال به دیتابیس
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_persian_ci"
        ]
    );
} catch (PDOException $e) {
    error_log('خطای اتصال به دیتابیس: ' . $e->getMessage());
    die('متأسفانه در حال حاضر امکان اتصال به پایگاه داده وجود ندارد.');
}

/**
 * تبدیل تاریخ میلادی به شمسی
 */
function jdate($format, $timestamp = null) {
    if (is_null($timestamp)) {
        $timestamp = time();
    }
    return Jalalian::forge($timestamp)->format($format);
}

// تنظیم هدرهای امنیتی
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// بررسی بروزرسانی برای ادمین
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
    if (isset($_GET['check_update'])) {
        $current_version = APP_VERSION;
        // در اینجا می‌توانید به سرور بروزرسانی متصل شوید
        $_SESSION['update_available'] = [
            'current_version' => APP_VERSION,
            'new_version' => '1.0.1',
            'details' => 'بروزرسانی جدید در دسترس است',
            'download_url' => '#'
        ];
    }
}