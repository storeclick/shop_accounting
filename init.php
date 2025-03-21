<?php
/**
 * فایل راه‌اندازی اولیه برنامه
 */

// جلوگیری از دسترسی مستقیم
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// تنظیم نمایش خطاها
error_reporting(E_ALL);
ini_set('display_errors', 1);

// تعریف مسیر اصلی پروژه
define('ROOT_PATH', dirname(__FILE__));

// تعریف آدرس پایه وب‌سایت
$base_url = 'http://localhost/shop_accounting';
define('BASE_URL', rtrim($base_url, '/'));

// تعریف مسیر فایل‌های استاتیک
define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMAGES_URL', ASSETS_URL . '/images');

// راه‌اندازی نشست
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تنظیم منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// لود کردن فایل‌های اصلی
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/functions.php';