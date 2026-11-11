<?php
/**
 * Sidebar Khusus Modul Kelola Jurnal Guru - Portal BIP
 * Menampilkan 5 Menu Utama:
 * 1. Dashboard
 * 2. Data Jurnal (Nama, Kegiatan, Kelas, Mapel, Presensi)
 * 3. Rekap Jurnal (Nama, Jumlah PIC & Beban JP)
 * 4. Data Rekap Harian Matrix (✓ / ✕ per tanggal)
 * 5. Rekap Jurnal Tidak Terisi
 */
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$activeTab = $activeTab ?? ($_GET['tab'] ?? 'dashboard');

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}

$sidebarStats = $kpiToday ?? [];
$countToday = (int)($sidebarStats['count_today'] ?? 0);
$jpToday = (int)($sidebarStats['jp_today'] ?? 0);
$totalUnfilledCount = (int)($unfilledTotalCount ?? 0);
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Jurnal Guru">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo Sekolah" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20 text-white font-bold shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Kelola Jurnal</h1>
                <p class="text-primary-300 text-xs truncate">Monitoring KBM & PIC Guru</p>
            </div>
        </div>
        
        <!-- Close Mobile Menu -->
        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Info Jurnal Hari Ini Badge -->
    <div class="px-4 pt-4 pb-2 shrink-0">
        <div class="bg-primary-950/40 rounded-xl p-3 border border-primary-800/60">
            <div class="flex items-center justify-between text-xs mb-1">
                <span class="text-primary-300 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Jurnal Hari Ini
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <?= $countToday ?> Sesi
                </span>
            </div>
            <div class="text-white font-bold text-xs tracking-wide mt-1">
                📚 Total Terlaksana: <?= $jpToday ?> JP
            </div>
            <p class="text-primary-300 text-[11px] mt-0.5 truncate">
                Sinkron langsung dari Portal Guru
            </p>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-4 space-y-6">
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Menu Kelola Jurnal
            </h3>
            <ul class="space-y-1">
                <?php
                if (!function_exists('renderJurnalSidebarItem')) {
                    function renderJurnalSidebarItem(string $tabKey, string $label, string $iconSvg, string $activeTab, ?int $badge = null, string $badgeClass = 'bg-primary-500 text-white'): void {
                        $isActive = ($activeTab === $tabKey);
                        $cls = $isActive
                            ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40'
                            : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent';
                        $iconCls = $isActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                        $href = url('kelola-jurnal-guru?tab=' . $tabKey);

                        echo '<li>
                            <a href="' . $href . '" 
                               onclick="if(typeof switchJurnalTab === \'function\'){ switchJurnalTab(\'' . $tabKey . '\'); return false; }"
                               data-jurnal-sidebar-tab="' . $tabKey . '"
                               class="jurnal-sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group ' . $cls . '">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 ' . $iconCls . ' transition-colors shrink-0">' . $iconSvg . '</span>
                                    <span class="text-sm truncate">' . e($label) . '</span>
                                </div>';
                        if ($badge !== null && $badge > 0) {
                            echo '<span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full ' . $badgeClass . ' shrink-0">' . $badge . '</span>';
                        }
                        echo '</a>
                        </li>';
                    }
                }

                // 1. Dashboard
                renderJurnalSidebarItem(
                    'dashboard',
                    'Dashboard Jurnal',
                    '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>',
                    $activeTab
                );

                // 2. Data Jurnal (Nama, Kegiatan, Kelas)
                renderJurnalSidebarItem(
                    'data',
                    'Data Jurnal Guru',
                    '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>',
                    $activeTab
                );

                // 3. Rekap Jurnal (Nama, Jumlah PIC)
                renderJurnalSidebarItem(
                    'rekap',
                    'Rekap Jurnal (PIC)',
                    '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>',
                    $activeTab
                );

                // 4. Data Rekap Harian Matrix (✓ / ✕)
                renderJurnalSidebarItem(
                    'matrix',
                    'Rekap Harian Matrix',
                    '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5"/></svg>',
                    $activeTab
                );

                // 5. Rekap Jurnal Tidak Terisi
                renderJurnalSidebarItem(
                    'kosong',
                    'Jurnal Tidak Terisi',
                    '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>',
                    $activeTab,
                    $totalUnfilledCount,
                    'bg-rose-500 text-white'
                );
                ?>

                <!-- Cetak Rekap KBM -->
                <li class="pt-2">
                    <a href="<?= url('kelola-jurnal-guru/cetak') ?>" 
                       target="_blank"
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent transition-all duration-200 group">
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.376 1.942-5.908 5.28-5.908 3.337 0 5.52 2.532 5.28 5.908m-10.56 0A5.967 5.967 0 0 0 12 18.25c2.476 0 4.636-1.503 5.28-4.421m-10.56 0c0-1.077.29-2.09.794-2.964m9.766 2.964a5.986 5.986 0 0 0-.794-2.964M6.72 13.829A7.95 7.95 0 0 1 12 11.25a7.95 7.95 0 0 1 5.28 2.579M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v1.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 10.5v-1.5A2.25 2.25 0 0 1 6.75 6.75Z" />
                            </svg>
                            <span class="text-sm truncate">Cetak Laporan PDF</span>
                        </div>
                        <span class="text-[10px] text-primary-400 group-hover:text-primary-200 font-bold uppercase tracking-wider">Print</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Terkait Pembelajaran & Guru -->
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Modul Terkait
            </h3>
            <ul class="space-y-1">
                <li>
                    <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent transition-all duration-200 group">
                        <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253 3.75h18M4.5 7.5h15a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 19.5 22.5h-15A2.25 2.25 0 0 1 2.25 20.25V9.75A2.25 2.25 0 0 1 4.5 7.5Z" />
                        </svg>
                        <span class="text-sm truncate">Jadwal Pelajaran KBM</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('kelola-perangkat-pembelajaran') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent transition-all duration-200 group">
                        <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <span class="text-sm truncate">Perangkat Ajar (RPP/Prota)</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('kelola-absen-pegawai') ?>" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent transition-all duration-200 group">
                        <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span class="text-sm truncate">Absen Pegawai (GPS)</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20 shrink-0">
        <a href="<?= url('dashboard') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span class="truncate">Kembali ke Portal Utama</span>
        </a>
    </div>
</aside>
