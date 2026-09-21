<?php
/**
 * Migrasi Database Khusus Modul Qur'an PAUD
 * - quran_paud_group : Grup/Wadah Target Pembelajaran (Tahsin / Tahfidz)
 * - quran_paud_nilai : Catatan Penilaian Santri per Grup Target
 */
define('BASE_PATH', realpath(__DIR__ . '/..'));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "=== 1. Membuat / Memperbarui Tabel quran_paud_group ===\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `quran_paud_group` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `nama_grup` varchar(150) NOT NULL,
            `kategori` varchar(50) NOT NULL DEFAULT 'all',
            `tahun_akademik_id` int(11) DEFAULT NULL,
            `semester` enum('Ganjil','Genap') NOT NULL DEFAULT 'Ganjil',
            `kelas` varchar(100) NOT NULL,
            `guru_id` bigint(20) UNSIGNED DEFAULT NULL,
            `guru_nama` varchar(150) DEFAULT NULL,
            `target_materi` text DEFAULT NULL COMMENT 'JSON rincian target tahsin dan tahfidz',
            `deskripsi` text DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_by` bigint(20) UNSIGNED DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_qpaud_kategori` (`kategori`),
            KEY `idx_qpaud_kelas` (`kelas`),
            KEY `idx_qpaud_ta` (`tahun_akademik_id`),
            KEY `idx_qpaud_guru` (`guru_id`),
            KEY `idx_qpaud_active` (`is_active`, `deleted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $pdo->exec("ALTER TABLE `quran_paud_group` MODIFY COLUMN `kategori` varchar(50) NOT NULL DEFAULT 'all'");
    echo "✓ Tabel quran_paud_group siap.\n";

    echo "=== 2. Membuat Tabel quran_paud_nilai ===\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `quran_paud_nilai` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `group_id` bigint(20) UNSIGNED NOT NULL,
            `siswa_id` bigint(20) UNSIGNED NOT NULL,
            `tanggal_penilaian` date NOT NULL,
            `materi_dinilai` varchar(150) NOT NULL,
            `nilai_kelancaran` enum('A','B','C','D') NOT NULL DEFAULT 'A',
            `nilai_makhraj` enum('A','B','C','D') NOT NULL DEFAULT 'A',
            `nilai_adab` enum('A','B','C','D') NOT NULL DEFAULT 'A',
            `bintang` tinyint(4) NOT NULL DEFAULT 5 COMMENT '1 sampai 5 bintang',
            `status_lulus` enum('Mutqin','Lancar','Ulang','Perlu Bimbingan') NOT NULL DEFAULT 'Lancar',
            `catatan` text DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_qpnilai_group` (`group_id`),
            KEY `idx_qpnilai_siswa` (`siswa_id`),
            KEY `idx_qpnilai_tanggal` (`tanggal_penilaian`),
            KEY `idx_qpnilai_status` (`status_lulus`),
            CONSTRAINT `fk_qpnilai_group` FOREIGN KEY (`group_id`) REFERENCES `quran_paud_group` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_qpnilai_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel quran_paud_nilai siap.\n";

    // 3. Tambahkan contoh grup target default jika belum ada data
    $checkGroups = (int)$pdo->query("SELECT COUNT(*) FROM quran_paud_group")->fetchColumn();
    if ($checkGroups === 0) {
        echo "=== 3. Memasukkan Data Awal (Seed) Grup Target PAUD ===\n";
        
        // Ambil tahun akademik aktif
        $ta = $pdo->query("SELECT id FROM tahun_akademik WHERE is_active = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $taId = $ta['id'] ?? 1;

        // Group 1: Tahsin TK A
        $tahsinTarget = json_encode([
            'jilid' => 1,
            'halaman_awal' => 1,
            'halaman_target' => 30,
            'fokus' => 'Pengenalan Huruf Tunggal Hijaiyah Alif - Ya dan Fathah'
        ]);
        $pdo->prepare("
            INSERT INTO quran_paud_group (nama_grup, kategori, tahun_akademik_id, semester, kelas, guru_nama, target_materi, deskripsi, is_active)
            VALUES (?, 'tahsin', ?, 'Ganjil', 'TK A - Al-Kautsar', 'Ustadzah Fatimah, S.Pd.I', ?, 'Target Pembelajaran Iqro Jilid 1 Semester Ganjil', 1)
        ")->execute(['Target Tahsin Iqro Jilid 1 - TK A', $taId, $tahsinTarget]);

        // Group 2: Tahfidz TK B
        $tahfidzTarget = json_encode([
            'surah' => ['An-Nas', 'Al-Falaq', 'Al-Ikhlas', 'Al-Lahab', 'An-Nasr', 'Al-Kafirun', 'Al-Kautsar'],
            'doa' => ['Doa Sebelum Belajar', 'Doa Kedua Orang Tua', 'Doa Sebelum Makan']
        ]);
        $pdo->prepare("
            INSERT INTO quran_paud_group (nama_grup, kategori, tahun_akademik_id, semester, kelas, guru_nama, target_materi, deskripsi, is_active)
            VALUES (?, 'tahfidz', ?, 'Ganjil', 'TK B - Ar-Rahman', 'Ustadzah Maryam, S.Pd', ?, 'Hafalan 7 Surah Pendek Juz Amma dan Doa Harian', 1)
        ")->execute(['Target Tahfidz Surah Pendek - TK B', $taId, $tahfidzTarget]);

        echo "✓ Seed 2 grup target (Tahsin TK A & Tahfidz TK B) berhasil ditambahkan.\n";
    }

    echo "=== Migrasi Qur'an PAUD Selesai dengan Sukses! ===\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
