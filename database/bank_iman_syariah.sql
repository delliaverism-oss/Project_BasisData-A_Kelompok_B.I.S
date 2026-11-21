-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 02:46 PM
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
-- Database: `bank_iman_syariah`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nomor_rekening` varchar(30) NOT NULL,
  `jenis_akad` enum('Wadiah','Mudharabah') NOT NULL,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `status` enum('Aktif','Nonaktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `user_id`, `nomor_rekening`, `jenis_akad`, `saldo`, `status`) VALUES
(1, 1, '1234567890', 'Wadiah', 13950000.00, 'Aktif'),
(2, 1, '8064889756', 'Mudharabah', 500000.00, 'Aktif'),
(3, 4, '3788324677', 'Wadiah', 1400000.00, 'Aktif'),
(4, 5, '8976790504', 'Wadiah', 10560000.00, 'Aktif'),
(5, 5, '4027993741', 'Mudharabah', 0.00, 'Nonaktif'),
(6, 5, '4810999496', 'Mudharabah', 0.00, 'Nonaktif'),
(7, 5, '1302365230', 'Mudharabah', 0.00, 'Nonaktif'),
(8, 5, '4206145912', 'Wadiah', 0.00, 'Nonaktif'),
(9, 5, '2999194398', 'Mudharabah', 0.00, 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `account_applications`
--

CREATE TABLE `account_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_akad` enum('Wadiah','Mudharabah') NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu Persetujuan',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_applications`
--

INSERT INTO `account_applications` (`id`, `user_id`, `jenis_akad`, `status`, `tanggal_pengajuan`) VALUES
(1, 5, 'Mudharabah', 'Ditolak', '2026-11-14 10:09:46'),
(2, 5, 'Mudharabah', 'Ditolak', '2026-11-14 10:49:22'),
(3, 5, 'Mudharabah', 'Disetujui', '2026-11-14 10:52:21'),
(4, 5, 'Mudharabah', 'Disetujui', '2026-11-18 05:42:06'),
(5, 5, 'Wadiah', 'Disetujui', '2026-11-18 06:23:46'),
(6, 5, 'Mudharabah', 'Disetujui', '2026-11-18 08:02:41');

-- --------------------------------------------------------

--
-- Table structure for table `account_closure_applications`
--

CREATE TABLE `account_closure_applications` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `alasan` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu Persetujuan',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_closure_applications`
--

INSERT INTO `account_closure_applications` (`id`, `account_id`, `alasan`, `status`, `tanggal_pengajuan`) VALUES
(1, 5, '', 'Disetujui', '2026-11-14 10:40:45'),
(2, 6, '', 'Disetujui', '2026-11-14 10:53:53'),
(3, 7, '', 'Disetujui', '2026-11-18 05:58:16'),
(4, 8, '', 'Disetujui', '2026-11-18 07:30:52');

-- --------------------------------------------------------

--
-- Table structure for table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `user_id`, `token_hash`, `expires_at`, `created_at`) VALUES
(1, 1, '9bd22e9ec201af6abb220c319a0c00fa00ea153c8b7aaf70487f2a0be105e4ca', '2026-11-03 10:28:40', '2026-11-02 09:28:40'),
(2, 1, '1d1799446306f80b8be6ff8fc35cb1a8e9900b7f95bb5835c6cc57ec34d39cbc', '2026-11-01 16:54:01', '2026-11-02 09:40:04'),
(3, 1, '072bf988be8041d08f93ffd137a27c60caeefe319f1d2aefd2c3faca570dda1d', '2026-11-03 10:57:00', '2026-11-02 09:57:00');

-- --------------------------------------------------------

--
-- Table structure for table `financings`
--

