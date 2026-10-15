<?php
/**
 * View Utama Hasil Observasi Awal Calon Siswa - Modul Kelola SPMB
 * Portal BIP
 *
 * @var array  $activeGroup
 * @var array  $items
 * @var array  $stats
 * @var array  $filters
 * @var int    $page
 * @var int    $totalPages
 * @var int    $totalItems
 * @var int    $limit
 */

$csrfToken = CSRF::token();
$selectedJenjang = $filters['jenjang'] ?? '';
$selectedStatus  = $filters['status_observasi'] ?? '';
$searchKeyword   = $filters['search'] ?? '';
?>

<div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Modul SPMB
                </span>
                <span class="text-xs text-slate-400">&bull;</span>
                <span class="text-xs font-medium text-slate-500">Tahun: <?= htmlspecialchars($activeGroup['nama_group'] ?? 'SPMB 2026/2027') ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Kuesioner Observasi Awal Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kelola tautan kuesioner observasi yang diisi oleh orang tua calon siswa, pantau hasil penilaian, dan broadcast tautan via WhatsApp.
            </p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= url('kelola-spmb/observasi-awal/pengaturan') ?>" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Pengaturan Soal & Aspek
            </a>
            <button type="button" 
                    onclick="openModalBroadcast()" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-emerald-300 text-xs sm:text-sm font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                </svg>
                Kirim Broadcast Link WA
            </button>
            <a href="<?= url('kelola-spmb/observasi-awal/export?' . http_build_query($filters)) ?>" 
               data-download="true" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 text-white text-xs sm:text-sm font-semibold hover:from-sky-700 hover:to-blue-700 transition-all shadow-md shadow-sky-600/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export CSV / Excel
            </a>
        </div>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Calon Siswa -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Siswa</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-slate-900"><?= number_format($stats['total_siswa'] ?? 0) ?></span>
                <span class="text-xs text-slate-400 ml-1">anak</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Terdaftar di group aktif</p>
        </div>

        <!-- Sudah Mengisi -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sudah Mengisi</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-emerald-600"><?= number_format($stats['total_sudah'] ?? 0) ?></span>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                    <?= $stats['persen_selesai'] ?? 0 ?>%
                </span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Telah diisi oleh orang tua</p>
        </div>

        <!-- Belum Mengisi -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Belum Mengisi</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <span class="text-2xl font-bold text-amber-600"><?= number_format($stats['total_belum'] ?? 0) ?></span>
                <span class="text-xs text-slate-400 ml-1">menunggu</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Perlu dikirimi tautan observasi</p>
        </div>

        <!-- Rata-rata Skor Capaian -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Skor</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-purple-600"><?= $stats['rata_persentase'] ?? 0 ?>%</span>
                <span class="text-[10px] text-slate-400">indeks rata-rata</span>
            </div>
            <div class="mt-1 flex items-center gap-1 text-[10px] text-slate-500">
                <span>Rata-rata persentase skor calon siswa</span>
            </div>
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="<?= url('kelola-spmb/observasi-awal') ?>" class="flex flex-col md:flex-row items-center gap-3">
            <!-- Jenjang / Unit -->
            <div class="w-full md:w-44 shrink-0">
                <select name="jenjang" onchange="this.form.submit()" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-sky-500 bg-white">
                    <option value="">Semua Unit</option>
                    <option value="PAUD" <?= $selectedJenjang === 'PAUD' ? 'selected' : '' ?>>PAUD</option>
                    <option value="SD" <?= $selectedJenjang === 'SD' ? 'selected' : '' ?>>SD</option>
                    <option value="SMP" <?= $selectedJenjang === 'SMP' ? 'selected' : '' ?>>SMP</option>
                    <option value="SMA" <?= $selectedJenjang === 'SMA' ? 'selected' : '' ?>>SMA</option>
                </select>
            </div>

            <!-- Status Observasi -->
            <div class="w-full md:w-48 shrink-0">
                <select name="status_observasi" onchange="this.form.submit()" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-sky-500 bg-white">
                    <option value="">Semua Status Observasi</option>
                    <option value="sudah" <?= $selectedStatus === 'sudah' ? 'selected' : '' ?>>Sudah Mengisi</option>
                    <option value="belum" <?= $selectedStatus === 'belum' ? 'selected' : '' ?>>Belum Mengisi (Menunggu)</option>
                </select>
            </div>

            <!-- Input Cari -->
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="<?= htmlspecialchars($searchKeyword) ?>" 
                       placeholder="Cari nama siswa, nomor pendaftaran, atau nama pengisi..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit" class="w-full md:w-auto px-4 py-2 rounded-xl bg-slate-800 text-white text-xs sm:text-sm font-semibold hover:bg-slate-900 transition-colors">
                    Filter
                </button>
                <?php if ($selectedJenjang || $selectedStatus || $searchKeyword): ?>
                    <a href="<?= url('kelola-spmb/observasi-awal') ?>" class="w-full md:w-auto px-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors text-center">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Calon Siswa & Hasil Observasi -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase text-[11px] tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Calon Siswa</th>
                        <th class="py-3.5 px-4">Unit / Jenjang</th>
                        <th class="py-3.5 px-4">Status & Hasil Observasi</th>
                        <th class="py-3.5 px-4">Tautan Khusus Orang Tua</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-normal">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada data calon siswa ditemukan.</p>
                                <p class="text-xs text-slate-400 mt-0.5">Coba ubah kata kunci pencarian atau filter di atas.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = ($page - 1) * $limit + 1;
                        foreach ($items as $it): 
                            $isSudah = ($it['status_observasi'] === 'Sudah Mengisi' && !empty($it['respon_id']));
                            $publicLink = $it['public_url'];
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- No -->
                                <td class="py-4 px-4 text-center font-medium text-slate-400">
                                    <?= $no++ ?>
                                </td>

                                <!-- Calon Siswa -->
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900 leading-snug">
                                        <?= htmlspecialchars($it['nama_lengkap']) ?>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-400 mt-0.5">
                                        <span class="font-mono text-slate-600"><?= htmlspecialchars($it['nomor_pendaftaran']) ?></span>
                                        <span>&bull;</span>
                                        <span><?= htmlspecialchars($it['jenis_pendaftaran']) ?></span>
                                    </div>
                                    <?php if (!empty($it['target_phone'])): ?>
                                        <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                            <span class="text-emerald-600 font-medium">WA:</span>
                                            <span><?= htmlspecialchars($it['target_phone']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Unit / Jenjang -->
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                        <?= htmlspecialchars($it['jenjang']) ?>
                                    </span>
                                </td>

                                <!-- Status & Hasil Observasi -->
                                <td class="py-4 px-4">
                                    <?php if ($isSudah): ?>
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                    Sudah Mengisi
                                                </span>
                                                <span class="text-xs font-bold text-slate-800">
                                                    Skor: <?= (int)$it['total_skor'] ?> / <?= (int)$it['skor_maksimal'] ?> (<?= (float)$it['persentase'] ?>%)
                                                </span>
                                            </div>

                                            <div class="text-[11px] text-slate-400">
                                                Oleh: <strong class="text-slate-600"><?= htmlspecialchars($it['nama_pengisi'] ?: 'Orang Tua') ?></strong>
                                                <?php if (!empty($it['submitted_at'])): ?>
                                                    &bull; <?= date('d/m/Y H:i', strtotime($it['submitted_at'])) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Menunggu Pengisian
                                            </span>
                                            <p class="text-[11px] text-slate-400 mt-1">Belum ada respon dari orang tua</p>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Tautan Khusus Orang Tua -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2">
                                        <button type="button" 
                                                onclick="copyLink('<?= htmlspecialchars($publicLink) ?>')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-colors"
                                                title="Salin Tautan">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                            </svg>
                                            Salin Link
                                        </button>

                                        <a href="<?= htmlspecialchars($publicLink) ?>" 
                                           target="_blank" 
                                           class="p-1.5 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-colors"
                                           title="Buka Formulir Observasi">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Tombol Detail Jawaban -->
                                        <button type="button" 
                                                onclick="showDetailObservasi(<?= (int)$it['pendaftar_id'] ?>)"
                                                class="p-2 rounded-xl text-slate-600 hover:text-sky-700 hover:bg-sky-50 border border-slate-200 transition-colors"
                                                title="Lihat Rincian Jawaban">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </button>

                                        <!-- Tombol Kirim WA Link Satuan -->
                                        <button type="button" 
                                                onclick="openModalWaSingle(<?= (int)$it['pendaftar_id'] ?>, '<?= htmlspecialchars(addslashes($it['nama_lengkap'])) ?>', '<?= htmlspecialchars(addslashes($it['target_phone'] ?: '')) ?>', '<?= htmlspecialchars(addslashes($publicLink)) ?>')"
                                                class="p-2 rounded-xl text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 border border-emerald-200 transition-colors"
                                                title="Kirim Link via WhatsApp">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                            </svg>
                                        </button>

                                        <!-- Tombol Cetak -->
                                        <a href="<?= url('kelola-spmb/observasi-awal/cetak/' . (int)$it['pendaftar_id']) ?>" 
                                           target="_blank" 
                                           class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 border border-slate-200 transition-colors"
                                           title="Cetak Lembar Observasi">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.103-1.272-3.036m12.984 0c-.598.933-1.032 1.96-1.272 3.036m-10.44 0a6.375 6.375 0 0 1 10.44 0m-10.44 0A6.375 6.375 0 0 0 12 18.75a6.375 6.375 0 0 0 5.22-4.921m-10.44 0L3 18.75m14.44-4.921L21 18.75M12 3v9m0 0L8.25 8.25M12 12l3.75-3.75" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between gap-4">
                <span class="text-xs text-slate-500">
                    Menampilkan Halaman <strong><?= $page ?></strong> dari <strong><?= $totalPages ?></strong> (Total <?= $totalItems ?> data)
                </span>
                <div class="flex items-center gap-1.5">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('kelola-spmb/observasi-awal?' . http_build_query(array_merge($filters, ['page' => $page - 1]))) ?>" 
                           class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Sebelumnya
                        </a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                        <a href="<?= url('kelola-spmb/observasi-awal?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>" 
                           class="px-3 py-1.5 rounded-lg text-xs font-semibold <?= $p === $page ? 'bg-sky-600 text-white shadow-sm' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('kelola-spmb/observasi-awal?' . http_build_query(array_merge($filters, ['page' => $page + 1]))) ?>" 
                           class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                            Selanjutnya
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL DETAIL JAWABAN OBSERVASI -->
<!-- ========================================================================= -->
<div id="modal-detail-observasi" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden my-8">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Detail Hasil Observasi Awal</h3>
                        <p id="detail-subtitle" class="text-xs text-slate-500">Memuat rincian jawaban...</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalDetail()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="detail-body-content" class="p-6 max-h-[75vh] overflow-y-auto custom-scrollbar space-y-6">
                <!-- Konten dinamis AJAX -->
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <a id="detail-btn-cetak" href="#" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.673-2.103-1.272-3.036m12.984 0c-.598.933-1.032 1.96-1.272 3.036m-10.44 0a6.375 6.375 0 0 1 10.44 0m-10.44 0A6.375 6.375 0 0 0 12 18.75a6.375 6.375 0 0 0 5.22-4.921m-10.44 0L3 18.75m14.44-4.921L21 18.75M12 3v9m0 0L8.25 8.25M12 12l3.75-3.75" />
                    </svg>
                    Cetak Lembar Observasi
                </a>
                <button type="button" onclick="closeModalDetail()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL KIRIM WA SATUAN -->
<!-- ========================================================================= -->
<div id="modal-wa-single" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 bg-emerald-50/80 px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Kirim Link Observasi via WhatsApp</h3>
                </div>
                <button type="button" onclick="closeModalWaSingle()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <input type="hidden" id="wa-single-pendaftar-id" value="">

                <div>
                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Calon Siswa</span>
                    <p id="wa-single-nama" class="text-sm font-bold text-slate-900"></p>
                </div>

                <div>
                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nomor Tujuan WA</span>
                    <input type="text" id="wa-single-phone" readonly class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm font-mono font-semibold text-slate-700">
                </div>

                <div>
                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tautan Khusus</span>
                    <input type="text" id="wa-single-link" readonly class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Pesan WhatsApp (Template Standar)
                    </label>
                    <textarea id="wa-single-template" rows="5" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-700 font-mono leading-relaxed focus:ring-2 focus:ring-emerald-500"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">Variabel otomatis: <code>{nama_siswa}</code>, <code>{nomor_pendaftaran}</code>, <code>{link_observasi}</code></p>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModalWaSingle()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="button" id="btn-send-wa-single" onclick="submitSendWaSingle()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all">
                    <span>Kirim Sekarang</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL BROADCAST LINK WA (DENGAN FILTER & LIMIT ANTI-BLOKIR) -->
<!-- ========================================================================= -->
<div id="modal-broadcast" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-slate-100 overflow-hidden my-8">
            <div class="flex items-center justify-between border-b border-slate-100 bg-emerald-50/80 px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Broadcast Link Observasi Awal</h3>
                        <p class="text-xs text-slate-500">Kirim otomatis tautan kuesioner ke orang tua</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalBroadcast()" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <!-- Pengaturan Filter Broadcast -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Filter Unit</label>
                        <select id="bc-filter-jenjang" onchange="loadBroadcastPreview()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="">Semua Unit</option>
                            <option value="PAUD">PAUD</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA">SMA</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Status Pengisian</label>
                        <select id="bc-filter-status" onchange="loadBroadcastPreview()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="belum" selected>Hanya yang Belum Mengisi</option>
                            <option value="">Semua (Termasuk yang Sudah)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Batasi Jumlah Kirim</label>
                        <select id="bc-filter-limit" onchange="loadBroadcastPreview()" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="0">Kirim ke Semua Target</option>
                            <option value="5">Maksimal 5 Nomor (Aman)</option>
                            <option value="10" selected>Maksimal 10 Nomor</option>
                            <option value="20">Maksimal 20 Nomor</option>
                            <option value="50">Maksimal 50 Nomor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jeda Antar Pesan</label>
                        <select id="bc-filter-delay" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold bg-white">
                            <option value="3">3 Detik (Cepat)</option>
                            <option value="5" selected>5 Detik (Disarankan)</option>
                            <option value="8">8 Detik (Sangat Aman)</option>
                        </select>
                    </div>
                </div>

                <!-- Alert Keamanan WA -->
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-emerald-800 flex items-start gap-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span><strong>Fitur Anti-Blokir Aktif:</strong> Pengiriman dilakukan bergiliran dengan jeda waktu teratur dan variabel dinamis agar aman dan tidak dicurigai sebagai spam.</span>
                </div>

                <!-- Ringkasan Target -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-600">Target Siswa Penerima:</span>
                    <span id="bc-target-count" class="font-bold text-emerald-700 text-sm">0 Siswa</span>
                </div>

                <!-- Template Pesan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Template Pesan</label>
                    <textarea id="bc-template" rows="4" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-700 font-mono leading-relaxed focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <!-- Live Progress Bar (Saat Mengirim) -->
                <div id="bc-progress-container" class="hidden space-y-2 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span id="bc-progress-status" class="text-slate-700">Mengirim pesan...</span>
                        <span id="bc-progress-percent" class="text-emerald-700 font-bold">0%</span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                        <div id="bc-progress-bar" class="h-full bg-emerald-600 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div id="bc-progress-log" class="text-[11px] font-mono text-slate-500 max-h-24 overflow-y-auto custom-scrollbar p-2 bg-slate-50 rounded-lg"></div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" id="bc-btn-cancel" onclick="closeModalBroadcast()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                    Tutup
                </button>
                <button type="button" id="bc-btn-start" onclick="startBroadcast()" class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all">
                    <span>Mulai Broadcast</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT LOGIC OBSERVASI AWAL -->
<!-- ========================================================================= -->
<script>
const CSRF_TOKEN = '<?= $csrfToken ?>';

// --- Copy Link Helper ---
function copyLink(url) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            showToast('Tautan observasi berhasil disalin ke clipboard!');
        }).catch(() => {
            fallbackCopyText(url);
        });
    } else {
        fallbackCopyText(url);
    }
}

