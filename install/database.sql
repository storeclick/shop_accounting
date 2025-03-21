-- ایجاد جدول کاربران
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL COMMENT 'نام کاربری',
    `password` varchar(255) NOT NULL COMMENT 'رمز عبور',
    `name` varchar(100) NOT NULL COMMENT 'نام و نام خانوادگی',
    `mobile` varchar(11) DEFAULT NULL COMMENT 'شماره موبایل',
    `email` varchar(100) DEFAULT NULL COMMENT 'ایمیل',
    `role` enum('admin','user') NOT NULL DEFAULT 'user' COMMENT 'نقش کاربر',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'وضعیت کاربر',
    `last_login` datetime DEFAULT NULL COMMENT 'آخرین ورود',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی',
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول کاربران';

-- ایجاد جدول دسته‌بندی محصولات
CREATE TABLE `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL COMMENT 'نام دسته‌بندی',
    `description` text DEFAULT NULL COMMENT 'توضیحات',
    `parent_id` int(11) DEFAULT NULL COMMENT 'دسته‌بندی والد',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'وضعیت',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی',
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`),
    CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول دسته‌بندی محصولات';

-- ایجاد جدول محصولات
CREATE TABLE `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(50) DEFAULT NULL COMMENT 'کد محصول',
    `barcode` varchar(50) DEFAULT NULL COMMENT 'بارکد',
    `name` varchar(255) NOT NULL COMMENT 'نام محصول',
    `description` text DEFAULT NULL COMMENT 'توضیحات',
    `category_id` int(11) DEFAULT NULL COMMENT 'دسته‌بندی',
    `purchase_price` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'قیمت خرید',
    `sale_price` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'قیمت فروش',
    `stock` int(11) NOT NULL DEFAULT 0 COMMENT 'موجودی',
    `min_stock` int(11) NOT NULL DEFAULT 0 COMMENT 'حداقل موجودی',
    `max_stock` int(11) DEFAULT NULL COMMENT 'حداکثر موجودی',
    `unit` varchar(20) DEFAULT NULL COMMENT 'واحد شمارش',
    `image` varchar(255) DEFAULT NULL COMMENT 'تصویر محصول',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'وضعیت',
    `created_by` int(11) DEFAULT NULL COMMENT 'ایجاد کننده',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی',
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `barcode` (`barcode`),
    KEY `category_id` (`category_id`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول محصولات';

-- ایجاد جدول لاگ انبار
CREATE TABLE `inventory_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL COMMENT 'محصول',
    `type` enum('in','out','adjust') NOT NULL COMMENT 'نوع تراکنش',
    `quantity` int(11) NOT NULL COMMENT 'تعداد',
    `price` decimal(12,0) DEFAULT NULL COMMENT 'قیمت واحد',
    `description` text DEFAULT NULL COMMENT 'توضیحات',
    `reference` varchar(50) DEFAULT NULL COMMENT 'شماره مرجع',
    `created_by` int(11) NOT NULL COMMENT 'ایجاد کننده',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
    CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول لاگ انبار';

-- ایجاد جدول مشتریان
CREATE TABLE `customers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL COMMENT 'نام مشتری',
    `mobile` varchar(11) DEFAULT NULL COMMENT 'شماره موبایل',
    `phone` varchar(20) DEFAULT NULL COMMENT 'شماره ثابت',
    `email` varchar(100) DEFAULT NULL COMMENT 'ایمیل',
    `address` text DEFAULT NULL COMMENT 'آدرس',
    `credit` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'اعتبار',
    `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'وضعیت',
    `created_by` int(11) DEFAULT NULL COMMENT 'ایجاد کننده',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی',
    PRIMARY KEY (`id`),
    UNIQUE KEY `mobile` (`mobile`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول مشتریان';

-- ایجاد جدول فاکتورهای فروش
CREATE TABLE `sales` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_number` varchar(20) NOT NULL COMMENT 'شماره فاکتور',
    `customer_id` int(11) DEFAULT NULL COMMENT 'مشتری',
    `total_amount` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'مبلغ کل',
    `discount_amount` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'مبلغ تخفیف',
    `tax_amount` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'مبلغ مالیات',
    `final_amount` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'مبلغ نهایی',
    `payment_status` enum('pending','paid','partial') NOT NULL DEFAULT 'pending' COMMENT 'وضعیت پرداخت',
    `payment_amount` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'مبلغ پرداختی',
    `payment_method` varchar(50) DEFAULT NULL COMMENT 'روش پرداخت',
    `description` text DEFAULT NULL COMMENT 'توضیحات',
    `created_by` int(11) DEFAULT NULL COMMENT 'ایجاد کننده',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی',
    PRIMARY KEY (`id`),
    UNIQUE KEY `invoice_number` (`invoice_number`),
    KEY `customer_id` (`customer_id`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
    CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول فاکتورهای فروش';

-- ایجاد جدول اقلام فاکتور
CREATE TABLE `sale_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sale_id` int(11) NOT NULL COMMENT 'فاکتور',
    `product_id` int(11) NOT NULL COMMENT 'محصول',
    `quantity` int(11) NOT NULL COMMENT 'تعداد',
    `price` decimal(12,0) NOT NULL COMMENT 'قیمت واحد',
    `discount` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'تخفیف',
    `tax` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'مالیات',
    `total` decimal(12,0) NOT NULL COMMENT 'مبلغ کل',
    PRIMARY KEY (`id`),
    KEY `sale_id` (`sale_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
    CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول اقلام فاکتور';

-- ایجاد جدول پرداخت‌ها
CREATE TABLE `payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sale_id` int(11) NOT NULL COMMENT 'فاکتور',
    `amount` decimal(12,0) NOT NULL COMMENT 'مبلغ',
    `method` varchar(50) NOT NULL COMMENT 'روش پرداخت',
    `reference` varchar(50) DEFAULT NULL COMMENT 'شماره مرجع',
    `description` text DEFAULT NULL COMMENT 'توضیحات',
    `created_by` int(11) DEFAULT NULL COMMENT 'ایجاد کننده',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    PRIMARY KEY (`id`),
    KEY `sale_id` (`sale_id`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
    CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول پرداخت‌ها';

-- ایجاد جدول تنظیمات
CREATE TABLE `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `key` varchar(50) NOT NULL COMMENT 'کلید',
    `value` text DEFAULT NULL COMMENT 'مقدار',
    `description` varchar(255) DEFAULT NULL COMMENT 'توضیحات',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ایجاد',
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاریخ بروزرسانی',
    PRIMARY KEY (`id`),
    UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول تنظیمات';

-- درج تنظیمات پیش‌فرض
INSERT INTO `settings` (`key`, `value`, `description`) VALUES
('site_title', 'سیستم حسابداری فروشگاه', 'عنوان سایت'),
('site_description', 'سیستم مدیریت فروش و انبارداری', 'توضیحات سایت'),
('invoice_prefix', 'INV-', 'پیشوند شماره فاکتور'),
('invoice_start', '1000', 'شماره شروع فاکتور'),
('tax_rate', '9', 'درصد مالیات'),
('currency', 'تومان', 'واحد پول'),
('low_stock_alert', '1', 'هشدار موجودی کم'),
('version', '1.0.0', 'نسخه نرم‌افزار');