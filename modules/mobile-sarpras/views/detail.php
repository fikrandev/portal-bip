<div class="px-4 pt-4 space-y-6">
    <!-- Asset Image / Info Card -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden relative">
        <div class="h-32 bg-blue-600 relative">
            <?php if (!empty($barang['foto'])): ?>
                <img src="<?= asset('storage/sarpras/' . $barang['foto']) ?>" class="w-full h-full object-cover opacity-50 mix-blend-overlay">
            <?php else: ?>
                <div class="absolute inset-0 opacity-20 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgo8cmVjdCB3aWR0aD0iOCIgaGVpZ2h0PSI4IiBmaWxsPSIjZmZmIj48L3JlY3Q+CjxwYXRoIGQ9Ik0wIDBMOCA4Wk04IDBMMCA4WiIgc3Ryb2tlPSIjMDAwIiBzdHJva2Utd2lkdGg9IjEiPjwvcGF0aD4KPC9zdmc+')]"></div>
            <?php endif; ?>
            <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between">
                <div>
                    <span class="px-2 py-1 bg-white/20 backdrop-blur-md rounded-lg text-[10px] font-bold text-white uppercase tracking-wider mb-2 inline-block"><?= e($barang['nama_kategori'] ?? 'Aset') ?></span>
                    <h2 class="text-xl font-bold text-white drop-shadow-md leading-tight"><?= e($barang['nama_barang']) ?></h2>
                </div>
            </div>
        </div>
        <div class="p-4 grid grid-cols-2 gap-4 border-b border-slate-100">
            <div>
                <span class="block text-[10px] text-slate-400 font-bold uppercase">Kode Aset</span>
                <span class="block text-sm font-bold text-slate-800 font-mono"><?= e($barang['kode_barang']) ?></span>
            </div>
            <div>
                <span class="block text-[10px] text-slate-400 font-bold uppercase">Kondisi</span>
                <?php if ($barang['kondisi'] === 'Baik'): ?>
                    <span class="inline-flex items-center gap-1 text-sm font-bold text-emerald-600"><i data-lucide="check-circle" class="w-4 h-4"></i> Baik</span>
                <?php elseif ($barang['kondisi'] === 'Rusak Ringan'): ?>
                    <span class="inline-flex items-center gap-1 text-sm font-bold text-amber-600"><i data-lucide="alert-triangle" class="w-4 h-4"></i> Rusak Ringan</span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 text-sm font-bold text-rose-600"><i data-lucide="x-circle" class="w-4 h-4"></i> Rusak Berat</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="p-4 bg-slate-50/50 space-y-3">
            <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Total Stok</span>
                <span class="font-bold text-slate-800"><?= $barang['jumlah'] ?> <?= e($barang['satuan']) ?></span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Tersedia</span>
                <span class="font-bold text-blue-600"><?= $barang['jumlah'] - ($barang['dipakai'] ?? 0) ?> <?= e($barang['satuan']) ?></span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Tahun Beli</span>
                <span class="font-bold text-slate-800"><?= e($barang['tahun_pengadaan'] ?? '-') ?></span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-slate-500">Sumber Dana</span>
                <span class="font-bold text-slate-800"><?= e($barang['sumber_dana'] ?? '-') ?></span>
            </div>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="flex gap-2">
        <a href="<?= url('mobile-sarpras/maintenance/tambah?barang_id=' . $barang['id']) ?>" class="flex-1 bg-white border border-slate-200 rounded-2xl p-3 flex items-center justify-center gap-2 shadow-sm text-slate-700 hover:bg-slate-50 active:bg-slate-100 transition">
            <i data-lucide="wrench" class="w-5 h-5 text-amber-500"></i>
            <span class="text-xs font-bold">Lapor Rusak</span>
        </a>
        <a href="<?= url('mobile-sarpras/peminjaman/tambah?barang_id=' . $barang['id']) ?>" class="flex-1 bg-white border border-slate-200 rounded-2xl p-3 flex items-center justify-center gap-2 shadow-sm text-slate-700 hover:bg-slate-50 active:bg-slate-100 transition">
            <i data-lucide="arrow-right-left" class="w-5 h-5 text-blue-500"></i>
            <span class="text-xs font-bold">Pinjam Aset</span>
        </a>
    </div>

    <!-- Maintenance History -->
    <div>
        <h3 class="text-sm font-bold text-slate-800 mb-3 uppercase tracking-wider">Riwayat Perbaikan</h3>
        <?php if (empty($maintenance)): ?>
            <div class="bg-white rounded-3xl border border-slate-100 p-8 flex flex-col items-center text-center shadow-sm">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-3">
                    <i data-lucide="shield-check" class="w-8 h-8"></i>
                </div>
                <h4 class="font-bold text-slate-700">Tidak ada riwayat perbaikan</h4>
                <p class="text-xs text-slate-500 mt-1">Aset ini belum pernah dilaporkan rusak.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($maintenance as $m): ?>
                    <div class="bg-white rounded-3xl border border-slate-100 p-4 shadow-sm relative overflow-hidden">
                        <?php if ($m['status'] === 'Selesai'): ?>
                            <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-50 rounded-bl-full flex items-start justify-end p-3">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i>
                            </div>
                        <?php elseif ($m['status'] === 'Dalam Perbaikan'): ?>
                            <div class="absolute top-0 right-0 w-16 h-16 bg-amber-50 rounded-bl-full flex items-start justify-end p-3">
                                <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                            </div>
                        <?php else: ?>
                            <div class="absolute top-0 right-0 w-16 h-16 bg-slate-50 rounded-bl-full flex items-start justify-end p-3">
                                <i data-lucide="pause" class="w-4 h-4 text-slate-400"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="pr-8">
                            <h4 class="font-bold text-slate-800 text-sm mb-1"><?= e($m['deskripsi_kerusakan']) ?></h4>
                            <p class="text-xs text-slate-500 mb-2">Dilaporkan oleh: <?= e($m['dilaporkan_oleh'] ?: 'Admin') ?></p>
                            
                            <div class="flex items-center gap-3 text-[10px] font-bold">
                                <span class="bg-slate-100 text-slate-500 px-2 py-1 rounded-md flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3 h-3"></i> <?= date('d/m/Y', strtotime($m['tanggal_lapor'])) ?>
                                </span>
                                <?php if ($m['biaya'] > 0): ?>
                                    <span class="text-rose-500 flex items-center gap-1">
                                        <i data-lucide="receipt" class="w-3 h-3"></i> Rp <?= number_format($m['biaya'], 0, ',', '.') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
