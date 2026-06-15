-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: cash_tracker
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('ewallet','bank','cash','ppob','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ewallet',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,'ShopeePay','ewallet',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(2,'Dana','ewallet',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(3,'OrderKuota','ppob',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(4,'GoPay','ewallet',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(5,'Rita','ppob',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(6,'Sidiva','ppob',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(7,'Simpel','ppob',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(8,'Digipos','ppob',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(9,'BCA','bank',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(10,'Cash','cash',1,'2026-06-10 04:55:33','2026-06-10 04:55:33'),(11,'EDC Pending','bank',0,'2026-06-10 06:47:03','2026-06-11 14:33:57');
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_payments`
--

DROP TABLE IF EXISTS `bill_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `recurring_bill_id` bigint unsigned DEFAULT NULL,
  `period` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expense_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_payments_recurring_bill_id_period_unique` (`recurring_bill_id`,`period`),
  KEY `bill_payments_expense_id_foreign` (`expense_id`),
  CONSTRAINT `bill_payments_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bill_payments_recurring_bill_id_foreign` FOREIGN KEY (`recurring_bill_id`) REFERENCES `recurring_bills` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_payments`
--

LOCK TABLES `bill_payments` WRITE;
/*!40000 ALTER TABLE `bill_payments` DISABLE KEYS */;
INSERT INTO `bill_payments` VALUES (2,8,'2026-06',75000.00,'2026-06-12 01:40:36',NULL,'2026-06-12 01:40:36','2026-06-12 01:40:36'),(3,12,'2026-06',35000.00,'2026-06-12 01:42:36',NULL,'2026-06-12 01:42:36','2026-06-12 01:42:36'),(4,11,'2026-06',167000.00,'2026-06-12 01:42:38',NULL,'2026-06-12 01:42:38','2026-06-12 01:42:38'),(5,10,'2026-06',185000.00,'2026-06-12 01:42:40',NULL,'2026-06-12 01:42:40','2026-06-12 01:42:40'),(6,9,'2026-06',255000.00,'2026-06-12 01:42:42',NULL,'2026-06-12 01:42:42','2026-06-12 01:42:42');
/*!40000 ALTER TABLE `bill_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_counter_sessions`
--

DROP TABLE IF EXISTS `cash_counter_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_counter_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `denominations` json NOT NULL,
  `target_amount` decimal(15,2) DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_counter_sessions_user_id_foreign` (`user_id`),
  CONSTRAINT `cash_counter_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_counter_sessions`
--

LOCK TABLES `cash_counter_sessions` WRITE;
/*!40000 ALTER TABLE `cash_counter_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `cash_counter_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `stock_transaction_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_stock_transaction_id_foreign` (`stock_transaction_id`),
  KEY `expenses_account_id_foreign` (`account_id`),
  CONSTRAINT `expenses_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_stock_transaction_id_foreign` FOREIGN KEY (`stock_transaction_id`) REFERENCES `stock_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (11,'2026-06-01 21:50:39',10,'Tagihan Bulanan',546068,'LISTRIK, WIFI, CHATGPT & GOOGLE PHOTOS','2026-06-11 14:50:39','2026-06-11 14:50:39',NULL),(20,'2026-06-14 22:12:45',9,'Sparepart',103000,'LCD Oppo A3s','2026-06-14 15:12:45','2026-06-14 15:12:45',NULL),(21,'2026-06-15 11:57:01',10,'Restock',229500,'SP Indosat 3gb = 3pcs ; SP Byu 3gb = 3pcs ; Voucher Blank Indosat 150pcs.','2026-06-15 04:57:01','2026-06-15 04:57:01',NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incomes`
--

DROP TABLE IF EXISTS `incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `incomes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `amount` int NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `stock_transaction_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incomes_account_id_foreign` (`account_id`),
  KEY `incomes_stock_transaction_id_foreign` (`stock_transaction_id`),
  CONSTRAINT `incomes_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `incomes_stock_transaction_id_foreign` FOREIGN KEY (`stock_transaction_id`) REFERENCES `stock_transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incomes`
--

LOCK TABLES `incomes` WRITE;
/*!40000 ALTER TABLE `incomes` DISABLE KEYS */;
INSERT INTO `incomes` VALUES (3,'2026-06-11 21:53:50',2138309,'Omzet','Omzet 1-11 Juni 2026','2026-06-11 14:08:51','2026-06-11 14:53:50',10,NULL),(8,'2026-06-12 19:53:44',162467,'Omzet',NULL,'2026-06-12 12:53:44','2026-06-12 12:53:44',10,NULL),(19,'2026-06-13 21:57:13',285360,'Omzet',NULL,'2026-06-13 14:57:13','2026-06-13 14:57:13',10,NULL),(21,'2026-06-14 11:46:36',14000,'Penjualan','Penjualan 14/06/2026 11:46','2026-06-14 04:46:36','2026-06-14 04:46:36',10,NULL),(22,'2026-06-14 21:03:48',7000,'Penjualan','Penjualan 14/06/2026 21:03 - Parfum Mezuca (1 pcs)','2026-06-14 14:03:48','2026-06-14 14:03:48',10,NULL),(24,'2026-06-14 09:26:13',306831,'Omzet',NULL,'2026-06-14 15:15:10','2026-06-15 02:26:13',10,NULL);
/*!40000 ALTER TABLE `incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_06_10_115248_create_accounts_table',1),(5,'2026_06_10_115249_create_mutations_table',1),(6,'2026_06_10_115249_create_opening_balances_table',1),(7,'2026_06_10_115250_create_expenses_table',1),(8,'2026_06_10_115250_create_receivables_table',1),(9,'2026_06_10_115251_create_receivable_payments_table',1),(12,'2026_06_10_115254_add_type_to_mutations_table',2),(13,'2026_06_11_093432_drop_type_from_mutations_table',3),(14,'2026_06_11_093448_create_incomes_table',4),(15,'2026_06_11_093857_add_fee_to_receivables_table',5),(16,'2026_06_11_111524_add_category_to_incomes_table',6),(17,'2026_06_11_122222_change_date_columns_to_datetime',7),(18,'2026_06_11_211614_add_account_id_to_incomes_table',8),(20,'2026_06_11_220625_create_recurring_bills_table',9),(21,'2026_06_11_220626_create_bill_payments_table',9),(22,'2026_06_13_084617_remove_fee_from_receivables_table',10),(23,'2026_06_13_094458_create_product_categories_table',11),(24,'2026_06_13_094502_create_products_table',11),(25,'2026_06_13_094505_create_stock_transactions_table',11),(26,'2026_06_13_100208_update_names_to_title_case',12),(27,'2026_06_13_102642_add_stock_transaction_id_to_incomes_and_expenses',13),(28,'2026_06_13_103123_backfill_stock_transaction_id',14),(29,'2026_06_13_105527_add_opname_type_to_stock_transactions',15),(30,'2026_06_13_110526_add_income_id_and_receipt_id_to_stock_transactions',16),(31,'2026_06_14_100811_add_username_and_permissions_to_users',17),(32,'2026_06_14_104752_make_email_nullable_in_users',18),(33,'2026_06_14_121526_change_cascade_to_null_on_delete',19),(34,'2026_06_15_000001_create_cash_counter_sessions_table',20);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mutations`
--

DROP TABLE IF EXISTS `mutations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mutations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `from_account_id` bigint unsigned DEFAULT NULL,
  `to_account_id` bigint unsigned DEFAULT NULL,
  `amount` int NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mutations_from_account_id_foreign` (`from_account_id`),
  KEY `mutations_to_account_id_foreign` (`to_account_id`),
  CONSTRAINT `mutations_from_account_id_foreign` FOREIGN KEY (`from_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mutations_to_account_id_foreign` FOREIGN KEY (`to_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mutations`
--

LOCK TABLES `mutations` WRITE;
/*!40000 ALTER TABLE `mutations` DISABLE KEYS */;
INSERT INTO `mutations` VALUES (12,'2026-06-11 21:21:34',1,10,21800,NULL,'2026-06-11 14:21:34','2026-06-11 14:21:34'),(13,'2026-06-11 21:28:00',10,9,618609,NULL,'2026-06-11 14:28:00','2026-06-11 14:28:00'),(14,'2026-06-12 19:32:57',10,9,1089000,'DARI EDC','2026-06-12 12:32:57','2026-06-12 12:32:57'),(15,'2026-06-12 19:39:36',1,10,251714,NULL,'2026-06-12 12:39:36','2026-06-12 12:39:36'),(16,'2026-06-12 19:40:23',10,2,35000,NULL,'2026-06-12 12:40:23','2026-06-12 12:40:23'),(17,'2026-06-12 19:40:56',3,10,269819,NULL,'2026-06-12 12:40:56','2026-06-12 12:40:56'),(18,'2026-06-12 19:41:39',4,10,50000,NULL,'2026-06-12 12:41:39','2026-06-12 12:41:39'),(19,'2026-06-12 19:42:31',5,10,22000,NULL,'2026-06-12 12:42:31','2026-06-12 12:42:31'),(20,'2026-06-12 19:48:36',10,9,100000,'Tarik Tunai','2026-06-12 12:48:36','2026-06-12 12:48:36'),(22,'2026-06-13 09:25:18',9,4,160000,'Topup Gopay','2026-06-13 02:24:51','2026-06-13 02:25:18'),(23,'2026-06-13 13:07:38',10,7,100000,'To Up Saldo','2026-06-13 06:07:38','2026-06-13 06:07:38'),(24,'2026-06-13 21:41:35',3,10,559096,'Transfer cepat','2026-06-13 14:41:35','2026-06-13 14:41:35'),(25,'2026-06-13 21:42:13',1,10,675979,'Transfer cepat','2026-06-13 14:42:13','2026-06-13 14:42:13'),(26,'2026-06-13 21:45:19',2,10,390000,'Transfer cepat','2026-06-13 14:45:19','2026-06-13 14:45:19'),(27,'2026-06-13 21:46:09',4,10,130000,'Transfer cepat','2026-06-13 14:46:09','2026-06-13 14:46:09'),(28,'2026-06-13 21:46:42',7,10,23500,'Transfer cepat','2026-06-13 14:46:42','2026-06-13 14:46:42'),(29,'2026-06-13 21:47:31',8,10,16930,'Transfer cepat','2026-06-13 14:47:31','2026-06-13 14:47:31'),(30,'2026-06-13 21:48:48',10,9,748440,'EDC','2026-06-13 14:48:48','2026-06-13 14:48:48'),(31,'2026-06-13 21:50:25',10,9,200000,'Tarik Tunai','2026-06-13 14:50:25','2026-06-13 14:50:25'),(32,'2026-06-13 21:52:57',6,10,75,'Transfer cepat','2026-06-13 14:52:57','2026-06-13 14:52:57'),(33,'2026-06-14 20:52:28',9,3,964785,'Topup','2026-06-14 01:14:13','2026-06-14 13:52:28'),(34,'2026-06-14 08:16:57',9,2,555000,'Topup','2026-06-14 01:16:57','2026-06-14 01:16:57'),(35,'2026-06-14 08:52:00',9,8,247820,'Topup','2026-06-14 01:41:36','2026-06-14 01:52:00'),(36,'2026-06-14 20:52:16',10,9,100000,'Tarik Tunai','2026-06-14 01:45:05','2026-06-14 13:52:16'),(37,'2026-06-14 08:53:41',9,1,1065232,'Topup','2026-06-14 01:53:41','2026-06-14 01:53:41'),(38,'2026-06-14 20:53:15',10,9,1997700,'Tarik Tunai EDC','2026-06-14 13:53:15','2026-06-14 13:53:15'),(39,'2026-06-14 21:08:47',1,10,275979,'Transfer cepat','2026-06-14 14:08:47','2026-06-14 14:08:47'),(40,'2026-06-14 21:10:50',10,9,290000,'Tarik Tunai','2026-06-14 14:10:50','2026-06-14 14:10:50'),(41,'2026-06-14 21:12:19',3,10,877790,'Transfer cepat','2026-06-14 14:12:19','2026-06-14 14:12:19'),(42,'2026-06-14 21:12:53',5,10,20000,'Transfer cepat','2026-06-14 14:12:53','2026-06-14 14:12:53'),(43,'2026-06-14 21:13:24',7,10,29100,'Transfer cepat','2026-06-14 14:13:24','2026-06-14 14:13:24'),(44,'2026-06-14 21:13:49',8,10,1500,'Transfer cepat','2026-06-14 14:13:49','2026-06-14 14:13:49'),(46,'2026-06-15 09:57:15',9,3,880063,'Top Up','2026-06-15 02:57:15','2026-06-15 02:57:15');
/*!40000 ALTER TABLE `mutations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opening_balances`
--

DROP TABLE IF EXISTS `opening_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opening_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint unsigned DEFAULT NULL,
  `period` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `opening_balances_account_id_period_unique` (`account_id`,`period`),
  CONSTRAINT `opening_balances_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opening_balances`
--

LOCK TABLES `opening_balances` WRITE;
/*!40000 ALTER TABLE `opening_balances` DISABLE KEYS */;
INSERT INTO `opening_balances` VALUES (1,1,'2026-06',2884261,'2026-06-10 05:26:47','2026-06-11 14:14:19'),(2,2,'2026-06',1300000,'2026-06-10 05:26:47','2026-06-11 14:14:19'),(3,3,'2026-06',885313,'2026-06-10 05:26:47','2026-06-11 14:14:19'),(4,4,'2026-06',190000,'2026-06-10 05:26:47','2026-06-10 07:24:01'),(5,5,'2026-06',300806,'2026-06-10 05:26:47','2026-06-10 07:24:01'),(6,6,'2026-06',4913,'2026-06-10 05:26:47','2026-06-11 14:14:19'),(7,7,'2026-06',191379,'2026-06-10 05:26:47','2026-06-10 07:24:01'),(8,8,'2026-06',70610,'2026-06-10 05:26:47','2026-06-10 07:24:01'),(9,9,'2026-06',1336718,'2026-06-10 05:26:47','2026-06-11 14:14:19'),(10,10,'2026-06',546068,'2026-06-10 05:26:47','2026-06-11 14:54:27'),(11,11,'2026-06',0,'2026-06-10 07:05:08','2026-06-10 07:05:08');
/*!40000 ALTER TABLE `opening_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (1,'Parfum','2026-06-13 02:57:59','2026-06-13 02:57:59'),(2,'Aksesoris HP','2026-06-13 03:22:46','2026-06-13 03:22:52'),(3,'Perdana','2026-06-15 04:57:51','2026-06-15 04:57:51');
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purchase_price` int NOT NULL DEFAULT '0',
  `selling_price` int NOT NULL DEFAULT '0',
  `stock` int NOT NULL DEFAULT '0',
  `stock_min` int NOT NULL DEFAULT '0',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pcs',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Parfum Mezuca 35ml',14000,16000,1,1,'pcs',1,'2026-06-13 03:23:41','2026-06-14 04:16:21'),(2,1,'Parfum Mezuca',4000,7000,10,1,'pcs',1,'2026-06-13 04:01:21','2026-06-14 14:03:48'),(3,3,'SP Indosat 3GB',30000,35000,10,3,'pcs',1,'2026-06-15 04:58:50','2026-06-15 05:03:43'),(4,3,'SP By.U 3GB',30000,35000,5,1,'pcs',1,'2026-06-15 04:59:16','2026-06-15 04:59:42'),(5,3,'SP Tri 3GB',30000,35000,12,3,'pcs',1,'2026-06-15 05:01:00','2026-06-15 05:03:53'),(6,3,'SP XL 3GB',30000,35000,8,3,'pcs',1,'2026-06-15 05:01:31','2026-06-15 05:03:57'),(7,3,'SP Axis 3GB',30000,35000,9,3,'pcs',1,'2026-06-15 05:02:03','2026-06-15 05:04:02'),(8,3,'SP Smartfren 3GB',30000,35000,8,3,'pcs',1,'2026-06-15 05:02:47','2026-06-15 05:02:47'),(9,3,'SP Telkomsel 3GB',30000,35000,4,1,'pcs',1,'2026-06-15 05:03:12','2026-06-15 05:03:12');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receivable_payments`
--

DROP TABLE IF EXISTS `receivable_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `receivable_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `receivable_id` bigint unsigned DEFAULT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `amount` int NOT NULL,
  `date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `receivable_payments_receivable_id_foreign` (`receivable_id`),
  KEY `receivable_payments_account_id_foreign` (`account_id`),
  CONSTRAINT `receivable_payments_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `receivable_payments_receivable_id_foreign` FOREIGN KEY (`receivable_id`) REFERENCES `receivables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receivable_payments`
--

LOCK TABLES `receivable_payments` WRITE;
/*!40000 ALTER TABLE `receivable_payments` DISABLE KEYS */;
INSERT INTO `receivable_payments` VALUES (7,11,10,23000,'2026-06-13 11:18:18','2026-06-13 04:18:18','2026-06-13 04:18:18'),(8,10,10,81000,'2026-06-13 16:49:06','2026-06-13 09:49:06','2026-06-13 09:49:06'),(9,15,10,13000,'2026-06-14 14:34:49','2026-06-14 07:34:49','2026-06-14 07:34:49'),(10,19,9,186000,'2026-06-15 12:46:42','2026-06-15 05:46:42','2026-06-15 05:46:42'),(11,22,10,195000,'2026-06-15 15:26:22','2026-06-15 08:26:22','2026-06-15 08:26:22'),(12,20,10,54000,'2026-06-15 16:05:43','2026-06-15 09:05:43','2026-06-15 09:05:43');
/*!40000 ALTER TABLE `receivable_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receivables`
--

DROP TABLE IF EXISTS `receivables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `receivables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` int NOT NULL,
  `date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `status` enum('unpaid','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receivables`
--

LOCK TABLES `receivables` WRITE;
/*!40000 ALTER TABLE `receivables` DISABLE KEYS */;
INSERT INTO `receivables` VALUES (10,'NAMI','+62 857-4614-9431',81000,'2026-06-10 10:52:57','2026-06-13 10:52:57','paid','2026-06-11 02:48:00','2026-06-13 09:49:06'),(11,'Zaskia','+62 895-3233-91083',23000,'2026-06-10 11:09:37','2026-06-13 11:09:37','paid','2026-06-11 02:48:17','2026-06-13 04:18:18'),(14,'Djul','+6285335809353',24000,'2026-06-13 09:52:17','2026-06-16 09:52:17','unpaid','2026-06-13 02:52:17','2026-06-13 02:52:17'),(15,'Zaskia',NULL,13000,'2026-06-13 21:12:33','2026-06-16 21:12:33','paid','2026-06-13 14:12:33','2026-06-14 07:34:49'),(16,'Mas Kris',NULL,16000,'2026-06-13 21:12:44','2026-06-16 21:12:44','unpaid','2026-06-13 14:12:44','2026-06-13 14:12:44'),(17,'Ibuk',NULL,50000,'2026-06-13 21:36:15','2026-06-16 21:36:15','unpaid','2026-06-13 14:36:15','2026-06-13 14:36:15'),(18,'Husen',NULL,23000,'2026-06-14 10:29:17','2026-06-17 10:29:17','unpaid','2026-06-14 03:29:17','2026-06-14 03:29:17'),(19,'Tutik','+62 858-5380-6626',81000,'2026-06-14 20:59:39','2026-06-17 00:00:00','paid','2026-06-14 05:40:19','2026-06-15 05:46:42'),(20,'Umaro',NULL,54000,'2026-06-14 16:59:37','2026-06-17 00:00:00','paid','2026-06-14 09:59:37','2026-06-15 09:05:43'),(22,'Dimas',NULL,195000,'2026-06-14 15:26:14','2026-06-17 00:00:00','paid','2026-06-14 14:33:53','2026-06-15 08:26:22'),(23,'Suki',NULL,17000,'2026-06-15 09:58:17','2026-06-18 00:00:00','unpaid','2026-06-15 02:58:17','2026-06-15 02:58:17');
/*!40000 ALTER TABLE `receivables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recurring_bills`
--

DROP TABLE IF EXISTS `recurring_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recurring_bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_day` tinyint NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `recurring_bills_account_id_foreign` (`account_id`),
  CONSTRAINT `recurring_bills_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recurring_bills`
--

LOCK TABLES `recurring_bills` WRITE;
/*!40000 ALTER TABLE `recurring_bills` DISABLE KEYS */;
INSERT INTO `recurring_bills` VALUES (8,'CHATGPT','Tools',4,75000.00,20,1,'2026-06-12 01:39:36','2026-06-12 01:39:36'),(9,'PLN','LISTRIK',1,255000.00,20,1,'2026-06-12 01:40:58','2026-06-12 01:40:58'),(10,'WIFI','Internet',1,185000.00,20,1,'2026-06-12 01:41:18','2026-06-12 01:41:18'),(11,'PARSEL LEBARAN','Amal',10,167000.00,20,1,'2026-06-12 01:41:46','2026-06-13 01:52:39'),(12,'Google Photos','Tools',1,35000.00,20,1,'2026-06-12 01:42:31','2026-06-12 01:42:31');
/*!40000 ALTER TABLE `recurring_bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transactions`
--

DROP TABLE IF EXISTS `stock_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned DEFAULT NULL,
  `type` enum('in','out','opname') COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int NOT NULL,
  `price` int NOT NULL,
  `account_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `income_id` bigint unsigned DEFAULT NULL,
  `receipt_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_transactions_account_id_foreign` (`account_id`),
  KEY `stock_transactions_income_id_foreign` (`income_id`),
  KEY `stock_transactions_receipt_id_index` (`receipt_id`),
  KEY `stock_transactions_product_id_foreign` (`product_id`),
  CONSTRAINT `stock_transactions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transactions_income_id_foreign` FOREIGN KEY (`income_id`) REFERENCES `incomes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transactions`
--

LOCK TABLES `stock_transactions` WRITE;
/*!40000 ALTER TABLE `stock_transactions` DISABLE KEYS */;
INSERT INTO `stock_transactions` VALUES (5,1,'opname',5,14000,NULL,'Stok opname','2026-06-13 10:58:42','2026-06-13 03:58:42','2026-06-13 03:58:42',NULL,NULL),(6,1,'opname',1,14000,NULL,'Stok opname','2026-06-13 10:58:56','2026-06-13 03:58:56','2026-06-13 03:58:56',NULL,NULL),(19,2,'out',2,7000,10,'Penjualan Parfum Mezuca','2026-06-14 11:46:36','2026-06-14 04:46:36','2026-06-14 04:46:36',21,'INV-20260614-88D23'),(20,2,'out',1,7000,10,'Penjualan Parfum Mezuca','2026-06-14 21:03:48','2026-06-14 14:03:48','2026-06-14 14:03:48',22,'INV-20260614-3DA5C'),(21,2,'opname',10,4000,NULL,'Stok opname','2026-06-15 11:59:42','2026-06-15 04:59:42','2026-06-15 04:59:42',NULL,NULL),(22,1,'opname',1,14000,NULL,'Stok opname','2026-06-15 11:59:42','2026-06-15 04:59:42','2026-06-15 04:59:42',NULL,NULL),(23,4,'opname',5,30000,NULL,'Stok opname','2026-06-15 11:59:42','2026-06-15 04:59:42','2026-06-15 04:59:42',NULL,NULL),(24,3,'opname',10,30000,NULL,'Stok opname','2026-06-15 11:59:42','2026-06-15 04:59:42','2026-06-15 04:59:42',NULL,NULL);
/*!40000 ALTER TABLE `stock_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Admin','admin@cash-tracker.local',NULL,'$2y$12$KpmsrFA/fe6ZURMLYJn8L.NXFX/YTc/.oX0G7nd0gUOCwqlQyQxz.',NULL,NULL,'2026-06-14 03:10:37','2026-06-14 03:10:37'),(3,'karyawan','Sodron',NULL,NULL,'$2y$12$HhuEFxMq5iZtuQcUaYM7IOGWEVDT56dom0yacpcKL9fa3HLHg3TYy',NULL,'[\"dashboard\", \"pos\", \"stock_in\", \"stock_opname\"]','2026-06-14 04:14:05','2026-06-14 04:15:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-15 17:09:23
