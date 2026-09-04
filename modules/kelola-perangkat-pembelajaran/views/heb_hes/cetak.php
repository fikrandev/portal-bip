<?php
/**
 * Cetak Dokumen Rincian Hari Efektif (HES & HEB)
 * Layout Presisi Sesuai Format Resmi Sekolah (A4 Portrait)
 * Menggunakan Font Standar Dokumen Resmi: Times New Roman, 12pt
 * Margin Kiri-Kanan Tipis: 0.5cm & Garis Ramping 1px
 */
$itemsToPrint = !empty($resultsList) ? $resultsList : (isset($result) ? [$result] : []);
$teacherTitle = !empty($itemsToPrint[0]['guru']['nama']) ? $itemsToPrint[0]['guru']['nama'] : 'Guru';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Hari Efektif - <?= e($teacherTitle) ?></title>
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
            font-size: 12pt;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #000000;
            padding: 4px 6px;
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
                width: 210mm;
                min-height: 297mm;
                padding: 12mm 12mm 12mm 12mm;
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
                font-size: 12pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .screen-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                width: 100% !important;
            }
            .print-page { 
                padding: 0 !important; 
                margin: 0 !important; 
                width: 100% !important; 
                max-width: 100% !important; 
                box-shadow: none !important;
                border: none !important;
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
                page-break-after: always !important;
                break-after: page !important;
            }
            .page-sheet:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }
            @page { 
                size: A4 portrait; 
                margin: 0.5cm 0.5cm 0.5cm 0.5cm; 
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (No Print) -->
    <div class="no-print bg-white border-b border-slate-200 px-6 py-3 sticky top-0 z-50 shadow-sm font-sans">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm">
                    🖨️
                </span>
                <div>
                    <h1 class="text-sm font-extrabold text-slate-800 leading-tight">
                        Pratinjau Cetak Rincian Hari Efektif
                    </h1>
                    <p class="text-xs text-slate-500">
                        Font: <strong>Times New Roman (12pt)</strong> &bull; Margin: <strong>0.5 cm</strong> &bull; Garis: <strong>1px</strong> &bull; <?= e($teacherTitle) ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.close()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-colors">
                    Tutup
                </button>
                <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs shadow-md shadow-sky-600/20 flex items-center gap-1.5 cursor-pointer transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                    <span>Cetak Sekarang (Print)</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Screen Wrapper & Sheets -->
    <div class="screen-wrapper">
        <div class="print-page w-full flex flex-col items-center gap-8">
            <?php foreach ($itemsToPrint as $res): ?>
                <div class="page-sheet">
                    
                    <!-- Header Box Sesuai Format Gambar Sekolah -->
                    <table class="header-box">
                        <tr>
                            <!-- Logo Box (Besar & Proporsional) -->
                            <td style="width: 18%; background-color: #dbeafe; padding: 4px 6px; text-align: center; vertical-align: middle;">
                                <?php if (!empty($res['logo_url'])): ?>
                                    <img src="<?= url(ltrim($res['logo_url'], '/')) ?>" style="max-height: 80px; max-width: 96%; margin: 0 auto; display: block; object-fit: contain;" alt="Logo">
                                <?php else: ?>
                                    <div style="font-size: 28pt; font-weight: bold; color: #0284c7; line-height: 1; letter-spacing: -0.5px; font-family: 'Arial', sans-serif;">BIP</div>
                                <?php endif; ?>
                            </td>

                            <!-- Box Tengah: Mata Pelajaran (Luas) -->
                            <td style="width: 36%; background-color: #bfdbfe; padding: 6px 8px; text-align: center; vertical-align: middle;">
                                <div style="font-size: 11.5pt; font-weight: bold; color: #000; text-transform: uppercase;">Mata Pelajaran</div>
                                <div style="font-size: 13.5pt; font-weight: bold; text-transform: uppercase; margin-top: 2px; color: #000; line-height: 1.2;">
                                    <?= e($res['mata_pelajaran']) ?>
                                </div>
                            </td>

                            <!-- Box Kanan: Data Pengajar (Luas & Rapi) -->
                            <td style="width: 46%; background-color: #bfdbfe; padding: 0; vertical-align: middle;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 11.5pt; color: #000;">
                                    <tr>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2.5px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Pengajar</td>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2.5px 6px; font-weight: bold; width: 72%;">: <?= e($res['guru']['nama']) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2.5px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Kelas</td>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2.5px 6px; font-weight: bold; width: 72%;">: <?= e($res['nama_kelas']) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2.5px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Semester</td>
                                        <td style="border: none; border-bottom: 1px solid #000; padding: 2.5px 6px; font-weight: bold; width: 72%;">: <?= e($res['semester']) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none; padding: 2.5px 6px; font-weight: bold; width: 28%; white-space: nowrap;">Tahun Ajaran</td>
                                        <td style="border: none; padding: 2.5px 6px; font-weight: bold; width: 72%;">: <?= e(trim(preg_replace('/\s*(Ganjil|Genap)/i', '', $res['tahun_ajaran']))) ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!-- Judul & Durasi -->
                    <div style="text-align: center; margin-top: 12px;">
                        <h2 style="font-size: 14pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; letter-spacing: 0.5px; margin: 0; color: #000;">
                            RINCIAN HARI EFEKTIF
                        </h2>
                        <p style="font-size: 12pt; font-weight: bold; margin-top: 6px; margin-bottom: 0; text-align: left; color: #000;">
                            <?= e($res['durasi_label']) ?>
                        </p>
                    </div>

                    <!-- I. Rincian Hari Efektif Sekolah -->
                    <div style="margin-top: 10px;">
                        <p style="font-size: 12pt; font-weight: bold; margin-bottom: 4px; color: #000;">
                            I. &nbsp; Rincian Hari Efektif Sekolah
                        </p>
                        <table class="table-custom">
                            <thead>
                                <tr style="background-color: #dbeafe; text-align: center; font-weight: bold;">
                                    <th style="width: 8%; text-align: center;">NO</th>
                                    <th style="width: 32%; text-align: center;">BULAN</th>
                                    <th style="width: 25%; text-align: center;">JUMLAH HARI</th>
                                    <th style="width: 35%; text-align: center;">KETERANGAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($res['hes']['rows'] as $r): ?>
                                    <tr>
                                        <td style="text-align: center; font-weight: bold;"><?= $r['no'] ?>.</td>
                                        <td style="font-weight: bold; text-transform: uppercase; padding-left: 10px;"><?= e($r['bulan']) ?></td>
                                        <td style="text-align: center; font-weight: bold;"><?= $r['jumlah_hari'] ?></td>
                                        <td><?= e($r['keterangan']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold; background-color: #f8fafc;">
                                    <td colspan="2" style="text-align: center; font-weight: bold; letter-spacing: 0.5px;">JUMLAH</td>
                                    <td style="text-align: center; color: #dc2626; font-weight: bold; font-size: 12pt;"><?= $res['hes']['total_hari'] ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- II. RINCIAN HARI EFEKTIF KBM -->
                    <div style="margin-top: 14px;">
                        <p style="font-size: 12pt; font-weight: bold; margin-bottom: 4px; text-transform: uppercase; color: #000;">
                            II. RINCIAN HARI EFEKTIF KBM
                        </p>
                        <table class="table-custom">
                            <thead>
                                <tr style="background-color: #dbeafe; text-align: center; font-weight: bold;">
                                    <th style="width: 28%; text-align: center;">BULAN</th>
                                    <th style="width: 14%; text-align: center;">HARI</th>
                                    <th style="width: 14%; text-align: center;">PEKAN</th>
                                    <th style="width: 16%; text-align: center;">JAM-PEL</th>
                                    <th style="width: 28%; text-align: center;">KET</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($res['heb']['rows'] as $r): ?>
                                    <tr>
                                        <td style="font-weight: bold; text-transform: uppercase; padding-left: 10px;"><?= e($r['bulan']) ?></td>
                                        <td style="text-align: center; font-weight: bold;"><?= $r['hari'] ?></td>
                                        <td style="text-align: center; font-weight: bold;"><?= $r['pekan'] ?></td>
                                        <td style="text-align: center; font-weight: bold;"><?= $r['jam_pel'] ?></td>
                                        <td style="font-weight: bold; font-size: 11pt; text-transform: uppercase;"><?= e($r['keterangan']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="font-weight: bold; background-color: #f8fafc;">
                                    <td style="text-align: center; font-weight: bold; letter-spacing: 0.5px;">JUMLAH</td>
                                    <td style="text-align: center; font-weight: bold;"><?= $res['heb']['total_hari'] ?></td>
                                    <td style="text-align: center; font-weight: bold;"><?= $res['heb']['total_pekan'] ?></td>
                                    <td style="text-align: center; font-weight: bold;"><?= $res['heb']['total_jam_pel'] ?></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Rekapitulasi & Tanda Tangan -->
                    <table style="width: 100%; margin-top: 18px; font-size: 12pt; font-weight: bold; color: #000; font-family: 'Times New Roman', Times, serif;">
                        <tr>
                            <!-- Ringkasan Kiri -->
                            <td style="width: 58%; vertical-align: top;">
                                <table style="width: 100%; border-collapse: collapse; font-size: 12pt;">
                                    <tr>
                                        <td style="width: 55%; padding: 2px 0;">Jumlah Hari Efektif Belajar</td>
                                        <td style="width: 45%; padding: 2px 0;">: &nbsp;<?= $res['summary']['jumlah_hari_efektif'] ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Banyaknya Jam Efektif</td>
                                        <td style="padding: 2px 0;">: &nbsp;<?= $res['summary']['banyaknya_jam_efektif'] ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2px 0;">Banyaknya Pekan Efektif</td>
                                        <td style="padding: 2px 0;">: &nbsp;<?= $res['summary']['banyaknya_pekan_efektif'] ?></td>
                                    </tr>
                                </table>
                            </td>

                            <!-- Tanda Tangan Kanan (Presisi Sesuai Pengaturan Sistem) -->
                            <td style="width: 42%; vertical-align: top; text-align: center; font-size: 12pt;">
                                <div style="font-weight: bold;">Mengetahui,</div>
                                <div style="font-weight: bold; margin-top: 1px;">Kepala Sekolah</div>
                                
                                <div style="height: 55px;"></div>
                                
                                <div style="font-weight: bold; text-decoration: underline;">
                                    <?= e($res['kepala_sekolah']['nama']) ?>
                                </div>
                                <?php if (!empty($res['kepala_sekolah']['nip'])): ?>
                                    <div style="font-size: 11pt; font-weight: normal; margin-top: 2px;">
                                        NIP. <?= e($res['kepala_sekolah']['nip']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>
</html>
