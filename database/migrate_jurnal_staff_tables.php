<?php
/**
 * Migration: Modul Kelola Jurnal Staff
 * Membuat tabel:
 * 1. jurnal_staff_jam (Pengaturan Jam / Sesi: 07.00 - 08.00 Tilawah, 08.00 - 09.00 Aktifitas, dll)
 * 2. jurnal_staff_tugas (Pengaturan Tugas / Job Desk per Jabatan referensi)
 * 3. jurnal_staff (Data Jurnal Harian Staff)
 * 4. jurnal_staff_pengaturan (Pengaturan Periode Rekap)
 * Dan mendaftarkan modul ke tabel `modules` serta RBAC permissions.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$db = Database::getInstance();

echo "Memulai migrasi Modul Kelola Jurnal Staff...\n";

// 1. Tabel jurnal_staff_jam
$db->query("
CREATE TABLE IF NOT EXISTS `jurnal_staff_jam` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NOT NULL,
  `nama_sesi` VARCHAR(100) NOT NULL,
  `tipe` ENUM('tilawah', 'aktifitas', 'istirahat', 'lainnya') NOT NULL DEFAULT 'aktifitas',
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `urutan` INT(11) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Tabel jurnal_staff_jam siap.\n";

// 2. Tabel jurnal_staff_tugas
$db->query("
CREATE TABLE IF NOT EXISTS `jurnal_staff_tugas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `jabatan_id` INT(11) NOT NULL,
  `nama_jabatan` VARCHAR(100) NOT NULL,
  `nama_tugas` VARCHAR(255) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  `kategori_tugas` ENUM('rutin', 'berkala', 'insidental') NOT NULL DEFAULT 'rutin',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_jabatan` (`jabatan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Tabel jurnal_staff_tugas siap.\n";

// 3. Tabel jurnal_staff
$db->query("
CREATE TABLE IF NOT EXISTS `jurnal_staff` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_jurnal` VARCHAR(35) NOT NULL,
  `pegawai_id` INT(11) NOT NULL,
  `nama_staff` VARCHAR(150) NOT NULL,
  `jabatan` VARCHAR(100) NOT NULL,
  `unit_tugas` VARCHAR(100) DEFAULT NULL,
  `tanggal` DATE NOT NULL,
  `jam_id` INT(11) DEFAULT NULL,
  `jam_rentang` VARCHAR(50) NOT NULL,
  `tugas_id` INT(11) DEFAULT NULL,
  `aktivitas` VARCHAR(255) NOT NULL,
  `uraian_hasil` TEXT NOT NULL,
  `kendala` TEXT DEFAULT NULL,
  `foto_bukti` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('selesai', 'proses', 'tertunda') NOT NULL DEFAULT 'selesai',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_kode` (`kode_jurnal`),
  KEY `idx_pegawai_tgl` (`pegawai_id`, `tanggal`),
  KEY `idx_tanggal` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Tabel jurnal_staff siap.\n";

// 4. Tabel jurnal_staff_pengaturan
$db->query("
CREATE TABLE IF NOT EXISTS `jurnal_staff_pengaturan` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `periode_awal` DATE DEFAULT NULL,
  `periode_akhir` DATE DEFAULT NULL,
  `keterangan` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Tabel jurnal_staff_pengaturan siap.\n";

// Seed Pengaturan Periode Default jika belum ada
$cfg = $db->find("SELECT id FROM jurnal_staff_pengaturan LIMIT 1");
if (!$cfg) {
    $db->query("
        INSERT INTO jurnal_staff_pengaturan (periode_awal, periode_akhir, keterangan)
        VALUES (?, ?, 'Default Periode Rekap Jurnal Staff')
    ", [date('Y-m-01'), date('Y-m-t')]);
}

// Seed default jam kerja jika masih kosong
$jamCount = $db->find("SELECT COUNT(*) as c FROM jurnal_staff_jam");
if (($jamCount['c'] ?? 0) == 0) {
    $defaultJams = [
        ['07:00:00', '08:00:00', 'Tilawah & Dzikir Pagi', 'tilawah', 'Membaca Al-Qur\'an, Dzikir Pagi Al-Ma\'tsurat, dan briefing pagi', 1],
        ['08:00:00', '09:00:00', 'Aktifitas Kerja Sesi 1', 'aktifitas', 'Pelaksanaan tugas utama / job desk divisi', 2],
        ['09:00:00', '10:00:00', 'Aktifitas Kerja Sesi 2', 'aktifitas', 'Pelaksanaan tugas utama / job desk divisi', 3],
        ['10:00:00', '11:00:00', 'Aktifitas Kerja Sesi 3', 'aktifitas', 'Pelaksanaan tugas utama / job desk divisi', 4],
        ['11:00:00', '12:00:00', 'Aktifitas Kerja Sesi 4', 'aktifitas', 'Pelaksanaan tugas operasional divisi', 5],
        ['12:00:00', '13:00:00', 'Istirahat & Sholat Dzuhur', 'istirahat', 'ISHOMA (Istirahat, Sholat, Makan Siang)', 6],
        ['13:00:00', '14:00:00', 'Aktifitas Kerja Sesi 5', 'aktifitas', 'Tugas tindak lanjut dan koordinasi tim', 7],
        ['14:00:00', '15:00:00', 'Aktifitas Kerja Sesi 6', 'aktifitas', 'Penyelesaian tugas harian operasional', 8],
        ['15:00:00', '16:00:00', 'Evaluasi & Laporan Harian', 'aktifitas', 'Pencatatan laporan hasil kerja dan evaluasi harian', 9],
    ];

    foreach ($defaultJams as $j) {
        $db->query("
            INSERT INTO jurnal_staff_jam (jam_mulai, jam_selesai, nama_sesi, tipe, keterangan, urutan, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ", $j);
    }
    echo "✓ Default slot jam kerja staff berhasil di-seed (9 sesi).\n";
}

// Seed default tugas per jabatan referensi
$tugasCount = $db->find("SELECT COUNT(*) as c FROM jurnal_staff_tugas");
if (($tugasCount['c'] ?? 0) == 0) {
    // Ambil jabatan yang ada di master_jabatan
    $jabatanList = $db->findAll("SELECT id, nama FROM master_jabatan");
    $jabMap = [];
    foreach ($jabatanList as $jb) {
        $jabMap[strtolower(trim($jb['nama']))] = (int)$jb['id'];
    }

    $defaultTasks = [
        // IT & Media
        ['Kepala Divisi Media dan IT', 'Maintenance Server, Jaringan & WiFi Sekolah', 'Memastikan infrastruktur internet dan server sekolah beroperasi optimal', 'rutin'],
        ['Kepala Divisi Media dan IT', 'Backup Database & Monitoring Server Portal BIP', 'Melakukan backup berkala dan memantau kinerja portal', 'rutin'],
        ['Kepala Divisi Media dan IT', 'Troubleshooting Hardware, Komputer & Proyektor', 'Perbaikan dan pemeliharaan perangkat IT unit sekolah', 'rutin'],
        ['Kepala Divisi Media dan IT', 'Desain Konten Media Sosial & Publikasi Digital', 'Pembuatan flyer, video dokumentasi, dan posting info sekolah', 'berkala'],
        ['Kepala Divisi Media dan IT', 'Liputan & Dokumentasi Kegiatan Sekolah', 'Pengambilan foto/video liputan kegiatan harian & event', 'insidental'],

        // Manajer Divisi Bisnis
        ['Manajer Divisi Bisnis', 'Pencatatan Transaksi & Rekapitulasi Kas Unit Usaha', 'Pencatatan arus kas dan pembukuan harian', 'rutin'],
        ['Manajer Divisi Bisnis', 'Pengecekan Stok & Pengadaan Inventaris Barang', 'Kontrol barang masuk, keluar, dan sisa stok', 'rutin'],
        ['Manajer Divisi Bisnis', 'Monitoring Layanan Kantin & Koperasi Sekolah', 'Pengawasan kebersihan dan operasional layanan', 'rutin'],

        // Manajer Divisi Sosial
        ['Manajer Divisi Sosial', 'Pengelolaan Penyaluran Program Sosial & Ziswaf', 'Koordinasi data penerima manfaat dan donatur', 'rutin'],
        ['Manajer Divisi Sosial', 'Layanan Komunikasi & Kemitraan Eksternal', 'Hubungan masyarakat dan kemitraan lembaga', 'berkala'],
    ];

    foreach ($defaultTasks as $dt) {
        $jName = $dt[0];
        $jId = $jabMap[strtolower(trim($jName))] ?? 0;
        if ($jId === 0) {
            // Coba cari LIKE
            foreach ($jabMap as $k => $id) {
                if (stripos($k, 'it') !== false && stripos($jName, 'it') !== false) {
                    $jId = $id;
                    break;
                }
            }
        }
        if ($jId > 0) {
            $db->query("
                INSERT INTO jurnal_staff_tugas (jabatan_id, nama_jabatan, nama_tugas, deskripsi, kategori_tugas, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ", [$jId, $jName, $dt[1], $dt[2], $dt[3]]);
        }
    }
    echo "✓ Default tugas per jabatan berhasil di-seed.\n";
}

// 5. Registrasi Modul ke Tabel `modules`
$mod = $db->find("SELECT id FROM modules WHERE slug = 'kelola-jurnal-staff'");
if (!$mod) {
    $db->query("
        INSERT INTO modules (name, slug, description, module_group, icon_svg, color, route, sort_order, is_active)
        VALUES (
            'Kelola Jurnal Staff',
            'kelola-jurnal-staff',
            'Monitoring jurnal aktivitas harian staff, pengaturan jam sesi kerja, dan daftar tugas per jabatan / divisi',
            'Kepegawaian',
            '<svg xmlns=\"http://www.w3.org/2000/svg\" fill=\"none\" viewBox=\"0 0 24 24\" stroke-width=\"1.5\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z\" /></svg>',
            '#6366F1',
            '/kelola-jurnal-staff',
            14,
            1
        )
    ");
    $moduleId = (int)$db->lastInsertId();
    echo "✓ Modul Kelola Jurnal Staff berhasil didaftarkan (ID: $moduleId).\n";
} else {
    $moduleId = (int)$mod['id'];
    echo "✓ Modul Kelola Jurnal Staff sudah ada (ID: $moduleId).\n";
}

// 6. Registrasi Permissions ke Tabel `permissions` & `role_permissions`
$perms = [
    ['jurnal_staff.view', 'Lihat Jurnal Staff', 'Melihat dashboard, rekap data jurnal kerja, dan jadwal staff'],
    ['jurnal_staff.manage', 'Kelola Jurnal Staff', 'Menambah, mengedit, memvalidasi jurnal, serta mengatur jam dan tugas jabatan'],
    ['jurnal_staff.delete', 'Hapus Jurnal Staff', 'Menghapus catatan jurnal kerja staff, jam sesi, dan tugas'],
];

foreach ($perms as $p) {
    $existing = $db->find("SELECT id FROM permissions WHERE slug = ?", [$p[0]]);
    if (!$existing) {
        $db->query("
            INSERT INTO permissions (name, slug, description, module_id)
            VALUES (?, ?, ?, ?)
        ", [$p[1], $p[0], $p[2], $moduleId]);
        $pId = (int)$db->lastInsertId();
        echo "✓ Permission {$p[0]} ditambahkan.\n";
    } else {
        $pId = (int)$existing['id'];
    }

    // Berikan hak akses ke Super Admin (1), Admin (2), dan Operator (3)
    foreach ([1, 2, 3] as $roleId) {
        if ($roleId === 3 && $p[0] === 'jurnal_staff.delete') {
            continue; // Operator tidak diberi delete
        }
        $hasRole = $db->find("SELECT * FROM role_permissions WHERE role_id = ? AND permission_id = ?", [$roleId, $pId]);
        if (!$hasRole) {
            $db->query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$roleId, $pId]);
        }
    }
}
echo "✓ Hak akses permissions berhasil dikonfigurasi.\n";

echo "Migrasi Modul Kelola Jurnal Staff SELESAI DENGAN SUKSES! 🎉\n";
