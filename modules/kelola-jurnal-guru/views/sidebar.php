<?php
/**
 * Sidebar Khusus Modul Kelola Jurnal Guru - Portal BIP
 */
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}

$sidebarStats = $kpiToday ?? [];
$countToday = (int)($sidebarStats['count_today'] ?? 0);
$jpToday = (int)($sidebarStats['jp_today'] ?? 0);
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
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Jurnal KBM</h1>
                <p class="text-primary-300 text-xs truncate">Administrasi Guru & Beban JP</p>
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
                Monitoring presensi & jam mengajar
            </p>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-4 space-y-6">
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Menu Jurnal Guru
            </h3>
            <ul class="space-y-1">
                <!-- 1. Rekap Jurnal KBM -->
                <li>
                    <a href="<?= url('kelola-jurnal-guru') ?>" 
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group <?= strpos($currentUri, 'kelola-jurnal-guru') !== false && strpos($currentUri, 'cetak') === false ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent' ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 <?= strpos($currentUri, 'kelola-jurnal-guru') !== false && strpos($currentUri, 'cetak') === false ? 'text-white' : 'text-primary-400 group-hover:text-primary-300' ?> transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                            <span class="text-sm truncate">Rekap Jurnal KBM</span>
                        </div>
                    </a>
                </li>

                <!-- 2. Cetak Rekap KBM -->
                <li>
                    <a href="<?= url('kelola-jurnal-guru/cetak') ?>" 
                       target="_blank"
                       class="flex items-center justify-between px-3 py-2.5 rounded-xl text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent transition-all duration-200 group">
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 transition-colors shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.376 1.942-5.908 5.28-5.908 3.337 0 5.52 2.532 5.28 5.908m-10.56 0A5.967 5.967 0 0 0 12 18.25c2.476 0 4.636-1.503 5.28-4.421m-10.56 0c0-1.077.29-2.09.794-2.964m9.766 2.964a5.986 5.986 0 0 0-.794-2.964M6.72 13.829A7.95 7.95 0 0 1 12 11.25a7.95 7.95 0 0 1 5.28 2.579M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v1.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 10.5v-1.5A2.25 2.25 0 0 1 6.75 6.75Z" />
                            </svg>
                            <span class="text-sm truncate">Cetak Rekap KBM</span>
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
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20 space-y-1.5 shrink-0">
        <!-- Shortcut Portal Guru Mobile -->
        <a href="<?= url('mobile') ?>" 
           target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-emerald-300 hover:text-white hover:bg-emerald-800/40 border border-emerald-500/20 transition-all duration-200 group">
            <svg class="w-5 h-5 text-emerald-400 group-hover:text-emerald-300 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
            </svg>
            <span class="truncate">Portal Guru (Mobile)</span>
        </a>

        <!-- Kembali ke Dashboard Utama -->
        <a href="<?= url('dashboard') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            <span class="truncate">Portal Utama</span>
        </a>

        <!-- Logout -->
        <a href="<?= url('logout') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
            </svg>
            <span class="truncate">Keluar</span>
        </a>
    </div>
</aside>
