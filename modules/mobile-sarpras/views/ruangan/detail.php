<?php
$totalUnitRuangan = 0;
foreach ($distribusi as $itemD) {
    $totalUnitRuangan += (int)($itemD['jumlah'] ?? 0);
}
?>
<div class="px-4 pt-4 pb-28 space-y-4">
    <!-- Header Info Ruangan -->
    <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-sm space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="<?= url('mobile-sarpras/ruangan') ?>" class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 active:scale-95">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-100"><?= e($ruangan['jenis_ruangan'] ?? 'Ruang Kelas') ?></span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-100">Unit: <?= e($ruangan['unit'] ?? 'SD') ?></span>
                    </div>
                    <h2 class="text-base font-bold text-slate-800 truncate mt-1"><?= e($ruangan['nama_ruangan']) ?></h2>
                    <p class="text-[11px] text-slate-500 truncate">
                        <?= e($ruangan['nama_bangunan'] ?? $ruangan['lokasi_gedung'] ?? '-') ?> &bull; Lt. <?= (int)($ruangan['lantai'] ?? 1) ?>
                    </p>
                </div>
            </div>
            <a href="<?= url('mobile-sarpras/ruangan/edit/' . $ruangan['id']) ?>" class="p-2 rounded-xl bg-slate-50 text-slate-500 border border-slate-200 shrink-0" title="Edit Ruangan">
                <i data-lucide="settings-2" class="w-4 h-4"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs">
            <div class="bg-slate-50 rounded-2xl p-2.5 border border-slate-100">
                <span class="text-[10px] text-slate-400 font-semibold block">Total Jenis Barang</span>
                <span class="text-sm font-black text-slate-800"><?= count($distribusi) ?> Aset</span>
            </div>
            <div class="bg-slate-50 rounded-2xl p-2.5 border border-slate-100">
                <span class="text-[10px] text-slate-400 font-semibold block">Total Kuantitas</span>
                <span class="text-sm font-black text-blue-600"><?= $totalUnitRuangan ?> Unit</span>
            </div>
        </div>

        <!-- Tombol Aksi Utama: Input Manual vs Ambil dari Inventaris -->
        <div class="grid grid-cols-1 gap-2 pt-1">
            <a href="<?= url('mobile-sarpras/inventaris/tambah?ruangan_id=' . $ruangan['id']) ?>" class="w-full py-3 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-98 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-md shadow-emerald-500/20 transition-all">
                <i data-lucide="package-plus" class="w-4 h-4"></i>
                <span>+ Input Manual Barang Baru di Ruangan Ini</span>
            </a>
            <button type="button" onclick="openModalDistribusiMobile()" class="w-full py-2.5 px-4 rounded-2xl bg-blue-50 hover:bg-blue-100 active:scale-98 text-blue-700 border border-blue-200 text-xs font-bold flex items-center justify-center gap-2 transition-all">
                <i data-lucide="arrow-Left-right" class="w-4 h-4"></i>
                <span>Ambil / Distribusi dari Stok Inventaris</span>
            </button>
        </div>
    </div>

    <!-- Daftar Barang di Ruangan Ini -->
    <div class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Daftar Aset di Ruangan Ini</h3>
            <span class="text-[11px] text-slate-400 font-medium"><?= count($distribusi) ?> item</span>
        </div>

        <?php if (empty($distribusi)): ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-sm">
                <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <i data-lucide="package-open" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700">Belum Ada Barang di Ruangan Ini</h4>
                <p class="text-xs text-slate-400 mt-1 mb-4">Input manual barang baru di kelas/ruangan ini agar otomatis masuk ke Inventaris dan ditempatkan di sini.</p>
                <a href="<?= url('mobile-sarpras/inventaris/tambah?ruangan_id=' . $ruangan['id']) ?>" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Input Manual Barang Baru</span>
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($distribusi as $d): 
                $fotos = [];
                if (!empty($d['foto'])) {
                    $decoded = json_decode($d['foto'], true);
                    $fotos = is_array($decoded) ? $decoded : [$d['foto']];
                }
                $thumbUrl = !empty($fotos[0]) ? url($fotos[0]) : null;
                $kondisi = $d['kondisi'] ?? 'Baik';
                $kondisiBadge = $kondisi === 'Baik'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    : ($kondisi === 'Rusak Ringan' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200');
            ?>
                <div class="bg-white p-3.5 rounded-3xl border border-slate-100 shadow-sm space-y-2.5">
                    <div class="flex gap-3">
                        <a href="<?= url('mobile-sarpras/detail/' . $d['barang_id']) ?>" class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200/80 overflow-hidden flex items-center justify-center shrink-0">
                            <?php if ($thumbUrl): ?>
                                <img src="<?= $thumbUrl ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <i data-lucide="package" class="w-6 h-6 text-slate-400"></i>
                            <?php endif; ?>
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <a href="<?= url('mobile-sarpras/detail/' . $d['barang_id']) ?>" class="font-bold text-slate-800 text-sm leading-snug truncate">
                                    <?= e($d['nama_barang']) ?>
                                </a>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border shrink-0 <?= $kondisiBadge ?>">
                                    <?= e($kondisi) ?>
                                </span>
                            </div>
                            <div class="text-[11px] font-mono font-bold text-blue-600 mt-0.5"><?= e($d['kode_barang']) ?></div>
                            <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold">
                                    <?= (int)$d['jumlah'] ?> <?= e($d['satuan']) ?>
                                </span>
                                <?php if (!empty($d['nama_kategori'])): ?>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-50 text-slate-500 border border-slate-200 text-[10px] font-medium">
                                        <?= e($d['nama_kategori']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($d['merk_model'])): ?>
                                    <span class="text-[10px] text-slate-400 truncate"><?= e($d['merk_model']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($d['keterangan'])): ?>
                                <p class="text-[11px] text-slate-500 mt-1 italic">📍 <?= e($d['keterangan']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-1.5">
                            <a href="<?= url('mobile-sarpras/detail/' . $d['barang_id']) ?>" class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200 text-[10px] font-bold flex items-center gap-1">
                                <i data-lucide="eye" class="w-3 h-3"></i> Detail
                            </a>
                            <a href="<?= url('mobile-sarpras/inventaris/edit/' . $d['barang_id']) ?>" class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-bold flex items-center gap-1">
                                <i data-lucide="edit" class="w-3 h-3"></i> Edit Master
                            </a>
                        </div>
                        <form action="<?= url('mobile-sarpras/distribusi/delete/' . $d['id']) ?>" method="POST" class="inline" onsubmit="return confirm('Tarik/hapus barang ini dari ruangan <?= htmlspecialchars($ruangan['nama_ruangan'], ENT_QUOTES) ?>?');">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras/ruangan/detail/' . $ruangan['id']) ?>">
                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-600 border border-rose-100 text-[10px] font-bold flex items-center gap-1">
                                <i data-lucide="undo-2" class="w-3 h-3"></i> Tarik
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Distribusi dari Inventaris (Mobile) -->
<div id="modalDistribusiMobile" class="fixed inset-0 z-[100] hidden flex items-end sm:items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xs" onclick="closeModalDistribusiMobile()"></div>
    <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl relative z-10 max-h-[85vh] flex flex-col overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Ambil dari Stok Inventaris</h3>
                <p class="text-[11px] text-slate-500">Tempatkan ke <?= e($ruangan['nama_ruangan']) ?></p>
            </div>
            <button type="button" onclick="closeModalDistribusiMobile()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">&times;</button>
        </div>
        <form action="<?= url('mobile-sarpras/distribusi/store') ?>" method="POST" class="p-5 overflow-y-auto space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="ruangan_id" value="<?= (int)$ruangan['id'] ?>">
            <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras/ruangan/detail/' . $ruangan['id']) ?>">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Barang Tersedia <span class="text-rose-500">*</span></label>
                <select name="barang_id[]" id="selBarangDistMobile" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500 font-medium">
                    <option value="">-- Pilih Barang dari Inventaris --</option>
                    <?php foreach ($barangTersedia as $b): $sisa = $b['jumlah'] - ($b['dipakai'] ?? 0); ?>
                        <option value="<?= $b['id'] ?>" data-sisa="<?= $sisa ?>" data-satuan="<?= e($b['satuan']) ?>">
                            <?= e($b['nama_barang']) ?> (<?= e($b['kode_barang']) ?>) - Sisa: <?= $sisa ?> <?= e($b['satuan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah <span class="text-rose-500">*</span></label>
                    <input type="number" name="jumlah[]" id="jumlahDistMobile" value="1" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-blue-500">
                    <p id="infoSisaDistMobile" class="text-[10px] text-slate-400 mt-1"></p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi <span class="text-rose-500">*</span></label>
                    <select name="kondisi[]" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Posisi di Ruangan</label>
                <input type="text" name="keterangan[]" placeholder="Cth: Meja Guru, Pojok Kelas..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
            </div>

            <div class="pt-2 flex gap-2">
                <button type="button" onclick="closeModalDistribusiMobile()" class="flex-1 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-bold">Batal</button>
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-md">Simpan Distribusi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModalDistribusiMobile() {
    document.getElementById('modalDistribusiMobile')?.classList.remove('hidden');
}
function closeModalDistribusiMobile() {
    document.getElementById('modalDistribusiMobile')?.classList.add('hidden');
}
document.getElementById('selBarangDistMobile')?.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const sisa = opt?.getAttribute('data-sisa');
    const satuan = opt?.getAttribute('data-satuan') || 'Unit';
    const inputJml = document.getElementById('jumlahDistMobile');
    const info = document.getElementById('infoSisaDistMobile');
    if (sisa && inputJml) {
        inputJml.max = sisa;
        inputJml.value = 1;
        if (info) info.textContent = `Maksimal: ${sisa} ${satuan}`;
    } else if (info) {
        info.textContent = '';
    }
});
</script>

