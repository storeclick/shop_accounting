<?php
/**
 * سربرگ برنامه حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 * آخرین بروزرسانی: ۱۴۰۳/۰۱/۰۲
 */

// بررسی دسترسی
if (!defined('BASE_PATH')) {
    die('دسترسی غیرمجاز');
}

// بررسی لاگین
if (!is_logged_in()) {
    redirect('../../login.php');
}

// گرفتن اطلاعات کاربر از کش
$current_user = get_cache('user_' . $_SESSION['user_id']);
if (!$current_user) {
    $current_user = db_get_row("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    set_cache('user_' . $_SESSION['user_id'], $current_user);
}

// تعیین منوی فعال
$current_module = '';
if (strpos($_SERVER['PHP_SELF'], '/products/') !== false) {
    $current_module = 'products';
} elseif (strpos($_SERVER['PHP_SELF'], '/sales/') !== false) {
    $current_module = 'sales';
} elseif (strpos($_SERVER['PHP_SELF'], '/settings/') !== false) {
    $current_module = 'settings';
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?><?= SITE_TITLE ?></title>
    
    <!-- فونت ایران‌سنس -->
    <link rel="preload" href="<?= ASSETS_URL ?>/fonts/iran-sans/IRANSansWeb.woff2" as="font" type="font/woff2" crossorigin>
    
    <!-- استایل‌ها -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">
    
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= $page_css ?>">
    <?php endif; ?>
    <script src="<?= ASSETS_URL ?>/js/barcode-scanner.js" defer></script>
</head>
<body>

<!-- منوی موبایل -->
<div class="mobile-menu">
    <div class="mobile-menu-header">
        <div class="d-flex align-items-center justify-content-between">
            <a href="<?= BASE_URL ?>" class="text-dark text-decoration-none">
                <img src="<?= ASSETS_URL ?>/images/logo.png" alt="<?= SITE_TITLE ?>" height="32">
            </a>
            <button type="button" class="btn-close" onclick="toggleMobileMenu()"></button>
        </div>
    </div>
    <div class="mobile-menu-body">
        <div class="nav flex-column">
            <!-- داشبورد -->
            <a href="<?= BASE_URL ?>" class="nav-link <?= $current_module == '' ? 'active' : '' ?>">
                <i class="bi bi-house"></i>
                داشبورد
            </a>
            
            <!-- محصولات -->
            <a href="#products-collapse" class="nav-link <?= $current_module == 'products' ? 'active' : '' ?>" 
               data-bs-toggle="collapse" role="button">
                <i class="bi bi-box-seam"></i>
                محصولات
                <i class="bi bi-chevron-down float-start"></i>
            </a>
            <div class="collapse <?= $current_module == 'products' ? 'show' : '' ?>" id="products-collapse">
                <a href="<?= BASE_URL ?>/modules/products/list.php" class="dropdown-item">
                    <i class="bi bi-list-ul"></i>
                    لیست محصولات
                </a>
                <a href="<?= BASE_URL ?>/modules/products/add.php" class="dropdown-item">
                    <i class="bi bi-plus-lg"></i>
                    افزودن محصول
                </a>
                <a href="<?= BASE_URL ?>/modules/products/categories.php" class="dropdown-item">
                    <i class="bi bi-folder"></i>
                    دسته‌بندی‌ها
                </a>
                <a href="<?= BASE_URL ?>/modules/products/inventory.php" class="dropdown-item">
                    <i class="bi bi-box"></i>
                    انبارداری
                </a>
            </div>
            
            <!-- فروش -->
            <a href="#sales-collapse" class="nav-link <?= $current_module == 'sales' ? 'active' : '' ?>"
               data-bs-toggle="collapse" role="button">
                <i class="bi bi-cart"></i>
                فروش
                <i class="bi bi-chevron-down float-start"></i>
            </a>
            <div class="collapse <?= $current_module == 'sales' ? 'show' : '' ?>" id="sales-collapse">
                <a href="<?= BASE_URL ?>/modules/sales/quick.php" class="dropdown-item">
                    <i class="bi bi-lightning"></i>
                    فروش سریع
                </a>
                <a href="<?= BASE_URL ?>/modules/sales/invoice.php" class="dropdown-item">
                    <i class="bi bi-receipt"></i>
                    فاکتور فروش
                </a>
            </div>
            
            <!-- تنظیمات -->
            <?php if (is_admin()): ?>
                <a href="#settings-collapse" class="nav-link <?= $current_module == 'settings' ? 'active' : '' ?>"
                   data-bs-toggle="collapse" role="button">
                    <i class="bi bi-gear"></i>
                    تنظیمات
                    <i class="bi bi-chevron-down float-start"></i>
                </a>
                <div class="collapse <?= $current_module == 'settings' ? 'show' : '' ?>" id="settings-collapse">
                    <a href="<?= BASE_URL ?>/modules/settings/general.php" class="dropdown-item">
                        <i class="bi bi-sliders"></i>
                        تنظیمات عمومی
                    </a>
                    <a href="<?= BASE_URL ?>/modules/settings/backup.php" class="dropdown-item">
                        <i class="bi bi-download"></i>
                        پشتیبان‌گیری
                    </a>
                    <a href="<?= BASE_URL ?>/modules/settings/update.php" class="dropdown-item">
                        <i class="bi bi-arrow-repeat"></i>
                        بروزرسانی برنامه
                    </a>
                </div>
            <?php endif; ?>
            
            <hr>
            
            <!-- پروفایل -->
            <a href="<?= BASE_URL ?>/modules/profile/index.php" class="nav-link">
                <i class="bi bi-person"></i>
                پروفایل
            </a>
            
            <!-- خروج -->
            <a href="<?= BASE_URL ?>/logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right"></i>
                خروج
            </a>
        </div>
    </div>
</div>

<!-- پس‌زمینه تیره منوی موبایل -->
<div class="mobile-menu-overlay" onclick="toggleMobileMenu()"></div>

<!-- دکمه منوی موبایل -->
<button type="button" class="mobile-menu-toggle" onclick="toggleMobileMenu()">
    <i class="bi bi-list"></i>
</button>

<!-- نوار ناوبری -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
            <img src="<?= ASSETS_URL ?>/images/logo.png" alt="<?= SITE_TITLE ?>" height="32" class="me-2">
            <?= SITE_TITLE ?>
        </a>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- داشبورد -->
                <li class="nav-item">
                    <a class="nav-link <?= $current_module == '' ? 'active' : '' ?>" href="<?= BASE_URL ?>">
                        <i class="bi bi-house"></i>
                        داشبورد
                    </a>
                </li>
                
                <!-- محصولات -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $current_module == 'products' ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-box-seam"></i>
                        محصولات
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/modules/products/list.php">
                                <i class="bi bi-list-ul"></i>
                                لیست محصولات
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/modules/products/add.php">
                                <i class="bi bi-plus-lg"></i>
                                افزودن محصول
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/modules/products/categories.php">
                                <i class="bi bi-folder"></i>
                                دسته‌بندی‌ها
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/modules/products/inventory.php">
                                <i class="bi bi-box"></i>
                                انبارداری
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- فروش -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $current_module == 'sales' ? 'active' : '' ?>" 
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-cart"></i>
                        فروش
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/modules/sales/quick.php">
                                <i class="bi bi-lightning"></i>
                                فروش سریع
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/modules/sales/invoice.php">
                                <i class="bi bi-receipt"></i>
                                فاکتور فروش
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- تنظیمات -->
                <?php if (is_admin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= $current_module == 'settings' ? 'active' : '' ?>" 
                           href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i>
                            تنظیمات
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/modules/settings/general.php">
                                    <i class="bi bi-sliders"></i>
                                    تنظیمات عمومی
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/modules/settings/backup.php">
                                    <i class="bi bi-download"></i>
                                    پشتیبان‌گیری
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/modules/settings/update.php">
                                    <i class="bi bi-arrow-repeat"></i>
                                    بروزرسانی برنامه
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- منوی کاربر -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?= $_SESSION['user_name'] ?>
                        <?php if (isset($_SESSION['update_available'])): ?>
                            <span class="badge bg-danger">!</span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/modules/profile/index.php">
                                <i class="bi bi-person"></i>
                                پروفایل
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/modules/profile/password.php">
                                <i class="bi bi-key"></i>
                                تغییر رمز عبور
                            </a>
                        </li>
                        <?php if (isset($_SESSION['update_available'])): ?>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/modules/settings/update.php">
                                    <i class="bi bi-arrow-up-circle"></i>
                                    بروزرسانی جدید
                                    <small class="badge bg-danger">
                                        <?= $_SESSION['update_available']['version'] ?>
                                    </small>
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                خروج
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- محتوای اصلی -->
<main class="container-fluid py-4 mt-5">
    <?php if (isset($page_title)): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><?= $page_title ?></h1>
            
            <?php if (isset($page_actions)): ?>
                <div class="btn-group">
                    <?= $page_actions ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php
    // نمایش پیام‌ها
    $message = get_message();
    if ($message):
    ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show">
            <?= $message['text'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>