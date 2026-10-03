<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <span class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shadow-inner">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </span>
            Penyusutan Aset
        </h1>
        <p class="text-sm text-slate-500 mt-1">Laporan penyusutan nilai barang inventaris sarana dan prasarana (Metode Garis Lurus).</p>
    </div>

    <!-- Action Toolbar (Cetak Landscape & Export Excel) -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="<?= url('kelola-sarpras/penyusutan/cetak') . '?bulan=' . $bulan . '&tahun=' . $tahun ?>" target="_blank" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-md shadow-slate-900/10 flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak PDF (Landscape)</span>
        </a>

        <a href="<?= url('kelola-sarpras/penyusutan/export-excel') . '?bulan=' . $bulan . '&tahun=' . $tahun ?>" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-xl transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Export Excel (.xls)</span>
        </a>
    </div>
</div>

<!-- Filter Box -->
<div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="text-sm font-semibold text-slate-700 flex items-center gap-2">
        <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span>Periode Laporan: <strong class="text-rose-600"><?= e($namaBulan ?? 'Bulan ' . $bulan) ?> <?= e($tahun) ?></strong></span>
    </div>

    <form action="" method="GET" class="flex flex-wrap items-center gap-2">
        <select name="bulan" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all cursor-pointer min-w-[130px]">
            <?php
            $months = [
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            foreach ($months as $k => $v) {
                $sel = (str_pad((string)$bulan, 2, '0', STR_PAD_LEFT) === $k) ? 'selected' : '';
                echo "<option value=\"$k\" $sel>$v</option>";
            }
            ?>
        </select>
        <select name="tahun" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all cursor-pointer min-w-[95px]">
            <?php
            $currentY = date('Y');
            for ($y = $currentY; $y >= $currentY - 10; $y--) {
                $sel = ($tahun == $y) ? 'selected' : '';
                echo "<option value=\"$y\" $sel>$y</option>";
            }
            ?>
        </select>
        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl transition-colors shadow-md shadow-rose-500/20 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <span>Tampilkan</span>
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-slate-50 rounded-full blur-2xl group-hover:bg-slate-100 transition-colors"></div>
        <p class="text-slate-500 font-medium text-xs mb-1 relative z-10">Jumlah Aset Terhitung</p>
        <h3 class="text-2xl font-black text-slate-800 relative z-10"><?= count($items) ?> <span class="text-sm font-normal text-slate-500">Barang</span></h3>
    </div>

    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary-50 rounded-full blur-2xl group-hover:bg-primary-100 transition-colors"></div>
        <p class="text-slate-500 font-medium text-xs mb-1 relative z-10">Total Harga Perolehan</p>
        <h3 class="text-2xl font-black text-slate-800 relative z-10">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></h3>
    </div>
    
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full blur-2xl group-hover:bg-amber-100 transition-colors"></div>
        <p class="text-slate-500 font-medium text-xs mb-1 relative z-10">Akumulasi Penyusutan</p>
        <h3 class="text-2xl font-black text-amber-600 relative z-10">Rp <?= number_format($totalAkumulasi, 0, ',', '.') ?></h3>
    </div>
    
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-50 rounded-full blur-2xl group-hover:bg-rose-100 transition-colors"></div>
        <p class="text-slate-500 font-medium text-xs mb-1 relative z-10">Total Nilai Buku Saat Ini</p>
        <h3 class="text-2xl font-black text-rose-600 relative z-10">Rp <?= number_format($totalNilaiBuku, 0, ',', '.') ?></h3>
    </div>
</div>

<!-- Table Card -->
<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 bg-slate-50 border-b border-slate-200 uppercase font-bold tracking-wider">
                <tr>
                    <th scope="col" class="px-5 py-4 text-center w-12">No</th>
                    <th scope="col" class="px-5 py-4">Nama Aset &amp; Merk</th>
                    <th scope="col" class="px-5 py-4">Ruangan / Lokasi</th>
                    <th scope="col" class="px-5 py-4 text-center">Tgl Perolehan</th>
                    <th scope="col" class="px-5 py-4 text-center">Masa Manfaat</th>
                    <th scope="col" class="px-5 py-4 text-center">Umur Pakai</th>
                    <th scope="col" class="px-5 py-4 text-right">Harga Perolehan</th>
                    <th scope="col" class="px-5 py-4 text-right">Penyusutan/Tahun</th>
                    <th scope="col" class="px-5 py-4 text-right">Akumulasi</th>
                    <th scope="col" class="px-5 py-4 text-right">Nilai Buku</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <div class="text-4xl mb-2">📦</div>
                            <p class="font-semibold">Tidak ada aset dengan harga perolehan yang tercatat.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($items as $b): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-800"><?= e($b['nama_barang']) ?></div>
                                <div class="text-[11px] font-mono text-primary-700 flex items-center gap-1.5 mt-0.5">
                                    <span><?= e($b['kode_barang']) ?></span>
                                    <span>&bull;</span>
                                    <span><?= e($b['kategori']) ?></span>
                                    <?php if (!empty($b['merk'])): ?>
                                        <span>&bull;</span>
                                        <span class="text-slate-500 font-sans italic"><?= e($b['merk']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <div class="font-medium text-xs"><?= e($b['ruangan'] ?? 'Tanpa Ruangan') ?></div>
                                <?php if (!empty($b['lokasi_gedung']) && $b['lokasi_gedung'] !== '-'): ?>
                                    <div class="text-[10px] text-slate-400"><?= e($b['lokasi_gedung']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-4 text-center text-xs text-slate-600"><?= e($b['tanggal_perolehan'] ?? '-') ?></td>
                            <td class="px-5 py-4 text-center font-bold text-slate-700"><?= $b['masa_manfaat'] ?> Thn</td>
                            <td class="px-5 py-4 text-center font-bold text-amber-600"><?= number_format($b['umur_pakai_tahun'], 1, ',', '.') ?> Thn</td>
                            <td class="px-5 py-4 text-right font-semibold text-slate-700">Rp <?= number_format($b['harga_awal'], 0, ',', '.') ?></td>
                            <td class="px-5 py-4 text-right font-semibold text-rose-500">Rp <?= number_format($b['penyusutan_per_tahun'], 0, ',', '.') ?></td>
                            <td class="px-5 py-4 text-right font-bold text-amber-600">Rp <?= number_format($b['akumulasi_penyusutan'], 0, ',', '.') ?></td>
                            <td class="px-5 py-4 text-right font-black text-rose-600">Rp <?= number_format($b['nilai_buku'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (!empty($items)): ?>
                <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200 text-xs">
                    <tr>
                        <td colspan="6" class="px-5 py-4 text-right uppercase tracking-wider text-slate-600">Total Keseluruhan</td>
                        <td class="px-5 py-4 text-right text-slate-900">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></td>
                        <td class="px-5 py-4 text-right text-rose-600">Rp <?= number_format($totalPenyusutanTahun ?? 0, 0, ',', '.') ?></td>
                        <td class="px-5 py-4 text-right text-amber-600">Rp <?= number_format($totalAkumulasi, 0, ',', '.') ?></td>
                        <td class="px-5 py-4 text-right text-rose-700 font-black">Rp <?= number_format($totalNilaiBuku, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
