<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <span class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </span>
            Laporan Kondisi Aset
        </h1>
        <p class="text-sm text-slate-500 mt-1">Laporan jumlah dan detail aset berdasarkan status kondisi fisiknya.</p>
    </div>
    
    <form action="" method="GET" class="flex flex-col sm:flex-row items-center gap-2 bg-white p-2 rounded-2xl shadow-sm border border-slate-200">
        <select name="ruangan_id" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all cursor-pointer min-w-[150px]">
            <option value="">-- Semua Ruangan / Kelas --</option>
            <?php foreach ($ruanganList as $r): ?>
                <?php $sel = ($filter_ruangan == $r['id']) ? 'selected' : ''; ?>
                <option value="<?= $r['id'] ?>" <?= $sel ?>><?= e($r['nama_ruangan']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="kategori_id" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all cursor-pointer min-w-[150px]">
            <option value="">-- Semua Kategori --</option>
            <?php foreach ($kategoriList as $k): ?>
                <?php $sel = ($filter_kategori == $k['id']) ? 'selected' : ''; ?>
                <option value="<?= $k['id'] ?>" <?= $sel ?>><?= e($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="w-full sm:w-auto px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors shadow-md shadow-indigo-500/20 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            Filter
        </button>
        <?php if (!empty($filter_ruangan) || !empty($filter_kategori)): ?>
            <a href="<?= url('kelola-sarpras/laporan') ?>" class="w-full sm:w-auto px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-colors flex items-center justify-center">
                Reset
            </a>
        <?php endif; ?>
        <a href="<?= url('kelola-sarpras/laporan/cetak') ?>?ruangan_id=<?= urlencode($filter_ruangan) ?>&kategori_id=<?= urlencode($filter_kategori) ?>" target="_blank"
           class="w-full sm:w-auto px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-xl transition-colors shadow-md shadow-rose-500/20 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak PDF
        </a>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-slate-100 rounded-full blur-2xl group-hover:bg-slate-200 transition-colors"></div>
        <p class="text-slate-500 font-medium text-xs mb-1 relative z-10">Total Aset / Barang</p>
        <h3 class="text-3xl font-black text-slate-800 relative z-10"><?= number_format($totalAset, 0, ',', '.') ?></h3>
    </div>
    
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full blur-2xl group-hover:bg-emerald-100 transition-colors"></div>
        <p class="text-slate-500 font-medium text-xs mb-1 relative z-10">Kondisi Baik</p>
        <h3 class="text-3xl font-black text-emerald-600 relative z-10"><?= number_format($statBaik, 0, ',', '.') ?></h3>
    </div>
    
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-50 rounded-full blur-2xl group-hover:bg-amber-100 transition-colors"></div>
        <p class="text-slate-500 font-medium text-xs mb-1 relative z-10">Kondisi Rusak Ringan</p>
        <h3 class="text-3xl font-black text-amber-600 relative z-10"><?= number_format($statRusakRingan, 0, ',', '.') ?></h3>
    </div>
    
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden group">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-50 rounded-full blur-2xl group-hover:bg-rose-100 transition-colors"></div>
        <p class="text-slate-500 font-medium text-xs mb-1 relative z-10">Kondisi Rusak Berat</p>
        <h3 class="text-3xl font-black text-rose-600 relative z-10"><?= number_format($statRusakBerat, 0, ',', '.') ?></h3>
    </div>
</div>

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 bg-slate-50 border-b border-slate-200 uppercase font-bold tracking-wider">
                <tr>
                    <th scope="col" class="px-6 py-4">Nama Barang</th>
                    <th scope="col" class="px-6 py-4">Kode & Kategori</th>
                    <th scope="col" class="px-6 py-4">Lokasi Ruangan</th>
                    <th scope="col" class="px-6 py-4 text-center">Stok / Qty</th>
                    <th scope="col" class="px-6 py-4 text-center">Kondisi Fisik</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            <div class="text-4xl mb-2">🔍</div>
                            <p class="font-semibold">Tidak ada data aset ditemukan.</p>
                            <p class="text-[11px] mt-1 text-slate-400">Coba ubah filter ruangan atau kategori.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $b): ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800"><?= e($b['nama_barang']) ?></div>
                                <?php if (!empty($b['merk_model'])): ?>
                                    <div class="text-[11px] text-slate-400">Merk: <?= e($b['merk_model']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-mono text-primary-700 font-bold text-xs"><?= e($b['kode_barang']) ?></div>
                                <div class="text-[11px] font-semibold text-slate-500"><?= e($b['nama_kategori'] ?? 'Tanpa Kategori') ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-700"><?= e($b['nama_ruangan'] ?? 'Belum Ditentukan') ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-bold text-slate-800 text-lg"><?= $b['jumlah'] ?></span>
                                <span class="text-[10px] text-slate-400 ml-1"><?= e($b['satuan']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($b['kondisi'] === 'Baik'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                        Baik
                                    </span>
                                <?php elseif ($b['kondisi'] === 'Rusak Ringan'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        Rusak Ringan
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                        Rusak Berat
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
