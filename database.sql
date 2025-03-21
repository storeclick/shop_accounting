-- ایجاد دیتابیس با پشتیبانی کامل از زبان فارسی
CREATE DATABASE IF NOT EXISTS shop_accounting
CHARACTER SET utf8mb4 
COLLATE utf8mb4_persian_ci;

USE shop_accounting;

-- جدول کاربران
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL COMMENT 'نام و نام خانوادگی',
    mobile VARCHAR(11) DEFAULT NULL COMMENT 'شماره موبایل',
    email VARCHAR(100) DEFAULT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    permissions TEXT DEFAULT NULL COMMENT 'دسترسی‌های کاربر',
    status TINYINT(1) NOT NULL DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    login_attempts INT DEFAULT 0,
    reset_token VARCHAR(100) DEFAULT NULL,
    reset_token_expire DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_username (username),
    UNIQUE KEY unique_mobile (mobile),
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول دسته‌بندی محصولات
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parent (parent_id),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    CONSTRAINT fk_category_parent FOREIGN KEY (parent_id) 
        REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول محصولات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT DEFAULT NULL,
    code VARCHAR(50) NOT NULL,
    barcode VARCHAR(50) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    features TEXT,
    unit VARCHAR(20) NOT NULL DEFAULT 'عدد',
    purchase_price DECIMAL(15,0) NOT NULL DEFAULT 0,
    sale_price DECIMAL(15,0) NOT NULL DEFAULT 0,
    wholesale_price DECIMAL(15,0) DEFAULT NULL,
    min_price DECIMAL(15,0) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    min_stock INT NOT NULL DEFAULT 0,
    max_stock INT DEFAULT NULL,
    weight DECIMAL(10,2) DEFAULT NULL,
    dimensions VARCHAR(50) DEFAULT NULL,
    color VARCHAR(50) DEFAULT NULL,
    size VARCHAR(50) DEFAULT NULL,
    manufacturer VARCHAR(100) DEFAULT NULL,
    shelf_number VARCHAR(50) DEFAULT NULL,
    status ENUM('active','inactive','out_of_stock') NOT NULL DEFAULT 'active',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    views INT NOT NULL DEFAULT 0,
    sales_count INT NOT NULL DEFAULT 0,
    last_sale DATETIME DEFAULT NULL,
    last_purchase DATETIME DEFAULT NULL,
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_code (code),
    UNIQUE KEY unique_barcode (barcode),
    UNIQUE KEY unique_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (is_featured),
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) 
        REFERENCES categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_product_creator FOREIGN KEY (created_by) 
        REFERENCES users(id),
    CONSTRAINT fk_product_updater FOREIGN KEY (updated_by) 
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول تصاویر محصولات
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    alt VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    CONSTRAINT fk_image_product FOREIGN KEY (product_id) 
        REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول مشتریان
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    company VARCHAR(100) DEFAULT NULL,
    national_id VARCHAR(10) DEFAULT NULL,
    economic_code VARCHAR(20) DEFAULT NULL,
    mobile VARCHAR(11) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    province VARCHAR(50) DEFAULT NULL,
    city VARCHAR(50) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    postal_code VARCHAR(10) DEFAULT NULL,
    credit_limit DECIMAL(15,0) DEFAULT 0,
    total_purchases DECIMAL(15,0) DEFAULT 0,
    last_purchase_date DATETIME DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_code (code),
    UNIQUE KEY unique_mobile (mobile),
    UNIQUE KEY unique_national_id (national_id),
    INDEX idx_status (status),
    CONSTRAINT fk_customer_creator FOREIGN KEY (created_by) 
        REFERENCES users(id),
    CONSTRAINT fk_customer_updater FOREIGN KEY (updated_by) 
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول تراکنش‌های انبار
CREATE TABLE inventory_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    type ENUM('in','out','adjustment') NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15,0) DEFAULT NULL,
    stock_before INT NOT NULL,
    stock_after INT NOT NULL,
    reference_type VARCHAR(50) DEFAULT NULL,
    reference_id INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    INDEX idx_type (type),
    INDEX idx_reference (reference_type, reference_id),
    CONSTRAINT fk_transaction_product FOREIGN KEY (product_id) 
        REFERENCES products(id),
    CONSTRAINT fk_transaction_creator FOREIGN KEY (created_by) 
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول تغییرات قیمت
CREATE TABLE price_changes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    field ENUM('purchase_price','sale_price','wholesale_price') NOT NULL,
    old_price DECIMAL(15,0) NOT NULL,
    new_price DECIMAL(15,0) NOT NULL,
    reason TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    CONSTRAINT fk_price_change_product FOREIGN KEY (product_id) 
        REFERENCES products(id),
    CONSTRAINT fk_price_change_creator FOREIGN KEY (created_by) 
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول فروش‌ها
CREATE TABLE sales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(20) NOT NULL,
    customer_id INT DEFAULT NULL,
    sale_date DATE NOT NULL,
    due_date DATE DEFAULT NULL,
    total_items INT NOT NULL DEFAULT 0,
    subtotal DECIMAL(15,0) NOT NULL DEFAULT 0,
    discount_type ENUM('fixed','percent') DEFAULT NULL,
    discount_value DECIMAL(15,0) DEFAULT NULL,
    discount_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
    tax_rate DECIMAL(5,2) DEFAULT NULL,
    tax_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
    shipping_cost DECIMAL(15,0) NOT NULL DEFAULT 0,
    total_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
    payment_status ENUM('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
    payment_method ENUM('cash','card','online','credit') NOT NULL DEFAULT 'cash',
    payment_ref VARCHAR(100) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    status ENUM('draft','pending','completed','cancelled') NOT NULL DEFAULT 'pending',
    created_by INT NOT NULL,
    updated_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_invoice (invoice_number),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_payment (payment_status),
    CONSTRAINT fk_sale_customer FOREIGN KEY (customer_id) 
        REFERENCES customers(id) ON DELETE SET NULL,
    CONSTRAINT fk_sale_creator FOREIGN KEY (created_by) 
        REFERENCES users(id),
    CONSTRAINT fk_sale_updater FOREIGN KEY (updated_by) 
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول اقلام فروش
CREATE TABLE sale_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(15,0) NOT NULL,
    total_price DECIMAL(15,0) NOT NULL,
    discount_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
    final_price DECIMAL(15,0) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sale (sale_id),
    INDEX idx_product (product_id),
    CONSTRAINT fk_sale_item_sale FOREIGN KEY (sale_id) 
        REFERENCES sales(id) ON DELETE CASCADE,
    CONSTRAINT fk_sale_item_product FOREIGN KEY (product_id) 
        REFERENCES products(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول پرداخت‌ها
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    amount DECIMAL(15,0) NOT NULL,
    method ENUM('cash','card','online','credit') NOT NULL,
    reference VARCHAR(100) DEFAULT NULL,
    status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sale (sale_id),
    CONSTRAINT fk_payment_sale FOREIGN KEY (sale_id) 
        REFERENCES sales(id),
    CONSTRAINT fk_payment_creator FOREIGN KEY (created_by) 
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول تنظیمات
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    `key` VARCHAR(50) NOT NULL,
    value TEXT DEFAULT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'string',
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    description VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول لاگ فعالیت‌ها
CREATE TABLE activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    activity VARCHAR(255) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_type (type),
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) 
        REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول بروزرسانی‌ها
CREATE TABLE updates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    version VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    sql_file VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending',
    error_log TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    UNIQUE KEY unique_version (version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- درج داده‌های اولیه

-- کاربر مدیر پیش‌فرض
INSERT INTO users (
    username, 
    password, 
    name, 
    role, 
    status
) VALUES (
    'akrdim',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- رمز: 6678232
    'مدیر سیستم',
    'admin',
    1
);

-- دسته‌بندی پیش‌فرض
INSERT INTO categories (
    name,
    slug,
    description,
    sort_order
) VALUES (
    'عمومی',
    'general',
    'دسته‌بندی پیش‌فرض محصولات',
    999
);

-- تنظیمات پایه
INSERT INTO settings (`key`, value, type, description) VALUES
('site_title', 'سیستم حسابداری فروشگاه', 'string', 'عنوان سایت'),
('site_description', 'نرم‌افزار مدیریت فروش و انبارداری', 'string', 'توضیحات سایت'),
('tax_rate', '9', 'number', 'درصد مالیات'),
('invoice_prefix', 'INV-', 'string', 'پیشوند شماره فاکتور'),
('invoice_start', '1000', 'number', 'شماره شروع فاکتور'),
('low_stock_threshold', '5', 'number', 'حد نصاب هشدار موجودی'),
('currency', 'تومان', 'string', 'واحد پول'),
('theme', 'default', 'string', 'قالب پیش‌فرض'),
('version', '1.0.0', 'string', 'نسخه نرم‌افزار');