function fallbackCopyText(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        document.execCommand('copy');
        showToast('Tautan observasi berhasil disalin!');
    } catch (err) {
        prompt("Salin tautan di bawah ini:", text);
    }
    document.body.removeChild(textArea);
}

function showToast(msg) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: msg,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    } else {
        alert(msg);
    }
}

// --- Detail Observasi Modal ---
function showDetailObservasi(pendaftarId) {
    const modal = document.getElementById('modal-detail-observasi');
    const bodyContent = document.getElementById('detail-body-content');
    const subtitle = document.getElementById('detail-subtitle');
    const btnCetak = document.getElementById('detail-btn-cetak');

    modal.classList.remove('hidden');
    bodyContent.innerHTML = `
        <div class="py-12 text-center text-slate-400">
            <svg class="w-8 h-8 mx-auto animate-spin text-sky-600 mb-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <p class="text-xs font-semibold">Mengambil data observasi...</p>
        </div>
    `;

    btnCetak.href = `<?= url('kelola-spmb/observasi-awal/cetak/') ?>${pendaftarId}`;

    fetch(`<?= url('kelola-spmb/observasi-awal/detail/') ?>${pendaftarId}`)
        .then(res => res.json())
        .then(res => {
            if (!res.success || !res.data) {
                bodyContent.innerHTML = `<div class="p-4 bg-rose-50 text-rose-700 text-xs rounded-xl font-semibold">${res.message || 'Data tidak ditemukan'}</div>`;
                return;
            }

            const p = res.data.pendaftar;
            const r = res.data.respon;
            const groups = res.data.group_breakdown || {};

            subtitle.innerText = `${p.nama_lengkap} (${p.nomor_pendaftaran}) - Unit ${p.jenjang}`;

            if (!r) {
                bodyContent.innerHTML = `
                    <div class="p-8 text-center bg-amber-50 rounded-2xl border border-amber-200">
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-2 font-bold">!</div>
                        <h4 class="text-sm font-bold text-amber-900">Belum Ada Pengisian Observasi</h4>
                        <p class="text-xs text-amber-700 mt-1">Orang tua calon siswa belum mengisi kuesioner observasi awal.</p>
                        <div class="mt-4">
                            <button type="button" onclick="copyLink('<?= url('spmb/observasi/') ?>${p.observasi_token}')" class="px-4 py-2 bg-amber-600 text-white rounded-xl text-xs font-bold shadow-sm">
                                Salin Link Observasi
                            </button>
                        </div>
                    </div>
                `;
                return;
            }

            let groupHtml = '';
            for (const gName in groups) {
                const g = groups[gName];
                let itemsHtml = '';
                g.items.forEach((it, idx) => {
                    itemsHtml += `
                        <div class="py-2.5 flex items-start justify-between gap-3 border-b border-slate-100 last:border-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-slate-800 leading-snug">${idx + 1}. ${escapeHtml(it.pertanyaan)}</p>
                                ${it.indikator ? `<p class="text-[11px] text-slate-400 mt-0.5">Indikator: ${escapeHtml(it.indikator)}</p>` : ''}
                            </div>
                            <div class="text-right shrink-0">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                    ${escapeHtml(it.pilihan_jawaban)}
                                </span>
                                <span class="block text-[10px] text-slate-400 mt-0.5">Skor: <strong>${it.skor}</strong>/${it.skor_maksimal}</span>
                            </div>
                        </div>
                    `;
                });

                groupHtml += `
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200">
                            <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider">${escapeHtml(g.nama_group)}</h5>
                            <span class="text-xs font-bold text-slate-700">${g.total_skor} / ${g.skor_maks} (${g.persen}%)</span>
                        </div>
                        <div>${itemsHtml}</div>
                    </div>
                `;
            }

            bodyContent.innerHTML = `
                <!-- Ringkasan Nilai Card -->
                <div class="p-4 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-700 text-white shadow-md">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <span class="text-xs text-sky-200 font-semibold uppercase tracking-wider">Status Kuesioner</span>
                            <h4 class="text-lg font-bold">Sudah Diisi Lengkap</h4>
                            <p class="text-xs text-sky-100 mt-0.5">Diisi oleh: <strong>${escapeHtml(r.nama_pengisi || '-')}</strong> (${escapeHtml(r.hubungan_pengisi || 'Wali')})</p>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-extrabold">${r.persentase}%</span>
                            <span class="block text-[11px] text-sky-200">Total Skor: ${r.total_skor} / ${r.skor_maksimal}</span>
                        </div>
                    </div>
                    ${r.catatan_orang_tua ? `
                        <div class="mt-3 pt-3 border-t border-sky-400/40 text-xs text-sky-100">
                            <strong class="text-white">Catatan Tambahan Ortu:</strong> <em>"${escapeHtml(r.catatan_orang_tua)}"</em>
                        </div>
                    ` : ''}
                </div>

                <!-- Per Group Breakdown -->
                <div class="space-y-4">
                    <h5 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Rincian Jawaban per Aspek / Group</h5>
                    ${groupHtml}
                </div>
            `;
        })
        .catch(err => {
            bodyContent.innerHTML = `<div class="p-4 bg-rose-50 text-rose-700 text-xs rounded-xl font-semibold">Gagal memuat rincian observasi: ${err.message}</div>`;
        });
}

