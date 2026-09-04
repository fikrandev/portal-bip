<?php
/**
 * Top Navigation Bar
 * Contains mobile menu toggle, breadcrumb area, search, and user dropdown
 */
?>

<header class="sticky top-0 z-30 bg-white/80 backdrop-blur-lg border-b border-primary-100/60" role="banner">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
        
        <!-- Left: Mobile menu + Page title -->
        <div class="flex items-center gap-3">
            <!-- Mobile Menu Toggle -->
            <?php if (!($hideSidebar ?? false)): ?>
            <button onclick="toggleSidebar()" 
                    id="btn-mobile-menu"
                    class="lg:hidden p-2 rounded-full text-primary-600 hover:bg-primary-50 hover:text-primary-700 transition-colors"
                    aria-label="Buka menu navigasi"
                    aria-expanded="false">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <?php endif; ?>
            
            <!-- Page Title / Breadcrumb -->
            <div>
                <h2 class="text-lg font-bold text-primary-900"><?= e($pageTitle ?? 'Dashboard') ?></h2>
                <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
                    <nav aria-label="Breadcrumb" class="hidden sm:block">
                        <ol class="flex items-center gap-1.5 text-xs text-slate-500">
                            <li><a href="<?= url('dashboard') ?>" class="hover:text-primary-600 transition-colors">Portal</a></li>
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <li class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                    </svg>
                                    <?php if (isset($crumb['url'])): ?>
                                        <a href="<?= $crumb['url'] ?>" class="hover:text-primary-600 transition-colors"><?= e($crumb['label']) ?></a>
                                    <?php else: ?>
                                        <span class="text-primary-700 font-medium"><?= e($crumb['label']) ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Search + Actions + User -->
        <div class="flex items-center gap-2 sm:gap-3">
            
            <!-- Portal Guru Mobile Shortcut Button -->
            <a href="<?= url('mobile') ?>" 
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/80 transition-all duration-200 text-xs sm:text-sm font-semibold shadow-sm hover:shadow"
               aria-label="Portal Guru Mobile"
               title="Buka Portal Mobile Guru (PWA)">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                </svg>
                <span class="hidden sm:inline">📱 Portal Guru</span>
                <span class="sm:hidden">📱 Mobile</span>
            </a>

            <?php if (!($hideSidebar ?? false)): ?>
            <!-- Back to Portal Button (Desktop) -->
            <a href="<?= url('dashboard') ?>" 
               class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors text-sm font-medium mr-2"
               aria-label="Kembali ke Portal">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                Kembali ke Portal
            </a>
            <?php endif; ?>
            
            <!-- Search (Desktop) -->
            <div class="hidden md:block relative">
                <input type="text" 
                       id="global-search"
                       placeholder="Cari modul, fitur..."
                       class="w-full pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium"
                       aria-label="Cari modul atau fitur">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>

            <!-- Notification Bell -->
            <button id="btn-notifications" 
                    class="relative p-2.5 rounded-full text-primary-500 hover:bg-primary-50 hover:text-primary-700 transition-colors"
                    aria-label="Notifikasi">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                </svg>
                <!-- Notification dot -->
                <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-2xl ring-2 ring-white"></span>
            </button>

            <!-- User Dropdown -->
            <div class="relative" id="user-dropdown-container">
                <button onclick="toggleUserDropdown()" 
                        id="btn-user-dropdown"
                        class="flex items-center gap-2.5 p-1.5 pr-3 rounded-full hover:bg-primary-50 transition-colors"
                        aria-haspopup="true"
                        aria-expanded="false">
                    <div class="flex items-center justify-center w-8 h-8 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 text-white font-bold text-xs shadow-sm">
                        <?= Auth::initials() ?>
                    </div>
                    <span class="hidden sm:block text-sm font-medium text-primary-800 max-w-[120px] truncate"><?= e(Auth::name()) ?></span>
                    <svg class="hidden sm:block w-4 h-4 text-primary-400 transition-transform duration-200" id="user-dropdown-arrow" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div id="user-dropdown-menu" 
                     class="absolute right-0 mt-2 w-56 py-2 bg-white rounded-2xl shadow-xl shadow-primary-900/10 border border-primary-100 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right"
                     role="menu"
                     aria-label="Menu pengguna">
                    <div class="px-4 py-3 border-b border-primary-50">
                        <p class="text-sm font-semibold text-primary-900"><?= e(Auth::name()) ?></p>
                        <p class="text-xs text-primary-500 mt-0.5"><?= e(Auth::user()['email'] ?? '') ?></p>
                    </div>
                    <div class="py-1">
                        <a href="<?= url('mobile') ?>" 
                           target="_blank"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors" 
                           role="menuitem">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                            </svg>
                            Portal Mobile Guru
                        </a>
                        <a href="<?= url('logout') ?>" 
                           id="btn-logout-dropdown"
                           class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors" 
                           role="menuitem">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                            </svg>
                            Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
