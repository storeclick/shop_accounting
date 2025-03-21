<?php
// اتصال به دیتابیس
require_once 'config/db.php';

// نام کاربری و رمز عبور جدید
$new_username = 'akradim';
$new_password = 'Mos6678';

// هش کردن رمز عبور جدید
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    // به‌روزرسانی نام کاربری و رمز عبور در دیتابیس
    $sql = "UPDATE users SET username = :username, password = :password WHERE username = 'admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':username' => $new_username,
        ':password' => $hashed_password
    ]);

    echo "نام کاربری و رمز عبور با موفقیت به‌روزرسانی شدند.";
} catch (PDOException $e) {
    echo "خطا در به‌روزرسانی نام کاربری و رمز عبور: " . $e->getMessage();
}
?>