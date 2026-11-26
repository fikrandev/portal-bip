<?php
/**
 * View Utama Observasi Siswa Langsung (Per Unit + RBAC Terpisah)
 * Tab 1: Buat Pertanyaan (Pertanyaan, Jawaban, Nilai, Keterangan Opsional) — RBAC: spmb.observasi_siswa.soal
 * Tab 2: Pengisian & Hasil Observasi + Kesimpulan Sistem + Cetak — RBAC: spmb.observasi_siswa.nilai / view / cetak
 */
$csrfToken = CSRF::token();
$selStatus = $filters['status_obs'] ?? '';
$search = $filters['search'] ?? '';
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Observasi Siswa Per Unit
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Unit <?= e($selJenjang) ?>
                </span>
                <span class="text-xs font-semibold text-slate-500"><?= e($activeGroup['nama_group'] ?? 'SPMB Aktif') ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Observasi Langsung Calon Siswa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Susun instrumen pertanyaan per unit, isi penilaian observasi siswa oleh petugas, kesimpulan otomatis oleh sistem, dan cetak hasil.
            </p>
        </div>

        <!-- Selector Unit -->
        <div class="flex flex-wrap items-center gap-1.5 bg-slate-100 p-1.5 rounded-2xl">
            <?php foreach ($allowedUnits as $uOpt): ?>
                <a href="<?= url('kelola-spmb/observasi-siswa?tab=' . urlencode($activeTab) . '&jenjang=' . urlencode($uOpt)) ?>"
                   class="px-4 py-2 rounded-xl text-sm font-bold transition-all <?= $selJenjang === $uOpt ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-600 hover:bg-white' ?>">
                    <?= e($uOpt) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Sub-Tabs (Sesuai RBAC Petugas) -->
    <div class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-xs">
        <?php if ($canSoal): ?>
            <a href="<?= url('kelola-spmb/observasi-siswa?tab=soal&jenjang=' . urlencode($selJenjang)) ?>"
               class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all <?= $activeTab === 'soal' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                1. Buat Pertanyaan &amp; Bobot Nilai (<?= count($soalList) ?>)
            </a>
        <?php endif; ?>
        <?php if ($canHasil): ?>
            <a href="<?= url('kelola-spmb/observasi-siswa?tab=hasil&jenjang=' . urlencode($selJenjang)) ?>"
               class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all <?= $activeTab === 'hasil' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
                2. Pengisian &amp; Hasil Observasi Siswa (<?= number_format($stats['sudah'] ?? 0) ?> / <?= number_format($stats['total'] ?? 0) ?>)
            </a>
        <?php endif; ?>
    </div>

    <?php if ($activeTab === 'soal' && $canSoal): ?>
        <?php
        // Kelompokkan daftar pertanyaan berdasarkan Aspek Penilaian
        $soalByAspek = [];
        foreach ($soalList as $sItem) {
            $aspKey = trim((string)($sItem['aspek'] ?? 'Umum')) ?: 'Umum';
            $soalByAspek[$aspKey][] = $sItem;
        }
        $existingAspeks = array_keys($soalByAspek);
        ?>
        <!-- ══════════ TAB 1: BUAT PERTANYAAN OBSERVASI SISWA (PER ASPEK & PER UNIT) ══════════ -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Form Buat / Edit Pertanyaan -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 sm:p-6 space-y-4">
                <div>
                    <h3 id="soalFormTitle" class="text-base font-extrabold text-slate-900">Tambah Pertanyaan — Unit <?= e($selJenjang) ?></h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih aspek yang sudah ada atau ketik aspek baru, lalu tambahkan beberapa pertanyaan di dalamnya.</p>
                </div>

                <form method="POST" action="<?= url('kelola-spmb/observasi-siswa/soal/store') ?>" class="space-y-4 text-sm">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="id" id="soal_id" value="">
                    <input type="hidden" name="jenjang" value="<?= e($selJenjang) ?>">
                    <input type="hidden" name="is_active" value="1">

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Aspek Penilaian *</label>
                            <input type="text" name="aspek" id="soal_aspek" list="aspekDatalist" value="<?= e($existingAspeks[0] ?? 'Kognitif') ?>" required placeholder="Misal: Kognitif / Agama / Motorik"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white outline-none focus:border-indigo-500 font-semibold">
                            <datalist id="aspekDatalist">
                                <?php foreach (array_unique(array_merge($existingAspeks, ['Kognitif', 'Agama & Akhlak', 'Motorik', 'Bahasa & Komunikasi', 'Kemandirian & Sosial'])) as $aOpt): ?>
                                    <option value="<?= e($aOpt) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Urutan</label>
                            <input type="number" name="urutan" id="soal_urutan" min="1" value="<?= count($soalList) + 1 ?>"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white outline-none text-center font-bold">
                        </div>
                    </div>

                    <?php if (!empty($existingAspeks)): ?>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[11px] font-bold text-slate-400">Pilih cepat aspek:</span>
                            <?php foreach ($existingAspeks as $aChip): ?>
                                <button type="button" onclick="pilihAspekCepat('<?= e(addslashes($aChip)) ?>')"
                                        class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold cursor-pointer">
                                    <?= e($aChip) ?> (<?= count($soalByAspek[$aChip]) ?>)
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Pertanyaan *</label>
                        <textarea name="pertanyaan" id="soal_pertanyaan" rows="3" required placeholder="Tuliskan pertanyaan / indikator observasi untuk calon siswa..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white outline-none"></textarea>
                    </div>

                    <!-- Opsi Jawaban & Nilai -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-slate-700">Pilihan Jawaban &amp; Nilai *</label>
                            <button type="button" onclick="addOpsiRow('', '')" class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold cursor-pointer">
                                + Tambah Jawaban
                            </button>
                        </div>
                        <div id="opsiContainer" class="space-y-2"></div>
                    </div>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer">
                        <input type="checkbox" name="pakai_keterangan" id="soal_pakai_ket" value="1" checked class="w-4 h-4 rounded text-indigo-600">
                        <span class="text-xs font-semibold text-slate-700">Tampilkan input <strong>Keterangan (Opsional)</strong> saat petugas mengisi</span>
                    </label>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="resetSoalForm()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 cursor-pointer">Reset</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-xs cursor-pointer">Simpan Pertanyaan</button>
                    </div>
                </form>
            </div>

            <!-- Daftar Pertanyaan Dikelompokkan Per Aspek -->
            <div class="lg:col-span-7 space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs px-5 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">Daftar Aspek &amp; Pertanyaan Unit <?= e($selJenjang) ?></h3>
                        <p class="text-xs text-slate-500"><?= count($soalByAspek) ?> Aspek &bull; <?= count($soalList) ?> Total Pertanyaan</p>
                    </div>
                </div>

                <?php if (empty($soalByAspek)): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-sm text-slate-400">
                        Belum ada pertanyaan Observasi Siswa untuk unit <strong><?= e($selJenjang) ?></strong>. Silakan buat pada form di sebelah kiri.
                    </div>
                <?php else: ?>
                    <?php $aspekIdx = 0; foreach ($soalByAspek as $aspekNama => $aspekSoals):
                        $aspekIdx++;
                        $aspekMaxSum = array_sum(array_map(fn($x) => (float)($x['nilai_maksimal'] ?? 0), $aspekSoals));
                    ?>
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                            <div class="px-5 py-3.5 bg-indigo-50/80 border-b border-indigo-100 flex flex-wrap items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-extrabold flex items-center justify-center"><?= chr(64 + $aspekIdx) ?></span>
                                    <h4 class="text-sm font-extrabold text-indigo-950">Aspek <?= e($aspekNama) ?></h4>
                                    <span class="px-2 py-0.5 rounded-full bg-white text-indigo-700 border border-indigo-200 text-[11px] font-bold">
                                        <?= count($aspekSoals) ?> Pertanyaan &bull; Maks <?= rtrim(rtrim(number_format($aspekMaxSum, 2, '.', ''), '0'), '.') ?>
                                    </span>
                                </div>
                                <button type="button" onclick="pilihAspekCepat('<?= e(addslashes($aspekNama)) ?>', true)"
                                        class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold cursor-pointer">
                                    + Tambah Pertanyaan
                                </button>
                            </div>
                            <div class="divide-y divide-slate-100">
                                <?php foreach ($aspekSoals as $qNo => $s): ?>
                                    <div class="p-4 sm:p-5 hover:bg-slate-50/70 space-y-2">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                                    <span class="px-2 py-0.5 rounded-lg bg-slate-800 text-white text-[11px] font-bold"><?= $qNo + 1 ?></span>
                                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-[11px] font-bold">Maks Nilai: <?= rtrim(rtrim(number_format((float)$s['nilai_maksimal'], 2, '.', ''), '0'), '.') ?></span>
                                                    <?php if (!empty($s['pakai_keterangan'])): ?>
                                                        <span class="px-2 py-0.5 rounded-lg bg-sky-50 text-sky-700 text-[11px] font-semibold">+ Keterangan</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-sm font-bold text-slate-900"><?= e($s['pertanyaan']) ?></p>
                                            </div>
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <button type="button" onclick='editSoal(<?= json_encode($s, JSON_UNESCAPED_UNICODE) ?>)'
                                                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 cursor-pointer">Edit</button>
                                                <form method="POST" action="<?= url('kelola-spmb/observasi-siswa/soal/delete/' . (int)$s['id']) ?>" class="inline" onsubmit="return confirm('Hapus pertanyaan ini?')">
                                                    <?= CSRF::field() ?>
                                                    <input type="hidden" name="jenjang" value="<?= e($selJenjang) ?>">
                                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white text-xs font-bold cursor-pointer">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5 pt-1">
                                            <?php foreach (($s['opsi'] ?? []) as $op): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold">
                                                    <span><?= e($op['jawaban']) ?></span>
                                                    <strong class="text-indigo-700">(<?= rtrim(rtrim(number_format((float)$op['nilai'], 2, '.', ''), '0'), '.') ?>)</strong>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($activeTab === 'hasil' && $canHasil): ?>
        <!-- ══════════ TAB 2: PENGISIAN & HASIL OBSERVASI SISWA ══════════ -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase">Total Siswa Unit <?= e($selJenjang) ?></span>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['total'] ?? 0) ?></div>
            </div>
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-emerald-200 shadow-xs">
                <span class="text-xs font-bold text-emerald-600 uppercase">Sudah Diobservasi</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1"><?= number_format($stats['sudah'] ?? 0) ?></div>
            </div>
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-amber-200 shadow-xs">
                <span class="text-xs font-bold text-amber-600 uppercase">Belum Diobservasi</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-1"><?= number_format($stats['belum'] ?? 0) ?></div>
            </div>
            <div class="bg-white rounded-2xl p-4 sm:p-5 border border-indigo-200 shadow-xs">
                <span class="text-xs font-bold text-indigo-600 uppercase">Rata-rata Nilai</span>
                <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600 mt-1"><?= number_format((float)($stats['rata_persen'] ?? 0), 1) ?>%</div>
            </div>
        </div>

        <!-- Toolbar Filter & Cetak -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <form method="GET" action="<?= url('kelola-spmb/observasi-siswa') ?>" class="flex flex-wrap items-center gap-2.5 flex-1">
                <input type="hidden" name="tab" value="hasil">
                <input type="hidden" name="jenjang" value="<?= e($selJenjang) ?>">
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Cari nama / no. pendaftaran..."
                       class="min-w-[220px] flex-1 max-w-sm px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm outline-none">
                <select name="status_obs" class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm font-medium outline-none">
                    <option value="">Semua Status</option>
                    <option value="sudah" <?= $selStatus === 'sudah' ? 'selected' : '' ?>>Sudah Diobservasi</option>
                    <option value="belum" <?= $selStatus === 'belum' ? 'selected' : '' ?>>Belum Diobservasi</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold cursor-pointer">Filter</button>
            </form>

            <?php if ($canCetak): ?>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <a href="<?= url('kelola-spmb/observasi-siswa/cetak-semua?jenjang=' . urlencode($selJenjang) . '&mode=rekap') ?>" target="_blank"
                       class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-xs">
                        Cetak Rekap Unit
                    </a>
                    <a href="<?= url('kelola-spmb/observasi-siswa/cetak-semua?jenjang=' . urlencode($selJenjang) . '&mode=detail') ?>" target="_blank"
                       class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold shadow-xs">
                        Cetak Semua Per Siswa
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabel Siswa & Hasil -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4">Calon Siswa</th>
                            <th class="py-3.5 px-4 text-center">Nilai &amp; Kategori</th>
                            <th class="py-3.5 px-4 max-w-md">Kesimpulan Sistem</th>
                            <th class="py-3.5 px-4">Petugas</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($items)): ?>
                            <tr><td colspan="6" class="py-12 text-center text-slate-400">Belum ada calon siswa pada unit <?= e($selJenjang) ?>.</td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $idx => $it):
                                $isDone = !empty($it['hasil_id']);
                            ?>
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-400"><?= $idx + 1 ?></td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900"><?= e($it['nama_lengkap']) ?></div>
                                        <div class="text-xs font-mono text-slate-500"><?= e($it['nomor_pendaftaran']) ?> &bull; <?= e($it['jenjang']) ?></div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <?php if ($isDone): ?>
                                            <div class="font-extrabold text-indigo-700">
                                                <?= rtrim(rtrim(number_format((float)$it['total_nilai'], 2, '.', ''), '0'), '.') ?> / <?= rtrim(rtrim(number_format((float)$it['nilai_maksimal'], 2, '.', ''), '0'), '.') ?>
                                                (<?= number_format((float)$it['persentase'], 1) ?>%)
                                            </div>
                                            <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <?= e($it['kategori_hasil']) ?>
                                            </span>
                                            <?php if (!empty($it['detail_nilai'])): ?>
                                                <div class="flex flex-wrap justify-center gap-1 mt-1.5">
                                                    <?php foreach ($it['detail_nilai'] as $dn): ?>
                                                        <span class="px-1.5 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-bold">
                                                            <?= e($dn['aspek']) ?>: <?= rtrim(rtrim(number_format((float)$dn['nilai'], 2, '.', ''), '0'), '.') ?>/<?= rtrim(rtrim(number_format((float)$dn['maks'], 2, '.', ''), '0'), '.') ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Diobservasi
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 max-w-md text-xs text-slate-600 leading-relaxed">
                                        <?= $isDone ? e($it['kesimpulan_sistem']) : '<span class="text-slate-400 italic">Kesimpulan otomatis muncul setelah petugas mengisi observasi.</span>' ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-slate-600 whitespace-nowrap">
                                        <?php if ($isDone): ?>
                                            <div class="font-bold text-slate-800"><?= e($it['petugas_nama'] ?: '-') ?></div>
                                            <div class="text-[11px] text-slate-400"><?= $it['observed_at'] ? date('d/m/Y H:i', strtotime($it['observed_at'])) : '' ?></div>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1.5">
                                        <?php if ($canNilai): ?>
                                            <button type="button" onclick="openIsiObservasiModal(<?= (int)$it['pendaftar_id'] ?>)"
                                                    class="inline-flex items-center px-3.5 py-1.5 rounded-xl <?= $isDone ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : 'bg-emerald-600 hover:bg-emerald-700 text-white' ?> text-xs font-bold cursor-pointer">
                                                <?= $isDone ? 'Ubah Nilai' : 'Isi Observasi' ?>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($isDone && $canCetak): ?>
                                            <a href="<?= url('kelola-spmb/observasi-siswa/cetak/' . (int)$it['pendaftar_id']) ?>" target="_blank"
                                               class="inline-flex items-center px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-600 text-sky-700 hover:text-white border border-sky-200 text-xs font-bold transition-colors">
                                                Cetak
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Pengisian Observasi Siswa -->
<div id="modalIsiObservasiSiswa" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative bg-white rounded-3xl max-w-3xl w-full shadow-2xl border border-slate-100 overflow-hidden my-8">
            <div class="px-6 py-4 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
                <div>
                    <h3 id="obsModalNama" class="text-base font-extrabold text-slate-900">Pengisian Observasi Siswa</h3>
                    <p id="obsModalMeta" class="text-xs text-slate-600">Pilih jawaban untuk setiap indikator observasi</p>
                </div>
                <button type="button" onclick="closeIsiObservasiModal()" class="p-1.5 rounded-full text-slate-400 hover:bg-slate-200 cursor-pointer">✕</button>
            </div>

            <form method="POST" action="<?= url('kelola-spmb/observasi-siswa/nilai/save') ?>" class="p-6 space-y-4">
                <?= CSRF::field() ?>
                <input type="hidden" name="pendaftar_id" id="obs_pendaftar_id" value="">
                <input type="hidden" name="jenjang" value="<?= e($selJenjang) ?>">

                <div id="obsLiveScoreBar" class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200 space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold text-indigo-900">
                        <span>Skor Sementara: <strong id="obsLiveScoreText">0 / 0 (0%)</strong></span>
                        <span id="obsLiveCatText" class="px-2.5 py-0.5 rounded-full bg-white text-indigo-700 border border-indigo-200">Menunggu Jawaban</span>
                    </div>
                    <div id="obsLiveAspekBar" class="flex flex-wrap gap-1.5"></div>
                </div>

                <div id="obsSoalFieldsWrap" class="space-y-3 max-h-[55vh] overflow-y-auto pr-1"></div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan Petugas (Opsional)</label>
                    <textarea name="catatan_petugas" id="obs_catatan_petugas" rows="2" placeholder="Catatan umum hasil pengamatan petugas..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeIsiObservasiModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 cursor-pointer">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-sm cursor-pointer">Simpan &amp; Generate Kesimpulan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── TAB 1: DYNAMIC OPSI JAWABAN & NILAI ──
