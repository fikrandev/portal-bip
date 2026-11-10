<div class="px-4 pt-4 pb-24 h-full overflow-y-auto">
    <div class="mb-4 sticky top-0 bg-slate-50 pt-2 pb-3 z-10 space-y-2.5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Data Ruangan</h2>
                <p class="text-xs text-slate-500 font-medium">Menampilkan <span id="mobileRuanganCount"><?= count($ruangan) ?></span> ruangan</p>
            </div>
        </div>
        <div class="relative">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" id="searchMobileRuangan" placeholder="Cari kelas / ruangan / gedung..." class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs font-medium text-slate-700 outline-none focus:border-blue-500 shadow-2xs">
        </div>
    </div>

    <!-- Data List -->
    <div class="space-y-3" id="mobileRuanganList">
        <?php if (empty($ruangan)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-sm mt-10">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="door-open" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Belum Ada Data Ruangan</h3>
                <p class="text-xs text-slate-500 mt-1">Silakan klik tombol + untuk menambah data baru.</p>
            </div>
        <?php else: ?>
            <?php foreach ($ruangan as $r): 
                $gedungName = $r['nama_bangunan'] ?? $r['lokasi_gedung'] ?? '-';
                $totalBarang = (int)($r['total_barang'] ?? 0);
                $totalQty = (int)($r['total_qty'] ?? 0);
                $searchKeywords = strtolower(($r['nama_ruangan'] ?? '') . ' ' . ($r['kode_ruangan'] ?? '') . ' ' . $gedungName . ' ' . ($r['jenis_ruangan'] ?? '') . ' ' . ($r['unit'] ?? ''));
            ?>
                <div class="mobile-ruangan-card block bg-white p-4 rounded-3xl shadow-sm border border-slate-100" data-search="<?= e($searchKeywords) ?>">
                    <div class="flex gap-3.5">
                        <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id']) ?>" class="w-14 h-14 bg-purple-50 rounded-2xl flex flex-col items-center justify-center flex-shrink-0 border border-purple-100 active:scale-95 transition-transform">
                            <i data-lucide="door-closed" class="w-6 h-6 text-purple-600"></i>
                            <span class="text-[9px] font-bold text-purple-700 mt-0.5"><?= e($r['unit'] ?? 'SD') ?></span>
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id']) ?>" class="font-bold text-slate-800 text-sm leading-tight truncate hover:text-blue-600">
                                    <?= e($r['nama_ruangan']) ?>
                                </a>
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100 shrink-0">
                                    <?= $totalBarang ?> Aset &bull; <?= $totalQty ?> Unit
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 truncate mt-0.5">Gedung: <?= e($gedungName) ?></p>
                            
                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold bg-purple-50 text-purple-700 border-purple-100/50">
                                    Lantai <?= e($r['posisi_lantai'] ?? $r['lantai'] ?? '1') ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold bg-slate-50 text-slate-600 border-slate-200">
                                    <?= floatval($r['luas'] ?? $r['luas_ruangan'] ?? 0) ?> m&sup2;
                                </span>
                                <?php if (!empty($r['jenis_ruangan'])): ?>
                                    <span class="px-2 py-0.5 rounded-lg border text-[10px] font-semibold bg-slate-50 text-slate-500 border-slate-200">
                                        <?= e($r['jenis_ruangan']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-100">
                        <a href="<?= url('mobile-sarpras/inventaris/tambah?ruangan_id=' . $r['id']) ?>" class="py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-xs transition-all">
                            <i data-lucide="package-plus" class="w-3.5 h-3.5"></i>
                            <span>+ Input Barang</span>
                        </a>
                        <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id']) ?>" class="py-2 px-3 rounded-xl bg-blue-50 hover:bg-blue-100 active:scale-98 text-blue-700 border border-blue-200 text-xs font-bold flex items-center justify-center gap-1.5 transition-all">
                            <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                            <span>Aset Ruang (<?= $totalBarang ?>)</span>
                        </a>
                    </div>

                    <div class="flex items-center justify-end gap-2 mt-2 pt-2 border-t border-slate-50">
                        <a href="<?= url('mobile-sarpras/ruangan/edit/' . $r['id']) ?>" class="px-2.5 py-1 rounded-lg border text-[10px] font-bold bg-slate-50 text-slate-600 border-slate-200 flex items-center gap-1 active:bg-slate-100">
                            <i data-lucide="edit" class="w-3 h-3"></i> Edit Ruang
                        </a>
                        <form action="<?= url('kelola-sarpras/ruangan/delete/' . $r['id']) ?>" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus ruangan ini? Stok barang akan disesuaikan.');">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="return_to" value="<?= url('mobile-sarpras/ruangan') ?>">
                            <button type="submit" class="px-2.5 py-1 rounded-lg border text-[10px] font-bold bg-rose-50 text-rose-600 border-rose-100 flex items-center gap-1 active:bg-rose-100">
                                <i data-lucide="trash-2" class="w-3 h-3"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Floating Action Button (FAB) for adding new room -->
<div class="fixed bottom-28 right-5 z-50">
    <a href="<?= url('mobile-sarpras/ruangan/tambah') ?>" class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-blue-500/50 shadow-xl flex items-center justify-center hover:bg-blue-700 active:scale-95 transition-all border-4 border-slate-50" title="Tambah Ruangan Baru">
        <i data-lucide="plus" class="w-7 h-7"></i>
    </a>
</div>

<script>
document.getElementById('searchMobileRuangan')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    const cards = document.querySelectorAll('.mobile-ruangan-card');
    let visible = 0;
    cards.forEach(card => {
        const match = !q || (card.getAttribute('data-search') || '').includes(q);
        card.style.display = match ? 'block' : 'none';
        if (match) visible++;
    });
    const countEl = document.getElementById('mobileRuanganCount');
    if (countEl) countEl.textContent = visible;
});
</script>
