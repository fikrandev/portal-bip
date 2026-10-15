<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$pdo = Database::getInstance()->getConnection();

$sql = "
CREATE TABLE IF NOT EXISTS `spmb_screening_psikolog` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pendaftar_id` INT NOT NULL UNIQUE,
    `file_pdf` VARCHAR(255) NOT NULL,
    `file_name_original` VARCHAR(255) DEFAULT NULL,
    `file_size` INT DEFAULT 0,
    `status_rekomendasi` ENUM('Direkomendasikan', 'Dipertimbangkan', 'Tidak Direkomendasikan', 'Belum Ada Hasil') NOT NULL DEFAULT 'Direkomendasikan',
    `catatan` TEXT DEFAULT NULL,
    `nama_psikolog` VARCHAR(150) DEFAULT NULL,
    `tanggal_screening` DATE DEFAULT NULL,
    `uploaded_by` BIGINT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`pendaftar_id`),
    INDEX (`status_rekomendasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($sql);
echo "Tabel spmb_screening_psikolog berhasil dibuat/diverifikasi.\n";

// Tambahkan kolom di spmb_pendaftar jika belum ada untuk redundancy & instant access
$cols = $pdo->query("SHOW COLUMNS FROM spmb_pendaftar LIKE 'file_screening_psikolog'")->fetchAll();
if (empty($cols)) {
    $pdo->exec("ALTER TABLE `spmb_pendaftar` ADD COLUMN `file_screening_psikolog` VARCHAR(255) NULL DEFAULT NULL AFTER `file_screenshot_dapodik`");
    $pdo->exec("ALTER TABLE `spmb_pendaftar` ADD COLUMN `status_screening_psikolog` VARCHAR(50) NULL DEFAULT NULL AFTER `file_screening_psikolog`");
    $pdo->exec("ALTER TABLE `spmb_pendaftar` ADD COLUMN `catatan_screening_psikolog` TEXT NULL DEFAULT NULL AFTER `status_screening_psikolog`");
    $pdo->exec("ALTER TABLE `spmb_pendaftar` ADD COLUMN `tgl_screening_psikolog` DATE NULL DEFAULT NULL AFTER `catatan_screening_psikolog`");
    echo "Kolom screening psikolog di spmb_pendaftar berhasil ditambahkan.\n";
} else {
    echo "Kolom screening psikolog di spmb_pendaftar sudah ada.\n";
}
