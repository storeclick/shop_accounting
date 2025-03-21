<?php
/**
 * صفحه افزودن/ویرایش محصول
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 * آخرین بروزرسانی: ۱۴۰۳/۰۱/۰۲
 */

require_once '../../config/config.php';
check_access();

$page_title = isset($_GET['edit']) ? 'ویرایش محصول' : 'افزودن محصول جدید';

// دریافت دسته‌بندی‌ها
$categories = db_get_rows("
    SELECT c.*, p.name as parent_name 
    FROM categories c 
    LEFT JOIN categories p ON c.parent_id = p.id 
    ORDER BY c.parent_id ASC, c.name ASC
");

// دریافت واحدهای شمارش
$units = [
    'عدد',
    'کیلوگرم',
    'گرم',
    'متر',
    'سانتی‌متر',
    'لیتر',
    'میلی‌لیتر',
    'بسته',
    'جعبه',
    'کارتن'
];

// اگر ویرایش است، دریافت اطلاعات محصول
if (isset($_GET['edit'])) {
    $product_id = (int)$_GET['edit'];
    $product = db_get_row("SELECT * FROM products WHERE id = ?", [$product_id]);
    if (!$product) {
        redirect('list.php');
    }
    
    // دریافت تصاویر گالری
    $gallery = db_get_rows("
        SELECT * FROM product_images 
        WHERE product_id = ? 
        ORDER BY sort_order ASC
    ", [$product_id]);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $data = [
            'name' => sanitize($_POST['name']),
            'code' => sanitize($_POST['code']),
            'barcode' => sanitize($_POST['barcode']),
            'category_id' => (int)$_POST['category_id'],
            'unit' => sanitize($_POST['unit']),
            'purchase_price' => (float)str_replace(',', '', $_POST['purchase_price']),
            'sale_price' => (float)str_replace(',', '', $_POST['sale_price']),
            'min_price' => (float)str_replace(',', '', $_POST['min_price']),
            'wholesale_price' => (float)str_replace(',', '', $_POST['wholesale_price']),
            'min_stock' => (int)$_POST['min_stock'],
            'max_stock' => (int)$_POST['max_stock'],
            'stock' => (int)$_POST['stock'],
            'reorder_point' => (int)$_POST['reorder_point'],
            'shelf_number' => sanitize($_POST['shelf_number']),
            'weight' => (float)$_POST['weight'],
            'dimensions' => sanitize($_POST['dimensions']),
            'status' => $_POST['status'],
            'description' => sanitize($_POST['description']),
            'notes' => sanitize($_POST['notes'])
        ];
        
        // بررسی فیلدهای اجباری
        if (empty($data['name'])) {
            throw new Exception('لطفاً نام محصول را وارد کنید.');
        }
        
        if (empty($data['code'])) {
            // تولید کد خودکار
            $data['code'] = generate_product_code();
        } else {
            // بررسی یکتا بودن کد
            $exists = db_get_row("
                SELECT id FROM products 
                WHERE code = ? AND id != ?
            ", [$data['code'], $product_id ?? 0]);
            
            if ($exists) {
                throw new Exception('این کد محصول قبلاً ثبت شده است.');
            }
        }
        
        // بررسی قیمت‌ها
        if ($data['sale_price'] < $data['min_price']) {
            throw new Exception('قیمت فروش نمی‌تواند از حداقل قیمت کمتر باشد.');
        }
        
        if ($data['wholesale_price'] > 0 && $data['wholesale_price'] < $data['min_price']) {
            throw new Exception('قیمت عمده‌فروشی نمی‌تواند از حداقل قیمت کمتر باشد.');
        }
        
        // آپلود تصویر اصلی
        if (!empty($_FILES['image']['name'])) {
            try {
                $image = upload_image($_FILES['image'], 'products', [
                    'thumb' => [150, 150],
                    'medium' => [400, 400]
                ]);
                
                // حذف تصویر قبلی در صورت ویرایش
                if (isset($product) && $product['image']) {
                    delete_file($product['image'], 'products');
                    delete_file(str_replace('.', '_thumb.', $product['image']), 'products');
                    delete_file(str_replace('.', '_medium.', $product['image']), 'products');
                }
                
                $data['image'] = basename($image['original']);
                $data['thumbnail'] = basename($image['thumb']);
            } catch (Exception $e) {
                throw new Exception('خطا در آپلود تصویر: ' . $e->getMessage());
            }
        }
        
        // آپلود تصاویر گالری
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $key => $tmp_name) {
                $gallery_file = [
                    'name' => $_FILES['gallery']['name'][$key],
                    'type' => $_FILES['gallery']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['gallery']['error'][$key],
                    'size' => $_FILES['gallery']['size'][$key]
                ];
                
                try {
                    $gallery_image = upload_image($gallery_file, 'products/gallery', [
                        'thumb' => [150, 150]
                    ]);
                    
                    // درج در جدول گالری
                    $sort_order = db_get_row("
                        SELECT MAX(sort_order) + 1 as next_order 
                        FROM product_images 
                        WHERE product_id = ?
                    ", [$product_id])['next_order'] ?? 1;
                    
                    db_query("
                        INSERT INTO product_images (
                            product_id, image, thumbnail, 
                            sort_order, created_by, created_at
                        ) VALUES (?, ?, ?, ?, ?, NOW())
                    ", [
                        $product_id,
                        basename($gallery_image['original']),
                        basename($gallery_image['thumb']),
                        $sort_order,
                        $_SESSION['user_id']
                    ]);
                    
                } catch (Exception $e) {
                    error_log('خطا در آپلود تصویر گالری: ' . $e->getMessage());
                    continue;
                }
            }
        }
        
        if (isset($_GET['edit'])) {
            // ویرایش محصول
            $result = db_query("
                UPDATE products 
                SET name = ?, code = ?, barcode = ?, category_id = ?, 
                    unit = ?, purchase_price = ?, sale_price = ?, min_price = ?, 
                    wholesale_price = ?, min_stock = ?, max_stock = ?, 
                    stock = ?, reorder_point = ?, shelf_number = ?, 
                    weight = ?, dimensions = ?, status = ?, 
                    description = ?, notes = ?
                    " . (!empty($data['image']) ? ", image = ?, thumbnail = ?" : "") . "
                WHERE id = ?
            ", array_merge(
                array_values($data),
                !empty($data['image']) ? [$data['image'], $data['thumbnail']] : [],
                [$product_id]
            ));
            
            if ($result) {
                // ثبت در تاریخچه تغییرات
                log_action('edit_product', sprintf(
                    'ویرایش محصول "%s" - کد: %s',
                    $data['name'],
                    $data['code']
                ));
                
                show_message('محصول با موفقیت ویرایش شد.');
                redirect('list.php');
            }
            
            throw new Exception('خطا در ویرایش محصول.');
            
        } else {
            // افزودن محصول جدید
            $result = db_query("
                INSERT INTO products (
                    name, code, barcode, category_id, unit, 
                    purchase_price, sale_price, min_price, wholesale_price, 
                    min_stock, max_stock, stock, reorder_point, 
                    shelf_number, weight, dimensions, 
                    status, description, notes, image, thumbnail,
                    created_by, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, NOW()
                )
            ", array_merge(
                array_values($data),
                [$data['image'] ?? null, $data['thumbnail'] ?? null],
                [$_SESSION['user_id']]
            ));
            
            if ($result) {
                $product_id = db_last_insert_id();
                
                // ثبت تراکنش موجودی اولیه
                if ($data['stock'] > 0) {
                    db_query("
                        INSERT INTO inventory_transactions (
                            product_id, type, quantity, 
                            description, created_by, created_at
                        ) VALUES (?, 'initial', ?, ?, ?, NOW())
                    ", [
                        $product_id,
                        $data['stock'],
                        'موجودی اولیه',
                        $_SESSION['user_id']
                    ]);
                }
                
                // ثبت در تاریخچه
                log_action('add_product', sprintf(
                    'افزودن محصول "%s" - کد: %s',
                    $data['name'],
                    $data['code']
                ));
                
                show_message('محصول با موفقیت ثبت شد.');
                redirect('list.php');
            }
            
            throw new Exception('خطا در ثبت محصول.');
        }
        
    } catch (Exception $e) {
        show_message($e->getMessage(), 'danger');
    }
}

