<?php
/**
 * Rekapitulasi Nilai Siswa — Komprehensif
 * Mendukung Rekap Per Kelas (Leger), Detail Per Mapel, dan Kartu Nilai Per Siswa
 */

$printUrl = url('kelola-nilai/rekap/cetak?' . http_build_query([
    'group_id' => $groupId,
    'kelas' => $selectedKelas,
    'mapel' => $selectedMapel,
    'mode' => $viewMode,
    'siswa_id' => $selectedSiswaId
]));

$exportUrl = url('kelola-nilai/rekap/export?' . http_build_query([
    'group_id' => $groupId,
    'kelas' => $selectedKelas,
    'mapel' => $selectedMapel
]));
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Laporan Nilai Siswa
                </span>
                <?php if ($activeGroup): ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <?= e($activeGroup['nama_tahun'] ?? '2026/2027') ?> &bull; Semester <?= e($activeGroup['semester'] ?? 'Ganjil') ?>
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2">
                <span>Rekapitulasi Nilai: <?= !empty($selectedKelas) ? 'Kelas ' . e($selectedKelas) : 'Pilih Kelas' ?></span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Rekap capaian nilai asesmen formatif, sumatif lingkup materi, dan sumatif akhir per siswa
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= url('kelola-nilai') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="<?= $exportUrl ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Ekspor Excel</span>
            </a>
            <a href="<?= $printUrl ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md shadow-slate-800/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Rekap</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <form method="GET" action="<?= url('kelola-nilai/rekap') ?>" id="formFilterRekap" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <input type="hidden" name="mode" id="filterMode" value="<?= e($viewMode) ?>">

            <!-- 1. Wadah Nilai -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Wadah Grup Nilai</label>
                <select name="group_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <?php if (empty($groups)): ?>
                        <option value="">(Belum ada wadah)</option>
                    <?php else: ?>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ((int)$groupId === (int)$g['id']) ? 'selected' : '' ?>>
                                [<?= e($g['unit']) ?>] <?= e($g['judul']) ?> (<?= e($g['nama_tahun']) ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 2. Pilihan Kelas -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Pilih Kelas</label>
                <select name="kelas" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <?php if (empty($kelasList)): ?>
                        <option value="">(Tidak ada data kelas)</option>
                    <?php else: ?>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= e($k) ?>" <?= ($selectedKelas === $k) ? 'selected' : '' ?>>
                                Kelas <?= e($k) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- 3. Pilihan Mata Pelajaran -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Mata Pelajaran</label>
                <select name="mapel" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="" <?= empty($selectedMapel) ? 'selected' : '' ?>>Semua Mapel (Leger Rapor)</option>
                    <?php foreach ($mapelList as $m): ?>
                        <option value="<?= e($m) ?>" <?= ($selectedMapel === $m) ? 'selected' : '' ?>>
                            <?= e($m) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 4. Tombol Terapkan & Reset -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Tampilkan</span>
                </button>
                <a href="<?= url('kelola-nilai/rekap') ?>" title="Reset Filter" class="p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </a>
            </div>
        </form>

        <!-- View Mode Navigation Tabs -->
        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 flex-wrap">
            <span class="text-xs font-bold text-slate-400 mr-2 uppercase">Mode Rekap:</span>
            
            <a href="javascript:void(0)" onclick="switchMode('leger')" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= ($viewMode === 'leger' || empty($viewMode)) ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                📋 Rekap Per Kelas (Leger Nilai)
            </a>
            
            <a href="javascript:void(0)" onclick="switchMode('mapel')" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $viewMode === 'mapel' ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                📖 Detail Asesmen per Mapel
            </a>

            <a href="javascript:void(0)" onclick="switchMode('siswa')" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $viewMode === 'siswa' ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                👤 Kartu Nilai Per Siswa
            </a>
        </div>
    </div>

    <!-- Quick Stats Summary for Selected Class -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase">Total Siswa di Kelas</p>
            <h4 class="text-xl font-black text-slate-800 mt-0.5"><?= $classStats['total_siswa'] ?> Siswa</h4>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase">Siswa Terisi Nilai</p>
            <h4 class="text-xl font-black text-teal-600 mt-0.5"><?= $classStats['total_terisi'] ?> Siswa</h4>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase">Rata-Rata Kelas</p>
            <h4 class="text-xl font-black text-slate-800 mt-0.5"><?= $classStats['avg'] > 0 ? $classStats['avg'] : '-' ?></h4>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase">Tertinggi / Terendah</p>
            <h4 class="text-xl font-black text-emerald-600 mt-0.5">
                <?= $classStats['max'] > 0 ? $classStats['max'] : '-' ?> <span class="text-xs text-slate-400 font-semibold">/ <?= $classStats['min'] > 0 ? $classStats['min'] : '-' ?></span>
            </h4>
        </div>
    </div>

    <!-- Mode 1: Rekap Per Kelas (Leger Nilai) -->
    <?php if ($viewMode === 'leger' || empty($viewMode)): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden space-y-4 p-5 sm:p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>📋</span>
                        <span>Leger Nilai Akhir Siswa — Kelas <?= e($selectedKelas) ?></span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Matriks nilai akhir seluruh mata pelajaran per siswa dalam 1 kelas</p>
                </div>
                <span class="text-[11px] font-bold px-3 py-1 rounded-xl bg-teal-50 text-teal-700 border border-teal-200">
                    <?= count($rekapSiswa) ?> Siswa Terdaftar
                </span>
            </div>

            <?php if (empty($rekapSiswa)): ?>
                <div class="py-16 text-center text-slate-400">
                    <p class="text-xs font-semibold">Tidak ada siswa ditemukan di kelas ini.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50/80 text-slate-700 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center border-r border-slate-200">No</th>
                                <th class="py-3 px-3 w-20 text-center border-r border-slate-200">NIS</th>
                                <th class="py-3 px-4 min-w-[180px] border-r border-slate-200">Nama Siswa</th>
                                <?php foreach ($mapelList as $m): ?>
                                    <th class="py-3 px-3 text-center border-r border-slate-200 min-w-[110px]" title="<?= e($m) ?>">
                                        <span class="truncate block max-w-[120px] mx-auto"><?= e($m) ?></span>
                                    </th>
                                <?php endforeach; ?>
                                <th class="py-3 px-3 text-center border-r border-slate-200 bg-teal-50/60 text-teal-800 min-w-[90px]">Rerata</th>
                                <th class="py-3 px-3 text-center bg-amber-50/60 text-amber-800 min-w-[80px]">Peringkat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            <?php foreach ($rekapSiswa as $idx => $rs): 
                                $s = $rs['siswa'];
                                $rank = $rs['ranking'];
                                $rankBadge = '';
                                if ($rank === 1) $rankBadge = '🥇 #1';
                                elseif ($rank === 2) $rankBadge = '🥈 #2';
                                elseif ($rank === 3) $rankBadge = '🥉 #3';
                                elseif ($rank !== '-') $rankBadge = '#' . $rank;
                                else $rankBadge = '-';
                            ?>
                                <tr class="hover:bg-teal-50/20 transition-colors">
                                    <td class="py-3 px-3 text-center text-slate-400 font-bold border-r border-slate-200"><?= $idx + 1 ?></td>
                                    <td class="py-3 px-3 text-center text-slate-500 font-mono border-r border-slate-200"><?= e($s['nis'] ?: '-') ?></td>
                                    <td class="py-3 px-4 border-r border-slate-200">
                                        <a href="javascript:void(0)" onclick="viewStudentCard(<?= (int)$s['id'] ?>)" class="font-bold text-slate-800 hover:text-teal-600 transition-colors">
                                            <?= e($s['nama']) ?>
                                        </a>
                                        <div class="text-[10px] text-slate-400">JK: <?= e($s['jenis_kelamin'] ?? '-') ?></div>
                                    </td>
                                    <?php foreach ($mapelList as $m): 
                                        $mInfo = $rs['mapel'][$m] ?? null;
                                        $naVal = $mInfo['na'] ?? null;
                                        $pred = $mInfo['predikat'] ?? '-';
                                    ?>
                                        <td class="py-3 px-3 text-center border-r border-slate-200">
                                            <?php if ($naVal !== null): ?>
                                                <div class="font-bold text-slate-800"><?= number_format($naVal, 1) ?></div>
                                                <span class="inline-block text-[9px] font-black px-1.5 py-0.2 rounded mt-0.5 <?= $pred === 'A' ? 'bg-emerald-100 text-emerald-800' : ($pred === 'B' ? 'bg-sky-100 text-sky-800' : ($pred === 'C' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) ?>">
                                                    <?= $pred ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-300 font-bold">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="py-3 px-3 text-center font-black border-r border-slate-200 bg-teal-50/30 text-teal-800">
                                        <?= $rs['overall_avg'] !== null ? number_format($rs['overall_avg'], 1) : '-' ?>
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold bg-amber-50/30">
                                        <span class="px-2 py-0.5 rounded-lg text-[11px] <?= ($rank === 1 || $rank === 2 || $rank === 3) ? 'bg-amber-100 text-amber-900 font-black' : 'text-slate-600' ?>">
                                            <?= $rankBadge ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <!-- Mode 2: Detail Asesmen per Mapel -->
    <?php elseif ($viewMode === 'mapel'): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden space-y-4 p-5 sm:p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>📖</span>
                        <span>Detail Asesmen: <?= !empty($selectedMapel) ? e($selectedMapel) : 'Semua Mapel' ?> — Kelas <?= e($selectedKelas) ?></span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Rincian nilai formatif TP/ATP, sumatif lingkup materi (LM), dan sumatif akhir (SAS)</p>
                </div>
            </div>

            <?php if (empty($rekapSiswa)): ?>
                <div class="py-16 text-center text-slate-400">
                    <p class="text-xs font-semibold">Tidak ada data siswa ditemukan.</p>
                </div>
            <?php else: ?>
                <?php 
                $targetMapels = !empty($selectedMapel) ? [$selectedMapel] : $mapelList;
                foreach ($targetMapels as $mKey): 
                ?>
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-xl bg-teal-600 text-white font-bold text-xs flex items-center justify-center">📚</span>
                                <h4 class="font-bold text-slate-800 text-xs sm:text-sm"><?= e($mKey) ?></h4>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-2xl border border-slate-200">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-slate-50/80 text-slate-700 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="py-3 px-3 w-10 text-center border-r border-slate-200">No</th>
                                        <th class="py-3 px-3 w-20 text-center border-r border-slate-200">NIS</th>
                                        <th class="py-3 px-4 min-w-[180px] border-r border-slate-200">Nama Siswa</th>
                                        <th class="py-3 px-3 text-center border-r border-slate-200 bg-teal-50/40 text-teal-800 min-w-[120px]">
                                            Rerata Formatif (TP/ATP)
                                        </th>
                                        <th class="py-3 px-3 text-center border-r border-slate-200 bg-sky-50/40 text-sky-800 min-w-[120px]">
                                            Sumatif LM
                                        </th>
                                        <th class="py-3 px-3 text-center border-r border-slate-200 bg-indigo-50/40 text-indigo-800 min-w-[100px]">
                                            Sumatif SAS
                                        </th>
                                        <th class="py-3 px-3 text-center border-r border-slate-200 bg-amber-50/40 text-amber-800 min-w-[100px]">
                                            Nilai Akhir (NA)
                                        </th>
                                        <th class="py-3 px-3 text-center min-w-[80px]">Predikat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                    <?php foreach ($rekapSiswa as $idx => $rs): 
                                        $s = $rs['siswa'];
                                        $mInfo = $rs['mapel'][$mKey] ?? null;
                                        $fmtAvg = $mInfo['formatif_avg'] ?? null;
                                        $lmAvg = $mInfo['lm_avg'] ?? null;
                                        $sasVal = $mInfo['sas'] ?? null;
                                        $naVal = $mInfo['na'] ?? null;
                                        $pred = $mInfo['predikat'] ?? '-';
                                    ?>
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="py-3 px-3 text-center text-slate-400 font-bold border-r border-slate-200"><?= $idx + 1 ?></td>
                                            <td class="py-3 px-3 text-center text-slate-500 font-mono border-r border-slate-200"><?= e($s['nis'] ?: '-') ?></td>
                                            <td class="py-3 px-4 font-bold text-slate-800 border-r border-slate-200"><?= e($s['nama']) ?></td>
                                            <td class="py-3 px-3 text-center border-r border-slate-200 bg-teal-50/20">
                                                <?= $fmtAvg !== null ? '<strong class="text-teal-800">' . number_format($fmtAvg, 1) . '</strong>' : '<span class="text-slate-300">-</span>' ?>
                                            </td>
                                            <td class="py-3 px-3 text-center border-r border-slate-200 bg-sky-50/20">
                                                <?= $lmAvg !== null ? '<strong class="text-sky-800">' . number_format($lmAvg, 1) . '</strong>' : '<span class="text-slate-300">-</span>' ?>
                                            </td>
                                            <td class="py-3 px-3 text-center border-r border-slate-200 bg-indigo-50/20">
                                                <?= $sasVal !== null ? '<strong class="text-indigo-800">' . number_format($sasVal, 1) . '</strong>' : '<span class="text-slate-300">-</span>' ?>
                                            </td>
                                            <td class="py-3 px-3 text-center border-r border-slate-200 bg-amber-50/20">
                                                <?= $naVal !== null ? '<strong class="text-amber-900 text-sm">' . number_format($naVal, 1) . '</strong>' : '<span class="text-slate-300">-</span>' ?>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <?php if ($pred !== '-'): ?>
                                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-black <?= $pred === 'A' ? 'bg-emerald-100 text-emerald-800' : ($pred === 'B' ? 'bg-sky-100 text-sky-800' : ($pred === 'C' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) ?>">
                                                        <?= $pred ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-slate-300">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    <!-- Mode 3: Kartu Nilai Per Siswa -->
    <?php elseif ($viewMode === 'siswa'): ?>
        <?php 
        // Siswa yang dipilih untuk ditampilkan kartu nilainya
        $activeSiswaData = null;
        if ($selectedSiswaId) {
            foreach ($rekapSiswa as $rs) {
                if ((int)$rs['siswa']['id'] === $selectedSiswaId) {
                    $activeSiswaData = $rs;
                    break;
                }
            }
        }
        if (!$activeSiswaData && !empty($rekapSiswa)) {
            $activeSiswaData = $rekapSiswa[0];
            $selectedSiswaId = (int)$activeSiswaData['siswa']['id'];
        }
        ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Student List Selector (4 cols) -->
            <div class="lg:col-span-4 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 pb-2">
                    Daftar Siswa Kelas <?= e($selectedKelas) ?>
                </h3>
                <div class="max-h-[600px] overflow-y-auto space-y-1.5 custom-scrollbar pr-1">
                    <?php foreach ($rekapSiswa as $rs): 
                        $s = $rs['siswa'];
                        $isSel = ((int)$s['id'] === $selectedSiswaId);
                    ?>
                        <a href="<?= url('kelola-nilai/rekap?' . http_build_query([
                            'group_id' => $groupId,
                            'kelas' => $selectedKelas,
                            'mapel' => $selectedMapel,
                            'mode' => 'siswa',
                            'siswa_id' => $s['id']
                        ])) ?>" class="flex items-center justify-between p-2.5 rounded-2xl transition-all <?= $isSel ? 'bg-teal-600 text-white shadow-sm' : 'hover:bg-slate-50 text-slate-700' ?>">
                            <div class="min-w-0">
                                <div class="font-bold text-xs truncate <?= $isSel ? 'text-white' : 'text-slate-800' ?>"><?= e($s['nama']) ?></div>
                                <div class="text-[10px] <?= $isSel ? 'text-teal-100' : 'text-slate-400' ?>">NIS: <?= e($s['nis'] ?: '-') ?></div>
                            </div>
                            <span class="text-xs font-black <?= $isSel ? 'bg-teal-700 text-white' : 'bg-slate-100 text-slate-700' ?> px-2 py-0.5 rounded-lg">
                                <?= $rs['overall_avg'] !== null ? number_format($rs['overall_avg'], 1) : '-' ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Student Report Card (8 cols) -->
            <div class="lg:col-span-8 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-6">
                <?php if (!$activeSiswaData): ?>
                    <div class="py-20 text-center text-slate-400">
                        <p class="text-xs font-semibold">Pilih salah satu siswa di sebelah kiri untuk melihat kartu nilai.</p>
                    </div>
                <?php else: 
                    $as = $activeSiswaData['siswa'];
                ?>
                    <!-- Student Profile Header -->
                    <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-md shadow-teal-500/20">
                                <?= strtoupper(mb_substr($as['nama'], 0, 1)) ?>
                            </div>
                            <div>
                                <h3 class="font-black text-slate-900 text-base"><?= e($as['nama']) ?></h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    NIS: <strong class="text-slate-600"><?= e($as['nis'] ?: '-') ?></strong> &bull;
                                    Kelas: <strong class="text-slate-600"><?= e($selectedKelas) ?></strong> &bull;
                                    JK: <strong class="text-slate-600"><?= e($as['jenis_kelamin'] ?? '-') ?></strong>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Rata-Rata Capaian</span>
                            <div class="text-2xl font-black text-teal-600">
                                <?= $activeSiswaData['overall_avg'] !== null ? number_format($activeSiswaData['overall_avg'], 1) : '-' ?>
                            </div>
                            <span class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                Peringkat #<?= $activeSiswaData['ranking'] ?>
                            </span>
                        </div>
                    </div>

                    <!-- Subject Grades Table -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50/80 text-slate-700 font-bold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3 w-10 text-center border-r border-slate-200">No</th>
                                    <th class="py-3 px-4 border-r border-slate-200">Mata Pelajaran</th>
                                    <th class="py-3 px-3 text-center border-r border-slate-200">Formatif</th>
                                    <th class="py-3 px-3 text-center border-r border-slate-200">Sumatif LM</th>
                                    <th class="py-3 px-3 text-center border-r border-slate-200">SAS</th>
                                    <th class="py-3 px-3 text-center border-r border-slate-200 bg-teal-50/40 text-teal-800">Nilai Akhir</th>
                                    <th class="py-3 px-3 text-center">Predikat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php $sNo = 1; foreach ($mapelList as $m): 
                                    $mInfo = $activeSiswaData['mapel'][$m] ?? null;
                                    $fmt = $mInfo['formatif_avg'] ?? null;
                                    $lm = $mInfo['lm_avg'] ?? null;
                                    $sas = $mInfo['sas'] ?? null;
                                    $na = $mInfo['na'] ?? null;
                                    $prd = $mInfo['predikat'] ?? '-';
                                ?>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-3 px-3 text-center text-slate-400 font-bold border-r border-slate-200"><?= $sNo++ ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-800 border-r border-slate-200">
                                            <?= e($m) ?>
                                            <?php if (!empty($mInfo['guru_nama'])): ?>
                                                <span class="block text-[10px] text-slate-400 font-normal">Guru: <?= e($mInfo['guru_nama']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center border-r border-slate-200">
                                            <?= $fmt !== null ? number_format($fmt, 1) : '-' ?>
                                        </td>
                                        <td class="py-3 px-3 text-center border-r border-slate-200">
                                            <?= $lm !== null ? number_format($lm, 1) : '-' ?>
                                        </td>
                                        <td class="py-3 px-3 text-center border-r border-slate-200">
                                            <?= $sas !== null ? number_format($sas, 1) : '-' ?>
                                        </td>
                                        <td class="py-3 px-3 text-center font-black border-r border-slate-200 bg-teal-50/20 text-teal-800">
                                            <?= $na !== null ? number_format($na, 1) : '-' ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <?php if ($prd !== '-'): ?>
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black <?= $prd === 'A' ? 'bg-emerald-100 text-emerald-800' : ($prd === 'B' ? 'bg-sky-100 text-sky-800' : ($prd === 'C' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) ?>">
                                                    <?= $prd ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-300">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function switchMode(mode) {
    const input = document.getElementById('filterMode');
    if (input) {
        input.value = mode;
        document.getElementById('formFilterRekap').submit();
    }
}

function viewStudentCard(siswaId) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('mode', 'siswa');
    urlParams.set('siswa_id', siswaId);
    window.location.search = urlParams.toString();
}
</script>
