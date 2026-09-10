<?php
/**
 * Daftar Nilai — Format Komprehensif
 * Multi-level header: CP → TP → ATP + Sumatif (LM, SAS, Nilai Rapor)
 *
 * Variables: $group, $doc, $cpatpRows, $cpGroups, $totalLM,
 *            $siswa, $mapNilai, $groupId, $cpatpDocId, $semesterLabel
 */

// Count total data columns for colspan reference
$totalAtpCols = 0;
foreach ($cpGroups as $cg) {
    $totalAtpCols += $cg['colspan'];
}
// Total cols = 3 (No, L/P, Nama) + totalAtpCols + 1 (NA F) + totalLM + 1 (NA S) + 1 (SAS) + 1 (Rapor)
$grandTotal = 3 + $totalAtpCols + 1 + $totalLM + 1 + 1 + 1;
?>
<style>
.custom-scrollbar::-webkit-scrollbar {
    height: 10px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 6px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
.atp-input, .lm-input, .sas-input {
    transition: background-color 0.25s ease, box-shadow 0.2s ease;
}
.dirty-cell {
    background-color: #fef9c3 !important;
}
.saved-cell {
    background-color: #dcfce7 !important;
}
</style>
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1.5">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Daftar Nilai Semester
                </span>
                <?php if (!empty($doc['fase'])): ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200"><?= e($doc['fase']) ?></span>
                <?php endif; ?>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Semester <?= e($semesterLabel) ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                <?= e($doc['mata_pelajaran']) ?> &mdash; <?= e($doc['tingkat_kelas']) ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                👨‍🏫 <?= e($doc['guru_nama']) ?> &bull; Tahun Ajaran <?= e($group['nama_tahun'] ?? '-') ?> &bull; Semester <?= e($semesterLabel) ?>
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="<?= url("kelola-nilai/input/{$groupId}/guru/{$doc['guru_id']}") ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                <span>Kembali</span>
            </a>
            <a href="<?= url("kelola-nilai/cetak/{$groupId}/doc/{$cpatpDocId}") ?>" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-sky-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536" /></svg>
                <span>Cetak A4</span>
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <form method="POST" action="<?= url("kelola-nilai/store/{$groupId}/doc/{$cpatpDocId}") ?>" id="form-input-nilai" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <?= CSRF::field() ?>

        <!-- Scroll Hint Banner -->
        <div class="px-5 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-3 text-xs text-slate-500">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-teal-100 text-teal-800 text-xs font-black">↔</span>
                <span>Geser tabel ke samping untuk melihat seluruh kolom. Kolom <strong>No</strong> &amp; <strong>Nama Siswa</strong> tetap terkunci (sticky).</span>
            </div>
            <span class="font-bold text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200 shrink-0 text-xs">
                <?= count($siswa) ?> Siswa Terdaftar
            </span>
        </div>

        <!-- Horizontal Scrollable Area -->
        <div class="overflow-x-auto w-full max-w-full custom-scrollbar">
            <table class="min-w-max w-full text-left text-[11px] text-slate-700 border-collapse" id="tbl-nilai">
                <thead>
                    <!-- ═══ HEADER ROW 1: CP / Lingkup Materi ═══ -->
                    <tr class="bg-slate-900 text-white text-[10px] uppercase tracking-wider">
                        <th class="px-2 py-3 text-center border border-slate-700 font-bold sticky left-0 bg-slate-900 z-20 w-[40px] min-w-[40px]" rowspan="3">No</th>
                        <th class="px-2 py-3 text-center border border-slate-700 font-bold sticky left-[40px] bg-slate-900 z-20 w-[35px] min-w-[35px]" rowspan="3">L/P</th>
                        <th class="px-3 py-3 border border-slate-700 font-bold sticky left-[75px] bg-slate-900 z-20 min-w-[170px] max-w-[200px] shadow-[2px_0_4px_rgba(0,0,0,0.2)]" rowspan="3">Nama Siswa</th>

                        <?php foreach ($cpGroups as $cpIdx => $cg): ?>
                            <th class="px-2 py-2.5 text-center border border-slate-700 font-black bg-slate-900" colspan="<?= $cg['colspan'] ?>" title="<?= e($cg['full_text']) ?>">
                                <?= e($cg['label']) ?>
                            </th>
                        <?php endforeach; ?>

                        <th class="px-2 py-2 text-center border border-slate-700 font-black bg-slate-900 text-teal-300" rowspan="3" style="min-width:45px">
                            <div class="leading-tight">NA<br>(F)</div>
                        </th>

                        <?php if ($totalLM > 0): ?>
                            <th class="px-2 py-2.5 text-center border border-slate-700 font-black bg-slate-900" colspan="<?= $totalLM ?>">
                                Sumatif Lingkup Materi
                            </th>
                        <?php endif; ?>

                        <th class="px-2 py-2 text-center border border-slate-700 font-black bg-slate-900 text-teal-300" rowspan="3" style="min-width:45px">
                            <div class="leading-tight">NA<br>(S)</div>
                        </th>
                        <th class="px-2 py-2 text-center border border-slate-700 font-black bg-slate-900 text-slate-200" rowspan="3" style="min-width:45px">
                            <div class="leading-tight">SAS<br>R</div>
                        </th>
                        <th class="px-2 py-2 text-center border border-slate-700 font-black bg-slate-950 text-teal-300 border-l-2 border-l-teal-500" rowspan="3" style="min-width:55px">
                            <div class="leading-tight">Nilai<br>Rapor</div>
                        </th>
                    </tr>

                    <!-- ═══ HEADER ROW 2: TP Groups ═══ -->
                    <tr class="bg-slate-800 text-slate-200 text-[9px] uppercase">
                        <?php foreach ($cpGroups as $cpIdx => $cg): ?>
                            <?php foreach ($cg['tps'] as $tp): ?>
                                <?php if ($tp['kktp_count'] > 0): ?>
                                    <th class="px-1 py-2 text-center border border-slate-700 font-bold bg-slate-800" colspan="<?= $tp['kktp_count'] ?>" title="<?= e($tp['elemen']) ?>: <?= e($tp['tp_text']) ?>">
                                        <?= e($tp['tp_label']) ?>
                                    </th>
                                <?php endif; ?>
                                <th class="px-1 py-2 text-center border border-slate-700 font-bold bg-slate-800 text-slate-300" rowspan="2" style="min-width:42px">
                                    <div class="leading-tight text-[8px]">R.TP</div>
                                </th>
                            <?php endforeach; ?>
                        <?php endforeach; ?>

                        <?php for ($lm = 1; $lm <= $totalLM; $lm++): ?>
                            <th class="px-1 py-2 text-center border border-slate-700 font-bold bg-slate-800 text-slate-200" rowspan="2" style="min-width:42px">
                                LM <?= $lm ?>
                            </th>
                        <?php endfor; ?>
                    </tr>

                    <!-- ═══ HEADER ROW 3: Individual ATPs ═══ -->
                    <tr class="bg-slate-700 text-slate-300 text-[8.5px] uppercase">
                        <?php foreach ($cpGroups as $cg): ?>
                            <?php foreach ($cg['tps'] as $tp): ?>
                                <?php foreach ($tp['kktp_list'] as $kIdx => $kktp): ?>
                                    <th class="px-1.5 py-1.5 text-center border border-slate-600 font-bold bg-slate-700 whitespace-nowrap" style="min-width:64px" title="<?= e($kktp['kktp'] ?? '') ?>">
                                        <?= e($kktp['atp_code']) ?>
                                    </th>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($siswa)): ?>
                        <tr>
                            <td colspan="<?= $grandTotal ?>" class="px-4 py-12 text-center text-slate-500 text-xs">
                                <div class="w-16 h-16 mx-auto bg-slate-100 rounded-full flex items-center justify-center text-3xl mb-3">🎓</div>
                                <p class="font-bold text-slate-700 text-sm">Belum ada data siswa</p>
                                <p class="text-slate-400 mt-1">Tidak ditemukan siswa aktif di kelas "<?= e($doc['tingkat_kelas'] ?? '') ?>"</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($siswa as $sIdx => $s):
                            $sId = (int)$s['id'];
                            $jk = ($s['jenis_kelamin'] === 'L' || $s['jenis_kelamin'] === 'Laki-Laki') ? 'L' : 'P';
                        ?>
                            <tr class="hover:bg-teal-50/20 transition-colors group" data-siswa="<?= $sId ?>">
                                <!-- No -->
                                <td class="px-2 py-1.5 text-center text-[10px] font-medium text-slate-500 border border-slate-200 sticky left-0 bg-white group-hover:bg-slate-50 z-10 w-[40px] min-w-[40px]"><?= $sIdx + 1 ?></td>
                                <!-- L/P -->
                                <td class="px-2 py-1.5 text-center text-[10px] font-bold border border-slate-200 sticky left-[40px] bg-white group-hover:bg-slate-50 z-10 w-[35px] min-w-[35px] <?= $jk === 'L' ? 'text-blue-600' : 'text-pink-600' ?>"><?= $jk ?></td>
                                <!-- Nama -->
                                <td class="px-3 py-1.5 text-[11px] font-semibold text-slate-800 border border-slate-200 sticky left-[75px] bg-white group-hover:bg-slate-50 z-10 truncate min-w-[170px] max-w-[200px] shadow-[2px_0_4px_rgba(0,0,0,0.06)]" title="<?= e($s['nama']) ?>"><?= e($s['nama']) ?></td>

                                <!-- ═══ ATP Inputs + R.TP per TP ═══ -->
                                <?php foreach ($cpGroups as $cg): ?>
                                    <?php foreach ($cg['tps'] as $tp): ?>
                                        <?php foreach ($tp['kktp_list'] as $kIdx => $kktp):
                                            $relIdx = $tp['rowIdx'] * 100 + $kIdx;
                                            $existVal = $mapNilai[$sId][$relIdx] ?? '';
                                        ?>
                                            <td class="px-0.5 py-0.5 text-center border border-slate-200 bg-white">
                                                <input type="number" step="0.01" min="0" max="100"
                                                       name="nilai[<?= $sId ?>][<?= $tp['rowIdx'] ?>_<?= $kIdx ?>]"
                                                       value="<?= e($existVal) ?>"
                                                       class="w-full px-1 py-1.5 text-center text-[11px] font-medium border-0 bg-transparent focus:bg-teal-50 focus:ring-1 focus:ring-teal-400 focus:outline-none rounded atp-input"
                                                       data-siswa="<?= $sId ?>"
                                                       data-tp="<?= $tp['rowIdx'] ?>"
                                                       placeholder="—">
                                            </td>
                                        <?php endforeach; ?>
                                        <!-- R.TP -->
                                        <td class="px-1 py-1.5 text-center border border-slate-200 bg-slate-50">
                                            <span class="rtp-cell text-[11px] font-black text-slate-500" data-siswa="<?= $sId ?>" data-tp="<?= $tp['rowIdx'] ?>">—</span>
                                        </td>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>

                                <!-- ═══ NA Formatif ═══ -->
                                <td class="px-1 py-1.5 text-center border border-slate-200 bg-slate-100/70">
                                    <span class="naf-cell text-[11px] font-black text-slate-800" data-siswa="<?= $sId ?>">—</span>
                                </td>

                                <!-- ═══ LM Inputs ═══ -->
                                <?php for ($lm = 1; $lm <= $totalLM; $lm++):
                                    $lmRelIdx = 90000 + $lm;
                                    $lmVal = $mapNilai[$sId][$lmRelIdx] ?? '';
                                ?>
                                    <td class="px-0.5 py-0.5 text-center border border-slate-200 bg-white">
                                        <input type="number" step="0.01" min="0" max="100"
                                               name="lm[<?= $sId ?>][<?= $lm ?>]"
                                               value="<?= e($lmVal) ?>"
                                               class="w-full px-1 py-1.5 text-center text-[11px] font-medium border-0 bg-transparent focus:bg-teal-50 focus:ring-1 focus:ring-teal-400 focus:outline-none rounded lm-input"
                                               data-siswa="<?= $sId ?>"
                                               placeholder="—">
                                    </td>
                                <?php endfor; ?>

                                <!-- ═══ NA Sumatif ═══ -->
                                <td class="px-1 py-1.5 text-center border border-slate-200 bg-slate-100/70">
                                    <span class="nas-cell text-[11px] font-black text-slate-800" data-siswa="<?= $sId ?>">—</span>
                                </td>

                                <!-- ═══ SAS ═══ -->
                                <?php $sasVal = $mapNilai[$sId][99000] ?? ''; ?>
                                <td class="px-0.5 py-0.5 text-center border border-slate-200 bg-white">
                                    <input type="number" step="0.01" min="0" max="100"
                                           name="sas[<?= $sId ?>]"
                                           value="<?= e($sasVal) ?>"
                                           class="w-full px-1 py-1.5 text-center text-[11px] font-medium border-0 bg-transparent focus:bg-teal-50 focus:ring-1 focus:ring-teal-400 focus:outline-none rounded sas-input"
                                           data-siswa="<?= $sId ?>"
                                           placeholder="—">
                                </td>

                                <!-- ═══ Nilai Rapor ═══ -->
                                <td class="px-1 py-1.5 text-center border border-slate-300 bg-teal-50/80 border-l-2 border-l-teal-600">
                                    <span class="nr-cell text-[11px] font-black text-teal-900" data-siswa="<?= $sId ?>">—</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($siswa)): ?>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 p-4 border-t border-slate-200 bg-slate-50/60 text-xs text-slate-500">
                <p>
                    Total: <strong><?= count($siswa) ?></strong> siswa &bull;
                    <strong><?= $totalAtpCols ?></strong> kolom penilaian ATP &bull;
                    <strong><?= $totalLM ?></strong> Lingkup Materi
                </p>
            </div>
        <?php endif; ?>
    </form>

    <!-- Keterangan Elemen CP & TP -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
            Keterangan Elemen CP &amp; TP
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($cpatpRows as $idx => $row): ?>
                <div class="flex gap-3 items-start p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/70">
                    <span class="px-2.5 py-1 rounded-xl bg-slate-200 text-slate-800 flex items-center justify-center text-[11px] font-black shrink-0 border border-slate-300">
                        <?= e($row['elemen'] ?? 'TP ' . ($idx + 1)) ?>
                    </span>
                    <div class="min-w-0 text-xs">
                        <p class="font-bold text-slate-800 leading-snug line-clamp-2">
                            CP: <?= e($row['cp'] ?? '-') ?>
                        </p>
                        <?php if (!empty($row['tp'])): ?>
                            <p class="text-slate-500 mt-1 line-clamp-2">
                                TP: <?= e($row['tp']) ?>
                            </p>
                        <?php endif; ?>
                        <p class="text-teal-700 font-bold mt-1 text-[10px]">
                            <?= count($row['kktp_list'] ?? []) ?> Indikator ATP
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════
// Auto-calculate R.TP, NA Formatif, NA Sumatif, Nilai Rapor
// ═══════════════════════════════════════════════════

