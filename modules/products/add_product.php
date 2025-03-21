<?php
require_once '../../config/config.php';
require_once '../../config/db.php';
require_once '../../includes/functions.php';

// بررسی دسترسی کاربر
check_user_access();

// دریافت لیست دسته‌بندی‌ها
$categories = db_get_rows("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order, name");

// دریافت لیست واحدها
$units = db_get_rows("SELECT id, name FROM units WHERE is_active = 1 ORDER BY name");

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // دریافت داده‌های فرم
    $category_id = $_POST['category_id'] ?? null;
    $unit_id = $_POST['unit_id'] ?? null;
    $code = trim($_POST['code']);
    $barcode = trim($_POST['barcode'] ?? '');
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $purchase_price = str_replace(',', '', $_POST['purchase_price']);
    $sale_price = str_replace(',', '', $_POST['sale_price']);
    $wholesale_price = !empty($_POST['wholesale_price']) ? str_replace(',', '', $_POST['wholesale_price']) : null;
    $min_stock = intval($_POST['min_stock'] ?? 0);
    $max_stock = !empty($_POST['max_stock']) ? intval($_POST['max_stock']) : null;
    $current_stock = intval($_POST['current_stock'] ?? 0);
    $tax_percent = floatval($_POST['tax_percent'] ?? 0);
    $discount_percent = floatval($_POST['discount_percent'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // اعتبارسنجی داده‌ها
    if (empty($code)) {
        $errors[] = 'کد محصول الزامی است';
    } else {
        // بررسی تکراری نبودن کد محصول
        $stmt = $pdo->prepare("SELECT id FROM products WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->rowCount() > 0) {
            $errors[] = 'این کد محصول قبلاً ثبت شده است';
        }
    }

    if (empty($name)) {
        $errors[] = 'نام محصول الزامی است';
    }

    if ($purchase_price < 0) {
        $errors[] = 'قیمت خرید نمی‌تواند منفی باشد';
    }

    if ($sale_price < 0) {
        $errors[] = 'قیمت فروش نمی‌تواند منفی باشد';
    }

    if (!empty($wholesale_price) && $wholesale_price < 0) {
        $errors[] = 'قیمت عمده نمی‌تواند منفی باشد';
    }

    if ($min_stock < 0) {
        $errors[] = 'حداقل موجودی نمی‌تواند منفی باشد';
    }

    if (!empty($max_stock) && $max_stock <= $min_stock) {
        $errors[] = 'حداکثر موجودی باید بیشتر از حداقل موجودی باشد';
    }

    if ($current_stock < 0) {
        $errors[] = 'موجودی فعلی نمی‌تواند منفی باشد';
    }

    if ($tax_percent < 0 || $tax_percent > 100) {
        $errors[] = 'درصد مالیات باید بین 0 تا 100 باشد';
    }

    if ($discount_percent < 0 || $discount_percent > 100) {
        $errors[] = 'درصد تخفیف باید بین 0 تا 100 باشد';
    }

    // آپلود تصویر محصول
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploaded_file = upload_file($_FILES['image'], UPLOADS_DIR . '/products', ['jpg', 'jpeg', 'png']);
        if ($uploaded_file) {
            $image = $uploaded_file;
        } else {
            $errors[] = 'خطا در آپلود تصویر محصول';
        }
    }

    // اگر خطایی وجود نداشت، محصول را ثبت می‌کنیم
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (
                    category_id, unit_id, code, barcode, name, description, image,
                    purchase_price, sale_price, wholesale_price, min_stock, max_stock,
                    current_stock, tax_percent, discount_percent, is_active, created_by
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");

            $stmt->execute([
                $category_id, $unit_id, $code, $barcode, $name, $description, $image,
                $purchase_price, $sale_price, $wholesale_price, $min_stock, $max_stock,
                $current_stock, $tax_percent, $discount_percent, $is_active, $_SESSION['user_id']
            ]);

            // اگر موجودی اولیه داریم، یک تراکنش انبار ثبت می‌کنیم
            if ($current_stock > 0) {
                $product_id = $pdo->lastInsertId();
                $stmt = $pdo->prepare("
                    INSERT INTO inventory_transactions (
                        product_id, type, quantity, unit_price, total_price,
                        reference_type, description, created_by
                    ) VALUES (
                        ?, 'in', ?, ?, ?, 'adjustment', 'موجودی اولیه', ?
                    )
                ");

                $stmt->execute([
                    $product_id,
                    $current_stock,
                    $purchase_price,
                    $current_stock * $purchase_price,
                    $_SESSION['user_id']
                ]);
            }

            $success = 'محصول با موفقیت ثبت شد';
            
            // ثبت لاگ
            log_action('add_product', sprintf('محصول جدید "%s" با کد "%s" اضافه شد', $name, $code));

            // پاک کردن داده‌های فرم
            $_POST = [];
            
        } catch (PDOException $e) {
            $errors[] = sprintf('خطا در ثبت محصول: %s', $e->getMessage());
        }
    }
}

