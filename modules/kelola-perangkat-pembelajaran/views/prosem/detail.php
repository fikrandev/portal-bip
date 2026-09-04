<?php
/**
 * Prosem - Detail View
 */
$prosemRows = $konten['prosem_rows'] ?? [];
$totalJP = $konten['total_jp'] ?? 0;
$bulanList = $konten['bulan_list'] ?? ($item['semester'] === 'Genap'
    ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
    : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']);

$statusBadge = [
    'draft' => ['label' => 'Draft', 'class' => 'bg-slate-100 text-slate-700 border-slate-300'],
    'diajukan' => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-amber-100 text-amber-800 border-amber-300'],
    'disetujui' => ['label' => 'Disetujui / Sah', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
    'ditolak' => ['label' => 'Perlu Revisi', 'class' => 'bg-rose-100 text-rose-800 border-rose-300']
][$item['status']] ?? ['label' => ucfirst($item['status']), 'class' => 'bg-slate-100 text-slate-700 border-slate-300'];
?>
<div class="max-w-7xl mx-auto space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border <?= $statusBadge['class'] ?>">
                    <?= $statusBadge['label'] ?>
                </span>
                <span class="text-xs text-slate-400"><?= e($item['mata_pelajaran']) ?> • <?= e($item['tingkat_kelas']) ?> (Semester <?= e($item['semester']) ?>)</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight"><?= e($item['judul']) ?></h1>
            <p class="text-xs text-slate-500">Guru Pengampu: <strong class="text-slate-700"><?= e($item['guru_nama']) ?></strong> <?= !empty($item['guru_nip']) ? '(' . e($item['guru_nip']) . ')' : '' ?></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?= !empty($groupId) ? url("kelola-perangkat-pembelajaran/prosem/group/{$groupId}") : url('kelola-perangkat-pembelajaran/prosem') ?>" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                ← Kembali
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/prosem/cetak/{$item['id']}") ?>" target="_blank" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                Cetak Prosem
            </a>
            <a href="<?= url("kelola-perangkat-pembelajaran/prosem/edit/{$item['id']}") ?>" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Edit Dokumen
            </a>
        </div>
    </div>

    <!-- Identitas & Ringkasan Prosem -->

    <!-- Matriks Prosem Detail -->
    <?php
    // Hitung kolom pekan aktif per bulan (hanya tampilkan pekan yang ada datanya)
    $bulanKolom = [];
    $totalKolomPekan = 0;
    foreach ($bulanList as $mIdx => $bNama) {
        $m = $mIdx + 1;
        $activeWeeks = [];
        for ($w = 1; $w <= 5; $w++) {
            $hasData = false;
            foreach ($prosemRows as $row) {
                $val = $row['matriks']["b{$m}_w{$w}"] ?? ($row['matriks'][$m][$w] ?? '');
                if ($val !== '' && $val !== null && (is_numeric($val) ? (float)$val > 0 : trim((string)$val) !== '')) {
                    $hasData = true;
                    break;
                }
            }
            if ($hasData) {
                $activeWeeks[] = $w;
            }
        }
        if (!empty($activeWeeks)) {
            $bulanKolom[$m] = [
                'nama' => $bNama,
                'weeks' => $activeWeeks
            ];
            $totalKolomPekan += count($activeWeeks);
        }
    }

    // Fallback jika belum ada data pekan sama sekali
    if (empty($bulanKolom)) {
        foreach ($bulanList as $mIdx => $bNama) {
            $bulanKolom[$mIdx + 1] = [
                'nama' => $bNama,
                'weeks' => [1, 2, 3, 4, 5]
            ];
        }
        $totalKolomPekan = 30;
    }

    // Deteksi Kolom Agenda / Asesmen Sumatif / Ujian / Libur untuk Vertical Red Rowspan
    $agendaCols = [];
    $agendaRowIndices = [];
    foreach ($prosemRows as $rIdx => $row) {
        $title = $row['materi_pokok'] ?? $row['tp_materi'] ?? '';
        $titleLower = strtolower($title);
        $isAgenda = (bool)preg_match('/\b(sts|sas|pts|pas|pat|ujian|libur|remedial|rapor|rapot|mpls)\b/i', $titleLower)
            || (strpos($titleLower, 'sumatif') !== false && (strpos($titleLower, 'tengah') !== false || strpos($titleLower, 'akhir') !== false || strpos($titleLower, 'semester') !== false || strpos($titleLower, 'ujian') !== false || strpos($titleLower, 'remedial') !== false))
            || (strpos($titleLower, 'asesmen') !== false && (strpos($titleLower, 'tengah') !== false || strpos($titleLower, 'akhir') !== false || strpos($titleLower, 'sumatif') !== false));

        if ($isAgenda) {
            $agendaRowIndices[$rIdx] = true;
            $label = strtoupper(trim($title));
            if (preg_match('/\b(tengah|sts|pts)\b/i', $titleLower)) {
                $label = 'SUMATIF TENGAH SEMESTER (STS)';
            } elseif (preg_match('/\b(akhir|sas|pas|pat)\b/i', $titleLower)) {
                $label = 'SUMATIF AKHIR SEMESTER (SAS)';
                if (strpos($titleLower, 'remedial') !== false) {
                    $label .= ' & REMEDIAL';
                }
            }
            
            foreach ($bulanKolom as $m => $bInfo) {
                foreach ($bInfo['weeks'] as $w) {
                    $cellKey = "b{$m}_w{$w}";
                    $val = $row['matriks'][$cellKey] ?? ($row['matriks'][$m][$w] ?? '');
                    if ($val !== '' && $val !== null && (is_numeric($val) ? (float)$val > 0 : trim((string)$val) !== '')) {
                        $agendaCols[$cellKey] = [
                            'label' => $label,
                            'full_title' => $title,
                            'row_idx' => $rIdx,
                            'type' => strpos($titleLower, 'libur') !== false ? 'libur' : 'sumatif'
                        ];
                    }
                }
            }
        }
    }
    ?>
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Matriks Distribusi Pekan KBM</h3>
            <span class="px-3 py-1 rounded-xl bg-purple-50 text-purple-800 font-extrabold text-xs">Total Alokasi: <?= $totalJP ?> JP</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border border-slate-200 border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 text-center font-bold text-[11px]">
                        <th rowspan="2" class="py-2 px-2 border border-slate-200 w-8">No</th>
                        <th rowspan="2" class="py-2 px-3 border border-slate-200 text-left">Materi Pokok / Agenda</th>
                        <th rowspan="2" class="py-2 px-2 border border-slate-200 w-16">JP</th>
                        <?php foreach ($bulanKolom as $m => $bInfo): ?>
                            <th colspan="<?= count($bInfo['weeks']) ?>" class="py-1 px-1 border border-slate-200"><?= e($bInfo['nama']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr class="bg-slate-50 text-slate-500 text-[10px] text-center font-semibold">
                        <?php foreach ($bulanKolom as $m => $bInfo): ?>
                            <?php foreach ($bInfo['weeks'] as $w): ?>
                                <?php $isAgendaCol = isset($agendaCols["b{$m}_w{$w}"]); ?>
                                <th class="py-1 px-0.5 border border-slate-200 w-7 <?= $isAgendaCol ? 'bg-red-100 text-red-700 font-extrabold' : '' ?>" title="<?= $isAgendaCol ? e($agendaCols["b{$m}_w{$w}"]['label']) : "Pekan {$w}" ?>"><?= $w ?></th>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($prosemRows)): ?>
                        <tr><td colspan="<?= $totalKolomPekan + 3 ?>" class="py-6 text-center text-slate-400">Belum ada data materi.</td></tr>
                    <?php else: ?>
                        <?php $totalRowCount = count($prosemRows); ?>
                        <?php foreach ($prosemRows as $i => $row): ?>
                            <?php $isRowAgenda = isset($agendaRowIndices[$i]); ?>
                            <tr class="<?= $isRowAgenda ? 'bg-rose-50/40 hover:bg-rose-50/70 font-semibold text-rose-950' : 'hover:bg-slate-50/70' ?>">
                                <td class="py-2 px-2 text-center <?= $isRowAgenda ? 'text-rose-500 font-bold' : 'text-slate-400' ?> border border-slate-200"><?= $i + 1 ?></td>
                                <td class="py-2 px-3 font-bold <?= $isRowAgenda ? 'text-rose-900' : 'text-slate-800' ?> border border-slate-200">
                                    <span><?= e($row['materi_pokok'] ?? $row['tp_materi'] ?? '') ?></span>
                                    <?php if ($isRowAgenda): ?>
                                        <span class="ml-2 px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-red-100 text-red-700 border border-red-200 uppercase tracking-wider">Agenda</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-2 text-center font-mono font-bold <?= $isRowAgenda ? 'text-rose-700' : 'text-purple-800' ?> border border-slate-200"><?= (int)($row['alokasi_jp'] ?? 0) ?></td>
                                <?php foreach ($bulanKolom as $m => $bInfo): ?>
                                    <?php foreach ($bInfo['weeks'] as $w): ?>
                                        <?php
                                        $cellKey = "b{$m}_w{$w}";
                                        $val = $row['matriks'][$cellKey] ?? ($row['matriks'][$m][$w] ?? '');
                                        ?>
                                        <?php if (isset($agendaCols[$cellKey])): ?>
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $totalRowCount ?>" 
                                                    class="text-center font-bold border border-red-600 p-0 shadow-inner" 
                                                    style="background-color: #dc2626 !important; color: #ffffff !important; width: 34px; min-width: 34px; max-width: 38px; vertical-align: middle;"
                                                    title="<?= e($agendaCols[$cellKey]['label']) ?>">
                                                    <div style="writing-mode: vertical-rl; transform: rotate(180deg); text-orientation: mixed; white-space: nowrap; margin: 0 auto; font-size: 10px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; padding: 12px 2px; color: #ffffff !important; text-shadow: 0 1px 2px rgba(0,0,0,0.35);">
                                                        <?= e($agendaCols[$cellKey]['label']) ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <td class="py-1 px-0.5 text-center border border-slate-200">
                                                <?php if (!empty($val)): ?>
                                                    <span class="inline-block w-5 h-5 leading-5 rounded bg-purple-600 text-white font-extrabold text-[10px] text-center font-mono shadow-sm">
                                                        <?= e($val) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-purple-50/70 font-extrabold text-purple-950 border-t-2 border-purple-600">
                        <td colspan="2" class="py-2.5 px-4 text-right uppercase tracking-wider text-xs">Total Alokasi Semester:</td>
                        <td class="py-2.5 px-2 text-center text-sm font-mono text-purple-900"><?= $totalJP ?> JP</td>
                        <td colspan="<?= $totalKolomPekan ?>" class="py-2.5 px-4 text-xs font-semibold text-purple-800">Distribusi KBM Efektif Selama 1 Semester</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (!empty($item['file_lampiran'])): ?>
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-slate-600">
                    <span class="text-base">📎</span>
                    <span>Berkas Lampiran Dokumen:</span>
                </div>
                <a href="<?= url($item['file_lampiran']) ?>" target="_blank" class="px-4 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold transition-colors">
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
