<?php
/**
 * Portal BIP - Mobile Database Migration Script
 * 
 * Creates and updates tables required for the mobile portal features:
 * 1. absensi_pegawai (GPS Geolocation Attendance)
 * 2. jurnal_mengajar (Daily Teaching Journal)
 * 3. presensi_kelas & presensi_kelas_detail (Classroom Presence & PIC/Mendampingi)
 * 4. mutabaah_ibadah_guru (Daily Worship: Sholat 5 Waktu, Tilawah, Dzikir, Tadabbur)
 * 5. keterlambatan_siswa (Student Lateness Tracker & Coaching Actions)
 * 6. izin_guru (Teacher Permit: Izin Tidak Masuk & Izin Keluar/Mengajar)
 * 7. cuti_guru (Teacher Leave Applications & Multi-step Verification)
 * 8. kuota_cuti_guru (Teacher Leave Quotas & Balances)
 * 9. quran_bookmark_guru (Quran Bookmark with exact timestamp)
 * 
 * Usage:
 * - Via CLI: php mobile-migrate/migrate.php
 * - Via Browser: http://localhost/portal-bip/mobile-migrate/migrate.php
 */

$isCli = (php_sapi_name() === 'cli');

// Define Base Paths
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Load Database Config if exists
$configFile = BASE_PATH . '/config/database.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'db_portal_bip');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// Connect to MySQL
try {
    // Connect to Server first (to check/create database)
    $pdoServer = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Create DB if not exists
    $pdoServer->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_CHARSET . "_unicode_ci;");

    // Connect to specific Database
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
    $errorMsg = "Koneksi database gagal: " . $e->getMessage();
    if ($isCli) {
        fwrite(STDERR, "[ERROR] $errorMsg\n");
    } else {
        echo "<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:12px;'><strong>Error:</strong> $errorMsg</div>";
    }
    exit(1);
}

