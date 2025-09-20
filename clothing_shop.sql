-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 20, 2025 at 09:47 AM
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
-- Database: `clothing_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `username` varchar(80) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `username`, `email`, `password_hash`, `created_at`) VALUES
(2, 'Udara Poorni', 'waupoorni', 'udarapoorni2004@gmail.com', '$2y$10$Oc4iIfphpJXguCe./2AnsOG8hhOr/2U1nb6ez4ppWgjasFT2pN56a', '2025-09-18 13:15:59');

-- --------------------------------------------------------

--
-- Table structure for table `clothes`
--

CREATE TABLE `clothes` (
  `id` int(11) NOT NULL,
  `name` varchar(160) NOT NULL,
  `sku` varchar(60) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `category` varchar(100) DEFAULT NULL,
  `size` varchar(40) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clothes`
--

INSERT INTO `clothes` (`id`, `name`, `sku`, `price`, `category`, `size`, `stock`, `image_url`, `description`, `created_at`) VALUES
(4, 'Summer frock', '21', 4000.00, 'women', 'S,M,L,Xl', 20, '/clothing-shop/uploads/clothes/1758203547_aec188.jpg', 'A frock is a stylish dress, often worn by women and girls, that combines comfort with elegance.', '2025-09-18 13:52:27'),
(5, 'CharmWear', '25', 3500.00, 'women', 's', 10, '/clothing-shop/uploads/clothes/1758203662_058639.jpg', 'It is usually designed with soft fabrics like cotton, silk, or chiffon for casual or formal wear.', '2025-09-18 13:54:22'),
(6, 'Floral Party Frock', '6', 2000.00, 'kids', 's', 10, '/clothing-shop/uploads/clothes/1758203877_175c3b.jpg', 'Floral Party Frock – A bright, colorful frock with floral prints, perfect for birthdays and special occasions.', '2025-09-18 13:57:57'),
(7, 'Polka Cutie', '5', 1500.00, 'kids', 'M', 5, '/clothing-shop/uploads/clothes/1758204051_8030af.jpg', 'Polka Dot Frock – A cute frock with polka dot patterns, stylish yet simple for outings.', '2025-09-18 14:00:51'),
(8, 'FlexiShirt', '33', 4000.00, 'men', 'L', 20, '/clothing-shop/uploads/clothes/1758204166_e6db06.jpg', 'Slim-fit shirt with a modern look.', '2025-09-18 14:02:46'),
(9, 'Classic Blue', '34', 5000.00, 'men', 'L', 20, '/clothing-shop/uploads/clothes/1758204282_8554ed.jpg', 'Popularly used in jeans and jackets.', '2025-09-18 14:04:42');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(2, 'Yasurika', 'yasurika@gmail.com', 'Order Request – Blue Denim', 'Do you have ripped denim jeans in size 32? If yes, I’d like to place an order.', '2025-09-18 14:12:29'),
(3, 'bhagya', 'bagya@gmail.com', 'Return/Exchange Request', 'I received my order yesterday but would like to exchange it for a different size. Can you help me with the process?', '2025-09-18 14:28:43'),
(4, 'Senura', 'senura@gmail.com', 'Size Availability Check', 'Do you have black denim in size 32? If yes, I’d like to place an order.', '2025-09-18 14:29:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `clothes`
--
ALTER TABLE `clothes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clothes`
--
ALTER TABLE `clothes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
