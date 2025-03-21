<?php
/**
 * مدیریت دسته‌بندی محصولات
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

require_once '../../config/config.php';
check_access();

$page_title = 'مدیریت دسته‌بندی‌ها';
$page_description = 'مدیریت دسته‌بندی محصولات فروشگاه';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['add'])) {
            // افزودن دسته‌بندی جدید
            $name = sanitize($_POST['name']);
            $parent_id = $_POST['parent_id'] ? (int)$_POST['parent_id'] : null;
            $description = sanitize($_POST['description']);
            
            // بررسی فیلدهای اجباری
            if (empty($name)) {
                throw new Exception('لطفاً نام دسته‌بندی را وارد کنید.');
            }
            
            // بررسی تکراری بودن
            $exists = db_get_row("
                SELECT id FROM categories 
                WHERE name = ? AND (parent_id = ? OR (parent_id IS NULL AND ? IS NULL))
            ", [$name, $parent_id, $parent_id]);
            
            if ($exists) {
                throw new Exception('این دسته‌بندی قبلاً ثبت شده است.');
            }
            
            // درج در دیتابیس
            $result = db_query("
                INSERT INTO categories (name, parent_id, description) 
                VALUES (?, ?, ?)
            ", [$name, $parent_id, $description]);
            
            if ($result) {
                log_action('add_category', sprintf(
                    'افزودن دسته‌بندی "%s"%s',
                    $name,
                    $parent_id ? ' در ' . db_get_row("SELECT name FROM categories WHERE id = ?", [$parent_id])['name'] : ''
                ));
                show_message('دسته‌بندی با موفقیت ایجاد شد.');
                redirect(get_current_url());
            }
            
            throw new Exception('خطا در ثبت دسته‌بندی.');
        }
        
        elseif (isset($_POST['edit'])) {
            // ویرایش دسته‌بندی
            $id = (int)$_POST['id'];
            $name = sanitize($_POST['name']);
            $parent_id = $_POST['parent_id'] ? (int)$_POST['parent_id'] : null;
            $description = sanitize($_POST['description']);
            
            // بررسی وجود دسته‌بندی
            $category = db_get_row("SELECT * FROM categories WHERE id = ?", [$id]);
            if (!$category) {
                throw new Exception('دسته‌بندی مورد نظر یافت نشد.');
            }
            
            // بررسی فیلدهای اجباری
            if (empty($name)) {
                throw new Exception('لطفاً نام دسته‌بندی را وارد کنید.');
            }
            
            // بررسی تکراری بودن
            $exists = db_get_row("
                SELECT id FROM categories 
                WHERE name = ? AND id != ? 
                AND (parent_id = ? OR (parent_id IS NULL AND ? IS NULL))
            ", [$name, $id, $parent_id, $parent_id]);
            
            if ($exists) {
                throw new Exception('این دسته‌بندی قبلاً ثبت شده است.');
            }
            
            // بررسی حلقه در دسته‌بندی‌ها
            if ($parent_id) {
                $current = $parent_id;
                while ($current) {
                    if ($current == $id) {
                        throw new Exception('امکان انتخاب این دسته‌بندی به عنوان والد وجود ندارد.');
                    }
                    $parent = db_get_row("SELECT parent_id FROM categories WHERE id = ?", [$current]);
                    $current = $parent ? $parent['parent_id'] : null;
                }
            }
            
            // بروزرسانی در دیتابیس
            $result = db_query("
                UPDATE categories 
                SET name = ?, parent_id = ?, description = ? 
                WHERE id = ?
            ", [$name, $parent_id, $description, $id]);
            
            if ($result) {
                log_action('edit_category', sprintf(
                    'ویرایش دسته‌بندی "%s"%s',
                    $name,
                    $parent_id ? ' در ' . db_get_row("SELECT name FROM categories WHERE id = ?", [$parent_id])['name'] : ''
                ));
                show_message('دسته‌بندی با موفقیت ویرایش شد.');
                redirect(remove_query_arg('edit'));
            }
            
            throw new Exception('خطا در ویرایش دسته‌بندی.');
        }
        
        elseif (isset($_POST['delete'])) {
            // حذف دسته‌بندی
            $id = (int)$_POST['id'];
            
            // بررسی وجود دسته‌بندی
            $category = db_get_row("SELECT * FROM categories WHERE id = ?", [$id]);
            if (!$category) {
                throw new Exception('دسته‌بندی مورد نظر یافت نشد.');
            }
            
            // بررسی وجود زیردسته
            $has_children = db_get_row("
                SELECT id FROM categories WHERE parent_id = ? LIMIT 1
            ", [$id]);
            
            if ($has_children) {
                throw new Exception('ابتدا زیردسته‌های این دسته‌بندی را حذف کنید.');
            }
            
            // بررسی وجود محصول
            $has_products = db_get_row("
                SELECT id FROM products WHERE category_id = ? LIMIT 1
            ", [$id]);
            
            if ($has_products) {
                throw new Exception('این دسته‌بندی دارای محصول است و قابل حذف نیست.');
            }
            
            // حذف از دیتابیس
            $result = db_query("DELETE FROM categories WHERE id = ?", [$id]);
            
            if ($result) {
                log_action('delete_category', sprintf(
                    'حذف دسته‌بندی "%s"%s',
                    $category['name'],
                    $category['parent_id'] ? ' از ' . db_get_row("SELECT name FROM categories WHERE id = ?", [$category['parent_id']])['name'] : ''
                ));
                show_message('دسته‌بندی با موفقیت حذف شد.');
                redirect(get_current_url());
            }
            
            throw new Exception('خطا در حذف دسته‌بندی.');
        }
        
    } catch (Exception $e) {
        show_message($e->getMessage(), 'danger');
    }
}

// دریافت دسته‌بندی‌ها
$categories = db_get_rows("
    SELECT c.*, p.name as parent_name,
           (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count
    FROM categories c
    LEFT JOIN categories p ON c.parent_id = p.id
    ORDER BY c.parent_id ASC, c.name ASC
");

// ساخت درخت دسته‌بندی‌ها
function build_category_tree($categories, $parent_id = null) {
    $tree = [];
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $category['children'] = build_category_tree($categories, $category['id']);
            $tree[] = $category;
        }
    }
    return $tree;
}

$category_tree = build_category_tree($categories);

// تابع نمایش درخت در select
function print_category_options($categories, $parent_id = null, $level = 0, $selected = null, $exclude = null) {
    $html = '';
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id && $category['id'] != $exclude) {
            $html .= sprintf(
                '<option value="%d" %s>%s%s</option>',
                $category['id'],
                ($selected == $category['id'] ? 'selected' : ''),
                str_repeat('─ ', $level),
                $category['name']
            );
            $html .= print_category_options($categories, $category['id'], $level + 1, $selected, $exclude);
        }
    }
    return $html;
}

require_once '../../includes/header.php';
?>

<div class="row">
    <!-- فرم افزودن/ویرایش -->
    <div class="col-md-4">
        <div class="card sticky-top" style="top: 1rem">
            <div class="card-body">
                <form method="post" onsubmit="return validateForm(this);">
                    <?php if (isset($_GET['edit'])): 
                        $id = (int)$_GET['edit'];
                        $category = db_get_row("SELECT * FROM categories WHERE id = ?", [$id]);
                        if (!$category) {
                            redirect(remove_query_arg('edit'));
                        }
                    ?>
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        
                        <h5 class="card-title mb-3">
                            <i class="bi bi-pencil-square text-primary"></i>
                            ویرایش دسته‌بندی
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">نام دسته‌بندی <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   value="<?= $category['name'] ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">دسته‌بندی والد</label>
                            <select name="parent_id" class="form-select">
                                <option value="">بدون والد</option>
                                <?= print_category_options($categories, null, 0, $category['parent_id'], $category['id']) ?>
                            </select>
                            <div class="form-text text-muted">
                                انتخاب دسته‌بندی والد اختیاری است
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3"><?= $category['description'] ?></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" name="edit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i>
                                ذخیره تغییرات
                            </button>
                            <a href="<?= remove_query_arg('edit') ?>" class="btn btn-outline-secondary">
                                <i class="bi bi-x"></i>
                                انصراف
                            </a>
                        </div>
                        
                    <?php else: ?>
                    
                        <h5 class="card-title mb-3">
                            <i class="bi bi-folder-plus text-primary"></i>
                            افزودن دسته‌بندی جدید
                        </h5>
                        
                        <div class="mb-3">
                            <label class="form-label">نام دسته‌بندی <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   value="<?= $_POST['name'] ?? '' ?>"
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">دسته‌بندی والد</label>
                            <select name="parent_id" class="form-select">
                                <option value="">بدون والد</option>
                                <?= print_category_options($categories, null, 0, $_POST['parent_id'] ?? null) ?>
                            </select>
                            <div class="form-text text-muted">
                                انتخاب دسته‌بندی والد اختیاری است
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">توضیحات</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="3"><?= $_POST['description'] ?? '' ?></textarea>
                        </div>
                        
                        <button type="submit" name="add" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i>
                            افزودن دسته‌بندی
                        </button>
                        
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <!-- لیست دسته‌بندی‌ها -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="bi bi-folder text-primary"></i>
                    لیست دسته‌بندی‌ها
                </h5>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>نام دسته‌بندی</th>
                                <th>دسته‌بندی والد</th>
                                <th>تعداد محصولات</th>
                                <th>توضیحات</th>
                                <th width="120">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td>
                                            <?php if ($category['parent_id']): ?>
                                                <i class="bi bi-dash text-muted me-2"></i>
                                            <?php endif; ?>
                                            <?= $category['name'] ?>
                                        </td>
                                        <td>
                                            <?php if ($category['parent_name']): ?>
                                                <?= $category['parent_name'] ?>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $category['products_count'] ? 'secondary' : 'light text-muted' ?>">
                                                <?= to_persian_num($category['products_count']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($category['description']): ?>
                                                <small><?= $category['description'] ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= add_query_arg('edit', $category['id']) ?>" 
                                                   class="btn btn-outline-primary"
                                                   title="ویرایش">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <?php if ($category['products_count'] == 0): ?>
                                                    <form method="post" class="d-inline" 
                                                          onsubmit="return confirmDelete('آیا از حذف دسته‌بندی «<?= $category['name'] ?>» اطمینان دارید؟')">
                                                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                        <button type="submit" 
                                                                name="delete" 
                                                                class="btn btn-outline-danger"
                                                                title="حذف">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger"
                                                            title="این دسته‌بندی دارای محصول است و قابل حذف نیست"
                                                            disabled>
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-folder-x display-6 text-muted d-block mb-3"></i>
                                        هنوز هیچ دسته‌بندی تعریف نشده است
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// اعتبارسنجی فرم
function validateForm(form) {
    let name = form.querySelector('[name="name"]').value.trim();
    
    if (!name) {
        alert('لطفاً نام دسته‌بندی را وارد کنید.');
        return false;
    }
    
    return true;
}
</script>

<?php
require_once '../../includes/footer.php';
?>