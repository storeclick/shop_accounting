<?php
/**
 * فایل نصب سیستم حسابداری فروشگاه
 * نویسنده: cofeclick1
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

// تنظیم charset برای نمایش درست حروف فارسی
header('Content-Type: text/html; charset=utf-8');

// تنظیم منطقه زمانی
date_default_timezone_set('Asia/Tehran');

// فعال کردن نمایش خطاها
error_reporting(E_ALL);
ini_set('display_errors', 1);

// شروع یا ادامه session
session_start();

// بررسی وجود فایل config
if (file_exists('config/config.php') && !isset($_SESSION['install_step'])) {
    die('سیستم قبلاً نصب شده است. برای نصب مجدد، فایل config/config.php را حذف کنید.');
}

$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// اطلاعات پیش‌فرض
$config = [
    'db_host' => 'localhost',
    'db_name' => 'shop_accounting',
    'db_user' => 'root',
    'db_pass' => '',
    'admin_username' => 'akrdim', // نام کاربری پیش‌فرض مدیر
    'admin_password' => '6678232', // رمز عبور پیش‌فرض مدیر
    'admin_name' => 'مدیر سیستم',
    'admin_mobile' => '',
    'site_title' => 'سیستم حسابداری فروشگاه',
    'software_version' => '1.0.0'
];

// دریافت اطلاعات از سشن
if (isset($_SESSION['install_config'])) {
    $config = array_merge($config, $_SESSION['install_config']);
}

// بررسی پیش‌نیازها
$requirements = [
    'php_version' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'mbstring' => extension_loaded('mbstring'),
    'gd' => extension_loaded('gd'),
    'curl' => extension_loaded('curl'),
    'config_writable' => is_writable('config') || (!file_exists('config') && is_writable('.')),
    'uploads_writable' => is_writable('uploads') || (!file_exists('uploads') && is_writable('.'))
];

$all_requirements_met = !in_array(false, $requirements);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // مرحله ۱: بررسی پیش‌نیازها
    if ($step == 1 && $all_requirements_met) {
        header('Location: install.php?step=2');
        exit;
    }
    
    // مرحله ۲: بررسی اطلاعات دیتابیس
    elseif ($step == 2) {
        $config['db_host'] = $_POST['db_host'];
        $config['db_name'] = $_POST['db_name'];
        $config['db_user'] = $_POST['db_user'];
        $config['db_pass'] = $_POST['db_pass'];
        
        try {
            // اتصال به سرور MySQL
            $pdo = new PDO(
                "mysql:host={$config['db_host']};charset=utf8mb4",
                $config['db_user'],
                $config['db_pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // ایجاد دیتابیس اگر وجود نداشت
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db_name']}` 
                       CHARACTER SET utf8mb4 COLLATE utf8mb4_persian_ci");
            
            // انتخاب دیتابیس
            $pdo->exec("USE `{$config['db_name']}`");
            
            // خواندن و اجرای فایل SQL
            $sql = file_get_contents('database.sql');
            if ($sql === false) {
                throw new Exception('خطا در خواندن فایل database.sql');
            }
            
            // اجرای اسکریپت SQL
            $pdo->exec($sql);
            
            // ذخیره اطلاعات در سشن
            $_SESSION['install_config'] = $config;
            $_SESSION['install_step'] = 2;
            
            // انتقال به مرحله بعد
            header('Location: install.php?step=3');
            exit;
            
        } catch(PDOException $e) {
            $error = 'خطا در اتصال به دیتابیس: ' . $e->getMessage();
        } catch(Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // مرحله ۳: تنظیم اطلاعات مدیر و سیستم
    elseif ($step == 3 && isset($_SESSION['install_step'])) {
        $config['site_title'] = $_POST['site_title'];
        $config['admin_name'] = $_POST['admin_name'];
        $config['admin_mobile'] = $_POST['admin_mobile'];
        
        try {
            $pdo = new PDO(
                "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
                $config['db_user'],
                $config['db_pass']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // بروزرسانی تنظیمات سایت
            $stmt = $pdo->prepare("
                UPDATE settings SET value = ? WHERE `key` = 'site_title'
            ");
            $stmt->execute([$config['site_title']]);
            
            // بروزرسانی اطلاعات مدیر
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, mobile = ?
                WHERE username = ?
            ");
            $stmt->execute([
                $config['admin_name'],
                $config['admin_mobile'],
                $config['admin_username']
            ]);
            
            // ایجاد فایل تنظیمات
            $config_content = "<?php
/**
 * تنظیمات سیستم حسابداری فروشگاه
 * تاریخ ایجاد: " . date('Y-m-d H:i:s') . "
 */

// تنظیمات پایه
define('BASE_PATH', dirname(__FILE__));
define('BASE_URL', 'http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "');

// تنظیمات دیتابیس
define('DB_HOST', '{$config['db_host']}');
define('DB_NAME', '{$config['db_name']}');
define('DB_USER', '{$config['db_user']}');
define('DB_PASS', '{$config['db_pass']}');

