-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 26, 2026 at 12:28 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bid_offers`
--
ALTER TABLE `bid_offers`
  ADD PRIMARY KEY (`offer_id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bid_offers`
--
ALTER TABLE `bid_offers`
  MODIFY `offer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bid_offers`
--
ALTER TABLE `bid_offers`
  ADD CONSTRAINT `bid_offers_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `bids` (`bids_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bid_offers_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
