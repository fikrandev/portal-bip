<?php
/**
 * SPMB Reusable Pagination Bar
 * Portal BIP
 *
 * Parameters passed from view:
 * @var int $currentPage (or $page)
 * @var int $totalPages
 * @var string $perPageParam ('50', '75', '100', '125', 'semua')
 * @var int $totalRows (or $totalItems)
 * @var array $filters
 * @var string $baseRoute
 */

$currentPage  = (int)($currentPage ?? ($page ?? 1));
$totalPages   = max(1, (int)($totalPages ?? 1));
$perPageParam = (string)($perPageParam ?? '50');
$totalRows    = (int)($totalRows ?? ($totalItems ?? 0));
$filters      = is_array($filters ?? null) ? $filters : [];
$baseRoute    = $baseRoute ?? 'kelola-spmb';

// Query string builders for Prev and Next (preserving active filters)
$prevQuery = http_build_query(array_merge($filters, [
    'page'     => max(1, $currentPage - 1),
    'per_page' => $perPageParam
]));
$nextQuery = http_build_query(array_merge($filters, [
    'page'     => min($totalPages, $currentPage + 1),
    'per_page' => $perPageParam
]));

$hasPrev = ($currentPage > 1);
$hasNext = ($currentPage < $totalPages && $perPageParam !== 'semua');
?>

<div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
    <!-- Left: Per Page Dropdown -->
    <div class="flex items-center gap-2 text-slate-500 order-2 sm:order-1">
        <label for="select_spmb_per_page" class="font-medium text-slate-600">Tampilkan:</label>
        <div class="relative">
            <select id="select_spmb_per_page" onchange="spmbChangePerPage(this.value)" 
                    class="appearance-none pl-3 pr-8 py-1.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-800 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none text-xs cursor-pointer shadow-2xs">
                <?php foreach (['50' => '50', '75' => '75', '100' => '100', '125' => '125', 'semua' => 'Semua'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($perPageParam === (string)$val) ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </div>
        </div>
        <span class="text-slate-400">data per halaman</span>
    </div>

    <!-- Right: Page Info & Previous / Next Buttons -->
    <div class="flex items-center gap-3 order-1 sm:order-2">
        <div class="text-slate-500 text-right">
            Halaman <strong class="text-slate-800"><?= $currentPage ?></strong> dari <strong class="text-slate-800"><?= $totalPages ?></strong>
            <?php if ($totalRows > 0): ?>
                <span class="text-slate-400 hidden md:inline font-normal">(Total <?= number_format($totalRows) ?> data)</span>
            <?php endif; ?>
        </div>

        <div class="inline-flex items-center gap-1.5">
            <?php if ($hasPrev): ?>
                <a href="<?= url($baseRoute . '?' . $prevQuery) ?>" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    <span>Sebelumnya</span>
                </a>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200/70 bg-slate-100/70 text-slate-300 font-bold text-xs cursor-not-allowed select-none">
                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                    <span>Sebelumnya</span>
                </span>
            <?php endif; ?>

            <?php if ($hasNext): ?>
                <a href="<?= url($baseRoute . '?' . $nextQuery) ?>" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors shadow-2xs">
                    <span>Selanjutnya</span>
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200/70 bg-slate-100/70 text-slate-300 font-bold text-xs cursor-not-allowed select-none">
                    <span>Selanjutnya</span>
                    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
if (typeof spmbChangePerPage !== 'function') {
    function spmbChangePerPage(val) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', val);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
}
</script>

