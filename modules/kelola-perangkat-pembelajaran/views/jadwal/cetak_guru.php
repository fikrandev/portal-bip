<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Mengajar — <?= e($guru['nama']) ?></title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 15mm 10mm 15mm;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.3;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 11pt;
            font-weight: 700;
            margin: 2px 0;
        }
        .header p {
            font-size: 9pt;
            margin: 0;
        }
        .title-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .title-box h3 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        table.grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            table-layout: fixed;
        }
        table.grid th, table.grid td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }
        table.grid th {
            background: #f1f5f9;
            font-weight: bold;
            font-size: 9.5pt;
            text-transform: uppercase;
        }
        .jp-num {
            width: 7%;
            background: #f8fafc;
            font-weight: bold;
        }
        .subject-box {
            font-weight: bold;
            font-size: 9.5pt;
        }
        .class-box {
            font-size: 8.5pt;
            font-weight: 600;
            color: #1e40af;
            margin-top: 2px;
        }
        .time-box {
            font-size: 7.5pt;
            color: #666;
        }
        .signature-table {
            width: 100%;
            margin-top: 15px;
        }
        .signature-table td {
            text-align: center;
            vertical-align: top;
            font-size: 9.5pt;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- No Print Action Bar -->
    <div class="no-print" style="background:#1e293b; color:#fff; padding:10px 20px; display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; border-radius:8px;">
        <div style="font-family:sans-serif; font-size:12px; font-weight:bold;">
            📄 Cetak Jadwal Mengajar Guru: <?= e($guru['nama']) ?>
        </div>
        <div>
            <button onclick="window.print()" style="background:#10b981; color:#fff; border:none; padding:6px 14px; border-radius:6px; font-weight:bold; cursor:pointer; margin-right:8px;">
                🖨️ Cetak Dokumen (Print)
            </button>
            <button onclick="window.close()" style="background:#64748b; color:#fff; border:none; padding:6px 12px; border-radius:6px; font-weight:bold; cursor:pointer;">
                ✕ Tutup
            </button>
        </div>
    </div>

    <!-- Kop Sekolah -->
    <div class="header">
        <h1>YAYASAN BINA INSAN PALU</h1>
        <h2>JADWAL MENGAJAR GURU TAHUN AJARAN <?= strtoupper(e($grup['tahun_ajaran'])) ?></h2>
        <p>Unit: <?= e($grup['jenjang']) ?> IT Bina Insan Palu | Semester: <?= e($grup['semester']) ?></p>
    </div>

    <div class="title-box">
        <h3>GURU PENGAMPU: <?= strtoupper(e($guru['nama'])) ?> (NIP/NUPTK: <?= e($guru['nip'] ?? $guru['nuptk'] ?? '-') ?>)</h3>
        <span style="font-size:9pt; font-weight:bold;">Versi: <?= e($grup['nama_grup']) ?></span>
    </div>

    <!-- Grid Table -->
    <table class="grid">
        <thead>
            <tr>
                <th class="jp-num">Jam Ke</th>
                <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day): ?>
                    <th><?= $day ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php for ($jk = 1; $jk <= $maxJp; $jk++): ?>
            <tr>
                <td class="jp-num">
                    JP <?= $jk ?>
                </td>
                <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day): ?>
                    <?php $item = $scheduleItems[$day][$jk] ?? null; ?>
                    <td>
                        <?php if ($item): ?>
                            <div class="subject-box"><?= e($item['mata_pelajaran']) ?></div>
                            <div class="class-box">🏫 <?= e($item['nama_kelas']) ?></div>
                            <div class="time-box"><?= substr($item['jam_mulai'], 0, 5) ?> - <?= substr($item['jam_selesai'], 0, 5) ?></div>
                        <?php else: ?>
                            <span style="color:#aaa;">-</span>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- Signatures -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <br>
                Guru Pengampu Bersangkutan,
                <br><br><br><br>
                ( <strong><?= e($guru['nama']) ?></strong> )<br>
                NIP/NIY: <?= e($guru['nip'] ?? '.......................................') ?>
            </td>
            <td style="width: 50%;">
                Palu, <?= date('d F Y') ?><br>
                Kepala Satuan Pendidikan,
                <br><br><br><br>
                ( <strong>......................................................</strong> )<br>
                NIP/NIY: .......................................
            </td>
        </tr>
    </table>

</body>
</html>
