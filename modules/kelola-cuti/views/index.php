<?php
/**
 * View Utama Modul Kelola Cuti Pegawai
 * Portal BIP
 *
 * Mencakup 6 Tab Terintegrasi:
 * 1. Data Cuti Pegawai (Semua Pegawai & Saldo Cuti Tahunan maks 36)
 * 2. Data Pengajuan Cuti
 * 3. Data Approve (Verifikasi Pengajuan Pending)
 * 4. Riwayat Approve (Disetujui / Ditolak / Dibatalkan)
 * 5. Log Pengurangan Cuti (& Mutasi Saldo)
 * 6. Jenis Cuti (Cuti Tahunan +1/bln maks 36 & Input Cuti Khusus)
 */

$activeTab = $activeTab ?? 'pegawai';
$cfgTahunan = $stats['configTahunan'] ?? ['id' => 1, 'tambah_per_bulan' => 1, 'maksimal_akumulasi' => 36, 'kuota_hari' => 36];
$maxAkumulasi = (int)($cfgTahunan['maksimal_akumulasi'] ?? 36);
$tambahPerBulan = (int)($cfgTahunan['tambah_per_bulan'] ?? 1);
?>

<div class="space-y-6">

    <!-- ═════════════════════════════════════════════════════════════════════
         TOP HERO BANNER & QUICK ACTIONS
         ═════════════════════════════════════════════════════════════════════ -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-950 via-primary-900 to-sky-950 p-6 sm:p-8 text-white shadow-xl border border-primary-800/80">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-sky-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute right-1/4 -top-10 w-60 h-60 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Cuti Tahunan Otomatis: +<?= $tambahPerBulan ?> Hari / Bulan (Maksimal <?= $maxAkumulasi ?> Hari)</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">
                    Manajemen & Kelola Cuti Pegawai
                </h1>
                <p class="text-primary-200/90 text-sm leading-relaxed">
                    Kelola kuota cuti seluruh pegawai, master jenis cuti tahunan & cuti khusus, pengajuan cuti, verifikasi persetujuan (approve), riwayat keputusan, hingga log pengurangan cuti secara terpadu.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button type="button" onclick="openModalAjukanCuti()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>+ Input Pengajuan Cuti</span>
                </button>

                <button type="button" onclick="openModalJenisCuti()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 backdrop-blur-sm transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                    <span>+ Tambah Cuti Khusus</span>
                </button>

                <a href="<?= url('kelola-cuti/export') ?>"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-sky-500/20 hover:bg-sky-500/30 text-sky-200 hover:text-white font-semibold text-xs border border-sky-400/30 backdrop-blur-sm transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span>Export Rekap CSV</span>
                </a>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         KPI SUMMARY CARDS
         ═════════════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <!-- 1. Total Pegawai -->
        <button type="button" onclick="switchCutiTab('pegawai')" class="text-left bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs hover:border-primary-300 hover:shadow-md transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pegawai</span>
                <div class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['totalPegawai']) ?></div>
            <p class="text-[11px] text-slate-500 mt-0.5">Pegawai aktif terdaftar</p>
        </button>

        <!-- 2. Menunggu Approve -->
        <button type="button" onclick="switchCutiTab('approve')" class="text-left bg-white rounded-2xl p-4 border border-amber-200/80 shadow-xs hover:border-amber-400 hover:shadow-md transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Perlu Approve</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-amber-600 mt-1"><?= number_format($stats['totalPending']) ?></div>
            <p class="text-[11px] text-slate-500 mt-0.5">Menunggu persetujuan</p>
        </button>

        <!-- 3. Disetujui -->
        <button type="button" onclick="switchCutiTab('riwayat')" class="text-left bg-white rounded-2xl p-4 border border-emerald-200/80 shadow-xs hover:border-emerald-400 hover:shadow-md transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Disetujui</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['totalApproved']) ?></div>
            <p class="text-[11px] text-slate-500 mt-0.5">Pengajuan di-approve</p>
        </button>

        <!-- 4. Sedang Cuti Hari Ini -->
        <button type="button" onclick="switchCutiTab('riwayat')" class="text-left bg-white rounded-2xl p-4 border border-sky-200/80 shadow-xs hover:border-sky-400 hover:shadow-md transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Sedang Cuti</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-sky-600 mt-1"><?= number_format($stats['sedangCutiHariIni']) ?></div>
            <p class="text-[11px] text-slate-500 mt-0.5">Pegawai cuti hari ini</p>
        </button>

        <!-- 5. Log Pengurangan -->
        <button type="button" onclick="switchCutiTab('log')" class="text-left bg-white rounded-2xl p-4 border border-rose-200/80 shadow-xs hover:border-rose-400 hover:shadow-md transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Log Potong</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-rose-600 mt-1"><?= number_format($stats['totalLogPengurangan']) ?></div>
            <p class="text-[11px] text-slate-500 mt-0.5">Transaksi potong cuti</p>
        </button>

        <!-- 6. Jenis Cuti -->
        <button type="button" onclick="switchCutiTab('jenis')" class="text-left bg-white rounded-2xl p-4 border border-purple-200/80 shadow-xs hover:border-purple-400 hover:shadow-md transition-all cursor-pointer">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-purple-600 uppercase tracking-wider">Jenis Cuti</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-purple-600 mt-1">1 + <?= number_format($stats['totalJenisKhusus']) ?></div>
            <p class="text-[11px] text-slate-500 mt-0.5">Tahunan & <?= $stats['totalJenisKhusus'] ?> Khusus</p>
        </button>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         NAVIGATION TABS (6 FITUR UTAMA KELOLA CUTI)
         ═════════════════════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-xs overflow-x-auto">
        <div class="flex items-center gap-1.5 min-w-max">
            <?php
            $tabsConfig = [
                'pegawai'   => ['label' => 'Data Cuti Pegawai', 'badge' => count($pegawaiList), 'badgeClass' => 'bg-slate-100 text-slate-700'],
                'pengajuan' => ['label' => 'Data Pengajuan', 'badge' => count($pengajuanList), 'badgeClass' => 'bg-sky-100 text-sky-700'],
                'approve'   => ['label' => 'Data Approve', 'badge' => count($pendingList), 'badgeClass' => 'bg-amber-500 text-white'],
                'riwayat'   => ['label' => 'Riwayat Approve', 'badge' => count($riwayatList), 'badgeClass' => 'bg-emerald-100 text-emerald-700'],
                'log'       => ['label' => 'Log Pengurangan Cuti', 'badge' => count($logList), 'badgeClass' => 'bg-rose-100 text-rose-700'],
                'jenis'     => ['label' => 'Jenis Cuti (Tahunan & Khusus)', 'badge' => count($jenisCutiList), 'badgeClass' => 'bg-purple-100 text-purple-700'],
                'hierarki'  => ['label' => 'Hierarki Approval', 'badge' => count($hierarkiList), 'badgeClass' => 'bg-indigo-100 text-indigo-700'],
            ];
            foreach ($tabsConfig as $tKey => $tCfg):
                $isAct = ($activeTab === $tKey);
            ?>
                <button type="button"
                    onclick="switchCutiTab('<?= $tKey ?>')"
                    data-tab-btn="<?= $tKey ?>"
                    class="cuti-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer <?= $isAct ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <span><?= e($tCfg['label']) ?></span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold <?= $isAct ? 'bg-white/20 text-white' : $tCfg['badgeClass'] ?>">
                        <?= (int)$tCfg['badge'] ?>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 1: DATA CUTI PEGAWAI (SEMUA PEGAWAI & KUOTA CUTI TAHUNAN MAKS 36)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="cuti-panel-pegawai" class="cuti-tab-panel space-y-4 <?= $activeTab === 'pegawai' ? '' : 'hidden' ?>">
        <!-- Filter & Action Bar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form method="GET" action="<?= url('kelola-cuti') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="pegawai">
                <div class="relative flex-1 min-w-[220px] max-w-xs">
                    <input type="text" name="search_pegawai" value="<?= e($pegawaiFilters['search']) ?>" placeholder="Cari nama pegawai, NIY, jabatan..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>

                <select name="unit_tugas" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none focus:border-primary-500">
                    <option value="">Semua Unit Tugas</option>
                    <?php foreach ($unitTugasList as $u): ?>
                        <option value="<?= e($u['unit_tugas']) ?>" <?= $pegawaiFilters['unit_tugas'] === $u['unit_tugas'] ? 'selected' : '' ?>><?= e($u['unit_tugas']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="status_pegawai" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none focus:border-primary-500">
                    <option value="">Semua Status</option>
                    <option value="Tetap" <?= $pegawaiFilters['status_pegawai'] === 'Tetap' ? 'selected' : '' ?>>Tetap</option>
                    <option value="Kontrak" <?= $pegawaiFilters['status_pegawai'] === 'Kontrak' ? 'selected' : '' ?>>Kontrak</option>
                    <option value="Training" <?= $pegawaiFilters['status_pegawai'] === 'Training' ? 'selected' : '' ?>>Training</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs transition-colors cursor-pointer">
                    Filter
                </button>
                <?php if ($pegawaiFilters['search'] || $pegawaiFilters['unit_tugas'] || $pegawaiFilters['status_pegawai']): ?>
                    <a href="<?= url('kelola-cuti?tab=pegawai') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl text-xs">
                        Reset
                    </a>
                <?php endif; ?>
            </form>

            <!-- Tombol Trigger Penambahan Bulanan (+1) -->
            <div class="flex items-center gap-2 shrink-0">
                <form method="POST" action="<?= url('kelola-cuti/tambah-bulanan') ?>" onsubmit="return confirm('Tambahkan +<?= $tambahPerBulan ?> hari Cuti Tahunan ke seluruh pegawai aktif (maksimal <?= $maxAkumulasi ?> hari)?');">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="return_tab" value="pegawai">
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs transition-colors cursor-pointer" title="Tambahkan +1 hari kuota cuti tahunan untuk semua pegawai aktif (maksimal 36)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Proses +<?= $tambahPerBulan ?> Cuti Bulanan (Maks <?= $maxAkumulasi ?>)</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Semua Pegawai & Saldo Cuti -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Daftar Saldo & Kuota Cuti Seluruh Pegawai</h3>
                    <p class="text-xs text-slate-500">Menampilkan <strong><?= count($pegawaiList) ?></strong> pegawai aktif &bull; Cuti Tahunan bertambah +<?= $tambahPerBulan ?> tiap bulan hingga maksimal <?= $maxAkumulasi ?> hari</p>
                </div>
                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                    Periode Akumulasi: <?= date('Y-m') ?>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-12">No</th>
                            <th class="py-3.5 px-4">Nama Pegawai & NIY</th>
                            <th class="py-3.5 px-4">Unit & Jabatan</th>
                            <th class="py-3.5 px-4">Masa Kerja</th>
                            <th class="py-3.5 px-4 text-center">Hak Cuti (+<?= $tambahPerBulan ?>/Bln)</th>
                            <th class="py-3.5 px-4 text-center">Terpakai (Tahunan)</th>
                            <th class="py-3.5 px-4 text-center">Terpakai (Khusus)</th>
                            <th class="py-3.5 px-4 text-center bg-emerald-50/70 text-emerald-900 border-x border-emerald-100 w-44">Sisa Cuti</th>
                            <th class="py-3.5 px-4 text-center">Status Hari Ini</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($pegawaiList)): ?>
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    Tidak ada data pegawai yang sesuai dengan pencarian.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pegawaiList as $idx => $p):
                                $saldo = (int)$p['saldo_tahunan'];
                                $terpakaiTahunan = (int)$p['total_terpakai_tahunan'];
                                $totalHakCuti = min($maxAkumulasi, max($saldo + $terpakaiTahunan, (int)$p['total_didapat']));
                                $pct = $maxAkumulasi > 0 ? min(100, round(($saldo / $maxAkumulasi) * 100)) : 0;
                                $barColor = $saldo >= 20 ? 'bg-emerald-500' : ($saldo >= 10 ? 'bg-sky-500' : ($saldo >= 4 ? 'bg-amber-500' : 'bg-rose-500'));
                                $sisaBadgeColor = $saldo >= 10
                                    ? 'bg-emerald-100 text-emerald-800 border-emerald-300'
                                    : ($saldo >= 4 ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-rose-100 text-rose-800 border-rose-300');
                                $namaLengkap = $p['nama'] . (!empty($p['gelar']) ? ', ' . $p['gelar'] : '');

                                // Hitung Masa Kerja
                                $masaKerja = '-';
                                $tglMasuk = !empty($p['tanggal_masuk']) ? $p['tanggal_masuk'] : (!empty($p['tmt']) ? $p['tmt'] : null);
                                if (!empty($tglMasuk)) {
                                    try {
                                        $d1 = new DateTime($tglMasuk);
                                        $d2 = new DateTime();
                                        if ($d1 <= $d2) {
                                            $diff = $d1->diff($d2);
                                            $parts = [];
                                            if ($diff->y > 0) $parts[] = $diff->y . ' Thn';
                                            if ($diff->m > 0) $parts[] = $diff->m . ' Bln';
                                            $masaKerja = empty($parts) ? '< 1 Bln' : implode(' ', $parts);
                                        }
                                    } catch (Throwable $e) {}
                                }
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-4 text-center text-slate-400 font-semibold"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($p['foto'])): ?>
                                                <img src="<?= url(ltrim($p['foto'], '/')) ?>" alt="" class="w-9 h-9 rounded-full object-cover border border-slate-200 shrink-0">
                                            <?php else: ?>
                                                <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center shrink-0 border border-primary-200">
                                                    <?= strtoupper(substr($p['nama'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-bold text-slate-800 text-xs"><?= e($namaLengkap) ?></div>
                                                <div class="text-[11px] text-slate-400 font-mono">NIY: <?= e($p['niy'] ?: '-') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-700"><?= e($p['unit_tugas'] ?: '-') ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($p['jabatan'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-700"><?= e($masaKerja) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= e($p['status_pegawai'] ?: ($p['status_kerja'] ?: 'Tetap')) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="font-bold text-slate-800"><?= $totalHakCuti ?> <span class="text-slate-400 font-normal">/ <?= $maxAkumulasi ?> Hari</span></div>
                                        <span class="inline-block mt-0.5 text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">+<?= $tambahPerBulan ?> hari/bln</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg font-bold bg-sky-50 text-sky-700 border border-sky-100">
                                            <?= $terpakaiTahunan ?> Hari
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg font-bold bg-purple-50 text-purple-700 border border-purple-100">
                                            <?= (int)$p['total_terpakai_khusus'] ?> Hari
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center bg-emerald-50/30 border-x border-emerald-100/60">
                                        <div class="inline-flex items-center gap-1 px-3 py-1 rounded-xl border font-extrabold text-sm shadow-2xs <?= $sisaBadgeColor ?>">
                                            <span><?= $saldo ?></span>
                                            <span class="text-[11px] font-bold">Hari</span>
                                        </div>
                                        <div class="w-full h-1.5 rounded-full bg-slate-200/80 overflow-hidden mt-1.5">
                                            <div class="h-full <?= $barColor ?> rounded-full transition-all duration-300" style="width: <?= $pct ?>%"></div>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Maks <?= $maxAkumulasi ?> Hari</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ((int)$p['sedang_cuti'] > 0): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                                Sedang Cuti
                                            </span>
                                        <?php elseif ((int)$p['jumlah_pending'] > 0): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                                <?= (int)$p['jumlah_pending'] ?> Pengajuan Pending
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Aktif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button"
                                                onclick="openModalAjukanCuti(<?= (int)$p['id'] ?>)"
                                                class="px-2.5 py-1.5 rounded-lg bg-primary-50 hover:bg-primary-100 text-primary-700 font-bold text-[11px] transition-colors cursor-pointer"
                                                title="Ajukan cuti untuk pegawai ini">
                                                + Cuti
                                            </button>
                                            <button type="button"
                                                onclick="openModalAdjustSaldo(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($namaLengkap), ENT_QUOTES, 'UTF-8') ?>, <?= $saldo ?>)"
                                                class="px-2.5 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-[11px] transition-colors cursor-pointer"
                                                title="Sesuaikan saldo cuti tahunan">
                                                Saldo
                                            </button>
                                            <a href="<?= url('kelola-cuti?tab=log&pegawai_log=' . (int)$p['id']) ?>"
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition-colors"
                                                title="Lihat log pengurangan & mutasi cuti pegawai ini">
                                                Log
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 2: DATA PENGAJUAN CUTI
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="cuti-panel-pengajuan" class="cuti-tab-panel space-y-4 <?= $activeTab === 'pengajuan' ? '' : 'hidden' ?>">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="<?= url('kelola-cuti') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="pengajuan">
                <div class="relative flex-1 min-w-[220px] max-w-xs">
                    <input type="text" name="search_pengajuan" value="<?= e($pengajuanFilters['search']) ?>" placeholder="Cari nomor, nama pegawai, alasan..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none focus:border-primary-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>

                <select name="kategori_cuti" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Kategori Cuti</option>
                    <option value="Tahunan" <?= $pengajuanFilters['kategori_cuti'] === 'Tahunan' ? 'selected' : '' ?>>Cuti Tahunan</option>
                    <option value="Khusus" <?= $pengajuanFilters['kategori_cuti'] === 'Khusus' ? 'selected' : '' ?>>Cuti Khusus</option>
                </select>

                <select name="status_pengajuan" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Status</option>
                    <option value="Pending" <?= $pengajuanFilters['status'] === 'Pending' ? 'selected' : '' ?>>Pending (Menunggu Approve)</option>
                    <option value="Disetujui" <?= $pengajuanFilters['status'] === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="Ditolak" <?= $pengajuanFilters['status'] === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    <option value="Dibatalkan" <?= $pengajuanFilters['status'] === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs cursor-pointer">
                    Filter
                </button>
                <?php if ($pengajuanFilters['search'] || $pengajuanFilters['kategori_cuti'] || $pengajuanFilters['status']): ?>
                    <a href="<?= url('kelola-cuti?tab=pengajuan') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl text-xs">
                        Reset
                    </a>
                <?php endif; ?>
            </form>

            <button type="button" onclick="openModalAjukanCuti()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-sm cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>+ Buat Pengajuan Cuti</span>
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Daftar Seluruh Pengajuan Cuti Pegawai</h3>
                    <p class="text-xs text-slate-500">Menampilkan <?= count($pengajuanList) ?> data permohonan cuti</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4">No. Pengajuan</th>
                            <th class="py-3.5 px-4">Pegawai</th>
                            <th class="py-3.5 px-4">Jenis Cuti</th>
                            <th class="py-3.5 px-4">Tanggal & Durasi</th>
                            <th class="py-3.5 px-4 text-center bg-emerald-50/70 text-emerald-900">Sisa Cuti</th>
                            <th class="py-3.5 px-4">Alasan & Pengganti</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($pengajuanList)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Belum ada data pengajuan cuti. Klik <strong>+ Buat Pengajuan Cuti</strong> untuk menambahkan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pengajuanList as $cp):
                                $statusBadge = match($cp['status']) {
                                    'Disetujui'  => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'Ditolak'    => 'bg-rose-100 text-rose-800 border-rose-200',
                                    'Dibatalkan' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    default      => 'bg-amber-100 text-amber-800 border-amber-200'
                                };
                                $namaPeg = $cp['nama_pegawai'] . (!empty($cp['gelar']) ? ', ' . $cp['gelar'] : '');
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-primary-700">
                                        <?= e($cp['nomor_pengajuan']) ?>
                                        <div class="text-[10px] font-sans font-normal text-slate-400 mt-0.5">
                                            <?= date('d/m/Y H:i', strtotime($cp['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($namaPeg) ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($cp['unit_tugas'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold <?= $cp['kategori_cuti'] === 'Tahunan' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-purple-50 text-purple-700 border border-purple-200' ?>">
                                            <?= e($cp['kategori_cuti']) ?>
                                        </span>
                                        <div class="font-bold text-slate-800 mt-1"><?= e($cp['nama_jenis']) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800">
                                            <?= CutiModel::formatTanggalCutiDisplay($cp) ?>
                                        </div>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold text-[10px]">
                                            <?= (int)$cp['jumlah_hari'] ?> Hari
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center bg-emerald-50/20">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-extrabold text-xs">
                                            <?= (int)$cp['saldo_saat_ini'] ?> Hari
                                        </span>
                                        <div class="text-[10px] text-slate-400 mt-0.5">dari <?= $maxAkumulasi ?> hr</div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <div class="text-slate-700 line-clamp-2"><?= e($cp['alasan']) ?></div>
                                        <?php if (!empty($cp['pelimpahan_tugas'])): ?>
                                            <div class="text-[10px] text-slate-400 mt-0.5">Pengganti: <strong class="text-slate-600"><?= e($cp['pelimpahan_tugas']) ?></strong></div>
                                        <?php endif; ?>
                                        <?php if (!empty($cp['file_lampiran'])): ?>
                                            <a href="<?= url('public/' . ltrim($cp['file_lampiran'], '/')) ?>" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-600 hover:underline mt-1">
                                                📎 Lihat Lampiran
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $statusBadge ?>">
                                            <?= e($cp['status']) ?>
                                        </span>
                                        <?php if ($cp['status'] === 'Pending' && !empty($cp['current_step'])): ?>
                                            <div class="mt-1 text-[9.5px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200 truncate max-w-[130px] mx-auto" title="<?= e($cp['current_step']['step_nama']) ?>">
                                                Step <?= (int)$cp['current_step']['step_order'] ?>: <?= e($cp['current_step']['step_nama']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <?php if ($cp['status'] === 'Pending'): 
                                                $curStep = $cp['current_step'] ?? null;
                                                $curStepOrder = $curStep ? (int)$curStep['step_order'] : 1;
                                                $curStepNama = $curStep ? $curStep['step_nama'] : 'Persetujuan';
                                                $curStepTipe = $curStep ? ($curStep['tipe_step'] ?? 'Verifikasi') : 'Verifikasi';
                                                $totalSteps = (int)($cp['total_steps'] ?: 1);
                                                $stepsJson = json_encode($cp['steps'] ?? []);
                                            ?>
                                                <button type="button"
                                                    onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Disetujui', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'pengajuan', <?= $curStepOrder ?>, <?= htmlspecialchars(json_encode($curStepNama), ENT_QUOTES, 'UTF-8') ?>, <?= $totalSteps ?>, <?= htmlspecialchars($stepsJson, ENT_QUOTES, 'UTF-8') ?>)"
                                                    class="px-2.5 py-1.5 rounded-lg <?= $curStepTipe === 'Approval' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-emerald-600 hover:bg-emerald-700' ?> text-white font-bold text-[11px] cursor-pointer">
                                                    <?= $curStepTipe === 'Approval' ? 'Approve' : 'Verifikasi' ?>
                                                </button>
                                                <button type="button"
                                                    onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Ditolak', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'pengajuan', <?= $curStepOrder ?>, <?= htmlspecialchars(json_encode($curStepNama), ENT_QUOTES, 'UTF-8') ?>, <?= $totalSteps ?>, <?= htmlspecialchars($stepsJson, ENT_QUOTES, 'UTF-8') ?>)"
                                                    class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[11px] cursor-pointer">
                                                    Tolak
                                                </button>
                                            <?php else: ?>
                                                <a href="<?= url('kelola-cuti/cetak/' . (int)$cp['id']) ?>" target="_blank"
                                                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px]">
                                                    🖨️ Cetak
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 3: DATA APPROVE (MENUNGGU VERIFIKASI / APPROVAL)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="cuti-panel-approve" class="cuti-tab-panel space-y-4 <?= $activeTab === 'approve' ? '' : 'hidden' ?>">
        <div class="bg-amber-50/90 border border-amber-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0">
                    ✓
                </div>
                <div>
                    <h3 class="font-bold text-amber-950 text-sm">Verifikasi & Persetujuan Cuti Pegawai (Data Approve)</h3>
                    <p class="text-xs text-amber-800">Saat Anda menekan tombol <strong>Setujui (Approve)</strong> pada Cuti Tahunan, sistem otomatis memotong saldo cuti pegawai dan mencatatnya ke <strong>Log Pengurangan Cuti</strong>.</p>
                </div>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-white border border-amber-200 text-amber-800 font-extrabold text-xs shrink-0">
                <?= count($pendingList) ?> Antrean Menunggu
            </span>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4">No. Pengajuan</th>
                            <th class="py-3.5 px-4">Nama Pegawai</th>
                            <th class="py-3.5 px-4">Jenis Cuti</th>
                            <th class="py-3.5 px-4">Tanggal & Durasi</th>
                            <th class="py-3.5 px-4 text-center">Sisa Saldo Saat Ini</th>
                            <th class="py-3.5 px-4">Alasan & Pelimpahan Tugas</th>
                            <th class="py-3.5 px-4 text-center">Keputusan Approve</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($pendingList)): ?>
                            <tr>
                                <td colspan="7" class="py-14 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto space-y-1">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center mx-auto mb-2 font-bold text-lg">✓</div>
                                        <p class="font-bold text-slate-700 text-sm">Tidak Ada Antrean Pengajuan</p>
                                        <p class="text-xs text-slate-400">Seluruh pengajuan cuti pegawai telah diverifikasi.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingList as $cp):
                                $namaPeg = $cp['nama_pegawai'] . (!empty($cp['gelar']) ? ', ' . $cp['gelar'] : '');
                                $potongTahunan = (int)$cp['potong_kuota_tahunan'] === 1;
                                $saldoCukup = !$potongTahunan || ((int)$cp['saldo_saat_ini'] >= (int)$cp['jumlah_hari']);

                                $curStep = $cp['current_step'] ?? null;
                                $curStepOrder = $curStep ? (int)$curStep['step_order'] : 1;
                                $curStepNama = $curStep ? $curStep['step_nama'] : 'Persetujuan';
                                $curStepTipe = $curStep ? ($curStep['tipe_step'] ?? 'Verifikasi') : 'Verifikasi';
                                $curJabId = $curStep ? (int)($curStep['approver_jabatan_id'] ?? 0) : 0;
                                $curRole = $curStep ? trim((string)($curStep['approver_role'] ?? '')) : '';
                                $totalSteps = (int)($cp['total_steps'] ?: 1);
                                $stepsJson = json_encode($cp['steps'] ?? []);

                                $isMyTurn = false;
                                if (!empty($isUserAdmin)) {
                                    $isMyTurn = true;
                                } elseif ($curJabId > 0 && !empty($currentJabatanIds) && in_array($curJabId, $currentJabatanIds, true)) {
                                    $isMyTurn = true;
                                } elseif (!empty($currentJabatanNama) && !empty($curRole) && (stripos($currentJabatanNama, $curRole) !== false || stripos($curRole, $currentJabatanNama) !== false)) {
                                    $isMyTurn = true;
                                }
                            ?>
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-primary-700">
                                        <?= e($cp['nomor_pengajuan']) ?>
                                        <div class="text-[10px] font-sans font-normal text-slate-400 mt-0.5">
                                            Diajukan: <?= date('d/m/Y H:i', strtotime($cp['created_at'])) ?>
                                        </div>
                                        <?php if (!empty($curStep)): ?>
                                            <div class="mt-1.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-lg <?= $curStepTipe === 'Approval' ? 'bg-purple-50 border-purple-200 text-purple-700' : 'bg-indigo-50 border-indigo-200 text-indigo-700' ?> border text-[10px] font-bold">
                                                <span>⏳ Tahap <?= $curStepOrder ?>/<?= $totalSteps ?> (<?= e($curStepTipe) ?>):</span>
                                                <span class="truncate max-w-[125px]"><?= e($curStepNama) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($isMyTurn): ?>
                                            <div class="mt-1">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-300 font-extrabold text-[10px]">
                                                    ⭐ Giliran Anda (<?= e($currentJabatanNama ?: 'Penyetuju') ?>)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($namaPeg) ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($cp['unit_tugas'] ?: '-') ?> &bull; <?= e($cp['jabatan'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?= $cp['kategori_cuti'] === 'Tahunan' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-purple-50 text-purple-700 border border-purple-200' ?>">
                                            <?= e($cp['kategori_cuti']) ?>
                                        </span>
                                        <div class="font-bold text-slate-800 mt-1"><?= e($cp['nama_jenis']) ?></div>
                                        <div class="text-[10px] <?= $potongTahunan ? 'text-rose-600 font-semibold' : 'text-emerald-600' ?>">
                                            <?= $potongTahunan ? 'Memotong Saldo Tahunan' : 'Tidak Potong Saldo Tahunan' ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800">
                                            <?= CutiModel::formatTanggalCutiDisplay($cp) ?>
                                        </div>
                                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-lg bg-amber-100 text-amber-800 font-extrabold text-[11px]">
                                            <?= (int)$cp['jumlah_hari'] ?> Hari
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="font-extrabold text-sm <?= $saldoCukup ? 'text-emerald-600' : 'text-rose-600' ?>">
                                            <?= (int)$cp['saldo_saat_ini'] ?> <span class="text-[10px] font-normal text-slate-400">/ <?= $maxAkumulasi ?> hr</span>
                                        </div>
                                        <?php if ($potongTahunan): ?>
                                            <div class="text-[10px] text-slate-500">
                                                Sisa setelah potong: <strong><?= max(0, (int)$cp['saldo_saat_ini'] - (int)$cp['jumlah_hari']) ?> hr</strong>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <div class="font-medium text-slate-800"><?= e($cp['alasan']) ?></div>
                                        <?php if (!empty($cp['pelimpahan_tugas'])): ?>
                                            <div class="text-[10px] text-slate-500 mt-0.5">Pelimpahan: <strong><?= e($cp['pelimpahan_tugas']) ?></strong></div>
                                        <?php endif; ?>
                                        <?php if (!empty($cp['file_lampiran'])): ?>
                                            <a href="<?= url('public/' . ltrim($cp['file_lampiran'], '/')) ?>" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-sky-600 hover:underline mt-1">
                                                📎 Buka Dokumen Pendukung
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button"
                                                onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Disetujui', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'approve', <?= $curStepOrder ?>, <?= htmlspecialchars(json_encode($curStepNama), ENT_QUOTES, 'UTF-8') ?>, <?= $totalSteps ?>, <?= htmlspecialchars($stepsJson, ENT_QUOTES, 'UTF-8') ?>)"
                                                class="px-3 py-2 rounded-xl <?= $curStepTipe === 'Approval' ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-emerald-600 hover:bg-emerald-700' ?> text-white font-bold text-xs shadow-sm cursor-pointer flex items-center gap-1.5 transition-all">
                                                <?php if ($curStepTipe === 'Approval'): ?>
                                                    <span>✍️ Setujui</span>
                                                <?php else: ?>
                                                    <span>🔍 Verifikasi</span>
                                                <?php endif; ?>
                                            </button>
                                            <button type="button"
                                                onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Ditolak', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'approve', <?= $curStepOrder ?>, <?= htmlspecialchars(json_encode($curStepNama), ENT_QUOTES, 'UTF-8') ?>, <?= $totalSteps ?>, <?= htmlspecialchars($stepsJson, ENT_QUOTES, 'UTF-8') ?>)"
                                                class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs cursor-pointer flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                                <span>Tolak</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 4: RIWAYAT APPROVE (DISETUJUI / DITOLAK / DIBATALKAN)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="cuti-panel-riwayat" class="cuti-tab-panel space-y-4 <?= $activeTab === 'riwayat' ? '' : 'hidden' ?>">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="<?= url('kelola-cuti') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="riwayat">
                <div class="relative flex-1 min-w-[220px] max-w-xs">
                    <input type="text" name="search_riwayat" value="<?= e($riwayatFilters['search'] ?? '') ?>" placeholder="Cari nama pegawai, nomor pengajuan..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>

                <select name="status_riwayat" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Riwayat Keputusan</option>
                    <option value="Disetujui" <?= ($_GET['status_riwayat'] ?? '') === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="Ditolak" <?= ($_GET['status_riwayat'] ?? '') === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    <option value="Dibatalkan" <?= ($_GET['status_riwayat'] ?? '') === 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs cursor-pointer">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Riwayat Persetujuan / Keputusan Cuti (Riwayat Approve)</h3>
                    <p class="text-xs text-slate-500">Menampilkan <?= count($riwayatList) ?> riwayat pengajuan yang telah diverifikasi</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4">No. Pengajuan</th>
                            <th class="py-3.5 px-4">Nama Pegawai</th>
                            <th class="py-3.5 px-4">Jenis & Durasi Cuti</th>
                            <th class="py-3.5 px-4 text-center">Potong Saldo</th>
                            <th class="py-3.5 px-4 text-center bg-emerald-50/70 text-emerald-900">Sisa Cuti</th>
                            <th class="py-3.5 px-4 text-center">Status Approve</th>
                            <th class="py-3.5 px-4">Diverifikasi Oleh & Catatan</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($riwayatList)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Belum ada riwayat pengajuan yang telah di-approve atau ditolak.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayatList as $cp):
                                $statusBadge = match($cp['status']) {
                                    'Disetujui'  => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'Ditolak'    => 'bg-rose-100 text-rose-800 border-rose-200',
                                    'Dibatalkan' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    default      => 'bg-amber-100 text-amber-800 border-amber-200'
                                };
                                $namaPeg = $cp['nama_pegawai'] . (!empty($cp['gelar']) ? ', ' . $cp['gelar'] : '');
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-primary-700">
                                        <?= e($cp['nomor_pengajuan']) ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($namaPeg) ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($cp['unit_tugas'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($cp['nama_jenis']) ?> (<?= (int)$cp['jumlah_hari'] ?> Hari)</div>
                                        <div class="text-[11px] text-slate-500">
                                            <?= CutiModel::formatTanggalCutiDisplay($cp) ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ($cp['status'] === 'Disetujui' && (int)$cp['potong_saldo'] > 0): ?>
                                            <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                                                -<?= (int)$cp['potong_saldo'] ?> Hari
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400 font-medium">0 Hari</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center bg-emerald-50/20">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-extrabold text-xs">
                                            <?= (int)$cp['saldo_saat_ini'] ?> Hari
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $statusBadge ?>">
                                            <?= e($cp['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($cp['approved_by_name'] ?: 'Admin') ?></div>
                                        <div class="text-[10px] text-slate-400">
                                            <?= !empty($cp['approved_at']) ? date('d/m/Y H:i', strtotime($cp['approved_at'])) : '-' ?>
                                        </div>
                                        <?php if (!empty($cp['catatan_approval'])): ?>
                                            <div class="text-[11px] text-slate-600 italic mt-0.5">"<?= e($cp['catatan_approval']) ?>"</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="<?= url('kelola-cuti/cetak/' . (int)$cp['id']) ?>" target="_blank"
                                                class="px-2.5 py-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-[11px]">
                                                🖨️ Surat Cuti
                                            </a>
                                            <?php if ($cp['status'] === 'Disetujui'): ?>
                                                <button type="button"
                                                    onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Dibatalkan', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'riwayat')"
                                                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 font-semibold text-[11px] cursor-pointer"
                                                    title="Batalkan cuti dan kembalikan saldo">
                                                    Batalkan
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 5: LOG PENGURANGAN CUTI (& MUTASI SALDO CUTI)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="cuti-panel-log" class="cuti-tab-panel space-y-4 <?= $activeTab === 'log' ? '' : 'hidden' ?>">
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <form method="GET" action="<?= url('kelola-cuti') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="log">
                <div class="relative flex-1 min-w-[220px] max-w-xs">
                    <input type="text" name="search_log" value="<?= e($logFilters['search'] ?? '') ?>" placeholder="Cari nama pegawai, keterangan log..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>

                <select name="tipe_log" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Log (Pengurangan & Penambahan)</option>
                    <option value="pengurangan" <?= ($logFilters['tipe'] ?? '') === 'pengurangan' ? 'selected' : '' ?>>Khusus Pengurangan / Potong Cuti (-)</option>
                    <option value="penambahan" <?= ($logFilters['tipe'] ?? '') === 'penambahan' ? 'selected' : '' ?>>Khusus Penambahan Bulanan (+1)</option>
                </select>

                <select name="pegawai_log" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none max-w-[220px]">
                    <option value="">Semua Pegawai</option>
                    <?php foreach ($allPegawaiDropdown as $pg): ?>
                        <option value="<?= (int)$pg['id'] ?>" <?= (int)($logFilters['pegawai_id'] ?? 0) === (int)$pg['id'] ? 'selected' : '' ?>>
                            <?= e($pg['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs cursor-pointer">
                    Filter Log
                </button>
                <?php if (!empty($logFilters['search']) || !empty($logFilters['tipe']) || !empty($logFilters['pegawai_id'])): ?>
                    <a href="<?= url('kelola-cuti?tab=log') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl text-xs">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Log Pengurangan Cuti & Riwayat Mutasi Saldo</h3>
                    <p class="text-xs text-slate-500">Rekam jejak otomatis setiap pemotongan cuti pegawai saat approve, penambahan bulanan (+1 s.d. 36), dan penyesuaian saldo</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4">Waktu</th>
                            <th class="py-3.5 px-4">Nama Pegawai</th>
                            <th class="py-3.5 px-4">Jenis Mutasi</th>
                            <th class="py-3.5 px-4 text-center">Saldo Sebelum</th>
                            <th class="py-3.5 px-4 text-center">Pengurangan / Mutasi</th>
                            <th class="py-3.5 px-4 text-center">Saldo Sesudah</th>
                            <th class="py-3.5 px-4">Keterangan / No. Pengajuan</th>
                            <th class="py-3.5 px-4">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($logList)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Belum ada data log mutasi/pengurangan cuti.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logList as $lg):
                                $chg = (int)$lg['perubahan'];
                                $isMinus = $chg < 0;
                                $isPlus = $chg > 0;
                                $namaPeg = $lg['nama_pegawai'] . (!empty($lg['gelar']) ? ', ' . $lg['gelar'] : '');
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                        <div class="font-semibold text-slate-700"><?= date('d/m/Y', strtotime($lg['created_at'])) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= date('H:i', strtotime($lg['created_at'])) ?> WITA</div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($namaPeg) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= e($lg['unit_tugas'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $isMinus ? 'bg-rose-50 text-rose-700 border-rose-200' : ($isPlus ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-purple-50 text-purple-700 border-purple-200') ?>">
                                            <?= e($lg['jenis_mutasi']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-semibold text-slate-600">
                                        <?= (int)$lg['saldo_sebelum'] ?> Hari
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg font-extrabold text-xs <?= $isMinus ? 'bg-rose-100 text-rose-700' : ($isPlus ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700') ?>">
                                            <?= $chg > 0 ? '+' . $chg : $chg ?> Hari
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-extrabold text-slate-900">
                                        <?= (int)$lg['saldo_sesudah'] ?> Hari
                                    </td>
                                    <td class="py-3.5 px-4 max-w-md">
                                        <div class="text-slate-700"><?= e($lg['keterangan']) ?></div>
                                        <?php if (!empty($lg['nomor_pengajuan'])): ?>
                                            <div class="text-[10px] font-mono text-primary-600 mt-0.5">Ref: <?= e($lg['nomor_pengajuan']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600 font-medium">
                                        <?= e($lg['created_by_name'] ?: 'Sistem') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 6: JENIS CUTI (CUTI TAHUNAN +1/BLN MAKS 36 & INPUT CUTI KHUSUS)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="cuti-panel-jenis" class="cuti-tab-panel space-y-6 <?= $activeTab === 'jenis' ? '' : 'hidden' ?>">

        <!-- Kartu 1: Pengaturan Cuti Tahunan (+1 per bulan maksimal 36) -->
        <div class="bg-gradient-to-r from-sky-50 via-white to-emerald-50 rounded-2xl p-6 border border-sky-200/80 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-1.5 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-800 text-xs font-extrabold">
                        <span>📅 KATEGORI UTAMA: CUTI TAHUNAN</span>
                    </div>
                    <h3 class="text-lg font-extrabold text-slate-900">
                        Aturan Akumulasi Cuti Tahunan Pegawai
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Kuota <strong>Cuti Tahunan</strong> setiap pegawai aktif bertambah secara otomatis sebesar <strong>+<?= $tambahPerBulan ?> hari setiap pergantian bulan</strong> dan dapat terakumulasi hingga batas maksimal <strong><?= $maxAkumulasi ?> hari</strong>. Setiap pengajuan Cuti Tahunan yang disetujui akan langsung memotong saldo ini.
                    </p>
                </div>

                <form method="POST" action="<?= url('kelola-cuti/jenis/store') ?>" class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs flex flex-wrap sm:flex-nowrap items-end gap-3 shrink-0">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="id" value="<?= (int)$cfgTahunan['id'] ?>">
                    <input type="hidden" name="nama_jenis" value="Cuti Tahunan">
                    <input type="hidden" name="kategori" value="Tahunan">
                    <input type="hidden" name="potong_kuota_tahunan" value="1">
                    <input type="hidden" name="is_active" value="1">
                    <input type="hidden" name="keterangan" value="Hak cuti tahunan pegawai yang bertambah otomatis setiap bulan hingga batas maksimal akumulasi.">

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tambah / Bulan (Hari)</label>
                        <input type="number" name="tambah_per_bulan" value="<?= $tambahPerBulan ?>" min="1" max="12" required
                               class="w-28 px-3 py-2 rounded-xl border border-slate-200 text-xs font-extrabold text-slate-900 focus:border-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Maksimal Saldo (Hari)</label>
                        <input type="number" name="maksimal_akumulasi" value="<?= $maxAkumulasi ?>" min="1" max="100" required
                               oninput="this.form.kuota_hari.value = this.value"
                               class="w-28 px-3 py-2 rounded-xl border border-slate-200 text-xs font-extrabold text-slate-900 focus:border-primary-500 outline-none">
                        <input type="hidden" name="kuota_hari" value="<?= $maxAkumulasi ?>">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-sm cursor-pointer">
                        Simpan Aturan
                    </button>
                </form>
            </div>
        </div>

        <!-- Kartu 2: Daftar & Input Cuti Khusus -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Daftar Jenis Cuti (Cuti Tahunan & Cuti Khusus)</h3>
                    <p class="text-xs text-slate-500">Anda dapat menambahkan berbagai jenis <strong>Cuti Khusus</strong> baru sesuai kebijakan yayasan/sekolah</p>
                </div>
                <button type="button" onclick="openModalJenisCuti()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm cursor-pointer self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>+ Tambah Jenis Cuti Khusus</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4">Kode</th>
                            <th class="py-3.5 px-4">Nama Jenis Cuti</th>
                            <th class="py-3.5 px-4 text-center">Kategori</th>
                            <th class="py-3.5 px-4 text-center">Batas / Kuota Hari</th>
                            <th class="py-3.5 px-4 text-center">Memotong Cuti Tahunan?</th>
                            <th class="py-3.5 px-4 text-center">Syarat Lampiran</th>
                            <th class="py-3.5 px-4">Keterangan</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($jenisCutiList as $jc): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-700"><?= e($jc['kode']) ?></td>
                                <td class="py-3.5 px-4 font-bold text-slate-900"><?= e($jc['nama_jenis']) ?></td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($jc['kategori'] === 'Tahunan'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-200">
                                            Cuti Tahunan
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            Cuti Khusus
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center font-extrabold text-slate-800">
                                    <?php if ($jc['kategori'] === 'Tahunan'): ?>
                                        +<?= (int)$jc['tambah_per_bulan'] ?>/bln (Maks <?= (int)$jc['maksimal_akumulasi'] ?> Hari)
                                    <?php else: ?>
                                        Maks <?= (int)$jc['kuota_hari'] ?> Hari
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ((int)$jc['potong_kuota_tahunan'] === 1): ?>
                                        <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[10px]">
                                            Ya (Potong Saldo)
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[10px]">
                                            Tidak (Kuota Khusus)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?= (int)$jc['butuh_lampiran'] === 1 ? '<span class="text-amber-600 font-bold">Wajib Lampiran</span>' : '<span class="text-slate-400">Opsional</span>' ?>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs text-slate-600">
                                    <?= e($jc['keterangan'] ?: '-') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ((int)$jc['is_active'] === 1): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                            onclick="openModalJenisCuti(<?= htmlspecialchars(json_encode($jc), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] cursor-pointer">
                                            Edit
                                        </button>
                                        <?php if ($jc['kategori'] !== 'Tahunan'): ?>
                                            <form method="POST" action="<?= url('kelola-cuti/jenis/delete') ?>" onsubmit="return confirm('Hapus jenis cuti khusus ini?');" class="inline">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="id" value="<?= (int)$jc['id'] ?>">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[11px] cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 7: HIERARKI APPROVAL (CUSTOM WORKFLOW PERSETUJUAN CUTI)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="cuti-panel-hierarki" class="cuti-tab-panel space-y-5 <?= $activeTab === 'hierarki' ? '' : 'hidden' ?>">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-indigo-950 via-indigo-900 to-purple-950 rounded-3xl p-6 sm:p-7 text-white shadow-xl border border-indigo-800/80 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold mb-2.5">
                    <span>⚙️ Custom Approval Workflow</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white mb-1.5">
                    Hierarki & Alur Persetujuan Cuti Pegawai
                </h2>
                <p class="text-indigo-200/90 text-xs sm:text-sm leading-relaxed">
                    Kustomisasi tahapan persetujuan cuti (Multi-Step Approval) sesuai kebijakan sekolah. Setiap pengajuan cuti pegawai akan diproses bertingkat sesuai unit dan jenis pegawai yang Anda konfigurasikan di bawah ini.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <button type="button" onclick="openModalTambahHierarki()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    <span>+ Tambah Tahapan Approval</span>
                </button>
                <form method="POST" action="<?= url('kelola-cuti/hierarki/reset') ?>" onsubmit="return confirm('Apakah Anda yakin ingin me-reset hierarki approval ke template standar 2-tahap?');" class="inline">
                    <?= CSRF::field() ?>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-2xl bg-white/10 hover:bg-rose-500/20 border border-white/20 text-indigo-200 hover:text-rose-200 font-semibold text-xs transition-all cursor-pointer">
                        <span>↺ Reset Default</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Control & Filter Bar Bagan -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Bagan View Switcher Tabs -->
                <div class="inline-flex items-center p-1 bg-slate-100 rounded-xl text-xs font-bold">
                    <button type="button" onclick="switchBaganView('all')" id="btn-bagan-all" class="px-3 py-1.5 rounded-lg bg-white text-indigo-700 shadow-xs transition-all cursor-pointer">
                        🌟 Semua Bagan
                    </button>
                    <button type="button" onclick="switchBaganView('guru')" id="btn-bagan-guru" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                        🎓 Bagan Guru
                    </button>
                    <button type="button" onclick="switchBaganView('support')" id="btn-bagan-support" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                        🏢 Bagan Support System
                    </button>
                    <button type="button" onclick="switchBaganView('table')" id="btn-bagan-table" class="px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition-all cursor-pointer">
                        📋 Format Tabel
                    </button>
                </div>

                <!-- Filter Form -->
                <form method="GET" action="<?= url('kelola-cuti') ?>" class="flex flex-wrap items-center gap-2.5">
                    <input type="hidden" name="tab" value="hierarki">
                    <select name="unit_hierarki" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-800 focus:bg-white outline-none focus:border-indigo-500">
                        <option value="">Semua Unit (Global)</option>
                        <?php foreach ($unitTugasList as $ut): ?>
                            <option value="<?= e($ut['unit_tugas']) ?>" <?= ($hierarkiFilters['unit'] === $ut['unit_tugas']) ? 'selected' : '' ?>>
                                <?= e($ut['unit_tugas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="jenis_hierarki" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-800 focus:bg-white outline-none focus:border-indigo-500">
                        <option value="">Semua Jenis Pegawai</option>
                        <option value="Guru" <?= ($hierarkiFilters['jenis_pegawai'] === 'Guru') ? 'selected' : '' ?>>Guru</option>
                        <option value="Support System" <?= ($hierarkiFilters['jenis_pegawai'] === 'Support System') ? 'selected' : '' ?>>Support System / Staff</option>
                    </select>

                    <?php if (!empty($hierarkiFilters['unit']) || !empty($hierarkiFilters['jenis_pegawai'])): ?>
                        <a href="<?= url('kelola-cuti?tab=hierarki') ?>" class="text-xs font-bold text-rose-600 hover:underline">
                            ✕ Reset Filter
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <span class="text-xs text-slate-500 font-medium">
                Ditemukan: <strong><?= count($hierarkiList) ?></strong> Tahapan Aktif
            </span>
        </div>

        <?php
        // Pisahkan hierarki berdasarkan jenis pegawai
        $stepsGuru = [];
        $stepsSupport = [];
        $stepsLainnya = [];

        foreach ($hierarkiList as $h) {
            if ($h['jenis_pegawai'] === 'Guru') {
                $stepsGuru[] = $h;
            } elseif ($h['jenis_pegawai'] === 'Support System') {
                $stepsSupport[] = $h;
            } else {
                $stepsLainnya[] = $h;
            }
        }
        ?>

        <!-- ═════════════════════════════════════════════════════════════════
             BAGAN HIERARKI VISUAL (ORGANIZATIONAL TREE / FLOWCHART)
             ═════════════════════════════════════════════════════════════════ -->
        <div id="section-bagan-wrapper" class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            <!-- 🎓 BAGAN 1: HIERARKI PERSETUJUAN CUTI — PEGAWAI GURU -->
            <div id="container-bagan-guru" class="bagan-view-card bg-gradient-to-b from-sky-50/70 via-white to-slate-50/60 rounded-3xl border border-sky-200 shadow-sm p-5 sm:p-6 space-y-5">
                <!-- Header Bagan Guru -->
                <div class="flex items-start justify-between gap-3 pb-4 border-b border-sky-100">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-600 to-indigo-700 text-white flex items-center justify-center font-black text-2xl shadow-md shadow-sky-500/25 shrink-0">
                            🎓
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Bagan Approval — Pegawai Guru</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-100 text-sky-800 border border-sky-200 uppercase tracking-wider">Guru & Pendidik</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Alur persetujuan bertingkat bagi Guru Mapel, Wali Kelas, dan Tenaga Pengajar.</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl bg-sky-100/80 text-sky-800 font-extrabold text-xs shrink-0">
                        <?= count($stepsGuru) ?> Level Persetujuan
                    </span>
                </div>

                <!-- Flow Vertikal Bagan Guru -->
                <div class="flex flex-col items-center space-y-0 py-1">
                    
                    <!-- Node Awal: Permohonan Guru -->
                    <div class="w-full bg-white rounded-2xl border-2 border-dashed border-sky-300 p-4 shadow-xs flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-xl font-bold shrink-0">
                            🧑‍🏫
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-sky-600 bg-sky-50 px-2 py-0.5 rounded">Titik Awal Alur</span>
                                <span class="text-[10px] text-slate-400 font-semibold">Portal Guru</span>
                            </div>
                            <h5 class="text-xs font-black text-slate-800 mt-0.5">Guru Mengajukan Permohonan Cuti</h5>
                            <p class="text-[11px] text-slate-500">Memilih tanggal cuti (multi-hari), guru pengganti di kelas, & berkas pendukung.</p>
                        </div>
                    </div>

                    <!-- Connector Down Line -->
                    <div class="flex flex-col items-center py-1">
                        <div class="w-0.5 h-6 bg-sky-300"></div>
                        <div class="w-6 h-6 rounded-full bg-sky-100 border border-sky-300 text-sky-700 flex items-center justify-center text-[10px] font-black shadow-xs">
                            ▼
                        </div>
                        <div class="w-0.5 h-6 bg-sky-300"></div>
                    </div>

                    <!-- Loop Steps Guru -->
                    <?php if (empty($stepsGuru)): ?>
                        <div class="w-full bg-white rounded-2xl border border-dashed border-slate-300 p-6 text-center text-slate-400 space-y-2">
                            <p class="font-bold text-slate-700 text-xs">Belum ada tahapan persetujuan untuk Guru.</p>
                            <p class="text-[11px]">Klik tombol "+ Tambah Level Guru" di bawah untuk mengatur alur.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($stepsGuru as $gIdx => $st): ?>
                            <!-- Card Step Node Guru -->
                            <div class="w-full bg-white rounded-2xl border border-slate-200/90 hover:border-sky-400 p-4 sm:p-5 shadow-xs transition-all relative">
                                <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-sky-600 text-white font-black text-[10px] tracking-wide uppercase">
                                            Level <?= (int)$st['step_order'] ?>
                                        </span>
                                        <?php $isTipeApp = ($st['tipe_step'] ?? '') === 'Approval'; ?>
                                        <span class="px-2.5 py-0.5 rounded-lg font-extrabold text-[10px] <?= $isTipeApp ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-sky-100 text-sky-800 border border-sky-200' ?>">
                                            <?= $isTipeApp ? '✍️ Approval' : '🔍 Verifikasi' ?>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px]">
                                            Unit: <?= e($st['unit'] ?: 'Semua (Global)') ?>
                                        </span>
                                    </div>
                                    <?php if ((int)$st['is_active'] === 1): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-black bg-emerald-100 text-emerald-800">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-black bg-slate-100 text-slate-500">Nonaktif</span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-start gap-3.5">
                                    <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center text-xl font-bold shrink-0">
                                        <?= ((int)$st['step_order'] === 1) ? '🏫' : '🏛️' ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight">
                                            <?= e($st['step_nama']) ?>
                                        </h4>
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            <span class="text-[10.5px] text-slate-500 font-medium">Jabatan Penyetuju:</span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-sky-50 text-sky-900 border border-sky-200 text-xs font-extrabold">
                                                <span>🏷️</span> <?= e($st['approver_role'] ?: 'Kepala Sekolah') ?>
                                                <?php if (!empty($st['approver_jabatan_id']) || !empty($st['master_jabatan_nama'])): ?>
                                                    <span class="text-[9.5px] font-bold text-sky-600 bg-sky-100 px-1.5 py-0.2 rounded-md">Dari Pengaturan</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($st['keterangan'])): ?>
                                            <p class="text-[11px] text-slate-600 mt-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100 italic leading-relaxed">
                                                "<?= e($st['keterangan']) ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                    <button type="button" onclick="openModalTambahHierarki('Guru', <?= (int)$st['step_order'] + 1 ?>, '<?= e($st['unit']) ?>')"
                                        class="text-[11px] font-bold text-sky-600 hover:text-sky-800 flex items-center gap-1 cursor-pointer">
                                        <span>+ Sisipkan Level</span>
                                    </button>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" onclick="openModalEditHierarki(<?= htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="px-2.5 py-1 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold text-[11px] cursor-pointer">
                                            Edit
                                        </button>
                                        <form method="POST" action="<?= url('kelola-cuti/hierarki/delete') ?>" onsubmit="return confirm('Hapus tahapan Level <?= (int)$st['step_order'] ?> untuk Guru ini?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$st['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[11px] cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Connector Down Line to Next Step or Finish -->
                            <div class="flex flex-col items-center py-1">
                                <div class="w-0.5 h-6 bg-sky-300"></div>
                                <div class="w-6 h-6 rounded-full bg-sky-100 border border-sky-300 text-sky-700 flex items-center justify-center text-[10px] font-black shadow-xs">
                                    ▼
                                </div>
                                <div class="w-0.5 h-6 bg-sky-300"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Node Akhir: Izin Cuti Diterbitkan -->
                    <div class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-4 text-white shadow-md shadow-emerald-600/20 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center text-xl font-bold shrink-0">
                            ✓
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-200 bg-white/10 px-2 py-0.5 rounded">Tahap Akhir (Selesai)</span>
                                <span class="text-[10px] text-emerald-100 font-bold">Resmi Terbit</span>
                            </div>
                            <h5 class="text-xs font-black mt-0.5">Cuti Guru Disetujui & Diterbitkan</h5>
                            <p class="text-[11px] text-emerald-100">Surat izin cuti resmi terbit otomatis & kuota saldo cuti tahunan langsung terpotong sistem.</p>
                        </div>
                    </div>

                    <!-- Tombol Tambah Level Baru Guru -->
                    <button type="button" onclick="openModalTambahHierarki('Guru', <?= count($stepsGuru) + 1 ?>)"
                        class="w-full mt-4 py-3 px-4 rounded-2xl border-2 border-dashed border-sky-300 hover:border-sky-500 bg-sky-50/50 hover:bg-sky-50 text-sky-700 font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>+ Tambah Level Persetujuan Guru Baru</span>
                    </button>
                </div>
            </div>

            <!-- 🏢 BAGAN 2: HIERARKI PERSETUJUAN CUTI — PEGAWAI SUPPORT SYSTEM -->
            <div id="container-bagan-support" class="bagan-view-card bg-gradient-to-b from-emerald-50/70 via-white to-slate-50/60 rounded-3xl border border-emerald-200 shadow-sm p-5 sm:p-6 space-y-5">
                <!-- Header Bagan Support System -->
                <div class="flex items-start justify-between gap-3 pb-4 border-b border-emerald-100">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center font-black text-2xl shadow-md shadow-emerald-500/25 shrink-0">
                            🏢
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Bagan Approval — Support System</h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">Staff / Operasional</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Alur persetujuan bagi Karyawan, Divisi Media & IT, Bisnis, Sosial, dan Operasional.</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl bg-emerald-100/80 text-emerald-800 font-extrabold text-xs shrink-0">
                        <?= count($stepsSupport) ?> Level Persetujuan
                    </span>
                </div>

                <!-- Flow Vertikal Bagan Support System -->
                <div class="flex flex-col items-center space-y-0 py-1">
                    
                    <!-- Node Awal: Permohonan Staff -->
                    <div class="w-full bg-white rounded-2xl border-2 border-dashed border-emerald-300 p-4 shadow-xs flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold shrink-0">
                            💼
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Titik Awal Alur</span>
                                <span class="text-[10px] text-slate-400 font-semibold">Portal Staff</span>
                            </div>
                            <h5 class="text-xs font-black text-slate-800 mt-0.5">Pegawai Support System Mengajukan Cuti</h5>
                            <p class="text-[11px] text-slate-500">Memilih tanggal cuti, koordinasi pelimpahan tugas divisi, & berkas keterangan.</p>
                        </div>
                    </div>

                    <!-- Connector Down Line -->
                    <div class="flex flex-col items-center py-1">
                        <div class="w-0.5 h-6 bg-emerald-300"></div>
                        <div class="w-6 h-6 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-700 flex items-center justify-center text-[10px] font-black shadow-xs">
                            ▼
                        </div>
                        <div class="w-0.5 h-6 bg-emerald-300"></div>
                    </div>

                    <!-- Loop Steps Support System -->
                    <?php if (empty($stepsSupport)): ?>
                        <div class="w-full bg-white rounded-2xl border border-dashed border-slate-300 p-6 text-center text-slate-400 space-y-2">
                            <p class="font-bold text-slate-700 text-xs">Belum ada tahapan persetujuan untuk Support System.</p>
                            <p class="text-[11px]">Klik tombol "+ Tambah Level Support System" di bawah untuk mengatur alur.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($stepsSupport as $sIdx => $st): ?>
                            <!-- Card Step Node Support System -->
                            <div class="w-full bg-white rounded-2xl border border-slate-200/90 hover:border-emerald-400 p-4 sm:p-5 shadow-xs transition-all relative">
                                <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-emerald-600 text-white font-black text-[10px] tracking-wide uppercase">
                                            Level <?= (int)$st['step_order'] ?>
                                        </span>
                                        <?php $isTipeApp = ($st['tipe_step'] ?? '') === 'Approval'; ?>
                                        <span class="px-2.5 py-0.5 rounded-lg font-extrabold text-[10px] <?= $isTipeApp ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                            <?= $isTipeApp ? '✍️ Approval' : '🔍 Verifikasi' ?>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px]">
                                            Unit: <?= e($st['unit'] ?: 'Semua (Global)') ?>
                                        </span>
                                    </div>
                                    <?php if ((int)$st['is_active'] === 1): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-black bg-emerald-100 text-emerald-800">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-black bg-slate-100 text-slate-500">Nonaktif</span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex items-start gap-3.5">
                                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-xl font-bold shrink-0">
                                        <?= ((int)$st['step_order'] === 1) ? '💼' : '👑' ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight">
                                            <?= e($st['step_nama']) ?>
                                        </h4>
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            <span class="text-[10.5px] text-slate-500 font-medium">Jabatan Penyetuju:</span>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-900 border border-emerald-200 text-xs font-extrabold">
                                                <span>🏷️</span> <?= e($st['approver_role'] ?: 'Manajer Divisi') ?>
                                                <?php if (!empty($st['approver_jabatan_id']) || !empty($st['master_jabatan_nama'])): ?>
                                                    <span class="text-[9.5px] font-bold text-emerald-600 bg-emerald-100 px-1.5 py-0.2 rounded-md">Dari Pengaturan</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($st['keterangan'])): ?>
                                            <p class="text-[11px] text-slate-600 mt-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100 italic leading-relaxed">
                                                "<?= e($st['keterangan']) ?>"
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                    <button type="button" onclick="openModalTambahHierarki('Support System', <?= (int)$st['step_order'] + 1 ?>, '<?= e($st['unit']) ?>')"
                                        class="text-[11px] font-bold text-emerald-600 hover:text-emerald-800 flex items-center gap-1 cursor-pointer">
                                        <span>+ Sisipkan Level</span>
                                    </button>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" onclick="openModalEditHierarki(<?= htmlspecialchars(json_encode($st), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-[11px] cursor-pointer">
                                            Edit
                                        </button>
                                        <form method="POST" action="<?= url('kelola-cuti/hierarki/delete') ?>" onsubmit="return confirm('Hapus tahapan Level <?= (int)$st['step_order'] ?> untuk Support System ini?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$st['id'] ?>">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[11px] cursor-pointer">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Connector Down Line to Next Step or Finish -->
                            <div class="flex flex-col items-center py-1">
                                <div class="w-0.5 h-6 bg-emerald-300"></div>
                                <div class="w-6 h-6 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-700 flex items-center justify-center text-[10px] font-black shadow-xs">
                                    ▼
                                </div>
                                <div class="w-0.5 h-6 bg-emerald-300"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Node Akhir: Izin Cuti Diterbitkan -->
                    <div class="w-full bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-4 text-white shadow-md shadow-emerald-600/20 flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-white/20 text-white flex items-center justify-center text-xl font-bold shrink-0">
                            ✓
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase tracking-wider text-emerald-200 bg-white/10 px-2 py-0.5 rounded">Tahap Akhir (Selesai)</span>
                                <span class="text-[10px] text-emerald-100 font-bold">Resmi Terbit</span>
                            </div>
                            <h5 class="text-xs font-black mt-0.5">Cuti Support System Disetujui & Diterbitkan</h5>
                            <p class="text-[11px] text-emerald-100">Surat izin cuti resmi terbit otomatis & kuota saldo cuti tahunan langsung terpotong sistem.</p>
                        </div>
                    </div>

                    <!-- Tombol Tambah Level Baru Support System -->
                    <button type="button" onclick="openModalTambahHierarki('Support System', <?= count($stepsSupport) + 1 ?>)"
                        class="w-full mt-4 py-3 px-4 rounded-2xl border-2 border-dashed border-emerald-300 hover:border-emerald-500 bg-emerald-50/50 hover:bg-emerald-50 text-emerald-700 font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>+ Tambah Level Persetujuan Support System Baru</span>
                    </button>
                </div>
            </div>

        </div>

        <?php if (!empty($stepsLainnya)): ?>
            <!-- Bagan Alur Global / Semua Pegawai (Jika Ada) -->
            <div id="container-bagan-lainnya" class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                        <h4 class="font-black text-slate-900 text-sm">Alur Global / Semua Pegawai (Aturan Umum)</h4>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                        <?= count($stepsLainnya) ?> Tahapan
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach ($stepsLainnya as $sl): ?>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="px-2 py-0.5 rounded bg-indigo-600 text-white font-black text-[10px]">Level <?= (int)$sl['step_order'] ?></span>
                                    <span class="text-[10px] text-slate-400 font-semibold"><?= e($sl['unit']) ?></span>
                                </div>
                                <div class="font-bold text-slate-900 text-xs mt-1"><?= e($sl['step_nama']) ?></div>
                                <div class="text-[10.5px] text-indigo-700 font-bold mt-1">Jabatan: <?= e($sl['approver_role']) ?></div>
                            </div>
                            <div class="mt-3 pt-2 border-t border-slate-200 flex items-center justify-end gap-1.5">
                                <button type="button" onclick="openModalEditHierarki(<?= htmlspecialchars(json_encode($sl), ENT_QUOTES, 'UTF-8') ?>)" class="text-[10.5px] font-bold text-indigo-600 hover:underline">
                                    Edit
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ═════════════════════════════════════════════════════════════════
             TABEL DETAIL MASTER TAHAPAN APPROVAL (COLLAPSIBLE / TOGGLEABLE)
             ═════════════════════════════════════════════════════════════════ -->
        <div id="container-tabel-master" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden mt-2">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
                <div>
                    <h4 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                        <span>📋</span>
                        <span>Tabel Master Tahapan Hierarki Approval</span>
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar lengkap konfigurasi tahapan approval beserta jabatan penyetuju dari pengaturan</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleTabelMaster()" id="btn-toggle-tabel"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <span id="label-toggle-tabel">Sembunyikan Tabel</span>
                    </button>
                </div>
            </div>

            <div id="tabel-master-body" class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4 text-center w-16">Urutan</th>
                            <th class="py-3.5 px-4">Nama Tahapan Approval</th>
                            <th class="py-3.5 px-4">Jenis Pegawai</th>
                            <th class="py-3.5 px-4">Unit Sekolah</th>
                            <th class="py-3.5 px-4">Jabatan Penyetuju (Dari Pengaturan)</th>
                            <th class="py-3.5 px-4">Panduan / SOP</th>
                            <th class="py-3.5 px-4 text-center w-24">Status</th>
                            <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($hierarkiList)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <p class="font-bold text-slate-700">Belum ada aturan hierarki approval.</p>
                                    <p class="text-xs mt-1">Klik "+ Tambah Tahapan Approval" atau "Reset Default" untuk memulai.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($hierarkiList as $h): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="w-7 h-7 rounded-xl <?= $h['jenis_pegawai'] === 'Guru' ? 'bg-sky-50 border-sky-200 text-sky-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' ?> border font-extrabold text-xs inline-flex items-center justify-center">
                                            <?= (int)$h['step_order'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-900">
                                        <div class="font-extrabold text-slate-900"><?= e($h['step_nama']) ?></div>
                                        <?php $isTipeApp = ($h['tipe_step'] ?? '') === 'Approval'; ?>
                                        <span class="inline-flex items-center gap-1 mt-0.5 px-2 py-0.2 rounded-md font-extrabold text-[9.5px] <?= $isTipeApp ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-sky-100 text-sky-800 border border-sky-200' ?>">
                                            <?= $isTipeApp ? '✍️ Approval' : '🔍 Verifikasi' ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-bold <?= $h['jenis_pegawai'] === 'Guru' ? 'bg-sky-50 text-sky-700 border border-sky-200' : ($h['jenis_pegawai'] === 'Support System' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200') ?>">
                                            <?= $h['jenis_pegawai'] === 'Guru' ? '🎓 Guru' : ($h['jenis_pegawai'] === 'Support System' ? '🏢 Support System' : 'Semua') ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= e($h['unit'] ?: 'Semua Unit') ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="inline-flex items-center gap-1 font-bold text-slate-900">
                                            <span>🏷️</span>
                                            <span><?= e($h['approver_role'] ?: 'Admin') ?></span>
                                            <?php if (!empty($h['approver_jabatan_id']) || !empty($h['master_jabatan_nama'])): ?>
                                                <span class="text-[9px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded">Master Jabatan</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-500 max-w-xs text-[11px]">
                                        <?= e($h['keterangan'] ?: '-') ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ((int)$h['is_active'] === 1): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Aktif</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button"
                                                onclick="openModalEditHierarki(<?= htmlspecialchars(json_encode($h), ENT_QUOTES, 'UTF-8') ?>)"
                                                class="px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[11px] cursor-pointer">
                                                Edit
                                            </button>
                                            <form method="POST" action="<?= url('kelola-cuti/hierarki/delete') ?>" onsubmit="return confirm('Hapus tahapan ini?');" class="inline">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-[11px] cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 1: INPUT / BUAT PENGAJUAN CUTI PEGAWAI
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalAjukanCuti" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Input Pengajuan Cuti Pegawai</h3>
                <p class="text-xs text-slate-500">Pilih pegawai dan jenis cuti (Cuti Tahunan atau Cuti Khusus)</p>
            </div>
            <button type="button" onclick="closeModal('modalAjukanCuti')" class="p-2 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                ✕
            </button>
        </div>

        <form method="POST" action="<?= url('kelola-cuti/pengajuan/store') ?>" enctype="multipart/form-data" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" id="ajukan_return_tab" value="<?= e($activeTab) ?>">

            <!-- Pilih Pegawai -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" id="ajukan_pegawai_id" required onchange="updateInfoSaldoPegawai()"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                    <option value="">-- Pilih Pegawai --</option>
                    <?php foreach ($allPegawaiDropdown as $pg):
                        $nm = $pg['nama'] . (!empty($pg['gelar']) ? ', ' . $pg['gelar'] : '');
                    ?>
                        <option value="<?= (int)$pg['id'] ?>" data-saldo="<?= (int)$pg['saldo_tahunan'] ?>" data-unit="<?= e($pg['unit_tugas'] ?: '-') ?>">
                            <?= e($nm) ?> (<?= e($pg['unit_tugas'] ?: '-') ?> — Sisa Cuti Tahunan: <?= (int)$pg['saldo_tahunan'] ?> Hari)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="info_saldo_box" class="hidden mt-2 p-2.5 rounded-xl bg-sky-50 border border-sky-200 text-xs text-sky-800 flex items-center justify-between">
                    <span>Sisa Saldo Cuti Tahunan Pegawai Ini:</span>
                    <strong id="info_saldo_angka" class="text-sm font-extrabold text-sky-900">0 / <?= $maxAkumulasi ?> Hari</strong>
                </div>
            </div>

            <!-- Pilih Jenis Cuti -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Cuti <span class="text-rose-500">*</span></label>
                <select name="jenis_cuti_id" id="ajukan_jenis_cuti_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                    <?php foreach ($jenisCutiAktif as $jc): ?>
                        <option value="<?= (int)$jc['id'] ?>"
                                data-kategori="<?= e($jc['kategori']) ?>"
                                data-potong="<?= (int)$jc['potong_kuota_tahunan'] ?>"
                                data-kuota="<?= (int)$jc['kuota_hari'] ?>">
                            [<?= e($jc['kategori']) ?>] <?= e($jc['nama_jenis']) ?>
                            <?= $jc['kategori'] === 'Tahunan' ? '(Potong Saldo Tahunan)' : '(Maks ' . (int)$jc['kuota_hari'] . ' Hari)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tanggal Cuti Spesifik (Pilih Satu per Satu / Melewati Hari Libur) -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700">
                        Pilih Tanggal Cuti <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Pilih per tanggal untuk melewati hari libur</span>
                </div>
                <div class="flex items-center gap-2">
                    <input type="date" id="admin_picker_tgl_cuti" value="<?= date('Y-m-d') ?>" onchange="adminTambahTanggalCuti()"
                           class="flex-1 px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                    <button type="button" onclick="adminTambahTanggalCuti()"
                            class="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-sm flex items-center gap-1 shrink-0 cursor-pointer">
                        <span>+ Tambah Tanggal</span>
                    </button>
                </div>

                <!-- Kontainer Chips Tanggal Terpilih -->
                <div class="mt-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200 min-h-[48px]">
                    <div id="admin_container_chips_cuti" class="flex flex-wrap gap-1.5">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs px-1 mt-1 font-semibold text-slate-500">
                    <span>Total Cuti: <b id="admin_text_total_hari_cuti" class="text-primary-700 font-extrabold">1 Hari Kerja</b></span>
                    <button type="button" onclick="adminClearSemuaTanggalCuti()" class="text-rose-500 hover:underline text-[11px] cursor-pointer">✕ Kosongkan</button>
                </div>

                <!-- Hidden inputs passed on submit -->
                <input type="hidden" name="tanggal_pilihan" id="admin_input_tanggal_pilihan" value="">
                <input type="hidden" name="tanggal_mulai" id="ajukan_tgl_mulai" value="<?= date('Y-m-d') ?>">
                <input type="hidden" name="tanggal_selesai" id="ajukan_tgl_selesai" value="<?= date('Y-m-d') ?>">
                <input type="hidden" name="jumlah_hari" id="ajukan_jumlah_hari" value="1">
            </div>

            <!-- Alasan Cuti -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan / Keperluan Cuti <span class="text-rose-500">*</span></label>
                <textarea name="alasan" rows="2" required placeholder="Tuliskan alasan dan keperluan pengajuan cuti..."
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500"></textarea>
            </div>

            <!-- Pelimpahan Tugas & Kontak -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pelimpahan Tugas / Guru Pengganti</label>
                    <input type="text" name="pelimpahan_tugas" placeholder="Nama rekan pengganti tugas..."
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / Kontak Selama Cuti</label>
                    <input type="text" name="kontak_darurat" placeholder="Contoh: 0812xxxx"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500">
                </div>
            </div>

            <!-- Upload Lampiran & Langsung Approve -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lampiran Surat / Bukti (Opsional)</label>
                    <input type="file" name="file_lampiran" accept=".pdf,.jpg,.jpeg,.png,.webp"
                           class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700">
                </div>
                <div class="pt-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none bg-emerald-50 px-3 py-2 rounded-xl border border-emerald-200 w-full">
                        <input type="checkbox" name="langsung_approve" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs font-bold text-emerald-900">Langsung Setujui (Auto-Approve)</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalAjukanCuti')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md cursor-pointer">
                    Simpan Pengajuan Cuti
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 2: APPROVE / TOLAK / BATALKAN PENGAJUAN CUTI
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalApproveCuti" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 id="approve_modal_title" class="text-base font-extrabold text-slate-900">Konfirmasi Persetujuan Cuti</h3>
            <button type="button" onclick="closeModal('modalApproveCuti')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-cuti/pengajuan/approve') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="pengajuan_id" id="approve_pengajuan_id">
            <input type="hidden" name="keputusan" id="approve_keputusan">
            <input type="hidden" name="return_tab" id="approve_return_tab" value="approve">
            <input type="hidden" name="step_order" id="approve_step_order" value="">
            <input type="hidden" name="approve_all" id="approve_all_flag" value="">

            <div id="approve_info_banner" class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                <div>Pegawai: <strong id="approve_nama_pegawai" class="text-slate-900"></strong></div>
                <div>Jenis Cuti: <strong id="approve_jenis_cuti" class="text-slate-900"></strong> (<span id="approve_jumlah_hari" class="font-bold"></span> Hari)</div>
                <div class="pt-1.5 border-t border-slate-200/80 text-[11px] text-slate-500 flex items-center justify-between">
                    <span>Pejabat Penyetuju (Akun Login):</span>
                    <strong class="text-slate-900"><?= e(Auth::name()) ?><?= !empty($currentJabatanNama) ? ' (' . e($currentJabatanNama) . ')' : '' ?></strong>
                </div>
            </div>

            <!-- Box Tahapan Approval Hierarki -->
            <div id="approve_step_box" class="hidden p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200 text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-extrabold text-indigo-950 flex items-center gap-1.5">
                        <span>⏳</span> <span id="approve_step_badge">Tahap 1 / 2</span>
                    </span>
                    <span id="approve_step_title" class="text-[11px] font-bold text-indigo-700"></span>
                </div>
                <div id="approve_steps_chain" class="space-y-1 pt-1.5 border-t border-indigo-200/70"></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Persetujuan / Penolakan</label>
                <textarea name="catatan_approval" id="approve_catatan" rows="2" placeholder="Tambahkan catatan verifikasi (opsional)..."
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500"></textarea>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalApproveCuti')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer order-last sm:order-first">
                    Batal
                </button>
                <button type="submit" id="approve_bypass_btn" onclick="document.getElementById('approve_all_flag').value = '1';" class="hidden px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm cursor-pointer">
                    ⚡ Setujui Final Seluruh Tahapan
                </button>
                <button type="submit" id="approve_submit_btn" onclick="document.getElementById('approve_all_flag').value = '';" class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 3: SESUAIKAN SALDO CUTI TAHUNAN PEGAWAI (MAKS 36 HARI)
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalAdjustSaldo" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Sesuaikan Saldo Cuti Tahunan</h3>
                <p class="text-xs text-slate-500">Perubahan saldo akan otomatis tercatat di Log Pengurangan / Mutasi Cuti</p>
            </div>
            <button type="button" onclick="closeModal('modalAdjustSaldo')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-cuti/saldo/adjust') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="pegawai_id" id="adjust_pegawai_id">
            <input type="hidden" name="return_tab" value="pegawai">

            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs">
                Pegawai: <strong id="adjust_nama_pegawai" class="text-slate-900"></strong>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Saldo Cuti Tahunan Baru (0 s.d. <?= $maxAkumulasi ?> Hari) <span class="text-rose-500">*</span></label>
                <input type="number" name="saldo_tahunan" id="adjust_saldo_input" min="0" max="<?= $maxAkumulasi ?>" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-extrabold text-slate-900 outline-none focus:border-primary-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Alasan Penyesuaian <span class="text-rose-500">*</span></label>
                <input type="text" name="keterangan" required placeholder="Contoh: Penyesuaian saldo awal / Pemotongan cuti bersama..."
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalAdjustSaldo')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Simpan Saldo
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 4: TAMBAH / EDIT JENIS CUTI KHUSUS
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalJenisCuti" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 id="jenis_modal_title" class="text-base font-extrabold text-slate-900">Tambah Jenis Cuti Khusus</h3>
                <p class="text-xs text-slate-500">Konfigurasi jenis cuti khusus pegawai beserta kuota maksimal harinya</p>
            </div>
            <button type="button" onclick="closeModal('modalJenisCuti')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-cuti/jenis/store') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="jenis_id" value="">
            <input type="hidden" name="kategori" id="jenis_kategori" value="Khusus">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Cuti</label>
                    <input type="text" name="kode" id="jenis_kode" placeholder="CK-KHUSUS"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-800 outline-none focus:border-primary-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Jenis Cuti Khusus <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_jenis" id="jenis_nama" required placeholder="Contoh: Cuti Melahirkan / Cuti Pernikahan Anak"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Batas Maksimal Hari <span class="text-rose-500">*</span></label>
                    <input type="number" name="kuota_hari" id="jenis_kuota_hari" value="3" min="1" max="365" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-extrabold text-slate-900 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Aktif</label>
                    <select name="is_active" id="jenis_is_active" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                        <option value="1">Aktif (Tampil di Form Pengajuan)</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                    <input type="checkbox" name="potong_kuota_tahunan" id="jenis_potong" value="1" class="rounded text-primary-600">
                    <span class="text-xs font-semibold text-slate-700">Memotong Saldo Cuti Tahunan</span>
                </label>
                <label class="flex items-center gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                    <input type="checkbox" name="butuh_lampiran" id="jenis_lampiran" value="1" class="rounded text-primary-600">
                    <span class="text-xs font-semibold text-slate-700">Wajib Upload Lampiran/Surat</span>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Ketentuan</label>
                <textarea name="keterangan" id="jenis_keterangan" rows="2" placeholder="Penjelasan ketentuan cuti khusus ini..."
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalJenisCuti')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Simpan Jenis Cuti
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 5: TAMBAH / EDIT STEP HIERARKI APPROVAL (CUSTOM WORKFLOW)
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalHierarkiStep" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <div>
                <h3 id="hierarki_modal_title" class="text-base font-extrabold text-slate-900">Tambah Level Approval</h3>
                <p class="text-xs text-slate-500">Konfigurasi urutan langkah dan pihak penyetuju dari Master Jabatan</p>
            </div>
            <button type="button" onclick="closeModal('modalHierarkiStep')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-cuti/hierarki/store') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" id="hierarki_id" value="">
            <input type="hidden" name="approver_jabatan_id" id="hierarki_approver_jabatan_id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pegawai <span class="text-rose-500">*</span></label>
                    <select name="jenis_pegawai" id="hierarki_jenis_pegawai" required onchange="onJenisPegawaiFormChange(this)"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 focus:bg-white outline-none focus:border-indigo-500">
                        <option value="Guru">🎓 Pegawai Guru (Pendidik)</option>
                        <option value="Support System">🏢 Support System / Staff</option>
                        <option value="Semua">Semua (Guru & Staff)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unit Sekolah <span class="text-rose-500">*</span></label>
                    <select name="unit" id="hierarki_unit" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 focus:bg-white outline-none focus:border-indigo-500">
                        <option value="Semua">Semua Unit (Global)</option>
                        <?php foreach ($unitTugasList as $ut): ?>
                            <option value="<?= e($ut['unit_tugas']) ?>"><?= e($ut['unit_tugas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Pilihan Tipe Tahapan Level: Verifikasi vs Approval -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Tahapan Level <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-2.5" id="hierarki_tipe_step_container">
                    <label id="label_tipe_verifikasi" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-sky-400 bg-sky-50 text-sky-900 font-extrabold text-xs cursor-pointer transition-all shadow-xs">
                        <input type="radio" name="tipe_step" id="tipe_step_verifikasi" value="Verifikasi" checked onchange="onTipeStepRadioChange('Verifikasi')" class="hidden">
                        <span>🔍 Verifikasi</span>
                    </label>
                    <label id="label_tipe_approval" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-bold text-xs cursor-pointer transition-all hover:bg-slate-50">
                        <input type="radio" name="tipe_step" id="tipe_step_approval" value="Approval" onchange="onTipeStepRadioChange('Approval')" class="hidden">
                        <span>✍️ Approval (Persetujuan)</span>
                    </label>
                </div>
                <p class="text-[10px] text-slate-400 mt-1">Pilih apakah tahapan ini berupa pemeriksaan kelengkapan (Verifikasi) atau keputusan persetujuan akhir (Approval).</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Urutan Level <span class="text-rose-500">*</span></label>
                    <input type="number" name="step_order" id="hierarki_step_order" min="1" max="10" required value="1"
                           class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-extrabold text-slate-800 outline-none focus:border-indigo-500 text-center">
                    <p class="text-[10px] text-slate-400 mt-0.5">Tingkat ke: 1, 2, 3...</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Jabatan Penyetuju (Dari Pengaturan) <span class="text-rose-500">*</span></label>
                    <select id="hierarki_jabatan_select" onchange="onJabatanPresetChange(this)"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 focus:bg-white outline-none focus:border-indigo-500">
                        <option value="">-- Pilih Jabatan Pengaturan --</option>
                        <optgroup label="📋 Jabatan Guru (Pengaturan Master)">
                            <?php foreach ($masterJabatanGuru as $jg): ?>
                                <option value="<?= e($jg['nama']) ?>" data-id="<?= (int)$jg['id'] ?>" data-kategori="guru">
                                    🎓 <?= e($jg['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="💼 Jabatan Support System (Pengaturan Master)">
                            <?php foreach ($masterJabatanSupport as $js): ?>
                                <option value="<?= e($js['nama']) ?>" data-id="<?= (int)$js['id'] ?>" data-kategori="support_system">
                                    🏢 <?= e($js['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>

            <!-- Nama Tahapan & Input Role -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Tahapan Approval <span class="text-rose-500">*</span></label>
                <input type="text" name="step_nama" id="hierarki_step_nama" required placeholder="Contoh: Verifikasi Kepala Sekolah / Unit"
                       class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500">
            </div>

            <!-- Teks Role Penyetuju -->
            <div id="hierarki_custom_role_container">
                <label class="block text-xs font-bold text-slate-700 mb-1">Label Jabatan / Pihak Penyetuju <span class="text-rose-500">*</span></label>
                <input type="text" name="approver_role" id="hierarki_approver_role" required placeholder="Contoh: Kepala Sekolah / Manajer Divisi / Yayasan"
                       class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 outline-none focus:border-indigo-500">
                <p class="text-[10px] text-slate-400 mt-0.5">Teks jabatan penyetuju yang berwenang (terisi otomatis dari dropdown jabatan).</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Panduan Verifikasi</label>
                <textarea name="keterangan" id="hierarki_keterangan" rows="2" placeholder="Catatan panduan / SOP untuk pejabat penyetuju..."
                          class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-indigo-500"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="hierarki_is_active" value="1" checked class="w-4 h-4 rounded text-indigo-600">
                <label for="hierarki_is_active" class="text-xs font-bold text-slate-700 cursor-pointer">Tahapan ini Aktif</label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3.5 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalHierarkiStep')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Simpan Tahapan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function switchCutiTab(tabName) {
    document.querySelectorAll('.cuti-tab-panel').forEach(el => el.classList.add('hidden'));
    const targetPanel = document.getElementById('cuti-panel-' + tabName);
    if (targetPanel) {
        targetPanel.classList.remove('hidden');
    }

    document.querySelectorAll('.cuti-tab-btn').forEach(btn => {
        const isMatch = btn.getAttribute('data-tab-btn') === tabName;
        btn.className = 'cuti-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer ' +
            (isMatch ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900');
    });

    document.querySelectorAll('.cuti-sidebar-link').forEach(link => {
        const isMatch = link.getAttribute('data-sidebar-tab') === tabName;
        if (isMatch) {
            link.classList.add('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
            link.classList.remove('text-primary-200', 'font-medium', 'border-transparent');
        } else {
            link.classList.remove('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
            link.classList.add('text-primary-200', 'font-medium', 'border-transparent');
        }
    });

    const urlObj = new URL(window.location.href);
    urlObj.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', urlObj.toString());
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
    }
}

let adminSelectedCutiDates = ['<?= date('Y-m-d') ?>'];

function adminFormatTglIndo(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    const hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][d.getDay()] || '';
    const bln = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][d.getMonth()] || '';
    return `${hari}, ${d.getDate()} ${bln} ${d.getFullYear()}`;
}

function adminRenderCutiChips() {
    const container = document.getElementById('admin_container_chips_cuti');
    const badge = document.getElementById('admin_text_total_hari_cuti');
    const inPilihan = document.getElementById('admin_input_tanggal_pilihan');
    const inMulai = document.getElementById('ajukan_tgl_mulai');
    const inSelesai = document.getElementById('ajukan_tgl_selesai');
    const inJumlah = document.getElementById('ajukan_jumlah_hari');

    if (!container) return;

    if (!Array.isArray(adminSelectedCutiDates)) adminSelectedCutiDates = [];
    adminSelectedCutiDates.sort();

    if (adminSelectedCutiDates.length === 0) {
        container.innerHTML = '<span class="text-slate-400 italic text-[11px] py-1 px-1">Pilih tanggal di atas & klik "+ Tambah Tanggal".</span>';
        if (badge) badge.textContent = '0 Hari Kerja';
        if (inPilihan) inPilihan.value = '[]';
        if (inJumlah) inJumlah.value = 0;
        return;
    }

    container.innerHTML = adminSelectedCutiDates.map(dStr => {
        const label = adminFormatTglIndo(dStr);
        return `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-white border border-primary-200 text-primary-900 shadow-2xs">
                <span>📅 ${label}</span>
                <button type="button" onclick="adminHapusTanggalCuti('${dStr}')" class="text-slate-400 hover:text-rose-600 font-extrabold ml-0.5 cursor-pointer" title="Hapus">✕</button>
            </span>
        `;
    }).join('');

    const count = adminSelectedCutiDates.length;
    if (badge) badge.textContent = `${count} Hari Kerja`;
    if (inPilihan) inPilihan.value = JSON.stringify(adminSelectedCutiDates);
    if (inMulai) inMulai.value = adminSelectedCutiDates[0];
    if (inSelesai) inSelesai.value = adminSelectedCutiDates[count - 1];
    if (inJumlah) inJumlah.value = count;
}

function adminTambahTanggalCuti() {
    const picker = document.getElementById('admin_picker_tgl_cuti');
    if (!picker || !picker.value) return;
    const val = picker.value.trim();
    if (!adminSelectedCutiDates) adminSelectedCutiDates = [];
    if (adminSelectedCutiDates.includes(val)) {
        alert('Tanggal tersebut sudah ada di daftar cuti.');
        return;
    }
    adminSelectedCutiDates.push(val);
    adminRenderCutiChips();
}

function adminHapusTanggalCuti(val) {
    if (!adminSelectedCutiDates) return;
    adminSelectedCutiDates = adminSelectedCutiDates.filter(d => d !== val);
    adminRenderCutiChips();
}

function adminClearSemuaTanggalCuti() {
    adminSelectedCutiDates = [];
    adminRenderCutiChips();
}

function openModalAjukanCuti(pegawaiId = null) {
    const modal = document.getElementById('modalAjukanCuti');
    const sel = document.getElementById('ajukan_pegawai_id');
    if (pegawaiId && sel) {
        sel.value = String(pegawaiId);
    }
    const today = new Date().toISOString().split('T')[0];
    if (!adminSelectedCutiDates || adminSelectedCutiDates.length === 0) {
        adminSelectedCutiDates = [today];
    }
    adminRenderCutiChips();
    updateInfoSaldoPegawai();
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function updateInfoSaldoPegawai() {
    const sel = document.getElementById('ajukan_pegawai_id');
    const box = document.getElementById('info_saldo_box');
    const angka = document.getElementById('info_saldo_angka');
    if (!sel || !sel.value) {
        if (box) box.classList.add('hidden');
        return;
    }
    const opt = sel.options[sel.selectedIndex];
    const saldo = opt ? (opt.getAttribute('data-saldo') || '0') : '0';
    if (angka) angka.textContent = saldo + ' / <?= $maxAkumulasi ?> Hari';
    if (box) box.classList.remove('hidden');
}

function hitungDurasiCuti() {
    const d1 = document.getElementById('ajukan_tgl_mulai')?.value;
    const d2 = document.getElementById('ajukan_tgl_selesai')?.value;
    const out = document.getElementById('ajukan_jumlah_hari');
    if (d1 && d2 && out) {
        const t1 = new Date(d1);
        const t2 = new Date(d2);
        if (t2 >= t1) {
            const diffDays = Math.round((t2 - t1) / (1000 * 60 * 60 * 24)) + 1;
            out.value = diffDays;
        }
    }
}

function openModalApprove(id, keputusan, namaPegawai, namaJenis, jumlahHari, returnTab, stepOrder = 1, stepNama = '', totalSteps = 1, steps = []) {
    document.getElementById('approve_pengajuan_id').value = id;
    document.getElementById('approve_keputusan').value = keputusan;
    document.getElementById('approve_return_tab').value = returnTab || 'approve';
    document.getElementById('approve_nama_pegawai').textContent = namaPegawai;
    document.getElementById('approve_jenis_cuti').textContent = namaJenis;
    document.getElementById('approve_jumlah_hari').textContent = jumlahHari;

    document.getElementById('approve_step_order').value = stepOrder || '1';
    document.getElementById('approve_all_flag').value = '';

    const stepBox = document.getElementById('approve_step_box');
    const stepBadge = document.getElementById('approve_step_badge');
    const stepTitle = document.getElementById('approve_step_title');
    const stepsChain = document.getElementById('approve_steps_chain');
    const bypassBtn = document.getElementById('approve_bypass_btn');
    const submitBtn = document.getElementById('approve_submit_btn');
    const title = document.getElementById('approve_modal_title');

    if (totalSteps > 1 && stepNama) {
        stepBox.classList.remove('hidden');
        stepBadge.textContent = `Tahap ${stepOrder} dari ${totalSteps}`;
        stepTitle.textContent = stepNama;

        // Render rantai tahapan approval
        let chainHtml = '';
        if (Array.isArray(steps) && steps.length > 0) {
            chainHtml = steps.map(s => {
                const isCurrent = parseInt(s.step_order) === parseInt(stepOrder);
                const isDone = s.status === 'Disetujui';
                const isRejected = s.status === 'Ditolak';

                let icon = '⚪';
                let textColor = 'text-slate-500';
                if (isDone) { icon = '✓'; textColor = 'text-emerald-700 font-bold'; }
                else if (isRejected) { icon = '✕'; textColor = 'text-rose-700 font-bold'; }
                else if (isCurrent) { icon = '⏳'; textColor = 'text-indigo-900 font-black'; }

                let approverNote = '';
                if (s.approved_by_name) approverNote = ` — disetujui oleh ${s.approved_by_name}`;

                return `<div class="flex items-center justify-between text-[11px] py-0.5 ${textColor}">
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="w-4 text-center shrink-0">${icon}</span>
                        <span class="truncate">Step ${s.step_order}: ${s.step_nama} (${s.approver_role || 'Penyetuju'})${approverNote}</span>
                    </div>
                    <span class="text-[10px] uppercase font-bold shrink-0 ml-1">${s.status}</span>
                </div>`;
            }).join('');
        }
        stepsChain.innerHTML = chainHtml;
    } else {
        stepBox.classList.add('hidden');
    }

    if (keputusan === 'Disetujui') {
        const isVerif = (stepNama || '').toLowerCase().includes('verifikasi');
        if (isVerif) {
            title.textContent = totalSteps > 1 ? `Verifikasi Tahap ${stepOrder}: ${stepNama}` : 'Verifikasi Pengajuan Cuti';
            submitBtn.textContent = (totalSteps > 1 && stepOrder < totalSteps)
                ? `Ya, Verifikasi Tahap ${stepOrder} & Lanjut`
                : 'Ya, Verifikasi & Setujui Final';
            submitBtn.className = 'px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-sm cursor-pointer';
        } else {
            title.textContent = totalSteps > 1 ? `Setujui Tahap ${stepOrder}: ${stepNama}` : 'Setujui (Approve) Pengajuan Cuti';
            submitBtn.textContent = (totalSteps > 1 && stepOrder < totalSteps)
                ? `Ya, Setujui Tahap ${stepOrder} & Lanjut`
                : 'Ya, Setujui Final & Potong Saldo';
            submitBtn.className = 'px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm cursor-pointer';
        }

        if (totalSteps > 1 && stepOrder < totalSteps) {
            bypassBtn.classList.remove('hidden');
        } else {
            bypassBtn.classList.add('hidden');
        }
    } else if (keputusan === 'Ditolak') {
        title.textContent = 'Tolak Pengajuan Cuti';
        submitBtn.textContent = 'Ya, Tolak Pengajuan';
        submitBtn.className = 'px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm cursor-pointer';
        bypassBtn.classList.add('hidden');
    } else {
        title.textContent = 'Batalkan Cuti & Kembalikan Saldo';
        submitBtn.textContent = 'Ya, Batalkan Cuti';
        submitBtn.className = 'px-5 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs shadow-sm cursor-pointer';
        bypassBtn.classList.add('hidden');
    }

    const modal = document.getElementById('modalApproveCuti');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function switchBaganView(mode) {
    const colGuru = document.getElementById('container-bagan-guru');
    const colSupport = document.getElementById('container-bagan-support');
    const colLainnya = document.getElementById('container-bagan-lainnya');
    const tblMaster = document.getElementById('container-tabel-master');
    const secWrapper = document.getElementById('section-bagan-wrapper');

    const btnAll = document.getElementById('btn-bagan-all');
    const btnGuru = document.getElementById('btn-bagan-guru');
    const btnSupport = document.getElementById('btn-bagan-support');
    const btnTable = document.getElementById('btn-bagan-table');

    [btnAll, btnGuru, btnSupport, btnTable].forEach(b => {
        if (!b) return;
        b.className = 'px-3 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 transition-all cursor-pointer';
    });

    const activeBtnClass = 'px-3 py-1.5 rounded-lg bg-white text-indigo-700 shadow-xs transition-all cursor-pointer';

    if (mode === 'all') {
        if (btnAll) btnAll.className = activeBtnClass;
        if (secWrapper) {
            secWrapper.classList.remove('hidden');
            secWrapper.className = 'grid grid-cols-1 lg:grid-cols-2 gap-6 items-start';
        }
        if (colGuru) colGuru.classList.remove('hidden');
        if (colSupport) colSupport.classList.remove('hidden');
        if (colLainnya) colLainnya.classList.remove('hidden');
        if (tblMaster) tblMaster.classList.remove('hidden');
    } else if (mode === 'guru') {
        if (btnGuru) btnGuru.className = activeBtnClass;
        if (secWrapper) {
            secWrapper.classList.remove('hidden');
            secWrapper.className = 'grid grid-cols-1 gap-6 items-start';
        }
        if (colGuru) colGuru.classList.remove('hidden');
        if (colSupport) colSupport.classList.add('hidden');
        if (colLainnya) colLainnya.classList.add('hidden');
        if (tblMaster) tblMaster.classList.add('hidden');
    } else if (mode === 'support') {
        if (btnSupport) btnSupport.className = activeBtnClass;
        if (secWrapper) {
            secWrapper.classList.remove('hidden');
            secWrapper.className = 'grid grid-cols-1 gap-6 items-start';
        }
        if (colGuru) colGuru.classList.add('hidden');
        if (colSupport) colSupport.classList.remove('hidden');
        if (colLainnya) colLainnya.classList.add('hidden');
        if (tblMaster) tblMaster.classList.add('hidden');
    } else if (mode === 'table') {
        if (btnTable) btnTable.className = activeBtnClass;
        if (secWrapper) secWrapper.classList.add('hidden');
        if (colLainnya) colLainnya.classList.add('hidden');
        if (tblMaster) {
            tblMaster.classList.remove('hidden');
            const tbBody = document.getElementById('tabel-master-body');
            if (tbBody) tbBody.classList.remove('hidden');
            const lbl = document.getElementById('label-toggle-tabel');
            if (lbl) lbl.textContent = 'Sembunyikan Tabel';
        }
    }
}

function toggleTabelMaster() {
    const tbBody = document.getElementById('tabel-master-body');
    const lbl = document.getElementById('label-toggle-tabel');
    if (!tbBody) return;
    if (tbBody.classList.contains('hidden')) {
        tbBody.classList.remove('hidden');
        if (lbl) lbl.textContent = 'Sembunyikan Tabel';
    } else {
        tbBody.classList.add('hidden');
        if (lbl) lbl.textContent = 'Buka Tabel Master';
    }
}

function onTipeStepRadioChange(tipe) {
    const lblVerif = document.getElementById('label_tipe_verifikasi');
    const lblApp = document.getElementById('label_tipe_approval');
    const radioVerif = document.getElementById('tipe_step_verifikasi');
    const radioApp = document.getElementById('tipe_step_approval');

    if (tipe === 'Approval') {
        if (radioApp) radioApp.checked = true;
        if (lblApp) {
            lblApp.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-purple-500 bg-purple-50 text-purple-900 font-extrabold text-xs cursor-pointer transition-all shadow-xs';
        }
        if (lblVerif) {
            lblVerif.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-bold text-xs cursor-pointer transition-all hover:bg-slate-50';
        }
    } else {
        if (radioVerif) radioVerif.checked = true;
        if (lblVerif) {
            lblVerif.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-sky-400 bg-sky-50 text-sky-900 font-extrabold text-xs cursor-pointer transition-all shadow-xs';
        }
        if (lblApp) {
            lblApp.className = 'flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-bold text-xs cursor-pointer transition-all hover:bg-slate-50';
        }
    }

    // Auto update nama tahapan jika jabatan sudah dipilih
    const jabSelect = document.getElementById('hierarki_jabatan_select');
    const stepNamaInput = document.getElementById('hierarki_step_nama');
    if (jabSelect && jabSelect.value && stepNamaInput) {
        stepNamaInput.value = (tipe === 'Approval' ? 'Approval ' : 'Verifikasi ') + jabSelect.value;
    }
}

function setTipeStepRadio(tipe) {
    onTipeStepRadioChange(tipe === 'Approval' ? 'Approval' : 'Verifikasi');
}

function onJabatanPresetChange(sel) {
    const val = sel.value;
    const opt = sel.selectedOptions[0];
    const roleInput = document.getElementById('hierarki_approver_role');
    const jabIdInput = document.getElementById('hierarki_approver_jabatan_id');
    const stepNamaInput = document.getElementById('hierarki_step_nama');

    // Ambil status radio tipe_step saat ini
    const radioApp = document.getElementById('tipe_step_approval');
    const activeTipe = (radioApp && radioApp.checked) ? 'Approval' : 'Verifikasi';

    if (val) {
        const jId = opt ? (opt.dataset.id || '') : '';
        if (roleInput) {
            roleInput.value = val;
            roleInput.readOnly = true;
        }
        if (jabIdInput) jabIdInput.value = jId;

        // Otomatis sarankan nama tahapan sesuai tipe dan jabatan
        if (stepNamaInput) {
            stepNamaInput.value = (activeTipe === 'Approval' ? 'Approval ' : 'Verifikasi ') + val;
        }
    } else {
        if (roleInput) roleInput.value = '';
        if (jabIdInput) jabIdInput.value = '';
    }
}

function onJenisPegawaiFormChange(sel) {
    const jenis = sel.value;
    const jabSelect = document.getElementById('hierarki_jabatan_select');
    if (!jabSelect) return;

    // Filter optgroup visibility agar relevan (Guru vs Support System)
    const optgroups = jabSelect.querySelectorAll('optgroup');
    optgroups.forEach(og => {
        const lbl = og.getAttribute('label') || '';
        if (jenis === 'Guru') {
            og.style.display = lbl.includes('Guru') ? '' : 'none';
        } else if (jenis === 'Support System') {
            og.style.display = lbl.includes('Support') ? '' : 'none';
        } else {
            og.style.display = '';
        }
    });
}

function openModalTambahHierarki(jenisPegawai = 'Guru', stepOrder = 1, unit = 'Semua') {
    document.getElementById('hierarki_id').value = '';
    document.getElementById('hierarki_unit').value = unit || 'Semua';
    document.getElementById('hierarki_jenis_pegawai').value = jenisPegawai || 'Guru';
    document.getElementById('hierarki_step_order').value = stepOrder || '1';
    document.getElementById('hierarki_step_nama').value = '';
    document.getElementById('hierarki_approver_role').value = '';
    document.getElementById('hierarki_approver_jabatan_id').value = '';
    document.getElementById('hierarki_keterangan').value = '';
    document.getElementById('hierarki_is_active').checked = true;
    document.getElementById('hierarki_modal_title').textContent = 'Tambah Level Approval Baru (' + (jenisPegawai || 'Guru') + ')';

    // Default tipe: jika step 1 -> Verifikasi, jika step >= 2 -> Approval
    const defaultTipe = (parseInt(stepOrder) > 1) ? 'Approval' : 'Verifikasi';
    setTipeStepRadio(defaultTipe);

    const jenisSel = document.getElementById('hierarki_jenis_pegawai');
    if (jenisSel) onJenisPegawaiFormChange(jenisSel);

    const jabSelect = document.getElementById('hierarki_jabatan_select');
    if (jabSelect) jabSelect.value = '';

    const modal = document.getElementById('modalHierarkiStep');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openModalEditHierarki(data) {
    if (!data) return;
    document.getElementById('hierarki_id').value = data.id || '';
    document.getElementById('hierarki_unit').value = data.unit || 'Semua';
    document.getElementById('hierarki_jenis_pegawai').value = data.jenis_pegawai || 'Guru';
    document.getElementById('hierarki_step_order').value = data.step_order || '1';
    document.getElementById('hierarki_step_nama').value = data.step_nama || '';
    document.getElementById('hierarki_approver_role').value = data.approver_role || '';
    document.getElementById('hierarki_approver_jabatan_id').value = data.approver_jabatan_id || '';
    document.getElementById('hierarki_keterangan').value = data.keterangan || '';
    document.getElementById('hierarki_is_active').checked = parseInt(data.is_active) === 1;
    document.getElementById('hierarki_modal_title').textContent = 'Edit Level Persetujuan (' + (data.jenis_pegawai || 'Guru') + ')';

    // Set tipe step radio (Verifikasi vs Approval)
    setTipeStepRadio(data.tipe_step || 'Verifikasi');

    const jenisSel = document.getElementById('hierarki_jenis_pegawai');
    if (jenisSel) onJenisPegawaiFormChange(jenisSel);

    // Cocokkan dropdown preset
    const jabSelect = document.getElementById('hierarki_jabatan_select');
    const roleInput = document.getElementById('hierarki_approver_role');
    if (jabSelect) {
        let found = false;
        // Cek by data-id
        if (data.approver_jabatan_id) {
            for (let i = 0; i < jabSelect.options.length; i++) {
                if (jabSelect.options[i].dataset.id == data.approver_jabatan_id) {
                    jabSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
        }
        // Cek by value/nama role
        if (!found && data.approver_role) {
            for (let i = 0; i < jabSelect.options.length; i++) {
                if (jabSelect.options[i].value === data.approver_role) {
                    jabSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
        }
        if (!found) {
            jabSelect.value = '';
            if (roleInput) roleInput.readOnly = false;
        } else {
            if (roleInput) roleInput.readOnly = true;
        }
    }

    const modal = document.getElementById('modalHierarkiStep');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openModalAdjustSaldo(pegawaiId, namaPegawai, saldoSekarang) {
    document.getElementById('adjust_pegawai_id').value = pegawaiId;
    document.getElementById('adjust_nama_pegawai').textContent = namaPegawai;
    document.getElementById('adjust_saldo_input').value = saldoSekarang;
    const modal = document.getElementById('modalAdjustSaldo');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openModalJenisCuti(data = null) {
    document.getElementById('jenis_id').value = data ? data.id : '';
    document.getElementById('jenis_kategori').value = data ? data.kategori : 'Khusus';
    document.getElementById('jenis_kode').value = data ? data.kode : '';
    document.getElementById('jenis_nama').value = data ? data.nama_jenis : '';
    document.getElementById('jenis_kuota_hari').value = data ? data.kuota_hari : 3;
    document.getElementById('jenis_is_active').value = data ? data.is_active : 1;
    document.getElementById('jenis_potong').checked = data ? (parseInt(data.potong_kuota_tahunan) === 1) : false;
    document.getElementById('jenis_lampiran').checked = data ? (parseInt(data.butuh_lampiran) === 1) : false;
    document.getElementById('jenis_keterangan').value = data ? (data.keterangan || '') : '';
    document.getElementById('jenis_modal_title').textContent = data ? 'Edit Jenis Cuti' : 'Tambah Jenis Cuti Khusus Baru';

    const modal = document.getElementById('modalJenisCuti');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
</script>
