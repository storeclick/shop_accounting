<?php
/**
 * داشبورد اصلی برنامه حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 * آخرین بروزرسانی: ۱۴۰۳/۰۱/۰۲
 */

require_once 'config/config.php';
check_access();

$page_title = 'داشبورد';

// فعال کردن Chart.js
$need_chart = true;

// بررسی وجود جداول مورد نیاز
$tables_exist = true;
$required_tables = ['users', 'products', 'customers', 'orders', 'order_items'];

foreach ($required_tables as $table) {
    $result = db_get_row("SHOW TABLES LIKE ?", [$table]);
    if (!$result) {
        $tables_exist = false;
        break;
    }
}

// اگر جداول وجود نداشت، اجرای فایل SQL
if (!$tables_exist) {
    try {
        // مسیر فایل SQL
        $sql_file = __DIR__ . '/database/tables.sql';
        
        // بررسی وجود فایل
        if (!file_exists($sql_file)) {
            throw new Exception('فایل tables.sql پیدا نشد!');
        }
        
        // خواندن و اجرای فایل SQL
        $sql = file_get_contents($sql_file);
        if (!empty($sql)) {
            $pdo->exec($sql);
            show_message('ساختار پایگاه داده با موفقیت ایجاد شد.', 'success');
        } else {
            throw new Exception('فایل SQL خالی است!');
        }
    } catch (Exception $e) {
        error_log('خطا در ایجاد جداول: ' . $e->getMessage());
        show_message('خطا در ایجاد ساختار پایگاه داده. لطفاً با پشتیبانی تماس بگیرید.', 'danger');
    }
}

