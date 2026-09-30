<?php
/**
 * Dashboard Kelola Sarpras — Visual Analytics & Monitoring
 * Portal BIP
 */
?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-primary-50 text-primary-700 border border-primary-200">
                    Sistem Manajemen Sarana &amp; Prasarana
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Aset &amp; Fasilitas Kampus
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2.5">
                <span>Dashboard Kelola Sarpras</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Monitoring inventaris aset, sirkulasi peminjaman, kelayakan fasilitas, dan pemeliharaan gedung terpadu
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= url('kelola-sarpras/export') ?>" class="px-3.5 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                <span>Unduh Rekap CSV</span>
            </a>
            <a href="<?= url('kelola-sarpras/peminjaman') ?>" class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-.75m0-3.75V9.75a2.25 2.25 0 0 0-2.25-2.25h-7.5A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-.75m3.75-9.75H19.5a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-1.5" />
                </svg>
                <span>+ Catat Pinjam</span>
            </a>
            <a href="<?= url('kelola-sarpras/barang/create') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Aset Baru</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Aset -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-primary-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Item &amp; Unit</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-800 mt-1"><?= number_format($stats['total_qty']) ?> <span class="text-xs font-semibold text-slate-400">Unit</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    📦
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500"><?= $stats['total_item'] ?> Jenis Barang</span>
                <span class="text-primary-700 font-bold">Rp <?= number_format($stats['total_nilai'], 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Card 2: Kelayakan Kondisi -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-primary-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kondisi Baik</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-primary-600 mt-1">
                        <?= number_format($stats['kondisi_baik']) ?> <span class="text-xs font-semibold text-slate-400">Unit</span>
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    ✅
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-amber-600 font-medium">⚠️ <?= $stats['kondisi_rusak_ringan'] ?> Rusak Ringan</span>
                <span class="text-rose-600 font-medium">❌ <?= $stats['kondisi_rusak_berat'] ?> Berat</span>
            </div>
        </div>

        <!-- Card 3: Peminjaman Aktif -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sedang Dipinjam</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-indigo-600 mt-1"><?= number_format($stats['total_dipinjam']) ?> <span class="text-xs font-semibold text-slate-400">Transaksi</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    🔄
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Guru &amp; KBM</span>
                <a href="<?= url('kelola-sarpras/peminjaman') ?>" class="text-indigo-600 font-bold hover:underline">Kelola &rarr;</a>
            </div>
        </div>

        <!-- Card 4: Pemeliharaan & Perbaikan -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Perbaikan Aktif</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-amber-600 mt-1"><?= number_format($stats['total_pemeliharaan']) ?> <span class="text-xs font-semibold text-slate-400">Laporan</span></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                    🛠️
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Menunggu / Diproses</span>
                <a href="<?= url('kelola-sarpras/pemeliharaan') ?>" class="text-amber-600 font-bold hover:underline">Tindak Lanjut &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Charts Section (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Distribusi Kondisi Aset (Doughnut) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Kondisi Fisik Sarpras</h3>
                    <p class="text-xs text-slate-400">Persentase kelayakan pakai inventaris</p>
                </div>
                <span class="px-2.5 py-1 rounded-xl text-[10px] font-bold bg-slate-100 text-slate-600">Real-time</span>
            </div>
            <div class="relative flex items-center justify-center" style="height: 220px;">
                <canvas id="kondisiChart"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-slate-100 text-center">
                <div>
                    <p class="text-xs font-bold text-primary-600"><?= $stats['kondisi_baik'] ?></p>
                    <p class="text-[10px] text-slate-400">Baik</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-amber-600"><?= $stats['kondisi_rusak_ringan'] ?></p>
                    <p class="text-[10px] text-slate-400">Rusak Ringan</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-rose-600"><?= $stats['kondisi_rusak_berat'] ?></p>
                    <p class="text-[10px] text-slate-400">Rusak Berat</p>
                </div>
            </div>
        </div>

        <!-- Chart 2: Aset per Kategori (Bar Chart) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Distribusi Kuantitas Aset per Kategori</h3>
                    <p class="text-xs text-slate-400">Jumlah unit barang berdasarkan kelompok kategori sarana</p>
                </div>
                <a href="<?= url('kelola-sarpras/kategori') ?>" class="text-xs text-primary-600 font-bold hover:underline">Semua Kategori &rarr;</a>
            </div>
            <div class="relative" style="height: 250px;">
                <canvas id="kategoriChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Items & Quick Activity Table -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: 5 Aset Terbaru -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Aset &amp; Barang Terbaru</h3>
                    <p class="text-xs text-slate-400">Entri inventaris yang baru didaftarkan ke sistem</p>
                </div>
                <a href="<?= url('kelola-sarpras/barang') ?>" class="text-xs text-primary-600 font-bold hover:underline">Lihat Semua (<?= $stats['total_item'] ?>) &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                        <tr>
                            <th class="py-3 px-4 rounded-l-2xl">Kode &amp; Nama Barang</th>
                            <th class="py-3 px-3">Kategori</th>
                            <th class="py-3 px-3">Ruangan</th>
                            <th class="py-3 px-3">Kondisi</th>
                            <th class="py-3 px-3 rounded-r-2xl text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($recentBarang)): ?>
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400">Belum ada data barang.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentBarang as $b): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800"><?= e($b['nama_barang']) ?></div>
                                        <div class="text-[11px] font-mono text-primary-600"><?= e($b['kode_barang']) ?> &bull; <?= $b['jumlah'] ?> <?= e($b['satuan']) ?></div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-medium bg-slate-100 text-slate-700">
                                            <?= e($b['nama_kategori'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-medium text-slate-700"><?= e($b['nama_ruangan'] ?? '-') ?></div>
                                        <div class="text-[10px] text-slate-400"><?= e($b['unit']) ?></div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <?php if ($b['kondisi'] === 'Baik'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-50 text-primary-600 border border-primary-200">Baik</span>
                                        <?php elseif ($b['kondisi'] === 'Rusak Ringan'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200">Rusak Ringan</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">Rusak Berat</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <?php if ($b['status'] === 'Tersedia'): ?>
                                            <span class="text-primary-600 font-bold text-[11px]">● Tersedia</span>
                                        <?php elseif ($b['status'] === 'Dipinjam'): ?>
                                            <span class="text-indigo-600 font-bold text-[11px]">● Dipinjam</span>
                                        <?php else: ?>
                                            <span class="text-amber-600 font-bold text-[11px]">● <?= e($b['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Peminjaman & Pemeliharaan Aktif -->
        <div class="space-y-6">
            <!-- Peminjaman Aktif -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        <span>Peminjaman Aktif</span>
                    </h3>
                    <a href="<?= url('kelola-sarpras/peminjaman') ?>" class="text-[11px] text-indigo-600 font-bold hover:underline">Semua</a>
                </div>

                <?php if (empty($activePinjam)): ?>
                    <p class="text-xs text-slate-400 py-3 text-center">Tidak ada barang yang sedang dipinjam.</p>
                <?php else: ?>
                    <div class="space-y-2.5">
                        <?php foreach (array_slice($activePinjam, 0, 3) as $p): ?>
                            <div class="p-3 rounded-2xl bg-indigo-50/50 border border-indigo-100 flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-slate-800"><?= e($p['nama_barang']) ?></p>
                                    <p class="text-[11px] text-slate-500">Peminjam: <strong><?= e($p['nama_peminjam']) ?></strong></p>
                                    <p class="text-[10px] text-indigo-600 font-medium">Batas: <?= date('d M Y', strtotime($p['estimasi_kembali'])) ?></p>
                                </div>
                                <span class="px-2 py-1 rounded-xl text-[10px] font-bold bg-indigo-600 text-white">Dipinjam</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pemeliharaan Menunggu -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                        <span>Perbaikan Berjalan</span>
                    </h3>
                    <a href="<?= url('kelola-sarpras/pemeliharaan') ?>" class="text-[11px] text-amber-600 font-bold hover:underline">Semua</a>
                </div>

                <?php if (empty($activeMnt)): ?>
                    <p class="text-xs text-slate-400 py-3 text-center">Seluruh fasilitas dalam kondisi baik.</p>
                <?php else: ?>
                    <div class="space-y-2.5">
                        <?php foreach (array_slice($activeMnt, 0, 3) as $m): ?>
                            <div class="p-3 rounded-2xl bg-amber-50/50 border border-amber-100 flex items-center justify-between text-xs">
                                <div class="min-w-0 pr-2">
                                    <p class="font-bold text-slate-800 truncate"><?= e($m['judul_laporan']) ?></p>
                                    <p class="text-[11px] text-slate-500 truncate"><?= e($m['nama_barang'] ?? $m['nama_ruangan'] ?? 'Fasilitas') ?></p>
                                    <p class="text-[10px] text-amber-700">Urgensi: <strong><?= e($m['tingkat_urgensi']) ?></strong></p>
                                </div>
                                <span class="px-2 py-1 rounded-xl text-[10px] font-bold <?= $m['status'] === 'Diproses' ? 'bg-amber-500 text-white' : 'bg-slate-200 text-slate-700' ?> shrink-0">
                                    <?= e($m['status']) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart Kondisi
    const ctxKondisi = document.getElementById('kondisiChart').getContext('2d');
    new Chart(ctxKondisi, {
        type: 'doughnut',
        data: {
            labels: ['Baik', 'Rusak Ringan', 'Rusak Berat'],
            datasets: [{
                data: [<?= $stats['kondisi_baik'] ?>, <?= $stats['kondisi_rusak_ringan'] ?>, <?= $stats['kondisi_rusak_berat'] ?>],
                backgroundColor: ['#10B981', '#F59E0B', '#EF4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            },
            cutout: '70%'
        }
    });

    // 2. Chart Kategori
    const katLabels = <?= json_encode(array_column($stats['per_kategori'], 'nama_kategori')) ?>;
    const katData = <?= json_encode(array_map('intval', array_column($stats['per_kategori'], 'total_qty'))) ?>;

    const ctxKat = document.getElementById('kategoriChart').getContext('2d');
    new Chart(ctxKat, {
        type: 'bar',
        data: {
            labels: katLabels,
            datasets: [{
                label: 'Jumlah Unit Barang',
                data: katData,
                backgroundColor: '#0D9488',
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
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, maxRotation: 30 }
                }
            }
        }
    });
});
</script>
