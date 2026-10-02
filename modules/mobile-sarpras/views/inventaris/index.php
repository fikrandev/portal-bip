<div class="px-4 pt-4 pb-24 h-full overflow-y-auto">
    <!-- Header & Search -->
    <div class="mb-4 sticky top-0 bg-slate-50 pt-2 pb-3 z-10 space-y-3">
        <!-- Search Bar -->
        <form id="filterForm" action="<?= url('mobile-sarpras/inventaris') ?>" method="GET" class="flex gap-2">
            <input type="hidden" name="kategori_id" id="kategoriInput" value="<?= e($filters['kategori_id']) ?>">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </div>
                <input type="text" name="search" value="<?= e($filters['search']) ?>" class="w-full pl-10 pr-3 py-3 bg-white border border-slate-200 rounded-2xl text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 shadow-sm transition-all" placeholder="Cari aset...">
            </div>
        </form>

        <!-- Category Pills (Horizontal Scroll) -->
        <div class="overflow-x-auto hide-scrollbar pb-1 -mx-4 px-4 flex gap-2 snap-x">
            <button type="button" onclick="setKategori('')" class="snap-start shrink-0 px-4 py-1.5 rounded-full text-xs font-bold transition-all border <?= empty($filters['kategori_id']) ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/30' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' ?>">
                Semua
            </button>
            <?php foreach ($kategoriList as $kat): ?>
                <button type="button" onclick="setKategori('<?= $kat['id'] ?>')" class="snap-start shrink-0 px-4 py-1.5 rounded-full text-xs font-bold transition-all border <?= $filters['kategori_id'] == $kat['id'] ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-500/30' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' ?>">
                    <?= e($kat['nama_kategori']) ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <p class="text-xs text-slate-500 font-medium ml-1 pt-1">Menampilkan <?= count($barang) ?> data aset</p>
    </div>

    <!-- Data List -->
    <div class="space-y-3">
        <?php if (empty($barang)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-sm mt-10">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="package-open" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Data Tidak Ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau filter kategori.</p>
            </div>
        <?php else: ?>
            <?php foreach ($barang as $b): ?>
                <div class="block bg-white p-4 rounded-3xl shadow-sm border border-slate-100 active:scale-[0.98] transition-transform">
                    <div class="flex gap-4">
                        <div class="w-16 h-16 bg-blue-50/50 rounded-2xl flex items-center justify-center flex-shrink-0 border border-blue-100/50 p-1">
                            <?php if (!empty($b['foto'])): ?>
                                <img src="<?= url('public/' . ltrim($b['foto'], '/')) ?>" alt="Foto" class="w-full h-full object-cover rounded-xl shadow-sm">
                            <?php else: ?>
                                <i data-lucide="monitor" class="w-7 h-7 text-blue-500"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0 flex flex-col justify-center">
                            <h3 class="font-bold text-slate-800 text-sm leading-tight truncate"><?= e($b['nama_barang']) ?></h3>
                            <p class="text-xs text-slate-500 truncate mt-1 tracking-wide"><?= e($b['kode_barang']) ?> &bull; <?= e($b['merk_model'] ?: '-') ?></p>
                            
                            <div class="flex items-center gap-2 mt-2">
                                <?php 
                                    $kondisiColor = 'bg-slate-100 text-slate-600';
                                    if ($b['kondisi'] === 'Baik') $kondisiColor = 'bg-emerald-50 text-emerald-700 border-emerald-100/50';
                                    elseif ($b['kondisi'] === 'Rusak Ringan') $kondisiColor = 'bg-amber-50 text-amber-700 border-amber-100/50';
                                    elseif ($b['kondisi'] === 'Rusak Berat') $kondisiColor = 'bg-rose-50 text-rose-700 border-rose-100/50';
                                ?>
                                <span class="px-2 py-0.5 rounded-lg border text-[10px] font-bold <?= $kondisiColor ?>">
                                    <?= e($b['kondisi']) ?>
                                </span>
                            </div>
                        
                            <div class="flex items-center gap-2 mt-3 pt-2 border-t border-slate-100">
                                <a href="<?= url('mobile-sarpras/detail/' . $b['id']) ?>" class="px-2 py-1 rounded-lg border text-[10px] font-bold bg-blue-50 text-blue-700 border-blue-200 flex items-center gap-1 active:bg-blue-100">
                                    <i data-lucide="eye" class="w-3 h-3"></i> Detail
                                </a>
                                <a href="<?= url('mobile-sarpras/inventaris/edit/' . $b['id']) ?>" class="px-2 py-1 rounded-lg border text-[10px] font-bold bg-slate-50 text-slate-700 border-slate-200 flex items-center gap-1 active:bg-slate-100">
                                    <i data-lucide="edit" class="w-3 h-3"></i> Edit
                                </a>
                                <form action="<?= url('kelola-sarpras/barang/delete/' . $b['id']) ?>" method="POST" class="inline ml-auto" onsubmit="return confirm('Yakin ingin menghapus barang ini?');">
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

<!-- Floating Action Button (FAB) for adding new item -->
<div class="fixed bottom-28 right-5 z-50">
    <a href="<?= url('mobile-sarpras/inventaris/tambah') ?>" class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-blue-500/50 shadow-xl flex items-center justify-center hover:bg-blue-700 active:scale-95 transition-all border-4 border-slate-50">
        <i data-lucide="plus" class="w-7 h-7"></i>
    </a>
</div>

<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
</style>

<script>
function setKategori(id) {
    document.getElementById('kategoriInput').value = id;
    document.getElementById('filterForm').submit();
}
</script>


