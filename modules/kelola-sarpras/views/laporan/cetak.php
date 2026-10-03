<?php
/**
 * Tampilan Cetak Laporan Aset Inventaris Sarpras
 * Portal BIP - Yayasan Bina Insan Palu
 * 
 * Mendukung:
 * 1. Cetak Seluruh Ruangan (Grouped Per Ruangan + Rekapitulasi + Aset Tanpa Ruangan)
 * 2. Cetak Ruangan Tunggal (Single Room)
 * 3. Cetak Khusus Aset Tanpa Ruangan / Belum Ditempatkan
 */

$kopSurat = $kopSurat ?? '';
$namaKepalaSarpras = $namaKepalaSarpras ?? '';
$niyKepalaSarpras = $niyKepalaSarpras ?? '';

$isGrouped = $isGrouped ?? false;
$ruanganList = $laporan['ruangan_list'] ?? [];
$unassigned = $laporan['unassigned'] ?? [];
$rekap = $laporan['rekap'] ?? [];

$filter_ruangan = $filter_ruangan ?? '';
$filter_kategori = $filter_kategori ?? '';
$selectedRuanganData = $selectedRuanganData ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Inventaris Aset Sarpras - <?= defined('SYS_APP_NAME') ? SYS_APP_NAME : 'Portal BIP' ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 1.5cm 2cm 1.5cm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: "Times New Roman", Times, Georgia, serif;
            color: #000000;
            background: #f8fafc;
            margin: 0;
            padding: 0;
            font-size: 11pt;
            line-height: 1.35;
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
            background: #1e3a8a;
            color: #ffffff;
        }
        .btn-print:hover { background: #1d4ed8; }

        .btn-close {
            background: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }
        .btn-close:hover { background: #e2e8f0; }

        /* Document Paper Container */
        .doc-page {
            background: #ffffff;
            max-width: 21cm;
            min-height: 29.7cm;
            margin: 20px auto;
            padding: 2cm 2cm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            position: relative;
        }

        /* Kop Surat Resmi */
        .kop-surat {
            border-bottom: 3px double #000000;
            padding-bottom: 12px;
            margin-bottom: 18px;
            text-align: center;
        }

        .kop-surat img {
            max-width: 100%;
            max-height: 110px;
            height: auto;
        }

        .kop-text-yayasan {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .kop-text-unit {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a8a;
            margin: 2px 0;
        }

        .kop-text-alamat {
            font-size: 9.5pt;
            font-style: italic;
            color: #334155;
            margin: 2px 0 0 0;
        }

        /* Title Sections */
        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 10.5pt;
            margin-bottom: 16px;
            color: #334155;
        }

        /* Room Metadata Card */
        .room-meta-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            font-size: 10pt;
        }

        .room-meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .room-meta-table td {
            padding: 2px 0;
            border: none;
            vertical-align: top;
        }

        .room-meta-table td.lbl {
            width: 130px;
            font-weight: bold;
            color: #475569;
        }

        .room-meta-table td.col {
            width: 10px;
            font-weight: bold;
        }

        .room-meta-table td.val {
            font-weight: bold;
            color: #0f172a;
        }

        /* Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5pt;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #000000;
            padding: 5px 6px;
            vertical-align: middle;
        }

        table.data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 8.5pt;
            letter-spacing: 0.2px;
        }

        table.data-table tfoot th, table.data-table tfoot td {
            background-color: #f8fafc;
            font-weight: bold;
            border-top: 2px solid #000000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .nowrap { white-space: nowrap; }

        /* Badge Kondisi */
        .kondisi-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid #94a3b8;
        }

        /* Signatures Section */
        .signature-grid {
            margin-top: 30px;
            width: 100%;
            display: table;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signature-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10pt;
        }

        .signature-space {
            height: 65px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-niy {
            font-size: 9pt;
            color: #334155;
            margin-top: 2px;
        }

        /* Page break separator for printing */
        .page-break {
            page-break-before: always;
            break-before: page;
        }

        /* Screen only divider */
        .screen-divider {
            border-top: 2px dashed #cbd5e1;
            margin: 40px auto;
            max-width: 21cm;
            position: relative;
            text-align: center;
        }

        .screen-divider span {
            background: #f8fafc;
            color: #64748b;
            padding: 0 12px;
            font-size: 11px;
            font-family: sans-serif;
            position: relative;
            top: -10px;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .screen-toolbar, .screen-divider {
                display: none !important;
            }

            .doc-page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
                min-height: auto !important;
            }

            .page-break {
                page-break-before: always !important;
                break-before: page !important;
            }

            table.data-table th {
                background-color: #f1f5f9 !important;
            }
        }
    </style>
