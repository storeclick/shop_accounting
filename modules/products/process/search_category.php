<?php
/**
 * جستجوی دسته‌بندی محصولات
 * تاریخ ایجاد: 1402/12/29
 */

require_once '../../../config/config.php';
require_once '../../../config/db.php';

// بررسی درخواست AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// دریافت پارامترهای جستجو
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$parent_id = isset($_GET['parent_id']) && !empty($_GET['parent_id']) ? intval($_GET['parent_id']) : null;

// بررسی خالی نبودن عبارت جستجو
if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    // آماده‌سازی پارامترها
    $params = [];
    $sql = '';
    
    // ساخت کوئری جستجو
    if ($parent_id !== null) {
        // جستجو در زیر دسته‌ها
        $sql = "SELECT id, category_name 
                FROM categories 
                WHERE parent_id = ? 
                AND category_name LIKE ? 
                ORDER BY category_name ASC 
                LIMIT 10";
        $params = [$parent_id, "%$query%"];
    } else {
        // جستجو در دسته‌های اصلی
        $sql = "SELECT id, category_name 
                FROM categories 
                WHERE parent_id IS NULL 
                AND category_name LIKE ? 
                ORDER BY category_name ASC 
                LIMIT 10";
        $params = ["%$query%"];
    }

    // اجرای کوئری
    $results = db_get_rows($sql, $params);

    // بررسی نتایج
    if (empty($results)) {
        $results = []; // برگرداندن آرایه خالی در صورت نبود نتیجه
    }

    // تنظیم هدر JSON
    header('Content-Type: application/json; charset=utf-8');
    
    // برگرداندن نتایج
    echo json_encode($results);

} catch (Exception $e) {
    // ثبت خطا در لاگ
    error_log("خطا در جستجوی دسته‌بندی: " . $e->getMessage());
    
    // ارسال کد خطای 500
    http_response_code(500);
    
    // برگرداندن پیام خطا
    echo json_encode([
        'error' => true,
        'message' => 'خطا در جستجوی دسته‌بندی',
        'details' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}