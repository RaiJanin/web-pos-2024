-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 05:12 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_2_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `business_account`
--

CREATE TABLE `business_account` (
  `restaurant_name` varchar(100) NOT NULL,
  `restaurant_owner` varchar(100) DEFAULT NULL,
  `password` char(255) NOT NULL,
  `username` text NOT NULL,
  `tables_number` int(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_account`
--

INSERT INTO `business_account` (`restaurant_name`, `restaurant_owner`, `password`, `username`, `tables_number`) VALUES
('Janin\'s Canteen', 'Janiño Abrenica', '$2y$10$txuog2VLJj1Ir3ROkncL1u8Kx6ER.xEKgmlNHgjPz1Z1XGerYocaG', 'Janin', 5);

-- --------------------------------------------------------

--
-- Table structure for table `cancelled_orders`
--

CREATE TABLE `cancelled_orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `cancelled_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancelled_orders`
--

INSERT INTO `cancelled_orders` (`id`, `customer_name`, `cancelled_at`) VALUES
(1, 'janin', '2025-04-22 16:21:18'),
(2, 'niki', '2025-04-22 16:58:53'),
(3, 'niki', '2025-04-25 10:16:09'),
(4, 'janin', '2025-04-25 10:17:05');

-- --------------------------------------------------------

--
-- Table structure for table `cancelled_order_items`
--

CREATE TABLE `cancelled_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancelled_order_items`
--

INSERT INTO `cancelled_order_items` (`id`, `order_id`, `item_name`, `quantity`, `price`) VALUES
(1, 1, 'Burger', 1, 53.00),
(2, 1, 'Japchae', 1, 114.00),
(3, 2, 'Japchae', 1, 114.00),
(4, 2, 'Burger', 1, 53.00),
(5, 3, 'Burger', 1, 53.00),
(6, 3, 'Tteokbokki with Boiled eggs', 1, 90.00),
(7, 3, 'Japchae', 1, 114.00),
(8, 3, 'Red Horse', 1, 140.00),
(9, 3, 'Soju', 1, 54.00),
(10, 3, 'Milo ebridi', 1, 28.00),
(11, 4, 'Burger', 1, 53.00),
(12, 4, 'Tteokbokki with Boiled eggs', 1, 90.00),
(13, 4, 'Japchae', 1, 114.00),
(14, 4, 'Milo ebridi', 1, 28.00),
(15, 4, 'Red Horse', 1, 140.00),
(16, 4, 'Soju', 1, 54.00);

-- --------------------------------------------------------

--
-- Table structure for table `items_stock`
--

CREATE TABLE `items_stock` (
  `item_name` text NOT NULL,
  `price` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `id` int(11) NOT NULL,
  `description` text NOT NULL,
  `stocks` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items_stock`
--

INSERT INTO `items_stock` (`item_name`, `price`, `image`, `id`, `description`, `stocks`) VALUES
('Burger', 53, 'uploads/1744204967_1bf9f909ecceb79c29284794c2d74217.jpg', 1, 'This burger is super delicious.', 737),
('Tteokbokki with Boiled eggs', 90, 'uploads/1744205233_33ed35902cceb8763af82ad89cb3e479.jpg', 2, 'undefined', 742),
('Japchae', 114, 'uploads/1744205286_8a97a30a195970749e16eaa97b697fc4.jpg', 3, 'undefined', 700),
('Red Horse', 140, 'uploads/1744205390_f3f1fa33d6b4da675731d6336ed7aa8e.jpg', 4, 'undefined', 733),
('Soju', 54, 'uploads/1744205468_799b788256c42dcb9ace7f16230f4c40.jpg', 5, 'undefined', 706),
('Milo ebridi', 28, 'uploads/1744205622_b643c9f00659f035bcb60a065e5a7286.jpg', 6, 'undefined', 737);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `message`, `is_read`, `created_at`) VALUES
(3, 'Attention Miss u! Your order no:3 has been accepted!', 0, '2025-04-10 04:38:44'),
(4, 'Attention Janin! Your order no:4 has been accepted!', 0, '2025-04-10 04:39:17'),
(5, 'Attention Janino! Your order no:5 has been accepted!', 0, '2025-04-11 00:57:15'),
(6, 'Attention Chantyle Igo-ogan For Mayor! Your order no:6 has been accepted!', 0, '2025-04-11 06:55:49'),
(7, 'Attention janin! Your order no:7 has been accepted!', 0, '2025-04-11 16:10:42'),
(8, 'Attention janin! Your order no:8 has been accepted!', 0, '2025-04-11 16:53:17'),
(9, 'Attention janin! Your order no:10 has been accepted!', 0, '2025-04-22 16:15:38'),
(10, 'Attention janin! Your order no:11 has been accepted!', 0, '2025-04-22 16:42:25'),
(11, 'Attention janin! Your order no:12 has been accepted!', 0, '2025-04-22 16:43:13'),
(12, 'Attention janin! Your order no:13 has been accepted!', 0, '2025-04-22 16:44:54'),
(13, 'Attention janin! Your order no:14 has been accepted!', 0, '2025-04-22 16:46:07'),
(14, 'Attention niki! Your order no:9 has been accepted!', 0, '2025-04-22 16:58:44'),
(15, 'Attention janin! Your order no:15 has been accepted!', 0, '2025-04-25 08:48:40'),
(16, 'Attention janin! Your order no:17 has been accepted!', 0, '2025-04-25 10:15:43'),
(17, 'Attention janin! Your order no:18 has been accepted!', 0, '2025-04-25 10:15:52'),
(18, 'Attention niki! Your order no:19 has been accepted!', 0, '2025-04-25 10:16:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `table_number` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `cash_paid` int(15) DEFAULT NULL,
  `change_given` int(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `table_number`, `total_price`, `order_date`, `status`, `cash_paid`, `change_given`) VALUES
(3, 'Miss u', '3', 679.00, '2025-04-10 04:37:57', 'completed', 700, 21),
(4, 'Janin', 'No Specific Table', 28.00, '2025-04-10 04:38:09', 'completed', 50, 22),
(5, 'Janino', '3', 479.00, '2025-04-11 00:57:07', 'completed', 1000, 521),
(6, 'Chantyle Igo-ogan For Mayor', '3', 1380.00, '2025-04-11 06:55:39', 'completed', 1500, 120),
(7, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 'completed', 500, 21),
(8, 'janin', 'No Specific Table', 53.00, '2025-04-11 08:52:53', 'completed', 100, 47),
(9, 'niki', 'No Specific Table', 167.00, '2025-04-11 08:53:07', 'cancelled', NULL, NULL),
(10, 'janin', 'No Specific Table', 167.00, '2025-04-22 08:15:29', 'cancelled', NULL, NULL),
(11, 'janin', 'No Specific Table', 1176.00, '2025-04-22 08:41:38', 'completed', 1200, 24),
(12, 'janin', 'No Specific Table', 53.00, '2025-04-22 08:43:07', 'completed', 60, 7),
(13, 'janin', 'No Specific Table', 114.00, '2025-04-22 08:44:43', 'completed', 150, 36),
(14, 'janin', 'No Specific Table', 47900.00, '2025-04-22 08:45:55', 'completed', 48000, 100),
(15, 'janin', 'No Specific Table', 1193.00, '2025-04-25 00:48:27', 'completed', 1200, 7),
(17, 'janin', '2', 479.00, '2025-04-25 02:13:48', 'cancelled', NULL, NULL),
(18, 'janin', '2', 6169.00, '2025-04-25 02:14:49', 'completed', 7000, 831),
(19, 'niki', 'No Specific Table', 479.00, '2025-04-25 02:15:23', 'cancelled', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_name`, `quantity`, `price`, `subtotal`) VALUES
(13, 3, 'Burger', 1, 53.00, 53.00),
(14, 3, 'Tteokbokki with Boiled eggs', 2, 90.00, 180.00),
(15, 3, 'Japchae', 1, 114.00, 114.00),
(16, 3, 'Red Horse', 1, 140.00, 140.00),
(17, 3, 'Soju', 2, 54.00, 108.00),
(18, 3, 'Milo ebridi', 3, 28.00, 84.00),
(19, 4, 'Milo ebridi', 1, 28.00, 28.00),
(20, 5, 'Tteokbokki with Boiled eggs', 1, 90.00, 90.00),
(21, 5, 'Burger', 1, 53.00, 53.00),
(22, 5, 'Japchae', 1, 114.00, 114.00),
(23, 5, 'Red Horse', 1, 140.00, 140.00),
(24, 5, 'Soju', 1, 54.00, 54.00),
(25, 5, 'Milo ebridi', 1, 28.00, 28.00),
(26, 6, 'Red Horse', 6, 140.00, 840.00),
(27, 6, 'Soju', 10, 54.00, 540.00),
(28, 7, 'Burger', 1, 53.00, 53.00),
(29, 7, 'Japchae', 1, 114.00, 114.00),
(30, 7, 'Tteokbokki with Boiled eggs', 1, 90.00, 90.00),
(31, 7, 'Red Horse', 1, 140.00, 140.00),
(32, 7, 'Soju', 1, 54.00, 54.00),
(33, 7, 'Milo ebridi', 1, 28.00, 28.00),
(34, 8, 'Burger', 1, 53.00, 53.00),
(35, 9, 'Japchae', 1, 114.00, 114.00),
(36, 9, 'Burger', 1, 53.00, 53.00),
(37, 10, 'Burger', 1, 53.00, 53.00),
(38, 10, 'Japchae', 1, 114.00, 114.00),
(39, 11, 'Japchae', 7, 114.00, 798.00),
(40, 11, 'Soju', 7, 54.00, 378.00),
(41, 12, 'Burger', 1, 53.00, 53.00),
(42, 13, 'Japchae', 1, 114.00, 114.00),
(43, 14, 'Tteokbokki with Boiled eggs', 100, 90.00, 9000.00),
(44, 14, 'Burger', 100, 53.00, 5300.00),
(45, 14, 'Japchae', 100, 114.00, 11400.00),
(46, 14, 'Red Horse', 100, 140.00, 14000.00),
(47, 14, 'Soju', 100, 54.00, 5400.00),
(48, 14, 'Milo ebridi', 100, 28.00, 2800.00),
(49, 15, 'Burger', 1, 53.00, 53.00),
(50, 15, 'Japchae', 10, 114.00, 1140.00),
(57, 17, 'Burger', 1, 53.00, 53.00),
(58, 17, 'Tteokbokki with Boiled eggs', 1, 90.00, 90.00),
(59, 17, 'Japchae', 1, 114.00, 114.00),
(60, 17, 'Milo ebridi', 1, 28.00, 28.00),
(61, 17, 'Red Horse', 1, 140.00, 140.00),
(62, 17, 'Soju', 1, 54.00, 54.00),
(63, 18, 'Burger', 13, 53.00, 689.00),
(64, 18, 'Tteokbokki with Boiled eggs', 13, 90.00, 1170.00),
(65, 18, 'Japchae', 13, 114.00, 1482.00),
(66, 18, 'Red Horse', 12, 140.00, 1680.00),
(67, 18, 'Soju', 14, 54.00, 756.00),
(68, 18, 'Milo ebridi', 14, 28.00, 392.00),
(69, 19, 'Burger', 1, 53.00, 53.00),
(70, 19, 'Tteokbokki with Boiled eggs', 1, 90.00, 90.00),
(71, 19, 'Japchae', 1, 114.00, 114.00),
(72, 19, 'Red Horse', 1, 140.00, 140.00),
(73, 19, 'Soju', 1, 54.00, 54.00),
(74, 19, 'Milo ebridi', 1, 28.00, 28.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales_report`
--

CREATE TABLE `sales_report` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `table_number` varchar(50) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `total_items` int(15) DEFAULT NULL,
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_report`
--

INSERT INTO `sales_report` (`id`, `customer_name`, `table_number`, `total_price`, `order_date`, `total_items`, `order_id`) VALUES
(6, 'Miss u', '3', 679.00, '2025-04-10 04:37:57', 10, 0),
(7, 'Janin', 'No Specific Table', 28.00, '2025-04-10 04:38:09', 1, 0),
(8, 'Chantyle Igo-ogan For Mayor', '3', 1380.00, '2025-04-11 06:55:39', 16, 0),
(9, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(10, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(11, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(12, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(13, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(14, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(15, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(16, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(17, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(18, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(19, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(20, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(21, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(22, 'janin', 'No Specific Table', 479.00, '2025-04-11 08:10:28', 6, 0),
(23, 'Janino', '3', 479.00, '2025-04-11 00:57:07', 6, 0),
(24, 'janin', 'No Specific Table', 53.00, '2025-04-11 08:52:53', 1, 0),
(25, 'janin', 'No Specific Table', 1176.00, '2025-04-22 08:41:38', 14, 0),
(26, 'janin', 'No Specific Table', 53.00, '2025-04-22 08:43:07', 1, 0),
(27, 'janin', 'No Specific Table', 114.00, '2025-04-22 08:44:43', 1, 0),
(28, 'janin', 'No Specific Table', 47900.00, '2025-04-22 08:45:55', 600, 0),
(29, 'janin', 'No Specific Table', 1193.00, '2025-04-25 00:48:27', 11, 0),
(30, 'janin', '2', 6169.00, '2025-04-25 02:14:49', 79, 0);

-- --------------------------------------------------------

--
-- Table structure for table `sold_items`
--

CREATE TABLE `sold_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity_sold` int(11) NOT NULL,
  `total_sales` decimal(10,2) NOT NULL,
  `order_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sold_items`
--

INSERT INTO `sold_items` (`id`, `order_id`, `item_id`, `item_name`, `quantity_sold`, `total_sales`, `order_date`) VALUES
(53, 7, 1, 'Burger', 1, 53.00, '2025-04-11'),
(54, 7, 3, 'Japchae', 1, 114.00, '2025-04-11'),
(55, 7, 2, 'Tteokbokki with Boiled eggs', 1, 90.00, '2025-04-11'),
(56, 7, 4, 'Red Horse', 1, 140.00, '2025-04-11'),
(57, 7, 5, 'Soju', 1, 54.00, '2025-04-11'),
(58, 7, 6, 'Milo ebridi', 1, 28.00, '2025-04-11'),
(59, 5, 2, 'Tteokbokki with Boiled eggs', 1, 90.00, '2025-04-11'),
(60, 5, 1, 'Burger', 1, 53.00, '2025-04-11'),
(61, 5, 3, 'Japchae', 1, 114.00, '2025-04-11'),
(62, 5, 4, 'Red Horse', 1, 140.00, '2025-04-11'),
(63, 5, 5, 'Soju', 1, 54.00, '2025-04-11'),
(64, 5, 6, 'Milo ebridi', 1, 28.00, '2025-04-11'),
(65, 8, 1, 'Burger', 1, 53.00, '2025-04-11'),
(66, 11, 3, 'Japchae', 7, 798.00, '2025-04-22'),
(67, 11, 5, 'Soju', 7, 378.00, '2025-04-22'),
(68, 12, 1, 'Burger', 1, 53.00, '2025-04-22'),
(69, 13, 3, 'Japchae', 1, 114.00, '2025-04-22'),
(70, 14, 2, 'Tteokbokki with Boiled eggs', 100, 9000.00, '2025-04-22'),
(71, 14, 1, 'Burger', 100, 5300.00, '2025-04-22'),
(72, 14, 3, 'Japchae', 100, 11400.00, '2025-04-22'),
(73, 14, 4, 'Red Horse', 100, 14000.00, '2025-04-22'),
(74, 14, 5, 'Soju', 100, 5400.00, '2025-04-22'),
(75, 14, 6, 'Milo ebridi', 100, 2800.00, '2025-04-22'),
(76, 15, 1, 'Burger', 1, 53.00, '2025-04-25'),
(77, 15, 3, 'Japchae', 10, 1140.00, '2025-04-25'),
(78, 18, 1, 'Burger', 13, 689.00, '2025-04-25'),
(79, 18, 2, 'Tteokbokki with Boiled eggs', 13, 1170.00, '2025-04-25'),
(80, 18, 3, 'Japchae', 13, 1482.00, '2025-04-25'),
(81, 18, 4, 'Red Horse', 12, 1680.00, '2025-04-25'),
(82, 18, 5, 'Soju', 14, 756.00, '2025-04-25'),
(83, 18, 6, 'Milo ebridi', 14, 392.00, '2025-04-25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cancelled_orders`
--
ALTER TABLE `cancelled_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cancelled_order_items`
--
ALTER TABLE `cancelled_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `items_stock`
--
ALTER TABLE `items_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_report`
--
ALTER TABLE `sales_report`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sold_items`
--
ALTER TABLE `sold_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cancelled_orders`
--
ALTER TABLE `cancelled_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cancelled_order_items`
--
ALTER TABLE `cancelled_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `items_stock`
--
ALTER TABLE `items_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `sales_report`
--
ALTER TABLE `sales_report`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `sold_items`
--
ALTER TABLE `sold_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cancelled_order_items`
--
ALTER TABLE `cancelled_order_items`
  ADD CONSTRAINT `cancelled_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `cancelled_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sold_items`
--
ALTER TABLE `sold_items`
  ADD CONSTRAINT `sold_items_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items_stock` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
