<?php
/**
 * Kaldik - Index View with Master Group Kaldik per Unit (PAUD, SD, SMP, SMA) & Active Status
 */
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2.5">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                Grup Kalender Pendidikan (Kaldik)
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola Master Kalender Pendidikan per Unit & Tahun Ajaran. Kaldik yang aktif menjadi acuan resmi guru saat membuat HES & HEB.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-perangkat-pembelajaran/kaldik/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-emerald-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Grup Kaldik
            </a>
        </div>
    </div>

    <!-- Info Guide Banner -->
    <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 border border-emerald-200/80 rounded-3xl p-4 sm:p-5 flex items-start gap-3.5 shadow-sm">
        <div class="w-9 h-9 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-bold text-base flex-shrink-0 shadow-sm">
            💡
        </div>
        <div class="text-xs text-slate-700 leading-relaxed space-y-1">
            <h4 class="font-bold text-emerald-950 text-[13px]">Cara Kerja Manajemen Grup Kalender Pendidikan:</h4>
            <p class="text-slate-600">
                1. Buat grup Kaldik baru untuk masing-masing unit (<strong>PAUD, SD, SMP, SMA</strong>) dan setel statusnya menjadi <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">🟢 Aktif</span>.<br>
                2. Di dalam grup tersebut, Anda dapat mengisi daftar agenda akademik, jadwal KBM, PTS/STS, PAS/SAS, dan hari libur.<br>
                3. Ketika pergantian tahun ajaran di masa depan, nonaktifkan Kaldik lama dan buat grup Kaldik baru untuk tahun berikutnya.
            </p>
        </div>
    </div>

    <!-- Filters Bar & Unit Tabs -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <!-- Quick Unit Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <span class="font-bold text-slate-400 uppercase text-[10px] whitespace-nowrap mr-1">Filter Unit:</span>
            <a href="<?= url('kelola-perangkat-pembelajaran/kaldik?' . http_build_query(array_merge($_GET, ['unit' => '']))) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold transition-all whitespace-nowrap <?= empty($filter_unit) ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                🌐 Semua Unit
            </a>
            <?php foreach ($unit_list as $uKey => $uInfo): ?>
                <?php $isActiveUnit = ($filter_unit === $uKey); ?>
                <a href="<?= url('kelola-perangkat-pembelajaran/kaldik?' . http_build_query(array_merge($_GET, ['unit' => $uKey]))) ?>" class="px-3.5 py-1.5 rounded-xl font-semibold transition-all whitespace-nowrap inline-flex items-center gap-1.5 <?= $isActiveUnit ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <span><?= $uInfo['icon'] ?? '🏫' ?></span>
                    <span>Unit <?= $uKey ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="GET" action="<?= url('kelola-perangkat-pembelajaran/kaldik') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t border-slate-100">
            <input type="hidden" name="unit" value="<?= e($filter_unit) ?>">
            
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Penggunaan</label>
                <select name="is_active" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Status (Aktif & Arsip)</option>
                    <option value="1" <?= ($filter_is_active === '1') ? 'selected' : '' ?>>🟢 Hanya Kaldik Aktif</option>
                    <option value="0" <?= ($filter_is_active === '0') ? 'selected' : '' ?>>⚪ Hanya Non-Aktif / Arsip</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Tahun Ajaran</label>
                <select name="ta" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Tahun Ajaran</option>
                    <?php foreach ($ta_list as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= $filter_ta == $ta['id'] ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status Persetujuan</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Status Persetujuan</option>
                    <option value="draft" <?= $filter_status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="diajukan" <?= $filter_status === 'diajukan' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="disetujui" <?= $filter_status === 'disetujui' ? 'selected' : '' ?>>Disetujui Resmi</option>
                    <option value="ditolak" <?= $filter_status === 'ditolak' ? 'selected' : '' ?>>Perlu Revisi</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Pencarian</label>
                <div class="flex items-center gap-2">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama kaldik / penyusun..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-xs transition-colors">
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table / List of Kaldik Groups -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <th class="py-3.5 px-4 font-bold">Unit</th>
                        <th class="py-3.5 px-4 font-bold">Nama Grup Kalender Pendidikan</th>
                        <th class="py-3.5 px-4 font-bold">Tahun Ajaran</th>
                        <th class="py-3.5 px-4 font-bold">Status Acuan</th>
                        <th class="py-3.5 px-4 font-bold">Total Agenda</th>
                        <th class="py-3.5 px-4 font-bold">Penyusun / PJ</th>
                        <th class="py-3.5 px-4 font-bold text-right">Aksi & Kelola</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-3xl">📅</span>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada Grup Kalender Pendidikan <?= !empty($filter_unit) ? "untuk Unit {$filter_unit}" : '' ?></p>
                                    <p class="text-xs text-slate-400">Klik tombol "Tambah Grup Kaldik" di atas untuk menambahkan master kalender pendidikan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $row): ?>
                            <?php
                            $rowUnit = $row['unit'] ?? 'SD';
                            $uBadge = $unit_list[$rowUnit]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-300';
                            $uIcon = $unit_list[$rowUnit]['icon'] ?? '🏫';
                            $isActive = !empty($row['is_active']);

                            $konten = !empty($row['konten_json']) ? json_decode($row['konten_json'], true) : [];
                            $totalAgendas = !empty($konten['agendas']) ? count($konten['agendas']) : 0;
                            ?>
                            <tr class="hover:bg-slate-50/70 transition-colors <?= $isActive ? 'bg-emerald-50/20' : '' ?>">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold border <?= $uBadge ?>">
                                        <span><?= $uIcon ?></span>
                                        <span><?= e($rowUnit) ?></span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <a href="<?= url("kelola-perangkat-pembelajaran/kaldik/detail/{$row['id']}") ?>" class="font-bold text-slate-800 hover:text-emerald-700 text-xs sm:text-sm transition-colors">
                                            <?= e($row['judul']) ?>
                                        </a>
                                        <?php if ($isActive): ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                Acuan Unit
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Dibuat: <?= date('d M Y', strtotime($row['created_at'])) ?></div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800"><?= e($row['nama_tahun'] ?? 'TA Aktif') ?></div>
                                    <div class="text-[10px] text-slate-400">Semester <?= e($row['semester'] ?? 'Ganjil & Genap') ?></div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/kaldik/toggle-active/{$row['id']}") ?>" class="inline">
                                        <?= CSRF::field() ?>
                                        <button type="submit" title="Klik untuk mengubah status aktif" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold transition-all border <?= $isActive ? 'bg-emerald-100 text-emerald-800 border-emerald-300 shadow-sm hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-300 hover:bg-slate-200' ?>">
                                            <span class="w-2 h-2 rounded-full <?= $isActive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' ?>"></span>
                                            <?= $isActive ? '🟢 AKTIF' : '⚪ NON-AKTIF' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <a href="<?= url("kelola-perangkat-pembelajaran/kaldik/detail/{$row['id']}") ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-bold text-xs transition-colors">
                                        <span>📅</span>
                                        <span><?= $totalAgendas ?> Kegiatan</span>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-700"><?= e($row['guru_nama']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= !empty($row['guru_nip']) ? 'NIP: ' . e($row['guru_nip']) : 'Staff/Admin' ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?= url("kelola-perangkat-pembelajaran/kaldik/detail/{$row['id']}") ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs transition-colors" title="Buka & Kelola Agenda Kaldik">
                                            <span>Kelola Agenda</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                        </a>
                                        <a href="<?= url("kelola-perangkat-pembelajaran/kaldik/edit/{$row['id']}") ?>" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" title="Edit Informasi Grup">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </a>
                                        <a href="<?= url("kelola-perangkat-pembelajaran/kaldik/cetak/{$row['id']}") ?>" target="_blank" class="p-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 transition-colors" title="Cetak Dokumen Kalender">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                                        </a>
                                        <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/delete/{$row['id']}") ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus grup Kaldik ini? Seluruh data agenda di dalamnya juga akan terhapus.');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors" title="Hapus Dokumen">
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

        <?php if ($totalPages > 1): ?>
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">Halaman <?= $page ?> dari <?= $totalPages ?> (Total: <?= $total ?>)</span>
                <div class="flex gap-1">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="<?= url('kelola-perangkat-pembelajaran/kaldik?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" class="px-3 py-1 rounded-lg text-xs font-semibold <?= $p === $page ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
