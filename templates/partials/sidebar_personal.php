<?php
/**
 * Sidebar Navigation — Khusus Akun Otorisasi Personal
 * Menampilkan seluruh modul beserta sub-fitur dalam satu sidebar penuh (Unified Multi-Module Sidebar)
 * Zero extra DB queries: sepenuhnya menggunakan cache RBAC & Auth di session/memori.
 */

$accessibleModules = RBAC::getAccessibleModules();
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$currentTab  = $_GET['tab'] ?? '';

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}

if (!function_exists('checkPersonalSubPerm')) {
    function checkPersonalSubPerm($perm): bool
    {
        if (empty($perm)) {
            return true;
        }
        if (is_array($perm)) {
            return RBAC::hasAnyPermission($perm);
        }
        return RBAC::hasPermission((string)$perm);
    }
}

if (!function_exists('getPersonalModuleSubItems')) {
    /**
     * Daftar sub-fitur untuk setiap modul berdasarkan izin RBAC pengguna.
     * Tidak melakukan query database sama sekali (0 extra query).
     */
    function getPersonalModuleSubItems(array $module): array
    {
        $slug = $module['slug'] ?? '';
        $items = [];

        switch ($slug) {
            case 'kelola-pegawai':
                $raw = [
                    ['label' => 'Biodata & Profil Saya',   'url' => url('kelola-pegawai'),           'match' => '/kelola-pegawai',           'exact' => true,  'perm' => 'pegawai.data.view'],
                    ['label' => 'Penugasan & SK Mengajar', 'url' => url('kelola-pegawai/penugasan'), 'match' => '/kelola-pegawai/penugasan', 'exact' => false, 'perm' => 'pegawai.penugasan.view'],
                    ['label' => 'Prestasi Saya',           'url' => url('kelola-pegawai/prestasi'),  'match' => '/kelola-pegawai/prestasi',  'exact' => false, 'perm' => 'pegawai.prestasi.view'],
                    ['label' => 'Pelatihan & Diklat',      'url' => url('kelola-pegawai/pelatihan'), 'match' => '/kelola-pegawai/pelatihan', 'exact' => false, 'perm' => 'pegawai.pelatihan.view'],
                    ['label' => 'Riwayat Karir',           'url' => url('kelola-pegawai/karir'),     'match' => '/kelola-pegawai/karir',     'exact' => false, 'perm' => 'pegawai.karir.view'],
                    ['label' => 'Dashboard',               'url' => url('kelola-pegawai/statistik'), 'match' => '/kelola-pegawai/statistik', 'exact' => false, 'perm' => 'pegawai.dashboard'],
                ];
                break;

            case 'kelola-perangkat-pembelajaran':
                $raw = [
                    ['label' => 'Dashboard Perangkat',       'url' => url('kelola-perangkat-pembelajaran'),                       'match' => '/kelola-perangkat-pembelajaran',                       'exact' => true,  'perm' => 'perangkat.dashboard'],
                    ['label' => 'Jadwal Mengajar Saya',      'url' => url('kelola-perangkat-pembelajaran/jadwal'),                'match' => '/kelola-perangkat-pembelajaran/jadwal',                'exact' => false, 'perm' => 'perangkat.jadwal.view'],
                    ['label' => 'Kalender Pendidikan',       'url' => url('kelola-perangkat-pembelajaran/kaldik'),                'match' => '/kelola-perangkat-pembelajaran/kaldik',                'exact' => false, 'perm' => 'perangkat.kaldik.view'],
                    ['label' => 'HEB & HES',                 'url' => url('kelola-perangkat-pembelajaran/rincian-hari-efektif'), 'match' => '/kelola-perangkat-pembelajaran/rincian-hari-efektif', 'exact' => false, 'perm' => 'perangkat.hari_efektif.view'],
                    ['label' => 'CP & ATP',                  'url' => url('kelola-perangkat-pembelajaran/cpatp'),                 'match' => '/kelola-perangkat-pembelajaran/cpatp',                 'exact' => false, 'perm' => 'perangkat.cpatp.view'],
                    ['label' => 'Program Semester (Prosem)', 'url' => url('kelola-perangkat-pembelajaran/prosem'),                'match' => '/kelola-perangkat-pembelajaran/prosem',                'exact' => false, 'perm' => 'perangkat.prosem.view'],
                    ['label' => 'Program Tahunan (Prota)',   'url' => url('kelola-perangkat-pembelajaran/prota'),                 'match' => '/kelola-perangkat-pembelajaran/prota',                 'exact' => false, 'perm' => 'perangkat.prota.view'],
                    ['label' => 'RPP / Modul Ajar',          'url' => url('kelola-perangkat-pembelajaran/rpp'),                   'match' => '/kelola-perangkat-pembelajaran/rpp',                   'exact' => false, 'perm' => 'perangkat.rpp.view'],
                    ['label' => 'Pusat Verifikasi',          'url' => url('kelola-perangkat-pembelajaran/verifikasi'),            'match' => '/kelola-perangkat-pembelajaran/verifikasi',            'exact' => false, 'perm' => 'perangkat.approve'],
                ];
                break;

            case 'kelola-jurnal-guru':
                $raw = [
                    ['label' => 'Isi & Jurnal Saya',       'url' => url('kelola-jurnal-guru?tab=jurnal_saya'), 'match' => '/kelola-jurnal-guru', 'tab' => 'jurnal_saya', 'js_fn' => 'switchJurnalTab'],
                    ['label' => 'Dashboard Jurnal',        'url' => url('kelola-jurnal-guru?tab=dashboard'),   'match' => '/kelola-jurnal-guru', 'tab' => 'dashboard',   'js_fn' => 'switchJurnalTab', 'default_tab' => true],
                    ['label' => 'Data Jurnal Mengajar',    'url' => url('kelola-jurnal-guru?tab=data'),        'match' => '/kelola-jurnal-guru', 'tab' => 'data',        'js_fn' => 'switchJurnalTab'],
                    ['label' => 'Rekap Persentase Jurnal', 'url' => url('kelola-jurnal-guru?tab=rekap'),       'match' => '/kelola-jurnal-guru', 'tab' => 'rekap',       'js_fn' => 'switchJurnalTab'],
                    ['label' => 'Matriks Harian Jurnal',   'url' => url('kelola-jurnal-guru?tab=matrix'),      'match' => '/kelola-jurnal-guru', 'tab' => 'matrix',      'js_fn' => 'switchJurnalTab'],
                    ['label' => 'Jurnal Belum Terisi',     'url' => url('kelola-jurnal-guru?tab=kosong'),      'match' => '/kelola-jurnal-guru', 'tab' => 'kosong',      'js_fn' => 'switchJurnalTab'],
                ];
                break;

            case 'kelola-jurnal-staff':
                $raw = [
                    ['label' => 'Dashboard Jurnal Staff',      'url' => url('kelola-jurnal-staff?tab=dashboard'), 'match' => '/kelola-jurnal-staff', 'tab' => 'dashboard', 'js_fn' => 'switchStaffTab', 'default_tab' => true],
                    ['label' => 'Data, Rekap & Matriks Jurnal','url' => url('kelola-jurnal-staff?tab=data'),      'match' => '/kelola-jurnal-staff', 'tab' => 'data',      'js_fn' => 'switchStaffTab'],
                    ['label' => 'Jam Sesi Kerja',              'url' => url('kelola-jurnal-staff?tab=jam'),       'match' => '/kelola-jurnal-staff', 'tab' => 'jam',       'js_fn' => 'switchStaffTab'],
                    ['label' => 'Daftar Tugas Jabatan',        'url' => url('kelola-jurnal-staff?tab=tugas'),     'match' => '/kelola-jurnal-staff', 'tab' => 'tugas',     'js_fn' => 'switchStaffTab'],
                ];
                break;

            case 'kelola-nilai':
                $raw = [
                    ['label' => 'Dashboard Nilai',   'url' => url('kelola-nilai'),       'match' => '/kelola-nilai',       'exact' => true,  'perm' => 'nilai.dashboard'],
                    ['label' => 'Input Nilai Siswa', 'url' => url('kelola-nilai/group'), 'match' => '/kelola-nilai/group', 'exact' => false, 'perm' => ['nilai.group.view', 'nilai.input.view', 'nilai.input.create']],
                    ['label' => 'Rekap Nilai',       'url' => url('kelola-nilai/rekap'), 'match' => '/kelola-nilai/rekap', 'exact' => false, 'perm' => 'nilai.rekap'],
                ];
                break;

            case 'kelola-absen-siswa':
                $raw = [
                    ['label' => 'Dashboard Absen Siswa', 'url' => url('kelola-absen-siswa'),       'match' => '/kelola-absen-siswa',       'exact' => true,  'perm' => 'absen_siswa.dashboard'],
                    ['label' => 'Input Absen Mapel',     'url' => url('kelola-absen-siswa/mapel'), 'match' => '/kelola-absen-siswa/mapel', 'exact' => false, 'perm' => ['absen_siswa.mapel.view', 'absen_siswa.mapel.create']],
                    ['label' => 'Input Absen Kelas',     'url' => url('kelola-absen-siswa/kelas'), 'match' => '/kelola-absen-siswa/kelas', 'exact' => false, 'perm' => ['absen_siswa.kelas.view', 'absen_siswa.kelas.create']],
                    ['label' => 'Rekap Absen Siswa',     'url' => url('kelola-absen-siswa/rekap'), 'match' => '/kelola-absen-siswa/rekap', 'exact' => false, 'perm' => ['absen_siswa.rekap', 'absen_siswa.dashboard', 'absen_siswa.mapel.view', 'absen_siswa.kelas.view', 'absen_siswa.view']],
                ];
                break;

            case 'kelola-ibadah-guru':
                $raw = [
                    ['label' => 'Isi Ibadah Harian Saya',  'url' => url('kelola-ibadah-guru?tab=ibadah_saya'), 'match' => '/kelola-ibadah-guru', 'tab' => 'ibadah_saya', 'js_fn' => 'switchIbadahTab'],
                    ['label' => 'Dashboard Ibadah',        'url' => url('kelola-ibadah-guru?tab=dashboard'),   'match' => '/kelola-ibadah-guru', 'tab' => 'dashboard',   'js_fn' => 'switchIbadahTab', 'default_tab' => true],
                    ['label' => 'Riwayat Ibadah Harian',   'url' => url('kelola-ibadah-guru?tab=harian'),      'match' => '/kelola-ibadah-guru', 'tab' => 'harian',      'js_fn' => 'switchIbadahTab'],
                    ['label' => 'Rekap & Matriks Ibadah',  'url' => url('kelola-ibadah-guru?tab=rekap'),       'match' => '/kelola-ibadah-guru', 'tab' => 'rekap',       'js_fn' => 'switchIbadahTab'],
                ];
                break;

            case 'kelola-absen-pegawai':
                $raw = [
                    ['label' => 'Ringkasan Kehadiran Saya', 'url' => url('kelola-absen-pegawai?tab=dashboard'), 'match' => '/kelola-absen-pegawai', 'tab' => 'dashboard', 'js_fn' => 'switchAbsenTab', 'default_tab' => true],
                    ['label' => 'Lokasi & Jam Kerja Saya',  'url' => url('kelola-absen-pegawai?tab=pegawai'),   'match' => '/kelola-absen-pegawai', 'tab' => 'pegawai',   'js_fn' => 'switchAbsenTab'],
                    ['label' => 'Riwayat & Rekap Presensi', 'url' => url('kelola-absen-pegawai?tab=rekap'),     'match' => '/kelola-absen-pegawai', 'tab' => 'rekap',     'js_fn' => 'switchAbsenTab'],
                ];
                break;

            case 'kelola-cuti':
                $raw = [
                    ['label' => 'Kuota & Saldo Cuti Saya',  'url' => url('kelola-cuti?tab=pegawai'),   'match' => '/kelola-cuti', 'tab' => 'pegawai',   'js_fn' => 'switchCutiTab', 'default_tab' => true, 'perm' => 'cuti.pengajuan.view'],
                    ['label' => 'Pengajuan & Riwayat Cuti', 'url' => url('kelola-cuti?tab=pengajuan'), 'match' => '/kelola-cuti', 'tab' => 'pengajuan', 'js_fn' => 'switchCutiTab', 'perm' => ['cuti.pengajuan.view', 'cuti.pengajuan.create']],
                    ['label' => 'Mutasi Saldo Cuti',        'url' => url('kelola-cuti?tab=log'),       'match' => '/kelola-cuti', 'tab' => 'log',       'js_fn' => 'switchCutiTab', 'perm' => 'cuti.pengajuan.view'],
                ];
                break;

            case 'kelola-siswa':
                $raw = [
                    ['label' => 'Dashboard Siswa',  'url' => url('kelola-siswa/statistik'),  'match' => '/kelola-siswa/statistik',  'exact' => false, 'perm' => 'siswa.dashboard'],
                    ['label' => 'Data Siswa',       'url' => url('kelola-siswa'),            'match' => '/kelola-siswa',            'exact' => true,  'perm' => 'siswa.data.view'],
                    ['label' => 'Buku Induk Siswa', 'url' => url('kelola-siswa/buku-induk'), 'match' => '/kelola-siswa/buku-induk', 'exact' => false, 'perm' => 'siswa.buku_induk.view'],
                    ['label' => 'Prestasi Siswa',   'url' => url('kelola-siswa/prestasi'),   'match' => '/kelola-siswa/prestasi',   'exact' => false, 'perm' => 'siswa.prestasi.view'],
                ];
                break;

            case 'kelola-quran-siswa':
                $raw = [
                    ['label' => 'Dashboard Qur\'an',     'url' => url('kelola-quran-siswa'),                'match' => '/kelola-quran-siswa',                'exact' => true,  'perm' => 'quran_siswa.dashboard'],
                    ['label' => 'Setoran SD/SMP/SMA',    'url' => url('kelola-quran-siswa/sd-smp-sma'),     'match' => '/kelola-quran-siswa/sd-smp-sma',     'exact' => false, 'perm' => ['quran_siswa.sd_smp.view', 'quran_siswa.sd_smp.create']],
                    ['label' => 'Penilaian Qur\'an PAUD','url' => url('kelola-quran-siswa/paud/penilaian'), 'match' => '/kelola-quran-siswa/paud/penilaian', 'exact' => false, 'perm' => ['quran_siswa.paud_penilaian.view', 'quran_siswa.paud_penilaian.create']],
                    ['label' => 'Rekap Qur\'an PAUD',    'url' => url('kelola-quran-siswa/paud/rekap'),     'match' => '/kelola-quran-siswa/paud/rekap',     'exact' => false, 'perm' => 'quran_siswa.paud_rekap.view'],
                ];
                break;

            case 'kelola-sarpras':
                $raw = [
                    ['label' => 'Dashboard Sarpras',  'url' => url('kelola-sarpras'),            'match' => '/kelola-sarpras',            'exact' => true,  'perm' => 'sarpras.dashboard'],
                    ['label' => 'Inventaris Barang',  'url' => url('kelola-sarpras/barang'),     'match' => '/kelola-sarpras/barang',     'exact' => false, 'perm' => 'sarpras.barang.view'],
                    ['label' => 'Peminjaman Sarpras', 'url' => url('kelola-sarpras/peminjaman'), 'match' => '/kelola-sarpras/peminjaman', 'exact' => false, 'perm' => 'sarpras.peminjaman.view'],
                ];
                break;

            case 'kelola-spmb':
                $raw = [
                    ['label' => 'Dashboard SPMB',     'url' => url('kelola-spmb'),                    'match' => '/kelola-spmb',                    'exact' => true,  'perm' => 'spmb.dashboard'],
                    ['label' => 'Data Siswa Baru',    'url' => url('kelola-spmb/siswa-baru'),         'match' => '/kelola-spmb/siswa-baru',         'exact' => false, 'perm' => 'spmb.siswa_baru.view'],
                    ['label' => 'Observasi Awal',     'url' => url('kelola-spmb/observasi-awal'),     'match' => '/kelola-spmb/observasi-awal',     'exact' => false, 'perm' => 'spmb.observasi.view'],
                    ['label' => 'Screening Psikolog', 'url' => url('kelola-spmb/screening-psikolog'), 'match' => '/kelola-spmb/screening-psikolog', 'exact' => false, 'perm' => 'spmb.psikolog.view'],
                ];
                break;

            default:
                $route = ltrim($module['route'] ?? $slug, '/');
                $raw = [
                    ['label' => $module['name'] ?? 'Buka Modul', 'url' => url($route), 'match' => '/' . $route, 'exact' => false],
                ];
                break;
        }

        foreach ($raw as $item) {
            if (checkPersonalSubPerm($item['perm'] ?? null)) {
                $items[] = $item;
            }
        }

        if (empty($items)) {
            $route = ltrim($module['route'] ?? $slug, '/');
            $items[] = [
                'label' => $module['name'] ?? 'Buka Modul',
                'url'   => url($route),
                'match' => '/' . $route,
                'exact' => false,
            ];
        }

        return $items;
    }
}

