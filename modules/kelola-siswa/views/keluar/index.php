<?php
/**
 * Siswa Keluar & Mutasi - Portal BIP
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-500 to-red-700 flex items-center justify-center text-white shadow-lg shadow-rose-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                </div>
                <span>Siswa Keluar & Mutasi</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Pencatatan data siswa yang pindah sekolah (mutasi keluar), lulus/alumni, mengundurkan diri, serta penerbitan surat keterangan pindah.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-siswa/keluar/create') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-rose-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Proses Siswa Keluar / Mutasi</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <form method="GET" action="<?= url('kelola-siswa/keluar') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Cari Nama Siswa / NIS / Sekolah Tujuan</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Ketik nama siswa, NISN, sekolah tujuan..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
            </div>

            <!-- Jenis Keluar -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Jenis Status Keluar</label>
                <select name="jenis" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                    <option value="">Semua Jenis Keluar</option>
                    <option value="Mutasi Keluar" <?= $filterJenis === 'Mutasi Keluar' ? 'selected' : '' ?>>Mutasi Keluar / Pindah</option>
                    <option value="Lulus" <?= $filterJenis === 'Lulus' ? 'selected' : '' ?>>Lulus / Alumni</option>
                    <option value="Mengundurkan Diri" <?= $filterJenis === 'Mengundurkan Diri' ? 'selected' : '' ?>>Mengundurkan Diri</option>
                    <option value="Dikeluarkan" <?= $filterJenis === 'Dikeluarkan' ? 'selected' : '' ?>>Dikeluarkan (DO)</option>
                    <option value="Wafat" <?= $filterJenis === 'Wafat' ? 'selected' : '' ?>>Wafat</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold transition-colors">
                    Filter
                </button>
                <?php if ($search || $filterJenis): ?>
                    <a href="<?= url('kelola-siswa/keluar') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">No</th>
                        <th class="px-4 py-3">Nama Siswa & Jenjang</th>
                        <th class="px-4 py-3">Jenis Keluar</th>
                        <th class="px-4 py-3">Tanggal Keluar</th>
                        <th class="px-4 py-3">Sekolah Tujuan / Alasan</th>
                        <th class="px-4 py-3">No. Surat Keterangan</th>
                        <th class="px-4 py-3 text-center">Cetak Surat</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($keluarList)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                            <p class="text-sm font-semibold text-slate-600">Tidak ada data siswa keluar / mutasi</p>
                            <p class="text-xs text-slate-400 mt-1">Klik tombol "Proses Siswa Keluar / Mutasi" bila ada siswa yang pindah atau lulus.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = $offset + 1; foreach ($keluarList as $k): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-3.5 text-center font-semibold text-slate-400"><?= $no++ ?></td>
                        <td class="px-4 py-3.5">
                            <div class="font-extrabold text-slate-900"><?= e($k['nama_lengkap'] ?: $k['nama']) ?></div>
                            <div class="text-[11px] text-slate-500">
                                <span class="font-semibold text-slate-700"><?= e($k['jenjang'] ?: 'SD') ?></span> - Kelas Terakhir: <?= e($k['kelas_terakhir'] ?: $k['kelas'] ?: '-') ?> (NISN: <?= e($k['nisn'] ?: '-') ?>)
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-800 border border-rose-200 inline-block">
                                <?= e($k['jenis_keluar']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-semibold text-slate-800"><?= $k['tanggal_keluar'] ? date('d M Y', strtotime($k['tanggal_keluar'])) : '-' ?></div>
                            <div class="text-[11px] text-slate-500">T.A: <?= e($k['tahun_ajaran'] ?: '-') ?></div>
                        </td>
                        <td class="px-4 py-3.5 max-w-[200px]">
                            <div class="font-bold text-slate-900 truncate" title="<?= e($k['sekolah_tujuan']) ?>">
                                <?= e($k['sekolah_tujuan'] ?: '-') ?>
                            </div>
                            <div class="text-[11px] text-slate-500 truncate" title="<?= e($k['alasan_keluar']) ?>">
                                <?= e($k['alasan_keluar'] ?: '-') ?>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 font-mono text-slate-700">
                            <?= e($k['nomor_surat'] ?: '-') ?>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <a href="<?= url('kelola-siswa/keluar/cetak/' . $k['id']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors" title="Cetak Surat Keterangan Pindah">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                                Cetak
                            </a>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Reaktivasi -->
                                <form action="<?= url('kelola-siswa/keluar/reaktivasi/' . $k['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda ingin membatalkan status keluar dan mengaktifkan kembali siswa ini?');" class="inline">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors" title="Reaktivasi / Aktifkan Kembali">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                    </button>
                                </form>
                                <a href="<?= url('kelola-siswa/keluar/edit/' . $k['id']) ?>" class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors" title="Edit Data Mutasi">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                </a>
                                <form action="<?= url('kelola-siswa/keluar/delete/' . $k['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan mutasi ini?');" class="inline">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
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
        <div class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-slate-100 gap-3">
            <p class="text-xs text-slate-500">
                Menampilkan <strong><?= $offset + 1 ?></strong> s/d <strong><?= min($total, $offset + $limit) ?></strong> dari <strong><?= number_format($total) ?></strong> siswa keluar
            </p>
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="<?= url('kelola-siswa/keluar?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-medium">← Sebelumnya</a>
                <?php endif; ?>
                <span class="px-3 py-1.5 bg-rose-600 text-white rounded-lg text-xs font-bold"><?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= url('kelola-siswa/keluar?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-medium">Berikutnya →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>
