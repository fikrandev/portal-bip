<?php
/**
 * Detail Wadah Grup Program Semester (Prosem)
 * Menampilkan Daftar Dokumen Prosem per Kelas & Aksi Cetak Landscape
 */
?>
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-purple-50 text-purple-700 border border-purple-200">
                    Wadah Dokumen Program Semester
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">
                <?= e($group['judul'] ?: 'Detail Wadah Prosem') ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kumpulan matriks distribusi KBM semester untuk Unit <strong><?= e($group['unit']) ?></strong>
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= url('kelola-perangkat-pembelajaran/prosem') ?>" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl text-xs transition-colors">
                &larr; Kembali
            </a>

            <!-- Tombol Sinkronisasi dari CP & ATP -->
            <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/prosem/group/{$group['id']}/sync") ?>" onsubmit="return confirm('Perbarui data matriks Prosem dari dokumen CP & ATP terbaru?');" class="inline">
                <?= CSRF::field() ?>
                <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs border border-purple-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                    <span>Sinkronkan dari CP & ATP</span>
                </button>
            </form>

            <a href="<?= url('kelola-perangkat-pembelajaran/prosem/create/' . $group['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md shadow-purple-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>+ Tambah Prosem</span>
            </a>
        </div>
    </div>

    <!-- Info Grup Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase mb-1">Judul Wadah</p>
                <p class="font-bold text-slate-800 text-sm"><?= e($group['judul'] ?: 'Kumpulan Prosem') ?></p>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase mb-1">Unit Sekolah</p>
                <p class="font-bold text-slate-800 text-sm">Unit <?= e($group['unit']) ?></p>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase mb-1">Tahun Ajaran & Semester</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                        <?php 
                        $taName = '';
                        foreach($ta_list ?? [] as $ta) {
                            if ($ta['id'] == $group['tahun_akademik_id']) {
                                $taName = $ta['nama_tahun']; break;
                            }
                        }
                        echo e($taName ?: '2026/2027');
                        ?>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-purple-50 text-purple-700">Smt <?= e($group['semester']) ?></span>
                </div>
            </div>
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase mb-1">Sumber CP & ATP</p>
                <?php if (!empty($cpatp_group)): ?>
                    <a href="<?= url("kelola-perangkat-pembelajaran/cpatp/group/{$cpatp_group['id']}") ?>" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1 mt-1" title="Buka Grup CP ATP Sumber">
                        <span>🔗 <?= e($cpatp_group['judul']) ?></span>
                    </a>
                <?php else: ?>
                    <span class="text-xs font-semibold text-slate-400">Manual / Belum ditautkan</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                    Daftar Dokumen Program Semester per Kelas (Total: <?= count($items) ?> Dokumen)
                </h2>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">Tersusun otomatis dari data CP & ATP</p>
            </div>

            <!-- Tombol Cetak Semua Dokumen dalam Grup -->
            <?php if (!empty($items)): ?>
                <a href="<?= url("kelola-perangkat-pembelajaran/prosem/group/{$group['id']}/cetak-semua") ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all self-start sm:self-auto shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                    <span>Cetak Semua Prosem</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Guru Pengampu</th>
                        <th class="py-3.5 px-4">Mata Pelajaran</th>
                        <th class="py-3.5 px-4">Tingkat / Kelas & Fase</th>
                        <th class="py-3.5 px-4 text-center">Alokasi Total JP</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-purple-50 flex items-center justify-center mx-auto mb-2 text-purple-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                </div>
                                Belum ada dokumen Prosem di dalam wadah ini.<br>
                                <span class="text-xs text-purple-600 font-semibold">Klik "Sinkronkan dari CP & ATP" di atas untuk generate otomatis.</span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $row): ?>
                            <tr class="hover:bg-purple-50/20 transition-colors">
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    <?= ($page - 1) * 20 + ($idx + 1) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900"><?= e($row['guru_nama'] ?: '-') ?></div>
                                    <?php if (!empty($row['guru_nip'])): ?>
                                        <div class="text-[10px] text-slate-400">NIP. <?= e($row['guru_nip']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-purple-900">
                                    <?= e($row['mata_pelajaran']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800"><?= e($row['tingkat_kelas']) ?></div>
                                    <?php if (!empty($row['fase'])): ?>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                            <?= e($row['fase']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-purple-100 text-purple-800">
                                        <?= e($row['alokasi_waktu'] ?: '0 JP') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php
                                        $badge = match($row['status']) {
                                            'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'diajukan'  => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'ditolak'   => 'bg-rose-100 text-rose-800 border-rose-200',
                                            default     => 'bg-slate-100 text-slate-700 border-slate-200'
                                        };
                                        $label = match($row['status']) {
                                            'disetujui' => 'Disetujui',
                                            'diajukan'  => 'Menunggu Verifikasi',
                                            'ditolak'   => 'Perlu Revisi',
                                            default     => 'Draft'
                                        };
                                    ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $badge ?>">
                                        <?= $label ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Cetak Per Kelas (Landscape) -->
                                        <a href="<?= url("kelola-perangkat-pembelajaran/prosem/cetak/{$row['id']}") ?>" target="_blank" title="Cetak Prosem Kelas Ini (Landscape)" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                                            <span>Cetak</span>
                                        </a>

                                        <!-- Edit Matriks -->
                                        <a href="<?= url("kelola-perangkat-pembelajaran/prosem/edit/{$row['id']}") ?>" title="Edit Matriks Distribusi Pekan" class="p-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </a>

                                        <!-- Detail -->
                                        <a href="<?= url("kelola-perangkat-pembelajaran/prosem/detail/{$row['id']}") ?>" title="Detail" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>

                                        <!-- Hapus Dokumen -->
                                        <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/prosem/delete/{$row['id']}") ?>" onsubmit="return confirm('Hapus dokumen Prosem ini?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" title="Hapus" class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors">
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
    </div>
</div>
