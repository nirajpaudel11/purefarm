-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2025 at 12:29 PM
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
-- Database: `shopping`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `creationDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `updationDate` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `creationDate`, `updationDate`) VALUES
(1, 'admin', '63a9f0ea7bb98050796b649e85481845', '2024-04-02 16:21:18', '03-05-2024 08:27:55 PM');

-- --------------------------------------------------------

--
-- Table structure for table `bundles`
--

CREATE TABLE `bundles` (
  `id` int(11) NOT NULL,
  `items` text DEFAULT NULL,
  `discount_percent` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bundles`
--

INSERT INTO `bundles` (`id`, `items`, `discount_percent`, `created_at`) VALUES
(1, 'Rato dal, Spinach', 10, '2025-05-24 18:41:08');

-- --------------------------------------------------------

--
-- Table structure for table `bundle_suggestions`
--

CREATE TABLE `bundle_suggestions` (
  `id` int(11) NOT NULL,
  `base_product_id` int(11) DEFAULT NULL,
  `suggested_product_ids` text DEFAULT NULL,
  `support_count` int(11) DEFAULT NULL,
  `confidence` float DEFAULT NULL,
  `lift` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `categoryName` varchar(255) DEFAULT NULL,
  `categoryDescription` longtext DEFAULT NULL,
  `creationDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `updationDate` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `categoryName`, `categoryDescription`, `creationDate`, `updationDate`) VALUES
(12, 'Dairy Products', 'dairy related products', '2025-05-22 14:47:15', NULL),
(13, 'Pulses', 'Organic Pulses', '2025-05-24 08:53:17', NULL),
(14, 'Vegetables', 'Fresh organic vegetables from farm', '2025-05-24 12:07:51', NULL),
(15, 'Farm products', 'Pure farm products', '2025-05-25 03:24:44', NULL),
(16, 'Pickles', 'Fresh pickles', '2025-05-26 03:11:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `frequent_itemsets`
--

CREATE TABLE `frequent_itemsets` (
  `id` int(11) NOT NULL,
  `itemset` text DEFAULT NULL,
  `support_count` int(11) DEFAULT NULL,
  `itemset_size` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `productId` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `orderDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `paymentMethod` varchar(50) DEFAULT NULL,
  `orderStatus` varchar(55) DEFAULT NULL,
  `order_token` varchar(6) NOT NULL,
  `payment_ss` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `userId`, `productId`, `quantity`, `orderDate`, `paymentMethod`, `orderStatus`, `order_token`, `payment_ss`) VALUES
