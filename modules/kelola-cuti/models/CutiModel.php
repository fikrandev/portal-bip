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
     * Pastikan kolom approver_jabatan_id selalu tersedia di tabel hierarki & step (Unconditional Auto-Migration)
     */
    public static function ensureApproverJabatanColumns(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        try {
            $pdo = self::db()->getConnection();
            $colsHier = $pdo->query("SHOW COLUMNS FROM `cuti_approval_hierarchy`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('approver_jabatan_id', $colsHier, true)) {
                $pdo->exec("ALTER TABLE `cuti_approval_hierarchy` ADD COLUMN `approver_jabatan_id` INT NULL AFTER `approver_role`");
            }
            $colsStep = $pdo->query("SHOW COLUMNS FROM `cuti_pengajuan_step`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('approver_jabatan_id', $colsStep, true)) {
                $pdo->exec("ALTER TABLE `cuti_pengajuan_step` ADD COLUMN `approver_jabatan_id` INT NULL AFTER `approver_role`");
            }
        } catch (Throwable $e) {}
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

        // Pastikan kolom baru langsung tersedia tanpa terhalang stamp lama
        self::ensureApproverJabatanColumns();

        $stampFile = BASE_PATH . '/storage/logs/.schema_cuti_v7_' . date('Y_m_d') . '.stamp';
        if (file_exists($stampFile)) {
            return;
        }

        $pdo = self::db()->getConnection();

        // Pastikan kolom tanggal_cuti_list, keterangan_tugas, dan file_lampiran_2 tersedia pada cuti_pengajuan
        try {
            $cols = $pdo->query("SHOW COLUMNS FROM `cuti_pengajuan`")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('tanggal_cuti_list', $cols, true)) {
                $pdo->exec("ALTER TABLE `cuti_pengajuan` ADD COLUMN `tanggal_cuti_list` TEXT NULL AFTER `tanggal_selesai`");
            }
            if (!in_array('keterangan_tugas', $cols, true)) {
                $pdo->exec("ALTER TABLE `cuti_pengajuan` ADD COLUMN `keterangan_tugas` TEXT NULL AFTER `pelimpahan_tugas`");
            }
            if (!in_array('file_lampiran_2', $cols, true)) {
                $pdo->exec("ALTER TABLE `cuti_pengajuan` ADD COLUMN `file_lampiran_2` VARCHAR(255) NULL AFTER `file_lampiran`");
            }
        } catch (Throwable $e) {}

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
                `tanggal_cuti_list` TEXT NULL COMMENT 'JSON array tanggal pilihan cuti, contoh: [\"2026-11-20\",\"2026-11-24\"]',
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

        // 5. Tabel Hierarki Approval Cuti yang Bisa Di-Custom
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cuti_approval_hierarchy` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `unit` VARCHAR(50) NOT NULL DEFAULT 'Semua' COMMENT 'SD, SMP, SMA, Yayasan, Semua',
                `jenis_pegawai` VARCHAR(50) NOT NULL DEFAULT 'Semua' COMMENT 'Guru, Support System, Semua',
                `step_order` INT NOT NULL DEFAULT 1,
                `step_nama` VARCHAR(120) NOT NULL,
                `approver_role` VARCHAR(100) NULL COMMENT 'Role/Jabatan: Kepala Sekolah, Manajer, Yayasan, HRD, dll',
                `approver_pegawai_id` INT NULL COMMENT 'Spesifik pegawai (opsional)',
                `keterangan` TEXT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_hier_scope` (`unit`, `jenis_pegawai`, `step_order`),
                INDEX `idx_hier_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 6. Tabel Tahapan Approval Riil Per Pengajuan Cuti (Tracking Multi-Step)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cuti_pengajuan_step` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pengajuan_id` INT NOT NULL,
                `step_order` INT NOT NULL DEFAULT 1,
                `step_nama` VARCHAR(120) NOT NULL,
                `approver_role` VARCHAR(100) NULL,
                `approver_pegawai_id` INT NULL,
                `status` ENUM('Pending', 'Disetujui', 'Ditolak', 'Dilewati') NOT NULL DEFAULT 'Pending',
                `approved_by_id` INT NULL,
                `approved_by_name` VARCHAR(150) NULL,
                `approved_at` DATETIME NULL,
                `catatan` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_step_pengajuan` (`pengajuan_id`),
                INDEX `idx_step_status` (`status`, `step_order`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        self::seedDefaultJenisCuti();
        self::seedDefaultHierarki();
        self::syncAllPegawaiSaldo();
        self::backfillUnsteppedPengajuan();

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
     * Seed Data Awal Alur Hierarki Approval Cuti jika masih kosong
     */
    private static function seedDefaultHierarki(): void
    {
        $cnt = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_approval_hierarchy`")['c'] ?? 0);
        if ($cnt > 0) {
            return;
        }

        $defaults = [
            // Alur Approval untuk Pegawai Guru (Default)
            [
                'unit' => 'Semua',
                'jenis_pegawai' => 'Guru',
                'step_order' => 1,
                'step_nama' => 'Verifikasi Kepala Sekolah / Unit',
                'approver_role' => 'Kepala Sekolah',
                'approver_jabatan_id' => 4,
                'keterangan' => 'Verifikasi ketersediaan guru pengganti di kelas dan kelengkapan dokumen pendukung.'
            ],
            [
                'unit' => 'Semua',
                'jenis_pegawai' => 'Guru',
                'step_order' => 2,
                'step_nama' => 'Persetujuan Yayasan / Pimpinan',
                'approver_role' => 'Yayasan / HRD / Pimpinan',
                'approver_jabatan_id' => null,
                'keterangan' => 'Persetujuan akhir penerbitan izin cuti resmi dan pengurangan saldo cuti tahunan.'
            ],
            // Alur Approval untuk Pegawai Support System / Staff (Default)
            [
                'unit' => 'Semua',
                'jenis_pegawai' => 'Support System',
                'step_order' => 1,
                'step_nama' => 'Verifikasi Manajer / Kepala Divisi',
                'approver_role' => 'Kepala Divisi Media dan IT / Manajer Divisi',
                'approver_jabatan_id' => 9,
                'keterangan' => 'Pemeriksaan pelimpahan tugas operasional dan koordinasi tim kerja divisi.'
            ],
            [
                'unit' => 'Semua',
                'jenis_pegawai' => 'Support System',
                'step_order' => 2,
                'step_nama' => 'Persetujuan Direktur / HRD Yayasan',
                'approver_role' => 'Direktur / HRD / Yayasan',
                'approver_jabatan_id' => null,
                'keterangan' => 'Persetujuan final pengajuan cuti dan pemotongan saldo kuota cuti tahunan.'
            ]
        ];

        foreach ($defaults as $d) {
            self::db()->query("
                INSERT INTO `cuti_approval_hierarchy`
                (`unit`, `jenis_pegawai`, `step_order`, `step_nama`, `approver_role`, `approver_jabatan_id`, `keterangan`, `is_active`)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ", [
                $d['unit'],
                $d['jenis_pegawai'],
                $d['step_order'],
                $d['step_nama'],
                $d['approver_role'],
                $d['approver_jabatan_id'] ?? null,
                $d['keterangan']
            ]);
        }
    }

    /**
     * Backfill pengajuan cuti yang belum memiliki step di cuti_pengajuan_step
     */
    private static function backfillUnsteppedPengajuan(): void
    {
        try {
            $unstepped = self::db()->findAll("
                SELECT cp.id, cp.pegawai_id, cp.status, cp.approved_by, cp.approved_by_name, cp.approved_at, cp.catatan_approval
                FROM `cuti_pengajuan` cp
                LEFT JOIN `cuti_pengajuan_step` s ON s.pengajuan_id = cp.id
                WHERE s.id IS NULL
            ");
            foreach ($unstepped as $un) {
                self::generatePengajuanSteps((int)$un['id'], (int)$un['pegawai_id'], $un['status'], $un);
            }
        } catch (Throwable $e) {}
    }

    /**
     * Generate step approval untuk sebuah pengajuan cuti berdasarkan alur hierarki custom aktif
     */
    public static function generatePengajuanSteps(int $pengajuanId, int $pegawaiId, string $initialStatus = 'Pending', array $overrideInfo = []): void
    {
        self::init();

        // Cari data pegawai (unit tugas & jenis pegawai)
        $pegawai = self::db()->find("
            SELECT p.unit_tugas, mj.kategori as penugasan_kategori 
            FROM `pegawai` p
            LEFT JOIN `pegawai_penugasan` pp ON pp.pegawai_id = p.id AND pp.status = 'Aktif'
            LEFT JOIN `master_jabatan` mj ON mj.id = pp.jabatan_id
            WHERE p.id = ? LIMIT 1
        ", [$pegawaiId]);

        $unit = trim((string)($pegawai['unit_tugas'] ?? ''));
        $isSupport = (!empty($pegawai['penugasan_kategori']) && $pegawai['penugasan_kategori'] === 'support_system');
        $jenis = $isSupport ? 'Support System' : 'Guru';

        // Cari hierarki yang paling spesifik (Unit + Jenis Pegawai)
        $steps = [];
        if (!empty($unit)) {
            $steps = self::db()->findAll("
                SELECT * FROM `cuti_approval_hierarchy`
                WHERE is_active = 1 AND unit = ? AND jenis_pegawai = ?
                ORDER BY step_order ASC
            ", [$unit, $jenis]);

            if (empty($steps)) {
                $steps = self::db()->findAll("
                    SELECT * FROM `cuti_approval_hierarchy`
                    WHERE is_active = 1 AND unit = ? AND jenis_pegawai = 'Semua'
                    ORDER BY step_order ASC
                ", [$unit]);
            }
        }

        if (empty($steps)) {
            $steps = self::db()->findAll("
                SELECT * FROM `cuti_approval_hierarchy`
                WHERE is_active = 1 AND unit = 'Semua' AND jenis_pegawai = ?
                ORDER BY step_order ASC
            ", [$jenis]);
        }

        if (empty($steps)) {
            $steps = self::db()->findAll("
                SELECT * FROM `cuti_approval_hierarchy`
                WHERE is_active = 1 AND unit = 'Semua' AND jenis_pegawai = 'Semua'
                ORDER BY step_order ASC
            ");
        }

        // Fallback jika database hierarki belum ada rule sama sekali
        if (empty($steps)) {
            $steps = [
                ['step_order' => 1, 'step_nama' => 'Verifikasi Atasan / Kepala Unit', 'approver_role' => 'Kepala Sekolah / Atasan'],
                ['step_order' => 2, 'step_nama' => 'Persetujuan Yayasan / Pimpinan', 'approver_role' => 'Yayasan / Pimpinan']
            ];
        }

        $totalSteps = count($steps);
        foreach ($steps as $idx => $st) {
            $order = (int)($st['step_order'] ?? ($idx + 1));
            $nama = $st['step_nama'];
            $role = $st['approver_role'] ?? null;
            $appJabId = !empty($st['approver_jabatan_id']) ? (int)$st['approver_jabatan_id'] : null;
            $appPegId = !empty($st['approver_pegawai_id']) ? (int)$st['approver_pegawai_id'] : null;

            $stepStatus = 'Pending';
            $approvedById = null;
            $approvedByName = null;
            $approvedAt = null;
            $catatan = null;

            if ($initialStatus === 'Disetujui') {
                $stepStatus = 'Disetujui';
                $approvedById = $overrideInfo['approved_by'] ?? null;
                $approvedByName = $overrideInfo['approved_by_name'] ?? 'Penyetuju';
                $approvedAt = $overrideInfo['approved_at'] ?? date('Y-m-d H:i:s');
                $catatan = $overrideInfo['catatan_approval'] ?? 'Disetujui';
            } elseif ($initialStatus === 'Ditolak') {
                // Tandai step terakhir sebagai ditolak
                if ($idx === $totalSteps - 1 || $totalSteps === 1) {
                    $stepStatus = 'Ditolak';
                    $approvedById = $overrideInfo['approved_by'] ?? null;
                    $approvedByName = $overrideInfo['approved_by_name'] ?? 'Penyetuju';
                    $approvedAt = $overrideInfo['approved_at'] ?? date('Y-m-d H:i:s');
                    $catatan = $overrideInfo['catatan_approval'] ?? 'Ditolak';
                }
            }

            self::db()->query("
                INSERT INTO `cuti_pengajuan_step`
                (`pengajuan_id`, `step_order`, `step_nama`, `approver_role`, `approver_jabatan_id`, `approver_pegawai_id`, `status`, `approved_by_id`, `approved_by_name`, `approved_at`, `catatan`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$pengajuanId, $order, $nama, $role, $appJabId, $appPegId, $stepStatus, $approvedById, $approvedByName, $approvedAt, $catatan]);
        }
    }

    /**
     * Ambil seluruh riwayat tahapan approval untuk 1 pengajuan cuti
     */
    public static function getPengajuanSteps(int $pengajuanId): array
    {
        self::init();
        self::ensureApproverJabatanColumns();
        try {
            return self::db()->findAll("
                SELECT s.*, mj.nama as master_jabatan_nama, mj.kategori as master_jabatan_kategori
                FROM `cuti_pengajuan_step` s
                LEFT JOIN `master_jabatan` mj ON mj.id = s.approver_jabatan_id
                WHERE s.`pengajuan_id` = ?
                ORDER BY s.`step_order` ASC, s.`id` ASC
            ", [$pengajuanId]);
        } catch (Throwable $e) {
            return self::db()->findAll("
                SELECT s.*, NULL as master_jabatan_nama, NULL as master_jabatan_kategori
                FROM `cuti_pengajuan_step` s
                WHERE s.`pengajuan_id` = ?
                ORDER BY s.`step_order` ASC, s.`id` ASC
            ", [$pengajuanId]);
        }
    }

    /**
     * Ambil step pending aktif saat ini untuk pengajuan cuti
     */
    public static function getCurrentPendingStep(int $pengajuanId): ?array
    {
        self::init();
        self::ensureApproverJabatanColumns();
        try {
            return self::db()->find("
                SELECT s.*, mj.nama as master_jabatan_nama, mj.kategori as master_jabatan_kategori
                FROM `cuti_pengajuan_step` s
                LEFT JOIN `master_jabatan` mj ON mj.id = s.approver_jabatan_id
                WHERE s.`pengajuan_id` = ? AND s.`status` = 'Pending'
                ORDER BY s.`step_order` ASC LIMIT 1
            ", [$pengajuanId]) ?: null;
        } catch (Throwable $e) {
            return self::db()->find("
                SELECT s.*, NULL as master_jabatan_nama, NULL as master_jabatan_kategori
                FROM `cuti_pengajuan_step` s
                WHERE s.`pengajuan_id` = ? AND s.`status` = 'Pending'
                ORDER BY s.`step_order` ASC LIMIT 1
            ", [$pengajuanId]) ?: null;
        }
    }

    /**
     * Ambil seluruh data konfigurasi Hierarki Approval Custom
     */
    public static function getAllHierarki(array $filters = []): array
    {
        self::init();
        self::ensureApproverJabatanColumns();

        $where = ["1=1"];
        $params = [];

        if (!empty($filters['unit'])) {
            $where[] = "h.unit = ?";
            $params[] = $filters['unit'];
        }
        if (!empty($filters['jenis_pegawai'])) {
            $where[] = "h.jenis_pegawai = ?";
            $params[] = $filters['jenis_pegawai'];
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $where[] = "h.is_active = ?";
            $params[] = (int)$filters['is_active'];
        }

        $whereSql = implode(' AND ', $where);

        // Coba query lengkap dengan JOIN master_jabatan
        try {
            return self::db()->findAll("
                SELECT h.*, mj.nama as master_jabatan_nama, mj.kategori as master_jabatan_kategori
                FROM `cuti_approval_hierarchy` h
                LEFT JOIN `master_jabatan` mj ON mj.id = h.approver_jabatan_id
                WHERE {$whereSql}
                ORDER BY h.unit ASC, h.jenis_pegawai ASC, h.step_order ASC, h.id ASC
            ", $params);
        } catch (Throwable $e) {
            // Jika kolom approver_jabatan_id belum sempat terbuat, buat sekarang dan query ulang
            try {
                self::db()->getConnection()->exec("ALTER TABLE `cuti_approval_hierarchy` ADD COLUMN `approver_jabatan_id` INT NULL AFTER `approver_role`");
                return self::db()->findAll("
                    SELECT h.*, mj.nama as master_jabatan_nama, mj.kategori as master_jabatan_kategori
                    FROM `cuti_approval_hierarchy` h
                    LEFT JOIN `master_jabatan` mj ON mj.id = h.approver_jabatan_id
                    WHERE {$whereSql}
                    ORDER BY h.unit ASC, h.jenis_pegawai ASC, h.step_order ASC, h.id ASC
                ", $params);
            } catch (Throwable $e2) {
                // Fallback query tanpa relasi jika gagal alter
                return self::db()->findAll("
                    SELECT h.*, NULL as master_jabatan_nama, NULL as master_jabatan_kategori
                    FROM `cuti_approval_hierarchy` h
                    WHERE {$whereSql}
                    ORDER BY h.unit ASC, h.jenis_pegawai ASC, h.step_order ASC, h.id ASC
                ", $params);
            }
        }
    }

    /**
     * Simpan / Update 1 Step Hierarki Approval
     */
    public static function saveHierarkiStep(array $data): int
    {
        self::init();
        $id = (int)($data['id'] ?? 0);
        $unit = trim((string)($data['unit'] ?? 'Semua')) ?: 'Semua';
        $jenisPegawai = trim((string)($data['jenis_pegawai'] ?? 'Semua')) ?: 'Semua';
        $stepOrder = max(1, (int)($data['step_order'] ?? 1));
        $stepNama = trim((string)($data['step_nama'] ?? ''));
        $approverRole = trim((string)($data['approver_role'] ?? '')) ?: null;
        $approverJabatanId = !empty($data['approver_jabatan_id']) ? (int)$data['approver_jabatan_id'] : null;
        $approverPegawaiId = !empty($data['approver_pegawai_id']) ? (int)$data['approver_pegawai_id'] : null;
        $keterangan = trim((string)($data['keterangan'] ?? '')) ?: null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if ($approverJabatanId && empty($approverRole)) {
            $jab = self::db()->find("SELECT nama FROM `master_jabatan` WHERE `id` = ?", [$approverJabatanId]);
            if ($jab) {
                $approverRole = $jab['nama'];
            }
        }

        if (empty($stepNama)) {
            throw new Exception('Nama tahapan approval wajib diisi.');
        }

        if ($id > 0) {
            self::db()->query("
                UPDATE `cuti_approval_hierarchy`
                SET `unit` = ?, `jenis_pegawai` = ?, `step_order` = ?, `step_nama` = ?,
                    `approver_role` = ?, `approver_jabatan_id` = ?, `approver_pegawai_id` = ?, `keterangan` = ?, `is_active` = ?
                WHERE `id` = ?
            ", [$unit, $jenisPegawai, $stepOrder, $stepNama, $approverRole, $approverJabatanId, $approverPegawaiId, $keterangan, $isActive, $id]);
            return $id;
        } else {
            self::db()->query("
                INSERT INTO `cuti_approval_hierarchy`
                (`unit`, `jenis_pegawai`, `step_order`, `step_nama`, `approver_role`, `approver_jabatan_id`, `approver_pegawai_id`, `keterangan`, `is_active`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$unit, $jenisPegawai, $stepOrder, $stepNama, $approverRole, $approverJabatanId, $approverPegawaiId, $keterangan, $isActive]);
            return (int)self::db()->getConnection()->lastInsertId();
        }
    }

    /**
     * Hapus 1 Step Hierarki Approval
     */
    public static function deleteHierarkiStep(int $id): bool
    {
        self::init();
        return (bool)self::db()->query("DELETE FROM `cuti_approval_hierarchy` WHERE `id` = ?", [$id]);
    }

    /**
     * Reset Hierarki Approval ke Template Default
     */
    public static function resetHierarkiDefault(): void
    {
        self::init();
        self::db()->query("DELETE FROM `cuti_approval_hierarchy`");
        self::seedDefaultHierarki();
    }

    /**
     * Parse input tanggal pilihan cuti (bisa JSON array, array PHP, atau string koma)
     * Mengembalikan array tanggal valid yang terurut kronologis unik: ['2026-11-20', '2026-11-24']
     */
    public static function parseTanggalPilihan($raw): array
    {
        if (empty($raw)) {
            return [];
        }
        if (is_string($raw)) {
            $trimmed = trim($raw);
            if ((str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) || (str_starts_with($trimmed, '\"[') && str_ends_with($trimmed, ']\"'))) {
                $decoded = json_decode(stripslashes($trimmed), true);
                if (is_array($decoded)) {
                    $raw = $decoded;
                } else {
                    $raw = explode(',', $trimmed);
                }
            } else {
                $raw = explode(',', $trimmed);
            }
        }
        if (!is_array($raw)) {
            return [];
        }

        $dates = [];
        foreach ($raw as $d) {
            $d = trim((string)$d);
            // Bersihkan kutip ganda/spasi
            $d = trim($d, " '\"\t\n\r\0\x0B");
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                $dates[] = $d;
            }
        }
        $dates = array_unique($dates);
        sort($dates);
        return array_values($dates);
    }

    /**
     * Format tampilan tanggal cuti untuk tabel riwayat, surat cuti, dan mobile card
     * Menampilkan per tanggal (bila ada jeda hari libur) atau format ringkas "dd/mm/yyyy s.d. dd/mm/yyyy" bila berurutan
     */
    public static function formatTanggalCutiDisplay(array $row, string $dateFormat = 'd/m/Y'): string
    {
        $list = self::parseTanggalPilihan($row['tanggal_cuti_list'] ?? null);
        if (!empty($list)) {
            if (count($list) === 1) {
                return date($dateFormat, strtotime($list[0]));
            }
            // Cek apakah tanggal berurutan tanpa jeda
            $isContinuous = true;
            for ($i = 0; $i < count($list) - 1; $i++) {
                $nextDay = date('Y-m-d', strtotime($list[$i] . ' +1 day'));
                if ($nextDay !== $list[$i + 1]) {
                    $isContinuous = false;
                    break;
                }
            }
            if ($isContinuous) {
                return date($dateFormat, strtotime($list[0])) . ' s.d. ' . date($dateFormat, strtotime(end($list)));
            }

            // Jika ada jeda libur: tampilkan tanggal-tanggal pilihan
            $formatted = array_map(function($d) use ($dateFormat) {
                return date($dateFormat, strtotime($d));
            }, $list);
            return implode(', ', $formatted);
        }

        $m = !empty($row['tanggal_mulai']) ? date($dateFormat, strtotime($row['tanggal_mulai'])) : '-';
        $s = !empty($row['tanggal_selesai']) ? date($dateFormat, strtotime($row['tanggal_selesai'])) : '-';
        if ($m === $s || empty($row['tanggal_selesai'])) {
            return $m;
        }
        return $m . ' s.d. ' . $s;
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

        $pParams = [];
        $uCond = Auth::unitSqlCondition('unit_tugas', $pParams);
        $totalPegawai = (int)(self::db()->find("SELECT COUNT(*) as c FROM `pegawai` WHERE `is_active` = 1 AND ({$uCond})", $pParams)['c'] ?? 0);

        $cpParams = [];
        $cpUCond = Auth::unitSqlCondition('p.unit_tugas', $cpParams);
        $totalPending = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_pengajuan` cp JOIN `pegawai` p ON cp.pegawai_id = p.id WHERE cp.`status` = 'Pending' AND ({$cpUCond})", $cpParams)['c'] ?? 0);
        $totalApproved = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_pengajuan` cp JOIN `pegawai` p ON cp.pegawai_id = p.id WHERE cp.`status` = 'Disetujui' AND ({$cpUCond})", $cpParams)['c'] ?? 0);
        $totalRejected = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_pengajuan` cp JOIN `pegawai` p ON cp.pegawai_id = p.id WHERE cp.`status` = 'Ditolak' AND ({$cpUCond})", $cpParams)['c'] ?? 0);

        $scParams = array_merge(['%"' . $today . '"%', $today], $cpParams);
        $sedangCutiHariIni = (int)(self::db()->find("
            SELECT COUNT(DISTINCT cp.pegawai_id) as c
            FROM `cuti_pengajuan` cp
            JOIN `pegawai` p ON cp.pegawai_id = p.id
            WHERE cp.`status` = 'Disetujui'
              AND (
                  (cp.`tanggal_cuti_list` IS NOT NULL AND cp.`tanggal_cuti_list` LIKE ?)
                  OR (cp.`tanggal_cuti_list` IS NULL AND ? BETWEEN cp.`tanggal_mulai` AND cp.`tanggal_selesai`)
              )
              AND ({$cpUCond})
        ", $scParams)['c'] ?? 0);
        $totalJenisKhusus = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_jenis` WHERE `kategori` = 'Khusus' AND `is_active` = 1")['c'] ?? 0);
        $totalLogPengurangan = (int)(self::db()->find("SELECT COUNT(*) as c FROM `cuti_log` l JOIN `pegawai` p ON l.pegawai_id = p.id WHERE l.`perubahan` < 0 AND ({$cpUCond})", $cpParams)['c'] ?? 0);

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
        $params = ['%"' . $today . '"%', $today];

        $uCond = Auth::unitSqlCondition('p.unit_tugas', $params);
        if ($uCond !== '1=1') {
            $where[] = "({$uCond})";
        }

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
                      AND (
                          (cp2.tanggal_cuti_list IS NOT NULL AND cp2.tanggal_cuti_list LIKE ?)
                          OR (cp2.tanggal_cuti_list IS NULL AND ? BETWEEN cp2.tanggal_mulai AND cp2.tanggal_selesai)
                      )
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
        $alasan = trim($data['alasan'] ?? '');

        // Proses daftar tanggal pilihan (multi-date selection / melewati hari libur)
        $selectedDates = self::parseTanggalPilihan($data['tanggal_pilihan'] ?? $data['tanggal_cuti_list'] ?? null);
        if (!empty($selectedDates)) {
            $hitungHari = count($selectedDates);
            $tglMulai = $selectedDates[0];
            $tglSelesai = end($selectedDates);
            $tanggalCutiList = json_encode($selectedDates);
        } else {
            $tglMulai = trim($data['tanggal_mulai'] ?? '');
            $tglSelesai = trim($data['tanggal_selesai'] ?? $tglMulai);
            if ($tglMulai === '') {
                throw new Exception('Tanggal permohonan cuti wajib dipilih.');
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
            // Generate list tanggal kontinu
            $datesArr = [];
            $cur = clone $d1;
            while ($cur <= $d2) {
                $datesArr[] = $cur->format('Y-m-d');
                $cur->modify('+1 day');
            }
            $tanggalCutiList = json_encode($datesArr);
        }

        if ($pegawaiId <= 0 || $jenisCutiId <= 0 || empty($tglMulai) || empty($tglSelesai) || $alasan === '') {
            throw new Exception('Pegawai, Jenis Cuti, Tanggal Cuti, dan Alasan wajib diisi lengkap.');
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
                `tanggal_mulai`, `tanggal_selesai`, `tanggal_cuti_list`, `jumlah_hari`, `alasan`,
                `pelimpahan_tugas`, `keterangan_tugas`, `alamat_selama_cuti`, `kontak_darurat`, `file_lampiran`, `file_lampiran_2`,
                `status`, `created_by`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
        ", [
            $nomor,
            $pegawaiId,
            $jenisCutiId,
            $jenis['kategori'],
            $tglMulai,
            $tglSelesai,
            $tanggalCutiList,
            $hitungHari,
            $alasan,
            trim($data['pelimpahan_tugas'] ?? '') ?: null,
            trim($data['keterangan_tugas'] ?? '') ?: null,
            trim($data['alamat_selama_cuti'] ?? '') ?: null,
            trim($data['kontak_darurat'] ?? '') ?: null,
            $data['file_lampiran'] ?? null,
            $data['file_lampiran_2'] ?? null,
            $userId
        ]);

        $newId = (int)self::db()->getConnection()->lastInsertId();

        // Bangkitkan tahapan approval berdasarkan alur hierarki custom aktif
        self::generatePengajuanSteps($newId, $pegawaiId);

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
     * Proses Persetujuan / Penolakan Berdasarkan Step Hierarki Tertentu
     */
    public static function approvePengajuanStep(
        int $pengajuanId,
        int $stepOrder,
        string $keputusan,
        string $catatan = '',
        ?int $approverId = null,
        string $approverName = 'Admin',
        bool $approveAll = false
    ): void {
        self::init();

        $pengajuan = self::db()->find("
            SELECT cp.*, j.nama_jenis, p.nama as nama_pegawai
            FROM `cuti_pengajuan` cp
            JOIN `cuti_jenis` j ON j.id = cp.jenis_cuti_id
            JOIN `pegawai` p ON p.id = cp.pegawai_id
            WHERE cp.id = ?
        ", [$pengajuanId]);

        if (!$pengajuan) {
            throw new Exception('Data pengajuan cuti tidak ditemukan.');
        }

        if ($approveAll) {
            // Setujui seluruh tahapan sekaligus (Bypass Langsung)
            self::db()->query("
                UPDATE `cuti_pengajuan_step`
                SET `status` = 'Disetujui',
                    `approved_by_id` = ?,
                    `approved_by_name` = ?,
                    `approved_at` = NOW(),
                    `catatan` = ?
                WHERE `pengajuan_id` = ? AND `status` = 'Pending'
            ", [$approverId, $approverName, $catatan ?: 'Disetujui Langsung Seluruh Tahapan (Bypass)', $pengajuanId]);

            self::approvePengajuan($pengajuanId, 'Disetujui', $catatan ?: 'Disetujui Langsung Seluruh Tahapan', $approverId, $approverName);
            return;
        }

        if ($keputusan === 'Ditolak') {
            // Tandai step ini Ditolak
            self::db()->query("
                UPDATE `cuti_pengajuan_step`
                SET `status` = 'Ditolak',
                    `approved_by_id` = ?,
                    `approved_by_name` = ?,
                    `approved_at` = NOW(),
                    `catatan` = ?
                WHERE `pengajuan_id` = ? AND `step_order` = ?
            ", [$approverId, $approverName, $catatan ?: 'Ditolak', $pengajuanId, $stepOrder]);

            // Status utama pengajuan menjadi Ditolak
            self::approvePengajuan($pengajuanId, 'Ditolak', $catatan ?: "Ditolak pada tahap {$stepOrder} oleh {$approverName}", $approverId, $approverName);
            return;
        }

        if ($keputusan === 'Disetujui') {
            // Ambil info nama step
            $currentStepRow = self::db()->find("
                SELECT * FROM `cuti_pengajuan_step`
                WHERE `pengajuan_id` = ? AND `step_order` = ?
            ", [$pengajuanId, $stepOrder]);
            $stepNama = $currentStepRow['step_nama'] ?? ('Tahap ' . $stepOrder);

            // Setujui step ini
            self::db()->query("
                UPDATE `cuti_pengajuan_step`
                SET `status` = 'Disetujui',
                    `approved_by_id` = ?,
                    `approved_by_name` = ?,
                    `approved_at` = NOW(),
                    `catatan` = ?
                WHERE `pengajuan_id` = ? AND `step_order` = ?
            ", [$approverId, $approverName, $catatan ?: 'Disetujui', $pengajuanId, $stepOrder]);

            // Cek apakah masih ada step Pending berikutnya
            $remaining = (int)(self::db()->find("
                SELECT COUNT(*) as c 
                FROM `cuti_pengajuan_step` 
                WHERE `pengajuan_id` = ? AND `status` = 'Pending'
            ", [$pengajuanId])['c'] ?? 0);

            if ($remaining > 0) {
                // Masih ada tahapan selanjutnya
                $nextStep = self::db()->find("
                    SELECT * FROM `cuti_pengajuan_step` 
                    WHERE `pengajuan_id` = ? AND `status` = 'Pending'
                    ORDER BY `step_order` ASC LIMIT 1
                ", [$pengajuanId]);
                $nextNama = $nextStep ? $nextStep['step_nama'] : 'berikutnya';

                self::db()->query("
                    UPDATE `cuti_pengajuan`
                    SET `catatan_approval` = ?
                    WHERE `id` = ?
                ", ["Tahap {$stepOrder} ({$stepNama}) disetujui oleh {$approverName}. Menunggu: {$nextNama}.", $pengajuanId]);
            } else {
                // Semua tahapan dalam hierarki telah selesai disetujui!
                self::approvePengajuan(
                    $pengajuanId,
                    'Disetujui',
                    $catatan ?: "Seluruh tahapan persetujuan selesai (Disetujui terakhir oleh {$approverName})",
                    $approverId,
                    $approverName
                );
            }
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

        $uCondList = Auth::unitSqlCondition('p.unit_tugas', $params);
        if ($uCondList !== '1=1') {
            $where[] = "({$uCondList})";
        }

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

        $rows = self::db()->findAll("
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

        if (empty($rows)) {
            return [];
        }

        // Lampirkan informasi tahapan hierarki approval untuk setiap pengajuan secara optimal
        $ids = array_column($rows, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $allSteps = self::db()->findAll("
            SELECT * FROM `cuti_pengajuan_step`
            WHERE `pengajuan_id` IN ($placeholders)
            ORDER BY `pengajuan_id` ASC, `step_order` ASC
        ", $ids);

        $stepsByPengajuan = [];
        foreach ($allSteps as $st) {
            $stepsByPengajuan[(int)$st['pengajuan_id']][] = $st;
        }

        foreach ($rows as &$r) {
            $pId = (int)$r['id'];
            $r['steps'] = $stepsByPengajuan[$pId] ?? [];
            $r['total_steps'] = count($r['steps']);
            $r['completed_steps'] = 0;
            $r['current_step'] = null;

            foreach ($r['steps'] as $sItem) {
                if ($sItem['status'] === 'Disetujui') {
                    $r['completed_steps']++;
                } elseif ($sItem['status'] === 'Pending' && $r['current_step'] === null) {
                    $r['current_step'] = $sItem;
                }
            }
        }
        unset($r);

        return $rows;
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

