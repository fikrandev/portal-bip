<?php
/**
 * View: Kelola Jurnal Guru (5 Menu Terpadu)
 * 1. Dashboard Jurnal
 * 2. Data Jurnal (Nama, Kegiatan, Kelas, Mapel, Materi, Presensi - sesuai Portal Guru)
 * 3. Rekap Jurnal (Nama Guru, Jumlah PIC, Sesi Terisi & JP)
 * 4. Data Rekap Harian Matrix (✓ Centang / ✕ Silang per Tanggal)
 * 5. Rekap Jurnal Tidak Terisi
 */
$activeTab = $activeTab ?? ($_GET['tab'] ?? 'dashboard');
$canManage = RBAC::hasPermission('jurnal.manage') || RBAC::canAccessModule('kelola-jurnal-guru') || Auth::isSuperAdmin();
$canDelete = RBAC::hasPermission('jurnal.delete') || RBAC::canAccessModule('kelola-jurnal-guru') || Auth::isSuperAdmin();

$cetakQuery = http_build_query([
    'search'     => $search ?? '',
    'guru_id'    => $guruId ?? '',
    'kelas'      => $kelas ?? '',
    'status'     => $status ?? '',
    'start_date' => $periodStart ?? date('Y-m-01'),
    'end_date'   => $periodEnd ?? date('Y-m-d'),
]);
?>

