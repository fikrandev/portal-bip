<?php
/**
 * Module Card Component
 * 
 * Reusable card used on the dashboard to display available modules.
 * 
 * Variables:
 * @param array $module - Module data with: name, slug, description, icon_svg, color, route
 * @param int   $index  - Card index for staggered animation
 */

$module = $module ?? [];
$index  = $index ?? 0;
$delay  = $index * 80; // stagger delay in ms
?>

<a href="<?= url($module['route'] ?? '#') ?>" 
   id="card-<?= e($module['slug'] ?? 'unknown') ?>"
   class="module-card group block relative overflow-hidden rounded-2xl bg-white border border-primary-100/60 shadow-sm 
          hover:shadow-xl hover:shadow-primary-200/40 hover:border-primary-200 hover:-translate-y-1 
          transition-all duration-300 ease-out opacity-0 translate-y-4"
   style="animation: cardFadeIn 0.5s ease-out <?= $delay ?>ms forwards;"
   aria-label="Buka modul <?= e($module['name'] ?? '') ?>">
    
    <!-- Top Gradient Border -->
    <div class="h-1 w-full bg-gradient-to-r" style="from: <?= e($module['color'] ?? '#0EA5E9') ?>; background: linear-gradient(90deg, <?= e($module['color'] ?? '#0EA5E9') ?>, <?= e($module['color'] ?? '#0EA5E9') ?>88);"></div>
    
    <!-- Card Content -->
    <div class="p-4">
        <!-- Icon -->
        <div class="flex items-center justify-center w-10 h-10 rounded-xl mb-3 transition-transform duration-300 group-hover:scale-110"
             style="background: linear-gradient(135deg, <?= e($module['color'] ?? '#0EA5E9') ?>15, <?= e($module['color'] ?? '#0EA5E9') ?>25);">
            <span class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 transition-colors duration-300" 
                  style="color: <?= e($module['color'] ?? '#0EA5E9') ?>;">
                <?= $module['icon_svg'] ?? '' ?>
            </span>
        </div>
        
        <!-- Title -->
        <h3 class="text-sm font-bold text-primary-900 mb-1 group-hover:text-primary-700 transition-colors">
            <?= e($module['name'] ?? 'Module') ?>
        </h3>
        
        <!-- Description -->
        <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">
            <?= e($module['description'] ?? '') ?>
        </p>
        
        <!-- Action Arrow -->
        <div class="flex items-center gap-1.5 mt-3 text-[11px] font-semibold text-primary-500 group-hover:text-primary-600 transition-colors">
            <span>Buka Modul</span>
            <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
            </svg>
        </div>
    </div>
    
    <!-- Hover Ripple Effect -->
    <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"
         style="background: radial-gradient(circle at var(--mouse-x, 50%) var(--mouse-y, 50%), <?= e($module['color'] ?? '#0EA5E9') ?>08 0%, transparent 60%);">
    </div>
</a>
