<?php
/**
 * View Galeri File Hasil Screening Psikolog - Modul SPMB
 * Portal BIP
 */
?>

<div class="space-y-6">
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-primary-600 font-medium mb-1">
                <a href="<?= url('kelola-spmb') ?>" class="hover:underline">Kelola SPMB</a>
                <span>&bull;</span>
                <span class="text-slate-500 font-bold">Hasil Screening Psikolog</span>
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-rose-500 to-red-600 text-white flex items-center justify-center shadow-md shadow-rose-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <span>Hasil Screening Psikolog</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Galeri berkas PDF laporan asesmen psikologi khusus calon siswa yang <strong class="text-emerald-700 font-bold">Lulus / Diterima</strong> (TA <?= e($activeGroup['tahun_ajaran'] ?? '2026/2027') ?>)
            </p>
        </div>

        <!-- Action Header Buttons -->
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <button type="button" onclick="bukaModalBroadcastPsikolog()"
                    class="px-3.5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Broadcast Link WA</span>
            </button>

            <a href="<?= url('kelola-spmb/screening-psikolog/export?' . http_build_query($filters)) ?>" 
               data-download="true"
               data-no-loading="true"
               download="Rekap_Screening_Psikolog.csv"
               class="px-3.5 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 hover:text-slate-900 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Export CSV/Excel</span>
            </a>

            <button type="button" onclick="openModalUploadQuick()"
                    class="px-4 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/25 transition-all flex items-center gap-2 active:scale-95">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Upload Hasil PDF</span>
            </button>
        </div>
    </div>

    <!-- Alert Flash Notifications -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-sm animate-fadeIn">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </div>
                <span><?= e($_SESSION['flash_success']) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center justify-between shadow-sm animate-fadeIn">
            <div class="flex items-center gap-2.5">
                <div class="w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </div>
                <span><?= e($_SESSION['flash_error']) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">&times;</button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Stat Metric Summary Bar -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Calon Siswa -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa Lulus / Diterima</p>
                <h3 class="text-xl sm:text-2xl font-black text-slate-800 mt-0.5"><?= number_format($stats['total_siswa'] ?? 0) ?></h3>
                <p class="text-[10px] text-emerald-600 font-semibold mt-0.5">Siap Melengkapi Psikolog</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
        </div>

        <!-- Sudah Upload PDF -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-emerald-200/80 shadow-sm flex items-center justify-between bg-gradient-to-br from-emerald-50/40 to-white">
            <div>
                <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Sudah Upload PDF
                </p>
                <h3 class="text-xl sm:text-2xl font-black text-emerald-800 mt-0.5"><?= number_format($stats['total_sudah_upload'] ?? 0) ?></h3>
                <p class="text-[10px] text-emerald-600/80 mt-0.5"><?= $stats['total_direkomendasikan'] ?? 0 ?> Direkomendasikan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-500/20">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>

        <!-- Belum Upload (Card Merah) -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-rose-300 shadow-sm flex items-center justify-between bg-gradient-to-br from-rose-50 to-white">
            <div>
                <p class="text-[11px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    Belum Upload (Merah)
                </p>
                <h3 class="text-xl sm:text-2xl font-black text-rose-700 mt-0.5"><?= number_format($stats['total_belum_upload'] ?? 0) ?></h3>
                <p class="text-[10px] text-rose-500 mt-0.5">Menunggu berkas psikolog</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/30">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
            </div>
        </div>

        <!-- Progres Persentase -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Progres Screening</p>
                <span class="text-xs font-black px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 border border-indigo-100">
                    <?= $stats['persen_selesai'] ?? 0 ?>%
                </span>
            </div>
            <div class="mt-2.5">
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-sky-500 to-indigo-600 rounded-full transition-all duration-500" 
                         style="width: <?= min(100, max(0, $stats['persen_selesai'] ?? 0)) ?>%"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] text-slate-400 mt-1 font-medium">
                    <span><?= $stats['total_sudah_upload'] ?? 0 ?> selesai</span>
                    <span>Sisa <?= $stats['total_belum_upload'] ?? 0 ?> siswa</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Control Panel -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-4 sm:p-5">
        <form method="GET" action="<?= url('kelola-spmb/screening-psikolog') ?>" id="formFilterScreening" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search Input -->
                <div class="lg:col-span-2 relative">
                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" 
                           placeholder="Cari nama calon siswa, no pendaftaran, asal sekolah..." 
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all">
                    <div class="absolute left-3 top-3 text-slate-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </div>
                </div>

                <!-- Filter Unit / Jenjang -->
                <div>
                    <select name="unit" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:bg-white focus:border-primary-500 outline-none">
                        <option value="">Semua Unit (PAUD, SD, SMP, SMA)</option>
                        <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $u): ?>
                            <option value="<?= $u ?>" <?= ($filters['jenjang'] === $u) ? 'selected' : '' ?>>Unit <?= $u ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Status File Screening (Sudah / Belum) -->
                <div>
                    <select name="status_screening" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:bg-white focus:border-primary-500 outline-none">
                        <option value="">Status Berkas: Semua</option>
                        <option value="belum" <?= ($filters['status_screening'] === 'belum') ? 'selected' : '' ?>>🔴 Belum Upload (Card Merah)</option>
                        <option value="sudah" <?= ($filters['status_screening'] === 'sudah') ? 'selected' : '' ?>>📄 Sudah Upload (Icon PDF)</option>
                    </select>
                </div>

                <!-- Filter Rekomendasi Psikolog -->
                <div>
                    <select name="status_rekomendasi" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:bg-white focus:border-primary-500 outline-none">
                        <option value="">Hasil Rekomendasi: Semua</option>
                        <option value="Direkomendasikan" <?= ($filters['status_rekomendasi'] === 'Direkomendasikan') ? 'selected' : '' ?>>Direkomendasikan</option>
                        <option value="Dipertimbangkan" <?= ($filters['status_rekomendasi'] === 'Dipertimbangkan') ? 'selected' : '' ?>>Dipertimbangkan</option>
                        <option value="Tidak Direkomendasikan" <?= ($filters['status_rekomendasi'] === 'Tidak Direkomendasikan') ? 'selected' : '' ?>>Tidak Direkomendasikan</option>
                    </select>
                </div>
            </div>

            <!-- Row 2 Filters: Status Pendaftaran & Status Kelulusan -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mr-1">Filter Tambahan:</span>

                    <!-- Filter Jenis Pendaftaran -->
                    <select name="jenis_pendaftaran" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-[11px] font-medium text-slate-700 outline-none">
                        <option value="">Jenis: Semua</option>
                        <option value="Siswa Baru" <?= ($filters['jenis_pendaftaran'] === 'Siswa Baru') ? 'selected' : '' ?>>Siswa Baru</option>
                        <option value="Pindahan" <?= ($filters['jenis_pendaftaran'] === 'Pindahan') ? 'selected' : '' ?>>Pindahan</option>
                        <option value="Daftar Tunggu" <?= ($filters['jenis_pendaftaran'] === 'Daftar Tunggu') ? 'selected' : '' ?>>Daftar Tunggu</option>
                    </select>

                    <!-- Indikator Khusus Kelulusan -->
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Status: Lulus / Diterima
                    </span>

                    <?php if (!empty($filters['search']) || !empty($filters['jenjang']) || !empty($filters['status_screening']) || !empty($filters['status_rekomendasi']) || !empty($filters['jenis_pendaftaran'])): ?>
                        <a href="<?= url('kelola-spmb/screening-psikolog') ?>" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 flex items-center gap-1 ml-2">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            Reset Filter
                        </a>
                    <?php endif; ?>
                </div>

                <div class="text-[11px] text-slate-400 font-medium">
                    Menampilkan <span class="font-bold text-slate-700"><?= count($items) ?></span> dari <span class="font-bold text-slate-700"><?= number_format($totalItems) ?></span> siswa
                </div>
            </div>
        </form>
    </div>

    <!-- GALLERY FILE GRID (GALERI FILE PDF HASIL SCREENING) -->
    <?php if (empty($items)): ?>
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-800">Tidak ada data siswa yang cocok</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                Coba sesuaikan kata kunci pencarian atau ubah filter status yang Anda pilih.
            </p>
            <a href="<?= url('kelola-spmb/screening-psikolog') ?>" class="inline-block mt-4 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all">
                Bersihkan Filter
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 2xl:grid-cols-6 gap-3 sm:gap-3.5">
            <?php foreach ($items as $item): 
                $hasPdf = !empty($item['file_pdf']);
                $unitBadgeColors = [
                    'PAUD' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'SD'   => 'bg-blue-50 text-blue-700 border-blue-200',
                    'SMP'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'SMA'  => 'bg-purple-50 text-purple-700 border-purple-200',
                ];
                $unitColor = $unitBadgeColors[$item['jenjang']] ?? 'bg-slate-50 text-slate-700 border-slate-200';

                // Status Kelulusan Badge
                $kelulusanBadges = [
                    'Lulus / Diterima' => 'bg-emerald-100 text-emerald-800',
                    'Cadangan'         => 'bg-amber-100 text-amber-800',
                    'Tidak Lulus'      => 'bg-rose-100 text-rose-800',
                    'Lolos Berkas'     => 'bg-sky-100 text-sky-800',
                    'Menunggu Seleksi' => 'bg-slate-100 text-slate-700'
                ];
                $stkBadge = $kelulusanBadges[$item['status_kelulusan']] ?? 'bg-slate-100 text-slate-700';
            ?>

            <?php if ($hasPdf): ?>
                <!-- ================= CARD 1: SUDAH ADA FILE PDF (COMPACT) ================= -->
                <div class="group bg-white rounded-2xl border border-slate-200/90 hover:border-sky-400 shadow-sm hover:shadow-lg hover:shadow-sky-500/10 transition-all duration-200 flex flex-col justify-between overflow-hidden relative">
                    
                    <!-- Card Top Header -->
                    <div class="p-2.5 pb-0 flex items-center justify-between gap-1.5">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border <?= $unitColor ?>">
                            <?= e($item['jenjang']) ?> &bull; <?= e($item['jenis_pendaftaran']) ?>
                        </span>

                        <!-- Badge Rekomendasi Psikolog -->
                        <?php 
                        $rekom = $item['status_rekomendasi'] ?: 'Direkomendasikan';
                        $rekomBadge = [
                            'Direkomendasikan'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Dipertimbangkan'       => 'bg-amber-50 text-amber-700 border-amber-200',
                            'Tidak Direkomendasikan'=> 'bg-rose-50 text-rose-700 border-rose-200',
                            'Belum Ada Hasil'       => 'bg-slate-50 text-slate-600 border-slate-200',
                        ][$rekom] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                        ?>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full border <?= $rekomBadge ?> truncate max-w-[100px]" title="<?= e($rekom) ?>">
                            <?= e($rekom) ?>
                        </span>
                    </div>

                    <!-- PDF Icon & File Preview Visual -->
                    <div class="p-2.5 pt-2 flex flex-col items-center">
                        <div class="w-full bg-slate-50/90 rounded-xl border border-slate-100 py-2.5 px-2 flex flex-col items-center text-center relative group-hover:bg-sky-50/40 transition-colors">
                            <!-- Compact PDF Icon -->
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-rose-500 to-red-600 text-white flex items-center justify-center shadow-md shadow-rose-500/20 group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>

                            <span class="text-[10px] font-bold text-slate-700 mt-1.5 truncate max-w-[130px]" title="<?= e($item['file_name_original'] ?? 'Laporan_Psikologi.pdf') ?>">
                                <?= e($item['file_name_original'] ?? 'Laporan_Psikologi.pdf') ?>
                            </span>

                            <div class="flex items-center gap-1.5 text-[9px] text-slate-400 mt-0.5">
                                <?php if (!empty($item['file_size'])): ?>
                                    <span><?= round($item['file_size'] / 1024, 1) ?> KB</span>
                                    <span>&bull;</span>
                                <?php endif; ?>
                                <span><?= $item['tanggal_screening'] ? date('d/m/Y', strtotime($item['tanggal_screening'])) : date('d/m/Y') ?></span>
                            </div>
                        </div>

                        <!-- Data Siswa Details -->
                        <div class="w-full mt-2 text-left">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="font-mono text-[9px] font-bold text-sky-600 bg-sky-50 px-1.5 py-0.5 rounded border border-sky-100">
                                    <?= e($item['nomor_pendaftaran']) ?>
                                </span>
                                <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded <?= $stkBadge ?>">
                                    <?= e($item['status_kelulusan'] ?: 'Menunggu') ?>
                                </span>
                            </div>

                            <h4 class="text-xs font-bold text-slate-800 truncate" title="<?= e($item['nama_lengkap']) ?>">
                                <?= e($item['nama_lengkap']) ?>
                            </h4>

                            <p class="text-[10px] text-slate-400 truncate mt-0.5">
                                <?= e($item['asal_sekolah'] ?: '-') ?>
                            </p>

                            <!-- WA Sent Status Indicator -->
                            <div class="mt-1.5 pt-1 border-t border-slate-100 flex items-center justify-between text-[9px]" id="waBadgeBox_<?= (int)$item['pendaftar_id'] ?>">
                                <?php if (!empty($item['wa_sent_at'])): ?>
                                    <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 truncate" title="WA Terakhir: <?= date('d/m/Y H:i', strtotime($item['wa_sent_at'])) ?>">
                                        <svg class="w-2.5 h-2.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                        <span>WA Terkirim</span>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 font-medium flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        <span>Belum WA</span>
                                    </span>
                                <?php endif; ?>
                                <span class="text-slate-400 font-mono text-[9px]"><?= e($item['no_wa'] ?: ($item['no_hp'] ?: '-')) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="p-2 bg-slate-50 border-t border-slate-100 flex items-center gap-1">
                        <!-- Preview PDF Button -->
                        <button type="button" 
                                onclick="previewPdfModal('<?= url('kelola-spmb/screening-psikolog/view/' . $item['pendaftar_id']) ?>', '<?= e($item['nama_lengkap']) ?>', '<?= e($item['nomor_pendaftaran']) ?>', '<?= url('spmb/psikolog/' . $item['biodata_token']) ?>', <?= (int)$item['pendaftar_id'] ?>, '<?= e($item['no_wa'] ?: $item['no_hp']) ?>')"
                                class="flex-1 py-1.5 px-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-[11px] flex items-center justify-center gap-1 shadow-sm active:scale-95 transition-all"
                                title="Lihat PDF">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span>Lihat</span>
                        </button>

                        <!-- Salin Link Publik Hasil Psikolog -->
                        <button type="button" 
                                onclick="salinLinkPsikolog('<?= url('spmb/psikolog/' . $item['biodata_token']) ?>', '<?= e($item['nama_lengkap']) ?>')"
                                class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-sky-50 text-sky-600 flex items-center justify-center transition-colors active:scale-95"
                                title="Salin Link Hasil Psikolog untuk Orang Tua">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                            </svg>
                        </button>

                        <!-- Kirim WA Japri Satuan -->
                        <button type="button" 
                                onclick="kirimWaPsikologSingle(<?= (int)$item['pendaftar_id'] ?>, '<?= e($item['nama_lengkap']) ?>', '<?= e($item['no_wa'] ?: $item['no_hp']) ?>')"
                                class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-emerald-50 text-emerald-600 flex items-center justify-center transition-colors active:scale-95"
                                title="Kirim Link Hasil Psikolog via WhatsApp">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                        </button>

                        <!-- Download PDF Button -->
                        <a href="<?= url('kelola-spmb/screening-psikolog/download/' . $item['pendaftar_id']) ?>" 
                           data-download="true"
                           data-no-loading="true"
                           class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 flex items-center justify-center transition-colors"
                           title="Unduh File PDF">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </a>

                        <!-- Edit / Update PDF Button -->
                        <button type="button" 
                                onclick='openModalUpload(<?= json_encode($item) ?>)'
                                class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 flex items-center justify-center transition-colors"
                                title="Ganti Berkas / Edit">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                            </svg>
                        </button>

                        <!-- Delete PDF Button -->
                        <form action="<?= url('kelola-spmb/screening-psikolog/hapus/' . $item['pendaftar_id']) ?>" 
                              method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas PDF hasil screening psikolog untuk siswa <?= e($item['nama_lengkap']) ?>?');"
                              class="inline">
                            <?= CSRF::field() ?>
                            <button type="submit" 
                                    class="p-1.5 rounded-lg bg-white border border-slate-200 hover:bg-rose-50 text-rose-600 flex items-center justify-center transition-colors"
                                    title="Hapus File PDF">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <!-- ================= CARD 2: BELUM ADA FILE (CARD MERAH COMPACT) ================= -->
                <!-- Sesuai permintaan spesifik: jika belum maka cardnya hanya merah -->
                <div class="group bg-gradient-to-br from-rose-50 via-red-50/50 to-white rounded-2xl border-2 border-rose-300 hover:border-rose-400 shadow-sm hover:shadow-lg hover:shadow-rose-600/10 transition-all duration-200 flex flex-col justify-between overflow-hidden relative">
                    
                    <!-- Top Ribbon Warning -->
                    <div class="p-2.5 pb-0 flex items-center justify-between gap-1.5">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border <?= $unitColor ?>">
                            <?= e($item['jenjang']) ?> &bull; <?= e($item['jenis_pendaftaran']) ?>
                        </span>

                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-rose-600 text-white shadow-sm shadow-rose-600/25 flex items-center gap-1">
                            <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span>
                            Belum Ada PDF
                        </span>
                    </div>

                    <!-- Placeholder Visual Merah Compact -->
                    <div class="p-2.5 pt-2 flex flex-col items-center">
                        <div class="w-full bg-white/90 rounded-xl border border-dashed border-rose-200 py-2.5 px-2 flex flex-col items-center text-center">
                            <!-- Empty Red PDF Icon -->
                            <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-500 flex items-center justify-center border border-rose-200 group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5 opacity-80" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>

                            <span class="text-[10px] font-bold text-rose-700 mt-1.5 leading-tight">
                                Belum Ada Laporan
                            </span>
                        </div>

                        <!-- Data Siswa Details -->
                        <div class="w-full mt-2 text-left">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="font-mono text-[9px] font-bold text-rose-600 bg-rose-100/70 px-1.5 py-0.5 rounded border border-rose-200">
                                    <?= e($item['nomor_pendaftaran']) ?>
                                </span>
                                <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded <?= $stkBadge ?>">
                                    <?= e($item['status_kelulusan'] ?: 'Menunggu') ?>
                                </span>
                            </div>

                            <h4 class="text-xs font-bold text-slate-800 truncate" title="<?= e($item['nama_lengkap']) ?>">
                                <?= e($item['nama_lengkap']) ?>
                            </h4>

                            <p class="text-[10px] text-slate-400 truncate mt-0.5">
                                <?= e($item['asal_sekolah'] ?: '-') ?>
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Action: Tombol Upload Merah Terang Compact -->
                    <div class="p-2 bg-white/90 border-t border-rose-100">
                        <button type="button" 
                                onclick='openModalUpload(<?= json_encode($item) ?>)'
                                class="w-full py-1.5 px-2 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-bold text-[11px] rounded-lg shadow-sm shadow-rose-600/25 flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            <span>Upload Berkas PDF</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php endforeach; ?>
        </div>

        <!-- Pagination Bar -->
        <?php if ($totalPages > 1): ?>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-6 border-t border-slate-200/80">
                <span class="text-xs text-slate-500">
                    Halaman <span class="font-bold text-slate-700"><?= $page ?></span> dari <span class="font-bold text-slate-700"><?= $totalPages ?></span> (Total <?= number_format($totalItems) ?> Calon Siswa)
                </span>

                <div class="flex items-center gap-1.5">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('kelola-spmb/screening-psikolog?' . http_build_query(array_merge($filters, ['page' => $page - 1]))) ?>" 
                           class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            &larr; Sebelumnya
                        </a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                        <a href="<?= url('kelola-spmb/screening-psikolog?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>" 
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition-all <?= ($p === $page) ? 'bg-sky-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= url('kelola-spmb/screening-psikolog?' . http_build_query(array_merge($filters, ['page' => $page + 1]))) ?>" 
                           class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Berikutnya &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ================= MODAL UPLOAD / UPDATE FILE PDF ================= -->
