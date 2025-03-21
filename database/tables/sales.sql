-- جدول فاکتورهای فروش
CREATE TABLE `sales` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `invoice_number` varchar(20) NOT NULL,
    `customer_name` varchar(100) DEFAULT NULL,
    `customer_mobile` varchar(11) DEFAULT NULL,
    `customer_address` text DEFAULT NULL,
    `total_amount` bigint(20) NOT NULL DEFAULT 0,
    `discount_amount` bigint(20) NOT NULL DEFAULT 0,
    `final_amount` bigint(20) NOT NULL DEFAULT 0,
    `payment_method` varchar(20) NOT NULL DEFAULT 'cash',
    `payment_ref` varchar(100) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `status` tinyint(1) NOT NULL DEFAULT 1,
    `created_by` int(11) NOT NULL,
    `updated_by` int(11) DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `invoice_number` (`invoice_number`),
    KEY `created_by` (`created_by`),
    KEY `updated_by` (`updated_by`),
    CONSTRAINT `sales_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
    CONSTRAINT `sales_updated_by_fk` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول اقلام فاکتور
CREATE TABLE `sales_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sale_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL,
    `price` bigint(20) NOT NULL,
    `discount` bigint(20) NOT NULL DEFAULT 0,
    `total` bigint(20) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `sale_id` (`sale_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `sales_items_sale_id_fk` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
    CONSTRAINT `sales_items_product_id_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- جدول تاریخچه پرداخت‌ها
CREATE TABLE `sales_payments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `sale_id` int(11) NOT NULL,
    `amount` bigint(20) NOT NULL,
    `method` varchar(20) NOT NULL DEFAULT 'cash',
    `reference` varchar(100) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `created_by` int(11) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `sale_id` (`sale_id`),
    KEY `created_by` (`created_by`),
    CONSTRAINT `sales_payments_sale_id_fk` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
    CONSTRAINT `sales_payments_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;