function closeModalDetail() {
    document.getElementById('modal-detail-observasi').classList.add('hidden');
}

// --- Kirim WA Satuan ---
function openModalWaSingle(pendaftarId, nama, phone, link) {
    document.getElementById('wa-single-pendaftar-id').value = pendaftarId;
    document.getElementById('wa-single-nama').innerText = nama;
    document.getElementById('wa-single-phone').value = phone || '(Nomor belum tersedia)';
    document.getElementById('wa-single-link').value = link;

    const defaultTpl = `Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\nYth. Bapak/Ibu Orang Tua/Wali dari ananda *${nama}*.\n\nBerikut tautan Kuesioner Observasi Awal SIT Bina Insan Palu:\n${link}\n\nMohon kesediaan Bapak/Ibu untuk mengisi formulir di atas.\n\nTerima kasih.\nWassalamu'alaikum Warahmatullahi Wabarakatuh.`;
    document.getElementById('wa-single-template').value = defaultTpl;

    document.getElementById('modal-wa-single').classList.remove('hidden');
}

function closeModalWaSingle() {
    document.getElementById('modal-wa-single').classList.add('hidden');
}

function submitSendWaSingle() {
    const id = document.getElementById('wa-single-pendaftar-id').value;
    const phone = document.getElementById('wa-single-phone').value;
    const tpl = document.getElementById('wa-single-template').value;
    const btn = document.getElementById('btn-send-wa-single');

    if (!phone || phone.includes('belum')) {
        Swal.fire('Perhatian', 'Nomor WhatsApp calon siswa belum diisi.', 'warning');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = `Mengirim...`;

    fetch(`<?= url('kelola-spmb/observasi-awal/send-wa/') ?>${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: new URLSearchParams({
            <?= json_encode(CSRF_TOKEN_NAME) ?>: CSRF_TOKEN,
            csrf_token: CSRF_TOKEN,
            pendaftar_id: id,
            pesan_custom: tpl
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = `Kirim Sekarang`;
        if (data.success) {
            closeModalWaSingle();
            Swal.fire('Alhamdulillah', data.message || 'Pesan WhatsApp berhasil dikirim!', 'success');
        } else {
            Swal.fire('Gagal', data.message || 'Gagal mengirim pesan WhatsApp.', 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `Kirim Sekarang`;
        Swal.fire('Kesalahan', err.message || 'Terjadi kesalahan sistem.', 'error');
    });
}

// --- Broadcast WhatsApp ---
let broadcastTargets = [];
let isBroadcasting = false;

function openModalBroadcast() {
    document.getElementById('modal-broadcast').classList.remove('hidden');
    loadBroadcastPreview();
}

function closeModalBroadcast() {
    if (isBroadcasting) {
        Swal.fire('Peringatan', 'Proses broadcast sedang berjalan. Mohon tunggu hingga selesai.', 'warning');
        return;
    }
    document.getElementById('modal-broadcast').classList.add('hidden');
}

function loadBroadcastPreview() {
    const jenjang = document.getElementById('bc-filter-jenjang').value;
    const status = document.getElementById('bc-filter-status').value;
    const targetLabel = document.getElementById('bc-target-count');

    targetLabel.innerText = 'Menghitung...';

    fetch(`<?= url('kelola-spmb/observasi-awal/broadcast-data') ?>?jenjang=${encodeURIComponent(jenjang)}&status_observasi=${encodeURIComponent(status)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                broadcastTargets = data.items || [];
                const limitVal = parseInt(document.getElementById('bc-filter-limit').value) || 0;
                let finalCount = broadcastTargets.length;
                if (limitVal > 0 && finalCount > limitVal) {
                    finalCount = limitVal;
                }
                targetLabel.innerText = `${finalCount} dari total ${broadcastTargets.length} Siswa`;
                
                if (!document.getElementById('bc-template').value.trim() && data.defaultTemplate) {
                    document.getElementById('bc-template').value = data.defaultTemplate;
                }
            } else {
                targetLabel.innerText = '0 Siswa';
            }
        })
        .catch(() => {
            targetLabel.innerText = 'Gagal memuat data';
        });
}