require_once '../../includes/header.php';

?>

<form method="post" enctype="multipart/form-data" class="row" onsubmit="return validateForm(this);">
    <!-- ستون اصلی -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="bi bi-info-circle text-primary"></i>
                    اطلاعات اصلی
                </h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">نام محصول <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="name" 
                               class="form-control" 
                               value="<?= $product['name'] ?? '' ?>"
                               maxlength="100"
                               required>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">کد محصول</label>
                        <input type="text" 
                               name="code" 
                               class="form-control" 
                               value="<?= $product['code'] ?? '' ?>"
                               maxlength="20"
                               pattern="[A-Za-z0-9-_]+"
                               title="فقط حروف انگلیسی، اعداد، خط تیره و زیرخط">
                        <div class="form-text">
                            در صورت خالی بودن، کد به صورت خودکار تولید می‌شود
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">بارکد</label>
                        <div class="input-group">
                            <input type="text" 
                                   name="barcode" 
                                   class="form-control" 
                                   value="<?= $product['barcode'] ?? '' ?>"
                                   maxlength="20">
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    onclick="scanBarcode()">
                                <i class="bi bi-upc-scan"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-outline-secondary" 
                                    onclick="generateRandomBarcode()">
                                <i class="bi bi-gear"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">دسته‌بندی</label>
                        <select name="category_id" class="form-select">
                            <option value="">بدون دسته‌بندی</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                        <?= (($product['category_id'] ?? '') == $category['id'] ? 'selected' : '') ?>>
                                    <?php if ($category['parent_name']): ?>
                                        <?= $category['parent_name'] ?> &raquo; 
                                    <?php endif; ?>
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">واحد شمارش</label>
                        <select name="unit" class="form-select">
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit ?>" 
                                        <?= (($product['unit'] ?? '') == $unit ? 'selected' : '') ?>>
                                    <?= $unit ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label class="form-label">وضعیت</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= (($product['status'] ?? '') == 'active' ? 'selected' : '') ?>>
                                فعال
                            </option>
                            <option value="inactive" <?= (($product['status'] ?? '') == 'inactive' ? 'selected' : '') ?>>
                                غیرفعال
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="bi bi-currency-dollar text-success"></i>
                    اطلاعات قیمت
                </h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">قیمت خرید (تومان)</label>
                        <input type="text" 
                               name="purchase_price" 
                               class="form-control price-format" 
                               value="<?= $product['purchase_price'] ?? '' ?>">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">قیمت فروش (تومان) <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="sale_price" 
                               class="form-control price-format" 
                               value="<?= $product['sale_price'] ?? '' ?>"
                               required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">حداقل قیمت (تومان)</label>
                        <input type="text" 
                               name="min_price" 
                               class="form-control price-format" 
                               value="<?= $product['min_price'] ?? '' ?>">
                        <div class="form-text">
                            حداقل قیمتی که می‌توان محصول را به فروش رساند
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">قیمت عمده‌فروشی (تومان)</label>
                        <input type="text" 
                               name="wholesale_price" 
                               class="form-control price-format" 
                               value="<?= $product['wholesale_price'] ?? '' ?>">
                        <div class="form-text">
                            قیمت فروش برای خریدهای عمده
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="bi bi-box text-warning"></i>
                    اطلاعات انبار
                </h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">موجودی اولیه</label>
                        <input type="number" 
                               name="stock" 
                               class="form-control" 
                               value="<?= $product['stock'] ?? '0' ?>"
                               min="0"
                               <?= isset($product) ? 'readonly' : '' ?>>
                        <?php if (isset($product)): ?>
                            <div class="form-text text-danger">
                                موجودی فقط از طریق ثبت رسید/حواله قابل تغییر است
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">نقطه سفارش</label>
                        <input type="number" 
                               name="reorder_point" 
                               class="form-control" 
                               value="<?= $product['reorder_point'] ?? '0' ?>"
                               min="0">
                        <div class="form-text">
                            حداقل موجودی که باید سفارش جدید ثبت شود
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">حداقل موجودی</label>
                        <input type="number" 
                               name="min_stock" 
                               class="form-control" 
                               value="<?= $product['min_stock'] ?? '0' ?>"
                               min="0">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">حداکثر موجودی</label>
                        <input type="number" 
                               name="max_stock" 
                               class="form-control" 
                               value="<?= $product['max_stock'] ?? '0' ?>"
                               min="0">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">شماره قفسه</label>
                        <input type="text" 
                               name="shelf_number" 
                               class="form-control" 
                               value="<?= $product['shelf_number'] ?? '' ?>"
                               maxlength="20">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="bi bi-card-text text-info"></i>
                    توضیحات
                </h5>
                
                <div class="mb-3">
                    <label class="form-label">توضیحات محصول</label>
                    <textarea name="description" 
                              class="form-control" 
                              rows="4"><?= $product['description'] ?? '' ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">یادداشت‌های داخلی</label>
                    <textarea name="notes" 
                              class="form-control" 
                              rows="3"><?= $product['notes'] ?? '' ?></textarea>
                    <div class="form-text">
                        این یادداشت‌ها فقط برای استفاده داخلی است و به مشتری نمایش داده نمی‌شود
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ستون کناری -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="bi bi-image text-success"></i>
                    تصویر محصول
                </h5>
                
                <div class="product-image-upload" onclick="document.getElementById('mainImage').click()">
                    <img src="<?= isset($product['image']) ? UPLOADS_URL . '/products/' . $product['image'] : ASSETS_URL . '/images/product-placeholder.png' ?>" 
                         alt="تصویر محصول">
                    
                    <div class="upload-overlay">
                        <i class="bi bi-cloud-upload"></i>
                        <span><?= isset($product['image']) ? 'تغییر تصویر اصلی' : 'افزودن تصویر اصلی' ?></span>
                    </div>
                    
                    <input type="file" 
                           id="mainImage"
                           name="image" 
                           class="d-none" 
                           accept="image/*"
                           onchange="previewImage(this)">
                </div>
                
                <div class="form-text text-center mt-2">
                    حداکثر حجم: ۲ مگابایت<br>
                    فرمت‌های مجاز: jpg, jpeg, png, gif
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="bi bi-images text-info"></i>
                    گالری تصاویر
                </h5>
                
                <div class="product-gallery">
                    <?php if (!empty($gallery)): ?>
                        <?php foreach ($gallery as $image): ?>
                            <div class="gallery-item" data-id="<?= $image['id'] ?>">
                                <img src="<?= UPLOADS_URL ?>/products/gallery/<?= $image['image'] ?>" 
                                     alt="تصویر محصول">
                                
                                <div class="gallery-overlay">
                                    <div class="gallery-actions">
                                        <button type="button" 
                                                onclick="previewGalleryImage('<?= UPLOADS_URL ?>/products/gallery/<?= $image['image'] ?>')"
                                                title="نمایش">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button"
                                                onclick="deleteGalleryImage(<?= $image['id'] ?>)"
                                                title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (isset($product)): ?>
                        <div class="gallery-item" onclick="document.getElementById('galleryImages').click()">
                            <div class="upload-overlay">
                                <i class="bi bi-plus-lg"></i>
                                <span>افزودن تصویر</span>
                            </div>
                        </div>
                        
                        <input type="file" 
                               id="galleryImages"
                               name="gallery[]" 
                               class="d-none" 
                               accept="image/*"
                               multiple
                               onchange="uploadGalleryImages(this)">
                    <?php endif; ?>
                </div>
                
                <?php if (!isset($product)): ?>
                    <div class="text-muted text-center py-3">
                        برای افزودن تصاویر گالری، ابتدا محصول را ذخیره کنید
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-check-lg"></i>
                    <?= isset($_GET['edit']) ? 'ذخیره تغییرات' : 'افزودن محصول' ?>
                </button>
                
                <a href="list.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x"></i>
                    انصراف
                </a>
            </div>
        </div>
    </div>
