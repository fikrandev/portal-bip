<?php
/**
 * Migration: Tabel Wadah Grup Absen & Penyempurnaan Presensi Kelas & Detail
 */
define('BASE_PATH', realpath(__DIR__ . '/..'));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();

    echo "=== 1. Membuat Tabel absen_group ===\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `absen_group` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `unit` varchar(50) NOT NULL,
            `tahun_akademik_id` int(11) NOT NULL DEFAULT 1,
            `semester` varchar(20) NOT NULL DEFAULT 'Ganjil',
            `tipe` enum('mapel','kelas','umum') NOT NULL DEFAULT 'mapel',
            `kelas` varchar(100) DEFAULT NULL,
            `mata_pelajaran` varchar(150) DEFAULT NULL,
            `guru_id` bigint(20) UNSIGNED DEFAULT NULL,
            `judul` varchar(255) NOT NULL,
            `deskripsi` text DEFAULT NULL,
            `tanggal_mulai` date DEFAULT NULL,
            `tanggal_selesai` date DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_by` bigint(20) UNSIGNED DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_absen_group_unit` (`unit`),
            KEY `idx_absen_group_guru` (`guru_id`),
            KEY `idx_absen_group_active` (`is_active`, `deleted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel 'absen_group' siap.\n";

    echo "=== 2. Memperbarui Kolom presensi_kelas ===\n";
    $existingCols = [];
    $stmt = $pdo->query("DESCRIBE presensi_kelas");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingCols[] = $row['Field'];
    }

    if (!in_array('group_id', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `group_id` bigint(20) UNSIGNED DEFAULT NULL AFTER `id`");
        echo "✓ Kolom 'group_id' ditambahkan ke presensi_kelas.\n";
    }
    if (!in_array('tipe_presensi', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `tipe_presensi` enum('mapel','kelas') NOT NULL DEFAULT 'mapel' AFTER `group_id`");
        echo "✓ Kolom 'tipe_presensi' ditambahkan ke presensi_kelas.\n";
    }
    if (!in_array('mata_pelajaran', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `mata_pelajaran` varchar(150) DEFAULT NULL AFTER `kelas`");
        echo "✓ Kolom 'mata_pelajaran' ditambahkan ke presensi_kelas.\n";
    }
    if (!in_array('pertemuan_ke', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `pertemuan_ke` int(11) DEFAULT 1 AFTER `tanggal`");
        echo "✓ Kolom 'pertemuan_ke' ditambahkan ke presensi_kelas.\n";
    }
    if (!in_array('materi', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `materi` varchar(255) DEFAULT NULL AFTER `jp`");
        echo "✓ Kolom 'materi' ditambahkan ke presensi_kelas.\n";
    }
    if (!in_array('catatan', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `catatan` text DEFAULT NULL AFTER `materi`");
        echo "✓ Kolom 'catatan' ditambahkan ke presensi_kelas.\n";
    }
    if (!in_array('created_by', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `created_by` bigint(20) UNSIGNED DEFAULT NULL AFTER `catatan`");
        echo "✓ Kolom 'created_by' ditambahkan ke presensi_kelas.\n";
    }
    if (!in_array('created_at', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP AFTER `waktu_presensi`");
        echo "✓ Kolom 'created_at' ditambahkan ke presensi_kelas.\n";
    }
    if (!in_array('updated_at', $existingCols)) {
        $pdo->exec("ALTER TABLE `presensi_kelas` ADD COLUMN `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`");
        echo "✓ Kolom 'updated_at' ditambahkan ke presensi_kelas.\n";
    }

    echo "=== 3. Memperbarui Kolom presensi_kelas_detail ===\n";
    $pdo->exec("ALTER TABLE `presensi_kelas_detail` MODIFY COLUMN `status` enum('H','S','I','A','T') NOT NULL DEFAULT 'H'");
    echo "✓ Enum status presensi_kelas_detail diperbarui ke ('H','S','I','A','T').\n";

    // Cek constraint unik
    $indexes = $pdo->query("SHOW INDEX FROM `presensi_kelas_detail` WHERE Key_name = 'uq_presensi_siswa'")->fetchAll();
    if (empty($indexes)) {
        $pdo->exec("ALTER TABLE `presensi_kelas_detail` ADD UNIQUE KEY `uq_presensi_siswa` (`presensi_kelas_id`, `siswa_id`)");
        echo "✓ Index unik 'uq_presensi_siswa' ditambahkan ke presensi_kelas_detail.\n";
    }

    echo "=== 4. Memastikan Permissions RBAC ===\n";
    $permissions = [
        [
            'name' => 'Rekap Absen Siswa',
            'slug' => 'absen_siswa.rekap',
            'description' => 'Melihat dan mencetak rekapitulasi presensi siswa (per kelas & per mapel)',
            'module_id' => 12
        ],
        [
            'name' => 'Ekspor Absen Siswa',
            'slug' => 'absen_siswa.export',
            'description' => 'Mengekspor data rekapitulasi presensi siswa ke Excel/CSV',
            'module_id' => 12
        ]
    ];

    foreach ($permissions as $p) {
        $check = $pdo->prepare("SELECT id FROM permissions WHERE slug = ?");
        $check->execute([$p['slug']]);
        $row = $check->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $insert = $pdo->prepare("INSERT INTO permissions (name, slug, description, module_id, created_at) VALUES (?, ?, ?, ?, NOW())");
            $insert->execute([$p['name'], $p['slug'], $p['description'], $p['module_id']]);
            $newPermId = $pdo->lastInsertId();
            echo "✓ Permission '{$p['slug']}' berhasil dibuat (ID: {$newPermId}).\n";

            // Tambahkan ke role Super Administrator (role_id = 1)
            $rpCheck = $pdo->prepare("SELECT 1 FROM role_permissions WHERE role_id = 1 AND permission_id = ?");
            $rpCheck->execute([$newPermId]);
            if (!$rpCheck->fetchColumn()) {
                $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (1, ?)")->execute([$newPermId]);
                echo "  -> Diberikan ke Super Administrator.\n";
            }
        } else {
            echo "✓ Permission '{$p['slug']}' sudah ada (ID: {$row['id']}).\n";
            // Pastikan Super Admin punya
            $rpCheck = $pdo->prepare("SELECT 1 FROM role_permissions WHERE role_id = 1 AND permission_id = ?");
            $rpCheck->execute([$row['id']]);
            if (!$rpCheck->fetchColumn()) {
                $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (1, ?)")->execute([$row['id']]);
                echo "  -> Diberikan ke Super Administrator.\n";
            }
        }
    }

    echo "\n✅ Migrasi database modul Kelola Absen Siswa Selesai dengan Sukses!\n";
} catch (Exception $e) {
    echo "❌ Error migrasi: " . $e->getMessage() . "\n";
    exit(1);
}