</head>
<body>

<!-- Screen Top Toolbar (Hidden on Print) -->
<div class="screen-toolbar">
    <div class="toolbar-info">
        <h2>🖨️ Cetak Laporan Inventaris Aset Sarpras</h2>
        <p>
            <?php if ($isGrouped): ?>
                Mode Cetak: <strong>Seluruh Ruangan Berkelompok (<?= count($ruanganList) ?> Ruangan + Aset Tanpa Ruangan)</strong>
            <?php elseif ($filter_ruangan === 'tanpa_ruangan'): ?>
                Mode Cetak: <strong>Khusus Aset Tanpa Ruangan / Belum Ditempatkan</strong>
            <?php else: ?>
                Mode Cetak: <strong>Ruangan: <?= e($selectedRuanganData['nama_ruangan'] ?? 'Ruangan Terpilih') ?></strong>
            <?php endif; ?>
        </p>
    </div>
    <div class="toolbar-actions">
        <button type="button" onclick="window.print()" class="btn btn-print">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v2.796c0 1.18.91 2.164 2.09 2.201a51.964 51.964 0 0 0 6.32 0c1.18-.037 2.09-1.022 2.09-2.201V9.456Z" />
            </svg>
            <span>Cetak Dokumen (Ctrl+P)</span>
        </button>
        <button type="button" onclick="window.close()" class="btn btn-close">
            Tutup
        </button>
    </div>
</div>

<?php 
// Helper function to render Kop Surat
function renderKopSurat($kopSurat) {
?>
    <div class="kop-surat">
        <?php if (!empty($kopSurat)): ?>
            <img src="<?= asset($kopSurat) ?>" alt="Kop Surat Resmi">
        <?php else: ?>
            <h1 class="kop-text-yayasan">YAYASAN BINA INSAN PALU</h1>
            <h2 class="kop-text-unit">SISTEM INFORMASI MANAJEMEN SARANA &amp; PRASARANA</h2>
            <p class="kop-text-alamat">Jl. Undata No. 1, Besusu Barat, Kec. Palu Timur, Kota Palu, Sulawesi Tengah | Telp: (0451) 421234</p>
        <?php endif; ?>
    </div>
<?php
}
?>

