<?php
/**
 * Cetak Dokumen CP & ATP (Capaian Pembelajaran & Alur Tujuan Pembelajaran)
 * Format 5 Kolom: Elemen, Capaian Pembelajaran, Tujuan Pembelajaran, KKTP, Bulan
 * Font: Times New Roman (12pt), Margin: 0.5cm, Garis: 1px
 */
$cpatpRows = $konten['cpatp_rows'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CP & ATP - <?= e($item['mata_pelajaran']) ?> - <?= e($item['tingkat_kelas']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            box-sizing: border-box;
        }

        body { 
            font-family: 'Times New Roman', Times, serif; 
            font-size: 12pt;
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
            font-size: 11pt;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: top;
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
                font-size: 11pt !important;
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
                margin: 0 0 0 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }
            @page { 
                size: A4 landscape; 
                margin: 0.5cm 0.5cm 0.5cm 0.5cm; 
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (No Print) -->
    <div class="no-print bg-white border-b border-slate-200 px-6 py-3 sticky top-0 z-50 shadow-sm font-sans">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                    🖨️
                </span>
                <div>
                    <h1 class="text-sm font-extrabold text-slate-800 leading-tight">
                        Pratinjau Cetak CP & ATP (Landscape)
                    </h1>
                    <p class="text-xs text-slate-500">
                        Format: <strong>A4 Landscape</strong> &bull; Font: <strong>Times New Roman</strong> &bull; Margin: <strong>0.5 cm</strong> &bull; Garis: <strong>1px</strong>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.close()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-colors">
                    Tutup
                </button>
                <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs shadow-md shadow-indigo-600/20 flex items-center gap-1.5 cursor-pointer transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                    <span>Cetak Landscape (Print)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Screen Wrapper & Sheets -->
    <div class="screen-wrapper">
        <div class="page-sheet">
            
            <!-- Header Box Sesuai Format Resmi Sekolah -->
            <table class="header-box">
                <tr>
                    <!-- Logo Box -->
                    <td style="width: 15%; background-color: #dbeafe; padding: 4px 6px; text-align: center; vertical-align: middle;">
                        <?php if (!empty($unitProfile['logo_url'])): ?>
                            <img src="<?= url(ltrim($unitProfile['logo_url'], '/')) ?>" style="max-height: 80px; max-width: 96%; margin: 0 auto; display: block; object-fit: contain;" alt="Logo">
                        <?php else: ?>
                            <div style="font-size: 28pt; font-weight: bold; color: #0284c7; line-height: 1; letter-spacing: -0.5px; font-family: 'Arial', sans-serif;">BIP</div>
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
                    <td style="width: 47%; background-color: #bfdbfe; padding: 0; vertical-align: middle;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 11pt; color: #000;">
                            <tr>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Pengajar</td>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 72%;">: <?= e($item['guru_nama']) ?></td>
                            </tr>
                            <tr>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Kelas / Fase</td>
                                <td style="border: none; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; width: 72%;">: <?= e($item['tingkat_kelas']) ?> (<?= e($item['fase']) ?>)</td>
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
            <div style="text-align: center; margin-top: 24px; margin-bottom: 16px;">
                <h2 style="font-size: 13pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; letter-spacing: 0.5px; margin: 0; color: #000;">
                    CAPAIAN & ALUR TUJUAN PEMBELAJARAN (CP & ATP)
                </h2>
            </div>

            <!-- Tabel CP & ATP (5 Kolom: Elemen, CP, TP, KKTP, Bulan) -->
            <div>
                <table class="table-custom">
                    <thead>
                        <tr style="background-color: #dbeafe; text-align: center; font-weight: bold;">
                            <th style="width: 4%; text-align: center;">NO</th>
                            <th style="width: 14%; text-align: center;">ELEMEN</th>
                            <th style="width: 28%; text-align: center;">CAPAIAN PEMBELAJARAN</th>
                            <th style="width: 22%; text-align: center;">TUJUAN PEMBELAJARAN</th>
                            <th style="width: 20%; text-align: center;">KKTP</th>
                            <th style="width: 12%; text-align: center;">BULAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cpatpRows)): ?>
                            <tr><td colspan="6" style="text-align: center; padding: 8px;">Belum ada data CP & ATP.</td></tr>
                        <?php else: ?>
                            <?php foreach ($cpatpRows as $idx => $row): ?>
                                <?php
                                    $kktpList = $row['kktp_list'] ?? [['kktp' => '-', 'bulan' => '-']];
                                    $kktpCount = count($kktpList);

                                    // Hitung rowspan untuk bulan yang sama berurutan dalam elemen ini
                                    $bulanRowspans = [];
                                    $i = 0;
                                    while ($i < $kktpCount) {
                                        $currentBulan = trim((string)($kktpList[$i]['bulan'] ?? '-'));
                                        $span = 1;
                                        while (($i + $span) < $kktpCount && trim((string)($kktpList[$i + $span]['bulan'] ?? '-')) === $currentBulan) {
                                            $span++;
                                        }
                                        $bulanRowspans[$i] = $span;
                                        $i += $span;
                                    }
                                ?>
                                <?php foreach ($kktpList as $kIdx => $kItem): ?>
                                    <tr>
                                        <?php if ($kIdx === 0): ?>
                                            <td style="text-align: center; font-weight: bold;" rowspan="<?= $kktpCount ?>"><?= $idx + 1 ?>.</td>
                                            <td style="font-weight: bold;" rowspan="<?= $kktpCount ?>"><?= e($row['elemen'] ?? '-') ?></td>
                                            <td style="text-align: justify;" rowspan="<?= $kktpCount ?>"><?= nl2br(e($row['cp'] ?? '-')) ?></td>
                                            <td style="text-align: justify;" rowspan="<?= $kktpCount ?>"><?= nl2br(e($row['tp'] ?? '-')) ?></td>
                                        <?php endif; ?>
                                        <td><?= e($kItem['kktp'] ?? '-') ?></td>
                                        <?php if (isset($bulanRowspans[$kIdx])): ?>
                                            <td style="text-align: center; font-weight: bold; vertical-align: middle;" rowspan="<?= $bulanRowspans[$kIdx] ?>">
                                                <?= e($kItem['bulan'] ?? '-') ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tanda Tangan Resmi Pengajar & Kepala Sekolah -->
            <table style="width: 100%; margin-top: 24px; font-size: 12pt; font-weight: bold; color: #000; font-family: 'Times New Roman', Times, serif; page-break-inside: avoid;">
                <tr>
                    <!-- Tanda Tangan Kiri: Kepala Sekolah -->
                    <td style="width: 50%; vertical-align: top; text-align: center;">
                        <div>Mengetahui,</div>
                        <div style="margin-top: 1px;">Kepala Sekolah</div>
                        
                        <div style="height: 55px;"></div>
                        
                        <div style="text-decoration: underline;">
                            <?= e($unitProfile['kepala_sekolah']['nama'] ?? 'FENI, S.Pd.I') ?>
                        </div>
                        <?php if (!empty($unitProfile['kepala_sekolah']['nip'])): ?>
                            <div style="font-size: 11pt; font-weight: normal; margin-top: 2px;">
                                NIP. <?= e($unitProfile['kepala_sekolah']['nip']) ?>
                            </div>
                        <?php endif; ?>
                    </td>

                    <!-- Tanda Tangan Kanan: Guru Pengampu -->
                    <td style="width: 50%; vertical-align: top; text-align: center;">
                        <div>Palu, <?= date('d F Y') ?></div>
                        <div style="margin-top: 1px;">Guru Mata Pelajaran</div>
                        
                        <div style="height: 55px;"></div>
                        
                        <div style="text-decoration: underline;">
                            <?= e($item['guru_nama']) ?>
                        </div>
                        <?php if (!empty($item['guru_nip'])): ?>
                            <div style="font-size: 11pt; font-weight: normal; margin-top: 2px;">
                                NIP. <?= e($item['guru_nip']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('print') || urlParams.has('autoprint')) {
                setTimeout(() => window.print(), 500);
            }
        });
    </script>
</body>
</html>
