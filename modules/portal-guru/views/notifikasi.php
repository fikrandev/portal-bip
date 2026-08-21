<?php
/**
 * Pusat Notifikasi Screen
 * Lists reminders, announcements, and presence notifications.
 */
?>

<!-- Header -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="font-bold text-slate-800 text-base">Notifikasi</h2>
    </div>
    <button onclick="window.showToast('Semua notifikasi telah ditandai dibaca', 'success')" class="text-xs font-semibold text-blue-600 hover:text-blue-700">
        Tandai Dibaca
    </button>
</div>

<div class="p-4 space-y-3">
    <?php foreach ($notifications as $n): ?>
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex items-start gap-3.5 <?= $n['unread'] ? 'ring-1 ring-blue-100 bg-blue-50/20' : '' ?>">
        <div class="w-10 h-10 rounded-2xl <?= $n['type'] === 'absen' ? 'bg-emerald-100 text-emerald-700' : ($n['type'] === 'jadwal' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700') ?> flex items-center justify-center shrink-0">
            <i data-lucide="<?= $n['type'] === 'absen' ? 'map-pin' : ($n['type'] === 'jadwal' ? 'calendar' : 'megaphone') ?>" class="w-5 h-5"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <h4 class="font-bold text-xs text-slate-900 leading-snug"><?= e($n['title']) ?></h4>
                <span class="text-[10px] text-slate-400"><?= e($n['time']) ?></span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1 leading-relaxed"><?= e($n['message']) ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>
