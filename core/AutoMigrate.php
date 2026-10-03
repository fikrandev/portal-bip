<?php
/**
 * AutoMigrate - Automatic Database Migration & Schema Synchronizer
 * Portal BIP
 *
 * Runs automatically on application bootstrap so production servers
 * stay 100% up-to-date with any schema changes without requiring manual CLI migrations.
 */

class AutoMigrate
{
    private static ?PDO $pdo = null;
    private static bool $hasRun = false;

    /**
     * Get or initialize PDO connection
     */
    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = Database::getInstance()->getConnection();
        }
        return self::$pdo;
    }

    /**
     * Set external PDO connection (useful for CLI/testing)
     */
    public static function setPdo(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    /**
     * List of all migration tasks in chronological order
     */
    private static function getMigrations(): array
    {
        return [
            '2026_01_01_000001_create_migrations_table' => 'migration001CreateMigrationsTable',
            '2026_01_01_000002_sync_sarpras_referensi' => 'migration002SarprasReferensi',
            '2026_01_01_000003_sync_sarpras_core' => 'migration003SarprasCore',
            '2026_01_01_000004_sync_sarpras_tanah_bangunan' => 'migration004SarprasTanahBangunan',
            '2026_01_01_000005_sync_sarpras_distribusi_maintenance' => 'migration005SarprasDistribusiMaintenance',
            '2026_01_01_000006_alter_sarpras_barang_foto_multi' => 'migration006SarprasBarangFotoMulti',
            '2026_01_01_000007_alter_sarpras_distribusi_kondisi' => 'migration007SarprasDistribusiKondisi',
            '2026_01_01_000008_alter_sarpras_ruangan_extensions' => 'migration008SarprasRuanganExtensions',
            '2026_01_01_000009_sync_sarpras_peminjaman_columns' => 'migration009SarprasPeminjamanColumns',
            '2026_01_01_000010_sync_mobile_portal_tables' => 'migration010MobilePortalTables',
            '2026_01_01_000011_sync_nilai_and_quran_tables' => 'migration011NilaiAndQuranTables',
            '2026_01_01_000012_sync_jadwal_and_perangkat_tables' => 'migration012JadwalAndPerangkatTables',
            '2026_01_01_000013_verify_full_database_integrity' => 'migration013FullDatabaseIntegrity',
            '2026_01_01_000014_wipe_sarpras_dummy_data' => 'migration014WipeSarprasDummyData',
            '2026_01_01_000015_sync_all_sarpras_columns' => 'migration015SyncAllSarprasColumns',
            '2026_10_03_000016_fix_sarpras_barang_missing_cols' => 'migration016FixSarprasBarangMissingCols',
            '2026_10_03_000017_ensure_all_sarpras_schema_integrity' => 'migration016FixSarprasBarangMissingCols',
            '2026_10_03_000018_wipe_all_dummy_sarpras_keep_kategori' => 'migration018WipeAllDummySarprasKeepKategori',
        ];
    }

    /**
     * Main execution entry point.
     * Checks for pending migrations and runs them idempotently.
     */
    public static function run(bool $forceVerbose = false): array
    {
        if (self::$hasRun && !$forceVerbose) {
            return ['status' => 'already_run_in_lifecycle'];
        }
        self::$hasRun = true;

        $results = [
            'applied' => [],
            'skipped' => [],
            'errors'  => []
        ];

        try {
            $pdo = self::getPdo();

            // 1. Ensure migrations table exists
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `migrations` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `migration` VARCHAR(191) NOT NULL UNIQUE,
                    `batch` INT NOT NULL,
                    `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 2. Fetch applied migrations
            $applied = $pdo->query("SELECT migration FROM `migrations`")->fetchAll(PDO::FETCH_COLUMN);
            $appliedMap = array_flip($applied ?: []);

            $allMigrations = self::getMigrations();

            // Quick check: if all migrations are applied, exit immediately (< 1ms overhead)
            $pending = [];
            foreach ($allMigrations as $key => $method) {
                if (!isset($appliedMap[$key])) {
                    $pending[$key] = $method;
                } else {
                    $results['skipped'][] = $key;
                }
            }

            if (empty($pending)) {
                return $results;
            }

            // 3. Determine next batch number
            $batchNum = (int)$pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM `migrations`")->fetchColumn();

            // 4. Run pending migrations
            foreach ($pending as $key => $method) {
                try {
                    if (method_exists(self::class, $method)) {
                        self::$method($pdo);
                        $stmt = $pdo->prepare("INSERT INTO `migrations` (`migration`, `batch`) VALUES (?, ?)");
                        $stmt->execute([$key, $batchNum]);
                        $results['applied'][] = $key;
                    }
                } catch (Throwable $e) {
                    $results['errors'][$key] = $e->getMessage();
                    error_log("[AutoMigrate Error] Migration {$key} failed: " . $e->getMessage());
                }
            }

        } catch (Throwable $e) {
            $results['errors']['system'] = $e->getMessage();
            error_log("[AutoMigrate System Error]: " . $e->getMessage());
        }

        return $results;
    }

    // =========================================================================
    // HELPER METHODS (SAFE, IDEMPOTENT DDL)
    // =========================================================================

    public static function hasTable(PDO $pdo, string $table): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
            $stmt->execute([$table]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            try {
                $clean = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                return (bool)$pdo->query("SHOW TABLES LIKE '{$clean}'")->fetchColumn();
            } catch (Throwable $e2) {
                return false;
            }
        }
    }

    public static function hasColumn(PDO $pdo, string $table, string $column): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
            $stmt->execute([$table, $column]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (Throwable $e) {
            try {
                $cleanTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
                $cleanCol = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
                return (bool)$pdo->query("SHOW COLUMNS FROM `{$cleanTable}` LIKE '{$cleanCol}'")->fetch();
            } catch (Throwable $e2) {
                return false;
            }
        }
    }

    public static function addColumnIfNotExists(PDO $pdo, string $table, string $column, string $definition, ?string $after = null): bool
    {
        if (!self::hasTable($pdo, $table)) {
            return false;
        }
        if (!self::hasColumn($pdo, $table, $column)) {
            if ($after && self::hasColumn($pdo, $table, $after)) {
                try {
                    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition} AFTER `{$after}`");
                    return true;
                } catch (Throwable $e) {}
            }
            try {
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
                return true;
            } catch (Throwable $e) {
                return false;
            }
        }
        return false;
    }

    public static function modifyColumn(PDO $pdo, string $table, string $column, string $definition): bool
    {
        if (!self::hasTable($pdo, $table) || !self::hasColumn($pdo, $table, $column)) {
            return false;
        }
        try {
            $pdo->exec("ALTER TABLE `{$table}` MODIFY COLUMN `{$column}` {$definition}");
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function addIndexIfNotExists(PDO $pdo, string $table, string $indexName, string $columns, bool $isUnique = false): bool
    {
        if (!self::hasTable($pdo, $table)) {
            return false;
        }
        try {
            $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = ?");
            $stmt->execute([$indexName]);
            if (!$stmt->fetchColumn()) {
                $type = $isUnique ? "UNIQUE INDEX" : "INDEX";
                $pdo->exec("ALTER TABLE `{$table}` ADD {$type} `{$indexName}` ({$columns})");
                return true;
            }
        } catch (Throwable $e) {
            return false;
        }
        return false;
    }

    // =========================================================================
    // MIGRATION IMPLEMENTATIONS
    // =========================================================================

    /**
     * 001. Ensure migrations table exists
     */
    private static function migration001CreateMigrationsTable(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(191) NOT NULL UNIQUE,
                `batch` INT NOT NULL,
                `applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * 002. Sarpras Referensi: Golongan, Kelompok, Asal Anggaran, Satuan
     */
    private static function migration002SarprasReferensi(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_golongan` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `kode` varchar(20) NOT NULL,
              `nama_golongan` varchar(150) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `kode` (`kode`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_kode_kelompok` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `golongan_id` int(11) NOT NULL,
              `kode` varchar(30) NOT NULL,
              `nama_kelompok` varchar(150) NOT NULL,
              `keterangan` text DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `golongan_id` (`golongan_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_asal_anggaran` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `kode` varchar(20) NOT NULL,
              `nama` varchar(150) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `kode` (`kode`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_satuan` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `nama_satuan` varchar(50) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Seed default satuan if empty
        $count = (int)$pdo->query("SELECT COUNT(*) FROM `sarpras_satuan`")->fetchColumn();
        if ($count === 0) {
            $pdo->exec("INSERT INTO `sarpras_satuan` (`nama_satuan`) VALUES ('Unit'), ('Buah'), ('Set'), ('Meter'), ('Kodi'), ('Lusin')");
        }
    }

    /**
     * 003. Sarpras Core: Kategori, Ruangan, Barang, Peminjaman, Pemeliharaan
     */
    private static function migration003SarprasCore(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_kategori` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `kode_kategori` VARCHAR(20) NOT NULL UNIQUE,
                `nama_kategori` VARCHAR(100) NOT NULL,
                `deskripsi` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_ruangan` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `kode_ruangan` VARCHAR(30) NOT NULL UNIQUE,
                `nama_ruangan` VARCHAR(150) NOT NULL,
                `unit` VARCHAR(50) NOT NULL DEFAULT 'Semua',
                `lokasi_gedung` VARCHAR(100) DEFAULT 'Gedung Utama',
                `penanggung_jawab` VARCHAR(150) DEFAULT NULL,
                `kapasitas` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (`unit`),
                INDEX (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_barang` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `kode_barang` VARCHAR(50) NOT NULL UNIQUE,
                `nama_barang` VARCHAR(200) NOT NULL,
                `golongan_id` INT NULL,
                `kelompok_id` INT NULL,
                `asal_anggaran_id` INT NULL,
                `kategori_id` INT NOT NULL,
                `ruangan_id` INT NOT NULL,
                `unit` VARCHAR(50) NOT NULL DEFAULT 'SD',
                `merk_model` VARCHAR(150) DEFAULT NULL,
                `nomor_seri` VARCHAR(100) DEFAULT NULL,
                `jumlah` INT NOT NULL DEFAULT 1,
                `dipakai` INT DEFAULT 0,
                `satuan` VARCHAR(30) NOT NULL DEFAULT 'Unit',
                `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
                `status` ENUM('Tersedia', 'Dipinjam', 'Dalam Perbaikan', 'Dihapuskan') NOT NULL DEFAULT 'Tersedia',
                `sumber_dana` VARCHAR(100) DEFAULT NULL,
                `tanggal_perolehan` DATE DEFAULT NULL,
                `tahun_pengadaan` VARCHAR(4) DEFAULT NULL,
                `harga_perolehan` DECIMAL(15, 2) DEFAULT 0.00,
                `masa_manfaat` INT DEFAULT 0,
                `foto` TEXT DEFAULT NULL,
                `keterangan` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`kategori_id`),
                INDEX (`ruangan_id`),
                INDEX (`unit`),
                INDEX (`status`),
                INDEX (`kondisi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_peminjaman` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `kode_pinjam` VARCHAR(50) NOT NULL UNIQUE,
                `barang_id` BIGINT UNSIGNED NOT NULL,
                `nama_peminjam` VARCHAR(150) NOT NULL,
                `role_peminjam` ENUM('Guru', 'Pegawai', 'Siswa', 'Lainnya') NOT NULL DEFAULT 'Guru',
                `kontak_peminjam` VARCHAR(30) DEFAULT NULL,
                `jumlah_pinjam` INT NOT NULL DEFAULT 1,
                `tanggal_pinjam` DATE NOT NULL,
                `estimasi_kembali` DATE NOT NULL,
                `tanggal_kembali` DATE DEFAULT NULL,
                `keperluan` TEXT NOT NULL,
                `kondisi_sebelum` VARCHAR(50) DEFAULT 'Baik',
                `kondisi_sesudah` VARCHAR(50) DEFAULT NULL,
                `status` ENUM('Dipinjam', 'Kembali', 'Terlambat') NOT NULL DEFAULT 'Dipinjam',
                `petugas_nama` VARCHAR(100) DEFAULT NULL,
                `catatan` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`barang_id`),
                INDEX (`status`),
                INDEX (`tanggal_pinjam`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_pemeliharaan` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `kode_perbaikan` VARCHAR(50) NOT NULL UNIQUE,
                `barang_id` BIGINT UNSIGNED DEFAULT NULL,
                `ruangan_id` INT DEFAULT NULL,
                `judul_laporan` VARCHAR(200) NOT NULL,
                `pelapor_nama` VARCHAR(150) NOT NULL,
                `tanggal_lapor` DATE NOT NULL,
                `tingkat_urgensi` ENUM('Rendah', 'Sedang', 'Tinggi', 'Darurat') NOT NULL DEFAULT 'Sedang',
                `deskripsi_kerusakan` TEXT NOT NULL,
                `status` ENUM('Menunggu', 'Diproses', 'Selesai', 'Afkir') NOT NULL DEFAULT 'Menunggu',
                `estimasi_biaya` DECIMAL(15, 2) DEFAULT 0.00,
                `biaya_realisasi` DECIMAL(15, 2) DEFAULT 0.00,
                `tanggal_selesai` DATE DEFAULT NULL,
                `teknisi_pihak` VARCHAR(150) DEFAULT NULL,
                `tindakan_perbaikan` TEXT DEFAULT NULL,
                `foto_kerusakan` VARCHAR(255) DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`barang_id`),
                INDEX (`ruangan_id`),
                INDEX (`status`),
                INDEX (`tingkat_urgensi`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * 004. Sarpras Tanah & Bangunan
     */
    private static function migration004SarprasTanahBangunan(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_tanah` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `kode_tanah` VARCHAR(50) NOT NULL,
                `nama_tanah` VARCHAR(200) NOT NULL,
                `no_sertifikat` VARCHAR(150) NOT NULL,
                `status_kepemilikan` VARCHAR(50) NOT NULL DEFAULT 'Milik Yayasan',
                `panjang` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `lebar` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `luas` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `tahun_perolehan` VARCHAR(4) NULL,
                `harga_perolehan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `alamat_lokasi` TEXT NULL,
                `gambar_sertifikat` TEXT NULL,
                `keterangan` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_kode_tanah` (`kode_tanah`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_bangunan` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tanah_id` BIGINT UNSIGNED NOT NULL,
                `kode_bangunan` VARCHAR(50) NOT NULL,
                `nama_bangunan` VARCHAR(200) NOT NULL,
                `jumlah_lantai` INT NOT NULL DEFAULT 1,
                `panjang` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `lebar` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `luas_bangunan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                `tahun_dibangun` VARCHAR(4) NULL,
                `masa_manfaat` INT NOT NULL DEFAULT 20,
                `kondisi_bangunan` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
                `sumber_dana` VARCHAR(100) NULL,
                `biaya_pembangunan` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
                `foto_bangunan` VARCHAR(255) NULL,
                `keterangan` TEXT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_kode_bangunan` (`kode_bangunan`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * 005. Sarpras Distribusi, Maintenance, Pengajuan
     */
    private static function migration005SarprasDistribusiMaintenance(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_distribusi` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `ruangan_id` int(11) NOT NULL,
                `barang_id` bigint(20) unsigned NOT NULL,
                `jumlah` int(11) NOT NULL DEFAULT 1,
                `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
                `tanggal_distribusi` date NULL,
                `keterangan` varchar(255) DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_maintenance` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `barang_id` BIGINT UNSIGNED NOT NULL,
                `tanggal_lapor` DATE NOT NULL,
                `dilaporkan_oleh` VARCHAR(100) NULL,
                `deskripsi_kerusakan` TEXT NOT NULL,
                `status` ENUM('Menunggu', 'Dalam Perbaikan', 'Selesai') NOT NULL DEFAULT 'Menunggu',
                `tanggal_selesai` DATE NULL,
                `biaya` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `keterangan` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_pengajuan` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `nama_barang` VARCHAR(255) NOT NULL,
                `spesifikasi` TEXT NULL,
                `jumlah` INT NOT NULL DEFAULT 1,
                `estimasi_harga` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `keperluan` TEXT NOT NULL,
                `pengaju` VARCHAR(100) NOT NULL,
                `tanggal_pengajuan` DATE NOT NULL,
                `status` ENUM('Draft', 'Diajukan', 'Disetujui', 'Ditolak', 'Selesai') NOT NULL DEFAULT 'Diajukan',
                `keterangan_admin` TEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * 006. Alter sarpras_barang.foto to TEXT for multi-photo support
     */
    private static function migration006SarprasBarangFotoMulti(PDO $pdo): void
    {
        self::modifyColumn($pdo, 'sarpras_barang', 'foto', 'TEXT NULL');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'golongan_id', 'INT NULL', 'nama_barang');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'kelompok_id', 'INT NULL', 'golongan_id');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'asal_anggaran_id', 'INT NULL', 'kelompok_id');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'dipakai', 'INT DEFAULT 0', 'jumlah');
    }

    /**
     * 007. Alter sarpras_distribusi.kondisi
     */
    private static function migration007SarprasDistribusiKondisi(PDO $pdo): void
    {
        self::addColumnIfNotExists($pdo, 'sarpras_distribusi', 'kondisi', "ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik'", 'jumlah');
        self::addColumnIfNotExists($pdo, 'sarpras_distribusi', 'tanggal_distribusi', "DATE NULL", 'kondisi');
    }

    /**
     * 008. Alter sarpras_ruangan extensions: bangunan_id, lantai, jenis_ruangan, panjang, lebar, luas
     */
    private static function migration008SarprasRuanganExtensions(PDO $pdo): void
    {
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'bangunan_id', 'BIGINT UNSIGNED NULL', 'kode_ruangan');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'lantai', 'INT NOT NULL DEFAULT 1', 'bangunan_id');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'jenis_ruangan', "VARCHAR(100) NOT NULL DEFAULT 'Ruang Kelas'", 'nama_ruangan');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'panjang', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00', 'kapasitas');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'lebar', 'DECIMAL(10,2) NOT NULL DEFAULT 0.00', 'panjang');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'luas', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00', 'lebar');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'keterangan', 'TEXT NULL', 'luas');
    }

    /**
     * 009. Ensure sarpras_peminjaman compatibility columns exist
     */
    private static function migration009SarprasPeminjamanColumns(PDO $pdo): void
    {
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'jumlah', 'INT NOT NULL DEFAULT 1', 'tanggal_kembali');
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'keterangan', 'TEXT NULL', 'catatan');
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'peminjam', 'VARCHAR(150) NULL', 'nama_peminjam');
    }

    /**
     * 010. Mobile Portal Tables (Absensi, Jurnal, Presensi, Mutabaah, dll)
     */
    private static function migration010MobilePortalTables(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `absensi_pegawai` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pegawai_id` int(11) NOT NULL,
                `tanggal` date NOT NULL,
                `jam_masuk` time DEFAULT NULL,
                `jam_pulang` time DEFAULT NULL,
                `status` enum('Hadir','Terlambat','Izin','Sakit','Alpa') NOT NULL DEFAULT 'Hadir',
                `foto_masuk` varchar(255) DEFAULT NULL,
                `foto_pulang` varchar(255) DEFAULT NULL,
                `lokasi_masuk` varchar(255) DEFAULT NULL,
                `lokasi_pulang` varchar(255) DEFAULT NULL,
                `keterangan` text DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_pegawai_tgl` (`pegawai_id`,`tanggal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `jurnal_mengajar` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pegawai_id` int(11) NOT NULL,
                `kelas_id` int(11) NOT NULL,
                `mata_pelajaran_id` int(11) DEFAULT NULL,
                `tanggal` date NOT NULL,
                `jam_ke` varchar(20) NOT NULL,
                `materi` text NOT NULL,
                `kegiatan` text DEFAULT NULL,
                `hambatan` text DEFAULT NULL,
                `solusi` text DEFAULT NULL,
                `foto_kegiatan` varchar(255) DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `presensi_kelas` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `kelas_id` int(11) NOT NULL,
                `guru_id` int(11) NOT NULL,
                `tanggal` date NOT NULL,
                `mata_pelajaran_id` int(11) DEFAULT NULL,
                `jam_ke` varchar(20) DEFAULT NULL,
                `keterangan` text DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `presensi_kelas_detail` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `presensi_kelas_id` int(11) NOT NULL,
                `siswa_id` int(11) NOT NULL,
                `status` enum('Hadir','Sakit','Izin','Alpa') NOT NULL DEFAULT 'Hadir',
                `catatan` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_presensi_kelas` (`presensi_kelas_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `mutabaah_ibadah_guru` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pegawai_id` int(11) NOT NULL,
                `tanggal` date NOT NULL,
                `sholat_subuh` tinyint(1) DEFAULT 0,
                `sholat_dzuhur` tinyint(1) DEFAULT 0,
                `sholat_ashar` tinyint(1) DEFAULT 0,
                `sholat_maghrib` tinyint(1) DEFAULT 0,
                `sholat_isya` tinyint(1) DEFAULT 0,
                `sholat_tahajud` tinyint(1) DEFAULT 0,
                `sholat_dhuha` tinyint(1) DEFAULT 0,
                `tilawah_quran` varchar(100) DEFAULT NULL,
                `dzikir_pagi_petang` tinyint(1) DEFAULT 0,
                `puasa_sunnah` tinyint(1) DEFAULT 0,
                `sedekah` tinyint(1) DEFAULT 0,
                `catatan` text DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_pegawai_mutabaah_tgl` (`pegawai_id`,`tanggal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `keterlambatan_siswa` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `siswa_id` int(11) NOT NULL,
                `tanggal` date NOT NULL,
                `jam_datang` time NOT NULL,
                `alasan` text DEFAULT NULL,
                `tindakan` text DEFAULT NULL,
                `pencatat_id` int(11) NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_siswa_tgl` (`siswa_id`,`tanggal`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `izin_guru` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pegawai_id` int(11) NOT NULL,
                `jenis_izin` enum('Tidak Masuk','Keluar Mengajar','Dinas Luar') NOT NULL,
                `tanggal_mulai` date NOT NULL,
                `tanggal_selesai` date NOT NULL,
                `jam_keluar` time DEFAULT NULL,
                `jam_kembali` time DEFAULT NULL,
                `alasan` text NOT NULL,
                `bukti_file` varchar(255) DEFAULT NULL,
                `status` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
                `catatan_persetujuan` text DEFAULT NULL,
                `disetujui_oleh` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `cuti_guru` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pegawai_id` int(11) NOT NULL,
                `jenis_cuti` varchar(50) NOT NULL,
                `tanggal_mulai` date NOT NULL,
                `tanggal_selesai` date NOT NULL,
                `jumlah_hari` int(11) NOT NULL DEFAULT 1,
                `alasan` text NOT NULL,
                `alamat_selama_cuti` text DEFAULT NULL,
                `kontak_darurat` varchar(30) DEFAULT NULL,
                `bukti_file` varchar(255) DEFAULT NULL,
                `status` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
                `catatan_persetujuan` text DEFAULT NULL,
                `disetujui_oleh` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `kuota_cuti_guru` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pegawai_id` int(11) NOT NULL,
                `tahun` int(4) NOT NULL,
                `kuota_tahunan` int(11) NOT NULL DEFAULT 12,
                `terpakai` int(11) NOT NULL DEFAULT 0,
                `sisa` int(11) NOT NULL DEFAULT 12,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_pegawai_tahun` (`pegawai_id`,`tahun`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `quran_bookmark_guru` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pegawai_id` int(11) NOT NULL,
                `surah_id` int(11) NOT NULL,
                `ayat_id` int(11) NOT NULL,
                `nama_surah` varchar(100) NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_pegawai_bookmark` (`pegawai_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * 011. Nilai & Quran Tables
     */
    private static function migration011NilaiAndQuranTables(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `nilai_group` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `guru_id` int(11) NOT NULL,
                `kelas_id` int(11) NOT NULL,
                `mata_pelajaran_id` int(11) NOT NULL,
                `tahun_akademik_id` int(11) NOT NULL,
                `semester` enum('Ganjil','Genap') NOT NULL DEFAULT 'Ganjil',
                `jenis_penilaian` varchar(50) NOT NULL DEFAULT 'Sumatif',
                `judul_penilaian` varchar(150) NOT NULL,
                `kkm` decimal(5,2) DEFAULT 75.00,
                `bobot` int(11) DEFAULT 1,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `nilai_detail` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `nilai_group_id` int(11) NOT NULL,
                `siswa_id` int(11) NOT NULL,
                `nilai` decimal(5,2) NOT NULL DEFAULT 0.00,
                `capaian_kompetensi` text DEFAULT NULL,
                `catatan` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_nilai_group` (`nilai_group_id`),
                KEY `idx_siswa` (`siswa_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `quran_siswa_target` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `siswa_id` int(11) NOT NULL,
                `tahun_akademik_id` int(11) NOT NULL,
                `target_juz` varchar(50) NOT NULL,
                `keterangan` varchar(255) DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `quran_siswa_setoran` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `siswa_id` int(11) NOT NULL,
                `guru_id` int(11) NOT NULL,
                `tanggal` date NOT NULL,
                `jenis_setoran` enum('Ziyadah','Murojaah','Tahsin') NOT NULL DEFAULT 'Ziyadah',
                `surah_mulai` varchar(100) NOT NULL,
                `ayat_mulai` int(11) NOT NULL,
                `surah_selesai` varchar(100) NOT NULL,
                `ayat_selesai` int(11) NOT NULL,
                `nilai_kelancaran` enum('A','B','C','D') NOT NULL DEFAULT 'A',
                `nilai_tajwid` enum('A','B','C','D') NOT NULL DEFAULT 'A',
                `catatan` text DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * 012. Jadwal & Perangkat Pembelajaran Tables
     */
    private static function migration012JadwalAndPerangkatTables(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `jadwal_grup` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `nama_grup` varchar(100) NOT NULL,
                `unit` varchar(50) NOT NULL DEFAULT 'SD',
                `tahun_akademik_id` int(11) NOT NULL,
                `semester` enum('Ganjil','Genap') NOT NULL DEFAULT 'Ganjil',
                `is_active` tinyint(1) NOT NULL DEFAULT 1,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `jadwal_pelajaran` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `jadwal_grup_id` int(11) NOT NULL,
                `kelas_id` int(11) NOT NULL,
                `guru_id` int(11) NOT NULL,
                `mata_pelajaran_id` int(11) NOT NULL,
                `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad') NOT NULL,
                `jam_mulai` time NOT NULL,
                `jam_selesai` time NOT NULL,
                `ruangan_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_jadwal_grup` (`jadwal_grup_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `perangkat_pembelajaran` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `guru_id` int(11) NOT NULL,
                `mata_pelajaran_id` int(11) NOT NULL,
                `kelas_id` int(11) DEFAULT NULL,
                `jenis_perangkat` varchar(50) NOT NULL,
                `judul` varchar(200) NOT NULL,
                `deskripsi` text DEFAULT NULL,
                `file_path` varchar(255) NOT NULL,
                `status_approval` enum('Draft','Menunggu Review','Disetujui','Revisi') NOT NULL DEFAULT 'Draft',
                `catatan_reviewer` text DEFAULT NULL,
                `reviewed_by` int(11) DEFAULT NULL,
                `reviewed_at` datetime DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * 013. Full Database Integrity Verification
     * Double checks and heals all critical columns across the application
     */
    private static function migration013FullDatabaseIntegrity(PDO $pdo): void
    {
        // 1. Sarpras Barang
        self::modifyColumn($pdo, 'sarpras_barang', 'foto', 'TEXT NULL');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'dipakai', 'INT DEFAULT 0', 'jumlah');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'masa_manfaat', 'INT DEFAULT 5', 'harga_perolehan');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'tahun_pengadaan', 'VARCHAR(4) NULL', 'tanggal_perolehan');

        // 2. Sarpras Distribusi
        self::addColumnIfNotExists($pdo, 'sarpras_distribusi', 'kondisi', "ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik'", 'jumlah');
        self::addColumnIfNotExists($pdo, 'sarpras_distribusi', 'tanggal_distribusi', 'DATE NULL', 'kondisi');

        // 3. Sarpras Ruangan
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'bangunan_id', 'BIGINT UNSIGNED NULL', 'kode_ruangan');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'lantai', 'INT NOT NULL DEFAULT 1', 'bangunan_id');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'jenis_ruangan', "VARCHAR(100) NOT NULL DEFAULT 'Ruang Kelas'", 'nama_ruangan');

        // 4. Sarpras Peminjaman
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'jumlah', 'INT NOT NULL DEFAULT 1', 'tanggal_kembali');
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'keterangan', 'TEXT NULL', 'catatan');
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'peminjam', 'VARCHAR(150) NULL', 'nama_peminjam');

        // 5. Pegawai
        self::addColumnIfNotExists($pdo, 'pegawai', 'nik', 'VARCHAR(20) NULL', 'nama');
        self::addColumnIfNotExists($pdo, 'pegawai', 'no_wa', 'VARCHAR(20) NULL', 'alamat');
    }

    /**
     * Migration 14: Kosongkan seluruh data dummy/bawaan Sarpras agar sistem bersih & fresh
     */
    private static function migration014WipeSarprasDummyData(PDO $pdo): void
    {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $tablesToWipe = [
            'sarpras_peminjaman',
            'sarpras_pemeliharaan',
            'sarpras_maintenance',
            'sarpras_distribusi',
            'sarpras_pengajuan',
            'sarpras_barang',
            'sarpras_ruangan',
            'sarpras_bangunan',
            'sarpras_tanah'
        ];

        foreach ($tablesToWipe as $tbl) {
            try {
                $check = $pdo->query("SHOW TABLES LIKE '$tbl'")->fetch();
                if ($check) {
                    $pdo->exec("TRUNCATE TABLE `$tbl`;");
                }
            } catch (Throwable $e) {
                try {
                    $pdo->exec("DELETE FROM `$tbl`;");
                    $pdo->exec("ALTER TABLE `$tbl` AUTO_INCREMENT = 1;");
                } catch (Throwable $ex) {}
            }
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * Migration 15: Sinkronisasi komprehensif seluruh kolom Sarpras
     * Memastikan tanggal_perolehan, golongan_id, kelompok_id, dll selalu tersedia di server
     */
    private static function migration015SyncAllSarprasColumns(PDO $pdo): void
    {
        // 1. sarpras_barang
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'golongan_id', 'INT NULL', 'nama_barang');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'kelompok_id', 'INT NULL', 'golongan_id');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'asal_anggaran_id', 'INT NULL', 'kelompok_id');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'dipakai', 'INT DEFAULT 0', 'jumlah');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'sumber_dana', 'VARCHAR(100) NULL', 'status');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'tanggal_perolehan', 'DATE NULL', 'sumber_dana');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'tahun_pengadaan', 'VARCHAR(4) NULL', 'tanggal_perolehan');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'harga_perolehan', 'DECIMAL(15,2) DEFAULT 0.00', 'tahun_pengadaan');
        self::addColumnIfNotExists($pdo, 'sarpras_barang', 'masa_manfaat', 'INT DEFAULT 5', 'harga_perolehan');
        self::modifyColumn($pdo, 'sarpras_barang', 'foto', 'TEXT NULL');

        // 2. sarpras_distribusi
        self::addColumnIfNotExists($pdo, 'sarpras_distribusi', 'kondisi', "ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik'", 'jumlah');
        self::addColumnIfNotExists($pdo, 'sarpras_distribusi', 'tanggal_distribusi', 'DATE NULL', 'kondisi');

        // 3. sarpras_ruangan
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'bangunan_id', 'BIGINT UNSIGNED NULL', 'kode_ruangan');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'lantai', 'INT NOT NULL DEFAULT 1', 'bangunan_id');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'jenis_ruangan', "VARCHAR(100) NOT NULL DEFAULT 'Ruang Kelas'", 'nama_ruangan');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'panjang', 'DECIMAL(10,2) DEFAULT 0.00', 'kapasitas');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'lebar', 'DECIMAL(10,2) DEFAULT 0.00', 'panjang');
        self::addColumnIfNotExists($pdo, 'sarpras_ruangan', 'luas', 'DECIMAL(10,2) DEFAULT 0.00', 'lebar');

        // 4. sarpras_bangunan
        self::addColumnIfNotExists($pdo, 'sarpras_bangunan', 'tanah_id', 'BIGINT UNSIGNED NULL', 'id');

        // 5. sarpras_peminjaman
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'jumlah', 'INT NOT NULL DEFAULT 1', 'tanggal_kembali');
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'keterangan', 'TEXT NULL', 'catatan');
        self::addColumnIfNotExists($pdo, 'sarpras_peminjaman', 'peminjam', 'VARCHAR(150) NULL', 'nama_peminjam');
    }

    /**
     * Migration 16: Re-verify critical sarpras_barang columns
     * migration015 may have been recorded as applied on the server but the columns
     * failed to be added (e.g. AFTER clause referenced a column that also didn't exist yet).
     * This migration adds each column individually without AFTER to guarantee they exist.
     */
    private static function migration016FixSarprasBarangMissingCols(PDO $pdo): void
    {
        // sarpras_barang - add missing columns one by one (no AFTER to avoid dependency issues)
        $barangCols = [
            'tanggal_perolehan' => 'DATE NULL',
            'tahun_pengadaan'   => 'VARCHAR(4) NULL',
            'golongan_id'       => 'INT NULL',
            'kelompok_id'       => 'INT NULL',
            'asal_anggaran_id'  => 'INT NULL',
            'dipakai'           => 'INT DEFAULT 0',
            'masa_manfaat'      => 'INT DEFAULT 5',
            'harga_perolehan'   => 'DECIMAL(15,2) DEFAULT 0.00',
            'sumber_dana'       => 'VARCHAR(100) NULL',
        ];
        foreach ($barangCols as $col => $def) {
            if (!self::hasColumn($pdo, 'sarpras_barang', $col)) {
                try {
                    $pdo->exec("ALTER TABLE `sarpras_barang` ADD COLUMN `{$col}` {$def}");
                } catch (Throwable $e) {}
            }
        }

        // sarpras_distribusi
        if (!self::hasColumn($pdo, 'sarpras_distribusi', 'kondisi')) {
            try { $pdo->exec("ALTER TABLE `sarpras_distribusi` ADD COLUMN `kondisi` ENUM('Baik','Rusak Ringan','Rusak Berat') NOT NULL DEFAULT 'Baik'"); } catch (Throwable $e) {}
        }
        if (!self::hasColumn($pdo, 'sarpras_distribusi', 'tanggal_distribusi')) {
            try { $pdo->exec("ALTER TABLE `sarpras_distribusi` ADD COLUMN `tanggal_distribusi` DATE NULL"); } catch (Throwable $e) {}
        }

        // sarpras_ruangan
        $ruanganCols = [
            'bangunan_id'   => 'BIGINT UNSIGNED NULL',
            'lantai'        => 'INT NOT NULL DEFAULT 1',
            'jenis_ruangan' => "VARCHAR(100) NOT NULL DEFAULT 'Ruang Kelas'",
            'panjang'       => 'DECIMAL(10,2) DEFAULT 0.00',
            'lebar'         => 'DECIMAL(10,2) DEFAULT 0.00',
            'luas'          => 'DECIMAL(10,2) DEFAULT 0.00',
        ];
        foreach ($ruanganCols as $col => $def) {
            if (!self::hasColumn($pdo, 'sarpras_ruangan', $col)) {
                try { $pdo->exec("ALTER TABLE `sarpras_ruangan` ADD COLUMN `{$col}` {$def}"); } catch (Throwable $e) {}
            }
        }

        // sarpras_bangunan
        if (!self::hasColumn($pdo, 'sarpras_bangunan', 'tanah_id')) {
            try { $pdo->exec("ALTER TABLE `sarpras_bangunan` ADD COLUMN `tanah_id` BIGINT UNSIGNED NULL"); } catch (Throwable $e) {}
        }

        // sarpras_pemeliharaan
        if (self::hasTable($pdo, 'sarpras_pemeliharaan')) {
            if (!self::hasColumn($pdo, 'sarpras_pemeliharaan', 'judul_laporan')) {
                try { $pdo->exec("ALTER TABLE `sarpras_pemeliharaan` ADD COLUMN `judul_laporan` VARCHAR(200) NOT NULL DEFAULT 'Laporan Kerusakan'"); } catch (Throwable $e) {}
            }
            if (!self::hasColumn($pdo, 'sarpras_pemeliharaan', 'ruangan_id')) {
                try { $pdo->exec("ALTER TABLE `sarpras_pemeliharaan` ADD COLUMN `ruangan_id` INT DEFAULT NULL"); } catch (Throwable $e) {}
            }
            if (!self::hasColumn($pdo, 'sarpras_pemeliharaan', 'foto_kerusakan')) {
                try { $pdo->exec("ALTER TABLE `sarpras_pemeliharaan` ADD COLUMN `foto_kerusakan` VARCHAR(255) DEFAULT NULL"); } catch (Throwable $e) {}
            }
        }
    }

    /**
     * Migration 18: Reset dan bersihkan seluruh data dummy operasional Sarpras
     * Menjamin data tanah, bangunan, ruangan, barang, distribusi, peminjaman, pemeliharaan 0 baris (bersih)
     * Tetap mempertahankan sarpras_kategori dan tabel referensi (satuan, golongan, kelompok, asal anggaran).
     */
    private static function migration018WipeAllDummySarprasKeepKategori(PDO $pdo): void
    {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $tablesToWipe = [
            'sarpras_peminjaman',
            'sarpras_pemeliharaan',
            'sarpras_maintenance',
            'sarpras_distribusi',
            'sarpras_pengajuan',
            'sarpras_barang',
            'sarpras_ruangan',
            'sarpras_bangunan',
            'sarpras_tanah'
        ];

        foreach ($tablesToWipe as $tbl) {
            try {
                if (self::hasTable($pdo, $tbl)) {
                    try {
                        $pdo->exec("TRUNCATE TABLE `{$tbl}`;");
                    } catch (Throwable $e) {
                        $pdo->exec("DELETE FROM `{$tbl}`;");
                        $pdo->exec("ALTER TABLE `{$tbl}` AUTO_INCREMENT = 1;");
                    }
                }
            } catch (Throwable $e) {}
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Bersihkan foto barang dummy yang mungkin tersisa di public/uploads/sarpras
        if (defined('BASE_PATH')) {
            $uploadDir = BASE_PATH . '/public/uploads/sarpras';
            if (is_dir($uploadDir)) {
                $files = glob($uploadDir . '/sarpras_*.*');
                if (is_array($files)) {
                    foreach ($files as $f) {
                        if (is_file($f)) {
                            @unlink($f);
                        }
                    }
                }
            }
        }
    }
}

// Standalone CLI execution support
if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', dirname(__DIR__));
    }
    require_once BASE_PATH . '/config/database.php';
    require_once BASE_PATH . '/core/Database.php';

    echo "=== Menjalankan AutoMigrate (CLI) ===\n";
    $res = AutoMigrate::run(true);
    echo "Selesai!\n";
    echo "Diterapkan: " . count($res['applied']) . "\n";
    echo "Dilewati (Sudah ada): " . count($res['skipped']) . "\n";
    if (!empty($res['errors'])) {
        echo "Error:\n" . print_r($res['errors'], true) . "\n";
    }
}
