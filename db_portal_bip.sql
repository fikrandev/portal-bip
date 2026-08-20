-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 20, 2026 at 06:49 AM
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
-- Database: `db_portal_bip`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL COMMENT 'CREATE, UPDATE, DELETE, LOGIN, LOGOUT',
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'user, role, module, siswa, etc.',
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN', 'user', 1, NULL, NULL, NULL, NULL, '2026-06-08 14:48:36'),
(2, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 14:56:22'),
(3, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:05:53'),
(4, 1, 'LOGOUT', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:12:05'),
(5, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-08 15:36:28'),
(6, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 10:27:28'),
(7, 1, 'LOGOUT', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 10:27:37'),
(8, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 10:48:10'),
(9, 1, 'UPDATE', 'settings', NULL, NULL, NULL, NULL, NULL, '2026-06-09 13:11:45'),
(10, 1, 'LOGOUT', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:12:01'),
(11, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-09 13:19:13'),
(12, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-06-10 13:53:57'),
(13, 1, 'CREATE', 'tahun_akademik', NULL, NULL, NULL, NULL, NULL, '2026-06-10 15:57:21'),
(14, 1, 'UPDATE', 'tahun_akademik', NULL, NULL, NULL, NULL, NULL, '2026-06-10 16:01:05'),
(15, 1, 'CREATE', 'tahun_akademik', NULL, NULL, NULL, NULL, NULL, '2026-06-10 16:01:31'),
(16, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-01 09:13:58'),
(17, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-08 09:05:43'),
(18, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-10 11:30:32'),
(19, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-23 11:07:47'),
(20, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-07-31 13:57:36'),
(21, 1, 'LOGIN', 'user', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-18 15:04:02');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tahun_akademik_id` int(11) DEFAULT NULL,
  `nama_kelas` varchar(100) NOT NULL,
  `wali_kelas` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_jabatan`
--

CREATE TABLE `master_jabatan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_jabatan`
--

INSERT INTO `master_jabatan` (`id`, `nama`, `created_at`) VALUES
(1, 'Wali Kelas', '2026-07-01 10:37:29'),
(2, 'Guru Mapel', '2026-07-01 10:37:33'),
(3, 'Wakakum', '2026-07-01 10:37:43'),
(4, 'Kepala Sekolah', '2026-07-01 11:08:51'),
(5, 'Kepala Divisi IT', '2026-07-01 11:09:00');

-- --------------------------------------------------------

--
-- Table structure for table `master_jenis_pegawai`
--

CREATE TABLE `master_jenis_pegawai` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_jenis_pegawai`
--

INSERT INTO `master_jenis_pegawai` (`id`, `nama`) VALUES
(1, 'Guru'),
(3, 'Support System');

-- --------------------------------------------------------

--
-- Table structure for table `master_status_kerja`
--

CREATE TABLE `master_status_kerja` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_status_kerja`
--

INSERT INTO `master_status_kerja` (`id`, `nama`) VALUES
(1, 'Tetap'),
(2, 'Kontrak'),
(4, 'Training');

-- --------------------------------------------------------

--
-- Table structure for table `master_unit_tugas`
--

CREATE TABLE `master_unit_tugas` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_unit_tugas`
--

INSERT INTO `master_unit_tugas` (`id`, `nama`, `created_at`) VALUES
(1, 'PAUD', '2026-07-01 10:37:17'),
(2, 'SD', '2026-07-01 10:37:19'),
(3, 'SMP', '2026-07-01 10:37:22'),
(4, 'SMA', '2026-07-01 10:37:25'),
(5, 'Yayasan', '2026-07-01 11:08:40');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `module_group` varchar(100) DEFAULT NULL,
  `icon_svg` text DEFAULT NULL COMMENT 'SVG icon markup',
  `color` varchar(7) NOT NULL DEFAULT '#0EA5E9' COMMENT 'HEX color for card',
  `route` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `name`, `slug`, `description`, `module_group`, `icon_svg`, `color`, `route`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Dashboard', 'dashboard', 'Halaman utama portal dan ringkasan informasi', NULL, '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z\"/></svg>', '#0EA5E9', '/dashboard', 0, 1, '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(2, 'Kelola Siswa', 'kelola-siswa', 'Manajemen data siswa, pendaftaran, dan profil siswa', 'Kesiswaan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5\"/></svg>', '#06B6D4', '/kelola-siswa', 1, 1, '2026-06-08 14:22:22', '2026-06-10 14:26:17'),
(3, 'Kelola Pegawai', 'kelola-pegawai', 'Manajemen data pegawai, jadwal mengajar, dan evaluasi', 'Kepegawaian', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z\"/></svg>', '#0284C7', '/kelola-pegawai', 2, 1, '2026-06-08 14:22:22', '2026-06-10 14:26:17'),
(4, 'Kelola Kelas', 'kelola-kelas', 'Manajemen kelas, wali kelas, dan pembagian rombel', 'Kesiswaan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z\"/></svg>', '#38BDF8', '/kelola-kelas', 3, 1, '2026-06-08 14:22:22', '2026-06-10 14:26:17'),
(5, 'Kelola Pengguna', 'kelola-pengguna', 'Manajemen akun pengguna, peran, dan hak akses', 'Pengaturan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z\"/></svg>', '#0369A1', '/users', 104, 1, '2026-06-08 14:22:22', '2026-06-10 14:53:05'),
(6, 'Kelola Peran', 'kelola-peran', 'Pengaturan peran dan hak akses pengguna (RBAC)', 'Pengaturan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z\"/></svg>', '#7DD3FC', '/roles', 105, 1, '2026-06-08 14:22:22', '2026-06-10 14:53:05'),
(7, 'Manajemen Modul', 'manajemen-modul', 'Tambah, edit, dan kelola modul-modul portal', 'Pengaturan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3\"/></svg>', '#0C4A6E', '/modules-manager', 106, 1, '2026-06-08 14:22:22', '2026-06-10 14:53:05'),
(8, 'Pengaturan Sistem', 'pengaturan-sistem', 'Konfigurasi umum aplikasi dan pemeliharaan data', 'Pengaturan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z\" /><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z\" /></svg>', '#64748B', '/pengaturan-sistem', 107, 1, '2026-06-09 11:07:05', '2026-06-10 14:53:05'),
(9, 'Kelola RPP', 'kelola-rpp', 'Manajemen Rencana Pelaksanaan Pembelajaran', 'Administrasi Guru', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25\" /></svg>', '#10B981', '/kelola-rpp', 8, 1, '2026-06-10 14:04:27', '2026-06-10 14:26:17'),
(10, 'Kelola Nilai', 'kelola-nilai', 'Manajemen nilai akademik siswa', 'Administrasi Guru', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605\" /></svg>', '#F59E0B', '/kelola-nilai', 9, 1, '2026-06-10 14:04:27', '2026-06-10 14:26:17'),
(11, 'Kelola Raport', 'kelola-raport', 'Manajemen dan pencetakan raport siswa', 'Administrasi Guru', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z\" /></svg>', '#8B5CF6', '/kelola-raport', 10, 1, '2026-06-10 14:04:27', '2026-06-10 14:26:17'),
(12, 'Kelola Absen Siswa', 'kelola-absen-siswa', 'Manajemen presensi dan kehadiran siswa', 'Administrasi Guru', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\" /></svg>', '#10B981', '/kelola-absen-siswa', 11, 1, '2026-06-10 14:35:25', '2026-06-10 14:35:25'),
(13, 'Kelola Absen Pegawai', 'kelola-absen-pegawai', 'Manajemen presensi dan kehadiran pegawai', 'Kepegawaian', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z\" /></svg>', '#0284C7', '/kelola-absen-pegawai', 12, 1, '2026-06-10 14:35:25', '2026-06-10 14:35:25'),
(14, 'Kelola Qur\'an Siswa', 'kelola-quran-siswa', 'Manajemen hafalan dan bacaan Al-Qur\'an siswa', 'Administrasi Guru', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25\" /></svg>', '#10B981', '/kelola-quran-siswa', 13, 1, '2026-06-10 14:35:25', '2026-06-10 14:35:25'),
(15, 'Kelola Ujian', 'kelola-ujian', 'Manajemen jadwal, soal, dan pelaksanaan ujian', 'Administrasi Guru', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75\" /></svg>', '#10B981', '/kelola-ujian', 14, 1, '2026-06-10 14:35:25', '2026-06-10 14:35:25'),
(16, 'Kelola Qur\'an Pegawai', 'kelola-quran-pegawai', 'Manajemen hafalan dan tahsin Al-Qur\'an pegawai', 'Kepegawaian', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25\" /></svg>', '#0284C7', '/kelola-quran-pegawai', 15, 1, '2026-06-10 14:35:25', '2026-06-10 14:35:25'),
(17, 'Kelola Cuti', 'kelola-cuti', 'Manajemen pengajuan dan persetujuan cuti pegawai', 'Kepegawaian', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z\" /></svg>', '#0284C7', '/kelola-cuti', 16, 1, '2026-06-10 14:35:25', '2026-06-10 14:35:25'),
(18, 'Kelola KPI', 'kelola-kpi', 'Manajemen Key Performance Indicator pegawai', 'Kepegawaian', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z\" /></svg>', '#0284C7', '/kelola-kpi', 17, 1, '2026-06-10 14:42:44', '2026-06-10 14:42:44'),
(19, 'Kelola Ibadah Guru', 'kelola-ibadah-guru', 'Manajemen ibadah dan amaliah guru', 'Kepegawaian', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25\" /></svg>', '#0284C7', '/kelola-ibadah-guru', 18, 1, '2026-06-10 14:46:28', '2026-06-10 14:46:28'),
(20, 'Kelola SPMB', 'kelola-spmb', 'Manajemen Seleksi Penerimaan Siswa Baru', 'Kesiswaan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z\" /></svg>', '#38BDF8', '/kelola-spmb', 19, 1, '2026-06-10 14:46:28', '2026-06-10 14:46:28'),
(21, 'Kelola Pelanggaran Siswa', 'kelola-pelanggaran-siswa', 'Manajemen catatan dan poin pelanggaran siswa', 'Kesiswaan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3Z\" /></svg>', '#38BDF8', '/kelola-pelanggaran-siswa', 20, 1, '2026-06-10 14:46:28', '2026-06-10 14:46:28'),
(22, 'Kelola Izin Guru', 'kelola-izin-guru', 'Manajemen pengajuan dan persetujuan izin guru', 'Kepegawaian', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z\" /></svg>', '#0284C7', '/kelola-izin-guru', 21, 1, '2026-06-10 14:46:28', '2026-06-10 14:46:28'),
(23, 'Kelola Buku Angkatan Siswa', 'kelola-buku-angkatan-siswa', 'Manajemen data dan profil buku angkatan siswa', 'Kesiswaan', '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25\" /></svg>', '#38BDF8', '/kelola-buku-angkatan-siswa', 22, 1, '2026-06-10 14:46:28', '2026-06-10 14:46:28');

-- --------------------------------------------------------

--
-- Table structure for table `module_permissions`
--

CREATE TABLE `module_permissions` (
  `module_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `module_permissions`
--

INSERT INTO `module_permissions` (`module_id`, `permission_id`) VALUES
(1, 1),
(2, 2),
(3, 6),
(4, 10),
(5, 14),
(6, 18),
(7, 22),
(8, 26),
(9, 26),
(10, 30),
(11, 34);

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `niy` varchar(50) DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `gelar` varchar(50) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL DEFAULT 'L',
  `status_nikah` varchar(50) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `unit_tugas` varchar(100) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `status_kerja` varchar(50) DEFAULT NULL,
  `jenis_pegawai` varchar(50) DEFAULT NULL,
  `status_dapodik` varchar(50) DEFAULT NULL,
  `tmt` date DEFAULT NULL,
  `alamat_ktp` text DEFAULT NULL,
  `kab_kota_ktp` varchar(100) DEFAULT NULL,
  `kec_ktp` varchar(100) DEFAULT NULL,
  `kel_ktp` varchar(100) DEFAULT NULL,
  `alamat_domisili` text DEFAULT NULL,
  `kab_kota_domisili` varchar(100) DEFAULT NULL,
  `kec_domisili` varchar(100) DEFAULT NULL,
  `kel_domisili` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id`, `foto`, `niy`, `nik`, `nama`, `gelar`, `jenis_kelamin`, `status_nikah`, `tempat_lahir`, `tanggal_lahir`, `nama_ibu`, `unit_tugas`, `jabatan`, `status_kerja`, `jenis_pegawai`, `status_dapodik`, `tmt`, `alamat_ktp`, `kab_kota_ktp`, `kec_ktp`, `kel_ktp`, `alamat_domisili`, `kab_kota_domisili`, `kec_domisili`, `kel_domisili`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '/public/uploads/pegawai/pegawai_1782876232_415.jpg', 'F55117002@akun', '720314161198003', 'Fikran', 'S.Kom', 'L', 'Menikah', 'Tonggolobibi', '1998-11-16', 'Nurman', 'Yayasan', 'Kepala Divisi IT', 'Tetap', 'Support System', 'Sudah Masuk', '2021-02-01', 'BTN Green Gawalise Mandiri no 303', 'Palu', 'Tatanga', 'Duyu', 'BTN Green Gawalise Mandiri no 303', 'Palu', 'Tatanga', 'Duyu', 1, '2026-07-01 11:23:52', '2026-07-01 11:23:52');

-- --------------------------------------------------------

--
-- Table structure for table `pegawai_pendidikan`
--

CREATE TABLE `pegawai_pendidikan` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pegawai_id` bigint(20) UNSIGNED NOT NULL,
  `jenjang` varchar(50) NOT NULL,
  `institusi` varchar(150) NOT NULL,
  `jurusan` varchar(100) DEFAULT NULL,
  `tahun_lulus` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pegawai_pendidikan`
--

INSERT INTO `pegawai_pendidikan` (`id`, `pegawai_id`, `jenjang`, `institusi`, `jurusan`, `tahun_lulus`) VALUES
(13, 1, 'SD', 'SDN Inti Tonggolobibi', '-', '2010'),
(14, 1, 'SMP', 'MTs Negeri 1 Palu', '-', '2014'),
(15, 1, 'SMA', 'MAN 1 Palu', 'IPA', '2017'),
(16, 1, 'S1', 'Universitas Tadulako', 'Teknologi Informasi', '2024');

-- --------------------------------------------------------

--
-- Table structure for table `pegawai_penugasan`
--

CREATE TABLE `pegawai_penugasan` (
  `id` int(11) NOT NULL,
  `pegawai_id` bigint(20) UNSIGNED NOT NULL,
  `no_sk` varchar(100) NOT NULL,
  `tanggal_sk` date NOT NULL,
  `unit_tugas_id` int(11) NOT NULL,
  `jabatan_id` int(11) NOT NULL,
  `tmt_mulai` date NOT NULL,
  `tst_selesai` date DEFAULT NULL,
  `file_sk` varchar(255) DEFAULT NULL,
  `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `module_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `module_id`, `created_at`) VALUES
(1, 'Lihat Dashboard', 'dashboard.view', 'Dapat mengakses halaman dashboard', 1, '2026-06-08 14:22:22'),
(2, 'Lihat Siswa', 'siswa.view', 'Dapat melihat daftar dan detail siswa', 2, '2026-06-08 14:22:22'),
(3, 'Tambah Siswa', 'siswa.create', 'Dapat menambahkan data siswa baru', 2, '2026-06-08 14:22:22'),
(4, 'Edit Siswa', 'siswa.update', 'Dapat mengubah data siswa', 2, '2026-06-08 14:22:22'),
(5, 'Hapus Siswa', 'siswa.delete', 'Dapat menghapus data siswa', 2, '2026-06-08 14:22:22'),
(6, 'Lihat Pegawai', 'pegawai.view', 'Dapat melihat daftar dan detail pegawai', 3, '2026-06-08 14:22:22'),
(7, 'Tambah Pegawai', 'pegawai.create', 'Dapat menambahkan data pegawai baru', 3, '2026-06-08 14:22:22'),
(8, 'Edit Pegawai', 'pegawai.update', 'Dapat mengubah data pegawai', 3, '2026-06-08 14:22:22'),
(9, 'Hapus Pegawai', 'pegawai.delete', 'Dapat menghapus data pegawai', 3, '2026-06-08 14:22:22'),
(10, 'Lihat Kelas', 'kelas.view', 'Dapat melihat daftar kelas', 4, '2026-06-08 14:22:22'),
(11, 'Tambah Kelas', 'kelas.create', 'Dapat membuat kelas baru', 4, '2026-06-08 14:22:22'),
(12, 'Edit Kelas', 'kelas.update', 'Dapat mengubah data kelas', 4, '2026-06-08 14:22:22'),
(13, 'Hapus Kelas', 'kelas.delete', 'Dapat menghapus kelas', 4, '2026-06-08 14:22:22'),
(14, 'Lihat Pengguna', 'users.view', 'Dapat melihat daftar pengguna', 5, '2026-06-08 14:22:22'),
(15, 'Tambah Pengguna', 'users.create', 'Dapat menambahkan pengguna baru', 5, '2026-06-08 14:22:22'),
(16, 'Edit Pengguna', 'users.update', 'Dapat mengubah data pengguna', 5, '2026-06-08 14:22:22'),
(17, 'Hapus Pengguna', 'users.delete', 'Dapat menghapus pengguna', 5, '2026-06-08 14:22:22'),
(18, 'Lihat Peran', 'roles.view', 'Dapat melihat daftar peran', 6, '2026-06-08 14:22:22'),
(19, 'Tambah Peran', 'roles.create', 'Dapat membuat peran baru', 6, '2026-06-08 14:22:22'),
(20, 'Edit Peran', 'roles.update', 'Dapat mengubah peran dan hak akses', 6, '2026-06-08 14:22:22'),
(21, 'Hapus Peran', 'roles.delete', 'Dapat menghapus peran', 6, '2026-06-08 14:22:22'),
(22, 'Lihat Modul', 'modules.view', 'Dapat melihat daftar modul', 7, '2026-06-08 14:22:22'),
(23, 'Tambah Modul', 'modules.create', 'Dapat menambahkan modul baru', 7, '2026-06-08 14:22:22'),
(24, 'Edit Modul', 'modules.update', 'Dapat mengubah konfigurasi modul', 7, '2026-06-08 14:22:22'),
(25, 'Hapus Modul', 'modules.delete', 'Dapat menghapus modul', 7, '2026-06-08 14:22:22'),
(26, 'Lihat Pengaturan', 'settings.view', 'Dapat melihat pengaturan sistem', 8, '2026-06-09 11:07:05'),
(27, 'Ubah Pengaturan', 'settings.update', 'Dapat mengubah pengaturan sistem', 8, '2026-06-09 11:07:05'),
(28, 'Reset Data', 'settings.reset', 'Dapat melakukan reset data operasional', 8, '2026-06-09 11:07:05'),
(29, 'Hapus RPP', 'rpp.delete', 'Dapat menghapus RPP', 9, '2026-06-10 14:04:27'),
(30, 'Lihat Nilai', 'nilai.view', 'Dapat melihat daftar nilai', 10, '2026-06-10 14:04:27'),
(31, 'Tambah Nilai', 'nilai.create', 'Dapat menambahkan nilai baru', 10, '2026-06-10 14:04:27'),
(32, 'Edit Nilai', 'nilai.update', 'Dapat mengubah nilai', 10, '2026-06-10 14:04:27'),
(33, 'Hapus Nilai', 'nilai.delete', 'Dapat menghapus nilai', 10, '2026-06-10 14:04:27'),
(34, 'Lihat Raport', 'raport.view', 'Dapat melihat daftar raport', 11, '2026-06-10 14:04:27'),
(35, 'Tambah Raport', 'raport.create', 'Dapat menambahkan raport baru', 11, '2026-06-10 14:04:27'),
(36, 'Edit Raport', 'raport.update', 'Dapat mengubah raport', 11, '2026-06-10 14:04:27'),
(37, 'Hapus Raport', 'raport.delete', 'Dapat menghapus raport', 11, '2026-06-10 14:04:27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'System roles cannot be deleted',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `created_at`) VALUES
(1, 'Super Admin', 'super_admin', 'Akses penuh ke semua fitur dan pengaturan sistem', 1, '2026-06-08 14:22:22'),
(2, 'Admin', 'admin', 'Mengelola modul-modul tertentu yang ditugaskan', 1, '2026-06-08 14:22:22'),
(3, 'Operator', 'operator', 'Melakukan input dan pengelolaan data operasional', 1, '2026-06-08 14:22:22'),
(4, 'Viewer', 'viewer', 'Hanya dapat melihat data tanpa melakukan perubahan', 1, '2026-06-08 14:22:22');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `granted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `granted_at`) VALUES
(1, 1, '2026-06-08 14:22:22'),
(1, 2, '2026-06-08 14:22:22'),
(1, 3, '2026-06-08 14:22:22'),
(1, 4, '2026-06-08 14:22:22'),
(1, 5, '2026-06-08 14:22:22'),
(1, 6, '2026-06-08 14:22:22'),
(1, 7, '2026-06-08 14:22:22'),
(1, 8, '2026-06-08 14:22:22'),
(1, 9, '2026-06-08 14:22:22'),
(1, 10, '2026-06-08 14:22:22'),
(1, 11, '2026-06-08 14:22:22'),
(1, 12, '2026-06-08 14:22:22'),
(1, 13, '2026-06-08 14:22:22'),
(1, 14, '2026-06-08 14:22:22'),
(1, 15, '2026-06-08 14:22:22'),
(1, 16, '2026-06-08 14:22:22'),
(1, 17, '2026-06-08 14:22:22'),
(1, 18, '2026-06-08 14:22:22'),
(1, 19, '2026-06-08 14:22:22'),
(1, 20, '2026-06-08 14:22:22'),
(1, 21, '2026-06-08 14:22:22'),
(1, 22, '2026-06-08 14:22:22'),
(1, 23, '2026-06-08 14:22:22'),
(1, 24, '2026-06-08 14:22:22'),
(1, 25, '2026-06-08 14:22:22'),
(1, 26, '2026-06-09 11:07:05'),
(1, 27, '2026-06-09 11:07:05'),
(1, 28, '2026-06-09 11:07:05'),
(1, 29, '2026-06-10 14:04:27'),
(1, 30, '2026-06-10 14:04:27'),
(1, 31, '2026-06-10 14:04:27'),
(1, 32, '2026-06-10 14:04:27'),
(1, 33, '2026-06-10 14:04:27'),
(1, 34, '2026-06-10 14:04:27'),
(1, 35, '2026-06-10 14:04:27'),
(1, 36, '2026-06-10 14:04:27'),
(1, 37, '2026-06-10 14:04:27'),
(2, 1, '2026-06-08 14:22:22'),
(2, 2, '2026-06-08 14:22:22'),
(2, 3, '2026-06-08 14:22:22'),
(2, 4, '2026-06-08 14:22:22'),
(2, 6, '2026-06-08 14:22:22'),
(2, 7, '2026-06-08 14:22:22'),
(2, 8, '2026-06-08 14:22:22'),
(2, 10, '2026-06-08 14:22:22'),
(2, 11, '2026-06-08 14:22:22'),
(2, 12, '2026-06-08 14:22:22'),
(2, 14, '2026-06-08 14:22:22'),
(2, 15, '2026-06-08 14:22:22'),
(2, 16, '2026-06-08 14:22:22'),
(2, 26, '2026-06-10 14:04:27'),
(2, 27, '2026-06-10 14:04:27'),
(2, 28, '2026-06-10 14:04:27'),
(2, 30, '2026-06-10 14:04:27'),
(2, 31, '2026-06-10 14:04:27'),
(2, 32, '2026-06-10 14:04:27'),
(2, 34, '2026-06-10 14:04:27'),
(2, 35, '2026-06-10 14:04:27'),
(2, 36, '2026-06-10 14:04:27'),
(3, 1, '2026-06-08 14:22:22'),
(3, 2, '2026-06-08 14:22:22'),
(3, 3, '2026-06-08 14:22:22'),
(3, 4, '2026-06-08 14:22:22'),
(3, 6, '2026-06-08 14:22:22'),
(3, 7, '2026-06-08 14:22:22'),
(3, 8, '2026-06-08 14:22:22'),
(3, 10, '2026-06-08 14:22:22'),
(3, 11, '2026-06-08 14:22:22'),
(3, 12, '2026-06-08 14:22:22'),
(3, 26, '2026-06-10 14:04:27'),
(3, 27, '2026-06-10 14:04:27'),
(3, 28, '2026-06-10 14:04:27'),
(3, 30, '2026-06-10 14:04:27'),
(3, 31, '2026-06-10 14:04:27'),
(3, 32, '2026-06-10 14:04:27'),
(3, 34, '2026-06-10 14:04:27'),
(3, 35, '2026-06-10 14:04:27'),
(3, 36, '2026-06-10 14:04:27'),
(4, 1, '2026-06-08 14:22:22'),
(4, 2, '2026-06-08 14:22:22'),
(4, 6, '2026-06-08 14:22:22'),
(4, 10, '2026-06-08 14:22:22'),
(4, 26, '2026-06-10 14:04:27'),
(4, 30, '2026-06-10 14:04:27'),
(4, 34, '2026-06-10 14:04:27');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(20) NOT NULL DEFAULT 'string' COMMENT 'string, integer, boolean, json',
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `setting_group`, `created_at`, `updated_at`) VALUES
(1, 'app_name', 'Portal BIP', 'string', 'general', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(2, 'app_description', 'Portal Manajemen Informasi Terpadu', 'string', 'general', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(3, 'app_version', '1.0.0', 'string', 'general', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(4, 'app_logo', '/public/uploads/settings/logo_1780981905.png', 'string', 'general', '2026-06-08 14:22:22', '2026-06-09 13:11:45'),
(5, 'max_login_attempts', '5', 'integer', 'security', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(6, 'lockout_duration', '15', 'integer', 'security', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(7, 'session_lifetime', '120', 'integer', 'security', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(8, 'pagination_limit', '15', 'integer', 'display', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(9, 'date_format', 'd/m/Y', 'string', 'display', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(10, 'timezone', 'Asia/Makassar', 'string', 'general', '2026-06-08 14:22:22', '2026-06-08 14:22:22'),
(12, 'app_favicon', '/public/uploads/settings/favicon_1780981905.ico', 'string', 'general', '2026-06-09 11:07:05', '2026-06-09 13:11:45');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL DEFAULT 'L',
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tahun_akademik`
--

CREATE TABLE `tahun_akademik` (
  `id` int(11) NOT NULL,
  `nama_tahun` varchar(100) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tahun_akademik`
--

INSERT INTO `tahun_akademik` (`id`, `nama_tahun`, `tanggal_mulai`, `tanggal_selesai`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '2026/2027 Ganjil', '2026-07-01', '2026-12-31', 1, '2026-06-10 07:57:21', '2026-06-10 08:01:04'),
(2, '2026/2027 Genap', '2027-01-01', '2027-06-30', 0, '2026-06-10 08:01:31', '2026-06-10 08:01:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `full_name`, `email`, `username`, `password`, `avatar`, `phone`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, '689a8441-6302-11f1-9a01-90e868c7d910', 'Super Administrator', 'admin@portal-bip.com', 'admin', '$2y$10$SXEp05nqpV3l/YrJZyi/c.NcazhNzzpv230.PumAyIPhpvPNrsoqa', NULL, NULL, 1, '2026-08-18 15:04:02', '2026-06-08 14:22:22', '2026-08-18 15:04:02');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`, `assigned_by`) VALUES
(1, 1, '2026-06-08 14:22:22', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kelas_tahun` (`tahun_akademik_id`);

--
-- Indexes for table `master_jabatan`
--
ALTER TABLE `master_jabatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_jenis_pegawai`
--
ALTER TABLE `master_jenis_pegawai`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_status_kerja`
--
ALTER TABLE `master_status_kerja`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_unit_tugas`
--
ALTER TABLE `master_unit_tugas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_modules_slug` (`slug`),
  ADD KEY `idx_modules_active_sort` (`is_active`,`sort_order`);

--
-- Indexes for table `module_permissions`
--
ALTER TABLE `module_permissions`
  ADD PRIMARY KEY (`module_id`,`permission_id`),
  ADD KEY `idx_mp_permission` (`permission_id`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pegawai_pendidikan`
--
ALTER TABLE `pegawai_pendidikan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_peg_pend_pegawai` (`pegawai_id`);

--
-- Indexes for table `pegawai_penugasan`
--
ALTER TABLE `pegawai_penugasan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pegawai_id` (`pegawai_id`),
  ADD KEY `unit_tugas_id` (`unit_tugas_id`),
  ADD KEY `jabatan_id` (`jabatan_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_permissions_slug` (`slug`),
  ADD KEY `idx_permissions_module` (`module_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_roles_slug` (`slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `idx_rp_permission` (`permission_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_settings_key` (`setting_key`),
  ADD KEY `idx_settings_group` (`setting_group`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_siswa_nis` (`nis`),
  ADD KEY `idx_siswa_kelas` (`kelas`),
  ADD KEY `idx_siswa_nama` (`nama`);

--
-- Indexes for table `tahun_akademik`
--
ALTER TABLE `tahun_akademik`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_users_uuid` (`uuid`),
  ADD UNIQUE KEY `uk_users_email` (`email`),
  ADD UNIQUE KEY `uk_users_username` (`username`),
  ADD KEY `idx_users_active` (`is_active`),
  ADD KEY `idx_users_created` (`created_at`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `idx_ur_role` (`role_id`),
  ADD KEY `fk_ur_assigned_by` (`assigned_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_jabatan`
--
ALTER TABLE `master_jabatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `master_jenis_pegawai`
--
ALTER TABLE `master_jenis_pegawai`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `master_status_kerja`
--
ALTER TABLE `master_status_kerja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `master_unit_tugas`
--
ALTER TABLE `master_unit_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pegawai_pendidikan`
--
ALTER TABLE `pegawai_pendidikan`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pegawai_penugasan`
--
ALTER TABLE `pegawai_penugasan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tahun_akademik`
--
ALTER TABLE `tahun_akademik`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `fk_kelas_tahun` FOREIGN KEY (`tahun_akademik_id`) REFERENCES `tahun_akademik` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `module_permissions`
--
ALTER TABLE `module_permissions`
  ADD CONSTRAINT `fk_mp_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pegawai_pendidikan`
--
ALTER TABLE `pegawai_pendidikan`
  ADD CONSTRAINT `fk_pegawai_pendidikan` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pegawai_penugasan`
--
ALTER TABLE `pegawai_penugasan`
  ADD CONSTRAINT `fk_penugasan_jabatan` FOREIGN KEY (`jabatan_id`) REFERENCES `master_jabatan` (`id`),
  ADD CONSTRAINT `fk_penugasan_pegawai` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penugasan_unit` FOREIGN KEY (`unit_tugas_id`) REFERENCES `master_unit_tugas` (`id`);

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `fk_permissions_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_ur_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
