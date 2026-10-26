-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 26, 2026 at 12:27 AM
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
-- Table structure for table `bids`
--

CREATE TABLE `bids` (
  `bids_id` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_time` datetime NOT NULL,
  `year` int(4) DEFAULT NULL,
  `condition_state` varchar(50) DEFAULT 'Good',
  `hours_used` int(11) DEFAULT 0,
  `rating` decimal(2,1) DEFAULT 5.0,
  `bids_count` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `min_increment` decimal(10,2) DEFAULT 100.00,
  `location` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `visibility` enum('public','private') DEFAULT 'public',
  `starting_bid` decimal(10,2) DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bids`
--

INSERT INTO `bids` (`bids_id`, `farmer_id`, `title`, `category`, `price`, `quantity`, `unit`, `image_path`, `created_at`, `end_time`, `year`, `condition_state`, `hours_used`, `rating`, `bids_count`, `description`, `min_increment`, `location`, `color`, `size`, `visibility`, `starting_bid`, `status`) VALUES
(22, 12, 'asd', 'Harvesters', 13215.00, 1, 'kg', 'assets/images/uploads/1769381891_1769356392_onion.webp', '2026-01-25 22:58:11', '2026-01-29 03:58:11', 1900, 'New', 0, 5.0, 0, 'adsdasdasdfasdfasd', 123.00, 'lahore', '0', '', 'public', 12231.00, 'open');

-- --------------------------------------------------------

--
-- Table structure for table `bid_history`
--

CREATE TABLE `bid_history` (
  `history_id` int(11) NOT NULL,
  `bids_id` int(11) NOT NULL,
  `bidder_id` int(11) NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL,
  `bid_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bid_offers`
--

CREATE TABLE `bid_offers` (
  `offer_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bid_offers`
--

INSERT INTO `bid_offers` (`offer_id`, `listing_id`, `buyer_id`, `bid_amount`, `status`, `created_at`) VALUES
(4, 22, 1, 12354.00, 'pending', '2026-01-25 23:21:48'),
(5, 22, 1, 12477.00, 'pending', '2026-01-25 23:21:52'),
(6, 22, 1, 12600.00, 'pending', '2026-01-25 23:21:56'),
(7, 22, 1, 12723.00, 'pending', '2026-01-25 23:21:59'),
(8, 22, 1, 12846.00, 'pending', '2026-01-25 23:25:44'),
(9, 22, 1, 12969.00, 'pending', '2026-01-25 23:26:50'),
(10, 22, 1, 13092.00, 'pending', '2026-01-25 23:26:53'),
(11, 22, 1, 13215.00, 'pending', '2026-01-25 23:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `buyer_profiles`
--

CREATE TABLE `buyer_profiles` (
  `buyer_id` int(11) NOT NULL,
  `buyer_type` enum('individual','wholesaler','retailer','exporter') DEFAULT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `business_reg_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyer_profiles`
--

INSERT INTO `buyer_profiles` (`buyer_id`, `buyer_type`, `company_name`, `business_reg_number`) VALUES
(12, 'individual', 'asd', 'asd'),
(13, 'wholesaler', 'das', '');

-- --------------------------------------------------------

--
-- Table structure for table `farmer_profiles`
--

CREATE TABLE `farmer_profiles` (
  `farmer_id` int(11) NOT NULL,
  `farm_location` text DEFAULT NULL,
  `farm_size_acres` decimal(10,2) DEFAULT NULL,
  `crop_types` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`bids_id`),
  ADD KEY `farmer_id` (`farmer_id`);

--
-- Indexes for table `bid_history`
--
ALTER TABLE `bid_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `bids_id` (`bids_id`),
  ADD KEY `bidder_id` (`bidder_id`);

--
-- Indexes for table `bid_offers`
--
ALTER TABLE `bid_offers`
  ADD PRIMARY KEY (`offer_id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `buyer_profiles`
--
ALTER TABLE `buyer_profiles`
  ADD PRIMARY KEY (`buyer_id`);

--
-- Indexes for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  ADD PRIMARY KEY (`farmer_id`);

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
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `bids_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `bid_history`
--
ALTER TABLE `bid_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bid_offers`
--
ALTER TABLE `bid_offers`
  MODIFY `offer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bids`
--
ALTER TABLE `bids`
  ADD CONSTRAINT `bids_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `bid_history`
--
ALTER TABLE `bid_history`
  ADD CONSTRAINT `bid_history_ibfk_1` FOREIGN KEY (`bids_id`) REFERENCES `bids` (`bids_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bid_history_ibfk_2` FOREIGN KEY (`bidder_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `bid_offers`
--
ALTER TABLE `bid_offers`
  ADD CONSTRAINT `bid_offers_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `bids` (`bids_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bid_offers_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `buyer_profiles`
--
ALTER TABLE `buyer_profiles`
  ADD CONSTRAINT `buyer_profiles_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `farmer_profiles`
--
ALTER TABLE `farmer_profiles`
  ADD CONSTRAINT `farmer_profiles_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
