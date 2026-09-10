<?php
define('BASE_PATH', realpath(__DIR__ . '/..'));
require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, DB_OPTIONS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- 1. Creating Quran Siswa Tables ---\n";

    $sqlTables = "
    CREATE TABLE IF NOT EXISTS `quran_siswa_setoran` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `siswa_id` bigint(20) UNSIGNED NOT NULL,
        `guru_id` bigint(20) UNSIGNED DEFAULT NULL,
        `tahun_akademik_id` int(11) DEFAULT NULL,
        `jenjang` enum('PAUD','SD','SMP_SMA') NOT NULL DEFAULT 'SD',
        `tanggal` date NOT NULL,
        `jenis_setoran` enum('ziyadah','murojaah','iqro','tasmi','munaqasyah','ujian') NOT NULL DEFAULT 'ziyadah',
        `iqro_jilid` tinyint(4) DEFAULT NULL,
        `iqro_halaman` smallint(6) DEFAULT NULL,
        `surah_nomor` tinyint(4) DEFAULT NULL,
        `surah_nama` varchar(100) DEFAULT NULL,
        `ayat_awal` smallint(6) DEFAULT NULL,
        `ayat_akhir` smallint(6) DEFAULT NULL,
        `juz` tinyint(4) DEFAULT NULL,
        `halaman_quran` smallint(6) DEFAULT NULL,
        `nilai_kelancaran` enum('A','B','C','D') NOT NULL DEFAULT 'A',
        `nilai_tajwid` enum('A','B','C','D') NOT NULL DEFAULT 'A',
        `nilai_makhorij` enum('A','B','C','D') NOT NULL DEFAULT 'A',
        `status_lulus` enum('lancar','ulang','mutqin','perlu_bimbingan') NOT NULL DEFAULT 'lancar',
        `catatan` text DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT current_timestamp(),
        `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        KEY `idx_qsetoran_siswa` (`siswa_id`),
        KEY `idx_qsetoran_jenjang` (`jenjang`),
        KEY `idx_qsetoran_tanggal` (`tanggal`),
        KEY `idx_qsetoran_jenis` (`jenis_setoran`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS `quran_siswa_target` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `siswa_id` bigint(20) UNSIGNED NOT NULL,
        `jenjang` enum('PAUD','SD','SMP_SMA') NOT NULL DEFAULT 'SD',
        `target_juz` varchar(100) DEFAULT NULL,
        `target_iqro` varchar(50) DEFAULT NULL,
        `capaian_terakhir` varchar(150) DEFAULT NULL,
        `total_juz_selesai` decimal(4,2) NOT NULL DEFAULT 0.00,
        `predikat` enum('Mumtaz','Jayyid Jiddan','Jayyid','Maqbul','Proses') NOT NULL DEFAULT 'Proses',
        `status` enum('Aktif','Tercapai') NOT NULL DEFAULT 'Aktif',
        `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_qtarget_siswa_jenjang` (`siswa_id`, `jenjang`),
        KEY `idx_qtarget_jenjang` (`jenjang`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sqlTables);
    echo "Tables 'quran_siswa_setoran' and 'quran_siswa_target' created or verified.\n";

    echo "\n--- 2. Registering / Updating 3 Modules in 'modules' ---\n";

    // Check if kelola-quran-siswa exists
    $existing = $pdo->query("SELECT slug, id FROM modules WHERE slug IN ('kelola-quran-siswa', 'kelola-quran-siswa-sd', 'kelola-quran-siswa-paud', 'kelola-quran-siswa-smp-sma')")->fetchAll(PDO::FETCH_KEY_PAIR);

    $iconPAUD = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 0v3m0-3h3m-3 0H9" /></svg>';
    $iconSD = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>';
    $iconSMPSMA = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>';

    // 1. Modul PAUD
    if (!isset($existing['kelola-quran-siswa-paud'])) {
        $stmt = $pdo->prepare("INSERT INTO modules (name, slug, description, module_group, icon_svg, color, route, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            "Qur'an Siswa PAUD",
            'kelola-quran-siswa-paud',
            "Iqro', Tilawati & Hafalan Surah Pendek Siswa PAUD/TK",
            'Administrasi Guru',
            $iconPAUD,
            '#F59E0B',
            '/kelola-quran-siswa-paud',
            13
        ]);
        $paudModuleId = $pdo->lastInsertId();
        echo "Created module: Qur'an Siswa PAUD (ID: $paudModuleId)\n";
    } else {
        $paudModuleId = array_search('kelola-quran-siswa-paud', $existing);
        $pdo->prepare("UPDATE modules SET name = ?, description = ?, route = ?, color = ? WHERE id = ?")
            ->execute(["Qur'an Siswa PAUD", "Iqro', Tilawati & Hafalan Surah Pendek Siswa PAUD/TK", "/kelola-quran-siswa-paud", "#F59E0B", $paudModuleId]);
        echo "Updated module: Qur'an Siswa PAUD (ID: $paudModuleId)\n";
    }

    // 2. Modul SD (Update kelola-quran-siswa or create kelola-quran-siswa-sd)
    if (isset($existing['kelola-quran-siswa'])) {
        $sdModuleId = array_search('kelola-quran-siswa', $existing);
        $pdo->prepare("UPDATE modules SET name = ?, slug = ?, description = ?, route = ?, color = ?, sort_order = ? WHERE id = ?")
            ->execute([
                "Qur'an Siswa SD",
                'kelola-quran-siswa-sd',
                "Ziyadah, Muroja'ah Juz 30/29 & Tilawah Siswa SD IT",
                '/kelola-quran-siswa-sd',
                '#10B981',
                14,
                $sdModuleId
            ]);
        echo "Updated module ID $sdModuleId -> Qur'an Siswa SD\n";
    } elseif (!isset($existing['kelola-quran-siswa-sd'])) {
        $stmt = $pdo->prepare("INSERT INTO modules (name, slug, description, module_group, icon_svg, color, route, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            "Qur'an Siswa SD",
            'kelola-quran-siswa-sd',
            "Ziyadah, Muroja'ah Juz 30/29 & Tilawah Siswa SD IT",
            'Administrasi Guru',
            $iconSD,
            '#10B981',
            '/kelola-quran-siswa-sd',
            14
        ]);
        $sdModuleId = $pdo->lastInsertId();
        echo "Created module: Qur'an Siswa SD (ID: $sdModuleId)\n";
    } else {
        $sdModuleId = array_search('kelola-quran-siswa-sd', $existing);
    }

    // 3. Modul SMP & SMA
    if (!isset($existing['kelola-quran-siswa-smp-sma'])) {
        $stmt = $pdo->prepare("INSERT INTO modules (name, slug, description, module_group, icon_svg, color, route, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([
            "Qur'an Siswa SMP & SMA",
            'kelola-quran-siswa-smp-sma',
            "Tahfidz Multi-Juz, Tasmi' Sekali Duduk & Munaqasyah SMP/SMA IT",
            'Administrasi Guru',
            $iconSMPSMA,
            '#6366F1',
            '/kelola-quran-siswa-smp-sma',
            15
        ]);
        $smpSmaModuleId = $pdo->lastInsertId();
        echo "Created module: Qur'an Siswa SMP & SMA (ID: $smpSmaModuleId)\n";
    } else {
        $smpSmaModuleId = array_search('kelola-quran-siswa-smp-sma', $existing);
        $pdo->prepare("UPDATE modules SET name = ?, description = ?, route = ?, color = ? WHERE id = ?")
            ->execute(["Qur'an Siswa SMP & SMA", "Tahfidz Multi-Juz, Tasmi' Sekali Duduk & Munaqasyah SMP/SMA IT", "/kelola-quran-siswa-smp-sma", "#6366F1", $smpSmaModuleId]);
        echo "Updated module: Qur'an Siswa SMP & SMA (ID: $smpSmaModuleId)\n";
    }

    $paudModule = $pdo->query("SELECT id FROM modules WHERE slug = 'kelola-quran-siswa-paud'")->fetch(PDO::FETCH_ASSOC);
    $sdModule = $pdo->query("SELECT id FROM modules WHERE slug = 'kelola-quran-siswa-sd'")->fetch(PDO::FETCH_ASSOC);
    $smpSmaModule = $pdo->query("SELECT id FROM modules WHERE slug = 'kelola-quran-siswa-smp-sma'")->fetch(PDO::FETCH_ASSOC);

    $paudModuleId = $paudModule['id'] ?? null;
    $sdModuleId = $sdModule['id'] ?? null;
    $smpSmaModuleId = $smpSmaModule['id'] ?? null;

    // Adjust sort_orders and deactivate legacy single module 14
    $pdo->exec("UPDATE modules SET is_active = 0 WHERE id = 14");
    $pdo->exec("UPDATE modules SET is_active = 1 WHERE id IN (" . implode(',', array_filter([$paudModuleId, $sdModuleId, $smpSmaModuleId])) . ")");
    $pdo->exec("UPDATE modules SET sort_order = 16 WHERE slug = 'kelola-ujian' AND sort_order < 16");
    $pdo->exec("UPDATE modules SET sort_order = 17 WHERE slug = 'kelola-quran-pegawai' AND sort_order < 17");

    echo "\n--- 3. Mapping Permissions in module_permissions ---\n";
    // Get quran_siswa permissions
    $perms = $pdo->query("SELECT id FROM permissions WHERE slug LIKE 'quran_siswa.%'")->fetchAll(PDO::FETCH_COLUMN);
    
    $targetModules = array_filter([$paudModuleId, $sdModuleId, $smpSmaModuleId]);
    foreach ($targetModules as $mId) {
        foreach ($perms as $pId) {
            $pdo->prepare("INSERT IGNORE INTO module_permissions (module_id, permission_id) VALUES (?, ?)")
                ->execute([$mId, $pId]);
        }
    }
    echo "Permissions linked for modules: " . implode(', ', $targetModules) . "\n";

    echo "\n=== Migration Completed Successfully! ===\n";

} catch (PDOException $e) {
    die("Migration Error: " . $e->getMessage() . "\n");
}
