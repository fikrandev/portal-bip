<?php
/**
 * CP & ATP - List View
 * Capaian Pembelajaran & Alur Tujuan Pembelajaran
 * Terintegrasi Berdasarkan Penugasan Mengajar Guru
 */
$selectedGuru = $filter_guru ?? $currentGuruId ?? null;
?>
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Grup Tahun Ajaran 2026/2027 (Ganjil & Genap)
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">
                Capaian Pembelajaran & Alur Tujuan Pembelajaran (CP & ATP)
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Penginputan CP Elemen & ATP oleh masing-masing guru pengampu sesuai mata pelajaran yang ditugaskan
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= url('kelola-perangkat-pembelajaran/cpatp/group/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>+ Buat CP & ATP Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Card & Unit Selector -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <!-- Unit Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-semibold">
            <span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider mr-2">Filter Unit:</span>
            <a href="<?= url('kelola-perangkat-pembelajaran/cpatp?unit=&ta=' . $filter_ta . '&semester=' . $filter_semester . '&guru_id=' . $selectedGuru) ?>" 
               class="px-3.5 py-1.5 rounded-xl transition-all <?= empty($filter_unit) ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                🌐 Semua Unit
            </a>
            <?php foreach ($unit_list as $uKey => $uInfo): ?>
                <a href="<?= url('kelola-perangkat-pembelajaran/cpatp?unit=' . $uKey . '&ta=' . $filter_ta . '&semester=' . $filter_semester . '&guru_id=' . $selectedGuru) ?>" 
                   class="px-3.5 py-1.5 rounded-xl transition-all <?= $filter_unit === $uKey ? 'bg-slate-900 text-white font-bold shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <?= $uInfo['icon'] ?> Unit <?= $uKey ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="<?= url('kelola-perangkat-pembelajaran/cpatp') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-2 border-t border-slate-100">
            <input type="hidden" name="unit" value="<?= e($filter_unit) ?>">
            
            <!-- Filter Guru Pengampu -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Guru Pengampu</label>
                <select name="guru_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Semua Guru Pengampu</option>
                    <?php foreach ($guru_list as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $selectedGuru == $g['id'] ? 'selected' : '' ?>>
                            <?= e($g['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Tahun Ajaran -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tahun Ajaran</label>
                <select name="ta" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Semua Tahun Ajaran</option>
                    <?php foreach ($ta_list as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= $filter_ta == $ta['id'] ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Semester -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Semester</label>
                <select name="semester" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Semua Semester</option>
                    <option value="Ganjil" <?= $filter_semester === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                    <option value="Genap" <?= $filter_semester === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                </select>
            </div>

            <!-- Filter Status -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Dokumen</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="draft" <?= $filter_status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="diajukan" <?= $filter_status === 'diajukan' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="disetujui" <?= $filter_status === 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="ditolak" <?= $filter_status === 'ditolak' ? 'selected' : '' ?>>Perlu Revisi</option>
                </select>
            </div>

            <!-- Pencarian -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Pencarian</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari mapel / judul..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl text-xs transition-colors">
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
                        <th class="py-3.5 px-4">Unit / Fase</th>
                        <th class="py-3.5 px-4">Judul Dokumen / Wadah</th>
                        <th class="py-3.5 px-4">Tahun Ajaran & Smt</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                Belum ada data CP & ATP yang ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $row): ?>
                            <?php 
                                $rowUnit = $row['unit'] ?? 'SD';
                                $uBadge = $unit_list[$rowUnit]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-300';
                                $uIcon = $unit_list[$rowUnit]['icon'] ?? '🏫';
                            ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    <?= ($page - 1) * 15 + ($idx + 1) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold border <?= $uBadge ?>">
                                            <span><?= $uIcon ?></span>
                                            <span>Unit <?= e($rowUnit) ?></span>
                                        </span>
                                        <?php if (!empty($row['fase'])): ?>
                                            <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                                                <?= e($row['fase']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 leading-snug">
                                        <a href="<?= url("kelola-perangkat-pembelajaran/cpatp/group/{$row['id']}") ?>" class="hover:text-indigo-600 transition-colors">
                                            <?= e($row['judul'] ?: 'Kumpulan CP ATP') ?>
                                        </a>
                                    </div>
                                    <div class="text-[11px] font-semibold text-slate-500 mt-0.5">
                                        Wadah Dokumen
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800"><?= e($row['nama_tahun']) ?></div>
                                    <div class="text-[11px] text-slate-500">Semester <?= e($row['semester']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-700">Wadah Dokumen</span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?= url("kelola-perangkat-pembelajaran/cpatp/group/{$row['id']}") ?>" title="Buka Grup" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold transition-colors">
                                            Buka <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                        </a>
                                        <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/cpatp/group/delete/{$row['id']}") ?>" onsubmit="return confirm('Hapus grup beserta semua dokumen CP ATP di dalamnya?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" title="Hapus Grup" class="p-1.5 rounded-xl bg-rose-100 hover:bg-rose-200 text-rose-700 transition-colors">
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
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">Total <strong><?= $total ?></strong> dokumen CP & ATP</span>
                <div class="flex items-center gap-1.5">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="<?= url("kelola-perangkat-pembelajaran/cpatp?page={$p}&unit={$filter_unit}&ta={$filter_ta}&semester={$filter_semester}&guru_id={$selectedGuru}&search={$search}") ?>" 
                           class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold transition-colors <?= $p === $page ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
