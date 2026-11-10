<?php
/**
 * Sidebar Khusus Modul Kelola Sarpras (Sarana & Prasarana)
 * Portal BIP
 */
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Sarpras">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo Sekolah" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20 text-white font-bold shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Kelola Sarpras</h1>
                <p class="text-primary-300 text-xs truncate">Sarana & Prasarana</p>
            </div>
        </div>
        
        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Sidebar Content -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-5 px-4 space-y-6">
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Navigasi Modul
            </h3>
            <ul class="space-y-1">
                <?php
                $isDashboardActive = ($currentUri === '/kelola-sarpras' || $currentUri === '/portal-bip/kelola-sarpras' || $currentUri === 'kelola-sarpras');
                $isDataAsetTreeActive = (strpos($currentUri, 'kelola-sarpras/data-aset') !== false || strpos($currentUri, 'kelola-sarpras/aset') !== false);
                $isTanahActive = (strpos($currentUri, 'kelola-sarpras/tanah') !== false);
                $isBangunanActive = (strpos($currentUri, 'kelola-sarpras/bangunan') !== false);
                $isRuangActive = (strpos($currentUri, 'kelola-sarpras/ruangan') !== false);
                $isDataAsetActive = $isDataAsetTreeActive || $isTanahActive || $isBangunanActive || $isRuangActive;

                $isBarangActive = (strpos($currentUri, 'kelola-sarpras/barang') !== false);
                $isPinjamActive = (strpos($currentUri, 'kelola-sarpras/peminjaman') !== false);
                $isMntActive = (strpos($currentUri, 'kelola-sarpras/pemeliharaan') !== false);
                $isKategoriActive = (strpos($currentUri, 'kelola-sarpras/kategori') !== false);
                $isReferensiActive = (strpos($currentUri, 'kelola-sarpras/referensi') !== false);

                if (!function_exists('renderSarprasMenuItem')) {
                    function renderSarprasMenuItem($label, $url, $icon, $isActive = false) {
                        $activeClass = $isActive 
                            ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' 
                            : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent';
                        
                        $iconColor = $isActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                        
                        echo "<li>
                            <a href=\"$url\" class=\"flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group $activeClass\">
                                <div class=\"flex items-center gap-3 min-w-0\">
                                    <div class=\"w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 $iconColor transition-colors shrink-0\">$icon</div>
                                    <span class=\"text-sm truncate\">$label</span>
                                </div>
                            </a>
                        </li>";
                    }
                }

                // 1. DASHBOARD
                renderSarprasMenuItem(
                    'Dashboard',
                    url('kelola-sarpras'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>',
                    $isDashboardActive
                );
                ?>

                <!-- 2. DROPDOWN: DATA ASET (Hierarki Accordion, Tanah, Bangunan, Ruang) -->
                <li class="relative">
                    <button type="button" 
                            onclick="toggleDataAsetDropdown()" 
                            id="btn-dropdown-data-aset"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group <?= $isDataAsetActive 
                                ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' 
                                : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent' ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 <?= $isDataAsetActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300' ?> transition-colors shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                </svg>
                            </div>
                            <span class="text-sm truncate">Data Aset</span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-500/20 text-primary-300 border border-primary-500/30">Hierarki</span>
                            <svg id="chevron-data-aset" 
                                 class="w-4 h-4 text-primary-400 transition-transform duration-200 <?= $isDataAsetActive ? 'rotate-180' : '' ?>" 
                                 fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Sub-menu: Tanah, Bangunan, Ruang -->
                    <div id="submenu-data-aset" class="<?= $isDataAsetActive ? 'block' : 'hidden' ?> pl-4 pr-1 pt-1.5 pb-1 space-y-1">
                        <!-- Submenu 1: Tanah -->
                        <a href="<?= url('kelola-sarpras/tanah') ?>" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 group <?= $isTanahActive 
                               ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' 
                               : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent' ?>">
                            <svg class="w-5 h-5 <?= $isTanahActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300' ?> shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.53 3.22 1.5-1.5a.75.75 0 0 0 0-1.06l-4.5-4.5a.75.75 0 0 0-1.06 0l-2.47 2.47a.75.75 0 0 1-1.06 0L4.22 8.78a.75.75 0 0 0-1.06 0l-1.5 1.5a.75.75 0 0 0 0 1.06l7.5 7.5c.293.293.768.293 1.06 0l2.47-2.47a.75.75 0 0 1 1.06 0l1.97 1.97a.75.75 0 0 0 1.06 0l1.5-1.5a.75.75 0 0 0 0-1.06l-4.5-4.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5" />
                            </svg>
                            <span class="text-sm truncate">Tanah</span>
                        </a>

                        <!-- Submenu 2: Bangunan -->
                        <a href="<?= url('kelola-sarpras/bangunan') ?>" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 group <?= $isBangunanActive 
                               ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' 
                               : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent' ?>">
                            <svg class="w-5 h-5 <?= $isBangunanActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300' ?> shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.333A2.25 2.25 0 0 0 17.25 8.1L12 4.6 6.75 8.1A2.25 2.25 0 0 0 4.5 10.333V21h15Z" />
                            </svg>
                            <span class="text-sm truncate">Bangunan</span>
                        </a>

                        <!-- Submenu 3: Ruang -->
                        <a href="<?= url('kelola-sarpras/ruangan') ?>" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 group <?= $isRuangActive 
                               ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40' 
                               : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent' ?>">
                            <svg class="w-5 h-5 <?= $isRuangActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300' ?> shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6h1.5m-1.5 3h1.5m-1.5 3h1.5M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <span class="text-sm truncate">Ruang</span>
                        </a>
                    </div>
                </li>

                <?php
                // 3. INVENTARIS BARANG
                renderSarprasMenuItem(
                    'Inventaris Barang',
                    url('kelola-sarpras/barang'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>',
                    $isBarangActive
                );

                // 3.1 CETAK LABEL QR
                renderSarprasMenuItem(
                    'Cetak Label Barcode & QR',
                    url('kelola-sarpras/cetak-label'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" /></svg>',
                    strpos($_SERVER['REQUEST_URI'] ?? '', 'kelola-sarpras/cetak-label') !== false
                );

                // 4. PENYUSUTAN ASET
                renderSarprasMenuItem(
                    'Penyusutan Aset',
                    url('kelola-sarpras/penyusutan'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>',
                    strpos($_SERVER['REQUEST_URI'] ?? '', 'kelola-sarpras/penyusutan') !== false
                );
                
                // 5. LAPORAN ASET
                renderSarprasMenuItem(
                    'Laporan Aset',
                    url('kelola-sarpras/laporan'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
                    strpos($_SERVER['REQUEST_URI'] ?? '', 'kelola-sarpras/laporan') !== false
                );

                // 6. SIRKULASI PEMINJAMAN
                renderSarprasMenuItem(
                    'Sirkulasi Peminjaman',
                    url('kelola-sarpras/peminjaman'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-.75m0-3.75V9.75a2.25 2.25 0 0 0-2.25-2.25h-7.5A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-.75m3.75-9.75H19.5a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-1.5" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12h5.25m0 0l-2.25-2.25m2.25 2.25l-2.25 2.25" /></svg>',
                    $isPinjamActive
                );

                // 7. PEMELIHARAAN
                renderSarprasMenuItem(
                    'Pemeliharaan & Kerusakan',
                    url('kelola-sarpras/pemeliharaan'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.017l-3.35 3.35m0 0l3.03 2.496c.384.317.626.74.766 1.208M14.25 3.75a3.75 3.75 0 013.75 3.75c0 .762-.228 1.47-.62 2.064l-2.03 2.463-3.664-3.664 2.464-2.03c.594-.392 1.302-.62 2.064-.62z" /></svg>',
                    $isMntActive
                );

                // 8. KATEGORI ASET
                renderSarprasMenuItem(
                    'Kategori Aset',
                    url('kelola-sarpras/kategori'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386l5.411-3.177c.827-.486 1.126-1.517.683-2.289l-5.61-9.782A2.25 2.25 0 0 0 14.382 3H9.568Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>',
                    $isKategoriActive
                );

                // 9. REFERENSI
                renderSarprasMenuItem(
                    'Referensi',
                    url('kelola-sarpras/referensi'),
                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>',
                    $isReferensiActive
                );
                ?>
            </ul>
        </div>
    </div>

    <!-- Script toggle dropdown Data Aset -->
    <script>
    function toggleDataAsetDropdown() {
        const submenu = document.getElementById('submenu-data-aset');
        const chevron = document.getElementById('chevron-data-aset');
        if (submenu) {
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                submenu.classList.add('block');
                if (chevron) chevron.classList.add('rotate-180');
            } else {
                submenu.classList.add('hidden');
                submenu.classList.remove('block');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }
    }
    </script>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20 shrink-0">
        <a href="<?= url('dashboard') ?>" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            <span class="truncate">Kembali ke Portal Utama</span>
        </a>
    </div>
</aside>
