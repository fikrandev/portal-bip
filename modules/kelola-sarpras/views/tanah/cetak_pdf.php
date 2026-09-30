<?php
/**
 * Laporan Inventaris Aset Tanah - Cetak PDF Format Portrait A4
 * Modul Kelola Sarpras - Portal BIP
 */

$appLogo = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $appLogo = url(ltrim(SYS_APP_LOGO, '/'));
} elseif (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $appLogo = url(ltrim(SYS_APP_FAVICON, '/'));
}

$tanggalCetak = date('d F Y');
$bulanIndo = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
$tglFormat = strtr($tanggalCetak, $bulanIndo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan_Inventaris_Aset_Tanah_<?= date('Ymd_His') ?></title>
    <style>
        /* Standar A4 Portrait */
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
        }

        body {
            background-color: #f1f5f9;
            color: #000;
            font-size: 11pt;
            line-height: 1.35;
        }

        .sheet {
            background: #fff;
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm 15mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: relative;
        }

        /* Kop Surat Resmi */
        .kop-surat {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .kop-logo {
            width: 75px;
            height: 75px;
            object-fit: contain;
            margin-right: 15px;
        }

        .kop-text {
            text-align: center;
            flex: 1;
        }

        .kop-text h3 {
            font-size: 12pt;
            font-weight: normal;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kop-text h1 {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .kop-text p {
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
        }

        /* Judul Laporan */
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .doc-title h2 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .doc-title p {
            font-size: 9.5pt;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            margin-top: 4px;
        }

        /* Box Ringkasan */
        .summary-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            padding: 10px 14px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
        }

        .summary-item {
            text-align: center;
            flex: 1;
        }

        .summary-item:not(:last-child) {
            border-right: 1px solid #cbd5e1;
        }

        .summary-label {
            color: #64748b;
            font-size: 8pt;
            text-transform: uppercase;
            font-weight: bold;
        }

        .summary-val {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Tabel Data A4 */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 25px;
        }

        table.data-table th, 
        table.data-table td {
            border: 1px solid #333;
            padding: 7px 6px;
            vertical-align: middle;
        }

        table.data-table th {
            background-color: #e2e8f0;
            color: #000;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.3px;
        }

        table.data-table tr:nth-child(even) td {
            background-color: #fcfcfc;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        /* Tanda Tangan */
        .ttd-container {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            page-break-inside: avoid;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
        }

        .ttd-box {
            width: 220px;
            text-align: center;
        }

        .ttd-space {
            height: 70px;
        }

        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }

        /* Floating Action Bar */
        .action-bar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 999;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(8px);
            padding: 10px 14px;
            border-radius: 50px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.15);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 30px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            font-family: Arial, Helvetica, sans-serif;
        }

        .btn-print {
            background: linear-gradient(135deg, #0d9488, #059669);
            color: #fff;
        }

        .btn-print:hover {
            background: linear-gradient(135deg, #0f766e, #047857);
            transform: translateY(-1px);
        }

        .btn-close {
            background: #334155;
            color: #f1f5f9;
        }

        .btn-close:hover {
            background: #475569;
        }

        /* Print Mode Override */
        @media print {
            body {
                background: #fff;
            }
            .sheet {
                margin: 0;
                padding: 0;
                box-shadow: none;
                max-width: 100%;
                min-height: auto;
            }
            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Action Bar (Hanya tampil di browser, hilang saat diprint) -->
    <div class="action-bar no-print">
        <button onclick="window.print()" class="btn-action btn-print">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.07-.641-2.07-1.171-3.007M6.72 13.829a20.016 20.016 0 0 0 10.56 0m-10.56 0c.24 1.07.641 2.07 1.171 3.007m10.56-3.007c.24-1.07.641-2.07 1.171-3.007m-1.171 3.007c-.24 1.07-.641 2.07-1.171 3.007M3 13.125C3 12.504 3.504 12 4.125 12h15.75c.621 0 1.125.504 1.125 1.125v6.75A2.25 2.25 0 0 1 18.75 22.125H5.25A2.25 2.25 0 0 1 3 19.875v-6.75ZM6.75 12V4.875C6.75 4.254 7.254 3.75 7.875 3.75h8.25c.621 0 1.125.504 1.125 1.125V12" />
            </svg>
            <span>Cetak / Simpan PDF (A4)</span>
        </button>
        <button onclick="window.close()" class="btn-action btn-close">
            Tutup
        </button>
    </div>

    <div class="sheet">
        <!-- 1. KOP SURAT RESMI -->
        <div class="kop-surat">
            <?php if ($appLogo): ?>
                <img src="<?= $appLogo ?>" alt="Logo Sekolah" class="kop-logo">
            <?php endif; ?>
            <div class="kop-text">
                <h3>YAYASAN BINA INSAN PALU</h3>
                <h1>BAGIAN SARANA & PRASARANA</h1>
                <p>Jl. Trans Palu - Donggala No. 45, Kel. Lere, Kec. Palu Barat, Kota Palu, Sulawesi Tengah</p>
                <p>Website: binainsanpalu.sch.id &bull; Email: sarpras@binainsanpalu.sch.id &bull; Telp: (0451) 421xxx</p>
            </div>
        </div>

        <!-- 2. JUDUL DOKUMEN -->
        <div class="doc-title">
            <h2>LAPORAN INVENTARIS ASET TANAH</h2>
            <p>Dokumen Resmi Pencatatan dan Status Kepemilikan Lahan Yayasan Bina Insan Palu</p>
        </div>

        <!-- 3. KOTAK REKAPITULASI EKSEKUTIF -->
        <div class="summary-box">
            <div class="summary-item">
                <div class="summary-label">Total Bidang Tanah</div>
                <div class="summary-val"><?= number_format($totalBidang) ?> Bidang</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Luas Lahan</div>
                <div class="summary-val"><?= number_format($totalLuas, 0, ',', '.') ?> m²</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Estimasi Kapitalisasi</div>
                <div class="summary-val">Rp <?= number_format($totalNilai, 0, ',', '.') ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Gedung / Bangunan</div>
                <div class="summary-val"><?= number_format($totalBangunan) ?> Gedung</div>
            </div>
        </div>

        <!-- 4. TABEL DATA RESMI -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 60px;">Kode</th>
                    <th>Nama Bidang Tanah & Lokasi</th>
                    <th style="width: 95px;">No. Sertifikat</th>
                    <th style="width: 50px;">Status</th>
                    <th style="width: 65px;">Dimensi</th>
                    <th style="width: 65px;">Luas</th>
                    <th style="width: 85px;">Harga Perolehan</th>
                    <th style="width: 45px;">Gedung</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px;">
                            Tidak ada data aset tanah yang terdaftar.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1;
                    foreach ($items as $t): 
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="text-center font-bold font-mono"><?= htmlspecialchars($t['kode_tanah']) ?></td>
                            <td>
                                <div class="font-bold"><?= htmlspecialchars($t['nama_tanah']) ?></div>
                                <div style="font-size: 7.5pt; color: #555;">
                                    <?= htmlspecialchars($t['alamat_lokasi'] ?: '-') ?>
                                </div>
                            </td>
                            <td class="font-mono" style="font-size: 8pt;"><?= htmlspecialchars($t['no_sertifikat'] ?: '-') ?></td>
                            <td class="text-center font-bold" style="font-size: 8pt;"><?= htmlspecialchars($t['status_kepemilikan']) ?></td>
                            <td class="text-center" style="font-size: 8pt;">
                                <?= number_format($t['panjang'], 1, ',', '.') ?> &times; <?= number_format($t['lebar'], 1, ',', '.') ?> m
                            </td>
                            <td class="text-right font-bold">
                                <?= number_format($t['luas'], 0, ',', '.') ?> m²
                            </td>
                            <td class="text-right font-bold" style="font-size: 8pt;">
                                Rp <?= number_format($t['harga_perolehan'], 0, ',', '.') ?>
                            </td>
                            <td class="text-center font-bold">
                                <?= (int)$t['total_bangunan'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="6" class="text-right font-bold" style="padding: 8px;">TOTAL REKAPITULASI:</td>
                    <td class="text-right font-bold" style="font-size: 9.5pt;"><?= number_format($totalLuas, 0, ',', '.') ?> m²</td>
                    <td class="text-right font-bold" style="font-size: 8.5pt;">Rp <?= number_format($totalNilai, 0, ',', '.') ?></td>
                    <td class="text-center font-bold"><?= number_format($totalBangunan) ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- 5. TANDA TANGAN RESMI -->
        <div class="ttd-container">
            <div class="ttd-box">
                <p>Mengetahui,</p>
                <p>Ketua Yayasan Bina Insan Palu</p>
                <div class="ttd-space"></div>
                <p class="ttd-name">Dr. H. Muh. Arsyad, M.Pd.</p>
                <p style="font-size: 8.5pt; color: #555;">NIP / NIK. 19780512 200312 1 002</p>
            </div>

            <div class="ttd-box">
                <p>Palu, <?= $tglFormat ?></p>
                <p>Koordinator Sarana & Prasarana</p>
                <div class="ttd-space"></div>
                <p class="ttd-name"><?= htmlspecialchars(Auth::name() ?? 'Fikran, S.Pd.') ?></p>
                <p style="font-size: 8.5pt; color: #555;">NIP / NIK. 19910405 201801 1 005</p>
            </div>
        </div>
    </div>

</body>
</html>
