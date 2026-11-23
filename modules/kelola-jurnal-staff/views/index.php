<?php
/**
 * View Utama Modul Kelola Jurnal Staff (Support System)
 * 4 Fitur / Tab:
 * 1. Dashboard
 * 2. Data Jurnal
 * 3. Pengaturan Jam
 * 4. Pengaturan Tugas
 */

$activeTab = $activeTab ?? ($_GET['tab'] ?? 'dashboard');
$todayYmd = date('Y-m-d');
?>

<div class="space-y-6">
    <!-- Header Modul & Navigasi Tab -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        Kepegawaian & Support System
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700 border border-primary-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                        Live Monitoring
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2">
                    Kelola Jurnal Staff
                </h1>
                <p class="text-slate-500 text-sm mt-0.5">
                    Monitoring aktivitas harian staf/support system, pengelolaan slot sesi jam kerja, dan pengaturan tugas per divisi.
                </p>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                <button type="button" 
                        onclick="openModalPengaturanPeriode()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition-all shadow-sm">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 9v7.5" />
                    </svg>
                    <span>Periode: <?= date('d M', strtotime($filterStartDate)) ?> - <?= date('d M Y', strtotime($filterEndDate)) ?></span>
                </button>

                <a href="<?= url('kelola-jurnal-staff/cetak?start_date=' . urlencode($filterStartDate) . '&end_date=' . urlencode($filterEndDate) . '&jabatan=' . urlencode($jabatan) . '&status=' . urlencode($status)) ?>" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl transition-all shadow-sm">
                    <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.144-1.28-3.093l-.408-.637c-.368-.574-.532-.83-.532-1.127 0-.482.352-.892.83-.974.776-.133 1.57-.213 2.378-.238m10.584 6.069c.24-1.076.672-2.144 1.28-3.093l.408-.637c.368-.574.532-.83.532-1.127 0-.482-.352-.892-.83-.974a24.28 24.28 0 0 0-2.378-.238M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v9.75a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25" />
                    </svg>
                    <span>Cetak Rekap</span>
                </a>

                <button type="button" 
                        onclick="openModalInputJurnal()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-md shadow-primary-600/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>+ Input Jurnal Staff</span>
                </button>
            </div>
        </div>

        <!-- Tab Bar Navigation (4 Fitur Utama) -->
        <div class="mt-6 border-b border-slate-200">
            <nav class="flex space-x-2 md:space-x-4 overflow-x-auto custom-scrollbar" aria-label="Tabs Modul">
                <!-- Tab 1: Dashboard -->
                <button type="button" 
                        onclick="switchStaffTab('dashboard')"
                        data-staff-tab-btn="dashboard"
                        class="staff-tab-button whitespace-nowrap py-3 px-4 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center gap-2 <?= $activeTab === 'dashboard' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    <span>Dashboard</span>
                </button>

                <!-- Tab 2: Data Jurnal -->
                <button type="button" 
                        onclick="switchStaffTab('data')"
                        data-staff-tab-btn="data"
                        class="staff-tab-button whitespace-nowrap py-3 px-4 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center gap-2 <?= $activeTab === 'data' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Data Jurnal</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 font-bold"><?= $jurnalData['total'] ?? 0 ?></span>
                </button>

                <!-- Tab 3: Pengaturan Jam -->
                <button type="button" 
                        onclick="switchStaffTab('jam')"
                        data-staff-tab-btn="jam"
                        class="staff-tab-button whitespace-nowrap py-3 px-4 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center gap-2 <?= $activeTab === 'jam' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Pengaturan Jam</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-primary-50 text-primary-600 font-bold"><?= count($jamList ?? []) ?> Sesi</span>
                </button>

                <!-- Tab 4: Pengaturan Tugas -->
                <button type="button" 
                        onclick="switchStaffTab('tugas')"
                        data-staff-tab-btn="tugas"
                        class="staff-tab-button whitespace-nowrap py-3 px-4 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center gap-2 <?= $activeTab === 'tugas' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>Pengaturan Tugas</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-amber-50 text-amber-700 font-bold"><?= count($masterJabatanList ?? []) ?> Jabatan</span>
                </button>

                <!-- Tab 5: Pengingat WA Otomatis -->
                <button type="button" 
                        onclick="switchStaffTab('pengingat')"
                        data-staff-tab-btn="pengingat"
                        class="staff-tab-button whitespace-nowrap py-3 px-4 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center gap-2 <?= $activeTab === 'pengingat' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                    </svg>
                    <span>Pengingat WA</span>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-50 text-emerald-700 font-bold"><?= (int)($reminderEval['summary']['needs_reminder'] ?? 0) ?> Belum</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: DASHBOARD -->
    <!-- ========================================================================= -->
    <div id="tab-content-dashboard" class="staff-tab-panel space-y-6 <?= $activeTab === 'dashboard' ? '' : 'hidden' ?>">
        <?php if (!empty($dashboardStats['today_libur'])): ?>
            <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="text-xl shrink-0">🏖️</span>
                    <div>
                        <h4 class="text-xs sm:text-sm font-extrabold text-sky-950">
                            Hari Ini Libur: <?= e($dashboardStats['today_libur']['nama_libur']) ?>
                        </h4>
                        <p class="text-xs text-sky-800 mt-0.5">
                            Tidak ada kewajiban pengisian jurnal staff pada hari Minggu atau hari libur terdaftar. Hari ini tidak masuk ke dalam target hari kerja periode.
                        </p>
                    </div>
                </div>
                <a href="<?= url('kelola-absen-pegawai?tab=libur') ?>" class="px-3.5 py-2 rounded-xl bg-white hover:bg-sky-100 text-sky-800 border border-sky-200 text-xs font-bold shrink-0 self-start sm:self-auto">
                    Kelola Hari Libur &rarr;
                </a>
            </div>
        <?php endif; ?>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Total Staff -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Staff Aktif</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= $dashboardStats['total_support_staff'] ?? 0 ?></h3>
                    <p class="text-[11px] text-slate-400 mt-1">Divisi & Support System</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>

            <!-- Card 2: Jurnal Hari Ini -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jurnal Hari Ini</p>
                    <h3 class="text-2xl font-bold text-primary-600 mt-1"><?= $dashboardStats['jurnal_hari_ini'] ?? 0 ?></h3>
                    <p class="text-[11px] text-slate-400 mt-1"><?= date('d F Y') ?></p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <!-- Card 3: Staff Mengisi Hari Ini -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Staff Mengisi (Hari Ini)</p>
                    <h3 class="text-2xl font-bold text-sky-600 mt-1"><?= $dashboardStats['staff_isi_hari_ini'] ?? 0 ?> <span class="text-xs font-normal text-slate-400">/ <?= $dashboardStats['total_support_staff'] ?? 0 ?></span></h3>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="text-[11px] font-bold text-sky-600"><?= $dashboardStats['persen_hari_ini'] ?? 0 ?>%</span>
                        <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-primary-600 h-1.5 rounded-full" style="width: <?= min(100, $dashboardStats['persen_hari_ini'] ?? 0) ?>%"></div>
                        </div>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <!-- Card 4: Keterisian & Tugas Periode -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Keterisian Periode</p>
                    <h3 class="text-2xl font-bold text-violet-600 mt-1"><?= number_format((float)($dashboardStats['persen_periode'] ?? 0), 1) ?>%</h3>
                    <p class="text-[11px] text-slate-400 mt-1">
                        <?= $dashboardStats['total_selesai_periode'] ?? 0 ?> selesai / <?= $dashboardStats['hari_kerja_periode'] ?? 0 ?> hari kerja<?php if (!empty($dashboardStats['libur_periode'])): ?> <span class="text-rose-500">(libur <?= (int)$dashboardStats['libur_periode'] ?>h)</span><?php endif; ?>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.003 0H9.497m5.003 0a2.25 2.25 0 0 0 2.25-2.25v-.872c0-.51-.395-.935-.904-.98l-2.096-.184a2.25 2.25 0 0 1-2.05-2.246V4.5M9.497 14.25a2.25 2.25 0 0 1-2.25-2.25v-.872c0-.51.395-.935.904-.98l2.096-.184a2.25 2.25 0 0 0 2.05-2.246V4.5" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Row 2: Grafik Tren 7 Hari & Rekapitulasi Divisi -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tren Jurnal 7 Hari Terakhir (2 Cols) -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Tren Pengisian Jurnal 7 Hari Terakhir</h3>
                            <p class="text-xs text-slate-500">Jumlah aktivitas kerja staff yang dilaporkan per hari (Minggu &amp; tanggal libur ditandai)</p>
                        </div>
                        <span class="text-xs px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg font-medium">Harian</span>
                    </div>

                    <!-- Custom SVG / CSS Bar Chart -->
                    <?php
                        $trendArr = $dashboardStats['trend_7_days'] ?? [];
                        $maxVal = 1;
                        foreach ($trendArr as $t) {
                            if ($t['count'] > $maxVal) $maxVal = $t['count'];
                        }
                    ?>
                    <div class="h-48 flex items-end justify-between gap-2 pt-6 px-2 border-b border-slate-100">
                        <?php foreach ($trendArr as $t): 
                            $isLiburBar = !empty($t['is_libur']);
                            $heightPct = $maxVal > 0 ? max(8, round(($t['count'] / $maxVal) * 100)) : 8;
                            $barColor = $isLiburBar
                                ? 'bg-sky-200/80 border border-dashed border-sky-400'
                                : ($t['is_today'] ? 'bg-primary-600 hover:bg-primary-700' : 'bg-slate-200 hover:bg-primary-400');
                            $textColor = $isLiburBar
                                ? 'text-sky-600 font-semibold'
                                : ($t['is_today'] ? 'text-primary-600 font-bold' : 'text-slate-500');
                        ?>
                            <div class="flex-1 flex flex-col items-center gap-1.5 h-full justify-end group" title="<?= e(($t['label'] ?? '') . ($isLiburBar ? ' — ' . ($t['libur_label'] ?? 'Libur') : '')) ?>">
                                <span class="text-[10px] font-bold <?= $isLiburBar ? 'text-sky-600' : 'text-slate-600 transition-opacity opacity-0 group-hover:opacity-100' ?>">
                                    <?= $isLiburBar ? 'Libur' : $t['count'] ?>
                                </span>
                                <div class="w-full max-w-[42px] rounded-t-lg transition-all duration-300 <?= $barColor ?>" style="height: <?= $heightPct ?>%"></div>
                                <span class="text-[10px] <?= $textColor ?> truncate text-center w-full">
                                    <?= $t['short_date'] ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-primary-600 inline-block"></span> Hari ini
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-sky-200 border border-dashed border-sky-400 inline-block"></span> Minggu / Libur
                        </span>
                    </div>
                    <span>Monitoring performa pengisian tim support</span>
                </div>
            </div>

            <!-- Rekapitulasi per Jabatan / Divisi (1 Col) -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-800">Partisipasi per Divisi</h3>
                    <span class="text-[11px] font-medium text-slate-400">Total Jurnal</span>
                </div>

                <div class="space-y-4 max-h-[220px] overflow-y-auto custom-scrollbar pr-1">
                    <?php 
                    $rekapJab = $dashboardStats['rekap_jabatan'] ?? [];
                    if (empty($rekapJab)):
                    ?>
                        <div class="text-center py-8 text-xs text-slate-400">
                            Belum ada catatan jurnal pada divisi.
                        </div>
                    <?php else: 
                        $totalAllJurnal = array_sum(array_column($rekapJab, 'total_jurnal')) ?: 1;
                        foreach ($rekapJab as $rj):
                            $jabPct = round(($rj['total_jurnal'] / $totalAllJurnal) * 100);
                    ?>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-semibold text-slate-700 truncate max-w-[170px]" title="<?= htmlspecialchars($rj['nama_jabatan']) ?>">
                                    <?= htmlspecialchars($rj['nama_jabatan']) ?>
                                </span>
                                <span class="text-slate-500 font-bold"><?= $rj['total_jurnal'] ?> <span class="font-normal text-[10px]">catatan</span></span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-primary-600 h-2 rounded-full" style="width: <?= min(100, $jabPct) ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>

        <!-- Row 3: Live Feed Aktivitas Jurnal Terbaru -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Catatan Jurnal Terbaru</h3>
                    <p class="text-xs text-slate-500">Laporan kerja staff terbaru yang baru saja disubmit</p>
                </div>
                <button type="button" onclick="switchStaffTab('data')" class="text-xs text-indigo-600 font-semibold hover:text-indigo-800">
                    Lihat Semua Data &rarr;
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-3">Waktu & Sesi</th>
                            <th class="py-3 px-3">Pegawai Staff</th>
                            <th class="py-3 px-3">Aktivitas / Job Desk</th>
                            <th class="py-3 px-3">Uraian Hasil</th>
                            <th class="py-3 px-3 text-center">Status</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php 
                        $latest = $dashboardStats['latest_jurnal'] ?? [];
                        if (empty($latest)):
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400">
                                    Belum ada aktivitas jurnal staff yang tercatat.
                                </td>
                            </tr>
                        <?php else: 
                            foreach ($latest as $lj):
                                $statusBadge = match($lj['status']) {
                                    'selesai'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'proses'   => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'tertunda' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default    => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                        ?>
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-800"><?= date('d M Y', strtotime($lj['tanggal'])) ?></div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <?= htmlspecialchars($lj['jam_rentang']) ?>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <?php if (!empty($lj['foto'])): ?>
                                            <img src="<?= url('public/' . ltrim($lj['foto'], '/')) ?>" alt="" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                <?= strtoupper(substr($lj['nama_staff'] ?? 'S', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 truncate"><?= htmlspecialchars($lj['nama_staff']) ?></div>
                                            <div class="text-[11px] text-slate-500 truncate"><?= htmlspecialchars($lj['jabatan']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <?php $dashAktParts = JurnalStaffModel::splitAktivitasList($lj['aktivitas'] ?? ''); ?>
                                    <?php if (count($dashAktParts) > 1): ?>
                                        <div class="space-y-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100 text-[10px] font-bold">
                                                <?= count($dashAktParts) ?> Tugas
                                            </span>
                                            <ul class="text-[11px] font-semibold text-slate-700 space-y-0.5">
                                                <?php foreach ($dashAktParts as $dap): ?>
                                                    <li class="line-clamp-1">&bull; <?= htmlspecialchars($dap) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <div class="font-semibold text-slate-700 line-clamp-1"><?= htmlspecialchars($lj['aktivitas']) ?></div>
                                    <?php endif; ?>
                                    <div class="text-[11px] text-indigo-600 font-mono mt-0.5"><?= htmlspecialchars($lj['kode_jurnal']) ?></div>
                                </td>
                                <td class="py-3 px-3">
                                    <p class="text-slate-600 line-clamp-2 max-w-xs text-[11px]">
                                        <?= htmlspecialchars($lj['uraian_hasil']) ?>
                                    </p>
                                </td>
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider <?= $statusBadge ?>">
                                        <?= htmlspecialchars($lj['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right whitespace-nowrap">
                                    <button type="button" 
                                            onclick="viewDetailJurnal(<?= (int)$lj['id'] ?>)"
                                            class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition-colors"
                                            title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: DATA JURNAL (REKAPITULASI LENGKAP) -->
    <!-- ========================================================================= -->
    <div id="tab-content-data" class="staff-tab-panel space-y-6 <?= $activeTab === 'data' ? '' : 'hidden' ?>">
        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100">
            <form action="<?= url('kelola-jurnal-staff') ?>" method="GET" class="space-y-4">
                <input type="hidden" name="tab" value="data">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($filterStartDate) ?>" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                    </div>

                    <!-- Tanggal Akhir -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($filterEndDate) ?>" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                    </div>

                    <!-- Filter Jabatan / Divisi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jabatan / Divisi</label>
                        <select name="jabatan" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                            <option value="">Semua Jabatan</option>
                            <?php foreach ($masterJabatanList as $mj): ?>
                                <option value="<?= htmlspecialchars($mj['nama']) ?>" <?= $jabatan === $mj['nama'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mj['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Staff -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Pegawai Staff</label>
                        <select name="pegawai_id" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                            <option value="">Semua Pegawai Staff</option>
                            <?php foreach ($staffList as $stf): ?>
                                <option value="<?= (int)$stf['id'] ?>" <?= $pegawaiId == $stf['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stf['nama']) ?> (<?= htmlspecialchars($stf['jabatan'] ?: 'Staff') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status Pelaksanaan</label>
                        <select name="status" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                            <option value="">Semua Status</option>
                            <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                            <option value="proses" <?= $status === 'proses' ? 'selected' : '' ?>>Proses</option>
                            <option value="tertunda" <?= $status === 'tertunda' ? 'selected' : '' ?>>Tertunda</option>
                        </select>
                    </div>
                </div>

                <!-- Baris Bawah: Pencarian & Tombol Aksi -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <div class="relative w-full sm:w-80">
                        <input type="text" 
                               name="search" 
                               value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Cari nama, aktivitas, uraian, kode..." 
                               class="w-full pl-9 pr-4 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <a href="<?= url('kelola-jurnal-staff?tab=data') ?>" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                            Reset
                        </a>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-sm">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Rekap Data Jurnal -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Daftar Rekapitulasi Jurnal Staff</h3>
                    <p class="text-xs text-slate-500">
                        Menampilkan <?= count($jurnalData['data']) ?> dari total <?= $jurnalData['total'] ?> catatan jurnal
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= url('kelola-jurnal-staff/cetak?start_date=' . urlencode($filterStartDate) . '&end_date=' . urlencode($filterEndDate) . '&jabatan=' . urlencode($jabatan) . '&status=' . urlencode($status) . '&search=' . urlencode($search)) ?>" 
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 border border-primary-200 rounded-xl transition-colors">
                        <svg class="w-3.5 h-3.5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.144-1.28-3.093l-.408-.637c-.368-.574-.532-.83-.532-1.127 0-.482.352-.892.83-.974.776-.133 1.57-.213 2.378-.238m10.584 6.069c.24-1.076.672-2.144 1.28-3.093l.408-.637c.368-.574.532-.83.532-1.127 0-.482-.352-.892-.83-.974a24.28 24.28 0 0 0-2.378-.238M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v9.75a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25" />
                        </svg>
                        <span>Cetak Data Ini</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-3 text-center w-12">No</th>
                            <th class="py-3.5 px-3">Tanggal & Sesi Jam</th>
                            <th class="py-3.5 px-3">Pegawai Staff</th>
                            <th class="py-3.5 px-3">Aktivitas / Job Desk</th>
                            <th class="py-3.5 px-3">Uraian Hasil & Kendala</th>
                            <th class="py-3.5 px-3 text-center">Bukti</th>
                            <th class="py-3.5 px-3 text-center">Status</th>
                            <th class="py-3.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php 
                        $no = ($jurnalData['page'] - 1) * $jurnalData['limit'] + 1;
                        if (empty($jurnalData['data'])):
                        ?>
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>
                                        <p class="font-medium text-slate-500">Tidak ada catatan jurnal staff yang sesuai dengan filter.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else:
                            foreach ($jurnalData['data'] as $j):
                                $statusBadge = match($j['status']) {
                                    'selesai'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'proses'   => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'tertunda' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default    => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-3 text-center font-bold text-slate-400"><?= $no++ ?></td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-800"><?= date('d M Y', strtotime($j['tanggal'])) ?></div>
                                    <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-semibold border border-indigo-100">
                                        <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <?= htmlspecialchars($j['jam_rentang']) ?>
                                    </div>
                                    <?php if (!empty($j['nama_sesi'])): ?>
                                        <div class="text-[10px] text-slate-400 mt-0.5 truncate"><?= htmlspecialchars($j['nama_sesi']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <?php if (!empty($j['foto'])): ?>
                                            <img src="<?= url('public/' . ltrim($j['foto'], '/')) ?>" alt="" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                <?= strtoupper(substr($j['nama_staff'] ?? 'S', 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-800 truncate"><?= htmlspecialchars($j['nama_staff']) ?></div>
                                            <div class="text-[11px] text-indigo-600 font-medium truncate"><?= htmlspecialchars($j['jabatan']) ?></div>
                                            <?php if (!empty($j['niy'])): ?>
                                                <div class="text-[10px] text-slate-400 font-mono">NIY: <?= htmlspecialchars($j['niy']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <?php $rowAktParts = JurnalStaffModel::splitAktivitasList($j['aktivitas'] ?? ''); ?>
                                    <?php if (count($rowAktParts) > 1): ?>
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100 text-[10px] font-bold">
                                                <?= count($rowAktParts) ?> Tugas
                                            </span>
                                            <ul class="text-xs font-semibold text-slate-800 space-y-0.5">
                                                <?php foreach ($rowAktParts as $rap): ?>
                                                    <li class="line-clamp-1">&bull; <?= htmlspecialchars($rap) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <div class="font-semibold text-slate-800 line-clamp-1"><?= htmlspecialchars($j['aktivitas']) ?></div>
                                    <?php endif; ?>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5"><?= htmlspecialchars($j['kode_jurnal']) ?></div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="max-w-xs space-y-1">
                                        <p class="text-slate-600 line-clamp-2 text-[11px] leading-relaxed">
                                            <?= htmlspecialchars($j['uraian_hasil']) ?>
                                        </p>
                                        <?php if (!empty($j['kendala'])): ?>
                                            <div class="text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200/60 inline-flex items-center gap-1">
                                                <span class="font-semibold">Kendala:</span> <?= htmlspecialchars($j['kendala']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    <?php if (!empty($j['foto_bukti'])): ?>
                                        <button type="button" 
                                                onclick="previewImage('<?= url('public/' . ltrim($j['foto_bukti'], '/')) ?>')"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200">
                                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            </svg>
                                            Foto
                                        </button>
                                    <?php else: ?>
                                        <span class="text-[10px] text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider <?= $statusBadge ?>">
                                        <?= htmlspecialchars($j['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <!-- Detail -->
                                        <button type="button" 
                                                onclick="viewDetailJurnal(<?= (int)$j['id'] ?>)"
                                                class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="Lihat Detail Jurnal">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>

                                        <!-- Edit -->
                                        <button type="button" 
                                                onclick='editJurnal(<?= json_encode($j, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="Edit Jurnal">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Hapus -->
                                        <form action="<?= url('kelola-jurnal-staff/jurnal/delete/' . (int)$j['id']) ?>" 
                                              method="POST" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan jurnal ini?');" 
                                              class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" 
                                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                    title="Hapus Jurnal">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($jurnalData['total_pages'] > 1): ?>
                <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Halaman <?= $jurnalData['page'] ?> dari <?= $jurnalData['total_pages'] ?>
                    </div>
                    <div class="flex items-center gap-1">
                        <?php for ($p = 1; $p <= $jurnalData['total_pages']; $p++): 
                            $pageQuery = array_merge($_GET, ['page' => $p, 'tab' => 'data']);
                            $pUrl = url('kelola-jurnal-staff?' . http_build_query($pageQuery));
                        ?>
                            <a href="<?= $pUrl ?>" class="px-3 py-1.5 rounded-lg font-semibold <?= $p === $jurnalData['page'] ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                                <?= $p ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: PENGATURAN JAM (SLOT SESI KERJA STAFF) -->
    <!-- ========================================================================= -->
    <div id="tab-content-jam" class="staff-tab-panel space-y-6 <?= $activeTab === 'jam' ? '' : 'hidden' ?>">
        <!-- Petunjuk Pengaturan Jam -->
        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-indigo-900">Format Slot Jam Kerja Staff</h3>
                <p class="text-xs text-indigo-700 mt-1 leading-relaxed">
                    Atur jadwal slot sesi kerja harian staf atau support system. Misalnya:
                    <span class="font-semibold underline">07.00 - 08.00 (Tilawah)</span>,
                    <span class="font-semibold underline">08.00 - 09.00 (Aktifitas)</span>,
                    <span class="font-semibold underline">12.00 - 13.00 (Istirahat/Ishoma)</span>, dll.
                    Slot jam ini akan otomatis muncul sebagai opsi pilihan saat staff atau admin menginput catatan jurnal.
                </p>
            </div>
            <button type="button" 
                    onclick="openModalTambahJam()"
                    class="shrink-0 inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-md shadow-primary-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Tambah Jam Kerja</span>
            </button>
        </div>

        <!-- Tabel Daftar Jam Kerja Staff -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Daftar Slot Jam Sesi Harian</h3>
                    <p class="text-xs text-slate-500">Urutan sesi harian kerja staf di lingkungan sekolah</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-3 text-center w-16">Urutan</th>
                            <th class="py-3.5 px-3">Rentang Jam</th>
                            <th class="py-3.5 px-3">Nama Sesi Jam Kerja</th>
                            <th class="py-3.5 px-3">Tipe Aktivitas</th>
                            <th class="py-3.5 px-3">Keterangan / Rincian</th>
                            <th class="py-3.5 px-3 text-center">Status</th>
                            <th class="py-3.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($jamList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400">
                                    Belum ada pengaturan jam sesi kerja staff. Silakan klik tombol "+ Tambah Jam Kerja" di atas.
                                </td>
                            </tr>
                        <?php else:
                            foreach ($jamList as $jm):
                                $tipeBadge = match($jm['tipe']) {
                                    'tilawah'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'aktifitas' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'istirahat' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    default     => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                                $startStr = date('H.i', strtotime($jm['jam_mulai']));
                                $endStr = date('H.i', strtotime($jm['jam_selesai']));
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-3 text-center font-bold text-slate-500">
                                    <span class="w-6 h-6 rounded-full bg-slate-100 inline-flex items-center justify-center text-xs">
                                        <?= (int)$jm['urutan'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap font-bold text-slate-800">
                                    <span class="font-mono text-sm px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800">
                                        <?= $startStr ?> - <?= $endStr ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 font-semibold text-slate-800">
                                    <?= htmlspecialchars($jm['nama_sesi']) ?>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider <?= $tipeBadge ?>">
                                        <?= htmlspecialchars($jm['tipe']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600 max-w-xs text-[11px]">
                                    <?= htmlspecialchars($jm['keterangan'] ?: '-') ?>
                                </td>
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    <form action="<?= url('kelola-jurnal-staff/jam/toggle/' . (int)$jm['id']) ?>" method="POST" class="inline">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition-colors <?= $jm['is_active'] ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' ?>" title="Klik untuk ubah status aktif">
                                            <?= $jm['is_active'] ? '● Aktif' : '○ Nonaktif' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <!-- Edit -->
                                        <button type="button" 
                                                onclick='editJam(<?= json_encode($jm, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="Edit Jam Kerja">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        <!-- Hapus -->
                                        <form action="<?= url('kelola-jurnal-staff/jam/delete/' . (int)$jm['id']) ?>" 
                                              method="POST" 
                                              onsubmit="return confirm('Hapus slot jam kerja ini?');" 
                                              class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" 
                                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                    title="Hapus Jam">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: PENGATURAN TUGAS (JOB DESK PER JABATAN REFERENSI) -->
    <!-- ========================================================================= -->
    <div id="tab-content-tugas" class="staff-tab-panel space-y-6 <?= $activeTab === 'tugas' ? '' : 'hidden' ?>">
        <!-- Instruksi Khusus Sesuai Permintaan User -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-amber-900">Pengaturan Tugas Berdasarkan Referensi Jabatan</h3>
                <p class="text-xs text-amber-800 mt-1 leading-relaxed">
                    Daftar jabatan di bawah ini ditarik langsung dari referensi master jabatan.
                    Pilih jabatan yang ingin diatur (misalnya <span class="font-bold underline">Kepala Divisi Media dan IT</span>, <span class="font-bold underline">Manajer Divisi Bisnis</span>, dsb), lalu Anda dapat menambah atau mengelola job desk/tugas kerja untuk jabatan tersebut.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Kolom Kiri: Daftar Jabatan Referensi (5 Cols) -->
            <div class="lg:col-span-4 space-y-3">
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-3 px-1">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Pilih Referensi Jabatan
                        </h4>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                            <?= count($masterJabatanList) ?> Jabatan
                        </span>
                    </div>

                    <div class="space-y-1.5 max-h-[580px] overflow-y-auto custom-scrollbar pr-1">
                        <?php foreach ($masterJabatanList as $mj): 
                            $isSel = ($selectedJabatanId === (int)$mj['id']);
                            $tInfo = $tugasCounts[(int)$mj['id']] ?? ['count_tugas' => 0, 'count_active' => 0];
                            $cntT = $tInfo['count_tugas'];
                        ?>
                            <a href="<?= url('kelola-jurnal-staff?tab=tugas&jabatan_id=' . (int)$mj['id']) ?>" 
                               class="flex items-center justify-between p-3 rounded-xl transition-all duration-200 <?= $isSel ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20 font-bold' : 'bg-slate-50 text-slate-700 hover:bg-slate-100 font-medium' ?>">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg <?= $isSel ? 'bg-white/20 text-white' : 'bg-white text-slate-500' ?> flex items-center justify-center shrink-0 text-xs">
                                        <?= (int)$mj['id'] ?>
                                    </div>
                                    <span class="text-xs truncate"><?= htmlspecialchars($mj['nama']) ?></span>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 <?= $isSel ? 'bg-white/20 text-white' : ($cntT > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-200 text-slate-500') ?>">
                                    <?= $cntT ?> Tugas
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Daftar Tugas untuk Jabatan Terpilih (8 Cols) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-slate-400">Jabatan Terpilih:</span>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    ID <?= $selectedJabatanId ?>
                                </span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800 mt-0.5">
                                <?= htmlspecialchars($selectedJabatan['nama'] ?? 'Pilih Jabatan') ?>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Job desk & tugas operasional yang dapat dipilih staff saat mengisi jurnal
                            </p>
                        </div>

                        <?php if ($selectedJabatan): ?>
                            <button type="button" 
                                    onclick="openModalTambahTugas(<?= (int)$selectedJabatan['id'] ?>, '<?= htmlspecialchars(addslashes($selectedJabatan['nama'])) ?>')"
                                    class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-md shadow-primary-600/20 shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span>+ Tambah Tugas <?= htmlspecialchars($selectedJabatan['nama']) ?></span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Tabel Tugas untuk Jabatan Terpilih -->
                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-3 px-3 text-center w-12">No</th>
                                    <th class="py-3 px-3">Nama Tugas / Job Desk</th>
                                    <th class="py-3 px-3">Kategori</th>
                                    <th class="py-3 px-3">Deskripsi / Panduan</th>
                                    <th class="py-3 px-3 text-center">Status</th>
                                    <th class="py-3 px-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php if (empty($tugasList)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-10 text-slate-400">
                                            <p class="font-medium">Belum ada tugas yang didaftarkan untuk jabatan ini.</p>
                                            <p class="text-[11px] mt-1 text-slate-400">Klik tombol "+ Tambah Tugas" di atas untuk menambahkan job desk.</p>
                                        </td>
                                    </tr>
                                <?php else:
                                    $tNo = 1;
                                    foreach ($tugasList as $tg):
                                        $katBadge = match($tg['kategori_tugas']) {
                                            'rutin'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'berkala'    => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'insidental' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            default      => 'bg-slate-50 text-slate-700 border-slate-200',
                                        };
                                ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-3 text-center font-bold text-slate-400"><?= $tNo++ ?></td>
                                        <td class="py-3 px-3 font-semibold text-slate-800">
                                            <?= htmlspecialchars($tg['nama_tugas']) ?>
                                        </td>
                                        <td class="py-3 px-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider <?= $katBadge ?>">
                                                <?= htmlspecialchars($tg['kategori_tugas']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-slate-600 max-w-xs text-[11px]">
                                            <?= htmlspecialchars($tg['deskripsi'] ?: '-') ?>
                                        </td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            <form action="<?= url('kelola-jurnal-staff/tugas/toggle/' . (int)$tg['id']) ?>" method="POST" class="inline">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="jabatan_id" value="<?= (int)$selectedJabatanId ?>">
                                                <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold border transition-colors <?= $tg['is_active'] ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' ?>" title="Ubah status">
                                                    <?= $tg['is_active'] ? '● Aktif' : '○ Nonaktif' ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-3 px-3 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1">
                                                <!-- Edit Tugas -->
                                                <button type="button" 
                                                        onclick='editTugas(<?= json_encode($tg, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                        class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                        title="Edit Tugas">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                    </svg>
                                                </button>

                                                <!-- Hapus Tugas -->
                                                <form action="<?= url('kelola-jurnal-staff/tugas/delete/' . (int)$tg['id']) ?>" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Hapus tugas ini?');" 
                                                      class="inline">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="jabatan_id" value="<?= (int)$selectedJabatanId ?>">
                                                    <button type="submit" 
                                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                            title="Hapus Tugas">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 5: PENGINGAT WA OTOMATIS JURNAL STAFF -->
    <!-- ========================================================================= -->
    <?php
        $remCfg = $reminderSettings ?? JurnalStaffReminderService::getSettings();
        $remEval = $reminderEval ?? ['date' => date('Y-m-d'), 'slot' => '17:00', 'required_jams' => [], 'summary' => [], 'items' => []];
        $remSummary = $remEval['summary'] ?? [];
        $remItems = $remEval['items'] ?? [];
        $remLogs = $reminderLogs ?? [];
        $waConn = $waConnection ?? ['connected' => false, 'status' => 'offline', 'phone' => null];
        $waConnected = !empty($waConn['connected']);
        $canManageReminder = !class_exists('RBAC') || RBAC::hasPermission('jurnal_staff.manage');
        $activeSchedules = $remCfg['schedules_array'] ?? ['09:00', '13:00', '17:00'];
        $activeDaysArr = $remCfg['active_days_array'] ?? [1, 2, 3, 4, 5, 6];
        $currentPreviewSlot = $previewSlot ?? ($remEval['slot'] ?? '17:00');
        $cronEndpointUrl = $remCfg['effective_base_url'] . '/cron/jurnal-staff-reminder?key=' . $remCfg['jurnal_staff_wa_cron_key'];
        $cliCronCmd = 'php ' . BASE_PATH . DIRECTORY_SEPARATOR . 'cron' . DIRECTORY_SEPARATOR . 'jurnal_staff_reminder.php';
    ?>
    <div id="tab-content-pengingat" class="staff-tab-panel space-y-6 <?= $activeTab === 'pengingat' ? '' : 'hidden' ?>">

        <!-- 5.1 Hero Status & Aksi Cepat Kirim Pengingat -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-primary-950 to-emerald-950 text-white p-6 shadow-xl border border-emerald-500/20">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col xl:flex-row xl:items-center justify-between gap-5">
                <div class="space-y-2.5 max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-white/10 border border-white/15">
                            <span class="w-2 h-2 rounded-full <?= $waConnected ? 'bg-emerald-400 animate-ping' : 'bg-rose-400' ?>"></span>
                            <span><?= $waConnected ? 'WA Gateway Terhubung (+' . e($waConn['phone'] ?? '-') . ')' : 'WA Gateway Belum Terhubung' ?></span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= ($remCfg['jurnal_staff_wa_enabled'] === '1') ? 'bg-emerald-500/20 text-emerald-200 border border-emerald-400/30' : 'bg-slate-700 text-slate-300 border border-slate-600' ?>">
                            <?= ($remCfg['jurnal_staff_wa_enabled'] === '1') ? '⏰ Jadwal Otomatis Aktif' : '⏸️ Jadwal Otomatis Nonaktif' ?>
                        </span>
                        <a href="<?= url('pengaturan-sistem/wa-gateway') ?>" class="text-xs text-emerald-300 hover:text-emerald-200 underline font-semibold">
                            Kelola WA Gateway &rarr;
                        </a>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black tracking-tight">
                        Pengingat Pengisian Jurnal Staff (WA Gateway)
                    </h2>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Sistem otomatis mengirim pengingat WhatsApp ke staff Support System yang belum mengisi atau baru mengisi sebagian sesi kerja pada jam terjadwal.
                    </p>

                    <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                        <span class="text-slate-400 font-semibold">Jadwal Aktif (WITA):</span>
                        <?php foreach ($activeSchedules as $sc): ?>
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 font-mono font-bold">
                                🕘 <?= e($sc) ?> WITA
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Quick Bulk Dispatch Panel -->
                <?php if ($canManageReminder): ?>
                <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-2xl p-4 space-y-3 shrink-0 w-full xl:w-auto xl:min-w-[340px]">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-200">Kirim Pengingat Manual</span>
                        <span class="text-[11px] font-mono text-slate-300"><?= date('d M Y H:i') ?> WITA</span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <select id="bulk-run-slot" class="flex-1 px-3 py-2 rounded-xl bg-slate-900/80 border border-white/20 text-white text-xs font-semibold outline-none focus:ring-2 focus:ring-emerald-400">
                            <?php foreach ($activeSchedules as $sc): ?>
                                <option value="<?= e($sc) ?>" <?= $currentPreviewSlot === $sc ? 'selected' : '' ?>>
                                    Slot <?= e($sc) ?> WITA
                                </option>
                            <?php endforeach; ?>
                            <option value="all" <?= $currentPreviewSlot === 'all' ? 'selected' : '' ?>>Semua Sesi Hari Ini</option>
                        </select>
                        <button type="button" id="btn-run-bulk-wa" onclick="runBulkWaReminder()"
                                class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-extrabold text-xs shadow-lg shadow-emerald-500/25 transition-all flex items-center justify-center gap-1.5 shrink-0">
                            <span>📤 Kirim Pengingat Sekarang</span>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-2 text-[11px]">
                        <label class="inline-flex items-center gap-1.5 text-slate-200 cursor-pointer select-none">
                            <input type="checkbox" id="bulk-run-force" class="rounded border-white/30 bg-slate-900 text-emerald-500 focus:ring-emerald-400">
                            <span>Kirim ulang meski sudah terkirim di slot ini</span>
                        </label>
                        <a href="#card-uji-kirim-wa" class="text-emerald-300 hover:text-emerald-200 font-bold underline shrink-0">
                            Uji Kirim
                        </a>
                    </div>
                    <div id="bulk-run-result" class="hidden text-xs p-2.5 rounded-xl bg-black/40 border border-white/15"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 5.2 Summary Cards & Monitoring Daftar Staff Hari Ini -->
        <?php if (!empty($remEval['is_holiday'])): ?>
            <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="text-xl shrink-0">🏖️</span>
                    <div>
                        <h4 class="text-xs sm:text-sm font-extrabold text-sky-950">
                            Hari Ini Libur: <?= e($remEval['holiday_info']['nama_libur'] ?? 'Hari Libur') ?>
                        </h4>
                        <p class="text-xs text-sky-800 mt-0.5">
                            Pengingat WA otomatis Jurnal Staff hari ini dilewati karena bukan hari kerja aktif.
                        </p>
                    </div>
                </div>
                <a href="<?= url('kelola-absen-pegawai?tab=libur') ?>" class="px-3.5 py-2 rounded-xl bg-white hover:bg-sky-100 text-sky-800 border border-sky-200 text-xs font-bold shrink-0 self-start sm:self-auto">
                    Atur Hari Libur &rarr;
                </a>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <p class="text-[11px] font-bold text-slate-400 uppercase">Penerima WA</p>
                <h4 class="text-2xl font-black text-slate-800 mt-1">
                    <?= (int)($remSummary['enabled_wa'] ?? 0) ?><span class="text-sm font-bold text-slate-400"> / <?= (int)($remSummary['total_staff'] ?? 0) ?></span>
                </h4>
                <p class="text-[10px] text-slate-400 mt-0.5"><?= (int)($remSummary['nonaktif_wa'] ?? 0) ?> staff dinonaktifkan</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm">
                <p class="text-[11px] font-bold text-rose-500 uppercase">Belum Isi Total</p>
                <h4 class="text-2xl font-black text-rose-600 mt-1"><?= (int)($remSummary['belum_isi'] ?? 0) ?></h4>
                <p class="text-[10px] text-rose-400 mt-0.5">0 sesi hari ini</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm">
                <p class="text-[11px] font-bold text-amber-600 uppercase">Isi Sebagian</p>
                <h4 class="text-2xl font-black text-amber-600 mt-1"><?= (int)($remSummary['sebagian'] ?? 0) ?></h4>
                <p class="text-[10px] text-amber-500 mt-0.5">Ada jam terlewat</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-sm">
                <p class="text-[11px] font-bold text-emerald-600 uppercase">Sudah Lengkap</p>
                <h4 class="text-2xl font-black text-emerald-600 mt-1"><?= (int)($remSummary['lengkap'] ?? 0) ?></h4>
                <p class="text-[10px] text-emerald-500 mt-0.5">Sesuai cutoff slot</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-sky-100 shadow-sm">
                <p class="text-[11px] font-bold text-sky-600 uppercase">Cuti / Izin</p>
                <h4 class="text-2xl font-black text-sky-600 mt-1"><?= (int)($remSummary['cuti_izin'] ?? 0) ?></h4>
                <p class="text-[10px] text-sky-400 mt-0.5">Dikecualikan</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm">
                <p class="text-[11px] font-bold text-slate-500 uppercase">Tanpa No. WA</p>
                <h4 class="text-2xl font-black text-slate-700 mt-1"><?= (int)($remSummary['no_wa'] ?? 0) ?></h4>
                <p class="text-[10px] text-slate-400 mt-0.5">Penerima perlu lengkapi WA</p>
            </div>
        </div>

        <!-- Monitoring Table & Checklist Penerima WA -->
        <?php
            $jabatanOpts = [];
            foreach ($remItems as $rit) {
                $jb = trim((string)($rit['jabatan'] ?? ''));
                if ($jb !== '') {
                    $jabatanOpts[$jb] = true;
                }
            }
            $jabatanFilterList = array_keys($jabatanOpts);
            sort($jabatanFilterList);
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>📋</span>
                        <span>Monitoring Pengisian &amp; Daftar Penerima WA (<?= e(JurnalStaffReminderService::formatTanggalIndo($remEval['date'])) ?>)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Centang staff yang menerima pengingat WA otomatis. Evaluasi wajib hingga cutoff
                        <strong class="text-slate-700"><?= $currentPreviewSlot === 'all' ? 'Seluruh Sesi Hari Ini' : 'Jam ' . e($currentPreviewSlot) . ' WITA' ?></strong>,
                        dan pesan menampilkan seluruh jam yang belum diisi.
                    </p>
                </div>

                <!-- Slot Filter Links -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <?php foreach ($activeSchedules as $sc): ?>
                        <a href="<?= url('kelola-jurnal-staff?tab=pengingat&preview_slot=' . urlencode($sc)) ?>"
                           class="px-3 py-1.5 rounded-xl text-xs font-bold border transition-all <?= $currentPreviewSlot === $sc ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' ?>">
                            Slot <?= e($sc) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="<?= url('kelola-jurnal-staff?tab=pengingat&preview_slot=all') ?>"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold border transition-all <?= $currentPreviewSlot === 'all' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' ?>">
                        Semua Sesi
                    </a>
                </div>
            </div>

            <!-- Filter & Aksi Checklist Penerima WA -->
            <div class="px-5 py-3 bg-slate-50/70 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <input type="text" id="filter-wa-staff-search" oninput="filterWaStaffTable()"
                               placeholder="Cari nama / jabatan..."
                               class="w-48 sm:w-56 pl-3 pr-3 py-1.5 text-xs bg-white border border-slate-200 rounded-xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                    </div>
                    <select id="filter-wa-jabatan" onchange="filterWaStaffTable()"
                            class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl focus:border-emerald-500 outline-none">
                        <option value="">Semua Jabatan / Tim</option>
                        <?php foreach ($jabatanFilterList as $jbName): ?>
                            <option value="<?= e($jbName) ?>"><?= e($jbName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span id="wa-recipient-counter" class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold">
                        Aktif WA: <?= (int)($remSummary['enabled_wa'] ?? 0) ?> / <?= (int)($remSummary['total_staff'] ?? 0) ?> Staff
                    </span>
                    <span id="wa-recipient-dirty" class="hidden px-2 py-0.5 rounded-lg bg-amber-100 text-amber-800 border border-amber-300 text-[10px] font-bold">
                        Belum disimpan
                    </span>
                </div>

                <?php if ($canManageReminder): ?>
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" onclick="toggleVisibleWaRecipients(true)"
                            class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-bold transition-colors">
                        ✓ Centang Tampil
                    </button>
                    <button type="button" onclick="toggleVisibleWaRecipients(false)"
                            class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 text-[11px] font-bold transition-colors">
                        ✕ Batal Tampil
                    </button>
                    <button type="button" id="btn-save-wa-recipients" onclick="saveWaRecipients()"
                            class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs transition-all">
                        💾 Simpan Daftar Penerima
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-3 w-14 text-center">
                                <label class="inline-flex items-center justify-center gap-1 cursor-pointer select-none" title="Centang / Batal semua yang tampil">
                                    <input type="checkbox" id="chk-wa-all"
                                           <?= $canManageReminder ? 'onchange="toggleVisibleWaRecipients(this.checked)"' : 'disabled' ?>
                                           class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span>WA</span>
                                </label>
                            </th>
                            <th class="py-3 px-4">Staff &amp; Jabatan</th>
                            <th class="py-3 px-4">No. WhatsApp</th>
                            <th class="py-3 px-4 text-center">Progres</th>
                            <th class="py-3 px-4">Seluruh Jam Belum Diisi</th>
                            <th class="py-3 px-4 text-center">Kondisi</th>
                            <th class="py-3 px-4">WA Hari Ini</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($remItems)): ?>
                            <tr>
                                <td colspan="8" class="py-10 text-center text-slate-400">
                                    Belum ada pegawai aktif yang terdeteksi sebagai kategori Support System.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($remItems as $it): ?>
                                <?php
                                    $cat = $it['category'];
                                    $catBadge = match($cat) {
                                        'belum_isi'   => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'sebagian'    => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'cuti_izin'   => 'bg-sky-50 text-sky-700 border-sky-200',
                                        'libur'       => 'bg-sky-50 text-sky-700 border-sky-200',
                                        'nonaktif_wa' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        default       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    };
                                    $catLabel = match($cat) {
                                        'belum_isi'   => '✕ Belum Isi',
                                        'sebagian'    => '⚠ Sebagian',
                                        'cuti_izin'   => '🏖 ' . ($it['excused_reason'] ?: 'Cuti/Izin'),
                                        'libur'       => '🏖️ Hari Libur',
                                        'nonaktif_wa' => '🔕 Nonaktif WA',
                                        default       => '✓ Lengkap',
                                    };
                                    $waEnabledRow = !isset($it['wa_enabled']) || !empty($it['wa_enabled']);
                                    $itemPayload = [
                                        'pegawai_id' => $it['pegawai_id'],
                                        'nama'       => $it['nama'],
                                        'no_wa'      => $it['no_wa_formatted'],
                                        'kategori'   => $cat,
                                        'slot'       => $currentPreviewSlot,
                                        'pesan'      => $it['preview_message'],
                                    ];
                                ?>
                                <tr data-wa-row="1"
                                    data-nama="<?= e(strtolower($it['nama'])) ?>"
                                    data-jabatan="<?= e($it['jabatan']) ?>"
                                    class="hover:bg-slate-50/70 transition-colors align-top <?= $waEnabledRow ? '' : 'bg-slate-50/80 opacity-75' ?>">
                                    <td class="py-3.5 px-3 text-center">
                                        <input type="checkbox"
                                               class="chk-wa-recipient w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                               value="<?= (int)$it['pegawai_id'] ?>"
                                               <?= $waEnabledRow ? 'checked' : '' ?>
                                               <?= $canManageReminder ? 'onchange="onWaRecipientCheckChange()"' : 'disabled' ?>>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($it['nama']) ?></div>
                                        <div class="text-[11px] text-slate-500"><?= e($it['jabatan']) ?> &bull; <?= e($it['unit_tugas']) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap font-mono">
                                        <?php if ($it['has_valid_wa']): ?>
                                            <span class="text-slate-700 font-semibold">+<?= e($it['no_wa_formatted']) ?></span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200 font-sans">
                                                Belum ada No. WA
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="font-extrabold text-slate-800"><?= (int)$it['filled_required_count'] ?> / <?= (int)$it['required_count'] ?></span>
                                        <div class="text-[10px] text-slate-400"><?= (int)$it['filled_today_count'] ?> / <?= (int)$it['total_active_jams'] ?> sesi hari ini</div>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-sm">
                                        <?php if (empty($it['missing_jams']) || in_array($cat, ['cuti_izin', 'libur', 'nonaktif_wa'], true)): ?>
                                            <span class="text-[11px] text-slate-400 italic">-</span>
                                        <?php else: ?>
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach ($it['missing_jams'] as $mj): ?>
                                                    <?php $isOverdue = !empty($mj['is_overdue']); ?>
                                                    <span title="<?= e($mj['nama_sesi'] ?? '') ?>"
                                                          class="px-2 py-0.5 rounded-lg text-[10px] font-semibold border <?= $isOverdue ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-100 text-slate-600 border-slate-200' ?>">
                                                        <?= e($mj['rentang']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $catBadge ?>">
                                            <?= e($catLabel) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <?php if (empty($it['sent_slots_today'])): ?>
                                            <span class="text-[11px] text-slate-400">Belum ada</span>
                                        <?php else: ?>
                                            <div class="flex flex-wrap gap-1">
                                                <?php foreach ($it['sent_slots_today'] as $ss): ?>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <?= e($ss) ?> ✓
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <?php if ($it['needs_reminder']): ?>
                                            <div class="inline-flex items-center gap-1.5">
                                                <button type="button"
                                                        onclick='openWaPreviewModal(<?= json_encode($itemPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                        class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold transition-colors">
                                                    👁️ Preview
                                                </button>
                                                <?php if ($canManageReminder && $it['has_valid_wa']): ?>
                                                    <button type="button"
                                                            id="btn-send-single-<?= (int)$it['pegawai_id'] ?>"
                                                            onclick="sendSingleWaReminder(<?= (int)$it['pegawai_id'] ?>, '<?= e($currentPreviewSlot) ?>', this)"
                                                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs transition-colors">
                                                        💬 Kirim WA
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-[11px] text-slate-400 font-medium">
                                                <?= $cat === 'nonaktif_wa' ? 'Dikecualikan' : 'Tidak perlu' ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5.3 Dua Kolom: Pengaturan Jadwal & Template + Uji Kirim & Otomatisasi -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Kolom Kiri: Pengaturan Pengingat WA Otomatis (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/70">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>⚙️</span>
                        <span>Pengaturan Pengingat WA Otomatis</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Sesuaikan jadwal pengiriman, hari aktif, URL portal, dan format pesan untuk kedua kondisi.</p>
                </div>

                <form action="<?= url('kelola-jurnal-staff/pengingat/save') ?>" method="POST" class="p-5 space-y-4">
                    <?= CSRF::field() ?>

                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                        <div>
                            <label for="inp-wa-enabled" class="font-bold text-xs text-emerald-950 block cursor-pointer">
                                Aktifkan Pengingat WA Otomatis Jurnal Staff
                            </label>
                            <span class="text-[11px] text-emerald-700">Jika aktif, pengingat dikirim otomatis sesuai jadwal WITA dan hari kerja.</span>
                        </div>
                        <input type="checkbox" id="inp-wa-enabled" name="jurnal_staff_wa_enabled" value="1"
                               <?= ($remCfg['jurnal_staff_wa_enabled'] === '1') ? 'checked' : '' ?>
                               class="w-5 h-5 rounded border-emerald-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Jadwal Jam Pengingat (WITA) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="jurnal_staff_wa_schedules" value="<?= e($remCfg['jurnal_staff_wa_schedules']) ?>"
                                   placeholder="09:00,13:00,17:00" required
                                   class="w-full px-3.5 py-2.5 text-xs font-mono font-semibold text-slate-800 bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                            <p class="text-[10.5px] text-slate-400 mt-1">Pisahkan dengan koma format HH:MM (contoh: <code>09:00,13:00,17:00</code>).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Base URL Portal (Link Isi Jurnal)
                            </label>
                            <input type="text" name="jurnal_staff_wa_base_url" value="<?= e($remCfg['effective_base_url']) ?>"
                                   placeholder="https://portal.sekolah.sch.id"
                                   class="w-full px-3.5 py-2.5 text-xs font-mono text-slate-800 bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                            <p class="text-[10.5px] text-slate-400 mt-1">Link jurnal: <code class="text-emerald-700"><?= e($remCfg['jurnal_link']) ?></code></p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Hari Kerja Aktif Pengingat</label>
                        <div class="grid grid-cols-4 sm:grid-cols-7 gap-2">
                            <?php
                                $dayOpts = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                                foreach ($dayOpts as $dNum => $dLabel):
                                    $checkedDay = in_array($dNum, $activeDaysArr, true);
                            ?>
                                <label class="flex items-center justify-center gap-1.5 px-2.5 py-2 rounded-xl border text-xs font-semibold cursor-pointer transition-colors <?= $checkedDay ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-slate-50 border-slate-200 text-slate-500' ?>">
                                    <input type="checkbox" name="jurnal_staff_wa_active_days[]" value="<?= $dNum ?>" <?= $checkedDay ? 'checked' : '' ?>
                                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <span><?= $dLabel ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer text-xs">
                            <input type="checkbox" name="jurnal_staff_wa_skip_cuti_izin" value="1"
                                   <?= ($remCfg['jurnal_staff_wa_skip_cuti_izin'] === '1') ? 'checked' : '' ?>
                                   class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <strong class="block text-slate-800">Abaikan Staff Cuti / Izin / Sakit</strong>
                                <span class="text-[11px] text-slate-500">Tidak mengirim pengingat bila staff sedang cuti/izin disetujui.</span>
                            </span>
                        </label>

                        <label class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer text-xs">
                            <input type="checkbox" name="jurnal_staff_wa_exclude_istirahat" value="1"
                                   <?= ($remCfg['jurnal_staff_wa_exclude_istirahat'] === '1') ? 'checked' : '' ?>
                                   class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>
                                <strong class="block text-slate-800">Abaikan Sesi Istirahat / ISHOMA</strong>
                                <span class="text-[11px] text-slate-500">Sesi bertipe istirahat tidak dihitung sebagai jam wajib.</span>
                            </span>
                        </label>
                    </div>

                    <!-- Variable Chips Helper -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-600 uppercase">Variabel Pesan Tersedia (klik untuk salin/sisipkan):</span>
                            <button type="button" onclick="resetDefaultTemplates()" class="text-[11px] font-bold text-indigo-600 hover:underline">
                                ↺ Reset Template Default
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-1.5 text-[11px]">
                            <?php foreach (['{nama}', '{jabatan}', '{list_jam}', '{link_jurnal}', '{doa_motivasi}', '{tanggal}', '{jam_pengingat}'] as $varChip): ?>
                                <button type="button" onclick="insertTplVar('<?= $varChip ?>')"
                                        class="px-2 py-0.5 rounded-lg bg-white border border-slate-300 hover:border-emerald-400 text-slate-700 font-mono font-bold transition-colors">
                                    <?= $varChip ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-[10.5px] text-slate-500">
                            <code>{list_jam}</code> otomatis menampilkan seluruh jam yang belum diisi, dan <code>{doa_motivasi}</code> menampilkan doa &amp; motivasi kerja acak yang berbeda setiap hari.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Template 1: Jika Belum Mengisi Jurnal Sama Sekali
                        </label>
                        <textarea id="tpl-empty" name="jurnal_staff_wa_tpl_empty" rows="9" required
                                  onfocus="window._activeTplId = 'tpl-empty'"
                                  class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none font-sans leading-relaxed"><?= htmlspecialchars($remCfg['jurnal_staff_wa_tpl_empty']) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Template 2: Jika Sudah Mengisi Beberapa (Ada Jam Belum Diisi)
                        </label>
                        <textarea id="tpl-partial" name="jurnal_staff_wa_tpl_partial" rows="9" required
                                  onfocus="window._activeTplId = 'tpl-partial'"
                                  class="w-full px-3.5 py-2.5 text-xs text-slate-800 bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none font-sans leading-relaxed"><?= htmlspecialchars($remCfg['jurnal_staff_wa_tpl_partial']) ?></textarea>
                    </div>

                    <?php if ($canManageReminder): ?>
                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all">
                            💾 Simpan Pengaturan &amp; Sinkronkan Jadwal
                        </button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Kolom Kanan: Uji Kirim & Otomatisasi Scheduler (5 cols) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- Card Uji Kirim -->
                <div id="card-uji-kirim-wa" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-5 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Uji Kirim Pesan Pengingat</h3>
                            <p class="text-xs text-slate-500">Simulasi kirim langsung ke nomor WA Anda untuk cek format.</p>
                        </div>
                        <span class="text-xl">🧪</span>
                    </div>

                    <form id="form-test-wa-jurnal" onsubmit="handleTestWaJurnal(event)" class="p-5 space-y-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WA Tujuan Uji Coba <span class="text-rose-500">*</span></label>
                            <input type="text" id="test-wa-phone" name="target_phone" required placeholder="08123456789"
                                   class="w-full px-3.5 py-2.5 text-xs font-mono text-slate-800 bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Pesan</label>
                                <select id="test-wa-kategori" name="kategori" class="w-full px-3 py-2.5 text-xs font-semibold text-slate-800 bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:border-emerald-500 outline-none">
                                    <option value="belum_isi">Belum Isi Sama Sekali (Semua Jam)</option>
                                    <option value="sebagian">Sudah Isi Sebagian (Sisa Jam Belum Diisi)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Contoh</label>
                                <input type="text" id="test-wa-name" name="sample_name" value="Rekan Staff"
                                       class="w-full px-3 py-2.5 text-xs text-slate-800 bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:border-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan Contoh</label>
                                <input type="text" id="test-wa-jabatan" name="sample_jabatan" value="Staff IT & Media"
                                       class="w-full px-3 py-2.5 text-xs text-slate-800 bg-slate-50 rounded-xl border border-slate-200 focus:bg-white focus:border-emerald-500 outline-none">
                            </div>
                        </div>

                        <?php if ($canManageReminder): ?>
                        <button type="submit" id="btn-test-wa-jurnal"
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-2">
                            <span>🚀 Kirim Uji Coba Sekarang</span>
                        </button>
                        <?php endif; ?>

                        <div id="test-wa-jurnal-result" class="hidden p-3 rounded-xl text-xs border space-y-1.5"></div>
                    </form>
                </div>

                <!-- Card Panduan Otomatisasi Scheduler -->
                <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-2xl shadow-sm border border-slate-800 p-5 text-white space-y-3.5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-extrabold flex items-center gap-2">
                            <span>🤖</span>
                            <span>Otomatisasi Jadwal (Auto Scheduler)</span>
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Auto-Sync Aktif
                        </span>
                    </div>

                    <p class="text-xs text-slate-300 leading-relaxed">
                        <strong>1. Scheduler Bawaan WA Server:</strong> Selama server <code class="text-emerald-300">wa-server</code> berjalan &amp; terhubung WhatsApp, jadwal <strong>09:00, 13:00 &amp; 17:00 WITA</strong> otomatis terpanggil setiap menitnya sesuai file <code class="text-emerald-300">wa-server/scheduler-config.json</code>.
                    </p>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] text-slate-300 font-semibold">
                            <span>2. Alternatif Cron Webhook URL (aaPanel / Crontab):</span>
                            <button type="button" onclick="copyTextToClipboard(document.getElementById('cron-url-box').textContent, this)"
                                    class="text-emerald-300 hover:text-emerald-200 underline">
                                Salin URL
                            </button>
                        </div>
                        <div id="cron-url-box" class="bg-black/50 p-2.5 rounded-xl border border-white/10 font-mono text-[10.5px] text-emerald-300 break-all"><?= e($cronEndpointUrl) ?></div>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] text-slate-300 font-semibold">
                            <span>3. Alternatif CLI Cron Server (Setiap 09:00, 13:00, 17:00):</span>
                            <button type="button" onclick="copyTextToClipboard(document.getElementById('cron-cli-box').textContent, this)"
                                    class="text-emerald-300 hover:text-emerald-200 underline">
                                Salin Perintah
                            </button>
                        </div>
                        <div id="cron-cli-box" class="bg-black/50 p-2.5 rounded-xl border border-white/10 font-mono text-[10.5px] text-sky-300 break-all"><?= e($cliCronCmd) ?></div>
                    </div>

                    <?php if ($canManageReminder): ?>
                    <form action="<?= url('kelola-jurnal-staff/pengingat/regenerate-key') ?>" method="POST"
                          onsubmit="return confirm('Buat ulang Cron Secret Key? URL cron lama tidak akan berlaku lagi.');"
                          class="pt-1 flex justify-end">
                        <?= CSRF::field() ?>
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-slate-200 text-[11px] font-bold border border-white/15 transition-colors">
                            🔑 Buat Ulang Secret Key
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 5.4 Tabel Riwayat Log Pengingat WA Jurnal Staff -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>📜</span>
                        <span>Riwayat Log Pengingat WA Jurnal Staff (30 Terbaru)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Catatan pengiriman otomatis &amp; manual beserta alasan / daftar jam yang terlewat.</p>
                </div>
                <button type="button" onclick="location.href='<?= url('kelola-jurnal-staff?tab=pengingat&preview_slot=' . urlencode($currentPreviewSlot)) ?>'"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition-colors">
                    🔄 Refresh Log
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Staff</th>
                            <th class="py-3 px-4">No. WA</th>
                            <th class="py-3 px-4 text-center">Slot</th>
                            <th class="py-3 px-4 text-center">Kondisi</th>
                            <th class="py-3 px-4 text-center">Sumber</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($remLogs)): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Belum ada riwayat pengiriman pengingat WA Jurnal Staff.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($remLogs as $rl): ?>
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        <?= date('d/m/Y H:i', strtotime($rl['created_at'])) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800"><?= e($rl['nama_staff']) ?></div>
                                        <?php if (!empty($rl['jam_belum_list'])): ?>
                                            <div class="text-[10.5px] text-slate-500 truncate max-w-xs" title="<?= e($rl['jam_belum_list']) ?>">
                                                Jam belum: <?= e($rl['jam_belum_list']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-slate-700 whitespace-nowrap">+<?= e($rl['no_wa']) ?></td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-mono font-bold text-[11px]">
                                            <?= e($rl['slot_waktu']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <?php if ($rl['kategori'] === 'belum_isi'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Belum Isi Total</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Sebagian</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-mono text-[10px]">
                                            <?= e($rl['trigger_source']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <?php if ($rl['status'] === 'success'): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                ✓ Terkirim
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="<?= e($rl['error_message'] ?? '') ?>">
                                                ✕ Gagal
                                            </span>
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
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: INPUT JURNAL STAFF (BARU / MANUAL) -->
<!-- ========================================================================= -->
<div id="modal-input-jurnal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-100 overflow-visible my-8">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-primary-50/50 rounded-t-3xl">
            <div>
                <h3 id="modal-jurnal-title" class="text-lg font-bold text-slate-800">Input Catatan Jurnal Staff</h3>
                <p class="text-xs text-slate-500">Rekam aktivitas kerja staf support system & divisi</p>
            </div>
            <button type="button" onclick="closeModalInputJurnal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-white/80">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-jurnal" action="<?= url('kelola-jurnal-staff/jurnal/store') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" id="jurnal-action-mode" value="store">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Pilih Pegawai Staff -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pegawai Staff / Pelaksana <span class="text-rose-500">*</span></label>
                    <select name="pegawai_id" id="jurnal-pegawai-id" required onchange="handlePegawaiChange(this)" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                        <option value="">-- Pilih Pegawai Staff --</option>
                        <?php foreach ($staffList as $st): ?>
                            <option value="<?= (int)$st['id'] ?>" data-jabatan="<?= htmlspecialchars($st['jabatan']) ?>" data-unit="<?= htmlspecialchars($st['unit_tugas']) ?>">
                                <?= htmlspecialchars($st['nama']) ?> (<?= htmlspecialchars($st['jabatan'] ?: 'Staff') ?> - <?= htmlspecialchars($st['unit_tugas'] ?: 'Bina Insan') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tanggal -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Kegiatan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" id="jurnal-tanggal" value="<?= date('Y-m-d') ?>" required class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                </div>

                <!-- Jam Sesi (dari Pengaturan Jam) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Jam / Sesi Kerja <span class="text-rose-500">*</span></label>
                    <select name="jam_id" id="jurnal-jam-id" onchange="handleJamChange(this)" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                        <option value="">-- Pilih Sesi Jam Kerja --</option>
                        <?php foreach ($activeJamOptions as $aj): 
                            $rentang = date('H.i', strtotime($aj['jam_mulai'])) . ' - ' . date('H.i', strtotime($aj['jam_selesai']));
                        ?>
                            <option value="<?= (int)$aj['id'] ?>" data-rentang="<?= $rentang ?>">
                                <?= $rentang ?> (<?= htmlspecialchars($aj['nama_sesi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="jam_rentang" id="jurnal-jam-rentang" value="08:00 - 09:00">
                </div>

                <!-- Opsi Tugas / Job Desk (Multi-Select Stay-Open Dropdown) -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Pilih Tugas / Job Desk Divisi <span class="text-indigo-600 font-bold">(Bisa Pilih Lebih Dari 1)</span>
                    </label>
                    <input type="hidden" name="tugas_id" id="jurnal-tugas-id-primary" value="">
                    <input type="hidden" name="tugas_ids" id="jurnal-tugas-ids" value="">
                    <select id="jurnal-tugas-id" multiple data-multiple="true" data-count-label="Tugas"
                            onchange="handleTugasChange(this)"
                            class="searchable-select w-full text-xs"
                            data-placeholder="-- Pilih Tugas / Job Desk (Bisa Lebih Dari 1) --"
                            data-search-placeholder="Cari nama tugas / job desk...">
                    </select>
                </div>

                <!-- Nama Aktivitas / Pekerjaan -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Aktivitas / Nama Pekerjaan <span class="text-rose-500">*</span>
                        <span class="text-[11px] font-normal text-slate-400">(Otomatis terisi dari tugas terpilih atau ketik manual)</span>
                    </label>
                    <input type="text" name="aktivitas" id="jurnal-aktivitas" required placeholder="Contoh: Maintenance Jaringan • Backup Database Server" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                </div>

                <!-- Uraian Hasil Kerja -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Uraian Hasil Kerja <span class="text-rose-500">*</span></label>
                    <textarea name="uraian_hasil" id="jurnal-uraian-hasil" rows="3" required placeholder="Jelaskan detail apa saja yang telah dikerjakan dan hasil yang dicapai..." class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400"></textarea>
                </div>

                <!-- Kendala / Hambatan -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kendala / Solusi (Opsional)</label>
                    <textarea name="kendala" id="jurnal-kendala" rows="2" placeholder="Catat jika terdapat kendala, hambatan, atau tindak lanjut yang dibutuhkan..." class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400"></textarea>
                </div>

                <!-- Foto Bukti -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Foto Bukti Kegiatan (Opsional)</label>
                    <input type="file" name="foto_bukti" id="jurnal-foto-bukti" accept="image/*" class="w-full px-3 py-2 text-xs text-slate-500 bg-slate-50/70 rounded-xl border border-dashed border-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pelaksanaan <span class="text-rose-500">*</span></label>
                    <select name="status" id="jurnal-status" required class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                        <option value="selesai">Selesai</option>
                        <option value="proses">Dalam Proses</option>
                        <option value="tertunda">Tertunda</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalInputJurnal()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-md shadow-primary-600/20">
                    Simpan Catatan Jurnal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: DETAIL JURNAL STAFF (MODAL POPUP) -->
<!-- ========================================================================= -->
<div id="modal-detail-jurnal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-100 overflow-hidden my-8">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <span id="detail-kode-jurnal" class="text-[11px] font-mono text-indigo-600 font-bold"></span>
                <h3 class="text-base font-bold text-slate-800">Detail Catatan Jurnal Staff</h3>
            </div>
            <button type="button" onclick="closeModalDetailJurnal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
            <!-- Header Staff -->
            <div class="flex items-center gap-3 p-3 bg-indigo-50/50 rounded-2xl border border-indigo-100/60">
                <div id="detail-avatar" class="w-12 h-12 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shrink-0">
                    S
                </div>
                <div class="min-w-0">
                    <h4 id="detail-nama-staff" class="font-bold text-slate-800 text-sm"></h4>
                    <p id="detail-jabatan" class="text-xs text-indigo-700 font-medium"></p>
                    <p id="detail-unit" class="text-[11px] text-slate-400"></p>
                </div>
            </div>

            <!-- Grid Tanggal, Jam, Status -->
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Tanggal Kegiatan</span>
                    <span id="detail-tanggal" class="font-bold text-slate-800 mt-0.5 block"></span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Sesi Jam Kerja</span>
                    <span id="detail-jam-rentang" class="font-bold text-indigo-600 mt-0.5 block"></span>
                </div>
            </div>

            <!-- Aktivitas -->
            <div>
                <span class="text-slate-400 block text-[11px] uppercase font-bold mb-1">Aktivitas / Tugas</span>
                <p id="detail-aktivitas" class="text-sm font-semibold text-slate-800 bg-slate-50 p-3 rounded-xl border border-slate-100"></p>
            </div>

            <!-- Uraian Hasil -->
            <div>
                <span class="text-slate-400 block text-[11px] uppercase font-bold mb-1">Uraian Hasil Kerja</span>
                <p id="detail-uraian" class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 whitespace-pre-line leading-relaxed"></p>
            </div>

            <!-- Kendala -->
            <div id="detail-kendala-box" class="hidden">
                <span class="text-amber-600 block text-[11px] uppercase font-bold mb-1">Kendala / Tindak Lanjut</span>
                <p id="detail-kendala" class="text-xs text-amber-800 bg-amber-50 p-3 rounded-xl border border-amber-200 whitespace-pre-line leading-relaxed"></p>
            </div>

            <!-- Bukti Foto -->
            <div id="detail-foto-box" class="hidden">
                <span class="text-slate-400 block text-[11px] uppercase font-bold mb-1">Foto Bukti Kegiatan</span>
                <img id="detail-foto-img" src="" alt="Bukti Jurnal" class="w-full max-h-72 object-cover rounded-2xl border border-slate-200 shadow-sm cursor-pointer" onclick="previewImage(this.src)">
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 flex justify-end bg-slate-50">
            <button type="button" onclick="closeModalDetailJurnal()" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-100 rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: TAMBAH & EDIT JAM KERJA STAFF -->
<!-- ========================================================================= -->
<div id="modal-jam" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden my-8">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-primary-50/50">
            <div>
                <h3 id="modal-jam-title" class="text-lg font-bold text-slate-800">Tambah Sesi Jam Kerja Staff</h3>
                <p class="text-xs text-slate-500">Contoh: 07.00 - 08.00 (Tilawah), 08.00 - 09.00 (Aktifitas)</p>
            </div>
            <button type="button" onclick="closeModalJam()" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-white/80">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-jam" action="<?= url('kelola-jurnal-staff/jam/store') ?>" method="POST" class="p-6 space-y-4">
            <?= CSRF::field() ?>
            
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Mulai <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_mulai" id="jam-mulai" required value="07:00" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Selesai <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_selesai" id="jam-selesai" required value="08:00" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Sesi Jam Kerja <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_sesi" id="jam-nama-sesi" required placeholder="Misal: Tilawah & Dzikir Pagi / Aktifitas Kerja Sesi 1" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Sesi <span class="text-rose-500">*</span></label>
                    <select name="tipe" id="jam-tipe" required class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                        <option value="tilawah">Tilawah / Dzikir</option>
                        <option value="aktifitas" selected>Aktifitas Kerja</option>
                        <option value="istirahat">Istirahat / Ishoma</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Urutan</label>
                    <input type="number" name="urutan" id="jam-urutan" value="1" min="1" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Catatan Sesi</label>
                <textarea name="keterangan" id="jam-keterangan" rows="2" placeholder="Keterangan opsional untuk panduan sesi ini..." class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keaktifan</label>
                <select name="is_active" id="jam-is-active" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalJam()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-md shadow-primary-600/20">
                    Simpan Jam Kerja
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: TAMBAH & EDIT TUGAS (JOB DESK JABATAN) -->
<!-- ========================================================================= -->
<div id="modal-tugas" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden my-8">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-primary-50/50">
            <div>
                <h3 id="modal-tugas-title" class="text-lg font-bold text-slate-800">Tambah Tugas Jabatan</h3>
                <p id="modal-tugas-sub" class="text-xs text-slate-500">Kelola rincian job desk untuk jabatan referensi</p>
            </div>
            <button type="button" onclick="closeModalTugas()" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-white/80">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-tugas" action="<?= url('kelola-jurnal-staff/tugas/store') ?>" method="POST" class="p-6 space-y-4">
            <?= CSRF::field() ?>
            
            <!-- Pilih Jabatan Referensi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan Referensi <span class="text-rose-500">*</span></label>
                <select name="jabatan_id" id="tugas-jabatan-id" required class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                    <?php foreach ($masterJabatanList as $mj): ?>
                        <option value="<?= (int)$mj['id'] ?>" <?= $selectedJabatanId === (int)$mj['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mj['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nama Tugas -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Tugas / Job Desk <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_tugas" id="tugas-nama" required placeholder="Misal: Maintenance Server, Backup Database, Troubleshooting Jaringan..." class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
            </div>

            <!-- Kategori Tugas & Status -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Tugas <span class="text-rose-500">*</span></label>
                    <select name="kategori_tugas" id="tugas-kategori" required class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                        <option value="rutin">Rutin (Harian)</option>
                        <option value="berkala">Berkala (Mingguan/Bulanan)</option>
                        <option value="insidental">Insidental (Kondisional)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Keaktifan</label>
                    <select name="is_active" id="tugas-is-active" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>

            <!-- Deskripsi Tugas -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi / Panduan Kerja</label>
                <textarea name="deskripsi" id="tugas-deskripsi" rows="3" placeholder="Jelaskan detail petunjuk kerja tugas ini..." class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalTugas()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-md shadow-primary-600/20">
                    Simpan Tugas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: PENGATURAN PERIODE REKAPITULASI -->
<!-- ========================================================================= -->
<div id="modal-pengaturan-periode" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden my-8">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="text-base font-bold text-slate-800">Pengaturan Periode Rekap</h3>
                <p class="text-xs text-slate-500">Rentang default rekapitulasi jurnal staff</p>
            </div>
            <button type="button" onclick="closeModalPengaturanPeriode()" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form action="<?= url('kelola-jurnal-staff/pengaturan-periode') ?>" method="POST" class="p-6 space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="<?= htmlspecialchars($activeTab) ?>">

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Periode Awal <span class="text-rose-500">*</span></label>
                <input type="date" name="periode_awal" value="<?= htmlspecialchars($periodeConfig['periode_awal'] ?: date('Y-m-01')) ?>" required class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Periode Akhir <span class="text-rose-500">*</span></label>
                <input type="date" name="periode_akhir" value="<?= htmlspecialchars($periodeConfig['periode_akhir'] ?: date('Y-m-t')) ?>" required class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Periode</label>
                <input type="text" name="keterangan" value="<?= htmlspecialchars($periodeConfig['keterangan'] ?? '') ?>" placeholder="Misal: Periode Rekap Bulan November 2026" class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50/70 hover:bg-white focus:bg-white rounded-xl border border-slate-200 hover:border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none focus:outline-none transition-all placeholder:text-slate-400">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalPengaturanPeriode()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all shadow-md shadow-primary-600/20">
                    Simpan Periode
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 6: PREVIEW FOTO POPUP -->
<!-- ========================================================================= -->
<div id="modal-image-preview" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeImagePreview()">
    <div class="relative max-w-3xl max-h-[90vh] p-2" onclick="event.stopPropagation()">
        <img id="preview-image-src" src="" alt="Bukti Foto" class="max-w-full max-h-[85vh] rounded-2xl shadow-2xl object-contain border-2 border-white/20">
        <button type="button" onclick="closeImagePreview()" class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-white text-slate-800 font-bold flex items-center justify-center shadow-lg hover:bg-slate-100">
            ✕
        </button>
    </div>
</div>

<script>
/**
 * Switch Tab Handler
 */
function switchStaffTab(tabName) {
    // 1. Update URL query tanpa full reload jika memungkinkan
    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.history.pushState({}, '', url);

    // 2. Hide all tab panels
    document.querySelectorAll('.staff-tab-panel').forEach(panel => {
        panel.classList.add('hidden');
    });

    // 3. Show active panel
    const target = document.getElementById('tab-content-' + tabName);
    if (target) {
        target.classList.remove('hidden');
    }

    // 4. Update Tab Buttons style
    document.querySelectorAll('.staff-tab-button').forEach(btn => {
        btn.classList.remove('border-indigo-600', 'text-indigo-600', 'border-emerald-600', 'text-emerald-700');
        btn.classList.add('border-transparent', 'text-slate-500');
    });
    const activeBtn = document.querySelector(`[data-staff-tab-btn="${tabName}"]`);
    if (activeBtn) {
        activeBtn.classList.remove('border-transparent', 'text-slate-500');
        if (tabName === 'pengingat') {
            activeBtn.classList.add('border-emerald-600', 'text-emerald-700');
        } else {
            activeBtn.classList.add('border-indigo-600', 'text-indigo-600');
        }
    }

    // 5. Update Sidebar Links style
    document.querySelectorAll('.staff-sidebar-link').forEach(link => {
        link.classList.remove('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
        link.classList.add('text-primary-200', 'font-medium', 'border-transparent');
    });
    const activeSb = document.querySelector(`[data-staff-sidebar-tab="${tabName}"]`);
    if (activeSb) {
        activeSb.classList.remove('text-primary-200', 'font-medium', 'border-transparent');
        activeSb.classList.add('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
    }
}

function applyAdminTugasPreselection(tugasSelect, rawTugasIds, rawAktivitas) {
    if (!tugasSelect) return;
    Array.from(tugasSelect.options).forEach(o => { o.selected = false; });

    const byId = {};
    const byLower = {};
    Array.from(tugasSelect.options).forEach(o => {
        if (!o.value) return;
        const nm = (o.getAttribute('data-nama') || o.textContent || '').trim();
        byId[String(o.value)] = o;
        if (nm) byLower[nm.toLowerCase()] = o;
    });

    const idList = String(rawTugasIds || '')
        .split(/[\s,;]+/)
        .map(s => parseInt(s, 10))
        .filter(n => !isNaN(n) && n > 0);
    const aktParts = String(rawAktivitas || '')
        .split(/\s*•\s*/)
        .map(s => s.trim())
        .filter(Boolean);

    idList.forEach(id => {
        if (byId[String(id)]) byId[String(id)].selected = true;
    });
    aktParts.forEach(part => {
        const low = part.toLowerCase();
        if (byLower[low]) byLower[low].selected = true;
    });

    const selectedMasterIds = Array.from(tugasSelect.options)
        .filter(o => o.selected && o.value !== '')
        .map(o => parseInt(o.value, 10))
        .filter(n => n > 0);

    const inpPrimary = document.getElementById('jurnal-tugas-id-primary');
    const inpIds = document.getElementById('jurnal-tugas-ids');
    if (inpPrimary) inpPrimary.value = selectedMasterIds.length > 0 ? String(selectedMasterIds[0]) : '';
    if (inpIds) inpIds.value = selectedMasterIds.join(',');
}

/**
 * Handle Pegawai Change di Modal Input Jurnal:
 * Mengambil referensi tugas berdasarkan jabatan pegawai via API
 */
function handlePegawaiChange(selectEl, preselectTugasIds = null, preselectAktivitas = null) {
    const opt = selectEl && selectEl.options ? selectEl.options[selectEl.selectedIndex] : null;
    const tugasSelect = document.getElementById('jurnal-tugas-id');
    if (!tugasSelect) return;

    tugasSelect.innerHTML = '';
    if (window.SearchableSelect && typeof window.SearchableSelect.refresh === 'function') {
        window.SearchableSelect.refresh(tugasSelect);
    }

    if (!preselectTugasIds && !preselectAktivitas) {
        const inpPrimary = document.getElementById('jurnal-tugas-id-primary');
        const inpIds = document.getElementById('jurnal-tugas-ids');
        if (inpPrimary) inpPrimary.value = '';
        if (inpIds) inpIds.value = '';
    }

    if (!opt) return;

    const jabatanName = opt.getAttribute('data-jabatan') || '';
    if (!jabatanName) return;

    // Cari jabatan_id dari dropdown tugas-jabatan-id jika ada
    const jSelect = document.getElementById('tugas-jabatan-id');
    let jId = null;
    if (jSelect) {
        for (let i = 0; i < jSelect.options.length; i++) {
            if (jSelect.options[i].text.toLowerCase().trim() === jabatanName.toLowerCase().trim()) {
                jId = jSelect.options[i].value;
                break;
            }
        }
    }

    if (jId) {
        fetch('<?= url("kelola-jurnal-staff/api/tugas") ?>/' + jId)
            .then(res => res.json())
            .then(resp => {
                if (resp.success && resp.data) {
                    resp.data.forEach(t => {
                        const newOpt = document.createElement('option');
                        newOpt.value = t.id;
                        newOpt.textContent = t.nama_tugas;
                        newOpt.setAttribute('data-nama', t.nama_tugas);
                        if (t.kategori_tugas) {
                            newOpt.setAttribute('data-badge', String(t.kategori_tugas).toUpperCase());
                        }
                        if (t.deskripsi) {
                            newOpt.setAttribute('data-subtext', t.deskripsi);
                        }
                        tugasSelect.appendChild(newOpt);
                    });
                    if (preselectTugasIds || preselectAktivitas) {
                        applyAdminTugasPreselection(tugasSelect, preselectTugasIds, preselectAktivitas);
                    }
                    if (window.SearchableSelect && typeof window.SearchableSelect.refresh === 'function') {
                        window.SearchableSelect.refresh(tugasSelect);
                    }
                }
            })
            .catch(err => console.error(err));
    }
}

function handleJamChange(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt) return;
    const rentang = opt.getAttribute('data-rentang') || '';
    if (rentang) {
        document.getElementById('jurnal-jam-rentang').value = rentang;
    }
}

function handleTugasChange(selectEl) {
    if (!selectEl) return;
    const selectedOpts = Array.from(selectEl.options).filter(o => o.selected && o.value !== '');
    const masterIds = selectedOpts.map(o => parseInt(o.value, 10)).filter(n => n > 0);
    const names = selectedOpts.map(o => (o.getAttribute('data-nama') || o.text || '').trim()).filter(Boolean);

    const inpPrimary = document.getElementById('jurnal-tugas-id-primary');
    const inpIds = document.getElementById('jurnal-tugas-ids');
    const inpAkt = document.getElementById('jurnal-aktivitas');

    if (inpPrimary) inpPrimary.value = masterIds.length > 0 ? String(masterIds[0]) : '';
    if (inpIds) inpIds.value = masterIds.join(',');
    if (inpAkt) inpAkt.value = names.join(' • ');
}

/**
 * Modal Jurnal Handlers
 */
function openModalInputJurnal() {
    document.getElementById('modal-jurnal-title').textContent = 'Input Catatan Jurnal Staff';
    document.getElementById('form-jurnal').action = '<?= url("kelola-jurnal-staff/jurnal/store") ?>';
    document.getElementById('form-jurnal').reset();
    const inpPrimary = document.getElementById('jurnal-tugas-id-primary');
    const inpIds = document.getElementById('jurnal-tugas-ids');
    if (inpPrimary) inpPrimary.value = '';
    if (inpIds) inpIds.value = '';
    const tugasSelect = document.getElementById('jurnal-tugas-id');
    if (tugasSelect) {
        tugasSelect.innerHTML = '';
        if (window.SearchableSelect && typeof window.SearchableSelect.refresh === 'function') {
            window.SearchableSelect.refresh(tugasSelect);
        }
    }
    document.getElementById('modal-input-jurnal').classList.remove('hidden');
}

function closeModalInputJurnal() {
    if (window.SearchableSelect && typeof window.SearchableSelect.closeAll === 'function') {
        window.SearchableSelect.closeAll();
    }
    document.getElementById('modal-input-jurnal').classList.add('hidden');
}

function editJurnal(data) {
    document.getElementById('modal-jurnal-title').textContent = 'Edit Catatan Jurnal Staff (' + data.kode_jurnal + ')';
    document.getElementById('form-jurnal').action = '<?= url("kelola-jurnal-staff/jurnal/update") ?>/' + data.id;
    
    const pegawaiSelect = document.getElementById('jurnal-pegawai-id');
    pegawaiSelect.value = data.pegawai_id || '';
    document.getElementById('jurnal-tanggal').value = data.tanggal || '';
    document.getElementById('jurnal-jam-id').value = data.jam_id || '';
    document.getElementById('jurnal-jam-rentang').value = data.jam_rentang || '';
    document.getElementById('jurnal-aktivitas').value = data.aktivitas || '';
    document.getElementById('jurnal-uraian-hasil').value = data.uraian_hasil || '';
    document.getElementById('jurnal-kendala').value = data.kendala || '';
    document.getElementById('jurnal-status').value = data.status || 'selesai';

    const rawTugasIds = data.tugas_ids || data.tugas_id || '';
    handlePegawaiChange(pegawaiSelect, rawTugasIds, data.aktivitas || '');

    document.getElementById('modal-input-jurnal').classList.remove('hidden');
}

function viewDetailJurnal(id) {
    fetch('<?= url("kelola-jurnal-staff/api/detail") ?>/' + id)
        .then(res => res.json())
        .then(resp => {
            if (resp.success && resp.data) {
                const d = resp.data;
                document.getElementById('detail-kode-jurnal').textContent = d.kode_jurnal;
                document.getElementById('detail-nama-staff').textContent = d.nama_staff;
                document.getElementById('detail-jabatan').textContent = d.jabatan;
                document.getElementById('detail-unit').textContent = d.unit_tugas || 'Bina Insan Palu';
                document.getElementById('detail-tanggal').textContent = d.tanggal;
                document.getElementById('detail-jam-rentang').textContent = d.jam_rentang + (d.nama_sesi ? ' (' + d.nama_sesi + ')' : '');

                const detailAktEl = document.getElementById('detail-aktivitas');
                const aktParts = String(d.aktivitas || '').split(/\s*•\s*/).map(s => s.trim()).filter(Boolean);
                if (detailAktEl) {
                    if (aktParts.length > 1) {
                        detailAktEl.innerHTML = '';
                        const ul = document.createElement('ul');
                        ul.className = 'space-y-1 list-disc list-inside';
                        aktParts.forEach(p => {
                            const li = document.createElement('li');
                            li.textContent = p;
                            ul.appendChild(li);
                        });
                        detailAktEl.appendChild(ul);
                    } else {
                        detailAktEl.textContent = d.aktivitas || '-';
                    }
                }
                document.getElementById('detail-uraian').textContent = d.uraian_hasil;

                if (d.kendala && d.kendala.trim() !== '') {
                    document.getElementById('detail-kendala').textContent = d.kendala;
                    document.getElementById('detail-kendala-box').classList.remove('hidden');
                } else {
                    document.getElementById('detail-kendala-box').classList.add('hidden');
                }

                if (d.foto_bukti) {
                    document.getElementById('detail-foto-img').src = '<?= url("public") ?>/' + d.foto_bukti.replace(/^\/+/, '');
                    document.getElementById('detail-foto-box').classList.remove('hidden');
                } else {
                    document.getElementById('detail-foto-box').classList.add('hidden');
                }

                document.getElementById('modal-detail-jurnal').classList.remove('hidden');
            }
        })
        .catch(err => alert('Gagal memuat detail: ' + err.message));
}

function closeModalDetailJurnal() {
    document.getElementById('modal-detail-jurnal').classList.add('hidden');
}

/**
 * Modal Jam Handlers
 */
function openModalTambahJam() {
    document.getElementById('modal-jam-title').textContent = 'Tambah Sesi Jam Kerja Staff';
    document.getElementById('form-jam').action = '<?= url("kelola-jurnal-staff/jam/store") ?>';
    document.getElementById('form-jam').reset();
    document.getElementById('modal-jam').classList.remove('hidden');
}

function closeModalJam() {
    document.getElementById('modal-jam').classList.add('hidden');
}

function editJam(data) {
    document.getElementById('modal-jam-title').textContent = 'Edit Sesi Jam Kerja';
    document.getElementById('form-jam').action = '<?= url("kelola-jurnal-staff/jam/update") ?>/' + data.id;
    
    document.getElementById('jam-mulai').value = data.jam_mulai ? data.jam_mulai.substring(0, 5) : '';
    document.getElementById('jam-selesai').value = data.jam_selesai ? data.jam_selesai.substring(0, 5) : '';
    document.getElementById('jam-nama-sesi').value = data.nama_sesi || '';
    document.getElementById('jam-tipe').value = data.tipe || 'aktifitas';
    document.getElementById('jam-urutan').value = data.urutan || 1;
    document.getElementById('jam-keterangan').value = data.keterangan || '';
    document.getElementById('jam-is-active').value = data.is_active;

    document.getElementById('modal-jam').classList.remove('hidden');
}

/**
 * Modal Tugas Handlers
 */
function openModalTambahTugas(jabatanId, jabatanNama) {
    document.getElementById('modal-tugas-title').textContent = 'Tambah Tugas untuk ' + jabatanNama;
    document.getElementById('form-tugas').action = '<?= url("kelola-jurnal-staff/tugas/store") ?>';
    document.getElementById('form-tugas').reset();
    if (jabatanId) {
        document.getElementById('tugas-jabatan-id').value = jabatanId;
    }
    document.getElementById('modal-tugas').classList.remove('hidden');
}

function closeModalTugas() {
    document.getElementById('modal-tugas').classList.add('hidden');
}

function editTugas(data) {
    document.getElementById('modal-tugas-title').textContent = 'Edit Tugas Jabatan';
    document.getElementById('form-tugas').action = '<?= url("kelola-jurnal-staff/tugas/update") ?>/' + data.id;
    
    document.getElementById('tugas-jabatan-id').value = data.jabatan_id || '';
    document.getElementById('tugas-nama').value = data.nama_tugas || '';
    document.getElementById('tugas-kategori').value = data.kategori_tugas || 'rutin';
    document.getElementById('tugas-deskripsi').value = data.deskripsi || '';
    document.getElementById('tugas-is-active').value = data.is_active;

    document.getElementById('modal-tugas').classList.remove('hidden');
}

/**
 * Modal Periode & Preview Image
 */
function openModalPengaturanPeriode() {
    document.getElementById('modal-pengaturan-periode').classList.remove('hidden');
}

function closeModalPengaturanPeriode() {
    document.getElementById('modal-pengaturan-periode').classList.add('hidden');
}

function previewImage(url) {
    document.getElementById('preview-image-src').src = url;
    document.getElementById('modal-image-preview').classList.remove('hidden');
}

function closeImagePreview() {
    document.getElementById('modal-image-preview').classList.add('hidden');
}
</script>

<!-- ========================================================================= -->
<!-- MODAL 7: PREVIEW PESAN WA PENGINGAT JURNAL STAFF -->
<!-- ========================================================================= -->
<div id="modal-preview-wa-jurnal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" onclick="closeWaPreviewModal()">
    <div class="relative bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden my-8" onclick="event.stopPropagation()">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-emerald-50/70">
            <div>
                <h3 class="text-base font-bold text-slate-800">Preview Pesan WhatsApp</h3>
                <p id="preview-wa-sub" class="text-xs text-slate-500">Staff Support System</p>
            </div>
            <button type="button" onclick="closeWaPreviewModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-white">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-5 bg-[#efeae2]">
            <div class="bg-[#d9fdd3] text-slate-900 rounded-2xl rounded-tr-sm p-4 shadow-sm border border-emerald-200/70 text-xs whitespace-pre-wrap leading-relaxed font-sans" id="preview-wa-bubble"></div>
        </div>
        <div class="p-4 border-t border-slate-100 flex items-center justify-end gap-2 bg-white">
            <button type="button" onclick="closeWaPreviewModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                Tutup
            </button>
            <button type="button" id="btn-preview-send-wa" onclick="sendWaFromPreviewModal()" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition-colors">
                💬 Kirim WA Sekarang
            </button>
        </div>
    </div>
</div>

<script>
const JURNAL_STAFF_CSRF_TOKEN = <?= json_encode(CSRF::token()) ?>;
const DEFAULT_WA_TPL_EMPTY = <?= json_encode(JurnalStaffReminderService::DEFAULT_TPL_EMPTY) ?>;
const DEFAULT_WA_TPL_PARTIAL = <?= json_encode(JurnalStaffReminderService::DEFAULT_TPL_PARTIAL) ?>;
window._previewWaState = null;

function openWaPreviewModal(item) {
    window._previewWaState = item;
    document.getElementById('preview-wa-sub').textContent = (item.nama || 'Staff') + (item.no_wa ? ' (+' + item.no_wa + ')' : ' (Tanpa No. WA)');
    document.getElementById('preview-wa-bubble').textContent = item.pesan || '';
    const sendBtn = document.getElementById('btn-preview-send-wa');
    if (sendBtn) {
        if (!item.no_wa) {
            sendBtn.classList.add('hidden');
        } else {
            sendBtn.classList.remove('hidden');
            sendBtn.disabled = false;
            sendBtn.textContent = '💬 Kirim WA Sekarang';
        }
    }
    document.getElementById('modal-preview-wa-jurnal').classList.remove('hidden');
}

function closeWaPreviewModal() {
    document.getElementById('modal-preview-wa-jurnal').classList.add('hidden');
}

function sendWaFromPreviewModal() {
    if (!window._previewWaState || !window._previewWaState.pegawai_id) return;
    const btn = document.getElementById('btn-preview-send-wa');
    sendSingleWaReminder(window._previewWaState.pegawai_id, window._previewWaState.slot || '17:00', btn, function(ok) {
        if (ok) {
            setTimeout(function() { closeWaPreviewModal(); location.reload(); }, 900);
        }
    });
}

function sendSingleWaReminder(pegawaiId, slot, btnEl, callback) {
    const origText = btnEl ? btnEl.textContent : '';
    if (btnEl) {
        btnEl.disabled = true;
        btnEl.textContent = '⏳ Mengirim...';
    }

    const body = new URLSearchParams();
    body.set('<?= CSRF_TOKEN_NAME ?>', JURNAL_STAFF_CSRF_TOKEN);
    body.set('pegawai_id', String(pegawaiId));
    body.set('slot', slot || '17:00');

    fetch('<?= url("kelola-jurnal-staff/pengingat/send-single") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.textContent = data.success ? '✓ Terkirim' : origText;
        }
        alert(data.message || (data.success ? 'Pengingat berhasil dikirim.' : 'Gagal mengirim pengingat.'));
        if (typeof callback === 'function') callback(Boolean(data.success));
    })
    .catch(err => {
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.textContent = origText;
        }
        alert('Terjadi kesalahan jaringan: ' + err.message);
        if (typeof callback === 'function') callback(false);
    });
}

function runBulkWaReminder() {
    const slotEl = document.getElementById('bulk-run-slot');
    const forceEl = document.getElementById('bulk-run-force');
    const btn = document.getElementById('btn-run-bulk-wa');
    const resBox = document.getElementById('bulk-run-result');

    const slot = slotEl ? slotEl.value : '17:00';
    const force = forceEl && forceEl.checked ? '1' : '';

    if (!confirm('Kirim pengingat WA sekarang ke seluruh staff Support System yang belum melengkapi jurnal pada slot "' + slot + '"?')) {
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Mengirim...</span>';
    }
    if (resBox) {
        resBox.classList.add('hidden');
    }

    const body = new URLSearchParams();
    body.set('<?= CSRF_TOKEN_NAME ?>', JURNAL_STAFF_CSRF_TOKEN);
    body.set('ajax', '1');
    body.set('slot', slot);
    if (force) body.set('force', '1');

    fetch('<?= url("kelola-jurnal-staff/pengingat/run") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>📤 Kirim Pengingat Sekarang</span>';
        }
        if (resBox) {
            resBox.classList.remove('hidden');
            resBox.className = 'text-xs p-2.5 rounded-xl border ' + (data.success ? 'bg-emerald-950/60 border-emerald-400/40 text-emerald-200' : 'bg-rose-950/60 border-rose-400/40 text-rose-200');
            resBox.textContent = data.message || data.reason || 'Proses selesai.';
        }
        if (data.success && (data.sent || 0) > 0) {
            setTimeout(() => location.reload(), 1600);
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>📤 Kirim Pengingat Sekarang</span>';
        }
        if (resBox) {
            resBox.classList.remove('hidden');
            resBox.className = 'text-xs p-2.5 rounded-xl border bg-rose-950/60 border-rose-400/40 text-rose-200';
            resBox.textContent = 'Gagal memproses: ' + err.message;
        }
    });
}

function handleTestWaJurnal(event) {
    event.preventDefault();
    const phone = document.getElementById('test-wa-phone').value.trim();
    const kategori = document.getElementById('test-wa-kategori').value;
    const sampleName = document.getElementById('test-wa-name').value.trim() || 'Rekan Staff';
    const sampleJabatanEl = document.getElementById('test-wa-jabatan');
    const sampleJabatan = sampleJabatanEl ? (sampleJabatanEl.value.trim() || 'Staff IT & Media') : 'Staff IT & Media';
    const btn = document.getElementById('btn-test-wa-jurnal');
    const resBox = document.getElementById('test-wa-jurnal-result');

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>⏳ Mengirim Uji Coba...</span>';
    }

    const body = new URLSearchParams();
    body.set('<?= CSRF_TOKEN_NAME ?>', JURNAL_STAFF_CSRF_TOKEN);
    body.set('target_phone', phone);
    body.set('kategori', kategori);
    body.set('sample_name', sampleName);
    body.set('sample_jabatan', sampleJabatan);

    fetch('<?= url("kelola-jurnal-staff/pengingat/test") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>🚀 Kirim Uji Coba Sekarang</span>';
        }
        if (resBox) {
            resBox.classList.remove('hidden');
            resBox.className = 'p-3 rounded-xl text-xs border space-y-1.5 ' + (data.success ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-rose-50 border-rose-200 text-rose-900');
            resBox.innerHTML = '<div class="font-bold"></div><pre class="whitespace-pre-wrap text-[11px] bg-white/80 p-2 rounded-lg border border-slate-200 font-sans"></pre>';
            resBox.firstChild.textContent = data.message || (data.success ? 'Pesan uji coba terkirim.' : 'Gagal mengirim pesan uji coba.');
            resBox.lastChild.textContent = data.preview || '';
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>🚀 Kirim Uji Coba Sekarang</span>';
        }
        if (resBox) {
            resBox.classList.remove('hidden');
            resBox.className = 'p-3 rounded-xl text-xs border bg-rose-50 border-rose-200 text-rose-900';
            resBox.textContent = 'Error: ' + err.message;
        }
    });
}

function getCheckedWaRecipientKey() {
    return Array.from(document.querySelectorAll('.chk-wa-recipient'))
        .filter(cb => cb.checked)
        .map(cb => cb.value)
        .join(',');
}

const INITIAL_WA_RECIPIENT_KEY = getCheckedWaRecipientKey();

function syncWaSelectAllCheckbox() {
    const master = document.getElementById('chk-wa-all');
    if (!master) return;
    const visibleCbs = [];
    document.querySelectorAll('tr[data-wa-row="1"]').forEach(row => {
        if (row.style.display !== 'none') {
            const cb = row.querySelector('.chk-wa-recipient');
            if (cb) visibleCbs.push(cb);
        }
    });
    if (visibleCbs.length === 0) {
        master.checked = false;
        master.indeterminate = false;
        return;
    }
    const checkedCount = visibleCbs.filter(cb => cb.checked).length;
    master.checked = (checkedCount === visibleCbs.length);
    master.indeterminate = (checkedCount > 0 && checkedCount < visibleCbs.length);
}

function onWaRecipientCheckChange() {
    const allCbs = Array.from(document.querySelectorAll('.chk-wa-recipient'));
    const checkedCount = allCbs.filter(cb => cb.checked).length;
    const totalCount = allCbs.length;

    allCbs.forEach(cb => {
        const row = cb.closest('tr[data-wa-row="1"]');
        if (row) {
            row.classList.toggle('bg-slate-50/80', !cb.checked);
            row.classList.toggle('opacity-75', !cb.checked);
        }
    });

    const counterEl = document.getElementById('wa-recipient-counter');
    if (counterEl) {
        counterEl.textContent = 'Aktif WA: ' + checkedCount + ' / ' + totalCount + ' Staff';
    }

    const dirtyEl = document.getElementById('wa-recipient-dirty');
    if (dirtyEl) {
        const isDirty = (getCheckedWaRecipientKey() !== INITIAL_WA_RECIPIENT_KEY);
        dirtyEl.classList.toggle('hidden', !isDirty);
    }

    syncWaSelectAllCheckbox();
}

function filterWaStaffTable() {
    const q = (document.getElementById('filter-wa-staff-search')?.value || '').trim().toLowerCase();
    const jab = document.getElementById('filter-wa-jabatan')?.value || '';

    document.querySelectorAll('tr[data-wa-row="1"]').forEach(row => {
        const rowNama = (row.getAttribute('data-nama') || '').toLowerCase();
        const rowJab = row.getAttribute('data-jabatan') || '';
        const matchQ = (q === '' || rowNama.includes(q) || rowJab.toLowerCase().includes(q));
        const matchJab = (jab === '' || rowJab === jab);
        row.style.display = (matchQ && matchJab) ? '' : 'none';
    });

    syncWaSelectAllCheckbox();
}

function toggleVisibleWaRecipients(checked) {
    document.querySelectorAll('tr[data-wa-row="1"]').forEach(row => {
        if (row.style.display !== 'none') {
            const cb = row.querySelector('.chk-wa-recipient');
            if (cb && !cb.disabled) {
                cb.checked = Boolean(checked);
            }
        }
    });
    onWaRecipientCheckChange();
}

function saveWaRecipients() {
    const allCbs = Array.from(document.querySelectorAll('.chk-wa-recipient'));
    const enabledIds = allCbs.filter(cb => cb.checked).map(cb => cb.value);

    if (enabledIds.length === 0) {
        if (!confirm('Tidak ada staff yang dicentang untuk menerima notifikasi WA. Yakin ingin menonaktifkan seluruh penerima?')) {
            return;
        }
    }

    const btn = document.getElementById('btn-save-wa-recipients');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Menyimpan...';
    }

    const body = new URLSearchParams();
    body.set('<?= CSRF_TOKEN_NAME ?>', JURNAL_STAFF_CSRF_TOKEN);
    body.set('ajax', '1');
    enabledIds.forEach(id => body.append('enabled_ids[]', id));

    fetch('<?= url("kelola-jurnal-staff/pengingat/recipients") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        alert(data.message || (data.success ? 'Daftar penerima WA berhasil disimpan.' : 'Gagal menyimpan daftar penerima.'));
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        alert('Terjadi kesalahan jaringan: ' + err.message);
    });
}

document.addEventListener('DOMContentLoaded', syncWaSelectAllCheckbox);

function insertTplVar(token) {
    const targetId = window._activeTplId || 'tpl-partial';
    const el = document.getElementById(targetId);
    if (!el) return;
    const start = el.selectionStart || 0;
    const end = el.selectionEnd || 0;
    const val = el.value;
    el.value = val.substring(0, start) + token + val.substring(end);
    el.focus();
    el.setSelectionRange(start + token.length, start + token.length);
}

function resetDefaultTemplates() {
    if (!confirm('Kembalikan kedua template pesan ke format default Portal BIP?')) return;
    const tEmpty = document.getElementById('tpl-empty');
    const tPartial = document.getElementById('tpl-partial');
    if (tEmpty) tEmpty.value = DEFAULT_WA_TPL_EMPTY;
    if (tPartial) tPartial.value = DEFAULT_WA_TPL_PARTIAL;
}

function copyTextToClipboard(text, btn) {
    if (!navigator.clipboard) {
        alert('Salin manual:\n' + text);
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        if (btn) {
            const old = btn.textContent;
            btn.textContent = '✓ Tersalin!';
            setTimeout(() => { btn.textContent = old; }, 1500);
        }
    }).catch(() => alert('Salin manual:\n' + text));
}
</script>
