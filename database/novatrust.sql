-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2025 at 11:58 AM
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
-- Database: `novatrust`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `fullname`, `email`, `password`) VALUES
(1, 'Admin User', 'admin@gmail.com', '$2y$10$..LS3SXW44ey9CUx9fjXJ.2UlMeuQ7fBma2L3AYvkQise/LbMyaku');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `created_at`) VALUES
(1, 1, '💰 Credit Alert', 'You have received USD40,000.00 from NovaTrust Bank. Reason: for bills ', 'alert', '2025-07-17 07:27:06'),
(2, 1, '💰 Credit Alert', 'You have received USD70,000.00 from NovaTrust Bank. Reason: payment for online job completed yesterday', 'alert', '2025-07-20 06:24:11'),
(3, 1, '💰 Credit Alert', 'You have received USD700,000.00 from NovaTrust Bank. Reason: user bonus ', 'alert', '2025-07-20 06:42:06'),
(4, 1, 'Virtual Card Blocked', 'Your virtual card has been blocked due to defaulting company policy.', 'warning', '2025-07-20 07:05:46'),
(5, 1, '💰 Credit Alert', 'You have received USD70,000.00 from NovaTrust Bank. Reason: for bill', 'alert', '2025-07-20 11:59:26'),
(6, 3, '💰 Credit Alert', 'You have received USD - US D4,000.00 from NovaTrust Bank. Reason: salary', 'alert', '2025-07-27 11:40:43'),
(7, 3, 'Virtual Card Blocked', 'Your virtual card has been blocked due to defaulting company policy.', 'warning', '2025-07-27 11:53:30');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `refunded_by` varchar(200) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refunds`
--

INSERT INTO `refunds` (`id`, `user_id`, `account_number`, `amount`, `reason`, `refunded_by`, `status`, `created_at`) VALUES
(1, 1, '9701421092', 80000.00, 'for fee ', 'Admin User', 'pending', '2025-07-20 12:08:04'),
(2, 3, '6433398059', 1000.00, 'imf code fee', 'Admin User', 'pending', '2025-07-27 11:41:24');

-- --------------------------------------------------------

--
-- Table structure for table `support`
--

CREATE TABLE `support` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_requests`
--

