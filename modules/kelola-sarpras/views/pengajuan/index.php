<?php
/**
 * Pengajuan Pembelian Baru Sarpras
 * Portal BIP
 */
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="<?= url('kelola-sarpras') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
                    &larr; Dashboard Sarpras
                </a>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1 flex items-center gap-2.5">
                <span>Pengajuan Pembelian Baru Sarpras</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Alur pengadaan aset, inventaris baru, serta verifikasi persetujuan pembelian antar unit sekolah
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <?php if ($canCreate): ?>
            <button onclick="openModalTambahPengajuan()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Pengajuan Baru</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- Total -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengajuan</p>
            <h3 class="text-xl sm:text-2xl font-extrabold text-slate-800 mt-1"><?= number_format($stats['total'] ?? 0) ?></h3>
            <p class="text-[10px] text-slate-500 mt-0.5">Semua usulan baru</p>
        </div>
        <!-- Menunggu Verifikasi -->
        <div class="bg-amber-50/60 rounded-2xl p-4 border border-amber-200/80 shadow-sm">
            <p class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu Verif</p>
            <h3 class="text-xl sm:text-2xl font-extrabold text-amber-800 mt-1"><?= number_format($stats['menunggu'] ?? 0) ?></h3>
            <p class="text-[10px] text-amber-600 mt-0.5">Butuh persetujuan</p>
        </div>
        <!-- Disetujui -->
        <div class="bg-emerald-50/60 rounded-2xl p-4 border border-emerald-200/80 shadow-sm">
            <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Disetujui</p>
            <h3 class="text-xl sm:text-2xl font-extrabold text-emerald-800 mt-1"><?= number_format($stats['disetujui'] ?? 0) ?></h3>
            <p class="text-[10px] text-emerald-600 mt-0.5">Siap dibelanjakan</p>
        </div>
        <!-- Ditolak -->
        <div class="bg-rose-50/60 rounded-2xl p-4 border border-rose-200/80 shadow-sm">
            <p class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Ditolak</p>
            <h3 class="text-xl sm:text-2xl font-extrabold text-rose-800 mt-1"><?= number_format($stats['ditolak'] ?? 0) ?></h3>
            <p class="text-[10px] text-rose-600 mt-0.5">Tidak disetujui</p>
        </div>
        <!-- Selesai -->
        <div class="bg-blue-50/60 rounded-2xl p-4 border border-blue-200/80 shadow-sm">
            <p class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Selesai / Terbeli</p>
            <h3 class="text-xl sm:text-2xl font-extrabold text-blue-800 mt-1"><?= number_format($stats['selesai'] ?? 0) ?></h3>
            <p class="text-[10px] text-blue-600 mt-0.5">Sudah inventarisasi</p>
        </div>
        <!-- Total Estimasi Biaya -->
        <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Estimasi</p>
            <h3 class="text-sm sm:text-base font-extrabold text-slate-800 mt-1.5 truncate">
                Rp <?= number_format($stats['total_estimasi'] ?? 0, 0, ',', '.') ?>
            </h3>
            <p class="text-[10px] text-slate-500 mt-0.5">Realisasi: Rp <?= number_format($stats['total_realisasi'] ?? 0, 0, ',', '.') ?></p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form method="GET" action="<?= url('kelola-sarpras/pengajuan') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
            <!-- Search -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Cari Usulan / Pengaju</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Nama barang / pemohon..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
            </div>

            <!-- Status Verifikasi -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Verifikasi</label>
                <select name="status_verifikasi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                    <option value="">Semua Status Verifikasi</option>
                    <option value="Menunggu" <?= ($filters['status_verifikasi'] ?? '') === 'Menunggu' ? 'selected' : '' ?>>⏳ Menunggu Verifikasi</option>
                    <option value="Disetujui" <?= ($filters['status_verifikasi'] ?? '') === 'Disetujui' ? 'selected' : '' ?>>✓ Disetujui</option>
                    <option value="Ditolak" <?= ($filters['status_verifikasi'] ?? '') === 'Ditolak' ? 'selected' : '' ?>>✕ Ditolak</option>
                </select>
            </div>

            <!-- Status Proses -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Status Proses Pengadaan</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                    <option value="">Semua Status Proses</option>
                    <option value="Menunggu" <?= ($filters['status'] ?? '') === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                    <option value="Disetujui" <?= ($filters['status'] ?? '') === 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="Diproses" <?= ($filters['status'] ?? '') === 'Diproses' ? 'selected' : '' ?>>Diproses Pembelian</option>
                    <option value="Selesai" <?= ($filters['status'] ?? '') === 'Selesai' ? 'selected' : '' ?>>Selesai Diterima</option>
                    <option value="Ditolak" <?= ($filters['status'] ?? '') === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>

            <!-- Unit -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Unit Sekolah</label>
                <select name="unit" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                    <option value="">Semua Unit</option>
                    <option value="PAUD" <?= ($filters['unit'] ?? '') === 'PAUD' ? 'selected' : '' ?>>PAUD</option>
                    <option value="SD" <?= ($filters['unit'] ?? '') === 'SD' ? 'selected' : '' ?>>SD</option>
                    <option value="SMP" <?= ($filters['unit'] ?? '') === 'SMP' ? 'selected' : '' ?>>SMP</option>
                    <option value="SMA" <?= ($filters['unit'] ?? '') === 'SMA' ? 'selected' : '' ?>>SMA</option>
                    <option value="Umum" <?= ($filters['unit'] ?? '') === 'Umum' ? 'selected' : '' ?>>Umum / Yayasan</option>
                </select>
            </div>

            <!-- Button -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    <span>Filter</span>
                </button>
                <a href="<?= url('kelola-sarpras/pengajuan') ?>" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-all text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50/80 text-slate-500 text-[11px] uppercase font-bold tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Kode &amp; Tanggal</th>
                        <th class="py-3.5 px-4">Unit &amp; Pemohon</th>
                        <th class="py-3.5 px-4">Nama Barang &amp; Rincian</th>
                        <th class="py-3.5 px-4 text-center">Jumlah</th>
                        <th class="py-3.5 px-4 text-right">Estimasi Anggaran</th>
                        <th class="py-3.5 px-4 text-center">Status Verifikasi</th>
                        <th class="py-3.5 px-4 text-center">Status Proses</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="text-4xl mb-2">📋</div>
                                <p class="font-semibold text-slate-600">Belum ada data pengajuan pembelian baru.</p>
                                <p class="text-[11px] text-slate-400 mt-1">Klik tombol "+ Buat Pengajuan Baru" untuk mengajukan pengadaan aset/barang.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = ($offset ?? 0) + 1; foreach ($items as $p): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-mono text-primary-700 font-bold text-xs"><?= e($p['kode_pengajuan']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= date('d M Y', strtotime($p['tanggal_pengajuan'])) ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-primary-50 text-primary-700 border border-primary-200">
                                            <?= e($p['unit'] ?: 'Umum') ?>
                                        </span>
                                    </div>
                                    <div class="font-semibold text-slate-800 mt-1"><?= e($p['pemohon_nama'] ?? $p['pengaju'] ?? 'Staff') ?></div>
                                    <div class="text-[10px] text-slate-400"><?= !empty($p['nama_ruangan']) ? e($p['nama_ruangan']) : 'Ruang Umum' ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= e($p['nama_barang']) ?></div>
                                    <div class="text-[11px] text-slate-500 font-medium"><?= e($p['nama_kategori'] ?: 'Umum') ?></div>
                                    <?php if (!empty($p['spesifikasi'])): ?>
                                        <p class="text-[10px] text-slate-400 mt-0.5 line-clamp-1"><?= e($p['spesifikasi']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-extrabold text-slate-800 text-sm"><?= number_format($p['jumlah']) ?></span>
                                    <span class="text-[10px] text-slate-500 block"><?= e($p['satuan'] ?? 'Unit') ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-bold text-slate-800">
                                        Rp <?= number_format($p['total_estimasi'] ?: ($p['jumlah'] * $p['estimasi_harga']), 0, ',', '.') ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        @ Rp <?= number_format($p['estimasi_harga'], 0, ',', '.') ?>
                                    </div>
                                    <?php if ($p['realisasi_harga'] > 0): ?>
                                        <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">
                                            Real: Rp <?= number_format($p['realisasi_harga'], 0, ',', '.') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (($p['status_verifikasi'] ?? '') === 'Disetujui'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 block">
                                            ✓ Disetujui
                                        </span>
                                        <?php if (!empty($p['verified_by_name'])): ?>
                                            <span class="text-[9px] text-slate-400 block mt-0.5" title="<?= e($p['catatan_verifikasi'] ?? '') ?>">oleh <?= e($p['verified_by_name']) ?></span>
                                        <?php endif; ?>
                                    <?php elseif (($p['status_verifikasi'] ?? '') === 'Ditolak'): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 block">
                                            ✕ Ditolak
                                        </span>
                                        <?php if (!empty($p['verified_by_name'])): ?>
                                            <span class="text-[9px] text-slate-400 block mt-0.5" title="<?= e($p['catatan_verifikasi'] ?? '') ?>">oleh <?= e($p['verified_by_name']) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 block">
                                            ⏳ Menunggu Verifikasi
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($p['status'] === 'Selesai'): ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">Selesai</span>
                                    <?php elseif ($p['status'] === 'Diproses'): ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Diproses</span>
                                    <?php elseif ($p['status'] === 'Disetujui'): ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Disetujui</span>
                                    <?php elseif ($p['status'] === 'Ditolak'): ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Ditolak</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">Menunggu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap">
                                        <?php if ($canVerify): ?>
                                            <button onclick="openModalVerifikasiPengajuan(<?= htmlspecialchars(json_encode($p)) ?>)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm shadow-emerald-600/20 transition-all hover:scale-[1.02] active:scale-95" title="Verifikasi Pengajuan Pembelian">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                                <span>Verifikasi</span>
                                            </button>
                                        <?php endif; ?>

                                        <button onclick="openModalDetailPengajuan(<?= htmlspecialchars(json_encode($p)) ?>)" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all hover:scale-105 active:scale-95" title="Lihat Detail Pengajuan">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                            <span>Detail</span>
                                        </button>

                                        <a href="<?= url('kelola-sarpras/pengajuan/cetak/' . $p['id']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200/80 font-bold text-xs transition-all hover:scale-105 active:scale-95" title="Cetak Lembar Pengajuan">
                                            <svg class="w-3.5 h-3.5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                                            <span>Cetak</span>
                                        </a>

                                        <?php if ($canDelete): ?>
                                            <form action="<?= url('kelola-sarpras/pengajuan/delete/' . $p['id']) ?>" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengajuan ini?')" class="inline m-0 p-0">
                                                <?= CSRF::field() ?>
                                                <button type="submit" class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/60 font-semibold text-xs transition-all hover:scale-105 active:scale-95 flex items-center justify-center" title="Hapus Pengajuan">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">
                    Menampilkan <strong><?= $offset + 1 ?></strong> - <strong><?= min($offset + $limit, $totalItems) ?></strong> dari <strong><?= $totalItems ?></strong> data
                </span>
                <div class="flex items-center gap-1.5">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php
                        $query = array_merge($filters, ['page' => $i]);
                        $link = url('kelola-sarpras/pengajuan?' . http_build_query($query));
                        ?>
                        <a href="<?= $link ?>" class="px-3 py-1.5 rounded-xl font-bold transition-all <?= $i === $page ? 'bg-primary-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL TAMBAH PENGAJUAN BARU -->
<div id="modal-tambah-pengajuan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-xl w-full max-h-[92vh] overflow-y-auto custom-scrollbar transform scale-95 transition-all duration-200 p-6 sm:p-7">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary-50 text-primary-600 border border-primary-200/60 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Ajukan Pembelian Baru Sarpras</h3>
                    <p class="text-[11px] text-slate-500">Usulan pengadaan aset/barang baru untuk diverifikasi oleh sarpras &amp; pimpinan</p>
                </div>
            </div>
            <button type="button" onclick="closeModalTambahPengajuan()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center text-lg font-bold transition-colors">&times;</button>
        </div>

        <form action="<?= url('kelola-sarpras/pengajuan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
            <?= CSRF::field() ?>

            <!-- Row 1: Unit & Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Unit Sekolah <span class="text-rose-500">*</span></label>
                    <select name="unit" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                        <option value="Umum">Umum / Yayasan</option>
                        <option value="PAUD">PAUD</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Pengajuan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_pengajuan" value="<?= date('Y-m-d') ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                </div>
            </div>

            <!-- Row 2: Kategori & Rencana Ruangan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori Aset</label>
                    <select name="kategori_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                        <option value="">-- Pilih Kategori (Opsional) --</option>
                        <?php foreach ($kategoriList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= e($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Rencana Penempatan Ruangan</label>
                    <select name="ruangan_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                        <option value="">-- Pilih Ruangan (Opsional) --</option>
                        <?php foreach ($ruanganList as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['nama_ruangan']) ?> (<?= e($r['unit'] ?? 'Umum') ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Nama Barang -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Barang / Pengadaan Baru <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_barang" required placeholder="Contoh: AC 1 PK Sharp, Smart TV 55 Inch, Laptop Core i5..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
            </div>

            <!-- Row 3: Jumlah, Satuan & Estimasi Harga Satuan -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jumlah <span class="text-rose-500">*</span></label>
                    <input type="number" id="input_tambah_jumlah" name="jumlah" value="1" min="1" required oninput="calcTotalEstimasi()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-bold text-center">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Satuan</label>
                    <input type="text" name="satuan" value="Unit" placeholder="Unit, Buah, Set, Pcs..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Estimasi Harga / Satuan <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs select-none">Rp</span>
                        <input type="text" id="input_tambah_harga" name="estimasi_harga" value="0" required oninput="formatRupiah(this); calcTotalEstimasi();" class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-bold text-right">
                    </div>
                </div>
            </div>

            <!-- Highlight Box Total Estimasi -->
            <div class="p-3.5 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold text-emerald-800">Total Anggaran Estimasi:</span>
                    <p class="text-[10px] text-emerald-600">Perhitungan otomatis (Jumlah × Estimasi Satuan)</p>
                </div>
                <div class="text-base font-extrabold text-emerald-800" id="label_total_estimasi">
                    Rp 0
                </div>
            </div>

            <!-- Spesifikasi -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Spesifikasi Lengkap / Rincian Merk</label>
                <textarea name="spesifikasi" rows="2" placeholder="Sebutkan merk, tipe, dimensi, warna, atau standar spek teknis yang diharapkan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium"></textarea>
            </div>

            <!-- Nama Pemohon / Pengaju -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Pemohon / Pengaju <span class="text-rose-500">*</span></label>
                <input type="text" name="pengaju" value="<?= e(Auth::name()) ?>" required placeholder="Nama lengkap pemohon..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
            </div>

            <!-- Alasan Kebutuhan -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Alasan Kebutuhan / Urgensi <span class="text-rose-500">*</span></label>
                <textarea name="keperluan" id="input_tambah_keperluan" rows="2" required placeholder="Jelaskan untuk keperluan apa dan mengapa pengadaan ini dibutuhkan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium"></textarea>
            </div>

            <!-- File Pendukung / Brosur / Quotation -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Dokumen Pendukung / Brosur / Penawaran (Opsional)</label>
                <input type="file" name="file_pendukung" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                <p class="text-[10px] text-slate-400 mt-0.5">Format: JPG, PNG, PDF (Maks. 5MB)</p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeModalTambahPengajuan()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-all">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold shadow-md shadow-primary-600/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                    <span>Kirim Pengajuan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VERIFIKASI PENGAJUAN (RBAC) -->
<div id="modal-verifikasi-pengajuan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full max-h-[92vh] overflow-y-auto custom-scrollbar transform scale-95 transition-all duration-200 p-6 sm:p-7">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200/60 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Verifikasi Pengajuan Pembelian</h3>
                    <p class="text-[11px] text-slate-500" id="verif-pengajuan-subtitle">Tinjau dan putuskan persetujuan pembelian barang</p>
                </div>
            </div>
            <button type="button" onclick="closeModalVerifikasiPengajuan()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center text-lg font-bold transition-colors">&times;</button>
        </div>

        <form id="form-verifikasi-pengajuan" action="" method="POST" class="space-y-4 text-xs">
            <?= CSRF::field() ?>

            <!-- Ringkasan Usulan -->
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1.5">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="font-mono text-[10px] font-bold text-primary-700" id="verif-kode">PGJ-xxxx</span>
                        <h4 class="font-bold text-slate-800 text-sm" id="verif-barang">Nama Barang</h4>
                    </div>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white text-slate-700 border border-slate-200" id="verif-unit">Unit</span>
                </div>
                <div class="text-slate-500 flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/60">
                    <span>Pengaju: <strong id="verif-pemohon" class="text-slate-700">Nama</strong></span>
                    <span>Estimasi: <strong id="verif-total" class="text-emerald-700">Rp 0</strong></span>
                </div>
            </div>

            <!-- Keputusan Verifikasi -->
            <div>
                <label class="block font-bold text-slate-700 mb-2">Keputusan Verifikasi <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50 cursor-pointer transition-all">
                        <input type="radio" name="status_verifikasi" value="Disetujui" id="verif-opt-setuju" required class="text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="font-bold text-emerald-800 block">✓ Setujui Pembelian</span>
                            <span class="text-[10px] text-emerald-600">Usulan disetujui untuk diproses</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-rose-200 bg-rose-50/50 hover:bg-rose-50 cursor-pointer transition-all">
                        <input type="radio" name="status_verifikasi" value="Ditolak" id="verif-opt-tolak" class="text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="font-bold text-rose-800 block">✕ Tolak Pengajuan</span>
                            <span class="text-[10px] text-rose-600">Usulan ditolak dengan alasan</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Status Proses Operasional -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tahap Proses Pembelian</label>
                <select name="status" id="verif-status-proses" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium">
                    <option value="Disetujui">Disetujui (Menunggu Pengadaan)</option>
                    <option value="Diproses">Sedang Diproses Belanja / PO</option>
                    <option value="Selesai">Selesai (Barang Diterima &amp; Siap Diinventarisir)</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>

            <!-- Realisasi Harga (Jika sudah dibeli) -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Realisasi Harga Beli Total (Rp)</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs select-none">Rp</span>
                    <input type="text" name="realisasi_harga" id="verif-realisasi-harga" placeholder="0" oninput="formatRupiah(this)" class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-bold text-slate-800">
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Isi saat pembelian telah dilakukan atau disetujui plafon biayanya</p>
            </div>

            <!-- Catatan Verifikasi -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Catatan / Arahan Verifikator <span class="text-rose-500">*</span></label>
                <textarea name="catatan_verifikasi" id="verif-catatan" rows="3" required placeholder="Tuliskan catatan persetujuan, instruksi merk, atau alasan penolakan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:bg-white focus:border-primary-500 font-medium"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeModalVerifikasiPengajuan()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition-all">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md shadow-emerald-600/20 transition-all">
                    Simpan Verifikasi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DETAIL PENGAJUAN -->
<div id="modal-detail-pengajuan" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-200">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-lg w-full max-h-[92vh] overflow-y-auto custom-scrollbar transform scale-95 transition-all duration-200 p-6 sm:p-7">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-5">
            <div>
                <span class="font-mono text-xs font-bold text-primary-700" id="det-kode">PGJ-xxxx</span>
                <h3 class="text-base font-bold text-slate-800" id="det-barang">Nama Barang</h3>
            </div>
            <button type="button" onclick="closeModalDetailPengajuan()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-400 hover:text-slate-600 flex items-center justify-center text-lg font-bold transition-colors">&times;</button>
        </div>

        <div class="space-y-3.5 text-xs">
            <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-100">
                <div>
                    <span class="text-slate-400 block text-[10px]">Unit Pemohon:</span>
                    <strong class="text-slate-800 text-sm" id="det-unit">-</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">Nama Pemohon:</span>
                    <strong class="text-slate-800 text-sm" id="det-pemohon">-</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">Tanggal Pengajuan:</span>
                    <strong class="text-slate-800" id="det-tanggal">-</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px]">Rencana Penempatan:</span>
                    <strong class="text-slate-800" id="det-ruangan">-</strong>
                </div>
            </div>

            <div class="p-3 bg-emerald-50/50 rounded-2xl border border-emerald-100 grid grid-cols-3 gap-2 text-center">
                <div>
                    <span class="text-emerald-700 block text-[10px] font-semibold">Jumlah:</span>
                    <strong class="text-slate-800 text-sm" id="det-jumlah">-</strong>
                </div>
                <div>
                    <span class="text-emerald-700 block text-[10px] font-semibold">Est. Satuan:</span>
                    <strong class="text-slate-800 text-xs" id="det-harga-satuan">-</strong>
                </div>
                <div>
                    <span class="text-emerald-700 block text-[10px] font-semibold">Total Estimasi:</span>
                    <strong class="text-emerald-700 text-sm" id="det-total-estimasi">-</strong>
                </div>
            </div>

            <div>
                <span class="font-bold text-slate-700 block mb-1">Spesifikasi / Rincian:</span>
                <p class="p-3 bg-slate-50 rounded-xl text-slate-700 whitespace-pre-line" id="det-spek">-</p>
            </div>

            <div>
                <span class="font-bold text-slate-700 block mb-1">Alasan Kebutuhan:</span>
                <p class="p-3 bg-slate-50 rounded-xl text-slate-700 whitespace-pre-line" id="det-alasan">-</p>
            </div>

            <!-- Status Verifikasi Box -->
            <div class="p-3.5 rounded-2xl border" id="det-box-verif">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-xs" id="det-verif-title">Status Verifikasi</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold" id="det-verif-badge">-</span>
                </div>
                <div class="space-y-1 text-[11px] text-slate-600">
                    <p>Diverifikasi oleh: <strong id="det-verif-by">-</strong></p>
                    <p>Waktu verifikasi: <strong id="det-verif-at">-</strong></p>
                    <p>Catatan verifikasi: <span class="italic text-slate-800 block mt-0.5 p-2 bg-white rounded-lg border border-slate-200" id="det-verif-notes">-</span></p>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
            <a href="#" id="det-link-cetak" target="_blank" class="px-4 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold text-xs border border-teal-200 transition-all flex items-center gap-1.5">
                🖨️ Cetak Formulir
            </a>
            <button type="button" onclick="closeModalDetailPengajuan()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
// ── MODAL TAMBAH PENGAJUAN ──────────────────────────────────────────────────
function openModalTambahPengajuan() {
    const m = document.getElementById('modal-tambah-pengajuan');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
    calcTotalEstimasi();
}

function closeModalTambahPengajuan() {
    const m = document.getElementById('modal-tambah-pengajuan');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}

function formatRupiah(el) {
    let clean = (el.value || '').replace(/[^0-9]/g, '');
    if (!clean) {
        el.value = '';
        return;
    }
    const val = parseInt(clean, 10);
    el.value = val > 0 ? val.toLocaleString('id-ID') : '0';
}

function calcTotalEstimasi() {
    const jml = parseInt(document.getElementById('input_tambah_jumlah').value, 10) || 1;
    let hrgStr = (document.getElementById('input_tambah_harga').value || '').replace(/[^0-9]/g, '');
    const hrg = parseInt(hrgStr, 10) || 0;
    
    // Format input harga
    document.getElementById('input_tambah_harga').value = hrg > 0 ? hrg.toLocaleString('id-ID') : '0';
    
    const total = jml * hrg;
    document.getElementById('label_total_estimasi').innerText = 'Rp ' + total.toLocaleString('id-ID');
}

// ── MODAL VERIFIKASI PENGAJUAN ──────────────────────────────────────────────
function openModalVerifikasiPengajuan(item) {
    document.getElementById('verif-kode').innerText = item.kode_pengajuan || '-';
    document.getElementById('verif-barang').innerText = item.nama_barang || '-';
    document.getElementById('verif-unit').innerText = item.unit || 'Umum';
    document.getElementById('verif-pemohon').innerText = item.pemohon_nama || item.pengaju || 'Staff';
    
    const total = item.total_estimasi || (item.jumlah * item.estimasi_harga);
    document.getElementById('verif-total').innerText = 'Rp ' + parseInt(total).toLocaleString('id-ID');
    
    if (item.status_verifikasi === 'Disetujui') {
        document.getElementById('verif-opt-setuju').checked = true;
    } else if (item.status_verifikasi === 'Ditolak') {
        document.getElementById('verif-opt-tolak').checked = true;
    } else {
        document.getElementById('verif-opt-setuju').checked = true;
    }
    
    document.getElementById('verif-status-proses').value = item.status || 'Disetujui';
    
    let realVal = item.realisasi_harga ? parseInt(item.realisasi_harga) : 0;
    document.getElementById('verif-realisasi-harga').value = realVal > 0 ? realVal.toLocaleString('id-ID') : '';
    
    document.getElementById('verif-catatan').value = item.catatan_verifikasi || '';
    document.getElementById('form-verifikasi-pengajuan').action = '<?= url("kelola-sarpras/pengajuan/verifikasi/") ?>' + item.id;
    
    const m = document.getElementById('modal-verifikasi-pengajuan');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}

function closeModalVerifikasiPengajuan() {
    const m = document.getElementById('modal-verifikasi-pengajuan');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}

// ── MODAL DETAIL PENGAJUAN ──────────────────────────────────────────────────
function openModalDetailPengajuan(item) {
    document.getElementById('det-kode').innerText = item.kode_pengajuan || '-';
    document.getElementById('det-barang').innerText = item.nama_barang || '-';
    document.getElementById('det-unit').innerText = item.unit || 'Umum';
    document.getElementById('det-pemohon').innerText = item.pemohon_nama || item.pengaju || 'Staff';
    document.getElementById('det-tanggal').innerText = item.tanggal_pengajuan || '-';
    document.getElementById('det-ruangan').innerText = item.nama_ruangan || 'Fasilitas Umum';
    
    document.getElementById('det-jumlah').innerText = (item.jumlah || 1) + ' ' + (item.satuan || 'Unit');
    document.getElementById('det-harga-satuan').innerText = 'Rp ' + parseInt(item.estimasi_harga || 0).toLocaleString('id-ID');
    
    const total = item.total_estimasi || ((item.jumlah || 1) * (item.estimasi_harga || 0));
    document.getElementById('det-total-estimasi').innerText = 'Rp ' + parseInt(total).toLocaleString('id-ID');
    
    document.getElementById('det-spek').innerText = item.spesifikasi || '-';
    document.getElementById('det-alasan').innerText = item.alasan_kebutuhan || item.keperluan || '-';
    
    const boxVerif = document.getElementById('det-box-verif');
    const badgeVerif = document.getElementById('det-verif-badge');
    
    if (item.status_verifikasi === 'Disetujui') {
        boxVerif.className = 'p-3.5 rounded-2xl border border-emerald-200 bg-emerald-50/50';
        badgeVerif.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800';
        badgeVerif.innerText = '✓ Disetujui';
    } else if (item.status_verifikasi === 'Ditolak') {
        boxVerif.className = 'p-3.5 rounded-2xl border border-rose-200 bg-rose-50/50';
        badgeVerif.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800';
        badgeVerif.innerText = '✕ Ditolak';
    } else {
        boxVerif.className = 'p-3.5 rounded-2xl border border-amber-200 bg-amber-50/50';
        badgeVerif.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800';
        badgeVerif.innerText = '⏳ Menunggu Verifikasi';
    }
    
    document.getElementById('det-verif-by').innerText = item.verified_by_name || 'Belum diverifikasi';
    document.getElementById('det-verif-at').innerText = item.verified_at || '-';
    document.getElementById('det-verif-notes').innerText = item.catatan_verifikasi || 'Tidak ada catatan khusus.';
    
    document.getElementById('det-link-cetak').href = '<?= url("kelola-sarpras/pengajuan/cetak/") ?>' + item.id;
    
    const m = document.getElementById('modal-detail-pengajuan');
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.remove('scale-95');
}

function closeModalDetailPengajuan() {
    const m = document.getElementById('modal-detail-pengajuan');
    m.classList.add('opacity-0', 'pointer-events-none');
    m.firstElementChild.classList.add('scale-95');
}
</script>
