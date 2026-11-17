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

        $stampFile = BASE_PATH . '/storage/logs/.schema_absen_pegawai_v3.stamp';
        if (file_exists($stampFile)) {
            return;
        }

        $pdo = self::db()->getConnection();

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
                `updated_by` INT NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

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

        // Pastikan kolom jadwal tanggal_mulai, tanggal_selesai, dan hari_aktif tersedia jika tabel sudah dibuat sebelumnya
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `absen_pegawai_lokasi` LIKE 'tanggal_mulai'")->fetchAll();
            if (empty($cols)) {
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi` ADD COLUMN `tanggal_mulai` DATE NULL DEFAULT NULL AFTER `longitude`");
            }
            $cols2 = $pdo->query("SHOW COLUMNS FROM `absen_pegawai_lokasi` LIKE 'tanggal_selesai'")->fetchAll();
            if (empty($cols2)) {
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi` ADD COLUMN `tanggal_selesai` DATE NULL DEFAULT NULL AFTER `tanggal_mulai`");
            }
            $cols3 = $pdo->query("SHOW COLUMNS FROM `absen_pegawai_lokasi` LIKE 'hari_aktif'")->fetchAll();
            if (empty($cols3)) {
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi` ADD COLUMN `hari_aktif` VARCHAR(100) NULL DEFAULT NULL AFTER `tanggal_selesai`");
            }
            $cols4 = $pdo->query("SHOW COLUMNS FROM `absen_pegawai_lokasi` LIKE 'jam_masuk_batas'")->fetchAll();
            if (empty($cols4)) {
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi` ADD COLUMN `jam_masuk_mulai` TIME NULL DEFAULT NULL AFTER `hari_aktif`");
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi` ADD COLUMN `jam_masuk_batas` TIME NULL DEFAULT NULL AFTER `jam_masuk_mulai`");
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi` ADD COLUMN `jam_pulang_mulai` TIME NULL DEFAULT NULL AFTER `jam_masuk_batas`");
            }
            $cols5 = $pdo->query("SHOW COLUMNS FROM `absen_pegawai_lokasi_unit` LIKE 'jam_masuk_batas'")->fetchAll();
            if (empty($cols5)) {
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi_unit` ADD COLUMN `jam_masuk_mulai` TIME NULL DEFAULT NULL AFTER `is_custom`");
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi_unit` ADD COLUMN `jam_masuk_batas` TIME NULL DEFAULT NULL AFTER `jam_masuk_mulai`");
                $pdo->exec("ALTER TABLE `absen_pegawai_lokasi_unit` ADD COLUMN `jam_pulang_mulai` TIME NULL DEFAULT NULL AFTER `jam_masuk_batas`");
            }
        } catch (Throwable $e) {}

        // 4. Tabel Log Kehadiran / Presensi Harian Pegawai
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
                'jam_pulang_mulai' => '15:00:00'
            ];
        }
        return $row;
    }

    /**
     * Simpan pengaturan global (Radius Global untuk semua + Lokasi Semula/Default)
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
        $jamMasukMulai = trim($data['jam_masuk_mulai'] ?? '06:00:00') ?: '06:00:00';
        $jamMasukBatas = trim($data['jam_masuk_batas'] ?? '07:30:00') ?: '07:30:00';
        $jamPulangMulai = trim($data['jam_pulang_mulai'] ?? '15:00:00') ?: '15:00:00';

        self::db()->query("
            UPDATE `absen_pegawai_pengaturan`
            SET `nama_kantor_default` = ?,
                `latitude_default` = ?,
                `longitude_default` = ?,
                `radius_meter` = ?,
                `jam_masuk_mulai` = ?,
                `jam_masuk_batas` = ?,
                `jam_pulang_mulai` = ?,
                `updated_by` = ?
            WHERE `id` = 1
        ", [$namaKantor, $lat, $lng, $radius, $jamMasukMulai, $jamMasukBatas, $jamPulangMulai, $userId]);

        // Perbarui juga baris di absen_pegawai_lokasi yang mode_lokasi = 'default' agar selaras dengan lokasi semula terbaru
        self::db()->query("
            UPDATE `absen_pegawai_lokasi`
            SET `nama_kantor` = ?, `latitude` = ?, `longitude` = ?
            WHERE `mode_lokasi` = 'default'
        ", [$namaKantor, $lat, $lng]);
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

        // Gabungkan nama unit dari master_unit_tugas dan pegawai aktif
        $unitsRaw = self::db()->findAll("
            SELECT unit_name, COUNT(*) as total_pegawai
            FROM (
                SELECT COALESCE(NULLIF(TRIM(unit_tugas), ''), 'Umum / Yayasan') as unit_name
                FROM `pegawai`
                WHERE is_active = 1
            ) t
            GROUP BY unit_name
            ORDER BY unit_name ASC
        ");

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
     * Simpan jam kerja khusus (jam kedatangan & batas pulang) untuk 1 atau banyak pegawai terpilih
     * Jika $resetToGlobal = true, jam kerja khusus dihapus (NULL) sehingga pegawai kembali mengikuti jam global
     */
    public static function saveJamKerjaPegawai(
        array $pegawaiIds,
        ?string $jamMasukMulai = null,
        ?string $jamMasukBatas = null,
        ?string $jamPulangMulai = null,
        bool $resetToGlobal = false,
        ?int $userId = null
    ): int {
        self::init();
        if (empty($pegawaiIds)) {
            throw new Exception('Pilih minimal 1 pegawai terlebih dahulu.');
        }

        $global = self::getPengaturanGlobal();
        $cnt = 0;

        foreach ($pegawaiIds as $pid) {
            $pid = (int)$pid;
            if ($pid <= 0) continue;

            $existing = self::db()->find("SELECT * FROM `absen_pegawai_lokasi` WHERE `pegawai_id` = ? LIMIT 1", [$pid]);

            if ($resetToGlobal) {
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
            } else {
                $jMulai = !empty($jamMasukMulai) ? trim($jamMasukMulai) : ($global['jam_masuk_mulai'] ?: '06:00:00');
                $jBatas = !empty($jamMasukBatas) ? trim($jamMasukBatas) : ($global['jam_masuk_batas'] ?: '07:30:00');
                $jPulang = !empty($jamPulangMulai) ? trim($jamPulangMulai) : ($global['jam_pulang_mulai'] ?: '15:00:00');

                if (strlen($jMulai) === 5) $jMulai .= ':00';
                if (strlen($jBatas) === 5) $jBatas .= ':00';
                if (strlen($jPulang) === 5) $jPulang .= ':00';

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

        $where = ["p.is_active = 1"];
        $params = [];

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(p.nama LIKE ? OR p.niy LIKE ? OR p.jabatan LIKE ? OR l.nama_kantor LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['unit_tugas'])) {
            if ($filters['unit_tugas'] === 'Umum / Yayasan') {
                $where[] = "(p.unit_tugas IS NULL OR TRIM(p.unit_tugas) = '' OR TRIM(p.unit_tugas) = 'Umum / Yayasan')";
            } else {
                $where[] = "TRIM(p.unit_tugas) = ?";
                $params[] = trim($filters['unit_tugas']);
            }
        }

        $whereSql = implode(' AND ', $where);

        $rows = self::db()->findAll("
            SELECT 
                p.id, p.niy, p.nama, p.gelar, p.foto, p.unit_tugas, p.jabatan, p.status_pegawai, p.status_kerja,
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
            LEFT JOIN `absen_pegawai_lokasi` l ON l.pegawai_id = p.id
            LEFT JOIN `absen_pegawai_lokasi_unit` u ON u.unit_tugas = COALESCE(NULLIF(TRIM(p.unit_tugas), ''), 'Umum / Yayasan')
            LEFT JOIN `absen_pegawai_log` apl ON apl.pegawai_id = p.id AND apl.tanggal = CURDATE()
            WHERE {$whereSql}
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

            // Hitung Jam Kerja Efektif Pegawai (Prioritas: Custom Pegawai -> Custom Unit -> Global Semua)
            $hasCustomPegJam = !empty($r['peg_jam_masuk_batas']);
            $hasCustomUnitJam = !empty($r['unit_jam_masuk_batas']);

            if ($hasCustomPegJam) {
                $jMasukMulai = $r['peg_jam_masuk_mulai'] ?: ($global['jam_masuk_mulai'] ?: '06:00:00');
                $jMasukBatas = $r['peg_jam_masuk_batas'];
                $jPulangMulai = $r['peg_jam_pulang_mulai'] ?: ($global['jam_pulang_mulai'] ?: '15:00:00');
                $isCustomJam = true;
                $jamKerjaLabel = 'Khusus Pegawai';
            } elseif ($hasCustomUnitJam && (int)($r['unit_is_custom'] ?? 0) === 1) {
                $jMasukMulai = $r['unit_jam_masuk_mulai'] ?: ($global['jam_masuk_mulai'] ?: '06:00:00');
                $jMasukBatas = $r['unit_jam_masuk_batas'];
                $jPulangMulai = $r['unit_jam_pulang_mulai'] ?: ($global['jam_pulang_mulai'] ?: '15:00:00');
                $isCustomJam = true;
                $jamKerjaLabel = 'Khusus Unit';
            } else {
                $jMasukMulai = $global['jam_masuk_mulai'] ?: '06:00:00';
                $jMasukBatas = $global['jam_masuk_batas'] ?: '07:30:00';
                $jPulangMulai = $global['jam_pulang_mulai'] ?: '15:00:00';
                $isCustomJam = false;
                $jamKerjaLabel = 'Global (Semua)';
            }

            $r['is_custom_jam_kerja'] = $isCustomJam;
            $r['jam_kerja_label'] = $jamKerjaLabel;
            $r['jam_masuk_mulai_efektif'] = $jMasukMulai;
            $r['jam_masuk_batas_efektif'] = $jMasukBatas;
            $r['jam_pulang_mulai_efektif'] = $jPulangMulai;

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

        if (!$pegawaiId) {
            return [
                'nama_kantor' => $global['nama_kantor_default'],
                'latitude' => (float)$global['latitude_default'],
                'longitude' => (float)$global['longitude_default'],
                'radius_meter' => (int)$global['radius_meter'],
                'mode' => 'default',
                'source_label' => 'Lokasi Semula (Default)',
                'is_custom_jam_kerja' => false,
                'jam_kerja_label' => 'Global (Semua)',
                'jam_masuk_mulai' => $global['jam_masuk_mulai'] ?: '06:00:00',
                'jam_masuk_batas' => $global['jam_masuk_batas'] ?: '07:30:00',
                'jam_pulang_mulai' => $global['jam_pulang_mulai'] ?: '15:00:00',
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
                    'jam_masuk_mulai' => $item['jam_masuk_mulai_efektif'] ?? $global['jam_masuk_mulai'],
                    'jam_masuk_batas' => $item['jam_masuk_batas_efektif'] ?? $global['jam_masuk_batas'],
                    'jam_pulang_mulai' => $item['jam_pulang_mulai_efektif'] ?? $global['jam_pulang_mulai'],
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
            'jam_kerja_label' => 'Global (Semua)',
            'jam_masuk_mulai' => $global['jam_masuk_mulai'] ?: '06:00:00',
            'jam_masuk_batas' => $global['jam_masuk_batas'] ?: '07:30:00',
            'jam_pulang_mulai' => $global['jam_pulang_mulai'] ?: '15:00:00',
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

        return [
            'totalPegawai' => count($all),
            'totalDefault' => $cntDefault,
            'totalCustomUnit' => $cntUnit,
            'totalCustomPegawai' => $cntCustom,
            'totalUnit' => count($units),
            'unitCustomCount' => $cntCustomUnit,
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
        $jamBatas = $global['jam_masuk_batas'] ?: '07:30:00';

        $where = ["p.is_active = 1"];
        $params = [$targetDate];

        if (!empty($filters['search'])) {
            $where[] = "(p.nama LIKE ? OR p.niy LIKE ? OR p.jabatan LIKE ?)";
            $term = '%' . trim($filters['search']) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['unit_tugas'])) {
            if ($filters['unit_tugas'] === 'Umum / Yayasan') {
                $where[] = "(p.unit_tugas IS NULL OR TRIM(p.unit_tugas) = '' OR TRIM(p.unit_tugas) = 'Umum / Yayasan')";
            } else {
                $where[] = "TRIM(p.unit_tugas) = ?";
                $params[] = trim($filters['unit_tugas']);
            }
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'Belum Absen') {
                $where[] = "apl.jam_masuk IS NULL";
            } else {
                $where[] = "apl.status = ?";
                $params[] = $filters['status'];
            }
        }

        $whereSql = implode(' AND ', $where);

        $rows = self::db()->findAll("
            SELECT 
                p.id, p.niy, p.nama, p.gelar, p.foto, p.unit_tugas, p.jabatan,
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
                l.jam_masuk_batas as peg_jam_masuk_batas,
                l.jam_pulang_mulai as peg_jam_pulang_mulai
            FROM `pegawai` p
            LEFT JOIN `absen_pegawai_lokasi` l ON l.pegawai_id = p.id
            LEFT JOIN `absen_pegawai_log` apl ON apl.pegawai_id = p.id AND apl.tanggal = ?
            WHERE {$whereSql}
            ORDER BY 
                CASE 
                    WHEN apl.status = 'Terlambat' THEN 1 
                    WHEN apl.status = 'Hadir' THEN 2 
                    ELSE 3 
                END,
                apl.jam_masuk ASC,
                p.nama ASC
        ", $params);

        foreach ($rows as &$r) {
            $r['status'] = $r['status'] ?? $r['log_status'] ?? null;
            $r['keterangan'] = $r['keterangan'] ?? $r['log_keterangan'] ?? null;
            $r['is_custom_jam_kerja'] = !empty($r['peg_jam_masuk_batas']);
            $r['jam_masuk_batas_efektif'] = !empty($r['peg_jam_masuk_batas']) ? $r['peg_jam_masuk_batas'] : $jamBatas;
            $r['jam_pulang_mulai_efektif'] = !empty($r['peg_jam_pulang_mulai']) ? $r['peg_jam_pulang_mulai'] : ($global['jam_pulang_mulai'] ?: '15:00:00');
        }
        unset($r);

        // Hitung statistik untuk tanggal terpilih
        $allStatsRows = self::db()->findAll("
            SELECT p.id, apl.status, apl.jam_masuk, apl.jam_pulang
            FROM `pegawai` p
            LEFT JOIN `absen_pegawai_log` apl ON apl.pegawai_id = p.id AND apl.tanggal = ?
            WHERE p.is_active = 1
        ", [$targetDate]);

        $totalPegawai = count($allStatsRows);
        $totalHadir = 0;
        $totalTerlambat = 0;
        $totalPulang = 0;
        $totalBelumAbsen = 0;

        foreach ($allStatsRows as $sr) {
            if (!empty($sr['jam_masuk'])) {
                if ($sr['status'] === 'Terlambat') {
                    $totalTerlambat++;
                } else {
                    $totalHadir++;
                }
                if (!empty($sr['jam_pulang'])) {
                    $totalPulang++;
                }
            } else {
                $totalBelumAbsen++;
            }
        }

        return [
            'date' => $targetDate,
            'tanggal' => $targetDate,
            'rows' => $rows,
            'logs' => $rows,
            'batasJamMasuk' => $jamBatas,
            'jamPulangMulai' => $global['jam_pulang_mulai'] ?: '15:00:00',
            'stats' => [
                'totalPegawai' => $totalPegawai,
                'totalHadir' => $totalHadir,
                'totalTerlambat' => $totalTerlambat,
                'totalPulang' => $totalPulang,
                'totalBelum' => $totalBelumAbsen,
                'totalBelumAbsen' => $totalBelumAbsen,
                'jamMasukBatas' => date('H:i', strtotime($jamBatas)) . ' WITA',
                'jamPulangMulai' => date('H:i', strtotime($global['jam_pulang_mulai'] ?: '15:00:00')) . ' WITA'
            ]
        ];
    }
}

