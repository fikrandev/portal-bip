<?php
/**
 * Tampilan Cetak Laporan Aset (PDF / Print)
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Aset Sarpras</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 12pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }
        .kop-image {
            max-width: 100%;
            height: auto;
            max-height: 120px;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .subtitle {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .signature-section {
            margin-top: 40px;
            width: 100%;
            display: table;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 70px;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            body {
                font-size: 11pt;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <!-- Tombol Print (Sembunyi saat dicetak) -->
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; background: #2563eb; color: #fff; border: none; border-radius: 5px; cursor: pointer;">
            Cetak PDF / Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; background: #64748b; color: #fff; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <!-- Kop Surat -->
    <?php if (!empty($kopSurat)): ?>
    <div class="header">
        <img src="<?= asset($kopSurat) ?>" alt="Kop Surat" class="kop-image">
    </div>
    <?php else: ?>
    <div class="header">
        <h2 style="margin:0;">LAPORAN ASET INVENTARIS</h2>
    </div>
    <?php endif; ?>

    <div class="title">LAPORAN DATA INVENTARIS RUANGAN</div>
    <div class="subtitle">Ruangan: <?= htmlspecialchars($namaRuanganCetak) ?></div>

    <!-- Tabel Data -->
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode Barang</th>
                <th width="30%">Nama Barang</th>
                <th width="10%">Kondisi</th>
                <th width="20%">Asal Anggaran</th>
                <th width="20%">Tahun / Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data aset untuk laporan ini.</td>
                </tr>
            <?php else: $no = 1; foreach ($items as $item): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['kode_barang']) ?></td>
                    <td><?= htmlspecialchars($item['nama_barang']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['kondisi_barang']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($item['nama_anggaran'] ?? '-') ?></td>
                    <td>
                        Thn: <?= htmlspecialchars($item['tahun_perolehan'] ?? '-') ?><br>
                        Rp <?= number_format($item['harga_perolehan'] ?? 0, 0, ',', '.') ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <div class="signature-box">
            Mengetahui,<br>
            Kepala Sarpras
            <div class="signature-name">
                <?= !empty($namaKepalaSarpras) ? htmlspecialchars($namaKepalaSarpras) : '( ...................................... )' ?>
            </div>
            <?php if (!empty($niyKepalaSarpras)): ?>
                NIY: <?= htmlspecialchars($niyKepalaSarpras) ?>
            <?php endif; ?>
        </div>
        <div class="signature-box">
            Palembang, <?= date('d F Y') ?><br>
            Penanggung Jawab Ruangan
            <div class="signature-name">
                <?= !empty($namaPenanggungJawab) ? htmlspecialchars($namaPenanggungJawab) : '( ...................................... )' ?>
            </div>
            <?php if (!empty($niyPenanggungJawab)): ?>
                NIY: <?= htmlspecialchars($niyPenanggungJawab) ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
