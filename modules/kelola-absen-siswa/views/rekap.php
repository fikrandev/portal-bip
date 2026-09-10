<?php
/**
 * Rekapitulasi Absen Siswa — Komprehensif
 * Mendukung:
 * 1. Mode Rekap Kelas (Matriks Harian Bulanan)
 * 2. Mode Rekap Mapel (Matriks Pertemuan)
 * 3. Mode Kartu Siswa (Riwayat Presensi Individual)
 */

$printUrl = url('kelola-absen-siswa/rekap/cetak?' . http_build_query([
    'mode' => $mode,
    'kelas' => $cleanKelas,
    'mapel' => $selectedMapel,
    'bulan' => $selectedBulan,
    'siswa_id' => $selectedSiswaId ?? 0
]));

$exportUrl = url('kelola-absen-siswa/rekap/export?' . http_build_query([
    'mode' => $mode,
    'kelas' => $cleanKelas,
    'mapel' => $selectedMapel,
    'bulan' => $selectedBulan
]));
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Laporan Presensi Siswa
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                    Kelas <?= htmlspecialchars($cleanKelas) ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2">
                <span>Rekapitulasi Presensi: Kelas <?= htmlspecialchars($cleanKelas) ?></span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Laporan kehadiran kumulatif, matriks presensi harian rombel, dan rekapitulasi mata pelajaran
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= url('kelola-absen-siswa') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                <span>Dashboard</span>
            </a>
            <?php if ($mode !== 'siswa'): ?>
                <a href="<?= $exportUrl ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Ekspor CSV</span>
                </a>
            <?php endif; ?>
            <a href="<?= $printUrl ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md shadow-slate-800/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak A4</span>
            </a>
        </div>
    </div>

    <!-- Mode Tabs & Filter Bar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm space-y-4">
        <!-- Mode Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3 flex-wrap">
            <a href="<?= url('kelola-absen-siswa/rekap?mode=kelas&kelas=' . urlencode($cleanKelas) . '&bulan=' . $selectedBulan) ?>" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 <?= $mode === 'kelas' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span>🏫</span>
                <span>Rekap Absen Kelas (Harian)</span>
            </a>
            <a href="<?= url('kelola-absen-siswa/rekap?mode=mapel&kelas=' . urlencode($cleanKelas) . '&mapel=' . urlencode($selectedMapel)) ?>" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 <?= $mode === 'mapel' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span>📚</span>
                <span>Rekap Absen Mapel (Pertemuan)</span>
            </a>
            <a href="<?= url('kelola-absen-siswa/rekap?mode=siswa&kelas=' . urlencode($cleanKelas) . (!empty($selectedSiswaId) ? '&siswa_id=' . $selectedSiswaId : '')) ?>" 
               class="px-4 py-2 rounded-2xl text-xs font-bold transition-all flex items-center gap-2 <?= $mode === 'siswa' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span>👤</span>
                <span>Kartu Presensi Siswa</span>
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="<?= url('kelola-absen-siswa/rekap') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

            <!-- Pilih Kelas -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Pilih Kelas</label>
                <select name="kelas" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= htmlspecialchars($k['kelas']) ?>" <?= $cleanKelas === preg_replace('/^Kelas\s+/i', '', $k['kelas']) ? 'selected' : '' ?>>
                            Kelas <?= htmlspecialchars($k['kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Mode Spesifik: Bulan atau Mapel atau Siswa -->
            <?php if ($mode === 'kelas'): ?>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Bulan Presensi</label>
                    <input type="month" name="bulan" value="<?= $selectedBulan ?>" onchange="this.form.submit()" class="w-full px-3.5 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
            <?php elseif ($mode === 'mapel'): ?>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Pilih Mata Pelajaran</label>
                    <select name="mapel" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <?php foreach ($mapelList as $mp): ?>
                            <option value="<?= htmlspecialchars($mp['mata_pelajaran']) ?>" <?= $selectedMapel === $mp['mata_pelajaran'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mp['mata_pelajaran']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php elseif ($mode === 'siswa'): ?>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Pilih Siswa</label>
                    <select name="siswa_id" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <?php foreach ($siswaList as $sw): ?>
                            <option value="<?= $sw['id'] ?>" <?= ($selectedSiswaId == $sw['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sw['nama']) ?> (<?= htmlspecialchars($sw['nis'] ?: '-') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div>
                <button type="submit" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Mode 1: Matriks Rekap Kelas (Harian) -->
    <?php if ($mode === 'kelas'): ?>
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Matriks Presensi Harian Kelas <?= htmlspecialchars($cleanKelas) ?></h3>
                <p class="text-xs text-slate-400">Bulan: <?= date('F Y', strtotime($selectedBulan . '-01')) ?> &bull; Total <?= count($sessions) ?> hari aktif tercatat</p>
            </div>
            <div class="flex items-center gap-2 text-[11px] font-bold">
                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800">H: Hadir</span>
                <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800">S: Sakit</span>
                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800">I: Izin</span>
                <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800">A: Alpa</span>
                <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800">T: Terlambat</span>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/90 text-slate-600 font-bold border-b border-slate-200/80 sticky top-0">
                    <tr>
                        <th class="py-3 px-3 w-10 text-center">No</th>
                        <th class="py-3 px-3 w-24">NIS</th>
                        <th class="py-3 px-3 min-w-[180px]">Nama Siswa</th>
                        <th class="py-3 px-2 w-10 text-center">L/P</th>
                        <?php foreach ($sessions as $ses): ?>
                            <th class="py-2 px-2 text-center font-mono border-l border-slate-200/60 min-w-[36px]" title="<?= date('d M Y', strtotime($ses['tanggal'])) ?>">
                                <div class="text-[10px] text-slate-400"><?= date('D', strtotime($ses['tanggal'])) ?></div>
                                <div><?= date('d', strtotime($ses['tanggal'])) ?></div>
                            </th>
                        <?php endforeach; ?>
                        <th class="py-3 px-2 text-center bg-emerald-50/80 text-emerald-800 border-l border-slate-200 font-bold w-12">H</th>
                        <th class="py-3 px-2 text-center bg-blue-50/80 text-blue-800 w-12 font-bold">S</th>
                        <th class="py-3 px-2 text-center bg-amber-50/80 text-amber-800 w-12 font-bold">I</th>
                        <th class="py-3 px-2 text-center bg-rose-50/80 text-rose-800 w-12 font-bold">A</th>
                        <th class="py-3 px-2 text-center bg-purple-50/80 text-purple-800 w-12 font-bold">T</th>
                        <th class="py-3 px-3 text-center bg-slate-100 font-black text-slate-800 min-w-[65px]">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($rekapData)): ?>
                        <tr>
                            <td colspan="<?= count($sessions) + 10 ?>" class="py-12 text-center text-slate-400">
                                Tidak ada data siswa atau presensi untuk kelas dan bulan terpilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekapData as $idx => $row): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-bold"><?= $idx + 1 ?></td>
                                <td class="py-2.5 px-3 font-mono text-slate-500"><?= htmlspecialchars($row['siswa']['nis'] ?: '-') ?></td>
                                <td class="py-2.5 px-3 font-bold text-slate-800 whitespace-nowrap"><?= htmlspecialchars($row['siswa']['nama']) ?></td>
                                <td class="py-2.5 px-2 text-center font-bold text-slate-500"><?= htmlspecialchars($row['siswa']['jenis_kelamin'] ?: '-') ?></td>
                                <?php foreach ($sessions as $ses): 
                                    $st = $row['daily'][$ses['id']] ?? '-';
                                    $bgClass = 'text-slate-300';
                                    if ($st === 'H') $bgClass = 'bg-emerald-50 font-bold text-emerald-700';
                                    elseif ($st === 'S') $bgClass = 'bg-blue-50 font-bold text-blue-700';
                                    elseif ($st === 'I') $bgClass = 'bg-amber-50 font-bold text-amber-700';
                                    elseif ($st === 'A') $bgClass = 'bg-rose-50 font-black text-rose-700';
                                    elseif ($st === 'T') $bgClass = 'bg-purple-50 font-bold text-purple-700';
                                ?>
                                    <td class="py-2 px-1 text-center font-mono border-l border-slate-100 <?= $bgClass ?>">
                                        <?= $st ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="py-2 px-2 text-center font-bold bg-emerald-50/40 text-emerald-800 border-l border-slate-200"><?= $row['h'] ?></td>
                                <td class="py-2 px-2 text-center font-bold bg-blue-50/40 text-blue-800"><?= $row['s'] ?></td>
                                <td class="py-2 px-2 text-center font-bold bg-amber-50/40 text-amber-800"><?= $row['i'] ?></td>
                                <td class="py-2 px-2 text-center font-bold bg-rose-50/40 text-rose-800"><?= $row['a'] ?></td>
                                <td class="py-2 px-2 text-center font-bold bg-purple-50/40 text-purple-800"><?= $row['t'] ?></td>
                                <td class="py-2 px-3 text-center bg-slate-50 font-black text-slate-800">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] <?= $row['persen'] >= 85 ? 'bg-emerald-100 text-emerald-800' : ($row['persen'] >= 70 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                        <?= $row['persen'] ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Mode 2: Matriks Rekap Mapel (Pertemuan) -->
    <?php if ($mode === 'mapel'): ?>
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Matriks Presensi Mapel: <?= htmlspecialchars($selectedMapel) ?> (Kelas <?= htmlspecialchars($cleanKelas) ?>)</h3>
                <p class="text-xs text-slate-400">Total <?= count($sessions) ?> pertemuan telah terlaksana</p>
            </div>
            <div class="flex items-center gap-2 text-[11px] font-bold">
                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800">H: Hadir</span>
                <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800">S: Sakit</span>
                <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800">I: Izin</span>
                <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800">A: Alpa</span>
                <span class="px-2 py-0.5 rounded-md bg-purple-100 text-purple-800">T: Terlambat</span>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/90 text-slate-600 font-bold border-b border-slate-200/80 sticky top-0">
                    <tr>
                        <th class="py-3 px-3 w-10 text-center">No</th>
                        <th class="py-3 px-3 w-24">NIS</th>
                        <th class="py-3 px-3 min-w-[180px]">Nama Siswa</th>
                        <th class="py-3 px-2 w-10 text-center">L/P</th>
                        <?php foreach ($sessions as $ses): ?>
                            <th class="py-2 px-2 text-center font-mono border-l border-slate-200/60 min-w-[42px]" title="Pertemuan <?= $ses['pertemuan_ke'] ?> (<?= date('d M Y', strtotime($ses['tanggal'])) ?>): <?= htmlspecialchars($ses['materi'] ?? '') ?>">
                                <div class="text-[10px] text-slate-400">P<?= $ses['pertemuan_ke'] ?></div>
                                <div class="text-[9px] text-slate-500"><?= date('d/m', strtotime($ses['tanggal'])) ?></div>
                            </th>
                        <?php endforeach; ?>
                        <th class="py-3 px-2 text-center bg-emerald-50/80 text-emerald-800 border-l border-slate-200 font-bold w-12">H</th>
                        <th class="py-3 px-2 text-center bg-blue-50/80 text-blue-800 w-12 font-bold">S</th>
                        <th class="py-3 px-2 text-center bg-amber-50/80 text-amber-800 w-12 font-bold">I</th>
                        <th class="py-3 px-2 text-center bg-rose-50/80 text-rose-800 w-12 font-bold">A</th>
                        <th class="py-3 px-2 text-center bg-purple-50/80 text-purple-800 w-12 font-bold">T</th>
                        <th class="py-3 px-3 text-center bg-slate-100 font-black text-slate-800 min-w-[65px]">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($rekapData)): ?>
                        <tr>
                            <td colspan="<?= count($sessions) + 10 ?>" class="py-12 text-center text-slate-400">
                                Tidak ada data siswa atau presensi untuk mata pelajaran dan kelas terpilih.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekapData as $idx => $row): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-2.5 px-3 text-center text-slate-400 font-bold"><?= $idx + 1 ?></td>
                                <td class="py-2.5 px-3 font-mono text-slate-500"><?= htmlspecialchars($row['siswa']['nis'] ?: '-') ?></td>
                                <td class="py-2.5 px-3 font-bold text-slate-800 whitespace-nowrap"><?= htmlspecialchars($row['siswa']['nama']) ?></td>
                                <td class="py-2.5 px-2 text-center font-bold text-slate-500"><?= htmlspecialchars($row['siswa']['jenis_kelamin'] ?: '-') ?></td>
                                <?php foreach ($sessions as $ses): 
                                    $st = $row['pertemuan'][$ses['id']] ?? '-';
                                    $bgClass = 'text-slate-300';
                                    if ($st === 'H') $bgClass = 'bg-emerald-50 font-bold text-emerald-700';
                                    elseif ($st === 'S') $bgClass = 'bg-blue-50 font-bold text-blue-700';
                                    elseif ($st === 'I') $bgClass = 'bg-amber-50 font-bold text-amber-700';
                                    elseif ($st === 'A') $bgClass = 'bg-rose-50 font-black text-rose-700';
                                    elseif ($st === 'T') $bgClass = 'bg-purple-50 font-bold text-purple-700';
                                ?>
                                    <td class="py-2 px-1 text-center font-mono border-l border-slate-100 <?= $bgClass ?>">
                                        <?= $st ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="py-2 px-2 text-center font-bold bg-emerald-50/40 text-emerald-800 border-l border-slate-200"><?= $row['h'] ?></td>
                                <td class="py-2 px-2 text-center font-bold bg-blue-50/40 text-blue-800"><?= $row['s'] ?></td>
                                <td class="py-2 px-2 text-center font-bold bg-amber-50/40 text-amber-800"><?= $row['i'] ?></td>
                                <td class="py-2 px-2 text-center font-bold bg-rose-50/40 text-rose-800"><?= $row['a'] ?></td>
                                <td class="py-2 px-2 text-center font-bold bg-purple-50/40 text-purple-800"><?= $row['t'] ?></td>
                                <td class="py-2 px-3 text-center bg-slate-50 font-black text-slate-800">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] <?= $row['persen'] >= 85 ? 'bg-emerald-100 text-emerald-800' : ($row['persen'] >= 70 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                        <?= $row['persen'] ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Mode 3: Kartu Presensi Siswa Individual -->
    <?php if ($mode === 'siswa' && !empty($targetSiswa)): 
        $totSesi = count($studentHistory);
        $totH = 0; $totS = 0; $totI = 0; $totA = 0; $totT = 0;
        foreach ($studentHistory as $sh) {
            if ($sh['status'] === 'H') $totH++;
            elseif ($sh['status'] === 'S') $totS++;
            elseif ($sh['status'] === 'I') $totI++;
            elseif ($sh['status'] === 'A') $totA++;
            elseif ($sh['status'] === 'T') $totT++;
        }
        $pct = $totSesi > 0 ? round(($totH / $totSesi) * 100, 1) : 0;
    ?>
    <div class="space-y-6">
        <!-- Student Info Header -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-3xl font-bold">
                    👨‍🎓
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($targetSiswa['nama']) ?></h2>
                    <p class="text-xs text-slate-500 mt-0.5 font-mono">NIS: <?= htmlspecialchars($targetSiswa['nis'] ?: '-') ?> &bull; Kelas <?= htmlspecialchars($targetSiswa['kelas'] ?? $cleanKelas) ?></p>
                </div>
            </div>

            <!-- Stats Badge -->
            <div class="flex items-center gap-3 flex-wrap">
                <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-center min-w-[70px]">
                    <p class="text-[10px] font-bold text-emerald-700 uppercase">Hadir</p>
                    <p class="text-lg font-black text-emerald-800"><?= $totH ?></p>
                </div>
                <div class="p-3 rounded-2xl bg-blue-50 border border-blue-200 text-center min-w-[70px]">
                    <p class="text-[10px] font-bold text-blue-700 uppercase">Sakit</p>
                    <p class="text-lg font-black text-blue-800"><?= $totS ?></p>
                </div>
                <div class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-center min-w-[70px]">
                    <p class="text-[10px] font-bold text-amber-700 uppercase">Izin</p>
                    <p class="text-lg font-black text-amber-800"><?= $totI ?></p>
                </div>
                <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-center min-w-[70px]">
                    <p class="text-[10px] font-bold text-rose-700 uppercase">Alpa</p>
                    <p class="text-lg font-black text-rose-800"><?= $totA ?></p>
                </div>
                <div class="p-3 rounded-2xl bg-slate-100 border border-slate-200 text-center min-w-[90px]">
                    <p class="text-[10px] font-bold text-slate-600 uppercase">% Hadir</p>
                    <p class="text-lg font-black text-slate-800"><?= $pct ?>%</p>
                </div>
            </div>
        </div>

        <!-- Detail Riwayat Log Presensi Siswa -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Riwayat Catatan Presensi</h3>
                <p class="text-xs text-slate-400">Log kehadiran siswa di setiap sesi mata pelajaran dan kelas</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Tipe</th>
                            <th class="py-3 px-4">Mata Pelajaran / Sesi</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <?php if (empty($studentHistory)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada riwayat presensi tercatat untuk siswa ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($studentHistory as $sh): 
                                $statusBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                if ($sh['status'] === 'S') $statusBadge = 'bg-blue-50 text-blue-700 border-blue-200';
                                elseif ($sh['status'] === 'I') $statusBadge = 'bg-amber-50 text-amber-700 border-amber-200';
                                elseif ($sh['status'] === 'A') $statusBadge = 'bg-rose-50 text-rose-700 border-rose-200';
                                elseif ($sh['status'] === 'T') $statusBadge = 'bg-purple-50 text-purple-700 border-purple-200';
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 font-bold text-slate-800"><?= date('d M Y', strtotime($sh['tanggal'])) ?></td>
                                    <td class="py-3 px-4 uppercase text-[10px] font-bold text-slate-500"><?= htmlspecialchars($sh['tipe_presensi']) ?></td>
                                    <td class="py-3 px-4"><?= htmlspecialchars($sh['mata_pelajaran'] ?? '-') ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black border <?= $statusBadge ?>">
                                            <?= htmlspecialchars($sh['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars($sh['catatan'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
