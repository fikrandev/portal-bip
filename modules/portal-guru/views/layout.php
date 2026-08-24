<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, interactive-widget=resizes-content">
    <title><?= e($pageTitle ?? 'Portal Guru') ?> — Portal BIP</title>
    
    <!-- PWA Settings -->
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="theme-color" content="#2563eb">
    <meta name="application-name" content="Portal Guru">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Portal Guru">
    <link rel="apple-touch-icon" href="<?= asset('images/pwa/apple-touch-icon.png') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/pwa/apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= asset('images/pwa/icon-192.png') ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= asset('images/pwa/icon-512.png') ?>">
    
    <!-- Google Fonts: Inter & Amiri (Arabic Calligraphy) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
    
    <!-- Tailwind CSS 4 -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Smooth Animations CSS -->
    <link rel="stylesheet" href="<?= asset('css/mobile/app-animations.css') ?>">
    
    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        window.APP_BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';
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
    </style>
</head>
<body class="h-full bg-slate-100 flex justify-center text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- Mobile Shell Wrapper (Original Compact Size: max-w-md) -->
    <div class="w-full max-w-md min-h-screen bg-[#f6f8fb] shadow-2xl flex flex-col relative overflow-x-hidden border-x border-slate-200/60 sm:my-4 sm:rounded-3xl sm:min-h-[860px]">
        
        <!-- Main View Content -->
        <main class="flex-1 pb-24 overflow-y-auto no-scrollbar">
            <?= $content ?? '' ?>
        </main>

        <!-- Bottom Navigation Bar (5 Items with Center Elevated FAB) -->
        <nav class="fixed bottom-0 sm:bottom-4 left-0 right-0 max-w-md mx-auto z-40 bg-white/90 backdrop-blur-xl border-t border-slate-200/80 px-3 py-2 flex items-center justify-around sm:rounded-b-3xl safe-bottom shadow-[0_-4px_20px_rgba(0,0,0,0.04)]">
            
            <!-- Tab 1: Beranda -->
            <a href="<?= url('mobile') ?>" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-2xl press-bounce <?= ($activeTab ?? '') === 'beranda' ? 'text-blue-600 font-semibold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <i data-lucide="home" class="w-5 h-5 <?= ($activeTab ?? '') === 'beranda' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                <span class="text-[11px] leading-none">Beranda</span>
            </a>

            <!-- Tab 2: Kelas -->
            <a href="<?= url('mobile/kelas') ?>" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-2xl press-bounce <?= ($activeTab ?? '') === 'kelas' ? 'text-blue-600 font-semibold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <i data-lucide="users" class="w-5 h-5 <?= ($activeTab ?? '') === 'kelas' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                <span class="text-[11px] leading-none">Kelas</span>
            </a>

            <!-- Center Elevated FAB: Buat (+) -->
            <div class="-mt-6 flex flex-col items-center">
                <button onclick="openAndroidActionSheet()" class="w-13 h-13 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/40 hover:bg-blue-700 press-bounce border-4 border-[#f6f8fb]">
                    <i data-lucide="plus" class="w-6 h-6 stroke-[3]"></i>
                </button>
                <span class="text-[10px] font-semibold text-slate-500 mt-1">Buat</span>
            </div>

            <!-- Tab 3: Murid -->
            <a href="<?= url('mobile/murid') ?>" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-2xl press-bounce <?= ($activeTab ?? '') === 'murid' ? 'text-blue-600 font-semibold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <i data-lucide="graduation-cap" class="w-5 h-5 <?= ($activeTab ?? '') === 'murid' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                <span class="text-[11px] leading-none">Murid</span>
            </a>

            <!-- Tab 4: Profil -->
            <a href="<?= url('mobile/profil') ?>" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-2xl press-bounce <?= ($activeTab ?? '') === 'profil' ? 'text-blue-600 font-semibold' : 'text-slate-400 hover:text-slate-600 font-medium' ?>">
                <i data-lucide="user" class="w-5 h-5 <?= ($activeTab ?? '') === 'profil' ? 'stroke-[2.5]' : 'stroke-2' ?>"></i>
                <span class="text-[11px] leading-none">Profil</span>
            </a>
        </nav>

    </div>

    <!-- Scripts -->
    <script src="<?= asset('js/mobile/android-ui.js') ?>"></script>
    <script src="<?= asset('js/mobile/mobile-api.js') ?>"></script>
    <script src="<?= asset('js/mobile/lazy-load.js') ?>"></script>
    <script src="<?= asset('js/pwa.js') ?>"></script>
    <script src="<?= asset('js/mobile/mobile-app.js') ?>"></script>

    <script>
        // Open Android-style Bottom Sheet for '+' FAB
        function openAndroidActionSheet() {
            AndroidUI.bottomSheet({
                title: 'Aksi Cepat Guru',
                subtitle: 'Pilih aktivitas pembelajaran, presensi & perizinan',
                icon: '⚡',
                iconBg: 'bg-blue-100 text-blue-600',
                content: `
                    <div class="grid grid-cols-3 gap-2.5 pt-1 pb-1">
                        <a href="<?= url('mobile/absen') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-emerald-50 rounded-2xl border border-emerald-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 text-base font-bold">📍</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Absen GPS</span>
                        </a>
                        <a href="<?= url('mobile/jurnal') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-blue-50 rounded-2xl border border-blue-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 text-base font-bold">📝</div>
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
                        <a href="<?= url('mobile/cuti') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-teal-50 rounded-2xl border border-teal-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-500/20 text-base font-bold">🏖️</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Cuti Guru</span>
                        </a>

                        <a href="<?= url('mobile/buat-tugas') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-purple-50 rounded-2xl border border-purple-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-md shadow-purple-500/20 text-base font-bold">📋</div>
                            <span class="text-[11px] font-bold text-slate-700 text-center leading-tight">Buat Tugas</span>
                        </a>
                        <a href="<?= url('mobile/quran') ?>" class="flex flex-col items-center gap-1.5 p-2.5 bg-emerald-50 rounded-2xl border border-emerald-100 press-bounce">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 text-base font-bold">📖</div>
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
                            <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">1</span>
                            <p>Tekan tombol <strong>Share / Bagikan</strong> <span class="px-1 py-0.5 bg-white border border-slate-200 rounded font-mono">⎋ / ⤤</span> di bar bawah Safari.</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">2</span>
                            <p>Gulir ke bawah dan pilih <strong>"Tambahkan ke Layar Utama" (Add to Home Screen)</strong> ➕.</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">3</span>
                            <p>Tekan <strong>"Tambah" (Add)</strong> di pojok kanan atas. Selesai!</p>
                        </div>
                    </div>
                `,
                actions: [
                    {
                        text: 'Saya Mengerti',
                        className: 'w-full py-3 bg-blue-600 text-white font-bold text-xs rounded-2xl shadow-md'
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
                iconBg: 'bg-emerald-100 text-emerald-700',
                content: `
                    <div class="space-y-2.5 bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs text-slate-700 leading-relaxed">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">1</span>
                            <p>Tekan menu titik tiga <strong class="font-mono">⋮</strong> di pojok kanan atas browser Google Chrome.</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">2</span>
                            <p>Pilih menu <strong>"Tambahkan ke Layar Utama"</strong> atau <strong>"Install Aplikasi"</strong>.</p>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center shrink-0 text-xs">3</span>
                            <p>Tekan <strong>"Install"</strong> dan tunggu ikon muncul di layar HP Anda.</p>
                        </div>
                    </div>
                `,
                actions: [
                    {
                        text: 'Saya Mengerti',
                        className: 'w-full py-3 bg-emerald-600 text-white font-bold text-xs rounded-2xl shadow-md'
                    }
                ]
            });
        };

        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
</body>
</html>
