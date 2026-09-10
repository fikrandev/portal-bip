<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekapitulasi Presensi: Kelas <?= htmlspecialchars($cleanKelas) ?></title>
    <style>
        @page {
            size: <?= ($mode === 'siswa') ? 'A4 portrait' : 'A4 landscape' ?>;
            margin: 12mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
            font-size: 10pt;
            line-height: 1.25;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-surat h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-surat h2 {
            margin: 2px 0 0 0;
            font-size: 12pt;
            font-weight: bold;
        }
        .kop-surat p {
            margin: 2px 0 0 0;
            font-size: 8.5pt;
            font-style: italic;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title h3 {
            margin: 0;
            font-size: 11.5pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-title p {
            margin: 2px 0 0 0;
            font-size: 9pt;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9pt;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 12px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 3px 4px;
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
        .signature-section {
            margin-top: 20px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            font-size: 9.5pt;
        }
        .signature-space {
            height: 55px;
        }
        .no-print {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 24px;
            margin-bottom: 15px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            background: #0d9488;
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
    <!-- Kop Surat -->
    <div class="kop-surat">
        <h1>YAYASAN BINA INSAN PALU</h1>
        <h2>SIT BINA INSAN PALU</h2>
        <p>Jl. Trans Sulawesi No. 12, Tondo, Kec. Mantikulore, Kota Palu, Sulawesi Tengah &bull; Telp: (0451) 481234</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <?php if ($mode === 'kelas'): ?>
            <h3>REKAPITULASI PRESENSI HARIAN KELAS</h3>
            <p>Bulan: <?= date('F Y', strtotime($selectedBulan . '-01')) ?> &bull; Tahun Pelajaran 2026/2027</p>
        <?php elseif ($mode === 'mapel'): ?>
            <h3>REKAPITULASI PRESENSI MATA PELAJARAN</h3>
            <p>Mata Pelajaran: <?= htmlspecialchars($selectedMapel) ?> &bull; Tahun Pelajaran 2026/2027</p>
        <?php else: ?>
            <h3>KARTU RIWAYAT PRESENSI SISWA</h3>
            <p>Tahun Pelajaran 2026/2027</p>
        <?php endif; ?>
    </div>

    <!-- Meta Info -->
    <table class="meta-table">
        <tr>
            <td style="width: 15%;"><strong>Rombel / Kelas</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 40%;">Kelas <?= htmlspecialchars($cleanKelas) ?></td>
            <?php if ($mode === 'mapel'): ?>
                <td style="width: 15%;"><strong>Mata Pelajaran</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 26%;"><?= htmlspecialchars($selectedMapel) ?></td>
            <?php elseif ($mode === 'kelas'): ?>
                <td style="width: 15%;"><strong>Periode</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 26%;"><?= date('F Y', strtotime($selectedBulan . '-01')) ?></td>
            <?php else: ?>
                <td style="width: 15%;"><strong>Nama Siswa</strong></td>
                <td style="width: 2%;">:</td>
                <td style="width: 26%;"><?= htmlspecialchars($targetSiswa['nama'] ?? '-') ?> (NIS: <?= htmlspecialchars($targetSiswa['nis'] ?? '-') ?>)</td>
            <?php endif; ?>
        </tr>
    </table>

    <!-- Konten Rekap -->
    <?php if ($mode === 'kelas'): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 65px;">NIS</th>
                    <th>Nama Siswa</th>
                    <th style="width: 28px;">L/P</th>
                    <?php foreach ($sessions as $ses): ?>
                        <th style="width: 22px;"><?= date('d', strtotime($ses['tanggal'])) ?></th>
                    <?php endforeach; ?>
                    <th style="width: 25px;">H</th>
                    <th style="width: 25px;">S</th>
                    <th style="width: 25px;">I</th>
                    <th style="width: 25px;">A</th>
                    <th style="width: 25px;">T</th>
                    <th style="width: 45px;">%</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekapData as $idx => $row): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['siswa']['nis'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($row['siswa']['nama']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['siswa']['jenis_kelamin'] ?: '-') ?></td>
                        <?php foreach ($sessions as $ses): 
                            $st = $row['daily'][$ses['id']] ?? '-';
                        ?>
                            <td class="text-center <?= ($st !== 'H' && $st !== '-') ? 'font-bold' : '' ?>">
                                <?= $st ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="text-center font-bold"><?= $row['h'] ?></td>
                        <td class="text-center font-bold"><?= $row['s'] ?></td>
                        <td class="text-center font-bold"><?= $row['i'] ?></td>
                        <td class="text-center font-bold"><?= $row['a'] ?></td>
                        <td class="text-center font-bold"><?= $row['t'] ?></td>
                        <td class="text-center font-bold"><?= $row['persen'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($mode === 'mapel'): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">No</th>
                    <th style="width: 65px;">NIS</th>
                    <th>Nama Siswa</th>
                    <th style="width: 28px;">L/P</th>
                    <?php foreach ($sessions as $ses): ?>
                        <th style="width: 32px;" title="<?= date('d/m', strtotime($ses['tanggal'])) ?>">P<?= $ses['pertemuan_ke'] ?></th>
                    <?php endforeach; ?>
                    <th style="width: 25px;">H</th>
                    <th style="width: 25px;">S</th>
                    <th style="width: 25px;">I</th>
                    <th style="width: 25px;">A</th>
                    <th style="width: 25px;">T</th>
                    <th style="width: 45px;">%</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekapData as $idx => $row): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['siswa']['nis'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($row['siswa']['nama']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['siswa']['jenis_kelamin'] ?: '-') ?></td>
                        <?php foreach ($sessions as $ses): 
                            $st = $row['pertemuan'][$ses['id']] ?? '-';
                        ?>
                            <td class="text-center <?= ($st !== 'H' && $st !== '-') ? 'font-bold' : '' ?>">
                                <?= $st ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="text-center font-bold"><?= $row['h'] ?></td>
                        <td class="text-center font-bold"><?= $row['s'] ?></td>
                        <td class="text-center font-bold"><?= $row['i'] ?></td>
                        <td class="text-center font-bold"><?= $row['a'] ?></td>
                        <td class="text-center font-bold"><?= $row['t'] ?></td>
                        <td class="text-center font-bold"><?= $row['persen'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <!-- Mode Siswa Individual -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 90px;">Tanggal</th>
                    <th style="width: 80px;">Tipe</th>
                    <th>Mata Pelajaran / Agenda Sesi</th>
                    <th style="width: 80px;">Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $h = 0; $s = 0; $i = 0; $a = 0; $t = 0;
                foreach ($studentHistory as $idx => $sh): 
                    if ($sh['status'] === 'H') $h++;
                    elseif ($sh['status'] === 'S') $s++;
                    elseif ($sh['status'] === 'I') $i++;
                    elseif ($sh['status'] === 'A') $a++;
                    elseif ($sh['status'] === 'T') $t++;
                ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($sh['tanggal'])) ?></td>
                        <td class="text-center uppercase"><?= htmlspecialchars($sh['tipe_presensi']) ?></td>
                        <td><?= htmlspecialchars($sh['mata_pelajaran'] ?? '-') ?></td>
                        <td class="text-center font-bold"><?= htmlspecialchars($sh['status']) ?></td>
                        <td><?= htmlspecialchars($sh['catatan'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="border: 1px solid #000; padding: 6px 10px; margin-bottom: 15px; font-size: 9pt;">
            <strong>Kumulatif Presensi Siswa:</strong>
            Hadir: <strong><?= $h ?></strong> | Sakit: <strong><?= $s ?></strong> | Izin: <strong><?= $i ?></strong> | Alpa: <strong><?= $a ?></strong> | Terlambat: <strong><?= $t ?></strong>
        </div>
    <?php endif; ?>

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
                    Palu, <?= date('d F Y') ?><br>
                    Wali Kelas / Guru Pengampu<br>
                    <div class="signature-space"></div>
                    <strong>( ............................................................ )</strong><br>
                    NIY. ....................................................
                </td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
