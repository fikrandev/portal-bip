<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Rekapitulasi Nilai — Kelas <?= e($selectedKelas) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 24px;
            color: #1e293b;
            background-color: #f8fafc;
        }
        .container {
            max-width: 1080px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .kop-header {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .kop-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .kop-header h2 {
            margin: 4px 0 0 0;
            font-size: 15px;
            font-weight: 700;
            color: #0d9488;
        }
        .kop-header p {
            margin: 4px 0 0 0;
            font-size: 11px;
            color: #64748b;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            text-decoration: underline;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 24px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 240px;
            text-align: center;
        }
        .signature-space {
            height: 70px;
        }
        .no-print {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print { background: #0d9488; color: white; }
        .btn-close { background: #e2e8f0; color: #334155; }
        @media print {
            body { background: white; padding: 0; }
            .container { box-shadow: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Dokumen</button>
        <button onclick="window.close()" class="btn btn-close">✕ Tutup</button>
    </div>

    <!-- Kop Sekolah -->
    <div class="kop-header">
        <h1>YAYASAN BINA INSAN PALU</h1>
        <h2>PORTAL SISTEM PENILAIAN AKADEMIK</h2>
        <p>Jl. Banteng No. 10, Kel. Tatura Selatan, Kec. Palu Selatan, Kota Palu, Sulawesi Tengah</p>
    </div>

    <!-- Judul Dokumen -->
    <div class="doc-title">
        <h3>
            <?= !empty($selectedMapel) ? 'REKAPITULASI NILAI MATA PELAJARAN ' . strtoupper(e($selectedMapel)) : 'LEGER NILAI AKHIR HASIL BELAJAR SISWA' ?>
        </h3>
    </div>

    <!-- Meta Informasi -->
    <div class="meta-info">
        <div>
            <div>Kelas : <strong><?= e($selectedKelas) ?></strong></div>
            <div>Wadah : <strong><?= e($activeGroup['judul'] ?? '-') ?></strong> (Unit <?= e($activeGroup['unit'] ?? '-') ?>)</div>
        </div>
        <div style="text-align: right;">
            <div>Tahun Ajaran : <strong><?= e($activeGroup['nama_tahun'] ?? '2026/2027') ?></strong></div>
            <div>Semester : <strong><?= e($activeGroup['semester'] ?? 'Ganjil') ?></strong></div>
        </div>
    </div>

    <!-- Data Table -->
    <?php if (!empty($selectedMapel)): ?>
        <!-- Single Mapel View -->
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 70px;">NIS</th>
                    <th>Nama Siswa</th>
                    <th style="width: 90px;">Rerata Formatif</th>
                    <th style="width: 90px;">Sumatif LM</th>
                    <th style="width: 80px;">SAS</th>
                    <th style="width: 90px;">Nilai Akhir</th>
                    <th style="width: 70px;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekapSiswa as $idx => $rs): 
                    $mInfo = $rs['mapel'][$selectedMapel] ?? null;
                    $fmt = $mInfo['formatif_avg'] ?? null;
                    $lm = $mInfo['lm_avg'] ?? null;
                    $sas = $mInfo['sas'] ?? null;
                    $na = $mInfo['na'] ?? null;
                    $prd = $mInfo['predikat'] ?? '-';
                ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="text-center"><?= e($rs['siswa']['nis'] ?: '-') ?></td>
                        <td class="font-bold"><?= e($rs['siswa']['nama']) ?></td>
                        <td class="text-center"><?= $fmt !== null ? number_format($fmt, 1) : '-' ?></td>
                        <td class="text-center"><?= $lm !== null ? number_format($lm, 1) : '-' ?></td>
                        <td class="text-center"><?= $sas !== null ? number_format($sas, 1) : '-' ?></td>
                        <td class="text-center font-bold"><?= $na !== null ? number_format($na, 1) : '-' ?></td>
                        <td class="text-center font-bold"><?= $prd ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <!-- Leger View -->
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 70px;">NIS</th>
                    <th>Nama Siswa</th>
                    <?php foreach ($mapelList as $m): ?>
                        <th style="max-width: 90px;" class="text-center"><?= e($m) ?></th>
                    <?php endforeach; ?>
                    <th style="width: 60px;">Rerata</th>
                    <th style="width: 50px;">Rank</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekapSiswa as $idx => $rs): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="text-center"><?= e($rs['siswa']['nis'] ?: '-') ?></td>
                        <td class="font-bold"><?= e($rs['siswa']['nama']) ?></td>
                        <?php foreach ($mapelList as $m): 
                            $na = $rs['mapel'][$m]['na'] ?? null;
                        ?>
                            <td class="text-center"><?= $na !== null ? number_format($na, 1) : '-' ?></td>
                        <?php endforeach; ?>
                        <td class="text-center font-bold"><?= $rs['overall_avg'] !== null ? number_format($rs['overall_avg'], 1) : '-' ?></td>
                        <td class="text-center"><?= $rs['ranking'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Mengetahui,</div>
            <div style="font-weight: bold;">Kepala Sekolah</div>
            <div class="signature-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">( .................................................. )</div>
            <div>NIP / NPY : ...................................</div>
        </div>
        <div class="signature-box">
            <div>Palu, <?= date('d F Y') ?></div>
            <div style="font-weight: bold;">Wali Kelas <?= e($selectedKelas) ?></div>
            <div class="signature-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">( .................................................. )</div>
            <div>NIP / NPY : ...................................</div>
        </div>
    </div>
</div>

</body>
</html>
