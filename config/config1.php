includes/auth.php<?php
/**
 * تنظیمات اصلی برنامه فروشگاهی
 * نام پروژه: سیستم حسابداری فروشگاه
 * نویسنده: [نام شما]
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 * آخرین ویرایش: ۱۴۰۳/۰۱/۰۱
 */

// جلوگیری از اجرای مستقیم فایل
if (!defined('BASE_PATH')) {
    // مسیر اصلی پروژه
    define('BASE_PATH', dirname(__DIR__));
    
    // آدرس وب‌سایت - تنظیم بر اساس مسیر نصب
    define('BASE_URL', 'http://localhost/shop_accounting');
    
    // مسیر آپلود فایل‌ها
    define('UPLOADS_PATH', BASE_PATH . '/uploads');
    define('UPLOADS_URL', BASE_URL . '/uploads');
    
    // مسیر فایل‌های استاتیک
    define('ASSETS_PATH', BASE_PATH . '/assets');
    define('ASSETS_URL', BASE_URL . '/assets');
    
    // تنظیمات دیتابیس
    define('DB_HOST', 'localhost');     // آدرس سرور دیتابیس
    define('DB_NAME', 'shop_accounting'); // نام دیتابیس
    define('DB_USER', 'root');          // نام کاربری دیتابیس
    define('DB_PASS', '');              // رمز عبور دیتابیس
    
    // نسخه برنامه - برای سیستم بروزرسانی
    define('APP_VERSION', '1.0.0');
    
    // تنظیمات زمانی
    date_default_timezone_set('Asia/Tehran');
    
    // نمایش خطاها در حالت توسعه
    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
    
    // تنظیمات امنیتی
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
}

// تابع خودکار لود کلاس‌ها
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/classes/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// لود کردن توابع عمومی
require_once BASE_PATH . '/includes/functions.php';

// لود کردن تنظیمات دیتابیس
require_once BASE_PATH . '/config/db.php';

// شروع یا ادامه سشن
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// تنظیم هدرهای امنیتی
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');