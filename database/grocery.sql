-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 21, 2026 at 06:57 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `grocery`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','replied','closed') DEFAULT 'pending',
  `admin_reply` text,
  `replied_by` int DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `status`, `admin_reply`, `replied_by`, `replied_at`, `created_at`) VALUES
(1, 'sqed423', 'eeqe@gmail.cpm', 'dqwd', 'general', 'ewe', 'pending', NULL, NULL, NULL, '2026-03-20 16:37:42'),
(2, 'dada', 'pmalviya375@rku.ac.in', '1234567890', 'general', 'jay Swaminarayan\r\nJay GeliAmbe ma', 'replied', 'jay Swaminarayan\r\nJay GeliAmbe ma', 14, '2026-03-20 22:27:48', '2026-03-20 16:54:36'),
(3, 'jay', 'pmalviya375@rku.ac.in', '1234567890', 'order', 'qwertyuioplkmjnbfd', 'pending', NULL, NULL, NULL, '2026-03-20 16:57:21'),
(4, 'jay', 'pmalviya375@rku.ac.in', '1234567890', 'order', 'qwertyuioplkmjnbfd', 'pending', NULL, NULL, NULL, '2026-03-20 17:12:07'),
(5, 'mnm', 'pmalviya375@rku.ac.in', '1234567899', 'general', '1234567890hgghnb', 'pending', NULL, NULL, NULL, '2026-03-20 17:13:01'),
(6, 'bfdhdh', 'pmalviya375@rku.ac.in', '1234567890', 'general', 'dsfegdgsgafaaga', 'replied', 'rawtwyrsysys', 14, '2026-03-30 08:51:42', '2026-03-30 03:21:20');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `offer_title` varchar(200) NOT NULL,
  `discount_display` varchar(50) DEFAULT NULL,
  `description` text,
  `discount_type` enum('percentage','flat') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT '0.00',
  `max_discount` decimal(10,2) DEFAULT '0.00',
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `usage_limit` int DEFAULT '0',
  `per_user_limit` int DEFAULT '1',
  `used_count` int DEFAULT '0',
  `apply_to` enum('all','category','products') DEFAULT 'all',
  `badge_bg_color` varchar(20) DEFAULT '#dc3545',
  `badge_text_color` varchar(20) DEFAULT '#ffffff',
  `custom_badge_text` varchar(100) DEFAULT NULL,
  `category_id` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `coupon_code`, `offer_title`, `discount_display`, `description`, `discount_type`, `discount_value`, `min_purchase`, `max_discount`, `valid_from`, `valid_to`, `usage_limit`, `per_user_limit`, `used_count`, `apply_to`, `badge_bg_color`, `badge_text_color`, `custom_badge_text`, `category_id`, `status`, `created_at`, `updated_at`) VALUES
(4, '', 'Summer dhadaka', NULL, 'summer dhadaka', 'percentage', 90.02, 0.00, 0.00, '2026-04-20 23:54:00', '2026-04-23 23:54:00', 0, 1, 0, 'category', '#dc3545', '#ffffff', NULL, 'Organic Fruits', 'active', '2026-04-21 18:24:47', '2026-04-21 18:31:17');

-- --------------------------------------------------------

--
-- Table structure for table `offer_products`
--

