<?php
/**
 * Laporan Kondisi & Inventaris Aset Sarpras
 * Portal BIP
 */
$ruanganListGrouped = $laporan['ruangan_list'] ?? [];
$unassigned = $laporan['unassigned'] ?? [];
$rekap = $laporan['rekap'] ?? [];

$unassignedItemCount = (int)($unassigned['total_item'] ?? 0);
$unassignedQty = (int)($unassigned['total_qty'] ?? 0);
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-sarpras') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
                    &larr; Dashboard Sarpras
                </a>
                <span class="text-slate-300">•</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                    Laporan Resmi &amp; Audit
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1 flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner text-lg">
                    📊
                </span>
                <span>Laporan Inventaris &amp; Kondisi Aset</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Rekapitulasi dan rincian aset fisik per ruangan kampus, kondisi kelayakan, serta aset cadangan tanpa ruangan.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="<?= url('kelola-sarpras/laporan/cetak') ?>?ruangan_id=<?= urlencode($filter_ruangan) ?>&kategori_id=<?= urlencode($filter_kategori) ?>" target="_blank"
               class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs sm:text-sm rounded-2xl transition-all shadow-md shadow-rose-500/20 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v2.796c0 1.18.91 2.164 2.09 2.201a51.964 51.964 0 0 0 6.32 0c1.18-.037 2.09-1.022 2.09-2.201V9.456Z" />
                </svg>
                <span><?= empty($filter_ruangan) ? 'Cetak Seluruh Ruangan (PDF)' : 'Cetak Laporan (PDF)' ?></span>
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm">
        <form action="<?= url('kelola-sarpras/laporan') ?>" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
            <!-- Filter Ruangan -->
            <div class="flex-1">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Ruangan / Penempatan</label>
                <select name="ruangan_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-medium text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all cursor-pointer">
                    <option value="">-- Semua Ruangan (Cetak Berkelompok Per Ruangan) --</option>
                    <?php foreach ($ruanganList as $r): ?>
                        <?php $sel = ($filter_ruangan !== '' && $filter_ruangan !== 'tanpa_ruangan' && (int)$filter_ruangan === (int)$r['id']) ? 'selected' : ''; ?>
                        <option value="<?= $r['id'] ?>" <?= $sel ?>>
                            <?= e($r['nama_ruangan']) ?> (<?= (int)$r['total_barang'] ?> aset)
                        </option>
                    <?php endforeach; ?>
                    <option value="tanpa_ruangan" <?= ($filter_ruangan === 'tanpa_ruangan') ? 'selected' : '' ?>>
                        📦 Belum Ditempatkan / Tanpa Ruangan (<?= $unassignedItemCount ?> aset)
                    </option>
                </select>
            </div>

            <!-- Filter Kategori -->
            <div class="w-full md:w-64">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Kategori</label>
                <select name="kategori_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-medium text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all cursor-pointer">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($kategoriList as $k): ?>
                        <?php $sel = ($filter_kategori == $k['id']) ? 'selected' : ''; ?>
                        <option value="<?= $k['id'] ?>" <?= $sel ?>><?= e($k['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2 pt-2 md:pt-4">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-2xl transition-all shadow-md shadow-indigo-500/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span>Terapkan</span>
                </button>
                <?php if (!empty($filter_ruangan) || !empty($filter_kategori)): ?>
                    <a href="<?= url('kelola-sarpras/laporan') ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm rounded-2xl transition-colors flex items-center justify-center" title="Reset Filter">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden">
            <p class="text-slate-500 font-semibold text-xs mb-1">Total Aset / Jenis</p>
            <h3 class="text-2xl sm:text-3xl font-black text-slate-800"><?= number_format($totalAset, 0, ',', '.') ?></h3>
            <span class="text-[11px] text-slate-400">Total <?= number_format($totalQty, 0, ',', '.') ?> unit fisik</span>
        </div>
        
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden">
            <p class="text-slate-500 font-semibold text-xs mb-1">Kondisi Baik</p>
            <h3 class="text-2xl sm:text-3xl font-black text-emerald-600"><?= number_format($statBaik, 0, ',', '.') ?></h3>
            <span class="text-[11px] text-emerald-600 font-bold">Unit Layak Pakai</span>
        </div>
        
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden">
            <p class="text-slate-500 font-semibold text-xs mb-1">Rusak Ringan</p>
            <h3 class="text-2xl sm:text-3xl font-black text-amber-600"><?= number_format($statRusakRingan, 0, ',', '.') ?></h3>
            <span class="text-[11px] text-amber-600 font-bold">Perlu Servis</span>
        </div>
        
        <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden">
            <p class="text-slate-500 font-semibold text-xs mb-1">Rusak Berat</p>
            <h3 class="text-2xl sm:text-3xl font-black text-rose-600"><?= number_format($statRusakBerat, 0, ',', '.') ?></h3>
            <span class="text-[11px] text-rose-600 font-bold">Tidak Berfungsi</span>
        </div>

        <div class="col-span-2 lg:col-span-1 bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden">
            <p class="text-slate-500 font-semibold text-xs mb-1">Total Nilai Perolehan</p>
            <h3 class="text-xl sm:text-2xl font-black text-indigo-700">Rp <?= number_format($totalNilai, 0, ',', '.') ?></h3>
            <span class="text-[11px] text-slate-400">Akumulasi Harga Aset</span>
        </div>
    </div>

    <!-- Mode Info Notice -->
    <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-indigo-900">
        <div class="flex items-center gap-2">
            <span class="text-lg">ℹ️</span>
            <div>
                <?php if (empty($filter_ruangan)): ?>
                    <strong>Laporan Seluruh Ruangan (Grouped):</strong> Hasil cetak otomatis dikelompokkan <strong>per ruangan</strong> di lembar tersendiri dengan penanggung jawabnya, plus seksi khusus <strong>aset tanpa ruangan</strong>.
                <?php elseif ($filter_ruangan === 'tanpa_ruangan'): ?>
                    <strong>Laporan Khusus Aset Tanpa Ruangan:</strong> Menampilkan stok barang cadangan atau aset yang belum dialokasikan ke ruangan tertentu.
                <?php else: ?>
                    <strong>Laporan Ruangan Terpilih:</strong> Menampilkan rincian inventaris khusus pada ruangan ini beserta lembar pengesahan penanggung jawab ruangan.
                <?php endif; ?>
            </div>
        </div>
        <a href="<?= url('kelola-sarpras/laporan/cetak') ?>?ruangan_id=<?= urlencode($filter_ruangan) ?>&kategori_id=<?= urlencode($filter_kategori) ?>" target="_blank" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl whitespace-nowrap shadow-sm text-center">
            Pratinjau / Cetak PDF &rarr;
        </a>
    </div>

    <!-- Data Display Section -->
    <?php if (empty($filter_ruangan)): ?>
        <!-- ========================================== -->
        <!-- MODE SEMUA RUANGAN (GROUPED PER RUANGAN)   -->
        <!-- ========================================== -->
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>🏢 Rincian Inventaris per Ruangan</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700">
                        <?= count($ruanganListGrouped) ?> Ruangan Aktif
                    </span>
                </h2>
            </div>

            <?php if (empty($ruanganListGrouped)): ?>
                <div class="bg-white rounded-3xl p-8 text-center border border-slate-200 text-slate-400">
                    Belum ada data barang di ruangan manapun.
                </div>
            <?php else: ?>
                <?php foreach ($ruanganListGrouped as $rg): 
                    $rInfo = $rg['ruangan'];
                    $items = $rg['items'];
                ?>
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
                        <!-- Room Header -->
                        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                                        <span><?= e($rInfo['nama_ruangan']) ?></span>
                                    </h3>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <?= e($rInfo['unit']) ?>
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 flex-wrap">
                                    <span>Gedung: <strong><?= e($rInfo['nama_bangunan'] ?? $rInfo['lokasi_gedung'] ?? 'Gedung Utama') ?></strong></span>
                                    <span>•</span>
                                    <span>PJ: <strong class="text-slate-700"><?= e($rInfo['penanggung_jawab'] ?: 'Belum Ditentukan') ?></strong></span>
                                    <span>•</span>
                                    <span>Total: <strong><?= (int)$rg['total_item'] ?> Jenis (<?= (int)$rg['total_qty'] ?> Unit)</strong></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black text-indigo-700 px-3 py-1 bg-white border border-slate-200 rounded-xl">
                                    Nilai: Rp <?= number_format($rg['total_nilai'], 0, ',', '.') ?>
                                </span>
                                <a href="<?= url('kelola-sarpras/laporan/cetak?ruangan_id=' . $rInfo['id']) ?>" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors flex items-center gap-1" title="Cetak Khusus Ruangan Ini">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v2.796c0 1.18.91 2.164 2.09 2.201a51.964 51.964 0 0 0 6.32 0c1.18-.037 2.09-1.022 2.09-2.201V9.456Z" /></svg>
                                    <span>Cetak</span>
                                </a>
                            </div>
                        </div>

                        <!-- Room Items Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs sm:text-sm border-collapse">
                                <thead class="bg-slate-50/50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                                    <tr>
                                        <th class="py-2.5 px-4 w-10 text-center">No</th>
                                        <th class="py-2.5 px-4">Kode &amp; Nama Barang</th>
                                        <th class="py-2.5 px-4">Merk / Model</th>
                                        <th class="py-2.5 px-4 text-center">Jumlah</th>
                                        <th class="py-2.5 px-4 text-center">Kondisi</th>
                                        <th class="py-2.5 px-4 text-center">Sumber Dana</th>
                                        <th class="py-2.5 px-4 text-right">Nilai Total (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php $no = 1; foreach ($items as $it): ?>
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="py-2.5 px-4 text-center text-slate-400"><?= $no++ ?></td>
                                            <td class="py-2.5 px-4">
                                                <div class="font-bold text-slate-800"><?= e($it['nama_barang']) ?></div>
                                                <div class="text-[10px] text-slate-500 font-mono"><?= e($it['kode_barang']) ?></div>
                                            </td>
                                            <td class="py-2.5 px-4 text-slate-600"><?= e($it['merk_model'] ?: '-') ?></td>
                                            <td class="py-2.5 px-4 text-center font-bold text-slate-800">
                                                <?= (int)$it['jumlah'] ?> <?= e($it['satuan']) ?>
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $it['kondisi'] === 'Baik' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' ?>">
                                                    <?= e($it['kondisi']) ?>
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center text-xs text-slate-500"><?= e($it['sumber_dana']) ?></td>
                                            <td class="py-2.5 px-4 text-right font-semibold text-slate-700"><?= number_format($it['total_nilai'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Aset Tanpa Ruangan Section -->
            <?php if (!empty($unassigned['items'])): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-amber-200/80 overflow-hidden">
                    <div class="p-4 sm:p-5 border-b border-amber-100 bg-amber-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                                    <span>📦 <?= e($unassigned['ruangan']['nama_ruangan']) ?></span>
                                </h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                    Stok Cadangan / Belum Didistribusikan
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Aset fisik yang belum dialokasikan ke ruangan tertentu atau sisa kuantitas belum terdistribusi.
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-amber-800 px-3 py-1 bg-white border border-amber-200 rounded-xl">
                                Total: <?= (int)$unassigned['total_item'] ?> Aset (<?= (int)$unassigned['total_qty'] ?> Unit)
                            </span>
                            <a href="<?= url('kelola-sarpras/laporan/cetak?ruangan_id=tanpa_ruangan') ?>" target="_blank" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl transition-colors flex items-center gap-1">
                                <span>Cetak Aset Ini</span>
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs sm:text-sm border-collapse">
                            <thead class="bg-amber-50/30 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="py-2.5 px-4 w-10 text-center">No</th>
                                    <th class="py-2.5 px-4">Kode &amp; Nama Barang</th>
                                    <th class="py-2.5 px-4">Merk / Model</th>
                                    <th class="py-2.5 px-4 text-center">Jumlah Sisa</th>
                                    <th class="py-2.5 px-4 text-center">Kondisi</th>
                                    <th class="py-2.5 px-4 text-center">Sumber Dana</th>
                                    <th class="py-2.5 px-4 text-right">Nilai Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php $noU = 1; foreach ($unassigned['items'] as $it): ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-2.5 px-4 text-center text-slate-400"><?= $noU++ ?></td>
                                        <td class="py-2.5 px-4">
                                            <div class="font-bold text-slate-800"><?= e($it['nama_barang']) ?></div>
                                            <div class="text-[10px] text-slate-500 font-mono"><?= e($it['kode_barang']) ?></div>
                                        </td>
                                        <td class="py-2.5 px-4 text-slate-600"><?= e($it['merk_model'] ?: '-') ?></td>
                                        <td class="py-2.5 px-4 text-center font-bold text-amber-700">
                                            <?= (int)$it['jumlah'] ?> <?= e($it['satuan']) ?>
                                        </td>
                                        <td class="py-2.5 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $it['kondisi'] === 'Baik' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' ?>">
                                                <?= e($it['kondisi']) ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-center text-xs text-slate-500"><?= e($it['sumber_dana']) ?></td>
                                        <td class="py-2.5 px-4 text-right font-semibold text-slate-700"><?= number_format($it['total_nilai'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- MODE FILTER TUNGGAL (1 RUANGAN / KHUSUS)  -->
        <!-- ========================================== -->
        <?php 
        $singleItems = [];
        $singleTitle = '';
        $singlePj = '';

        if ($filter_ruangan === 'tanpa_ruangan') {
            $singleItems = $unassigned['items'] ?? [];
            $singleTitle = $unassigned['ruangan']['nama_ruangan'];
            $singlePj = $unassigned['ruangan']['penanggung_jawab'];
        } elseif (!empty($ruanganListGrouped)) {
            $singleItems = $ruanganListGrouped[0]['items'] ?? [];
            $singleTitle = $ruanganListGrouped[0]['ruangan']['nama_ruangan'];
            $singlePj = $ruanganListGrouped[0]['ruangan']['penanggung_jawab'] ?: 'Belum Ditentukan';
        }
        ?>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">
                        <span>Daftar Aset: <?= e($singleTitle) ?></span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Penanggung Jawab: <strong><?= e($singlePj) ?></strong> • Total: <strong><?= count($singleItems) ?> Barang</strong>
                    </p>
                </div>
                <div>
                    <a href="<?= url('kelola-sarpras/laporan/cetak?ruangan_id=' . urlencode($filter_ruangan) . '&kategori_id=' . urlencode($filter_kategori)) ?>" target="_blank" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-1.5">
                        <span>Cetak Laporan Ruangan Ini (PDF)</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm border-collapse">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4 w-10 text-center">No</th>
                            <th class="py-3 px-4">Kode &amp; Nama Barang</th>
                            <th class="py-3 px-4">Merk / Model</th>
                            <th class="py-3 px-4 text-center">Jumlah</th>
                            <th class="py-3 px-4 text-center">Kondisi</th>
                            <th class="py-3 px-4 text-center">Sumber Dana</th>
                            <th class="py-3 px-4 text-right">Nilai Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($singleItems)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    Belum ada data barang pada pilihan ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($singleItems as $it): ?>
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-3 px-4 text-center text-slate-400"><?= $no++ ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800"><?= e($it['nama_barang']) ?></div>
                                        <div class="text-[10px] text-slate-500 font-mono"><?= e($it['kode_barang']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600"><?= e($it['merk_model'] ?: '-') ?></td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-800">
                                        <?= (int)$it['jumlah'] ?> <?= e($it['satuan']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $it['kondisi'] === 'Baik' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' ?>">
                                            <?= e($it['kondisi']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs text-slate-500"><?= e($it['sumber_dana']) ?></td>
                                    <td class="py-3 px-4 text-right font-semibold text-slate-700"><?= number_format($it['total_nilai'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
