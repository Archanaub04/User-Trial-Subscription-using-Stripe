-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2024 at 09:55 AM
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
-- Database: `stripe-subscription`
--

-- --------------------------------------------------------

--
-- Table structure for table `card_details`
--

CREATE TABLE `card_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `card_id` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `card_no` varchar(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `month` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `card_details`
--

INSERT INTO `card_details` (`id`, `user_id`, `customer_id`, `card_id`, `name`, `card_no`, `brand`, `month`, `year`, `created_at`, `updated_at`) VALUES
(26, 1, 'cus_REVi6Nf2Dr4uuF', 'card_1QPPMHKpYwDrX1m89yTpnyni', NULL, '1111', 'Visa', '11', 2026, '2024-11-26 08:33:51', '2024-11-26 08:33:51'),
(27, 3, 'cus_RHzlSlMZJO2ogb', 'card_1QR8tyKpYwDrX1m8iobE4PPB', NULL, '1111', 'Visa', '11', 2034, '2024-12-01 03:23:46', '2024-12-01 03:23:46'),
(28, 3, 'cus_RHzlSlMZJO2ogb', 'card_1QR8nBKpYwDrX1m8dCXehZML', NULL, '4242', 'Visa', '4', 2026, '2024-12-01 03:16:43', '2024-12-01 03:16:43');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2024_09_29_052514_create_subscription_plans_table', 1),
(6, '2024_09_29_064605_create_card_details_table', 1),
(8, '2024_10_27_070955_modify_name_nullable_in_card_details_table', 2),
(9, '2024_09_29_070143_create_subscription_details_table', 3),
(10, '2024_11_01_144750_create_pending_fees_table', 4),
(11, '2024_11_04_151117_add_stripe_customer_id_to_users_table', 5),
(12, '2024_11_10_144257_change_plan_period_columns_to_datetime_in_subscription_details', 6);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending_fees`
--

CREATE TABLE `pending_fees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `charge_id` text NOT NULL,
  `customer_id` varchar(255) NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pending_fees`
--

INSERT INTO `pending_fees` (`id`, `user_id`, `charge_id`, `customer_id`, `amount`, `created_at`, `updated_at`) VALUES
(12, 3, 'ch_3QPPjFKpYwDrX1m81PKUFnIZ', 'cus_RHzer5SG1l3etY', 2.00, '2024-11-26 08:57:22', '2024-11-26 08:57:22'),
(13, 3, 'ch_3QPPvvKpYwDrX1m815IpT7p7', 'cus_RHzlSlMZJO2ogb', 2.00, '2024-11-26 09:10:28', '2024-11-26 09:10:28'),
(14, 3, 'ch_3QPQRJKpYwDrX1m80QNUQ1ky', 'cus_RHzlSlMZJO2ogb', 2.00, '2024-11-26 09:42:54', '2024-11-26 09:42:54'),
(15, 3, 'ch_3QPQcRKpYwDrX1m81NYbxQeO', 'cus_RHzlSlMZJO2ogb', 2.00, '2024-11-26 09:54:24', '2024-11-26 09:54:24'),
(16, 3, 'ch_3QPQfxKpYwDrX1m80k0jhd3d', 'cus_RHzlSlMZJO2ogb', 17.00, '2024-11-26 09:58:01', '2024-11-26 09:58:01'),
(17, 3, 'ch_3QR8nGKpYwDrX1m81KWJPZMZ', 'cus_RHzlSlMZJO2ogb', 12.00, '2024-12-01 03:16:41', '2024-12-01 03:16:41'),
(18, 3, 'ch_3QR8u3KpYwDrX1m80kXqXl4O', 'cus_RHzlSlMZJO2ogb', 12.00, '2024-12-01 03:23:42', '2024-12-01 03:23:42');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_details`
--

CREATE TABLE `subscription_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `stripe_subscription_schedule_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) NOT NULL,
  `subscription_plan_price_id` varchar(255) NOT NULL,
  `plan_amount` decimal(10,2) NOT NULL,
  `plan_amount_currency` varchar(255) NOT NULL,
  `plan_interval` varchar(255) NOT NULL,
  `plan_interval_count` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `plan_period_start` timestamp NULL DEFAULT NULL,
  `plan_period_end` datetime DEFAULT NULL,
  `trial_end` bigint(20) DEFAULT NULL,
  `status` enum('active','cancelled') NOT NULL,
  `cancel` int(11) NOT NULL DEFAULT 0 COMMENT '0->active, 1->cancelled',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_details`
