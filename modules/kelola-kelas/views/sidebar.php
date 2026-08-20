<?php
/**
 * Sidebar Khusus Kelola Kelas
 */
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-40 w-[280px] h-screen transition-transform -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Kelas">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg tracking-tight">Kelola Kelas</h1>
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
                function renderKelasMenu($label, $url, $icon, $isActive = false) {
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
                
                // Data Kelas
                renderKelasMenu(
                    'Data Kelas', 
                    url('kelola-kelas'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>',
                    strpos($currentUri, '/kelola-kelas') !== false
                );
                ?>
            </ul>
        </div>
    </div>
</aside>
