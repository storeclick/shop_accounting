<?php
/**
 * صفحه ثبت‌نام در سیستم حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

// تنظیم مسیر اصلی
define('BASE_PATH', dirname(__FILE__));

// لود کردن تنظیمات و توابع مورد نیاز
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

// اگر کاربر قبلاً لاگین کرده، به داشبورد منتقل شود
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// پردازش فرم ثبت‌نام
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // بررسی توکن CSRF
    if (!CSRF::validate()) {
        die('توکن CSRF نامعتبر است');
    }

    // دریافت و پاکسازی داده‌های ورودی
    $data = [
        'username' => sanitize($_POST['username']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'name' => sanitize($_POST['name']),
        'mobile' => sanitize($_POST['mobile'])
    ];

    // اعتبارسنجی داده‌ها
    $validator = new Validator($data);
    $validator->rules([
        'username' => 'required|min:3|max:50',
        'password' => 'required|min:6',
        'confirm_password' => 'required',
        'name' => 'required|min:3|max:100',
        'mobile' => 'required|mobile'
    ]);

    if ($validator->validate()) {
        // بررسی یکسان بودن رمز عبور
        if ($data['password'] !== $data['confirm_password']) {
            $error = 'رمز عبور و تکرار آن یکسان نیستند';
        } else {
            try {
                $pdo = db_connect();
                
                // بررسی تکراری نبودن نام کاربری
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->execute([$data['username']]);
                
                if ($stmt->fetchColumn() > 0) {
                    $error = 'این نام کاربری قبلاً ثبت شده است';
                } else {
                    // ثبت کاربر جدید
                    $stmt = $pdo->prepare("
                        INSERT INTO users (username, password, name, mobile, role, status, created_at)
                        VALUES (?, ?, ?, ?, 'user', 1, CURRENT_TIMESTAMP)
                    ");
                    
                    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                    
                    if ($stmt->execute([
                        $data['username'],
                        $hashed_password,
                        $data['name'],
                        $data['mobile']
                    ])) {
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
    } else {
        $errors = $validator->errors();
        $error = reset($errors)[0]; // نمایش اولین خطا
    }
}

// قالب صفحه
require_once 'templates/header.php';
?>

<div class="register-box">
    <div class="register-logo">
        <img src="<?= ASSETS_URL ?>/images/logo.png" alt="<?= SITE_TITLE ?>">
        <h4 class="mt-3">ثبت‌نام در <?= SITE_TITLE ?></h4>
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
    
    <form method="post" autocomplete="off" onsubmit="return validateForm(this)">
        <!-- توکن CSRF -->
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= CSRF::generate() ?>">
        
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

<?php require_once 'templates/footer.php'; ?>