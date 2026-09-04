<?php
/**
 * Prota - Detail View
 */
$materiRows = $konten['materi_rows'] ?? [];
$totalSmt1 = $konten['total_jp_smt1'] ?? 0;
$totalSmt2 = $konten['total_jp_smt2'] ?? 0;
$totalTahun = $konten['total_jp_tahun'] ?? ($totalSmt1 + $totalSmt2);

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
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $statusBadge['class'] ?>">
                    <?= $statusBadge['label'] ?>
                </span>
                <span class="text-xs text-slate-400"><?= e($item['mata_pelajaran']) ?> • <?= e($item['tingkat_kelas']) ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight"><?= e($item['judul']) ?></h1>
            <p class="text-xs text-slate-500">Guru Pengampu: <strong class="text-slate-700"><?= e($item['guru_nama']) ?></strong> <?= !empty($item['guru_nip']) ? '(' . e($item['guru_nip']) . ')' : '' ?></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= url('kelola-perangkat-pembelajaran/prota') ?>" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                ← Kembali
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/prota/cetak/{$item['id']}") ?>" target="_blank" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                Cetak Prota
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/prota/edit/{$item['id']}") ?>" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Edit Dokumen
            </a>
        </div>
    </div>

    <!-- Summary JP Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Alokasi Semester 1</p>
            <p class="text-2xl font-black text-indigo-700 mt-1"><?= $totalSmt1 ?> JP</p>
            <p class="text-[11px] text-slate-500">Semester Ganjil</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm text-center">
            <p class="text-[10px] text-slate-400 uppercase font-bold">Alokasi Semester 2</p>
            <p class="text-2xl font-black text-indigo-700 mt-1"><?= $totalSmt2 ?> JP</p>
            <p class="text-[11px] text-slate-500">Semester Genap</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-indigo-200 shadow-sm text-center bg-indigo-50/50">
            <p class="text-[10px] text-indigo-800 uppercase font-bold">Total Alokasi 1 Tahun</p>
            <p class="text-2xl font-black text-indigo-900 mt-1"><?= $totalTahun ?> JP</p>
            <p class="text-[11px] text-indigo-700">Tahun Ajaran Penuh</p>
        </div>
    </div>

    <!-- Deskripsi & Tabel Prota -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
        <?php if (!empty($konten['capaian_umum'])): ?>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-700">
                <p class="font-bold text-slate-800 mb-1">Capaian Pembelajaran (CP) Umum:</p>
                <p class="leading-relaxed"><?= nl2br(e($konten['capaian_umum'])) ?></p>
            </div>
        <?php endif; ?>

        <div>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-3">Tabel Pemetaan Materi & Alokasi JP</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-4 w-1/4 font-bold">Capaian / Elemen (CP/KD)</th>
                            <th class="py-3 px-4 w-1/3 font-bold">Materi Pokok / Bab / Topik</th>
                            <th class="py-3 px-3 text-center w-24">JP Smt 1</th>
                            <th class="py-3 px-3 text-center w-24">JP Smt 2</th>
                            <th class="py-3 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <?php if (empty($materiRows)): ?>
                            <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada rincian materi.</td></tr>
                        <?php else: ?>
                            <?php foreach ($materiRows as $i => $m): ?>
                                <tr class="hover:bg-slate-50/70">
                                    <td class="py-3 px-3 text-center text-slate-400"><?= $i + 1 ?></td>
                                    <td class="py-3 px-4 font-semibold text-slate-600"><?= e($m['cp_kd'] ?? '-') ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-800"><?= e($m['materi_pokok']) ?></td>
                                    <td class="py-3 px-3 text-center font-mono font-bold text-indigo-700"><?= (int)($m['jp_smt1'] ?? 0) ?> JP</td>
                                    <td class="py-3 px-3 text-center font-mono font-bold text-indigo-700"><?= (int)($m['jp_smt2'] ?? 0) ?> JP</td>
                                    <td class="py-3 px-4 text-slate-500"><?= e($m['keterangan'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-indigo-50/70 font-extrabold text-indigo-950 border-t-2 border-indigo-600">
                            <td colspan="3" class="py-3.5 px-4 text-right uppercase tracking-wider text-xs">Total Jam Pelajaran:</td>
                            <td class="py-3.5 px-3 text-center text-sm font-mono text-indigo-900"><?= $totalSmt1 ?> JP</td>
                            <td class="py-3.5 px-3 text-center text-sm font-mono text-indigo-900"><?= $totalSmt2 ?> JP</td>
                            <td class="py-3.5 px-4 text-xs font-bold text-indigo-800">Total 1 Tahun: <?= $totalTahun ?> JP</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <?php if (!empty($item['file_lampiran'])): ?>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="text-base">📎</span>
                    <span>Berkas Lampiran Dokumen:</span>
                </div>
                <a href="<?= url($item['file_lampiran']) ?>" target="_blank" class="px-4 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors">
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
