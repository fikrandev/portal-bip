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
                                            <?= date('d/m/Y', strtotime($cp['tanggal_mulai'])) ?> - <?= date('d/m/Y', strtotime($cp['tanggal_selesai'])) ?>
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
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <?php if ($cp['status'] === 'Pending'): ?>
                                                <button type="button"
                                                    onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Disetujui', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'pengajuan')"
                                                    class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] cursor-pointer">
                                                    Approve
                                                </button>
                                                <button type="button"
                                                    onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Ditolak', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'pengajuan')"
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
                            ?>
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-primary-700">
                                        <?= e($cp['nomor_pengajuan']) ?>
                                        <div class="text-[10px] font-sans font-normal text-slate-400 mt-0.5">
                                            Diajukan: <?= date('d/m/Y H:i', strtotime($cp['created_at'])) ?>
                                        </div>
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
                                            <?= date('d/m/Y', strtotime($cp['tanggal_mulai'])) ?> s.d. <?= date('d/m/Y', strtotime($cp['tanggal_selesai'])) ?>
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
                                                onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Disetujui', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'approve')"
                                                class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm cursor-pointer flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                <span>Setujui</span>
                                            </button>
                                            <button type="button"
                                                onclick="openModalApprove(<?= (int)$cp['id'] ?>, 'Ditolak', <?= htmlspecialchars(json_encode($namaPeg), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($cp['nama_jenis']), ENT_QUOTES, 'UTF-8') ?>, <?= (int)$cp['jumlah_hari'] ?>, 'approve')"
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
                                            <?= date('d/m/Y', strtotime($cp['tanggal_mulai'])) ?> s.d. <?= date('d/m/Y', strtotime($cp['tanggal_selesai'])) ?>
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

            <!-- Tanggal Mulai, Selesai, & Jumlah Hari -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_mulai" id="ajukan_tgl_mulai" value="<?= date('Y-m-d') ?>" required onchange="hitungDurasiCuti()"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_selesai" id="ajukan_tgl_selesai" value="<?= date('Y-m-d') ?>" required onchange="hitungDurasiCuti()"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Hari Cuti <span class="text-rose-500">*</span></label>
                    <input type="number" name="jumlah_hari" id="ajukan_jumlah_hari" value="1" min="1" max="120" required
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-extrabold text-primary-700 bg-primary-50/50 outline-none focus:border-primary-500">
                </div>
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

            <div id="approve_info_banner" class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                <div>Pegawai: <strong id="approve_nama_pegawai" class="text-slate-900"></strong></div>
                <div>Jenis Cuti: <strong id="approve_jenis_cuti" class="text-slate-900"></strong> (<span id="approve_jumlah_hari" class="font-bold"></span> Hari)</div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Persetujuan / Penolakan</label>
                <textarea name="catatan_approval" id="approve_catatan" rows="2" placeholder="Tambahkan catatan verifikasi (opsional)..."
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalApproveCuti')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="approve_submit_btn" class="px-5 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm cursor-pointer">
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

function openModalAjukanCuti(pegawaiId = null) {
    const modal = document.getElementById('modalAjukanCuti');
    const sel = document.getElementById('ajukan_pegawai_id');
    if (pegawaiId && sel) {
        sel.value = String(pegawaiId);
    }
    updateInfoSaldoPegawai();
    hitungDurasiCuti();
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

function openModalApprove(id, keputusan, namaPegawai, namaJenis, jumlahHari, returnTab) {
    document.getElementById('approve_pengajuan_id').value = id;
    document.getElementById('approve_keputusan').value = keputusan;
    document.getElementById('approve_return_tab').value = returnTab || 'approve';
    document.getElementById('approve_nama_pegawai').textContent = namaPegawai;
    document.getElementById('approve_jenis_cuti').textContent = namaJenis;
    document.getElementById('approve_jumlah_hari').textContent = jumlahHari;

    const title = document.getElementById('approve_modal_title');
    const btn = document.getElementById('approve_submit_btn');
    if (keputusan === 'Disetujui') {
        title.textContent = 'Setujui (Approve) Pengajuan Cuti';
        btn.textContent = 'Ya, Setujui & Potong Saldo';
        btn.className = 'px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm cursor-pointer';
    } else if (keputusan === 'Ditolak') {
        title.textContent = 'Tolak Pengajuan Cuti';
        btn.textContent = 'Ya, Tolak Pengajuan';
        btn.className = 'px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm cursor-pointer';
    } else {
        title.textContent = 'Batalkan Cuti & Kembalikan Saldo';
        btn.textContent = 'Ya, Batalkan Cuti';
        btn.className = 'px-5 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs shadow-sm cursor-pointer';
    }

    const modal = document.getElementById('modalApproveCuti');
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
