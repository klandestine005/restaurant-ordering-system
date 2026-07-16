-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2026 at 01:08 PM
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
-- Database: `restaurant_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `category` enum('food','drink','snack') NOT NULL,
  `menu_name` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `category`, `menu_name`, `price`, `image`) VALUES
(1, 'food', 'Fettuccine Alfredo', 50000, 'images/fettuccine_alfredo.jpg'),
(2, 'food', 'Spaghetti Bolognese', 50000, 'images/spaghetti_bolognese.jpg'),
(3, 'food', 'Chicken Ricebowl', 30000, 'images/chicken_ricebowl.jpg'),
(4, 'food', 'Cheese Burger', 25000, 'images/cheese_burger.jpg'),
(5, 'food', 'Fish and Chips', 50000, 'images/fish_and_chips.jpg'),
(6, 'food', 'Fried Rice', 30000, 'images/fried_rice.jpg'),
(7, 'drink', 'Coffee Latte', 20000, 'images/coffee_latte.jpg'),
(8, 'drink', 'Cappuccino', 20000, 'images/cappuccino.jpg'),
(9, 'drink', 'Iced Tea', 12000, 'images/iced_tea.jpg'),
(10, 'drink', 'Hot Tea', 10000, 'images/hot_tea.jpg'),
(11, 'drink', 'Iced Lemonade', 12000, 'images/iced_lemonade.jpg'),
(12, 'drink', 'Mineral Water', 5000, 'images/mineral_water.jpg'),
(13, 'snack', 'Chocolate Brownies', 10000, 'images/chocolate_brownies.jpg'),
(14, 'snack', 'Fruit Pie', 10000, 'images/fruit_pie.jpg'),
(15, 'snack', 'Nachos', 20000, 'images/nachos.jpg'),
(16, 'snack', 'Chocolate Ice Cream', 15000, 'images/chocolate_ice_cream.jpg'),
(17, 'snack', 'Vanilla Ice Cream', 15000, 'images/vanilla_ice_cream.jpg'),
(18, 'snack', 'Strawberry Ice Cream', 15000, 'images/strawberry_ice_cream.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_number` varchar(50) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `table_number` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `status` enum('On Progress','Done') NOT NULL DEFAULT 'On Progress',
  `payment_status` enum('Unpaid','Paid') NOT NULL DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_number`, `date`, `table_number`, `customer_name`, `status`, `payment_status`) VALUES
('ORD-20260713-17435', '2026-07-13 15:09:10', 1, 'Klandestine', 'Done', 'Paid'),
('ORD-20260714-DD58B', '2026-07-14 11:58:39', 2, 'Brendon Urie', 'Done', 'Paid'),
('ORD-20260714-F6936', '2026-07-14 12:28:07', 3, 'Ryan Ross', 'Done', 'Paid'),
('ORD-20260715-13C9A', '2026-07-15 15:58:59', 5, 'Camilla', 'Done', 'Paid'),
('ORD-20260715-27D13', '2026-07-15 15:59:15', 5, 'Camilla', 'Done', 'Paid'),
('ORD-20260715-31261', '2026-07-15 15:49:43', 6, 'Dicky', 'Done', 'Paid'),
('ORD-20260715-3B128', '2026-07-15 15:37:35', 4, 'Khansa', 'Done', 'Paid'),
('ORD-20260715-D5773', '2026-07-15 16:37:41', 5, 'Camilla', 'Done', 'Unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `menu_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_number`, `menu_id`, `quantity`) VALUES
(1, 'ORD-20260713-17435', 4, 1),
(2, 'ORD-20260713-17435', 8, 1),
(3, 'ORD-20260714-DD58B', 1, 1),
(4, 'ORD-20260714-DD58B', 5, 1),
(5, 'ORD-20260714-DD58B', 9, 1),
(6, 'ORD-20260714-DD58B', 12, 2),
(7, 'ORD-20260714-DD58B', 15, 1),
(8, 'ORD-20260714-F6936', 5, 1),
(9, 'ORD-20260714-F6936', 7, 1),
(12, 'ORD-20260715-3B128', 2, 1),
(13, 'ORD-20260715-3B128', 9, 1),
(14, 'ORD-20260715-31261', 1, 1),
(15, 'ORD-20260715-31261', 8, 1),
(16, 'ORD-20260715-13C9A', 8, 1),
(17, 'ORD-20260715-13C9A', 14, 1),
(18, 'ORD-20260715-27D13', 4, 1),
(19, 'ORD-20260715-27D13', 12, 1),
(20, 'ORD-20260715-D5773', 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('kitchen','cashier') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'staf_kitchen', 'password123', 'kitchen'),
(2, 'staf_cashier', 'password123', 'cashier');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_number`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_number` (`order_number`),
  ADD KEY `menu_id` (`menu_id`);

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
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_number`) REFERENCES `orders` (`order_number`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
