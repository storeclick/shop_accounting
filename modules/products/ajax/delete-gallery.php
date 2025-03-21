<?php
/**
 * حذف تصویر از گالری محصول
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

require_once '../../../config/config.php';
check_access();

header('Content-Type: application/json');

try {
    if (!isset($_POST['image_id'])) {
        throw new Exception('شناسه تصویر نامعتبر است.');
    }
    
    $image_id = (int)$_POST['image_id'];
    
    // دریافت اطلاعات تصویر
    $image = db_get_row("SELECT * FROM product_images WHERE id = ?", [$image_id]);
    if (!$image) {
        throw new Exception('تصویر مورد نظر یافت نشد.');
    }
    
    // حذف فایل‌های تصویر
    delete_file($image['image'], 'products/gallery');
    delete_file($image['thumbnail'], 'products/gallery');
    
    // حذف از دیتابیس
    $result = db_query("DELETE FROM product_images WHERE id = ?", [$image_id]);
    
    if (!$result) {
        throw new Exception('خطا در حذف تصویر.');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'تصویر با موفقیت حذف شد.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}