function addOpsiRow(jawaban = '', nilai = '') {
    const wrap = document.getElementById('opsiContainer');
    if (!wrap) return;
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `
        <input type="text" name="opsi_jawaban[]" value="${String(jawaban).replace(/"/g, '&quot;')}" required placeholder="Jawaban (misal: Sangat Baik)"
               class="flex-1 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm outline-none">
        <input type="number" step="any" name="opsi_nilai[]" value="${nilai}" required placeholder="Nilai"
               class="w-24 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white text-sm font-bold text-center outline-none">
        <button type="button" onclick="this.parentElement.remove()" class="px-2.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold cursor-pointer">✕</button>
    `;
    wrap.appendChild(row);
}

function initDefaultOpsi() {
    const wrap = document.getElementById('opsiContainer');
    if (!wrap) return;
    wrap.innerHTML = '';
    addOpsiRow('Sangat Baik', 4);
    addOpsiRow('Baik', 3);
    addOpsiRow('Cukup', 2);
    addOpsiRow('Perlu Bimbingan', 1);
}
initDefaultOpsi();

function editSoal(s) {
    document.getElementById('soalFormTitle').textContent = 'Edit Pertanyaan #' + s.urutan;
    document.getElementById('soal_id').value = s.id || '';
    document.getElementById('soal_aspek').value = s.aspek || 'Umum';
    document.getElementById('soal_urutan').value = s.urutan || 1;
    document.getElementById('soal_pertanyaan').value = s.pertanyaan || '';
    document.getElementById('soal_pakai_ket').checked = Number(s.pakai_keterangan) === 1;
    const wrap = document.getElementById('opsiContainer');
    wrap.innerHTML = '';
    (s.opsi || []).forEach(op => addOpsiRow(op.jawaban, op.nilai));
    if (!(s.opsi || []).length) initDefaultOpsi();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function pilihAspekCepat(namaAspek, focusInput = false) {
    document.getElementById('soal_id').value = '';
    document.getElementById('soalFormTitle').textContent = 'Tambah Pertanyaan di Aspek ' + namaAspek;
    const aspInp = document.getElementById('soal_aspek');
    if (aspInp) aspInp.value = namaAspek;
    const qInp = document.getElementById('soal_pertanyaan');
    if (qInp) {
        qInp.value = '';
        if (focusInput) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            qInp.focus();
        }
    }
}