function recalcRow(siswaId) {
    // 1. R.TP per TP
    const allAtps = document.querySelectorAll(`.atp-input[data-siswa="${siswaId}"]`);
    const tpSet = new Set();
    allAtps.forEach(el => tpSet.add(el.dataset.tp));

    let rtpValues = [];
    tpSet.forEach(tp => {
        const inputs = document.querySelectorAll(`.atp-input[data-siswa="${siswaId}"][data-tp="${tp}"]`);
        let sum = 0, count = 0;
        inputs.forEach(inp => {
            const v = parseFloat(inp.value);
            if (!isNaN(v)) { sum += v; count++; }
        });
        const cell = document.querySelector(`.rtp-cell[data-siswa="${siswaId}"][data-tp="${tp}"]`);
        if (cell) {
            if (count > 0) {
                const avg = sum / count;
                cell.textContent = avg.toFixed(1);
                cell.className = 'rtp-cell text-[11px] font-black ' + colorClass(avg);
                rtpValues.push(avg);
            } else {
                cell.textContent = '—';
                cell.className = 'rtp-cell text-[11px] font-black text-slate-400';
            }
        }
    });

    // 2. NA Formatif = avg of all R.TP
    const nafCell = document.querySelector(`.naf-cell[data-siswa="${siswaId}"]`);
    let naf = NaN;
    if (rtpValues.length > 0) {
        naf = rtpValues.reduce((a, b) => a + b, 0) / rtpValues.length;
        nafCell.textContent = naf.toFixed(1);
        nafCell.className = 'naf-cell text-[11px] font-black ' + colorClass(naf);
    } else {
        nafCell.textContent = '—';
        nafCell.className = 'naf-cell text-[11px] font-black text-slate-400';
    }

    // 3. NA Sumatif = avg of all LM
    const lmInputs = document.querySelectorAll(`.lm-input[data-siswa="${siswaId}"]`);
    let lmSum = 0, lmCount = 0;
    lmInputs.forEach(inp => {
        const v = parseFloat(inp.value);
        if (!isNaN(v)) { lmSum += v; lmCount++; }
    });
    const nasCell = document.querySelector(`.nas-cell[data-siswa="${siswaId}"]`);
    let nas = NaN;
    if (lmCount > 0) {
        nas = lmSum / lmCount;
        nasCell.textContent = nas.toFixed(1);
        nasCell.className = 'nas-cell text-[11px] font-black ' + colorClass(nas);
    } else {
        nasCell.textContent = '—';
        nasCell.className = 'nas-cell text-[11px] font-black text-slate-400';
    }

    // 4. Nilai Rapor = avg(F, S, SAS)
    const sasInput = document.querySelector(`.sas-input[data-siswa="${siswaId}"]`);
    const sasVal = parseFloat(sasInput?.value);
    const nrCell = document.querySelector(`.nr-cell[data-siswa="${siswaId}"]`);

    let parts = [];
    if (!isNaN(naf)) parts.push(naf);
    if (!isNaN(nas)) parts.push(nas);
    if (!isNaN(sasVal)) parts.push(sasVal);

    if (parts.length > 0) {
        const nr = parts.reduce((a, b) => a + b, 0) / parts.length;
        nrCell.textContent = nr.toFixed(1);
        nrCell.className = 'nr-cell text-[11px] font-black ' + colorClass(nr);
    } else {
        nrCell.textContent = '—';
        nrCell.className = 'nr-cell text-[11px] font-black text-slate-400';
    }
}

