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
            '2026_10_03_000017_ensure_all_sarpras_schema_integrity' => 'migration016FixSarprasBarangMissingCols',
            '2026_10_03_000018_wipe_all_dummy_sarpras_keep_kategori' => 'migration018WipeAllDummySarprasKeepKategori',
            '2026_10_03_000019_alter_sarpras_tanah_nullable_cols' => 'migration019AlterSarprasTanahNullableCols',
            '2026_10_08_000020_create_sarpras_remember_tokens' => 'migration020CreateSarprasRememberTokens',
            '2026_10_08_000021_create_spmb_tables' => 'migration021CreateSpmbTables',
            '2026_10_12_000022_create_wa_gateway_tables' => 'migration022CreateWaGatewayTables',
            '2026_10_12_000023_sync_spmb_biodata_lengkap_columns' => 'migration023SyncSpmbBiodataLengkapColumns',
            '2026_10_13_000024_add_spmb_group_tanggal_patokan_umur' => 'migration024AddSpmbGroupTanggalPatokanUmur',
            '2026_10_13_000025_add_kategori_pendaftar_column' => 'migration025AddKategoriPendaftarColumn',
            '2026_10_13_000026_add_spmb_group_biaya_pindahan' => 'migration026AddSpmbGroupBiayaPindahan',
            '2026_10_13_000027_add_spmb_surat_komitmen_features' => 'migration027AddSpmbSuratKomitmenFeatures',
            '2026_10_13_000028_add_pengembalian_berkas_features' => 'migration028AddPengembalianBerkasFeatures',
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
                `no_sertifikat` VARCHAR(150) NULL DEFAULT NULL,
                `status_kepemilikan` VARCHAR(50) NOT NULL DEFAULT 'SHM',
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

    /**
     * 019. Alter sarpras_tanah: jadikan no_sertifikat nullable dan default safe
     */
    private static function migration019AlterSarprasTanahNullableCols(PDO $pdo): void
    {
        if (self::hasTable($pdo, 'sarpras_tanah')) {
            try {
                $pdo->exec("ALTER TABLE `sarpras_tanah` MODIFY COLUMN `no_sertifikat` VARCHAR(150) NULL DEFAULT NULL;");
            } catch (Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE `sarpras_tanah` MODIFY COLUMN `status_kepemilikan` VARCHAR(50) NOT NULL DEFAULT 'SHM';");
            } catch (Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE `sarpras_tanah` MODIFY COLUMN `alamat_lokasi` TEXT NULL DEFAULT NULL;");
            } catch (Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE `sarpras_tanah` MODIFY COLUMN `gambar_sertifikat` TEXT NULL DEFAULT NULL;");
            } catch (Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE `sarpras_tanah` MODIFY COLUMN `keterangan` TEXT NULL DEFAULT NULL;");
            } catch (Throwable $e) {}
            try {
                $pdo->exec("ALTER TABLE `sarpras_tanah` MODIFY COLUMN `tahun_perolehan` VARCHAR(4) NULL DEFAULT NULL;");
            } catch (Throwable $e) {}
        }
    }

    /**
     * 020. Sarpras Mobile Remember Tokens (Sesi Selamanya / Sekali Login)
     */
    private static function migration020CreateSarprasRememberTokens(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sarpras_remember_tokens` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `token_hash` VARCHAR(64) NOT NULL,
                `device_name` VARCHAR(150) DEFAULT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `expires_at` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_used_at` DATETIME DEFAULT NULL,
                INDEX `idx_srt_user` (`user_id`),
                INDEX `idx_srt_hash` (`token_hash`),
                INDEX `idx_srt_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * 021. Kelola SPMB Tables (spmb_group, spmb_pendaftar, spmb_pengembalian)
     */
    private static function migration021CreateSpmbTables(PDO $pdo): void
    {
        // 1. Group / Tahun SPMB
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `spmb_group` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nama_group` VARCHAR(100) NOT NULL,
                `tahun_akademik_id` INT NULL,
                `tahun_ajaran` VARCHAR(50) NOT NULL,
                `gelombang_default` VARCHAR(50) DEFAULT 'Gelombang 1',
                `biaya_formulir` DECIMAL(12,2) DEFAULT 0,
                `biaya_pindahan` DECIMAL(12,2) DEFAULT 0,
                `kuota_paud` INT DEFAULT 50,
                `kuota_sd` INT DEFAULT 100,
                `kuota_smp` INT DEFAULT 100,
                `kuota_sma` INT DEFAULT 100,
                `tanggal_buka` DATE NULL,
                `tanggal_tutup` DATE NULL,
                `tanggal_patokan_umur` DATE NULL DEFAULT '2027-07-01',
                `is_active` TINYINT(1) DEFAULT 0,
                `keterangan` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_spmb_group_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Data Calon Siswa SPMB (Siswa Baru & Pindahan)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `spmb_pendaftar` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `group_id` INT NOT NULL,
                `jenis_pendaftaran` ENUM('Siswa Baru', 'Pindahan', 'Daftar Tunggu') NOT NULL DEFAULT 'Siswa Baru',
                `nomor_pendaftaran` VARCHAR(50) NOT NULL UNIQUE,
                `tanggal_daftar` DATE NOT NULL,
                `gelombang` VARCHAR(50) DEFAULT 'Gelombang 1',
                `jenjang` ENUM('PAUD', 'SD', 'SMP', 'SMA') NOT NULL,
                `jalur_pendaftaran` VARCHAR(50) DEFAULT 'Reguler',
                `nisn` VARCHAR(20) NULL,
                `nik` VARCHAR(20) NULL,
                `nama_lengkap` VARCHAR(150) NOT NULL,
                `nama_panggilan` VARCHAR(50) NULL,
                `jenis_kelamin` ENUM('L', 'P') NOT NULL,
                `tempat_lahir` VARCHAR(100) NULL,
                `tanggal_lahir` DATE NULL,
                `agama` VARCHAR(50) DEFAULT 'Islam',
                `anak_ke` INT NULL,
                `jumlah_saudara` INT NULL,
                `alamat` TEXT NULL,
                `rt` VARCHAR(10) NULL,
                `rw` VARCHAR(10) NULL,
                `kelurahan` VARCHAR(100) NULL,
                `kecamatan` VARCHAR(100) NULL,
                `kota` VARCHAR(100) DEFAULT 'Palu',
                `provinsi` VARCHAR(100) DEFAULT 'Sulawesi Tengah',
                `kode_pos` VARCHAR(10) NULL,
                `no_hp` VARCHAR(30) NULL,
                `email` VARCHAR(100) NULL,
                `foto` VARCHAR(255) NULL,
                `asal_sekolah` VARCHAR(150) NULL,
                `npsn_asal` VARCHAR(20) NULL,
                `kelas_tujuan` VARCHAR(50) NULL,
                `alasan_pindah` TEXT NULL,
                `nomor_surat_pindah` VARCHAR(100) NULL,
                `nama_ayah` VARCHAR(150) NULL,
                `pekerjaan_ayah` VARCHAR(100) NULL,
                `penghasilan_ayah` VARCHAR(50) NULL,
                `no_hp_ayah` VARCHAR(30) NULL,
                `nama_ibu` VARCHAR(150) NULL,
                `pekerjaan_ibu` VARCHAR(100) NULL,
                `penghasilan_ibu` VARCHAR(50) NULL,
                `no_hp_ibu` VARCHAR(30) NULL,
                `nama_wali` VARCHAR(150) NULL,
                `pekerjaan_wali` VARCHAR(100) NULL,
                `no_hp_wali` VARCHAR(30) NULL,
                `biaya_pendaftaran` DECIMAL(12,2) DEFAULT 0,
                `status_pembayaran` ENUM('Belum Lunas', 'Lunas', 'Gratis') DEFAULT 'Belum Lunas',
                `bukti_bayar` VARCHAR(255) NULL,
                `status_berkas` ENUM('Belum Lengkap', 'Lengkap', 'Perlu Perbaikan') DEFAULT 'Belum Lengkap',
                `status_kelulusan` ENUM('Menunggu Seleksi', 'Lolos Berkas', 'Lulus / Diterima', 'Cadangan', 'Tidak Lulus', 'Dibatalkan') DEFAULT 'Menunggu Seleksi',
                `catatan` TEXT NULL,
                `created_by` BIGINT UNSIGNED NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_spmb_pendaftar_group` (`group_id`),
                INDEX `idx_spmb_pendaftar_jenis` (`jenis_pendaftaran`),
                INDEX `idx_spmb_pendaftar_jenjang` (`jenjang`),
                INDEX `idx_spmb_pendaftar_status` (`status_kelulusan`),
                INDEX `idx_spmb_pendaftar_tgl` (`tanggal_daftar`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Pengembalian Formulir / Berkas / Refund (Siswa Baru & Pindahan)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `spmb_pengembalian` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `pendaftar_id` INT NOT NULL,
                `nomor_pengembalian` VARCHAR(50) NOT NULL UNIQUE,
                `tanggal_pengembalian` DATE NOT NULL,
                `jenis_pengembalian` ENUM('Pengembalian Formulir', 'Pengembalian Berkas', 'Pengembalian Dana / Refund', 'Pembatalan Pendaftaran') DEFAULT 'Pengembalian Formulir',
                `alasan` TEXT NOT NULL,
                `nominal_refund` DECIMAL(12,2) DEFAULT 0,
                `status` ENUM('Diajukan', 'Diproses', 'Selesai', 'Ditolak') DEFAULT 'Selesai',
                `penerima_berkas` VARCHAR(150) NULL,
                `petugas_penerima` VARCHAR(150) NULL,
                `dokumen_bukti` VARCHAR(255) NULL,
                `catatan` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_spmb_pengembalian_pendaftar` (`pendaftar_id`),
                INDEX `idx_spmb_pengembalian_tgl` (`tanggal_pengembalian`),
                INDEX `idx_spmb_pengembalian_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 4. Seed default group jika belum ada
        $countGroup = (int)$pdo->query("SELECT COUNT(*) FROM `spmb_group`")->fetchColumn();
        if ($countGroup === 0) {
            $pdo->exec("
                INSERT INTO `spmb_group` (
                    `nama_group`, `tahun_ajaran`, `gelombang_default`, `biaya_formulir`,
                    `kuota_paud`, `kuota_sd`, `kuota_smp`, `kuota_sma`,
                    `tanggal_buka`, `tanggal_tutup`, `is_active`, `keterangan`
                ) VALUES (
                    'SPMB 2026/2027', '2026/2027', 'Gelombang 1', 250000.00,
                    50, 100, 100, 100,
                    '2026-01-01', '2026-07-31', 1, 'Penerimaan Murid Baru Tahun Pelajaran 2026/2027 SIT Bina Insan Palu'
                );
            ");
        }
    }

    /**
     * Migration 022: Server WhatsApp Gateway & Logs
     */
    private static function migration022CreateWaGatewayTables(PDO $pdo): void
    {
        // 1. Tabel riwayat pengiriman pesan WhatsApp Gateway
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `wa_gateway_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `phone_number` VARCHAR(35) NOT NULL,
                `message` TEXT NOT NULL,
                `module` VARCHAR(60) NOT NULL DEFAULT 'system',
                `status` ENUM('success', 'failed', 'pending') NOT NULL DEFAULT 'pending',
                `response_data` TEXT NULL,
                `sent_by` INT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_wa_phone` (`phone_number`),
                INDEX `idx_wa_status` (`status`),
                INDEX `idx_wa_module` (`module`),
                INDEX `idx_wa_created` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 2. Default settings untuk WA Gateway
        $defaultWaSettings = [
            'wa_gateway_enabled' => '0',
            'wa_gateway_server_name' => 'WA Gateway eportal',
            'wa_gateway_provider' => 'fonnte',
            'wa_gateway_api_url' => 'https://api.fonnte.com/send',
            'wa_gateway_api_key' => '',
            'wa_gateway_sender_number' => '',
            'wa_gateway_footer_note' => 'Pesan otomatis dikirim melalui Portal BIP.'
        ];

        $stmtCheck = $pdo->prepare("SELECT 1 FROM `settings` WHERE `setting_key` = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");

        foreach ($defaultWaSettings as $key => $val) {
            $stmtCheck->execute([$key]);
            if (!$stmtCheck->fetch()) {
                $stmtInsert->execute([$key, $val]);
            }
        }
    }

    /**
     * Migration 023: Kolom Formulir Biodata Lengkap SPMB & Pengaturan Dropdown
     */
    private static function migration023SyncSpmbBiodataLengkapColumns(PDO $pdo): void
    {
        if (self::hasTable($pdo, 'spmb_pendaftar')) {
            // Data Calon Siswa
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'no_kk', 'VARCHAR(30) NULL', 'nik');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'no_akta_lahir', 'VARCHAR(60) NULL', 'umur');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'alergi', 'VARCHAR(150) NULL', 'jumlah_saudara');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'tinggi_badan', 'INT NULL', 'alergi');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'berat_badan', 'INT NULL', 'tinggi_badan');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'lingkar_kepala', 'INT NULL', 'berat_badan');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'hobi', 'VARCHAR(100) NULL', 'lingkar_kepala');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'cita_cita', 'VARCHAR(100) NULL', 'hobi');

            // Data Sekolah Asal
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'alamat_sekolah_asal', 'TEXT NULL', 'npsn_asal');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'file_screenshot_dapodik', 'VARCHAR(255) NULL', 'alamat_sekolah_asal');

            // Data Alamat & Tempat Tinggal
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'dusun', 'VARCHAR(100) NULL', 'rw');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'lintang', 'VARCHAR(50) NULL', 'kode_pos');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'bujur', 'VARCHAR(50) NULL', 'lintang');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'tempat_tinggal', 'VARCHAR(100) NULL', 'bujur');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'moda_transportasi', 'VARCHAR(100) NULL', 'tempat_tinggal');

            // Data Ibu
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'nik_ibu', 'VARCHAR(30) NULL', 'nama_ibu');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'tahun_lahir_ibu', 'VARCHAR(10) NULL', 'nik_ibu');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'pendidikan_ibu', 'VARCHAR(50) NULL', 'tahun_lahir_ibu');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'kantor_ibu', 'VARCHAR(150) NULL', 'penghasilan_ibu');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'jabatan_ibu', 'VARCHAR(100) NULL', 'kantor_ibu');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'kebutuhan_khusus_ibu', 'VARCHAR(100) NULL', 'jabatan_ibu');

            // Data Ayah
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'nik_ayah', 'VARCHAR(30) NULL', 'nama_ayah');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'tahun_lahir_ayah', 'VARCHAR(10) NULL', 'nik_ayah');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'pendidikan_ayah', 'VARCHAR(50) NULL', 'tahun_lahir_ayah');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'kantor_ayah', 'VARCHAR(150) NULL', 'penghasilan_ayah');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'jabatan_ayah', 'VARCHAR(100) NULL', 'kantor_ayah');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'kebutuhan_khusus_ayah', 'VARCHAR(100) NULL', 'jabatan_ayah');

            // Kontak & Berkas Pendukung (Upload Terenkripsi)
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'no_wa', 'VARCHAR(35) NULL', 'no_hp');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'berkas_kk', 'VARCHAR(255) NULL', 'bukti_bayar');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'berkas_akta', 'VARCHAR(255) NULL', 'berkas_kk');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'berkas_ktp_ayah', 'VARCHAR(255) NULL', 'berkas_akta');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'berkas_ktp_ibu', 'VARCHAR(255) NULL', 'berkas_ktp_ayah');

            // Token & Status Pengisian Biodata
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'biodata_token', 'VARCHAR(64) NULL', 'berkas_ktp_ibu');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'biodata_status', "ENUM('Belum Diisi', 'Draft', 'Lengkap') NOT NULL DEFAULT 'Belum Diisi'", 'biodata_token');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'biodata_step', 'INT NOT NULL DEFAULT 1', 'biodata_status');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'persetujuan_data', 'TINYINT(1) NOT NULL DEFAULT 0', 'biodata_step');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'biodata_submitted_at', 'DATETIME NULL', 'persetujuan_data');

            // Indeks
            self::addIndexIfNotExists($pdo, 'spmb_pendaftar', 'idx_spmb_biodata_token', 'biodata_token', true);
            self::addIndexIfNotExists($pdo, 'spmb_pendaftar', 'idx_spmb_biodata_status', 'biodata_status');

            // Generate token untuk pendaftar yang belum memiliki token
            $emptyTokens = $pdo->query("SELECT id FROM `spmb_pendaftar` WHERE `biodata_token` IS NULL OR `biodata_token` = ''")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($emptyTokens)) {
                $stmtTok = $pdo->prepare("UPDATE `spmb_pendaftar` SET `biodata_token` = ? WHERE `id` = ?");
                foreach ($emptyTokens as $pId) {
                    $token = bin2hex(random_bytes(24));
                    $stmtTok->execute([$token, $pId]);
                }
            }
        }

        // Pengaturan Opsi Master Dropdown SPMB
        $defaultSpmbOptions = [
            'spmb_opt_alergi' => "Tidak Ada\nAlergi Makanan / Seafood / Kacang\nAlergi Susu Sapi / Laktosa\nAlergi Debu / Dingin\nAlergi Obat Tertentu\nLainnya",
            'spmb_opt_tempat_tinggal' => "Bersama Orang Tua\nWali / Keluarga\nAsrama / Pondok\nKost\nLainnya",
            'spmb_opt_transportasi' => "Jalan Kaki\nSepeda Motor\nMobil Pribadi\nAntar Jemput Sekolah\nAngkutan Umum\nOjek Online\nLainnya",
            'spmb_opt_pendidikan' => "SD / Sederajat\nSMP / Sederajat\nSMA / SMK / Sederajat\nD1 / D2 / D3\nD4 / S1 (Sarjana)\nS2 (Magister)\nS3 (Doktor)\nTidak Sekolah",
            'spmb_opt_pekerjaan' => "PNS / ASN\nTNI / Polri\nPegawai BUMN\nKaryawan Swasta\nWiraswasta / Pengusaha\nPedagang\nPetani / Peternak\nNelayan\nDokter / Tenaga Kesehatan\nGuru / Dosen\nIbu Rumah Tangga (IRT)\nBuruh\nLainnya",
            'spmb_opt_penghasilan' => "Kurang dari Rp 1.000.000\nRp 1.000.000 - Rp 3.000.000\nRp 3.000.000 - Rp 5.000.000\nRp 5.000.000 - Rp 10.000.000\nLebih dari Rp 10.000.000\nTidak Berpenghasilan",
            'spmb_opt_kebutuhan_khusus' => "Tidak Ada\nTunanetra\nTunarungu\nTunawicara\nTunadaksa\nTunagrahita\nAutis\nHiperaktif (ADHD)\nLainnya",
            'spmb_template_dapodik' => '',
            'spmb_wa_notif_enabled' => '1',
            'spmb_wa_template_biodata' => "Assalamu'alaikum Wr. Wb. Bpk/Ibu Orang Tua/Wali dari ananda {nama_siswa}.\n\nTerima kasih telah melakukan pendaftaran di Portal SPMB SIT Bina Insan Palu (No. Registrasi: {nomor_pendaftaran}).\n\nUntuk kelengkapan administrasi dan sinkronisasi data Dapodik/EMIS, silakan melengkapi Formulir Biodata Lengkap melalui tautan resmi berikut:\n👉 {link_biodata}\n\nCatatan:\n- Anda dapat menyimpan sementara (Simpan Draft) dan melanjutkannya kapan saja.\n- Siapkan foto KK, Akta Kelahiran, dan KTP Orang Tua (maks. 500 KB).\n\nTerima kasih.\nPanitia SPMB Yayasan Bina Insan Palu"
        ];

        $stmtCheck = $pdo->prepare("SELECT 1 FROM `settings` WHERE `setting_key` = ?");
        $stmtInsert = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");

        foreach ($defaultSpmbOptions as $key => $val) {
            $stmtCheck->execute([$key]);
            if (!$stmtCheck->fetch()) {
                $stmtInsert->execute([$key, $val]);
            }
        }
    }

    /**
     * 024. Tambah tanggal_patokan_umur pada spmb_group untuk hitung umur pendaftar (default 2027-07-01)
     * Dapat diatur per group SPMB agar setiap tahun ajaran baru bisa disesuaikan.
     */
    private static function migration024AddSpmbGroupTanggalPatokanUmur(PDO $pdo): void
    {
        if (self::hasTable($pdo, 'spmb_group')) {
            self::addColumnIfNotExists($pdo, 'spmb_group', 'tanggal_patokan_umur', "DATE NULL DEFAULT '2027-07-01'", 'tanggal_tutup');

            // Pastikan group yang sudah ada terisi tanggal_patokan_umur = '2027-07-01'
            $pdo->exec("UPDATE `spmb_group` SET `tanggal_patokan_umur` = '2027-07-01' WHERE `tanggal_patokan_umur` IS NULL OR `tanggal_patokan_umur` = '0000-00-00'");

            // Sinkronkan / perbarui umur calon siswa yang sudah terdaftar berdasarkan patokan umur group-nya
            $pendaftarRows = $pdo->query("
                SELECT p.id, p.tanggal_lahir, g.tanggal_patokan_umur
                FROM `spmb_pendaftar` p
                LEFT JOIN `spmb_group` g ON g.id = p.group_id
                WHERE p.tanggal_lahir IS NOT NULL AND p.tanggal_lahir != ''
            ")->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($pendaftarRows)) {
                $stmtUpdateUmur = $pdo->prepare("UPDATE `spmb_pendaftar` SET `umur` = ? WHERE `id` = ?");
                foreach ($pendaftarRows as $row) {
                    try {
                        $tglLahir = new DateTime($row['tanggal_lahir']);
                        $patokan = new DateTime(!empty($row['tanggal_patokan_umur']) ? $row['tanggal_patokan_umur'] : '2027-07-01');
                        if ($tglLahir <= $patokan) {
                            $diff = $patokan->diff($tglLahir);
                            $umurStr = $diff->y . ' Tahun' . ($diff->m > 0 ? ' ' . $diff->m . ' Bulan' : '');
                            $stmtUpdateUmur->execute([$umurStr, $row['id']]);
                        }
                    } catch (Throwable $e) {}
                }
            }
        }
    }

    private static function migration025AddKategoriPendaftarColumn(PDO $pdo): void
    {
        if (self::hasTable($pdo, 'spmb_pendaftar')) {
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'kategori_pendaftar', "VARCHAR(80) NULL DEFAULT 'Umum (Luar Bina Insan)'", 'jalur_pendaftaran');

            // Defaultkan data pendaftar yang sudah ada
            $pdo->exec("
                UPDATE `spmb_pendaftar` 
                SET `kategori_pendaftar` = 'Umum (Luar Bina Insan)',
                    `jalur_pendaftaran` = 'Umum (Luar Bina Insan)'
                WHERE `kategori_pendaftar` IS NULL OR `kategori_pendaftar` = '' OR `jalur_pendaftaran` = 'Reguler'
            ");
        }
    }

    /**
     * 026. Tambah kolom biaya_pindahan pada spmb_group untuk biaya pendaftaran siswa pindahan custom per group
     */
    private static function migration026AddSpmbGroupBiayaPindahan(PDO $pdo): void
    {
        if (self::hasTable($pdo, 'spmb_group')) {
            self::addColumnIfNotExists($pdo, 'spmb_group', 'biaya_pindahan', 'DECIMAL(12,2) NOT NULL DEFAULT 0.00', 'biaya_formulir');

            // Defaultkan biaya_pindahan group jika masih 0, disamakan dengan biaya_formulir
            $pdo->exec("
                UPDATE `spmb_group` 
                SET `biaya_pindahan` = `biaya_formulir`
                WHERE `biaya_pindahan` IS NULL OR `biaya_pindahan` = 0.00
            ");
        }
    }

    /**
     * 027. Fitur Surat Pernyataan Komitmen Orang Tua & TTD Digital Terenkripsi
     */
    private static function migration027AddSpmbSuratKomitmenFeatures(PDO $pdo): void
    {
        // 1. Kolom tanda tangan & status komitmen pada spmb_pendaftar
        if (self::hasTable($pdo, 'spmb_pendaftar')) {
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'status_ttd_komitmen', "ENUM('Belum TTD','Sudah TTD') NOT NULL DEFAULT 'Belum TTD'", 'biodata_submitted_at');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'ttd_ayah', 'MEDIUMTEXT NULL', 'status_ttd_komitmen');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'ttd_ibu', 'MEDIUMTEXT NULL', 'ttd_ayah');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'ttd_nama_ayah', 'VARCHAR(150) NULL', 'ttd_ibu');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'ttd_nama_ibu', 'VARCHAR(150) NULL', 'ttd_nama_ayah');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'ttd_submitted_at', 'DATETIME NULL', 'ttd_nama_ibu');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'ttd_ip_address', 'VARCHAR(45) NULL', 'ttd_submitted_at');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'ttd_user_agent', 'TEXT NULL', 'ttd_ip_address');
            self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'ttd_catatan', 'TEXT NULL', 'ttd_user_agent');
        }

        // 2. Tabel Template Surat Pernyataan Komitmen per Unit (PAUD, SD, SMP, SMA)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `spmb_surat_template` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `jenjang` ENUM('PAUD', 'SD', 'SMP', 'SMA') NOT NULL UNIQUE,
                `judul_surat` VARCHAR(255) NOT NULL,
                `nama_sekolah` VARCHAR(255) NOT NULL,
                `kop_gambar` VARCHAR(255) NULL,
                `isi_template` LONGTEXT NOT NULL,
                `catatan_bawah` TEXT NULL,
                `created_at` DATETIME NULL,
                `updated_at` DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // 3. Seed template default untuk 4 unit jika belum ada
        $units = [
            'SD' => [
                'nama' => 'SD Islam Terpadu Bina Insan Palu',
                'jam_pulang' => "<li>Kelas 1–2: pukul 14.15 WITA.</li>\n      <li>Kelas 3–6: pukul 16.00 WITA.</li>",
            ],
            'SMP' => [
                'nama' => 'SMP Islam Terpadu Bina Insan Palu',
                'jam_pulang' => "<li>Kelas 7–9: pukul 16.00 WITA.</li>",
            ],
            'SMA' => [
                'nama' => 'SMA Islam Terpadu Bina Insan Palu',
                'jam_pulang' => "<li>Kelas 10–12: pukul 16.00 WITA.</li>",
            ],
            'PAUD' => [
                'nama' => 'PAUD Islam Terpadu Bina Insan Palu',
                'jam_pulang' => "<li>Kelompok Bermain & TK: pukul 11.30 WITA.</li>",
            ]
        ];

        $checkStmt = $pdo->prepare("SELECT 1 FROM `spmb_surat_template` WHERE `jenjang` = ?");
        $insertStmt = $pdo->prepare("
            INSERT INTO `spmb_surat_template` 
            (`jenjang`, `judul_surat`, `nama_sekolah`, `kop_gambar`, `isi_template`, `catatan_bawah`, `created_at`, `updated_at`)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");

        foreach ($units as $jenjang => $u) {
            $checkStmt->execute([$jenjang]);
            if (!$checkStmt->fetch()) {
                $namaSekolah = $u['nama'];
                $jamPulang = $u['jam_pulang'];
                
                $isi = '<p style="text-align: center; font-weight: bold; margin-bottom: 20px; font-size: 14pt;">BISMILLAHIRROHMANIRROHIM</p>' . "\n\n" .
'<p>Dengan mengharapkan Ridho Allah Yang Maha Kuasa, saya bertanda tangan di bawah ini:</p>' . "\n\n" .
'<table style="width: 100%; margin-left: 15px; margin-bottom: 15px; border-collapse: collapse;">' . "\n" .
'  <tr><td style="width: 160px;">Nama Orang tua</td><td></td><td></td></tr>' . "\n" .
'  <tr><td style="padding-left: 20px;">Ayah</td><td style="width: 15px;">:</td><td><strong>{nama_ayah}</strong></td></tr>' . "\n" .
'  <tr><td style="padding-left: 20px;">Ibu</td><td style="width: 15px;">:</td><td><strong>{nama_ibu}</strong></td></tr>' . "\n" .
'</table>' . "\n\n" .
'<p>Adalah orang tua dari murid ' . $namaSekolah . ' :</p>' . "\n\n" .
'<table style="width: 100%; margin-left: 15px; margin-bottom: 20px; border-collapse: collapse;">' . "\n" .
'  <tr><td style="width: 160px;">Nama Anak</td><td style="width: 15px;">:</td><td><strong>{nama_anak}</strong></td></tr>' . "\n" .
'</table>' . "\n\n" .
'<p>Sebagai bagian dari ikhtiar bersama dalam mendampingi pendidikan dan perkembangan anak, saya berkomitmen untuk bekerja sama dengan ' . $namaSekolah . ' serta memenuhi ketentuan yang berlaku sebagai berikut:</p>' . "\n\n" .
'<h4 style="margin-top: 18px; margin-bottom: 6px;">A. PENDAMPINGAN PENDIDIKAN DAN PEMBINAAN ANAK</h4>' . "\n" .
'<ol style="margin-left: 20px; padding-left: 0; line-height: 1.6;">' . "\n" .
'  <li>Bekerja sama dengan guru dan sekolah dalam mendampingi pendidikan dan perkembangan anak, terutama dalam:' . "\n" .
'    <ul style="list-style-type: lower-alpha; margin-left: 20px; padding-left: 0;">' . "\n" .
'      <li>Pelaksanaan <strong>shalat lima waktu</strong>.</li>' . "\n" .
'      <li>Pembiasaan membaca <strong>buku jilid dan tilawah Al-Qur\'an</strong>.</li>' . "\n" .
'      <li>Perkembangan <strong>tahfidz, bacaan Al-Qur\'an, doa, dan bacaan shalat</strong>.</li>' . "\n" .
'      <li>Perkembangan <strong>akhlak dan budi pekerti</strong>.</li>' . "\n" .
'      <li>Pendampingan dalam <strong>penggunaan gadget</strong>.</li>' . "\n" .
'      <li>Perkembangan <strong>pembelajaran akademik</strong>.</li>' . "\n" .
'    </ul>' . "\n" .
'  </li>' . "\n" .
'  <li>Membantu anak mengulang hafalan, melakukan <strong>murojaah</strong>, atau menambah hafalan di rumah sesuai target yang telah direncanakan.</li>' . "\n" .
'  <li>Membaca dan mengisi <strong>lembar yaumian</strong> yang terdapat dalam buku penghubung anak.</li>' . "\n" .
'  <li>Memperhatikan dan melaksanakan pesan atau informasi dari guru dan yayasan yang ditujukan kepada anak maupun orang tua, baik melalui buku penghubung maupun media informasi lainnya.</li>' . "\n" .
'</ol>' . "\n\n" .
'<h4 style="margin-top: 18px; margin-bottom: 6px;">B. KEDISIPLINAN DAN KEHADIRAN</h4>' . "\n" .
'<ol start="5" style="margin-left: 20px; padding-left: 0; line-height: 1.6;">' . "\n" .
'  <li>Mendukung anak untuk memenuhi kehadiran minimal 85% setiap semester.</li>' . "\n" .
'  <li>Membantu anak hadir tepat waktu di sekolah, yaitu pukul 07.00 WITA.</li>' . "\n" .
'  <li>Apabila anak datang terlambat, orang tua akan menerima pemberitahuan keterlambatan dari wali kelas.</li>' . "\n" .
'  <li>Apabila keterlambatan terjadi untuk keempat kalinya dalam bulan yang sama, sekolah akan melakukan koordinasi dengan orang tua/wali dan anak dapat dipulangkan sesuai ketentuan sekolah.</li>' . "\n" .
'  <li>Mematuhi tata tertib dan ketentuan kedisiplinan yang berlaku di ' . $namaSekolah . '.</li>' . "\n" .
'</ol>' . "\n\n" .
'<h4 style="margin-top: 18px; margin-bottom: 6px;">C. PENJEMPUTAN DAN KEAMANAN ANAK</h4>' . "\n" .
'<ol start="10" style="margin-left: 20px; padding-left: 0; line-height: 1.6;">' . "\n" .
'  <li>Menjemput anak sesuai dengan jadwal kepulangan yang telah ditentukan:' . "\n" .
'    <ul style="list-style-type: lower-alpha; margin-left: 20px; padding-left: 0;">' . "\n" .
'      ' . $jamPulang . "\n" .
'    </ul>' . "\n" .
'  </li>' . "\n" .
'  <li>Mengupayakan penjemputan anak <strong>paling lambat 30 menit</strong> setelah waktu kepulangan sekolah.</li>' . "\n" .
'  <li>Menginformasikan kepada pihak sekolah apabila terdapat kondisi tertentu yang menyebabkan keterlambatan penjemputan.</li>' . "\n" .
'  <li>Tidak mengizinkan anak membawa <strong>benda tajam, perhiasan, barang berharga, maupun handphone (HP)</strong> ke sekolah.</li>' . "\n" .
'</ol>' . "\n\n" .
'<h4 style="margin-top: 18px; margin-bottom: 6px;">D. KOMUNIKASI DAN KEMITRAAN ORANG TUA</h4>' . "\n" .
'<ol start="14" style="margin-left: 20px; padding-left: 0; line-height: 1.6;">' . "\n" .
'  <li>Melakukan konsultasi secara berkala dengan <strong>kepala sekolah, wali kelas, atau guru mata pelajaran</strong> terkait perkembangan anak.</li>' . "\n" .
'  <li>Menjalin hubungan silaturahmi yang baik dengan <strong>guru, yayasan, dan sesama orang tua/wali peserta didik</strong>.</li>' . "\n" .
'  <li>Mengikuti kegiatan <strong>parenting, seminar, dan pelatihan</strong> yang diselenggarakan oleh sekolah sebagai bagian dari penguatan peran orang tua dalam pendidikan anak.</li>' . "\n" .
'</ol>' . "\n\n" .
'<h4 style="margin-top: 18px; margin-bottom: 6px;">E. KEGIATAN DAN PROGRAM SEKOLAH</h4>' . "\n" .
'<ol start="17" style="margin-left: 20px; padding-left: 0; line-height: 1.6;">' . "\n" .
'  <li>Mengikutsertakan anak dalam <strong>kegiatan kunjungan edukatif dan kegiatan ekstrakurikuler</strong> yang diselenggarakan sekolah, serta mendampingi apabila diperlukan.</li>' . "\n" .
'  <li>Tidak mengadakan <strong>perayaan ulang tahun</strong> di lingkungan sekolah. Kegiatan berbagi kepada teman diperbolehkan dengan tetap berkoordinasi dengan pihak sekolah.</li>' . "\n" .
'</ol>' . "\n\n" .
'<h4 style="margin-top: 18px; margin-bottom: 6px;">F. PEMBIAYAAN PENDIDIKAN</h4>' . "\n" .
'<ol start="19" style="margin-left: 20px; padding-left: 0; line-height: 1.6;">' . "\n" .
'  <li>Memenuhi kewajiban <strong>infaq pendidikan anak</strong> sesuai ketentuan yang telah ditetapkan, meliputi:' . "\n" .
'    <ul style="list-style-type: lower-alpha; margin-left: 20px; padding-left: 0;">' . "\n" .
'      <li><strong>Investasi Penyelenggaraan Pendidikan</strong>, dibayarkan paling lambat tanggal 10 pada bulan berjalan.</li>' . "\n" .
'      <li><strong>Biaya kegiatan</strong>, sesuai anggaran dan waktu pelaksanaan kegiatan.</li>' . "\n" .
'      <li><strong>Buku paket</strong> sesuai kebutuhan dan ketentuan sekolah.</li>' . "\n" .
'    </ul>' . "\n" .
'  </li>' . "\n" .
'</ol>' . "\n\n" .
'<h4 style="margin-top: 18px; margin-bottom: 6px;">G. KOMITMEN SEBAGAI KELUARGA BINA INSAN</h4>' . "\n" .
'<ol start="20" style="margin-left: 20px; padding-left: 0; line-height: 1.6;">' . "\n" .
'  <li>Menjadi bagian dari keluarga besar ' . $namaSekolah . ' serta turut menjaga nama baik sekolah.</li>' . "\n" .
'  <li>Mendukung terciptanya lingkungan pendidikan yang <strong>aman, nyaman, positif, dan kondusif</strong> bagi seluruh murid.</li>' . "\n" .
'  <li>Menjadikan <strong>rumah dan sekolah sebagai mitra yang saling menguatkan</strong> dalam mendampingi pendidikan, pembinaan, dan perkembangan anak.</li>' . "\n" .
'</ol>' . "\n\n" .
'<p style="margin-top: 25px; line-height: 1.6;">Demikian pernyataan komitmen ini saya buat dengan <strong>sadar, sukarela, dan penuh tanggung jawab</strong> sebagai bentuk kesediaan untuk bekerja sama dengan ' . $namaSekolah . ' dalam mendampingi pendidikan dan perkembangan anak.</p>' . "\n\n" .
'<p style="margin-top: 15px; line-height: 1.6;">Semoga Allah Subhanahu wa Ta\'ala memberikan kemudahan dan keberkahan dalam setiap ikhtiar kita dalam mendidik generasi yang beriman, berakhlak, dan unggul.</p>';

                $insertStmt->execute([
                    $jenjang,
                    'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID',
                    $namaSekolah,
                    null,
                    $isi,
                    'Catatan: Dokumen ini disimpan di arsip sekolah sebagai bukti komitmen resmi orang tua/wali murid.'
                ]);
            }
        }
    }

    /**
     * Migration 028: Kolom Pengembalian Berkas & Surat Kesanggupan Calon Siswa
     */
    private static function migration028AddPengembalianBerkasFeatures(PDO $pdo): void
    {
        // 1. Tambah kolom surat_kesanggupan
        self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'surat_kesanggupan', 'VARCHAR(255) NULL', 'status_berkas');
        // 2. Tambah kolom tanggal_kembali_berkas
        self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'tanggal_kembali_berkas', 'DATETIME NULL', 'surat_kesanggupan');
        // 3. Tambah kolom catatan_berkas
        self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'catatan_berkas', 'TEXT NULL', 'tanggal_kembali_berkas');
        // 4. Tambah kolom petugas_verifikasi_berkas
        self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'petugas_verifikasi_berkas', 'VARCHAR(100) NULL', 'catatan_berkas');
        // 5. Tambah kolom is_data_sesuai
        self::addColumnIfNotExists($pdo, 'spmb_pendaftar', 'is_data_sesuai', 'TINYINT(1) DEFAULT 0', 'petugas_verifikasi_berkas');

        // 6. Modifikasi status_berkas agar mencakup 'Belum Dikembalikan'
        try {
            $pdo->exec("ALTER TABLE `spmb_pendaftar` MODIFY COLUMN `status_berkas` ENUM('Belum Dikembalikan', 'Belum Lengkap', 'Lengkap', 'Perlu Perbaikan') DEFAULT 'Belum Dikembalikan'");
            $pdo->exec("UPDATE `spmb_pendaftar` SET `status_berkas` = 'Belum Dikembalikan' WHERE `status_berkas` = 'Belum Lengkap' OR `status_berkas` IS NULL");
        } catch (Throwable $e) {}
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
