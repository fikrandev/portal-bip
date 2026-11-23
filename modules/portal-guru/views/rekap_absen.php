<?php
/**
 * Rekap Presensi & Keterlambatan Guru - Portal Guru Mobile
 */
$summary = $rekap['summary'] ?? [];
$listTerlambat = $rekap['list_terlambat'] ?? [];
$listTidakAbsen = $rekap['list_tidak_absen'] ?? [];
$allLogs = $rekap['all_logs'] ?? [];
$pStart = $startDate ?? date('Y-m-01');
$pEnd = $endDate ?? date('Y-m-d');
?>

<!-- Header with Back Button -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile/absen') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-bold text-slate-800 text-base leading-tight">Rekap Presensi Saya</h2>
            <p class="text-[10px] text-slate-400 font-medium">Monitoring Kehadiran & Keterlambatan</p>
        </div>
    </div>
    <a href="<?= url('mobile/absen') ?>" class="w-9 h-9 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center press-bounce" title="Buka Presensi GPS">
        <i data-lucide="map-pin" class="w-4 h-4"></i>
    </a>
</div>

<div class="p-4 space-y-4">

    <!-- 1. Filter Rentang Periode (Tanggal Awal & Tanggal Akhir) -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm">
                    📅
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-xs">Periode Rekap Presensi</h3>
                    <p class="text-[10px] text-slate-400">Atur tanggal awal & akhir rekap</p>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                Web Config
            </span>
        </div>

        <form action="<?= url('mobile/rekap-absen') ?>" method="GET" class="space-y-2.5" id="form-filter-rekap-absen">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" id="rekap_start_date" value="<?= e($pStart) ?>" required
                           class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 outline-none focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="rekap_end_date" value="<?= e($pEnd) ?>" required
                           class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 outline-none focus:border-primary-500">
                </div>
            </div>

            <!-- Preset Buttons & Submit -->
            <div class="flex items-center justify-between gap-1.5 pt-1">
                <div class="flex items-center gap-1 overflow-x-auto no-scrollbar">
                    <button type="button" onclick="setPresetRekapAbsen('bulan_ini')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-primary-50 text-[10px] font-bold text-slate-600 hover:text-primary-600 transition-colors">Bulan Ini</button>
                    <button type="button" onclick="setPresetRekapAbsen('bulan_lalu')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-primary-50 text-[10px] font-bold text-slate-600 hover:text-primary-600 transition-colors">Bulan Lalu</button>
                    <button type="button" onclick="setPresetRekapAbsen('pekan_ini')" class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-primary-50 text-[10px] font-bold text-slate-600 hover:text-primary-600 transition-colors">Pekan Ini</button>
                </div>
                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-sm press-bounce shrink-0">
                    Terapkan
                </button>
            </div>
        </form>

        <div class="text-[10px] text-slate-400 bg-slate-50 p-2 rounded-xl flex items-center justify-between">
            <span>Menampilkan data: <strong><?= date('d M Y', strtotime($pStart)) ?></strong> s/d <strong><?= date('d M Y', strtotime($pEnd)) ?></strong></span>
        </div>
    </div>

    <!-- 2. Hero KPI Cards: Ringkasan Presensi -->
    <div class="bg-gradient-to-br from-primary-600 to-indigo-700 rounded-3xl p-4 text-white shadow-lg shadow-primary-600/20 space-y-3 relative overflow-hidden">
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-semibold text-primary-200 uppercase tracking-wider">Persentase Kehadiran</span>
                <div class="text-3xl font-black tracking-tight mt-0.5">
                    <?= $summary['persen_kehadiran'] ?? 0 ?>%
                </div>
                <p class="text-[11px] text-primary-100 mt-0.5">
                    Total <strong><?= (int)($summary['total_hadir'] ?? 0) ?></strong> hadir dari <strong><?= (int)($summary['total_hari_kerja'] ?? 0) ?></strong> hari kerja
                    <?php if (!empty($summary['total_libur'])): ?>
                        &bull; (<?= (int)$summary['total_libur'] ?> hari libur dikecualikan)
                    <?php endif; ?>
                </p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-2xl shadow-inner">
                📊
            </div>
        </div>

        <!-- 4 Grid Stats -->
        <div class="grid grid-cols-4 gap-2 pt-1 border-t border-white/15 text-center relative z-10">
            <div class="bg-white/10 rounded-2xl p-2 backdrop-blur-xs">
                <span class="text-[9px] uppercase font-bold text-primary-200 block">Kerja</span>
                <span class="text-base font-extrabold text-white block mt-0.5"><?= (int)($summary['total_hari_kerja'] ?? 0) ?></span>
                <span class="text-[8px] text-primary-200">Hari</span>
            </div>
            <div class="bg-emerald-400/20 rounded-2xl p-2 backdrop-blur-xs border border-emerald-300/30">
                <span class="text-[9px] uppercase font-bold text-emerald-200 block">Tepat Waktu</span>
                <span class="text-base font-extrabold text-white block mt-0.5"><?= (int)($summary['hadir_tepat_waktu'] ?? 0) ?></span>
                <span class="text-[8px] text-emerald-200">Kali</span>
            </div>
            <div class="bg-amber-400/20 rounded-2xl p-2 backdrop-blur-xs border border-amber-300/30">
                <span class="text-[9px] uppercase font-bold text-amber-200 block">Terlambat</span>
                <span class="text-base font-extrabold text-white block mt-0.5"><?= (int)($summary['terlambat'] ?? 0) ?></span>
                <span class="text-[8px] text-amber-200"><?= (int)($summary['total_menit_terlambat'] ?? 0) ?>m</span>
            </div>
            <div class="bg-rose-400/20 rounded-2xl p-2 backdrop-blur-xs border border-rose-300/30">
                <span class="text-[9px] uppercase font-bold text-rose-200 block">Tidak Absen</span>
                <span class="text-base font-extrabold text-white block mt-0.5"><?= (int)($summary['tidak_absen'] ?? 0) ?></span>
                <span class="text-[8px] text-rose-200">Alpa</span>
            </div>
        </div>

        <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
    </div>

    <!-- 3. Segmented Tab Switcher: Kapan Terlambat vs Kapan Tidak Absen vs Semua -->
    <div class="flex p-1 bg-slate-200/70 rounded-2xl">
        <button type="button" onclick="switchRekapTab('terlambat')" id="btn-rekap-terlambat" class="flex-1 py-2 text-xs font-bold rounded-xl bg-white text-amber-700 shadow-sm transition-all flex items-center justify-center gap-1">
            <span>⚠️ Terlambat</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-amber-100 text-amber-800"><?= count($listTerlambat) ?></span>
        </button>
        <button type="button" onclick="switchRekapTab('alpa')" id="btn-rekap-alpa" class="flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1">
            <span>❌ Tidak Absen</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-rose-100 text-rose-800"><?= count($listTidakAbsen) ?></span>
        </button>
        <button type="button" onclick="switchRekapTab('semua')" id="btn-rekap-semua" class="flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1">
            <span>📋 Semua</span>
            <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-slate-100 text-slate-700"><?= count($allLogs) ?></span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: DAFTAR KAPAN SAJA TERLAMBAT                       -->
    <!-- ======================================================== -->
    <div id="panel-rekap-terlambat" class="space-y-2.5">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-extrabold text-slate-800 text-xs">Catatan Hari & Jam Terlambat</h3>
            <span class="text-[10px] text-amber-700 font-bold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                Total: <?= count($listTerlambat) ?> Kali (<?= (int)($summary['total_menit_terlambat'] ?? 0) ?> Menit)
            </span>
        </div>

        <?php if (!empty($listTerlambat)): ?>
            <?php foreach ($listTerlambat as $lt): ?>
            <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-amber-200/80 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-base shrink-0">
                            ⏳
                        </div>
                        <div>
                            <h4 class="text-xs font-extrabold text-slate-900 leading-tight">
                                <?= e($lt['hari']) ?>, <?= date('d M Y', strtotime($lt['tanggal'])) ?>
                            </h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">
                                Check-in: <strong class="text-slate-700"><?= e($lt['jam_masuk']) ?></strong> &bull; Pulang: <?= e($lt['jam_pulang']) ?>
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 shrink-0">
                        +<?= (int)$lt['menit_terlambat'] ?> Menit
                    </span>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-[10px] text-slate-500">
                    <span>📍 Lokasi: <?= e($lt['lokasi'] ?? 'Sekolah') ?> (<?= round($lt['jarak'] ?? 0) ?>m)</span>
                    <span class="text-amber-700 font-bold"><?= e($lt['keterangan']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-6 text-center shadow-sm border border-slate-100 space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto text-2xl font-bold">
                    ✓
                </div>
                <h4 class="font-extrabold text-slate-800 text-sm">Alhamdulillah, Tidak Ada Keterlambatan!</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto">
                    Seluruh presensi masuk Anda pada rentang tanggal ini tercatat tepat waktu sebelum batas toleransi.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: DAFTAR KAPAN SAJA TIDAK ABSEN (ALPA)              -->
    <!-- ======================================================== -->
    <div id="panel-rekap-alpa" class="space-y-2.5 hidden">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-extrabold text-slate-800 text-xs">Hari Kerja Tanpa Presensi (Alpa)</h3>
            <span class="text-[10px] text-rose-700 font-bold bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200">
                Total: <?= count($listTidakAbsen) ?> Hari
            </span>
        </div>

        <?php if (!empty($listTidakAbsen)): ?>
            <?php foreach ($listTidakAbsen as $la): ?>
            <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-rose-200 space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-base shrink-0">
                            ❌
                        </div>
                        <div>
                            <h4 class="text-xs font-extrabold text-slate-900 leading-tight">
                                <?= e($la['hari']) ?>, <?= date('d M Y', strtotime($la['tanggal'])) ?>
                            </h4>
                            <p class="text-[10px] text-rose-600 font-medium mt-0.5">
                                <?= e($la['keterangan']) ?>
                            </p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300 shrink-0">
                        Tidak Absen
                    </span>
                </div>

                <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-[10px]">
                    <span class="text-slate-400">Status: Tidak ada rekaman GPS / Log presensi</span>
                    <a href="<?= url('mobile/izin') ?>" class="font-bold text-primary-600 hover:underline">
                        Ajukan Izin &rarr;
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-6 text-center shadow-sm border border-slate-100 space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto text-2xl font-bold">
                    ✓
                </div>
                <h4 class="font-extrabold text-slate-800 text-sm">Masya Allah, Kehadiran Lengkap!</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto">
                    Tidak ada hari kerja yang terlewat tanpa presensi pada rentang tanggal ini.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: SELURUH RIWAYAT PRESENSI PERIODE                  -->
    <!-- ======================================================== -->
    <div id="panel-rekap-semua" class="space-y-2.5 hidden">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-extrabold text-slate-800 text-xs">Seluruh Log Kehadiran Periode</h3>
            <span class="text-[10px] text-slate-500 font-bold">
                <?= count($allLogs) ?> Hari Dievaluasi
            </span>
        </div>

        <div class="space-y-2">
            <?php foreach ($allLogs as $log): ?>
            <?php 
                $badgeColor = match($log['status'] ?? '') {
                    'Hadir' => 'emerald',
                    'Terlambat' => 'amber',
                    'Tidak Absen' => 'rose',
                    'Libur' => 'sky',
                    default => 'indigo'
                };
            ?>
            <div class="flex items-center justify-between p-3 rounded-2xl bg-white border border-slate-100 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-<?= $badgeColor ?>-100 text-<?= $badgeColor ?>-700 flex items-center justify-center font-bold text-xs shrink-0">
                        <?= ($log['status'] ?? '') === 'Libur' ? '🏖️' : substr($log['hari'] ?? 'S', 0, 1) ?>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">
                            <?= e($log['hari'] ?? '') ?>, <?= date('d M Y', strtotime($log['tanggal'])) ?>
                        </p>
                        <p class="text-[10px] text-slate-400">
                            <?php if (($log['status'] ?? '') === 'Libur'): ?>
                                <?= e($log['keterangan'] ?? 'Hari Libur') ?>
                            <?php else: ?>
                                Masuk: <strong><?= e($log['jam_masuk']) ?></strong> &bull; Pulang: <?= e($log['jam_pulang']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-<?= $badgeColor ?>-50 text-<?= $badgeColor ?>-700 border border-<?= $badgeColor ?>-200 shrink-0">
                    <?= e($log['status']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
function switchRekapTab(tab) {
    const panels = ['terlambat', 'alpa', 'semua'];
    panels.forEach(p => {
        const pEl = document.getElementById('panel-rekap-' + p);
        const bEl = document.getElementById('btn-rekap-' + p);
        if (p === tab) {
            if (pEl) pEl.classList.remove('hidden');
            if (bEl) {
                bEl.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-700 shadow-sm transition-all flex items-center justify-center gap-1';
            }
        } else {
            if (pEl) pEl.classList.add('hidden');
            if (bEl) {
                bEl.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1';
            }
        }
    });
}

function setPresetRekapAbsen(preset) {
    const sEl = document.getElementById('rekap_start_date');
    const eEl = document.getElementById('rekap_end_date');
    if (!sEl || !eEl) return;

    const now = new Date();
    const y = now.getFullYear();
    const m = now.getMonth();

    if (preset === 'bulan_ini') {
        const first = new Date(y, m, 1);
        const last = new Date(y, m + 1, 0);
        sEl.value = first.toISOString().slice(0, 10);
        eEl.value = last.toISOString().slice(0, 10);
    } else if (preset === 'bulan_lalu') {
        const first = new Date(y, m - 1, 1);
        const last = new Date(y, m, 0);
        sEl.value = first.toISOString().slice(0, 10);
        eEl.value = last.toISOString().slice(0, 10);
    } else if (preset === 'pekan_ini') {
        const day = now.getDay() || 7; // Mon is 1
        const mon = new Date(now);
        mon.setDate(now.getDate() - day + 1);
        const sat = new Date(mon);
        sat.setDate(mon.getDate() + 5);
        sEl.value = mon.toISOString().slice(0, 10);
        eEl.value = sat.toISOString().slice(0, 10);
    }
    document.getElementById('form-filter-rekap-absen').submit();
}
</script>
