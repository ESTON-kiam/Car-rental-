
 
CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL,
  `role` enum('superadmin','admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `admins` (`id`, `name`, `contact_no`, `email_address`, `gender`, `password`, `profile_picture`, `reset_token`, `token_expiration`, `role`) VALUES
(1, 'ESTON KIAMA', '0757196660', 'engestonbrandon@gmail.com', 'male', '$2y$10$5EyDMJu3wLrMux5NoqNJBO/oWUtesrDdsBpvq1aKjdeE1nJF9O7SK', 'adminprof/673f24ca792e2_prof.jpg', NULL, NULL, 'superadmin');



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
  `additional_charges` decimal(10,2) DEFAULT 0.00,
  `kilometers` int(11) DEFAULT NULL,
  `due_payment` decimal(10,2) DEFAULT 0.00,
  `due_payment_status` enum('paid','pending','canceled') DEFAULT 'pending',
  `return_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `bookings` (`booking_id`, `vehicle_id`, `customer_id`, `start_date`, `end_date`, `pick_up_location`, `pick_up_time`, `car_type`, `charge_type`, `driver_option`, `total_fare`, `advance_deposit`, `booking_status`, `registration_no`, `model_name`, `created_at`, `booking_date`, `invoice_number`, `additional_charges`, `kilometers`, `due_payment`, `due_payment_status`, `return_date`) VALUES
(64, 1, 6, '2025-03-11', '2025-03-13', 'Lenana', '14:30:00', 'With AC', 'per_day', 'no', 3500.00, 2450.00, 'completed', 'KBF 321W', 'Hatchback', '2025-03-10 07:47:18', '2025-03-10', 'INV-20250310-0064-0491c3', 0.00, 0, 0.00, 'pending', '2025-03-10 07:55:00');


DELIMITER $$
CREATE TRIGGER `reset_vehicle_status_on_booking_completion` AFTER UPDATE ON `bookings` FOR EACH ROW BEGIN
    IF NEW.booking_status = 'completed' THEN
        UPDATE vehicles 
        SET availability_status = 'Available', status_reason = NULL
        WHERE vehicle_id = NEW.vehicle_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `set_vehicle_status_on_booking` AFTER INSERT ON `bookings` FOR EACH ROW BEGIN
    UPDATE vehicles 
    SET availability_status = 'Unavailable', status_reason = 'Booked' 
    WHERE vehicle_id = NEW.vehicle_id;
END
$$
DELIMITER ;



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



INSERT INTO `cancelledbookings` (`cancel_id`, `booking_id`, `customer_id`, `vehicle_id`, `start_date`, `end_date`, `cancellation_date`, `cancellation_reason`) VALUES
(11, 63, 6, 1, '2025-03-11', '2025-03-12', '2025-03-10 07:46:29', 'I will be in a meeting');


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



CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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



INSERT INTO `customers` (`id`, `full_name`, `email`, `status`, `last_logout`, `password`, `mobile`, `gender`, `dob`, `occupation`, `residence`, `created_at`, `profile_picture`, `remember_token`, `last_login`, `reset_token`, `token_expiration`) VALUES
(1, 'Moses Karunga', 'kiamaeston0@gmail.com', 'active', '2025-03-04 13:45:25', '$2y$10$zjNkNayXMyapOL9019Rs3OwfanqO8GW34TAv0psbP0vhUGSCCdKne', '254757196660', 'male', '1993-08-24', 'Software Engineer', 'Westlands', '2024-10-29 10:46:51', 'Customerprofile/son.jpeg', '$2y$10$kfR4QTOdvSkmZq1FNd6NJuG/WFW8H9veOLJNdbYliGV7ZF6VSqMFm', '2025-03-04 11:07:58', NULL, NULL),
(2, 'Milton Otieno', 'estonnmose@gmail.com', 'active', '2025-03-06 13:06:12', '$2y$10$0mN3Xb.oUPuf6mJ.nqf7xetL6oRf9OaTTp2BMlzqPN//f5cZcO2fy', '25471234567', 'female', '1978-04-11', 'Police Officer', 'Ngong Town', '2024-10-31 14:49:35', 'Customerprofile/my-profile-img.jpg', '$2y$10$o8wR41JFseDJSho3csevmOs3NNBxmBwT04myRXA3aB.LyZUEEGxtG', '2025-03-10 11:12:48', NULL, NULL),
(4, 'Tom Kamau', 'tomkam@gmail.com', 'active', '2024-12-02 14:57:07', '$2y$10$UD.xvr6YVEH0AG0zpAmE3ucqxGfMwf.b4mnKMbitqSr8Q3J.kx1jm', '0765554444', 'male', '2000-12-17', 'A Teacher', 'Kitengela', '2024-12-02 11:51:11', 'Customerprofile/BMW X1.jpeg', NULL, '2024-12-02 14:56:18', NULL, NULL),
(6, 'Mary Ochieng', 'mary@gmail.com', 'active', '2025-03-10 11:12:36', '$2y$10$mxzJAoxsUAklP23EUnYGVORUKoYRQItrElwEg/57p.K2ufuTiZOrW', '7575196660', 'male', '2002-11-11', 'Doctor', 'Kangemi', '2025-02-28 08:49:39', 'Customerprofile/BMW 523D.jpeg', NULL, '2025-03-10 11:09:52', NULL, NULL);


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
(1, 'TonyRoy Smith', '0765554444', 'Kawangware', 23, 'PHD106', 'Drivers/license.jpeg', 'kiamaeston2@gmail.com', '$2y$10$pA3Lnptk7cFSM3jnM4eYTup/6t5hBRt5e8lTiBJD1MxaBkJdHGDlq', 'Driverprof/th.jpeg', 'Available', NULL, NULL),
(2, 'Prestone Ongoro', '0765554444', 'Ngong', 35, 'LIC-6728908c99cf0', 'Drivers/license.jpeg', 'kiamaeston0@gmail.com', '$2y$10$iTrB6jM1Pl51HYWE3drKI.Q1eVjzWMdasM2ocf9qUP5Fu0ghmB2sO', 'Driverprof/20220827_203534.jpg', 'Available', NULL, NULL);

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

-- --------------------------------------------------------

--
-- Table structure for table `email_reminders`
--

CREATE TABLE `email_reminders` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reminder_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `email_reminders`
--

INSERT INTO `email_reminders` (`id`, `booking_id`, `customer_id`, `customer_email`, `customer_name`, `end_date`, `status`, `created_at`, `reminder_time`) VALUES
(1, 55, 2, 'estonnmose@gmail.com', 'Milton Otieno', '2025-03-10', 'pending', '2025-03-06 07:31:51', NULL),
(2, 56, 2, 'estonnmose@gmail.com', 'Milton Otieno', '2025-03-07', 'pending', '2025-03-06 07:55:36', NULL),
(3, 57, 2, 'estonnmose@gmail.com', 'Milton Otieno', '2025-03-07', 'pending', '2025-03-06 08:01:55', NULL),
(4, 58, 2, 'estonnmose@gmail.com', 'Milton Otieno', '2025-03-07', 'pending', '2025-03-06 08:10:46', NULL),
(5, 59, 2, 'estonnmose@gmail.com', 'Milton Otieno', '2025-03-08', 'pending', '2025-03-06 08:12:51', NULL),
(6, 61, 2, 'estonnmose@gmail.com', 'Milton Otieno', '2025-03-08', 'pending', '2025-03-06 08:46:54', '2025-03-08 12:00:00'),
(7, 62, 2, 'estonnmose@gmail.com', 'Milton Otieno', '2025-03-08', 'pending', '2025-03-06 08:58:57', '2025-03-08 12:00:00'),
(8, 63, 6, 'mary@gmail.com', 'Mary Ochieng', '2025-03-12', 'pending', '2025-03-10 07:44:41', '2025-03-12 12:00:00'),
(9, 64, 6, 'mary@gmail.com', 'Mary Ochieng', '2025-03-13', 'pending', '2025-03-10 07:47:22', '2025-03-13 12:00:00');

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
  `service_comments` text DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `vehicle_id`, `booking_id`, `service_date`, `return_condition`, `additional_charges`, `service_comments`, `rating`) VALUES
(12, 1, 64, '2025-03-10 07:55:00', 'good', 0.00, 'the ride was great', 5);

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
  `km_price` decimal(10,2) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT 0.00,
  `status_reason` enum('Booked','Under Service') DEFAULT NULL,
  `original_price_per_day` decimal(10,2) DEFAULT NULL,
  `original_ac_price_per_day` decimal(10,2) DEFAULT NULL,
  `original_non_ac_price_per_day` decimal(10,2) DEFAULT NULL,
  `original_km_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `registration_no`, `model_name`, `description`, `availability_status`, `photo`, `created_at`, `price_per_day`, `ac_price_per_day`, `non_ac_price_per_day`, `km_price`, `discount_percentage`, `status_reason`, `original_price_per_day`, `original_ac_price_per_day`, `original_non_ac_price_per_day`, `original_km_price`) VALUES
