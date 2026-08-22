<?php
/**
 * Portal BIP - Desktop Database Migration Script
 * 
 * Migrasi dan sinkronisasi struktur database untuk modul Desktop:
 * 1. pegawai (dengan field NIK, NPWP, Email, No. WA, dll.)
 * 2. pegawai_pendidikan (Riwayat Pendidikan Pegawai)
 * 3. pegawai_penugasan (SK & Riwayat Penugasan Pegawai)
 * 4. master_unit_tugas, master_jabatan, master_status_kerja, master_jenis_pegawai
 * 5. tahun_akademik, kelas, siswa
 * 6. users, roles, permissions, modules, module_permissions, role_permissions, audit_logs, settings
 * 
 * Penggunaan:
 * - Terminal (CLI): php desktop-migrate/migrate.php
 * - Browser: http://localhost/portal-bip/desktop-migrate
 */

$isCli = (php_sapi_name() === 'cli');

// 1. Tentukan Base Path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// 2. Ambil konfigurasi database langsung dari config/database.php
$configFile = BASE_PATH . '/config/database.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    // Fallback jika file config tidak ditemukan
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'db_portal_bip');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// 3. Inisialisasi Koneksi PDO
try {
    // Koneksi ke server MySQL terlebih dahulu untuk memastikan database dibuat
    $pdoServer = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Buat database jika belum ada
    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci;");

    // Koneksi spesifik ke database target
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {
    $errorMsg = "Koneksi database gagal (" . DB_HOST . ":" . DB_PORT . " / " . DB_NAME . "): " . $e->getMessage();
    if ($isCli) {
        fwrite(STDERR, "[ERROR] $errorMsg\n");
    } else {
        echo "<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:12px;margin:20px;'><strong>Error:</strong> $errorMsg</div>";
    }
    exit(1);
}

// 4. Skema Tabel Desktop
$tables = [
    // Master Kepegawaian
    'master_unit_tugas' => "
        CREATE TABLE IF NOT EXISTS `master_unit_tugas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama` VARCHAR(100) NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'master_jabatan' => "
        CREATE TABLE IF NOT EXISTS `master_jabatan` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama` VARCHAR(100) NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'master_status_kerja' => "
        CREATE TABLE IF NOT EXISTS `master_status_kerja` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama` VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'master_jenis_pegawai' => "
        CREATE TABLE IF NOT EXISTS `master_jenis_pegawai` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama` VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Data Pegawai (dengan NPWP, Email, No. WA)
    'pegawai' => "
        CREATE TABLE IF NOT EXISTS `pegawai` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `foto` VARCHAR(255) DEFAULT NULL,
            `niy` VARCHAR(50) DEFAULT NULL,
            `nik` VARCHAR(50) DEFAULT NULL,
            `npwp` VARCHAR(50) DEFAULT NULL,
            `email` VARCHAR(100) DEFAULT NULL,
            `no_wa` VARCHAR(30) DEFAULT NULL,
            `nama` VARCHAR(100) NOT NULL,
            `gelar` VARCHAR(50) DEFAULT NULL,
            `jenis_kelamin` ENUM('L','P') NOT NULL DEFAULT 'L',
            `status_nikah` VARCHAR(50) DEFAULT NULL,
            `tempat_lahir` VARCHAR(100) DEFAULT NULL,
            `tanggal_lahir` DATE DEFAULT NULL,
            `nama_ibu` VARCHAR(100) DEFAULT NULL,
            `unit_tugas` VARCHAR(100) DEFAULT NULL,
            `jabatan` VARCHAR(100) DEFAULT NULL,
            `status_kerja` VARCHAR(50) DEFAULT NULL,
            `jenis_pegawai` VARCHAR(50) DEFAULT NULL,
            `status_dapodik` VARCHAR(50) DEFAULT NULL,
            `tanggal_masuk` DATE DEFAULT NULL,
            `tmt` DATE DEFAULT NULL,
            `alamat_ktp` TEXT DEFAULT NULL,
            `kab_kota_ktp` VARCHAR(100) DEFAULT NULL,
            `kec_ktp` VARCHAR(100) DEFAULT NULL,
            `kel_ktp` VARCHAR(100) DEFAULT NULL,
            `alamat_domisili` TEXT DEFAULT NULL,
            `kab_kota_domisili` VARCHAR(100) DEFAULT NULL,
            `kec_domisili` VARCHAR(100) DEFAULT NULL,
            `kel_domisili` VARCHAR(100) DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Riwayat Pendidikan Pegawai
    'pegawai_pendidikan' => "
        CREATE TABLE IF NOT EXISTS `pegawai_pendidikan` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `pegawai_id` BIGINT UNSIGNED NOT NULL,
            `jenjang` VARCHAR(50) NOT NULL,
            `institusi` VARCHAR(150) NOT NULL,
            `jurusan` VARCHAR(100) DEFAULT NULL,
            `tahun_lulus` VARCHAR(4) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_peg_pend_pegawai` (`pegawai_id`),
            CONSTRAINT `fk_pegawai_pendidikan` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Grup Penugasan Pegawai (Periode SK / Pembagian Tugas)
    'penugasan_grup' => "
        CREATE TABLE IF NOT EXISTS `penugasan_grup` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama_grup` VARCHAR(150) NOT NULL,
            `tahun_akademik_id` INT DEFAULT NULL,
            `semester` ENUM('Ganjil','Genap') DEFAULT 'Ganjil',
            `no_sk` VARCHAR(100) DEFAULT NULL,
            `tanggal_sk` DATE DEFAULT NULL,
            `tmt_mulai` DATE DEFAULT NULL,
            `tst_selesai` DATE DEFAULT NULL,
            `penandatangan_nama` VARCHAR(150) DEFAULT NULL,
            `penandatangan_jabatan` VARCHAR(150) DEFAULT NULL,
            `penandatangan_nip` VARCHAR(50) DEFAULT NULL,
            `kota_sk` VARCHAR(100) DEFAULT 'Palu',
            `file_kop` VARCHAR(255) DEFAULT NULL,
            `file_footer` VARCHAR(255) DEFAULT NULL,
            `menimbang` TEXT DEFAULT NULL,
            `mengingat` TEXT DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 0,
            `keterangan` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Penugasan & SK Pegawai
    'pegawai_penugasan' => "
        CREATE TABLE IF NOT EXISTS `pegawai_penugasan` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `grup_id` INT DEFAULT NULL,
            `pegawai_id` BIGINT UNSIGNED NOT NULL,
            `no_sk` VARCHAR(100) NOT NULL,
            `tanggal_sk` DATE NOT NULL,
            `unit_tugas_id` INT NOT NULL,
            `jabatan_id` INT NOT NULL,
            `tmt_mulai` DATE NOT NULL,
            `tst_selesai` DATE DEFAULT NULL,
            `file_sk` VARCHAR(255) DEFAULT NULL,
            `status` ENUM('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
            `keterangan` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`grup_id`),
            INDEX (`pegawai_id`),
            INDEX (`unit_tugas_id`),
            INDEX (`jabatan_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Riwayat Karir & Jabatan Pegawai (Otomatis dari SK Penugasan & Tambah Manual)
    'pegawai_karir' => "
        CREATE TABLE IF NOT EXISTS `pegawai_karir` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `pegawai_id` BIGINT UNSIGNED NOT NULL,
            `penugasan_id` INT DEFAULT NULL,
            `tipe_karir` VARCHAR(50) NOT NULL DEFAULT 'Penugasan SK',
            `unit_tugas` VARCHAR(100) DEFAULT NULL,
            `unit_tugas_id` INT DEFAULT NULL,
            `jabatan` VARCHAR(100) NOT NULL,
            `jabatan_id` INT DEFAULT NULL,
            `no_sk` VARCHAR(100) DEFAULT NULL,
            `tanggal_sk` DATE DEFAULT NULL,
            `tmt_mulai` DATE NOT NULL,
            `tst_selesai` DATE DEFAULT NULL,
            `penandatangan_sk` VARCHAR(150) DEFAULT NULL,
            `file_sk` VARCHAR(255) DEFAULT NULL,
            `status` ENUM('Aktif','Selesai','Riwayat Lalu') NOT NULL DEFAULT 'Aktif',
            `keterangan` TEXT DEFAULT NULL,
            `is_otomatis` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`pegawai_id`),
            INDEX (`penugasan_id`),
            INDEX (`unit_tugas_id`),
            INDEX (`jabatan_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Prestasi & Penghargaan Pegawai / Guru
    'pegawai_prestasi' => "
        CREATE TABLE IF NOT EXISTS `pegawai_prestasi` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `pegawai_id` BIGINT UNSIGNED NOT NULL,
            `nama_prestasi` VARCHAR(200) NOT NULL,
            `tingkat` ENUM('Sekolah/Internal','Kecamatan','Kota/Kabupaten','Provinsi','Nasional','Internasional') NOT NULL DEFAULT 'Kota/Kabupaten',
            `kategori` VARCHAR(100) NOT NULL DEFAULT 'Akademik',
            `peringkat` VARCHAR(100) NOT NULL DEFAULT 'Juara 1',
            `penyelenggara` VARCHAR(150) NOT NULL,
            `tahun` VARCHAR(4) NOT NULL,
            `tanggal_peroleh` DATE DEFAULT NULL,
            `nomor_sertifikat` VARCHAR(100) DEFAULT NULL,
            `file_sertifikat` VARCHAR(255) DEFAULT NULL,
            `foto_dokumentasi` VARCHAR(255) DEFAULT NULL,
            `keterangan` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`pegawai_id`),
            INDEX (`tingkat`),
            INDEX (`tahun`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Riwayat Pelatihan & Diklat Pegawai / Guru
    'pegawai_pelatihan' => "
        CREATE TABLE IF NOT EXISTS `pegawai_pelatihan` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `pegawai_id` BIGINT UNSIGNED NOT NULL,
            `nama_pelatihan` VARCHAR(200) NOT NULL,
            `jenis_pelatihan` ENUM('Diklat Fungsional','Bimtek & Workshop','Pelatihan Teknis/Manajerial','Seminar / Webinar','Sertifikasi Keahlian / Profesi','Kursus / Pelatihan Mandiri','In House Training') NOT NULL DEFAULT 'Bimtek & Workshop',
            `penyelenggara` VARCHAR(150) NOT NULL,
            `tempat` VARCHAR(150) DEFAULT NULL,
            `tahun` VARCHAR(4) NOT NULL,
            `tanggal_mulai` DATE NOT NULL,
            `tanggal_selesai` DATE DEFAULT NULL,
            `jumlah_jam` INT DEFAULT 0,
            `nomor_sertifikat` VARCHAR(100) DEFAULT NULL,
            `peran` ENUM('Peserta','Narasumber / Pemateri','Fasilitator / Moderator','Panitia') NOT NULL DEFAULT 'Peserta',
            `file_sertifikat` VARCHAR(255) DEFAULT NULL,
            `foto_dokumentasi` VARCHAR(255) DEFAULT NULL,
            `keterangan` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`pegawai_id`),
            INDEX (`jenis_pelatihan`),
            INDEX (`tahun`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Data Anggota Keluarga Pegawai (Suami/Istri, Anak, Orang Tua)
    'pegawai_keluarga' => "
        CREATE TABLE IF NOT EXISTS `pegawai_keluarga` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `pegawai_id` BIGINT UNSIGNED NOT NULL,
            `hubungan` VARCHAR(50) NOT NULL,
            `nama` VARCHAR(100) NOT NULL,
            `jenis_kelamin` ENUM('L','P') NOT NULL DEFAULT 'L',
            `tempat_lahir` VARCHAR(100) DEFAULT NULL,
            `tanggal_lahir` DATE DEFAULT NULL,
            `pendidikan_terakhir` VARCHAR(50) DEFAULT NULL,
            `pekerjaan` VARCHAR(100) DEFAULT NULL,
            `no_hp` VARCHAR(30) DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX (`pegawai_id`),
            CONSTRAINT `fk_pegawai_keluarga` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Keahlian & Keterampilan Pegawai (Skill Pegawai & Guru)
    'pegawai_skill' => "
        CREATE TABLE IF NOT EXISTS `pegawai_skill` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `pegawai_id` BIGINT UNSIGNED NOT NULL,
            `nama_skill` VARCHAR(100) NOT NULL,
            `kategori` VARCHAR(50) DEFAULT 'Teknis & IT',
            `tingkat_keahlian` ENUM('Pemula','Menengah','Mahir','Ahli') NOT NULL DEFAULT 'Menengah',
            `deskripsi` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX (`pegawai_id`),
            CONSTRAINT `fk_pegawai_skill` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // Data Akademik: Tahun Akademik & Kelas
    'tahun_akademik' => "
        CREATE TABLE IF NOT EXISTS `tahun_akademik` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tahun` VARCHAR(20) NOT NULL,
            `semester` ENUM('Ganjil','Genap') NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'kelas' => "
        CREATE TABLE IF NOT EXISTS `kelas` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `tahun_akademik_id` INT DEFAULT NULL,
            `nama_kelas` VARCHAR(100) NOT NULL,
            `wali_kelas` VARCHAR(100) DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    'siswa' => "
        CREATE TABLE IF NOT EXISTS `siswa` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `nis` VARCHAR(30) DEFAULT NULL,
            `nisn` VARCHAR(30) DEFAULT NULL,
            `nik` VARCHAR(50) DEFAULT NULL,
            `nama_lengkap` VARCHAR(150) NOT NULL,
            `nama_panggilan` VARCHAR(50) DEFAULT NULL,
            `jenis_kelamin` ENUM('L','P') NOT NULL DEFAULT 'L',
            `tempat_lahir` VARCHAR(100) DEFAULT NULL,
            `tanggal_lahir` DATE DEFAULT NULL,
            `kelas_id` BIGINT UNSIGNED DEFAULT NULL,
            `nama_ayah` VARCHAR(100) DEFAULT NULL,
            `nama_ibu` VARCHAR(100) DEFAULT NULL,
            `no_hp_ortu` VARCHAR(30) DEFAULT NULL,
            `alamat` TEXT DEFAULT NULL,
            `foto` VARCHAR(255) DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    "
];

// 5. Eksekusi Migrasi Tabel & Alter Kolom
$results = [];
$startTime = microtime(true);

foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        $results[] = [
            'type' => 'table',
            'name' => $tableName,
            'status' => 'OK',
            'message' => "Tabel `{$tableName}` siap / berhasil dibuat."
        ];
    } catch (PDOException $e) {
        $results[] = [
            'type' => 'table',
            'name' => $tableName,
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ];
    }
}

