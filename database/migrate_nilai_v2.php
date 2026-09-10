<?php
/**
 * Migration: Daftar Nilai V2 - Format Komprehensif CP→TP→ATP + Sumatif
 * 
 * Encoding baru untuk cpatp_row_index:
 * - docBase = cpatpDocId × 100000
 * - ATP formatif: docBase + rowIdx × 100 + kktpIdx
 * - Sumatif LM:   docBase + 90000 + lmIdx (1-based)
 * - Sumatif SAS:   docBase + 99000
 * 
 * Catatan: Data lama (encoding ×1000) tidak kompatibel.
 * Jalankan script ini untuk membersihkan data lama.
 */
define('BASE_PATH', realpath(__DIR__ . '/..'));
require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Pastikan tabel nilai_group dan nilai_detail sudah ada
    $tables = $pdo->query("SHOW TABLES LIKE 'nilai_group'")->fetchAll();
    if (empty($tables)) {
        die("❌ Tabel 'nilai_group' belum ada. Jalankan migrate_nilai_tables.php terlebih dahulu.\n");
    }

    // 2. Pastikan kolom cpatp_row_index sudah ada di nilai_detail
    $cols = $pdo->query("SHOW COLUMNS FROM `nilai_detail` LIKE 'cpatp_row_index'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `nilai_detail` ADD COLUMN `cpatp_row_index` INT NOT NULL DEFAULT 0 AFTER `group_id`");
        echo "✓ Kolom 'cpatp_row_index' ditambahkan ke nilai_detail.\n";
    } else {
        echo "✓ Kolom 'cpatp_row_index' sudah ada.\n";
    }

    // 3. Pastikan kolom cpatp_id sudah ada di nilai_group
    $cols2 = $pdo->query("SHOW COLUMNS FROM `nilai_group` LIKE 'cpatp_id'")->fetchAll();
    if (empty($cols2)) {
        $pdo->exec("ALTER TABLE `nilai_group` ADD COLUMN `cpatp_id` BIGINT UNSIGNED DEFAULT NULL AFTER `jenis_penilaian`");
        echo "✓ Kolom 'cpatp_id' ditambahkan ke nilai_group.\n";
    } else {
        echo "✓ Kolom 'cpatp_id' sudah ada.\n";
    }

    // 4. Bersihkan data nilai lama (encoding ×1000 tidak kompatibel)
    $oldCount = $pdo->query("SELECT COUNT(*) FROM nilai_detail")->fetchColumn();
    if ((int)$oldCount > 0) {
        $pdo->exec("TRUNCATE TABLE nilai_detail");
        echo "✓ Data lama dibersihkan ({$oldCount} baris dihapus).\n";
    } else {
        echo "✓ Tabel nilai_detail sudah kosong.\n";
    }

    // 5. Pastikan unique constraint menggunakan format baru
    $indexes = $pdo->query("SHOW INDEX FROM `nilai_detail` WHERE Key_name = 'uq_nilai_per_cp'")->fetchAll();
    if (!empty($indexes)) {
        $pdo->exec("ALTER TABLE `nilai_detail` DROP INDEX `uq_nilai_per_cp`");
        echo "✓ Constraint lama 'uq_nilai_per_cp' dihapus.\n";
    }
    $pdo->exec("ALTER TABLE `nilai_detail` ADD UNIQUE KEY `uq_nilai_per_cp` (`group_id`, `siswa_id`, `cpatp_row_index`)");
    echo "✓ Constraint baru 'uq_nilai_per_cp' dibuat.\n";

    echo "\n✅ Migrasi Daftar Nilai V2 berhasil!\n";
    echo "Encoding baru: docBase = docId × 100000\n";
    echo "  ATP: docBase + rowIdx × 100 + kktpIdx\n";
    echo "  LM:  docBase + 90000 + lmIdx\n";
    echo "  SAS: docBase + 99000\n";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
