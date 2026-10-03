<!DOCTYPE html>
<html lang="id" class="h-full antialiased bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#2563eb">
    <title><?= htmlspecialchars($pageTitle ?? 'Login Sarpras') ?></title>
    
    <link rel="manifest" href="<?= url('mobile-sarpras/manifest.json') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="h-full flex flex-col items-center justify-center bg-slate-50 relative p-4">

    <div class="w-full max-w-sm bg-white rounded-3xl shadow-xl p-8 relative z-10 overflow-hidden">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center shadow-inner">
                <?php 
                    $logoUrl = '';
                    if (defined('SYS_APP_LOGO') && SYS_APP_LOGO) {
                        $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
                    }
                ?>
                <?php if ($logoUrl): ?>
                    <img src="<?= $logoUrl ?>" alt="Logo" class="w-10 h-10 object-contain">
                <?php else: ?>
                    <i data-lucide="layout-dashboard" class="w-8 h-8"></i>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Sarpras Mobile</h1>
            <p class="text-slate-500 text-sm mt-1">Silakan login untuk melanjutkan</p>
        </div>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="mb-4 bg-red-100 text-red-600 p-3 rounded-lg text-sm text-center font-medium">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <form action="<?= url('mobile-sarpras/login') ?>" method="POST" autocomplete="on">
            <?= CSRF::field() ?>
            
            <div class="mb-4">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </span>
                    <input type="text" 
                           name="username" 
                           placeholder="Username"
                           required
                           class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-medium">
                </div>
            </div>

            <div class="mb-8">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </span>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Password"
                           required
                           class="w-full pl-12 pr-12 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-400 outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm font-medium">
                    <button type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none">
                        <i data-lucide="eye" id="icon-eye" class="w-5 h-5"></i>
                        <i data-lucide="eye-off" id="icon-eye-off" class="w-5 h-5 hidden"></i>
                    </button>
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transform active:scale-95 transition-all text-sm">
                Masuk
            </button>

            <!-- Tombol Install PWA di Halaman Login -->
            <div id="login-pwa-install-wrapper" class="mt-4 pt-4 border-t border-slate-100 text-center hidden">
                <button type="button" id="btn-login-install" class="w-full py-2.5 px-4 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all active:scale-95">
                    <i data-lucide="download" class="w-4 h-4 text-blue-600"></i> Pasang Aplikasi di Layar Utama HP
                </button>
            </div>
        </form>
    </div>
    
    <!-- Background Decoration -->
    <div class="fixed top-0 left-0 w-full h-1/2 bg-blue-600 rounded-b-[4rem] z-0"></div>

    <script>
        lucide.createIcons();
        
        function togglePassword() {
            var input = document.getElementById('password');
            var iconEye = document.getElementById('icon-eye');
            var iconEyeOff = document.getElementById('icon-eye-off');
            
            if (input.type === 'password') {
                input.type = 'text';
                iconEye.classList.add('hidden');
                iconEyeOff.classList.remove('hidden');
            } else {
                input.type = 'password';
                iconEye.classList.remove('hidden');
                iconEyeOff.classList.add('hidden');
            }
        }
        
        // PWA Install on Login
        let loginDeferredPrompt = null;
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            loginDeferredPrompt = e;
            if (!isStandalone) {
                const wrap = document.getElementById('login-pwa-install-wrapper');
                if (wrap) {
                    wrap.classList.remove('hidden');
                    lucide.createIcons();
                }
            }
        });

        const btnLoginInstall = document.getElementById('btn-login-install');
        if (btnLoginInstall) {
            btnLoginInstall.addEventListener('click', async () => {
                if (loginDeferredPrompt) {
                    loginDeferredPrompt.prompt();
                    const { outcome } = await loginDeferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        loginDeferredPrompt = null;
                        document.getElementById('login-pwa-install-wrapper').classList.add('hidden');
                    }
                } else {
                    alert('Untuk memasang di HP: Buka menu browser (titik 3 di Android atau tombol Share di iPhone Safari) lalu pilih "Tambahkan ke Layar Utama".');
                }
            });
        }

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= url('sw-sarpras.js') ?>')
                .then(function(reg) {
                    console.log('[PWA] SW registered OK on login:', reg.scope);
                })
                .catch(function(err) {
                    console.error('[PWA] SW registration FAILED:', err);
                });
        }
    </script>
</body>
</html>
