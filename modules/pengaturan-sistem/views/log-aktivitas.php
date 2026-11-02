<?php
/**
 * View Log Aktivitas Sistem
 * Portal BIP
 */

if (!function_exists('logTimeAgo')) {
    function logTimeAgo($datetime) {
        if (!$datetime) return '-';
        $time = strtotime($datetime);
        $diff = time() - $time;
        if ($diff < 5) return 'Baru saja';
        if ($diff < 60) return $diff . ' detik lalu';
        if ($diff < 3600) return floor($diff / 60) . ' mnt lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        return date('d/m/Y', $time);
    }
}

// Generate URL query parameters helper for preserving filters in pagination & links
$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$exportUrl = url('pengaturan-sistem/log-aktivitas/export') . ($baseQuery ? '?' . $baseQuery : '');
$clearFilterUrl = url('pengaturan-sistem/log-aktivitas');

// Calculate pagination items
$startRecord = $total > 0 ? $offset + 1 : 0;
$endRecord = min($offset + $perPage, $total);
?>

<div class="space-y-6 animate-slide-in">

    <!-- Flash Notifications -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span class="text-sm font-medium"><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span class="text-sm font-medium"><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Top Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Log Aktivitas Sistem</h2>
                    <p class="text-xs text-slate-500">Rekam jejak dan audit seluruh aktivitas pengguna di dalam aplikasi</p>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Tombol Ekspor CSV -->
            <a href="<?= $exportUrl ?>" 
               title="Unduh seluruh data log terfilter dalam format file Excel CSV"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm transition-all duration-150">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Ekspor CSV</span>
            </a>

            <!-- Tombol Bersihkan Log (Hanya admin/pemeliharaan) -->
            <button type="button" 
                    onclick="openClearModal()"
                    title="Pembersihan berkala riwayat aktivitas"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-semibold rounded-xl border border-slate-200 transition-all duration-150">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                <span>Bersihkan Log</span>
            </button>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Log -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Log Aplikasi</p>
                <h4 class="text-2xl font-extrabold text-slate-800 mt-1"><?= number_format($stats['total_all']) ?></h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Seluruh jejak aktivitas</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
                </svg>
            </div>
        </div>

        <!-- Log Hari Ini -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Aktivitas Hari Ini</p>
                <h4 class="text-2xl font-extrabold text-emerald-700 mt-1"><?= number_format($stats['today_all']) ?></h4>
                <p class="text-[11px] text-slate-400 mt-0.5"><?= date('d F Y') ?></p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </div>
        </div>

        <!-- User Teraktif -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">User Paling Aktif</p>
                <h4 class="text-base font-bold text-slate-800 mt-1 truncate" title="<?= htmlspecialchars($stats['top_user']['full_name'] ?? '-') ?>">
                    <?= htmlspecialchars($stats['top_user']['full_name'] ?? '-') ?>
                </h4>
                <p class="text-[11px] text-indigo-600 font-medium mt-0.5">
                    <?= number_format($stats['top_user']['cnt'] ?? 0) ?> tindakan tercatat
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
        </div>

        <!-- Login Terakhir -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Login Terakhir</p>
                <h4 class="text-base font-bold text-slate-800 mt-1 truncate" title="<?= htmlspecialchars($stats['last_login']['full_name'] ?? '-') ?>">
                    <?= htmlspecialchars($stats['last_login']['full_name'] ?? '-') ?>
                </h4>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    <?= !empty($stats['last_login']['created_at']) ? logTimeAgo($stats['last_login']['created_at']) . ' (' . date('H:i', strtotime($stats['last_login']['created_at'])) . ')' : '-' ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Filter Data Aktivitas</span>
            </div>
            
            <!-- Quick Date Buttons -->
            <div class="flex items-center gap-1.5 text-xs">
                <span class="text-slate-400 font-medium hidden sm:inline">Pintas Tanggal:</span>
                <button type="button" onclick="setFilterDates('today')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 font-medium transition-colors">Hari Ini</button>
                <button type="button" onclick="setFilterDates('7days')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 font-medium transition-colors">7 Hari</button>
                <button type="button" onclick="setFilterDates('30days')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 font-medium transition-colors">30 Hari</button>
                <button type="button" onclick="setFilterDates('all')" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 font-medium transition-colors">Semua</button>
            </div>
        </div>

        <form id="filterForm" action="<?= url('pengaturan-sistem/log-aktivitas') ?>" method="GET" class="p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                
                <!-- Tanggal Mulai -->
                <div>
                    <label for="tgl_mulai" class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Tanggal Mulai
                    </label>
                    <input type="date" 
                           id="tgl_mulai" 
                           name="tgl_mulai" 
                           value="<?= htmlspecialchars($tglMulai) ?>"
                           class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label for="tgl_selesai" class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Tanggal Selesai
                    </label>
                    <input type="date" 
                           id="tgl_selesai" 
                           name="tgl_selesai" 
                           value="<?= htmlspecialchars($tglSelesai) ?>"
                           class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                </div>

                <!-- Filter Pengguna -->
                <div>
                    <label for="user_id" class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Pengguna
                    </label>
                    <select id="user_id" 
                            name="user_id" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                        <option value="">Semua Pengguna</option>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($userId == $u['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['username']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Aksi -->
                <div>
                    <label for="action" class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Jenis Aksi
                    </label>
                    <select id="action" 
                            name="action" 
                            class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                        <option value="">Semua Aksi</option>
                        <?php foreach ($actionsList as $act): ?>
                            <option value="<?= htmlspecialchars($act['action']) ?>" <?= ($action === $act['action']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($act['action']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Kata Kunci Pencarian -->
                <div>
                    <label for="search" class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Kata Kunci
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="search" 
                               name="search" 
                               value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Cari IP, modul, dll..."
                               class="w-full pl-8 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                    </div>
                </div>

            </div>

            <!-- Action Buttons & Hidden per_page -->
            <input type="hidden" name="per_page" value="<?= (int)$perPage ?>">

            <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    Menampilkan total <strong class="text-slate-800"><?= number_format($total) ?></strong> aktivitas
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($tglMulai || $tglSelesai || $userId || $action || $search): ?>
                        <a href="<?= $clearFilterUrl ?>" 
                           class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition-colors">
                            Reset Filter
                        </a>
                    <?php endif; ?>

                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filter Chips / Badges (if any active) -->
    <?php if ($tglMulai || $tglSelesai || $userId || $action || $search): ?>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-slate-400 font-medium">Filter Aktif:</span>
            
            <?php if ($tglMulai || $tglSelesai): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 font-medium">
                    <svg class="w-3 h-3 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Periode: <?= $tglMulai ? date('d/m/Y', strtotime($tglMulai)) : 'Awal' ?> - <?= $tglSelesai ? date('d/m/Y', strtotime($tglSelesai)) : 'Sekarang' ?>
                </span>
            <?php endif; ?>

            <?php if ($userId): ?>
                <?php 
                $selectedUser = null;
                foreach ($usersList as $u) { if ($u['id'] == $userId) { $selectedUser = $u; break; } }
                ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-medium">
                    User: <?= htmlspecialchars($selectedUser['full_name'] ?? "ID #{$userId}") ?>
                </span>
            <?php endif; ?>

            <?php if ($action): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50 border border-purple-200 text-purple-700 font-medium">
                    Aksi: <?= htmlspecialchars($action) ?>
                </span>
            <?php endif; ?>

            <?php if ($search): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 font-medium">
                    Pencarian: "<?= htmlspecialchars($search) ?>"
                </span>
            <?php endif; ?>

            <a href="<?= $clearFilterUrl ?>" class="text-xs text-rose-600 hover:text-rose-700 font-semibold hover:underline ml-1">
                Hapus Semua
            </a>
        </div>
    <?php endif; ?>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        
        <!-- Table Top Controls -->
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 bg-slate-50/40">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Daftar Aktivitas</h3>
                <span class="text-xs font-semibold px-2.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-full">
                    <?= number_format($total) ?> catatan
                </span>
            </div>

            <!-- Per-page selector -->
            <div class="flex items-center gap-2 text-xs text-slate-600">
                <span>Baris per halaman:</span>
                <select onchange="changePerPage(this.value)" 
                        class="px-2 py-1 bg-white border border-slate-300 rounded-lg text-xs text-slate-800 focus:outline-none focus:border-indigo-500">
                    <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $perPage == 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
        </div>

        <!-- Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-4 py-3.5 text-center w-12">#</th>
                        <th scope="col" class="px-5 py-3.5 whitespace-nowrap">Waktu</th>
                        <th scope="col" class="px-5 py-3.5">Pengguna</th>
                        <th scope="col" class="px-4 py-3.5 text-center">Aksi</th>
                        <th scope="col" class="px-5 py-3.5">Modul / Entitas</th>
                        <th scope="col" class="px-5 py-3.5">IP & Perangkat</th>
                        <th scope="col" class="px-4 py-3.5 text-center">Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Tidak ada catatan log ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter pencarian atau tanggal di atas</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $index => $row): ?>
                            <?php 
                            $rowNum = $offset + $index + 1;
                            $uaInfo = AuditLog::parseUserAgent($row['user_agent'] ?? '');
                            $hasDetail = !empty($row['new_value']) || !empty($row['old_value']);

                            // Action badge color
                            $act = strtoupper($row['action'] ?? '');
                            $badgeStyle = 'bg-slate-100 text-slate-700 border-slate-200';
                            if ($act === 'LOGIN') {
                                $badgeStyle = 'bg-blue-50 text-blue-700 border-blue-200';
                            } elseif ($act === 'LOGOUT') {
                                $badgeStyle = 'bg-slate-100 text-slate-600 border-slate-200';
                            } elseif (in_array($act, ['CREATE', 'INSERT', 'STORE'])) {
                                $badgeStyle = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            } elseif (in_array($act, ['UPDATE', 'EDIT', 'SET_ACTIVE'])) {
                                $badgeStyle = 'bg-amber-50 text-amber-700 border-amber-200';
                            } elseif (in_array($act, ['DELETE', 'TRUNCATE', 'RESET', 'RESET_DATA'])) {
                                $badgeStyle = 'bg-rose-50 text-rose-700 border-rose-200';
                            }
                            ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- No -->
                                <td class="px-4 py-3 text-center text-slate-400 font-medium">
                                    <?= $rowNum ?>
                                </td>

                                <!-- Waktu -->
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800">
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                        <span class="font-mono"><?= date('H:i:s', strtotime($row['created_at'])) ?></span>
                                        <span>•</span>
                                        <span class="text-indigo-600 font-medium"><?= logTimeAgo($row['created_at']) ?></span>
                                    </div>
                                </td>

                                <!-- Pengguna -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <?php if (!empty($row['avatar'])): ?>
                                            <img src="<?= url(ltrim($row['avatar'], '/')) ?>" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-slate-200">
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                                                <?= strtoupper(substr($row['full_name'] ?? ($row['username'] ?? 'S'), 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-800 truncate max-w-[160px]" title="<?= htmlspecialchars($row['full_name'] ?? 'Sistem') ?>">
                                                <?= htmlspecialchars($row['full_name'] ?? 'Sistem / Anonim') ?>
                                            </div>
                                            <div class="text-[11px] text-slate-400 truncate max-w-[160px]">
                                                <?php if (!empty($row['username'])): ?>
                                                    <span class="font-mono text-slate-500">@<?= htmlspecialchars($row['username']) ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($row['role_names'])): ?>
                                                    <span class="inline-block px-1.5 py-0.2 rounded text-[10px] bg-slate-100 text-slate-600 font-medium">
                                                        <?= htmlspecialchars($row['role_names']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border shadow-2xs <?= $badgeStyle ?>">
                                        <?= htmlspecialchars($row['action']) ?>
                                    </span>
                                </td>

                                <!-- Modul / Entitas -->
                                <td class="px-5 py-3">
                                    <div class="font-medium text-slate-800">
                                        <?= htmlspecialchars($row['entity_type'] ?: 'system') ?>
                                    </div>
                                    <?php if (!empty($row['entity_id'])): ?>
                                        <div class="text-[11px] text-slate-400 font-mono">
                                            ID: #<?= (int)$row['entity_id'] ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- IP & Perangkat -->
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-slate-700 font-medium">
                                            <?= htmlspecialchars($row['ip_address'] === '::1' ? '127.0.0.1 (Local)' : ($row['ip_address'] ?: '-')) ?>
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[200px] mt-0.5" 
                                         title="<?= htmlspecialchars($row['user_agent'] ?? '') ?>">
                                        <?= htmlspecialchars($uaInfo['label']) ?>
                                    </div>
                                </td>

                                <!-- Detail Payload -->
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <?php if ($hasDetail): ?>
                                        <button type="button" 
                                                onclick='openDetailModal(<?= json_encode([
                                                    'id' => $row['id'],
                                                    'user' => $row['full_name'] ?: 'Sistem',
                                                    'action' => $row['action'],
                                                    'entity' => $row['entity_type'],
                                                    'entity_id' => $row['entity_id'],
                                                    'time' => date('d M Y H:i:s', strtotime($row['created_at'])),
                                                    'ip' => $row['ip_address'],
                                                    'ua' => $uaInfo['label'],
                                                    'new_value' => $row['new_value'],
                                                    'old_value' => $row['old_value'],
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Detail</span>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-slate-300">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
                <div class="text-xs text-slate-500">
                    Menampilkan <strong class="text-slate-800"><?= number_format($startRecord) ?></strong> - <strong class="text-slate-800"><?= number_format($endRecord) ?></strong> dari <strong class="text-slate-800"><?= number_format($total) ?></strong> data
                </div>

                <div class="flex items-center gap-1">
                    <?php
                    // Helper to build page URL
                    $buildPageUrl = function($p) use ($queryParams) {
                        $params = array_merge($queryParams, ['page' => $p]);
                        return url('pengaturan-sistem/log-aktivitas') . '?' . http_build_query($params);
                    };

                    // Previous Button
                    if ($page > 1): ?>
                        <a href="<?= $buildPageUrl($page - 1) ?>" 
                           class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-medium transition-colors">
                            &laquo; Prev
                        </a>
                    <?php endif; ?>

                    <?php
                    // Page numbers with smart window
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    if ($startPage > 1): ?>
                        <a href="<?= $buildPageUrl(1) ?>" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-medium">1</a>
                        <?php if ($startPage > 2): ?>
                            <span class="px-2 text-slate-400">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                        <?php if ($p == $page): ?>
                            <span class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold shadow-xs">
                                <?= $p ?>
                            </span>
                        <?php else: ?>
                            <a href="<?= $buildPageUrl($p) ?>" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-medium transition-colors">
                                <?= $p ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="px-2 text-slate-400">...</span>
                        <?php endif; ?>
                        <a href="<?= $buildPageUrl($totalPages) ?>" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-medium"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <!-- Next Button -->
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $buildPageUrl($page + 1) ?>" 
                           class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs font-medium transition-colors">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- MODAL DETAIL PAYLOAD / PERUBAHAN -->
<div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-scale-up">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-indigo-100 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Detail Perubahan Log</h3>
                    <p id="modalSub" class="text-xs text-slate-500"></p>
                </div>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/60 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            
            <!-- Metadata Summary -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Pengguna</span>
                    <strong id="modalUser" class="text-slate-700"></strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Aksi</span>
                    <strong id="modalAction" class="text-slate-700"></strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Entitas</span>
                    <strong id="modalEntity" class="text-slate-700"></strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Waktu</span>
                    <strong id="modalTime" class="text-slate-700"></strong>
                </div>
            </div>

            <!-- New Value Box -->
            <div id="boxNewValue">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Data Baru (New Value)
                    </span>
                    <button type="button" onclick="copyModalCode('codeNewValue')" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold">Salin JSON</button>
                </div>
                <pre id="codeNewValue" class="p-3 bg-slate-900 text-emerald-400 text-xs font-mono rounded-xl overflow-x-auto max-h-56 leading-relaxed select-all"></pre>
            </div>

            <!-- Old Value Box -->
            <div id="boxOldValue" class="hidden">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        Data Lama (Old Value)
                    </span>
                    <button type="button" onclick="copyModalCode('codeOldValue')" class="text-[11px] text-indigo-600 hover:text-indigo-800 font-semibold">Salin JSON</button>
                </div>
                <pre id="codeOldValue" class="p-3 bg-slate-900 text-rose-300 text-xs font-mono rounded-xl overflow-x-auto max-h-56 leading-relaxed select-all"></pre>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex justify-end">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition-colors">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- MODAL BERSIHKAN LOG -->
<div id="clearModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="relative bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-scale-up">
        
        <form action="<?= url('pengaturan-sistem/log-aktivitas/clear') ?>" method="POST">
            <?= CSRF::field() ?>

            <!-- Modal Header -->
            <div class="p-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Bersihkan Riwayat Log</h3>
                <p class="text-xs text-slate-500 mt-1">Pilih rentang usia log aktivitas yang ingin dihapus untuk menjaga performa dan efisiensi penyimpanan database.</p>
            </div>

            <!-- Modal Options -->
            <div class="px-6 pb-6 space-y-3">
                <label class="block text-xs font-semibold text-slate-700">Pilih Opsi Pembersihan:</label>
                
                <div class="space-y-2 text-xs">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                        <input type="radio" name="range" value="30" checked class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="font-bold text-slate-700">Hapus yang lebih lama dari 30 hari</span>
                            <p class="text-slate-400 text-[11px]">Mempertahankan aktivitas 1 bulan terakhir</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                        <input type="radio" name="range" value="60" class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="font-bold text-slate-700">Hapus yang lebih lama dari 60 hari</span>
                            <p class="text-slate-400 text-[11px]">Mempertahankan aktivitas 2 bulan terakhir</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition-colors">
                        <input type="radio" name="range" value="90" class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="font-bold text-slate-700">Hapus yang lebih lama dari 90 hari</span>
                            <p class="text-slate-400 text-[11px]">Mempertahankan aktivitas 3 bulan terakhir</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 cursor-pointer transition-colors">
                        <input type="radio" name="range" value="all" class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="font-bold text-rose-700">Hapus SEMUA catatan log</span>
                            <p class="text-rose-500 text-[11px]">Mengosongkan seluruh tabel audit log</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2.5">
                <button type="button" onclick="closeClearModal()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                    Ya, Bersihkan Sekarang
                </button>
            </div>

        </form>

    </div>
</div>

<script>
// Fungsi Pintas Tanggal
function setFilterDates(type) {
    const startInput = document.getElementById('tgl_mulai');
    const endInput = document.getElementById('tgl_selesai');
    const now = new Date();

    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    if (type === 'today') {
        const todayStr = formatDate(now);
        startInput.value = todayStr;
        endInput.value = todayStr;
    } else if (type === '7days') {
        endInput.value = formatDate(now);
        const past = new Date();
        past.setDate(now.getDate() - 7);
        startInput.value = formatDate(past);
    } else if (type === '30days') {
        endInput.value = formatDate(now);
        const past = new Date();
        past.setDate(now.getDate() - 30);
        startInput.value = formatDate(past);
    } else if (type === 'all') {
        startInput.value = '';
        endInput.value = '';
    }

    // Auto submit form
    document.getElementById('filterForm').submit();
}

// Ubah per_page
function changePerPage(val) {
    const form = document.getElementById('filterForm');
    const hiddenPer = form.querySelector('input[name="per_page"]');
    if (hiddenPer) hiddenPer.value = val;
    form.submit();
}

// Modal Detail
function openDetailModal(data) {
    document.getElementById('modalSub').textContent = `Log ID #${data.id} - ${data.time}`;
    document.getElementById('modalUser').textContent = data.user;
    document.getElementById('modalAction').textContent = data.action;
    document.getElementById('modalEntity').textContent = data.entity + (data.entity_id ? ` (#${data.entity_id})` : '');
    document.getElementById('modalTime').textContent = data.time;

    const boxNew = document.getElementById('boxNewValue');
    const codeNew = document.getElementById('codeNewValue');
    const boxOld = document.getElementById('boxOldValue');
    const codeOld = document.getElementById('codeOldValue');

    const formatJson = (str) => {
        if (!str) return '';
        try {
            const parsed = typeof str === 'string' ? JSON.parse(str) : str;
            return JSON.stringify(parsed, null, 2);
        } catch (e) {
            return String(str);
        }
    };

    if (data.new_value) {
        boxNew.classList.remove('hidden');
        codeNew.textContent = formatJson(data.new_value);
    } else {
        boxNew.classList.add('hidden');
    }

    if (data.old_value) {
        boxOld.classList.remove('hidden');
        codeOld.textContent = formatJson(data.old_value);
    } else {
        boxOld.classList.add('hidden');
    }

    document.getElementById('detailModal').classList.remove('hidden');
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

function copyModalCode(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    navigator.clipboard.writeText(el.textContent).then(() => {
        alert('Data JSON berhasil disalin ke clipboard!');
    });
}

// Modal Clear
function openClearModal() {
    document.getElementById('clearModal').classList.remove('hidden');
}

function closeClearModal() {
    document.getElementById('clearModal').classList.add('hidden');
}

// Close modals on Esc key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDetailModal();
        closeClearModal();
    }
});
</script>
