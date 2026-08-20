<?php /** Dashboard View — Module Cards Grid */ ?>

<!-- Welcome Banner -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-primary-500 via-primary-400 to-primary-400 p-6 sm:p-8 mb-8 shadow-lg shadow-primary-500/20">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10" aria-hidden="true">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white rounded-full"></div>
        <div class="absolute -bottom-8 -left-8 w-36 h-36 bg-white rounded-full"></div>
        <div class="absolute top-1/2 right-1/4 w-20 h-20 bg-white rounded-full"></div>
    </div>
    
    <div class="relative z-10">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-1">
            Selamat Datang, <?= e(Auth::name()) ?>! 👋
        </h2>
        <p class="text-primary-100 text-sm sm:text-base">
            Kelola semua modul dan fitur dari satu tempat.
        </p>
        
        <!-- Quick Stats -->
        <div class="flex flex-wrap gap-4 sm:gap-6 mt-6">
            <div class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-white/15 backdrop-blur-sm">
                <svg class="w-5 h-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
                <div>
                    <p class="text-lg font-bold text-white"><?= $totalUsers ?></p>
                    <p class="text-[11px] text-white/70 font-medium">Pengguna</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-white/15 backdrop-blur-sm">
                <svg class="w-5 h-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
                </svg>
                <div>
                    <p class="text-lg font-bold text-white"><?= $totalModules ?></p>
                    <p class="text-[11px] text-white/70 font-medium">Modul Aktif</p>
                </div>
            </div>
            <div class="flex items-center gap-3 px-4 py-2.5 rounded-2xl bg-white/15 backdrop-blur-sm">
                <svg class="w-5 h-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                </svg>
                <div>
                    <p class="text-lg font-bold text-white"><?= $totalRoles ?></p>
                    <p class="text-[11px] text-white/70 font-medium">Peran</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Filter (Mobile) -->
<div class="md:hidden mb-6">
    <div class="relative">
        <input type="text" 
               id="module-search-mobile"
               placeholder="Cari modul..."
               class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium"
               aria-label="Cari modul">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
        </svg>
    </div>
</div>

<!-- Section Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-lg font-bold text-primary-900">Modul Tersedia</h3>
        <p class="text-sm text-slate-500"><?= count($modules) ?> modul yang dapat Anda akses</p>
    </div>
</div>

<?php
$ungroupedDashModules = [];
$groupedDashModules = [];

foreach ($modules as $module) {
    $group = $module['module_group'] ?? '';
    if (empty($group)) {
        $ungroupedDashModules[] = $module;
    } else {
        $groupedDashModules[$group][] = $module;
    }
}
?>

<!-- Ungrouped Modules -->
<?php if (!empty($ungroupedDashModules)): ?>
    <div class="mb-8">
        <div id="modules-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <?php foreach ($ungroupedDashModules as $index => $module): ?>
                <?php include TEMPLATES_PATH . '/components/module-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Grouped Modules -->
<?php foreach ($groupedDashModules as $groupName => $grpModules): ?>
    <div class="mb-8">
        <h4 class="text-md font-bold text-slate-700 mb-4 pb-2 border-b border-slate-200"><?= e($groupName) ?></h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <?php foreach ($grpModules as $index => $module): ?>
                <?php include TEMPLATES_PATH . '/components/module-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<!-- Empty State -->
<?php if (empty($modules)): ?>
<div class="text-center py-16">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-50 mb-4">
        <svg class="w-8 h-8 text-primary-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"/>
        </svg>
    </div>
    <h4 class="text-lg font-semibold text-primary-900 mb-2">Belum ada modul</h4>
    <p class="text-sm text-slate-500">Hubungi administrator untuk mendapatkan akses ke modul.</p>
</div>
<?php endif; ?>
