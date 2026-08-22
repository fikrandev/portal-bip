<?php
/**
 * Riwayat Karir Pegawai & Guru - Index View
 * Menampilkan seluruh riwayat karir (otomatis dari penugasan SK maupun manual)
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                </div>
                <span>Riwayat Karir & Penugasan Pegawai</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Catatan komprehensif perjalanan karir guru & staf — tersinkron otomatis dari SK penugasan dan mendukung input manual.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="<?= url('kelola-pegawai/penugasan') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-xl text-sm border border-slate-200 shadow-sm transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V8.25ZM6.75 21v-3.375c0-.621.504-1.125 1.125-1.125H22.5" />
                </svg>
                <span>Kelola SK Penugasan</span>
            </a>

            <a href="<?= url('kelola-pegawai/karir/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-bold rounded-xl text-sm shadow-lg shadow-primary-500/25 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Tambah Riwayat Manual</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Riwayat -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Riwayat Karir</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-0.5"><?= number_format($stats['total'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Posisi Aktif -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Menjabat / Aktif</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-0.5"><?= number_format($stats['aktif'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Otomatis SK -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Otomatis dari SK</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-0.5"><?= number_format($stats['otomatis'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Input Manual -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Input Manual</p>
                <h3 class="text-2xl font-bold text-purple-600 mt-0.5"><?= number_format($stats['manual'] ?? 0) ?></h3>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <form action="<?= url('kelola-pegawai/karir') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Cari nama pegawai, NIY, jabatan, atau No SK..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-primary-500 transition-colors">
            </div>

            <!-- Filter Pegawai -->
            <div>
                <select name="pegawai_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-primary-500 transition-colors">
                    <option value="">Semua Pegawai</option>
                    <?php foreach ($pegawaiList as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($filterPegawai == $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Unit Tugas -->
            <div>
                <select name="unit_tugas" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-primary-500 transition-colors">
                    <option value="">Semua Unit Tugas</option>
                    <?php foreach ($unitTugasList as $u): ?>
                        <option value="<?= e($u['nama']) ?>" <?= ($filterUnit === $u['nama']) ? 'selected' : '' ?>><?= e($u['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Sumber / Tipe -->
            <div class="flex items-center gap-2">
                <select name="sumber" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-primary-500 transition-colors">
                    <option value="">Semua Sumber</option>
                    <option value="otomatis" <?= ($filterSumber === 'otomatis') ? 'selected' : '' ?>>🤖 Otomatis (SK)</option>
                    <option value="manual" <?= ($filterSumber === 'manual') ? 'selected' : '' ?>>✍️ Input Manual</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-sm transition-colors shrink-0">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($filterPegawai) || !empty($filterUnit) || !empty($filterSumber)): ?>
                    <a href="<?= url('kelola-pegawai/karir') ?>" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors" title="Reset Filter">
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
                        <th class="py-3.5 px-4">Pegawai</th>
                        <th class="py-3.5 px-4">Jabatan & Unit</th>
                        <th class="py-3.5 px-4">Nomor & Tanggal SK</th>
                        <th class="py-3.5 px-4">Periode Masa Tugas</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Sumber</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($karirList)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                    </svg>
                                </div>
                                <p class="font-semibold text-slate-600">Belum ada riwayat karir ditemukan.</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan riwayat karir manual atau buat penugasan pegawai melalui modul penugasan.</p>
                                <a href="<?= url('kelola-pegawai/karir/create') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 bg-primary-50 text-primary-700 font-bold rounded-xl text-xs hover:bg-primary-100 transition-colors">
                                    + Tambah Riwayat Manual
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($karirList as $row): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                <td class="py-3.5 px-4 text-center text-slate-400 text-xs"><?= $no++ ?></td>
                                
                                <!-- Pegawai Info -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-primary-100 text-primary-700 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden border border-primary-200">
                                            <?php if (!empty($row['foto_pegawai'])): ?>
                                                <img src="<?= url(ltrim($row['foto_pegawai'], '/')) ?>" alt="Foto" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?= strtoupper(substr($row['nama_pegawai'] ?? 'P', 0, 2)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= url('kelola-pegawai/karir/pegawai/' . $row['pegawai_id']) ?>" class="font-bold text-slate-900 hover:text-primary-600 transition-colors text-sm flex items-center gap-1.5">
                                                <span><?= e($row['nama_pegawai']) ?><?= !empty($row['gelar_pegawai']) ? ', ' . e($row['gelar_pegawai']) : '' ?></span>
                                            </a>
                                            <p class="text-[11px] text-slate-400 font-mono">
                                                NIY: <?= e($row['niy_pegawai'] ?: ($row['nik_pegawai'] ?: '-')) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Jabatan & Unit -->
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-800 text-sm"><?= e($row['jabatan']) ?></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[11px] font-medium">
                                            <?= e($row['unit_tugas'] ?: 'Yayasan') ?>
                                        </span>
                                        <span class="text-[11px] text-slate-400">• <?= e($row['tipe_karir'] ?: 'Penugasan') ?></span>
                                    </div>
                                </td>

                                <!-- No & Tanggal SK -->
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($row['no_sk'])): ?>
                                        <p class="font-semibold text-slate-800 text-xs font-mono"><?= e($row['no_sk']) ?></p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">
                                            <?= !empty($row['tanggal_sk']) ? date('d M Y', strtotime($row['tanggal_sk'])) : '-' ?>
                                        </p>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs italic">Tanpa Nomor SK</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Periode Masa Tugas -->
                                <td class="py-3.5 px-4">
                                    <div class="text-xs text-slate-700">
                                        <span class="font-medium text-emerald-700">TMT: <?= !empty($row['tmt_mulai']) ? date('d/m/Y', strtotime($row['tmt_mulai'])) : '-' ?></span>
                                        <span class="text-slate-400 mx-1">s/d</span>
                                        <span class="<?= empty($row['tst_selesai']) ? 'text-blue-600 font-semibold' : 'text-slate-600' ?>">
                                            <?= !empty($row['tst_selesai']) ? date('d/m/Y', strtotime($row['tst_selesai'])) : 'Sekarang' ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($row['tmt_mulai'])): ?>
                                        <?php 
                                            $start = new DateTime($row['tmt_mulai']);
                                            $end = !empty($row['tst_selesai']) ? new DateTime($row['tst_selesai']) : new DateTime();
                                            $diff = $start->diff($end);
                                            $durasiStr = ($diff->y > 0 ? $diff->y . ' thn ' : '') . ($diff->m > 0 ? $diff->m . ' bln' : '');
                                            if (empty($durasiStr)) $durasiStr = '< 1 bulan';
                                        ?>
                                        <p class="text-[10.5px] text-slate-400 mt-0.5">Durasi: <?= $durasiStr ?></p>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($row['status'] === 'Aktif'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-full text-xs font-bold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    <?php elseif ($row['status'] === 'Selesai'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 text-slate-700 border border-slate-200 rounded-full text-xs font-semibold">
                                            Selesai
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200/60 rounded-full text-xs font-semibold">
                                            <?= e($row['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Sumber Data -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (!empty($row['is_otomatis'])): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-50 text-blue-700 rounded-lg text-[11px] font-semibold border border-blue-100" title="Terbuat otomatis dari grup penugasan SK">
                                            <span>🤖 Otomatis SK</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-purple-50 text-purple-700 rounded-lg text-[11px] font-semibold border border-purple-100" title="Diinput manual oleh admin">
                                            <span>✍️ Manual</span>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Timeline Pegawai -->
                                        <a href="<?= url('kelola-pegawai/karir/pegawai/' . $row['pegawai_id']) ?>" class="p-1.5 bg-slate-100 hover:bg-primary-50 text-slate-600 hover:text-primary-700 rounded-lg transition-colors" title="Lihat Riwayat Perjalanan Karir">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </a>

                                        <!-- Berkas SK jika ada -->
                                        <?php if (!empty($row['file_sk'])): ?>
                                            <a href="<?= url(ltrim($row['file_sk'], '/')) ?>" target="_blank" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg transition-colors" title="Buka Berkas SK">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                                </svg>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Edit -->
                                        <a href="<?= url('kelola-pegawai/karir/edit/' . $row['id']) ?>" class="p-1.5 bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-700 rounded-lg transition-colors" title="Edit Riwayat Karir">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="<?= url('kelola-pegawai/karir/delete/' . $row['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data riwayat karir ini?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 rounded-lg transition-colors" title="Hapus Riwayat Karir">
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
                        <a href="<?= url('kelola-pegawai/karir?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($filterPegawai ? '&pegawai_id=' . $filterPegawai : '') . ($filterUnit ? '&unit_tugas=' . urlencode($filterUnit) : '') . ($filterSumber ? '&sumber=' . $filterSumber : '')) ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold <?= $page == $i ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?> transition-colors">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
