-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 26, 2026 at 12:29 AM
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
-- Database: `farm_auction_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `cnic` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','farmer','buyer') NOT NULL DEFAULT 'buyer',
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `address` text NOT NULL,
  `city` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `cnic`, `email`, `phone`, `password_hash`, `role`, `status`, `address`, `city`, `country`, `created_at`) VALUES
(1, 'System Admin', '00000-0000000-0', 'admin@farm.com', '00000000000', '$2y$10$8jxSw1NiZBjY.jJTyLoDReigfPfZ72RuNPxz7E9a4yI4fPj8ZMPp.', 'admin', 'active', 'Headquarters', 'Islamabad', 'Pakistan', '2026-01-25 19:00:00'),
(12, 'abdullah', '12345-1234567-1', '123@212', '+920111111', '$2y$10$BLK2pNlUZ2cQOtRl/hv5gOYnL/IXOrbBTI0p/W7xQ2mXOx43CH2qS', 'buyer', 'active', 'asd', '123@212', 'Pakistan', '2026-01-24 15:37:20'),
(13, 'abdullah', '12345-1234567-2', '123@213', '+920111111', '$2y$10$libq37GNn8DCVZ5xnx1bAeA8Kq8BWpmNRh3MkKSzQPiq0IpHKX3Tu', 'buyer', 'active', 'asd', '123@213', 'Pakistan', '2026-01-24 15:51:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `cnic` (`cnic`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