<div id="modalUploadScreening" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all animate-scaleIn">
        
        <!-- Modal Header -->
        <div class="p-6 pb-4 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-rose-50/50 to-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-rose-500 to-red-600 text-white flex items-center justify-center shadow-md shadow-rose-500/25 shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base" id="uploadModalTitle">Upload Hasil Screening Psikolog</h3>
                    <p class="text-xs text-slate-500" id="uploadModalSubtitle">Lampirkan dokumen PDF resmi hasil asesmen psikolog</p>
                </div>
            </div>
            <button type="button" onclick="closeModalUpload()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-lg font-bold transition-colors">
                &times;
            </button>
        </div>

        <!-- Form Upload -->
        <form action="<?= url('kelola-spmb/screening-psikolog/upload') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_url" value="<?= e($_SERVER['REQUEST_URI'] ?? url('kelola-spmb/screening-psikolog')) ?>">
            <input type="hidden" name="pendaftar_id" id="modalPendaftarId" value="">

            <!-- Info Calon Siswa (Readonly atau Select) -->
            <div id="boxPendaftarFixed" class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Calon Siswa:</span>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-800" id="fixedNamaSiswa">-</h4>
                    <p class="text-[11px] text-sky-600 font-mono font-bold" id="fixedNomorPendaftar">-</p>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-xl bg-white border border-slate-200 shadow-sm" id="fixedJenjang">-</span>
            </div>

            <!-- Alternatif: Dropdown Pilih Siswa jika buka dari tombol Upload Quick di header -->
            <div id="boxPendaftarSelect" class="hidden">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Pilih Calon Siswa <span class="text-rose-500">*</span>
                </label>
                <select id="selectPendaftarId" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none focus:border-rose-500">
                    <option value="">-- Pilih Nama Siswa / No Pendaftaran --</option>
                    <?php foreach ($allPendaftar as $p): ?>
                        <option value="<?= $p['id'] ?>" data-nama="<?= e($p['nama_lengkap']) ?>" data-nomor="<?= e($p['nomor_pendaftaran']) ?>" data-jenjang="<?= e($p['jenjang']) ?>">
                            <?= e($p['nomor_pendaftaran']) ?> - <?= e($p['nama_lengkap']) ?> (Unit <?= e($p['jenjang']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Upload Area Drag & Drop -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Berkas Laporan PDF <span class="text-rose-500">*</span>
                </label>
                <div class="relative border-2 border-dashed border-slate-300 hover:border-rose-400 rounded-2xl p-5 text-center bg-slate-50/60 hover:bg-rose-50/20 transition-all cursor-pointer" id="dropzoneArea">
                    <input type="file" name="file_pdf" id="filePdfInput" accept="application/pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mb-2">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-slate-700" id="labelFileName">
                            Tarik file PDF ke sini atau <span class="text-rose-600 underline">Pilih File</span>
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Format file khusus: <strong>.PDF</strong> (Maksimal 20 MB)</p>
                    </div>
                </div>
            </div>

            <!-- Status Rekomendasi Hasil Screening -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Hasil Rekomendasi Psikolog <span class="text-rose-500">*</span>
                    </label>
                    <select name="status_rekomendasi" id="inputStatusRekomendasi" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:border-rose-500">
                        <option value="Direkomendasikan">✅ Direkomendasikan</option>
                        <option value="Dipertimbangkan">⚠️ Dipertimbangkan</option>
                        <option value="Tidak Direkomendasikan">❌ Tidak Direkomendasikan</option>
                        <option value="Belum Ada Hasil">⏳ Belum Ada Hasil</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Tanggal Asesmen / Tes
                    </label>
                    <input type="date" name="tanggal_screening" id="inputTanggalScreening" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 outline-none focus:border-rose-500">
                </div>
            </div>

            <!-- Nama Psikolog / Lembaga -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Nama Psikolog / Lembaga Penguji
                </label>
                <input type="text" name="nama_psikolog" id="inputNamaPsikolog" placeholder="Cth: Biro Psikologi Bina Insan / Dra. Psikolog..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 outline-none focus:border-rose-500">
            </div>

            <!-- Catatan Psikolog -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Catatan / Rekomendasi Khusus (Opsional)
                </label>
                <textarea name="catatan" id="inputCatatanPsikolog" rows="2" placeholder="Catatan kesiapan belajar, emosi, interaksi sosial, atau kebutuhan khusus..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 outline-none focus:border-rose-500"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeModalUpload()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-bold text-xs rounded-xl shadow-md shadow-rose-600/25 flex items-center gap-2 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                    <span>Simpan Berkas PDF</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ================= MODAL PDF VIEWER (PREVIEW PDF LANGSUNG) ================= -->
<div id="modalPdfViewer" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-md flex flex-col justify-between p-2 sm:p-4 md:p-6 overflow-hidden">
    <div class="bg-white rounded-3xl w-full h-full flex flex-col shadow-2xl overflow-hidden border border-white/20 animate-scaleIn">
        
        <!-- Viewer Top Bar -->
        <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between shrink-0 border-b border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-rose-600 flex items-center justify-center text-white shrink-0 font-bold text-xs">
                    PDF
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-white flex items-center gap-2">
                        <span id="viewerNamaSiswa">Nama Calon Siswa</span>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-white/20 text-sky-200" id="viewerNoPendaftar">-</span>
                    </h3>
                    <p class="text-[11px] text-slate-400">Pratinjau Berkas PDF Hasil Screening Psikolog</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Salin Link Publik -->
                <button type="button" id="viewerBtnCopyLink" onclick="viewerSalinLinkAction()" class="p-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer" title="Salin Link Publik Hasil Psikolog">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                    </svg>
                    <span class="hidden sm:inline">Salin Link</span>
                </button>

                <!-- Kirim WA -->
                <button type="button" id="viewerBtnSendWa" onclick="viewerKirimWaAction()" class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer" title="Kirim ke WhatsApp Orang Tua">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span class="hidden sm:inline">Kirim WA</span>
                </button>

                <!-- Buka Tab Baru -->
                <a id="viewerBtnOpenNewTab" href="#" target="_blank" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors" title="Buka di Jendela Baru">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                    <span class="hidden sm:inline">Tab Baru</span>
                </a>

                <!-- Download -->
                <a id="viewerBtnDownload" href="#" data-download="true" data-no-loading="true" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors" title="Unduh File">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span class="hidden sm:inline">Unduh</span>
                </a>

                <!-- Tutup -->
                <button type="button" onclick="closeModalPdfViewer()" class="p-2 rounded-xl bg-rose-600/80 hover:bg-rose-600 text-white transition-colors" title="Tutup Viewer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Viewer Iframe Frame -->
        <div class="flex-1 bg-slate-100 relative overflow-hidden">
            <div id="viewerLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-white/80 z-10">
                <div class="w-10 h-10 border-4 border-rose-600 border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs font-bold text-slate-600 mt-3">Memuat Dokumen PDF...</p>
            </div>
            <iframe id="viewerPdfFrame" src="" class="w-full h-full border-0" onload="document.getElementById('viewerLoading').classList.add('hidden')"></iframe>
        </div>
    </div>
</div>

<!-- ================= JAVASCRIPT LOGIC ================= -->
<script>
    // 1. Modal Upload Setup
    const modalUpload = document.getElementById('modalUploadScreening');
    const modalPendaftarId = document.getElementById('modalPendaftarId');
    const boxPendaftarFixed = document.getElementById('boxPendaftarFixed');
    const boxPendaftarSelect = document.getElementById('boxPendaftarSelect');
    const selectPendaftarId = document.getElementById('selectPendaftarId');
    const fixedNamaSiswa = document.getElementById('fixedNamaSiswa');
    const fixedNomorPendaftar = document.getElementById('fixedNomorPendaftar');
    const fixedJenjang = document.getElementById('fixedJenjang');
    const inputStatusRekomendasi = document.getElementById('inputStatusRekomendasi');
    const inputTanggalScreening = document.getElementById('inputTanggalScreening');
    const inputNamaPsikolog = document.getElementById('inputNamaPsikolog');
    const inputCatatanPsikolog = document.getElementById('inputCatatanPsikolog');
    const filePdfInput = document.getElementById('filePdfInput');
    const labelFileName = document.getElementById('labelFileName');

    // Handle Open from Card (Fixed student)
    function openModalUpload(item) {
        modalPendaftarId.value = item.pendaftar_id || item.id;
        boxPendaftarFixed.classList.remove('hidden');
        boxPendaftarSelect.classList.add('hidden');

        fixedNamaSiswa.textContent = item.nama_lengkap || '-';
        fixedNomorPendaftar.textContent = item.nomor_pendaftaran || '-';
        fixedJenjang.textContent = 'Unit ' + (item.jenjang || 'SD');

        document.getElementById('uploadModalTitle').textContent = item.file_pdf ? 'Perbarui Hasil Screening Psikolog' : 'Upload Hasil Screening Psikolog';
        
        if (item.status_rekomendasi) {
            inputStatusRekomendasi.value = item.status_rekomendasi;
        } else {
            inputStatusRekomendasi.value = 'Direkomendasikan';
        }

        if (item.tanggal_screening) {
            inputTanggalScreening.value = item.tanggal_screening;
        }
        inputNamaPsikolog.value = item.nama_psikolog || '';
        inputCatatanPsikolog.value = item.catatan_psikolog || item.catatan || '';

        // Reset file input label
        labelFileName.innerHTML = item.file_name_original 
            ? `File tersimpan: <strong class="text-rose-600">${item.file_name_original}</strong> (Pilih file baru jika ingin mengganti)`
            : 'Tarik file PDF ke sini atau <span class="text-rose-600 underline">Pilih File</span>';

        filePdfInput.value = '';
        modalUpload.classList.remove('hidden');
    }

    // Handle Open from Quick Button (Dropdown select student)
    function openModalUploadQuick() {
        modalPendaftarId.value = '';
        boxPendaftarFixed.classList.add('hidden');
        boxPendaftarSelect.classList.remove('hidden');
        selectPendaftarId.value = '';
        
        document.getElementById('uploadModalTitle').textContent = 'Upload Hasil Screening Psikolog';
        inputStatusRekomendasi.value = 'Direkomendasikan';
        inputNamaPsikolog.value = '';
        inputCatatanPsikolog.value = '';
        labelFileName.innerHTML = 'Tarik file PDF ke sini atau <span class="text-rose-600 underline">Pilih File</span>';
        filePdfInput.value = '';

        modalUpload.classList.remove('hidden');
    }

    // Sync select dropdown with hidden input
    if (selectPendaftarId) {
        selectPendaftarId.addEventListener('change', function() {
            modalPendaftarId.value = this.value;
        });
    }

    function closeModalUpload() {
        modalUpload.classList.add('hidden');
        if (window.LoadingModal && typeof LoadingModal.hide === 'function') {
            LoadingModal.hide();
        }
    }

    // Update label when file selected
    if (filePdfInput) {
        filePdfInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const f = this.files[0];
                const kb = Math.round(f.size / 1024);
                labelFileName.innerHTML = `<span class="text-emerald-600 font-bold">✓ Terpilih:</span> ${f.name} (${kb} KB)`;
            }
        });
    }

    // 2. Modal PDF Viewer
    const modalViewer = document.getElementById('modalPdfViewer');
    const viewerPdfFrame = document.getElementById('viewerPdfFrame');
    const viewerNamaSiswa = document.getElementById('viewerNamaSiswa');
    const viewerNoPendaftar = document.getElementById('viewerNoPendaftar');
    const viewerBtnOpenNewTab = document.getElementById('viewerBtnOpenNewTab');
    const viewerBtnDownload = document.getElementById('viewerBtnDownload');
    const viewerLoading = document.getElementById('viewerLoading');

    let viewerCurrentPublicUrl = '';
    let viewerCurrentNama = '';
    let viewerCurrentPendaftarId = 0;
    let viewerCurrentPhone = '';

    function previewPdfModal(pdfUrl, namaSiswa, noPendaftar, publicUrl = '', pendaftarId = 0, phone = '') {
        viewerCurrentPublicUrl = publicUrl;
        viewerCurrentNama = namaSiswa;
        viewerCurrentPendaftarId = pendaftarId;
        viewerCurrentPhone = phone;

        viewerNamaSiswa.textContent = namaSiswa;
        viewerNoPendaftar.textContent = noPendaftar;
        viewerBtnOpenNewTab.href = pdfUrl;
        viewerBtnDownload.href = pdfUrl.replace('/view/', '/download/');

        viewerLoading.classList.remove('hidden');
        viewerPdfFrame.src = pdfUrl;
        modalViewer.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModalPdfViewer() {
        modalViewer.classList.add('hidden');
        viewerPdfFrame.src = '';
        if (viewerLoading) viewerLoading.classList.add('hidden');
        if (window.LoadingModal && typeof LoadingModal.hide === 'function') {
            LoadingModal.hide();
        }
        document.body.style.overflow = '';
    }

    function viewerSalinLinkAction() {
        if (!viewerCurrentPublicUrl) {
            alert('Tautan publik belum tersedia untuk dokumen ini.');
            return;
        }
        salinLinkPsikolog(viewerCurrentPublicUrl, viewerCurrentNama);
    }

    function viewerKirimWaAction() {
        if (!viewerCurrentPendaftarId) {
            alert('Data siswa tidak ditemukan.');
            return;
        }
        kirimWaPsikologSingle(viewerCurrentPendaftarId, viewerCurrentNama, viewerCurrentPhone);
    }

    // 3. Salin Link ke Clipboard & Toast Notification
    function showToast(pesan, tipe = 'success') {
        const box = document.getElementById('toastNotificationBox');
        if (!box) return;

        const toast = document.createElement('div');
        const isSuccess = tipe === 'success';
        const bgClass = isSuccess ? 'bg-slate-900 text-white border-slate-700' : 'bg-rose-900 text-white border-rose-700';
        const icon = isSuccess ? '✓' : '⚠️';

        toast.className = `p-3.5 rounded-2xl shadow-xl border text-xs font-semibold flex items-center gap-2.5 transform transition-all duration-300 translate-y-3 opacity-0 ${bgClass} pointer-events-auto max-w-sm`;
        toast.innerHTML = `
            <div class="w-6 h-6 rounded-full ${isSuccess ? 'bg-emerald-500' : 'bg-rose-500'} text-white flex items-center justify-center shrink-0 font-bold text-xs">${icon}</div>
            <div class="flex-1 text-[11px] leading-snug">${pesan}</div>
        `;

        box.appendChild(toast);

        // Animate In
        setTimeout(() => {
            toast.classList.remove('translate-y-3', 'opacity-0');
        }, 20);

        // Animate Out & Remove
        setTimeout(() => {
            toast.classList.add('translate-y-3', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    function salinLinkPsikolog(url, namaSiswa) {
        if (!url) {
            alert('Tautan berkas belum tersedia.');
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => {
                showToast(`Link berkas hasil psikolog ananda <strong>${namaSiswa}</strong> berhasil disalin ke clipboard!`);
            }).catch(() => {
                fallbackCopyText(url, namaSiswa);
            });
        } else {
            fallbackCopyText(url, namaSiswa);
        }
    }

    function fallbackCopyText(text, namaSiswa) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            showToast(`Link berkas hasil psikolog ananda <strong>${namaSiswa}</strong> berhasil disalin!`);
        } catch (err) {
            prompt('Salin link berikut secara manual:', text);
        }
        document.body.removeChild(textArea);
    }

    // 4. Kirim WhatsApp Satuan per Siswa
    function kirimWaPsikologSingle(pendaftarId, namaSiswa, phone) {
        const phoneLabel = phone ? `ke nomor (${phone})` : 'ke nomor terdaftar';
        if (!confirm(`Kirim tautan laporan hasil psikolog ananda "${namaSiswa}" ${phoneLabel}?`)) {
            return;
        }

        showToast(`Sedang mengirim WhatsApp ke orang tua ${namaSiswa}...`, 'info');

        const formData = new FormData();
        formData.append('csrf_token', '<?= CSRF::token() ?>');
        formData.append('pendaftar_id', pendaftarId);

        fetch('<?= url('kelola-spmb/screening-psikolog/send-wa/') ?>' + pendaftarId, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(`✓ Pesan WhatsApp berhasil dikirim ke orang tua <strong>${namaSiswa}</strong>!`, 'success');
                // Perbarui badge pada kartu siswa jika ada di DOM
                const badgeBox = document.getElementById('waBadgeBox_' + pendaftarId);
                if (badgeBox) {
                    badgeBox.innerHTML = `
                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 truncate">
                            <svg class="w-2.5 h-2.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                            <span>WA Terkirim</span>
                        </span>
                        <span class="text-slate-400 font-mono text-[9px]">${phone || '-'}</span>
                    `;
                }
            } else {
                alert('⚠️ Gagal mengirim WhatsApp: ' + (res.message || 'Terjadi kesalahan pada server WhatsApp gateway.'));
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan koneksi saat mengirim WhatsApp.');
        });
    }

    // 5. MODAL & LOGIKA BROADCAST WHATSAPP HASIL PSIKOLOG (BATCH & ANTI-BLOKIR)
    const modalBroadcast = document.getElementById('modalBroadcastPsikolog');
    let broadcastQueue = [];
    let isBroadcastRunning = false;
    let defaultTemplateCached = '';

    function bukaModalBroadcastPsikolog() {
        if (modalBroadcast) {
            modalBroadcast.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            muatDataBroadcast();
        }
    }

    function tutupModalBroadcastPsikolog() {
        if (isBroadcastRunning) {
            if (!confirm('Pengiriman broadcast sedang berjalan. Yakin ingin menutup jendela ini?')) {
                return;
            }
            isBroadcastRunning = false;
        }
        if (modalBroadcast) {
            modalBroadcast.classList.add('hidden');
            document.body.style.overflow = '';
        }
        if (window.LoadingModal && typeof LoadingModal.hide === 'function') {
            LoadingModal.hide();
        }
    }

    function toggleBcPreviewList() {
        const list = document.getElementById('bcPreviewList');
        const arrow = document.getElementById('bcPreviewArrow');
        if (list) {
            const isHidden = list.classList.contains('hidden');
            if (isHidden) {
                list.classList.remove('hidden');
                if (arrow) arrow.textContent = '▲';
            } else {
                list.classList.add('hidden');
                if (arrow) arrow.textContent = '▼';
            }
        }
    }

    function resetBcTemplate() {
        if (defaultTemplateCached) {
            document.getElementById('bcCustomTemplate').value = defaultTemplateCached;
            showToast('Template teks dikembalikan ke standar resmi.');
        }
    }

    function muatDataBroadcast() {
        const unit = document.getElementById('bcFilterUnit').value;
        const rekom = document.getElementById('bcFilterRekom').value;
        const statusWa = document.getElementById('bcFilterStatusWa').value;
        const limit = document.getElementById('bcFilterLimit').value;

        const txtTarget = document.getElementById('txtBcTotalTarget');
        const txtPreviewCount = document.getElementById('txtBcPreviewCount');
        const previewList = document.getElementById('bcPreviewList');
        const btnMulai = document.getElementById('btnMulaiBroadcast');

        if (txtTarget) txtTarget.textContent = 'Memuat...';

        const params = new URLSearchParams({
            jenjang: unit,
            status_rekomendasi: rekom,
            status_wa: statusWa,
            limit: limit
        });

        fetch('<?= url('kelola-spmb/screening-psikolog/broadcast-data') ?>?' + params.toString())
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                if (txtTarget) txtTarget.textContent = '0 Siswa';
                return;
            }

            broadcastQueue = res.items || [];
            defaultTemplateCached = res.defaultTemplate || '';

            const tArea = document.getElementById('bcCustomTemplate');
            if (tArea && !tArea.value) {
                tArea.value = defaultTemplateCached;
            }

            const total = broadcastQueue.length;
            if (txtTarget) txtTarget.textContent = `${total} Siswa Siap Dikirim`;
            if (txtPreviewCount) txtPreviewCount.textContent = total;

            if (btnMulai) {
                btnMulai.disabled = (total === 0);
                if (total === 0) {
                    btnMulai.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    btnMulai.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            // Render Preview List
            if (previewList) {
                if (total === 0) {
                    previewList.innerHTML = '<div class="p-3 text-center text-slate-400">Tidak ada calon siswa yang cocok dengan filter yang dipilih.</div>';
                } else {
                    let html = '';
                    broadcastQueue.forEach((it, idx) => {
                        const phone = it.target_phone || '<span class="text-rose-500 font-bold">No HP Kosong</span>';
                        const waBadge = it.wa_sent_count > 0 
                            ? `<span class="text-emerald-600 font-bold">[Sudah Dikirim ${it.wa_sent_count}x]</span>` 
                            : `<span class="text-slate-400 font-medium">[Belum Pernah]</span>`;
                        html += `
                            <div class="py-1.5 px-2 flex items-center justify-between gap-2 hover:bg-slate-50 rounded">
                                <div class="truncate">
                                    <span class="text-slate-400 font-bold">${idx + 1}.</span>
                                    <strong class="text-slate-800">${it.nama_lengkap}</strong>
                                    <span class="text-slate-400">(${it.nomor_pendaftaran} - ${it.jenjang})</span>
                                </div>
                                <div class="shrink-0 flex items-center gap-2 text-[10px]">
                                    <span class="font-mono text-slate-600">${phone}</span>
                                    ${waBadge}
                                </div>
                            </div>
                        `;
                    });
                    previewList.innerHTML = html;
                }
            }
        })
        .catch(err => {
            if (txtTarget) txtTarget.textContent = 'Gagal memuat';
        });
    }

    async function mulaiBroadcastPsikolog() {
        if (!broadcastQueue || broadcastQueue.length === 0) {
            alert('Tidak ada calon siswa pada antrean broadcast.');
            return;
        }

        const total = broadcastQueue.length;
        const delayMs = parseInt(document.getElementById('bcFilterDelay').value) || 3000;
        const delayDetik = Math.round(delayMs / 1000);

        if (!confirm(`Mulai pengiriman WhatsApp hasil psikolog ke ${total} calon siswa?\n\nPengiriman akan dilakukan secara bertahap dengan jeda ${delayDetik} detik per pesan untuk keamanan nomor WhatsApp.`)) {
            return;
        }

        isBroadcastRunning = true;

        const btnMulai = document.getElementById('btnMulaiBroadcast');
        const btnBatal = document.getElementById('btnBatalBroadcast');
        const btnHentikan = document.getElementById('btnHentikanBroadcast');
        const progressBox = document.getElementById('bcProgressBox');
        const progressBar = document.getElementById('bcProgressBar');
        const progressLabel = document.getElementById('bcProgressLabel');
        const progressPct = document.getElementById('bcProgressPct');
        const logConsole = document.getElementById('bcLogConsole');
        const customTemplate = document.getElementById('bcCustomTemplate').value;

        if (btnMulai) btnMulai.classList.add('hidden');
        if (btnBatal) btnBatal.disabled = true;
        if (btnHentikan) btnHentikan.classList.remove('hidden');
        if (progressBox) progressBox.classList.remove('hidden');

        progressBar.style.width = '0%';
        progressPct.innerText = '0%';
        logConsole.innerHTML = `<div class="text-teal-400 font-bold">🚀 Memulai pengiriman antrean ${total} pesan WhatsApp... (Jeda: ${delayDetik}s)</div>`;

        let successCount = 0;
        let failCount = 0;

        for (let i = 0; i < total; i++) {
            if (!isBroadcastRunning) {
                logConsole.innerHTML += `<div class="text-amber-400 font-bold py-1">⏸ Pengiriman dihentikan oleh pengguna pada nomor ke-${i + 1}.</div>`;
                break;
            }

            const item = broadcastQueue[i];
            const currentNo = i + 1;
            const pct = Math.round((currentNo / total) * 100);

            progressLabel.textContent = `Mengirim ${currentNo} dari ${total}: ${item.nama_lengkap} (${item.target_phone || '-'})`;
            progressBar.style.width = pct + '%';
            progressPct.innerText = pct + '%';

            // Kirim via AJAX request
            try {
                const formData = new FormData();
                formData.append('csrf_token', '<?= CSRF::token() ?>');
                formData.append('pendaftar_id', item.pendaftar_id);
                if (customTemplate) {
                    formData.append('pesan_custom', customTemplate);
                }

                const response = await fetch('<?= url('kelola-spmb/screening-psikolog/broadcast-send-single') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const res = await response.json();

                if (res.success) {
                    successCount++;
                    logConsole.innerHTML += `<div class="text-emerald-400">[✓ Selesai] ${currentNo}. ${item.nama_lengkap} (${item.target_phone}): Pesan Terkirim</div>`;
                    
                    // Update badge di card jika ada
                    const badgeBox = document.getElementById('waBadgeBox_' + item.pendaftar_id);
                    if (badgeBox) {
                        badgeBox.innerHTML = `
                            <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 truncate">
                                <svg class="w-2.5 h-2.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                <span>WA Terkirim</span>
                            </span>
                            <span class="text-slate-400 font-mono text-[9px]">${item.target_phone || '-'}</span>
                        `;
                    }
                } else {
                    failCount++;
                    logConsole.innerHTML += `<div class="text-rose-400">[✗ Gagal] ${currentNo}. ${item.nama_lengkap} (${item.target_phone}): ${res.message || 'Gagal terkirim'}</div>`;
                }
            } catch (err) {
                failCount++;
                logConsole.innerHTML += `<div class="text-rose-400">[✗ Gagal Koneksi] ${currentNo}. ${item.nama_lengkap}: ${err.message || 'Error'}</div>`;
            }

            // Scroll log console to bottom
            logConsole.scrollTop = logConsole.scrollHeight;

            // Tunggu jeda anti-spam jika masih ada nomor berikutnya
            if (i < total - 1 && isBroadcastRunning) {
                await new Promise(resolve => setTimeout(resolve, delayMs));
            }
        }

        isBroadcastRunning = false;
        progressLabel.textContent = `Pengiriman Selesai! Berhasil: ${successCount}, Gagal: ${failCount}.`;
        logConsole.innerHTML += `<div class="text-teal-300 font-bold border-t border-slate-700 pt-2 mt-2">🏁 PROSES SELESAI: ${successCount} pesan berhasil terkirim, ${failCount} gagal.</div>`;
        logConsole.scrollTop = logConsole.scrollHeight;

        if (btnMulai) btnMulai.classList.remove('hidden');
        if (btnBatal) btnBatal.disabled = false;
        if (btnHentikan) btnHentikan.classList.add('hidden');

        showToast(`🎉 Broadcast selesai! ${successCount} nomor berhasil terkirim.`);
    }

    function hentikanBroadcastPsikolog() {
        if (isBroadcastRunning) {
            isBroadcastRunning = false;
            showToast('Membatalkan pengiriman antrean broadcast...', 'info');
        }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModalUpload();
            closeModalPdfViewer();
            tutupModalBroadcastPsikolog();
        }
    });