CREATE TABLE `financings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_akad` varchar(50) DEFAULT NULL,
  `plafon` decimal(15,2) DEFAULT NULL,
  `margin` decimal(5,2) DEFAULT NULL,
  `tenor` int(11) DEFAULT NULL,
  `angsuran` decimal(15,2) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financings`
--

INSERT INTO `financings` (`id`, `user_id`, `jenis_akad`, `plafon`, `margin`, `tenor`, `angsuran`, `status`) VALUES
(1, 1, 'Murabahah', 50000000.00, 8.00, 24, 2416667.00, 'Aktif'),
(2, 5, 'Murabahah', 5000000.00, 8.00, 12, 450000.00, 'Lunas'),
(3, 5, 'Murabahah', 1000000.00, 8.00, 12, 90000.00, 'Lunas'),
(4, 4, 'Murabahah', 1000000.00, 8.00, 12, 90000.00, 'Aktif'),
(5, 5, 'Murabahah', 1000000.00, 8.00, 12, 90000.00, 'Lunas'),
(6, 5, 'Murabahah', 1000000.00, 8.00, 12, 90000.00, 'Lunas'),
(7, 5, 'Murabahah', 1000000.00, 8.00, 12, 90000.00, 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `financing_applications`
--

CREATE TABLE `financing_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `jenis_akad` varchar(50) NOT NULL,
  `plafon` decimal(15,2) NOT NULL,
  `tenor` int(11) NOT NULL,
  `margin` decimal(5,2) NOT NULL DEFAULT 8.00,
  `estimasi_angsuran` decimal(15,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Menunggu Persetujuan',
  `tanggal_pengajuan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financing_applications`
--

INSERT INTO `financing_applications` (`id`, `user_id`, `account_id`, `jenis_akad`, `plafon`, `tenor`, `margin`, `estimasi_angsuran`, `status`, `tanggal_pengajuan`) VALUES
(1, 1, NULL, 'Mudharabah', 3000000.00, 12, 8.00, 270000.00, 'Ditolak', '2026-11-04 05:33:26'),
(2, 5, NULL, 'Murabahah', 5000000.00, 12, 8.00, 450000.00, 'Disetujui', '2026-11-11 10:57:35'),
(3, 5, NULL, 'Murabahah', 1000000.00, 12, 8.00, 90000.00, 'Disetujui', '2026-11-13 10:07:32'),
(4, 4, 3, 'Murabahah', 1000000.00, 12, 8.00, 90000.00, 'Disetujui', '2026-11-13 10:23:50'),
(5, 5, 4, 'Murabahah', 1000000.00, 12, 8.00, 90000.00, 'Disetujui', '2026-11-14 09:16:20'),
(6, 5, 4, 'Murabahah', 1000000.00, 12, 8.00, 90000.00, 'Ditolak', '2026-11-14 09:46:00'),
(7, 5, 4, 'Murabahah', 1000000.00, 12, 8.00, 90000.00, 'Disetujui', '2026-11-14 09:49:21'),
(8, 5, 4, 'Murabahah', 1000000.00, 12, 8.00, 90000.00, 'Disetujui', '2026-11-18 07:32:04');

-- --------------------------------------------------------

--
-- Table structure for table `financing_payments`
--

CREATE TABLE `financing_payments` (
  `id` int(11) NOT NULL,
  `financing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `angsuran_ke` int(11) NOT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `tanggal_bayar` datetime DEFAULT current_timestamp(),
  `transaction_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financing_payments`
--

INSERT INTO `financing_payments` (`id`, `financing_id`, `user_id`, `account_id`, `angsuran_ke`, `nominal`, `tanggal_bayar`, `transaction_id`) VALUES
(1, 2, 5, 4, 1, 450000.00, '2026-11-13 15:46:10', 23),
(2, 2, 5, 4, 2, 450000.00, '2026-11-13 15:49:31', 25),
(3, 2, 5, 4, 3, 450000.00, '2026-11-13 15:50:00', 26),
(4, 2, 5, 4, 4, 450000.00, '2026-11-13 15:56:05', 27),
(5, 2, 5, 4, 5, 450000.00, '2026-11-13 16:09:54', 28),
(6, 2, 5, 4, 6, 450000.00, '2026-11-13 16:10:00', 29),
(7, 2, 5, 4, 7, 450000.00, '2026-11-13 16:10:06', 30),
(8, 2, 5, 4, 8, 450000.00, '2026-11-13 16:10:12', 31),
(9, 2, 5, 4, 9, 450000.00, '2026-11-13 16:10:22', 32),
(10, 2, 5, 4, 10, 450000.00, '2026-11-13 16:10:29', 33),
(11, 2, 5, 4, 11, 450000.00, '2026-11-13 16:10:36', 34),
(12, 2, 5, 4, 12, 450000.00, '2026-11-13 16:10:43', 35),
(13, 3, 5, 4, 1, 90000.00, '2026-11-13 17:09:07', 36),
(14, 3, 5, 4, 2, 90000.00, '2026-11-13 17:22:51', 37),
(15, 3, 5, 4, 3, 90000.00, '2026-11-13 17:22:56', 38),
(16, 3, 5, 4, 4, 90000.00, '2026-11-13 17:23:00', 39),
(17, 3, 5, 4, 5, 90000.00, '2026-11-13 17:23:05', 40),
(18, 3, 5, 4, 6, 90000.00, '2026-11-13 17:23:09', 41),
(19, 3, 5, 4, 7, 90000.00, '2026-11-14 00:28:55', 48),
(20, 3, 5, 4, 8, 90000.00, '2026-11-14 00:28:59', 49),
(21, 3, 5, 4, 9, 90000.00, '2026-11-14 00:29:05', 50),
(22, 3, 5, 4, 10, 90000.00, '2026-11-14 00:29:36', 51),
(23, 3, 5, 4, 11, 90000.00, '2026-11-14 00:29:41', 52),
(24, 3, 5, 4, 12, 90000.00, '2026-11-14 00:29:50', 53),
(25, 5, 5, 4, 1, 90000.00, '2026-11-14 16:45:06', 55),
(26, 5, 5, 4, 2, 90000.00, '2026-11-14 16:45:10', 56),
(27, 5, 5, 4, 3, 90000.00, '2026-11-14 16:45:13', 57),
(28, 5, 5, 4, 4, 90000.00, '2026-11-14 16:45:17', 58),
(29, 5, 5, 4, 5, 90000.00, '2026-11-14 16:45:20', 59),
(30, 5, 5, 4, 6, 90000.00, '2026-11-14 16:45:24', 60),
(31, 5, 5, 4, 7, 90000.00, '2026-11-14 16:45:27', 61),
(32, 5, 5, 4, 8, 90000.00, '2026-11-14 16:45:30', 62),
(33, 5, 5, 4, 9, 90000.00, '2026-11-14 16:45:36', 63),
(34, 5, 5, 4, 10, 90000.00, '2026-11-14 16:45:39', 64),
(35, 5, 5, 4, 11, 90000.00, '2026-11-14 16:45:43', 65),
(36, 5, 5, 4, 12, 90000.00, '2026-11-14 16:45:47', 66),
(37, 6, 5, 4, 1, 90000.00, '2026-11-14 18:03:46', 68),
(38, 6, 5, 4, 2, 90000.00, '2026-11-14 18:03:53', 69),
(39, 6, 5, 4, 3, 90000.00, '2026-11-14 18:04:01', 70),
(40, 6, 5, 4, 4, 90000.00, '2026-11-14 18:04:06', 71),
(41, 6, 5, 4, 5, 90000.00, '2026-11-18 14:31:16', 72),
(42, 6, 5, 4, 6, 90000.00, '2026-11-18 14:31:20', 73),
(43, 6, 5, 4, 7, 90000.00, '2026-11-18 14:31:25', 74),
(44, 6, 5, 4, 8, 90000.00, '2026-11-18 14:31:31', 75),
(45, 6, 5, 4, 9, 90000.00, '2026-11-18 14:31:35', 76),
(46, 6, 5, 4, 10, 90000.00, '2026-11-18 14:31:39', 77),
(47, 6, 5, 4, 11, 90000.00, '2026-11-18 14:31:44', 78),
(48, 6, 5, 4, 12, 90000.00, '2026-11-18 14:31:49', 79);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `rekening_lawan_id` int(11) DEFAULT NULL,
  `kode_transaksi` varchar(30) DEFAULT NULL,
  `referensi_transfer` varchar(30) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `nominal` decimal(15,2) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `account_id`, `rekening_lawan_id`, `kode_transaksi`, `referensi_transfer`, `keterangan`, `jenis`, `nominal`, `tanggal`) VALUES
(1, 1, NULL, 'TRX-001', NULL, 'Setoran gaji Oktober', 'masuk', 8500000.00, '2026-10-27 17:00:00'),
(2, 1, NULL, 'TRX-002', NULL, 'Pembayaran zakat penghasilan', 'keluar', 212500.00, '2026-10-24 17:00:00'),
(3, 1, NULL, 'TRX-003', NULL, 'Angsuran Murabahah', 'keluar', 2416667.00, '2026-10-20 17:00:00'),
(4, 1, NULL, 'TRX-004', NULL, 'Transfer ke Siti Rahma', 'keluar', 500000.00, '2026-10-14 17:00:00'),
(5, 1, NULL, 'TRX-005', NULL, 'Tarik tunai ATM', 'keluar', 1000000.00, '2026-10-01 17:00:00'),
(6, 1, NULL, 'TRX-006', NULL, 'Bagi hasil Mudharabah', 'masuk', 45000.00, '2026-09-30 17:00:00'),
(7, 1, NULL, 'STR20261103132237356', NULL, 'Setor tunai', 'masuk', 500000.00, '2026-11-03 12:22:37'),
(8, 1, NULL, 'TRK20261103132828701', NULL, 'Tarik tunai', 'keluar', 5000000.00, '2026-11-03 12:28:28'),
(15, 4, NULL, 'ST20261109091733699', NULL, 'Setor tunai oleh admin', 'masuk', 1000000.00, '2026-11-09 08:17:33'),
(16, 4, NULL, 'ST20261109091802911', NULL, 'setoran tunai', 'masuk', 1000000.00, '2026-11-09 08:18:02'),
(17, 4, 3, 'OUT20261109165414696', 'TRF20261109165414996', 'pembayaran kuliah', 'keluar', 500000.00, '2026-11-09 15:54:14'),
(18, 3, 4, 'IN20261109165414773', 'TRF20261109165414996', 'pembayaran kuliah', 'masuk', 500000.00, '2026-11-09 15:54:14'),
(19, 4, NULL, 'TRK20261113091744298', NULL, 'Tarik tunai', 'keluar', 100000.00, '2026-11-13 08:17:44'),
(20, 4, 3, 'OUT20261113091847659', 'TRF20261113091847317', 'pembayaran sekolah', 'keluar', 200000.00, '2026-11-13 08:18:47'),
(21, 3, 4, 'IN20261113091847987', 'TRF20261113091847317', 'pembayaran sekolah', 'masuk', 200000.00, '2026-11-13 08:18:47'),
(22, 3, NULL, 'STR20261113091925437', NULL, 'Setor tunai', 'masuk', 200000.00, '2026-11-13 08:19:25'),
(23, 4, NULL, 'ANG20261113094610280', NULL, 'Pembayaran angsuran ke-1', 'keluar', 450000.00, '2026-11-13 08:46:10'),
(24, 4, NULL, 'STR20261113094917258', NULL, 'Setor tunai', 'masuk', 10000000.00, '2026-11-13 08:49:17'),
(25, 4, NULL, 'ANG20261113094931575', NULL, 'Pembayaran angsuran ke-2', 'keluar', 450000.00, '2026-11-13 08:49:31'),
(26, 4, NULL, 'ANG20261113095000223', NULL, 'Pembayaran angsuran ke-3', 'keluar', 450000.00, '2026-11-13 08:50:00'),
(27, 4, NULL, 'ANG20261113095605663', NULL, 'Pembayaran angsuran ke-4', 'keluar', 450000.00, '2026-11-13 08:56:05'),
(28, 4, NULL, 'ANG20261113100954879', NULL, 'Pembayaran angsuran ke-5', 'keluar', 450000.00, '2026-11-13 09:09:54'),
(29, 4, NULL, 'ANG20261113101000456', NULL, 'Pembayaran angsuran ke-6', 'keluar', 450000.00, '2026-11-13 09:10:00'),
(30, 4, NULL, 'ANG20261113101006274', NULL, 'Pembayaran angsuran ke-7', 'keluar', 450000.00, '2026-11-13 09:10:06'),
(31, 4, NULL, 'ANG20261113101012237', NULL, 'Pembayaran angsuran ke-8', 'keluar', 450000.00, '2026-11-13 09:10:12'),
(32, 4, NULL, 'ANG20261113101022534', NULL, 'Pembayaran angsuran ke-9', 'keluar', 450000.00, '2026-11-13 09:10:22'),
(33, 4, NULL, 'ANG20261113101029610', NULL, 'Pembayaran angsuran ke-10', 'keluar', 450000.00, '2026-11-13 09:10:29'),
(34, 4, NULL, 'ANG20261113101036748', NULL, 'Pembayaran angsuran ke-11', 'keluar', 450000.00, '2026-11-13 09:10:36'),
(35, 4, NULL, 'ANG20261113101043275', NULL, 'Pembayaran angsuran ke-12', 'keluar', 450000.00, '2026-11-13 09:10:43'),
(36, 4, NULL, 'ANG20261113110907723', NULL, 'Pembayaran angsuran ke-1', 'keluar', 90000.00, '2026-11-13 10:09:07'),
(37, 4, NULL, 'ANG20261113112251347', NULL, 'Pembayaran angsuran ke-2', 'keluar', 90000.00, '2026-11-13 10:22:51'),
(38, 4, NULL, 'ANG20261113112256648', NULL, 'Pembayaran angsuran ke-3', 'keluar', 90000.00, '2026-11-13 10:22:56'),
(39, 4, NULL, 'ANG20261113112300558', NULL, 'Pembayaran angsuran ke-4', 'keluar', 90000.00, '2026-11-13 10:23:00'),
(40, 4, NULL, 'ANG20261113112305649', NULL, 'Pembayaran angsuran ke-5', 'keluar', 90000.00, '2026-11-13 10:23:05'),
(41, 4, NULL, 'ANG20261113112309271', NULL, 'Pembayaran angsuran ke-6', 'keluar', 90000.00, '2026-11-13 10:23:09'),
(42, 3, NULL, 'PBY-20261113112434-5132', NULL, 'Pencairan pembiayaan Murabahah', 'masuk', 1000000.00, '2026-11-13 10:24:34'),
(43, 3, 2, 'OUT20261113112936939', 'TRF20261113112936415', 'pembayaran spp', 'keluar', 500000.00, '2026-11-13 10:29:36'),
(44, 2, 3, 'IN20261113112936449', 'TRF20261113112936415', 'pembayaran spp', 'masuk', 500000.00, '2026-11-13 10:29:36'),
(45, 4, NULL, 'TRK20261113182749919', NULL, 'Tarik tunai', 'keluar', 1000000.00, '2026-11-13 17:27:49'),
(46, 4, NULL, 'STR20261113182819339', NULL, 'Setor tunai', 'masuk', 1000000.00, '2026-11-13 17:28:19'),
(47, 4, NULL, 'STR20261113182843761', NULL, 'Setor tunai', 'masuk', 5000000.00, '2026-11-13 17:28:43'),
(48, 4, NULL, 'ANG20261113182855138', NULL, 'Pembayaran angsuran ke-7', 'keluar', 90000.00, '2026-11-13 17:28:55'),
(49, 4, NULL, 'ANG20261113182859405', NULL, 'Pembayaran angsuran ke-8', 'keluar', 90000.00, '2026-11-13 17:28:59'),
(50, 4, NULL, 'ANG20261113182905125', NULL, 'Pembayaran angsuran ke-9', 'keluar', 90000.00, '2026-11-13 17:29:05'),
(51, 4, NULL, 'ANG20261113182936154', NULL, 'Pembayaran angsuran ke-10', 'keluar', 90000.00, '2026-11-13 17:29:36'),
(52, 4, NULL, 'ANG20261113182941332', NULL, 'Pembayaran angsuran ke-11', 'keluar', 90000.00, '2026-11-13 17:29:41'),
(53, 4, NULL, 'ANG20261113182950515', NULL, 'Pembayaran angsuran ke-12', 'keluar', 90000.00, '2026-11-13 17:29:50'),
(54, 4, NULL, 'PBY-20261114101710-6068', NULL, 'Pencairan pembiayaan Murabahah', 'masuk', 1000000.00, '2026-11-14 09:17:10'),
(55, 4, NULL, 'ANG20261114104506315', NULL, 'Pembayaran angsuran ke-1', 'keluar', 90000.00, '2026-11-14 09:45:06'),
(56, 4, NULL, 'ANG20261114104510338', NULL, 'Pembayaran angsuran ke-2', 'keluar', 90000.00, '2026-11-14 09:45:10'),
(57, 4, NULL, 'ANG20261114104513721', NULL, 'Pembayaran angsuran ke-3', 'keluar', 90000.00, '2026-11-14 09:45:13'),
(58, 4, NULL, 'ANG20261114104517904', NULL, 'Pembayaran angsuran ke-4', 'keluar', 90000.00, '2026-11-14 09:45:17'),
(59, 4, NULL, 'ANG20261114104520869', NULL, 'Pembayaran angsuran ke-5', 'keluar', 90000.00, '2026-11-14 09:45:20'),
(60, 4, NULL, 'ANG20261114104524686', NULL, 'Pembayaran angsuran ke-6', 'keluar', 90000.00, '2026-11-14 09:45:24'),
(61, 4, NULL, 'ANG20261114104527513', NULL, 'Pembayaran angsuran ke-7', 'keluar', 90000.00, '2026-11-14 09:45:27'),
(62, 4, NULL, 'ANG20261114104530950', NULL, 'Pembayaran angsuran ke-8', 'keluar', 90000.00, '2026-11-14 09:45:30'),
(63, 4, NULL, 'ANG20261114104536706', NULL, 'Pembayaran angsuran ke-9', 'keluar', 90000.00, '2026-11-14 09:45:36'),
(64, 4, NULL, 'ANG20261114104539452', NULL, 'Pembayaran angsuran ke-10', 'keluar', 90000.00, '2026-11-14 09:45:39'),
(65, 4, NULL, 'ANG20261114104543568', NULL, 'Pembayaran angsuran ke-11', 'keluar', 90000.00, '2026-11-14 09:45:43'),
(66, 4, NULL, 'ANG20261114104547953', NULL, 'Pembayaran angsuran ke-12', 'keluar', 90000.00, '2026-11-14 09:45:47'),
(67, 4, NULL, 'PBY-20261114105026-8300', NULL, 'Pencairan pembiayaan Murabahah', 'masuk', 1000000.00, '2026-11-14 09:50:26'),
(68, 4, NULL, 'ANG20261114120346987', NULL, 'Pembayaran angsuran ke-1', 'keluar', 90000.00, '2026-11-14 11:03:46'),
(69, 4, NULL, 'ANG20261114120353276', NULL, 'Pembayaran angsuran ke-2', 'keluar', 90000.00, '2026-11-14 11:03:53'),
(70, 4, NULL, 'ANG20261114120401857', NULL, 'Pembayaran angsuran ke-3', 'keluar', 90000.00, '2026-11-14 11:04:01'),
(71, 4, NULL, 'ANG20261114120406330', NULL, 'Pembayaran angsuran ke-4', 'keluar', 90000.00, '2026-11-14 11:04:06'),
(72, 4, NULL, 'ANG20261118083116254', NULL, 'Pembayaran angsuran ke-5', 'keluar', 90000.00, '2026-11-18 07:31:16'),
(73, 4, NULL, 'ANG20261118083120616', NULL, 'Pembayaran angsuran ke-6', 'keluar', 90000.00, '2026-11-18 07:31:20'),
(74, 4, NULL, 'ANG20261118083125870', NULL, 'Pembayaran angsuran ke-7', 'keluar', 90000.00, '2026-11-18 07:31:25'),
(75, 4, NULL, 'ANG20261118083131576', NULL, 'Pembayaran angsuran ke-8', 'keluar', 90000.00, '2026-11-18 07:31:31'),
(76, 4, NULL, 'ANG20261118083135385', NULL, 'Pembayaran angsuran ke-9', 'keluar', 90000.00, '2026-11-18 07:31:35'),
(77, 4, NULL, 'ANG20261118083139115', NULL, 'Pembayaran angsuran ke-10', 'keluar', 90000.00, '2026-11-18 07:31:39'),
(78, 4, NULL, 'ANG20261118083144887', NULL, 'Pembayaran angsuran ke-11', 'keluar', 90000.00, '2026-11-18 07:31:44'),
(79, 4, NULL, 'ANG20261118083149753', NULL, 'Pembayaran angsuran ke-12', 'keluar', 90000.00, '2026-11-18 07:31:49'),
(80, 4, NULL, 'PBY-20261118083237-2031', NULL, 'Pencairan pembiayaan Murabahah', 'masuk', 1000000.00, '2026-11-18 07:32:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `nik` char(16) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_profil` varchar(255) DEFAULT NULL,
  `role` enum('admin','nasabah') NOT NULL DEFAULT 'nasabah',
  `pin_transaksi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `nik`, `username`, `password`, `no_hp`, `alamat`, `created_at`, `foto_profil`, `role`, `pin_transaksi`) VALUES
(1, 'rian', NULL, NULL, 'rian', '$2y$10$ae2tj.95L93jnVwlkq/WTeCQMRfZivmlv867csBDArSNuwDjLK0z6', '081234567890', 'Depok', '2026-11-01 15:07:33', 'assets/uploads/profiles/6aea9e40f0bd535c6ceb8774f855010c.jpg', 'admin', NULL),
(3, 'Muhammad Rava', NULL, NULL, 'Rava', '$2y$10$tGgHG6KN/PVtUnYo7XO58uuO2.ODFZ1fdVNbvVomJ42VX3ztkHwiq', '087823095613', 'Citayam,Kp.Pabuaran, JL Departemen Agama NO 23', '2026-11-09 06:38:43', NULL, 'nasabah', NULL),
(4, 'Muhammad Ryza', NULL, NULL, 'Ryza', '$2y$10$iwQzec5XzZHqM7Ha3PKjmej2IhG3W7QtrbB1boSBqCw249IvnXV5m', '087823095613', 'Citayam, Kp Pabuarana, JL Departemen Agama', '2026-11-09 06:46:38', NULL, 'nasabah', '$2y$10$ebFdAy1q4shFmdkbTZqwH.mrn7RD0lL0JJs5p0h9vAY8OzPcLfbqe'),
(5, 'Muhammad Faishal', 'riannnnn4525047@univpancasila.ac.id', '3041837388369914', 'Faishal', '$2y$10$0yCUx4cU.yP4a.W2qJlFG.rbY2P49hrhhnjFNWXwW8wLpQuaZstHK', '087823095613', 'Citayam, Kp Pabuaran, BojongGede, JL Departemen Agama', '2026-11-09 06:54:15', 'assets/uploads/profiles/7ba55380d3d9f19126a613af2f33ebbe.jpg', 'nasabah', '$2y$10$N/5UgVs4h1grx0YO.6RtMOwcRxv4bEg8.JyC3BbxcwCTnbccPMBYS');

-- --------------------------------------------------------

--
-- Table structure for table `zakat_payments`
--

CREATE TABLE `zakat_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_zakat` varchar(100) DEFAULT NULL,
  `nominal` decimal(15,2) DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Lunas',
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zakat_payments`
--

INSERT INTO `zakat_payments` (`id`, `user_id`, `jenis_zakat`, `nominal`, `status`, `tanggal`) VALUES
(1, 1, 'Zakat Penghasilan', 212500.00, 'Berhasil', '2026-10-25'),
(2, 1, 'Zakat Penghasilan', 212500.00, 'Berhasil', '2026-09-25'),
(3, 1, 'Zakat Penghasilan', 212500.00, 'Berhasil', '2026-08-25'),
(4, 1, 'Zakat Penghasilan', 212500.00, 'Berhasil', '2026-07-25'),
(5, 1, 'Zakat Penghasilan', 200000.00, 'Berhasil', '2026-06-25'),
(6, 1, 'Zakat Penghasilan', 200000.00, 'Berhasil', '2026-05-25'),
(7, 1, 'Zakat Penghasilan', 50000.00, 'Berhasil', '2026-11-01'),
(8, 1, 'Zakat Penghasilan', 2125000.00, 'Berhasil', '2026-11-02'),
(9, 1, 'Zakat Fitrah', 21250.00, 'Berhasil', '2026-11-04'),
(10, 5, 'Zakat Penghasilan', 12500.00, 'Berhasil', '2026-11-11'),
(11, 5, 'Zakat Penghasilan', 100000.00, 'Berhasil', '2026-11-13'),
(12, 4, 'Zakat Maal', 12500.00, 'Berhasil', '2026-11-13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_rekening` (`nomor_rekening`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `account_applications`
--
ALTER TABLE `account_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `account_closure_applications`
--
ALTER TABLE `account_closure_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `financings`
--
ALTER TABLE `financings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `financing_applications`
--
ALTER TABLE `financing_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financing_payments`
--
ALTER TABLE `financing_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unik_angsuran` (`financing_id`,`angsuran_ke`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `idx_referensi_transfer` (`referensi_transfer`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_nik` (`nik`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `zakat_payments`
--
ALTER TABLE `zakat_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `account_applications`
--
ALTER TABLE `account_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `account_closure_applications`
--
ALTER TABLE `account_closure_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `financings`
--
ALTER TABLE `financings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `financing_applications`
--
ALTER TABLE `financing_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `financing_payments`
--
ALTER TABLE `financing_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zakat_payments`
--
ALTER TABLE `zakat_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `account_applications`
--
ALTER TABLE `account_applications`
  ADD CONSTRAINT `account_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `account_closure_applications`
--
ALTER TABLE `account_closure_applications`
  ADD CONSTRAINT `account_closure_applications_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

--
-- Constraints for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `api_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `financings`
--
ALTER TABLE `financings`
  ADD CONSTRAINT `financings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`);

--
-- Constraints for table `zakat_payments`
--
ALTER TABLE `zakat_payments`
  ADD CONSTRAINT `zakat_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
