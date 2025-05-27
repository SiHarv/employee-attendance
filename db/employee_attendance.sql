-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2025 at 06:58 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `employee_attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `qr_pin` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `email`, `qr_pin`, `created_at`) VALUES
(2, 'admin', '$2y$10$u01qTOMsueXPlwyIfojzqe6g.Zi3SW.omO.lkt96lb4T2lSIvJKmu', 'admin@example.com', '', '2025-05-26 14:59:51');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `time_in` time NOT NULL,
  `threshold_minute` int(11) NOT NULL,
  `time_out` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `time_in`, `threshold_minute`, `time_out`) VALUES
(1, '10:00:00', 15, '17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `time_log`
--

CREATE TABLE `time_log` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `status` enum('present','late','absent') DEFAULT 'absent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_log`
--

INSERT INTO `time_log` (`id`, `employee_id`, `time_in`, `time_out`, `status`) VALUES
(1, 1, '2025-05-19 09:21:00', '2025-05-19 17:22:00', 'late'),
(2, 2, '2025-05-19 09:25:00', '2025-05-27 16:49:00', 'late'),
(3, 3, '2025-05-19 09:14:00', '2025-05-19 16:54:00', 'present'),
(4, 4, '2025-05-19 09:20:00', '2025-05-19 17:21:00', 'late'),
(5, 5, '2025-05-19 08:47:00', '2025-05-19 17:30:00', 'present'),
(6, 6, '2025-05-19 09:09:00', '2025-05-19 17:09:00', 'present'),
(8, 8, '2025-05-19 08:56:00', '2025-05-19 17:30:00', 'present'),
(9, 9, '2025-05-19 09:06:00', '2025-05-19 17:00:00', 'present'),
(10, 1, '2025-05-20 08:45:00', '2025-05-20 17:14:00', 'present'),
(12, 3, '2025-05-20 09:10:00', '2025-05-20 16:45:00', 'present'),
(13, 4, '2025-05-20 09:06:00', '2025-05-20 17:11:00', 'present'),
(14, 5, '2025-05-20 09:23:00', '2025-05-20 16:53:00', 'late'),
(15, 6, '2025-05-20 09:24:00', '2025-05-20 16:48:00', 'late'),
(17, 8, '2025-05-20 09:08:00', '2025-05-20 17:06:00', 'present'),
(18, 9, '2025-05-20 09:28:00', '2025-05-20 17:20:00', 'late'),
(19, 10, '2025-05-20 08:47:00', '2025-05-20 17:12:00', 'present'),
(20, 1, '2025-05-21 09:28:00', '2025-05-21 16:57:00', 'late'),
(21, 3, '2025-05-21 09:29:00', '2025-05-21 16:59:00', 'late'),
(22, 4, '2025-05-21 09:23:00', '2025-05-21 16:52:00', 'late'),
(23, 5, '2025-05-21 09:10:00', '2025-05-21 17:15:00', 'present'),
(24, 6, '2025-05-21 09:23:00', '2025-05-21 17:15:00', 'late'),
(25, 8, '2025-05-21 09:27:00', '2025-05-21 17:22:00', 'late'),
(26, 9, '2025-05-21 08:54:00', '2025-05-21 16:55:00', 'present'),
(27, 10, '2025-05-21 08:57:00', '2025-05-21 17:30:00', 'present'),
(28, 1, '2025-05-22 08:54:00', '2025-05-22 16:57:00', 'present'),
(29, 2, '2025-05-22 09:08:00', '2025-05-22 16:50:00', 'present'),
(30, 3, '2025-05-22 08:51:00', '2025-05-22 16:48:00', 'present'),
(31, 4, '2025-05-22 09:22:00', '2025-05-22 16:53:00', 'late'),
(32, 5, '2025-05-22 08:59:00', '2025-05-22 17:03:00', 'present'),
(33, 8, '2025-05-22 09:14:00', '2025-05-22 17:27:00', 'present'),
(34, 10, '2025-05-22 09:05:00', '2025-05-22 17:09:00', 'present'),
(35, 1, '2025-05-23 09:12:00', '2025-05-23 16:54:00', 'present'),
(36, 2, '2025-05-23 09:25:00', '2025-05-23 16:49:00', 'late'),
(37, 3, '2025-05-23 08:54:00', '2025-05-23 17:24:00', 'present'),
(38, 4, '2025-05-23 08:48:00', '2025-05-23 17:25:00', 'present'),
(39, 6, '2025-05-23 08:45:00', '2025-05-23 17:26:00', 'present'),
(41, 8, '2025-05-23 08:58:00', '2025-05-23 17:23:00', 'present'),
(42, 9, '2025-05-23 09:29:00', '2025-05-23 17:03:00', 'late'),
(43, 10, '2025-05-23 09:05:00', '2025-05-23 17:23:00', 'present'),
(44, 1, '2025-05-24 08:45:00', '2025-05-24 16:52:00', 'present'),
(45, 2, '2025-05-24 09:28:00', '2025-05-24 17:18:00', 'late'),
(46, 3, '2025-05-24 09:09:00', '2025-05-24 17:18:00', 'present'),
(47, 4, '2025-05-24 08:46:00', '2025-05-24 17:06:00', 'present'),
(48, 5, '2025-05-24 09:30:00', '2025-05-24 16:50:00', 'late'),
(50, 8, '2025-05-24 09:03:00', '2025-05-24 17:24:00', 'present'),
(51, 9, '2025-05-24 08:58:00', '2025-05-24 17:30:00', 'present'),
(52, 10, '2025-05-24 09:15:00', '2025-05-24 17:20:00', 'present'),
(53, 2, '2025-05-25 09:09:00', '2025-05-25 17:25:00', 'present'),
(54, 3, '2025-05-25 09:01:00', '2025-05-25 17:20:00', 'present'),
(55, 4, '2025-05-25 09:00:00', '2025-05-25 16:45:00', 'present'),
(56, 5, '2025-05-25 09:05:00', '2025-05-25 17:13:00', 'present'),
(57, 6, '2025-05-25 09:16:00', '2025-05-25 17:04:00', 'late'),
(59, 8, '2025-05-25 08:47:00', '2025-05-25 17:24:00', 'present'),
(60, 9, '2025-05-25 09:09:00', '2025-05-25 17:18:00', 'present'),
(61, 10, '2025-05-25 09:24:00', '2025-05-25 16:55:00', 'late'),
(62, 1, '2025-05-26 08:57:00', NULL, 'present'),
(63, 2, '2025-05-26 08:50:00', '2025-05-26 16:52:00', 'present'),
(64, 3, '2025-05-26 08:58:00', '2025-05-26 16:52:00', 'present'),
(65, 4, '2025-05-26 09:02:00', '2025-05-26 17:23:00', 'present'),
(66, 5, '2025-05-26 09:00:00', NULL, 'present'),
(67, 6, '2025-05-26 09:01:00', '2025-05-26 17:18:00', 'present'),
(69, 8, '2025-05-26 09:23:00', '2025-05-26 16:48:00', 'late'),
(70, 9, '2025-05-27 09:29:00', '2025-05-27 17:25:00', 'late'),
(71, 10, '2025-05-26 09:10:00', NULL, 'present'),
(73, 11, '2025-05-27 11:15:44', NULL, 'late'),
(74, 4, '2025-05-27 23:32:29', NULL, 'late');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `code` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `code`, `created_at`) VALUES
(1, 'John Smith', '$2y$10$3XIz9XCbBGS08CuCwiVm8OP1xW5Qdh4iXxayflNxWYiptv.Sy/yli', 'john.smith@example.com', '', '2025-05-26 15:49:50'),
(2, 'Maria Garcia', '$2y$10$vR5CeIkmtb6iTaga9pmH.ek2ST4QYNP7QRV9D/bZeIXtJ1YfGsO5S', 'maria.garcia@example.com', '', '2025-05-26 15:49:50'),
(3, 'David Johnson', '$2y$10$FyF9QbjlFvpl6R..EDL83uHjqdTgeR6YhRtbygmwLkZqMzuqpJttG', 'david.j@example.com', '', '2025-05-26 15:49:50'),
(4, 'Sarah Williams', '$2y$10$87T0heCIWjYgiklGyOqoa.rbPh6NxOwMUfT.YHc/Jir70N6nQ5L2y', 'sarah.w@example.com', 'sarahconner', '2025-05-26 15:49:51'),
(5, 'Michael Brown', '$2y$10$FcRbX0nhjq0jqdeBjQ.oxOu4Emhg.Hsgm6eRoIf/Hen5.yYGlRXSe', 'michael.b@example.com', 'michael brown', '2025-05-26 15:49:51'),
(6, 'Jessica Davis', '$2y$10$ZHxCxC8DMSwFpgzNXx3QMOGMoZnMncejs8unlnKah3rTmc.vpKvPm', 'jessica.d@example.com', '', '2025-05-26 15:49:51'),
(8, 'Jennifer Taylor', '$2y$10$AXZO8831aPp7rYfdgeWXMev3eQt9ehrQpfPbWx/e1Db04ID10nmS.', 'jennifer.t@example.com', '', '2025-05-26 15:49:51'),
(9, 'William Martinez', '$2y$10$.ORmImkjWFdO7ZcSBI9Mg.LOH1hPChxv0eqQBvzmJFzNIcpR0HRA6', 'william.m@example.com', '', '2025-05-26 15:49:51'),
(10, 'Lisa Anderson', '$2y$10$roaHDS9wX7vuPnyWBZSlueCRLrkGvazk3IuSt/.CATLGD3FWd0IlS', 'lisa.a@example.com', '', '2025-05-26 15:49:51'),
(11, 'doms', '$2y$10$XdXgst1Ih9SD.jtB0EdeDer7HoKb1W7/eNeCJRH8BYRuTV06MaUo6', 'doms@gmail.com', 'EMPAFXVTN', '2025-05-26 18:32:08'),
(12, 'elbi', '$2y$10$eoQKLWlkMv1cZCxyfvwd.eVmG5RqTwPxtI6pc3YmudtPR2o/3VcNW', 'elbi@gmail.com', 'elbihaha', '2025-05-27 15:02:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_log`
--
ALTER TABLE `time_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `time_log`
--
ALTER TABLE `time_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `time_log`
--
ALTER TABLE `time_log`
  ADD CONSTRAINT `time_log_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
