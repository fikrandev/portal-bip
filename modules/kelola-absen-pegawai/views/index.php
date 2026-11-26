<?php
/**
 * View Utama Modul Kelola Absen Pegawai
 * Portal BIP
 *
 * Fitur:
 * 1. Pengaturan Radius Global (5m, 10m, 15m, dst. berlaku untuk SEMUA pegawai & SEMUA unit)
 * 2. Lokasi Absen Masing-Masing Pegawai (Custom Per Pegawai + Bulk Custom + Reset per Pegawai)
 * 3. Lokasi Absen Custom Per Unit (Custom Per Unit + Reset per Unit)
 * 4. Pengaturan Lokasi Absen Semula / Default + Reset Semua ke Lokasi Semula
 * 5. Peta Interaktif (Leaflet) untuk memilih titik Latitude & Longitude secara presisi
 */

$activeTab = $activeTab ?? 'pegawai';
$radiusGlobal = (int)($globalConfig['radius_meter'] ?? 10);
$defaultKantor = $globalConfig['nama_kantor_default'] ?? 'Kampus Utama SIT Bina Insan Palu';
$defaultLat = (float)($globalConfig['latitude_default'] ?? -0.8917000);
$defaultLng = (float)($globalConfig['longitude_default'] ?? 119.8707000);
$jamMasukMulai = substr($globalConfig['jam_masuk_mulai'] ?? '06:00:00', 0, 5);
$jamMasukBatas = substr($globalConfig['jam_masuk_batas'] ?? '07:30:00', 0, 5);
$jamPulangMulai = substr($globalConfig['jam_pulang_mulai'] ?? '15:00:00', 0, 5);
$sabtuKhususAktif = !empty($globalConfig['sabtu_khusus_aktif']);
$sabtuJamMasukMulai = substr($globalConfig['sabtu_jam_masuk_mulai'] ?? $jamMasukMulai, 0, 5);
$sabtuJamMasukBatas = substr($globalConfig['sabtu_jam_masuk_batas'] ?? '07:30:00', 0, 5);
$sabtuJamPulangMulai = substr($globalConfig['sabtu_jam_pulang_mulai'] ?? $jamPulangMulai, 0, 5);
$globalJamHariIni = AbsenPegawaiModel::resolveGlobalJamForDate($globalConfig);
$jamMasukBatasHariIni = substr($globalJamHariIni['jam_masuk_batas'], 0, 5);
$jamPulangMulaiHariIni = substr($globalJamHariIni['jam_pulang_mulai'], 0, 5);
$targetDate = $targetDate ?? ($rekapData['tanggal'] ?? date('Y-m-d'));
?>

