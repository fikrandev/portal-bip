<?php
/**
 * View Data Daftar Tunggu (Waiting List) SPMB
 * Portal BIP
 */
$activeGroup = $activeGroup ?? null;
$groups = $groups ?? (class_exists('SpmbModel') ? SpmbModel::getAllGroups() : []);
$autoNomor = $autoNomor ?? (class_exists('SpmbModel') ? SpmbModel::generateNomorPendaftaran('Daftar Tunggu', $activeGroup['tahun_ajaran'] ?? null, 'PAUD') : 'DT-PAUD-001');
$list = $pendaftarList ?? [];
$filters = $filters ?? [];
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Data Siswa Daftar Tunggu</h1>
                <?php if ($activeGroup): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <?= e($activeGroup['nama_group']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-xs sm:text-sm text-slate-500">
                Pendaftaran calon siswa daftar tunggu (waiting list) dengan jalur rekomendasi Yayasan, Dinas, dan Lainnya.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="bukaModalBroadcastDt()"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Broadcast WA</span>
                <span id="badgeCheckedDt" class="hidden px-2 py-0.5 rounded-full text-[10px] bg-white text-emerald-800 font-extrabold shadow-xs">0</span>
            </button>
            <a href="<?= url('kelola-spmb/daftar-tunggu/export?' . http_build_query($filters)) ?>" 
               data-download="true"
               data-no-loading="true"
               download="Data_Daftar_Tunggu_SPMB.csv"
               class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                <span>Export CSV</span>
            </a>
            <button type="button" onclick="openModalTambahDaftarTunggu()" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-white font-semibold text-xs shadow-md shadow-amber-500/20 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Tambah Daftar Tunggu</span>
            </button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-5 rounded-2xl border border-primary-100/70 shadow-sm">
        <form method="GET" action="<?= url('kelola-spmb/daftar-tunggu') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="per_page" value="<?= e($perPageParam ?? '50') ?>">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Siswa / No. Daftar / Rekomendasi</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Ketik nama, no formulir, atau nama rekomendasi..." 
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </div>
            </div>

            <!-- Jenjang -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Jenjang</label>
                <select name="jenjang" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none bg-white">
                    <option value="">Semua Jenjang</option>
                    <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $j): ?>
                        <option value="<?= $j ?>" <?= (($filters['jenjang'] ?? '') === $j) ? 'selected' : '' ?>><?= $j ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Rekomendasi Dropdown Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Rekomendasi</label>
                <select name="kategori_pendaftar" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none bg-white font-medium text-slate-700">
                    <option value="">Semua Rekomendasi</option>
                    <option value="Yayasan" <?= (($filters['kategori_pendaftar'] ?? '') === 'Yayasan') ? 'selected' : '' ?>>🏢 Yayasan</option>
                    <option value="Dinas" <?= (($filters['kategori_pendaftar'] ?? '') === 'Dinas') ? 'selected' : '' ?>>🏛️ Dinas</option>
                    <option value="Lainnya" <?= (($filters['kategori_pendaftar'] ?? '') === 'Lainnya') ? 'selected' : '' ?>>📌 Lainnya</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition-colors">
                    Filter
                </button>
                <a href="<?= url('kelola-spmb/daftar-tunggu') ?>" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-primary-100/70 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Menampilkan <strong><?= count($list) ?></strong> dari <strong><?= $totalRows ?? count($list) ?></strong> pendaftar daftar tunggu</span>
            <span class="text-amber-600 font-semibold">Tahun Aktif: <?= e($activeGroup['nama_group'] ?? '-') ?></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/75 text-slate-500 border-b border-slate-100 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-3 w-10 text-center">
                            <input type="checkbox" id="checkAllDt" onchange="toggleSelectAllDt(this)" class="rounded text-amber-600 focus:ring-amber-500 cursor-pointer">
                        </th>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">No. Pendaftaran</th>
                        <th class="py-3 px-4">Calon Siswa</th>
                        <th class="py-3 px-4">Jenjang</th>
                        <th class="py-3 px-4">Rekomendasi</th>
                        <th class="py-3 px-4">Kontak Ortu</th>
                        <th class="py-3 px-4">Tgl Daftar</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    </div>
                                    <p class="font-bold text-slate-700">Belum ada data Daftar Tunggu</p>
                                    <p class="text-[11px] text-slate-400">Gunakan tombol "Tambah Daftar Tunggu" untuk menambahkan calon siswa rekomendasi.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = ($currentPage - 1) * $perPage + 1;
                        foreach ($list as $item): 
                            $targetPhone = !empty($item['no_wa']) ? $item['no_wa'] :
                                          (!empty($item['no_hp']) ? $item['no_hp'] :
                                          (!empty($item['no_hp_ayah']) ? $item['no_hp_ayah'] :
                                          (!empty($item['no_hp_ibu']) ? $item['no_hp_ibu'] : '')));
                            $rekomStr = $item['kategori_pendaftar'] . (!empty($item['nama_rekomendasi']) ? ' (' . $item['nama_rekomendasi'] . ')' : '');
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors dt-table-row" id="dt-row-<?= (int)$item['id'] ?>">
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox" class="dt-row-check rounded text-amber-600 focus:ring-amber-500 cursor-pointer"
                                           value="<?= (int)$item['id'] ?>"
                                           data-id="<?= (int)$item['id'] ?>"
                                           data-nama="<?= e($item['nama_lengkap']) ?>"
                                           data-phone="<?= e($targetPhone) ?>"
                                           data-jenjang="<?= e($item['jenjang']) ?>"
                                           data-nodaf="<?= e($item['nomor_pendaftaran']) ?>"
                                           data-rekom="<?= e($rekomStr) ?>"
                                           onchange="updateSelectedDtCount()">
                                </td>
                                <td class="py-3 px-4 text-center font-medium text-slate-400"><?= $no++ ?></td>
                                <td class="py-3 px-4">
                                    <a href="<?= url('kelola-spmb/daftar-tunggu/detail/' . $item['id']) ?>" class="font-mono font-bold text-amber-700 hover:underline">
                                        <?= e($item['nomor_pendaftaran']) ?>
                                    </a>
                                    <div class="text-[10px] text-slate-400"><?= e($item['gelombang'] ?? 'Gelombang 1') ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800"><?= e($item['nama_lengkap']) ?></div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= ($item['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan' ?> &bull; Umur: <?= e($item['umur'] ?: '-') ?>
                                    </div>
                                    <?php if (!empty($item['asal_sekolah'])): ?>
                                        <div class="text-[10px] text-slate-500 italic mt-0.5">Asal: <?= e($item['asal_sekolah']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                        <?= e($item['jenjang']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <?php 
                                    $kat = $item['kategori_pendaftar'] ?: 'Yayasan';
                                    $namaRekom = $item['nama_rekomendasi'] ?? '';
                                    $badgeStyle = match($kat) {
                                        'Yayasan' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        'Dinas'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        default   => 'bg-amber-100 text-amber-800 border-amber-200',
                                    };
                                    $icon = match($kat) {
                                        'Yayasan' => '🏢',
                                        'Dinas'   => '🏛️',
                                        default   => '📌',
                                    };
                                    ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold border shadow-sm <?= $badgeStyle ?>">
                                        <span><?= $icon ?></span>
                                        <span><?= e($kat) ?></span>
                                    </span>
                                    <?php if (!empty($namaRekom)): ?>
                                        <div class="text-[11px] font-semibold text-slate-700 mt-1 max-w-[200px] truncate" title="<?= e($namaRekom) ?>">
                                            <?= e($namaRekom) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-slate-700"><?= e($item['nama_ayah'] ?: $item['nama_ibu'] ?: '-') ?></div>
                                    <div class="text-[11px] text-slate-500"><?= e($targetPhone ?: '-') ?></div>
                                    <?php if (!empty($item['wa_dt_sent_at'])): ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 mt-1" title="Terkirim: <?= date('d M Y H:i', strtotime($item['wa_dt_sent_at'])) ?>">
                                            <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                            <span>WA Terkirim</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($item['tanggal_daftar'])) ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php
                                    $stKelulusan = $item['status_kelulusan'] ?? 'Cadangan';
                                    $stStyle = match($stKelulusan) {
                                        'Lulus / Diterima' => 'bg-emerald-100 text-emerald-800',
                                        'Lolos Berkas'     => 'bg-blue-100 text-blue-800',
                                        'Cadangan'         => 'bg-amber-100 text-amber-800 font-bold',
                                        'Tidak Lulus'      => 'bg-rose-100 text-rose-800',
                                        default            => 'bg-slate-100 text-slate-600'
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold <?= $stStyle ?>">
                                        <?= e($stKelulusan) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" 
                                                onclick="bukaModalBroadcastDtSingle(<?= (int)$item['id'] ?>, '<?= e(addslashes($item['nama_lengkap'])) ?>', '<?= e($targetPhone) ?>', '<?= e($item['jenjang']) ?>', '<?= e($item['nomor_pendaftaran']) ?>', '<?= e(addslashes($rekomStr)) ?>')" 
                                                title="Kirim Pesan WhatsApp Japri"
                                                class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        </button>
                                        <a href="<?= url('kelola-spmb/daftar-tunggu/detail/' . $item['id']) ?>" 
                                            title="Detail Siswa"
                                            class="p-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                        <a href="<?= url('kelola-spmb/daftar-tunggu/edit/' . $item['id']) ?>" 
                                            title="Edit Data"
                                            class="p-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </a>
                                        <a href="<?= url('kelola-spmb/daftar-tunggu/cetak/' . $item['id']) ?>" target="_blank"
                                            title="Cetak Bukti Pendaftaran"
                                            class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.656h10.5Z" /></svg>
                                        </a>
                                        <button type="button" onclick="bukaModalPromosi(<?= $item['id'] ?>)" 
                                                title="Promosikan ke Siswa Baru"
                                                class="p-1.5 rounded-lg bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition-colors font-bold">
                                            <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                        </button>
                                        <button onclick="confirmDelete(<?= $item['id'] ?>, '<?= e(addslashes($item['nama_lengkap'])) ?>')" 
                                                title="Hapus"
                                                class="p-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php 
        $baseRoute = 'kelola-spmb/daftar-tunggu';
        include BASE_PATH . '/modules/kelola-spmb/views/partials/pagination.php'; 
        ?>
    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- FLOATING ACTION BAR SAAT SISWA DICENTANG                                   -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="floatingSelectionDtBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900/95 backdrop-blur-md text-white px-5 py-3 rounded-2xl shadow-2xl border border-slate-700/80 flex items-center gap-4 transition-all">
    <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs font-semibold"><span id="floatingSelectedCount" class="font-extrabold text-emerald-300">0</span> Calon Siswa Dicentang</span>
    </div>
    <div class="h-4 w-px bg-slate-700"></div>
    <button type="button" onclick="bukaModalBroadcastDt()" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1.5 cursor-pointer active:scale-95">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        <span>Kirim Broadcast WA Terpilih</span>
    </button>
    <button type="button" onclick="resetCheckedDt()" class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium text-xs transition-colors cursor-pointer">
        Batal
    </button>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL CHAT CUSTOM & BROADCAST WHATSAPP SISWA DAFTAR TUNGGU                -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalBroadcastDt" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4 bg-slate-950/65 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-auto max-h-[94vh] flex flex-col animate-slide-in">
        
        <!-- Header Modal -->
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white flex items-center justify-between shrink-0 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                </div>
                <div>
                    <h3 id="dtModalHeaderTitle" class="text-base font-extrabold tracking-tight">Kustom Chat WhatsApp Calon Siswa</h3>
                    <p id="dtModalHeaderSubtitle" class="text-[11px] text-emerald-100">Sesuaikan pesan kustom dengan tag {nama} sebelum dikirim</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalBroadcastDt()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center transition-colors text-white cursor-pointer" title="Tutup">
                ✕
            </button>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- PANEL 1: EDIT PESAN CHAT KUSTOM (TAMPIL PERTAMA KALI)              -->
        <!-- ═════════════════════════════════════════════════════════════════ -->
        <div id="dtPanelChatCustom" class="flex flex-col flex-grow overflow-hidden">
            <!-- Scrollable Content -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs text-slate-700 flex-grow">
                
                <!-- 1. Sasaran Penerima Info & Chips -->
                <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Sasaran Penerima Pesan:
                        </span>
                        <span id="dtBroadcastQueueBadge" class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-600 text-white shadow-xs">
                            0 Calon Siswa Terpilih
                        </span>
                    </div>
                    <div id="dtQueueChipsContainer" class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto p-1 custom-scrollbar">
                        <!-- Dynamic chips of recipients -->
                    </div>
                </div>

                <!-- 2. Pesan Template Editor -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block font-bold text-slate-800 text-xs">
                            Tulis Pesan WhatsApp Kustom <span class="text-rose-500">*</span>
                        </label>
                        <button type="button" onclick="resetDefaultTemplateDt()" class="text-[11px] font-semibold text-emerald-700 hover:underline cursor-pointer flex items-center gap-1">
                            <span>↺</span>
                            <span>Reset Template Standar</span>
                        </button>
                    </div>

                    <!-- Helper Tags Toolbar -->
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Sisipkan Tag Dinamis (Klik untuk Menambahkan):</div>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="insertTagDt('{nama}')" class="px-2.5 py-1 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-extrabold text-[11px] border border-emerald-300 transition-all cursor-pointer shadow-xs active:scale-95" title="Nama Lengkap Calon Siswa">
                                + {nama}
                            </button>
                            <button type="button" onclick="insertTagDt('{no_daftar}')" class="px-2 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-bold text-[11px] border border-slate-200 transition-all cursor-pointer shadow-xs active:scale-95" title="Nomor Pendaftaran">
                                + {no_daftar}
                            </button>
                            <button type="button" onclick="insertTagDt('{jenjang}')" class="px-2 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-bold text-[11px] border border-slate-200 transition-all cursor-pointer shadow-xs active:scale-95" title="Jenjang (PAUD/SD/SMP/SMA)">
                                + {jenjang}
                            </button>
                            <button type="button" onclick="insertTagDt('{rekomendasi}')" class="px-2 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-bold text-[11px] border border-slate-200 transition-all cursor-pointer shadow-xs active:scale-95" title="Jalur Rekomendasi">
                                + {rekomendasi}
                            </button>
                            <button type="button" onclick="insertTagDt('{tahun_ajaran}')" class="px-2 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-bold text-[11px] border border-slate-200 transition-all cursor-pointer shadow-xs active:scale-95" title="Tahun Ajaran Aktif">
                                + {tahun_ajaran}
                            </button>
                            <button type="button" onclick="insertTagDt('{nama_ortu}')" class="px-2 py-1 rounded-lg bg-white hover:bg-slate-100 text-slate-700 font-bold text-[11px] border border-slate-200 transition-all cursor-pointer shadow-xs active:scale-95" title="Nama Ayah/Ibu">
                                + {nama_ortu}
                            </button>
                        </div>
                    </div>

                    <textarea id="dtWaCustomTemplate" rows="6" 
                              oninput="updatePreviewDtMessage()"
                              class="w-full p-3.5 rounded-2xl border border-slate-200 text-xs font-mono text-slate-800 leading-relaxed outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all bg-white shadow-inner"
                              placeholder="Ketik template pesan WhatsApp di sini... Gunakan tag {nama} agar otomatis menyapa nama siswa."></textarea>
                </div>

                <!-- 3. Authentic WhatsApp Live Preview Card -->
                <div class="space-y-1.5">
                    <span class="text-[11px] font-bold text-slate-500">Pratinjau Tampilan Pesan di WhatsApp:</span>
                    <div class="rounded-2xl border border-slate-200 overflow-hidden shadow-xs bg-[#efeae2]">
                        <!-- WA Header Simulation -->
                        <div class="px-4 py-2 bg-[#075e54] text-white flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-emerald-700 flex items-center justify-center text-white font-bold text-xs shadow-xs">
                                    👤
                                </div>
                                <div>
                                    <div class="text-xs font-bold leading-tight flex items-center gap-1.5">
                                        <span id="dtWaPreviewRecipientName">Orang Tua Calon Siswa</span>
                                    </div>
                                    <div class="text-[9.5px] text-emerald-200">Online</div>
                                </div>
                            </div>
                            <span class="text-[9.5px] text-emerald-200 font-mono">WhatsApp</span>
                        </div>

                        <!-- WA Bubble Chat Area -->
                        <div class="p-3.5 max-h-44 overflow-y-auto custom-scrollbar flex flex-col justify-end">
                            <div class="max-w-[92%] self-start bg-white rounded-2xl rounded-tl-xs p-3 shadow-xs text-slate-800 text-xs leading-relaxed relative border border-slate-200/60">
                                <div id="dtPreviewContent" class="whitespace-pre-wrap font-sans text-xs text-slate-800">
                                    (Pratinjau pesan akan muncul di sini)
                                </div>
                                <div class="flex items-center justify-end gap-1 mt-1.5 text-[9px] text-slate-400">
                                    <span><?= date('H:i') ?></span>
                                    <span class="text-sky-500 font-bold">✓✓</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Keamanan Delay / Jeda Pengiriman -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <label class="block font-bold text-slate-800 text-xs">Jeda Antar Pesan (Anti-Blokir WA)</label>
                        <p class="text-[10.5px] text-slate-400">Jeda waktu berhenti sejenak sebelum mengirim pesan ke nomor selanjutnya</p>
                    </div>
                    <select id="dtBroadcastDelay" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white font-bold text-xs text-slate-700 outline-none">
                        <option value="1000">1 Detik (Cepat)</option>
                        <option value="2000">2 Detik</option>
                        <option value="3000" selected>3 Detik (Rekomendasi Aman)</option>
                        <option value="5000">5 Detik (Sangat Aman)</option>
                    </select>
                </div>

            </div>

            <!-- Panel 1 Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0">
                <button type="button" onclick="tutupModalBroadcastDt()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btnKirimSekarangDt" onclick="mulaiBroadcastDt()" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span id="txtBtnKirimSekarang">Kirim WhatsApp Sekarang ➔</span>
                </button>
            </div>
        </div>

        <!-- ═════════════════════════════════════════════════════════════════ -->
        <!-- PANEL 2: PROGRESS & REALTIME LOG PENGIRIMAN (BERJALAN DI BG)        -->
        <!-- ═════════════════════════════════════════════════════════════════ -->
        <div id="dtPanelProgress" class="hidden flex flex-col flex-grow overflow-hidden">
            <!-- Scrollable Content -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs text-slate-700 flex-grow">
                
                <!-- Real-time Progress Box -->
                <div class="space-y-3 p-4 rounded-2xl bg-emerald-50/80 border border-emerald-300 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-ping"></span>
                            <span id="dtProgressStatusText" class="font-bold text-emerald-950 text-xs">Memulai antrean pengiriman...</span>
                        </div>
                        <span id="dtProgressPercentage" class="font-mono font-black text-emerald-700 text-sm">0%</span>
                    </div>
                    <div class="w-full bg-slate-200/80 rounded-full h-3 overflow-hidden shadow-inner p-0.5">
                        <div id="dtProgressBar" class="bg-gradient-to-r from-emerald-500 via-teal-500 to-sky-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div class="flex items-center gap-3 pt-1 text-[11px] font-bold">
                        <span class="text-slate-600">Total: <strong id="dtCounterTotal">0</strong></span>
                        <span class="text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-lg border border-emerald-300">✓ Berhasil: <strong id="dtCounterSuccess">0</strong></span>
                        <span class="text-rose-700 bg-rose-100 px-2.5 py-0.5 rounded-lg border border-rose-300">✕ Gagal: <strong id="dtCounterFail">0</strong></span>
                    </div>
                </div>

                <!-- Real-time Results List -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                            <span>📋</span>
                            <span>Riwayat Status Pengiriman WhatsApp:</span>
                        </h4>
                        <button type="button" id="btnRetryAllFailedDt" onclick="retryAllFailedDt()" class="hidden px-2.5 py-1 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-[11px] shadow-sm transition-all cursor-pointer">
                            🔄 Kirim Ulang Semua yang Gagal (<span id="dtFailedTotalCount">0</span>)
                        </button>
                    </div>
                    
                    <div id="dtFailSummaryBanner" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800 flex items-center justify-between">
                        <span>⚠️ Ada nomor yang belum berhasil terkirim. Anda dapat mengklik tombol "Kirim Ulang" di bawah.</span>
                    </div>

                    <div class="max-h-72 overflow-y-auto rounded-2xl border border-slate-200 divide-y divide-slate-100 bg-white shadow-inner custom-scrollbar" id="dtResultsList">
                        <!-- Dynamically populated rows -->
                    </div>
                </div>

            </div>

            <!-- Panel 2 Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between shrink-0">
                <button type="button" id="btnKembaliKeChatDt" onclick="kembaliKeChatDt()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer flex items-center gap-1.5">
                    <span>✏️</span>
                    <span>Kembali ke Edit Pesan</span>
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" id="btnHentikanBroadcastDt" onclick="hentikanBroadcastDt()" class="hidden px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md transition-colors cursor-pointer">
                        ⏸ Jeda / Hentikan
                    </button>
                    <button type="button" id="btnSelesaiTutupDt" onclick="tutupModalBroadcastDt()" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition-colors cursor-pointer">
                        Selesai & Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL TAMBAH DAFTAR TUNGGU (CLEAN 1-COLUMN QUICK REGISTRATION)           -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalTambahDaftarTunggu" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-auto max-h-[92vh] flex flex-col">
        
        <!-- Modal Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 text-white flex items-center justify-between shrink-0 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center font-bold text-lg shadow-inner">
                    📋
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-extrabold tracking-tight">Formulir Daftar Tunggu</h3>
                        <?php if ($activeGroup): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-200 text-amber-950 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-950 animate-pulse"></span>
                                <?= e($activeGroup['nama_group']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-[11px] text-amber-100 mt-0.5">Input data calon siswa rekomendasi & waiting list</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahDaftarTunggu()" class="p-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form Body (1 Column Clean Flow) -->
        <form id="formTambahDaftarTunggu" action="<?= url('kelola-spmb/daftar-tunggu/store') ?>" method="POST" enctype="multipart/form-data" data-no-loading="true" data-confirm="true" onsubmit="event.preventDefault(); submitFormTambahDaftarTunggu(event); return false;" class="flex flex-col flex-1 overflow-hidden">
            <?= CSRF::field() ?>
            <input type="hidden" name="is_ajax" value="1">
            <input type="hidden" name="jenis_pendaftaran" value="Daftar Tunggu">
            <input type="hidden" name="group_id" value="<?= $activeGroup['id'] ?? 1 ?>">

            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4 text-xs text-slate-700">
                
                <!-- 1. Jenjang -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Jenjang Sekolah Tujuan <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenjang" id="modal_jenjang" onchange="onJenjangChangeDaftarTunggu()" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                        <option value="PAUD" selected>PAUD</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                    </select>
                </div>

                <!-- 2. Rekomendasi (Dropdown: Yayasan, Dinas, Lainnya) -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Rekomendasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="kategori_pendaftar" id="modal_kategori_pendaftar" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                        <option value="Yayasan" selected>🏢 Yayasan</option>
                        <option value="Dinas">🏛️ Dinas</option>
                        <option value="Lainnya">📌 Lainnya</option>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Pilih sumber rekomendasi pendaftar daftar tunggu.</p>
                </div>

                <!-- 3. Nama Rekomendasi (Input text) -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Nama Rekomendasi / Detail Rekomendasi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_rekomendasi" id="modal_nama_rekomendasi" required 
                           placeholder="Contoh: Bpk. H. Ahmad (Yayasan) / Dinas Pendidikan Kota Palu" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                    <p class="text-[10px] text-slate-400 mt-1">Tuliskan nama tokoh, instansi, atau pejabat pemberi rekomendasi.</p>
                </div>

                <!-- 4. No Formulir Auto (DT-Unit-001) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800">
                            No. Formulir <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded-md">
                            Otomatis (Format: DT-Unit-001)
                        </span>
                    </div>
                    <div class="relative">
                        <input type="text" name="nomor_pendaftaran" id="modal_nomor_pendaftaran" value="<?= e($autoNomor) ?>" required 
                               class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-200 bg-amber-50/60 font-mono font-bold text-amber-900 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                        <button type="button" onclick="onJenjangChangeDaftarTunggu()" title="Generate Ulang Nomor" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-amber-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Format otomatis mengikuti jenjang (misal DT-PAUD-001, DT-SD-001, DT-SMP-001, DT-SMA-001).</p>
                </div>

                <!-- 5. Gelombang -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800">
                            Gelombang Pendaftaran
                        </label>
                        <span class="text-[10px] text-slate-400">Dari Pengaturan Group</span>
                    </div>
                    <input type="text" name="gelombang" id="modal_gelombang" 
                           value="<?= e($activeGroup['gelombang_default'] ?? 'Gelombang 1') ?>" 
                           placeholder="Contoh: Gelombang 1"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-800 focus:bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                </div>

                <!-- 6. Nama Lengkap -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Nama Lengkap Siswa <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_lengkap" id="modal_nama_lengkap" required 
                           placeholder="Nama lengkap calon siswa sesuai akta..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                </div>

                <!-- 7. Jenis Kelamin -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Jenis Kelamin <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis_kelamin" id="modal_jenis_kelamin" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-800 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                        <option value="L" selected>Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <!-- 8. Tempat Lahir -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Tempat Lahir <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="tempat_lahir" id="modal_tempat_lahir" required placeholder="Contoh: Palu" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                </div>

                <!-- 9. Tanggal Lahir -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Tanggal Lahir <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_lahir" id="modal_tanggal_lahir" required onchange="hitungUmurOtomatisDaftarTunggu(this.value)" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                </div>

                <!-- 10. Umur (Auto) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-bold text-slate-800">
                            Umur Siswa <span class="text-[10px] font-normal text-slate-500">(Per <?= !empty($activeGroup['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($activeGroup['tanggal_patokan_umur']) : '1 Juli 2027' ?>)</span>
                        </label>
                        <span id="badge_umur" class="hidden text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">
                            Otomatis Dihitung
                        </span>
                    </div>
                    <div class="relative">
                        <input type="text" name="umur" id="modal_umur" readonly 
                               placeholder="Otomatis terisi saat tanggal lahir dipilih" 
                               class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 outline-none text-xs cursor-default">
                        <span class="absolute left-3 top-2.5 text-slate-400">🎂</span>
                    </div>
                </div>

                <!-- 11. Asal Sekolah -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Asal Sekolah <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="asal_sekolah" id="modal_asal_sekolah" required placeholder="Nama TK / PAUD / Sekolah sebelumnya..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                </div>

                <!-- 12. Nama Ayah -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Nama Ayah <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_ayah" id="modal_nama_ayah" required placeholder="Nama lengkap ayah kandung..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                </div>

                <!-- 13. Nama Ibu -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        Nama Ibu <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_ibu" id="modal_nama_ibu" required placeholder="Nama lengkap ibu kandung..." 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                </div>

                <!-- 14. No WA -->
                <div>
                    <label class="block font-bold text-slate-800 mb-1">
                        No. WhatsApp / HP Orang Tua <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="no_hp" id="modal_no_hp" required placeholder="08xxxxxxxxxx" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-mono focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs">
                </div>

                <!-- 15. Alamat -->
                <div class="sm:col-span-2">
                    <label class="block font-bold text-slate-800 mb-1">
                        Alamat Tempat Tinggal <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="alamat" id="modal_alamat" required rows="2" placeholder="Nama jalan, nomor rumah, kelurahan, kota..." 
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none text-xs"></textarea>
                </div>

                <!-- 16. Informasi Biaya Pendaftaran Awal (Gratis / Rp 0) -->
                <div class="sm:col-span-2 p-3.5 bg-emerald-50/80 border border-emerald-200/90 rounded-2xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                            ✓
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-emerald-950">Biaya Pendaftaran Awal: Bebas Biaya</span>
                            <span class="block text-[11px] text-emerald-700">Tahap awal pendaftaran daftar tunggu tidak dipungut biaya (Gratis).</span>
                        </div>
                    </div>
                    <span class="px-3 py-1 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm shrink-0">
                        Rp 0 (Gratis)
                    </span>
                    <input type="hidden" name="biaya_pendaftaran" id="modal_biaya" value="0">
                    <input type="hidden" name="status_pembayaran" value="Gratis">
                    <input type="hidden" name="metode_pembayaran" value="Gratis">
                    <input type="hidden" name="status_kelulusan" value="Cadangan">
                </div>

            </div>

            <!-- Modal Footer Actions -->
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0 gap-3">
                <span class="text-[11px] text-slate-500 italic">
                    * Kolom wajib diisi
                </span>
                <div class="flex items-center gap-2 ml-auto">
                    <button type="button" onclick="closeModalTambahDaftarTunggu()" 
                            class="px-4 py-2.5 rounded-xl bg-slate-200/80 hover:bg-slate-300/80 text-slate-700 font-bold text-xs transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="btnSubmitModalDaftarTunggu" onclick="submitFormTambahDaftarTunggu(event)" 
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>Simpan & Daftarkan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Promosi ke Siswa Baru (Detail Lengkap & Konfirmasi) -->
<div id="modalPromosiDT" class="fixed inset-0 z-50 hidden flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden my-auto max-h-[92vh] flex flex-col" onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white flex items-center justify-between shrink-0 shadow-md">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center font-bold text-lg shadow-inner">
                    🚀
                </div>
                <div>
                    <h3 class="text-base font-extrabold tracking-tight">Detail Calon Siswa & Promosi</h3>
                    <p class="text-[11px] text-emerald-100 mt-0.5">Tinjau kelengkapan data sebelum dipromosikan ke Siswa Baru</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalPromosi()" class="p-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Modal Form Body -->
        <form id="formPromosiDT" method="POST" class="flex flex-col flex-1 overflow-hidden">
            <?= CSRF::field() ?>

            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4 text-xs text-slate-700">
                
                <!-- Status & Nomor Banner -->
                <div class="p-3.5 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200/80 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">No. Daftar Tunggu Saat Ini:</span>
                        <span class="font-mono font-bold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-lg border border-amber-200" id="promosiNomor">-</span>
                    </div>
                    <div class="flex items-center justify-between pt-1.5 border-t border-emerald-200/60">
                        <span class="text-slate-500 font-medium">No. Siswa Baru Resmi:</span>
                        <span class="font-mono font-bold text-emerald-800 bg-emerald-100/90 px-2.5 py-0.5 rounded-lg border border-emerald-300">
                            ✨ Otomatis Digenerate Resmi
                        </span>
                    </div>
                </div>

                <!-- Bagian 1: Informasi Pendaftaran & Rekomendasi -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/90 space-y-2.5 shadow-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">1. Data Pendaftaran & Rekomendasi</span>
                        <span class="px-2.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]" id="promosiJenjang">-</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Rekomendasi</span>
                            <span class="font-bold text-slate-800" id="promosiRekomendasi">-</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Nama Rekomendasi</span>
                            <span class="font-bold text-slate-800" id="promosiNamaRekomendasi">-</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Gelombang</span>
                            <span class="font-semibold text-slate-700" id="promosiGelombang">-</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Tanggal Mendaftar</span>
                            <span class="font-semibold text-slate-700" id="promosiTanggalDaftar">-</span>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Identitas Calon Siswa -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/90 space-y-2.5 shadow-xs">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block border-b border-slate-100 pb-2">2. Identitas Calon Siswa</span>
                    <div class="space-y-2 pt-1 text-xs">
                        <div>
                            <span class="text-slate-400 text-[11px] block">Nama Lengkap Siswa</span>
                            <span class="text-sm font-extrabold text-slate-900" id="promosiNama">-</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 text-[11px] block">Jenis Kelamin</span>
                                <span class="font-semibold text-slate-800" id="promosiJK">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Umur Siswa</span>
                                <span class="font-semibold text-slate-800" id="promosiUmur">-</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 text-[11px] block">Tempat, Tanggal Lahir</span>
                                <span class="font-semibold text-slate-800" id="promosiTTL">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Asal Sekolah</span>
                                <span class="font-semibold text-slate-800" id="promosiAsalSekolah">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: Orang Tua & Kontak -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/90 space-y-2.5 shadow-xs">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 block border-b border-slate-100 pb-2">3. Data Orang Tua & Kontak</span>
                    <div class="space-y-2 pt-1 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-400 text-[11px] block">Nama Ayah</span>
                                <span class="font-semibold text-slate-800" id="promosiNamaAyah">-</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[11px] block">Nama Ibu</span>
                                <span class="font-semibold text-slate-800" id="promosiNamaIbu">-</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">No. WhatsApp / HP Orang Tua</span>
                            <span class="font-mono font-bold text-slate-900" id="promosiKontak">-</span>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px] block">Alamat Tinggal</span>
                            <span class="font-normal text-slate-700" id="promosiAlamat">-</span>
                        </div>
                    </div>
                </div>

                <!-- Catatan Info & Opsi WA -->
                <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-2xl text-[11px] text-amber-900 leading-relaxed">
                    ℹ️ <strong>Catatan:</strong> Setelah menekan tombol <strong>Lanjutkan</strong>, calon siswa akan resmi berstatus <strong>Siswa Baru</strong> dan ditandai badge <em>Eks DT</em>. Selanjutnya orang tua dapat mengisi formulir online biodata lengkap untuk kebutuhan berkas Dapodik/EMIS.
                </div>

                <label class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs cursor-pointer hover:bg-slate-100 transition-colors">
                    <input type="checkbox" name="kirim_wa" value="1" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 mt-0.5">
                    <div class="text-slate-700">
                        <span class="font-bold text-slate-900">Kirim notifikasi WhatsApp ke nomor orang tua</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kirimkan pesan otomatis berisi pemberitahuan dan tautan formulir biodata lengkap ke nomor <span id="promosiKontakNotif" class="font-semibold">-</span>.</p>
                    </div>
                </label>

            </div>

            <!-- Modal Footer Actions -->
            <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0 gap-3">
                <button type="button" onclick="tutupModalPromosi()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold text-xs hover:bg-slate-100 transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <span>Lanjutkan Promosikan ke Siswa Baru</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$listById = [];
foreach ($list as $row) {
    $listById[$row['id']] = [
        'id'                 => (int)$row['id'],
        'nama_lengkap'       => $row['nama_lengkap'] ?? '',
        'nomor_pendaftaran'  => $row['nomor_pendaftaran'] ?? '',
        'jenjang'            => $row['jenjang'] ?? '',
        'kategori_pendaftar' => $row['kategori_pendaftar'] ?? 'Yayasan',
        'nama_rekomendasi'   => $row['nama_rekomendasi'] ?? '-',
        'gelombang'          => $row['gelombang'] ?? 'Gelombang 1',
        'tanggal_daftar'     => !empty($row['tanggal_daftar']) ? date('d F Y', strtotime($row['tanggal_daftar'])) : '-',
        'jenis_kelamin'      => ($row['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan',
        'tempat_lahir'       => $row['tempat_lahir'] ?? '-',
        'tanggal_lahir'      => !empty($row['tanggal_lahir']) ? date('d/m/Y', strtotime($row['tanggal_lahir'])) : '-',
        'umur'               => $row['umur'] ?? '-',
        'asal_sekolah'       => $row['asal_sekolah'] ?? '-',
        'nama_ayah'          => $row['nama_ayah'] ?? '-',
        'nama_ibu'           => $row['nama_ibu'] ?? '-',
        'kontak'             => $row['no_hp'] ?: ($row['no_wa'] ?: ($row['no_hp_ayah'] ?: '-')),
        'alamat'             => $row['alamat'] ?? '-'
    ];
}
?>
<script>
const DATA_PENDAFTAR = <?= json_encode($listById, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function bukaModalPromosi(id) {
    const data = DATA_PENDAFTAR[id];
    if (!data) return;

    document.getElementById('promosiNama').innerText = data.nama_lengkap || '-';
    document.getElementById('promosiJenjang').innerText = data.jenjang || '-';
    document.getElementById('promosiNomor').innerText = data.nomor_pendaftaran || '-';
    document.getElementById('promosiRekomendasi').innerText = data.kategori_pendaftar || 'Yayasan';
    document.getElementById('promosiNamaRekomendasi').innerText = data.nama_rekomendasi || '-';
    document.getElementById('promosiGelombang').innerText = data.gelombang || 'Gelombang 1';
    document.getElementById('promosiTanggalDaftar').innerText = data.tanggal_daftar || '-';
    document.getElementById('promosiJK').innerText = data.jenis_kelamin || '-';
    document.getElementById('promosiUmur').innerText = data.umur || '-';
    document.getElementById('promosiTTL').innerText = (data.tempat_lahir || '-') + ', ' + (data.tanggal_lahir || '-');
    document.getElementById('promosiAsalSekolah').innerText = data.asal_sekolah || '-';
    document.getElementById('promosiNamaAyah').innerText = data.nama_ayah || '-';
    document.getElementById('promosiNamaIbu').innerText = data.nama_ibu || '-';
    document.getElementById('promosiKontak').innerText = data.kontak || '-';
    document.getElementById('promosiKontakNotif').innerText = data.kontak || '-';
    document.getElementById('promosiAlamat').innerText = data.alamat || '-';

    document.getElementById('formPromosiDT').action = '<?= url('kelola-spmb/daftar-tunggu/promosikan/') ?>' + id;
    
    const modal = document.getElementById('modalPromosiDT');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function tutupModalPromosi() {
    const modal = document.getElementById('modalPromosiDT');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}
// Tanggal patokan umur dari group aktif (standar 1 Juli 2027)
const TANGGAL_PATOKAN_UMUR = <?= json_encode($activeGroup['tanggal_patokan_umur'] ?? '2027-07-01') ?>;

function openModalTambahDaftarTunggu() {
    const modal = document.getElementById('modalTambahDaftarTunggu');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Refresh nomor formulir otomatis saat modal dibuka agar selalu nomor terbaru
        if (typeof onJenjangChangeDaftarTunggu === 'function') {
            onJenjangChangeDaftarTunggu();
        }

        setTimeout(() => {
            document.getElementById('modal_nama_lengkap')?.focus();
        }, 150);
    }
}

function closeModalTambahDaftarTunggu() {
    const modal = document.getElementById('modalTambahDaftarTunggu');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

// Tutup modal jika user tekan ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModalTambahDaftarTunggu();
    }
});

// Update otomatis nomor pendaftaran saat jenjang diganti
function onJenjangChangeDaftarTunggu() {
    const jenjang = document.getElementById('modal_jenjang').value;
    const noInput = document.getElementById('modal_nomor_pendaftaran');
    if (!noInput) return;

    noInput.classList.add('opacity-50');

    fetch('<?= url('kelola-spmb/daftar-tunggu/get-nomor') ?>?jenjang=' + encodeURIComponent(jenjang))
        .then(res => res.json())
        .then(data => {
            if (data && data.success && data.nomor) {
                noInput.value = data.nomor;
            }
        })
        .catch(err => {
            console.warn('Gagal ambil nomor otomatis:', err);
        })
        .finally(() => {
            noInput.classList.remove('opacity-50');
        });
}

// Hitung Umur Calon Siswa Berdasarkan Tanggal Patokan Group
function hitungUmurOtomatisDaftarTunggu(tglLahirStr) {
    const inputUmur = document.getElementById('modal_umur');
    const badge = document.getElementById('badge_umur');
    if (!tglLahirStr || !inputUmur) return;

    try {
        const dLahir = new Date(tglLahirStr);
        const dPatokan = new Date(TANGGAL_PATOKAN_UMUR);

        if (isNaN(dLahir.getTime())) {
            inputUmur.value = '';
            if (badge) badge.classList.add('hidden');
            return;
        }

        if (dLahir > dPatokan) {
            inputUmur.value = 'Tanggal belum tiba';
            if (badge) badge.classList.add('hidden');
            return;
        }

        let years = dPatokan.getFullYear() - dLahir.getFullYear();
        let months = dPatokan.getMonth() - dLahir.getMonth();
        let days = dPatokan.getDate() - dLahir.getDate();

        if (days < 0) {
            months--;
        }

        if (months < 0) {
            years--;
            months += 12;
        }

        let hasil = years + ' Tahun';
        if (months > 0) {
            hasil += ' ' + months + ' Bulan';
        }

        inputUmur.value = hasil;
        if (badge) badge.classList.remove('hidden');
    } catch (e) {
        inputUmur.value = '';
    }
}

// Submit Form Tambah Daftar Tunggu dengan Validasi Lengkap & Konfirmasi SweetAlert2
function submitFormTambahDaftarTunggu(event) {
    if (event) event.preventDefault();

    const form = document.getElementById('formTambahDaftarTunggu');
    if (!form) return;

    // 1. Validasi Nama Lengkap
    const namaLengkap = (document.getElementById('modal_nama_lengkap')?.value || '').trim();
    if (!namaLengkap) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Siswa Wajib Diisi',
            text: 'Silakan masukkan nama lengkap calon siswa daftar tunggu.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_nama_lengkap')?.focus();
        return;
    }

    // 2. Validasi Nama Rekomendasi
    const namaRekomendasi = (document.getElementById('modal_nama_rekomendasi')?.value || '').trim();
    if (!namaRekomendasi) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Rekomendasi Wajib Diisi',
            text: 'Silakan tuliskan nama tokoh, pengurus yayasan, atau instansi yang memberikan rekomendasi.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_nama_rekomendasi')?.focus();
        return;
    }

    // 3. Validasi Tempat Lahir
    const tempatLahir = (document.getElementById('modal_tempat_lahir')?.value || '').trim();
    if (!tempatLahir) {
        Swal.fire({
            icon: 'warning',
            title: 'Tempat Lahir Wajib Diisi',
            text: 'Silakan masukkan tempat lahir calon siswa.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_tempat_lahir')?.focus();
        return;
    }

    // 4. Validasi Tanggal Lahir
    const tglLahir = document.getElementById('modal_tanggal_lahir')?.value || '';
    if (!tglLahir) {
        Swal.fire({
            icon: 'warning',
            title: 'Tanggal Lahir Wajib Diisi',
            text: 'Silakan pilih tanggal lahir calon siswa.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_tanggal_lahir')?.focus();
        return;
    }

    // 5. Validasi Asal Sekolah
    const asalSekolah = (document.getElementById('modal_asal_sekolah')?.value || '').trim();
    if (!asalSekolah) {
        Swal.fire({
            icon: 'warning',
            title: 'Asal Sekolah Wajib Diisi',
            text: 'Silakan masukkan nama sekolah / TK sebelumnya.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_asal_sekolah')?.focus();
        return;
    }

    // 6. Validasi Nama Ayah & Ibu
    const namaAyah = (document.getElementById('modal_nama_ayah')?.value || '').trim();
    if (!namaAyah) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Ayah Wajib Diisi',
            text: 'Silakan masukkan nama ayah kandung.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_nama_ayah')?.focus();
        return;
    }

    const namaIbu = (document.getElementById('modal_nama_ibu')?.value || '').trim();
    if (!namaIbu) {
        Swal.fire({
            icon: 'warning',
            title: 'Nama Ibu Wajib Diisi',
            text: 'Silakan masukkan nama ibu kandung.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_nama_ibu')?.focus();
        return;
    }

    // 7. Validasi No WA
    const noHp = (document.getElementById('modal_no_hp')?.value || '').trim();
    if (!noHp) {
        Swal.fire({
            icon: 'warning',
            title: 'No. WhatsApp Wajib Diisi',
            text: 'Silakan masukkan nomor WhatsApp orang tua calon siswa.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_no_hp')?.focus();
        return;
    }

    // 8. Validasi Alamat
    const alamat = (document.getElementById('modal_alamat')?.value || '').trim();
    if (!alamat) {
        Swal.fire({
            icon: 'warning',
            title: 'Alamat Tempat Tinggal Wajib Diisi',
            text: 'Silakan masukkan alamat tempat tinggal calon siswa.',
            confirmButtonColor: '#d97706'
        });
        document.getElementById('modal_alamat')?.focus();
        return;
    }

    // Kumpulkan data untuk ditampilkan di Konfirmasi
    const jenjang = document.getElementById('modal_jenjang')?.value || 'PAUD';
    const katPendaftar = document.getElementById('modal_kategori_pendaftar')?.value || 'Yayasan';
    const noFormulir = document.getElementById('modal_nomor_pendaftaran')?.value || '-';
    const gelombang = document.getElementById('modal_gelombang')?.value || '-';
    const jkVal = document.getElementById('modal_jenis_kelamin')?.value || 'L';
    const jkText = jkVal === 'L' ? 'Laki-laki' : 'Perempuan';
    const umur = document.getElementById('modal_umur')?.value?.trim() || '-';

    let ttlText = (tempatLahir || '') + (tglLahir ? (tempatLahir ? ', ' : '') + tglLahir : '');

    // POPUP KONFIRMASI DATA SEBELUM SIMPAN
    Swal.fire({
        title: 'Konfirmasi Data Calon Siswa Daftar Tunggu',
        html: `
            <div class="text-left text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-1.5 mt-2">
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Jenjang & No. Formulir:</span>
                    <span class="font-bold text-slate-800">${jenjang} &bull; <span class="font-mono text-amber-700 font-bold">${noFormulir}</span></span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Rekomendasi:</span>
                    <span class="font-bold text-amber-800">${katPendaftar}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Nama Rekomendasi:</span>
                    <span class="font-bold text-slate-900">${namaRekomendasi}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Nama Lengkap:</span>
                    <span class="font-bold text-slate-900">${namaLengkap}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Jenis Kelamin:</span>
                    <span class="font-semibold text-slate-800">${jkText}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Tempat, Tgl Lahir:</span>
                    <span class="font-semibold text-slate-800">${ttlText}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Umur Siswa:</span>
                    <span class="font-bold text-emerald-700">${umur}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Asal Sekolah:</span>
                    <span class="font-semibold text-slate-800">${asalSekolah}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Orang Tua:</span>
                    <span class="font-semibold text-slate-800">Ayah: ${namaAyah} | Ibu: ${namaIbu}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">No. WhatsApp:</span>
                    <span class="font-mono font-bold text-slate-800">${noHp}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-200/70">
                    <span class="text-slate-500 font-medium">Alamat:</span>
                    <span class="font-semibold text-slate-800 truncate max-w-[220px]" title="${alamat}">${alamat}</span>
                </div>
                <div class="flex justify-between pt-1 font-bold">
                    <span class="text-slate-700">Biaya Awal:</span>
                    <span class="text-emerald-600 font-bold">Bebas Biaya (Rp 0)</span>
                </div>
            </div>
            <p class="text-[11px] text-slate-500 mt-2.5 text-center">Pastikan data calon siswa daftar tunggu sudah sesuai sebelum disimpan ke database.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d97706',
        cancelButtonColor: '#64748b',
        confirmButtonText: '✓ Ya, Simpan Data',
        cancelButtonText: 'Cek Kembali'
    }).then((result) => {
        if (!result.isConfirmed) return;

        // LOADING STATE
        Swal.fire({
            title: 'Menyimpan Data...',
            text: 'Mohon tunggu sebentar, sistem sedang memproses...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(async (response) => {
            const contentType = response.headers.get('content-type') || '';
            let data = null;
            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                const text = await response.text();
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    if (response.ok) {
                        data = { success: true, message: 'Calon siswa daftar tunggu berhasil didaftarkan!' };
                    } else {
                        data = { success: false, message: 'Terjadi kesalahan sistem (' + response.status + ')' };
                    }
                }
            }
            return { ok: response.ok, data: data };
        })
        .then(({ ok, data }) => {
            if (ok && data && data.success) {
                closeModalTambahDaftarTunggu();

                const cetakUrl = data.cetak_url || ('<?= url('kelola-spmb/daftar-tunggu/cetak/') ?>' + data.id + '?autoprint=1');

                // OTOMATIS BUKA HALAMAN CETAK DI TAB BARU
                window.open(cetakUrl, '_blank');

                // NOTIFIKASI SUKSES
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    html: `
                        <div class="space-y-3">
                            <p class="text-xs text-slate-600">
                                Calon siswa <strong>${namaLengkap}</strong> dengan No. Formulir <strong>${data.nomor || noFormulir}</strong> telah berhasil didaftarkan ke Daftar Tunggu.
                            </p>
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 flex items-center gap-2.5 text-left">
                                <span class="text-2xl">🖨️</span>
                                <div>
                                    <div class="font-bold">Bukti Pendaftaran Daftar Tunggu</div>
                                    <div class="text-[11px] text-amber-700">Tanda bukti pendaftaran telah otomatis dibuka pada tab baru untuk dicetak.</div>
                                </div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '🖨️ Cetak Lagi',
                    cancelButtonText: 'Selesai'
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.open(cetakUrl, '_blank');
                    }
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan Data',
                    text: (data && data.message) ? data.message : 'Terjadi kesalahan saat memproses data pendaftaran.',
                    confirmButtonColor: '#d97706'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Bermasalah',
                text: 'Terjadi gangguan koneksi internet: ' + error.message,
                confirmButtonColor: '#d97706'
            });
        });
    });
}

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Calon Siswa Daftar Tunggu?',
        text: `Data calon siswa "${nama}" akan dihapus permanen dari sistem.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Data',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= url('kelola-spmb/daftar-tunggu/delete/') ?>' + id;
        }
    });
}

function salinLinkPublic(url) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Link Berhasil Disalin',
                text: 'Tautan formulir biodata lengkap berhasil disalin ke clipboard.',
                timer: 1500,
                showConfirmButton: false
            });
        });
    } else {
        const inp = document.createElement('input');
        inp.value = url;
        document.body.appendChild(inp);
        inp.select();
        document.execCommand('copy');
        document.body.removeChild(inp);
        Swal.fire({
            icon: 'success',
            title: 'Link Berhasil Disalin',
            text: 'Tautan formulir biodata lengkap berhasil disalin.',
            timer: 1500,
            showConfirmButton: false
        });
    }
}

// ═════════════════════════════════════════════════════════════════════════
// JAVASCRIPT BROADCAST WHATSAPP SISWA DAFTAR TUNGGU
// ═════════════════════════════════════════════════════════════════════════
let dtBroadcastQueue = [];
let dtFailedItems = [];
let isDtBroadcastRunning = false;
const DT_TAHUN_AJARAN = <?= json_encode($activeGroup['tahun_ajaran'] ?? ($activeGroup['nama_group'] ?? '2026/2027')) ?>;
const DT_CSRF_TOKEN = '<?= CSRF::token() ?>';
const DT_DEFAULT_TEMPLATE = `Assalamu'alaikum Warahmatullahi Wabarakatuh,