CREATE TABLE `support_requests` (
  `id` int(11) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date_sent` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `amount`, `currency`, `type`, `message`, `receiver_id`, `status`, `method`, `reference`, `created_at`) VALUES
(1, 1, 40000.00, 'USD', 'credit', 'for bills ', 1, NULL, NULL, NULL, '2025-07-17 07:27:06'),
(2, 1, 70000.00, 'USD', 'credit', 'payment for online job completed yesterday', 1, NULL, NULL, NULL, '2025-07-20 06:24:11'),
(3, 1, 700000.00, 'USD', 'credit', 'user bonus ', 1, NULL, NULL, NULL, '2025-07-20 06:42:06'),
(4, 1, 70000.00, 'USD', 'credit', 'for bill', 1, NULL, NULL, NULL, '2025-07-20 11:59:26'),
(5, 3, 4000.00, 'USD - US D', 'credit', 'salary', 3, NULL, NULL, NULL, '2025-07-27 11:40:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `fullname` varchar(200) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `refunded_balance` decimal(15,2) DEFAULT 0.00,
  `account_number` varchar(30) DEFAULT NULL,
  `account_status` varchar(20) DEFAULT 'active',
  `account_type` varchar(30) DEFAULT 'regular',
  `employment` varchar(50) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `is_upgraded` tinyint(1) DEFAULT 0,
  `is_upgrade_verified` tinyint(1) DEFAULT 0,
  `is_kyc_verified` tinyint(1) DEFAULT 0,
  `kyc_code` varchar(50) DEFAULT NULL,
  `is_imf_verified` tinyint(1) DEFAULT 0,
  `imf_code` varchar(50) DEFAULT NULL,
  `is_vat_verified` tinyint(1) DEFAULT 0,
  `vat_code` varchar(50) DEFAULT NULL,
  `is_ars_verified` tinyint(1) DEFAULT 0,
  `ars_code` varchar(50) DEFAULT NULL,
  `is_withdrawal_verified` tinyint(1) DEFAULT 0,
  `withdrawal_code` varchar(50) DEFAULT NULL,
  `is_virtual_card_cleared` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `fullname`, `email`, `phone`, `country`, `currency`, `balance`, `refunded_balance`, `account_number`, `account_status`, `account_type`, `employment`, `gender`, `is_upgraded`, `is_upgrade_verified`, `is_kyc_verified`, `kyc_code`, `is_imf_verified`, `imf_code`, `is_vat_verified`, `vat_code`, `is_ars_verified`, `ars_code`, `is_withdrawal_verified`, `withdrawal_code`, `is_virtual_card_cleared`, `reset_token`, `password`, `dob`, `created_at`) VALUES
(1, 'Gary M', 'steve', 'Turner', NULL, 'christophergarry414@gmail.com', '6065243903', 'United States', 'USD', 960000.00, 80000.00, '9701421092', 'active', 'Personal', 'Employed', 'Male', 0, 0, 1, 'KYC-67AAEAF3', 0, NULL, 0, NULL, 0, NULL, 0, NULL, 1, NULL, '$2y$10$YPmp6w41az8Eji4C.m1DFe6K.frRtmKKOVd2BxSAa7QfkszXikfli', '2001-10-08', '2025-07-16 08:43:13'),
(2, 'james', 'angel', 'grace', NULL, 'gabrieljuan2244@gmail.com', '5476872-4593', 'Belgium', 'EUR', 0.00, 0.00, '6394547152', 'Active', 'Personal', 'Employed', 'Female', 0, 0, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, 0, NULL, '$2y$10$qJbsBSz7KRVBy5PXJXaFiuXlpj3IbYu6aM3fUYQtfv5T24tnK.tA2', '2001-06-17', '2025-07-17 09:14:29'),
(3, 'john', 'fred', 'klerk', NULL, 'gred@gmail.com', '44678936782', 'United Kingdom', 'USD - US D', 5000.00, 1000.00, '6433398059', 'active', 'upgraded', 'employed', 'Male', 1, 1, 1, 'KYC-0DDE763C', 1, 'IMF12345', 1, 'VAT2468', 1, 'ARS8377', 1, 'W1223', 1, NULL, '$2y$10$1AMIBNyjJ3OajvUjFNNAs.PKKIScgZRiSD8zl9k4KXYfL1YsCcjzq', '1999-12-25', '2025-07-27 11:08:02');

-- --------------------------------------------------------

--
-- Table structure for table `virtual_cards`
--

CREATE TABLE `virtual_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `card_number` varchar(30) DEFAULT NULL,
  `expiry_date` varchar(10) DEFAULT NULL,
  `cvv` varchar(10) DEFAULT NULL,
  `cardholder_name` varchar(200) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `is_virtual_card_approved` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `virtual_cards`
--

INSERT INTO `virtual_cards` (`id`, `user_id`, `card_number`, `expiry_date`, `cvv`, `cardholder_name`, `balance`, `status`, `is_virtual_card_approved`, `created_at`) VALUES
(1, 1, '46323 5469 8567 7178', '06/28', '998', 'Cardholder', NULL, 'Blocked', 0, '2025-07-20 06:29:43'),
(2, 3, '47843 9168 1416 5034', '11/28', '327', 'john klerk', NULL, 'Blocked', 1, '2025-07-27 11:47:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support`
--
ALTER TABLE `support`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_requests`
--
ALTER TABLE `support_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `account_number` (`account_number`);

--
-- Indexes for table `virtual_cards`
--
ALTER TABLE `virtual_cards`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `support`
--
ALTER TABLE `support`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_requests`
--
ALTER TABLE `support_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `virtual_cards`
--
ALTER TABLE `virtual_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
