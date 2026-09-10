<?php
/**
 * Migration Script: RBAC Granular Permissions & Approval Checkboxes
 * 
 * Sets up permissions for all 23 active modules in Portal BIP,
 * including specialized 'approve' permissions for modules with approval workflows.
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

echo "=== Memulai Migrasi Hak Akses RBAC & Approval ===\n";

$db = Database::getInstance();
$pdo = $db->getConnection();

// Mapping of modules and their permissions
// Format: slug => [ 'module_slug' => ..., 'permissions' => [ [slug, name, description, is_approve], ... ] ]
$modulesDefinition = [
    'dashboard' => [
        'name' => 'Dashboard',
        'permissions' => [
            ['dashboard.view', 'Lihat Dashboard', 'Mengakses halaman utama dashboard dan statistik umum', false],
        ]
    ],
    'kelola-siswa' => [
        'name' => 'Kelola Siswa',
        'permissions' => [
            ['siswa.view', 'Lihat Siswa', 'Melihat daftar, detail, buku induk, dan data siswa', false],
            ['siswa.create', 'Tambah Siswa', 'Menambah data siswa baru, prestasi, dan mutasi masuk', false],
            ['siswa.update', 'Edit Siswa', 'Memperbarui data profil, foto, dan status siswa', false],
            ['siswa.delete', 'Hapus Siswa', 'Menghapus data siswa atau riwayat prestasi siswa', false],
            ['siswa.export', 'Ekspor Siswa', 'Mengekspor data siswa, buku induk, dan cetak kartu massal', false],
        ]
    ],
    'kelola-pegawai' => [
        'name' => 'Kelola Pegawai',
        'permissions' => [
            ['pegawai.view', 'Lihat Pegawai', 'Melihat daftar pegawai, guru, profil, dan statistik pegawai', false],
            ['pegawai.create', 'Tambah Pegawai', 'Menambah pegawai baru, riwayat karir, pelatihan, dan prestasi', false],
            ['pegawai.update', 'Edit Pegawai', 'Mengubah data pegawai, update status, dan karir', false],
            ['pegawai.delete', 'Hapus Pegawai', 'Menghapus data pegawai atau riwayat karir/pelatihan', false],
            ['pegawai.export', 'Ekspor & Cetak Pegawai', 'Mengekspor excel data pegawai dan mencetak CV pegawai', false],
            ['pegawai.penugasan', 'Kelola Penugasan & SK', 'Mengatur grup SK penugasan dan pembagian tugas mengajar guru', false],
        ]
    ],
    'kelola-kelas' => [
        'name' => 'Kelola Kelas',
        'permissions' => [
            ['kelas.view', 'Lihat Kelas', 'Melihat daftar rombongan belajar dan kelas', false],
            ['kelas.create', 'Tambah Kelas', 'Menambah kelas baru dan salin data kelas', false],
            ['kelas.update', 'Edit Kelas', 'Mengubah wali kelas, tingkat, dan identitas kelas', false],
            ['kelas.delete', 'Hapus Kelas', 'Menghapus data kelas yang ada', false],
        ]
    ],
    'kelola-pengguna' => [
        'name' => 'Kelola Pengguna',
        'permissions' => [
            ['users.view', 'Lihat Pengguna', 'Melihat daftar akun pengguna portal', false],
            ['users.create', 'Tambah Pengguna', 'Membuat akun pengguna baru dan menetapkan peran awal', false],
            ['users.update', 'Edit Pengguna', 'Mengubah data akun, password, dan status aktif pengguna', false],
            ['users.delete', 'Hapus Pengguna', 'Menghapus akun pengguna dari sistem', false],
        ]
    ],
    'kelola-peran' => [
        'name' => 'Kelola Peran',
        'permissions' => [
            ['roles.view', 'Lihat Peran', 'Melihat daftar peran dan rincian hak akses peran', false],
            ['roles.create', 'Tambah Peran', 'Membuat peran baru dan mengonfigurasi hak akses', false],
            ['roles.update', 'Edit Peran', 'Mengubah nama peran dan matriks centang hak akses/approval', false],
            ['roles.delete', 'Hapus Peran', 'Menghapus peran kustom dari sistem', false],
        ]
    ],
    'manajemen-modul' => [
        'name' => 'Manajemen Modul',
        'permissions' => [
            ['modules.view', 'Lihat Modul', 'Melihat daftar modul sistem yang terpasang', false],
            ['modules.create', 'Tambah Modul', 'Mendaftarkan modul baru ke dalam portal', false],
            ['modules.update', 'Edit Modul', 'Mengubah rute, status aktif, dan konfigurasi modul', false],
            ['modules.delete', 'Hapus Modul', 'Menghapus modul dari portal', false],
        ]
    ],
    'pengaturan-sistem' => [
        'name' => 'Pengaturan Sistem',
        'permissions' => [
            ['settings.view', 'Lihat Pengaturan', 'Melihat pengaturan umum, tahun akademik, dan master data', false],
            ['settings.update', 'Ubah Pengaturan', 'Mengubah konfigurasi identitas sekolah, logo, dan mata pelajaran', false],
            ['settings.reset', 'Reset Data Sistem', 'Wewenang tingkat tinggi untuk mereset data operasional sistem', false],
        ]
    ],
    'kelola-perangkat-pembelajaran' => [
        'name' => 'Kelola Perangkat Pembelajaran',
        'permissions' => [
            ['perangkat.view', 'Lihat Perangkat & Jadwal', 'Melihat Kaldik, HEB/HES, CP & ATP, Prota, Prosem, RPP, dan Jadwal KBM', false],
            ['perangkat.create', 'Buat Perangkat & Jadwal', 'Membuat Kaldik, jadwal, CP&ATP, Prota, Prosem, dan RPP baru', false],
            ['perangkat.update', 'Edit Perangkat & Jadwal', 'Mengubah isi dokumen perangkat ajar dan konfigurasi JP/jadwal', false],
            ['perangkat.delete', 'Hapus Perangkat & Jadwal', 'Menghapus dokumen perangkat ajar, slot jadwal, atau agenda', false],
            ['perangkat.approve', 'Pusat Verifikasi / Pengesahan', 'Wewenang khusus menyetujui (Approve), menolak (Reject), dan mengesahkan RPP/Perangkat Ajar', true],
        ]
    ],
    'kelola-nilai' => [
        'name' => 'Kelola Nilai',
        'permissions' => [
            ['nilai.view', 'Lihat Nilai', 'Melihat rekap nilai akademik, formatif, dan sumatif siswa', false],
            ['nilai.create', 'Input Nilai', 'Menginput nilai tugas, ujian, dan asesmen siswa', false],
            ['nilai.update', 'Edit Nilai', 'Mengubah atau mengoreksi nilai siswa yang telah diinput', false],
            ['nilai.delete', 'Hapus Nilai', 'Menghapus data nilai ujian atau tugas siswa', false],
        ]
    ],
    'kelola-raport' => [
        'name' => 'Kelola Raport',
        'permissions' => [
            ['raport.view', 'Lihat Raport', 'Melihat leger dan lembaran raport siswa', false],
            ['raport.create', 'Generate Raport', 'Membuat atau meng-generate cetakan raport semester', false],
            ['raport.update', 'Edit Catatan Raport', 'Mengedit catatan wali kelas, kepribadian, dan capaian kompetensi', false],
            ['raport.delete', 'Hapus Raport', 'Menghapus draf raport yang telah dibuat', false],
            ['raport.approve', 'Pengesahan Raport', 'Wewenang verifikasi dan pengesahan resmi raport oleh Kepala Sekolah/Waka', true],
        ]
    ],
    'kelola-absen-siswa' => [
        'name' => 'Kelola Absen Siswa',
        'permissions' => [
            ['absen_siswa.view', 'Lihat Absen Siswa', 'Melihat rekap dan laporan kehadiran siswa harian/bulanan', false],
            ['absen_siswa.create', 'Input Absen Siswa', 'Melakukan presensi kehadiran siswa di kelas', false],
            ['absen_siswa.update', 'Edit Absen Siswa', 'Mengubah status kehadiran siswa (Sakit, Izin, Alpa)', false],
            ['absen_siswa.delete', 'Hapus Absen Siswa', 'Menghapus catatan kehadiran siswa tertentu', false],
        ]
    ],
    'kelola-absen-pegawai' => [
        'name' => 'Kelola Absen Pegawai',
        'permissions' => [
            ['absen_pegawai.view', 'Lihat Absen Pegawai', 'Melihat riwayat presensi harian, jam masuk/pulang, dan GPS pegawai', false],
            ['absen_pegawai.create', 'Input Presensi Pegawai', 'Mencatat presensi manual pegawai jika ada kendala mesin/aplikasi', false],
            ['absen_pegawai.update', 'Koreksi Absen Pegawai', 'Mengoreksi data jam atau status kehadiran pegawai', false],
            ['absen_pegawai.delete', 'Hapus Data Absen', 'Menghapus catatan log presensi pegawai', false],
            ['absen_pegawai.approve', 'Verifikasi Absen Pegawai', 'Wewenang memeriksa, memvalidasi, dan mengesahkan rekap bulanan absen pegawai', true],
        ]
    ],
    'kelola-quran-siswa' => [
        'name' => 'Kelola Qur\'an Siswa',
        'permissions' => [
            ['quran_siswa.view', 'Lihat Tahfidz Siswa', 'Melihat capaian hafalan dan tilawah Al-Qur\'an siswa', false],
            ['quran_siswa.create', 'Input Setoran Qur\'an', 'Mencatat setoran ziyadah dan muroja\'ah siswa', false],
            ['quran_siswa.update', 'Edit Capaian Qur\'an', 'Mengubah catatan tajwid, fashohah, dan halaman hafalan', false],
            ['quran_siswa.delete', 'Hapus Catatan Setoran', 'Menghapus rekaman setoran tahfidz siswa', false],
        ]
    ],
    'kelola-ujian' => [
        'name' => 'Kelola Ujian',
        'permissions' => [
            ['ujian.view', 'Lihat Ujian & CBT', 'Melihat daftar sesi ujian, jadwal tes, dan hasil tes', false],
            ['ujian.create', 'Buat Sesi & Bank Soal', 'Menambah bank soal, membuat sesi ujian CBT baru', false],
            ['ujian.update', 'Edit Sesi & Soal', 'Mengubah soal, durasi waktu tes, dan reset login peserta', false],
            ['ujian.delete', 'Hapus Soal / Ujian', 'Menghapus paket soal atau sesi ujian', false],
        ]
    ],
    'kelola-quran-pegawai' => [
        'name' => 'Kelola Qur\'an Pegawai',
        'permissions' => [
            ['quran_pegawai.view', 'Lihat Qur\'an Pegawai', 'Melihat target tilawah, one day one juz, dan khataman pegawai', false],
            ['quran_pegawai.create', 'Input Tilawah Pegawai', 'Mencatat setoran tilawah harian pegawai', false],
            ['quran_pegawai.update', 'Edit Tilawah Pegawai', 'Mengoreksi halaman dan juz tilawah pegawai', false],
            ['quran_pegawai.delete', 'Hapus Catatan Tilawah', 'Menghapus catatan tilawah pegawai', false],
        ]
    ],
    'kelola-cuti' => [
        'name' => 'Kelola Cuti',
        'permissions' => [
            ['cuti.view', 'Lihat Pengajuan Cuti', 'Melihat daftar dan riwayat permohonan cuti pegawai', false],
            ['cuti.create', 'Ajukan Cuti', 'Mengajukan surat permohonan cuti baru untuk pegawai', false],
            ['cuti.update', 'Edit Permohonan Cuti', 'Mengubah tanggal atau berkas pendukung permohonan cuti', false],
            ['cuti.delete', 'Batalkan / Hapus Cuti', 'Membatalkan atau menghapus permohonan cuti', false],
            ['cuti.approve', 'Persetujuan / Pengesahan Cuti', 'Wewenang khusus menyetujui atau menolak permohonan cuti pegawai', true],
        ]
    ],
    'kelola-kpi' => [
        'name' => 'Kelola KPI',
        'permissions' => [
            ['kpi.view', 'Lihat KPI', 'Melihat indikator kinerja, target capaian, dan evaluasi guru/pegawai', false],
            ['kpi.create', 'Input Target KPI', 'Menyusun indikator dan sasaran kinerja pegawai', false],
            ['kpi.update', 'Edit Sasaran KPI', 'Mengubah bobot dan deskripsi target kinerja', false],
            ['kpi.delete', 'Hapus Indikator KPI', 'Menghapus target atau indikator penilaian kinerja', false],
            ['kpi.approve', 'Penilaian & Pengesahan KPI', 'Wewenang memberikan skor evaluasi akhir dan mengesahkan rapor KPI', true],
        ]
    ],
    'kelola-ibadah-guru' => [
        'name' => 'Kelola Ibadah Guru',
        'permissions' => [
            ['ibadah_guru.view', 'Lihat Mutaba\'ah Ibadah', 'Melihat catatan ibadah yaumiyah guru (Shalat, Dhuha, Tahajjud, Shaum)', false],
            ['ibadah_guru.create', 'Input Ibadah Harian', 'Mengisi checklist mutaba\'ah ibadah harian', false],
            ['ibadah_guru.update', 'Edit Catatan Ibadah', 'Mengoreksi laporan mutaba\'ah yang telah diinput', false],
            ['ibadah_guru.delete', 'Hapus Catatan Ibadah', 'Menghapus rekaman mutaba\'ah harian', false],
            ['ibadah_guru.approve', 'Verifikasi Ibadah Guru', 'Wewenang verifikasi dan evaluasi capaian mutaba\'ah ibadah guru', true],
        ]
    ],
    'kelola-spmb' => [
        'name' => 'Kelola SPMB',
        'permissions' => [
            ['spmb.view', 'Lihat Pendaftar SPMB', 'Melihat daftar calon peserta didik baru dan data pendaftaran', false],
            ['spmb.create', 'Daftarkan Calon Siswa', 'Menginput pendaftar baru secara manual melalui admin', false],
            ['spmb.update', 'Edit Berkas & Data SPMB', 'Memperbarui data dan verifikasi kelengkapan dokumen pendaftar', false],
            ['spmb.delete', 'Hapus Data Pendaftar', 'Menghapus data pendaftaran calon siswa', false],
            ['spmb.approve', 'Verifikasi & Kelulusan SPMB', 'Wewenang menentukan dan mengesahkan status kelulusan/diterima SPMB', true],
        ]
    ],
    'kelola-pelanggaran-siswa' => [
        'name' => 'Kelola Pelanggaran Siswa',
        'permissions' => [
            ['pelanggaran_siswa.view', 'Lihat Pelanggaran & Poin', 'Melihat rekam jejak poin disiplin dan pelanggaran siswa', false],
            ['pelanggaran_siswa.create', 'Input Pelanggaran', 'Mencatat pelanggaran tata tertib dan poin kedisiplinan', false],
            ['pelanggaran_siswa.update', 'Edit Catatan Pelanggaran', 'Mengubah rincian kejadian, sanksi, atau pembinaan', false],
            ['pelanggaran_siswa.delete', 'Hapus Catatan Pelanggaran', 'Menghapus catatan pelanggaran siswa', false],
        ]
    ],
    'kelola-izin-guru' => [
        'name' => 'Kelola Izin Guru',
        'permissions' => [
            ['izin_guru.view', 'Lihat Izin Guru', 'Melihat daftar permohonan izin tidak hadir / dispensasi guru', false],
            ['izin_guru.create', 'Ajukan Izin', 'Mengajukan surat permohonan izin dispensasi mengajar', false],
            ['izin_guru.update', 'Edit Permohonan Izin', 'Mengubah surat keterangan atau tanggal izin', false],
            ['izin_guru.delete', 'Batalkan / Hapus Izin', 'Membatalkan permohonan izin yang diajukan', false],
            ['izin_guru.approve', 'Persetujuan Izin Guru', 'Wewenang menyetujui atau menolak izin dispensasi kehadiran guru', true],
        ]
    ],
    'kelola-buku-angkatan-siswa' => [
        'name' => 'Kelola Buku Angkatan Siswa',
        'permissions' => [
            ['buku_angkatan.view', 'Lihat Buku Angkatan', 'Melihat yearbook, direktori alumni, dan foto angkatan siswa', false],
            ['buku_angkatan.create', 'Tambah Buku Angkatan', 'Menambah profil alumni, kutipan, dan foto buku tahunan', false],
            ['buku_angkatan.update', 'Edit Buku Angkatan', 'Mengubah konten dan biodata alumni pada buku angkatan', false],
            ['buku_angkatan.delete', 'Hapus Buku Angkatan', 'Menghapus data profil atau buku angkatan', false],
        ]
    ]
];

// Fetch all modules from DB to get exact IDs
$dbModules = $db->findAll("SELECT id, slug, name FROM modules");
$moduleMap = [];
foreach ($dbModules as $m) {
    $moduleMap[$m['slug']] = (int)$m['id'];
}

$allPermIds = [];
$adminPermIds = [];
$operatorPermIds = [];
$viewerPermIds = [];

$insertedCount = 0;
$updatedCount = 0;

foreach ($modulesDefinition as $modSlug => $modDef) {
    if (!isset($moduleMap[$modSlug])) {
        echo "[Peringatan] Modul {$modSlug} tidak ditemukan di tabel modules, dilewati.\n";
        continue;
    }

    $moduleId = $moduleMap[$modSlug];

    foreach ($modDef['permissions'] as $p) {
        $pSlug = $p[0];
        $pName = $p[1];
        $pDesc = $p[2];
        $isApprove = $p[3];

        // Check if permission already exists
        $existing = $db->find("SELECT id FROM permissions WHERE slug = ?", [$pSlug]);

        if ($existing) {
            $permId = (int)$existing['id'];
            $db->update('permissions', [
                'name' => $pName,
                'description' => $pDesc,
                'module_id' => $moduleId
            ], 'id = ?', [$permId]);
            $updatedCount++;
        } else {
            $permId = (int)$db->insert('permissions', [
                'name' => $pName,
                'slug' => $pSlug,
                'description' => $pDesc,
                'module_id' => $moduleId
            ]);
            $insertedCount++;
        }

        $allPermIds[] = $permId;

        // Admin gets all permissions
        $adminPermIds[] = $permId;

        // Operator gets everything except settings.reset, roles.*, and modules.*
        if (!in_array($pSlug, ['settings.reset', 'roles.create', 'roles.delete', 'modules.create', 'modules.delete'])) {
            $operatorPermIds[] = $permId;
        }

        // Viewer gets only .view permissions
        if (str_ends_with($pSlug, '.view')) {
            $viewerPermIds[] = $permId;
        }

        // Ensure mapped in module_permissions
        $hasMp = $db->find("SELECT COUNT(*) as cnt FROM module_permissions WHERE module_id = ? AND permission_id = ?", [$moduleId, $permId])['cnt'] ?? 0;
        if ($hasMp == 0) {
            $db->insert('module_permissions', [
                'module_id' => $moduleId,
                'permission_id' => $permId
            ]);
        }
    }
}

echo "Total Izin Baru Dibuat: {$insertedCount}\n";
echo "Total Izin Diperbarui: {$updatedCount}\n";
echo "Total Seluruh Izin: " . count($allPermIds) . "\n";

// Now sync default permissions to existing roles
$roles = $db->findAll("SELECT id, slug, name FROM roles");
foreach ($roles as $role) {
    $roleId = (int)$role['id'];
    $rSlug = $role['slug'];

    // Super admin automatically bypasses everything in code, but populate role_permissions as well
    if ($rSlug === 'super_admin') {
        foreach ($allPermIds as $pId) {
            $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)")->execute([$roleId, $pId]);
        }
        echo "Role 'Super Admin' diberikan semua " . count($allPermIds) . " izin.\n";
    } elseif ($rSlug === 'admin') {
        foreach ($adminPermIds as $pId) {
            $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)")->execute([$roleId, $pId]);
        }
        echo "Role 'Admin' diberikan " . count($adminPermIds) . " izin (termasuk seluruh approve).\n";
    } elseif ($rSlug === 'operator') {
        foreach ($operatorPermIds as $pId) {
            $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)")->execute([$roleId, $pId]);
        }
        echo "Role 'Operator' disinkronkan (" . count($operatorPermIds) . " izin).\n";
    } elseif ($rSlug === 'viewer') {
        foreach ($viewerPermIds as $pId) {
            $pdo->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)")->execute([$roleId, $pId]);
        }
        echo "Role 'Viewer' disinkronkan (" . count($viewerPermIds) . " izin view).\n";
    }
}

// Clean up rogue or obsolete permissions
try {
    // Clean up rogue mapping of settings.view to module 9
    $pdo->exec("DELETE FROM module_permissions WHERE module_id = 9 AND permission_id = 26");

    $oldRppDel = $db->find("SELECT id FROM permissions WHERE slug = 'rpp.delete'");
    if ($oldRppDel) {
        $db->delete('role_permissions', 'permission_id = ?', [$oldRppDel['id']]);
        $db->delete('module_permissions', 'permission_id = ?', [$oldRppDel['id']]);
        $db->delete('permissions', 'id = ?', [$oldRppDel['id']]);
        echo "Pembersihan izin lama 'rpp.delete' selesai.\n";
    }
} catch (Exception $e) {
    // Ignore if not present
}

// Clear session / RBAC cache
if (class_exists('RBAC')) {
    RBAC::clearCache();
}

echo "=== Migrasi Hak Akses RBAC Selesai dengan Sukses! ===\n";
