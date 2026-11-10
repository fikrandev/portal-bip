<?php
/**
 * Sidebar Khusus Modul Kelola Cuti Pegawai - Portal BIP
 */
$currentTab = $_GET['tab'] ?? 'pegawai';
$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}

$sidebarStats = $stats ?? (class_exists('CutiModel') ? CutiModel::getSummaryStats() : []);
$pendingCount = (int)($sidebarStats['totalPending'] ?? 0);
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Cuti">
    
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Kelola Cuti</h1>
                <p class="text-primary-300 text-xs truncate">Manajemen Cuti Pegawai</p>
            </div>
        </div>
        
        <!-- Close Mobile Menu -->
        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Info Aturan Cuti Tahunan Badge -->
    <div class="px-4 pt-4 pb-2 shrink-0">
        <div class="bg-primary-950/40 rounded-xl p-3 border border-primary-800/60">
            <div class="flex items-center justify-between text-xs mb-1">
                <span class="text-primary-300 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Aturan Cuti Tahunan
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">AKTIF</span>
            </div>
            <div class="text-white font-bold text-xs tracking-wide mt-1">
                +<?= (int)($sidebarStats['configTahunan']['tambah_per_bulan'] ?? 1) ?> Hari / Bulan &bull; Maks <?= (int)($sidebarStats['configTahunan']['maksimal_akumulasi'] ?? 36) ?> Hari
            </div>
            <p class="text-primary-300 text-[11px] mt-0.5 truncate">
                Periode berjalan: <?= date('F Y') ?>
            </p>
        </div>
    </div>

    <!-- Sidebar Content (Scrollable) -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-4 space-y-6">
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Menu Kelola Cuti
            </h3>
            <ul class="space-y-1">
                <?php
                if (!function_exists('renderCutiSidebarMenu')) {
                    function renderCutiSidebarMenu(string $label, string $tabKey, string $icon, string $currentTab, ?int $badge = null, string $badgeColor = 'bg-amber-500 text-white'): void {
                        $isActive = ($currentTab === $tabKey);
                        $activeClass = $isActive 
                            ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' 
                            : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent';
                        $iconClass = $isActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                        $href = url('kelola-cuti?tab=' . $tabKey);

                        echo '<li>
                            <a href="' . $href . '" onclick="if(typeof switchCutiTab === \'function\'){ switchCutiTab(\'' . $tabKey . '\'); return false; }" data-sidebar-tab="' . $tabKey . '" class="cuti-sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group ' . $activeClass . '">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 ' . $iconClass . ' transition-colors shrink-0">' . $icon . '</div>
                                    <span class="text-sm truncate">' . e($label) . '</span>
                                </div>';
                        if ($badge !== null && $badge > 0) {
                            echo '<span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full ' . $badgeColor . ' shrink-0">' . $badge . '</span>';
                        }
                        echo '</a>
                        </li>';
                    }
                }

                // 1. Data Cuti Pegawai (Semua Pegawai)
                renderCutiSidebarMenu(
                    'Data Cuti Pegawai',
                    'pegawai',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
                    $currentTab
                );

                // 2. Data Pengajuan Cuti
                renderCutiSidebarMenu(
                    'Data Pengajuan',
                    'pengajuan',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
                    $currentTab
                );

                // 3. Data Approve (Verifikasi)
                renderCutiSidebarMenu(
                    'Data Approve',
                    'approve',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>',
                    $currentTab,
                    $pendingCount,
                    'bg-amber-500 text-white animate-pulse'
                );

                // 4. Riwayat Approve
                renderCutiSidebarMenu(
                    'Riwayat Approve',
                    'riwayat',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
                    $currentTab
                );

                // 5. Log Pengurangan Cuti
                renderCutiSidebarMenu(
                    'Log Pengurangan Cuti',
                    'log',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>',
                    $currentTab
                );

                // 6. Jenis Cuti (Tahunan & Khusus)
                renderCutiSidebarMenu(
                    'Jenis Cuti',
                    'jenis',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>',
                    $currentTab
                );
                ?>
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

