<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100 overflow-x-hidden">
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
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <script>
        window.APP_BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
        // Auto-cleanup ONLY rogue root service workers (protecting both /mobile/ and /mobile-sarpras/)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(regs) {
                regs.forEach(function(reg) {
                    var scope = reg.scope || '';
                    if (!scope.includes('/mobile/') && !scope.includes('/mobile-sarpras/')) {
                        reg.unregister().then(function() {
                            console.log('[PWA Cleanup] Unregistered rogue root SW:', scope);
                        });
                    }
                });
            }).catch(function() {});
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
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
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
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>?v=<?= file_exists(BASE_PATH . '/public/css/app.css') ? filemtime(BASE_PATH . '/public/css/app.css') : '1.2' ?>">

    <!-- Smooth Animations CSS -->
    <link rel="stylesheet" href="<?= asset('css/mobile/app-animations.css') ?>?v=<?= file_exists(BASE_PATH . '/public/css/mobile/app-animations.css') ? filemtime(BASE_PATH . '/public/css/mobile/app-animations.css') : '3' ?>">

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
            --sal: env(safe-area-inset-left, 0px);
            --sar: env(safe-area-inset-right, 0px);
        }
        html {
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
            overscroll-behavior-x: none;
            -webkit-text-size-adjust: 100%;
            touch-action: manipulation;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
            overscroll-behavior-y: auto;
            overscroll-behavior-x: none;
            overflow-x: hidden;
            width: 100%;
            max-width: 100vw;
            position: relative;
            min-height: 100%;
            min-height: -webkit-fill-available;
            min-height: 100dvh;
        }

        /* ── iPhone / iOS Auto-Zoom Prevention (Crucial: font-size >= 16px) ── */
        @supports (-webkit-touch-callout: none) {
            input, select, textarea {
                font-size: 16px !important;
            }
        }
        @media screen and (max-width: 768px) {
            input:not([type="checkbox"]):not([type="radio"]), select, textarea {
                font-size: 16px !important;
            }
        }

        /* ── iPhone Form Control Native Reset & Tap Highlights ── */
        button, input[type="button"], input[type="submit"], [role="button"], a {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        button, input, select, textarea {
            -webkit-font-smoothing: antialiased;
        }

        /* ── iPhone Notch & Dynamic Island Safe-Top for Sticky Headers ── */
        .sticky.top-0,
        [class*="sticky"][class*="top-0"] {
            top: 0 !important;
            padding-top: calc(0.875rem + env(safe-area-inset-top, 0px)) !important;
        }
        .safe-top {
            padding-top: calc(1rem + env(safe-area-inset-top, 0px)) !important;
        }

        /* ── iPhone Home Indicator Safe-Bottom ── */
        .safe-bottom {
            padding-bottom: calc(0.65rem + env(safe-area-inset-bottom, 0px)) !important;
        }
        .safe-x {
            padding-left: env(safe-area-inset-left, 0px);
            padding-right: env(safe-area-inset-right, 0px);
        }

        #mobile-main-view {
            overscroll-behavior-x: none;
            overscroll-behavior-y: auto;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            padding-bottom: calc(6.5rem + env(safe-area-inset-bottom, 0px)) !important;
        }

        #mobile-bottom-nav {
            padding-bottom: calc(0.5rem + env(safe-area-inset-bottom, 0px)) !important;
            padding-left: calc(0.5rem + env(safe-area-inset-left, 0px));
            padding-right: calc(0.5rem + env(safe-area-inset-right, 0px));
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* Universal Pull To Refresh Indicator */
        .ptr-indicator {
            position: absolute;
            top: -55px;
            left: 50%;
            transform: translateX(-50%) translateY(0);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.16);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s cubic-bezier(0.2, 0.9, 0.3, 1), opacity 0.2s ease;
            opacity: 0;
            z-index: 50;
            pointer-events: none;
        }
        .ptr-indicator.active {
            opacity: 1;
        }
        .ptr-indicator.refreshing svg {
            animation: ptr-spin 0.8s linear infinite;
        }
        @keyframes ptr-spin {
            100% { transform: rotate(360deg); }
        }
        
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
<body class="h-full bg-slate-100 flex justify-center text-slate-800 antialiased selection:bg-primary-600 selection:text-white overflow-x-hidden">

    <!-- Top Slim SPA Progress Bar -->
    <div id="spa-progress-bar" aria-hidden="true"></div>

    <!-- Mobile Shell Wrapper (Original Compact Size: max-w-md) -->
    <div class="w-full max-w-md min-w-0 min-h-screen bg-[#f6f8fb] shadow-2xl flex flex-col relative overflow-x-hidden border-x border-slate-200/60 sm:my-4 sm:rounded-3xl sm:min-h-[860px]">
        
        <!-- Pull to Refresh Floating Badge -->
        <div id="ptr-indicator" class="ptr-indicator" aria-hidden="true">
            <svg class="w-5 h-5 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
        </div>
        
        <!-- Main View Content -->
        <main id="mobile-main-view" 
              data-active-tab="<?= e($activeTab ?? 'beranda') ?>" 
              data-page-title="<?= e($pageTitle ?? 'Portal Guru') ?> — Portal BIP"
              class="flex-1 pb-24 overflow-y-auto overflow-x-hidden no-scrollbar w-full max-w-full">
            <?= $content ?? '' ?>
        </main>

        <!-- Bottom Navigation Bar (Elevated FAB & Pill Indicators) -->
        <nav id="mobile-bottom-nav" class="fixed bottom-0 sm:bottom-4 left-0 right-0 max-w-md mx-auto z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/70 px-2 py-1.5 flex items-center justify-around sm:rounded-b-3xl safe-bottom shadow-[0_-6px_25px_rgba(15,23,42,0.05)]">
            
            <!-- Tab 1: Beranda -->
            <a href="<?= url('mobile') ?>" data-nav-tab="beranda" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'beranda' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                    <i data-lucide="home" class="w-5 h-5 <?= ($activeTab ?? '') === 'beranda' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                </div>
                <span class="text-[10.5px] leading-none tracking-tight">Beranda</span>
            </a>

            <?php if (!empty($isSupport)): ?>
                <!-- Tab 2 Support System: Presensi GPS -->
                <a href="<?= url('mobile/absen') ?>" data-nav-tab="absen" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'absen' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                    <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                        <i data-lucide="map-pin" class="w-5 h-5 <?= ($activeTab ?? '') === 'absen' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                    </div>
                    <span class="text-[10.5px] leading-none tracking-tight">Presensi</span>
                </a>
            <?php else: ?>
                <!-- Tab 2 Guru: Kelas -->
                <a href="<?= url('mobile/kelas') ?>" data-nav-tab="kelas" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'kelas' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                    <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                        <i data-lucide="users" class="w-5 h-5 <?= ($activeTab ?? '') === 'kelas' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                    </div>
                    <span class="text-[10.5px] leading-none tracking-tight">Kelas</span>
                </a>
            <?php endif; ?>

            <!-- Center Elevated FAB: Aksi (+) -->
            <div class="-mt-6 px-1 flex flex-col items-center">
                <button type="button" onclick="openAndroidActionSheet()" class="w-13 h-13 rounded-full bg-gradient-to-tr from-primary-600 via-primary-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-primary-600/35 hover:shadow-primary-600/50 press-bounce border-4 border-[#f6f8fb]">
                    <i data-lucide="plus" class="w-6 h-6 stroke-[3]"></i>
                </button>
                <span class="text-[10px] font-bold text-slate-500 mt-0.5 tracking-tight">Aksi</span>
            </div>

            <?php if (!empty($isSupport)): ?>
                <!-- Tab 3 Support System: Jurnal Pegawai -->
                <a href="<?= url('mobile/jurnal') ?>" data-nav-tab="jurnal" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'jurnal' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                    <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                        <i data-lucide="book-open" class="w-5 h-5 <?= ($activeTab ?? '') === 'jurnal' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                    </div>
                    <span class="text-[10.5px] leading-none tracking-tight">Jurnal</span>
                </a>
            <?php else: ?>
                <!-- Tab 3 Guru: Murid -->
                <a href="<?= url('mobile/murid') ?>" data-nav-tab="murid" class="mobile-nav-item flex-1 flex flex-col items-center gap-0.5 py-1 rounded-2xl <?= ($activeTab ?? '') === 'murid' ? 'is-active text-primary-600 font-bold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                    <div class="nav-pill w-12 h-7 rounded-full flex items-center justify-center">
                        <i data-lucide="graduation-cap" class="w-5 h-5 <?= ($activeTab ?? '') === 'murid' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                    </div>
                    <span class="text-[10.5px] leading-none tracking-tight">Murid</span>
                </a>
            <?php endif; ?>

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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="<?= asset('js/mobile/android-ui.js') ?>?v=<?= file_exists(BASE_PATH . '/public/js/mobile/android-ui.js') ? filemtime(BASE_PATH . '/public/js/mobile/android-ui.js') : 3 ?>"></script>
    <script src="<?= asset('js/mobile/mobile-api.js') ?>?v=<?= file_exists(BASE_PATH . '/public/js/mobile/mobile-api.js') ? filemtime(BASE_PATH . '/public/js/mobile/mobile-api.js') : 3 ?>"></script>
    <script src="<?= asset('js/mobile/lazy-load.js') ?>?v=2"></script>
    <script src="<?= asset('js/pwa.js') ?>?v=3"></script>
    <script src="<?= asset('js/searchable-select.js') ?>?v=<?= file_exists(BASE_PATH . '/public/js/searchable-select.js') ? filemtime(BASE_PATH . '/public/js/searchable-select.js') : 1 ?>"></script>
    <script src="<?= asset('js/mobile/mobile-app.js') ?>?v=<?= file_exists(BASE_PATH . '/public/js/mobile/mobile-app.js') ? filemtime(BASE_PATH . '/public/js/mobile/mobile-app.js') : 3 ?>"></script>

    <script>
        // Open Android-style Bottom Sheet for '+' FAB
        function openAndroidActionSheet() {
            <?php if (!empty($isSupport)): ?>
            AndroidUI.bottomSheet({
                title: 'Aksi Cepat Pegawai',
                subtitle: 'Pilih presensi, jurnal, perizinan & ibadah',
                icon: '⚡',
                iconBg: 'bg-primary-100 text-primary-600',
                content: `
                    <div class="grid grid-cols-3 gap-2.5 pt-1 pb-1">
                        <a href="<?= url('mobile/absen') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📍</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Absen GPS</span>
                        </a>
                        <a href="<?= url('mobile/jurnal') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📝</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Isi Jurnal</span>
                        </a>
                        <a href="<?= url('mobile/rekap-absen') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📊</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Rekap Absen</span>
                        </a>

                        <a href="<?= url('mobile/izin') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📄</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Izin Pegawai</span>
                        </a>
                        <a href="<?= url('mobile/cuti') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">🏖️</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Cuti Pegawai</span>
                        </a>
                        <a href="<?= url('mobile/quran') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📖</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Al-Qur'an</span>
                        </a>

                        <a href="<?= url('mobile/dzikir') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📿</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Dzikir</span>
                        </a>

                        <a href="<?= url('mobile/tasbih') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">✨</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Tasbih</span>
                        </a>

                        <a href="<?= url('mobile/jurnal?tab=riwayat') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📋</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Riwayat Jurnal</span>
                        </a>
                    </div>
                `
            });
            <?php else: ?>
            AndroidUI.bottomSheet({
                title: 'Aksi Cepat Guru',
                subtitle: 'Pilih aktivitas pembelajaran, presensi & perizinan',
                icon: '⚡',
                iconBg: 'bg-primary-100 text-primary-600',
                content: `
                    <div class="grid grid-cols-3 gap-2.5 pt-1 pb-1">
                        <a href="<?= url('mobile/absen') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📍</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Absen GPS</span>
                        </a>
                        <a href="<?= url('mobile/jurnal') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📝</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Isi Jurnal</span>
                        </a>
                        <a href="<?= url('mobile/absensi-kelas') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">👥</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Absen Kelas</span>
                        </a>

                        <!-- Rekapitulasi Presensi & Jurnal PIC -->
                        <a href="<?= url('mobile/rekap-absen') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📊</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Rekap Absen</span>
                        </a>
                        <a href="<?= url('mobile/rekap-jurnal') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📈</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Rekap Jurnal</span>
                        </a>
                        <a href="<?= url('mobile/keterlambatan-siswa') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">⏳</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Terlambat</span>
                        </a>

                        <a href="<?= url('mobile/izin') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📄</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Izin Guru</span>
                        </a>
                        <a href="<?= url('mobile/cuti') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">🏖️</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Cuti Guru</span>
                        </a>
                        <a href="<?= url('mobile/buat-tugas') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">📋</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Buat Tugas</span>
                        </a>

                        <a href="<?= url('mobile/tasbih') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-primary-50/60 hover:bg-primary-50 rounded-2xl border border-primary-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-sm shadow-primary-500/20 text-base font-bold">✨</div>
                            <span class="text-xs font-bold text-slate-800 text-center leading-tight">Tasbih</span>
                        </a>
                    </div>
                `
            });
            <?php endif; ?>
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

        // ============================================================
        // UNIVERSAL MOBILE PULL-TO-REFRESH (Tarik Layar untuk Refresh)
        // ============================================================
        (function initPullToRefresh() {
            const mainView = document.getElementById('mobile-main-view');
            const ptr = document.getElementById('ptr-indicator');
            if (!mainView || !ptr) return;

            let startY = 0;
            let currentY = 0;
            let isPulling = false;
            let isRefreshing = false;
            const threshold = 75;

            // Target elemen yang bisa di-scroll: mainView atau window/document
            function isAtTop() {
                const scrollPos = Math.max(mainView.scrollTop || 0, window.scrollY || 0, document.documentElement.scrollTop || 0);
                return scrollPos <= 0;
            }

            function isAnyModalOpen() {
                return !!document.querySelector('.fixed:not(.hidden):not(#mobile-bottom-nav):not(#spa-progress-bar):not(#toast-container)');
            }

            mainView.addEventListener('touchstart', function(e) {
                if (isRefreshing) return;
                if (isAtTop() && !isAnyModalOpen()) {
                    startY = e.touches[0].pageY;
                    isPulling = true;
                }
            }, { passive: true });

            mainView.addEventListener('touchmove', function(e) {
                if (!isPulling || isRefreshing) return;
                currentY = e.touches[0].pageY;
                const diff = currentY - startY;

                if (diff > 0 && isAtTop()) {
                    const distance = Math.min(diff * 0.42, 65);
                    const progress = Math.min(diff / threshold, 1);
                    ptr.style.transform = `translateX(-50%) translateY(${distance}px)`;
                    ptr.style.opacity = String(progress);
                    ptr.classList.add('active');
                } else {
                    ptr.classList.remove('active');
                    ptr.style.opacity = '0';
                }
            }, { passive: true });

            mainView.addEventListener('touchend', function() {
                if (!isPulling || isRefreshing) return;
                const diff = currentY - startY;

                if (diff >= threshold && isAtTop()) {
                    isRefreshing = true;
                    ptr.classList.add('refreshing');
                    ptr.style.transform = 'translateX(-50%) translateY(55px)';
                    ptr.style.opacity = '1';

                    if (typeof AndroidUI !== 'undefined' && typeof AndroidUI.toast === 'function') {
                        AndroidUI.toast('Memperbarui data...', 'info');
                    }

                    setTimeout(() => {
                        try {
                            const curUrl = new URL(window.location.href);
                            curUrl.searchParams.set('_r', Date.now());
                            window.location.href = curUrl.toString();
                        } catch (e) {
                            window.location.reload();
                        }
                    }, 300);
                } else {
                    ptr.classList.remove('active', 'refreshing');
                    ptr.style.transform = 'translateX(-50%) translateY(-55px)';
                    ptr.style.opacity = '0';
                }

                isPulling = false;
                startY = 0;
                currentY = 0;
            }, { passive: true });
        })();
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