(1, 'KBF 321W', 'Hatchback', 'Hatchback', 'Available', 'Cars/Hatchback.jpeg', '2024-10-29 11:12:33', 1750.00, 1750.00, 1400.00, 1050.00, 30.00, NULL, 2500.00, 2500.00, 2000.00, 1500.00),
(2, 'KCM 213M', 'BMW X6', 'Bold, Luxurious, Powerful, Sporty, Innovative.', 'Available', 'Cars/BMW X6 M.jpeg', '2024-10-29 09:03:51', 3500.00, 3500.00, 3100.00, 2000.00, 0.00, NULL, 3500.00, 3500.00, 3100.00, 2000.00),
(3, 'KDF 544F', 'Honda Civic', 'Bold, Luxurious, Powerful', 'Available', 'Cars/Honda Civic.png', '2024-10-29 09:05:45', 3300.00, 3300.00, 3000.00, 1500.00, 0.00, NULL, 3300.00, 3300.00, 2999.99, 2300.00),
(4, 'KAZ 312Y', 'Convertible', 'Convertible', 'Available', 'Cars/Convertible.jpg', '2024-10-29 09:07:23', 5000.00, 5000.00, 4000.00, 2500.00, 0.00, NULL, 5000.00, 5000.00, 4500.00, 2400.00),
(5, 'KAL 456K', 'Chevrolet Silverado', 'Powerful American pickup truck, reliable workhorse.', 'Available', 'Cars/Chevrolet Silverado.jpeg', '2024-10-29 10:21:16', 9000.00, 9000.00, 7000.00, 2000.00, 0.00, NULL, 9000.00, 9000.00, 7000.00, 2000.00),
(6, 'KDN 761A', 'VOLKSWAGEN GOLF', 'Dynamic Chassis Control system(DCC) Launch control Daytime running LED lights. ', 'Available', 'Cars/Volkswagen Golf R.jpeg', '2024-10-29 10:52:54', 2900.00, 3900.00, 2700.00, 1500.00, 0.00, NULL, 3900.00, 3900.00, 3500.00, 1500.00),
(7, 'KDL 432W', 'Range Rover Sport', 'Luxurious, capable, versatile, premium SUV.', 'Available', 'Cars/Range Rover Sport.jpeg', '2024-10-30 12:20:43', 10000.00, 10000.00, 9000.00, 5000.00, 0.00, NULL, 10000.00, 10000.00, 9000.00, 5000.00),
(8, 'KBG 516T', 'Sedan', 'Sedans offer comfort and style.', 'Available', 'Cars/Sedan.jpeg', '2024-10-30 13:42:03', 1800.00, 1800.00, 1700.00, 1000.00, 0.00, NULL, 1800.00, 1800.00, 1700.00, 1000.00),
(9, 'KAZ 702A', 'Minivan', 'Minivans provide spacious family travel.', 'Available', 'Cars/Minivan.jpg', '2024-10-30 13:43:52', 1500.00, 1500.00, 1300.00, 800.00, 0.00, NULL, 1500.00, 1500.00, 1300.00, 800.00),
(10, 'KDK 423F', 'Coupe', 'Coupes offer a stylish two-door design.', 'Available', 'Cars/Coupe.jpg', '2024-10-30 13:45:37', 4500.00, 4600.00, 4200.00, 2500.00, 0.00, NULL, 4500.00, 4500.00, 4200.00, 2500.00),
(11, 'KBZ 200Y', 'Ford F-series', 'The Ford F-Series is a popular truck line.', 'Available', 'Cars/Ford F-series.jpeg', '2024-10-30 13:47:57', 7500.00, 7500.00, 5000.00, 1200.00, 0.00, NULL, 7500.00, 7500.00, 5000.00, 1700.00),
(12, 'KBM 510L', 'Fortune', 'Prestigious comfort and elegant design', 'Available', 'Cars/Fortuner.png', '2024-10-31 07:52:24', 3300.00, 3300.00, 2500.00, 1200.00, 0.00, NULL, 3300.00, 3300.00, 2500.00, 1200.00),
(13, 'KDG 310P', 'Tesla', 'Electric, innovative, autonomous, stylish, efficient', 'Available', 'Cars/Tesla.jpg', '2024-10-31 13:02:48', 50000.00, 50000.00, 45000.00, 9000.00, 0.00, NULL, 50000.00, 50000.00, 45000.00, 9000.00),
(14, 'KDM 100Z', 'Mercedez Benz', 'Elegance, performance, luxury redefined daily', 'Available', 'Cars/Mercedez Benz.jpg', '2024-10-31 17:28:25', 8500.00, 8600.00, 8000.00, 4500.00, 0.00, NULL, 8500.00, 8500.00, 8000.00, 4500.00),
(15, 'KDF 500P', 'Mercedes-Benz G Wagon', 'Luxury, rugged, powerful, iconic, off-road.', 'Available', 'Cars/G Wagon benz.jpeg', '2024-11-05 07:17:06', 35000.00, 35000.00, 29000.00, 2500.00, 0.00, NULL, 35000.00, 35000.00, 30000.00, 4000.00),
(16, 'KDN 702R', 'Lexus Rx 2024', 'Stylish, versatile, comfortable, premium, reliable.', 'Available', 'Cars/Lexus RX 2024.jpeg', '2024-11-05 07:19:45', 30000.00, 30000.00, 25000.00, 1900.00, 0.00, NULL, 30000.00, 30000.00, 28000.00, 1900.00),
(17, 'KBZ 421T', 'Audi S5 Sportback', 'Sleek, sporty, powerful, luxurious, dynamic.', 'Available', 'Cars/Audi S5 Sportback.jpeg', '2024-11-05 07:24:54', 18000.00, 18000.00, 15000.00, 5000.00, 0.00, NULL, 18000.00, 18000.00, 15000.00, 5000.00),
(18, 'KDG 125A', 'Subaru Forester', 'Versatile, rugged, spacious, reliable, adventurous.', 'Available', 'Cars/Subaru Forester.jpeg', '2024-11-05 07:26:55', 10000.00, 10000.00, 8000.00, 1700.00, 0.00, NULL, 17000.00, 17000.00, 8000.00, 1700.00),
(19, 'KAX 102', 'Volkswagen Beetle.', 'Classic, round-shaped, iconic vintage Volkswagen Beetle.', 'Available', 'Cars/Volkswagen Beetle.jpg', '2024-11-05 09:05:52', 900.00, 1000.00, 300.00, 450.00, 0.00, NULL, 1100.00, 1100.00, 900.00, 450.00),
(20, 'KCH 412K', 'Mahindra XUV', ' With a bold and modern exterior, it showcases a distinctive grille, sleek LED headlights', 'Available', 'Cars/Mahindra XUV.jpg', '2024-11-05 11:44:36', 8500.00, 8500.00, 7000.00, 2100.00, 0.00, NULL, 8600.00, 8600.00, 8000.00, 2000.00),
(21, 'KBF 120Y', 'Nexon', 'Tata Nexon: Stylish, safe, efficient, versatile, compact, sporty SUV.', 'Available', 'Cars/nexon.jpg', '2024-11-05 11:45:57', 7500.00, 7500.00, 6500.00, 1750.00, 0.00, NULL, 7500.00, 7500.00, 7000.00, 1750.00),
(22, 'KBM 500P', 'Subaru Outback', 'Subaru Outback: Rugged, spacious, versatile, reliable, safe, all-terrain SUV.', 'Available', 'Cars/Subaru Outback.jpeg', '2024-11-05 11:47:52', 9500.00, 9500.00, 8500.00, 1900.00, 0.00, NULL, 9500.00, 9500.00, 8500.00, 1900.00),
(23, 'KBN 056A', 'Toyota Harrier', 'Toyota Harrier: Stylish, comfortable, luxurious, efficient, advanced, family-friendly SUV.', 'Available', 'Cars/Toyota Harrier.jpeg', '2024-11-05 11:50:23', 7500.00, 7500.00, 6500.00, 2000.00, 0.00, NULL, 7500.00, 7500.00, 6500.00, 2000.00),
(24, 'KAK 009I', 'Toyota Land Cruiser 250 Series', 'Toyota Land Cruiser 250 Series: Rugged, powerful, spacious, reliable, advanced, off-road-capable SUV.', 'Available', 'Cars/Toyota land Cruise 250 series.jpeg', '2024-11-05 11:53:38', 10500.00, 10500.00, 9000.00, 2500.00, 0.00, NULL, 10500.00, 10500.00, 9000.00, 2500.00),
(25, 'KCZ 154L', 'Hilux SR 4X4 Double cab', 'Tough, versatile, powerful, durable, spacious, off-road-ready truck.', 'Available', 'Cars/Hilux SR 4X4 Double cab.jpeg', '2024-11-05 11:56:06', 19000.00, 19000.00, 16000.00, 6070.00, 0.00, NULL, 19000.00, 19000.00, 16500.00, 6070.00),
(26, 'KCM 134k', 'BMW X6', ' Sleek, sporty, luxurious, powerful, coupe-style, dynamic, advanced, premium.', 'Available', 'Cars/BMW X6.jpeg', '2024-11-05 11:57:47', 8700.02, 8700.00, 7700.00, 2000.00, 0.00, NULL, 8700.00, 8700.00, 7700.00, 2000.00),
(27, 'KDM 400V', 'Mazda CX-5', 'Sleek, compact, versatile crossover SUV.', 'Available', 'Cars/Mazda CX-5.jpeg', '2024-11-05 14:02:23', 4500.00, 4500.00, 3500.00, 1000.00, 0.00, NULL, 4500.00, 4500.00, 3500.00, 1000.00),
(28, 'KAM 704H', 'Subaru WRX', 'Sporty, agile, turbocharged performance sedan.', 'Available', 'Cars/Subaru WRX.jpeg', '2024-11-05 14:04:38', 3500.00, 3500.00, 2500.00, 1500.00, 0.00, NULL, 3500.00, 3500.00, 2500.00, 1650.00),
(29, 'KBL 411B', 'Subaru Forester XT', 'Rugged, spacious, all-wheel-drive crossover.', 'Available', 'Cars/Subaru Forester XT.jpeg', '2024-11-05 14:20:47', 6500.00, 6500.00, 5000.00, 1600.00, 0.00, NULL, 6500.00, 6500.00, 5000.00, 1650.00),
(30, 'KDP 600O', 'BMW 523D', 'Elegant, luxurious', 'Available', 'Cars/BMW 523D.jpeg', '2024-11-05 14:22:02', 8500.00, 8500.00, 7900.00, 2300.00, 0.00, NULL, 8500.00, 8500.00, 7500.00, 2150.00),
(31, 'KDG 011S', 'Nissan Patrol', 'Powerful, rugged, spacious off-road SUV.', 'Available', 'Cars/Nissan Patrol.jpeg', '2024-11-05 14:23:19', 65000.00, 65000.00, 60000.00, 15000.00, 0.00, NULL, 4500.00, 4500.00, 3000.00, 1400.00),
(32, 'KCS 400Y', 'Mazda Demio', 'Compact, efficient, stylish', 'Available', 'Cars/Mazda Demio.jpeg', '2024-11-05 14:24:24', 2500.00, 2500.00, 2000.00, 700.00, 0.00, NULL, 2500.00, 2500.00, 2000.00, 700.00),
(33, 'KDH 300T', 'Toyota Land Cruiser', ' Rugged, powerful, spacious, reliable, advanced, off-road-capable SUV.', 'Available', 'Cars/Toyota Land Cruiser prado.jpeg', '2024-11-21 11:19:08', 17000.00, 17000.00, 15000.00, 3000.00, 0.00, NULL, 17000.00, 17000.00, 15000.00, 3550.00),
(38, 'KAZ 154', 'Nissan', 'Powerful', 'Available', 'Cars/Nissan Xtrail.jpeg', '2024-11-21 11:26:38', 3500.00, 3500.00, 3000.00, 1400.00, 0.00, NULL, 3500.00, 3500.00, 3000.00, 1350.00),
(39, 'KBM 125L', 'Mercedez Benz', 'Bold, Luxurious, Powerful', 'Available', 'Cars/Mercedes Benz C200.jpeg', '2024-11-21 12:29:46', 7500.00, 7600.00, 7000.00, 2000.00, 0.00, NULL, 7500.00, 7500.00, 7000.00, 2000.00),
(40, 'KDK 001z', 'Subaru Pleo', 'The Subaru Pleo is a compact, practical, fuel-efficient kei car.', 'Available', 'Cars/Subaru Pleo.jpeg', '2024-11-30 11:47:17', 800.00, 1000.00, 800.00, 500.00, 0.00, NULL, 1500.00, 1500.00, 800.00, 500.00),
(41, 'KDG 102G', 'BMW X1', 'Luxury compact SUV, sporty, efficient.', 'Available', 'Cars/BMW X1.jpeg', '2025-03-05 09:56:47', 0.00, NULL, NULL, NULL, 0.00, NULL, 7500.00, 7500.00, 6500.00, 1950.00),
(42, 'KDN 240K', 'Lexus Rx350', 'Luxury midsize SUV, smooth, reliable.', 'Available', 'Cars/Lexus Rx350.jpeg', '2025-03-05 10:06:49', 8150.00, 8150.00, 7150.00, 2000.00, 0.00, NULL, 8150.00, 8150.00, 7150.00, 2000.00);

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
-- Indexes for table `email_reminders`
--
ALTER TABLE `email_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `cancelledbookings`
--
ALTER TABLE `cancelledbookings`
  MODIFY `cancel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `completed_tasks`
--
ALTER TABLE `completed_tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `email_reminders`
--
ALTER TABLE `email_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=276;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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


ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;


ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `services_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`);


ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);
COMMIT;