Kepada Yth. Ayah/Bunda dari ananda *{nama}*,

Terima kasih telah mendaftarkan ananda dalam Data Daftar Tunggu (Waiting List) {jenjang} SIT Bina Insan Palu Tahun Ajaran {tahun_ajaran}.

Kami menginformasikan bahwa pendaftaran ananda telah tercatat dengan Nomor Pendaftaran: *{no_daftar}* melalui jalur rekomendasi: *{rekomendasi}*.

Informasi lebih lanjut mengenai ketersediaan kuota dan tahapan selanjutnya akan kami sampaikan secara berkala melalui nomor WhatsApp ini.

Apabila ada pertanyaan atau dokumen pendukung yang ingin dilengkapi, silakan membalas pesan ini.

Wassalamu'alaikum Warahmatullahi Wabarakatuh.

_Panitia SPMB SIT Bina Insan Palu_`;

function toggleSelectAllDt(master) {
    document.querySelectorAll('.dt-row-check').forEach(chk => {
        chk.checked = master.checked;
    });
    updateSelectedDtCount();
}

function updateSelectedDtCount() {
    const checked = document.querySelectorAll('.dt-row-check:checked');
    const count = checked.length;
    const badge = document.getElementById('badgeCheckedDt');
    const floatBar = document.getElementById('floatingSelectionDtBar');
    const floatCount = document.getElementById('floatingSelectedCount');
    const checkAll = document.getElementById('checkAllDt');
    const allChecks = document.querySelectorAll('.dt-row-check');

    if (badge) {
        badge.textContent = count;
        badge.classList.toggle('hidden', count === 0);
    }
    if (floatBar) {
        floatBar.classList.toggle('hidden', count === 0);
        floatBar.classList.toggle('flex', count > 0);
    }
    if (floatCount) {
        floatCount.textContent = count;
    }
    if (checkAll && allChecks.length > 0) {
        checkAll.checked = (count === allChecks.length);
        checkAll.indeterminate = (count > 0 && count < allChecks.length);
    }
}

function resetCheckedDt() {
    document.querySelectorAll('.dt-row-check').forEach(c => c.checked = false);
    const checkAll = document.getElementById('checkAllDt');
    if (checkAll) {
        checkAll.checked = false;
        checkAll.indeterminate = false;
    }
    updateSelectedDtCount();
}

function getSelectedDtItems() {
    const checked = document.querySelectorAll('.dt-row-check:checked');
    const items = [];
    checked.forEach(chk => {
        items.push({
            id: parseInt(chk.dataset.id),
            nama: chk.dataset.nama || '',
            phone: chk.dataset.phone || '',
            jenjang: chk.dataset.jenjang || '',
            nodaf: chk.dataset.nodaf || '',
            rekom: chk.dataset.rekom || 'Yayasan'
        });
    });
    return items;
}

function getAllDtItemsOnPage() {
    const checks = document.querySelectorAll('.dt-row-check');
    const items = [];
    checks.forEach(chk => {
        items.push({
            id: parseInt(chk.dataset.id),
            nama: chk.dataset.nama || '',
            phone: chk.dataset.phone || '',
            jenjang: chk.dataset.jenjang || '',
            nodaf: chk.dataset.nodaf || '',
            rekom: chk.dataset.rekom || 'Yayasan'
        });
    });
    return items;
}

function bukaModalBroadcastDt() {
    let items = getSelectedDtItems();
    if (items.length === 0) {
        const allItems = getAllDtItemsOnPage();
        if (allItems.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Kosong',
                text: 'Belum ada calon siswa daftar tunggu pada halaman ini.',
                confirmButtonColor: '#d97706'
            });
            return;
        }

        // Otomatis pilih semua di halaman saat ini
        document.querySelectorAll('.dt-row-check').forEach(c => c.checked = true);
        updateSelectedDtCount();
        items = allItems;
    }

    // TAMPILKAN DULU MODAL CHAT CUSTOMNYA
    siapkanDanTampilkanModalBroadcast(items);
}

function bukaModalBroadcastDtSingle(id, nama, phone, jenjang, nodaf, rekom) {
    const singleItem = [{
        id: parseInt(id),
        nama: nama,
        phone: phone,
        jenjang: jenjang,
        nodaf: nodaf,
        rekom: rekom
    }];
    siapkanDanTampilkanModalBroadcast(singleItem);
}

function siapkanDanTampilkanModalBroadcast(items) {
    dtBroadcastQueue = items;
    dtFailedItems = [];

    // Set badge & chip penerima
    const badgeEl = document.getElementById('dtBroadcastQueueBadge');
    if (badgeEl) {
        if (items.length === 1) {
            badgeEl.textContent = `1 Siswa: ${items[0].nama}`;
        } else {
            badgeEl.textContent = `${items.length} Calon Siswa Terpilih`;
        }
    }

    const btnKirimText = document.getElementById('txtBtnKirimSekarang');
    if (btnKirimText) {
        btnKirimText.textContent = `Kirim WhatsApp Sekarang (${items.length} Siswa) ➔`;
    }

    const chipsContainer = document.getElementById('dtQueueChipsContainer');
    if (chipsContainer) {
        chipsContainer.innerHTML = items.map(it => `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-white border border-emerald-200 text-slate-800 text-[11px] shadow-xs">
                <span class="font-bold text-emerald-900">${escapeHtml(it.nama)}</span>
                <span class="text-slate-400 font-mono text-[10px]">(${escapeHtml(it.phone || 'No HP Kosong')})</span>
                <span class="text-[9.5px] px-1 py-0.2 rounded bg-slate-100 font-semibold text-slate-500">${escapeHtml(it.jenjang)}</span>
            </span>
        `).join('');
    }

    // Set template default jika masih kosong
    const templateEl = document.getElementById('dtWaCustomTemplate');
    if (templateEl && !templateEl.value.trim()) {
        templateEl.value = DT_DEFAULT_TEMPLATE;
    }

    // Update Live Pratinjau WhatsApp
    updatePreviewDtMessage();

    // Reset ke Tampilan Panel 1 (Chat Custom)
    document.getElementById('dtPanelChatCustom').classList.remove('hidden');
    document.getElementById('dtPanelProgress').classList.add('hidden');
    document.getElementById('dtModalHeaderTitle').textContent = 'Kustom Chat WhatsApp Calon Siswa';
    document.getElementById('dtModalHeaderSubtitle').textContent = 'Sesuaikan pesan kustom dengan tag {nama} sebelum dikirim';

    // Buka Modal
    const modal = document.getElementById('modalBroadcastDt');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function tutupModalBroadcastDt() {
    if (isDtBroadcastRunning) {
        if (!confirm('Pengiriman broadcast sedang berjalan di latar belakang. Yakin ingin menutup jendela? Pengiriman akan dihentikan.')) {
            return;
        }
        isDtBroadcastRunning = false;
    }
    const modal = document.getElementById('modalBroadcastDt');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function kembaliKeChatDt() {
    if (isDtBroadcastRunning) {
        if (!confirm('Pengiriman sedang berjalan. Jeda pengiriman dan kembali ke edit pesan?')) {
            return;
        }
        isDtBroadcastRunning = false;
    }

    document.getElementById('dtPanelProgress').classList.add('hidden');
    document.getElementById('dtPanelChatCustom').classList.remove('hidden');
    document.getElementById('dtModalHeaderTitle').textContent = 'Kustom Chat WhatsApp Calon Siswa';
    document.getElementById('dtModalHeaderSubtitle').textContent = 'Sesuaikan pesan kustom dengan tag {nama} sebelum dikirim';

    const textarea = document.getElementById('dtWaCustomTemplate');
    if (textarea) textarea.focus();
}

function insertTagDt(tag) {
    const textarea = document.getElementById('dtWaCustomTemplate');
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.substring(0, start) + tag + text.substring(end);
    textarea.selectionStart = textarea.selectionEnd = start + tag.length;
    textarea.focus();
    updatePreviewDtMessage();
}

function resetDefaultTemplateDt() {
    const textarea = document.getElementById('dtWaCustomTemplate');
    if (textarea) {
        textarea.value = DT_DEFAULT_TEMPLATE;
        updatePreviewDtMessage();
    }
}

function updatePreviewDtMessage() {
    const textarea = document.getElementById('dtWaCustomTemplate');
    const previewEl = document.getElementById('dtPreviewContent');
    const recipientHeader = document.getElementById('dtWaPreviewRecipientName');
    if (!textarea || !previewEl) return;

    const sample = dtBroadcastQueue[0] || {
        nama: 'Ahmad Fauzan',
        no_daftar: 'DT-SD-001',
        jenjang: 'SD',
        rekomendasi: 'Yayasan (Bina Insan)',
        nama_ortu: 'Bpk. Hendra Saputra',
        phone: '08123456789'
    };

    if (recipientHeader) {
        recipientHeader.textContent = sample.nama ? `Ayah/Bunda ${sample.nama}` : 'Orang Tua Calon Siswa';
    }

    let text = textarea.value || DT_DEFAULT_TEMPLATE;
    text = text.replace(/\{nama\}/g, sample.nama)
               .replace(/\{nama_siswa\}/g, sample.nama)
               .replace(/\{no_daftar\}/g, sample.nodaf || sample.no_daftar || 'DT-SD-001')
               .replace(/\{nomor_pendaftaran\}/g, sample.nodaf || sample.no_daftar || 'DT-SD-001')
               .replace(/\{jenjang\}/g, sample.jenjang || 'SD')
               .replace(/\{rekomendasi\}/g, sample.rekom || 'Yayasan')
               .replace(/\{jalur\}/g, sample.rekom || 'Yayasan')
               .replace(/\{tahun_ajaran\}/g, DT_TAHUN_AJARAN)
               .replace(/\{nama_ortu\}/g, sample.nama_ortu || 'Ayah/Bunda')
               .replace(/\{nama_sekolah\}/g, 'SIT Bina Insan Palu');

    previewEl.textContent = text;
}

async function mulaiBroadcastDt() {
    if (!dtBroadcastQueue || dtBroadcastQueue.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Antrean Kosong',
            text: 'Tidak ada calon siswa pada antrean pesan WhatsApp.',
            confirmButtonColor: '#d97706'
        });
        return;
    }

    const total = dtBroadcastQueue.length;
    const delayMs = parseInt(document.getElementById('dtBroadcastDelay').value) || 3000;
    const template = document.getElementById('dtWaCustomTemplate').value.trim();

    if (!template) {
        Swal.fire({
            icon: 'warning',
            title: 'Pesan Kosong',
            text: 'Format teks pesan WhatsApp wajib diisi sebelum mengirim.',
            confirmButtonColor: '#d97706'
        });
        return;
    }

    // Beralih dari Panel Chat Custom ke Panel Progress Pengiriman
    document.getElementById('dtPanelChatCustom').classList.add('hidden');
    document.getElementById('dtPanelProgress').classList.remove('hidden');
    document.getElementById('dtModalHeaderTitle').textContent = 'Status Pengiriman WhatsApp';
    document.getElementById('dtModalHeaderSubtitle').textContent = `Mengirim pesan WhatsApp ke ${total} calon siswa di latar belakang...`;

    isDtBroadcastRunning = true;
    dtFailedItems = [];

    // UI Updates
    document.getElementById('btnHentikanBroadcastDt').classList.remove('hidden');
    document.getElementById('btnKembaliKeChatDt').disabled = true;
    document.getElementById('btnRetryAllFailedDt').classList.add('hidden');
    document.getElementById('dtFailSummaryBanner').classList.add('hidden');

    const progressBar = document.getElementById('dtProgressBar');
    const progressPct = document.getElementById('dtProgressPercentage');
    const statusText = document.getElementById('dtProgressStatusText');
    const counterTotal = document.getElementById('dtCounterTotal');
    const counterSuccess = document.getElementById('dtCounterSuccess');
    const counterFail = document.getElementById('dtCounterFail');
    const resultsList = document.getElementById('dtResultsList');

    resultsList.innerHTML = '';
    counterTotal.textContent = total;
    counterSuccess.textContent = '0';
    counterFail.textContent = '0';
    progressBar.style.width = '0%';
    progressPct.textContent = '0%';

    let successCount = 0;
    let failCount = 0;

    for (let i = 0; i < total; i++) {
        if (!isDtBroadcastRunning) {
            statusText.textContent = `⏸ Pengiriman dihentikan pada nomor ke-${i + 1}.`;
            break;
        }

        const item = dtBroadcastQueue[i];
        statusText.textContent = `Mengirim (${i + 1}/${total}): Ananda ${item.nama}...`;

        const pct = Math.round((i / total) * 100);
        progressBar.style.width = pct + '%';
        progressPct.textContent = pct + '%';

        const rowId = `dt-res-row-${item.id}`;
        let resData = null;

        try {
            const formData = new FormData();
            formData.append('csrf_token', DT_CSRF_TOKEN);
            formData.append('pendaftar_id', item.id);
            formData.append('pesan_custom', template);

            const response = await fetch('<?= url('kelola-spmb/daftar-tunggu/broadcast-send-single') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            resData = await response.json();
        } catch (err) {
            resData = {
                success: false,
                message: 'Gangguan koneksi: ' + err.message
            };
        }

        const nowTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

        if (resData && resData.success) {
            successCount++;
            counterSuccess.textContent = successCount;

            const rowHtml = `
                <div id="${rowId}" class="p-3 flex items-center justify-between gap-3 hover:bg-slate-50 transition-colors animate-slide-in">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs">
                            ✓
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-800 text-xs truncate">${escapeHtml(item.nama)}</div>
                            <div class="text-[10px] text-slate-400 font-mono">${escapeHtml(resData.phone || item.phone || '-')} &bull; ${escapeHtml(item.nodaf)} (${escapeHtml(item.jenjang)})</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span>Terkirim</span>
                            <span class="text-slate-400 font-normal">${nowTime}</span>
                        </span>
                    </div>
                </div>
            `;
            resultsList.insertAdjacentHTML('afterbegin', rowHtml);
        } else {
            failCount++;
            counterFail.textContent = failCount;
            dtFailedItems.push(item);

            const errorMsg = (resData && resData.message) ? resData.message : 'Nomor HP tidak valid atau gagal terkirim';
            const rowHtml = `
                <div id="${rowId}" class="p-3 flex items-center justify-between gap-3 bg-rose-50/50 hover:bg-rose-50/80 transition-colors animate-slide-in">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 font-bold text-xs">
                            ✕
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-800 text-xs truncate">${escapeHtml(item.nama)}</div>
                            <div class="text-[10px] text-rose-600 font-medium">${escapeHtml(errorMsg)} &bull; No: ${escapeHtml(item.phone || '-')}</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0" id="action-box-${item.id}">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                            Gagal
                        </span>
                        <button type="button" onclick="retrySingleDtWa(${item.id}, this)" class="px-2.5 py-1 rounded-xl bg-white hover:bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[10px] shadow-2xs transition-all flex items-center gap-1 cursor-pointer">
                            <svg class="w-3 h-3 text-rose-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            <span>Kirim Ulang</span>
                        </button>
                    </div>
                </div>
            `;
            resultsList.insertAdjacentHTML('afterbegin', rowHtml);
        }

        const pctDone = Math.round(((i + 1) / total) * 100);
        progressBar.style.width = pctDone + '%';
        progressPct.textContent = pctDone + '%';

        // Jeda waktu antar pesan jika bukan item terakhir
        if (i < total - 1 && isDtBroadcastRunning) {
            await new Promise(resolve => setTimeout(resolve, delayMs));
        }
    }

    // Broadcast Finished
    isDtBroadcastRunning = false;
    document.getElementById('btnHentikanBroadcastDt').classList.add('hidden');
    document.getElementById('btnKembaliKeChatDt').disabled = false;

    if (failCount > 0) {
        statusText.textContent = `⚠️ Selesai! Berhasil: ${successCount}, Gagal: ${failCount}.`;
        document.getElementById('dtFailSummaryBanner').classList.remove('hidden');
        document.getElementById('btnRetryAllFailedDt').classList.remove('hidden');
        document.getElementById('dtFailedTotalCount').textContent = failCount;
    } else {
        statusText.textContent = `🎉 Selesai! Seluruh ${successCount} pesan WhatsApp berhasil terkirim sempurna.`;
    }
}

function hentikanBroadcastDt() {
    isDtBroadcastRunning = false;
    const statusText = document.getElementById('dtProgressStatusText');
    if (statusText) {
        statusText.textContent = '⏸ Sedang menghentikan antrean pengiriman...';
    }
}

async function retrySingleDtWa(pendaftarId, btnEl) {
    const item = dtBroadcastQueue.find(it => it.id === pendaftarId) || dtFailedItems.find(it => it.id === pendaftarId);
    if (!item) {
        alert('Data calon siswa tidak ditemukan.');
        return;
    }

    const template = document.getElementById('dtWaCustomTemplate').value.trim() || DT_DEFAULT_TEMPLATE;
    const originalBtnHtml = btnEl.innerHTML;
    btnEl.disabled = true;
    btnEl.innerHTML = `<span class="animate-spin inline-block mr-1">⟳</span> Mengirim...`;

    try {
        const formData = new FormData();
        formData.append('csrf_token', DT_CSRF_TOKEN);
        formData.append('pendaftar_id', item.id);
        formData.append('pesan_custom', template);

        const response = await fetch('<?= url('kelola-spmb/daftar-tunggu/broadcast-send-single') ?>', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const resData = await response.json();
        const actionBox = document.getElementById(`action-box-${item.id}`);
        const nowTime = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

        if (resData && resData.success) {
            // Update row to success
            if (actionBox) {
                actionBox.innerHTML = `
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span>✓ Terkirim (Kirim Ulang)</span>
                        <span class="text-slate-400 font-normal">${nowTime}</span>
                    </span>
                `;
            }

            // Adjust counters
            const counterSuccess = document.getElementById('dtCounterSuccess');
            const counterFail = document.getElementById('dtCounterFail');
            let succ = parseInt(counterSuccess.textContent) || 0;
            let fail = parseInt(counterFail.textContent) || 0;
            counterSuccess.textContent = succ + 1;
            counterFail.textContent = Math.max(0, fail - 1);

            // Remove from failed list
            dtFailedItems = dtFailedItems.filter(it => it.id !== item.id);
            document.getElementById('dtFailedTotalCount').textContent = dtFailedItems.length;

            if (dtFailedItems.length === 0) {
                document.getElementById('dtFailSummaryBanner').classList.add('hidden');
                document.getElementById('btnRetryAllFailedDt').classList.add('hidden');
            }
        } else {
            alert('Gagal mengirim ulang: ' + ((resData && resData.message) ? resData.message : 'Nomor tidak valid'));
            btnEl.disabled = false;
            btnEl.innerHTML = originalBtnHtml;
        }
    } catch (err) {
        alert('Koneksi bermasalah: ' + err.message);
        btnEl.disabled = false;
        btnEl.innerHTML = originalBtnHtml;
    }
}

function retryAllFailedDt() {
    if (!dtFailedItems || dtFailedItems.length === 0) {
        return;
    }
    dtBroadcastQueue = [...dtFailedItems];
    mulaiBroadcastDt();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
