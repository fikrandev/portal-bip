<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, interactive-widget=resizes-content">
    <title><?= e($pageTitle ?? 'Portal Guru') ?> — Portal BIP</title>
    
    <!-- PWA Settings -->
    <link rel="manifest" href="<?= url('mobile/manifest.json') ?>">
    <meta name="theme-color" content="#2563eb">
    <meta name="application-name" content="Portal BIP">
    <meta name="apple-mobile-web-app-title" content="Portal BIP">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <script>
        window.APP_BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        // Auto-cleanup any rogue Sarpras or root service worker immediately
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(regs) {
                regs.forEach(function(reg) {
                    var script = (reg.active && reg.active.scriptURL) ? reg.active.scriptURL : '';
                    var scope = reg.scope || '';
                    if (script.indexOf('sw-sarpras') !== -1 || (!scope.includes('/mobile/') && !scope.includes('/mobile-sarpras/'))) {
                        reg.unregister().then(function() {
                            console.log('[PWA Cleanup] Unregistered rogue SW:', script, scope);
                        });
                    }
                });
            }).catch(function() {});
            if ('caches' in window) {
                caches.keys().then(function(keys) {
                    keys.forEach(function(k) {
                        if (k.indexOf('sarpras') !== -1) {
                            caches.delete(k);
                        }
                    });
                }).catch(function() {});
            }
        }
    </script>
    <?php
        $iconVer = defined('SYS_APP_FAVICON') ? md5(SYS_APP_FAVICON . (defined('SYS_APP_LOGO') ? SYS_APP_LOGO : '')) : time();
        $pwaIcon180 = url('pwa-icon.png?size=180&v=' . $iconVer);
        $pwaIcon192 = url('pwa-icon.png?size=192&v=' . $iconVer);
        $pwaIcon512 = url('pwa-icon.png?size=512&v=' . $iconVer);
        $favIconRaw = defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON) ? url(ltrim(SYS_APP_FAVICON, '/')) : $pwaIcon192;
    ?>
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $pwaIcon180 ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= $pwaIcon192 ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= $pwaIcon512 ?>">
    <link rel="shortcut icon" href="<?= $favIconRaw ?>">
    
    <!-- Google Fonts & CDN Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Uthmani';
            src: url('https://cdn.qurancdn.com/assets/fonts/quran/hafs/uthmanic/KFGQPC_Uthmanic_Script_HAFS_Regular.woff2') format('woff2'),
                 url('https://cdn.qurancdn.com/assets/fonts/quran/hafs/uthmanic/KFGQPC_Uthmanic_Script_HAFS_Regular.woff') format('woff');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        .font-arabic { font-family: 'Amiri', 'Traditional Arabic', 'Scheherazade New', serif; }
        .font-quran { font-family: 'Uthmani', 'Amiri', 'Traditional Arabic', serif; }
    </style>
    
    <!-- CSRF Token -->
    <?= class_exists('CSRF') ? CSRF::meta() : '' ?>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    },
                    spacing: {
                        '13': '3.25rem',
                    },
                    boxShadow: {
                        'xs': '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
                    },
                    backdropBlur: {
                        'xs': '2px',
                    }
                }
            }
        }
    </script>
    
    <!-- Core App CSS & Utilities -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">

    <!-- Smooth Animations CSS -->
    <link rel="stylesheet" href="<?= asset('css/mobile/app-animations.css') ?>?v=2">

    <!-- Leaflet Map CSS & Deferred JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
    
    <!-- Pinned Fast Lucide Icons (Zero 302 Redirect Latency) -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.460.0/dist/umd/lucide.min.js"></script>

    <script>
        window.APP_BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        // Ensure DOMContentLoaded listeners inside SPA-swapped views execute immediately
        (function() {
            const origAddEvent = document.addEventListener.bind(document);
            document.addEventListener = function(type, listener, options) {
                if (type === 'DOMContentLoaded' && document.readyState !== 'loading') {
                    if (typeof listener === 'function') {
                        setTimeout(function() {
                            try { listener(new Event('DOMContentLoaded')); } catch (e) { console.warn(e); }
                        }, 0);
                    }
                    return;
                }
                return origAddEvent(type, listener, options);
            };
        })();
    </script>

    <style>
        :root {
            --sat: env(safe-area-inset-top, 0px);
            --sab: env(safe-area-inset-bottom, 0px);
        }
        body {
            font-family: 'Inter', sans-serif;
            -webkit-tap-highlight-color: transparent;
            overscroll-behavior-y: contain;
        }
        .safe-top { padding-top: var(--sat); }
        .safe-bottom { padding-bottom: var(--sab); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Smooth bounce & active press */
        .press-bounce {
            transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.15s ease;
        }
        .press-bounce:active {
            transform: scale(0.95);
            opacity: 0.9;
        }

        /* Utility Fallbacks for Tailwind CDN */
        .w-13 { width: 3.25rem !important; }
        .h-13 { height: 3.25rem !important; }
        .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; }
        .backdrop-blur-xs { backdrop-filter: blur(2px) !important; -webkit-backdrop-filter: blur(2px) !important; }

        /* Color Badges Fallbacks (Zero JIT Overhead) */
        .bg-emerald-50 { background-color: #ecfdf5 !important; }
        .text-emerald-700 { color: #047857 !important; }
        .border-emerald-200 { border-color: #a7f3d0 !important; }
        .bg-amber-50 { background-color: #fffbeb !important; }
        .text-amber-700 { color: #b45309 !important; }
        .border-amber-200 { border-color: #fde68a !important; }
        .bg-indigo-50 { background-color: #eef2ff !important; }
        .text-indigo-600 { color: #4f46e5 !important; }
        .border-indigo-100 { border-color: #e0e7ff !important; }
        .bg-purple-50 { background-color: #faf5ff !important; }
        .text-purple-600 { color: #9333ea !important; }
        .border-purple-100 { border-color: #f3e8ff !important; }
        .bg-teal-50 { background-color: #f0fdfa !important; }
        .text-teal-600 { color: #0d9488 !important; }
        .border-teal-100 { border-color: #ccfbf1 !important; }
        .bg-sky-50 { background-color: #f0f9ff !important; }
        .text-sky-600 { color: #0284c7 !important; }
        .border-sky-100 { border-color: #e0f2fe !important; }
        .bg-blue-50 { background-color: #eff6ff !important; }
        .text-blue-600 { color: #2563eb !important; }
        .border-blue-100 { border-color: #dbeafe !important; }
    </style>
</head>
<body class="h-full bg-slate-100 flex justify-center text-slate-800 antialiased selection:bg-primary-600 selection:text-white">

    <!-- Top Slim SPA Progress Bar -->
    <div id="spa-progress-bar" aria-hidden="true"></div>

    <!-- Mobile Shell Wrapper (Original Compact Size: max-w-md) -->
    <div class="w-full max-w-md min-h-screen bg-[#f6f8fb] shadow-2xl flex flex-col relative overflow-x-hidden border-x border-slate-200/60 sm:my-4 sm:rounded-3xl sm:min-h-[860px]">
        
        <!-- Main View Content -->
        <main id="mobile-main-view" 
              data-active-tab="<?= e($activeTab ?? 'beranda') ?>" 
              data-page-title="<?= e($pageTitle ?? 'Portal Guru') ?> — Portal BIP"
              class="flex-1 pb-24 overflow-y-auto no-scrollbar">
            <?= $content ?? '' ?>
        </main>

        <!-- Bottom Navigation Bar (5 Items with Center Elevated FAB & Pill Indicators) -->
        <nav id="mobile-bottom-nav" class="fixed bottom-0 sm:bottom-4 left-0 right-0 max-w-md mx-auto z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/70 px-2 py-1.5 flex items-center justify-around sm:rounded-b-3xl safe-bottom shadow-[0_-6px_25px_rgba(15,23,42,0.05)]">
            
            <!-- Tab 1: Beranda -->
            <a href="<?= url('mobile') ?>" data-nav-tab="beranda" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'beranda' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                    <i data-lucide="home" class="w-5 h-5 <?= ($activeTab ?? '') === 'beranda' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                </div>
                <span class="text-[10.5px] leading-none tracking-tight">Beranda</span>
            </a>

            <!-- Tab 2: Kelas -->
            <a href="<?= url('mobile/kelas') ?>" data-nav-tab="kelas" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'kelas' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 <?= ($activeTab ?? '') === 'kelas' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                </div>
                <span class="text-[10.5px] leading-none tracking-tight">Kelas</span>
            </a>

            <!-- Center Elevated FAB: Buat (+) -->
            <div class="-mt-6 px-1 flex flex-col items-center">
                <button type="button" onclick="openAndroidActionSheet()" class="w-13 h-13 rounded-full bg-gradient-to-tr from-primary-600 via-primary-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-primary-600/35 hover:shadow-primary-600/50 press-bounce border-4 border-[#f6f8fb]">
                    <i data-lucide="plus" class="w-6 h-6 stroke-[3]"></i>
                </button>
                <span class="text-[10px] font-bold text-slate-500 mt-0.5 tracking-tight">Aksi</span>
            </div>

            <!-- Tab 3: Murid -->
            <a href="<?= url('mobile/murid') ?>" data-nav-tab="murid" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'murid' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-5 h-5 <?= ($activeTab ?? '') === 'murid' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                </div>
                <span class="text-[10.5px] leading-none tracking-tight">Murid</span>
            </a>

            <!-- Tab 4: Profil -->
            <a href="<?= url('mobile/profil') ?>" data-nav-tab="profil" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'profil' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                    <i data-lucide="user" class="w-5 h-5 <?= ($activeTab ?? '') === 'profil' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                </div>
                <span class="text-[10.5px] leading-none tracking-tight">Profil</span>
            </a>
        </nav>

    </div>

    <!-- Scripts -->
    <script src="<?= asset('js/mobile/android-ui.js') ?>?v=2"></script>
    <script src="<?= asset('js/mobile/mobile-api.js') ?>?v=2"></script>
    <script src="<?= asset('js/mobile/lazy-load.js') ?>?v=2"></script>
    <script src="<?= asset('js/pwa.js') ?>?v=2"></script>
    <script src="<?= asset('js/mobile/mobile-app.js') ?>?v=2"></script>

    <script>
        // Open Android-style Bottom Sheet for '+' FAB
        function openAndroidActionSheet() {
            AndroidUI.bottomSheet({
                title: 'Aksi Cepat Guru',
                subtitle: 'Pilih aktivitas pembelajaran, presensi & perizinan',
                icon: '⚡',
                iconBg: 'bg-primary-100 text-primary-600',
                content: `
                    <div class="grid grid-cols-3 gap-2.5 pt-1 pb-1">
                        <a href="<?= url('mobile/absen') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-500 text-white flex items-center justify-center shadow-md shadow-primary-500/20 text-base font-bold">📍</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Absen GPS</span>
                        </a>
                        <a href="<?= url('mobile/jurnal') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-md shadow-primary-500/20 text-base font-bold">📝</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Isi Jurnal</span>
                        </a>
                        <a href="<?= url('mobile/absensi-kelas') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-amber-50 rounded-2xl border border-amber-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/20 text-base font-bold">👥</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Absen Kelas</span>
                        </a>

                        <!-- New Features -->
                        <a href="<?= url('mobile/keterlambatan-siswa') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-rose-50 rounded-2xl border border-rose-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-md shadow-rose-500/20 text-base font-bold">⏳</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Terlambat</span>
                        </a>
                        <a href="<?= url('mobile/izin') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-indigo-50 rounded-2xl border border-indigo-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 text-base font-bold">📄</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Izin Guru</span>
                        </a>
                        <a href="<?= url('mobile/cuti') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-md shadow-primary-500/20 text-base font-bold">🏖️</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Cuti Guru</span>
                        </a>

                        <a href="<?= url('mobile/buat-tugas') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-purple-50 rounded-2xl border border-purple-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-md shadow-purple-500/20 text-base font-bold">📋</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Buat Tugas</span>
                        </a>
                        <a href="<?= url('mobile/quran') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-md shadow-primary-500/20 text-base font-bold">📖</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Al-Qur'an</span>
                        </a>
                        <a href="<?= url('mobile/dzikir') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-purple-50 rounded-2xl border border-purple-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-md shadow-purple-500/20 text-base font-bold">📿</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Dzikir</span>
                        </a>
                    </div>
                `
            });
        }

        // Custom iOS Install Bottom Sheet
        window.showIOSInstallModal = function() {
            AndroidUI.bottomSheet({
                title: 'Pasang di iPhone / iPad',
                subtitle: 'Panduan menambahkan ke Layar Utama Safari',
                icon: '🍎',
                iconBg: 'bg-slate-100 text-slate-800',
                content: `
                    <div class="space-y-2.5 bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs text-slate-700 leading-relaxed">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">1</span>
                            <p>Tekan tombol <strong>Share / Bagikan</strong> <span class="px-1 py-0.5 bg-white border border-slate-200 rounded font-mono">⎋ / ⤤</span> di bar bawah Safari.</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">2</span>
                            <p>Gulir ke bawah dan pilih <strong>"Tambahkan ke Layar Utama" (Add to Home Screen)</strong> ➕.</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">3</span>
                            <p>Tekan <strong>"Tambah" (Add)</strong> di pojok kanan atas. Selesai!</p>
                        </div>
                    </div>
                `,
                actions: [
                    {
                        text: 'Saya Mengerti',
                        className: 'w-full py-3 bg-primary-600 text-white font-bold text-xs rounded-2xl shadow-md'
                    }
                ]
            });
        };

        // Custom Android Install Bottom Sheet
        window.showAndroidInstallModal = function() {
            AndroidUI.bottomSheet({
                title: 'Pasang di Android',
                subtitle: 'Panduan menambahkan ke Layar Utama Chrome',
                icon: '🤖',
                iconBg: 'bg-primary-100 text-primary-700',
                content: `
                    <div class="space-y-2.5 bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs text-slate-700 leading-relaxed">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">1</span>
                            <p>Tekan menu titik tiga <strong class="font-mono">⋮</strong> di pojok kanan atas browser Google Chrome.</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">2</span>
                            <p>Pilih menu <strong>"Tambahkan ke Layar Utama"</strong> atau <strong>"Install Aplikasi"</strong>.</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">3</span>
                            <p>Tekan <strong>"Install"</strong> dan tunggu ikon muncul di layar HP Anda.</p>
                        </div>
                    </div>
                `,
                actions: [
                    {
                        text: 'Saya Mengerti',
                        className: 'w-full py-3 bg-primary-600 text-white font-bold text-xs rounded-2xl shadow-md'
                    }
                ]
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>

    <?php if (!empty($_SESSION['active_guru_token'])): ?>
    <script>
        try {
            if (localStorage.getItem('portal_guru_token') !== '<?= e($_SESSION['active_guru_token']) ?>') {
                localStorage.setItem('portal_guru_token', '<?= e($_SESSION['active_guru_token']) ?>');
            }
        } catch(e) {}
    </script>
    <?php endif; ?>
</body>
</html>
