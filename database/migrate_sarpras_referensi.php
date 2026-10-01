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

    echo "Memulai migrasi tabel referensi Sarpras...\n";

    // 1. Golongan
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_golongan` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `kode` varchar(20) NOT NULL,
          `nama_golongan` varchar(150) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `kode` (`kode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // 2. Kode Kelompok
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_kode_kelompok` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `golongan_id` int(11) NOT NULL,
          `kode` varchar(30) NOT NULL,
          `nama_kelompok` varchar(150) NOT NULL,
          `keterangan` text DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `golongan_id` (`golongan_id`)
          -- FOREIGN KEY (\`golongan_id\`) REFERENCES \`sarpras_golongan\` (\`id\`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // 3. Asal Anggaran
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_asal_anggaran` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `kode` varchar(20) NOT NULL,
          `nama` varchar(150) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `kode` (`kode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");

    // 4. Satuan
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_satuan` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nama_satuan` varchar(50) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    
    // Insert initial Data for Satuan if empty
    $count = $pdo->query("SELECT COUNT(*) FROM `sarpras_satuan`")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO `sarpras_satuan` (`nama_satuan`) VALUES
            ('Unit'), ('Buah'), ('Set'), ('Meter'), ('Kodi'), ('Lusin');
        ");
    }

    echo "Tabel referensi berhasil dibuat/dicek.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