--

INSERT INTO `subscription_details` (`id`, `user_id`, `stripe_subscription_id`, `stripe_subscription_schedule_id`, `stripe_customer_id`, `subscription_plan_price_id`, `plan_amount`, `plan_amount_currency`, `plan_interval`, `plan_interval_count`, `created`, `plan_period_start`, `plan_period_end`, `trial_end`, `status`, `cancel`, `cancelled_at`, `created_at`, `updated_at`) VALUES
(53, 3, NULL, '', 'cus_RHzlSlMZJO2ogb', 'price_1QC0MlKpYwDrX1m85hYOQCXi', 12.00, 'usd', 'month', 1, '2024-12-01 08:36:51', '2024-12-01 03:05:58', '2024-12-06 23:59:59', 1733529599, 'cancelled', 1, '2024-12-01 03:06:51', '2024-12-01 03:05:58', '2024-12-01 03:06:51'),
(56, 3, 'sub_1QR8u5KpYwDrX1m8Sh1gXubb', '', 'cus_RHzlSlMZJO2ogb', 'price_1QC0MlKpYwDrX1m85hYOQCXi', 12.00, 'usd', 'month', 1, '2024-12-01 08:54:47', '2024-12-31 18:30:00', '2025-01-31 23:59:59', NULL, 'cancelled', 1, '2024-12-01 03:24:47', '2024-12-01 03:23:46', '2024-12-01 03:24:47');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `stripe_price_id` varchar(255) DEFAULT NULL,
  `trial_days` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` int(11) NOT NULL COMMENT '0->Monthly, 1->Yearly, 2->Lifetime',
  `enabled` int(11) NOT NULL COMMENT '0->disabled, 1->enabled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `stripe_price_id`, `trial_days`, `amount`, `type`, `enabled`, `created_at`, `updated_at`) VALUES
(1, 'Monthly', 'price_1QC0MlKpYwDrX1m85hYOQCXi', 5, 12.00, 0, 1, '2024-09-29 08:49:19', '2024-09-29 08:49:19'),
(2, 'Yearly', 'price_1QC0NFKpYwDrX1m8kGnZdCVR', 5, 100.00, 1, 1, '2024-09-29 08:49:19', '2024-09-29 08:49:19'),
(3, 'LifeTime', 'price_1QC0NfKpYwDrX1m8so2hfJvY', 5, 400.00, 2, 1, '2024-09-29 08:49:19', '2024-09-29 08:49:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_subscribed` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `is_subscribed`, `remember_token`, `created_at`, `updated_at`, `stripe_customer_id`) VALUES
(3, 'Archana', 'archana@gmail.com', NULL, '$2y$10$U7ZRijcMPel8sDdrkjGZtelDgqipJM5BDkrwkcjQjaCa/heQfsDFu', 0, NULL, '2024-11-26 08:52:26', '2024-12-01 03:24:47', 'cus_RHzlSlMZJO2ogb');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `card_details`
--
ALTER TABLE `card_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pending_fees`
--
ALTER TABLE `pending_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `subscription_details`
--
ALTER TABLE `subscription_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `card_details`
--
ALTER TABLE `card_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pending_fees`
--
ALTER TABLE `pending_fees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_details`
--
ALTER TABLE `subscription_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