function colorClass(val) {
    if (val >= 75) return 'text-teal-700';
    if (val >= 60) return 'text-slate-700';
    return 'text-rose-600';
}

// ═══════════════════════════════════════════════════
// AutoSave Engine (Debounced Batch Upsert)
// Sangat ringan untuk beban server:
// - Debounce 600ms atau instan saat pindah cell (blur/Enter)
// - Mengirim batch perubahan dalam 1 request kecil
// - Indikator live status di pojok kanan atas
// ═══════════════════════════════════════════════════

const pendingQueue = new Map();
let autoSaveTimer = null;
let isSaving = false;
const AUTOSAVE_DELAY_MS = 600;
const AUTOSAVE_URL = '<?= url("kelola-nilai/autosave/{$groupId}/doc/{$cpatpDocId}") ?>';
const CSRF_TOKEN = '<?= CSRF::token() ?>';
const CSRF_NAME = '<?= CSRF_TOKEN_NAME ?>';

function setAutoSaveStatus(state, msg) {
    const badge = document.getElementById('autosave-status');
    if (!badge) return;

    if (state === 'saved') {
        badge.className = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition-all bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm select-none';
        badge.onclick = null;
        badge.innerHTML = `
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            <span id="autosave-text">${msg || 'Otomatis Tersimpan'}</span>
        `;
    } else if (state === 'saving') {
        badge.className = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition-all bg-amber-50 text-amber-700 border border-amber-200 shadow-sm select-none';
        badge.onclick = null;
        badge.innerHTML = `
            <svg class="w-4 h-4 text-amber-600 animate-spin shrink-0" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <span id="autosave-text">${msg || 'Menyimpan...'}</span>
        `;
    } else if (state === 'error') {
        badge.className = 'inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-xs font-bold transition-all bg-rose-50 text-rose-700 border border-rose-200 shadow-sm cursor-pointer select-none';
        badge.onclick = () => flushAutoSave(true);
        badge.innerHTML = `
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span id="autosave-text">${msg || 'Gagal simpan (Klik untuk coba lagi)'}</span>
        `;
    }
}

