<?php
/**
 * Riwayat Pelatihan & Diklat Pegawai / Guru - Detail Portofolio Pegawai
 */
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="<?= url('kelola-pegawai/pelatihan') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-indigo-600 mb-2 transition-colors">
                ← Kembali ke Daftar Pelatihan
            </a>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight">
                Portofolio Pengembangan Diri & Diklat
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-pegawai/pelatihan/create?pegawai_id=' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-indigo-600/25 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Tambah Pelatihan</span>
            </a>
            <a href="<?= url('kelola-pegawai/edit/' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition-colors">
                Profil Pegawai
            </a>
        </div>
    </div>

    <!-- Employee Hero Card -->
    <div class="bg-gradient-to-br from-indigo-900 via-primary-900 to-indigo-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-indigo-950/10 flex flex-col sm:flex-row items-center sm:items-start gap-6 relative overflow-hidden border border-indigo-800/40">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white/10 p-1 border-2 border-white/20 shadow-2xl shrink-0 overflow-hidden">
            <?php if (!empty($pegawai['foto'])): ?>
                <img src="<?= url(ltrim($pegawai['foto'], '/')) ?>" alt="Foto" class="w-full h-full object-cover rounded-xl">
            <?php else: ?>
                <div class="w-full h-full bg-indigo-800 flex items-center justify-center text-3xl font-extrabold text-white rounded-xl">
                    <?= strtoupper(substr($pegawai['nama'], 0, 2)) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex-1 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-1.5">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">
                    <?= e($pegawai['nama']) ?><?= !empty($pegawai['gelar']) ? ', ' . e($pegawai['gelar']) : '' ?>
                </h2>
                <span class="px-2.5 py-0.5 bg-indigo-500/30 text-indigo-200 border border-indigo-500/40 text-xs font-semibold rounded-full">
                    📚 <?= count($pelatihanList) ?> Kegiatan Pelatihan
                </span>
            </div>

            <p class="text-indigo-200 text-sm font-medium">
                <?= e($pegawai['jabatan'] ?: 'Guru / Staf') ?> • <?= e($pegawai['unit_tugas'] ?: 'Yayasan Bina Insan Paripurna') ?>
            </p>

            <?php 
                $totalJP = array_sum(array_column($pelatihanList, 'jumlah_jam'));
                $sertifikasiCount = count(array_filter($pelatihanList, fn($p) => $p['jenis_pelatihan'] === 'Sertifikasi Keahlian / Profesi'));
            ?>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-5 border-t border-indigo-800/60 text-xs">
                <div>
                    <span class="text-indigo-300 block mb-0.5">NIY Pegawai</span>
                    <span class="font-bold text-white"><?= e($pegawai['niy'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-indigo-300 block mb-0.5">Total Jam Diklat</span>
                    <span class="font-bold text-emerald-300"><?= $totalJP ?> JP</span>
                </div>
                <div>
                    <span class="text-indigo-300 block mb-0.5">Sertifikasi Profesi</span>
                    <span class="font-bold text-amber-300"><?= $sertifikasiCount ?> Sertifikat</span>
                </div>
                <div>
                    <span class="text-indigo-300 block mb-0.5">Status Pegawai</span>
                    <span class="font-bold text-white"><?= $pegawai['is_active'] ? 'Aktif' : 'Non-Aktif' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pelatihan Cards List -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
            </svg>
            <span>Daftar Pelatihan, Workshop, & Sertifikasi</span>
        </h3>

        <?php if (empty($pelatihanList)): ?>
            <div class="text-center py-12">
                <p class="text-slate-400 text-sm">Belum ada riwayat pelatihan yang tercatat untuk pegawai ini.</p>
                <a href="<?= url('kelola-pegawai/pelatihan/create?pegawai_id=' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 mt-4 bg-indigo-600 text-white font-bold rounded-xl text-xs hover:bg-indigo-700 transition-colors">
                    + Tambah Pelatihan Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($pelatihanList as $item): ?>
                    <div class="bg-slate-50 hover:bg-slate-100/80 transition-colors rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between">
                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-2">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h4 class="font-bold text-slate-900 text-base"><?= e($item['nama_pelatihan']) ?></h4>
                                        <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 rounded-full text-xs font-semibold">
                                            <?= e($item['jenis_pelatihan']) ?>
                                        </span>
                                        <?php if (!empty($item['jumlah_jam']) && $item['jumlah_jam'] > 0): ?>
                                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold">
                                                <?= $item['jumlah_jam'] ?> JP
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-slate-500">
                                        Penyelenggara: <span class="font-semibold text-slate-700"><?= e($item['penyelenggara']) ?></span>
                                        <?php if (!empty($item['tempat'])): ?>
                                            • Lokasi: <span class="text-slate-600"><?= e($item['tempat']) ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <?php if (!empty($item['file_sertifikat'])): ?>
                                        <a href="<?= url(ltrim($item['file_sertifikat'], '/')) ?>" target="_blank" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors">
                                            📜 Unduh Sertifikat
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($item['foto_dokumentasi'])): ?>
                                        <a href="<?= url(ltrim($item['foto_dokumentasi'], '/')) ?>" target="_blank" class="p-2 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors">
                                            📷 Foto
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= url('kelola-pegawai/pelatihan/edit/' . $item['id']) ?>" class="p-2 bg-white hover:bg-amber-50 text-slate-600 hover:text-amber-700 rounded-lg border border-slate-200 text-xs transition-colors" title="Edit">
                                        ✏️
                                    </a>
                                    <form action="<?= url('kelola-pegawai/pelatihan/delete/' . $item['id']) ?>" method="POST" onsubmit="return confirm('Hapus riwayat pelatihan ini?');" class="inline">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="p-2 bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-700 rounded-lg border border-slate-200 text-xs transition-colors" title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-200/60 text-xs text-slate-600">
                                <div>
                                    <span class="text-slate-400 block mb-0.5">Waktu Pelaksanaan:</span>
                                    <p class="font-semibold text-slate-800">
                                        <?= date('d M Y', strtotime($item['tanggal_mulai'])) ?>
                                        <?= !empty($item['tanggal_selesai']) ? ' s/d ' . date('d M Y', strtotime($item['tanggal_selesai'])) : '' ?>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-slate-400 block mb-0.5">Peran:</span>
                                    <p class="font-semibold text-slate-800"><?= e($item['peran']) ?></p>
                                </div>
                                <div>
                                    <span class="text-slate-400 block mb-0.5">No Sertifikat:</span>
                                    <p class="font-semibold text-slate-800 font-mono"><?= e($item['nomor_sertifikat'] ?: '-') ?></p>
                                </div>
                            </div>

                            <?php if (!empty($item['keterangan'])): ?>
                                <div class="mt-3 p-2.5 bg-white rounded-xl border border-slate-200 text-xs text-slate-600">
                                    <span class="font-bold text-slate-700">Materi / Catatan:</span> <?= nl2br(e($item['keterangan'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
