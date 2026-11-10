<?php
/**
 * Sidebar Khusus Modul Kelola Absen Pegawai - Portal BIP
 */
$currentTab = $_GET['tab'] ?? 'pegawai';
$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}

$sidebarStats = $stats ?? (class_exists('AbsenPegawaiModel') ? AbsenPegawaiModel::getSummaryStats() : []);
$radiusGlobal = (int)($sidebarStats['radiusGlobal'] ?? 10);
$namaKantorDefault = $sidebarStats['globalConfig']['nama_kantor_default'] ?? 'Kampus Utama SIT Bina Insan Palu';
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Absen Pegawai">
    
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Absen Pegawai</h1>
                <p class="text-primary-300 text-xs truncate">Geolokasi & Radius Absen</p>
            </div>
        </div>
        
        <!-- Close Mobile Menu -->
        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Info Radius Global Badge -->
    <div class="px-4 pt-4 pb-2 shrink-0">
        <div class="bg-primary-950/40 rounded-xl p-3 border border-primary-800/60">
            <div class="flex items-center justify-between text-xs mb-1">
                <span class="text-primary-300 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Radius Global (Semua)
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <?= $radiusGlobal ?> Meter
                </span>
            </div>
            <div class="text-white font-bold text-xs tracking-wide mt-1 truncate" title="<?= e($namaKantorDefault) ?>">
                📍 <?= e($namaKantorDefault) ?>
            </div>
            <p class="text-primary-300 text-[11px] mt-0.5 truncate">
                Berlaku untuk seluruh pegawai & unit
            </p>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-4 space-y-6">
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Menu Absen Pegawai
            </h3>
            <ul class="space-y-1">
                <?php
                if (!function_exists('renderAbsenPegawaiSidebarMenu')) {
                    function renderAbsenPegawaiSidebarMenu(string $label, string $tabKey, string $icon, string $currentTab, ?int $badge = null, string $badgeColor = 'bg-primary-500 text-white'): void {
                        $isActive = ($currentTab === $tabKey);
                        $activeClass = $isActive 
                            ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' 
                            : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent';
                        $iconClass = $isActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                        $href = url('kelola-absen-pegawai?tab=' . $tabKey);

                        echo '<li>
                            <a href="' . $href . '" onclick="if(typeof switchAbsenTab === \'function\'){ switchAbsenTab(\'' . $tabKey . '\'); return false; }" data-sidebar-tab="' . $tabKey . '" class="absen-sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group ' . $activeClass . '">
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

                // 1. Lokasi Absen Per Pegawai
                renderAbsenPegawaiSidebarMenu(
                    'Lokasi Per Pegawai',
                    'pegawai',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
                    $currentTab,
                    (int)($sidebarStats['totalPegawai'] ?? 0),
                    'bg-primary-700 text-primary-100'
                );

                // 2. Lokasi Custom Per Unit
                renderAbsenPegawaiSidebarMenu(
                    'Lokasi Custom Per Unit',
                    'unit',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>',
                    $currentTab,
                    (int)($sidebarStats['totalUnit'] ?? 0),
                    'bg-purple-500/30 text-purple-200'
                );

                // 3. Pengaturan Radius Global & Lokasi Semula
                renderAbsenPegawaiSidebarMenu(
                    'Radius Global & Lokasi Semula',
                    'pengaturan',
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>',
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

