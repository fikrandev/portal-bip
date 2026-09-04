<?php
/**
 * Sidebar Khusus Kelola Pegawai
 */
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Pegawai">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg tracking-tight">Kelola Pegawai</h1>
                <p class="text-primary-300 text-xs">Modul Portal BIP</p>
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
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-6 px-4">
        
        <!-- Menu Section -->
        <div class="mb-6">
            <h3 class="px-3 mb-3 text-xs font-bold text-primary-400 uppercase tracking-wider">
                Menu Utama
            </h3>
            <ul class="space-y-1">
                <?php
                // Function to render menu items
                if (!function_exists('renderPegawaiMenu')) {
                    function renderPegawaiMenu($label, $url, $icon, $isActive = false) {
                        $activeClass = $isActive 
                            ? 'bg-primary-800/80 text-white font-medium shadow-sm border border-primary-700/50' 
                            : 'text-primary-200 hover:bg-primary-800/50 hover:text-white border border-transparent';
                        
                        $iconClass = $isActive ? 'text-primary-400' : 'text-primary-400 group-hover:text-primary-300';
                        
                        echo "<li>
                            <a href=\"$url\" class=\"flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group $activeClass\">
                                <div class=\"$iconClass transition-colors\">$icon</div>
                                <span class=\"text-sm\">$label</span>
                            </a>
                        </li>";
                    }
                }

                // Get current URL for active state
                $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                
                // Dashboard Statistik
                renderPegawaiMenu(
                    'Dashboard Statistik', 
                    url('kelola-pegawai/statistik'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>',
                    strpos($currentUri, '/kelola-pegawai/statistik') !== false
                );

                // Data Pegawai
                renderPegawaiMenu(
                    'Data Pegawai', 
                    url('kelola-pegawai'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
                    strpos($currentUri, '/kelola-pegawai') !== false && 
                    strpos($currentUri, '/kelola-pegawai/statistik') === false && 
                    strpos($currentUri, '/kelola-pegawai/prestasi') === false &&
                    strpos($currentUri, '/kelola-pegawai/pelatihan') === false &&
                    strpos($currentUri, '/kelola-pegawai/karir') === false &&
                    strpos($currentUri, '/kelola-pegawai/keluar') === false &&
                    strpos($currentUri, '/kelola-pegawai/penugasan') === false
                );

                // Penugasan Pegawai
                renderPegawaiMenu(
                    'Penugasan Pegawai', 
                    url('kelola-pegawai/penugasan'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V8.25ZM6.75 21v-3.375c0-.621.504-1.125 1.125-1.125H22.5" /></svg>',
                    strpos($currentUri, '/kelola-pegawai/penugasan') !== false
                );

                // Prestasi Pegawai
                renderPegawaiMenu(
                    'Prestasi Pegawai', 
                    url('kelola-pegawai/prestasi'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" /></svg>',
                    strpos($currentUri, '/kelola-pegawai/prestasi') !== false
                );

                // Pelatihan & Diklat Pegawai
                renderPegawaiMenu(
                    'Pelatihan & Diklat', 
                    url('kelola-pegawai/pelatihan'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" /></svg>',
                    strpos($currentUri, '/kelola-pegawai/pelatihan') !== false
                );

                // Riwayat Karir Pegawai
                renderPegawaiMenu(
                    'Riwayat Karir Pegawai', 
                    url('kelola-pegawai/karir'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" /></svg>',
                    strpos($currentUri, '/kelola-pegawai/karir') !== false
                );

                // Pegawai Keluar
                renderPegawaiMenu(
                    'Pegawai Keluar', 
                    url('kelola-pegawai/keluar'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>',
                    strpos($currentUri, '/kelola-pegawai/keluar') !== false
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