function getCellInfo(inp) {
    const siswaId = inp.dataset.siswa;
    let type = 'atp';
    let key = '';
    if (inp.classList.contains('atp-input')) {
        type = 'atp';
        const m = inp.name.match(/nilai\[\d+\]\[([^\]]+)\]/);
        key = m ? m[1] : '';
    } else if (inp.classList.contains('lm-input')) {
        type = 'lm';
        const m = inp.name.match(/lm\[\d+\]\[([^\]]+)\]/);
        key = m ? m[1] : '';
    } else if (inp.classList.contains('sas-input')) {
        type = 'sas';
    }
    return { siswa_id: siswaId, type: type, key: key, val: inp.value.trim() };
}

function queueCellSave(inp, immediate = false) {
    const info = getCellInfo(inp);
    const qKey = `${info.siswa_id}_${info.type}_${info.key}`;
    pendingQueue.set(qKey, info);

    inp.classList.add('dirty-cell');
    setAutoSaveStatus('saving', 'Menyimpan perubahan...');

    if (autoSaveTimer) clearTimeout(autoSaveTimer);

    if (immediate) {
        flushAutoSave();
    } else {
        autoSaveTimer = setTimeout(() => {
            flushAutoSave();
        }, AUTOSAVE_DELAY_MS);
    }
}

