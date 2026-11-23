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
        <div class="mt-5 pt-4 border-t border-white/15 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/15">
                    <svg class="w-4 h-4 text-teal-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                    </svg>
                    <span class="text-xs font-semibold text-white">Rata-Rata Capaian Modul: <strong class="text-teal-200"><?= (int)($personalOverallProgress ?? 0) ?>%</strong></span>
                </div>
                <div class="flex items-center gap-2.5 px-3.5 py-2 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/15">
                    <svg class="w-4 h-4 text-teal-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                    </svg>
                    <span class="text-xs font-semibold text-white"><?= count($modules) ?> Modul Terhubung</span>
                </div>
            </div>

            <!-- Overall Progress Bar in Banner -->
            <div class="w-full sm:w-64 bg-white/15 rounded-xl p-2.5 border border-white/15 backdrop-blur-sm">
                <div class="flex items-center justify-between text-[11px] font-bold text-white mb-1">
                    <span>Indeks Progres Personal</span>
                    <span><?= (int)($personalOverallProgress ?? 0) ?>%</span>
                </div>
                <div class="w-full h-2 rounded-full bg-black/20 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-teal-300 to-emerald-300 transition-all duration-500"
                         style="width: <?= min(100, max(0, (int)($personalOverallProgress ?? 0))) ?>%"></div>
                </div>
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

