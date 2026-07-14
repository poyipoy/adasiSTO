-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 13, 2026 at 03:19 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adasi_sto`
--

-- --------------------------------------------------------

--
-- Table structure for table `barcode_requests`
--

CREATE TABLE `barcode_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `sto_code_id` bigint UNSIGNED DEFAULT NULL,
  `plant_id` bigint UNSIGNED NOT NULL,
  `location_id` bigint UNSIGNED NOT NULL,
  `material_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shape_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shape_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thickness` int UNSIGNED DEFAULT NULL,
  `width` int UNSIGNED DEFAULT NULL,
  `diameter` int UNSIGNED DEFAULT NULL,
  `length` int UNSIGNED DEFAULT NULL,
  `lot_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int UNSIGNED DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `generated_barcode_material` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reviewed_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barcode_requests`
--
ALTER TABLE `barcode_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barcode_requests_sto_code_id_foreign` (`sto_code_id`),
  ADD KEY `barcode_requests_reviewed_by_user_id_foreign` (`reviewed_by_user_id`),
  ADD KEY `barcode_requests_user_id_index` (`user_id`),
  ADD KEY `barcode_requests_plant_id_index` (`plant_id`),
  ADD KEY `barcode_requests_location_id_index` (`location_id`),
  ADD KEY `barcode_requests_status_index` (`status`),
  ADD KEY `barcode_requests_material_code_index` (`material_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barcode_requests`
--
ALTER TABLE `barcode_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barcode_requests`
--
ALTER TABLE `barcode_requests`
  ADD CONSTRAINT `barcode_requests_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `barcode_requests_plant_id_foreign` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `barcode_requests_reviewed_by_user_id_foreign` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `barcode_requests_sto_code_id_foreign` FOREIGN KEY (`sto_code_id`) REFERENCES `sto_codes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `barcode_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
