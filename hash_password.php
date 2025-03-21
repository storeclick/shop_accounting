<?php
// رمز عبور جدید
$new_password = 'Mos6678';

// هش کردن رمز عبور
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

echo $hashed_password;
?>

UPDATE users SET username = 'akradim', password = '$2y$10$UkBRaxiwo0GUfvwgEultg.kMbxvaWbguxUcUbDPYS/msYVUeUzVmy' WHERE username = 'admin';

$2y$10$UkBRaxiwo0GUfvwgEultg.kMbxvaWbguxUcUbDPYS/msYVUeUzVmy