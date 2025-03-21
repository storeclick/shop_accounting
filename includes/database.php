<?php
/**
 * فایل توابع دیتابیس سیستم حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

// بررسی دسترسی
if (!defined('BASE_PATH')) {
    die('دسترسی غیرمجاز');
}

/**
 * اتصال به دیتابیس
 */
function db_connect() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die('خطا در اتصال به دیتابیس: ' . $e->getMessage());
    }
}

/**
 * اجرای یک query و بازگرداندن یک سطر
 */
function db_get_row($query, $params = []) {
    $pdo = db_connect();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * اجرای یک query و بازگرداندن همه سطرها
 */
function db_get_rows($query, $params = []) {
    $pdo = db_connect();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * اجرای یک query برای درج، بروزرسانی یا حذف
 */
function db_query($query, $params = []) {
    $pdo = db_connect();
    $stmt = $pdo->prepare($query);
    return $stmt->execute($params);
}

/**
 * درج یک سطر و بازگرداندن شناسه درج شده
 */
function db_insert($query, $params = []) {
    $pdo = db_connect();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $pdo->lastInsertId();
}

/**
 * شروع یک تراکنش
 */
function db_begin_transaction() {
    $pdo = db_connect();
    $pdo->beginTransaction();
}

/**
 * تایید یک تراکنش
 */
function db_commit() {
    $pdo = db_connect();
    $pdo->commit();
}

/**
 * لغو یک تراکنش
 */
function db_rollback() {
    $pdo = db_connect();
    $pdo->rollBack();
}