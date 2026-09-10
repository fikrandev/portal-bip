<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara & Presensi Mapel: <?= htmlspecialchars($mapel) ?> - Kelas <?= htmlspecialchars($cleanKelas) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            font-size: 11pt;
            line-height: 1.3;
        }
        .container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .kop-surat h1 {
            margin: 0;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-surat h2 {
            margin: 2px 0 0 0;
            font-size: 13pt;
            font-weight: bold;
        }
        .kop-surat p {
            margin: 2px 0 0 0;
            font-size: 9pt;
            font-style: italic;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 15px;
        }
        .doc-title h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-title p {
            margin: 2px 0 0 0;
            font-size: 10pt;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .summary-box {
            border: 1px solid #000;
            padding: 6px 10px;
            margin-bottom: 15px;
            font-size: 9.5pt;
            background: #fafafa;
        }
        .signature-section {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            font-size: 10pt;
        }
        .signature-space {
            height: 60px;
        }
        .no-print {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 24px;
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            background: #059669;
            color: #fff;
            padding: 6px 16px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 12px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Dokumen A4</button>
</div>

<div class="container">
    <!-- Kop Surat Yayasan Bina Insan Palu -->
    <div class="kop-surat">
        <h1>YAYASAN BINA INSAN PALU</h1>
        <h2>SIT BINA INSAN PALU</h2>
        <p>Jl. Trans Sulawesi No. 12, Tondo, Kec. Mantikulore, Kota Palu, Sulawesi Tengah &bull; Telp: (0451) 481234</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <h3>BERITA ACARA &amp; PRESENSI PEMBELAJARAN MAPEL</h3>
        <p>Tahun Pelajaran 2026/2027 &bull; Semester Ganjil</p>
    </div>

    <!-- Informasi Pembelajaran -->
    <table class="meta-table">
        <tr>
            <td style="width: 18%;"><strong>Mata Pelajaran</strong></td>
            <td style="width: 3%;">:</td>
            <td style="width: 39%;"><?= htmlspecialchars($mapel) ?></td>
            <td style="width: 16%;"><strong>Tanggal</strong></td>
            <td style="width: 3%;">:</td>
            <td style="width: 21%;"><?= date('d F Y', strtotime($tanggal)) ?></td>
        </tr>
        <tr>
            <td><strong>Rombel / Kelas</strong></td>
            <td>:</td>
            <td>Kelas <?= htmlspecialchars($cleanKelas) ?></td>
            <td><strong>Pertemuan Ke-</strong></td>
            <td>:</td>
            <td>Pertemuan Ke-<?= $pertemuan_ke ?></td>
        </tr>
        <tr>
            <td><strong>Guru Pengampu</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($guru['nama'] ?? 'Guru Pengampu') ?><?= !empty($guru['gelar']) ? ', ' . htmlspecialchars($guru['gelar']) : '' ?></td>
            <td><strong>Alokasi Waktu</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($session['jp'] ?? 2) ?> JP (Jam Ke: <?= htmlspecialchars($session['jam_ke'] ?? '-') ?>)</td>
        </tr>
        <tr>
            <td><strong>Materi / Topik</strong></td>
            <td>:</td>
            <td colspan="4"><?= htmlspecialchars($session['materi'] ?: 'Pembelajaran Reguler') ?></td>
        </tr>
    </table>

    <!-- Tabel Siswa -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">NIS</th>
                <th>Nama Siswa</th>
                <th style="width: 40px;">L/P</th>
                <th style="width: 70px;">Status</th>
                <th>Catatan / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totH = 0; $totS = 0; $totI = 0; $totA = 0; $totT = 0;
            if (empty($siswaList)): ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data siswa untuk rombel ini.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($siswaList as $idx => $sw): 
                    $st = $sw['status'] ?? 'H';
                    if ($st === 'H') $totH++;
                    elseif ($st === 'S') $totS++;
                    elseif ($st === 'I') $totI++;
                    elseif ($st === 'A') $totA++;
                    elseif ($st === 'T') $totT++;

                    $statusText = [
                        'H' => 'Hadir',
                        'S' => 'Sakit',
                        'I' => 'Izin',
                        'A' => 'Alpa',
                        'T' => 'Terlambat'
                    ][$st] ?? 'Hadir';
                ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="text-center"><?= htmlspecialchars($sw['nis'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($sw['nama']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($sw['jenis_kelamin'] ?: '-') ?></td>
                        <td class="text-center font-bold"><?= $statusText ?></td>
                        <td><?= htmlspecialchars($sw['catatan'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Ringkasan Kehadiran -->
    <div class="summary-box">
        <strong>Rekapitulasi Kehadiran:</strong>
        Hadir (H): <strong><?= $totH ?></strong> | 
        Sakit (S): <strong><?= $totS ?></strong> | 
        Izin (I): <strong><?= $totI ?></strong> | 
        Alpa (A): <strong><?= $totA ?></strong> | 
        Terlambat (T): <strong><?= $totT ?></strong> | 
        Total Siswa: <strong><?= count($siswaList) ?></strong>
    </div>

    <!-- Tanda Tangan Resmi -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 50%; text-align: center;">
                    Mengetahui,<br>
                    Kepala Sekolah<br>
                    <div class="signature-space"></div>
                    <strong>( ............................................................ )</strong><br>
                    NIY. ....................................................
                </td>
                <td style="width: 50%; text-align: center;">
                    Palu, <?= date('d F Y', strtotime($tanggal)) ?><br>
                    Guru Mata Pelajaran<br>
                    <div class="signature-space"></div>
                    <strong><u><?= htmlspecialchars($guru['nama'] ?? 'Guru Pengampu') ?><?= !empty($guru['gelar']) ? ', ' . htmlspecialchars($guru['gelar']) : '' ?></u></strong><br>
                    NIY. <?= htmlspecialchars($guru['niy'] ?? '....................................................') ?>
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
