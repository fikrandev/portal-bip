<div class="px-4 pt-4 pb-24 h-full overflow-y-auto">
    <!-- Header & Search -->
    <div class="mb-4 sticky top-0 bg-slate-50 pt-2 pb-3 z-10 space-y-2.5">
        <!-- Tombol Cepat: Tambah Inventaris Per Ruangan vs Umum -->
        <div class="grid grid-cols-2 gap-2">
            <?php if (!empty($selectedRuangan)): ?>
                <a href="<?= url('mobile-sarpras/ruangan/detail/' . $selectedRuangan['id'] . '?action=input-manual') ?>" class="col-span-2 py-2.5 px-3.5 rounded-2xl bg-emerald-600 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20 active:scale-98">
                    <i data-lucide="package-plus" class="w-4 h-4"></i>
                    <span>+ Input Inventaris di <?= e($selectedRuangan['nama_ruangan']) ?></span>
                </a>
            <?php else: ?>
                <button type="button" onclick="openModalPilihRuanganInventaris()" class="py-2.5 px-3 rounded-2xl bg-emerald-600 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm shadow-emerald-600/20 active:scale-98">
                    <i data-lucide="door-open" class="w-4 h-4"></i>
                    <span>+ Inventaris Per Ruang</span>
                </button>
                <a href="<?= url('mobile-sarpras/inventaris/tambah') ?>" class="py-2.5 px-3 rounded-2xl bg-blue-600 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm shadow-blue-600/20 active:scale-98">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>+ Inventaris Umum</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Search & Filter Form -->
        <form id="filterForm" action="<?= url('mobile-sarpras/inventaris') ?>" method="GET" class="space-y-2">
            <input type="hidden" name="kategori_id" id="kategoriInput" value="<?= e($filters['kategori_id'] ?? '') ?>">
            <div class="grid grid-cols-1 gap-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" class="w-full pl-9 pr-3 py-2.5 bg-white border border-slate-200 rounded-2xl text-xs outline-none focus:border-blue-500 shadow-2xs" placeholder="Cari nama / kode aset...">
                </div>
                <div class="flex items-center gap-2">
                    <select name="ruangan_id" onchange="document.getElementById('filterForm').submit()" class="flex-1 px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-blue-500">
                        <option value="0">📍 Semua Ruangan / Kelas</option>
                        <?php if (!empty($ruanganList)): ?>
                            <?php foreach ($ruanganList as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= (int)($filters['ruangan_id'] ?? 0) === (int)$r['id'] ? 'selected' : '' ?>>
                                    <?= e($r['nama_ruangan']) ?> (<?= (int)($r['total_barang'] ?? 0) ?> Aset)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <?php if (!empty($selectedRuangan)): ?>
                        <a href="<?= url('mobile-sarpras/ruangan/detail/' . $selectedRuangan['id']) ?>" class="px-3 py-2 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 text-xs font-bold shrink-0">
                            Detail Ruang
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <!-- Category Pills (Horizontal Scroll) -->
        <div class="overflow-x-auto hide-scrollbar pb-1 -mx-4 px-4 flex gap-2 snap-x">
            <button type="button" onclick="setKategori('')" class="snap-start shrink-0 px-3.5 py-1.5 rounded-full text-[11px] font-bold transition-all border <?= empty($filters['kategori_id']) ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200' ?>">
                Semua
            </button>
            <?php foreach ($kategoriList as $kat): ?>
                <button type="button" onclick="setKategori('<?= $kat['id'] ?>')" class="snap-start shrink-0 px-3.5 py-1.5 rounded-full text-[11px] font-bold transition-all border <?= ($filters['kategori_id'] ?? '') == $kat['id'] ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200' ?>">
                    <?= e($kat['nama_kategori']) ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <p class="text-[11px] text-slate-500 font-medium ml-1">Menampilkan <?= count($barang) ?> data aset<?= !empty($selectedRuangan) ? ' di ' . e($selectedRuangan['nama_ruangan']) : '' ?></p>
    </div>

    <!-- Data List -->
    <div class="space-y-3">
        <?php if (empty($barang)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-sm mt-6">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="package-open" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Data Tidak Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1 mb-4">Belum ada inventaris yang sesuai filter ini.</p>
                <button type="button" onclick="openModalPilihRuanganInventaris()" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Inventaris Per Ruangan</span>
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($barang as $b): ?>
                <div class="block bg-white p-4 rounded-3xl shadow-sm border border-slate-100 active:scale-[0.98] transition-transform">
                    <div class="flex gap-4">
                        <?php 
                        $fotoList = SarprasModel::getFotoList($b['foto'] ?? '');
                        $firstFoto = !empty($fotoList) ? $fotoList[0] : null;
                        $countFoto = count($fotoList);
                        ?>
                        <div class="relative w-16 h-16 bg-blue-50/50 rounded-2xl flex items-center justify-center flex-shrink-0 border border-blue-100/50 p-1">
                            <?php if ($firstFoto): ?>
                                <img src="<?= url(ltrim($firstFoto, '/')) ?>" alt="Foto" class="w-full h-full object-cover rounded-xl shadow-sm">
                                <?php if ($countFoto > 1): ?>
                                    <span class="absolute -top-1 -right-1 px-1.5 py-0.5 rounded-full text-[8px] font-bold bg-blue-600 text-white shadow-sm border border-white">
                                        +<?= $countFoto - 1 ?>
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <i data-lucide="monitor" class="w-7 h-7 text-blue-500"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="font-bold text-slate-800 text-sm leading-tight truncate"><?= e($b['nama_barang']) ?></h3>
                            <p class="text-xs text-slate-500 truncate mt-1 tracking-wide"><?= e($b['kode_barang']) ?> &bull; <?= e($b['merk_model'] ?: '-') ?></p>
                            
                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                <?php 
                                    $kondisiColor = 'bg-slate-100 text-slate-600';
                                    if ($b['kondisi'] === 'Baik') $kondisiColor = 'bg-emerald-50 text-emerald-700 border-emerald-100/50';
                                    elseif ($b['kondisi'] === 'Rusak Ringan') $kondisiColor = 'bg-amber-50 text-amber-700 border-amber-100/50';
                                    elseif ($b['kondisi'] === 'Rusak Berat') $kondisiColor = 'bg-rose-50 text-rose-700 border-rose-100/50';
                                ?>
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold <?= $kondisiColor ?>">
                                    <?= e($b['kondisi']) ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[10px] font-bold">
                                    <?= (int)$b['jumlah'] ?> <?= e($b['satuan'] ?? 'Unit') ?>
                                </span>
                                <?php if (!empty($b['nama_ruangan'])): ?>
                                    <span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 border border-purple-100 text-[10px] font-bold truncate max-w-[140px]">
                                        📍 <?= e($b['nama_ruangan']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        
                            <div class="flex items-center gap-2 mt-3 pt-2 border-t border-slate-100">
                                <a href="<?= url('mobile-sarpras/detail/' . $b['id']) ?>" class="px-2 py-1 rounded-lg border text-[10px] font-bold bg-blue-50 text-blue-700 border-blue-200 flex items-center gap-1 active:bg-blue-100">
                                    <i data-lucide="eye" class="w-3 h-3"></i> Detail
                                </a>
                                <a href="<?= url('mobile-sarpras/inventaris/edit/' . $b['id']) ?>" class="px-2 py-1 rounded-lg border text-[10px] font-bold bg-slate-50 text-slate-700 border-slate-200 flex items-center gap-1 active:bg-slate-100">
                                    <i data-lucide="edit" class="w-3 h-3"></i> Edit
                                </a>
                                <form action="<?= url('kelola-sarpras/barang/delete/' . $b['id']) ?>" method="POST" class="inline ml-auto" onsubmit="return confirm('Yakin ingin menghapus barang ini?');">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="return_to" value="<?= url('mobile-sarpras/inventaris') ?>">
                                    <button type="submit" class="px-2 py-1 rounded-lg border text-[10px] font-bold bg-rose-50 text-rose-600 border-rose-100 flex items-center gap-1 active:bg-rose-100">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Floating Action Button (FAB) -->
<div class="fixed bottom-28 right-5 z-50">
    <?php if (!empty($selectedRuangan)): ?>
        <a href="<?= url('mobile-sarpras/ruangan/detail/' . $selectedRuangan['id'] . '?action=input-manual') ?>" class="w-14 h-14 bg-emerald-600 text-white rounded-full shadow-emerald-500/50 shadow-xl flex items-center justify-center hover:bg-emerald-700 active:scale-95 transition-all border-4 border-slate-50" title="Tambah Inventaris di <?= e($selectedRuangan['nama_ruangan']) ?>">
            <i data-lucide="plus" class="w-7 h-7"></i>
        </a>
    <?php else: ?>
        <a href="<?= url('mobile-sarpras/inventaris/tambah') ?>" class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-blue-500/50 shadow-xl flex items-center justify-center hover:bg-blue-700 active:scale-95 transition-all border-4 border-slate-50" title="Tambah Inventaris">
            <i data-lucide="plus" class="w-7 h-7"></i>
        </a>
    <?php endif; ?>
</div>

<!-- Modal Pilih Ruangan untuk Tambah Inventaris Per Ruangan -->
<div id="modalPilihRuanganInventaris" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModalPilihRuanganInventaris()"></div>
    <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl relative z-10 max-h-[82vh] flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Tambah Inventaris Per Ruangan</h3>
                <p class="text-[11px] text-slate-500">Pilih ruangan/kelas untuk input manual barang baru</p>
            </div>
            <button type="button" onclick="closeModalPilihRuanganInventaris()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">&times;</button>
        </div>
        <div class="p-3 border-b border-slate-100 bg-slate-50">
            <input type="text" id="searchPilihRuanganInv" placeholder="Cari nama kelas / ruangan..." class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs outline-none focus:border-emerald-500">
        </div>
        <div class="p-3 overflow-y-auto space-y-2 flex-1">
            <?php if (!empty($ruanganList)): ?>
                <?php foreach ($ruanganList as $r): ?>
                    <div class="pilih-ruangan-inv-item flex items-center justify-between p-3 rounded-2xl border border-slate-100 hover:border-emerald-300 bg-white transition-all"
                         data-search="<?= strtolower(e(($r['nama_ruangan'] ?? '') . ' ' . ($r['nama_bangunan'] ?? '') . ' ' . ($r['unit'] ?? ''))) ?>">
                        <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id']) ?>" class="min-w-0 flex-1 pr-2">
                            <div class="font-bold text-slate-800 text-xs truncate"><?= e($r['nama_ruangan']) ?></div>
                            <div class="text-[10px] text-slate-400 truncate"><?= e($r['nama_bangunan'] ?? $r['lokasi_gedung'] ?? '-') ?> &bull; <?= (int)($r['total_barang'] ?? 0) ?> Aset</div>
                        </a>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id']) ?>" class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-[10px] font-bold">Lihat</a>
                            <a href="<?= url('mobile-sarpras/ruangan/detail/' . $r['id'] . '?action=input-manual') ?>" class="px-2.5 py-1.5 rounded-xl bg-emerald-600 text-white text-[10px] font-bold">+ Input Barang</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-center text-xs text-slate-400">Belum ada data ruangan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
function setKategori(id) {
    document.getElementById('kategoriInput').value = id;
    document.getElementById('filterForm').submit();
}
function openModalPilihRuanganInventaris() {
    document.getElementById('modalPilihRuanganInventaris')?.classList.remove('hidden');
}
function closeModalPilihRuanganInventaris() {
    document.getElementById('modalPilihRuanganInventaris')?.classList.add('hidden');
}
document.getElementById('searchPilihRuanganInv')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.pilih-ruangan-inv-item').forEach(el => {
        const match = !q || (el.getAttribute('data-search') || '').includes(q);
        el.style.display = match ? 'flex' : 'none';
    });
});
</script>