(61, 6, '31', 1, '2024-12-24 09:41:07', 'Khalti Wallet', NULL, '', NULL),
(62, 6, '31', 1, '2024-12-24 14:10:21', 'Khalti Wallet', NULL, '', NULL),
(63, 6, '32', 1, '2024-12-24 17:16:54', 'Khalti Wallet', NULL, '', NULL),
(64, 6, '32', 1, '2024-12-24 17:35:09', 'Khalti Wallet', NULL, '', NULL),
(65, 6, '31', 1, '2024-12-24 18:30:25', 'Khalti Wallet', NULL, '', NULL),
(66, 7, '32', 1, '2024-12-24 19:19:35', 'Khalti Wallet', NULL, '', NULL),
(67, 6, '32', 1, '2024-12-24 19:19:52', 'Khalti Wallet', NULL, '', NULL),
(68, 7, '33', 1, '2024-12-24 19:52:41', 'Khalti Wallet', NULL, '', NULL),
(69, 6, '32', 1, '2024-12-24 20:03:18', 'Khalti Wallet', NULL, '', NULL),
(70, 6, '32', 1, '2024-12-24 20:23:23', 'Khalti Wallet', NULL, '', NULL),
(71, 7, '32', 1, '2024-12-24 20:31:39', 'Khalti Wallet', NULL, '', NULL),
(75, 7, '32', 1, '2024-12-25 06:56:21', 'Khalti Wallet', NULL, '', NULL),
(76, 7, '32', 1, '2024-12-25 07:00:09', 'Khalti Wallet', NULL, '', NULL),
(77, 7, '32', 1, '2024-12-25 07:12:43', 'Khalti Wallet', NULL, '', NULL),
(78, 7, '31', 1, '2024-12-25 07:47:21', 'Khalti Wallet', NULL, '', NULL),
(82, 7, '32', 1, '2024-12-28 08:25:18', NULL, NULL, '32igu6', NULL),
(85, 6, '34', 1, '2024-12-29 04:24:32', 'Khalti Wallet', NULL, 'kcUPwk', NULL),
(86, 6, '34', 1, '2024-12-29 04:24:42', 'Khalti Wallet', NULL, '42qG5S', NULL),
(87, 6, '34', 1, '2024-12-29 04:25:38', 'Khalti Wallet', NULL, 'k7gvtc', NULL),
(88, 6, '34', 1, '2024-12-29 04:35:34', 'Khalti Wallet', NULL, '', NULL),
(89, 6, '34', 1, '2024-12-29 04:44:49', 'QR Payment', NULL, 'IAe2dt', NULL),
(90, 6, '34', 1, '2024-12-29 04:45:00', 'QR Payment', NULL, 'IAe2dt', NULL),
(91, 6, '36', 1, '2024-12-29 04:59:34', 'QR Payment', NULL, 'vWD60n', NULL),
(92, 1, '32', 1, '2024-12-29 06:54:02', 'QR Payment', NULL, 'O7VH0o', NULL),
(93, 1, '36', 1, '2024-12-29 07:21:15', 'QR Payment', NULL, 'fo57GX', ''),
(94, 1, '35', 1, '2024-12-29 07:26:01', 'QR Payment', NULL, 'bZkjgv', 'fa85ba218460a343980ce4b16d8c2a'),
(95, 1, '32', 1, '2024-12-29 08:28:07', 'QR Payment', NULL, 'QajDKj', 'e91d6aa3e140b29e81d9c63f13fa24'),
(96, 1, '32', 1, '2024-12-29 08:50:07', 'QR Payment', NULL, 'FzqVHc', '87927b6fca147246e56f236ca1d241'),
(97, 6, '31', 1, '2024-12-29 09:02:47', 'QR Payment', NULL, 'WAL84X', '7157e5452e155b1f642220e3a8838d'),
(98, 6, '32', 1, '2024-12-29 09:04:29', 'QR Payment', NULL, 'vyJGK4', 'ba96edc64df5efc288b8a0be91382d'),
(100, 6, '32', 1, '2024-12-29 09:28:39', 'QR Payment', NULL, 'b2s1Le', '050ff0729175d1e3c5aa3d6a170eb41a.jpg'),
(101, 7, '36', 1, '2024-12-29 09:41:09', NULL, NULL, '8jGva4', NULL),
(102, 7, '34', 1, '2024-12-29 09:49:55', 'QR Payment', 'Delivered', 'q4VZBQ', '44db2f12177d4bf575c85b4b8bd4019f.jpg'),
(103, 7, '32', 1, '2024-12-29 10:06:59', NULL, NULL, 'ZoZsrD', NULL),
(104, 7, '32', 1, '2024-12-29 10:08:33', NULL, NULL, 'ZoZsrD', NULL),
(105, 7, '32', 1, '2024-12-29 10:09:01', NULL, NULL, 'ZoZsrD', NULL),
(106, 6, '34', 1, '2024-12-29 10:10:15', 'Khalti Wallet', NULL, 'DfHsUe', NULL),
(107, 7, '32', 1, '2024-12-29 10:11:08', NULL, NULL, 'jZGaRc', NULL),
(109, 6, '39', 1, '2025-05-24 12:11:34', 'Khalti Wallet', NULL, 'vMS7QY', NULL),
(110, 6, '39', 1, '2025-05-24 14:52:12', 'khalti_wallet', NULL, 'YlCFrf', NULL),
(111, 6, '38', 1, '2025-05-24 14:52:38', 'khalti_wallet', NULL, 'YlCFrf', NULL),
(112, 6, '39', 1, '2025-05-24 14:52:38', 'khalti_wallet', NULL, 'YlCFrf', NULL),
(113, 6, '39', 1, '2025-05-24 14:53:53', 'Khalti Wallet', NULL, 'YlCFrf', NULL),
(114, 8, '38', 1, '2025-05-25 01:59:54', 'Khalti Wallet', NULL, 'Oq1zLO', NULL),
(115, 9, '40', 1, '2025-05-26 03:05:59', NULL, NULL, 'FvyTsw', NULL),
(116, 8, '39', 0, '2025-05-26 05:55:34', 'Khalti Wallet', NULL, 'b3aRjR', NULL),
(117, 9, '39', 4, '2025-05-26 05:56:56', NULL, NULL, 'yw5iIT', NULL),
(118, 9, '39', 4, '2025-05-26 05:57:35', NULL, NULL, 'yw5iIT', NULL),
(119, 8, '39', 0, '2025-05-26 06:58:59', 'Khalti Wallet', NULL, 'ROKtc4', NULL),
(120, 8, '39', 0, '2025-05-26 07:06:36', 'Khalti Wallet', NULL, 'ROKtc4', NULL),
(121, 8, '38', 1, '2025-05-26 07:23:53', 'khalti_wallet', NULL, 'bmBtjn', NULL),
(122, 8, '38', 1, '2025-05-26 07:55:42', 'khalti_wallet', NULL, 'bmBtjn', NULL),
(123, 8, '38', 1, '2025-05-26 07:58:35', 'khalti_wallet', NULL, 'Q22384', NULL),
(124, 8, '38', 1, '2025-05-26 07:59:12', 'khalti_wallet', NULL, 'Q22384', NULL),
(125, 8, '38', 1, '2025-05-26 07:59:50', 'khalti_wallet', NULL, 'Q22384', NULL),
(126, 8, '38', 1, '2025-05-26 08:20:43', 'khalti_wallet', NULL, 'Q22384', NULL),
(127, 8, '38', 1, '2025-05-26 09:35:48', 'khalti_wallet', NULL, 'pbxV8T', NULL),
(128, 8, '38', 1, '2025-05-26 13:01:45', 'khalti_wallet', NULL, 'atBlr0', NULL),
(129, 8, '50', 1, '2025-05-26 13:01:45', 'khalti_wallet', NULL, 'atBlr0', NULL),
(130, 8, '38', 1, '2025-05-26 15:08:30', NULL, NULL, 'atBlr0', NULL),
(131, 8, '38', 1, '2025-05-26 15:23:28', NULL, NULL, '3Yx1F5', NULL),
(132, 8, '38', 1, '2025-05-26 15:24:19', NULL, NULL, '3Yx1F5', NULL),
(133, 8, '43', 1, '2025-05-26 15:43:33', NULL, NULL, 'PETCWQ', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ordertrackhistory`
--

CREATE TABLE `ordertrackhistory` (
  `id` int(11) NOT NULL,
  `orderId` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `remark` mediumtext DEFAULT NULL,
  `postingDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `ordertrackhistory`
--

INSERT INTO `ordertrackhistory` (`id`, `orderId`, `status`, `remark`, `postingDate`) VALUES
(13, 102, 'Delivered', 'Spotify@mail.com\r\nPassword123', '2024-12-29 09:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `productreviews`
--

CREATE TABLE `productreviews` (
  `id` int(11) NOT NULL,
  `productId` int(11) DEFAULT NULL,
  `quality` int(11) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `review` longtext DEFAULT NULL,
  `reviewDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `productQuantity` int(10) DEFAULT NULL,
  `subCategory` int(11) DEFAULT NULL,
  `productName` varchar(255) DEFAULT NULL,
  `productCompany` varchar(255) DEFAULT NULL,
  `productPrice` int(11) DEFAULT NULL,
  `productPriceBeforeDiscount` int(11) DEFAULT NULL,
  `productDescription` longtext DEFAULT NULL,
  `productImage1` varchar(255) DEFAULT NULL,
  `productImage2` varchar(255) DEFAULT NULL,
  `productImage3` varchar(255) DEFAULT NULL,
  `shippingCharge` int(11) DEFAULT NULL,
  `productAvailability` varchar(255) DEFAULT NULL,
  `postingDate` timestamp NULL DEFAULT current_timestamp(),
  `updationDate` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category`, `productQuantity`, `subCategory`, `productName`, `productCompany`, `productPrice`, `productPriceBeforeDiscount`, `productDescription`, `productImage1`, `productImage2`, `productImage3`, `shippingCharge`, `productAvailability`, `postingDate`, `updationDate`) VALUES
(38, 13, 50, 29, 'Rato dal', 'local', 180, 180, 'Organic dal from terai', 'masoor-dal-red-lentils.jpg', 'masoor-dal-red-lentils.jpg', 'masoor-dal-red-lentils.jpg', 20, 'In Stock', '2025-05-24 09:08:12', NULL),
(39, 14, 50, 30, 'Spinach', 'local', 100, 100, '<br>', 'Spinach.png', 'Spinach.png', 'Spinach.png', 0, 'In Stock', '2025-05-24 12:10:02', NULL),
(40, 15, 50, 31, 'Chicken Egg', 'local', 480, 480, 'Pure egg', 'egg.png', 'egg.png', 'egg.png', 20, 'In Stock', '2025-05-25 03:27:07', NULL),
(41, 12, 50, 32, 'Cow milk', 'local', 50, 50, 'Fresh Milk', 'milk.jpg', 'milk.jpg', 'milk.jpg', 20, 'In Stock', '2025-05-25 03:29:26', NULL),
(42, 12, NULL, 28, 'kulfi', 'local', 50, 50, 'test', 'kulfi.png', 'kulfi.png', 'kulfi.png', 0, 'In Stock', '2025-05-26 06:54:23', NULL),
(43, 13, 50, 34, 'Kaalo Dal', 'local', 200, 200, 'Great lentils', 'kalo dal.jpg', 'kalo dal.jpg', 'kalo dal.jpg', 20, 'In Stock', '2025-05-26 11:51:53', NULL),
(44, 12, NULL, 32, 'Buffalo Milk', 'local', 70, 70, 'Fresh Buffalo MIlk', 'burraduc-buffalo-milk.jpg', 'burraduc-buffalo-milk.jpg', 'burraduc-buffalo-milk.jpg', 30, 'In Stock', '2025-05-26 11:54:12', NULL),
(45, 14, NULL, 30, 'Mustard Spinach', 'local', 100, 100, 'Fresh and organic spinach', 'mustard spinach.png', 'mustard spinach.png', 'mustard spinach.png', 30, 'In Stock', '2025-05-26 12:00:10', NULL),
(48, 12, NULL, 36, 'Juju Dhau', 'local', 100, 100, '					', 'Juju dhau.png', 'Juju dhau.png', 'Juju dhau.png', 0, 'In Stock', '2025-05-26 12:37:44', NULL),
(49, 12, 20, 36, 'Juju Dhau', 'local', 100, 100, '					', 'Juju dhau.png', 'Juju dhau.png', 'Juju dhau.png', 0, 'In Stock', '2025-05-26 12:43:52', NULL),
(50, 16, 30, 33, 'Mango pickle', 'local', 240, 250, 'Organic pickle', 'Pickle.png', 'Pickle.png', 'Pickle.png', 20, 'In Stock', '2025-05-26 12:46:03', NULL),
(51, 16, 30, 33, 'Mango pickle', 'local', 240, 250, 'Organic pickle', 'Pickle.png', 'Pickle.png', 'Pickle.png', 20, 'In Stock', '2025-05-26 15:12:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subcategory`
--

CREATE TABLE `subcategory` (
  `id` int(11) NOT NULL,
  `categoryid` int(11) DEFAULT NULL,
  `subcategory` varchar(255) DEFAULT NULL,
  `creationDate` timestamp NULL DEFAULT current_timestamp(),
  `updationDate` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategory`
--

INSERT INTO `subcategory` (`id`, `categoryid`, `subcategory`, `creationDate`, `updationDate`) VALUES
(28, 12, 'chocolate', '2025-05-22 14:48:19', NULL),
(29, 13, 'Masur(red) pulses', '2025-05-24 08:55:08', '24-05-2025 02:25:25 PM'),
(30, 14, 'Spinach', '2025-05-24 12:08:18', NULL),
(31, 15, 'Egg', '2025-05-25 03:24:57', NULL),
(32, 12, 'Milk', '2025-05-25 03:27:44', NULL),
(33, 16, 'Dry pickles', '2025-05-26 03:11:51', NULL),
(34, 13, 'Kaalo (Maas) Dal', '2025-05-26 11:49:56', NULL),
(36, 12, 'Curd', '2025-05-26 12:27:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `userlog`
--

CREATE TABLE `userlog` (
  `id` int(11) NOT NULL,
  `userEmail` varchar(255) DEFAULT NULL,
  `userip` binary(16) DEFAULT NULL,
  `loginTime` timestamp NULL DEFAULT current_timestamp(),
  `logout` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `userlog`
--

INSERT INTO `userlog` (`id`, `userEmail`, `userip`, `loginTime`, `logout`, `status`) VALUES
(23, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-20 22:50:01', '21-12-2024 11:05:22 AM', 1),
(24, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-21 05:35:53', NULL, 1),
(25, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-21 07:58:23', '21-12-2024 06:56:08 PM', 1),
(26, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-21 14:22:54', '21-12-2024 08:32:03 PM', 1),
(27, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-21 15:02:18', '21-12-2024 08:33:47 PM', 1),
(28, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-21 16:38:12', '22-12-2024 09:13:06 AM', 1),
(29, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-22 03:46:54', NULL, 1),
(30, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-24 09:40:45', NULL, 0),
(31, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-24 09:40:52', NULL, 0),
(32, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-24 09:40:59', NULL, 1),
(33, 'kshitishbhurtel@tuicms.edu.np', 0x3a3a3100000000000000000000000000, '2024-12-24 17:16:31', NULL, 0),
(34, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-24 17:16:39', NULL, 1),
(35, 'bhurtelxitish223@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-24 17:35:01', NULL, 1),
(36, 'bhurtelxitish223@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-24 18:30:05', '25-12-2024 01:20:30 AM', 1),
(37, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-24 19:16:56', NULL, 1),
(38, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-24 19:45:20', '25-12-2024 01:16:26 AM', 1),
(39, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-24 19:46:37', NULL, 1),
(40, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-24 19:50:42', NULL, 1),
(41, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-24 20:31:35', NULL, 1),
(42, 'dhakalrakshya2233@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-25 06:56:18', NULL, 1),
(43, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-28 06:59:27', '28-12-2024 12:44:45 PM', 1),
(44, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-28 07:47:03', '29-12-2024 09:28:46 AM', 1),
(45, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-28 08:25:13', NULL, 1),
(46, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 04:01:11', NULL, 0),
(47, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 04:01:19', NULL, 0),
(48, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 04:01:28', '29-12-2024 09:31:45 AM', 1),
(49, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 04:02:14', '29-12-2024 09:35:30 AM', 1),
(50, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 04:05:47', '29-12-2024 02:22:58 PM', 1),
(51, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 08:53:10', NULL, 0),
(52, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 08:53:19', '29-12-2024 02:24:44 PM', 1),
(53, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 08:54:55', '29-12-2024 02:27:04 PM', 1),
(54, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 08:57:14', NULL, 0),
(55, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 08:57:29', '29-12-2024 02:34:05 PM', 1),
(56, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 09:04:22', '29-12-2024 02:58:13 PM', 1),
(57, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 09:28:30', NULL, 1),
(58, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2024-12-29 09:39:20', NULL, 1),
(59, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-29 09:40:59', NULL, 1),
(60, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-29 09:49:42', '29-12-2024 03:22:23 PM', 1),
(61, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-29 09:56:19', NULL, 1),
(62, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-29 10:06:49', '29-12-2024 03:40:34 PM', 1),
(63, 'dhakalrakshya2233@gmail.com', 0x3139322e3136382e312e363600000000, '2024-12-29 10:10:46', NULL, 1),
(64, 'hemantasharma223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-22 14:55:57', NULL, 0),
(65, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-22 14:56:12', NULL, 0),
(66, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-22 14:56:33', NULL, 0),
(67, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-22 14:56:40', NULL, 1),
(68, 'hemantasharma223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-24 12:10:43', NULL, 0),
(69, 'hemantasharma223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-24 12:10:48', NULL, 0),
(70, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-24 12:10:54', NULL, 0),
(71, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-24 12:11:26', NULL, 1),
(72, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-24 14:52:03', NULL, 1),
(73, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-24 16:39:18', '25-05-2025 12:02:23 AM', 1),
(74, 'bhurtelxitish223@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-24 18:49:13', '25-05-2025 07:25:42 AM', 1),
(75, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-25 01:51:08', '25-05-2025 07:22:31 AM', 1),
(76, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-25 01:56:08', NULL, 1),
(77, '', 0x3a3a3100000000000000000000000000, '2025-05-26 02:58:16', NULL, 0),
(78, 'neerajpaudel1@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 03:00:08', '26-05-2025 08:31:34 AM', 1),
(79, 'neerajpaudel1@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 03:02:13', '26-05-2025 08:40:00 AM', 1),
(80, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 05:55:01', NULL, 0),
(81, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 05:55:18', '26-05-2025 11:25:50 AM', 1),
(82, 'neerajpaudel1@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 05:56:18', '26-05-2025 11:28:30 AM', 1),
(83, 'neerajpaudel1@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 05:58:54', NULL, 0),
(84, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 05:59:02', '26-05-2025 11:49:48 AM', 1),
(85, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:09:13', '26-05-2025 11:54:16 AM', 1),
(86, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:09:51', '26-05-2025 11:55:02 AM', 1),
(87, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:10:21', '26-05-2025 11:55:57 AM', 1),
(88, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:12:02', '26-05-2025 11:57:37 AM', 1),
(89, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:14:08', '26-05-2025 11:59:11 AM', 1),
(90, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:15:41', '26-05-2025 12:00:45 PM', 1),
(91, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:16:17', '26-05-2025 12:01:19 PM', 1),
(92, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:16:49', '26-05-2025 12:01:52 PM', 1),
(93, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:24:29', NULL, 0),
(94, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 06:24:35', '26-05-2025 01:42:12 PM', 1),
(95, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 07:57:29', '26-05-2025 02:28:55 PM', 1),
(96, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 08:54:12', '26-05-2025 02:40:40 PM', 1),
(97, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 09:05:05', '26-05-2025 02:50:10 PM', 1),
(98, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 09:08:49', '26-05-2025 02:56:25 PM', 1),
(99, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 09:35:43', '26-05-2025 05:32:28 PM', 1),
(100, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 12:39:45', '26-05-2025 09:01:34 PM', 1),
(101, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 15:16:44', '26-05-2025 09:27:50 PM', 1),
(102, 'neerajpaudel11@gmail.com', 0x3a3a3100000000000000000000000000, '2025-05-26 15:43:29', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contactno` bigint(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `shippingAddress` longtext DEFAULT NULL,
  `shippingState` varchar(255) DEFAULT NULL,
  `shippingCity` varchar(255) DEFAULT NULL,
  `shippingPincode` int(11) DEFAULT NULL,
  `regDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `updationDate` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `contactno`, `password`, `shippingAddress`, `shippingState`, `shippingCity`, `shippingPincode`, `regDate`, `updationDate`) VALUES
(6, 'Kshitish Bhurtel', 'bhurtelxitish223@gmail.com', 9869837027, '21232f297a57a5a743894a0e4a801fc3', 'Kawasoti, Nawalpur East', 'Gandaki provience', 'Kawasoti', 33000, '2024-12-17 12:04:27', '21-12-2024 12:15:01 AM'),
(7, 'Rakshya Dhakal', 'dhakalrakshya2233@gmail.com', 9840007600, '63a9f0ea7bb98050796b649e85481845', 'Kathmandu ', 'Bagmati', 'Satungal ', 8500, '2024-12-24 19:16:48', NULL),
(8, 'Niraj Paudel', 'neerajpaudel11@gmail.com', 9865419581, '63a9f0ea7bb98050796b649e85481845', 'Koteshwor', 'Bagmati Province', 'Kathmandu', 33000, '2025-05-25 01:50:49', NULL),
(9, 'Niraj Paudel', 'neerajpaudel1@gmail.com', 9865419581, 'e10adc3949ba59abbe56e057f20f883e', 'hkjhkj', 'ghkjghkj', 'hkjhk', 0, '2025-05-26 02:59:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `productId` int(11) DEFAULT NULL,
  `postingDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `userId`, `productId`, `postingDate`) VALUES
(3, 6, 33, '2024-12-28 07:47:09'),
(6, 8, 40, '2025-05-26 11:45:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bundles`
--
ALTER TABLE `bundles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bundle_suggestions`
--
ALTER TABLE `bundle_suggestions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `frequent_itemsets`
--
ALTER TABLE `frequent_itemsets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ordertrackhistory`
--
ALTER TABLE `ordertrackhistory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productreviews`
--
ALTER TABLE `productreviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subcategory`
--
ALTER TABLE `subcategory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `userlog`
--
ALTER TABLE `userlog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bundles`
--
ALTER TABLE `bundles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bundle_suggestions`
--
ALTER TABLE `bundle_suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `frequent_itemsets`
--
ALTER TABLE `frequent_itemsets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `ordertrackhistory`
--
ALTER TABLE `ordertrackhistory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `productreviews`
--
ALTER TABLE `productreviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `subcategory`
--
ALTER TABLE `subcategory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `userlog`
--
ALTER TABLE `userlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
