<?php
/**
 * HEB - Detail View
 */
$pekanRows = $konten['pekan_rows'] ?? [];
$totalPekanSemua = $konten['total_pekan_semua'] ?? 0;
$totalPekanNon = $konten['total_pekan_tidak_efektif'] ?? 0;
$totalPekanEfektif = $konten['total_pekan_efektif'] ?? 0;
$totalJpEfektif = $konten['total_jp_efektif'] ?? 0;
$jpPerMinggu = $konten['jp_per_minggu'] ?? 3;
$distribusi = $konten['distribusi_jp'] ?? [];

$statusBadge = [
    'draft' => ['label' => 'Draft', 'class' => 'bg-slate-100 text-slate-700 border-slate-300'],
    'diajukan' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'disetujui' => ['label' => 'Disetujui / Sah', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'ditolak' => ['label' => 'Perlu Revisi', 'class' => 'bg-rose-100 text-rose-800 border-rose-300']
][$item['status']] ?? ['label' => ucfirst($item['status']), 'class' => 'bg-slate-100 text-slate-700 border-slate-300'];
?>
<div class="max-w-5xl mx-auto space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <?php
                $itemUnit = $item['unit'] ?? 'SD';
                $unitList = PerangkatModel::getUnitList();
                $uBadge = $unitList[$itemUnit]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-300';
                $uIcon = $unitList[$itemUnit]['icon'] ?? '🏫';
                ?>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $uBadge ?>">
                    <?= $uIcon ?> Unit <?= e($itemUnit) ?>
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $statusBadge['class'] ?>">
                    <?= $statusBadge['label'] ?>
                </span>
                <span class="text-xs text-slate-400"><?= e($item['mata_pelajaran']) ?> • <?= e($item['tingkat_kelas']) ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight"><?= e($item['judul']) ?></h1>
            <p class="text-xs text-slate-500">Unit: <strong class="text-slate-700">Unit <?= e($itemUnit) ?></strong> • Guru Pengampu: <strong class="text-slate-700"><?= e($item['guru_nama']) ?></strong> <?= !empty($item['guru_nip']) ? '(' . e($item['guru_nip']) . ')' : '' ?></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= url('kelola-perangkat-pembelajaran/heb') ?>" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                ← Kembali
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/heb/cetak/{$item['id']}") ?>" target="_blank" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                Cetak HEB
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/heb/edit/{$item['id']}") ?>" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Edit Dokumen
            </a>
        </div>
    </div>

    <!-- Summary JP Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Pekan Efektif</p>
            <p class="text-2xl font-black text-cyan-700 mt-1"><?= $totalPekanEfektif ?></p>
            <p class="text-[11px] text-slate-500">Pekan Belajar</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Beban JP/Minggu</p>
            <p class="text-2xl font-black text-slate-800 mt-1"><?= $jpPerMinggu ?> JP</p>
            <p class="text-[11px] text-slate-500">Per Pertemuan</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-cyan-200 shadow-sm text-center bg-cyan-50/50">
            <p class="text-[10px] text-cyan-800 uppercase font-bold">Total Jam Efektif</p>
            <p class="text-2xl font-black text-cyan-900 mt-1"><?= $totalJpEfektif ?> JP</p>
            <p class="text-[11px] text-cyan-700">1 Semester</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-emerald-200 shadow-sm text-center bg-emerald-50/50">
            <p class="text-[10px] text-emerald-800 uppercase font-bold">JP KBM Materi</p>
            <p class="text-2xl font-black text-emerald-900 mt-1"><?= (int)($distribusi['jp_kbm'] ?? 0) ?> JP</p>
            <p class="text-[11px] text-emerald-700">Tatap Muka</p>
        </div>
    </div>

    <!-- Tabel Pekan Efektif -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Rincian Pekan per Bulan</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-4 font-bold">Bulan</th>
                        <th class="py-3 px-4 text-center">Total Pekan</th>
                        <th class="py-3 px-4 text-center text-rose-700 font-bold">Pekan Non-Efektif</th>
                        <th class="py-3 px-4 text-center text-cyan-800 font-bold">Pekan Efektif</th>
                        <th class="py-3 px-4">Keterangan Non-Efektif / Agenda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($pekanRows)): ?>
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada rincian pekan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pekanRows as $i => $b): ?>
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-3 px-3 text-center text-slate-400"><?= $i + 1 ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= e($b['bulan']) ?></td>
                                <td class="py-3 px-4 text-center font-mono font-semibold"><?= (int)($b['pekan_total'] ?? 0) ?> Pekan</td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-rose-600"><?= (int)($b['pekan_non_efektif'] ?? 0) ?> Pekan</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg bg-cyan-50 text-cyan-800 font-extrabold font-mono">
                                        <?= (int)($b['pekan_efektif'] ?? 0) ?> Pekan
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-500"><?= e($b['keterangan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-cyan-50/70 font-extrabold text-cyan-950 border-t-2 border-cyan-600">
                        <td colspan="2" class="py-3.5 px-4 text-right uppercase tracking-wider text-xs">Total Semester:</td>
                        <td class="py-3.5 px-4 text-center text-sm font-mono"><?= $totalPekanSemua ?> Pekan</td>
                        <td class="py-3.5 px-4 text-center text-sm font-mono text-rose-700"><?= $totalPekanNon ?> Pekan</td>
                        <td class="py-3.5 px-4 text-center text-base font-mono text-cyan-900"><?= $totalPekanEfektif ?> Pekan</td>
                        <td class="py-3.5 px-4 text-xs font-semibold text-cyan-800">Total KBM Efektif (<?= $totalJpEfektif ?> JP)</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Distribusi Waktu Table -->
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-700 uppercase mb-3">Distribusi Jam Pelajaran (JP)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500">1. Pembelajaran / Tatap Muka:</span>
                    <p class="text-base font-bold text-slate-800 mt-0.5"><?= (int)($distribusi['jp_kbm'] ?? 0) ?> Jam Pelajaran (JP)</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500">2. Asesmen Formatif & Sumatif:</span>
                    <p class="text-base font-bold text-slate-800 mt-0.5"><?= (int)($distribusi['jp_penilaian'] ?? 0) ?> Jam Pelajaran (JP)</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                    <span class="text-slate-500">3. Cadangan & Pengayaan:</span>
                    <p class="text-base font-bold text-slate-800 mt-0.5"><?= (int)($distribusi['jp_cadangan'] ?? 0) ?> Jam Pelajaran (JP)</p>
                </div>
            </div>
        </div>

        <?php if (!empty($item['file_lampiran'])): ?>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="text-base">📎</span>
                    <span>Berkas Lampiran Dokumen:</span>
                </div>
                <a href="<?= url($item['file_lampiran']) ?>" target="_blank" class="px-4 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 text-cyan-700 text-xs font-bold transition-colors">
                    Unduh File Lampiran
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Riwayat Log Verifikasi -->
    <?php if (!empty($logs)): ?>
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span>🕒</span> Riwayat Aktivitas & Pengesahan
            </h3>
            <div class="space-y-3">
                <?php foreach ($logs as $l): ?>
                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                        <span class="text-base">
                            <?= $l['aksi'] === 'setujui' ? '✅' : ($l['aksi'] === 'tolak' ? '❌' : ($l['aksi'] === 'ajukan' ? '📤' : '📝')) ?>
                        </span>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800"><?= e($l['user_nama']) ?> (<?= strtoupper($l['aksi']) ?>)</span>
                                <span class="text-[10px] text-slate-400"><?= date('d M Y H:i', strtotime($l['created_at'])) ?></span>
                            </div>
                            <?php if (!empty($l['catatan'])): ?>
                                <p class="text-slate-600 mt-1"><?= e($l['catatan']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
