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
    <span id="network-status-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Online
    </span>
</div>

<div class="p-4 space-y-4">

    <!-- 1. Teacher Profile Card -->
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 text-center relative overflow-hidden space-y-3">
        <div class="relative w-20 h-20 mx-auto">
            <img src="<?= asset('images/mobile/teacher_rina.jpg') ?>" alt="Bu Rina" loading="lazy" class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-md mx-auto">
            <span class="absolute bottom-1 right-1 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full flex items-center justify-center text-white text-[10px]">✓</span>
        </div>

        <div>
            <h3 class="font-black text-slate-900 text-lg leading-tight"><?= e($profile['name']) ?></h3>
            <p class="text-xs text-blue-600 font-bold mt-0.5"><?= e($profile['role']) ?></p>
            <p class="text-[11px] text-slate-400 font-mono mt-0.5">NIP: <?= e($profile['nip']) ?></p>
        </div>

        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 text-xs text-left">
            <div class="bg-slate-50 p-2.5 rounded-2xl">
                <p class="text-[10px] text-slate-400">Beban Mengajar</p>
                <p class="font-bold text-slate-800"><?= e($profile['teachingHours']) ?></p>
            </div>
            <div class="bg-slate-50 p-2.5 rounded-2xl">
                <p class="text-[10px] text-slate-400">Tahun Akademik</p>
                <p class="font-bold text-slate-800"><?= e($profile['academicYear']) ?></p>
            </div>
        </div>
    </div>

    <!-- 2. PWA Installation & Device Controls -->
    <div class="bg-gradient-to-br from-blue-700 via-indigo-700 to-blue-800 rounded-3xl p-5 text-white shadow-xl shadow-blue-600/20 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="<?= asset('images/pwa/icon-192.png') ?>" alt="App Icon" loading="lazy" class="w-12 h-12 rounded-2xl shadow-md border-2 border-white/30 shrink-0">
                <div>
                    <h4 class="font-bold text-sm leading-tight">Aplikasi PWA & WebAPK</h4>
                    <p class="text-xs text-blue-100 mt-0.5">Paket Resmi Portal Guru BIP</p>
                </div>
            </div>
            <span class="px-2 py-0.5 bg-emerald-400 text-slate-900 text-[10px] font-black rounded-lg">v1.2.0</span>
        </div>

        <!-- Real-time Diagnostics Grid -->
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-2.5 border border-white/10">
                <p class="text-[10px] text-blue-200">Mode Eksekusi</p>
                <p id="diag-app-mode" class="font-bold text-white text-[11px] mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-300 animate-pulse"></span> Mendeteksi...
                </p>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-2.5 border border-white/10">
                <p class="text-[10px] text-blue-200">Penyimpanan Offline</p>
                <p id="diag-cache-size" class="font-bold text-white text-[11px] mt-0.5 flex items-center gap-1">
                    <span>💾</span> ±3.8 MB
                </p>
            </div>
        </div>

        <p class="text-xs text-blue-100/90 leading-relaxed">
            Pasang aplikasi ini ke perangkat untuk pengalaman layar penuh bebas hambatan, akses instan tanpa browser, dan kemampuan presensi GPS offline.
        </p>

        <!-- PWA Action Buttons -->
        <div class="space-y-2 pt-1">
            <button type="button" onclick="window.triggerPWAInstall()" class="w-full py-3 bg-white hover:bg-blue-50 text-blue-700 font-bold text-xs rounded-2xl shadow-lg flex items-center justify-center gap-2 press-bounce">
                <i data-lucide="download" class="w-4 h-4"></i>
                Pasang Aplikasi ke Smartphone
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
        
        <a href="<?= url('mobile/absen') ?>" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition-all press-bounce">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-slate-800">Riwayat Presensi Saya</h4>
                    <p class="text-[10px] text-slate-400">Rekapitulasi kehadiran bulan berjalan</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </a>

        <a href="<?= url('mobile/jurnal') ?>" class="flex items-center justify-between p-3.5 hover:bg-slate-50 rounded-2xl transition-all press-bounce">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="book-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-slate-800">Arsip Jurnal Mengajar</h4>
                    <p class="text-[10px] text-slate-400">Lihat semua laporan jurnal harian</p>
                </div>
            </div>
            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
        </a>

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
                setTimeout(() => {
                    window.location.href = '<?= url("logout") ?>';
                }, 800);
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
                modeElem.innerHTML = '<span class="text-emerald-300 font-bold">✓ Standalone App</span>';
            } else {
                modeElem.innerHTML = '<span class="text-blue-200">Web Browser</span>';
            }
        }

        if (window.checkPWADiagnostics) {
            const diag = await window.checkPWADiagnostics();
            const cacheElem = document.getElementById('diag-cache-size');
            if (cacheElem && diag.cacheSize) {
                cacheElem.innerHTML = `<span>💾</span> ${diag.cacheSize}`;
            }
        }
    });
</script>