async function flushAutoSave(isRetry = false) {
    if (pendingQueue.size === 0) {
        setAutoSaveStatus('saved', 'Otomatis Tersimpan');
        return;
    }

    if (isSaving && !isRetry) return;
    isSaving = true;
    setAutoSaveStatus('saving', 'Menyimpan ke server...');

    const itemsToSend = Array.from(pendingQueue.values());
    const keysInFlight = Array.from(pendingQueue.keys());

    try {
        const payload = {
            [CSRF_NAME]: CSRF_TOKEN,
            items: itemsToSend
        };

        const res = await fetch(AUTOSAVE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (res.ok && data.success) {
            keysInFlight.forEach(k => pendingQueue.delete(k));
            document.querySelectorAll('.dirty-cell').forEach(el => {
                el.classList.remove('dirty-cell');
                el.classList.add('saved-cell');
                setTimeout(() => el.classList.remove('saved-cell'), 1000);
            });
            isSaving = false;
            setAutoSaveStatus('saved', `Otomatis Tersimpan (${data.timestamp || 'baru saja'})`);

            if (pendingQueue.size > 0) {
                flushAutoSave();
            }
        } else {
            throw new Error(data.message || 'Server gagal menyimpan');
        }
    } catch (err) {
        console.error('AutoSave Error:', err);
        isSaving = false;
        setAutoSaveStatus('error', 'Gagal menyimpan (Klik untuk coba lagi)');
    }
}

// ═══ Warn before leaving if pending items ═══
window.addEventListener('beforeunload', (e) => {
    if (pendingQueue.size > 0) {
        flushAutoSave(true);
        e.preventDefault();
        e.returnValue = 'Ada nilai yang sedang disimpan ke server. Yakin ingin keluar?';
    }
});

// ═══ Attach listeners ═══
document.querySelectorAll('.atp-input, .lm-input, .sas-input').forEach(inp => {
    // Saat mengetik: hitung rumus instan (0ms) & enqueue autosave (debounce)
    inp.addEventListener('input', () => {
        recalcRow(inp.dataset.siswa);
        queueCellSave(inp, false);
    });

    // Saat validasi angka & keluar cell: simpan instan
    inp.addEventListener('change', () => {
        let v = parseFloat(inp.value);
        if (!isNaN(v)) {
            if (v < 0) inp.value = 0;
            if (v > 100) inp.value = 100;
        }
        recalcRow(inp.dataset.siswa);
        queueCellSave(inp, true);
    });

    inp.addEventListener('blur', () => {
        if (inp.classList.contains('dirty-cell')) {
            queueCellSave(inp, true);
        }
    });
});

// ═══ Init on load ═══
document.addEventListener('DOMContentLoaded', () => {
    const siswaIds = new Set();
    document.querySelectorAll('[data-siswa]').forEach(el => siswaIds.add(el.dataset.siswa));
    siswaIds.forEach(id => recalcRow(id));
});

// ═══ Keyboard nav: Enter → next row same column ═══
document.getElementById('tbl-nilai')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const cur = e.target;
        if (!cur.matches('input[type="number"]')) return;
        
        // Save current cell immediately
        queueCellSave(cur, true);

        const td = cur.closest('td');
        const tr = td.closest('tr');
        const tdIdx = Array.from(tr.children).indexOf(td);
        const nextRow = tr.nextElementSibling;
        if (nextRow) {
            const nextTd = nextRow.children[tdIdx];
            const nextInput = nextTd?.querySelector('input');
            if (nextInput) { nextInput.focus(); nextInput.select(); }
        }
    }
});
</script>
