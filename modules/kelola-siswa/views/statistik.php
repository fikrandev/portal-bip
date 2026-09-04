<?php
/**
 * Dashboard & Monitoring Statistik Data Siswa - Portal BIP
 */
?>
<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                </div>
                <span>Dashboard & Statistik Kesiswaan</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Pusat monitoring demografi siswa, distribusi per jenjang, status Dapodik, rekapitulasi buku induk, dan prestasi siswa.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-siswa') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                <span>Kelola Siswa</span>
            </a>
            <a href="<?= url('kelola-siswa/create') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-emerald-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Tambah Siswa</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Siswa Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-emerald-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Siswa Aktif</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($totalAktif) ?></h3>
                    <p class="text-[11px] text-emerald-600 font-medium mt-1">Total terdaftar di Portal</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span>Laki-Laki: <strong class="text-blue-600"><?= number_format($totalLaki) ?></strong></span>
                <span>Perempuan: <strong class="text-pink-600"><?= number_format($totalPerempuan) ?></strong></span>
            </div>
        </div>

        <!-- Terdaftar Dapodik -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-blue-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sync Dapodik</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($totalDapodik) ?></h3>
                    <p class="text-[11px] text-blue-600 font-medium mt-1">Sudah Sinkron Online</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <span>Belum Sync: <strong><?= number_format(max(0, $totalAktif - $totalDapodik)) ?></strong></span>
                <span>Persentase: <strong><?= $totalAktif > 0 ? round(($totalDapodik / $totalAktif) * 100) : 0 ?>%</strong></span>
            </div>
        </div>

        <!-- Total Prestasi Siswa -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Prestasi Siswa</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($totalPrestasi) ?></h3>
                    <p class="text-[11px] text-amber-600 font-medium mt-1">Penghargaan & Juara</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" /></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <a href="<?= url('kelola-siswa/prestasi') ?>" class="text-amber-600 hover:underline font-semibold flex items-center gap-1">
                    Lihat Semua Prestasi →
                </a>
            </div>
        </div>

        <!-- Siswa Keluar / Mutasi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-rose-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Mutasi / Alumni</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($totalKeluar) ?></h3>
                    <p class="text-[11px] text-rose-600 font-medium mt-1">Siswa Pindah / Lulus</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                <a href="<?= url('kelola-siswa/keluar') ?>" class="text-rose-600 hover:underline font-semibold flex items-center gap-1">
                    Kelola Siswa Keluar →
                </a>
            </div>
        </div>
    </div>

    <!-- Distribution by Education Level (Jenjang) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- PAUD / TK -->
        <a href="<?= url('kelola-siswa?jenjang=PAUD') ?>" class="bg-gradient-to-br from-emerald-500 to-teal-700 p-5 rounded-2xl text-white shadow-md shadow-emerald-600/20 hover:scale-[1.02] transition-transform">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider opacity-80">PAUD / TK</span>
                <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-semibold">Tingkat Awal</span>
            </div>
            <h4 class="text-2xl font-extrabold mt-3"><?= number_format($totalPaud) ?> <span class="text-xs font-normal opacity-80">Siswa</span></h4>
            <p class="text-[11px] opacity-80 mt-1">Bina Insan Palu</p>
        </a>

        <!-- SD -->
        <a href="<?= url('kelola-siswa?jenjang=SD') ?>" class="bg-gradient-to-br from-blue-600 to-indigo-700 p-5 rounded-2xl text-white shadow-md shadow-blue-600/20 hover:scale-[1.02] transition-transform">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider opacity-80">SD IT</span>
                <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-semibold">Sekolah Dasar</span>
            </div>
            <h4 class="text-2xl font-extrabold mt-3"><?= number_format($totalSd) ?> <span class="text-xs font-normal opacity-80">Siswa</span></h4>
            <p class="text-[11px] opacity-80 mt-1">Bina Insan Palu</p>
        </a>

        <!-- SMP -->
        <a href="<?= url('kelola-siswa?jenjang=SMP') ?>" class="bg-gradient-to-br from-indigo-600 to-purple-700 p-5 rounded-2xl text-white shadow-md shadow-indigo-600/20 hover:scale-[1.02] transition-transform">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider opacity-80">SMP IT</span>
                <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-semibold">Menengah Pertama</span>
            </div>
            <h4 class="text-2xl font-extrabold mt-3"><?= number_format($totalSmp) ?> <span class="text-xs font-normal opacity-80">Siswa</span></h4>
            <p class="text-[11px] opacity-80 mt-1">Bina Insan Palu</p>
        </a>

        <!-- SMA -->
        <a href="<?= url('kelola-siswa?jenjang=SMA') ?>" class="bg-gradient-to-br from-purple-600 to-rose-700 p-5 rounded-2xl text-white shadow-md shadow-purple-600/20 hover:scale-[1.02] transition-transform">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider opacity-80">SMA IT</span>
                <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-semibold">Menengah Atas</span>
            </div>
            <h4 class="text-2xl font-extrabold mt-3"><?= number_format($totalSma) ?> <span class="text-xs font-normal opacity-80">Siswa</span></h4>
            <p class="text-[11px] opacity-80 mt-1">Bina Insan Palu</p>
        </a>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Distribusi Siswa per Jenjang & Gender -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Distribusi Siswa per Jenjang</h3>
                    <p class="text-xs text-slate-500">Perbandingan jumlah siswa menurut satuan pendidikan</p>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="chartJenjang"></canvas>
            </div>
        </div>

        <!-- Chart 2: Komposisi Jenis Kelamin -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Komposisi Gender Siswa</h3>
                    <p class="text-xs text-slate-500">Perbandingan Laki-Laki vs Perempuan</p>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="chartGender"></canvas>
            </div>
        </div>
    </div>

    <!-- Table Section: Rekapitulasi Rombel & Kelas -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Distribusi Rombongan Belajar (Kelas)</h3>
                <p class="text-xs text-slate-500">Rincian jumlah siswa aktif per kelas</p>
            </div>
            <a href="<?= url('kelola-siswa/buku-induk') ?>" class="text-xs font-semibold text-primary-600 hover:underline">
                Buka Buku Induk Siswa →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100 uppercase">
                    <tr>
                        <th class="px-5 py-3">Nama Kelas / Rombel</th>
                        <th class="px-5 py-3">Jenjang</th>
                        <th class="px-5 py-3 text-center">Laki-Laki</th>
                        <th class="px-5 py-3 text-center">Perempuan</th>
                        <th class="px-5 py-3 text-center">Total Siswa</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($rekapKelas)): ?>
                    <tr>
                        <td colspan="6" class="px-5 py-6 text-center text-slate-400">Belum ada data rombongan belajar.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rekapKelas as $rk): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3.5 font-bold text-slate-800"><?= e($rk['kelas'] ?: 'Tanpa Kelas') ?></td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                <?= e($rk['jenjang'] ?: 'SD') ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center text-blue-600 font-medium"><?= number_format($rk['laki']) ?></td>
                        <td class="px-5 py-3.5 text-center text-pink-600 font-medium"><?= number_format($rk['perempuan']) ?></td>
                        <td class="px-5 py-3.5 text-center font-extrabold text-slate-900"><?= number_format($rk['total']) ?></td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="<?= url('kelola-siswa?kelas=' . urlencode($rk['kelas'])) ?>" class="px-3 py-1 bg-primary-50 text-primary-700 hover:bg-primary-100 rounded-lg text-xs font-semibold transition-colors">
                                Lihat Siswa
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Jenjang Chart
    const ctxJenjang = document.getElementById('chartJenjang').getContext('2d');
    new Chart(ctxJenjang, {
        type: 'bar',
        data: {
            labels: ['PAUD / TK', 'SD IT', 'SMP IT', 'SMA IT'],
            datasets: [{
                label: 'Jumlah Siswa',
                data: [<?= $totalPaud ?>, <?= $totalSd ?>, <?= $totalSmp ?>, <?= $totalSma ?>],
                backgroundColor: ['#10B981', '#3B82F6', '#6366F1', '#A855F7'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Gender Chart
    const ctxGender = document.getElementById('chartGender').getContext('2d');
    new Chart(ctxGender, {
        type: 'doughnut',
        data: {
            labels: ['Laki-Laki', 'Perempuan'],
            datasets: [{
                data: [<?= $totalLaki ?>, <?= $totalPerempuan ?>],
                backgroundColor: ['#3B82F6', '#EC4899'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%'
        }
    });
});
</script>
