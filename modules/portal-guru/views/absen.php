<?php
/**
 * Absen Kehadiran & Geolokasi Screen
 * Pure GPS Attendance (No Camera needed) with Check-in Masuk and 15:00 device time-locked Absen Pulang.
 */
?>

<script>
    window.CUSTOM_SCHOOL_COORDS = {
        lat: <?= json_encode((float)($schoolCoords['lat'] ?? -0.8917)) ?>,
        lng: <?= json_encode((float)($schoolCoords['lng'] ?? 119.8707)) ?>,
        name: <?= json_encode((string)($schoolName ?? 'Kampus Utama SIT Bina Insan Palu')) ?>,
        radius: <?= json_encode((int)($schoolRadius ?? 10)) ?>
    };
    window.SERVER_ATTENDANCE_TODAY = <?= json_encode($todayAttendance ?? null) ?>;
    window.SERVER_JAM_PULANG_MULAI = <?= json_encode($jamPulangMulai ?? '15:00:00') ?>;
    window.SERVER_JAM_MASUK_BATAS = <?= json_encode($jamMasukBatas ?? '07:30:00') ?>;
    if (typeof getEffectiveSchoolCoords === 'function') {
        getEffectiveSchoolCoords();
    }
</script>

<!-- Header with Back Button -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <h2 class="font-bold text-slate-800 text-base">Presensi Geolokasi</h2>
    <a href="<?= url('mobile/rekap-absen') ?>" class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center press-bounce" title="Lihat Rekap Absensi">
        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
    </a>
</div>

