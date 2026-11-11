<?php
/**
 * View: Kelola Jurnal Guru (Admin & Monitoring Dashboard)
 */
$csrfToken = CSRF::token();
$currentQs = http_build_query([
    'search' => $search ?? '',
    'guru_id' => $guruId ?? '',
    'kelas' => $kelas ?? '',
    'status' => $status ?? '',
    'start_date' => $startDate ?? '',
    'end_date' => $endDate ?? '',
]);
?>

<!-- Header & Actions -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-3">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/25">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <span>Kelola Jurnal Guru & Jam Mengajar</span>
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Monitoring pengisian jurnal KBM harian, verifikasi materi, dan rekapitulasi beban Jam Pelajaran (JP) guru.
        </p>
    </div>

    <div class="flex items-center gap-2.5">
        <!-- Tombol Cetak Rekapitulasi -->
        <a href="<?= url('kelola-jurnal-guru/cetak' . ($currentQs ? '?' . $currentQs : '')) ?>" 
           target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:border-slate-300 font-bold text-xs rounded-xl shadow-sm transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.376 1.942-5.908 5.28-5.908 3.337 0 5.52 2.532 5.28 5.908m-10.56 0A5.967 5.967 0 0 0 12 18.25c2.476 0 4.636-1.503 5.28-4.421m-10.56 0c0-1.077.29-2.09.794-2.964m9.766 2.964a5.986 5.986 0 0 0-.794-2.964M6.72 13.829A7.95 7.95 0 0 1 12 11.25a7.95 7.95 0 0 1 5.28 2.579M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v1.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 10.5v-1.5A2.25 2.25 0 0 1 6.75 6.75Z" />
            </svg>
            <span>Cetak Rekap KBM</span>
        </a>

        <!-- Shortcut ke Jadwal Pelajaran -->
        <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" 
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm shadow-blue-500/25 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.253 3.75h18M4.5 7.5h15a2.25 2.25 0 0 1 2.25 2.25v10.5A2.25 2.25 0 0 1 19.5 22.5h-15A2.25 2.25 0 0 1 2.25 20.25V9.75A2.25 2.25 0 0 1 4.5 7.5Z" />
            </svg>
            <span>Kelola Jadwal KBM</span>
        </a>
    </div>
</div>

