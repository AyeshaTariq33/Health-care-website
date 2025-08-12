-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 06:49 PM
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
-- Database: `healthcare-db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `date`, `reason`, `status`) VALUES
(1, 7, 2, '2025-05-14 08:00:00', 'For General Physician', 'pending'),
(2, 7, 16, '2025-05-23 09:44:00', 'kjij', 'completed'),
(3, 10, 16, '2025-05-16 10:05:00', 'none', 'completed'),
(4, 7, 11, '2025-05-13 11:00:00', 'Nutrition Guide', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `batch_assignments`
--

CREATE TABLE `batch_assignments` (
  `id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL COMMENT 'lab_reports.id',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diagnoses`
--

CREATE TABLE `diagnoses` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `diagnosis` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_maintenance`
--

CREATE TABLE `equipment_maintenance` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `maintenance_type` enum('routine','repair','calibration') NOT NULL,
  `description` text NOT NULL,
  `performed_date` date NOT NULL,
  `next_due_date` date DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_equipment`
--

CREATE TABLE `lab_equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_number` varchar(50) DEFAULT NULL,
  `status` enum('active','maintenance','out_of_service') DEFAULT 'active',
  `last_calibration` date DEFAULT NULL,
  `next_calibration` date DEFAULT NULL,
  `location` varchar(50) DEFAULT 'Main Lab'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `lab_equipment`
--

INSERT INTO `lab_equipment` (`id`, `name`, `model`, `serial_number`, `status`, `last_calibration`, `next_calibration`, `location`) VALUES
(1, 'Centrifuge X-200', 'X-200 v3.2', NULL, 'active', '2025-04-15', NULL, 'Main Lab'),
(2, 'Hematology Analyzer', 'HA-5000', NULL, 'active', '2025-03-20', NULL, 'Main Lab'),
(3, 'Chemistry Analyzer', 'CA-7600', NULL, 'active', '2025-05-01', NULL, 'Main Lab'),
(4, 'Microscope', 'Olympus CX43', NULL, 'maintenance', '2024-12-10', NULL, 'Main Lab');

-- --------------------------------------------------------

--
-- Table structure for table `lab_orders`
--

CREATE TABLE `lab_orders` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `test_type_id` int(11) NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_reports`
--

CREATE TABLE `lab_reports` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `result` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `is_abnormal` tinyint(1) DEFAULT 0,
  `critical_flag` tinyint(1) DEFAULT 0,
  `technician_id` int(11) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `report_date` date DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_requests`
--

CREATE TABLE `lab_requests` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `test_type_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `priority` enum('routine','urgent') DEFAULT 'routine',
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `lab_requests`
--

INSERT INTO `lab_requests` (`id`, `doctor_id`, `patient_id`, `appointment_id`, `test_type_id`, `notes`, `priority`, `status`, `created_at`) VALUES
(1, 16, 10, NULL, 4, 'hjkjk', 'routine', 'pending', '2025-05-14 06:16:01');

-- --------------------------------------------------------

--
-- Table structure for table `lab_test_types`
--

CREATE TABLE `lab_test_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `turnaround_hours` int(11) DEFAULT 24,
  `sample_type` varchar(50) DEFAULT 'blood',
  `preparation_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `lab_test_types`
--

