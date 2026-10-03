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
            display: none;
            animation: slideUp 0.4s ease-out;
        }
        @keyframes slideUp { from { transform: translateX(-50%) translateY(100px); opacity: 0; } to { transform: translateX(-50%) translateY(0); opacity: 1; } }

        /* Sembunyikan elemen install jika sudah terpasang sebagai aplikasi (standalone) */
        @media all and (display-mode: standalone) {
            .pwa-install-trigger { display: none !important; }
        }
    </style>
</head>
<body class="h-full flex flex-col overflow-hidden bg-slate-50 relative">

    <!-- Splash Screen Loader -->
    <div id="mobile-splash-loader" class="fixed inset-0 z-[9999] bg-blue-600 flex flex-col items-center justify-center transition-opacity duration-500">
        <div class="relative w-16 h-16 mb-6 flex items-center justify-center bg-white rounded-2xl shadow-xl p-3 animate-bounce">
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
    
    <!-- PWA Install Banner Floating (Auto-triggered when prompt is available) -->
    <div id="pwa-install-banner" class="pwa-install-banner pwa-install-trigger">
        <div class="flex items-center gap-3 text-white">
            <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="download" class="w-6 h-6"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-sm">Install Sarpras Mobile</p>
                <p id="pwa-install-desc" class="text-xs text-white/80">Pasang ke Layar Utama HP Anda</p>
            </div>
            <button type="button" onclick="triggerPwaInstall()" class="px-4 py-2 bg-white text-blue-600 font-bold text-xs rounded-xl shadow active:scale-95 transition-transform shrink-0">
                Install
            </button>
            <button type="button" onclick="dismissPwaBanner()" class="p-1 text-white/70 hover:text-white shrink-0">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <!-- PWA Install Modal Guide (Universal for Android & iOS) -->
    <div id="pwa-install-modal" class="fixed inset-0 z-[9998] flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 opacity-0 pointer-events-none transition-all duration-200">
        <div class="bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl space-y-4 transform translate-y-8 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center font-bold shadow-sm">
                        <i data-lucide="download" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Install Sarpras Mobile</h3>
                        <p class="text-xs text-slate-500">Aplikasi PWA Resmi Portal BIP</p>
                    </div>
                </div>
                <button type="button" onclick="closePwaModal()" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div id="pwa-native-action" class="hidden">
                <button type="button" onclick="executeNativeInstall()" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 transition-all active:scale-[0.98]">
                    <i data-lucide="download" class="w-5 h-5"></i> Install Sekarang (1-Klik)
                </button>
            </div>

            <!-- Petunjuk Manual Jika Browser Butuh Langkah Tambahan -->
            <div id="pwa-manual-guide" class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs text-slate-600">
                <p class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                    <i data-lucide="info" class="w-4 h-4 text-blue-600"></i> Cara Pasang di Layar Utama HP:
                </p>
                <div class="space-y-2">
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200/60">
                        <span class="font-bold text-slate-800 block mb-0.5">📱 Android (Chrome / Edge):</span>
                        <span>Ketuk menu titik tiga (<strong>⋮</strong>) di kanan atas browser &rarr; pilih <strong>"Install aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama"</strong>.</span>
                    </div>
                    <div class="p-2.5 bg-white rounded-xl border border-slate-200/60">
                        <span class="font-bold text-slate-800 block mb-0.5">🍎 iPhone / iPad (Safari):</span>
                        <span>Ketuk tombol Bagikan (<strong>Share <svg class="inline w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg></strong>) di bawah &rarr; pilih <strong>"Tambah ke Layar Utama"</strong>.</span>
                    </div>
                </div>
            </div>

            <button type="button" onclick="closePwaModal()" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition">
                Tutup
            </button>
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
                <button type="button" id="btn-header-install" onclick="triggerPwaInstall()" class="pwa-install-trigger px-2.5 py-1 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all active:scale-95 shadow-sm">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Install</span>
                </button>
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
            // Unregister old conflicting service workers first
            navigator.serviceWorker.getRegistrations().then(function(regs) {
                regs.forEach(function(reg) {
                    if (reg.active && reg.active.scriptURL && !reg.active.scriptURL.includes('sw-sarpras.js')) {
                        reg.unregister();
                        console.log('[PWA] Unregistered old SW:', reg.active.scriptURL);
                    }
                });
            });
            // Register the sarpras SW - use absolute path from root
            navigator.serviceWorker.register('<?= url('sw-sarpras.js') ?>')
                .then(function(reg) {
                    console.log('[PWA] SW registered OK, scope:', reg.scope);
                })
                .catch(function(err) {
                    console.error('[PWA] SW registration FAILED:', err);
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
        // PWA INSTALL SYSTEM (PROMPT + UNIVERSAL MODAL)
        // ============================================================
        let deferredPrompt = null;
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches 
                          || window.navigator.standalone === true;

        function updateInstallUiState() {
            if (isStandalone) {
                document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.add('hidden'));
                const banner = document.getElementById('pwa-install-banner');
                if (banner) banner.style.display = 'none';
            }
        }
        updateInstallUiState();

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            console.log('[PWA] beforeinstallprompt captured successfully!');

            // Tampilkan tombol dan banner
            if (!isStandalone) {
                const banner = document.getElementById('pwa-install-banner');
                const dismissed = sessionStorage.getItem('pwa_banner_dismissed');
                if (banner && !dismissed) {
                    banner.style.display = 'block';
                    if (window.lucide) lucide.createIcons();
                }
                const nativeAction = document.getElementById('pwa-native-action');
                if (nativeAction) nativeAction.classList.remove('hidden');
            }
        });

        window.triggerPwaInstall = async function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log('[PWA] User choice outcome:', outcome);
                if (outcome === 'accepted') {
                    deferredPrompt = null;
                    dismissPwaBanner();
                    closePwaModal();
                }
            } else {
                openPwaModal();
            }
        };

        window.executeNativeInstall = async function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log('[PWA] Native modal install outcome:', outcome);
                if (outcome === 'accepted') {
                    deferredPrompt = null;
                    dismissPwaBanner();
                    closePwaModal();
                }
            }
        };

        window.openPwaModal = function() {
            const m = document.getElementById('pwa-install-modal');
            if (!m) return;
            const nativeAction = document.getElementById('pwa-native-action');
            if (nativeAction) {
                if (deferredPrompt) {
                    nativeAction.classList.remove('hidden');
                } else {
                    nativeAction.classList.add('hidden');
                }
            }
            m.classList.remove('opacity-0', 'pointer-events-none');
            m.firstElementChild.classList.remove('translate-y-8');
            if (window.lucide) lucide.createIcons();
        };

        window.closePwaModal = function() {
            const m = document.getElementById('pwa-install-modal');
            if (!m) return;
            m.classList.add('opacity-0', 'pointer-events-none');
            m.firstElementChild.classList.add('translate-y-8');
        };

        window.dismissPwaBanner = function() {
            const banner = document.getElementById('pwa-install-banner');
            if (banner) banner.style.display = 'none';
            sessionStorage.setItem('pwa_banner_dismissed', '1');
        };

        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App successfully installed!');
            dismissPwaBanner();
            closePwaModal();
            deferredPrompt = null;
            document.querySelectorAll('.pwa-install-trigger').forEach(el => el.classList.add('hidden'));
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
