-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2025 at 08:51 AM
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
-- Database: `car_rental_management`
--
CREATE DATABASE IF NOT EXISTS `car_rental_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `car_rental_management`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `contact_no`, `email_address`, `gender`, `password`, `profile_picture`, `reset_token`, `token_expiration`) VALUES
(1, 'ESTON KIAMA', '0757196660', 'engestonbrandon@gmail.com', 'male', '$2y$10$pEBsZi8h97piEiugO9z92eEdoVphbk0K.JihKySeOrfcDpRaKm4.6', 'adminprof/673f24ca792e2_prof.jpg', NULL, NULL),
(2, 'admin', '0757196660', 'admin@gmail.com', 'female', '$2y$10$0W58kh.20HUtu0pou.J7oOBrhKI/JAkH.brGn82AZJhUUNfsu5kca', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `pick_up_location` varchar(255) NOT NULL,
  `pick_up_time` time NOT NULL,
  `car_type` varchar(50) NOT NULL,
  `charge_type` varchar(50) NOT NULL,
  `driver_option` enum('yes','no') NOT NULL,
  `total_fare` decimal(10,2) NOT NULL,
  `advance_deposit` decimal(10,2) NOT NULL,
  `booking_status` enum('active','pending','completed') DEFAULT 'pending',
  `registration_no` varchar(50) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `booking_date` date DEFAULT curdate(),
  `invoice_number` varchar(255) DEFAULT NULL,
  `return_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `vehicle_id`, `customer_id`, `start_date`, `end_date`, `pick_up_location`, `pick_up_time`, `car_type`, `charge_type`, `driver_option`, `total_fare`, `advance_deposit`, `booking_status`, `registration_no`, `model_name`, `created_at`, `booking_date`, `invoice_number`, `return_date`) VALUES
