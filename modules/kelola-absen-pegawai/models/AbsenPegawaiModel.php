<?php
/**
 * AbsenPegawaiModel - Model untuk Modul Kelola Absen Pegawai
 * Portal BIP
 *
 * Fitur Utama:
 * 1. Pengaturan Global:
 *    - Radius Absen Global (meter: 5m, 10m, 15m, dst.) -> Berlaku untuk SEMUA pegawai & SEMUA unit
 *    - Lokasi Kantor Semula / Default (Nama Kantor, Latitude, Longitude) -> Acuan saat di-reset ke semula
 * 2. Lokasi Custom Per Unit (Nama Kantor, Latitude, Longitude per Unit Tugas)
 * 3. Lokasi Custom Per Pegawai (Nama Kantor, Latitude, Longitude per Individu Pegawai)
 * 4. Reset Lokasi Absen ke Semula (Per Pegawai, Per Unit, maupun Reset Semua Sekaligus)
 * 5. Pencatatan & Rekap Log Kehadiran Absen Pegawai
 */

class AbsenPegawaiModel
{
    private static bool $initialized = false;

    public static function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Inisialisasi tabel-tabel Kelola Absen Pegawai secara otomatis dan idempoten
     */
    public static function init(): void
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;

        $stampFile = BASE_PATH . '/storage/logs/.schema_absen_pegawai_v10.stamp';
        if (file_exists($stampFile)) {
            return;
        }

        $pdo = self::db()->getConnection();

