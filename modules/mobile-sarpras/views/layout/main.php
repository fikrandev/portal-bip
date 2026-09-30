<!DOCTYPE html>
<html lang="id" class="h-full antialiased bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <title><?= e($pageTitle ?? 'Sarpras Mobile') ?></title>
    
    <link rel="manifest" href="<?= url('mobile-sarpras/manifest.json') ?>">
    <link rel="apple-touch-icon" href="<?= url('pwa-icon.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/html5-qrcode.min.js"></script>

    <style>
        /* Fix for unwanted text nodes (BOM) pushing layout down */
        body { font-size: 0px !important; line-height: 0 !important; font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
        body > * { font-size: 16px; line-height: 1.5; }
        
        .mobile-safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0px) !important; }
        .mobile-safe-top { padding-top: env(safe-area-inset-top, 0px) !important; }
        
        /* Hide scrollbar for clean mobile look */
        ::-webkit-scrollbar { width: 0px; background: transparent; }
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
    <main class="flex-1 overflow-y-auto w-full max-w-md mx-auto bg-slate-50 relative">
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

        // Push Notification Subscription Logic
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
