-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 30, 2026 at 01:15 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `alnisr2`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_dealer_sections`
--

CREATE TABLE `about_dealer_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `dealer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_hero_sections`
--

CREATE TABLE `about_hero_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heading` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subheading` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_mission_sections`
--

CREATE TABLE `about_mission_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_product_sections`
--

CREATE TABLE `about_product_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_timeline_sections`
--

CREATE TABLE `about_timeline_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_who_we_are_sections`
--

CREATE TABLE `about_who_we_are_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activation_reviews`
--

CREATE TABLE `activation_reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `review_notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `flagged_reason` text COLLATE utf8mb4_unicode_ci,
  `agent_id` bigint UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `first_response_due` timestamp NULL DEFAULT NULL,
  `decision_due` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` bigint UNSIGNED NOT NULL,
  `deal_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` datetime NOT NULL,
  `assigned_to` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint UNSIGNED NOT NULL,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `addon_settings`
--

CREATE TABLE `addon_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_values` longtext COLLATE utf8mb4_unicode_ci,
  `test_values` longtext COLLATE utf8mb4_unicode_ci,
  `settings_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'live',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addon_settings`
--

INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`) VALUES
('070c6bbd-d777-11ed-96f4-0c7a158e4468', 'sms_com_eg', '{\"gateway\":\"sms_com_eg\",\"mode\":\"live\",\"status\":0,\"username\":\"\",\"password\":\"\",\"sender\":\"\",\"language\":\"2\",\"otp_template\":\"\"}', '{\"gateway\":\"sms_com_eg\",\"mode\":\"live\",\"status\":0,\"username\":\"\",\"password\":\"\",\"sender\":\"\",\"language\":\"2\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('070c6bbd-d777-11ed-96f4-0c7a158e4469', 'twilio', '{\"gateway\":\"twilio\",\"mode\":\"live\",\"status\":\"0\",\"sid\":\"data\",\"messaging_service_sid\":\"data\",\"token\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', '{\"gateway\":\"twilio\",\"mode\":\"live\",\"status\":\"0\",\"sid\":\"data\",\"messaging_service_sid\":\"data\",\"token\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', 'sms_config', 'live', 0, NULL, '2023-08-12 07:01:29', NULL),
('0cb5edd0-43e6-4109-8f67-28276fda4f5d', 'bosta', '{\"gateway\":\"bosta\",\"mode\":\"live\",\"status\":1,\"api_key\":\"dc5950ecd9d28bd81fe959d5433e68bcd80dcf3f48a1f1b8500471f10b2d20c1\",\"base_url\":\"https:\\/\\/app.bosta.co\\/api\\/v2\"}', '{\"gateway\":\"bosta\",\"mode\":\"live\",\"status\":1,\"api_key\":\"dc5950ecd9d28bd81fe959d5433e68bcd80dcf3f48a1f1b8500471f10b2d20c1\",\"base_url\":\"https:\\/\\/app.bosta.co\\/api\\/v2\"}', 'shipping_config', 'live', 1, '2026-03-05 23:23:36', '2026-03-05 23:23:59', NULL),
('18210f2b-d776-11ed-96f4-0c7a158e4469', 'nexmo', '{\"gateway\":\"nexmo\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"\",\"api_secret\":\"\",\"token\":\"\",\"from\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"nexmo\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"\",\"api_secret\":\"\",\"token\":\"\",\"from\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, '2023-04-10 02:14:44', NULL),
('4593b25c-d6a1-11ed-962c-0c7a158e4469', 'paytabs', '{\"gateway\":\"paytabs\",\"mode\":\"live\",\"status\":0,\"profile_id\":\"\",\"server_key\":\"\",\"base_url\":\"https:\\/\\/secure-egypt.paytabs.com\\/\"}', '{\"gateway\":\"paytabs\",\"mode\":\"live\",\"status\":0,\"profile_id\":\"\",\"server_key\":\"\",\"base_url\":\"https:\\/\\/secure-egypt.paytabs.com\\/\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:34:51', '{\"gateway_title\":\"Paytabs\",\"gateway_image\":null}'),
('998ccc62-d6a0-11ed-962c-0c7a158e4469', 'stripe', '{\"gateway\":\"stripe\",\"mode\":\"test\",\"status\":0,\"api_key\":\"tgggfffff\",\"published_key\":\"sssssss\"}', '{\"gateway\":\"stripe\",\"mode\":\"test\",\"status\":0,\"api_key\":\"tgggfffff\",\"published_key\":\"sssssss\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:20:45', '{\"gateway_title\":\"Stripe\",\"gateway_image\":\"2025-12-11-693aab4250028.png\"}'),
('b6c33c87-d8e9-11ed-8249-0c7a158e4469', 'global_sms', '{\"gateway\":\"global_sms\",\"mode\":\"live\",\"status\":0,\"user_name\":\"\",\"password\":\"\",\"from\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"global_sms\",\"mode\":\"live\",\"status\":0,\"user_name\":\"\",\"password\":\"\",\"from\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('b8992bd4-d6a0-11ed-962c-0c7a158e4469', 'paymob_accept', '{\"gateway\":\"paymob_accept\",\"mode\":\"live\",\"status\":\"0\",\"callback_url\":null,\"api_key\":\"\",\"iframe_id\":\"\",\"integration_id\":\"\",\"hmac\":\"\"}', '{\"gateway\":\"paymob_accept\",\"mode\":\"live\",\"status\":\"0\",\"callback_url\":null,\"api_key\":\"\",\"iframe_id\":\"\",\"integration_id\":\"\",\"hmac\":\"\"}', 'payment_config', 'test', 0, NULL, NULL, '{\"gateway_title\":\"Paymob accept\",\"gateway_image\":null}'),
('d822f1a5-c864-11ed-ac7a-0c7a158e4469', 'paystack', '{\"gateway\":\"paystack\",\"mode\":\"live\",\"status\":\"0\",\"callback_url\":\"https:\\/\\/api.paystack.co\",\"public_key\":null,\"secret_key\":null,\"merchant_email\":null}', '{\"gateway\":\"paystack\",\"mode\":\"live\",\"status\":\"0\",\"callback_url\":\"https:\\/\\/api.paystack.co\",\"public_key\":null,\"secret_key\":null,\"merchant_email\":null}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:20:45', '{\"gateway_title\":\"Paystack\",\"gateway_image\":null}');

-- --------------------------------------------------------

--
-- Table structure for table `add_fund_bonus_categories`
--

CREATE TABLE `add_fund_bonus_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `bonus_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bonus_amount` double(14,2) NOT NULL DEFAULT '0.00',
  `min_add_money_amount` double(14,2) NOT NULL DEFAULT '0.00',
  `max_bonus_amount` double(14,2) NOT NULL DEFAULT '0.00',
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_role_id` bigint NOT NULL DEFAULT '2',
  `branch_id` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'if user is branch manager',
  `department_id` int DEFAULT '0',
  `is_supervisor` tinyint(1) NOT NULL DEFAULT '0',
  `image` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def.png',
  `identify_image` text COLLATE utf8mb4_unicode_ci,
  `identify_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identify_number` int DEFAULT NULL,
  `email` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `phone`, `admin_role_id`, `branch_id`, `department_id`, `is_supervisor`, `image`, `identify_image`, `identify_type`, `identify_number`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `status`) VALUES
(1, 'admin', '+20111222111', 1, '', 0, 0, 'def.png', NULL, NULL, NULL, 'admin@admin.com', NULL, '$2y$10$83QgJ5719vKxS4BFq.BOBOA6XHofmTTl3uUWf0icu4He/PoK8zFX6', 'q9GKBp8bFIsdkM4CYSjqkvAc4NfHmZ7YkqOLdEK50aZkSeb1xQ4dSYwHFaDE', '2025-01-02 16:54:23', '2025-01-02 16:54:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `notification_for` int NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `customer_id` bigint DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_access` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_wallets`
--

CREATE TABLE `admin_wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint DEFAULT NULL,
  `inhouse_earning` double NOT NULL DEFAULT '0',
  `withdrawn` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `commission_earned` double(8,2) NOT NULL DEFAULT '0.00',
  `delivery_charge_earned` double(8,2) NOT NULL DEFAULT '0.00',
  `pending_amount` double(8,2) NOT NULL DEFAULT '0.00',
  `total_tax_collected` double(8,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_wallet_histories`
--

CREATE TABLE `admin_wallet_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint DEFAULT NULL,
  `amount` double NOT NULL DEFAULT '0',
  `order_id` bigint DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `payment` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `analytic_scripts`
--

CREATE TABLE `analytic_scripts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `script_id` text COLLATE utf8mb4_unicode_ci,
  `script` longtext COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

CREATE TABLE `areas` (
  `id` bigint NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `attachable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachable_id` bigint UNSIGNED NOT NULL,
  `file_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `storage_disk` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'public',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attributes`
--

CREATE TABLE `attributes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audits`
--

CREATE TABLE `audits` (
  `id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint UNSIGNED NOT NULL,
  `old_values` text COLLATE utf8mb4_unicode_ci,
  `new_values` text COLLATE utf8mb4_unicode_ci,
  `url` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(1023) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint UNSIGNED NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `published` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` bigint DEFAULT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing_addresses`
--

CREATE TABLE `billing_addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `contact_person_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blacklists`
--

CREATE TABLE `blacklists` (
  `id` bigint UNSIGNED NOT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `blacklisted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` bigint UNSIGNED NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `readable_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `writer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_storage_type` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `draft_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `draft_image_storage_type` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `publish_date` datetime NOT NULL DEFAULT '2025-02-13 14:40:55',
  `is_published` tinyint NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '0',
  `is_draft` tinyint NOT NULL DEFAULT '0',
  `draft_data` text COLLATE utf8mb4_unicode_ci,
  `click_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `click_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_seos`
--

CREATE TABLE `blog_seos` (
  `id` bigint UNSIGNED NOT NULL,
  `blog_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `index` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_follow` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_image_index` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_archive` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_snippet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_snippet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_snippet_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_video_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_video_preview_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_image_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_image_preview_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_translations`
--

CREATE TABLE `blog_translations` (
  `id` bigint UNSIGNED NOT NULL,
  `translation_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translation_id` bigint UNSIGNED NOT NULL,
  `locale` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `is_draft` tinyint DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint UNSIGNED NOT NULL,
  `vendor_id` int NOT NULL DEFAULT '1',
  `branch_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch_country` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch_state` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `branch_address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch_zipcode` int DEFAULT NULL,
  `branch_hours_from` time DEFAULT NULL,
  `branch_hours_to` time DEFAULT NULL,
  `sun_branch_hours_from` time DEFAULT NULL,
  `sun_branch_hours_to` time DEFAULT NULL,
  `mon_branch_hours_from` time DEFAULT NULL,
  `mon_branch_hours_to` time DEFAULT NULL,
  `tue_branch_hours_from` time DEFAULT NULL,
  `tue_branch_hours_to` time DEFAULT NULL,
  `wed_branch_hours_from` time DEFAULT NULL,
  `wed_branch_hours_to` time DEFAULT NULL,
  `thu_branch_hours_from` time DEFAULT NULL,
  `thu_branch_hours_to` time DEFAULT NULL,
  `fri_branch_hours_from` time DEFAULT NULL,
  `fri_branch_hours_to` time DEFAULT NULL,
  `sat_branch_hours_from` time DEFAULT NULL,
  `sat_branch_hours_to` time DEFAULT NULL,
  `shipping_method_city` int DEFAULT '0',
  `shipping_methods_area` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `delivery_restriction` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch_latitude` double NOT NULL DEFAULT '0',
  `branch_longitude` double NOT NULL DEFAULT '0',
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `manager_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Refers to admins.id for branch manager',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `vendor_id`, `branch_name`, `phone`, `email`, `branch_country`, `branch_state`, `branch_address`, `branch_zipcode`, `branch_hours_from`, `branch_hours_to`, `sun_branch_hours_from`, `sun_branch_hours_to`, `mon_branch_hours_from`, `mon_branch_hours_to`, `tue_branch_hours_from`, `tue_branch_hours_to`, `wed_branch_hours_from`, `wed_branch_hours_to`, `thu_branch_hours_from`, `thu_branch_hours_to`, `fri_branch_hours_from`, `fri_branch_hours_to`, `sat_branch_hours_from`, `sat_branch_hours_to`, `shipping_method_city`, `shipping_methods_area`, `delivery_restriction`, `branch_latitude`, `branch_longitude`, `status`, `created_at`, `updated_at`, `manager_id`, `deleted_at`) VALUES
(1, 1, 'System', '987654321', 'bg@gmail.com', 'EG', '', 'Egypt', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 31.17250161021052, 29.88293457908464, 'active', '2025-01-21 10:28:23', '2025-04-18 06:30:01', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branch_delivery_restrictions`
--

CREATE TABLE `branch_delivery_restrictions` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `delivery_area_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch_shipping_method_areas`
--

CREATE TABLE `branch_shipping_method_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `shipping_method_area_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def.png',
  `image_storage_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `image_alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business_pages`
--

CREATE TABLE `business_pages` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `default_status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_pages`
--

INSERT INTO `business_pages` (`id`, `title`, `slug`, `description`, `status`, `default_status`, `created_at`, `updated_at`) VALUES
(1, 'About Us', 'about-us', '<p data-path-to-node=\"3\"><b data-path-to-node=\"3\" data-index-in-node=\"0\">Heritage of Power and Reliability</b>\nEstablished as a cornerstone of the Egyptian automotive industry, <b data-path-to-node=\"3\" data-index-in-node=\"100\">Elnisr Batteries</b> has dedicated decades to manufacturing high-performance car batteries that power millions of vehicles across the nation. As a proud local manufacturer, we combine deep-rooted technical expertise with modern innovation to produce batteries specifically engineered to withstand the demanding climate and driving conditions of Egypt. Our commitment to excellence ensures that every battery leaving our factory meets the highest international standards of durability and starting power.</p><p data-path-to-node=\"4\"><b data-path-to-node=\"4\" data-index-in-node=\"0\">Driving the Future of Energy</b>\nAt Elnisr, we are more than just a manufacturer; we are your trusted partner on every journey. Through our integrated e-commerce platform, we aim to bridge the gap between traditional industrial quality and modern digital convenience, allowing customers to access premium energy solutions with ease. By investing in sustainable technologies and rigorous quality control, we continue to lead the Egyptian market, ensuring that the \"Eagle\" (Elnisr) remains the symbol of trust, longevity, and unstoppable energy for every car owner.</p><p data-path-to-node=\"4\"><br></p><p data-path-to-node=\"4\"><br></p>', 1, 1, '2025-05-11 07:50:11', '2026-03-20 08:16:36'),
(2, 'Terms And Conditions', 'terms-and-conditions', '<h1 data-start=\"411\" data-end=\"435\">Terms and Conditions</h1>\n<p data-start=\"436\" data-end=\"472\"><strong data-start=\"436\" data-end=\"472\">SalesCentral.DynamicLogic.online</strong></p>\n<p data-start=\"474\" data-end=\"509\"><strong data-start=\"474\" data-end=\"493\">Effective Date:</strong> January 8, 2026</p>\n<p data-start=\"511\" data-end=\"784\">These Terms and Conditions (“Terms”) govern your access to and use of SalesCentral.DynamicLogic.online (“Website,” “Service,” “we,” “us,” or “our”). By accessing or using this Website, you agree to be bound by these Terms. If you do not agree, you must not use the Service.</p>\n<hr data-start=\"786\" data-end=\"789\">\n<h2 data-start=\"791\" data-end=\"833\">1. Eligibility and Account Registration</h2>\n<p data-start=\"835\" data-end=\"922\">To use certain features of the Website, you must create an account. You represent that:</p>\n<ul data-start=\"923\" data-end=\"1093\"><li data-start=\"923\" data-end=\"954\">\n<p data-start=\"925\" data-end=\"954\">You are at least 18 years old</p>\n</li><li data-start=\"955\" data-end=\"1009\">\n<p data-start=\"957\" data-end=\"1009\">The information you provide is accurate and complete</p>\n</li><li data-start=\"1010\" data-end=\"1093\">\n<p data-start=\"1012\" data-end=\"1093\">You are responsible for maintaining the confidentiality of your login credentials</p>\n</li></ul>\n<p data-start=\"1095\" data-end=\"1163\">You are responsible for all activity that occurs under your account.</p>\n<hr data-start=\"1165\" data-end=\"1168\">\n<h2 data-start=\"1170\" data-end=\"1194\">2. Use of the Website</h2>\n<p data-start=\"1196\" data-end=\"1213\">You agree not to:</p>\n<ul data-start=\"1214\" data-end=\"1433\"><li data-start=\"1214\" data-end=\"1269\">\n<p data-start=\"1216\" data-end=\"1269\">Use the Website for unlawful or fraudulent purposes</p>\n</li><li data-start=\"1270\" data-end=\"1328\">\n<p data-start=\"1272\" data-end=\"1328\">Attempt to gain unauthorized access to systems or data</p>\n</li><li data-start=\"1329\" data-end=\"1388\">\n<p data-start=\"1331\" data-end=\"1388\">Interfere with the security or operation of the Website</p>\n</li><li data-start=\"1389\" data-end=\"1433\">\n<p data-start=\"1391\" data-end=\"1433\">Upload malicious code or harmful content</p>\n</li></ul>\n<p data-start=\"1435\" data-end=\"1514\">We reserve the right to suspend or terminate accounts that violate these Terms.</p>\n<hr data-start=\"1516\" data-end=\"1519\">\n<h2 data-start=\"1521\" data-end=\"1557\">3. Products, Orders, and Payments</h2>\n<ul data-start=\"1559\" data-end=\"1788\"><li data-start=\"1559\" data-end=\"1634\">\n<p data-start=\"1561\" data-end=\"1634\">Product descriptions and pricing are provided as accurately as possible</p>\n</li><li data-start=\"1635\" data-end=\"1677\">\n<p data-start=\"1637\" data-end=\"1677\">Prices may change without prior notice</p>\n</li><li data-start=\"1678\" data-end=\"1733\">\n<p data-start=\"1680\" data-end=\"1733\">Orders are subject to availability and confirmation</p>\n</li><li data-start=\"1734\" data-end=\"1788\">\n<p data-start=\"1736\" data-end=\"1788\">Payments must be completed before order processing</p>\n</li></ul>\n<p data-start=\"1790\" data-end=\"1913\">We reserve the right to refuse or cancel orders at our discretion, including in cases of suspected fraud or pricing errors.</p>\n<hr data-start=\"1915\" data-end=\"1918\">\n<h2 data-start=\"1920\" data-end=\"1947\">4. Shipping and Delivery</h2>\n<p data-start=\"1949\" data-end=\"2097\">Delivery times are estimates and not guaranteed. We are not responsible for delays caused by carriers, customs, or circumstances beyond our control.</p>\n<hr data-start=\"2099\" data-end=\"2102\">\n<h2 data-start=\"2104\" data-end=\"2129\">5. Returns and Refunds</h2>\n<p data-start=\"2131\" data-end=\"2315\">Return and refund eligibility is determined according to the specific product and applicable consumer protection laws. Details will be provided at the time of purchase or upon request.</p>\n<hr data-start=\"2317\" data-end=\"2320\">\n<h2 data-start=\"2322\" data-end=\"2360\">6. Account Termination and Deletion</h2>\n<p data-start=\"2362\" data-end=\"2428\">Users may delete their accounts at any time through the dashboard:</p>\n<p data-start=\"2430\" data-end=\"2475\"><strong data-start=\"2430\" data-end=\"2475\">Dashboard → Profile Info → Delete account</strong></p>\n<p data-start=\"2477\" data-end=\"2499\">Upon account deletion:</p>\n<ul data-start=\"2500\" data-end=\"2667\"><li data-start=\"2500\" data-end=\"2546\">\n<p data-start=\"2502\" data-end=\"2546\">Access to the account is permanently revoked</p>\n</li><li data-start=\"2547\" data-end=\"2611\">\n<p data-start=\"2549\" data-end=\"2611\">Personal data is deleted in accordance with our Privacy Policy</p>\n</li><li data-start=\"2612\" data-end=\"2667\">\n<p data-start=\"2614\" data-end=\"2667\">Certain records may be retained where required by law</p>\n</li></ul>\n<p data-start=\"2669\" data-end=\"2753\">We reserve the right to suspend or terminate accounts for violations of these Terms.</p>\n<hr data-start=\"2755\" data-end=\"2758\">\n<h2 data-start=\"2760\" data-end=\"2787\">7. Intellectual Property</h2>\n<p data-start=\"2789\" data-end=\"2980\">All content on the Website, including text, graphics, logos, and software, is the property of SalesCentral.DynamicLogic.online or its licensors and is protected by intellectual property laws.</p>\n<p data-start=\"2982\" data-end=\"3076\">You may not copy, modify, distribute, or exploit any content without prior written permission.</p>\n<hr data-start=\"3078\" data-end=\"3081\">\n<h2 data-start=\"3083\" data-end=\"3119\">8. Third-Party Services and Links</h2>\n<p data-start=\"3121\" data-end=\"3275\">The Website may contain links to third-party websites or services. We are not responsible for the content, policies, or practices of third-party services.</p>\n<p data-start=\"3277\" data-end=\"3325\">Use of third-party services is at your own risk.</p>\n<hr data-start=\"3327\" data-end=\"3330\">\n<h2 data-start=\"3332\" data-end=\"3362\">9. Disclaimer of Warranties</h2>\n<p data-start=\"3364\" data-end=\"3496\">The Website and services are provided <strong data-start=\"3402\" data-end=\"3413\">“as is”</strong> and <strong data-start=\"3418\" data-end=\"3436\">“as available”</strong> without warranties of any kind, whether express or implied.</p>\n<p data-start=\"3498\" data-end=\"3556\">We do not guarantee uninterrupted or error-free operation.</p>\n<hr data-start=\"3558\" data-end=\"3561\">\n<h2 data-start=\"3563\" data-end=\"3593\">10. Limitation of Liability</h2>\n<p data-start=\"3595\" data-end=\"3692\">To the maximum extent permitted by law, SalesCentral.DynamicLogic.online shall not be liable for:</p>\n<ul data-start=\"3693\" data-end=\"3869\"><li data-start=\"3693\" data-end=\"3743\">\n<p data-start=\"3695\" data-end=\"3743\">Indirect, incidental, or consequential damages</p>\n</li><li data-start=\"3744\" data-end=\"3796\">\n<p data-start=\"3746\" data-end=\"3796\">Loss of data, profits, or business opportunities</p>\n</li><li data-start=\"3797\" data-end=\"3869\">\n<p data-start=\"3799\" data-end=\"3869\">Unauthorized access to user data beyond reasonable security measures</p>\n</li></ul>\n<hr data-start=\"3871\" data-end=\"3874\">\n<h2 data-start=\"3876\" data-end=\"3898\">11. Indemnification</h2>\n<p data-start=\"3900\" data-end=\"4038\">You agree to indemnify and hold harmless SalesCentral.DynamicLogic.online from any claims, damages, liabilities, or expenses arising from:</p>\n<ul data-start=\"4039\" data-end=\"4160\"><li data-start=\"4039\" data-end=\"4066\">\n<p data-start=\"4041\" data-end=\"4066\">Your use of the Website</p>\n</li><li data-start=\"4067\" data-end=\"4100\">\n<p data-start=\"4069\" data-end=\"4100\">Your violation of these Terms</p>\n</li><li data-start=\"4101\" data-end=\"4160\">\n<p data-start=\"4103\" data-end=\"4160\">Your violation of applicable laws or third-party rights</p>\n</li></ul>\n<hr data-start=\"4162\" data-end=\"4165\">\n<h2 data-start=\"4167\" data-end=\"4187\">12. Governing Law</h2>\n<p data-start=\"4189\" data-end=\"4339\">These Terms are governed by and construed in accordance with the laws of the <strong data-start=\"4266\" data-end=\"4292\">Arab Republic of Egypt</strong>, without regard to conflict-of-law principles.</p>\n<hr data-start=\"4341\" data-end=\"4344\">\n<h2 data-start=\"4346\" data-end=\"4375\">13. Changes to These Terms</h2>\n<p data-start=\"4377\" data-end=\"4526\">We may update these Terms from time to time. Continued use of the Website after changes become effective constitutes acceptance of the updated Terms.</p>\n<hr data-start=\"4528\" data-end=\"4531\">\n<h2 data-start=\"4533\" data-end=\"4559\">14. Contact Information</h2>\n<p data-start=\"4561\" data-end=\"4628\">For questions regarding these Terms and Conditions, please contact:</p>\n<p data-start=\"4630\" data-end=\"4714\"><strong data-start=\"4630\" data-end=\"4640\">Email:</strong> <a data-start=\"4641\" data-end=\"4661\" class=\"decorated-link cursor-pointer\" rel=\"noopener\">enlisrmisr@gmail.com<span aria-hidden=\"true\" class=\"ms-0.5 inline-block align-middle leading-none\"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" aria-hidden=\"true\" data-rtl-flip=\"\" class=\"block h-[0.75em] w-[0.75em] stroke-current stroke-[0.75]\"><use fill=\"currentColor\"></use></svg></span></a><br data-start=\"4661\" data-end=\"4664\">\n<strong data-start=\"4664\" data-end=\"4676\">Address:</strong><br data-start=\"4676\" data-end=\"4679\">\n455 Horria Road<br data-start=\"4694\" data-end=\"4697\">\nAlexandria, Egypt</p>', 1, 1, '2025-05-11 07:50:11', '2026-03-19 13:16:55'),
(3, 'Privacy Policy', 'privacy-policy', '<hr data-start=\"410\" data-end=\"413\">\n<h1 data-start=\"415\" data-end=\"433\">Privacy Policy</h1>\n<p data-start=\"434\" data-end=\"470\"><strong data-start=\"434\" data-end=\"470\">elnisr.online</strong></p>\n<p data-start=\"472\" data-end=\"507\"><strong data-start=\"472\" data-end=\"491\">Effective Date:</strong> January 8, 2026</p>\n<p data-start=\"509\" data-end=\"748\">SalesCentral.DynamicLogic.online \n(“we,” “us,” or “our”) is committed to protecting your privacy. This \nPrivacy Policy explains how we collect, use, disclose, and safeguard \nyour personal data when you access our website and use our services.</p>\n<p data-start=\"750\" data-end=\"916\">By using this website, you agree to \nthe collection and use of information in accordance with this Privacy \nPolicy. If you do not agree, please do not use our services.</p>\n<hr data-start=\"918\" data-end=\"921\">\n<h2 data-start=\"923\" data-end=\"951\">1. Information We Collect</h2>\n<p data-start=\"953\" data-end=\"1068\">We collect information that you provide directly and information collected automatically when you use our services.</p>\n<h3 data-start=\"1070\" data-end=\"1102\">A. Personal Data You Provide</h3>\n<ul data-start=\"1103\" data-end=\"1304\"><li data-start=\"1103\" data-end=\"1116\">\n<p data-start=\"1105\" data-end=\"1116\">Full name</p>\n</li><li data-start=\"1117\" data-end=\"1134\">\n<p data-start=\"1119\" data-end=\"1134\">Email address</p>\n</li><li data-start=\"1135\" data-end=\"1151\">\n<p data-start=\"1137\" data-end=\"1151\">Phone number</p>\n</li><li data-start=\"1152\" data-end=\"1184\">\n<p data-start=\"1154\" data-end=\"1184\">Billing and shipping address</p>\n</li><li data-start=\"1185\" data-end=\"1214\">\n<p data-start=\"1187\" data-end=\"1214\">Account login credentials</p>\n</li><li data-start=\"1215\" data-end=\"1248\">\n<p data-start=\"1217\" data-end=\"1248\">Order and transaction details</p>\n</li><li data-start=\"1249\" data-end=\"1304\">\n<p data-start=\"1251\" data-end=\"1304\">Messages submitted through contact or support forms</p>\n</li></ul>\n<h3 data-start=\"1306\" data-end=\"1341\">B. Automatically Collected Data</h3>\n<ul data-start=\"1342\" data-end=\"1503\"><li data-start=\"1342\" data-end=\"1356\">\n<p data-start=\"1344\" data-end=\"1356\">IP address</p>\n</li><li data-start=\"1357\" data-end=\"1385\">\n<p data-start=\"1359\" data-end=\"1385\">Browser type and version</p>\n</li><li data-start=\"1386\" data-end=\"1422\">\n<p data-start=\"1388\" data-end=\"1422\">Device type and operating system</p>\n</li><li data-start=\"1423\" data-end=\"1457\">\n<p data-start=\"1425\" data-end=\"1457\">Pages visited and interactions</p>\n</li><li data-start=\"1458\" data-end=\"1503\">\n<p data-start=\"1460\" data-end=\"1503\">Cookies and similar tracking technologies</p>\n</li></ul>\n<p data-start=\"1505\" data-end=\"1636\">We do not intentionally collect \nsensitive personal data (such as race, religion, or health data) unless \nyou voluntarily provide it.</p>\n<hr data-start=\"1638\" data-end=\"1641\">\n<h2 data-start=\"1643\" data-end=\"1676\">2. How We Use Your Information</h2>\n<p data-start=\"1678\" data-end=\"1748\">We use personal data only for legitimate business purposes, including:</p>\n<ul data-start=\"1750\" data-end=\"2068\"><li data-start=\"1750\" data-end=\"1792\">\n<p data-start=\"1752\" data-end=\"1792\">Providing and maintaining our services</p>\n</li><li data-start=\"1793\" data-end=\"1831\">\n<p data-start=\"1795\" data-end=\"1831\">Processing orders and transactions</p>\n</li><li data-start=\"1832\" data-end=\"1858\">\n<p data-start=\"1834\" data-end=\"1858\">Managing user accounts</p>\n</li><li data-start=\"1859\" data-end=\"1907\">\n<p data-start=\"1861\" data-end=\"1907\">Responding to inquiries and support requests</p>\n</li><li data-start=\"1908\" data-end=\"1961\">\n<p data-start=\"1910\" data-end=\"1961\">Improving website performance and user experience</p>\n</li><li data-start=\"1962\" data-end=\"2022\">\n<p data-start=\"1964\" data-end=\"2022\">Sending administrative or service-related communications</p>\n</li><li data-start=\"2023\" data-end=\"2068\">\n<p data-start=\"2025\" data-end=\"2068\">Preventing fraud and securing our systems</p>\n</li></ul>\n<p data-start=\"2070\" data-end=\"2151\">We do not use personal data for purposes incompatible with those described above.</p>\n<hr data-start=\"2153\" data-end=\"2156\">\n<h2 data-start=\"2158\" data-end=\"2209\">3. Legal Basis for Processing (Where Applicable)</h2>\n<p data-start=\"2211\" data-end=\"2277\">Depending on your jurisdiction, we process personal data based on:</p>\n<ul data-start=\"2279\" data-end=\"2532\"><li data-start=\"2279\" data-end=\"2339\">\n<p data-start=\"2281\" data-end=\"2339\"><strong data-start=\"2281\" data-end=\"2305\">Contract performance</strong> – to deliver requested services</p>\n</li><li data-start=\"2340\" data-end=\"2415\">\n<p data-start=\"2342\" data-end=\"2415\"><strong data-start=\"2342\" data-end=\"2366\">Legitimate interests</strong> – to operate, improve, and secure our platform</p>\n</li><li data-start=\"2416\" data-end=\"2473\">\n<p data-start=\"2418\" data-end=\"2473\"><strong data-start=\"2418\" data-end=\"2429\">Consent</strong> – for marketing and non-essential cookies</p>\n</li><li data-start=\"2474\" data-end=\"2532\">\n<p data-start=\"2476\" data-end=\"2532\"><strong data-start=\"2476\" data-end=\"2497\">Legal obligations</strong> – to comply with applicable laws</p>\n</li></ul>\n<hr data-start=\"2534\" data-end=\"2537\">\n<h2 data-start=\"2539\" data-end=\"2578\">4. Cookies and Tracking Technologies</h2>\n<p data-start=\"2580\" data-end=\"2623\">We use cookies and similar technologies to:</p>\n<ul data-start=\"2624\" data-end=\"2725\"><li data-start=\"2624\" data-end=\"2658\">\n<p data-start=\"2626\" data-end=\"2658\">Enable core site functionality</p>\n</li><li data-start=\"2659\" data-end=\"2697\">\n<p data-start=\"2661\" data-end=\"2697\">Analyze traffic and usage patterns</p>\n</li><li data-start=\"2698\" data-end=\"2725\">\n<p data-start=\"2700\" data-end=\"2725\">Improve user experience</p>\n</li></ul>\n<p data-start=\"2727\" data-end=\"2840\">You can manage or disable cookies through your browser settings. Disabling cookies may affect site functionality.</p>\n<hr data-start=\"2842\" data-end=\"2845\">\n<h2 data-start=\"2847\" data-end=\"2875\">5. Sharing of Information</h2>\n<p data-start=\"2877\" data-end=\"2909\">We may share personal data with:</p>\n<ul data-start=\"2910\" data-end=\"3093\"><li data-start=\"2910\" data-end=\"2972\">\n<p data-start=\"2912\" data-end=\"2972\">Service providers (hosting, payment processing, analytics)</p>\n</li><li data-start=\"2973\" data-end=\"3029\">\n<p data-start=\"2975\" data-end=\"3029\">Legal or regulatory authorities when required by law</p>\n</li><li data-start=\"3030\" data-end=\"3093\">\n<p data-start=\"3032\" data-end=\"3093\">Business successors in case of merger, acquisition, or sale</p>\n</li></ul>\n<p data-start=\"3095\" data-end=\"3154\">We <strong data-start=\"3098\" data-end=\"3113\">do not sell</strong> personal data for monetary compensation.</p>\n<hr data-start=\"3156\" data-end=\"3159\">\n<h2 data-start=\"3161\" data-end=\"3195\">6. International Data Transfers</h2>\n<p data-start=\"3197\" data-end=\"3408\">Your personal data may be \ntransferred to and processed in countries outside your country of \nresidence. Where required, we implement appropriate safeguards to \nprotect your data in accordance with applicable laws.</p>\n<hr data-start=\"3410\" data-end=\"3413\">\n<h2 data-start=\"3415\" data-end=\"3440\">7. Your Privacy Rights</h2>\n<p data-start=\"3442\" data-end=\"3496\">Depending on your location, you may have the right to:</p>\n<ul data-start=\"3497\" data-end=\"3694\"><li data-start=\"3497\" data-end=\"3526\">\n<p data-start=\"3499\" data-end=\"3526\">Access your personal data</p>\n</li><li data-start=\"3527\" data-end=\"3561\">\n<p data-start=\"3529\" data-end=\"3561\">Correct inaccurate information</p>\n</li><li data-start=\"3562\" data-end=\"3595\">\n<p data-start=\"3564\" data-end=\"3595\">Request deletion of your data</p>\n</li><li data-start=\"3596\" data-end=\"3632\">\n<p data-start=\"3598\" data-end=\"3632\">Restrict or object to processing</p>\n</li><li data-start=\"3633\" data-end=\"3661\">\n<p data-start=\"3635\" data-end=\"3661\">Request data portability</p>\n</li><li data-start=\"3662\" data-end=\"3694\">\n<p data-start=\"3664\" data-end=\"3694\">Withdraw consent at any time</p>\n</li></ul>\n<p data-start=\"3696\" data-end=\"3758\">Requests can be made using the contact details provided below.</p>\n<hr data-start=\"3760\" data-end=\"3763\">\n<h2 data-start=\"3765\" data-end=\"3789\">8. Children’s Privacy</h2>\n<p data-start=\"3791\" data-end=\"3968\">Our services are not intended for \nchildren under the age of 16. We do not knowingly collect personal data \nfrom children. If such data is discovered, it will be deleted promptly.</p>\n<hr data-start=\"3970\" data-end=\"3973\">\n<h2 data-start=\"3975\" data-end=\"3994\">9. Data Security</h2>\n<p data-start=\"3996\" data-end=\"4150\">We implement reasonable \nadministrative, technical, and organizational measures to protect \npersonal data. However, no system can be guaranteed 100% secure.</p>\n<hr data-start=\"4152\" data-end=\"4155\">\n<h2 data-start=\"4157\" data-end=\"4178\">10. Data Retention</h2>\n<p data-start=\"4180\" data-end=\"4237\">We retain personal data only for as long as necessary to:</p>\n<ul data-start=\"4238\" data-end=\"4348\"><li data-start=\"4238\" data-end=\"4262\">\n<p data-start=\"4240\" data-end=\"4262\">Provide our services</p>\n</li><li data-start=\"4263\" data-end=\"4304\">\n<p data-start=\"4265\" data-end=\"4304\">Meet legal and accounting obligations</p>\n</li><li data-start=\"4305\" data-end=\"4348\">\n<p data-start=\"4307\" data-end=\"4348\">Resolve disputes and enforce agreements</p>\n</li></ul>\n<p data-start=\"4350\" data-end=\"4420\">When data is no longer required, it is securely deleted or anonymized.</p>\n<hr data-start=\"4422\" data-end=\"4425\">\n<h2 data-start=\"4427\" data-end=\"4464\">11. Account and User Data Deletion</h2>\n<p data-start=\"4466\" data-end=\"4594\">Users can delete their account and associated personal data directly through their account dashboard without contacting support.</p>\n<h3 data-start=\"4596\" data-end=\"4629\">Self-Service Account Deletion</h3>\n<p data-start=\"4630\" data-end=\"4671\">To delete your account and personal data:</p>\n<ol data-start=\"4673\" data-end=\"4845\"><li data-start=\"4673\" data-end=\"4738\">\n<p data-start=\"4676\" data-end=\"4738\">Log in to your account at <strong data-start=\"4702\" data-end=\"4738\">SalesCentral.DynamicLogic.online</strong></p>\n</li><li data-start=\"4739\" data-end=\"4782\">\n<p data-start=\"4742\" data-end=\"4782\">Navigate to <strong data-start=\"4754\" data-end=\"4782\">Dashboard → Profile Info</strong></p>\n</li><li data-start=\"4783\" data-end=\"4813\">\n<p data-start=\"4786\" data-end=\"4813\">Select <strong data-start=\"4793\" data-end=\"4813\">“Delete account”</strong></p>\n</li><li data-start=\"4814\" data-end=\"4845\">\n<p data-start=\"4817\" data-end=\"4845\">Confirm the deletion request</p>\n</li></ol>\n<p data-start=\"4847\" data-end=\"4911\">Once confirmed, the account deletion process begins immediately.</p>\n<h3 data-start=\"4913\" data-end=\"4930\">Data Affected</h3>\n<p data-start=\"4931\" data-end=\"4956\">Account deletion removes:</p>\n<ul data-start=\"4957\" data-end=\"5130\"><li data-start=\"4957\" data-end=\"5009\">\n<p data-start=\"4959\" data-end=\"5009\">Account profile data (name, email, phone number)</p>\n</li><li data-start=\"5010\" data-end=\"5045\">\n<p data-start=\"5012\" data-end=\"5045\">Saved addresses and preferences</p>\n</li><li data-start=\"5046\" data-end=\"5085\">\n<p data-start=\"5048\" data-end=\"5085\">Order history linked to the account</p>\n</li><li data-start=\"5086\" data-end=\"5130\">\n<p data-start=\"5088\" data-end=\"5130\">User identifiers and stored account data</p>\n</li></ul>\n<h3 data-start=\"5132\" data-end=\"5151\">Processing Time</h3>\n<p data-start=\"5152\" data-end=\"5298\">Account deletion is processed immediately.<br data-start=\"5194\" data-end=\"5197\">\nResidual data stored in encrypted backups may persist for up to <strong data-start=\"5261\" data-end=\"5272\">30 days</strong> before permanent removal.</p>\n<h3 data-start=\"5300\" data-end=\"5332\">Legal and Security Retention</h3>\n<p data-start=\"5333\" data-end=\"5502\">Certain records may be retained \nwhere required by law (e.g., tax, fraud prevention, or compliance \nobligations). Such data is isolated and not used for active processing.</p>\n<hr data-start=\"5504\" data-end=\"5507\">\n<h2 data-start=\"5509\" data-end=\"5546\">12. Changes to This Privacy Policy</h2>\n<p data-start=\"5548\" data-end=\"5686\">We may update this Privacy Policy \nfrom time to time. Any changes will be reflected by an updated \n“Effective Date” at the top of this page.</p>\n<hr data-start=\"5688\" data-end=\"5691\">\n<h2 data-start=\"5693\" data-end=\"5719\">13. Contact Information</h2>\n<p data-start=\"5721\" data-end=\"5832\">If you have questions about this Privacy Policy or wish to exercise your privacy rights, you may contact us at:</p>\n<p data-start=\"5834\" data-end=\"5918\"><strong data-start=\"5834\" data-end=\"5844\">Email:</strong> <a data-start=\"5845\" data-end=\"5865\" class=\"decorated-link cursor-pointer\" rel=\"noopener\">enlisrmisr@gmail.com<span aria-hidden=\"true\" class=\"ms-0.5 inline-block align-middle leading-none\"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" aria-hidden=\"true\" data-rtl-flip=\"\" class=\"block h-[0.75em] w-[0.75em] stroke-current stroke-[0.75]\"><use fill=\"currentColor\"></use></svg></span></a><br data-start=\"5865\" data-end=\"5868\">\n<strong data-start=\"5868\" data-end=\"5880\">Address:</strong><br data-start=\"5880\" data-end=\"5883\">\n455 Horria Road<br data-start=\"5898\" data-end=\"5901\">\nAlexandria, Egypt</p>', 1, 1, '2025-05-11 07:50:11', '2026-03-20 06:29:24'),
(4, 'Refund Policy', 'refund-policy', '<h1 data-path-to-node=\"1\">Refund Policy</h1><p data-path-to-node=\"2\"><b data-path-to-node=\"2\" data-index-in-node=\"0\">elnisr.online</b>\r\n<b data-path-to-node=\"2\" data-index-in-node=\"14\">Effective Date:</b> January 8, 2026</p><p data-path-to-node=\"3\">Thank you for choosing <b data-path-to-node=\"3\" data-index-in-node=\"23\">elnisr.online</b>. We value your satisfaction and aim to provide a fair and transparent refund process for all our customers.</p><hr data-path-to-node=\"4\"><h4 data-path-to-node=\"5\">1. Eligibility for Refunds</h4><p data-path-to-node=\"6\">To be eligible for a refund, the following conditions must be met:</p><ul data-path-to-node=\"7\"><li><p data-path-to-node=\"7,0,0\"><b data-path-to-node=\"7,0,0\" data-index-in-node=\"0\">Digital Services:</b> Requests must be submitted within <b data-path-to-node=\"7,0,0\" data-index-in-node=\"52\">[e.g., 14 days]</b> of the purchase date, provided that the service has not been fully utilized or downloaded.</p></li><li><p data-path-to-node=\"7,1,0\"><b data-path-to-node=\"7,1,0\" data-index-in-node=\"0\">Physical Products:</b> Items must be returned in their original packaging, unused, and in the same condition as received.</p></li><li><p data-path-to-node=\"7,2,0\"><b data-path-to-node=\"7,2,0\" data-index-in-node=\"0\">Proof of Purchase:</b> A valid receipt or order number must be provided.</p></li></ul><h4 data-path-to-node=\"8\">2. Non-Refundable Items</h4><p data-path-to-node=\"9\">Certain types of purchases are exempt from being refunded:</p><ul data-path-to-node=\"10\"><li><p data-path-to-node=\"10,0,0\">Services that have been fully completed or delivered.</p></li><li><p data-path-to-node=\"10,1,0\">Customized or personalized products made specifically to your specifications.</p></li><li><p data-path-to-node=\"10,2,0\">Downloadable software products once the license key has been revealed or activated.</p></li><li><p data-path-to-node=\"10,3,0\">Subscription fees after the initial trial period (if applicable).</p></li></ul><h4 data-path-to-node=\"11\">3. Refund Process</h4><ol start=\"1\" data-path-to-node=\"12\"><li><p data-path-to-node=\"12,0,0\"><b data-path-to-node=\"12,0,0\" data-index-in-node=\"0\">Request:</b> Email us at <b data-path-to-node=\"12,0,0\" data-index-in-node=\"21\">enlisrmisr@gmail.com</b> with your order number and the reason for the refund.</p></li><li><p data-path-to-node=\"12,1,0\"><b data-path-to-node=\"12,1,0\" data-index-in-node=\"0\">Review:</b> Our team will review your request within <b data-path-to-node=\"12,1,0\" data-index-in-node=\"49\">[e.g., 3-5 business days]</b>.</p></li><li><p data-path-to-node=\"12,2,0\"><b data-path-to-node=\"12,2,0\" data-index-in-node=\"0\">Approval:</b> If approved, your refund will be processed, and a credit will automatically be applied to your original method of payment.</p></li></ol><h4 data-path-to-node=\"13\">4. Late or Missing Refunds</h4><p data-path-to-node=\"14\">If you haven’t received a refund yet:</p><ul data-path-to-node=\"15\"><li><p data-path-to-node=\"15,0,0\">Check your bank account again.</p></li><li><p data-path-to-node=\"15,1,0\">Contact your credit card company; it may take some time before your refund is officially posted.</p></li><li><p data-path-to-node=\"15,2,0\">If you’ve done all of this and still have not received your refund, please contact us.</p></li></ul><h4 data-path-to-node=\"16\">5. Shipping Costs (For Physical Goods)</h4><p data-path-to-node=\"17\">You will be responsible for paying for your own shipping costs for returning your item. Shipping costs are non-refundable.</p>', 1, 1, '2025-05-11 07:50:11', '2026-03-20 10:23:20'),
(5, 'Return Policy', 'return-policy', '<h1 data-path-to-node=\"1\">Return Policy</h1><h5 class=\"mb-0 text-capitalize\" style=\"color: rgb(51, 66, 87);\"><p data-path-to-node=\"2\"><b data-path-to-node=\"2\" data-index-in-node=\"0\">elnisr.online</b>\r\n<b data-path-to-node=\"2\" data-index-in-node=\"14\">Effective Date:</b> January 8, 2026</p><p data-path-to-node=\"3\">At <b data-path-to-node=\"3\" data-index-in-node=\"3\">elnisr.online</b>, we want you to be completely satisfied with your purchase. If you are not happy with your physical product, we are here to help you with the return process.</p><hr data-path-to-node=\"4\"></h5><h4 data-path-to-node=\"5\">1. Return Window</h4><h5 class=\"mb-0 text-capitalize\" style=\"color: rgb(51, 66, 87);\"><p data-path-to-node=\"6\">You have <b data-path-to-node=\"6\" data-index-in-node=\"9\">[e.g., 30 days]</b> from the date you received your item to request a return. If this period has passed, we unfortunately cannot offer you a return or exchange.</p></h5><h4 data-path-to-node=\"7\">2. Condition of Items</h4><h5 class=\"mb-0 text-capitalize\" style=\"color: rgb(51, 66, 87);\"><p data-path-to-node=\"8\">To be eligible for a return, your item must meet the following criteria:</p><ul data-path-to-node=\"9\"><li><p data-path-to-node=\"9,0,0\">The item must be <b data-path-to-node=\"9,0,0\" data-index-in-node=\"17\">unused</b> and in the same condition that you received it.</p></li><li><p data-path-to-node=\"9,1,0\">It must be in the <b data-path-to-node=\"9,1,0\" data-index-in-node=\"18\">original packaging</b>.</p></li><li><p data-path-to-node=\"9,2,0\">All tags, manuals, and accessories must be included.</p></li><li><p data-path-to-node=\"9,3,0\">Any \"tamper-evident\" seals must be intact.</p></li></ul></h5><h4 data-path-to-node=\"10\">3. Non-Returnable Items</h4><h5 class=\"mb-0 text-capitalize\" style=\"color: rgb(51, 66, 87);\"><p data-path-to-node=\"11\">The following items cannot be returned:</p><ul data-path-to-node=\"12\"><li><p data-path-to-node=\"12,0,0\"><b data-path-to-node=\"12,0,0\" data-index-in-node=\"0\">Digital downloads</b> or software licenses.</p></li><li><p data-path-to-node=\"12,1,0\"><b data-path-to-node=\"12,1,0\" data-index-in-node=\"0\">Perishable goods</b> (if applicable).</p></li><li><p data-path-to-node=\"12,2,0\"><b data-path-to-node=\"12,2,0\" data-index-in-node=\"0\">Personal care</b> or hygiene products.</p></li><li><p data-path-to-node=\"12,3,0\"><b data-path-to-node=\"12,3,0\" data-index-in-node=\"0\">Custom-made</b> or personalized orders.</p></li></ul></h5><h4 data-path-to-node=\"13\">4. How to Start a Return</h4><h5 class=\"mb-0 text-capitalize\" style=\"color: rgb(51, 66, 87);\"><ol start=\"1\" data-path-to-node=\"14\"><li><p data-path-to-node=\"14,0,0\"><b data-path-to-node=\"14,0,0\" data-index-in-node=\"0\">Contact Support:</b> Send an email to <b data-path-to-node=\"14,0,0\" data-index-in-node=\"34\">enlisrmisr@gmail.com</b> with your subject line as \"Return Request - [Order Number]\".</p></li><li><p data-path-to-node=\"14,1,0\"><b data-path-to-node=\"14,1,0\" data-index-in-node=\"0\">Authorization:</b> Once we review your request, we will send you a <b data-path-to-node=\"14,1,0\" data-index-in-node=\"63\">Return Authorization</b> and instructions on where to send your package.</p></li><li><p data-path-to-node=\"14,2,0\"><b data-path-to-node=\"14,2,0\" data-index-in-node=\"0\">Shipping:</b> Please do not send your purchase back to the manufacturer. Use the address provided by our support team.</p></li></ol></h5><h4 data-path-to-node=\"15\">5. Exchanges</h4><h5 class=\"mb-0 text-capitalize\" style=\"color: rgb(51, 66, 87);\"><p data-path-to-node=\"16\">We only replace items if they are defective or damaged upon arrival. If you need to exchange it for the same item, please mention this in your email.</p></h5><h4 data-path-to-node=\"17\">6. Return Shipping</h4><h5 class=\"mb-0 text-capitalize\" style=\"color: rgb(51, 66, 87);\"><ul data-path-to-node=\"18\"><li><p data-path-to-node=\"18,0,0\">You will be responsible for paying for your own shipping costs for returning your item.</p></li><li><p data-path-to-node=\"18,1,0\">Shipping costs are non-refundable.</p></li><li><p data-path-to-node=\"18,2,0\">We recommend using a trackable shipping service or purchasing shipping insurance, as we cannot guarantee that we will receive your returned item.</p></li></ul></h5>', 1, 1, '2025-05-11 07:50:11', '2026-03-20 10:23:35'),
(6, 'Cancellation Policy', 'cancellation-policy', '<h1 data-path-to-node=\"1\">Cancellation Policy</h1><h1 data-path-to-node=\"1\"><p data-path-to-node=\"2\"><b data-path-to-node=\"2\" data-index-in-node=\"0\">elnisr.online</b>\r\n<b data-path-to-node=\"2\" data-index-in-node=\"14\">Effective Date:</b> January 8, 2026</p><p data-path-to-node=\"3\">At <b data-path-to-node=\"3\" data-index-in-node=\"3\">elnisr.online</b>, we understand that plans can change. Our Cancellation Policy is designed to be fair to both our clients and our service providers.</p><hr data-path-to-node=\"4\"></h1><h4 data-path-to-node=\"5\">1. Service Cancellations</h4><h1 data-path-to-node=\"1\"><ul data-path-to-node=\"6\"><li><p data-path-to-node=\"6,0,0\"><b data-path-to-node=\"6,0,0\" data-index-in-node=\"0\">Before Processing:</b> You may cancel any service order within <b data-path-to-node=\"6,0,0\" data-index-in-node=\"59\">[e.g., 24 hours]</b> of purchase for a full refund, provided work has not yet commenced.</p></li><li><p data-path-to-node=\"6,1,0\"><b data-path-to-node=\"6,1,0\" data-index-in-node=\"0\">Ongoing Projects:</b> For long-term projects or milestone-based services, cancellations made after work has started will be subject to a fee covering the work already completed.</p></li><li><p data-path-to-node=\"6,2,0\"><b data-path-to-node=\"6,2,0\" data-index-in-node=\"0\">Subscription Services:</b> You may cancel your subscription at any time. Your access will remain active until the end of your current billing cycle, and no further charges will be applied.</p></li></ul></h1><h4 data-path-to-node=\"7\">2. Order Cancellations (Physical Goods)</h4><h1 data-path-to-node=\"1\"><ul data-path-to-node=\"8\"><li><p data-path-to-node=\"8,0,0\"><b data-path-to-node=\"8,0,0\" data-index-in-node=\"0\">Before Shipping:</b> You can cancel your order for physical products at any time before the status changes to \"Shipped\" or \"Dispatched.\"</p></li><li><p data-path-to-node=\"8,1,0\"><b data-path-to-node=\"8,1,0\" data-index-in-node=\"0\">After Shipping:</b> Once an item has left our warehouse, the order cannot be canceled. In this case, please follow our <b data-path-to-node=\"8,1,0\" data-index-in-node=\"115\">Return Policy</b> once the item arrives.</p></li></ul></h1><h4 data-path-to-node=\"9\">3. How to Cancel</h4><h1 data-path-to-node=\"1\"><p data-path-to-node=\"10\">To request a cancellation, please use one of the following methods:</p><ol start=\"1\" data-path-to-node=\"11\"><li><p data-path-to-node=\"11,0,0\"><b data-path-to-node=\"11,0,0\" data-index-in-node=\"0\">Dashboard:</b> Log in to your account, go to \"My Orders,\" and select \"Cancel Order\" (if the option is available).</p></li><li><p data-path-to-node=\"11,1,0\"><b data-path-to-node=\"11,1,0\" data-index-in-node=\"0\">Email:</b> Send a request to <b data-path-to-node=\"11,1,0\" data-index-in-node=\"25\">enlisrmisr@gmail.com</b> with the subject: \"Cancellation Request - [Order Number]\".</p></li></ol></h1><h4 data-path-to-node=\"12\">4. Refund Timeline</h4><h1 data-path-to-node=\"1\"><p data-path-to-node=\"13\">Once a cancellation is confirmed:</p><ul data-path-to-node=\"14\"><li><p data-path-to-node=\"14,0,0\">Refunds are processed within <b data-path-to-node=\"14,0,0\" data-index-in-node=\"29\">[e.g., 5-7 business days]</b>.</p></li><li><p data-path-to-node=\"14,1,0\">The funds will be returned to the original payment method used during checkout.</p></li></ul></h1>', 1, 1, '2025-05-11 07:50:11', '2026-03-20 10:23:41'),
(7, 'Shipping Policy', 'shipping-policy', '<h3 data-path-to-node=\"0\">1. Shipping Policy (English)</h3><h1 data-path-to-node=\"1\">Shipping Policy</h1><p data-path-to-node=\"2\"><b data-path-to-node=\"2\" data-index-in-node=\"0\">elnisr.online</b> <b data-path-to-node=\"2\" data-index-in-node=\"14\">Effective Date:</b> January 8, 2026</p><p data-path-to-node=\"3\">Thank you for visiting and shopping at <b data-path-to-node=\"3\" data-index-in-node=\"39\">elnisr.online</b>. The following are the terms and conditions that constitute our Shipping Policy.</p><hr data-path-to-node=\"4\"><h4 data-path-to-node=\"5\">1. Shipment Processing Time</h4><p data-path-to-node=\"6\">All orders are processed within <b data-path-to-node=\"6\" data-index-in-node=\"32\">[e.g., 1–3 business days]</b>. Orders are not shipped or delivered on weekends or holidays. If we are experiencing a high volume of orders, shipments may be delayed by a few days. Please allow additional days in transit for delivery.</p><h4 data-path-to-node=\"7\">2. Shipping Rates &amp; Delivery Estimates</h4><p data-path-to-node=\"8\">Shipping charges for your order will be calculated and displayed at checkout.</p><ul data-path-to-node=\"9\"><li><p data-path-to-node=\"9,0,0\"><b data-path-to-node=\"9,0,0\" data-index-in-node=\"0\">Standard Shipping:</b> [e.g., 3–5 business days] — Cost: [e.g., $5.00]</p></li><li><p data-path-to-node=\"9,1,0\"><b data-path-to-node=\"9,1,0\" data-index-in-node=\"0\">Express Shipping:</b> [e.g., 1–2 business days] — Cost: [e.g., $15.00]</p></li><li><p data-path-to-node=\"9,2,0\"><b data-path-to-node=\"9,2,0\" data-index-in-node=\"0\">Local Delivery (Alexandria):</b> [e.g., Same day or next day] — Cost: [e.g., Free over $50]</p></li></ul><h4 data-path-to-node=\"10\">3. Shipment Confirmation &amp; Order Tracking</h4><p data-path-to-node=\"11\">You will receive a Shipment Confirmation email once your order has shipped containing your tracking number(s). The tracking number will be active within 24 hours.</p><h4 data-path-to-node=\"12\">4. Customs, Duties, and Taxes</h4><p data-path-to-node=\"13\"><b data-path-to-node=\"13\" data-index-in-node=\"0\">elnisr.online</b> is not responsible for any customs and taxes applied to your order. All fees imposed during or after shipping are the responsibility of the customer (tariffs, taxes, etc.).</p><h4 data-path-to-node=\"14\">5. Damages</h4><p data-path-to-node=\"15\"><b data-path-to-node=\"15\" data-index-in-node=\"0\">elnisr.online</b> is not liable for any products damaged or lost during shipping. If you received your order damaged, please contact the shipment carrier to file a claim. Please save all packaging materials and damaged goods before filing a claim.</p><h4 data-path-to-node=\"16\">6. International Shipping Policy</h4><p data-path-to-node=\"17\">We currently ship to [Insert Countries]. Shipping rates and delivery times for international orders vary by location and will be calculated at checkout.</p>', 0, 1, '2025-05-11 07:50:11', '2026-03-20 10:23:46'),
(8, 'Service Policy', 'service-policy', '<ul class=\"text-muted small mb-2\" style=\"-webkit-font-smoothing: antialiased; list-style-position: initial; list-style-image: initial; padding-left: 20px;\"><li class=\"mb-1\" style=\"color: rgb(125, 135, 156) !important; font-family: &quot;Open Sans&quot;, sans-serif; font-size: 12.8px; -webkit-font-smoothing: antialiased;\"><h1 data-path-to-node=\"2\">Service Policy for elnisr.online</h1><p data-path-to-node=\"3\"><b data-path-to-node=\"3\" data-index-in-node=\"0\">Effective Date:</b> January 8, 2026</p><p data-path-to-node=\"4\"><b data-path-to-node=\"4\" data-index-in-node=\"0\">elnisr.online</b> (\"we,\" \"us,\" or \"our\") is dedicated to providing a high-quality and reliable experience for our users. This Service Policy outlines the terms and conditions regarding how our services are delivered and the mutual responsibilities between us and the user.</p><hr data-path-to-node=\"5\"><h3 data-path-to-node=\"6\">1. Scope of Services</h3><p data-path-to-node=\"7\">We provide a range of [Insert Service Type, e.g., Digital Solutions, E-commerce, Consultancy]. While we strive to ensure information accuracy and 24/7 service availability, services may be subject to periodic updates or technical maintenance.</p><h3 data-path-to-node=\"8\">2. User Eligibility</h3><p data-path-to-node=\"9\">By using our services, you represent and warrant that:</p><ul data-path-to-node=\"10\"><li><p data-path-to-node=\"10,0,0\">You are at least <b data-path-to-node=\"10,0,0\" data-index-in-node=\"17\">18 years of age</b> (or have parental/guardian consent).</p></li><li><p data-path-to-node=\"10,1,0\">You have provided accurate and complete information when creating your account.</p></li><li><p data-path-to-node=\"10,2,0\">You will use the service for lawful purposes only and in compliance with local and international laws.</p></li></ul><h3 data-path-to-node=\"11\">3. Service Delivery Standards</h3><ul data-path-to-node=\"12\"><li><p data-path-to-node=\"12,0,0\"><b data-path-to-node=\"12,0,0\" data-index-in-node=\"0\">Quality:</b> We commit to delivering services in accordance with established professional standards.</p></li><li><p data-path-to-node=\"12,1,0\"><b data-path-to-node=\"12,1,0\" data-index-in-node=\"0\">Timeline:</b> We will make every effort to adhere to the delivery or activation schedules specified at the time of purchase.</p></li><li><p data-path-to-node=\"12,2,0\"><b data-path-to-node=\"12,2,0\" data-index-in-node=\"0\">Support:</b> Technical support is available to users via designated channels (Email/Chat) during official business hours.</p></li></ul><h3 data-path-to-node=\"13\">4. Payments and Fees</h3><ul data-path-to-node=\"14\"><li><p data-path-to-node=\"14,0,0\">All applicable fees must be paid prior to service activation, unless otherwise stated.</p></li><li><p data-path-to-node=\"14,1,0\">All listed prices [include / do not include] applicable taxes.</p></li><li><p data-path-to-node=\"14,2,0\">In the event of late payment, we reserve the right to suspend or terminate access to the service.</p></li></ul><h3 data-path-to-node=\"15\">5. Cancellation and Refund Policy</h3><ul data-path-to-node=\"16\"><li><p data-path-to-node=\"16,0,0\"><b data-path-to-node=\"16,0,0\" data-index-in-node=\"0\">Cancellation:</b> Users may request service cancellation through their dashboard or by contacting support.</p></li><li><p data-path-to-node=\"16,1,0\"><b data-path-to-node=\"16,1,0\" data-index-in-node=\"0\">Refunds:</b> Refund requests are evaluated based on a [Insert Period, e.g., 14-day] window from the date of purchase, provided the service has not been fully consumed or the terms violated.</p></li></ul><h3 data-path-to-node=\"17\">6. Acceptable Use</h3><p data-path-to-node=\"18\">The following actions are strictly prohibited:</p><ul data-path-to-node=\"19\"><li><p data-path-to-node=\"19,0,0\">Using the service to send unsolicited messages (Spam).</p></li><li><p data-path-to-node=\"19,1,0\">Attempting to breach or disable the site\'s infrastructure.</p></li><li><p data-path-to-node=\"19,2,0\">Reselling the service to third parties without prior written consent from us.</p></li></ul><h3 data-path-to-node=\"20\">7. Limitation of Liability</h3><p data-path-to-node=\"21\">While we strive for excellence, <b data-path-to-node=\"21\" data-index-in-node=\"32\">elnisr.online</b> is not liable for:</p><ul data-path-to-node=\"22\"><li><p data-path-to-node=\"22,0,0\">Interruptions caused by third-party internet service providers or Force Majeure.</p></li><li><p data-path-to-node=\"22,1,0\">Data loss resulting from the user\'s misuse of the account.</p></li><li><p data-path-to-node=\"22,2,0\">Indirect or consequential damages arising from the use of the service.</p></li></ul><h3 data-path-to-node=\"23\">8. Policy Amendments</h3><p data-path-to-node=\"24\">We reserve the right to modify this Service Policy at any time. Users will be notified of any material changes via email or through a notice on the website.</p><hr data-path-to-node=\"25\"><h3 data-path-to-node=\"26\">9. Contact Us</h3><p data-path-to-node=\"27\">For any inquiries regarding this Service Policy, please contact:</p><ul data-path-to-node=\"28\"><li><p data-path-to-node=\"28,0,0\"><b data-path-to-node=\"28,0,0\" data-index-in-node=\"0\">Email:</b> [email protected]</p></li><li><p data-path-to-node=\"28,1,0\"><b data-path-to-node=\"28,1,0\" data-index-in-node=\"0\">Address:</b> 455 Horria Road, Alexandria, Egypt</p></li></ul></li></ul>', 1, 1, '2026-03-14 18:23:51', '2026-03-14 18:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `business_settings`
--

CREATE TABLE `business_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_settings`
--

INSERT INTO `business_settings` (`id`, `type`, `value`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'system_default_currency', '8', 1, '2020-10-11 07:43:44', '2026-03-23 11:00:13'),
(2, 'language', '[{\"id\":\"1\",\"name\":\"english\",\"direction\":\"ltr\",\"code\":\"en\",\"country_code\":\"en\",\"status\":1,\"default\":true},{\"id\":2,\"name\":\"\\u0639\\u0631\\u0628\\u064a\",\"direction\":\"rtl\",\"code\":\"ar\",\"country_code\":\"eg\",\"status\":1,\"default\":false}]', 1, '2020-10-11 07:53:02', '2026-03-23 13:42:32'),
(3, 'mail_config', '{\"status\":0,\"name\":\"demo\",\"host\":\"mail.demo.com\",\"driver\":\"SMTP\",\"port\":\"587\",\"username\":\"info@demo.com\",\"email_id\":\"info@demo.com\",\"encryption\":\"TLS\",\"password\":\"demo\"}', 1, '2020-10-12 10:29:18', '2021-07-06 12:32:01'),
(4, 'cash_on_delivery', '{\"status\":\"1\"}', 1, NULL, '2025-02-04 03:59:07'),
(6, 'ssl_commerz_payment', '{\"status\":\"0\",\"environment\":\"sandbox\",\"store_id\":\"\",\"store_password\":\"\"}', 1, '2020-11-09 08:36:51', '2023-01-10 05:51:56'),
(10, 'company_phone', '16870', 1, NULL, '2026-03-23 11:00:13'),
(11, 'company_name', '{\"en\":\"ElNisr\",\"ar\":\"\\u0627\\u0644\\u0646\\u0633\\u0631\"}', 1, NULL, '2026-03-23 23:55:20'),
(12, 'company_web_logo', '{\"image_name\":\"2025-07-08-686cba44bf91a.webp\",\"storage\":\"public\"}', 1, NULL, '2025-07-08 03:57:16'),
(13, 'company_mobile_logo', '{\"image_name\":\"2025-07-08-686cba44f26c0.webp\",\"storage\":\"public\"}', 1, NULL, '2025-07-08 03:57:16'),
(14, 'terms_condition', '<h1 data-start=\"411\" data-end=\"435\">Terms and Conditions</h1>\r\n<p data-start=\"436\" data-end=\"472\"><strong data-start=\"436\" data-end=\"472\">SalesCentral.DynamicLogic.online</strong></p>\r\n<p data-start=\"474\" data-end=\"509\"><strong data-start=\"474\" data-end=\"493\">Effective Date:</strong> January 8, 2026</p>\r\n<p data-start=\"511\" data-end=\"784\">These Terms and Conditions (“Terms”) govern your access to and use of SalesCentral.DynamicLogic.online (“Website,” “Service,” “we,” “us,” or “our”). By accessing or using this Website, you agree to be bound by these Terms. If you do not agree, you must not use the Service.</p>\r\n<hr data-start=\"786\" data-end=\"789\">\r\n<h2 data-start=\"791\" data-end=\"833\">1. Eligibility and Account Registration</h2>\r\n<p data-start=\"835\" data-end=\"922\">To use certain features of the Website, you must create an account. You represent that:</p>\r\n<ul data-start=\"923\" data-end=\"1093\"><li data-start=\"923\" data-end=\"954\">\r\n<p data-start=\"925\" data-end=\"954\">You are at least 18 years old</p>\r\n</li><li data-start=\"955\" data-end=\"1009\">\r\n<p data-start=\"957\" data-end=\"1009\">The information you provide is accurate and complete</p>\r\n</li><li data-start=\"1010\" data-end=\"1093\">\r\n<p data-start=\"1012\" data-end=\"1093\">You are responsible for maintaining the confidentiality of your login credentials</p>\r\n</li></ul>\r\n<p data-start=\"1095\" data-end=\"1163\">You are responsible for all activity that occurs under your account.</p>\r\n<hr data-start=\"1165\" data-end=\"1168\">\r\n<h2 data-start=\"1170\" data-end=\"1194\">2. Use of the Website</h2>\r\n<p data-start=\"1196\" data-end=\"1213\">You agree not to:</p>\r\n<ul data-start=\"1214\" data-end=\"1433\"><li data-start=\"1214\" data-end=\"1269\">\r\n<p data-start=\"1216\" data-end=\"1269\">Use the Website for unlawful or fraudulent purposes</p>\r\n</li><li data-start=\"1270\" data-end=\"1328\">\r\n<p data-start=\"1272\" data-end=\"1328\">Attempt to gain unauthorized access to systems or data</p>\r\n</li><li data-start=\"1329\" data-end=\"1388\">\r\n<p data-start=\"1331\" data-end=\"1388\">Interfere with the security or operation of the Website</p>\r\n</li><li data-start=\"1389\" data-end=\"1433\">\r\n<p data-start=\"1391\" data-end=\"1433\">Upload malicious code or harmful content</p>\r\n</li></ul>\r\n<p data-start=\"1435\" data-end=\"1514\">We reserve the right to suspend or terminate accounts that violate these Terms.</p>\r\n<hr data-start=\"1516\" data-end=\"1519\">\r\n<h2 data-start=\"1521\" data-end=\"1557\">3. Products, Orders, and Payments</h2>\r\n<ul data-start=\"1559\" data-end=\"1788\"><li data-start=\"1559\" data-end=\"1634\">\r\n<p data-start=\"1561\" data-end=\"1634\">Product descriptions and pricing are provided as accurately as possible</p>\r\n</li><li data-start=\"1635\" data-end=\"1677\">\r\n<p data-start=\"1637\" data-end=\"1677\">Prices may change without prior notice</p>\r\n</li><li data-start=\"1678\" data-end=\"1733\">\r\n<p data-start=\"1680\" data-end=\"1733\">Orders are subject to availability and confirmation</p>\r\n</li><li data-start=\"1734\" data-end=\"1788\">\r\n<p data-start=\"1736\" data-end=\"1788\">Payments must be completed before order processing</p>\r\n</li></ul>\r\n<p data-start=\"1790\" data-end=\"1913\">We reserve the right to refuse or cancel orders at our discretion, including in cases of suspected fraud or pricing errors.</p>\r\n<hr data-start=\"1915\" data-end=\"1918\">\r\n<h2 data-start=\"1920\" data-end=\"1947\">4. Shipping and Delivery</h2>\r\n<p data-start=\"1949\" data-end=\"2097\">Delivery times are estimates and not guaranteed. We are not responsible for delays caused by carriers, customs, or circumstances beyond our control.</p>\r\n<hr data-start=\"2099\" data-end=\"2102\">\r\n<h2 data-start=\"2104\" data-end=\"2129\">5. Returns and Refunds</h2>\r\n<p data-start=\"2131\" data-end=\"2315\">Return and refund eligibility is determined according to the specific product and applicable consumer protection laws. Details will be provided at the time of purchase or upon request.</p>\r\n<hr data-start=\"2317\" data-end=\"2320\">\r\n<h2 data-start=\"2322\" data-end=\"2360\">6. Account Termination and Deletion</h2>\r\n<p data-start=\"2362\" data-end=\"2428\">Users may delete their accounts at any time through the dashboard:</p>\r\n<p data-start=\"2430\" data-end=\"2475\"><strong data-start=\"2430\" data-end=\"2475\">Dashboard → Profile Info → Delete account</strong></p>\r\n<p data-start=\"2477\" data-end=\"2499\">Upon account deletion:</p>\r\n<ul data-start=\"2500\" data-end=\"2667\"><li data-start=\"2500\" data-end=\"2546\">\r\n<p data-start=\"2502\" data-end=\"2546\">Access to the account is permanently revoked</p>\r\n</li><li data-start=\"2547\" data-end=\"2611\">\r\n<p data-start=\"2549\" data-end=\"2611\">Personal data is deleted in accordance with our Privacy Policy</p>\r\n</li><li data-start=\"2612\" data-end=\"2667\">\r\n<p data-start=\"2614\" data-end=\"2667\">Certain records may be retained where required by law</p>\r\n</li></ul>\r\n<p data-start=\"2669\" data-end=\"2753\">We reserve the right to suspend or terminate accounts for violations of these Terms.</p>\r\n<hr data-start=\"2755\" data-end=\"2758\">\r\n<h2 data-start=\"2760\" data-end=\"2787\">7. Intellectual Property</h2>\r\n<p data-start=\"2789\" data-end=\"2980\">All content on the Website, including text, graphics, logos, and software, is the property of SalesCentral.DynamicLogic.online or its licensors and is protected by intellectual property laws.</p>\r\n<p data-start=\"2982\" data-end=\"3076\">You may not copy, modify, distribute, or exploit any content without prior written permission.</p>\r\n<hr data-start=\"3078\" data-end=\"3081\">\r\n<h2 data-start=\"3083\" data-end=\"3119\">8. Third-Party Services and Links</h2>\r\n<p data-start=\"3121\" data-end=\"3275\">The Website may contain links to third-party websites or services. We are not responsible for the content, policies, or practices of third-party services.</p>\r\n<p data-start=\"3277\" data-end=\"3325\">Use of third-party services is at your own risk.</p>\r\n<hr data-start=\"3327\" data-end=\"3330\">\r\n<h2 data-start=\"3332\" data-end=\"3362\">9. Disclaimer of Warranties</h2>\r\n<p data-start=\"3364\" data-end=\"3496\">The Website and services are provided <strong data-start=\"3402\" data-end=\"3413\">“as is”</strong> and <strong data-start=\"3418\" data-end=\"3436\">“as available”</strong> without warranties of any kind, whether express or implied.</p>\r\n<p data-start=\"3498\" data-end=\"3556\">We do not guarantee uninterrupted or error-free operation.</p>\r\n<hr data-start=\"3558\" data-end=\"3561\">\r\n<h2 data-start=\"3563\" data-end=\"3593\">10. Limitation of Liability</h2>\r\n<p data-start=\"3595\" data-end=\"3692\">To the maximum extent permitted by law, SalesCentral.DynamicLogic.online shall not be liable for:</p>\r\n<ul data-start=\"3693\" data-end=\"3869\"><li data-start=\"3693\" data-end=\"3743\">\r\n<p data-start=\"3695\" data-end=\"3743\">Indirect, incidental, or consequential damages</p>\r\n</li><li data-start=\"3744\" data-end=\"3796\">\r\n<p data-start=\"3746\" data-end=\"3796\">Loss of data, profits, or business opportunities</p>\r\n</li><li data-start=\"3797\" data-end=\"3869\">\r\n<p data-start=\"3799\" data-end=\"3869\">Unauthorized access to user data beyond reasonable security measures</p>\r\n</li></ul>\r\n<hr data-start=\"3871\" data-end=\"3874\">\r\n<h2 data-start=\"3876\" data-end=\"3898\">11. Indemnification</h2>\r\n<p data-start=\"3900\" data-end=\"4038\">You agree to indemnify and hold harmless SalesCentral.DynamicLogic.online from any claims, damages, liabilities, or expenses arising from:</p>\r\n<ul data-start=\"4039\" data-end=\"4160\"><li data-start=\"4039\" data-end=\"4066\">\r\n<p data-start=\"4041\" data-end=\"4066\">Your use of the Website</p>\r\n</li><li data-start=\"4067\" data-end=\"4100\">\r\n<p data-start=\"4069\" data-end=\"4100\">Your violation of these Terms</p>\r\n</li><li data-start=\"4101\" data-end=\"4160\">\r\n<p data-start=\"4103\" data-end=\"4160\">Your violation of applicable laws or third-party rights</p>\r\n</li></ul>\r\n<hr data-start=\"4162\" data-end=\"4165\">\r\n<h2 data-start=\"4167\" data-end=\"4187\">12. Governing Law</h2>\r\n<p data-start=\"4189\" data-end=\"4339\">These Terms are governed by and construed in accordance with the laws of the <strong data-start=\"4266\" data-end=\"4292\">Arab Republic of Egypt</strong>, without regard to conflict-of-law principles.</p>\r\n<hr data-start=\"4341\" data-end=\"4344\">\r\n<h2 data-start=\"4346\" data-end=\"4375\">13. Changes to These Terms</h2>\r\n<p data-start=\"4377\" data-end=\"4526\">We may update these Terms from time to time. Continued use of the Website after changes become effective constitutes acceptance of the updated Terms.</p>\r\n<hr data-start=\"4528\" data-end=\"4531\">\r\n<h2 data-start=\"4533\" data-end=\"4559\">14. Contact Information</h2>\r\n<p data-start=\"4561\" data-end=\"4628\">For questions regarding these Terms and Conditions, please contact:</p>\r\n<p data-start=\"4630\" data-end=\"4714\"><strong data-start=\"4630\" data-end=\"4640\">Email:</strong> <a data-start=\"4641\" data-end=\"4661\" class=\"decorated-link cursor-pointer\" rel=\"noopener\">enlisrmisr@gmail.com<span aria-hidden=\"true\" class=\"ms-0.5 inline-block align-middle leading-none\"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" aria-hidden=\"true\" data-rtl-flip=\"\" class=\"block h-[0.75em] w-[0.75em] stroke-current stroke-[0.75]\"><use fill=\"currentColor\"></use></svg></span></a><br data-start=\"4661\" data-end=\"4664\">\r\n<strong data-start=\"4664\" data-end=\"4676\">Address:</strong><br data-start=\"4676\" data-end=\"4679\">\r\n455 Horria Road<br data-start=\"4694\" data-end=\"4697\">\r\nAlexandria, Egypt</p>', 1, NULL, '2026-03-19 13:16:55'),
(15, 'about_us', '<p data-path-to-node=\"3\"><b data-path-to-node=\"3\" data-index-in-node=\"0\">Heritage of Power and Reliability</b>\r\nEstablished as a cornerstone of the Egyptian automotive industry, <b data-path-to-node=\"3\" data-index-in-node=\"100\">Elnisr Batteries</b> has dedicated decades to manufacturing high-performance car batteries that power millions of vehicles across the nation. As a proud local manufacturer, we combine deep-rooted technical expertise with modern innovation to produce batteries specifically engineered to withstand the demanding climate and driving conditions of Egypt. Our commitment to excellence ensures that every battery leaving our factory meets the highest international standards of durability and starting power.</p><p data-path-to-node=\"4\"><b data-path-to-node=\"4\" data-index-in-node=\"0\">Driving the Future of Energy</b>\r\nAt Elnisr, we are more than just a manufacturer; we are your trusted partner on every journey. Through our integrated e-commerce platform, we aim to bridge the gap between traditional industrial quality and modern digital convenience, allowing customers to access premium energy solutions with ease. By investing in sustainable technologies and rigorous quality control, we continue to lead the Egyptian market, ensuring that the \"Eagle\" (Elnisr) remains the symbol of trust, longevity, and unstoppable energy for every car owner.</p><p data-path-to-node=\"4\"><br></p><p data-path-to-node=\"4\"><br></p>', 1, NULL, '2026-03-20 08:16:36'),
(16, 'sms_nexmo', '{\"status\":\"0\",\"nexmo_key\":\"custo5cc042f7abf4c\",\"nexmo_secret\":\"custo5cc042f7abf4c@ssl\"}', 1, NULL, NULL),
(17, 'company_email', 'info@elnisr.com', 1, NULL, '2026-03-23 11:00:13'),
(18, 'colors', '{\"primary\":\"#239e92\",\"secondary\":\"#000000\",\"primary_light\":\"#CFDFFB\"}', 1, '2020-10-11 13:53:02', '2026-03-23 11:00:13'),
(19, 'company_footer_logo', '{\"image_name\":\"2025-04-29-6810ad0dae327.webp\",\"storage\":\"public\"}', 1, NULL, '2025-04-29 13:42:21'),
(20, 'company_copyright_text', '{\"en\":\"CopyRight DynamicLogic@2026\",\"ar\":\"\\u062d\\u0642\\u0648\\u0642 \\u0627\\u0644\\u0645\\u0644\\u0643\\u064a\\u0629\"}', 1, NULL, '2026-03-23 11:00:13'),
(21, 'download_app_apple_stroe', '{\"status\":\"1\",\"link\":\"https:\\/\\/www.apple.com\\/eg\\/app-store\\/\"}', 1, NULL, '2026-03-23 11:00:13'),
(22, 'download_app_google_stroe', '{\"status\":\"1\",\"link\":\"https:\\/\\/play.google.com\\/store\\/apps?hl=en_US&gl=US\"}', 1, NULL, '2026-03-23 11:00:13'),
(23, 'company_fav_icon', '{\"image_name\":\"2025-07-08-686cba45343f3.webp\",\"storage\":\"public\"}', 1, '2020-10-11 13:53:02', '2025-07-08 03:57:17'),
(24, 'fcm_topic', '', 1, NULL, NULL),
(25, 'fcm_project_id', '', 1, NULL, '2025-10-31 07:05:56'),
(26, 'push_notification_key', '{\r\n  \"type\": \"service_account\",\r\n  \"project_id\": \"dinnermile-2bafc\",\r\n  \"private_key_id\": \"bc8213a2636c9f58547fd4a3603403422db11261\",\r\n  \"private_key\": \"-----BEGIN PRIVATE KEY-----\\nMIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCd7ulJCnPBrBih\\nFhtHuoD9YP1PahSIdvjAlvgPLVwTIrLuTu0QgQAliSAt1Mr4HoxJgAa0awbIMTyh\\nyJ1noexzCSsckPtX0PY6KmxNrz2Ma0ErIj4fI5yHj7ii7+BOy+Z+WzwrQCsXAtSO\\nI4dBDgk9qK9TJvkrsZUepTZpZWs4BSD3B7t+mpNP/3Iqb8uwOLmz5Lsg9IsF/N3V\\nGp83a1zEXhSeUwpISXxZrDhuZ1XWgKnUmGWWQSLTEAcNXUSFiMXZ2WOIk/VYLcAf\\n5pgqe03Rteelyg5OUelzgFLLB+d46RPwTIbxAeXEueUftR7ljCH20EfF9XQ76nrz\\n6MV6+Aw9AgMBAAECggEADbyiapRP0zr+fWxofQmvfy/OI00tHLLfGhPyND0UHXn5\\nfu7l1yH7+vJ9a7Ru3xFRJHIlTdC4AKD/uWwIp43sUhPXEx9X1/XjT4CHIQ6qrNRb\\nZmlju95OFssdGoGrd08W7PX55XfUXPuwX0NJ2BJkzV8nDiAoBhvmcN7v56TxpCfz\\nESCdcGOqtTNc842I4jCkDO65o5LSt90IXi65EVhh4Yg3UlvpbUWwYAjJC3lFinVF\\nA/bV0EMRCRUupPTWqmJl5q+fgHntpTlYi29yRIvc9um1ac5j9KUHxp8J01km50h6\\nDAX5tVunPLmngclEPpIQ3mmzm8J/a66PZvqujrvjBQKBgQDJJIia7ezCho7TAvfI\\n0VQ8caRiqG9v+BNp4YlYdqgqHhyWPbmM/QVs7BmGKtus/XZg/7tWnzC7wJiVJRB0\\nOSYPWC3Ltwt99UEnQlXkOayeUmkRGMUdhEyhQFNSwUOl9f0Sckk9aCQCAM3O+M9T\\naP2QEoUUFrQiyu1AR5xC+xGxDwKBgQDJAY+sZg4RTE2wrDcB4UtKqi6vdlvGlBF4\\n1kCbfpNMjRohIlB517kcVFtGNu07jMMcgpiBoUrBwMQiCW/JCv6JXlkGk4IsbOUA\\nuK4Kz6xSWYflBMHbM3RDzKfHLh1QvwYdC1evPXJ75vi5xJm/GUrqjUc0N77sHftm\\nKpDpum1V8wKBgCc8aSlPoA+SD+o5efxCWRwxTs+v64z7502QISqQet08YncsMzW9\\nZYGJzLDPS2rDRoRFXlXXV7pIJ3twb3U8cKAto0FJw4Qeg0cVOYv7dCCuErCzFEBd\\nvlT2J0rNSFTnVyZyBLdlySBa58qn3kl3AX9JHYx9oUXoL7+KOIEYWKshAoGAfe/J\\neaVTaQkan/e2Wyoxxy0LJQoOBEPfEouCXSoX1d2OInZiX4SGSTadHUfqqOXPlPxJ\\n2uYYdX52JDEvZZHK2nxPYOxoobb0X9hVyxZEjC/mEdpCLzl0vcnq0MOWwHF+vhHO\\nNVBVe0XCTnncLjwFkSFFHHVU3JEIYwGGW7pfKHsCgYBg2c5VX2rDOgpqMjvV8F6P\\nzAT79ypTvp0BowMs5uEEIC6TWvSZ2DMIWtyFxnTZ+v14utw/XSdrEPorKdM1Z3lt\\nkMUILv+r6oCs9JE6sX//qy8CbJi4L3NSzUj1zVtHPwn2FzxvyQUhdoaso9IQKIt4\\nGXhvzOWUGLsA2sFULBLvcA==\\n-----END PRIVATE KEY-----\\n\",\r\n  \"client_email\": \"firebase-adminsdk-fbsvc@dinnermile-2bafc.iam.gserviceaccount.com\",\r\n  \"client_id\": \"102505534036029876583\",\r\n  \"auth_uri\": \"https://accounts.google.com/o/oauth2/auth\",\r\n  \"token_uri\": \"https://oauth2.googleapis.com/token\",\r\n  \"auth_provider_x509_cert_url\": \"https://www.googleapis.com/oauth2/v1/certs\",\r\n  \"client_x509_cert_url\": \"https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-fbsvc%40dinnermile-2bafc.iam.gserviceaccount.com\",\r\n  \"universe_domain\": \"googleapis.com\"\r\n}', 1, NULL, '2025-10-31 07:05:56'),
(27, 'order_pending_message', '{\"status\":\"1\",\"message\":\"order pen message\"}', 1, NULL, NULL),
(28, 'order_confirmation_msg', '{\"status\":\"1\",\"message\":\"Order con Message\"}', 1, NULL, NULL),
(29, 'order_processing_message', '{\"status\":\"1\",\"message\":\"Order pro Message\"}', 1, NULL, NULL),
(30, 'out_for_delivery_message', '{\"status\":\"1\",\"message\":\"Order ouut Message\"}', 1, NULL, NULL),
(31, 'order_delivered_message', '{\"status\":\"1\",\"message\":\"Order del Message\"}', 1, NULL, NULL),
(33, 'sales_commission', '0', 1, NULL, '2021-06-11 18:13:13'),
(34, 'seller_registration', '1', 1, NULL, '2021-06-04 21:02:48'),
(35, 'pnc_language', '[\"en\",\"ar\"]', 1, NULL, '2026-03-23 13:42:32'),
(36, 'order_returned_message', '{\"status\":\"1\",\"message\":\"Order hh Message\"}', 1, NULL, NULL),
(37, 'order_failed_message', '{\"status\":null,\"message\":\"Order fa Message\"}', 1, NULL, NULL),
(40, 'delivery_boy_assign_message', '{\"status\":0,\"message\":\"\"}', 1, NULL, NULL),
(41, 'delivery_boy_start_message', '{\"status\":0,\"message\":\"\"}', 1, NULL, NULL),
(42, 'delivery_boy_delivered_message', '{\"status\":0,\"message\":\"\"}', 1, NULL, NULL),
(43, 'terms_and_conditions', '', 1, NULL, NULL),
(44, 'minimum_order_value', '1', 1, NULL, NULL),
(45, 'privacy_policy', '<hr data-start=\"410\" data-end=\"413\">\r\n<h1 data-start=\"415\" data-end=\"433\">Privacy Policy</h1>\r\n<p data-start=\"434\" data-end=\"470\"><strong data-start=\"434\" data-end=\"470\">elnisr.online</strong></p>\r\n<p data-start=\"472\" data-end=\"507\"><strong data-start=\"472\" data-end=\"491\">Effective Date:</strong> January 8, 2026</p>\r\n<p data-start=\"509\" data-end=\"748\">SalesCentral.DynamicLogic.online \r\n(“we,” “us,” or “our”) is committed to protecting your privacy. This \r\nPrivacy Policy explains how we collect, use, disclose, and safeguard \r\nyour personal data when you access our website and use our services.</p>\r\n<p data-start=\"750\" data-end=\"916\">By using this website, you agree to \r\nthe collection and use of information in accordance with this Privacy \r\nPolicy. If you do not agree, please do not use our services.</p>\r\n<hr data-start=\"918\" data-end=\"921\">\r\n<h2 data-start=\"923\" data-end=\"951\">1. Information We Collect</h2>\r\n<p data-start=\"953\" data-end=\"1068\">We collect information that you provide directly and information collected automatically when you use our services.</p>\r\n<h3 data-start=\"1070\" data-end=\"1102\">A. Personal Data You Provide</h3>\r\n<ul data-start=\"1103\" data-end=\"1304\"><li data-start=\"1103\" data-end=\"1116\">\r\n<p data-start=\"1105\" data-end=\"1116\">Full name</p>\r\n</li><li data-start=\"1117\" data-end=\"1134\">\r\n<p data-start=\"1119\" data-end=\"1134\">Email address</p>\r\n</li><li data-start=\"1135\" data-end=\"1151\">\r\n<p data-start=\"1137\" data-end=\"1151\">Phone number</p>\r\n</li><li data-start=\"1152\" data-end=\"1184\">\r\n<p data-start=\"1154\" data-end=\"1184\">Billing and shipping address</p>\r\n</li><li data-start=\"1185\" data-end=\"1214\">\r\n<p data-start=\"1187\" data-end=\"1214\">Account login credentials</p>\r\n</li><li data-start=\"1215\" data-end=\"1248\">\r\n<p data-start=\"1217\" data-end=\"1248\">Order and transaction details</p>\r\n</li><li data-start=\"1249\" data-end=\"1304\">\r\n<p data-start=\"1251\" data-end=\"1304\">Messages submitted through contact or support forms</p>\r\n</li></ul>\r\n<h3 data-start=\"1306\" data-end=\"1341\">B. Automatically Collected Data</h3>\r\n<ul data-start=\"1342\" data-end=\"1503\"><li data-start=\"1342\" data-end=\"1356\">\r\n<p data-start=\"1344\" data-end=\"1356\">IP address</p>\r\n</li><li data-start=\"1357\" data-end=\"1385\">\r\n<p data-start=\"1359\" data-end=\"1385\">Browser type and version</p>\r\n</li><li data-start=\"1386\" data-end=\"1422\">\r\n<p data-start=\"1388\" data-end=\"1422\">Device type and operating system</p>\r\n</li><li data-start=\"1423\" data-end=\"1457\">\r\n<p data-start=\"1425\" data-end=\"1457\">Pages visited and interactions</p>\r\n</li><li data-start=\"1458\" data-end=\"1503\">\r\n<p data-start=\"1460\" data-end=\"1503\">Cookies and similar tracking technologies</p>\r\n</li></ul>\r\n<p data-start=\"1505\" data-end=\"1636\">We do not intentionally collect \r\nsensitive personal data (such as race, religion, or health data) unless \r\nyou voluntarily provide it.</p>\r\n<hr data-start=\"1638\" data-end=\"1641\">\r\n<h2 data-start=\"1643\" data-end=\"1676\">2. How We Use Your Information</h2>\r\n<p data-start=\"1678\" data-end=\"1748\">We use personal data only for legitimate business purposes, including:</p>\r\n<ul data-start=\"1750\" data-end=\"2068\"><li data-start=\"1750\" data-end=\"1792\">\r\n<p data-start=\"1752\" data-end=\"1792\">Providing and maintaining our services</p>\r\n</li><li data-start=\"1793\" data-end=\"1831\">\r\n<p data-start=\"1795\" data-end=\"1831\">Processing orders and transactions</p>\r\n</li><li data-start=\"1832\" data-end=\"1858\">\r\n<p data-start=\"1834\" data-end=\"1858\">Managing user accounts</p>\r\n</li><li data-start=\"1859\" data-end=\"1907\">\r\n<p data-start=\"1861\" data-end=\"1907\">Responding to inquiries and support requests</p>\r\n</li><li data-start=\"1908\" data-end=\"1961\">\r\n<p data-start=\"1910\" data-end=\"1961\">Improving website performance and user experience</p>\r\n</li><li data-start=\"1962\" data-end=\"2022\">\r\n<p data-start=\"1964\" data-end=\"2022\">Sending administrative or service-related communications</p>\r\n</li><li data-start=\"2023\" data-end=\"2068\">\r\n<p data-start=\"2025\" data-end=\"2068\">Preventing fraud and securing our systems</p>\r\n</li></ul>\r\n<p data-start=\"2070\" data-end=\"2151\">We do not use personal data for purposes incompatible with those described above.</p>\r\n<hr data-start=\"2153\" data-end=\"2156\">\r\n<h2 data-start=\"2158\" data-end=\"2209\">3. Legal Basis for Processing (Where Applicable)</h2>\r\n<p data-start=\"2211\" data-end=\"2277\">Depending on your jurisdiction, we process personal data based on:</p>\r\n<ul data-start=\"2279\" data-end=\"2532\"><li data-start=\"2279\" data-end=\"2339\">\r\n<p data-start=\"2281\" data-end=\"2339\"><strong data-start=\"2281\" data-end=\"2305\">Contract performance</strong> – to deliver requested services</p>\r\n</li><li data-start=\"2340\" data-end=\"2415\">\r\n<p data-start=\"2342\" data-end=\"2415\"><strong data-start=\"2342\" data-end=\"2366\">Legitimate interests</strong> – to operate, improve, and secure our platform</p>\r\n</li><li data-start=\"2416\" data-end=\"2473\">\r\n<p data-start=\"2418\" data-end=\"2473\"><strong data-start=\"2418\" data-end=\"2429\">Consent</strong> – for marketing and non-essential cookies</p>\r\n</li><li data-start=\"2474\" data-end=\"2532\">\r\n<p data-start=\"2476\" data-end=\"2532\"><strong data-start=\"2476\" data-end=\"2497\">Legal obligations</strong> – to comply with applicable laws</p>\r\n</li></ul>\r\n<hr data-start=\"2534\" data-end=\"2537\">\r\n<h2 data-start=\"2539\" data-end=\"2578\">4. Cookies and Tracking Technologies</h2>\r\n<p data-start=\"2580\" data-end=\"2623\">We use cookies and similar technologies to:</p>\r\n<ul data-start=\"2624\" data-end=\"2725\"><li data-start=\"2624\" data-end=\"2658\">\r\n<p data-start=\"2626\" data-end=\"2658\">Enable core site functionality</p>\r\n</li><li data-start=\"2659\" data-end=\"2697\">\r\n<p data-start=\"2661\" data-end=\"2697\">Analyze traffic and usage patterns</p>\r\n</li><li data-start=\"2698\" data-end=\"2725\">\r\n<p data-start=\"2700\" data-end=\"2725\">Improve user experience</p>\r\n</li></ul>\r\n<p data-start=\"2727\" data-end=\"2840\">You can manage or disable cookies through your browser settings. Disabling cookies may affect site functionality.</p>\r\n<hr data-start=\"2842\" data-end=\"2845\">\r\n<h2 data-start=\"2847\" data-end=\"2875\">5. Sharing of Information</h2>\r\n<p data-start=\"2877\" data-end=\"2909\">We may share personal data with:</p>\r\n<ul data-start=\"2910\" data-end=\"3093\"><li data-start=\"2910\" data-end=\"2972\">\r\n<p data-start=\"2912\" data-end=\"2972\">Service providers (hosting, payment processing, analytics)</p>\r\n</li><li data-start=\"2973\" data-end=\"3029\">\r\n<p data-start=\"2975\" data-end=\"3029\">Legal or regulatory authorities when required by law</p>\r\n</li><li data-start=\"3030\" data-end=\"3093\">\r\n<p data-start=\"3032\" data-end=\"3093\">Business successors in case of merger, acquisition, or sale</p>\r\n</li></ul>\r\n<p data-start=\"3095\" data-end=\"3154\">We <strong data-start=\"3098\" data-end=\"3113\">do not sell</strong> personal data for monetary compensation.</p>\r\n<hr data-start=\"3156\" data-end=\"3159\">\r\n<h2 data-start=\"3161\" data-end=\"3195\">6. International Data Transfers</h2>\r\n<p data-start=\"3197\" data-end=\"3408\">Your personal data may be \r\ntransferred to and processed in countries outside your country of \r\nresidence. Where required, we implement appropriate safeguards to \r\nprotect your data in accordance with applicable laws.</p>\r\n<hr data-start=\"3410\" data-end=\"3413\">\r\n<h2 data-start=\"3415\" data-end=\"3440\">7. Your Privacy Rights</h2>\r\n<p data-start=\"3442\" data-end=\"3496\">Depending on your location, you may have the right to:</p>\r\n<ul data-start=\"3497\" data-end=\"3694\"><li data-start=\"3497\" data-end=\"3526\">\r\n<p data-start=\"3499\" data-end=\"3526\">Access your personal data</p>\r\n</li><li data-start=\"3527\" data-end=\"3561\">\r\n<p data-start=\"3529\" data-end=\"3561\">Correct inaccurate information</p>\r\n</li><li data-start=\"3562\" data-end=\"3595\">\r\n<p data-start=\"3564\" data-end=\"3595\">Request deletion of your data</p>\r\n</li><li data-start=\"3596\" data-end=\"3632\">\r\n<p data-start=\"3598\" data-end=\"3632\">Restrict or object to processing</p>\r\n</li><li data-start=\"3633\" data-end=\"3661\">\r\n<p data-start=\"3635\" data-end=\"3661\">Request data portability</p>\r\n</li><li data-start=\"3662\" data-end=\"3694\">\r\n<p data-start=\"3664\" data-end=\"3694\">Withdraw consent at any time</p>\r\n</li></ul>\r\n<p data-start=\"3696\" data-end=\"3758\">Requests can be made using the contact details provided below.</p>\r\n<hr data-start=\"3760\" data-end=\"3763\">\r\n<h2 data-start=\"3765\" data-end=\"3789\">8. Children’s Privacy</h2>\r\n<p data-start=\"3791\" data-end=\"3968\">Our services are not intended for \r\nchildren under the age of 16. We do not knowingly collect personal data \r\nfrom children. If such data is discovered, it will be deleted promptly.</p>\r\n<hr data-start=\"3970\" data-end=\"3973\">\r\n<h2 data-start=\"3975\" data-end=\"3994\">9. Data Security</h2>\r\n<p data-start=\"3996\" data-end=\"4150\">We implement reasonable \r\nadministrative, technical, and organizational measures to protect \r\npersonal data. However, no system can be guaranteed 100% secure.</p>\r\n<hr data-start=\"4152\" data-end=\"4155\">\r\n<h2 data-start=\"4157\" data-end=\"4178\">10. Data Retention</h2>\r\n<p data-start=\"4180\" data-end=\"4237\">We retain personal data only for as long as necessary to:</p>\r\n<ul data-start=\"4238\" data-end=\"4348\"><li data-start=\"4238\" data-end=\"4262\">\r\n<p data-start=\"4240\" data-end=\"4262\">Provide our services</p>\r\n</li><li data-start=\"4263\" data-end=\"4304\">\r\n<p data-start=\"4265\" data-end=\"4304\">Meet legal and accounting obligations</p>\r\n</li><li data-start=\"4305\" data-end=\"4348\">\r\n<p data-start=\"4307\" data-end=\"4348\">Resolve disputes and enforce agreements</p>\r\n</li></ul>\r\n<p data-start=\"4350\" data-end=\"4420\">When data is no longer required, it is securely deleted or anonymized.</p>\r\n<hr data-start=\"4422\" data-end=\"4425\">\r\n<h2 data-start=\"4427\" data-end=\"4464\">11. Account and User Data Deletion</h2>\r\n<p data-start=\"4466\" data-end=\"4594\">Users can delete their account and associated personal data directly through their account dashboard without contacting support.</p>\r\n<h3 data-start=\"4596\" data-end=\"4629\">Self-Service Account Deletion</h3>\r\n<p data-start=\"4630\" data-end=\"4671\">To delete your account and personal data:</p>\r\n<ol data-start=\"4673\" data-end=\"4845\"><li data-start=\"4673\" data-end=\"4738\">\r\n<p data-start=\"4676\" data-end=\"4738\">Log in to your account at <strong data-start=\"4702\" data-end=\"4738\">SalesCentral.DynamicLogic.online</strong></p>\r\n</li><li data-start=\"4739\" data-end=\"4782\">\r\n<p data-start=\"4742\" data-end=\"4782\">Navigate to <strong data-start=\"4754\" data-end=\"4782\">Dashboard → Profile Info</strong></p>\r\n</li><li data-start=\"4783\" data-end=\"4813\">\r\n<p data-start=\"4786\" data-end=\"4813\">Select <strong data-start=\"4793\" data-end=\"4813\">“Delete account”</strong></p>\r\n</li><li data-start=\"4814\" data-end=\"4845\">\r\n<p data-start=\"4817\" data-end=\"4845\">Confirm the deletion request</p>\r\n</li></ol>\r\n<p data-start=\"4847\" data-end=\"4911\">Once confirmed, the account deletion process begins immediately.</p>\r\n<h3 data-start=\"4913\" data-end=\"4930\">Data Affected</h3>\r\n<p data-start=\"4931\" data-end=\"4956\">Account deletion removes:</p>\r\n<ul data-start=\"4957\" data-end=\"5130\"><li data-start=\"4957\" data-end=\"5009\">\r\n<p data-start=\"4959\" data-end=\"5009\">Account profile data (name, email, phone number)</p>\r\n</li><li data-start=\"5010\" data-end=\"5045\">\r\n<p data-start=\"5012\" data-end=\"5045\">Saved addresses and preferences</p>\r\n</li><li data-start=\"5046\" data-end=\"5085\">\r\n<p data-start=\"5048\" data-end=\"5085\">Order history linked to the account</p>\r\n</li><li data-start=\"5086\" data-end=\"5130\">\r\n<p data-start=\"5088\" data-end=\"5130\">User identifiers and stored account data</p>\r\n</li></ul>\r\n<h3 data-start=\"5132\" data-end=\"5151\">Processing Time</h3>\r\n<p data-start=\"5152\" data-end=\"5298\">Account deletion is processed immediately.<br data-start=\"5194\" data-end=\"5197\">\r\nResidual data stored in encrypted backups may persist for up to <strong data-start=\"5261\" data-end=\"5272\">30 days</strong> before permanent removal.</p>\r\n<h3 data-start=\"5300\" data-end=\"5332\">Legal and Security Retention</h3>\r\n<p data-start=\"5333\" data-end=\"5502\">Certain records may be retained \r\nwhere required by law (e.g., tax, fraud prevention, or compliance \r\nobligations). Such data is isolated and not used for active processing.</p>\r\n<hr data-start=\"5504\" data-end=\"5507\">\r\n<h2 data-start=\"5509\" data-end=\"5546\">12. Changes to This Privacy Policy</h2>\r\n<p data-start=\"5548\" data-end=\"5686\">We may update this Privacy Policy \r\nfrom time to time. Any changes will be reflected by an updated \r\n“Effective Date” at the top of this page.</p>\r\n<hr data-start=\"5688\" data-end=\"5691\">\r\n<h2 data-start=\"5693\" data-end=\"5719\">13. Contact Information</h2>\r\n<p data-start=\"5721\" data-end=\"5832\">If you have questions about this Privacy Policy or wish to exercise your privacy rights, you may contact us at:</p>\r\n<p data-start=\"5834\" data-end=\"5918\"><strong data-start=\"5834\" data-end=\"5844\">Email:</strong> <a data-start=\"5845\" data-end=\"5865\" class=\"decorated-link cursor-pointer\" rel=\"noopener\">enlisrmisr@gmail.com<span aria-hidden=\"true\" class=\"ms-0.5 inline-block align-middle leading-none\"><svg xmlns=\"http://www.w3.org/2000/svg\" width=\"20\" height=\"20\" aria-hidden=\"true\" data-rtl-flip=\"\" class=\"block h-[0.75em] w-[0.75em] stroke-current stroke-[0.75]\"><use fill=\"currentColor\"></use></svg></span></a><br data-start=\"5865\" data-end=\"5868\">\r\n<strong data-start=\"5868\" data-end=\"5880\">Address:</strong><br data-start=\"5880\" data-end=\"5883\">\r\n455 Horria Road<br data-start=\"5898\" data-end=\"5901\">\r\nAlexandria, Egypt</p>', 1, NULL, '2026-03-20 06:29:24'),
(48, 'currency_model', 'multi_currency', 1, NULL, NULL),
(49, 'social_login', '[{\"login_medium\":\"google\",\"client_id\":\"786488962854-iba0ck7s7jnp49pj811a1k8mhsfroj8n.apps.googleusercontent.com\",\"client_secret\":\"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\",\"status\":1},{\"login_medium\":\"facebook\",\"client_id\":\"864655833201446\",\"client_secret\":\"1c8166fad82596ab9a43e2d2d3c4d03a\",\"status\":1}]', 1, NULL, '2026-01-12 14:58:25'),
(50, 'digital_payment', '{\"status\":\"1\"}', 1, NULL, '2025-02-04 03:59:07'),
(51, 'phone_verification', '', 1, NULL, '2026-03-23 11:00:13'),
(52, 'email_verification', '', 1, NULL, '2026-03-23 11:00:13'),
(53, 'order_verification', '1', 1, NULL, '2026-02-23 19:14:40'),
(54, 'country_code', 'EG', 1, NULL, '2026-03-23 11:00:13'),
(55, 'pagination_limit', '100', 1, NULL, '2026-03-23 11:00:13'),
(56, 'shipping_method', 'inhouse_shipping', 1, NULL, '2026-02-18 08:02:15'),
(59, 'forgot_password_verification', 'email', 1, NULL, NULL),
(61, 'stock_limit', '10', 1, NULL, '2026-03-29 14:32:13'),
(64, 'announcement', '{\"status\":\"0\",\"color\":\"#000000\",\"text_color\":\"#ffffff\",\"announcement\":\"Announcement test\"}', 1, NULL, '2026-02-23 20:47:46'),
(65, 'fawry_pay', '{\"status\":0,\"merchant_code\":\"\",\"security_key\":\"\"}', 1, NULL, '2022-01-18 09:46:30'),
(66, 'recaptcha', '{\"status\":\"1\",\"site_key\":\"\",\"secret_key\":\"\"}', 1, NULL, '2025-12-09 08:11:06'),
(67, 'seller_pos', '0', 1, NULL, NULL),
(70, 'refund_day_limit', '14', 1, NULL, '2026-02-23 19:14:40'),
(71, 'business_mode', 'single', 1, NULL, '2026-03-23 11:00:13'),
(72, 'mail_config_sendgrid', '{\"status\":0,\"name\":\"\",\"host\":\"\",\"driver\":\"\",\"port\":\"\",\"username\":\"\",\"email_id\":\"\",\"encryption\":\"\",\"password\":\"\"}', 1, NULL, NULL),
(73, 'decimal_point_settings', '2', 1, NULL, '2026-03-23 11:00:13'),
(74, 'shop_address', '{\"en\":\"45 horia rood\",\"ar\":\"45 \\u0627\\u0644\\u062d\\u0631\\u064a\\u0629\"}', 1, NULL, '2026-03-23 11:00:13'),
(75, 'billing_input_by_customer', '1', 1, NULL, '2026-02-23 19:14:40'),
(76, 'wallet_status', '1', 1, NULL, '2026-02-21 12:13:50'),
(77, 'loyalty_point_status', '1', 1, NULL, '2026-02-21 12:13:50'),
(78, 'wallet_add_refund', '1', 1, NULL, '2026-02-21 12:13:50'),
(79, 'loyalty_point_exchange_rate', '1', 1, NULL, '2026-02-21 12:13:50'),
(80, 'loyalty_point_item_purchase_point', '10', 1, NULL, '2026-02-21 12:13:50'),
(81, 'loyalty_point_minimum_point', '1000', 1, NULL, '2026-02-21 12:13:50'),
(82, 'minimum_order_limit', '1', 1, NULL, NULL),
(83, 'product_brand', '1', 1, NULL, '2026-03-29 14:32:13'),
(84, 'digital_product', '1', 1, NULL, NULL),
(85, 'delivery_boy_expected_delivery_date_message', '{\"status\":0,\"message\":\"\"}', 1, NULL, NULL),
(86, 'order_canceled', '{\"status\":0,\"message\":\"\"}', 1, NULL, NULL),
(87, 'refund-policy', '{\"status\":\"1\",\"content\":\"<h1 data-path-to-node=\\\"1\\\">Refund Policy<\\/h1><p data-path-to-node=\\\"2\\\"><b data-path-to-node=\\\"2\\\" data-index-in-node=\\\"0\\\">elnisr.online<\\/b>\\r\\n<b data-path-to-node=\\\"2\\\" data-index-in-node=\\\"14\\\">Effective Date:<\\/b> January 8, 2026<\\/p><p data-path-to-node=\\\"3\\\">Thank you for choosing <b data-path-to-node=\\\"3\\\" data-index-in-node=\\\"23\\\">elnisr.online<\\/b>. We value your satisfaction and aim to provide a fair and transparent refund process for all our customers.<\\/p><hr data-path-to-node=\\\"4\\\"><h4 data-path-to-node=\\\"5\\\">1. Eligibility for Refunds<\\/h4><p data-path-to-node=\\\"6\\\">To be eligible for a refund, the following conditions must be met:<\\/p><ul data-path-to-node=\\\"7\\\"><li><p data-path-to-node=\\\"7,0,0\\\"><b data-path-to-node=\\\"7,0,0\\\" data-index-in-node=\\\"0\\\">Digital Services:<\\/b> Requests must be submitted within <b data-path-to-node=\\\"7,0,0\\\" data-index-in-node=\\\"52\\\">[e.g., 14 days]<\\/b> of the purchase date, provided that the service has not been fully utilized or downloaded.<\\/p><\\/li><li><p data-path-to-node=\\\"7,1,0\\\"><b data-path-to-node=\\\"7,1,0\\\" data-index-in-node=\\\"0\\\">Physical Products:<\\/b> Items must be returned in their original packaging, unused, and in the same condition as received.<\\/p><\\/li><li><p data-path-to-node=\\\"7,2,0\\\"><b data-path-to-node=\\\"7,2,0\\\" data-index-in-node=\\\"0\\\">Proof of Purchase:<\\/b> A valid receipt or order number must be provided.<\\/p><\\/li><\\/ul><h4 data-path-to-node=\\\"8\\\">2. Non-Refundable Items<\\/h4><p data-path-to-node=\\\"9\\\">Certain types of purchases are exempt from being refunded:<\\/p><ul data-path-to-node=\\\"10\\\"><li><p data-path-to-node=\\\"10,0,0\\\">Services that have been fully completed or delivered.<\\/p><\\/li><li><p data-path-to-node=\\\"10,1,0\\\">Customized or personalized products made specifically to your specifications.<\\/p><\\/li><li><p data-path-to-node=\\\"10,2,0\\\">Downloadable software products once the license key has been revealed or activated.<\\/p><\\/li><li><p data-path-to-node=\\\"10,3,0\\\">Subscription fees after the initial trial period (if applicable).<\\/p><\\/li><\\/ul><h4 data-path-to-node=\\\"11\\\">3. Refund Process<\\/h4><ol start=\\\"1\\\" data-path-to-node=\\\"12\\\"><li><p data-path-to-node=\\\"12,0,0\\\"><b data-path-to-node=\\\"12,0,0\\\" data-index-in-node=\\\"0\\\">Request:<\\/b> Email us at <b data-path-to-node=\\\"12,0,0\\\" data-index-in-node=\\\"21\\\">enlisrmisr@gmail.com<\\/b> with your order number and the reason for the refund.<\\/p><\\/li><li><p data-path-to-node=\\\"12,1,0\\\"><b data-path-to-node=\\\"12,1,0\\\" data-index-in-node=\\\"0\\\">Review:<\\/b> Our team will review your request within <b data-path-to-node=\\\"12,1,0\\\" data-index-in-node=\\\"49\\\">[e.g., 3-5 business days]<\\/b>.<\\/p><\\/li><li><p data-path-to-node=\\\"12,2,0\\\"><b data-path-to-node=\\\"12,2,0\\\" data-index-in-node=\\\"0\\\">Approval:<\\/b> If approved, your refund will be processed, and a credit will automatically be applied to your original method of payment.<\\/p><\\/li><\\/ol><h4 data-path-to-node=\\\"13\\\">4. Late or Missing Refunds<\\/h4><p data-path-to-node=\\\"14\\\">If you haven\\u2019t received a refund yet:<\\/p><ul data-path-to-node=\\\"15\\\"><li><p data-path-to-node=\\\"15,0,0\\\">Check your bank account again.<\\/p><\\/li><li><p data-path-to-node=\\\"15,1,0\\\">Contact your credit card company; it may take some time before your refund is officially posted.<\\/p><\\/li><li><p data-path-to-node=\\\"15,2,0\\\">If you\\u2019ve done all of this and still have not received your refund, please contact us.<\\/p><\\/li><\\/ul><h4 data-path-to-node=\\\"16\\\">5. Shipping Costs (For Physical Goods)<\\/h4><p data-path-to-node=\\\"17\\\">You will be responsible for paying for your own shipping costs for returning your item. Shipping costs are non-refundable.<\\/p>\"}', 1, NULL, '2026-03-20 10:23:20'),
(88, 'return-policy', '{\"status\":\"1\",\"content\":\"<h1 data-path-to-node=\\\"1\\\">Return Policy<\\/h1><h5 class=\\\"mb-0 text-capitalize\\\" style=\\\"color: rgb(51, 66, 87);\\\"><p data-path-to-node=\\\"2\\\"><b data-path-to-node=\\\"2\\\" data-index-in-node=\\\"0\\\">elnisr.online<\\/b>\\r\\n<b data-path-to-node=\\\"2\\\" data-index-in-node=\\\"14\\\">Effective Date:<\\/b> January 8, 2026<\\/p><p data-path-to-node=\\\"3\\\">At <b data-path-to-node=\\\"3\\\" data-index-in-node=\\\"3\\\">elnisr.online<\\/b>, we want you to be completely satisfied with your purchase. If you are not happy with your physical product, we are here to help you with the return process.<\\/p><hr data-path-to-node=\\\"4\\\"><\\/h5><h4 data-path-to-node=\\\"5\\\">1. Return Window<\\/h4><h5 class=\\\"mb-0 text-capitalize\\\" style=\\\"color: rgb(51, 66, 87);\\\"><p data-path-to-node=\\\"6\\\">You have <b data-path-to-node=\\\"6\\\" data-index-in-node=\\\"9\\\">[e.g., 30 days]<\\/b> from the date you received your item to request a return. If this period has passed, we unfortunately cannot offer you a return or exchange.<\\/p><\\/h5><h4 data-path-to-node=\\\"7\\\">2. Condition of Items<\\/h4><h5 class=\\\"mb-0 text-capitalize\\\" style=\\\"color: rgb(51, 66, 87);\\\"><p data-path-to-node=\\\"8\\\">To be eligible for a return, your item must meet the following criteria:<\\/p><ul data-path-to-node=\\\"9\\\"><li><p data-path-to-node=\\\"9,0,0\\\">The item must be <b data-path-to-node=\\\"9,0,0\\\" data-index-in-node=\\\"17\\\">unused<\\/b> and in the same condition that you received it.<\\/p><\\/li><li><p data-path-to-node=\\\"9,1,0\\\">It must be in the <b data-path-to-node=\\\"9,1,0\\\" data-index-in-node=\\\"18\\\">original packaging<\\/b>.<\\/p><\\/li><li><p data-path-to-node=\\\"9,2,0\\\">All tags, manuals, and accessories must be included.<\\/p><\\/li><li><p data-path-to-node=\\\"9,3,0\\\">Any \\\"tamper-evident\\\" seals must be intact.<\\/p><\\/li><\\/ul><\\/h5><h4 data-path-to-node=\\\"10\\\">3. Non-Returnable Items<\\/h4><h5 class=\\\"mb-0 text-capitalize\\\" style=\\\"color: rgb(51, 66, 87);\\\"><p data-path-to-node=\\\"11\\\">The following items cannot be returned:<\\/p><ul data-path-to-node=\\\"12\\\"><li><p data-path-to-node=\\\"12,0,0\\\"><b data-path-to-node=\\\"12,0,0\\\" data-index-in-node=\\\"0\\\">Digital downloads<\\/b> or software licenses.<\\/p><\\/li><li><p data-path-to-node=\\\"12,1,0\\\"><b data-path-to-node=\\\"12,1,0\\\" data-index-in-node=\\\"0\\\">Perishable goods<\\/b> (if applicable).<\\/p><\\/li><li><p data-path-to-node=\\\"12,2,0\\\"><b data-path-to-node=\\\"12,2,0\\\" data-index-in-node=\\\"0\\\">Personal care<\\/b> or hygiene products.<\\/p><\\/li><li><p data-path-to-node=\\\"12,3,0\\\"><b data-path-to-node=\\\"12,3,0\\\" data-index-in-node=\\\"0\\\">Custom-made<\\/b> or personalized orders.<\\/p><\\/li><\\/ul><\\/h5><h4 data-path-to-node=\\\"13\\\">4. How to Start a Return<\\/h4><h5 class=\\\"mb-0 text-capitalize\\\" style=\\\"color: rgb(51, 66, 87);\\\"><ol start=\\\"1\\\" data-path-to-node=\\\"14\\\"><li><p data-path-to-node=\\\"14,0,0\\\"><b data-path-to-node=\\\"14,0,0\\\" data-index-in-node=\\\"0\\\">Contact Support:<\\/b> Send an email to <b data-path-to-node=\\\"14,0,0\\\" data-index-in-node=\\\"34\\\">enlisrmisr@gmail.com<\\/b> with your subject line as \\\"Return Request - [Order Number]\\\".<\\/p><\\/li><li><p data-path-to-node=\\\"14,1,0\\\"><b data-path-to-node=\\\"14,1,0\\\" data-index-in-node=\\\"0\\\">Authorization:<\\/b> Once we review your request, we will send you a <b data-path-to-node=\\\"14,1,0\\\" data-index-in-node=\\\"63\\\">Return Authorization<\\/b> and instructions on where to send your package.<\\/p><\\/li><li><p data-path-to-node=\\\"14,2,0\\\"><b data-path-to-node=\\\"14,2,0\\\" data-index-in-node=\\\"0\\\">Shipping:<\\/b> Please do not send your purchase back to the manufacturer. Use the address provided by our support team.<\\/p><\\/li><\\/ol><\\/h5><h4 data-path-to-node=\\\"15\\\">5. Exchanges<\\/h4><h5 class=\\\"mb-0 text-capitalize\\\" style=\\\"color: rgb(51, 66, 87);\\\"><p data-path-to-node=\\\"16\\\">We only replace items if they are defective or damaged upon arrival. If you need to exchange it for the same item, please mention this in your email.<\\/p><\\/h5><h4 data-path-to-node=\\\"17\\\">6. Return Shipping<\\/h4><h5 class=\\\"mb-0 text-capitalize\\\" style=\\\"color: rgb(51, 66, 87);\\\"><ul data-path-to-node=\\\"18\\\"><li><p data-path-to-node=\\\"18,0,0\\\">You will be responsible for paying for your own shipping costs for returning your item.<\\/p><\\/li><li><p data-path-to-node=\\\"18,1,0\\\">Shipping costs are non-refundable.<\\/p><\\/li><li><p data-path-to-node=\\\"18,2,0\\\">We recommend using a trackable shipping service or purchasing shipping insurance, as we cannot guarantee that we will receive your returned item.<\\/p><\\/li><\\/ul><\\/h5>\"}', 1, NULL, '2026-03-20 10:23:35'),
(89, 'cancellation-policy', '{\"status\":\"1\",\"content\":\"<h1 data-path-to-node=\\\"1\\\">Cancellation Policy<\\/h1><h1 data-path-to-node=\\\"1\\\"><p data-path-to-node=\\\"2\\\"><b data-path-to-node=\\\"2\\\" data-index-in-node=\\\"0\\\">elnisr.online<\\/b>\\r\\n<b data-path-to-node=\\\"2\\\" data-index-in-node=\\\"14\\\">Effective Date:<\\/b> January 8, 2026<\\/p><p data-path-to-node=\\\"3\\\">At <b data-path-to-node=\\\"3\\\" data-index-in-node=\\\"3\\\">elnisr.online<\\/b>, we understand that plans can change. Our Cancellation Policy is designed to be fair to both our clients and our service providers.<\\/p><hr data-path-to-node=\\\"4\\\"><\\/h1><h4 data-path-to-node=\\\"5\\\">1. Service Cancellations<\\/h4><h1 data-path-to-node=\\\"1\\\"><ul data-path-to-node=\\\"6\\\"><li><p data-path-to-node=\\\"6,0,0\\\"><b data-path-to-node=\\\"6,0,0\\\" data-index-in-node=\\\"0\\\">Before Processing:<\\/b> You may cancel any service order within <b data-path-to-node=\\\"6,0,0\\\" data-index-in-node=\\\"59\\\">[e.g., 24 hours]<\\/b> of purchase for a full refund, provided work has not yet commenced.<\\/p><\\/li><li><p data-path-to-node=\\\"6,1,0\\\"><b data-path-to-node=\\\"6,1,0\\\" data-index-in-node=\\\"0\\\">Ongoing Projects:<\\/b> For long-term projects or milestone-based services, cancellations made after work has started will be subject to a fee covering the work already completed.<\\/p><\\/li><li><p data-path-to-node=\\\"6,2,0\\\"><b data-path-to-node=\\\"6,2,0\\\" data-index-in-node=\\\"0\\\">Subscription Services:<\\/b> You may cancel your subscription at any time. Your access will remain active until the end of your current billing cycle, and no further charges will be applied.<\\/p><\\/li><\\/ul><\\/h1><h4 data-path-to-node=\\\"7\\\">2. Order Cancellations (Physical Goods)<\\/h4><h1 data-path-to-node=\\\"1\\\"><ul data-path-to-node=\\\"8\\\"><li><p data-path-to-node=\\\"8,0,0\\\"><b data-path-to-node=\\\"8,0,0\\\" data-index-in-node=\\\"0\\\">Before Shipping:<\\/b> You can cancel your order for physical products at any time before the status changes to \\\"Shipped\\\" or \\\"Dispatched.\\\"<\\/p><\\/li><li><p data-path-to-node=\\\"8,1,0\\\"><b data-path-to-node=\\\"8,1,0\\\" data-index-in-node=\\\"0\\\">After Shipping:<\\/b> Once an item has left our warehouse, the order cannot be canceled. In this case, please follow our <b data-path-to-node=\\\"8,1,0\\\" data-index-in-node=\\\"115\\\">Return Policy<\\/b> once the item arrives.<\\/p><\\/li><\\/ul><\\/h1><h4 data-path-to-node=\\\"9\\\">3. How to Cancel<\\/h4><h1 data-path-to-node=\\\"1\\\"><p data-path-to-node=\\\"10\\\">To request a cancellation, please use one of the following methods:<\\/p><ol start=\\\"1\\\" data-path-to-node=\\\"11\\\"><li><p data-path-to-node=\\\"11,0,0\\\"><b data-path-to-node=\\\"11,0,0\\\" data-index-in-node=\\\"0\\\">Dashboard:<\\/b> Log in to your account, go to \\\"My Orders,\\\" and select \\\"Cancel Order\\\" (if the option is available).<\\/p><\\/li><li><p data-path-to-node=\\\"11,1,0\\\"><b data-path-to-node=\\\"11,1,0\\\" data-index-in-node=\\\"0\\\">Email:<\\/b> Send a request to <b data-path-to-node=\\\"11,1,0\\\" data-index-in-node=\\\"25\\\">enlisrmisr@gmail.com<\\/b> with the subject: \\\"Cancellation Request - [Order Number]\\\".<\\/p><\\/li><\\/ol><\\/h1><h4 data-path-to-node=\\\"12\\\">4. Refund Timeline<\\/h4><h1 data-path-to-node=\\\"1\\\"><p data-path-to-node=\\\"13\\\">Once a cancellation is confirmed:<\\/p><ul data-path-to-node=\\\"14\\\"><li><p data-path-to-node=\\\"14,0,0\\\">Refunds are processed within <b data-path-to-node=\\\"14,0,0\\\" data-index-in-node=\\\"29\\\">[e.g., 5-7 business days]<\\/b>.<\\/p><\\/li><li><p data-path-to-node=\\\"14,1,0\\\">The funds will be returned to the original payment method used during checkout.<\\/p><\\/li><\\/ul><\\/h1>\"}', 1, NULL, '2026-03-20 10:23:41'),
(90, 'offline_payment', '{\"status\":\"1\"}', 1, NULL, '2025-02-04 03:59:07'),
(91, 'temporary_close', '{\"status\":0}', 1, NULL, '2023-03-04 06:25:36'),
(92, 'vacation_add', '{\"status\":0,\"vacation_start_date\":null,\"vacation_end_date\":null,\"vacation_note\":null}', 1, NULL, '2023-03-04 06:25:36');
INSERT INTO `business_settings` (`id`, `type`, `value`, `is_active`, `created_at`, `updated_at`) VALUES
(93, 'cookie_setting', '{\"status\":0,\"cookie_text\":\"Cookies Policy\\r\\nelnisr.online\\r\\nEffective Date: January 8, 2026\\r\\n\\r\\nThis Cookies Policy explains how elnisr.online (\\\"we,\\\" \\\"us,\\\" or \\\"our\\\") uses cookies and similar technologies to recognize you when you visit our website. It explains what these technologies are and why we use them, as well as your rights to control our use of them.\\r\\n\\r\\n1. What are Cookies?\\r\\nCookies are small data files that are placed on your computer or mobile device when you visit a website. They are widely used by website owners to make their websites work, or to work more efficiently, as well as to provide reporting information.\\r\\n\\r\\n2. Why do we use Cookies?\\r\\nWe use first-party and third-party cookies for several reasons:\\r\\n\\r\\nEssential Cookies: These are strictly necessary to provide you with services available through our website (e.g., access to secure areas or keeping your battery cart saved).\\r\\n\\r\\nPerformance & Analytics: These cookies help us understand how visitors use our site (e.g., which battery models are most viewed) so we can improve the experience.\\r\\n\\r\\nFunctionality: These allow the site to remember choices you make (such as your language preference).\\r\\n\\r\\nAdvertising: These cookies are used to make advertising messages more relevant to you and your interests regarding automotive products.\\r\\n\\r\\n3. Specific Cookies We Use\\r\\nSession Cookies: Temporary cookies that expire when you close your browser.\\r\\n\\r\\nPersistent Cookies: These stay on your device for a set period to remember your preferences for future visits.\\r\\n\\r\\n4. How can I control Cookies?\\r\\nYou have the right to decide whether to accept or reject cookies.\\r\\n\\r\\nBrowser Controls: You can set or amend your web browser controls to accept or refuse cookies. If you choose to reject cookies, you may still use our website, though your access to some functionality and areas may be restricted.\\r\\n\\r\\nOpt-out Tools: You can manage preferences for targeted advertising through settings in your account or via third-party opt-out portals.\\r\\n\\r\\n5. Updates to this Policy\\r\\nWe may update this Cookies Policy from time to time to reflect changes in the cookies we use for operational, legal, or regulatory reasons.\"}', 1, NULL, '2026-02-22 23:16:58'),
(94, 'maximum_otp_hit', '0', 1, NULL, '2023-06-13 13:04:49'),
(95, 'otp_resend_time', '0', 1, NULL, '2023-06-13 13:04:49'),
(96, 'temporary_block_time', '0', 1, NULL, '2023-06-13 13:04:49'),
(97, 'maximum_login_hit', '0', 1, NULL, '2023-06-13 13:04:49'),
(98, 'temporary_login_block_time', '0', 1, NULL, '2023-06-13 13:04:49'),
(104, 'apple_login', '[{\"login_medium\":\"apple\",\"client_id\":\"\",\"client_secret\":\"\",\"status\":1,\"team_id\":\"\",\"key_id\":\"\",\"service_file\":\"\",\"redirect_url\":\"\"}]', 1, NULL, '2024-10-27 08:14:24'),
(105, 'ref_earning_status', '1', 1, NULL, '2026-02-21 12:13:50'),
(106, 'ref_earning_exchange_rate', '100', 1, NULL, '2026-02-21 12:13:50'),
(107, 'guest_checkout', '1', 1, NULL, '2026-02-23 19:14:40'),
(108, 'minimum_order_amount', '0', 1, NULL, '2023-10-13 11:34:53'),
(109, 'minimum_order_amount_by_seller', '0', 1, NULL, '2023-10-13 11:34:53'),
(110, 'minimum_order_amount_status', '0', 1, NULL, '2026-02-23 19:14:40'),
(111, 'admin_login_url', 'admin', 1, NULL, '2023-10-13 11:34:53'),
(112, 'employee_login_url', 'employee', 1, NULL, '2023-10-13 11:34:53'),
(113, 'free_delivery_status', '0', 1, NULL, '2026-02-23 19:14:40'),
(114, 'free_delivery_responsibility', 'admin', 1, NULL, '2026-02-23 19:14:40'),
(115, 'free_delivery_over_amount', '0', 1, NULL, '2023-10-13 11:34:53'),
(116, 'free_delivery_over_amount_seller', '0', 1, NULL, '2026-02-23 19:14:40'),
(117, 'add_funds_to_wallet', '1', 1, NULL, '2026-02-21 12:13:50'),
(118, 'minimum_add_fund_amount', '1', 1, NULL, '2026-02-21 12:13:50'),
(119, 'maximum_add_fund_amount', '10000', 1, NULL, '2026-02-21 12:13:50'),
(120, 'user_app_version_control', '{\"for_android\":{\"status\":1,\"version\":\"14.1\",\"link\":\"\"},\"for_ios\":{\"status\":1,\"version\":\"14.1\",\"link\":\"\"}}', 1, NULL, '2023-10-13 11:34:53'),
(121, 'seller_app_version_control', '{\"for_android\":{\"status\":1,\"version\":\"14.1\",\"link\":\"\"},\"for_ios\":{\"status\":1,\"version\":\"14.1\",\"link\":\"\"}}', 1, NULL, '2023-10-13 11:34:53'),
(122, 'delivery_man_app_version_control', '{\"for_android\":{\"status\":1,\"version\":\"14.1\",\"link\":\"\"},\"for_ios\":{\"status\":1,\"version\":\"14.1\",\"link\":\"\"}}', 1, NULL, '2023-10-13 11:34:53'),
(123, 'whatsapp', '{\"status\":\"1\",\"phone\":\"00201101017310\"}', 1, NULL, '2026-02-20 20:35:07'),
(124, 'currency_symbol_position', 'right', 1, NULL, '2026-03-23 11:00:13'),
(148, 'company_reliability', '[{\"item\":\"delivery_info\",\"title\":\"Fast Delivery all across the country\",\"image\":\"\",\"status\":\"1\"},{\"item\":\"safe_payment\",\"title\":\"Safe Payment\",\"image\":\"\",\"status\":\"1\"},{\"item\":\"return_policy\",\"title\":\"7 Days Return Policy\",\"image\":\"\",\"status\":\"1\"},{\"item\":\"authentic_product\",\"title\":\"100% Authentic Products\",\"image\":\"\",\"status\":\"1\"}]', 1, NULL, '2025-07-07 03:58:49'),
(149, 'react_setup', '{\"status\":0,\"react_license_code\":\"\",\"react_domain\":\"\",\"react_platform\":\"\"}', 1, NULL, '2024-01-09 04:05:15'),
(150, 'app_activation', '{\"software_id\":\"\",\"is_active\":0}', 1, NULL, '2024-01-09 04:05:15'),
(151, 'shop_banner', '', 1, NULL, '2023-10-13 11:34:53'),
(152, 'map_api_status', '1', 1, NULL, '2025-01-06 07:25:39'),
(153, 'vendor_registration_header', '{\"title\":\"Vendor Registration\",\"sub_title\":\"Create your Shop. and have alredy one?\",\"image\":{\"image_name\":null,\"storage\":\"public\"}}', 1, NULL, '2025-05-24 03:54:38'),
(154, 'vendor_registration_sell_with_us', '{\"title\":\"Why Sell With Us\",\"sub_title\":\"Why Sell With Us\",\"image\":{\"image_name\":null,\"storage\":\"public\"}}', 1, NULL, '2025-05-24 04:15:09'),
(155, 'download_vendor_app', '{\"title\":\"Download\",\"sub_title\":\"Download our free app and start reaching millions of buyers on the go! Easy setup, manage listings, and boost sales anywhere.\",\"image\":{\"image_name\":null,\"storage\":\"public\"},\"download_google_app\":null,\"download_google_app_status\":0,\"download_apple_app\":null,\"download_apple_app_status\":0}', 1, NULL, '2025-05-24 04:28:05'),
(156, 'business_process_main_section', '{\"title\":\"3 Easy Steps To Start Sell\",\"sub_title\":\"Start biyingquickly! Register, upload your products with detailed info and images, and reach millions of buyers instantly.\"}', 1, NULL, '2025-05-24 06:23:47'),
(157, 'business_process_step', '[{\"title\":\"Get Registered\",\"description\":\"Sign up easily and create your wholesaler account in just a few minutes. It fast and simple to get started.\",\"image\":{\"image_name\":null,\"storage\":\"public\"}},{\"title\":\"Upload Products\",\"description\":\"List your products with detailed descriptions and high-quality images to attract more buyers effortlessly.\",\"image\":{\"image_name\":null,\"storage\":\"public\"}},{\"title\":\"Start Selling\",\"description\":\"Go live and start reaching millions of potential buyers immediately. Watch your sales grow with our vast audience.\",\"image\":{\"image_name\":null,\"storage\":\"public\"}}]', 1, NULL, '2025-05-24 06:23:48'),
(158, 'brand_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(159, 'category_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(160, 'vendor_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(161, 'flash_deal_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(162, 'featured_product_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(163, 'feature_deal_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(164, 'new_arrival_product_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(165, 'top_vendor_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(166, 'category_wise_product_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(167, 'top_rated_product_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(168, 'best_selling_product_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(169, 'searched_product_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(170, 'vendor_product_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(171, 'storage_connection_type', 'public', 1, '2024-09-24 07:52:17', '2024-09-24 07:52:17'),
(172, 'google_search_console_code', '', 1, '2024-09-24 07:52:17', '2024-09-24 07:52:17'),
(173, 'bing_webmaster_code', '', 1, '2024-09-24 07:52:17', '2024-09-24 07:52:17'),
(174, 'baidu_webmaster_code', '', 1, '2024-09-24 07:52:17', '2024-09-24 07:52:17'),
(175, 'yandex_webmaster_code', '', 1, '2024-09-24 07:52:17', '2024-09-24 07:52:17'),
(176, 'firebase_otp_verification', '{\"status\":0,\"web_api_key\":\"\"}', 1, '2024-09-24 07:52:17', '2025-10-31 06:43:19'),
(177, 'maintenance_system_setup', '{\"user_app\":\"1\",\"user_website\":\"1\",\"vendor_app\":\"1\",\"deliveryman_app\":\"1\",\"vendor_panel\":\"1\"}', 1, '2024-09-24 07:52:17', '2025-07-04 06:42:13'),
(178, 'maintenance_duration_setup', '{\"maintenance_duration\":\"until_change\",\"start_date\":null,\"end_date\":null}', 1, NULL, '2025-07-04 06:42:13'),
(179, 'maintenance_message_setup', '{\"business_number\":1,\"business_email\":1,\"maintenance_message\":\"We are Working On Something Special\",\"message_body\":\"We apologize for any inconvenience. For immediate assistance, please contact with our support team\"}', 1, NULL, '2025-07-04 06:42:13'),
(180, 'shipping-policy', '{\"status\":0,\"content\":\"<h3 data-path-to-node=\\\"0\\\">1. Shipping Policy (English)<\\/h3><h1 data-path-to-node=\\\"1\\\">Shipping Policy<\\/h1><p data-path-to-node=\\\"2\\\"><b data-path-to-node=\\\"2\\\" data-index-in-node=\\\"0\\\">elnisr.online<\\/b> <b data-path-to-node=\\\"2\\\" data-index-in-node=\\\"14\\\">Effective Date:<\\/b> January 8, 2026<\\/p><p data-path-to-node=\\\"3\\\">Thank you for visiting and shopping at <b data-path-to-node=\\\"3\\\" data-index-in-node=\\\"39\\\">elnisr.online<\\/b>. The following are the terms and conditions that constitute our Shipping Policy.<\\/p><hr data-path-to-node=\\\"4\\\"><h4 data-path-to-node=\\\"5\\\">1. Shipment Processing Time<\\/h4><p data-path-to-node=\\\"6\\\">All orders are processed within <b data-path-to-node=\\\"6\\\" data-index-in-node=\\\"32\\\">[e.g., 1\\u20133 business days]<\\/b>. Orders are not shipped or delivered on weekends or holidays. If we are experiencing a high volume of orders, shipments may be delayed by a few days. Please allow additional days in transit for delivery.<\\/p><h4 data-path-to-node=\\\"7\\\">2. Shipping Rates &amp; Delivery Estimates<\\/h4><p data-path-to-node=\\\"8\\\">Shipping charges for your order will be calculated and displayed at checkout.<\\/p><ul data-path-to-node=\\\"9\\\"><li><p data-path-to-node=\\\"9,0,0\\\"><b data-path-to-node=\\\"9,0,0\\\" data-index-in-node=\\\"0\\\">Standard Shipping:<\\/b> [e.g., 3\\u20135 business days] \\u2014 Cost: [e.g., $5.00]<\\/p><\\/li><li><p data-path-to-node=\\\"9,1,0\\\"><b data-path-to-node=\\\"9,1,0\\\" data-index-in-node=\\\"0\\\">Express Shipping:<\\/b> [e.g., 1\\u20132 business days] \\u2014 Cost: [e.g., $15.00]<\\/p><\\/li><li><p data-path-to-node=\\\"9,2,0\\\"><b data-path-to-node=\\\"9,2,0\\\" data-index-in-node=\\\"0\\\">Local Delivery (Alexandria):<\\/b> [e.g., Same day or next day] \\u2014 Cost: [e.g., Free over $50]<\\/p><\\/li><\\/ul><h4 data-path-to-node=\\\"10\\\">3. Shipment Confirmation &amp; Order Tracking<\\/h4><p data-path-to-node=\\\"11\\\">You will receive a Shipment Confirmation email once your order has shipped containing your tracking number(s). The tracking number will be active within 24 hours.<\\/p><h4 data-path-to-node=\\\"12\\\">4. Customs, Duties, and Taxes<\\/h4><p data-path-to-node=\\\"13\\\"><b data-path-to-node=\\\"13\\\" data-index-in-node=\\\"0\\\">elnisr.online<\\/b> is not responsible for any customs and taxes applied to your order. All fees imposed during or after shipping are the responsibility of the customer (tariffs, taxes, etc.).<\\/p><h4 data-path-to-node=\\\"14\\\">5. Damages<\\/h4><p data-path-to-node=\\\"15\\\"><b data-path-to-node=\\\"15\\\" data-index-in-node=\\\"0\\\">elnisr.online<\\/b> is not liable for any products damaged or lost during shipping. If you received your order damaged, please contact the shipment carrier to file a claim. Please save all packaging materials and damaged goods before filing a claim.<\\/p><h4 data-path-to-node=\\\"16\\\">6. International Shipping Policy<\\/h4><p data-path-to-node=\\\"17\\\">We currently ship to [Insert Countries]. Shipping rates and delivery times for international orders vary by location and will be calculated at checkout.<\\/p>\"}', 1, '2024-09-24 07:52:17', '2026-03-20 10:23:46'),
(181, 'vendor_forgot_password_method', 'phone', 1, '2024-10-27 08:14:24', '2024-10-27 08:14:24'),
(182, 'deliveryman_forgot_password_method', 'phone', 1, '2024-10-27 08:14:24', '2024-10-27 08:14:24'),
(183, 'timezone', 'Africa/Cairo', 1, NULL, '2026-03-23 11:00:13'),
(184, 'default_location', '{\"lat\":\"31.244903927533166\",\"lng\":\"29.986584315786555\"}', 1, NULL, '2026-03-23 11:00:13'),
(185, 'map_api_key', 'AIzaSyCy1jPeVRgwq_qYDPy8Jj7OiI49ZmQ-oKs', 1, NULL, '2025-01-06 07:25:39'),
(186, 'map_api_key_server', 'AIzaSyCy1jPeVRgwq_qYDPy8Jj7OiI49ZmQ-oKs', 1, NULL, '2025-01-06 07:25:39'),
(187, 'delivery_country_restriction', '1', 1, NULL, '2026-03-13 20:51:47'),
(188, 'delivery_zip_code_area_restriction', '0', 1, NULL, '2026-02-14 19:52:33'),
(189, 'delivery_area_restriction', '1', 1, NULL, '2026-03-13 20:51:47'),
(190, 'delivery_state_restriction', '1', 1, NULL, '2026-03-13 20:51:47'),
(191, 'delivery_city_restriction', '1', 1, NULL, '2026-03-13 20:51:47'),
(192, 'invoice_settings', '{\"terms_and_condition\":null,\"business_identity\":null,\"business_identity_value\":null,\"image\":null}', 1, NULL, '2025-06-14 04:09:48'),
(193, 'stock_check', '1', 1, NULL, '2026-02-23 19:14:40'),
(194, 'character_trigger_limit_for_autosearch', '3', 1, NULL, '2026-02-23 19:14:40'),
(195, 'maintenance_mode', '0', 1, NULL, '2025-07-04 06:42:13'),
(196, 'wholesaler_registration_header', '{\"title\":\"Wholesaler Registration\",\"sub_title\":\"Create your account and buy bulk products\",\"image\":{\"image_name\":\"2025-06-19-6853d6af70339.webp\",\"storage\":\"public\"}}', 1, NULL, '2025-07-11 10:59:43'),
(197, '', '', 1, NULL, '2025-05-24 03:54:38'),
(198, 'wholesaler_registration_sell_with_us', '{\"title\":\"Advanced Technology\",\"sub_title\":\"Powered by the latest tech for unmatched performance.\",\"image\":{\"image_name\":\"2025-05-24-6831976d17b59.webp\",\"storage\":\"public\"}}', 1, NULL, '2025-07-11 11:02:54'),
(199, 'download_wholesaler_app', '{\"title\":\"Download Free App\",\"sub_title\":\"Download our free app and start reaching millions of buyers on the go! Easy setup, manage listings, and boost sales anywhere.\",\"image\":{\"image_name\":\"2025-05-24-683196e80b5fb.webp\",\"storage\":\"public\"},\"download_google_app\":\"http:\\/\\/127.0.0.1:8000\\/wholesaler\\/auth\\/registration\\/index\",\"download_google_app_status\":\"1\",\"download_apple_app\":\"http:\\/\\/127.0.0.1:8000\\/wholesaler\\/auth\\/registration\\/index\",\"download_apple_app_status\":\"1\"}', 0, NULL, '2025-05-26 13:02:41'),
(200, 'wholesaler_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(201, 'top_wholesaler_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(202, 'wholesaler_product_list_priority', '', 1, '2024-05-18 10:57:03', '2024-05-18 10:57:03'),
(203, 'wholesaler_forgot_password_method', 'phone', 1, '2024-10-27 08:14:24', '2024-10-27 08:14:24'),
(204, 'wholesaler_process_main_section', '{\"title\":\"3 Easy Steps To Start Buy\",\"sub_title\":\"Create your account and buy bulk products\"}', 1, NULL, '2025-07-12 04:07:35'),
(206, 'wholesaler_process_step', '[{\"title\":\"Get Registered\",\"description\":\"Sign up easily and create your wholesaler account in just a few minutes. It fast and simple to get started.\",\"image\":{\"image_name\":null,\"storage\":\"public\"}},{\"title\":\"Upload Products\",\"description\":\"List your products with detailed descriptions and high-quality images to attract more buyers effortlessly.\",\"image\":{\"image_name\":null,\"storage\":\"public\"}},{\"title\":\"Start Selling\",\"description\":\"Go live and start reaching millions of potential buyers immediately. Watch your sales grow with our vast audience.\",\"image\":{\"image_name\":null,\"storage\":\"public\"}}]', 1, NULL, '2025-07-12 04:07:35'),
(207, 'quotation_settings', '{\"image_header\":{\"image_name\":\"2025-06-14-684d201b28c1f.webp\",\"storage\":\"public\"},\"image_bg\":{\"image_name\":\"2025-06-14-684d201b55bfc.webp\",\"storage\":\"public\"},\"image_footer\":{\"image_name\":\"2025-06-14-684d201b64c36.webp\",\"storage\":\"public\"}}', 1, NULL, '2025-06-14 04:57:30'),
(208, 'blog_feature_download_app_title', '{\"en\":\"Download User App\",\"ar\":\"\\u062d\\u0645\\u0644 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642 \\u0645\\u0646 \\u0647\\u0646\\u0627\"}', 1, NULL, '2026-03-22 20:08:33'),
(209, 'blog_feature_download_app_subtitle', '{\"en\":\"All the power of niche in your pocket. Schedule, publish and monitir your accounts with ease.\",\"ar\":\"\\u0643\\u0644 \\u062e\\u062f\\u0645\\u0627\\u062a\\u0646\\u0627 \\u0645\\u0646 \\u062e\\u0644\\u0627\\u0644 \\u0627\\u0644\\u062a\\u0637\\u0628\\u064a\\u0642\"}', 1, NULL, '2026-03-22 20:08:33'),
(210, 'blog_feature_download_google_app_button_status', '1', 1, NULL, '2026-03-22 20:08:33'),
(211, 'blog_feature_download_apple_app_button_status', '1', 1, NULL, '2026-03-22 20:08:33'),
(212, 'blog_feature_download_app_icon', '{\"image_name\":\"2025-06-26-685cdd53d88d2.webp\",\"storage\":\"public\"}', 1, NULL, '2025-06-26 03:10:35'),
(213, 'blog_feature_download_app_background', '{\"image_name\":\"2025-06-26-685cdd53f100c.webp\",\"storage\":\"public\"}', 1, NULL, '2025-06-26 03:10:35'),
(214, 'blog_feature_title', '{\"en\":\"Blog\",\"eg\":null}', 1, NULL, '2025-06-26 05:06:53'),
(215, 'blog_feature_sub_title', '{\"en\":\"Read Our Tranding Blogs\",\"eg\":null}', 1, NULL, '2025-06-26 05:06:53'),
(216, 'blog_feature_download_app_status', '1', 1, NULL, '2025-06-26 08:01:58'),
(218, 'blog_feature_active_status', '1', 1, NULL, '2025-07-04 10:21:07'),
(219, 'services', '1', 1, NULL, '2026-03-29 14:32:13'),
(220, 'service_policy', '<ul class=\"text-muted small mb-2\" style=\"-webkit-font-smoothing: antialiased; list-style-position: initial; list-style-image: initial; padding-left: 20px;\"><li class=\"mb-1\" style=\"color: rgb(125, 135, 156) !important; font-family: &quot;Open Sans&quot;, sans-serif; font-size: 12.8px; -webkit-font-smoothing: antialiased;\"><h1 data-path-to-node=\"2\">Service Policy for elnisr.online</h1><p data-path-to-node=\"3\"><b data-path-to-node=\"3\" data-index-in-node=\"0\">Effective Date:</b> January 8, 2026</p><p data-path-to-node=\"4\"><b data-path-to-node=\"4\" data-index-in-node=\"0\">elnisr.online</b> (\"we,\" \"us,\" or \"our\") is dedicated to providing a high-quality and reliable experience for our users. This Service Policy outlines the terms and conditions regarding how our services are delivered and the mutual responsibilities between us and the user.</p><hr data-path-to-node=\"5\"><h3 data-path-to-node=\"6\">1. Scope of Services</h3><p data-path-to-node=\"7\">We provide a range of [Insert Service Type, e.g., Digital Solutions, E-commerce, Consultancy]. While we strive to ensure information accuracy and 24/7 service availability, services may be subject to periodic updates or technical maintenance.</p><h3 data-path-to-node=\"8\">2. User Eligibility</h3><p data-path-to-node=\"9\">By using our services, you represent and warrant that:</p><ul data-path-to-node=\"10\"><li><p data-path-to-node=\"10,0,0\">You are at least <b data-path-to-node=\"10,0,0\" data-index-in-node=\"17\">18 years of age</b> (or have parental/guardian consent).</p></li><li><p data-path-to-node=\"10,1,0\">You have provided accurate and complete information when creating your account.</p></li><li><p data-path-to-node=\"10,2,0\">You will use the service for lawful purposes only and in compliance with local and international laws.</p></li></ul><h3 data-path-to-node=\"11\">3. Service Delivery Standards</h3><ul data-path-to-node=\"12\"><li><p data-path-to-node=\"12,0,0\"><b data-path-to-node=\"12,0,0\" data-index-in-node=\"0\">Quality:</b> We commit to delivering services in accordance with established professional standards.</p></li><li><p data-path-to-node=\"12,1,0\"><b data-path-to-node=\"12,1,0\" data-index-in-node=\"0\">Timeline:</b> We will make every effort to adhere to the delivery or activation schedules specified at the time of purchase.</p></li><li><p data-path-to-node=\"12,2,0\"><b data-path-to-node=\"12,2,0\" data-index-in-node=\"0\">Support:</b> Technical support is available to users via designated channels (Email/Chat) during official business hours.</p></li></ul><h3 data-path-to-node=\"13\">4. Payments and Fees</h3><ul data-path-to-node=\"14\"><li><p data-path-to-node=\"14,0,0\">All applicable fees must be paid prior to service activation, unless otherwise stated.</p></li><li><p data-path-to-node=\"14,1,0\">All listed prices [include / do not include] applicable taxes.</p></li><li><p data-path-to-node=\"14,2,0\">In the event of late payment, we reserve the right to suspend or terminate access to the service.</p></li></ul><h3 data-path-to-node=\"15\">5. Cancellation and Refund Policy</h3><ul data-path-to-node=\"16\"><li><p data-path-to-node=\"16,0,0\"><b data-path-to-node=\"16,0,0\" data-index-in-node=\"0\">Cancellation:</b> Users may request service cancellation through their dashboard or by contacting support.</p></li><li><p data-path-to-node=\"16,1,0\"><b data-path-to-node=\"16,1,0\" data-index-in-node=\"0\">Refunds:</b> Refund requests are evaluated based on a [Insert Period, e.g., 14-day] window from the date of purchase, provided the service has not been fully consumed or the terms violated.</p></li></ul><h3 data-path-to-node=\"17\">6. Acceptable Use</h3><p data-path-to-node=\"18\">The following actions are strictly prohibited:</p><ul data-path-to-node=\"19\"><li><p data-path-to-node=\"19,0,0\">Using the service to send unsolicited messages (Spam).</p></li><li><p data-path-to-node=\"19,1,0\">Attempting to breach or disable the site\'s infrastructure.</p></li><li><p data-path-to-node=\"19,2,0\">Reselling the service to third parties without prior written consent from us.</p></li></ul><h3 data-path-to-node=\"20\">7. Limitation of Liability</h3><p data-path-to-node=\"21\">While we strive for excellence, <b data-path-to-node=\"21\" data-index-in-node=\"32\">elnisr.online</b> is not liable for:</p><ul data-path-to-node=\"22\"><li><p data-path-to-node=\"22,0,0\">Interruptions caused by third-party internet service providers or Force Majeure.</p></li><li><p data-path-to-node=\"22,1,0\">Data loss resulting from the user\'s misuse of the account.</p></li><li><p data-path-to-node=\"22,2,0\">Indirect or consequential damages arising from the use of the service.</p></li></ul><h3 data-path-to-node=\"23\">8. Policy Amendments</h3><p data-path-to-node=\"24\">We reserve the right to modify this Service Policy at any time. Users will be notified of any material changes via email or through a notice on the website.</p><hr data-path-to-node=\"25\"><h3 data-path-to-node=\"26\">9. Contact Us</h3><p data-path-to-node=\"27\">For any inquiries regarding this Service Policy, please contact:</p><ul data-path-to-node=\"28\"><li><p data-path-to-node=\"28,0,0\"><b data-path-to-node=\"28,0,0\" data-index-in-node=\"0\">Email:</b> [email protected]</p></li><li><p data-path-to-node=\"28,1,0\"><b data-path-to-node=\"28,1,0\" data-index-in-node=\"0\">Address:</b> 455 Horria Road, Alexandria, Egypt</p></li></ul></li></ul>', 1, NULL, '2026-02-22 22:58:58'),
(221, 'warranty_require_otp', '1', 1, NULL, '2026-03-13 18:50:18'),
(222, 'warranty_auto_approve_off_platform', '0', 0, NULL, '2026-03-13 18:50:18'),
(223, 'warranty_agent_email', 'warranty@elnisr.online', 1, NULL, '2026-03-13 18:50:18'),
(224, 'warranty_sla_first_response', '1', 1, NULL, '2026-03-13 18:50:18'),
(225, 'warranty_sla_decision', '3', 1, NULL, '2026-03-13 18:50:18'),
(226, 'warranty_rate_limit', '2', 1, NULL, '2023-06-13 13:04:49'),
(227, 'warranty_enable_public_form', '1', 1, NULL, '2026-03-13 18:50:18'),
(228, 'warranty_captcha_enabled', '1', 1, NULL, '2026-03-13 18:50:18'),
(229, 'warranty_rate_limit_window', '2', 1, NULL, '2026-03-13 18:50:18'),
(230, 'warranty_rate_limit_max_attempts', '5', 1, NULL, '2026-03-13 18:50:18'),
(231, 'warranty_months', '12', 1, NULL, '2026-03-13 18:50:18'),
(232, 'view_token_ttl_minutes', '10', 1, NULL, '2025-10-27 08:43:00'),
(233, 'download_signed_url_ttl_minutes', '5', 1, NULL, '2025-10-27 08:43:00'),
(234, 'fcm_credentials', '{\"apiKey\":\"AIzaSyDtOpInxjGIj2cb9QMjb2atPY9BVVxTHcU\",\"authDomain\":\"buiobites.firebaseapp.com\",\"projectId\":\"buiobites\",\"storageBucket\":\"buiobites.firebasestorage.app\",\"messagingSenderId\":\"1039474110878\",\"appId\":\"1:1039474110878:web:282cf8e806c29509e2a68f\",\"measurementId\":\"G-9RKBHLSSMS\"}', 1, NULL, '2025-10-31 07:05:56'),
(235, 'warranty_activation_days', '7', 1, NULL, '2025-10-31 06:48:32'),
(236, 'ucm_api_config', '{\r\n  \"status\": 1,\r\n  \"host\": \"192.168.1.100\",\r\n  \"port\": \"8089\",\r\n  \"username\": \"api_user\",\r\n  \"password\": \"ApiPass2025!\",\r\n  \"digest\": 1\r\n}', 1, '2020-10-12 10:29:18', '2021-07-06 12:32:01'),
(237, 'currency_symbol_space', '1', 1, NULL, '2026-03-23 11:00:13');
INSERT INTO `business_settings` (`id`, `type`, `value`, `is_active`, `created_at`, `updated_at`) VALUES
(238, 'ui_translation_messages', '{\"find_perfect_match\":\"Find perfect match\",\"shop_by_vehicle_year_make_model\":\"Shop by vehicle year make model\",\"filter_options\":\"Filter options\",\"make\":\"Make\",\"model\":\"Model\",\"model_year\":\"Model year\",\"select_make\":\"Select make\",\"select_model\":\"Select model\",\"select_year\":\"Select year\",\"apply_filters\":\"Apply filters\",\"Read More\":\"Read More\",\"What Our Clients Say\":\"What Our Clients Say\",\"Frequently Asked Questions\":\"Frequently Asked Questions\",\"home\":\"Home\",\"Store\":\"Store\",\"Product\":\"Product\",\"Services\":\"Services\",\"About Us\":\"About Us\",\"Blog\":\"Blog\",\"Career\":\"Career\",\"Contact Us\":\"Contact Us\",\"Home\":\"Home\",\"click_to_view\":\"Click to view\",\"OTP_has_been_sent_again.\":\"OTP has been sent again.\",\"please_wait_for_new_code.\":\"Please wait for new code.\",\"please_check_the_recaptcha.\":\"Please check the recaptcha.\",\"please_ReType_Password\":\"Please ReType Password\",\"password_do_not_match\":\"Password do not match\",\"password_match\":\"Password match\",\"password_Must_Be_6_Character\":\"Password Must Be 6 Character\",\"send_successfully\":\"Send successfully\",\"update_successfully\":\"Update successfully\",\"successfully_copied\":\"Successfully copied\",\"copied_failed\":\"Copied failed\",\"please_select_a_payment_Methods\":\"Please select a payment Methods\",\"please_choose_all_the_options\":\"Please choose all the options\",\"cannot_input_minus_value\":\"Cannot input minus value\",\"all_input_field_required\":\"All input field required\",\"no_data_found\":\"No data found\",\"minimum_order_quantity_cannot_be_less_than_\":\"Minimum order quantity cannot be less than \",\"item_has_been_removed_from_cart\":\"Item has been removed from cart\",\"sorry_stock_limit_exceeded\":\"Sorry stock limit exceeded\",\"sorry_the_minimum_order_quantity_does_not_match\":\"Sorry the minimum order quantity does not match\",\"cart\":\"Cart\",\"at_least_8_characters\":\"At least 8 characters\",\"at_least_one_uppercase_letter_\":\"At least one uppercase letter \",\"at_least_one_number\":\"At least one number\",\"at_least_one_symbol\":\"At least one symbol\",\"about_us\":\"About us\",\"Know_about_our_company_more.\":\"Know about our company more.\",\"contact_Us\":\"Contact Us\",\"We_are_Here_to_Help\":\"We are Here to Help\",\"FAQ\":\"FAQ\",\"Get_all_Answers\":\"Get all Answers\",\"Check_Latest_Blogs\":\"Check Latest Blogs\",\"download_our_app\":\"Download our app\",\"special\":\"Special\",\"flash_deal\":\"Flash deal\",\"featured_products\":\"Featured products\",\"latest_products\":\"Latest products\",\"best_selling_product\":\"Best selling product\",\"top_rated_product\":\"Top rated product\",\"account_&_shipping_info\":\"Account & shipping info\",\"profile_info\":\"Profile info\",\"wish_list\":\"Wish list\",\"track_order\":\"Track order\",\"warranty\":\"Warranty\",\"refund_policy\":\"Refund policy\",\"return_policy\":\"Return policy\",\"cancellation_policy\":\"Cancellation policy\",\"newsletter\":\"Newsletter\",\"subscribe_to_our_new_channel_to_get_latest_updates\":\"Subscribe to our new channel to get latest updates\",\"your_Email_Address\":\"Your Email Address\",\"subscribe\":\"Subscribe\",\"start_a_conversation\":\"Start a conversation\",\"support_ticket\":\"Support ticket\",\"address\":\"Address\",\"terms_&_conditions\":\"Terms & conditions\",\"privacy_policy\":\"Privacy policy\",\"Out_of_stock\":\"Out of stock\",\"Okay\":\"Okay\",\"Product_added_to_wishlist\":\"Product added to wishlist\",\"Please_Sign_in\":\"Please Sign in\",\"You_need_to_Sign_in_to_view_this_feature\":\"You need to Sign in to view this feature\",\"Cancel\":\"Cancel\",\"sign_in\":\"Sign in\",\"Delete_this_address\":\"Delete this address\",\"This_address_will_be_removed_from_this_list\":\"This address will be removed from this list\",\"Remove\":\"Remove\",\"Chat_with_us_on_WhatsApp\":\"Chat with us on WhatsApp\",\"are_you_sure\":\"Are you sure\",\"no\":\"No\",\"yes\":\"Yes\",\"Your_Privacy_Matter\":\"Your Privacy Matter\",\"no_thanks\":\"No thanks\",\"i_Accept\":\"I Accept\",\"online_Shopping\":\"Online Shopping\",\"ecommerce\":\"Ecommerce\",\"categories\":\"Categories\",\"view_more\":\"View more\",\"store\":\"Store\",\"product\":\"Product\",\"services\":\"Services\",\"discounted_products\":\"Discounted products\",\"sign_up\":\"Sign up\",\"wholesaler_zone\":\"Wholesaler zone\",\"become_a_wholesaler\":\"Become a wholesaler\",\"wholesaler_login\":\"Wholesaler login\",\"expand_Menu\":\"Expand Menu\",\"my_cart\":\"My cart\",\"shopping_cart\":\"Shopping cart\",\"Your_Cart_is_Empty\":\"Your Cart is Empty\",\"search_for_items\":\"Search for items\",\"cancel\":\"Cancel\",\"view_all\":\"View all\",\"recommended_product\":\"Recommended product\",\"buy_now\":\"Buy now\",\"new_arrivals\":\"New arrivals\",\"best_sellings\":\"Best sellings\",\"top_rated\":\"Top rated\",\"admin\":\"Admin\",\"login\":\"Login\",\"Make Your Business\":\"Make Your Business\",\"Profitable...\":\"Profitable...\",\"software_version\":\"Software version\",\"welcome_back_to\":\"Welcome back to\",\"Login\":\"Login\",\"your_email\":\"Your email\",\"password\":\"Password\",\"8+_characters_required\":\"8+ characters required\",\"remember_me\":\"Remember me\",\"please_check_the_recaptcha\":\"Please check the recaptcha\",\"copied_successfully\":\"Copied successfully\",\"page_Not_found\":\"Page Not found\",\"we_are_sorry\":\"We are sorry\",\"the_page_you_requested_could_not_be_found\":\"The page you requested could not be found\",\"please_go_back_to_the_homepage\":\"Please go back to the homepage\",\"the\":\"The\",\"field is required\":\"Field is required\",\"dashboard\":\"Dashboard\",\"welcome\":\"Welcome\",\"monitor_your_business_analytics_and_statistics\":\"Monitor your business analytics and statistics\",\"business_analytics\":\"Business analytics\",\"overall_statistics\":\"Overall statistics\",\"todays_Statistics\":\"Todays Statistics\",\"this_Months_Statistics\":\"This Months Statistics\",\"total_order\":\"Total order\",\"total_Services\":\"Total Services\",\"total_Products\":\"Total Products\",\"total_Customers\":\"Total Customers\",\"pending\":\"Pending\",\"confirmed\":\"Confirmed\",\"packaging\":\"Packaging\",\"out_for_delivery\":\"Out for delivery\",\"delivered\":\"Delivered\",\"canceled\":\"Canceled\",\"returned\":\"Returned\",\"failed_to_delivery\":\"Failed to delivery\",\"admin_wallet\":\"Admin wallet\",\"in-house_earning\":\"In-house earning\",\"total_tax_collected\":\"Total tax collected\",\"pending_amount\":\"Pending amount\",\"order_statistics\":\"Order statistics\",\"this_Year\":\"This Year\",\"this_Month\":\"This Month\",\"this_Week\":\"This Week\",\"inhouse\":\"Inhouse\",\"user_overview\":\"User overview\",\"total_User\":\"Total User\",\"total_customer\":\"Total customer\",\"total_delivery_man\":\"Total delivery man\",\"earning_statistics\":\"Earning statistics\",\"top_customer\":\"Top customer\",\"orders\":\"Orders\",\"most_Popular_Products\":\"Most Popular Products\",\"image\":\"Image\",\"reviews\":\"Reviews\",\"top_selling_products\":\"Top selling products\",\"sold\":\"Sold\",\"top_Delivery_Man\":\"Top Delivery Man\",\"vendor\":\"Vendor\",\"commission\":\"Commission\",\"In-house\":\"In-house\",\"customer\":\"Customer\",\"order\":\"Order\",\"brand\":\"Brand\",\"business\":\"Business\",\"Total_Customer\":\"Total Customer\",\"Total_Vendor\":\"Total Vendor\",\"Total_Delivery_Man\":\"Total Delivery Man\",\"logo\":\"Logo\",\"message\":\"Message\",\"no_notifications_found\":\"No notifications found\",\"See_all_notifications\":\"See all notifications\",\"pending_Orders\":\"Pending Orders\",\"image_description\":\"Image description\",\"settings\":\"Settings\",\"logout\":\"Logout\",\"view_website\":\"View website\",\"order_list\":\"Order list\",\"search_menu\":\"Search menu\",\"POS\":\"POS\",\"select_branch\":\"Select branch\",\"order_management\":\"Order management\",\"all\":\"All\",\"failed\":\"Failed\",\"failed_to_Deliver\":\"Failed to Deliver\",\"refund_Requests\":\"Refund Requests\",\"approved\":\"Approved\",\"refunded\":\"Refunded\",\"rejected\":\"Rejected\",\"product_management\":\"Product management\",\"category_Setup\":\"Category Setup\",\"sub_Categories\":\"Sub Categories\",\"sub_Sub_Categories\":\"Sub Sub Categories\",\"product_Attribute_Setup\":\"Product Attribute Setup\",\"in-House_Products\":\"In-House Products\",\"in-house_Products\":\"In-house Products\",\"Product_List\":\"Product List\",\"add_New_Product\":\"Add New Product\",\"bulk_import\":\"Bulk import\",\"product_stock\":\"Product stock\",\"Request_Restock_List\":\"Request Restock List\",\"vendor_Products\":\"Vendor Products\",\"new_Products_Requests\":\"New Products Requests\",\"approved_Products\":\"Approved Products\",\"denied_Products\":\"Denied Products\",\"product_makes\":\"Product makes\",\"product_make_setup\":\"Product make setup\",\"makes\":\"Makes\",\"warranty_management\":\"Warranty management\",\"warranty_dashboard\":\"Warranty dashboard\",\"imports\":\"Imports\",\"csv_upload\":\"Csv upload\",\"history\":\"History\",\"activations\":\"Activations\",\"manual_activate\":\"Manual activate\",\"activation_reviews\":\"Activation reviews\",\"blacklist\":\"Blacklist\",\"reports\":\"Reports\",\"claims_report\":\"Claims report\",\"sla_compliance\":\"Sla compliance\",\"activation_report\":\"Activation report\",\"serial_transaction_history\":\"Serial transaction history\",\"promotion_management\":\"Promotion management\",\"banner_Setup\":\"Banner Setup\",\"offers_&_Deals\":\"Offers & Deals\",\"coupon\":\"Coupon\",\"flash_Deals\":\"Flash Deals\",\"deal_of_the_day\":\"Deal of the day\",\"featured_Deal\":\"Featured Deal\",\"Clearance_Sale\":\"Clearance Sale\",\"notifications\":\"Notifications\",\"send_notification\":\"Send notification\",\"send_notification_svg\":\"Send notification svg\",\"push_notifications_setup\":\"Push notifications setup\",\"push_notification_svg\":\"Push notification svg\",\"announcement\":\"Announcement\",\"reports_&_Analysis\":\"Reports & Analysis\",\"sales_&_Transaction_Report\":\"Sales & Transaction Report\",\"Earning_Reports\":\"Earning Reports\",\"inhouse_Sales\":\"Inhouse Sales\",\"vendor_Sales\":\"Vendor Sales\",\"transaction_Report\":\"Transaction Report\",\"product_Report\":\"Product Report\",\"order_Report\":\"Order Report\",\"Branch Management\":\"Branch Management\",\"branch\":\"Branch\",\"add_New_Branch\":\"Add New Branch\",\"vendor_List\":\"Vendor List\",\"branch_List\":\"Branch List\",\"Branches_Stock_List\":\"Branches Stock List\",\"Branches_Charts\":\"Branches Charts\",\"chart\":\"Chart\",\"crm_charts\":\"Crm charts\",\"Branch_Stock\":\"Branch Stock\",\"Stock_Request_List\":\"Stock Request List\",\"add-stock-request\":\"Add-stock-request\",\"Add_New_Stock_Request\":\"Add New Stock Request\",\"Stock_Transfert_List\":\"Stock Transfert List\",\"Stock_Transfer_List\":\"Stock Transfer List\",\"Transfer_New_Stock\":\"Transfer New Stock\",\"Alerts and Thresholds\":\"Alerts and Thresholds\",\"Stock Alerts\":\"Stock Alerts\",\"user_management\":\"User management\",\"customers\":\"Customers\",\"Customer_List\":\"Customer List\",\"customer_List\":\"Customer List\",\"customer_Reviews\":\"Customer Reviews\",\"wallet\":\"Wallet\",\"wallet_Bonus_Setup\":\"Wallet Bonus Setup\",\"loyalty_Points\":\"Loyalty Points\",\"vendors\":\"Vendors\",\"add_New_Vendor\":\"Add New Vendor\",\"withdraws\":\"Withdraws\",\"withdrawal_Methods\":\"Withdrawal Methods\",\"delivery_men\":\"Delivery men\",\"add_new\":\"Add new\",\"list\":\"List\",\"emergency_contact\":\"Emergency contact\",\"Emergency_Contact\":\"Emergency Contact\",\"employees\":\"Employees\",\"employee_Role_Create\":\"Employee Role Create\",\"employee_Roles\":\"Employee Roles\",\"department\":\"Department\",\"add_New_Department\":\"Add New Department\",\"department_List\":\"Department List\",\"Department_List\":\"Department List\",\"subscribers\":\"Subscribers\",\"crm_management\":\"Crm management\",\"inbox\":\"Inbox\",\"leads\":\"Leads\",\"deals\":\"Deals\",\"wholesaler_Deals\":\"Wholesaler Deals\",\"retail_Deals\":\"Retail Deals\",\"Tickets\":\"Tickets\",\"support\":\"Support\",\"complaint\":\"Complaint\",\"career\":\"Career\",\"service\":\"Service\",\"retail\":\"Retail\",\"wholesale\":\"Wholesale\",\"warranty_Claims\":\"Warranty Claims\",\"new\":\"New\",\"triage_pending\":\"Triage pending\",\"rma_issued\":\"Rma issued\",\"received\":\"Received\",\"repair_pending\":\"Repair pending\",\"resolved\":\"Resolved\",\"closed\":\"Closed\",\"chat_Box\":\"Chat Box\",\"calendar\":\"Calendar\",\"sla_configration\":\"Sla configration\",\"Wholesaler_Management\":\"Wholesaler Management\",\"Wholesaler_Business\":\"Wholesaler Business\",\"Wholesaler Tier\":\"Wholesaler Tier\",\"Tiers\":\"Tiers\",\"Wholesaler Requests\":\"Wholesaler Requests\",\"Join Requests\":\"Join Requests\",\"Wholesalers\":\"Wholesalers\",\"Order Requests\":\"Order Requests\",\"Purchase Requests\":\"Purchase Requests\",\"wholesale_Orders\":\"Wholesale Orders\",\"Quotation_Sent\":\"Quotation Sent\",\"Create_Quotation\":\"Create Quotation\",\"Confirmed_Orders\":\"Confirmed Orders\",\"Whole_Sellers\":\"Whole Sellers\",\"Wholesale_Products\":\"Wholesale Products\",\"add_New_Wholesaler\":\"Add New Wholesaler\",\"Whole_seller_list\":\"Whole seller list\",\"system_Settings\":\"System Settings\",\"business_Setup\":\"Business Setup\",\"business_Settings\":\"Business Settings\",\"in-house_Shop\":\"In-house Shop\",\"SEO_Settings\":\"SEO Settings\",\"system_Setup\":\"System Setup\",\"login_Settings\":\"Login Settings\",\"email_template\":\"Email template\",\"3rd_Party\":\"3rd Party\",\"payment_methods\":\"Payment methods\",\"Marketing_Tool\":\"Marketing Tool\",\"other_Configurations\":\"Other Configurations\",\"Pages_&_Media\":\"Pages & Media\",\"business_Pages\":\"Business Pages\",\"social_Media_Links\":\"Social Media Links\",\"gallery\":\"Gallery\",\"wholesaler_Registration\":\"Wholesaler Registration\",\"Products\":\"Products\",\"blog\":\"Blog\",\"add_New\":\"Add New\",\"List\":\"List\",\"this_option_is_disabled_for_demo\":\"This option is disabled for demo\",\"status_updated_successfully\":\"Status updated successfully\",\"status_update_failed\":\"Status update failed\",\"updated_successfully\":\"Updated successfully\",\"extension\":\"Extension\",\"deleted_successfully\":\"Deleted successfully\",\"once_deleted_you_will_not_be_able_to_recover_this\":\"Once deleted you will not be able to recover this\",\"are_you_sure_you_want_to_delete_this\":\"Are you sure you want to delete this\",\"you_will_not_be_able_to_revert_this\":\"You will not be able to revert this\",\"yes_delete_it\":\"Yes delete it\",\"copied_to_the_clipboard\":\"Copied to the clipboard\",\"The_file_upload_field_is_required\":\"The file upload field is required\",\"successfully_updated\":\"Successfully updated\",\"both_Phone_&_Email_verification_can_not_be_active_at_a_time\":\"Both Phone & Email verification can not be active at a time\",\"select_country\":\"Select country\",\"invalid_date_range\":\"Invalid date range\",\"minimum_amount_can_not_be_greater_than_maximum_amount\":\"Minimum amount can not be greater than maximum amount\",\"the_file_upload_field_is_required\":\"The file upload field is required\",\"select_minimum_one_selection_box\":\"Select minimum one selection box\",\"status_updated_failed\":\"Status updated failed\",\"product_must_be_approved\":\"Product must be approved\",\"featured_status_updated_successfully\":\"Featured status updated successfully\",\"please_only_input_png_or_jpg_type_file\":\"Please only input png or jpg type file\",\"file_size_too_big\":\"File size too big\",\"do_you_want_to_sign_out\":\"Do you want to sign out\",\"do_not_Logout\":\"Do not Logout\",\"select_product\":\"Select product\",\"want_to_change_this_language\":\"Want to change this language\",\"please_enter_a_valid_integer_for_current_stock\":\"Please enter a valid integer for current stock\",\"ex\":\"Ex\",\"file_not_found\":\"File not found\",\"out_of_stock\":\"Out of stock\",\"please_check_your_inventory_and_update\":\"Please check your inventory and update\",\"There_is_not_enough_quantity_on_stock\":\"There is not enough quantity on stock\",\"please_check_your_inventory\":\"Please check your inventory\",\"profile\":\"Profile\",\"ready_to_Leave\":\"Ready to Leave\",\"Select_Logout_below_if_you_are_ready_to_end_your_current_session\":\"Select Logout below if you are ready to end your current session\",\"you_have_new_order\":\"You have new order\",\"check_please\":\"Check please\",\"Ignore_this_now\":\"Ignore this now\",\"ok\":\"Ok\",\"let_me_check\":\"Let me check\",\"do_you_want_to_logout\":\"Do you want to logout\",\"Message\":\"Message\",\"New_Message\":\"New Message\",\"do_not_show_again\":\"Do not show again\",\"Warning\":\"Warning\",\"please_fill_out_this_field\":\"Please fill out this field\",\"select\":\"Select\",\"status_change_successfully\":\"Status change successfully\",\"are_you_sure_to_delete_this\":\"Are you sure to delete this\",\"warning\":\"Warning\",\"there_is_not_enough_quantity_on_stock\":\"There is not enough quantity on stock\",\"please_check_products_in_limited_stock\":\"Please check products in limited stock\",\"_more_products_have_low_stock\":\" more products have low stock\",\"this_product_is_low_on_stock\":\"This product is low on stock\",\"want_to_clear_all_stock_clearance_products?\":\"Want to clear all stock clearance products \",\"all_Products\":\"All Products\",\"product_Stock\":\"Product Stock\",\"wish_Listed_Products\":\"Wish Listed Products\",\"filter_Data\":\"Filter Data\",\"today\":\"Today\",\"custom_Date\":\"Custom Date\",\"start_date\":\"Start date\",\"end_date\":\"End date\",\"filter\":\"Filter\",\"total_Product\":\"Total Product\",\"active\":\"Active\",\"total_Product_Sale\":\"Total Product Sale\",\"total_Discount_Given\":\"Total Discount Given\",\"product_wise_discounted_amount_will_be_shown_here\":\"Product wise discounted amount will be shown here\",\"product_Statistics\":\"Product Statistics\",\"total_product\":\"Total product\",\"search_product_name\":\"Search product name\",\"search\":\"Search\",\"export\":\"Export\",\"SL\":\"SL\",\"product_Name\":\"Product Name\",\"product_Unit_Price\":\"Product Unit Price\",\"total_Amount_Sold\":\"Total Amount Sold\",\"total_Quantity_Sold\":\"Total Quantity Sold\",\"average_Product_Value\":\"Average Product Value\",\"current_Stock_Amount\":\"Current Stock Amount\",\"average_Ratings\":\"Average Ratings\",\"inhouse_product_sale_report\":\"Inhouse product sale report\",\"inhouse_sales_analytics\":\"Inhouse sales analytics\",\"category\":\"Category\",\"leave_empty_for_all\":\"Leave empty for all\",\"from\":\"From\",\"to\":\"To\",\"reset\":\"Reset\",\"excel\":\"Excel\",\"PDF\":\"PDF\",\"total_sales\":\"Total sales\",\"qty\":\"Qty\",\"online\":\"Online\",\"sales_by_date\":\"Sales by date\",\"channel_mix\":\"Channel mix\",\"branch_and_sales_type\":\"Branch and sales type\",\"sales_type_and_product\":\"Sales type and product\",\"branch_and_product\":\"Branch and product\",\"sales\":\"Sales\",\"sales_online\":\"Sales online\",\"system\":\"System\",\"initial_stock\":\"Initial stock\",\"product_stock_analytics_report\":\"Product stock analytics report\",\"include_internal_transfers\":\"Include internal transfers\",\"total_current_stock\":\"Total current stock\",\"products_count\":\"Products count\",\"total_stock_in\":\"Total stock in\",\"total_stock_out\":\"Total stock out\",\"branches_count\":\"Branches count\",\"net_stock_movement\":\"Net stock movement\",\"stock_report\":\"Stock report\",\"stock_movement_by_date\":\"Stock movement by date\",\"stock_by_branch_chart\":\"Stock by branch chart\",\"stock_by_product_chart\":\"Stock by product chart\",\"branch_and_product_chart\":\"Branch and product chart\",\"stock_by_product\":\"Stock by product\",\"current_stock\":\"Current stock\",\"stock_in\":\"Stock in\",\"stock_out\":\"Stock out\",\"stock_by_branch\":\"Stock by branch\",\"stock_by_branch_and_product\":\"Stock by branch and product\",\"stock_movement_history\":\"Stock movement history\",\"date\":\"Date\",\"type\":\"Type\",\"quantity\":\"Quantity\",\"reference\":\"Reference\",\"variation\":\"Variation\",\"stock_limit_products\":\"Stock limit products\",\"Products_Stocked_List\":\"Products Stocked List\",\"search_by_Product_Name\":\"Search by Product Name\",\"default\":\"Default\",\"inventory_quantity(low_to_high)\":\"Inventory quantity(low to high)\",\"inventory_quantity(high_to_low)\":\"Inventory quantity(high to low)\",\"order_volume(low_to_high)\":\"Order volume(low to high)\",\"order_volume(high_to_low)\":\"Order volume(high to low)\",\"unit_price\":\"Unit price\",\"active_status\":\"Active status\",\"action\":\"Action\",\"Want_to_Turn_ON\":\"Want to Turn ON\",\"status\":\"Status\",\"Want_to_Turn_OFF\":\"Want to Turn OFF\",\"if_enabled_this_product_will_be_available_on_the_website_and_customer_app\":\"If enabled this product will be available on the website and customer app\",\"if_disabled_this_product_will_be_hidden_from_the_website_and_customer_app\":\"If disabled this product will be hidden from the website and customer app\",\"update_quantity\":\"Update quantity\",\"close\":\"Close\",\"submit\":\"Submit\",\"view_internal_branch_transfers\":\"View internal branch transfers\",\"manual_adjust_add\":\"Manual adjust add\",\"returns\":\"Returns\",\"sales_pos\":\"Sales pos\",\"sales_wholesale_transfer\":\"Sales wholesale transfer\",\"manual_adjust_negative\":\"Manual adjust negative\",\"internal_branch_transfer\":\"Internal branch transfer\",\"internal_transfers_do_not_change_net_general_stock\":\"Internal transfers do not change net general stock\",\"product_Section\":\"Product Section\",\"all_categories\":\"All categories\",\"search_by_name_or_sku\":\"Search by name or sku\",\"billing_Section\":\"Billing Section\",\"Branch\":\"Branch\",\"please_resume_the_order_from_here\":\"Please resume the order from here\",\"view_All_Hold_Orders\":\"View All Hold Orders\",\"walking_customer\":\"Walking customer\",\"add_new_customer\":\"Add new customer\",\"add_New_Customer\":\"Add New Customer\",\"view_all_hold_orders\":\"View all hold orders\",\"clear_Cart\":\"Clear Cart\",\"new_Order\":\"New Order\",\"item\":\"Item\",\"price\":\"Price\",\"delete\":\"Delete\",\"sub_total\":\"Sub total\",\"product_Discount\":\"Product Discount\",\"extra_Discount\":\"Extra Discount\",\"coupon_Discount\":\"Coupon Discount\",\"tax\":\"Tax\",\"total\":\"Total\",\"paid_By\":\"Paid By\",\"cash\":\"Cash\",\"card\":\"Card\",\"Paid_Amount\":\"Paid Amount\",\"Change_Amount\":\"Change Amount\",\"insufficient_balance\":\"Insufficient balance\",\"cancel_Order\":\"Cancel Order\",\"place_Order\":\"Place Order\",\"first_name\":\"First name\",\"last_name\":\"Last name\",\"email\":\"Email\",\"phone\":\"Phone\",\"enter_phone_number\":\"Enter phone number\",\"country\":\"Country\",\"city\":\"City\",\"zip_code\":\"Zip code\",\"list_of_hold_orders\":\"List of hold orders\",\"search_by_customer_name\":\"Search by customer name\",\"coupon_discount\":\"Coupon discount\",\"coupon_code\":\"Coupon code\",\"please_enter_coupon_code\":\"Please enter coupon code\",\"update_discount\":\"Update discount\",\"amount\":\"Amount\",\"percent\":\"Percent\",\"discount\":\"Discount\",\"please_enter_discount_amount\":\"Please enter discount amount\",\"short_cut_keys\":\"Short cut keys\",\"to_click_order\":\"To click order\",\"to_click_payment_submit\":\"To click payment submit\",\"to_close_payment_submit\":\"To close payment submit\",\"to_click_cancel_cart_item_all\":\"To click cancel cart item all\",\"to_click_add_new_customer\":\"To click add new customer\",\"to_submit_add_new_customer_form\":\"To submit add new customer form\",\"to_click_short_cut_keys\":\"To click short cut keys\",\"to_print_invoice\":\"To print invoice\",\"to_cancel_invoice\":\"To cancel invoice\",\"to_focus_search_input\":\"To focus search input\",\"to_click_extra_discount\":\"To click extra discount\",\"to_click_coupon_discount\":\"To click coupon discount\",\"to_click_clear_cart\":\"To click clear cart\",\"to_click_new_order\":\"To click new order\",\"in_stock\":\"In stock\",\"add_to_cart\":\"Add to cart\",\"cart_updated\":\"Cart updated\",\"update_to_cart\":\"Update to cart\",\"cart_is_empty\":\"Cart is empty\",\"please_enter_a_valid_amount\":\"Please enter a valid amount\",\"paid_amount_is_less_than_total_amount\":\"Paid amount is less than total amount\",\"coupon_is_invalid\":\"Coupon is invalid\",\"product_quantity_updated\":\"Product quantity updated\",\"coupon_added_successfully\":\"Coupon added successfully\",\"you_want_to_remove_all_items_from_cart\":\"You want to remove all items from cart\",\"product_quantity_is_not_enough\":\"Product quantity is not enough\",\"sorry_product_is_out_of_stock\":\"Sorry product is out of stock\",\"item_has_been_added_in_your_cart\":\"Item has been added in your cart\",\"extra_discount_added_successfully\":\"Extra discount added successfully\",\"amount_can_not_be_negative_or_zero\":\"Amount can not be negative or zero\",\"sorry_the_minimum_value_was_reached\":\"Sorry the minimum value was reached\",\"this_discount_is_not_applied_for_this_amount\":\"This discount is not applied for this amount\",\"product_quantity_can_not_be_zero_or_less_than_zero_in_cart\":\"Product quantity can not be zero or less than zero in cart\",\"order_List\":\"Order List\",\"filter_order\":\"Filter order\",\"order_type\":\"Order type\",\"in_House_Order\":\"In House Order\",\"all_customer\":\"All customer\",\"date_type\":\"Date type\",\"select_Date_Type\":\"Select Date Type\",\"show_data\":\"Show data\",\"search_by_Order_ID\":\"Search by Order ID\",\"order_ID\":\"Order ID\",\"order_date\":\"Order date\",\"customer_info\":\"Customer info\",\"total_amount\":\"Total amount\",\"order_status\":\"Order status\",\"in_House\":\"In House\",\"paid\":\"Paid\",\"view\":\"View\",\"invoice\":\"Invoice\",\"customer_not_found\":\"Customer not found\",\"unpaid\":\"Unpaid\",\"order_Details\":\"Order Details\",\"Order_ID\":\"Order ID\",\"show_locations_on_map\":\"Show locations on map\",\"print_Invoice\":\"Print Invoice\",\"payment_Method\":\"Payment Method\",\"cash_on_delivery\":\"Cash on delivery\",\"payment_Status\":\"Payment Status\",\"order_verification_code\":\"Order verification code\",\"selected_pickup_branch\":\"Selected pickup branch\",\"Check_Branch_inventory\":\"Check Branch inventory\",\"check_which_branch_have_the_order_product_stock\":\"Check which branch have the order product stock\",\"Branch_stock\":\"Branch stock\",\"item_details\":\"Item details\",\"item_price\":\"Item price\",\"Inst._Charges\":\"Inst. Charges\",\"Exc._Charges\":\"Exc. Charges\",\"item_discount\":\"Item discount\",\"total_price\":\"Total price\",\"image_Description\":\"Image Description\",\"tax_incl.\":\"Tax incl.\",\"expense_bearer_\":\"Expense bearer \",\"vat\":\"Vat\",\"shipping_fee\":\"Shipping fee\",\"order_&_Shipping_Info\":\"Order & Shipping Info\",\"delivered_from_branch\":\"Delivered from branch\",\"System\":\"System\",\"change_order_status\":\"Change order status\",\"product_for_exchange\":\"Product for exchange\",\"No\":\"No\",\"payment_status\":\"Payment status\",\"shipping_Method\":\"Shipping Method\",\"no_shipping_method_selected\":\"No shipping method selected\",\"choose_delivery_type\":\"Choose delivery type\",\"by_self_delivery_man\":\"By self delivery man\",\"by_third_party_delivery_service\":\"By third party delivery service\",\"delivery_man\":\"Delivery man\",\"Image\":\"Image\",\"no_delivery_man_assigned\":\"No delivery man assigned\",\"not_assign_yet\":\"Not assign yet\",\"track_ID\":\"Track ID\",\"customer_information\":\"Customer information\",\"shipping_address\":\"Shipping address\",\"name\":\"Name\",\"contact\":\"Contact\",\"state\":\"State\",\"area\":\"Area\",\"billing_address\":\"Billing address\",\"shop_Information\":\"Shop Information\",\"orders_Served\":\"Orders Served\",\"contact_person_name\":\"Contact person name\",\"john_doe\":\"John doe\",\"phone_number\":\"Phone number\",\"select_state\":\"Select state\",\"select_city\":\"Select city\",\"select_area\":\"Select area\",\"zip\":\"Zip\",\"street_1,_street_2,_street_3,_street_4\":\"Street 1  street 2  street 3  street 4\",\"Ex\":\"Ex\",\"search_your_location_here\":\"Search your location here\",\"search_here\":\"Search here\",\"update\":\"Update\",\"location_on_Map\":\"Location on Map\",\"order_placed\":\"Order placed\",\"order_confirmed\":\"Order confirmed\",\"preparing_shipment\":\"Preparing shipment\",\"order_is_on_the_way\":\"Order is on the way\",\"order_Shipped\":\"Order Shipped\",\"update_third_party_delivery_info\":\"Update third party delivery info\",\"delivery_service_name\":\"Delivery service name\",\"tracking_id\":\"Tracking id\",\"optional\":\"Optional\",\"exchange_product_info\":\"Exchange product info\",\"product_info\":\"Product info\",\"Amount\":\"Amount\",\"are_you_sure_change_this\":\"Are you sure change this\",\"change the transfered branch\":\"Change the transfered branch\",\"confirm_payments_before_change_the_status\":\"Confirm payments before change the status\",\"change_the_status_paid_only_when_you_received_the_payment_from_customer\":\"Change the status paid only when you received the payment from customer\",\"_once_you_change_the_status_to_paid\":\" once you change the status to paid\",\"_you_cannot_change_the_status_again\":\" you cannot change the status again\",\"confirm_exchange_before_change_the_status\":\"Confirm exchange before change the status\",\"change_the_status_yes_only_when_you_received_the_exchange_product_from_customer\":\"Change the status yes only when you received the exchange product from customer\",\"_once_you_change_the_status_to_yes\":\" once you change the status to yes\",\"yes_change_it\":\"Yes change it\",\"account_has_been_deleted_you_can_not_change_the_status\":\"Account has been deleted you can not change the status\",\"order_is_already_delivered_you_can_not_change_it\":\"Order is already delivered you can not change it\",\"these_order_product_stock_is_not_available_for_selected_branch\":\"These order product stock is not available for selected branch\",\"transfer_branch_change_successfully\":\"Transfer branch change successfully\",\"before_delivered_you_need_to_make_payment_status_paid\":\"Before delivered you need to make payment status paid\",\"Branch is required!\":\"Branch is required!\",\"delivery_man_successfully_assigned\\/changed\":\"Delivery man successfully assigned\\/changed\",\"deliveryman_man_can_not_assign_or_change_in_that_status\":\"Deliveryman man can not assign or change in that status\",\"deliveryman_charge_add_successfully\":\"Deliveryman charge add successfully\",\"failed_to_add_deliveryman_charge\":\"Failed to add deliveryman charge\",\"add_valid_data\":\"Add valid data\",\"when_order_status_delivered_you_can`t_update_the_delivery_man_incentive\":\"When order status delivered you can`t update the delivery man incentive\",\"when_payment_status_paid_then_you_can`t_change_payment_status_paid_to_unpaid\":\"When payment status paid then you can`t change payment status paid to unpaid\",\"when_exchange_status_yes_then_you_can`t_change_exchange_status_yes_to_no\":\"When exchange status yes then you can`t change exchange status yes to no\",\"our_core_products\":\"Our core products\",\"milestones_over_the_years\":\"Milestones over the years\",\"our_trusted_dealers\":\"Our trusted dealers\",\"Blogs\":\"Blogs\",\"Search_Blog\":\"Search Blog\",\"By\":\"By\",\"Recent_Posts\":\"Recent Posts\",\"Google_Play\":\"Google Play\",\"App_Store\":\"App Store\",\"Contact_us\":\"Contact us\",\"Why Work With NISR?\":\"Why Work With NISR \",\"Current Openings\":\"Current Openings\",\"Experience\":\"Experience\",\"Location\":\"Location\",\"Skills\":\"Skills\",\"Description\":\"Description\",\"Apply Now\":\"Apply Now\",\"Perks_&_Benefits\":\"Perks & Benefits\",\"contact_us\":\"Contact us\",\"Call Us\":\"Call Us\",\"Email Us\":\"Email Us\",\"follow_us\":\"Follow us\",\"send_us_a_message\":\"Send us a message\",\"your_name\":\"Your name\",\"John_Doe\":\"John Doe\",\"email_address\":\"Email address\",\"enter_email_address\":\"Enter email address\",\"your_phone\":\"Your phone\",\"contact_number\":\"Contact number\",\"subject\":\"Subject\",\"short_title\":\"Short title\",\"send\":\"Send\",\"Our_branches\":\"Our branches\",\"Branches\":\"Branches\",\"Search branch...\":\"Search branch...\",\"Search\":\"Search\",\"Phone\":\"Phone\",\"Address\":\"Address\",\"Status\":\"Status\",\"Direction\":\"Direction\",\"Closed\":\"Closed\",\"added_to_wishlist\":\"Added to wishlist\",\"removed_from_wishlist\":\"Removed from wishlist\",\"wish_listed\":\"Wish listed\",\"1\":\"1\",\"update_cart\":\"Update cart\",\"Request_Restock\":\"Request Restock\",\"Request_Sent\":\"Request Sent\",\"overview\":\"Overview\",\"ratings\":\"Ratings\",\"excellent\":\"Excellent\",\"good\":\"Good\",\"average\":\"Average\",\"below_Average\":\"Below Average\",\"poor\":\"Poor\",\"Product_review\":\"Product review\",\"not exist\":\"Not exist\",\"Fast Delivery all across the country\":\"Fast Delivery all across the country\",\"Safe Payment\":\"Safe Payment\",\"7 Days Return Policy\":\"7 Days Return Policy\",\"100% Authentic Products\":\"100% Authentic Products\",\"you_may_also_like\":\"You may also like\",\"similar_products\":\"Similar products\",\"Write_here\":\"Write here\",\"go_to_chatbox\":\"Go to chatbox\",\"successfully_added\":\"Successfully added\",\"you_have_saved\":\"You have saved\",\"subtotal\":\"Subtotal\",\"expand_carts\":\"Expand carts\",\"proceed_to_checkout\":\"Proceed to checkout\",\"shipping_Address\":\"Shipping Address\",\"checkout\":\"Checkout\",\"shipping\":\"Shipping\",\"and_billing\":\"And billing\",\"payment\":\"Payment\",\"Delivery_Type\":\"Delivery Type\",\"delivery\":\"Delivery\",\"pickup\":\"Pickup\",\"address_type\":\"Address type\",\"permanent\":\"Permanent\",\"office\":\"Office\",\"others\":\"Others\",\"Pickup_branch\":\"Pickup branch\",\"please_select\":\"Please select\",\"Alexandria\":\"Alexandria\",\"El Rehab\":\"El Rehab\",\"Pickup_branch_address\":\"Pickup branch address\",\"note\":\"Note\",\"you_need_to_select_address_from_your_selected_country\":\"You need to select address from your selected country\",\"Create_an_account_with_the_above_info\":\"Create an account with the above info\",\"new_Password\":\"New Password\",\"show_password\":\"Show password\",\"confirm_Password\":\"Confirm Password\",\"same_as_shipping_address\":\"Same as shipping address\",\"Update_this_Address\":\"Update this Address\",\"Shipping_cost_updated\":\"Shipping cost updated\",\"no_items_in_basket\":\"No items in basket\",\"Our_Products\":\"Our Products\",\"asd\":\"Asd\",\"N220\":\"N220\",\"NS70\":\"NS70\",\"N80\":\"N80\",\"Request_Service\":\"Request Service\",\"general_Settings\":\"General Settings\",\"please_click_save_information_button_below_to_save_all_the_changes\":\"Please click save information button below to save all the changes\",\"general\":\"General\",\"payment_options\":\"Payment options\",\"products\":\"Products\",\"priority_setup\":\"Priority setup\",\"delivery_restriction\":\"Delivery restriction\",\"delivery_available\":\"Delivery available\",\"State_&_City\":\"State & City\",\"quotation\":\"Quotation\",\"changing_some_settings_will_take_time_to_show_effect_please_clear_session_or_wait_for_60_minutes_else_browse_from_incognito_mode\":\"Changing some settings will take time to show effect please clear session or wait for 60 minutes else browse from incognito mode\",\"System_Maintenance\":\"System Maintenance\",\"By turning on maintenance mode Control your all system & function\":\"By turning on maintenance mode Control your all system & function\",\"Maintenance_Mode\":\"Maintenance Mode\",\"do_you_want_to_turn_off_the_maintenance_mode\":\"Do you want to turn off the maintenance mode\",\"by_turning_on_maintenance_mode_control_your_all_system_&_function\":\"By turning on maintenance mode control your all system & function\",\"Select_System\":\"Select System\",\"select_the_systems_you_want_to_temporarily_deactivate_for_maintenance\":\"Select the systems you want to temporarily deactivate for maintenance\",\"All_System\":\"All System\",\"user_app\":\"User app\",\"user_website\":\"User website\",\"vendor_app\":\"Vendor app\",\"deliveryman_app\":\"Deliveryman app\",\"vendor_panel\":\"Vendor panel\",\"Maintenance_Date_and_Time\":\"Maintenance Date and Time\",\"choose_the_maintenance_mode_duration_for_your_selected_system.\":\"Choose the maintenance mode duration for your selected system.\",\"For_24_Hours\":\"For 24 Hours\",\"For_1_Week\":\"For 1 Week\",\"Until_I_change\":\"Until I change\",\"Customize\":\"Customize\",\"Start_Date\":\"Start Date\",\"End_Date\":\"End Date\",\"start_date_cannot_be_greater_than_end_date.\":\"Start date cannot be greater than end date.\",\"Advance_Feature\":\"Advance Feature\",\"Maintenance_Massage\":\"Maintenance Massage\",\"select_&_type_what_massage_you_want_to_see_your_selected_system_when_maintenance_mode_is_active.\":\"Select & type what massage you want to see your selected system when maintenance mode is active.\",\"Show_Contact_Info\":\"Show Contact Info\",\"Business_Number\":\"Business Number\",\"Business_Email\":\"Business Email\",\"Maintenance_Message\":\"Maintenance Message\",\"the_maximum_character_limit_is_200\":\"The maximum character limit is 200\",\"we_are_working_on_something_special!\":\"We are working on something special!\",\"Message_Body\":\"Message Body\",\"sorry_for_the_inconvenience!\":\"Sorry for the inconvenience!\",\"we_are_currently_undergoing_scheduled_maintenance_to_improve_our_services.\":\"We are currently undergoing scheduled maintenance to improve our services.\",\"we_will_be_back_shortly.\":\"We will be back shortly.\",\"thank_you_for_your_patience.\":\"Thank you for your patience.\",\"See_Less\":\"See Less\",\"Save\":\"Save\",\"company_information\":\"Company information\",\"company_Name\":\"Company Name\",\"new_business\":\"New business\",\"company_address\":\"Company address\",\"your_shop_address\":\"Your shop address\",\"01xxxxxxxx\":\"01xxxxxxxx\",\"company@gmail.com\":\"Company@gmail.com\",\"time_zone\":\"Time zone\",\"language\":\"Language\",\"latitude\":\"Latitude\",\"copy_the_latitude_of_your_business_location_from_Google_Maps_and_paste_it_here\":\"Copy the latitude of your business location from Google Maps and paste it here\",\"longitude\":\"Longitude\",\"copy_the_longitude_of_your_business_location_from_Google_Maps_and_paste_it_here\":\"Copy the longitude of your business location from Google Maps and paste it here\",\"business_information\":\"Business information\",\"currency\":\"Currency\",\"currency_Position\":\"Currency Position\",\"left\":\"Left\",\"right\":\"Right\",\"currency_space\":\"Currency space\",\"without_space\":\"Without space\",\"with_space\":\"With space\",\"business_model\":\"Business model\",\"single_vendor\":\"Single vendor\",\"multi_vendor\":\"Multi vendor\",\"pagination\":\"Pagination\",\"this_number_indicates_how_much_data_will_be_shown_in_the_list_or_table\":\"This number indicates how much data will be shown in the list or table\",\"Company_Copyright_Text\":\"Company Copyright Text\",\"company_copyright_text\":\"Company copyright text\",\"digit_after_decimal_point\":\"Digit after decimal point\",\"4\":\"4\",\"app_download_info\":\"App download info\",\"apple_store\":\"Apple store\",\"download_link\":\"Download link\",\"if_enabled_the_download_button_from_the_App_Store_will_be_visible_in_the_Footer_section\":\"If enabled the download button from the App Store will be visible in the Footer section\",\"want_to_Turn_OFF_the_App_Store_button\":\"Want to Turn OFF the App Store button\",\"want_to_Turn_ON_the_App_Store_button\":\"Want to Turn ON the App Store button\",\"if_disabled_the_App_Store_button_will_be_hidden_from_the_website_footer\":\"If disabled the App Store button will be hidden from the website footer\",\"if_enabled_everyone_can_see_the_App_Store_button_in_the_website_footer\":\"If enabled everyone can see the App Store button in the website footer\",\"google_play_store\":\"Google play store\",\"if_enabled_the_Google_Play_Store_will_be_visible_in_the_website_footer_section\":\"If enabled the Google Play Store will be visible in the website footer section\",\"want_to_Turn_OFF_the_Google_Play_Store_button\":\"Want to Turn OFF the Google Play Store button\",\"want_to_Turn_ON_the_Google_Play_Store_button\":\"Want to Turn ON the Google Play Store button\",\"if_disabled_the_Google_Play_Store_button_will_be_hidden_from_the_website_footer\":\"If disabled the Google Play Store button will be hidden from the website footer\",\"if_enabled_everyone_can_see_the_Google_Play_Store_button_in_the_website_footer\":\"If enabled everyone can see the Google Play Store button in the website footer\",\"Ex: https:\\/\\/play.google.com\\/store\\/apps\":\"Ex: https:\\/\\/play.google.com\\/store\\/apps\",\"website_Color\":\"Website Color\",\"primary_Color\":\"Primary Color\",\"secondary_Color\":\"Secondary Color\",\"website_header_logo\":\"Website header logo\",\"choose_file\":\"Choose file\",\"website_footer_logo\":\"Website footer logo\",\"website_Favicon\":\"Website Favicon\",\"ratio\":\"Ratio\",\"choose_File\":\"Choose File\",\"loading_gif\":\"Loading gif\",\"App_Logo\":\"App Logo\",\"save_information\":\"Save information\",\"order_settings\":\"Order settings\",\"business_setup\":\"Business setup\",\"please_click_the_Save_button_below_to_save_all_the_changes\":\"Please click the Save button below to save all the changes\",\"order_delivery_verification\":\"Order delivery verification\",\"customers_receive_a_verification_code_after_placing_an_order\":\"Customers receive a verification code after placing an order\",\"when_a_deliveryman_arrives_for_delivery_they_must_provide_the_code_to_the_deliveryman_to_verify_the_order_delivery\":\"When a deliveryman arrives for delivery they must provide the code to the deliveryman to verify the order delivery\",\"want_to_Turn_ON_Order_Delivery_Verification\":\"Want to Turn ON Order Delivery Verification\",\"want_to_Turn_OFF_Order_Delivery_Verification\":\"Want to Turn OFF Order Delivery Verification\",\"if_enabled_deliverymen_must_verify_the_order_deliveries_by_collecting_the_OTP_from_customers\":\"If enabled deliverymen must verify the order deliveries by collecting the OTP from customers\",\"if_disabled_deliverymen_do_not_need_to_verify_the_order_deliveries\":\"If disabled deliverymen do not need to verify the order deliveries\",\"minimum_order_amount\":\"Minimum order amount\",\"if_enabled_customers_must_place_at_least_or_more_than_the_order_amount_that_admin_or_vendors_set\":\"If enabled customers must place at least or more than the order amount that admin or vendors set\",\"want_to_Turn_ON_Minimum_Order_Amount\":\"Want to Turn ON Minimum Order Amount\",\"want_to_Turn_OFF_Minimum_Order_Amount\":\"Want to Turn OFF Minimum Order Amount\",\"if_enabled_customers_must_order_over_the_minimum_amount_of_orders_that_admin_or_vendors_set\":\"If enabled customers must order over the minimum amount of orders that admin or vendors set\",\"if_disabled_there_will_be_no_minimum_order_restrictions_and_customers_can_place_any_order_amount\":\"If disabled there will be no minimum order restrictions and customers can place any order amount\",\"show_billing_address_in_checkout\":\"Show billing address in checkout\",\"if_enabled_the_billing_address_will_be_shown_on_the_checkout_page\":\"If enabled the billing address will be shown on the checkout page\",\"want_to_Turn_ON_Billing_Address_in_Checkout\":\"Want to Turn ON Billing Address in Checkout\",\"want_to_Turn_OFF_Billing_Address_in_Checkout\":\"Want to Turn OFF Billing Address in Checkout\",\"if_disabled_the_billing_address_will_be_hidden_from_the_checkout_page\":\"If disabled the billing address will be hidden from the checkout page\",\"free_delivery\":\"Free delivery\",\"if_enabled_free_delivery_will_be_available_when_customers_order_over_a_certain_amount\":\"If enabled free delivery will be available when customers order over a certain amount\",\"want_to_Turn_ON_Free_Delivery\":\"Want to Turn ON Free Delivery\",\"want_to_Turn_OFF_Free_Delivery\":\"Want to Turn OFF Free Delivery\",\"if_enabled_the_free_delivery_feature_will_be_shown_from_the_system\":\"If enabled the free delivery feature will be shown from the system\",\"if_disabled_the_free_delivery_feature_will_be_hidden_from_the_system\":\"If disabled the free delivery feature will be hidden from the system\",\"free_delivery_over\":\"Free delivery over\",\"free_delivery_over_amount_for_every_vendor_if_they_do_not_set_any_range_yet\":\"Free delivery over amount for every vendor if they do not set any range yet\",\"refund_order_validity\":\"Refund order validity\",\"days\":\"Days\",\"guest_checkout\":\"Guest checkout\",\"if_enabled_users_can_complete_the_checkout_process_without_logging_in_to_the_system\":\"If enabled users can complete the checkout process without logging in to the system\",\"by_Turning_ON_Guest_Checkout_Mode\":\"By Turning ON Guest Checkout Mode\",\"by_Turning_Off_Guest_Checkout_Mode\":\"By Turning Off Guest Checkout Mode\",\"user_can_place_order_without_login\":\"User can place order without login\",\"user_cannot_place_order_without_login\":\"User cannot place order without login\",\"stock_Check\":\"Stock Check\",\"if_enabled_users_can_complete_the_checkout_process_without_checking_stock_is_availble_or_not\":\"If enabled users can complete the checkout process without checking stock is availble or not\",\"by_Turning_ON_Stock_Check_Mode\":\"By Turning ON Stock Check Mode\",\"by_Turning_Off_Stock_Check_Mode\":\"By Turning Off Stock Check Mode\",\"User_cannot_place_an_order_if_stock_is_not_available.\":\"User cannot place an order if stock is not available.\",\"user_can_place_an_order_if_stock_is_not_availble\":\"User can place an order if stock is not availble\",\"stock_validation_refactor\":\"Stock validation refactor\",\"if_enabled_stock_validation_uses_the_new_policy_and_variant_matching_service\":\"If enabled stock validation uses the new policy and variant matching service\",\"by_turning_on_stock_validation_refactor\":\"By turning on stock validation refactor\",\"by_turning_off_stock_validation_refactor\":\"By turning off stock validation refactor\",\"new_stock_policy_and_variant_matching_will_be_enforced\":\"New stock policy and variant matching will be enforced\",\"legacy_stock_validation_logic_will_be_enforced\":\"Legacy stock validation logic will be enforced\",\"stock_validation_mirror_mode\":\"Stock validation mirror mode\",\"if_enabled_system_compares_refactor_and_legacy_results_but_enforces_legacy_result\":\"If enabled system compares refactor and legacy results but enforces legacy result\",\"by_turning_on_stock_validation_mirror_mode\":\"By turning on stock validation mirror mode\",\"by_turning_off_stock_validation_mirror_mode\":\"By turning off stock validation mirror mode\",\"legacy_checker_is_enforced_while_mismatches_are_logged\":\"Legacy checker is enforced while mismatches are logged\",\"mirror_comparison_is_disabled\":\"Mirror comparison is disabled\",\"Character_Trigger_Limit\":\"Character Trigger Limit\",\"The_search_response_will_appear_only_after_the_user_types_a_predefined_number_of_characters\":\"The search response will appear only after the user types a predefined number of characters\",\"save\":\"Save\",\"customer_settings\":\"Customer settings\",\"customer_wallet\":\"Customer wallet\",\"by_enabling_the_option,_customers_can_view_their_wallets_from_the_app_&_website\":\"By enabling the option  customers can view their wallets from the app & website\",\"want_to_Turn_ON_Customer_Wallet\":\"Want to Turn ON Customer Wallet\",\"want_to_Turn_OFF_Customer_Wallet\":\"Want to Turn OFF Customer Wallet\",\"if_enabled_customers_can_have_the_wallet_option_on_their_account_and_use_it_while_placing_orders_and_getting_refunds\":\"If enabled customers can have the wallet option on their account and use it while placing orders and getting refunds\",\"if_disabled_customer_wallet_option_will_be_hidden_from_their_account\":\"If disabled customer wallet option will be hidden from their account\",\"customer_Loyalty_Point\":\"Customer Loyalty Point\",\"by_enabling_this_option,_customers_can_earn_loyalty_points_and_convert_this_point_to_wallet_money.\":\"By enabling this option  customers can earn loyalty points and convert this point to wallet money.\",\"also_customers_can_view_their_point_wallet_from_the_app_&_website.\":\"Also customers can view their point wallet from the app & website.\",\"want_to_Turn_ON_Loyalty_Point\":\"Want to Turn ON Loyalty Point\",\"want_to_Turn_OFF_Loyalty_Point\":\"Want to Turn OFF Loyalty Point\",\"if_enabled_the_loyalty_point_option_will_be_available_to_the_customers_account\":\"If enabled the loyalty point option will be available to the customers account\",\"if_disabled_loyalty_point_option_will_be_hidden_from_the_customers_account\":\"If disabled loyalty point option will be hidden from the customers account\",\"customer_referral_earning\":\"Customer referral earning\",\"by_enabling_the_option_each_registered_customer_is_provided_with_a_personalized_code_which_can_be_referred_to_as_an_invitation_to_the_shops.\":\"By enabling the option each registered customer is provided with a personalized code which can be referred to as an invitation to the shops.\",\"and_when_other_use_the_refer_code,_the_referral_customer_get_reward\":\"And when other use the refer code  the referral customer get reward\",\"want_to_Turn_ON_Referral_And_Earning_option\":\"Want to Turn ON Referral And Earning option\",\"want_to_Turn_OFF_Referral_And_Earning_option\":\"Want to Turn OFF Referral And Earning option\",\"if_enabled_customers_will_receive_rewards_for_each_successful_referral\":\"If enabled customers will receive rewards for each successful referral\",\"if_disabled_customers_will_not_receive_rewards_for_successful_referral\":\"If disabled customers will not receive rewards for successful referral\",\"customer_Wallet_Settings\":\"Customer Wallet Settings\",\"for_these_wallet_settings,__customers_can_get_the_refund_to_the_wallet_and_also_can_use_their_wallet_money_to_pay_for_any_order.\":\"For these wallet settings   customers can get the refund to the wallet and also can use their wallet money to pay for any order.\",\"add_Refund_Amount_to_Wallet\":\"Add Refund Amount to Wallet\",\"enabling_the_option_refund_amount_will_be_added_to_the_wallet_automatically.\":\"Enabling the option refund amount will be added to the wallet automatically.\",\"want_to_Turn_ON_Refund_to_Wallet_option\":\"Want to Turn ON Refund to Wallet option\",\"want_to_Turn_OFF_Refund_to_Wallet_option\":\"Want to Turn OFF Refund to Wallet option\",\"if_enabled_Admin_can_return_the_refund_amount_directly_to_the_customers_wallet_\":\"If enabled Admin can return the refund amount directly to the customers wallet \",\"if_disabled_Admin_needs_to_return_the_refund_amount_manually\":\"If disabled Admin needs to return the refund amount manually\",\"add_Fund_to_Wallet\":\"Add Fund to Wallet\",\"enabling_the_option,_customers_will_be_able_to_add_funds_to_the_wallet_through_the_available_payment_method.\":\"Enabling the option  customers will be able to add funds to the wallet through the available payment method.\",\"want_to_Turn_ON_Add_Fund_to_Wallet_option\":\"Want to Turn ON Add Fund to Wallet option\",\"want_to_Turn_OFF_Add_Fund_to_Wallet_option\":\"Want to Turn OFF Add Fund to Wallet option\",\"if_enabled_customers_can_add_money_to_their_wallet\":\"If enabled customers can add money to their wallet\",\"if_disabled_customers_would_not_be_able_to_add_money_to_their_wallet\":\"If disabled customers would not be able to add money to their wallet\",\"minimum_Add_Fund_Amount\":\"Minimum Add Fund Amount\",\"maximum_Add_Fund_Amount\":\"Maximum Add Fund Amount\",\"customer_Loyalty_Point_Settings\":\"Customer Loyalty Point Settings\",\"in_this_settings_admin_can_set_the_rules_for_the_customers_for_earning_and_use_the_loyalty_points\":\"In this settings admin can set the rules for the customers for earning and use the loyalty points\",\"equivalent_Point_to_1_Unit_Currency\":\"Equivalent Point to 1 Unit Currency\",\"loyalty_Point_Earn_on_Each_Order\":\"Loyalty Point Earn on Each Order\",\"minimum_Point_Required_To_Convert\":\"Minimum Point Required To Convert\",\"this_point_is_the_required_amount_which_is_needed_to_convert_the_point_to_the_wallet_balance\":\"This point is the required amount which is needed to convert the point to the wallet balance\",\"customer_Referrer_Settings\":\"Customer Referrer Settings\",\"admin_can_setup_the_rules_how_much_the_customer_will_earn_for_referring_others\":\"Admin can setup the rules how much the customer will earn for referring others\",\"earnings_to_Each_Referral\":\"Earnings to Each Referral\",\"this_set_amount_will_be_the_reward_point_which_will_get_the_customer_for_each_successful_referral.\":\"This set amount will be the reward point which will get the customer for each successful referral.\",\"delivery_Man_Settings\":\"Delivery Man Settings\",\"upload_Picture_on_Delivery\":\"Upload Picture on Delivery\",\"admin_can_set_whether_deliveryman_needs_to_upload_the_picture_of_delivery_by_enabling_or_disabling_this_button\":\"Admin can set whether deliveryman needs to upload the picture of delivery by enabling or disabling this button\",\"by_Turning_ON_Picture_Upload_on_Delivery\":\"By Turning ON Picture Upload on Delivery\",\"by_Turning_OFF_Picture_Upload_on_Delivery\":\"By Turning OFF Picture Upload on Delivery\",\"if_enabled_deliverymen_can_upload_picture_at_the_order_deliveries_time\":\"If enabled deliverymen can upload picture at the order deliveries time\",\"if_enabled_deliverymen_can_not_upload_picture_at_the_order_deliveries_time\":\"If enabled deliverymen can not upload picture at the order deliveries time\",\"forgot_password_verification_by\":\"Forgot password verification by\",\"set_how_deliverymen_recover_their_forgotten_passwords\":\"Set how deliverymen recover their forgotten passwords\",\"OTP\":\"OTP\",\"shipping_method\":\"Shipping method\",\"shipping_responsibility\":\"Shipping responsibility\",\"want_to_change_the_shipping_responsibility_to_Inhouse\":\"Want to change the shipping responsibility to Inhouse\",\"want_to_change_the_shipping_responsibility_to_Third_Party_Delivery\":\"Want to change the shipping responsibility to Third Party Delivery\",\"admin_will_handle_the_shipping_responsibilities_when_you_choose_inhouse_shipping_method\":\"Admin will handle the shipping responsibilities when you choose inhouse shipping method\",\"inhouse_shipping\":\"Inhouse shipping\",\"want_to_change_the_shipping_responsibility_to_Third_Party\":\"Want to change the shipping responsibility to Third Party\",\"Want_to_change_the_shipping_responsibility_to_Inhouse\":\"Want to change the shipping responsibility to Inhouse\",\"third_party_will_handle_the_shipping_responsibilities_when_you_choose_third_party_shipping_method\":\"Third party will handle the shipping responsibilities when you choose third party shipping method\",\"third_party_will_handle_the_shipping_responsibilities_when_you_choose_vendor_wise_shipping_method\":\"Third party will handle the shipping responsibilities when you choose vendor wise shipping method\",\"third_party_delivery\":\"Third party delivery\",\"shipping_method_for_In-house_deliver\":\"Shipping method for In-house deliver\",\"order_wise\":\"Order wise\",\"category_wise\":\"Category wise\",\"product_wise\":\"Product wise\",\"area_wise\":\"Area wise\",\"when_adding_a_product_a_product_specific_shipping_charge_is_added_Verify_that_all_of_the_products_delivery_costs_are_up_to_date\":\"When adding a product a product specific shipping charge is added Verify that all of the products delivery costs are up to date\",\"category_wise_shipping_cost\":\"Category wise shipping cost\",\"category_name\":\"Category name\",\"cost_per_product\":\"Cost per product\",\"add_order_wise_shipping\":\"Add order wise shipping\",\"title\":\"Title\",\"duration\":\"Duration\",\"4_to_6_days\":\"4 to 6 days\",\"cost\":\"Cost\",\"order_wise_shipping_method\":\"Order wise shipping method\",\"want_to_Turn_ON_This_Shipping_Method\":\"Want to Turn ON This Shipping Method\",\"want_to_Turn_OFF_This_Shipping_Method\":\"Want to Turn OFF This Shipping Method\",\"if_you_enable_this_shipping_method_will_be_shown_in_the_user_app_and_website_for_customer_checkout\":\"If you enable this shipping method will be shown in the user app and website for customer checkout\",\"if_you_disable_this_shipping_method_will_not_be_shown_in_the_user_app_and_website_for_customer_checkout\":\"If you disable this shipping method will not be shown in the user app and website for customer checkout\",\"edit\":\"Edit\",\"add_area_wise_shipping\":\"Add area wise shipping\",\"Coordinates\":\"Coordinates\",\"messages.draw_your_zone_on_the_map\":\"Messages.draw your zone on the map\",\"area_wise_shipping_method\":\"Area wise shipping method\",\"Country\":\"Country\",\"State\":\"State\",\"City\":\"City\",\"Area\":\"Area\",\"want_to_Turn_ON_This_Area_Shipping_Method\":\"Want to Turn ON This Area Shipping Method\",\"want_to_Turn_OFF_This_Area_Shipping_Method\":\"Want to Turn OFF This Area Shipping Method\",\"if_you_enable_this_area_shipping_method_will_be_shown_in_the_user_app_and_website_for_customer_checkout\":\"If you enable this area shipping method will be shown in the user app and website for customer checkout\",\"if_you_disable_this_area_shipping_method_will_not_be_shown_in_the_user_app_and_website_for_customer_checkout\":\"If you disable this area shipping method will not be shown in the user app and website for customer checkout\",\"shipping_method_updated_successfully\":\"Shipping method updated successfully\",\"State & City Management\":\"State & City Management\",\"State & City\":\"State & City\",\"Add State\":\"Add State\",\"--Select--\":\"--Select--\",\"State Name\":\"State Name\",\"Enter state\":\"Enter state\",\"Add\":\"Add\",\"Action\":\"Action\",\"Delete\":\"Delete\",\"Add City\":\"Add City\",\"--Select State--\":\"--Select State--\",\"City Name\":\"City Name\",\"Enter city\":\"Enter city\",\"Add Area\":\"Add Area\",\"--Select City--\":\"--Select City--\",\"Area Name\":\"Area Name\",\"Enter Area\":\"Enter Area\",\"delivery_Restriction\":\"Delivery Restriction\",\"delivery_available_country\":\"Delivery available country\",\"if_enabled,_you_can_choose_one_or_multiple_countries_for_product_delivery\":\"If enabled  you can choose one or multiple countries for product delivery\",\"want_to_Turn_ON_Delivery_Available_Country\":\"Want to Turn ON Delivery Available Country\",\"want_to_Turn_OFF_Delivery_Available_Country\":\"Want to Turn OFF Delivery Available Country\",\"if_enabled_the_admin_or_vendor_can_deliver_orders_to_the_selected_countries\":\"If enabled the admin or vendor can deliver orders to the selected countries\",\"if_disabled_there_will_be_no_delivery_restrictions_for_admin\":\"If disabled there will be no delivery restrictions for admin\",\"delivery_available_state\":\"Delivery available state\",\"if_enabled,_you_can_choose_one_or_multiple_states_for_product_delivery\":\"If enabled  you can choose one or multiple states for product delivery\",\"want_to_Turn_ON_Delivery_Available_State\":\"Want to Turn ON Delivery Available State\",\"want_to_Turn_OFF_Delivery_Available_State\":\"Want to Turn OFF Delivery Available State\",\"if_enabled_the_admin_or_vendor_can_deliver_orders_to_the_selected_states\":\"If enabled the admin or vendor can deliver orders to the selected states\",\"delivery_available_city\":\"Delivery available city\",\"if_enabled,_you_can_choose_one_or_multiple_cities_for_product_delivery\":\"If enabled  you can choose one or multiple cities for product delivery\",\"want_to_Turn_ON_Delivery_Available_City\":\"Want to Turn ON Delivery Available City\",\"want_to_Turn_OFF_Delivery_Available_City\":\"Want to Turn OFF Delivery Available City\",\"if_enabled_the_admin_can_deliver_orders_to_the_selected_cities\":\"If enabled the admin can deliver orders to the selected cities\",\"delivery_available_zip_code_area\":\"Delivery available zip code area\",\"if_enabled,_the_zip_code_areas_will_be_available_for_delivery\":\"If enabled  the zip code areas will be available for delivery\",\"Please_Note\":\"Please Note\",\"If_you_don’t_enter_a_specific_zip_code_from_a_country,_that_area_won’t_be_available_for_delivery\":\"If you don’t enter a specific zip code from a country  that area won’t be available for delivery\",\"want_to_Turn_ON_Delivery_Available_Zip_Code_Area\":\"Want to Turn ON Delivery Available Zip Code Area\",\"want_to_Turn_OFF_Delivery_Available_Zip_Code_Area\":\"Want to Turn OFF Delivery Available Zip Code Area\",\"if_enabled_deliveries_will_be_available_only_in_the_added_zip_code_areas\":\"If enabled deliveries will be available only in the added zip code areas\",\"if_disabled_there_will_be_no_delivery_restrictions_based_on_zip_code_areas\":\"If disabled there will be no delivery restrictions based on zip code areas\",\"delivery_available_areas\":\"Delivery available areas\",\"if_enabled,_the_areas_will_be_available_for_delivery\":\"If enabled  the areas will be available for delivery\",\"If_you_don’t_enter_a_specific_areas_from_a_country,_that_area_won’t_be_available_for_delivery\":\"If you don’t enter a specific areas from a country  that area won’t be available for delivery\",\"want_to_Turn_ON_Delivery_Available_Areas\":\"Want to Turn ON Delivery Available Areas\",\"want_to_Turn_OFF_Delivery_Available_Areas\":\"Want to Turn OFF Delivery Available Areas\",\"if_enabled_deliveries_will_be_available_only_in_the_added_areas\":\"If enabled deliveries will be available only in the added areas\",\"if_disabled_there_will_be_no_delivery_restrictions_based_on_areas\":\"If disabled there will be no delivery restrictions based on areas\",\"sl\":\"Sl\",\"country_name\":\"Country name\",\"state_name\":\"State name\",\"city_name\":\"City name\",\"enter_zip_code\":\"Enter zip code\",\"multiple_zip_codes_can_be_inputted_by_comma_separating_or_pressing_enter_button\":\"Multiple zip codes can be inputted by comma separating or pressing enter button\",\"run_eCommerce_business_in_your_country_and_beyond\":\"Run eCommerce business in your country and beyond\",\"how_does_it_work\":\"How does it work\",\"step\":\"Step\",\"enable\":\"Enable\",\"Delivery_Available_Country\":\"Delivery Available Country\",\"if_you_want_to_run_your_business_in_a_specific_country\":\"If you want to run your business in a specific country\",\"choose_Country\":\"Choose Country\",\"Delivery_Available_Zip_Code_Area\":\"Delivery Available Zip Code Area\",\"Enter_Zip_Code\":\"Enter Zip Code\",\"of_the_country_you_have_selected\":\"Of the country you have selected\",\"important_note\":\"Important note\",\"if_both_features_are_disabled,_then_all_places_will_be_available_as_delivery_area\":\"If both features are disabled  then all places will be available as delivery area\",\"If_only_the Delivery_Available_Country feature_is_enabled,_and_you_add_your_preferred_country,_then_you’ll_be_able_to_deliver_all_over_the_country\":\"If only the Delivery Available Country feature is enabled  and you add your preferred country  then you’ll be able to deliver all over the country\",\"If_only_the Delivery_Available_Zip_Code_Area feature_is_enabled,_then_you_will_be_able_to_deliver_on_all_the_zip_code_areas\":\"If only the Delivery Available Zip Code Area feature is enabled  then you will be able to deliver on all the zip code areas\",\"you_cannot_deliver_to_any_specific_country_or_zip_code_areas_if_its_not_added_and_saved\":\"You cannot deliver to any specific country or zip code areas if its not added and saved\",\"enjoy_a_borderless_business_experience_with_\":\"Enjoy a borderless business experience with \",\"got_it\":\"Got it\",\"please_enter_zip_code\":\"Please enter zip code\",\"please_enter_area\":\"Please enter area\",\"Wholesaler_dashboard\":\"Wholesaler dashboard\",\"wholesaler_business_analytics\":\"Wholesaler business analytics\",\"total_wholesale_order\":\"Total wholesale order\",\"total_WHolesaler\":\"Total WHolesaler\",\"Purchase_order\":\"Purchase order\",\"Quotation\":\"Quotation\",\"partials\":\"Partials\",\"top_wholesalers\":\"Top wholesalers\",\"product_Preview\":\"Product Preview\",\"product_details\":\"Product details\",\"view_live\":\"View live\",\"rating\":\"Rating\",\"5\":\"5\",\"star\":\"Star\",\"3\":\"3\",\"2\":\"2\",\"description\":\"Description\",\"total_sold\":\"Total sold\",\"total_sold_amount\":\"Total sold amount\",\"general_information\":\"General information\",\"product_type\":\"Product type\",\"physical\":\"Physical\",\"product_unit\":\"Product unit\",\"current_Stock\":\"Current Stock\",\"product_SKU\":\"Product SKU\",\"Warranty\":\"Warranty\",\"No_Warranty\":\"No Warranty\",\"price_information\":\"Price information\",\"shipping_cost\":\"Shipping cost\",\"tags\":\"Tags\",\"make:\":\"Make:\",\"model:\":\"Model:\",\"year:\":\"Year:\",\"SKU\":\"SKU\",\"variation_wise_price\":\"Variation wise price\",\"stock\":\"Stock\",\"product_SEO_&_meta_data\":\"Product SEO & meta data\",\"meta_image\":\"Meta image\",\"product_video\":\"Product video\",\"video_link\":\"Video link\",\"no_data_to_show\":\"No data to show\",\"Review_ID\":\"Review ID\",\"reviewer\":\"Reviewer\",\"review\":\"Review\",\"Reply\":\"Reply\",\"no_review_found\":\"No review found\",\"rejected_note\":\"Rejected note\",\"0\\/100\":\"0\\/100\",\"product_Bulk_Import\":\"Product Bulk Import\",\"bulk_Import\":\"Bulk Import\",\"instructions\":\"Instructions\",\"download_the_format_file_and_fill_it_with_proper_data.\":\"Download the format file and fill it with proper data.\",\"you_can_download_the_example_file_to_understand_how_the_data_must_be_filled.\":\"You can download the example file to understand how the data must be filled.\",\"once_you_have_downloaded_and_filled_the_format_file\":\"Once you have downloaded and filled the format file\",\"upload_it_in_the_form_below_and_submit.\":\"Upload it in the form below and submit.\",\"after_uploading_products_you_need_to_edit_them_and_set_product_images_and_choices.\":\"After uploading products you need to edit them and set product images and choices.\",\"you_can_get_brand_and_category_id_from_their_list_please_input_the_right_ids.\":\"You can get brand and category id from their list please input the right ids.\",\"you_can_upload_your_product_images_in_product_folder_from_gallery_and_copy_image_path.\":\"You can upload your product images in product folder from gallery and copy image path.\",\"do_not_have_the_template\":\"Do not have the template\",\"download_here\":\"Download here\",\"drag_&_drop_file_or_browse_file\":\"Drag & drop file or browse file\",\"main_Banner\":\"Main Banner\",\"popup_Banner\":\"Popup Banner\",\"footer_Banner\":\"Footer Banner\",\"main_Section_Banner\":\"Main Section Banner\",\"banner\":\"Banner\",\"currently_you_are_managing_banners_for\":\"Currently you are managing banners for\",\"these_saved_data_is_only_applicable_only_for_\":\"These saved data is only applicable only for \",\"if_you_change_theme_from_theme_setup_these_banners_will_not_be_shown_in_changed_theme._You_have_upload_all_the_banners_over_again _according_to_the_new_theme_ratio_and_sizes._If_you_switch_back_to_\":\"If you change theme from theme setup these banners will not be shown in changed theme. You have upload all the banners over again  according to the new theme ratio and sizes. If you switch back to \",\"_again_,_you_will_see_the_saved_data.\":\" again   you will see the saved data.\",\"banner_form\":\"Banner form\",\"banner_type\":\"Banner type\",\"banner_URL\":\"Banner URL\",\"Enter_url\":\"Enter url\",\"resource_type\":\"Resource type\",\"shop\":\"Shop\",\"banner_image\":\"Banner image\",\"banner_Image_ratio_is_not_same_for_all_sections_in_website\":\"Banner Image ratio is not same for all sections in website\",\"please_review_the_ratio_before_upload\":\"Please review the ratio before upload\",\"banner_table\":\"Banner table\",\"add_banner\":\"Add banner\",\"published\":\"Published\",\"Main Banner\":\"Main Banner\",\"if_enabled_this_banner_will_be_available_on_the_website_and_customer_app\":\"If enabled this banner will be available on the website and customer app\",\"if_disabled_this_banner_will_be_hidden_from_the_website_and_customer_app\":\"If disabled this banner will be hidden from the website and customer app\",\"banner_update_form\":\"Banner update form\",\"back\":\"Back\",\"enter_url\":\"Enter url\",\"banner_image_ratio_is_not_same_for_all_sections_in_website\":\"Banner image ratio is not same for all sections in website\",\"Please_review_the_ratio_before_upload\":\"Please review the ratio before upload\",\"Inbox_List\":\"Inbox List\",\"Inbox_list\":\"Inbox list\",\"Select_Date\":\"Select Date\",\"select_status\":\"Select status\",\"All\":\"All\",\"New\":\"New\",\"processing\":\"Processing\",\"converted\":\"Converted\",\"ignored\":\"Ignored\",\"Channel\":\"Channel\",\"select_Channel\":\"Select Channel\",\"form\":\"Form\",\"chat\":\"Chat\",\"social\":\"Social\",\"Choose_First\":\"Choose First\",\"200\":\"200\",\"Filter\":\"Filter\",\"search_by_Name_or_Email_or_Phone\":\"Search by Name or Email or Phone\",\"Bulk_convert\":\"Bulk convert\",\"add_Massage\":\"Add Massage\",\"Subject\":\"Subject\",\"Source\":\"Source\",\"Name\":\"Name\",\"Contact\":\"Contact\",\"Owner\":\"Owner\",\"Received_At\":\"Received At\",\"no_record_found\":\"No record found\",\"User_Suggestion\":\"User Suggestion\",\"Yes_Connect\":\"Yes Connect\",\"Select Department\":\"Select Department\",\"select_department\":\"Select department\",\"Priority\":\"Priority\",\"Select Priority\":\"Select Priority\",\"Low\":\"Low\",\"Medium\":\"Medium\",\"High\":\"High\",\"Urgent\":\"Urgent\",\"Message (Optional)\":\"Message (Optional)\",\"Enter message if any...\":\"Enter message if any...\",\"Select Employee\":\"Select Employee\",\"select_type\":\"Select type\",\"Add Massage\":\"Add Massage\",\"Sender Name\":\"Sender Name\",\"Sender Email\":\"Sender Email\",\"Sender Phone\":\"Sender Phone\",\"Message Type\":\"Message Type\",\"Details\":\"Details\",\"Note\":\"Note\",\"Attachment\":\"Attachment\",\"Close\":\"Close\",\"chatting_Page\":\"Chatting Page\",\"chatting_List\":\"Chatting List\",\"search_customers\":\"Search customers\",\"delivery_Man\":\"Delivery Man\",\"you_have_not_any_conversation_yet\":\"You have not any conversation yet\",\"search_delivery_men\":\"Search delivery men\",\"Filter Events\":\"Filter Events\",\"Task\":\"Task\",\"Call\":\"Call\",\"Activity\":\"Activity\",\"To_Do\":\"To Do\",\"retail_Deal_View\":\"Retail Deal View\",\"Deal Details\":\"Deal Details\",\"Customer Information\":\"Customer Information\",\"Email\":\"Email\",\"Deal Information\":\"Deal Information\",\"Created At\":\"Created At\",\"Employee\":\"Employee\",\"Order Status\":\"Order Status\",\"Related Actions\":\"Related Actions\",\"Upload File\":\"Upload File\",\"activities\":\"Activities\",\"Date\":\"Date\",\"Type\":\"Type\",\"Title\":\"Title\",\"Activity Details\":\"Activity Details\",\"Due Date\":\"Due Date\",\"Add New Note\":\"Add New Note\",\"Content\":\"Content\",\"Enter note\":\"Enter note\",\"Noted At\":\"Noted At\",\"Save Note\":\"Save Note\",\"Existing Notes\":\"Existing Notes\",\"Add\\/Edit Task\":\"Add\\/Edit Task\",\"Enter name\":\"Enter name\",\"Enter description\":\"Enter description\",\"Pending\":\"Pending\",\"Complete\":\"Complete\",\"Save Task\":\"Save Task\",\"Existing Tasks\":\"Existing Tasks\",\"Add New Call\":\"Add New Call\",\"Enter title\":\"Enter title\",\"From\":\"From\",\"To\":\"To\",\"Guests\":\"Guests\",\"Select Guest\":\"Select Guest\",\"Enter location\":\"Enter location\",\"Save Call\":\"Save Call\",\"Existing Calls\":\"Existing Calls\",\"Upload New File\":\"Upload New File\",\"File\":\"File\",\"Upload\":\"Upload\",\"Existing Files\":\"Existing Files\",\"Activity saved successfully!\":\"Activity saved successfully!\",\"Note saved successfully!\":\"Note saved successfully!\",\"Task saved successfully!\":\"Task saved successfully!\",\"Task updated successfully!\":\"Task updated successfully!\",\"Task marked as complete!\":\"Task marked as complete!\",\"Call saved successfully!\":\"Call saved successfully!\",\"File uploaded successfully!\":\"File uploaded successfully!\",\"import_serials\":\"Import serials\",\"active_warranties\":\"Active warranties\",\"expired_warranties\":\"Expired warranties\",\"open_claims\":\"Open claims\",\"recent_claims\":\"Recent claims\",\"claim_number\":\"Claim number\",\"serial\":\"Serial\",\"submitted_at\":\"Submitted at\",\"earning_Reports\":\"Earning Reports\",\"admin_Earning\":\"Admin Earning\",\"vendor_Earning\":\"Vendor Earning\",\"total_earnings\":\"Total earnings\",\"total_In_House_Products\":\"Total In House Products\",\"total_Shop\":\"Total Shop\",\"earning_Statistics\":\"Earning Statistics\",\"total_Earnings\":\"Total Earnings\",\"payment_Statistics\":\"Payment Statistics\",\"payments_Amount\":\"Payments Amount\",\"cash_payments\":\"Cash payments\",\"digital_payments\":\"Digital payments\",\"offline_payments\":\"Offline payments\",\"in-House_Earning\":\"In-House Earning\",\"commission_Earning\":\"Commission Earning\",\"earn_From_Shipping\":\"Earn From Shipping\",\"deliveryman_incentive\":\"Deliveryman incentive\",\"discount_Given\":\"Discount Given\",\"VAT\\/TAX\":\"VAT\\/TAX\",\"refund_Given\":\"Refund Given\",\"total_Earning\":\"Total Earning\",\"cash_Payments\":\"Cash Payments\",\"digital_payment\":\"Digital payment\",\"wallet_payment\":\"Wallet payment\",\"offline_payment\":\"Offline payment\",\"home_page\":\"Home page\",\"trusted_by\":\"Trusted by\",\"why_choose_us\":\"Why choose us\",\"why_join_us\":\"Why join us\",\"client_review\":\"Client review\",\"wholesaler_section\":\"Wholesaler section\",\"download_app\":\"Download app\",\"main_banner\":\"Main banner\",\"Add Banner\":\"Add Banner\",\"Heading\":\"Heading\",\"Paragraph\":\"Paragraph\",\"Button Text\":\"Button Text\",\"Button Link\":\"Button Link\",\"Banner\":\"Banner\",\"Edit Banner\":\"Edit Banner\",\"Image Preview\":\"Image Preview\",\"Upload Image\":\"Upload Image\",\"Update\":\"Update\",\"Save Banner\":\"Save Banner\",\"Section Heading\":\"Section Heading\",\"Hero Heading\":\"Hero Heading\",\"Hero Description\":\"Hero Description\",\"Filter Title\":\"Filter Title\",\"Apply Button Text\":\"Apply Button Text\",\"Make Label\":\"Make Label\",\"Model Label\":\"Model Label\",\"Model Year Label\":\"Model Year Label\",\"Make Placeholder\":\"Make Placeholder\",\"Model Placeholder\":\"Model Placeholder\",\"Model Year Placeholder\":\"Model Year Placeholder\",\"Reset\":\"Reset\",\"heading\":\"Heading\",\"enter_heading\":\"Enter heading\",\"sub_heading\":\"Sub heading\",\"enter_paragraph\":\"Enter paragraph\",\"this_page_is_only_off_and_on_core_product_page_in_home_page_you_can_add_show_product_by_clicking_on_toggle_in_product_list\":\"This page is only off and on core product page in home page you can add show product by clicking on toggle in product list\",\"this_page_is_only_off_and_on_deals_page_in_home_page_you_can_add_deals_in_offer_&_deals_section\":\"This page is only off and on deals page in home page you can add deals in offer & deals section\",\"enter_title\":\"Enter title\",\"subtitle\":\"Subtitle\",\"enter_subtitle\":\"Enter subtitle\",\"Icon\":\"Icon\",\"Color\":\"Color\",\"Animation\":\"Animation\",\"Icon Name\":\"Icon Name\",\"Icon Color\":\"Icon Color\",\"Icon Animation\":\"Icon Animation\",\"Save changes\":\"Save changes\",\"About Page Sections\":\"About Page Sections\",\"subheading\":\"Subheading\",\"tech web\":\"Tech web\",\"Tech Web Company delivered exactly what we needed. Their team was professional,...\":\"Tech Web Company delivered exactly what we needed. Their team was professional ...\",\"Edit\":\"Edit\",\"Our Product Is Best\":\"Our Product Is Best\",\"NISR Is Top Brand\":\"NISR Is Top Brand\",\"content\":\"Content\",\"Who We Are\":\"Who We Are\",\"At NISR , we’ve been powering vehicles with premium batteries and durable tyres...\":\"At NISR   we’ve been powering vehicles with premium batteries and durable tyres...\",\"Battery Services\":\"Battery Services\",\"Installation, maintenance & replacement with expert care.\":\"Installation  maintenance & replacement with expert care.\",\"Car Batteries\":\"Car Batteries\",\"High-performance batteries with long life.\":\"High-performance batteries with long life.\",\"Tyres for Every Terrain\":\"Tyres for Every Terrain\",\"Grip, durability & safety for Indian roads.\":\"Grip  durability & safety for Indian roads.\",\"Our Mission\":\"Our Mission\",\"At NISR, our mission is simple — to power the future with innovation, reliabilit...\":\"At NISR  our mission is simple — to power the future with innovation  reliabilit...\",\"year\":\"Year\",\"1945\":\"1945\",\"Started Small\":\"Started Small\",\"Started as a small shop dealing in automobile batteries.\":\"Started as a small shop dealing in automobile batteries.\",\"1980s\":\"1980s\",\"Tyre Expansion\":\"Tyre Expansion\",\"Expanded into tyre distribution with top-tier brands.\":\"Expanded into tyre distribution with top-tier brands.\",\"N\\/A\":\"N\\/A\",\"2000s\":\"2000s\",\"Dealer Network\":\"Dealer Network\",\"Built nationwide network of dealers and partners.\":\"Built nationwide network of dealers and partners.\",\"2020+\":\"2020+\",\"Digital Growth\":\"Digital Growth\",\"Now embracing digital solutions & e-commerce to reach more customers.\":\"Now embracing digital solutions & e-commerce to reach more customers.\",\"Edit Timeline Section\":\"Edit Timeline Section\",\"Year\":\"Year\",\" Image\":\" Image\",\" Image_Preview\":\" Image Preview\",\"Submit\":\"Submit\",\"Career Page Sections\":\"Career Page Sections\",\"Job Title\":\"Job Title\",\"Delete this item?\":\"Delete this item \",\"Edit Job Opening\":\"Edit Job Opening\",\"Job Description\":\"Job Description\",\"Our_Services\":\"Our Services\",\"No_review_given_yet\":\"No review given yet\",\"login_first_for_next_steps\":\"Login first for next steps\",\"Sign_In\":\"Sign In\",\"enter_email_or_phone\":\"Enter email or phone\",\"please_provide_valid_email_or_phone_number\":\"Please provide valid email or phone number\",\"enter_password\":\"Enter password\",\"forgot_password\":\"Forgot password\",\"Or Sign in with\":\"Or Sign in with\",\"google\":\"Google\",\"facebook\":\"Facebook\",\"Enjoy_New_experience\":\"Enjoy New experience\",\"Please_wait\":\"Please wait\",\"Logging_in_please_wait\":\"Logging in please wait\",\"Server_is_busy_please_wait\":\"Server is busy please wait\",\"Invalid_credentials\":\"Invalid credentials\",\"Something_went_wrong\":\"Something went wrong\",\"frequently_asked_question\":\"Frequently asked question\",\"No_Blogs_Found\":\"No Blogs Found\",\"Currently_no_blog_available_in_this_section\":\"Currently no blog available in this section\",\"digital\":\"Digital\",\"items_found\":\"Items found\",\"sort_by\":\"Sort by\",\"latest\":\"Latest\",\"low_to_High_Price\":\"Low to High Price\",\"High_to_Low_Price\":\"High to Low Price\",\"A_to_Z_Order\":\"A to Z Order\",\"Z_to_A_Order\":\"Z to A Order\",\"show_products\":\"Show products\",\"oldest\":\"Oldest\",\"Make\":\"Make\",\"Select Make\":\"Select Make\",\"Model\":\"Model\",\"Select Model\":\"Select Model\",\"Vehicle Year\":\"Vehicle Year\",\"Select Year\":\"Select Year\",\"Choose\":\"Choose\",\"Best_Selling_Product\":\"Best Selling Product\",\"Top_Rated\":\"Top Rated\",\"Most_Favorite\":\"Most Favorite\",\"Featured_Deal\":\"Featured Deal\",\"Product_Type\":\"Product Type\",\"Sort_By\":\"Sort By\",\"min\":\"Min\",\"max\":\"Max\",\"no_product_found\":\"No product found\",\"register\":\"Register\",\"create_an_account\":\"Create an account\",\"Jhone\":\"Jhone\",\"please_enter_your_first_name\":\"Please enter your first name\",\"Doe\":\"Doe\",\"please_enter_your_last_name\":\"Please enter your last name\",\"please_enter_valid_email_address\":\"Please enter valid email address\",\"minimum_8_characters_long\":\"Minimum 8 characters long\",\"confirm_password\":\"Confirm password\",\"refer_code\":\"Refer code\",\"use_referral_code\":\"Use referral code\",\"got_questions_about_becoming_a_wholesaler\":\"Got questions about becoming a wholesaler\",\"explore_our_wholesaler_FAQ_section_for_answers_to_any_queries_you_may_have_about_joining_our_platform_as_a_wholesaler\":\"Explore our wholesaler FAQ section for answers to any queries you may have about joining our platform as a wholesaler\",\"congratulations\":\"Congratulations\",\"your_registration_is_successful\":\"Your registration is successful\",\"please-wait_for_admin_approval\":\"Please-wait for admin approval\",\" you_will_get_a_mail_soon\":\" you will get a mail soon\",\"want_to_apply_as_a_vendor\":\"Want to apply as a vendor\",\"please_enter_your_email\":\"Please enter your email\",\"please_enter_your_phone_number\":\"Please enter your phone number\",\"please_enter_a_valid_email_address\":\"Please enter a valid email address\",\"please_enter_your_password\":\"Please enter your password\",\"please_enter_your_confirm_password\":\"Please enter your confirm password\",\"passwords_do_not_match\":\"Passwords do not match\",\"discounted\":\"Discounted\",\"pending_activation_reviews\":\"Pending activation reviews\",\"flagged_reason\":\"Flagged reason\",\"Welcome_Admin\":\"Welcome Admin\",\"Inbound Messages\":\"Inbound Messages\",\"New Messages\":\"New Messages\",\"Converted Messages\":\"Converted Messages\",\"Ignored Messages\":\"Ignored Messages\",\"Total Leads\":\"Total Leads\",\"Working Leads\":\"Working Leads\",\"Qualified Leads\":\"Qualified Leads\",\"Converted Leads\":\"Converted Leads\",\"Open Retail Deals\":\"Open Retail Deals\",\"Won Retail Deals\":\"Won Retail Deals\",\"Lost Retail Deals\":\"Lost Retail Deals\",\"Open Wholesale Deals\":\"Open Wholesale Deals\",\"Won Wholesale Deals\":\"Won Wholesale Deals\",\"Lost Wholesale Deals\":\"Lost Wholesale Deals\",\"Tickets section\":\"Tickets section\",\"Support\":\"Support\",\"Complaint\":\"Complaint\",\"Service\":\"Service\",\"Retail\":\"Retail\",\"Wholesale\":\"Wholesale\",\"warranty section \":\"Warranty section \",\"Claims\":\"Claims\",\"Approved\":\"Approved\",\"Active\":\"Active\",\"SLA and Activity\":\"SLA and Activity\",\"Overdue SLAs\":\"Overdue SLAs\",\"Pending Activities\":\"Pending Activities\",\"VoIP Calls Today\":\"VoIP Calls Today\",\" Service Overview\":\" Service Overview\",\"Total Services Completed \":\"Total Services Completed \",\"Total Invoice Amount \":\"Total Invoice Amount \",\"social_Media_Chatting\":\"Social Media Chatting\",\"3rd_party\":\"3rd party\",\"social_media_chat\":\"Social media chat\",\"social_media_login\":\"Social media login\",\"mail_config\":\"Mail config\",\"voip_config\":\"Voip config\",\"SMS_config\":\"SMS config\",\"recaptcha\":\"Recaptcha\",\"google_map_APIs\":\"Google map APIs\",\"storage_connection\":\"Storage connection\",\"Firebase_Auth\":\"Firebase Auth\",\"whatsApp\":\"WhatsApp\",\"want_to_turn_ON_WhatsApp_as_social_media_chat_option\":\"Want to turn ON WhatsApp as social media chat option\",\"want_to_turn_OFF_WhatsApp_as_social_media_chat_option\":\"Want to turn OFF WhatsApp as social media chat option\",\"if_enabled,WhatsApp_chatting_option_will_be_available_in_the_system\":\"If enabled WhatsApp chatting option will be available in the system\",\"if_enabled,WhatsApp_chatting_option_will_be_hidden_from_the_system\":\"If enabled WhatsApp chatting option will be hidden from the system\",\"whatsapp_number\":\"Whatsapp number\",\"provide_a_WhatsApp_number_without_country_code\":\"Provide a WhatsApp number without country code\",\"UCM_API_Config\":\"UCM API Config\",\"how_it_works\":\"How it works\",\"language_change_successfully\":\"Language change successfully\",\"reCAPTCHA verification error\":\"ReCAPTCHA verification error\",\"login_successful\":\"Login successful\",\"user_profile\":\"User profile\",\"Wishlist\":\"Wishlist\",\"hello\":\"Hello\",\"my_Profile\":\"My Profile\",\"featured_deal\":\"Featured deal\",\"see_the_latest_deals_and_exciting_new_offers\":\"See the latest deals and exciting new offers\",\"brands\":\"Brands\",\"My_Shopping_Cart\":\"My Shopping Cart\",\"tax_included\":\"Tax included\",\"total_shipping_cost\":\"Total shipping cost\",\"order_note\":\"Order note\",\"you_have_Saved\":\"You have Saved\",\"discount_on_product\":\"Discount on product\",\"apply\":\"Apply\",\"please_provide_coupon_code\":\"Please provide coupon code\",\"proceed_to_Checkout\":\"Proceed to Checkout\",\"continue_Shopping\":\"Continue Shopping\",\"Successfully_Update\":\"Successfully Update\",\"save_this_Address\":\"Save this Address\",\"Please_remove_this_unavailable_product_for_continue\":\"Please remove this unavailable product for continue\",\"Please_add_or_checked_items_before_proceeding_to_checkout\":\"Please add or checked items before proceeding to checkout\",\"Item_has_been_removed_from_cart\":\"Item has been removed from cart\",\"variant\":\"Variant\",\"come_back_soon\":\"Come back soon\",\"my_Order\":\"My Order\",\"purchase_order\":\"Purchase order\",\"Wholesale Products\":\"Wholesale Products\",\"View All\":\"View All\",\"Add To Purchase Order\":\"Add To Purchase Order\",\"Added to cart!\":\"Added to cart!\",\"Clear_cart\":\"Clear cart\",\"Clear_selected_item\":\"Clear selected item\",\"order_Complete\":\"Order Complete\",\"Purchess_Order_Placed_Successfully\":\"Purchess Order Placed Successfully\",\"thank_you_for_your_order\":\"Thank you for your order\",\"your_purchess_has_been_processed\":\"Your purchess has been processed\",\"our_team_review_your_order_soon_check_in_my_quotation\":\"Our team review your order soon check in my quotation\",\"continue_shopping\":\"Continue shopping\",\"my_Wholesale_Order_List\":\"My Wholesale Order List\",\"Business_info\":\"Business info\",\"my_purchase_order\":\"My purchase order\",\"Quotations\":\"Quotations\",\"want_to_delete_this_account\":\"Want to delete this account\",\"delete_account\":\"Delete account\",\"my_Wholesale_Orders\":\"My Wholesale Orders\",\"Order_List\":\"Order List\",\"Total\":\"Total\",\"items\":\"Items\",\"view_order_details\":\"View order details\",\"please_login_your_account\":\"Please login your account\",\"choose_Payment_Method\":\"Choose Payment Method\",\"payment_method\":\"Payment method\",\"go_back\":\"Go back\",\"select_a_payment_method_to_proceed\":\"Select a payment method to proceed\",\"cash_on_Delivery\":\"Cash on Delivery\",\"pay_via_Wallet\":\"Pay via Wallet\",\"pay_your_bill_using_any_of_the_payment_method_below_and_input_the_required_information_in_the_form\":\"Pay your bill using any of the payment method below and input the required information in the form\",\"select_Payment_Method\":\"Select Payment Method\",\"your_current_balance\":\"Your current balance\",\"order_amount\":\"Order amount\",\"remaining_balance\":\"Remaining balance\",\"you_do_not_have_sufficient_balance_for_pay_this_order!!\":\"You do not have sufficient balance for pay this order!!\",\"Order_Placed_Successfully\":\"Order Placed Successfully\",\"your_payment_has_been_successfully_processed_and_your_order\":\"Your payment has been successfully processed and your order\",\"has_been_placed.\":\"Has been placed.\",\"track_Order\":\"Track Order\",\"Continue_Shopping\":\"Continue Shopping\",\"track_Order_Result\":\"Track Order Result\",\"clear\":\"Clear\",\"order_id\":\"Order id\",\"your_phone_number\":\"Your phone number\",\"enter_your_order_ID_&_phone_number_to_get_delivery_updates\":\"Enter your order ID & phone number to get delivery updates\",\"saved_address\":\"Saved address\",\"Please_select_a_valid_state\":\"Please select a valid state\",\"Please_fill_the_following_fields\":\"Please fill the following fields\",\"branch_stocks\":\"Branch stocks\",\"branch_Stocks\":\"Branch Stocks\",\"Attribute\":\"Attribute\",\"search_by_branch_name_or_product_name\":\"Search by branch name or product name\",\"branch_name\":\"Branch name\",\"product_name\":\"Product name\",\"Current_stock\":\"Current stock\",\"Actions\":\"Actions\",\"View History\":\"View History\",\"Stock Transfer History\":\"Stock Transfer History\",\"Variation\":\"Variation\",\"Current Stock\":\"Current Stock\",\"Quantity\":\"Quantity\",\"Reference\":\"Reference\",\"No transfer history found\":\"No transfer history found\",\"Stock In\":\"Stock In\",\"Stock Out\":\"Stock Out\",\"Received from\":\"Received from\",\"Sent to\":\"Sent to\",\"completed\":\"Completed\",\"No data available\":\"No data available\",\"Transfer_Product_Stock\":\"Transfer Product Stock\",\"transfer_Product_Stock\":\"Transfer Product Stock\",\"stock_Transfer\":\"Stock Transfer\",\"from_branch\":\"From branch\",\"to_branch\":\"To branch\",\"Add_Products\":\"Add Products\",\"Download_Sample_Csv\":\"Download Sample Csv\",\"select_category\":\"Select category\",\"select_variation\":\"Select variation\",\"Add Product\":\"Add Product\",\"Transfer Stock\":\"Transfer Stock\",\"The From Branch is required.\":\"The From Branch is required.\",\"The To Branch is required.\":\"The To Branch is required.\",\"The Transfer Date is required.\":\"The Transfer Date is required.\",\"The Transfer Date must be a valid date.\":\"The Transfer Date must be a valid date.\",\"The Transfer Date cannot be a future date.\":\"The Transfer Date cannot be a future date.\",\"At least one product is required.\":\"At least one product is required.\",\"Products should be an array.\":\"Products should be an array.\",\"Each product must have a valid product.\":\"Each product must have a valid product.\",\"Each product must have a valid category.\":\"Each product must have a valid category.\",\"The selected product does not exist.\":\"The selected product does not exist.\",\"The selected category does not exist.\":\"The selected category does not exist.\",\"Each product must have a quantity.\":\"Each product must have a quantity.\",\"The quantity must be an integer.\":\"The quantity must be an integer.\",\"The quantity must be at least 1.\":\"The quantity must be at least 1.\",\"Stock_Transfer\":\"Stock Transfer\",\"stock_Transfer_List\":\"Stock Transfer List\",\"transfer_New_Product_Stock\":\"Transfer New Product Stock\",\"To Branch\":\"To Branch\",\"Transfer Date\":\"Transfer Date\",\"Category\":\"Category\",\"Qty\":\"Qty\",\"CSV\":\"CSV\",\"Transferred\":\"Transferred\",\"no_csv_file_found\":\"No csv file found\",\"successfully_updated!\":\"Successfully updated!\",\"Installation_Service\":\"Installation Service\",\"Exchange_Service\":\"Exchange Service\",\"The_phone_number_must_be_at_least_4_characters\":\"The phone number must be at least 4 characters\",\"coupon_Add\":\"Coupon Add\",\"coupon_setup\":\"Coupon setup\",\"coupon_type\":\"Coupon type\",\"select_coupon_type\":\"Select coupon type\",\"discount_on_Purchase\":\"Discount on Purchase\",\"free_Delivery\":\"Free Delivery\",\"first_Order\":\"First Order\",\"coupon_title\":\"Coupon title\",\"generate_code\":\"Generate code\",\"select_customer\":\"Select customer\",\"limit_for_same_user\":\"Limit for same user\",\"discount_type\":\"Discount type\",\"percentage\":\"Percentage\",\"discount_Amount\":\"Discount Amount\",\"minimum_purchase\":\"Minimum purchase\",\"maximum_discount\":\"Maximum discount\",\"expire_date\":\"Expire date\",\"coupon_list\":\"Coupon list\",\"search_by_Title_or_Code_or_Discount_Type\":\"Search by Title or Code or Discount Type\",\"user_limit\":\"User limit\",\"discount_bearer\":\"Discount bearer\",\"code\":\"Code\",\"discount on purchase\":\"Discount on purchase\",\"limit\":\"Limit\",\"used\":\"Used\",\"Want_to_Turn_ON_Coupon_Status\":\"Want to Turn ON Coupon Status\",\"Want_to_Turn_OFF_Coupon_Status\":\"Want to Turn OFF Coupon Status\",\"if_enabled_this_coupon_will_be_available_on_the_website_and_customer_app\":\"If enabled this coupon will be available on the website and customer app\",\"if_disabled_this_coupon_will_be_hidden_from_the_website_and_customer_app\":\"If disabled this coupon will be hidden from the website and customer app\",\"select_customer_is_required!\":\"Select customer is required!\",\"limit_for_same_user_is_required!\":\"Limit for same user is required!\",\"discount_type_is_required!\":\"Discount type is required!\",\"discount_amount_is_required!\":\"Discount amount is required!\",\"minimum_purchase_is_required!\":\"Minimum purchase is required!\",\"free delivery\":\"Free delivery\",\"first order\":\"First order\",\"coupon_applied_successfully\":\"Coupon applied successfully\",\"coupon_removed\":\"Coupon removed\",\"import_history\":\"Import history\",\"new_import\":\"New import\",\"warranty_imports\":\"Warranty imports\",\"view_details\":\"View details\",\"import_details\":\"Import details\",\"details_for\":\"Details for\",\"back_to_imports\":\"Back to imports\",\"details\":\"Details\",\"search_by_serial\":\"Search by serial\",\"export_csv\":\"Export csv\",\"serial_number\":\"Serial number\",\"created_at\":\"Created at\",\"activate\":\"Activate\",\"activations_list\":\"Activations list\",\"all_methods\":\"All methods\",\"user_public_form\":\"User public form\",\"admin_manual\":\"Admin manual\",\"method\":\"Method\",\"start_end_date\":\"Start end date\",\"preactivated\":\"Preactivated\",\"View Details\":\"View Details\",\"warranty_import\":\"Warranty import\",\"upload_csv\":\"Upload csv\",\"csv_file\":\"Csv file\",\"columns: serial_number (required), product_id (optional), warranty_months (required)\":\"Columns: serial number (required)  product id (optional)  warranty months (required)\",\"import\":\"Import\",\"Serials imported successfully!\":\"Serials imported successfully!\",\"import_summary\":\"Import summary\",\"created\":\"Created\",\"updated\":\"Updated\",\"product_List\":\"Product List\",\"in_House_Product_List\":\"In House Product List\",\"filter_Products\":\"Filter Products\",\"all_brand\":\"All brand\",\"sub_Category\":\"Sub Category\",\"select_Sub_Category\":\"Select Sub Category\",\"sub_Sub_Category\":\"Sub Sub Category\",\"select_Sub_Sub_Category\":\"Select Sub Sub Category\",\"limited_Stocks\":\"Limited Stocks\",\"add_new_product\":\"Add new product\",\"product Name\":\"Product Name\",\"product Type\":\"Product Type\",\"show_as_featured\":\"Show as featured\",\"show_in_home\":\"Show in home\",\"show_in_showcase\":\"Show in showcase\",\"Want_to_Add\":\"Want to Add\",\"to_the_featured_section\":\"To the featured section\",\"Want_to_Remove\":\"Want to Remove\",\"if_enabled_this_product_will_be_shown_in_the_featured_product_on_the_website_and_customer_app\":\"If enabled this product will be shown in the featured product on the website and customer app\",\"if_disabled_this_product_will_be_removed_from_the_featured_product_section_of_the_website_and_customer_app\":\"If disabled this product will be removed from the featured product section of the website and customer app\",\"Want_to_Show\":\"Want to Show\",\"to_the_home_page\":\"To the home page\",\"Want_to_Hide\":\"Want to Hide\",\"from_the_featured_section\":\"From the featured section\",\"if_enabled_this_product_will_be_shown_in_the_home_page_on_the_website\":\"If enabled this product will be shown in the home page on the website\",\"if_disabled_this_product_will_be_removed_from_the_home_page_on_the_website\":\"If disabled this product will be removed from the home page on the website\",\"to_the_product_page\":\"To the product page\",\"from_the_product_section\":\"From the product section\",\"if_enabled_this_product_will_be_shown_in_the_product_page_on_the_website\":\"If enabled this product will be shown in the product page on the website\",\"if_disabled_this_product_will_be_removed_from_the_product_page_on_the_website\":\"If disabled this product will be removed from the product page on the website\",\"barcode\":\"Barcode\",\"product_Edit\":\"Product Edit\",\"new_Product\":\"New Product\",\"general_setup\":\"General setup\",\"Author\":\"Author\",\"Creator\":\"Creator\",\"Artist\":\"Artist\",\"Publishing_House\":\"Publishing House\",\"delivery_type\":\"Delivery type\",\"for_Ready_Product_deliveries,_customers_can_pay_&_instantly_download_pre-uploaded_digital_products._For_Ready_After_Sale_deliveries,_customers_pay_first,_then_admin_uploads_the_digital_products_that_become_available_to_customers_for_download\":\"For Ready Product deliveries  customers can pay & instantly download pre-uploaded digital products. For Ready After Sale deliveries  customers pay first  then admin uploads the digital products that become available to customers for download\",\"ready_After_Sell\":\"Ready After Sell\",\"ready_Product\":\"Ready Product\",\"create_a_unique_product_code_by_clicking_on_the_Generate_Code_button\":\"Create a unique product code by clicking on the Generate Code button\",\"unit\":\"Unit\",\"search_tags\":\"Search tags\",\"add_the_product_search_tag_for_this_product_that_customers_can_use_to_search_quickly\":\"Add the product search tag for this product that customers can use to search quickly\",\"Add vehicle makes (e.g., BMW, Audi, etc.)\":\"Add vehicle makes (e.g.  BMW  Audi  etc.)\",\"Add vehicle models (e.g., Civic, Phantom, etc.)\":\"Add vehicle models (e.g.  Civic  Phantom  etc.)\",\"Add vehicle years (e.g., 2023, 2024, etc.)\":\"Add vehicle years (e.g.  2023  2024  etc.)\",\"is_warranty\":\"Is warranty\",\"service_details\":\"Service details\",\"e.g. Synthetic Oil Change – up to 5 L\":\"E.g. Synthetic Oil Change – up to 5 L\",\"service_main_title\":\"Service main title\",\"parts_included\":\"Parts included\",\"e.g. 5 L 5W-30, OEM oil filter\":\"E.g. 5 L 5W-30  OEM oil filter\",\"comma separated values\":\"Comma separated values\",\"service_id\":\"Service id\",\"e.g. SRV-OIL-SYN\":\"E.g. SRV-OIL-SYN\",\"base_price_inshop\":\"Base price inshop\",\"e.g. 20\":\"E.g. 20\",\"base_price_mobile\":\"Base price mobile\",\"e.g. 27.5\":\"E.g. 27.5\",\"parts_cost\":\"Parts cost\",\"e.g. 0\":\"E.g. 0\",\"included_km_mobile\":\"Included km mobile\",\"travel_fee_per_km\":\"Travel fee per km\",\"e.g. 1\":\"E.g. 1\",\"labor_hours\":\"Labor hours\",\"e.g. 0.5\":\"E.g. 0.5\",\"call_center_flag\":\"Call center flag\",\"Pricing_&_others\":\"Pricing & others\",\"purchase_price\":\"Purchase price\",\"add_the_purchase_price_for_this_product\":\"Add the purchase price for this product\",\"set_the_selling_price_for_each_unit_of_this_product.\":\"Set the selling price for each unit of this product.\",\"this_Unit_Price_section_would_not_be_applied_if_you_set_a_variation_wise_price.\":\"This Unit Price section would not be applied if you set a variation wise price.\",\"minimum_order_qty\":\"Minimum order qty\",\"set_the_minimum_order_quantity_that_customers_must_choose._Otherwise,_the_checkout_process_would_not_start\":\"Set the minimum order quantity that customers must choose. Otherwise  the checkout process would not start\",\"minimum_order_quantity\":\"Minimum order quantity\",\"current_stock_qty\":\"Current stock qty\",\"add_the_Stock_Quantity_of_this_product_that_will_be_visible_to_customers\":\"Add the Stock Quantity of this product that will be visible to customers\",\"discount_Type\":\"Discount Type\",\"if_Flat_discount_amount_will_be_set_as_fixed_amount.\":\"If Flat discount amount will be set as fixed amount.\",\"if_Percentage_discount_amount_will_be_set_as_percentage.\":\"If Percentage discount amount will be set as percentage.\",\"flat\":\"Flat\",\"discount_amount\":\"Discount amount\",\"add_the_discount_amount_in_percentage_or_a_fixed_value_here\":\"Add the discount amount in percentage or a fixed value here\",\"ex: 5\":\"Ex: 5\",\"tax_amount\":\"Tax amount\",\"set_the_Tax_Amount_in_percentage_here\":\"Set the Tax Amount in percentage here\",\"tax_calculation\":\"Tax calculation\",\"set_the_tax_calculation_method_from_here.\":\"Set the tax calculation method from here.\",\"select_Include_with_product_to_combine_product_price_and_tax_on_the_checkout.\":\"Select Include with product to combine product price and tax on the checkout.\",\"pick_Exclude_from_product_to_display_product_price_and_tax_amount_separately.\":\"Pick Exclude from product to display product price and tax amount separately.\",\"include_with_product\":\"Include with product\",\"exclude_with_product\":\"Exclude with product\",\"set_the_shipping_cost_for_this_product_here._Shipping_cost_will_only_be_applicable_if_product-wise_shipping_is_enabled.\":\"Set the shipping cost for this product here. Shipping cost will only be applicable if product-wise shipping is enabled.\",\"shipping_cost_multiply_with_quantity\":\"Shipping cost multiply with quantity\",\"if_enabled,_the_shipping_charge_will_increase_with_the_product_quantity\":\"If enabled  the shipping charge will increase with the product quantity\",\"product_variation_setup\":\"Product variation setup\",\"File_Type\":\"File Type\",\"audio\":\"Audio\",\"video\":\"Video\",\"document\":\"Document\",\"software\":\"Software\",\"select_colors\":\"Select colors\",\"select_attributes\":\"Select attributes\",\"product_thumbnail\":\"Product thumbnail\",\"add_your_products_thumbnail_in\":\"Add your products thumbnail in\",\"format_within\":\"Format within\",\"Upload_Image\":\"Upload Image\",\"image_format\":\"Image format\",\"image_size\":\"Image size\",\"colour_wise_product_image\":\"Colour wise product image\",\"add_color-wise_product_images_here\":\"Add color-wise product images here\",\"must_upload_colour_wise_images_first.\":\"Must upload colour wise images first.\",\"Colour_is_shown_in_the_image_section_top_right\":\"Colour is shown in the image section top right\",\"upload_additional_image\":\"Upload additional image\",\"upload_any_additional_images_for_this_product_from_here\":\"Upload any additional images for this product from here\",\"upload_additional_product_images\":\"Upload additional product images\",\"Product_Preview_File\":\"Product Preview File\",\"upload_a_suitable_file_for_a_short_product_preview.\":\"Upload a suitable file for a short product preview.\",\"this_preview_will_be_common_for_all_variations.\":\"This preview will be common for all variations.\",\"Upload_a_short_preview\":\"Upload a short preview\",\"Upload_File\":\"Upload File\",\"Format\":\"Format\",\"add_the_YouTube_video_link_here._Only_the_YouTube-embedded_link_is_supported\":\"Add the YouTube video link here. Only the YouTube-embedded link is supported\",\"youtube_video_link\":\"Youtube video link\",\"optional_please_provide_embed_link_not_direct_link\":\"Optional please provide embed link not direct link\",\"seo_section\":\"Seo section\",\"add_meta_titles_descriptions_and_images_for_products\":\"Add meta titles descriptions and images for products\",\"this_will_help_more_people_to_find_them_on_search_engines_and_see_the_right_details_while_sharing_on_other_social_platforms\":\"This will help more people to find them on search engines and see the right details while sharing on other social platforms\",\"meta_Title\":\"Meta Title\",\"add_the_products_title_name_taglines_etc_here\":\"Add the products title name taglines etc here\",\"this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms\":\"This title will be seen on Search Engine Results Pages and while sharing the products link on social platforms\",\"character_Limit\":\"Character Limit\",\"meta_Description\":\"Meta Description\",\"write_a_short_description_of_the_InHouse_shops_product\":\"Write a short description of the InHouse shops product\",\"this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_products_link_on_social_platforms\":\"This description will be seen on Search Engine Results Pages and while sharing the products link on social platforms\",\"meta_Image\":\"Meta Image\",\"add_Meta_Image_in\":\"Add Meta Image in\",\"which_will_be_shown_in_search_engine_results\":\"Which will be shown in search engine results\",\"Index\":\"Index\",\"allow_search_engines_to_put_this_web_page_on_their_list_or_index_and_show_it_on_search_results.\":\"Allow search engines to put this web page on their list or index and show it on search results.\",\"No_Follow\":\"No Follow\",\"instruct_search_engines_not_to_follow_links_from_this_web_page.\":\"Instruct search engines not to follow links from this web page.\",\"No_Image_Index\":\"No Image Index\",\"prevents_images_from_being_listed_or_indexed_by_search_engines\":\"Prevents images from being listed or indexed by search engines\",\"no_index\":\"No index\",\"disallow_search_engines_to_put_this_web_page_on_their_list_or_index_and_do_not_show_it_on_search_results.\":\"Disallow search engines to put this web page on their list or index and do not show it on search results.\",\"No_Archive\":\"No Archive\",\"instruct_search_engines_not_to_display_this_webpages_cached_or_saved_version.\":\"Instruct search engines not to display this webpages cached or saved version.\",\"No_Snippet\":\"No Snippet\",\"instruct_search_engines_not_to_show_a_summary_or_snippet_of_this_webpages_content_in_search_results.\":\"Instruct search engines not to show a summary or snippet of this webpages content in search results.\",\"max_Snippet\":\"Max Snippet\",\"determine_the_maximum_length_of_a_snippet_or_preview_text_of_the_webpage.\":\"Determine the maximum length of a snippet or preview text of the webpage.\",\"max_Video_Preview\":\"Max Video Preview\",\"determine_the_maximum_duration_of_a_video_preview_that_search_engines_will_display\":\"Determine the maximum duration of a video preview that search engines will display\",\"max_Image_Preview\":\"Max Image Preview\",\"determine_the_maximum_size_or_dimensions_of_an_image_preview_that_search_engines_will_display.\":\"Determine the maximum size or dimensions of an image preview that search engines will display.\",\"large\":\"Large\",\"medium\":\"Medium\",\"small\":\"Small\",\"enter_choice_values\":\"Enter choice values\",\"upload_Image\":\"Upload Image\",\"want_to_update_this_product\":\"Want to update this product\",\"product_added_successfully\":\"Product added successfully\",\"the_discount_price_will_not_larger_then_Variant_Price\":\"The discount price will not larger then Variant Price\",\"Select\":\"Select\",\"please_ensure_your_code_does_not_exceed_20_characters\":\"Please ensure your code does not exceed 20 characters\",\"code_with_a_minimum_length_requirement_of_6_characters\":\"Code with a minimum length requirement of 6 characters\",\"product_updated_successfully\":\"Product updated successfully\",\"Available\":\"Available\",\"Exchange\":\"Exchange\",\"exchange_charge\":\"Exchange charge\",\"installation_charge\":\"Installation charge\",\"delivery_man_incentive\":\"Delivery man incentive\",\"encourage_your_deliveryman_by_giving_him_incentive\":\"Encourage your deliveryman by giving him incentive\",\"this_amount_will_be_count_as_admin_expense\":\"This amount will be count as admin expense\",\"expected_delivery_date\":\"Expected delivery date\",\"expected_delivery_date_added_successfully\":\"Expected delivery date added successfully\",\"Packaging\":\"Packaging\",\"out for delivery\":\"Out for delivery\",\"no_shipping_address_found\":\"No shipping address found\",\"order_delivered\":\"Order delivered\",\"my_order\":\"My order\",\"Restock_Requests\":\"Restock Requests\",\"my_wallet\":\"My wallet\",\"my_loyalty_point\":\"My loyalty point\",\"my_Address\":\"My Address\",\"refer_&_earn\":\"Refer & earn\",\"coupons\":\"Coupons\",\"profile_Info\":\"Profile Info\",\"new_password\":\"New password\",\"order_Transactions\":\"Order Transactions\",\"transaction_report\":\"Transaction report\",\"expense_Transactions\":\"Expense Transactions\",\"refund_Transactions\":\"Refund Transactions\",\"all_status\":\"All status\",\"disburse\":\"Disburse\",\"hold\":\"Hold\",\"total_Orders\":\"Total Orders\",\"in_House_Orders\":\"In House Orders\",\"vendor_Orders\":\"Vendor Orders\",\"in_House_Products\":\"In House Products\",\"total_Stores\":\"Total Stores\",\"order_Statistics\":\"Order Statistics\",\"total_order_amount\":\"Total order amount\",\"completed_payments\":\"Completed payments\",\"total_Transactions\":\"Total Transactions\",\"search_by_orders_id\":\"Search by orders id\",\"download_PDF\":\"Download PDF\",\"shop_name\":\"Shop name\",\"customer_name\":\"Customer name\",\"total_product_amount\":\"Total product amount\",\"product_discount\":\"Product discount\",\"discounted_amount\":\"Discounted amount\",\"shipping_charge\":\"Shipping charge\",\"delivered_by\":\"Delivered by\",\"admin_discount\":\"Admin discount\",\"vendor_discount\":\"Vendor discount\",\"admin_commission\":\"Admin commission\",\"admin_net_income\":\"Admin net income\",\"vendor_net_income\":\"Vendor net income\",\"not_found\":\"Not found\",\"cash_payment\":\"Cash payment\",\"in-House\":\"In-House\",\"this_count_is_the_summation_of\":\"This count is the summation of\",\"failed_to_deliver\":\"Failed to deliver\",\"and\":\"And\",\"returned_orders\":\"Returned orders\",\"ongoing\":\"Ongoing\",\"out_for_delivery_orders\":\"Out for delivery orders\",\"this_count_is_the_summation_of_delivered_orders\":\"This count is the summation of delivered orders\",\"total_Order_Amount\":\"Total Order Amount\",\"due_Amount\":\"Due Amount\",\"the_ongoing_order_amount_will_be_shown_here\":\"The ongoing order amount will be shown here\",\"already_Settled\":\"Already Settled\",\"after_the_order_is_delivered_total_order_amount_will_be_shown_here\":\"After the order is delivered total order amount will be shown here\",\"total_settled_amount\":\"Total settled amount\",\"payments\":\"Payments\",\"digital_Payments\":\"Digital Payments\",\"search_by_order_id\":\"Search by order id\",\"Download_PDF\":\"Download PDF\",\"total_Amount\":\"Total Amount\",\"shipping_Charge\":\"Shipping Charge\",\"my_Order_List\":\"My Order List\",\"download_invoice\":\"Download invoice\",\"order_summary\":\"Order summary\",\"vendor_info\":\"Vendor info\",\"delivery_man_info\":\"Delivery man info\",\"warranty_and_support\":\"Warranty and support\",\"reorder\":\"Reorder\",\"payment_info\":\"Payment info\",\"order_details\":\"Order details\",\"refund\":\"Refund\",\"Total_Item\":\"Total Item\",\"tax_fee\":\"Tax fee\",\"shipping_Fee\":\"Shipping Fee\",\"Activate Warranty\":\"Activate Warranty\",\"Serial No\":\"Serial No\",\"Enter serial number\":\"Enter serial number\",\"I have read and agree to the\":\"I have read and agree to the\",\"Warranty Policy\":\"Warranty Policy\",\"submit_a_review\":\"Submit a review\",\"rate_the_quality\":\"Rate the quality\",\"have_thoughts_to_share\":\"Have thoughts to share\",\"best_product,_highly_recommended\":\"Best product  highly recommended\",\"upload_images\":\"Upload images\",\"great\":\"Great\",\"refund_request\":\"Refund request\",\"total_refundable_amount\":\"Total refundable amount\",\"give_a_refund_reason\":\"Give a refund reason\",\"write_here\":\"Write here\",\"send_request\":\"Send request\",\"refund_details\":\"Refund details\",\"the_delivery_service_is_good\":\"The delivery service is good\",\"very_Good\":\"Very Good\",\"this_delivery_service_is_very_good_I_am_highly_impressed\":\"This delivery service is very good I am highly impressed\",\"best_delivery_service_highly_recommended\":\"Best delivery service highly recommended\",\"please_rate_the_quality\":\"Please rate the quality\",\"The_comment_is_required\":\"The comment is required\",\"successfully_added_review\":\"Successfully added review\",\"Update_Review\":\"Update Review\",\"warranty_Lookup\":\"Warranty Lookup\",\"Warranty Activate\":\"Warranty Activate\",\"Warranty Lookup\":\"Warranty Lookup\",\"Enter your Serial Number and Contact to receive an OTP and verify your warranty details.\":\"Enter your Serial Number and Contact to receive an OTP and verify your warranty details.\",\"warranty_form\":\"Warranty form\",\"Warranty Activation\":\"Warranty Activation\",\"Serial Number\":\"Serial Number\",\"Purchase Date\":\"Purchase Date\",\"Retailer Source\":\"Retailer Source\",\"Select Branch\":\"Select Branch\",\"Enter Distributor Name\":\"Enter Distributor Name\",\"-- Select Branch --\":\"-- Select Branch --\",\"Distributor \\/ Retailer name\":\"Distributor \\/ Retailer name\",\"Full Name\":\"Full Name\",\"Invoice Number\":\"Invoice Number\",\"Receipt\\/Proof of Purchase\":\"Receipt\\/Proof of Purchase\",\"warranty_Policy\":\"Warranty Policy\",\"Version\":\"Version\",\"Effective Date\":\"Effective Date\",\"Verify OTP\":\"Verify OTP\",\"An OTP has been sent to\":\"An OTP has been sent to\",\"Enter OTP\":\"Enter OTP\",\"Verify\":\"Verify\",\"You can resend OTP in\":\"You can resend OTP in\",\"Resend OTP\":\"Resend OTP\",\"Back to Form\":\"Back to Form\",\"Invalid OTP. Please try again.\":\"Invalid OTP. Please try again.\",\"Warranty Activated Successfully!\":\"Warranty Activated Successfully!\",\"Valid Until\":\"Valid Until\",\"A confirmation has been sent to your email.\":\"A confirmation has been sent to your email.\",\"Back to Home\":\"Back to Home\",\"Track_warranty\":\"Track warranty\",\"Enter Serial Number\":\"Enter Serial Number\",\"Email or Phone Number\":\"Email or Phone Number\",\"must_use_country_code_before_phone_number\":\"Must use country code before phone number\",\"Send OTP\":\"Send OTP\",\"chat_with_vendor\":\"Chat with vendor\",\"no_delivery_man_assigned_yet\":\"No delivery man assigned yet\",\"rate_the_delivery_quality\":\"Rate the delivery quality\",\"best_delivery_service,_highly_recommended\":\"Best delivery service  highly recommended\",\"update_review\":\"Update review\",\"my_review\":\"My review\",\"no_warranty\":\"No warranty\",\"create_support_ticket\":\"Create support ticket\",\"activate_warranty\":\"Activate warranty\",\"serial_no\":\"Serial no\",\"you_can_fill_one_or_more_serial_numbers\":\"You can fill one or more serial numbers\",\"I_have_read_and_agree_to_the\":\"I have read and agree to the\",\"warranty_policy\":\"Warranty policy\",\"scan_serial_number\":\"Scan serial number\",\"describe_your_issue\":\"Describe your issue\",\"write_your_message\":\"Write your message\",\"submit_a_ticket\":\"Submit a ticket\",\"enter_serial_number\":\"Enter serial number\",\"remaining_serial_numbers_to_activate\":\"Remaining serial numbers to activate\",\"scan_barcode_or_qr\":\"Scan barcode or qr\",\"camera_is_not_available_on_this_device\":\"Camera is not available on this device\",\"scanner_failed_to_load\":\"Scanner failed to load\",\"no_camera_found\":\"No camera found\",\"unable_to_start_camera_scanner\":\"Unable to start camera scanner\",\"all_warranty_units_for_this_item_are_already_activated\":\"All warranty units for this item are already activated\",\"Verification_Code\":\"Verification Code\",\"your_order\":\"Your order\",\"activated\":\"Activated\",\"no_serial_number_could_be_activated\":\"No serial number could be activated\",\"support_ticket_created_successfully\":\"Support ticket created successfully\",\"chat_with_delivery_man\":\"Chat with delivery man\",\"Send_Message_to_Deliveryman\":\"Send Message to Deliveryman\",\"delivery_man_review\":\"Delivery man review\",\"type_something\":\"Type something\",\"the_image_format_is_not_supported\":\"The image format is not supported\",\"supported_format_are\":\"Supported format are\",\"image_maximum_size_\":\"Image maximum size \",\"the_file_format_is_not_supported\":\"The file format is not supported\",\"file_maximum_size_\":\"File maximum size \",\"deliveryman\":\"Deliveryman\",\"write_your_message_here\":\"Write your message here\",\"sorry\":\"Sorry\",\"currently_we_are_not_available.\":\"Currently we are not available.\",\"but_you_can_ask_or_still_message_us.\":\"But you can ask or still message us.\",\"We_will_get_back_to_you_soon.\":\"We will get back to you soon.\",\"Thank_you_for_your_patience.\":\"Thank you for your patience.\",\"refund_requested_successful!!\":\"Refund requested successful!!\",\"refund_pending\":\"Refund pending\",\"Warranty Details\":\"Warranty Details\",\"Back to List\":\"Back to List\",\"Warranty Information\":\"Warranty Information\",\"Warranty Duration\":\"Warranty Duration\",\"months\":\"Months\",\"Start Date\":\"Start Date\",\"End Date\":\"End Date\",\"Remaining Days\":\"Remaining Days\",\"Activation Method\":\"Activation Method\",\"Customer Details\":\"Customer Details\",\"Activated IP\":\"Activated IP\",\"Activity Timeline\":\"Activity Timeline\",\"Date & Time\":\"Date & Time\",\"Event\":\"Event\",\"complaint_ticket\":\"Complaint ticket\",\"search_ticket_by_subject_or_status\":\"Search ticket by subject or status\",\"all_Priority\":\"All Priority\",\"low\":\"Low\",\"high\":\"High\",\"urgent\":\"Urgent\",\"all_Status\":\"All Status\",\"Open\":\"Open\",\"Assigned\":\"Assigned\",\"In Progress\":\"In Progress\",\"Waiting\":\"Waiting\",\"Resolved\":\"Resolved\",\"Export\":\"Export\",\"Customer\":\"Customer\",\"Source ID\":\"Source ID\",\"no_support_ticket_found\":\"No support ticket found\",\"Support Follow Up\":\"Support Follow Up\",\"Select Status\":\"Select Status\",\"Next Follow-Up Date\":\"Next Follow-Up Date\",\"Enter follow-up note\":\"Enter follow-up note\",\"Update Follow Up\":\"Update Follow Up\",\"Escalate Ticket\":\"Escalate Ticket\",\"Escalation Reason\":\"Escalation Reason\",\"Explain why this ticket needs escalation (e.g., limited access, department intervention required)...\":\"Explain why this ticket needs escalation (e.g.  limited access  department intervention required)...\",\"Escalate\":\"Escalate\",\"Customer Not Found\":\"Customer Not Found\",\"View\":\"View\",\"Chat\":\"Chat\",\"Re-Assign Employee\":\"Re-Assign Employee\",\"change_Status\":\"Change Status\",\"Triage\":\"Triage\",\"InProgress\":\"InProgress\",\"support_list\":\"Support list\",\"open\":\"Open\",\"won\":\"Won\",\"lost\":\"Lost\",\"Converted_At\":\"Converted At\",\"Department\":\"Department\",\"Escalate Deal\":\"Escalate Deal\",\"Explain why this deal needs escalation...\":\"Explain why this deal needs escalation...\",\"Convert Lead to Deal\":\"Convert Lead to Deal\",\"Party Type\":\"Party Type\",\"Search Party\":\"Search Party\",\"Type to search...\":\"Type to search...\",\"Select Order\":\"Select Order\",\"Deal Value\":\"Deal Value\",\"Convert\":\"Convert\",\"Are you sure?\":\"Are you sure \",\"This will notify the department and owner.\":\"This will notify the department and owner.\",\"Yes, Escalate\":\"Yes  Escalate\",\"Assign Owner\":\"Assign Owner\",\"Assign Employee\":\"Assign Employee\",\"Leads\":\"Leads\",\"Working\":\"Working\",\"Qualified\":\"Qualified\",\"Disqualified\":\"Disqualified\",\"Converted\":\"Converted\",\"Party_Type\":\"Party Type\",\"Party_Name\":\"Party Name\",\"Assign Department\":\"Assign Department\",\"Disqualify\":\"Disqualify\",\"Merge\":\"Merge\",\"Escalate Lead\":\"Escalate Lead\",\"Explain why this lead needs escalation (e.g., limited access, department intervention required)...\":\"Explain why this lead needs escalation (e.g.  limited access  department intervention required)...\",\"Loading...\":\"Loading...\",\"No Orders Found\":\"No Orders Found\",\"Error loading orders\":\"Error loading orders\",\"career_Ticket\":\"Career Ticket\",\"career_tickets\":\"Career tickets\",\"all_priority\":\"All priority\",\"Screening\":\"Screening\",\"Interview\":\"Interview\",\"Offer\":\"Offer\",\"Hired\":\"Hired\",\"Rejected\":\"Rejected\",\"all_talent_pool\":\"All talent pool\",\"talent_pool_yes\":\"Talent pool yes\",\"talent_pool_no\":\"Talent pool no\",\"Career_Ticket_List\":\"Career Ticket List\",\"candidate\":\"Candidate\",\"recruiter\":\"Recruiter\",\"no_career_ticket_found\":\"No career ticket found\",\"assign_recruiter\":\"Assign recruiter\",\"priority\":\"Priority\",\"assign\":\"Assign\",\"screen_candidate\":\"Screen candidate\",\"notes\":\"Notes\",\"qualified\":\"Qualified\",\"reason_code\":\"Reason code\",\"schedule_interview\":\"Schedule interview\",\"scheduled_at\":\"Scheduled at\",\"panel\":\"Panel\",\"schedule\":\"Schedule\",\"conduct_interview\":\"Conduct interview\",\"outcome\":\"Outcome\",\"pass\":\"Pass\",\"fail\":\"Fail\",\"no_show\":\"No show\",\"attach_signed_offer\":\"Attach signed offer\",\"offer_file\":\"Offer file\",\"attach\":\"Attach\",\"decline_offer\":\"Decline offer\",\"reason\":\"Reason\",\"reject_candidate\":\"Reject candidate\",\"closure_message\":\"Closure message\",\"reject\":\"Reject\",\"add_to_talent_pool\":\"Add to talent pool\",\"consent\":\"Consent\",\"recontact_date\":\"Recontact date\",\"add\":\"Add\",\"service_Ticket\":\"Service Ticket\",\"service_ticket\":\"Service ticket\",\"Scheduled\":\"Scheduled\",\"Completed\":\"Completed\",\"Service_Ticket_List\":\"Service Ticket List\",\"Created_At\":\"Created At\",\"no_service_ticket_found\":\"No service ticket found\",\"Assign Ticket\":\"Assign Ticket\",\"Technician\":\"Technician\",\"SLA (Hours)\":\"SLA (Hours)\",\"Assign\":\"Assign\",\"Create Estimate\":\"Create Estimate\",\"Service Mode\":\"Service Mode\",\"In-shop\":\"In-shop\",\"Mobile\":\"Mobile\",\"Base Price (In-shop)\":\"Base Price (In-shop)\",\"Parts Cost\":\"Parts Cost\",\"Labor Charge\":\"Labor Charge\",\"Subtotal\":\"Subtotal\",\"Base Price (Mobile)\":\"Base Price (Mobile)\",\"Travel Fee per KM\":\"Travel Fee per KM\",\"Travel Free up to (KM)\":\"Travel Free up to (KM)\",\"Enter KM\":\"Enter KM\",\"Extra Charge\":\"Extra Charge\",\"Schedule Ticket\":\"Schedule Ticket\",\"Scheduled Date\\/Time\":\"Scheduled Date\\/Time\",\"Schedule\":\"Schedule\",\"Start Job\":\"Start Job\",\"GPS Coordinates\":\"GPS Coordinates\",\"Odometer Reading\":\"Odometer Reading\",\"Upload Images\":\"Upload Images\",\"Complete Job\":\"Complete Job\",\"Odometer End\":\"Odometer End\",\"Remarks\":\"Remarks\",\"e.g., Job completed successfully\":\"E.g.  Job completed successfully\",\"Attachments\":\"Attachments\",\"Customer Signature\":\"Customer Signature\",\"Parts and Labor\":\"Parts and Labor\",\"Part\":\"Part\",\"Labor\":\"Labor\",\"Rate\":\"Rate\",\"Add Item\":\"Add Item\",\"Create Change Order\":\"Create Change Order\",\"Additional Charges\":\"Additional Charges\",\"QA Confirmation\":\"QA Confirmation\",\"QA Result\":\"QA Result\",\"Passed\":\"Passed\",\"Failed\":\"Failed\",\"QA Notes\":\"QA Notes\",\"Submit QA\":\"Submit QA\",\"Close Ticket\":\"Close Ticket\",\"Cancel Ticket\":\"Cancel Ticket\",\"Cancellation Reason\":\"Cancellation Reason\",\"e.g., Customer no-show\":\"E.g.  Customer no-show\",\"Fee Amount\":\"Fee Amount\",\"Refund Amount\":\"Refund Amount\",\"This action cannot be undone.\":\"This action cannot be undone.\",\"Yes\":\"Yes\",\"no_job_associated\":\"No job associated\",\"invalid_action\":\"Invalid action\",\"Item Name\":\"Item Name\",\"retail_ticket\":\"Retail ticket\",\"Cancelled\":\"Cancelled\",\"Return Requested\":\"Return Requested\",\"RMA Issued\":\"RMA Issued\",\"RMA Received\":\"RMA Received\",\"Refund Approved\":\"Refund Approved\",\"Refund Rejected\":\"Refund Rejected\",\"Refund Posted\":\"Refund Posted\",\"Ticket_Type\":\"Ticket Type\",\"follow_Up\":\"Follow Up\",\"select_ticket_status\":\"Select ticket status\",\"Next_Follow_Up_Date\":\"Next Follow Up Date\",\"When_remainder_day\":\"When remainder day\",\"in_day\":\"In day\",\"remainder_interval\":\"Remainder interval\",\"in_hrs\":\"In hrs\",\"remainder_cycle\":\"Remainder cycle\",\"enter_follow_up_note\":\"Enter follow up note\",\"No Status\":\"No Status\",\"ticket_details\":\"Ticket details\",\"ticket_information\":\"Ticket information\",\"sub_type\":\"Sub type\",\"assigned_employee\":\"Assigned employee\",\"reopen_count\":\"Reopen count\",\"activity_log\":\"Activity log\",\"no_activity_logged\":\"No activity logged\",\"employee\":\"Employee\",\"noted_at\":\"Noted at\",\"support_Ticket\":\"Support Ticket\",\"leave_a_Message\":\"Leave a Message\",\"send_Reply\":\"Send Reply\",\"The_file_must_be_an_image\":\"The file must be an image\",\"department_updated_successfully\":\"Department updated successfully\",\"wholesale_ticket\":\"Wholesale ticket\",\"Wholesale Follow Up\":\"Wholesale Follow Up\",\"expense_transaction\":\"Expense transaction\",\"this_year\":\"This year\",\"this_month\":\"This month\",\"this_week\":\"This week\",\"custom_date\":\"Custom date\",\"start Date\":\"Start Date\",\"end Date\":\"End Date\",\"total_Expense\":\"Total Expense\",\"coupon_discount_will_be_shown_here\":\"Coupon discount will be shown here\",\"discount_on_purchase_and_first_delivery_coupon_amount_will_be_shown_here\":\"Discount on purchase and first delivery coupon amount will be shown here\",\"expense_Statistics\":\"Expense Statistics\",\"total_expense_amount\":\"Total expense amount\",\"search_by_Order_ID_or_Transaction_ID\":\"Search by Order ID or Transaction ID\",\"XID\":\"XID\",\"transaction_Date\":\"Transaction Date\",\"expense_Amount\":\"Expense Amount\",\"expense_Type\":\"Expense Type\",\"refund_transactions\":\"Refund transactions\",\"total_transaction\":\"Total transaction\",\"search_by_orders_id_or_refund_id\":\"Search by orders id or refund id\",\"digitally_paid\":\"Digitally paid\",\"refund_id\":\"Refund id\",\"paid_by\":\"Paid by\",\"transaction_type\":\"Transaction type\",\"order_Transaction_Statement\":\"Order Transaction Statement\",\"vendor_Info\":\"Vendor Info\",\"customer_Info\":\"Customer Info\",\"delivered_By\":\"Delivered By\",\"total_Product_Amount\":\"Total Product Amount\",\"discounted_Amount\":\"Discounted Amount\",\"delivery_Charge\":\"Delivery Charge\",\"order_Amount\":\"Order Amount\",\"additional_information\":\"Additional information\",\"totals\":\"Totals\",\"admin_Discount\":\"Admin Discount\",\"vendor_Discount\":\"Vendor Discount\",\"admin_Commission\":\"Admin Commission\",\"admin_Net_Income\":\"Admin Net Income\",\"vendor_Net_Income\":\"Vendor Net Income\",\"all_copy_right_reserved_©_2026_\":\"All copy right reserved © 2026 \",\"wishlist_sort_by_(low_to_high)\":\"Wishlist sort by (low to high)\",\"wishlist_sort_by_(high_to_low)\":\"Wishlist sort by (high to low)\",\"search_Product_Name\":\"Search Product Name\",\"total_in_Wishlist\":\"Total in Wishlist\",\"customer_list\":\"Customer list\",\"Order_Date\":\"Order Date\",\"Customer_Joining_Date\":\"Customer Joining Date\",\"Customer_Status\":\"Customer Status\",\"Inactive\":\"Inactive\",\"Select_Customer_sorting_order\":\"Select Customer sorting order\",\"Sort_By_Order_Amount\":\"Sort By Order Amount\",\"Sort_By_Oldest\":\"Sort By Oldest\",\"Sort_By_Newest\":\"Sort By Newest\",\"100\":\"100\",\"Customer_list\":\"Customer list\",\"contact_info\":\"Contact info\",\"total_Order\":\"Total Order\",\"block\":\"Block\",\"unblock\":\"Unblock\",\"want_to_unblock\":\"Want to unblock\",\"want_to_block\":\"Want to block\",\"if_enabled_this_customer_will_be_unblocked_and_can_log_in_to_this_system_again\":\"If enabled this customer will be unblocked and can log in to this system again\",\"if_disabled_this_customer_will_be_blocked_and_cannot_log_in_to_this_system\":\"If disabled this customer will be blocked and cannot log in to this system\",\"customer_Details\":\"Customer Details\",\"customer_details\":\"Customer details\",\"joined_date\":\"Joined date\",\"total_orders\":\"Total orders\",\"search_orders\":\"Search orders\",\"order_Status\":\"Order Status\",\"claims\":\"Claims\",\"claims_list\":\"Claims list\",\"replacement_pending\":\"Replacement pending\",\"qc_pending\":\"Qc pending\",\"shipped_ready\":\"Shipped ready\",\"dispatched\":\"Dispatched\",\"waiting_customer\":\"Waiting customer\",\"waiting_parts\":\"Waiting parts\",\"waiting_payment\":\"Waiting payment\",\"search_by_claim_or_serial\":\"Search by claim or serial\",\"sla_due\":\"Sla due\",\"Close Claim\":\"Close Claim\",\"Resolution Notes\":\"Resolution Notes\",\"Wholesaler Order Requests\":\"Wholesaler Order Requests\",\"Wholesaler_Purchase_Requests\":\"Wholesaler Purchase Requests\",\"Date From\":\"Date From\",\"Date To\":\"Date To\",\"Apply Filters\":\"Apply Filters\",\"Search...\":\"Search...\",\"DATE\":\"DATE\",\"Purchase_order_no\":\"Purchase order no\",\"Wholesaler\":\"Wholesaler\",\"Tier\":\"Tier\",\"Assign Purchase Order No\":\"Assign Purchase Order No\",\"Purchase Order No\":\"Purchase Order No\",\"Purchase Order number assigned successfully.\":\"Purchase Order number assigned successfully.\",\"purchase_request_view\":\"Purchase request view\",\"Quotation No\":\"Quotation No\",\"Wholeseller\":\"Wholeseller\",\"Wholeseller Tier\":\"Wholeseller Tier\",\"Product Name\":\"Product Name\",\"Requested Qty\":\"Requested Qty\",\"Base Price\":\"Base Price\",\"Tax\":\"Tax\",\"Final Price\":\"Final Price\",\"Purchase_order_quotation\":\"Purchase order quotation\",\"Order Request\":\"Order Request\",\"Purchase Order\\n                    No\":\"Purchase Order No\",\"Quotation\\n                        No\":\"Quotation No\",\"Select Product\":\"Select Product\",\"Charges\":\"Charges\",\"Add\\n                            Charge\":\"Add Charge\",\"Discounts\":\"Discounts\",\"Add\\n                            Discount\":\"Add Discount\",\"Wholesaler Discount\":\"Wholesaler Discount\",\"Total Final Price\":\"Total Final Price\",\"Terms and\\n                            Conditions\":\"Terms and Conditions\",\"Order approved successfully\":\"Order approved successfully\",\"All Tiers\":\"All Tiers\",\"Gold\":\"Gold\",\"Silver\":\"Silver\",\"Bronze\":\"Bronze\",\"Sent\":\"Sent\",\"Accepted\":\"Accepted\",\"Price\":\"Price\",\"Default\":\"Default\",\"Low to High\":\"Low to High\",\"High to Low\":\"High to Low\",\"Quotation_list\":\"Quotation list\",\"Order_No\":\"Order No\",\"Quotation_No\":\"Quotation No\",\"Final_price\":\"Final price\",\"Wholesale Order Invoice\":\"Wholesale Order Invoice\",\"Print\":\"Print\",\"Choose Print Option\":\"Choose Print Option\",\"Print with Images\":\"Print with Images\",\"Print without Images\":\"Print without Images\",\"Business Info\":\"Business Info\",\"Order Info\":\"Order Info\",\"Quotation NO\":\"Quotation NO\",\"Purchase Order NO\":\"Purchase Order NO\",\"Product Details\":\"Product Details\",\"Terms and Conditions\":\"Terms and Conditions\",\"Sub Total\":\"Sub Total\",\"if_enabled,_the_cash_on_delivery_option_will_be_available_on_the_system._Customers_can_use_COD_as_a_payment_option.\":\"If enabled  the cash on delivery option will be available on the system. Customers can use COD as a payment option.\",\"want_to_Turn_ON_the_Cash_On_Delivery_option\":\"Want to Turn ON the Cash On Delivery option\",\"want_to_Turn_OFF_the_Cash_On_Delivery_option\":\"Want to Turn OFF the Cash On Delivery option\",\"if_enabled_customers_can_select_Cash_on_Delivery_as_a_payment_method_during_checkout\":\"If enabled customers can select Cash on Delivery as a payment method during checkout\",\"if_disabled_the_Cash_on_Delivery_payment_method_will_be_hidden_from_the_checkout_page\":\"If disabled the Cash on Delivery payment method will be hidden from the checkout page\",\"if_enabled,_customers_can_choose_digital_payment_options_during_the_checkout_process\":\"If enabled  customers can choose digital payment options during the checkout process\",\"want_to_Turn_ON_the_Digital_Payment_option\":\"Want to Turn ON the Digital Payment option\",\"want_to_Turn_OFF_the_Digital_Payment_option\":\"Want to Turn OFF the Digital Payment option\",\"if_enabled_customers_can_select_Digital_Payment_during_checkout\":\"If enabled customers can select Digital Payment during checkout\",\"if_disabled_Digital_Payment_options_will_be_hidden_from_the_checkout_page\":\"If disabled Digital Payment options will be hidden from the checkout page\",\"offline_Payment_allows_customers_to_use_external_payment_methods.\":\"Offline Payment allows customers to use external payment methods.\",\"They_must_share_payment_details_with_the_vendor_afterward.\":\"They must share payment details with the vendor afterward.\",\"Admin_can_set_whether_customers_can_make_offline_payments_by_enabling\\/disabling_this_button.\":\"Admin can set whether customers can make offline payments by enabling\\/disabling this button.\",\"want_to_Turn_ON_the_Offline_Payment_option\":\"Want to Turn ON the Offline Payment option\",\"want_to_Turn_OFF_the_Offline_Payment_option\":\"Want to Turn OFF the Offline Payment option\",\"if_enabled_customers_can_pay_through_external_payment_methods\":\"If enabled customers can pay through external payment methods\",\"if_disabled_customers_have_to_use_the_system_added_payment_gateways\":\"If disabled customers have to use the system added payment gateways\",\"You_must_active_at_least_one_method\":\"You must active at least one method\",\"you_can_not_turn_off_all_payment_methods_at_a_time.\":\"You can not turn off all payment methods at a time.\",\"must_active_at_least_1_payment_methods_for_smooth_order_payment_system.\":\"Must active at least 1 payment methods for smooth order payment system.\",\"okay\":\"Okay\",\"You must active one of digital payment methods.\":\"You must active one of digital payment methods.\",\"go_to_3rd_party_payment_methods\":\"Go to 3rd party payment methods\",\"Go_to_Offline_Payment_Methods\":\"Go to Offline Payment Methods\",\"product_settings\":\"Product settings\",\"product_setup\":\"Product setup\",\"re-order_level\":\"Re-order level\",\"set_the_stock_limit_for_the_Reorder_level\":\"Set the stock limit for the Reorder level\",\"vendors_can_see_all_products_that_need_to_be_re_stocked_in_a_section_when_they_reach_this_ReOrder_Level\":\"Vendors can see all products that need to be re stocked in a section when they reach this ReOrder Level\",\"if_enabled_services_are_shown_in_website\":\"If enabled services are shown in website\",\"want_to_Turn_ON_Services\":\"Want to Turn ON Services\",\"want_to_Turn_OFF_Services\":\"Want to Turn OFF Services\",\"if_disabled_services_are_hide_from_website\":\"If disabled services are hide from website\",\"show_brand\":\"Show brand\",\"if_enabled_customers_can_see_brands_on_the_app_and_website\":\"If enabled customers can see brands on the app and website\",\"they_can_browse_and_search_for_products_from_each_brand_inside_any_shop\":\"They can browse and search for products from each brand inside any shop\",\"want_to_Turn_ON_Product_Brand\":\"Want to Turn ON Product Brand\",\"want_to_Turn_OFF_Product_Brand\":\"Want to Turn OFF Product Brand\",\"if_disabled_brand_section_will_be_hidden_from_the_customer_app_and_website\":\"If disabled brand section will be hidden from the customer app and website\"}', 1, '2026-02-20 03:20:19', '2026-02-20 13:12:12');
INSERT INTO `business_settings` (`id`, `type`, `value`, `is_active`, `created_at`, `updated_at`) VALUES
(239, 'stock_validation_refactor_enabled', '1', 1, NULL, '2026-02-23 19:14:40'),
(240, 'stock_validation_refactor_mirror_mode', '0', 1, NULL, '2026-02-23 19:14:40'),
(241, 'product_tax_calculation', 'include', 1, NULL, '2026-03-29 14:32:14');

-- --------------------------------------------------------

--
-- Table structure for table `calendar_todos`
--

CREATE TABLE `calendar_todos` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_activities`
--

CREATE TABLE `career_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `activity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_applies`
--

CREATE TABLE `career_applies` (
  `id` bigint UNSIGNED NOT NULL,
  `job_id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('Male','Female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notice_period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_ctc` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resume` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_benefits`
--

CREATE TABLE `career_benefits` (
  `id` bigint UNSIGNED NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_cards`
--

CREATE TABLE `career_cards` (
  `id` bigint UNSIGNED NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_interviews`
--

CREATE TABLE `career_interviews` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `conducted_at` datetime DEFAULT NULL,
  `panel` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `outcome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_jobs`
--

CREATE TABLE `career_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `experience` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `job_description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_offers`
--

CREATE TABLE `career_offers` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `signed_at` datetime DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_rejections`
--

CREATE TABLE `career_rejections` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `reason_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `closure_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_sections`
--

CREATE TABLE `career_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `button_text` text COLLATE utf8mb4_unicode_ci,
  `button_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `career_talent_pool`
--

CREATE TABLE `career_talent_pool` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `consent` tinyint(1) NOT NULL,
  `recontact_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint DEFAULT NULL,
  `customer_type` int NOT NULL DEFAULT '0',
  `cart_group_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `product_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'physical',
  `digital_product_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choices` text COLLATE utf8mb4_unicode_ci,
  `variations` text COLLATE utf8mb4_unicode_ci,
  `variant` text COLLATE utf8mb4_unicode_ci,
  `quantity` int NOT NULL DEFAULT '1',
  `price` double NOT NULL DEFAULT '1',
  `tax` double NOT NULL DEFAULT '1',
  `discount` double NOT NULL DEFAULT '1',
  `wholesale_discount` float(12,6) DEFAULT '0.000000',
  `wholesale_spacial_discount` float(12,6) DEFAULT '0.000000',
  `installtion_charges` double DEFAULT '0',
  `exchange_qty` int DEFAULT '0',
  `exchange_charges` double DEFAULT '0',
  `tax_model` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'exclude',
  `is_checked` tinyint(1) NOT NULL DEFAULT '0',
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seller_id` bigint DEFAULT NULL,
  `seller_is` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shop_info` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_cost` double(8,2) DEFAULT NULL,
  `shipping_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_guest` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_shippings`
--

CREATE TABLE `cart_shippings` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_group_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_method_id` bigint DEFAULT NULL,
  `shipping_cost` double(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon_storage_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `parent_id` int NOT NULL,
  `position` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `home_status` tinyint(1) NOT NULL DEFAULT '0',
  `priority` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_shipping_costs`
--

CREATE TABLE `category_shipping_costs` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `cost` double(8,2) DEFAULT NULL,
  `multiply_qty` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chattings`
--

CREATE TABLE `chattings` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `seller_id` bigint DEFAULT NULL,
  `admin_id` bigint DEFAULT NULL,
  `delivery_man_id` bigint DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `attachment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `sent_by_customer` tinyint(1) NOT NULL DEFAULT '0',
  `sent_by_seller` tinyint(1) NOT NULL DEFAULT '0',
  `sent_by_admin` tinyint(1) DEFAULT NULL,
  `sent_by_delivery_man` tinyint(1) DEFAULT NULL,
  `seen_by_customer` tinyint(1) NOT NULL DEFAULT '1',
  `seen_by_seller` tinyint(1) NOT NULL DEFAULT '1',
  `seen_by_admin` tinyint(1) DEFAULT NULL,
  `seen_by_delivery_man` tinyint(1) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `notification_receiver` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'admin, seller, customer, deliveryman',
  `seen_notification` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shop_id` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cms_pages`
--

CREATE TABLE `cms_pages` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cms_pages`
--

INSERT INTO `cms_pages` (`id`, `title`, `slug`, `content`, `created_at`, `updated_at`) VALUES
(1, 'Home', 'home', '<p>Default content for Home</p>', '2025-04-14 08:23:16', '2025-04-14 08:23:16'),
(2, 'About Us', 'about-us', '<p>Default content for About Us</p>', '2025-04-14 08:23:16', '2025-04-14 08:23:16'),
(3, 'Blog', 'blog', '<p>Default content for Blog</p>', '2025-04-14 08:23:16', '2025-04-14 08:23:16'),
(4, 'Career', 'career', '<p>Default content for Career</p>', '2025-04-14 08:23:16', '2025-04-14 08:23:16'),
(5, 'Contact Us', 'contact-us', '<p>Default content for Contact Us</p>', '2025-04-14 08:23:16', '2025-04-14 08:23:16'),
(6, 'Refund Policy', 'refund-policy', '<p>Default content for Refund Policy</p>', '2025-04-14 08:23:16', '2025-04-14 08:23:16'),
(7, 'Return Policy', 'return-policy', '<p>Default content for Return Policy</p>', '2025-04-14 08:23:16', '2025-04-14 08:23:16'),
(8, 'Cancellation Policy', 'cancellation-policy', '<p>Default content for Cancellation Policy</p>', '2025-04-14 08:23:16', '2025-04-14 08:23:16');

-- --------------------------------------------------------

--
-- Table structure for table `cms_products`
--

CREATE TABLE `cms_products` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `heading` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `button_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cms_products`
--

INSERT INTO `cms_products` (`id`, `type`, `heading`, `description`, `button_link`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'core_product_slider', 'Our Core Products', 'Browse our core range of top-rated, most-loved products by our customers.', NULL, NULL, 1, '2025-06-10 08:50:25', '2025-06-10 08:50:25'),
(5, 'feature_product', '🌟 Featured Products', 'Explore our handpicked selection of top-rated featured products, loved by our customers.', NULL, NULL, 0, '2025-06-10 08:52:26', '2025-07-12 06:38:06'),
(6, 'request_card_1', 'The One Platform With Everything You Need', 'Unlike many other WordPress website builder plugins, Elementor offers all the necessary tools and design features for free.', NULL, NULL, 1, '2025-06-10 08:52:26', '2025-11-14 06:57:51'),
(7, 'request_card_2', 'The One Platform With Everything You Need', 'Unlike many other WordPress website builder plugins, Elementor offers all the necessary tools and design features for free.', NULL, NULL, 1, '2025-06-10 08:52:26', '2025-07-12 06:34:35'),
(8, 'request_card_3', 'The One Platform With Everything You Need', 'Unlike many other WordPress website builder plugins, Elementor offers all the necessary tools and design features for free.', NULL, NULL, 1, '2025-06-10 08:52:26', '2025-07-12 06:36:28'),
(9, 'main_banner', 'Themehour Latest Products.', 'Having your battery load-tested to check its electrical output cleaning any corrosion or buildup on the terminals and inspecting the battery for signs of physical damage.', NULL, NULL, 1, '2025-06-10 08:50:25', '2025-07-12 06:39:11');

-- --------------------------------------------------------

--
-- Table structure for table `cms_services`
--

CREATE TABLE `cms_services` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `heading` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `button_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cms_services`
--

INSERT INTO `cms_services` (`id`, `type`, `heading`, `description`, `button_link`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'latest_services', 'Battery Manufacturing', 'We produce high-quality lead-acid batteries using calcium MF technology, suitable for all types of vehicles and industrial applications.', 'http://127.0.0.1:8000/wholesaler/auth/registration/index', 'cms-service/2026-02-23-699c31b73b233.webp', 1, NULL, '2026-02-23 10:53:43'),
(2, 'core_services', 'Battery Recycling', 'We recycle used batteries through a closed-loop, eco-safe system—recovering up to 80% of materials like lead and plastic to protect the environment.', 'www.elnisr.online', 'cms-service/2026-02-23-699c32100f2ef.webp', 1, NULL, '2026-02-23 10:55:12'),
(3, 'request_card_1', 'The One Platform With Everything You Need', 'Powering Egypt\'s Future, One Battery at a Time', 'http://127.0.0.1:8000/wholesaler/auth/registration/index', 'cms-service/2026-02-23-699c32ece95a6.webp', 1, NULL, '2026-02-23 10:58:52'),
(4, 'request_card_2', 'Lifetime Support', 'Experience innovation, reliability, and performance with Elnisr Batteries, engineered for the toughest Egyptian roads.', 'http://127.0.0.1:8000/career', 'cms-service/2026-02-23-699c33bb63b07.webp', 1, NULL, '2026-02-23 11:02:19'),
(5, 'request_card_3', 'Powering Egypt\'s Future, One Battery at a Time', 'A proud Egyptian manufacturer, now bringing premium battery solutions directly to your doorstep with our new e-commerce platform.', 'http://127.0.0.1:8000/career', 'cms-service/2026-02-23-699c3432641d7.webp', 1, NULL, '2026-02-23 11:04:18');

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` int NOT NULL,
  `name` varchar(30) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `code` varchar(10) COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'IndianRed', '#CD5C5C', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(2, 'LightCoral', '#F08080', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(3, 'Salmon', '#FA8072', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(4, 'DarkSalmon', '#E9967A', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(5, 'LightSalmon', '#FFA07A', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(6, 'Crimson', '#DC143C', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(7, 'Red', '#FF0000', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(8, 'FireBrick', '#B22222', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(9, 'DarkRed', '#8B0000', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(10, 'Pink', '#FFC0CB', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(11, 'LightPink', '#FFB6C1', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(12, 'HotPink', '#FF69B4', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(13, 'DeepPink', '#FF1493', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(14, 'MediumVioletRed', '#C71585', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(15, 'PaleVioletRed', '#DB7093', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(17, 'Coral', '#FF7F50', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(18, 'Tomato', '#FF6347', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(19, 'OrangeRed', '#FF4500', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(20, 'DarkOrange', '#FF8C00', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(21, 'Orange', '#FFA500', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(22, 'Gold', '#FFD700', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(23, 'Yellow', '#FFFF00', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(24, 'LightYellow', '#FFFFE0', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(25, 'LemonChiffon', '#FFFACD', '2018-11-05 02:12:26', '2018-11-05 02:12:26'),
(26, 'LightGoldenrodYellow', '#FAFAD2', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(27, 'PapayaWhip', '#FFEFD5', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(28, 'Moccasin', '#FFE4B5', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(29, 'PeachPuff', '#FFDAB9', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(30, 'PaleGoldenrod', '#EEE8AA', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(31, 'Khaki', '#F0E68C', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(32, 'DarkKhaki', '#BDB76B', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(33, 'Lavender', '#E6E6FA', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(34, 'Thistle', '#D8BFD8', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(35, 'Plum', '#DDA0DD', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(36, 'Violet', '#EE82EE', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(37, 'Orchid', '#DA70D6', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(39, 'Magenta', '#FF00FF', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(40, 'MediumOrchid', '#BA55D3', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(41, 'MediumPurple', '#9370DB', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(42, 'Amethyst', '#9966CC', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(43, 'BlueViolet', '#8A2BE2', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(44, 'DarkViolet', '#9400D3', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(45, 'DarkOrchid', '#9932CC', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(46, 'DarkMagenta', '#8B008B', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(47, 'Purple', '#800080', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(48, 'Indigo', '#4B0082', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(49, 'SlateBlue', '#6A5ACD', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(50, 'DarkSlateBlue', '#483D8B', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(51, 'MediumSlateBlue', '#7B68EE', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(52, 'GreenYellow', '#ADFF2F', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(53, 'Chartreuse', '#7FFF00', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(54, 'LawnGreen', '#7CFC00', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(55, 'Lime', '#00FF00', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(56, 'LimeGreen', '#32CD32', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(57, 'PaleGreen', '#98FB98', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(58, 'LightGreen', '#90EE90', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(59, 'MediumSpringGreen', '#00FA9A', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(60, 'SpringGreen', '#00FF7F', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(61, 'MediumSeaGreen', '#3CB371', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(62, 'SeaGreen', '#2E8B57', '2018-11-05 02:12:27', '2018-11-05 02:12:27'),
(63, 'ForestGreen', '#228B22', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(64, 'Green', '#008000', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(65, 'DarkGreen', '#006400', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(66, 'YellowGreen', '#9ACD32', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(67, 'OliveDrab', '#6B8E23', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(68, 'Olive', '#808000', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(69, 'DarkOliveGreen', '#556B2F', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(70, 'MediumAquamarine', '#66CDAA', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(71, 'DarkSeaGreen', '#8FBC8F', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(72, 'LightSeaGreen', '#20B2AA', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(73, 'DarkCyan', '#008B8B', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(74, 'Teal', '#008080', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(75, 'Aqua', '#00FFFF', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(77, 'LightCyan', '#E0FFFF', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(78, 'PaleTurquoise', '#AFEEEE', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(79, 'Aquamarine', '#7FFFD4', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(80, 'Turquoise', '#40E0D0', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(81, 'MediumTurquoise', '#48D1CC', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(82, 'DarkTurquoise', '#00CED1', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(83, 'CadetBlue', '#5F9EA0', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(84, 'SteelBlue', '#4682B4', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(85, 'LightSteelBlue', '#B0C4DE', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(86, 'PowderBlue', '#B0E0E6', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(87, 'LightBlue', '#ADD8E6', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(88, 'SkyBlue', '#87CEEB', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(89, 'LightSkyBlue', '#87CEFA', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(90, 'DeepSkyBlue', '#00BFFF', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(91, 'DodgerBlue', '#1E90FF', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(92, 'CornflowerBlue', '#6495ED', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(94, 'RoyalBlue', '#4169E1', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(95, 'Blue', '#0000FF', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(96, 'MediumBlue', '#0000CD', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(97, 'DarkBlue', '#00008B', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(98, 'Navy', '#000080', '2018-11-05 02:12:28', '2018-11-05 02:12:28'),
(99, 'MidnightBlue', '#191970', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(100, 'Cornsilk', '#FFF8DC', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(101, 'BlanchedAlmond', '#FFEBCD', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(102, 'Bisque', '#FFE4C4', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(103, 'NavajoWhite', '#FFDEAD', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(104, 'Wheat', '#F5DEB3', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(105, 'BurlyWood', '#DEB887', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(106, 'Tan', '#D2B48C', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(107, 'RosyBrown', '#BC8F8F', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(108, 'SandyBrown', '#F4A460', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(109, 'Goldenrod', '#DAA520', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(110, 'DarkGoldenrod', '#B8860B', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(111, 'Peru', '#CD853F', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(112, 'Chocolate', '#D2691E', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(113, 'SaddleBrown', '#8B4513', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(114, 'Sienna', '#A0522D', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(115, 'Brown', '#A52A2A', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(116, 'Maroon', '#800000', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(117, 'White', '#FFFFFF', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(118, 'Snow', '#FFFAFA', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(119, 'Honeydew', '#F0FFF0', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(120, 'MintCream', '#F5FFFA', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(121, 'Azure', '#F0FFFF', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(122, 'AliceBlue', '#F0F8FF', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(123, 'GhostWhite', '#F8F8FF', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(124, 'WhiteSmoke', '#F5F5F5', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(125, 'Seashell', '#FFF5EE', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(126, 'Beige', '#F5F5DC', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(127, 'OldLace', '#FDF5E6', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(128, 'FloralWhite', '#FFFAF0', '2018-11-05 02:12:29', '2018-11-05 02:12:29'),
(129, 'Ivory', '#FFFFF0', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(130, 'AntiqueWhite', '#FAEBD7', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(131, 'Linen', '#FAF0E6', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(132, 'LavenderBlush', '#FFF0F5', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(133, 'MistyRose', '#FFE4E1', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(134, 'Gainsboro', '#DCDCDC', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(135, 'LightGrey', '#D3D3D3', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(136, 'Silver', '#C0C0C0', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(137, 'DarkGray', '#A9A9A9', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(138, 'Gray', '#808080', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(139, 'DimGray', '#696969', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(140, 'LightSlateGray', '#778899', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(141, 'SlateGray', '#708090', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(142, 'DarkSlateGray', '#2F4F4F', '2018-11-05 02:12:30', '2018-11-05 02:12:30'),
(143, 'Black', '#000000', '2018-11-05 02:12:30', '2018-11-05 02:12:30');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `seen` tinyint(1) NOT NULL DEFAULT '0',
  `feedback` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reply` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_banners`
--

CREATE TABLE `contact_banners` (
  `id` bigint UNSIGNED NOT NULL,
  `heading` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subheading` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `added_by` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `coupon_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coupon_bearer` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inhouse',
  `seller_id` bigint DEFAULT NULL COMMENT 'NULL=in-house, 0=all seller',
  `customer_id` bigint DEFAULT NULL COMMENT '0 = all customer',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expire_date` date DEFAULT NULL,
  `min_purchase` decimal(8,2) NOT NULL DEFAULT '0.00',
  `max_discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `limit` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crm_calls`
--

CREATE TABLE `crm_calls` (
  `id` bigint UNSIGNED NOT NULL,
  `call_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint UNSIGNED DEFAULT NULL,
  `agent_id` bigint UNSIGNED DEFAULT NULL,
  `call_date` timestamp NOT NULL,
  `call_duration` int NOT NULL DEFAULT '0',
  `call_notes` text COLLATE utf8mb4_unicode_ci,
  `direction` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ringing',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ucm_channel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucm_peer_channel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucm_uniqueid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucm_bridge_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `src_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dst_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `answered_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `raw_payload` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cron_configuration`
--

CREATE TABLE `cron_configuration` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_status_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'after',
  `duration` double(8,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cron_sender_details`
--

CREATE TABLE `cron_sender_details` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `send_for` bigint UNSIGNED NOT NULL DEFAULT '1',
  `sender_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `send_date` timestamp NULL DEFAULT NULL,
  `ticket_status` bigint UNSIGNED NOT NULL DEFAULT '0',
  `status` bigint UNSIGNED NOT NULL DEFAULT '0',
  `is_active` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `exchange_rate` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `name`, `symbol`, `code`, `exchange_rate`, `status`, `created_at`, `updated_at`) VALUES
(1, 'USD', '$', 'USD', '1', 0, NULL, '2026-03-23 11:00:13'),
(8, 'Egyptian Pound', 'EGP', 'EGP', '1', 1, '2025-01-02 13:40:16', '2026-03-23 11:00:13');

-- --------------------------------------------------------

--
-- Table structure for table `customer_wallets`
--

CREATE TABLE `customer_wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint DEFAULT NULL,
  `balance` decimal(8,2) NOT NULL DEFAULT '0.00',
  `royality_points` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_wallet_histories`
--

CREATE TABLE `customer_wallet_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint DEFAULT NULL,
  `transaction_amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `transaction_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_method` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `id` bigint UNSIGNED NOT NULL,
  `related_party_type` enum('company','contact') COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_party_id` bigint UNSIGNED NOT NULL,
  `contact_id` bigint UNSIGNED DEFAULT NULL,
  `stage` enum('join_request','register','confirmed_order','negotiation','closed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `value` decimal(15,2) DEFAULT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `po_id` bigint DEFAULT NULL COMMENT 'purchase_order_id ',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'register',
  `escalation_level` enum('none','l1','l2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalated_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `response_due` timestamp NULL DEFAULT NULL,
  `resolution_due` timestamp NULL DEFAULT NULL,
  `first_response_at` timestamp NULL DEFAULT NULL,
  `reopen_count` int NOT NULL DEFAULT '0',
  `sla_paused_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `lead_id` bigint UNSIGNED DEFAULT NULL,
  `quotation_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotation_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fulfillment_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deal_activities`
--

CREATE TABLE `deal_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `deal_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci,
  `note_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deal_calls`
--

CREATE TABLE `deal_calls` (
  `id` bigint UNSIGNED NOT NULL,
  `deal_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deal_files`
--

CREATE TABLE `deal_files` (
  `id` bigint UNSIGNED NOT NULL,
  `deal_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deal_notes`
--

CREATE TABLE `deal_notes` (
  `id` bigint UNSIGNED NOT NULL,
  `deal_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `noted_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deal_of_the_days`
--

CREATE TABLE `deal_of_the_days` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'amount',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deal_tasks`
--

CREATE TABLE `deal_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `deal_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `due_date` date NOT NULL,
  `status` enum('pending','complete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveryman_notifications`
--

CREATE TABLE `deliveryman_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `delivery_man_id` bigint NOT NULL,
  `order_id` bigint NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveryman_wallets`
--

CREATE TABLE `deliveryman_wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `delivery_man_id` bigint NOT NULL,
  `current_balance` decimal(50,2) NOT NULL DEFAULT '0.00',
  `cash_in_hand` decimal(50,2) NOT NULL DEFAULT '0.00',
  `pending_withdraw` decimal(50,2) NOT NULL DEFAULT '0.00',
  `total_withdraw` decimal(50,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_areas`
--

CREATE TABLE `delivery_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `area_id` int DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_cities`
--

CREATE TABLE `delivery_cities` (
  `id` bigint UNSIGNED NOT NULL,
  `city_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_country_codes`
--

CREATE TABLE `delivery_country_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `country_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_histories`
--

CREATE TABLE `delivery_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint DEFAULT NULL,
  `deliveryman_id` bigint DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `longitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_man_transactions`
--

CREATE TABLE `delivery_man_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `delivery_man_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `user_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `debit` decimal(50,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(50,2) NOT NULL DEFAULT '0.00',
  `transaction_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_men`
--

CREATE TABLE `delivery_men` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint DEFAULT NULL,
  `f_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `l_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `country_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identity_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identity_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_no` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `holder_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_online` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `auth_token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '6yIRXJRRfp78qJsAoKZZ6TTqhzuNJ3TcdvPBmk6n',
  `fcm_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_language` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_states`
--

CREATE TABLE `delivery_states` (
  `id` bigint UNSIGNED NOT NULL,
  `state_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_zip_codes`
--

CREATE TABLE `delivery_zip_codes` (
  `id` bigint UNSIGNED NOT NULL,
  `zipcode` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `head_id` int DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department_users`
--

CREATE TABLE `department_users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_desktop` tinyint(1) NOT NULL DEFAULT '0',
  `is_mobile` tinyint(1) NOT NULL DEFAULT '0',
  `language` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_trusted` tinyint(1) NOT NULL DEFAULT '0',
  `is_untrusted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `digital_product_authors`
--

CREATE TABLE `digital_product_authors` (
  `id` bigint UNSIGNED NOT NULL,
  `author_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `digital_product_otp_verifications`
--

CREATE TABLE `digital_product_otp_verifications` (
  `id` bigint UNSIGNED NOT NULL,
  `order_details_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_hit_count` tinyint NOT NULL DEFAULT '0',
  `is_temp_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `temp_block_time` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `digital_product_publishing_houses`
--

CREATE TABLE `digital_product_publishing_houses` (
  `id` bigint UNSIGNED NOT NULL,
  `publishing_house_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `digital_product_variations`
--

CREATE TABLE `digital_product_variations` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` int NOT NULL,
  `variant_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(24,8) DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `template_name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `user_type` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `template_design_name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_general_ci,
  `banner_image` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `button_name` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `button_url` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `footer_text` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `copyright_text` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `social_media` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `hide_field` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `button_content_status` tinyint NOT NULL DEFAULT '1',
  `product_information_status` tinyint NOT NULL DEFAULT '1',
  `order_information_status` tinyint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `template_name`, `user_type`, `template_design_name`, `title`, `body`, `banner_image`, `image`, `logo`, `button_name`, `button_url`, `footer_text`, `copyright_text`, `pages`, `social_media`, `hide_field`, `button_content_status`, `product_information_status`, `order_information_status`, `status`, `created_at`, `updated_at`) VALUES
(1, 'order-received', 'admin', 'order-received', 'New Order Received', '<p><b>Hi {adminName},</b></p><p>We have sent you this email to notify that you have a new order.You will be able to see your orders after login to your panel.</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"icon\", \"product_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(2, 'order-place', 'customer', 'order-place', 'Order # {orderId} Has Been Placed Successfully!', '<p><b>Hi {userName},</b></p><p>Your order from {shopName} has been placed to know the current status of your order click track order</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"icon\", \"product_information\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(3, 'registration-verification', 'customer', 'registration-verification', 'Registration Verification', '<p><b>Hi {userName},</b></p><p>Your verification code is</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(4, 'registration-from-pos', 'customer', 'registration-from-pos', 'Registration Complete', '<p><b>Hi {userName},</b></p><p>Thank you for joining Dynamic Shop.If you want to become a registered customer then reset your password below by using this email. Then you’ll be able to explore the website and app as a registered customer.</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_url\", \"button_content_status\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(5, 'account-block', 'customer', 'account-block', 'Account Blocked', '<div><b>Hi {userName},</b></div><div><b><br></b></div><div>Your account has been blocked due to suspicious activity by the admin .To resolve this issue please contact with admin or support center. We apologize for any inconvenience caused.</div><div><br></div><div>Meanwhile, click here to visit theDynamicshop website</div><div><font color=\"#0000ff\"> <a href=\"http://6valley.test\" target=\"_blank\">http://6valley.test</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(6, 'account-unblock', 'customer', 'account-unblock', 'Account Unblocked', '<div><b>Hi {userName},</b></div><div><b><br></b></div><div>Your account has been successfully unblocked. We appreciate your cooperation in resolving this issue. Thank you for your understanding and patience. </div><div><br></div><div>Meanwhile, click here to visit theDynamic shop website</div><div><font color=\"#0000ff\"> <a href=\"http://6valley.test\" target=\"_blank\">http://6valley.test</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(7, 'digital-product-download', 'customer', 'digital-product-download', 'Congratulations', '<p>Thank you for choosing Dynamic shop! Your digital product is ready for download. To download your product use your email <b>{emailId}</b> and order # {orderId} below.</b><br></p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(8, 'digital-product-otp', 'customer', 'digital-product-otp', 'Digital Product Download OTP Verification', '<p><b>Hi {userName},</b></p><p>Your verification code is</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(9, 'add-fund-to-wallet', 'customer', 'add-fund-to-wallet', 'Transaction Successful', '<div style=\"text-align: center; \">Amount successfully credited to your wallet .</div><div style=\"text-align: center; \"><br></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(10, 'registration', 'vendor', 'registration', 'Registration Complete', '<div><b>Hi {vendorName},</b></div><div><b><br></b></div><div>Congratulation! Your registration request has been send to admin successfully! Please wait until admin reviewal. </div><div><br></div><div>meanwhile click here to visit the Dynamic Shop Website</div><div><font color=\"#0000ff\"> <a href=\"http://6valley.test\" target=\"_blank\">http://6valley.test</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(11, 'registration-approved', 'vendor', 'registration-approved', 'Registration Approved', '<div><b>Hi {vendorName},</b></div><div><b><br></b></div><div>Your registration request has been approved by admin. Now you can complete your store setting and start selling your product on Dynamic Shop. </div><div><br></div><div>Meanwhile, click here to visit theDynamic shop website</div><div><font color=\"#0000ff\"> <a href=\"http://6valley.test\" target=\"_blank\">http://6valley.test</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(12, 'registration-denied', 'vendor', 'registration-denied', 'Registration Denied', '<div><b>Hi {vendorName},</b></div><div><b><br></b></div><div>Your registration request has been denied by admin. Please contact with admin or support center if you have any queries.</div><div><br></div><div>Meanwhile, click here to visit theDynamic shop website</div><div><font color=\"#0000ff\"> <a href=\"http://6valley.test\" target=\"_blank\">http://6valley.test</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(13, 'account-suspended', 'vendor', 'account-suspended', 'Account Suspended', '<div><b>Hi {vendorName},</b></div><div><b><br></b></div><div>Your account access has been suspended by admin.From now you can access your app and panel again Please contact us for any queries we’re always happy to help.</div><div><br></div><div>Meanwhile, click here to visit theDynamic shop website</div><div><font color=\"#0000ff\"> <a href=\"http://6valley.test\" target=\"_blank\">http://6valley.test</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(14, 'account-activation', 'vendor', 'account-activation', 'Account Activation', '<div><b>Hi {vendorName},</b></div><div><b><br></b></div><div>Your account suspension has been revoked by admin. From now you can access your app and panel again Please contact us for any queries we’re always happy to help.</div><div><br></div><div>Meanwhile, click here to visit theDynamic shop website</div><div><font color=\"#0000ff\"> <a href=\"http://6valley.test\" target=\"_blank\">http://6valley.test</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(15, 'forgot-password', 'vendor', 'forgot-password', 'Change Password Request', '<p><b>Hi {vendorName},</b></p><p>Please click the link below to change your password.</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(16, 'order-received', 'vendor', 'order-received', 'New Order Received', '<p><b>Hi {vendorName},</b></p><p>We have sent you this email to notify that you have a new order.You will be able to see your orders after login to your panel.</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"icon\", \"product_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(17, 'reset-password-verification', 'delivery-man', 'reset-password-verification', 'OTP Verification For Password Reset', '<p><b>Hi {deliveryManName},</b></p><p>Your verification code is</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries,New-messages._we_are_always_happy_to_help.', 'Copyright 2025 Dynamic. All right reserved.', NULL, NULL, '[\"product_information\", \"order_information\", \"button_content\", \"banner_image\"]', 1, 1, 1, 1, '2025-01-02 16:54:23', '2025-01-02 16:54:23'),
(18, 'registration', 'wholesaler', 'registration', 'Registration Complete', '<div><b>Hi {wholesalerName},</b></div><div><b><br></b></div><div>Congratulation! Your registration request has been send to admin successfully! Please wait until admin reviewal. </div><div><br></div><div>meanwhile click here to visit the Dynamic Logic Shop Website</div><div><font color=\"#0000ff\"> <a href=\"https://fitandfix.guptatechweb.com\" target=\"_blank\">https://fitandfix.guptatechweb.com</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04'),
(19, 'registration-approved', 'wholesaler', 'registration-approved', 'Registration Approved', '<div><b>Hi {wholesalerName},</b></div><div><b><br></b></div><div>Your registration request has been approved by admin. Now you can complete your store setting and start selling your product on Dynamic Logic Shop. </div><div><br></div><div>Meanwhile, click here to visit theDynamic Logic shop website</div><div><font color=\"#0000ff\"> <a href=\"https://fitandfix.guptatechweb.com\" target=\"_blank\">https://fitandfix.guptatechweb.com</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04'),
(20, 'account-suspended', 'wholesaler', 'account-suspended', 'Account Suspended', '<div><b>Hi {wholesalerName},</b></div><div><b><br></b></div><div>Your account access has been suspended by admin.From now you can access your app and panel again Please contact us for any queries we’re always happy to help.</div><div><br></div><div>Meanwhile, click here to visit theDynamic Logic shop website</div><div><font color=\"#0000ff\"> <a href=\"https://fitandfix.guptatechweb.com\" target=\"_blank\">https://fitandfix.guptatechweb.com</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04'),
(21, 'account-activation', 'wholesaler', 'account-activation', 'Account Activation', '<div><b>Hi {wholesalerName},</b></div><div><b><br></b></div><div>Your account suspension has been revoked by admin. From now you can access your app and panel again Please contact us for any queries we’re always happy to help.</div><div><br></div><div>Meanwhile, click here to visit theDynamic Logic shop website</div><div><font color=\"#0000ff\"> <a href=\"https://fitandfix.guptatechweb.com\" target=\"_blank\">https://fitandfix.guptatechweb.com</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04'),
(22, 'forgot-password', 'wholesaler', 'forgot-password', 'Change Password Request', '<p><b>Hi {wholesalerName},</b></p><p>Please click the link below to change your password.</p>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04'),
(23, 'quotation-send', 'wholesaler', 'quotation-send', 'Quotation Send', '<div><b>Hi {wholesalerName},</b></div><div><b><br></b></div><div>Your A complete quoatation of your purchase order has been sent to you. You can check in my Quotation in profile section </div><div><br></div><div>Meanwhile, click here to visit theDynamic Logic shop website</div><div><font color=\"#0000ff\"> <a href=\"https://fitandfix.guptatechweb.com\" target=\"_blank\">https://fitandfix.guptatechweb.com</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04'),
(24, 'purchase-order-recevied', 'wholesaler', 'purchase-order-recevied', 'Purchase Order Recevied', '<div><b>Hi {wholesalerName},</b></div><div><b><br></b></div><div>Your purchase order for wholesale product is recevied and our team review your order and send you a mail when your order is approve</div><div><br></div><div>Meanwhile, click here to your order and track hearDynamic Logic shop website</div><div><font color=\"#0000ff\"> <a href=\"https://fitandfix.guptatechweb.com\" target=\"_blank\">https://fitandfix.guptatechweb.com</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04'),
(25, 'product-send', 'wholesaler', 'product-send', 'Product Send', '<div><b>Hi {wholesalerName},</b></div><div><b><br></b></div><div>We sent your purchess order you can track in your profile my purchase order section</div><div><br></div><div>Meanwhile, click here to visit theDynamic Logic shop website</div><div><font color=\"#0000ff\"> <a href=\"https://fitandfix.guptatechweb.com\" target=\"_blank\">https://fitandfix.guptatechweb.com</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04'),
(26, 'payment-received', 'wholesaler', 'payment-received', 'Payment Recevied', '<div><b>Hi {wholesalerName},</b></div><div><b><br></b></div><div>Your payment for purchess wholesale product is recevied and we process your order as soon as possible</div><div><br></div><div>Meanwhile, click here to your order and track hearDynamic Logic shop website</div><div><font color=\"#0000ff\"> <a href=\"https://fitandfix.guptatechweb.com\" target=\"_blank\">https://fitandfix.guptatechweb.com</a></font></div>', NULL, NULL, NULL, NULL, NULL, 'Please contact us for any queries, we are always happy to help.', 'Copyright 2025 Dynamic Logic. All right reserved.', NULL, NULL, '[\"product_information\",\"order_information\",\"button_content\",\"banner_image\"]', 1, 1, 1, 1, '2025-05-12 16:12:04', '2025-05-12 16:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `error_logs`
--

CREATE TABLE `error_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `status_code` int NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hit_counts` int NOT NULL DEFAULT '0',
  `redirect_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `error_logs`
--

INSERT INTO `error_logs` (`id`, `status_code`, `url`, `hit_counts`, `redirect_url`, `redirect_status`, `created_at`, `updated_at`) VALUES
(1918, 404, 'http://alnisr2.test/%20public/assets/back-end/img/arrow-down.png', 12, NULL, NULL, '2026-03-30 00:07:44', '2026-03-30 00:58:08'),
(1919, 404, 'http://alnisr2.test/assets/front-end/js/service/js/jquery-3.7.1.min.js', 2, NULL, NULL, '2026-03-30 00:48:50', '2026-03-30 00:52:36'),
(1920, 404, 'http://alnisr2.test/assets/back-end/img/state.png', 1, NULL, NULL, '2026-03-30 00:58:08', '2026-03-30 00:58:08'),
(1921, 404, 'http://alnisr2.test/assets/back-end/img/city.png', 1, NULL, NULL, '2026-03-30 00:58:08', '2026-03-30 00:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `escalations`
--

CREATE TABLE `escalations` (
  `id` bigint UNSIGNED NOT NULL,
  `escalatable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `escalatable_id` bigint UNSIGNED NOT NULL,
  `escalated_by` bigint UNSIGNED NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feature_deals`
--

CREATE TABLE `feature_deals` (
  `id` bigint UNSIGNED NOT NULL,
  `url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flash_deals`
--

CREATE TABLE `flash_deals` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `background_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `deal_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flash_deal_products`
--

CREATE TABLE `flash_deal_products` (
  `id` bigint UNSIGNED NOT NULL,
  `flash_deal_id` bigint DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `flash_deal_products`
--

INSERT INTO `flash_deal_products` (`id`, `flash_deal_id`, `product_id`, `discount`, `discount_type`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0.00, '0', '2026-01-14 16:15:54', '2026-01-14 16:15:54');

-- --------------------------------------------------------

--
-- Table structure for table `guest_users`
--

CREATE TABLE `guest_users` (
  `id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fcm_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `help_topics`
--

CREATE TABLE `help_topics` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `question` text COLLATE utf8mb4_unicode_ci,
  `answer` text COLLATE utf8mb4_unicode_ci,
  `ranking` int NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `home_page_sections`
--

CREATE TABLE `home_page_sections` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `home_page_sections`
--

INSERT INTO `home_page_sections` (`id`, `type`, `name`, `is_active`, `value`, `created_at`, `updated_at`) VALUES
(2, 'trusted_by', 'Trusted By', 1, '[{\"heading\":\"Trusted by Generations Since\",\"year\":\"1946\",\"paragraph\":\"Delivering excellence in battery technology for over 75 years.\",\"is_active\":true}]', '2025-05-28 04:13:55', '2025-07-04 03:29:46'),
(3, 'categories', 'Categories', 1, '{\"heading\":\"Shop by Category\",\"paragraph\":\"Chose your product from our category products\"}', '2025-05-28 04:13:55', '2026-02-23 18:43:07'),
(4, 'products', 'Products', 1, '[{\"section_title\":\"Shop Our Top Products\",\"section_paragraph\":\"Catch up on everything Hear\"}]', '2025-05-28 04:13:55', '2025-05-31 05:51:26'),
(5, 'why_choose_us', 'Why Choose Us', 1, '{\"section\":{\"title\":\"Why Choose Us\",\"subtitle\":\"Experience innovation, reliability, and performance with NISR\",\"cards\":[{\"title\":\"focus on highlighting\",\"description\":\"Built to last with minimal maintenance and maximum uptime.\",\"icon\":{\"type\":\"svg\",\"name\":\"cpu\",\"color\":\"teal-600\",\"animation\":\"animate-bounce\"}},{\"title\":\"Advanced Technology\",\"description\":\"Powered by the latest tech for unmatched performance.\",\"icon\":{\"type\":\"svg\",\"name\":\"cpu\",\"color\":\"teal-600\",\"animation\":\"animate-bounce\"}},{\"title\":\"Trusted Quality\",\"description\":\"Tested and trusted by thousands of customers worldwide.\",\"icon\":{\"type\":\"svg\",\"name\":\"shield\",\"color\":\"teal-600\",\"animation\":\"animate-spin-slow\"}},{\"title\":\"Long Life\",\"description\":\"Built to last with minimal maintenance and maximum uptime.\",\"icon\":{\"type\":\"svg\",\"name\":\"infinity\",\"color\":\"teal-600\",\"animation\":\"animate-bounce\"}}]},\"content\":{\"subtitle\":\"Experience innovation, reliability, and performance with NISR\"},\"main_heading\":\"Shop by Category\",\"main_paragraph\":\"Find your favorite products in these amazing categories.\"}', '2025-05-28 04:13:55', '2026-02-24 06:27:58'),
(6, 'why_join_us', 'Why Join Us', 1, '{\"section\":{\"title\":\"Become an Authorized NISR Dealer\",\"subtitle\":\"Join our dealer network for unmatched service and tech.\",\"cards\":[{\"image\":\"storage\\/uploads\\/why_join_us\\/aOZ0p96d06lF68RM4wBQCTTAlnYjBytXlxG4bmuF.jpg\",\"title\":\"Deals In Car\'s tyer\",\"image_alt\":\"Dealer 1\",\"description\":\"Trusted dealer delivering excellence with modern security solutions. Join NISR with pride and passion.\"},{\"image\":\"storage\\/uploads\\/why_join_us\\/L5lakdVDGOwLwu5oPMh6Fnk6Owqz5sHFa0wga80L.jpg\",\"title\":\"Deals In Power\",\"image_alt\":\"Dealer 2\",\"description\":\"Leading partner in surveillance tech with NISR. Grow your network with powerful solutions.\"},{\"image\":\"storage\\/uploads\\/why_join_us\\/wVP5xC6JpU7D8tvbFJY3vTxznUP39tvcFujBzcvJ.jpg\",\"title\":\"Deals In Power\",\"image_alt\":\"Dealer 3\",\"description\":\"Leading partner in surveillance tech with NISR. Grow your network with powerful solutions.\"}]}}', '2025-05-28 04:13:55', '2025-07-08 06:17:17'),
(7, 'blog', 'Blog', 1, '{\"heading\":\"Latest from Our Blog\",\"paragraph\":\"Read latast and tranding blog comes from worldwide\"}', '2025-05-28 04:13:55', '2025-07-04 03:26:01'),
(8, 'client_review', 'Client Review', 0, '{\"clients\":[{\"rating\":\"\\u2605\\u2605\\u2605\\u2605\\u2605\",\"name\":\"Sneha Verma\",\"review\":\"My tyre was punctured on the highway \\u2014 they reached in 20 mins and fixed it. Lifesavers!\",\"image\":\"https:\\/\\/images.pexels.com\\/photos\\/2379004\\/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=600\"},{\"rating\":\"\\u2605\\u2605\\u2605\\u2605\\u2606\",\"name\":\"Rakesh Mehta\",\"review\":\"Battery installation was quick. Staff was polite and explained everything properly.\",\"image\":\"https:\\/\\/images.pexels.com\\/photos\\/2379004\\/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=600\"},{\"rating\":\"\\u2605\\u2605\\u2605\\u2605\\u2605\",\"name\":\"Priya Joshi\",\"review\":\"I got brand new tyres at the best price. Fitting was free and done professionally!\",\"image\":\"https:\\/\\/images.pexels.com\\/photos\\/2379004\\/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=600\"}]}', '2025-05-28 04:13:55', '2026-02-22 23:45:58'),
(9, 'wholesaler_section', 'Wholesaler Section', 1, '{\"title\":\"Join Our Network of Wholesalers\",\"description\":\"Partner with us and unlock access to premium battery and tyre solutions at competitive wholesale pricing.\",\"button\":{\"text\":\"Join Us\",\"link\":\"http:\\/\\/127.0.0.1:8000\\/wholesaler\\/auth\\/registration\\/index\"},\"image\":\"storage\\/uploads\\/wholesaler_section\\/TK3ZznDd6m6si4ebdjAlAMxBroLtUNyvcny7TPsO.png\"}', '2025-05-28 04:13:55', '2025-06-20 03:24:29'),
(11, 'download_app', 'Download Mobile App', 1, '{\"type\":\"download_app_section\",\"content\":{\"heading\":\"Download Mobile App\",\"android_button\":{\"image\":\"1765351687_app store.png\",\"alt\":\"Play Store Logo\",\"link\":\"\"},\"ios_button\":{\"image\":\"1765351750_apple_app.png\",\"alt\":\"App Store Logo\",\"link\":\"\"},\"mockup_image\":{\"image\":\"1765351789_select-payment-method.png\",\"alt\":\"Mobile Mockup\"}},\"android_button\":{\"alt\":\"App Store Logo\",\"image\":\"1748450488_banner 4.jpg\"}}', '2025-05-28 04:13:55', '2025-12-10 03:59:49'),
(13, 'main_banner', 'Main Banner', 1, '[{\"heading\":\"Power Your Drive with Premium Car Batteries\",\"paragraph\":\"Discover high-performance car batteries that ensure your vehicle starts every time.\",\"buttonText\":\"Get Started\",\"buttonLink\":\"https:\\/\\/salescentral1.dynamiclogic.online\\/store\",\"image\":\"storage\\/banners\\/2026-02-23-699b903eccc3e.webp\",\"is_active\":true},{\"heading\":\"One Stop Shop for Batteries \\u2013 NISR\",\"paragraph\":\"From batteries, NISR gives all vehicle essentials with expert support.\",\"buttonText\":\"Get Started\",\"buttonLink\":\"#https:\\/\\/salescentral1.dynamiclogic.online\\/contacts\",\"image\":\"storage\\/banners\\/2026-02-23-699b9092be0bd.webp\",\"is_active\":true},{\"heading\":\"Top-Quality Batteries for a Safer Journey\",\"paragraph\":\"Choose from top Batteries for safety, comfort on every road.\",\"buttonText\":\"Get Started\",\"buttonLink\":\"https:\\/\\/salescentral1.dynamiclogic.online\\/our-products\",\"image\":\"storage\\/banners\\/2026-02-23-699b91aa343d6.webp\",\"is_active\":true},{\"heading\":\"Deals In Car\'s tyer\",\"paragraph\":\"Deals In Car\'s tyer\",\"buttonText\":\"buy now\",\"buttonLink\":\"http:\\/\\/127.0.0.1:8000\\/wholesaler\\/auth\\/registration\\/index\",\"image\":\"storage\\/banners\\/V9vbXs1YjmZiTYilbSHFB5vn2Gin2sA3T2PPx007.jpg\",\"is_active\":false},{\"heading\":\"The One Platform With Everything You Need\",\"paragraph\":\"The One Platform With Everything You Need\",\"buttonText\":\"click\",\"buttonLink\":\"http:\\/\\/127.0.0.1:8000\\/wholesaler\\/auth\\/registration\\/index\",\"image\":\"storage\\/banners\\/2D9p3VpAMoCWsRYddrCTQqX42tpNNdglBIXy1t3B.jpg\",\"is_active\":false}]', '2025-05-28 06:56:12', '2026-02-22 23:31:25'),
(14, 'find_perfect_match', 'find your perfect match', 1, '{\"section_heading\":\"Find perfect match\",\"hero_heading\":\"Find perfect match\",\"hero_description\":\"Shop by vehicle year make model\",\"filter_title\":\"Filter options\",\"make_label\":\"Make\",\"model_label\":\"Model\",\"year_label\":\"Model year\",\"make_placeholder\":\"Select make\",\"model_placeholder\":\"Select model\",\"year_placeholder\":\"Select year\",\"apply_button_text\":\"Apply filters\"}', '2025-05-28 06:56:12', '2026-02-22 23:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `inbox_activities`
--

CREATE TABLE `inbox_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inbox_calls`
--

CREATE TABLE `inbox_calls` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inbox_files`
--

CREATE TABLE `inbox_files` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inbox_messages`
--

CREATE TABLE `inbox_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `contact_id` bigint DEFAULT NULL,
  `sender_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pipeline` enum('email','form','chat','social','phone') COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_type` enum('support','service','career','warranty','contact') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `related_lead_id` bigint UNSIGNED DEFAULT NULL,
  `related_ticket_id` bigint UNSIGNED DEFAULT NULL,
  `related_warranty_id` bigint UNSIGNED DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` enum('new','processing','converted','ignored','spam') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `escalation_level` enum('none','l1','l2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalated_by` bigint UNSIGNED DEFAULT NULL,
  `spam_score` double(8,2) DEFAULT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` longtext COLLATE utf8mb4_unicode_ci,
  `reply` longtext COLLATE utf8mb4_unicode_ci,
  `follow_up_date` date DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `convert_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `convert_sub_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `response_due` timestamp NULL DEFAULT NULL,
  `resolution_due` timestamp NULL DEFAULT NULL,
  `first_response_at` timestamp NULL DEFAULT NULL,
  `reopen_count` int NOT NULL DEFAULT '0',
  `sla_paused_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inbox_notes`
--

CREATE TABLE `inbox_notes` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `noted_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inbox_suggestions`
--

CREATE TABLE `inbox_suggestions` (
  `id` bigint UNSIGNED NOT NULL,
  `inbox_message_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inbox_tasks`
--

CREATE TABLE `inbox_tasks` (
  `id` bigint UNSIGNED NOT NULL,
  `message_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `due_date` date NOT NULL,
  `status` enum('pending','complete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint UNSIGNED NOT NULL,
  `party_type` enum('wholesale','retail','service') COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `contact_id` bigint UNSIGNED DEFAULT NULL,
  `source_id` bigint UNSIGNED DEFAULT NULL,
  `po_id` bigint DEFAULT NULL COMMENT 'purchase_order_id ',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` bigint DEFAULT NULL,
  `department_id` bigint DEFAULT NULL,
  `employee_id` bigint DEFAULT NULL,
  `utm_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_campaign` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_medium` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_term` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `utm_content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('new','working','qualified','disqualified','converted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `escalation_level` enum('none','l1','l2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalated_by` bigint UNSIGNED DEFAULT NULL,
  `converted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `response_due` timestamp NULL DEFAULT NULL,
  `resolution_due` timestamp NULL DEFAULT NULL,
  `first_response_at` timestamp NULL DEFAULT NULL,
  `reopen_count` int NOT NULL DEFAULT '0',
  `sla_paused_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_activity`
--

CREATE TABLE `lead_activity` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activity_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `note_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_call`
--

CREATE TABLE `lead_call` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from` datetime DEFAULT NULL,
  `to` datetime DEFAULT NULL,
  `guests` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_file`
--

CREATE TABLE `lead_file` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_note`
--

CREATE TABLE `lead_note` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `noted_at` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_notifications`
--

CREATE TABLE `lead_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'notified admin id',
  `from_user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'who generate notification',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'task, quotation_request, deal, lead etc.',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `related_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Lead/Deal/Quotation ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = unread, 1 = read',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_task`
--

CREATE TABLE `lead_task` (
  `id` bigint UNSIGNED NOT NULL,
  `lead_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `due_date` date NOT NULL,
  `status` enum('pending','complete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logins`
--

CREATE TABLE `logins` (
  `id` bigint UNSIGNED NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'auth',
  `user_id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_setups`
--

CREATE TABLE `login_setups` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_setups`
--

INSERT INTO `login_setups` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'login_options', '{\"manual_login\":1,\"otp_login\":0,\"social_login\":1}', '2024-09-24 07:52:17', '2024-09-24 07:52:17'),
(2, 'social_media_for_login', '{\"google\":1,\"facebook\":1,\"apple\":1}', '2024-09-24 07:52:17', '2024-09-24 07:52:17'),
(3, 'email_verification', '0', '2024-09-24 07:52:17', '2024-09-24 07:52:17'),
(4, 'phone_verification', '0', '2024-09-24 07:52:17', '2024-09-24 07:52:17');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_point_transactions`
--

CREATE TABLE `loyalty_point_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `transaction_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit` decimal(24,3) NOT NULL DEFAULT '0.000',
  `debit` decimal(24,3) NOT NULL DEFAULT '0.000',
  `balance` decimal(24,3) NOT NULL DEFAULT '0.000',
  `reference` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` int NOT NULL,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manage_branch_product_stock`
--

CREATE TABLE `manage_branch_product_stock` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `attribute_id` bigint UNSIGNED DEFAULT NULL,
  `variation_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variation_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attributes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manage_extra_charges`
--

CREATE TABLE `manage_extra_charges` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `category_id` bigint UNSIGNED NOT NULL,
  `charges` double(15,2) NOT NULL DEFAULT '0.00',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2020_09_08_105159_create_admins_table', 1),
(5, '2020_09_08_111837_create_admin_roles_table', 1),
(6, '2020_09_16_142451_create_categories_table', 2),
(7, '2020_09_16_181753_create_categories_table', 3),
(8, '2020_09_17_134238_create_brands_table', 4),
(9, '2020_09_17_203054_create_attributes_table', 5),
(10, '2020_09_19_112509_create_coupons_table', 6),
(11, '2020_09_19_161802_create_curriencies_table', 7),
(12, '2020_09_20_114509_create_sellers_table', 8),
(13, '2020_09_23_113454_create_shops_table', 9),
(14, '2020_09_23_115615_create_shops_table', 10),
(15, '2020_09_23_153822_create_shops_table', 11),
(16, '2020_09_21_122817_create_products_table', 12),
(17, '2020_09_22_140800_create_colors_table', 12),
(18, '2020_09_28_175020_create_products_table', 13),
(19, '2020_09_28_180311_create_products_table', 14),
(20, '2020_10_04_105041_create_search_functions_table', 15),
(21, '2020_10_05_150730_create_customers_table', 15),
(22, '2020_10_08_133548_create_wishlists_table', 16),
(23, '2016_06_01_000001_create_oauth_auth_codes_table', 17),
(24, '2016_06_01_000002_create_oauth_access_tokens_table', 17),
(25, '2016_06_01_000003_create_oauth_refresh_tokens_table', 17),
(26, '2016_06_01_000004_create_oauth_clients_table', 17),
(27, '2016_06_01_000005_create_oauth_personal_access_clients_table', 17),
(28, '2020_10_06_133710_create_product_stocks_table', 17),
(29, '2020_10_06_134636_create_flash_deals_table', 17),
(30, '2020_10_06_134719_create_flash_deal_products_table', 17),
(31, '2020_10_08_115439_create_orders_table', 17),
(32, '2020_10_08_115453_create_order_details_table', 17),
(33, '2020_10_08_121135_create_shipping_addresses_table', 17),
(34, '2020_10_10_171722_create_business_settings_table', 17),
(35, '2020_09_19_161802_create_currencies_table', 18),
(36, '2020_10_12_152350_create_reviews_table', 18),
(37, '2020_10_12_161834_create_reviews_table', 19),
(38, '2020_10_12_180510_create_support_tickets_table', 20),
(39, '2020_10_14_140130_create_transactions_table', 21),
(40, '2020_10_14_143553_create_customer_wallets_table', 21),
(41, '2020_10_14_143607_create_customer_wallet_histories_table', 21),
(42, '2020_10_22_142212_create_support_ticket_convs_table', 21),
(43, '2020_10_24_234813_create_banners_table', 22),
(44, '2020_10_27_111557_create_shipping_methods_table', 23),
(45, '2020_10_27_114154_add_url_to_banners_table', 24),
(46, '2020_10_28_170308_add_shipping_id_to_order_details', 25),
(47, '2020_11_02_140528_add_discount_to_order_table', 26),
(48, '2020_11_03_162723_add_column_to_order_details', 27),
(49, '2020_11_08_202351_add_url_to_banners_table', 28),
(50, '2020_11_10_112713_create_help_topic', 29),
(51, '2020_11_10_141513_create_contacts_table', 29),
(52, '2020_11_15_180036_add_address_column_user_table', 30),
(53, '2020_11_18_170209_add_status_column_to_product_table', 31),
(54, '2020_11_19_115453_add_featured_status_product', 32),
(55, '2020_11_21_133302_create_deal_of_the_days_table', 33),
(56, '2020_11_20_172332_add_product_id_to_products', 34),
(57, '2020_11_27_234439_add__state_to_shipping_addresses', 34),
(58, '2020_11_28_091929_create_chattings_table', 35),
(59, '2020_12_02_011815_add_bank_info_to_sellers', 36),
(60, '2020_12_08_193234_create_social_medias_table', 37),
(61, '2020_12_13_122649_shop_id_to_chattings', 37),
(62, '2020_12_14_145116_create_seller_wallet_histories_table', 38),
(63, '2020_12_14_145127_create_seller_wallets_table', 38),
(64, '2020_12_15_174804_create_admin_wallets_table', 39),
(65, '2020_12_15_174821_create_admin_wallet_histories_table', 39),
(66, '2020_12_15_214312_create_feature_deals_table', 40),
(67, '2020_12_17_205712_create_withdraw_requests_table', 41),
(68, '2021_02_22_161510_create_notifications_table', 42),
(69, '2021_02_24_154706_add_deal_type_to_flash_deals', 43),
(70, '2021_03_03_204349_add_cm_firebase_token_to_users', 44),
(71, '2021_04_17_134848_add_column_to_order_details_stock', 45),
(72, '2021_05_12_155401_add_auth_token_seller', 46),
(73, '2021_06_03_104531_ex_rate_update', 47),
(74, '2021_06_03_222413_amount_withdraw_req', 48),
(75, '2021_06_04_154501_seller_wallet_withdraw_bal', 49),
(76, '2021_06_04_195853_product_dis_tax', 50),
(77, '2021_05_27_103505_create_product_translations_table', 51),
(78, '2021_06_17_054551_create_soft_credentials_table', 51),
(79, '2021_06_29_212549_add_active_col_user_table', 52),
(80, '2021_06_30_212619_add_col_to_contact', 53),
(81, '2021_07_01_160828_add_col_daily_needs_products', 54),
(82, '2021_07_04_182331_add_col_seller_sales_commission', 55),
(83, '2021_08_07_190655_add_seo_columns_to_products', 56),
(84, '2021_08_07_205913_add_col_to_category_table', 56),
(85, '2021_08_07_210808_add_col_to_shops_table', 56),
(86, '2021_08_14_205216_change_product_price_col_type', 56),
(87, '2021_08_16_201505_change_order_price_col', 56),
(88, '2021_08_16_201552_change_order_details_price_col', 56),
(89, '2019_09_29_154000_create_payment_cards_table', 57),
(90, '2021_08_17_213934_change_col_type_seller_earning_history', 57),
(91, '2021_08_17_214109_change_col_type_admin_earning_history', 57),
(92, '2021_08_17_214232_change_col_type_admin_wallet', 57),
(93, '2021_08_17_214405_change_col_type_seller_wallet', 57),
(94, '2021_08_22_184834_add_publish_to_products_table', 57),
(95, '2021_09_08_211832_add_social_column_to_users_table', 57),
(96, '2021_09_13_165535_add_col_to_user', 57),
(97, '2021_09_19_061647_add_limit_to_coupons_table', 57),
(98, '2021_09_20_020716_add_coupon_code_to_orders_table', 57),
(99, '2021_09_23_003059_add_gst_to_sellers_table', 57),
(100, '2021_09_28_025411_create_order_transactions_table', 57),
(101, '2021_10_02_185124_create_carts_table', 57),
(102, '2021_10_02_190207_create_cart_shippings_table', 57),
(103, '2021_10_03_194334_add_col_order_table', 57),
(104, '2021_10_03_200536_add_shipping_cost', 57),
(105, '2021_10_04_153201_add_col_to_order_table', 57),
(106, '2021_10_07_172701_add_col_cart_shop_info', 57),
(107, '2021_10_07_184442_create_phone_or_email_verifications_table', 57),
(108, '2021_10_07_185416_add_user_table_email_verified', 57),
(109, '2021_10_11_192739_add_transaction_amount_table', 57),
(110, '2021_10_11_200850_add_order_verification_code', 57),
(111, '2021_10_12_083241_add_col_to_order_transaction', 57),
(112, '2021_10_12_084440_add_seller_id_to_order', 57),
(113, '2021_10_12_102853_change_col_type', 57),
(114, '2021_10_12_110434_add_col_to_admin_wallet', 57),
(115, '2021_10_12_110829_add_col_to_seller_wallet', 57),
(116, '2021_10_13_091801_add_col_to_admin_wallets', 57),
(117, '2021_10_13_092000_add_col_to_seller_wallets_tax', 57),
(118, '2021_10_13_165947_rename_and_remove_col_seller_wallet', 57),
(119, '2021_10_13_170258_rename_and_remove_col_admin_wallet', 57),
(120, '2021_10_14_061603_column_update_order_transaction', 57),
(121, '2021_10_15_103339_remove_col_from_seller_wallet', 57),
(122, '2021_10_15_104419_add_id_col_order_tran', 57),
(123, '2021_10_15_213454_update_string_limit', 57),
(124, '2021_10_16_234037_change_col_type_translation', 57),
(125, '2021_10_16_234329_change_col_type_translation_1', 57),
(126, '2021_10_27_091250_add_shipping_address_in_order', 58),
(127, '2021_01_24_205114_create_paytabs_invoices_table', 59),
(128, '2021_11_20_043814_change_pass_reset_email_col', 59),
(129, '2021_11_25_043109_create_delivery_men_table', 60),
(130, '2021_11_25_062242_add_auth_token_delivery_man', 60),
(131, '2021_11_27_043405_add_deliveryman_in_order_table', 60),
(132, '2021_11_27_051432_create_delivery_histories_table', 60),
(133, '2021_11_27_051512_add_fcm_col_for_delivery_man', 60),
(134, '2021_12_15_123216_add_columns_to_banner', 60),
(135, '2022_01_04_100543_add_order_note_to_orders_table', 60),
(136, '2022_01_10_034952_add_lat_long_to_shipping_addresses_table', 60),
(137, '2022_01_10_045517_create_billing_addresses_table', 60),
(138, '2022_01_11_040755_add_is_billing_to_shipping_addresses_table', 60),
(139, '2022_01_11_053404_add_billing_to_orders_table', 60),
(140, '2022_01_11_234310_add_firebase_toke_to_sellers_table', 60),
(141, '2022_01_16_121801_change_colu_type', 60),
(142, '2022_01_22_101601_change_cart_col_type', 61),
(143, '2022_01_23_031359_add_column_to_orders_table', 61),
(144, '2022_01_28_235054_add_status_to_admins_table', 61),
(145, '2022_02_01_214654_add_pos_status_to_sellers_table', 61),
(146, '2019_12_14_000001_create_personal_access_tokens_table', 62),
(147, '2022_02_11_225355_add_checked_to_orders_table', 62),
(148, '2022_02_14_114359_create_refund_requests_table', 62),
(149, '2022_02_14_115757_add_refund_request_to_order_details_table', 62),
(150, '2022_02_15_092604_add_order_details_id_to_transactions_table', 62),
(151, '2022_02_15_121410_create_refund_transactions_table', 62),
(152, '2022_02_24_091236_add_multiple_column_to_refund_requests_table', 62),
(153, '2022_02_24_103827_create_refund_statuses_table', 62),
(154, '2022_03_01_121420_add_refund_id_to_refund_transactions_table', 62),
(155, '2022_03_10_091943_add_priority_to_categories_table', 63),
(156, '2022_03_13_111914_create_shipping_types_table', 63),
(157, '2022_03_13_121514_create_category_shipping_costs_table', 63),
(158, '2022_03_14_074413_add_four_column_to_products_table', 63),
(159, '2022_03_15_105838_add_shipping_to_carts_table', 63),
(160, '2022_03_16_070327_add_shipping_type_to_orders_table', 63),
(161, '2022_03_17_070200_add_delivery_info_to_orders_table', 63),
(162, '2022_03_18_143339_add_shipping_type_to_carts_table', 63),
(163, '2022_04_06_020313_create_subscriptions_table', 64),
(164, '2022_04_12_233704_change_column_to_products_table', 64),
(165, '2022_04_19_095926_create_jobs_table', 64),
(166, '2022_05_12_104247_create_wallet_transactions_table', 65),
(167, '2022_05_12_104511_add_two_column_to_users_table', 65),
(168, '2022_05_14_063309_create_loyalty_point_transactions_table', 65),
(169, '2022_05_26_044016_add_user_type_to_password_resets_table', 65),
(170, '2022_04_15_235820_add_provider', 66),
(171, '2022_07_21_101659_add_code_to_products_table', 66),
(172, '2022_07_26_103744_add_notification_count_to_notifications_table', 66),
(173, '2022_07_31_031541_add_minimum_order_qty_to_products_table', 66),
(174, '2022_08_11_172839_add_product_type_and_digital_product_type_and_digital_file_ready_to_products', 67),
(175, '2022_08_11_173941_add_product_type_and_digital_product_type_and_digital_file_to_order_details', 67),
(176, '2022_08_20_094225_add_product_type_and_digital_product_type_and_digital_file_ready_to_carts_table', 67),
(177, '2022_10_04_160234_add_banking_columns_to_delivery_men_table', 68),
(178, '2022_10_04_161339_create_deliveryman_wallets_table', 68),
(179, '2022_10_04_184506_add_deliverymanid_column_to_withdraw_requests_table', 68),
(180, '2022_10_11_103011_add_deliverymans_columns_to_chattings_table', 68),
(181, '2022_10_11_144902_add_deliverman_id_cloumn_to_reviews_table', 68),
(182, '2022_10_17_114744_create_order_status_histories_table', 68),
(183, '2022_10_17_120840_create_order_expected_delivery_histories_table', 68),
(184, '2022_10_18_084245_add_deliveryman_charge_and_expected_delivery_date', 68),
(185, '2022_10_18_130938_create_delivery_zip_codes_table', 68),
(186, '2022_10_18_130956_create_delivery_country_codes_table', 68),
(187, '2022_10_20_164712_create_delivery_man_transactions_table', 68),
(188, '2022_10_27_145604_create_emergency_contacts_table', 68),
(189, '2022_10_29_182930_add_is_pause_cause_to_orders_table', 68),
(190, '2022_10_31_150604_add_address_phone_country_code_column_to_delivery_men_table', 68),
(191, '2022_11_05_185726_add_order_id_to_reviews_table', 68),
(192, '2022_11_07_190749_create_deliveryman_notifications_table', 68),
(193, '2022_11_08_132745_change_transaction_note_type_to_withdraw_requests_table', 68),
(194, '2022_11_10_193747_chenge_order_amount_seller_amount_admin_commission_delivery_charge_tax_toorder_transactions_table', 68),
(195, '2022_12_17_035723_few_field_add_to_coupons_table', 69),
(196, '2022_12_26_231606_add_coupon_discount_bearer_and_admin_commission_to_orders', 69),
(197, '2023_01_04_003034_alter_billing_addresses_change_zip', 69),
(198, '2023_01_05_121600_change_id_to_transactions_table', 69),
(199, '2023_02_02_113330_create_product_tag_table', 70),
(200, '2023_02_02_114518_create_tags_table', 70),
(201, '2023_02_02_152248_add_tax_model_to_products_table', 70),
(202, '2023_02_02_152718_add_tax_model_to_order_details_table', 70),
(203, '2023_02_02_171034_add_tax_type_to_carts', 70),
(204, '2023_02_06_124447_add_color_image_column_to_products_table', 70),
(205, '2023_02_07_120136_create_withdrawal_methods_table', 70),
(206, '2023_02_07_175939_add_withdrawal_method_id_and_withdrawal_method_fields_to_withdraw_requests_table', 70),
(207, '2023_02_08_143314_add_vacation_start_and_vacation_end_and_vacation_not_column_to_shops_table', 70),
(208, '2023_02_09_104656_add_payment_by_and_payment_not_to_orders_table', 70),
(209, '2023_03_27_150723_add_expires_at_to_phone_or_email_verifications', 71),
(210, '2023_04_17_095721_create_shop_followers_table', 71),
(211, '2023_04_17_111249_add_bottom_banner_to_shops_table', 71),
(212, '2023_04_20_125423_create_product_compares_table', 71),
(213, '2023_04_30_165642_add_category_sub_category_and_sub_sub_category_add_in_product_table', 71),
(214, '2023_05_16_131006_add_expires_at_to_password_resets', 71),
(215, '2023_05_17_044243_add_visit_count_to_tags_table', 71),
(216, '2023_05_18_000403_add_title_and_subtitle_and_background_color_and_button_text_to_banners_table', 71),
(217, '2023_05_21_111300_add_login_hit_count_and_is_temp_blocked_and_temp_block_time_to_users_table', 71),
(218, '2023_05_21_111600_add_login_hit_count_and_is_temp_blocked_and_temp_block_time_to_phone_or_email_verifications_table', 71),
(219, '2023_05_21_112215_add_login_hit_count_and_is_temp_blocked_and_temp_block_time_to_password_resets_table', 71),
(220, '2023_06_04_210726_attachment_lenght_change_to_reviews_table', 71),
(221, '2023_06_05_115153_add_referral_code_and_referred_by_to_users_table', 72),
(222, '2023_06_21_002658_add_offer_banner_to_shops_table', 72),
(223, '2023_07_08_210747_create_most_demandeds_table', 72),
(224, '2023_07_31_111419_add_minimum_order_amount_to_sellers_table', 72),
(225, '2023_08_03_105256_create_offline_payment_methods_table', 72),
(226, '2023_08_07_131013_add_is_guest_column_to_carts_table', 72),
(227, '2023_08_07_170601_create_offline_payments_table', 72),
(228, '2023_08_12_102355_create_add_fund_bonus_categories_table', 72),
(229, '2023_08_12_215346_create_guest_users_table', 72),
(230, '2023_08_12_215659_add_is_guest_column_to_orders_table', 72),
(231, '2023_08_12_215933_add_is_guest_column_to_shipping_addresses_table', 72),
(232, '2023_08_15_000957_add_email_column_toshipping_address_table', 72),
(233, '2023_08_17_222330_add_identify_related_columns_to_admins_table', 72),
(234, '2023_08_20_230624_add_sent_by_and_send_to_in_notifications_table', 72),
(235, '2023_08_20_230911_create_notification_seens_table', 72),
(236, '2023_08_21_042331_add_theme_to_banners_table', 72),
(237, '2023_08_24_150009_add_free_delivery_over_amount_and_status_to_seller_table', 72),
(238, '2023_08_26_161214_add_is_shipping_free_to_orders_table', 72),
(239, '2023_08_26_173523_add_payment_method_column_to_wallet_transactions_table', 72),
(240, '2023_08_26_204653_add_verification_status_column_to_orders_table', 72),
(241, '2023_08_26_225113_create_order_delivery_verifications_table', 72),
(242, '2023_09_03_212200_add_free_delivery_responsibility_column_to_orders_table', 72),
(243, '2023_09_23_153314_add_shipping_responsibility_column_to_orders_table', 72),
(244, '2023_09_25_152733_create_digital_product_otp_verifications_table', 72),
(245, '2023_09_27_191638_add_attachment_column_to_support_ticket_convs_table', 73),
(246, '2023_10_01_205117_add_attachment_column_to_chattings_table', 73),
(247, '2023_10_07_182714_create_notification_messages_table', 73),
(248, '2023_10_21_113354_add_app_language_column_to_users_table', 73),
(249, '2023_10_21_123433_add_app_language_column_to_sellers_table', 73),
(250, '2023_10_21_124657_add_app_language_column_to_delivery_men_table', 73),
(251, '2023_10_22_130225_add_attachment_to_support_tickets_table', 73),
(252, '2023_10_25_113233_make_message_nullable_in_chattings_table', 73),
(253, '2023_10_30_152005_make_attachment_column_type_change_to_reviews_table', 73),
(254, '2024_01_14_192546_add_slug_to_shops_table', 74),
(255, '2024_01_25_175421_add_country_code_to_emergency_contacts_table', 75),
(256, '2024_02_01_200417_add_denied_count_and_approved_count_to_refund_requests_table', 75),
(257, '2024_03_11_130425_add_seen_notification_and_notification_receiver_to_chattings_table', 76),
(258, '2024_03_12_123322_update_images_column_in_refund_requests_table', 76),
(259, '2024_03_21_134659_change_denied_note_column_type_to_text', 76),
(260, '2024_04_03_093637_create_email_templates_table', 77),
(261, '2024_04_17_102137_add_is_checked_column_to_carts_table', 77),
(262, '2024_04_23_130436_create_vendor_registration_reasons_table', 77),
(263, '2024_04_24_093932_add_type_to_help_topics_table', 77),
(264, '2024_05_20_133216_create_review_replies_table', 78),
(265, '2024_05_20_163043_add_image_alt_text_to_brands_table', 78),
(266, '2024_05_26_152030_create_digital_product_variations_table', 78),
(267, '2024_05_26_152339_create_product_seos_table', 78),
(268, '2024_05_27_184401_add_digital_product_file_types_and_digital_product_extensions_to_products_table', 78),
(269, '2024_05_30_101603_create_storages_table', 78),
(270, '2024_06_10_174952_create_robots_meta_contents_table', 78),
(271, '2024_06_12_105137_create_error_logs_table', 78),
(272, '2024_07_03_130217_add_storage_type_columns_to_product_table', 78),
(273, '2024_07_03_153301_add_icon_storage_type_to_catogory_table', 78),
(274, '2024_07_03_171214_add_image_storage_type_to_brands_table', 78),
(275, '2024_07_03_185048_add_storage_type_columns_to_shop_table', 78),
(276, '2024_07_31_133306_create_login_setups_table', 79),
(277, '2024_08_04_123750_add_preview_file_to_products_table', 79),
(278, '2024_08_04_123805_create_authors_table', 79),
(279, '2024_08_04_123845_create_publishing_houses_table', 79),
(280, '2024_08_04_124023_create_digital_product_authors_table', 79),
(281, '2024_08_04_124046_create_digital_product_publishing_houses_table', 79),
(282, '2024_08_25_130313_modify_email_column_as_nullable_in_users_table', 79),
(283, '2024_08_26_130313_modify_token_column_as_text_in_phone_or_email_verifications_table', 79),
(284, '2024_10_01_130036_add_paid_amount_column_in_orders_table', 80),
(285, '2024_10_01_131352_create_restock_products_table', 80),
(286, '2024_10_01_132315_create_restock_product_customers_table', 80),
(287, '2024_11_02_075917_create_stock_clearance_setups_table', 81),
(288, '2024_11_02_075931_create_stock_clearance_products_table', 81),
(289, '2024_11_04_162929_create_analytic_scripts_table', 81),
(290, '2025_01_13_100747_create_transfer_requests_table', 82),
(291, '2025_01_13_100812_create_transfer_request_products_table', 82),
(292, '2025_01_13_163109_create_delivery_area_table', 83),
(293, '2025_01_14_073759_create_shipping_method_area_table', 84),
(294, '2025_01_14_073759_create_shipping_method_areas_table', 85),
(295, '2025_01_13_100747_create_stock_requestsss_table', 86),
(296, '2025_01_13_100812_create_stock_request_productsss_table', 86),
(297, '2025_01_13_100812_create_stock_request_poproducts_table', 87),
(298, '2025_01_13_100812_create_stock_reqquest_products_table', 88),
(299, '2025_01_16_083618_create_zone_table', 89),
(300, '2025_01_16_112855_create_states_table', 90),
(301, '2025_01_16_112901_create_cities_table', 90),
(302, '2025_01_16_133225_add_coordinates_to_shipping_method_areas_table', 91),
(303, '2025_01_17_101919_create_delivery_statess_table', 92),
(304, '2025_01_17_101926_create_delivery_citssy_table', 92),
(305, '2025_01_17_101919_create_deliverywewewe_states_table', 93),
(306, '2025_01_17_101926_create_deliveryewew_cities_table', 93),
(307, '2025_01_17_101919_create_delivery_states_table', 94),
(308, '2025_01_17_101926_create_delivery_cities_table', 94),
(309, '2025_01_17_133149_create_stock_transferss_table', 95),
(310, '2025_01_17_133234_create_stock_transffer_products_table', 95),
(311, '2025_01_20_091722_create_departments_table', 96),
(312, '2025_01_20_095837_create_department_ussers_table', 97),
(313, '2025_01_20_095837_create_department_usssers_table', 98),
(314, '2025_01_20_091722_create_departsment_table', 99),
(315, '2025_01_20_095837_create_department_susers_table', 99),
(316, '2025_01_20_091722_create_department_table', 100),
(317, '2025_01_20_095837_create_department_users_table', 100),
(318, '2025_01_13_100812_create_stock_request_produscts_table', 101),
(319, '2025_01_17_133234_create_stock_transfer_prodsucts_table', 101),
(320, '2025_01_13_100747_create_stock_requests_table', 102),
(321, '2025_01_13_100812_create_stock_request_products_table', 102),
(322, '2025_01_17_133149_create_stock_transfers_table', 102),
(323, '2025_01_17_133234_create_stock_transfer_products_table', 102),
(324, '2025_01_22_130810_create_manage_branches_product_stock_table', 103),
(325, '2025_01_22_130810_create_manage_branch_product_stock_table', 104),
(326, '2025_04_17_151137_add_deleted_at_to_branches_table', 105),
(327, '2025_04_17_151531_add_deleted_at_to_branches_table', 106),
(328, '2025_04_19_140839_add_status_and_approved_at_to_stock_transfer_products_table', 107),
(329, '2025_04_20_192023_create_stock_received_table', 108),
(330, '2025_04_22_074246_create_wholesaler_summary_table', 109),
(331, '2025_04_22_081338_add_wholesale_fields_to_users_table', 110),
(332, '2025_04_22_081334_create_wholesale_price_tiers_table', 111),
(333, '2025_04_22_134204_add_tier_and_discount_to_wholesale_price_ranges_table', 112),
(334, '2025_05_05_180242_create_wholesale_orders_table', 113),
(335, '2025_05_05_180514_create_wholesale_order_items_table', 114),
(336, '2025_05_09_154557_add_final_price_and_notes_to_wholesale_orders_table', 115),
(337, '2025_05_09_154904_add_approved_quantity_and_status_to_wholesale_order_items_table', 116),
(338, '2025_05_21_101020_create_quotation_metas_table', 117),
(339, '2025_05_22_145606_create_wholesale_confirmorder_item_table', 118),
(340, '2025_05_24_100754_create_wholesaler_registration_reasons_table', 119),
(341, '2025_05_28_093458_create_home_page_sections_table', 120),
(342, '2025_06_06_084658_add_match_filters_to_products_table', 121),
(343, '2025_06_10_113045_create_cms_products_table', 122),
(344, '2025_06_14_155318_create_activity_log_table', 123),
(345, '2025_06_14_155319_add_event_column_to_activity_log_table', 124),
(346, '2025_06_14_155320_add_batch_uuid_column_to_activity_log_table', 125),
(347, '2025_06_15_113521_create_services_table', 126),
(348, '2025_06_15_141405_update_foreign_keys_on_branch_stock_table', 127),
(349, '2025_06_24_132810_create_vehicle_makes_table', 128),
(350, '2025_06_24_132813_create_vehicle_models_table', 129),
(351, '2025_06_24_132814_create_vehicle_years_table', 130),
(352, '2025_06_28_100112_create_service_requests_table', 131),
(353, '2025_07_14_122147_create_permission_tables', 132),
(354, '2025_07_14_143520_add_deleted_at_to_products_table', 133),
(355, '2025_07_20_233045_add_batch_uuid_column_to_activity_log_table', 134),
(356, '2025_07_21_111250_add_teams_fields', 134),
(357, '2025_07_21_111251_create_laravel_crm_tables', 134),
(358, '2025_07_21_111252_create_laravel_crm_settings_table', 134),
(359, '2025_07_21_111253_add_fields_to_roles_permissions_tables', 134),
(360, '2025_07_21_111254_add_label_editable_fields_to_laravel_crm_settings_table', 134),
(361, '2025_07_21_111255_add_team_id_to_laravel_crm_tables', 134),
(362, '2025_07_21_111256_create_laravel_crm_products_table', 134),
(363, '2025_07_21_111257_create_laravel_crm_product_categories_table', 134),
(364, '2025_07_21_111258_create_laravel_crm_product_prices_table', 134),
(365, '2025_07_21_111259_create_laravel_crm_product_variations_table', 134),
(366, '2025_07_21_111300_create_laravel_crm_deal_products_table', 134),
(367, '2025_07_21_111301_add_global_to_laravel_crm_settings_table', 134),
(368, '2025_07_21_111302_alter_fields_for_encryption_on_laravel_crm_tables', 134),
(369, '2025_07_21_111303_create_laravel_crm_address_types_table', 134),
(370, '2025_07_21_111304_alter_type_on_laravel_crm_phones_table', 134),
(371, '2025_07_21_111305_add_description_to_laravel_crm_labels_table', 134),
(372, '2025_07_21_111306_add_name_to_laravel_crm_addresses_table', 134),
(373, '2025_07_21_111307_create_laravel_crm_contacts_table', 134),
(374, '2025_07_21_111308_create_laravel_crm_contact_types_table', 134),
(375, '2025_07_21_111309_create_laravel_crm_contact_contact_type_table', 134),
(376, '2025_07_21_111310_create_audits_table', 134),
(377, '2025_07_21_111311_create_devices_table', 134),
(378, '2025_07_21_111312_create_logins_table', 134),
(379, '2025_07_21_111313_update_logins_and_devices_table_user_relation', 134),
(380, '2025_07_21_111314_create_laravel_crm_organisation_types_table', 134),
(381, '2025_07_21_111315_change_morph_col_names_on_laravel_crm_notes_table', 134),
(382, '2025_07_21_111316_add_related_note_to_laravel_crm_notes_table', 134),
(383, '2025_07_21_111317_add_noted_at_to_laravel_crm_notes_table', 134),
(384, '2025_07_21_111318_create_laravel_crm_quotes_table', 134),
(385, '2025_07_21_111319_create_laravel_crm_quote_products_table', 134),
(386, '2025_07_21_111320_create_laravel_crm_files_table', 134),
(387, '2025_07_21_111321_add_mime_to_laravel_crm_files_table', 134),
(388, '2025_07_21_111322_create_xero_tokens_table', 134),
(389, '2025_07_21_111323_create_laravel_crm_xero_items_table', 134),
(390, '2025_07_21_111324_create_laravel_crm_xero_contacts_table', 134),
(391, '2025_07_21_111325_create_laravel_crm_xero_people_table', 134),
(392, '2025_07_21_111326_add_reference_to_laravel_crm_quotes_table', 134),
(393, '2025_07_21_111327_create_laravel_crm_tasks_table', 134),
(394, '2025_07_21_111328_add_deleted_at_to_laravel_crm_activities_table', 134),
(395, '2025_07_21_111329_create_laravel_crm_timezones_table', 134),
(396, '2025_07_21_111330_add_team_id_to_xero_tokens_table', 134),
(397, '2025_07_21_111331_create_laravel_crm_orders_table', 134),
(398, '2025_07_21_111332_create_laravel_crm_order_products_table', 134),
(399, '2025_07_21_111333_create_laravel_crm_invoices_table', 134),
(400, '2025_07_21_111334_create_laravel_crm_invoice_lines_table', 134),
(401, '2025_07_21_111335_add_reference_to_laravel_crm_orders_table', 134),
(402, '2025_07_21_111336_create_laravel_crm_calls_table', 134),
(403, '2025_07_21_111337_create_laravel_crm_meetings_table', 134),
(404, '2025_07_21_111338_create_laravel_crm_lunches_table', 134),
(405, '2025_07_21_111339_add_location_to_laravel_crm_activities_table', 134),
(406, '2025_07_21_111340_add_prefix_to_laravel_crm_invoices_table', 134),
(407, '2025_07_21_111341_create_laravel_crm_usage_requests_table', 134),
(408, '2025_07_21_111342_add_label_type_to_laravel_crm_fields_table', 134),
(409, '2025_07_21_111343_create_laravel_crm_field_models_table', 134),
(410, '2025_07_21_111344_create_laravel_crm_field_groups_table', 134),
(411, '2025_07_21_111345_add_team_id_to_laravel_crm_usage_requests_table', 134),
(412, '2025_07_21_111346_alter_field_group_id_on_laravel_crm_fields_table', 134),
(413, '2025_07_21_111347_add_system_to_laravel_crm_fields_table', 134),
(414, '2025_07_21_111348_add_comments_to_laravel_crm_quote_products_table', 134),
(415, '2025_07_21_111349_add_comments_to_laravel_crm_order_products_table', 134),
(416, '2025_07_21_111350_create_laravel_crm_deliveries_table', 134),
(417, '2025_07_21_111351_create_laravel_crm_delivery_products_table', 134),
(418, '2025_07_21_111352_alter_url_on_laravel_crm_usage_requests_table', 134),
(419, '2025_07_21_111353_create_laravel_crm_clients_table', 134),
(420, '2025_07_21_111354_create_laravel_crm_xero_invoices_table', 134),
(421, '2025_07_21_111355_add_contact_to_laravel_crm_addresses_table', 134),
(422, '2025_07_21_111356_add_phone_to_laravel_crm_addresses_table', 134),
(423, '2025_07_21_111357_add_name_to_laravel_crm_clients_table', 134),
(424, '2025_07_21_111358_add_delivery_dates_to_laravel_crm_deliveries_table', 134),
(425, '2025_07_21_111359_add_client_to_laravel_crm_orders_table', 134),
(426, '2025_07_21_111400_add_client_to_laravel_crm_leads_table', 134),
(427, '2025_08_04_132519_add_order_id_to_activity_log_table', 135),
(428, '2025_08_05_155248_add_teams_fields', 136),
(429, '2025_08_22_154206_create_career_applies_table', 137),
(430, '2025_08_23_142213_create_inbox_messages_table', 138),
(431, '2025_09_02_150611_add_extra_columns_to_inbox_messages_table', 139),
(432, '2025_09_03_140627_create_leads_table', 140),
(433, '2025_09_11_123635_create_leads_table', 141),
(434, '2025_09_11_123636_create_deals_table', 141),
(435, '2025_09_12_093210_create_activities_table', 142),
(436, '2025_09_17_140128_add_lead_id_to_deals', 143),
(440, '2025_09_19_122811_create_lead_activities_table', 144),
(441, '2025_09_19_122833_create_lead_notes_table', 144),
(442, '2025_09_19_122915_create_lead_tasks_table', 144),
(443, '2025_09_19_122926_create_lead_calls_table', 144),
(444, '2025_09_19_123239_create_lead_files_table', 144),
(445, '2025_09_22_120252_create_inbox_activities_table', 145),
(446, '2025_09_22_120322_create_inbox_notes_table', 145),
(447, '2025_09_22_120334_create_inbox_tasks_table', 145),
(448, '2025_09_22_120342_create_inbox_calls_table', 145),
(449, '2025_09_22_120351_create_inbox_files_table', 145),
(450, '2025_09_22_153800_update_deals_table', 146),
(451, '2025_09_23_134722_create_lead_notifications_table', 147),
(452, '2025_09_23_155311_create_deal_tasks_table', 148),
(453, '2025_09_23_155312_create_deal_notes_table', 148),
(454, '2025_09_23_155313_create_deal_files_table', 148),
(455, '2025_09_23_155314_create_deal_calls_table', 148),
(456, '2025_09_23_155330_create_deal_activities_table', 148),
(457, '2025_09_29_101747_add_order_fields_to_deals_table', 149),
(458, '2025_09_30_084551_add_extra_fields_to_support_tickets_table', 150),
(459, '2025_10_04_163156_create_service_jobs_table', 151),
(460, '2025_10_04_163414_create_service_job_items_table', 152),
(461, '2025_10_04_163506_create_service_job_activities_table', 153),
(462, '2025_10_04_163514_create_service_invoices_table', 153),
(463, '2025_10_09_134500_create_inbox_suggestions_table', 154),
(464, '2025_10_10_132431_create_sla_policies_table', 155),
(465, '2025_10_10_132505_create_sla_breaches_table', 155),
(466, '2025_10_10_133647_sla_fields_to_entities', 156),
(467, '2025_10_14_092135_create_calendar_todos_table', 157),
(468, '2025_10_14_123844_create_support_ticket_activities_table', 158),
(469, '2025_10_15_085442_create_escalations_table', 159),
(470, '2025_10_15_094752_create_admin_notifications_table', 160),
(471, '2025_10_16_125042_add_warranty_duration_to_products_table', 161),
(472, '2025_10_16_125045_create_warranties_table', 161),
(473, '2025_10_16_125046_create_warranty_claims_table', 161),
(476, '2025_10_16_125047_create_warranty_distribution_histories_table', 162),
(477, '2025_10_16_125048_create_warranty_replacements_table', 162),
(478, '2025_10_16_125049_create_work_orders_table', 162),
(480, '2025_10_16_125051_create_blacklists_table', 163),
(481, '2025_10_16_125052_create_activation_reviews_table', 163),
(482, '2025_10_16_125053_create_warranty_claim_attachments_table', 163),
(483, '2025_10_16_125053_create_warranty_timeline_events_table', 163),
(484, '2025_10_27_102754_create_policies_table', 164),
(485, '2025_10_27_112841_create_view_tokens_table', 165),
(486, '2025_10_27_113645_add_warranty_public_id_and_consent_fields_to_warranties_table', 166),
(487, '2025_10_28_090241_create_serial_transfer_histories_table', 167),
(488, '2025_11_03_121421_add_claim_status_fields_to_warranty_claims_table', 168),
(489, '2025_11_03_121744_add_checklists_and_photos_to_warranty_claims_table', 169),
(490, '2025_11_03_144504_add_work_order_columns_to_work_orders_table', 170),
(491, '2025_11_05_080202_create_warranty_claim_charges_table', 171),
(492, '2025_11_10_110019_create_warranty_timeline_events_table', 172),
(493, '2025_11_11_152407_create_crm_calls_table', 173),
(494, '2026_01_28_155415_create_product_stock_transactions_table', 174),
(495, '2026_02_14_024500_harden_manage_branch_product_stock_uniqueness', 175),
(496, '2026_02_14_230500_fix_order_details_is_stock_decreased_default', 176),
(497, '2026_02_15_001000_add_rank_to_wholesale_tiers_table', 177),
(498, '2026_02_20_090000_add_ucm_metadata_to_crm_calls_table', 178),
(499, '2026_02_27_120000_add_refund_uniqueness_indexes', 179),
(500, '2026_02_26_120000_align_warranty_schema_with_module_logic', 180),
(501, '2026_02_26_180000_create_spatie_permission_tables_for_admin_guard', 180),
(502, '2026_03_02_120000_add_is_supervisor_to_admins_table', 180),
(503, '2026_03_04_210000_create_pos_cart_states_table', 181),
(504, '2026_03_05_090000_rename_products_is_warranty_to_is_traceable', 182),
(505, '2026_03_05_203500_add_is_warranty_to_products_table', 183),
(506, '2026_03_05_220000_assign_super_admin_role_to_master_admin_user', 184),
(507, '2026_03_06_120000_create_warranty_claim_payments_table', 185),
(508, '2026_03_06_140000_add_payment_link_lifecycle_fields_to_service_invoices_table', 186),
(509, '2026_03_08_160000_add_is_traceable_to_products_table', 186),
(510, '2026_03_13_120000_add_service_id_to_support_tickets_table', 186),
(513, '2026_03_18_223538_create_branch_area_pivot_tables', 187),
(514, '2026_03_18_223711_add_branch_performance_indexes', 188),
(515, '2026_03_19_000001_fix_inbox_message_id_typo', 189),
(516, '2026_03_19_000002_add_soft_deletes_to_crm_tables', 190),
(517, '2026_03_19_000003_add_crm_performance_indexes', 191),
(518, '2026_03_19_000004_add_delivery_performance_indexes', 192),
(519, '2026_03_19_000005_add_products_performance_indexes', 193),
(520, '2026_03_19_000006_add_service_performance_indexes', 194),
(521, '2026_03_19_000008_add_support_performance_indexes', 195),
(522, '2026_03_18_002_fix_claim_column_names', 196),
(523, '2026_03_18_007_fix_claim_unique_constraint', 197),
(524, '2026_03_18_001_fix_warranty_status_enum', 198),
(525, '2026_03_19_000009_add_warranty_performance_indexes', 199),
(526, '2026_03_19_000010_fix_warranty_unique_constraint', 200),
(527, '2026_03_19_000011_add_warranty_performance_indexes', 201),
(528, '2026_03_19_000012_add_wholesale_performance_indexes', 202),
(529, '2026_03_20_125126_add_effective_date_to_policies_table', 203),
(530, '2025_01_01_002_products_catalog_tables', 204),
(531, '2025_01_01_003_orders_orders_tables', 204),
(532, '2025_01_01_006_delivery_tables', 204),
(533, '2025_01_01_007_service_module_tables', 204),
(534, '2025_01_01_008_support_tables', 204),
(535, '2025_01_01_011_career_module_tables', 204),
(536, '2025_01_01_015_crm_module_tables', 204),
(537, '2026_03_18_003_fix_warranty_foreign_keys', 204),
(538, '2026_03_18_004_fix_replacement_technician_fk', 204),
(539, '2026_03_18_005_fix_timeline_user_fk', 204),
(540, '2026_03_18_006_add_blacklist_serial_index', 204),
(541, '2026_03_19_000007_create_service_job_tables', 204),
(542, '2026_03_19_120000_drop_contact_us_table', 204),
(543, '2026_03_22_193953_add_effective_date_to_policies_table', 205),
(544, '2026_03_22_120000_create_vehicle_years_table', 206),
(545, '2026_03_28_000001_add_unique_index_to_wholesale_purchase_orders_purchase_order_no', 207),
(546, '2026_03_28_120000_add_missing_unique_indexes_to_warranty_claim_identifiers', 208);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(3, 'App\\Models\\Admin', 1),
(14, 'App\\Models\\Admin', 1),
(16, 'App\\Models\\Admin', 17),
(18, 'App\\Models\\Admin', 18),
(15, 'App\\Models\\Admin', 19),
(16, 'App\\Models\\Admin', 20),
(9, 'App\\Models\\Admin', 21),
(17, 'App\\Models\\Admin', 22),
(15, 'App\\Models\\Admin', 23),
(16, 'App\\Models\\Admin', 24),
(9, 'App\\Models\\Admin', 25),
(17, 'App\\Models\\Admin', 26),
(6, 'App\\Models\\Admin', 27),
(16, 'App\\Models\\Admin', 28),
(19, 'App\\Models\\Admin', 29),
(19, 'App\\Models\\Admin', 30);

-- --------------------------------------------------------

--
-- Table structure for table `most_demandeds`
--

CREATE TABLE `most_demandeds` (
  `id` bigint UNSIGNED NOT NULL,
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `sent_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'system',
  `sent_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_count` int NOT NULL DEFAULT '0',
  `image` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_messages`
--

CREATE TABLE `notification_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_messages`
--

INSERT INTO `notification_messages` (`id`, `user_type`, `key`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'customer', 'order_pending_message', 'customize your order pending message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(2, 'customer', 'order_confirmation_message', 'customize your order confirmation message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(3, 'customer', 'order_processing_message', 'customize your order processing message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(4, 'customer', 'out_for_delivery_message', 'customize your out for delivery message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(5, 'customer', 'order_delivered_message', 'customize your order delivered message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(6, 'customer', 'order_returned_message', 'customize your order returned message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(7, 'customer', 'order_failed_message', 'customize your order failed message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(8, 'customer', 'order_canceled', 'customize your order canceled message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(9, 'customer', 'order_refunded_message', 'customize your order refunded message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(10, 'customer', 'refund_request_canceled_message', 'customize your refund request canceled message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(11, 'customer', 'message_from_delivery_man', 'customize your message from delivery man message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(12, 'customer', 'message_from_admin', 'customize your message from admin message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(13, 'customer', 'message_from_seller', 'customize your message from seller message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(14, 'customer', 'fund_added_by_admin_message', 'customize your fund added by admin message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(15, 'seller', 'new_order_message', 'customize your new order message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(16, 'seller', 'refund_request_message', 'customize your refund request message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(17, 'seller', 'order_edit_message', 'customize your order edit message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(18, 'seller', 'withdraw_request_status_message', 'customize your withdraw request status message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(19, 'seller', 'message_from_customer', 'customize your message from customer message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(20, 'seller', 'message_from_delivery_man', 'customize your message from delivery man message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(21, 'seller', 'delivery_man_assign_by_admin_message', 'customize your delivery man assign by admin message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(22, 'seller', 'order_delivered_message', 'customize your order delivered message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(23, 'seller', 'order_canceled', 'customize your order canceled message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(24, 'seller', 'order_refunded_message', 'customize your order refunded message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(25, 'seller', 'refund_request_canceled_message', 'customize your refund request canceled message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(26, 'seller', 'refund_request_status_changed_by_admin', 'customize your refund request status changed by admin message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(27, 'seller', 'product_request_approved_message', 'customize your product request approved message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(28, 'seller', 'product_request_rejected_message', 'customize your product request rejected message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(29, 'delivery_man', 'new_order_assigned_message', 'customize your new order assigned message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(30, 'delivery_man', 'expected_delivery_date', 'customize your expected delivery date message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(31, 'delivery_man', 'delivery_man_charge', 'customize your delivery man charge message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(32, 'delivery_man', 'order_canceled', 'customize your order canceled message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(33, 'delivery_man', 'order_rescheduled_message', 'customize your order rescheduled message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(34, 'delivery_man', 'order_edit_message', 'customize your order edit message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(35, 'delivery_man', 'message_from_seller', 'customize your message from seller message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(36, 'delivery_man', 'message_from_admin', 'customize your message from admin message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(37, 'delivery_man', 'message_from_customer', 'customize your message from customer message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(38, 'delivery_man', 'cash_collect_by_admin_message', 'customize your cash collect by admin message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(39, 'delivery_man', 'cash_collect_by_seller_message', 'customize your cash collect by seller message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50'),
(40, 'delivery_man', 'withdraw_request_status_message', 'customize your withdraw request status message message', 1, '2025-12-29 13:13:50', '2025-12-29 13:13:50');

-- --------------------------------------------------------

--
-- Table structure for table `notification_seens`
--

CREATE TABLE `notification_seens` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `notification_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `client_id` int UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint NOT NULL,
  `client_id` int UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` int UNSIGNED NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `provider` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` int UNSIGNED NOT NULL,
  `client_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offline_payments`
--

CREATE TABLE `offline_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` int NOT NULL,
  `payment_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offline_payment_methods`
--

CREATE TABLE `offline_payment_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `method_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_fields` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_informations` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transfer_from_branch` int NOT NULL DEFAULT '0',
  `is_guest` tinyint NOT NULL DEFAULT '0',
  `customer_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `order_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_ref` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_note` text COLLATE utf8mb4_unicode_ci,
  `order_amount` double NOT NULL DEFAULT '0',
  `paid_amount` double(8,2) NOT NULL DEFAULT '0.00',
  `admin_commission` decimal(8,2) NOT NULL DEFAULT '0.00',
  `is_pause` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `cause` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `discount_amount` double NOT NULL DEFAULT '0',
  `discount_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coupon_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coupon_discount_bearer` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inhouse',
  `shipping_responsibility` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_method_id` bigint NOT NULL DEFAULT '0',
  `shipping_cost` double(8,2) NOT NULL DEFAULT '0.00',
  `is_shipping_free` tinyint(1) NOT NULL DEFAULT '0',
  `order_group_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def-order-group',
  `verification_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `verification_status` tinyint NOT NULL DEFAULT '0',
  `seller_id` bigint DEFAULT NULL,
  `seller_is` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address_data` text COLLATE utf8mb4_unicode_ci,
  `delivery_man_id` bigint DEFAULT NULL,
  `deliveryman_charge` double NOT NULL DEFAULT '0',
  `expected_delivery_date` date DEFAULT NULL,
  `order_note` text COLLATE utf8mb4_unicode_ci,
  `billing_address` bigint UNSIGNED DEFAULT NULL,
  `billing_address_data` text COLLATE utf8mb4_unicode_ci,
  `order_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default_type',
  `extra_discount` double(8,2) NOT NULL DEFAULT '0.00',
  `extra_discount_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installation_charge` float DEFAULT '0',
  `exchange_charge` float DEFAULT '0',
  `free_delivery_bearer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `shipping_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_from_branch` int NOT NULL DEFAULT '0',
  `delivery_service_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `third_party_delivery_tracking_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exchange_status` int DEFAULT '0',
  `exchange_product_info` int DEFAULT NULL,
  `exchange_amount` int DEFAULT '0',
  `wholesale_discount` float(12,6) DEFAULT '0.000000',
  `wholesale_spacial_discount` float(12,6) DEFAULT '0.000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_delivery_verifications`
--

CREATE TABLE `order_delivery_verifications` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `image` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `seller_id` bigint DEFAULT NULL,
  `digital_file_after_sell` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_details` text COLLATE utf8mb4_unicode_ci,
  `qty` int NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `tax` double NOT NULL DEFAULT '0',
  `discount` double NOT NULL DEFAULT '0',
  `installtion_charges` double DEFAULT '0',
  `exchange_qty` int DEFAULT '0',
  `exchange_charges` double DEFAULT '0',
  `wholesale_discount` double(12,6) NOT NULL DEFAULT '0.000000',
  `wholesale_spacial_discount` double(12,6) NOT NULL DEFAULT '0.000000',
  `tax_model` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'exclude',
  `delivery_status` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shipping_method_id` bigint DEFAULT NULL,
  `variant` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_stock_decreased` tinyint(1) NOT NULL DEFAULT '0',
  `refund_request` int NOT NULL DEFAULT '0',
  `warranty_status` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_expected_delivery_histories`
--

CREATE TABLE `order_expected_delivery_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `user_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expected_delivery_date` date NOT NULL,
  `cause` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status_histories`
--

CREATE TABLE `order_status_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint NOT NULL,
  `user_id` bigint NOT NULL,
  `user_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cause` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_transactions`
--

CREATE TABLE `order_transactions` (
  `seller_id` bigint NOT NULL,
  `order_id` bigint NOT NULL,
  `order_amount` decimal(50,2) NOT NULL DEFAULT '0.00',
  `seller_amount` decimal(50,2) NOT NULL DEFAULT '0.00',
  `admin_commission` decimal(50,2) NOT NULL DEFAULT '0.00',
  `received_by` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_charge` decimal(50,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(50,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `customer_id` bigint DEFAULT NULL,
  `seller_is` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivered_by` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `identity` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp_hit_count` tinyint NOT NULL DEFAULT '0',
  `is_temp_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `temp_block_time` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payer_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `gateway_callback_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `success_hook` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_hook` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payer_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `external_redirect_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `attribute_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_platform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_terms`
--

CREATE TABLE `payment_terms` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `wholesaler_id` int NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `particulars` text COLLATE utf8mb4_general_ci NOT NULL,
  `isPaymentReceived` tinyint NOT NULL DEFAULT '0' COMMENT '1 for payment received  ',
  `payment_receipt` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paytabs_invoices`
--

CREATE TABLE `paytabs_invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `result` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_code` int UNSIGNED NOT NULL,
  `pt_invoice_id` int UNSIGNED DEFAULT NULL,
  `amount` double(8,2) DEFAULT NULL,
  `currency` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` int UNSIGNED DEFAULT NULL,
  `card_brand` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_first_six_digits` int UNSIGNED DEFAULT NULL,
  `card_last_four_digits` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crm_permission` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phone_or_email_verifications`
--

CREATE TABLE `phone_or_email_verifications` (
  `id` bigint UNSIGNED NOT NULL,
  `phone_or_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` text COLLATE utf8mb4_unicode_ci,
  `otp_hit_count` tinyint NOT NULL DEFAULT '0',
  `is_temp_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `temp_block_time` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `version`, `effective_date`, `value`, `created_by`, `published_at`, `created_at`, `updated_at`) VALUES
(1, '125.00020', NULL, '<h1 data-path-to-node=\"1\">Warranty Policy</h1><p data-path-to-node=\"2\"><b data-path-to-node=\"2\" data-index-in-node=\"0\">elnisr.online</b>\r\n<b data-path-to-node=\"2\" data-index-in-node=\"14\">Effective Date:</b> January 8, 2026</p><p data-path-to-node=\"3\">At <b data-path-to-node=\"3\" data-index-in-node=\"3\">elnisr.online</b>, we stand behind the quality of our products and services. This Warranty Policy outlines the protections provided to our customers and the process for filing a claim.</p><hr data-path-to-node=\"4\"><h4 data-path-to-node=\"5\">1. Warranty Coverage</h4><p data-path-to-node=\"6\">We provide a limited warranty on products and services purchased directly through our platform. This warranty covers:</p><ul data-path-to-node=\"7\"><li><p data-path-to-node=\"7,0,0\"><b data-path-to-node=\"7,0,0\" data-index-in-node=\"0\">Defects in materials</b> and manufacturing.</p></li><li><p data-path-to-node=\"7,1,0\"><b data-path-to-node=\"7,1,0\" data-index-in-node=\"0\">Functionality issues</b> that prevent the service or product from performing as described.</p></li><li><p data-path-to-node=\"7,2,0\"><b data-path-to-node=\"7,2,0\" data-index-in-node=\"0\">Workmanship errors</b> occurring under normal use.</p></li></ul><h4 data-path-to-node=\"8\">2. Warranty Period</h4><p data-path-to-node=\"9\">The duration of the warranty depends on the item purchased:</p><ul data-path-to-node=\"10\"><li><p data-path-to-node=\"10,0,0\"><b data-path-to-node=\"10,0,0\" data-index-in-node=\"0\">Physical Products:</b> [e.g., 12 months] from the date of delivery.</p></li><li><p data-path-to-node=\"10,1,0\"><b data-path-to-node=\"10,1,0\" data-index-in-node=\"0\">Digital Services:</b> Valid for the duration of the active subscription or [e.g., 90 days] post-delivery for one-time projects.</p></li></ul><h4 data-path-to-node=\"11\">3. Exclusions</h4><p data-path-to-node=\"12\">This warranty <b data-path-to-node=\"12\" data-index-in-node=\"14\">does not</b> cover:</p><ul data-path-to-node=\"13\"><li><p data-path-to-node=\"13,0,0\">Damage resulting from misuse, abuse, or accidents.</p></li><li><p data-path-to-node=\"13,1,0\">Unauthorized modifications or repairs performed by third parties.</p></li><li><p data-path-to-node=\"13,2,0\">Normal wear and tear over time.</p></li><li><p data-path-to-node=\"13,3,0\">Issues caused by external factors (e.g., power surges, internet outages, or third-party software conflicts).</p></li></ul><h4 data-path-to-node=\"14\">4. Claim Process</h4><p data-path-to-node=\"15\">To exercise your warranty rights:</p><ol start=\"1\" data-path-to-node=\"16\"><li><p data-path-to-node=\"16,0,0\">Contact our support team at <b data-path-to-node=\"16,0,0\" data-index-in-node=\"28\">enlisrmisr@gmail.com</b>.</p></li><li><p data-path-to-node=\"16,1,0\">Provide your <b data-path-to-node=\"16,1,0\" data-index-in-node=\"13\">Order Number</b> and proof of purchase.</p></li><li><p data-path-to-node=\"16,2,0\">Include a detailed description (and photos/videos if applicable) of the defect.</p></li></ol><h4 data-path-to-node=\"17\">5. Remedies</h4><p data-path-to-node=\"18\">If a claim is found to be valid, we will, at our sole discretion:</p><ul data-path-to-node=\"19\"><li><p data-path-to-node=\"19,0,0\"><b data-path-to-node=\"19,0,0\" data-index-in-node=\"0\">Repair</b> the defect at no cost to the customer.</p></li><li><p data-path-to-node=\"19,1,0\"><b data-path-to-node=\"19,1,0\" data-index-in-node=\"0\">Replace</b> the item with a new or equivalent model.</p></li><li><p data-path-to-node=\"19,2,0\"><b data-path-to-node=\"19,2,0\" data-index-in-node=\"0\">Refund</b> the purchase price if a repair or replacement is not possible.</p></li></ul>', NULL, '2025-10-19 21:00:00', '2025-10-27 09:52:08', '2026-02-22 23:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `pos_cart_states`
--

CREATE TABLE `pos_cart_states` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `added_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint DEFAULT NULL,
  `shop_id` bigint DEFAULT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'physical',
  `is_traceable` tinyint(1) DEFAULT '0',
  `category_ids` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_id` int NOT NULL DEFAULT '0',
  `category_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_category_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_sub_category_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_id` bigint DEFAULT NULL,
  `unit` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_qty` int NOT NULL DEFAULT '1',
  `refundable` tinyint(1) NOT NULL DEFAULT '1',
  `digital_product_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `digital_file_ready` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `digital_file_ready_storage_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `images` longtext COLLATE utf8mb4_unicode_ci,
  `color_image` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_storage_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `preview_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preview_file_storage_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `featured` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_cms` tinyint(1) NOT NULL DEFAULT '0',
  `showcase_product` tinyint(1) NOT NULL DEFAULT '0',
  `flash_deal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_provider` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `colors` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_product` tinyint(1) NOT NULL DEFAULT '0',
  `attributes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `choice_options` text COLLATE utf8mb4_unicode_ci,
  `variation` text COLLATE utf8mb4_unicode_ci,
  `digital_product_file_types` longtext COLLATE utf8mb4_unicode_ci,
  `digital_product_extensions` longtext COLLATE utf8mb4_unicode_ci,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `unit_price` double NOT NULL DEFAULT '0',
  `purchase_price` double NOT NULL DEFAULT '0',
  `tax` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.00',
  `tax_type` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_model` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'exclude',
  `discount` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.00',
  `discount_type` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_stock` int DEFAULT NULL,
  `minimum_order_qty` int NOT NULL DEFAULT '1',
  `details` text COLLATE utf8mb4_unicode_ci,
  `warranty_duration` int NOT NULL DEFAULT '12',
  `free_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `attachment` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `featured_status` tinyint(1) NOT NULL DEFAULT '1',
  `meta_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_status` tinyint(1) NOT NULL DEFAULT '0',
  `denied_note` text COLLATE utf8mb4_unicode_ci,
  `shipping_cost` double(8,2) DEFAULT NULL,
  `multiply_qty` tinyint(1) DEFAULT NULL,
  `temp_shipping_cost` double(8,2) DEFAULT NULL,
  `is_shipping_cost_updated` tinyint(1) DEFAULT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `match_makes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `match_models` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `match_years` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_warranty` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_compares`
--

CREATE TABLE `product_compares` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL COMMENT 'customer_id',
  `product_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_seos`
--

CREATE TABLE `product_seos` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `index` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_follow` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_image_index` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_archive` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_snippet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_snippet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_snippet_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_video_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_video_preview_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_image_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_image_preview_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_stocks`
--

CREATE TABLE `product_stocks` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint DEFAULT NULL,
  `variant` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `qty` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_stock_transactions`
--

CREATE TABLE `product_stock_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `product_stock_id` bigint UNSIGNED NOT NULL,
  `type` enum('IN','OUT','TRANSFER') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_branch_id` bigint UNSIGNED DEFAULT NULL,
  `to_branch_id` bigint UNSIGNED DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_tag`
--

CREATE TABLE `product_tag` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `tag_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `publishing_houses`
--

CREATE TABLE `publishing_houses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_metas`
--

CREATE TABLE `quotation_metas` (
  `id` bigint UNSIGNED NOT NULL,
  `wholesale_quotation_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refund_requests`
--

CREATE TABLE `refund_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `order_details_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_count` tinyint NOT NULL DEFAULT '0',
  `denied_count` tinyint NOT NULL DEFAULT '0',
  `amount` double(8,2) NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `refund_reason` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `images` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approved_note` longtext COLLATE utf8mb4_unicode_ci,
  `rejected_note` longtext COLLATE utf8mb4_unicode_ci,
  `payment_info` longtext COLLATE utf8mb4_unicode_ci,
  `change_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refund_statuses`
--

CREATE TABLE `refund_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `refund_request_id` bigint UNSIGNED DEFAULT NULL,
  `change_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_by_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refund_transactions`
--

CREATE TABLE `refund_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `payment_for` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_id` bigint UNSIGNED DEFAULT NULL,
  `payment_receiver_id` bigint UNSIGNED DEFAULT NULL,
  `paid_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_to` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double(8,2) DEFAULT NULL,
  `transaction_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_details_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `refund_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restock_products`
--

CREATE TABLE `restock_products` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` int NOT NULL,
  `variant` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `restock_product_customers`
--

CREATE TABLE `restock_product_customers` (
  `id` bigint UNSIGNED NOT NULL,
  `restock_product_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `variant` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint NOT NULL,
  `customer_id` bigint NOT NULL,
  `delivery_man_id` bigint DEFAULT NULL,
  `order_id` bigint DEFAULT NULL,
  `comment` mediumtext COLLATE utf8mb4_general_ci,
  `attachment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `rating` int NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '1',
  `is_saved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_replies`
--

CREATE TABLE `review_replies` (
  `id` bigint UNSIGNED NOT NULL,
  `review_id` int NOT NULL,
  `added_by_id` int DEFAULT NULL,
  `added_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'customer, seller, admin, deliveryman',
  `reply_text` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `robots_meta_contents`
--

CREATE TABLE `robots_meta_contents` (
  `id` bigint UNSIGNED NOT NULL,
  `page_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonicals_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `index` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_follow` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_image_index` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_archive` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_snippet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_snippet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_snippet_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_video_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_video_preview_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_image_preview` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_image_preview_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crm_role` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `status`, `description`, `crm_role`, `created_at`, `updated_at`) VALUES
(3, 'Super Admin', 'admin', 1, NULL, 0, '2026-02-26 12:59:21', '2026-03-03 12:34:14'),
(5, 'RBAC Manager', 'admin', 1, NULL, 0, '2026-02-26 12:59:22', '2026-03-03 12:34:15'),
(6, 'CRM Manager', 'admin', 1, NULL, 0, '2026-02-26 12:59:22', '2026-03-03 12:34:15'),
(7, 'CRM Agent', 'admin', 1, NULL, 0, '2026-02-26 12:59:22', '2026-03-03 12:34:15'),
(8, 'SLA Manager', 'admin', 1, NULL, 0, '2026-02-26 12:59:23', '2026-03-03 12:34:15'),
(9, 'Warranty Manager', 'admin', 1, NULL, 0, '2026-02-26 12:59:23', '2026-03-03 12:34:13'),
(10, 'Inventory Manager', 'admin', 1, NULL, 0, '2026-02-26 12:59:23', '2026-03-03 12:34:15'),
(11, 'Sales Agent', 'admin', 1, NULL, 0, '2026-02-26 12:59:23', '2026-03-03 12:34:16'),
(12, 'Content Manager', 'admin', 1, NULL, 0, '2026-02-26 12:59:23', '2026-03-03 12:34:16'),
(13, 'Read Only Auditor', 'admin', 1, NULL, 0, '2026-02-26 12:59:24', '2026-03-03 12:34:16'),
(14, 'Master Admin', 'admin', 1, NULL, 0, '2026-02-26 13:15:06', '2026-03-03 12:34:13'),
(15, 'Support', 'admin', 1, NULL, 0, '2026-02-26 13:15:07', '2026-03-03 12:34:13'),
(16, 'wholesaler admin', 'admin', 1, NULL, 0, '2026-02-26 13:15:07', '2026-03-03 12:34:13'),
(17, 'CRM Customer', 'admin', 1, NULL, 0, '2026-02-26 13:15:07', '2026-03-03 12:34:14'),
(18, 'Abhishek Kurmi(warranty)', 'admin', 1, NULL, 0, '2026-02-26 13:15:07', '2026-03-03 12:34:14'),
(19, 'Branch Manager', 'admin', 1, NULL, 0, '2026-02-26 13:15:07', '2026-03-03 12:34:14'),
(20, 'Operations Manager', 'admin', 1, NULL, 0, '2026-02-26 18:56:11', '2026-03-03 12:34:16');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_functions`
--

CREATE TABLE `search_functions` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visible_for` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `search_functions`
--

INSERT INTO `search_functions` (`id`, `key`, `url`, `visible_for`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard', 'admin/dashboard', 'admin', NULL, NULL),
(2, 'Order All', 'admin/orders/list/all', 'admin', NULL, NULL),
(3, 'Order Pending', 'admin/orders/list/pending', 'admin', NULL, NULL),
(4, 'Order Processed', 'admin/orders/list/processed', 'admin', NULL, NULL),
(5, 'Order Delivered', 'admin/orders/list/delivered', 'admin', NULL, NULL),
(6, 'Order Returned', 'admin/orders/list/returned', 'admin', NULL, NULL),
(7, 'Order Failed', 'admin/orders/list/failed', 'admin', NULL, NULL),
(8, 'Brand Add', 'admin/brand/add-new', 'admin', NULL, NULL),
(9, 'Brand List', 'admin/brand/list', 'admin', NULL, NULL),
(10, 'Banner', 'admin/banner/list', 'admin', NULL, NULL),
(11, 'Category', 'admin/category/view', 'admin', NULL, NULL),
(12, 'Sub Category', 'admin/category/sub-category/view', 'admin', NULL, NULL),
(13, 'Sub sub category', 'admin/category/sub-sub-category/view', 'admin', NULL, NULL),
(14, 'Attribute', 'admin/attribute/view', 'admin', NULL, NULL),
(15, 'Product', 'admin/product/list', 'admin', NULL, NULL),
(16, 'Promotion', 'admin/coupon/add-new', 'admin', NULL, NULL),
(17, 'Custom Role', 'admin/custom-role/create', 'admin', NULL, NULL),
(18, 'Employee', 'admin/employee/add-new', 'admin', NULL, NULL),
(19, 'Seller', 'admin/sellers/seller-list', 'admin', NULL, NULL),
(20, 'Contacts', 'admin/contact/list', 'admin', NULL, NULL),
(21, 'Flash Deal', 'admin/deal/flash', 'admin', NULL, NULL),
(22, 'Deal of the day', 'admin/deal/day', 'admin', NULL, NULL),
(23, 'Language', 'admin/business-settings/language', 'admin', NULL, NULL),
(24, 'Mail', 'admin/business-settings/mail', 'admin', NULL, NULL),
(25, 'Shipping method', 'admin/business-settings/shipping-method/add', 'admin', NULL, NULL),
(26, 'Currency', 'admin/currency/view', 'admin', NULL, NULL),
(27, 'Payment method', 'admin/business-settings/payment-method', 'admin', NULL, NULL),
(28, 'SMS Gateway', 'admin/business-settings/sms-gateway', 'admin', NULL, NULL),
(29, 'Support Ticket', 'admin/support-ticket/view', 'admin', NULL, NULL),
(30, 'FAQ', 'admin/helpTopic/list', 'admin', NULL, NULL),
(31, 'About Us', 'admin/business-settings/about-us', 'admin', NULL, NULL),
(32, 'Terms and Conditions', 'admin/business-settings/terms-condition', 'admin', NULL, NULL),
(33, 'Web Config', 'admin/business-settings/web-config', 'admin', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `id` bigint UNSIGNED NOT NULL,
  `f_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `l_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def.png',
  `email` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bank_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_no` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `holder_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` text COLLATE utf8mb4_unicode_ci,
  `sales_commission_percentage` double(8,2) DEFAULT NULL,
  `gst` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cm_firebase_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pos_status` tinyint(1) NOT NULL DEFAULT '0',
  `minimum_order_amount` double(8,2) NOT NULL DEFAULT '0.00',
  `free_delivery_status` int NOT NULL DEFAULT '0',
  `free_delivery_over_amount` double(8,2) NOT NULL DEFAULT '0.00',
  `app_language` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seller_wallets`
--

CREATE TABLE `seller_wallets` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint DEFAULT NULL,
  `total_earning` double NOT NULL DEFAULT '0',
  `withdrawn` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `commission_given` double(8,2) NOT NULL DEFAULT '0.00',
  `pending_withdraw` double(8,2) NOT NULL DEFAULT '0.00',
  `delivery_charge_earned` double(8,2) NOT NULL DEFAULT '0.00',
  `collected_cash` double(8,2) NOT NULL DEFAULT '0.00',
  `total_tax_collected` double(8,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seller_wallets`
--

INSERT INTO `seller_wallets` (`id`, `seller_id`, `total_earning`, `withdrawn`, `created_at`, `updated_at`, `commission_given`, `pending_withdraw`, `delivery_charge_earned`, `collected_cash`, `total_tax_collected`) VALUES
(1, 1, 0, 0, '2026-01-03 13:00:10', '2026-01-03 13:00:10', 0.00, 0.00, 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `seller_wallet_histories`
--

CREATE TABLE `seller_wallet_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint DEFAULT NULL,
  `amount` double NOT NULL DEFAULT '0',
  `order_id` bigint DEFAULT NULL,
  `product_id` bigint DEFAULT NULL,
  `payment` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `serial_transfer_histories`
--

CREATE TABLE `serial_transfer_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_transfer_id` bigint UNSIGNED DEFAULT NULL,
  `wholesale_delivery_id` bigint UNSIGNED DEFAULT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_branch_id` bigint UNSIGNED DEFAULT NULL,
  `to_branch_id` bigint UNSIGNED DEFAULT NULL,
  `distributor_id` bigint UNSIGNED DEFAULT NULL,
  `transfer_type` enum('branch_to_branch','branch_to_wholesale') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transferred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_price_inshop` decimal(10,2) NOT NULL,
  `base_price_mobile` decimal(10,2) NOT NULL,
  `parts_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `included_km_mobile` int NOT NULL DEFAULT '0',
  `travel_fee_per_km` decimal(10,2) NOT NULL DEFAULT '0.00',
  `labor_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `parts_included` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `call_center_flag` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_cancellations`
--

CREATE TABLE `service_cancellations` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `job_id` bigint UNSIGNED DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_general_ci,
  `fee_amount` decimal(10,2) DEFAULT NULL COMMENT 'Policy-based cancellation fee',
  `refund_amount` decimal(10,2) DEFAULT NULL COMMENT 'Refund after deducting fee',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp for soft deletes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_change_orders`
--

CREATE TABLE `service_change_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `job_id` bigint UNSIGNED DEFAULT NULL,
  `additional_charges` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `image` longtext COLLATE utf8mb4_general_ci,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp for soft deletes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_estimates`
--

CREATE TABLE `service_estimates` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `extra` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `service_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp for soft deletes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_invoices`
--

CREATE TABLE `service_invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `job_id` bigint UNSIGNED DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_status` enum('pending','paid','partial','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_link_expires_at` timestamp NULL DEFAULT NULL,
  `gateway_payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `estimate_id` int DEFAULT NULL COMMENT 'Reference to initial estimate',
  `is_estimate` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Final Invoice, 1 = Estimate',
  `change_order_id` int DEFAULT NULL COMMENT 'Reference to change order',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp for soft deletes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_jobs`
--

CREATE TABLE `service_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `technician_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `service_mode` enum('in_shop','mobile') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `odometer_start` int DEFAULT NULL,
  `odometer_end` int DEFAULT NULL,
  `gps_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `customer_signature` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Base64 encoded signature',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'low',
  `sla_hours` int DEFAULT NULL COMMENT 'SLA in hours',
  `service_sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Service SKU (e.g., Oil Change)',
  `is_mobile` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = In-shop, 1 = Mobile',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp for soft deletes',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_job_activities`
--

CREATE TABLE `service_job_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `job_id` bigint UNSIGNED NOT NULL,
  `activity_type` enum('status_change','photo_upload','qa_call','reopen','note') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `part_id` int DEFAULT NULL COMMENT 'ID of part scanned/consumed',
  `labor_hours` decimal(5,2) DEFAULT NULL COMMENT 'Labor hours tracked',
  `gps_coordinates` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'GPS location for mobile jobs',
  `odometer_reading` int DEFAULT NULL COMMENT 'Odometer reading for mobile jobs',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Timestamp for soft deletes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_job_items`
--

CREATE TABLE `service_job_items` (
  `id` bigint UNSIGNED NOT NULL,
  `job_id` bigint UNSIGNED NOT NULL,
  `item_type` enum('service','part','labor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` bigint UNSIGNED DEFAULT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `rate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `service_option` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `vehicle_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_make` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_mileage` int DEFAULT NULL,
  `vin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_guest` tinyint NOT NULL DEFAULT '0',
  `contact_person_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_billing` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `creator_id` bigint DEFAULT NULL,
  `creator_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost` decimal(8,2) NOT NULL DEFAULT '0.00',
  `duration` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_method_areas`
--

CREATE TABLE `shipping_method_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `creator_id` bigint UNSIGNED DEFAULT NULL,
  `creator_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state_id` int DEFAULT '0',
  `city_id` int DEFAULT '0',
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cost` decimal(10,2) NOT NULL,
  `coordinates` text COLLATE utf8mb4_unicode_ci,
  `duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_types`
--

CREATE TABLE `shipping_types` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint UNSIGNED DEFAULT NULL,
  `shipping_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shops`
--

CREATE TABLE `shops` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def.png',
  `image_storage_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `bottom_banner` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bottom_banner_storage_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `offer_banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offer_banner_storage_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'public',
  `vacation_start_date` date DEFAULT NULL,
  `vacation_end_date` date DEFAULT NULL,
  `vacation_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vacation_status` tinyint NOT NULL DEFAULT '0',
  `temporary_close` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `banner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `banner_storage_type` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'public'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shop_followers`
--

CREATE TABLE `shop_followers` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int NOT NULL,
  `user_id` int NOT NULL COMMENT 'Customer ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sla_breaches`
--

CREATE TABLE `sla_breaches` (
  `id` bigint UNSIGNED NOT NULL,
  `entity_type` enum('inbox_message','lead','retail_deal','wholesale_deal','warranty_claim','complaint_ticket','service_ticket','career_ticket','support_ticket','retail_ticket','wholesale_ticket') COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` bigint UNSIGNED NOT NULL,
  `breach_type` enum('response','resolution') COLLATE utf8mb4_unicode_ci NOT NULL,
  `occurred_at` timestamp NOT NULL,
  `notified` tinyint(1) NOT NULL DEFAULT '0',
  `escalation_level` enum('none','l1','l2') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sla_policies`
--

CREATE TABLE `sla_policies` (
  `id` bigint UNSIGNED NOT NULL,
  `entity_type` enum('inbox_message','lead','retail_deal','wholesale_deal','warranty_claim','complaint_ticket','service_ticket','career_ticket','support_ticket','retail_ticket','wholesale_ticket') COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_time_minutes` int NOT NULL,
  `resolution_time_minutes` int NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sla_policies`
--

INSERT INTO `sla_policies` (`id`, `entity_type`, `priority`, `response_time_minutes`, `resolution_time_minutes`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'inbox_message', 'medium', 1, 60, 1, '2025-11-12 02:34:23', '2025-11-12 03:15:26'),
(5, 'warranty_claim', 'medium', 10, 100, 1, '2025-11-12 06:56:44', '2025-11-12 06:56:44'),
(6, 'inbox_message', 'high', 30, 30, 1, '2026-02-06 12:32:06', '2026-02-06 12:32:06');

-- --------------------------------------------------------

--
-- Table structure for table `social_medias`
--

CREATE TABLE `social_medias` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active_status` int NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `soft_credentials`
--

CREATE TABLE `soft_credentials` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_clearance_products`
--

CREATE TABLE `stock_clearance_products` (
  `id` bigint UNSIGNED NOT NULL,
  `added_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int DEFAULT NULL,
  `setup_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `shop_id` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'percentage',
  `discount_amount` decimal(18,12) NOT NULL DEFAULT '0.000000000000',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_clearance_setups`
--

CREATE TABLE `stock_clearance_setups` (
  `id` bigint UNSIGNED NOT NULL,
  `setup_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `shop_id` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'percentage',
  `discount_amount` decimal(18,12) NOT NULL DEFAULT '0.000000000000',
  `offer_active_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `offer_active_range_start` time DEFAULT NULL,
  `offer_active_range_end` time DEFAULT NULL,
  `show_in_homepage` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_homepage_once` tinyint(1) NOT NULL DEFAULT '0',
  `show_in_shop` tinyint(1) NOT NULL DEFAULT '1',
  `duration_start_date` timestamp NULL DEFAULT NULL,
  `duration_end_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_received`
--

CREATE TABLE `stock_received` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity_received` int NOT NULL,
  `received_date` date NOT NULL,
  `status` enum('approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_requests`
--

CREATE TABLE `stock_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `from_branch_id` bigint UNSIGNED NOT NULL,
  `to_branch_id` bigint UNSIGNED NOT NULL,
  `transfer_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_request_products`
--

CREATE TABLE `stock_request_products` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_requests_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `variation_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variation_key` text COLLATE utf8mb4_unicode_ci,
  `attributes` text COLLATE utf8mb4_unicode_ci,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL,
  `received_from_branch` bigint UNSIGNED DEFAULT NULL,
  `received_time` datetime DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` bigint UNSIGNED NOT NULL,
  `from_branch_id` bigint UNSIGNED DEFAULT '0',
  `to_branch_id` bigint UNSIGNED NOT NULL,
  `transfer_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_products`
--

CREATE TABLE `stock_transfer_products` (
  `id` bigint UNSIGNED NOT NULL,
  `stock_transfers_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `attribute_id` bigint UNSIGNED DEFAULT NULL,
  `variation_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variation_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variation_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `attributes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int NOT NULL,
  `serial_csv_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Transferred',
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storages`
--

CREATE TABLE `storages` (
  `id` bigint UNSIGNED NOT NULL,
  `data_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `request_type` int DEFAULT '1',
  `service_id` bigint UNSIGNED DEFAULT NULL,
  `customer_id` bigint DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `source_id` int NOT NULL,
  `owner_id` bigint UNSIGNED DEFAULT NULL,
  `department_id` int DEFAULT '0',
  `employee_id` int DEFAULT '0',
  `subject` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sub_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `priority` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'low',
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attachment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `reply` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(15) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open',
  `escalation_level` enum('none','l1','l2') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalated_by` int DEFAULT NULL,
  `reopen_count` int NOT NULL DEFAULT '0',
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `response_due` timestamp NULL DEFAULT NULL,
  `resolution_due` timestamp NULL DEFAULT NULL,
  `first_response_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sla_hours` int DEFAULT NULL,
  `sla_paused_at` timestamp NULL DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets_notification`
--

CREATE TABLE `support_tickets_notification` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` int NOT NULL DEFAULT '0',
  `notification_for` int DEFAULT '1',
  `user_id` bigint UNSIGNED NOT NULL,
  `customer_id` int DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` bigint UNSIGNED NOT NULL DEFAULT '0',
  `is_active` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_activities`
--

CREATE TABLE `support_ticket_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `support_ticket_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `noted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_convs`
--

CREATE TABLE `support_ticket_convs` (
  `id` bigint UNSIGNED NOT NULL,
  `support_ticket_id` bigint DEFAULT NULL,
  `admin_id` bigint DEFAULT NULL,
  `customer_message` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `attachment` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `admin_message` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_department_employee`
--

CREATE TABLE `support_ticket_department_employee` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `department_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `employee_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `status_id` int DEFAULT '0',
  `status_type_id` int DEFAULT '0',
  `created_by` int DEFAULT '0',
  `status` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_status_master`
--

CREATE TABLE `support_ticket_status_master` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `master_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `position` int NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_ticket_status_master`
--

INSERT INTO `support_ticket_status_master` (`id`, `name`, `master_id`, `position`, `status`, `created_at`, `updated_at`) VALUES
(1, 'New', 1, 1, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(2, 'Open', 1, 2, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(3, 'Assigned', 1, 3, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(4, 'Triage', 1, 4, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(5, 'InProgress', 1, 5, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(10, 'Resolved', 1, 10, 'inactive', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(19, 'Closed', 1, 19, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(20, 'New', 2, 1, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(21, 'Open', 2, 2, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(22, 'Assigned', 2, 3, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(23, 'Scheduled', 2, 4, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(24, 'InProgress', 2, 5, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(25, 'Completed', 2, 6, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(26, 'Closed', 2, 7, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(27, 'New', 3, 1, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(28, 'Open', 3, 2, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(29, 'Assigned', 3, 3, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(30, 'Screening', 3, 4, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(31, 'Interview', 3, 5, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(32, 'Offer', 3, 6, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(33, 'Hired', 3, 7, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(34, 'Rejected', 3, 8, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(35, 'Closed', 3, 9, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(36, 'New', 4, 1, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(37, 'Open', 4, 2, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(38, 'Assigned', 4, 3, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(39, 'In Progress', 4, 4, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(40, 'Waiting', 4, 5, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(41, 'Resolved', 4, 6, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(42, 'Closed', 4, 7, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(43, 'New', 5, 1, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(44, 'Open', 5, 2, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(45, 'Assigned', 5, 3, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(46, 'In Progress', 5, 4, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(47, 'Resolved', 5, 5, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(48, 'Closed', 5, 6, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(49, 'Cancelled', 5, 7, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(50, 'Return Requested', 5, 8, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(51, 'RMA Issued', 5, 9, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(52, 'RMA Received', 5, 10, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(53, 'Refund Approved', 5, 11, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(54, 'Refund Rejected', 5, 12, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(55, 'Refund Posted', 5, 13, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(56, 'New', 6, 1, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(57, 'Open', 6, 2, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(58, 'Assigned', 6, 3, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(59, 'In Progress', 6, 4, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(60, 'Resolved', 6, 5, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(61, 'Closed', 6, 6, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30'),
(62, 'Cancelled', 6, 7, 'active', '2025-10-03 10:20:30', '2025-10-03 10:20:30');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint UNSIGNED NOT NULL,
  `tag` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visit_count` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int UNSIGNED NOT NULL,
  `order_id` bigint DEFAULT NULL,
  `payment_for` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_id` bigint DEFAULT NULL,
  `payment_receiver_id` bigint DEFAULT NULL,
  `paid_by` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_to` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `amount` double(8,2) NOT NULL DEFAULT '0.00',
  `transaction_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_details_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `translationable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translationable_id` bigint UNSIGNED NOT NULL,
  `locale` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_index` bigint DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `f_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `l_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def.png',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` int NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `street_address` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `house_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apartment_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cm_firebase_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `payment_card_last_four` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_card_brand` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_card_fawry_token` text COLLATE utf8mb4_unicode_ci,
  `login_medium` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_phone_verified` tinyint(1) NOT NULL DEFAULT '0',
  `temporary_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `wallet_balance` double(8,2) DEFAULT NULL,
  `loyalty_point` double(8,2) DEFAULT NULL,
  `login_hit_count` tinyint NOT NULL DEFAULT '0',
  `is_temp_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `temp_block_time` timestamp NULL DEFAULT NULL,
  `referral_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referred_by` int DEFAULT NULL,
  `app_language` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `wholesaler_status` tinyint(1) NOT NULL DEFAULT '0',
  `wholesaler_discount` double(8,2) NOT NULL DEFAULT '0.00',
  `tier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moq_override_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `wholesale_category` int DEFAULT '0',
  `wholesale_category_discount` double(15,2) DEFAULT '0.00',
  `wholesale_manager_id` int DEFAULT '0',
  `wholesale_special_discount` double(15,2) DEFAULT '0.00',
  `crm_access` tinyint(1) NOT NULL DEFAULT '0',
  `last_online_at` timestamp NULL DEFAULT NULL,
  `current_crm_team_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `f_name`, `l_name`, `phone`, `image`, `email`, `user_type`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `street_address`, `country`, `city`, `zip`, `house_no`, `apartment_no`, `cm_firebase_token`, `is_active`, `payment_card_last_four`, `payment_card_brand`, `payment_card_fawry_token`, `login_medium`, `social_id`, `is_phone_verified`, `temporary_token`, `is_email_verified`, `wallet_balance`, `loyalty_point`, `login_hit_count`, `is_temp_blocked`, `temp_block_time`, `referral_code`, `referred_by`, `app_language`, `wholesaler_status`, `wholesaler_discount`, `tier`, `moq_override_enabled`, `wholesale_category`, `wholesale_category_discount`, `wholesale_manager_id`, `wholesale_special_discount`, `crm_access`, `last_online_at`, `current_crm_team_id`) VALUES
(0, 'walking customer', 'walking', 'customer', '+20000000000000', 'def.png', 'walking@customer.com', 0, NULL, '$2y$10$Tljr3wR0Yc30v1f468B91OGTvlpsWZZx0UlQQwzotn9anzsjxdUC2', NULL, NULL, '2026-02-24 07:53:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 86020.09, NULL, 0, 0, NULL, NULL, NULL, 'en', 0, 0.00, NULL, 0, 0, 0.00, 0, 0.00, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_makes`
--

CREATE TABLE `vehicle_makes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_models`
--

CREATE TABLE `vehicle_models` (
  `id` bigint UNSIGNED NOT NULL,
  `make_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` year DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_years`
--

CREATE TABLE `vehicle_years` (
  `id` bigint UNSIGNED NOT NULL,
  `year` year NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_registration_reasons`
--

CREATE TABLE `vendor_registration_reasons` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` tinyint NOT NULL DEFAULT '1',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `view_tokens`
--

CREATE TABLE `view_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `jti` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warranty_public_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `issued_at` datetime NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `transaction_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit` decimal(24,3) NOT NULL DEFAULT '0.000',
  `debit` decimal(24,3) NOT NULL DEFAULT '0.000',
  `admin_bonus` decimal(24,3) NOT NULL DEFAULT '0.000',
  `balance` decimal(24,3) NOT NULL DEFAULT '0.000',
  `transaction_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warranties`
--

CREATE TABLE `warranties` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_public_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `policy_version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consent_checked` tinyint(1) NOT NULL DEFAULT '0',
  `consent_timestamp` datetime DEFAULT NULL,
  `consent_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warranty_months` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `product_stock_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('preactivated','active','cancelled','replaced','expired','pending_review') COLLATE utf8mb4_unicode_ci DEFAULT 'preactivated',
  `activation_date` timestamp NULL DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `final_user_id` bigint UNSIGNED DEFAULT NULL,
  `distributor_id` bigint UNSIGNED DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `activated_by_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_by_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_by_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activation_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_admin_manual_activation` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin_override` tinyint(1) NOT NULL DEFAULT '0',
  `original_warranty_id` bigint UNSIGNED DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `retailer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `retailer_branch_id` bigint UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `warranties`
--
DELIMITER $$
CREATE TRIGGER `prevent_duplicate_active_warranties_insert` BEFORE INSERT ON `warranties` FOR EACH ROW BEGIN
                IF NEW.status = 'active' AND
                   EXISTS (
                       SELECT 1 FROM warranties
                       WHERE serial_number = NEW.serial_number
                       AND status = 'active'
                       AND end_date >= CURDATE()
                       AND id != COALESCE(NEW.id, 0)
                   ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'An active warranty already exists for this serial number';
                END IF;
            END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_duplicate_active_warranties_update` BEFORE UPDATE ON `warranties` FOR EACH ROW BEGIN
                IF NEW.status = 'active' AND OLD.status != 'active' AND
                   EXISTS (
                       SELECT 1 FROM warranties
                       WHERE serial_number = NEW.serial_number
                       AND status = 'active'
                       AND end_date >= CURDATE()
                       AND id != NEW.id
                   ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot activate warranty - an active warranty already exists for this serial number';
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `warranty_claims`
--

CREATE TABLE `warranty_claims` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_id` bigint UNSIGNED NOT NULL,
  `serial_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `claim_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('new','triage_pending','approved','rma_issued','received','diagnosis_pending','repair_pending','replacement_pending','shipped_ready','resolved','closed','rejected','waiting_customer','waiting_parts','waiting_payment','qc_pending','dispatched') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'new',
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `response_due` timestamp NULL DEFAULT NULL,
  `resolution_due` timestamp NULL DEFAULT NULL,
  `rma_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rma_deadline` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `technician_id` bigint UNSIGNED DEFAULT NULL,
  `diagnosis_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `repair_or_replace` enum('repair','replace') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamper_detected` tinyint(1) NOT NULL DEFAULT '0',
  `replacement_mode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `qc_passed_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `is_admin_override` tinyint(1) NOT NULL DEFAULT '0',
  `override_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `override_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `reopen_count` int NOT NULL DEFAULT '0',
  `inspection_fee_due` tinyint(1) NOT NULL DEFAULT '0',
  `is_fee_waived` tinyint(1) NOT NULL DEFAULT '0',
  `inspection_fee_amount` double(8,2) DEFAULT NULL,
  `checklists` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `first_response_at` timestamp NULL DEFAULT NULL,
  `escalation_level` int DEFAULT NULL,
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalated_by` bigint DEFAULT NULL,
  `sla_paused_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `dispatch_due` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `warranty_claims`
--
DELIMITER $$
CREATE TRIGGER `prevent_duplicate_open_claims` BEFORE INSERT ON `warranty_claims` FOR EACH ROW BEGIN
                IF NEW.status NOT IN ('resolved', 'closed', 'rejected') AND
                   EXISTS (
                       SELECT 1 FROM warranty_claims
                       WHERE warranty_id = NEW.warranty_id
                       AND status NOT IN ('resolved', 'closed', 'rejected')
                       AND id != COALESCE(NEW.id, 0)
                   ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot create duplicate open claim for warranty';
                END IF;
            END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_duplicate_open_claims_update` BEFORE UPDATE ON `warranty_claims` FOR EACH ROW BEGIN
                IF NEW.status NOT IN ('resolved', 'closed', 'rejected') AND
                   OLD.status IN ('resolved', 'closed', 'rejected') AND
                   EXISTS (
                       SELECT 1 FROM warranty_claims
                       WHERE warranty_id = NEW.warranty_id
                       AND status NOT IN ('resolved', 'closed', 'rejected')
                       AND id != NEW.id
                   ) THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Cannot reopen claim with existing open claim';
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `warranty_claim_attachments`
--

CREATE TABLE `warranty_claim_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_claim_id` bigint UNSIGNED NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'document',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warranty_claim_charges`
--

CREATE TABLE `warranty_claim_charges` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_claim_id` bigint UNSIGNED NOT NULL,
  `charge_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warranty_claim_payments`
--

CREATE TABLE `warranty_claim_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_claim_id` bigint UNSIGNED NOT NULL,
  `payment_channel` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `charge_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `payment_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payment_link_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_link_expires_at` timestamp NULL DEFAULT NULL,
  `gateway_payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `paid_by_user_id` bigint UNSIGNED DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warranty_distribution_histories`
--

CREATE TABLE `warranty_distribution_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_id` bigint UNSIGNED NOT NULL,
  `from_distributor_id` bigint UNSIGNED DEFAULT NULL,
  `to_distributor_id` bigint UNSIGNED DEFAULT NULL,
  `from_branch_id` bigint UNSIGNED DEFAULT NULL,
  `to_branch_id` bigint UNSIGNED DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warranty_replacements`
--

CREATE TABLE `warranty_replacements` (
  `id` bigint UNSIGNED NOT NULL,
  `original_warranty_id` bigint UNSIGNED NOT NULL,
  `new_warranty_id` bigint UNSIGNED NOT NULL,
  `replaced_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `technician_id` bigint UNSIGNED DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warranty_timeline_events`
--

CREATE TABLE `warranty_timeline_events` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_id` bigint UNSIGNED DEFAULT NULL,
  `warranty_claim_id` bigint UNSIGNED DEFAULT NULL,
  `event_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesaler_businesses`
--

CREATE TABLE `wholesaler_businesses` (
  `id` bigint UNSIGNED NOT NULL,
  `wholesaler_id` bigint UNSIGNED NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trade_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_expiry_date` timestamp NULL DEFAULT NULL,
  `register_copy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tax_expiry_date` timestamp NULL DEFAULT NULL,
  `tax_card_copy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vat_expiry_date` timestamp NULL DEFAULT NULL,
  `vat_register_copy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesaler_logs`
--

CREATE TABLE `wholesaler_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `wholesaler_id` bigint UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `performed_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesaler_product_requests`
--

CREATE TABLE `wholesaler_product_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `account_manager_id` int NOT NULL DEFAULT '0',
  `wholesaler_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `requested_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_qty` int NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `expected_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesaler_registration_reasons`
--

CREATE TABLE `wholesaler_registration_reasons` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesaler_summary`
--

CREATE TABLE `wholesaler_summary` (
  `id` bigint UNSIGNED NOT NULL,
  `wholesaler_id` bigint UNSIGNED NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trade_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_card_copy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_register_copy` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subcategory_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_qty` int DEFAULT NULL,
  `max_qty` int DEFAULT NULL,
  `price_per_piece` decimal(8,2) DEFAULT NULL,
  `minimum_order_amount` decimal(8,2) DEFAULT NULL,
  `wholesaler_status` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_categories`
--

CREATE TABLE `wholesale_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_amount` double(15,2) NOT NULL DEFAULT '0.00',
  `max_amount` double(15,2) NOT NULL DEFAULT '0.00',
  `discount` double(15,1) NOT NULL DEFAULT '0.0',
  `status` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_confirmorder_item`
--

CREATE TABLE `wholesale_confirmorder_item` (
  `id` bigint UNSIGNED NOT NULL,
  `confirmed_order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_variation_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_quantity` int NOT NULL,
  `base_price` decimal(10,0) NOT NULL,
  `tax` decimal(10,0) DEFAULT '0',
  `final_price` decimal(10,0) NOT NULL,
  `quantity_sent` int NOT NULL DEFAULT '0',
  `remaining` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_confirm_orders`
--

CREATE TABLE `wholesale_confirm_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_order_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_po_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotation_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirm_order_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wholesaler_id` bigint UNSIGNED NOT NULL,
  `status` enum('confirmed','rejected','delivered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'confirmed',
  `delivery_status` enum('pending','partials','delivered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','partials','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `final_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `attachments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_contacts`
--

CREATE TABLE `wholesale_contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_number_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_number_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` bigint UNSIGNED DEFAULT NULL,
  `preferred_contact_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_contacted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_order_delivery`
--

CREATE TABLE `wholesale_order_delivery` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirmed_order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_variation_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity_sent` int NOT NULL DEFAULT '0',
  `note` text COLLATE utf8mb4_unicode_ci,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `delivery_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `serial_csv_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_order_payments`
--

CREATE TABLE `wholesale_order_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wholesale_confirm_order_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `remaining_amount` decimal(10,2) NOT NULL,
  `payment_through` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_price_ranges`
--

CREATE TABLE `wholesale_price_ranges` (
  `id` bigint UNSIGNED NOT NULL,
  `wholesale_id` int NOT NULL,
  `tier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_qty` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_qty` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_per_piece` decimal(10,2) NOT NULL,
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_price_tiers`
--

CREATE TABLE `wholesale_price_tiers` (
  `id` bigint UNSIGNED NOT NULL,
  `subcategory_id` bigint UNSIGNED NOT NULL,
  `tier` enum('bronze','silver','platinum') COLLATE utf8mb4_unicode_ci NOT NULL,
  `moq` int NOT NULL,
  `discount_percent` double(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_products`
--

CREATE TABLE `wholesale_products` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `sub_category_id` int NOT NULL,
  `product_id` int NOT NULL,
  `variation_type` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `variation_key` text COLLATE utf8mb4_general_ci,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_purchase_orders`
--

CREATE TABLE `wholesale_purchase_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_order_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wholeseller_id` bigint UNSIGNED NOT NULL,
  `wholeseller_tier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','processed','quotationsend','delivered','partials') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_purchase_order_items`
--

CREATE TABLE `wholesale_purchase_order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `wholesale_order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_variation_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_quantity` int NOT NULL,
  `base_price` decimal(12,2) NOT NULL,
  `tax` decimal(12,2) DEFAULT '0.00',
  `final_price` decimal(12,2) NOT NULL,
  `price_range_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_quotations`
--

CREATE TABLE `wholesale_quotations` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_order_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotation_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wholeseller_id` bigint UNSIGNED NOT NULL,
  `wholeseller_tier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('sent','accepted','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent',
  `final_price` decimal(10,2) NOT NULL,
  `wholesaler_discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `terms_and_conditions` text COLLATE utf8mb4_unicode_ci,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_quotation_items`
--

CREATE TABLE `wholesale_quotation_items` (
  `id` bigint UNSIGNED NOT NULL,
  `wholesale_quotation_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_variation_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_quantity` int NOT NULL,
  `base_price` decimal(12,2) NOT NULL,
  `final_price` decimal(12,2) NOT NULL,
  `price_range_id` bigint UNSIGNED DEFAULT NULL,
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wholesale_tiers`
--

CREATE TABLE `wholesale_tiers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `rank` int UNSIGNED NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint NOT NULL,
  `product_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_methods`
--

CREATE TABLE `withdrawal_methods` (
  `id` bigint UNSIGNED NOT NULL,
  `method_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_fields` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint NOT NULL DEFAULT '0',
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_requests`
--

CREATE TABLE `withdraw_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `seller_id` bigint DEFAULT NULL,
  `delivery_man_id` bigint DEFAULT NULL,
  `admin_id` bigint DEFAULT NULL,
  `amount` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0.00',
  `withdrawal_method_id` bigint UNSIGNED DEFAULT NULL,
  `withdrawal_method_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `transaction_note` text COLLATE utf8mb4_unicode_ci,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `work_orders`
--

CREATE TABLE `work_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `warranty_claim_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checklist_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `diagnosis` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `parts_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `labor_hours` decimal(8,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `xero_tokens`
--

CREATE TABLE `xero_tokens` (
  `id` int UNSIGNED NOT NULL,
  `team_id` bigint UNSIGNED DEFAULT NULL,
  `id_token` text COLLATE utf8mb4_unicode_ci,
  `access_token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_in` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refresh_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auth_event_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tenant_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date_utc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_date_utc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_dealer_sections`
--
ALTER TABLE `about_dealer_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_hero_sections`
--
ALTER TABLE `about_hero_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_mission_sections`
--
ALTER TABLE `about_mission_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_product_sections`
--
ALTER TABLE `about_product_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_timeline_sections`
--
ALTER TABLE `about_timeline_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `about_who_we_are_sections`
--
ALTER TABLE `about_who_we_are_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activation_reviews`
--
ALTER TABLE `activation_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activation_reviews_warranty_id_foreign` (`warranty_id`),
  ADD KEY `idx_activation_reviews_warranty_id` (`warranty_id`),
  ADD KEY `idx_activation_reviews_status` (`status`),
  ADD KEY `idx_activation_reviews_created_at` (`created_at`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `addon_settings`
--
ALTER TABLE `addon_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_settings_id_index` (`id`);

--
-- Indexes for table `add_fund_bonus_categories`
--
ALTER TABLE `add_fund_bonus_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  ADD KEY `admin_notifications_department_id_foreign` (`department_id`),
  ADD KEY `admin_notifications_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_wallets`
--
ALTER TABLE `admin_wallets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_wallet_histories`
--
ALTER TABLE `admin_wallet_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `analytic_scripts`
--
ALTER TABLE `analytic_scripts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `areas`
--
ALTER TABLE `areas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attachments_attachable_type_attachable_id_index` (`attachable_type`,`attachable_id`);

--
-- Indexes for table `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `audits`
--
ALTER TABLE `audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audits_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audits_user_id_user_type_index` (`user_id`,`user_type`);

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billing_addresses`
--
ALTER TABLE `billing_addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blacklists`
--
ALTER TABLE `blacklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blacklists_serial_number_unique` (`serial_number`),
  ADD KEY `blacklists_user_id_foreign` (`user_id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_categories_name_unique` (`name`);

--
-- Indexes for table `blog_seos`
--
ALTER TABLE `blog_seos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_translations`
--
ALTER TABLE `blog_translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_translations_translation_id_index` (`translation_id`),
  ADD KEY `blog_translations_locale_index` (`locale`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_manager` (`manager_id`),
  ADD KEY `branches_status_country_index` (`status`,`branch_country`),
  ADD KEY `branches_manager_status_index` (`manager_id`,`status`),
  ADD KEY `branches_vendor_status_index` (`vendor_id`,`status`),
  ADD KEY `branches_status_location_index` (`status`,`branch_state`);

--
-- Indexes for table `branch_delivery_restrictions`
--
ALTER TABLE `branch_delivery_restrictions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_delivery_restriction_unique` (`branch_id`,`delivery_area_id`),
  ADD KEY `branch_delivery_restrictions_delivery_area_id_foreign` (`delivery_area_id`),
  ADD KEY `branch_delivery_restriction_lookup` (`branch_id`,`delivery_area_id`);

--
-- Indexes for table `branch_shipping_method_areas`
--
ALTER TABLE `branch_shipping_method_areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_shipping_area_unique` (`branch_id`,`shipping_method_area_id`),
  ADD KEY `branch_shipping_method_areas_shipping_method_area_id_foreign` (`shipping_method_area_id`),
  ADD KEY `branch_shipping_area_lookup` (`branch_id`,`shipping_method_area_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_brands_status` (`status`);

--
-- Indexes for table `business_pages`
--
ALTER TABLE `business_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `business_settings`
--
ALTER TABLE `business_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calendar_todos`
--
ALTER TABLE `calendar_todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendar_todos_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `career_activities`
--
ALTER TABLE `career_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `career_activities_ticket_id_foreign` (`ticket_id`),
  ADD KEY `career_activities_created_by_foreign` (`created_by`);

--
-- Indexes for table `career_applies`
--
ALTER TABLE `career_applies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `career_applies_job_id_foreign` (`job_id`);

--
-- Indexes for table `career_benefits`
--
ALTER TABLE `career_benefits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `career_cards`
--
ALTER TABLE `career_cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `career_interviews`
--
ALTER TABLE `career_interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `career_interviews_ticket_id_foreign` (`ticket_id`);

--
-- Indexes for table `career_jobs`
--
ALTER TABLE `career_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `career_offers`
--
ALTER TABLE `career_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `career_offers_ticket_id_foreign` (`ticket_id`);

--
-- Indexes for table `career_rejections`
--
ALTER TABLE `career_rejections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `career_rejections_ticket_id_foreign` (`ticket_id`);

--
-- Indexes for table `career_sections`
--
ALTER TABLE `career_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `career_talent_pool`
--
ALTER TABLE `career_talent_pool`
  ADD PRIMARY KEY (`id`),
  ADD KEY `career_talent_pool_ticket_id_foreign` (`ticket_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_shippings`
--
ALTER TABLE `cart_shippings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categories_parent` (`parent_id`),
  ADD KEY `idx_categories_position` (`position`),
  ADD KEY `idx_categories_home_status` (`home_status`),
  ADD KEY `idx_categories_priority` (`priority`),
  ADD KEY `idx_categories_slug` (`slug`);

--
-- Indexes for table `category_shipping_costs`
--
ALTER TABLE `category_shipping_costs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chattings`
--
ALTER TABLE `chattings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cities_state_id_foreign` (`state_id`);

--
-- Indexes for table `cms_pages`
--
ALTER TABLE `cms_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cms_pages_slug_unique` (`slug`);

--
-- Indexes for table `cms_products`
--
ALTER TABLE `cms_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_services`
--
ALTER TABLE `cms_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cms_services_created_at` (`created_at`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_banners`
--
ALTER TABLE `contact_banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crm_calls`
--
ALTER TABLE `crm_calls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `crm_calls_call_id_unique` (`call_id`),
  ADD KEY `crm_calls_customer_id_foreign` (`customer_id`),
  ADD KEY `crm_calls_agent_id_foreign` (`agent_id`),
  ADD KEY `crm_calls_ucm_channel_index` (`ucm_channel`),
  ADD KEY `crm_calls_ucm_uniqueid_index` (`ucm_uniqueid`),
  ADD KEY `crm_calls_ucm_bridge_id_index` (`ucm_bridge_id`),
  ADD KEY `crm_calls_src_number_index` (`src_number`),
  ADD KEY `crm_calls_dst_number_index` (`dst_number`);

--
-- Indexes for table `cron_configuration`
--
ALTER TABLE `cron_configuration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cron_sender_details`
--
ALTER TABLE `cron_sender_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_wallets`
--
ALTER TABLE `customer_wallets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_wallet_histories`
--
ALTER TABLE `customer_wallet_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deals_lead_id_foreign` (`lead_id`),
  ADD KEY `deals_employee_id_foreign` (`employee_id`),
  ADD KEY `idx_deals_stage_created` (`stage`,`created_at`),
  ADD KEY `idx_deals_stage_owner` (`stage`,`owner_id`),
  ADD KEY `idx_deals_status` (`status`),
  ADD KEY `idx_deals_lead` (`lead_id`),
  ADD KEY `idx_deals_priority_status` (`priority`,`status`);

--
-- Indexes for table `deal_activities`
--
ALTER TABLE `deal_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deal_activities_deal_id_foreign` (`deal_id`),
  ADD KEY `deal_activities_employee_id_foreign` (`employee_id`),
  ADD KEY `idx_deal_activities_timeline` (`deal_id`,`created_at`);

--
-- Indexes for table `deal_calls`
--
ALTER TABLE `deal_calls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deal_calls_deal_id_foreign` (`deal_id`),
  ADD KEY `deal_calls_employee_id_foreign` (`employee_id`),
  ADD KEY `deal_calls_department_id_foreign` (`department_id`),
  ADD KEY `idx_deal_calls_timeline` (`deal_id`,`created_at`);

--
-- Indexes for table `deal_files`
--
ALTER TABLE `deal_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deal_files_deal_id_foreign` (`deal_id`),
  ADD KEY `deal_files_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `deal_notes`
--
ALTER TABLE `deal_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deal_notes_deal_id_foreign` (`deal_id`),
  ADD KEY `deal_notes_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `deal_of_the_days`
--
ALTER TABLE `deal_of_the_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deal_day_product_status` (`product_id`,`status`);

--
-- Indexes for table `deal_tasks`
--
ALTER TABLE `deal_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deal_tasks_deal_id_foreign` (`deal_id`),
  ADD KEY `deal_tasks_employee_id_foreign` (`employee_id`),
  ADD KEY `deal_tasks_department_id_foreign` (`department_id`);

--
-- Indexes for table `deliveryman_notifications`
--
ALTER TABLE `deliveryman_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_man_order` (`delivery_man_id`,`order_id`),
  ADD KEY `idx_notifications_order` (`order_id`);

--
-- Indexes for table `deliveryman_wallets`
--
ALTER TABLE `deliveryman_wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wallets_delivery_man` (`delivery_man_id`);

--
-- Indexes for table `delivery_areas`
--
ALTER TABLE `delivery_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_delivery_areas_name` (`area`);

--
-- Indexes for table `delivery_cities`
--
ALTER TABLE `delivery_cities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_country_codes`
--
ALTER TABLE `delivery_country_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_histories`
--
ALTER TABLE `delivery_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_delivery_histories_man_time` (`deliveryman_id`,`time`),
  ADD KEY `idx_delivery_histories_order` (`order_id`),
  ADD KEY `idx_delivery_histories_tracking` (`deliveryman_id`,`time`);

--
-- Indexes for table `delivery_man_transactions`
--
ALTER TABLE `delivery_man_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transactions_man_date` (`delivery_man_id`,`created_at`),
  ADD KEY `idx_transactions_user` (`user_id`,`user_type`),
  ADD KEY `idx_transactions_id` (`transaction_id`),
  ADD KEY `idx_transactions_type` (`transaction_type`);

--
-- Indexes for table `delivery_men`
--
ALTER TABLE `delivery_men`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_delivery_men_available` (`is_active`,`is_online`),
  ADD KEY `idx_delivery_men_seller` (`seller_id`),
  ADD KEY `idx_delivery_men_auth_token` (`auth_token`),
  ADD KEY `idx_delivery_men_fcm_token` (`fcm_token`);

--
-- Indexes for table `delivery_states`
--
ALTER TABLE `delivery_states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_zip_codes`
--
ALTER TABLE `delivery_zip_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_name_unique` (`name`);

--
-- Indexes for table `department_users`
--
ALTER TABLE `department_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_users_email_unique` (`email`),
  ADD KEY `department_users_department_id_foreign` (`department_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `devices_is_trusted_index` (`is_trusted`),
  ADD KEY `devices_is_untrusted_index` (`is_untrusted`);

--
-- Indexes for table `digital_product_authors`
--
ALTER TABLE `digital_product_authors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `digital_product_otp_verifications`
--
ALTER TABLE `digital_product_otp_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_digital_otp_expires` (`expires_at`);

--
-- Indexes for table `digital_product_publishing_houses`
--
ALTER TABLE `digital_product_publishing_houses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `digital_product_variations`
--
ALTER TABLE `digital_product_variations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_digital_variations_product` (`product_id`),
  ADD KEY `idx_digital_variations_key` (`variant_key`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `error_logs`
--
ALTER TABLE `error_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `escalations`
--
ALTER TABLE `escalations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `escalations_escalatable_type_escalatable_id_index` (`escalatable_type`,`escalatable_id`),
  ADD KEY `escalations_escalated_by_foreign` (`escalated_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feature_deals`
--
ALTER TABLE `feature_deals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flash_deals`
--
ALTER TABLE `flash_deals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_flash_deals_dates_status` (`start_date`,`end_date`,`status`),
  ADD KEY `idx_flash_deals_slug` (`slug`);

--
-- Indexes for table `flash_deal_products`
--
ALTER TABLE `flash_deal_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_flash_deal_products_deal_product` (`flash_deal_id`,`product_id`),
  ADD KEY `idx_flash_deal_products_product` (`product_id`);

--
-- Indexes for table `guest_users`
--
ALTER TABLE `guest_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `help_topics`
--
ALTER TABLE `help_topics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_page_sections`
--
ALTER TABLE `home_page_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `home_page_sections_type_unique` (`type`);

--
-- Indexes for table `inbox_activities`
--
ALTER TABLE `inbox_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inbox_activities_massage_id_foreign` (`message_id`),
  ADD KEY `inbox_activities_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `inbox_calls`
--
ALTER TABLE `inbox_calls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inbox_calls_massage_id_foreign` (`message_id`),
  ADD KEY `inbox_calls_employee_id_foreign` (`employee_id`),
  ADD KEY `inbox_calls_department_id_foreign` (`department_id`);

--
-- Indexes for table `inbox_files`
--
ALTER TABLE `inbox_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inbox_files_massage_id_foreign` (`message_id`),
  ADD KEY `inbox_files_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `inbox_messages`
--
ALTER TABLE `inbox_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inbox_messages_department_id_foreign` (`department_id`),
  ADD KEY `inbox_messages_employee_id_foreign` (`employee_id`),
  ADD KEY `Admins` (`owner_id`),
  ADD KEY `idx_inbox_status_owner` (`status`,`owner_id`),
  ADD KEY `idx_inbox_department_status` (`department_id`,`status`),
  ADD KEY `idx_inbox_employee_status` (`employee_id`,`status`),
  ADD KEY `idx_inbox_message_type` (`message_type`),
  ADD KEY `idx_inbox_pipeline` (`pipeline`),
  ADD KEY `idx_inbox_related_lead` (`related_lead_id`),
  ADD KEY `idx_inbox_related_ticket` (`related_ticket_id`),
  ADD KEY `idx_inbox_related_warranty` (`related_warranty_id`);

--
-- Indexes for table `inbox_notes`
--
ALTER TABLE `inbox_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inbox_notes_massage_id_foreign` (`message_id`),
  ADD KEY `inbox_notes_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `inbox_suggestions`
--
ALTER TABLE `inbox_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inbox_suggestions_inbox_message_id_index` (`inbox_message_id`),
  ADD KEY `inbox_suggestions_user_id_index` (`user_id`);

--
-- Indexes for table `inbox_tasks`
--
ALTER TABLE `inbox_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inbox_tasks_massage_id_foreign` (`message_id`),
  ADD KEY `inbox_tasks_employee_id_foreign` (`employee_id`),
  ADD KEY `inbox_tasks_department_id_foreign` (`department_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leads_status_owner` (`status`,`owner_id`),
  ADD KEY `idx_leads_status_employee` (`status`,`employee_id`),
  ADD KEY `idx_leads_department_status` (`department_id`,`status`),
  ADD KEY `idx_leads_company` (`company_id`),
  ADD KEY `idx_leads_contact` (`contact_id`),
  ADD KEY `idx_leads_utm_source` (`utm_source`),
  ADD KEY `idx_leads_utm_campaign` (`utm_campaign`);

--
-- Indexes for table `lead_activity`
--
ALTER TABLE `lead_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_activity_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_activity_employee_id_foreign` (`employee_id`),
  ADD KEY `idx_lead_activity_timeline` (`lead_id`,`created_at`);

--
-- Indexes for table `lead_call`
--
ALTER TABLE `lead_call`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_call_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_call_employee_id_foreign` (`employee_id`),
  ADD KEY `lead_call_department_id_foreign` (`department_id`),
  ADD KEY `idx_lead_calls_timeline` (`lead_id`,`created_at`);

--
-- Indexes for table `lead_file`
--
ALTER TABLE `lead_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_file_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_file_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `lead_note`
--
ALTER TABLE `lead_note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_note_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_note_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `lead_notifications`
--
ALTER TABLE `lead_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_notifications_user_id_foreign` (`user_id`),
  ADD KEY `lead_notifications_from_user_id_foreign` (`from_user_id`);

--
-- Indexes for table `lead_task`
--
ALTER TABLE `lead_task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_task_lead_id_foreign` (`lead_id`),
  ADD KEY `lead_task_employee_id_foreign` (`employee_id`),
  ADD KEY `lead_task_department_id_foreign` (`department_id`);

--
-- Indexes for table `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `logins_device_id_index` (`device_id`);

--
-- Indexes for table `login_setups`
--
ALTER TABLE `login_setups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_point_transactions`
--
ALTER TABLE `loyalty_point_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `manage_branch_product_stock`
--
ALTER TABLE `manage_branch_product_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_branch_product_variation` (`branch_id`,`product_id`,`variation_key`),
  ADD UNIQUE KEY `mbps_branch_product_variation_unique` (`branch_id`,`product_id`,`variation_type`,`variation_key`),
  ADD KEY `manage_branch_product_stock_branch_id_foreign` (`branch_id`),
  ADD KEY `manage_branch_product_stock_product_id_foreign` (`product_id`),
  ADD KEY `idx_branch_product_variation` (`branch_id`,`product_id`,`variation_key`),
  ADD KEY `fk_branch_stock_attribute` (`attribute_id`);

--
-- Indexes for table `manage_extra_charges`
--
ALTER TABLE `manage_extra_charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manage_extra_charges_category_id_foreign` (`category_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `most_demandeds`
--
ALTER TABLE `most_demandeds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_most_demanded_product_status` (`product_id`,`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_messages`
--
ALTER TABLE `notification_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_seens`
--
ALTER TABLE `notification_seens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_personal_access_clients_client_id_index` (`client_id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `offline_payments`
--
ALTER TABLE `offline_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offline_payment_methods`
--
ALTER TABLE `offline_payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_delivery_verifications`
--
ALTER TABLE `order_delivery_verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_expected_delivery_histories`
--
ALTER TABLE `order_expected_delivery_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_status_histories`
--
ALTER TABLE `order_status_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_transactions`
--
ALTER TABLE `order_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_resets_email_index` (`identity`);

--
-- Indexes for table `payment_terms`
--
ALTER TABLE `payment_terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paytabs_invoices`
--
ALTER TABLE `paytabs_invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `phone_or_email_verifications`
--
ALTER TABLE `phone_or_email_verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `policies_created_by_foreign` (`created_by`);

--
-- Indexes for table `pos_cart_states`
--
ALTER TABLE `pos_cart_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pos_cart_states_cart_id_unique` (`cart_id`),
  ADD KEY `pos_cart_states_actor_type_index` (`actor_type`),
  ADD KEY `pos_cart_states_actor_id_index` (`actor_id`),
  ADD KEY `pos_cart_states_branch_id_index` (`branch_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category_status` (`category_id`,`status`),
  ADD KEY `idx_products_brand_status` (`brand_id`,`status`),
  ADD KEY `idx_products_status_date` (`status`,`created_at`),
  ADD KEY `idx_products_shop` (`shop_id`),
  ADD KEY `idx_products_subcategory_status` (`sub_category_id`,`status`),
  ADD KEY `idx_products_sub_subcategory_status` (`sub_sub_category_id`,`status`),
  ADD KEY `idx_products_slug` (`slug`),
  ADD KEY `idx_products_user` (`user_id`),
  ADD KEY `idx_products_featured` (`featured_status`,`status`),
  ADD KEY `idx_products_branch_status` (`branch_id`,`status`),
  ADD KEY `idx_products_published` (`published`,`status`),
  ADD KEY `idx_products_type` (`product_type`),
  ADD KEY `idx_products_code` (`code`),
  ADD KEY `idx_products_showcase` (`showcase_product`),
  ADD KEY `idx_products_request_status` (`request_status`),
  ADD KEY `idx_products_branch_id` (`branch_id`),
  ADD KEY `idx_products_status` (`status`);

--
-- Indexes for table `product_compares`
--
ALTER TABLE `product_compares`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compares_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_compares_product` (`product_id`);

--
-- Indexes for table `product_seos`
--
ALTER TABLE `product_seos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_seos_product` (`product_id`);

--
-- Indexes for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_stocks_product` (`product_id`),
  ADD KEY `idx_product_stocks_sku` (`sku`),
  ADD KEY `idx_product_stocks_product_qty` (`product_id`,`qty`);

--
-- Indexes for table `product_stock_transactions`
--
ALTER TABLE `product_stock_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_stock_transactions_product_stock_id_foreign` (`product_stock_id`),
  ADD KEY `product_stock_transactions_from_branch_id_foreign` (`from_branch_id`),
  ADD KEY `product_stock_transactions_to_branch_id_foreign` (`to_branch_id`);

--
-- Indexes for table `product_tag`
--
ALTER TABLE `product_tag`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_tag_tag` (`tag_id`),
  ADD KEY `idx_product_tag_product` (`product_id`);

--
-- Indexes for table `publishing_houses`
--
ALTER TABLE `publishing_houses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quotation_metas`
--
ALTER TABLE `quotation_metas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_metas_wholesale_quotation_id_foreign` (`wholesale_quotation_id`);

--
-- Indexes for table `refund_requests`
--
ALTER TABLE `refund_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `refund_requests_order_details_id_unique` (`order_details_id`);

--
-- Indexes for table `refund_statuses`
--
ALTER TABLE `refund_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `refund_transactions`
--
ALTER TABLE `refund_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `refund_transactions_refund_id_unique` (`refund_id`);

--
-- Indexes for table `restock_products`
--
ALTER TABLE `restock_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_restock_products_product` (`product_id`);

--
-- Indexes for table `restock_product_customers`
--
ALTER TABLE `restock_product_customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_restock_customers_restock` (`restock_product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reviews_product_status` (`product_id`,`status`),
  ADD KEY `idx_reviews_customer_product` (`customer_id`,`product_id`),
  ADD KEY `idx_reviews_rating` (`rating`),
  ADD KEY `idx_reviews_status` (`status`),
  ADD KEY `idx_reviews_product` (`product_id`);

--
-- Indexes for table `review_replies`
--
ALTER TABLE `review_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_review_replies_review` (`review_id`);

--
-- Indexes for table `robots_meta_contents`
--
ALTER TABLE `robots_meta_contents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `search_functions`
--
ALTER TABLE `search_functions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sellers_email_unique` (`email`);

--
-- Indexes for table `seller_wallets`
--
ALTER TABLE `seller_wallets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seller_wallet_histories`
--
ALTER TABLE `seller_wallet_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `serial_transfer_histories`
--
ALTER TABLE `serial_transfer_histories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_serial_stock` (`serial_number`,`stock_transfer_id`),
  ADD UNIQUE KEY `uniq_serial_delivery` (`serial_number`,`wholesale_delivery_id`),
  ADD KEY `serial_transfer_histories_stock_transfer_id_foreign` (`stock_transfer_id`),
  ADD KEY `serial_transfer_histories_wholesale_delivery_id_foreign` (`wholesale_delivery_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_service_id_unique` (`service_id`),
  ADD KEY `services_product_id_foreign` (`product_id`),
  ADD KEY `idx_services_service_id` (`service_id`),
  ADD KEY `idx_services_product_id` (`product_id`),
  ADD KEY `idx_services_title` (`title`),
  ADD KEY `idx_services_call_center_flag` (`call_center_flag`),
  ADD KEY `idx_services_created_at` (`created_at`);

--
-- Indexes for table `service_cancellations`
--
ALTER TABLE `service_cancellations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `service_change_orders`
--
ALTER TABLE `service_change_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `service_estimates`
--
ALTER TABLE `service_estimates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `service_invoices`
--
ALTER TABLE `service_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_invoices_ticket_id_foreign` (`ticket_id`),
  ADD KEY `service_invoices_job_id_foreign` (`job_id`),
  ADD KEY `service_invoices_status_expires_at_idx` (`payment_status`,`payment_link_expires_at`);

--
-- Indexes for table `service_jobs`
--
ALTER TABLE `service_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_jobs_ticket_id_foreign` (`ticket_id`),
  ADD KEY `service_jobs_technician_id_foreign` (`technician_id`);

--
-- Indexes for table `service_job_activities`
--
ALTER TABLE `service_job_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_job_activities_job_id_foreign` (`job_id`),
  ADD KEY `service_job_activities_created_by_foreign` (`created_by`);

--
-- Indexes for table `service_job_items`
--
ALTER TABLE `service_job_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_job_items_job_id_foreign` (`job_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_requests_service_id_foreign` (`service_id`),
  ADD KEY `service_requests_customer_id_foreign` (`customer_id`),
  ADD KEY `idx_service_requests_service_id` (`service_id`),
  ADD KEY `idx_service_requests_customer_id` (`customer_id`),
  ADD KEY `idx_service_requests_deleted_at` (`deleted_at`),
  ADD KEY `idx_service_requests_created_at` (`created_at`),
  ADD KEY `idx_service_requests_state` (`state`),
  ADD KEY `idx_service_requests_city` (`city`),
  ADD KEY `idx_service_requests_area` (`area`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shipping_methods_status` (`status`,`creator_type`),
  ADD KEY `idx_shipping_methods_creator` (`creator_id`,`creator_type`);

--
-- Indexes for table `shipping_method_areas`
--
ALTER TABLE `shipping_method_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipping_method_areas_creator_id_index` (`creator_id`);

--
-- Indexes for table `shipping_types`
--
ALTER TABLE `shipping_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shop_followers`
--
ALTER TABLE `shop_followers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sla_breaches`
--
ALTER TABLE `sla_breaches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sla_policies`
--
ALTER TABLE `sla_policies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_medias`
--
ALTER TABLE `social_medias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `soft_credentials`
--
ALTER TABLE `soft_credentials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `states_name_unique` (`name`);

--
-- Indexes for table `stock_clearance_products`
--
ALTER TABLE `stock_clearance_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_clearance_setups`
--
ALTER TABLE `stock_clearance_setups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_received`
--
ALTER TABLE `stock_received`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_received_branch_id_foreign` (`branch_id`),
  ADD KEY `stock_received_product_id_foreign` (`product_id`);

--
-- Indexes for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_requests_from_branch_id_foreign` (`from_branch_id`);

--
-- Indexes for table `stock_request_products`
--
ALTER TABLE `stock_request_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_request_products_stock_requests_id_foreign` (`stock_requests_id`),
  ADD KEY `stock_request_products_category_id_foreign` (`category_id`),
  ADD KEY `stock_request_products_product_id_foreign` (`product_id`),
  ADD KEY `stock_request_products_received_from_branch_foreign` (`received_from_branch`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_transfers_to_branch_id_foreign` (`to_branch_id`);

--
-- Indexes for table `stock_transfer_products`
--
ALTER TABLE `stock_transfer_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_transfer_products_stock_transfers_id_foreign` (`stock_transfers_id`),
  ADD KEY `stock_transfer_products_category_id_foreign` (`category_id`),
  ADD KEY `stock_transfer_products_product_id_foreign` (`product_id`),
  ADD KEY `idx_product_variation` (`product_id`,`variation_key`),
  ADD KEY `fk_stock_transfer_product_attribute` (`attribute_id`);

--
-- Indexes for table `storages`
--
ALTER TABLE `storages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `storages_data_id_index` (`data_id`),
  ADD KEY `storages_value_index` (`value`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_tickets_company_id_foreign` (`company_id`),
  ADD KEY `support_tickets_owner_id_foreign` (`owner_id`),
  ADD KEY `support_tickets_service_id_idx` (`service_id`),
  ADD KEY `idx_support_tickets_status_department` (`status`,`department_id`),
  ADD KEY `idx_support_tickets_status_employee` (`status`,`employee_id`),
  ADD KEY `idx_support_tickets_priority_created` (`priority`,`created_at`),
  ADD KEY `idx_support_tickets_customer_id` (`customer_id`),
  ADD KEY `idx_support_tickets_department_id` (`department_id`),
  ADD KEY `idx_support_tickets_employee_id` (`employee_id`),
  ADD KEY `idx_support_tickets_status` (`status`),
  ADD KEY `idx_support_tickets_priority` (`priority`),
  ADD KEY `idx_support_tickets_request_type` (`request_type`),
  ADD KEY `idx_support_tickets_created_at` (`created_at`),
  ADD KEY `idx_support_tickets_updated_at` (`updated_at`),
  ADD KEY `idx_support_tickets_follow_up_date` (`follow_up_date`);

--
-- Indexes for table `support_tickets_notification`
--
ALTER TABLE `support_tickets_notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_support_notifications_ticket_id` (`ticket_id`),
  ADD KEY `idx_support_notifications_created_at` (`created_at`);

--
-- Indexes for table `support_ticket_activities`
--
ALTER TABLE `support_ticket_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_ticket_activities_support_ticket_id_foreign` (`support_ticket_id`),
  ADD KEY `support_ticket_activities_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `support_ticket_convs`
--
ALTER TABLE `support_ticket_convs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_support_ticket_convs_ticket_id` (`support_ticket_id`),
  ADD KEY `idx_support_ticket_convs_created_at` (`created_at`);

--
-- Indexes for table `support_ticket_department_employee`
--
ALTER TABLE `support_ticket_department_employee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_ticket_department_employee_ticket_id_foreign` (`ticket_id`),
  ADD KEY `idx_support_dept_emp_department_id` (`department_id`),
  ADD KEY `idx_support_dept_emp_employee_id` (`employee_id`);

--
-- Indexes for table `support_ticket_status_master`
--
ALTER TABLE `support_ticket_status_master`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`,`master_id`),
  ADD KEY `idx_support_status_master_status` (`status`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tags_tag` (`tag`),
  ADD KEY `idx_tags_visit_count` (`visit_count`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD UNIQUE KEY `transactions_id_unique` (`id`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translations_translationable_id_index` (`translationable_id`),
  ADD KEY `translations_locale_index` (`locale`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_current_crm_team_id_index` (`current_crm_team_id`);

--
-- Indexes for table `vehicle_makes`
--
ALTER TABLE `vehicle_makes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicle_makes_name_unique` (`name`);

--
-- Indexes for table `vehicle_models`
--
ALTER TABLE `vehicle_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_models_make_id_foreign` (`make_id`);

--
-- Indexes for table `vehicle_years`
--
ALTER TABLE `vehicle_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicle_years_year_unique` (`year`);

--
-- Indexes for table `vendor_registration_reasons`
--
ALTER TABLE `vendor_registration_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `view_tokens`
--
ALTER TABLE `view_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `view_tokens_jti_unique` (`jti`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `warranties`
--
ALTER TABLE `warranties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warranties_serial_number_unique` (`serial_number`),
  ADD UNIQUE KEY `warranties_warranty_public_id_unique` (`warranty_public_id`),
  ADD KEY `warranties_product_id_foreign` (`product_id`),
  ADD KEY `warranties_product_stock_id_foreign` (`product_stock_id`),
  ADD KEY `warranties_final_user_id_foreign` (`final_user_id`),
  ADD KEY `warranties_branch_id_foreign` (`branch_id`),
  ADD KEY `warranties_original_warranty_id_foreign` (`original_warranty_id`),
  ADD KEY `idx_warranties_status` (`status`),
  ADD KEY `idx_warranties_serial` (`serial_number`),
  ADD KEY `idx_warranties_product_id` (`product_id`),
  ADD KEY `idx_warranties_distributor_id` (`distributor_id`),
  ADD KEY `idx_warranties_retailer_branch_id` (`retailer_branch_id`),
  ADD KEY `idx_warranties_purchase_date` (`purchase_date`),
  ADD KEY `idx_warranties_status_end_date` (`status`,`end_date`),
  ADD KEY `idx_warranties_serial_status` (`serial_number`,`status`),
  ADD KEY `idx_warranties_final_user_status` (`final_user_id`,`status`),
  ADD KEY `idx_warranties_branch_status` (`branch_id`,`status`),
  ADD KEY `idx_warranties_end_date` (`end_date`),
  ADD KEY `idx_warranties_warranty_public_id` (`warranty_public_id`);

--
-- Indexes for table `warranty_claims`
--
ALTER TABLE `warranty_claims`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warranty_claims_claim_number_unique` (`claim_number`),
  ADD UNIQUE KEY `warranty_claims_rma_number_unique` (`rma_number`),
  ADD KEY `warranty_claims_warranty_id_foreign` (`warranty_id`),
  ADD KEY `warranty_claims_branch_id_foreign` (`branch_id`),
  ADD KEY `warranty_claims_technician_id_foreign` (`technician_id`),
  ADD KEY `warranty_claims_override_by_user_id_foreign` (`override_by_user_id`),
  ADD KEY `idx_warranty_claims_status_created` (`status`,`created_at`),
  ADD KEY `idx_warranty_claims_warranty_status` (`warranty_id`,`status`),
  ADD KEY `idx_warranty_claims_branch_status` (`branch_id`,`status`),
  ADD KEY `idx_warranty_claims_technician_status` (`technician_id`,`status`),
  ADD KEY `idx_warranty_claims_status` (`status`),
  ADD KEY `idx_warranty_claims_serial` (`serial_number`),
  ADD KEY `idx_warranty_claims_claim_number` (`claim_number`),
  ADD KEY `idx_warranty_claims_rma_number` (`rma_number`),
  ADD KEY `idx_warranty_claims_response_due` (`response_due`),
  ADD KEY `idx_warranty_claims_resolution_due` (`resolution_due`),
  ADD KEY `idx_warranty_claims_submitted_at` (`submitted_at`),
  ADD KEY `idx_warranty_claims_priority` (`priority`);

--
-- Indexes for table `warranty_claim_attachments`
--
ALTER TABLE `warranty_claim_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warranty_claim_attachments_warranty_claim_id_foreign` (`warranty_claim_id`),
  ADD KEY `idx_warranty_attachments_claim_id` (`warranty_claim_id`);

--
-- Indexes for table `warranty_claim_charges`
--
ALTER TABLE `warranty_claim_charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warranty_claim_charges_warranty_claim_id_foreign` (`warranty_claim_id`),
  ADD KEY `idx_warranty_charges_claim_id` (`warranty_claim_id`),
  ADD KEY `idx_warranty_charges_type` (`charge_type`);

--
-- Indexes for table `warranty_claim_payments`
--
ALTER TABLE `warranty_claim_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warranty_claim_payments_payment_link_token_unique` (`payment_link_token`),
  ADD KEY `warranty_claim_payments_warranty_claim_id_payment_status_index` (`warranty_claim_id`,`payment_status`),
  ADD KEY `warranty_claim_payments_payment_channel_payment_status_index` (`payment_channel`,`payment_status`),
  ADD KEY `idx_warranty_payments_claim_id` (`warranty_claim_id`);

--
-- Indexes for table `warranty_distribution_histories`
--
ALTER TABLE `warranty_distribution_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warranty_distribution_histories_warranty_id_foreign` (`warranty_id`),
  ADD KEY `warranty_distribution_histories_from_branch_id_foreign` (`from_branch_id`),
  ADD KEY `warranty_distribution_histories_to_branch_id_foreign` (`to_branch_id`),
  ADD KEY `idx_warranty_dist_warranty_id` (`warranty_id`),
  ADD KEY `idx_warranty_dist_from_branch` (`from_branch_id`),
  ADD KEY `idx_warranty_dist_to_branch` (`to_branch_id`),
  ADD KEY `idx_warranty_dist_created_at` (`created_at`);

--
-- Indexes for table `warranty_replacements`
--
ALTER TABLE `warranty_replacements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warranty_replacements_original_warranty_id_foreign` (`original_warranty_id`),
  ADD KEY `warranty_replacements_new_warranty_id_foreign` (`new_warranty_id`),
  ADD KEY `warranty_replacements_technician_id_foreign` (`technician_id`),
  ADD KEY `idx_warranty_replace_original_id` (`original_warranty_id`);

--
-- Indexes for table `warranty_timeline_events`
--
ALTER TABLE `warranty_timeline_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warranty_timeline_events_warranty_id_foreign` (`warranty_id`),
  ADD KEY `warranty_timeline_events_warranty_claim_id_foreign` (`warranty_claim_id`),
  ADD KEY `warranty_timeline_events_user_id_foreign` (`user_id`),
  ADD KEY `idx_warranty_timeline_claim_id` (`warranty_claim_id`),
  ADD KEY `idx_warranty_timeline_event_type` (`event_type`),
  ADD KEY `idx_warranty_timeline_created_at` (`created_at`),
  ADD KEY `idx_warranty_timeline_warranty_id` (`warranty_id`);

--
-- Indexes for table `wholesaler_businesses`
--
ALTER TABLE `wholesaler_businesses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wholesaler_businesses_wholesaler` (`wholesaler_id`),
  ADD KEY `idx_wholesaler_businesses_deleted_at` (`deleted_at`);

--
-- Indexes for table `wholesaler_logs`
--
ALTER TABLE `wholesaler_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wholesaler_product_requests`
--
ALTER TABLE `wholesaler_product_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesaler_product_requests_wholesaler_id_foreign` (`wholesaler_id`),
  ADD KEY `wholesaler_product_requests_product_id_foreign` (`product_id`);

--
-- Indexes for table `wholesaler_registration_reasons`
--
ALTER TABLE `wholesaler_registration_reasons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wholesaler_summary`
--
ALTER TABLE `wholesaler_summary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesaler_summary_wholesaler_id_foreign` (`wholesaler_id`);

--
-- Indexes for table `wholesale_categories`
--
ALTER TABLE `wholesale_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wholesale_confirmorder_item`
--
ALTER TABLE `wholesale_confirmorder_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesale_confirmorder_item_confirmed_order_id_foreign` (`confirmed_order_id`),
  ADD KEY `wholesale_confirmorder_item_product_id_foreign` (`product_id`);

--
-- Indexes for table `wholesale_confirm_orders`
--
ALTER TABLE `wholesale_confirm_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wholesale_contacts`
--
ALTER TABLE `wholesale_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wholesale_contacts_company` (`company_id`);

--
-- Indexes for table `wholesale_order_delivery`
--
ALTER TABLE `wholesale_order_delivery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesale_order_delivery_confirmed_order_id_foreign` (`confirmed_order_id`),
  ADD KEY `wholesale_order_delivery_product_id_foreign` (`product_id`),
  ADD KEY `wholesale_order_delivery_branch_id_foreign` (`branch_id`),
  ADD KEY `idx_wholesale_delivery_order_id` (`order_id`);

--
-- Indexes for table `wholesale_order_payments`
--
ALTER TABLE `wholesale_order_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesale_order_payments_wholesale_confirm_order_id_foreign` (`wholesale_confirm_order_id`),
  ADD KEY `idx_wholesale_payments_order_id` (`order_id`);

--
-- Indexes for table `wholesale_price_ranges`
--
ALTER TABLE `wholesale_price_ranges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesale_price_ranges_product_id_foreign` (`wholesale_id`),
  ADD KEY `idx_wholesale_price_ranges_wholesale_tier` (`wholesale_id`,`tier`),
  ADD KEY `idx_wholesale_price_ranges_wholesale` (`wholesale_id`),
  ADD KEY `idx_wholesale_price_ranges_tier` (`tier`);

--
-- Indexes for table `wholesale_price_tiers`
--
ALTER TABLE `wholesale_price_tiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesale_price_tiers_subcategory_id_foreign` (`subcategory_id`);

--
-- Indexes for table `wholesale_products`
--
ALTER TABLE `wholesale_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wholesale_products_product_status` (`product_id`,`status`),
  ADD KEY `idx_wholesale_products_category_status` (`category_id`,`status`),
  ADD KEY `idx_wholesale_products_subcategory_status` (`sub_category_id`,`status`),
  ADD KEY `idx_wholesale_products_status` (`status`),
  ADD KEY `idx_wholesale_products_variation_type` (`variation_type`);

--
-- Indexes for table `wholesale_purchase_orders`
--
ALTER TABLE `wholesale_purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_wholesale_purchase_orders_purchase_order_no` (`purchase_order_no`),
  ADD KEY `wholesale_orders_wholeseller_id_foreign` (`wholeseller_id`),
  ADD KEY `wholesale_orders_approved_by_foreign` (`approved_by`),
  ADD KEY `idx_wholesale_orders_wholeseller_status` (`wholeseller_id`,`status`),
  ADD KEY `idx_wholesale_orders_status_created` (`status`,`created_at`),
  ADD KEY `idx_wholesale_orders_status` (`status`),
  ADD KEY `idx_wholesale_orders_wholeseller` (`wholeseller_id`),
  ADD KEY `idx_wholesale_orders_created_at` (`created_at`);

--
-- Indexes for table `wholesale_purchase_order_items`
--
ALTER TABLE `wholesale_purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesale_order_items_wholesale_order_id_foreign` (`wholesale_order_id`),
  ADD KEY `wholesale_order_items_product_id_foreign` (`product_id`),
  ADD KEY `idx_wholesale_order_items_product_id` (`product_id`);

--
-- Indexes for table `wholesale_quotations`
--
ALTER TABLE `wholesale_quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesale_quotations_wholeseller_id_foreign` (`wholeseller_id`),
  ADD KEY `idx_wholesale_quotations_wholeseller_status` (`wholeseller_id`,`status`),
  ADD KEY `idx_wholesale_quotations_status` (`status`),
  ADD KEY `idx_wholesale_quotations_created_at` (`created_at`);

--
-- Indexes for table `wholesale_quotation_items`
--
ALTER TABLE `wholesale_quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wholesale_quotation_items_wholesale_quotation_id_foreign` (`wholesale_quotation_id`),
  ADD KEY `wholesale_quotation_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `wholesale_tiers`
--
ALTER TABLE `wholesale_tiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wholesale_tiers_is_active` (`is_active`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wishlists_customer_product` (`customer_id`,`product_id`),
  ADD KEY `idx_wishlists_product` (`product_id`);

--
-- Indexes for table `withdrawal_methods`
--
ALTER TABLE `withdrawal_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_orders_warranty_claim_id_foreign` (`warranty_claim_id`);

--
-- Indexes for table `xero_tokens`
--
ALTER TABLE `xero_tokens`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_dealer_sections`
--
ALTER TABLE `about_dealer_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `about_hero_sections`
--
ALTER TABLE `about_hero_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `about_mission_sections`
--
ALTER TABLE `about_mission_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `about_product_sections`
--
ALTER TABLE `about_product_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `about_timeline_sections`
--
ALTER TABLE `about_timeline_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `about_who_we_are_sections`
--
ALTER TABLE `about_who_we_are_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activation_reviews`
--
ALTER TABLE `activation_reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=315;

--
-- AUTO_INCREMENT for table `add_fund_bonus_categories`
--
ALTER TABLE `add_fund_bonus_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `admin_wallets`
--
ALTER TABLE `admin_wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_wallet_histories`
--
ALTER TABLE `admin_wallet_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `analytic_scripts`
--
ALTER TABLE `analytic_scripts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `areas`
--
ALTER TABLE `areas`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attributes`
--
ALTER TABLE `attributes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `audits`
--
ALTER TABLE `audits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `billing_addresses`
--
ALTER TABLE `billing_addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `blacklists`
--
ALTER TABLE `blacklists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blog_seos`
--
ALTER TABLE `blog_seos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_translations`
--
ALTER TABLE `blog_translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `branch_delivery_restrictions`
--
ALTER TABLE `branch_delivery_restrictions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branch_shipping_method_areas`
--
ALTER TABLE `branch_shipping_method_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `business_pages`
--
ALTER TABLE `business_pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `business_settings`
--
ALTER TABLE `business_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242;

--
-- AUTO_INCREMENT for table `calendar_todos`
--
ALTER TABLE `calendar_todos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `career_activities`
--
ALTER TABLE `career_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `career_applies`
--
ALTER TABLE `career_applies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `career_benefits`
--
ALTER TABLE `career_benefits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `career_cards`
--
ALTER TABLE `career_cards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `career_interviews`
--
ALTER TABLE `career_interviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `career_jobs`
--
ALTER TABLE `career_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `career_offers`
--
ALTER TABLE `career_offers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `career_rejections`
--
ALTER TABLE `career_rejections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `career_sections`
--
ALTER TABLE `career_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `career_talent_pool`
--
ALTER TABLE `career_talent_pool`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=400066;

--
-- AUTO_INCREMENT for table `cart_shippings`
--
ALTER TABLE `cart_shippings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `category_shipping_costs`
--
ALTER TABLE `category_shipping_costs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chattings`
--
ALTER TABLE `chattings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `cms_pages`
--
ALTER TABLE `cms_pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cms_products`
--
ALTER TABLE `cms_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cms_services`
--
ALTER TABLE `cms_services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `contact_banners`
--
ALTER TABLE `contact_banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `crm_calls`
--
ALTER TABLE `crm_calls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cron_configuration`
--
ALTER TABLE `cron_configuration`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cron_sender_details`
--
ALTER TABLE `cron_sender_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customer_wallets`
--
ALTER TABLE `customer_wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_wallet_histories`
--
ALTER TABLE `customer_wallet_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `deal_activities`
--
ALTER TABLE `deal_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;

--
-- AUTO_INCREMENT for table `deal_calls`
--
ALTER TABLE `deal_calls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deal_files`
--
ALTER TABLE `deal_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deal_notes`
--
ALTER TABLE `deal_notes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deal_of_the_days`
--
ALTER TABLE `deal_of_the_days`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deal_tasks`
--
ALTER TABLE `deal_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deliveryman_notifications`
--
ALTER TABLE `deliveryman_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `deliveryman_wallets`
--
ALTER TABLE `deliveryman_wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery_areas`
--
ALTER TABLE `delivery_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `delivery_cities`
--
ALTER TABLE `delivery_cities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `delivery_country_codes`
--
ALTER TABLE `delivery_country_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `delivery_histories`
--
ALTER TABLE `delivery_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_man_transactions`
--
ALTER TABLE `delivery_man_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `delivery_men`
--
ALTER TABLE `delivery_men`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `delivery_states`
--
ALTER TABLE `delivery_states`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `delivery_zip_codes`
--
ALTER TABLE `delivery_zip_codes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `department_users`
--
ALTER TABLE `department_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `digital_product_authors`
--
ALTER TABLE `digital_product_authors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `digital_product_otp_verifications`
--
ALTER TABLE `digital_product_otp_verifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `digital_product_publishing_houses`
--
ALTER TABLE `digital_product_publishing_houses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `digital_product_variations`
--
ALTER TABLE `digital_product_variations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `error_logs`
--
ALTER TABLE `error_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1922;

--
-- AUTO_INCREMENT for table `escalations`
--
ALTER TABLE `escalations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feature_deals`
--
ALTER TABLE `feature_deals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flash_deals`
--
ALTER TABLE `flash_deals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `flash_deal_products`
--
ALTER TABLE `flash_deal_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `guest_users`
--
ALTER TABLE `guest_users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=884;

--
-- AUTO_INCREMENT for table `help_topics`
--
ALTER TABLE `help_topics`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `home_page_sections`
--
ALTER TABLE `home_page_sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `inbox_activities`
--
ALTER TABLE `inbox_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `inbox_calls`
--
ALTER TABLE `inbox_calls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inbox_files`
--
ALTER TABLE `inbox_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inbox_messages`
--
ALTER TABLE `inbox_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `inbox_notes`
--
ALTER TABLE `inbox_notes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inbox_suggestions`
--
ALTER TABLE `inbox_suggestions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inbox_tasks`
--
ALTER TABLE `inbox_tasks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `lead_activity`
--
ALTER TABLE `lead_activity`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `lead_call`
--
ALTER TABLE `lead_call`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_file`
--
ALTER TABLE `lead_file`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lead_note`
--
ALTER TABLE `lead_note`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lead_notifications`
--
ALTER TABLE `lead_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_task`
--
ALTER TABLE `lead_task`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `logins`
--
ALTER TABLE `logins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_setups`
--
ALTER TABLE `login_setups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `loyalty_point_transactions`
--
ALTER TABLE `loyalty_point_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `manage_branch_product_stock`
--
ALTER TABLE `manage_branch_product_stock`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `manage_extra_charges`
--
ALTER TABLE `manage_extra_charges`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=547;

--
-- AUTO_INCREMENT for table `most_demandeds`
--
ALTER TABLE `most_demandeds`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notification_messages`
--
ALTER TABLE `notification_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `notification_seens`
--
ALTER TABLE `notification_seens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `offline_payments`
--
ALTER TABLE `offline_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offline_payment_methods`
--
ALTER TABLE `offline_payment_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100123;

--
-- AUTO_INCREMENT for table `order_delivery_verifications`
--
ALTER TABLE `order_delivery_verifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT for table `order_expected_delivery_histories`
--
ALTER TABLE `order_expected_delivery_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_status_histories`
--
ALTER TABLE `order_status_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=322;

--
-- AUTO_INCREMENT for table `order_transactions`
--
ALTER TABLE `order_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_terms`
--
ALTER TABLE `payment_terms`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paytabs_invoices`
--
ALTER TABLE `paytabs_invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=331;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `phone_or_email_verifications`
--
ALTER TABLE `phone_or_email_verifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pos_cart_states`
--
ALTER TABLE `pos_cart_states`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=400066;

--
-- AUTO_INCREMENT for table `product_compares`
--
ALTER TABLE `product_compares`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_seos`
--
ALTER TABLE `product_seos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `product_stocks`
--
ALTER TABLE `product_stocks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `product_stock_transactions`
--
ALTER TABLE `product_stock_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `product_tag`
--
ALTER TABLE `product_tag`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `publishing_houses`
--
ALTER TABLE `publishing_houses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_metas`
--
ALTER TABLE `quotation_metas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `refund_requests`
--
ALTER TABLE `refund_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `refund_statuses`
--
ALTER TABLE `refund_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `refund_transactions`
--
ALTER TABLE `refund_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `restock_products`
--
ALTER TABLE `restock_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `restock_product_customers`
--
ALTER TABLE `restock_product_customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `review_replies`
--
ALTER TABLE `review_replies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `robots_meta_contents`
--
ALTER TABLE `robots_meta_contents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `search_functions`
--
ALTER TABLE `search_functions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `seller_wallets`
--
ALTER TABLE `seller_wallets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `seller_wallet_histories`
--
ALTER TABLE `seller_wallet_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `serial_transfer_histories`
--
ALTER TABLE `serial_transfer_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_cancellations`
--
ALTER TABLE `service_cancellations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_change_orders`
--
ALTER TABLE `service_change_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_estimates`
--
ALTER TABLE `service_estimates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `service_invoices`
--
ALTER TABLE `service_invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_jobs`
--
ALTER TABLE `service_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `service_job_activities`
--
ALTER TABLE `service_job_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `service_job_items`
--
ALTER TABLE `service_job_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=279;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `shipping_method_areas`
--
ALTER TABLE `shipping_method_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `shipping_types`
--
ALTER TABLE `shipping_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shop_followers`
--
ALTER TABLE `shop_followers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sla_breaches`
--
ALTER TABLE `sla_breaches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sla_policies`
--
ALTER TABLE `sla_policies`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `social_medias`
--
ALTER TABLE `social_medias`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `soft_credentials`
--
ALTER TABLE `soft_credentials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `stock_clearance_products`
--
ALTER TABLE `stock_clearance_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_clearance_setups`
--
ALTER TABLE `stock_clearance_setups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_received`
--
ALTER TABLE `stock_received`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_requests`
--
ALTER TABLE `stock_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `stock_request_products`
--
ALTER TABLE `stock_request_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `stock_transfer_products`
--
ALTER TABLE `stock_transfer_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `storages`
--
ALTER TABLE `storages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `support_tickets_notification`
--
ALTER TABLE `support_tickets_notification`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `support_ticket_activities`
--
ALTER TABLE `support_ticket_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `support_ticket_convs`
--
ALTER TABLE `support_ticket_convs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `support_ticket_department_employee`
--
ALTER TABLE `support_ticket_department_employee`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `support_ticket_status_master`
--
ALTER TABLE `support_ticket_status_master`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=869;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `vehicle_makes`
--
ALTER TABLE `vehicle_makes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `vehicle_models`
--
ALTER TABLE `vehicle_models`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `vehicle_years`
--
ALTER TABLE `vehicle_years`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `vendor_registration_reasons`
--
ALTER TABLE `vendor_registration_reasons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `view_tokens`
--
ALTER TABLE `view_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `warranties`
--
ALTER TABLE `warranties`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=211;

--
-- AUTO_INCREMENT for table `warranty_claims`
--
ALTER TABLE `warranty_claims`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `warranty_claim_attachments`
--
ALTER TABLE `warranty_claim_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `warranty_claim_charges`
--
ALTER TABLE `warranty_claim_charges`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `warranty_claim_payments`
--
ALTER TABLE `warranty_claim_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `warranty_distribution_histories`
--
ALTER TABLE `warranty_distribution_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warranty_replacements`
--
ALTER TABLE `warranty_replacements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warranty_timeline_events`
--
ALTER TABLE `warranty_timeline_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `wholesaler_businesses`
--
ALTER TABLE `wholesaler_businesses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wholesaler_logs`
--
ALTER TABLE `wholesaler_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wholesaler_product_requests`
--
ALTER TABLE `wholesaler_product_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wholesaler_registration_reasons`
--
ALTER TABLE `wholesaler_registration_reasons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `wholesaler_summary`
--
ALTER TABLE `wholesaler_summary`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wholesale_categories`
--
ALTER TABLE `wholesale_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wholesale_confirmorder_item`
--
ALTER TABLE `wholesale_confirmorder_item`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wholesale_confirm_orders`
--
ALTER TABLE `wholesale_confirm_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wholesale_contacts`
--
ALTER TABLE `wholesale_contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wholesale_order_delivery`
--
ALTER TABLE `wholesale_order_delivery`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wholesale_order_payments`
--
ALTER TABLE `wholesale_order_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wholesale_price_ranges`
--
ALTER TABLE `wholesale_price_ranges`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `wholesale_price_tiers`
--
ALTER TABLE `wholesale_price_tiers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wholesale_products`
--
ALTER TABLE `wholesale_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `wholesale_purchase_orders`
--
ALTER TABLE `wholesale_purchase_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `wholesale_purchase_order_items`
--
ALTER TABLE `wholesale_purchase_order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `wholesale_quotations`
--
ALTER TABLE `wholesale_quotations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `wholesale_quotation_items`
--
ALTER TABLE `wholesale_quotation_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `wholesale_tiers`
--
ALTER TABLE `wholesale_tiers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `withdrawal_methods`
--
ALTER TABLE `withdrawal_methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `work_orders`
--
ALTER TABLE `work_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `xero_tokens`
--
ALTER TABLE `xero_tokens`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activation_reviews`
--
ALTER TABLE `activation_reviews`
  ADD CONSTRAINT `activation_reviews_warranty_id_foreign` FOREIGN KEY (`warranty_id`) REFERENCES `warranties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_notifications_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blacklists`
--
ALTER TABLE `blacklists`
  ADD CONSTRAINT `blacklists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `fk_manager` FOREIGN KEY (`manager_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `branch_delivery_restrictions`
--
ALTER TABLE `branch_delivery_restrictions`
  ADD CONSTRAINT `branch_delivery_restrictions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_delivery_restrictions_delivery_area_id_foreign` FOREIGN KEY (`delivery_area_id`) REFERENCES `delivery_areas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_shipping_method_areas`
--
ALTER TABLE `branch_shipping_method_areas`
  ADD CONSTRAINT `branch_shipping_method_areas_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_shipping_method_areas_shipping_method_area_id_foreign` FOREIGN KEY (`shipping_method_area_id`) REFERENCES `shipping_method_areas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `calendar_todos`
--
ALTER TABLE `calendar_todos`
  ADD CONSTRAINT `calendar_todos_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `career_activities`
--
ALTER TABLE `career_activities`
  ADD CONSTRAINT `career_activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `career_activities_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `career_applies`
--
ALTER TABLE `career_applies`
  ADD CONSTRAINT `career_applies_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `career_jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `career_interviews`
--
ALTER TABLE `career_interviews`
  ADD CONSTRAINT `career_interviews_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `career_offers`
--
ALTER TABLE `career_offers`
  ADD CONSTRAINT `career_offers_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `career_rejections`
--
ALTER TABLE `career_rejections`
  ADD CONSTRAINT `career_rejections_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `career_talent_pool`
--
ALTER TABLE `career_talent_pool`
  ADD CONSTRAINT `career_talent_pool_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `crm_calls`
--
ALTER TABLE `crm_calls`
  ADD CONSTRAINT `crm_calls_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `crm_calls_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deals`
--
ALTER TABLE `deals`
  ADD CONSTRAINT `deals_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deals_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deal_activities`
--
ALTER TABLE `deal_activities`
  ADD CONSTRAINT `deal_activities_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_activities_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deal_calls`
--
ALTER TABLE `deal_calls`
  ADD CONSTRAINT `deal_calls_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_calls_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deal_calls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deal_files`
--
ALTER TABLE `deal_files`
  ADD CONSTRAINT `deal_files_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_files_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deal_notes`
--
ALTER TABLE `deal_notes`
  ADD CONSTRAINT `deal_notes_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_notes_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deal_tasks`
--
ALTER TABLE `deal_tasks`
  ADD CONSTRAINT `deal_tasks_deal_id_foreign` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deal_tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deal_tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `department_users`
--
ALTER TABLE `department_users`
  ADD CONSTRAINT `department_users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `escalations`
--
ALTER TABLE `escalations`
  ADD CONSTRAINT `escalations_escalated_by_foreign` FOREIGN KEY (`escalated_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inbox_activities`
--
ALTER TABLE `inbox_activities`
  ADD CONSTRAINT `inbox_activities_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inbox_activities_massage_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `inbox_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inbox_calls`
--
ALTER TABLE `inbox_calls`
  ADD CONSTRAINT `inbox_calls_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inbox_calls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inbox_calls_massage_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `inbox_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inbox_files`
--
ALTER TABLE `inbox_files`
  ADD CONSTRAINT `inbox_files_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inbox_files_massage_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `inbox_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inbox_messages`
--
ALTER TABLE `inbox_messages`
  ADD CONSTRAINT `inbox_messages_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inbox_messages_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inbox_notes`
--
ALTER TABLE `inbox_notes`
  ADD CONSTRAINT `inbox_notes_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inbox_notes_massage_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `inbox_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inbox_suggestions`
--
ALTER TABLE `inbox_suggestions`
  ADD CONSTRAINT `inbox_suggestions_inbox_message_id_foreign` FOREIGN KEY (`inbox_message_id`) REFERENCES `inbox_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inbox_suggestions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inbox_tasks`
--
ALTER TABLE `inbox_tasks`
  ADD CONSTRAINT `inbox_tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inbox_tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inbox_tasks_massage_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `inbox_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_activity`
--
ALTER TABLE `lead_activity`
  ADD CONSTRAINT `lead_activity_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_activity_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_call`
--
ALTER TABLE `lead_call`
  ADD CONSTRAINT `lead_call_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_call_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_call_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_file`
--
ALTER TABLE `lead_file`
  ADD CONSTRAINT `lead_file_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_file_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_note`
--
ALTER TABLE `lead_note`
  ADD CONSTRAINT `lead_note_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_note_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_notifications`
--
ALTER TABLE `lead_notifications`
  ADD CONSTRAINT `lead_notifications_from_user_id_foreign` FOREIGN KEY (`from_user_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_task`
--
ALTER TABLE `lead_task`
  ADD CONSTRAINT `lead_task_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_task_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_task_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `manage_branch_product_stock`
--
ALTER TABLE `manage_branch_product_stock`
  ADD CONSTRAINT `fk_branch_stock_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `manage_branch_product_stock_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `manage_branch_product_stock_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `manage_extra_charges`
--
ALTER TABLE `manage_extra_charges`
  ADD CONSTRAINT `manage_extra_charges_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `policies`
--
ALTER TABLE `policies`
  ADD CONSTRAINT `policies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_stock_transactions`
--
ALTER TABLE `product_stock_transactions`
  ADD CONSTRAINT `product_stock_transactions_from_branch_id_foreign` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_stock_transactions_product_stock_id_foreign` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stocks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_stock_transactions_to_branch_id_foreign` FOREIGN KEY (`to_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `serial_transfer_histories`
--
ALTER TABLE `serial_transfer_histories`
  ADD CONSTRAINT `serial_transfer_histories_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `serial_transfer_histories_wholesale_delivery_id_foreign` FOREIGN KEY (`wholesale_delivery_id`) REFERENCES `wholesale_order_delivery` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_cancellations`
--
ALTER TABLE `service_cancellations`
  ADD CONSTRAINT `service_cancellations_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_cancellations_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `service_jobs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_change_orders`
--
ALTER TABLE `service_change_orders`
  ADD CONSTRAINT `service_change_orders_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_change_orders_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `service_jobs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_estimates`
--
ALTER TABLE `service_estimates`
  ADD CONSTRAINT `service_estimates_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_invoices`
--
ALTER TABLE `service_invoices`
  ADD CONSTRAINT `service_invoices_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `service_jobs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_invoices_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_jobs`
--
ALTER TABLE `service_jobs`
  ADD CONSTRAINT `service_jobs_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_jobs_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_job_activities`
--
ALTER TABLE `service_job_activities`
  ADD CONSTRAINT `service_job_activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_job_activities_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `service_jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_job_items`
--
ALTER TABLE `service_job_items`
  ADD CONSTRAINT `service_job_items_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `service_jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_requests`
--
ALTER TABLE `stock_requests`
  ADD CONSTRAINT `stock_requests_from_branch_id_foreign` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `stock_transfer_products`
--
ALTER TABLE `stock_transfer_products`
  ADD CONSTRAINT `fk_stock_transfer_product_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `wholesaler_businesses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `support_ticket_activities`
--
ALTER TABLE `support_ticket_activities`
  ADD CONSTRAINT `support_ticket_activities_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_ticket_activities_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warranties`
--
ALTER TABLE `warranties`
  ADD CONSTRAINT `warranties_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranties_final_user_id_foreign` FOREIGN KEY (`final_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranties_original_warranty_id_foreign` FOREIGN KEY (`original_warranty_id`) REFERENCES `warranties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warranties_product_stock_id_foreign` FOREIGN KEY (`product_stock_id`) REFERENCES `product_stocks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranties_retailer_branch_id_foreign` FOREIGN KEY (`retailer_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `warranty_claims`
--
ALTER TABLE `warranty_claims`
  ADD CONSTRAINT `warranty_claims_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranty_claims_override_by_user_id_foreign` FOREIGN KEY (`override_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranty_claims_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranty_claims_warranty_id_foreign` FOREIGN KEY (`warranty_id`) REFERENCES `warranties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warranty_claim_attachments`
--
ALTER TABLE `warranty_claim_attachments`
  ADD CONSTRAINT `warranty_claim_attachments_warranty_claim_id_foreign` FOREIGN KEY (`warranty_claim_id`) REFERENCES `warranty_claims` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warranty_claim_charges`
--
ALTER TABLE `warranty_claim_charges`
  ADD CONSTRAINT `warranty_claim_charges_warranty_claim_id_foreign` FOREIGN KEY (`warranty_claim_id`) REFERENCES `warranty_claims` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warranty_claim_payments`
--
ALTER TABLE `warranty_claim_payments`
  ADD CONSTRAINT `warranty_claim_payments_warranty_claim_id_foreign` FOREIGN KEY (`warranty_claim_id`) REFERENCES `warranty_claims` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warranty_distribution_histories`
--
ALTER TABLE `warranty_distribution_histories`
  ADD CONSTRAINT `warranty_distribution_histories_from_branch_id_foreign` FOREIGN KEY (`from_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranty_distribution_histories_to_branch_id_foreign` FOREIGN KEY (`to_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranty_distribution_histories_warranty_id_foreign` FOREIGN KEY (`warranty_id`) REFERENCES `warranties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warranty_replacements`
--
ALTER TABLE `warranty_replacements`
  ADD CONSTRAINT `warranty_replacements_new_warranty_id_foreign` FOREIGN KEY (`new_warranty_id`) REFERENCES `warranties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warranty_replacements_original_warranty_id_foreign` FOREIGN KEY (`original_warranty_id`) REFERENCES `warranties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warranty_replacements_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `warranty_timeline_events`
--
ALTER TABLE `warranty_timeline_events`
  ADD CONSTRAINT `warranty_timeline_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `warranty_timeline_events_warranty_claim_id_foreign` FOREIGN KEY (`warranty_claim_id`) REFERENCES `warranty_claims` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warranty_timeline_events_warranty_id_foreign` FOREIGN KEY (`warranty_id`) REFERENCES `warranties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD CONSTRAINT `work_orders_warranty_claim_id_foreign` FOREIGN KEY (`warranty_claim_id`) REFERENCES `warranty_claims` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
