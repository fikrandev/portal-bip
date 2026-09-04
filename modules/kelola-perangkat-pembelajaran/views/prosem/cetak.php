<?php
/**
 * Prosem - Cetak / Print View Layout Resmi (A4 Landscape)
 */
$prosemRows = $konten['prosem_rows'] ?? $konten['materi_list'] ?? [];
$totalJP = $konten['total_jp'] ?? 0;
$bulanList = $konten['bulan_list'] ?? $konten['bulan_names'] ?? ($item['semester'] === 'Genap'
    ? ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni']
    : ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']);

$ksNama = $unitProfile['kepala_sekolah']['nama'] ?? $unitProfile['nama_kepala_sekolah'] ?? 'Feni, S.Pd.I.';
$ksNip = $unitProfile['kepala_sekolah']['nip'] ?? $unitProfile['nip_kepala_sekolah'] ?? '';

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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Semester (Prosem) - <?= e($item['mata_pelajaran']) ?> <?= e($item['tingkat_kelas']) ?></title>
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
        
        .table-custom {
            border-collapse: collapse;
            width: 100%;
            margin-top: 4px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #000000;
            padding: 3px 4px;
            vertical-align: middle;
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
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs shadow-md shadow-purple-600/20 flex items-center gap-1.5 cursor-pointer transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                <span>Cetak Landscape (Print)</span>
            </button>
        </div>
    </div>

    <!-- Screen Wrapper & Sheets -->
    <div class="screen-wrapper">
        <div class="page-sheet">
            
            <!-- Header Box Sesuai Format Resmi Sekolah -->
            <table class="header-box">
                <tr>
                    <!-- Logo Box -->
                    <td style="width: 14%; background-color: #dbeafe; padding: 4px 6px; text-align: center; vertical-align: middle;">
                        <?php if (!empty($unitProfile['logo_url'])): ?>
                            <img src="<?= url(ltrim($unitProfile['logo_url'], '/')) ?>" style="max-height: 75px; max-width: 95%; margin: 0 auto; display: block; object-fit: contain;" alt="Logo">
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
                        <table style="width: 100%; border-collapse: collapse; font-size: 10.5pt; color: #000;">
                            <tr>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Pengajar</td>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 72%;">: <?= e($item['guru_nama']) ?></td>
                            </tr>
                            <tr>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Kelas / Fase</td>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 72%;">: <?= e($item['tingkat_kelas']) ?> <?= !empty($item['fase']) ? '(' . e($item['fase']) . ')' : '' ?></td>
                            </tr>
                            <tr>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Semester</td>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 72%;">: <?= e($item['semester']) ?></td>
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
            <div style="text-align: center; margin-top: 20px; margin-bottom: 14px;">
                <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                    PROGRAM SEMESTER (PROSEM)
                </div>
            </div>

            <!-- Matriks Distribusi Pekan KBM -->
            <table class="table-custom">
                <thead>
                    <tr style="background-color: #f1f5f9; text-align: center; font-weight: bold;">
                        <th rowspan="2" style="width: 28px; text-align: center;">NO</th>
                        <th rowspan="2" style="text-align: left; min-width: 250px;">MATERI POKOK / TUJUAN PEMBELAJARAN / ASESMEN</th>
                        <th rowspan="2" style="width: 36px; text-align: center;">JP</th>
                        <?php foreach ($bulanKolom as $m => $bInfo): ?>
                            <th colspan="<?= count($bInfo['weeks']) ?>" style="text-align: center;"><?= strtoupper(e($bInfo['nama'])) ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <tr style="background-color: #f8fafc; text-align: center; font-weight: bold;">
                        <?php foreach ($bulanKolom as $m => $bInfo): ?>
                            <?php foreach ($bInfo['weeks'] as $w): ?>
                                <?php 
                                $cellKey = "b{$m}_w{$w}";
                                $isAgendaCol = isset($agendaCols[$cellKey]);
                                ?>
                                <th style="width: 24px; padding: 2px 0; text-align: center; font-size: 9pt; <?= $isAgendaCol ? 'background-color: #fee2e2 !important; color: #991b1b !important; font-weight: bold;' : '' ?>" title="<?= $isAgendaCol ? e($agendaCols[$cellKey]['label']) : '' ?>"><?= $w ?></th>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($prosemRows)): ?>
                        <tr><td colspan="<?= $totalKolomPekan + 3 ?>" style="text-align: center; padding: 12px;">Tidak ada rincian materi.</td></tr>
                    <?php else: ?>
                        <?php $totalRowCount = count($prosemRows); ?>
                        <?php foreach ($prosemRows as $i => $row): ?>
                            <?php $isRowAgenda = isset($agendaRowIndices[$i]); ?>
                            <tr style="<?= $isRowAgenda ? 'background-color: #fff1f2;' : '' ?>">
                                <td style="text-align: center; font-weight: bold;"><?= $i + 1 ?></td>
                                <td style="padding: 3px 6px; font-weight: <?= $isRowAgenda ? 'bold' : 'normal' ?>;">
                                    <?= e($row['materi_pokok'] ?? $row['tp_materi'] ?? '') ?>
                                    <?php if ($isRowAgenda): ?>
                                        <span style="font-size: 7.5pt; font-weight: bold; color: #b91c1c; border: 1px solid #f87171; background-color: #fee2e2; padding: 1px 4px; border-radius: 3px; margin-left: 4px; text-transform: uppercase;">Agenda</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                    <?= (int)($row['alokasi_jp'] ?? 0) ?>
                                </td>
                                <?php foreach ($bulanKolom as $m => $bInfo): ?>
                                    <?php foreach ($bInfo['weeks'] as $w): ?>
                                        <?php
                                        $cellKey = "b{$m}_w{$w}";
                                        $val = $row['matriks'][$cellKey] ?? ($row['matriks'][$m][$w] ?? '');
                                        ?>
                                        <?php if (isset($agendaCols[$cellKey])): ?>
                                            <?php if ($i === 0): ?>
                                                <td rowspan="<?= $totalRowCount ?>" 
                                                    style="background-color: #dc2626 !important; color: #ffffff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; width: 24px; min-width: 24px; max-width: 28px; text-align: center; vertical-align: middle; padding: 4px 0; border: 1px solid #000000;"
                                                    title="<?= e($agendaCols[$cellKey]['label']) ?>">
                                                    <div style="writing-mode: vertical-rl; transform: rotate(180deg); text-orientation: mixed; white-space: nowrap; margin: 0 auto; font-size: 8pt; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; color: #ffffff !important;">
                                                        <?= e($agendaCols[$cellKey]['label']) ?>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <td style="text-align: center; font-weight: bold; font-family: monospace; font-size: 9pt; <?= !empty($val) ? 'background-color: #fef08a;' : '' ?>">
                                                <?= !empty($val) ? e($val) : '' ?>
                                            </td>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding-right: 8px;">JUMLAH TOTAL JAM PELAJARAN (JP):</td>
                        <td style="text-align: center; font-family: monospace; font-weight: bold;"><?= $totalJP ?></td>
                        <td colspan="<?= $totalKolomPekan ?>" style="padding-left: 8px; font-style: italic; color: #334155;">
                            Distribusi Pekan KBM Efektif Semester <?= e($item['semester']) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Tanda Tangan Pengesahan -->
            <table style="width: 100%; margin-top: 28px; border: none; font-size: 10.5pt; page-break-inside: avoid;">
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
                        <strong><u><?= e($item['guru_nama']) ?></u></strong><br>
                        <?= !empty($item['guru_nip']) ? 'NIP. ' . e($item['guru_nip']) : '' ?>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</body>
</html>
