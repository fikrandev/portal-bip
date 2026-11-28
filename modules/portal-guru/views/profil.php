<?php
/**
 * Profil Guru & PWA Management Screen
 * Compact size with Android Bottom Sheet confirmations and install dialogs.
 */
?>

<!-- Header -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <h2 class="font-bold text-slate-800 text-base">Profil & Pengaturan</h2>
    </div>
    <span id="network-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 border border-primary-200">
        <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span> Online
    </span>
</div>

<div class="p-4 space-y-4">

    <!-- 1. Teacher Profile Card -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 text-center relative overflow-hidden space-y-3">
        <div class="relative w-20 h-20 mx-auto">
            <?php 
                $profileAvatar = !empty($profile['avatar']) ? $profile['avatar'] : Auth::avatar(); 
                $profileInitials = '';
                if (!empty($profile['name'])) {
                    $pParts = preg_split('/\s+/', trim($profile['name']));
                    $profileInitials = strtoupper(substr($pParts[0] ?? '', 0, 1) . substr($pParts[1] ?? '', 0, 1));
                }
                if (empty($profileInitials)) $profileInitials = 'G';
            ?>
            <?php if (!empty($profileAvatar)): ?>
                <img src="<?= e($profileAvatar) ?>" alt="<?= e($profile['name']) ?>" loading="lazy" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-md mx-auto">
            <?php else: ?>
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-white font-black text-2xl flex items-center justify-center border-4 border-white shadow-md mx-auto tracking-wider">
                    <?= e($profileInitials) ?>
                </div>
            <?php endif; ?>
            <span class="absolute bottom-1 right-1 w-5 h-5 bg-primary-500 border-2 border-white rounded-full flex items-center justify-center text-white text-[10px]">✓</span>
        </div>

        <div>
            <h3 class="font-black text-slate-900 text-lg leading-tight"><?= e($profile['name']) ?></h3>
            <p class="text-xs text-primary-600 font-bold mt-0.5"><?= e($profile['role']) ?></p>
            <p class="text-[11px] text-slate-400 font-mono mt-0.5">NIP: <?= e($profile['nip']) ?></p>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs text-left">
            <div class="bg-slate-50 p-2.5 rounded-2xl">
                <p class="text-[10px] text-slate-400"><?= !empty($profile['is_support']) ? 'Status Penugasan' : 'Beban Mengajar' ?></p>
                <p class="font-bold text-slate-800"><?= e($profile['teachingHours']) ?></p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-2xl">
                <p class="text-[10px] text-slate-400">Tahun Akademik</p>
                <p class="font-bold text-slate-800"><?= e($profile['academicYear']) ?></p>
            </div>
        </div>
    </div>

    <!-- 1B. Push Notification Real-Time Toggle -->
    <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                    <i data-lucide="bell-ring" class="w-5 h-5"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="font-extrabold text-sm text-slate-900 leading-tight">Push Notification Real-Time</h4>
                    <p id="push-status-text" class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                        Memeriksa status notifikasi perangkat...
                    </p>
                </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer shrink-0">
                <input type="checkbox" id="toggle-push-notif" class="sr-only peer" onchange="handlePushToggleChange(this)">
                <div class="w-12 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:rounded-full after:h-[22px] after:w-[22px] after:shadow-sm after:transition-all peer-checked:bg-emerald-500"></div>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
            <div id="push-schedule-pills" class="flex flex-wrap items-center gap-1.5 text-[10.5px] font-bold text-slate-600">
                <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-600">⏰ Jadwal Pengingat Jurnal</span>
            </div>
            <button type="button" id="btn-test-push" onclick="sendTestPushNotif(this)"
                    class="hidden px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs press-bounce">
                🔔 Tes Bunyi &amp; Notifikasi
            </button>
        </div>
        <p class="text-[10.5px] text-slate-400 leading-relaxed">
            Notifikasi otomatis mengikuti jadwal pengingat WA jurnal kerja dan menggunakan <strong>suara notifikasi default perangkat</strong> Anda.
        </p>
    </div>

    <!-- 2. PWA Installation & Device Controls -->
    <div class="bg-gradient-to-br from-primary-700 via-indigo-700 to-primary-800 rounded-3xl p-5 text-white shadow-xl shadow-primary-600/20 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php 
                    $iconVer = defined('SYS_APP_FAVICON') ? md5(SYS_APP_FAVICON . (defined('SYS_APP_LOGO') ? SYS_APP_LOGO : '')) : time();
                    $pwaInstallerIcon = url('pwa-icon.png?size=192&v=' . $iconVer);
                ?>
                <img src="<?= $pwaInstallerIcon ?>" alt="App Icon" loading="lazy" class="w-12 h-12 rounded-2xl shadow-md border-2 border-white/30 shrink-0 bg-white/90 p-1 object-contain">
                <div>
                    <h4 class="font-bold text-sm leading-tight">Aplikasi PWA & WebAPK</h4>
                    <p class="text-xs text-primary-100 mt-0.5">Paket Resmi Portal Guru BIP</p>
                </div>
            </div>
            <span class="px-2 py-0.5 bg-primary-400 text-slate-900 text-[10px] font-black rounded-lg">v1.2.0</span>
        </div>

        <!-- Real-time Diagnostics Grid -->
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-2.5 border border-white/10">
                <p class="text-[10px] text-primary-200">Mode Eksekusi</p>
                <p id="diag-app-mode" class="font-bold text-white text-[11px] mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-300 animate-pulse"></span> Mendeteksi...
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-2.5 border border-white/10">
                <p class="text-[10px] text-primary-200">Penyimpanan Offline</p>
                <p id="diag-cache-size" class="font-bold text-white text-[11px] mt-0.5 flex items-center gap-1">
                    <span>💾</span> ±3.8 MB
                </p>
            </div>
        </div>

        <p class="text-xs text-primary-100/90 leading-relaxed">
            Pasang aplikasi ini ke perangkat untuk pengalaman layar penuh bebas hambatan, akses instan tanpa browser, dan kemampuan presensi GPS offline.
        </p>

        <!-- PWA Action Buttons -->
        <div class="space-y-2 pt-1">
            <button type="button" onclick="window.triggerPWAInstall()" class="w-full py-3 bg-white hover:bg-primary-50 text-primary-700 font-bold text-xs rounded-2xl shadow-lg flex items-center justify-center gap-2 press-bounce">
                <i data-lucide="download" class="w-4 h-4"></i>
                Pasang Aplikasi Portal BIP ke Smartphone
            </button>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="window.showAndroidInstallModal()" class="py-2.5 bg-white/15 hover:bg-white/25 text-white font-semibold text-xs rounded-2xl border border-white/20 flex items-center justify-center gap-1.5 press-bounce">
                    <span>🤖</span> Panduan Android
                </button>
                <button type="button" onclick="window.showIOSInstallModal()" class="py-2.5 bg-white/15 hover:bg-white/25 text-white font-semibold text-xs rounded-2xl border border-white/20 flex items-center justify-center gap-1.5 press-bounce">
                    <span>🍎</span> Panduan iPhone
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Account & Portal Navigation List -->
    <div class="bg-white rounded-3xl p-2 shadow-sm border border-slate-100 divide-y divide-slate-100">
        
        <a href="<?= url('mobile/rekap-absen') ?>" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition-all press-bounce">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-slate-800">Rekap Presensi & Keterlambatan</h4>
                    <p class="text-[10px] text-slate-400">Rincian jam hadir, telat & alpa periode</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </a>

        <?php if (!empty($profile['is_support']) || !empty($isSupport)): ?>
        <a href="<?= url('mobile/jurnal?tab=riwayat') ?>" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition-all press-bounce">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="book-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-slate-800">Riwayat Jurnal Kerja Staff</h4>
                    <p class="text-[10px] text-slate-400">Log aktivitas & rincian tugas harian staff</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </a>
        <?php else: ?>
        <a href="<?= url('mobile/rekap-jurnal') ?>" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition-all press-bounce">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="book-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-slate-800">Rekap Jurnal PIC & Ketuntasan KBM</h4>
                    <p class="text-[10px] text-slate-400">Monitoring sesi KBM & jadwal belum diisi</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </a>
        <?php endif; ?>

        <button type="button" onclick="clearOfflineCache()" class="w-full flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition-all press-bounce text-left">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-slate-800">Sinkronisasi & Hapus Cache</h4>
                    <p class="text-[10px] text-slate-400">Perbarui asset & reload cache offline PWA</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </button>

        <a href="<?= url('mobile-sarpras') ?>" target="_blank" rel="noopener" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition-all press-bounce">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="package" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-slate-800">Buka Sarpras Mobile</h4>
                    <p class="text-[10px] text-slate-400">Aplikasi inventaris aset, QR scan & perbaikan</p>
                </div>
            </div>
            <i data-lucide="external-link" class="w-4 h-4 text-slate-400"></i>
        </a>

        <a href="<?= url('dashboard') ?>" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition-all press-bounce">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-slate-800">Buka Portal Web BIP (Desktop)</h4>
                    <p class="text-[10px] text-slate-400">Akses dashboard admin dan kelola modul</p>
                </div>
            </div>
            <i data-lucide="external-link" class="w-4 h-4 text-slate-400"></i>
        </a>

        <button type="button" onclick="confirmLogout()" class="w-full flex items-center justify-between p-3.5 hover:bg-rose-50 text-rose-600 rounded-2xl transition-all press-bounce text-left">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs">Keluar dari Akun</h4>
                    <p class="text-[10px] text-rose-400">Akhiri sesi portal di perangkat ini</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </button>

    </div>

    <div class="text-center py-2">
        <p class="text-[11px] text-slate-400">Portal BIP Guru Mobile PWA • Versi 1.0.0</p>
    </div>

