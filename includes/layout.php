<?php
if (!defined('ADMIN_ACCESS')) {
    die('دسترسی مستقیم به این فایل مجاز نیست.');
}

// چک کردن دسترسی کاربر
if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// بررسی لاگین بودن کاربر
check_user_access();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? SITE_NAME; ?></title>
    
    <!-- فایل‌های CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <!-- نوار بالای صفحه -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <!-- دکمه همبرگر موبایل -->
            <button class="btn btn-link d-lg-none text-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <i class="bi bi-list fs-4"></i>
            </button>
            
            <!-- لوگو -->
            <a class="navbar-brand mx-lg-3" href="dashboard.php">
                <img src="assets/images/logo.png" alt="<?php echo SITE_NAME; ?>" height="40">
            </a>

            <!-- منوی سمت راست -->
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i>
                            داشبورد
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="modules/products/quick_add.php">
                            <i class="bi bi-plus-circle"></i>
                            افزودن سریع محصول
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="modules/sales/quick.php">
                            <i class="bi bi-cart-plus"></i>
                            فروش سریع
                        </a>
                    </li>
                </ul>
            </div>

            <!-- منوی کاربر -->
            <div class="dropdown">
                <button class="btn btn-link text-white dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-lg-inline"><?php echo $_SESSION['full_name']; ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="profile.php">
                            <i class="bi bi-person"></i>
                            پروفایل
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            خروج
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- منوی کناری -->
    <div class="offcanvas-lg offcanvas-start sidebar bg-dark text-white" id="sidebar">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">منوی اصلی</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="navbar-dark">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i>
                            داشبورد
                        </a>
                    </li>
                    
                    <!-- بخش محصولات -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'products' ? 'active' : ''; ?> d-flex justify-content-between" 
                           data-bs-toggle="collapse" 
                           href="#productsMenu">
                            <span>
                                <i class="bi bi-box-seam"></i>
                                محصولات
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <div class="collapse <?php echo $active_menu == 'products' ? 'show' : ''; ?>" id="productsMenu">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/products/add.php">
                                        <i class="bi bi-plus-lg"></i>
                                        افزودن محصول
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/products/list.php">
                                        <i class="bi bi-list-ul"></i>
                                        لیست محصولات
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/products/categories.php">
                                        <i class="bi bi-diagram-2"></i>
                                        دسته‌بندی‌ها
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <!-- بخش انبارداری -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'inventory' ? 'active' : ''; ?> d-flex justify-content-between" 
                           data-bs-toggle="collapse" 
                           href="#inventoryMenu">
                            <span>
                                <i class="bi bi-building"></i>
                                انبارداری
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <div class="collapse <?php echo $active_menu == 'inventory' ? 'show' : ''; ?>" id="inventoryMenu">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/inventory/adjust.php">
                                        <i class="bi bi-plus-slash-minus"></i>
                                        تنظیم موجودی
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/inventory/transactions.php">
                                        <i class="bi bi-arrow-left-right"></i>
                                        گردش موجودی
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <!-- بخش فروش -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'sales' ? 'active' : ''; ?> d-flex justify-content-between" 
                           data-bs-toggle="collapse" 
                           href="#salesMenu">
                            <span>
                                <i class="bi bi-cart3"></i>
                                فروش
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <div class="collapse <?php echo $active_menu == 'sales' ? 'show' : ''; ?>" id="salesMenu">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/sales/quick.php">
                                        <i class="bi bi-cart-plus"></i>
                                        فروش سریع
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/sales/invoice.php">
                                        <i class="bi bi-receipt"></i>
                                        صدور فاکتور
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/sales/list.php">
                                        <i class="bi bi-list-ul"></i>
                                        لیست فروش‌ها
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <!-- بخش گزارشات -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'reports' ? 'active' : ''; ?>" href="modules/reports/">
                            <i class="bi bi-graph-up"></i>
                            گزارشات
                        </a>
                    </li>
                    
                    <!-- خط جداکننده -->
                    <li><hr class="navbar-divider"></li>
                    
                    <!-- بخش تنظیمات -->
                    <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $active_menu == 'settings' ? 'active' : ''; ?> d-flex justify-content-between" 
                           data-bs-toggle="collapse" 
                           href="#settingsMenu">
                            <span>
                                <i class="bi bi-gear"></i>
                                تنظیمات
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </a>
                        <div class="collapse <?php echo $active_menu == 'settings' ? 'show' : ''; ?>" id="settingsMenu">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/settings/general.php">
                                        <i class="bi bi-sliders"></i>
                                        تنظیمات کلی
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/settings/users.php">
                                        <i class="bi bi-people"></i>
                                        کاربران
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/settings/backup.php">
                                        <i class="bi bi-download"></i>
                                        پشتیبان‌گیری
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="modules/settings/update.php">
                                        <i class="bi bi-cloud-arrow-down"></i>
                                        بروزرسانی
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <!-- محتوای اصلی -->
    <main class="content">
        <div class="container-fluid p-4">
            <?php if (isset($page_title)): ?>
                <h1 class="h3 mb-4"><?php echo $page_title; ?></h1>
            <?php endif; ?>
            
            <?php echo $content ?? ''; ?>
        </div>
    </main>

    <!-- فایل‌های JavaScript -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/admin.js"></script>
    <?php if (isset($scripts)) echo $scripts; ?>
</body>
</html>