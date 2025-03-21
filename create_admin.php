<?php
// اتصال به دیتابیس
require_once 'config/db.php';

// نام کاربری و رمز عبور ادمین جدید
$admin_username = 'admin';
$admin_password = 'Mos6678';
$admin_full_name = 'Admin User';

// هش کردن رمز عبور
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

try {
    // افزودن کاربر ادمین به دیتابیس
    $sql = "INSERT INTO users (username, password, full_name, is_admin) VALUES (:username, :password, :full_name, :is_admin)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $admin_username,
        ':password' => $hashed_password,
        ':full_name' => $admin_full_name,
        ':is_admin' => 1
    ]);

    echo "کاربر ادمین با موفقیت ایجاد شد.";
} catch (PDOException $e) {
    echo "خطا در ایجاد کاربر ادمین: " . $e->getMessage();
}
?>