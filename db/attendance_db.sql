-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2025 at 07:43 PM
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
(4, 'Sarah Williams', '$2y$10$87T0heCIWjYgiklGyOqoa.rbPh6NxOwMUfT.YHc/Jir70N6nQ5L2y', 'sarah.w@example.com', '', '2025-05-26 15:49:51'),
(5, 'Michael Brown', '$2y$10$FcRbX0nhjq0jqdeBjQ.oxOu4Emhg.Hsgm6eRoIf/Hen5.yYGlRXSe', 'michael.b@example.com', '', '2025-05-26 15:49:51'),
(6, 'Jessica Davis', '$2y$10$ZHxCxC8DMSwFpgzNXx3QMOGMoZnMncejs8unlnKah3rTmc.vpKvPm', 'jessica.d@example.com', '', '2025-05-26 15:49:51'),
(7, 'Robert Wilson', '$2y$10$X5HuUAlIaPl6AL74s/15KuS91O1oe4zkGsw4EW8KuSHnIcN.r82R.', 'robert.w@example.com', '', '2025-05-26 15:49:51'),
(8, 'Jennifer Taylor', '$2y$10$AXZO8831aPp7rYfdgeWXMev3eQt9ehrQpfPbWx/e1Db04ID10nmS.', 'jennifer.t@example.com', '', '2025-05-26 15:49:51'),
(9, 'William Martinez', '$2y$10$.ORmImkjWFdO7ZcSBI9Mg.LOH1hPChxv0eqQBvzmJFzNIcpR0HRA6', 'william.m@example.com', '', '2025-05-26 15:49:51'),
(10, 'Lisa Anderson', '$2y$10$roaHDS9wX7vuPnyWBZSlueCRLrkGvazk3IuSt/.CATLGD3FWd0IlS', 'lisa.a@example.com', '', '2025-05-26 15:49:51');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
