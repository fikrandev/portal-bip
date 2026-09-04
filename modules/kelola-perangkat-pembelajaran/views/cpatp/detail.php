<?php
/**
 * CP & ATP - Detail View
 * Format 5 Kolom: Elemen, Capaian Pembelajaran, Tujuan Pembelajaran, KKTP, Bulan
 */
$cpatpRows = $konten['cpatp_rows'] ?? [];
?>
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Unit <?= e($item['unit']) ?> &bull; <?= e($item['fase'] ?: 'Fase B') ?>
                </span>
                <?php if ($item['status'] === 'disetujui'): ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-100 text-emerald-700">Disetujui</span>
                <?php elseif ($item['status'] === 'diajukan'): ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-black bg-amber-100 text-amber-700">Menunggu Verifikasi</span>
                <?php elseif ($item['status'] === 'ditolak'): ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-black bg-rose-100 text-rose-700">Perlu Revisi</span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-black bg-slate-100 text-slate-600">Draft</span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight mt-2"><?= e($item['judul']) ?></h1>
            <p class="text-xs text-slate-500 mt-0.5"><?= e($item['mata_pelajaran']) ?> &bull; <?= e($item['tingkat_kelas']) ?> &bull; <?= e($item['semester']) ?> (<?= e($item['nama_tahun']) ?>)</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= url("kelola-perangkat-pembelajaran/cpatp/cetak/{$item['id']}") ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md shadow-sky-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                Cetak CP & ATP
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/cpatp/edit/{$item['id']}") ?>" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Edit Dokumen
            </a>
            <a href="<?= !empty($group) ? url("kelola-perangkat-pembelajaran/cpatp/group/{$group['id']}") : url('kelola-perangkat-pembelajaran/cpatp') ?>" class="px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Metadata Banner Card -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Mata Pelajaran</span>
            <p class="text-sm font-extrabold text-slate-900 mt-1"><?= e($item['mata_pelajaran']) ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Kelas & Fase</span>
            <p class="text-sm font-extrabold text-slate-900 mt-1"><?= e($item['tingkat_kelas']) ?> (<?= e($item['fase']) ?>)</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Guru Pengampu</span>
            <p class="text-sm font-extrabold text-slate-900 mt-1 truncate"><?= e($item['guru_nama']) ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase">Total Blok Elemen</span>
            <p class="text-sm font-black text-indigo-600 mt-1"><?= count($cpatpRows) ?> Elemen / TP</p>
        </div>
    </div>

    <!-- Tabel CP & ATP (Format 5 Kolom) -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-2">
            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
            Tabel Capaian & Alur Tujuan Pembelajaran (CP & ATP)
        </h2>
        <div class="overflow-x-auto border border-slate-200/80 rounded-2xl">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-indigo-50 text-indigo-950 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-3" style="width:15%">Elemen</th>
                        <th class="py-2.5 px-4" style="width:25%">Capaian Pembelajaran</th>
                        <th class="py-2.5 px-4" style="width:25%">Tujuan Pembelajaran</th>
                        <th class="py-2.5 px-4" style="width:22%">KKTP</th>
                        <th class="py-2.5 px-3" style="width:10%">Bulan & Pekan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($cpatpRows)): ?>
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada data CP & ATP yang diinput.</td></tr>
                    <?php else: ?>
                        <?php foreach ($cpatpRows as $idx => $row): ?>
                            <?php
                                $kktpList = $row['kktp_list'] ?? [['kktp' => '-', 'bulan' => '-']];
                                $kktpCount = count($kktpList);
                            ?>
                            <?php foreach ($kktpList as $kIdx => $kItem): ?>
                                <tr class="<?= $kIdx === 0 ? 'border-t-2 border-indigo-200' : '' ?>">
                                    <?php if ($kIdx === 0): ?>
                                        <td class="py-3 px-3 text-center font-bold text-slate-400" rowspan="<?= $kktpCount ?>"><?= $idx + 1 ?></td>
                                        <td class="py-3 px-3 font-bold text-slate-900" rowspan="<?= $kktpCount ?>"><?= e($row['elemen'] ?? '-') ?></td>
                                        <td class="py-3 px-4 leading-relaxed" rowspan="<?= $kktpCount ?>"><?= nl2br(e($row['cp'] ?? '-')) ?></td>
                                        <td class="py-3 px-4 leading-relaxed" rowspan="<?= $kktpCount ?>"><?= nl2br(e($row['tp'] ?? '-')) ?></td>
                                    <?php endif; ?>
                                    <td class="py-2.5 px-4 text-slate-600"><?= e($kItem['kktp'] ?? '-') ?></td>
                                    <td class="py-2.5 px-3 font-semibold text-indigo-700">
                                        <?= e($kItem['bulan'] ?? '-') ?> 
                                        <?php
                                            $pVal = trim((string)($kItem['pekan'] ?? ''));
                                            if ($pVal !== '' && !str_starts_with(strtolower($pVal), 'pekan')) {
                                                $pVal = 'Pekan ' . $pVal;
                                            }
                                        ?>
                                        <span class="block text-[10px] text-slate-400 font-normal"><?= e($pVal ?: 'Pekan 1') ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- History Log & Verifikasi Box (Untuk Kepala Sekolah / Kurikulum) -->
    <?php if ($can_approve && $item['status'] === 'diajukan'): ?>
        <div class="bg-white rounded-3xl p-6 border-2 border-indigo-200 shadow-md space-y-4">
            <h2 class="text-sm font-bold text-indigo-900 uppercase tracking-wider flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                Persetujuan & Verifikasi Dokumen
            </h2>
            <p class="text-xs text-slate-600">Sebagai Verifikator / Kepala Sekolah, Anda dapat menyetujui atau meminta revisi atas dokumen CP & ATP ini.</p>
            
            <div class="flex items-center gap-3 pt-2">
                <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/approve/{$item['id']}") ?>">
                    <?= CSRF::field() ?>
                    <button type="submit" onclick="return confirm('Setujui dokumen CP & ATP ini?');" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all">
                        ✓ Setujui Dokumen
                    </button>
                </form>

                <button type="button" onclick="document.getElementById('reject-box').classList.toggle('hidden')" class="px-5 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-500/20 transition-all">
                    ✕ Minta Revisi
                </button>
            </div>

            <div id="reject-box" class="hidden pt-3">
                <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/reject/{$item['id']}") ?>" class="space-y-3">
                    <?= CSRF::field() ?>
                    <textarea name="catatan_revisi" rows="2" required placeholder="Tuliskan catatan perbaikan atau revisi..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs transition-colors">
                        Kirim Catatan Revisi
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