CREATE TABLE `offer_products` (
  `id` int NOT NULL,
  `offer_id` int NOT NULL,
  `product_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_mobile` varchar(15) NOT NULL,
  `customer_address` text NOT NULL,
  `payment_method` enum('COD','Online') NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `gst_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending',
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `razorpay_order_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `customer_name`, `customer_mobile`, `customer_address`, `payment_method`, `payment_status`, `subtotal`, `gst_amount`, `total_amount`, `order_status`, `order_date`, `updated_date`, `razorpay_payment_id`, `razorpay_order_id`) VALUES
(1, 23, 'ORD-69DE203AB5EFC', 'awfaf', '1234567890', 'fagfagaga', 'COD', 'pending', 80.00, 14.40, 94.40, 'cancelled', '2026-04-14 11:08:42', '2026-04-14 11:24:56', NULL, NULL),
(2, 23, 'ORD-20260414-4CAD47', 'dsfhgjhkj', '1234567890', 'xfcgvhbj', 'COD', 'pending', 1871250.00, 336825.00, 2208075.00, 'confirmed', '2026-04-14 11:23:48', '2026-04-14 11:24:00', NULL, NULL),
(3, 23, 'ORD-20260414-CBFEA1', 'gvh', '1234567890', 'dtfgh', 'COD', 'pending', 18000.00, 3240.00, 21240.00, 'cancelled', '2026-04-14 11:26:20', '2026-04-14 11:26:57', NULL, NULL),
(4, 23, 'ORD-20260415-04AF28', 'priyank', '1234567890', 'csfsfsfs', 'COD', 'pending', 200.00, 36.00, 236.00, 'cancelled', '2026-04-15 02:42:56', '2026-04-15 02:46:26', NULL, NULL),
(5, 23, 'ORD-20260415-BF0E94', 'asf', '1234567890', 'xsdfghjkl', 'Online', 'pending', 12000.00, 2160.00, 14160.00, 'pending', '2026-04-15 02:45:47', '2026-04-15 02:45:47', NULL, NULL),
(6, 23, 'ORD-20260415-0158C0', 'fhxhfhd', '1234567890', 'sfdgfhgjhkj', 'Online', 'pending', 20000.00, 3600.00, 23600.00, 'cancelled', '2026-04-15 02:47:28', '2026-04-15 02:48:29', NULL, NULL),
(7, 23, 'ORD69E60ADEBEE10', 'priyank malaviya', '9979878965', 'sEdadfsfsa', 'COD', 'pending', 840.00, 151.20, 991.20, 'pending', '2026-04-20 11:15:42', '2026-04-20 11:15:42', NULL, NULL),
(8, 23, 'ORD69E6144C7ADC1', 'priyank malaviya', '9979878965', 'sfdgfhgjhjk', 'Online', 'paid', 120.00, 21.60, 141.60, 'confirmed', '2026-04-20 11:55:56', '2026-04-20 11:55:56', NULL, NULL),
(9, 23, 'ORD202604201619', 'priyank malaviya', '9979878965', 'GADSGS', 'COD', 'pending', 40.00, 2.00, 42.00, 'pending', '2026-04-20 11:58:28', '2026-04-20 11:58:28', NULL, NULL),
(10, 23, 'ORD69E61605A602B', 'priyank malaviya', '9979878965', 'sdsfghj', 'Online', 'paid', 368.00, 66.24, 434.24, 'confirmed', '2026-04-20 12:03:17', '2026-04-20 12:03:17', NULL, NULL),
(11, 23, 'ORD69E617E0D3908', 'priyank malaviya', '9979878965', 'cjk', 'Online', 'paid', 40.00, 7.20, 47.20, 'confirmed', '2026-04-20 12:11:12', '2026-04-20 12:11:12', NULL, NULL),
(12, 23, 'ORD202604201278CE', 'priyank malaviya', '9979878965', 'bfnfn', 'Online', 'paid', 40.00, 7.20, 47.20, 'confirmed', '2026-04-20 12:17:53', '2026-04-20 12:17:53', NULL, NULL),
(13, 23, 'ORD-20260420-31AF3B', 'sfasf', '1234567890', 'fgxfdhdh', 'COD', 'pending', 80.00, 14.40, 94.40, 'pending', '2026-04-20 16:33:55', '2026-04-20 16:33:55', NULL, NULL),
(16, 23, 'ORD-20260420-530B43', 'asfsf', '1234567890', 'sdggdsg, sdgdg - 123225', 'Online', 'pending', 240.00, 43.20, 283.20, 'pending', '2026-04-20 16:51:33', '2026-04-20 16:51:35', NULL, 'order_SfordnZxezxCLi'),
(17, 23, 'ORD-20260420-A64B4F', 'asfsf', '1234567890', 'sdggdsg, sdgdg - 123225', 'Online', 'pending', 240.00, 43.20, 283.20, 'pending', '2026-04-20 16:51:38', '2026-04-20 16:51:40', NULL, 'order_SforjFuXurON5Y'),
(18, 23, 'ORD-20260420-2382B9', 'asfsf', '1234567890', 'sdggdsg, sdgdg - 123225', 'COD', 'pending', 240.00, 43.20, 283.20, 'pending', '2026-04-20 16:51:46', '2026-04-20 16:51:46', NULL, NULL),
(19, 23, 'ORD-20260421-243539', 'dadd', '1234567890', 'dsafsgdhfjg', 'Online', 'paid', 48750.00, 2437.50, 51187.50, 'pending', '2026-04-21 17:38:10', '2026-04-21 17:38:10', 'pay_SgEBd4k9S4UClz', 'order_SgEBLVqT4FABjO'),
(20, 23, 'ORD-20260421-EB01D8', 'Cc', '1234567890', 'sdfgh', 'COD', 'pending', 80.00, 4.00, 84.00, 'confirmed', '2026-04-21 17:40:14', '2026-04-21 17:50:35', NULL, NULL),
(21, 23, 'ORD-20260421-5B14D0', 'fsf', '1234567890', 'dsdfgh', 'COD', 'pending', 333870.00, 16693.50, 350563.50, 'pending', '2026-04-21 18:44:53', '2026-04-21 18:44:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `weight`, `subtotal`) VALUES
(1, 1, 7, 'Badshah Kitchen King Masala', 2, 40.00, '1', 80.00),
(2, 2, 8, 'Ankur Filtered Groundnut Oil', 499, 3750.00, '15', 1871250.00),
(3, 3, 9, 'Potato - New Crop (Aloo)', 450, 40.00, '1', 18000.00),
(4, 4, 7, 'Badshah Kitchen King Masala', 5, 40.00, '1', 200.00),
(5, 5, 7, 'Badshah Kitchen King Masala', 300, 40.00, '1', 12000.00),
(6, 6, 9, 'Potato - New Crop (Aloo)', 500, 40.00, '1', 20000.00),
(7, 7, 2, 'Red Apple', 6, 120.00, '1Kg', 720.00),
(8, 7, 7, 'Badshah Kitchen King Masala', 3, 40.00, '1pcs', 120.00),
(9, 8, 9, 'Potato - New Crop (Aloo)', 3, 40.00, '1kg', 120.00),
(10, 9, 7, 'Badshah Kitchen King Masala', 1, 40.00, '1pcs', 40.00),
(11, 10, 6, 'Ambari Byadgi Chilli Powder', 2, 184.00, '500gm', 368.00),
(12, 11, 7, 'Badshah Kitchen King Masala', 1, 40.00, '1pcs', 40.00),
(13, 12, 7, 'Badshah Kitchen King Masala', 1, 40.00, '1pcs', 40.00),
(14, 13, 9, 'Potato - New Crop (Aloo)', 2, 40.00, '1', 80.00),
(15, 18, 9, 'Potato - New Crop (Aloo)', 6, 40.00, '1', 240.00),
(16, 19, 8, 'Ankur Filtered Groundnut Oil', 13, 3750.00, '15', 48750.00),
(17, 20, 7, 'Badshah Kitchen King Masala', 2, 40.00, '1', 80.00),
(18, 21, 2, 'Red Apple', 1, 120.00, '1', 120.00),
(19, 21, 8, 'Ankur Filtered Groundnut Oil', 89, 3750.00, '15', 333750.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `razorpay_payment_id`, `razorpay_order_id`, `amount`, `status`, `created_at`) VALUES
(1, 8, 'pay_Sfjp0ToL08g5UL', 'order_Sfjomx9w2PehDE', 141.60, 'success', '2026-04-20 11:55:56'),
(2, 10, 'pay_SfjworjrVOx0Uh', 'order_SfjwjZNaQRtvxF', 434.24, 'success', '2026-04-20 12:03:17'),
(3, 11, 'pay_Sfk55xJiUDAScC', 'order_Sfk50HpxPFIKme', 47.20, 'success', '2026-04-20 12:11:12'),
(4, 12, 'pay_SfkCDRpnjZM5HB', 'order_SfkC7mw5hfsaNA', 47.20, 'success', '2026-04-20 12:17:53');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `weight` varchar(50) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('in-stock','out-of-stock') DEFAULT 'in-stock',
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `category`, `price`, `weight`, `stock`, `description`, `image`, `status`, `created_date`, `updated_date`) VALUES
(2, 'Red Apple', 'Organic Fruits', 120.00, '1Kg', 193, 'This apple is red and Organic', '1772682602_apple.jpg', 'in-stock', '2026-03-05 03:50:02', '2026-04-21 18:44:53'),
(3, 'Fresh Milk', 'Dairy & Bekary', 60.00, '1L', 500, 'desi cow milk', '1772682685_Fresh Milk.jpg', 'in-stock', '2026-03-05 03:51:25', '2026-03-22 02:34:17'),
(4, 'Potato Chips', 'snacks', 40.00, '1pcs', 500, 'Lays Potato chips', '1772682774_Potato Chips.jpg', 'in-stock', '2026-03-05 03:52:54', '2026-03-05 03:52:54'),
(5, 'Amul Cheese Cubes', 'Dairy & Bekary', 40.00, '1pcs', 100, 'Amul cheese', '1772682832_Cheese.jpg', 'in-stock', '2026-03-05 03:53:52', '2026-03-22 02:34:31'),
(6, 'Ambari Byadgi Chilli Powder', 'masala', 184.00, '500gm', 298, 'Ambari Byadgi Chilli Powder', '1772682914_Ambari Byadgi Chilli Powder.jpg', 'in-stock', '2026-03-05 03:55:14', '2026-04-20 12:03:17'),
(7, 'Badshah Kitchen King Masala', 'masala', 40.00, '1pcs', 93, 'Badshah Kitchen King Masala', '1772682960_Badshah Kitchen King Masala.jpg', 'in-stock', '2026-03-05 03:56:00', '2026-04-21 17:50:35'),
(8, 'Ankur Filtered Groundnut Oil', 'Oil', 3750.00, '15L', 20, 'Ankur Filtered Groundnut Oil', '1772683020_Ankur Filtered Groundnut Oil.jpg', 'in-stock', '2026-03-05 03:57:00', '2026-04-21 18:45:28'),
(9, 'Potato - New Crop (Aloo)', 'Fresh Vegetables', 40.00, '1kg', 489, 'Potato - New Crop (Aloo)', '1772683082_patato.jpg', 'in-stock', '2026-03-05 03:58:02', '2026-04-20 16:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int NOT NULL,
  `image` varchar(255) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `image`, `category_name`, `description`, `status`, `created_date`) VALUES
