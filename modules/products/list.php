<?php
/**
 * صفحه لیست محصولات
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

require_once '../../config/config.php';
check_access();

$page_title = 'لیست محصولات';

// دریافت پارامترهای فیلتر
$filters = [
    'search' => $_GET['search'] ?? '',
    'category' => (int)($_GET['category'] ?? 0),
    'status' => $_GET['status'] ?? '',
    'min_stock' => $_GET['min_stock'] ?? '',
    'max_stock' => $_GET['max_stock'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'sort' => $_GET['sort'] ?? 'id',
    'order' => $_GET['order'] ?? 'DESC',
    'view' => $_GET['view'] ?? 'table', // table یا grid
    'per_page' => (int)($_GET['per_page'] ?? 20),
    'page' => (int)($_GET['page'] ?? 1)
];

// ساخت شرط‌های کوئری
$where = ['1=1'];
$params = [];

if ($filters['search']) {
    $where[] = "(
        name LIKE ? OR 
        code LIKE ? OR 
        barcode LIKE ? OR 
        description LIKE ?
    )";
    $search = '%' . $filters['search'] . '%';
    $params = array_merge($params, [$search, $search, $search, $search]);
}

if ($filters['category']) {
    $where[] = "category_id = ?";
    $params[] = $filters['category'];
}

if ($filters['status']) {
    $where[] = "status = ?";
    $params[] = $filters['status'];
}

if ($filters['min_stock'] !== '') {
    $where[] = "stock >= ?";
    $params[] = $filters['min_stock'];
}

if ($filters['max_stock'] !== '') {
    $where[] = "stock <= ?";
    $params[] = $filters['max_stock'];
}

if ($filters['min_price'] !== '') {
    $where[] = "sale_price >= ?";
    $params[] = str_replace(',', '', $filters['min_price']);
}

if ($filters['max_price'] !== '') {
    $where[] = "sale_price <= ?";
    $params[] = str_replace(',', '', $filters['max_price']);
}

// محاسبه تعداد کل
$total = db_get_var("
    SELECT COUNT(*) 
    FROM products 
    WHERE " . implode(' AND ', $where), 
    $params
);

// محاسبه تعداد صفحات
$total_pages = ceil($total / $filters['per_page']);
if ($filters['page'] > $total_pages) {
    $filters['page'] = $total_pages;
}

// دریافت لیست محصولات
$products = db_get_rows("
    SELECT p.*, 
           c.name as category_name,
           (SELECT image FROM product_images WHERE product_id = p.id ORDER BY sort_order LIMIT 1) as gallery_image,
           (
               SELECT COUNT(*) 
               FROM sales_items si 
               JOIN sales s ON si.sale_id = s.id 
               WHERE si.product_id = p.id AND s.status = 'completed'
           ) as total_sales
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY {$filters['sort']} {$filters['order']}
    LIMIT ? OFFSET ?
", array_merge(
    $params,
    [$filters['per_page'], ($filters['page'] - 1) * $filters['per_page']]
));

// دریافت دسته‌بندی‌ها
$categories = db_get_rows("
    SELECT c.*, p.name as parent_name 
    FROM categories c 
    LEFT JOIN categories p ON c.parent_id = p.id 
    ORDER BY c.parent_id ASC, c.name ASC
");

// محاسبه آمار
$stats = db_get_row("
    SELECT 
        COUNT(*) as total_products,
        SUM(CASE WHEN stock <= reorder_point THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock,
        SUM(stock) as total_stock,
        SUM(stock * purchase_price) as total_inventory_value
    FROM products
");

require_once '../../includes/header.php';
?>

<!-- فیلترها -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" id="filter-form" class="row g-3">
            <!-- جستجو -->
            <div class="col-md-3">
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="جستجو در محصولات..."
                           value="<?= $filters['search'] ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            
            <!-- دسته‌بندی -->
            <div class="col-md-2">
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">همه دسته‌بندی‌ها</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" 
                                <?= $filters['category'] == $category['id'] ? 'selected' : '' ?>>
                            <?php if ($category['parent_name']): ?>
                                <?= $category['parent_name'] ?> &raquo; 
                            <?php endif; ?>
                            <?= $category['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- وضعیت -->
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active" <?= $filters['status'] == 'active' ? 'selected' : '' ?>>
                        فعال
                    </option>
                    <option value="inactive" <?= $filters['status'] == 'inactive' ? 'selected' : '' ?>>
                        غیرفعال
                    </option>
                </select>
            </div>
            
            <!-- موجودی -->
            <div class="col-md-2">
                <div class="input-group">
                    <input type="number" 
                           name="min_stock" 
                           class="form-control" 
                           placeholder="حداقل موجودی"
                           value="<?= $filters['min_stock'] ?>">
                    <input type="number" 
                           name="max_stock" 
                           class="form-control" 
                           placeholder="حداکثر موجودی"
                           value="<?= $filters['max_stock'] ?>">
                </div>
            </div>
            
            <!-- قیمت -->
            <div class="col-md-2">
                <div class="input-group">
                    <input type="text" 
                           name="min_price" 
                           class="form-control price-format" 
                           placeholder="حداقل قیمت"
                           value="<?= $filters['min_price'] ?>">
                    <input type="text" 
                           name="max_price" 
                           class="form-control price-format" 
                           placeholder="حداکثر قیمت"
                           value="<?= $filters['max_price'] ?>">
                </div>
            </div>
            
            <!-- نمایش -->
            <div class="col-md-1">
                <div class="btn-group w-100">
                    <button type="button" 
                            class="btn btn-outline-secondary <?= $filters['view'] == 'table' ? 'active' : '' ?>"
                            onclick="changeView('table')">
                        <i class="bi bi-list"></i>
                    </button>
                    <button type="button" 
                            class="btn btn-outline-secondary <?= $filters['view'] == 'grid' ? 'active' : '' ?>"
                            onclick="changeView('grid')">
                        <i class="bi bi-grid"></i>
                    </button>
                </div>
                <input type="hidden" name="view" value="<?= $filters['view'] ?>">
            </div>
        </form>
    </div>
</div>

<!-- آمار -->
<div class="row g-4 mb-4">
    <div class="col-md">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">تعداد کل محصولات</h6>
                <h3 class="card-text"><?= number_format($stats['total_products']) ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">موجودی کل انبار</h6>
                <h3 class="card-text"><?= number_format($stats['total_stock']) ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">محصولات کم موجود</h6>
                <h3 class="card-text text-warning"><?= number_format($stats['low_stock']) ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">محصولات ناموجود</h6>
                <h3 class="card-text text-danger"><?= number_format($stats['out_of_stock']) ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-md">
        <div class="card text-center">
            <div class="card-body">
                <h6 class="card-title text-muted mb-1">ارزش موجودی</h6>
                <h3 class="card-text">
                    <?= number_format($stats['total_inventory_value']) ?>
                    <small class="text-muted fs-6">تومان</small>
                </h3>
            </div>
        </div>
    </div>
</div>

<?php if ($filters['view'] == 'table'): ?>
    <!-- نمای جدولی -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th width="80">تصویر</th>
                        <th>
                            <a href="?<?= http_build_query(array_merge($filters, ['sort' => 'name', 'order' => $filters['sort'] == 'name' && $filters['order'] == 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                               class="text-dark text-decoration-none">
                                نام محصول
                                <?php if ($filters['sort'] == 'name'): ?>
                                    <i class="bi bi-arrow-<?= $filters['order'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>کد</th>
                        <th>دسته‌بندی</th>
                        <th class="text-center">
                            <a href="?<?= http_build_query(array_merge($filters, ['sort' => 'stock', 'order' => $filters['sort'] == 'stock' && $filters['order'] == 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                               class="text-dark text-decoration-none">
                                موجودی
                                <?php if ($filters['sort'] == 'stock'): ?>
                                    <i class="bi bi-arrow-<?= $filters['order'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="text-center">
                            <a href="?<?= http_build_query(array_merge($filters, ['sort' => 'sale_price', 'order' => $filters['sort'] == 'sale_price' && $filters['order'] == 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                               class="text-dark text-decoration-none">
                                قیمت فروش
                                <?php if ($filters['sort'] == 'sale_price'): ?>
                                    <i class="bi bi-arrow-<?= $filters['order'] == 'ASC' ? 'up' : 'down' ?>"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="text-center">وضعیت</th>
                        <th width="150">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="9" class="text-center p-4 text-muted">
                                هیچ محصولی یافت نشد.
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td>
                                <img src="<?= get_product_image_url($product) ?>" 
                                     class="img-thumbnail" 
                                     width="50" 
                                     height="50"
                                     alt="<?= $product['name'] ?>">
                            </td>
                            <td>
                                <div class="fw-500"><?= $product['name'] ?></div>
                                <?php if ($product['total_sales']): ?>
                                    <small class="text-success">
                                        <i class="bi bi-graph-up"></i>
                                        <?= number_format($product['total_sales']) ?> فروش
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><?= $product['code'] ?></div>
                                <?php if ($product['barcode']): ?>
                                    <small class="text-muted"><?= $product['barcode'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= $product['category_name'] ?? '-' ?></td>
                            <td class="text-center">
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
                            <td class="text-center">
                                <?= number_format($product['sale_price']) ?>
                                <small class="text-muted">تومان</small>
                            </td>
                            <td class="text-center">
                                <?php if ($product['status'] == 'active'): ?>
                                    <span class="badge bg-success">فعال</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="add.php?edit=<?= $product['id'] ?>" 
                                       class="btn btn-primary" 
                                       title="ویرایش">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-info" 
                                            title="مشاهده جزئیات"
                                            onclick="showDetails(<?= $product['id'] ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            title="حذف محصول"
                                            onclick="deleteProduct(<?= $product['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <!-- نمای گرید -->
    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="text-center p-4 text-muted">
                    هیچ محصولی یافت نشد.
                </div>
            </div>
        <?php endif; ?>
        
        <?php foreach ($products as $product): ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="card h-100">
                    <img src="<?= get_product_image_url($product) ?>" 
                         class="card-img-top p-3" 
                         alt="<?= $product['name'] ?>"
                         style="height: 200px; object-fit: contain;">
                         
                    <div class="card-body">
                        <h6 class="card-title mb-1">
                            <?= $product['name'] ?>
                        </h6>
                        
                        <div class="small text-muted mb-2">
                            <?= $product['category_name'] ?? '-' ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
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
                            </div>
                            <div class="fw-bold">
                                <?= number_format($product['sale_price']) ?>
                                <small class="text-muted">تومان</small>
                            </div>
                        </div>
                        
                        <div class="btn-group btn-group-sm w-100">
                            <a href="add.php?edit=<?= $product['id'] ?>" 
                               class="btn btn-primary" 
                               title="ویرایش">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" 
                                    class="btn btn-info" 
                                    title="مشاهده جزئیات"
                                    onclick="showDetails(<?= $product['id'] ?>)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    title="حذف محصول"
                                    onclick="deleteProduct(<?= $product['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- صفحه‌بندی -->
<?php if ($total_pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <!-- صفحه قبل -->
            <li class="page-item <?= $filters['page'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $filters['page'] - 1])) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            
            <!-- شماره صفحات -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $filters['page'] == $i ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            
            <!-- صفحه بعد -->
            <li class="page-item <?= $filters['page'] >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $filters['page'] + 1])) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<!-- مودال جزئیات محصول -->
<div class="modal fade" id="productDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">جزئیات محصول</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center p-4">
                    <div class="spinner-border"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// تغییر نوع نمایش
function changeView(view) {
    document.querySelector('[name="view"]').value = view;
    document.getElementById('filter-form').submit();
}

// نمایش جزئیات محصول
function showDetails(productId) {
    const modal = new bootstrap.Modal(document.getElementById('productDetailsModal'));
    modal.show();
    
    fetch('ajax/product-details.php?id=' + productId)
        .then(response => response.text())
        .then(html => {
            document.querySelector('#productDetailsModal .modal-body').innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('#productDetailsModal .modal-body').innerHTML = 
                '<div class="text-center text-danger p-4">خطا در دریافت اطلاعات</div>';
        });
}

// حذف محصول
function deleteProduct(productId) {
    if (!confirm('آیا از حذف این محصول اطمینان دارید؟')) {
        return;
    }
    
    fetch('ajax/delete-product.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            location.reload();
        } else {
            alert(result.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('خطا در حذف محصول');
    });
}

// فرمت‌بندی قیمت‌ها
document.querySelectorAll('.price-format').forEach(function(input) {
    input.addEventListener('input', function() {
        let value = this.value.replace(/[^\d]/g, '');
        this.value = value ? new Intl.NumberFormat('fa-IR').format(value) : '';
    });
    
    if (input.value) {
        input.value = new Intl.NumberFormat('fa-IR').format(input.value);
    }
});
</script>

<?php
require_once '../../includes/footer.php';