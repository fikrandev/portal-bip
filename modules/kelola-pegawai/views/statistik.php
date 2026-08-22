<?php
/**
 * Dashboard & Monitoring Data Kepegawaian
 */
?>
<!-- Include Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-600 to-indigo-700 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                </div>
                <span>Dashboard & Monitoring Pegawai</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Pusat kontrol data SDM, pemantauan masa kerja, status penugasan SK, rekap prestasi, dan pengembangan kompetensi guru.
            </p>
        </div>
    </div>

    <!-- Control & Filter Panel -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between gap-4 mb-3 pb-2 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
                <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Panel Kontrol Filter Data</span>
            </div>
            <?php if (!empty($filterUnit) || !empty($filterStatusKerja) || !empty($filterJenisPegawai) || !empty($filterDapodik) || !empty($filterMasaKerja)): ?>
                <a href="<?= url('kelola-pegawai/statistik') ?>" class="text-xs text-rose-600 hover:underline font-semibold flex items-center gap-1">
                    ✕ Reset Filter
                </a>
            <?php endif; ?>
        </div>

        <form action="<?= url('kelola-pegawai/statistik') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Filter Unit Tugas -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Unit Tugas</label>
                <select name="unit_tugas" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs focus:bg-white focus:border-primary-500 transition-colors">
                    <option value="">Semua Unit Tugas</option>
                    <?php foreach ($unitList as $u): ?>
                        <option value="<?= e($u['nama']) ?>" <?= $filterUnit === $u['nama'] ? 'selected' : '' ?>><?= e($u['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Status Kerja -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Kerja</label>
                <select name="status_kerja" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs focus:bg-white focus:border-primary-500 transition-colors">
                    <option value="">Semua Status Kerja</option>
                    <?php foreach ($statusKerjaList as $sk): ?>
                        <option value="<?= e($sk['nama']) ?>" <?= $filterStatusKerja === $sk['nama'] ? 'selected' : '' ?>><?= e($sk['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Jenis Pegawai -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Jenis Pegawai</label>
                <select name="jenis_pegawai" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs focus:bg-white focus:border-primary-500 transition-colors">
                    <option value="">Semua Jenis (Guru / Tendik)</option>
                    <?php foreach ($jenisPegawaiList as $jp): ?>
                        <option value="<?= e($jp['nama']) ?>" <?= $filterJenisPegawai === $jp['nama'] ? 'selected' : '' ?>><?= e($jp['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Status Dapodik -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Status Dapodik</label>
                <select name="status_dapodik" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs focus:bg-white focus:border-primary-500 transition-colors">
                    <option value="">Semua Status Dapodik</option>
                    <option value="Sudah Terdaftar" <?= $filterDapodik === 'Sudah Terdaftar' ? 'selected' : '' ?>>Sudah Terdaftar Dapodik</option>
                    <option value="Belum Terdaftar" <?= $filterDapodik === 'Belum Terdaftar' ? 'selected' : '' ?>>Belum Terdaftar Dapodik</option>
                </select>
            </div>

            <!-- Filter Rentang Masa Kerja -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Rentang Masa Kerja</label>
                <select name="masa_kerja" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs focus:bg-white focus:border-primary-500 transition-colors">
                    <option value="">Semua Masa Kerja</option>
                    <option value="<1" <?= $filterMasaKerja === '<1' ? 'selected' : '' ?>>Baru (&lt; 1 Tahun)</option>
                    <option value="1-3" <?= $filterMasaKerja === '1-3' ? 'selected' : '' ?>>1 s/d 3 Tahun</option>
                    <option value="3-5" <?= $filterMasaKerja === '3-5' ? 'selected' : '' ?>>3 s/d 5 Tahun</option>
                    <option value="5-10" <?= $filterMasaKerja === '5-10' ? 'selected' : '' ?>>5 s/d 10 Tahun</option>
                    <option value=">10" <?= $filterMasaKerja === '>10' ? 'selected' : '' ?>>Senior (&gt; 10 Tahun)</option>
                </select>
            </div>
        </form>
    </div>

    <!-- KPI Summary Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Pegawai Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-primary-300 transition-all">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pegawai Aktif</p>
                <h3 class="text-3xl font-extrabold text-primary-950"><?= number_format($kpi['total_aktif']) ?></h3>
                <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
                    <span class="text-sky-600 font-bold">👨 <?= $kpi['pria_aktif'] ?></span> • 
                    <span class="text-rose-500 font-bold">👩 <?= $kpi['wanita_aktif'] ?></span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
        </div>

        <!-- Guru vs Tendik -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-emerald-300 transition-all">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Guru & Pendidik</p>
                <h3 class="text-3xl font-extrabold text-emerald-600"><?= number_format($kpi['total_guru']) ?></h3>
                <p class="text-xs text-slate-500 font-medium">Tendik / Staf: <span class="font-bold text-slate-700"><?= $kpi['total_tendik'] ?></span> orang</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                </svg>
            </div>
        </div>

        <!-- Penugasan SK Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-amber-300 transition-all">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tertugaskan di SK</p>
                <h3 class="text-3xl font-extrabold text-amber-600"><?= number_format($kpi['total_ditugaskan']) ?></h3>
                <p class="text-xs text-slate-500 font-medium">
                    Belum Ditugaskan: <span class="<?= $kpi['total_belum_ditugaskan'] > 0 ? 'text-rose-600 font-bold' : 'text-slate-700' ?>"><?= $kpi['total_belum_ditugaskan'] ?></span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V8.25ZM6.75 21v-3.375c0-.621.504-1.125 1.125-1.125H22.5" />
                </svg>
            </div>
        </div>

        <!-- Prestasi & Pelatihan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between relative overflow-hidden group hover:border-purple-300 transition-all">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Prestasi & Diklat</p>
                <h3 class="text-3xl font-extrabold text-purple-600"><?= number_format($kpi['total_prestasi']) ?> <span class="text-sm font-normal text-slate-400">Prestasi</span></h3>
                <p class="text-xs text-slate-500 font-medium">Diklat: <span class="font-bold text-slate-700"><?= $kpi['total_pelatihan'] ?> (<?= $kpi['total_jp'] ?> JP)</span></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" />
                </svg>
            </div>
        </div>

    </div>

    <!-- Alert Monitoring: Pegawai Belum Ditugaskan di SK Aktif -->
    <?php if ($kpi['total_belum_ditugaskan'] > 0 && !empty($activeGrup)): ?>
        <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-300/80 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-amber-950 text-sm">Perhatian: Ada <?= $kpi['total_belum_ditugaskan'] ?> Pegawai Aktif Belum Ditugaskan di SK Aktif</h4>
                    <p class="text-xs text-amber-800 mt-0.5">
                        SK Aktif saat ini: <span class="font-bold"><?= e($activeGrup['nama_grup']) ?></span> (<?= e($activeGrup['no_sk'] ?: 'Belum ada No SK') ?>). Lengkapi penugasan pegawai agar struktur organisasi teratur.
                    </p>
                </div>
            </div>
            <a href="<?= url('kelola-pegawai/penugasan/grup/' . $activeGrup['id']) ?>" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow transition-colors shrink-0">
                <span>Kelola Anggota SK →</span>
            </a>
        </div>
    <?php endif; ?>

    <!-- Charts Grid Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Chart: Distribusi Unit Tugas -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Distribusi Pegawai per Unit Tugas</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Jumlah guru dan staf yang ditempatkan pada masing-masing unit</p>
                    </div>
                </div>
                <div class="h-64 sm:h-72 relative">
                    <canvas id="chartUnitTugas"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart: Status Kerja & Jenis Pegawai (Doughnut) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Status Kepegawaian</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Proporsi ikatan kerja pegawai</p>
                    </div>
                </div>
                <div class="h-56 relative flex items-center justify-center">
                    <canvas id="chartStatusKerja"></canvas>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 pt-4 border-t border-slate-100 text-xs mt-4">
                <div class="text-center p-2 rounded-xl bg-slate-50">
                    <span class="text-slate-400 block text-[10.5px]">Dapodik Terdaftar</span>
                    <span class="font-bold text-emerald-600 text-sm"><?= $kpi['dapodik_sudah'] ?></span>
                </div>
                <div class="text-center p-2 rounded-xl bg-slate-50">
                    <span class="text-slate-400 block text-[10.5px]">Belum Dapodik</span>
                    <span class="font-bold text-slate-700 text-sm"><?= $kpi['dapodik_belum'] ?></span>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts Grid Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Chart: Piramida Masa Kerja (Berdasarkan Tanggal Masuk) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Piramida Masa Kerja Pegawai</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Dihitung dari tanggal mulai masuk kerja</p>
                    </div>
                </div>
                <div class="h-60 relative">
                    <canvas id="chartMasaKerja"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart: Pendidikan Terakhir -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Kualifikasi Pendidikan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Jenjang pendidikan formal tertinggi</p>
                    </div>
                </div>
                <div class="h-60 relative">
                    <canvas id="chartPendidikan"></canvas>
                </div>
            </div>
        </div>

        <!-- Chart: Komposisi Gender & Usia -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base">Kelompok Usia Pegawai</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Rentang usia tenaga pendidik & staf</p>
                    </div>
                </div>
                <div class="h-60 relative">
                    <canvas id="chartUsia"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Data Monitoring Tables Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top Guru / Pegawai Berprestasi -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                            🏆
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Top Pegawai Berprestasi</h3>
                            <p class="text-xs text-slate-400">Guru & staf dengan perolehan penghargaan terbanyak</p>
                        </div>
                    </div>
                    <a href="<?= url('kelola-pegawai/prestasi') ?>" class="text-xs text-amber-600 hover:underline font-semibold">
                        Lihat Semua →
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    <?php if (empty($topPrestasi)): ?>
                        <p class="text-xs text-slate-400 py-6 text-center italic">Belum ada data prestasi yang tercatat.</p>
                    <?php else: ?>
                        <?php $rank = 1; foreach ($topPrestasi as $tp): ?>
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full <?= $rank === 1 ? 'bg-amber-400 text-white font-extrabold' : ($rank === 2 ? 'bg-slate-300 text-slate-700 font-bold' : ($rank === 3 ? 'bg-amber-700/20 text-amber-800 font-bold' : 'bg-slate-100 text-slate-500 font-medium')) ?> text-xs flex items-center justify-center shrink-0">
                                        <?= $rank++ ?>
                                    </span>
                                    <div>
                                        <a href="<?= url('kelola-pegawai/prestasi/pegawai/' . $tp['id']) ?>" class="font-bold text-slate-800 hover:text-amber-600 text-xs transition-colors">
                                            <?= e($tp['nama']) ?><?= !empty($tp['gelar']) ? ', ' . e($tp['gelar']) : '' ?>
                                        </a>
                                        <p class="text-[11px] text-slate-400"><?= e($tp['unit_tugas'] ?: 'Yayasan BIP') ?> • <?= e($tp['jabatan'] ?: 'Guru') ?></p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-extrabold">
                                        <?= $tp['total_prestasi'] ?> Penghargaan
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Pegawai Terbanyak Pelatihan / JP -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                            📚
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Top Pengembangan Diri & Diklat</h3>
                            <p class="text-xs text-slate-400">Guru & staf dengan jam pelatihan (JP) terakumulasi terbanyak</p>
                        </div>
                    </div>
                    <a href="<?= url('kelola-pegawai/pelatihan') ?>" class="text-xs text-indigo-600 hover:underline font-semibold">
                        Lihat Semua →
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    <?php if (empty($topPelatihan)): ?>
                        <p class="text-xs text-slate-400 py-6 text-center italic">Belum ada data pelatihan yang tercatat.</p>
                    <?php else: ?>
                        <?php $rank = 1; foreach ($topPelatihan as $tpl): ?>
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-full <?= $rank === 1 ? 'bg-indigo-600 text-white font-extrabold' : ($rank === 2 ? 'bg-indigo-300 text-indigo-900 font-bold' : ($rank === 3 ? 'bg-indigo-100 text-indigo-800 font-bold' : 'bg-slate-100 text-slate-500 font-medium')) ?> text-xs flex items-center justify-center shrink-0">
                                        <?= $rank++ ?>
                                    </span>
                                    <div>
                                        <a href="<?= url('kelola-pegawai/pelatihan/pegawai/' . $tpl['id']) ?>" class="font-bold text-slate-800 hover:text-indigo-600 text-xs transition-colors">
                                            <?= e($tpl['nama']) ?><?= !empty($tpl['gelar']) ? ', ' . e($tpl['gelar']) : '' ?>
                                        </a>
                                        <p class="text-[11px] text-slate-400"><?= e($tpl['unit_tugas'] ?: 'Yayasan BIP') ?> • <?= e($tpl['total_pelatihan']) ?> Kegiatan</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-extrabold">
                                        <?= $tpl['total_jp'] ?> JP
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js Logic Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart Unit Tugas (Bar)
    const ctxUnit = document.getElementById('chartUnitTugas');
    if (ctxUnit) {
        new Chart(ctxUnit, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartData['unit']['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: <?= json_encode($chartData['unit']['data'] ?? []) ?>,
                    backgroundColor: 'rgba(37, 99, 235, 0.8)',
                    hoverBackgroundColor: 'rgba(29, 78, 216, 1)',
                    borderRadius: 8,
                    maxBarThickness: 45
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
                        ticks: { precision: 0, font: { family: 'Inter', size: 11 } },
                        grid: { color: 'rgba(241, 245, 249, 1)' }
                    },
                    x: {
                        ticks: { font: { family: 'Inter', size: 11 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Chart Status Kerja (Doughnut)
    const ctxStatus = document.getElementById('chartStatusKerja');
    if (ctxStatus) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chartData['status_kerja']['labels'] ?? []) ?>,
                datasets: [{
                    data: <?= json_encode($chartData['status_kerja']['data'] ?? []) ?>,
                    backgroundColor: [
                        '#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Inter', size: 11 }, boxWidth: 12, padding: 12 }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // 3. Chart Masa Kerja (Bar)
    const ctxMasa = document.getElementById('chartMasaKerja');
    if (ctxMasa) {
        new Chart(ctxMasa, {
            type: 'bar',
            data: {
                labels: ['< 1 Thn', '1-3 Thn', '3-5 Thn', '5-10 Thn', '> 10 Thn'],
                datasets: [{
                    label: 'Pegawai',
                    data: <?= json_encode($chartData['masa_kerja'] ?? [0,0,0,0,0]) ?>,
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    hoverBackgroundColor: 'rgba(5, 150, 105, 1)',
                    borderRadius: 8,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { family: 'Inter', size: 10 } },
                        grid: { color: 'rgba(241, 245, 249, 1)' }
                    },
                    x: {
                        ticks: { font: { family: 'Inter', size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 4. Chart Pendidikan Terakhir (Bar)
    const ctxPend = document.getElementById('chartPendidikan');
    if (ctxPend) {
        new Chart(ctxPend, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartData['pendidikan']['labels'] ?? []) ?>,
                datasets: [{
                    label: 'Pegawai',
                    data: <?= json_encode($chartData['pendidikan']['data'] ?? []) ?>,
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                    hoverBackgroundColor: 'rgba(124, 58, 237, 1)',
                    borderRadius: 8,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { family: 'Inter', size: 10 } },
                        grid: { color: 'rgba(241, 245, 249, 1)' }
                    },
                    x: {
                        ticks: { font: { family: 'Inter', size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 5. Chart Usia Pegawai (Bar)
    const ctxUsia = document.getElementById('chartUsia');
    if (ctxUsia) {
        new Chart(ctxUsia, {
            type: 'bar',
            data: {
                labels: ['< 25 Thn', '25-35 Thn', '36-45 Thn', '46-55 Thn', '> 55 Thn'],
                datasets: [{
                    label: 'Pegawai',
                    data: <?= json_encode($chartData['usia'] ?? [0,0,0,0,0]) ?>,
                    backgroundColor: 'rgba(245, 158, 11, 0.8)',
                    hoverBackgroundColor: 'rgba(217, 119, 6, 1)',
                    borderRadius: 8,
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { family: 'Inter', size: 10 } },
                        grid: { color: 'rgba(241, 245, 249, 1)' }
                    },
                    x: {
                        ticks: { font: { family: 'Inter', size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
