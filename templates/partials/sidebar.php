<?php
/**
 * Sidebar Navigation (Portal Utama)
 * Responsive: mobile slide-over, desktop fixed
 * Dynamic menu built from accessible modules
 */

$accessibleModules = RBAC::getAccessibleModules();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Group system modules vs feature modules
$systemModules = ['dashboard', 'kelola-pengguna', 'kelola-peran', 'manajemen-modul'];

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}
?>

<!-- Sidebar -->
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       role="navigation" 
       aria-label="Navigasi utama">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo Sekolah" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20 text-white font-bold shrink-0">
                    <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate"><?= SYS_APP_NAME ?></h1>
                <p class="text-primary-300 text-xs truncate">Management Portal</p>
            </div>
        </div>
        <!-- Mobile close button -->
        <button onclick="toggleSidebar()" 
                class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors"
                aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- User Card -->
    <div class="px-4 pt-4 pb-2 shrink-0">
        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-primary-950/40 border border-primary-800/60">
            <?php $sidebarAvatar = Auth::avatar(); ?>
            <?php if ($sidebarAvatar): ?>
                <img src="<?= e($sidebarAvatar) ?>" alt="<?= e(Auth::name()) ?>" class="w-9 h-9 rounded-xl object-cover shrink-0 border border-primary-700/60 shadow-xs">
            <?php else: ?>
                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-primary-600/40 text-white font-bold text-xs shrink-0">
                    <?= Auth::initials() ?>
                </div>
            <?php endif; ?>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-white truncate"><?= e(Auth::name()) ?></p>
                <p class="text-xs text-primary-300 truncate"><?= e(implode(', ', Auth::roles())) ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-4 space-y-6" aria-label="Menu navigasi">
        <?php
        if (!function_exists('renderMainPortalMenuItem')) {
            function renderMainPortalMenuItem($module, $isActive = false) {
                $activeClass = $isActive 
                    ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' 
                    : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent';
                
                $iconColor = $isActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                $url = url($module['route']);
                $slug = e($module['slug']);
                $name = e($module['name']);
                $iconSvg = $module['icon_svg'] ?? '';
                $ariaCurrent = $isActive ? 'page' : 'false';
                
                echo "<li>
                    <a href=\"$url\" id=\"nav-$slug\" aria-current=\"$ariaCurrent\" class=\"flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group $activeClass\">
                        <div class=\"flex items-center gap-3 min-w-0\">
                            <span class=\"w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 $iconColor transition-colors shrink-0\">$iconSvg</span>
                            <span class=\"text-sm truncate\">$name</span>
                        </div>
                    </a>
                </li>";
            }
        }

        // Extract Dashboard
        $dashboardModule = null;
        $ungroupedModules = [];
        $groupedModules = [];
        
        foreach ($accessibleModules as $module) {
            if ($module['slug'] === 'dashboard') {
                $dashboardModule = $module;
                continue;
            }
            
            $group = $module['module_group'] ?? '';
            if (empty($group)) {
                $ungroupedModules[] = $module;
            } else {
                $groupedModules[$group][] = $module;
            }
        }
        
        // Extract "Pengaturan" so it's always at the bottom
        $pengaturanModules = [];
        if (isset($groupedModules['Pengaturan'])) {
            $pengaturanModules = $groupedModules['Pengaturan'];
            unset($groupedModules['Pengaturan']);
        }
        ?>

        <!-- Dashboard -->
        <?php if ($dashboardModule): ?>
            <div>
                <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">Beranda</h3>
                <ul class="space-y-1">
                    <?php 
                    $isActive = strpos($currentPath, $dashboardModule['route']) !== false;
                    renderMainPortalMenuItem($dashboardModule, $isActive);
                    ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Ungrouped Modules (Menu Utama) -->
        <?php if (!empty($ungroupedModules)): ?>
            <div>
                <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">Menu Utama</h3>
                <ul class="space-y-1">
                    <?php foreach ($ungroupedModules as $module): ?>
                        <?php 
                        $isActive = strpos($currentPath, $module['route']) !== false;
                        renderMainPortalMenuItem($module, $isActive);
                        ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Grouped Modules -->
        <?php foreach ($groupedModules as $groupName => $modules): ?>
            <div>
                <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider"><?= e($groupName) ?></h3>
                <ul class="space-y-1">
                    <?php foreach ($modules as $module): ?>
                        <?php 
                        $isActive = strpos($currentPath, $module['route']) !== false;
                        renderMainPortalMenuItem($module, $isActive);
                        ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <!-- System Menu (Pengaturan) -->
        <?php if (!empty($pengaturanModules)): ?>
            <div>
                <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">Pengaturan</h3>
                <ul class="space-y-1">
                    <?php foreach ($pengaturanModules as $module): ?>
                        <?php 
                        $isActive = strpos($currentPath, $module['route']) !== false;
                        renderMainPortalMenuItem($module, $isActive);
                        ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20 space-y-1.5 shrink-0">
        <!-- Tahun Ajaran Aktif -->
        <div class="px-3 py-2 mb-1 rounded-xl bg-primary-950/40 border border-primary-800/60">
            <p class="text-[11px] text-primary-400 font-bold uppercase tracking-wider mb-0.5">Tahun Ajaran Aktif</p>
            <p class="text-xs text-white font-medium truncate flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                <span class="truncate"><?= e(SYS_TAHUN_AKADEMIK_NAME) ?></span>
            </p>
        </div>

        <!-- Portal Guru Mobile Shortcut Button -->
        <a href="<?= url('mobile') ?>" 
           target="_blank"
           id="nav-portal-guru-mobile"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
            </svg>
            <span class="truncate">Portal Guru (Mobile)</span>
        </a>

        <a href="<?= url('logout') ?>" 
           id="btn-logout-sidebar"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
            </svg>
            <span class="truncate">Keluar</span>
        </a>
    </div>
</aside>