// Table Definitions & Migration Schemas
$migrations = [

    // 1. Absensi Pegawai (GPS Geolocation)
    'absensi_pegawai' => "
        CREATE TABLE IF NOT EXISTS `absensi_pegawai` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL DEFAULT 1,
            `tanggal` DATE NOT NULL,
            `jam_masuk` TIME NULL,
            `jam_pulang` TIME NULL,
            `latitude_masuk` DECIMAL(10, 8) NULL,
            `longitude_masuk` DECIMAL(11, 8) NULL,
            `jarak_masuk_meter` INT NULL,
            `latitude_pulang` DECIMAL(10, 8) NULL,
            `longitude_pulang` DECIMAL(11, 8) NULL,
            `jarak_pulang_meter` INT NULL,
            `status_kehadiran` ENUM('hadir', 'terlambat', 'izin', 'sakit', 'alpha') DEFAULT 'hadir',
            `keterangan` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`user_id`),
            INDEX (`tanggal`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 2. Jurnal Mengajar Harian
    'jurnal_mengajar' => "
        CREATE TABLE IF NOT EXISTS `jurnal_mengajar` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `kode_jurnal` VARCHAR(30) UNIQUE NOT NULL,
            `guru_id` INT NOT NULL DEFAULT 1,
            `kelas` VARCHAR(50) NOT NULL,
            `mata_pelajaran` VARCHAR(100) NOT NULL,
            `tanggal` DATE NOT NULL,
            `jam_ke` VARCHAR(50) NOT NULL,
            `materi_pokok` VARCHAR(255) NOT NULL,
            `uraian_kegiatan` TEXT NULL,
            `catatan_kelas` TEXT NULL,
            `kehadiran_summary` VARCHAR(100) NULL DEFAULT '30/32 Hadir',
            `status` ENUM('draft', 'selesai', 'tervalidasi') DEFAULT 'selesai',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`guru_id`),
            INDEX (`tanggal`),
            INDEX (`kelas`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 3. Presensi Masuk Kelas (PIC / Mendampingi)
    'presensi_kelas' => "
        CREATE TABLE IF NOT EXISTS `presensi_kelas` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `guru_id` INT NOT NULL DEFAULT 1,
            `kelas` VARCHAR(50) NOT NULL,
            `tanggal` DATE NOT NULL,
            `jam_ke` VARCHAR(50) NOT NULL,
            `role_mengajar` ENUM('PIC', 'Mendampingi') DEFAULT 'PIC',
            `jp` INT DEFAULT 2,
            `keterangan_masuk` VARCHAR(15) NULL,
            `waktu_presensi` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`guru_id`),
            INDEX (`tanggal`),
            INDEX (`kelas`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 4. Detail Absensi Siswa per Kelas
    'presensi_kelas_detail' => "
        CREATE TABLE IF NOT EXISTS `presensi_kelas_detail` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `presensi_kelas_id` INT NOT NULL,
            `siswa_id` INT NOT NULL,
            `nama_siswa` VARCHAR(100) NOT NULL,
            `status` ENUM('H', 'S', 'I', 'A') DEFAULT 'H',
            `catatan` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`presensi_kelas_id`),
            INDEX (`siswa_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 5. Mutaba'ah Ibadah Guru Harian
    'mutabaah_ibadah_guru' => "
        CREATE TABLE IF NOT EXISTS `mutabaah_ibadah_guru` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `guru_id` INT NOT NULL DEFAULT 1,
            `tanggal` DATE NOT NULL,
            `gender` ENUM('L', 'P') DEFAULT 'P',
            `sholat_subuh` TINYINT(1) DEFAULT 0,
            `sholat_subuh_loc` ENUM('rumah', 'masjid') DEFAULT 'rumah',
            `sholat_dzuhur` TINYINT(1) DEFAULT 0,
            `sholat_dzuhur_loc` ENUM('rumah', 'masjid') DEFAULT 'rumah',
            `sholat_ashar` TINYINT(1) DEFAULT 0,
            `sholat_ashar_loc` ENUM('rumah', 'masjid') DEFAULT 'rumah',
            `sholat_maghrib` TINYINT(1) DEFAULT 0,
            `sholat_maghrib_loc` ENUM('rumah', 'masjid') DEFAULT 'rumah',
            `sholat_isya` TINYINT(1) DEFAULT 0,
            `sholat_isya_loc` ENUM('rumah', 'masjid') DEFAULT 'rumah',
            `tilawah_checked` TINYINT(1) DEFAULT 0,
            `tilawah_surah` VARCHAR(100) NULL,
            `tilawah_duration_seconds` INT DEFAULT 0,
            `dzikir_istighfar` TINYINT(1) DEFAULT 0,
            `dzikir_sholawat` TINYINT(1) DEFAULT 0,
            `tadabbur_checked` TINYINT(1) DEFAULT 0,
            `tadabbur_notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_guru_tanggal` (`guru_id`, `tanggal`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 6. Pencatatan Keterlambatan Siswa
    'keterlambatan_siswa' => "
        CREATE TABLE IF NOT EXISTS `keterlambatan_siswa` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `kode_keterlambatan` VARCHAR(30) UNIQUE NOT NULL,
            `guru_pencatat_id` INT NOT NULL DEFAULT 1,
            `nama_siswa` VARCHAR(100) NOT NULL,
            `nisn` VARCHAR(30) NULL,
            `kelas` VARCHAR(50) NOT NULL,
            `tanggal` DATE NOT NULL,
            `jam_kedatangan` TIME NOT NULL,
            `menit_terlambat` INT NOT NULL DEFAULT 15,
            `alasan` VARCHAR(255) NOT NULL,
            `tindakan_pembinaan` VARCHAR(255) NOT NULL,
            `catatan` TEXT NULL,
            `status` ENUM('Selesai Dibina', 'Peringatan', 'Panggilan Ortu') DEFAULT 'Selesai Dibina',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`guru_pencatat_id`),
            INDEX (`tanggal`),
            INDEX (`kelas`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 7. Pengajuan Izin Guru (Full Day & Keluar Mengajar)
    'izin_guru' => "
        CREATE TABLE IF NOT EXISTS `izin_guru` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nomor_izin` VARCHAR(50) UNIQUE NOT NULL,
            `guru_id` INT NOT NULL DEFAULT 1,
            `jenis_izin` ENUM('tidak_masuk', 'keluar_mengajar') NOT NULL DEFAULT 'tidak_masuk',
            `kategori` ENUM('Sakit', 'Dinas Luar', 'Keperluan Keluarga', 'Lainnya') DEFAULT 'Sakit',
            `tanggal_mulai` DATE NOT NULL,
            `tanggal_selesai` DATE NOT NULL,
            `jam_keluar` TIME NULL,
            `jam_kembali` TIME NULL,
            `kelas_terdampak` VARCHAR(255) NULL,
            `tugas_siswa` TEXT NULL,
            `guru_pengganti` VARCHAR(100) NULL,
            `alasan` TEXT NOT NULL,
            `lampiran_dokumen` VARCHAR(255) NULL,
            `status` ENUM('Menunggu Persetujuan', 'Disetujui', 'Ditolak') DEFAULT 'Menunggu Persetujuan',
            `catatan_pimpinan` TEXT NULL,
            `disetujui_oleh` VARCHAR(100) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`guru_id`),
            INDEX (`tanggal_mulai`),
            INDEX (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 8. Pengajuan Cuti Guru
    'cuti_guru' => "
        CREATE TABLE IF NOT EXISTS `cuti_guru` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nomor_cuti` VARCHAR(50) UNIQUE NOT NULL,
            `guru_id` INT NOT NULL DEFAULT 1,
            `jenis_cuti` ENUM('Cuti Tahunan', 'Cuti Sakit', 'Cuti Alasan Penting', 'Cuti Melahirkan', 'Cuti Ibadah Keagamaan', 'Lainnya') NOT NULL DEFAULT 'Cuti Tahunan',
            `tanggal_mulai` DATE NOT NULL,
            `tanggal_selesai` DATE NOT NULL,
            `jumlah_hari` INT NOT NULL DEFAULT 1,
            `alasan` TEXT NOT NULL,
            `kontak_darurat` VARCHAR(100) NULL,
            `alamat_cuti` TEXT NULL,
            `guru_pengganti` VARCHAR(150) NULL,
            `lampiran_dokumen` VARCHAR(255) NULL,
            `status` ENUM('Diajukan', 'Verifikasi Wakasek', 'Disetujui Kepala Sekolah', 'Ditolak', 'Selesai') DEFAULT 'Diajukan',
            `catatan_pimpinan` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`guru_id`),
            INDEX (`tanggal_mulai`),
            INDEX (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 9. Kuota Cuti Guru
    'kuota_cuti_guru' => "
        CREATE TABLE IF NOT EXISTS `kuota_cuti_guru` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `guru_id` INT NOT NULL DEFAULT 1,
            `tahun` YEAR NOT NULL,
            `kuota_tahunan` INT DEFAULT 12,
            `terpakai_tahunan` INT DEFAULT 2,
            `sisa_tahunan` INT DEFAULT 10,
            `kuota_sakit` INT DEFAULT 14,
            `kuota_alasan_penting` INT DEFAULT 5,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_guru_tahun` (`guru_id`, `tahun`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",

    // 10. Quran Bookmark Guru (with precise timestamp)
    'quran_bookmark_guru' => "
        CREATE TABLE IF NOT EXISTS `quran_bookmark_guru` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `guru_id` INT NOT NULL DEFAULT 1,
            `surah_nomor` INT NOT NULL,
            `surah_nama` VARCHAR(100) NOT NULL,
            `ayat_nomor` INT NOT NULL,
            `ditandai_pada` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_bookmark_guru` (`guru_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    "
];

// Execute Migrations
$results = [];
foreach ($migrations as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        $results[$tableName] = ['status' => 'success', 'msg' => 'Tabel berhasil dibuat / diverifikasi'];
    } catch (PDOException $e) {
        $results[$tableName] = ['status' => 'error', 'msg' => $e->getMessage()];
    }
}

