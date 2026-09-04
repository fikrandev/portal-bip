<?php
/**
 * Prestasi Siswa - Portal BIP
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" />
                    </svg>
                </div>
                <span>Prestasi & Penghargaan Siswa</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Pencatatan rekam jejak juara lomba, olimpiade, tahfidz, kejuaraan olahraga, seni budaya, dan penghargaan peserta didik.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-siswa/prestasi/create') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold shadow-sm shadow-amber-600/30 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                <span>Catat Prestasi Baru</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">🏆</div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase">Total Prestasi</p>
                <h4 class="text-xl font-extrabold text-slate-900"><?= number_format($totalPrestasi) ?></h4>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">🌐</div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase">Nasional / Internasional</p>
                <h4 class="text-xl font-extrabold text-blue-600"><?= number_format($totalNasional) ?></h4>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">🏛️</div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase">Provinsi / Kota</p>
                <h4 class="text-xl font-extrabold text-emerald-600"><?= number_format($totalProvinsi) ?></h4>
            </div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">👥</div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase">Siswa Berprestasi</p>
                <h4 class="text-xl font-extrabold text-purple-600"><?= number_format($totalSiswaBerprestasi) ?></h4>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
        <form method="GET" action="<?= url('kelola-siswa/prestasi') ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Cari Siswa / Nama Prestasi</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Ketik nama siswa, nama lomba / kejuaraan..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
            </div>

            <!-- Tingkat -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Tingkat Prestasi</label>
                <select name="tingkat" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                    <option value="">Semua Tingkat</option>
                    <option value="Sekolah/Internal" <?= $filterTingkat === 'Sekolah/Internal' ? 'selected' : '' ?>>Sekolah / Internal</option>
                    <option value="Kecamatan" <?= $filterTingkat === 'Kecamatan' ? 'selected' : '' ?>>Kecamatan</option>
                    <option value="Kota/Kabupaten" <?= $filterTingkat === 'Kota/Kabupaten' ? 'selected' : '' ?>>Kota / Kabupaten</option>
                    <option value="Provinsi" <?= $filterTingkat === 'Provinsi' ? 'selected' : '' ?>>Provinsi</option>
                    <option value="Nasional" <?= $filterTingkat === 'Nasional' ? 'selected' : '' ?>>Nasional</option>
                    <option value="Internasional" <?= $filterTingkat === 'Internasional' ? 'selected' : '' ?>>Internasional</option>
                </select>
            </div>

            <!-- Bidang -->
            <div>
                <label class="block text-[11px] font-bold text-slate-600 mb-1">Bidang Prestasi</label>
                <select name="bidang" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                    <option value="">Semua Bidang</option>
                    <option value="Akademik" <?= $filterBidang === 'Akademik' ? 'selected' : '' ?>>Akademik / Sains</option>
                    <option value="Keagamaan & Tahfidz" <?= $filterBidang === 'Keagamaan & Tahfidz' ? 'selected' : '' ?>>Keagamaan & Tahfidz</option>
                    <option value="Olahraga" <?= $filterBidang === 'Olahraga' ? 'selected' : '' ?>>Olahraga</option>
                    <option value="Seni & Budaya" <?= $filterBidang === 'Seni & Budaya' ? 'selected' : '' ?>>Seni & Budaya</option>
                    <option value="Lainnya" <?= $filterBidang === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold transition-colors">
                    Filter
                </button>
                <?php if ($search || $filterTingkat || $filterBidang): ?>
                    <a href="<?= url('kelola-siswa/prestasi') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
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
                        <th class="px-4 py-3">Nama Prestasi & Juara</th>
                        <th class="px-4 py-3">Bidang & Tingkat</th>
                        <th class="px-4 py-3">Penyelenggara & Tahun</th>
                        <th class="px-4 py-3">Guru Pembimbing</th>
                        <th class="px-4 py-3 text-center">Berkas</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($prestasiList)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3"/></svg>
                            <p class="text-sm font-semibold text-slate-600">Belum ada data prestasi siswa</p>
                            <p class="text-xs text-slate-400 mt-1">Klik tombol "Catat Prestasi Baru" untuk menambahkan riwayat prestasi peserta didik.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = $offset + 1; foreach ($prestasiList as $p): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-3.5 text-center font-semibold text-slate-400"><?= $no++ ?></td>
                        <td class="px-4 py-3.5">
                            <a href="<?= url('kelola-siswa/prestasi/siswa/' . $p['siswa_id']) ?>" class="font-extrabold text-slate-900 hover:text-amber-600 transition-colors">
                                <?= e($p['nama_lengkap'] ?: $p['nama']) ?>
                            </a>
                            <div class="text-[11px] text-slate-500">
                                <span class="font-semibold text-slate-700"><?= e($p['jenjang'] ?: 'SD') ?></span> - Kelas <?= e($p['kelas'] ?: '-') ?> (NISN: <?= e($p['nisn'] ?: '-') ?>)
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-bold text-slate-900"><?= e($p['nama_prestasi']) ?></div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200 mt-0.5">
                                🥇 <?= e($p['peringkat'] ?: 'Juara') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="font-medium text-slate-800"><?= e($p['bidang'] ?: 'Akademik') ?></div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 mt-0.5 inline-block">
                                <?= e($p['tingkat']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="text-slate-800 font-medium"><?= e($p['penyelenggara'] ?: '-') ?></div>
                            <div class="text-slate-500 text-[11px]">Tahun <?= e($p['tahun'] ?: date('Y')) ?></div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="text-slate-700 font-medium"><?= e($p['guru_pendamping'] ?: '-') ?></span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <?php if (!empty($p['file_sertifikat'])): ?>
                                <a href="<?= asset('uploads/prestasi_siswa/' . $p['file_sertifikat']) ?>" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-bold hover:bg-indigo-100 transition-colors">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    Piagam
                                </a>
                            <?php else: ?>
                                <span class="text-slate-300 text-[10px]">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="<?= url('kelola-siswa/prestasi/edit/' . $p['id']) ?>" class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors" title="Edit Prestasi">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                </a>
                                <form action="<?= url('kelola-siswa/prestasi/delete/' . $p['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data prestasi ini?');" class="inline">
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
                Menampilkan data <strong><?= $offset + 1 ?></strong> s/d <strong><?= min($total, $offset + $limit) ?></strong> dari <strong><?= number_format($total) ?></strong> prestasi
            </p>
            <div class="flex items-center gap-1">
                <?php if ($page > 1): ?>
                    <a href="<?= url('kelola-siswa/prestasi?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-medium">← Sebelumnya</a>
                <?php endif; ?>
                <span class="px-3 py-1.5 bg-amber-600 text-white rounded-lg text-xs font-bold"><?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= url('kelola-siswa/prestasi?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-medium">Berikutnya →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>
