<?php
/**
 * Cetak Laporan Penyusutan Aset - Format Landscape (A4)
 * Portal BIP - Yayasan Bina Insan Palu
 */

if (!function_exists('e')) {
    function e(?string $value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('url')) {
    function url(string $path = ''): string {
        return (defined('BASE_URL') ? BASE_URL : '') . '/' . ltrim($path, '/');
    }
}

$kopSurat = $kopSurat ?? '';
$namaKepalaSarpras = $namaKepalaSarpras ?? '';
$niyKepalaSarpras = $niyKepalaSarpras ?? '';

$items = $items ?? [];
$tahun = $tahun ?? date('Y');
$bulan = $bulan ?? date('m');
$namaBulan = $namaBulan ?? 'Bulan ' . $bulan;

$totalHargaAwal = $totalHargaAwal ?? 0;
$totalNilaiSisa = $totalNilaiSisa ?? 0;
$totalAkumulasi = $totalAkumulasi ?? 0;
$totalNilaiBuku = $totalNilaiBuku ?? 0;
$totalPenyusutanTahun = $totalPenyusutanTahun ?? 0;
$totalPenyusutanBulan = $totalPenyusutanBulan ?? 0;

$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$tglCetak = date('d') . ' ' . ($bulanIndo[(int)date('m')] ?? date('F')) . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penyusutan Aset - <?= e($namaBulan) ?> <?= e($tahun) ?> - <?= defined('SYS_APP_NAME') ? SYS_APP_NAME : 'Portal BIP' ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm 1cm 1.2cm 1cm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: "Times New Roman", Times, Georgia, serif;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
            padding: 0;
            font-size: 10pt;
            line-height: 1.3;
        }

        /* Screen Toolbar */
        .screen-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #ffffff;
            border-bottom: 2px solid #cbd5e1;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .toolbar-info h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }

        .toolbar-info p {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: #64748b;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }

        .btn-print {
            background: #e11d48;
            color: #ffffff;
        }
        .btn-print:hover { background: #be123c; }

        .btn-excel {
            background: #059669;
            color: #ffffff;
        }
        .btn-excel:hover { background: #047857; }

        .btn-close {
            background: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }
        .btn-close:hover { background: #e2e8f0; }

        /* Document Paper Container (A4 Landscape) */
        .doc-page {
            background: #ffffff;
            max-width: 29.7cm;
            min-height: 21cm;
            margin: 20px auto;
            padding: 1.2cm 1.5cm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            position: relative;
        }

        /* Kop Surat Resmi */
        .kop-surat {
            border-bottom: 3px double #000000;
            padding-bottom: 10px;
            margin-bottom: 14px;
            text-align: center;
        }

        .kop-surat img {
            max-width: 100%;
            max-height: 90px;
            height: auto;
        }

        .kop-text-yayasan {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            color: #0f172a;
        }

        .kop-text-unit {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #be123c;
            margin: 2px 0;
        }

        .kop-text-alamat {
            font-size: 9pt;
            font-style: italic;
            color: #334155;
            margin: 2px 0 0 0;
        }

        /* Document Header */
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 10.5pt;
            margin-bottom: 14px;
            color: #475569;
        }

        .doc-period-badge {
            display: inline-block;
            background: #fff1f2;
            color: #be123c;
            padding: 3px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10pt;
            border: 1px solid #fecdd3;
        }

        /* Summary Recap Cards */
        .recap-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .recap-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .recap-card.primary {
            border-left: 4px solid #0284c7;
        }
        .recap-card.amber {
            border-left: 4px solid #d97706;
        }
        .recap-card.rose {
            border-left: 4px solid #e11d48;
        }
        .recap-card.slate {
            border-left: 4px solid #475569;
        }

        .recap-label {
            font-size: 8.5pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .recap-value {
            font-size: 12pt;
            font-weight: 800;
            color: #0f172a;
        }

        /* Table Styling */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 12px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            vertical-align: middle;
        }

        .report-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .report-table td.center { text-align: center; }
        .report-table td.right { text-align: right; font-variant-numeric: tabular-nums; }
        .report-table td.mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 8.5pt; }

        .report-table tr:nth-child(even) td {
            background: #fcfcfd;
        }

        .report-table tfoot td {
            font-weight: bold;
            background: #f1f5f9;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
        }

        /* Methodology Note */
        .method-note {
            font-size: 8pt;
            color: #64748b;
            font-style: italic;
            margin-bottom: 20px;
        }

        /* Signatures Section */
        .signature-wrapper {
            margin-top: 20px;
            width: 100%;
            display: table;
            page-break-inside: avoid;
        }

        .signature-row {
            display: table-row;
        }

        .signature-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: center;
            font-size: 10pt;
            padding: 0 20px;
        }

        .signature-col.left { text-align: left; padding-left: 40px; }
        .signature-col.right { text-align: right; padding-right: 40px; }

        .signature-space {
            height: 65px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 10.5pt;
        }

        .signature-niy {
            font-size: 9pt;
            color: #334155;
            margin-top: 2px;
        }

        /* Print Media Query */
        @media print {
            .screen-toolbar {
                display: none !important;
            }

            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .doc-page {
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                min-height: auto !important;
            }

            .recap-grid {
                break-inside: avoid;
            }

            .report-table tr {
                page-break-inside: avoid;
            }

            .signature-wrapper {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Sticky Toolbar for Browser View -->
    <div class="screen-toolbar">
        <div class="toolbar-info">
            <h2>Laporan Penyusutan Aset Inventaris (Landscape)</h2>
            <p>Periode: <strong><?= e($namaBulan) ?> <?= e($tahun) ?></strong> &bull; Total Aset: <?= count($items) ?> barang</p>
        </div>
        <div class="toolbar-actions">
            <a href="<?= url('kelola-sarpras/penyusutan/export-excel') . '?bulan=' . $bulan . '&tahun=' . $tahun ?>" class="btn btn-excel">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel (.xls)
            </a>
            <button onclick="window.print()" class="btn btn-print">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-close">
                Tutup
            </button>
        </div>
    </div>

    <!-- Printable Paper Area -->
    <div class="doc-page">
        
        <!-- Kop Surat -->
        <div class="kop-surat">
            <?php if (!empty($kopSurat)): ?>
                <img src="<?= e($kopSurat) ?>" alt="Kop Surat Resmi">
            <?php else: ?>
                <div class="kop-text-yayasan">YAYASAN BINA INSAN PALU</div>
                <div class="kop-text-unit">UNIT SARANA DAN PRASARANA</div>
                <div class="kop-text-alamat">
                    Jl. Banteng No. 12, Kel. Birobuli Selatan, Kec. Palu Selatan, Kota Palu, Sulawesi Tengah<br>
                    Email: sarpras@binainsanpalu.sch.id &bull; Website: www.binainsanpalu.sch.id
                </div>
            <?php endif; ?>
        </div>

        <!-- Judul Laporan & Periode -->
        <div class="doc-title">Laporan Penyusutan Aset Inventaris</div>
        <div class="doc-subtitle">
            Metode Garis Lurus (Straight-Line Depreciation) &bull; 
            <span class="doc-period-badge">Periode: <?= e($namaBulan) ?> <?= e($tahun) ?></span>
        </div>

        <!-- Ringkasan Eksekutif (Recap Grid) -->
        <div class="recap-grid">
            <div class="recap-card slate">
                <div class="recap-label">Total Aset Terdaftar</div>
                <div class="recap-value"><?= number_format(count($items), 0, ',', '.') ?> <span style="font-size: 9pt; font-weight: normal; color: #64748b;">Unit Barang</span></div>
            </div>
            <div class="recap-card primary">
                <div class="recap-label">Total Harga Perolehan</div>
                <div class="recap-value">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></div>
            </div>
            <div class="recap-card amber">
                <div class="recap-label">Total Akumulasi Penyusutan</div>
                <div class="recap-value" style="color: #b45309;">Rp <?= number_format($totalAkumulasi, 0, ',', '.') ?></div>
            </div>
            <div class="recap-card rose">
                <div class="recap-label">Total Nilai Buku Saat Ini</div>
                <div class="recap-value" style="color: #be123c;">Rp <?= number_format($totalNilaiBuku, 0, ',', '.') ?></div>
            </div>
        </div>

        <!-- Tabel Detail Penyusutan Aset -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 85px;">Kode Barang</th>
                    <th>Nama Aset &amp; Merk</th>
                    <th style="width: 100px;">Kategori</th>
                    <th style="width: 110px;">Ruangan / Lokasi</th>
                    <th style="width: 70px;">Tgl / Thn Perolehan</th>
                    <th style="width: 50px;">Masa Manfaat</th>
                    <th style="width: 50px;">Umur Pakai</th>
                    <th style="width: 105px;">Harga Perolehan</th>
                    <th style="width: 95px;">Penyusutan / Thn</th>
                    <th style="width: 105px;">Akumulasi Penyusutan</th>
                    <th style="width: 105px;">Nilai Buku Saat Ini</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="12" class="center" style="padding: 30px; color: #64748b;">
                            Tidak ada aset inventaris dengan nilai perolehan pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($items as $item): ?>
                        <tr>
                            <td class="center"><?= $no++ ?></td>
                            <td class="center mono" style="font-weight: 600; color: #1e3a8a;"><?= e($item['kode_barang']) ?></td>
                            <td>
                                <strong><?= e($item['nama_barang']) ?></strong>
                                <?php if (!empty($item['merk'])): ?>
                                    <span style="color: #475569; font-size: 8pt;"> - <?= e($item['merk']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($item['kategori']) ?></td>
                            <td><?= e($item['ruangan']) ?></td>
                            <td class="center"><?= e($item['tanggal_perolehan']) ?></td>
                            <td class="center"><?= $item['masa_manfaat'] ?> Thn</td>
                            <td class="center" style="color: #b45309; font-weight: 600;"><?= number_format($item['umur_pakai_tahun'], 1, ',', '.') ?> Thn</td>
                            <td class="right">Rp <?= number_format($item['harga_awal'], 0, ',', '.') ?></td>
                            <td class="right" style="color: #be123c;">Rp <?= number_format($item['penyusutan_per_tahun'], 0, ',', '.') ?></td>
                            <td class="right" style="color: #b45309; font-weight: 600;">Rp <?= number_format($item['akumulasi_penyusutan'], 0, ',', '.') ?></td>
                            <td class="right" style="font-weight: bold; color: #0f172a;">Rp <?= number_format($item['nilai_buku'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" class="center" style="text-transform: uppercase; letter-spacing: 0.5px;">TOTAL KESELURUHAN</td>
                    <td class="right">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></td>
                    <td class="right" style="color: #be123c;">Rp <?= number_format($totalPenyusutanTahun, 0, ',', '.') ?></td>
                    <td class="right" style="color: #b45309;">Rp <?= number_format($totalAkumulasi, 0, ',', '.') ?></td>
                    <td class="right" style="color: #0f172a;">Rp <?= number_format($totalNilaiBuku, 0, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="method-note">
            * Catatan Metodologi: Perhitungan nilai penyusutan menggunakan metode Garis Lurus (Straight-Line) dengan asumsi Nilai Residu 10% dari Harga Perolehan. Aset yang telah melewati masa manfaat penuh akan mempertahankan nilai buku sebesar nilai residu.
        </div>

        <!-- Kolom Tanda Tangan -->
        <div class="signature-wrapper">
            <div class="signature-row">
                <div class="signature-col left">
                    <div>Mengetahui,</div>
                    <div style="font-weight: bold;">Pimpinan Yayasan Bina Insan Palu</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">( ............................................................ )</div>
                    <div class="signature-niy">Ketua Yayasan / Direktur Operasional</div>
                </div>
                <div class="signature-col right">
                    <div>Palu, <?= $tglCetak ?></div>
                    <div style="font-weight: bold;">Penanggung Jawab Sarana &amp; Prasarana</div>
                    <div class="signature-space"></div>
                    <div class="signature-name"><?= e($namaKepalaSarpras ?: '( ............................................................ )') ?></div>
                    <div class="signature-niy">NIY: <?= e($niyKepalaSarpras ?: '........................') ?></div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