(1, '1772601934_Vegetables.jpg', 'Fresh Vegetables', 'Fresh Vegetables Best', 'Active', '2026-03-04 05:25:34'),
(2, '1772602085_Organic Fruits.jpg', 'Organic Fruits', 'Organic Fruits Is Best', 'Active', '2026-03-04 05:28:05'),
(3, '1774146588_masala.jpg', 'Masala', 'Masala is Good', 'Active', '2026-03-22 02:29:48'),
(4, '1774146692_Dairy.jpg', 'Dairy & Bekary', 'Dairy & Bekary Products', 'Active', '2026-03-22 02:31:32'),
(5, '1774146764_Snacks & Chips.jpg', 'Snacks', 'Snacks is Goods', 'Active', '2026-03-22 02:32:44'),
(6, '1774146840_Ankur Filtered Groundnut Oil.jpg', 'Oil', 'Oil is for Good', 'Active', '2026-03-22 02:34:00');

-- --------------------------------------------------------

--
-- Table structure for table `register`
--

CREATE TABLE `register` (
  `id` int NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `mobile` bigint DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `profile_picture` text,
  `role` varchar(30) DEFAULT 'user',
  `status` varchar(30) DEFAULT 'Inactive',
  `token` text,
  `token_expiry` datetime DEFAULT NULL,
  `reset_otp` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `otp_attempts` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `register`
--

INSERT INTO `register` (`id`, `first_name`, `last_name`, `email`, `password`, `mobile`, `gender`, `profile_picture`, `role`, `status`, `token`, `token_expiry`, `reset_otp`, `otp_expiry`, `otp_attempts`, `created_at`, `updated_at`) VALUES
(6, 'Priyank', 'Malaviya', 'priyankmalaviya111@gamil.com', '$2y$10$NMV4EewCq4kudbXBdRe3m.N4xUTFykpgTo130zZ.Zx0JJHamiTcr.', 1234567890, 'Male', NULL, 'user', 'Inactive', '2d916308fe33da9b4d1899b48460c354', NULL, NULL, NULL, 0, '2026-03-10 18:40:38', '2026-03-10 18:41:26'),
(14, 'Priyank', 'Malaviya', 'priyankmalaviya111@gmail.com', '$2y$10$hhSvJ6UgEQdAAqUL5KCLyenC6e.3vz6i7eZisPIbLUdH/EtPtnWHK', 1234567890, 'Male', 'admin_14_1774235523.jpeg', 'admin', 'Active', NULL, NULL, NULL, NULL, 0, '2026-03-12 10:49:52', '2026-03-23 03:12:03'),
(20, 'akhil', 'visani', 'avisani652@rku.ac.in', '$2y$10$fa7JVotk50OxmMERkJRLJuRTl4VKMV/ac9CCRrl91IkzFb1QDlEwO', 1234567890, 'Male', NULL, 'user', 'Active', 'eae90611a19bff685fe0eff5d68889cc', NULL, NULL, NULL, 0, '2026-03-19 08:17:12', '2026-03-22 03:11:53'),
(22, 'priyank', 'malaviya', 'priyankmalaviya3654@gmail.com', '$2y$10$JJ2QLIoCLkNlwuAz4JwiMOtRjH5e57/E7.aVRtZuyr8YAC8W8F0Ai', 1234567890, 'Male', 'admin_22_1774172917.jpg', 'admin', 'Active', '2c06316550515aa4d043aceed2b0c5ec', NULL, NULL, NULL, 0, '2026-03-22 09:19:23', '2026-03-22 09:48:59'),
(23, 'priyank', 'malaviya', 'pmalviya375@rku.ac.in', '$2y$10$upC99H0QxB7.28DjVgVMmuElgMJxWLcfLbBdlPAlahUANYi/GSNCG', 9979878965, 'Male', 'uploads/profiles/profile_23_1775021648.jpg', 'user', 'Active', 'eb28e36d1f3ecf5cf600d3b96ed537872e25425640978c16b98d202be7dce354', NULL, NULL, NULL, 0, '2026-04-01 05:17:10', '2026-04-01 05:35:05');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
(1, 'shop_name', 'BharatMart', 'text', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(2, 'shop_tagline', 'Your Fresh Grocery Store', 'text', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(3, 'shop_address', '123 Grocery Street, City, State 123456 ', 'text', '2026-03-24 10:03:51', '2026-03-24 10:07:41'),
(4, 'shop_phone', '+91 88490-05979', 'text', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(5, 'shop_email', 'info@BharatMart.com', 'email', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(6, 'shop_logo', 'fas fa-shopping-basket', 'text', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(7, 'facebook_url', '#', 'url', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(8, 'twitter_url', '#', 'url', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(9, 'instagram_url', '#', 'url', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(10, 'linkedin_url', '#', 'url', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(11, 'youtube_url', '#', 'url', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(12, 'opening_hours', 'Mon-Sat: 8AM-10PM', 'text', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(13, 'copyright_text', '&copy; 2026 BharatMart. All rights reserved.', 'text', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(14, 'privacy_policy_url', '#', 'url', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(15, 'terms_url', '#', 'url', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(16, 'refund_policy_url', '#', 'url', '2026-03-24 10:03:51', '2026-03-24 10:03:51'),
(37, 'about_page_title', 'BharatMart About', 'text', '2026-03-24 10:20:32', '2026-03-24 10:28:03'),
(38, 'about_page_subtitle', 'Your trusted partner for fresh, quality groceries delivered right to your doorstep.', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(39, 'about_story_title', 'Our Story', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(40, 'about_story_content', 'BharatMart was founded with a simple mission: to make fresh, quality groceries accessible to everyone. Started in 2020, we grown from a small local service to a trusted name in grocery delivery. We work directly with local farmers and suppliers to ensure you get the freshest produce at the best prices. Our commitment to quality, customer service, and community has made us the preferred choice for thousands of families who value freshness and convenience.', 'textarea', '2026-03-24 10:20:32', '2026-03-24 10:29:43'),
(41, 'about_story_image', 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=800', 'url', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(42, 'about_stat_1_number', '50K+', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(43, 'about_stat_1_label', 'Happy Customers', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(44, 'about_stat_2_number', '500+', 'text', '2026-03-24 10:20:32', '2026-03-24 10:37:15'),
(45, 'about_stat_2_label', 'Products', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(46, 'about_stat_3_number', '24/7', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(47, 'about_stat_3_label', 'Support', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(48, 'about_mission_title', 'Our Mission', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(49, 'about_mission_content', 'To provide fresh, high-quality groceries to every household while supporting local farmers and suppliers. We aim to make grocery shopping convenient, affordable, and sustainable.', 'textarea', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(50, 'about_vision_title', 'Our Vision', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(51, 'about_vision_content', 'To become the leading grocery delivery service in the region by maintaining the highest standards of quality, customer service, and community engagement. We envision a future where everyone has access to fresh, healthy food.', 'textarea', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(52, 'about_cta_title', 'Ready to Start Shopping?', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(53, 'about_cta_content', 'Join thousands of satisfied customers and experience the BharatMart difference!', 'text', '2026-03-24 10:20:32', '2026-03-24 10:20:32'),
(190, 'razorpay_key_id', 'rzp_test_SdRyJvc7RUYFog', 'text', '2026-04-20 11:23:10', '2026-04-20 16:58:43'),
(191, 'razorpay_key_secret', 'BzSIg5UJ3gl12hIirmmcNF8t', 'text', '2026-04-20 11:23:10', '2026-04-20 16:58:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`),
  ADD KEY `idx_coupon_code` (`coupon_code`),
  ADD KEY `idx_status_dates` (`status`,`valid_from`,`valid_to`);

--
-- Indexes for table `offer_products`
--
ALTER TABLE `offer_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_offer_product` (`offer_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
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
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `register`
--
ALTER TABLE `register`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `offer_products`
--
ALTER TABLE `offer_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `register`
--
ALTER TABLE `register`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offer_products`
--
ALTER TABLE `offer_products`
  ADD CONSTRAINT `offer_products_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offer_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `register` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
