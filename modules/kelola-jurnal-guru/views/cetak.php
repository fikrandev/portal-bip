<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Jurnal Mengajar Guru - Yayasan Bina Insan Palu</title>
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
            max-width: 900px;
            margin: 0 auto;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }

        /* Kop Surat */
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

        /* Info Filter Box */
        .meta-box {
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
            margin-bottom: 14px;
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        /* Tabel Formal */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 18px;
        }

        table.report-table th, 
        table.report-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        table.report-table th {
            background-color: #e2e8f0;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9pt;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* Tanda Tangan */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 35px;
            padding: 0 20px;
            page-break-inside: avoid;
        }

        .sig-box {
            text-align: center;
            width: 250px;
            font-size: 10.5pt;
        }

        .sig-space {
            height: 65px;
        }

        /* Toolbar Layar */
        .no-print-toolbar {
            max-width: 900px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 16px;
            background: #1e293b;
            color: #fff;
            border-radius: 8px;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 13px;
        }

        .btn-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-print:hover {
            background: #1d4ed8;
        }

        .btn-close {
            background: #475569;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                padding: 10mm 15mm;
                max-width: 100%;
            }
            .no-print-toolbar {
                display: none;
            }
            table.report-table th {
                background-color: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Layar Non-Print -->
    <div class="no-print-toolbar">
        <div>
            <strong>Mode Cetak Laporan KBM</strong> &bull; Total <?= count($jurnalList) ?> jurnal (<?= $totalJP ?> JP)
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-print">🖨️ Cetak / Simpan PDF</button>
            <button onclick="window.close()" class="btn-close">Tutup</button>
        </div>
    </div>

    <div class="paper">
        <!-- Kop Surat -->
        <div class="header-kop">
            <h2>Yayasan Bina Insan Palu</h2>
            <h3>SIT Bina Insan Palu</h3>
            <p>Jalan Basuki Rahmat No. 12, Birobuli Utara, Kec. Palu Selatan, Kota Palu, Sulawesi Tengah</p>
            <p>Website: www.binainsanpalu.sch.id &bull; Email: info@binainsanpalu.sch.id</p>
        </div>

        <!-- Judul Dokumen -->
        <div class="title-section">
            <h1>Rekapitulasi Jurnal Mengajar & Beban Mengajar (JP) Guru</h1>
            <p>
                Periode: <strong><?= !empty($startDate) ? date('d/m/Y', strtotime($startDate)) : 'Awal' ?></strong> 
                s.d. 
                <strong><?= !empty($endDate) ? date('d/m/Y', strtotime($endDate)) : 'Sekarang' ?></strong>
            </p>
        </div>

        <!-- Meta Box -->
        <div class="meta-box">
            <div>
                <strong>Guru Pengajar:</strong> <?= $selectedGuru ? e($selectedGuru['nama']) . ' (' . ($selectedGuru['nip'] ?? $selectedGuru['niy'] ?? '-') . ')' : 'Semua Guru' ?>
            </div>
            <div>
                <strong>Kelas:</strong> <?= !empty($kelas) ? e($kelas) : 'Semua Kelas' ?>
            </div>
            <div>
                <strong>Total Pertemuan:</strong> <?= count($jurnalList) ?> Sesi
            </div>
            <div>
                <strong>Total Beban:</strong> <?= $totalJP ?> JP
            </div>
        </div>

        <!-- Tabel 1: Rekap Jam Mengajar per Guru -->
        <?php if (!$selectedGuru && count($rekapPerGuru) > 1): ?>
            <p style="font-size: 10.5pt; font-weight: bold; margin-bottom: 6px;">I. Rekapitulasi Beban Jam Pelajaran (JP) per Guru</p>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th>Nama Guru</th>
                        <th>NIP / NIY</th>
                        <th style="width: 120px;">Jml Pertemuan</th>
                        <th style="width: 100px;">Total JP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $noG = 1; foreach ($rekapPerGuru as $rg): ?>
                        <tr>
                            <td class="text-center"><?= $noG++ ?></td>
                            <td class="font-bold"><?= e($rg['nama']) ?></td>
                            <td class="text-center"><?= e($rg['nip'] !== '-' ? $rg['nip'] : $rg['niy']) ?></td>
                            <td class="text-center"><?= $rg['total_pertemuan'] ?> kali</td>
                            <td class="text-center font-bold"><?= $rg['total_jp'] ?> JP</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="3" class="text-right">TOTAL KESELURUHAN</td>
                        <td class="text-center"><?= count($jurnalList) ?> kali</td>
                        <td class="text-center"><?= $totalJP ?> JP</td>
                    </tr>
                </tfoot>
            </table>
            <p style="font-size: 10.5pt; font-weight: bold; margin-bottom: 6px; margin-top: 16px;">II. Rincian Pelaksanaan KBM</p>
        <?php endif; ?>

        <!-- Tabel 2: Rincian Kegiatan KBM -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 75px;">Tanggal</th>
                    <th style="width: 65px;">Jam Ke</th>
                    <?php if (!$selectedGuru): ?>
                        <th>Guru Pengajar</th>
                    <?php endif; ?>
                    <th style="width: 60px;">Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th style="width: 45px;">JP</th>
                    <th>Materi Pokok & Kegiatan</th>
                    <th style="width: 80px;">Kehadiran</th>
                    <th style="width: 75px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jurnalList)): ?>
                    <tr>
                        <td colspan="<?= $selectedGuru ? 9 : 10 ?>" class="text-center" style="padding: 20px; font-style: italic;">
                            Tidak ada data jurnal mengajar pada periode yang dipilih.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($jurnalList as $j): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($j['tanggal'])) ?></td>
                            <td class="text-center">Jam Ke-<?= e($j['jam_ke']) ?></td>
                            <?php if (!$selectedGuru): ?>
                                <td><?= e($j['guru_nama_lengkap']) ?></td>
                            <?php endif; ?>
                            <td class="text-center font-bold"><?= e($j['kelas']) ?></td>
                            <td><?= e($j['mata_pelajaran']) ?></td>
                            <td class="text-center font-bold"><?= (int)($j['jumlah_jp'] ?? 2) ?></td>
                            <td>
                                <strong><?= e($j['materi_pokok']) ?></strong>
                                <?php if (!empty($j['catatan_kelas'])): ?>
                                    <div style="font-size: 8.5pt; color: #444; margin-top: 2px;">
                                        <em>Catatan: <?= e($j['catatan_kelas']) ?></em>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" style="font-size: 8.5pt;">
                                <?= e($j['kehadiran_summary'] ?: 'Hadir') ?>
                            </td>
                            <td class="text-center" style="font-size: 8.5pt; text-transform: uppercase;">
                                <?= e($j['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="<?= $selectedGuru ? 5 : 6 ?>" class="text-right">TOTAL BEBAN MENGAJAR</td>
                    <td class="text-center"><?= $totalJP ?> JP</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <div class="sig-box">
                <p>Mengetahui,</p>
                <p><strong>Kepala Sekolah</strong></p>
                <div class="sig-space"></div>
                <p style="text-decoration: underline; font-weight: bold;">( .................................................. )</p>
                <p>NIP/NIY. .....................................</p>
            </div>
            <div class="sig-box">
                <p>Palu, <?= date('d F Y') ?></p>
                <p><strong>Wakil Kurikulum</strong></p>
                <div class="sig-space"></div>
                <p style="text-decoration: underline; font-weight: bold;">( .................................................. )</p>
                <p>NIP/NIY. .....................................</p>
            </div>
        </div>
    </div>

</body>
</html>

