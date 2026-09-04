<?php
/**
 * Sidebar Modul Kelola Perangkat Pembelajaran
 */
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Fetch pending count for verifikasi badge
$dbPending = Database::getInstance();
$pendingCount = 0;
try {
    $pRes = $dbPending->find("SELECT COUNT(*) as total FROM perangkat_pembelajaran WHERE status = 'diajukan'");
    $pendingCount = (int) ($pRes['total'] ?? 0);
} catch (Exception $e) {
    $pendingCount = 0;
}
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Perangkat Pembelajaran">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 text-white font-bold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <div>
                <h1 class="text-white font-bold text-base tracking-tight leading-tight">Perangkat Ajar</h1>
                <p class="text-emerald-300 text-xs">Administrasi Guru & KBM</p>
            </div>
        </div>
        
        <!-- Close Mobile Menu -->
        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Sidebar Content (Scrollable) -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-5 px-4 space-y-6">
        
        <!-- Menu Utama -->
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Navigasi Modul
            </h3>
            <ul class="space-y-1">
                <?php
                if (!function_exists('renderPerangkatMenuItem')) {
                    function renderPerangkatMenuItem($label, $url, $icon, $isActive = false, $badge = null) {
                        $activeClass = $isActive 
                            ? 'bg-emerald-600/30 text-white font-semibold shadow-sm border border-emerald-500/40' 
                            : 'text-primary-200 hover:bg-primary-800/60 hover:text-white border border-transparent';
                        
                        $iconColor = $isActive ? 'text-emerald-400' : 'text-primary-400 group-hover:text-emerald-300';
                        
                        echo "<li>
                            <a href=\"$url\" class=\"flex items-center justify-between px-3 py-2.5 rounded-2xl transition-all duration-200 group $activeClass\">
                                <div class=\"flex items-center gap-3 min-w-0\">
                                    <div class=\"$iconColor transition-colors flex-shrink-0\">$icon</div>
                                    <span class=\"text-sm truncate\">$label</span>
                                </div>";
                        if ($badge !== null && $badge > 0) {
                            echo "<span class=\"ml-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-500/30 text-amber-300 border border-amber-500/40 animate-pulse\">$badge</span>";
                        }
                        echo "</a>
                        </li>";
                    }
                }

                // 1. Dashboard
                renderPerangkatMenuItem(
                    'Dashboard Overview',
                    url('kelola-perangkat-pembelajaran'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>',
                    $currentUri === '/kelola-perangkat-pembelajaran' || $currentUri === '/kelola-perangkat-pembelajaran/dashboard'
                );

                // 2. Jadwal Pelajaran
                renderPerangkatMenuItem(
                    'Jadwal Pelajaran',
                    url('kelola-perangkat-pembelajaran/jadwal'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
                    strpos($currentUri, '/kelola-perangkat-pembelajaran/jadwal') !== false
                );

                // 2. Kalender Pendidikan
                renderPerangkatMenuItem(
                    'Kalender Pendidikan',
                    url('kelola-perangkat-pembelajaran/kaldik'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" /></svg>',
                    strpos($currentUri, '/kelola-perangkat-pembelajaran/kaldik') !== false
                );

                // 3. HEB & HES (Hari Efektif Belajar & Sekolah)
                renderPerangkatMenuItem(
                    'HEB & HES',
                    url('kelola-perangkat-pembelajaran/rincian-hari-efektif'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
                    strpos($currentUri, '/kelola-perangkat-pembelajaran/rincian-hari-efektif') !== false || strpos($currentUri, '/kelola-perangkat-pembelajaran/heb') !== false || strpos($currentUri, '/kelola-perangkat-pembelajaran/hes') !== false
                );

                // 4. CP & ATP (Capaian Pembelajaran & Alur Tujuan Pembelajaran)
                renderPerangkatMenuItem(
                    'CP & ATP',
                    url('kelola-perangkat-pembelajaran/cpatp'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>',
                    strpos($currentUri, '/kelola-perangkat-pembelajaran/cpatp') !== false
                );

                // 5. Prosem
                renderPerangkatMenuItem(
                    'Prosem',
                    url('kelola-perangkat-pembelajaran/prosem'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" /></svg>',
                    strpos($currentUri, '/kelola-perangkat-pembelajaran/prosem') !== false
                );

                // 5. Prota
                renderPerangkatMenuItem(
                    'Prota',
                    url('kelola-perangkat-pembelajaran/prota'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
                    strpos($currentUri, '/kelola-perangkat-pembelajaran/prota') !== false
                );

                // 7. RPP / Modul Ajar
                renderPerangkatMenuItem(
                    'RPP / Modul Ajar',
                    url('kelola-perangkat-pembelajaran/rpp'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
                    strpos($currentUri, '/kelola-perangkat-pembelajaran/rpp') !== false
                );
                ?>
            </ul>
        </div>

        <!-- Approval Hub -->
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Verifikasi & Pengesahan
            </h3>
            <ul class="space-y-1">
                <?php
                renderPerangkatMenuItem(
                    'Pusat Verifikasi',
                    url('kelola-perangkat-pembelajaran/verifikasi'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" /></svg>',
                    strpos($currentUri, '/kelola-perangkat-pembelajaran/verifikasi') !== false,
                    $pendingCount
                );
                ?>
            </ul>
        </div>

    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20 space-y-2">
        <a href="<?= url('dashboard') ?>" 
           class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold text-primary-300 hover:text-white hover:bg-white/10 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Portal Utama</span>
        </a>
    </div>
</aside>