function resetSoalForm() {
    document.getElementById('soalFormTitle').textContent = 'Tambah Pertanyaan — Unit <?= e($selJenjang) ?>';
    document.getElementById('soal_id').value = '';
    document.getElementById('soal_aspek').value = 'Kognitif';
    document.getElementById('soal_pertanyaan').value = '';
    document.getElementById('soal_pakai_ket').checked = true;
    initDefaultOpsi();
}

// ── TAB 2: MODAL PENGISIAN OBSERVASI SISWA ──
let currentObsSoalList = [];

async function openIsiObservasiModal(pendaftarId) {
    const modal = document.getElementById('modalIsiObservasiSiswa');
    const wrap = document.getElementById('obsSoalFieldsWrap');
    document.getElementById('obs_pendaftar_id').value = pendaftarId;
    wrap.innerHTML = '<div class="py-10 text-center text-sm text-slate-400">Memuat instrumen pertanyaan...</div>';
    modal.classList.remove('hidden');

    try {
        const res = await fetch('<?= url('kelola-spmb/observasi-siswa/form-data/') ?>' + pendaftarId);
        const json = await res.json();
        if (!json.success) {
            wrap.innerHTML = '<div class="py-8 text-center text-sm text-rose-600">' + (json.message || 'Gagal memuat data.') + '</div>';
            return;
        }
        const p = json.pendaftar;
        const h = json.hasil || {};
        const jMap = json.jawaban_map || {};
        currentObsSoalList = json.soal_list || [];

        document.getElementById('obsModalNama').textContent = p.nama_lengkap;
        document.getElementById('obsModalMeta').textContent = `No. Pendaftaran: ${p.nomor_pendaftaran} • Unit ${p.jenjang}`;
        document.getElementById('obs_catatan_petugas').value = h.catatan_petugas || '';

        if (!currentObsSoalList.length) {
            wrap.innerHTML = '<div class="py-8 text-center text-sm text-amber-700 bg-amber-50 rounded-2xl border border-amber-200">Belum ada pertanyaan observasi siswa untuk unit ' + p.jenjang + '. Minta petugas pembuat soal untuk menambah pertanyaan di Tab 1.</div>';
            return;
        }

        // Kelompokkan soal berdasarkan Aspek di dalam Modal Pengisian
        const groupedAspek = {};
        currentObsSoalList.forEach(s => {
            const asp = (s.aspek || 'Umum').trim() || 'Umum';
            if (!groupedAspek[asp]) groupedAspek[asp] = [];
            groupedAspek[asp].push(s);
        });

        let gIdx = 0;
        wrap.innerHTML = Object.entries(groupedAspek).map(([aspekNama, soals]) => {
            gIdx++;
            const aspSlug = 'asp_' + gIdx;
            const aspMax = soals.reduce((acc, x) => acc + parseFloat(x.nilai_maksimal || 0), 0);
            const itemsHtml = soals.map((s, qIdx) => {
                const prev = jMap[s.id] || null;
                const opsiHtml = (s.opsi || []).map((op, oIdx) => {
                    const checked = prev && prev.jawaban_dipilih === op.jawaban ? 'checked' : '';
                    return `<label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:border-emerald-400 cursor-pointer text-xs font-semibold">
                        <input type="radio" name="jawaban[${s.id}]" value="${oIdx}" data-soal-id="${s.id}" data-aspek="${String(aspekNama).replace(/"/g, '&quot;')}" data-aspek-slug="${aspSlug}" data-val="${op.nilai}" data-max="${s.nilai_maksimal || 0}" ${checked} required onchange="recalcLiveObsScore()" class="obs-radio text-emerald-600">
                        <span>${op.jawaban}</span>
                        <span class="text-indigo-700 font-bold">(${op.nilai})</span>
                    </label>`;
                }).join('');

                const ketHtml = Number(s.pakai_keterangan) === 1
                    ? `<input type="text" name="keterangan[${s.id}]" value="${prev && prev.keterangan ? String(prev.keterangan).replace(/"/g, '&quot;') : ''}" placeholder="Keterangan tambahan (opsional)..." class="w-full mt-2 px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs outline-none focus:border-indigo-400">`
                    : '';

                const maxClean = parseFloat(s.nilai_maksimal || 0);
                return `<div class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-slate-800 text-white text-[10px] font-bold">${qIdx + 1}</span>
                            <span class="text-sm font-bold text-slate-900">${s.pertanyaan}</span>
                        </div>
                        <span id="obs_q_score_${s.id}" class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600">
                            Nilai: - / ${maxClean}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-0.5">${opsiHtml}</div>
                    ${ketHtml}
                </div>`;
            }).join('');

            return `<div class="rounded-2xl border border-indigo-200 bg-indigo-50/40 overflow-hidden">
                <div class="px-4 py-2.5 bg-indigo-100/70 border-b border-indigo-200 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-extrabold flex items-center justify-center">${String.fromCharCode(64 + gIdx)}</span>
                        <span class="text-xs sm:text-sm font-extrabold text-indigo-950">Aspek ${aspekNama} (${soals.length} Pertanyaan)</span>
                    </div>
                    <span id="obs_aspek_total_${aspSlug}" data-aspek-max="${aspMax}" class="px-2.5 py-0.5 rounded-full bg-white text-indigo-800 border border-indigo-200 text-xs font-extrabold">
                        Total: 0 / ${aspMax}
                    </span>
                </div>
                <div class="p-3 space-y-2.5">${itemsHtml}</div>
            </div>`;
        }).join('');
        recalcLiveObsScore();
    } catch (e) {
        wrap.innerHTML = '<div class="py-8 text-center text-sm text-rose-600">Terjadi kesalahan jaringan.</div>';
    }
}

