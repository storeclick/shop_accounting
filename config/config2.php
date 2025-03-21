<?php
/**
 * تنظیمات برنامه حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

// بررسی تعریف مسیر اصلی
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// نمایش خطاها در محیط توسعه
error_reporting(E_ALL);
ini_set('display_errors', 1);

// تنظیمات دیتابیس
define('DB_HOST', 'localhost');     // آدرس سرور دیتابیس
define('DB_NAME', 'shop_accounting'); // نام دیتابیس
define('DB_USER', 'root');          // نام کاربری دیتابیس
define('DB_PASS', '');              // رمز عبور دیتابیس
define('DB_CHARSET', 'utf8mb4');    // کاراکترست دیتابیس

// تنظیمات مسیرها
define('BASE_URL', 'http://localhost/shop_accounting'); // آدرس اصلی برنامه
define('ASSETS_URL', BASE_URL . '/assets');           // مسیر فایل‌های استاتیک
define('UPLOADS_URL', BASE_URL . '/uploads');         // مسیر آپلودها - لینک
define('UPLOADS_PATH', BASE_PATH . '/uploads');       // مسیر آپلودها - فیزیکی

// تنظیمات برنامه
define('SITE_TITLE', 'حسابداری فروشگاه');          // عنوان برنامه
define('SITE_DESCRIPTION', 'برنامه حسابداری و مدیریت فروشگاه'); // توضیحات
define('SITE_VERSION', '1.0.0');                     // نسخه برنامه
define('SITE_AUTHOR', 'cofeclick1');                 // نویسنده
define('SITE_EMAIL', 'info@example.com');            // ایمیل پشتیبانی

// تنظیمات امنیتی
define('CSRF_TOKEN_NAME', 'csrf_token');             // نام توکن CSRF
define('SESSION_NAME', 'shop_accounting');           // نام سشن
define('SESSION_LIFETIME', 7200);                    // مدت زمان سشن (2 ساعت)
define('REMEMBER_COOKIE_NAME', 'remember_token');    // نام کوکی مرا به خاطر بسپار
define('REMEMBER_COOKIE_LIFETIME', 2592000);         // مدت زمان کوکی (30 روز)
define('PASSWORD_RESET_LIFETIME', 3600);             // مدت زمان بازیابی رمز (1 ساعت)

// تنظیمات آپلود
define('MAX_UPLOAD_SIZE', 5242880);                  // حداکثر حجم آپلود (5MB)
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']); // پسوندهای مجاز تصاویر
define('MAX_IMAGE_WIDTH', 2048);                     // حداکثر عرض تصویر
define('MAX_IMAGE_HEIGHT', 2048);                    // حداکثر ارتفاع تصویر
define('THUMBNAIL_WIDTH', 150);                      // عرض تصویر بندانگشتی
define('THUMBNAIL_HEIGHT', 150);                     // ارتفاع تصویر بندانگشتی

// تنظیمات صفحه‌بندی
define('ITEMS_PER_PAGE', 20);                        // تعداد آیتم در هر صفحه
define('PAGE_LINKS', 5);                             // تعداد لینک‌های صفحه‌بندی

// تنظیمات منطقه زمانی و زبان
date_default_timezone_set('Asia/Tehran');            // منطقه زمانی
setlocale(LC_ALL, 'fa_IR.UTF-8', 'fa_IR', 'fa');    // تنظیمات محلی

// تنظیمات فاکتور
define('INVOICE_PREFIX', 'INV-');                    // پیشوند شماره فاکتور
define('INVOICE_START', '1000');                     // شماره شروع فاکتور
define('VAT_RATE', 9);                              // درصد مالیات بر ارزش افزوده

// تنظیمات انبار
define('LOW_STOCK_WARNING', 5);                      // هشدار موجودی کم
define('STOCK_WARNING_EMAIL', true);                 // ارسال ایمیل هشدار موجودی
define('NEGATIVE_STOCK_ALLOWED', false);             // اجازه موجودی منفی

// تنظیمات بروزرسانی
define('UPDATE_CHECK_INTERVAL', (int) '86400');      // فاصله بررسی بروزرسانی (1 روز)
define('UPDATE_REPOSITORY', 'cofeclick1/shop_accounting'); // مخزن گیت‌هاب
define('UPDATE_BRANCH', 'main');                     // شاخه بروزرسانی

// تنظیمات گزارش‌گیری
define('REPORT_TIMEZONE', 'Asia/Tehran');            // منطقه زمانی گزارش‌ها
define('REPORT_DATE_FORMAT', 'Y/m/d');              // فرمت تاریخ در گزارش‌ها
define('REPORT_TIME_FORMAT', 'H:i:s');              // فرمت ساعت در گزارش‌ها

// تنظیمات پشتیبان‌گیری
define('BACKUP_PATH', BASE_PATH . '/backups');       // مسیر پشتیبان‌گیری
define('BACKUP_FILENAME', 'backup_%s.sql');          // فرمت نام فایل پشتیبان
define('BACKUP_COMPRESS', true);                     // فشرده‌سازی فایل پشتیبان
define('BACKUP_LIFETIME', (int) '604800');           // مدت نگهداری پشتیبان (7 روز)

// شروع جلسه
session_name(SESSION_NAME);
session_start();

// لود کردن توابع
$required_files = [
    'includes/functions.php',    // توابع عمومی
    'includes/database.php',     // توابع دیتابیس
    'includes/auth.php',         // توابع احراز هویت
];

foreach ($required_files as $file) {
    $path = BASE_PATH . '/' . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        // لاگ خطا
        error_log("خطا: فایل {$file} یافت نشد!");
    }
}

// بررسی آپدیت جدید (فقط برای کاربران لاگین شده)
if (isset($_SESSION['user_id']) && 
    (!isset($_SESSION['update_checked']) || 
    (time() - $_SESSION['update_checked']) > (int) UPDATE_CHECK_INTERVAL)) {
    check_update();
}

// تنظیم متغیرهای عمومی
$GLOBALS['messages'] = [];           // پیام‌ها
$GLOBALS['start_time'] = microtime(true); // زمان شروع اجرا