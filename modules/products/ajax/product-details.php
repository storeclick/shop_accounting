<?php
/**
 * نمایش جزئیات محصول
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

require_once '../../../config/config.php';
check_access();

if (!isset($_GET['id'])) {
    die('شناسه محصول نامعتبر است.');
}

$product_id = (int)$_GET['id'];

// دریافت اطلاعات محصول
$product = db_get_row("
    SELECT p.*, 
           c.name as category_name,
           u.name as created_by_name,
           (
               SELECT COUNT(*) 
               FROM sales_items si 
               JOIN sales s ON si.sale_id = s.id 
               WHERE si.product_id = p.id AND s.status = 'completed'
           ) as total_sales,
           (
               SELECT SUM(quantity) 
               FROM sales_items si 
               JOIN sales s ON si.sale_id = s.id 
               WHERE si.product_id = p.id AND s.status = 'completed'
           ) as total_quantity_sold
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN users u ON p.created_by = u.id
    WHERE p.id = ?
", [$product_id]);

if (!$product) {
    die('محصول مورد نظر یافت نشد.');
}

// دریافت تصاویر گالری
$gallery = db_get_rows("
    SELECT * FROM product_images 
    WHERE product_id = ? 
    ORDER BY sort_order ASC
", [$product_id]);

// دریافت تراکنش‌های اخیر
$transactions = db_get_rows("
    SELECT t.*, u.name as user_name
    FROM inventory_transactions t
    LEFT JOIN users u ON t.created_by = u.id
    WHERE t.product_id = ?
    ORDER BY t.created_at DESC
    LIMIT 5
", [$product_id]);

// دریافت تغییرات قیمت
$price_changes = db_get_rows("
    SELECT pc.*, u.name as user_name
    FROM price_changes pc
    LEFT JOIN users u ON pc.created_by = u.id
    WHERE pc.product_id = ?
    ORDER BY pc.created_at DESC
    LIMIT 5
", [$product_id]);

// محاسبه میانگین فروش
$sales_avg = db_get_row("
    SELECT 
        ROUND(AVG(CASE WHEN s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN si.quantity ELSE NULL END)) as monthly_avg,
        ROUND(AVG(CASE WHEN s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN si.quantity ELSE NULL END)) as weekly_avg,
        ROUND(AVG(CASE WHEN s.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN si.quantity ELSE NULL END)) as daily_avg
    FROM sales_items si 
    JOIN sales s ON si.sale_id = s.id 
    WHERE si.product_id = ? AND s.status = 'completed'
", [$product_id]);
?>

<div class="row g-4">
    <!-- تصاویر -->
    <div class="col-md-4">
        <div class="text-center">
            <img src="<?= get_product_image_url($product) ?>" 
                 class="img-fluid mb-3" 
                 alt="<?= $product['name'] ?>">
            
            <?php if (!empty($gallery)): ?>
                <div class="product-gallery">
                    <?php foreach ($gallery as $image): ?>
                        <div class="gallery-item">
                            <img src="<?= UPLOADS_URL ?>/products/gallery/<?= $image['image'] ?>" 
                                 alt="<?= $product['name'] ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- اطلاعات -->
    <div class="col-md-8">
        <table class="table table-sm">
            <tr>
                <td width="200" class="text-muted">نام محصول:</td>
                <td class="fw-bold"><?= $product['name'] ?></td>
            </tr>
            <tr>
                <td class="text-muted">کد محصول:</td>
                <td>
                    <?= $product['code'] ?>
                    <?php if ($product['barcode']): ?>
                        <br>
                        <small class="text-muted">بارکد: <?= $product['barcode'] ?></small>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="text-muted">دسته‌بندی:</td>
                <td><?= $product['category_name'] ?? '-' ?></td>
            </tr>
            <tr>
                <td class="text-muted">واحد شمارش:</td>
                <td><?= $product['unit'] ?></td>
            </tr>
            <tr>
                <td class="text-muted">موجودی فعلی:</td>
                <td>
                    <?php
                    $stock_class = 'success';
                    if ($product['stock'] <= 0) {
                        $stock_class = 'danger';
                    } elseif ($product['stock'] <= $product['reorder_point']) {
                        $stock_class = 'warning';
                    }
                    ?>
                    <span class="badge bg-<?= $stock_class ?>">
                        <?= number_format($product['stock']) ?>
                        <?= $product['unit'] ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="text-muted">نقطه سفارش:</td>
                <td>
                    <?= number_format($product['reorder_point']) ?>
                    <?= $product['unit'] ?>
                </td>
            </tr>
            <tr>
                <td class="text-muted">محدوده موجودی:</td>
                <td>
                    حداقل: <?= number_format($product['min_stock']) ?> <?= $product['unit'] ?>
                    <br>
                    حداکثر: <?= number_format($product['max_stock']) ?> <?= $product['unit'] ?>
                </td>
            </tr>
            <tr>
                <td class="text-muted">قیمت‌ها:</td>
                <td>
                    <div class="mb-1">
                        قیمت خرید: 
                        <span class="fw-bold">
                            <?= number_format($product['purchase_price']) ?>
                            <small class="text-muted">تومان</small>
                        </span>
                    </div>
                    <div class="mb-1">
                        قیمت فروش: 
                        <span class="fw-bold text-primary">
                            <?= number_format($product['sale_price']) ?>
                            <small class="text-muted">تومان</small>
                        </span>
                    </div>
                    <?php if ($product['min_price']): ?>
                        <div class="mb-1">
                            حداقل قیمت: 
                            <span class="fw-bold text-danger">
                                <?= number_format($product['min_price']) ?>
                                <small class="text-muted">تومان</small>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if ($product['wholesale_price']): ?>
                        <div>
                            قیمت عمده: 
                            <span class="fw-bold text-success">
                                <?= number_format($product['wholesale_price']) ?>
                                <small class="text-muted">تومان</small>
                            </span>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="text-muted">آمار فروش:</td>
                <td>
                    <div class="mb-1">
                        تعداد کل فروش: 
                        <span class="fw-bold text-success">
                            <?= number_format($product['total_sales']) ?> مورد
                        </span>
                    </div>
                    <div class="mb-1">
                        مجموع فروش: 
                        <span class="fw-bold text-success">
                            <?= number_format($product['total_quantity_sold']) ?>
                            <?= $product['unit'] ?>
                        </span>
                    </div>
                    <div class="small text-muted">
                        میانگین فروش:
                        <br>
                        روزانه: <?= number_format($sales_avg['daily_avg']) ?> <?= $product['unit'] ?>
                        &nbsp;|&nbsp;
                        هفتگی: <?= number_format($sales_avg['weekly_avg']) ?> <?= $product['unit'] ?>
                        &nbsp;|&nbsp;
                        ماهانه: <?= number_format($sales_avg['monthly_avg']) ?> <?= $product['unit'] ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="text-muted">وضعیت:</td>
                <td>
                    <?php if ($product['status'] == 'active'): ?>
                        <span class="badge bg-success">فعال</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">غیرفعال</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if ($product['shelf_number']): ?>
                <tr>
                    <td class="text-muted">شماره قفسه:</td>
                    <td><?= $product['shelf_number'] ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($product['weight']): ?>
                <tr>
                    <td class="text-muted">وزن:</td>
                    <td><?= $product['weight'] ?> گرم</td>
                </tr>
            <?php endif; ?>
            <?php if ($product['dimensions']): ?>
                <tr>
                    <td class="text-muted">ابعاد:</td>
                    <td><?= $product['dimensions'] ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($product['description']): ?>
                <tr>
                    <td class="text-muted">توضیحات:</td>
                    <td><?= nl2br($product['description']) ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="text-muted">اطلاعات ثبت:</td>
                <td>
                    <div class="small text-muted">
                        ثبت توسط: <?= $product['created_by_name'] ?>
                        <br>
                        تاریخ ثبت: <?= format_date($product['created_at']) ?>
                    </div>
                </td>
            </tr>
        </table>
        
        <!-- تراکنش‌های اخیر -->
        <?php if (!empty($transactions)): ?>
            <h6 class="mt-4 mb-3">تراکنش‌های اخیر</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>نوع</th>
                            <th>تعداد</th>
                            <th>توضیحات</th>
                            <th>کاربر</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td class="small">
                                    <?= format_date($transaction['created_at']) ?>
                                </td>
                                <td>
                                    <?php
                                    $type_class = 'primary';
                                    $type_text = 'ورود';
                                    
                                    if ($transaction['type'] == 'out') {
                                        $type_class = 'danger';
                                        $type_text = 'خروج';
                                    } elseif ($transaction['type'] == 'adjustment') {
                                        $type_class = 'warning';
                                        $type_text = 'تعدیل';
                                    }
                                    ?>
                                    <span class="badge bg-<?= $type_class ?>">
                                        <?= $type_text ?>
                                    </span>
                                </td>
                                <td>
                                    <?= number_format(abs($transaction['quantity'])) ?>
                                    <?= $product['unit'] ?>
                                </td>
                                <td><?= $transaction['description'] ?></td>
                                <td class="small"><?= $transaction['user_name'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <!-- تغییرات قیمت -->
        <?php if (!empty($price_changes)): ?>
            <h6 class="mt-4 mb-3">تاریخچه تغییر قیمت</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>قیمت قبلی</th>
                            <th>قیمت جدید</th>
                            <th>توضیحات</th>
                            <th>کاربر</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($price_changes as $change): ?>
                            <tr>
                                <td class="small">
                                    <?= format_date($change['created_at']) ?>
                                </td>
                                <td>
                                    <?= number_format($change['old_price']) ?>
                                    <small class="text-muted">تومان</small>
                                </td>
                                <td>
                                    <?= number_format($change['new_price']) ?>
                                    <small class="text-muted">تومان</small>
                                </td>
                                <td><?= $change['description'] ?></td>
                                <td class="small"><?= $change['user_name'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>