function recalcLiveObsScore() {
    let total = 0, maxT = 0;
    const aspekTotals = {};
    const aspekSlugTotals = {};
    currentObsSoalList.forEach(s => {
        const m = parseFloat(s.nilai_maksimal || 0);
        maxT += m;
        const asp = (s.aspek || 'Umum').trim() || 'Umum';
        if (!aspekTotals[asp]) aspekTotals[asp] = { val: 0, max: 0 };
        aspekTotals[asp].max += m;

        const badge = document.getElementById('obs_q_score_' + s.id);
        if (badge) {
            badge.textContent = `Nilai: - / ${m}`;
            badge.className = 'shrink-0 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-slate-100 text-slate-600';
        }
    });

    document.querySelectorAll('[id^="obs_aspek_total_"]').forEach(el => {
        const mx = el.getAttribute('data-aspek-max') || '0';
        el.textContent = `Total: 0 / ${mx}`;
    });

    document.querySelectorAll('.obs-radio:checked').forEach(r => {
        const sid = r.getAttribute('data-soal-id');
        const asp = r.getAttribute('data-aspek') || 'Umum';
        const slug = r.getAttribute('data-aspek-slug') || '';
        const val = parseFloat(r.getAttribute('data-val') || 0);
        const max = parseFloat(r.getAttribute('data-max') || 0);
        total += val;
        if (!aspekTotals[asp]) aspekTotals[asp] = { val: 0, max: max };
        aspekTotals[asp].val += val;
        if (slug) aspekSlugTotals[slug] = (aspekSlugTotals[slug] || 0) + val;

        const badge = document.getElementById('obs_q_score_' + sid);
        if (badge) {
            badge.textContent = `Nilai: ${val} / ${max}`;
            badge.className = 'shrink-0 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300';
        }
    });

    Object.entries(aspekSlugTotals).forEach(([slug, sumVal]) => {
        const el = document.getElementById('obs_aspek_total_' + slug);
        if (el) {
            const mx = el.getAttribute('data-aspek-max') || '0';
            el.textContent = `Total: ${sumVal} / ${mx}`;
        }
    });

    const pct = maxT > 0 ? ((total / maxT) * 100) : 0;
    document.getElementById('obsLiveScoreText').textContent = `${total} / ${maxT} (${pct.toFixed(1)}%)`;
    let cat = 'Perlu Pendampingan Khusus';
    if (pct >= 85) cat = 'Sangat Siap / Sangat Baik';
    else if (pct >= 70) cat = 'Siap / Baik';
    else if (pct >= 55) cat = 'Cukup Siap (Perlu Penguatan)';
    document.getElementById('obsLiveCatText').textContent = cat;

    const aspekBar = document.getElementById('obsLiveAspekBar');
    if (aspekBar) {
        aspekBar.innerHTML = Object.entries(aspekTotals).map(([asp, st]) =>
            `<span class="px-2 py-0.5 rounded-lg bg-white border border-indigo-200 text-[11px] font-bold text-indigo-800">${asp}: ${st.val} / ${st.max}</span>`
        ).join('');
    }
}

function closeIsiObservasiModal() {
    document.getElementById('modalIsiObservasiSiswa').classList.add('hidden');
}
</script>