</form>

<!-- مودال نمایش تصویر -->
<div class="modal fade image-preview-modal" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">نمایش تصویر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img src="" alt="تصویر محصول" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<script>
// پیش‌نمایش تصویر اصلی
function previewImage(input) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            input.closest('.product-image-upload').querySelector('img').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// پیش‌نمایش تصویر گالری
function previewGalleryImage(src) {
    let modal = document.getElementById('imagePreviewModal');
    modal.querySelector('.modal-body img').src = src;
    new bootstrap.Modal(modal).show();
}

// آپلود تصاویر گالری
function uploadGalleryImages(input) {
    if (!input.files.length) return;
    
    let formData = new FormData();
    formData.append('product_id', '<?= $product['id'] ?? 0 ?>');
    
    for (let file of input.files) {
        formData.append('images[]', file);
    }
    
    // ارسال درخواست آپلود
    fetch('ajax/upload-gallery.php', {
        method: 'POST',
        body: formData
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
        alert('خطا در آپلود تصاویر');
    });
}

// حذف تصویر گالری
function deleteGalleryImage(imageId) {
    if (!confirm('آیا از حذف این تصویر اطمینان دارید؟')) {
        return;
    }
    
    fetch('ajax/delete-gallery.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'image_id=' + imageId
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.querySelector(`.gallery-item[data-id="${imageId}"]`).remove();
        } else {
            alert(result.error);
        }
    })
    .catch(error => {
        console.error('خطا:', error);
        alert('خطا در حذف تصویر');
    });
}

