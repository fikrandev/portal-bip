<?php
/**
 * Grup Penugasan Pegawai - List View (Mendukung Multi-Aktif: Yayasan, PAUD, SD, SMP, SMA, dll.)
 */
?>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800"><?= e($pageTitle) ?></h1>
        <p class="text-sm text-slate-500 mt-1">Kelola grup/periode SK pembagian tugas dan jabatan pegawai (bisa aktif lebih dari 5 grup unit bersamaan).</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="<?= url('kelola-pegawai') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:text-primary-600 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Data Pegawai
        </a>
        <a href="<?= url('kelola-pegawai/penugasan/grup/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold rounded-xl shadow-lg shadow-primary-500/25 transition-all text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Grup Penugasan
        </a>
    </div>
</div>

<?php if (!empty($activeGrups)): ?>
<!-- Banner Multi-Grup Aktif -->
<div class="mb-6 p-6 rounded-3xl bg-gradient-to-br from-emerald-600 via-teal-700 to-slate-900 text-white shadow-xl shadow-emerald-600/20 relative overflow-hidden">
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/10 pb-4 mb-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-2xl shrink-0 shadow-inner">
                ⚡
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-400 text-emerald-950 mb-1 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-950 animate-ping"></span>
                    <?= count($activeGrups) ?> GRUP PENUGASAN AKTIF BERSAMAAN
                </div>
                <h2 class="text-lg font-bold text-white">Status Penugasan Berjalan di Seluruh Unit</h2>
                <p class="text-xs text-emerald-100/90 mt-0.5">
                    Grup unit (Yayasan, PAUD, SD, SMP, SMA, dll.) berjalan aktif secara paralel untuk penetapan jabatan dan cetak SK.
                </p>
            </div>
        </div>
    </div>

    <!-- Grid Pill Grup Aktif -->
    <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
        <?php foreach ($activeGrups as $ag): ?>
            <div class="bg-white/10 backdrop-blur-md border border-white/15 hover:bg-white/15 rounded-2xl p-3.5 transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <span class="px-2 py-0.5 rounded-md bg-emerald-400/20 text-emerald-300 font-bold text-[10px] uppercase">
                            AKTIF
                        </span>
                        <span class="text-[10px] font-mono text-emerald-200">
                            <?= (int)$ag['total_pegawai'] ?> Staf
                        </span>
                    </div>
                    <h3 class="font-bold text-white text-xs leading-snug line-clamp-2"><?= e($ag['nama_grup']) ?></h3>
                    <p class="text-[10.5px] text-emerald-100/70 font-mono mt-1 truncate">SK: <?= e($ag['no_sk'] ?: '-') ?></p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-white/10 flex items-center justify-between gap-2">
                    <a href="<?= url('kelola-pegawai/penugasan/grup/' . $ag['id']) ?>" class="text-[11px] font-bold text-white hover:text-emerald-300 flex items-center gap-1 transition-colors">
                        Kelola →
                    </a>
                    <a href="<?= url('kelola-pegawai/penugasan/grup/' . $ag['id'] . '/cetak') ?>" target="_blank" class="text-[11px] font-bold text-amber-300 hover:text-amber-200" title="Cetak SK">
                        🖨️ SK
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Filters and Actions -->
<div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6">
    <form action="" method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>
            <input type="text" name="search" value="<?= e($search) ?>" 
                   placeholder="Cari nama grup atau No SK..." 
                   class="block w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors">
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm">
                Cari
            </button>
            <?php if ($search): ?>
                <a href="<?= url('kelola-pegawai/penugasan') ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl transition-all">
                    Reset
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Grid Cards Grup Penugasan -->
<?php if (empty($grupList)): ?>
<div class="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-sm">
    <div class="w-16 h-16 mx-auto rounded-full bg-primary-50 text-primary-500 flex items-center justify-center text-2xl mb-4">
        📂
    </div>
    <h3 class="text-base font-bold text-slate-800 mb-1">Belum Ada Grup Penugasan</h3>
    <p class="text-xs text-slate-500 max-w-md mx-auto mb-5">Buat grup penugasan seperti "Pembagian Tugas 2026/2027 Unit SD" untuk mulai menugaskan pegawai.</p>
    <a href="<?= url('kelola-pegawai/penugasan/grup/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-600/20 transition-all">
        Buat Grup Penugasan Baru
    </a>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($grupList as $g): ?>
    <div class="bg-white rounded-3xl border <?= $g['is_active'] ? 'border-emerald-400 ring-2 ring-emerald-500/20 shadow-lg shadow-emerald-500/10' : 'border-slate-200/80 shadow-sm hover:shadow-md' ?> p-6 flex flex-col justify-between transition-all">
        
        <div>
            <!-- Header Grup -->
            <div class="flex items-start justify-between gap-3 mb-4">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $g['is_active'] ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>">
                        <?= $g['is_active'] ? '● AKTIF' : 'NONAKTIF' ?>
                    </span>
                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-primary-50 text-primary-700 border border-primary-100">
                        <?= e($g['semester']) ?>
                    </span>
                </div>
                
                <!-- Dropdown Actions -->
                <div class="flex items-center gap-1">
                    <a href="<?= url('kelola-pegawai/penugasan/grup/' . $g['id'] . '/cetak') ?>" target="_blank" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Cetak SK">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                        </svg>
                    </a>
                    <a href="<?= url('kelola-pegawai/penugasan/grup/edit/' . $g['id']) ?>" class="p-1.5 text-slate-400 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors" title="Edit Grup">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                    </a>
                    <form method="POST" action="<?= url('kelola-pegawai/penugasan/grup/delete/' . $g['id']) ?>" onsubmit="AppNotif.confirm(event, this, 'Hapus Grup', 'Yakin ingin menghapus grup ini beserta seluruh anggota penugasannya?');">
                        <?= CSRF::field() ?>
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Grup">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Title & Info -->
            <h3 class="text-base font-bold text-slate-900 mb-2"><?= e($g['nama_grup']) ?></h3>
            <div class="space-y-1.5 text-xs text-slate-500 mb-5">
                <?php if ($g['no_sk']): ?>
                    <p class="flex items-center gap-1.5">
                        <span class="text-slate-400">📄 No. SK:</span> <span class="font-medium text-slate-700"><?= e($g['no_sk']) ?></span>
                    </p>
                <?php endif; ?>
                <p class="flex items-center gap-1.5">
                    <span class="text-slate-400">📅 Periode:</span> 
                    <span class="font-medium text-slate-700">
                        <?= !empty($g['tmt_mulai']) ? date('d/m/Y', strtotime($g['tmt_mulai'])) : '-' ?> 
                        s/d 
                        <?= !empty($g['tst_selesai']) ? date('d/m/Y', strtotime($g['tst_selesai'])) : 'Sekarang' ?>
                    </span>
                </p>
                <?php if ($g['keterangan']): ?>
                    <p class="text-[11px] text-slate-400 italic line-clamp-2"><?= e($g['keterangan']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer Card -->
        <div class="pt-4 border-t border-slate-100 flex flex-col gap-3">
            <div class="flex items-center justify-between text-xs">
                <span class="text-slate-500">Anggota Pegawai:</span>
                <span class="font-bold text-primary-700 bg-primary-50 px-2.5 py-0.5 rounded-full font-mono border border-primary-100">
                    <?= (int)$g['total_pegawai'] ?> Orang
                </span>
            </div>

            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-pegawai/penugasan/grup/' . $g['id']) ?>" class="flex-1 py-2 text-center bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold rounded-xl transition-colors">
                    Kelola Anggota (<?= (int)$g['total_pegawai'] ?>)
                </a>

                <a href="<?= url('kelola-pegawai/penugasan/grup/' . $g['id'] . '/cetak') ?>" target="_blank" class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow transition-all flex items-center gap-1" title="Cetak SK Penugasan">
                    <span>🖨️ SK</span>
                </a>

                <?php if ($g['is_active']): ?>
                <!-- Tombol Nonaktifkan -->
                <form method="POST" action="<?= url('kelola-pegawai/penugasan/grup/toggle-aktif/' . $g['id']) ?>" class="shrink-0" onsubmit="AppNotif.confirm(event, this, 'Nonaktifkan Grup', 'Nonaktifkan grup ini? Data jabatan pegawai akan disesuaikan dengan grup aktif lainnya.');">
                    <?= CSRF::field() ?>
                    <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 text-xs font-bold rounded-xl border border-slate-200 hover:border-rose-200 transition-all flex items-center gap-1" title="Nonaktifkan Grup">
                        <span>🛑 Nonaktif</span>
                    </button>
                </form>
                <?php else: ?>
                <!-- Tombol Aktifkan -->
                <form method="POST" action="<?= url('kelola-pegawai/penugasan/grup/toggle-aktif/' . $g['id']) ?>" class="shrink-0" onsubmit="AppNotif.confirm(event, this, 'Aktifkan Grup Penugasan', 'Aktifkan grup ini? Grup ini akan berjalan aktif bersamaan dengan grup unit aktif lainnya (Yayasan, PAUD, SD, SMP, SMA).');">
                    <?= CSRF::field() ?>
                    <button type="submit" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition-all flex items-center gap-1" title="Aktifkan Grup Ini">
                        <span>⚡ Aktifkan</span>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
