<?php
/**
 * Prestasi & Penghargaan Pegawai / Guru - Index View
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/25">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" />
                    </svg>
                </div>
                <span>Prestasi & Penghargaan Pegawai</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Apresiasi dan rekam jejak capaian unggul, kejuaraan, sertifikasi, serta penghargaan guru & staf Yayasan BIP.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="<?= url('kelola-pegawai/prestasi/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-600 hover:from-amber-600 hover:to-yellow-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-amber-500/25 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Tambah Prestasi Baru</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Prestasi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Prestasi</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-0.5"><?= number_format($stats['total'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Nasional & Internasional -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nasional & Global</p>
                <h3 class="text-2xl font-bold text-purple-600 mt-0.5"><?= number_format($stats['nasional_intl'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Tingkat Provinsi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tingkat Provinsi</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-0.5"><?= number_format($stats['provinsi'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Tingkat Kota / Daerah -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kota / Kab / Internal</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-0.5"><?= number_format($stats['kota_kab'] ?? 0) ?></h3>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <form action="<?= url('kelola-pegawai/prestasi') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Cari nama prestasi, pegawai, atau penyelenggara..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-amber-500 transition-colors">
            </div>

            <!-- Filter Pegawai -->
            <div>
                <select name="pegawai_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-amber-500 transition-colors">
                    <option value="">Semua Pegawai</option>
                    <?php foreach ($pegawaiList as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($filterPegawai == $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Tingkat -->
            <div>
                <select name="tingkat" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-amber-500 transition-colors">
                    <option value="">Semua Tingkat</option>
                    <option value="Sekolah/Internal" <?= ($filterTingkat === 'Sekolah/Internal') ? 'selected' : '' ?>>Sekolah / Internal</option>
                    <option value="Kecamatan" <?= ($filterTingkat === 'Kecamatan') ? 'selected' : '' ?>>Kecamatan</option>
                    <option value="Kota/Kabupaten" <?= ($filterTingkat === 'Kota/Kabupaten') ? 'selected' : '' ?>>Kota / Kabupaten</option>
                    <option value="Provinsi" <?= ($filterTingkat === 'Provinsi') ? 'selected' : '' ?>>Provinsi</option>
                    <option value="Nasional" <?= ($filterTingkat === 'Nasional') ? 'selected' : '' ?>>Nasional</option>
                    <option value="Internasional" <?= ($filterTingkat === 'Internasional') ? 'selected' : '' ?>>Internasional</option>
                </select>
            </div>

            <!-- Filter Kategori / Submit -->
            <div class="flex items-center gap-2">
                <select name="tahun" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-amber-500 transition-colors">
                    <option value="">Semua Tahun</option>
                    <?php for ($y = date('Y'); $y >= 2018; $y--): ?>
                        <option value="<?= $y ?>" <?= ($filterTahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>

                <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-sm transition-colors shrink-0">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($filterPegawai) || !empty($filterTingkat) || !empty($filterTahun)): ?>
                    <a href="<?= url('kelola-pegawai/prestasi') ?>" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors" title="Reset Filter">
                        ✕
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4 text-center w-12">No</th>
                        <th class="py-3.5 px-4">Nama Prestasi & Penghargaan</th>
                        <th class="py-3.5 px-4">Pegawai / Guru</th>
                        <th class="py-3.5 px-4 text-center">Peringkat & Kategori</th>
                        <th class="py-3.5 px-4 text-center">Tingkat</th>
                        <th class="py-3.5 px-4">Penyelenggara & Tahun</th>
                        <th class="py-3.5 px-4 text-center">Dokumen</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($prestasiList)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" />
                                    </svg>
                                </div>
                                <p class="font-semibold text-slate-600">Belum ada data prestasi yang tercatat.</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan catatan kejuaraan, sertifikasi, atau penghargaan yang diraih guru & pegawai.</p>
                                <a href="<?= url('kelola-pegawai/prestasi/create') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 bg-amber-50 text-amber-700 font-bold rounded-xl text-xs hover:bg-amber-100 transition-colors">
                                    + Tambah Prestasi Baru
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($prestasiList as $row): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                <td class="py-3.5 px-4 text-center text-slate-400 text-xs"><?= $no++ ?></td>
                                
                                <!-- Nama Prestasi -->
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900 text-sm"><?= e($row['nama_prestasi']) ?></p>
                                    <?php if (!empty($row['nomor_sertifikat'])): ?>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">No: <?= e($row['nomor_sertifikat']) ?></p>
                                    <?php endif; ?>
                                </td>

                                <!-- Pegawai Info -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden border border-amber-200">
                                            <?php if (!empty($row['foto_pegawai'])): ?>
                                                <img src="<?= url(ltrim($row['foto_pegawai'], '/')) ?>" alt="Foto" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?= strtoupper(substr($row['nama_pegawai'] ?? 'P', 0, 2)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= url('kelola-pegawai/prestasi/pegawai/' . $row['pegawai_id']) ?>" class="font-bold text-slate-800 hover:text-amber-600 transition-colors text-xs flex items-center gap-1">
                                                <span><?= e($row['nama_pegawai']) ?><?= !empty($row['gelar_pegawai']) ? ', ' . e($row['gelar_pegawai']) : '' ?></span>
                                            </a>
                                            <p class="text-[10.5px] text-slate-400"><?= e($row['unit_pegawai'] ?: 'Yayasan') ?></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Peringkat & Kategori -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php 
                                        $pBadge = 'bg-amber-50 text-amber-700 border-amber-200/80';
                                        $trophy = '🏆';
                                        if (stripos($row['peringkat'], '1') !== false || stripos($row['peringkat'], 'emas') !== false || stripos($row['peringkat'], 'pertama') !== false) {
                                            $pBadge = 'bg-amber-100 text-amber-800 border-amber-300 font-bold';
                                            $trophy = '🥇';
                                        } elseif (stripos($row['peringkat'], '2') !== false || stripos($row['peringkat'], 'perak') !== false || stripos($row['peringkat'], 'kedua') !== false) {
                                            $pBadge = 'bg-slate-100 text-slate-700 border-slate-300 font-bold';
                                            $trophy = '🥈';
                                        } elseif (stripos($row['peringkat'], '3') !== false || stripos($row['peringkat'], 'perunggu') !== false || stripos($row['peringkat'], 'ketiga') !== false) {
                                            $pBadge = 'bg-amber-50 text-amber-900 border-amber-200 font-bold';
                                            $trophy = '🥉';
                                        }
                                    ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs border <?= $pBadge ?>">
                                        <span><?= $trophy ?></span>
                                        <span><?= e($row['peringkat']) ?></span>
                                    </span>
                                    <p class="text-[10.5px] text-slate-400 mt-1"><?= e($row['kategori']) ?></p>
                                </td>

                                <!-- Tingkat Prestasi -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($row['tingkat'] === 'Internasional'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold">
                                            🌍 Internasional
                                        </span>
                                    <?php elseif ($row['tingkat'] === 'Nasional'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg text-xs font-bold">
                                            🇮🇩 Nasional
                                        </span>
                                    <?php elseif ($row['tingkat'] === 'Provinsi'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-xs font-semibold">
                                            Provinsi
                                        </span>
                                    <?php elseif ($row['tingkat'] === 'Kota/Kabupaten'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold">
                                            Kota/Kabupaten
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-medium">
                                            <?= e($row['tingkat']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Penyelenggara & Tahun -->
                                <td class="py-3.5 px-4">
                                    <p class="font-semibold text-slate-800 text-xs"><?= e($row['penyelenggara']) ?></p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Tahun <span class="font-bold text-slate-700"><?= e($row['tahun']) ?></span>
                                        <?php if (!empty($row['tanggal_peroleh'])): ?>
                                            • <?= date('d M Y', strtotime($row['tanggal_peroleh'])) ?>
                                        <?php endif; ?>
                                    </p>
                                </td>

                                <!-- Dokumen & Foto -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <?php if (!empty($row['file_sertifikat'])): ?>
                                            <a href="<?= url(ltrim($row['file_sertifikat'], '/')) ?>" target="_blank" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs transition-colors" title="Lihat Sertifikat / Piagam">
                                                📜 Sertifikat
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($row['foto_dokumentasi'])): ?>
                                            <a href="<?= url(ltrim($row['foto_dokumentasi'], '/')) ?>" target="_blank" class="p-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg text-xs transition-colors" title="Lihat Foto Dokumentasi">
                                                📷 Foto
                                            </a>
                                        <?php endif; ?>
                                        <?php if (empty($row['file_sertifikat']) && empty($row['foto_dokumentasi'])): ?>
                                            <span class="text-slate-300 text-xs">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Detail Portofolio Pegawai -->
                                        <a href="<?= url('kelola-pegawai/prestasi/pegawai/' . $row['pegawai_id']) ?>" class="p-1.5 bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-700 rounded-lg transition-colors" title="Portofolio Prestasi Pegawai">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="<?= url('kelola-pegawai/prestasi/edit/' . $row['id']) ?>" class="p-1.5 bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-700 rounded-lg transition-colors" title="Edit Prestasi">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="<?= url('kelola-pegawai/prestasi/delete/' . $row['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data prestasi ini?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 rounded-lg transition-colors" title="Hapus Prestasi">
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
        <?php if ($total > $limit): ?>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
                <p class="text-xs text-slate-500">
                    Menampilkan <span class="font-bold text-slate-800"><?= $offset + 1 ?></span> - <span class="font-bold text-slate-800"><?= min($offset + $limit, $total) ?></span> dari <span class="font-bold text-slate-800"><?= $total ?></span> data
                </p>
                <div class="flex items-center gap-1.5">
                    <?php $totalPages = ceil($total / $limit); ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?= url('kelola-pegawai/prestasi?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($filterPegawai ? '&pegawai_id=' . $filterPegawai : '') . ($filterTingkat ? '&tingkat=' . urlencode($filterTingkat) : '') . ($filterTahun ? '&tahun=' . $filterTahun : '')) ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold <?= $page == $i ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?> transition-colors">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
