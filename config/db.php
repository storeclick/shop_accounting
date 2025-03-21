<?php
/**
 * تنظیمات اتصال به پایگاه داده
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

// جلوگیری از دسترسی مستقیم به فایل
if (!defined('BASE_PATH')) {
    die('دسترسی مستقیم به این فایل مجاز نیست');
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die('خطا در اتصال به پایگاه داده: ' . $e->getMessage());
}

/**
 * دریافت یک رکورد از جدول
 */
function db_get_row($table, $where = []) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM {$table}";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * دریافت چند رکورد از جدول
 */
function db_get_rows($table, $where = [], $orderBy = '', $limit = 0) {
    global $pdo;
    
    try {
        $sql = "SELECT * FROM {$table}";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/**
 * درج رکورد در جدول
 */
function db_insert($table, $data) {
    global $pdo;
    
    try {
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * بروزرسانی رکورد در جدول
 */
function db_update($table, $data, $where) {
    global $pdo;
    
    try {
        $sets = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $conditions = [];
        foreach ($where as $key => $value) {
            $conditions[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $sets);
        $sql .= " WHERE " . implode(' AND ', $conditions);
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * حذف رکورد از جدول
 */
function db_delete($table, $where) {
    global $pdo;
    
    try {
        $conditions = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $conditions);
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * شمارش تعداد رکوردها
 */
function db_count($table, $where = []) {
    global $pdo;
    
    try {
        $sql = "SELECT COUNT(*) FROM {$table}";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return 0;
    }
}