// تابع اسکن بارکد
function scanBarcode() {
    // در نسخه‌های بعدی پیاده‌سازی خواهد شد
    alert('این قابلیت در نسخه‌های بعدی اضافه خواهد شد.');
}

// تولید بارکد رندوم
function generateRandomBarcode() {
    const barcode = Math.floor(Math.random() * 1000000000000); // تولید عدد ۱۲ رقمی به صورت رندوم
    document.querySelector('[name="barcode"]').value = barcode;
}

// اعتبارسنجی فرم قبل از ارسال
function validateForm(form) {
    // بررسی نام محصول
    let name = form.querySelector('[name="name"]').value.trim();
    if (!name) {
        alert('لطفاً نام محصول را وارد کنید.');
        return false;
    }
    
    // بررسی قیمت‌ها
    let salePrice = parseInt(form.querySelector('[name="sale_price"]').value.replace(/[^\d]/g, '') || 0);
    let minPrice = parseInt(form.querySelector('[name="min_price"]').value.replace(/[^\d]/g, '') || 0);
    let wholesalePrice = parseInt(form.querySelector('[name="wholesale_price"]').value.replace(/[^\d]/g, '') || 0);
    
    if (salePrice <= 0) {
        alert('لطفاً قیمت فروش را وارد کنید.');
        return false;
    }
    
    if (minPrice > 0 && salePrice < minPrice) {
        alert('قیمت فروش نمی‌تواند از حداقل قیمت کمتر باشد.');
        return false;
    }
    
    if (wholesalePrice > 0 && wholesalePrice < minPrice) {
        alert('قیمت عمده‌فروشی نمی‌تواند از حداقل قیمت کمتر باشد.');
        return false;
    }
    
    // بررسی موجودی
    let stock = parseInt(form.querySelector('[name="stock"]').value || 0);
    let minStock = parseInt(form.querySelector('[name="min_stock"]').value || 0);
    let maxStock = parseInt(form.querySelector('[name="max_stock"]').value || 0);
    
    if (minStock > 0 && maxStock > 0 && minStock >= maxStock) {
        alert('حداقل موجودی باید کمتر از حداکثر موجودی باشد.');
        return false;
    }
    
    return true;
}