(16, 3, 2, '2024-11-07', '2024-11-09', 'Shell Karen', '14:28:00', 'With AC', 'per_day', 'yes', 9600.00, 6720.00, 'completed', 'KDF 544F', 'Honda Civic', '2024-11-06 11:26:36', '2024-11-06', NULL, NULL),
(17, 4, 1, '2024-11-08', '2024-11-10', 'Lenana', '17:48:00', 'With AC', 'per_day', 'yes', 13000.00, 9100.00, 'completed', 'KAZ 312Y', 'Convertible', '2024-11-07 12:48:30', '2024-11-07', NULL, NULL),
(19, 5, 3, '2024-11-08', '2024-11-09', 'Karen shell', '16:44:00', 'With AC', 'per_day', 'no', 9500.00, 6650.00, 'completed', 'KAL 456K', 'Chevrolet Silverado', '2024-11-07 13:44:34', '2024-11-07', NULL, NULL),
(20, 6, 1, '2024-11-08', '2024-11-09', 'Lenana', '17:14:00', 'With AC', 'per_day', 'no', 3400.00, 2380.00, 'completed', 'KDN 761A', 'VOLKSWAGEN GOLF', '2024-11-07 14:13:25', '2024-11-07', NULL, NULL),
(21, 8, 3, '2024-11-07', '2024-11-09', 'Corner', '19:16:00', 'With AC', 'per_day', 'yes', 6600.00, 4620.00, 'completed', 'KBG 516T', 'Sedan', '2024-11-07 14:14:40', '2024-11-07', NULL, NULL),
(22, 7, 2, '2024-11-08', '2024-11-09', 'Kindaruma road', '15:45:00', 'With AC', 'per_day', 'yes', 12500.00, 8750.00, 'completed', 'KDL 432W', 'Range Rover Sport', '2024-11-07 14:18:52', '2024-11-07', NULL, NULL),
(23, 9, 1, '2024-11-08', '2024-11-09', 'Lenana', '16:16:00', 'With AC', 'per_day', 'yes', 4000.00, 2800.00, 'completed', 'KAZ 702A', 'Minivan', '2024-11-07 14:47:12', '2024-11-07', NULL, NULL),
(24, 10, 2, '2024-11-07', '2024-11-09', 'Kindaruma road', '17:55:00', 'With AC', 'per_day', 'yes', 12000.00, 8400.00, 'completed', 'KDK 423F', 'Coupe', '2024-11-07 14:47:42', '2024-11-07', NULL, NULL),
(25, 14, 3, '2024-11-08', '2024-11-12', 'Karen shell', '13:20:00', 'With AC', 'per_day', 'no', 28000.00, 19600.00, 'completed', 'KDM 100Z', 'Mercedez Benz', '2024-11-07 14:48:45', '2024-11-07', NULL, NULL),
(26, 12, 1, '2024-11-07', '2024-11-09', 'Shell Karen', '14:01:00', 'With AC', 'per_day', 'yes', 6000.00, 4200.00, 'completed', 'KBM 510L', 'Fortune', '2024-11-07 15:36:45', '2024-11-07', NULL, NULL),
(27, 11, 2, '2024-11-09', '2024-11-12', 'karen shell', '18:15:00', 'With AC', 'per_day', 'yes', 26000.00, 18200.00, 'completed', 'KBZ 200Y', 'Ford F-series', '2024-11-08 08:22:47', '2024-11-08', NULL, NULL),
(28, 15, 2, '2024-11-08', '2024-11-10', 'karen shell', '14:15:00', 'With AC', 'per_day', 'yes', 73000.00, 51100.00, 'completed', 'KDF 500P', 'Mercedes-Benz G Wagon', '2024-11-08 09:29:15', '2024-11-08', NULL, NULL),
(29, 16, 2, '2024-11-10', '2024-11-14', 'karen shell', '15:15:00', 'With AC', 'per_day', 'yes', 124000.00, 86800.00, 'completed', 'KDN 702R', 'Lexus Rx', '2024-11-08 10:15:04', '2024-11-08', NULL, '2024-11-30 11:21:58'),
(30, 20, 3, '2024-11-12', '2024-11-14', 'Kindaruma road', '16:16:00', 'With AC', 'per_day', 'yes', 20000.00, 14000.00, 'completed', 'KCH 412K', 'Mahindra XUV', '2024-11-08 10:16:05', '2024-11-08', NULL, NULL),
(31, 1, 3, '2024-11-22', '2024-11-23', 'Shell Karen', '15:15:00', 'Without AC', 'per_day', 'yes', 4000.00, 2800.00, 'completed', 'KBF 321W', 'Hatchback', '2024-11-21 11:30:49', '2024-11-21', NULL, NULL),
(32, 4, 3, '2024-11-24', '2024-11-26', 'Shell Karen', '16:20:00', 'With AC', 'per_day', 'yes', 13000.00, 9100.00, 'completed', 'KAZ 312Y', 'Convertible', '2024-11-21 11:35:11', '2024-11-21', NULL, '2024-11-30 11:16:57'),
(36, 14, 1, '2024-12-01', '2024-12-03', 'Kayole', '14:14:00', 'With AC', 'per_day', 'yes', 16500.00, 11200.00, 'completed', 'KDM 100Z', 'Mercedez Benz', '2024-11-30 10:54:24', '2024-11-30', NULL, '2024-11-30 11:09:35'),
(38, 1, 1, '2024-12-01', '2024-12-02', 'Kitengera', '12:12:00', 'With AC', 'per_day', 'yes', 4500.00, 3150.00, 'completed', 'KBF 321W', 'Hatchback', '2024-11-30 11:13:02', '2024-11-30', NULL, '2024-11-30 11:18:56');

-- --------------------------------------------------------

--
-- Table structure for table `cancelledbookings`
--