<?php if ($isPersonalDash): ?>
    <!-- BERANDA PERSONAL: Progress & Ringkasan Dashboard Setiap Modul -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight">Progress & Ringkasan Dashboard Modul</h3>
            <p class="text-xs sm:text-sm text-slate-500">Pantau perkembangan administrasi, kehadiran, dan aktivitas pribadi Anda dari setiap modul</p>
        </div>
        <a href="<?= url('dashboard?refresh=1') ?>"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/90 text-xs font-semibold shadow-2xs transition-colors self-start sm:self-auto">
            <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
            <span>Perbarui Progress</span>
        </a>
    </div>

    <?php if (!empty($personalModuleWidgets)): ?>
        <?php
        $colorStyles = [
            'indigo' => [
                'accent' => 'from-indigo-500 to-indigo-600',
                'icon_bg' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
                'bar' => 'bg-gradient-to-r from-indigo-500 to-indigo-600',
                'btn' => 'hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200',
            ],
            'blue' => [
                'accent' => 'from-blue-500 to-primary-600',
                'icon_bg' => 'bg-blue-50 text-blue-600 border-blue-100',
                'badge' => 'bg-blue-50 text-blue-700 border-blue-200/80',
                'bar' => 'bg-gradient-to-r from-blue-500 to-primary-600',
                'btn' => 'hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200',
            ],
            'emerald' => [
                'accent' => 'from-emerald-500 to-teal-600',
                'icon_bg' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                'bar' => 'bg-gradient-to-r from-emerald-500 to-teal-500',
                'btn' => 'hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200',
            ],
            'teal' => [
                'accent' => 'from-teal-500 to-cyan-600',
                'icon_bg' => 'bg-teal-50 text-teal-600 border-teal-100',
                'badge' => 'bg-teal-50 text-teal-700 border-teal-200/80',
                'bar' => 'bg-gradient-to-r from-teal-500 to-cyan-500',
                'btn' => 'hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200',
            ],
            'amber' => [
                'accent' => 'from-amber-500 to-orange-500',
                'icon_bg' => 'bg-amber-50 text-amber-600 border-amber-100',
                'badge' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                'bar' => 'bg-gradient-to-r from-amber-500 to-orange-500',
                'btn' => 'hover:bg-amber-50 hover:text-amber-800 hover:border-amber-200',
            ],
            'cyan' => [
                'accent' => 'from-cyan-500 to-sky-600',
                'icon_bg' => 'bg-cyan-50 text-cyan-600 border-cyan-100',
                'badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200/80',
                'bar' => 'bg-gradient-to-r from-cyan-500 to-sky-500',
                'btn' => 'hover:bg-cyan-50 hover:text-cyan-700 hover:border-cyan-200',
            ],
            'purple' => [
                'accent' => 'from-purple-500 to-violet-600',
                'icon_bg' => 'bg-purple-50 text-purple-600 border-purple-100',
                'badge' => 'bg-purple-50 text-purple-700 border-purple-200/80',
                'bar' => 'bg-gradient-to-r from-purple-500 to-violet-500',
                'btn' => 'hover:bg-purple-50 hover:text-purple-700 hover:border-purple-200',
            ],
            'rose' => [
                'accent' => 'from-rose-500 to-pink-600',
                'icon_bg' => 'bg-rose-50 text-rose-600 border-rose-100',
                'badge' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                'bar' => 'bg-gradient-to-r from-rose-500 to-pink-500',
                'btn' => 'hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200',
            ],
            'primary' => [
                'accent' => 'from-primary-500 to-primary-600',
                'icon_bg' => 'bg-primary-50 text-primary-600 border-primary-100',
                'badge' => 'bg-primary-50 text-primary-700 border-primary-200/80',
                'bar' => 'bg-gradient-to-r from-primary-500 to-primary-600',
                'btn' => 'hover:bg-primary-50 hover:text-primary-700 hover:border-primary-200',
            ],
        ];
        ?>
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <?php foreach ($personalModuleWidgets as $w):
                $cKey = $w['color'] ?? 'primary';
                $cs = $colorStyles[$cKey] ?? $colorStyles['primary'];
                $pct = min(100, max(0, (int)($w['progress_pct'] ?? 0)));
            ?>
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">
                <!-- Top Accent Line -->
                <div class="h-1.5 w-full bg-gradient-to-r <?= $cs['accent'] ?>"></div>

                <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between space-y-5">
                    <!-- Header: Module Icon + Name + Live Status Badge -->
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-2xl border <?= $cs['icon_bg'] ?> flex items-center justify-center shrink-0 [&>svg]:w-5 [&>svg]:h-5">
                                    <?= $w['icon_svg'] ?? '' ?>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block"><?= e($w['group'] ?: 'Modul Personal') ?></span>
                                    <h4 class="text-base font-bold text-slate-800 truncate"><?= e($w['name']) ?></h4>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $cs['badge'] ?> shrink-0">
                                <?= e($w['status_badge'] ?? 'Aktif') ?>
                            </span>
                        </div>

                        <!-- Headline Value & Main Progress Bar -->
                        <div class="mt-4 p-4 rounded-xl bg-slate-50/90 border border-slate-200/70">
                            <div class="flex items-baseline justify-between gap-2 mb-1">
                                <span class="text-xs font-semibold text-slate-500"><?= e($w['progress_label'] ?? 'Capaian') ?></span>
                                <span class="text-lg sm:text-xl font-black text-slate-800"><?= e($w['headline_value'] ?? '-') ?></span>
                            </div>
                            <?php if (!empty($w['headline_sub'])): ?>
                                <p class="text-xs text-slate-500 mb-2.5"><?= e($w['headline_sub']) ?></p>
                            <?php endif; ?>

                            <div class="w-full h-2.5 rounded-full bg-slate-200/80 overflow-hidden">
                                <div class="h-full rounded-full <?= $cs['bar'] ?> transition-all duration-500"
                                     style="width: <?= $pct ?>%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold mt-1.5">
                                <span>Progres Capaian</span>
                                <span class="text-slate-700 font-bold"><?= $pct ?>%</span>
                            </div>
                        </div>

                        <!-- 4-Column Mini KPI Metrics Grid -->
                        <?php if (!empty($w['metrics'])): ?>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-4">
                            <?php foreach ($w['metrics'] as $m): ?>
                            <div class="px-3 py-2.5 rounded-xl bg-white border border-slate-200/80 text-center">
                                <p class="text-[11px] text-slate-400 font-semibold truncate"><?= e($m['label'] ?? '') ?></p>
                                <p class="text-xs sm:text-sm font-bold text-slate-800 mt-0.5 truncate"><?= e((string)($m['value'] ?? '-')) ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Sub-Progress Breakdown Bars -->
                        <?php if (!empty($w['breakdown']) && count($w['breakdown']) > 1): ?>
                        <div class="mt-4 space-y-2.5 pt-3 border-t border-slate-100">
                            <?php foreach ($w['breakdown'] as $bd):
                                $bdPct = min(100, max(0, (int)($bd['pct'] ?? 0)));
                            ?>
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="font-medium text-slate-600"><?= e($bd['label'] ?? '') ?></span>
                                    <span class="font-bold text-slate-700"><?= e($bd['text'] ?? ($bdPct . '%')) ?></span>
                                </div>
                                <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full <?= $cs['bar'] ?>" style="width: <?= $bdPct ?>%"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Footer Quick Actions -->
                    <?php if (!empty($w['actions'])): ?>
                    <div class="pt-3.5 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        <?php foreach ($w['actions'] as $idx => $act): ?>
                        <a href="<?= e($act['url'] ?? '#') ?>"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-colors <?= $idx === 0 ? 'bg-slate-800 text-white border-slate-800 hover:bg-slate-700' : 'bg-slate-50 text-slate-700 border-slate-200/90 ' . $cs['btn'] ?>">
                            <span><?= e($act['label'] ?? 'Buka') ?></span>
                            <svg class="w-3 h-3 opacity-75" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-slate-200/80">
            <h4 class="text-lg font-semibold text-primary-900 mb-2">Belum ada modul aktif</h4>
            <p class="text-sm text-slate-500">Hubungi administrator untuk mendapatkan akses ke modul.</p>
        </div>
    <?php endif; ?>

<?php else: ?>
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
<?php endif; ?>
