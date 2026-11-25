<?php
/**
 * Sidebar Khusus Modul Persuratan (Yayasan & Sekolah) - Portal BIP
 */
$currentTab = $activeTab ?? ($_GET['tab'] ?? 'dashboard');
$scope = $scope ?? 'sekolah';
$baseRoute = $baseRoute ?? ($scope === 'yayasan' ? 'persuratan-yayasan' : 'persuratan-sekolah');
$moduleTitle = $scope === 'yayasan' ? 'Persuratan Yayasan' : 'Persuratan Sekolah';
$moduleSub = $scope === 'yayasan' ? 'Arsip & Tata Naskah Yayasan' : 'Arsip & Tata Naskah Unit Sekolah';

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}

$pendingMasuk = (int)($stats['masuk_pending'] ?? 0);
?>
<aside id="sidebar"
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar <?= e($moduleTitle) ?>">

    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg text-white font-bold shrink-0">
                    ✉️
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate"><?= e($moduleTitle) ?></h1>
                <p class="text-primary-300 text-xs truncate"><?= e($moduleSub) ?></p>
            </div>
        </div>

        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Menu Persuratan -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-4 space-y-6">
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Menu <?= e($moduleTitle) ?>
            </h3>
            <ul class="space-y-1">
                <?php
                $menuItems = [
                    [
                        'key' => 'dashboard',
                        'label' => 'Dashboard',
                        'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>',
                        'badge' => null,
                    ],
                    [
                        'key' => 'masuk',
                        'label' => 'Surat Masuk',
                        'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859M12 3v8.25m0 0-3-3m3 3 3-3" /></svg>',
                        'badge' => $pendingMasuk > 0 ? $pendingMasuk : null,
                    ],
                    [
                        'key' => 'keluar',
                        'label' => 'Surat Keluar',
                        'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>',
                        'badge' => null,
                    ],
                    [
                        'key' => 'data',
                        'label' => 'Data Surat',
                        'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>',
                        'badge' => null,
                    ],
                    [
                        'key' => 'pengaturan',
                        'label' => 'Pengaturan Surat',
                        'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>',
                        'badge' => null,
                    ],
                ];
                foreach ($menuItems as $m):
                    $isActive = ($currentTab === $m['key']);
                    $activeClass = $isActive
                        ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40'
                        : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent';
                    $iconClass = $isActive ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                ?>
                    <li>
                        <a href="<?= url($baseRoute . '?tab=' . $m['key']) ?>"
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group <?= $activeClass ?>">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-5 h-5 [&>svg]:w-5 [&>svg]:h-5 <?= $iconClass ?> transition-colors shrink-0"><?= $m['icon'] ?></div>
                                <span class="text-sm truncate"><?= e($m['label']) ?></span>
                            </div>
                            <?php if (!empty($m['badge'])): ?>
                                <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-amber-500 text-white shrink-0"><?= (int)$m['badge'] ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20 shrink-0">
        <a href="<?= url('dashboard') ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-primary-200 hover:text-white hover:bg-primary-800/60 transition-all duration-200 group">
            <svg class="w-5 h-5 text-primary-400 group-hover:text-primary-300 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span class="truncate">Kembali ke Portal Utama</span>
        </a>
    </div>
</aside>
