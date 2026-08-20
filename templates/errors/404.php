<?php $pageTitle = '404 - Halaman Tidak Ditemukan'; ?>
<?php include TEMPLATES_PATH . '/partials/header.php'; ?>
<body class="font-sans bg-primary-50 antialiased min-h-screen flex items-center justify-center">
    <div class="text-center px-6">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-primary-100 mb-6">
            <svg class="w-10 h-10 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
        </div>
        <h1 class="text-6xl font-extrabold text-primary-900 mb-2">404</h1>
        <p class="text-lg text-slate-600 mb-6">Halaman yang Anda cari tidak ditemukan.</p>
        <a href="<?= url('dashboard') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-full transition-colors shadow-lg shadow-primary-500/25">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
</body></html>
