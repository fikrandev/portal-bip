<?php
/**
 * Sidebar Modul Qur'an PAUD / TK
 * Referensi Desain: Modul Kelola Nilai (Clean, Elegant, Professional, Tanpa Ikon Berlebihan)
 * TEPAT 4 Fitur Utama:
 * 1. Dashboard
 * 2. Pengaturan Target (a. Tahsin, b. Tahfidz)
 * 3. Input Penilaian
 * 4. Rekap Nilai
 */
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$curKategori = $_GET['kategori'] ?? '';

$sbLogo = '';
if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $sbLogo = url(ltrim(SYS_APP_FAVICON, '/'));
} elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}
?>
<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-[280px] h-screen transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 bg-primary-900 border-r border-primary-800 flex flex-col"
       aria-label="Sidebar Modul Qur'an PAUD">
    
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-primary-800/50 bg-primary-950/30">
        <div class="flex items-center gap-3">
            <?php if ($sbLogo): ?>
                <div class="w-9 h-9 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-black/20 shrink-0">
                    <img src="<?= $sbLogo ?>" alt="Logo Sekolah" class="max-w-full max-h-full object-contain">
                </div>
            <?php else: ?>
                <div class="w-8 h-8 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center shadow-lg shadow-teal-500/20 text-white font-bold shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="text-white font-bold text-base tracking-tight leading-tight truncate">Qur'an PAUD</h1>
                <p class="text-teal-300 text-xs truncate">Pendidikan Anak Usia Dini</p>
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
                Navigasi Modul (4 Fitur)
            </h3>
            <ul class="space-y-1">
                <?php
                // 1. Dashboard
                $isDashboard = ($currentUri === '/kelola-quran-siswa-paud' || $currentUri === '/portal-bip/kelola-quran-siswa-paud' || $currentUri === '/kelola-quran-siswa/paud' || $currentUri === '/portal-bip/kelola-quran-siswa/paud') && !str_contains($currentUri, 'target') && !str_contains($currentUri, 'penilaian') && !str_contains($currentUri, 'rekap');
                $dashClass = $isDashboard 
                    ? 'bg-teal-600/30 text-white font-semibold shadow-sm border border-teal-500/40' 
                    : 'text-primary-200 hover:bg-primary-800/60 hover:text-white border border-transparent';
                $dashIcon = $isDashboard ? 'text-teal-400' : 'text-primary-400 group-hover:text-teal-300';
                ?>
                <li>
                    <a href="<?= url('kelola-quran-siswa-paud') ?>" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition-all duration-200 group <?= $dashClass ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="<?= $dashIcon ?> transition-colors flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                </svg>
                            </div>
                            <span class="text-sm truncate">Dashboard</span>
                        </div>
                    </a>
                </li>

                <!-- 2. Pengaturan Target -->
                <?php
                $isTarget = str_contains($currentUri, 'kelola-quran-siswa-paud/target');
                $targetClass = $isTarget 
                    ? 'bg-teal-600/30 text-white font-semibold shadow-sm border border-teal-500/40' 
                    : 'text-primary-200 hover:bg-primary-800/60 hover:text-white border border-transparent';
                $targetIcon = $isTarget ? 'text-teal-400' : 'text-primary-400 group-hover:text-teal-300';
                ?>
                <li>
                    <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition-all duration-200 group <?= $targetClass ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="<?= $targetIcon ?> transition-colors flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                </svg>
                            </div>
                            <span class="text-sm truncate">Pengaturan Target</span>
                        </div>
                    </a>
                </li>

                <!-- 3. Input Penilaian -->
                <?php
                $isPenilaian = str_contains($currentUri, 'kelola-quran-siswa-paud/penilaian');
                $penilaianClass = $isPenilaian 
                    ? 'bg-teal-600/30 text-white font-semibold shadow-sm border border-teal-500/40' 
                    : 'text-primary-200 hover:bg-primary-800/60 hover:text-white border border-transparent';
                $penilaianIcon = $isPenilaian ? 'text-teal-400' : 'text-primary-400 group-hover:text-teal-300';
                ?>
                <li>
                    <a href="<?= url('kelola-quran-siswa-paud/penilaian') ?>" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition-all duration-200 group <?= $penilaianClass ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="<?= $penilaianIcon ?> transition-colors flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </div>
                            <span class="text-sm truncate">Input Penilaian</span>
                        </div>
                    </a>
                </li>

                <!-- 4. Rekap Nilai -->
                <?php
                $isRekap = str_contains($currentUri, 'kelola-quran-siswa-paud/rekap');
                $rekapClass = $isRekap 
                    ? 'bg-teal-600/30 text-white font-semibold shadow-sm border border-teal-500/40' 
                    : 'text-primary-200 hover:bg-primary-800/60 hover:text-white border border-transparent';
                $rekapIcon = $isRekap ? 'text-teal-400' : 'text-primary-400 group-hover:text-teal-300';
                ?>
                <li>
                    <a href="<?= url('kelola-quran-siswa-paud/rekap') ?>" class="flex items-center justify-between px-3 py-2.5 rounded-2xl transition-all duration-200 group <?= $rekapClass ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="<?= $rekapIcon ?> transition-colors flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                            </div>
                            <span class="text-sm truncate">Rekap Nilai</span>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-primary-800/50 bg-primary-950/20 space-y-2">
        <a href="<?= url('mobile') ?>" 
           target="_blank"
           class="flex items-center gap-2.5 px-3 py-2 rounded-2xl text-xs font-semibold text-emerald-300 hover:text-white bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/20 transition-all">
            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
            </svg>
            <span>📱 Portal Guru (Mobile)</span>
        </a>

        <a href="<?= url('dashboard') ?>" 
           class="flex items-center gap-2.5 px-3 py-2 rounded-2xl text-xs font-semibold text-primary-300 hover:text-white hover:bg-white/10 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            <span>Kembali ke Portal Utama</span>
        </a>
    </div>
</aside>
