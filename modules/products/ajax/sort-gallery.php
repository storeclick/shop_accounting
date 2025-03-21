<?php
/**
 * ذخیره ترتیب تصاویر گالری
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

require_once '../../../config/config.php';
check_access();

header('Content-Type: application/json');

try {
    // دریافت داده‌های ارسالی
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!is_array($input)) {
        throw new Exception('داده‌های نامعتبر.');
    }
    
    // بروزرسانی ترتیب تصاویر
    foreach ($input as $item) {
        db_query("
            UPDATE product_images 
            SET sort_order = ? 
            WHERE id = ?
        ", [$item['order'], $item['id']]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'ترتیب تصاویر با موفقیت ذخیره شد.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}