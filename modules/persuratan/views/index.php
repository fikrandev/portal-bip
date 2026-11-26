<?php
/**
 * View Utama Modul Persuratan Yayasan & Persuratan Sekolah
 * Portal BIP
 *
 * @var string $scope       'yayasan' | 'sekolah'
 * @var string $activeTab   'dashboard' | 'masuk' | 'keluar' | 'data' | 'pengaturan'
 * @var string $baseRoute
 * @var string $moduleLabel
 * @var array  $units
 * @var array  $stats
 * @var array  $suratList
 * @var array  $jenisList
 * @var array  $jenisAktif
 * @var array  $kopList
 * @var string $activeKopUnit
 * @var array  $filters
 */
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $scope === 'yayasan' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                    <?= $scope === 'yayasan' ? 'Tingkat Yayasan' : 'Unit Sekolah (' . implode(', ', $units) . ')' ?>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Tahun Aktif: <?= (int)($tahunAktif ?? date('Y')) ?> (Jan – Des <?= (int)($tahunAktif ?? date('Y')) ?>)
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight"><?= e($moduleLabel) ?></h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Pengelolaan Surat Masuk, Surat Keluar, Arsip Data Surat, KOP Unit, Jenis &amp; Kode Surat.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="button" onclick="openSuratModal('masuk')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Surat Masuk</span>
            </button>
            <button type="button" onclick="openSuratModal('keluar')"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white text-sm font-bold shadow-sm transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                <span>Surat Keluar</span>
            </button>
        </div>
    </div>

    <?php if ($activeTab === 'dashboard'): ?>
        <!-- ══════════ TAB 1: DASHBOARD ══════════ -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Arsip Surat</span>
                <div class="text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['total_surat'] ?? 0) ?></div>
                <p class="text-xs text-slate-500 mt-1">Bulan ini: <strong><?= number_format($stats['bulan_ini'] ?? 0) ?></strong> surat</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-amber-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Surat Masuk</span>
                <div class="text-3xl font-extrabold text-amber-600 mt-1"><?= number_format($stats['total_masuk'] ?? 0) ?></div>
                <p class="text-xs text-slate-500 mt-1">Perlu tindak lanjut: <strong><?= number_format($stats['masuk_pending'] ?? 0) ?></strong></p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-emerald-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Surat Keluar</span>
                <div class="text-3xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['total_keluar'] ?? 0) ?></div>
                <p class="text-xs text-slate-500 mt-1">Terdaftar resmi &amp; siap cetak</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-sky-200/80 shadow-xs">
                <span class="text-[11px] font-bold text-sky-600 uppercase tracking-wider">Jenis &amp; Kode Surat</span>
                <div class="text-3xl font-extrabold text-sky-600 mt-1"><?= count($jenisAktif) ?></div>
                <p class="text-xs text-slate-500 mt-1"><?= count($units) ?> Unit KOP Aktif</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Rekap Per Unit & Jenis -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-900">Rekap Surat Per Unit &amp; Jenis</h3>
                <?php if (empty($stats['by_unit']) && empty($stats['by_jenis'])): ?>
                    <p class="text-xs text-slate-400 py-6 text-center">Belum ada data surat tercatat.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach (($stats['by_unit'] ?? []) as $bu): ?>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <span class="font-bold text-slate-800"><?= e($bu['unit_nama']) ?></span>
                                <div class="flex items-center gap-3">
                                    <span class="text-amber-700 font-semibold">Masuk: <?= (int)$bu['masuk'] ?></span>
                                    <span class="text-emerald-700 font-semibold">Keluar: <?= (int)$bu['keluar'] ?></span>
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-200 text-slate-800 font-bold"><?= (int)$bu['total'] ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($stats['by_jenis'])): ?>
                        <div class="pt-3 border-t border-slate-100">
                            <p class="text-[11px] font-bold text-slate-400 uppercase mb-2">Berdasarkan Jenis Surat</p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($stats['by_jenis'] as $bj): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-primary-50 text-primary-800 border border-primary-100 text-xs font-semibold">
                                        <span class="font-mono font-bold"><?= e($bj['kode']) ?></span>
                                        <span><?= e($bj['jenis_label']) ?>:</span>
                                        <strong><?= (int)$bj['total'] ?></strong>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Surat Terbaru -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-900">Aktivitas Surat Terbaru</h3>
                    <a href="<?= url($baseRoute . '?tab=data') ?>" class="text-xs font-bold text-primary-600 hover:underline">Lihat Semua &rarr;</a>
                </div>
                <?php if (empty($stats['recent'])): ?>
                    <p class="text-xs text-slate-400 py-6 text-center">Belum ada surat masuk atau keluar.</p>
                <?php else: ?>
                    <div class="divide-y divide-slate-100">
                        <?php foreach ($stats['recent'] as $rc): ?>
                            <div class="py-2.5 flex items-start justify-between gap-3 text-xs">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= $rc['arah'] === 'masuk' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                            <?= $rc['arah'] === 'masuk' ? 'Masuk' : 'Keluar' ?>
                                        </span>
                                        <span class="font-mono font-bold text-slate-700 truncate"><?= e($rc['nomor_surat']) ?></span>
                                    </div>
                                    <p class="font-semibold text-slate-900 mt-1 truncate"><?= e($rc['perihal']) ?></p>
                                    <p class="text-[11px] text-slate-400">
                                        <?= e($rc['unit_nama']) ?> &bull; <?= date('d/m/Y', strtotime($rc['tanggal_surat'])) ?>
                                    </p>
                                </div>
                                <?php if ($rc['arah'] === 'keluar'): ?>
                                    <a href="<?= url('persuratan/cetak/' . (int)$rc['id']) ?>" target="_blank"
                                       class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] shrink-0">
                                        Cetak
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif (in_array($activeTab, ['masuk', 'keluar', 'data'], true)): ?>
        <!-- ══════════ TAB 2, 3, 4: SURAT MASUK / SURAT KELUAR / DATA SURAT ══════════ -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form method="GET" action="<?= url($baseRoute) ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <input type="hidden" name="tab" value="<?= e($activeTab) ?>">
                <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Cari nomor, perihal, pengirim/tujuan..."
                       class="lg:col-span-2 px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm font-medium text-slate-900 placeholder:text-slate-400 outline-none focus:bg-white focus:border-primary-500">

                <?php if ($activeTab === 'data'): ?>
                    <select name="arah" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50 focus:bg-white font-medium text-slate-900 outline-none focus:border-primary-500">
                        <option value="">Semua Arah (Masuk &amp; Keluar)</option>
                        <option value="masuk" <?= $filters['arah'] === 'masuk' ? 'selected' : '' ?>>Surat Masuk</option>
                        <option value="keluar" <?= $filters['arah'] === 'keluar' ? 'selected' : '' ?>>Surat Keluar</option>
                    </select>
                <?php endif; ?>

                <select name="tahun" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50 focus:bg-white font-semibold text-slate-900 outline-none focus:border-primary-500">
                    <?php
                    $curThn = (int)($tahunAktif ?? date('Y'));
                    $selThn = (string)($filters['tahun'] ?? $curThn);
                    for ($y = $curThn + 1; $y >= $curThn - 3; $y--):
                    ?>
                        <option value="<?= $y ?>" <?= $selThn === (string)$y ? 'selected' : '' ?>>Tahun <?= $y ?><?= $y === $curThn ? ' (Aktif)' : '' ?></option>
                    <?php endfor; ?>
                    <option value="all" <?= $selThn === 'all' ? 'selected' : '' ?>>Semua Tahun</option>
                </select>

                <select name="unit_nama" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50 focus:bg-white font-medium text-slate-900 outline-none focus:border-primary-500">
                    <option value="">Semua Unit</option>
                    <?php foreach ($units as $u): ?>
                        <option value="<?= e($u) ?>" <?= $filters['unit_nama'] === $u ? 'selected' : '' ?>><?= e($u) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="jenis_id" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50 focus:bg-white font-medium text-slate-900 outline-none focus:border-primary-500">
                    <option value="">Semua Jenis Surat</option>
                    <?php foreach ($jenisList as $jl): ?>
                        <option value="<?= (int)$jl['id'] ?>" <?= (string)$filters['jenis_id'] === (string)$jl['id'] ? 'selected' : '' ?>>
                            [<?= e($jl['kode_surat']) ?>] <?= e($jl['nama_jenis']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold cursor-pointer shadow-xs">
                        Filter
                    </button>
                    <a href="<?= url('persuratan/export?' . http_build_query(['scope' => $scope] + $filters)) ?>"
                       class="py-2.5 px-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-sm font-bold">
                        CSV
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Nomor &amp; Jenis Surat</th>
                            <th class="py-3.5 px-4">Tanggal</th>
                            <th class="py-3.5 px-4">Unit</th>
                            <th class="py-3.5 px-4">Pengirim / Tujuan</th>
                            <th class="py-3.5 px-4">Perihal</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($suratList)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Belum ada data surat untuk filter ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suratList as $idx => $s): ?>
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-400"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold <?= $s['arah'] === 'masuk' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' ?>">
                                                <?= $s['arah'] === 'masuk' ? 'MASUK' : 'KELUAR' ?>
                                            </span>
                                            <span class="font-mono font-bold text-slate-900"><?= e($s['nomor_surat']) ?></span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            <?= e($s['nama_jenis'] ?: ($s['kode_surat'] ?: 'Umum')) ?> &bull; Sifat: <?= e($s['sifat_surat']) ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap text-slate-700">
                                        <?= date('d/m/Y', strtotime($s['tanggal_surat'])) ?>
                                        <?php if (!empty($s['tanggal_diterima'])): ?>
                                            <div class="text-[10px] text-slate-400">Terima: <?= date('d/m/Y', strtotime($s['tanggal_diterima'])) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px]"><?= e($s['unit_nama']) ?></span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($s['arah'] === 'masuk'): ?>
                                            <div class="font-semibold text-slate-800"><?= e($s['pengirim'] ?: '-') ?></div>
                                            <?php if (!empty($s['disposisi'])): ?>
                                                <div class="text-[10px] text-sky-700 mt-0.5">Disposisi: <?= e($s['disposisi']) ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="font-semibold text-slate-800"><?= e($s['tujuan'] ?: '-') ?></div>
                                            <?php if (!empty($s['penandatangan'])): ?>
                                                <div class="text-[10px] text-slate-400 mt-0.5">TTD: <?= e($s['penandatangan']) ?></div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-xs">
                                        <div class="font-semibold text-slate-900"><?= e($s['perihal']) ?></div>
                                        <?php if (!empty($s['file_lampiran'])): ?>
                                            <a href="<?= url('public/' . ltrim($s['file_lampiran'], '/')) ?>" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-primary-600 hover:underline mt-0.5">
                                                📎 Lihat Lampiran
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= e($s['status_surat']) ?>
                                        </span>
                                        <?php if ($scope === 'sekolah' && $s['arah'] === 'keluar'):
                                            $apSt = $s['approval_status'] ?? 'none';
                                            $apLabel = match($apSt) {
                                                'approved' => '✓ Di-Approve & TTD',
                                                'rejected' => '✕ Ditolak Kepsek',
                                                'pending'  => '⏳ Menunggu Kepsek',
                                                default    => 'Belum Diajukan',
                                            };
                                            $apCls = match($apSt) {
                                                'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                                default    => 'bg-slate-50 text-slate-500 border-slate-200',
                                            };
                                        ?>
                                            <div class="mt-1">
                                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $apCls ?>" title="<?= e($s['wa_notify_status'] ?? '') ?>">
                                                    <?= $apLabel ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1.5">
                                        <?php if ($scope === 'sekolah' && $s['arah'] === 'keluar' && ($s['approval_status'] ?? 'none') !== 'approved'): ?>
                                            <form method="POST" action="<?= url('persuratan/surat/resend-wa') ?>" class="inline">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="scope" value="<?= e($scope) ?>">
                                                <input type="hidden" name="return_tab" value="<?= e($activeTab) ?>">
                                                <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white border border-emerald-200 font-bold text-xs cursor-pointer transition-colors" title="<?= e($s['wa_notify_status'] ?? 'Kirim WA Approve ke Kepala Sekolah') ?>">
                                                    WA Approve
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($s['arah'] === 'keluar'): ?>
                                            <a href="<?= url('persuratan/cetak/' . (int)$s['id']) ?>" target="_blank"
                                               class="inline-flex items-center px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white border border-sky-200 font-bold text-xs transition-colors">
                                                Cetak KOP
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" onclick="openSuratModal('<?= e($s['arah']) ?>', <?= htmlspecialchars(json_encode($s)) ?>)"
                                                class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-colors">
                                            Edit
                                        </button>
                                        <form method="POST" action="<?= url('persuratan/surat/delete') ?>" class="inline" onsubmit="return confirm('Hapus surat ini?')">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="scope" value="<?= e($scope) ?>">
                                            <input type="hidden" name="return_tab" value="<?= e($activeTab) ?>">
                                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 font-bold text-xs cursor-pointer transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php elseif ($activeTab === 'pengaturan'): ?>
        <!-- ══════════ TAB 5: PENGATURAN SURAT (TAHUN AKTIF + KOP PER UNIT + JENIS & KODE SURAT) ══════════ -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Pengaturan Tahun Aktif Persuratan (<?= $scope === 'yayasan' ? 'Yayasan' : 'Sekolah' ?>)</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Periode berlaku <strong>1 Januari <?= (int)($tahunAktif ?? date('Y')) ?> s/d 31 Desember <?= (int)($tahunAktif ?? date('Y')) ?></strong>. Nomor urut surat keluar otomatis dimulai dari <strong>001</strong> setiap pergantian tahun.
                </p>
            </div>
            <form method="POST" action="<?= url('persuratan/tahun-aktif/save') ?>" class="flex items-center gap-2.5 shrink-0">
                <?= CSRF::field() ?>
                <input type="hidden" name="scope" value="<?= e($scope) ?>">
                <input type="hidden" name="return_tab" value="pengaturan">
                <input type="number" name="tahun_aktif" min="2020" max="2099" value="<?= (int)($tahunAktif ?? date('Y')) ?>" required
                       class="w-28 px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-extrabold text-center text-slate-900 outline-none">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-xs cursor-pointer">
                    Simpan Tahun Aktif
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- KOP Surat Masing-Masing Unit (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-5">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Pengaturan KOP Surat Masing-Masing Unit</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Atur identitas KOP, kode unit penomoran, logo, dan penandatangan default untuk setiap unit.</p>
                </div>

                <?php if (count($units) > 1): ?>
                    <div class="flex flex-wrap gap-2 border-b border-slate-100 pb-3.5">
                        <?php foreach ($units as $u): ?>
                            <a href="<?= url($baseRoute . '?tab=pengaturan&kop_unit=' . urlencode($u)) ?>"
                               class="px-4 py-2 rounded-xl text-sm font-bold transition-all <?= $activeKopUnit === $u ? 'bg-primary-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                                KOP <?= e($u) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php $curKop = $kopList[$activeKopUnit] ?? PersuratanModel::getKopByUnit($scope, $activeKopUnit); ?>
                <form method="POST" action="<?= url('persuratan/kop/save') ?>" enctype="multipart/form-data" class="space-y-4">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="scope" value="<?= e($scope) ?>">
                    <input type="hidden" name="unit_nama" value="<?= e($activeKopUnit) ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Unit</label>
                            <input type="text" value="<?= e($activeKopUnit) ?>" readonly class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-sm font-bold text-slate-700">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Unit (untuk Nomor Surat)</label>
                            <input type="text" name="kode_unit" value="<?= e($curKop['kode_unit'] ?? '') ?>" required placeholder="Contoh: SD-BIP"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-mono font-bold outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">KOP Baris 1 (Yayasan / Naungan)</label>
                            <input type="text" name="baris_1" value="<?= e($curKop['baris_1'] ?? '') ?>" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-bold outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">KOP Baris 2 (Nama Unit / Lembaga)</label>
                            <input type="text" name="baris_2" value="<?= e($curKop['baris_2'] ?? '') ?>" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-bold outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sub-Judul / Akreditasi / NPSN (Opsional)</label>
                        <input type="text" name="sub_judul" value="<?= e($curKop['sub_judul'] ?? '') ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Lengkap</label>
                        <textarea name="alamat" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none"><?= e($curKop['alamat'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kontak (Telepon / Email / Website)</label>
                        <input type="text" name="kontak" value="<?= e($curKop['kontak'] ?? '') ?>"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gambar KOP Surat Full (Opsional, Menggantikan Teks KOP)</label>
                            <input type="file" name="kop_gambar" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                            <?php if (!empty($curKop['kop_gambar'])): ?>
                                <div class="mt-2 p-2 rounded-xl border border-slate-200 bg-slate-50">
                                    <img src="<?= url('public/' . ltrim($curKop['kop_gambar'], '/')) ?>" alt="KOP Gambar" class="max-h-20 w-full object-contain">
                                    <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 font-semibold mt-1.5 cursor-pointer">
                                        <input type="checkbox" name="hapus_kop_gambar" value="1"> Hapus gambar KOP (kembali ke KOP teks)
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gambar Cap / Stempel Unit (PNG Transparan)</label>
                            <input type="file" name="cap_gambar" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <?php if (!empty($curKop['cap_gambar'])): ?>
                                <div class="mt-2 p-2 rounded-xl border border-slate-200 bg-slate-50 flex items-center gap-3">
                                    <img src="<?= url('public/' . ltrim($curKop['cap_gambar'], '/')) ?>" alt="Cap Unit" class="h-14 w-14 object-contain">
                                    <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 font-semibold cursor-pointer">
                                        <input type="checkbox" name="hapus_cap_gambar" value="1"> Hapus Cap Unit
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Logo KOP Kiri (Jika Pakai KOP Teks)</label>
                            <input type="file" name="logo_kiri" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            <?php if (!empty($curKop['logo_kiri'])): ?>
                                <img src="<?= url('public/' . ltrim($curKop['logo_kiri'], '/')) ?>" alt="Logo Kiri" class="h-12 mt-2 rounded border p-0.5">
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Logo KOP Kanan (Opsional)</label>
                            <input type="file" name="logo_kanan" accept="image/*" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            <?php if (!empty($curKop['logo_kanan'])): ?>
                                <img src="<?= url('public/' . ltrim($curKop['logo_kanan'], '/')) ?>" alt="Logo Kanan" class="h-12 mt-2 rounded border p-0.5">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-slate-100">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jabatan Penandatangan</label>
                            <input type="text" name="penandatangan_jabatan" value="<?= e($curKop['penandatangan_jabatan'] ?? '') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Penandatangan</label>
                            <input type="text" name="penandatangan_nama" value="<?= e($curKop['penandatangan_nama'] ?? '') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">NIY / NIP</label>
                            <input type="text" name="penandatangan_niy" value="<?= e($curKop['penandatangan_niy'] ?? '') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-mono outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold shadow-sm cursor-pointer">
                            Simpan KOP <?= e($activeKopUnit) ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Master Jenis & Kode Surat (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 sm:p-6 space-y-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Jenis &amp; Kode Surat</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Atur kode surat dan pola penomoran otomatis.</p>
                </div>

                <form method="POST" action="<?= url('persuratan/jenis/store') ?>" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="scope" value="<?= e($scope) ?>">
                    <input type="hidden" name="id" id="jenis_id" value="">
                    <input type="hidden" name="is_active" value="1">

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Kode *</label>
                            <input type="text" name="kode_surat" id="jenis_kode" required placeholder="SK"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-mono font-bold uppercase outline-none focus:border-primary-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Jenis Surat *</label>
                            <input type="text" name="nama_jenis" id="jenis_nama" required placeholder="Surat Keputusan"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm outline-none focus:border-primary-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Format Penomoran</label>
                        <input type="text" name="format_nomor" id="jenis_format" value="{no}/{kode}/{unit}/{romawi}/{tahun}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-mono outline-none focus:border-primary-500">
                        <p class="text-xs text-slate-400 mt-1">Variabel: <code>{no}</code>, <code>{kode}</code>, <code>{unit}</code>, <code>{romawi}</code>, <code>{tahun}</code></p>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" onclick="resetJenisForm()" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-300 text-sm font-semibold text-slate-700 cursor-pointer">Reset</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold cursor-pointer shadow-xs">Simpan Jenis</button>
                    </div>
                </form>

                <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
                    <?php foreach ($jenisList as $jl): ?>
                        <div class="py-3 flex items-center justify-between gap-2 text-sm">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-lg bg-primary-50 text-primary-700 border border-primary-200 font-mono font-bold text-xs"><?= e($jl['kode_surat']) ?></span>
                                    <span class="font-bold text-slate-800 truncate"><?= e($jl['nama_jenis']) ?></span>
                                </div>
                                <div class="text-xs font-mono text-slate-400 mt-0.5 truncate"><?= e($jl['format_nomor']) ?></div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" onclick="editJenis(<?= htmlspecialchars(json_encode($jl)) ?>)"
                                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">Edit</button>
                                <form method="POST" action="<?= url('persuratan/jenis/delete') ?>" class="inline" onsubmit="return confirm('Hapus jenis surat ini?')">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="scope" value="<?= e($scope) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$jl['id'] ?>">
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white text-xs font-bold cursor-pointer">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah / Edit Surat (Masuk & Keluar) -->
<div id="modalSurat" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="relative bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-7 shadow-2xl border border-slate-100 my-8">
        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
            <h3 id="modalSuratTitle" class="text-lg font-extrabold text-slate-900">Tambah Surat</h3>
            <button type="button" onclick="closeSuratModal()" class="p-2 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-100 cursor-pointer">✕</button>
        </div>

        <form method="POST" action="<?= url('persuratan/surat/store') ?>" enctype="multipart/form-data" class="space-y-4 mt-4 text-sm">
            <?= CSRF::field() ?>
            <input type="hidden" name="scope" value="<?= e($scope) ?>">
            <input type="hidden" name="return_tab" value="<?= e($activeTab === 'dashboard' ? 'data' : $activeTab) ?>">
            <input type="hidden" name="id" id="surat_id" value="">
            <input type="hidden" name="arah" id="surat_arah" value="masuk">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Unit *</label>
                    <select name="unit_nama" id="surat_unit" onchange="autoGenerateNomorIfKeluar()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-bold outline-none">
                        <?php foreach ($units as $u): ?>
                            <option value="<?= e($u) ?>"><?= e($u) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jenis &amp; Kode Surat</label>
                    <select name="jenis_id" id="surat_jenis" onchange="autoGenerateNomorIfKeluar()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-medium outline-none">
                        <option value="">-- Pilih Jenis --</option>
                        <?php foreach ($jenisAktif as $ja): ?>
                            <option value="<?= (int)$ja['id'] ?>">[<?= e($ja['kode_surat']) ?>] <?= e($ja['nama_jenis']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sifat Surat</label>
                    <select name="sifat_surat" id="surat_sifat" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-medium outline-none">
                        <option value="Biasa">Biasa</option>
                        <option value="Penting">Penting</option>
                        <option value="Segera">Segera</option>
                        <option value="Rahasia">Rahasia</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nomor Surat *</label>
                    <div class="flex gap-2">
                        <input type="text" name="nomor_surat" id="surat_nomor" required placeholder="Contoh: 001/SK/SD-BIP/XI/2026"
                               class="flex-1 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-mono font-bold outline-none">
                        <button type="button" id="btnGenNomor" onclick="autoGenerateNomorIfKeluar(true)"
                                class="px-4 py-2.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-sm font-bold cursor-pointer">
                            Auto
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Surat *</label>
                        <?php
                        $defaultTglModal = ((int)($tahunAktif ?? date('Y')) === (int)date('Y'))
                            ? date('Y-m-d')
                            : sprintf('%04d-01-01', (int)($tahunAktif ?? date('Y')));
                        ?>
                        <input type="date" name="tanggal_surat" id="surat_tanggal" value="<?= $defaultTglModal ?>" required onchange="autoGenerateNomorIfKeluar()"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-bold outline-none">
                    </div>
                    <div id="wrap_tgl_terima">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Terima</label>
                        <input type="date" name="tanggal_diterima" id="surat_tgl_terima" value="<?= date('Y-m-d') ?>"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-bold outline-none">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div id="wrap_pengirim">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pengirim Surat</label>
                    <input type="text" name="pengirim" id="surat_pengirim" placeholder="Instansi / Nama Pengirim"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none">
                </div>
                <div id="wrap_tujuan">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tujuan / Penerima</label>
                    <input type="text" name="tujuan" id="surat_tujuan" placeholder="Tujuan Surat"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Perihal *</label>
                <input type="text" name="perihal" id="surat_perihal" required placeholder="Perihal / Judul Surat"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-semibold outline-none">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Isi Ringkas / Redaksi Surat</label>
                <textarea name="isi_ringkas" id="surat_isi" rows="3" placeholder="Ringkasan isi surat (untuk surat keluar akan tampil di halaman cetak)..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div id="wrap_disposisi">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Disposisi</label>
                    <input type="text" name="disposisi" id="surat_disposisi" placeholder="Diteruskan ke..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none">
                </div>
                <div id="wrap_penandatangan">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Penandatangan</label>
                    <input type="text" name="penandatangan" id="surat_penandatangan" placeholder="Nama Pejabat TTD"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                    <select name="status_surat" id="surat_status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 text-sm font-medium outline-none">
                        <option value="Diterima">Diterima</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Draft">Draft</option>
                        <option value="Terkirim">Terkirim</option>
                        <option value="Arsip">Arsip</option>
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">File Lampiran (PDF/Foto)</label>
                    <input type="file" name="file_lampiran" accept=".pdf,image/*,.doc,.docx" class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2.5">
                <button type="button" onclick="closeSuratModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-sm font-semibold text-slate-700 cursor-pointer">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold shadow-sm cursor-pointer">Simpan Surat</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSuratModal(arah, data = null) {
    const modal = document.getElementById('modalSurat');
    document.getElementById('surat_arah').value = arah;
    document.getElementById('surat_id').value = data ? (data.id || '') : '';
    document.getElementById('modalSuratTitle').textContent = (data ? 'Edit ' : 'Tambah ') + (arah === 'masuk' ? 'Surat Masuk' : 'Surat Keluar');

    const isMasuk = (arah === 'masuk');
    document.getElementById('wrap_tgl_terima').style.display = isMasuk ? '' : 'none';
    document.getElementById('wrap_pengirim').style.display = isMasuk ? '' : 'none';
    document.getElementById('wrap_disposisi').style.display = isMasuk ? '' : 'none';
    document.getElementById('wrap_tujuan').style.display = isMasuk ? 'none' : '';
    document.getElementById('wrap_penandatangan').style.display = isMasuk ? 'none' : '';
    document.getElementById('btnGenNomor').style.display = isMasuk ? 'none' : '';

    if (data) {
        document.getElementById('surat_unit').value = data.unit_nama || '<?= e($units[0] ?? 'Yayasan') ?>';
        document.getElementById('surat_jenis').value = data.jenis_id || '';
        document.getElementById('surat_sifat').value = data.sifat_surat || 'Biasa';
        document.getElementById('surat_nomor').value = data.nomor_surat || '';
        document.getElementById('surat_tanggal').value = data.tanggal_surat || '<?= date('Y-m-d') ?>';
        document.getElementById('surat_tgl_terima').value = data.tanggal_diterima || '';
        document.getElementById('surat_pengirim').value = data.pengirim || '';
        document.getElementById('surat_tujuan').value = data.tujuan || '';
        document.getElementById('surat_perihal').value = data.perihal || '';
        document.getElementById('surat_isi').value = data.isi_ringkas || '';
        document.getElementById('surat_disposisi').value = data.disposisi || '';
        document.getElementById('surat_penandatangan').value = data.penandatangan || '';
        document.getElementById('surat_status').value = data.status_surat || (isMasuk ? 'Diterima' : 'Terkirim');
    } else {
        document.getElementById('surat_nomor').value = '';
        document.getElementById('surat_pengirim').value = '';
        document.getElementById('surat_tujuan').value = '';
        document.getElementById('surat_perihal').value = '';
        document.getElementById('surat_isi').value = '';
        document.getElementById('surat_disposisi').value = '';
        document.getElementById('surat_penandatangan').value = '';
        document.getElementById('surat_status').value = isMasuk ? 'Diterima' : 'Terkirim';
        if (!isMasuk) autoGenerateNomorIfKeluar(true);
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeSuratModal() {
    const modal = document.getElementById('modalSurat');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function autoGenerateNomorIfKeluar(force = false) {
    if (document.getElementById('surat_arah').value !== 'keluar') return;
    if (!force && document.getElementById('surat_id').value !== '') return;
    const unit = document.getElementById('surat_unit').value;
    const jenisId = document.getElementById('surat_jenis').value;
    const tanggal = document.getElementById('surat_tanggal').value;
    fetch(`<?= url('persuratan/api/next-nomor') ?>?scope=<?= e($scope) ?>&unit_nama=${encodeURIComponent(unit)}&jenis_id=${encodeURIComponent(jenisId)}&tanggal=${encodeURIComponent(tanggal)}`)
        .then(r => r.json())
        .then(res => {
            if (res && res.success && res.nomor_surat) {
                document.getElementById('surat_nomor').value = res.nomor_surat;
            }
        }).catch(() => {});
}

function editJenis(j) {
    document.getElementById('jenis_id').value = j.id || '';
    document.getElementById('jenis_kode').value = j.kode_surat || '';
    document.getElementById('jenis_nama').value = j.nama_jenis || '';
    document.getElementById('jenis_format').value = j.format_nomor || '{no}/{kode}/{unit}/{romawi}/{tahun}';
}

function resetJenisForm() {
    document.getElementById('jenis_id').value = '';
    document.getElementById('jenis_kode').value = '';
    document.getElementById('jenis_nama').value = '';
    document.getElementById('jenis_format').value = '{no}/{kode}/{unit}/{romawi}/{tahun}';
}
</script>
