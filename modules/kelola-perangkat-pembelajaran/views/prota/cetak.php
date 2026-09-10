<?php
/**
 * Prota - Cetak Landscape A4 Resmi
 * Kop mengikuti format resmi Prosem (Header Box Logo + Mapel + Info Pengajar)
 * Format Matriks Kurikulum Merdeka (NO, TP, ATP, JML JP, SMT)
 */
$protaRows = $konten['prota_rows'] ?? [];

// Fallback jika data format lama
if (empty($protaRows) && !empty($konten['materi_list'])) {
    $legacyList = $konten['materi_list'];
    $rNo = 1;
    foreach ($legacyList as $l) {
        $jp1 = (int)($l['jp_smt1'] ?? 0);
        $jp2 = (int)($l['jp_smt2'] ?? 0);
        $smt = ($jp2 > 0 && $jp1 == 0) ? 2 : 1;
        $atpJp = $smt == 1 ? ($jp1 ?: 2) : ($jp2 ?: 2);

        $protaRows[] = [
            'no' => $rNo++,
            'tp' => $l['cp_kd'] ?: $l['materi_pokok'],
            'atp_list' => [
                ['atp' => $l['materi_pokok'], 'jp' => $atpJp]
            ],
            'penilaian_harian' => [
                'label' => 'Penilaian Harian',
                'jp' => 4
            ],
            'semester' => $smt
        ];
    }
}

// Pisahkan per semester untuk merge vertikal kolom SMT
$rowsSmt1 = [];
$rowsSmt2 = [];
foreach ($protaRows as $r) {
    if ((int)($r['semester'] ?? 1) === 2) {
        $rowsSmt2[] = $r;
    } else {
        $rowsSmt1[] = $r;
    }
}

$jpSmt1 = (int)($konten['total_jp_smt1'] ?? 0);
$jpSmt2 = (int)($konten['total_jp_smt2'] ?? 0);
$jpTotal = (int)($konten['total_jp_tahun'] ?? ($jpSmt1 + $jpSmt2));

// Profil Sekolah & Kepala Sekolah
$unitKey = $item['unit'] ?? 'SD';
if (!isset($unitProfile) || empty($unitProfile)) {
    $unitProfile = \PerangkatController::getUnitProfile($unitKey);
}
$ksNama = $unitProfile['kepala_sekolah']['nama'] ?? $unitProfile['nama_kepala_sekolah'] ?? 'Kepala Sekolah';
$ksNip = $unitProfile['kepala_sekolah']['nip'] ?? $unitProfile['nip_kepala_sekolah'] ?? '';
$namaSekolah = $unitProfile['nama'] ?? 'BINA INSAN PALU';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Program Tahunan - <?= e($item['mata_pelajaran']) ?> <?= e($item['tingkat_kelas']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            line-height: 1.25;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .header-box {
            border: 1px solid #000000;
            width: 100%;
            border-collapse: collapse;
            font-family: 'Times New Roman', Times, serif;
        }
        .header-box td {
            border: 1px solid #000000;
            vertical-align: middle;
        }

        table.prota-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            border: 1px solid #000;
            margin-top: 4px;
        }
        table.prota-table th, table.prota-table td {
            border: 1px solid #000;
            padding: 3px 5px;
        }
        table.prota-table thead th {
            background-color: #d7f3f6 !important;
            color: #000;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9.5pt;
        }

        /* Screen Preview Styling */
        @media screen {
            .screen-wrapper {
                padding: 24px 16px;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 28px;
            }
            .page-sheet {
                background: #ffffff;
                width: 297mm;
                min-height: 210mm;
                padding: 10mm 12mm 10mm 12mm;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                border-radius: 6px;
            }
        }

        /* Print Media Styling */
        @media print {
            .no-print { 
                display: none !important; 
            }
            html, body { 
                background: #ffffff !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 100% !important;
                color: #000000 !important;
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 10pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .screen-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                width: 100% !important;
            }
            .page-sheet {
                background: #ffffff !important;
                width: 100% !important;
                min-height: auto !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }
            @page { 
                size: A4 landscape; 
                margin: 0.6cm 0.6cm 0.6cm 0.6cm; 
            }
        }
    </style>
