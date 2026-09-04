<?php
/**
 * Buku Induk Siswa - Portal BIP
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <span>Buku Induk Siswa</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Rekapitulasi lengkap lembar buku induk resmi peserta didik, nomor induk, identitas kependudukan, dan data orang tua.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-siswa/buku-induk/export?' . http_build_query($_GET)) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold shadow-sm transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                <span>Export Buku Induk</span>
            </a>
            <a href="<?= url('kelola-siswa/create') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-indigo-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Tambah Siswa Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <form method="GET" action="<?= url('kelola-siswa/buku-induk') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Cari Nama / NIS / NISN / NIK</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Ketik nama siswa, NIS, NISN, NIK..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
            </div>

            <!-- Jenjang -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Satuan Jenjang</label>
                <select name="jenjang" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                    <option value="">Semua Satuan</option>
                    <option value="PAUD" <?= strtoupper($filterJenjang) === 'PAUD' ? 'selected' : '' ?>>PAUD / TK</option>
                    <option value="SD" <?= strtoupper($filterJenjang) === 'SD' ? 'selected' : '' ?>>SD IT</option>
                    <option value="SMP" <?= strtoupper($filterJenjang) === 'SMP' ? 'selected' : '' ?>>SMP IT</option>
                    <option value="SMA" <?= strtoupper($filterJenjang) === 'SMA' ? 'selected' : '' ?>>SMA IT</option>
                </select>
            </div>

            <!-- Kelas -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Kelas / Rombel</label>
                <select name="kelas" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= e($k['kelas']) ?>" <?= $filterKelas === $k['kelas'] ? 'selected' : '' ?>><?= e($k['kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-colors">
                    Filter
                </button>
                <?php if ($search || $filterJenjang || $filterKelas): ?>
                    <a href="<?= url('kelola-siswa/buku-induk') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
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
                        <th class="px-4 py-3">No. Induk / NISN</th>
                        <th class="px-4 py-3">Nama Lengkap & NIK</th>
                        <th class="px-4 py-3 text-center">L/P</th>
                        <th class="px-4 py-3">Tempat, Tgl Lahir</th>
                        <th class="px-4 py-3">Jenjang / Kelas</th>
                        <th class="px-4 py-3">Nama Orang Tua</th>
                        <th class="px-4 py-3">Alamat Domisili</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($siswaList)): ?>
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                            <p class="text-sm font-semibold text-slate-600">Tidak ada data buku induk siswa yang cocok</p>
                            <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter satuan jenjang.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = $offset + 1; foreach ($siswaList as $s): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-3.5 text-center font-semibold text-slate-400"><?= $no++ ?></td>
                        <td class="px-4 py-3.5">
                            <div class="font-extrabold text-slate-900"><?= e($s['nis'] ?: '-') ?></div>
                            <div class="text-[11px] text-indigo-600 font-mono">NISN: <?= e($s['nisn'] ?: '-') ?></div>
                        </td>
                        <td class="px-4 py-3.5">
                            <a href="<?= url('kelola-siswa/buku-induk/' . $s['id']) ?>" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors">
                                <?= e($s['nama_lengkap'] ?: $s['nama']) ?>
                            </a>
                            <div class="text-[11px] text-slate-500">NIK: <?= e($s['no_nik'] ?: '-') ?></div>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <?php if ($s['jenis_kelamin'] === 'L' || $s['jenis_kelamin'] === 'Laki-Laki'): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">L</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-pink-50 text-pink-700">P</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-medium text-slate-800"><?= e($s['tempat_lahir'] ?: '-') ?></div>
                            <div class="text-[11px] text-slate-500">
                                <?= $s['tgl_lahir'] ? date('d M Y', strtotime($s['tgl_lahir'])) : '-' ?>
                                <?= !empty($s['umur']) ? "({$s['umur']} thn)" : '' ?>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 mr-1">
                                <?= e($s['jenjang'] ?: 'SD') ?>
                            </span>
                            <span class="font-semibold text-slate-800"><?= e($s['kelas'] ?: '-') ?></span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="text-slate-800 font-medium">A: <?= e($s['nama_ayah'] ?: '-') ?></div>
                            <div class="text-slate-500 text-[11px]">I: <?= e($s['nama_ibu'] ?: '-') ?></div>
                        </td>
                        <td class="px-4 py-3.5 max-w-[180px] truncate" title="<?= e($s['alamat']) ?>">
                            <span class="text-slate-600 text-xs"><?= e($s['alamat'] ?: '-') ?></span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <?php if (!empty($s['is_active'])): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">Aktif</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200/60"><?= e($s['status_siswa'] ?: 'Keluar') ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="<?= url('kelola-siswa/buku-induk/' . $s['id']) ?>" class="p-1.5 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors" title="Lembar Buku Induk">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                </a>
                                <a href="<?= url('kelola-siswa/buku-induk/' . $s['id'] . '/cetak') ?>" target="_blank" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors" title="Cetak Lembar Buku Induk">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
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
        <div class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-slate-100 gap-3">
            <p class="text-xs text-slate-500">
                Menampilkan data <strong><?= $offset + 1 ?></strong> s/d <strong><?= min($total, $offset + $limit) ?></strong> dari total <strong><?= number_format($total) ?></strong> siswa
            </p>
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="<?= url('kelola-siswa/buku-induk?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-medium">← Sebelumnya</a>
                <?php endif; ?>
                <span class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold"><?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= url('kelola-siswa/buku-induk?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-medium">Berikutnya →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>