// آمار فروش امروز - با مقادیر پیش‌فرض
$today_stats = db_get_row("
    SELECT 
        COUNT(*) as orders_count,
        COALESCE(SUM(total_amount), 0) as total_sales
    FROM orders 
    WHERE DATE(created_at) = CURDATE()
") ?: ['orders_count' => 0, 'total_sales' => 0];

// آمار کل - با مقادیر پیش‌فرض
$total_stats = [
    'products_count' => 0,
    'customers_count' => 0,
    'orders_count' => 0,
    'total_sales' => 0
];

// شمارش محصولات
$products_count = db_get_row("SELECT COUNT(*) as count FROM products");
$total_stats['products_count'] = $products_count ? $products_count['count'] : 0;

// شمارش مشتریان
$customers_count = db_get_row("SELECT COUNT(*) as count FROM customers");
$total_stats['customers_count'] = $customers_count ? $customers_count['count'] : 0;

// شمارش فاکتورها و مجموع فروش
$orders_stats = db_get_row("
    SELECT 
        COUNT(*) as count,
        COALESCE(SUM(total_amount), 0) as total
    FROM orders
");
if ($orders_stats) {
    $total_stats['orders_count'] = $orders_stats['count'];
    $total_stats['total_sales'] = $orders_stats['total'];
}

// محصولات کم موجود
$low_stock_products = db_get_rows("
    SELECT id, name, code, stock, min_stock
    FROM products 
    WHERE stock <= min_stock
    ORDER BY stock ASC
    LIMIT 5
") ?: [];

// آخرین فروش‌ها
$recent_sales = db_get_rows("
    SELECT o.*, c.name as customer_name
    FROM orders o
    LEFT JOIN customers c ON c.id = o.customer_id
    ORDER BY o.created_at DESC
    LIMIT 5
") ?: [];

// نمودار فروش ۷ روز گذشته
$sales_chart = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sales = db_get_row("
        SELECT 
            COUNT(*) as orders_count,
            COALESCE(SUM(total_amount), 0) as total_amount
        FROM orders 
        WHERE DATE(created_at) = ?
    ", [$date]) ?: ['orders_count' => 0, 'total_amount' => 0];
    
    $sales_chart[] = [
        'date' => jdate('d F', strtotime($date)),
        'orders' => (int)$sales['orders_count'],
        'amount' => (float)$sales['total_amount']
    ];
}

require_once 'includes/header.php';
?>

<div class="row g-4">
    <!-- آمار کلی -->
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="display-6 text-primary mb-3">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h5 class="card-title mb-2">محصولات</h5>
                <p class="h3 mb-0 persian-number">
                    <?= number_format($total_stats['products_count']) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="display-6 text-success mb-3">
                    <i class="bi bi-people"></i>
                </div>
                <h5 class="card-title mb-2">مشتریان</h5>
                <p class="h3 mb-0 persian-number">
                    <?= number_format($total_stats['customers_count']) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="display-6 text-warning mb-3">
                    <i class="bi bi-receipt"></i>
                </div>
                <h5 class="card-title mb-2">فاکتورها</h5>
                <p class="h3 mb-0 persian-number">
                    <?= number_format($total_stats['orders_count']) ?>
                </p>
            </div>
        </div>
    </div>
    
    <div class="col-sm-6 col-lg-3">
        <div class="card text-center h-100">
            <div class="card-body">
                <div class="display-6 text-danger mb-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <h5 class="card-title mb-2">کل فروش</h5>
                <p class="h3 mb-0 persian-number">
                    <?= number_format($total_stats['total_sales']) ?>
                    <small>تومان</small>
                </p>
            </div>
        </div>
    </div>
    
    <!-- نمودار فروش -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-4">نمودار فروش ۷ روز گذشته</h5>
                <canvas id="salesChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- آمار امروز -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-4">آمار فروش امروز</h5>
                
                <div class="mb-4">
                    <small class="text-muted d-block mb-2">تعداد فاکتور</small>
                    <p class="h4 mb-0 persian-number">
                        <?= number_format($today_stats['orders_count']) ?>
                    </p>
                </div>
                
                <div>
                    <small class="text-muted d-block mb-2">مبلغ کل</small>
                    <p class="h4 mb-0 persian-number">
                        <?= number_format($today_stats['total_sales']) ?>
                        <small>تومان</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- محصولات کم موجود -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-4">محصولات کم موجود</h5>
                
                <?php if ($low_stock_products): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>نام محصول</th>
                                    <th>کد</th>
                                    <th>موجودی</th>
                                    <th>حداقل</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock_products as $product): ?>
                                    <tr>
                                        <td><?= $product['name'] ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= $product['code'] ?>
                                            </span>
                                        </td>
                                        <td class="persian-number <?= $product['stock'] == 0 ? 'text-danger' : 'text-warning' ?>">
                                            <?= number_format($product['stock']) ?>
                                        </td>
                                        <td class="persian-number text-muted">
                                            <?= number_format($product['min_stock']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle display-6 d-block mb-3"></i>
                        موجودی تمام محصولات کافی است
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- آخرین فروش‌ها -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-4">آخرین فروش‌ها</h5>
                
                <?php if ($recent_sales): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>شماره</th>
                                    <th>مشتری</th>
                                    <th>مبلغ</th>
                                    <th>تاریخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_sales as $sale): ?>
                                    <tr>
                                        <td>
                                            <a href="modules/sales/invoice.php?id=<?= $sale['id'] ?>" 
                                               class="text-decoration-none">
                                                #<?= $sale['id'] ?>
                                            </a>
                                        </td>
                                        <td><?= $sale['customer_name'] ?></td>
                                        <td class="persian-number">
                                            <?= number_format($sale['total_amount']) ?>
                                            <small class="text-muted">تومان</small>
                                        </td>
                                        <td>
                                            <small class="text-muted d-block">
                                                <?= jdate('d F Y', strtotime($sale['created_at'])) ?>
                                            </small>
                                            <small class="text-muted">
                                                <?= jdate('H:i', strtotime($sale['created_at'])) ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-receipt display-6 d-block mb-3"></i>
                        هنوز فروشی ثبت نشده است
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- اسکریپت نمودار -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // نمودار فروش
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($sales_chart, 'date')) ?>,
            datasets: [
                {
                    label: 'مبلغ فروش',
                    data: <?= json_encode(array_column($sales_chart, 'amount')) ?>,
                    borderColor: '#0d6efd',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: '#0d6efd20'
                },
                {
                    label: 'تعداد فاکتور',
                    data: <?= json_encode(array_column($sales_chart, 'orders')) ?>,
                    borderColor: '#198754',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: '#19875420'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            family: 'IRANSans'
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            family: 'IRANSans'
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            family: 'IRANSans'
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>