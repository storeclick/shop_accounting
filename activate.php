<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

$message = '';
$messageType = '';

if (isset($_GET['code'])) {
    $activation_code = $_GET['code'];
    
    try {
        // بررسی کد فعال‌سازی
        $stmt = $pdo->prepare("
            SELECT id, email 
            FROM users 
            WHERE activation_code = ? AND email_verified = 0
        ");
        $stmt->execute([$activation_code]);
        $user = $stmt->fetch();
        
        if ($user) {
            // فعال‌سازی حساب کاربری
            $stmt = $pdo->prepare("
                UPDATE users 
                SET email_verified = 1, 
                    activation_code = NULL,
                    is_active = 1
                WHERE id = ?
            ");
            $stmt->execute([$user['id']]);
            
            $message = 'حساب کاربری شما با موفقیت فعال شد. اکنون می‌توانید وارد شوید.';
            $messageType = 'success';
        } else {
            $message = 'کد فعال‌سازی نامعتبر است یا قبلاً استفاده شده است.';
            $messageType = 'danger';
        }
    } catch (PDOException $e) {
        $message = 'خطا در فعال‌سازی حساب: ' . $e->getMessage();
        $messageType = 'danger';
    }
} else {
    $message = 'کد فعال‌سازی یافت نشد.';
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فعال‌سازی حساب کاربری</title>
    <link rel="stylesheet" href="assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="text-center mb-4">
                    <a href="index.php">
                        <img src="assets/images/logo.png" alt="لوگو" height="60">
                    </a>
                </div>
                
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h4 class="mb-0">فعال‌سازی حساب کاربری</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-<?php echo $messageType; ?> text-center">
                            <?php echo $message; ?>
                        </div>
                        
                        <div class="text-center mt-4">
                            <?php if ($messageType === 'success'): ?>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    ورود به سیستم
                                </a>
                            <?php else: ?>
                                <a href="register.php" class="btn btn-primary">
                                    <i class="bi bi-person-plus me-1"></i>
                                    ثبت‌نام مجدد
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-house me-1"></i>
                        بازگشت به صفحه اصلی
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>