<!-- Leaflet CSS & JS untuk Map Picker Interaktif -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="space-y-6">

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 1: DASHBOARD ABSEN & DAFTAR PEGAWAI TERLAMBAT HARI INI
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="absen-panel-dashboard" class="absen-tab-panel space-y-5 <?= $activeTab === 'dashboard' ? '' : 'hidden' ?>">

        <!-- ═════════════════════════════════════════════════════════════════════
             HEADER DASHBOARD PRESENSI PEGAWAI
             ═════════════════════════════════════════════════════════════════════ -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-600 animate-pulse"></span>
                        Live Monitoring Presensi
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= !empty($effectiveUserUnit) ? 'bg-sky-50 text-sky-800 border border-sky-200' : 'bg-slate-100 text-slate-700 border border-slate-200' ?>">
                        Unit: <strong><?= !empty($effectiveUserUnit) ? e($effectiveUserUnit) : 'Semua Unit (Super Admin)' ?></strong>
                    </span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                    Dashboard Presensi Pegawai
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Daftar pegawai yang terlambat hari ini dan rekapitulasi persentase kehadiran pegawai <?= !empty($effectiveUserUnit) ? 'unit <strong>' . e($effectiveUserUnit) . '</strong>' : 'seluruh unit' ?>.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <div class="bg-slate-50 px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs flex items-center gap-2">
                    <span class="text-slate-500 font-medium">Batas Jam Masuk Hari Ini:</span>
                    <strong class="text-rose-600 font-mono text-sm"><?= substr($dashboardData['global_jam']['jam_masuk_batas'] ?? $jamMasukBatasHariIni, 0, 5) ?> WITA</strong>
                </div>
            </div>
        </div>


        <!-- Toolbar Filter Tanggal & Unit Dashboard -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form method="GET" action="<?= url('kelola-absen-pegawai') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="dashboard">

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700">Tanggal:</span>
                    <input type="date" name="tanggal" value="<?= e($targetDate) ?>" onchange="this.form.submit()"
                           class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="document.querySelector('#absen-panel-dashboard input[name=tanggal]').value='<?= date('Y-m-d') ?>'; this.closest('form').submit();"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold <?= $targetDate === date('Y-m-d') ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                        Hari Ini
                    </button>
                    <button type="button" onclick="document.querySelector('#absen-panel-dashboard input[name=tanggal]').value='<?= date('Y-m-d', strtotime('-1 day')) ?>'; this.closest('form').submit();"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold <?= $targetDate === date('Y-m-d', strtotime('-1 day')) ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                        Kemarin
                    </button>
                </div>

                <?php if (!empty($effectiveUserUnit)): ?>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 text-xs font-bold">
                        <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                        <span>Unit Tugas Anda: <strong><?= e($effectiveUserUnit) ?></strong></span>
                    </div>
                <?php else: ?>
                    <select name="unit_tugas" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-primary-500">
                        <option value="">Semua Unit Tugas</option>
                        <?php foreach ($unitLokasiList as $ul): ?>
                            <option value="<?= e($ul['unit_tugas']) ?>" <?= ($filters['unit_tugas'] ?? '') === $ul['unit_tugas'] ? 'selected' : '' ?>>
                                <?= e($ul['unit_tugas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs cursor-pointer shadow-xs">
                    Refresh Dashboard
                </button>
            </form>

            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs font-semibold text-slate-500">
                    Batas Jam Masuk: <strong class="text-rose-600 font-mono"><?= substr($dashboardData['global_jam']['jam_masuk_batas'] ?? $jamMasukBatasHariIni, 0, 5) ?> WITA</strong>
                </span>
            </div>
        </div>

        <?php if (!empty($dashboardData['libur_info'])): ?>
            <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="text-xl shrink-0">🏖️</span>
                    <div>
                        <h4 class="text-xs sm:text-sm font-extrabold text-sky-950">
                            Hari Libur: <?= e($dashboardData['libur_info']['nama_libur']) ?>
                        </h4>
                        <p class="text-xs text-sky-800 mt-0.5">
                            Tanggal <strong><?= date('d M Y', strtotime($targetDate)) ?></strong> tidak dihitung sebagai hari kerja efektif, tidak memicu alpa absensi, dan otomatis sinkron ke Jurnal Staff &amp; Pengingat WA.
                        </p>
                    </div>
                </div>
                <button type="button" onclick="switchAbsenTab('libur')" class="px-3.5 py-2 rounded-xl bg-white hover:bg-sky-100 text-sky-800 border border-sky-200 text-xs font-bold shrink-0 cursor-pointer self-start sm:self-auto">
                    Kelola Hari Libur &rarr;
                </button>
            </div>
        <?php endif; ?>

        <!-- 6 KPI Cards Dashboard Presensi -->
        <?php $dStats = $dashboardData['stats'] ?? []; ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
            <!-- Total Pegawai -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pegawai</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= number_format($dStats['total_pegawai'] ?? 0) ?></div>
                <p class="text-[11px] text-slate-500 mt-0.5">Pegawai aktif unit</p>
            </div>

            <!-- Hadir Tepat Waktu -->
            <div class="bg-white rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Tepat Waktu</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1"><?= number_format($dStats['hadir_tepat_waktu'] ?? 0) ?></div>
                <p class="text-[11px] text-emerald-700/80 mt-0.5 font-semibold"><?= number_format($dStats['persen_tepat_waktu'] ?? 0, 1) ?>% tepat waktu</p>
            </div>

            <!-- Terlambat -->
            <div class="bg-white rounded-2xl p-4 border <?= ($dStats['terlambat'] ?? 0) > 0 ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/80' ?> shadow-xs">
                <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Terlambat</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1"><?= number_format($dStats['terlambat'] ?? 0) ?></div>
                <p class="text-[11px] text-amber-700/80 mt-0.5 font-semibold"><?= number_format($dStats['persen_terlambat'] ?? 0, 1) ?>% terlambat</p>
            </div>

            <!-- Izin / Sakit / Cuti -->
            <div class="bg-white rounded-2xl p-4 border border-sky-200/80 shadow-xs">
                <span class="text-[10px] font-bold text-sky-600 uppercase tracking-wider">Izin / Sakit / Cuti</span>
                <div class="text-2xl font-extrabold text-sky-600 mt-1"><?= number_format(($dStats['izin'] ?? 0) + ($dStats['sakit'] ?? 0) + ($dStats['cuti'] ?? 0)) ?></div>
                <p class="text-[11px] text-slate-500 mt-0.5"><?= $dStats['izin'] ?? 0 ?> Izin, <?= $dStats['sakit'] ?? 0 ?> Sakit, <?= $dStats['cuti'] ?? 0 ?> Cuti</p>
            </div>

            <!-- Belum Absen -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Belum Absen</span>
                <div class="text-2xl font-extrabold text-slate-600 mt-1"><?= number_format($dStats['belum_absen'] ?? 0) ?></div>
                <p class="text-[11px] text-slate-400 mt-0.5">Belum melakukan presensi</p>
            </div>

            <!-- Persentase Kehadiran -->
            <div class="bg-white rounded-2xl p-4 border border-primary-200/80 shadow-xs bg-gradient-to-br from-white to-primary-50/40">
                <span class="text-[10px] font-bold text-primary-600 uppercase tracking-wider">% Kehadiran</span>
                <div class="text-2xl font-extrabold text-primary-700 mt-1"><?= number_format($dStats['persen_kehadiran'] ?? 0, 1) ?>%</div>
                <div class="w-full bg-slate-200 h-1.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-primary-600 h-full rounded-full transition-all duration-500" style="width: <?= min(100, (float)($dStats['persen_kehadiran'] ?? 0)) ?>%"></div>
                </div>
            </div>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════
             BAGIAN 1: DAFTAR PEGAWAI TERLAMBAT HARI INI
             ═════════════════════════════════════════════════════════════════ -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-amber-50/50 to-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <span>Daftar Pegawai yang Terlambat Hari Ini</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= count($dashboardData['daftar_terlambat'] ?? []) > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                <?= count($dashboardData['daftar_terlambat'] ?? []) ?> Orang
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Presensi melewati batas jam masuk (<?= substr($dashboardData['global_jam']['jam_masuk_batas'] ?? $jamMasukBatasHariIni, 0, 5) ?> WITA) pada <?= date('d F Y', strtotime($targetDate)) ?>
                        </p>
                    </div>
                </div>

                <?php if (count($dashboardData['daftar_terlambat'] ?? []) > 0): ?>
                    <span class="text-xs font-bold text-amber-700 bg-amber-100/80 px-3 py-1.5 rounded-xl border border-amber-200">
                        ⚠️ Perlu Pembinaan / Monitoring Kedisiplinan
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($dashboardData['daftar_terlambat'])): ?>
                <!-- Empty State: Tidak ada yang terlambat -->
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 border border-emerald-100 mx-auto flex items-center justify-center mb-3.5 shadow-sm">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </div>
                    <h4 class="text-base font-bold text-slate-800">Alhamdulillah! Tidak Ada Pegawai yang Terlambat</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                        Seluruh pegawai di unit ini yang sudah hadir melakukan check-in tepat waktu sebelum batas jam masuk.
                    </p>
                </div>
            <?php else: ?>
                <!-- Tabel Daftar Pegawai Terlambat -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Pegawai</th>
                                <th class="py-3 px-4">NIY</th>
                                <th class="py-3 px-4">Unit Tugas & Jabatan</th>
                                <th class="py-3 px-4 text-center">Jam Masuk</th>
                                <th class="py-3 px-4 text-center">Batas Jam</th>
                                <th class="py-3 px-4 text-center">Keterlambatan</th>
                                <th class="py-3 px-4">Lokasi Absen</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4">Catatan (Evaluasi & Tindak Lanjut)</th>
                                <th class="py-3 px-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php 
                            $noL = 1;
                            foreach ($dashboardData['daftar_terlambat'] as $late): 
                            ?>
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="py-3.5 px-4 text-center text-slate-400 font-bold"><?= $noL++ ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($late['foto'])): ?>
                                                <img src="<?= url(ltrim($late['foto'], '/')) ?>" alt="Foto" class="w-9 h-9 rounded-xl object-cover border border-slate-200 shrink-0">
                                            <?php else: ?>
                                                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white font-extrabold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                                    <?= strtoupper(substr($late['nama_polos'], 0, 2)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-bold text-slate-900 text-xs"><?= e($late['nama']) ?></div>
                                                <div class="text-[10px] text-slate-500"><?= e($late['jabatan']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600"><?= e($late['niy']) ?></td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                            <?= e($late['unit_tugas']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="font-mono font-bold text-amber-700 bg-amber-50 px-2 py-1 rounded-lg border border-amber-200">
                                            <?= substr($late['jam_masuk'], 0, 5) ?> WITA
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono text-slate-400 text-xs">
                                        <?= substr($late['jam_masuk_batas'], 0, 5) ?> WITA
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span>+<?= $late['menit_terlambat'] ?> Menit</span>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="text-xs font-semibold text-slate-800 truncate max-w-xs" title="<?= e($late['nama_kantor_absen']) ?>">
                                            📍 <?= e($late['nama_kantor_absen']) ?>
                                        </div>
                                        <?php if ($late['jarak_masuk_meter'] > 0): ?>
                                            <div class="text-[10px] text-slate-400">Jarak: <?= round($late['jarak_masuk_meter']) ?> m</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                            Terlambat
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if (!empty($late['evaluasi']) || !empty($late['tindak_lanjut'])): ?>
                                            <div class="space-y-1.5 min-w-[200px] max-w-xs">
                                                <?php if (!empty($late['evaluasi'])): ?>
                                                    <div class="bg-amber-50/70 rounded-xl p-2 border border-amber-200/70 text-slate-800 text-[11px] leading-relaxed">
                                                        <div class="flex items-center gap-1 font-extrabold text-amber-800 text-[10px] uppercase tracking-wide">
                                                            <span>📝 Evaluasi:</span>
                                                        </div>
                                                        <div class="mt-0.5 text-slate-800 font-medium" title="<?= e($late['evaluasi']) ?>">
                                                            <?= e($late['evaluasi']) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($late['tindak_lanjut'])): ?>
                                                    <div class="bg-blue-50/70 rounded-xl p-2 border border-blue-200/70 text-slate-800 text-[11px] leading-relaxed">
                                                        <div class="flex items-center gap-1 font-extrabold text-primary-700 text-[10px] uppercase tracking-wide">
                                                            <span>🎯 Tindak Lanjut:</span>
                                                        </div>
                                                        <div class="mt-0.5 text-slate-800 font-medium" title="<?= e($late['tindak_lanjut']) ?>">
                                                            <?= e($late['tindak_lanjut']) ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($late['evaluasi_oleh'])): ?>
                                                    <div class="text-[10px] text-slate-400 flex items-center gap-1 pt-0.5">
                                                        <span>Oleh: <?= e($late['evaluasi_oleh']) ?></span>
                                                        <?php if (!empty($late['evaluasi_at'])): ?>
                                                            <span>• <?= date('d/m H:i', strtotime($late['evaluasi_at'])) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-50 text-slate-400 border border-dashed border-slate-200">
                                                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                                </svg>
                                                Belum ada evaluasi
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" 
                                                    onclick="openModalCatatanTerlambat(<?= htmlspecialchars(json_encode([
                                                        'pegawai_id'        => $late['id'],
                                                        'nama'              => $late['nama'],
                                                        'niy'               => $late['niy'] ?? '',
                                                        'unit_tugas'        => $late['unit_tugas'] ?? '',
                                                        'jabatan'           => $late['jabatan'] ?? '',
                                                        'tanggal'           => $targetDate,
                                                        'tanggal_formatted' => date('d F Y', strtotime($targetDate)),
                                                        'jam_masuk'         => $late['jam_masuk'] ?? '',
                                                        'batas_jam'         => substr($late['jam_masuk_batas'], 0, 5),
                                                        'menit_terlambat'   => $late['menit_terlambat'] ?? 0,
                                                        'evaluasi'          => $late['evaluasi'] ?? '',
                                                        'tindak_lanjut'     => $late['tindak_lanjut'] ?? '',
                                                        'evaluasi_oleh'     => $late['evaluasi_oleh'] ?? '',
                                                        'evaluasi_at'       => $late['evaluasi_at'] ?? '',
                                                        'return_tab'        => 'dashboard',
                                                    ]), ENT_QUOTES, 'UTF-8') ?>)"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold <?= (!empty($late['evaluasi']) || !empty($late['tindak_lanjut'])) ? 'bg-amber-50 text-amber-800 hover:bg-amber-600 hover:text-white border border-amber-300' : 'bg-primary-600 text-white hover:bg-primary-700 shadow-xs' ?> transition-all cursor-pointer"
                                                    title="<?= (!empty($late['evaluasi']) || !empty($late['tindak_lanjut'])) ? 'Edit Catatan (Evaluasi & Tindak Lanjut)' : 'Tambah Catatan (Evaluasi & Tindak Lanjut)' ?>">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                <span><?= (!empty($late['evaluasi']) || !empty($late['tindak_lanjut'])) ? 'Catatan 📝' : 'Catatan' ?></span>
                                            </button>

                                            <button type="button" 
                                                    onclick="openModalEditLogPresensi(<?= htmlspecialchars(json_encode([
                                                        'pegawai_id' => $late['id'],
                                                        'nama' => $late['nama'],
                                                        'niy' => $late['niy'] ?? '',
                                                        'unit_tugas' => $late['unit_tugas'] ?? '',
                                                        'jabatan' => $late['jabatan'] ?? '',
                                                        'tanggal' => $targetDate,
                                                        'tanggal_formatted' => date('d F Y', strtotime($targetDate)),
                                                        'jam_masuk' => $late['jam_masuk'] ?? '',
                                                        'jam_pulang' => $late['jam_pulang'] ?? '',
                                                        'status' => 'Terlambat',
                                                        'keterangan' => $late['keterangan'] ?? '',
                                                        'nama_kantor' => $late['nama_kantor_absen'] ?? '',
                                                        'batas_jam' => substr($late['jam_masuk_batas'], 0, 5),
                                                        'has_log' => true,
                                                        'return_tab' => 'dashboard',
                                                        'rekap_view' => 'harian',
                                                    ]), ENT_QUOTES, 'UTF-8') ?>)"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-all cursor-pointer shadow-xs"
                                                    title="Ubah Log Presensi">
                                                <span>Ubah</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════
             BAGIAN 2: REKAPITULASI PERSENTASE KEHADIRAN PEGAWAI PER UNIT
             ═════════════════════════════════════════════════════════════════ -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between gap-3 bg-gradient-to-r from-primary-50/50 to-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-primary-100 text-primary-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Rekapitulasi Persentase Kehadiran Pegawai</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Persentase tingkat kehadiran per unit tugas dan breakdown status presensi</p>
                    </div>
                </div>

                <div class="text-right">
                    <span class="text-xs font-bold text-primary-700 bg-primary-50 px-3 py-1.5 rounded-xl border border-primary-200">
                        Rata-rata: <strong><?= number_format($dStats['persen_kehadiran'] ?? 0, 1) ?>%</strong>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Unit Tugas</th>
                            <th class="py-3 px-4 text-center">Total Pegawai</th>
                            <th class="py-3 px-4 text-center">Hadir Total</th>
                            <th class="py-3 px-4 text-center">Tepat Waktu</th>
                            <th class="py-3 px-4 text-center">Terlambat</th>
                            <th class="py-3 px-4 text-center">Izin / Sakit / Cuti</th>
                            <th class="py-3 px-4 text-center">Belum Absen</th>
                            <th class="py-3 px-4 text-center w-48">Persentase Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php 
                        $breakdowns = $dashboardData['unit_breakdown'] ?? [];
                        if (empty($breakdowns)): 
                        ?>
                            <tr>
                                <td colspan="8" class="py-6 text-center text-slate-400">Tidak ada data unit tugas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($breakdowns as $ub): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-primary-500"></span>
                                            <span><?= e($ub['unit_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-900"><?= $ub['total_pegawai'] ?></td>
                                    <td class="py-3.5 px-4 text-center font-bold text-primary-700"><?= $ub['hadir_total'] ?></td>
                                    <td class="py-3.5 px-4 text-center font-bold text-emerald-600"><?= $ub['hadir_tepat_waktu'] ?></td>
                                    <td class="py-3.5 px-4 text-center font-bold text-amber-600"><?= $ub['terlambat'] ?></td>
                                    <td class="py-3.5 px-4 text-center font-bold text-sky-600"><?= ($ub['izin'] ?? 0) + ($ub['sakit'] ?? 0) + ($ub['cuti'] ?? 0) ?></td>
                                    <td class="py-3.5 px-4 text-center text-slate-400"><?= $ub['belum_absen'] ?></td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center gap-2.5 justify-center">
                                            <div class="w-24 bg-slate-100 h-2 rounded-full overflow-hidden shrink-0">
                                                <div class="bg-gradient-to-r from-primary-500 to-emerald-500 h-full rounded-full" style="width: <?= min(100, (float)$ub['persen_kehadiran']) ?>%"></div>
                                            </div>
                                            <span class="font-extrabold text-xs text-slate-900"><?= number_format($ub['persen_kehadiran'], 1) ?>%</span>
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
         TAB 2: LOKASI ABSEN MASING-MASING PEGAWAI (CUSTOM PER PEGAWAI & RESET)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="absen-panel-pegawai" class="absen-tab-panel space-y-5 <?= $activeTab === 'pegawai' ? '' : 'hidden' ?>">

        <!-- ═════════════════════════════════════════════════════════════════════
             TOP HERO BANNER & STATISTIK LOKASI PEGAWAI
             ═════════════════════════════════════════════════════════════════════ -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-950 via-primary-900 to-sky-950 p-6 sm:p-8 text-white shadow-xl border border-primary-800/80">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-sky-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/4 -top-10 w-60 h-60 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-semibold mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>Radius Global Aktif: <strong><?= $radiusGlobal ?> Meter</strong> (Berlaku untuk Seluruh Pegawai & Unit)</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">
                        Kelola Lokasi Absen Pegawai
                    </h2>
                    <p class="text-primary-200/90 text-sm leading-relaxed">
                        Atur titik koordinat lokasi kantor absen (<strong>Nama Kantor</strong>, <strong>Latitude</strong>, dan <strong>Longitude</strong>) secara <strong>Custom Per Pegawai</strong>, <strong>Custom Per Unit</strong>, atau <strong>Reset ke Lokasi Semula</strong>.
                    </p>
                    <div class="flex flex-wrap items-center gap-2.5 mt-4 text-xs text-primary-200">
                        <span class="inline-flex items-center gap-1.5 bg-primary-900/70 px-3 py-1.5 rounded-xl border border-primary-700/60">
                            📍 Lokasi Semula: <strong class="text-white"><?= e($defaultKantor) ?></strong>
                        </span>
                        <span class="inline-flex items-center gap-1.5 bg-primary-900/70 px-3 py-1.5 rounded-xl border border-primary-700/60 font-mono">
                            Lat: <strong class="text-sky-300"><?= number_format($defaultLat, 6, '.', '') ?></strong> | Lng: <strong class="text-sky-300"><?= number_format($defaultLng, 6, '.', '') ?></strong>
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <button type="button" onclick="openModalCustomPegawai()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                        <span>+ Custom Lokasi Pegawai</span>
                    </button>

                    <button type="button" onclick="openModalCustomUnit()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 backdrop-blur-sm transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21"/></svg>
                        <span>+ Custom Lokasi Unit</span>
                    </button>

                    <a href="<?= url('kelola-absen-pegawai/export') ?>"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs border border-white/20 backdrop-blur-sm transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        <span>Export CSV</span>
                    </a>

                    <form method="POST" action="<?= url('kelola-absen-pegawai/reset-semua') ?>" onsubmit="return confirm('Reset SELURUH pegawai dan unit kembali ke Lokasi Absen Semula (<?= e(addslashes($defaultKantor)) ?>)?');">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="return_tab" value="pegawai">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-rose-500/20 hover:bg-rose-500/30 text-rose-200 hover:text-white font-semibold text-xs border border-rose-400/30 backdrop-blur-sm transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            <span>Reset Semua ke Semula</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 4 KPI CARDS RINGKASAN LOKASI ABSEN -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pegawai Aktif</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['totalPegawai']) ?></div>
                <p class="text-[11px] text-slate-500 mt-0.5">Terdaftar dalam sistem absen</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-sky-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Lokasi Semula (Default)</span>
                <div class="text-2xl font-extrabold text-sky-600 mt-1"><?= number_format($stats['totalDefault']) ?> <span class="text-xs font-normal text-slate-400">Pegawai</span></div>
                <p class="text-[11px] text-slate-500 mt-0.5 truncate" title="<?= e($defaultKantor) ?>"><?= e($defaultKantor) ?></p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-purple-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-purple-600 uppercase tracking-wider">Custom Lokasi Per Unit</span>
                <div class="text-2xl font-extrabold text-purple-600 mt-1"><?= number_format($stats['totalCustomUnit']) ?> <span class="text-xs font-normal text-slate-400">Pegawai</span></div>
                <p class="text-[11px] text-slate-500 mt-0.5"><?= (int)$stats['unitCustomCount'] ?> dari <?= (int)$stats['totalUnit'] ?> unit diatur khusus</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Custom Per Pegawai</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['totalCustomPegawai']) ?> <span class="text-xs font-normal text-slate-400">Pegawai</span></div>
                <p class="text-[11px] text-slate-500 mt-0.5">Titik lokasi khusus individu</p>
            </div>
        </div>


        <!-- Filter & Bulk Action Toolbar -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form method="GET" action="<?= url('kelola-absen-pegawai') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="pegawai">
                <div class="relative flex-1 min-w-[220px] max-w-xs">
                    <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Cari nama pegawai, NIY, nama kantor..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>

                <select name="unit_tugas" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Unit Tugas</option>
                    <?php foreach ($unitLokasiList as $ul): ?>
                        <option value="<?= e($ul['unit_tugas']) ?>" <?= $filters['unit_tugas'] === $ul['unit_tugas'] ? 'selected' : '' ?>>
                            <?= e($ul['unit_tugas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="mode_lokasi" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Status Lokasi</option>
                    <option value="default" <?= $filters['mode_lokasi'] === 'default' ? 'selected' : '' ?>>Lokasi Semula (Default)</option>
                    <option value="unit" <?= $filters['mode_lokasi'] === 'unit' ? 'selected' : '' ?>>Custom Unit</option>
                    <option value="custom" <?= $filters['mode_lokasi'] === 'custom' ? 'selected' : '' ?>>Custom Pegawai</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs cursor-pointer">
                    Filter
                </button>
                <?php if ($filters['search'] || $filters['unit_tugas'] || $filters['mode_lokasi']): ?>
                    <a href="<?= url('kelola-absen-pegawai?tab=pegawai') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl text-xs">
                        Reset Filter
                    </a>
                <?php endif; ?>
            </form>

            <!-- Bulk Action Buttons (Saat Checkbox Pegawai Dipilih) -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button type="button" onclick="openBulkCustomPegawai()"
                    class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs cursor-pointer flex items-center gap-1.5" title="Atur lokasi kantor khusus untuk pegawai yang dicentang">
                    <span>📍 Atur Lokasi (<span id="selected_pegawai_count">0</span>)</span>
                </button>

                <button type="button" onclick="openBulkCustomJamKerja()"
                    class="px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs cursor-pointer flex items-center gap-1.5" title="Atur jam kedatangan & pulang untuk pegawai yang dicentang">
                    <span>⏰ Atur Jam Kerja (<span id="selected_pegawai_count_jam">0</span>)</span>
                </button>

                <form id="formBulkResetPegawai" method="POST" action="<?= url('kelola-absen-pegawai/pegawai/reset') ?>" onsubmit="return submitBulkResetPegawai();">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="return_tab" value="pegawai">
                    <input type="hidden" name="pegawai_ids_csv" id="bulk_reset_ids_csv" value="">
                    <button type="submit" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs cursor-pointer" title="Kembalikan lokasi terpilih ke lokasi semula">
                        ↺ Reset Lokasi
                    </button>
                </form>

                <form id="formBulkResetJam" method="POST" action="<?= url('kelola-absen-pegawai/jam-kerja/reset') ?>" onsubmit="return submitBulkResetJam();">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="return_tab" value="pegawai">
                    <input type="hidden" name="pegawai_ids_csv" id="bulk_reset_jam_ids_csv" value="">
                    <button type="submit" class="px-3 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold text-xs cursor-pointer" title="Kembalikan jam kerja terpilih mengikuti global">
                        ↺ Reset Jam Global
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabel Seluruh Pegawai & Lokasi Absennya -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Daftar Lokasi & Jam Kerja Seluruh Pegawai</h3>
                    <p class="text-xs text-slate-500">Menampilkan <strong><?= count($pegawaiList) ?></strong> pegawai &bull; Bisa diatur per pegawai maupun untuk semua</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-amber-800 bg-amber-50 px-3 py-1 rounded-xl border border-amber-200">
                        ⏰ Jam Global <?= !empty($globalJamHariIni['is_sabtu_khusus']) ? '(Sabtu)' : '' ?>: <?= $jamMasukBatasHariIni ?> - <?= $jamPulangMulaiHariIni ?> WITA
                        <?php if ($sabtuKhususAktif && empty($globalJamHariIni['is_sabtu_khusus'])): ?>
                            <span class="text-amber-600 font-semibold">(Sabtu: <?= $sabtuJamMasukBatas ?>)</span>
                        <?php endif; ?>
                    </span>
                    <span class="text-xs font-extrabold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-200">
                        Radius: <?= $radiusGlobal ?>m
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-3 text-center w-10">
                                <input type="checkbox" id="checkAllPegawai" onchange="toggleSelectAllPegawai(this)" class="rounded text-primary-600 cursor-pointer">
                            </th>
                            <th class="py-3.5 px-3 text-center w-10">No</th>
                            <th class="py-3.5 px-4">Nama Pegawai & NIY</th>
                            <th class="py-3.5 px-4">Unit Tugas</th>
                            <th class="py-3.5 px-4 text-center">Status Lokasi</th>
                            <th class="py-3.5 px-4">Nama Kantor Tempat Absen</th>
                            <th class="py-3.5 px-4">Latitude & Longitude</th>
                            <th class="py-3.5 px-4 text-center">Jam Kerja (Batas & Pulang)</th>
                            <th class="py-3.5 px-4 text-center">Kedatangan Hari Ini</th>
                            <th class="py-3.5 px-4 text-center">Aksi Pegawai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($pegawaiList)): ?>
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    Tidak ada data pegawai yang ditemukan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pegawaiList as $idx => $p):
                                $namaLengkap = $p['nama'] . (!empty($p['gelar']) ? ', ' . $p['gelar'] : '');
                                $mode = $p['mode_efektif'];
                                $modeBadge = match($mode) {
                                    'custom' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'unit'   => 'bg-purple-100 text-purple-800 border-purple-200',
                                    default  => 'bg-sky-50 text-sky-700 border-sky-200'
                                };
                                $latStr = number_format((float)$p['latitude_efektif'], 6, '.', '');
                                $lngStr = number_format((float)$p['longitude_efektif'], 6, '.', '');
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3.5 px-3 text-center">
                                        <input type="checkbox" value="<?= (int)$p['id'] ?>" onchange="updateSelectedPegawaiCount()" class="pegawai-row-check rounded text-primary-600 cursor-pointer">
                                    </td>
                                    <td class="py-3.5 px-3 text-center text-slate-400 font-semibold"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($namaLengkap) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">NIY: <?= e($p['niy'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-700"><?= e($p['unit_tugas'] ?: 'Umum / Yayasan') ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($p['jabatan'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold border <?= $modeBadge ?>">
                                            <?= e($p['status_lokasi_label']) ?>
                                        </span>
                                        <?php if (!empty($p['jadwal_custom_label'])): ?>
                                            <div class="mt-1">
                                                <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-semibold <?= ($p['jadwal_custom_status'] ?? '') === 'aktif_sekarang' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600' ?>">
                                                    🗓️ <?= e($p['jadwal_custom_label']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                            <span class="text-rose-500">🏢</span>
                                            <span><?= e($p['nama_kantor_efektif']) ?></span>
                                        </div>
                                        <?php if ($p['peg_mode'] === 'custom' && $mode !== 'custom' && !empty($p['peg_kantor'])): ?>
                                            <div class="text-[10px] text-amber-700 font-semibold mt-0.5">
                                                ⏳ Lokasi Jadwal: <?= e($p['peg_kantor']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($p['peg_ket'])): ?>
                                            <div class="text-[10px] text-slate-400 mt-0.5"><?= e($p['peg_ket']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono">
                                        <div class="text-slate-800 font-bold"><?= $latStr ?>, <?= $lngStr ?></div>
                                        <a href="https://www.google.com/maps?q=<?= $latStr ?>,<?= $lngStr ?>" target="_blank"
                                           class="inline-flex items-center gap-1 text-[10px] font-sans font-bold text-sky-600 hover:underline mt-0.5">
                                            🗺️ Lihat di Maps &rarr;
                                        </a>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php
                                        $jamLabelTampil = $p['jam_kerja_label'] ?? (!empty($p['is_custom_jam_kerja']) ? 'Khusus' : 'Global');
                                        $jamHarianRow = $p['jam_harian'] ?? [];
                                        $dayAbbrMap = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
                                        ?>
                                        <?php if (!empty($p['is_custom_jam_kerja'])): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <?= e($jamLabelTampil) ?>
                                            </span>
                                            <div class="font-mono font-bold text-xs text-slate-800 mt-0.5">
                                                <?= substr($p['jam_masuk_batas_efektif'] ?? $jamMasukBatas, 0, 5) ?> - <?= substr($p['jam_pulang_mulai_efektif'] ?? $jamPulangMulai, 0, 5) ?>
                                            </div>
                                            <div class="text-[10px] text-amber-700">Mulai: <?= substr($p['jam_masuk_mulai_efektif'] ?? $jamMasukMulai, 0, 5) ?></div>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                <?= e($jamLabelTampil) ?>
                                            </span>
                                            <div class="font-mono text-xs text-slate-700 font-bold mt-0.5">
                                                <?= substr($p['jam_masuk_batas_efektif'] ?? $jamMasukBatas, 0, 5) ?> - <?= substr($p['jam_pulang_mulai_efektif'] ?? $jamPulangMulai, 0, 5) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($jamHarianRow)): ?>
                                            <div class="mt-1 flex flex-wrap items-center justify-center gap-1 max-w-[190px] mx-auto">
                                                <?php foreach ($jamHarianRow as $hkNum => $hVal): ?>
                                                    <span class="px-1.5 py-0.5 rounded bg-amber-50 border border-amber-200 text-[9px] font-mono font-bold text-amber-900" title="Batas <?= e($dayAbbrMap[(int)$hkNum] ?? $hkNum) ?>: <?= e($hVal['jam_masuk_batas']) ?>">
                                                        <?= e($dayAbbrMap[(int)$hkNum] ?? $hkNum) ?> <?= e($hVal['jam_masuk_batas']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if (!empty($p['today_jam_masuk'])): ?>
                                            <div class="inline-flex flex-col items-center">
                                                <span class="font-extrabold text-slate-800 font-mono text-xs">
                                                    <?= substr($p['today_jam_masuk'], 0, 5) ?> WITA
                                                </span>
                                                <?php if ($p['today_status'] === 'Terlambat'): ?>
                                                    <span class="mt-0.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" title="<?= e($p['today_keterangan']) ?>">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                        <?= e($p['today_keterangan'] ?: 'Terlambat') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="mt-0.5 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                        Tepat Waktu
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($p['today_jam_pulang'])): ?>
                                                    <span class="text-[10px] text-slate-500 font-mono mt-0.5">
                                                        Pulang: <?= substr($p['today_jam_pulang'], 0, 5) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                                Belum Check-in
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                            <button type="button"
                                                onclick="openModalCustomPegawai(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($namaLengkap), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($p['peg_kantor'] ?: $p['nama_kantor_efektif']), ENT_QUOTES, 'UTF-8') ?>, '<?= $p['peg_lat'] ?: $latStr ?>', '<?= $p['peg_lng'] ?: $lngStr ?>', <?= htmlspecialchars(json_encode($p['peg_tgl_mulai'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($p['peg_tgl_selesai'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($p['peg_hari_aktif'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($p['peg_ket'] ?? ''), ENT_QUOTES, 'UTF-8') ?>, '<?= !empty($p['is_custom_jam_kerja']) ? 'custom' : 'global' ?>', '<?= substr($p['peg_jam_masuk_mulai'] ?? '', 0, 5) ?>', '<?= substr($p['peg_jam_masuk_batas'] ?? '', 0, 5) ?>', '<?= substr($p['peg_jam_pulang_mulai'] ?? '', 0, 5) ?>')"
                                                class="px-2.5 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-bold text-[11px] transition-colors cursor-pointer" title="Atur Lokasi Absen">
                                                📍 Lokasi
                                            </button>

                                            <?php
                                            $jamModeBtn = !empty($p['jam_harian']) ? 'per_hari' : (!empty($p['is_custom_jam_kerja']) ? 'custom' : 'global');
                                            ?>
                                            <button type="button"
                                                onclick="openModalCustomJamKerja(<?= (int)$p['id'] ?>, <?= htmlspecialchars(json_encode($namaLengkap), ENT_QUOTES, 'UTF-8') ?>, '<?= $jamModeBtn ?>', '<?= substr($p['jam_masuk_mulai_efektif'] ?? $jamMasukMulai, 0, 5) ?>', '<?= substr($p['jam_masuk_batas_efektif'] ?? $jamMasukBatas, 0, 5) ?>', '<?= substr($p['jam_pulang_mulai_efektif'] ?? $jamPulangMulai, 0, 5) ?>')"
                                                class="px-2.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] transition-colors cursor-pointer" title="Atur Jam Kedatangan & Pulang">
                                                ⏰ Jam
                                            </button>

                                            <?php if ($mode !== 'default' || ($p['peg_mode'] ?? '') === 'custom'): ?>
                                                <form method="POST" action="<?= url('kelola-absen-pegawai/pegawai/reset') ?>" onsubmit="return confirm('Reset lokasi absen <?= e(addslashes($namaLengkap)) ?> kembali ke lokasi semula?');" class="inline">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="pegawai_id" value="<?= (int)$p['id'] ?>">
                                                    <input type="hidden" name="return_tab" value="pegawai">
                                                    <button type="submit" class="px-2 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-[10px] transition-colors cursor-pointer" title="Reset lokasi ke semula">
                                                        ↺ Lokasi
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (!empty($p['is_custom_jam_kerja'])): ?>
                                                <form method="POST" action="<?= url('kelola-absen-pegawai/jam-kerja/reset') ?>" onsubmit="return confirm('Kembalikan jam kerja <?= e(addslashes($namaLengkap)) ?> mengikuti pengaturan global (semua pegawai)?');" class="inline">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="pegawai_id" value="<?= (int)$p['id'] ?>">
                                                    <input type="hidden" name="return_tab" value="pegawai">
                                                    <button type="submit" class="px-2 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold text-[10px] transition-colors cursor-pointer" title="Kembalikan jam kerja ke global">
                                                        ↺ Jam
                                                    </button>
                                                </form>
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
         TAB 3: DATA ABSEN & REKAP (REKAPITULASI PERIODE CETAK PDF & LOG HARIAN)
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="absen-panel-rekap" class="absen-tab-panel space-y-4 <?= $activeTab === 'rekap' ? '' : 'hidden' ?>">

        <!-- Sub-Navigation Toggle: Rekapitulasi Periode vs Log Harian -->
        <?php $currentRekapView = $filters['rekap_view'] ?? 'periode'; ?>
        <div class="bg-white p-2 rounded-2xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="switchRekapSubView('periode')" id="btn-rekap-sub-periode"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer <?= $currentRekapView === 'periode' ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span>Rekapitulasi Periode & Cetak PDF</span>
                </button>

                <button type="button" onclick="switchRekapSubView('harian')" id="btn-rekap-sub-harian"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer <?= $currentRekapView === 'harian' ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Log Presensi Harian</span>
                </button>

                <button type="button" onclick="switchRekapSubView('matrix')" id="btn-rekap-sub-matrix"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer <?= $currentRekapView === 'matrix' ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25" />
                    </svg>
                    <span>Matriks Kehadiran</span>
                </button>
            </div>

            <!-- Tombol Absen Manual & Cetak PDF Cepat -->
            <div class="flex flex-wrap items-center gap-2">
                <?php if (!(class_exists('Auth') && Auth::isPersonalScope()) && RBAC::hasAnyPermission(['absen_pegawai.create', 'absen_pegawai.update'])): ?>
                <button type="button" onclick="openModalAbsenManual()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>+ Absen Manual</span>
                </button>
                <?php endif; ?>
                <?php
                $cetakPdfUrl = url('kelola-absen-pegawai/cetak-pdf?' . http_build_query([
                    'start_date' => $filters['start_date'] ?? date('Y-m-01'),
                    'end_date'   => $filters['end_date'] ?? date('Y-m-d'),
                    'unit_tugas' => $filters['unit_tugas'] ?? '',
                    'search'     => $filters['search'] ?? ''
                ]));
                ?>
                <a href="<?= $cetakPdfUrl ?>" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.144-1.28-3.093l-.408-.637c-.368-.574-.532-.83-.532-1.127 0-.482.352-.892.83-.974.776-.133 1.57-.213 2.378-.238m10.584 6.069c.24-1.076.672-2.144 1.28-3.093l.408-.637c.368-.574.532-.83.532-1.127 0-.482-.352-.892-.83-.974a24.28 24.28 0 0 0-2.378-.238M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v9.75a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25" />
                    </svg>
                    <span>Cetak PDF Rekap</span>
                </a>
            </div>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════
             SUB-VIEW A: REKAPITULASI PERIODE & CETAK PDF
             ═════════════════════════════════════════════════════════════════ -->
        <div id="subview-rekap-periode" class="space-y-4 <?= $currentRekapView === 'periode' ? '' : 'hidden' ?>">

            <!-- Toolbar Filter Rekapitulasi Periode -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                <form method="GET" action="<?= url('kelola-absen-pegawai') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                    <input type="hidden" name="tab" value="rekap">
                    <input type="hidden" name="rekap_view" value="periode">

                    <!-- Periode Mulai & Selesai -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-slate-700">Periode:</span>
                        <input type="date" name="start_date" id="rekap_start_date" value="<?= e($filters['start_date']) ?>"
                               class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                        <span class="text-xs text-slate-400 font-bold">s/d</span>
                        <input type="date" name="end_date" id="rekap_end_date" value="<?= e($filters['end_date']) ?>"
                               class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                    </div>

                    <!-- Shortcut Periode -->
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="setPeriodeShortcut('bulan_ini')"
                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                            Bulan Ini
                        </button>
                        <button type="button" onclick="setPeriodeShortcut('bulan_lalu')"
                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                            Bulan Lalu
                        </button>
                    </div>

                    <!-- Cari Pegawai / NIY -->
                    <div class="relative min-w-[180px]">
                        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Cari nama / NIY..."
                               class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </div>

                    <!-- Filter Unit (Jika Bukan Restricted) -->
                    <?php if (!empty($effectiveUserUnit)): ?>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                            <span>Unit: <strong><?= e($effectiveUserUnit) ?></strong></span>
                        </div>
                    <?php else: ?>
                        <select name="unit_tugas" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 outline-none focus:border-primary-500">
                            <option value="">Semua Unit Tugas</option>
                            <?php foreach ($unitLokasiList as $ul): ?>
                                <option value="<?= e($ul['unit_tugas']) ?>" <?= ($filters['unit_tugas'] ?? '') === $ul['unit_tugas'] ? 'selected' : '' ?>>
                                    <?= e($ul['unit_tugas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs cursor-pointer shadow-xs">
                        Filter Rekap
                    </button>
                </form>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="openModalUrutanUnit('periode')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                        </svg>
                        <span>Atur Urutan Unit</span>
                    </button>
                    <a href="<?= $cetakPdfUrl ?>" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary-50 hover:bg-primary-100 text-primary-700 border border-primary-200 text-xs font-bold transition-all">
                        <svg class="w-4 h-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.144-1.28-3.093l-.408-.637c-.368-.574-.532-.83-.532-1.127 0-.482.352-.892.83-.974.776-.133 1.57-.213 2.378-.238m10.584 6.069c.24-1.076.672-2.144 1.28-3.093l.408-.637c.368-.574.532-.83.532-1.127 0-.482-.352-.892-.83-.974a24.28 24.28 0 0 0-2.378-.238M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v9.75a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25" />
                        </svg>
                        <span>Pratinjau Cetak</span>
                    </a>
                </div>
            </div>

            <!-- Summary Bar Periode -->
            <?php $pSum = $rekapPeriodeData['summary'] ?? []; ?>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pegawai</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= number_format($pSum['total_pegawai'] ?? count($rekapPeriodeData['rows'] ?? [])) ?></div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pegawai dalam rekap</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hari Kerja</span>
                    <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= (int)($rekapPeriodeData['jumlah_hari_kerja'] ?? 0) ?> <span class="text-xs font-normal text-slate-500">Hari</span></div>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        Senin - Sabtu
                        <?php if (!empty($rekapPeriodeData['jumlah_libur_khusus'])): ?>
                            <span class="text-rose-600 font-semibold">(dikurangi <?= (int)$rekapPeriodeData['jumlah_libur_khusus'] ?> hari libur)</span>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Hadir Tepat Waktu</span>
                    <div class="text-2xl font-extrabold text-emerald-600 mt-1"><?= number_format($pSum['total_tepat_waktu_semua'] ?? 0) ?></div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Total hari tepat waktu</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-amber-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Terlambat</span>
                    <div class="text-2xl font-extrabold text-amber-600 mt-1"><?= number_format($pSum['total_terlambat_semua'] ?? 0) ?></div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Total presensi terlambat</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-rose-200/80 shadow-xs">
                    <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Alpa (Tanpa Ket.)</span>
                    <div class="text-2xl font-extrabold text-rose-600 mt-1"><?= number_format($pSum['total_alpa_semua'] ?? 0) ?></div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Hari tanpa presensi/izin</p>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-primary-200/80 shadow-xs bg-gradient-to-br from-white to-primary-50/40">
                    <span class="text-[10px] font-bold text-primary-600 uppercase tracking-wider">Rata-rata Hadir</span>
                    <div class="text-2xl font-extrabold text-primary-700 mt-1"><?= number_format($pSum['rata_rata_kehadiran'] ?? 0, 1) ?>%</div>
                    <p class="text-[11px] text-primary-600 font-semibold mt-0.5">Tingkat kehadiran unit</p>
                </div>
            </div>

            <!-- Tabel Rekapitulasi Presensi Pegawai Sesuai Format Resmi PDF -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <span>Tabel Rekapitulasi Presensi Pegawai (Periode)</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-100 text-primary-800">
                                <?= count($rekapPeriodeData['rows'] ?? []) ?> Pegawai
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Data rekapitulasi kehadiran resmi per pegawai siap diekspor dan dicetak PDF.
                        </p>
                    </div>

                    <a href="<?= $cetakPdfUrl ?>" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm transition-all shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.144-1.28-3.093l-.408-.637c-.368-.574-.532-.83-.532-1.127 0-.482.352-.892.83-.974.776-.133 1.57-.213 2.378-.238m10.584 6.069c.24-1.076.672-2.144 1.28-3.093l.408-.637c.368-.574.532-.83.532-1.127 0-.482-.352-.892-.83-.974a24.28 24.28 0 0 0-2.378-.238M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v9.75a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25" />
                        </svg>
                        <span>Cetak Dokumen PDF</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-rose-200/75 border-b border-slate-300 text-[10px] font-bold text-slate-800 uppercase tracking-wider">
                                <th rowspan="2" class="py-2.5 px-2.5 w-10 text-center border-r border-slate-300">No</th>
                                <th rowspan="2" class="py-2.5 px-3 min-w-[190px] border-r border-slate-300">Nama</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300">Kehadiran<br>Lain-lain</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300">Periode 1</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300">Periode 2</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300">Hari<br>Kerja</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300">Hari<br>Masuk</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300 text-amber-800">Total<br>Terlambat</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300 text-amber-800">Terlambat<br>Finger</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300 text-orange-800" title="Berlaku untuk Guru dengan jadwal 06:30 yang hadir >= 06:31 (otomatis Terlambat Finger)">Terlambat<br>Piket</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300 text-emerald-800">Hari Tidak<br>Terlambat</th>
                                <th colspan="6" class="py-1.5 px-2 text-center border-b border-r border-slate-300">Tidak hadir</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300">Presentasi<br>Kehadiran</th>
                                <th rowspan="2" class="py-2.5 px-2 text-center border-r border-slate-300">Presentasi<br>Kedisiplinan</th>
                                <th rowspan="2" class="py-2.5 px-2.5 text-center w-16">Aksi</th>
                            </tr>
                            <tr class="bg-rose-200/75 border-b border-slate-300 text-[10px] font-bold text-slate-800">
                                <th class="py-1.5 px-2 text-center border-r border-slate-300">Izin</th>
                                <th class="py-1.5 px-2 text-center border-r border-slate-300">Izin Khusus</th>
                                <th class="py-1.5 px-2 text-center border-r border-slate-300">Sakit</th>
                                <th class="py-1.5 px-2 text-center border-r border-slate-300">Alpa</th>
                                <th class="py-1.5 px-2 text-center border-r border-slate-300">Cuti</th>
                                <th class="py-1.5 px-2 text-center border-r border-slate-300">Tugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 font-medium">
                            <?php 
                            $rRows = $rekapPeriodeData['rows'] ?? [];
                            $rGroups = $rekapPeriodeData['groups'] ?? [
                                'guru' => array_values(array_filter($rRows, fn($x) => ($x['kategori_group'] ?? '') === 'guru')),
                                'support_system' => array_values(array_filter($rRows, fn($x) => ($x['kategori_group'] ?? '') !== 'guru')),
                            ];
                            if (empty($rRows)): 
                            ?>
                                <tr>
                                    <td colspan="20" class="py-12 text-center text-slate-400">
                                        Tidak ada data pegawai yang sesuai dengan filter periode atau unit.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php 
                                $webSections = [
                                    ['letter' => 'A.', 'label' => 'Guru', 'items' => $rGroups['guru'] ?? []],
                                    ['letter' => 'B.', 'label' => 'Support System', 'items' => $rGroups['support_system'] ?? []],
                                ];
                                foreach ($webSections as $sec):
                                    if (empty($sec['items'])) continue;
                                ?>
                                    <tr class="bg-sky-200/80 border-y border-slate-300 font-extrabold text-slate-900">
                                        <td class="py-2 px-2.5 text-center border-r border-slate-300"><?= e($sec['letter']) ?></td>
                                        <td class="py-2 px-3 border-r border-slate-300"><?= e($sec['label']) ?></td>
                                        <td colspan="18" class="py-2 px-2"></td>
                                    </tr>
                                    <?php 
                                    $rNo = 1;
                                    foreach ($sec['items'] as $row): 
                                        $pct = (float)($row['total_kehadiran'] ?? 0);
                                        $pctDisiplin = (float)($row['presentasi_kedisiplinan'] ?? 0);
                                        $hariMasuk = (int)($row['jumlah_hadir'] ?? 0);
                                        $badgeBg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                        if ($pct < 75) {
                                            $badgeBg = 'bg-rose-50 text-rose-700 border-rose-200';
                                        } elseif ($pct < 90) {
                                            $badgeBg = 'bg-sky-50 text-sky-700 border-sky-200';
                                        }
                                    ?>
                                        <tr class="hover:bg-slate-50/70 transition-colors">
                                            <td class="py-2.5 px-2.5 text-center text-slate-500 font-bold border-r border-slate-100"><?= !empty($row['custom_urutan']) ? (int)$row['custom_urutan'] : $rNo ?><?php $rNo++; ?></td>
                                            <td class="py-2.5 px-3 border-r border-slate-100">
                                                <div class="font-bold text-slate-900"><?= e($row['nama']) ?></div>
                                                <div class="text-[10px] text-slate-500"><?= e($row['unit_tugas']) ?> · <?= e($row['jabatan']) ?></div>
                                            </td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 text-slate-600"><?= (int)($row['kehadiran_lain'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 text-slate-700"><?= (int)($row['periode_1'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 text-slate-700"><?= (int)($row['periode_2'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 font-bold text-slate-900"><?= (int)($row['jumlah_hari_kerja'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 font-extrabold text-primary-700"><?= $hariMasuk ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 font-bold <?= (int)($row['terlambat'] ?? 0) > 0 ? 'text-amber-600 bg-amber-50/50' : 'text-slate-400' ?>"><?= (int)($row['terlambat'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 font-bold <?= (int)($row['terlambat_finger'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-400' ?>"><?= (int)($row['terlambat_finger'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 font-bold <?= (int)($row['terlambat_piket'] ?? 0) > 0 ? 'text-orange-600 bg-orange-50/60' : 'text-slate-400' ?>"><?= (int)($row['terlambat_piket'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 font-bold text-emerald-600"><?= (int)($row['hadir_tanpa_terlambat'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 <?= (int)($row['izin'] ?? 0) > 0 ? 'font-bold text-sky-600' : 'text-slate-400' ?>"><?= (int)($row['izin'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 <?= (int)($row['izin_khusus'] ?? 0) > 0 ? 'font-bold text-indigo-600' : 'text-slate-400' ?>"><?= (int)($row['izin_khusus'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 <?= (int)($row['sakit'] ?? 0) > 0 ? 'font-bold text-purple-600' : 'text-slate-400' ?>"><?= (int)($row['sakit'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 <?= (int)($row['alpa'] ?? 0) > 0 ? 'font-bold text-rose-600 bg-rose-50/50' : 'text-slate-400' ?>"><?= (int)($row['alpa'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 <?= (int)($row['cuti'] ?? 0) > 0 ? 'font-bold text-teal-600' : 'text-slate-400' ?>"><?= (int)($row['cuti'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 <?= (int)($row['tugas'] ?? 0) > 0 ? 'font-bold text-cyan-700' : 'text-slate-400' ?>"><?= (int)($row['tugas'] ?? 0) ?></td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-extrabold border <?= $badgeBg ?>">
                                                    <?= number_format($pct, 0) ?>%
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-2 text-center border-r border-slate-100 font-extrabold text-slate-800">
                                                <?= $hariMasuk > 0 ? number_format($pctDisiplin, 0) . '%' : '0%' ?>
                                            </td>
                                            <td class="py-2.5 px-2.5 text-center">
                                                <a href="<?= url('kelola-absen-pegawai?tab=rekap&rekap_view=harian&tanggal=' . urlencode($targetDate) . '&search=' . urlencode($row['niy'] ?: $row['nama_polos'])) ?>"
                                                   class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[11px] font-bold bg-primary-50 text-primary-700 hover:bg-primary-600 hover:text-white border border-primary-200 transition-all"
                                                   title="Lihat & Ubah Log Presensi Harian Pegawai Ini">
                                                    <span>Log</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-slate-100/90 border-t-2 border-slate-300 text-xs font-bold text-slate-800">
                            <tr>
                                <td colspan="5" class="py-3 px-4 text-right border-r border-slate-200">TOTAL / RATA-RATA:</td>
                                <td class="py-3 px-2 text-center border-r border-slate-200"><?= (int)($rekapPeriodeData['jumlah_hari_kerja'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200 text-primary-700"><?= number_format($pSum['total_hadir_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200 text-amber-600"><?= number_format($pSum['total_terlambat_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200 text-amber-600"><?= number_format($pSum['total_terlambat_finger_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200 text-orange-600"><?= number_format($pSum['total_terlambat_piket_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200 text-emerald-600"><?= number_format($pSum['total_tepat_waktu_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200"><?= number_format($pSum['total_izin_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200"><?= number_format($pSum['total_izin_khusus_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200"><?= number_format($pSum['total_sakit_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200 text-rose-600"><?= number_format($pSum['total_alpa_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200"><?= number_format($pSum['total_cuti_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200"><?= number_format($pSum['total_tugas_semua'] ?? 0) ?></td>
                                <td class="py-3 px-2 text-center border-r border-slate-200 text-primary-700"><?= number_format($pSum['rata_rata_kehadiran'] ?? 0, 0) ?>%</td>
                                <td class="py-3 px-2 text-center border-r border-slate-200 text-emerald-700"><?= number_format($pSum['rata_rata_kedisiplinan'] ?? 0, 0) ?>%</td>
                                <td class="py-3 px-2.5 text-center text-slate-400">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>

        <!-- ═════════════════════════════════════════════════════════════════
             SUB-VIEW B: LOG PRESENSI HARIAN PEGAWAI (DETAIL PER TANGGAL)
             ═════════════════════════════════════════════════════════════════ -->
        <div id="subview-rekap-harian" class="space-y-4 <?= $currentRekapView === 'harian' ? '' : 'hidden' ?>">

        <!-- Toolbar Filter Tanggal & Unit -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <form method="GET" action="<?= url('kelola-absen-pegawai') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="rekap">
                <input type="hidden" name="rekap_view" value="harian">

                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700">Tanggal:</span>
                    <input type="date" name="tanggal" id="rekap_harian_filter_tanggal" value="<?= e($targetDate) ?>" onchange="this.form.submit()"
                           class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="document.getElementById('rekap_harian_filter_tanggal').value='<?= date('Y-m-d') ?>'; this.closest('form').submit();"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold <?= $targetDate === date('Y-m-d') ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                        Hari Ini
                    </button>
                    <button type="button" onclick="document.getElementById('rekap_harian_filter_tanggal').value='<?= date('Y-m-d', strtotime('-1 day')) ?>'; this.closest('form').submit();"
                            class="px-2.5 py-1.5 rounded-xl text-xs font-bold <?= $targetDate === date('Y-m-d', strtotime('-1 day')) ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                        Kemarin
                    </button>
                </div>

                <div class="relative min-w-[200px]">
                    <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Cari nama pegawai / NIY..."
                           class="w-full pl-9 pr-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>

                <select name="unit_tugas" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Unit Tugas</option>
                    <?php foreach ($unitLokasiList as $ul): ?>
                        <option value="<?= e($ul['unit_tugas']) ?>" <?= $filters['unit_tugas'] === $ul['unit_tugas'] ? 'selected' : '' ?>>
                            <?= e($ul['unit_tugas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 outline-none">
                    <option value="">Semua Status Kehadiran</option>
                    <option value="Hadir" <?= $filters['status'] === 'Hadir' ? 'selected' : '' ?>>Tepat Waktu (Hadir)</option>
                    <option value="Terlambat" <?= $filters['status'] === 'Terlambat' ? 'selected' : '' ?>>Terlambat</option>
                    <option value="Izin" <?= $filters['status'] === 'Izin' ? 'selected' : '' ?>>Izin</option>
                    <option value="Sakit" <?= $filters['status'] === 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                    <option value="Cuti" <?= $filters['status'] === 'Cuti' ? 'selected' : '' ?>>Cuti</option>
                    <option value="Alpha" <?= ($filters['status'] === 'Alpha' || $filters['status'] === 'Alpa') ? 'selected' : '' ?>>Alpa</option>
                    <option value="Dinas Luar" <?= $filters['status'] === 'Dinas Luar' ? 'selected' : '' ?>>Dinas Luar</option>
                    <option value="Belum" <?= $filters['status'] === 'Belum' ? 'selected' : '' ?>>Belum Check-in</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs cursor-pointer">
                    Filter
                </button>
            </form>

            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs font-semibold text-slate-500">
                    Batas Jam Masuk: <strong class="text-rose-600 font-mono"><?= substr($rekapData['batasJamMasuk'], 0, 5) ?> WITA</strong>
                </span>
            </div>
        </div>

        <!-- 4 KPI Cards Rekap Kehadiran -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pegawai</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= number_format($rekapData['stats']['totalPegawai']) ?></div>
                <p class="text-[11px] text-slate-500 mt-0.5">Pegawai aktif terdaftar</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-emerald-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Hadir Tepat Waktu</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1"><?= number_format($rekapData['stats']['totalHadir']) ?></div>
                <p class="text-[11px] text-emerald-700 mt-0.5">&le; <?= substr($rekapData['batasJamMasuk'], 0, 5) ?> WITA</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-amber-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Terlambat</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1"><?= number_format($rekapData['stats']['totalTerlambat']) ?></div>
                <p class="text-[11px] text-amber-700 mt-0.5">&gt; <?= substr($rekapData['batasJamMasuk'], 0, 5) ?> WITA</p>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Belum Check-in</span>
                <div class="text-2xl font-extrabold text-slate-500 mt-1"><?= number_format($rekapData['stats']['totalBelum']) ?></div>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    <?= !empty($rekapData['stats']['totalIzinSakit']) ? '+' . $rekapData['stats']['totalIzinSakit'] . ' Izin/Sakit/Alpa' : 'Belum absen hari ini' ?>
                </p>
            </div>
        </div>

        <!-- Tabel Log Jam Kedatangan & Pulang -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Rekap Jam Kedatangan Pegawai (<?= date('d M Y', strtotime($targetDate)) ?>)</h3>
                    <p class="text-xs text-slate-500">Mencatat jam check-in, status keterlambatan, jarak GPS ke kantor, dan jam check-out.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600 bg-white px-3 py-1 rounded-xl border border-slate-200">
                        Batas Jam Masuk Global: <strong class="text-rose-600 font-mono"><?= substr($rekapData['batasJamMasuk'], 0, 5) ?> WITA</strong>
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-3 text-center w-10">No</th>
                            <th class="py-3.5 px-4">Nama Pegawai & NIY</th>
                            <th class="py-3.5 px-4">Unit Tugas</th>
                            <th class="py-3.5 px-4 text-center">Jam Kedatangan (Check-in)</th>
                            <th class="py-3.5 px-4 text-center">Status Kehadiran</th>
                            <th class="py-3.5 px-4 text-center">Jam Pulang (Check-out)</th>
                            <th class="py-3.5 px-4">Lokasi Absen Efektif</th>
                            <th class="py-3.5 px-4 text-center">Jarak Meter</th>
                            <th class="py-3.5 px-4">Keterangan</th>
                            <th class="py-3.5 px-3 text-center w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($rekapData['logs'])): ?>
                            <tr>
                                <td colspan="10" class="py-12 text-center text-slate-400">
                                    Tidak ada data kehadiran pada tanggal <?= date('d F Y', strtotime($targetDate)) ?>.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rekapData['logs'] as $idx => $r):
                                $namaLengkap = $r['nama'] . (!empty($r['gelar']) ? ', ' . $r['gelar'] : '');
                                $statusKehadiran = $r['status'] ?? $r['log_status'] ?? '';
                                $isTerlambat = ($statusKehadiran === 'Terlambat');
                                $isHadir = ($statusKehadiran === 'Hadir');
                                $hasCheckin = !empty($r['jam_masuk']);
                                $isCustomJam = !empty($r['is_custom_jam_kerja']);
                                $jamBatasEfektif = $r['jam_masuk_batas_efektif'] ?? $rekapData['batasJamMasuk'];
                            ?>
                                <tr class="hover:bg-slate-50/80 transition-colors <?= $isTerlambat ? 'bg-amber-50/30' : '' ?>">
                                    <td class="py-3.5 px-3 text-center text-slate-400 font-semibold"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800"><?= e($namaLengkap) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">NIY: <?= e($r['niy'] ?: '-') ?></div>
                                        <?php if ($isCustomJam): ?>
                                            <span class="inline-flex items-center gap-1 mt-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                ⏰ Jam Khusus (Batas <?= substr($jamBatasEfektif, 0, 5) ?>)
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-700"><?= e($r['unit_tugas'] ?: 'Umum') ?></div>
                                        <div class="text-[11px] text-slate-400"><?= e($r['jabatan'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ($hasCheckin): ?>
                                            <span class="font-extrabold font-mono text-xs <?= $isTerlambat ? 'text-amber-700' : 'text-slate-800' ?>">
                                                <?= substr($r['jam_masuk'], 0, 5) ?> WITA
                                            </span>
                                            <div class="text-[10px] text-slate-400 font-mono">
                                                Batas: <?= substr($jamBatasEfektif, 0, 5) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                            <div class="text-[10px] text-slate-400 font-mono">
                                                Batas: <?= substr($jamBatasEfektif, 0, 5) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if ($isTerlambat): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Terlambat
                                            </span>
                                        <?php elseif ($isHadir): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Tepat Waktu
                                            </span>
                                        <?php elseif (in_array(strtolower($statusKehadiran), ['izin'], true)): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-sky-100 text-sky-800 border border-sky-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                                Izin
                                            </span>
                                        <?php elseif (in_array(strtolower($statusKehadiran), ['sakit'], true)): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-purple-100 text-purple-800 border border-purple-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                Sakit
                                            </span>
                                        <?php elseif (in_array(strtolower($statusKehadiran), ['cuti'], true)): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-teal-100 text-teal-800 border border-teal-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                                Cuti
                                            </span>
                                        <?php elseif (in_array(strtolower($statusKehadiran), ['alpha', 'alpa'], true)): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Alpa
                                            </span>
                                        <?php elseif (in_array(strtolower($statusKehadiran), ['dinas luar', 'tugas luar'], true)): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                                Dinas Luar
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                                Belum Check-in
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <?php if (!empty($r['jam_pulang'])): ?>
                                            <span class="font-mono font-bold text-xs text-sky-700">
                                                <?= substr($r['jam_pulang'], 0, 5) ?> WITA
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php $lokasiRekapTampil = ($r['nama_kantor_absen'] ?: ($r['nama_kantor_efektif'] ?? $defaultKantor)); ?>
                                        <div class="font-medium text-slate-800 truncate max-w-[200px]" title="<?= e($lokasiRekapTampil) ?>">
                                            🏢 <?= e($lokasiRekapTampil) ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-mono">
                                        <?php if ($r['jarak_masuk_meter'] !== null): ?>
                                            <span class="px-2 py-0.5 rounded-md font-bold text-[11px] <?= (float)$r['jarak_masuk_meter'] <= (int)$radiusGlobal ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                                <?= round((float)$r['jarak_masuk_meter']) ?> m
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="text-xs text-slate-600"><?= e($r['keterangan'] ?? $r['log_keterangan'] ?? '-') ?></span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <button type="button" 
                                                onclick="openModalEditLogPresensi(<?= htmlspecialchars(json_encode([
                                                    'pegawai_id' => $r['id'],
                                                    'nama' => $namaLengkap,
                                                    'niy' => $r['niy'] ?? '',
                                                    'unit_tugas' => $r['unit_tugas'] ?? '',
                                                    'jabatan' => $r['jabatan'] ?? '',
                                                    'tanggal' => $targetDate,
                                                    'tanggal_formatted' => date('d F Y', strtotime($targetDate)),
                                                    'jam_masuk' => $r['jam_masuk'] ?? '',
                                                    'jam_pulang' => $r['jam_pulang'] ?? '',
                                                    'status' => $statusKehadiran ?: 'Hadir',
                                                    'keterangan' => $r['keterangan'] ?? $r['log_keterangan'] ?? '',
                                                    'nama_kantor' => $r['nama_kantor_absen'] ?: ($r['nama_kantor_efektif'] ?? $defaultKantor),
                                                    'batas_jam' => substr($jamBatasEfektif, 0, 5),
                                                    'has_log' => !empty($r['log_id']),
                                                    'return_tab' => 'rekap',
                                                    'rekap_view' => 'harian',
                                                ]), ENT_QUOTES, 'UTF-8') ?>)"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-primary-50 text-primary-700 hover:bg-primary-600 hover:text-white border border-primary-200 transition-all cursor-pointer shadow-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            <span>Ubah</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        </div> <!-- End of subview-rekap-harian -->

        <!-- ═════════════════════════════════════════════════════════════════
             SUB-VIEW C: MATRIKS KEHADIRAN PEGAWAI (PER TANGGAL)
             ═════════════════════════════════════════════════════════════════ -->
        <div id="subview-rekap-matrix" class="space-y-4 <?= $currentRekapView === 'matrix' ? '' : 'hidden' ?>">
            <?php
            $matrixEndDefault = $filters['matrix_end_date'] ?? $filters['end_date'];
            $mData = $rekapMatrixData ?? AbsenPegawaiModel::getRekapMatrix($filters['start_date'], $matrixEndDefault, $filters['unit_tugas'], $filters['search']);
            $mDates = $mData['dates'] ?? [];
            $mGroups = $mData['groups'] ?? ['guru' => [], 'support_system' => []];
            ?>
            <!-- Toolbar Filter Matriks -->
            <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-xs">
                <form method="GET" action="<?= url('kelola-absen-pegawai') ?>" class="flex flex-wrap items-center gap-2.5">
                    <input type="hidden" name="tab" value="rekap">
                    <input type="hidden" name="rekap_view" value="matrix">

                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-slate-700">Periode:</span>
                        <input type="date" name="start_date" value="<?= e($mData['start_date'] ?? $filters['start_date']) ?>"
                               class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                        <span class="text-xs text-slate-400 font-bold">s/d</span>
                        <input type="date" name="end_date" value="<?= e($mData['end_date'] ?? $matrixEndDefault) ?>"
                               class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                    </div>

                    <?php if (empty($isUnitRestricted)): ?>
                        <select name="unit_tugas" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none">
                            <option value="">Semua Unit</option>
                            <?php foreach (['PAUD', 'SD', 'SMP', 'SMA', 'Yayasan', 'Staff'] as $uOpt): ?>
                                <option value="<?= $uOpt ?>" <?= ($filters['unit_tugas'] ?? '') === $uOpt ? 'selected' : '' ?>><?= $uOpt ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Cari nama / NIY..."
                           class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:bg-white focus:border-primary-500">

                    <button type="submit" class="px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold cursor-pointer">
                        Tampilkan Matriks
                    </button>
                    <button type="button" onclick="openModalUrutanUnit('matrix')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold transition-all cursor-pointer ml-auto">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                        </svg>
                        <span>Atur Urutan Unit</span>
                    </button>
                </form>
            </div>

            <!-- Tabel Matriks Kehadiran (Sama Dengan Matrix Kelola Jurnal) -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Data Rekap Harian Matrix (✓ Hadir / ✕ Alpa)</h3>
                        <p class="text-xs text-slate-500">
                            Menampilkan matriks kehadiran pegawai dari tanggal <strong><?= date('d M Y', strtotime($mData['start_date'] ?? $filters['start_date'])) ?></strong> s/d <strong><?= date('d M Y', strtotime($mData['end_date'] ?? $matrixEndDefault)) ?></strong>
                        </p>
                    </div>

                    <!-- Legend -->
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-4 h-4 rounded-md bg-emerald-500 text-white flex items-center justify-center text-[10px]">✓</span>
                            Hadir
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-4 h-4 rounded-md bg-amber-500 text-white flex items-center justify-center text-[10px]">T</span>
                            Terlambat
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-orange-50 text-orange-700 border border-orange-200">
                            <span class="w-4 h-4 rounded-md bg-orange-500 text-white flex items-center justify-center text-[9px]">TP</span>
                            Telat Piket
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-sky-50 text-sky-700 border border-sky-200">
                            <span class="w-4 h-4 rounded-md bg-sky-500 text-white flex items-center justify-center text-[10px]">I</span>
                            Izin/Sakit/Cuti
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-4 h-4 rounded-md bg-rose-500 text-white flex items-center justify-center text-[10px]">✕</span>
                            Alpa
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-500">
                            <span class="w-4 h-4 rounded-md bg-slate-200 text-slate-500 flex items-center justify-center text-[10px]">-</span>
                            Libur / Ahad
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-extrabold text-slate-600 uppercase">
                                <th class="py-3 px-3 sticky left-0 z-20 bg-slate-50 border-r border-slate-200 min-w-[195px]">Nama Pegawai</th>
                                <?php foreach ($mDates as $md):
                                    $hdrCls = !empty($md['is_sunday'])
                                        ? 'bg-rose-50/60 text-rose-600'
                                        : (!empty($md['is_today']) ? 'bg-primary-50 text-primary-700' : (!empty($md['is_libur']) ? 'bg-amber-50/70 text-amber-700' : ''));
                                ?>
                                    <th class="py-2 px-1.5 text-center min-w-[38px] border-r border-slate-100 <?= $hdrCls ?>"
                                        title="<?= e($md['ymd'] . (!empty($md['libur_nama']) ? ' (' . $md['libur_nama'] . ')' : '')) ?>">
                                        <div class="text-[9px] font-bold opacity-75"><?= e($md['day_short']) ?></div>
                                        <div class="text-xs font-black"><?= e($md['day_num']) ?></div>
                                        <div class="text-[8px] font-normal opacity-70"><?= e($md['month_short'] ?? '') ?></div>
                                    </th>
                                <?php endforeach; ?>
                                <th class="py-3 px-2.5 text-center bg-emerald-50/60 text-emerald-700 border-l border-slate-200">Total ✓</th>
                                <th class="py-3 px-2.5 text-center bg-amber-50/60 text-amber-700">Telat</th>
                                <th class="py-3 px-2.5 text-center bg-rose-50/60 text-rose-700">Total ✕</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php
                            $mSections = [
                                ['letter' => 'A.', 'label' => 'Guru', 'items' => $mGroups['guru'] ?? []],
                                ['letter' => 'B.', 'label' => 'Support System', 'items' => $mGroups['support_system'] ?? []],
                            ];
                            $hasAnyMatrix = !empty($mGroups['guru']) || !empty($mGroups['support_system']);
                            if (!$hasAnyMatrix):
                            ?>
                                <tr>
                                    <td colspan="<?= count($mDates) + 4 ?>" class="py-10 text-center text-slate-400">
                                        Tidak ada data pegawai untuk filter ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($mSections as $sec): if (empty($sec['items'])) continue; ?>
                                    <tr class="bg-sky-50/90 font-extrabold text-slate-800">
                                        <td class="py-2 px-3 sticky left-0 z-10 bg-sky-50 border-r border-slate-200">
                                            <?= e($sec['letter'] . ' ' . $sec['label']) ?>
                                        </td>
                                        <td colspan="<?= count($mDates) + 3 ?>"></td>
                                    </tr>
                                    <?php foreach ($sec['items'] as $mRow): ?>
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="py-2.5 px-3 sticky left-0 z-10 bg-white border-r border-slate-200 font-bold text-slate-900">
                                                <div class="truncate max-w-[185px]" title="<?= e($mRow['nama']) ?>"><?= e($mRow['nama']) ?></div>
                                                <div class="text-[10px] text-slate-400 font-normal truncate max-w-[185px]"><?= e($mRow['unit_tugas']) ?></div>
                                            </td>
                                            <?php foreach ($mDates as $md):
                                                $ymd = $md['ymd'];
                                                $c = $mRow['cells'][$ymd] ?? ['code' => ''];
                                                $code = $c['code'] ?? '';
                                                $tip = $ymd . ($code !== '' ? (' • ' . ($c['status'] ?? $code) . (!empty($c['jam_masuk']) ? ' (' . $c['jam_masuk'] . ')' : '')) : (!empty($md['is_sunday']) ? ' • Ahad' : (!empty($md['libur_nama']) ? ' • ' . $md['libur_nama'] : '')));
                                                $editPayload = json_encode([
                                                    'pegawai_id' => $mRow['pegawai_id'],
                                                    'nama' => $mRow['nama'],
                                                    'niy' => $mRow['niy'],
                                                    'unit_tugas' => $mRow['unit_tugas'],
                                                    'jabatan' => $mRow['jabatan'],
                                                    'tanggal' => $ymd,
                                                    'tanggal_formatted' => date('d/m/Y', strtotime($ymd)),
                                                    'status' => $c['status'] ?: 'Hadir',
                                                    'jam_masuk' => $c['jam_masuk'] ?? '',
                                                    'jam_pulang' => $c['jam_pulang'] ?? '',
                                                    'keterangan' => $c['keterangan'] ?? '',
                                                    'nama_kantor' => $c['nama_kantor'] ?? '',
                                                    'batas_jam' => $c['batas_jam'] ?? '07:30',
                                                    'has_log' => !empty($c['has_log']),
                                                    'return_tab' => 'rekap',
                                                    'rekap_view' => 'matrix',
                                                ]);
                                            ?>
                                                <td class="py-2 px-1 text-center border-r border-slate-100 <?= !empty($md['is_sunday']) ? 'bg-rose-50/20' : '' ?>">
                                                    <?php if ($code === ''): ?>
                                                        <span class="text-slate-300 font-bold text-xs cursor-pointer"
                                                              onclick='openModalEditLogPresensi(<?= htmlspecialchars($editPayload, ENT_QUOTES, "UTF-8") ?>)'
                                                              title="<?= e($tip) ?>">-</span>
                                                    <?php elseif ($code === 'A'): ?>
                                                        <button type="button"
                                                                onclick='openModalEditLogPresensi(<?= htmlspecialchars($editPayload, ENT_QUOTES, "UTF-8") ?>)'
                                                                title="<?= e($tip . ' (Klik untuk isi/ubah)') ?>"
                                                                class="w-7 h-7 mx-auto rounded-lg bg-rose-100 hover:bg-rose-500 text-rose-600 hover:text-white border border-rose-300 font-black text-xs flex items-center justify-center transition-all cursor-pointer">
                                                            ✕
                                                        </button>
                                                    <?php else:
                                                        $solidCls = match($code) {
                                                            'H'  => 'bg-emerald-500 text-white',
                                                            'T'  => 'bg-amber-500 text-white',
                                                            'TP' => 'bg-orange-500 text-white text-[10px]',
                                                            'I'  => 'bg-sky-500 text-white',
                                                            'S'  => 'bg-purple-500 text-white',
                                                            'C'  => 'bg-teal-500 text-white',
                                                            'D'  => 'bg-cyan-600 text-white',
                                                            default => 'bg-slate-400 text-white',
                                                        };
                                                        $disp = ($code === 'H') ? '✓' : $code;
                                                    ?>
                                                        <button type="button"
                                                                onclick='openModalEditLogPresensi(<?= htmlspecialchars($editPayload, ENT_QUOTES, "UTF-8") ?>)'
                                                                title="<?= e($tip) ?>"
                                                                class="w-7 h-7 mx-auto rounded-lg font-black text-xs flex items-center justify-center shadow-2xs transition-transform hover:scale-105 cursor-pointer <?= $solidCls ?>">
                                                            <?= $disp ?>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="py-2.5 px-2.5 text-center font-black text-emerald-700 bg-emerald-50/30 border-l border-slate-200"><?= (int)$mRow['hadir'] ?></td>
                                            <td class="py-2.5 px-2.5 text-center font-black text-amber-600 bg-amber-50/30"><?= (int)$mRow['terlambat'] ?></td>
                                            <td class="py-2.5 px-2.5 text-center font-black text-rose-600 bg-rose-50/30"><?= (int)($mRow['alpa'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div> <!-- End of subview-rekap-matrix -->
    </div> <!-- End of absen-panel-rekap -->

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 3: LOKASI ABSEN CUSTOM PER UNIT
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="absen-panel-unit" class="absen-tab-panel space-y-4 <?= $activeTab === 'unit' ? '' : 'hidden' ?>">
        <div class="bg-purple-50/80 border border-purple-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-purple-950 text-sm">Pengaturan Lokasi Absen Custom Per Unit Tugas</h3>
                <p class="text-xs text-purple-800 mt-0.5">
                    Anda dapat mengatur <strong>Nama Kantor Tempat Absen</strong>, <strong>Latitude</strong>, dan <strong>Longitude</strong> untuk setiap Unit (PAUD, SD, SMP, SMA, Yayasan, dll.). Seluruh pegawai di unit tersebut otomatis mengikuti titik lokasi unitnya.
                </p>
            </div>
            <button type="button" onclick="openModalCustomUnit()"
                class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm cursor-pointer shrink-0">
                + Atur Lokasi Unit
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($unitLokasiList as $ul):
                $latU = number_format((float)$ul['latitude'], 6, '.', '');
                $lngU = number_format((float)$ul['longitude'], 6, '.', '');
                $isCustomU = !empty($ul['is_custom_unit']);
            ?>
                <div class="bg-white rounded-2xl p-5 border <?= $isCustomU ? 'border-purple-300 shadow-sm' : 'border-slate-200/80 shadow-xs' ?> flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full <?= $isCustomU ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-sky-50 text-sky-700 border border-sky-200' ?>">
                                    <?= $isCustomU ? 'Custom Unit Aktif' : 'Mengikuti Lokasi Semula' ?>
                                </span>
                                <h4 class="text-base font-extrabold text-slate-900 mt-2"><?= e($ul['unit_tugas']) ?></h4>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-extrabold text-xs">
                                <?= (int)$ul['total_pegawai'] ?> Pegawai
                            </span>
                        </div>

                        <div class="mt-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Nama Kantor Tempat Absen:</span>
                                <span class="font-bold text-slate-800">🏢 <?= e($ul['nama_kantor']) ?></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-200/60 font-mono">
                                <div>
                                    <span class="text-[10px] font-sans text-slate-400 block">Latitude:</span>
                                    <strong class="text-slate-800"><?= $latU ?></strong>
                                </div>
                                <div>
                                    <span class="text-[10px] font-sans text-slate-400 block">Longitude:</span>
                                    <strong class="text-slate-800"><?= $lngU ?></strong>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                                <span class="text-[11px] text-slate-500">Radius Global: <strong class="text-emerald-700"><?= (int)$ul['radius_meter'] ?> Meter</strong></span>
                                <a href="https://www.google.com/maps?q=<?= $latU ?>,<?= $lngU ?>" target="_blank" class="text-[11px] font-bold text-sky-600 hover:underline">
                                    Lihat Maps &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                        <button type="button"
                            onclick="openModalCustomUnit(<?= htmlspecialchars(json_encode($ul['unit_tugas']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($ul['nama_kantor']), ENT_QUOTES, 'UTF-8') ?>, '<?= $latU ?>', '<?= $lngU ?>')"
                            class="flex-1 py-2 px-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs text-center cursor-pointer transition-colors">
                            Atur Lokasi Unit
                        </button>

                        <?php if ($isCustomU): ?>
                            <form method="POST" action="<?= url('kelola-absen-pegawai/unit/reset') ?>" onsubmit="return confirm('Reset lokasi Unit <?= e(addslashes($ul['unit_tugas'])) ?> kembali ke Lokasi Semula?');">
                                <?= CSRF::field() ?>
                                <input type="hidden" name="unit_tugas" value="<?= e($ul['unit_tugas']) ?>">
                                <input type="hidden" name="return_tab" value="unit">
                                <button type="submit" class="py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-bold text-xs cursor-pointer">
                                    Reset Semula
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 3: PENGATURAN LOKASI SEMULA (DEFAULT) & RADIUS GLOBAL
         ═════════════════════════════════════════════════════════════════════ -->
    <div id="absen-panel-pengaturan" class="absen-tab-panel space-y-6 <?= $activeTab === 'pengaturan' ? '' : 'hidden' ?>">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Pengaturan Utama -->
            <div class="lg:col-span-6 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-extrabold text-slate-900">Pengaturan Lokasi Absen Semula (Default) & Radius Global</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Titik lokasi di bawah ini adalah <strong>Lokasi Kantor Semula (Default)</strong> yang menjadi acuan utama seluruh pegawai dan saat fitur <strong>Reset Lokasi Semula</strong> dijalankan.
                    </p>
                </div>

                <form method="POST" action="<?= url('kelola-absen-pegawai/pengaturan/save') ?>" class="space-y-4">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="return_tab" value="pengaturan">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kantor / Tempat Absen Semula (Utama) <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_kantor_default" id="def_nama_kantor" value="<?= e($defaultKantor) ?>" required
                               placeholder="Contoh: Kampus Utama SIT Bina Insan Palu"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-primary-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Latitude Default <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="latitude_default" id="def_lat" value="<?= number_format($defaultLat, 7, '.', '') ?>" required
                                   oninput="updateDefaultMapMarker()"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Longitude Default <span class="text-rose-500">*</span></label>
                            <input type="number" step="any" name="longitude_default" id="def_lng" value="<?= number_format($defaultLng, 7, '.', '') ?>" required
                                   oninput="updateDefaultMapMarker()"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-primary-500">
                        </div>
                    </div>

                    <!-- Radius Global -->
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-2.5">
                        <label class="block text-xs font-extrabold text-emerald-950">
                            Radius Presensi Global (Berlaku untuk Semua Pegawai & Unit) <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="number" name="radius_meter" id="def_radius" value="<?= $radiusGlobal ?>" min="1" max="5000" required
                                   oninput="updateDefaultMapMarker()"
                                   class="w-28 px-3.5 py-2 rounded-xl border border-emerald-300 bg-white text-sm font-extrabold text-emerald-900 outline-none">
                            <span class="text-xs font-bold text-emerald-800">Meter</span>

                            <div class="flex flex-wrap gap-1 ml-auto">
                                <?php foreach ([5, 10, 15, 25, 50, 100] as $rOpt): ?>
                                    <button type="button" onclick="document.getElementById('def_radius').value='<?= $rOpt ?>'; updateDefaultMapMarker();"
                                            class="px-2 py-1 rounded-lg bg-white hover:bg-emerald-600 text-emerald-800 hover:text-white border border-emerald-200 text-[11px] font-bold transition-colors cursor-pointer">
                                        <?= $rOpt ?>m
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <p class="text-[11px] text-emerald-800">
                            Radius ini berlaku untuk seluruh pegawai tanpa terkecuali (baik lokasi semula, custom unit, maupun custom individu).
                        </p>
                    </div>

                    <!-- Jam Kerja & Batas Keterlambatan -->
                    <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base">⏰</span>
                                <div>
                                    <h4 class="text-xs font-extrabold text-amber-950">Jam Kedatangan &amp; Toleransi Absen (Senin – Jumat)</h4>
                                    <p class="text-[11px] text-amber-800">Berlaku sebagai jam kerja standar hari kerja biasa untuk semua pegawai (yang tidak diatur khusus).</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                            <div>
                                <label class="block text-[11px] font-bold text-amber-950 mb-1">Mulai Check-in</label>
                                <input type="time" name="jam_masuk_mulai" value="<?= e($jamMasukMulai) ?>" required
                                       class="w-full px-3 py-2 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                                <span class="text-[10px] text-amber-700">Awal buka absen masuk</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-amber-950 mb-1">Batas Tepat Waktu <span class="text-rose-500">*</span></label>
                                <input type="time" name="jam_masuk_batas" value="<?= e($jamMasukBatas) ?>" required
                                       class="w-full px-3 py-2 rounded-xl border border-rose-300 bg-white text-xs font-extrabold text-rose-700 outline-none">
                                <span class="text-[10px] text-rose-600 font-semibold">&gt; jam ini = Terlambat</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-amber-950 mb-1">Buka Check-out</label>
                                <input type="time" name="jam_pulang_mulai" value="<?= e($jamPulangMulai) ?>" required
                                       class="w-full px-3 py-2 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                                <span class="text-[10px] text-amber-700">Awal buka absen pulang</span>
                            </div>
                        </div>

                        <!-- Pengaturan Jam Khusus Hari Sabtu -->
                        <div class="pt-3 mt-1 border-t border-amber-200/80 space-y-2.5">
                            <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="sabtu_khusus_aktif" id="chk_sabtu_khusus" value="1"
                                       <?= $sabtuKhususAktif ? 'checked' : '' ?> onchange="toggleSabtuKhususGlobal()"
                                       class="rounded text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-extrabold text-amber-950">🗓️ Bedakan Jam Khusus Hari Sabtu</span>
                                <span class="text-[10px] font-semibold text-amber-700">(Misal Senin–Jumat 07:00, Sabtu 07:30)</span>
                            </label>

                            <div id="box_sabtu_khusus_global" class="<?= $sabtuKhususAktif ? '' : 'hidden' ?> p-3 rounded-xl bg-white/90 border border-amber-200">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Mulai Check-in (Sabtu)</label>
                                        <input type="time" name="sabtu_jam_masuk_mulai" id="sabtu_jam_masuk_mulai" value="<?= e($sabtuJamMasukMulai) ?>"
                                               class="w-full px-3 py-1.5 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Batas Tepat Waktu (Sabtu) <span class="text-rose-500">*</span></label>
                                        <input type="time" name="sabtu_jam_masuk_batas" id="sabtu_jam_masuk_batas" value="<?= e($sabtuJamMasukBatas) ?>" <?= $sabtuKhususAktif ? 'required' : '' ?>
                                               class="w-full px-3 py-1.5 rounded-xl border border-rose-300 bg-white text-xs font-extrabold text-rose-700 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Buka Check-out (Sabtu)</label>
                                        <input type="time" name="sabtu_jam_pulang_mulai" id="sabtu_jam_pulang_mulai" value="<?= e($sabtuJamPulangMulai) ?>"
                                               class="w-full px-3 py-1.5 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Periode Rekap Presensi (Untuk Portal Mobile & Web) -->
                    <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-base">📅</span>
                                <div>
                                    <h4 class="text-xs font-extrabold text-indigo-950">Periode Default Rekap Presensi</h4>
                                    <p class="text-[11px] text-indigo-800">Menentukan rentang tanggal awal & akhir default di menu Rekap Portal Mobile Guru.</p>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-200 text-indigo-800">Mobile & Web</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-[11px] font-bold text-indigo-950 mb-1">Tanggal Awal Rekap <span class="text-rose-500">*</span></label>
                                <input type="date" name="periode_awal" id="periode_awal_absen" value="<?= e($globalConfig['periode_awal'] ?? date('Y-m-01')) ?>" required
                                       class="w-full px-3 py-2 rounded-xl border border-indigo-300 bg-white text-xs font-bold text-slate-800 outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-indigo-950 mb-1">Tanggal Akhir Rekap <span class="text-rose-500">*</span></label>
                                <input type="date" name="periode_akhir" id="periode_akhir_absen" value="<?= e($globalConfig['periode_akhir'] ?? date('Y-m-t')) ?>" required
                                       class="w-full px-3 py-2 rounded-xl border border-indigo-300 bg-white text-xs font-bold text-slate-800 outline-none">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <span class="text-[10px] font-bold text-indigo-700">Pilihan Cepat:</span>
                            <button type="button" onclick="setPeriodeAbsen('bulan_ini')" class="px-2 py-0.5 rounded-lg bg-white hover:bg-indigo-600 hover:text-white border border-indigo-200 text-[10px] font-semibold text-indigo-800 transition-colors cursor-pointer">Bulan Ini</button>
                            <button type="button" onclick="setPeriodeAbsen('bulan_lalu')" class="px-2 py-0.5 rounded-lg bg-white hover:bg-indigo-600 hover:text-white border border-indigo-200 text-[10px] font-semibold text-indigo-800 transition-colors cursor-pointer">Bulan Lalu</button>
                            <button type="button" onclick="setPeriodeAbsen('semester_ini')" class="px-2 py-0.5 rounded-lg bg-white hover:bg-indigo-600 hover:text-white border border-indigo-200 text-[10px] font-semibold text-indigo-800 transition-colors cursor-pointer">Semester Ini</button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        <button type="button" onclick="ambilGpsUntukDefault()"
                                class="px-3.5 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 font-bold text-xs cursor-pointer">
                            📍 Gunakan Titik GPS Saya Saat Ini
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md cursor-pointer">
                            Simpan Pengaturan Global
                        </button>
                    </div>
                </form>
            </div>

            <!-- Peta Visualisasi Lokasi Semula & Radius -->
            <div class="lg:col-span-6 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Peta Interaktif Lokasi Kantor Semula</h3>
                        <p class="text-xs text-slate-500">Klik pada peta atau geser pin biru untuk memilih titik Latitude & Longitude</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 font-bold text-xs">Interactive Map</span>
                </div>

                <div id="mapDefaultPicker" class="w-full h-80 rounded-2xl border border-slate-200 overflow-hidden z-10"></div>

                <div class="p-3.5 rounded-2xl bg-rose-50/70 border border-rose-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="text-xs font-extrabold text-rose-900">Ingin Mengembalikan Semua Lokasi ke Semula?</h4>
                        <p class="text-[11px] text-rose-700">Menghapus seluruh custom lokasi pegawai & unit agar kembali ke Lokasi Semula di atas.</p>
                    </div>
                    <form method="POST" action="<?= url('kelola-absen-pegawai/reset-semua') ?>" onsubmit="return confirm('Yakin ingin me-reset seluruh lokasi pegawai & unit kembali ke Lokasi Semula?');">
                        <?= CSRF::field() ?>
                        <input type="hidden" name="return_tab" value="pengaturan">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shrink-0 cursor-pointer">
                            ↺ Reset Semua Lokasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ═════════════════════════════════════════════════════════════════════
         TAB 6: PENGATURAN HARI LIBUR (SELAIN MINGGU)
         ═════════════════════════════════════════════════════════════════════ -->
    <?php
        $liburList = $liburList ?? [];
        $liburTahunSelected = $liburTahun ?? (int)date('Y');
        $rawLiburTahunQuery = trim((string)($_GET['libur_tahun'] ?? ''));
        $isAllTahunLibur = ($rawLiburTahunQuery === 'all');
        $liburDayMap = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
            5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
    ?>
    <div id="absen-panel-libur" class="absen-tab-panel space-y-6 <?= $activeTab === 'libur' ? '' : 'hidden' ?>">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Tambah Hari Libur -->
            <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Sinkron Absen &amp; Jurnal Staff
                        </span>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Tambah Hari Libur (Selain Minggu)</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Hari Minggu sudah otomatis tidak dihitung sebagai hari kerja. Tambahkan tanggal libur nasional, cuti bersama, atau libur yayasan di sini.
                    </p>
                </div>

                <div class="p-3.5 rounded-2xl bg-sky-50/80 border border-sky-200 text-xs text-sky-900 space-y-1">
                    <p class="font-bold flex items-center gap-1.5"><span>🔄</span> Sinkronisasi Otomatis:</p>
                    <p class="text-[11px] leading-relaxed text-sky-800">
                        Tanggal yang ditambahkan tidak dihitung hari kerja/alpa di <strong>Rekap Absen</strong>, menonaktifkan kewajiban isi <strong>Jurnal Staff</strong>, melewati <strong>Pengingat WA</strong>, dan membuat persentase periode lebih akurat.
                    </p>
                </div>

                <form method="POST" action="<?= url('kelola-absen-pegawai/libur/save') ?>" class="space-y-4">
                    <?= CSRF::field() ?>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" value="<?= date('Y-m-d') ?>" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Tanggal <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <input type="date" name="tanggal_selesai"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-primary-500">
                            <span class="text-[10px] text-slate-400">Kosongkan jika hanya 1 hari</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Hari Libur <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_libur" required placeholder="Contoh: Hari Raya Idul Fitri / Libur Awal Ramadhan"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-900 outline-none focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Libur</label>
                        <select name="jenis" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                            <option value="nasional">Libur Nasional</option>
                            <option value="cuti_bersama">Cuti Bersama</option>
                            <option value="yayasan">Libur Yayasan / Sekolah</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan <span class="text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="text" name="keterangan" placeholder="Contoh: Berdasarkan SKB 3 Menteri / Edaran Yayasan"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500">
                    </div>

                    <div class="pt-2 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 cursor-pointer">
                            + Simpan Hari Libur
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Hari Libur -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/60">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                            <span>Daftar Hari Libur Terdaftar</span>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-700">
                                <?= count($liburList) ?> Tanggal
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Selain daftar di bawah ini, seluruh hari <strong>Minggu</strong> otomatis dianggap libur.
                        </p>
                    </div>

                    <form method="GET" action="<?= url('kelola-absen-pegawai') ?>" class="flex items-center gap-2 shrink-0">
                        <input type="hidden" name="tab" value="libur">
                        <select name="libur_tahun" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 outline-none focus:border-primary-500">
                            <?php $curY = (int)date('Y'); ?>
                            <?php for ($y = $curY + 1; $y >= $curY - 2; $y--): ?>
                                <option value="<?= $y ?>" <?= (!$isAllTahunLibur && (int)$liburTahunSelected === $y) ? 'selected' : '' ?>>
                                    Tahun <?= $y ?>
                                </option>
                            <?php endfor; ?>
                            <option value="all" <?= $isAllTahunLibur ? 'selected' : '' ?>>Semua Tahun</option>
                        </select>
                    </form>
                </div>

                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Tanggal &amp; Hari</th>
                                <th class="py-3 px-4">Nama Hari Libur</th>
                                <th class="py-3 px-4 text-center">Kategori</th>
                                <th class="py-3 px-4 text-right w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($liburList)): ?>
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">🗓️</div>
                                        <p class="font-bold text-slate-600">Belum ada hari libur khusus terdaftar</p>
                                        <p class="text-[11px] mt-0.5">Tambahkan tanggal libur di formulir sebelah kiri jika ada hari libur selain Minggu.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($liburList as $lb): ?>
                                    <?php
                                        $lbTs = strtotime($lb['tanggal']);
                                        $lbDay = $liburDayMap[(int)date('N', $lbTs)] ?? '';
                                        $jenisVal = $lb['jenis'] ?? 'nasional';
                                        $jenisBadge = match ($jenisVal) {
                                            'cuti_bersama' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'yayasan'      => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'lainnya'      => 'bg-slate-100 text-slate-700 border-slate-200',
                                            default        => 'bg-rose-50 text-rose-700 border-rose-200',
                                        };
                                        $jenisLabel = match ($jenisVal) {
                                            'cuti_bersama' => 'Cuti Bersama',
                                            'yayasan'      => 'Libur Yayasan',
                                            'lainnya'      => 'Lainnya',
                                            default        => 'Nasional',
                                        };
                                    ?>
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="font-extrabold text-slate-900"><?= date('d M Y', $lbTs) ?></div>
                                            <div class="text-[10px] text-slate-500 font-semibold"><?= e($lbDay) ?></div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-800"><?= e($lb['nama_libur']) ?></div>
                                            <?php if (!empty($lb['keterangan'])): ?>
                                                <div class="text-[10.5px] text-slate-500 mt-0.5"><?= e($lb['keterangan']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $jenisBadge ?>">
                                                <?= e($jenisLabel) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <form method="POST" action="<?= url('kelola-absen-pegawai/libur/delete') ?>" onsubmit="return confirm('Hapus hari libur <?= e(addslashes($lb['nama_libur'])) ?> (<?= date('d/m/Y', $lbTs) ?>)?');">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="id" value="<?= (int)$lb['id'] ?>">
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 text-[11px] font-bold transition-colors cursor-pointer">
                                                    Hapus
                                                </button>
                                            </form>
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

</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 1: ATUR LOKASI CUSTOM PER PEGAWAI (INDIVIDU / MASSAL)
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalCustomPegawai" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 id="modal_peg_title" class="text-base font-extrabold text-slate-900">Atur Lokasi Absen Custom Pegawai</h3>
                <p class="text-xs text-slate-500">Tentukan Nama Kantor Tempat Absen, Latitude, dan Longitude khusus pegawai</p>
            </div>
            <button type="button" onclick="closeModal('modalCustomPegawai')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-absen-pegawai/pegawai/save') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="pegawai">
            <input type="hidden" name="pegawai_ids_csv" id="modal_peg_ids_csv" value="">

            <div id="modal_peg_select_wrapper">
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" id="modal_peg_select" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:border-primary-500">
                    <?php foreach ($allPegawaiDropdown as $pg):
                        $nm = $pg['nama'] . (!empty($pg['gelar']) ? ', ' . $pg['gelar'] : '');
                    ?>
                        <option value="<?= (int)$pg['id'] ?>">
                            <?= e($nm) ?> (<?= e($pg['unit_tugas'] ?: 'Umum') ?> — <?= e($pg['nama_kantor_efektif']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="modal_peg_bulk_banner" class="hidden p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-900">
                Mengatur lokasi custom untuk <span id="modal_peg_bulk_count">0</span> pegawai terpilih sekaligus.
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kantor / Tempat Absen <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_kantor" id="modal_peg_kantor" required placeholder="Contoh: Kampus 2 SD IT Bina Insan / Kantor Yayasan"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-primary-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Latitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="latitude" id="modal_peg_lat" required oninput="updateModalPegMap()"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Longitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="longitude" id="modal_peg_lng" required oninput="updateModalPegMap()"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-primary-500">
                </div>
            </div>

            <!-- Peta Mini Pilih Titik Koordinat -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold text-slate-500">Klik pada peta untuk memilih titik lokasi:</span>
                    <button type="button" onclick="ambilGpsKeInput('modal_peg_lat', 'modal_peg_lng', updateModalPegMap)" class="text-[11px] font-bold text-sky-600 hover:underline cursor-pointer">
                        📍 Ambil Titik GPS Saat Ini
                    </button>
                </div>
                <div id="mapPegawaiPicker" class="w-full h-48 rounded-2xl border border-slate-200 overflow-hidden"></div>
            </div>

            <!-- Pengaturan Durasi / Jadwal Hari & Tanggal (Misal: Senin, Selasa, Rabu Pekan Ini) -->
            <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <label class="text-xs font-extrabold text-amber-950">🗓️ Masa Berlaku Lokasi Custom:</label>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="radio" name="tipe_durasi" id="durasi_permanen" value="permanen" checked onchange="toggleJadwalCustomPegawai()" class="text-primary-600">
                            <span>Permanen (Sampai Di-reset)</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-900 cursor-pointer">
                            <input type="radio" name="tipe_durasi" id="durasi_terjadwal" value="terjadwal" onchange="toggleJadwalCustomPegawai()" class="text-amber-600">
                            <span>Hari / Tanggal Tertentu</span>
                        </label>
                    </div>
                </div>

                <div id="box_jadwal_custom_pegawai" class="hidden space-y-3 pt-2 border-t border-amber-200/80">
                    <!-- Tombol Preset Cepat -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="text-[11px] font-bold text-amber-800 mr-1">Pilih Cepat:</span>
                        <button type="button" onclick="presetJadwalPegawai('senin_rabu_pekan_ini')"
                                class="px-2.5 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-[11px] font-bold cursor-pointer">
                            ⚡ Senin - Rabu Pekan Ini
                        </button>
                        <button type="button" onclick="presetJadwalPegawai('hari_ini')"
                                class="px-2.5 py-1 rounded-lg bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 text-[11px] font-bold cursor-pointer">
                            Hanya Hari Ini
                        </button>
                        <button type="button" onclick="presetJadwalPegawai('pekan_ini')"
                                class="px-2.5 py-1 rounded-lg bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 text-[11px] font-bold cursor-pointer">
                            1 Pekan Ini (Senin - Sabtu)
                        </button>
                    </div>

                    <!-- Pilih Hari Aktif -->
                    <div>
                        <label class="block text-[11px] font-bold text-amber-900 mb-1">Berlaku Pada Hari (Centang hari absen di lokasi ini):</label>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $namaHari): ?>
                                <label class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-amber-200 text-xs font-bold text-slate-700 cursor-pointer hover:bg-amber-100">
                                    <input type="checkbox" name="hari_aktif[]" value="<?= $namaHari ?>" class="chk-hari-aktif rounded text-amber-600">
                                    <span><?= $namaHari ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Rentang Tanggal Mulai s/d Selesai -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Dari Tanggal (Mulai)</label>
                            <input type="date" name="tanggal_mulai" id="modal_peg_tgl_mulai"
                                   class="w-full px-3 py-1.5 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Sampai Tanggal (Selesai)</label>
                            <input type="date" name="tanggal_selesai" id="modal_peg_tgl_selesai"
                                   class="w-full px-3 py-1.5 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                        </div>
                    </div>

                    <p class="text-[11px] text-amber-800 leading-relaxed">
                        💡 <strong>Otomatis Kembali Semula:</strong> Di luar hari/tanggal yang dipilih (misal mulai hari Kamis), lokasi absen pegawai ini <strong>otomatis kembali ke Lokasi Semula / Unit</strong> tanpa perlu di-reset manual.
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Alasan Penugasan (Opsional)</label>
                <input type="text" name="keterangan" id="modal_peg_ket" placeholder="Contoh: Tugas Pelatihan / Pendampingan Senin-Rabu Pekan Ini"
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 outline-none focus:border-primary-500">
            </div>

            <!-- Pengaturan Jam Kedatangan & Pulang Khusus Pegawai Ini -->
            <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <label class="text-xs font-extrabold text-amber-950">⏰ Jam Kerja (Kedatangan & Pulang):</label>
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="radio" name="tipe_jam_kerja" id="jam_tipe_global" value="global" checked onchange="toggleJamCustomPegawai()" class="text-primary-600">
                            <span>Ikuti Jam Global</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-900 cursor-pointer">
                            <input type="radio" name="tipe_jam_kerja" id="jam_tipe_custom" value="custom" onchange="toggleJamCustomPegawai()" class="text-amber-600">
                            <span>Atur Jam Khusus</span>
                        </label>
                    </div>
                </div>

                <div id="box_jam_custom_pegawai" class="hidden pt-2 border-t border-amber-200/80">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Mulai Masuk</label>
                            <input type="time" name="jam_masuk_mulai" id="modal_peg_jam_masuk_mulai" value="<?= substr($jamMasukMulai, 0, 5) ?>"
                                   class="w-full px-3 py-1.5 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Batas Tepat Waktu <span class="text-rose-500">*</span></label>
                            <input type="time" name="jam_masuk_batas" id="modal_peg_jam_masuk_batas" value="<?= substr($jamMasukBatas, 0, 5) ?>"
                                   class="w-full px-3 py-1.5 rounded-xl border border-rose-300 bg-white text-xs font-bold text-rose-700 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Buka Check-out</label>
                            <input type="time" name="jam_pulang_mulai" id="modal_peg_jam_pulang_mulai" value="<?= substr($jamPulangMulai, 0, 5) ?>"
                                   class="w-full px-3 py-1.5 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                <span class="text-slate-500">Radius Presensi Berlaku (Global):</span>
                <strong class="text-emerald-700"><?= $radiusGlobal ?> Meter (Untuk Semua)</strong>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCustomPegawai')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Simpan Lokasi Pegawai
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL KHUSUS: ATUR JAM KERJA (KEDATANGAN & PULANG) PER PEGAWAI / MASSAL
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalCustomJamKerja" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 id="modal_jam_title" class="text-base font-extrabold text-slate-900">Atur Jam Kerja Pegawai</h3>
                <p class="text-xs text-slate-500">Tentukan jam kedatangan &amp; pulang sama setiap hari, atau berbeda-beda tiap hari (Senin – Sabtu)</p>
            </div>
            <button type="button" onclick="closeModal('modalCustomJamKerja')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-absen-pegawai/jam-kerja/save') ?>" onsubmit="return validateModalJamSubmit();" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="pegawai">
            <input type="hidden" name="pegawai_ids_csv" id="modal_jam_ids_csv" value="">

            <div id="modal_jam_select_wrapper">
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" id="modal_jam_select" onchange="onModalJamPegawaiSelectChange(this.value)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:border-amber-500">
                    <?php foreach ($allPegawaiDropdown as $pg):
                        $nm = $pg['nama'] . (!empty($pg['gelar']) ? ', ' . $pg['gelar'] : '');
                    ?>
                        <option value="<?= (int)$pg['id'] ?>">
                            <?= e($nm) ?> (<?= e($pg['unit_tugas'] ?: 'Umum') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="modal_jam_bulk_banner" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs font-bold text-amber-900">
                ⏰ Mengatur jam kerja untuk <span id="modal_jam_bulk_count">0</span> pegawai terpilih sekaligus.
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <label class="text-xs font-extrabold text-slate-800 block">Pilihan Pengaturan Jam Kerja:</label>
                <div class="space-y-2">
                    <label class="flex items-start gap-2.5 p-2 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-slate-300">
                        <input type="radio" name="mode_jam_kerja" id="mode_jam_global" value="global" onchange="toggleModalJamMode()" class="mt-0.5 text-primary-600">
                        <div>
                            <div class="text-xs font-bold text-slate-800">Ikuti Jam Kerja Global (Semua Pegawai)</div>
                            <div class="text-[11px] text-slate-500">
                                Senin–Jumat <?= substr($jamMasukBatas, 0, 5) ?><?= $sabtuKhususAktif ? ' &bull; Sabtu ' . e($sabtuJamMasukBatas) : '' ?> &bull; Pulang <?= substr($jamPulangMulai, 0, 5) ?> WITA
                            </div>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-2 rounded-xl border border-amber-300 bg-amber-50/50 cursor-pointer hover:border-amber-400">
                        <input type="radio" name="mode_jam_kerja" id="mode_jam_custom" value="custom" checked onchange="toggleModalJamMode()" class="mt-0.5 text-amber-600">
                        <div>
                            <div class="text-xs font-bold text-amber-900">Jam Kerja Khusus (Sama Setiap Hari)</div>
                            <div class="text-[11px] text-amber-700">Satu aturan jam masuk &amp; pulang khusus untuk seluruh hari kerja</div>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-2 rounded-xl border border-sky-300 bg-sky-50/60 cursor-pointer hover:border-sky-400">
                        <input type="radio" name="mode_jam_kerja" id="mode_jam_per_hari" value="per_hari" onchange="toggleModalJamMode()" class="mt-0.5 text-sky-600">
                        <div>
                            <div class="text-xs font-bold text-sky-950">🗓️ Jadwal Khusus Per Hari (Senin – Sabtu Berbeda-beda)</div>
                            <div class="text-[11px] text-sky-800">Misal Selasa 06:30, Senin 07:00, dll. Hari yang tidak dicentang otomatis mengikuti Jam Global.</div>
                        </div>
                    </label>
                </div>
            </div>

            <div id="box_inputs_jam_kerja" class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-amber-950 mb-1">Mulai Masuk</label>
                        <input type="time" name="jam_masuk_mulai" id="modal_jam_masuk_mulai" value="<?= substr($jamMasukMulai, 0, 5) ?>"
                               class="w-full px-3 py-2 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                        <span class="text-[10px] text-amber-700">Awal check-in</span>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-amber-950 mb-1">Batas Tepat Waktu <span class="text-rose-500">*</span></label>
                        <input type="time" name="jam_masuk_batas" id="modal_jam_masuk_batas" value="<?= substr($jamMasukBatas, 0, 5) ?>" required
                               class="w-full px-3 py-2 rounded-xl border border-rose-300 bg-white text-xs font-extrabold text-rose-700 outline-none">
                        <span class="text-[10px] text-rose-600 font-semibold">&gt; jam ini = Terlambat</span>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-amber-950 mb-1">Buka Check-out</label>
                        <input type="time" name="jam_pulang_mulai" id="modal_jam_pulang_mulai" value="<?= substr($jamPulangMulai, 0, 5) ?>"
                               class="w-full px-3 py-2 rounded-xl border border-amber-300 bg-white text-xs font-bold text-slate-800 outline-none">
                        <span class="text-[10px] text-amber-700">Awal check-out</span>
                    </div>
                </div>
            </div>

            <!-- Jadwal Per Hari (Senin - Sabtu) -->
            <div id="box_inputs_jam_harian" class="hidden p-3.5 rounded-2xl bg-sky-50/60 border border-sky-200 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-extrabold text-sky-950">Centang Hari yang Ingin Diatur Khusus:</span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="setAllHariJamHarian(true)" class="px-2.5 py-1 rounded-lg bg-white hover:bg-sky-600 hover:text-white border border-sky-300 text-[10px] font-bold text-sky-800 transition-colors cursor-pointer">
                            Centang Senin–Sabtu
                        </button>
                        <button type="button" onclick="setAllHariJamHarian(false)" class="px-2.5 py-1 rounded-lg bg-white hover:bg-slate-200 border border-slate-200 text-[10px] font-bold text-slate-600 transition-colors cursor-pointer">
                            Kosongkan
                        </button>
                    </div>
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    <?php
                    $hariListForm = [
                        1 => ['nama' => 'Senin',  'mulai' => $jamMasukMulai, 'batas' => $jamMasukBatas, 'pulang' => $jamPulangMulai],
                        2 => ['nama' => 'Selasa', 'mulai' => $jamMasukMulai, 'batas' => $jamMasukBatas, 'pulang' => $jamPulangMulai],
                        3 => ['nama' => 'Rabu',   'mulai' => $jamMasukMulai, 'batas' => $jamMasukBatas, 'pulang' => $jamPulangMulai],
                        4 => ['nama' => 'Kamis',  'mulai' => $jamMasukMulai, 'batas' => $jamMasukBatas, 'pulang' => $jamPulangMulai],
                        5 => ['nama' => 'Jumat',  'mulai' => $jamMasukMulai, 'batas' => $jamMasukBatas, 'pulang' => $jamPulangMulai],
                        6 => ['nama' => 'Sabtu',  'mulai' => ($sabtuKhususAktif ? $sabtuJamMasukMulai : $jamMasukMulai), 'batas' => ($sabtuKhususAktif ? $sabtuJamMasukBatas : $jamMasukBatas), 'pulang' => ($sabtuKhususAktif ? $sabtuJamPulangMulai : $jamPulangMulai)],
                    ];
                    foreach ($hariListForm as $hk => $hDef): ?>
                        <div id="row_hari_jam_<?= $hk ?>" class="grid grid-cols-12 items-center gap-2 p-2 rounded-xl border border-slate-200 bg-white/80 opacity-65 transition-all">
                            <label class="col-span-12 sm:col-span-3 inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="hari_custom[<?= $hk ?>][aktif]" value="1"
                                       id="hari_jam_aktif_<?= $hk ?>"
                                       data-hari="<?= $hk ?>"
                                       onchange="refreshHariJamRowStyle(<?= $hk ?>)"
                                       class="chk-hari-jam-pegawai rounded text-sky-600 focus:ring-sky-500">
                                <span class="text-xs font-extrabold text-slate-800"><?= $hDef['nama'] ?></span>
                            </label>
                            <div class="col-span-4 sm:col-span-3">
                                <span class="block text-[9px] font-bold text-slate-400">Mulai</span>
                                <input type="time" name="hari_custom[<?= $hk ?>][jam_masuk_mulai]" id="hari_jam_mulai_<?= $hk ?>"
                                       value="<?= e($hDef['mulai']) ?>" data-default="<?= e($hDef['mulai']) ?>"
                                       class="w-full px-2 py-1 rounded-lg border border-slate-200 bg-white text-[11px] font-bold text-slate-800 outline-none">
                            </div>
                            <div class="col-span-4 sm:col-span-3">
                                <span class="block text-[9px] font-bold text-rose-600">Batas Masuk</span>
                                <input type="time" name="hari_custom[<?= $hk ?>][jam_masuk_batas]" id="hari_jam_batas_<?= $hk ?>"
                                       value="<?= e($hDef['batas']) ?>" data-default="<?= e($hDef['batas']) ?>"
                                       class="w-full px-2 py-1 rounded-lg border border-rose-300 bg-white text-[11px] font-extrabold text-rose-700 outline-none">
                            </div>
                            <div class="col-span-4 sm:col-span-3">
                                <span class="block text-[9px] font-bold text-slate-400">Pulang</span>
                                <input type="time" name="hari_custom[<?= $hk ?>][jam_pulang_mulai]" id="hari_jam_pulang_<?= $hk ?>"
                                       value="<?= e($hDef['pulang']) ?>" data-default="<?= e($hDef['pulang']) ?>"
                                       class="w-full px-2 py-1 rounded-lg border border-slate-200 bg-white text-[11px] font-bold text-slate-800 outline-none">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p class="text-[11px] text-sky-800 leading-relaxed">
                    💡 Hari yang <strong>tidak dicentang</strong> otomatis mengikuti pengaturan Jam Global (Senin–Jumat: <strong><?= e($jamMasukBatas) ?></strong><?= $sabtuKhususAktif ? ', Sabtu: <strong>' . e($sabtuJamMasukBatas) . '</strong>' : '' ?>).
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCustomJamKerja')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Simpan Jam Kerja
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════
     MODAL 2: ATUR LOKASI CUSTOM PER UNIT
     ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalCustomUnit" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Atur Lokasi Absen Custom Per Unit</h3>
                <p class="text-xs text-slate-500">Seluruh pegawai pada unit yang dipilih akan mengikuti lokasi kantor ini</p>
            </div>
            <button type="button" onclick="closeModal('modalCustomUnit')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form method="POST" action="<?= url('kelola-absen-pegawai/unit/save') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="unit">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Unit Tugas <span class="text-rose-500">*</span></label>
                <select name="unit_tugas" id="modal_unit_select" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:border-purple-500">
                    <?php foreach ($unitLokasiList as $ul): ?>
                        <option value="<?= e($ul['unit_tugas']) ?>">
                            <?= e($ul['unit_tugas']) ?> (<?= (int)$ul['total_pegawai'] ?> Pegawai)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kantor / Tempat Absen Unit <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_kantor" id="modal_unit_kantor" required placeholder="Contoh: Gedung SMP IT Bina Insan Palu"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 outline-none focus:border-purple-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Latitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="latitude" id="modal_unit_lat" required oninput="updateModalUnitMap()"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Longitude <span class="text-rose-500">*</span></label>
                    <input type="number" step="any" name="longitude" id="modal_unit_lng" required oninput="updateModalUnitMap()"
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-mono font-bold text-slate-900 outline-none focus:border-purple-500">
                </div>
            </div>

            <!-- Peta Mini Pilih Titik Koordinat Unit -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[11px] font-bold text-slate-500">Klik pada peta untuk memilih titik lokasi unit:</span>
                    <button type="button" onclick="ambilGpsKeInput('modal_unit_lat', 'modal_unit_lng', updateModalUnitMap)" class="text-[11px] font-bold text-sky-600 hover:underline cursor-pointer">
                        📍 Ambil Titik GPS Saat Ini
                    </button>
                </div>
                <div id="mapUnitPicker" class="w-full h-48 rounded-2xl border border-slate-200 overflow-hidden"></div>
            </div>

            <label class="flex items-center gap-2 p-3 rounded-xl bg-purple-50 border border-purple-200 cursor-pointer">
                <input type="checkbox" name="overwrite_custom_pegawai" value="1" checked class="rounded text-purple-600">
                <span class="text-xs font-semibold text-purple-900">Terapkan ke seluruh pegawai di unit ini (termasuk yang sebelumnya diatur custom individu)</span>
            </label>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalCustomUnit')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm cursor-pointer">
                    Simpan Lokasi Unit
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ═════════════════════════════════════════════════════════════════════
     MODAL 4: UBAH LOG PRESENSI HARIAN PEGAWAI (TEPAT WAKTU / TERLAMBAT / DLL)
     ═════════════════════════════════════════════════════════════════════ -->
<div id="modalEditLogPresensi" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 my-8">
        <!-- Modal Header -->
        <div class="flex items-start justify-between pb-4 border-b border-slate-100">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200 mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-600"></span>
                    Edit Presensi Harian
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Ubah Log Presensi Pegawai</h3>
                <p class="text-xs text-slate-500">Sesuaikan jam masuk, status kehadiran (Tepat Waktu / Terlambat), atau alasan gagal absen.</p>
            </div>
            <button type="button" onclick="closeModal('modalEditLogPresensi')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Info Pegawai Card -->
        <div class="my-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
            <div>
                <h4 id="editlog_nama_display" class="font-extrabold text-sm text-slate-900">Nama Pegawai</h4>
                <p id="editlog_unit_display" class="text-xs text-slate-500 mt-0.5">Unit Tugas • Jabatan</p>
            </div>
            <div class="text-right shrink-0">
                <span id="editlog_tanggal_display" class="inline-flex px-2.5 py-1 rounded-xl text-xs font-bold bg-white text-slate-700 border border-slate-200 shadow-xs">
                    Tanggal
                </span>
                <div class="text-[10px] text-slate-400 mt-1">
                    Batas Jam: <strong id="editlog_batas_display" class="text-rose-600 font-mono">07:30 WITA</strong>
                </div>
            </div>
        </div>

        <!-- Form Update Log -->
        <form method="POST" action="<?= url('kelola-absen-pegawai/log/update') ?>" class="space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" id="editlog_return_tab" value="rekap">
            <input type="hidden" name="rekap_view" id="editlog_rekap_view" value="harian">
            <input type="hidden" name="pegawai_id" id="editlog_pegawai_id" value="">
            <input type="hidden" name="tanggal" id="editlog_tanggal" value="">

            <!-- Preset Cepat Status Kehadiran -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Cepat Status:</label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="setEditLogStatusPreset('Hadir')" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-colors cursor-pointer">
                        🟢 Tepat Waktu (07:15)
                    </button>
                    <button type="button" onclick="setEditLogStatusPreset('Terlambat')" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors cursor-pointer">
                        🟡 Terlambat (07:45)
                    </button>
                    <button type="button" onclick="setEditLogStatusPreset('Izin')" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 transition-colors cursor-pointer">
                        🔵 Izin
                    </button>
                    <button type="button" onclick="setEditLogStatusPreset('Sakit')" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 transition-colors cursor-pointer">
                        🟣 Sakit
                    </button>
                    <button type="button" onclick="setEditLogStatusPreset('Cuti')" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 transition-colors cursor-pointer">
                        🏖️ Cuti
                    </button>
                    <button type="button" onclick="setEditLogStatusPreset('Alpha')" class="px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors cursor-pointer">
                        🔴 Alpa
                    </button>
                </div>
            </div>

            <!-- Status Kehadiran Dropdown -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehadiran <span class="text-rose-500">*</span></label>
                <select name="status" id="editlog_status" required onchange="handleEditLogStatusChange(this.value)"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                    <option value="Hadir">Hadir (Tepat Waktu)</option>
                    <option value="Terlambat">Hadir Terlambat</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Cuti">Cuti</option>
                    <option value="Alpha">Alpa (Tanpa Keterangan)</option>
                    <option value="Dinas Luar">Tugas / Dinas Luar</option>
                </select>
            </div>

            <!-- Jam Masuk & Jam Pulang -->
            <div class="grid grid-cols-2 gap-3" id="editlog_jam_wrapper">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Jam Kedatangan (Check-in)
                    </label>
                    <input type="time" name="jam_masuk" id="editlog_jam_masuk"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                    <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika Izin/Sakit/Alpa</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Jam Pulang (Check-out)
                    </label>
                    <input type="time" name="jam_pulang" id="editlog_jam_pulang"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono font-bold text-slate-800 outline-none focus:bg-white focus:border-primary-500">
                    <p class="text-[10px] text-slate-400 mt-1">Opsional jam kepulangan</p>
                </div>
            </div>

            <!-- Nama Kantor Tempat Absen -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kantor / Tempat Absen</label>
                <input type="text" name="nama_kantor_absen" id="editlog_nama_kantor" placeholder="Contoh: Kampus Utama SIT Bina Insan Palu"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-800 outline-none focus:bg-white focus:border-primary-500">
            </div>

            <!-- Keterangan / Catatan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Alasan Perubahan</label>
                <input type="text" name="keterangan" id="editlog_keterangan" placeholder="Contoh: Gagal absen karena kendala GPS / Absen manual admin"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-800 outline-none focus:bg-white focus:border-primary-500">
            </div>

            <!-- Modal Actions -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                <div id="editlog_delete_wrapper">
                    <button type="button" onclick="submitDeleteCurrentLog()" id="btn_delete_log"
                            class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 transition-all cursor-pointer">
                        Hapus / Reset Log
                    </button>
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" onclick="closeModal('modalEditLogPresensi')"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 shadow-md shadow-primary-600/20 transition-all cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Delete Form -->
        <form id="formDeleteLogPresensi" method="POST" action="<?= url('kelola-absen-pegawai/log/delete') ?>" class="hidden">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" id="dellog_return_tab" value="rekap">
            <input type="hidden" name="rekap_view" id="dellog_rekap_view" value="harian">
            <input type="hidden" name="pegawai_id" id="dellog_pegawai_id" value="">
            <input type="hidden" name="tanggal" id="dellog_tanggal" value="">
        </form>
    </div>
</div>

<!-- Modal Input Absen Manual (Untuk Pegawai yang Lupa / Kendala / Terlambat Melapor) -->
<div id="modalAbsenManual" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-start justify-between pb-4 border-b border-slate-100">
            <div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 mb-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                    Input Absen Manual
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Catat Presensi Pegawai Secara Manual</h3>
                <p class="text-xs text-slate-500">Gunakan form ini jika pegawai terlambat melapor atau mengalami kendala tidak bisa absen di HP.</p>
            </div>
            <button type="button" onclick="closeModal('modalAbsenManual')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="<?= url('kelola-absen-pegawai/log/update') ?>" class="space-y-4 mt-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="rekap">
            <input type="hidden" name="rekap_view" id="manual_rekap_view" value="<?= e($currentRekapView ?? 'periode') ?>">

            <!-- Pilih Pegawai (dengan pencarian cepat) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <input type="text" id="manual_pegawai_search" oninput="filterManualPegawaiOptions(this.value)" placeholder="Ketik nama / NIY pegawai untuk filter cepat..."
                       class="w-full px-3.5 py-2 mb-1.5 rounded-xl border border-slate-200 bg-white text-xs text-slate-800 outline-none focus:border-emerald-500">
                <select name="pegawai_id" id="manual_pegawai_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-emerald-500">
                    <option value="">-- Pilih Pegawai --</option>
                    <?php foreach (($allPegawaiDropdown ?? []) as $pgItem):
                        $pgNama = trim((string)($pgItem['nama'] ?? '')) . (!empty($pgItem['gelar']) ? ', ' . $pgItem['gelar'] : '');
                        $pgUnit = $pgItem['unit_tugas'] ?: 'Yayasan';
                        $pgNiy  = $pgItem['niy'] ?: '-';
                    ?>
                        <option value="<?= (int)$pgItem['id'] ?>" data-search="<?= e(strtolower($pgNama . ' ' . $pgNiy . ' ' . $pgUnit)) ?>">
                            <?= e($pgNama) ?> (<?= e($pgUnit) ?> • NIY: <?= e($pgNiy) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tanggal & Status -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Absen <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal" id="manual_tanggal" value="<?= e($targetDate ?? date('Y-m-d')) ?>" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehadiran <span class="text-rose-500">*</span></label>
                    <select name="status" id="manual_status" required onchange="handleManualAbsenStatusChange(this.value)"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-emerald-500">
                        <option value="Hadir">Hadir (Tepat Waktu)</option>
                        <option value="Terlambat">Hadir Terlambat</option>
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Dinas Luar">Tugas / Dinas Luar</option>
                        <option value="Alpha">Alpa (Tanpa Keterangan)</option>
                    </select>
                </div>
            </div>

            <!-- Preset Cepat -->
            <div class="flex flex-wrap gap-1.5">
                <button type="button" onclick="setManualAbsenStatusPreset('Hadir')" class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition-colors cursor-pointer">🟢 Hadir (07:15)</button>
                <button type="button" onclick="setManualAbsenStatusPreset('Terlambat')" class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors cursor-pointer">🟡 Terlambat (07:45)</button>
                <button type="button" onclick="setManualAbsenStatusPreset('Izin')" class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 transition-colors cursor-pointer">🔵 Izin</button>
                <button type="button" onclick="setManualAbsenStatusPreset('Sakit')" class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 transition-colors cursor-pointer">🟣 Sakit</button>
                <button type="button" onclick="setManualAbsenStatusPreset('Dinas Luar')" class="px-2.5 py-1 rounded-xl text-[11px] font-bold bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 transition-colors cursor-pointer">💼 Tugas</button>
            </div>

            <!-- Jam Masuk & Jam Pulang -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Masuk</label>
                    <input type="time" name="jam_masuk" id="manual_jam_masuk" value="07:15"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono font-bold text-slate-800 outline-none focus:bg-white focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Pulang</label>
                    <input type="time" name="jam_pulang" id="manual_jam_pulang" value="15:00"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-mono font-bold text-slate-800 outline-none focus:bg-white focus:border-emerald-500">
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Alasan Absen Manual</label>
                <input type="text" name="keterangan" id="manual_keterangan" value="Absen manual admin (kendala absen / laporan terlambat)"
                       placeholder="Contoh: Kendala GPS / HP bermasalah / laporan terlambat"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-800 outline-none focus:bg-white focus:border-emerald-500">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modalAbsenManual')"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                    Simpan Absen Manual
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Catatan Keterlambatan (Evaluasi & Tindak Lanjut) -->
<!-- Modal Atur Nomor Urut Custom Per Unit -->
<?php
$urutanUnitOptions = [];
if (!empty($effectiveUserUnit)) {
    $urutanUnitOptions = [$effectiveUserUnit];
} else {
    $seenU = [];
    foreach (($unitLokasiList ?? []) as $ul) {
        $uName = trim((string)($ul['unit_tugas'] ?? ''));
        if ($uName !== '' && !isset($seenU[$uName])) {
            $seenU[$uName] = true;
            $urutanUnitOptions[] = $uName;
        }
    }
    foreach (['PAUD', 'SD', 'SMP', 'SMA', 'Yayasan', 'Staff'] as $defU) {
        if (!isset($seenU[$defU])) {
            $seenU[$defU] = true;
            $urutanUnitOptions[] = $defU;
        }
    }
}
$urutanDefaultUnit = !empty($filters['unit_tugas']) ? $filters['unit_tugas'] : ($urutanUnitOptions[0] ?? 'Yayasan');
$pegawaiForUrutan = [];
foreach (($allPegawaiDropdown ?? []) as $pg) {
    $pgUnit = trim((string)($pg['unit_tugas'] ?? '')) ?: 'Yayasan';
    $pgJab = trim((string)($pg['jabatan'] ?? ''));
    $pgJenis = trim((string)($pg['jenis_pegawai'] ?? ''));
    $isG = (strcasecmp($pgJenis, 'Guru') === 0) || (stripos($pgJab, 'guru') !== false) || (stripos($pgJab, 'wali kelas') !== false);
    $pgNama = trim((string)($pg['nama'] ?? '')) . (!empty($pg['gelar']) ? ', ' . $pg['gelar'] : '');
    $pegawaiForUrutan[] = [
        'id'         => (int)$pg['id'],
        'nama'       => $pgNama,
        'niy'        => $pg['niy'] ?: '-',
        'unit_tugas' => $pgUnit,
        'jabatan'    => $pgJab ?: ($isG ? 'Guru' : 'Support System'),
        'is_guru'    => $isG,
    ];
}
?>
<div id="modalUrutanUnit" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="relative bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Atur Nomor Urut Custom Per Unit</h3>
                <p class="text-xs text-slate-500 mt-0.5">Urutkan tampilan pegawai di Rekap Periode, Matriks, dan Cetak PDF sesuai nomor urut masing-masing unit.</p>
            </div>
            <button type="button" onclick="closeModal('modalUrutanUnit')" class="p-1.5 rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="<?= url('kelola-absen-pegawai/urutan-unit/save') ?>" class="mt-4 space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="rekap_view" id="urutan_rekap_view" value="<?= e($currentRekapView ?? 'periode') ?>">
            <input type="hidden" name="start_date" value="<?= e($filters['start_date'] ?? '') ?>">
            <input type="hidden" name="end_date" value="<?= e($filters['end_date'] ?? '') ?>">

            <div class="flex flex-wrap items-center justify-between gap-2.5 bg-slate-50 p-3 rounded-2xl border border-slate-200">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-700">Pilih Unit:</label>
                    <?php if (!empty($effectiveUserUnit)): ?>
                        <input type="hidden" name="unit_tugas" id="urutan_unit_select" value="<?= e($effectiveUserUnit) ?>">
                        <span class="px-3 py-1.5 rounded-xl bg-sky-100 text-sky-800 text-xs font-bold"><?= e($effectiveUserUnit) ?></span>
                    <?php else: ?>
                        <select name="unit_tugas" id="urutan_unit_select" onchange="renderUrutanUnitList()"
                                class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-800 outline-none focus:border-indigo-500">
                            <?php foreach ($urutanUnitOptions as $uo): ?>
                                <option value="<?= e($uo) ?>" <?= strcasecmp($urutanDefaultUnit, $uo) === 0 ? 'selected' : '' ?>><?= e($uo) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="autoNumberUrutanUnit()"
                            class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold cursor-pointer">
                        Isi Otomatis 1, 2, 3...
                    </button>
                    <button type="button" onclick="resetUrutanUnitInputs()"
                            class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-600 border border-slate-200 text-xs font-bold cursor-pointer">
                        Kosongkan
                    </button>
                </div>
            </div>

            <div id="urutan_unit_list" class="max-h-96 overflow-y-auto divide-y divide-slate-100 border border-slate-200 rounded-2xl"></div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalUrutanUnit')" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-600 cursor-pointer">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm cursor-pointer">Simpan Urutan Unit</button>
            </div>
        </form>
    </div>
</div>

<script>
const urutanPegawaiData = <?= json_encode($pegawaiForUrutan, JSON_UNESCAPED_UNICODE) ?>;
const urutanSavedMap = <?= json_encode($urutanUnitMap ?? [], JSON_UNESCAPED_UNICODE) ?>;

function openModalUrutanUnit(viewMode) {
    const viewInp = document.getElementById('urutan_rekap_view');
    if (viewInp && viewMode) viewInp.value = viewMode;
    renderUrutanUnitList();
    openModal('modalUrutanUnit');
}

function renderUrutanUnitList() {
    const sel = document.getElementById('urutan_unit_select');
    const listEl = document.getElementById('urutan_unit_list');
    if (!sel || !listEl) return;
    const unit = sel.value || '';
    const saved = urutanSavedMap[unit] || {};

    const items = urutanPegawaiData
        .filter(p => p.unit_tugas.toLowerCase() === unit.toLowerCase())
        .sort((a, b) => {
            if (a.is_guru !== b.is_guru) return a.is_guru ? -1 : 1;
            const ua = parseInt(saved[a.id] || 999999, 10);
            const ub = parseInt(saved[b.id] || 999999, 10);
            if (ua !== ub) return ua - ub;
            return a.nama.localeCompare(b.nama);
        });

    if (items.length === 0) {
        listEl.innerHTML = '<div class="py-10 text-center text-xs text-slate-400">Tidak ada pegawai aktif pada unit ' + unit + '.</div>';
        return;
    }

    listEl.innerHTML = items.map((p, idx) => {
        const val = saved[p.id] ? String(saved[p.id]) : '';
        const grpBadge = p.is_guru
            ? '<span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold">Guru</span>'
            : '<span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 text-[10px] font-bold">Support</span>';
        return `<div class="flex items-center justify-between gap-3 px-4 py-2.5 hover:bg-slate-50">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-900 truncate">${p.nama}</span>
                    ${grpBadge}
                </div>
                <div class="text-[11px] text-slate-400">${p.jabatan} &bull; NIY: ${p.niy}</div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-[11px] text-slate-400 font-semibold">No:</span>
                <input type="number" min="1" name="urutan[${p.id}]" value="${val}" placeholder="${idx + 1}"
                       class="urutan-unit-input w-20 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-center text-slate-900 outline-none focus:border-indigo-500">
            </div>
        </div>`;
    }).join('');
}

function autoNumberUrutanUnit() {
    document.querySelectorAll('.urutan-unit-input').forEach((inp, idx) => {
        inp.value = idx + 1;
    });
}

function resetUrutanUnitInputs() {
    document.querySelectorAll('.urutan-unit-input').forEach((inp) => {
        inp.value = '';
    });
}
</script>

<div id="modalCatatanTerlambat" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-8 animate-in fade-in zoom-in duration-150">
        <!-- Header -->
        <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-primary-600 to-primary-700 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/10 text-white flex items-center justify-center backdrop-blur-xs shadow-inner shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-white">Catatan Keterlambatan Pegawai</h3>
                    <p class="text-xs text-white/80">Evaluasi klarifikasi penyebab & tetapkan komitmen tindak lanjut pembinaan</p>
                </div>
            </div>
            <button type="button" onclick="closeModalCatatanTerlambat()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors cursor-pointer">
                ✕
            </button>
        </div>

        <!-- Info Pegawai Card -->
        <div class="p-5 bg-slate-50/70 border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-extrabold text-slate-900" id="catatan_nama_display">-</div>
                    <div class="text-xs text-slate-500 mt-0.5" id="catatan_unit_display">-</div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200" id="catatan_terlambat_badge">
                        Terlambat
                    </span>
                    <span class="px-2.5 py-1 rounded-xl text-xs font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200" id="catatan_jam_display">
                        00:00 WITA
                    </span>
                </div>
            </div>
        </div>

        <!-- Form Simpan Catatan -->
        <form method="POST" action="<?= url('kelola-absen-pegawai/catatan-terlambat/save') ?>" class="p-6 space-y-5">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="dashboard">
            <input type="hidden" name="tanggal" id="catatan_tanggal" value="">
            <input type="hidden" name="pegawai_id" id="catatan_pegawai_id" value="">

            <!-- 1. Evaluasi Keterlambatan -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-extrabold text-slate-800">
                        1. Evaluasi Penyebab Keterlambatan <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-slate-400">Pilih template cepat di bawah</span>
                </div>

                <!-- Template Chips Evaluasi -->
                <div class="flex flex-wrap gap-1.5 mb-2">
                    <button type="button" onclick="appendCatatanPreset('evaluasi', 'Macet parah dan kendala lalu lintas di perjalanan')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        🚗 Kendala Lalu Lintas
                    </button>
                    <button type="button" onclick="appendCatatanPreset('evaluasi', 'Ada keperluan keluarga mendesak sebelum berangkat')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        👨‍👩‍👧 Keperluan Keluarga
                    </button>
                    <button type="button" onclick="appendCatatanPreset('evaluasi', 'Cuaca hujan lebat dan genangan air di rute')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        🌧️ Hujan Lebat / Cuaca
                    </button>
                    <button type="button" onclick="appendCatatanPreset('evaluasi', 'Kondisi kesehatan kurang fit / singgah membeli obat')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        🏥 Kondisi Kurang Fit
                    </button>
                    <button type="button" onclick="appendCatatanPreset('evaluasi', 'Kendaraan bermotor mogok / ban bocor')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        🔧 Masalah Kendaraan
                    </button>
                    <button type="button" onclick="appendCatatanPreset('evaluasi', 'Kesiangan dan terlambat bangun pagi')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        ⏰ Kesiangan
                    </button>
                </div>

                <textarea name="evaluasi" id="catatan_evaluasi" rows="3" required placeholder="Tuliskan hasil klarifikasi / penyebab keterlambatan pegawai..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-800 leading-relaxed outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/10"></textarea>
            </div>

            <!-- 2. Tindak Lanjut / Solusi / Sanksi -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-extrabold text-slate-800">
                        2. Tindak Lanjut / Pembinaan / Solusi <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] text-slate-400">Pilih template cepat di bawah</span>
                </div>

                <!-- Template Chips Tindak Lanjut -->
                <div class="flex flex-wrap gap-1.5 mb-2">
                    <button type="button" onclick="appendCatatanPreset('tindak_lanjut', 'Diberikan teguran lisan dan arahan disiplin oleh pimpinan')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        🗣️ Teguran Lisan
                    </button>
                    <button type="button" onclick="appendCatatanPreset('tindak_lanjut', 'Pegawai berkomitmen berangkat lebih awal (minimal 20 menit sebelumnya)')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        ⏰ Komitmen Datang Awal
                    </button>
                    <button type="button" onclick="appendCatatanPreset('tindak_lanjut', 'Mengganti kekurangan jam kerja pada saat kepulangan sore hari')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        ⏳ Ganti Jam Pulang Sore
                    </button>
                    <button type="button" onclick="appendCatatanPreset('tindak_lanjut', 'Monitoring kedisiplinan khusus selama 1 pekan ke depan')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        📋 Monitoring 1 Pekan
                    </button>
                    <button type="button" onclick="appendCatatanPreset('tindak_lanjut', 'Keterlambatan dapat dimaklumi (force majeure/alasan mendesak valid)')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        ✅ Dispensasi / Dimaklumi
                    </button>
                    <button type="button" onclick="appendCatatanPreset('tindak_lanjut', 'Diberikan Surat Peringatan Pertama (SP 1) jika mengulang keterlambatan')" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 border border-slate-200 transition-colors cursor-pointer">
                        ⚠️ Peringatan SP 1
                    </button>
                </div>

                <textarea name="tindak_lanjut" id="catatan_tindak_lanjut" rows="3" required placeholder="Tuliskan tindak lanjut, komitmen perbaikan, atau solusi pembinaan yang disepakati..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-800 leading-relaxed outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/10"></textarea>
            </div>

            <!-- Evaluator Meta Info -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 flex items-center justify-between text-[11px] text-slate-500">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                    <span>Dicatat oleh: <strong class="text-slate-800"><?= e(Auth::user()['nama'] ?? (Auth::user()['username'] ?? 'Admin')) ?></strong></span>
                </div>
                <div id="catatan_last_updated" class="text-[10px] text-slate-400"></div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-3">
                <button type="button" onclick="submitResetCatatanTerlambat()" id="btn_reset_catatan"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 border border-rose-200 transition-all cursor-pointer hidden">
                    Reset Catatan
                </button>

                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" onclick="closeModalCatatanTerlambat()"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 shadow-md shadow-primary-600/20 transition-all cursor-pointer">
                        Simpan Catatan
                    </button>
                </div>
            </div>
        </form>

        <!-- Hidden Delete Catatan Form -->
        <form id="formDeleteCatatanTerlambat" method="POST" action="<?= url('kelola-absen-pegawai/catatan-terlambat/delete') ?>" class="hidden">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_tab" value="dashboard">
            <input type="hidden" name="pegawai_id" id="delcatatan_pegawai_id" value="">
            <input type="hidden" name="tanggal" id="delcatatan_tanggal" value="">
        </form>
    </div>
</div>
<script>
const DEFAULT_LAT = <?= json_encode($defaultLat) ?>;
const DEFAULT_LNG = <?= json_encode($defaultLng) ?>;
const DEFAULT_KANTOR = <?= json_encode($defaultKantor) ?>;
const GLOBAL_RADIUS = <?= json_encode($radiusGlobal) ?>;
const GLOBAL_JAM_MASUK_MULAI = <?= json_encode($jamMasukMulai) ?>;
const GLOBAL_JAM_MASUK_BATAS = <?= json_encode($jamMasukBatas) ?>;
const GLOBAL_JAM_PULANG_MULAI = <?= json_encode($jamPulangMulai) ?>;

let defaultMap = null, defaultMarker = null, defaultCircle = null;
let pegMap = null, pegMarker = null, pegCircle = null;
let unitMap = null, unitMarker = null, unitCircle = null;

function toggleSabtuKhususGlobal() {
    const chk = document.getElementById('chk_sabtu_khusus');
    const box = document.getElementById('box_sabtu_khusus_global');
    const batasEl = document.getElementById('sabtu_jam_masuk_batas');
    const active = !!(chk && chk.checked);
    if (box) box.classList.toggle('hidden', !active);
    if (batasEl) batasEl.required = active;
}

function setPeriodeAbsen(preset) {
    const awalEl = document.getElementById('periode_awal_absen');
    const akhirEl = document.getElementById('periode_akhir_absen');
    if (!awalEl || !akhirEl) return;

    const now = new Date();
    const y = now.getFullYear();
    const m = now.getMonth(); // 0-based

    if (preset === 'bulan_ini') {
        const first = new Date(y, m, 1);
        const last = new Date(y, m + 1, 0);
        awalEl.value = first.toISOString().slice(0, 10);
        akhirEl.value = last.toISOString().slice(0, 10);
    } else if (preset === 'bulan_lalu') {
        const first = new Date(y, m - 1, 1);
        const last = new Date(y, m, 0);
        awalEl.value = first.toISOString().slice(0, 10);
        akhirEl.value = last.toISOString().slice(0, 10);
    } else if (preset === 'semester_ini') {
        // Ganjil: Jul-Des, Genap: Jan-Jun
        const startMonth = m >= 6 ? 6 : 0;
        const endMonth = m >= 6 ? 11 : 5;
        const first = new Date(y, startMonth, 1);
        const last = new Date(y, endMonth + 1, 0);
        awalEl.value = first.toISOString().slice(0, 10);
        akhirEl.value = last.toISOString().slice(0, 10);
    }
}

function switchRekapSubView(mode) {
    const subPeriode = document.getElementById('subview-rekap-periode');
    const subHarian  = document.getElementById('subview-rekap-harian');
    const subMatrix  = document.getElementById('subview-rekap-matrix');
    const btnPeriode = document.getElementById('btn-rekap-sub-periode');
    const btnHarian  = document.getElementById('btn-rekap-sub-harian');
    const btnMatrix  = document.getElementById('btn-rekap-sub-matrix');

    const activeCls   = 'px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer bg-primary-600 text-white shadow-xs';
    const inactiveCls = 'px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer text-slate-600 hover:bg-slate-100';

    if (subPeriode) subPeriode.classList.toggle('hidden', mode !== 'periode');
    if (subHarian)  subHarian.classList.toggle('hidden', mode !== 'harian');
    if (subMatrix)  subMatrix.classList.toggle('hidden', mode !== 'matrix');
    if (btnPeriode) btnPeriode.className = (mode === 'periode') ? activeCls : inactiveCls;
    if (btnHarian)  btnHarian.className  = (mode === 'harian')  ? activeCls : inactiveCls;
    if (btnMatrix)  btnMatrix.className  = (mode === 'matrix')  ? activeCls : inactiveCls;

    const urlObj = new URL(window.location.href);
    urlObj.searchParams.set('tab', 'rekap');
    urlObj.searchParams.set('rekap_view', mode);
    window.history.replaceState({}, '', urlObj.toString());
}

function setPeriodeShortcut(preset) {
    const startInp = document.getElementById('rekap_start_date');
    const endInp   = document.getElementById('rekap_end_date');
    if (!startInp || !endInp) return;

    const now = new Date();
    if (preset === 'bulan_ini') {
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        startInp.value = `${y}-${m}-01`;
        endInp.value   = `${y}-${m}-${d}`;
    } else if (preset === 'bulan_lalu') {
        const prevMonthDate = new Date(now.getFullYear(), now.getMonth(), 0);
        const y = prevMonthDate.getFullYear();
        const m = String(prevMonthDate.getMonth() + 1).padStart(2, '0');
        const lastDay = String(prevMonthDate.getDate()).padStart(2, '0');
        startInp.value = `${y}-${m}-01`;
        endInp.value   = `${y}-${m}-${lastDay}`;
    }
}

function switchAbsenTab(tabName) {
    document.querySelectorAll('.absen-tab-panel').forEach(el => el.classList.add('hidden'));
    const panel = document.getElementById('absen-panel-' + tabName);
    if (panel) panel.classList.remove('hidden');

    document.querySelectorAll('.absen-tab-btn').forEach(btn => {
        const isMatch = btn.getAttribute('data-tab-btn') === tabName;
        btn.className = 'absen-tab-btn px-4 py-2.5 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer ' +
            (isMatch ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100');
    });

    document.querySelectorAll('.absen-sidebar-link').forEach(link => {
        const isMatch = link.getAttribute('data-sidebar-tab') === tabName;
        if (isMatch) {
            link.classList.add('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
            link.classList.remove('text-primary-200', 'font-medium', 'border-transparent');
        } else {
            link.classList.remove('bg-primary-600/30', 'text-white', 'font-semibold', 'shadow-sm', 'border-primary-500/40');
            link.classList.add('text-primary-200', 'font-medium', 'border-transparent');
        }
    });

    if (tabName === 'pengaturan') {
        setTimeout(initDefaultMap, 150);
    }

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

// ── Checkbox Bulk Selection ──
function getSelectedPegawaiIds() {
    const checks = document.querySelectorAll('.pegawai-row-check:checked');
    return Array.from(checks).map(c => c.value);
}

function updateSelectedPegawaiCount() {
    const ids = getSelectedPegawaiIds();
    const el = document.getElementById('selected_pegawai_count');
    if (el) el.textContent = ids.length;
    const elJam = document.getElementById('selected_pegawai_count_jam');
    if (elJam) elJam.textContent = ids.length;
}

function toggleSelectAllPegawai(master) {
    document.querySelectorAll('.pegawai-row-check').forEach(c => {
        c.checked = master.checked;
    });
    updateSelectedPegawaiCount();
}

function submitBulkResetPegawai() {
    const ids = getSelectedPegawaiIds();
    if (ids.length === 0) {
        alert('Silakan centang minimal 1 pegawai pada tabel terlebih dahulu.');
        return false;
    }
    if (!confirm('Reset lokasi absen ' + ids.length + ' pegawai terpilih kembali ke Lokasi Semula?')) {
        return false;
    }
    document.getElementById('bulk_reset_ids_csv').value = ids.join(',');
    return true;
}

function submitBulkResetJam() {
    const ids = getSelectedPegawaiIds();
    if (ids.length === 0) {
        alert('Silakan centang minimal 1 pegawai pada tabel terlebih dahulu.');
        return false;
    }
    if (!confirm('Kembalikan jam kerja ' + ids.length + ' pegawai terpilih mengikuti jam kerja global (semua pegawai)?')) {
        return false;
    }
    document.getElementById('bulk_reset_jam_ids_csv').value = ids.join(',');
    return true;
}

// ── Modal Custom Pegawai ──
function toggleJadwalCustomPegawai() {
    const isTerjadwal = document.getElementById('durasi_terjadwal').checked;
    const box = document.getElementById('box_jadwal_custom_pegawai');
    if (box) {
        box.classList.toggle('hidden', !isTerjadwal);
    }
}

function toggleJamCustomPegawai() {
    const isCustom = document.getElementById('jam_tipe_custom').checked;
    const box = document.getElementById('box_jam_custom_pegawai');
    if (box) {
        box.classList.toggle('hidden', !isCustom);
    }
    const batasEl = document.getElementById('modal_peg_jam_masuk_batas');
    if (batasEl) {
        batasEl.required = isCustom;
    }
}

function formatYmd(dateObj) {
    const y = dateObj.getFullYear();
    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
    const d = String(dateObj.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function presetJadwalPegawai(mode) {
    document.getElementById('durasi_terjadwal').checked = true;
    toggleJadwalCustomPegawai();

    const now = new Date();
    const dayOfWeek = now.getDay(); // 0=Minggu, 1=Senin, ...
    const diffToMonday = (dayOfWeek === 0 ? -6 : 1 - dayOfWeek);
    const monday = new Date(now);
    monday.setDate(now.getDate() + diffToMonday);

    const checks = document.querySelectorAll('.chk-hari-aktif');
    checks.forEach(c => c.checked = false);

    if (mode === 'senin_rabu_pekan_ini') {
        const wednesday = new Date(monday);
        wednesday.setDate(monday.getDate() + 2);
        document.getElementById('modal_peg_tgl_mulai').value = formatYmd(monday);
        document.getElementById('modal_peg_tgl_selesai').value = formatYmd(wednesday);
        checks.forEach(c => {
            if (['Senin', 'Selasa', 'Rabu'].includes(c.value)) c.checked = true;
        });
    } else if (mode === 'hari_ini') {
        const todayStr = formatYmd(now);
        document.getElementById('modal_peg_tgl_mulai').value = todayStr;
        document.getElementById('modal_peg_tgl_selesai').value = todayStr;
    } else if (mode === 'pekan_ini') {
        const saturday = new Date(monday);
        saturday.setDate(monday.getDate() + 5);
        document.getElementById('modal_peg_tgl_mulai').value = formatYmd(monday);
        document.getElementById('modal_peg_tgl_selesai').value = formatYmd(saturday);
        checks.forEach(c => {
            if (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'].includes(c.value)) c.checked = true;
        });
    }
}

function openModalCustomPegawai(pegawaiId = null, namaPegawai = '', namaKantor = '', lat = '', lng = '', tglMulai = '', tglSelesai = '', hariAktif = '', ket = '', tipeJam = 'global', jmMulai = '', jmBatas = '', jmPulang = '') {
    document.getElementById('modal_peg_ids_csv').value = '';
    document.getElementById('modal_peg_select_wrapper').classList.remove('hidden');
    document.getElementById('modal_peg_bulk_banner').classList.add('hidden');

    if (pegawaiId) {
        document.getElementById('modal_peg_select').value = String(pegawaiId);
        document.getElementById('modal_peg_title').textContent = 'Atur Lokasi & Jam: ' + namaPegawai;
    } else {
        document.getElementById('modal_peg_title').textContent = 'Atur Lokasi Absen Custom Pegawai';
    }

    document.getElementById('modal_peg_kantor').value = namaKantor || DEFAULT_KANTOR;
    document.getElementById('modal_peg_lat').value = lat || DEFAULT_LAT;
    document.getElementById('modal_peg_lng').value = lng || DEFAULT_LNG;
    document.getElementById('modal_peg_ket').value = ket || '';

    // Set jam kerja custom
    if (tipeJam === 'custom') {
        document.getElementById('jam_tipe_custom').checked = true;
    } else {
        document.getElementById('jam_tipe_global').checked = true;
    }
    toggleJamCustomPegawai();
    if (jmMulai) document.getElementById('modal_peg_jam_masuk_mulai').value = jmMulai;
    if (jmBatas) document.getElementById('modal_peg_jam_masuk_batas').value = jmBatas;
    if (jmPulang) document.getElementById('modal_peg_jam_pulang_mulai').value = jmPulang;

    // Set jadwal / durasi
    const checks = document.querySelectorAll('.chk-hari-aktif');
    checks.forEach(c => c.checked = false);

    if (tglMulai || tglSelesai || hariAktif) {
        document.getElementById('durasi_terjadwal').checked = true;
        document.getElementById('modal_peg_tgl_mulai').value = tglMulai || '';
        document.getElementById('modal_peg_tgl_selesai').value = tglSelesai || '';
        if (hariAktif) {
            const arr = hariAktif.split(',').map(s => s.trim());
            checks.forEach(c => {
                if (arr.includes(c.value)) c.checked = true;
            });
        }
    } else {
        document.getElementById('durasi_permanen').checked = true;
        document.getElementById('modal_peg_tgl_mulai').value = '';
        document.getElementById('modal_peg_tgl_selesai').value = '';
    }
    toggleJadwalCustomPegawai();

    const modal = document.getElementById('modalCustomPegawai');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(initModalPegMap, 150);
}

function openBulkCustomPegawai() {
    const ids = getSelectedPegawaiIds();
    if (ids.length === 0) {
        openModalCustomPegawai();
        return;
    }
    document.getElementById('modal_peg_ids_csv').value = ids.join(',');
    document.getElementById('modal_peg_select_wrapper').classList.add('hidden');
    document.getElementById('modal_peg_bulk_banner').classList.remove('hidden');
    document.getElementById('modal_peg_bulk_count').textContent = ids.length;
    document.getElementById('modal_peg_title').textContent = 'Atur Lokasi Custom (' + ids.length + ' Pegawai Terpilih)';
    document.getElementById('modal_peg_kantor').value = DEFAULT_KANTOR;
    document.getElementById('modal_peg_lat').value = DEFAULT_LAT;
    document.getElementById('modal_peg_lng').value = DEFAULT_LNG;
    document.getElementById('modal_peg_ket').value = '';

    document.getElementById('durasi_permanen').checked = true;
    document.getElementById('modal_peg_tgl_mulai').value = '';
    document.getElementById('modal_peg_tgl_selesai').value = '';
    document.querySelectorAll('.chk-hari-aktif').forEach(c => c.checked = false);
    toggleJadwalCustomPegawai();

    document.getElementById('jam_tipe_global').checked = true;
    document.getElementById('modal_peg_jam_masuk_mulai').value = GLOBAL_JAM_MASUK_MULAI;
    document.getElementById('modal_peg_jam_masuk_batas').value = GLOBAL_JAM_MASUK_BATAS;
    document.getElementById('modal_peg_jam_pulang_mulai').value = GLOBAL_JAM_PULANG_MULAI;
    toggleJamCustomPegawai();

    const modal = document.getElementById('modalCustomPegawai');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(initModalPegMap, 150);
}

// ── Modal Custom Jam Kerja (Per Pegawai, Per Hari & Massal) ──
<?php
$jamHarianByPegawai = [];
foreach ($allPegawaiDropdown as $pgRow) {
    if (!empty($pgRow['jam_harian'])) {
        $jamHarianByPegawai[(int)$pgRow['id']] = $pgRow['jam_harian'];
    }
}
?>
const PEGAWAI_JAM_HARIAN_MAP = <?= json_encode($jamHarianByPegawai ?: new stdClass()) ?>;

function refreshHariJamRowStyle(hk) {
    const chk = document.getElementById('hari_jam_aktif_' + hk);
    const row = document.getElementById('row_hari_jam_' + hk);
    const active = !!(chk && chk.checked);
    if (row) {
        row.classList.toggle('opacity-65', !active);
        row.classList.toggle('border-sky-300', active);
        row.classList.toggle('bg-white', active);
    }
}

function setAllHariJamHarian(checked) {
    for (let hk = 1; hk <= 6; hk++) {
        const chk = document.getElementById('hari_jam_aktif_' + hk);
        if (chk) {
            chk.checked = !!checked;
            refreshHariJamRowStyle(hk);
        }
    }
}

function populateHariJamInputs(scheduleMap) {
    const map = scheduleMap || {};
    for (let hk = 1; hk <= 6; hk++) {
        const cfg = map[hk] || map[String(hk)] || null;
        const chk = document.getElementById('hari_jam_aktif_' + hk);
        const mEl = document.getElementById('hari_jam_mulai_' + hk);
        const bEl = document.getElementById('hari_jam_batas_' + hk);
        const pEl = document.getElementById('hari_jam_pulang_' + hk);
        if (cfg && cfg.jam_masuk_batas) {
            if (chk) chk.checked = true;
            if (mEl) mEl.value = cfg.jam_masuk_mulai || mEl.dataset.default || GLOBAL_JAM_MASUK_MULAI;
            if (bEl) bEl.value = cfg.jam_masuk_batas || bEl.dataset.default || GLOBAL_JAM_MASUK_BATAS;
            if (pEl) pEl.value = cfg.jam_pulang_mulai || pEl.dataset.default || GLOBAL_JAM_PULANG_MULAI;
        } else {
            if (chk) chk.checked = false;
            if (mEl) mEl.value = mEl.dataset.default || GLOBAL_JAM_MASUK_MULAI;
            if (bEl) bEl.value = bEl.dataset.default || GLOBAL_JAM_MASUK_BATAS;
            if (pEl) pEl.value = pEl.dataset.default || GLOBAL_JAM_PULANG_MULAI;
        }
        refreshHariJamRowStyle(hk);
    }
}

function onModalJamPegawaiSelectChange(pegawaiId) {
    const sched = PEGAWAI_JAM_HARIAN_MAP[String(pegawaiId)] || null;
    populateHariJamInputs(sched);
}

function validateModalJamSubmit() {
    const perHariChecked = document.getElementById('mode_jam_per_hari') && document.getElementById('mode_jam_per_hari').checked;
    if (perHariChecked) {
        const anyDay = Array.from(document.querySelectorAll('.chk-hari-jam-pegawai')).some(c => c.checked);
        if (!anyDay) {
            alert('Centang minimal 1 hari pada Jadwal Khusus Per Hari.');
            return false;
        }
    }
    return true;
}

function toggleModalJamMode() {
    const isCustom = document.getElementById('mode_jam_custom').checked;
    const isPerHari = document.getElementById('mode_jam_per_hari') ? document.getElementById('mode_jam_per_hari').checked : false;
    const box = document.getElementById('box_inputs_jam_kerja');
    if (box) {
        box.classList.toggle('hidden', !isCustom);
    }
    const boxHarian = document.getElementById('box_inputs_jam_harian');
    if (boxHarian) {
        boxHarian.classList.toggle('hidden', !isPerHari);
    }
    const batasEl = document.getElementById('modal_jam_masuk_batas');
    if (batasEl) {
        batasEl.required = isCustom;
    }
}

function openModalCustomJamKerja(pegawaiId = null, namaPegawai = '', mode = 'custom', masukMulai = '', masukBatas = '', pulangMulai = '') {
    document.getElementById('modal_jam_ids_csv').value = '';
    document.getElementById('modal_jam_select_wrapper').classList.remove('hidden');
    document.getElementById('modal_jam_bulk_banner').classList.add('hidden');

    if (pegawaiId) {
        document.getElementById('modal_jam_select').value = String(pegawaiId);
        document.getElementById('modal_jam_title').textContent = 'Atur Jam Kerja: ' + namaPegawai;
    } else {
        document.getElementById('modal_jam_title').textContent = 'Atur Jam Kerja Pegawai';
        pegawaiId = document.getElementById('modal_jam_select').value || null;
    }

    if (mode === 'per_hari') {
        document.getElementById('mode_jam_per_hari').checked = true;
    } else if (mode === 'custom') {
        document.getElementById('mode_jam_custom').checked = true;
    } else {
        document.getElementById('mode_jam_global').checked = true;
    }
    toggleModalJamMode();

    document.getElementById('modal_jam_masuk_mulai').value = masukMulai || GLOBAL_JAM_MASUK_MULAI;
    document.getElementById('modal_jam_masuk_batas').value = masukBatas || GLOBAL_JAM_MASUK_BATAS;
    document.getElementById('modal_jam_pulang_mulai').value = pulangMulai || GLOBAL_JAM_PULANG_MULAI;

    populateHariJamInputs(pegawaiId ? (PEGAWAI_JAM_HARIAN_MAP[String(pegawaiId)] || null) : null);

    const modal = document.getElementById('modalCustomJamKerja');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function openBulkCustomJamKerja() {
    const ids = getSelectedPegawaiIds();
    if (ids.length === 0) {
        openModalCustomJamKerja();
        return;
    }
    document.getElementById('modal_jam_ids_csv').value = ids.join(',');
    document.getElementById('modal_jam_select_wrapper').classList.add('hidden');
    document.getElementById('modal_jam_bulk_banner').classList.remove('hidden');
    document.getElementById('modal_jam_bulk_count').textContent = ids.length;
    document.getElementById('modal_jam_title').textContent = 'Atur Jam Kerja (' + ids.length + ' Pegawai Terpilih)';
    document.getElementById('mode_jam_custom').checked = true;
    toggleModalJamMode();

    document.getElementById('modal_jam_masuk_mulai').value = GLOBAL_JAM_MASUK_MULAI;
    document.getElementById('modal_jam_masuk_batas').value = GLOBAL_JAM_MASUK_BATAS;
    document.getElementById('modal_jam_pulang_mulai').value = GLOBAL_JAM_PULANG_MULAI;
    populateHariJamInputs(null);

    const modal = document.getElementById('modalCustomJamKerja');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// ── Modal Custom Unit ──
function openModalCustomUnit(unitName = '', namaKantor = '', lat = '', lng = '') {
    if (unitName) {
        document.getElementById('modal_unit_select').value = unitName;
    }
    document.getElementById('modal_unit_kantor').value = namaKantor || ('Kampus ' + (unitName || 'Unit') + ' SIT Bina Insan Palu');
    document.getElementById('modal_unit_lat').value = lat || DEFAULT_LAT;
    document.getElementById('modal_unit_lng').value = lng || DEFAULT_LNG;

    const modal = document.getElementById('modalCustomUnit');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(initModalUnitMap, 150);
}

// ── Leaflet Maps Initialization ──
function initDefaultMap() {
    if (typeof L === 'undefined') return;
    const lat = parseFloat(document.getElementById('def_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('def_lng').value) || DEFAULT_LNG;
    const rad = parseInt(document.getElementById('def_radius').value) || GLOBAL_RADIUS;

    if (!defaultMap) {
        defaultMap = L.map('mapDefaultPicker').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '&copy; OpenStreetMap'
        }).addTo(defaultMap);

        defaultMarker = L.marker([lat, lng], { draggable: true }).addTo(defaultMap);
        defaultCircle = L.circle([lat, lng], { radius: rad, color: '#10b981', fillColor: '#10b981', fillOpacity: 0.2 }).addTo(defaultMap);

        defaultMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('def_lat').value = pos.lat.toFixed(7);
            document.getElementById('def_lng').value = pos.lng.toFixed(7);
            defaultCircle.setLatLng(pos);
        });

        defaultMap.on('click', function(e) {
            document.getElementById('def_lat').value = e.latlng.lat.toFixed(7);
            document.getElementById('def_lng').value = e.latlng.lng.toFixed(7);
            defaultMarker.setLatLng(e.latlng);
            defaultCircle.setLatLng(e.latlng);
        });
    } else {
        defaultMap.invalidateSize();
        updateDefaultMapMarker();
    }
}

function updateDefaultMapMarker() {
    if (!defaultMap || !defaultMarker) return;
    const lat = parseFloat(document.getElementById('def_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('def_lng').value) || DEFAULT_LNG;
    const rad = parseInt(document.getElementById('def_radius').value) || GLOBAL_RADIUS;
    defaultMarker.setLatLng([lat, lng]);
    defaultCircle.setLatLng([lat, lng]);
    defaultCircle.setRadius(rad);
    defaultMap.panTo([lat, lng]);
}

function initModalPegMap() {
    if (typeof L === 'undefined') return;
    const lat = parseFloat(document.getElementById('modal_peg_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('modal_peg_lng').value) || DEFAULT_LNG;

    if (!pegMap) {
        pegMap = L.map('mapPegawaiPicker').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 20 }).addTo(pegMap);
        pegMarker = L.marker([lat, lng], { draggable: true }).addTo(pegMap);
        pegCircle = L.circle([lat, lng], { radius: GLOBAL_RADIUS, color: '#0284c7', fillOpacity: 0.2 }).addTo(pegMap);

        pegMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('modal_peg_lat').value = pos.lat.toFixed(7);
            document.getElementById('modal_peg_lng').value = pos.lng.toFixed(7);
            pegCircle.setLatLng(pos);
        });

        pegMap.on('click', function(e) {
            document.getElementById('modal_peg_lat').value = e.latlng.lat.toFixed(7);
            document.getElementById('modal_peg_lng').value = e.latlng.lng.toFixed(7);
            pegMarker.setLatLng(e.latlng);
            pegCircle.setLatLng(e.latlng);
        });
    } else {
        pegMap.invalidateSize();
        updateModalPegMap();
    }
}

function updateModalPegMap() {
    if (!pegMap || !pegMarker) return;
    const lat = parseFloat(document.getElementById('modal_peg_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('modal_peg_lng').value) || DEFAULT_LNG;
    pegMarker.setLatLng([lat, lng]);
    pegCircle.setLatLng([lat, lng]);
    pegMap.panTo([lat, lng]);
}

function initModalUnitMap() {
    if (typeof L === 'undefined') return;
    const lat = parseFloat(document.getElementById('modal_unit_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('modal_unit_lng').value) || DEFAULT_LNG;

    if (!unitMap) {
        unitMap = L.map('mapUnitPicker').setView([lat, lng], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 20 }).addTo(unitMap);
        unitMarker = L.marker([lat, lng], { draggable: true }).addTo(unitMap);
        unitCircle = L.circle([lat, lng], { radius: GLOBAL_RADIUS, color: '#9333ea', fillOpacity: 0.2 }).addTo(unitMap);

        unitMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('modal_unit_lat').value = pos.lat.toFixed(7);
            document.getElementById('modal_unit_lng').value = pos.lng.toFixed(7);
            unitCircle.setLatLng(pos);
        });

        unitMap.on('click', function(e) {
            document.getElementById('modal_unit_lat').value = e.latlng.lat.toFixed(7);
            document.getElementById('modal_unit_lng').value = e.latlng.lng.toFixed(7);
            unitMarker.setLatLng(e.latlng);
            unitCircle.setLatLng(e.latlng);
        });
    } else {
        unitMap.invalidateSize();
        updateModalUnitMap();
    }
}

function updateModalUnitMap() {
    if (!unitMap || !unitMarker) return;
    const lat = parseFloat(document.getElementById('modal_unit_lat').value) || DEFAULT_LAT;
    const lng = parseFloat(document.getElementById('modal_unit_lng').value) || DEFAULT_LNG;
    unitMarker.setLatLng([lat, lng]);
    unitCircle.setLatLng([lat, lng]);
    unitMap.panTo([lat, lng]);
}

function ambilGpsKeInput(latInputId, lngInputId, callbackFn) {
    if (!navigator.geolocation) {
        alert('Browser Anda tidak mendukung fitur Geolokasi GPS.');
        return;
    }
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById(latInputId).value = pos.coords.latitude.toFixed(7);
        document.getElementById(lngInputId).value = pos.coords.longitude.toFixed(7);
        if (typeof callbackFn === 'function') callbackFn();
    }, function(err) {
        alert('Gagal mengambil titik GPS: ' + err.message);
    }, { enableHighAccuracy: true });
}

function ambilGpsUntukDefault() {
    ambilGpsKeInput('def_lat', 'def_lng', updateDefaultMapMarker);
}

document.addEventListener('DOMContentLoaded', function() {
    if ('<?= e($activeTab) ?>' === 'pengaturan') {
        setTimeout(initDefaultMap, 200);
    }
});

let currentEditLogData = null;

function openModalAbsenManual() {
    const searchInp = document.getElementById('manual_pegawai_search');
    if (searchInp) {
        searchInp.value = '';
        filterManualPegawaiOptions('');
    }
    const sel = document.getElementById('manual_pegawai_id');
    if (sel) sel.value = '';
    setManualAbsenStatusPreset('Hadir');
    const modal = document.getElementById('modalAbsenManual');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function filterManualPegawaiOptions(keyword) {
    const sel = document.getElementById('manual_pegawai_id');
    if (!sel) return;
    const q = (keyword || '').trim().toLowerCase();
    Array.from(sel.options).forEach((opt, idx) => {
        if (idx === 0) {
            opt.hidden = false;
            return;
        }
        const hay = opt.getAttribute('data-search') || opt.textContent.toLowerCase();
        opt.hidden = q !== '' && !hay.includes(q);
    });
}

function setManualAbsenStatusPreset(st) {
    const sel = document.getElementById('manual_status');
    if (sel) sel.value = st;
    handleManualAbsenStatusChange(st);
}

function handleManualAbsenStatusChange(st) {
    const jamIn = document.getElementById('manual_jam_masuk');
    const jamOut = document.getElementById('manual_jam_pulang');
    const ket = document.getElementById('manual_keterangan');
    if (!jamIn || !jamOut) return;

    if (st === 'Hadir') {
        if (!jamIn.value) jamIn.value = '07:15';
        if (!jamOut.value) jamOut.value = '15:00';
        if (ket && (!ket.value || ket.value.startsWith('Izin') || ket.value.startsWith('Sakit'))) {
            ket.value = 'Absen manual admin (kendala absen / laporan terlambat)';
        }
    } else if (st === 'Terlambat') {
        jamIn.value = '07:45';
        if (!jamOut.value) jamOut.value = '15:00';
    } else if (['Izin', 'Sakit', 'Cuti', 'Alpha'].includes(st)) {
        jamIn.value = '';
        jamOut.value = '';
    }
}

function openModalEditLogPresensi(data) {
    currentEditLogData = data;
    document.getElementById('editlog_pegawai_id').value = data.pegawai_id || '';
    document.getElementById('editlog_tanggal').value = data.tanggal || '';
    document.getElementById('editlog_return_tab').value = data.return_tab || 'rekap';
    if (document.getElementById('editlog_rekap_view')) {
        document.getElementById('editlog_rekap_view').value = data.rekap_view || 'harian';
    }
    document.getElementById('editlog_nama_display').textContent = data.nama + (data.niy ? ' (' + data.niy + ')' : '');
    document.getElementById('editlog_unit_display').textContent = (data.unit_tugas || '-') + ' • ' + (data.jabatan || '-');
    document.getElementById('editlog_tanggal_display').textContent = data.tanggal_formatted || data.tanggal || '';
    document.getElementById('editlog_batas_display').textContent = data.batas_jam ? data.batas_jam + ' WITA' : '07:30 WITA';

    let st = data.status || 'Hadir';
    if (st.toLowerCase() === 'alpa') st = 'Alpha';
    document.getElementById('editlog_status').value = st;

    const jamIn = data.jam_masuk ? data.jam_masuk.substring(0, 5) : '';
    document.getElementById('editlog_jam_masuk').value = jamIn;

    const jamOut = data.jam_pulang ? data.jam_pulang.substring(0, 5) : '';
    document.getElementById('editlog_jam_pulang').value = jamOut;

    document.getElementById('editlog_nama_kantor').value = data.nama_kantor || '';
    document.getElementById('editlog_keterangan').value = data.keterangan || '';

    // Jika pegawai belum pernah check-in dan status Hadir/Terlambat, bantu isi default jam
    if (!jamIn && st === 'Hadir') {
        document.getElementById('editlog_jam_masuk').value = '07:15';
        if (!jamOut) document.getElementById('editlog_jam_pulang').value = '15:00';
    } else if (!jamIn && st === 'Terlambat') {
        document.getElementById('editlog_jam_masuk').value = '07:45';
        if (!jamOut) document.getElementById('editlog_jam_pulang').value = '15:00';
    }

    const btnDel = document.getElementById('btn_delete_log');
    if (btnDel) {
        btnDel.style.display = data.has_log ? 'inline-block' : 'none';
    }

    const modal = document.getElementById('modalEditLogPresensi');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function handleEditLogStatusChange(st) {
    if (st.toLowerCase() === 'alpa') st = 'Alpha';
    const jamInp = document.getElementById('editlog_jam_masuk');
    const jamOut = document.getElementById('editlog_jam_pulang');
    const ketInp = document.getElementById('editlog_keterangan');
    const batasJam = (currentEditLogData && currentEditLogData.batas_jam) ? currentEditLogData.batas_jam : '07:30';

    if (st === 'Hadir') {
        if (!jamInp.value || jamInp.value > batasJam) {
            jamInp.value = '07:15';
        }
        if (!jamOut.value) jamOut.value = '15:00';
    } else if (st === 'Terlambat') {
        if (!jamInp.value || jamInp.value <= batasJam) {
            jamInp.value = '07:45';
        }
        if (!jamOut.value) jamOut.value = '15:00';
    } else if (['Izin', 'Sakit', 'Cuti', 'Alpha'].includes(st)) {
        jamInp.value = '';
        jamOut.value = '';
        if (st === 'Izin' && (!ketInp.value || ketInp.value.startsWith('Terlambat'))) {
            ketInp.value = 'Izin';
        } else if (st === 'Sakit' && (!ketInp.value || ketInp.value.startsWith('Terlambat'))) {
            ketInp.value = 'Sakit';
        } else if (st === 'Cuti' && (!ketInp.value || ketInp.value.startsWith('Terlambat'))) {
            ketInp.value = 'Cuti';
        } else if (st === 'Alpha' && (!ketInp.value || ketInp.value.startsWith('Terlambat'))) {
            ketInp.value = 'Alpa (Tanpa Keterangan)';
        }
    } else if (st === 'Dinas Luar') {
        if (!jamInp.value) jamInp.value = '07:30';
        if (!jamOut.value) jamOut.value = '15:00';
        if (!ketInp.value || ketInp.value.startsWith('Terlambat')) {
            ketInp.value = 'Dinas Luar';
        }
    }
}

function setEditLogStatusPreset(st) {
    document.getElementById('editlog_status').value = st;
    handleEditLogStatusChange(st);
}

function submitDeleteCurrentLog() {
    if (!currentEditLogData || !currentEditLogData.pegawai_id || !currentEditLogData.tanggal) return;
    if (!confirm('Hapus log presensi ' + currentEditLogData.nama + ' pada tanggal ' + currentEditLogData.tanggal + ' dan kembalikan ke status Belum Absen?')) {
        return;
    }
    document.getElementById('dellog_pegawai_id').value = currentEditLogData.pegawai_id;
    document.getElementById('dellog_tanggal').value = currentEditLogData.tanggal;
    document.getElementById('dellog_return_tab').value = currentEditLogData.return_tab || 'rekap';
    const delRekap = document.getElementById('dellog_rekap_view');
    if (delRekap) {
        delRekap.value = currentEditLogData.rekap_view || 'harian';
    }
    document.getElementById('formDeleteLogPresensi').submit();
}

let currentCatatanData = null;

function openModalCatatanTerlambat(data) {
    currentCatatanData = data;
    document.getElementById('catatan_pegawai_id').value = data.pegawai_id || '';
    document.getElementById('catatan_tanggal').value = data.tanggal || '';
    document.getElementById('delcatatan_pegawai_id').value = data.pegawai_id || '';
    document.getElementById('delcatatan_tanggal').value = data.tanggal || '';

    document.getElementById('catatan_nama_display').textContent = data.nama + (data.niy && data.niy !== '-' ? ' (' + data.niy + ')' : '');
    document.getElementById('catatan_unit_display').textContent = (data.unit_tugas || '-') + ' • ' + (data.jabatan || '-');
    document.getElementById('catatan_terlambat_badge').textContent = 'Terlambat +' + (data.menit_terlambat || '0') + ' Menit';
    document.getElementById('catatan_jam_display').textContent = 'Check-in: ' + (data.jam_masuk ? data.jam_masuk.substring(0, 5) : '-') + ' WITA (Batas: ' + (data.batas_jam || '-') + ' WITA)';

    document.getElementById('catatan_evaluasi').value = data.evaluasi || '';
    document.getElementById('catatan_tindak_lanjut').value = data.tindak_lanjut || '';

    const btnReset = document.getElementById('btn_reset_catatan');
    const lastUpdate = document.getElementById('catatan_last_updated');
    if (data.evaluasi || data.tindak_lanjut) {
        btnReset.classList.remove('hidden');
        if (data.evaluasi_oleh) {
            lastUpdate.textContent = 'Terakhir diisi oleh ' + data.evaluasi_oleh;
        } else {
            lastUpdate.textContent = '';
        }
    } else {
        btnReset.classList.add('hidden');
        lastUpdate.textContent = '';
    }

    const modal = document.getElementById('modalCatatanTerlambat');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeModalCatatanTerlambat() {
    const modal = document.getElementById('modalCatatanTerlambat');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function appendCatatanPreset(field, text) {
    const el = document.getElementById('catatan_' + field);
    if (!el) return;
    const currentVal = el.value.trim();
    if (!currentVal) {
        el.value = text;
    } else {
        el.value = currentVal + '. ' + text;
    }
    el.focus();
}

function submitResetCatatanTerlambat() {
    if (!currentCatatanData || !currentCatatanData.pegawai_id || !currentCatatanData.tanggal) return;
    if (!confirm('Yakin ingin mereset/menghapus catatan evaluasi dan tindak lanjut untuk ' + currentCatatanData.nama + '?')) {
        return;
    }
    document.getElementById('formDeleteCatatanTerlambat').submit();
}
</script>
