<div class="px-4 pt-4 pb-24 h-full overflow-y-auto">
    <div class="mb-4 sticky top-0 bg-slate-50 pt-2 pb-3 z-10 space-y-3">
        <h2 class="text-xl font-bold text-slate-800">Data Peminjaman</h2>
        <p class="text-xs text-slate-500 font-medium">Menampilkan <?= count($peminjaman) ?> data</p>
    </div>

    <!-- Data List -->
    <div class="space-y-3">
        <?php if (empty($peminjaman)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-sm mt-10">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="arrow-right-left" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Belum Ada Data</h3>
                <p class="text-xs text-slate-500 mt-1">Silakan klik tombol + untuk menambah peminjaman baru.</p>
            </div>
        <?php else: ?>
            <?php foreach ($peminjaman as $p): ?>
                <?php 
                    $isOverdue = false;
                    $statusColor = 'slate';
                    $statusText = $p['status'];
                    
                    if ($p['status'] == 'Dipinjam') {
                        $statusColor = 'amber';
                        $tglRencana = strtotime($p['estimasi_kembali']);
                        $now = strtotime(date('Y-m-d'));
                        if ($tglRencana < $now) {
                            $isOverdue = true;
                            $statusColor = 'rose';
                            $statusText = 'Terlambat';
                        }
                    } elseif ($p['status'] == 'Kembali' || $p['status'] == 'Dikembalikan') {
                        $statusColor = 'emerald';
                        $statusText = 'Kembali';
                    }
                ?>
                <div class="block bg-white p-4 rounded-3xl shadow-sm border <?= $isOverdue ? 'border-rose-200' : 'border-slate-100' ?>">
                    <div class="flex gap-4">
                        <div class="w-16 h-16 bg-<?= $statusColor ?>-50 rounded-2xl flex items-center justify-center flex-shrink-0 border border-<?= $statusColor ?>-100">
                            <i data-lucide="arrow-right-left" class="w-7 h-7 text-<?= $statusColor ?>-500"></i>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="font-bold text-slate-800 text-sm leading-tight truncate"><?= e($p['nama_barang']) ?></h3>
                            <p class="text-xs font-semibold text-blue-600 mt-0.5 truncate"><?= e($p['nama_peminjam']) ?></p>
                            
                            <div class="flex flex-col gap-1 mt-2 text-[10px] text-slate-500">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                    <span>Pinjam: <?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></span>
                                </div>
                                <div class="flex items-center gap-1 <?= $isOverdue ? 'text-rose-600 font-bold' : '' ?>">
                                    <i data-lucide="calendar-clock" class="w-3 h-3"></i>
                                    <span>Tenggat: <?= date('d M Y', strtotime($p['estimasi_kembali'])) ?></span>
                                </div>
                                <?php if (($p['status'] == 'Kembali' || $p['status'] == 'Dikembalikan') && !empty($p['tanggal_kembali'])): ?>
                                <div class="flex items-center gap-1 text-emerald-600 font-bold">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i>
                                    <span>Kembali: <?= date('d M Y', strtotime($p['tanggal_kembali'])) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center gap-2 mt-2">
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold bg-<?= $statusColor ?>-50 text-<?= $statusColor ?>-700 border-<?= $statusColor ?>-100/50">
                                    <?= e($statusText) ?>
                                </span>
                                <?php if ($isOverdue): ?>
                                    <?php 
                                        $diff = $now - $tglRencana;
                                        $days = floor($diff / (60 * 60 * 24));
                                    ?>
                                    <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold bg-rose-50 text-rose-700 border-rose-100">
                                        Lewat <?= $days ?> Hari
                                    </span>
                                <?php endif; ?>
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
    <a href="<?= url('mobile-sarpras/peminjaman/tambah') ?>" class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-blue-500/50 shadow-xl flex items-center justify-center hover:bg-blue-700 active:scale-95 transition-all border-4 border-slate-50">
        <i data-lucide="plus" class="w-7 h-7"></i>
    </a>
</div>
