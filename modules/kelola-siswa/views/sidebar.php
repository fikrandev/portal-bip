<?php
/**
 * Sidebar Khusus Kelola Siswa - Portal BIP
 */
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Siswa">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/20 text-white font-bold text-sm">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                </svg>
            </div>
            <div>
                <h1 class="text-white font-bold text-base tracking-tight">Kelola Siswa</h1>
                <p class="text-primary-300 text-[11px]">Dapodik & Database BIP</p>
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
            <h3 class="px-3 mb-2 text-[10px] font-extrabold text-primary-400 uppercase tracking-wider">
                Navigasi Siswa
            </h3>
            <ul class="space-y-1">
                <?php
                if (!function_exists('renderSiswaMenuItem')) {
                    function renderSiswaMenuItem($label, $url, $iconSvg, $isActive = false, $badge = null) {
                        $activeClass = $isActive 
                            ? 'bg-emerald-600/90 text-white font-semibold shadow-sm border border-emerald-500/50' 
                            : 'text-primary-200 hover:bg-primary-800/50 hover:text-white border border-transparent';
                        
                        $iconClass = $isActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                        
                        echo "<li>
                            <a href=\"$url\" class=\"flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group $activeClass\">
                                <div class=\"flex items-center gap-3\">
                                    <div class=\"$iconClass transition-colors flex-shrink-0 [&>svg]:w-4 [&>svg]:h-4\">$iconSvg</div>
                                    <span class=\"text-xs\">$label</span>
                                </div>
                                " . ($badge ? "<span class=\"px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-950/40 text-primary-300\">$badge</span>" : "") . "
                            </a>
                        </li>";
                    }
                }

                $currentUri = $_SERVER['REQUEST_URI'] ?? '';
                $jenjangQuery = $_GET['jenjang'] ?? '';

                // Dashboard & Statistik
                renderSiswaMenuItem(
                    'Dashboard Statistik', 
                    url('kelola-siswa/statistik'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>',
                    strpos($currentUri, '/kelola-siswa/statistik') !== false
                );

                // Data Siswa
                renderSiswaMenuItem(
                    'Data Siswa', 
                    url('kelola-siswa'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
                    (strpos($currentUri, '/kelola-siswa') !== false && empty($jenjangQuery) && strpos($currentUri, '/statistik') === false && strpos($currentUri, '/buku-induk') === false && strpos($currentUri, '/prestasi') === false && strpos($currentUri, '/keluar') === false && strpos($currentUri, '/create') === false && strpos($currentUri, '/foto') === false)
                );

                // Galeri Foto Siswa
                renderSiswaMenuItem(
                    'Galeri Foto Siswa', 
                    url('kelola-siswa/foto'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>',
                    strpos($currentUri, '/kelola-siswa/foto') !== false
                );

                // Buku Induk Siswa
                renderSiswaMenuItem(
                    'Buku Induk Siswa', 
                    url('kelola-siswa/buku-induk'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
                    strpos($currentUri, '/kelola-siswa/buku-induk') !== false
                );

                // Prestasi Siswa
                renderSiswaMenuItem(
                    'Prestasi Siswa', 
                    url('kelola-siswa/prestasi'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" /></svg>',
                    strpos($currentUri, '/kelola-siswa/prestasi') !== false
                );

                // Siswa Keluar & Mutasi
                renderSiswaMenuItem(
                    'Siswa Keluar & Mutasi', 
                    url('kelola-siswa/keluar'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>',
                    strpos($currentUri, '/kelola-siswa/keluar') !== false
                );

                // Tambah Siswa Baru
                renderSiswaMenuItem(
                    'Tambah Siswa Baru', 
                    url('kelola-siswa/create'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.765Z" /></svg>',
                    strpos($currentUri, '/kelola-siswa/create') !== false
                );
                ?>
            </ul>
        </div>

        <!-- Filter Satuan / Jenjang -->
        <div>
            <h3 class="px-3 mb-2 text-[10px] font-extrabold text-primary-400 uppercase tracking-wider">
                Satuan Pendidikan
            </h3>
            <ul class="space-y-1">
                <?php
                // PAUD / TK
                renderSiswaMenuItem(
                    'Siswa PAUD / TK', 
                    url('kelola-siswa?jenjang=PAUD'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg>',
                    strtoupper($jenjangQuery) === 'PAUD'
                );

                // SD
                renderSiswaMenuItem(
                    'Siswa SD', 
                    url('kelola-siswa?jenjang=SD'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>',
                    strtoupper($jenjangQuery) === 'SD'
                );

                // SMP
                renderSiswaMenuItem(
                    'Siswa SMP', 
                    url('kelola-siswa?jenjang=SMP'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14.25a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /></svg>',
                    strtoupper($jenjangQuery) === 'SMP'
                );

                // SMA
                renderSiswaMenuItem(
                    'Siswa SMA', 
                    url('kelola-siswa?jenjang=SMA'), 
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>',
                    strtoupper($jenjangQuery) === 'SMA'
                );
                ?>
            </ul>
        </div>

    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20">
        <a href="<?= url('dashboard') ?>" 
           class="flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold text-primary-300 hover:text-white hover:bg-white/10 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Portal Utama</span>
        </a>
    </div>
</aside>
