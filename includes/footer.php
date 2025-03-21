<?php
/**
 * پاورقی سیستم حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 * آخرین بروزرسانی: ۱۴۰۳/۰۱/۰۲
 */
?>
    </main>
    
    <!-- اسکریپت‌های ضروری -->
    <script src="<?= ASSETS_URL ?>/js/bootstrap.bundle.min.js"></script>
    <script src="<?= ASSETS_URL ?>/js/functions.js"></script>
    
    <!-- اسکریپت‌های اضافی -->
    <?php if (isset($need_chart)): ?>
        <script src="<?= ASSETS_URL ?>/js/chart.js"></script>
    <?php endif; ?>
    
    <?php if (isset($page_js)): ?>
        <script src="<?= ASSETS_URL ?>/js/<?= $page_js ?>"></script>
    <?php endif; ?>
    
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
                    <a href="<?= BASE_URL ?>/modules/products/add.php" class="dropdown-item">
                        <i class="bi bi-plus-lg"></i>
                        افزودن محصول
                    </a>
                    <a href="<?= BASE_URL ?>/modules/products/list.php" class="dropdown-item">
                        <i class="bi bi-list-ul"></i>
                        لیست محصولات
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
            </div>
        </div>
    </div>

    <!-- پس‌زمینه تیره منوی موبایل -->
    <div class="mobile-menu-overlay" onclick="toggleMobileMenu()"></div>

    <!-- دکمه منوی موبایل -->
    <button type="button" class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- اسکریپت منوی موبایل -->
    <script>
    function toggleMobileMenu() {
        document.querySelector('.mobile-menu').classList.toggle('active');
        document.querySelector('.mobile-menu-overlay').classList.toggle('active');
        document.body.classList.toggle('mobile-menu-open');
    }
    
    // بستن منو با کلیک خارج از منو
    document.addEventListener('click', function(e) {
        if (document.querySelector('.mobile-menu.active')) {
            if (!e.target.closest('.mobile-menu') && !e.target.closest('.mobile-menu-toggle')) {
                toggleMobileMenu();
            }
        }
    });
    </script>
    
    <?php if (defined('DEBUG') && DEBUG && isset($GLOBALS['queries'])): ?>
    <!-- اطلاعات دیباگ -->
    <div class="debug-info">
        <details>
            <summary>اطلاعات دیباگ</summary>
            <div class="debug-content">
                <p>زمان اجرا: <?= number_format((microtime(true) - START_TIME) * 1000, 2) ?> میلی‌ثانیه</p>
                <p>تعداد کوئری: <?= $GLOBALS['query_count'] ?? 0 ?></p>
                <?php if (!empty($GLOBALS['queries'])): ?>
                    <ul>
                        <?php foreach ($GLOBALS['queries'] as $query): ?>
                            <li>
                                <pre><?= $query['query'] ?></pre>
                                <small>
                                    پارامترها: <?= json_encode($query['params']) ?>
                                    (<?= number_format($query['time'], 2) ?> میلی‌ثانیه)
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </details>
    </div>
    <?php endif; ?>
    
</body>
</html>