// Optional: Seed Sample / Default Data
try {
    // Seed Kuota Cuti if empty
    $check = $pdo->query("SELECT COUNT(*) FROM `kuota_cuti_guru` WHERE `guru_id` = 1 AND `tahun` = 2026")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO `kuota_cuti_guru` (`guru_id`, `tahun`, `kuota_tahunan`, `terpakai_tahunan`, `sisa_tahunan`, `kuota_sakit`, `kuota_alasan_penting`) VALUES (1, 2026, 12, 2, 10, 14, 5);");
    }

    // Seed Sample Keterlambatan if empty
    $checkLate = $pdo->query("SELECT COUNT(*) FROM `keterlambatan_siswa`")->fetchColumn();
    if ($checkLate == 0) {
        $pdo->exec("
            INSERT INTO `keterlambatan_siswa` (`kode_keterlambatan`, `guru_pencatat_id`, `nama_siswa`, `nisn`, `kelas`, `tanggal`, `jam_kedatangan`, `menit_terlambat`, `alasan`, `tindakan_pembinaan`, `status`) VALUES
            ('TRL-001', 1, 'Ahmad Fadhil', '0082194821', 'Kelas 7A', CURDATE(), '07:25:00', 25, 'Macet Lalu Lintas di Flyover', 'Teguran Lisan & Pembacaan Doa', 'Selesai Dibina'),
            ('TRL-002', 1, 'Bima Pratama', '0083921849', 'Kelas 8B', CURDATE(), '07:15:00', 15, 'Kendaraan Ban Bocor', 'Pencatatan di Buku Piket', 'Selesai Dibina'),
            ('TRL-003', 1, 'Chandra Wijaya', '0084920194', 'Kelas 8A', CURDATE(), '07:40:00', 40, 'Bangun Kesiangan', 'Tugas Tambahan & Notifikasi Ortu', 'Peringatan');
        ");
    }
} catch (Throwable $e) {
    // Ignore seed errors if duplicates
}

