<?php
/** Dashboard View — Module Cards Grid / Workspace Personal */
$isPersonalDash = class_exists('Auth') && Auth::isPersonalScope();
?>

<!-- Welcome Banner -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r <?= $isPersonalDash ? 'from-teal-600 via-primary-600 to-primary-500' : 'from-primary-500 via-primary-400 to-primary-400' ?> p-6 sm:p-8 mb-8 shadow-lg shadow-primary-500/20">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10" aria-hidden="true">
        <div class="absolute -top-12 -right-12 w-48 h-48 bg-white rounded-full"></div>
        <div class="absolute -bottom-8 -left-8 w-36 h-36 bg-white rounded-full"></div>
        <div class="absolute top-1/2 right-1/4 w-20 h-20 bg-white rounded-full"></div>
    </div>
    
    <div class="relative z-10">
        <div class="flex items-center gap-4 mb-2">
            <?php $dashAvatar = Auth::avatar(); ?>
            <?php if ($dashAvatar): ?>
                <img src="<?= e($dashAvatar) ?>" alt="<?= e(Auth::name()) ?>" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover border-2 border-white/70 shadow-md shrink-0">
            <?php else: ?>
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-white/20 backdrop-blur-sm border-2 border-white/40 text-white font-black text-xl flex items-center justify-center shadow-md shrink-0">
                    <?= Auth::initials() ?>
                </div>
            <?php endif; ?>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-0.5">
                    <p class="text-primary-100 text-xs sm:text-sm font-semibold tracking-wide">
                        Assalamu'alaikum,
                    </p>
                    <?php if ($isPersonalDash): ?>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white border border-white/30">
                            👤 Workspace Personal
                        </span>
                    <?php endif; ?>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-white mb-0.5">
                    Selamat datang, <?= e(Auth::name()) ?>! 👋
                </h2>
                <p class="text-primary-100 text-xs sm:text-sm">
                    <span class="font-semibold text-white/95"><?= e(Auth::jabatan()) ?></span> &bull;
                    <?= $isPersonalDash ? 'Gunakan menu sidebar di sebelah kiri untuk berpindah antar fitur dan data pribadi Anda secara cepat.' : 'Kelola semua modul dan fitur portal dari satu tempat.' ?>
                </p>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <?php if ($isPersonalDash): ?>
        <div class="flex flex-wrap gap-3 sm:gap-4 mt-5">
            <div class="flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/15">
                <svg class="w-4 h-4 text-teal-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                </svg>
                <span class="text-xs font-semibold text-white">Mode Otorisasi: Data Pribadi (Personal)</span>
            </div>
            <div class="flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/15">
                <svg class="w-4 h-4 text-teal-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                </svg>
                <span class="text-xs font-semibold text-white"><?= count($modules) ?> Modul Aktif di Sidebar</span>
            </div>
        </div>
        <?php else: ?>
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
        <?php endif; ?>
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
        <h3 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight">Modul Tersedia</h3>
        <p class="text-xs sm:text-sm text-slate-500"><?= count($modules) ?> modul yang dapat Anda akses</p>
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

<?php if (!empty($modules)): ?>
<div id="modules-grid" class="space-y-8">
    <!-- Ungrouped Modules -->
    <?php if (!empty($ungroupedDashModules)): ?>
        <div class="dashboard-group-section">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <?php foreach ($ungroupedDashModules as $index => $module): ?>
                    <?php include TEMPLATES_PATH . '/components/module-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Grouped Modules -->
    <?php foreach ($groupedDashModules as $groupName => $grpModules): ?>
        <div class="dashboard-group-section">
            <div class="flex items-center gap-2.5 mb-4 pb-2.5 border-b border-slate-200/80">
                <span class="w-2 h-5 rounded-full bg-primary-600 shrink-0"></span>
                <h4 class="text-sm sm:text-base font-bold text-slate-800 tracking-tight"><?= e($groupName) ?></h4>
                <span class="ml-auto px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-100"><?= count($grpModules) ?> Modul</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <?php foreach ($grpModules as $index => $module): ?>
                    <?php include TEMPLATES_PATH . '/components/module-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

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
