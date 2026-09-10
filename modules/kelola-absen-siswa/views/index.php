<?php
/**
 * Dashboard Kelola Absen Siswa — Visual & Data Analytics
 * Menggunakan Chart.js untuk visualisasi kehadiran dan tren presensi
 */
?>
<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Sistem Presensi Siswa
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                    Real-time Monitoring
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2.5">
                <span>Dashboard Kelola Absen Siswa</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pusat monitoring kehadiran siswa harian, presensi mata pelajaran, dan rekapitulasi presensi terpadu
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="<?= url('kelola-absen-siswa/mapel') ?>" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                <span>Input Absen Mapel</span>
            </a>
            <a href="<?= url('kelola-absen-siswa/kelas') ?>" class="px-4 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                <span>Input Absen Kelas</span>
            </a>
            <a href="<?= url('kelola-absen-siswa/rekap') ?>" class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rekap Absen</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Kehadiran Hari Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-emerald-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kehadiran Hari Ini</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= $attendanceRate ?>%</h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    📊
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-emerald-700 font-bold">● <?= number_format($todayHadir) ?> Hadir</span>
                <span class="text-slate-500 font-medium">Dari <?= number_format($todayTotal) ?> Presensi</span>
            </div>
        </div>

        <!-- Total Siswa Aktif -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-teal-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa Aktif</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($totalSiswa) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    👨‍🎓
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-teal-700 font-bold">Terdaftar di BIP</span>
                <span class="text-slate-400">Status Aktif</span>
            </div>
        </div>

        <!-- Wadah Grup Absen -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Wadah Grup Absen</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($totalGroups) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    📁
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-indigo-700 font-bold">Wadah Terkonfigurasi</span>
                <a href="<?= url('kelola-absen-siswa/mapel') ?>" class="text-indigo-600 font-bold hover:underline">Lihat Wadah &rarr;</a>
            </div>
        </div>

        <!-- Rekap Presensi Bulan Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Presensi Bulan Ini</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($monthStats['total_presensi'] ?? 0) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    📅
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-emerald-600 font-bold">H: <?= $monthStats['total_h'] ?? 0 ?></span>
                <span class="text-blue-600 font-bold">S: <?= $monthStats['total_s'] ?? 0 ?></span>
                <span class="text-amber-600 font-bold">I: <?= $monthStats['total_i'] ?? 0 ?></span>
                <span class="text-rose-600 font-bold">A: <?= $monthStats['total_a'] ?? 0 ?></span>
            </div>
        </div>
    </div>

    <!-- Live Status Pill Bar (Hari Ini) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Rincian Status Kehadiran Hari Ini (<?= date('d M Y') ?>)</span>
            </h4>
            <span class="text-xs text-slate-400">Pembaruan data otomatis</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="p-3 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 text-center">
                <p class="text-[10px] font-bold text-emerald-700 uppercase">Hadir (H)</p>
                <p class="text-xl font-black text-emerald-800 mt-0.5"><?= number_format($todayHadir) ?></p>
            </div>
            <div class="p-3 rounded-2xl bg-blue-50/70 border border-blue-200/80 text-center">
                <p class="text-[10px] font-bold text-blue-700 uppercase">Sakit (S)</p>
                <p class="text-xl font-black text-blue-800 mt-0.5"><?= number_format($todaySakit) ?></p>
            </div>
            <div class="p-3 rounded-2xl bg-amber-50/70 border border-amber-200/80 text-center">
                <p class="text-[10px] font-bold text-amber-700 uppercase">Izin (I)</p>
                <p class="text-xl font-black text-amber-800 mt-0.5"><?= number_format($todayIzin) ?></p>
            </div>
            <div class="p-3 rounded-2xl bg-rose-50/70 border border-rose-200/80 text-center">
                <p class="text-[10px] font-bold text-rose-700 uppercase">Alpa / Tanpa Ket (A)</p>
                <p class="text-xl font-black text-rose-800 mt-0.5"><?= number_format($todayAlpa) ?></p>
            </div>
            <div class="p-3 rounded-2xl bg-purple-50/70 border border-purple-200/80 text-center">
                <p class="text-[10px] font-bold text-purple-700 uppercase">Terlambat (T)</p>
                <p class="text-xl font-black text-purple-800 mt-0.5"><?= number_format($todayTerlambat) ?></p>
            </div>
        </div>
    </div>

    <!-- Charts Section (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Tren Kehadiran 7 Hari Terakhir (Span 2) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-800">Tren Kehadiran 7 Hari Terakhir</h3>
                    <p class="text-xs text-slate-400">Komparasi jumlah siswa hadir vs tidak hadir per hari</p>
                </div>
                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-100 text-slate-600">
                    Live Tren
                </span>
            </div>
            <div class="h-64 sm:h-72">
                <canvas id="trenKehadiranChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Distribusi Status Kehadiran Bulan Ini (Span 1) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-800">Distribusi Status</h3>
                    <p class="text-xs text-slate-400">Proporsi kehadiran bulan <?= date('F Y') ?></p>
                </div>
            </div>
            <div class="h-56 sm:h-60 relative flex items-center justify-center">
                <canvas id="distribusiStatusChart"></canvas>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-[11px]">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span>Hadir: <?= $monthStats['total_h'] ?? 0 ?></span></div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span><span>Sakit: <?= $monthStats['total_s'] ?? 0 ?></span></div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span><span>Izin: <?= $monthStats['total_i'] ?? 0 ?></span></div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span><span>Alpa: <?= $monthStats['total_a'] ?? 0 ?></span></div>
            </div>
        </div>
    </div>

    <!-- Chart 3: Komparasi Kehadiran per Kelas (Bar Chart) -->
    <?php if (!empty($kelasChartLabels)): ?>
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Tingkat Kehadiran per Kelas (%)</h3>
                <p class="text-xs text-slate-400">Persentase kehadiran rata-rata per rombongan belajar</p>
            </div>
            <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                Data Presensi
            </span>
        </div>
        <div class="h-64 sm:h-72">
            <canvas id="kelasKehadiranChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Presensi Sesi Terbaru (Live Feed / Recent) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Riwayat Presensi Terbaru</h3>
                <p class="text-xs text-slate-400">Sesi presensi mapel & kelas harian yang baru dicatat</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-absen-siswa/rekap') ?>" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                    Lihat Semua Rekap &rarr;
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Tipe</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Mapel / Keterangan</th>
                        <th class="py-3.5 px-4">Guru / Petugas</th>
                        <th class="py-3.5 px-4 text-center">Kehadiran</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($recentSessions)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="font-bold text-slate-600">Belum ada catatan presensi</p>
                                <p class="text-[11px] mt-0.5">Mulai input presensi mapel atau presensi kelas menggunakan tombol di atas.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentSessions as $rs): 
                            $rate = $rs['total_siswa'] > 0 ? round(($rs['total_hadir'] / $rs['total_siswa']) * 100) : 0;
                            $tipeBadge = $rs['tipe_presensi'] === 'mapel'
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                : 'bg-teal-50 text-teal-700 border-teal-200';
                        ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    <?= date('d M Y', strtotime($rs['tanggal'])) ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase <?= $tipeBadge ?>">
                                        <?= htmlspecialchars($rs['tipe_presensi']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    <?= htmlspecialchars($rs['kelas']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    <?= htmlspecialchars($rs['mata_pelajaran'] ?? '-') ?>
                                    <?php if (!empty($rs['pertemuan_ke']) && $rs['tipe_presensi'] === 'mapel'): ?>
                                        <span class="text-[10px] font-bold text-slate-400 ml-1">(P<?= $rs['pertemuan_ke'] ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-medium">
                                    <?= htmlspecialchars($rs['nama_guru'] ?? 'Petugas') ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $rate >= 85 ? 'bg-emerald-100 text-emerald-800' : ($rate >= 70 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                        <?= $rs['total_hadir'] ?>/<?= $rs['total_siswa'] ?> (<?= $rate ?>%)
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <?php if ($rs['tipe_presensi'] === 'mapel'): ?>
                                        <a href="<?= url('kelola-absen-siswa/mapel/input?guru_id=' . $rs['guru_id'] . '&mapel=' . urlencode($rs['mata_pelajaran']) . '&kelas=' . urlencode($rs['kelas']) . '&tanggal=' . $rs['tanggal'] . '&pertemuan_ke=' . $rs['pertemuan_ke']) ?>" 
                                           class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-600 font-bold text-[11px] transition-all">
                                            Buka Presensi
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= url('kelola-absen-siswa/kelas/input?kelas=' . urlencode($rs['kelas']) . '&tanggal=' . $rs['tanggal']) ?>" 
                                           class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-600 font-bold text-[11px] transition-all">
                                            Buka Presensi
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart Tren Kehadiran
    const ctxTren = document.getElementById('trenKehadiranChart');
    if (ctxTren) {
        new Chart(ctxTren, {
            type: 'line',
            data: {
                labels: <?= json_encode($chartDates) ?>,
                datasets: [
                    {
                        label: 'Hadir',
                        data: <?= json_encode($chartHadir) ?>,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Tidak Hadir (S/I/A/T)',
                        data: <?= json_encode($chartTidakHadir) ?>,
                        borderColor: '#F43F5E',
                        backgroundColor: 'rgba(244, 63, 94, 0.08)',
                        borderWidth: 2,
                        borderDash: [4, 4],
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { family: 'inherit', size: 11, weight: 'bold' }, boxWidth: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { family: 'inherit', size: 10 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'inherit', size: 10 } }
                    }
                }
            }
        });
    }

    // 2. Chart Distribusi Status Kehadiran
    const ctxDist = document.getElementById('distribusiStatusChart');
    if (ctxDist) {
        const totalH = <?= (int)($monthStats['total_h'] ?? 0) ?>;
        const totalS = <?= (int)($monthStats['total_s'] ?? 0) ?>;
        const totalI = <?= (int)($monthStats['total_i'] ?? 0) ?>;
        const totalA = <?= (int)($monthStats['total_a'] ?? 0) ?>;
        const totalT = <?= (int)($monthStats['total_t'] ?? 0) ?>;

        const hasData = (totalH + totalS + totalI + totalA + totalT) > 0;

        new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Terlambat'],
                datasets: [{
                    data: hasData ? [totalH, totalS, totalI, totalA, totalT] : [1],
                    backgroundColor: hasData 
                        ? ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6']
                        : ['#E2E8F0'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 3. Chart Komparasi per Kelas
    const ctxKelas = document.getElementById('kelasKehadiranChart');
    if (ctxKelas) {
        new Chart(ctxKelas, {
            type: 'bar',
            data: {
                labels: <?= json_encode($kelasChartLabels) ?>,
                datasets: [{
                    label: '% Kehadiran',
                    data: <?= json_encode($kelasChartRates) ?>,
                    backgroundColor: '#14B8A6',
                    borderRadius: 8,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(v) { return v + '%'; },
                            font: { family: 'inherit', size: 10 }
                        },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'inherit', size: 10 } }
                    }
                }
            }
        });
    }
});
</script>
