<?php
/**
 * صفحه ثبت‌نام در سیستم
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

require_once 'config/config.php';

// اگر کاربر قبلاً لاگین کرده، به داشبورد منتقل شود
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// پردازش فرم ثبت‌نام
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = sanitize($_POST['name']);
    $mobile = sanitize($_POST['mobile']);
    
    // بررسی خالی نبودن فیلدها
    if (empty($username) || empty($password) || empty($confirm_password) || empty($name) || empty($mobile)) {
        $error = 'لطفاً همه فیلدها را پر کنید';
    } 
    // بررسی یکسان بودن رمز عبور
    elseif ($password !== $confirm_password) {
        $error = 'رمز عبور و تکرار آن یکسان نیستند';
    }
    // بررسی طول رمز عبور
    elseif (strlen($password) < 6) {
        $error = 'رمز عبور باید حداقل ۶ کاراکتر باشد';
    }
    // بررسی فرمت موبایل
    elseif (!preg_match('/^09[0-9]{9}$/', $mobile)) {
        $error = 'شماره موبایل معتبر نیست';
    }
    else {
        try {
            // بررسی تکراری نبودن نام کاربری
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'این نام کاربری قبلاً ثبت شده است';
            } else {
                // ثبت کاربر جدید
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, password, name, mobile, role, status, created_at)
                    VALUES (?, ?, ?, ?, 'user', 1, CURRENT_TIMESTAMP)
                ");
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                if ($stmt->execute([$username, $hashed_password, $name, $mobile])) {
                    $success = 'ثبت‌نام با موفقیت انجام شد. اکنون می‌توانید وارد شوید';
                    // ریدایرکت به صفحه ورود بعد از ۳ ثانیه
                    header("refresh:3;url=login.php");
                } else {
                    $error = 'خطا در ثبت اطلاعات';
                }
            }
        } catch(PDOException $e) {
            error_log($e->getMessage());
            $error = 'خطا در ثبت‌نام';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ثبت‌نام در سیستم حسابداری</title>
    
    <!-- فونت‌ها -->
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
        
        .register-box {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 500px;
            width: 100%;
            padding: 30px;
        }
        
        .register-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-logo img {
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
<body>

<div class="register-box">
    <div class="register-logo">
        <img src="<?= ASSETS_URL ?>/images/logo.png" alt="لوگو">
        <h4 class="mt-3">ثبت‌نام در سیستم حسابداری</h4>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $success ?>
        </div>
    <?php endif; ?>
    
    <form method="post" autocomplete="off">
        <div class="row">
            <div class="col-md-6 mb-4">
                <label class="form-label">نام کاربری</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           name="username" 
                           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                           required>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <label class="form-label">نام و نام خانوادگی</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-person-vcard"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           name="name" 
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
                           required>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label">شماره موبایل</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-phone"></i>
                </span>
                <input type="tel" 
                       class="form-control" 
                       name="mobile" 
                       pattern="09[0-9]{9}"
                       placeholder="مثال: 09123456789"
                       value="<?= isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : '' ?>"
                       required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
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
            
            <div class="col-md-6 mb-4">
                <label class="form-label">تکرار رمز عبور</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-key-fill"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           name="confirm_password" 
                           required>
                    <button class="btn btn-outline-secondary" 
                            type="button" 
                            onclick="togglePassword(this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 mb-3">
            <i class="bi bi-person-plus-fill me-2"></i>
            ثبت‌نام
        </button>
        
        <div class="text-center">
            حساب کاربری دارید؟
            <a href="login.php" class="text-decoration-none">
                ورود به سیستم
            </a>
        </div>
    </form>
</div>

<!-- اسکریپت‌ها -->
<script src="<?= ASSETS_URL ?>/js/bootstrap.bundle.min.js"></script>
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
</script>

</body>
</html>