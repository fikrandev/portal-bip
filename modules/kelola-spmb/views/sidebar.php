<?php
/**
 * Sidebar Khusus Modul Kelola SPMB - Portal BIP
 * Navigasi resmi:
 * 1. Dashboard
 * 2. Siswa Baru
 * 3. Pindahan
 * 4. Pengaturan Tahun Group SPMB
 */

$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}

// Ambil info group aktif untuk display di sidebar
$activeGroupSidebar = null;
try {
    if (class_exists('SpmbModel')) {
        $activeGroupSidebar = SpmbModel::getActiveGroup();
    }
} catch (Throwable $e) {}
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-950 border-r border-primary-800/60 flex flex-col shadow-2xl"
       aria-label="Sidebar Modul Kelola SPMB">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/80">
        <div class="flex items-center gap-3">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo Sekolah" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-500/20 text-white font-bold shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333M4.5 21V10.333" />
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Kelola SPMB</h1>
                <p class="text-sky-300 text-xs truncate">Penerimaan Siswa Baru</p>
            </div>
        </div>
        
        <!-- Mobile Close Button -->
        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Active Year Badge Card -->
    <div class="p-4 border-b border-primary-850/40 bg-gradient-to-b from-primary-900/40 to-transparent">
        <div class="bg-primary-900/60 rounded-xl p-3 border border-primary-700/50 backdrop-blur-sm">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-primary-300 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Group Aktif
                </span>
                <a href="<?= url('kelola-spmb/pengaturan') ?>" class="text-[10px] text-sky-400 hover:text-sky-300 underline font-semibold">Ubah</a>
            </div>
            <div class="text-white font-bold text-sm tracking-wide flex items-center justify-between">
                <span><?= e($activeGroupSidebar['nama_group'] ?? 'SPMB 2026/2027') ?></span>
                <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">AKTIF</span>
            </div>
            <p class="text-primary-300/80 text-[11px] mt-1 truncate">
                <?= e($activeGroupSidebar['gelombang_default'] ?? 'Gelombang 1') ?> &bull; TA <?= e($activeGroupSidebar['tahun_ajaran'] ?? '2026/2027') ?>
            </p>
        </div>
    </div>

    <!-- Sidebar Menu Content -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-3 space-y-4">
        <div>
            <h3 class="px-3 mb-2 text-[10px] font-extrabold text-primary-400 uppercase tracking-wider">
                Navigasi SPMB
            </h3>
            <ul class="space-y-1.5">
                <?php
                // Check Active States
                $isDashboardActive = ($currentUri === '/kelola-spmb' || $currentUri === '/kelola-spmb/' || str_ends_with($currentUri, '/kelola-spmb') || strpos($currentUri, 'kelola-spmb/dashboard') !== false);
                $isSiswaBaruActive = (strpos($currentUri, 'kelola-spmb/siswa-baru') !== false);
                $isPindahanActive  = (strpos($currentUri, 'kelola-spmb/pindahan') !== false);
                $isDaftarTungguActive = (strpos($currentUri, 'kelola-spmb/daftar-tunggu') !== false);
                $isSuratPernyataanActive = (strpos($currentUri, 'kelola-spmb/surat-pernyataan') !== false);
                $isScreeningPsikologActive = (strpos($currentUri, 'kelola-spmb/screening-psikolog') !== false);
                $isObservasiAwalActive = (strpos($currentUri, 'kelola-spmb/observasi-awal') !== false);
                $isPengaturanActive = (strpos($currentUri, 'kelola-spmb/pengaturan') !== false);
                
                // Helper render menu item
                function renderSpmbNav($label, $url, $iconSvg, $isActive = false) {
                    $bgClass = $isActive 
                        ? 'bg-gradient-to-r from-sky-600 to-blue-600 text-white font-semibold shadow-md shadow-sky-600/20 border border-sky-400/30' 
                        : 'text-primary-200 hover:bg-primary-850/60 hover:text-white border border-transparent';
                    $iconClass = $isActive ? 'text-white' : 'text-sky-400 group-hover:text-sky-300';

                    echo "<li>
                        <a href=\"$url\" class=\"flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group $bgClass\">
                            <div class=\"$iconClass transition-colors flex-shrink-0\">$iconSvg</div>
                            <span class=\"text-xs tracking-tight truncate\">$label</span>
                        </a>
                    </li>";
                }

                // 1. DASHBOARD
                renderSpmbNav(
                    'Dashboard',
                    url('kelola-spmb'),
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>',
                    $isDashboardActive
                );

                // 2. SISWA BARU
                renderSpmbNav(
                    'Siswa Baru',
                    url('kelola-spmb/siswa-baru'),
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>',
                    $isSiswaBaruActive
                );

                // 3. PINDAHAN
                renderSpmbNav(
                    'Pindahan',
                    url('kelola-spmb/pindahan'),
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>',
                    $isPindahanActive
                );

                // 4. DAFTAR TUNGGU
                renderSpmbNav(
                    'Daftar Tunggu',
                    url('kelola-spmb/daftar-tunggu'),
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>',
                    $isDaftarTungguActive
                );

                // 5. SURAT PERNYATAAN KOMITMEN
                renderSpmbNav(
                    'Surat Pernyataan',
                    url('kelola-spmb/surat-pernyataan'),
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>',
                    $isSuratPernyataanActive
                );

                // 6. HASIL SCREENING PSIKOLOG (Singkat di Sidebar: Hasil Psikolog)
                renderSpmbNav(
                    'Hasil Psikolog',
                    url('kelola-spmb/screening-psikolog'),
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>',
                    $isScreeningPsikologActive
                );

                // 7. OBSERVASI AWAL
                renderSpmbNav(
                    'Observasi Awal',
                    url('kelola-spmb/observasi-awal'),
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>',
                    $isObservasiAwalActive
                );

                // 8. PENGATURAN
                renderSpmbNav(
                    'Pengaturan',
                    url('kelola-spmb/pengaturan'),
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 1 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>',
                    $isPengaturanActive
                );
                ?>
            </ul>
        </div>
    </div>

    <!-- Sidebar Footer: Kembali ke Portal Utama -->
    <div class="p-3 border-t border-primary-800/60 bg-primary-950/90">
        <a href="<?= url('') ?>" 
           class="flex items-center justify-center gap-2 w-full px-3 py-2.5 rounded-xl bg-primary-900/80 hover:bg-primary-800 text-primary-200 hover:text-white text-xs font-medium transition-colors border border-primary-800">
            <svg class="w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
            <span>Kembali ke Portal Utama</span>
        </a>
    </div>
</aside>
