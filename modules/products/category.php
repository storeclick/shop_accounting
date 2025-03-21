<?php
/**
 * مدیریت دسته‌بندی محصولات
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۱
 */

require_once '../../config/config.php';
check_access();

$page_title = 'مدیریت دسته‌بندی محصولات';

// بررسی درخواست‌های POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // افزودن دسته‌بندی جدید
    if (isset($_POST['add'])) {
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        if (empty($name)) {
            show_message('لطفاً نام دسته‌بندی را وارد کنید', 'error');
        } else {
            // بررسی تکراری نبودن نام
            $exists = db_get_row("SELECT id FROM categories WHERE name = ?", [$name]);
            
            if ($exists) {
                show_message('این نام دسته‌بندی قبلاً ثبت شده است', 'warning');
            } else {
                $result = db_query("
                    INSERT INTO categories (name, description, created_at)
                    VALUES (?, ?, NOW())
                ", [$name, $description]);
                
                if ($result) {
                    log_action('add_category', sprintf('افزودن دسته‌بندی: %s', $name));
                    show_message('دسته‌بندی با موفقیت ایجاد شد');
                    redirect('category.php');
                } else {
                    show_message('خطا در ثبت دسته‌بندی', 'error');
                }
            }
        }
    }
    
    // ویرایش دسته‌بندی
    elseif (isset($_POST['edit'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        
        if (empty($name)) {
            show_message('لطفاً نام دسته‌بندی را وارد کنید', 'error');
        } else {
            // بررسی تکراری نبودن نام
            $exists = db_get_row("
                SELECT id FROM categories 
                WHERE name = ? AND id != ?
            ", [$name, $id]);
            
            if ($exists) {
                show_message('این نام دسته‌بندی قبلاً ثبت شده است', 'warning');
            } else {
                $result = db_query("
                    UPDATE categories 
                    SET name = ?, description = ?, updated_at = NOW()
                    WHERE id = ?
                ", [$name, $description, $id]);
                
                if ($result) {
                    log_action('edit_category', sprintf('ویرایش دسته‌بندی: %s', $name));
                    show_message('دسته‌بندی با موفقیت ویرایش شد');
                    redirect('category.php');
                } else {
                    show_message('خطا در ویرایش دسته‌بندی', 'error');
                }
            }
        }
    }
    
    // حذف دسته‌بندی
    elseif (isset($_POST['delete'])) {
        $id = (int)$_POST['id'];
        
        // بررسی استفاده در محصولات
        $used = db_get_row("
            SELECT COUNT(*) as count 
            FROM products 
            WHERE category_id = ?
        ", [$id]);
        
        if ($used['count'] > 0) {
            show_message('این دسته‌بندی در محصولات استفاده شده و قابل حذف نیست', 'warning');
        } else {
            $category = db_get_row("SELECT * FROM categories WHERE id = ?", [$id]);
            
            $result = db_query("DELETE FROM categories WHERE id = ?", [$id]);
            
            if ($result) {
                log_action('delete_category', sprintf('حذف دسته‌بندی: %s', $category['name']));
                show_message('دسته‌بندی با موفقیت حذف شد');
                redirect('category.php');
            } else {
                show_message('خطا در حذف دسته‌بندی', 'error');
            }
        }
    }
}

// دریافت لیست دسته‌بندی‌ها
$categories = db_get_rows("
    SELECT c.*, 
           (SELECT COUNT(*) FROM products WHERE category_id = c.id) as products_count
    FROM categories c
    ORDER BY c.name ASC
");
// modules/products/category.php - بهینه‌سازی کوئری اصلی
$categories = db_get_rows("
    SELECT c.*, 
           COUNT(p.id) as products_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
");

require_once '../../includes/header.php';
?>

<div class="row">
    <!-- فرم افزودن/ویرایش -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <?php if (isset($_GET['edit'])): 
                    $id = (int)$_GET['edit'];
                    $category = db_get_row("SELECT * FROM categories WHERE id = ?", [$id]);
                    if (!$category) {
                        redirect('category.php');
                    }
                ?>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        
                        <h5 class="card-title mb-4">ویرایش دسته‌بندی</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">نام دسته‌بندی</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control" 
                                   value="<?= $category['name'] ?>" 
                                   required>
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
                            
                            <a href="category.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x"></i>
                                انصراف
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <form method="post">
                        <h5 class="card-title mb-4">افزودن دسته‌بندی جدید</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">نام دسته‌بندی</label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control"
                                   value="<?= $_POST['name'] ?? '' ?>" 
                                   required>
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
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- لیست دسته‌بندی‌ها -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">لیست دسته‌بندی‌ها</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>نام دسته‌بندی</th>
                                <th>تعداد محصولات</th>
                                <th>توضیحات</th>
                                <th>تاریخ ایجاد</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($categories): ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?= $category['name'] ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
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
                                            <small class="text-muted">
                                                <?= format_date($category['created_at']) ?>
                                            </small>
                                        </td>
                                        <td class="actions">
                                            <a href="?edit=<?= $category['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            <?php if ($category['products_count'] == 0): ?>
                                                <form method="post" class="d-inline"
                                                      onsubmit="return confirmDelete('آیا از حذف این دسته‌بندی اطمینان دارید؟')">
                                                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                    <button type="submit" 
                                                            name="delete" 
                                                            class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-folder-x fs-1 text-muted d-block mb-2"></i>
                                        هیچ دسته‌بندی تعریف نشده است
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

<?php
require_once '../../includes/footer.php';
?>