if (!function_exists('isPersonalSubItemActive')) {
    function isPersonalSubItemActive(array $item, string $currentPath, string $currentTab): bool
    {
        $normCurrent = '/' . trim($currentPath, '/');
        $match = '/' . trim($item['match'] ?? '', '/');

        // Cek apakah menggunakan tab query (?tab=...)
        if (!empty($item['tab'])) {
            $onBaseModule = ($normCurrent === $match || str_ends_with($normCurrent, $match));
            if (!$onBaseModule) {
                return false;
            }
            if ($currentTab === '' && !empty($item['default_tab'])) {
                return true;
            }
            return $currentTab === $item['tab'];
        }

        // Exact path match
        if (!empty($item['exact'])) {
            return $normCurrent === $match || str_ends_with($normCurrent, $match);
        }

        // Prefix path match
        return strpos($normCurrent, $match) !== false;
    }
}

$normCurrentPath = '/' . trim($currentPath, '/');
$isOnDashboard = ($normCurrentPath === '/dashboard' || $normCurrentPath === '/' || str_ends_with($normCurrentPath, '/dashboard'));
?>

<!-- Sidebar Full — Mode Otorisasi Personal -->
<aside id="sidebar"
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col select-none"
       role="navigation"
       aria-label="Navigasi Workspace Personal">

    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-5 border-b border-primary-800/60 bg-primary-950/30 shrink-0">
        <a href="<?= url('dashboard') ?>" class="flex items-center gap-3 min-w-0 group">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-teal-400 to-primary-600 flex items-center justify-center shadow-lg text-white font-bold shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-sm tracking-tight leading-tight truncate group-hover:text-teal-200 transition-colors"><?= e(SYS_APP_NAME) ?></h1>
                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 mt-0.5 rounded-md text-[10px] font-bold bg-teal-500/20 text-teal-200 border border-teal-400/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                    Workspace Personal
                </span>
            </div>
        </a>
        <!-- Mobile close button -->
        <button onclick="toggleSidebar()"
                class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors"
                aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- User Profile Card -->
    <div class="px-4 pt-3.5 pb-2 shrink-0">
        <div class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl bg-primary-950/45 border border-primary-800/70">
            <?php $sidebarAvatar = Auth::avatar(); ?>
            <?php if ($sidebarAvatar): ?>
                <img src="<?= e($sidebarAvatar) ?>" alt="<?= e(Auth::name()) ?>" class="w-9 h-9 rounded-xl object-cover shrink-0 border border-teal-400/40 shadow-xs">
            <?php else: ?>
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-teal-500/30 text-teal-100 border border-teal-400/30 font-bold text-xs shrink-0">
                    <?= Auth::initials() ?>
                </div>
            <?php endif; ?>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-white truncate"><?= e(Auth::name()) ?></p>
                <p class="text-[11px] text-primary-300 truncate"><?= e(Auth::jabatan()) ?></p>
                <?php if (Auth::isJenjangRestricted()): ?>
                    <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                        Jenjang: <?= e(implode(', ', Auth::getAllowedJenjang())) ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Filter Input (0 Server Load) -->
        <div class="relative mt-2.5">
            <input type="text"
                   id="personal-sidebar-search"
                   oninput="window.filterPersonalSidebar && window.filterPersonalSidebar(this.value)"
                   placeholder="Cari menu atau fitur..."
                   class="w-full pl-8 pr-3 py-1.5 rounded-xl bg-primary-950/50 border border-primary-800/80 text-xs text-white placeholder:text-primary-400 focus:outline-none focus:border-teal-400/70 transition-colors">
            <svg class="w-3.5 h-3.5 text-primary-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
        </div>
    </div>

    <!-- Unified Multi-Module Navigation -->
    <nav id="personal-sidebar-nav" class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-2 px-3 space-y-2" aria-label="Menu Modul Personal">

        <!-- Beranda Personal -->
        <div>
            <a href="<?= url('dashboard') ?>"
               class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-all duration-150 <?= $isOnDashboard ? 'bg-teal-500/25 text-white border border-teal-400/40 shadow-xs' : 'text-primary-200 hover:bg-primary-800/60 hover:text-white border border-transparent' ?>">
                <svg class="w-4 h-4 <?= $isOnDashboard ? 'text-teal-300' : 'text-primary-400' ?> shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                <span>Beranda Personal</span>
            </a>
        </div>

        <?php
        foreach ($accessibleModules as $module):
            $modSlug = $module['slug'] ?? '';
            if ($modSlug === 'dashboard') {
                continue;
            }
            $modName = $module['name'] ?? $modSlug;
            $modRoute = '/' . trim($module['route'] ?? $modSlug, '/');
            $modIcon = $module['icon_svg'] ?? '';
            $subItems = getPersonalModuleSubItems($module);

            // Cek apakah modul ini sedang aktif
            $isModuleActive = (strpos($normCurrentPath, $modRoute) !== false);
            foreach ($subItems as $si) {
                if (isPersonalSubItemActive($si, $currentPath, $currentTab)) {
                    $isModuleActive = true;
                    break;
                }
            }
        ?>
        <div class="personal-mod-group rounded-xl border <?= $isModuleActive ? 'bg-primary-950/45 border-primary-700/80' : 'bg-primary-950/20 border-primary-800/40' ?> overflow-hidden transition-colors"
             data-mod-slug="<?= e($modSlug) ?>"
             data-mod-name="<?= e(strtolower($modName)) ?>">

            <!-- Module Accordion Header -->
            <button type="button"
                    onclick="window.togglePersonalModSection && window.togglePersonalModSection('<?= e($modSlug) ?>')"
                    class="w-full flex items-center justify-between gap-2 px-3 py-2 text-left transition-colors <?= $isModuleActive ? 'text-white bg-primary-800/40' : 'text-primary-200 hover:text-white hover:bg-primary-800/30' ?>"
                    aria-expanded="true">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-4 h-4 [&>svg]:w-4 [&>svg]:h-4 <?= $isModuleActive ? 'text-teal-300' : 'text-primary-400' ?> shrink-0">
                        <?= $modIcon ?>
                    </span>
                    <span class="text-xs font-bold tracking-tight truncate"><?= e($modName) ?></span>
                </div>
                <svg id="personal-mod-chevron-<?= e($modSlug) ?>"
                     class="w-3.5 h-3.5 text-primary-400 shrink-0 transition-transform duration-200"
                     fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                </svg>
            </button>

            <!-- Sub-Features List (Full Sidebar View) -->
            <ul id="personal-mod-list-<?= e($modSlug) ?>"
                class="px-2 pb-2 pt-1 space-y-0.5 border-t border-primary-800/40">
                <?php foreach ($subItems as $sub):
                    $subActive = isPersonalSubItemActive($sub, $currentPath, $currentTab);
                    $subLabel  = $sub['label'] ?? '';
                    $subUrl    = $sub['url'] ?? '#';
                    $subTab    = $sub['tab'] ?? '';
                    $subJsFn   = $sub['js_fn'] ?? '';
                    $subMatch  = $sub['match'] ?? '';
                ?>
                <li class="personal-sub-item" data-sub-label="<?= e(strtolower($subLabel . ' ' . $modName)) ?>">
                    <a href="<?= e($subUrl) ?>"
                       <?php if ($subTab && $subJsFn): ?>
                       data-personal-tab="<?= e($subTab) ?>"
                       data-personal-fn="<?= e($subJsFn) ?>"
                       data-personal-match="<?= e($subMatch) ?>"
                       onclick="return window.handlePersonalTabClick ? window.handlePersonalTabClick(event, this) : true;"
                       <?php endif; ?>
                       class="personal-sub-link flex items-center gap-2 pl-6 pr-2.5 py-1.5 rounded-lg text-xs transition-all duration-150 <?= $subActive ? 'bg-teal-500/25 text-white font-semibold border border-teal-400/40 shadow-2xs' : 'text-primary-200/90 hover:text-white hover:bg-primary-800/50 font-medium border border-transparent' ?>">
                        <span class="w-1.5 h-1.5 rounded-full shrink-0 <?= $subActive ? 'bg-teal-300 ring-2 ring-teal-400/30' : 'bg-primary-600' ?>"></span>
                        <span class="truncate"><?= e($subLabel) ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>

    </nav>

    <!-- Sidebar Footer -->
    <div class="p-3.5 border-t border-primary-800/50 bg-primary-950/25 space-y-1.5 shrink-0">
        <div class="px-3 py-1.5 rounded-xl bg-primary-950/50 border border-primary-800/60 flex items-center justify-between">
            <span class="text-[10px] text-primary-400 font-bold uppercase tracking-wider">T.A. Aktif</span>
            <span class="text-xs text-white font-semibold truncate flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <?= e(SYS_TAHUN_AKADEMIK_NAME) ?>
            </span>
        </div>

        <div class="grid grid-cols-2 gap-1.5">
            <a href="<?= url('mobile') ?>"
               target="_blank"
               class="flex items-center justify-center gap-1.5 px-2.5 py-2 rounded-xl text-xs font-semibold text-primary-200 hover:text-white bg-primary-950/40 hover:bg-primary-800/70 border border-primary-800/60 transition-colors">
                <svg class="w-3.5 h-3.5 text-teal-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                </svg>
                <span class="truncate">App Mobile</span>
            </a>

            <a href="<?= url('logout') ?>"
               class="flex items-center justify-center gap-1.5 px-2.5 py-2 rounded-xl text-xs font-semibold text-rose-300 hover:text-white bg-rose-950/30 hover:bg-rose-600/80 border border-rose-800/40 transition-colors">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                </svg>
                <span class="truncate">Keluar</span>
            </a>
        </div>
    </div>
