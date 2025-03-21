<?php
/**
 * توابع عمومی برنامه حسابداری فروشگاه
 * تاریخ ایجاد: ۱۴۰۳/۰۱/۰۲
 * آخرین به‌روزرسانی: ۱۴۰۳/۰۱/۰۳
 */

// بررسی دسترسی
if (!defined('BASE_PATH')) {
    die('دسترسی غیرمجاز');
}

/**
 * بررسی لاگین بودن کاربر
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * بررسی ادمین بودن کاربر
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * بررسی دسترسی به صفحه
 * @param string|array $required_roles نقش‌های مجاز
 */
function check_access($required_roles = []) {
    if (!is_logged_in()) {
        set_message('لطفاً ابتدا وارد سیستم شوید', 'warning');
        redirect('login.php');
    }
    
    if (!empty($required_roles)) {
        if (!is_array($required_roles)) {
            $required_roles = [$required_roles];
        }
        
        if (!in_array($_SESSION['user_role'], $required_roles)) {
            set_message('شما دسترسی به این بخش را ندارید', 'error');
            redirect('index.php');
        }
    }
}

/**
 * ریدایرکت به یک صفحه
 * @param string $url آدرس مقصد
 * @param array $params پارامترهای GET
 */
function redirect($url, $params = []) {
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    header("Location: " . BASE_URL . "/{$url}");
    exit;
}

/**
 * تنظیم پیام
 * @param string $text متن پیام
 * @param string $type نوع پیام (success, error, warning, info)
 * @param bool $flash ذخیره در سشن
 */
function set_message($text, $type = 'success', $flash = true) {
    $message = [
        'text' => $text,
        'type' => $type,
        'time' => time()
    ];
    
    if ($flash) {
        $_SESSION['messages'][] = $message;
    } else {
        return $message;
    }
}

/**
 * دریافت پیام‌ها
 * @return array پیام‌های ذخیره شده
 */
function get_messages() {
    $messages = $_SESSION['messages'] ?? [];
    unset($_SESSION['messages']);
    return $messages;
}

/**
 * مدیریت کش
 */
class Cache {
    private static $instance = null;
    private $data = [];
    
    private function __construct() {
        $this->data = $_SESSION['cache'] ?? [];
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function set($key, $value, $expire = 3600) {
        $this->data[$key] = [
            'value' => $value,
            'expire' => time() + $expire
        ];
        $this->save();
    }
    
    public function get($key) {
        if (isset($this->data[$key])) {
            if ($this->data[$key]['expire'] > time()) {
                return $this->data[$key]['value'];
            }
            $this->delete($key);
        }
        return null;
    }
    
    public function delete($key) {
        unset($this->data[$key]);
        $this->save();
    }
    
    public function clear() {
        $this->data = [];
        $this->save();
    }
    
    private function save() {
        $_SESSION['cache'] = $this->data;
    }
}

/**
 * تبدیل تاریخ میلادی به شمسی
 * @param string $date تاریخ میلادی
 * @param string $format فرمت خروجی
 */
function format_date($date, $format = 'Y/m/d H:i') {
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
    
    switch ($format) {
        case 'full':
            return "{$day} {$month} {$year} ساعت {$hour}";
        case 'date':
            return "{$year}/{$month}/{$day}";
        case 'time':
            return $hour;
        default:
            return "{$year}/{$month}/{$day} - {$hour}";
    }
}

/**
 * فرمت‌بندی قیمت
 * @param int $price قیمت
 * @param bool $with_currency نمایش واحد پول
 */
function format_price($price, $with_currency = true) {
    $formatted = number_format($price, 0, '.', ',');
    return $with_currency ? "{$formatted} تومان" : $formatted;
}

/**
 * آدرس تصویر محصول
 */
function get_product_image_url($product, $size = 'medium') {
    if (!empty($product['image'])) {
        $path = "products/{$size}/{$product['image']}";
        if (file_exists(UPLOADS_PATH . '/' . $path)) {
            return UPLOADS_URL . '/' . $path;
        }
    }
    return ASSETS_URL . '/images/no-image.png';
}

/**
 * تولید و بررسی توکن CSRF
 */
class CSRF {
    public static function generate() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    public static function validate() {
        if (!isset($_POST[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        
        $valid = hash_equals($_SESSION[CSRF_TOKEN_NAME], $_POST[CSRF_TOKEN_NAME]);
        unset($_SESSION[CSRF_TOKEN_NAME]); // یک‌بار مصرف
        return $valid;
    }
}

/**
 * اعتبارسنجی فرم با قوانین پیشرفته
 */
class Validator {
    private $rules = [];
    private $messages = [];
    private $data = [];
    private $errors = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    public function rules($rules) {
        $this->rules = $rules;
        return $this;
    }
    
    public function messages($messages) {
        $this->messages = $messages;
        return $this;
    }
    
    public function validate() {
        $this->errors = [];
        
        foreach ($this->rules as $field => $rules) {
            $rules = explode('|', $rules);
            
            foreach ($rules as $rule) {
                $params = [];
                
                if (strpos($rule, ':') !== false) {
                    list($rule, $param) = explode(':', $rule);
                    $params = explode(',', $param);
                }
                
                $value = $this->data[$field] ?? null;
                
                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $this->addError($field, 'required');
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->addError($field, 'email');
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && mb_strlen($value) < $params[0]) {
                            $this->addError($field, 'min', ['min' => $params[0]]);
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && mb_strlen($value) > $params[0]) {
                            $this->addError($field, 'max', ['max' => $params[0]]);
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $this->addError($field, 'numeric');
                        }
                        break;
                        
                    case 'mobile':
                        if (!empty($value) && !preg_match('/^09[0-9]{9}$/', $value)) {
                            $this->addError($field, 'mobile');
                        }
                        break;
                }
            }
        }
        
        return empty($this->errors);
    }
    
    public function errors() {
        return $this->errors;
    }
    
    private function addError($field, $rule, $params = []) {
        $message = $this->messages[$field][$rule] ?? $this->getDefaultMessage($rule);
        
        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        
        $this->errors[$field][] = $message;
    }
    
    private function getDefaultMessage($rule) {
        $messages = [
            'required' => 'این فیلد الزامی است',
            'email' => 'ایمیل معتبر نیست',
            'min' => 'حداقل طول مجاز {min} کاراکتر است',
            'max' => 'حداکثر طول مجاز {max} کاراکتر است',
            'numeric' => 'فقط عدد مجاز است',
            'mobile' => 'شماره موبایل معتبر نیست'
        ];
        
        return $messages[$rule] ?? 'مقدار نامعتبر است';
    }
}

/**
 * آپلود فایل با امنیت بالا
 */
class FileUploader {
    private $allowed_types = [];
    private $max_size = 5242880; // 5MB
    private $upload_path = '';
    
