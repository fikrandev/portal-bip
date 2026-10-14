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
            <a href="<?= url('kelola-spmb/daftar-tunggu/export?' . http_build_query($filters)) ?>" 
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
                            <td colspan="9" class="py-12 text-center text-slate-400">
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
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
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
                                    <div class="text-[11px] text-slate-500"><?= e($item['no_hp'] ?: $item['no_hp_ayah'] ?: '-') ?></div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($item['tanggal_daftar'])) ?>
                                </td>
                                <td class="py-3 px-4 space-y-1">
                                    <div>
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
                                    </div>
                                    <div>
                                        <?php
                                        $bioSt = $item['biodata_status'] ?? 'belum_diisi';
                                        $bioBadge = match($bioSt) {
                                            'lengkap' => 'bg-emerald-100 text-emerald-800 font-bold',
                                            'draft'   => 'bg-amber-100 text-amber-800 font-semibold',
                                            default   => 'bg-slate-100 text-slate-500'
                                        };
                                        $bioLabel = match($bioSt) {
                                            'lengkap' => '✓ Biodata Lengkap',
                                            'draft'   => '⏱ Biodata Draft',
                                            default   => 'Biodata Belum Diisi'
                                        };
                                        ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] <?= $bioBadge ?>">
                                            <?= $bioLabel ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="<?= url('kelola-spmb/daftar-tunggu/detail/' . $item['id']) ?>" 
                                            title="Detail Siswa"
                                            class="p-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                        <button type="button" onclick="salinLinkPublic('<?= url('spmb/biodata/' . ($item['biodata_token'] ?? '')) ?>')" 
                                                title="Salin Link Biodata Lengkap"
                                                class="p-1.5 rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                                        </button>
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
        <?php if (($totalPages ?? 1) > 1): ?>
            <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">
                    Halaman <strong><?= $currentPage ?></strong> dari <strong><?= $totalPages ?></strong>
                </span>
                <div class="flex items-center gap-1">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= url('kelola-spmb/daftar-tunggu?' . http_build_query(array_merge($filters, ['page' => $currentPage - 1]))) ?>" 
                           class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold">
                            &larr; Sebelumnya
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <a href="<?= url('kelola-spmb/daftar-tunggu?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>" 
                           class="px-3 py-1.5 rounded-lg <?= ($i === $currentPage) ? 'bg-amber-600 text-white font-bold' : 'border border-slate-200 hover:bg-slate-50 text-slate-700' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="<?= url('kelola-spmb/daftar-tunggu?' . http_build_query(array_merge($filters, ['page' => $currentPage + 1]))) ?>" 
                           class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold">
                            Selanjutnya &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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

<script>
// Tanggal patokan umur dari group aktif (standar 1 Juli 2027)
const TANGGAL_PATOKAN_UMUR = <?= json_encode($activeGroup['tanggal_patokan_umur'] ?? '2027-07-01') ?>;

function openModalTambahDaftarTunggu() {
    const modal = document.getElementById('modalTambahDaftarTunggu');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
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
</script>
