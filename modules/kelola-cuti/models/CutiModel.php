<?php
/**
 * CutiModel - Model untuk Modul Kelola Cuti Pegawai
 * Portal BIP
 *
 * Fitur:
 * 1. Master Jenis Cuti: Cuti Tahunan (+1/bulan s.d. maks 36) & Cuti Khusus (bisa ditambah/diedit dinamis)
 * 2. Saldo Cuti Seluruh Pegawai: Auto-sinkronisasi semua pegawai aktif + akumulasi bulanan (+1/bulan maks 36)
 * 3. Pengajuan Cuti Pegawai
 * 4. Approval (Setujui / Tolak / Batalkan) + potong/kembalikan saldo otomatis
 * 5. Riwayat Approve & Log Pengurangan / Mutasi Cuti
 */

class CutiModel
{
    private static bool $tablesInitialized = false;

    public static function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Pastikan tabel-tabel cuti sudah tersedia dan tersinkronisasi dengan data pegawai
     */
    public static function init(): void
    {
        if (self::$tablesInitialized) {
            return;
        }
        self::$tablesInitialized = true;

        $stampFile = BASE_PATH . '/storage/logs/.schema_cuti_' . date('Y_m_d') . '.stamp';
        if (file_exists($stampFile)) {
            return;
        }

        $pdo = self::db()->getConnection();

        // 1. Tabel Master Jenis Cuti
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cuti_jenis` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `kode` VARCHAR(40) NOT NULL UNIQUE,
                `nama_jenis` VARCHAR(120) NOT NULL,
                `kategori` ENUM('Tahunan', 'Khusus') NOT NULL DEFAULT 'Khusus',
                `kuota_hari` INT NOT NULL DEFAULT 12 COMMENT 'Maks hari per pengajuan / batas maksimal',
                `tambah_per_bulan` INT NOT NULL DEFAULT 0 COMMENT 'Khusus Tahunan: +1 per bulan',
                `maksimal_akumulasi` INT NOT NULL DEFAULT 36 COMMENT 'Khusus Tahunan: maksimal 36 hari',
                `potong_kuota_tahunan` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = potong saldo cuti tahunan, 0 = tidak potong',
                `butuh_lampiran` TINYINT(1) NOT NULL DEFAULT 0,
                `keterangan` TEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Tabel Saldo Cuti Pegawai
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cuti_saldo_pegawai` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pegawai_id` INT NOT NULL UNIQUE,
                `saldo_tahunan` INT NOT NULL DEFAULT 0,
                `total_didapat` INT NOT NULL DEFAULT 0,
                `total_terpakai_tahunan` INT NOT NULL DEFAULT 0,
                `total_terpakai_khusus` INT NOT NULL DEFAULT 0,
                `periode_terakhir_tambah` VARCHAR(7) NULL COMMENT 'Format YYYY-MM',
                `catatan` VARCHAR(255) NULL,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_saldo_pegawai` (`pegawai_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Tabel Pengajuan Cuti
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cuti_pengajuan` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nomor_pengajuan` VARCHAR(40) NOT NULL UNIQUE,
                `pegawai_id` INT NOT NULL,
                `jenis_cuti_id` INT NOT NULL,
                `kategori_cuti` ENUM('Tahunan', 'Khusus') NOT NULL DEFAULT 'Tahunan',
                `tanggal_mulai` DATE NOT NULL,
                `tanggal_selesai` DATE NOT NULL,
                `jumlah_hari` INT NOT NULL DEFAULT 1,
                `alasan` TEXT NOT NULL,
                `pelimpahan_tugas` VARCHAR(191) NULL,
                `alamat_selama_cuti` VARCHAR(255) NULL,
                `kontak_darurat` VARCHAR(100) NULL,
                `file_lampiran` VARCHAR(255) NULL,
                `status` ENUM('Pending', 'Disetujui', 'Ditolak', 'Dibatalkan') NOT NULL DEFAULT 'Pending',
                `potong_saldo` INT NOT NULL DEFAULT 0,
                `approved_by` INT NULL,
                `approved_by_name` VARCHAR(150) NULL,
                `approved_at` DATETIME NULL,
                `catatan_approval` TEXT NULL,
                `created_by` INT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_cuti_pegawai` (`pegawai_id`),
                INDEX `idx_cuti_status` (`status`),
                INDEX `idx_cuti_tanggal` (`tanggal_mulai`, `tanggal_selesai`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. Tabel Log Pengurangan & Mutasi Saldo Cuti
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cuti_log` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pegawai_id` INT NOT NULL,
                `pengajuan_id` INT NULL,
                `jenis_mutasi` VARCHAR(50) NOT NULL COMMENT 'Pengurangan Cuti, Tambah Bulanan (+1), Inisialisasi, Penyesuaian Manual, Pengembalian Batal',
                `tipe` ENUM('masuk', 'keluar', 'penyesuaian') NOT NULL DEFAULT 'keluar',
                `perubahan` INT NOT NULL COMMENT 'Negatif untuk pengurangan, Positif untuk penambahan',
                `saldo_sebelum` INT NOT NULL DEFAULT 0,
                `saldo_sesudah` INT NOT NULL DEFAULT 0,
                `periode_bulan` VARCHAR(7) NULL,
                `keterangan` TEXT NOT NULL,
                `created_by` INT NULL,
                `created_by_name` VARCHAR(150) NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_log_pegawai` (`pegawai_id`),
                INDEX `idx_log_pengajuan` (`pengajuan_id`),
                INDEX `idx_log_tipe` (`tipe`, `jenis_mutasi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        self::seedDefaultJenisCuti();
        self::syncAllPegawaiSaldo();

        if (!is_dir(dirname($stampFile))) {
            @mkdir(dirname($stampFile), 0755, true);
        }
        @file_put_contents($stampFile, (string)time());
    }

    /**
     * Seed data awal Jenis Cuti (Cuti Tahunan + Cuti Khusus default)
     */
    private static function seedDefaultJenisCuti(): void
    {
        $cnt = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_jenis`")['c'] ?? 0);
        if ($cnt > 0) {
            return;
        }

        $defaults = [
            [
                'kode' => 'CT-TAHUNAN',
                'nama_jenis' => 'Cuti Tahunan',
                'kategori' => 'Tahunan',
                'kuota_hari' => 36,
                'tambah_per_bulan' => 1,
                'maksimal_akumulasi' => 36,
                'potong_kuota_tahunan' => 1,
                'butuh_lampiran' => 0,
                'keterangan' => 'Hak cuti tahunan pegawai yang bertambah otomatis +1 hari setiap bulan hingga batas maksimal 36 hari.'
            ],
            [
                'kode' => 'CK-MELAHIRKAN',
                'nama_jenis' => 'Cuti Melahirkan / Bersalin',
                'kategori' => 'Khusus',
                'kuota_hari' => 90,
                'tambah_per_bulan' => 0,
                'maksimal_akumulasi' => 90,
                'potong_kuota_tahunan' => 0,
                'butuh_lampiran' => 1,
                'keterangan' => 'Cuti khusus bagi karyawati/guru perempuan yang melahirkan (maksimal 90 hari kalender) tanpa memotong saldo cuti tahunan.'
            ],
            [
                'kode' => 'CK-MENIKAH',
                'nama_jenis' => 'Cuti Menikah',
                'kategori' => 'Khusus',
                'kuota_hari' => 3,
                'tambah_per_bulan' => 0,
                'maksimal_akumulasi' => 3,
                'potong_kuota_tahunan' => 0,
                'butuh_lampiran' => 1,
                'keterangan' => 'Cuti khusus pernikahan pegawai (maksimal 3 hari kerja) tanpa memotong cuti tahunan.'
            ],
            [
                'kode' => 'CK-SAKIT',
                'nama_jenis' => 'Cuti Sakit (Rawat Inap / Surat Dokter)',
                'kategori' => 'Khusus',
                'kuota_hari' => 14,
                'tambah_per_bulan' => 0,
                'maksimal_akumulasi' => 14,
                'potong_kuota_tahunan' => 0,
                'butuh_lampiran' => 1,
                'keterangan' => 'Cuti khusus sakit dengan surat keterangan dokter / rawat inap.'
            ],
            [
                'kode' => 'CK-DUKA',
                'nama_jenis' => 'Cuti Kedukaan (Keluarga Inti Meninggal)',
                'kategori' => 'Khusus',
                'kuota_hari' => 3,
                'tambah_per_bulan' => 0,
                'maksimal_akumulasi' => 3,
                'potong_kuota_tahunan' => 0,
                'butuh_lampiran' => 0,
                'keterangan' => 'Cuti alasan penting apabila keluarga inti (suami/istri/anak/orang tua/mertua) meninggal dunia.'
            ],
            [
                'kode' => 'CK-IBADAH',
                'nama_jenis' => 'Cuti Ibadah Haji / Umrah',
                'kategori' => 'Khusus',
                'kuota_hari' => 40,
                'tambah_per_bulan' => 0,
                'maksimal_akumulasi' => 40,
                'potong_kuota_tahunan' => 0,
                'butuh_lampiran' => 1,
                'keterangan' => 'Cuti khusus pelaksanaan ibadah Haji atau Umrah sesuai jadwal keberangkatan resmi.'
            ],
        ];

        foreach ($defaults as $d) {
            self::db()->query("
                INSERT IGNORE INTO `cuti_jenis`
                (`kode`, `nama_jenis`, `kategori`, `kuota_hari`, `tambah_per_bulan`, `maksimal_akumulasi`, `potong_kuota_tahunan`, `butuh_lampiran`, `keterangan`, `is_active`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ", [
                $d['kode'],
                $d['nama_jenis'],
                $d['kategori'],
                $d['kuota_hari'],
                $d['tambah_per_bulan'],
                $d['maksimal_akumulasi'],
                $d['potong_kuota_tahunan'],
                $d['butuh_lampiran'],
                $d['keterangan']
            ]);
        }
    }

    /**
     * Ambil konfigurasi Cuti Tahunan (penambahan per bulan & batas maksimal)
     */
    public static function getConfigTahunan(): array
    {
        $row = self::db()->find("SELECT * FROM `cuti_jenis` WHERE `kategori` = 'Tahunan' ORDER BY id ASC LIMIT 1");
        if (!$row) {
            return [
                'id' => 1,
                'tambah_per_bulan' => 1,
                'maksimal_akumulasi' => 36,
                'kuota_hari' => 36
            ];
        }
        return [
            'id' => (int)$row['id'],
            'tambah_per_bulan' => max(1, (int)($row['tambah_per_bulan'] ?: 1)),
            'maksimal_akumulasi' => max(1, (int)($row['maksimal_akumulasi'] ?: 36)),
            'kuota_hari' => max(1, (int)($row['kuota_hari'] ?: 36)),
            'nama_jenis' => $row['nama_jenis'],
            'keterangan' => $row['keterangan']
        ];
    }

    /**
     * Sinkronisasi seluruh pegawai aktif ke tabel cuti_saldo_pegawai
     * serta proses akumulasi otomatis +1 setiap pergantian bulan hingga maksimal 36 hari.
     */
    public static function syncAllPegawaiSaldo(): void
    {
        $cfg = self::getConfigTahunan();
        $addPerMonth = $cfg['tambah_per_bulan']; // 1
        $maxCap = $cfg['maksimal_akumulasi'];    // 36
        $currentPeriod = date('Y-m');

        // 1. Cari pegawai aktif yang belum punya record di cuti_saldo_pegawai
        $missingPegawai = self::db()->findAll("
            SELECT p.id, p.nama, p.tanggal_masuk, p.tmt
            FROM `pegawai` p
            LEFT JOIN `cuti_saldo_pegawai` s ON s.pegawai_id = p.id
            WHERE p.is_active = 1 AND s.id IS NULL
        ");

        foreach ($missingPegawai as $mp) {
            // Hitung saldo awal berdasarkan bulan berjalan di tahun ini (atau minimal 1 hari untuk bulan aktif, maks 36)
            // Jika ada tanggal_masuk / tmt, hitung selisih bulan kerja (dibatasi maksimal $maxCap = 36)
            $initialSaldo = (int)date('n'); // Default: jumlah bulan berjalan di tahun ini (misal bulan ke-11 = 11 hari)
            $tglMasuk = !empty($mp['tanggal_masuk']) ? $mp['tanggal_masuk'] : (!empty($mp['tmt']) ? $mp['tmt'] : null);
            if (!empty($tglMasuk)) {
                try {
                    $d1 = new DateTime($tglMasuk);
                    $d2 = new DateTime();
                    if ($d1 <= $d2) {
                        $diff = $d1->diff($d2);
                        $monthsWorked = ($diff->y * 12) + $diff->m + 1;
                        $initialSaldo = min($maxCap, max(1, $monthsWorked * $addPerMonth));
                    } else {
                        $initialSaldo = $addPerMonth;
                    }
                } catch (Throwable $e) {
                    $initialSaldo = min($maxCap, max(1, $initialSaldo));
                }
            } else {
                $initialSaldo = min($maxCap, max(1, $initialSaldo));
            }

            self::db()->query("
                INSERT IGNORE INTO `cuti_saldo_pegawai`
                (`pegawai_id`, `saldo_tahunan`, `total_didapat`, `total_terpakai_tahunan`, `total_terpakai_khusus`, `periode_terakhir_tambah`, `catatan`)
                VALUES (?, ?, ?, 0, 0, ?, 'Inisialisasi otomatis saldo cuti tahunan')
            ", [(int)$mp['id'], $initialSaldo, $initialSaldo, $currentPeriod]);

            self::db()->query("
                INSERT INTO `cuti_log`
                (`pegawai_id`, `pengajuan_id`, `jenis_mutasi`, `tipe`, `perubahan`, `saldo_sebelum`, `saldo_sesudah`, `periode_bulan`, `keterangan`, `created_by_name`)
                VALUES (?, NULL, 'Inisialisasi Saldo', 'masuk', ?, 0, ?, ?, ?, 'Sistem Otomatis')
            ", [
                (int)$mp['id'],
                $initialSaldo,
                $initialSaldo,
                $currentPeriod,
                "Inisialisasi kuota Cuti Tahunan (+{$addPerMonth}/bulan, maks {$maxCap} hari)"
            ]);
        }

        // 2. Cek pegawai yang periode_terakhir_tambah-nya belum bulan ini (otomatis tambah +1 per bulan s.d. maks 36)
        $outdatedRows = self::db()->findAll("
            SELECT s.*, p.nama
            FROM `cuti_saldo_pegawai` s
            JOIN `pegawai` p ON p.id = s.pegawai_id
            WHERE p.is_active = 1
              AND (s.periode_terakhir_tambah IS NULL OR s.periode_terakhir_tambah < ?)
        ", [$currentPeriod]);

        foreach ($outdatedRows as $row) {
            $lastPeriod = $row['periode_terakhir_tambah'];
            $monthsDiff = 1;
            if (!empty($lastPeriod) && preg_match('/^\d{4}-\d{2}$/', $lastPeriod)) {
                try {
                    $dLast = new DateTime($lastPeriod . '-01');
                    $dCurr = new DateTime($currentPeriod . '-01');
                    if ($dLast < $dCurr) {
                        $diff = $dLast->diff($dCurr);
                        $monthsDiff = max(1, ($diff->y * 12) + $diff->m);
                    } else {
                        $monthsDiff = 0;
                    }
                } catch (Throwable $e) {
                    $monthsDiff = 1;
                }
            }

            if ($monthsDiff > 0) {
                $saldoLama = (int)$row['saldo_tahunan'];
                $tambahan = $monthsDiff * $addPerMonth;
                $saldoBaru = min($maxCap, $saldoLama + $tambahan);
                $actualAdded = max(0, $saldoBaru - $saldoLama);

                self::db()->query("
                    UPDATE `cuti_saldo_pegawai`
                    SET `saldo_tahunan` = ?,
                        `total_didapat` = `total_didapat` + ?,
                        `periode_terakhir_tambah` = ?
                    WHERE `id` = ?
                ", [$saldoBaru, $actualAdded, $currentPeriod, (int)$row['id']]);

                if ($actualAdded > 0) {
                    self::db()->query("
                        INSERT INTO `cuti_log`
                        (`pegawai_id`, `pengajuan_id`, `jenis_mutasi`, `tipe`, `perubahan`, `saldo_sebelum`, `saldo_sesudah`, `periode_bulan`, `keterangan`, `created_by_name`)
                        VALUES (?, NULL, 'Tambah Bulanan (+1)', 'masuk', ?, ?, ?, ?, ?, 'Sistem Otomatis')
                    ", [
                        (int)$row['pegawai_id'],
                        $actualAdded,
                        $saldoLama,
                        $saldoBaru,
                        $currentPeriod,
                        "Penambahan otomatis kuota Cuti Tahunan periode {$currentPeriod} (+{$actualAdded} hari, maks {$maxCap} hari)"
                    ]);
                }
            }
        }
    }

    /**
     * Trigger tambah kuota bulanan (+1) secara manual oleh Admin (dibatasi maksimal 36)
     */
    public static function triggerTambahBulananManual(?int $adminId = null, string $adminName = 'Admin'): int
    {
        self::init();
        $cfg = self::getConfigTahunan();
        $addPerMonth = $cfg['tambah_per_bulan'];
        $maxCap = $cfg['maksimal_akumulasi'];
        $currentPeriod = date('Y-m');

        $rows = self::db()->findAll("
            SELECT s.*, p.nama
            FROM `cuti_saldo_pegawai` s
            JOIN `pegawai` p ON p.id = s.pegawai_id
            WHERE p.is_active = 1 AND s.saldo_tahunan < ?
        ", [$maxCap]);

        $updatedCount = 0;
        foreach ($rows as $row) {
            $saldoLama = (int)$row['saldo_tahunan'];
            $saldoBaru = min($maxCap, $saldoLama + $addPerMonth);
            $selisih = $saldoBaru - $saldoLama;

            if ($selisih > 0) {
                self::db()->query("
                    UPDATE `cuti_saldo_pegawai`
                    SET `saldo_tahunan` = ?,
                        `total_didapat` = `total_didapat` + ?,
                        `periode_terakhir_tambah` = ?
                    WHERE `id` = ?
                ", [$saldoBaru, $selisih, $currentPeriod, (int)$row['id']]);

                self::db()->query("
                    INSERT INTO `cuti_log`
                    (`pegawai_id`, `pengajuan_id`, `jenis_mutasi`, `tipe`, `perubahan`, `saldo_sebelum`, `saldo_sesudah`, `periode_bulan`, `keterangan`, `created_by`, `created_by_name`)
                    VALUES (?, NULL, 'Tambah Bulanan (+1)', 'masuk', ?, ?, ?, ?, ?, ?, ?)
                ", [
                    (int)$row['pegawai_id'],
                    $selisih,
                    $saldoLama,
                    $saldoBaru,
                    $currentPeriod,
                    "Penambahan bulanan Cuti Tahunan (+{$selisih} hari, maks {$maxCap} hari)",
                    $adminId,
                    $adminName
                ]);
                $updatedCount++;
            }
        }
        return $updatedCount;
    }

    /**
     * Sesuaikan saldo cuti tahunan 1 pegawai secara manual (dan catat di log)
     */
    public static function adjustSaldoPegawai(int $pegawaiId, int $saldoBaru, string $keterangan, ?int $adminId = null, string $adminName = 'Admin'): void
    {
        self::init();
        $cfg = self::getConfigTahunan();
        $maxCap = $cfg['maksimal_akumulasi'];

        $saldoBaru = max(0, min($maxCap, $saldoBaru));
        $row = self::db()->find("SELECT * FROM `cuti_saldo_pegawai` WHERE `pegawai_id` = ?", [$pegawaiId]);

        $saldoLama = (int)($row['saldo_tahunan'] ?? 0);
        $perubahan = $saldoBaru - $saldoLama;

        if ($row) {
            self::db()->query("
                UPDATE `cuti_saldo_pegawai`
                SET `saldo_tahunan` = ?,
                    `catatan` = ?
                WHERE `pegawai_id` = ?
            ", [$saldoBaru, $keterangan, $pegawaiId]);
        } else {
            self::db()->query("
                INSERT INTO `cuti_saldo_pegawai`
                (`pegawai_id`, `saldo_tahunan`, `total_didapat`, `periode_terakhir_tambah`, `catatan`)
                VALUES (?, ?, ?, ?, ?)
            ", [$pegawaiId, $saldoBaru, $saldoBaru, date('Y-m'), $keterangan]);
        }

        $tipe = $perubahan < 0 ? 'keluar' : ($perubahan > 0 ? 'masuk' : 'penyesuaian');
        $jenisMutasi = $perubahan < 0 ? 'Pengurangan Cuti' : 'Penyesuaian Manual';

        self::db()->query("
            INSERT INTO `cuti_log`
            (`pegawai_id`, `pengajuan_id`, `jenis_mutasi`, `tipe`, `perubahan`, `saldo_sebelum`, `saldo_sesudah`, `periode_bulan`, `keterangan`, `created_by`, `created_by_name`)
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $pegawaiId,
            $jenisMutasi,
            $tipe,
            $perubahan,
            $saldoLama,
            $saldoBaru,
            date('Y-m'),
            $keterangan !== '' ? $keterangan : "Penyesuaian manual saldo Cuti Tahunan menjadi {$saldoBaru} hari",
            $adminId,
            $adminName
        ]);
    }

    /**
     * Ambil statistik KPI untuk header modul Kelola Cuti
     */
    public static function getSummaryStats(): array
    {
        self::init();
        $today = date('Y-m-d');

        $totalPegawai = (int)(self::db()->find("SELECT COUNT(*) as c FROM `pegawai` WHERE `is_active` = 1")['c'] ?? 0);
        $totalPending = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_pengajuan` WHERE `status` = 'Pending'")['c'] ?? 0);
        $totalApproved = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_pengajuan` WHERE `status` = 'Disetujui'")['c'] ?? 0);
        $totalRejected = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_pengajuan` WHERE `status` = 'Ditolak'")['c'] ?? 0);
        $sedangCutiHariIni = (int)(self::db()->find("
            SELECT COUNT(DISTINCT pegawai_id) as c
            FROM `cuti_pengajuan`
            WHERE `status` = 'Disetujui'
              AND ? BETWEEN `tanggal_mulai` AND `tanggal_selesai`
        ", [$today])['c'] ?? 0);
        $totalJenisKhusus = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_jenis` WHERE `kategori` = 'Khusus' AND `is_active` = 1")['c'] ?? 0);
        $totalLogPengurangan = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_log` WHERE `perubahan` < 0")['c'] ?? 0);

        return [
            'totalPegawai' => $totalPegawai,
            'totalPending' => $totalPending,
            'totalApproved' => $totalApproved,
            'totalRejected' => $totalRejected,
            'sedangCutiHariIni' => $sedangCutiHariIni,
            'totalJenisKhusus' => $totalJenisKhusus,
            'totalLogPengurangan' => $totalLogPengurangan,
            'configTahunan' => self::getConfigTahunan()
        ];
    }

    /**
     * Ambil daftar seluruh pegawai beserta saldo cuti, pemakaian, dan status cuti aktif
     */
    public static function getAllPegawaiCuti(array $filters = []): array
    {
        self::init();
        $today = date('Y-m-d');
        $where = ["p.is_active = 1"];
        $params = [$today];

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(p.nama LIKE ? OR p.niy LIKE ? OR p.nik LIKE ? OR p.jabatan LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if (!empty($filters['unit_tugas'])) {
            $where[] = "p.unit_tugas = ?";
            $params[] = $filters['unit_tugas'];
        }

        if (!empty($filters['jabatan'])) {
            $where[] = "p.jabatan = ?";
            $params[] = $filters['jabatan'];
        }

        if (!empty($filters['status_pegawai'])) {
            $where[] = "(p.status_pegawai = ? OR p.status_kerja = ?)";
            $params[] = $filters['status_pegawai'];
            $params[] = $filters['status_pegawai'];
        }

        $whereSql = implode(' AND ', $where);

        return self::db()->findAll("
            SELECT 
                p.id, p.niy, p.nik, p.nama, p.gelar, p.foto, p.unit_tugas, p.jabatan,
                p.status_pegawai, p.status_kerja, p.tanggal_masuk, p.tmt, p.no_wa,
                COALESCE(s.saldo_tahunan, 0) as saldo_tahunan,
                COALESCE(s.total_didapat, 0) as total_didapat,
                COALESCE(s.total_terpakai_tahunan, 0) as total_terpakai_tahunan,
                COALESCE(s.total_terpakai_khusus, 0) as total_terpakai_khusus,
                s.periode_terakhir_tambah,
                s.catatan as catatan_saldo,
                (
                    SELECT COUNT(*) FROM `cuti_pengajuan` cp 
                    WHERE cp.pegawai_id = p.id AND cp.status = 'Pending'
                ) as jumlah_pending,
                (
                    SELECT COUNT(*) FROM `cuti_pengajuan` cp2 
                    WHERE cp2.pegawai_id = p.id 
                      AND cp2.status = 'Disetujui' 
                      AND ? BETWEEN cp2.tanggal_mulai AND cp2.tanggal_selesai
                ) as sedang_cuti
            FROM `pegawai` p
            LEFT JOIN `cuti_saldo_pegawai` s ON s.pegawai_id = p.id
            WHERE {$whereSql}
            ORDER BY p.nama ASC
        ", $params);
    }

    /**
     * Ambil semua jenis cuti (Tahunan & Khusus)
     */
    public static function getAllJenisCuti(bool $onlyActive = false): array
    {
        self::init();
        $where = $onlyActive ? "WHERE `is_active` = 1" : "";
        return self::db()->findAll("
            SELECT j.*,
                   (SELECT COUNT(*) FROM `cuti_pengajuan` cp WHERE cp.jenis_cuti_id = j.id) as total_pengajuan
            FROM `cuti_jenis` j
            {$where}
            ORDER BY FIELD(j.kategori, 'Tahunan', 'Khusus'), j.id ASC
        ");
    }

    /**
     * Simpan / Update Jenis Cuti (Khusus maupun pengaturan Cuti Tahunan)
     */
    public static function saveJenisCuti(array $data, ?int $id = null): int
    {
        self::init();
        $nama = trim($data['nama_jenis'] ?? '');
        if ($nama === '') {
            throw new Exception('Nama jenis cuti wajib diisi.');
        }

        $kategori = ($data['kategori'] ?? 'Khusus') === 'Tahunan' ? 'Tahunan' : 'Khusus';
        $kuotaHari = max(1, (int)($data['kuota_hari'] ?? ($kategori === 'Tahunan' ? 36 : 3)));
        $tambahPerBulan = $kategori === 'Tahunan' ? max(1, (int)($data['tambah_per_bulan'] ?? 1)) : 0;
        $maksAkumulasi = $kategori === 'Tahunan' ? max(1, (int)($data['maksimal_akumulasi'] ?? 36)) : $kuotaHari;
        $potongTahunan = $kategori === 'Tahunan' ? 1 : (!empty($data['potong_kuota_tahunan']) ? 1 : 0);
        $butuhLampiran = !empty($data['butuh_lampiran']) ? 1 : 0;
        $keterangan = trim($data['keterangan'] ?? '');
        $isActive = isset($data['is_active']) ? ((int)$data['is_active'] ? 1 : 0) : 1;

        if ($id) {
            self::db()->query("
                UPDATE `cuti_jenis`
                SET `nama_jenis` = ?,
                    `kuota_hari` = ?,
                    `tambah_per_bulan` = ?,
                    `maksimal_akumulasi` = ?,
                    `potong_kuota_tahunan` = ?,
                    `butuh_lampiran` = ?,
                    `keterangan` = ?,
                    `is_active` = ?
                WHERE `id` = ?
            ", [$nama, $kuotaHari, $tambahPerBulan, $maksAkumulasi, $potongTahunan, $butuhLampiran, $keterangan, $isActive, $id]);
            return $id;
        }

        $kode = trim($data['kode'] ?? '');
        if ($kode === '') {
            $slug = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', substr($nama, 0, 8)));
            $kode = 'CK-' . ($slug ?: 'KHUSUS') . '-' . rand(10, 99);
        }

        self::db()->query("
            INSERT INTO `cuti_jenis`
            (`kode`, `nama_jenis`, `kategori`, `kuota_hari`, `tambah_per_bulan`, `maksimal_akumulasi`, `potong_kuota_tahunan`, `butuh_lampiran`, `keterangan`, `is_active`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [$kode, $nama, $kategori, $kuotaHari, $tambahPerBulan, $maksAkumulasi, $potongTahunan, $butuhLampiran, $keterangan, $isActive]);

        return (int)self::db()->getConnection()->lastInsertId();
    }

    /**
     * Hapus Jenis Cuti Khusus
     */
    public static function deleteJenisCuti(int $id): void
    {
        self::init();
        $jenis = self::db()->find("SELECT * FROM `cuti_jenis` WHERE `id` = ?", [$id]);
        if (!$jenis) {
            throw new Exception('Jenis cuti tidak ditemukan.');
        }
        if ($jenis['kategori'] === 'Tahunan') {
            throw new Exception('Jenis Cuti Tahunan utama tidak dapat dihapus.');
        }
        $used = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_pengajuan` WHERE `jenis_cuti_id` = ?", [$id])['c'] ?? 0);
        if ($used > 0) {
            throw new Exception("Jenis cuti ini sudah digunakan pada {$used} data pengajuan. Nonaktifkan saja statusnya jika tidak ingin dipakai lagi.");
        }
        self::db()->query("DELETE FROM `cuti_jenis` WHERE `id` = ?", [$id]);
    }

    /**
     * Generate Nomor Pengajuan Cuti Otomatis
     */
    public static function generateNomorPengajuan(): string
    {
        $prefix = 'CUTI/' . date('Y/m') . '/';
        $row = self::db()->find("
            SELECT nomor_pengajuan 
            FROM `cuti_pengajuan` 
            WHERE nomor_pengajuan LIKE ? 
            ORDER BY id DESC LIMIT 1
        ", [$prefix . '%']);

        $seq = 1;
        if ($row && !empty($row['nomor_pengajuan'])) {
            $parts = explode('/', $row['nomor_pengajuan']);
            $lastNum = end($parts);
            if (is_numeric($lastNum)) {
                $seq = (int)$lastNum + 1;
            }
        }

        do {
            $candidate = $prefix . str_pad((string)$seq, 3, '0', STR_PAD_LEFT);
            $exists = self::db()->find("SELECT id FROM `cuti_pengajuan` WHERE `nomor_pengajuan` = ?", [$candidate]);
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Buat Pengajuan Cuti Baru
     */
    public static function createPengajuan(array $data, ?int $userId = null, string $userName = 'Admin'): int
    {
        self::init();
        $pegawaiId = (int)($data['pegawai_id'] ?? 0);
        $jenisCutiId = (int)($data['jenis_cuti_id'] ?? 0);
        $tglMulai = trim($data['tanggal_mulai'] ?? '');
        $tglSelesai = trim($data['tanggal_selesai'] ?? '');
        $alasan = trim($data['alasan'] ?? '');

        if ($pegawaiId <= 0 || $jenisCutiId <= 0 || $tglMulai === '' || $tglSelesai === '' || $alasan === '') {
            throw new Exception('Pegawai, Jenis Cuti, Tanggal Mulai/Selesai, dan Alasan wajib diisi lengkap.');
        }

        $d1 = new DateTime($tglMulai);
        $d2 = new DateTime($tglSelesai);
        if ($d2 < $d1) {
            throw new Exception('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
        }

        $hitungHari = (int)($data['jumlah_hari'] ?? 0);
        if ($hitungHari <= 0) {
            $hitungHari = $d1->diff($d2)->days + 1;
        }

        $jenis = self::db()->find("SELECT * FROM `cuti_jenis` WHERE `id` = ?", [$jenisCutiId]);
        if (!$jenis) {
            throw new Exception('Jenis cuti tidak valid.');
        }

        // Cek saldo jika memotong kuota tahunan
        $saldoRow = self::db()->find("SELECT * FROM `cuti_saldo_pegawai` WHERE `pegawai_id` = ?", [$pegawaiId]);
        $saldoSaatIni = (int)($saldoRow['saldo_tahunan'] ?? 0);

        if ((int)$jenis['potong_kuota_tahunan'] === 1 && $hitungHari > $saldoSaatIni) {
            throw new Exception("Saldo Cuti Tahunan pegawai tidak mencukupi (Sisa saldo: {$saldoSaatIni} hari, diajukan: {$hitungHari} hari).");
        }

        if ($jenis['kategori'] === 'Khusus' && (int)$jenis['kuota_hari'] > 0 && $hitungHari > (int)$jenis['kuota_hari']) {
            throw new Exception("Jumlah hari melebihi batas maksimal {$jenis['nama_jenis']} ({$jenis['kuota_hari']} hari).");
        }

        $nomor = self::generateNomorPengajuan();

        self::db()->query("
            INSERT INTO `cuti_pengajuan` (
                `nomor_pengajuan`, `pegawai_id`, `jenis_cuti_id`, `kategori_cuti`,
                `tanggal_mulai`, `tanggal_selesai`, `jumlah_hari`, `alasan`,
                `pelimpahan_tugas`, `alamat_selama_cuti`, `kontak_darurat`, `file_lampiran`,
                `status`, `created_by`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
        ", [
            $nomor,
            $pegawaiId,
            $jenisCutiId,
            $jenis['kategori'],
            $tglMulai,
            $tglSelesai,
            $hitungHari,
            $alasan,
            trim($data['pelimpahan_tugas'] ?? '') ?: null,
            trim($data['alamat_selama_cuti'] ?? '') ?: null,
            trim($data['kontak_darurat'] ?? '') ?: null,
            $data['file_lampiran'] ?? null,
            $userId
        ]);

        $newId = (int)self::db()->getConnection()->lastInsertId();

        // Jika admin memilih "Langsung Setujui (Auto-Approve)"
        if (!empty($data['langsung_approve'])) {
            self::approvePengajuan($newId, 'Disetujui', 'Disetujui langsung saat input pengajuan oleh ' . $userName, $userId, $userName);
        }

        return $newId;
    }

    /**
     * Proses Approve / Tolak / Batalkan Pengajuan Cuti
     * Saat Disetujui: otomatis potong saldo_tahunan (jika potong_kuota_tahunan = 1) & tulis ke cuti_log!
     */
    public static function approvePengajuan(int $pengajuanId, string $keputusan, string $catatan = '', ?int $approverId = null, string $approverName = 'Admin'): void
    {
        self::init();
        if (!in_array($keputusan, ['Disetujui', 'Ditolak', 'Dibatalkan'], true)) {
            throw new Exception('Status keputusan tidak valid.');
        }

        $pengajuan = self::db()->find("
            SELECT cp.*, j.nama_jenis, j.kategori, j.potong_kuota_tahunan, p.nama as nama_pegawai
            FROM `cuti_pengajuan` cp
            JOIN `cuti_jenis` j ON j.id = cp.jenis_cuti_id
            JOIN `pegawai` p ON p.id = cp.pegawai_id
            WHERE cp.id = ?
        ", [$pengajuanId]);

        if (!$pengajuan) {
            throw new Exception('Data pengajuan cuti tidak ditemukan.');
        }

        $statusLama = $pengajuan['status'];
        if ($statusLama === $keputusan) {
            return;
        }

        $pegawaiId = (int)$pengajuan['pegawai_id'];
        $jumlahHari = (int)$pengajuan['jumlah_hari'];
        $potongTahunan = (int)$pengajuan['potong_kuota_tahunan'] === 1;

        $saldoRow = self::db()->find("SELECT * FROM `cuti_saldo_pegawai` WHERE `pegawai_id` = ?", [$pegawaiId]);
        $saldoSekarang = (int)($saldoRow['saldo_tahunan'] ?? 0);

        $pdo = self::db()->getConnection();
        $pdo->beginTransaction();

        try {
            // KASUS 1: Dari Pending -> Disetujui
            if ($statusLama === 'Pending' && $keputusan === 'Disetujui') {
                $potong = 0;
                if ($potongTahunan) {
                    if ($saldoSekarang < $jumlahHari) {
                        throw new Exception("Saldo Cuti Tahunan {$pengajuan['nama_pegawai']} tidak mencukupi (Sisa: {$saldoSekarang} hari, dibutuhkan: {$jumlahHari} hari).");
                    }
                    $potong = $jumlahHari;
                    $saldoBaru = $saldoSekarang - $potong;

                    self::db()->query("
                        UPDATE `cuti_saldo_pegawai`
                        SET `saldo_tahunan` = ?,
                            `total_terpakai_tahunan` = `total_terpakai_tahunan` + ?
                        WHERE `pegawai_id` = ?
                    ", [$saldoBaru, $potong, $pegawaiId]);

                    self::db()->query("
                        INSERT INTO `cuti_log`
                        (`pegawai_id`, `pengajuan_id`, `jenis_mutasi`, `tipe`, `perubahan`, `saldo_sebelum`, `saldo_sesudah`, `periode_bulan`, `keterangan`, `created_by`, `created_by_name`)
                        VALUES (?, ?, 'Pengurangan Cuti', 'keluar', ?, ?, ?, ?, ?, ?, ?)
                    ", [
                        $pegawaiId,
                        $pengajuanId,
                        -$potong,
                        $saldoSekarang,
                        $saldoBaru,
                        date('Y-m'),
                        "Pengurangan Cuti ({$pengajuan['nama_jenis']}) selama {$potong} hari [{$pengajuan['nomor_pengajuan']}]: {$pengajuan['alasan']}",
                        $approverId,
                        $approverName
                    ]);
                } else {
                    // Cuti Khusus yang tidak memotong saldo tahunan
                    self::db()->query("
                        UPDATE `cuti_saldo_pegawai`
                        SET `total_terpakai_khusus` = `total_terpakai_khusus` + ?
                        WHERE `pegawai_id` = ?
                    ", [$jumlahHari, $pegawaiId]);

                    self::db()->query("
                        INSERT INTO `cuti_log`
                        (`pegawai_id`, `pengajuan_id`, `jenis_mutasi`, `tipe`, `perubahan`, `saldo_sebelum`, `saldo_sesudah`, `periode_bulan`, `keterangan`, `created_by`, `created_by_name`)
                        VALUES (?, ?, 'Cuti Khusus (Tanpa Potong Tahunan)', 'penyesuaian', 0, ?, ?, ?, ?, ?, ?)
                    ", [
                        $pegawaiId,
                        $pengajuanId,
                        $saldoSekarang,
                        $saldoSekarang,
                        date('Y-m'),
                        "Disetujui {$pengajuan['nama_jenis']} selama {$jumlahHari} hari (tidak memotong saldo Cuti Tahunan) [{$pengajuan['nomor_pengajuan']}]",
                        $approverId,
                        $approverName
                    ]);
                }

                self::db()->query("
                    UPDATE `cuti_pengajuan`
                    SET `status` = 'Disetujui',
                        `potong_saldo` = ?,
                        `approved_by` = ?,
                        `approved_by_name` = ?,
                        `approved_at` = NOW(),
                        `catatan_approval` = ?
                    WHERE `id` = ?
                ", [$potong, $approverId, $approverName, $catatan ?: 'Disetujui', $pengajuanId]);
            }
            // KASUS 2: Dari Pending -> Ditolak
            elseif ($statusLama === 'Pending' && $keputusan === 'Ditolak') {
                self::db()->query("
                    UPDATE `cuti_pengajuan`
                    SET `status` = 'Ditolak',
                        `approved_by` = ?,
                        `approved_by_name` = ?,
                        `approved_at` = NOW(),
                        `catatan_approval` = ?
                    WHERE `id` = ?
                ", [$approverId, $approverName, $catatan ?: 'Ditolak', $pengajuanId]);
            }
            // KASUS 3: Pembatalan Pengajuan (Jika tadinya Disetujui -> Dibatalkan, kembalikan saldo!)
            elseif ($keputusan === 'Dibatalkan') {
                if ($statusLama === 'Disetujui') {
                    $potongSebelumnya = (int)$pengajuan['potong_saldo'];
                    if ($potongSebelumnya > 0) {
                        $cfg = self::getConfigTahunan();
                        $saldoBaru = min($cfg['maksimal_akumulasi'], $saldoSekarang + $potongSebelumnya);

                        self::db()->query("
                            UPDATE `cuti_saldo_pegawai`
                            SET `saldo_tahunan` = ?,
                                `total_terpakai_tahunan` = GREATEST(0, `total_terpakai_tahunan` - ?)
                            WHERE `pegawai_id` = ?
                        ", [$saldoBaru, $potongSebelumnya, $pegawaiId]);

                        self::db()->query("
                            INSERT INTO `cuti_log`
                            (`pegawai_id`, `pengajuan_id`, `jenis_mutasi`, `tipe`, `perubahan`, `saldo_sebelum`, `saldo_sesudah`, `periode_bulan`, `keterangan`, `created_by`, `created_by_name`)
                            VALUES (?, ?, 'Pengembalian Batal', 'masuk', ?, ?, ?, ?, ?, ?, ?)
                        ", [
                            $pegawaiId,
                            $pengajuanId,
                            $potongSebelumnya,
                            $saldoSekarang,
                            $saldoBaru,
                            date('Y-m'),
                            "Pengembalian saldo cuti karena pembatalan pengajuan [{$pengajuan['nomor_pengajuan']}]",
                            $approverId,
                            $approverName
                        ]);
                    } else {
                        self::db()->query("
                            UPDATE `cuti_saldo_pegawai`
                            SET `total_terpakai_khusus` = GREATEST(0, `total_terpakai_khusus` - ?)
                            WHERE `pegawai_id` = ?
                        ", [$jumlahHari, $pegawaiId]);
                    }
                }

                self::db()->query("
                    UPDATE `cuti_pengajuan`
                    SET `status` = 'Dibatalkan',
                        `approved_by` = ?,
                        `approved_by_name` = ?,
                        `approved_at` = NOW(),
                        `catatan_approval` = ?
                    WHERE `id` = ?
                ", [$approverId, $approverName, $catatan ?: 'Dibatalkan', $pengajuanId]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Ambil daftar Pengajuan Cuti (bisa difilter untuk tab Pengajuan, tab Data Approve, maupun tab Riwayat Approve)
     */
    public static function getPengajuanList(array $filters = []): array
    {
        self::init();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $placeholders = implode(',', array_fill(0, count($filters['status']), '?'));
                $where[] = "cp.status IN ($placeholders)";
                foreach ($filters['status'] as $st) {
                    $params[] = $st;
                }
            } else {
                $where[] = "cp.status = ?";
                $params[] = $filters['status'];
            }
        }

        if (!empty($filters['kategori_cuti'])) {
            $where[] = "cp.kategori_cuti = ?";
            $params[] = $filters['kategori_cuti'];
        }

        if (!empty($filters['jenis_cuti_id'])) {
            $where[] = "cp.jenis_cuti_id = ?";
            $params[] = (int)$filters['jenis_cuti_id'];
        }

        if (!empty($filters['pegawai_id'])) {
            $where[] = "cp.pegawai_id = ?";
            $params[] = (int)$filters['pegawai_id'];
        }

        if (!empty($filters['unit_tugas'])) {
            $where[] = "p.unit_tugas = ?";
            $params[] = $filters['unit_tugas'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(p.nama LIKE ? OR p.niy LIKE ? OR cp.nomor_pengajuan LIKE ? OR cp.alasan LIKE ? OR j.nama_jenis LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);
        $orderBy = ($filters['order_by'] ?? '') === 'approved_at'
            ? 'cp.approved_at DESC, cp.id DESC'
            : 'cp.id DESC';

        return self::db()->findAll("
            SELECT 
                cp.*,
                p.nama as nama_pegawai, p.gelar, p.niy, p.unit_tugas, p.jabatan, p.foto, p.no_wa,
                j.nama_jenis, j.kode as kode_jenis, j.potong_kuota_tahunan,
                COALESCE(s.saldo_tahunan, 0) as saldo_saat_ini
            FROM `cuti_pengajuan` cp
            JOIN `pegawai` p ON p.id = cp.pegawai_id
            JOIN `cuti_jenis` j ON j.id = cp.jenis_cuti_id
            LEFT JOIN `cuti_saldo_pegawai` s ON s.pegawai_id = cp.pegawai_id
            WHERE {$whereSql}
            ORDER BY {$orderBy}
        ", $params);
    }

    /**
     * Ambil 1 detail pengajuan untuk Cetak Surat Cuti
     */
    public static function getPengajuanById(int $id): ?array
    {
        self::init();
        return self::db()->find("
            SELECT 
                cp.*,
                p.nama as nama_pegawai, p.gelar, p.niy, p.nik, p.unit_tugas, p.jabatan, p.status_pegawai, p.foto, p.no_wa,
                j.nama_jenis, j.kode as kode_jenis, j.potong_kuota_tahunan,
                COALESCE(s.saldo_tahunan, 0) as saldo_saat_ini
            FROM `cuti_pengajuan` cp
            JOIN `pegawai` p ON p.id = cp.pegawai_id
            JOIN `cuti_jenis` j ON j.id = cp.jenis_cuti_id
            LEFT JOIN `cuti_saldo_pegawai` s ON s.pegawai_id = cp.pegawai_id
            WHERE cp.id = ?
        ", [$id]) ?: null;
    }

    /**
     * Ambil daftar Log Pengurangan Cuti & Mutasi Saldo Cuti
     */
    public static function getLogList(array $filters = []): array
    {
        self::init();
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['tipe'])) {
            if ($filters['tipe'] === 'pengurangan') {
                $where[] = "(l.perubahan < 0 OR l.jenis_mutasi LIKE '%Pengurangan%')";
            } elseif ($filters['tipe'] === 'penambahan') {
                $where[] = "l.perubahan > 0";
            } else {
                $where[] = "l.tipe = ?";
                $params[] = $filters['tipe'];
            }
        }

        if (!empty($filters['pegawai_id'])) {
            $where[] = "l.pegawai_id = ?";
            $params[] = (int)$filters['pegawai_id'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $where[] = "(p.nama LIKE ? OR p.niy LIKE ? OR l.keterangan LIKE ? OR l.jenis_mutasi LIKE ? OR cp.nomor_pengajuan LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereSql = implode(' AND ', $where);

        return self::db()->findAll("
            SELECT 
                l.*,
                p.nama as nama_pegawai, p.gelar, p.niy, p.unit_tugas, p.jabatan,
                cp.nomor_pengajuan, cp.tanggal_mulai, cp.tanggal_selesai, cp.jumlah_hari
            FROM `cuti_log` l
            JOIN `pegawai` p ON p.id = l.pegawai_id
            LEFT JOIN `cuti_pengajuan` cp ON cp.id = l.pengajuan_id
            WHERE {$whereSql}
            ORDER BY l.id DESC
            LIMIT 300
        ", $params);
    }
}