INSERT INTO `lab_test_types` (`id`, `name`, `description`, `price`, `turnaround_hours`, `sample_type`, `preparation_instructions`) VALUES
(1, 'Complete Blood Count (CBC)', 'Measures red/white blood cells, hemoglobin, etc.', 25.00, 24, 'blood', NULL),
(2, 'Basic Metabolic Panel', 'Measures glucose, calcium, electrolytes', 35.00, 24, 'blood', NULL),
(3, 'Lipid Panel', 'Cholesterol and triglycerides', 30.00, 48, 'blood', NULL),
(4, 'Hemoglobin A1C', '3-month blood sugar average', 40.00, 72, 'blood', NULL),
(5, 'Urinalysis', 'Physical, chemical, and microscopic analysis', 20.00, 24, 'urine', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `record_type` enum('diagnosis','prescription','lab_result','procedure','note') NOT NULL,
  `record_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `name`, `quantity`, `price`, `stock`) VALUES
(1, 'Myteka', 10, 200.00, 0),
(2, 'Softin', 10, 150.00, 0),
(3, 'Panadol', 20, 100.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `medicine_orders`
--

CREATE TABLE `medicine_orders` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `prescription_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `diagnosis_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `medication` text NOT NULL,
  `dosage` text NOT NULL,
  `instructions` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending',
  `delivery_status` varchar(20) DEFAULT NULL,
  `pharmacy_id` int(11) DEFAULT NULL,
  `is_digital` tinyint(1) DEFAULT 0,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `doctor_id`, `patient_id`, `diagnosis_id`, `appointment_id`, `medication`, `dosage`, `instructions`, `created_at`, `status`, `delivery_status`, `pharmacy_id`, `is_digital`, `file_path`) VALUES
(1, 11, 10, NULL, NULL, 'Myteka', '1 Pill Per Day', 'Take empty stomach', '2025-05-13 05:56:41', 'processed', 'shipped', NULL, 0, NULL),
(2, 11, 7, NULL, NULL, 'Myteka', '1 Pill Per Day', 'Take in morning', '2025-05-13 05:58:24', 'processed', 'in_transit', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sample_collections`
--

CREATE TABLE `sample_collections` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `scheduled_date` datetime NOT NULL,
  `collector_id` int(11) DEFAULT NULL COMMENT 'Phlebotomist/staff ID',
  `status` enum('pending','scheduled','collected','failed','cancelled') DEFAULT 'pending',
  `collection_notes` text DEFAULT NULL,
  `actual_collection_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_alerts`
--

CREATE TABLE `system_alerts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `resolved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `system_alerts`
--

INSERT INTO `system_alerts` (`id`, `title`, `description`, `priority`, `resolved`, `created_at`) VALUES
(1, 'Database Backup Failed', 'Last night\'s automated backup failed to complete', 'high', 0, '2025-05-14 01:05:38'),
(2, 'High Server Load', 'CPU usage reached 95% during peak hours', 'medium', 0, '2025-05-14 01:05:38'),
(3, 'Unauthorized Access Attempt', 'Multiple failed login attempts detected', 'critical', 0, '2025-05-14 01:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `action_type`, `description`, `ip_address`, `timestamp`) VALUES
(1, 1, 'login', 'Admin user logged in', NULL, '2025-05-14 01:05:38'),
(2, NULL, 'system', 'Nightly maintenance job completed', NULL, '2025-05-14 01:05:38'),
(3, 2, 'error', 'Failed to access patient records', NULL, '2025-05-14 01:05:38');

-- --------------------------------------------------------

--
-- Table structure for table `test_batches`
--

CREATE TABLE `test_batches` (
  `id` int(11) NOT NULL,
  `batch_name` varchar(50) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `status` enum('pending','processing','completed') DEFAULT 'pending',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `hospital_affiliation` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `first_name`, `last_name`, `email`, `password_hash`, `role`, `specialization`, `license_number`, `hospital_affiliation`, `created_at`) VALUES
(7, 'Ayesha', 'Tariq', 'tariq786atm3@gmail.com', '$2y$10$ZYV3NatgRhEebIKWvskQ2uvwOcmZ8FYZkG8wt8lxwjmbR59v6QB0q', 'patient', NULL, NULL, NULL, '2025-05-12 23:35:25'),
(8, 'Test', 'Usersss', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', NULL, NULL, NULL, '2025-05-12 23:40:46'),
(10, 'Faiza', 'Ameen', 'faiza@gmail.com', '$2y$10$g60YIpz9yA5UkuGajsUwFeWb6DdEwufgStiU5QhFymIi42YiuRHbe', 'patient', NULL, NULL, NULL, '2025-05-12 23:43:26'),
(11, 'M', 'Taha', 'taha@gmail.com', '$2y$10$DCthFzuVpVx7jKKuXWifL.CTddiTPWZqOKOLOpUM6Adr/dUuOTPx2', 'doctor', NULL, NULL, NULL, '2025-05-13 00:01:03'),
(12, 'AB', 'Nisar', 'abnisar@gmail.com', '$2y$10$uitCU0OW4B./i4c8vJ0c4u3R2WQ3Pb/gxc6QWLRoe95F.5z0Gnkm6', 'admin', NULL, NULL, NULL, '2025-05-13 00:03:20'),
(14, 'Pharmacy ', '4U', 'pharmacy@gmail.com', '$2y$10$iwDaGBYcGfA.PbbYmnNcku8LGS7DW6AKtB0EK/TgCVE7p3GHH5L/2', 'pharmacy', NULL, NULL, NULL, '2025-05-13 00:09:04'),
(15, 'Lab', '4U', 'lab@gmail.com', '$2y$10$A4WKtzPPebUFfNmn11KW7OyyLccEW5/nTAqc9vE6Gt.KTtGsucMya', 'lab', NULL, NULL, NULL, '2025-05-13 00:12:56'),
(16, 'Kainat ', 'Iqbal', 'kainat@gmail.com', '$2y$10$1LN0LyzKDArlbreQJw1k1eo3pnePJVMqthQTFsiPdYV4py7CHGTKS', 'doctor', NULL, NULL, NULL, '2025-05-13 04:17:06'),
(17, 'Lab', 'Tech', 'tech@gmail.com', '', 'lab', NULL, NULL, NULL, '2025-05-14 00:49:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batch_assignments`
--
ALTER TABLE `batch_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `test_id` (`test_id`);

--
-- Indexes for table `diagnoses`
--
ALTER TABLE `diagnoses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indexes for table `lab_equipment`
--
ALTER TABLE `lab_equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_orders`
--
ALTER TABLE `lab_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `test_type_id` (`test_type_id`);

--
-- Indexes for table `lab_reports`
--
ALTER TABLE `lab_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_report_technician` (`technician_id`),
  ADD KEY `fk_report_verifier` (`verified_by`);

--
-- Indexes for table `lab_requests`
--
ALTER TABLE `lab_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `test_type_id` (`test_type_id`);

--
-- Indexes for table `lab_test_types`
--
ALTER TABLE `lab_test_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicine_orders`
--
ALTER TABLE `medicine_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `prescription_id` (`prescription_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `fk_prescription_diagnosis` (`diagnosis_id`),
  ADD KEY `fk_prescription_appointment` (`appointment_id`),
  ADD KEY `fk_prescription_pharmacy` (`pharmacy_id`);

--
-- Indexes for table `sample_collections`
--
ALTER TABLE `sample_collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `collector_id` (`collector_id`);

--
-- Indexes for table `system_alerts`
--
ALTER TABLE `system_alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_batches`
--
ALTER TABLE `test_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `batch_assignments`
--
ALTER TABLE `batch_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diagnoses`
--
ALTER TABLE `diagnoses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_equipment`
--
ALTER TABLE `lab_equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lab_orders`
--
ALTER TABLE `lab_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_reports`
--
ALTER TABLE `lab_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_requests`
--
ALTER TABLE `lab_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_test_types`
--
ALTER TABLE `lab_test_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `medicine_orders`
--
ALTER TABLE `medicine_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sample_collections`
--
ALTER TABLE `sample_collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_alerts`
--
ALTER TABLE `system_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `test_batches`
--
ALTER TABLE `test_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `batch_assignments`
--
ALTER TABLE `batch_assignments`
  ADD CONSTRAINT `batch_assignments_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `test_batches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `batch_assignments_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `lab_reports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD CONSTRAINT `equipment_maintenance_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `lab_equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_maintenance_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `lab_orders`
--
ALTER TABLE `lab_orders`
  ADD CONSTRAINT `lab_orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `lab_orders_ibfk_2` FOREIGN KEY (`test_type_id`) REFERENCES `lab_test_types` (`id`);

--
-- Constraints for table `lab_reports`
--
ALTER TABLE `lab_reports`
  ADD CONSTRAINT `fk_report_technician` FOREIGN KEY (`technician_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_report_verifier` FOREIGN KEY (`verified_by`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `lab_reports_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `lab_orders` (`id`);

--
-- Constraints for table `lab_requests`
--
ALTER TABLE `lab_requests`
  ADD CONSTRAINT `fk_labrequest_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  ADD CONSTRAINT `fk_labrequest_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_labrequest_patient` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_labrequest_testtype` FOREIGN KEY (`test_type_id`) REFERENCES `lab_test_types` (`id`);

--
-- Constraints for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD CONSTRAINT `fk_medhistory_creator` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `fk_medhistory_patient` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `medicine_orders`
--
ALTER TABLE `medicine_orders`
  ADD CONSTRAINT `medicine_orders_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `medicine_orders_ibfk_2` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `medicine_orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `medicine_orders` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `fk_prescription_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  ADD CONSTRAINT `fk_prescription_diagnosis` FOREIGN KEY (`diagnosis_id`) REFERENCES `diagnoses` (`id`),
  ADD CONSTRAINT `fk_prescription_pharmacy` FOREIGN KEY (`pharmacy_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `sample_collections`
--
ALTER TABLE `sample_collections`
  ADD CONSTRAINT `sample_collections_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `lab_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sample_collections_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `sample_collections_ibfk_3` FOREIGN KEY (`collector_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `test_batches`
--
ALTER TABLE `test_batches`
  ADD CONSTRAINT `test_batches_ibfk_1` FOREIGN KEY (`technician_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
