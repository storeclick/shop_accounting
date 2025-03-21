-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2025 at 12:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop_accounting`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'نام دسته‌بندی',
  `description` text DEFAULT NULL COMMENT 'توضیحات',
  `parent_id` int(11) DEFAULT NULL COMMENT 'دسته‌بندی والد',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'وضعیت',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ایجاد',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'تاریخ بروزرسانی'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول دسته‌بندی محصولات';

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'موس', '', NULL, 1, '2025-03-20 23:09:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'نام مشتری',
  `mobile` varchar(11) DEFAULT NULL COMMENT 'شماره موبایل',
  `phone` varchar(20) DEFAULT NULL COMMENT 'شماره ثابت',
  `email` varchar(100) DEFAULT NULL COMMENT 'ایمیل',
  `address` text DEFAULT NULL COMMENT 'آدرس',
  `credit` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'اعتبار',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'وضعیت',
  `created_by` int(11) DEFAULT NULL COMMENT 'ایجاد کننده',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ایجاد',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'تاریخ بروزرسانی'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول مشتریان';

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL COMMENT 'محصول',
  `type` enum('in','out','adjust') NOT NULL COMMENT 'نوع تراکنش',
  `quantity` int(11) NOT NULL COMMENT 'تعداد',
  `price` decimal(12,0) DEFAULT NULL COMMENT 'قیمت واحد',
  `description` text DEFAULT NULL COMMENT 'توضیحات',
  `reference` varchar(50) DEFAULT NULL COMMENT 'شماره مرجع',
  `created_by` int(11) NOT NULL COMMENT 'ایجاد کننده',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ایجاد'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول لاگ انبار';

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` enum('order','adjustment') DEFAULT 'adjustment',
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `discount` decimal(12,0) DEFAULT 0,
  `final_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `payment_method` enum('cash','card','credit') DEFAULT 'cash',
  `payment_status` enum('paid','unpaid','partial') DEFAULT 'unpaid',
  `status` enum('pending','completed','canceled') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(12,0) NOT NULL,
  `discount` decimal(12,0) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL COMMENT 'فاکتور',
  `amount` decimal(12,0) NOT NULL COMMENT 'مبلغ',
  `method` varchar(50) NOT NULL COMMENT 'روش پرداخت',
  `reference` varchar(50) DEFAULT NULL COMMENT 'شماره مرجع',
  `description` text DEFAULT NULL COMMENT 'توضیحات',
  `created_by` int(11) DEFAULT NULL COMMENT 'ایجاد کننده',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ایجاد'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول پرداخت‌ها';

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
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
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ایجاد',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'تاریخ بروزرسانی'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول محصولات';

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
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
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ایجاد',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'تاریخ بروزرسانی'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول فاکتورهای فروش';

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL COMMENT 'فاکتور',
  `product_id` int(11) NOT NULL COMMENT 'محصول',
  `quantity` int(11) NOT NULL COMMENT 'تعداد',
  `price` decimal(12,0) NOT NULL COMMENT 'قیمت واحد',
  `discount` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'تخفیف',
  `tax` decimal(12,0) NOT NULL DEFAULT 0 COMMENT 'مالیات',
  `total` decimal(12,0) NOT NULL COMMENT 'مبلغ کل'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول اقلام فاکتور';

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(50) NOT NULL COMMENT 'کلید',
  `value` text DEFAULT NULL COMMENT 'مقدار',
  `description` varchar(255) DEFAULT NULL COMMENT 'توضیحات',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ایجاد',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'تاریخ بروزرسانی'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول تنظیمات';

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'site_title', 'سیستم حسابداری فروشگاه', 'عنوان سایت', '2025-03-20 19:15:34', NULL),
(2, 'site_description', 'سیستم مدیریت فروش و انبارداری', 'توضیحات سایت', '2025-03-20 19:15:34', NULL),
(3, 'invoice_prefix', 'INV-', 'پیشوند شماره فاکتور', '2025-03-20 19:15:34', NULL),
(4, 'invoice_start', '1000', 'شماره شروع فاکتور', '2025-03-20 19:15:34', NULL),
(5, 'tax_rate', '9', 'درصد مالیات', '2025-03-20 19:15:34', NULL),
(6, 'currency', 'تومان', 'واحد پول', '2025-03-20 19:15:34', NULL),
(7, 'low_stock_alert', '1', 'هشدار موجودی کم', '2025-03-20 19:15:34', NULL),
(8, 'version', '1.0.0', 'نسخه نرم‌افزار', '2025-03-20 19:15:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL COMMENT 'نام کاربری',
  `password` varchar(255) NOT NULL COMMENT 'رمز عبور',
  `name` varchar(100) NOT NULL COMMENT 'نام و نام خانوادگی',
  `mobile` varchar(11) DEFAULT NULL COMMENT 'شماره موبایل',
  `email` varchar(100) DEFAULT NULL COMMENT 'ایمیل',
  `role` enum('admin','user') NOT NULL DEFAULT 'user' COMMENT 'نقش کاربر',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'وضعیت کاربر',
  `last_login` datetime DEFAULT NULL COMMENT 'آخرین ورود',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'تاریخ ایجاد',
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'تاریخ بروزرسانی'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci COMMENT='جدول کاربران';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `mobile`, `email`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'akradim', '$2y$10$xCi5CmtvcujmGyR7bpzVOepZhDelVRYOfAoo79kYrD7c9XRUXRQn2', 'مصطفی اکرادی', '09911785401', NULL, 'admin', 1, '2025-03-20 19:20:26', '2025-03-20 19:15:54', '2025-03-20 19:20:26'),
(2, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم', NULL, 'admin@example.com', 'admin', 1, NULL, '2025-03-21 00:47:13', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_category_parent` (`parent_id`),
  ADD KEY `idx_category_status` (`status`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mobile` (`mobile`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE NO ACTION,
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE NO ACTION;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_images_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
