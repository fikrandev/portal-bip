<?php
/**
 * Sidebar Khusus Modul Kelola & Rekap Ibadah Guru - Portal BIP
 */
$activeTab = $activeTab ?? ($_GET['tab'] ?? 'dashboard');
$canViewAll = $canViewAll ?? true;
$kpiDaily = $kpiDaily ?? [];
$sudahIsiToday = (int)($kpiDaily['sudah_isi'] ?? 0);
$totalGuruAll = (int)($kpiDaily['total_guru'] ?? 0);
$empatSempurna = (int)($kpiDaily['empat_sempurna'] ?? 0);

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}
?>
<aside id="sidebar"
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Ibadah Guru">

    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo Sekolah" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 text-white font-bold shrink-0">
                    🌙
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Ibadah Guru</h1>
                <p class="text-primary-300 text-xs truncate">Mutaba'ah Yaumiyah BIP</p>
            </div>
        </div>

        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Info Ibadah Hari Ini Badge -->
    <div class="px-4 pt-4 pb-2 shrink-0">
        <div class="bg-primary-950/40 rounded-xl p-3 border border-primary-800/60">
            <div class="flex items-center justify-between text-xs mb-1">
                <span class="text-primary-300 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Pengisi Mutaba'ah
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <?= $sudahIsiToday ?> / <?= $totalGuruAll ?> Guru
                </span>
            </div>
            <div class="text-white font-bold text-xs tracking-wide mt-1">
                ✨ <?= $empatSempurna ?> Guru 4/4 Sempurna
            </div>
            <p class="text-primary-300 text-[11px] mt-0.5 truncate">
                Sinkron dengan Portal Guru Mobile
            </p>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-4 space-y-6">
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Menu Mutaba'ah Ibadah
            </h3>
            <ul class="space-y-1">
                <?php
                if (!function_exists('renderIbadahSidebarItem')) {
                    function renderIbadahSidebarItem(string $tabKey, string $label, string $iconSvg, string $activeTab, int|string|null $badge = null, string $badgeClass = 'bg-primary-500 text-white'): void {
                        $isActive = ($activeTab === $tabKey);
                        $cls = $isActive
                            ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40'
                            : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent';
                        $iconCls = $isActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                        $href = url('kelola-ibadah-guru?tab=' . $tabKey);

                        echo '<li>
                            <a href="' . $href . '"
                               onclick="if(typeof switchIbadahTab === \'function\'){ switchIbadahTab(\'' . $tabKey . '\'); return false; }"
                               data-ibadah-sidebar-tab="' . $tabKey . '"
                               class="ibadah-sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group ' . $cls . '">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 ' . $iconCls . ' transition-colors shrink-0">' . $iconSvg . '</span>
                                    <span class="text-sm truncate">' . e($label) . '</span>
                                </div>';
                        if ($badge !== null && ((is_int($badge) && $badge > 0) || (is_string($badge) && $badge !== ''))) {
                            echo '<span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full ' . $badgeClass . ' shrink-0">' . e((string)$badge) . '</span>';
                        }
                        echo '</a>
                        </li>';
                    }
                }

                renderIbadahSidebarItem(
                    'ibadah_saya',
                    'Isi Ibadah Saya',
                    '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>',
                    $activeTab,
                    'Akun Login',
                    'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30'
                );

                if ($canViewAll) {
                    renderIbadahSidebarItem(
                        'dashboard',
                        'Dashboard Monitoring',
                        '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/></svg>',
                        $activeTab
                    );

                    renderIbadahSidebarItem(
                        'harian',
                        'Data Harian Guru',
                        '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>',
                        $activeTab,
                        $sudahIsiToday > 0 ? $sudahIsiToday : null,
                        'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30'
                    );

                    renderIbadahSidebarItem(
                        'rekap',
                        'Rekap Periode & Matriks',
                        '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25"/></svg>',
                        $activeTab
                    );
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20 space-y-1.5 shrink-0">
        <a href="<?= url('mobile') ?>" target="_blank"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
            </svg>
            <span class="truncate">Portal Guru (Mobile)</span>
        </a>

        <a href="<?= url('dashboard') ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            <span class="truncate">Kembali ke Portal Utama</span>
        </a>
    </div>
</aside>
