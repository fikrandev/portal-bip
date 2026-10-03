<?php
/**
 * Migration: Tabel Sarpras Tanah & Bangunan
 * Portal BIP
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..'));
}
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== 1. Membuat Tabel sarpras_tanah ===\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_tanah` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `kode_tanah` VARCHAR(50) NOT NULL UNIQUE,
            `nama_tanah` VARCHAR(200) NOT NULL,
            `no_sertifikat` VARCHAR(150) NULL DEFAULT NULL,
            `status_kepemilikan` VARCHAR(50) NOT NULL DEFAULT 'SHM',
            `panjang` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `lebar` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `luas` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `tahun_perolehan` VARCHAR(4) DEFAULT NULL,
            `harga_perolehan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `alamat_lokasi` TEXT DEFAULT NULL,
            `gambar_sertifikat` TEXT DEFAULT NULL COMMENT 'JSON array of image paths',
            `keterangan` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`nama_tanah`),
            INDEX (`no_sertifikat`),
            INDEX (`status_kepemilikan`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel 'sarpras_tanah' siap.\n";

    echo "=== 2. Membuat Tabel sarpras_bangunan ===\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_bangunan` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tanah_id` BIGINT UNSIGNED NOT NULL,
            `kode_bangunan` VARCHAR(50) NOT NULL UNIQUE,
            `nama_bangunan` VARCHAR(200) NOT NULL,
            `jumlah_lantai` INT NOT NULL DEFAULT 1,
            `panjang` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `lebar` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `luas_bangunan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `tahun_dibangun` VARCHAR(4) DEFAULT NULL,
            `masa_manfaat` INT NOT NULL DEFAULT 20,
            `kondisi_bangunan` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
            `sumber_dana` VARCHAR(100) DEFAULT 'Yayasan',
            `biaya_pembangunan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `foto_bangunan` VARCHAR(255) DEFAULT NULL,
            `keterangan` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`tanah_id`),
            INDEX (`kondisi_bangunan`),
            CONSTRAINT `fk_bangunan_tanah` FOREIGN KEY (`tanah_id`) REFERENCES `sarpras_tanah` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel 'sarpras_bangunan' siap.\n";

    // Hubungkan sarpras_ruangan ke bangunan jika belum ada
    $ruanganCols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM `sarpras_ruangan`");
    while ($r = $stmt->fetch()) {
        $ruanganCols[] = $r['Field'];
    }
    if (!in_array('bangunan_id', $ruanganCols)) {
        $pdo->exec("ALTER TABLE `sarpras_ruangan` ADD COLUMN `bangunan_id` BIGINT UNSIGNED NULL AFTER `kode_ruangan`");
        echo "✓ Kolom 'bangunan_id' ditambahkan ke 'sarpras_ruangan'.\n";
    }
    if (!in_array('lantai', $ruanganCols)) {
        $pdo->exec("ALTER TABLE `sarpras_ruangan` ADD COLUMN `lantai` INT NOT NULL DEFAULT 1 AFTER `bangunan_id`");
        echo "✓ Kolom 'lantai' ditambahkan ke 'sarpras_ruangan'.\n";
    }
    if (!in_array('keterangan', $ruanganCols)) {
        $pdo->exec("ALTER TABLE `sarpras_ruangan` ADD COLUMN `keterangan` TEXT NULL AFTER `kapasitas`");
        echo "✓ Kolom 'keterangan' ditambahkan ke 'sarpras_ruangan'.\n";
    }
    if (!in_array('jenis_ruangan', $ruanganCols)) {
        $pdo->exec("ALTER TABLE `sarpras_ruangan` ADD COLUMN `jenis_ruangan` VARCHAR(100) NOT NULL DEFAULT 'Ruang Kelas' AFTER `nama_ruangan`");
        echo "✓ Kolom 'jenis_ruangan' ditambahkan ke 'sarpras_ruangan'.\n";
    }
    if (!in_array('panjang', $ruanganCols)) {
        $pdo->exec("ALTER TABLE `sarpras_ruangan` ADD COLUMN `panjang` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `kapasitas`");
    }
    if (!in_array('lebar', $ruanganCols)) {
        $pdo->exec("ALTER TABLE `sarpras_ruangan` ADD COLUMN `lebar` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `panjang`");
    }
    if (!in_array('luas', $ruanganCols)) {
        $pdo->exec("ALTER TABLE `sarpras_ruangan` ADD COLUMN `luas` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `lebar`");
        echo "✓ Kolom dimensi 'panjang, lebar, luas' ditambahkan ke 'sarpras_ruangan'.\n";
    }

    // Seeding dinonaktifkan agar data sarpras selalu fresh dan bersih (0 baris) sesuai permintaan.
    echo "\n🎉 MIGRASI STRUKTUR TABEL TANAH & BANGUNAN SELESAI DENGAN SUKSES! 🎉\n";

} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
