<?php
/**
 * Matriks Grid Jadwal Pelajaran - View
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                    ← Kembali ke Daftar Grup
                </a>
            </div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3 mt-1">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-primary-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                </div>
                <span>Matriks Jadwal Pelajaran</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Grup: <strong class="text-slate-800"><?= e($grup['nama_grup']) ?></strong> (<?= e($grup['tahun_ajaran']) ?> - Semester <?= e($grup['semester']) ?>)
            </p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($mode === 'kelas' && !empty($filterKelas)): ?>
                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/cetak-kelas/' . $grup['id'] . '?kelas=' . urlencode($filterKelas)) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/30 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                    <span>Cetak Jadwal Kelas</span>
                </a>
            <?php elseif ($mode === 'guru' && !empty($filterGuru)): ?>
                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/cetak-guru/' . $grup['id'] . '?guru_id=' . $filterGuru) ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/30 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                    <span>Cetak Jadwal Guru</span>
                </a>
            <?php endif; ?>

            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/export/' . $grup['id']) ?>" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                <span>Export CSV</span>
            </a>
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/generate/' . $grup['id']) ?>" class="inline-flex items-center gap-2 px-3.5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                <span>Generator Ulang</span>
            </a>
        </div>
    </div>

    <!-- Filter & View Switcher Bar -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Mode Switcher Tabs -->
        <div class="flex items-center p-1 bg-slate-100 rounded-2xl w-full md:w-auto">
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/matriks/' . $grup['id'] . '?mode=kelas&kelas=' . urlencode($filterKelas)) ?>" class="flex-1 md:flex-none px-5 py-2 rounded-xl text-xs font-bold transition-all <?= $mode === 'kelas' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">
                🏫 Tampilan Per Kelas
            </a>
            <a href="<?= url('kelola-perangkat-pembelajaran/jadwal/matriks/' . $grup['id'] . '?mode=guru&guru_id=' . $filterGuru) ?>" class="flex-1 md:flex-none px-5 py-2 rounded-xl text-xs font-bold transition-all <?= $mode === 'guru' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">
                👨‍🏫 Tampilan Per Guru
            </a>
        </div>

        <!-- Filter Selection Dropdown -->
        <div class="w-full md:w-auto flex items-center gap-3">
            <?php if ($mode === 'kelas'): ?>
                <label class="text-xs font-bold text-slate-600 flex-shrink-0">Pilih Rombel / Kelas:</label>
                <select onchange="window.location.href='<?= url('kelola-perangkat-pembelajaran/jadwal/matriks/' . $grup['id'] . '?mode=kelas&kelas=') ?>' + encodeURIComponent(this.value)" class="w-full md:w-64 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= e($k['nama_kelas']) ?>" <?= $filterKelas === $k['nama_kelas'] ? 'selected' : '' ?>>
                            <?= e($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <label class="text-xs font-bold text-slate-600 flex-shrink-0">Pilih Guru Pengampu:</label>
                <select onchange="window.location.href='<?= url('kelola-perangkat-pembelajaran/jadwal/matriks/' . $grup['id'] . '?mode=guru&guru_id=') ?>' + this.value" class="w-full md:w-64 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                    <?php foreach ($guruList as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $filterGuru == $g['id'] ? 'selected' : '' ?>>
                            <?= e($g['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
    </div>

    <!-- Weekly Schedule Grid Matrix -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">
                    <?= $mode === 'kelas' ? 'Jadwal Kelas: ' . e($filterKelas ?: 'Pilih Kelas') : 'Jadwal Mengajar: ' . e($guruList[array_search($filterGuru, array_column($guruList, 'id'))]['nama'] ?? 'Pilih Guru') ?>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Matriks pembelajaran mingguan (Senin s.d. Sabtu).</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 font-semibold text-slate-600">
                    <span class="w-3 h-3 rounded-md bg-indigo-50 border border-indigo-200 inline-block"></span> KBM
                </span>
                <span class="inline-flex items-center gap-1.5 font-semibold text-slate-600">
                    <span class="w-3 h-3 rounded-md bg-amber-100 border border-amber-300 inline-block"></span> Istirahat / Sholat
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-center text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 font-extrabold uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-slate-500 w-24 border-r border-slate-200">Jam Ke</th>
                        <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day): ?>
                            <th class="px-4 py-3.5 border-r border-slate-200 last:border-r-0 font-extrabold text-slate-900"><?= $day ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($maxJp == 0): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-slate-400">
                            Belum ada jadwal yang ter-generate. Silakan buka tab "Generator Ulang" untuk men-generate jadwal otomatis.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php for ($jk = 1; $jk <= $maxJp; $jk++): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <!-- Jam Ke Header -->
                        <td class="px-4 py-4 font-black text-indigo-700 bg-slate-50/60 border-r border-slate-200">
                            <span class="text-xs">JP <?= $jk ?></span>
                        </td>

                        <!-- Loop Hari -->
                        <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day): ?>
                            <?php 
                            $item = $scheduleItems[$day][$jk] ?? null;
                            ?>
                            <td class="px-3 py-3 border-r border-slate-200 last:border-r-0 align-top min-w-[150px] max-w-[200px]">
                                <?php if ($item): ?>
                                    <div class="p-3 rounded-2xl bg-gradient-to-br from-indigo-50/90 to-blue-50/90 border border-indigo-200/80 shadow-sm space-y-1 text-left group hover:border-indigo-400 hover:shadow-md transition-all">
                                        <p class="font-extrabold text-indigo-950 text-xs tracking-tight line-clamp-2" title="<?= e($item['mata_pelajaran']) ?>">
                                            <?= e($item['mata_pelajaran']) ?>
                                        </p>
                                        <div class="text-[11px] text-slate-600 flex items-center justify-between pt-1 border-t border-indigo-100/60">
                                            <span class="font-semibold truncate" title="<?= $mode === 'kelas' ? e($item['nama_guru']) : e($item['nama_kelas']) ?>">
                                                <?= $mode === 'kelas' ? '👨‍🏫 ' . e($item['nama_guru']) : '🏫 ' . e($item['nama_kelas']) ?>
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-indigo-600 font-mono font-bold">
                                            <?= substr($item['jam_mulai'], 0, 5) ?> - <?= substr($item['jam_selesai'], 0, 5) ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="h-16 flex items-center justify-center text-slate-300 text-xs font-semibold rounded-2xl border border-dashed border-slate-200">
                                        -
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endfor; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
