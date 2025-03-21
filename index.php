<?php
/**
 * صفحه اصلی برنامه حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

// تعریف مسیر اصلی برنامه
define('BASE_PATH', __DIR__);

// لود کردن تنظیمات
require_once 'config/config.php';
check_access();

// عنوان صفحه
$page_title = 'داشبورد';

require_once 'includes/header.php';

// دریافت آمار کلی
$stats = [
    'total_products' => db_get_var("SELECT COUNT(*) FROM products"),
    'low_stock' => db_get_var("SELECT COUNT(*) FROM products WHERE stock <= reorder_point"),
    'total_sales_today' => db_get_var("
        SELECT COUNT(*) 
        FROM sales 
        WHERE DATE(created_at) = CURDATE() AND status = 'completed'
    "),
    'total_amount_today' => db_get_var("
        SELECT COALESCE(SUM(total_amount), 0) 
        FROM sales 
        WHERE DATE(created_at) = CURDATE() AND status = 'completed'
    ")
];
?>

<div class="row g-4 mb-4">
    <!-- تعداد محصولات -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-2">تعداد محصولات</h6>
                        <h3 class="mb-0"><?= number_format($stats['total_products']) ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer py-2">
                <a href="<?= BASE_URL ?>/modules/products/list.php" class="text-decoration-none small">
                    مشاهده محصولات
                    <i class="bi bi-chevron-left align-middle"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- محصولات کم موجود -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-2">محصولات کم موجود</h6>
                        <h3 class="mb-0 text-warning"><?= number_format($stats['low_stock']) ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer py-2">
                <a href="<?= BASE_URL ?>/modules/products/inventory.php" class="text-decoration-none small">
                    مدیریت انبار
                    <i class="bi bi-chevron-left align-middle"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- تعداد فروش امروز -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-2">تعداد فروش امروز</h6>
                        <h3 class="mb-0 text-success"><?= number_format($stats['total_sales_today']) ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="bi bi-cart-check fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer py-2">
                <a href="<?= BASE_URL ?>/modules/sales/list.php" class="text-decoration-none small">
                    مشاهده فروش‌ها
                    <i class="bi bi-chevron-left align-middle"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- مبلغ فروش امروز -->
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="card-title text-muted mb-2">مبلغ فروش امروز</h6>
                        <h3 class="mb-0 text-primary">
                            <?= number_format($stats['total_amount_today']) ?>
                            <small class="fs-6">تومان</small>
                        </h3>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-currency-dollar fs-1"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer py-2">
                <a href="<?= BASE_URL ?>/modules/reports/sales.php" class="text-decoration-none small">
                    گزارش فروش
                    <i class="bi bi-chevron-left align-middle"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- فروش سریع -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">فروش سریع</h5>
                <a href="<?= BASE_URL ?>/modules/sales/quick.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg"></i>
                    فروش جدید
                </a>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/modules/sales/quick.php" method="post" class="quick-sale-form">
                    <div class="input-group mb-3">
                        <input type="text" 
                               name="barcode" 
                               class="form-control" 
                               placeholder="بارکد محصول را اسکن کنید..."
                               autofocus>
                        <button type="button" class="btn btn-secondary" onclick="scanBarcode()">
                            <i class="bi bi-upc-scan"></i>
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cart-plus"></i>
                            افزودن
                        </button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>کد</th>
                                <th>نام محصول</th>
                                <th>قیمت واحد</th>
                                <th width="150">تعداد</th>
                                <th>قیمت کل</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    محصولی به سبد فروش اضافه نشده است.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- محصولات کم موجود -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">محصولات کم موجود</h5>
            </div>
            <div class="table-responsive" style="max-height: 300px">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>نام محصول</th>
                            <th class="text-center">موجودی</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $low_stock_products = db_get_rows("
                            SELECT name, stock, unit, reorder_point
                            FROM products 
                            WHERE stock <= reorder_point
                            ORDER BY stock ASC
                            LIMIT 10
                        ");
                        
                        if (empty($low_stock_products)):
                        ?>
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">
                                    همه محصولات موجودی کافی دارند.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($low_stock_products as $product): ?>
                                <tr>
                                    <td><?= $product['name'] ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $product['stock'] <= 0 ? 'danger' : 'warning' ?>">
                                            <?= number_format($product['stock']) ?>
                                            <?= $product['unit'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';