</div>

<script>
    function confirmLogout() {
        AndroidUI.confirm({
            title: 'Keluar dari Akun?',
            subtitle: 'Konfirmasi keluar sesi',
            icon: '🚪',
            iconBg: 'bg-rose-100 text-rose-600',
            type: 'danger',
            message: 'Apakah Anda yakin ingin keluar dari akun Portal Guru pada perangkat ini?',
            confirmText: 'Ya, Keluar',
            cancelText: 'Batal',
            onConfirm: () => {
                AndroidUI.toast('Mengeluarkan akun...', 'info');
                try {
                    localStorage.removeItem('portal_guru_token');
                } catch(e) {}
                setTimeout(() => {
                    window.location.href = '<?= url("mobile/logout") ?>';
                }, 400);
            }
        });
    }

    function clearOfflineCache() {
        AndroidUI.confirm({
            title: 'Sinkronisasi Cache Offline?',
            subtitle: 'Pembersihan memori lokal',
            icon: '🔄',
            iconBg: 'bg-amber-100 text-amber-600',
            message: 'Tindakan ini akan mengunduh ulang aset terbaru dari server dan menyegarkan penyimpanan offline.',
            confirmText: 'Sinkronkan',
            cancelText: 'Batal',
            onConfirm: () => {
                AndroidUI.showCenterLoading('Menyinkronkan cache offline...');
                if ('caches' in window) {
                    caches.keys().then(names => {
                        names.forEach(name => caches.delete(name));
                    });
                }
                setTimeout(() => {
                    AndroidUI.hideCenterLoading();
                    AndroidUI.success({
                        title: 'Sinkronisasi Selesai!',
                        subtitle: 'Cache offline telah diperbarui',
                        message: 'Penyimpanan lokal berhasil disegarkan dengan aset aplikasi terbaru.',
                        buttonText: 'Muat Ulang',
                        onOk: () => window.location.reload()
                    });
                }, 800);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', async () => {
        // Load PWA Diagnostics
        const isStandalone = window.isInStandaloneMode();
        const modeElem = document.getElementById('diag-app-mode');
        if (modeElem) {
            if (isStandalone) {
                modeElem.innerHTML = '<span class="text-primary-300 font-bold">✓ Standalone App</span>';
            } else {
                modeElem.innerHTML = '<span class="text-primary-200">Web Browser</span>';
            }
        }

        if (window.checkPWADiagnostics) {
            const diag = await window.checkPWADiagnostics();
            const cacheElem = document.getElementById('diag-cache-size');
            if (cacheElem && diag.cacheSize) {
                cacheElem.innerHTML = `<span>💾</span> ${diag.cacheSize}`;
            }
        }

        initPushNotificationToggle();
    });

    window._pushVapidPublicKey = null;

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function getMobileCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? (meta.getAttribute('content') || '') : '';
    }

    async function ensureMobileSwRegistration() {
        if (!('serviceWorker' in navigator)) {
            throw new Error('Browser perangkat ini belum mendukung Service Worker.');
        }
        const baseUrl = window.APP_BASE_URL || '';
        const swPath = baseUrl ? `${baseUrl}/sw.js` : '/sw.js';
        const scopePath = baseUrl ? `${baseUrl}/mobile/` : '/mobile/';

        let reg = await navigator.serviceWorker.getRegistration(scopePath);
        if (!reg) {
            reg = await navigator.serviceWorker.register(swPath, { scope: scopePath });
        }
        if (reg.waiting) {
            reg.waiting.postMessage({ action: 'SKIP_WAITING' });
        }
        await navigator.serviceWorker.ready;
        return reg;
    }

    function setPushUiState(isOn, statusMsg, isError) {
        const toggle = document.getElementById('toggle-push-notif');
        const statusEl = document.getElementById('push-status-text');
        const testBtn = document.getElementById('btn-test-push');

        if (toggle) toggle.checked = Boolean(isOn);
        if (statusEl) {
            statusEl.textContent = statusMsg;
            statusEl.className = 'text-[11px] mt-0.5 leading-snug ' + (isError ? 'text-rose-600 font-semibold' : (isOn ? 'text-emerald-600 font-semibold' : 'text-slate-500'));
        }
        if (testBtn) {
            testBtn.classList.toggle('hidden', !isOn);
        }
    }

    async function initPushNotificationToggle() {
        const toggle = document.getElementById('toggle-push-notif');
        const pillsEl = document.getElementById('push-schedule-pills');

        // Ambil konfigurasi VAPID & jadwal pengingat jurnal
        try {
            const cfgRes = await fetch('<?= url("mobile/api/push/config") ?>', { credentials: 'same-origin' });
            const cfg = await cfgRes.json();
            if (cfg && cfg.success) {
                window._pushVapidPublicKey = cfg.public_key || null;
                if (pillsEl && Array.isArray(cfg.schedules) && cfg.schedules.length) {
                    pillsEl.innerHTML = '<span class="text-slate-400 font-semibold">Jadwal:</span> ' +
                        cfg.schedules.map(s => `<span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono">${s} WITA</span>`).join(' ');
                }
            }
        } catch (e) {}

        const isIOS = window.isIOSDevice && window.isIOSDevice();
        const isStandalone = window.isInStandaloneMode && window.isInStandaloneMode();

        if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
            if (toggle) toggle.disabled = true;
            if (isIOS && !isStandalone) {
                setPushUiState(false, 'Di iPhone, pasang dulu ke Layar Utama (Add to Home Screen) agar Push Notification aktif.', true);
            } else {
                setPushUiState(false, 'Browser perangkat ini belum mendukung Push Notification.', true);
            }
            return;
        }

        if (Notification.permission === 'denied') {
            setPushUiState(false, 'Izin notifikasi diblokir browser. Aktifkan di pengaturan situs browser Anda.', true);
            return;
        }

        try {
            const reg = await ensureMobileSwRegistration();
            const sub = await reg.pushManager.getSubscription();
            if (sub && Notification.permission === 'granted') {
                setPushUiState(true, 'Aktif di perangkat ini (suara bawaan perangkat).', false);
            } else {
                setPushUiState(false, 'Nonaktif — nyalakan tombol untuk menerima pengingat real-time.', false);
            }
        } catch (err) {
            setPushUiState(false, 'Nonaktif — ketuk tombol untuk mengaktifkan notifikasi.', false);
        }
    }

    async function handlePushToggleChange(toggleEl) {
        if (!toggleEl) return;
        const wantEnable = Boolean(toggleEl.checked);
        toggleEl.disabled = true;

        try {
            if (wantEnable) {
                setPushUiState(true, 'Mengaktifkan Push Notification...', false);

                if (Notification.permission !== 'granted') {
                    const perm = await Notification.requestPermission();
                    if (perm !== 'granted') {
                        throw new Error('Izin notifikasi ditolak. Silakan izinkan notifikasi pada browser/HP Anda.');
                    }
                }

                if (!window._pushVapidPublicKey) {
                    const cfgRes = await fetch('<?= url("mobile/api/push/config") ?>', { credentials: 'same-origin' });
                    const cfg = await cfgRes.json();
                    if (!cfg || !cfg.success || !cfg.public_key) {
                        throw new Error((cfg && cfg.message) ? cfg.message : 'Kunci VAPID server tidak tersedia.');
                    }
                    window._pushVapidPublicKey = cfg.public_key;
                }

                const reg = await ensureMobileSwRegistration();
                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(window._pushVapidPublicKey)
                    });
                }

                const subJson = sub.toJSON();
                const saveRes = await fetch('<?= url("mobile/api/push/subscribe") ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getMobileCsrfToken()
                    },
                    body: JSON.stringify(subJson)
                });
                const saveData = await saveRes.json();
                if (!saveRes.ok || !saveData || !saveData.success) {
                    throw new Error((saveData && saveData.message) ? saveData.message : 'Gagal menyimpan langganan push di server.');
                }

                setPushUiState(true, 'Aktif di perangkat ini (suara bawaan perangkat).', false);
                if (window.AndroidUI && AndroidUI.toast) {
                    AndroidUI.toast('Push Notification real-time aktif!', 'success');
                }
            } else {
                setPushUiState(false, 'Menonaktifkan Push Notification...', false);
                let endpoint = '';
                try {
                    const reg = await ensureMobileSwRegistration();
                    const sub = await reg.pushManager.getSubscription();
                    if (sub) {
                        endpoint = sub.endpoint || '';
                        await sub.unsubscribe();
                    }
                } catch (e) {}

                await fetch('<?= url("mobile/api/push/unsubscribe") ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getMobileCsrfToken()
                    },
                    body: JSON.stringify({ endpoint: endpoint })
                });

                setPushUiState(false, 'Nonaktif — nyalakan tombol untuk menerima pengingat real-time.', false);
                if (window.AndroidUI && AndroidUI.toast) {
                    AndroidUI.toast('Push Notification dinonaktifkan.', 'info');
                }
            }
        } catch (err) {
            setPushUiState(!wantEnable, err.message || 'Gagal mengubah pengaturan notifikasi.', true);
            if (window.AndroidUI && AndroidUI.toast) {
                AndroidUI.toast(err.message || 'Gagal mengubah Push Notification', 'error');
            }
        } finally {
            toggleEl.disabled = false;
        }
    }

    async function sendTestPushNotif(btnEl) {
        const orig = btnEl ? btnEl.innerHTML : '';
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = '⏳ Mengirim...';
        }
        try {
            const res = await fetch('<?= url("mobile/api/push/test") ?>', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getMobileCsrfToken()
                },
                body: JSON.stringify({})
            });
            const data = await res.json();
            if (window.AndroidUI && AndroidUI.toast) {
                AndroidUI.toast(data.message || (data.success ? 'Tes notifikasi terkirim!' : 'Gagal mengirim tes notifikasi.'), data.success ? 'success' : 'error');
            } else {
                alert(data.message || (data.success ? 'Tes notifikasi terkirim!' : 'Gagal mengirim tes notifikasi.'));
            }
        } catch (err) {
            if (window.AndroidUI && AndroidUI.toast) {
                AndroidUI.toast('Gagal mengirim tes push: ' + err.message, 'error');
            }
        } finally {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = orig;
            }
        }
    }
</script>
