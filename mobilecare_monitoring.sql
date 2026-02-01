-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 31, 2026 at 02:10 PM
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
-- Database: `mobilecare_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `chubb_claims`
--

CREATE TABLE `chubb_claims` (
  `id` int(11) NOT NULL,
  `claim_no` varchar(100) DEFAULT NULL,
  `claim_date` date NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `unit_replacement` varchar(100) DEFAULT NULL,
  `claimant` varchar(100) DEFAULT NULL,
  `kgb_serial` varchar(100) DEFAULT NULL,
  `special_price` decimal(10,2) DEFAULT NULL,
  `chubb_pf` varchar(100) DEFAULT NULL,
  `storage_location` varchar(100) DEFAULT NULL,
  `site` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chubb_claims`
--

INSERT INTO `chubb_claims` (`id`, `claim_no`, `claim_date`, `serial_number`, `model`, `unit_replacement`, `claimant`, `kgb_serial`, `special_price`, `chubb_pf`, `storage_location`, `site`, `created_at`, `status`, `is_deleted`) VALUES
(1, '111223331144', '2026-01-24', 'KLasdjjASDASD', 'iPhone 13 Pro', 'iPhone 13 Pro', 'alex', 'adsas', 10.00, '10', 'ios15', 'MobileCare-Manila', '2026-01-24 03:55:31', 'Pending', 0),
(2, '1231123123', '2026-01-07', 'G272CJC9K7', 'iPhone 13 Pro', 'iPhone 13 Pro', 'alex', 'adsas', 1000.00, '1000', 'ios15', 'MobileCare-Podium', '2026-01-24 04:04:08', 'Rejected', 0),
(3, '20031596', '2026-01-28', '123123', 'iPhone 13 Pro', 'iPhone 13 Pro', 'Marc', 'KASLDASLDNASD', 123.00, '10', 'ios17', 'MobileCare-Manila', '2026-01-24 06:37:45', 'Pending', 1),
(5, '20036156', '2026-01-30', 'KLLASDJKASDN', 'Iphone 11', 'Iphone 11', 'Chel', 'ASDKASKD', 123.00, '123', 'MAc123', 'MobileCare-Manila', '2026-01-24 11:42:52', 'Pending', 0);

-- --------------------------------------------------------

--
-- Table structure for table `endorsements`
--

CREATE TABLE `endorsements` (
  `id` int(11) NOT NULL,
  `engineer_name` varchar(255) NOT NULL,
  `type` enum('iPhone','MacBook','iOS','iMac') NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `account_type` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `endorsements`
--

INSERT INTO `endorsements` (`id`, `engineer_name`, `type`, `quantity`, `account_type`, `created_at`) VALUES
(28, 'Alexander B. Losaynon', 'iPhone', 1, 'MobileCare-Podium', '2026-01-26 19:49:24'),
(30, 'Jasmil Rose Guban', 'iPhone', 1, 'MobileCare-Manila', '2026-01-27 20:05:17');

-- --------------------------------------------------------

--
-- Table structure for table `engineers`
--

CREATE TABLE `engineers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `escalations`
--

CREATE TABLE `escalations` (
  `id` int(11) NOT NULL,
  `ar_number` varchar(100) DEFAULT NULL,
  `engineer_name` varchar(100) DEFAULT NULL,
  `dispatch_id` varchar(100) DEFAULT NULL,
  `escalation_id` varchar(100) DEFAULT NULL,
  `escalation_status` varchar(50) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `unit_description` varchar(255) DEFAULT NULL,
  `css_response` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `site` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `escalations`
--

INSERT INTO `escalations` (`id`, `ar_number`, `engineer_name`, `dispatch_id`, `escalation_id`, `escalation_status`, `serial_number`, `unit_description`, `css_response`, `remarks`, `site`, `created_at`) VALUES
(1, '20031959', 'Alexander B. Losaynon', 'G123451122', '123123123123123', 'Open', 'OP123QWE', 'iphone 13 pro max green', '-', 'the unit was out of warranty\r\n', 'MobileCare-Manila', '2026-01-24 06:14:18'),
(2, '20031959', 'Alexander B. Losaynon', 'G123123123', '11111255556698', 'Open', 'G272CJC9K7', 'iphone 13 pro max red', 'caskdlka', 'hotdog', 'MobileCare-Podium', '2026-01-24 06:15:23');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `part_no` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `type` enum('adhesive','fixed asset','consumables','others') NOT NULL DEFAULT 'others',
  `ownership` varchar(50) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `site` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_name`, `serial_no`, `part_no`, `quantity`, `type`, `ownership`, `unit_price`, `total_price`, `site`, `created_at`) VALUES
