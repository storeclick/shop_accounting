<?php
/**
 * توابع دیتابیس سیستم حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

// بررسی دسترسی
if (!defined('BASE_PATH')) {
    die('دسترسی غیرمجاز');
}

/**
 * اتصال به دیتابیس
 * @return PDO
 */
function db_connect() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("خطا در اتصال به دیتابیس: " . $e->getMessage());
            die('خطا در اتصال به دیتابیس');
        }
    }
    
    return $pdo;
}

/**
 * اجرای یک کوئری و بازگرداندن یک سطر
 */
function db_get_row($query, $params = []) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * اجرای یک کوئری و بازگرداندن همه سطرها
 */
function db_get_rows($query, $params = []) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * درج یک سطر و بازگرداندن شناسه درج شده
 */
function db_insert($query, $params = []) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * بروزرسانی یک یا چند سطر
 */
function db_update($query, $params = []) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * حذف یک یا چند سطر
 */
function db_delete($query, $params = []) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * شمارش تعداد نتایج
 */
function db_count($query, $params = []) {
    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return 0;
    }
}

/**
 * شروع تراکنش
 */
function db_begin_transaction() {
    try {
        $pdo = db_connect();
        $pdo->beginTransaction();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * پایان موفق تراکنش
 */
function db_commit() {
    try {
        $pdo = db_connect();
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * برگشت تراکنش
 */
function db_rollback() {
    try {
        $pdo = db_connect();
        $pdo->rollBack();
        return true;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}