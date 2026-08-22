<?php
/**
 * Riwayat Karir Pegawai & Guru - Timeline View
 * Menampilkan linimasa perjalanan karir visual seorang pegawai
 */
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Back & Breadcrumb Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="<?= url('kelola-pegawai/karir') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-primary-600 mb-2 transition-colors">
                ← Kembali ke Riwayat Karir
            </a>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight">
                Perjalanan Karir & Riwayat Jabatan
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-pegawai/karir/create?pegawai_id=' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-primary-500/25 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Tambah Riwayat</span>
            </a>
            <a href="<?= url('kelola-pegawai/edit/' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition-colors">
                Profil Pegawai
            </a>
        </div>
    </div>

    <!-- Employee Profile Banner Card -->
    <div class="bg-gradient-to-br from-primary-900 to-primary-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl shadow-primary-950/10 flex flex-col sm:flex-row items-center sm:items-start gap-6 border border-primary-800/50 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-primary-600/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white/10 p-1 border-2 border-white/20 shadow-2xl shrink-0 overflow-hidden">
            <?php if (!empty($pegawai['foto'])): ?>
                <img src="<?= url(ltrim($pegawai['foto'], '/')) ?>" alt="Foto" class="w-full h-full object-cover rounded-xl">
            <?php else: ?>
                <div class="w-full h-full bg-primary-800 flex items-center justify-center text-3xl font-extrabold text-white rounded-xl">
                    <?= strtoupper(substr($pegawai['nama'], 0, 2)) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex-1 text-center sm:text-left">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5 mb-1.5">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight">
                    <?= e($pegawai['nama']) ?><?= !empty($pegawai['gelar']) ? ', ' . e($pegawai['gelar']) : '' ?>
                </h2>
                <span class="px-2.5 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold rounded-full">
                    <?= $pegawai['is_active'] ? 'Pegawai Aktif' : 'Non-Aktif' ?>
                </span>
            </div>

            <p class="text-primary-200 text-sm font-medium">
                <?= e($pegawai['jabatan'] ?: 'Belum Ada Jabatan Aktif') ?> • <?= e($pegawai['unit_tugas'] ?: 'Yayasan') ?>
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5 pt-5 border-t border-primary-800/60 text-xs">
                <div>
                    <span class="text-primary-400 block mb-0.5">NIY Yayasan</span>
                    <span class="font-bold text-white"><?= e($pegawai['niy'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-primary-400 block mb-0.5">NIK KTP</span>
                    <span class="font-bold text-white"><?= e($pegawai['nik'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-primary-400 block mb-0.5">Status Kerja</span>
                    <span class="font-bold text-white"><?= e($pegawai['status_kerja'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-primary-400 block mb-0.5">Total Posisi Karir</span>
                    <span class="font-bold text-amber-300"><?= count($karirList) ?> Riwayat</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Journey -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 sm:p-8">
        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2.5">
            <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <span>Linimasa Perjalanan Karir</span>
        </h3>

        <?php if (empty($karirList)): ?>
            <div class="text-center py-12">
                <p class="text-slate-400 text-sm">Belum ada riwayat karir yang tercatat untuk pegawai ini.</p>
                <a href="<?= url('kelola-pegawai/karir/create?pegawai_id=' . $pegawai['id']) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 mt-4 bg-primary-600 text-white font-bold rounded-xl text-xs hover:bg-primary-700 transition-colors">
                    + Tambah Riwayat Karir Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="relative pl-6 sm:pl-8 border-l-2 border-primary-200 space-y-8 my-4">
                <?php foreach ($karirList as $idx => $item): ?>
                    <div class="relative group">
                        <!-- Dot Indicator on Timeline -->
                        <div class="absolute -left-[31px] sm:-left-[39px] top-1.5 w-6 h-6 rounded-full <?= $item['status'] === 'Aktif' ? 'bg-emerald-500 ring-4 ring-emerald-100 text-white' : 'bg-primary-500 ring-4 ring-primary-100 text-white' ?> flex items-center justify-center shadow">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                        </div>

                        <!-- Card Content -->
                        <div class="bg-slate-50 hover:bg-slate-100/80 transition-colors rounded-2xl p-5 border border-slate-200/80">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <h4 class="text-base font-bold text-slate-900"><?= e($item['jabatan']) ?></h4>
                                        <span class="px-2 py-0.5 bg-primary-100 text-primary-800 rounded text-xs font-semibold">
                                            <?= e($item['unit_tugas'] ?: 'Yayasan') ?>
                                        </span>
                                        <?php if ($item['status'] === 'Aktif'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Sedang Menjabat
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 bg-slate-200 text-slate-700 rounded-full text-xs font-medium">
                                                <?= e($item['status']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <p class="text-xs text-slate-500">
                                        Tipe: <span class="font-semibold text-slate-700"><?= e($item['tipe_karir']) ?></span> • 
                                        <?= !empty($item['is_otomatis']) ? '<span class="text-blue-600 font-semibold">🤖 Otomatis dari SK</span>' : '<span class="text-purple-600 font-semibold">✍️ Input Manual</span>' ?>
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <?php if (!empty($item['file_sk'])): ?>
                                        <a href="<?= url(ltrim($item['file_sk'], '/')) ?>" target="_blank" class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold flex items-center gap-1 transition-colors">
                                            <span>📄 Unduh SK</span>
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= url('kelola-pegawai/karir/edit/' . $item['id']) ?>" class="p-2 bg-white hover:bg-amber-50 text-slate-600 hover:text-amber-700 rounded-lg border border-slate-200 text-xs transition-colors" title="Edit Riwayat">
                                        ✏️
                                    </a>

                                    <form action="<?= url('kelola-pegawai/karir/delete/' . $item['id']) ?>" method="POST" onsubmit="return confirm('Hapus riwayat karir ini?');" class="inline">
                                        <?= CSRF::field() ?>
                                        <button type="submit" class="p-2 bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-700 rounded-lg border border-slate-200 text-xs transition-colors" title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Periode & SK Details -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-200/60 text-xs text-slate-600">
                                <div>
                                    <span class="text-slate-400 block mb-0.5">Masa Penugasan / TMT:</span>
                                    <p class="font-semibold text-slate-800">
                                        <?= !empty($item['tmt_mulai']) ? date('d M Y', strtotime($item['tmt_mulai'])) : '-' ?> 
                                        s/d 
                                        <?= !empty($item['tst_selesai']) ? date('d M Y', strtotime($item['tst_selesai'])) : '<span class="text-emerald-600">Sekarang</span>' ?>
                                    </p>
                                </div>

                                <div>
                                    <span class="text-slate-400 block mb-0.5">Nomor & Tgl SK:</span>
                                    <p class="font-semibold text-slate-800">
                                        <?= e($item['no_sk'] ?: 'Tanpa No SK') ?> 
                                        <?= !empty($item['tanggal_sk']) ? '(' . date('d/m/Y', strtotime($item['tanggal_sk'])) . ')' : '' ?>
                                    </p>
                                </div>

                                <div>
                                    <span class="text-slate-400 block mb-0.5">Pejabat Pengesah:</span>
                                    <p class="font-semibold text-slate-800">
                                        <?= e($item['penandatangan_sk'] ?: '-') ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($item['keterangan'])): ?>
                                <div class="mt-3 p-2.5 bg-white rounded-xl border border-slate-200 text-xs text-slate-600">
                                    <span class="font-bold text-slate-700">Catatan:</span> <?= nl2br(e($item['keterangan'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
