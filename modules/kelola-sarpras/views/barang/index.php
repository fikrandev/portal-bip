<?php
/**
 * Daftar Inventaris Aset & Barang Sarpras
 * Portal BIP
 */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-sarpras') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
                    &larr; Dashboard Sarpras
                </a>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1 flex items-center gap-2.5">
                <span>Inventaris Sarana &amp; Prasarana</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-100 text-primary-800">
                    <?= number_format($total) ?> Aset
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pencatatan aset fisik, nomor inventaris, penempatan ruangan, dan kondisi operasional
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= url('kelola-sarpras/export') ?>" class="px-3.5 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                <span>Export Excel/CSV</span>
            </a>
            <button onclick="openModalTambah()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Aset Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="<?= url('kelola-sarpras/barang') ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pencarian</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Nama, kode, atau merk barang..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Filter Unit -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Unit / Jenjang</label>
                <select name="unit" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    <option value="">Semua Unit</option>
                    <?php foreach (['PAUD', 'SD', 'SMP', 'SMA', 'Yayasan', 'Semua'] as $u): ?>
                        <option value="<?= $u ?>" <?= $filters['unit'] === $u ? 'selected' : '' ?>><?= $u ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Kategori -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori</label>
                <select name="kategori_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filters['kategori_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Kondisi -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kondisi Fisik</label>
                <select name="kondisi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    <option value="">Semua Kondisi</option>
                    <option value="Baik" <?= $filters['kondisi'] === 'Baik' ? 'selected' : '' ?>>Baik</option>
                    <option value="Rusak Ringan" <?= $filters['kondisi'] === 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                    <option value="Rusak Berat" <?= $filters['kondisi'] === 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat</option>
                </select>
            </div>

            <!-- Submit Filter -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all text-center">
                    Filter
                </button>
                <a href="<?= url('kelola-sarpras/barang') ?>" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold" title="Reset Filter">
                    ↺
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Aset &amp; Kode Barang</th>
                        <th class="py-3.5 px-4">Kategori &amp; Unit</th>
                        <th class="py-3.5 px-4 text-center">Jumlah</th>
                        <th class="py-3.5 px-4 text-center">Kondisi</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Nilai Aset</th>
                        <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="text-4xl mb-2">🔍</div>
                                <p class="font-semibold">Tidak ada inventaris barang ditemukan.</p>
                                <p class="text-[11px] text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1 + (($page - 1) * 20); foreach ($items as $b): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <?php 
                                        $fotoList = SarprasModel::getFotoList($b['foto'] ?? '');
                                        $firstFoto = !empty($fotoList) ? $fotoList[0] : null;
                                        $countFoto = count($fotoList);
                                        ?>
                                        <?php if ($firstFoto): ?>
                                            <div class="relative cursor-pointer group shrink-0" onclick="openFotoGallery(<?= htmlspecialchars(json_encode($fotoList)) ?>, '<?= e(addslashes($b['nama_barang'])) ?>')" title="Klik untuk lihat <?= $countFoto ?> foto">
                                                <img src="<?= url('public/' . ltrim($firstFoto, '/')) ?>" alt="Foto" class="w-10 h-10 rounded-xl object-cover border border-slate-200 group-hover:scale-105 transition-transform shadow-sm">
                                                <?php if ($countFoto > 1): ?>
                                                    <span class="absolute -top-1.5 -right-1.5 px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-blue-600 text-white shadow-sm border border-white">
                                                        +<?= $countFoto - 1 ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-xl bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 text-lg shrink-0">
                                                📦
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm leading-tight hover:text-primary-600 cursor-pointer" onclick="showDetail(<?= htmlspecialchars(json_encode($b)) ?>)">
                                                <?= e($b['nama_barang']) ?>
                                            </div>
                                            <div class="text-[11px] font-mono text-primary-700 font-semibold mt-0.5">
                                                <?= e($b['kode_barang']) ?>
                                                <?php if (!empty($b['merk_model'])): ?>
                                                    <span class="text-slate-400 font-sans">&bull; <?= e($b['merk_model']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-block px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700 mb-1">
                                        <?= e($b['nama_kategori'] ?? 'Umum') ?>
                                    </span>
                                    <div class="text-[10px] text-primary-800 font-bold">Unit: <?= e($b['unit']) ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php 
                                    $dipakai = $b['dipakai'] ?? 0;
                                    $sisa = $b['jumlah'] - $dipakai;
                                    ?>
                                    <div class="text-[10px] min-w-[90px]">
                                        <div class="flex justify-between mb-0.5"><span class="text-slate-500">Total:</span> <span class="font-bold text-slate-800"><?= $b['jumlah'] ?></span></div>
                                        <div class="flex justify-between mb-0.5"><span class="text-slate-500">Dipakai:</span> <span class="font-bold text-amber-600"><?= $dipakai ?></span></div>
                                        <div class="flex justify-between pt-0.5 border-t border-slate-200 mt-0.5"><span class="text-slate-500">Sisa:</span> <span class="font-bold text-emerald-600"><?= $sisa ?></span></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($b['kondisi'] === 'Baik'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                            Baik
                                        </span>
                                    <?php elseif ($b['kondisi'] === 'Rusak Ringan'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Rusak Ringan
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Rusak Berat
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php 
                                        $sisaStatusText = ($sisa > 0) ? "Tersedia" : "Tidak Tersedia";
                                        $sisaStatusColor = ($sisa > 0) ? "text-primary-700 hover:text-primary-800 hover:underline" : "text-rose-700 hover:text-rose-800 hover:underline";
                                        $sisaDotColor = ($sisa > 0) ? "text-primary-700" : "text-rose-700";
                                    ?>
                                    <button type="button" onclick="openModalDetailDistribusi(<?= $b['id'] ?>)" class="<?= $sisaStatusColor ?> font-bold text-[11px] cursor-pointer">
                                        <span class="<?= $sisaDotColor ?>">&#x2022;</span> <?= $sisaStatusText ?>
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-bold text-slate-800">Rp <?= number_format($b['harga_perolehan'], 0, ',', '.') ?></div>
                                    <div class="text-[10px] text-slate-400">@ Rp <?= number_format($b['harga_perolehan'] / max(1, $b['jumlah']), 0, ',', '.') ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Cetak Label Barcode -->
                                        <a href="<?= url('kelola-sarpras/cetak-label/print?barang_id=' . $b['id'] . '&format=barcode') ?>" target="_blank" class="p-1.5 rounded-lg text-slate-500 hover:text-primary-600 hover:bg-primary-50 transition-colors" title="Cetak Label Barcode">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v2.796c0 1.18.91 2.164 2.09 2.201a51.964 51.964 0 0 0 6.32 0c1.18-.037 2.09-1.022 2.09-2.201V9.456Z" />
                                            </svg>
                                        </a>
                                        <!-- Edit -->
                                        <button type="button" onclick="openModalEdit(<?= htmlspecialchars(json_encode($b)) ?>)" class="p-1.5 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Edit Aset">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                        </button>
                                        <!-- Hapus -->
                                        <form action="<?= url('kelola-sarpras/barang/delete/' . $b['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aset <?= e($b['nama_barang']) ?>?')">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Aset">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">
                    Menampilkan halaman <strong><?= $page ?></strong> dari <strong><?= $totalPages ?></strong>
                </span>
                <div class="flex items-center gap-1.5">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('kelola-sarpras/barang?' . http_build_query(array_merge($filters, ['page' => $page - 1]))) ?>" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100">Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('kelola-sarpras/barang?' . http_build_query(array_merge($filters, ['page' => $page + 1]))) ?>" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100">Selanjutnya</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Quick Detail -->
<div id="modal-detail" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden transform scale-95 transition-all duration-200 p-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 class="text-base font-bold text-slate-800" id="detail-title">Detail Aset Sarpras</h3>
            <button onclick="closeDetail()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <div class="space-y-3 text-xs" id="detail-body">
            <!-- Dynamic Injection -->
        </div>
        <div class="mt-6 pt-3 border-t border-slate-100 flex justify-end">
            <button onclick="closeDetail()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang -->
<div id="modalTambah" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModalTambah()"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl sm:rounded-3xl flex flex-col max-h-[90vh] scale-95 opacity-0" id="modalTambahContent">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center">📦</span>
                    Tambah Inventaris Baru
                </h2>
                <button onclick="closeModalTambah()" type="button" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 overflow-y-auto flex-1">
                <form action="<?= url('kelola-sarpras/barang/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-6" id="formTambahBarang">
                    <?= CSRF::field() ?>

                    <div class="bg-primary-50 rounded-xl p-4 border border-primary-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-primary-600 uppercase tracking-wide">Preview Kode Barang</p>
                            <p class="text-lg font-mono font-bold text-primary-900" id="previewKode">... . ... . ... . 001</p>
                        </div>
                    </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Golongan <span class="text-rose-500">*</span></label>
                        <select name="golongan_id" id="selGolongan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                            <option value="" data-kode="00">-- Pilih --</option>
                            <?php foreach ($golonganList as $g): ?>
                                <option value="<?= $g['id'] ?>" data-kode="<?= e($g['kode']) ?>"><?= e($g['kode']) ?> - <?= e($g['nama_golongan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok <span class="text-rose-500">*</span></label>
                        <select name="kelompok_id" id="selKelompok" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                            <option value="" data-kode="00">-- Pilih --</option>
                            <?php foreach ($kelompokList as $k): ?>
                                <option value="<?= $k['id'] ?>" data-kode="<?= e($k['kode']) ?>"><?= e($k['kode']) ?> - <?= e($k['nama_kelompok']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                        <select name="asal_anggaran_id" id="selAnggaran" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                            <option value="" data-kode="00">-- Pilih --</option>
                            <?php foreach ($asalAnggaranList as $a): ?>
                                <option value="<?= $a['id'] ?>" data-kode="<?= e($a['kode']) ?>"><?= e($a['kode']) ?> - <?= e($a['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_barang" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500" placeholder="Contoh: Meja Siswa">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Merk / Model</label>
                        <input type="text" name="merk_model" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Seri</label>
                        <input type="text" name="nomor_seri" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah (Qty) <span class="text-rose-500">*</span></label>
                        <input type="number" name="jumlah" value="1" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                        <select name="satuan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                            <?php foreach ($satuanList as $s): ?>
                                <option value="<?= e($s['nama_satuan']) ?>"><?= e($s['nama_satuan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Perolehan (Rp)</label>
                        <input type="text" id="inputHarga" name="harga_perolehan_formatted" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-bold text-right">
                        <input type="hidden" name="harga_perolehan" id="hiddenHarga" value="0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_perolehan" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Masa Manfaat (Tahun)</label>
                        <input type="number" name="masa_manfaat" value="5" min="0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi</label>
                        <select name="kondisi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>
                    
                    <div class="sm:col-span-2 pt-2">
                        <?php 
                        $uploaderId = 'foto_modal_tambah';
                        $existingFotos = [];
                        $isMobile = false;
                        include BASE_PATH . '/modules/kelola-sarpras/views/barang/_foto_uploader.php';
                        ?>
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Sticky) -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 shrink-0 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModalTambah()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" form="formTambahBarang" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span>Simpan Data</span>
                </button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModalTambah() {
        const uploaderTambah = document.getElementById('foto_modal_tambah');
        if (uploaderTambah && uploaderTambah.clearPhotos) {
            uploaderTambah.clearPhotos();
        }

        const modal = document.getElementById('modalTambah');
        const modalContent = document.getElementById('modalTambahContent');
        modal.classList.remove('hidden');
        
        // Trigger reflow
        void modal.offsetWidth;
        
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }

    function closeModalTambah() {
        const modal = document.getElementById('modalTambah');
        const modalContent = document.getElementById('modalTambahContent');
        
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200); // match transition duration
    }

    // Format Rupiah
    const inputHarga = document.getElementById('inputHarga');
    const hiddenHarga = document.getElementById('hiddenHarga');
    
    inputHarga.addEventListener('input', function(e) {
        let value = this.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        this.value = rupiah;
        
        // Update hidden input with raw number
        hiddenHarga.value = value.replace(/\./g, '').replace(',', '.');
    });

    // Preview Kode Barang
    const selGol = document.getElementById('selGolongan');
    const selKel = document.getElementById('selKelompok');
    const selAng = document.getElementById('selAnggaran');
    const previewKode = document.getElementById('previewKode');

    function updatePreviewKode() {
        const kGol = selGol.options[selGol.selectedIndex]?.dataset?.kode || '...';
        const kKel = selKel.options[selKel.selectedIndex]?.dataset?.kode || '...';
        const kAng = selAng.options[selAng.selectedIndex]?.dataset?.kode || '...';
        previewKode.textContent = `${kGol}.${kKel}.${kAng}.001`;
    }

    selGol.addEventListener('change', updatePreviewKode);
    selKel.addEventListener('change', updatePreviewKode);
    selAng.addEventListener('change', updatePreviewKode);
</script>

<script>
function showDetail(item) {
    document.getElementById('detail-title').innerText = item.nama_barang;
    let html = `
        <div class="grid grid-cols-2 gap-2 p-3 bg-slate-50 rounded-2xl">
            <div><span class="text-slate-400">Kode Barang:</span><div class="font-mono font-bold text-primary-700">${item.kode_barang}</div></div>
            <div><span class="text-slate-400">Nomor Seri:</span><div class="font-mono">${item.nomor_seri || '-'}</div></div>
            <div><span class="text-slate-400">Kategori:</span><div class="font-semibold">${item.nama_kategori || '-'}</div></div>
            <div><span class="text-slate-400">Unit / Jenjang:</span><div class="font-semibold">${item.unit}</div></div>
            <div><span class="text-slate-400">Ruangan:</span><div class="font-semibold">${item.nama_ruangan || '-'}</div></div>
            <div><span class="text-slate-400">Total Stok:</span><div class="font-semibold">${item.jumlah} ${item.satuan}</div></div>
            <div><span class="text-slate-400">Terpakai:</span><div class="font-semibold text-amber-600">${item.dipakai || 0} ${item.satuan}</div></div>
            <div><span class="text-slate-400">Sisa / Tersedia:</span><div class="font-semibold text-emerald-600">${item.jumlah - (item.dipakai || 0)} ${item.satuan}</div></div>
            <div><span class="text-slate-400">Kondisi:</span><div class="font-semibold">${item.kondisi}</div></div>
            <div><span class="text-slate-400">Status:</span><div class="font-semibold">${item.status}</div></div>
            <div><span class="text-slate-400">Sumber Dana:</span><div class="font-semibold">${item.sumber_dana || '-'}</div></div>
            <div><span class="text-slate-400">Tahun Perolehan:</span><div class="font-semibold">${item.tahun_pengadaan || '-'}</div></div>
        </div>
        <div class="p-3 bg-primary-50/50 rounded-2xl border border-primary-100">
            <span class="text-slate-500 font-medium">Harga Perolehan (Total):</span>
            <div class="text-base font-bold text-primary-800">Rp ${Number(item.harga_perolehan).toLocaleString('id-ID')}</div>
            <div class="text-[10px] text-slate-500">@ Rp ${Number(item.harga_perolehan / Math.max(1, item.jumlah)).toLocaleString('id-ID')} / ${item.satuan}</div>
        </div>
        ${item.keterangan ? `<div class="p-3 bg-slate-50 rounded-2xl"><span class="text-slate-400 block mb-1">Catatan Tambahan:</span><p class="text-slate-700">${item.keterangan}</p></div>` : ''}
    `;
    document.getElementById('detail-body').innerHTML = html;
    const m = document.getElementById('modal-detail');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}

function closeDetail() {
    const m = document.getElementById('modal-detail');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}
</script>

<!-- Modal Edit Barang -->
<div id="modalEdit" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeModalEdit()"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-3xl sm:rounded-3xl flex flex-col max-h-[90vh] scale-95 opacity-0" id="modalEditContent">
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">✏️</span>
                    Edit Inventaris
                </h2>
                <button onclick="closeModalEdit()" type="button" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <form id="formEditBarang" action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?= CSRF::field() ?>

                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-amber-600 uppercase tracking-wide">Preview Kode Barang</p>
                            <p class="text-lg font-mono font-bold text-amber-900" id="previewKodeEdit">... . ... . ... . 001</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Golongan <span class="text-rose-500">*</span></label>
                            <select name="golongan_id" id="selGolonganEdit" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                <option value="" data-kode="00">-- Pilih --</option>
                                <?php foreach ($golonganList as $g): ?>
                                    <option value="<?= $g['id'] ?>" data-kode="<?= e($g['kode']) ?>"><?= e($g['kode']) ?> - <?= e($g['nama_golongan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok <span class="text-rose-500">*</span></label>
                            <select name="kelompok_id" id="selKelompokEdit" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                <option value="" data-kode="00">-- Pilih --</option>
                                <?php foreach ($kelompokList as $k): ?>
                                    <option value="<?= $k['id'] ?>" data-kode="<?= e($k['kode']) ?>"><?= e($k['kode']) ?> - <?= e($k['nama_kelompok']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                            <select name="asal_anggaran_id" id="selAnggaranEdit" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                <option value="" data-kode="00">-- Pilih --</option>
                                <?php foreach ($asalAnggaranList as $a): ?>
                                    <option value="<?= $a['id'] ?>" data-kode="<?= e($a['kode']) ?>"><?= e($a['kode']) ?> - <?= e($a['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_barang" id="editNamaBarang" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Merk / Model</label>
                            <input type="text" name="merk_model" id="editMerkModel" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Seri</label>
                            <input type="text" name="nomor_seri" id="editNomorSeri" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah (Qty) <span class="text-rose-500">*</span></label>
                            <input type="number" name="jumlah" id="editJumlah" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                            <select name="satuan" id="editSatuan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                <?php foreach ($satuanList as $s): ?>
                                    <option value="<?= e($s['nama_satuan']) ?>"><?= e($s['nama_satuan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Harga Perolehan (Rp)</label>
                            <input type="text" id="inputHargaEdit" name="harga_perolehan_formatted" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500 font-bold text-right">
                            <input type="hidden" name="harga_perolehan" id="hiddenHargaEdit">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_perolehan" id="editTanggal" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Masa Manfaat (Tahun)</label>
                            <input type="number" name="masa_manfaat" id="editMasaManfaat" min="0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi</label>
                            <select name="kondisi" id="editKondisi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-primary-500">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                        
                        <div class="sm:col-span-2 pt-2">
                            <?php 
                            $uploaderId = 'foto_modal_edit';
                            $existingFotos = [];
                            $isMobile = false;
                            include BASE_PATH . '/modules/kelola-sarpras/views/barang/_foto_uploader.php';
                            ?>
                        </div>
                    </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 shrink-0 flex items-center justify-end gap-3">
                <button type="button" onclick="closeModalEdit()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">Batal</button>
                <button type="submit" form="formEditBarang" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span>Update Data</span>
                </button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    const urlBaseEdit = "<?= url('kelola-sarpras/barang/update/') ?>";

    function openModalEdit(item) {
        document.getElementById('formEditBarang').action = urlBaseEdit + item.id;
        
        document.getElementById('selGolonganEdit').value = item.golongan_id || '';
        document.getElementById('selKelompokEdit').value = item.kelompok_id || '';
        document.getElementById('selAnggaranEdit').value = item.asal_anggaran_id || '';
        document.getElementById('editNamaBarang').value = item.nama_barang || '';
        document.getElementById('editMerkModel').value = item.merk_model || '';
        document.getElementById('editNomorSeri').value = item.nomor_seri || '';
        document.getElementById('editJumlah').value = item.jumlah || '1';
        document.getElementById('editSatuan').value = item.satuan || '';
        document.getElementById('editTanggal').value = item.tanggal_perolehan || '';
        document.getElementById('editMasaManfaat').value = item.masa_manfaat || '0';
        document.getElementById('editKondisi').value = item.kondisi || 'Baik';
        
        const rawHarga = item.harga_perolehan || 0;
        document.getElementById('hiddenHargaEdit').value = rawHarga;
        document.getElementById('inputHargaEdit').value = Number(rawHarga).toLocaleString('id-ID');
        
        // Update Preview Kode
        updatePreviewKodeEdit(item.kode_barang);

        // Load Existing Photos into uploader
        let existingFotos = [];
        if (item.foto) {
            if (typeof item.foto === 'string' && item.foto.trim().startsWith('[')) {
                try {
                    existingFotos = JSON.parse(item.foto);
                } catch(e) {
                    existingFotos = [item.foto];
                }
            } else if (Array.isArray(item.foto)) {
                existingFotos = item.foto;
            } else if (typeof item.foto === 'string' && item.foto.trim() !== '') {
                existingFotos = [item.foto.trim()];
            }
        }
        const uploaderEdit = document.getElementById('foto_modal_edit');
        if (uploaderEdit && uploaderEdit.loadExistingPhotos) {
            uploaderEdit.loadExistingPhotos(existingFotos);
        }

        const modal = document.getElementById('modalEdit');
        const modalContent = document.getElementById('modalEditContent');
        modal.classList.remove('hidden');
        
        void modal.offsetWidth;
        
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }

    function closeModalEdit() {
        const modal = document.getElementById('modalEdit');
        const modalContent = document.getElementById('modalEditContent');
        
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // Format Rupiah Edit
    const inputHargaEdit = document.getElementById('inputHargaEdit');
    const hiddenHargaEdit = document.getElementById('hiddenHargaEdit');
    
    inputHargaEdit.addEventListener('input', function(e) {
        let value = this.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        this.value = rupiah;
        
        hiddenHargaEdit.value = value.replace(/\./g, '').replace(',', '.');
    });

    // Preview Kode Barang Edit
    const selGolEdit = document.getElementById('selGolonganEdit');
    const selKelEdit = document.getElementById('selKelompokEdit');
    const selAngEdit = document.getElementById('selAnggaranEdit');
    const previewKodeEdit = document.getElementById('previewKodeEdit');

    function updatePreviewKodeEdit(kodeBarangExisting = null) {
        const kGol = selGolEdit.options[selGolEdit.selectedIndex]?.dataset?.kode || '...';
        const kKel = selKelEdit.options[selKelEdit.selectedIndex]?.dataset?.kode || '...';
        const kAng = selAngEdit.options[selAngEdit.selectedIndex]?.dataset?.kode || '...';
        
        let urutan = '001';
        if (kodeBarangExisting && typeof kodeBarangExisting === 'string') {
            const parts = kodeBarangExisting.split('.');
            if (parts.length > 3) urutan = parts[parts.length - 1];
        }
        
        previewKodeEdit.textContent = `${kGol}.${kKel}.${kAng}.${urutan}`;
    }

    selGolEdit.addEventListener('change', updatePreviewKodeEdit);
    selKelEdit.addEventListener('change', updatePreviewKodeEdit);
    selAngEdit.addEventListener('change', updatePreviewKodeEdit);
</script>

<div id="modal-detail-distribusi" class="fixed inset-0 z-[100] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModalDetailDistribusi()"></div>
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative z-10 max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Detail Distribusi & Peminjaman</h3>
            <button onclick="closeModalDetailDistribusi()" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white transition-colors text-xl font-bold">&times;</button>
        </div>
        <div class="p-6 overflow-y-auto" id="distribusi-content-body">
            <div class="flex justify-center items-center py-10">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end">
            <button type="button" onclick="closeModalDetailDistribusi()" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-bold rounded-xl transition-colors">Tutup</button>
        </div>
    </div>
</div>

<script>
    function openModalDetailDistribusi(id) {
        const modal = document.getElementById('modal-detail-distribusi');
        const body = document.getElementById('distribusi-content-body');
        
        modal.classList.remove('hidden');
        body.innerHTML = '<div class="flex justify-center items-center py-10"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div></div>';
        
        fetch('<?= url('kelola-sarpras/barang/detail-distribusi') ?>?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    renderDistribusiData(data.data);
                } else {
                    body.innerHTML = '<div class="text-center text-rose-500 py-10 font-bold">' + data.message + '</div>';
                }
            })
            .catch(err => {
                body.innerHTML = '<div class="text-center text-rose-500 py-10 font-bold">Terjadi kesalahan jaringan</div>';
            });
    }

    function renderDistribusiData(data) {
        const body = document.getElementById('distribusi-content-body');
        
        let html = `
            <div class="mb-5 flex justify-between items-start">
                <div>
                    <h4 class="font-bold text-lg text-slate-800">${data.barang.nama_barang}</h4>
                    <p class="text-xs text-slate-500 mt-1">${data.barang.kode_barang}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Ketersediaan</p>
                    <div class="flex items-end gap-3 text-sm">
                        <div>Total: <span class="font-bold text-slate-800">${data.barang.jumlah}</span></div>
                        <div>Sisa: <span class="font-bold text-emerald-600">${data.barang.sisa}</span></div>
                    </div>
                </div>
            </div>
        `;
        
        html += '<h5 class="text-sm font-bold text-slate-700 mb-3 border-b border-slate-200 pb-2">Distribusi Ruangan</h5>';
        if (data.distribusi.length > 0) {
            html += '<ul class="space-y-3 mb-6">';
            data.distribusi.forEach(item => {
                let nama_bangunan = item.nama_bangunan || '-';
                let ket = item.keterangan || '-';
                html += `
                    <li class="bg-slate-50 p-3 rounded-xl border border-slate-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-sm text-slate-800">${item.nama_ruangan}</p>
                            <p class="text-xs text-slate-500">Gedung: ${nama_bangunan} | Ket: ${ket}</p>
                        </div>
                        <div class="bg-white px-3 py-1.5 rounded-lg border border-slate-200 font-bold text-sm shadow-sm text-slate-700">
                            ${item.jumlah} Unit
                        </div>
                    </li>
                `;
            });
            html += '</ul>';
        } else {
            html += '<p class="text-xs text-slate-500 italic mb-6">Belum ada distribusi ke ruangan.</p>';
        }
        
        html += '<h5 class="text-sm font-bold text-slate-700 mb-3 border-b border-slate-200 pb-2">Peminjaman Aktif</h5>';
        if (data.peminjaman.length > 0) {
            html += '<ul class="space-y-3">';
            data.peminjaman.forEach(item => {
                html += `
                    <li class="bg-amber-50 p-3 rounded-xl border border-amber-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-sm text-amber-900">${item.peminjam}</p>
                            <p class="text-xs text-amber-700">Status: ${item.status}</p>
                        </div>
                        <div class="bg-white px-3 py-1.5 rounded-lg border border-amber-200 font-bold text-sm shadow-sm text-amber-800">
                            ${item.jumlah} Unit
                        </div>
                    </li>
                `;
            });
            html += '</ul>';
        } else {
            html += '<p class="text-xs text-slate-500 italic">Tidak ada peminjaman aktif.</p>';
        }
        
        body.innerHTML = html;
    }

    function closeModalDetailDistribusi() {
        const modal = document.getElementById('modal-detail-distribusi');
        modal.classList.add('hidden');
    }

    // ── LIGHTBOX MODAL MULTI-FOTO ─────────────────────────────────
    let currentGalleryPhotos = [];
    let currentGalleryIndex = 0;

    function openFotoGallery(photos, title) {
        if (!photos || photos.length === 0) return;
        currentGalleryPhotos = photos;
        currentGalleryIndex = 0;

        document.getElementById('gallery-title').textContent = title || 'Foto Inventaris';
        renderGalleryImage();

        const modal = document.getElementById('modal-foto-gallery');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function renderGalleryImage() {
        const img = document.getElementById('gallery-main-img');
        const counter = document.getElementById('gallery-counter');
        const thumbs = document.getElementById('gallery-thumbs');
        const baseUrl = '<?= url('public/') ?>';

        const cur = currentGalleryPhotos[currentGalleryIndex];
        img.src = baseUrl + cur.replace(/^\//, '');
        counter.textContent = `${currentGalleryIndex + 1} / ${currentGalleryPhotos.length}`;

        thumbs.innerHTML = '';
        currentGalleryPhotos.forEach((p, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `w-12 h-12 rounded-xl overflow-hidden border-2 transition-all shrink-0 ${idx === currentGalleryIndex ? 'border-blue-500 scale-105 shadow-md' : 'border-transparent opacity-60 hover:opacity-100'}`;
            btn.innerHTML = `<img src="${baseUrl + p.replace(/^\//, '')}" class="w-full h-full object-cover">`;
            btn.onclick = () => {
                currentGalleryIndex = idx;
                renderGalleryImage();
            };
            thumbs.appendChild(btn);
        });

        // Hide prev/next if only 1 photo
        const prevBtn = document.getElementById('gallery-prev-btn');
        const nextBtn = document.getElementById('gallery-next-btn');
        if (currentGalleryPhotos.length <= 1) {
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
        } else {
            prevBtn.classList.remove('hidden');
            nextBtn.classList.remove('hidden');
        }
    }

    function galleryPrev() {
        if (currentGalleryPhotos.length <= 1) return;
        currentGalleryIndex = (currentGalleryIndex - 1 + currentGalleryPhotos.length) % currentGalleryPhotos.length;
        renderGalleryImage();
    }

    function galleryNext() {
        if (currentGalleryPhotos.length <= 1) return;
        currentGalleryIndex = (currentGalleryIndex + 1) % currentGalleryPhotos.length;
        renderGalleryImage();
    }

    function closeFotoGallery() {
        const modal = document.getElementById('modal-foto-gallery');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Keyboard support for gallery
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('modal-foto-gallery');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'Escape') closeFotoGallery();
            if (e.key === 'ArrowLeft') galleryPrev();
            if (e.key === 'ArrowRight') galleryNext();
        }
    });
</script>

<!-- Modal Lightbox Galeri Foto -->
<div id="modal-foto-gallery" class="fixed inset-0 z-[1100] hidden bg-slate-950/90 backdrop-blur-md flex flex-col justify-between">
    <!-- Header -->
    <div class="px-6 py-4 flex items-center justify-between text-white border-b border-white/10 shrink-0">
        <div>
            <h3 id="gallery-title" class="font-bold text-sm sm:text-base leading-tight">Foto Inventaris</h3>
            <p id="gallery-counter" class="text-xs text-white/60 font-mono mt-0.5">1 / 1</p>
        </div>
        <button type="button" onclick="closeFotoGallery()" class="p-2 rounded-full bg-white/10 hover:bg-white/20 active:scale-95 text-white transition-all text-xl font-bold">
            &times;
        </button>
    </div>

    <!-- Main Image Stage -->
    <div class="relative flex-1 flex items-center justify-center p-4 overflow-hidden">
        <button id="gallery-prev-btn" type="button" onclick="galleryPrev()" class="absolute left-4 p-3 rounded-full bg-white/10 hover:bg-white/20 text-white z-10 active:scale-90 transition-all backdrop-blur-sm">
            &#10094;
        </button>
        <img id="gallery-main-img" src="" alt="Preview" class="max-h-[70vh] max-w-full rounded-2xl shadow-2xl object-contain border border-white/10">
        <button id="gallery-next-btn" type="button" onclick="galleryNext()" class="absolute right-4 p-3 rounded-full bg-white/10 hover:bg-white/20 text-white z-10 active:scale-90 transition-all backdrop-blur-sm">
            &#10095;
        </button>
    </div>

    <!-- Thumbnails Footer -->
    <div class="p-4 bg-slate-900/60 backdrop-blur-md border-t border-white/10 shrink-0 flex justify-center">
        <div id="gallery-thumbs" class="flex gap-2 overflow-x-auto max-w-full py-1"></div>
    </div>
</div>

