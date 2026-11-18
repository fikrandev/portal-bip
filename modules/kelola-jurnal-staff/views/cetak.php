<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Jurnal Staff & Support System - Bina Insan Palu</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background-color: #f8fafc;
            padding: 20px;
            line-height: 1.3;
        }

        .paper {
            background: #fff;
            max-width: 950px;
            margin: 0 auto;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }

        /* Kop Surat Formal */
        .header-kop {
            text-align: center;
            border-bottom: 2.5px double #000;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header-kop h2 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-kop h3 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .header-kop p {
            font-size: 9.5pt;
            color: #222;
            margin-top: 4px;
        }

        /* Judul Laporan */
        .title-section {
            text-align: center;
            margin-bottom: 18px;
        }

        .title-section h1 {
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .title-section p {
            font-size: 10pt;
            margin-top: 4px;
        }

        /* Meta Filter Box */
        .meta-box {
            display: flex;
            justify-content: space-between;
            font-size: 9.5pt;
            margin-bottom: 14px;
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        /* Tabel Laporan */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 18px;
        }

        table.report-table th, 
        table.report-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        table.report-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9pt;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
            border: 1px solid #999;
        }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 250px;
            text-align: center;
            font-size: 10pt;
        }

        .signature-space {
            height: 65px;
        }

        .print-btn-bar {
            max-width: 950px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            font-family: sans-serif;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
            border: none;
            text-decoration: none;
        }

        .btn-print {
            background-color: #0284c7;
            color: #fff;
        }

        .btn-close {
            background-color: #e2e8f0;
            color: #334155;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .print-btn-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <button onclick="window.close()" class="btn btn-close">Tutup</button>
        <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Dokumen</button>
    </div>

    <div class="paper">
        <!-- Kop Surat -->
        <div class="header-kop">
            <h2>YAYASAN BINA INSAN PALU</h2>
            <h3>LAPORAN MONITORING AKTIVITAS & JURNAL STAFF</h3>
            <p>Jl. Banteng No. 12, Kel. Birobuli Selatan, Kec. Palu Selatan, Kota Palu, Sulawesi Tengah</p>
        </div>

        <!-- Judul Laporan -->
        <div class="title-section">
            <h1>REKAPITULASI CATATAN JURNAL KERJA STAFF</h1>
            <p>Periode: <?= date('d F Y', strtotime($startDate)) ?> s/d <?= date('d F Y', strtotime($endDate)) ?></p>
        </div>

        <!-- Meta Info -->
        <div class="meta-box">
            <div>
                <strong>Jabatan / Divisi:</strong> <?= htmlspecialchars($jabatan ?: 'Semua Divisi / Unit') ?><br>
                <strong>Status Filter:</strong> <?= htmlspecialchars($status ? strtoupper($status) : 'Semua Status') ?>
            </div>
            <div style="text-align: right;">
                <strong>Total Catatan:</strong> <?= count($jurnalList) ?> Data<br>
                <strong>Tugas Selesai:</strong> <?= $totalSelesai ?> Data
            </div>
        </div>

        <!-- Tabel Laporan -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 85px;">Tanggal</th>
                    <th style="width: 85px;">Sesi Jam</th>
                    <th style="width: 150px;">Nama Staff & Jabatan</th>
                    <th>Aktivitas & Job Desk</th>
                    <th>Uraian Hasil Kerja</th>
                    <th style="width: 75px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jurnalList)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 25px; color: #666;">
                            Tidak ada data jurnal kerja staff pada periode dan kriteria ini.
                        </td>
                    </tr>
                <?php else:
                    $no = 1;
                    foreach ($jurnalList as $row):
                ?>
                    <tr>
                        <td style="text-align: center;"><?= $no++ ?></td>
                        <td style="text-align: center;"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                        <td style="text-align: center; font-family: monospace; font-size: 8.5pt;">
                            <?= htmlspecialchars($row['jam_rentang']) ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['nama_staff']) ?></strong><br>
                            <span style="font-size: 8.5pt; color: #444;"><?= htmlspecialchars($row['jabatan']) ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($row['aktivitas']) ?></strong><br>
                            <span style="font-size: 8pt; color: #666; font-family: monospace;"><?= htmlspecialchars($row['kode_jurnal']) ?></span>
                        </td>
                        <td>
                            <?= nl2br(htmlspecialchars($row['uraian_hasil'])) ?>
                            <?php if (!empty($row['kendala'])): ?>
                                <div style="margin-top: 4px; font-size: 8.5pt; color: #854d0e;">
                                    <em>Kendala: <?= htmlspecialchars($row['kendala']) ?></em>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge-status">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Mengetahui,</p>
                <p><strong>Kepala Sekolah / Manajer Terkait</strong></p>
                <div class="signature-space"></div>
                <p>_____________________________</p>
                <p style="font-size: 9pt;">NIP / NIY.</p>
            </div>

            <div class="signature-box">
                <p>Palu, <?= date('d F Y') ?></p>
                <p><strong>Koordinator Administrasi & HRD</strong></p>
                <div class="signature-space"></div>
                <p>_____________________________</p>
                <p style="font-size: 9pt;">NIP / NIY.</p>
            </div>
        </div>
    </div>

</body>
</html>