<div class="p-4 space-y-3.5">

    <!-- 1. Compact Realtime Digital Clock & GPS Status Strip -->
    <div class="bg-gradient-to-r from-primary-600 to-indigo-700 rounded-2xl px-4 py-3 text-white shadow-lg shadow-primary-600/20 relative overflow-hidden">
        <div class="relative z-10 flex items-center justify-between gap-3">
            <div>
                <span class="inline-block text-[10px] font-semibold tracking-wider uppercase text-primary-100">
                    Waktu Perangkat
                </span>
                <div id="live-clock" class="text-2xl font-black tracking-tight leading-tight">
                    <?= date('H:i') ?><span class="text-base opacity-80" id="live-seconds">:<?= date('s') ?></span> <span class="text-[11px] font-normal opacity-75">WITA</span>
                </div>
            </div>
            <?php
                $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][(int)date('w')];
                $bulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][(int)date('n')];
                $tglIndo = $hariIndo . ', ' . date('j') . ' ' . $bulanIndo . ' ' . date('Y');
            ?>
            <div class="text-right">
                <p class="text-xs text-white font-bold"><?= e($tglIndo) ?></p>
                <p class="text-[10px] text-primary-100 truncate max-w-[170px]"><?= e($schoolName ?? 'Kampus Utama BIP') ?></p>
            </div>
        </div>
        <div class="absolute -right-6 -bottom-6 w-20 h-20 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
    </div>

    <?php if (!empty($liburInfo)): ?>
    <div class="bg-sky-50 border border-sky-200 rounded-2xl p-3 flex items-start gap-2.5 text-sky-900 text-xs shadow-xs">
        <span class="text-lg shrink-0">🏖️</span>
        <div>
            <p class="font-extrabold">Hari Libur: <?= e($liburInfo['nama_libur']) ?></p>
            <p class="text-[11px] text-sky-800 mt-0.5 leading-snug">
                Tanggal hari ini dikecualikan dari perhitungan hari kerja, alpa absensi, serta target jurnal staff.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- 2. Dynamic Attendance Actions (Langsung terlihat di atas tanpa scroll) -->
    <div id="absen-action-container" class="space-y-3">
        <!-- Injected dynamically by renderAttendancePageUI() based on state and device time -->
    </div>

    <!-- 3. Interactive Geolocation & Radius Status + Map -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-xs">Titik Lokasi Presensi</h3>
                    <p class="text-[10px] text-slate-400"><?= e($schoolName ?? 'Kampus Utama BIP') ?> • Radius <?= (int)($schoolRadius ?? 10) ?>m (<?= e($lokasiSourceLabel ?? 'Default') ?>)</p>
                </div>
            </div>
            <button type="button" onclick="window.initGeolocation(true)" class="px-2.5 py-1 text-[11px] font-semibold text-primary-600 bg-primary-50 rounded-xl hover:bg-primary-100 flex items-center gap-1 press-bounce">
                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Refresh GPS
            </button>
        </div>

        <!-- GPS Status Badge -->
        <div class="flex items-center justify-between pt-0.5">
            <span id="gps-radius-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                <span class="w-2 h-2 rounded-full bg-slate-400 animate-pulse"></span> Mengunci GPS...
            </span>
            <span id="gps-status-text" class="text-[10px] text-slate-400 font-medium">Menghubungkan satelit...</span>
        </div>

        <!-- GPS Coordinates Summary Grid -->
        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs">
            <div>
                <p class="text-[10px] text-slate-400">Latitude</p>
                <p id="gps-lat" class="font-mono font-bold text-slate-700">-</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400">Longitude</p>
                <p id="gps-lng" class="font-mono font-bold text-slate-700">-</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400">Akurasi Perangkat</p>
                <p id="gps-accuracy" class="font-mono font-bold text-slate-700">-</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400">Jarak ke Sekolah</p>
                <p id="gps-distance" class="font-mono font-bold text-primary-600">Menghitung...</p>
            </div>
        </div>

        <!-- Leaflet Map Container -->
        <div class="w-full h-44 rounded-2xl overflow-hidden border border-slate-200 relative shadow-inner">
            <div id="leaflet-map" class="w-full h-full"></div>
        </div>
    </div>

    <!-- 4. Riwayat Presensi Hari Ini & Sebelumnya -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-xs">Riwayat Presensi Terbaru</h3>
            <a href="<?= url('mobile/rekap-absen') ?>" class="text-[11px] font-bold text-primary-600 hover:text-primary-700 flex items-center gap-1">
                <span>Rekap Lengkap</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
        <div class="space-y-2">
            <?php foreach ($recentLogs as $log): ?>
            <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 border border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-<?= $log['badge'] ?>-100 text-<?= $log['badge'] ?>-700 flex items-center justify-center font-bold text-xs">
                        <?= substr($log['date'], 0, 1) ?>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800"><?= e($log['date']) ?></p>
                        <p class="text-[10px] text-slate-400">Masuk: <?= e($log['checkin']) ?> • Pulang: <?= e($log['checkout']) ?></p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-<?= $log['badge'] ?>-50 text-<?= $log['badge'] ?>-700 border border-<?= $log['badge'] ?>-200">
                    <?= e($log['status']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<script>
    window.CUSTOM_SCHOOL_COORDS = {
        lat: <?= json_encode((float)($schoolCoords['lat'] ?? -0.8917)) ?>,
        lng: <?= json_encode((float)($schoolCoords['lng'] ?? 119.8707)) ?>,
        name: <?= json_encode((string)($schoolName ?? 'Kampus Utama BIP')) ?>,
        radius: <?= json_encode((int)($schoolRadius ?? 10)) ?>
    };
    window.SERVER_ATTENDANCE_TODAY = <?= json_encode($todayAttendance ?? null) ?>;
    window.SERVER_JAM_PULANG_MULAI = <?= json_encode($jamPulangMulai ?? '15:00:00') ?>;
    window.SERVER_JAM_MASUK_BATAS = <?= json_encode($jamMasukBatas ?? '07:30:00') ?>;

    if (window.SERVER_ATTENDANCE_TODAY && typeof syncAttendanceFromTodayData === 'function') {
        syncAttendanceFromTodayData(window.SERVER_ATTENDANCE_TODAY);
    }

    // Realtime digital seconds clock & live check for 15.00 threshold
    if (window._absenClockTimer) {
        clearInterval(window._absenClockTimer);
    }
    window._absenClockTimer = setInterval(() => {
        const clockEl = document.getElementById('live-clock');
        if (!clockEl) {
            clearInterval(window._absenClockTimer);
            return;
        }
        const now = new Date();
        const s = String(now.getSeconds()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const h = String(now.getHours()).padStart(2, '0');
        clockEl.innerHTML = `${h}:${m}<span class="text-xl opacity-80">:${s}</span> <span class="text-xs font-normal opacity-75">WITA</span>`;
    }, 1000);

    document.addEventListener('DOMContentLoaded', () => {
        if (window.initGeolocation) {
            window.initGeolocation();
        }
        if (typeof renderAttendancePageUI === 'function') {
            renderAttendancePageUI();
        }
    });
</script>
