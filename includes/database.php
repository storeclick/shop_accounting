<?php
/**
 * توابع دیتابیس برنامه حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

// اتصال به دیتابیس
function db_connect() {
    static $db = null;
    
    if ($db === null) {
        try {
            $db = new PDO(
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
            error_log("خطا در اتصال به دیتابیس: " . $e->getMessage());
            die("خطا در اتصال به دیتابیس");
        }
    }
    
    return $db;
}

// اجرای کوئری با پارامتر
function db_query($query, $params = []) {
    try {
        $stmt = db_connect()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("خطا در اجرای کوئری: " . $e->getMessage());
        error_log("کوئری: " . $query);
        error_log("پارامترها: " . print_r($params, true));
        throw $e;
    }
}

// دریافت یک مقدار
function db_get_var($query, $params = []) {
    try {
        return db_query($query, $params)->fetchColumn();
    } catch (PDOException $e) {
        error_log("خطا در دریافت مقدار: " . $e->getMessage());
        return false;
    }
}

// دریافت یک سطر
function db_get_row($query, $params = []) {
    try {
        return db_query($query, $params)->fetch();
    } catch (PDOException $e) {
        error_log("خطا در دریافت سطر: " . $e->getMessage());
        return false;
    }
}

// دریافت چند سطر
function db_get_rows($query, $params = []) {
    try {
        return db_query($query, $params)->fetchAll();
    } catch (PDOException $e) {
        error_log("خطا در دریافت سطرها: " . $e->getMessage());
        return [];
    }
}

// درج یک سطر و دریافت شناسه
function db_insert($query, $params = []) {
    try {
        db_query($query, $params);
        return db_connect()->lastInsertId();
    } catch (PDOException $e) {
        error_log("خطا در درج سطر: " . $e->getMessage());
        return false;
    }
}

// آخرین شناسه درج شده
function db_last_insert_id() {
    return db_connect()->lastInsertId();
}

// شروع تراکنش
function db_begin_transaction() {
    try {
        db_connect()->beginTransaction();
    } catch (PDOException $e) {
        error_log("خطا در شروع تراکنش: " . $e->getMessage());
        return false;
    }
}

// تایید تراکنش
function db_commit() {
    try {
        db_connect()->commit();
    } catch (PDOException $e) {
        error_log("خطا در تایید تراکنش: " . $e->getMessage());
        return false;
    }
}

// برگشت تراکنش
function db_rollback() {
    try {
        db_connect()->rollBack();
    } catch (PDOException $e) {
        error_log("خطا در برگشت تراکنش: " . $e->getMessage());
        return false;
    }
}

// تعداد سطرهای تحت تاثیر
function db_affected_rows($stmt) {
    return $stmt->rowCount();
}

// فرار از کاراکترهای خاص
function db_escape($value) {
    if (is_array($value)) {
        return array_map('db_escape', $value);
    }
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}