// فرمت‌بندی فیلدهای قیمت
document.querySelectorAll('.price-format').forEach(function(input) {
    input.addEventListener('input', function() {
        let value = this.value.replace(/[^\d]/g, '');
        this.value = value ? new Intl.NumberFormat('fa-IR').format(value) : '';
    });
    
    // فرمت‌بندی مقدار اولیه
    if (input.value) {
        input.value = new Intl.NumberFormat('fa-IR').format(input.value);
    }
});

// مرتب‌سازی تصاویر گالری با Drag & Drop
if (document.querySelector('.product-gallery')) {
    new Sortable(document.querySelector('.product-gallery'), {
        animation: 150,
        handle: '.gallery-item:not(:last-child)',
        onEnd: function(evt) {
            let items = document.querySelectorAll('.gallery-item[data-id]');
            let order = Array.from(items).map((item, index) => ({
                id: item.dataset.id,
                order: index + 1
            }));
            
            // ذخیره ترتیب جدید
            fetch('ajax/sort-gallery.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(order)
            })
            .then(response => response.json())
            .then(result => {
                if (!result.success) {
                    alert(result.error);
                }
            })
            .catch(error => {
                console.error('خطا:', error);
                alert('خطا در ذخیره ترتیب تصاویر');
            });
        }
    });
}
// راه‌اندازی اسکنر بارکد
document.addEventListener('DOMContentLoaded', () => {
    const barcodeInput = document.querySelector('[name="barcode"]');
    if (barcodeInput) {
        initBarcodeScanner(barcodeInput);
    }
});

</script>

<?php
require_once '../../includes/footer.php';
?>