        // 0. Tabel Pengaturan Hari Libur (Selain Minggu)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `absen_pegawai_libur` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `tanggal` DATE NOT NULL,
                `nama_libur` VARCHAR(150) NOT NULL,
                `jenis` ENUM('nasional', 'cuti_bersama', 'yayasan', 'lainnya') NOT NULL DEFAULT 'nasional',
                `keterangan` VARCHAR(255) DEFAULT NULL,
                `created_by` INT(11) DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_absen_libur_tanggal` (`tanggal`),
                KEY `idx_absen_libur_jenis` (`jenis`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 1. Tabel Pengaturan Global Absen (Radius Global & Lokasi Semula/Default)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `absen_pegawai_pengaturan` (
                `id` INT PRIMARY KEY DEFAULT 1,
                `nama_kantor_default` VARCHAR(150) NOT NULL DEFAULT 'Kampus Utama SIT Bina Insan Palu',
                `latitude_default` DECIMAL(10, 7) NOT NULL DEFAULT -0.8917000,
                `longitude_default` DECIMAL(10, 7) NOT NULL DEFAULT 119.8707000,
                `radius_meter` INT NOT NULL DEFAULT 10 COMMENT 'Radius global dalam meter untuk seluruh pegawai & unit',
                `jam_masuk_mulai` TIME NOT NULL DEFAULT '06:00:00',
                `jam_masuk_batas` TIME NOT NULL DEFAULT '07:30:00',
                `jam_pulang_mulai` TIME NOT NULL DEFAULT '15:00:00',
                `periode_awal` DATE NULL,
                `periode_akhir` DATE NULL,
                `updated_by` INT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Pastikan kolom periode_awal dan periode_akhir ada pada absen_pegawai_pengaturan
        try {
            $colsPeriode = $pdo->query("SHOW COLUMNS FROM `absen_pegawai_pengaturan` LIKE 'periode_awal'")->fetchAll();
            if (empty($colsPeriode)) {
                $pdo->exec("ALTER TABLE `absen_pegawai_pengaturan` ADD COLUMN `periode_awal` DATE NULL AFTER `jam_pulang_mulai`, ADD COLUMN `periode_akhir` DATE NULL AFTER `periode_awal`");
                $pdo->exec("UPDATE `absen_pegawai_pengaturan` SET `periode_awal` = DATE_FORMAT(NOW(), '%Y-%m-01'), `periode_akhir` = LAST_DAY(NOW()) WHERE `id` = 1 AND `periode_awal` IS NULL");
            }
        } catch (Throwable $e) {}

        // Pastikan tabel pengaturan rekap jurnal guru tersedia
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `jurnal_guru_pengaturan` (
                    `id` INT NOT NULL DEFAULT 1,
                    `periode_awal` DATE NULL,
                    `periode_akhir` DATE NULL,
                    `keterangan` VARCHAR(255) NULL,
                    `updated_by` INT NULL,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
            $pdo->exec("
                INSERT IGNORE INTO `jurnal_guru_pengaturan`
                (`id`, `periode_awal`, `periode_akhir`, `keterangan`)
                VALUES (1, DATE_FORMAT(NOW(), '%Y-%m-01'), LAST_DAY(NOW()), 'Default Periode Rekap Jurnal PIC')
            ");
        } catch (Throwable $e) {}

        // Pastikan baris pengaturan default ID=1 selalu ada
        $pdo->exec("
            INSERT IGNORE INTO `absen_pegawai_pengaturan`
            (`id`, `nama_kantor_default`, `latitude_default`, `longitude_default`, `radius_meter`, `jam_masuk_mulai`, `jam_masuk_batas`, `jam_pulang_mulai`)
            VALUES (1, 'Kampus Utama SIT Bina Insan Palu', -0.8917000, 119.8707000, 10, '06:00:00', '07:30:00', '15:00:00')
        ");

        // 2. Tabel Lokasi Custom Per Unit Tugas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `absen_pegawai_lokasi_unit` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `unit_tugas` VARCHAR(100) NOT NULL UNIQUE,
                `nama_kantor` VARCHAR(150) NOT NULL,
                `latitude` DECIMAL(10, 7) NOT NULL,
                `longitude` DECIMAL(10, 7) NOT NULL,
                `is_custom` TINYINT(1) NOT NULL DEFAULT 1,
                `updated_by` INT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_absen_unit` (`unit_tugas`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Tabel Lokasi Custom Per Pegawai
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `absen_pegawai_lokasi` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pegawai_id` INT NOT NULL UNIQUE,
                `mode_lokasi` ENUM('default', 'unit', 'custom') NOT NULL DEFAULT 'default',
                `nama_kantor` VARCHAR(150) NOT NULL,
                `latitude` DECIMAL(10, 7) NOT NULL,
                `longitude` DECIMAL(10, 7) NOT NULL,
                `tanggal_mulai` DATE NULL DEFAULT NULL,
                `tanggal_selesai` DATE NULL DEFAULT NULL,
                `hari_aktif` VARCHAR(100) NULL DEFAULT NULL,
                `keterangan` VARCHAR(255) NULL,
                `updated_by` INT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_absen_pegawai_id` (`pegawai_id`),
                INDEX `idx_absen_mode` (`mode_lokasi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Pastikan kolom tambahan tersedia pada tabel yang sudah ada (idempoten per kolom)
        $schemaColumns = [
            'absen_pegawai_pengaturan' => [
                'periode_awal'           => 'DATE NULL AFTER `jam_pulang_mulai`',
                'periode_akhir'          => 'DATE NULL AFTER `periode_awal`',
                'sabtu_khusus_aktif'     => 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `periode_akhir`',
                'sabtu_jam_masuk_mulai'  => "TIME NULL DEFAULT '06:00:00' AFTER `sabtu_khusus_aktif`",
                'sabtu_jam_masuk_batas'  => "TIME NULL DEFAULT '07:30:00' AFTER `sabtu_jam_masuk_mulai`",
                'sabtu_jam_pulang_mulai' => "TIME NULL DEFAULT '15:00:00' AFTER `sabtu_jam_masuk_batas`",
            ],
            'absen_pegawai_lokasi' => [
                'tanggal_mulai'    => 'DATE NULL DEFAULT NULL AFTER `longitude`',
                'tanggal_selesai'  => 'DATE NULL DEFAULT NULL AFTER `tanggal_mulai`',
                'hari_aktif'       => 'VARCHAR(100) NULL DEFAULT NULL AFTER `tanggal_selesai`',
                'jam_masuk_mulai'  => 'TIME NULL DEFAULT NULL AFTER `hari_aktif`',
                'jam_masuk_batas'  => 'TIME NULL DEFAULT NULL AFTER `jam_masuk_mulai`',
                'jam_pulang_mulai' => 'TIME NULL DEFAULT NULL AFTER `jam_masuk_batas`',
            ],
            'absen_pegawai_lokasi_unit' => [
                'jam_masuk_mulai'  => 'TIME NULL DEFAULT NULL AFTER `is_custom`',
                'jam_masuk_batas'  => 'TIME NULL DEFAULT NULL AFTER `jam_masuk_mulai`',
                'jam_pulang_mulai' => 'TIME NULL DEFAULT NULL AFTER `jam_masuk_batas`',
            ],
            'absen_pegawai_log' => [
                'evaluasi'      => 'TEXT NULL AFTER `keterangan`',
                'tindak_lanjut' => 'TEXT NULL AFTER `evaluasi`',
                'evaluasi_oleh' => 'VARCHAR(100) NULL AFTER `tindak_lanjut`',
                'evaluasi_at'   => 'DATETIME NULL AFTER `evaluasi_oleh`',
            ],
        ];
        foreach ($schemaColumns as $tableName => $columns) {
            foreach ($columns as $colName => $colDef) {
                try {
                    $exists = $pdo->query("SHOW COLUMNS FROM `{$tableName}` LIKE '{$colName}'")->fetchAll();
                    if (empty($exists)) {
                        $pdo->exec("ALTER TABLE `{$tableName}` ADD COLUMN `{$colName}` {$colDef}");
                    }
                } catch (Throwable $e) {}
            }
        }

        try {
            $pdo->exec("ALTER TABLE `absen_pegawai_log` MODIFY COLUMN `radius_saat_absen` INT NOT NULL DEFAULT 10");
        } catch (Throwable $e) {}

        // 4. Tabel Jadwal Jam Kerja Khusus Per Hari (Per Pegawai)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `absen_pegawai_jam_harian` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pegawai_id` INT NOT NULL,
                `hari_ke` TINYINT NOT NULL COMMENT '1=Senin..7=Minggu',
                `jam_masuk_mulai` TIME NULL DEFAULT NULL,
                `jam_masuk_batas` TIME NOT NULL,
                `jam_pulang_mulai` TIME NULL DEFAULT NULL,
                `updated_by` INT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_pegawai_hari` (`pegawai_id`, `hari_ke`),
                INDEX `idx_jam_harian_pegawai` (`pegawai_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 5. Tabel Log Kehadiran / Presensi Harian Pegawai
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `absen_pegawai_log` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pegawai_id` INT NOT NULL,
                `tanggal` DATE NOT NULL,
                `jam_masuk` TIME NULL,
                `jam_pulang` TIME NULL,
                `status` ENUM('Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti', 'Alpha', 'Dinas Luar') NOT NULL DEFAULT 'Hadir',
                `nama_kantor_absen` VARCHAR(150) NULL,
                `lat_masuk` DECIMAL(10, 7) NULL,
                `lng_masuk` DECIMAL(10, 7) NULL,
                `jarak_masuk_meter` DECIMAL(8, 2) NULL,
                `lat_pulang` DECIMAL(10, 7) NULL,
                `lng_pulang` DECIMAL(10, 7) NULL,
                `jarak_pulang_meter` DECIMAL(8, 2) NULL,
                `radius_saat_absen` INT NOT NULL DEFAULT 10,
                `keterangan` VARCHAR(255) NULL,
                `evaluasi` TEXT NULL,
                `tindak_lanjut` TEXT NULL,
                `evaluasi_oleh` VARCHAR(100) NULL,
                `evaluasi_at` DATETIME NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uniq_pegawai_tanggal` (`pegawai_id`, `tanggal`),
                INDEX `idx_log_tanggal` (`tanggal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        if (!is_dir(dirname($stampFile))) {
            @mkdir(dirname($stampFile), 0755, true);
        }
        @file_put_contents($stampFile, (string)time());
    }

    /**
     * Normalisasi string jam ke format HH:MM:SS
     */
    private static function normalizeTimeStr(?string $val, string $fallback): string
    {
        $clean = trim((string)($val ?? ''));
        if ($clean === '') {
            $clean = $fallback;
        }
        return strlen($clean) === 5 ? $clean . ':00' : $clean;
    }

    /**
     * Tentukan jam kerja global efektif untuk tanggal tertentu (mendukung jam khusus Hari Sabtu)
     */
    public static function resolveGlobalJamForDate(array $global, ?string $tanggal = null): array
    {
        $ts = $tanggal ? strtotime($tanggal) : time();
        if ($ts === false) {
            $ts = time();
        }
        $isSaturday = ((int)date('N', $ts) === 6);
        $sabtuAktif = !empty($global['sabtu_khusus_aktif']);

        if ($isSaturday && $sabtuAktif && !empty($global['sabtu_jam_masuk_batas'])) {
            return [
                'jam_masuk_mulai'  => !empty($global['sabtu_jam_masuk_mulai']) ? $global['sabtu_jam_masuk_mulai'] : ($global['jam_masuk_mulai'] ?: '06:00:00'),
                'jam_masuk_batas'  => $global['sabtu_jam_masuk_batas'],
                'jam_pulang_mulai' => !empty($global['sabtu_jam_pulang_mulai']) ? $global['sabtu_jam_pulang_mulai'] : ($global['jam_pulang_mulai'] ?: '15:00:00'),
                'is_sabtu_khusus'  => true,
            ];
        }

        return [
            'jam_masuk_mulai'  => $global['jam_masuk_mulai'] ?: '06:00:00',
            'jam_masuk_batas'  => $global['jam_masuk_batas'] ?: '07:30:00',
            'jam_pulang_mulai' => $global['jam_pulang_mulai'] ?: '15:00:00',
            'is_sabtu_khusus'  => false,
        ];
    }

    /**
     * Ambil Unit Tugas efektif pengguna yang sedang login.
     * Jika Super Admin, mengembalikan null (akses seluruh unit).
     * Jika user unit tertentu (PAUD, SD, SMP, SMA, Yayasan, Staff), mengembalikan unit tersebut.
     */
    public static function getEffectiveUserUnit(): ?string
    {
        if (class_exists('Auth') && Auth::isSuperAdmin()) {
            return null;
        }

        // 1. Cek restriction jenjang RBAC
        if (class_exists('Auth') && Auth::isJenjangRestricted()) {
            $active = Auth::getActiveJenjang();
            if (!empty($active)) {
                return $active;
            }
            $allowed = Auth::getAllowedJenjang();
            if (!empty($allowed) && !empty($allowed[0])) {
                return $allowed[0];
            }
        }

        // 2. Cek pegawai yang tertaut ke akun user
        if (class_exists('Auth')) {
            $linked = Auth::findLinkedPegawai();
            if ($linked) {
                $u = trim((string)($linked['unit_tugas'] ?? ''));
                if ($u !== '') {
                    return $u;
                }
                // Fallback: cek riwayat penugasan pegawai
                try {
                    $pid = (int)($linked['id'] ?? 0);
                    if ($pid > 0) {
                        $assignUnit = self::db()->findColumn("
                            SELECT mu.nama 
                            FROM pegawai_penugasan pp 
                            JOIN master_unit_tugas mu ON mu.id = pp.unit_tugas_id 
                            WHERE pp.pegawai_id = ? 
                            ORDER BY pp.id DESC LIMIT 1
                        ", [$pid]);
                        if (!empty($assignUnit)) {
                            return trim($assignUnit);
                        }
                    }
                } catch (Throwable $e) {}
            }
        }

        // 3. Cek allowed_jenjang pada user
        if (class_exists('Auth')) {
            $u = Auth::user();
            if (!empty($u['allowed_jenjang'])) {
                $parts = array_map('trim', explode(',', $u['allowed_jenjang']));
                if (!empty($parts[0])) {
                    return $parts[0];
                }
            }
        }

        // 4. Session jenjang
        if (!empty($_SESSION['user_active_jenjang'])) {
            return $_SESSION['user_active_jenjang'];
        }

        return null;
    }

    /**
     * Bangun kondisi SQL filtering Unit Tugas yang aman dan konsisten.
     * Mengunci filter ke unit user jika bukan Super Admin.
     */
    public static function getUnitFilterSql(string $column, array &$params, ?string $requestedUnit = null): string
    {
        $effective = self::getEffectiveUserUnit();
        $targetUnit = $effective ?: $requestedUnit;

        if (empty($targetUnit) || in_array(strtolower($targetUnit), ['semua', 'all', 'semua unit'], true)) {
            if (!empty($effective)) {
                $targetUnit = $effective;
            } else {
                return '1=1';
            }
        }

        $targetUnit = trim($targetUnit);
        $upper = strtoupper($targetUnit);

        if ($upper === 'PAUD' || $upper === 'TK') {
            $params[] = 'PAUD';
            $params[] = 'TK';
            $params[] = 'PAUD IT';
            $params[] = 'TK IT';
            $params[] = 'PAUD / TK';
            $params[] = 'PAUD/TK';
            return "(UPPER(TRIM({$column})) IN (?, ?, ?, ?, ?, ?) OR {$column} LIKE 'PAUD%' OR {$column} LIKE 'TK%')";
        }

        if ($upper === 'SD') {
            $params[] = 'SD';
            $params[] = 'SD IT';
            $params[] = 'SDIT';
            $params[] = 'SD IT BINA INSAN';
            return "(UPPER(TRIM({$column})) IN (?, ?, ?, ?) OR {$column} LIKE 'SD%')";
        }

        if ($upper === 'SMP') {
            $params[] = 'SMP';
            $params[] = 'SMP IT';
            $params[] = 'SMPIT';
            return "(UPPER(TRIM({$column})) IN (?, ?, ?) OR {$column} LIKE 'SMP%')";
        }

        if ($upper === 'SMA') {
            $params[] = 'SMA';
            $params[] = 'SMA IT';
            $params[] = 'SMAIT';
            return "(UPPER(TRIM({$column})) IN (?, ?, ?) OR {$column} LIKE 'SMA%')";
        }

        if (stripos($upper, 'YAYASAN') !== false || in_array($upper, ['UMUM', 'UMUM / YAYASAN'], true)) {
            $params[] = 'Yayasan';
            $params[] = 'Umum / Yayasan';
            $params[] = 'Umum';
            return "({$column} IS NULL OR TRIM({$column}) = '' OR UPPER(TRIM({$column})) IN (?, ?, ?) OR {$column} LIKE '%Yayasan%')";
        }

        if ($upper === 'STAFF') {
            $params[] = 'Staff';
            $params[] = 'Support System';
            return "(UPPER(TRIM({$column})) IN (?, ?) OR {$column} LIKE '%Staff%')";
        }

        $params[] = $targetUnit;
        $params[] = '%' . $targetUnit . '%';
        return "(UPPER(TRIM({$column})) = UPPER(?) OR {$column} LIKE ?)";
    }

    /**
     * Ambil data lengkap Dashboard Absen Pegawai:
     * 1. Daftar Pegawai Terlambat Hari Ini (Foto, Nama, NIY, Unit, Jam Masuk, Batas, Durasi Menit, Lokasi).
     * 2. KPI Statistik Presensi Hari Ini (Hadir Total, Tepat Waktu, Terlambat, Izin/Sakit/Cuti, Belum Absen, Persentase).
     * 3. Breakdown Persentase Kehadiran per Unit Tugas.
     * Semua otomatis disaring sesuai Unit pengguna yang sedang login.
     */
    public static function getDashboardData(?string $tanggal = null, ?string $unit = null): array
    {
        self::init();
        $targetDate = !empty($tanggal) ? trim($tanggal) : date('Y-m-d');
        $global = self::getPengaturanGlobal();
        $globalJam = self::resolveGlobalJamForDate($global, $targetDate);
        $jamBatasFallback = addslashes($globalJam['jam_masuk_batas'] ?: '07:30:00');

        $effectiveUnit = self::getEffectiveUserUnit();
        $targetUnit = $effectiveUnit ?: $unit;

        $where = ["p.is_active = 1"];
        $params = [$targetDate];

        $unitColExpr = "COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')";
        $uCond = self::getUnitFilterSql($unitColExpr, $params, $targetUnit);
        if ($uCond !== '1=1') {
            $where[] = "({$uCond})";
        }
        $whereSql = implode(' AND ', $where);

        $sql = "
            SELECT 
                p.id, p.niy, p.nama, p.gelar, p.foto, p.jabatan,
                COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan') as unit_tugas_efektif,
                apl.id as log_id,
                apl.jam_masuk,
                apl.jam_pulang,
                apl.status as log_status,
                apl.keterangan as log_keterangan,
                apl.nama_kantor_absen,
                apl.jarak_masuk_meter,
                apl.lat_masuk,
                apl.lng_masuk,
                apl.evaluasi,
                apl.tindak_lanjut,
                apl.evaluasi_oleh,
                apl.evaluasi_at,
                COALESCE(l.jam_masuk_batas, u.jam_masuk_batas, '{$jamBatasFallback}') as batas_jam_masuk
            FROM `pegawai` p
            LEFT JOIN `pegawai_penugasan` pp ON pp.pegawai_id = p.id
            LEFT JOIN `master_unit_tugas` mu ON mu.id = pp.unit_tugas_id
            LEFT JOIN `absen_pegawai_lokasi` l ON l.pegawai_id = p.id
            LEFT JOIN `absen_pegawai_lokasi_unit` u ON u.unit_tugas = COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')
            LEFT JOIN `absen_pegawai_log` apl ON apl.pegawai_id = p.id AND apl.tanggal = ?
            WHERE {$whereSql}
            GROUP BY p.id
            ORDER BY 
                CASE 
                    WHEN apl.status = 'Terlambat' THEN 1 
                    WHEN apl.jam_masuk IS NOT NULL AND apl.jam_masuk > COALESCE(l.jam_masuk_batas, u.jam_masuk_batas, '{$jamBatasFallback}') THEN 2
                    WHEN apl.status = 'Hadir' THEN 3
                    WHEN apl.jam_masuk IS NOT NULL THEN 4
                    ELSE 5 
                END,
                apl.jam_masuk DESC,
                p.nama ASC
        ";

        $rows = self::db()->findAll($sql, $params);

        $totalPegawai = count($rows);
        $hadirTotal = 0;
        $hadirTepatWaktu = 0;
        $terlambat = 0;
        $izin = 0;
        $sakit = 0;
        $cuti = 0;
        $dinasLuar = 0;
        $daftarTerlambat = [];
        $unitStatsMap = [];

        foreach ($rows as $r) {
            $unitName = $r['unit_tugas_efektif'] ?: 'Yayasan';
            if (!isset($unitStatsMap[$unitName])) {
                $unitStatsMap[$unitName] = [
                    'unit_name' => $unitName,
                    'total_pegawai' => 0,
                    'hadir_total' => 0,
                    'hadir_tepat_waktu' => 0,
                    'terlambat' => 0,
                    'izin' => 0,
                    'sakit' => 0,
                    'cuti' => 0,
                    'belum_absen' => 0,
                    'persen_kehadiran' => 0.0
                ];
            }
            $unitStatsMap[$unitName]['total_pegawai']++;

            $jamMasuk = $r['jam_masuk'] ? substr($r['jam_masuk'], 0, 8) : null;
            $jamBatas = $r['batas_jam_masuk'] ? substr($r['batas_jam_masuk'], 0, 8) : '07:30:00';
            $logStatus = trim((string)($r['log_status'] ?? ''));

            $isLate = false;
            if ($logStatus === 'Terlambat') {
                $isLate = true;
            } elseif (!empty($jamMasuk) && strtotime($jamMasuk) > strtotime($jamBatas)) {
                $isLate = true;
            }

            if ($isLate) {
                $terlambat++;
                $hadirTotal++;
                $unitStatsMap[$unitName]['terlambat']++;
                $unitStatsMap[$unitName]['hadir_total']++;

                $menitTerlambat = 0;
                if (preg_match('/(\d+)\s*Menit/i', (string)($r['log_keterangan'] ?? ''), $m)) {
                    $menitTerlambat = (int)$m[1];
                } elseif (!empty($jamMasuk)) {
                    $menitTerlambat = max(1, (int)round((strtotime($jamMasuk) - strtotime($jamBatas)) / 60));
                }

                $gelar = trim((string)($r['gelar'] ?? ''));
                $gelarStr = ($gelar !== '' && stripos($r['nama'], $gelar) === false) ? ', ' . $gelar : '';
                $daftarTerlambat[] = [
                    'id'                => (int)$r['id'],
                    'pegawai_id'        => (int)$r['id'],
                    'nama'              => trim($r['nama'] . $gelarStr),
                    'nama_polos'        => $r['nama'],
                    'gelar'             => $r['gelar'],
                    'niy'               => $r['niy'] ?: '-',
                    'foto'              => $r['foto'],
                    'unit_tugas'        => $unitName,
                    'jabatan'           => $r['jabatan'] ?: 'Pegawai',
                    'jam_masuk'         => $jamMasuk,
                    'jam_masuk_batas'   => $jamBatas,
                    'menit_terlambat'   => $menitTerlambat,
                    'nama_kantor_absen' => $r['nama_kantor_absen'] ?: '-',
                    'jarak_masuk_meter' => (float)($r['jarak_masuk_meter'] ?? 0),
                    'lat_masuk'         => $r['lat_masuk'],
                    'lng_masuk'         => $r['lng_masuk'],
                    'keterangan'        => $r['log_keterangan'] ?: "Terlambat {$menitTerlambat} Menit",
                    'log_id'            => !empty($r['log_id']) ? (int)$r['log_id'] : null,
                    'jam_pulang'        => $r['jam_pulang'] ? substr($r['jam_pulang'], 0, 8) : null,
                    'evaluasi'          => $r['evaluasi'] ?? null,
                    'tindak_lanjut'     => $r['tindak_lanjut'] ?? null,
                    'evaluasi_oleh'     => $r['evaluasi_oleh'] ?? null,
                    'evaluasi_at'       => $r['evaluasi_at'] ?? null,
                ];
            } elseif ($logStatus === 'Hadir' || !empty($jamMasuk)) {
                $hadirTepatWaktu++;
                $hadirTotal++;
                $unitStatsMap[$unitName]['hadir_tepat_waktu']++;
                $unitStatsMap[$unitName]['hadir_total']++;
            } elseif ($logStatus === 'Izin') {
                $izin++;
                $unitStatsMap[$unitName]['izin']++;
            } elseif ($logStatus === 'Sakit') {
                $sakit++;
                $unitStatsMap[$unitName]['sakit']++;
            } elseif ($logStatus === 'Cuti') {
                $cuti++;
                $unitStatsMap[$unitName]['cuti']++;
            } elseif ($logStatus === 'Dinas Luar') {
                $dinasLuar++;
                $hadirTotal++;
                $unitStatsMap[$unitName]['hadir_total']++;
            } else {
                $unitStatsMap[$unitName]['belum_absen']++;
            }
        }

        $belumAbsen = max(0, $totalPegawai - ($hadirTotal + $izin + $sakit + $cuti));
        $persenKehadiran = $totalPegawai > 0 ? round(($hadirTotal / $totalPegawai) * 100, 1) : 0.0;
        $persenTepatWaktu = $totalPegawai > 0 ? round(($hadirTepatWaktu / $totalPegawai) * 100, 1) : 0.0;
        $persenTerlambat = $totalPegawai > 0 ? round(($terlambat / $totalPegawai) * 100, 1) : 0.0;

        foreach ($unitStatsMap as $uKey => &$uStat) {
            $uTot = $uStat['total_pegawai'];
            $uStat['persen_kehadiran'] = $uTot > 0 ? round(($uStat['hadir_total'] / $uTot) * 100, 1) : 0.0;
        }
        unset($uStat);
        ksort($unitStatsMap);

        return [
            'tanggal'          => $targetDate,
            'unit_efektif'     => $effectiveUnit,
            'target_unit'      => $targetUnit,
            'is_restricted'    => !empty($effectiveUnit),
            'global_jam'       => $globalJam,
            'libur_info'       => self::getLiburInfoForDate($targetDate),
            'stats'            => [
                'total_pegawai'      => $totalPegawai,
                'hadir_total'        => $hadirTotal,
                'hadir_tepat_waktu'  => $hadirTepatWaktu,
                'terlambat'          => $terlambat,
                'izin'               => $izin,
                'sakit'              => $sakit,
                'cuti'               => $cuti,
                'dinas_luar'         => $dinasLuar,
                'belum_absen'        => $belumAbsen,
                'persen_kehadiran'   => $persenKehadiran,
                'persen_tepat_waktu' => $persenTepatWaktu,
                'persen_terlambat'   => $persenTerlambat,
            ],
            'daftar_terlambat' => $daftarTerlambat,
            'unit_breakdown'   => array_values($unitStatsMap),
        ];
    }

    /**
     * Ambil Rekapitulasi Presensi Pegawai Periode (Bulanan/Kustom) untuk tabel Data Absen & Cetak PDF.
     * Kolom Rekap:
     * - No, Nama Pegawai, NIY, Unit Tugas, Jabatan
     * - Jumlah Hari Kerja, Jumlah Hadir, Jumlah Hadir Tanpa Terlambat
     * - Terlambat, Izin, Sakit, Cuti, Alpa
     * - Total Kehadiran (%)
     */
    public static function getRekapPeriode(string $startDate, string $endDate, ?string $unit = null, ?string $search = null): array
    {
        self::init();
        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        if ($startTs > $endTs) {
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
            $startTs = strtotime($startDate);
            $endTs = strtotime($endDate);
        }

        $todayTs = strtotime(date('Y-m-d'));
        $evalEndTs = min($endTs, $todayTs);

        // Hitung Jumlah Hari Kerja Efektif (Senin - Sabtu, abaikan Minggu & tanggal libur terdaftar)
        $evalStartStr = date('Y-m-d', $startTs);
        $evalEndStr = date('Y-m-d', $evalEndTs);
        $liburMap = ($startTs <= $evalEndTs) ? self::getLiburMap($evalStartStr, $evalEndStr) : [];
        $workInfo = ($startTs <= $evalEndTs)
            ? self::hitungHariKerjaRange($evalStartStr, $evalEndStr, $liburMap)
            : ['jumlah_hari_kerja' => 0, 'jumlah_minggu' => 0, 'jumlah_libur_khusus' => 0, 'libur_dates' => [], 'working_dates' => []];
        $jumlahHariKerja = (int)$workInfo['jumlah_hari_kerja'];

        // Hitung Periode 1 dan Periode 2 (jika lintas bulan: bulan awal vs bulan akhir; jika 1 bulan: tgl 1-15 vs 16-akhir)
        $periode1Hari = 0;
        $periode2Hari = 0;
        if ($jumlahHariKerja > 0 && !empty($workInfo['working_dates'])) {
            $startYm = date('Y-m', $startTs);
            $endYm = date('Y-m', $evalEndTs);
            if ($startYm !== $endYm) {
                foreach ($workInfo['working_dates'] as $wd) {
                    if (substr($wd, 0, 7) === $startYm) {
                        $periode1Hari++;
                    } else {
                        $periode2Hari++;
                    }
                }
            } else {
                foreach ($workInfo['working_dates'] as $wd) {
                    if ((int)substr($wd, 8, 2) <= 15) {
                        $periode1Hari++;
                    } else {
                        $periode2Hari++;
                    }
                }
            }
        }

        $effectiveUnit = self::getEffectiveUserUnit();
        $targetUnit = $effectiveUnit ?: $unit;

        $where = ["p.is_active = 1"];
        $params = [];

        $unitColExpr = "COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')";
        $uCond = self::getUnitFilterSql($unitColExpr, $params, $targetUnit);
        if ($uCond !== '1=1') {
            $where[] = "({$uCond})";
        }

        if (!empty($search)) {
            $term = '%' . trim($search) . '%';
            $where[] = "(p.nama LIKE ? OR p.niy LIKE ? OR p.jabatan LIKE ? OR p.jenis_pegawai LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $sqlPegawai = "
            SELECT 
                p.id, p.niy, p.nama, p.gelar, p.foto, p.jabatan, p.jenis_pegawai,
                COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan') as unit_tugas_efektif,
                l.jam_masuk_mulai as peg_jam_masuk_mulai,
                l.jam_masuk_batas as peg_jam_masuk_batas,
                l.jam_pulang_mulai as peg_jam_pulang_mulai,
                u.is_custom as unit_is_custom,
                u.jam_masuk_mulai as unit_jam_masuk_mulai,
                u.jam_masuk_batas as unit_jam_masuk_batas,
                u.jam_pulang_mulai as unit_jam_pulang_mulai
            FROM `pegawai` p
            LEFT JOIN `pegawai_penugasan` pp ON pp.pegawai_id = p.id
            LEFT JOIN `master_unit_tugas` mu ON mu.id = pp.unit_tugas_id
            LEFT JOIN `absen_pegawai_lokasi` l ON l.pegawai_id = p.id
            LEFT JOIN `absen_pegawai_lokasi_unit` u ON u.unit_tugas = COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')
            WHERE {$whereSql}
            GROUP BY p.id
            ORDER BY 
                CASE 
                    WHEN LOWER(COALESCE(p.jenis_pegawai, '')) = 'guru'
                      OR LOWER(COALESCE(p.jabatan, '')) LIKE '%guru%'
                      OR LOWER(COALESCE(p.jabatan, '')) LIKE '%wali kelas%'
                    THEN 0 ELSE 1 
                END ASC,
                p.nama ASC
        ";

        $pegawaiList = self::db()->findAll($sqlPegawai, $params);
        $globalSettings = self::getPengaturanGlobal();
        $jamHarianMap = self::getAllJamHarianMap();

        // Ambil seluruh log absensi dalam rentang periode
        $sqlLogs = "
            SELECT 
                apl.pegawai_id,
                apl.tanggal,
                apl.jam_masuk,
                apl.status,
                apl.keterangan
            FROM `absen_pegawai_log` apl
            WHERE apl.tanggal BETWEEN ? AND ?
        ";
        $logs = self::db()->findAll($sqlLogs, [$startDate, $endDate]);

        $logsByPegawai = [];
        foreach ($logs as $l) {
            $logsByPegawai[$l['pegawai_id']][] = $l;
        }

        // Ambil cuti disetujui jika ada (pisahkan Tahunan -> Cuti, Khusus -> Izin Khusus)
        $cutiMap = [];
        try {
            $cutiRows = self::db()->findAll("
                SELECT pegawai_id, kategori_cuti, SUM(jumlah_hari) as total_cuti 
                FROM `cuti_pengajuan` 
                WHERE status = 'Disetujui' AND tanggal_mulai <= ? AND tanggal_selesai >= ?
                GROUP BY pegawai_id, kategori_cuti
            ", [$endDate, $startDate]);
            foreach ($cutiRows as $cr) {
                $pid = (int)$cr['pegawai_id'];
                if (!isset($cutiMap[$pid])) {
                    $cutiMap[$pid] = ['cuti' => 0, 'izin_khusus' => 0];
                }
                if (strcasecmp((string)($cr['kategori_cuti'] ?? ''), 'Khusus') === 0) {
                    $cutiMap[$pid]['izin_khusus'] += (int)$cr['total_cuti'];
                } else {
                    $cutiMap[$pid]['cuti'] += (int)$cr['total_cuti'];
                }
            }
        } catch (Throwable $e) {}

        // Ambil izin guru disetujui jika ada (pisahkan Sakit, Dinas Luar -> Tugas, lainnya -> Izin)
        $izinMap = [];
        try {
            $izinRows = self::db()->findAll("
                SELECT guru_id as pegawai_id, kategori, COUNT(*) as cnt 
                FROM `izin_guru` 
                WHERE status = 'Disetujui' AND tanggal_mulai <= ? AND tanggal_selesai >= ?
                GROUP BY guru_id, kategori
            ", [$endDate, $startDate]);
            foreach ($izinRows as $ir) {
                $pid = (int)$ir['pegawai_id'];
                $kat = strtolower(trim((string)$ir['kategori']));
                if (!isset($izinMap[$pid])) {
                    $izinMap[$pid] = ['izin' => 0, 'sakit' => 0, 'tugas' => 0];
                }
                if ($kat === 'sakit') {
                    $izinMap[$pid]['sakit'] += (int)$ir['cnt'];
                } elseif ($kat === 'dinas luar') {
                    $izinMap[$pid]['tugas'] += (int)$ir['cnt'];
                } else {
                    $izinMap[$pid]['izin'] += (int)$ir['cnt'];
                }
            }
        } catch (Throwable $e) {}

        $rows = [];
        $guruRows = [];
        $supportRows = [];
        $noGuru = 1;
        $noSupport = 1;

        $totalHadirSemua = 0;
        $totalTepatWaktuSemua = 0;
        $totalTerlambatSemua = 0;
        $totalTerlambatFingerSemua = 0;
        $totalTerlambatPiketSemua = 0;
        $totalIzinSemua = 0;
        $totalIzinKhususSemua = 0;
        $totalSakitSemua = 0;
        $totalCutiSemua = 0;
        $totalTugasSemua = 0;
        $totalAlpaSemua = 0;
        $sumPersen = 0;
        $sumDisiplin = 0;
        $countDisiplinPegawai = 0;

        foreach ($pegawaiList as $p) {
            $pid = (int)$p['id'];
            $pegLogs = $logsByPegawai[$pid] ?? [];
            $dailyMap = $jamHarianMap[$pid] ?? [];

            $jenisPegawai = trim((string)($p['jenis_pegawai'] ?? ''));
            $jabatanStr = trim((string)($p['jabatan'] ?? ''));
            $isGuru = (strcasecmp($jenisPegawai, 'Guru') === 0)
                || (stripos($jabatanStr, 'guru') !== false)
                || (stripos($jabatanStr, 'wali kelas') !== false);
            $kategoriGroup = $isGuru ? 'guru' : 'support_system';

            $hadirCount = 0;
            $terlambatFingerCount = 0;
            $terlambatPiketCount = 0;
            $totalTerlambatCount = 0;
            $izinCount = 0;
            $izinKhususCount = 0;
            $sakitCount = 0;
            $cutiCount = 0;
            $tugasCount = 0;
            $alpaExplicitCount = 0;
            $kehadiranLainCount = 0;

            foreach ($pegLogs as $pl) {
                $st = trim((string)($pl['status'] ?? ''));
                $tglLog = (string)($pl['tanggal'] ?? '');
                $jamMasuk = trim((string)($pl['jam_masuk'] ?? ''));

                if ($st === 'Terlambat' || $st === 'Hadir' || $jamMasuk !== '') {
                    $hadirCount++;

                    // Resolve batas jam masuk efektif untuk tanggal log ini
                    $resolvedJam = self::resolvePegawaiJamForDate($p, $dailyMap, $globalSettings, $tglLog);
                    $batasEfektif = self::normalizeTimeStr($resolvedJam['jam_masuk_batas'] ?? null, '07:30:00');
                    $isPiketSchedule = ($isGuru && substr($batasEfektif, 0, 5) <= '06:30');

                    $isLatePiket = false;
                    $isLateFinger = false;

                    if ($isPiketSchedule) {
                        // Aturan Piket Guru (jadwal 06:30): hadir > 06:30:00 (misal 06:31) => Terlambat Piket & otomatis Terlambat Finger
                        if (($jamMasuk !== '' && substr($jamMasuk, 0, 8) > '06:30:00') || $st === 'Terlambat') {
                            $isLatePiket = true;
                            $isLateFinger = true;
                        }
                    } else {
                        if ($st === 'Terlambat' || ($jamMasuk !== '' && substr($jamMasuk, 0, 8) > $batasEfektif)) {
                            $isLateFinger = true;
                        }
                    }

                    if ($isLatePiket) {
                        $terlambatPiketCount++;
                    }
                    if ($isLateFinger) {
                        $terlambatFingerCount++;
                    }
                    if ($isLatePiket || $isLateFinger) {
                        $totalTerlambatCount++;
                    }
                } elseif ($st === 'Izin') {
                    $izinCount++;
                } elseif ($st === 'Sakit') {
                    $sakitCount++;
                } elseif ($st === 'Cuti') {
                    $cutiCount++;
                } elseif ($st === 'Dinas Luar') {
                    $tugasCount++;
                } elseif (in_array($st, ['Alpha', 'Alpa'], true)) {
                    $alpaExplicitCount++;
                }
            }

            // Gabungkan dengan data izin/cuti disetujui jika di log belum tercatat
            if ($cutiCount === 0 && !empty($cutiMap[$pid]['cuti'])) {
                $cutiCount = $cutiMap[$pid]['cuti'];
            }
            if ($izinKhususCount === 0 && !empty($cutiMap[$pid]['izin_khusus'])) {
                $izinKhususCount = $cutiMap[$pid]['izin_khusus'];
            }
            if ($izinCount === 0 && !empty($izinMap[$pid]['izin'])) {
                $izinCount = $izinMap[$pid]['izin'];
            }
            if ($sakitCount === 0 && !empty($izinMap[$pid]['sakit'])) {
                $sakitCount = $izinMap[$pid]['sakit'];
            }
            if ($tugasCount === 0 && !empty($izinMap[$pid]['tugas'])) {
                $tugasCount = $izinMap[$pid]['tugas'];
            }

            $hadirTepatWaktuCount = max(0, $hadirCount - $totalTerlambatCount);

            // Alpa dihitung dari sisa hari kerja efektif yang tidak ada keterangan kehadiran
            if ($jumlahHariKerja > 0) {
                $calcAlpa = max(0, $jumlahHariKerja - ($hadirCount + $izinCount + $izinKhususCount + $sakitCount + $cutiCount + $tugasCount));
                $alpa = max($calcAlpa, $alpaExplicitCount);

                $persen = round(($hadirCount / $jumlahHariKerja) * 100, 0);
                $persen = min(100.0, max(0.0, $persen));
            } else {
                $alpa = $alpaExplicitCount;
                $persen = 0.0;
            }

            $persenDisiplin = $hadirCount > 0 ? round(($hadirTepatWaktuCount / $hadirCount) * 100, 0) : 0.0;
            $persenDisiplin = min(100.0, max(0.0, $persenDisiplin));
            if ($hadirCount > 0) {
                $sumDisiplin += $persenDisiplin;
                $countDisiplinPegawai++;
            }

            $totalHadirSemua += $hadirCount;
            $totalTepatWaktuSemua += $hadirTepatWaktuCount;
            $totalTerlambatSemua += $totalTerlambatCount;
            $totalTerlambatFingerSemua += $terlambatFingerCount;
            $totalTerlambatPiketSemua += $terlambatPiketCount;
            $totalIzinSemua += $izinCount;
            $totalIzinKhususSemua += $izinKhususCount;
            $totalSakitSemua += $sakitCount;
            $totalCutiSemua += $cutiCount;
            $totalTugasSemua += $tugasCount;
            $totalAlpaSemua += $alpa;
            $sumPersen += $persen;

            $gelar = trim((string)($p['gelar'] ?? ''));
            $gelarStr = ($gelar !== '' && stripos($p['nama'], $gelar) === false) ? ', ' . $gelar : '';
            $item = [
                'no'                      => $isGuru ? $noGuru++ : $noSupport++,
                'pegawai_id'              => $pid,
                'nama'                    => trim($p['nama'] . $gelarStr),
                'nama_polos'              => $p['nama'],
                'gelar'                   => $p['gelar'],
                'niy'                     => $p['niy'] ?: '-',
                'foto'                    => $p['foto'],
                'unit_tugas'              => $p['unit_tugas_efektif'] ?: 'Yayasan',
                'jabatan'                 => $p['jabatan'] ?: ($isGuru ? 'Guru' : 'Support System'),
                'jenis_pegawai'           => $jenisPegawai ?: ($isGuru ? 'Guru' : 'Support System'),
                'kategori_group'          => $kategoriGroup,
                'kehadiran_lain'          => $kehadiranLainCount,
                'periode_1'               => $periode1Hari,
                'periode_2'               => $periode2Hari,
                'jumlah_hari_kerja'       => $jumlahHariKerja,
                'jumlah_hadir'            => $hadirCount,
                'terlambat'               => $totalTerlambatCount,
                'terlambat_finger'        => $terlambatFingerCount,
                'terlambat_piket'         => $terlambatPiketCount,
                'hadir_tanpa_terlambat'   => $hadirTepatWaktuCount,
                'izin'                    => $izinCount,
                'izin_khusus'             => $izinKhususCount,
                'sakit'                   => $sakitCount,
                'alpa'                    => $alpa,
                'cuti'                    => $cutiCount,
                'tugas'                   => $tugasCount,
                'total_kehadiran'         => $persen,
                'presentasi_kedisiplinan' => $persenDisiplin,
            ];

            $rows[] = $item;
            if ($isGuru) {
                $guruRows[] = $item;
            } else {
                $supportRows[] = $item;
            }
        }

        $totalPeg = count($rows);
        $rataRata = $totalPeg > 0 ? round($sumPersen / $totalPeg, 1) : 0.0;
        $rataRataDisiplin = $countDisiplinPegawai > 0 ? round($sumDisiplin / $countDisiplinPegawai, 1) : 0.0;

        $kepalaSekolahList = self::getKepalaSekolahByUnit($targetUnit, $rows);

        return [
            'start_date'          => $startDate,
            'end_date'            => $endDate,
            'jumlah_hari_kerja'   => $jumlahHariKerja,
            'periode_1_hari'      => $periode1Hari,
            'periode_2_hari'      => $periode2Hari,
            'jumlah_minggu'       => (int)($workInfo['jumlah_minggu'] ?? 0),
            'jumlah_libur_khusus' => (int)($workInfo['jumlah_libur_khusus'] ?? 0),
            'libur_list'          => $workInfo['libur_dates'] ?? [],
            'unit_efektif'        => $effectiveUnit,
            'target_unit'         => $targetUnit,
            'is_restricted'       => !empty($effectiveUnit),
            'kepala_sekolah_list' => $kepalaSekolahList,
            'summary'             => [
                'total_pegawai'               => $totalPeg,
                'total_guru'                  => count($guruRows),
                'total_support'               => count($supportRows),
                'rata_rata_kehadiran'         => $rataRata,
                'rata_rata_kedisiplinan'      => $rataRataDisiplin,
                'total_hadir_semua'           => $totalHadirSemua,
                'total_tepat_waktu_semua'     => $totalTepatWaktuSemua,
                'total_terlambat_semua'       => $totalTerlambatSemua,
                'total_terlambat_finger_semua'=> $totalTerlambatFingerSemua,
                'total_terlambat_piket_semua' => $totalTerlambatPiketSemua,
                'total_izin_semua'            => $totalIzinSemua,
                'total_izin_khusus_semua'     => $totalIzinKhususSemua,
                'total_sakit_semua'           => $totalSakitSemua,
                'total_cuti_semua'            => $totalCutiSemua,
                'total_tugas_semua'           => $totalTugasSemua,
                'total_alpa_semua'            => $totalAlpaSemua,
            ],
            'groups'              => [
                'guru'           => $guruRows,
                'support_system' => $supportRows,
            ],
            'rows'                => $rows
        ];
    }

    /**
     * Ambil daftar Kepala Sekolah per unit untuk blok tanda tangan rekap absen
     */
    public static function getKepalaSekolahByUnit(?string $targetUnit = null, array $rows = []): array
    {
        self::init();
        $kepsekRows = self::db()->findAll("
            SELECT 
                p.id, p.niy, p.nama, p.gelar, p.jabatan,
                COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan') as unit_tugas_efektif
            FROM `pegawai` p
            LEFT JOIN `pegawai_penugasan` pp ON pp.pegawai_id = p.id
            LEFT JOIN `master_unit_tugas` mu ON mu.id = pp.unit_tugas_id
            WHERE p.is_active = 1
              AND (
                  LOWER(COALESCE(p.jabatan, '')) LIKE '%kepala sekolah%'
                  OR LOWER(COALESCE(p.jabatan, '')) LIKE '%kepsek%'
                  OR LOWER(COALESCE(p.jabatan, '')) LIKE '%kepala unit%'
              )
            GROUP BY p.id
            ORDER BY unit_tugas_efektif ASC, p.nama ASC
        ");

        $byUnit = [];
        foreach ($kepsekRows as $kr) {
            $u = trim((string)($kr['unit_tugas_efektif'] ?? ''));
            if ($u === '') continue;
            $gelar = trim((string)($kr['gelar'] ?? ''));
            $gelarStr = ($gelar !== '' && stripos($kr['nama'], $gelar) === false) ? ', ' . $gelar : '';
            $byUnit[strtoupper($u)] = [
                'unit'    => $u,
                'jabatan' => 'Kepala Sekolah ' . $u,
                'nama'    => trim($kr['nama'] . $gelarStr),
                'niy'     => $kr['niy'] ?: '-',
            ];
        }

        if (!empty($targetUnit)) {
            $key = strtoupper(trim($targetUnit));
            if (isset($byUnit[$key])) {
                return [$byUnit[$key]];
            }
            return [[
                'unit'    => $targetUnit,
                'jabatan' => 'Kepala Sekolah ' . $targetUnit,
                'nama'    => '( ........................................ )',
                'niy'     => '................................',
            ]];
        }

        // Ambil unit sekolah aktif dari master_unit_tugas atau rows (utamakan PAUD/TK, SD, SMP, SMA)
        $units = [];
        try {
            $masterUnits = self::db()->findAll("SELECT nama FROM `master_unit_tugas` ORDER BY id ASC");
            foreach ($masterUnits as $mu) {
                $uName = trim((string)($mu['nama'] ?? ''));
                if ($uName !== '' && !in_array(strtolower($uName), ['yayasan', 'staff'], true)) {
                    $units[strtoupper($uName)] = $uName;
                }
            }
        } catch (Throwable $e) {}

        foreach ($rows as $r) {
            $uName = trim((string)($r['unit_tugas'] ?? ''));
            if ($uName !== '' && !in_array(strtolower($uName), ['yayasan', 'staff'], true)) {
                $units[strtoupper($uName)] = $uName;
            }
        }

        if (empty($units)) {
            $units = ['PAUD' => 'PAUD', 'SD' => 'SD', 'SMP' => 'SMP', 'SMA' => 'SMA'];
        }

        $result = [];
        foreach ($units as $key => $uName) {
            if (isset($byUnit[$key])) {
                $result[] = $byUnit[$key];
            } else {
                $result[] = [
                    'unit'    => $uName,
                    'jabatan' => 'Kepala Sekolah ' . $uName,
                    'nama'    => '( ........................................ )',
                    'niy'     => '................................',
                ];
            }
        }

        return $result;
    }

    /**
     * Ambil pengaturan global (Lokasi Semula/Default & Radius Global)
     */
    public static function getPengaturanGlobal(): array
    {
        self::init();
        $row = self::db()->find("SELECT * FROM `absen_pegawai_pengaturan` WHERE `id` = 1");
        if (!$row) {
            return [
                'id' => 1,
                'nama_kantor_default' => 'Kampus Utama SIT Bina Insan Palu',
                'latitude_default' => -0.8917000,
                'longitude_default' => 119.8707000,
                'radius_meter' => 10,
                'jam_masuk_mulai' => '06:00:00',
                'jam_masuk_batas' => '07:30:00',
                'jam_pulang_mulai' => '15:00:00',
                'periode_awal' => date('Y-m-01'),
                'periode_akhir' => date('Y-m-t'),
                'sabtu_khusus_aktif' => 0,
                'sabtu_jam_masuk_mulai' => '06:00:00',
                'sabtu_jam_masuk_batas' => '07:30:00',
                'sabtu_jam_pulang_mulai' => '15:00:00',
            ];
        }
        if (empty($row['periode_awal'])) {
            $row['periode_awal'] = date('Y-m-01');
        }
        if (empty($row['periode_akhir'])) {
            $row['periode_akhir'] = date('Y-m-t');
        }
        $row['sabtu_khusus_aktif'] = (int)($row['sabtu_khusus_aktif'] ?? 0);
        $row['sabtu_jam_masuk_mulai'] = !empty($row['sabtu_jam_masuk_mulai']) ? $row['sabtu_jam_masuk_mulai'] : ($row['jam_masuk_mulai'] ?? '06:00:00');
        $row['sabtu_jam_masuk_batas'] = !empty($row['sabtu_jam_masuk_batas']) ? $row['sabtu_jam_masuk_batas'] : '07:30:00';
        $row['sabtu_jam_pulang_mulai'] = !empty($row['sabtu_jam_pulang_mulai']) ? $row['sabtu_jam_pulang_mulai'] : ($row['jam_pulang_mulai'] ?? '15:00:00');
        return $row;
    }

    /**
     * Simpan pengaturan global (Radius Global untuk semua + Lokasi Semula/Default + Periode Rekap Absen)
     */
    public static function savePengaturanGlobal(array $data, ?int $userId = null): void
    {
        self::init();
        $namaKantor = trim($data['nama_kantor_default'] ?? 'Kampus Utama SIT Bina Insan Palu');
        if ($namaKantor === '') {
            $namaKantor = 'Kampus Utama SIT Bina Insan Palu';
        }
        $lat = (float)($data['latitude_default'] ?? -0.8917000);
        $lng = (float)($data['longitude_default'] ?? 119.8707000);
        $radius = max(1, (int)($data['radius_meter'] ?? 10));
        $jamMasukMulai = self::normalizeTimeStr($data['jam_masuk_mulai'] ?? null, '06:00:00');
        $jamMasukBatas = self::normalizeTimeStr($data['jam_masuk_batas'] ?? null, '07:30:00');
        $jamPulangMulai = self::normalizeTimeStr($data['jam_pulang_mulai'] ?? null, '15:00:00');
        $periodeAwal = !empty($data['periode_awal']) ? trim($data['periode_awal']) : date('Y-m-01');
        $periodeAkhir = !empty($data['periode_akhir']) ? trim($data['periode_akhir']) : date('Y-m-t');

        $sabtuAktif = !empty($data['sabtu_khusus_aktif']) ? 1 : 0;
        $sabtuMulai = self::normalizeTimeStr($data['sabtu_jam_masuk_mulai'] ?? null, $jamMasukMulai);
        $sabtuBatas = self::normalizeTimeStr($data['sabtu_jam_masuk_batas'] ?? null, '07:30:00');
        $sabtuPulang = self::normalizeTimeStr($data['sabtu_jam_pulang_mulai'] ?? null, $jamPulangMulai);

        self::db()->query("
            UPDATE `absen_pegawai_pengaturan`
            SET `nama_kantor_default` = ?,
                `latitude_default` = ?,
                `longitude_default` = ?,
                `radius_meter` = ?,
                `jam_masuk_mulai` = ?,
                `jam_masuk_batas` = ?,
                `jam_pulang_mulai` = ?,
                `periode_awal` = ?,
                `periode_akhir` = ?,
                `sabtu_khusus_aktif` = ?,
                `sabtu_jam_masuk_mulai` = ?,
                `sabtu_jam_masuk_batas` = ?,
                `sabtu_jam_pulang_mulai` = ?,
                `updated_by` = ?
            WHERE `id` = 1
        ", [$namaKantor, $lat, $lng, $radius, $jamMasukMulai, $jamMasukBatas, $jamPulangMulai, $periodeAwal, $periodeAkhir, $sabtuAktif, $sabtuMulai, $sabtuBatas, $sabtuPulang, $userId]);

        // Perbarui juga baris di absen_pegawai_lokasi yang mode_lokasi = 'default' agar selaras dengan lokasi semula terbaru
        self::db()->query("
            UPDATE `absen_pegawai_lokasi`
            SET `nama_kantor` = ?, `latitude` = ?, `longitude` = ?
            WHERE `mode_lokasi` = 'default'
        ", [$namaKantor, $lat, $lng]);
    }

    /**
     * Ambil rekapitulasi presensi individu pegawai dalam rentang tanggal tertentu
     * Menyajikan ringkasan KPI, daftar hari terlambat, dan daftar hari tidak absen (alpa)
     */
    public static function getRekapAbsenPegawai(int $pegawaiId, ?string $startDate = null, ?string $endDate = null): array
    {
        self::init();
        $global = self::getPengaturanGlobal();

        $startDate = !empty($startDate) ? trim($startDate) : ($global['periode_awal'] ?: date('Y-m-01'));
        $endDate   = !empty($endDate) ? trim($endDate) : ($global['periode_akhir'] ?: date('Y-m-d'));
        if (strtotime($startDate) > strtotime($endDate)) {
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
        }

        $today = date('Y-m-d');
        // Hanya evaluasi hari kerja sampai hari ini (jangan tandai hari depan sebagai alpa)
        $evalEnd = (strtotime($endDate) > strtotime($today)) ? $today : $endDate;

        // Ambil semua log absensi guru dalam rentang tanggal
        $logs = self::db()->findAll(
            "SELECT * FROM `absen_pegawai_log` 
             WHERE `pegawai_id` = ? AND `tanggal` BETWEEN ? AND ? 
             ORDER BY `tanggal` ASC",
            [$pegawaiId, $startDate, $endDate]
        );

        $logsByDate = [];
        foreach ($logs as $l) {
            $logsByDate[$l['tanggal']] = $l;
        }

        $liburMap = (strtotime($evalEnd) >= strtotime($startDate))
            ? self::getLiburMap($startDate, $evalEnd)
            : [];

        $dayMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];

        $totalHariKerja = 0;
        $totalLiburKhusus = 0;
        $totalHadir = 0;
        $totalTepatWaktu = 0;
        $totalTerlambat = 0;
        $totalMenitTerlambat = 0;
        $totalTidakAbsen = 0;

        $listTerlambat = [];
        $listTidakAbsen = [];
        $allDaysHistory = [];

        // Loop dari startDate s/d evalEnd untuk hari-hari yang sudah/sedang berjalan
        $currentTs = strtotime($startDate);
        $endTs = strtotime($evalEnd);

        while ($currentTs <= $endTs) {
            $curDate = date('Y-m-d', $currentTs);
            $dayOfWeek = (int)date('N', $currentTs);
            $namaHari = $dayMap[$dayOfWeek] ?? 'Senin';
            $isSunday = ($dayOfWeek === 7);
            $liburRow = $liburMap[$curDate] ?? null;
            $isCustomLibur = ($liburRow !== null);

            $log = $logsByDate[$curDate] ?? null;

            if ($isSunday || $isCustomLibur) {
                if ($isCustomLibur && !$isSunday) {
                    $totalLiburKhusus++;
                }
                // Hari Minggu dan hari libur terdaftar tidak dihitung hari kerja/alpa
                if ($log) {
                    $allDaysHistory[] = [
                        'tanggal' => $curDate,
                        'hari' => $namaHari,
                        'status' => $log['status'],
                        'jam_masuk' => $log['jam_masuk'] ? substr($log['jam_masuk'], 0, 5) . ' WITA' : '-',
                        'jam_pulang' => $log['jam_pulang'] ? substr($log['jam_pulang'], 0, 5) . ' WITA' : '-',
                        'keterangan' => $log['keterangan'] ?: ('Presensi Hari Libur' . ($liburRow ? ': ' . $liburRow['nama_libur'] : '')),
                        'jarak' => (float)($log['jarak_masuk_meter'] ?? 0),
                        'badge' => 'emerald'
                    ];
                } elseif ($isCustomLibur) {
                    $allDaysHistory[] = [
                        'tanggal' => $curDate,
                        'hari' => $namaHari,
                        'status' => 'Libur',
                        'jam_masuk' => '-',
                        'jam_pulang' => '-',
                        'keterangan' => 'Hari Libur: ' . $liburRow['nama_libur'],
                        'jarak' => 0,
                        'badge' => 'sky'
                    ];
                }
                $currentTs = strtotime('+1 day', $currentTs);
                continue;
            }

            // Hari Kerja (Senin - Sabtu)
            $totalHariKerja++;

            if ($log && !empty($log['jam_masuk'])) {
                $status = $log['status'] ?? 'Hadir';
                $jamMasukFmt = substr($log['jam_masuk'], 0, 5) . ' WITA';
                $jamPulangFmt = !empty($log['jam_pulang']) ? substr($log['jam_pulang'], 0, 5) . ' WITA' : '-';

                if ($status === 'Terlambat') {
                    $totalTerlambat++;
                    $totalHadir++;

                    // Ekstrak menit terlambat dari keterangan jika ada
                    $menit = 0;
                    if (preg_match('/(\d+)\s*Menit/i', $log['keterangan'] ?? '', $m)) {
                        $menit = (int)$m[1];
                    }
                    $totalMenitTerlambat += $menit;

                    $itemLate = [
                        'tanggal' => $curDate,
                        'hari' => $namaHari,
                        'jam_masuk' => $jamMasukFmt,
                        'jam_pulang' => $jamPulangFmt,
                        'menit_terlambat' => $menit,
                        'keterangan' => $log['keterangan'] ?: "Terlambat {$menit} Menit",
                        'lokasi' => $log['nama_kantor_absen'] ?: 'Sekolah',
                        'jarak' => (float)($log['jarak_masuk_meter'] ?? 0),
                        'badge' => 'amber'
                    ];
                    $listTerlambat[] = $itemLate;
                    $allDaysHistory[] = array_merge($itemLate, ['status' => 'Terlambat']);
                } elseif (in_array($status, ['Izin', 'Sakit', 'Cuti', 'Dinas Luar'], true)) {
                    $allDaysHistory[] = [
                        'tanggal' => $curDate,
                        'hari' => $namaHari,
                        'status' => $status,
                        'jam_masuk' => '-',
                        'jam_pulang' => '-',
                        'keterangan' => $log['keterangan'] ?: $status,
                        'jarak' => 0,
                        'badge' => 'indigo'
                    ];
                } else {
                    // Hadir Tepat Waktu
                    $totalHadir++;
                    $totalTepatWaktu++;
                    $allDaysHistory[] = [
                        'tanggal' => $curDate,
                        'hari' => $namaHari,
                        'status' => 'Hadir',
                        'jam_masuk' => $jamMasukFmt,
                        'jam_pulang' => $jamPulangFmt,
                        'keterangan' => $log['keterangan'] ?: 'Hadir Tepat Waktu',
                        'jarak' => (float)($log['jarak_masuk_meter'] ?? 0),
                        'badge' => 'emerald'
                    ];
                }
            } else {
                // Hari kerja aktif tapi tidak ada log absensi sama sekali (Tidak Absen / Alpa)
                $totalTidakAbsen++;
                $itemMissing = [
                    'tanggal' => $curDate,
                    'hari' => $namaHari,
                    'status' => 'Tidak Absen',
                    'jam_masuk' => '-',
                    'jam_pulang' => '-',
                    'keterangan' => 'Tidak melakukan presensi (Alpa/Tanpa Keterangan)',
                    'jarak' => 0,
                    'badge' => 'rose'
                ];
                $listTidakAbsen[] = $itemMissing;
                $allDaysHistory[] = $itemMissing;
            }

            $currentTs = strtotime('+1 day', $currentTs);
        }

        // Urutkan riwayat dari yang paling baru ke lama
        usort($allDaysHistory, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));
        usort($listTerlambat, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));
        usort($listTidakAbsen, fn($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

        $persenKehadiran = $totalHariKerja > 0 ? round(($totalHadir / $totalHariKerja) * 100, 1) : 0;

        return [
            'summary' => [
                'total_hari_kerja'      => $totalHariKerja,
                'total_libur'           => $totalLiburKhusus,
                'total_hadir'           => $totalHadir,
                'hadir_tepat_waktu'     => $totalTepatWaktu,
                'terlambat'             => $totalTerlambat,
                'total_menit_terlambat' => $totalMenitTerlambat,
                'tidak_absen'           => $totalTidakAbsen,
                'persen_kehadiran'      => $persenKehadiran,
            ],
            'list_terlambat'   => $listTerlambat,
            'list_tidak_absen' => $listTidakAbsen,
            'all_logs'         => $allDaysHistory,
            'periode' => [
                'start' => $startDate,
                'end'   => $endDate,
                'start_formatted' => date('d M Y', strtotime($startDate)),
                'end_formatted'   => date('d M Y', strtotime($endDate))
            ]
        ];
    }

    /**
     * Update khusus Radius Global saja (5m, 10m, dll. untuk semua pegawai & semua unit)
     */
    public static function updateRadiusGlobal(int $radiusMeter, ?int $userId = null): void
    {
        self::init();
        $radiusMeter = max(1, $radiusMeter);
        self::db()->query("
            UPDATE `absen_pegawai_pengaturan`
            SET `radius_meter` = ?, `updated_by` = ?
            WHERE `id` = 1
        ", [$radiusMeter, $userId]);
    }

    /**
     * Ambil daftar seluruh Unit Tugas beserta konfigurasi lokasi absen masing-masing unit
     */
    public static function getAllUnitLokasi(): array
    {
        self::init();
        $global = self::getPengaturanGlobal();
        $effectiveUnit = self::getEffectiveUserUnit();

        // Gabungkan nama unit dari master_unit_tugas dan pegawai aktif (respects unit / jenjang restriction)
        $uParams = [];
        $unitColExpr = "COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')";
        $uCond = self::getUnitFilterSql($unitColExpr, $uParams, null);

        $unitsRaw = self::db()->findAll("
            SELECT unit_name, COUNT(*) as total_pegawai
            FROM (
                SELECT COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan') as unit_name
                FROM `pegawai` p
                LEFT JOIN `pegawai_penugasan` pp ON pp.pegawai_id = p.id
                LEFT JOIN `master_unit_tugas` mu ON mu.id = pp.unit_tugas_id
                WHERE p.is_active = 1 AND ({$uCond})
                GROUP BY p.id
            ) t
            GROUP BY unit_name
            ORDER BY unit_name ASC
        ", $uParams);

        if (empty($effectiveUnit)) {
            try {
                $masterUnits = self::db()->findAll("SELECT nama FROM `master_unit_tugas` ORDER BY nama ASC");
                $existingMap = array_column($unitsRaw, 'total_pegawai', 'unit_name');
                foreach ($masterUnits as $mu) {
                    $uName = trim($mu['nama'] ?? '');
                    if ($uName !== '' && !isset($existingMap[$uName])) {
                        $unitsRaw[] = ['unit_name' => $uName, 'total_pegawai' => 0];
                    }
                }
            } catch (Throwable $e) {}
        }

        $customUnits = self::db()->findAll("SELECT * FROM `absen_pegawai_lokasi_unit`");
        $customMap = [];
        foreach ($customUnits as $cu) {
            $customMap[$cu['unit_tugas']] = $cu;
        }

        // Hitung berapa pegawai di tiap unit yang memakai custom individu
        $customPegawaiCounts = self::db()->findAll("
            SELECT COALESCE(NULLIF(TRIM(p.unit_tugas), ''), 'Umum / Yayasan') as unit_name, COUNT(*) as cnt
            FROM `absen_pegawai_lokasi` l
            JOIN `pegawai` p ON p.id = l.pegawai_id
            WHERE p.is_active = 1 AND l.mode_lokasi = 'custom'
            GROUP BY unit_name
        ");
        $customPegMap = array_column($customPegawaiCounts, 'cnt', 'unit_name');

        $result = [];
        foreach ($unitsRaw as $ur) {
            $uName = $ur['unit_name'];
            $cu = $customMap[$uName] ?? null;
            $isCustomUnit = ($cu && (int)$cu['is_custom'] === 1);

            $result[] = [
                'unit_tugas' => $uName,
                'total_pegawai' => (int)$ur['total_pegawai'],
                'custom_pegawai_count' => (int)($customPegMap[$uName] ?? 0),
                'is_custom_unit' => $isCustomUnit,
                'nama_kantor' => $isCustomUnit ? $cu['nama_kantor'] : $global['nama_kantor_default'],
                'latitude' => $isCustomUnit ? (float)$cu['latitude'] : (float)$global['latitude_default'],
                'longitude' => $isCustomUnit ? (float)$cu['longitude'] : (float)$global['longitude_default'],
                'radius_meter' => (int)$global['radius_meter'],
                'updated_at' => $cu['updated_at'] ?? null
            ];
        }

        return $result;
    }

    /**
     * Simpan lokasi absen custom untuk 1 Unit Tugas
     * Jika $forceOverwriteCustomPegawai = true, pegawai di unit tersebut yang tadinya punya custom individu juga ikut disamakan ke lokasi unit ini.
     */
    public static function saveLokasiUnit(string $unitTugas, string $namaKantor, float $latitude, float $longitude, bool $forceOverwriteCustomPegawai = true, ?int $userId = null): int
    {
        self::init();
        $unitTugas = trim($unitTugas);
        $namaKantor = trim($namaKantor);
        if ($unitTugas === '' || $namaKantor === '') {
            throw new Exception('Nama Unit dan Nama Kantor Tempat Absen wajib diisi.');
        }

        self::db()->query("
            INSERT INTO `absen_pegawai_lokasi_unit`
            (`unit_tugas`, `nama_kantor`, `latitude`, `longitude`, `is_custom`, `updated_by`)
            VALUES (?, ?, ?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE
                `nama_kantor` = VALUES(`nama_kantor`),
                `latitude` = VALUES(`latitude`),
                `longitude` = VALUES(`longitude`),
                `is_custom` = 1,
                `updated_by` = VALUES(`updated_by`)
        ", [$unitTugas, $namaKantor, $latitude, $longitude, $userId]);

        // Ambil daftar pegawai di unit tersebut
        if ($unitTugas === 'Umum / Yayasan') {
            $pegawaiUnit = self::db()->findAll("
                SELECT id FROM `pegawai` 
                WHERE is_active = 1 AND (unit_tugas IS NULL OR TRIM(unit_tugas) = '' OR TRIM(unit_tugas) = 'Umum / Yayasan')
            ");
        } else {
            $pegawaiUnit = self::db()->findAll("
                SELECT id FROM `pegawai` 
                WHERE is_active = 1 AND TRIM(unit_tugas) = ?
            ", [$unitTugas]);
        }

        $affected = 0;
        foreach ($pegawaiUnit as $p) {
            $pid = (int)$p['id'];
            $existing = self::db()->find("SELECT mode_lokasi FROM `absen_pegawai_lokasi` WHERE `pegawai_id` = ?", [$pid]);

            if ($existing && $existing['mode_lokasi'] === 'custom' && !$forceOverwriteCustomPegawai) {
                continue;
            }

            self::db()->query("
                INSERT INTO `absen_pegawai_lokasi`
                (`pegawai_id`, `mode_lokasi`, `nama_kantor`, `latitude`, `longitude`, `keterangan`, `updated_by`)
                VALUES (?, 'unit', ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    `mode_lokasi` = 'unit',
                    `nama_kantor` = VALUES(`nama_kantor`),
                    `latitude` = VALUES(`latitude`),
                    `longitude` = VALUES(`longitude`),
                    `keterangan` = VALUES(`keterangan`),
                    `updated_by` = VALUES(`updated_by`)
            ", [$pid, $namaKantor, $latitude, $longitude, "Mengikuti lokasi Unit {$unitTugas}", $userId]);
            $affected++;
        }

        return $affected;
    }

    /**
     * Reset lokasi absen 1 Unit kembali ke Lokasi Semula (Default)
     */
    public static function resetLokasiUnit(string $unitTugas): int
    {
        self::init();
        $unitTugas = trim($unitTugas);
        self::db()->query("DELETE FROM `absen_pegawai_lokasi_unit` WHERE `unit_tugas` = ?", [$unitTugas]);

        if ($unitTugas === 'Umum / Yayasan') {
            $pegawaiUnit = self::db()->findAll("
                SELECT id FROM `pegawai` 
                WHERE is_active = 1 AND (unit_tugas IS NULL OR TRIM(unit_tugas) = '' OR TRIM(unit_tugas) = 'Umum / Yayasan')
            ");
        } else {
            $pegawaiUnit = self::db()->findAll("
                SELECT id FROM `pegawai` 
                WHERE is_active = 1 AND TRIM(unit_tugas) = ?
            ", [$unitTugas]);
        }

        $cnt = 0;
        foreach ($pegawaiUnit as $p) {
            self::db()->query("DELETE FROM `absen_pegawai_lokasi` WHERE `pegawai_id` = ?", [(int)$p['id']]);
            $cnt++;
        }
        return $cnt;
    }

    /**
     * Simpan lokasi absen custom untuk 1 Pegawai atau Banyak Pegawai sekaligus
     * Mendukung lokasi custom permanen maupun terjadwal (misal: Senin, Selasa, Rabu pekan ini saja)
     */
    public static function saveLokasiPegawai(
        array $pegawaiIds,
        string $namaKantor,
        float $latitude,
        float $longitude,
        string $keterangan = '',
        ?int $userId = null,
        ?string $tanggalMulai = null,
        ?string $tanggalSelesai = null,
        ?string $hariAktif = null,
        ?string $jamMasukMulai = null,
        ?string $jamMasukBatas = null,
        ?string $jamPulangMulai = null,
        bool $customJamKerja = false
    ): int {
        self::init();
        $namaKantor = trim($namaKantor);
        if (empty($pegawaiIds) || $namaKantor === '') {
            throw new Exception('Pegawai dan Nama Kantor Tempat Absen wajib diisi.');
        }

        $tglMulaiVal = (!empty($tanggalMulai) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalMulai)) ? $tanggalMulai : null;
        $tglSelesaiVal = (!empty($tanggalSelesai) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalSelesai)) ? $tanggalSelesai : null;
        if ($tglMulaiVal && $tglSelesaiVal && $tglSelesaiVal < $tglMulaiVal) {
            $tmp = $tglMulaiVal;
            $tglMulaiVal = $tglSelesaiVal;
            $tglSelesaiVal = $tmp;
        }
        $hariAktifVal = !empty($hariAktif) ? trim($hariAktif) : null;

        $jMulaiVal = ($customJamKerja && !empty($jamMasukMulai)) ? trim($jamMasukMulai) : null;
        $jBatasVal = ($customJamKerja && !empty($jamMasukBatas)) ? trim($jamMasukBatas) : null;
        $jPulangVal = ($customJamKerja && !empty($jamPulangMulai)) ? trim($jamPulangMulai) : null;
        if ($jMulaiVal && strlen($jMulaiVal) === 5) $jMulaiVal .= ':00';
        if ($jBatasVal && strlen($jBatasVal) === 5) $jBatasVal .= ':00';
        if ($jPulangVal && strlen($jPulangVal) === 5) $jPulangVal .= ':00';

        $cnt = 0;
        foreach ($pegawaiIds as $pid) {
            $pid = (int)$pid;
            if ($pid <= 0) continue;

            self::db()->query("
                INSERT INTO `absen_pegawai_lokasi`
                (`pegawai_id`, `mode_lokasi`, `nama_kantor`, `latitude`, `longitude`, `tanggal_mulai`, `tanggal_selesai`, `hari_aktif`, `jam_masuk_mulai`, `jam_masuk_batas`, `jam_pulang_mulai`, `keterangan`, `updated_by`)
                VALUES (?, 'custom', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    `mode_lokasi` = 'custom',
                    `nama_kantor` = VALUES(`nama_kantor`),
                    `latitude` = VALUES(`latitude`),
                    `longitude` = VALUES(`longitude`),
                    `tanggal_mulai` = VALUES(`tanggal_mulai`),
                    `tanggal_selesai` = VALUES(`tanggal_selesai`),
                    `hari_aktif` = VALUES(`hari_aktif`),
                    `jam_masuk_mulai` = VALUES(`jam_masuk_mulai`),
                    `jam_masuk_batas` = VALUES(`jam_masuk_batas`),
                    `jam_pulang_mulai` = VALUES(`jam_pulang_mulai`),
                    `keterangan` = VALUES(`keterangan`),
                    `updated_by` = VALUES(`updated_by`)
            ", [
                $pid,
                $namaKantor,
                $latitude,
                $longitude,
                $tglMulaiVal,
                $tglSelesaiVal,
                $hariAktifVal,
                $jMulaiVal,
                $jBatasVal,
                $jPulangVal,
                $keterangan ?: 'Lokasi Custom Pegawai',
                $userId
            ]);
            $cnt++;
        }
        return $cnt;
    }

    /**
     * Ambil seluruh jadwal jam kerja khusus per hari, terindeks [pegawai_id][hari_ke]
     */
    public static function getAllJamHarianMap(): array
    {
        self::init();
        $rows = self::db()->findAll("
            SELECT pegawai_id, hari_ke, jam_masuk_mulai, jam_masuk_batas, jam_pulang_mulai
            FROM `absen_pegawai_jam_harian`
            ORDER BY pegawai_id ASC, hari_ke ASC
        ");
        $map = [];
        foreach ($rows as $row) {
            $pid = (int)$row['pegawai_id'];
            $hk = (int)$row['hari_ke'];
            $map[$pid][$hk] = [
                'jam_masuk_mulai'  => $row['jam_masuk_mulai'] ? substr((string)$row['jam_masuk_mulai'], 0, 5) : '',
                'jam_masuk_batas'  => substr((string)$row['jam_masuk_batas'], 0, 5),
                'jam_pulang_mulai' => $row['jam_pulang_mulai'] ? substr((string)$row['jam_pulang_mulai'], 0, 5) : '',
                'raw_masuk_mulai'  => $row['jam_masuk_mulai'],
                'raw_masuk_batas'  => $row['jam_masuk_batas'],
                'raw_pulang_mulai' => $row['jam_pulang_mulai'],
            ];
        }
        return $map;
    }

    /**
     * Selesaikan jam kerja efektif pegawai pada tanggal tertentu
     * Prioritas: Khusus Per Hari -> Khusus Pegawai (Semua Hari) -> Khusus Unit -> Global (termasuk Sabtu Khusus)
     */
    public static function resolvePegawaiJamForDate(array $row, array $dailyMap, array $global, ?string $tanggal = null): array
    {
        $ts = $tanggal ? strtotime($tanggal) : time();
        if ($ts === false) {
            $ts = time();
        }
        $hariKe = (int)date('N', $ts);
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $namaHari = $dayNames[$hariKe] ?? 'Senin';
        $globalJam = self::resolveGlobalJamForDate($global, $tanggal);

        if (!empty($dailyMap[$hariKe]['raw_masuk_batas']) || !empty($dailyMap[$hariKe]['raw_pulang_mulai'])) {
            $d = $dailyMap[$hariKe];
            return [
                'jam_masuk_mulai'     => !empty($d['raw_masuk_mulai']) ? $d['raw_masuk_mulai'] : $globalJam['jam_masuk_mulai'],
                'jam_masuk_batas'     => !empty($d['raw_masuk_batas']) ? $d['raw_masuk_batas'] : $globalJam['jam_masuk_batas'],
                'jam_pulang_mulai'    => !empty($d['raw_pulang_mulai']) ? $d['raw_pulang_mulai'] : $globalJam['jam_pulang_mulai'],
                'is_custom_jam_kerja' => true,
                'jam_kerja_label'     => "Khusus ({$namaHari})",
                'is_per_hari_aktif'   => true,
            ];
        }

        if (!empty($row['peg_jam_masuk_batas']) || !empty($row['peg_jam_pulang_mulai'])) {
            return [
                'jam_masuk_mulai'     => !empty($row['peg_jam_masuk_mulai']) ? $row['peg_jam_masuk_mulai'] : $globalJam['jam_masuk_mulai'],
                'jam_masuk_batas'     => !empty($row['peg_jam_masuk_batas']) ? $row['peg_jam_masuk_batas'] : $globalJam['jam_masuk_batas'],
                'jam_pulang_mulai'    => !empty($row['peg_jam_pulang_mulai']) ? $row['peg_jam_pulang_mulai'] : $globalJam['jam_pulang_mulai'],
                'is_custom_jam_kerja' => true,
                'jam_kerja_label'     => 'Khusus Pegawai',
                'is_per_hari_aktif'   => false,
            ];
        }

        if (((int)($row['unit_is_custom'] ?? 0) === 1) && (!empty($row['unit_jam_masuk_batas']) || !empty($row['unit_jam_pulang_mulai']))) {
            return [
                'jam_masuk_mulai'     => !empty($row['unit_jam_masuk_mulai']) ? $row['unit_jam_masuk_mulai'] : $globalJam['jam_masuk_mulai'],
                'jam_masuk_batas'     => !empty($row['unit_jam_masuk_batas']) ? $row['unit_jam_masuk_batas'] : $globalJam['jam_masuk_batas'],
                'jam_pulang_mulai'    => !empty($row['unit_jam_pulang_mulai']) ? $row['unit_jam_pulang_mulai'] : $globalJam['jam_pulang_mulai'],
                'is_custom_jam_kerja' => true,
                'jam_kerja_label'     => 'Khusus Unit',
                'is_per_hari_aktif'   => false,
            ];
        }

        return [
            'jam_masuk_mulai'     => $globalJam['jam_masuk_mulai'],
            'jam_masuk_batas'     => $globalJam['jam_masuk_batas'],
            'jam_pulang_mulai'    => $globalJam['jam_pulang_mulai'],
            'is_custom_jam_kerja' => false,
            'jam_kerja_label'     => !empty($globalJam['is_sabtu_khusus']) ? 'Global (Sabtu Khusus)' : 'Global (Semua)',
            'is_per_hari_aktif'   => false,
        ];
    }

    /**
     * Simpan jam kerja khusus (semua hari, per hari berbeda, atau reset global) untuk 1 atau banyak pegawai
     */
    public static function saveJamKerjaPegawai(
        array $pegawaiIds,
        ?string $jamMasukMulai = null,
        ?string $jamMasukBatas = null,
        ?string $jamPulangMulai = null,
        bool $resetToGlobal = false,
        ?int $userId = null,
        ?array $jamHarian = null
    ): int {
        self::init();
        if (empty($pegawaiIds)) {
            throw new Exception('Pilih minimal 1 pegawai terlebih dahulu.');
        }

        $isPerHariMode = (!$resetToGlobal && $jamHarian !== null);
        $normalizedDaily = [];
        if ($isPerHariMode) {
            foreach ($jamHarian as $hk => $cfg) {
                $hkInt = (int)$hk;
                if ($hkInt < 1 || $hkInt > 7) continue;
                $aktif = !empty($cfg['aktif']);
                $batas = trim((string)($cfg['jam_masuk_batas'] ?? ''));
                if (!$aktif || $batas === '') continue;
                $normalizedDaily[$hkInt] = [
                    'jam_masuk_mulai'  => self::normalizeTimeStr($cfg['jam_masuk_mulai'] ?? null, '06:00:00'),
                    'jam_masuk_batas'  => self::normalizeTimeStr($batas, '07:30:00'),
                    'jam_pulang_mulai' => self::normalizeTimeStr($cfg['jam_pulang_mulai'] ?? null, '15:00:00'),
                ];
            }
            if (empty($normalizedDaily)) {
                throw new Exception('Centang minimal 1 hari dan isi Batas Tepat Waktu untuk jadwal per hari.');
            }
        }

        $global = self::getPengaturanGlobal();
        $cnt = 0;

        foreach ($pegawaiIds as $pid) {
            $pid = (int)$pid;
            if ($pid <= 0) continue;

            $existing = self::db()->find("SELECT * FROM `absen_pegawai_lokasi` WHERE `pegawai_id` = ? LIMIT 1", [$pid]);

            if ($resetToGlobal || $isPerHariMode) {
                if ($existing) {
                    self::db()->query("
                        UPDATE `absen_pegawai_lokasi`
                        SET `jam_masuk_mulai` = NULL,
                            `jam_masuk_batas` = NULL,
                            `jam_pulang_mulai` = NULL,
                            `updated_by` = ?,
                            `updated_at` = NOW()
                        WHERE `pegawai_id` = ?
                    ", [$userId, $pid]);
                }
                self::db()->query("DELETE FROM `absen_pegawai_jam_harian` WHERE `pegawai_id` = ?", [$pid]);

                if ($isPerHariMode) {
                    foreach ($normalizedDaily as $hkInt => $vals) {
                        self::db()->query("
                            INSERT INTO `absen_pegawai_jam_harian`
                            (`pegawai_id`, `hari_ke`, `jam_masuk_mulai`, `jam_masuk_batas`, `jam_pulang_mulai`, `updated_by`)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ", [
                            $pid,
                            $hkInt,
                            $vals['jam_masuk_mulai'],
                            $vals['jam_masuk_batas'],
                            $vals['jam_pulang_mulai'],
                            $userId
                        ]);
                    }
                }
            } else {
                $jMulai = self::normalizeTimeStr($jamMasukMulai, $global['jam_masuk_mulai'] ?: '06:00:00');
                $jBatas = self::normalizeTimeStr($jamMasukBatas, $global['jam_masuk_batas'] ?: '07:30:00');
                $jPulang = self::normalizeTimeStr($jamPulangMulai, $global['jam_pulang_mulai'] ?: '15:00:00');

                if ($existing) {
                    self::db()->query("
                        UPDATE `absen_pegawai_lokasi`
                        SET `jam_masuk_mulai` = ?,
                            `jam_masuk_batas` = ?,
                            `jam_pulang_mulai` = ?,
                            `updated_by` = ?,
                            `updated_at` = NOW()
                        WHERE `pegawai_id` = ?
                    ", [$jMulai, $jBatas, $jPulang, $userId, $pid]);
                } else {
                    self::db()->query("
                        INSERT INTO `absen_pegawai_lokasi`
                        (`pegawai_id`, `mode_lokasi`, `nama_kantor`, `latitude`, `longitude`, `jam_masuk_mulai`, `jam_masuk_batas`, `jam_pulang_mulai`, `keterangan`, `updated_by`)
                        VALUES (?, 'default', ?, ?, ?, ?, ?, ?, 'Custom Jam Kerja Pegawai', ?)
                    ", [
                        $pid,
                        $global['nama_kantor_default'],
                        (float)$global['latitude_default'],
                        (float)$global['longitude_default'],
                        $jMulai,
                        $jBatas,
                        $jPulang,
                        $userId
                    ]);
                }
                self::db()->query("DELETE FROM `absen_pegawai_jam_harian` WHERE `pegawai_id` = ?", [$pid]);
            }
            $cnt++;
        }

        return $cnt;
    }

    /**
     * Cek apakah jadwal lokasi custom pegawai sedang berlaku pada hari ini
     * @return array ['is_active_today' => bool, 'schedule_status' => 'permanen'|'aktif_sekarang'|'belum_mulai'|'hari_lain'|'berakhir', 'jadwal_label' => string]
     */
    public static function evaluateCustomSchedule(?string $tglMulai, ?string $tglSelesai, ?string $hariAktif): array
    {
        $today = date('Y-m-d');
        $dayMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu'
        ];
        $todayDayName = $dayMap[(int)date('N')] ?? 'Senin';

        $hasDateRange = (!empty($tglMulai) || !empty($tglSelesai));
        $hasDayFilter = !empty($hariAktif);

        if (!$hasDateRange && !$hasDayFilter) {
            return [
                'is_active_today' => true,
                'schedule_status' => 'permanen',
                'jadwal_label' => 'Permanen (Sampai Di-reset)'
            ];
        }

        $parts = [];
        if ($hasDayFilter) {
            $parts[] = 'Hari: ' . $hariAktif;
        }
        if ($tglMulai && $tglSelesai) {
            if ($tglMulai === $tglSelesai) {
                $parts[] = date('d M Y', strtotime($tglMulai));
            } else {
                $parts[] = date('d M', strtotime($tglMulai)) . ' - ' . date('d M Y', strtotime($tglSelesai));
            }
        } elseif ($tglMulai) {
            $parts[] = 'Mulai ' . date('d M Y', strtotime($tglMulai));
        } elseif ($tglSelesai) {
            $parts[] = 's/d ' . date('d M Y', strtotime($tglSelesai));
        }
        $jadwalLabel = implode(' • ', $parts);

        // 1. Cek apakah sudah lewat tanggal selesai
        if ($tglSelesai && $today > $tglSelesai) {
            return [
                'is_active_today' => false,
                'schedule_status' => 'berakhir',
                'jadwal_label' => $jadwalLabel . ' (Sudah Berakhir -> Otomatis Kembali Semula)'
            ];
        }

        // 2. Cek apakah belum masuk tanggal mulai
        if ($tglMulai && $today < $tglMulai) {
            return [
                'is_active_today' => false,
                'schedule_status' => 'belum_mulai',
                'jadwal_label' => $jadwalLabel . ' (Terjadwal)'
            ];
        }

        // 3. Cek filter hari (Senin, Selasa, Rabu, dst.)
        if ($hasDayFilter) {
            $allowedDays = array_map('trim', explode(',', $hariAktif));
            if (!in_array($todayDayName, $allowedDays, true)) {
                return [
                    'is_active_today' => false,
                    'schedule_status' => 'hari_lain',
                    'jadwal_label' => $jadwalLabel . " (Hari {$todayDayName} Kembali Semula)"
                ];
            }
        }

        return [
            'is_active_today' => true,
            'schedule_status' => 'aktif_sekarang',
            'jadwal_label' => $jadwalLabel . ' (Aktif Hari Ini)'
        ];
    }

    /**
     * Reset lokasi absen 1 Pegawai atau Beberapa Pegawai ke Lokasi Semula
     * ($resetTo = 'default' -> benar-benar kembali ke lokasi semula/utama meskipun unitnya punya lokasi custom;
     *  atau hapus override individu)
     */
    public static function resetLokasiPegawai(array $pegawaiIds): int
    {
        self::init();
        $cnt = 0;
        foreach ($pegawaiIds as $pid) {
            $pid = (int)$pid;
            if ($pid <= 0) continue;
            self::db()->query("DELETE FROM `absen_pegawai_lokasi` WHERE `pegawai_id` = ?", [$pid]);
            $cnt++;
        }
        return $cnt;
    }

    /**
     * Reset SELURUH Lokasi Absen (Semua Pegawai & Semua Unit) kembali ke Lokasi Semula (Default)
     */
    public static function resetSemuaLokasiKeSemula(): void
    {
        self::init();
        self::db()->query("TRUNCATE TABLE `absen_pegawai_lokasi`");
        self::db()->query("TRUNCATE TABLE `absen_pegawai_lokasi_unit`");
    }

    /**
     * Ambil daftar seluruh pegawai beserta titik lokasi absen aktifnya (hierarki: Custom Pegawai -> Custom Unit -> Lokasi Semula/Default)
     * dan Radius Global yang berlaku.
     */
    public static function getAllPegawaiLokasi(array $filters = []): array
    {
        self::init();
        $global = self::getPengaturanGlobal();
        $radiusGlobal = (int)$global['radius_meter'];
        $jamHarianMap = self::getAllJamHarianMap();

        $where = ["p.is_active = 1"];
        $params = [];

        $unitColExpr = "COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')";
        $uCondPeg = self::getUnitFilterSql($unitColExpr, $params, $filters['unit_tugas'] ?? null);
        if ($uCondPeg !== '1=1') {
            $where[] = "({$uCondPeg})";
        }
        if (class_exists('Auth') && Auth::isPersonalScope()) {
            $where[] = Auth::personalPegawaiSqlCondition('p.id', $params);
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(p.nama LIKE ? OR p.niy LIKE ? OR p.jabatan LIKE ? OR l.nama_kantor LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        $rows = self::db()->findAll("
            SELECT 
                p.id, p.niy, p.nama, p.gelar, p.foto,
                COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan') as unit_tugas,
                p.jabatan, p.status_pegawai, p.status_kerja,
                l.mode_lokasi as peg_mode,
                l.nama_kantor as peg_kantor,
                l.latitude as peg_lat,
                l.longitude as peg_lng,
                l.tanggal_mulai as peg_tgl_mulai,
                l.tanggal_selesai as peg_tgl_selesai,
                l.hari_aktif as peg_hari_aktif,
                l.jam_masuk_mulai as peg_jam_masuk_mulai,
                l.jam_masuk_batas as peg_jam_masuk_batas,
                l.jam_pulang_mulai as peg_jam_pulang_mulai,
                l.keterangan as peg_ket,
                l.updated_at as peg_updated_at,
                u.is_custom as unit_is_custom,
                u.nama_kantor as unit_kantor,
                u.latitude as unit_lat,
                u.longitude as unit_lng,
                u.jam_masuk_mulai as unit_jam_masuk_mulai,
                u.jam_masuk_batas as unit_jam_masuk_batas,
                u.jam_pulang_mulai as unit_jam_pulang_mulai,
                apl.jam_masuk as today_jam_masuk,
                apl.jam_pulang as today_jam_pulang,
                apl.status as today_status,
                apl.keterangan as today_keterangan,
                apl.jarak_masuk_meter as today_jarak
            FROM `pegawai` p
            LEFT JOIN `pegawai_penugasan` pp ON pp.pegawai_id = p.id
            LEFT JOIN `master_unit_tugas` mu ON mu.id = pp.unit_tugas_id
            LEFT JOIN `absen_pegawai_lokasi` l ON l.pegawai_id = p.id
            LEFT JOIN `absen_pegawai_lokasi_unit` u ON u.unit_tugas = COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')
            LEFT JOIN `absen_pegawai_log` apl ON apl.pegawai_id = p.id AND apl.tanggal = CURDATE()
            WHERE {$whereSql}
            GROUP BY p.id
            ORDER BY p.nama ASC
        ", $params);

        $result = [];
        foreach ($rows as $r) {
            // Evaluasi apakah custom pegawai memiliki jadwal tanggal / hari tertentu
            $schedInfo = [
                'is_active_today' => false,
                'schedule_status' => 'none',
                'jadwal_label' => ''
            ];
            $isCustomActiveToday = false;
            if ($r['peg_mode'] === 'custom' && !empty($r['peg_kantor'])) {
                $schedInfo = self::evaluateCustomSchedule(
                    $r['peg_tgl_mulai'] ?? null,
                    $r['peg_tgl_selesai'] ?? null,
                    $r['peg_hari_aktif'] ?? null
                );
                $isCustomActiveToday = $schedInfo['is_active_today'];
            }

            // Tentukan lokasi efektif pegawai HARI INI berdasarkan prioritas:
            // 1. Custom Pegawai yang sedang aktif hari ini (`peg_mode === 'custom'` && $isCustomActiveToday)
            // 2. Custom Unit (`unit_is_custom == 1` atau `peg_mode === 'unit'`)
            // 3. Lokasi Semula / Default Global
            if ($r['peg_mode'] === 'custom' && !empty($r['peg_kantor']) && $isCustomActiveToday) {
                $statusLokasi = ($schedInfo['schedule_status'] === 'aktif_sekarang')
                    ? 'Custom Terjadwal (Aktif)'
                    : 'Custom Pegawai';
                $modeKey = 'custom';
                $namaKantor = $r['peg_kantor'];
                $lat = (float)$r['peg_lat'];
                $lng = (float)$r['peg_lng'];
            } elseif ((int)($r['unit_is_custom'] ?? 0) === 1 && !empty($r['unit_kantor'])) {
                $statusLokasi = 'Custom Unit';
                $modeKey = 'unit';
                $namaKantor = $r['unit_kantor'];
                $lat = (float)$r['unit_lat'];
                $lng = (float)$r['unit_lng'];
            } elseif ($r['peg_mode'] === 'unit' && !empty($r['peg_kantor'])) {
                $statusLokasi = 'Custom Unit';
                $modeKey = 'unit';
                $namaKantor = $r['peg_kantor'];
                $lat = (float)$r['peg_lat'];
                $lng = (float)$r['peg_lng'];
            } else {
                $statusLokasi = 'Lokasi Semula (Default)';
                $modeKey = 'default';
                $namaKantor = $global['nama_kantor_default'];
                $lat = (float)$global['latitude_default'];
                $lng = (float)$global['longitude_default'];
            }

            // Filter berdasarkan mode lokasi jika dipilih
            if (!empty($filters['mode_lokasi']) && $filters['mode_lokasi'] !== $modeKey) {
                continue;
            }

            // Hitung Jam Kerja Efektif Pegawai HARI INI (Per Hari -> Custom Pegawai -> Custom Unit -> Global)
            $dailyMap = $jamHarianMap[(int)$r['id']] ?? [];
            $resolvedJam = self::resolvePegawaiJamForDate($r, $dailyMap, $global);

            $r['jam_harian'] = $dailyMap;
            $r['has_jam_harian'] = !empty($dailyMap);
            $r['is_custom_jam_kerja'] = $resolvedJam['is_custom_jam_kerja'] || !empty($dailyMap);
            $r['is_custom_jam_hari_ini'] = $resolvedJam['is_custom_jam_kerja'];
            $r['jam_kerja_label'] = $resolvedJam['jam_kerja_label'];
            $r['jam_masuk_mulai_efektif'] = $resolvedJam['jam_masuk_mulai'];
            $r['jam_masuk_batas_efektif'] = $resolvedJam['jam_masuk_batas'];
            $r['jam_pulang_mulai_efektif'] = $resolvedJam['jam_pulang_mulai'];

            $r['mode_efektif'] = $modeKey;
            $r['status_lokasi_label'] = $statusLokasi;
            $r['nama_kantor_efektif'] = $namaKantor;
            $r['latitude_efektif'] = $lat;
            $r['longitude_efektif'] = $lng;
            $r['radius_meter'] = $radiusGlobal;
            $r['jadwal_custom_label'] = $schedInfo['jadwal_label'];
            $r['jadwal_custom_status'] = $schedInfo['schedule_status'];

            $result[] = $r;
        }

        return $result;
    }

    /**
     * Ambil lokasi dan jam kerja efektif untuk 1 pegawai tertentu (digunakan oleh Portal Guru saat absen & validasi)
     */
    public static function getLokasiEfektifPegawai(?int $pegawaiId = null): array
    {
        self::init();
        $global = self::getPengaturanGlobal();
        $globalJam = self::resolveGlobalJamForDate($global);

        if (!$pegawaiId) {
            return [
                'nama_kantor' => $global['nama_kantor_default'],
                'latitude' => (float)$global['latitude_default'],
                'longitude' => (float)$global['longitude_default'],
                'radius_meter' => (int)$global['radius_meter'],
                'mode' => 'default',
                'source_label' => 'Lokasi Semula (Default)',
                'is_custom_jam_kerja' => false,
                'jam_kerja_label' => !empty($globalJam['is_sabtu_khusus']) ? 'Global (Sabtu Khusus)' : 'Global (Semua)',
                'jam_masuk_mulai' => $globalJam['jam_masuk_mulai'],
                'jam_masuk_batas' => $globalJam['jam_masuk_batas'],
                'jam_pulang_mulai' => $globalJam['jam_pulang_mulai'],
            ];
        }

        $list = self::getAllPegawaiLokasi([]);
        foreach ($list as $item) {
            if ((int)$item['id'] === $pegawaiId) {
                return [
                    'nama_kantor' => $item['nama_kantor_efektif'],
                    'latitude' => (float)$item['latitude_efektif'],
                    'longitude' => (float)$item['longitude_efektif'],
                    'radius_meter' => (int)$item['radius_meter'],
                    'mode' => $item['mode_efektif'],
                    'source_label' => $item['status_lokasi_label'],
                    'is_custom_jam_kerja' => (bool)($item['is_custom_jam_kerja'] ?? false),
                    'jam_kerja_label' => $item['jam_kerja_label'] ?? 'Global (Semua)',
                    'jam_masuk_mulai' => $item['jam_masuk_mulai_efektif'] ?? $globalJam['jam_masuk_mulai'],
                    'jam_masuk_batas' => $item['jam_masuk_batas_efektif'] ?? $globalJam['jam_masuk_batas'],
                    'jam_pulang_mulai' => $item['jam_pulang_mulai_efektif'] ?? $globalJam['jam_pulang_mulai'],
                ];
            }
        }

        return [
            'nama_kantor' => $global['nama_kantor_default'],
            'latitude' => (float)$global['latitude_default'],
            'longitude' => (float)$global['longitude_default'],
            'radius_meter' => (int)$global['radius_meter'],
            'mode' => 'default',
            'source_label' => 'Lokasi Semula (Default)',
            'is_custom_jam_kerja' => false,
            'jam_kerja_label' => !empty($globalJam['is_sabtu_khusus']) ? 'Global (Sabtu Khusus)' : 'Global (Semua)',
            'jam_masuk_mulai' => $globalJam['jam_masuk_mulai'],
            'jam_masuk_batas' => $globalJam['jam_masuk_batas'],
            'jam_pulang_mulai' => $globalJam['jam_pulang_mulai'],
        ];
    }

    /**
     * Statistik ringkasan untuk kartu KPI di atas halaman
     */
    public static function getSummaryStats(): array
    {
        self::init();
        $global = self::getPengaturanGlobal();
        $all = self::getAllPegawaiLokasi([]);
        $units = self::getAllUnitLokasi();

        $cntDefault = 0;
        $cntUnit = 0;
        $cntCustom = 0;
        foreach ($all as $p) {
            if ($p['mode_efektif'] === 'custom') {
                $cntCustom++;
            } elseif ($p['mode_efektif'] === 'unit') {
                $cntUnit++;
            } else {
                $cntDefault++;
            }
        }

        $cntCustomUnit = 0;
        foreach ($units as $u) {
            if (!empty($u['is_custom_unit'])) {
                $cntCustomUnit++;
            }
        }

        self::ensureLiburTable();
        $totalLiburTahunIni = 0;
        try {
            $cntLiburRow = self::db()->find(
                "SELECT COUNT(*) as c FROM `absen_pegawai_libur` WHERE YEAR(`tanggal`) = ?",
                [(int)date('Y')]
            );
            $totalLiburTahunIni = (int)($cntLiburRow['c'] ?? 0);
        } catch (Throwable $e) {}

        return [
            'totalPegawai' => count($all),
            'totalDefault' => $cntDefault,
            'totalCustomUnit' => $cntUnit,
            'totalCustomPegawai' => $cntCustom,
            'totalUnit' => count($units),
            'unitCustomCount' => $cntCustomUnit,
            'totalLiburTahunIni' => $totalLiburTahunIni,
            'radiusGlobal' => (int)$global['radius_meter'],
            'globalConfig' => $global
        ];
    }

    /**
     * Hitung jarak Haversine antara dua koordinat GPS (dalam meter)
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return (float)round($earthRadius * $c, 2);
    }

    /**
     * Catat Check-in Masuk Pegawai / Guru
     * Memvalidasi radius lokasi kantor efektif dan mendeteksi keterlambatan berdasarkan jam_masuk_batas
     */
    public static function recordCheckin(int $pegawaiId, float $lat, float $lng, ?float $accuracy = null): array
    {
        self::init();
        if ($pegawaiId <= 0) {
            return ['success' => false, 'message' => 'Identitas pegawai tidak valid.'];
        }

        $efektif = self::getLokasiEfektifPegawai($pegawaiId);
        $global = self::getPengaturanGlobal();
        $targetLat = (float)$efektif['latitude'];
        $targetLng = (float)$efektif['longitude'];
        $radiusBatas = (int)$efektif['radius_meter'];
        $namaKantor = $efektif['nama_kantor'];

        $jarak = self::calculateDistance($lat, $lng, $targetLat, $targetLng);

        // Validasi radius (dengan toleransi akurasi GPS jika akurasi agak longgar, maksimal toleransi +60m untuk smartphone indoor)
        $toleransiAkurasi = ($accuracy !== null && $accuracy > 0) ? min(60.0, max(15.0, (float)$accuracy)) : 15.0;
        $maxAllowedDistance = max((float)$radiusBatas, 15.0) + $toleransiAkurasi;

        if ($jarak > $maxAllowedDistance) {
            return [
                'success' => false,
                'message' => "Anda berada di luar radius presensi ({$jarak}m dari {$namaKantor}). Batas radius maksimal adalah {$radiusBatas}m.",
                'data' => [
                    'distance' => $jarak,
                    'max_radius' => $radiusBatas,
                    'target_office' => $namaKantor
                ]
            ];
        }

        // Cek apakah sudah pernah check-in hari ini
        $existing = self::db()->find(
            "SELECT * FROM `absen_pegawai_log` WHERE `pegawai_id` = ? AND `tanggal` = CURDATE() LIMIT 1",
            [$pegawaiId]
        );

        if ($existing && !empty($existing['jam_masuk'])) {
            $formattedJam = date('H:i', strtotime($existing['jam_masuk'])) . ' WITA';
            return [
                'success' => true,
                'already_checked_in' => true,
                'message' => "Anda sudah melakukan check-in hari ini pada pukul {$formattedJam}.",
                'data' => [
                    'jam_masuk' => $formattedJam,
                    'jam_masuk_raw' => $existing['jam_masuk'],
                    'status' => $existing['status'],
                    'keterangan' => $existing['keterangan'] ?: ($existing['status'] === 'Terlambat' ? 'Terlambat' : 'Hadir Tepat Waktu'),
                    'jarak_meter' => (float)($existing['jarak_masuk_meter'] ?? $jarak),
                    'nama_kantor' => $existing['nama_kantor_absen'] ?: $namaKantor
                ]
            ];
        }

        // Tentukan apakah Hadir Tepat Waktu atau Terlambat berdasarkan batas jam kedatangan efektif pegawai
        $currentTime = date('H:i:s');
        $jamMasukBatas = $efektif['jam_masuk_batas'] ?? ($global['jam_masuk_batas'] ?: '07:30:00');

        if (strtotime($currentTime) <= strtotime($jamMasukBatas)) {
            $status = 'Hadir';
            $keterangan = 'Hadir Tepat Waktu';
        } else {
            $selisihMenit = max(1, (int)round((strtotime($currentTime) - strtotime($jamMasukBatas)) / 60));
            $status = 'Terlambat';
            $keterangan = "Terlambat {$selisihMenit} Menit";
        }

        try {
            self::db()->query("
                INSERT INTO `absen_pegawai_log` (
                    `pegawai_id`, `tanggal`, `jam_masuk`, `status`, `nama_kantor_absen`,
                    `lat_masuk`, `lng_masuk`, `jarak_masuk_meter`, `radius_saat_absen`, `keterangan`
                ) VALUES (
                    ?, CURDATE(), ?, ?, ?,
                    ?, ?, ?, ?, ?
                ) ON DUPLICATE KEY UPDATE
                    `jam_masuk` = VALUES(`jam_masuk`),
                    `status` = VALUES(`status`),
                    `nama_kantor_absen` = VALUES(`nama_kantor_absen`),
                    `lat_masuk` = VALUES(`lat_masuk`),
                    `lng_masuk` = VALUES(`lng_masuk`),
                    `jarak_masuk_meter` = VALUES(`jarak_masuk_meter`),
                    `keterangan` = VALUES(`keterangan`),
                    `updated_at` = NOW()
            ", [
                $pegawaiId,
                $currentTime,
                $status,
                $namaKantor,
                $lat,
                $lng,
                $jarak,
                $radiusBatas,
                $keterangan
            ]);

            // Sinkronkan ke tabel absensi_pegawai jika ada
            try {
                $uid = (int)self::db()->findColumn("SELECT user_id FROM `pegawai` WHERE `id` = ? LIMIT 1", [$pegawaiId]);
                if ($uid > 0) {
                    self::db()->query("
                        INSERT INTO `absensi_pegawai` (
                            `user_id`, `tanggal`, `jam_masuk`, `status_kehadiran`,
                            `latitude_masuk`, `longitude_masuk`, `jarak_masuk_meter`, `keterangan`
                        ) VALUES (
                            ?, CURDATE(), ?, ?,
                            ?, ?, ?, ?
                        ) ON DUPLICATE KEY UPDATE
                            `jam_masuk` = VALUES(`jam_masuk`),
                            `status_kehadiran` = VALUES(`status_kehadiran`),
                            `latitude_masuk` = VALUES(`latitude_masuk`),
                            `longitude_masuk` = VALUES(`longitude_masuk`),
                            `jarak_masuk_meter` = VALUES(`jarak_masuk_meter`),
                            `keterangan` = VALUES(`keterangan`)
                    ", [
                        $uid,
                        $currentTime,
                        strtolower($status),
                        $lat,
                        $lng,
                        (int)round($jarak),
                        $keterangan
                    ]);
                }
            } catch (Throwable $e) {}

            $jamMasukFmt = date('H:i', strtotime($currentTime)) . ' WITA';
            return [
                'success' => true,
                'message' => 'Check-in masuk berhasil dicatat!',
                'data' => [
                    'jam_masuk' => $jamMasukFmt,
                    'jam_masuk_raw' => $currentTime,
                    'status' => $status,
                    'keterangan' => $keterangan,
                    'jarak_meter' => $jarak,
                    'nama_kantor' => $namaKantor
                ]
            ];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Gagal mencatat check-in: ' . $e->getMessage()];
        }
    }

    /**
     * Catat Absen Pulang Pegawai / Guru
     * Memvalidasi jam pulang (default 15.00) dan radius kantor
     */
    public static function recordCheckout(int $pegawaiId, float $lat, float $lng, ?float $accuracy = null): array
    {
        self::init();
        if ($pegawaiId <= 0) {
            return ['success' => false, 'message' => 'Identitas pegawai tidak valid.'];
        }

        $log = self::db()->find(
            "SELECT * FROM `absen_pegawai_log` WHERE `pegawai_id` = ? AND `tanggal` = CURDATE() LIMIT 1",
            [$pegawaiId]
        );

        if (!$log || empty($log['jam_masuk'])) {
            return ['success' => false, 'message' => 'Anda belum melakukan check-in masuk hari ini.'];
        }

        if (!empty($log['jam_pulang'])) {
            $pulangFmt = date('H:i', strtotime($log['jam_pulang'])) . ' WITA';
            return [
                'success' => true,
                'already_checked_out' => true,
                'message' => "Anda sudah melakukan absen pulang hari ini pada pukul {$pulangFmt}.",
                'data' => [
                    'jam_pulang' => $pulangFmt,
                    'jam_pulang_raw' => $log['jam_pulang']
                ]
            ];
        }

        $global = self::getPengaturanGlobal();
        $efektif = self::getLokasiEfektifPegawai($pegawaiId);
        $jamPulangMulai = $efektif['jam_pulang_mulai'] ?? ($global['jam_pulang_mulai'] ?: '15:00:00');
        $currentTime = date('H:i:s');

        // Periksa apakah waktu saat ini sudah memenuhi jam pulang pegawai
        if (strtotime($currentTime) < strtotime($jamPulangMulai)) {
            $jamBukaFmt = date('H:i', strtotime($jamPulangMulai)) . ' WITA';
            return [
                'success' => false,
                'message' => "Absen pulang baru dibuka pada pukul {$jamBukaFmt}."
            ];
        }

        $targetLat = (float)$efektif['latitude'];
        $targetLng = (float)$efektif['longitude'];
        $radiusBatas = (int)$efektif['radius_meter'];
        $namaKantor = $efektif['nama_kantor'];

        $jarak = self::calculateDistance($lat, $lng, $targetLat, $targetLng);
        $toleransiAkurasi = ($accuracy !== null && $accuracy > 0) ? min(60.0, max(15.0, (float)$accuracy)) : 15.0;
        $maxAllowedDistance = max((float)$radiusBatas, 15.0) + $toleransiAkurasi;

        if ($jarak > $maxAllowedDistance) {
            return [
                'success' => false,
                'message' => "Anda berada di luar radius presensi ({$jarak}m dari {$namaKantor}). Batas radius maksimal adalah {$radiusBatas}m.",
                'data' => [
                    'distance' => $jarak,
                    'max_radius' => $radiusBatas,
                    'target_office' => $namaKantor
                ]
            ];
        }

        try {
            self::db()->query("
                UPDATE `absen_pegawai_log`
                SET `jam_pulang` = ?,
                    `lat_pulang` = ?,
                    `lng_pulang` = ?,
                    `jarak_pulang_meter` = ?,
                    `updated_at` = NOW()
                WHERE `pegawai_id` = ? AND `tanggal` = CURDATE()
            ", [
                $currentTime,
                $lat,
                $lng,
                $jarak,
                $pegawaiId
            ]);

            // Sinkronkan ke tabel absensi_pegawai jika ada
            try {
                $uid = (int)self::db()->findColumn("SELECT user_id FROM `pegawai` WHERE `id` = ? LIMIT 1", [$pegawaiId]);
                if ($uid > 0) {
                    self::db()->query("
                        UPDATE `absensi_pegawai`
                        SET `jam_pulang` = ?,
                            `latitude_pulang` = ?,
                            `longitude_pulang` = ?,
                            `jarak_pulang_meter` = ?,
                            `updated_at` = NOW()
                        WHERE `user_id` = ? AND `tanggal` = CURDATE()
                    ", [$currentTime, $lat, $lng, (int)round($jarak), $uid]);
                }
            } catch (Throwable $e) {}

            $pulangFmt = date('H:i', strtotime($currentTime)) . ' WITA';
            return [
                'success' => true,
                'message' => 'Absen pulang berhasil dicatat!',
                'data' => [
                    'jam_pulang' => $pulangFmt,
                    'jam_pulang_raw' => $currentTime
                ]
            ];
        } catch (Throwable $e) {
            return ['success' => false, 'message' => 'Gagal mencatat absen pulang: ' . $e->getMessage()];
        }
    }

    /**
     * Ambil status presensi hari ini untuk 1 pegawai
     */
    public static function getTodayAttendance(int $pegawaiId): array
    {
        self::init();
        if ($pegawaiId <= 0) {
            return [
                'has_checkin' => false,
                'has_checkout' => false,
                'jam_masuk' => null,
                'jam_pulang' => null,
                'status' => null,
                'keterangan' => null
            ];
        }

        $log = self::db()->find(
            "SELECT * FROM `absen_pegawai_log` WHERE `pegawai_id` = ? AND `tanggal` = CURDATE() LIMIT 1",
            [$pegawaiId]
        );

        if (!$log || empty($log['jam_masuk'])) {
            return [
                'has_checkin' => false,
                'has_checkout' => false,
                'jam_masuk' => null,
                'jam_pulang' => null,
                'status' => null,
                'keterangan' => null
            ];
        }

        $hasPulang = !empty($log['jam_pulang']);
        return [
            'has_checkin' => true,
            'has_checkout' => $hasPulang,
            'jam_masuk' => date('H:i', strtotime($log['jam_masuk'])) . ' WITA',
            'jam_masuk_raw' => $log['jam_masuk'],
            'jam_pulang' => $hasPulang ? (date('H:i', strtotime($log['jam_pulang'])) . ' WITA') : null,
            'jam_pulang_raw' => $log['jam_pulang'],
            'status' => $log['status'],
            'keterangan' => $log['keterangan'] ?: ($log['status'] === 'Terlambat' ? 'Terlambat' : 'Hadir Tepat Waktu'),
            'nama_kantor' => $log['nama_kantor_absen'] ?? '',
            'jarak_meter' => (float)($log['jarak_masuk_meter'] ?? 0)
        ];
    }

    /**
     * Ambil riwayat log presensi terbaru untuk 1 pegawai
     */
    public static function getRecentLogs(int $pegawaiId, int $limit = 7): array
    {
        self::init();
        if ($pegawaiId <= 0) return [];

        $rows = self::db()->findAll(
            "SELECT * FROM `absen_pegawai_log` WHERE `pegawai_id` = ? ORDER BY `tanggal` DESC LIMIT " . (int)$limit,
            [$pegawaiId]
        );

        $todayStr = date('Y-m-d');
        $kemarinStr = date('Y-m-d', strtotime('-1 day'));
        $duaHariLaluStr = date('Y-m-d', strtotime('-2 days'));

        $result = [];
        foreach ($rows as $r) {
            $tgl = $r['tanggal'];
            if ($tgl === $todayStr) {
                $dateLabel = 'Hari ini';
            } elseif ($tgl === $kemarinStr) {
                $dateLabel = 'Kemarin';
            } elseif ($tgl === $duaHariLaluStr) {
                $dateLabel = '2 Hari Lalu';
            } else {
                $dateLabel = date('d M Y', strtotime($tgl));
            }

            $checkinFmt = !empty($r['jam_masuk']) ? (date('H:i', strtotime($r['jam_masuk'])) . ' WITA') : '-';
            $checkoutFmt = !empty($r['jam_pulang']) ? (date('H:i', strtotime($r['jam_pulang'])) . ' WITA') : '-';
            $isTerlambat = ($r['status'] === 'Terlambat');
            $badge = $isTerlambat ? 'amber' : 'emerald';
            $statusLabel = $r['keterangan'] ?: ($isTerlambat ? 'Terlambat' : 'Hadir Tepat Waktu');

            $result[] = [
                'date' => $dateLabel,
                'checkin' => $checkinFmt,
                'checkout' => $checkoutFmt,
                'status' => $statusLabel,
                'badge' => $badge
            ];
        }

        return $result;
    }

    /**
     * Ambil data Rekap & Log Presensi Harian Seluruh Pegawai untuk halaman Web
     */
    public static function getRekapHarian(?string $tanggal = null, array $filters = []): array
    {
        self::init();
        $targetDate = $tanggal ?: date('Y-m-d');
        $global = self::getPengaturanGlobal();
        $globalJamTanggal = self::resolveGlobalJamForDate($global, $targetDate);
        $jamBatas = $globalJamTanggal['jam_masuk_batas'];
        $jamPulang = $globalJamTanggal['jam_pulang_mulai'];

        $where = ["p.is_active = 1"];
        $params = [$targetDate];

        $unitColExpr = "COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')";
        $uCondRekap = self::getUnitFilterSql($unitColExpr, $params, $filters['unit_tugas'] ?? null);
        if ($uCondRekap !== '1=1') {
            $where[] = "({$uCondRekap})";
        }
        if (class_exists('Auth') && Auth::isPersonalScope()) {
            $where[] = Auth::personalPegawaiSqlCondition('p.id', $params);
        }

        if (!empty($filters['search'])) {
            $where[] = "(p.nama LIKE ? OR p.niy LIKE ? OR p.jabatan LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['status'])) {
            if (in_array($filters['status'], ['Belum', 'Belum Absen'], true)) {
                $where[] = "(apl.status IS NULL OR apl.status = '' OR apl.status = 'Belum') AND apl.jam_masuk IS NULL";
            } elseif ($filters['status'] === 'Alpa' || $filters['status'] === 'Alpha') {
                $where[] = "apl.status IN ('Alpa', 'Alpha')";
            } else {
                $where[] = "apl.status = ?";
                $params[] = $filters['status'];
            }
        }

        $whereSql = implode(' AND ', $where);

        $rows = self::db()->findAll("
            SELECT 
                p.id, p.niy, p.nama, p.gelar, p.foto,
                COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan') as unit_tugas,
                p.jabatan,
                apl.id as log_id,
                apl.tanggal,
                apl.jam_masuk,
                apl.jam_pulang,
                apl.status,
                apl.status as log_status,
                apl.keterangan,
                apl.keterangan as log_keterangan,
                apl.nama_kantor_absen,
                apl.jarak_masuk_meter,
                apl.lat_masuk,
                apl.lng_masuk,
                apl.evaluasi,
                apl.tindak_lanjut,
                apl.evaluasi_oleh,
                apl.evaluasi_at,
                l.mode_lokasi as peg_mode,
                l.nama_kantor as peg_kantor,
                l.tanggal_mulai as peg_tgl_mulai,
                l.tanggal_selesai as peg_tgl_selesai,
                l.hari_aktif as peg_hari_aktif,
                l.jam_masuk_mulai as peg_jam_masuk_mulai,
                l.jam_masuk_batas as peg_jam_masuk_batas,
                l.jam_pulang_mulai as peg_jam_pulang_mulai,
                u.is_custom as unit_is_custom,
                u.nama_kantor as unit_kantor,
                u.jam_masuk_mulai as unit_jam_masuk_mulai,
                u.jam_masuk_batas as unit_jam_masuk_batas,
                u.jam_pulang_mulai as unit_jam_pulang_mulai
            FROM `pegawai` p
            LEFT JOIN `pegawai_penugasan` pp ON pp.pegawai_id = p.id
            LEFT JOIN `master_unit_tugas` mu ON mu.id = pp.unit_tugas_id
            LEFT JOIN `absen_pegawai_lokasi` l ON l.pegawai_id = p.id
            LEFT JOIN `absen_pegawai_lokasi_unit` u ON u.unit_tugas = COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan')
            LEFT JOIN `absen_pegawai_log` apl ON apl.pegawai_id = p.id AND apl.tanggal = ?
            WHERE {$whereSql}
            GROUP BY p.id
            ORDER BY 
                CASE 
                    WHEN apl.status = 'Terlambat' THEN 1 
                    WHEN apl.status = 'Hadir' THEN 2 
                    ELSE 3 
                END,
                apl.jam_masuk ASC,
                p.nama ASC
        ", $params);

        $defaultKantor = $global['nama_kantor_default'] ?: 'Kampus Utama SIT Bina Insan Palu';
        $jamHarianMap = self::getAllJamHarianMap();
        foreach ($rows as &$r) {
            $r['status'] = $r['status'] ?? $r['log_status'] ?? null;
            $r['keterangan'] = $r['keterangan'] ?? $r['log_keterangan'] ?? null;
            $resolvedJam = self::resolvePegawaiJamForDate($r, $jamHarianMap[(int)$r['id']] ?? [], $global, $targetDate);
            $r['is_custom_jam_kerja'] = $resolvedJam['is_custom_jam_kerja'];
            $r['jam_kerja_label'] = $resolvedJam['jam_kerja_label'];
            $r['jam_masuk_batas_efektif'] = $resolvedJam['jam_masuk_batas'];
            $r['jam_pulang_mulai_efektif'] = $resolvedJam['jam_pulang_mulai'];

            $isCustomPeg = false;
            if (($r['peg_mode'] ?? '') === 'custom' && !empty($r['peg_kantor'])) {
                $sched = self::evaluateCustomSchedule($r['peg_tgl_mulai'] ?? null, $r['peg_tgl_selesai'] ?? null, $r['peg_hari_aktif'] ?? null);
                $isCustomPeg = $sched['is_active_today'];
            }
            if ($isCustomPeg) {
                $r['nama_kantor_efektif'] = $r['peg_kantor'];
            } elseif (!empty($r['unit_is_custom']) && !empty($r['unit_kantor'])) {
                $r['nama_kantor_efektif'] = $r['unit_kantor'];
            } else {
                $r['nama_kantor_efektif'] = $defaultKantor;
            }
        }
        unset($r);

        // Hitung statistik untuk tanggal terpilih (respects jenjang & personal restriction)
        $statsParams = [$targetDate];
        $statsUCond = Auth::unitSqlCondition('p.unit_tugas', $statsParams) . ' AND (' . Auth::personalPegawaiSqlCondition('p.id', $statsParams) . ')';
        $allStatsRows = self::db()->findAll("
            SELECT p.id, apl.status, apl.jam_masuk, apl.jam_pulang
            FROM `pegawai` p
            LEFT JOIN `absen_pegawai_log` apl ON apl.pegawai_id = p.id AND apl.tanggal = ?
            WHERE p.is_active = 1 AND ({$statsUCond})
        ", $statsParams);

        $totalPegawai = count($allStatsRows);
        $totalHadir = 0;
        $totalTerlambat = 0;
        $totalPulang = 0;
        $totalIzinSakit = 0;
        $totalBelumAbsen = 0;

        foreach ($allStatsRows as $sr) {
            $st = $sr['status'] ?? '';
            if (!empty($sr['jam_masuk'])) {
                if ($st === 'Terlambat') {
                    $totalTerlambat++;
                } else {
                    $totalHadir++;
                }
                if (!empty($sr['jam_pulang'])) {
                    $totalPulang++;
                }
            } else {
                if (in_array($st, ['Izin', 'Sakit', 'Cuti', 'Alpha', 'Alpa', 'Dinas Luar'], true)) {
                    $totalIzinSakit++;
                } else {
                    $totalBelumAbsen++;
                }
            }
        }

        return [
            'date' => $targetDate,
            'tanggal' => $targetDate,
            'rows' => $rows,
            'logs' => $rows,
            'batasJamMasuk' => $jamBatas,
            'jamPulangMulai' => $jamPulang,
            'isSabtuKhusus' => !empty($globalJamTanggal['is_sabtu_khusus']),
            'stats' => [
                'totalPegawai' => $totalPegawai,
                'totalHadir' => $totalHadir,
                'totalTerlambat' => $totalTerlambat,
                'totalPulang' => $totalPulang,
                'totalIzinSakit' => $totalIzinSakit,
                'totalBelum' => $totalBelumAbsen,
                'totalBelumAbsen' => $totalBelumAbsen,
                'jamMasukBatas' => date('H:i', strtotime($jamBatas)) . ' WITA',
                'jamPulangMulai' => date('H:i', strtotime($jamPulang)) . ' WITA'
            ]
        ];
    }

    /**
     * Ambil unit tugas efektif seorang pegawai
     */
    public static function getPegawaiUnit(int $pegawaiId): ?string
    {
        self::init();
        $row = self::db()->find("
            SELECT COALESCE(NULLIF(TRIM(p.unit_tugas), ''), (mu.nama COLLATE utf8mb4_unicode_ci), 'Yayasan') as unit_tugas
            FROM `pegawai` p
            LEFT JOIN `pegawai_penugasan` pp ON pp.pegawai_id = p.id
            LEFT JOIN `master_unit_tugas` mu ON mu.id = pp.unit_tugas_id
            WHERE p.id = ? LIMIT 1
        ", [$pegawaiId]);
        return $row['unit_tugas'] ?? null;
    }

    /**
     * Update atau Insert (Upsert) Log Presensi Pegawai secara manual oleh Admin/Pengelola
     */
    public static function updateLogPresensi(array $data): bool
    {
        self::init();
        $pegawaiId = (int)($data['pegawai_id'] ?? 0);
        $tanggal = trim((string)($data['tanggal'] ?? ''));
        if (!$pegawaiId || empty($tanggal)) {
            return false;
        }

        $status = trim((string)($data['status'] ?? 'Hadir'));
        if (strcasecmp($status, 'Alpa') === 0) {
            $status = 'Alpha';
        }
        $validStatuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Cuti', 'Alpha', 'Dinas Luar'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'Hadir';
        }

        $jamMasuk = !empty($data['jam_masuk']) ? self::normalizeTimeStr($data['jam_masuk'], '07:15:00') : null;
        $jamPulang = !empty($data['jam_pulang']) ? self::normalizeTimeStr($data['jam_pulang'], '15:00:00') : null;

        if (in_array($status, ['Izin', 'Sakit', 'Cuti', 'Alpha'], true)) {
            $jamMasuk = null;
            $jamPulang = null;
        } elseif ($status === 'Hadir' && empty($jamMasuk)) {
            $jamMasuk = '07:15:00';
        } elseif ($status === 'Terlambat' && empty($jamMasuk)) {
            $jamMasuk = '07:45:00';
        }

        $namaKantor = !empty($data['nama_kantor_absen']) ? trim((string)$data['nama_kantor_absen']) : null;
        $keterangan = !empty($data['keterangan']) ? trim((string)$data['keterangan']) : null;

        if ($status === 'Terlambat' && empty($keterangan) && !empty($jamMasuk)) {
            $menit = max(1, (int)round((strtotime($jamMasuk) - strtotime('07:30:00')) / 60));
            $keterangan = "Terlambat {$menit} Menit";
        } elseif ($status === 'Izin' && empty($keterangan)) {
            $keterangan = 'Izin Tidak Masuk';
        } elseif ($status === 'Sakit' && empty($keterangan)) {
            $keterangan = 'Sakit';
        } elseif ($status === 'Cuti' && empty($keterangan)) {
            $keterangan = 'Cuti';
        } elseif ($status === 'Alpha' && empty($keterangan)) {
            $keterangan = 'Tanpa Keterangan (Alpa)';
        } elseif ($status === 'Dinas Luar' && empty($keterangan)) {
            $keterangan = 'Dinas Luar';
        }

        $existing = self::db()->find(
            "SELECT id FROM `absen_pegawai_log` WHERE `pegawai_id` = ? AND `tanggal` = ? LIMIT 1",
            [$pegawaiId, $tanggal]
        );

        if ($existing) {
            $sql = "UPDATE `absen_pegawai_log` SET 
                        `status` = ?, 
                        `jam_masuk` = ?, 
                        `jam_pulang` = ?, 
                        `keterangan` = ?,
                        `nama_kantor_absen` = COALESCE(?, `nama_kantor_absen`),
                        `updated_at` = NOW()
                    WHERE `id` = ?";
            self::db()->query($sql, [$status, $jamMasuk, $jamPulang, $keterangan, $namaKantor, $existing['id']]);
            return true;
        } else {
            $sql = "INSERT INTO `absen_pegawai_log` 
                        (`pegawai_id`, `tanggal`, `jam_masuk`, `jam_pulang`, `status`, `nama_kantor_absen`, `radius_saat_absen`, `keterangan`, `created_at`, `updated_at`)
                    VALUES 
                        (?, ?, ?, ?, ?, ?, 10, ?, NOW(), NOW())";
            self::db()->query($sql, [$pegawaiId, $tanggal, $jamMasuk, $jamPulang, $status, $namaKantor, $keterangan]);
            return true;
        }
    }

    /**
     * Hapus / Reset Log Presensi Pegawai pada tanggal tertentu (kembali ke Belum Absen)
     */
    public static function deleteLogPresensi(int $pegawaiId, string $tanggal): bool
    {
        self::init();
        self::db()->query(
            "DELETE FROM `absen_pegawai_log` WHERE `pegawai_id` = ? AND `tanggal` = ?",
            [$pegawaiId, $tanggal]
        );
        return true;
    }

    /**
     * Simpan Catatan Keterlambatan (Evaluasi & Tindak Lanjut)
     */
    public static function saveCatatanTerlambat(int $pegawaiId, string $tanggal, string $evaluasi, string $tindakLanjut, ?string $evaluator = null): bool
    {
        self::init();
        if (!$pegawaiId || empty($tanggal)) {
            return false;
        }

        $existing = self::db()->find(
            "SELECT id FROM `absen_pegawai_log` WHERE `pegawai_id` = ? AND `tanggal` = ? LIMIT 1",
            [$pegawaiId, $tanggal]
        );

        if ($existing) {
            $sql = "UPDATE `absen_pegawai_log` SET 
                        `evaluasi` = ?, 
                        `tindak_lanjut` = ?, 
                        `evaluasi_oleh` = ?, 
                        `evaluasi_at` = NOW(),
                        `updated_at` = NOW()
                    WHERE `id` = ?";
            self::db()->query($sql, [$evaluasi, $tindakLanjut, $evaluator, $existing['id']]);
            return true;
        } else {
            // Jika belum ada log presensi, buat log baru dengan status Terlambat
            $sql = "INSERT INTO `absen_pegawai_log` 
                        (`pegawai_id`, `tanggal`, `status`, `radius_saat_absen`, `evaluasi`, `tindak_lanjut`, `evaluasi_oleh`, `evaluasi_at`, `created_at`, `updated_at`)
                    VALUES 
                        (?, ?, 'Terlambat', 10, ?, ?, ?, NOW(), NOW(), NOW())";
            self::db()->query($sql, [$pegawaiId, $tanggal, $evaluasi, $tindakLanjut, $evaluator]);
            return true;
        }
    }

    /**
     * Hapus / Reset Catatan Keterlambatan
     */
    public static function deleteCatatanTerlambat(int $pegawaiId, string $tanggal): bool
    {
        self::init();
        self::db()->query(
            "UPDATE `absen_pegawai_log` SET `evaluasi` = NULL, `tindak_lanjut` = NULL, `evaluasi_oleh` = NULL, `evaluasi_at` = NULL, `updated_at` = NOW() WHERE `pegawai_id` = ? AND `tanggal` = ?",
            [$pegawaiId, $tanggal]
        );
        return true;
    }

    /**
     * =========================================================================
     * PENGATURAN HARI LIBUR (SELAIN MINGGU) & HARI KERJA EFEKTIF
     * Digunakan bersama oleh Modul Absen Pegawai, Jurnal Staff, dan WA Reminder
     * =========================================================================
     */

    public static function ensureLiburTable(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            $pdo = self::db()->getConnection();
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `absen_pegawai_libur` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `tanggal` DATE NOT NULL,
                    `nama_libur` VARCHAR(150) NOT NULL,
                    `jenis` ENUM('nasional', 'cuti_bersama', 'yayasan', 'lainnya') NOT NULL DEFAULT 'nasional',
                    `keterangan` VARCHAR(255) DEFAULT NULL,
                    `created_by` INT(11) DEFAULT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uniq_absen_libur_tanggal` (`tanggal`),
                    KEY `idx_absen_libur_jenis` (`jenis`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (Throwable $e) {
            error_log('Error ensureLiburTable: ' . $e->getMessage());
        }
    }

    /**
     * Ambil daftar hari libur tersimpan (filter opsional berdasarkan tahun atau rentang tanggal)
     */
    public static function getLiburList(?int $tahun = null, ?string $startDate = null, ?string $endDate = null): array
    {
        self::ensureLiburTable();

        $where = ['1=1'];
        $params = [];

        if ($startDate !== null && $startDate !== '') {
            $where[] = '`tanggal` >= ?';
            $params[] = $startDate;
        }
        if ($endDate !== null && $endDate !== '') {
            $where[] = '`tanggal` <= ?';
            $params[] = $endDate;
        }
        if ($tahun !== null && $tahun > 0 && $startDate === null && $endDate === null) {
            $where[] = 'YEAR(`tanggal`) = ?';
            $params[] = $tahun;
        }

        $order = ($startDate !== null || $endDate !== null) ? 'ASC' : 'DESC';
        $whereSql = implode(' AND ', $where);

        return self::db()->findAll(
            "SELECT * FROM `absen_pegawai_libur` WHERE {$whereSql} ORDER BY `tanggal` {$order}",
            $params
        );
    }

    /**
     * Ambil peta hari libur ['Y-m-d' => row] dalam rentang tanggal untuk pencarian O(1)
     */
    public static function getLiburMap(string $startDate, string $endDate): array
    {
        self::ensureLiburTable();
        $rows = self::getLiburList(null, $startDate, $endDate);
        $map = [];
        foreach ($rows as $row) {
            if (!empty($row['tanggal'])) {
                $map[(string)$row['tanggal']] = $row;
            }
        }
        return $map;
    }

    /**
     * Cek apakah tanggal tertentu adalah hari libur (Hari Minggu atau Hari Libur Terdaftar)
     */
    public static function getLiburInfoForDate(string $tanggal): ?array
    {
        $tanggal = trim($tanggal);
        $ts = strtotime($tanggal);
        if ($ts === false) {
            return null;
        }
        $normDate = date('Y-m-d', $ts);
        $dayOfWeek = (int)date('N', $ts);

        self::ensureLiburTable();
        $custom = self::db()->find(
            "SELECT * FROM `absen_pegawai_libur` WHERE `tanggal` = ? LIMIT 1",
            [$normDate]
        );

        if ($custom) {
            return [
                'is_libur'        => true,
                'is_sunday'       => ($dayOfWeek === 7),
                'is_custom_libur' => true,
                'nama_libur'      => (string)$custom['nama_libur'],
                'jenis'           => (string)($custom['jenis'] ?? 'nasional'),
                'keterangan'      => (string)($custom['keterangan'] ?? ''),
                'tanggal'         => $normDate,
            ];
        }

        if ($dayOfWeek === 7) {
            return [
                'is_libur'        => true,
                'is_sunday'       => true,
                'is_custom_libur' => false,
                'nama_libur'      => 'Hari Minggu',
                'jenis'           => 'mingguan',
                'keterangan'      => 'Hari libur rutin mingguan',
                'tanggal'         => $normDate,
            ];
        }

        return null;
    }

    public static function isHariLibur(string $tanggal): bool
    {
        return self::getLiburInfoForDate($tanggal) !== null;
    }

    /**
     * Hitung rincian hari kerja efektif dalam rentang tanggal (mengecualikan Minggu & tanggal libur terdaftar)
     * Parameter $liburMap opsional memudahkan pengujian murni maupun pemanggilan berulang.
     */
    public static function hitungHariKerjaRange(string $startDate, string $endDate, ?array $liburMap = null): array
    {
        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        if ($startTs === false || $endTs === false || $startTs > $endTs) {
            return [
                'jumlah_hari_kerja'   => 0,
                'jumlah_minggu'       => 0,
                'jumlah_libur_khusus' => 0,
                'libur_dates'         => [],
                'working_dates'       => [],
            ];
        }

        if ($liburMap === null) {
            $liburMap = self::getLiburMap(date('Y-m-d', $startTs), date('Y-m-d', $endTs));
        }

        $jumlahHariKerja = 0;
        $jumlahMinggu = 0;
        $jumlahLiburKhusus = 0;
        $liburDates = [];
        $workingDates = [];

        $curTs = $startTs;
        while ($curTs <= $endTs) {
            $curDate = date('Y-m-d', $curTs);
            $dayOfWeek = (int)date('N', $curTs);

            if ($dayOfWeek === 7) {
                $jumlahMinggu++;
            } elseif (isset($liburMap[$curDate])) {
                $jumlahLiburKhusus++;
                $liburDates[$curDate] = $liburMap[$curDate];
            } else {
                $jumlahHariKerja++;
                $workingDates[] = $curDate;
            }

            $curTs = strtotime('+1 day', $curTs);
        }

        return [
            'jumlah_hari_kerja'   => $jumlahHariKerja,
            'jumlah_minggu'       => $jumlahMinggu,
            'jumlah_libur_khusus' => $jumlahLiburKhusus,
            'libur_dates'         => $liburDates,
            'working_dates'       => $workingDates,
        ];
    }

    /**
     * Simpan tanggal libur (tunggal atau rentang, otomatis melewati hari Minggu)
     */
    public static function saveLibur(
        string $tanggalMulai,
        ?string $tanggalSelesai,
        string $namaLibur,
        string $jenis = 'nasional',
        ?string $keterangan = null,
        ?int $userId = null
    ): array {
        self::ensureLiburTable();

        $tanggalMulai = trim($tanggalMulai);
        $tanggalSelesai = ($tanggalSelesai !== null && trim($tanggalSelesai) !== '') ? trim($tanggalSelesai) : $tanggalMulai;
        $namaLibur = trim($namaLibur);
        $jenis = in_array($jenis, ['nasional', 'cuti_bersama', 'yayasan', 'lainnya'], true) ? $jenis : 'nasional';
        $keterangan = ($keterangan !== null && trim($keterangan) !== '') ? trim($keterangan) : null;

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalMulai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalSelesai)) {
            throw new Exception('Format tanggal libur tidak valid (harus YYYY-MM-DD).');
        }
        if ($namaLibur === '') {
            throw new Exception('Nama hari libur wajib diisi.');
        }

        $startTs = strtotime($tanggalMulai);
        $endTs = strtotime($tanggalSelesai);
        if ($startTs === false || $endTs === false || $endTs < $startTs) {
            throw new Exception('Tanggal selesai libur tidak boleh lebih kecil dari tanggal mulai.');
        }

        $daysDiff = (int)floor(($endTs - $startTs) / 86400) + 1;
        if ($daysDiff > 90) {
            throw new Exception('Rentang pengisian hari libur sekaligus maksimal 90 hari.');
        }

        $saved = 0;
        $skippedSunday = 0;
        $curTs = $startTs;

        while ($curTs <= $endTs) {
            $curDate = date('Y-m-d', $curTs);
            if ((int)date('N', $curTs) === 7) {
                $skippedSunday++;
            } else {
                self::db()->query("
                    INSERT INTO `absen_pegawai_libur` (`tanggal`, `nama_libur`, `jenis`, `keterangan`, `created_by`)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        `nama_libur` = VALUES(`nama_libur`),
                        `jenis`      = VALUES(`jenis`),
                        `keterangan` = VALUES(`keterangan`),
                        `created_by` = VALUES(`created_by`),
                        `updated_at` = NOW()
                ", [$curDate, $namaLibur, $jenis, $keterangan, $userId]);
                $saved++;
            }
            $curTs = strtotime('+1 day', $curTs);
        }

        if ($saved === 0 && $skippedSunday > 0) {
            throw new Exception('Tanggal yang dipilih adalah hari Minggu. Hari Minggu sudah otomatis tidak dihitung sebagai hari kerja.');
        }

        return [
            'saved'          => $saved,
            'skipped_sunday' => $skippedSunday,
        ];
    }

    /**
     * Hapus satu entri hari libur berdasarkan ID
     */
    public static function deleteLibur(int $id): bool
    {
        self::ensureLiburTable();
        self::db()->query("DELETE FROM `absen_pegawai_libur` WHERE `id` = ?", [$id]);
        return true;
    }
}

