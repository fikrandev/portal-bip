<?php
/**
 * Migration: Tabel Sarpras Tanah & Bangunan
 * Portal BIP
 */

define('BASE_PATH', realpath(__DIR__ . '/..'));
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
            `no_sertifikat` VARCHAR(150) NOT NULL,
            `status_kepemilikan` VARCHAR(50) NOT NULL DEFAULT 'Milik Yayasan',
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

    echo "=== 3. Mengisi Data Contoh Tanah & Bangunan ===\n";
    $tanahCount = $pdo->query("SELECT COUNT(*) FROM sarpras_tanah")->fetchColumn();
    if ((int)$tanahCount === 0) {
        $pdo->exec("
            INSERT INTO `sarpras_tanah` (
                `kode_tanah`, `nama_tanah`, `no_sertifikat`, `status_kepemilikan`, 
                `panjang`, `lebar`, `luas`, `tahun_perolehan`, `harga_perolehan`, 
                `alamat_lokasi`, `keterangan`
            ) VALUES 
            (
                'TNH-001', 'Tanah Kampus Terpadu BIP (Gedung Utama & Lapangan)', 
                'SHM No. 04821/Tondo/2016', 'Sertifikat Hak Milik (SHM)', 
                100.00, 50.00, 5000.00, '2016', 2500000000.00, 
                'Jl. Soekarno Hatta No. 45, Kel. Tondo, Kec. Mantikulore, Kota Palu', 
                'Area induk kampus terpadu menampung Gedung A, Gedung B, dan Masjid.'
            ),
            (
                'TNH-002', 'Tanah Kampus Unit SMA & Laboratorium Terpadu', 
                'SHM No. 07192/Tondo/2020', 'Wakaf Tunai Yayasan', 
                60.00, 40.00, 2400.00, '2020', 1200000000.00, 
                'Jl. Soekarno Hatta (Kompleks Belakang), Kel. Tondo, Kota Palu', 
                'Pengembangan gedung pembelajaran baru dan fasilitas laboratorium sains.'
            ),
            (
                'TNH-003', 'Tanah Sarana Olahraga & Lapangan Futsal Outdoor', 
                'SHM No. 08831/Tondo/2022', 'Sertifikat Hak Milik (SHM)', 
                45.00, 30.00, 1350.00, '2022', 750000000.00, 
                'Sebelah Barat Kampus Utama, Kel. Tondo, Kota Palu', 
                'Lapangan olahraga serbaguna futsal, basket, dan arena upacara bendera.'
            )
        ");
        echo "✓ Seed 3 Bidang Tanah berhasil.\n";

        // Seed Bangunan
        $tanah1Id = $pdo->query("SELECT id FROM sarpras_tanah WHERE kode_tanah = 'TNH-001'")->fetchColumn();
        $tanah2Id = $pdo->query("SELECT id FROM sarpras_tanah WHERE kode_tanah = 'TNH-002'")->fetchColumn();

        if ($tanah1Id && $tanah2Id) {
            $pdo->exec("
                INSERT INTO `sarpras_bangunan` (
                    `tanah_id`, `kode_bangunan`, `nama_bangunan`, `jumlah_lantai`, 
                    `panjang`, `lebar`, `luas_bangunan`, `tahun_dibangun`, 
                    `kondisi_bangunan`, `sumber_dana`, `biaya_pembangunan`, `keterangan`
                ) VALUES 
                (
                    {$tanah1Id}, 'BGN-001', 'Gedung A (Kantor Yayasan, Aula & Perpustakaan)', 
                    2, 40.00, 20.00, 800.00, '2017', 'Baik', 'Yayasan', 1850000000.00, 
                    'Lantai 1 Kantor Yayasan & Aula, Lantai 2 Perpustakaan Terpadu.'
                ),
                (
                    {$tanah1Id}, 'BGN-002', 'Gedung B (Ruang Belajar SD & SMP BIP)', 
                    3, 45.00, 18.00, 810.00, '2018', 'Baik', 'BOS & Yayasan', 2400000000.00, 
                    'Lantai 1 Kelas SD, Lantai 2 Kelas SMP, Lantai 3 Lab Komputer.'
                ),
                (
                    {$tanah1Id}, 'BGN-003', 'Masjid Kampus Bina Insan Palu', 
                    1, 20.00, 20.00, 400.00, '2019', 'Baik', 'Wakaf Wali Santri', 950000000.00, 
                    'Sarana sholat berjamaah 5 waktu seluruh santri & dewan guru.'
                ),
                (
                    {$tanah2Id}, 'BGN-004', 'Gedung C (Kelas SMA & Lab Sains)', 
                    2, 35.00, 16.00, 560.00, '2021', 'Baik', 'Yayasan', 1450000000.00, 
                    'Gedung pembelajaran khusus jenjang SMA dan Lab Biologi/Kimia.'
                )
            ");
            echo "✓ Seed 4 Gedung / Bangunan berhasil.\n";
        }
    }

    echo "\n🎉 MIGRASI TABEL TANAH & BANGUNAN SELESAI DENGAN SUKSES! 🎉\n";

} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
