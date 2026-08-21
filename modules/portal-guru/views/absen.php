<?php
/**
 * Absen Kehadiran & Geolokasi Screen
 * Pure GPS Attendance (No Camera needed) with Check-in Masuk and 15:00 device time-locked Absen Pulang.
 */
?>

<!-- Header with Back Button -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <h2 class="font-bold text-slate-800 text-base">Presensi Geolokasi</h2>
    <a href="<?= url('mobile/profil') ?>" class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
        <i data-lucide="history" class="w-4 h-4"></i>
    </a>
</div>

<div class="p-4 space-y-4">

    <!-- 1. Realtime Digital Clock Banner -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-4 text-white shadow-lg shadow-blue-600/20 text-center relative overflow-hidden">
        <div class="relative z-10">
            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-semibold tracking-wider uppercase mb-1">
                Waktu Perangkat Anda
            </span>
            <div id="live-clock" class="text-3xl font-black tracking-tight my-0.5">
                <?= date('H:i') ?><span class="text-xl opacity-80" id="live-seconds">:<?= date('s') ?></span> <span class="text-xs font-normal opacity-75">WITA</span>
            </div>
            <p class="text-xs text-blue-100 font-medium">
                <?= strftime('%A, %d %B %Y') ?? date('l, d F Y') ?>
            </p>
        </div>
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
    </div>

    <!-- 2. Interactive Geolocation & Radius Status -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-xs">Titik Lokasi Presensi</h3>
                    <p class="text-[10px] text-slate-400">Kampus Utama BIP • Radius 150m</p>
                </div>
            </div>
            <button type="button" onclick="window.initGeolocation()" class="px-2.5 py-1 text-[11px] font-semibold text-blue-600 bg-blue-50 rounded-xl hover:bg-blue-100 flex items-center gap-1 press-bounce">
                <i data-lucide="refresh-cw" class="w-3 h-3"></i> Refresh GPS
            </button>
        </div>

        <!-- GPS Coordinates Summary Grid -->
        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs">
            <div>
                <p class="text-[10px] text-slate-400">Latitude</p>
                <p id="gps-lat" class="font-mono font-bold text-slate-700">-5.14766</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400">Longitude</p>
                <p id="gps-lng" class="font-mono font-bold text-slate-700">119.43273</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400">Akurasi Perangkat</p>
                <p id="gps-accuracy" class="font-mono font-bold text-slate-700">±8m</p>
            </div>
            <div>
                <p class="text-[10px] text-slate-400">Jarak ke Sekolah</p>
                <p id="gps-distance" class="font-mono font-bold text-emerald-600">18 Meter</p>
            </div>
        </div>

        <!-- GPS Status Badge -->
        <div class="flex items-center justify-between pt-1">
            <span id="gps-radius-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span> Dalam Radius Sekolah (18m)
            </span>
            <span id="gps-status-text" class="text-[10px] text-slate-400 font-medium">GPS Terkunci</span>
        </div>

        <!-- Leaflet Map Container -->
        <div class="w-full h-44 rounded-2xl overflow-hidden border border-slate-200 relative shadow-inner">
            <div id="leaflet-map" class="w-full h-full"></div>
        </div>
    </div>

    <!-- 3. Dynamic Attendance Actions (Check-in Masuk & Check-out Pulang Jam 15.00) -->
    <div id="absen-action-container" class="space-y-3">
        <!-- Injected dynamically by renderAttendancePageUI() based on state and device time -->
    </div>

    <!-- 4. Riwayat Presensi Hari Ini & Sebelumnya -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <h3 class="font-bold text-slate-800 text-xs">Riwayat Presensi Terbaru</h3>
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
    // Realtime digital seconds clock & live check for 15.00 threshold
    setInterval(() => {
        const now = new Date();
        const s = String(now.getSeconds()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const h = String(now.getHours()).padStart(2, '0');
        const clockEl = document.getElementById('live-clock');
        if (clockEl) {
            clockEl.innerHTML = `${h}:${m}<span class="text-xl opacity-80">: ${s}</span> <span class="text-xs font-normal opacity-75">WITA</span>`;
        }
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
