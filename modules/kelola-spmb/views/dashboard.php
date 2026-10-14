<?php
/**
 * View Dashboard Modul Kelola SPMB
 * Portal BIP
 */
$group = $stats['activeGroup'] ?? null;
?>

<div class="space-y-6">
    <!-- Top Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-950 via-primary-900 to-sky-950 p-6 sm:p-8 text-white shadow-xl border border-primary-800/80">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-sky-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/4 -top-10 w-60 h-60 rounded-full bg-blue-500/10 blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 border border-sky-400/30 text-sky-300 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Tahun SPMB Aktif: <?= e($group['nama_group'] ?? 'SPMB 2026/2027') ?></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">
                    Sistem Penerimaan Murid Baru (SPMB)
                </h1>
                <p class="text-primary-200/90 text-sm leading-relaxed">
                    Pusat kendali seleksi penerimaan murid baru, verifikasi berkas pendaftaran, mutasi pindahan, dan administrasi formulir SIT Bina Insan Palu.
                </p>
                <div class="flex flex-wrap items-center gap-3 mt-4 text-xs text-primary-300">
                    <span class="inline-flex items-center gap-1.5 bg-primary-900/60 px-3 py-1.5 rounded-xl border border-primary-700/60">
                        <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        TA: <strong class="text-white"><?= e($group['tahun_ajaran'] ?? '2026/2027') ?></strong>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-primary-900/60 px-3 py-1.5 rounded-xl border border-primary-700/60">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        Gelombang: <strong class="text-white"><?= e($group['gelombang_default'] ?? 'Gelombang 1') ?></strong>
                    </span>
                    <span class="inline-flex items-center gap-1.5 bg-primary-900/60 px-3 py-1.5 rounded-xl border border-primary-700/60">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6H2.25m0 0v1.5m0-1.5h1.5m16.5 0h.75a.75.75 0 0 1 .75.75v.75m0 0H21m0 0v1.5m0-1.5h-1.5m-16.5 6h1.5m0 0v1.5m0-1.5H3m18 0h-1.5m0 0v1.5m0-1.5H21" /></svg>
                        Baru: <strong class="text-white">Rp <?= number_format($group['biaya_formulir'] ?? 0, 0, ',', '.') ?></strong>
                        <span class="text-white/40">|</span>
                        Pindahan: <strong class="text-amber-300">Rp <?= number_format($group['biaya_pindahan'] ?? 0, 0, ',', '.') ?></strong>
                    </span>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-2.5 shrink-0">
                <a href="<?= url('kelola-spmb/siswa-baru/create') ?>" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white font-semibold text-xs shadow-lg shadow-sky-500/25 transition-all transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Siswa Baru</span>
                </a>
                <a href="<?= url('kelola-spmb/pindahan/create') ?>" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 backdrop-blur-sm transition-all transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                    <span>Pindahan</span>
                </a>
                <a href="<?= url('kelola-spmb/daftar-tunggu/create') ?>" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 hover:text-white font-semibold text-xs border border-amber-400/30 backdrop-blur-sm transition-all transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <span>Daftar Tunggu</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Main KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Pendaftar -->
        <div class="bg-white rounded-2xl p-5 border border-primary-100/70 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Pendaftar</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['totalPendaftar']) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Group: <strong class="text-slate-700"><?= e($group['nama_group'] ?? 'Aktif') ?></strong></span>
                <span class="text-sky-600 font-semibold">Semua Jenjang</span>
            </div>
        </div>

        <!-- 2. Siswa Baru -->
        <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="group bg-white rounded-2xl p-5 border border-primary-100/70 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Siswa Baru</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['totalSiswaBaru']) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Pendaftar Baru Masuk</span>
                <span class="text-emerald-600 font-semibold group-hover:translate-x-0.5 transition-transform">Lihat Data &rarr;</span>
            </div>
        </a>

        <!-- 3. Pindahan -->
        <a href="<?= url('kelola-spmb/pindahan') ?>" class="group bg-white rounded-2xl p-5 border border-primary-100/70 shadow-sm hover:shadow-md hover:border-blue-200 transition-all block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Pindahan (Mutasi)</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-blue-600 mt-1"><?= number_format($stats['totalPindahan']) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Siswa Masuk Antar-Sekolah</span>
                <span class="text-blue-600 font-semibold group-hover:translate-x-0.5 transition-transform">Lihat Data &rarr;</span>
            </div>
        </a>

        <!-- 4. Daftar Tunggu -->
        <a href="<?= url('kelola-spmb/daftar-tunggu') ?>" class="group bg-white rounded-2xl p-5 border border-primary-100/70 shadow-sm hover:shadow-md hover:border-amber-200 transition-all block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Daftar Tunggu</p>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-1"><?= number_format($stats['totalDaftarTunggu'] ?? 0) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Rekomendasi / Waiting List</span>
                <span class="text-amber-600 font-semibold group-hover:translate-x-0.5 transition-transform">Lihat Data &rarr;</span>
            </div>
        </a>
    </div>

    <!-- Secondary Status Pills: Kategori Pendaftar & Administrasi -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 flex items-center gap-3.5 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm shrink-0">
                <span class="text-lg">👨‍👩‍👦</span>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Keluarga BIP</p>
                <h4 class="text-lg font-bold text-purple-900"><?= number_format($stats['totalKeluarga'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 flex items-center gap-3.5 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm shrink-0">
                <span class="text-lg">🌐</span>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Umum (Luar BIP)</p>
                <h4 class="text-lg font-bold text-sky-900"><?= number_format($stats['totalUmum'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 flex items-center gap-3.5 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Biodata Lengkap</p>
                <h4 class="text-lg font-bold text-emerald-900"><?= number_format($stats['totalBiodataLengkap'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 flex items-center gap-3.5 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 2.25 19.5Z" /></svg>
            </div>
            <div>
                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">Biaya Lunas</p>
                <h4 class="text-lg font-bold text-slate-800"><?= number_format($stats['totalLunas']) ?> <span class="text-xs font-normal text-slate-400">/ <?= number_format($stats['totalPendaftar']) ?></span></h4>
            </div>
        </div>
    </div>

    <!-- Kuota Jenjang & Visual Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kuota per Jenjang -->
        <div class="bg-white rounded-2xl p-6 border border-primary-100/70 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Progress Kuota Pendaftar</h3>
                    <p class="text-xs text-slate-500">Kapasitas kursi per jenjang pendidikan</p>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 border border-sky-100">
                    <?= e($group['nama_group'] ?? 'SPMB') ?>
                </span>
            </div>

            <div class="space-y-4">
                <?php
                $jenjangConfigs = [
                    'PAUD' => ['label' => 'PAUD', 'kuota' => (int)($group['kuota_paud'] ?? 50), 'color' => 'bg-emerald-500'],
                    'SD'   => ['label' => 'SD',   'kuota' => (int)($group['kuota_sd'] ?? 100),   'color' => 'bg-blue-500'],
                    'SMP'  => ['label' => 'SMP',  'kuota' => (int)($group['kuota_smp'] ?? 100),  'color' => 'bg-indigo-500'],
                    'SMA'  => ['label' => 'SMA',  'kuota' => (int)($group['kuota_sma'] ?? 100),  'color' => 'bg-purple-500'],
                ];
                foreach ($jenjangConfigs as $k => $cfg):
                    $terisi = $stats['jenjangStats'][$k] ?? 0;
                    $pct = $cfg['kuota'] > 0 ? min(100, round(($terisi / $cfg['kuota']) * 100)) : 0;
                ?>
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-semibold text-slate-700"><?= $cfg['label'] ?></span>
                            <span class="text-slate-500">
                                <strong class="text-slate-800"><?= $terisi ?></strong> / <?= $cfg['kuota'] ?> kursi (<?= $pct ?>%)
                            </span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full <?= $cfg['color'] ?> rounded-full transition-all duration-500" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">Ingin mengubah kuota?</span>
                <a href="<?= url('kelola-spmb/pengaturan') ?>" class="text-sky-600 font-bold hover:underline">
                    Edit di Pengaturan &rarr;
                </a>
            </div>
        </div>

        <!-- Chart / Distribusi Pendaftar -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-primary-100/70 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Distribusi Pendaftar per Jenjang & Kategori</h3>
                    <p class="text-xs text-slate-500">Visualisasi calon peserta didik baru yang terdaftar</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                <div class="h-56 relative flex items-center justify-center">
                    <canvas id="chartJenjangSpmb"></canvas>
                </div>
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori Pendaftar:</h4>
                    <?php if (empty($stats['jalurStats'])): ?>
                        <p class="text-xs text-slate-400 italic">Belum ada data pendaftar pada group ini.</p>
                    <?php else: ?>
                        <div class="space-y-2">
                            <?php foreach ($stats['jalurStats'] as $j): ?>
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                    <span class="font-semibold text-slate-700"><?= e($j['jalur_pendaftaran'] ?: 'Reguler') ?></span>
                                    <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-800 font-bold">
                                        <?= $j['jumlah'] ?> siswa
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-right">
                <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="text-xs font-bold text-sky-600 hover:text-sky-700">
                    Buka Kelola Pendaftar &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Tabel Pendaftar Terbaru -->
    <div class="bg-white rounded-2xl border border-primary-100/70 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 text-base">Pendaftar Terbaru</h3>
                <p class="text-xs text-slate-500">7 calon siswa yang baru mendaftar pada group aktif</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="text-xs font-bold px-3 py-1.5 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 transition-colors">
                    Semua Siswa Baru &rarr;
                </a>
                <a href="<?= url('kelola-spmb/pindahan') ?>" class="text-xs font-bold px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                    Semua Pindahan &rarr;
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4">No. Pendaftaran</th>
                        <th class="py-3 px-4">Nama Calon Siswa</th>
                        <th class="py-3 px-4">Jenis</th>
                        <th class="py-3 px-4">Jenjang</th>
                        <th class="py-3 px-4">Tanggal Daftar</th>
                        <th class="py-3 px-4">Kategori Pendaftar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($stats['recentPendaftar'])): ?>
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-400 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                                    </div>
                                    <p class="font-semibold text-slate-700">Belum ada data pendaftar</p>
                                    <p class="text-[11px] text-slate-400">Silakan tambahkan data calon siswa baru atau pindahan pertama.</p>
                                    <a href="<?= url('kelola-spmb/siswa-baru/create') ?>" class="inline-block mt-2 px-3 py-1.5 rounded-xl bg-sky-600 text-white font-bold text-xs hover:bg-sky-500">
                                        Tambah Pendaftar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stats['recentPendaftar'] as $row): 
                            $isPindah = ($row['jenis_pendaftaran'] === 'Pindahan');
                            $detailUrl = $isPindah ? url('kelola-spmb/pindahan/detail/' . $row['id']) : url('kelola-spmb/siswa-baru/detail/' . $row['id']);
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-sky-700">
                                    <a href="<?= $detailUrl ?>" class="hover:underline">
                                        <?= e($row['nomor_pendaftaran']) ?>
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800"><?= e($row['nama_lengkap']) ?></div>
                                    <div class="text-[11px] text-slate-400">NISN: <?= e($row['nisn'] ?: '-') ?> &bull; <?= e($row['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan') ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $isPindah ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' ?>">
                                        <?= e($row['jenis_pendaftaran']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700">
                                    <?= e($row['jenjang']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    <?= date('d/m/Y', strtotime($row['tanggal_daftar'])) ?>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <?php 
                                    $kat = $row['kategori_pendaftar'] ?: ($row['jalur_pendaftaran'] ?: 'Umum (Luar Bina Insan)');
                                    $isKeluarga = str_contains(strtolower($kat), 'keluarga') || str_contains(strtolower($kat), 'adik') || str_contains(strtolower($kat), 'saudara');
                                    $isYayasan = str_contains(strtolower($kat), 'yayasan');
                                    $isDinas = str_contains(strtolower($kat), 'dinas');
                                    ?>
                                    <?php if ($isKeluarga): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 shadow-sm">
                                            <span>👨‍👩‍👦</span>
                                            <span>Keluarga Bina Insan</span>
                                        </span>
                                    <?php elseif ($isYayasan): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200 shadow-sm">
                                            <span>🏢</span>
                                            <span>Yayasan</span>
                                        </span>
                                    <?php elseif ($isDinas): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                            <span>🏛️</span>
                                            <span>Dinas</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200 shadow-sm">
                                            <span>🌐</span>
                                            <span>Umum</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="<?= $detailUrl ?>" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition-colors">
                                        Detail
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

<!-- Chart.js Script for SPMB -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('chartJenjangSpmb');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['PAUD', 'SD', 'SMP', 'SMA'],
                datasets: [{
                    data: [
                        <?= (int)($stats['jenjangStats']['PAUD'] ?? 0) ?>,
                        <?= (int)($stats['jenjangStats']['SD'] ?? 0) ?>,
                        <?= (int)($stats['jenjangStats']['SMP'] ?? 0) ?>,
                        <?= (int)($stats['jenjangStats']['SMA'] ?? 0) ?>
                    ],
                    backgroundColor: ['#10b981', '#3b82f6', '#6366f1', '#a855f7'],
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
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { family: 'Inter', size: 11 }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