// 6. Pastikan Kolom Baru (NPWP, Email, No. WA) Ada pada tabel pegawai
try {
    $existingCols = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM `pegawai`");
    while ($row = $stmt->fetch()) {
        $existingCols[] = $row['Field'];
    }

    if (!in_array('npwp', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `npwp` VARCHAR(50) DEFAULT NULL AFTER `nik`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.npwp', 'status' => 'OK', 'message' => 'Kolom `npwp` berhasil ditambahkan.'];
    }
    if (!in_array('email', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `email` VARCHAR(100) DEFAULT NULL AFTER `npwp`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.email', 'status' => 'OK', 'message' => 'Kolom `email` berhasil ditambahkan.'];
    }
    if (!in_array('no_wa', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `no_wa` VARCHAR(30) DEFAULT NULL AFTER `email`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.no_wa', 'status' => 'OK', 'message' => 'Kolom `no_wa` berhasil ditambahkan.'];
    }
    if (!in_array('tanggal_masuk', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `tanggal_masuk` DATE DEFAULT NULL AFTER `status_dapodik`");
        $pdo->exec("UPDATE `pegawai` SET `tanggal_masuk` = `tmt` WHERE `tanggal_masuk` IS NULL AND `tmt` IS NOT NULL");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.tanggal_masuk', 'status' => 'OK', 'message' => 'Kolom `tanggal_masuk` berhasil ditambahkan.'];
    }

    // Kontak Darurat 1 & 2
    if (!in_array('kontak_darurat_1_nama', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `kontak_darurat_1_nama` VARCHAR(100) DEFAULT NULL AFTER `kel_domisili`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.kontak_darurat_1_nama', 'status' => 'OK', 'message' => 'Kolom `kontak_darurat_1_nama` berhasil ditambahkan.'];
    }
    if (!in_array('kontak_darurat_1_hubungan', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `kontak_darurat_1_hubungan` VARCHAR(50) DEFAULT NULL AFTER `kontak_darurat_1_nama`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.kontak_darurat_1_hubungan', 'status' => 'OK', 'message' => 'Kolom `kontak_darurat_1_hubungan` berhasil ditambahkan.'];
    }
    if (!in_array('kontak_darurat_1_no_hp', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `kontak_darurat_1_no_hp` VARCHAR(30) DEFAULT NULL AFTER `kontak_darurat_1_hubungan`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.kontak_darurat_1_no_hp', 'status' => 'OK', 'message' => 'Kolom `kontak_darurat_1_no_hp` berhasil ditambahkan.'];
    }
    if (!in_array('kontak_darurat_2_nama', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `kontak_darurat_2_nama` VARCHAR(100) DEFAULT NULL AFTER `kontak_darurat_1_no_hp`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.kontak_darurat_2_nama', 'status' => 'OK', 'message' => 'Kolom `kontak_darurat_2_nama` berhasil ditambahkan.'];
    }
    if (!in_array('kontak_darurat_2_hubungan', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `kontak_darurat_2_hubungan` VARCHAR(50) DEFAULT NULL AFTER `kontak_darurat_2_nama`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.kontak_darurat_2_hubungan', 'status' => 'OK', 'message' => 'Kolom `kontak_darurat_2_hubungan` berhasil ditambahkan.'];
    }
    if (!in_array('kontak_darurat_2_no_hp', $existingCols)) {
        $pdo->exec("ALTER TABLE `pegawai` ADD COLUMN `kontak_darurat_2_no_hp` VARCHAR(30) DEFAULT NULL AFTER `kontak_darurat_2_hubungan`");
        $results[] = ['type' => 'alter', 'name' => 'pegawai.kontak_darurat_2_no_hp', 'status' => 'OK', 'message' => 'Kolom `kontak_darurat_2_no_hp` berhasil ditambahkan.'];
    }

    // Pastikan kolom penandatangan & SK ada pada penugasan_grup
    $existingGrupCols = [];
    $stmtGrup = $pdo->query("SHOW COLUMNS FROM `penugasan_grup`");
    while ($row = $stmtGrup->fetch()) {
        $existingGrupCols[] = $row['Field'];
    }

    if (!in_array('penandatangan_nama', $existingGrupCols)) {
        $pdo->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `penandatangan_nama` VARCHAR(150) DEFAULT NULL AFTER `tst_selesai`");
        $results[] = ['type' => 'alter', 'name' => 'penugasan_grup.penandatangan_nama', 'status' => 'OK', 'message' => 'Kolom `penandatangan_nama` berhasil ditambahkan.'];
    }
    if (!in_array('penandatangan_jabatan', $existingGrupCols)) {
        $pdo->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `penandatangan_jabatan` VARCHAR(150) DEFAULT NULL AFTER `penandatangan_nama`");
        $results[] = ['type' => 'alter', 'name' => 'penugasan_grup.penandatangan_jabatan', 'status' => 'OK', 'message' => 'Kolom `penandatangan_jabatan` berhasil ditambahkan.'];
    }
    if (!in_array('penandatangan_nip', $existingGrupCols)) {
        $pdo->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `penandatangan_nip` VARCHAR(50) DEFAULT NULL AFTER `penandatangan_jabatan`");
        $results[] = ['type' => 'alter', 'name' => 'penugasan_grup.penandatangan_nip', 'status' => 'OK', 'message' => 'Kolom `penandatangan_nip` berhasil ditambahkan.'];
    }
    if (!in_array('kota_sk', $existingGrupCols)) {
        $pdo->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `kota_sk` VARCHAR(100) DEFAULT 'Makassar' AFTER `penandatangan_nip`");
        $results[] = ['type' => 'alter', 'name' => 'penugasan_grup.kota_sk', 'status' => 'OK', 'message' => 'Kolom `kota_sk` berhasil ditambahkan.'];
    }
    if (!in_array('file_kop', $existingGrupCols)) {
        $pdo->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `file_kop` VARCHAR(255) DEFAULT NULL AFTER `kota_sk`");
        $results[] = ['type' => 'alter', 'name' => 'penugasan_grup.file_kop', 'status' => 'OK', 'message' => 'Kolom `file_kop` berhasil ditambahkan.'];
    }
    if (!in_array('file_footer', $existingGrupCols)) {
        $pdo->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `file_footer` VARCHAR(255) DEFAULT NULL AFTER `file_kop`");
        $results[] = ['type' => 'alter', 'name' => 'penugasan_grup.file_footer', 'status' => 'OK', 'message' => 'Kolom `file_footer` berhasil ditambahkan.'];
    }
    if (!in_array('menimbang', $existingGrupCols)) {
        $pdo->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `menimbang` TEXT DEFAULT NULL AFTER `file_footer`");
        $results[] = ['type' => 'alter', 'name' => 'penugasan_grup.menimbang', 'status' => 'OK', 'message' => 'Kolom `menimbang` berhasil ditambahkan.'];
    }
    if (!in_array('mengingat', $existingGrupCols)) {
        $pdo->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `mengingat` TEXT DEFAULT NULL AFTER `menimbang`");
        $results[] = ['type' => 'alter', 'name' => 'penugasan_grup.mengingat', 'status' => 'OK', 'message' => 'Kolom `mengingat` berhasil ditambahkan.'];
    }

    // Update kota penetapan default ke Palu
    $pdo->exec("UPDATE `penugasan_grup` SET `kota_sk` = 'Palu' WHERE `kota_sk` = 'Makassar' OR `kota_sk` IS NULL OR `kota_sk` = ''");

    // Sinkronisasi data penugasan yang sudah ada ke pegawai_karir (jika belum tercatat)
    $stmtPenugasan = $pdo->query("
        SELECT pp.*, pg.nama_grup, pg.penandatangan_nama, mut.nama AS nama_unit, mj.nama AS nama_jabatan
        FROM pegawai_penugasan pp
        LEFT JOIN penugasan_grup pg ON pp.grup_id = pg.id
        LEFT JOIN master_unit_tugas mut ON pp.unit_tugas_id = mut.id
        LEFT JOIN master_jabatan mj ON pp.jabatan_id = mj.id
    ");
    $penugasanRows = $stmtPenugasan->fetchAll(PDO::FETCH_ASSOC);
    foreach ($penugasanRows as $pRow) {
        $check = $pdo->prepare("SELECT id FROM pegawai_karir WHERE penugasan_id = ?");
        $check->execute([$pRow['id']]);
        if (!$check->fetch()) {
            $ins = $pdo->prepare("
                INSERT INTO pegawai_karir 
                (pegawai_id, penugasan_id, tipe_karir, unit_tugas, unit_tugas_id, jabatan, jabatan_id, no_sk, tanggal_sk, tmt_mulai, tst_selesai, penandatangan_sk, status, keterangan, is_otomatis)
                VALUES (?, ?, 'Penugasan SK', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $ins->execute([
                $pRow['pegawai_id'],
                $pRow['id'],
                $pRow['nama_unit'] ?? null,
                $pRow['unit_tugas_id'],
                $pRow['nama_jabatan'] ?? 'Staff',
                $pRow['jabatan_id'],
                $pRow['no_sk'] ?? null,
                $pRow['tanggal_sk'] ?? null,
                $pRow['tmt_mulai'],
                $pRow['tst_selesai'] ?? null,
                $pRow['penandatangan_nama'] ?? null,
                $pRow['status'] ?? 'Aktif',
                !empty($pRow['nama_grup']) ? 'Otomatis dari penugasan grup: ' . $pRow['nama_grup'] : 'Otomatis dari penugasan SK'
            ]);
        }
    }
} catch (PDOException $e) {
    $results[] = ['type' => 'alter', 'name' => 'pegawai_columns', 'status' => 'WARN', 'message' => $e->getMessage()];
}

// 7. Seed Master Data Default Jika Kosong
$masterSeeds = [
    'master_unit_tugas' => [
        ['nama' => 'PAUD'],
        ['nama' => 'SD'],
        ['nama' => 'SMP'],
        ['nama' => 'SMA'],
        ['nama' => 'Yayasan']
    ],
    'master_jabatan' => [
        ['nama' => 'Wali Kelas'],
        ['nama' => 'Guru Mapel'],
        ['nama' => 'Wakakum'],
        ['nama' => 'Kepala Sekolah'],
        ['nama' => 'Kepala Divisi IT']
    ],
    'master_status_kerja' => [
        ['nama' => 'Tetap'],
        ['nama' => 'Kontrak'],
        ['nama' => 'Training'],
        ['nama' => 'Honorer']
    ],
    'master_jenis_pegawai' => [
        ['nama' => 'Guru'],
        ['nama' => 'Support System'],
        ['nama' => 'Tenaga Kependidikan']
    ]
];

foreach ($masterSeeds as $table => $rows) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        if ($count == 0) {
            $stmt = $pdo->prepare("INSERT INTO `{$table}` (`nama`) VALUES (:nama)");
            foreach ($rows as $r) {
                $stmt->execute([':nama' => $r['nama']]);
            }
            $results[] = [
                'type' => 'seed',
                'name' => $table,
                'status' => 'OK',
                'message' => "Data awal default berhasil diisi (" . count($rows) . " item)."
            ];
        }
    } catch (PDOException $e) {
        $results[] = [
            'type' => 'seed',
            'name' => $table,
            'status' => 'WARN',
            'message' => $e->getMessage()
        ];
    }
}

$executionTime = round((microtime(true) - $startTime) * 1000, 2);

// 8. Tampilkan Output (CLI atau HTML)
if ($isCli) {
    echo "\n=======================================================\n";
    echo "   PORTAL BIP - DESKTOP DATABASE MIGRATION\n";
    echo "   Database: " . DB_NAME . " (" . DB_HOST . ":" . DB_PORT . ")\n";
    echo "=======================================================\n\n";

    foreach ($results as $res) {
        $badge = $res['status'] === 'OK' ? '[OK]   ' : ($res['status'] === 'WARN' ? '[WARN] ' : '[ERR]  ');
        echo sprintf("%s %-25s : %s\n", $badge, $res['name'], $res['message']);
    }

    echo "\n-------------------------------------------------------\n";
    echo "Selesai dalam {$executionTime} ms.\n\n";
} else {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Migrasi Database Desktop - Portal BIP</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    </head>
    <body class="bg-slate-900 text-slate-100 min-h-screen p-4 sm:p-8 flex items-center justify-center">
        <div class="max-w-2xl w-full bg-slate-800 border border-slate-700/60 rounded-3xl p-6 sm:p-8 shadow-2xl">
            <div class="flex items-center justify-between mb-6 border-b border-slate-700/60 pb-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/20 border border-sky-500/30 flex items-center justify-center text-sky-400 font-bold text-xl">
                        🖥️
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Migrasi Database Desktop</h1>
                        <p class="text-xs text-slate-400">Database: <span class="text-sky-400 font-mono font-semibold"><?= htmlspecialchars(DB_NAME) ?></span> (<?= htmlspecialchars(DB_HOST) ?>:<?= htmlspecialchars(DB_PORT) ?>)</p>
                    </div>
                </div>
                <span class="px-3 py-1 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-semibold rounded-full">
                    Selesai (<?= $executionTime ?> ms)
                </span>
            </div>

            <div class="space-y-3 mb-6">
                <?php foreach ($results as $res): ?>
                    <div class="flex items-start justify-between p-3.5 bg-slate-900/60 border border-slate-700/40 rounded-2xl gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base"><?= $res['status'] === 'OK' ? '✅' : ($res['status'] === 'WARN' ? '⚠️' : '❌') ?></span>
                            <div>
                                <p class="text-sm font-semibold text-white font-mono"><?= htmlspecialchars($res['name']) ?></p>
                                <p class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($res['message']) ?></p>
                            </div>
                        </div>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md <?= $res['status'] === 'OK' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' ?>">
                            <?= $res['status'] ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-700/60">
                <a href="<?= (defined('BASE_URL') ? BASE_URL : '') . '/kelola-pegawai' ?>" class="w-full sm:w-auto px-6 py-2.5 bg-sky-500 hover:bg-sky-600 text-white font-semibold rounded-xl text-sm transition-colors text-center">
                    Buka Kelola Pegawai →
                </a>
                <a href="<?= (defined('BASE_URL') ? BASE_URL : '') . '/dashboard' ?>" class="w-full sm:w-auto px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold rounded-xl text-sm transition-colors text-center">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