CREATE TABLE `cancelledbookings` (
  `cancel_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `cancellation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `cancellation_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancelledbookings`
--

INSERT INTO `cancelledbookings` (`cancel_id`, `booking_id`, `customer_id`, `vehicle_id`, `start_date`, `end_date`, `cancellation_date`, `cancellation_reason`) VALUES
(1, 39, 1, 1, '2024-11-30', '2024-12-03', '2024-11-30 11:29:24', 'I will be in a meeting'),
(2, 40, 1, 1, '2024-11-30', '2024-12-03', '2024-11-30 11:35:24', 'I will be in a meeting'),
(3, 41, 1, 1, '2024-12-01', '2024-12-04', '2024-11-30 11:36:50', ''),
(4, 42, 1, 1, '2024-12-03', '2024-12-04', '2024-12-02 10:00:43', 'I want to chage the time and date');

-- --------------------------------------------------------

--
-- Table structure for table `completed_tasks`
--

CREATE TABLE `completed_tasks` (
  `task_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `registration_no` varchar(50) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `completed_tasks`
--

INSERT INTO `completed_tasks` (`task_id`, `assignment_id`, `driver_id`, `booking_id`, `vehicle_id`, `registration_no`, `model_name`, `completed_at`, `created_at`) VALUES
(1, 7, 1, 26, 12, 'KBM 510L', 'Fortune', '2024-11-08 08:02:42', '2024-11-08 08:02:42'),
(8, 9, 1, 28, 15, 'KDF 500P', 'Mercedes-Benz G Wagon', '2024-11-08 10:04:39', '2024-11-08 10:04:39'),
(11, 10, 1, 29, 16, 'KDN 702R', 'Lexus Rx', '2024-11-08 12:54:20', '2024-11-08 12:54:20'),
(12, 11, 2, 30, 20, 'KCH 412K', 'Mahindra XUV', '2024-11-21 11:28:06', '2024-11-21 11:28:06'),
(13, 12, 2, 31, 1, 'KBF 321W', 'Hatchback', '2024-11-21 11:34:17', '2024-11-21 11:34:17'),
(14, 13, 1, 32, 4, 'KAZ 312Y', 'Convertible', '2024-11-22 10:25:27', '2024-11-22 10:25:27'),
(15, 17, 3, 36, 14, 'KDM 100Z', 'Mercedez Benz', '2024-11-30 10:55:23', '2024-11-30 10:55:23'),
(16, 19, 3, 38, 1, 'KBF 321W', 'Hatchback', '2024-11-30 11:18:47', '2024-11-30 11:18:47');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Eston Kiama', 'engestonbrandonkiama@gmail.com', 'Hio error ni ya ?', 'vp', '2024-10-25 13:48:10'),
(2, 'Eston Kiama', 'engestonbrandonkiama@gmail.com', 'Hio error ni ya ?', 'vp', '2024-10-25 13:50:16'),
(3, 'Eston Kiama', 'engestonbrandonkiama@gmail.com', 'Hio error ni ya ?', 'vp', '2024-10-25 13:54:09'),
(4, 'BRANDON ESTON', 'kiamaeston0@gmail.com', 'Hio error ni ya ?', 'vp', '2024-10-26 12:22:04'),
(5, 'MALT BRANDON', 'estonnmose@gmail.com', 'Call', 'me', '2024-11-21 12:53:18');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('active','inactive','suspended','pending') NOT NULL DEFAULT 'active',
  `last_logout` datetime DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `mobile` varchar(12) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `dob` date NOT NULL,
  `occupation` varchar(100) NOT NULL,
  `residence` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT 'default_profile.png',
  `remember_token` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL
) ;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `email`, `status`, `last_logout`, `password`, `mobile`, `gender`, `dob`, `occupation`, `residence`, `created_at`, `profile_picture`, `remember_token`, `last_login`, `reset_token`, `token_expiration`) VALUES
(1, 'Moses Karunga', 'kiamaeston0@gmail.com', 'active', '2024-12-02 13:14:05', '$2y$10$w/kqlWy45AiKgqJalwdAde/22UCEFsZtpPipg9jnRZVR.McItMGnK', '254757196660', 'male', '1993-08-24', 'Software Engineer', 'Westlands', '2024-10-29 10:46:51', 'Customerprofile/son.jpeg', '$2y$10$kfR4QTOdvSkmZq1FNd6NJuG/WFW8H9veOLJNdbYliGV7ZF6VSqMFm', '2024-12-02 13:13:55', NULL, NULL),
(2, 'Milton Otieno', 'estonnmose@gmail.com', 'active', '2024-12-02 13:14:30', '$2y$10$WQYC1636B74Nv17e1a7HNuGNwyMh9kI7WSJL7eIiJD2ABD/cqGEbq', '25471234567', 'male', '1978-04-11', 'Police Officer', 'Ngong Town', '2024-10-31 14:49:35', 'Customerprofile/my-profile-img.jpg', '$2y$10$o8wR41JFseDJSho3csevmOs3NNBxmBwT04myRXA3aB.LyZUEEGxtG', '2024-12-02 13:14:22', NULL, NULL),
(3, 'Mary Ochieng', 'mary@gmail.com', 'active', '2024-12-02 13:16:39', '$2y$10$O5/f4pB.pADTHHXVSEK7zeoXj9p9Ye1oNM1C5mo9k1rHhlZ62HUOC', '2547656555', 'female', '1976-04-12', 'Missionary', 'Kangemi', '2024-11-05 09:45:44', 'Customerprofile/Convertible.jpg', NULL, '2024-12-02 13:14:49', NULL, NULL),
(4, 'Tom Kamau', 'tomkam@gmail.com', 'active', '2024-12-02 14:57:07', '$2y$10$UD.xvr6YVEH0AG0zpAmE3ucqxGfMwf.b4mnKMbitqSr8Q3J.kx1jm', '0765554444', 'male', '2000-12-17', 'A Teacher', 'Kitengela', '2024-12-02 11:51:11', 'Customerprofile/BMW X1.jpeg', NULL, '2024-12-02 14:56:18', NULL, NULL),
(5, 'Sarah watima', 'sarah@gmail.com', 'active', '2024-12-02 15:03:55', '$2y$10$tbl7dPno/ZBwIKIpe4hBaeqZfUKAHcikcrB6MOavYa2lPDo2lhH4K', '0706831847', 'female', '1993-11-13', 'Youth leader', 'Kawangware', '2024-12-02 11:53:50', 'Customerprofile/Audi S5 Sportback.jpeg', NULL, '2024-12-02 15:02:42', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `driver_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_no` varchar(15) NOT NULL,
  `residence` text NOT NULL,
  `age` int(11) NOT NULL CHECK (`age` between 18 and 70),
  `driving_license_no` varchar(50) NOT NULL,
  `license_image` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `availability_status` enum('Available','Unavailable') DEFAULT 'Available',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`driver_id`, `name`, `contact_no`, `residence`, `age`, `driving_license_no`, `license_image`, `email`, `password`, `profile_picture`, `availability_status`, `reset_token`, `token_expiration`) VALUES
(1, 'TonyRoy Smith', '0765554444', 'Kawangware', 23, 'PHD106', 'Drivers/license.jpeg', 'kiamaeston2@gmail.com', '$2y$10$Prqkg0QdRFDQyFZ0VyaFWO8BIUBl2N4U3qpCygS9B5LbaA4XKIQjC', 'Driverprof/th.jpeg', 'Available', NULL, NULL),
(2, 'Prestone Ongoro', '0765554444', 'Ngong', 35, 'LIC-6728908c99cf0', 'Drivers/license.jpeg', 'kiamaeston0@gmail.com', '$2y$10$LlCHaivLmqaY4SpaC0TqSOr1wtqME2Ul0gvzywBGAZIChozyLUwoO', 'Driverprof/20220827_203534.jpg', 'Available', NULL, NULL),
(3, 'Crispin Wambugu', '7575196660', 'Kayole', 33, 'LIC-67404e672fa42', 'Drivers/license.jpeg', 'crispin@gmail.com', '$2y$10$YWcGb4moqhSVvQeTATClzeF/VQqzX1AphSwlJxNJthCqTRRfzfFVi', NULL, 'Available', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `driver_assignments`
--

CREATE TABLE `driver_assignments` (
  `assignment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `registration_no` varchar(20) NOT NULL,
  `model_name` varchar(50) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver_assignments`
--

INSERT INTO `driver_assignments` (`assignment_id`, `booking_id`, `vehicle_id`, `registration_no`, `model_name`, `driver_id`, `customer_id`, `fullname`, `assigned_at`) VALUES
(7, 26, 12, 'KBM 510L', 'Fortune', 1, 1, 'Moses Karunga', '2024-11-07 18:36:45'),
(8, 27, 11, 'KBZ 200Y', 'Ford F-series', 2, 2, 'Milton Otieno', '2024-11-08 11:22:47'),
(9, 28, 15, 'KDF 500P', 'Mercedes-Benz G Wagon', 1, 2, 'Milton Otieno', '2024-11-08 12:29:15'),
(10, 29, 16, 'KDN 702R', 'Lexus Rx', 1, 2, 'Milton Otieno', '2024-11-08 13:15:04'),
(11, 30, 20, 'KCH 412K', 'Mahindra XUV', 2, 3, 'Mary Ochieng', '2024-11-08 13:16:05'),
(12, 31, 1, 'KBF 321W', 'Hatchback', 2, 3, 'Mary Ochieng', '2024-11-21 14:30:49'),
(13, 32, 4, 'KAZ 312Y', 'Convertible', 1, 3, 'Mary Ochieng', '2024-11-21 14:35:11'),
(17, 36, 14, 'KDM 100Z', 'Mercedez Benz', 3, 1, 'Moses Karunga', '2024-11-30 13:54:24'),
(19, 38, 1, 'KBF 321W', 'Hatchback', 3, 1, 'Moses Karunga', '2024-11-30 14:13:02');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES
(1, 'kiamaeston2@gmail.com', '2024-10-28 08:07:19'),
(2, 'estonnmose@gmail.com', '2024-11-21 12:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `service_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `return_condition` enum('good','fair','damaged') NOT NULL,
  `additional_charges` decimal(10,2) DEFAULT 0.00,
  `service_comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `vehicle_id`, `booking_id`, `service_date`, `return_condition`, `additional_charges`, `service_comments`) VALUES
(1, 14, 36, '2024-11-30 11:09:35', 'good', 500.00, 'it was awesome ride'),
(2, 4, 32, '2024-11-30 11:16:57', 'good', 0.00, '1'),
(3, 1, 38, '2024-11-30 11:18:56', 'good', 0.00, ''),
(4, 16, 29, '2024-11-30 11:21:58', 'good', 0.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `sender` enum('customer','admin') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_messages`
--

INSERT INTO `support_messages` (`id`, `customer_id`, `sender`, `message`, `created_at`) VALUES
(25, 2, 'customer', 'Hello', '2024-11-08 09:11:34'),
(26, 2, 'admin', 'Hello', '2024-11-08 09:11:44'),
(27, 3, 'customer', 'hello', '2024-11-21 10:53:43'),
(28, 3, 'admin', 'hello', '2024-11-21 10:53:52'),
(29, 1, 'customer', 'hello', '2024-12-02 09:46:16'),
(30, 1, 'admin', 'How is you', '2024-12-02 09:46:32'),
(31, 1, 'customer', 'Am good', '2024-12-02 09:46:44'),
(32, 1, 'admin', 'Great how can i be of assistance', '2024-12-02 09:47:45'),
(33, 1, 'customer', 'I have an issue with making payments', '2024-12-02 09:48:08'),
(34, 1, 'admin', 'Yes we are having  an issue with that and we are in the process of solving it', '2024-12-02 09:48:47');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `registration_no` varchar(8) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `availability_status` enum('Available','Unavailable') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price_per_day` decimal(10,2) NOT NULL,
  `ac_price_per_day` decimal(10,2) DEFAULT NULL,
  `non_ac_price_per_day` decimal(10,2) DEFAULT NULL,
  `km_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `registration_no`, `model_name`, `description`, `availability_status`, `photo`, `created_at`, `price_per_day`, `ac_price_per_day`, `non_ac_price_per_day`, `km_price`) VALUES
(1, 'KBF 321W', 'Hatchback', 'Hatchback', 'Available', 'Cars/Hatchback.jpeg', '2024-10-29 11:12:33', 2000.00, NULL, NULL, NULL),
(2, 'KCM 213M', 'BMW X6', 'Bold, Luxurious, Powerful, Sporty, Innovative.', 'Available', 'Cars/BMW X6 M.jpeg', '2024-10-29 09:03:51', 3500.00, NULL, NULL, NULL),
(3, 'KDF 544F', 'Honda Civic', 'Bold, Luxurious, Powerful', 'Available', 'Cars/Honda Civic.png', '2024-10-29 09:05:45', 3300.00, NULL, NULL, NULL),
(4, 'KAZ 312Y', 'Convertible', 'Convertible', 'Available', 'Cars/Convertible.jpg', '2024-10-29 09:07:23', 5000.00, NULL, NULL, NULL),
(5, 'KAL 456K', 'Chevrolet Silverado', 'Powerful American pickup truck, reliable workhorse.', 'Available', 'Cars/Chevrolet Silverado.jpeg', '2024-10-29 10:21:16', 9000.00, NULL, NULL, NULL),
(6, 'KDN 761A', 'VOLKSWAGEN GOLF', 'Dynamic Chassis Control system(DCC) Launch control Daytime running LED lights. ', 'Available', 'Cars/Volkswagen Golf R.jpeg', '2024-10-29 10:52:54', 2900.00, NULL, NULL, NULL),
(7, 'KDL 432W', 'Range Rover Sport', 'Luxurious, capable, versatile, premium SUV.', 'Available', 'Cars/Range Rover Sport.jpeg', '2024-10-30 12:20:43', 10000.00, NULL, NULL, NULL),
(8, 'KBG 516T', 'Sedan', 'Sedans offer comfort and style.', 'Available', 'Cars/Sedan.jpeg', '2024-10-30 13:42:03', 1800.00, NULL, NULL, NULL),
(9, 'KAZ 702A', 'Minivan', 'Minivans provide spacious family travel.', 'Available', 'Cars/Minivan.jpg', '2024-10-30 13:43:52', 1500.00, NULL, NULL, NULL),
(10, 'KDK 423F', 'Coupe', 'Coupes offer a stylish two-door design.', 'Available', 'Cars/Coupe.jpg', '2024-10-30 13:45:37', 4500.00, NULL, NULL, NULL),
(11, 'KBZ 200Y', 'Ford F-series', 'The Ford F-Series is a popular truck line.', 'Available', 'Cars/Ford F-series.jpeg', '2024-10-30 13:47:57', 7500.00, NULL, NULL, NULL),
(12, 'KBM 510L', 'Fortune', 'Prestigious comfort and elegant design', 'Available', 'Cars/Fortuner.png', '2024-10-31 07:52:24', 1500.00, NULL, NULL, NULL),
(13, 'KDG 310P', 'Tesla', 'Electric, innovative, autonomous, stylish, efficient', 'Available', 'Cars/Tesla.jpg', '2024-10-31 13:02:48', 50000.00, NULL, NULL, NULL),
(14, 'KDM 100Z', 'Mercedez Benz', 'Elegance, performance, luxury redefined daily', 'Available', 'Cars/Mercedez Benz.jpg', '2024-10-31 17:28:25', 6500.00, NULL, NULL, NULL),
(15, 'KDF 500P', 'Mercedes-Benz G Wagon', 'Luxury, rugged, powerful, iconic, off-road.', 'Available', 'Cars/G Wagon benz.jpeg', '2024-11-05 07:17:06', 35000.00, NULL, NULL, NULL),
(16, 'KDN 702R', 'Lexus Rx', '\r\nStylish, versatile, comfortable, premium, reliable.', 'Available', 'Cars/Lexus RX 2024.jpeg', '2024-11-05 07:19:45', 30000.00, NULL, NULL, NULL),
(17, 'KBZ 421T', 'Audi S5 Sportback', 'Sleek, sporty, powerful, luxurious, dynamic.', 'Available', 'Cars/Audi S5 Sportback.jpeg', '2024-11-05 07:24:54', 18000.00, NULL, NULL, NULL),
(18, 'KDG 125A', 'Subaru Forester', 'Versatile, rugged, spacious, reliable, adventurous.', 'Available', 'Cars/Subaru Forester.jpeg', '2024-11-05 07:26:55', 10000.00, NULL, NULL, NULL),
(19, 'KAX 102', 'Volkswagen Beetle.', 'Classic, round-shaped, iconic vintage Volkswagen Beetle.', 'Available', 'Cars/Volkswagen Beetle.jpg', '2024-11-05 09:05:52', 900.00, NULL, NULL, NULL),
(20, 'KCH 412K', 'Mahindra XUV', ' With a bold and modern exterior, it showcases a distinctive grille, sleek LED headlights', 'Available', 'Cars/Mahindra XUV.jpg', '2024-11-05 11:44:36', 8500.00, NULL, NULL, NULL),
(21, 'KBF 120Y', 'Nexon', 'Tata Nexon: Stylish, safe, efficient, versatile, compact, sporty SUV.', 'Available', 'Cars/nexon.jpg', '2024-11-05 11:45:57', 7500.00, NULL, NULL, NULL),
(22, 'KBM 500P', 'Subaru Outback', 'Subaru Outback: Rugged, spacious, versatile, reliable, safe, all-terrain SUV.', 'Available', 'Cars/Subaru Outback.jpeg', '2024-11-05 11:47:52', 9500.00, NULL, NULL, NULL),
(23, 'KBN 056A', 'Toyota Harrier', 'Toyota Harrier: Stylish, comfortable, luxurious, efficient, advanced, family-friendly SUV.', 'Available', 'Cars/Toyota Harrier.jpeg', '2024-11-05 11:50:23', 7500.00, NULL, NULL, NULL),
(24, 'KAK 009I', 'Toyota Land Cruiser 250 Series', 'Toyota Land Cruiser 250 Series: Rugged, powerful, spacious, reliable, advanced, off-road-capable SUV.', 'Available', 'Cars/Toyota land Cruise 250 series.jpeg', '2024-11-05 11:53:38', 10000.00, NULL, NULL, NULL),
(25, 'KCZ 154L', 'Hilux SR 4X4 Double cab', 'Tough, versatile, powerful, durable, spacious, off-road-ready truck.', 'Available', 'Cars/Hilux SR 4X4 Double cab.jpeg', '2024-11-05 11:56:06', 19000.00, NULL, NULL, NULL),
(26, 'KCM 134k', 'BMW X6', ' Sleek, sporty, luxurious, powerful, coupe-style, dynamic, advanced, premium.', 'Available', 'Cars/BMW X6.jpeg', '2024-11-05 11:57:47', 8700.02, NULL, NULL, NULL),
(27, 'KDM 400V', 'Mazda CX-5', 'Sleek, compact, versatile crossover SUV.', 'Available', 'Cars/Mazda CX-5.jpeg', '2024-11-05 14:02:23', 4500.00, NULL, NULL, NULL),
(28, 'KAM 704H', 'Subaru WRX', 'Sporty, agile, turbocharged performance sedan.', 'Available', 'Cars/Subaru WRX.jpeg', '2024-11-05 14:04:38', 3500.00, NULL, NULL, NULL),
(29, 'KBL 411B', 'Subaru Forester XT', 'Rugged, spacious, all-wheel-drive crossover.', 'Available', 'Cars/Subaru Forester XT.jpeg', '2024-11-05 14:20:47', 6500.00, NULL, NULL, NULL),
(30, 'KDP 600O', 'BMW 523D', 'Elegant, luxurious', 'Available', 'Cars/BMW 523D.jpeg', '2024-11-05 14:22:02', 8500.00, NULL, NULL, NULL),
(31, 'KDG 011S', 'Nissan Patrol', 'Powerful, rugged, spacious off-road SUV.', 'Available', 'Cars/Nissan Patrol.jpeg', '2024-11-05 14:23:19', 95000.00, NULL, NULL, NULL),
(32, 'KCS 400Y', 'Mazda Demio', 'Compact, efficient, stylish', 'Available', 'Cars/Mazda Demio.jpeg', '2024-11-05 14:24:24', 2000.00, NULL, NULL, NULL),
(33, 'KDH 300T', 'Toyota Land Cruiser', ' Rugged, powerful, spacious, reliable, advanced, off-road-capable SUV.', 'Available', 'Cars/Toyota Land Cruiser prado.jpeg', '2024-11-21 11:19:08', 17000.00, NULL, NULL, NULL),
(38, 'KAZ 154', 'Nissan', 'Powerful', 'Available', 'Cars/Nissan Xtrail.jpeg', '2024-11-21 11:26:38', 3500.00, NULL, NULL, NULL),
(39, 'KBM 125L', 'Mercedez Benz', 'Bold, Luxurious, Powerful', 'Available', 'Cars/Mercedes Benz C200.jpeg', '2024-11-21 12:29:46', 7500.00, NULL, NULL, NULL),
(40, 'KDK 001z', 'Subaru Pleo', '\r\nThe Subaru Pleo is a compact, practical, fuel-efficient kei car.', 'Available', 'Cars/Subaru Pleo.jpeg', '2024-11-30 11:47:17', 800.00, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `cancelledbookings`
--
ALTER TABLE `cancelledbookings`
  ADD PRIMARY KEY (`cancel_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `completed_tasks`
--
ALTER TABLE `completed_tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `assignment_id` (`assignment_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`driver_id`),
  ADD UNIQUE KEY `driving_license_no` (`driving_license_no`);

--
-- Indexes for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `driver_assignments_ibfk_1` (`booking_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `cancelledbookings`
--
ALTER TABLE `cancelledbookings`
  MODIFY `cancel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `completed_tasks`
--
ALTER TABLE `completed_tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `cancelledbookings`
--
ALTER TABLE `cancelledbookings`
  ADD CONSTRAINT `cancelledbookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `cancelledbookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`);

--
-- Constraints for table `completed_tasks`
--
ALTER TABLE `completed_tasks`
  ADD CONSTRAINT `completed_tasks_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `driver_assignments` (`assignment_id`),
  ADD CONSTRAINT `completed_tasks_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`),
  ADD CONSTRAINT `completed_tasks_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`),
  ADD CONSTRAINT `completed_tasks_ibfk_4` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`);

--
-- Constraints for table `driver_assignments`
--
ALTER TABLE `driver_assignments`
  ADD CONSTRAINT `driver_assignments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `driver_assignments_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `driver_assignments_ibfk_3` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`driver_id`),
  ADD CONSTRAINT `driver_assignments_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);

--
-- Constraints for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
