<?php
/**
 * Migration: Tasbih Digital Kategori & Riwayat
 */
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$db = Database::getInstance();

$db->query("
CREATE TABLE IF NOT EXISTS `tasbih_kategori` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT(11) DEFAULT 0,
  `nama_tasbih` VARCHAR(100) NOT NULL,
  `lafadz_arab` VARCHAR(255) DEFAULT NULL,
  `arti` TEXT DEFAULT NULL,
  `target_default` INT(11) DEFAULT 33,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pegawai` (`pegawai_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$db->query("
CREATE TABLE IF NOT EXISTS `tasbih_riwayat` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `pegawai_id` INT(11) NOT NULL,
  `kategori_id` INT(11) DEFAULT NULL,
  `nama_tasbih` VARCHAR(100) NOT NULL,
  `jumlah` INT(11) NOT NULL DEFAULT 0,
  `target` INT(11) NOT NULL DEFAULT 33,
  `putaran` INT(11) NOT NULL DEFAULT 1,
  `catatan` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pegawai_tgl` (`pegawai_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$count = $db->find("SELECT COUNT(*) as c FROM tasbih_kategori WHERE pegawai_id = 0");
if (($count['c'] ?? 0) == 0) {
    $defaults = [
        ['Subhanallah (Tasbih)', 'سُبْحَانَ اللَّهِ', 'Maha Suci Allah', 33, 1],
        ['Alhamdulillah (Tahmid)', 'الْحَمْدُ لِلَّهِ', 'Segala puji bagi Allah', 33, 1],
        ['Laa Ilaha Illallah (Tahlil)', 'لَا إِلٰهَ إِلَّا اللَّهُ', 'Tiada Tuhan selain Allah', 33, 1],
        ['Allahu Akbar (Takbir)', 'اللَّهُ أَكْبَرُ', 'Allah Maha Besar', 33, 1],
        ['Sholawat Nabi', 'اللَّهُمَّ صَلِّ عَلَى سَيِّدِنَا مُحَمَّدٍ', 'Ya Allah, limpahkanlah rahmat kepada junjungan kami Nabi Muhammad', 33, 1],
        ['Astaghfirullah (Istighfar)', 'أَسْتَغْفِرُ اللَّهَ الْعَظِيمَ', 'Aku memohon ampun kepada Allah Yang Maha Agung', 100, 1],
        ['Hauqalah', 'لَا حَوْلَ وَلَا قُوَّةَ إِلَّا بِاللَّهِ', 'Tiada daya dan kekuatan kecuali dengan pertolongan Allah', 33, 1],
        ['Tasbih Lengkap (Subhanallah wa Bihamdihi)', 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ سُبْحَانَ اللَّهِ الْعَظِيمِ', 'Maha Suci Allah dengan memuji-Nya, Maha Suci Allah Yang Maha Agung', 100, 1],
    ];

    foreach ($defaults as $d) {
        $db->query(
            "INSERT INTO tasbih_kategori (nama_tasbih, lafadz_arab, arti, target_default, is_default, pegawai_id) VALUES (?, ?, ?, ?, ?, 0)",
            [$d[0], $d[1], $d[2], $d[3], $d[4]]
        );
    }
    echo "Default tasbih categories seeded.\n";
} else {
    echo "Tasbih categories ready.\n";
}
