<?php
/**
 * توابع عمومی برنامه حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 */

// بررسی دسترسی
if (!defined('BASE_PATH')) {
    die('دسترسی غیرمجاز');
}

/**
 * بررسی لاگین بودن کاربر
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * بررسی ادمین بودن کاربر
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * بررسی دسترسی به صفحه
 */
function check_access() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

/**
 * ریدایرکت به یک صفحه
 */
function redirect($url) {
    header("Location: " . BASE_URL . "/{$url}");
    exit;
}

/**
 * تنظیم پیام
 */
function set_message($text, $type = 'success') {
    $_SESSION['message'] = [
        'text' => $text,
        'type' => $type
    ];
}

/**
 * دریافت پیام
 */
function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

/**
 * ذخیره در کش
 */
function set_cache($key, $value, $expire = 3600) {
    $cache = [
        'value' => $value,
        'expire' => time() + $expire
    ];
    $_SESSION['cache'][$key] = $cache;
}

/**
 * دریافت از کش
 */
function get_cache($key) {
    if (isset($_SESSION['cache'][$key])) {
        $cache = $_SESSION['cache'][$key];
        if ($cache['expire'] > time()) {
            return $cache['value'];
        }
        unset($_SESSION['cache'][$key]);
    }
    return null;
}

/**
 * حذف از کش
 */
function delete_cache($key) {
    if (isset($_SESSION['cache'][$key])) {
        unset($_SESSION['cache'][$key]);
    }
}

/**
 * فرمت تاریخ شمسی
 */
function format_date($date) {
    if (!$date) return '';
    
    $timestamp = strtotime($date);
    
    $months = [
        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
    ];
    
    $year = date('Y', $timestamp);
    $month = $months[date('n', $timestamp) - 1];
    $day = date('d', $timestamp);
    $hour = date('H:i', $timestamp);
    
    return "{$day} {$month} {$year} - {$hour}";
}

/**
 * آدرس تصویر محصول
 */
function get_product_image_url($product) {
    if (!empty($product['gallery_image'])) {
        return UPLOADS_URL . '/products/gallery/' . $product['gallery_image'];
    }
    return ASSETS_URL . '/images/no-image.png';
}

/**
 * تولید توکن CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * بررسی توکن CSRF
 */
function check_csrf_token() {
    if (!isset($_POST[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    
    if ($_POST[CSRF_TOKEN_NAME] !== $_SESSION[CSRF_TOKEN_NAME]) {
        return false;
    }
    
    return true;
}

/**
 * اعتبارسنجی فرم
 */
function validate_form($rules, $data) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        // فیلد اجباری
        if (strpos($rule, 'required') !== false && empty($data[$field])) {
            $errors[$field] = 'این فیلد الزامی است';
            continue;
        }
        
        if (empty($data[$field])) {
            continue;
        }
        
        // حداقل طول
        if (preg_match('/min:(\d+)/', $rule, $matches)) {
            $min = $matches[1];
            if (mb_strlen($data[$field]) < $min) {
                $errors[$field] = "حداقل {$min} کاراکتر وارد کنید";
            }
        }
        
        // حداکثر طول
        if (preg_match('/max:(\d+)/', $rule, $matches)) {
            $max = $matches[1];
            if (mb_strlen($data[$field]) > $max) {
                $errors[$field] = "حداکثر {$max} کاراکتر وارد کنید";
            }
        }
        
        // ایمیل
        if (strpos($rule, 'email') !== false) {
            if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = 'ایمیل معتبر نیست';
            }
        }
        
        // عدد
        if (strpos($rule, 'numeric') !== false) {
            if (!is_numeric($data[$field])) {
                $errors[$field] = 'فقط عدد وارد کنید';
            }
        }
    }
    
    return $errors;
}

/**
 * آپلود فایل
 */
function upload_file($file, $path, $allowed_types = [], $max_size = 5242880) {
    // بررسی خطاها
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'error' => 'خطا در آپلود فایل',
            'code' => $file['error']
        ];
    }
    
    // بررسی سایز
    if ($file['size'] > $max_size) {
        return [
            'error' => 'حجم فایل بیشتر از حد مجاز است',
            'code' => 'size'
        ];
    }
    
    // بررسی پسوند
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!empty($allowed_types) && !in_array($ext, $allowed_types)) {
        return [
            'error' => 'پسوند فایل مجاز نیست',
            'code' => 'type'
        ];
    }
    
    // ایجاد نام یکتا
    $filename = uniqid() . '.' . $ext;
    
    // ایجاد مسیر
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    
    // آپلود فایل
    if (!move_uploaded_file($file['tmp_name'], $path . '/' . $filename)) {
        return [
            'error' => 'خطا در ذخیره فایل',
            'code' => 'save'
        ];
    }
    
    return [
        'filename' => $filename,
        'path' => $path . '/' . $filename
    ];
}

/**
 * حذف فایل
 */
function delete_file($path) {
    if (file_exists($path)) {
        unlink($path);
    }
}

/**
 * تبدیل اعداد فارسی به انگلیسی
 */
function fa_to_en($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persian, $english, $string);
}

/**
 * تبدیل اعداد انگلیسی به فارسی
 */
function en_to_fa($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english, $persian, $string);
}

/**
 * بررسی آپدیت جدید
 */
function check_update() {
    // اگر امروز چک شده، دوباره چک نکن
    if (isset($_SESSION['update_checked']) && $_SESSION['update_checked'] == date('Y-m-d')) {
        return;
    }
    
    // آدرس فایل نسخه در مخزن
    $version_url = 'https://raw.githubusercontent.com/cofeclick1/shop_accounting/main/version.json';
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $version_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $latest = json_decode($response, true);
            if ($latest && version_compare($latest['version'], SITE_VERSION, '>')) {
                $_SESSION['update_available'] = $latest;
            } else {
                unset($_SESSION['update_available']);
            }
        }
        
        $_SESSION['update_checked'] = date('Y-m-d');
    } catch (Exception $e) {
        error_log('خطا در بررسی آپدیت: ' . $e->getMessage());
    }
}