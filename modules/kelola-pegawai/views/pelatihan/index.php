<?php
/**
 * Riwayat Pelatihan & Diklat Pegawai / Guru - Index View
 */
?>
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-primary-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                    </svg>
                </div>
                <span>Riwayat Pelatihan & Diklat Pegawai</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Pencatatan rekam jejak pengembangan kompetensi, workshop, bimtek, seminar, dan sertifikasi guru & pegawai.
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="<?= url('kelola-pegawai/pelatihan/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Tambah Pelatihan Baru</span>
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Kegiatan -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pelatihan</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-0.5"><?= number_format($stats['total'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Total Jam Pelajaran (JP) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Jam (JP)</p>
                <h3 class="text-2xl font-bold text-emerald-600 mt-0.5"><?= number_format($stats['total_jp'] ?? 0) ?> <span class="text-xs font-normal text-slate-400">JP</span></h3>
            </div>
        </div>

        <!-- Sertifikasi Keahlian / Profesi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sertifikasi Profesi</p>
                <h3 class="text-2xl font-bold text-purple-600 mt-0.5"><?= number_format($stats['sertifikasi'] ?? 0) ?></h3>
            </div>
        </div>

        <!-- Diklat & Bimtek / Workshop -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Diklat & Workshop</p>
                <h3 class="text-2xl font-bold text-amber-600 mt-0.5"><?= number_format($stats['diklat_workshop'] ?? 0) ?></h3>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
        <form action="<?= url('kelola-pegawai/pelatihan') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="search" value="<?= e($search ?? '') ?>" placeholder="Cari nama pelatihan, materi, penyelenggara, atau pegawai..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-indigo-500 transition-colors">
            </div>

            <!-- Filter Pegawai -->
            <div>
                <select name="pegawai_id" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                    <option value="">Semua Pegawai</option>
                    <?php foreach ($pegawaiList as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($filterPegawai == $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Jenis Pelatihan -->
            <div>
                <select name="jenis" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                    <option value="">Semua Jenis Pelatihan</option>
                    <option value="Diklat Fungsional" <?= ($filterJenis === 'Diklat Fungsional') ? 'selected' : '' ?>>Diklat Fungsional</option>
                    <option value="Bimtek & Workshop" <?= ($filterJenis === 'Bimtek & Workshop') ? 'selected' : '' ?>>Bimtek & Workshop</option>
                    <option value="Pelatihan Teknis/Manajerial" <?= ($filterJenis === 'Pelatihan Teknis/Manajerial') ? 'selected' : '' ?>>Pelatihan Teknis/Manajerial</option>
                    <option value="Seminar / Webinar" <?= ($filterJenis === 'Seminar / Webinar') ? 'selected' : '' ?>>Seminar / Webinar</option>
                    <option value="Sertifikasi Keahlian / Profesi" <?= ($filterJenis === 'Sertifikasi Keahlian / Profesi') ? 'selected' : '' ?>>Sertifikasi Keahlian / Profesi</option>
                    <option value="Kursus / Pelatihan Mandiri" <?= ($filterJenis === 'Kursus / Pelatihan Mandiri') ? 'selected' : '' ?>>Kursus / Pelatihan Mandiri</option>
                    <option value="In House Training" <?= ($filterJenis === 'In House Training') ? 'selected' : '' ?>>In House Training</option>
                </select>
            </div>

            <!-- Filter Tahun & Submit -->
            <div class="flex items-center gap-2">
                <select name="tahun" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs sm:text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                    <option value="">Semua Tahun</option>
                    <?php for ($y = date('Y'); $y >= 2018; $y--): ?>
                        <option value="<?= $y ?>" <?= ($filterTahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>

                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm transition-colors shrink-0">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($filterPegawai) || !empty($filterJenis) || !empty($filterTahun)): ?>
                    <a href="<?= url('kelola-pegawai/pelatihan') ?>" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors" title="Reset Filter">
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
                        <th class="py-3.5 px-4">Nama Pelatihan / Diklat</th>
                        <th class="py-3.5 px-4">Pegawai / Guru</th>
                        <th class="py-3.5 px-4 text-center">Jenis & Peran</th>
                        <th class="py-3.5 px-4">Penyelenggara & Lokasi</th>
                        <th class="py-3.5 px-4 text-center">Durasi & Waktu</th>
                        <th class="py-3.5 px-4 text-center">Dokumen</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($pelatihanList)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                                    </svg>
                                </div>
                                <p class="font-semibold text-slate-600">Belum ada riwayat pelatihan yang tercatat.</p>
                                <p class="text-xs text-slate-400 mt-1">Catat kegiatan workshop, bimtek, diklat fungsional, dan sertifikasi pegawai.</p>
                                <a href="<?= url('kelola-pegawai/pelatihan/create') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 bg-indigo-50 text-indigo-700 font-bold rounded-xl text-xs hover:bg-indigo-100 transition-colors">
                                    + Tambah Pelatihan Baru
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = $offset + 1; foreach ($pelatihanList as $row): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                <td class="py-3.5 px-4 text-center text-slate-400 text-xs"><?= $no++ ?></td>
                                
                                <!-- Nama Pelatihan -->
                                <td class="py-3.5 px-4">
                                    <p class="font-bold text-slate-900 text-sm"><?= e($row['nama_pelatihan']) ?></p>
                                    <?php if (!empty($row['nomor_sertifikat'])): ?>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">Sertifikat: <?= e($row['nomor_sertifikat']) ?></p>
                                    <?php endif; ?>
                                </td>

                                <!-- Pegawai Info -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden border border-indigo-200">
                                            <?php if (!empty($row['foto_pegawai'])): ?>
                                                <img src="<?= url(ltrim($row['foto_pegawai'], '/')) ?>" alt="Foto" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <?= strtoupper(substr($row['nama_pegawai'] ?? 'P', 0, 2)) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= url('kelola-pegawai/pelatihan/pegawai/' . $row['pegawai_id']) ?>" class="font-bold text-slate-800 hover:text-indigo-600 transition-colors text-xs flex items-center gap-1">
                                                <span><?= e($row['nama_pegawai']) ?><?= !empty($row['gelar_pegawai']) ? ', ' . e($row['gelar_pegawai']) : '' ?></span>
                                            </a>
                                            <p class="text-[10.5px] text-slate-400"><?= e($row['unit_pegawai'] ?: 'Yayasan') ?></p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Jenis & Peran -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80">
                                        <?= e($row['jenis_pelatihan']) ?>
                                    </span>
                                    <p class="text-[11px] text-slate-500 font-medium mt-1">Peran: <span class="font-bold text-slate-700"><?= e($row['peran']) ?></span></p>
                                </td>

                                <!-- Penyelenggara & Lokasi -->
                                <td class="py-3.5 px-4">
                                    <p class="font-semibold text-slate-800 text-xs"><?= e($row['penyelenggara']) ?></p>
                                    <?php if (!empty($row['tempat'])): ?>
                                        <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                            <span>📍</span> <span><?= e($row['tempat']) ?></span>
                                        </p>
                                    <?php endif; ?>
                                </td>

                                <!-- Durasi & Waktu -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (!empty($row['jumlah_jam']) && $row['jumlah_jam'] > 0): ?>
                                        <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded-lg text-xs border border-emerald-200">
                                            <?= $row['jumlah_jam'] ?> JP
                                        </span>
                                    <?php endif; ?>
                                    <p class="text-[11px] text-slate-500 mt-1">
                                        <?= date('d/m/Y', strtotime($row['tanggal_mulai'])) ?>
                                        <?= !empty($row['tanggal_selesai']) ? ' - ' . date('d/m/Y', strtotime($row['tanggal_selesai'])) : '' ?>
                                    </p>
                                </td>

                                <!-- Dokumen & Foto -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <?php if (!empty($row['file_sertifikat'])): ?>
                                            <a href="<?= url(ltrim($row['file_sertifikat'], '/')) ?>" target="_blank" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs transition-colors" title="Lihat Sertifikat / STTPL">
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
                                        <a href="<?= url('kelola-pegawai/pelatihan/pegawai/' . $row['pegawai_id']) ?>" class="p-1.5 bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-700 rounded-lg transition-colors" title="Portofolio Pengembangan Diri">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>

                                        <!-- Edit -->
                                        <a href="<?= url('kelola-pegawai/pelatihan/edit/' . $row['id']) ?>" class="p-1.5 bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-700 rounded-lg transition-colors" title="Edit Pelatihan">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="<?= url('kelola-pegawai/pelatihan/delete/' . $row['id']) ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat pelatihan ini?');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" class="p-1.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-700 rounded-lg transition-colors" title="Hapus Pelatihan">
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
                        <a href="<?= url('kelola-pegawai/pelatihan?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($filterPegawai ? '&pegawai_id=' . $filterPegawai : '') . ($filterJenis ? '&jenis=' . urlencode($filterJenis) : '') . ($filterTahun ? '&tahun=' . $filterTahun : '')) ?>" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold <?= $page == $i ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?> transition-colors">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>