</head>
<body>

    <!-- Floating Print Action Bar -->
    <div class="no-print fixed top-4 right-4 z-50 flex items-center gap-3">
        <div class="bg-white/95 backdrop-blur shadow-xl border border-slate-200 rounded-2xl p-2 flex items-center gap-2">
            <button onclick="window.close()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-colors">
                Tutup
            </button>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs shadow-md shadow-indigo-600/20 flex items-center gap-1.5 cursor-pointer transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                <span>Cetak Landscape (Print)</span>
            </button>
        </div>
    </div>

    <!-- Screen Wrapper & Sheet -->
    <div class="screen-wrapper">
        <div class="page-sheet">

            <!-- Header Box Kop Sesuai Format Resmi Prosem -->
            <table class="header-box">
                <tr>
                    <!-- Logo Box -->
                    <td style="width: 14%; background-color: #dbeafe; padding: 4px 6px; text-align: center; vertical-align: middle;">
                        <?php if (!empty($unitProfile['logo_url'])): ?>
                            <img src="<?= function_exists('url') ? url(ltrim($unitProfile['logo_url'], '/')) : '/' . ltrim($unitProfile['logo_url'], '/') ?>" style="max-height: 75px; max-width: 95%; margin: 0 auto; display: block; object-fit: contain;" alt="Logo">
                        <?php else: ?>
                            <div style="font-size: 26pt; font-weight: bold; color: #0284c7; line-height: 1; letter-spacing: -0.5px; font-family: 'Arial', sans-serif;">BIP</div>
                        <?php endif; ?>
                    </td>

                    <!-- Box Tengah: Mata Pelajaran -->
                    <td style="width: 38%; background-color: #bfdbfe; padding: 6px 8px; text-align: center; vertical-align: middle;">
                        <div style="font-size: 11pt; font-weight: bold; color: #000; text-transform: uppercase;">Mata Pelajaran</div>
                        <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin-top: 2px; color: #000; line-height: 1.2;">
                            <?= e($item['mata_pelajaran']) ?>
                        </div>
                    </td>

                    <!-- Box Kanan: Data Pengajar -->
                    <td style="width: 48%; background-color: #bfdbfe; padding: 0; vertical-align: middle;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 10pt; color: #000;">
                            <tr>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Pengajar</td>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 72%;">: <?= e($item['guru_nama'] ?: 'Guru Pengampu') ?></td>
                            </tr>
                            <tr>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Kelas / Fase</td>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 72%;">: <?= e($item['tingkat_kelas']) ?> <?= !empty($item['fase']) ? '(' . e($item['fase']) . ')' : '' ?></td>
                            </tr>
                            <tr>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Semester</td>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 72%;">: 1 (Ganjil) & 2 (Genap)</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Tahun Ajaran</td>
                                <td style="border: none; padding: 2px 6px; font-weight: bold; width: 72%;">: <?= e(trim(preg_replace('/\s*(Ganjil|Genap)/i', '', $item['nama_tahun'] ?? '2026/2027'))) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Judul Dokumen -->
            <div style="text-align: center; margin-top: 18px; margin-bottom: 12px;">
                <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                    PROGRAM TAHUNAN (PROTA)
                </div>
            </div>

            <!-- Tabel Matriks Persis Gambar Referensi -->
            <table class="prota-table">
                <thead>
                    <tr>
                        <th style="width: 38px;">NO</th>
                        <th style="width: 38%; text-align: center;">TP</th>
                        <th style="text-align: center;">ATP</th>
                        <th style="width: 55px; text-align: center;">JML<br>JP</th>
                        <th style="width: 48px; text-align: center;">SMT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $renderSemesterRows = function($rows, $smtNumber) {
                        if (empty($rows)) return;
                        
                        $totalHtmlRows = 0;
                        foreach ($rows as $tp) {
                            $atpCount = max(1, count($tp['atp_list'] ?? []));
                            $totalHtmlRows += $atpCount;
                            if (!empty($tp['penilaian_harian'])) {
                                $totalHtmlRows += 1;
                            }
                        }

                        $isFirstRowOfSemester = true;

                        foreach ($rows as $rIdx => $tp) {
                            $atpList = !empty($tp['atp_list']) ? $tp['atp_list'] : [['atp' => '-', 'jp' => 0]];
                            $ph = $tp['penilaian_harian'] ?? null;
                            $atpCount = count($atpList);
                            $tpRowspan = $atpCount;

                            foreach ($atpList as $aIdx => $atp) {
                                echo '<tr>';

                                if ($aIdx === 0) {
                                    echo '<td style="text-align: center; font-weight: bold; vertical-align: middle;" rowspan="' . $tpRowspan . '">' . e($tp['no'] ?? ($rIdx + 1)) . '</td>';
                                    echo '<td style="vertical-align: middle; text-align: justify; line-height: 1.35;" rowspan="' . $tpRowspan . '">' . nl2br(e($tp['tp'])) . '</td>';
                                }

                                echo '<td style="line-height: 1.35;">' . e($atp['atp']) . '</td>';
                                echo '<td style="text-align: center; font-weight: bold; vertical-align: middle;">' . (int)$atp['jp'] . '</td>';

                                if ($isFirstRowOfSemester) {
                                    echo '<td style="text-align: center; font-weight: bold; font-size: 11pt; vertical-align: middle;" rowspan="' . $totalHtmlRows . '">' . $smtNumber . '</td>';
                                    $isFirstRowOfSemester = false;
                                }

                                echo '</tr>';
                            }

                            // Penilaian Harian Row
                            if ($ph && !empty($ph['label'])) {
                                echo '<tr style="background-color: #fafafa;">';
                                echo '<td style="text-align: center; font-weight: bold;" colspan="2">' . e($ph['label']) . '</td>';
                                echo '<td style="text-align: center; font-weight: bold;">' . (int)($ph['jp'] ?? 4) . '</td>';
                                echo '</tr>';
                            }
                        }
                    };

                    // Semester 1
                    $renderSemesterRows($rowsSmt1, 1);

                    // Semester 2
                    $renderSemesterRows($rowsSmt2, 2);
                    ?>

                    <!-- Rekapitulasi Total JP -->
                    <tr style="background-color: #d7f3f6 !important; font-weight: bold;">
                        <td colspan="3" style="text-align: right; font-weight: bold; padding-right: 8px;">
                            TOTAL ALOKASI WAKTU SEMESTER 1 (GANJIL)
                        </td>
                        <td style="text-align: center; font-weight: bold;"><?= $jpSmt1 ?></td>
                        <td style="text-align: center;">JP</td>
                    </tr>
                    <tr style="background-color: #d7f3f6 !important; font-weight: bold;">
                        <td colspan="3" style="text-align: right; font-weight: bold; padding-right: 8px;">
                            TOTAL ALOKASI WAKTU SEMESTER 2 (GENAP)
                        </td>
                        <td style="text-align: center; font-weight: bold;"><?= $jpSmt2 ?></td>
                        <td style="text-align: center;">JP</td>
                    </tr>
                    <tr style="background-color: #e0f2fe !important; font-weight: bold; border-top: 2px solid #000;">
                        <td colspan="3" style="text-align: right; font-weight: bold; padding-right: 8px; font-size: 9.5pt;">
                            TOTAL ALOKASI WAKTU 1 TAHUN AJARAN
                        </td>
                        <td style="text-align: center; font-weight: bold; font-size: 10pt;"><?= $jpTotal ?></td>
                        <td style="text-align: center; font-weight: bold;">JP</td>
                    </tr>
                </tbody>
            </table>

            <!-- Tanda Tangan Pengesahan Sesuai Format Prosem -->
            <table style="width: 100%; margin-top: 28px; border: none; font-size: 10pt; page-break-inside: avoid;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                        Mengetahui,<br>
                        Kepala Sekolah<br>
                        <div style="height: 65px;"></div>
                        <strong><u><?= e($ksNama) ?></u></strong><br>
                        <?= !empty($ksNip) ? 'NIP. ' . e($ksNip) : '' ?>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                        Palu, <?= date('d F Y', strtotime($item['created_at'])) ?><br>
                        Guru Pengampu Mata Pelajaran<br>
                        <div style="height: 65px;"></div>
                        <strong><u><?= e($item['guru_nama'] ?: 'Guru Pengampu') ?></u></strong><br>
                        <?= !empty($item['guru_nip']) ? 'NIP. ' . e($item['guru_nip']) : '' ?>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</body>
</html>
