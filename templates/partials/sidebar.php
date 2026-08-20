<?php
/**
 * Sidebar Navigation
 * Responsive: mobile slide-over, desktop fixed
 * Dynamic menu built from accessible modules
 */

$accessibleModules = RBAC::getAccessibleModules();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Group system modules vs feature modules
$systemModules = ['dashboard', 'kelola-pengguna', 'kelola-peran', 'manajemen-modul'];
?>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" 
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300"
     onclick="toggleSidebar()"
     aria-hidden="true"></div>

<!-- Sidebar -->
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 h-full w-[280px] transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col"
       role="navigation" 
       aria-label="Navigasi utama">
    
    <!-- Sidebar Background with Gradient -->
    <div class="absolute inset-0 bg-gradient-to-b from-primary-900 via-primary-800 to-primary-950"></div>
    
    <!-- Sidebar Content -->
    <div class="relative z-10 flex flex-col h-full">
        
        <!-- Logo & Brand -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <?php if (defined('SYS_APP_LOGO') && SYS_APP_LOGO): ?>
            <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-white p-1 backdrop-blur-sm">
                <img src="<?= url(ltrim(SYS_APP_LOGO, '/')) ?>" alt="Logo" class="max-w-full max-h-full object-contain">
            </div>
            <?php else: ?>
            <div class="flex items-center justify-center w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-sm">
                <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
                </svg>
            </div>
            <?php endif; ?>
            <div>
                <h1 class="text-lg font-bold text-white tracking-tight truncate max-w-[130px]"><?= SYS_APP_NAME ?></h1>
                <p class="text-[11px] text-primary-300 font-medium tracking-wide uppercase">Management Portal</p>
            </div>
            <!-- Mobile close button -->
            <button onclick="toggleSidebar()" 
                    class="lg:hidden ml-auto p-1.5 rounded-full text-primary-300 hover:text-white hover:bg-white/10 transition-colors"
                    aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- User Card -->
        <div class="px-4 py-4">
            <div class="flex items-center gap-3 px-3 py-3 rounded-2xl bg-white/8 backdrop-blur-sm">
                <div class="flex items-center justify-center w-9 h-9 rounded-2xl bg-primary-400/30 text-white font-bold text-sm">
                    <?= Auth::initials() ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white truncate"><?= e(Auth::name()) ?></p>
                    <p class="text-[11px] text-primary-300 truncate"><?= e(implode(', ', Auth::roles())) ?></p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto px-4 pb-4 scrollbar-thin" aria-label="Menu navigasi">
            
            <?php
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
                <ul class="space-y-1 mb-6">
                    <?php 
                    $isActive = strpos($currentPath, $dashboardModule['route']) !== false;
                    ?>
                    <li>
                        <a href="<?= url($dashboardModule['route']) ?>" 
                           id="nav-<?= e($dashboardModule['slug']) ?>"
                           class="group flex items-center gap-3 px-3 py-2.5 rounded-full text-sm font-medium transition-all duration-200 <?= $isActive 
                               ? 'bg-white/15 text-white shadow-lg shadow-black/10' 
                               : 'text-primary-200 hover:bg-white/8 hover:text-white' ?>"
                           aria-current="<?= $isActive ? 'page' : 'false' ?>">
                            <span class="flex items-center justify-center w-8 h-8 rounded-2xl <?= $isActive ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' ?> transition-colors">
                                <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5">
                                    <?= $dashboardModule['icon_svg'] ?>
                                </span>
                            </span>
                            <span><?= e($dashboardModule['name']) ?></span>
                            <?php if ($isActive): ?>
                                <span class="ml-auto w-1.5 h-1.5 rounded-2xl bg-primary-400 animate-pulse"></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
            
            <!-- Ungrouped Modules (Menu Utama) -->
            <?php if (!empty($ungroupedModules)): ?>
                <p class="px-3 mb-2 text-[11px] font-semibold text-primary-400 uppercase tracking-wider">Menu Utama</p>
                <ul class="space-y-1 mb-6">
                    <?php foreach ($ungroupedModules as $module): ?>
                        <?php $isActive = strpos($currentPath, $module['route']) !== false; ?>
                        <li>
                            <a href="<?= url($module['route']) ?>" 
                               id="nav-<?= e($module['slug']) ?>"
                               class="group flex items-center gap-3 px-3 py-2.5 rounded-full text-sm font-medium transition-all duration-200 <?= $isActive && $currentPath !== 'kelola-pegawai/penugasan'
                                   ? 'bg-white/15 text-white shadow-lg shadow-black/10' 
                                   : 'text-primary-200 hover:bg-white/8 hover:text-white' ?>">
                                <span class="flex items-center justify-center w-8 h-8 rounded-2xl <?= $isActive && $currentPath !== 'kelola-pegawai/penugasan' ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' ?> transition-colors">
                                    <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5"><?= $module['icon_svg'] ?></span>
                                </span>
                                <span><?= e($module['name']) ?></span>
                            </a>
                            
                            <?php if ($module['slug'] === 'kelola-pegawai'): ?>
                                <div class="pl-14 pr-3 mt-1 space-y-1">
                                    <a href="<?= url('kelola-pegawai/penugasan') ?>" class="block px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200 <?= $currentPath === 'kelola-pegawai/penugasan' || strpos($currentPath, 'kelola-pegawai/penugasan/') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        Penugasan Pegawai
                                    </a>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <!-- Grouped Modules -->
            <?php foreach ($groupedModules as $groupName => $modules): ?>
                <p class="px-3 mt-6 mb-2 text-[11px] font-semibold text-primary-400 uppercase tracking-wider"><?= e($groupName) ?></p>
                <ul class="space-y-1 mb-6">
                    <?php foreach ($modules as $module): ?>
                        <?php $isActive = strpos($currentPath, $module['route']) !== false; ?>
                        <li>
                            <a href="<?= url($module['route']) ?>" 
                               id="nav-<?= e($module['slug']) ?>"
                               class="group flex items-center gap-3 px-3 py-2.5 rounded-full text-sm font-medium transition-all duration-200 <?= $isActive && $currentPath !== 'kelola-pegawai/penugasan'
                                   ? 'bg-white/15 text-white shadow-lg shadow-black/10' 
                                   : 'text-primary-200 hover:bg-white/8 hover:text-white' ?>">
                                <span class="flex items-center justify-center w-8 h-8 rounded-2xl <?= $isActive && $currentPath !== 'kelola-pegawai/penugasan' ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' ?> transition-colors">
                                    <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5"><?= $module['icon_svg'] ?></span>
                                </span>
                                <span><?= e($module['name']) ?></span>
                            </a>
                            
                            <?php if ($module['slug'] === 'kelola-pegawai'): ?>
                                <div class="pl-14 pr-3 mt-1 space-y-1">
                                    <a href="<?= url('kelola-pegawai/penugasan') ?>" class="block px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200 <?= $currentPath === 'kelola-pegawai/penugasan' || strpos($currentPath, 'kelola-pegawai/penugasan/') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        Penugasan Pegawai
                                    </a>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>

            <!-- System Menu (Pengaturan) -->
            <?php if (!empty($pengaturanModules)): ?>
                <p class="px-3 mt-6 mb-2 text-[11px] font-semibold text-primary-400 uppercase tracking-wider">Pengaturan</p>
                <ul class="space-y-1">
                    <?php foreach ($pengaturanModules as $module): ?>
                        <?php $isActive = strpos($currentPath, $module['route']) !== false; ?>
                        <li>
                            <a href="<?= url($module['route']) ?>"
                               id="nav-<?= e($module['slug']) ?>"
                               class="group flex items-center gap-3 px-3 py-2.5 rounded-full text-sm font-medium transition-all duration-200 <?= $isActive 
                                   ? 'bg-white/15 text-white shadow-lg shadow-black/10' 
                                   : 'text-primary-200 hover:bg-white/8 hover:text-white' ?>">
                                <span class="flex items-center justify-center w-8 h-8 rounded-2xl <?= $isActive ? 'bg-white/20' : 'bg-white/5 group-hover:bg-white/10' ?> transition-colors">
                                    <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5"><?= $module['icon_svg'] ?></span>
                                </span>
                                <span><?= e($module['name']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </nav>

        <!-- Sidebar Footer -->
        <div class="px-4 py-4 border-t border-white/10">
            <div class="mb-3 px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                <p class="text-[10px] text-primary-400 font-semibold uppercase tracking-wider mb-1">Tahun Ajaran Aktif</p>
                <p class="text-xs text-white font-medium truncate flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                    <?= e(SYS_TAHUN_AKADEMIK_NAME) ?>
                </p>
            </div>
            <a href="<?= url('logout') ?>" 
               id="btn-logout-sidebar"
               class="flex items-center gap-3 px-3 py-2.5 rounded-full text-sm font-medium text-red-300 hover:bg-red-500/15 hover:text-red-200 transition-all duration-200">
                <span class="flex items-center justify-center w-8 h-8 rounded-2xl bg-red-500/10">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                    </svg>
                </span>
                <span>Keluar</span>
            </a>
            <p class="mt-3 px-3 text-[10px] text-primary-500"><?= SYS_APP_NAME ?> v<?= APP_VERSION ?></p>
        </div>
    </div>
</aside>