    public function __construct($config = []) {
        $this->allowed_types = $config['allowed_types'] ?? [];
        $this->max_size = $config['max_size'] ?? 5242880;
        $this->upload_path = $config['upload_path'] ?? UPLOADS_PATH;
    }
    
    public function upload($file, $path = '') {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'error' => $this->getUploadError($file['error'])
            ];
        }
        
        // بررسی سایز
        if ($file['size'] > $this->max_size) {
            return [
                'error' => 'حجم فایل بیشتر از حد مجاز است'
            ];
        }
        
        // بررسی نوع فایل
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($this->allowed_types) && !in_array($ext, $this->allowed_types)) {
            return [
                'error' => 'پسوند فایل مجاز نیست'
            ];
        }
        
        // ایجاد مسیر
        $upload_path = rtrim($this->upload_path, '/') . '/' . trim($path, '/');
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        // نام فایل یکتا
        $filename = uniqid() . '.' . $ext;
        $filepath = $upload_path . '/' . $filename;
        
        // آپلود فایل
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'error' => 'خطا در ذخیره فایل'
            ];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'filesize' => $file['size'],
            'filetype' => $file['type']
        ];
    }
    
    private function getUploadError($code) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'حجم فایل بیشتر از حد مجاز است',
            UPLOAD_ERR_FORM_SIZE => 'حجم فایل بیشتر از حد مجاز است',
            UPLOAD_ERR_PARTIAL => 'فایل به طور کامل آپلود نشد',
            UPLOAD_ERR_NO_FILE => 'فایلی آپلود نشد',
            UPLOAD_ERR_NO_TMP_DIR => 'پوشه موقت یافت نشد',
            UPLOAD_ERR_CANT_WRITE => 'خطا در نوشتن فایل',
            UPLOAD_ERR_EXTENSION => 'آپلود توسط افزونه متوقف شد'
        ];
        
        return $errors[$code] ?? 'خطای ناشناخته در آپلود فایل';
    }
}

// توابع کمکی دیگر...
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function fa_to_en($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($persian, $english, $string);
}

function en_to_fa($string) {
    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    return str_replace($english, $persian, $string);
}

function generate_random_string($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}

function format_file_size($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}