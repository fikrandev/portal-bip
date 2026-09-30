<?php
define('BASE_PATH', dirname(__DIR__));
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
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "Connected to database.\n";

    // Create sarpras_distribusi table
    $sql = "
    CREATE TABLE IF NOT EXISTS `sarpras_distribusi` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ruangan_id` int(11) NOT NULL,
        `barang_id` bigint(20) unsigned NOT NULL,
        `jumlah` int(11) NOT NULL DEFAULT 1,
        `tanggal_distribusi` date NULL,
        `keterangan` varchar(255) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`ruangan_id`) REFERENCES `sarpras_ruangan`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`barang_id`) REFERENCES `sarpras_barang`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Table sarpras_distribusi created successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