<!-- 4 KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Card 1: Total Jurnal -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Jurnal KBM</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-black text-slate-800"><?= number_format($kpiFiltered['total_count'] ?? 0) ?></h3>
                <span class="text-xs text-slate-400 font-medium">terdata</span>
            </div>
            <p class="text-[11px] text-blue-600 font-semibold mt-1 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                <?= number_format($kpiMonth['count_month'] ?? 0) ?> jurnal bulan ini
            </p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
        </div>
    </div>

    <!-- Card 2: Total JP Terpenuhi -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Beban Mengajar</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-black text-indigo-600"><?= number_format($kpiFiltered['total_jp'] ?? 0) ?></h3>
                <span class="text-xs text-indigo-500 font-bold uppercase">JP</span>
            </div>
            <p class="text-[11px] text-slate-500 font-medium mt-1">
                <?= number_format($kpiMonth['jp_month'] ?? 0) ?> JP bulan ini
            </p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
    </div>

    <!-- Card 3: Guru Aktif Mengisi -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Guru Aktif Mengajar</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-black text-emerald-600"><?= number_format($kpiFiltered['total_guru_aktif'] ?? 0) ?></h3>
                <span class="text-xs text-slate-400 font-medium">dari <?= count($guruOptions) ?> guru</span>
            </div>
            <p class="text-[11px] text-emerald-600 font-semibold mt-1">
                <?= number_format($kpiToday['guru_today'] ?? 0) ?> guru mengajar hari ini
            </p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
        </div>
    </div>

    <!-- Card 4: Jurnal Hari Ini -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Jurnal Hari Ini</p>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-black text-amber-600"><?= number_format($kpiToday['count_today'] ?? 0) ?></h3>
                <span class="text-xs text-amber-600 font-bold uppercase"><?= number_format($kpiToday['jp_today'] ?? 0) ?> JP</span>
            </div>
            <p class="text-[11px] text-slate-400 font-medium mt-1">
                <?= date('d M Y') ?>
            </p>
        </div>
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-sm mb-6">
    <form method="GET" action="<?= url('kelola-jurnal-guru') ?>" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
        <!-- Search Keyword -->
        <div class="lg:col-span-2">
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Pencarian</label>
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="<?= e($search) ?>" 
                       placeholder="Cari materi, kode, mapel..." 
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>
        </div>

        <!-- Filter Guru -->
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Guru Pengajar</label>
            <select name="guru_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white">
                <option value="">Semua Guru</option>
                <?php foreach ($guruOptions as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= ($guruId == $g['id']) ? 'selected' : '' ?>>
                        <?= e($g['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Kelas -->
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Kelas</label>
            <select name="kelas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelasOptions as $kls): ?>
                    <option value="<?= e($kls) ?>" <?= ($kelas === $kls) ? 'selected' : '' ?>>
                        <?= e($kls) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Filter Status -->
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1.5">Status Validasi</label>
            <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white">
                <option value="">Semua Status</option>
                <option value="selesai" <?= ($status === 'selesai') ? 'selected' : '' ?>>Selesai</option>
                <option value="tervalidasi" <?= ($status === 'tervalidasi') ? 'selected' : '' ?>>Tervalidasi</option>
                <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft</option>
            </select>
        </div>

        <!-- Tombol Aksi Filter -->
        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all text-center">
                Filter
            </button>
            <a href="<?= url('kelola-jurnal-guru') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition-all" title="Reset Filter">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Table Data Jurnal -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-black text-slate-800 tracking-tight">Daftar Jurnal Pembelajaran Guru</h2>
            <p class="text-xs text-slate-400 mt-0.5">Menampilkan <?= count($jurnalList) ?> dari total <?= number_format($totalRows) ?> data</p>
        </div>
        <div class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg">
            Halaman <?= $page ?> / <?= $totalPages ?>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3.5 text-center w-12">No</th>
                    <th class="px-4 py-3.5">Tanggal & Jam</th>
                    <th class="px-4 py-3.5">Guru Pengajar</th>
                    <th class="px-4 py-3.5">Kelas & Mapel</th>
                    <th class="px-4 py-3.5 text-center">JP</th>
                    <th class="px-4 py-3.5">Materi Pokok & Kegiatan</th>
                    <th class="px-4 py-3.5 text-center">Kehadiran</th>
                    <th class="px-4 py-3.5 text-center">Status</th>
                    <th class="px-4 py-3.5 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($jurnalList)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-slate-700">Belum Ada Data Jurnal</h3>
                            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                Guru belum mengisi jurnal mengajar sesuai filter di atas atau silakan sesuaikan pencarian.
                            </p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = $offset + 1; foreach ($jurnalList as $row): ?>
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3.5 text-center font-bold text-slate-400"><?= $no++ ?></td>
                            
                            <!-- Tanggal & Jam -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-800">
                                    <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                                </div>
                                <div class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
                                    <span>Jam Ke-<?= e($row['jam_ke']) ?></span>
                                    <?php if (!empty($row['jam_mulai']) && !empty($row['jam_selesai'])): ?>
                                        <span>(<?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?>)</span>
                                    <?php endif; ?>
                                </div>
                                <span class="inline-block mt-1 text-[10px] text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded font-mono font-medium">
                                    <?= e($row['kode_jurnal']) ?>
                                </span>
                            </td>

                            <!-- Guru Pengajar -->
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-600 overflow-hidden shrink-0">
                                        <?php if (!empty($row['guru_foto']) && file_exists(BASE_PATH . '/public/' . ltrim($row['guru_foto'], '/'))): ?>
                                            <img src="<?= url($row['guru_foto']) ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span><?= strtoupper(substr($row['guru_nama_lengkap'] ?? 'G', 0, 1)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-800 truncate"><?= e($row['guru_nama_lengkap']) ?></div>
                                        <div class="text-[11px] text-slate-400">
                                            <?= !empty($row['nip']) ? 'NIP: ' . e($row['nip']) : (!empty($row['niy']) ? 'NIY: ' . e($row['niy']) : 'Guru Pengampu') ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kelas & Mapel -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-extrabold text-[11px]">
                                        <?= e($row['kelas']) ?>
                                    </span>
                                    <span class="truncate"><?= e($row['mata_pelajaran']) ?></span>
                                </div>
                                <?php if (!empty($row['ruangan'])): ?>
                                    <div class="text-[11px] text-slate-400 mt-0.5">R. <?= e($row['ruangan']) ?></div>
                                <?php endif; ?>
                            </td>

                            <!-- JP (Jam Pelajaran) -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black bg-indigo-100 text-indigo-800 border border-indigo-200 shadow-sm">
                                    <?= (int)($row['jumlah_jp'] ?? 2) ?> JP
                                </span>
                            </td>

                            <!-- Materi Pokok -->
                            <td class="px-4 py-3.5 max-w-xs">
                                <div class="font-bold text-slate-800 truncate" title="<?= e($row['materi_pokok']) ?>">
                                    <?= e($row['materi_pokok']) ?>
                                </div>
                                <?php if (!empty($row['uraian_kegiatan'])): ?>
                                    <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5" title="<?= e($row['uraian_kegiatan']) ?>">
                                        <?= e($row['uraian_kegiatan']) ?>
                                    </p>
                                <?php endif; ?>
                            </td>

                            <!-- Kehadiran -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">
                                    <?= e($row['kehadiran_summary'] ?: 'Semua Hadir') ?>
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <?php if ($row['status'] === 'tervalidasi'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                                        </svg>
                                        Tervalidasi
                                    </span>
                                <?php elseif ($row['status'] === 'selesai'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Selesai KBM
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Draft
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Detail Modal Button -->
                                    <button type="button" 
                                            onclick="openDetailModal(<?= $row['id'] ?>)" 
                                            class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors"
                                            title="Lihat Detail Jurnal">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    <!-- Quick Validate / Cancel Button -->
                                    <?php if ($row['status'] === 'tervalidasi'): ?>
                                        <form method="POST" action="<?= url('kelola-jurnal-guru/status/' . $row['id']) ?>" onsubmit="return confirm('Batalkan validasi jurnal ini?')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="status" value="selesai">
                                            <button type="submit" 
                                                    class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-colors"
                                                    title="Batalkan Validasi (Jadikan Selesai)">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                                                </svg>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('kelola-jurnal-guru/status/' . $row['id']) ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                            <input type="hidden" name="status" value="tervalidasi">
                                            <button type="submit" 
                                                    class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 flex items-center justify-center transition-colors"
                                                    title="Validasi Jurnal Ini">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Delete Button -->
                                    <form method="POST" action="<?= url('kelola-jurnal-guru/delete/' . $row['id']) ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan jurnal ini?')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" 
                                                class="w-7 h-7 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors"
                                                title="Hapus Jurnal">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
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
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <p class="text-xs text-slate-500">
                Menampilkan <?= $offset + 1 ?> sampai <?= min($offset + $perPage, $totalRows) ?> dari <?= $totalRows ?> entri
            </p>
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="<?= url('kelola-jurnal-guru?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>" 
                       class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        &laquo; Prev
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="<?= url('kelola-jurnal-guru?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold <?= ($i === $page) ? 'bg-blue-600 text-white' : 'border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= url('kelola-jurnal-guru?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>" 
                       class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                        Next &raquo;
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail Jurnal -->
<div id="modal-detail-jurnal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl animate-fade-in">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <span id="modal-kode-jurnal" class="text-[11px] font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded"></span>
                <h3 class="text-base font-black text-slate-800 mt-1">Detail Jurnal Mengajar</h3>
            </div>
            <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                ✕
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-600" id="modal-body-content">
            <!-- Loading indicator -->
            <div id="modal-loading" class="py-12 text-center">
                <div class="w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                <p class="text-xs text-slate-400 font-medium">Memuat detail jurnal...</p>
            </div>

            <!-- Content Detail (Filled via JS) -->
            <div id="modal-data" class="hidden space-y-4">
                <!-- Meta Info Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Guru</span>
                        <span id="dt-guru" class="font-bold text-slate-800 text-xs"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Kelas & Mapel</span>
                        <span id="dt-kelas-mapel" class="font-bold text-slate-800 text-xs"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Waktu & Jam</span>
                        <span id="dt-waktu" class="font-bold text-slate-800 text-xs"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] uppercase font-bold text-slate-400">Beban JP</span>
                        <span id="dt-jp" class="font-black text-indigo-700 text-xs"></span>
                    </div>
                </div>

                <!-- Materi Pokok -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Materi Pokok / Pembahasan</label>
                    <div id="dt-materi" class="p-3 bg-blue-50/50 border border-blue-100 rounded-xl font-semibold text-slate-800"></div>
                </div>

                <!-- Uraian Kegiatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Uraian Aktivitas Pembelajaran</label>
                    <div id="dt-uraian" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 leading-relaxed whitespace-pre-wrap"></div>
                </div>

                <!-- Catatan Kelas & Kehadiran -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Kelas / Kejadian Khusus</label>
                        <div id="dt-catatan" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 whitespace-pre-wrap"></div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ringkasan Kehadiran</label>
                        <div id="dt-kehadiran" class="p-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-emerald-700"></div>
                    </div>
                </div>

                <!-- Foto Kegiatan jika ada -->
                <div id="dt-foto-wrapper" class="hidden">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dokumentasi KBM</label>
                    <div class="rounded-2xl overflow-hidden border border-slate-200 max-h-60 bg-black/5 flex items-center justify-center">
                        <img id="dt-foto" src="" alt="Foto KBM" class="w-full h-auto object-cover max-h-60">
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-400">Status Jurnal:</span>
                    <span id="dt-status-badge"></span>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-end">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function openDetailModal(id) {
    const modal = document.getElementById('modal-detail-jurnal');
    const loading = document.getElementById('modal-loading');
    const dataContainer = document.getElementById('modal-data');
    
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    dataContainer.classList.add('hidden');

    fetch('<?= url("kelola-jurnal-guru/api/detail/") ?>' + id)
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                alert(res.message || 'Gagal memuat detail jurnal');
                closeDetailModal();
                return;
            }

            const item = res.data;
            document.getElementById('modal-kode-jurnal').textContent = item.kode_jurnal || 'JURNAL';
            document.getElementById('dt-guru').textContent = item.guru_nama_lengkap || item.nama_guru || '-';
            document.getElementById('dt-kelas-mapel').textContent = (item.kelas || '') + ' - ' + (item.mata_pelajaran || '');
            document.getElementById('dt-waktu').textContent = item.tanggal + ' (Jam Ke-' + item.jam_ke + ')';
            document.getElementById('dt-jp').textContent = (item.jumlah_jp || 2) + ' JP';
            document.getElementById('dt-materi').textContent = item.materi_pokok || '-';
            document.getElementById('dt-uraian').textContent = item.uraian_kegiatan || 'Tidak ada uraian kegiatan tambahan.';
            document.getElementById('dt-catatan').textContent = item.catatan_kelas || 'Kelas kondusif dan tertib.';
            document.getElementById('dt-kehadiran').textContent = item.kehadiran_summary || 'Semua Hadir';

            const fotoWrap = document.getElementById('dt-foto-wrapper');
            const fotoImg = document.getElementById('dt-foto');
            if (item.foto_kegiatan) {
                fotoImg.src = '<?= url("") ?>/' + item.foto_kegiatan.replace(/^\//, '');
                fotoWrap.classList.remove('hidden');
            } else {
                fotoWrap.classList.add('hidden');
            }

            const statusBadge = document.getElementById('dt-status-badge');
            if (item.status === 'tervalidasi') {
                statusBadge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">✓ Tervalidasi</span>';
            } else if (item.status === 'selesai') {
                statusBadge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Selesai KBM</span>';
            } else {
                statusBadge.innerHTML = '<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">Draft</span>';
            }

            loading.classList.add('hidden');
            dataContainer.classList.remove('hidden');
        })
        .catch(err => {
            alert('Terjadi kesalahan memuat data.');
            closeDetailModal();
        });
}

function closeDetailModal() {
    document.getElementById('modal-detail-jurnal').classList.add('hidden');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDetailModal();
});
</script>

