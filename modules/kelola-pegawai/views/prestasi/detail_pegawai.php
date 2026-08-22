<?php
/**
 * Prestasi & Penghargaan Pegawai / Guru - Detail Portofolio Pegawai
 */
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="<?= url('kelola-pegawai/prestasi') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-amber-600 mb-2 transition-colors">
                ← Kembali ke Daftar Prestasi
            </a>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight">
                Portofolio & Rekam Jejak Prestasi
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-pegawai/prestasi/create?pegawai_id=' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-lg shadow-amber-500/25 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Tambah Prestasi</span>
            </a>
            <a href="<?= url('kelola-pegawai/edit/' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition-colors">
                Profil Pegawai
            </a>
        </div>
    </div>

    <!-- Employee Hero Card -->
    <div class="bg-gradient-to-br from-amber-600 via-yellow-600 to-amber-700 text-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-amber-600/10 flex flex-col sm:flex-row items-center sm:items-start gap-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white/20 p-1 border-2 border-white/30 shadow-2xl shrink-0 overflow-hidden">
            <?php if (!empty($pegawai['foto'])): ?>
                <img src="<?= url(ltrim($pegawai['foto'], '/')) ?>" alt="Foto" class="w-full h-full object-cover rounded-xl">
            <?php else: ?>
                <div class="w-full h-full bg-amber-800 flex items-center justify-center text-3xl font-extrabold text-white rounded-xl">
                    <?= strtoupper(substr($pegawai['nama'], 0, 2)) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex-1 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-1.5">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">
                    <?= e($pegawai['nama']) ?><?= !empty($pegawai['gelar']) ? ', ' . e($pegawai['gelar']) : '' ?>
                </h2>
                <span class="px-2.5 py-0.5 bg-white/20 text-white border border-white/30 text-xs font-semibold rounded-full">
                    🏆 <?= count($prestasiList) ?> Prestasi Tercatat
                </span>
            </div>

            <p class="text-amber-100 text-sm font-medium">
                <?= e($pegawai['jabatan'] ?: 'Guru / Staf') ?> • <?= e($pegawai['unit_tugas'] ?: 'Yayasan Bina Insan Paripurna') ?>
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-5 border-t border-white/20 text-xs">
                <div>
                    <span class="text-amber-200 block mb-0.5">NIY Pegawai</span>
                    <span class="font-bold text-white"><?= e($pegawai['niy'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-amber-200 block mb-0.5">NIK KTP</span>
                    <span class="font-bold text-white"><?= e($pegawai['nik'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-amber-200 block mb-0.5">Tingkat Nasional</span>
                    <span class="font-bold text-white">
                        <?= count(array_filter($prestasiList, fn($p) => in_array($p['tingkat'], ['Nasional', 'Internasional']))) ?> Prestasi
                    </span>
                </div>
                <div>
                    <span class="text-amber-200 block mb-0.5">Tingkat Provinsi</span>
                    <span class="font-bold text-white">
                        <?= count(array_filter($prestasiList, fn($p) => $p['tingkat'] === 'Provinsi')) ?> Prestasi
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Prestasi Grid Cards -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" />
            </svg>
            <span>Daftar Penghargaan & Piagam Kejuaraan</span>
        </h3>

        <?php if (empty($prestasiList)): ?>
            <div class="text-center py-12">
                <p class="text-slate-400 text-sm">Belum ada data prestasi yang tercatat untuk pegawai ini.</p>
                <a href="<?= url('kelola-pegawai/prestasi/create?pegawai_id=' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 mt-4 bg-amber-500 text-white font-bold rounded-xl text-xs hover:bg-amber-600 transition-colors">
                    + Tambah Prestasi Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($prestasiList as $p): ?>
                    <div class="bg-slate-50 hover:bg-slate-100/80 transition-all rounded-2xl p-5 border border-slate-200/80 flex flex-col justify-between group">
                        <div>
                            <!-- Top Bar Badge -->
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-full text-xs border border-amber-200">
                                        🏆 <?= e($p['peringkat']) ?>
                                    </span>
                                    <span class="px-2 py-0.5 bg-purple-50 text-purple-700 font-medium rounded-lg text-xs">
                                        <?= e($p['tingkat']) ?>
                                    </span>
                                </div>
                                <span class="text-xs font-bold text-slate-400">
                                    <?= e($p['tahun']) ?>
                                </span>
                            </div>

                            <!-- Judul & Kategori -->
                            <h4 class="font-bold text-slate-900 text-base mb-1 group-hover:text-amber-700 transition-colors">
                                <?= e($p['nama_prestasi']) ?>
                            </h4>
                            <p class="text-xs text-slate-500 mb-3">
                                Kategori: <span class="font-semibold text-slate-700"><?= e($p['kategori']) ?></span>
                            </p>

                            <!-- Penyelenggara -->
                            <div class="p-3 bg-white rounded-xl border border-slate-200 text-xs space-y-1 mb-3">
                                <p class="text-slate-600"><strong class="text-slate-800">Penyelenggara:</strong> <?= e($p['penyelenggara']) ?></p>
                                <?php if (!empty($p['tanggal_peroleh'])): ?>
                                    <p class="text-slate-500"><strong class="text-slate-700">Tanggal:</strong> <?= date('d M Y', strtotime($p['tanggal_peroleh'])) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($p['nomor_sertifikat'])): ?>
                                    <p class="text-slate-500 font-mono text-[11px]"><strong class="text-slate-700">No Sertifikat:</strong> <?= e($p['nomor_sertifikat']) ?></p>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($p['keterangan'])): ?>
                                <p class="text-xs text-slate-600 italic mb-3">
                                    "<?= nl2br(e($p['keterangan'])) ?>"
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Footer Actions & Media -->
                        <div class="flex items-center justify-between pt-3 border-t border-slate-200/80 text-xs">
                            <div class="flex items-center gap-1.5">
                                <?php if (!empty($p['file_sertifikat'])): ?>
                                    <a href="<?= url(ltrim($p['file_sertifikat'], '/')) ?>" target="_blank" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg font-bold flex items-center gap-1 transition-colors">
                                        📜 Sertifikat
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($p['foto_dokumentasi'])): ?>
                                    <a href="<?= url(ltrim($p['foto_dokumentasi'], '/')) ?>" target="_blank" class="p-1.5 bg-sky-50 hover:bg-sky-100 text-sky-700 rounded-lg font-bold flex items-center gap-1 transition-colors">
                                        📷 Foto
                                    </a>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-1">
                                <a href="<?= url('kelola-pegawai/prestasi/edit/' . $p['id']) ?>" class="p-1.5 bg-white hover:bg-amber-50 text-slate-600 hover:text-amber-700 rounded-lg border border-slate-200 transition-colors" title="Edit">
                                    ✏️
                                </a>
                                <form action="<?= url('kelola-pegawai/prestasi/delete/' . $p['id']) ?>" method="POST" onsubmit="return confirm('Hapus data prestasi ini?');" class="inline">
                                    <?= CSRF::field() ?>
                                    <button type="submit" class="p-1.5 bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-700 rounded-lg border border-slate-200 transition-colors" title="Hapus">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
