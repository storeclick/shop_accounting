<?php
/**
 * صفحه ورود به سیستم حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 * آخرین بروزرسانی: ۱۴۰۳/۰۱/۰۲
 */

// تعریف مسیر اصلی
define('BASE_PATH', __DIR__);

// لود کردن تنظیمات
require_once 'config/config.php';

// ریدایرکت به داشبورد اگر لاگین است
if (is_logged_in()) {
    redirect('index.php');
}

$error = '';

// پردازش فرم ورود
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'لطفاً نام کاربری و رمز عبور را وارد کنید';
    } else {
        // تلاش برای ورود
        $result = auth_login($username, $password, $remember);
        
        if ($result['success']) {
            // ریدایرکت به صفحه اصلی
            redirect('index.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود به سیستم - <?= SITE_TITLE ?></title>
    
    <!-- فونت ایران‌سنس -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    
    <!-- استایل‌ها -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-box {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 400px;
            width: 100%;
            padding: 30px;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo img {
            width: 100px;
            height: auto;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }
        
        .btn-primary {
            background: #764ba2;
            border-color: #764ba2;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #667eea;
            border-color: #667eea;
            transform: translateY(-1px);
        }
        
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body class="<?= isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light' ?>">

<div class="login-box">
    <div class="login-logo">
        <img src="<?= ASSETS_URL ?>/images/logo.png" alt="<?= SITE_TITLE ?>">
        <h4 class="mt-3"><?= SITE_TITLE ?></h4>
        <p class="text-muted small"><?= SITE_VERSION ?></p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <form method="post" autocomplete="off">
        <!-- توکن CSRF -->
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generate_csrf_token() ?>">
        
        <div class="mb-4">
            <label class="form-label">نام کاربری</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text" 
                       class="form-control" 
                       name="username" 
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                       required 
                       autofocus>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label">رمز عبور</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-key"></i>
                </span>
                <input type="password" 
                       class="form-control" 
                       name="password" 
                       required>
                <button class="btn btn-outline-secondary" 
                        type="button" 
                        onclick="togglePassword(this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="form-check">
                <input type="checkbox" 
                       class="form-check-input" 
                       name="remember" 
                       id="remember">
                <label class="form-check-label" for="remember">
                    مرا به خاطر بسپار
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-box-arrow-in-left me-2"></i>
            ورود به سیستم
        </button>
        
        <div class="text-center">
            <a href="forgot-password.php" class="text-decoration-none small">
                رمز عبور خود را فراموش کرده‌اید؟
            </a>
        </div>
        <div class="text-center mt-3">
    <hr>
    حساب کاربری ندارید؟
    <a href="register.php" class="text-decoration-none">
        ثبت‌نام در سیستم
    </a>
</div>
    </form>
</div>

<script>
function togglePassword(button) {
    const input = button.parentElement.querySelector('input');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// حذف پیام‌های خطا بعد از 5 ثانیه
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = 0;
        setTimeout(() => alert.remove(), 500);
    }, 5000);
});

// تنظیم تم دارک/لایت
const theme = localStorage.getItem('theme') || 'light';
document.body.classList.add(theme);
</script>

<script src="<?= ASSETS_URL ?>/js/bootstrap.bundle.min.js"></script>
</body>
</html>