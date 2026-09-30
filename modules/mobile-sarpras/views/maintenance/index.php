<div class="px-4 pt-4 pb-24 h-full overflow-y-auto">
    <div class="mb-4 sticky top-0 bg-slate-50 pt-2 pb-3 z-10 space-y-3">
        <h2 class="text-xl font-bold text-slate-800">Data Perbaikan (Maintenance)</h2>
        <p class="text-xs text-slate-500 font-medium">Menampilkan <?= count($maintenance) ?> data</p>
    </div>

    <!-- Data List -->
    <div class="space-y-3">
        <?php if (empty($maintenance)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-sm mt-10">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="tool" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Belum Ada Data Perbaikan</h3>
                <p class="text-xs text-slate-500 mt-1">Silakan klik tombol + untuk menambah laporan perbaikan baru.</p>
            </div>
        <?php else: ?>
            <?php foreach ($maintenance as $m): ?>
                <div class="block bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <div class="flex gap-4">
                        <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center flex-shrink-0 border border-red-100">
                            <i data-lucide="wrench" class="w-7 h-7 text-red-500"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="font-bold text-slate-800 text-sm leading-tight truncate"><?= e($m['nama_barang']) ?></h3>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5 truncate"><?= e($m['kode_barang']) ?></p>
                            <p class="text-xs text-slate-500 line-clamp-2 mt-1"><?= e($m['deskripsi_kerusakan']) ?></p>
                            
                            <div class="flex items-center gap-2 mt-2">
                                <?php
                                $statusColor = 'slate';
                                if ($m['status'] == 'Menunggu') $statusColor = 'amber';
                                if ($m['status'] == 'Dalam Perbaikan') $statusColor = 'blue';
                                if ($m['status'] == 'Selesai') $statusColor = 'emerald';
                                ?>
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold bg-<?= $statusColor ?>-50 text-<?= $statusColor ?>-700 border-<?= $statusColor ?>-100/50">
                                    <?= e($m['status']) ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold bg-slate-50 text-slate-600 border-slate-200">
                                    <?= date('d M Y', strtotime($m['tanggal_lapor'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Floating Action Button (FAB) for adding new item -->
<div class="fixed bottom-28 right-5 z-50">
    <a href="<?= url('mobile-sarpras/maintenance/tambah') ?>" class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-blue-500/50 shadow-xl flex items-center justify-center hover:bg-blue-700 active:scale-95 transition-all border-4 border-slate-50">
        <i data-lucide="plus" class="w-7 h-7"></i>
    </a>
</div>