</aside>

<script>
(function() {
    window.togglePersonalModSection = function(slug) {
        var list = document.getElementById('personal-mod-list-' + slug);
        var chev = document.getElementById('personal-mod-chevron-' + slug);
        if (!list) return;
        var isHidden = list.classList.contains('hidden');
        if (isHidden) {
            list.classList.remove('hidden');
            if (chev) chev.classList.remove('-rotate-90');
            try { sessionStorage.setItem('psb_mod_' + slug, 'open'); } catch (e) {}
        } else {
            list.classList.add('hidden');
            if (chev) chev.classList.add('-rotate-90');
            try { sessionStorage.setItem('psb_mod_' + slug, 'closed'); } catch (e) {}
        }
    };

    window.filterPersonalSidebar = function(query) {
        var q = (query || '').toLowerCase().trim();
        var groups = document.querySelectorAll('#personal-sidebar-nav .personal-mod-group');
        groups.forEach(function(grp) {
            var slug = grp.getAttribute('data-mod-slug');
            var list = document.getElementById('personal-mod-list-' + slug);
            var chev = document.getElementById('personal-mod-chevron-' + slug);
            var items = grp.querySelectorAll('.personal-sub-item');
            var anyVisible = false;

            items.forEach(function(item) {
                var text = item.getAttribute('data-sub-label') || '';
                if (!q || text.indexOf(q) !== -1) {
                    item.classList.remove('hidden');
                    anyVisible = true;
                } else {
                    item.classList.add('hidden');
                }
            });

            if (!q) {
                grp.classList.remove('hidden');
            } else if (anyVisible) {
                grp.classList.remove('hidden');
                if (list) list.classList.remove('hidden');
                if (chev) chev.classList.remove('-rotate-90');
            } else {
                grp.classList.add('hidden');
            }
        });
    };

    window.handlePersonalTabClick = function(event, linkEl) {
        var tab = linkEl.getAttribute('data-personal-tab');
        var fnName = linkEl.getAttribute('data-personal-fn');
        var matchPath = linkEl.getAttribute('data-personal-match') || '';
        var currPath = window.location.pathname || '';

        if (tab && fnName && typeof window[fnName] === 'function' && currPath.indexOf(matchPath) !== -1) {
            event.preventDefault();
            window[fnName](tab);

            // Update active styling inside personal sidebar
            var allLinks = document.querySelectorAll('#personal-sidebar-nav .personal-sub-link');
            allLinks.forEach(function(a) {
                a.className = 'personal-sub-link flex items-center gap-2 pl-6 pr-2.5 py-1.5 rounded-lg text-xs transition-all duration-150 text-primary-200/90 hover:text-white hover:bg-primary-800/50 font-medium border border-transparent';
                var dot = a.querySelector('span:first-child');
                if (dot) dot.className = 'w-1.5 h-1.5 rounded-full shrink-0 bg-primary-600';
            });
            linkEl.className = 'personal-sub-link flex items-center gap-2 pl-6 pr-2.5 py-1.5 rounded-lg text-xs transition-all duration-150 bg-teal-500/25 text-white font-semibold border border-teal-400/40 shadow-2xs';
            var activeDot = linkEl.querySelector('span:first-child');
            if (activeDot) activeDot.className = 'w-1.5 h-1.5 rounded-full shrink-0 bg-teal-300 ring-2 ring-teal-400/30';

            if (window.innerWidth < 1024 && typeof window.toggleSidebar === 'function') {
                window.toggleSidebar();
            }
            return false;
        }
        return true;
    };
})();
</script>
