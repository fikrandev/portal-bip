<?php
/**
 * Sidebar Khusus Pengaturan Sistem
 * Digunakan oleh Modul: Kelola Pengguna, Kelola Peran, Manajemen Modul
 */
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-40 w-[280px] h-screen transition-transform -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Pengaturan">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30">
        <a href="<?= url('dashboard') ?>" class="flex items-center gap-3 w-full">
            <?php if (defined('SYS_APP_LOGO') && SYS_APP_LOGO): ?>
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shadow-lg bg-white p-1">
                <img src="<?= url(ltrim(SYS_APP_LOGO, '/')) ?>" alt="Logo" class="max-w-full max-h-full object-contain">
            </div>
            <?php else: ?>
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854-.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>
            <?php endif; ?>
            <div>
                <h1 class="text-white font-bold text-lg tracking-tight">Pengaturan</h1>
                <p class="text-primary-300 text-xs truncate max-w-[150px]"><?= SYS_APP_NAME ?></p>
            </div>
        </a>
        
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
                Konfigurasi Sistem
            </h3>
            <ul class="space-y-1">
                <?php
                // Function to render menu items
                function renderSettingsMenu($label, $url, $icon, $isActive = false) {
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
                
                // Kelola Pengguna
                renderSettingsMenu(
                    'Kelola Pengguna', 
                    url('users'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>',
                    strpos($currentUri, '/users') !== false
                );

                // Kelola Peran & Hak Akses
                renderSettingsMenu(
                    'Peran & Hak Akses', 
                    url('roles'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>',
                    strpos($currentUri, '/roles') !== false
                );

                // Manajemen Modul
                renderSettingsMenu(
                    'Manajemen Modul', 
                    url('modules'), 
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>',
                    strpos($currentUri, '/modules') !== false
                );
                ?>
            </ul>
        </div>
    </div>
</aside>