</script>

<!-- ================= MODAL BROADCAST WHATSAPP HASIL PSIKOLOG ================= -->
<div id="modalBroadcastPsikolog" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl w-full max-w-2xl shadow-2xl border border-slate-100 overflow-hidden my-6 animate-scaleIn">
        
        <!-- Header Modal -->
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-sky-700 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white shrink-0 shadow-sm">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold tracking-tight">Broadcast Link Hasil Psikolog via WhatsApp</h3>
                    <p class="text-[11px] text-white/80">Kirim tautan laporan asesmen psikolog personal ke orang tua/wali murid</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalBroadcastPsikolog()" class="p-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors cursor-pointer">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
            
            <!-- Anti-Ban Advisory Box -->
            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 flex items-start gap-3">
                <span class="text-lg">🛡️</span>
                <div class="space-y-0.5 text-[11px] text-amber-900 leading-relaxed">
                    <strong class="font-bold block text-amber-950">Proteksi Anti-Blokir WhatsApp:</strong>
                    Sistem mengirim pesan secara bertahap (batch) dengan jeda waktu otomatis per pesan agar nomor WhatsApp tidak terdeteksi spam oleh Meta/WhatsApp. Disarankan mengirim 10–20 nomor per sesi.
                </div>
            </div>

            <!-- Filters Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-slate-50/80 rounded-2xl border border-slate-200/80">
                <!-- Filter Unit / Jenjang -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Pilih Unit / Jenjang:
                    </label>
                    <select id="bcFilterUnit" onchange="muatDataBroadcast()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:border-teal-500">
                        <option value="">Semua Unit (PAUD, TK, SD, SMP, SMA)</option>
                        <option value="PAUD">Unit PAUD IT</option>
                        <option value="TK">Unit TK IT</option>
                        <option value="SD">Unit SD IT</option>
                        <option value="SMP">Unit SMP IT</option>
                        <option value="SMA">Unit SMA IT</option>
                    </select>
                </div>

                <!-- Filter Rekomendasi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Hasil Rekomendasi:
                    </label>
                    <select id="bcFilterRekom" onchange="muatDataBroadcast()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:border-teal-500">
                        <option value="">Semua Rekomendasi</option>
                        <option value="Direkomendasikan">Direkomendasikan</option>
                        <option value="Dipertimbangkan">Dipertimbangkan</option>
                        <option value="Tidak Direkomendasikan">Tidak Direkomendasikan</option>
                    </select>
                </div>

                <!-- Filter Riwayat Pengiriman -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Status Riwayat Kirim:
                    </label>
                    <select id="bcFilterStatusWa" onchange="muatDataBroadcast()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:border-teal-500">
                        <option value="belum">Belum Pernah Dikirim WA (Disarankan)</option>
                        <option value="semua">Semua (Termasuk yang Pernah Dikirim)</option>
                        <option value="sudah">Hanya yang Sudah Pernah Dikirim</option>
                    </select>
                </div>

                <!-- Limit Batch Jumlah Kirim -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Batasi Jumlah Kirim Sesi Ini:
                    </label>
                    <select id="bcFilterLimit" onchange="muatDataBroadcast()" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-800 outline-none focus:border-teal-500">
                        <option value="5">5 Siswa (Uji Coba Cepat)</option>
                        <option value="10" selected>10 Siswa (Sangat Aman / Disarankan)</option>
                        <option value="20">20 Siswa (Aman)</option>
                        <option value="30">30 Siswa</option>
                        <option value="50">50 Siswa</option>
                        <option value="0">Kirim Semua Sekaligus</option>
                    </select>
                </div>

                <!-- Jeda Waktu Antar Pesan -->
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                        Jeda Waktu Antar Pesan (Delay Anti-Spam):
                    </label>
                    <select id="bcFilterDelay" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none focus:border-teal-500">
                        <option value="2000">2 Detik per pesan</option>
                        <option value="3000" selected>3 Detik per pesan (Rekomendasi Aman)</option>
                        <option value="5000">5 Detik per pesan (Ekstra Aman)</option>
                        <option value="7000">7 Detik per pesan (Sangat Aman)</option>
                    </select>
                </div>
            </div>

            <!-- Recipient Counter Banner -->
            <div class="p-3 bg-teal-50/70 border border-teal-200/80 rounded-2xl flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-600 animate-pulse"></span>
                    <span class="font-bold text-teal-950">Target Penerima Sesi Ini:</span>
                    <span id="txtBcTotalTarget" class="px-2 py-0.5 rounded-md bg-teal-600 text-white font-extrabold text-[11px]">Memuat...</span>
                </div>
                <button type="button" onclick="muatDataBroadcast()" class="text-[11px] text-teal-700 hover:text-teal-900 font-bold underline cursor-pointer">
                    Refresh Target
                </button>
            </div>

            <!-- Template Editor -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700">
                        Template Teks Pesan WhatsApp:
                    </label>
                    <button type="button" onclick="resetBcTemplate()" class="text-[10px] text-sky-600 hover:underline font-bold cursor-pointer">
                        Reset Default
                    </button>
                </div>
                <textarea id="bcCustomTemplate" rows="5" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-2xl font-mono text-[11px] text-slate-800 outline-none focus:border-teal-500 leading-relaxed custom-scrollbar"></textarea>
                <p class="text-[10px] text-slate-400 mt-1">
                    Tag otomatis: <code class="text-teal-600 font-bold">{nama_siswa}</code>, <code class="text-teal-600 font-bold">{nomor_pendaftaran}</code>, <code class="text-teal-600 font-bold">{nama_sekolah}</code>, <code class="text-teal-600 font-bold">{rekomendasi}</code>, <code class="text-teal-600 font-bold">{link_hasil_psikolog}</code>
                </p>
            </div>

            <!-- Preview List Accordion -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <button type="button" onclick="toggleBcPreviewList()" class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-left text-xs font-bold text-slate-700 flex items-center justify-between transition-colors cursor-pointer">
                    <span class="flex items-center gap-1.5">
                        <span>👥</span>
                        <span>Daftar Calon Penerima (<span id="txtBcPreviewCount">0</span>)</span>
                    </span>
                    <span id="bcPreviewArrow">▼</span>
                </button>
                <div id="bcPreviewList" class="hidden max-h-48 overflow-y-auto p-2 bg-white text-xs divide-y divide-slate-100 font-mono text-[11px] custom-scrollbar">
                    <div class="p-3 text-center text-slate-400">Belum memuat data penerima.</div>
                </div>
            </div>

            <!-- Progress & Live Log Box -->
            <div id="bcProgressBox" class="space-y-2 hidden">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span id="bcProgressLabel" class="text-teal-800">Menyiapkan pengiriman...</span>
                    <span id="bcProgressPct" class="text-teal-700 font-mono">0%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden shadow-inner">
                    <div id="bcProgressBar" class="bg-gradient-to-r from-emerald-500 via-teal-500 to-sky-500 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                
                <div id="bcLogConsole" class="max-h-36 overflow-y-auto p-3 bg-slate-900 text-slate-200 rounded-2xl font-mono text-[10px] space-y-1 custom-scrollbar shadow-inner">
                    <div class="text-teal-400">Siap memulai antrean broadcast WhatsApp...</div>
                </div>
            </div>

        </div>

        <!-- Footer Modal Buttons -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <button type="button" id="btnBatalBroadcast" onclick="tutupModalBroadcastPsikolog()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer">
                Tutup
            </button>
            <div class="flex items-center gap-2">
                <button type="button" id="btnHentikanBroadcast" onclick="hentikanBroadcastPsikolog()" class="hidden px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition-colors cursor-pointer">
                    ⏸ Jeda / Hentikan
                </button>
                <button type="button" id="btnMulaiBroadcast" onclick="mulaiBroadcastPsikolog()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold shadow-md shadow-emerald-600/20 text-xs transition-all flex items-center gap-2 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>Mulai Kirim Broadcast</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ================= TOAST NOTIFICATION CONTAINER ================= -->
<div id="toastNotificationBox" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none"></div>
