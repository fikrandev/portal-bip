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
                                    <a href="<?= url('kelola-pegawai/penugasan') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= $currentPath === 'kelola-pegawai/penugasan' || strpos($currentPath, 'kelola-pegawai/penugasan/') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V8.25ZM6.75 21v-3.375c0-.621.504-1.125 1.125-1.125H22.5" />
                                        </svg>
                                        <span>Penugasan Pegawai</span>
                                    </a>
                                </div>
                            <?php elseif ($module['slug'] === 'kelola-perangkat-pembelajaran'): ?>
                                <div class="pl-14 pr-3 mt-1 space-y-1">
                                    <a href="<?= url('kelola-perangkat-pembelajaran/kaldik') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/kaldik') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <span>Kalender Pendidikan</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/rincian-hari-efektif') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= (strpos($currentPath, 'kelola-perangkat-pembelajaran/rincian-hari-efektif') !== false || strpos($currentPath, 'kelola-perangkat-pembelajaran/heb') !== false || strpos($currentPath, 'kelola-perangkat-pembelajaran/hes') !== false) ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span>HEB & HES</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/cpatp') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/cpatp') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                        </svg>
                                        <span>CP & ATP</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/prosem') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/prosem') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                                        </svg>
                                        <span>Prosem</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/prota') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/prota') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <span>Prota</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/rpp') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/rpp') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        <span>RPP / Modul Ajar</span>
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
                                    <a href="<?= url('kelola-pegawai/penugasan') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= $currentPath === 'kelola-pegawai/penugasan' || strpos($currentPath, 'kelola-pegawai/penugasan/') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V8.25ZM6.75 21v-3.375c0-.621.504-1.125 1.125-1.125H22.5" />
                                        </svg>
                                        <span>Penugasan Pegawai</span>
                                    </a>
                                </div>
                            <?php elseif ($module['slug'] === 'kelola-perangkat-pembelajaran'): ?>
                                <div class="pl-14 pr-3 mt-1 space-y-1">
                                    <a href="<?= url('kelola-perangkat-pembelajaran/kaldik') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/kaldik') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <span>Kalender Pendidikan</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/rincian-hari-efektif') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= (strpos($currentPath, 'kelola-perangkat-pembelajaran/rincian-hari-efektif') !== false || strpos($currentPath, 'kelola-perangkat-pembelajaran/heb') !== false || strpos($currentPath, 'kelola-perangkat-pembelajaran/hes') !== false) ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span>HEB & HES</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/cpatp') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/cpatp') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                        </svg>
                                        <span>CP & ATP</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/prosem') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/prosem') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                                        </svg>
                                        <span>Prosem</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/prota') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/prota') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <span>Prota</span>
                                    </a>
                                    <a href="<?= url('kelola-perangkat-pembelajaran/rpp') ?>" class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-all duration-200 <?= strpos($currentPath, 'kelola-perangkat-pembelajaran/rpp') !== false ? 'text-white bg-white/10' : 'text-primary-300 hover:text-white hover:bg-white/5' ?>">
                                        <svg class="w-3.5 h-3.5 text-primary-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        <span>RPP / Modul Ajar</span>
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
