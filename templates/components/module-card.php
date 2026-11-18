<?php
/**
 * Module Card Component
 *
 * Reusable card used on the dashboard to display available modules.
 *
 * Variables:
 * @param array $module - Module data with: name, slug, description, icon_svg, route
 * @param int   $index  - Card index (unused; staggered delay removed for instant rendering)
 */

$module = $module ?? [];
$index  = $index ?? 0;
?>

<a href="<?= url($module['route'] ?? '#') ?>"
   id="card-<?= e($module['slug'] ?? 'unknown') ?>"
   class="module-card group flex flex-col relative overflow-hidden rounded-2xl bg-white border border-slate-200/80 shadow-xs
          hover:shadow-lg hover:shadow-primary-500/10 hover:border-primary-300 hover:-translate-y-0.5"
   aria-label="Buka modul <?= e($module['name'] ?? '') ?>">

    <!-- Top Accent Bar (Unified Portal Primary) -->
    <div class="h-1 w-full bg-gradient-to-r from-primary-600 via-primary-500 to-sky-400"></div>

    <!-- Card Content -->
    <div class="p-4 sm:p-5 flex flex-col h-full">
        <!-- Unified Icon Container -->
        <div class="flex items-center justify-center w-11 h-11 rounded-xl mb-3.5 bg-primary-50 text-primary-600 border border-primary-100/80 shadow-2xs transition-all duration-200 group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 group-hover:scale-105 shrink-0">
            <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 transition-colors duration-200">
                <?= $module['icon_svg'] ?? '' ?>
            </span>
        </div>

        <!-- Unified Title -->
        <h3 class="text-sm sm:text-[15px] font-bold text-slate-800 tracking-tight leading-snug mb-1.5 group-hover:text-primary-700 transition-colors">
            <?= e($module['name'] ?? 'Module') ?>
        </h3>

        <!-- Unified Description -->
        <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 flex-1">
            <?= e($module['description'] ?? '') ?>
        </p>

        <!-- Action Footer -->
        <div class="flex items-center gap-1.5 mt-3.5 pt-2.5 border-t border-slate-100 text-xs font-semibold text-primary-600 group-hover:text-primary-700 transition-colors">
            <span>Buka Modul</span>
            <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
            </svg>
        </div>
    </div>
</a>
