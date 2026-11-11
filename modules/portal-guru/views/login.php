<!DOCTYPE html>
<html lang="id" class="h-full antialiased bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#1d4ed8">
    <title><?= htmlspecialchars($pageTitle ?? 'Portal BIP') ?></title>
    <meta name="application-name" content="Portal BIP">
    <meta name="apple-mobile-web-app-title" content="Portal BIP">
    
    <!-- Favicon -->
    <?= function_exists('renderFaviconTags') ? renderFaviconTags() : '' ?>
    
    <!-- PWA Web App Manifest -->
    <link rel="manifest" href="<?= url('mobile/manifest.json') ?>">
    <link rel="apple-touch-icon" href="<?= url('pwa-icon.png?size=192') ?>">
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
                            console.log('[PWA Cleanup] Removed rogue SW:', script, scope);
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
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            -webkit-tap-highlight-color: transparent; 
        }
        .press-bounce {
            transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .press-bounce:active {
            transform: scale(0.96);
        }
    </style>
</head>
<body class="h-full flex flex-col items-center justify-center bg-slate-100 relative p-4 selection:bg-primary-600 selection:text-white">

    <!-- Top Blue Wave Decoration -->
    <div class="fixed top-0 left-0 w-full h-[38vh] bg-gradient-to-br from-primary-800 via-primary-700 to-indigo-800 rounded-b-[3.5rem] shadow-lg z-0"></div>

    <!-- Auto-Restore Session Overlay (PWA Standalone & Browser Handshake) -->
    <div id="session-restore-loader" class="fixed inset-0 z-50 bg-slate-950/40 backdrop-blur-sm flex flex-col items-center justify-center hidden">
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-6 shadow-2xl flex flex-col items-center max-w-xs text-center border border-slate-100">
            <div class="w-14 h-14 bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center mb-3 shadow-inner">
                <i data-lucide="loader-2" class="w-7 h-7 animate-spin"></i>
            </div>
            <h4 class="font-bold text-slate-800 text-base">Menghubungkan Akun...</h4>
            <p class="text-xs text-slate-500 mt-1">Memulihkan sesi login guru, mohon tunggu sebentar</p>
        </div>
    </div>

    <!-- Main Login Card Container -->
    <div class="w-full max-w-sm bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-7 sm:p-8 relative z-10 border border-slate-100 overflow-hidden">
        
        <!-- App Logo & Icon Header -->
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 bg-gradient-to-tr from-primary-600 to-sky-400 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/25 p-3">
                <?php 
                    $logoUrl = '';
                    if (defined('SYS_APP_LOGO') && SYS_APP_LOGO) {
                        $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
                    }
                ?>
                <?php if ($logoUrl): ?>
                    <img src="<?= $logoUrl ?>" alt="Logo" class="w-10 h-10 object-contain drop-shadow-sm">
                <?php else: ?>
                    <i data-lucide="graduation-cap" class="w-9 h-9 stroke-[2.2]"></i>
                <?php endif; ?>
            </div>
        </div>

        <!-- Greeting & Title -->
        <div class="text-center mb-6">
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Portal Guru BIP</h1>
            <p class="text-slate-500 text-xs mt-1">Presensi, Jurnal & Pembelajaran Digital</p>
        </div>

        <?php if (!empty($currentUser)): ?>
            <!-- Active Session Notice -->
            <div class="mb-5 p-4 bg-primary-50/80 border border-primary-200/80 rounded-2xl text-xs space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] text-primary-700 font-medium">Sesi Aktif:</span>
                    <span class="px-2 py-0.5 bg-primary-600 text-white rounded-full text-[10px] font-bold">Terhubung</span>
                </div>
                <p class="font-black text-slate-900 text-sm leading-tight"><?= htmlspecialchars($currentUser['full_name']) ?></p>
                <p class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($currentUser['email'] ?? $currentUser['username']) ?></p>
                <div class="pt-1.5 flex gap-2">
                    <a href="<?= url('mobile') ?>" class="flex-1 py-2 px-3 bg-primary-600 hover:bg-primary-700 text-white text-center font-bold rounded-xl text-xs press-bounce shadow-xs">
                        Buka Beranda
                    </a>
                    <a href="<?= url('mobile/login?switch=1') ?>" class="py-2 px-3 bg-white text-rose-600 hover:bg-rose-50 border border-rose-200 text-center font-bold rounded-xl text-xs press-bounce">
                        Ganti Akun
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-700 px-3.5 py-2.5 rounded-2xl text-xs flex items-center gap-2 font-medium">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-500 shrink-0"></i>
                <span class="flex-1"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-3.5 py-2.5 rounded-2xl text-xs flex items-center gap-2 font-medium">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 shrink-0"></i>
                <span class="flex-1"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="<?= url('mobile/login') ?>" method="POST" autocomplete="on" id="form-mobile-login" onsubmit="handleFormSubmit(this)">
            <?= CSRF::field() ?>
            
            <!-- Username / Email Field -->
            <div class="mb-3.5">
                <label for="username" class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Username / Email</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i data-lucide="user" class="w-4 h-4"></i>
                    </span>
                    <input type="text" 
                           id="username"
                           name="username" 
                           placeholder="Masukkan username atau email"
                           required
                           autofocus
                           autocomplete="username"
                           class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 placeholder:text-slate-400 outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all text-xs font-semibold">
                </div>
            </div>

            <!-- Password Field -->
            <div class="mb-4">
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 ml-1">Password</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i data-lucide="lock" class="w-4 h-4"></i>
                    </span>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Masukkan password"
                           required
                           autocomplete="current-password"
                           class="w-full pl-11 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-slate-900 placeholder:text-slate-400 outline-none focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all text-xs font-semibold">
                    <button type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1">
                        <i data-lucide="eye" id="icon-eye" class="w-4 h-4"></i>
                        <i data-lucide="eye-off" id="icon-eye-off" class="w-4 h-4 hidden"></i>
                    </button>
                </div>
            </div>

            <!-- Permanent Session Badge Info -->
            <div class="mb-5 flex items-center justify-between bg-primary-50/70 border border-primary-100 px-3.5 py-2.5 rounded-2xl text-[11px] text-primary-800 font-medium">
                <span class="flex items-center gap-1.5 font-semibold">
                    <i data-lucide="shield-check" class="w-4 h-4 text-primary-600"></i>
                    Ingat Sesi Selamanya
                </span>
                <span class="bg-primary-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold shadow-xs">Aktif</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    id="btn-login-submit"
                    class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-2xl shadow-lg shadow-primary-600/30 press-bounce flex items-center justify-center gap-2 transition-all text-sm tracking-wide">
                <span>Masuk ke Portal Guru</span>
                <i data-lucide="arrow-right" class="w-4 h-4 stroke-[2.5]"></i>
            </button>
            
            <p class="text-[11px] text-slate-400 text-center mt-3 leading-tight">
                Gunakan akun guru yang didaftarkan di Pengaturan Akun Portal.
            </p>

            <!-- Install PWA on Home Screen -->
            <div id="login-pwa-install-wrapper" class="mt-4 pt-4 border-t border-slate-100 text-center hidden">
                <button type="button" id="btn-login-install" class="w-full py-2.5 px-4 bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold rounded-2xl text-xs flex items-center justify-center gap-2 transition-all press-bounce">
                    <i data-lucide="download" class="w-4 h-4 text-primary-600"></i> Pasang Aplikasi Portal BIP
                </button>
            </div>
        </form>
    </div>

    <!-- Footer Copyright -->
    <p class="relative z-10 text-xs text-slate-400 mt-6 text-center font-medium">
        &copy; <?= date('Y') ?> <?= defined('SYS_APP_NAME') && SYS_APP_NAME ? htmlspecialchars(SYS_APP_NAME) : 'Bina Insan Palu' ?>
    </p>

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

        function handleFormSubmit(form) {
            const btn = document.getElementById('btn-login-submit');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Memverifikasi...</span>`;
                lucide.createIcons();
            }
        }

        // ============================================================
        // AUTO-RESTORE PERSISTENT SESSION (Login Sekali untuk Selamanya)
        // ============================================================
        (function checkAutoLogin() {
            const urlParams = new URLSearchParams(window.location.search);
            const isLoggedOut = urlParams.get('logged_out') === '1';
            const isSwitch = urlParams.get('switch') === '1';
            const isRestore = urlParams.get('restore') === '1';

            if (isLoggedOut || isSwitch) {
                // Pengguna menekan logout atau ganti akun, bersihkan token lokal
                try {
                    localStorage.removeItem('portal_guru_token');
                } catch (e) {}
                window.history.replaceState({}, document.title, window.location.pathname);
                return;
            }

            // Hanya auto-restore dari localStorage jika redirect resmi via ?restore=1 (misal cookie terhapus di PWA standalone)
            if (!isRestore) {
                return;
            }

            // Periksa token tersimpan di localStorage
            let savedToken = null;
            try {
                savedToken = localStorage.getItem('portal_guru_token');
            } catch (e) {}

            if (savedToken) {
                const loader = document.getElementById('session-restore-loader');
                if (loader) {
                    loader.classList.remove('hidden');
                    if (window.lucide) lucide.createIcons();
                }

                fetch('<?= url("mobile/api/token-login") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ token: savedToken })
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        window.location.href = data.redirect || '<?= url("mobile") ?>';
                    } else {
                        // Token kedaluwarsa atau sudah dihapus di database
                        try {
                            localStorage.removeItem('portal_guru_token');
                        } catch (e) {}
                        if (loader) loader.classList.add('hidden');
                    }
                })
                .catch(() => {
                    if (loader) loader.classList.add('hidden');
                });
            }
        })();

        // ============================================================
        // PWA Install Prompt Banner
        // ============================================================
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
                    alert('Untuk memasang di HP: Buka menu browser (titik 3 di Android atau tombol Bagikan di iPhone Safari) lalu pilih "Tambahkan ke Layar Utama".');
                }
            });
        }

        // Register Portal Guru SW strictly within mobile scope
        if ('serviceWorker' in navigator) {
            const guruScope = '<?= url('mobile/') ?>';
            navigator.serviceWorker.register('<?= url('sw.js') ?>', { scope: guruScope })
                .then(function(reg) {
                    console.log('[PWA Portal Guru] SW registered OK on login, scope:', reg.scope);
                })
                .catch(function(err) {
                    console.error('[PWA Portal Guru] SW reg error:', err);
                });
        }
    </script>
</body>
</html>

