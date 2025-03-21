<?php
/**
 * مدیریت انبارداری
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 * آخرین ویرایش: ۱۴۰۳/۰۱/۰۱
 */

require_once '../../config/config.php';
require_once '../../includes/functions.php';

// بررسی دسترسی کاربر
checkAccess('products_inventory');

// تنظیمات پایه
$pageTitle = 'مدیریت انبار';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// فیلترها
$filters = [
    'category_id' => isset($_GET['category_id']) ? intval($_GET['category_id']) : 0,
    'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
    'stock' => isset($_GET['stock']) ? $_GET['stock'] : '',
    'sort' => isset($_GET['sort']) ? $_GET['sort'] : 'stock',
    'order' => isset($_GET['order']) && $_GET['order'] == 'asc' ? 'ASC' : 'DESC'
];

// ساخت شرط‌های کوئری
$where = ['1=1'];
$params = [];

if ($filters['category_id'] > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $filters['category_id'];
}

if (!empty($filters['search'])) {
    $where[] = "(p.name LIKE ? OR p.code LIKE ? OR p.barcode LIKE ?)";
    $searchTerm = "%{$filters['search']}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

switch ($filters['stock']) {
    case 'low':
        $where[] = "p.stock <= p.min_stock AND p.min_stock > 0";
        break;
    case 'out':
        $where[] = "p.stock = 0";
        break;
    case 'over':
        $where[] = "p.stock > p.max_stock AND p.max_stock > 0";
        break;
}

// ساخت دستور مرتب‌سازی
$sortColumns = [
    'name' => 'p.name',
    'code' => 'p.code',
    'category' => 'c.name',
    'stock' => 'p.stock',
    'min_stock' => 'p.min_stock',
    'last_update' => 'p.updated_at'
];

$orderBy = isset($sortColumns[$filters['sort']]) ? 
           $sortColumns[$filters['sort']] : 
           'p.stock';

// دریافت تعداد کل محصولات
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE " . implode(' AND ', $where)
    );
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();
    $totalPages = ceil($totalItems / $perPage);
} catch(PDOException $e) {
    error_log($e->getMessage());
    die('خطا در دریافت تعداد محصولات');
}

// دریافت لیست محصولات
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            c.name as category_name,
            (
                SELECT created_at 
                FROM inventory_log 
                WHERE product_id = p.id 
                ORDER BY id DESC 
                LIMIT 1
            ) as last_inventory_update
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY {$orderBy} {$filters['order']}
        LIMIT {$offset}, {$perPage}
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log($e->getMessage());
    die('خطا در دریافت لیست محصولات');
}

