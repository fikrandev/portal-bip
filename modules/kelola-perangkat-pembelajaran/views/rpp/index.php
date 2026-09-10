<?php
/**
 * RPP / Modul Ajar - Index View with Wadah Grup & Filter Unit
 */
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Wadah Dokumen Modul Ajar
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">RPP & Modul Ajar (JSIT)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Rencana Pelaksanaan Pembelajaran & Modul Ajar Kurikulum Merdeka Pendekatan TERPADU & INTROFLEX</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-perangkat-pembelajaran/rpp/group/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>+ Buat Grup RPP Baru</span>
            </a>
        </div>
    </div>

    <!-- Filters Bar & Unit Pills -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <!-- Quick Unit Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <span class="font-bold text-slate-400 uppercase text-[10px] whitespace-nowrap mr-1">Filter Unit:</span>
            <a href="<?= url('kelola-perangkat-pembelajaran/rpp?' . http_build_query(array_merge($_GET, ['unit' => '']))) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold transition-all whitespace-nowrap <?= empty($filter_unit) ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                🌐 Semua Unit
            </a>
            <?php foreach ($unit_list as $uKey => $uInfo): ?>
                <?php $isActiveUnit = ($filter_unit === $uKey); ?>
                <a href="<?= url('kelola-perangkat-pembelajaran/rpp?' . http_build_query(array_merge($_GET, ['unit' => $uKey]))) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold transition-all whitespace-nowrap inline-flex items-center gap-1.5 <?= $isActiveUnit ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <span><?= $uInfo['icon'] ?></span>
                    <span>Unit <?= $uKey ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="GET" action="<?= url('kelola-perangkat-pembelajaran/rpp') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t border-slate-100">
            <input type="hidden" name="unit" value="<?= e($filter_unit) ?>">
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tahun Ajaran</label>
                <select name="ta" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">Semua Tahun Ajaran</option>
                    <?php foreach ($ta_list as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= $filter_ta == $ta['id'] ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Semester</label>
                <select name="semester" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">Semua Semester</option>
                    <option value="Ganjil" <?= $filter_semester === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                    <option value="Genap" <?= $filter_semester === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Dokumen</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="draft" <?= $filter_status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="diajukan" <?= $filter_status === 'diajukan' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="disetujui" <?= $filter_status === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="ditolak" <?= $filter_status === 'ditolak' ? 'selected' : '' ?>>Perlu Revisi</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Pencarian</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari judul wadah / RPP..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl text-xs transition-colors">
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Unit Sekolah</th>
                        <th class="py-3.5 px-4">Judul Wadah Grup RPP</th>
                        <th class="py-3.5 px-4">Tahun Ajaran & Smt</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Dokumen</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-2xl">📑</div>
                                <p class="text-sm font-semibold text-slate-600">Belum ada wadah grup RPP / Modul Ajar.</p>
                                <p class="text-xs text-slate-400 mt-1">Klik tombol "+ Buat Grup RPP Baru" di atas untuk menghubungkan dengan Grup CP & ATP.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $item): 
                            $uInfo = $unit_list[$item['unit']] ?? ['icon' => '🏫', 'name' => 'Unit ' . $item['unit'], 'badge' => 'bg-slate-100 text-slate-800'];
                        ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400"><?= $offset + $idx + 1 ?></td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold <?= $uInfo['badge'] ?>">
                                        <span><?= $uInfo['icon'] ?></span>
                                        <span>Unit <?= e($item['unit']) ?></span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="<?= url("kelola-perangkat-pembelajaran/rpp/group/{$item['id']}") ?>" class="font-bold text-slate-800 hover:text-teal-600 transition-colors block text-sm">
                                        <?= e($item['judul']) ?>
                                    </a>
                                    <span class="text-[11px] text-slate-400">Dibuat: <?= date('d M Y, H:i', strtotime($item['created_at'])) ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800"><?= e($item['nama_tahun'] ?? '-') ?></div>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-md text-[10px] font-bold <?= ($item['semester'] === 'Ganjil') ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' ?>">
                                        Semester <?= e($item['semester']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-800 border border-teal-200">
                                        📄 <?= (int)($item['doc_count'] ?? 0) ?> Dokumen RPP
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php
                                    $statusBadges = [
                                        'draft' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        'diajukan' => 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse',
                                        'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200'
                                    ];
                                    $statusLabels = [
                                        'draft' => 'Draft',
                                        'diajukan' => 'Menunggu Verifikasi',
                                        'disetujui' => 'Disetujui',
                                        'ditolak' => 'Perlu Revisi'
                                    ];
                                    $st = $item['status'] ?? 'draft';
                                    ?>
                                    <span class="inline-block px-2.5 py-1 rounded-xl text-[10px] font-bold border <?= $statusBadges[$st] ?? $statusBadges['draft'] ?>">
                                        <?= $statusLabels[$st] ?? ucfirst($st) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?= url("kelola-perangkat-pembelajaran/rpp/group/{$item['id']}") ?>" class="px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-xs transition-colors" title="Buka Wadah Grup">
                                            Buka Wadah &rarr;
                                        </a>
                                        <a href="<?= url("kelola-perangkat-pembelajaran/rpp/group/{$item['id']}/cetak-semua") ?>" target="_blank" class="p-1.5 rounded-xl bg-slate-100 hover:bg-emerald-50 hover:text-emerald-600 text-slate-600 transition-colors" title="Cetak Semua Dokumen dalam Grup">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                                        </a>
                                        <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/rpp/group/delete/{$item['id']}") ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus wadah grup ini beserta seluruh dokumen RPP di dalamnya?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-400 transition-colors" title="Hapus Grup">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
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
            <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <div>Menampilkan <?= count($items) ?> dari <?= $total ?> wadah</div>
                <div class="flex items-center gap-1">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="<?= url('kelola-perangkat-pembelajaran/rpp?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="px-3 py-1.5 rounded-lg font-bold <?= ($page == $p) ? 'bg-teal-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