<div class="space-y-6">

    <!-- ===================================================================== -->
    <!-- HEADER & TOP NAVIGATION TABS                                          -->
    <!-- ===================================================================== -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 border border-primary-100 text-primary-700 text-xs font-bold mb-2">
                    <span class="w-2 h-2 rounded-full bg-primary-600 animate-pulse"></span>
                    Sinkronisasi Real-Time dengan Portal Guru
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Kelola Jurnal Mengajar & Monitoring PIC Guru
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Pantau aktivitas KBM harian, rekap beban PIC & Jam Pelajaran (JP), matriks kehadiran jurnal, serta deteksi jurnal yang belum diisi.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <?php if ($canManage): ?>
                <button type="button"
                        onclick="openInputJurnalModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-emerald-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>Input Jurnal Manual</span>
                </button>
                <?php endif; ?>

                <a href="<?= url('kelola-jurnal-guru/cetak?' . $cetakQuery) ?>"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-primary-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.376 1.942-5.908 5.28-5.908 3.337 0 5.52 2.532 5.28 5.908m-10.56 0A5.967 5.967 0 0 0 12 18.25c2.476 0 4.636-1.503 5.28-4.421m-10.56 0c0-1.077.29-2.09.794-2.964m9.766 2.964a5.986 5.986 0 0 0-.794-2.964M6.72 13.829A7.95 7.95 0 0 1 12 11.25a7.95 7.95 0 0 1 5.28 2.579M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v1.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 10.5v-1.5A2.25 2.25 0 0 1 6.75 6.75Z"/>
                    </svg>
                    <span>Cetak Rekap PDF</span>
                </a>
            </div>
        </div>

        <!-- 5 Navigation Tabs -->
        <div class="pt-4 flex items-center gap-2 overflow-x-auto no-scrollbar">
            <button type="button"
                    onclick="switchJurnalTab('dashboard')"
                    data-jurnal-tab-btn="dashboard"
                    class="jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'dashboard' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' ?>">
                <span>📊</span>
                <span>1. Dashboard</span>
            </button>

            <button type="button"
                    onclick="switchJurnalTab('data')"
                    data-jurnal-tab-btn="data"
                    class="jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'data' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' ?>">
                <span>📖</span>
                <span>2. Data Jurnal</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full bg-black/15 font-extrabold"><?= number_format((int)($kpiFiltered['total_count'] ?? 0)) ?></span>
            </button>

            <button type="button"
                    onclick="switchJurnalTab('rekap')"
                    data-jurnal-tab-btn="rekap"
                    class="jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'rekap' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' ?>">
                <span>👨‍🏫</span>
                <span>3. Rekap Jurnal (PIC)</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full bg-black/15 font-extrabold"><?= count($rekapGuruList) ?> Guru</span>
            </button>

            <button type="button"
                    onclick="switchJurnalTab('matrix')"
                    data-jurnal-tab-btn="matrix"
                    class="jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'matrix' ? 'bg-primary-600 text-white shadow-md shadow-primary-600/20' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80' ?>">
                <span>🗓️</span>
                <span>4. Rekap Harian Matrix (✓/✕)</span>
            </button>

            <button type="button"
                    onclick="switchJurnalTab('kosong')"
                    data-jurnal-tab-btn="kosong"
                    class="jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all <?= $activeTab === 'kosong' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/20' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200/70' ?>">
                <span>⚠️</span>
                <span>5. Jurnal Tidak Terisi</span>
                <span class="px-2 py-0.5 text-[10px] rounded-full bg-rose-600 text-white font-extrabold"><?= number_format($unfilledTotalCount) ?></span>
            </button>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- GLOBAL FILTER BAR (Berlaku untuk Data Jurnal, Rekap PIC, Matrix, dll) -->
    <!-- ===================================================================== -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5">
        <form method="GET" action="<?= url('kelola-jurnal-guru') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <input type="hidden" name="tab" id="filter_active_tab" value="<?= e($activeTab) ?>">

            <!-- Cari Kata Kunci -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Pencarian Cepat</label>
                <div class="relative">
                    <input type="text"
                           name="search"
                           value="<?= e($search ?? '') ?>"
                           placeholder="Cari nama guru, materi, kegiatan, kelas, mapel..."
                           class="w-full pl-9 pr-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                </div>
            </div>

            <!-- Filter Guru -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Filter Guru (PIC)</label>
                <select name="guru_id" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 focus:border-primary-500 outline-none bg-white">
                    <option value="">— Semua Guru —</option>
                    <?php foreach ($guruOptions as $g): ?>
                        <option value="<?= (int)$g['id'] ?>" <?= ($guruId == $g['id']) ? 'selected' : '' ?>>
                            <?= e($g['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Kelas -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Filter Kelas</label>
                <select name="kelas" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 focus:border-primary-500 outline-none bg-white">
                    <option value="">— Semua Kelas —</option>
                    <?php foreach ($kelasOptions as $klsOpt): ?>
                        <option value="<?= e($klsOpt) ?>" <?= ($kelas === $klsOpt) ? 'selected' : '' ?>>
                            <?= e($klsOpt) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dari Tanggal -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Dari Tanggal</label>
                <input type="date"
                       name="start_date"
                       value="<?= e($startDate !== '' ? $startDate : $periodStart) ?>"
                       class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 focus:border-primary-500 outline-none">
            </div>

            <!-- Sampai Tanggal & Tombol Filter -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date"
                           name="end_date"
                           value="<?= e($endDate !== '' ? $endDate : $periodEnd) ?>"
                           class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 focus:border-primary-500 outline-none">
                </div>
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm transition-all shrink-0">
                    Terapkan
                </button>
                <?php if ($search !== '' || $guruId !== null || $kelas !== '' || $startDate !== '' || $endDate !== ''): ?>
                <a href="<?= url('kelola-jurnal-guru?tab=' . urlencode($activeTab)) ?>"
                   class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs sm:text-sm rounded-xl transition-all shrink-0"
                   title="Reset Filter">
                    Reset
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: DASHBOARD JURNAL GURU                                          -->
    <!-- ===================================================================== -->
    <div id="tab-panel-dashboard" class="jurnal-tab-panel space-y-6 <?= $activeTab === 'dashboard' ? '' : 'hidden' ?>">

        <!-- 4 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- KPI 1: Jurnal Hari Ini -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Jurnal Hari Ini</span>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                        <?= number_format((int)($kpiToday['count_today'] ?? 0)) ?> <span class="text-sm font-bold text-slate-400">Sesi</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 font-medium">
                        Terlaksana <strong class="text-emerald-700"><?= number_format((int)($kpiToday['jp_today'] ?? 0)) ?> JP</strong> oleh <?= number_format((int)($kpiToday['guru_today'] ?? 0)) ?> Guru
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold shrink-0">
                    ✓
                </div>
            </div>

            <!-- KPI 2: Akumulasi Bulan Ini -->
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-primary-600 uppercase tracking-wider">Total Bulan Ini</span>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                        <?= number_format((int)($kpiMonth['count_month'] ?? 0)) ?> <span class="text-sm font-bold text-slate-400">Jurnal</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 font-medium">
                        Akumulasi Beban: <strong class="text-primary-700"><?= number_format((int)($kpiMonth['jp_month'] ?? 0)) ?> JP</strong>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl font-bold shrink-0">
                    📚
                </div>
            </div>

            <!-- KPI 3: Total Guru & PIC Terdata -->
            <div onclick="switchJurnalTab('rekap')" class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between cursor-pointer hover:border-indigo-300 transition-all">
                <div>
                    <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Rekap Guru & PIC</span>
                    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                        <?= count($rekapGuruList) ?> <span class="text-sm font-bold text-slate-400">Guru</span>
                    </div>
                    <p class="text-xs text-indigo-600 mt-1 font-bold">
                        Lihat Rekap Jumlah PIC &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold shrink-0">
                    👨‍🏫
                </div>
            </div>

            <!-- KPI 4: Jurnal Tidak Terisi -->
            <div onclick="switchJurnalTab('kosong')" class="bg-white rounded-3xl p-5 border border-rose-200/80 shadow-sm flex items-center justify-between cursor-pointer hover:bg-rose-50/40 transition-all">
                <div>
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Jurnal Tidak Terisi</span>
                    <div class="text-2xl sm:text-3xl font-black text-rose-600 mt-1">
                        <?= number_format($unfilledTotalCount) ?> <span class="text-sm font-bold text-rose-400">Sesi ✕</span>
                    </div>
                    <p class="text-xs text-rose-600 mt-1 font-bold">
                        Klik untuk tindak lanjut &rarr;
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl font-bold shrink-0">
                    ✕
                </div>
            </div>
        </div>

        <!-- Tren 7 Hari Terakhir & Top Guru PIC -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tren 7 Hari -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Tren Pengisian Jurnal (7 Hari Terakhir)</h3>
                        <p class="text-xs text-slate-500">Perbandingan jumlah sesi jurnal dan Jam Pelajaran (JP) harian</p>
                    </div>
                    <button type="button" onclick="switchJurnalTab('matrix')" class="text-xs font-bold text-primary-600 hover:underline">
                        Buka Matrix Harian &rarr;
                    </button>
                </div>

                <?php
                $maxTrendCount = 1;
                foreach ($trend7Days as $t) {
                    if ($t['count'] > $maxTrendCount) $maxTrendCount = $t['count'];
                }
                ?>
                <div class="grid grid-cols-7 gap-2 sm:gap-3 pt-4 items-end h-44 border-b border-slate-100 pb-3">
                    <?php foreach ($trend7Days as $t): ?>
                        <?php $barHeightPct = max(8, (int)round(($t['count'] / $maxTrendCount) * 100)); ?>
                        <div class="flex flex-col items-center justify-end h-full group">
                            <span class="text-[10px] font-extrabold text-slate-700 mb-1"><?= $t['count'] ?> sesi</span>
                            <div class="w-full max-w-[40px] rounded-t-xl transition-all <?= $t['count'] > 0 ? 'bg-gradient-to-t from-primary-600 to-sky-400 group-hover:from-primary-700 group-hover:to-sky-500' : 'bg-slate-100' ?>"
                                 style="height: <?= $barHeightPct ?>%;"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="grid grid-cols-7 gap-2 sm:gap-3 pt-2 text-center">
                    <?php foreach ($trend7Days as $t): ?>
                        <div>
                            <p class="text-[11px] font-bold text-slate-700"><?= e($t['label']) ?></p>
                            <p class="text-[10px] text-slate-400"><?= $t['jp'] ?> JP</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Top 5 Guru PIC & Kepatuhan -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Top Keaktifan Guru (PIC)</h3>
                        <p class="text-xs text-slate-500">Periode <?= date('d/m/Y', strtotime($periodStart)) ?> - <?= date('d/m/Y', strtotime($periodEnd)) ?></p>
                    </div>
                    <button type="button" onclick="switchJurnalTab('rekap')" class="text-xs font-bold text-primary-600 hover:underline">
                        Semua &rarr;
                    </button>
                </div>

                <div class="space-y-3">
                    <?php foreach (array_slice($rekapGuruList, 0, 5) as $idx => $rg): ?>
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-7 h-7 rounded-xl bg-primary-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                                    <?= $idx + 1 ?>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 truncate"><?= e($rg['nama']) ?></p>
                                    <p class="text-[10px] text-slate-500">
                                        PIC: <strong><?= $rg['jumlah_pic'] ?> Kelas/Mapel</strong> &bull; <?= $rg['total_jp'] ?> JP
                                    </p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 shrink-0">
                                <?= $rg['terisi_sesi'] ?> Jurnal
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Ringkasan 5 Menu Cepat -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div onclick="switchJurnalTab('data')" class="bg-gradient-to-br from-primary-600 to-indigo-700 rounded-3xl p-5 text-white shadow-lg cursor-pointer hover:opacity-95 transition-all">
                <div class="text-2xl mb-2">📖</div>
                <h4 class="font-black text-base">Data Jurnal Lengkap</h4>
                <p class="text-xs text-primary-100 mt-1">Lihat detail Nama Guru, Kelas, Materi Pokok, Uraian Kegiatan, dan Presensi Siswa hasil input dari Portal Guru.</p>
            </div>
            <div onclick="switchJurnalTab('matrix')" class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-5 text-white shadow-lg cursor-pointer hover:opacity-95 transition-all">
                <div class="text-2xl mb-2">🗓️</div>
                <h4 class="font-black text-base">Matrix Harian (✓ / ✕)</h4>
                <p class="text-xs text-emerald-100 mt-1">Tabel matriks per tanggal dengan indikator Centang (✓) jika jurnal terisi dan Silang (✕) jika belum diisi.</p>
            </div>
            <div onclick="switchJurnalTab('kosong')" class="bg-gradient-to-br from-rose-600 to-orange-600 rounded-3xl p-5 text-white shadow-lg cursor-pointer hover:opacity-95 transition-all">
                <div class="text-2xl mb-2">⚠️</div>
                <h4 class="font-black text-base">Jurnal Tidak Terisi (<?= $unfilledTotalCount ?>)</h4>
                <p class="text-xs text-rose-100 mt-1">Daftar sesi mengajar yang terlewat belum diisi jurnal, lengkap dengan tombol pengingat WhatsApp & isi manual.</p>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 2: DATA JURNAL (Nama, Kegiatan, Kelas, Materi, Presensi)          -->
    <!-- ===================================================================== -->
    <div id="tab-panel-data" class="jurnal-tab-panel space-y-4 <?= $activeTab === 'data' ? '' : 'hidden' ?>">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="font-black text-slate-900 text-base">2. Data Jurnal Mengajar Guru</h3>
                    <p class="text-xs text-slate-500">Data lengkap sesuai field pengisian jurnal di Portal Guru (Nama Guru, Kelas, Mapel, Materi Pokok, Uraian Kegiatan & Presensi Siswa)</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary-50 text-primary-700 border border-primary-100 self-start sm:self-auto">
                    Total: <?= number_format($totalRows) ?> Data Jurnal (<?= number_format((int)($kpiFiltered['total_jp'] ?? 0)) ?> JP)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">No</th>
                            <th class="py-3.5 px-4">Tanggal & Jam</th>
                            <th class="py-3.5 px-4">Nama Guru (PIC)</th>
                            <th class="py-3.5 px-4">Kelas & Mata Pelajaran</th>
                            <th class="py-3.5 px-4">Materi Pokok & Uraian Kegiatan</th>
                            <th class="py-3.5 px-4">Presensi Siswa</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        <?php if (!empty($jurnalList)): ?>
                            <?php foreach ($jurnalList as $idx => $item): ?>
                                <?php
                                $rowNo = $offset + $idx + 1;
                                $st = $item['status'] ?? 'selesai';
                                $statusBadge = [
                                    'tervalidasi' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'selesai'     => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'draft'       => 'bg-amber-50 text-amber-700 border-amber-200',
                                ][$st] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-colors align-top">
                                    <td class="py-3.5 px-4 font-bold text-slate-400"><?= $rowNo ?></td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800"><?= date('d M Y', strtotime($item['tanggal'])) ?></div>
                                        <div class="text-[11px] text-slate-500">
                                            Jam ke-<?= e($item['jam_ke'] ?? '1') ?> &bull; <strong class="text-primary-700"><?= (int)($item['jumlah_jp'] ?? 2) ?> JP</strong>
                                        </div>
                                        <div class="text-[10px] font-mono text-slate-400 mt-0.5"><?= e($item['kode_jurnal'] ?? '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900"><?= e($item['guru_nama_lengkap']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">
                                            NIY: <?= e($item['niy'] ?: ($item['nik'] ?: '-')) ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-block px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs border border-indigo-100">
                                            <?= e($item['kelas'] ?? '-') ?>
                                        </span>
                                        <div class="font-semibold text-slate-800 mt-1"><?= e($item['mata_pelajaran'] ?? '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-md">
                                        <div class="font-bold text-slate-900 leading-snug"><?= e($item['materi_pokok'] ?? '-') ?></div>
                                        <?php if (!empty($item['uraian_kegiatan'])): ?>
                                            <p class="text-xs text-slate-600 mt-1 line-clamp-2 leading-relaxed">
                                                <span class="font-semibold text-slate-500">Kegiatan:</span> <?= e($item['uraian_kegiatan']) ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($item['catatan_kelas'])): ?>
                                            <p class="text-[11px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md mt-1 inline-block border border-amber-100">
                                                Catatan: <?= e($item['catatan_kelas']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                            👥 <?= e($item['kehadiran_summary'] ?: 'Semua Siswa Hadir') ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $statusBadge ?>">
                                            <?= ucfirst(e($st)) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button"
                                                    onclick="openDetailJurnalModal(<?= (int)$item['id'] ?>)"
                                                    class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all"
                                                    title="Lihat Detail">
                                                Detail
                                            </button>

                                            <?php if ($canManage && $st !== 'tervalidasi'): ?>
                                                <form method="POST" action="<?= url('kelola-jurnal-guru/status/' . (int)$item['id']) ?>" class="inline">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="status" value="tervalidasi">
                                                    <input type="hidden" name="return_tab" value="data">
                                                    <button type="submit"
                                                            class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-all"
                                                            title="Validasi Jurnal">
                                                        ✓ Validasi
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($canDelete): ?>
                                                <form method="POST"
                                                      action="<?= url('kelola-jurnal-guru/delete/' . (int)$item['id']) ?>"
                                                      onsubmit="return confirm('Hapus data jurnal mengajar ini?');"
                                                      class="inline">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="return_tab" value="data">
                                                    <button type="submit"
                                                            class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition-all"
                                                            title="Hapus Jurnal">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2">📭</div>
                                    <p class="font-bold text-slate-600 text-sm">Belum ada data jurnal mengajar pada filter ini.</p>
                                    <p class="text-xs mt-1">Jurnal yang diinput guru melalui Portal Guru akan otomatis tampil di sini.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Halaman <strong><?= $page ?></strong> dari <strong><?= $totalPages ?></strong></span>
                    <div class="flex items-center gap-1.5">
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a href="<?= url('kelola-jurnal-guru?' . http_build_query(array_merge($_GET, ['tab' => 'data', 'page' => $p]))) ?>"
                               class="px-3 py-1.5 rounded-xl font-bold <?= $p === $page ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: REKAP JURNAL (Nama Guru, Jumlah PIC, Terisi, JP)               -->
    <!-- ===================================================================== -->
    <div id="tab-panel-rekap" class="jurnal-tab-panel space-y-4 <?= $activeTab === 'rekap' ? '' : 'hidden' ?>">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="font-black text-slate-900 text-base">3. Rekapitulasi Jurnal Guru (Nama & Jumlah PIC)</h3>
                    <p class="text-xs text-slate-500">
                        Periode: <strong><?= date('d M Y', strtotime($periodStart)) ?></strong> s/d <strong><?= date('d M Y', strtotime($periodEnd)) ?></strong>
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    Total <?= count($rekapGuruList) ?> Guru Terdata
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">No</th>
                            <th class="py-3.5 px-4">Nama Guru</th>
                            <th class="py-3.5 px-4 text-center">Jumlah PIC (Kelas/Mapel)</th>
                            <th class="py-3.5 px-4">Rincian Kelas & Mapel PIC</th>
                            <th class="py-3.5 px-4 text-center">Jurnal Terisi (✓)</th>
                            <th class="py-3.5 px-4 text-center">Tidak Terisi (✕)</th>
                            <th class="py-3.5 px-4 text-center">Total JP</th>
                            <th class="py-3.5 px-4">Kepatuhan</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        <?php if (!empty($rekapGuruList)): ?>
                            <?php foreach ($rekapGuruList as $idx => $rg): ?>
                                <?php
                                $pct = (int)$rg['persentase'];
                                $barColor = $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-rose-500');
                                ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-slate-400"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900"><?= e($rg['nama']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">NIY: <?= e($rg['niy']) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            👨‍🏫 <?= (int)$rg['jumlah_pic'] ?> PIC
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <?php if (!empty($rg['pic_classes'])): ?>
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach (array_slice($rg['pic_classes'], 0, 5) as $klsBadge): ?>
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                                        <?= e($klsBadge) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                                <?php if (count($rg['pic_classes']) > 5): ?>
                                                    <span class="text-[10px] text-slate-400 font-bold">+<?= count($rg['pic_classes']) - 5 ?> lainnya</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-400 italic">Belum ada kelas terjadwal</span>
                                        <?php endif; ?>
                                        <?php if (!empty($rg['pic_subjects'])): ?>
                                            <div class="text-[11px] text-slate-500 mt-1 truncate">
                                                <?= e(implode(', ', $rg['pic_subjects'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-xl font-extrabold text-xs bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ✓ <?= (int)$rg['terisi_sesi'] ?> Sesi
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ($rg['kosong_sesi'] > 0): ?>
                                            <span class="px-2.5 py-1 rounded-xl font-extrabold text-xs bg-rose-50 text-rose-700 border border-rose-200">
                                                ✕ <?= (int)$rg['kosong_sesi'] ?> Sesi
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-slate-400">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="font-black text-primary-700"><?= (int)$rg['total_jp'] ?> JP</span>
                                    </td>
                                    <td class="py-3.5 px-4 min-w-[130px]">
                                        <div class="flex items-center justify-between text-[11px] font-bold mb-1">
                                            <span class="text-slate-700"><?= $pct ?>%</span>
                                            <span class="text-slate-400"><?= $rg['terisi_sesi'] ?>/<?= $rg['target_sesi'] ?></span>
                                        </div>
                                        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full <?= $barColor ?>" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <?php if (!empty($rg['guru_id'])): ?>
                                            <a href="<?= url('kelola-jurnal-guru?tab=data&guru_id=' . (int)$rg['guru_id'] . '&start_date=' . urlencode($periodStart) . '&end_date=' . urlencode($periodEnd)) ?>"
                                               class="px-3 py-1.5 rounded-xl bg-primary-50 hover:bg-primary-100 text-primary-700 font-bold text-xs transition-all">
                                                Lihat Jurnal
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-400">Belum ada data guru untuk direkap.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 4: DATA REKAP HARIAN MATRIX (✓ Centang / ✕ Silang per Tanggal)    -->
    <!-- ===================================================================== -->
    <div id="tab-panel-matrix" class="jurnal-tab-panel space-y-4 <?= $activeTab === 'matrix' ? '' : 'hidden' ?>">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div>
                    <h3 class="font-black text-slate-900 text-base">4. Data Rekap Harian Matrix (✓ Terisi / ✕ Tidak Diisi)</h3>
                    <p class="text-xs text-slate-500">
                        Menampilkan matriks pengisian jurnal harian dari tanggal <strong><?= date('d M Y', strtotime($matrixDates[0]['date'] ?? $periodStart)) ?></strong> s/d <strong><?= date('d M Y', strtotime(end($matrixDates)['date'] ?? $periodEnd)) ?></strong>
                    </p>
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap items-center gap-3 text-xs font-bold">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-4 h-4 rounded-md bg-emerald-500 text-white flex items-center justify-center text-[10px]">✓</span>
                        Sudah Isi Jurnal
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-4 h-4 rounded-md bg-amber-500 text-white flex items-center justify-center text-[10px]">✓</span>
                        Terisi Sebagian
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-200">
                        <span class="w-4 h-4 rounded-md bg-rose-500 text-white flex items-center justify-center text-[10px]">✕</span>
                        Tidak Diisi
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-500">
                        <span class="w-4 h-4 rounded-md bg-slate-200 text-slate-500 flex items-center justify-center text-[10px]">-</span>
                        Libur / Tanpa Jadwal
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold text-slate-600 uppercase">
                            <th class="py-3 px-3 sticky left-0 bg-slate-50 z-10 border-r border-slate-200 min-w-[190px]">Nama Guru (PIC)</th>
                            <th class="py-3 px-2 text-center border-r border-slate-200">PIC</th>
                            <?php foreach ($matrixDates as $md): ?>
                                <th class="py-2 px-1.5 text-center min-w-[38px] border-r border-slate-100 <?= $md['is_sunday'] ? 'bg-rose-50/60 text-rose-600' : ($md['is_today'] ? 'bg-primary-50 text-primary-700' : '') ?>">
                                    <div class="text-[9px] font-bold opacity-75"><?= e($md['hari_short']) ?></div>
                                    <div class="text-xs font-black"><?= e($md['day_num']) ?></div>
                                    <div class="text-[8px] font-normal opacity-70"><?= e($md['month_short']) ?></div>
                                </th>
                            <?php endforeach; ?>
                            <th class="py-3 px-2.5 text-center bg-emerald-50/60 text-emerald-700 border-l border-slate-200">Total ✓</th>
                            <th class="py-3 px-2.5 text-center bg-rose-50/60 text-rose-700">Total ✕</th>
                            <th class="py-3 px-2.5 text-center bg-primary-50/60 text-primary-700">JP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php foreach ($matrixRows as $mRow): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-2.5 px-3 sticky left-0 bg-white z-10 border-r border-slate-200 font-bold text-slate-900">
                                    <div class="truncate max-w-[180px]" title="<?= e($mRow['nama']) ?>"><?= e($mRow['nama']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-normal truncate max-w-[180px]">
                                        <?= !empty($mRow['pic_classes']) ? e(implode(', ', array_slice($mRow['pic_classes'], 0, 3))) : 'Tenaga Pendidik' ?>
                                    </div>
                                </td>
                                <td class="py-2.5 px-2 text-center border-r border-slate-200 font-bold text-indigo-700">
                                    <?= (int)$mRow['jumlah_pic'] ?>
                                </td>

                                <?php foreach ($matrixDates as $md): ?>
                                    <?php
                                    $dStr = $md['date'];
                                    $cell = $mRow['dates'][$dStr] ?? ['status' => 'off', 'filled_count' => 0, 'jp' => 0, 'details' => ''];
                                    $st = $cell['status'];
                                    ?>
                                    <td class="py-2 px-1 text-center border-r border-slate-100 <?= $md['is_sunday'] ? 'bg-rose-50/20' : '' ?>">
                                        <?php if ($st === 'filled'): ?>
                                            <div class="w-7 h-7 mx-auto rounded-lg bg-emerald-500 text-white font-black text-xs flex items-center justify-center shadow-2xs cursor-pointer"
                                                 title="<?= e($dStr . ' — Terisi ' . $cell['filled_count'] . ' Sesi (' . $cell['jp'] . ' JP): ' . $cell['details']) ?>">
                                                ✓
                                            </div>
                                        <?php elseif ($st === 'partial'): ?>
                                            <div class="w-7 h-7 mx-auto rounded-lg bg-amber-500 text-white font-black text-[10px] flex items-center justify-center shadow-2xs cursor-pointer"
                                                 title="<?= e($dStr . ' — Terisi Sebagian (' . $cell['filled_count'] . '/' . $cell['expected_count'] . ' Sesi): ' . $cell['details']) ?>">
                                                ✓<?= $cell['filled_count'] ?>
                                            </div>
                                        <?php elseif ($st === 'unfilled'): ?>
                                            <button type="button"
                                                    onclick="openQuickFillFromMatrix(<?= (int)$mRow['guru_id'] ?>, '<?= e(addslashes($mRow['nama'])) ?>', '<?= e($dStr) ?>', '<?= e(addslashes($mRow['pic_classes'][0] ?? '')) ?>', '<?= e(addslashes($mRow['pic_subjects'][0] ?? '')) ?>')"
                                                    class="w-7 h-7 mx-auto rounded-lg bg-rose-100 hover:bg-rose-500 text-rose-600 hover:text-white border border-rose-300 font-black text-xs flex items-center justify-center transition-all"
                                                    title="<?= e($dStr . ' — BELUM MENGISI JURNAL (Klik untuk isi manual)') ?>">
                                                ✕
                                            </button>
                                        <?php else: ?>
                                            <span class="text-slate-300 font-bold text-xs" title="Tidak ada jadwal / Hari libur">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>

                                <td class="py-2.5 px-2.5 text-center font-black text-emerald-700 bg-emerald-50/30 border-l border-slate-200">
                                    <?= (int)$mRow['hari_terisi'] ?>
                                </td>
                                <td class="py-2.5 px-2.5 text-center font-black text-rose-600 bg-rose-50/30">
                                    <?= (int)$mRow['hari_kosong'] ?>
                                </td>
                                <td class="py-2.5 px-2.5 text-center font-black text-primary-700 bg-primary-50/30">
                                    <?= (int)$mRow['total_jp'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 5: REKAP JURNAL TIDAK TERISI                                      -->
    <!-- ===================================================================== -->
    <div id="tab-panel-kosong" class="jurnal-tab-panel space-y-4 <?= $activeTab === 'kosong' ? '' : 'hidden' ?>">
        <div class="bg-white rounded-3xl border border-rose-200/80 shadow-sm overflow-hidden">
            <div class="p-5 bg-rose-50/50 border-b border-rose-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="font-black text-rose-950 text-base flex items-center gap-2">
                        <span>⚠️</span>
                        <span>5. Daftar Rekap Jurnal yang Tidak Terisi</span>
                    </h3>
                    <p class="text-xs text-rose-700/80 mt-0.5">
                        Menampilkan sesi jadwal KBM yang seharusnya berlangsung pada periode <strong><?= date('d M Y', strtotime($periodStart)) ?></strong> s/d <strong><?= date('d M Y', strtotime($periodEnd)) ?></strong> namun belum diisi oleh Guru PIC.
                    </p>
                </div>
                <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-rose-600 text-white shadow-sm self-start sm:self-auto">
                    <?= number_format($unfilledTotalCount) ?> Sesi Belum Diisi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-4">No</th>
                            <th class="py-3.5 px-4">Tanggal & Hari</th>
                            <th class="py-3.5 px-4">Nama Guru (PIC)</th>
                            <th class="py-3.5 px-4">Kelas & Mata Pelajaran</th>
                            <th class="py-3.5 px-4">Jadwal Jam & JP</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        <?php if (!empty($unfilledList)): ?>
                            <?php foreach (array_slice($unfilledList, 0, 200) as $idx => $unf): ?>
                                <?php
                                $waPhone = preg_replace('/[^0-9]/', '', $unf['no_wa'] ?? '');
                                if (str_starts_with($waPhone, '0')) {
                                    $waPhone = '62' . substr($waPhone, 1);
                                }
                                $waText = urlencode("Assalamu'alaikum Ustadz/Ustadzah {$unf['nama_guru']}, mohon kesediaannya untuk melengkapi pengisian Jurnal Mengajar Kelas {$unf['kelas']} ({$unf['mata_pelajaran']}) untuk hari {$unf['hari']}, " . date('d-m-Y', strtotime($unf['tanggal'])) . " melalui aplikasi Portal BIP. Syukran.");
                                ?>
                                <tr class="hover:bg-rose-50/30 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-slate-400"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-900"><?= date('d M Y', strtotime($unf['tanggal'])) ?></div>
                                        <div class="text-[11px] text-rose-600 font-semibold">Hari <?= e($unf['hari']) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900"><?= e($unf['nama_guru']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">NIY: <?= e($unf['niy']) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-block px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs border border-indigo-100">
                                            <?= e($unf['kelas']) ?>
                                        </span>
                                        <div class="font-semibold text-slate-800 mt-1"><?= e($unf['mata_pelajaran']) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-700"><?= e($unf['waktu']) ?></div>
                                        <div class="text-[11px] text-primary-600 font-bold">Jam ke-<?= e($unf['jam_ke']) ?> (<?= (int)$unf['jumlah_jp'] ?> JP)</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-100 text-rose-700 border border-rose-200">
                                            ✕ Tidak Terisi
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <?php if ($waPhone !== ''): ?>
                                                <a href="https://wa.me/<?= e($waPhone) ?>?text=<?= $waText ?>"
                                                   target="_blank"
                                                   class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs transition-all">
                                                    💬 Ingatkan WA
                                                </a>
                                            <?php endif; ?>

                                            <?php if ($canManage): ?>
                                                <button type="button"
                                                        onclick="openQuickFillFromMatrix(<?= (int)$unf['guru_id'] ?>, '<?= e(addslashes($unf['nama_guru'])) ?>', '<?= e($unf['tanggal']) ?>', '<?= e(addslashes($unf['kelas'])) ?>', '<?= e(addslashes($unf['mata_pelajaran'])) ?>', '<?= e(addslashes($unf['jam_ke'])) ?>', <?= (int)$unf['jumlah_jp'] ?>, <?= (int)$unf['jadwal_id'] ?>)"
                                                        class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-2xs transition-all">
                                                    + Isi Jurnal
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center">
                                    <div class="text-3xl mb-2">🎉</div>
                                    <p class="font-bold text-emerald-700 text-sm">Luar Biasa! Tidak ada jurnal yang terlewat/kosong pada periode ini.</p>
                                    <p class="text-xs text-slate-400 mt-1">Seluruh jadwal mengajar guru telah terisi dengan lengkap.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: DETAIL JURNAL MENGAJAR                                           -->
<!-- ========================================================================= -->
<div id="modal-detail-jurnal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-fade-in">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-black text-slate-900 text-base">Rincian Jurnal Mengajar Guru</h3>
                <p id="detail-kode-jurnal" class="text-xs text-primary-600 font-mono font-bold mt-0.5">-</p>
            </div>
            <button type="button" onclick="closeDetailJurnalModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold">✕</button>
        </div>

        <div id="detail-jurnal-body" class="space-y-3 text-xs sm:text-sm">
            <div class="py-8 text-center text-slate-400">Memuat rincian jurnal...</div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeDetailJurnalModal()" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: INPUT / LENGKAPI JURNAL MANUAL (Sesuai Field Portal Guru)        -->
<!-- ========================================================================= -->
<?php if ($canManage): ?>
<div id="modal-input-jurnal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 my-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-black text-slate-900 text-base">Input / Lengkapi Jurnal Mengajar</h3>
                <p class="text-xs text-slate-500">Field terintegrasi langsung dengan Jurnal Portal Guru</p>
            </div>
            <button type="button" onclick="closeInputJurnalModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-jurnal-guru/store') ?>" class="space-y-3.5 text-xs sm:text-sm">
            <?= CSRF::field() ?>
            <input type="hidden" name="jadwal_id" id="form_jadwal_id" value="">
            <input type="hidden" name="return_tab" id="form_return_tab" value="<?= e($activeTab) ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Guru Pengajar (PIC) *</label>
                    <select name="guru_id" id="form_guru_id" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                        <option value="">— Pilih Guru —</option>
                        <?php foreach ($guruOptions as $g): ?>
                            <option value="<?= (int)$g['id'] ?>"><?= e($g['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mengajar *</label>
                    <input type="date" name="tanggal" id="form_tanggal" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelas *</label>
                    <input type="text" name="kelas" id="form_kelas" required list="datalist-kelas" placeholder="Contoh: Kelas 1 Abu Bakar" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                    <datalist id="datalist-kelas">
                        <?php foreach ($kelasOptions as $klsOpt): ?>
                            <option value="<?= e($klsOpt) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran *</label>
                    <input type="text" name="mata_pelajaran" id="form_mapel" required placeholder="Contoh: Matematika / Tahsin" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Ke</label>
                    <input type="text" name="jam_ke" id="form_jam_ke" value="1-2" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah JP</label>
                    <input type="number" name="jumlah_jp" id="form_jumlah_jp" value="2" min="1" max="12" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Presensi Siswa</label>
                    <input type="text" name="kehadiran_summary" id="form_kehadiran" value="Semua Siswa Hadir" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Materi Pokok / Bahasan *</label>
                <input type="text" name="materi_pokok" id="form_materi" required placeholder="Contoh: Bab 2 Operasi Hitung Pecahan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Uraian Aktivitas / Kegiatan Belajar</label>
                <textarea name="uraian_kegiatan" id="form_kegiatan" rows="2" placeholder="Ringkasan kegiatan pembelajaran di kelas..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Evaluasi Kelas (Opsional)</label>
                <input type="text" name="catatan_kelas" id="form_catatan" placeholder="Catatan khusus..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeInputJurnalModal()" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20">
                    Simpan & Validasi Jurnal
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
    /**
     * Instant Tab Switcher (Sinkron dengan Tombol Tab Atas, Sidebar Kiri & URL Parameter)
     */
    function switchJurnalTab(tabKey) {
        // 1. Toggle Panel Visibility
        document.querySelectorAll('.jurnal-tab-panel').forEach(panel => {
            panel.classList.add('hidden');
        });
        const activePanel = document.getElementById('tab-panel-' + tabKey);
        if (activePanel) {
            activePanel.classList.remove('hidden');
        }

        // 2. Update Top Tab Buttons
        document.querySelectorAll('[data-jurnal-tab-btn]').forEach(btn => {
            const key = btn.getAttribute('data-jurnal-tab-btn');
            if (key === tabKey) {
                if (key === 'kosong') {
                    btn.className = 'jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all bg-rose-600 text-white shadow-md shadow-rose-600/20';
                } else {
                    btn.className = 'jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all bg-primary-600 text-white shadow-md shadow-primary-600/20';
                }
            } else {
                if (key === 'kosong') {
                    btn.className = 'jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200/70';
                } else {
                    btn.className = 'jurnal-tab-btn inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all bg-slate-100 text-slate-600 hover:bg-slate-200/80';
                }
            }
        });

        // 3. Update Sidebar Links
        document.querySelectorAll('[data-jurnal-sidebar-tab]').forEach(link => {
            const key = link.getAttribute('data-jurnal-sidebar-tab');
            if (key === tabKey) {
                link.classList.add('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
                link.classList.remove('text-primary-200', 'font-medium', 'border-transparent');
            } else {
                link.classList.remove('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
                link.classList.add('text-primary-200', 'font-medium', 'border-transparent');
            }
        });

        // 4. Update Hidden Filter Input & URL Query
        const filterTabInput = document.getElementById('filter_active_tab');
        if (filterTabInput) filterTabInput.value = tabKey;

        const formReturnTab = document.getElementById('form_return_tab');
        if (formReturnTab) formReturnTab.value = tabKey;

        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabKey);
            window.history.replaceState({}, '', url.toString());
        } catch (e) {}
    }

    function openDetailJurnalModal(id) {
        const modal = document.getElementById('modal-detail-jurnal');
        const body = document.getElementById('detail-jurnal-body');
        const kodeEl = document.getElementById('detail-kode-jurnal');
        if (!modal || !body) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        body.innerHTML = '<div class="py-8 text-center text-slate-400">Memuat rincian jurnal...</div>';

        fetch('<?= url("kelola-jurnal-guru/api/detail/") ?>' + id)
            .then(r => r.json())
            .then(res => {
                if (!res || !res.success) {
                    body.innerHTML = '<div class="p-4 rounded-2xl bg-rose-50 text-rose-600 text-center">Gagal memuat data jurnal.</div>';
                    return;
                }
                const d = res.data;
                if (kodeEl) kodeEl.textContent = d.kode_jurnal || ('ID #' + d.id);
                body.innerHTML = `
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Nama Guru (PIC)</p>
                            <p class="font-bold text-slate-900 mt-0.5">${d.guru_nama_lengkap || d.nama_guru || '-'}</p>
                            <p class="text-[11px] text-slate-500 font-mono">NIY: ${d.niy || d.nik || '-'}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Tanggal & Jam</p>
                            <p class="font-bold text-slate-900 mt-0.5">${d.tanggal}</p>
                            <p class="text-[11px] text-primary-600 font-bold">Jam ke-${d.jam_ke || '1'} (${d.jumlah_jp || 2} JP)</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Kelas</p>
                            <p class="font-bold text-indigo-700 mt-0.5">${d.kelas || '-'}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Mata Pelajaran</p>
                            <p class="font-bold text-slate-800 mt-0.5">${d.mata_pelajaran || '-'}</p>
                        </div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 space-y-2">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Materi Pokok / Bahasan</p>
                            <p class="font-bold text-slate-900 mt-0.5">${d.materi_pokok || '-'}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Uraian Kegiatan Belajar</p>
                            <p class="text-slate-700 mt-0.5 whitespace-pre-line">${d.uraian_kegiatan || '-'}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase">Presensi Siswa</p>
                            <p class="font-bold text-emerald-700 mt-0.5">👥 ${d.kehadiran_summary || 'Semua Siswa Hadir'}</p>
                        </div>
                        ${d.catatan_kelas ? `
                        <div class="pt-1 border-t border-slate-100">
                            <p class="text-[10px] font-bold text-amber-700 uppercase">Catatan / Evaluasi Kelas</p>
                            <p class="text-slate-700 mt-0.5">${d.catatan_kelas}</p>
                        </div>` : ''}
                    </div>
                `;
            })
            .catch(() => {
                body.innerHTML = '<div class="p-4 rounded-2xl bg-rose-50 text-rose-600 text-center">Terjadi kesalahan jaringan.</div>';
            });
    }

    function closeDetailJurnalModal() {
        const modal = document.getElementById('modal-detail-jurnal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function openInputJurnalModal() {
        const modal = document.getElementById('modal-input-jurnal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeInputJurnalModal() {
        const modal = document.getElementById('modal-input-jurnal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openQuickFillFromMatrix(guruId, namaGuru, tanggal, kelas, mapel, jamKe = '1-2', jumlahJp = 2, jadwalId = '') {
        openInputJurnalModal();
        const gEl = document.getElementById('form_guru_id');
        if (gEl && guruId) gEl.value = guruId;
        const tEl = document.getElementById('form_tanggal');
        if (tEl && tanggal) tEl.value = tanggal;
        const kEl = document.getElementById('form_kelas');
        if (kEl && kelas) kEl.value = kelas;
        const mEl = document.getElementById('form_mapel');
        if (mEl && mapel) mEl.value = mapel;
        const jkEl = document.getElementById('form_jam_ke');
        if (jkEl && jamKe) jkEl.value = jamKe;
        const jpEl = document.getElementById('form_jumlah_jp');
        if (jpEl && jumlahJp) jpEl.value = jumlahJp;
        const jdEl = document.getElementById('form_jadwal_id');
        if (jdEl) jdEl.value = jadwalId || '';
    }
</script>
