-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2025 at 02:20 PM
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
-- Database: `medpass_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `first_name`, `last_name`) VALUES
(6, 200101, 'Michael', 'Scott'),
(7, 200104, 'Dwight', 'Schrute'),
(8, 200801, 'Leslie', 'Knope'),
(9, 200802, 'Ron', 'Swanson');

-- --------------------------------------------------------

--
-- Table structure for table `medical_clearance`
--

CREATE TABLE `medical_clearance` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `test_date` date DEFAULT NULL,
  `test_expiry` date DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` date NOT NULL,
  `updated_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_clearance`
--

INSERT INTO `medical_clearance` (`id`, `patient_id`, `test_id`, `status`, `test_date`, `test_expiry`, `file_name`, `file_path`, `created_at`, `updated_at`) VALUES
(44, 12, 1, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(45, 12, 2, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(46, 12, 4, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(47, 12, 5, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(48, 12, 3, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(49, 13, 1, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(50, 13, 4, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(51, 13, 3, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(52, 11, 1, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(53, 11, 2, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(54, 11, 4, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(55, 11, 5, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(56, 11, 3, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(57, 16, 1, 'Completed', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(58, 16, 2, 'Completed', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(59, 16, 4, 'Completed', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(60, 16, 5, 'Completed', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(61, 16, 3, 'Completed', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(62, 14, 1, 'Completed', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(63, 14, 2, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(64, 14, 4, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(65, 14, 5, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00'),
(66, 14, 3, 'Pending', '2025-04-05', '2025-10-05', '', '', '0000-00-00', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `medical_tests`
--

CREATE TABLE `medical_tests` (
  `id` int(11) NOT NULL,
  `test_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_tests`
--

INSERT INTO `medical_tests` (`id`, `test_name`) VALUES
(1, 'CBC'),
(2, 'Chest X-Ray'),
(4, 'Fecalysis'),
(5, 'Physical Exam'),
(3, 'Urinalysis');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `contact_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `user_id`, `first_name`, `last_name`, `dob`, `contact_number`) VALUES
(11, 200102, 'Jim', 'Halpert', '1980-10-01', '09170000001'),
(12, 200103, 'Pam', 'Beesly', '1981-03-25', '09170000002'),
(13, 200105, 'Andy', 'Bernard', '1978-07-12', '09170000003'),
(14, 200803, 'Ben', 'Wyatt', '1984-04-16', '09170000004'),
(15, 200804, 'April', 'Ludgate', '1990-05-02', '09170000005'),
(16, 200805, 'Tom', 'Haverford', '1985-12-09', '09170000006');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(6) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','patient') NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `reset_token`, `reset_expires`) VALUES
(200101, 'michael.scott@email.com', 'admin', 'admin', NULL, NULL),
(200102, 'jim.halpert@email.com', 'HALPERT', 'patient', NULL, NULL),
(200103, 'pam.beesly@email.com', 'BEESLY', 'patient', NULL, NULL),
(200104, 'dwight.schrute@email.com', 'admin', 'admin', NULL, NULL),
(200105, 'andy.bernard@email.com', 'BERNARD', 'patient', NULL, NULL),
(200801, 'leslie.knope@email.com', 'admin', 'admin', NULL, NULL),
(200802, 'ron.swanson@email.com', 'admin', 'admin', NULL, NULL),
(200803, 'ben.wyatt@email.com', 'WYATT', 'patient', NULL, NULL),
(200804, 'april.ludgate@email.com', 'LUDGATE', 'patient', NULL, NULL),
(200805, 'tom.haverford@email.com', 'HAVERFORD', 'patient', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `medical_clearance`
--
ALTER TABLE `medical_clearance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `medical_tests`
--
ALTER TABLE `medical_tests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `test_name` (`test_name`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `medical_clearance`
--
ALTER TABLE `medical_clearance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `medical_tests`
--
ALTER TABLE `medical_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200806;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_clearance`
--
ALTER TABLE `medical_clearance`
  ADD CONSTRAINT `medical_clearance_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `medical_clearance_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `medical_tests` (`id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
