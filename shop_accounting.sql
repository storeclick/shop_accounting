-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 20, 2025 at 04:37 PM
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
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(21, NULL, 'login', 'ورود موفق به سیستم', '::1', '2025-03-19 17:06:06'),
(22, NULL, 'login', 'ورود موفق به سیستم', '::1', '2025-03-19 19:09:46');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `subcategory` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `description`, `parent_id`, `is_active`, `created_at`, `updated_at`, `subcategory`, `image`, `category_code`) VALUES
(1, 'عمومی', 'دسته‌بندی پیش‌فرض', NULL, 1, '2025-03-19 15:04:41', NULL, NULL, NULL, NULL),
(2, 'لوازم التحریر', 'انواع لوازم التحریر', NULL, 1, '2025-03-19 15:04:41', NULL, NULL, NULL, NULL),
(3, 'لوازم اداری', 'تجهیزات و لوازم اداری', NULL, 1, '2025-03-19 15:04:41', NULL, NULL, NULL, NULL),
(4, 'لپ‌تاپ', 'انواع لپ‌تاپ‌ها', NULL, 1, '2025-03-19 17:21:28', '2025-03-19 23:18:28', NULL, '../../uploads/default.png', 'CAT-100001'),
(5, 'کامپیوتر رومیزی', 'انواع کامپیوترهای رومیزی', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100002'),
(6, 'گوشی هوشمند', 'انواع گوشی‌های هوشمند', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100003'),
(7, 'تبلت', 'انواع تبلت‌ها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100004'),
(8, 'مانیتور', 'انواع مانیتورها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100005'),
(9, 'کیبورد', 'انواع کیبوردها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100006'),
(10, 'ماوس', 'انواع ماوس‌ها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100007'),
(11, 'پرینتر', 'انواع پرینترها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100008'),
(12, 'اسکنر', 'انواع اسکنرها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100009'),
(13, 'مودم', 'انواع مودم‌ها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100010'),
(14, 'روتر', 'انواع روترها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100011'),
(15, 'هارد دیسک', 'انواع هارد دیسک‌ها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100012'),
(16, 'رم', 'انواع رم‌ها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100013'),
(17, 'کارت گرافیک', 'انواع کارت‌های گرافیک', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100014'),
(18, 'مادربورد', 'انواع مادربوردها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100015'),
(19, 'پردازنده', 'انواع پردازنده‌ها', NULL, 1, '2025-03-19 17:21:28', NULL, NULL, '../../uploads/default.png', 'CAT-100016'),
(20, 'لپ‌تاپ گیمینگ', 'لپ‌تاپ‌های مخصوص بازی', 1, 1, '2025-03-19 17:21:28', NULL, 'لپ‌تاپ', '../../uploads/default.png', 'CAT-100017'),
(21, 'لپ‌تاپ حرفه‌ای', 'لپ‌تاپ‌های حرفه‌ای', 1, 1, '2025-03-19 17:21:28', NULL, 'لپ‌تاپ', '../../uploads/default.png', 'CAT-100018'),
(22, 'لپ‌تاپ دانشجویی', 'لپ‌تاپ‌های مناسب دانشجویان', 1, 1, '2025-03-19 17:21:28', NULL, 'لپ‌تاپ', '../../uploads/default.png', 'CAT-100019'),
(23, 'کامپیوتر رومیزی گیمینگ', 'کامپیوترهای رومیزی مخصوص بازی', 2, 1, '2025-03-19 17:21:28', NULL, 'کامپیوتر رومیزی', '../../uploads/default.png', 'CAT-100020'),
(24, 'کامپیوتر رومیزی اداری', 'کامپیوترهای رومیزی مناسب محیط‌های اداری', 2, 1, '2025-03-19 17:21:28', NULL, 'کامپیوتر رومیزی', '../../uploads/default.png', 'CAT-100021'),
(25, 'گوشی هوشمند اندروید', 'گوشی‌های هوشمند با سیستم عامل اندروید', 3, 1, '2025-03-19 17:21:28', NULL, 'گوشی هوشمند', '../../uploads/default.png', 'CAT-100022'),
(26, 'گوشی هوشمند iOS', 'گوشی‌های هوشمند با سیستم عامل iOS', 3, 1, '2025-03-19 17:21:28', NULL, 'گوشی هوشمند', '../../uploads/default.png', 'CAT-100023'),
(27, 'تبلت اندروید', 'تبلت‌های با سیستم عامل اندروید', 4, 1, '2025-03-19 17:21:28', NULL, 'تبلت', '../../uploads/default.png', 'CAT-100024'),
(28, 'تبلت iOS', 'تبلت‌های با سیستم عامل iOS', 4, 1, '2025-03-19 17:21:28', NULL, 'تبلت', '../../uploads/default.png', 'CAT-100025'),
(29, 'مانیتور گیمینگ', 'مانیتورهای مخصوص بازی', 5, 1, '2025-03-19 17:21:28', NULL, 'مانیتور', '../../uploads/default.png', 'CAT-100026'),
(30, 'مانیتور حرفه‌ای', 'مانیتورهای حرفه‌ای برای کارهای گرافیکی', 5, 1, '2025-03-19 17:21:28', NULL, 'مانیتور', '../../uploads/default.png', 'CAT-100027'),
(31, 'س', 'سیب', NULL, 1, '2025-03-19 21:19:51', '2025-03-19 22:26:04', 'اصلی', '../../uploads/default.png', 'CAT-241440'),
(34, 'کیبورد 1', 'سیبسی', NULL, 1, '2025-03-19 23:12:17', '2025-03-19 23:24:59', NULL, '../../uploads/default.png', 'CAT-392383'),
(35, 'کیبورد تسکو', 'سیب', 34, 1, '2025-03-19 23:13:07', NULL, NULL, '../../uploads/default.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

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
  `reference_type` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_mobile` varchar(20) DEFAULT NULL,
  `total_amount` decimal(15,0) DEFAULT 0,
  `discount_amount` decimal(15,0) DEFAULT 0,
  `tax_amount` decimal(15,0) DEFAULT 0,
  `final_amount` decimal(15,0) DEFAULT 0,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_ref` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(15,0) NOT NULL DEFAULT 0,
  `discount` decimal(15,0) DEFAULT 0,
  `tax` decimal(15,0) DEFAULT 0,
  `total` decimal(15,0) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `purchase_price` decimal(12,0) NOT NULL DEFAULT 0,
  `sale_price` decimal(12,0) NOT NULL DEFAULT 0,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `total_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `discount_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `final_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `payment_status` enum('pending','paid','partial') NOT NULL DEFAULT 'pending',
  `payment_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(12,0) NOT NULL,
  `discount` decimal(12,0) NOT NULL DEFAULT 0,
  `total` decimal(12,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_payments`
--

CREATE TABLE `sales_payments` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `amount` decimal(12,0) NOT NULL,
  `method` varchar(20) NOT NULL DEFAULT 'cash',
  `reference` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,0) NOT NULL,
  `total_price` decimal(12,0) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key` varchar(50) NOT NULL,
  `value` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `is_public`, `updated_at`) VALUES
(1, 'site_name', 'سیستم حسابداری فروشگاه', 1, NULL),
(2, 'site_description', 'نرم‌افزار مدیریت فروشگاه و حسابداری', 1, NULL),
(3, 'app_version', '1.0.0', 1, NULL),
(4, 'currency', 'تومان', 1, NULL),
(5, 'vat_rate', '9', 1, NULL),
(6, 'show_low_stock_alert', '1', 0, NULL),
(7, 'default_category', '1', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

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
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
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
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `invoice_number_2` (`invoice_number`),
  ADD KEY `customer_mobile` (`customer_mobile`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales_payments`
--
ALTER TABLE `sales_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
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
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
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
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_payments`
--
ALTER TABLE `sales_payments`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `inventory_log_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_log_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

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
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sales_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sales_payments`
--
ALTER TABLE `sales_payments`
  ADD CONSTRAINT `sales_payments_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_payments_sale_id_fk` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