<?php if ($isGrouped): ?>
    <!-- ============================================================== -->
    <!-- LEMBAR 1: REKAPITULASI INVENTARIS ASET SELURUH RUANGAN          -->
    <!-- ============================================================== -->
    <div class="doc-page">
        <?php renderKopSurat($kopSurat); ?>

        <div class="doc-title">REKAPITULASI INVENTARIS ASET PER RUANGAN</div>
        <div class="doc-subtitle">
            Periode / Tanggal Cetak: <strong><?= date('d F Y') ?></strong>
            <?php if (!empty($filter_kategori)): ?>
                &nbsp;|&nbsp; Kategori: <strong>Terfilter</strong>
            <?php endif; ?>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="26%">Nama Ruangan / Lokasi</th>
                    <th width="15%">Gedung / Unit</th>
                    <th width="20%">Penanggung Jawab</th>
                    <th width="8%">Jenis</th>
                    <th width="8%">Qty Unit</th>
                    <th width="9%">Kondisi Baik</th>
                    <th width="10%">Estimasi Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($ruanganList as $rg): 
                    $rInfo = $rg['ruangan'];
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <strong><?= e($rInfo['nama_ruangan']) ?></strong>
                            <div style="font-size: 8pt; color: #64748b;">Kode: <?= e($rInfo['kode_ruangan']) ?></div>
                        </td>
                        <td>
                            <?= e($rInfo['nama_bangunan'] ?? $rInfo['lokasi_gedung'] ?? 'Gedung Utama') ?><br>
                            <span style="font-size: 8pt; color: #1e40af; font-weight: bold;">Unit: <?= e($rInfo['unit']) ?></span>
                        </td>
                        <td><?= e($rInfo['penanggung_jawab'] ?: '-') ?></td>
                        <td class="text-center"><?= number_format($rg['total_item']) ?></td>
                        <td class="text-center font-bold"><strong><?= number_format($rg['total_qty']) ?></strong></td>
                        <td class="text-center font-semibold text-emerald-700"><?= number_format($rg['stat_baik']) ?></td>
                        <td class="text-right"><?= number_format($rg['total_nilai'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Baris Aset Tanpa Ruangan di Rekapitulasi -->
                <?php if (!empty($unassigned['items'])): ?>
                    <tr style="background-color: #fffbeb;">
                        <td class="text-center"><?= $no++ ?></td>
                        <td>
                            <strong><?= e($unassigned['ruangan']['nama_ruangan']) ?></strong>
                            <div style="font-size: 8pt; color: #b45309;">(Stok Cadangan / Belum Didistribusikan)</div>
                        </td>
                        <td>Gudang Sarpras</td>
                        <td><?= e($unassigned['ruangan']['penanggung_jawab']) ?></td>
                        <td class="text-center"><?= number_format($unassigned['total_item']) ?></td>
                        <td class="text-center"><strong><?= number_format($unassigned['total_qty']) ?></strong></td>
                        <td class="text-center"><?= number_format($unassigned['stat_baik']) ?></td>
                        <td class="text-right"><?= number_format($unassigned['total_nilai'], 0, ',', '.') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">TOTAL KESELURUHAN KAMPUS:</th>
                    <th class="text-center"><?= number_format($rekap['grand_total_items'] ?? 0) ?> Item</th>
                    <th class="text-center"><?= number_format($rekap['grand_total_qty'] ?? 0) ?> Unit</th>
                    <th class="text-center"><?= number_format($rekap['grand_stat_baik'] ?? 0) ?> Baik</th>
                    <th class="text-right">Rp <?= number_format($rekap['grand_total_nilai'] ?? 0, 0, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures Rekapitulasi -->
        <div class="signature-grid">
            <div class="signature-cell">
                Menyetujui,<br>
                <strong>Pimpinan / Direktur Yayasan</strong>
                <div class="signature-space"></div>
                <div class="signature-name">( .................................................... )</div>
                <div class="signature-niy">NIP/NIY: .......................................</div>
            </div>
            <div class="signature-cell">
                Palu, <?= date('d F Y') ?><br>
                <strong>Kepala Sarana &amp; Prasarana</strong>
                <div class="signature-space"></div>
                <div class="signature-name"><?= !empty($namaKepalaSarpras) ? e($namaKepalaSarpras) : '( .................................................... )' ?></div>
                <div class="signature-niy"><?= !empty($niyKepalaSarpras) ? 'NIY: ' . e($niyKepalaSarpras) : 'NIY: .......................................' ?></div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- LEMBAR 2..N: RINCIAN PER RUANGAN (PAGE BREAK PER RUANGAN)       -->
    <!-- ============================================================== -->
    <?php foreach ($ruanganList as $index => $rg): 
        $rInfo = $rg['ruangan'];
        $items = $rg['items'];
    ?>
        <div class="screen-divider"><span>Pemisah Lembar Halaman Ruangan: <?= e($rInfo['nama_ruangan']) ?></span></div>
        <div class="doc-page page-break">
            <?php renderKopSurat($kopSurat); ?>

            <div class="doc-title">RINCIAN INVENTARIS ASET RUANGAN</div>
            <div class="doc-subtitle">Nomor Ruangan: <strong><?= e($rInfo['kode_ruangan']) ?></strong> &nbsp;|&nbsp; Unit: <strong><?= e($rInfo['unit']) ?></strong></div>

            <!-- Metadata Ruangan -->
            <div class="room-meta-box">
                <table class="room-meta-table">
                    <tr>
                        <td class="lbl">Nama Ruangan</td>
                        <td class="col">:</td>
                        <td class="val"><?= e($rInfo['nama_ruangan']) ?></td>
                        <td class="lbl" style="padding-left: 20px;">Penanggung Jawab</td>
                        <td class="col">:</td>
                        <td class="val"><?= e($rInfo['penanggung_jawab'] ?: 'Belum Ditentukan') ?></td>
                    </tr>
                    <tr>
                        <td class="lbl">Lokasi / Gedung</td>
                        <td class="col">:</td>
                        <td class="val"><?= e($rInfo['nama_bangunan'] ?? $rInfo['lokasi_gedung'] ?? 'Gedung Utama') ?> (Lantai <?= e($rInfo['lantai'] ?? 1) ?>)</td>
                        <td class="lbl" style="padding-left: 20px;">Total Fisik Barang</td>
                        <td class="col">:</td>
                        <td class="val"><?= (int)$rg['total_item'] ?> Jenis (<?= (int)$rg['total_qty'] ?> Unit Fisik)</td>
                    </tr>
                </table>
            </div>

            <!-- Tabel Barang Ruangan -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="16%">Kode Barang</th>
                        <th width="28%">Nama Aset &amp; Spesifikasi</th>
                        <th width="14%">Merk / Model</th>
                        <th width="8%">Jumlah</th>
                        <th width="10%">Kondisi</th>
                        <th width="10%">Sumber Dana</th>
                        <th width="10%">Total Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 15px; color: #64748b;">Belum ada aset terdaftar di ruangan ini.</td>
                        </tr>
                    <?php else: 
                        $noItem = 1;
                        foreach ($items as $it): 
                    ?>
                        <tr>
                            <td class="text-center"><?= $noItem++ ?></td>
                            <td class="text-center" style="font-family: monospace; font-size: 8.5pt; font-weight: bold;"><?= e($it['kode_barang']) ?></td>
                            <td>
                                <strong><?= e($it['nama_barang']) ?></strong>
                                <?php if (!empty($it['nomor_seri'])): ?>
                                    <div style="font-size: 8pt; color: #475569;">S/N: <?= e($it['nomor_seri']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= e($it['merk_model'] ?: '-') ?></td>
                            <td class="text-center"><strong><?= (int)$it['jumlah'] ?></strong> <?= e($it['satuan']) ?></td>
                            <td class="text-center">
                                <span class="kondisi-badge"><?= e($it['kondisi']) ?></span>
                            </td>
                            <td class="text-center" style="font-size: 8.5pt;"><?= e($it['sumber_dana']) ?></td>
                            <td class="text-right"><?= number_format($it['total_nilai'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">SUBTOTAL RUANGAN:</th>
                        <th class="text-center"><?= (int)$rg['total_qty'] ?> Unit</th>
                        <th colspan="2" class="text-center"><?= (int)$rg['stat_baik'] ?> Baik <?= ((int)$rg['stat_rusak_ringan'] > 0) ? ', ' . (int)$rg['stat_rusak_ringan'] . ' RR' : '' ?></th>
                        <th class="text-right">Rp <?= number_format($rg['total_nilai'], 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>

            <!-- Signatures Khusus Ruangan Ini -->
            <div class="signature-grid">
                <div class="signature-cell">
                    Mengetahui,<br>
                    <strong>Kepala Sarana &amp; Prasarana</strong>
                    <div class="signature-space"></div>
                    <div class="signature-name"><?= !empty($namaKepalaSarpras) ? e($namaKepalaSarpras) : '( .................................................... )' ?></div>
                    <div class="signature-niy"><?= !empty($niyKepalaSarpras) ? 'NIY: ' . e($niyKepalaSarpras) : 'NIY: .......................................' ?></div>
                </div>
                <div class="signature-cell">
                    Palu, <?= date('d F Y') ?><br>
                    <strong>Penanggung Jawab Ruangan</strong>
                    <div class="signature-space"></div>
                    <div class="signature-name"><?= !empty($rInfo['penanggung_jawab']) ? e($rInfo['penanggung_jawab']) : '( .................................................... )' ?></div>
                    <div class="signature-niy">Jabatan / Guru: <?= e($rInfo['penanggung_jawab'] ? $rInfo['nama_ruangan'] : '.......................................') ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- ============================================================== -->
    <!-- LEMBAR TERAKHIR: ASET TANPA RUANGAN / BELUM DITEMPATKAN        -->
    <!-- ============================================================== -->
    <?php if (!empty($unassigned['items'])): ?>
        <div class="screen-divider"><span>Pemisah Lembar: Aset Tanpa Ruangan / Belum Ditempatkan</span></div>
        <div class="doc-page page-break">
            <?php renderKopSurat($kopSurat); ?>

            <div class="doc-title">DAFTAR ASET TANPA RUANGAN / BELUM DITEMPATKAN</div>
            <div class="doc-subtitle">Inventaris Gudang Cadangan &amp; Aset Belum Didistribusikan</div>

            <!-- Metadata Ruangan -->
            <div class="room-meta-box" style="background: #fffbeb; border-color: #fde68a;">
                <table class="room-meta-table">
                    <tr>
                        <td class="lbl">Status Penempatan</td>
                        <td class="col">:</td>
                        <td class="val" style="color: #b45309;">Belum Ditempatkan ke Ruangan (Stok Gudang Sarpras)</td>
                        <td class="lbl" style="padding-left: 20px;">Pengelola</td>
                        <td class="col">:</td>
                        <td class="val">Staf / Petugas Gudang Sarpras</td>
                    </tr>
                    <tr>
                        <td class="lbl">Keterangan</td>
                        <td class="col">:</td>
                        <td class="val" colspan="4">Aset fisik cadangan yang siap didistribusikan ke kelas atau ruangan jika dibutuhkan.</td>
                    </tr>
                </table>
            </div>

            <!-- Tabel Barang Tanpa Ruangan -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="4%">No</th>
                        <th width="16%">Kode Barang</th>
                        <th width="28%">Nama Aset &amp; Spesifikasi</th>
                        <th width="14%">Merk / Model</th>
                        <th width="8%">Jumlah</th>
                        <th width="10%">Kondisi</th>
                        <th width="10%">Sumber Dana</th>
                        <th width="10%">Total Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $noItem = 1;
                    foreach ($unassigned['items'] as $it): 
                    ?>
                        <tr>
                            <td class="text-center"><?= $noItem++ ?></td>
                            <td class="text-center" style="font-family: monospace; font-size: 8.5pt; font-weight: bold;"><?= e($it['kode_barang']) ?></td>
                            <td>
                                <strong><?= e($it['nama_barang']) ?></strong>
                                <?php if (!empty($it['nomor_seri'])): ?>
                                    <div style="font-size: 8pt; color: #475569;">S/N: <?= e($it['nomor_seri']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= e($it['merk_model'] ?: '-') ?></td>
                            <td class="text-center"><strong><?= (int)$it['jumlah'] ?></strong> <?= e($it['satuan']) ?></td>
                            <td class="text-center">
                                <span class="kondisi-badge"><?= e($it['kondisi']) ?></span>
                            </td>
                            <td class="text-center" style="font-size: 8.5pt;"><?= e($it['sumber_dana']) ?></td>
                            <td class="text-right"><?= number_format($it['total_nilai'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">SUBTOTAL ASET TANPA RUANGAN:</th>
                        <th class="text-center"><?= (int)$unassigned['total_qty'] ?> Unit</th>
                        <th colspan="2" class="text-center"><?= (int)$unassigned['stat_baik'] ?> Baik</th>
                        <th class="text-right">Rp <?= number_format($unassigned['total_nilai'], 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>

            <!-- Signatures Tanpa Ruangan -->
            <div class="signature-grid">
                <div class="signature-cell">
                    Mengetahui,<br>
                    <strong>Kepala Sarana &amp; Prasarana</strong>
                    <div class="signature-space"></div>
                    <div class="signature-name"><?= !empty($namaKepalaSarpras) ? e($namaKepalaSarpras) : '( .................................................... )' ?></div>
                    <div class="signature-niy"><?= !empty($niyKepalaSarpras) ? 'NIY: ' . e($niyKepalaSarpras) : 'NIY: .......................................' ?></div>
                </div>
                <div class="signature-cell">
                    Palu, <?= date('d F Y') ?><br>
                    <strong>Petugas Gudang / Inventaris</strong>
                    <div class="signature-space"></div>
                    <div class="signature-name">( .................................................... )</div>
                    <div class="signature-niy">Staf Pengelola Sarpras</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- ============================================================== -->
    <!-- CETAK SINGLE RUANGAN / FILTER KHUSUS                           -->
    <!-- ============================================================== -->
    <?php 
    $targetRuangan = null;
    $targetItems = [];
    $targetTotalQty = 0;
    $targetTotalNilai = 0;

    if ($filter_ruangan === 'tanpa_ruangan') {
        $targetRuangan = $unassigned['ruangan'];
        $targetItems = $unassigned['items'];
        $targetTotalQty = $unassigned['total_qty'];
        $targetTotalNilai = $unassigned['total_nilai'];
    } elseif (!empty($ruanganList)) {
        $targetRuangan = $ruanganList[0]['ruangan'];
        $targetItems = $ruanganList[0]['items'];
        $targetTotalQty = $ruanganList[0]['total_qty'];
        $targetTotalNilai = $ruanganList[0]['total_nilai'];
    } else {
        $targetRuangan = $selectedRuanganData ?? [
            'nama_ruangan' => 'Ruangan Tidak Ditemukan',
            'kode_ruangan' => '-',
            'penanggung_jawab' => '-'
        ];
    }
    ?>

    <div class="doc-page">
        <?php renderKopSurat($kopSurat); ?>

        <div class="doc-title">LAPORAN DATA INVENTARIS RUANGAN</div>
        <div class="doc-subtitle">Ruangan: <strong><?= e($targetRuangan['nama_ruangan']) ?></strong></div>

        <!-- Metadata Ruangan -->
        <div class="room-meta-box">
            <table class="room-meta-table">
                <tr>
                    <td class="lbl">Nama Ruangan</td>
                    <td class="col">:</td>
                    <td class="val"><?= e($targetRuangan['nama_ruangan']) ?></td>
                    <td class="lbl" style="padding-left: 20px;">Penanggung Jawab</td>
                    <td class="col">:</td>
                    <td class="val"><?= e($targetRuangan['penanggung_jawab'] ?: 'Belum Ditentukan') ?></td>
                </tr>
                <tr>
                    <td class="lbl">Lokasi / Gedung</td>
                    <td class="col">:</td>
                    <td class="val"><?= e($targetRuangan['nama_bangunan'] ?? $targetRuangan['lokasi_gedung'] ?? 'Gedung Utama') ?></td>
                    <td class="lbl" style="padding-left: 20px;">Total Fisik Barang</td>
                    <td class="col">:</td>
                    <td class="val"><?= count($targetItems) ?> Jenis (<?= (int)$targetTotalQty ?> Unit Fisik)</td>
                </tr>
            </table>
        </div>

        <!-- Tabel Barang -->
        <table class="data-table">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="16%">Kode Barang</th>
                    <th width="28%">Nama Aset &amp; Spesifikasi</th>
                    <th width="14%">Merk / Model</th>
                    <th width="8%">Jumlah</th>
                    <th width="10%">Kondisi</th>
                    <th width="10%">Sumber Dana</th>
                    <th width="10%">Total Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($targetItems)): ?>
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: #64748b;">Tidak ada data aset terdaftar di ruangan ini.</td>
                    </tr>
                <?php else: 
                    $no = 1;
                    foreach ($targetItems as $it): 
                ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td class="text-center" style="font-family: monospace; font-size: 8.5pt; font-weight: bold;"><?= e($it['kode_barang']) ?></td>
                        <td>
                            <strong><?= e($it['nama_barang']) ?></strong>
                            <?php if (!empty($it['nomor_seri'])): ?>
                                <div style="font-size: 8pt; color: #475569;">S/N: <?= e($it['nomor_seri']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><?= e($it['merk_model'] ?: '-') ?></td>
                        <td class="text-center"><strong><?= (int)$it['jumlah'] ?></strong> <?= e($it['satuan']) ?></td>
                        <td class="text-center">
                            <span class="kondisi-badge"><?= e($it['kondisi']) ?></span>
                        </td>
                        <td class="text-center" style="font-size: 8.5pt;"><?= e($it['sumber_dana']) ?></td>
                        <td class="text-right"><?= number_format($it['total_nilai'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">TOTAL:</th>
                    <th class="text-center"><?= (int)$targetTotalQty ?> Unit</th>
                    <th colspan="2"></th>
                    <th class="text-right">Rp <?= number_format($targetTotalNilai, 0, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>

        <!-- Signatures -->
        <div class="signature-grid">
            <div class="signature-cell">
                Mengetahui,<br>
                <strong>Kepala Sarana &amp; Prasarana</strong>
                <div class="signature-space"></div>
                <div class="signature-name"><?= !empty($namaKepalaSarpras) ? e($namaKepalaSarpras) : '( .................................................... )' ?></div>
                <div class="signature-niy"><?= !empty($niyKepalaSarpras) ? 'NIY: ' . e($niyKepalaSarpras) : 'NIY: .......................................' ?></div>
            </div>
            <div class="signature-cell">
                Palu, <?= date('d F Y') ?><br>
                <strong>Penanggung Jawab Ruangan</strong>
                <div class="signature-space"></div>
                <div class="signature-name"><?= !empty($targetRuangan['penanggung_jawab']) ? e($targetRuangan['penanggung_jawab']) : '( .................................................... )' ?></div>
                <div class="signature-niy">Jabatan / Guru: <?= e($targetRuangan['penanggung_jawab'] ? $targetRuangan['nama_ruangan'] : '.......................................') ?></div>
            </div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