(1, 'Monitor', NULL, 'FA-0E-2019-05-5750', 1, 'fixed asset', 'Alexander Losaynon', 0.00, 0.00, 'MobileCare-Podium', '2026-01-24 06:53:47');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `read_status` tinyint(1) DEFAULT 0,
  `sent_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `file_name`, `file_path`, `read_status`, `sent_at`) VALUES
(1, 8, 2, NULL, 'IMG_7717 2x2.jpg', '/Mobilecare_monitoring/uploads/1769862706_IMG_7717 2x2.jpg', 1, '2026-01-31 20:31:46'),
(2, 8, 2, 'f', NULL, NULL, 1, '2026-01-31 20:31:50'),
(3, 8, 2, 'please help me!', NULL, NULL, 1, '2026-01-31 20:39:14'),
(4, 8, 2, 'd', NULL, NULL, 1, '2026-01-31 20:39:58'),
(5, 8, 2, 'd', NULL, NULL, 1, '2026-01-31 20:44:45'),
(6, 8, 2, 'f', NULL, NULL, 1, '2026-01-31 20:57:23'),
(7, 8, 2, 'hi', NULL, NULL, 1, '2026-01-31 21:01:58'),
(8, 2, 8, NULL, 'IMG_7717 2x2.jpg', '/Mobilecare_monitoring/uploads/1769864917_IMG_7717 2x2.jpg', 1, '2026-01-31 21:08:37');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sites`
--

CREATE TABLE `sites` (
  `id` int(11) NOT NULL,
  `site_code` varchar(50) NOT NULL,
  `site_name` varchar(100) NOT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sites`
--

INSERT INTO `sites` (`id`, `site_code`, `site_name`, `active`) VALUES
(1, 'MobileCare-Manila', 'MobileCare Manila', 1),
(2, 'MobileCare-Podium', 'MobileCare Podium', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','engineer','user') NOT NULL DEFAULT 'user',
  `account_type` varchar(100) NOT NULL,
  `is_engineer` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `personal_id` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `name`, `password`, `role`, `account_type`, `is_engineer`, `created_at`, `last_activity`, `reset_token`, `reset_expires`, `full_name`, `personal_id`, `profile_image`, `position`, `active`) VALUES
(2, 'admin@mobilecare.com', NULL, '$2y$10$iZrCr8V4nJh17UOqBm3pyufdu3anSKheOUsrz3EKB1lAxm4VTbO.6', 'admin', 'MobileCare-Manila', 0, '2026-01-23 10:59:53', '2026-01-31 21:08:19', NULL, NULL, 'Admin', '', 'profile_2_1769234247.png', 'Admin', 1),
(5, 'alexander.losaynon@mobilecareph.com', NULL, '$2y$10$iP6o.cVHeFWwtHTlaNkNuO6UwSX3t.b/4sw8iUeo5Ae/E/oc/Sriu', 'user', 'MobileCare-Podium', 0, '2026-01-24 02:10:58', '2026-01-31 18:50:52', NULL, NULL, 'Alexander B. Losaynon', '', 'profile_5_1769338140.jpg', 'Engineer', 1),
(6, 'patriaciaanne.sanandres@mobilecareph.com', NULL, '$2y$10$AongVTLEEVqmDbuki8xVCuktFF0QhXdm4WtnYLnWSF.1Rq/kgh12y', 'user', 'MobileCare-Podium', 0, '2026-01-25 02:12:37', '2026-01-31 16:50:47', NULL, NULL, 'Patricia Anne San Andres', '', 'profile_6_1769307387.jpg', 'Customer Service', 1),
(7, 'jasmilrose.guban@mobilecareph.com', NULL, '$2y$10$Kjnqk8ix7b58Ak5OlIrh0OIpNaPp8pXzm1TaUAKJSH.DBkqur3NHe', 'user', 'MobileCare-Manila', 0, '2026-01-28 20:04:30', '2026-01-31 17:09:48', NULL, NULL, 'Jasmil Rose Guban', '', NULL, 'Engineer', 1),
(8, 'sample@gmail.com', NULL, '$2y$10$DxzGmEDizvvGe/ZwNN6gAevZyRrW6t0O22NL9JRcNGahY9MOHD/HC', 'user', 'MobileCare-Manila', 0, '2026-01-31 11:43:20', '2026-01-31 21:08:46', NULL, NULL, 'sample', '', NULL, 'Engineer', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chubb_claims`
--
ALTER TABLE `chubb_claims`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `claim_no` (`claim_no`);

--
-- Indexes for table `endorsements`
--
ALTER TABLE `endorsements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `engineers`
--
ALTER TABLE `engineers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `escalations`
--
ALTER TABLE `escalations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sites`
--
ALTER TABLE `sites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `site_code` (`site_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chubb_claims`
--
ALTER TABLE `chubb_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `endorsements`
--
ALTER TABLE `endorsements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `engineers`
--
ALTER TABLE `engineers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `escalations`
--
ALTER TABLE `escalations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sites`
--
ALTER TABLE `sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `engineers`
--
ALTER TABLE `engineers`
  ADD CONSTRAINT `engineers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
