<?php
/**
 * Migration: Modul Kelola Sarpras (Sarana & Prasarana)
 * 
 * Group: "Sarana & Prasarana"
 * Tables:
 * 1. sarpras_kategori
 * 2. sarpras_ruangan
 * 3. sarpras_barang
 * 4. sarpras_peminjaman
 * 5. sarpras_pemeliharaan
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__ . '/..'));
}
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== 1. Membuat Tabel-Tabel Sarpras ===\n";

    // 1. Kategori Sarpras
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_kategori` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `kode_kategori` VARCHAR(20) NOT NULL UNIQUE,
            `nama_kategori` VARCHAR(100) NOT NULL,
            `deskripsi` TEXT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel 'sarpras_kategori' siap.\n";

    // 2. Ruangan / Lokasi
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
    echo "✓ Tabel 'sarpras_ruangan' siap.\n";

    // 3. Aset & Barang
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_barang` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `kode_barang` VARCHAR(50) NOT NULL UNIQUE,
            `nama_barang` VARCHAR(200) NOT NULL,
            `kategori_id` INT NOT NULL,
            `ruangan_id` INT NOT NULL,
            `unit` VARCHAR(50) NOT NULL DEFAULT 'SD',
            `merk_model` VARCHAR(150) DEFAULT NULL,
            `nomor_seri` VARCHAR(100) DEFAULT NULL,
            `jumlah` INT NOT NULL DEFAULT 1,
            `satuan` VARCHAR(30) NOT NULL DEFAULT 'Unit',
            `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
            `status` ENUM('Tersedia', 'Dipinjam', 'Dalam Perbaikan', 'Dihapuskan') NOT NULL DEFAULT 'Tersedia',
            `sumber_dana` VARCHAR(100) DEFAULT 'Yayasan / BOS',
            `tahun_pengadaan` VARCHAR(4) DEFAULT NULL,
            `harga_perolehan` DECIMAL(15,2) DEFAULT 0.00,
            `foto` VARCHAR(255) DEFAULT NULL,
            `keterangan` TEXT DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`kategori_id`),
            INDEX (`ruangan_id`),
            INDEX (`unit`),
            INDEX (`kondisi`),
            INDEX (`status`),
            INDEX (`nama_barang`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel 'sarpras_barang' siap.\n";

    // 4. Sirkulasi Peminjaman Sarpras
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
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`barang_id`),
            INDEX (`status`),
            INDEX (`tanggal_pinjam`),
            INDEX (`estimasi_kembali`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel 'sarpras_peminjaman' siap.\n";

    // 5. Pemeliharaan & Perbaikan Sarpras
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
            `estimasi_biaya` DECIMAL(15,2) DEFAULT 0.00,
            `biaya_realisasi` DECIMAL(15,2) DEFAULT 0.00,
            `tanggal_selesai` DATE DEFAULT NULL,
            `teknisi_pihak` VARCHAR(150) DEFAULT NULL,
            `tindakan_perbaikan` TEXT DEFAULT NULL,
            `foto_kerusakan` VARCHAR(255) DEFAULT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`barang_id`),
            INDEX (`ruangan_id`),
            INDEX (`status`),
            INDEX (`tingkat_urgensi`),
            INDEX (`tanggal_lapor`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel 'sarpras_pemeliharaan' siap.\n";

    echo "=== 2. Mengisi Data Awal (Kategori & Ruangan) ===\n";

    // Seed Kategori jika kosong
    $katCount = $pdo->query("SELECT COUNT(*) FROM sarpras_kategori")->fetchColumn();
    if ((int)$katCount === 0) {
        $defaultKategori = [
            ['ELK', 'Elektronik & Multimedia', 'Laptop, PC, Proyektor, Sound System, Televisi, Kamera, Printer'],
            ['MBL', 'Meubeler & Furnitur', 'Meja Guru, Kursi Siswa, Lemari Arsip, Rak Buku, Meja Rapat'],
            ['LAB', 'Peralatan Lab & Sains', 'Mikroskop, Alat Peraga Anatomi, Tabung Kimia, Kit Fisika'],
            ['OLG', 'Sarana Olahraga', 'Bola Futsal, Bola Basket, Matras Senam, Net Voli, Raket Badminton'],
            ['IBD', 'Sarana Ibadah & Dakwah', 'Karpet Masjid, Sound Mushola, Al-Qur\'an Mushaf, Mukena, Mimbar'],
            ['KLS', 'Perlengkapan Kelas', 'Papan Tulis Whiteboard, AC Split, Kipas Angin, Jam Dinding, Dispenser'],
            ['KBR', 'Alat Kebersihan & Perawatan', 'Mesin Pemotong Rumput, Vacum Cleaner, Tangga Lipat, Toolset']
        ];
        $stmtKat = $pdo->prepare("INSERT INTO sarpras_kategori (kode_kategori, nama_kategori, deskripsi) VALUES (?, ?, ?)");
        foreach ($defaultKategori as $k) {
            $stmtKat->execute($k);
        }
        echo "✓ Seed 7 Kategori Sarpras berhasil.\n";
    }

    // Catatan: Seeding dummy data (ruangan, barang, peminjaman, pemeliharaan) dinonaktifkan
    // agar data sarpras selalu fresh dan bersih untuk data riil sekolah.

    echo "=== 3. Mendaftarkan Modul di Tabel modules ===\n";
    $modSlug = 'kelola-sarpras';
    $modExists = $pdo->query("SELECT id FROM modules WHERE slug = '$modSlug'")->fetch();

    $iconSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>';

    if (!$modExists) {
        $stmtM = $pdo->prepare("
            INSERT INTO modules (name, slug, description, module_group, icon_svg, color, route, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmtM->execute([
            'Kelola Sarpras',
            'kelola-sarpras',
            'Manajemen aset sarana dan prasarana, fasilitas ruangan, peminjaman barang, dan pemeliharaan',
            'Sarana & Prasarana',
            $iconSvg,
            '#0D9488',
            '/kelola-sarpras',
            23
        ]);
        $moduleId = $pdo->lastInsertId();
        echo "✓ Modul 'Kelola Sarpras' berhasil didaftarkan (ID: $moduleId, Group: 'Sarana & Prasarana').\n";
    } else {
        $moduleId = $modExists['id'];
        $pdo->exec("
            UPDATE modules 
            SET name = 'Kelola Sarpras',
                module_group = 'Sarana & Prasarana',
                description = 'Manajemen aset sarana dan prasarana, fasilitas ruangan, peminjaman barang, dan pemeliharaan',
                route = '/kelola-sarpras',
                icon_svg = '$iconSvg',
                color = '#0D9488',
                is_active = 1
            WHERE id = $moduleId
        ");
        echo "✓ Modul 'Kelola Sarpras' diperbarui (ID: $moduleId, Group: 'Sarana & Prasarana').\n";
    }

    echo "=== 4. Mendaftarkan Permissions RBAC ===\n";
    $permissions = [
        ['sarpras.view', 'Lihat Sarpras', 'Melihat dashboard, inventaris barang, ruangan, peminjaman, dan laporan perbaikan'],
        ['sarpras.create', 'Tambah Sarpras', 'Menambah aset baru, ruangan, transaksi peminjaman, dan pengaduan perbaikan'],
        ['sarpras.update', 'Edit Sarpras', 'Mengubah data aset, pengembalian barang, dan update status perbaikan'],
        ['sarpras.delete', 'Hapus Sarpras', 'Menghapus data aset, ruangan, atau riwayat sarpras'],
        ['sarpras.export', 'Ekspor & Cetak Sarpras', 'Mengekspor rekapitulasi inventaris ke Excel dan mencetak label QR/Barcode'],
        ['sarpras.mobile', 'Akses Mobile Sarpras', 'Dapat mengakses dan menggunakan aplikasi PWA Mobile Sarpras'],
        ['sarpras.approve', 'Persetujuan Sarpras', 'Dapat menyetujui peminjaman dan permohonan perbaikan sarpras'],
    ];

    $stmtP = $pdo->prepare("INSERT INTO permissions (name, slug, description, module_id) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), module_id = VALUES(module_id)");
    foreach ($permissions as $p) {
        $stmtP->execute([$p[1], $p[0], $p[2], $moduleId]);
    }
    echo "✓ 5 Hak Akses RBAC Sarpras berhasil didaftarkan.\n";

    echo "=== 5. Memberikan Hak Akses ke Super Admin & Admin ===\n";
    // Ambil permission IDs
    $permSlugs = array_column($permissions, 0);
    $inClause = "'" . implode("','", $permSlugs) . "'";
    $permRows = $pdo->query("SELECT id FROM permissions WHERE slug IN ($inClause)")->fetchAll(PDO::FETCH_COLUMN);

    // Module Permissions
    $stmtMP = $pdo->prepare("INSERT IGNORE INTO module_permissions (module_id, permission_id) VALUES (?, ?)");
    foreach ($permRows as $pid) {
        $stmtMP->execute([$moduleId, $pid]);
    }

    // Role Permissions untuk Super Admin (1) & Admin (2)
    $stmtRP = $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ([1, 2] as $roleId) {
        foreach ($permRows as $pid) {
            $stmtRP->execute([$roleId, $pid]);
        }
    }
    echo "✓ Izin RBAC berhasil dihubungkan ke Super Admin & Admin.\n";

    echo "\n🎉 MIGRASI MODUL KELOLA SARPRAS SELESAI DENGAN SUKSES! 🎉\n";

} catch (Exception $e) {
    die("\n❌ Terjadi kesalahan: " . $e->getMessage() . "\n");
}