// بارگذاری قالب
include '../../templates/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="card-title mb-0">
                                <i class="bi bi-plus-circle"></i>
                                افزودن محصول جدید
                            </h3>
                        </div>
                        <div class="col text-start">
                            <a href="list_products.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-right"></i>
                                بازگشت به لیست محصولات
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
                        <!-- کد و بارکد -->
                        <div class="col-md-6">
                            <label for="code" class="form-label required">کد محصول</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-upc"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="code" 
                                       name="code" 
                                       value="<?php echo htmlspecialchars($_POST['code'] ?? ''); ?>"
                                       required>
                            </div>
                            <div class="form-text">کد اختصاصی محصول در سیستم شما</div>
                        </div>

                        <div class="col-md-6">
                            <label for="barcode" class="form-label">بارکد</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-upc-scan"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="barcode" 
                                       name="barcode"
                                       value="<?php echo htmlspecialchars($_POST['barcode'] ?? ''); ?>">
                            </div>
                            <div class="form-text">بارکد استاندارد محصول (اختیاری)</div>
                        </div>

                        <!-- نام و دسته‌بندی -->
                        <div class="col-md-6">
                            <label for="name" class="form-label required">نام محصول</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name"
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label">دسته‌بندی</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">بدون دسته‌بندی</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- واحد و توضیحات -->
                        <div class="col-md-6">
                            <label for="unit_id" class="form-label">واحد</label>
                            <select class="form-select" id="unit_id" name="unit_id">
                                <option value="">بدون واحد</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?php echo $unit['id']; ?>"
                                            <?php echo (isset($_POST['unit_id']) && $_POST['unit_id'] == $unit['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($unit['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="description" class="form-label">توضیحات</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>

                        <!-- قیمت‌ها -->
                        <div class="col-md-4">
                            <label for="purchase_price" class="form-label required">قیمت خرید</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control number-format" 
                                       id="purchase_price" 
                                       name="purchase_price"
                                       value="<?php echo number_format($_POST['purchase_price'] ?? 0); ?>"
                                       required>
                                <span class="input-group-text">تومان</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="sale_price" class="form-label required">قیمت فروش</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control number-format" 
                                       id="sale_price" 
                                       name="sale_price"
                                       value="<?php echo number_format($_POST['sale_price'] ?? 0); ?>"
                                       required>
                                <span class="input-group-text">تومان</span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="wholesale_price" class="form-label">قیمت عمده</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control number-format" 
                                       id="wholesale_price" 
                                       name="wholesale_price"
                                       value="<?php echo !empty($_POST['wholesale_price']) ? number_format($_POST['wholesale_price']) : ''; ?>">
                                <span class="input-group-text">تومان</span>
                            </div>
                        </div>

                                                <!-- موجودی -->
                                                <div class="col-md-4">
                            <label for="min_stock" class="form-label">حداقل موجودی</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="min_stock" 
                                   name="min_stock"
                                   value="<?php echo htmlspecialchars($_POST['min_stock'] ?? '0'); ?>">
                            <div class="form-text">برای هشدار موجودی کم</div>
                        </div>

                        <div class="col-md-4">
                            <label for="max_stock" class="form-label">حداکثر موجودی</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="max_stock" 
                                   name="max_stock"
                                   value="<?php echo htmlspecialchars($_POST['max_stock'] ?? ''); ?>">
                            <div class="form-text">برای هشدار موجودی زیاد (اختیاری)</div>
                        </div>

                        <div class="col-md-4">
                            <label for="current_stock" class="form-label">موجودی اولیه</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="current_stock" 
                                   name="current_stock"
                                   value="<?php echo htmlspecialchars($_POST['current_stock'] ?? '0'); ?>">
                            <div class="form-text">موجودی فعلی انبار</div>
                        </div>

                        <!-- درصدها -->
                        <div class="col-md-6">
                            <label for="tax_percent" class="form-label">درصد مالیات</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="tax_percent" 
                                       name="tax_percent"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       value="<?php echo htmlspecialchars($_POST['tax_percent'] ?? '0'); ?>">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="discount_percent" class="form-label">درصد تخفیف</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="discount_percent" 
                                       name="discount_percent"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       value="<?php echo htmlspecialchars($_POST['discount_percent'] ?? '0'); ?>">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <!-- تصویر محصول -->
                        <div class="col-md-12">
                            <label for="image" class="form-label">تصویر محصول</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="image" 
                                   name="image"
                                   accept="image/jpeg,image/png">
                            <div class="form-text">فرمت‌های مجاز: JPG، JPEG، PNG - حداکثر حجم: 2 مگابایت</div>
                        </div>

                        <!-- وضعیت -->
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" 
                                       class="form-check-input" 
                                       id="is_active" 
                                       name="is_active"
                                       <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_active">محصول فعال است</label>
                            </div>
                        </div>

                        <!-- دکمه‌ها -->
                        <div class="col-12 text-end">
                            <button type="reset" class="btn btn-light">
                                <i class="bi bi-eraser"></i>
                                پاک کردن فرم
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i>
                                ثبت محصول
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- کد جاوااسکریپت -->
<script>
// فرمت کردن اعداد
document.querySelectorAll('.number-format').forEach(function(input) {
    input.addEventListener('input', function(e) {
        // حذف همه کاراکترهای غیر عددی
        let value = this.value.replace(/[^\d]/g, '');
        
        // تبدیل به عدد
        value = parseInt(value) || 0;
        
        // فرمت کردن با کاما
        this.value = value.toLocaleString('fa-IR');
    });
});

// نمایش پیش‌نمایش تصویر
document.getElementById('image').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        // بررسی حجم فایل (حداکثر 2 مگابایت)
        if (this.files[0].size > 2 * 1024 * 1024) {
            alert('حجم فایل انتخاب شده بیشتر از حد مجاز است (حداکثر 2 مگابایت)');
            this.value = '';
            return;
        }

        // بررسی نوع فایل
        const validTypes = ['image/jpeg', 'image/png'];
        if (!validTypes.includes(this.files[0].type)) {
            alert('فرمت فایل انتخاب شده مجاز نیست. لطفاً یک تصویر JPG یا PNG انتخاب کنید.');
            this.value = '';
            return;
        }
    }
});

// محاسبه خودکار قیمت فروش بر اساس قیمت خرید (با 20 درصد سود)
document.getElementById('purchase_price').addEventListener('input', function() {
    const purchasePrice = parseInt(this.value.replace(/[^\d]/g, '')) || 0;
    const salePrice = Math.round(purchasePrice * 1.2); // 20% سود
    
    document.getElementById('sale_price').value = salePrice.toLocaleString('fa-IR');
});

// تایید حذف فرم
document.querySelector('button[type="reset"]').addEventListener('click', function(e) {
    if (!confirm('آیا از پاک کردن فرم مطمئن هستید؟')) {
        e.preventDefault();
    }
});
</script>

<?php
// بارگذاری فوتر
include '../../templates/footer.php';
?>