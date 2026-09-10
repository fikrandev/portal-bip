<?php
/**
 * Dashboard Kelola Nilai — Visual & Data Analytics
 * Menggunakan Chart.js untuk visualisasi performa akademik
 */
?>
<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Sistem Penilaian Akademik
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Kurikulum Merdeka
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2.5">
                <span>Dashboard Kelola Nilai</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pusat monitoring performa penilaian siswa, ketercapaian tujuan pembelajaran, dan statistik nilai per mata pelajaran
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="<?= url('kelola-nilai/group') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span>Daftar Wadah</span>
            </a>
            <a href="<?= url('kelola-nilai/rekap') ?>" class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rekap Nilai</span>
            </a>
            <a href="<?= url('kelola-nilai/group/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Grup Nilai</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Wadah -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-teal-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Wadah Grup Nilai</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($totalGroups) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    📁
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-emerald-700 font-bold">● <?= $totalActiveGroups ?> Wadah Aktif</span>
                <span class="text-slate-400">○ <?= $totalInactiveGroups ?> Nonaktif</span>
            </div>
        </div>

        <!-- Total Nilai Terinput -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Nilai Diinput</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($totalNilai) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    📝
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Asesmen Formatif &amp; Sumatif</span>
                <span class="text-indigo-600 font-bold">Tersimpan</span>
            </div>
        </div>

        <!-- Siswa Dinilai -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa Terdata</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($totalSiswaDinilai) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    👨‍🎓
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Memiliki Rekam Nilai</span>
                <span class="text-amber-600 font-bold">Aktif</span>
            </div>
        </div>

        <!-- Rata-Rata Nilai -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-emerald-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-Rata Nilai</p>
                    <h3 class="text-3xl font-black text-emerald-600 mt-1"><?= $avgNilai > 0 ? $avgNilai : '-' ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    📈
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Rerata Nilai Keseluruhan</span>
                <span class="px-2 py-0.5 rounded-full font-bold <?= $avgNilai >= 75 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                    <?= $avgNilai >= 85 ? 'Sangat Baik' : ($avgNilai >= 75 ? 'Baik' : ($avgNilai >= 65 ? 'Cukup' : 'Evaluasi')) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Charts Section (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Chart 1: Rata-Rata Nilai per Mapel (8 cols) -->
        <div class="lg:col-span-8 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>📊</span>
                        <span>Rata-Rata Capaian Nilai per Mata Pelajaran</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Perbandingan rata-rata capaian nilai siswa antar mata pelajaran</p>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600">
                    Skala 0 - 100
                </span>
            </div>
            
            <?php if (empty($mapelLabels)): ?>
                <div class="py-16 text-center text-slate-400">
                    <div class="text-3xl mb-2">📉</div>
                    <p class="text-xs font-semibold">Belum ada data nilai mata pelajaran untuk ditampilkan pada grafik.</p>
                </div>
            <?php else: ?>
                <div class="h-64 sm:h-72">
                    <canvas id="chartMapelAvg"></canvas>
                </div>
            <?php endif; ?>
        </div>

        <!-- Chart 2: Distribusi Predikat Nilai (4 cols) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>🎯</span>
                        <span>Distribusi Predikat</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Proporsi nilai siswa (A, B, C, D)</p>
                </div>
            </div>

            <?php 
            $totalPredikat = array_sum($predikatCounts);
            if ($totalPredikat === 0): 
            ?>
                <div class="py-16 text-center text-slate-400">
                    <div class="text-3xl mb-2">🎯</div>
                    <p class="text-xs font-semibold">Belum ada sebaran predikat.</p>
                </div>
            <?php else: ?>
                <div class="h-52 relative flex items-center justify-center">
                    <canvas id="chartPredikatDoughnut"></canvas>
                </div>
                <!-- Legend Details -->
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-[11px]">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-md bg-emerald-500"></span>
                        <span class="text-slate-600 font-medium">A (≥ 85): <strong><?= $predikatCounts['A'] ?></strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-md bg-sky-500"></span>
                        <span class="text-slate-600 font-medium">B (75-84): <strong><?= $predikatCounts['B'] ?></strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-md bg-amber-500"></span>
                        <span class="text-slate-600 font-medium">C (65-74): <strong><?= $predikatCounts['C'] ?></strong></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-md bg-rose-500"></span>
                        <span class="text-slate-600 font-medium">D (&lt; 65): <strong><?= $predikatCounts['D'] ?></strong></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informasi Singkat Data: Wadah Terbaru & Top Siswa -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Wadah Nilai Aktif Terbaru (7 cols) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>📁</span>
                        <span>Wadah Grup Nilai Terbaru</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar wadah penilaian aktif yang baru dibuat</p>
                </div>
                <a href="<?= url('kelola-nilai/group') ?>" class="text-xs font-bold text-teal-600 hover:text-teal-700">
                    Lihat Semua &rarr;
                </a>
            </div>

            <?php if (empty($recentGroups)): ?>
                <div class="py-12 text-center text-slate-400">
                    <p class="text-xs">Belum ada wadah nilai yang dibuat.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-100 uppercase tracking-wider font-semibold">
                                <th class="pb-2.5">Wadah &amp; Unit</th>
                                <th class="pb-2.5">Tahun &amp; Smt</th>
                                <th class="pb-2.5 text-center">Progress Input</th>
                                <th class="pb-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php foreach ($recentGroups as $rg): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3">
                                        <div class="font-bold text-slate-800 leading-snug">
                                            <?= e($rg['judul']) ?>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-100 font-bold text-slate-600">Unit <?= e($rg['unit']) ?></span>
                                            <span>&bull;</span>
                                            <span><?= !empty($rg['is_active']) ? '<span class="text-emerald-600 font-bold">Aktif</span>' : '<span class="text-slate-400">Nonaktif</span>' ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="text-slate-700 font-semibold"><?= e($rg['nama_tahun'] ?: '2026/2027') ?></span>
                                        <span class="block text-[10px] text-slate-400">Smt <?= e($rg['semester']) ?></span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200">
                                            <?= (int)$rg['total_input'] ?> Nilai
                                        </span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <a href="<?= url('kelola-nilai/input/' . $rg['id']) ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-[11px] transition-colors">
                                            <span>Buka</span>
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Top 5 Siswa dengan Nilai Tertinggi (5 cols) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>🏆</span>
                        <span>Top Capaian Siswa</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Siswa dengan rata-rata nilai asesmen tertinggi</p>
                </div>
                <a href="<?= url('kelola-nilai/rekap') ?>" class="text-xs font-bold text-amber-600 hover:text-amber-700">
                    Buka Rekap &rarr;
                </a>
            </div>

            <?php if (empty($topSiswa)): ?>
                <div class="py-12 text-center text-slate-400">
                    <p class="text-xs">Belum ada siswa dengan rekam nilai lengkap.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($topSiswa as $rankIdx => $ts): 
                        $medals = ['🥇', '🥈', '🥉', '4️⃣', '5️⃣'];
                    ?>
                        <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-100 hover:border-amber-200 hover:bg-amber-50/20 transition-all">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-lg"><?= $medals[$rankIdx] ?? ($rankIdx + 1) ?></span>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-800 text-xs truncate"><?= e($ts['nama']) ?></h4>
                                    <p class="text-[10px] text-slate-400 truncate">
                                        <?= !empty($ts['kelas']) ? 'Kelas ' . e($ts['kelas']) : 'Unit ' . e($ts['jenjang'] ?? '-') ?>
                                        &bull; <?= (int)$ts['total_asesmen'] ?> Asesmen
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-800">
                                    <?= number_format($ts['rerata_nilai'], 1) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($mapelLabels)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Chart Rata-Rata Nilai per Mapel
    const ctxMapel = document.getElementById('chartMapelAvg');
    if (ctxMapel) {
        new Chart(ctxMapel, {
            type: 'bar',
            data: {
                labels: <?= json_encode($mapelLabels) ?>,
                datasets: [{
                    label: 'Rerata Nilai',
                    data: <?= json_encode($mapelScores) ?>,
                    backgroundColor: [
                        'rgba(13, 148, 136, 0.85)',
                        'rgba(79, 70, 229, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(14, 165, 233, 0.85)',
                        'rgba(168, 85, 247, 0.85)',
                        'rgba(236, 72, 153, 0.85)',
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(239, 68, 68, 0.85)'
                    ],
                    borderColor: [
                        '#0d9488', '#4f46e5', '#f59e0b', '#0ea5e9',
                        '#a855f7', '#ec4899', '#10b981', '#ef4444'
                    ],
                    borderWidth: 1.5,
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                return ' Rerata: ' + ctx.parsed.y + ' / 100';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: {
                            font: { size: 10, weight: 'bold' },
                            color: '#64748b'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, weight: '600' },
                            color: '#475569',
                            maxRotation: 20,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }

    // 2. Chart Distribusi Predikat (Doughnut)
    const ctxPred = document.getElementById('chartPredikatDoughnut');
    if (ctxPred) {
        new Chart(ctxPred, {
            type: 'doughnut',
            data: {
                labels: ['Sangat Baik (A)', 'Baik (B)', 'Cukup (C)', 'Perlu Bimbingan (D)'],
                datasets: [{
                    data: [
                        <?= (int)$predikatCounts['A'] ?>,
                        <?= (int)$predikatCounts['B'] ?>,
                        <?= (int)$predikatCounts['C'] ?>,
                        <?= (int)$predikatCounts['D'] ?>
                    ],
                    backgroundColor: [
                        '#10b981', // Emerald
                        '#0ea5e9', // Sky
                        '#f59e0b', // Amber
                        '#f43f5e'  // Rose
                    ],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const val = ctx.parsed;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ' ' + ctx.label + ': ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>
