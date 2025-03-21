<?php
/**
 * تنظیمات سیستم حسابداری فروشگاه
 * تاریخ ایجاد: 2025-03-21 14:52:23
 */

// بررسی تعریف ثابت‌ها
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__FILE__));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/shop_accounting');
}

// تنظیمات دیتابیس
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'shop_accounting');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

// تنظیمات سایت
if (!defined('SITE_TITLE')) {
    define('SITE_TITLE', 'سیستم حسابداری فروشگاه');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}

// تنظیمات مسیرها
if (!defined('MODULES_PATH')) {
    define('MODULES_PATH', BASE_PATH . '/modules');
}
if (!defined('INCLUDES_PATH')) {
    define('INCLUDES_PATH', BASE_PATH . '/includes');
}
if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', BASE_PATH . '/uploads');
}
if (!defined('ASSETS_PATH')) {
    define('ASSETS_PATH', BASE_PATH . '/assets');
}
if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_URL . '/assets');
}
if (!defined('UPLOADS_URL')) {
    define('UPLOADS_URL', BASE_URL . '/uploads');
}

// تنظیمات زمانی
date_default_timezone_set('Asia/Tehran');

// تنظیمات امنیتی
if (!defined('CSRF_TOKEN_NAME')) {
    define('CSRF_TOKEN_NAME', 'csrf_token');
}
if (!defined('SESSION_NAME')) {
    define('SESSION_NAME', 'shop_accounting');
}
if (!defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 7200);
}
if (!defined('REMEMBER_COOKIE_NAME')) {
    define('REMEMBER_COOKIE_NAME', 'remember_token');
}
if (!defined('REMEMBER_COOKIE_LIFETIME')) {
    define('REMEMBER_COOKIE_LIFETIME', 2592000);
}

// تنظیمات آپلود
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', 5242880);
}
if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
}

// تنظیمات فروش
if (!defined('VAT_RATE')) {
    define('VAT_RATE', 9);
}
if (!defined('INVOICE_PREFIX')) {
    define('INVOICE_PREFIX', 'INV-');
}
if (!defined('INVOICE_START_NUMBER')) {
    define('INVOICE_START_NUMBER', 1000);
}

// تنظیمات انبار
if (!defined('LOW_STOCK_WARNING')) {
    define('LOW_STOCK_WARNING', 5);
}
if (!defined('NEGATIVE_STOCK_ALLOWED')) {
    define('NEGATIVE_STOCK_ALLOWED', false);
}

// تنظیمات بروزرسانی
if (!defined('UPDATE_CHECK_INTERVAL')) {
    define('UPDATE_CHECK_INTERVAL', 86400);
}
if (!defined('UPDATE_REPOSITORY')) {
    define('UPDATE_REPOSITORY', 'cofeclick1/shop_accounting');
}
if (!defined('UPDATE_BRANCH')) {
    define('UPDATE_BRANCH', 'main');

// تنظیمات برنامه
if (!defined('SITE_VERSION')) {
    define('SITE_VERSION', '1.0.0');
}

// شروع سشن
session_name(SESSION_NAME);
session_start();

// لود کردن توابع
require_once INCLUDES_PATH . '/functions.php';  
}