// تنظیمات سایت
define('SITE_TITLE', '{$config['site_title']}');
define('APP_VERSION', '{$config['software_version']}');

// تنظیمات مسیرها
define('MODULES_PATH', BASE_PATH . '/modules');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');

// تنظیمات زمانی
date_default_timezone_set('Asia/Tehran');

// تنظیمات امنیتی
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_NAME', 'shop_accounting');
define('SESSION_LIFETIME', 7200);
define('REMEMBER_COOKIE_NAME', 'remember_token');
define('REMEMBER_COOKIE_LIFETIME', 2592000);

// تنظیمات آپلود
define('MAX_UPLOAD_SIZE', 5242880);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// تنظیمات فروش
define('VAT_RATE', 9);
define('INVOICE_PREFIX', 'INV-');
define('INVOICE_START_NUMBER', 1000);

// تنظیمات انبار
define('LOW_STOCK_WARNING', 5);
define('NEGATIVE_STOCK_ALLOWED', false);

// تنظیمات بروزرسانی
define('UPDATE_CHECK_INTERVAL', 86400);
define('UPDATE_REPOSITORY', 'cofeclick1/shop_accounting');
define('UPDATE_BRANCH', 'main');

// شروع سشن
session_name(SESSION_NAME);
session_start();

// لود کردن توابع
require_once INCLUDES_PATH . '/functions.php';
";
            
            // ایجاد پوشه‌های مورد نیاز
            $directories = [
                'config',
                'uploads',
                'uploads/products',
                'uploads/invoices',
                'uploads/temp'
            ];
            
            foreach ($directories as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
            
            // ذخیره فایل تنظیمات
            file_put_contents('config/config.php', $config_content);
            
            // ایجاد فایل .htaccess برای امنیت
            $htaccess = "Options -Indexes\n\n";
            $htaccess .= "<Files .htaccess>\norder allow,deny\ndeny from all\n</Files>\n\n";
            $htaccess .= "<Files config.php>\norder allow,deny\ndeny from all\n</Files>";
            file_put_contents('config/.htaccess', $htaccess);
            
            // پاک کردن سشن نصب
            unset($_SESSION['install_config']);
            unset($_SESSION['install_step']);
            
            // انتقال به صفحه پایان نصب
            header('Location: install.php?step=4');
            exit;
            
        } catch(PDOException $e) {
            $error = 'خطا در تنظیم اطلاعات: ' . $e->getMessage();
        } catch(Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نصب سیستم حسابداری فروشگاه</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            line-height: 1.6;
        }
        
        .install-box {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 600px;
            width: 100%;
            padding: 30px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .steps {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #ddd;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 20px;
            position: relative;
            z-index: 1;
            transition: all 0.3s;
        }
        
        .steps:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60%;
            height: 2px;
            background: #ddd;
        }
        
        .step.active {
            background: #764ba2;
            color: #fff;
            transform: scale(1.2);
        }
        
        .step.done {
            background: #4CAF50;
            color: #fff;
        }
        
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        
        input[type="text"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            font-size: inherit;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="tel"]:focus {
            border-color: #764ba2;
            outline: none;
            box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.1);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        
        .requirements {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .requirement {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .requirement:last-child {
            border-bottom: none;
        }
        
        .requirement-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            color: #fff;
            font-weight: bold;
        }
        
        .requirement-status.ok {
            background: #4CAF50;
        }
        
        .requirement-status.error {
            background: #f44336;
        }
        
        button {
            background: #764ba2;
            color: #fff;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            font-size: inherit;
            width: 100%;
            transition: all 0.3s;
        }
        
        button:hover {
            background: #667eea;
            transform: translateY(-1px);
        }
        
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .success-message {
            text-align: center;
        }
        
        .success-message .icon {
            font-size: 48px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            display: inline-block;
            background: #764ba2;
            color: #fff;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            transition: all 0.3s;
            text-align: center;
            margin-top: 20px;
        }
        
        .btn-primary:hover {
            background: #667eea;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="install-box">
    <div class="logo">
        <h1>نصب سیستم حسابداری</h1>
    </div>
    
    <div class="steps">
        <div class="step <?= $step >= 1 ? 'done' : '' ?> <?= $step == 1 ? 'active' : '' ?>">۱</div>
        <div class="step <?= $step >= 2 ? 'done' : '' ?> <?= $step == 2 ? 'active' : '' ?>">۲</div>
        <div class="step <?= $step >= 3 ? 'done' : '' ?> <?= $step == 3 ? 'active' : '' ?>">۳</div>
        <div class="step <?= $step >= 4 ? 'done' : '' ?> <?= $step == 4 ? 'active' : '' ?>">۴</div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if ($step == 1): ?>
        <h2>بررسی پیش‌نیازها</h2>
        <div class="requirements">
            <div class="requirement">
                <span>نسخه PHP (حداقل 7.4)</span>
                <span class="requirement-status <?= $requirements['php_version'] ? 'ok' : 'error' ?>">
                    <?= $requirements['php_version'] ? '✓' : '⨯' ?>
                </span>
            </div>
            
            <div class="requirement">
                <span>افزونه PDO MySQL</span>
                <span class="requirement-status <?= $requirements['pdo_mysql'] ? 'ok' : 'error' ?>">
                    <?= $requirements['pdo_mysql'] ? '✓' : '⨯' ?>
                </span>
            </div>
            
            <div class="requirement">
                <span>افزونه mbstring</span>
                <span class="requirement-status <?= $requirements['mbstring'] ? 'ok' : 'error' ?>">
                    <?= $requirements['mbstring'] ? '✓' : '⨯' ?>
                </span>
            </div>
            
            <div class="requirement">
                <span>افزونه GD</span>
                <span class="requirement-status <?= $requirements['gd'] ? 'ok' : 'error' ?>">
                    <?= $requirements['gd'] ? '✓' : '⨯' ?>
                </span>
            </div>
            
            <div class="requirement">
                <span>افزونه cURL</span>
                <span class="requirement-status <?= $requirements['curl'] ? 'ok' : 'error' ?>">
                    <?= $requirements['curl'] ? '✓' : '⨯' ?>
                </span>
            </div>
            
            <div class="requirement">
                <span>دسترسی نوشتن - پوشه config</span>
                <span class="requirement-status <?= $requirements['config_writable'] ? 'ok' : 'error' ?>">
                    <?= $requirements['config_writable'] ? '✓' : '⨯' ?>
                </span>
            </div>
            
            <div class="requirement">
                <span>دسترسی نوشتن - پوشه uploads</span>
                <span class="requirement-status <?= $requirements['uploads_writable'] ? 'ok' : 'error' ?>">
                    <?= $requirements['uploads_writable'] ? '✓' : '⨯' ?>
                </span>
            </div>
        </div>
        
        <form method="post">
            <button type="submit" <?= $all_requirements_met ? '' : 'disabled' ?>>
                شروع نصب
            </button>
        </form>
        
    <?php elseif ($step == 2): ?>
        <h2>تنظیمات دیتابیس</h2>
        <form method="post">
            <div class="form-group">
                <label>آدرس سرور دیتابیس</label>
                <input type="text" name="db_host" value="<?= $config['db_host'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>نام دیتابیس</label>
                <input type="text" name="db_name" value="<?= $config['db_name'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>نام کاربری دیتابیس</label>
                <input type="text" name="db_user" value="<?= $config['db_user'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>رمز عبور دیتابیس</label>
                <input type="password" name="db_pass" value="<?= $config['db_pass'] ?>">
            </div>
            
            <button type="submit">ادامه نصب</button>
        </form>
        
    <?php elseif ($step == 3): ?>
        <h2>تنظیمات سیستم</h2>
        <form method="post">
            <div class="form-group">
                <label>عنوان سایت</label>
                <input type="text" name="site_title" value="<?= $config['site_title'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>نام و نام خانوادگی مدیر</label>
                <input type="text" name="admin_name" value="<?= $config['admin_name'] ?>" required>
            </div>
            
            <div class="form-group">
                <label>شماره موبایل مدیر</label>
                <input type="tel" name="admin_mobile" value="<?= $config['admin_mobile'] ?>" 
                       pattern="09[0-9]{9}" placeholder="مثال: 09123456789">
            </div>
            
            <div class="alert alert-success">
                نام کاربری: <?= $config['admin_username'] ?><br>
                رمز عبور: <?= $config['admin_password'] ?>
            </div>
            
            <button type="submit">پایان نصب</button>
        </form>
        
    <?php elseif ($step == 4): ?>
        <div class="success-message">
            <div class="icon">✓</div>
            <h2>نصب با موفقیت انجام شد</h2>
            <p>سیستم حسابداری با موفقیت نصب شد. اکنون می‌توانید با اطلاعات زیر وارد سیستم شوید:</p>
            
            <div class="alert alert-success">
                نام کاربری: <?= $config['admin_username'] ?><br>
                رمز عبور: <?= $config['admin_password'] ?>
            </div>
            
            <p>برای امنیت بیشتر، لطفاً موارد زیر را انجام دهید:</p>
            <ol style="text-align: right">
                <li>فایل install.php را حذف کنید</li>
                <li>دسترسی‌های پوشه config را محدود کنید</li>
                <li>پس از اولین ورود، رمز عبور خود را تغییر دهید</li>
            </ol>
            
            <a href="login.php" class="btn-primary">ورود به سیستم</a>
        </div>
    <?php endif; ?>
</div>

<script>
// حذف پیام‌های خطا بعد از 5 ثانیه
document.querySelectorAll('.alert-danger').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = 0;
        setTimeout(() => alert.remove(), 500);
    }, 5000);
});
</script>

</body>
</html>