// تنظیم موجودی محصول
if (isset($_POST['action']) && $_POST['action'] == 'adjust_stock') {
    try {
        $productId = intval($_POST['product_id']);
        $type = $_POST['type']; // in یا out
        $quantity = intval($_POST['quantity']);
        $description = sanitize($_POST['description']);
        
        if ($quantity <= 0) {
            throw new Exception('مقدار وارد شده باید بیشتر از صفر باشد');
        }
        
        // دریافت موجودی فعلی
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $currentStock = $stmt->fetchColumn();
        
        // محاسبه موجودی جدید
        $newStock = $type == 'in' ? 
                   $currentStock + $quantity : 
                   $currentStock - $quantity;
        
        if ($newStock < 0) {
            throw new Exception('موجودی نمی‌تواند منفی باشد');
        }
        
        $pdo->beginTransaction();
        
        // بروزرسانی موجودی
        $stmt = $pdo->prepare("
            UPDATE products 
            SET stock = ?, updated_by = ? 
            WHERE id = ?
        ");
        $stmt->execute([$newStock, $_SESSION['user_id'], $productId]);
        
        // ثبت در تاریخچه
        $stmt = $pdo->prepare("
            INSERT INTO inventory_log 
            (product_id, type, quantity, previous_stock, current_stock,
             description, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $productId,
            $type,
            $quantity,
            $currentStock,
            $newStock,
            $description,
            $_SESSION['user_id']
        ]);
        
        $pdo->commit();
        
        echo json_encode(['success' => true, 'new_stock' => $newStock]);
        exit;
        
    } catch(Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// دریافت تاریخچه موجودی
if (isset($_POST['action']) && $_POST['action'] == 'get_history') {
    try {
        $productId = intval($_POST['product_id']);
        
        $stmt = $pdo->prepare("
            SELECT 
                il.*,
                u.name as user_name,
                p.name as product_name
            FROM inventory_log il
            LEFT JOIN users u ON il.created_by = u.id
            LEFT JOIN products p ON il.product_id = p.id
            WHERE il.product_id = ?
            ORDER BY il.id DESC
            LIMIT 50
        ");
        $stmt->execute([$productId]);
        $history = $stmt->fetchAll();
        
        $output = '';
        foreach ($history as $item) {
            $typeClass = $item['type'] == 'in' ? 'text-success' : 'text-danger';
            $typeIcon = $item['type'] == 'in' ? 'plus' : 'dash';
            $output .= sprintf('
                <tr>
                    <td>%s</td>
                    <td class="%s">
                        <i class="bi bi-%s-lg"></i>
                        %s
                    </td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td class="small text-muted">%s</td>
                </tr>',
                jdate('Y/m/d H:i', strtotime($item['created_at'])),
                $typeClass,
                $typeIcon,
                number_format($item['quantity']),
                number_format($item['previous_stock']),
                number_format($item['current_stock']),
                htmlspecialchars($item['description']),
                htmlspecialchars($item['user_name']),
                timeAgo($item['created_at'])
            );
        }
        
        echo json_encode(['success' => true, 'html' => $output]);
        exit;
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => 'خطا در دریافت تاریخچه']);
        exit;
    }
}

// دریافت لیست دسته‌بندی‌ها
function getCategoriesList() {
    global $pdo, $filters;
    try {
        $stmt = $pdo->query("
            SELECT id, name 
            FROM categories 
            WHERE status = 1 
            ORDER BY sort_order, name
        ");
        $categories = $stmt->fetchAll();
        
        $result = '<option value="0">همه دسته‌بندی‌ها</option>';
        foreach ($categories as $cat) {
            $result .= sprintf(
                '<option value="%d" %s>%s</option>',
                $cat['id'],
                $cat['id'] == $filters['category_id'] ? 'selected' : '',
                htmlspecialchars($cat['name'])
            );
        }
        return $result;
    } catch(PDOException $e) {
        error_log($e->getMessage());
        return '<option value="0">خطا در دریافت دسته‌بندی‌ها</option>';
    }
}
?>

<?php require_once '../../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-3">
            <?php require_once '../../includes/sidebar.php'; ?>
        </div>
        
        <div class="col-lg-9">
            <!-- فیلترها -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <?= $pageTitle ?>
                        <span class="badge bg-primary"><?= number_format($totalItems) ?></span>
                    </h4>
                    
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filters">
                        <i class="bi bi-funnel"></i>
                        فیلترها
                    </button>
                </div>
                
                <div class="collapse <?= !empty($filters['search']) || $filters['category_id'] || !empty($filters['stock']) ? 'show' : '' ?>" id="filters">
                    <div class="card-body">
                        <form method="get" class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">جستجو</label>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       value="<?= htmlspecialchars($filters['search']) ?>"
                                       placeholder="نام، کد یا بارکد محصول">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">دسته‌بندی</label>
                                <select class="form-select" name="category_id">
                                    <?= getCategoriesList() ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label">وضعیت موجودی</label>
                                <select class="form-select" name="stock">
                                    <option value="" <?= empty($filters['stock']) ? 'selected' : '' ?>>همه</option>
                                    <option value="low" <?= $filters['stock'] == 'low' ? 'selected' : '' ?>>کمتر از حد نصاب</option>
                                    <option value="out" <?= $filters['stock'] == 'out' ? 'selected' : '' ?>>ناموجود</option>
                                    <option value="over" <?= $filters['stock'] == 'over' ? 'selected' : '' ?>>بیش از حد مجاز</option>
                                </select>
                            </div>
                            
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i>
                                    جستجو
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- لیست محصولات -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th width="80">تصویر</th>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($filters, ['sort' => 'name', 'order' => $filters['sort'] == 'name' && $filters['order'] == 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                                           class="text-dark text-decoration-none">
                                            نام محصول
                                            <?php if ($filters['sort'] == 'name'): ?>
                                                <i class="bi bi-arrow-<?= $filters['order'] == 'ASC' ? 'down' : 'up' ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($filters, ['sort' => 'code', 'order' => $filters['sort'] == 'code' && $filters['order'] == 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                                           class="text-dark text-decoration-none">
                                            کد محصول
                                            <?php if ($filters['sort'] == 'code'): ?>
                                                <i class="bi bi-arrow-<?= $filters['order'] == 'ASC' ? 'down' : 'up' ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($filters, ['sort' => 'category', 'order' => $filters['sort'] == 'category' && $filters['order'] == 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                                           class="text-dark text-decoration-none">
                                            دسته‌بندی
                                            <?php if ($filters['sort'] == 'category'): ?>
                                                <i class="bi bi-arrow-<?= $filters['order'] == 'ASC' ? 'down' : 'up' ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?<?= http_build_query(array_merge($filters, ['sort' => 'stock', 'order' => $filters['sort'] == 'stock' && $filters['order'] == 'ASC' ? 'DESC' : 'ASC'])) ?>" 
                                           class="text-dark text-decoration-none">
                                            موجودی فعلی
                                            <?php if ($filters['sort'] == 'stock'): ?>
                                                <i class="bi bi-arrow-<?= $filters['order'] == 'ASC' ? 'down' : 'up' ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>حد نصاب</th>
                                    <th>آخرین بروزرسانی</th>
                                    <th width="150">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox display-1 text-muted"></i>
                                        <div class="text-muted">محصولی یافت نشد</div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= $product['image'] ? UPLOADS_URL . '/products/' . $product['image'] : ASSETS_URL . '/images/no-image.png' ?>" 
                                                 class="img-thumbnail" 
                                                 width="60" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>">
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($product['name']) ?>
                                            <?php if (!empty($product['barcode'])): ?>
                                                <div class="small text-muted">
                                                    بارکد: <?= htmlspecialchars($product['barcode']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['code']) ?></td>
                                        <td><?= $product['category_name'] ?: '---' ?></td>
                                        <td>
                                            <?php
                                            $stockClass = 'bg-success';
                                            $stockTitle = '';
                                            
                                            if ($product['stock'] == 0) {
                                                $stockClass = 'bg-danger';
                                                $stockTitle = 'ناموجود';
                                            } elseif ($product['stock'] <= $product['min_stock']) {
                                                $stockClass = 'bg-warning';
                                                $stockTitle = 'کمتر از حد نصاب';
                                            } elseif ($product['max_stock'] > 0 && $product['stock'] > $product['max_stock']) {
                                                $stockClass = 'bg-info';
                                                $stockTitle = 'بیش از حد مجاز';
                                            }
                                            ?>
                                            <span class="badge <?= $stockClass ?>" 
                                                  <?= $stockTitle ? 'title="'.$stockTitle.'"' : '' ?>>
                                                <?= number_format($product['stock']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($product['min_stock'] > 0): ?>
                                                <div class="small">
                                                    حداقل: <?= number_format($product['min_stock']) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($product['max_stock'] > 0): ?>
                                                <div class="small">
                                                    حداکثر: <?= number_format($product['max_stock']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($product['last_inventory_update']): ?>
                                                <div><?= jdate('Y/m/d', strtotime($product['last_inventory_update'])) ?></div>
                                                <div class="small text-muted">
                                                    <?= timeAgo($product['last_inventory_update']) ?>
                                                </div>
                                            <?php else: ?>
                                                ---
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-success adjust-stock" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#adjustStockModal"
                                                    data-product-id="<?= $product['id'] ?>"
                                                    data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                                    data-product-stock="<?= $product['stock'] ?>">
                                                <i class="bi bi-plus-slash-minus"></i>
                                                تنظیم موجودی
                                            </button>
                                            
                                            <button type="button" 
                                                    class="btn btn-sm btn-info show-history"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#historyModal"
                                                    data-product-id="<?= $product['id'] ?>"
                                                    data-product-name="<?= htmlspecialchars($product['name']) ?>">
                                                <i class="bi bi-clock-history"></i>
                                                تاریخچه
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- مودال تنظیم موجودی -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تنظیم موجودی</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <form id="adjustStockForm">
                    <input type="hidden" name="product_id" id="adjustProductId">
                    
                    <div class="text-center mb-3">
                        <div class="h5" id="adjustProductName"></div>
                        <div class="text-muted">موجودی فعلی: <span id="adjustCurrentStock"></span></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">نوع تغییر</label>
                        <div class="btn-group w-100">
                            <input type="radio" 
                                   class="btn-check" 
                                   name="type" 
                                   id="typeIn" 
                                   value="in" 
                                   checked>
                            <label class="btn btn-outline-success" for="typeIn">
                                <i class="bi bi-plus-lg"></i>
                                افزایش موجودی
                            </label>
                            
                            <input type="radio" 
                                   class="btn-check" 
                                   name="type" 
                                   id="typeOut" 
                                   value="out">
                            <label class="btn btn-outline-danger" for="typeOut">
                                <i class="bi bi-dash-lg"></i>
                                کاهش موجودی
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">تعداد</label>
                        <input type="number" 
                               class="form-control" 
                               name="quantity" 
                               min="1" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea class="form-control" 
                                  name="description" 
                                  rows="2"></textarea>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-primary" id="saveAdjustStock">
                    <i class="bi bi-check-lg"></i>
                    ذخیره تغییرات
                </button>
            </div>
        </div>
    </div>
</div>

<!-- مودال تاریخچه -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تاریخچه تغییرات موجودی</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="h5" id="historyProductName"></div>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>تاریخ</th>
                                <th>تعداد</th>
                                <th>موجودی قبلی</th>
                                <th>موجودی جدید</th>
                                <th>توضیحات</th>
                                <th>کاربر</th>
                                <th>زمان ثبت</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>

<script>
// نمایش مودال تنظیم موجودی
document.querySelectorAll('.adjust-stock').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        const productStock = this.dataset.productStock;
        
        document.getElementById('adjustProductId').value = productId;
        document.getElementById('adjustProductName').textContent = productName;
        document.getElementById('adjustCurrentStock').textContent = Number(productStock).toLocaleString();
        
        document.querySelector('[name="quantity"]').value = '';
        document.querySelector('[name="description"]').value = '';
        document.getElementById('typeIn').checked = true;
    });
});

// ذخیره تغییرات موجودی
document.getElementById('saveAdjustStock').addEventListener('click', function() {
    const form = document.getElementById('adjustStockForm');
    const formData = new FormData(form);
    
    if (!formData.get('quantity')) {
        showMessage('لطفاً تعداد را وارد کنید', 'danger');
        return;
    }
    
    formData.append('action', 'adjust_stock');
    
    fetch('inventory.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showMessage(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('خطا:', error);
        showMessage('خطا در ذخیره تغییرات', 'danger');
    });
});

// نمایش تاریخچه
document.querySelectorAll('.show-history').forEach(button => {
    button.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const productName = this.dataset.productName;
        
        document.getElementById('historyProductName').textContent = productName;
        document.getElementById('historyTableBody').innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner"></div>
                    <div class="mt-2">در حال دریافت تاریخچه...</div>
                </td>
            </tr>
        `;
        
        const formData = new FormData();
        formData.append('action', 'get_history');
        formData.append('product_id', productId);
        
        fetch('inventory.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('historyTableBody').innerHTML = data.html;
            } else {
                document.getElementById('historyTableBody').innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger py-4">
                            <i class="bi bi-exclamation-circle display-4"></i>
                            <div class="mt-2">${data.message}</div>
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('خطا:', error);
            document.getElementById('historyTableBody').innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-circle display-4"></i>
                        <div class="mt-2">خطا در دریافت تاریخچه</div>
                    </td>
                </tr>
            `;
        });
    });
});
</script>