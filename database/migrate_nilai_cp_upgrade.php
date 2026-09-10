<?php
/**
 * Migration: Upgrade nilai tables for CP-based grading
 * - Adds cpatp_id and cpatp_doc_id to nilai_group
 * - Adds cpatp_row_index + unique constraint to nilai_detail
 */
define('BASE_PATH', realpath(__DIR__ . '/..'));
require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Add cpatp_id to nilai_group (if not exists)
    $cols = $pdo->query("SHOW COLUMNS FROM `nilai_group` LIKE 'cpatp_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE `nilai_group` ADD COLUMN `cpatp_id` BIGINT UNSIGNED DEFAULT NULL AFTER `jenis_penilaian`");
        echo "Added column 'cpatp_id' to nilai_group.\n";
    } else {
        echo "Column 'cpatp_id' already exists in nilai_group.\n";
    }

    // 2. Add cpatp_doc_id to nilai_group (if not exists)
    $cols2 = $pdo->query("SHOW COLUMNS FROM `nilai_group` LIKE 'cpatp_doc_id'")->fetchAll();
    if (empty($cols2)) {
        $pdo->exec("ALTER TABLE `nilai_group` ADD COLUMN `cpatp_doc_id` BIGINT UNSIGNED DEFAULT NULL AFTER `cpatp_id`");
        echo "Added column 'cpatp_doc_id' to nilai_group.\n";
    } else {
        echo "Column 'cpatp_doc_id' already exists in nilai_group.\n";
    }

    // 3. Add cpatp_row_index to nilai_detail (if not exists)
    $cols3 = $pdo->query("SHOW COLUMNS FROM `nilai_detail` LIKE 'cpatp_row_index'")->fetchAll();
    if (empty($cols3)) {
        $pdo->exec("ALTER TABLE `nilai_detail` ADD COLUMN `cpatp_row_index` INT NOT NULL DEFAULT 0 AFTER `group_id`");
        echo "Added column 'cpatp_row_index' to nilai_detail.\n";
    } else {
        echo "Column 'cpatp_row_index' already exists in nilai_detail.\n";
    }

    // 4. Add unique constraint (if not exists)
    $indexes = $pdo->query("SHOW INDEX FROM `nilai_detail` WHERE Key_name = 'uq_nilai_per_cp'")->fetchAll();
    if (empty($indexes)) {
        // Remove potential duplicates first
        $pdo->exec("
            DELETE nd1 FROM nilai_detail nd1
            INNER JOIN nilai_detail nd2
            WHERE nd1.id > nd2.id
              AND nd1.group_id = nd2.group_id
              AND nd1.siswa_id = nd2.siswa_id
              AND nd1.cpatp_row_index = nd2.cpatp_row_index
        ");
        $pdo->exec("ALTER TABLE `nilai_detail` ADD UNIQUE KEY `uq_nilai_per_cp` (`group_id`, `siswa_id`, `cpatp_row_index`)");
        echo "Added unique constraint 'uq_nilai_per_cp' to nilai_detail.\n";
    } else {
        echo "Unique constraint 'uq_nilai_per_cp' already exists.\n";
    }

    echo "\n✅ Migration completed successfully!\n";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
