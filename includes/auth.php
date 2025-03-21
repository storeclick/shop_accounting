<?php
/**
 * توابع احراز هویت برنامه حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

// بررسی دسترسی
if (!defined('BASE_PATH')) {
    die('دسترسی غیرمجاز');
}

/**
 * بررسی اعتبار نام کاربری و رمز عبور
 */
function auth_login($username, $password) {
    // بررسی وجود کاربر
    $user = db_get_row("
        SELECT * FROM users 
        WHERE username = ? AND status = 'active'
    ", [$username]);
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'نام کاربری یا رمز عبور اشتباه است'
        ];
    }
    
    // بررسی رمز عبور
    if (!password_verify($password, $user['password'])) {
        return [
            'success' => false,
            'message' => 'نام کاربری یا رمز عبور اشتباه است'
        ];
    }
    
    // ذخیره اطلاعات در سشن
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    
    // ثبت لاگ ورود
    db_insert("
        INSERT INTO login_logs 
        (user_id, ip_address, user_agent, created_at)
        VALUES (?, ?, ?, NOW())
    ", [
        $user['id'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
    
    // بررسی آپدیت جدید
    check_update();
    
    return [
        'success' => true,
        'message' => 'ورود موفقیت‌آمیز'
    ];
}

/**
 * خروج کاربر
 */
function auth_logout() {
    // حذف سشن‌ها
    session_unset();
    session_destroy();
    
    // حذف کوکی‌ها
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
}

/**
 * تغییر رمز عبور
 */
function auth_change_password($user_id, $old_password, $new_password) {
    // بررسی رمز عبور فعلی
    $user = db_get_row("
        SELECT password FROM users 
        WHERE id = ?
    ", [$user_id]);
    
    if (!$user || !password_verify($old_password, $user['password'])) {
        return [
            'success' => false,
            'message' => 'رمز عبور فعلی اشتباه است'
        ];
    }
    
    // تغییر رمز عبور
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    db_query("
        UPDATE users 
        SET password = ?,
            updated_at = NOW()
        WHERE id = ?
    ", [$hash, $user_id]);
    
    return [
        'success' => true,
        'message' => 'رمز عبور با موفقیت تغییر کرد'
    ];
}

/**
 * فراموشی رمز عبور
 */
function auth_forgot_password($username) {
    // بررسی وجود کاربر
    $user = db_get_row("
        SELECT id, email FROM users 
        WHERE username = ? AND status = 'active'
    ", [$username]);
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'کاربری با این نام کاربری یافت نشد'
        ];
    }
    
    // تولید توکن بازیابی
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // ذخیره توکن
    db_insert("
        INSERT INTO password_resets 
        (user_id, token, expires_at, created_at)
        VALUES (?, ?, ?, NOW())
    ", [
        $user['id'],
        $token,
        $expires
    ]);
    
    // ارسال ایمیل بازیابی
    $reset_link = BASE_URL . '/reset-password.php?token=' . $token;
    
    $to = $user['email'];
    $subject = 'بازیابی رمز عبور - ' . SITE_TITLE;
    $message = "
        برای بازیابی رمز عبور خود روی لینک زیر کلیک کنید:
        {$reset_link}
        
        این لینک تا یک ساعت معتبر است.
        
        با تشکر
        " . SITE_TITLE;
    
    mail($to, $subject, $message);
    
    return [
        'success' => true,
        'message' => 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد'
    ];
}

/**
 * بررسی توکن بازیابی رمز عبور
 */
function auth_check_reset_token($token) {
    return db_get_row("
        SELECT user_id 
        FROM password_resets
        WHERE token = ? 
          AND expires_at > NOW()
          AND used_at IS NULL
        LIMIT 1
    ", [$token]);
}

/**
 * بازیابی رمز عبور
 */
function auth_reset_password($token, $password) {
    // بررسی توکن
    $reset = auth_check_reset_token($token);
    if (!$reset) {
        return [
            'success' => false,
            'message' => 'لینک بازیابی نامعتبر یا منقضی شده است'
        ];
    }
    
    // تغییر رمز عبور
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    db_begin_transaction();
    
    try {
        // بروزرسانی رمز عبور
        db_query("
            UPDATE users 
            SET password = ?,
                updated_at = NOW()
            WHERE id = ?
        ", [$hash, $reset['user_id']]);
        
        // غیرفعال کردن توکن
        db_query("
            UPDATE password_resets
            SET used_at = NOW()
            WHERE token = ?
        ", [$token]);
        
        db_commit();
        
        return [
            'success' => true,
            'message' => 'رمز عبور با موفقیت تغییر کرد'
        ];
        
    } catch (Exception $e) {
        db_rollback();
        
        return [
            'success' => false,
            'message' => 'خطا در تغییر رمز عبور'
        ];
    }
}