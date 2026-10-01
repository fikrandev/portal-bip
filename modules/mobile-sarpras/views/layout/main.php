<!DOCTYPE html>
<html lang="id" class="h-full antialiased bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= e($pageTitle ?? 'Sarpras Mobile') ?></title>
    
    <link rel="manifest" href="<?= url('mobile-sarpras/manifest.json') ?>">
    <link rel="apple-touch-icon" href="<?= url('pwa-icon.png') ?>?s=192">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <style>
        /* Fix for unwanted text nodes (BOM) pushing layout down */
        body { font-size: 0px !important; line-height: 0 !important; font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        body > * { font-size: 16px; line-height: 1.5; }
        
        .mobile-safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0px) !important; }
        .mobile-safe-top { padding-top: env(safe-area-inset-top, 0px) !important; }
        
        /* Hide scrollbar for clean mobile look */
        ::-webkit-scrollbar { width: 0px; background: transparent; }

        /* Pull to Refresh */
        .ptr-indicator {
            position: absolute; top: -50px; left: 50%; transform: translateX(-50%);
            width: 40px; height: 40px; border-radius: 50%;
            background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.2s, opacity 0.2s; opacity: 0; z-index: 50;
        }
        .ptr-indicator.active { opacity: 1; }
        .ptr-indicator.refreshing svg { animation: spin 0.8s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* PWA Install Banner */
        .pwa-install-banner {
            position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
            z-index: 9998; width: calc(100% - 2rem); max-width: 400px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 1rem; padding: 1rem 1.25rem;
            box-shadow: 0 10px 40px rgba(37, 99, 235, 0.4);
            display: none; /* hidden until beforeinstallprompt fires */
            animation: slideUp 0.4s ease-out;
        }
        @keyframes slideUp { from { transform: translateX(-50%) translateY(100px); opacity: 0; } to { transform: translateX(-50%) translateY(0); opacity: 1; } }
    </style>
</head>
<body class="h-full flex flex-col overflow-hidden bg-slate-50 relative">

    <!-- Splash Screen Loader -->
    <div id="mobile-splash-loader" class="fixed inset-0 z-[9999] bg-blue-600 flex flex-col items-center justify-center transition-opacity duration-500">
        <div class="relative w-24 h-24 mb-6 flex items-center justify-center bg-white rounded-3xl shadow-2xl p-4 animate-bounce">
            <?php 
                $logoUrl = '';
                if (defined('SYS_APP_LOGO') && SYS_APP_LOGO) {
                    $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
                }
            ?>
            <?php if ($logoUrl): ?>
                <img src="<?= $logoUrl ?>" alt="Logo" class="w-full h-full object-contain">
            <?php else: ?>
                <i data-lucide="layout-dashboard" class="w-12 h-12 text-blue-600"></i>
            <?php endif; ?>
        </div>
        <h2 class="text-white font-bold text-xl tracking-wide mb-2"><?= e($pageTitle ?? 'Sarpras Mobile') ?></h2>
        <div class="flex items-center gap-1.5">
            <div class="w-2 h-2 bg-white rounded-full animate-ping" style="animation-delay: 0s;"></div>
            <div class="w-2 h-2 bg-white rounded-full animate-ping" style="animation-delay: 0.2s;"></div>
            <div class="w-2 h-2 bg-white rounded-full animate-ping" style="animation-delay: 0.4s;"></div>
        </div>
    </div>
    
    <!-- PWA Install Banner (Auto Install for Chrome/Android) -->
    <div id="pwa-install-banner" class="pwa-install-banner">
        <div class="flex items-center gap-3 text-white">
            <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="download" class="w-6 h-6"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm">Install Sarpras Mobile</p>
                <p id="pwa-install-desc" class="text-xs text-white/70">Akses cepat langsung dari layar utama</p>
            </div>
            <button id="pwa-install-btn" class="px-4 py-2 bg-white text-blue-600 font-bold text-sm rounded-xl shadow flex-shrink-0 active:scale-95 transition-transform">
                Install
            </button>
            <button id="pwa-install-close" class="p-1 text-white/60 hover:text-white flex-shrink-0">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <!-- PWA Install Guide for iOS (manual) -->
    <div id="pwa-ios-guide" class="pwa-install-banner" style="display:none;">
        <div class="text-white">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i data-lucide="smartphone" class="w-6 h-6"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-sm">Install Sarpras Mobile</p>
                    <p class="text-xs text-white/70">Ikuti langkah di bawah ini</p>
                </div>
                <button id="pwa-ios-close" class="p-1 text-white/60 hover:text-white flex-shrink-0">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="bg-white/10 rounded-lg p-3 text-xs space-y-1">
                <p>1. Ketuk ikon <strong>⋮</strong> (titik tiga) atau <strong>Share ↑</strong></p>
                <p>2. Pilih <strong>"Tambahkan ke Layar Utama"</strong></p>
                <p>3. Ketuk <strong>"Tambahkan"</strong></p>
            </div>
        </div>
    </div>

    <!-- Top App Bar -->
    <header class="bg-blue-600 text-white shadow-md z-40 flex-shrink-0 mobile-safe-top">
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <?php if (($activeTab ?? '') !== 'beranda'): ?>
                    <button onclick="history.back()" class="p-1 rounded-full hover:bg-blue-700 transition">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </button>
                <?php endif; ?>
                <h1 class="text-lg font-bold truncate"><?= e($pageTitle ?? 'Sarpras Mobile') ?></h1>
            </div>
            <div class="flex items-center gap-2">
                <button id="btn-notification" class="p-1 rounded-full relative hover:bg-blue-700 transition">
                    <i data-lucide="bell" class="w-6 h-6"></i>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full animate-ping"></span>
                    <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="flex-1 overflow-y-auto w-full max-w-md mx-auto bg-slate-50 relative">
        <!-- Pull to Refresh Indicator -->
        <div id="ptr-indicator" class="ptr-indicator">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 4 23 10 17 10"></polyline>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
            </svg>
        </div>
        <div class="pb-24">
            <?= $content ?? '' ?>
        </div>
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 w-full max-w-md left-1/2 -translate-x-1/2 bg-white border-t border-slate-200 z-40 mobile-safe-bottom shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] rounded-t-3xl">
        <div class="flex justify-around items-center px-2 py-2">
            <!-- Beranda -->
            <a href="<?= url('mobile-sarpras') ?>" class="flex flex-col items-center p-2 rounded-2xl w-16 <?= ($activeTab === 'beranda') ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' ?>">
                <div class="<?= ($activeTab === 'beranda') ? 'bg-blue-100 p-1.5 rounded-xl' : '' ?> transition-all">
                    <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-medium mt-1">Dashboard</span>
            </a>
            
            <!-- Inventaris / List Data -->
            <a href="<?= url('mobile-sarpras/inventaris') ?>" class="flex flex-col items-center p-2 rounded-2xl w-16 <?= ($activeTab === 'inventaris' || $activeTab === 'input') ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' ?>">
                <div class="<?= ($activeTab === 'inventaris' || $activeTab === 'input') ? 'bg-blue-100 p-1.5 rounded-xl' : '' ?> transition-all">
                    <i data-lucide="package" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-medium mt-1">Inventaris</span>
            </a>

            <!-- Scan QR Center Floating Button -->
            <div class="relative -top-5">
                <a href="<?= url('mobile-sarpras/scan') ?>" class="flex flex-col items-center justify-center w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg shadow-blue-500/40 hover:bg-blue-700 transition-transform hover:scale-105 active:scale-95 border-4 border-slate-50">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </a>
            </div>

            <!-- Maintenance -->
            <a href="<?= url('mobile-sarpras/maintenance') ?>" class="flex flex-col items-center p-2 rounded-2xl w-16 <?= ($activeTab === 'maintenance') ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' ?>">
                <div class="<?= ($activeTab === 'maintenance') ? 'bg-blue-100 p-1.5 rounded-xl' : '' ?> transition-all">
                    <i data-lucide="wrench" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-medium mt-1">Perbaikan</span>
            </a>

            <!-- Peminjaman -->
            <a href="<?= url('mobile-sarpras/peminjaman') ?>" class="flex flex-col items-center p-2 rounded-2xl w-16 <?= ($activeTab === 'peminjaman') ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600' ?>">
                <div class="<?= ($activeTab === 'peminjaman') ? 'bg-blue-100 p-1.5 rounded-xl' : '' ?> transition-all">
                    <i data-lucide="arrow-right-left" class="w-6 h-6"></i>
                </div>
                <span class="text-[10px] font-medium mt-1">Pinjam</span>
            </a>
        </div>
    </nav>

    <script>
        // Remove Splash Screen Loader when everything is loaded
        window.addEventListener('load', function() {
            const loader = document.getElementById('mobile-splash-loader');
            if (loader) {
                // Add a small delay to make it feel deliberate
                setTimeout(() => {
                    loader.style.opacity = '0';
                    setTimeout(() => {
                        loader.style.display = 'none';
                    }, 500);
                }, 300);
            }
        });

        lucide.createIcons();

        // ============================================================
        // SERVICE WORKER REGISTRATION
        // ============================================================
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= url("sw-sarpras.js") ?>')
                .then(reg => {
                    console.log('[PWA] Service Worker registered, scope:', reg.scope);
                })
                .catch(err => {
                    console.error('[PWA] Service Worker registration failed:', err);
                });
        }

        // ============================================================
        // PULL TO REFRESH
        // ============================================================
        (function() {
            const mainContent = document.getElementById('main-content');
            const ptrIndicator = document.getElementById('ptr-indicator');
            let startY = 0;
            let currentY = 0;
            let pulling = false;
            const threshold = 80;

            if (mainContent && ptrIndicator) {
                mainContent.addEventListener('touchstart', function(e) {
                    if (mainContent.scrollTop <= 0) {
                        startY = e.touches[0].pageY;
                        pulling = true;
                    }
                }, { passive: true });

                mainContent.addEventListener('touchmove', function(e) {
                    if (!pulling) return;
                    currentY = e.touches[0].pageY;
                    const diff = currentY - startY;
                    if (diff > 0 && mainContent.scrollTop <= 0) {
                        const progress = Math.min(diff / threshold, 1);
                        ptrIndicator.style.transform = `translateX(-50%) translateY(${diff * 0.5}px)`;
                        ptrIndicator.style.opacity = progress;
                        ptrIndicator.classList.add('active');
                    }
                }, { passive: true });

                mainContent.addEventListener('touchend', function() {
                    if (!pulling) return;
                    const diff = currentY - startY;
                    if (diff >= threshold && mainContent.scrollTop <= 0) {
                        ptrIndicator.classList.add('refreshing');
                        ptrIndicator.style.transform = 'translateX(-50%) translateY(50px)';
                        setTimeout(() => {
                            window.location.reload();
                        }, 400);
                    } else {
                        ptrIndicator.classList.remove('active');
                        ptrIndicator.style.transform = 'translateX(-50%) translateY(-50px)';
                        ptrIndicator.style.opacity = '0';
                    }
                    pulling = false;
                    startY = 0;
                    currentY = 0;
                }, { passive: true });
            }
        })();

        // ============================================================
        // PWA INSTALL — AUTO SHOW ON PAGE LOAD
        // ============================================================
        let deferredPrompt = null;
        let promptFired = false;
        const installBanner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('pwa-install-btn');
        const installClose = document.getElementById('pwa-install-close');
        const iosGuide = document.getElementById('pwa-ios-guide');
        const iosClose = document.getElementById('pwa-ios-close');

        // Check if already installed as PWA (standalone mode)
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches 
                          || window.navigator.standalone === true;
        // Check if user dismissed today
        const dismissKey = 'pwa_install_dismissed';
        const lastDismiss = localStorage.getItem(dismissKey);
        const dismissedToday = lastDismiss && (Date.now() - parseInt(lastDismiss)) < 86400000; // 24 hours

        // Intercept beforeinstallprompt (Chrome/Edge Android)
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            promptFired = true;

            // Show native install banner immediately
            if (!isStandalone && !dismissedToday && installBanner) {
                installBanner.style.display = 'block';
                lucide.createIcons();
            }
            console.log('[PWA] beforeinstallprompt fired');
        });

        // If beforeinstallprompt doesn't fire within 2s, show manual guide
        if (!isStandalone && !dismissedToday) {
            setTimeout(() => {
                if (!promptFired) {
                    // Show manual guide (iOS Safari / Firefox / etc)
                    if (iosGuide) {
                        iosGuide.style.display = 'block';
                        lucide.createIcons();
                    }
                    console.log('[PWA] No beforeinstallprompt, showing manual guide');
                }
            }, 2000);
        }

        // Install button click (native prompt)
        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log('[PWA] User choice:', outcome);
                    deferredPrompt = null;
                    if (installBanner) installBanner.style.display = 'none';
                } else {
                    // No native prompt available, show manual guide
                    if (installBanner) installBanner.style.display = 'none';
                    if (iosGuide) {
                        iosGuide.style.display = 'block';
                        lucide.createIcons();
                    }
                }
            });
        }

        // Close buttons
        if (installClose) {
            installClose.addEventListener('click', () => {
                if (installBanner) installBanner.style.display = 'none';
                localStorage.setItem(dismissKey, Date.now().toString());
            });
        }
        if (iosClose) {
            iosClose.addEventListener('click', () => {
                if (iosGuide) iosGuide.style.display = 'none';
                localStorage.setItem(dismissKey, Date.now().toString());
            });
        }

        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed');
            if (installBanner) installBanner.style.display = 'none';
            if (iosGuide) iosGuide.style.display = 'none';
            deferredPrompt = null;
        });

        // ============================================================
        // PUSH NOTIFICATION
        // ============================================================
        document.getElementById('btn-notification').addEventListener('click', async () => {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                alert('Push notification tidak didukung di browser ini.');
                return;
            }
            
            try {
                const permission = await Notification.requestPermission();
                if (permission === 'granted') {
                    const registration = await navigator.serviceWorker.ready;
                    // Replace VAPID_PUBLIC_KEY with actual key if available
                    // const subscription = await registration.pushManager.subscribe({
                    //     userVisibleOnly: true,
                    //     applicationServerKey: 'VAPID_PUBLIC_KEY'
                    // });
                    
                    // Mock success for now
                    alert('Notifikasi berhasil diaktifkan!');
                    // Send to backend
                    // fetch('<?= url("mobile-sarpras/action/subscribe-push") ?>', {
                    //     method: 'POST',
                    //     body: JSON.stringify(subscription)
                    // });
                }
            } catch (error) {
                console.error('Error subscribing to push:', error);
            }
        });
    </script>
</body>
</html>
