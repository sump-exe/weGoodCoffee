-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2026 at 11:00 AM
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
-- Database: `we_good_coffee`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `category` enum('Drinks','Food') NOT NULL,
  `sub_category` enum('Coffee','Milk-Based Beverages','Matcha Series','Ice-Blended / Frappe','Hot Drinks','Fruit Sodas','Refreshers','Add Ons','Rice Bowl','Pasta','Sandwiches','Dessert','Snacks') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `price`, `category`, `sub_category`) VALUES
(1, 'Adobo', 140, 'Food', 'Rice Bowl'),
(2, 'Hungarian Sausage', 140, 'Food', 'Rice Bowl'),
(3, 'Chicken Bites - Garlic Parmesan', 135, 'Food', 'Rice Bowl'),
(4, 'Chicken Bites - Spicy Korean', 135, 'Food', 'Rice Bowl'),
(5, 'Chicken Bites - Thyme & Lemon', 135, 'Food', 'Rice Bowl'),
(6, 'Pork Tonkatsu', 135, 'Food', 'Rice Bowl'),
(7, 'Tapa', 140, 'Food', 'Rice Bowl'),
(8, 'Korean Spam', 135, 'Food', 'Rice Bowl'),
(9, 'Longganisa Rice Bowl', 130, 'Food', 'Rice Bowl'),
(10, 'Fishy Nuggies', 100, 'Food', 'Rice Bowl'),
(11, 'Egg', 15, 'Food', 'Add Ons'),
(12, 'Large Rice', 10, 'Food', 'Add Ons'),
(13, 'Tuna Pesto', 150, 'Food', 'Pasta'),
(14, 'Creamy Carbonara', 140, 'Food', 'Pasta'),
(15, 'Lasagna', 160, 'Food', 'Pasta'),
(16, 'Grilled Ham & Cheese', 155, 'Food', 'Sandwiches'),
(17, 'Spam Sandwich', 155, 'Food', 'Sandwiches'),
(18, 'Hungarian Sausage', 155, 'Food', 'Sandwiches'),
(19, 'Tuna Melt', 140, 'Food', 'Sandwiches'),
(20, 'Churro Bites - Caramel', 80, 'Food', 'Dessert'),
(21, 'Churro Bites - Chocolate', 80, 'Food', 'Dessert'),
(22, 'We Good Oreos', 100, 'Food', 'Dessert'),
(23, 'Fries - Cheese', 75, 'Food', 'Snacks'),
(24, 'Fries - Barbecue', 75, 'Food', 'Snacks'),
(25, 'Fries - Sour Cream', 75, 'Food', 'Snacks'),
(26, 'Fries - Truffle', 75, 'Food', 'Snacks'),
(27, 'Mojos', 100, 'Food', 'Snacks'),
(28, 'Americano (Medium)', 80, 'Drinks', 'Coffee'),
(29, 'Americano (Large)', 95, 'Drinks', 'Coffee'),
(30, 'Latte (Medium)', 100, 'Drinks', 'Coffee'),
(31, 'Latte (Large)', 120, 'Drinks', 'Coffee'),
(32, 'Spanish Latte (Medium)', 120, 'Drinks', 'Coffee'),
(33, 'Spanish Latte (Large)', 140, 'Drinks', 'Coffee'),
(34, 'Mocha (Medium)', 125, 'Drinks', 'Coffee'),
(35, 'Mocha (Large)', 145, 'Drinks', 'Coffee'),
(36, 'White Mocha (Medium)', 125, 'Drinks', 'Coffee'),
(37, 'White Mocha (Large)', 145, 'Drinks', 'Coffee'),
(38, 'Caramel Macchiato (Medium)', 135, 'Drinks', 'Coffee'),
(39, 'Caramel Macchiato (Large)', 150, 'Drinks', 'Coffee'),
(40, 'Espresso Matcha (Medium)', 140, 'Drinks', 'Coffee'),
(41, 'Espresso Matcha (Large)', 155, 'Drinks', 'Coffee'),
(42, 'We Good Coffee (Medium)', 135, 'Drinks', 'Coffee'),
(43, 'Pink Drink (Medium)', 125, 'Drinks', 'Milk-Based Beverages'),
(44, 'Pink Drink (Large)', 145, 'Drinks', 'Milk-Based Beverages'),
(45, 'Blueberry Milk (Medium)', 120, 'Drinks', 'Milk-Based Beverages'),
(46, 'Blueberry Milk (Large)', 140, 'Drinks', 'Milk-Based Beverages'),
(47, 'Chocolate Milk (Medium)', 130, 'Drinks', 'Milk-Based Beverages'),
(48, 'Chocolate Milk (Large)', 150, 'Drinks', 'Milk-Based Beverages'),
(49, 'Choco Berry Milk (Medium)', 135, 'Drinks', 'Milk-Based Beverages'),
(50, 'Choco Berry Milk (Large)', 155, 'Drinks', 'Milk-Based Beverages'),
(51, 'Matcha Latte (Medium)', 135, 'Drinks', 'Matcha Series'),
(52, 'Matcha Latte (Large)', 155, 'Drinks', 'Matcha Series'),
(53, 'Strawberry Matcha (Medium)', 140, 'Drinks', 'Matcha Series'),
(54, 'Strawberry Matcha (Large)', 160, 'Drinks', 'Matcha Series'),
(55, 'Brown Sugar Matcha (Medium)', 130, 'Drinks', 'Matcha Series'),
(56, 'Brown Sugar Matcha (Large)', 150, 'Drinks', 'Matcha Series'),
(57, 'Caramel Matcha (Medium)', 140, 'Drinks', 'Matcha Series'),
(58, 'Caramel Matcha (Large)', 160, 'Drinks', 'Matcha Series'),
(59, 'Espresso Matcha (Medium)', 145, 'Drinks', 'Matcha Series'),
(60, 'Espresso Matcha (Large)', 165, 'Drinks', 'Matcha Series'),
(61, 'Strawberry Cream', 135, 'Drinks', 'Ice-Blended / Frappe'),
(62, 'Blueberry Cream', 135, 'Drinks', 'Ice-Blended / Frappe'),
(63, 'Matcha Cream', 150, 'Drinks', 'Ice-Blended / Frappe'),
(64, 'Chocolate Chip Cream', 150, 'Drinks', 'Ice-Blended / Frappe'),
(65, 'Caramel Affogato', 155, 'Drinks', 'Ice-Blended / Frappe'),
(66, 'Hany Caramel', 145, 'Drinks', 'Ice-Blended / Frappe'),
(67, 'Americano', 80, 'Drinks', 'Hot Drinks'),
(68, 'Latte', 115, 'Drinks', 'Hot Drinks'),
(69, 'Spanish Latte', 140, 'Drinks', 'Hot Drinks'),
(70, 'Mocha', 145, 'Drinks', 'Hot Drinks'),
(71, 'White Mocha', 145, 'Drinks', 'Hot Drinks'),
(72, 'Caramel Macchiato', 150, 'Drinks', 'Hot Drinks'),
(73, 'Espresso Matcha', 155, 'Drinks', 'Hot Drinks'),
(74, 'Lemon Soda (Medium)', 75, 'Drinks', 'Fruit Sodas'),
(75, 'Lemon Soda (Large)', 90, 'Drinks', 'Fruit Sodas'),
(76, 'Strawberry Soda (Medium)', 75, 'Drinks', 'Fruit Sodas'),
(77, 'Strawberry Soda (Large)', 90, 'Drinks', 'Fruit Sodas'),
(78, 'Blueberry Soda (Medium)', 75, 'Drinks', 'Fruit Sodas'),
(79, 'Blueberry Soda (Large)', 90, 'Drinks', 'Fruit Sodas'),
(80, 'Green Apple Soda (Medium)', 75, 'Drinks', 'Fruit Sodas'),
(81, 'Green Apple Soda (Large)', 90, 'Drinks', 'Fruit Sodas'),
(82, 'Calamansi Refresher (Medium)', 65, 'Drinks', 'Refreshers'),
(83, 'Blue Sparkle (Medium)', 55, 'Drinks', 'Refreshers'),
(84, 'Blue Sparkle (Large)', 65, 'Drinks', 'Refreshers'),
(85, 'Extra Shot', 30, 'Drinks', 'Add Ons'),
(86, 'Extra Syrup', 20, 'Drinks', 'Add Ons'),
(87, 'Extra Sauce', 20, 'Drinks', 'Add Ons'),
(88, 'Whipped Cream', 30, 'Drinks', 'Add Ons');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier','manager','inventory_staff') NOT NULL DEFAULT 'cashier',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(2, 'admin', '$2y$10$sjhAvzLBX9lFRDPp3xo7NeUeP2Izq1kHxpIaqy.hml1IUXBzm8R7C', 'admin', '2026-11-01 15:13:58'),
(3, 'manager', '$2y$10$Pak2NdpEB/Yr1lk85lBxteyTrhCewNBTpqUnFdLeEQUGf3Kkjk8LK', 'manager', '2026-11-01 15:14:10'),
(4, 'inventory', '$2y$10$QbqaxAZux05c2MImUxkAL.4YREph9um9Cxwn.TqECJ74/ARfKSsxS', 'inventory_staff', '2026-11-01 15:14:22'),
(5, 'cashier', '$2y$10$pciX5foTbWpVHqujwlYzA.317wAiHGfXV31QuLt5KP0qU.hMzw/Te', 'cashier', '2026-11-01 15:19:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
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
  ADD PRIMARY KEY (`order_id`,`menu_item_id`),
  ADD KEY `fk_order_items_menu_item` (`menu_item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_menu_item` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`),
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
