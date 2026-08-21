<?php
/**
 * Materi Pembelajaran Screen
 */
?>

<!-- Header -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="font-bold text-slate-800 text-base">Materi Pembelajaran</h2>
    </div>
    <button onclick="window.showToast('Membuka formulir upload materi...', 'info')" class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 press-bounce">
        <i data-lucide="plus" class="w-5 h-5"></i>
    </button>
</div>

<div class="p-4 space-y-3">
    <?php foreach ($materials as $m): ?>
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                <?= e($m['fileType']) ?>
            </div>
            <div class="min-w-0">
                <h4 class="font-bold text-xs text-slate-800 truncate"><?= e($m['title']) ?></h4>
                <p class="text-[10px] text-slate-400"><?= e($m['class']) ?> • <?= e($m['size']) ?> • <?= $m['downloads'] ?> Unduhan</p>
            </div>
        </div>
        <button onclick="window.showToast('Mengunduh materi: <?= e($m['title']) ?>', 'success')" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 hover:bg-slate-200 flex items-center justify-center press-bounce">
            <i data-lucide="download" class="w-4 h-4"></i>
        </button>
    </div>
    <?php endforeach; ?>
</div>
