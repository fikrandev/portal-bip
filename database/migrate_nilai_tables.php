<?php
define('BASE_PATH', realpath(__DIR__ . '/..'));
require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS `nilai_group` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `unit` varchar(50) NOT NULL,
        `tahun_akademik_id` int(11) NOT NULL,
        `semester` varchar(20) NOT NULL,
        `kelas_id` bigint(20) UNSIGNED NOT NULL,
        `mata_pelajaran` varchar(150) NOT NULL,
        `jenis_penilaian` varchar(100) NOT NULL,
        `guru_id` bigint(20) UNSIGNED DEFAULT NULL,
        `judul` varchar(255) NOT NULL,
        `tanggal` date DEFAULT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS `nilai_detail` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `group_id` bigint(20) UNSIGNED NOT NULL,
        `siswa_id` bigint(20) UNSIGNED NOT NULL,
        `nilai` decimal(5,2) DEFAULT NULL,
        `catatan` text DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_nilai_detail_group` (`group_id`),
        KEY `idx_nilai_detail_siswa` (`siswa_id`),
        CONSTRAINT `fk_nilai_detail_group` FOREIGN KEY (`group_id`) REFERENCES `nilai_group` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "Tables 'nilai_group' and 'nilai_detail' created successfully.\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