// ── Output Rendering ────────────────────────────────────────
if ($isCli) {
    echo "\n========================================================\n";
    echo "  🚀 PORTAL BIP - MOBILE DATABASE MIGRATION ENGINE\n";
    echo "========================================================\n\n";
    echo "Target Database: " . DB_NAME . " (" . DB_HOST . ":" . DB_PORT . ")\n\n";

    $successCount = 0;
    foreach ($results as $table => $res) {
        if ($res['status'] === 'success') {
            $successCount++;
            echo " [✓ OK] Table `$table` -> {$res['msg']}\n";
        } else {
            echo " [✗ FAIL] Table `$table` -> {$res['msg']}\n";
        }
    }

    echo "\n--------------------------------------------------------\n";
    echo "  Selesai: $successCount/" . count($results) . " tabel berhasil dimigrasi.\n";
    echo "========================================================\n\n";

} else {
    // Beautiful HTML Web UI
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mobile Database Migration - Portal BIP</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; }
            code, .font-mono { font-family: 'JetBrains Mono', monospace; }
        </style>
    </head>
    <body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
        
        <div class="max-w-xl w-full bg-slate-800 rounded-3xl p-6 shadow-2xl border border-slate-700 space-y-5">
            
            <!-- Header -->
            <div class="flex items-center gap-3 pb-4 border-b border-slate-700">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-2xl">
                    🚀
                </div>
                <div>
                    <h1 class="text-lg font-black text-white">Mobile Database Migration</h1>
                    <p class="text-xs text-slate-400">Database: <code class="text-emerald-400 font-bold"><?= DB_NAME ?></code></p>
                </div>
            </div>

            <!-- Migration Results -->
            <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                <?php foreach ($results as $table => $res): ?>
                    <div class="flex items-center justify-between p-3 rounded-2xl <?= $res['status'] === 'success' ? 'bg-emerald-950/40 border border-emerald-800/40 text-emerald-300' : 'bg-rose-950/40 border border-rose-800/40 text-rose-300' ?> text-xs font-mono">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full <?= $res['status'] === 'success' ? 'bg-emerald-500 text-slate-950' : 'bg-rose-500 text-white' ?> font-black flex items-center justify-center text-[10px]">
                                <?= $res['status'] === 'success' ? '✓' : '✗' ?>
                            </span>
                            <span class="font-bold text-white"><?= $table ?></span>
                        </div>
                        <span class="text-[11px] font-sans text-slate-400"><?= $res['msg'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary & Actions -->
            <div class="pt-3 border-t border-slate-700 flex items-center justify-between text-xs">
                <span class="text-slate-400">Status: <strong class="text-emerald-400 font-bold"><?= count($results) ?>/<?= count($results) ?> Tabel Siap</strong></span>
                <a href="../mobile" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-all shadow-md">
                    Buka Aplikasi Mobile &rarr;
                </a>
            </div>

        </div>

    </body>
    </html>
    <?php
}
