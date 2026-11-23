<?php
/**
 * Sidebar Khusus Modul Kelola Jurnal Staff (Support System) - Portal BIP
 * Menampilkan 4 Menu Utama:
 * 1. Dashboard
 * 2. Data Jurnal
 * 3. Pengaturan Jam (07.00 - 08.00 Tilawah, 08.00 - 09.00 Aktifitas, dll)
 * 4. Pengaturan Tugas (Job Desk per Jabatan referensi)
 */

$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$activeTab = $activeTab ?? ($_GET['tab'] ?? 'dashboard');

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}

$dashStats = $dashboardStats ?? [];
$cntToday = (int)($dashStats['jurnal_hari_ini'] ?? 0);
$staffToday = (int)($dashStats['staff_isi_hari_ini'] ?? 0);
$staffTotal = (int)($dashStats['total_support_staff'] ?? 0);
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Kelola Jurnal Staff">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30 shrink-0">
        <div class="flex items-center gap-3 min-w-0">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo Sekolah" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-9 h-9 rounded-2xl bg-primary-600 flex items-center justify-center shadow-lg shadow-primary-600/20 text-white font-bold shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Jurnal Staff</h1>
                <p class="text-primary-300 text-xs truncate">Support System & Divisi</p>
            </div>
        </div>
        
        <!-- Close Mobile Menu -->
        <button onclick="toggleSidebar()" class="lg:hidden ml-auto p-1.5 rounded-full text-primary-400 hover:text-white hover:bg-primary-800 transition-colors" aria-label="Tutup menu">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Info Ringkasan Hari Ini Widget -->
    <div class="px-4 pt-4 pb-2 shrink-0">
        <div class="bg-primary-950/40 rounded-xl p-3 border border-primary-800/60">
            <div class="flex items-center justify-between text-xs mb-1">
                <span class="text-primary-300 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-primary-400 animate-pulse"></span>
                    Aktivitas Hari Ini
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-primary-500/20 text-primary-200 border border-primary-500/30">
                    <?= $cntToday ?> Catatan
                </span>
            </div>
            <div class="text-white font-bold text-xs tracking-wide mt-1">
                👥 Staff Mengisi: <?= $staffToday ?> / <?= $staffTotal ?> Orang
            </div>
            <div class="w-full bg-primary-800/80 rounded-full h-1.5 mt-2 overflow-hidden">
                <?php 
                    $pct = $staffTotal > 0 ? min(100, round(($staffToday / $staffTotal) * 100)) : 0;
                ?>
                <div class="bg-primary-400 h-1.5 rounded-full transition-all duration-500" style="width: <?= $pct ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Sidebar Menu List -->
    <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar py-4 px-4 space-y-6">
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Fitur Modul Jurnal Staff
            </h3>
            <ul class="space-y-1">
                <?php
                $menuItems = [
                    [
                        'tab' => 'dashboard',
                        'label' => '1. Dashboard',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />',
                        'badge' => null
                    ],
                    [
                        'tab' => 'data',
                        'label' => '2. Data Jurnal',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />',
                        'badge' => $dashStats['total_jurnal_periode'] ?? null,
                        'badgeClass' => 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30'
                    ],
                    [
                        'tab' => 'jam',
                        'label' => '3. Pengaturan Jam',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />',
                        'badge' => count($jamList ?? []),
                        'badgeClass' => 'bg-primary-500/20 text-primary-200 border border-primary-500/30'
                    ],
                    [
                        'tab' => 'tugas',
                        'label' => '4. Pengaturan Tugas',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />',
                        'badge' => count($masterJabatanList ?? []),
                        'badgeClass' => 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
                    ],
                    [
                        'tab' => 'pengingat',
                        'label' => '5. Pengingat WA',
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />',
                        'badge' => (int)($reminderEval['summary']['needs_reminder'] ?? 0),
                        'badgeClass' => 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                    ]
                ];

                foreach ($menuItems as $m):
                    $isCur = ($activeTab === $m['tab']);
                    $cls = $isCur
                        ? 'bg-primary-600/30 text-white font-semibold shadow-sm border border-primary-500/40'
                        : 'text-primary-200 font-medium hover:bg-primary-800/60 hover:text-white border border-transparent';
                    $iconCls = $isCur ? 'text-white' : 'text-primary-400 group-hover:text-primary-300';
                    $href = url('kelola-jurnal-staff?tab=' . $m['tab']);
                ?>
                    <li>
                        <a href="<?= $href ?>" 
                           onclick="if(typeof switchStaffTab === 'function'){ switchStaffTab('<?= $m['tab'] ?>'); return false; }"
                           data-staff-sidebar-tab="<?= $m['tab'] ?>"
                           class="staff-sidebar-link flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group <?= $cls ?>">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <svg class="w-5 h-5 shrink-0 transition-colors <?= $iconCls ?>" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <?= $m['icon'] ?>
                                </svg>
                                <span class="truncate text-xs font-medium"><?= $m['label'] ?></span>
                            </div>
                            <?php if ($m['badge'] !== null): ?>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 <?= $m['badgeClass'] ?? 'bg-primary-700 text-white' ?>">
                                    <?= $m['badge'] ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Aksi Cepat & Navigasi Luar -->
        <div>
            <h3 class="px-3 mb-2 text-[11px] font-bold text-primary-400 uppercase tracking-wider">
                Aksi Cepat
            </h3>
            <ul class="space-y-1">
                <li>
                    <a href="<?= url('kelola-jurnal-staff/cetak?start_date=' . urlencode($filterStartDate ?? date('Y-m-01')) . '&end_date=' . urlencode($filterEndDate ?? date('Y-m-t'))) ?>" 
                       target="_blank"
                       class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-primary-200 hover:bg-primary-800/60 hover:text-white transition-all text-xs font-medium">
                        <svg class="w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.144-1.28-3.093l-.408-.637c-.368-.574-.532-.83-.532-1.127 0-.482.352-.892.83-.974.776-.133 1.57-.213 2.378-.238m10.584 6.069c.24-1.076.672-2.144 1.28-3.093l.408-.637c.368-.574.532-.83.532-1.127 0-.482-.352-.892-.83-.974a24.28 24.28 0 0 0-2.378-.238M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v9.75a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25" />
                        </svg>
                        <span>Cetak Laporan Rekap</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('kelola-pegawai') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-primary-200 hover:bg-primary-800/60 hover:text-white transition-all text-xs font-medium">
                        <svg class="w-4 h-4 text-primary-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                        <span>Data Pegawai & Staff</span>
                    </a>
                </li>
                <li>
                    <a href="<?= url('kelola-jurnal-guru') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-primary-200 hover:bg-primary-800/60 hover:text-white transition-all text-xs font-medium">
                        <svg class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                        <span>Kelola Jurnal Guru</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/40 shrink-0">
        <a href="<?= url('dashboard') ?>" class="flex items-center gap-2 text-xs text-primary-300 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
    </div>
</aside>