async function startBroadcast() {
    const limitVal = parseInt(document.getElementById('bc-filter-limit').value) || 0;
    const delaySec = parseInt(document.getElementById('bc-filter-delay').value) || 5;
    const template = document.getElementById('bc-template').value.trim();

    let targets = [...broadcastTargets];
    if (limitVal > 0) {
        targets = targets.slice(0, limitVal);
    }

    if (targets.length === 0) {
        Swal.fire('Perhatian', 'Tidak ada target siswa yang memenuhi kriteria filter.', 'warning');
        return;
    }

    const confirm = await Swal.fire({
        title: 'Mulai Broadcast?',
        html: `Akan mengirim pesan WhatsApp ke <strong>${targets.length} orang tua calon siswa</strong> dengan jeda <strong>${delaySec} detik</strong> antar pesan.<br><br>Lanjutkan?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Jalankan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#059669'
    });

    if (!confirm.isConfirmed) return;

    isBroadcasting = true;
    document.getElementById('bc-btn-start').disabled = true;
    document.getElementById('bc-btn-cancel').disabled = true;
    document.getElementById('bc-progress-container').classList.remove('hidden');

    const progressBar = document.getElementById('bc-progress-bar');
    const progressStatus = document.getElementById('bc-progress-status');
    const progressPercent = document.getElementById('bc-progress-percent');
    const progressLog = document.getElementById('bc-progress-log');
    progressLog.innerHTML = '';

    let successCount = 0;
    let failCount = 0;

    for (let i = 0; i < targets.length; i++) {
        const item = targets[i];
        const percent = Math.round(((i + 1) / targets.length) * 100);
        progressStatus.innerText = `Mengirim ke (${i + 1}/${targets.length}): ${item.nama_lengkap}`;
        progressBar.style.width = percent + '%';
        progressPercent.innerText = percent + '%';

        try {
            const res = await fetch(`<?= url('kelola-spmb/observasi-awal/send-wa/') ?>${item.pendaftar_id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: new URLSearchParams({
                    <?= json_encode(CSRF_TOKEN_NAME) ?>: CSRF_TOKEN,
                    csrf_token: CSRF_TOKEN,
                    pendaftar_id: item.pendaftar_id,
                    pesan_custom: template
                })
            });
            const data = await res.json();
            if (data.success) {
                successCount++;
                progressLog.innerHTML += `<div class="text-emerald-700">✓ [${i+1}] Berhasil kirim ke ${escapeHtml(item.nama_lengkap)} (${escapeHtml(item.target_phone || '')})</div>`;
            } else {
                failCount++;
                progressLog.innerHTML += `<div class="text-rose-600">✗ [${i+1}] Gagal ke ${escapeHtml(item.nama_lengkap)}: ${escapeHtml(data.message || 'Error')}</div>`;
            }
        } catch (e) {
            failCount++;
            progressLog.innerHTML += `<div class="text-rose-600">✗ [${i+1}] Gagal jaringan ke ${escapeHtml(item.nama_lengkap)}: ${escapeHtml(e.message)}</div>`;
        }
        progressLog.scrollTop = progressLog.scrollHeight;

        // Jeda waktu antar pesan
        if (i < targets.length - 1) {
            await new Promise(resolve => setTimeout(resolve, delaySec * 1000));
        }
    }

    isBroadcasting = false;
    document.getElementById('bc-btn-start').disabled = false;
    document.getElementById('bc-btn-cancel').disabled = false;
    progressStatus.innerText = `Selesai! Berhasil: ${successCount}, Gagal: ${failCount}`;

    Swal.fire({
        title: 'Broadcast Selesai',
        html: `Proses broadcast selesai.<br><strong>Berhasil:</strong> ${successCount}<br><strong>Gagal/Dilewati:</strong> ${failCount}`,
        icon: 'success'
    });
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
