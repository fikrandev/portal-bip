<?php
/**
 * Sidebar Khusus Kelola Siswa
 */
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Siswa">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                </svg>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg tracking-tight">Kelola Siswa</h1>
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
                function renderSiswaMenu($label, $url, $icon, $isActive = false) {
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

                // Get current URL for active state
                $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                
                // Dashboard Statistik
                renderSiswaMenu(
                    'Dashboard Statistik', 
                    url('kelola-siswa/statistik'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" /></svg>',
                    strpos($currentUri, '/kelola-siswa/statistik') !== false
                );

                // Data Siswa
                renderSiswaMenu(
                    'Data Siswa', 
                    url('kelola-siswa'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
                    strpos($currentUri, '/kelola-siswa') !== false && strpos($currentUri, '/kelola-siswa/statistik') === false && strpos($currentUri, '/kelola-siswa/prestasi') === false
                );

                // Prestasi Siswa
                renderSiswaMenu(
                    'Prestasi Siswa', 
                    url('kelola-siswa/prestasi'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" /></svg>',
                    strpos($currentUri, '/kelola-siswa/prestasi') !== false
                );
                ?>
            </ul>
        </div>
    </div>
</aside>
