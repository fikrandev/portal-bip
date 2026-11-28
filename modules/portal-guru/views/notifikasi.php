<?php
/**
 * Pusat Notifikasi Screen (Mobile PWA)
 * Menampilkan feed real-time dari modul Pusat Notifikasi (Push & Popup).
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
    <button type="button" onclick="markAllMobileNotifRead(this)" class="text-xs font-semibold text-primary-600 hover:text-primary-700 cursor-pointer">
        Tandai Dibaca
    </button>
</div>

<div class="p-4 space-y-3" id="mobile-notif-list">
    <?php if (empty($notifications)): ?>
        <div class="bg-white rounded-3xl p-8 text-center shadow-sm border border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="bell-off" class="w-6 h-6"></i>
            </div>
            <h4 class="font-bold text-sm text-slate-800">Belum Ada Notifikasi</h4>
            <p class="text-xs text-slate-500 mt-1">Pemberitahuan penting dan pengumuman akan muncul di sini secara real-time.</p>
        </div>
    <?php else: ?>
        <?php foreach ($notifications as $n):
            $isUnread = !empty($n['unread']);
            $isPopup = (($n['tipe'] ?? '') === 'popup');
            $prio = $n['prioritas'] ?? 'info';
            $iconBg = $prio === 'urgent'
                ? 'bg-rose-100 text-rose-700'
                : ($prio === 'warning'
                    ? 'bg-amber-100 text-amber-700'
                    : ($prio === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-primary-100 text-primary-700'));
        ?>
        <div class="mobile-notif-card bg-white rounded-3xl p-4 shadow-sm border border-slate-100 flex items-start gap-3.5 <?= $isUnread ? 'ring-1 ring-primary-200 bg-primary-50/20' : '' ?>">
            <div class="w-10 h-10 rounded-2xl <?= $iconBg ?> flex items-center justify-center shrink-0">
                <i data-lucide="<?= $isPopup ? 'app-window' : 'bell-ring' ?>" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <h4 class="font-bold text-xs text-slate-900 leading-snug"><?= e($n['title']) ?></h4>
                    <span class="text-[10px] text-slate-400 shrink-0"><?= e($n['time']) ?></span>
                </div>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold <?= $isPopup ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-sky-50 text-sky-700 border border-sky-200' ?>">
                        <?= $isPopup ? 'Popup tiap ' . (int)($n['popup_interval_menit'] ?? 5) . ' mnt' : 'Push Notif' ?>
                    </span>
                    <?php if (!empty($n['created_by_name'])): ?>
                        <span class="text-[10px] text-slate-400 truncate">&bull; <?= e($n['created_by_name']) ?></span>
                    <?php endif; ?>
                </div>
                <p class="text-[11.5px] text-slate-600 mt-1.5 leading-relaxed whitespace-pre-line"><?= e($n['message']) ?></p>
                <?php if (!empty($n['link_url'])): ?>
                    <a href="<?= e($n['link_url']) ?>"
                       onclick="window.BipNotifCenter && window.BipNotifCenter.markClick(<?= (int)$n['id'] ?>)"
                       class="mt-2.5 inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-primary-600 text-white text-[11px] font-bold shadow-xs press-bounce">
                        <span><?= e($n['link_label'] ?: 'Buka Tautan') ?></span>
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
async function markAllMobileNotifRead(btn) {
    if (btn) btn.disabled = true;
    try {
        if (window.BipNotifCenter && typeof window.BipNotifCenter.markAllRead === 'function') {
            await window.BipNotifCenter.markAllRead();
        }
        document.querySelectorAll('.mobile-notif-card').forEach(el => {
            el.classList.remove('ring-1', 'ring-primary-200', 'bg-primary-50/20');
        });
        if (window.showToast) {
            window.showToast('Semua notifikasi telah ditandai dibaca', 'success');
        }
    } finally {
        if (btn) btn.disabled = false;
    }
}
</script>
