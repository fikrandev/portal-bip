<?php
/**
 * Cetak Daftar Nilai — Halaman standalone A4 Landscape
 * Tanpa layout app (sidebar/header), langsung print-ready
 *
 * Variables: $group, $doc, $cpatpRows, $cpGroups, $totalLM,
 *            $siswa, $mapNilai, $semesterLabel
 */
$sekolahNama = 'SD Islam Terpadu Bina Insan Palu';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Nilai — <?= e($doc['mata_pelajaran']) ?> (<?= e($doc['tingkat_kelas']) ?>)</title>
    <style>
        /* ── Reset & Base ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 7pt; color: #1a1a1a; }

        /* ── Page Setup ── */
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
        }

        /* ── Print Styles ── */
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }

        /* ── Header ── */
        .print-header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 6px;
        }
        .print-header h1 {
            font-size: 14pt;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .print-header h2 {
            font-size: 10pt;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .info-table {
            width: 100%;
            font-size: 8pt;
            margin-bottom: 8px;
        }
        .info-table td {
            padding: 1px 4px;
            vertical-align: top;
        }
        .info-table .label {
            font-weight: 700;
            width: 180px;
            text-transform: uppercase;
        }

        /* ── Data Table ── */
        table.data-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5pt;
        }
        table.data-tbl th,
        table.data-tbl td {
            border: 0.5pt solid #333;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.2;
        }

        /* Header rows - Single clean neutral tone */
        table.data-tbl thead th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 6pt;
            text-transform: uppercase;
        }
        table.data-tbl thead tr:first-child th {
            background: #e2e8f0;
            font-size: 6.5pt;
        }

        /* Clean unified header tone */
        .th-cp { background: #e2e8f0 !important; }
        .th-tp { background: #eaedf2 !important; }
        .th-atp { background: #f8fafc !important; font-weight: 700; white-space: nowrap; }
        .th-rtp { background: #f1f5f9 !important; }
        .th-naf { background: #e2e8f0 !important; }
        .th-lm { background: #eaedf2 !important; }
        .th-nas { background: #e2e8f0 !important; }
        .th-sas { background: #f1f5f9 !important; }
        .th-nr { background: #e2e8f0 !important; font-weight: 900; }

        /* Body */
        table.data-tbl tbody td { font-size: 7pt; }
        table.data-tbl tbody td.nama-cell {
            text-align: left;
            padding-left: 4px;
            white-space: nowrap;
            overflow: hidden;
            max-width: 100px;
        }

        .val-cell { font-weight: 700; }
        .avg-cell { font-weight: 900; }

        /* Colors for grades */
        .grade-high { color: #166534; }
        .grade-mid { color: #92400e; }
        .grade-low { color: #991b1b; }

        /* ── Toolbar (screen only) ── */
        .toolbar {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,.3);
        }
        .toolbar button {
            background: #0ea5e9;
            color: #fff;
            border: none;
            padding: 8px 24px;
            font-weight: 700;
            font-size: 13px;
            border-radius: 8px;
            cursor: pointer;
        }
        .toolbar button:hover { background: #0284c7; }
        @media print {
            .toolbar { display: none !important; }
            body { padding-top: 0; }
        }
        @media screen {
            body { padding-top: 50px; }
        }

        /* Footer signature */
        .sign-area {
            margin-top: 12px;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        .sign-area table { border: none; }
        .sign-area td { border: none !important; padding: 3px 8px; text-align: center; }
    </style>
</head>
<body>
    <!-- Toolbar (screen only) -->
    <div class="toolbar no-print">
        <div>
            <strong>Daftar Nilai — <?= e($doc['mata_pelajaran']) ?></strong>
            <span style="opacity:.6;margin-left:8px;"><?= e($doc['tingkat_kelas']) ?> • <?= e($group['nama_tahun'] ?? '') ?></span>
        </div>
        <button onclick="window.print()">🖨️ Cetak</button>
    </div>

    <!-- Header -->
    <div class="print-header">
        <h1>Daftar Nilai</h1>
        <h2>Tahun Pelajaran <?= e($group['nama_tahun'] ?? '-') ?></h2>
    </div>

    <!-- Info -->
    <table class="info-table">
        <tr>
            <td class="label">Satuan Pendidikan</td>
            <td>: <?= $sekolahNama ?></td>
            <td class="label">Semester</td>
            <td>: <?= e($semesterLabel) ?></td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td>: <?= e($doc['tingkat_kelas']) ?></td>
            <td class="label">Guru Pengampu</td>
            <td>: <?= e($doc['guru_nama']) ?></td>
        </tr>
        <tr>
            <td class="label">Mata Pelajaran</td>
            <td>: <?= e($doc['mata_pelajaran']) ?></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-tbl">
        <thead>
            <!-- ═══ ROW 1: CP Categories ═══ -->
            <tr>
                <th rowspan="3" style="width:20px;">No</th>
                <th rowspan="3" style="width:18px;">L/P</th>
                <th rowspan="3" style="min-width:80px;">Nama</th>

                <?php foreach ($cpGroups as $cg): ?>
                    <th class="th-cp" colspan="<?= $cg['colspan'] ?>" title="<?= e($cg['full_text']) ?>">
                        <?= e($cg['label']) ?>
                    </th>
                <?php endforeach; ?>

                <th class="th-naf" rowspan="3" style="width:28px;">NA<br>(F)</th>

                <?php if ($totalLM > 0): ?>
                    <th class="th-lm" colspan="<?= $totalLM ?>">Sumatif LM</th>
                <?php endif; ?>

                <th class="th-nas" rowspan="3" style="width:28px;">NA<br>(S)</th>
                <th class="th-sas" rowspan="3" style="width:28px;">SAS<br>R</th>
                <th class="th-nr" rowspan="3" style="width:32px;">Nilai<br>Rapor</th>
            </tr>

            <!-- ═══ ROW 2: TP Groups ═══ -->
            <tr>
                <?php foreach ($cpGroups as $cg): ?>
                    <?php foreach ($cg['tps'] as $tp): ?>
                        <?php if ($tp['kktp_count'] > 0): ?>
                            <th class="th-tp" colspan="<?= $tp['kktp_count'] ?>" title="<?= e($tp['elemen']) ?>: <?= e($tp['tp_text']) ?>"><?= e($tp['tp_label']) ?></th>
                        <?php endif; ?>
                        <th class="th-rtp" rowspan="2" style="width:25px;">R.TP</th>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <?php for ($lm = 1; $lm <= $totalLM; $lm++): ?>
                    <th class="th-lm" rowspan="2" style="width:25px;">LM <?= $lm ?></th>
                <?php endfor; ?>
            </tr>

            <!-- ═══ ROW 3: ATPs ═══ -->
            <tr>
                <?php foreach ($cpGroups as $cg): ?>
                    <?php foreach ($cg['tps'] as $tp): ?>
                        <?php foreach ($tp['kktp_list'] as $kIdx => $kktp): ?>
                            <th class="th-atp" style="min-width:36px; padding: 2px 1px;" title="<?= e($kktp['kktp'] ?? '') ?>">
                                <?= e($kktp['atp_code']) ?>
                            </th>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($siswa as $sIdx => $s):
                $sId = (int)$s['id'];
                $jk = ($s['jenis_kelamin'] === 'L' || $s['jenis_kelamin'] === 'Laki-Laki') ? 'L' : 'P';

                // Pre-calculate R.TP values
                $rtpVals = [];
                foreach ($cpGroups as $cg) {
                    foreach ($cg['tps'] as $tp) {
                        $sum = 0; $cnt = 0;
                        foreach ($tp['kktp_list'] as $kIdx => $kktp) {
                            $relIdx = $tp['rowIdx'] * 100 + $kIdx;
                            $v = $mapNilai[$sId][$relIdx] ?? null;
                            if ($v !== null && $v !== '') {
                                $sum += (float)$v;
                                $cnt++;
                            }
                        }
                        $rtpVals[$tp['rowIdx']] = ($cnt > 0) ? ($sum / $cnt) : null;
                    }
                }

                // NA Formatif
                $fVals = array_filter($rtpVals, fn($v) => $v !== null);
                $naf = count($fVals) > 0 ? array_sum($fVals) / count($fVals) : null;

                // NA Sumatif
                $lmVals = [];
                for ($lm = 1; $lm <= $totalLM; $lm++) {
                    $v = $mapNilai[$sId][90000 + $lm] ?? null;
                    if ($v !== null && $v !== '') $lmVals[] = (float)$v;
                }
                $nas = count($lmVals) > 0 ? array_sum($lmVals) / count($lmVals) : null;

                // SAS
                $sasVal = $mapNilai[$sId][99000] ?? null;
                $sas = ($sasVal !== null && $sasVal !== '') ? (float)$sasVal : null;

                // Nilai Rapor
                $nrParts = array_filter([$naf, $nas, $sas], fn($v) => $v !== null);
                $nr = count($nrParts) > 0 ? array_sum($nrParts) / count($nrParts) : null;

                if (!function_exists('gradeClass')) {
                    function gradeClass($val) {
                        if ($val === null) return '';
                        if ($val >= 80) return 'grade-high';
                        if ($val >= 60) return 'grade-mid';
                        return 'grade-low';
                    }
                }
                if (!function_exists('fmtVal')) {
                    function fmtVal($val) {
                        return $val !== null ? number_format($val, 1) : '';
                    }
                }
            ?>
                <tr>
                    <td><?= $sIdx + 1 ?></td>
                    <td><?= $jk ?></td>
                    <td class="nama-cell"><?= e($s['nama']) ?></td>

                    <?php foreach ($cpGroups as $cg): ?>
                        <?php foreach ($cg['tps'] as $tp): ?>
                            <?php foreach ($tp['kktp_list'] as $kIdx => $kktp):
                                $relIdx = $tp['rowIdx'] * 100 + $kIdx;
                                $v = $mapNilai[$sId][$relIdx] ?? '';
                            ?>
                                <td class="val-cell"><?= e($v) ?></td>
                            <?php endforeach; ?>
                            <!-- R.TP -->
                            <td class="avg-cell <?= gradeClass($rtpVals[$tp['rowIdx']]) ?>"><?= fmtVal($rtpVals[$tp['rowIdx']]) ?></td>
                        <?php endforeach; ?>
                    <?php endforeach; ?>

                    <!-- NA (F) -->
                    <td class="avg-cell <?= gradeClass($naf) ?>"><?= fmtVal($naf) ?></td>

                    <!-- LM -->
                    <?php for ($lm = 1; $lm <= $totalLM; $lm++):
                        $lmV = $mapNilai[$sId][90000 + $lm] ?? '';
                    ?>
                        <td class="val-cell"><?= e($lmV) ?></td>
                    <?php endfor; ?>

                    <!-- NA (S) -->
                    <td class="avg-cell <?= gradeClass($nas) ?>"><?= fmtVal($nas) ?></td>
                    <!-- SAS -->
                    <td class="val-cell"><?= e($sasVal ?? '') ?></td>
                    <!-- Nilai Rapor -->
                    <td class="avg-cell <?= gradeClass($nr) ?>"><?= fmtVal($nr) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Signature area -->
    <div class="sign-area">
        <table style="width:100%;">
            <tr>
                <td style="width:50%;"></td>
                <td style="width:50%;">
                    Palu, _________________ 20___<br><br>
                    Guru Mata Pelajaran<br><br><br><br><br>
                    <strong><u><?= e($doc['guru_nama']) ?></u></strong><br>
                    <?php if (!empty($doc['guru_nip'])): ?>
                        NIP. <?= e($doc['guru_nip']) ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
