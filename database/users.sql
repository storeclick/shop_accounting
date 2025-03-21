-- جدول کاربران
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'نام کاربری',
    password VARCHAR(255) NOT NULL COMMENT 'رمز عبور',
    full_name VARCHAR(100) NOT NULL COMMENT 'نام و نام خانوادگی',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'ایمیل',
    mobile VARCHAR(11) COMMENT 'شماره موبایل',
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user' COMMENT 'نقش کاربر',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'وضعیت فعال/غیرفعال',
    activation_code VARCHAR(32) COMMENT 'کد فعال‌سازی',
    email_verified TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'وضعیت تایید ایمیل',
    last_login DATETIME COMMENT 'آخرین ورود',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ثبت‌نام',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی'
);

-- اضافه کردن کاربر ادمین پیش‌فرض
INSERT INTO users (username, password, full_name, email, role, is_active, email_verified) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم', 'admin@example.com', 'admin', 1, 1);