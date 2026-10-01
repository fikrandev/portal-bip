<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
$configFile = BASE_PATH . '/config/database.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'db_portal_bip');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Memulai migrasi tabel ekstensi Sarpras...\n";

    // 1. Tabel Maintenance
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_maintenance` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `barang_id` BIGINT UNSIGNED NOT NULL,
            `tanggal_lapor` DATE NOT NULL,
            `dilaporkan_oleh` VARCHAR(100) NULL,
            `deskripsi_kerusakan` TEXT NOT NULL,
            `status` ENUM('Menunggu', 'Dalam Perbaikan', 'Selesai') NOT NULL DEFAULT 'Menunggu',
            `tanggal_selesai` DATE NULL,
            `biaya` DECIMAL(15,2) NOT NULL DEFAULT 0,
            `keterangan` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`barang_id`) REFERENCES `sarpras_barang`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel sarpras_maintenance berhasil dibuat/dicek.\n";

    // 2. Tabel Peminjaman
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_peminjaman` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `barang_id` BIGINT UNSIGNED NOT NULL,
            `peminjam` VARCHAR(100) NOT NULL,
            `tanggal_pinjam` DATE NOT NULL,
            `tanggal_rencana_kembali` DATE NOT NULL,
            `tanggal_kembali` DATE NULL,
            `jumlah` INT NOT NULL DEFAULT 1,
            `status` ENUM('Dipinjam', 'Dikembalikan', 'Terlambat') NOT NULL DEFAULT 'Dipinjam',
            `keterangan` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`barang_id`) REFERENCES `sarpras_barang`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel sarpras_peminjaman berhasil dibuat/dicek.\n";

    // 3. Tabel Pengajuan
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_pengajuan` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `nama_barang` VARCHAR(255) NOT NULL,
            `spesifikasi` TEXT NULL,
            `jumlah` INT NOT NULL DEFAULT 1,
            `estimasi_harga` DECIMAL(15,2) NOT NULL DEFAULT 0,
            `keperluan` TEXT NOT NULL,
            `pengaju` VARCHAR(100) NOT NULL,
            `tanggal_pengajuan` DATE NOT NULL,
            `status` ENUM('Draft', 'Diajukan', 'Disetujui', 'Ditolak', 'Selesai') NOT NULL DEFAULT 'Diajukan',
            `keterangan_admin` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Tabel sarpras_pengajuan berhasil dibuat/dicek.\n";

    echo "Semua migrasi tabel ekstensi selesai!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
