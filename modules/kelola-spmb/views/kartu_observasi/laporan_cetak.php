<?php
/**
 * View Cetak Laporan Siswa Tidak Absen QR Observasi (Per Sesi & Per Ruangan)
 * Portal BIP
 */
$groups = $laporan['groups'] ?? [];
$sum = $laporan['summary'] ?? ['total_peserta' => 0, 'total_hadir' => 0, 'total_tidak_hadir' => 0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Siswa Tidak Absen QR Observasi</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #0f172a; background: #f8fafc; padding: 18px; }
        .toolbar { max-width: 210mm; margin: 0 auto 14px auto; display: flex; justify-content: space-between; align-items: center; background: #0f172a; color: #fff; padding: 10px 16px; border-radius: 10px; }
        .btn { padding: 7px 14px; border: none; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; background: #2563eb; color: #fff; }
        .sheet { max-width: 210mm; margin: 0 auto; background: #fff; padding: 16mm 14mm; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
        h1 { font-size: 15px; text-align: center; text-transform: uppercase; margin-bottom: 4px; }
        .sub { text-align: center; font-size: 11px; color: #475569; margin-bottom: 14px; }
        .summary { display: flex; gap: 10px; margin-bottom: 16px; }
        .sum-box { flex: 1; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 10px; text-align: center; }
        .sum-val { font-size: 16px; font-weight: 800; }
        .grp { margin-bottom: 16px; page-break-inside: avoid; }
        .grp-head { background: #e2e8f0; padding: 6px 10px; font-weight: 700; border: 1px solid #94a3b8; border-bottom: none; display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #94a3b8; padding: 5px 7px; text-align: left; }
        th { background: #f1f5f9; font-size: 10px; text-transform: uppercase; }
        .center { text-align: center; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none; padding: 8mm; }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div style="font-weight:700;font-size:13px;">Laporan Siswa Tidak Absen QR (Per Sesi &amp; Per Ruangan)</div>
        <button class="btn" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <div class="sheet">
        <h1>Laporan Calon Siswa Tidak Absen QR Code Observasi</h1>
        <div class="sub"><?= htmlspecialchars($activeGroup['nama_group'] ?? 'SPMB SIT Bina Insan Palu') ?> &bull; Dicetak: <?= date('d/m/Y H:i') ?></div>

        <div class="summary">
            <div class="sum-box"><div>Total Peserta</div><div class="sum-val"><?= (int)$sum['total_peserta'] ?></div></div>
            <div class="sum-box"><div>Sudah Absen QR</div><div class="sum-val" style="color:#15803d;"><?= (int)$sum['total_hadir'] ?></div></div>
            <div class="sum-box"><div>Tidak Absen QR</div><div class="sum-val" style="color:#be123c;"><?= (int)$sum['total_tidak_hadir'] ?></div></div>
        </div>

        <?php foreach ($groups as $grp): ?>
            <div class="grp">
                <div class="grp-head">
                    <span>
                        <?= htmlspecialchars($grp['nama_ruangan']) ?> — <?= htmlspecialchars($grp['sesi_nama']) ?>
                        <?= !empty($grp['tgl_sesi']) ? ' (' . date('d/m/Y', strtotime($grp['tgl_sesi'])) . ' ' . $grp['jam_mulai'] . '-' . $grp['jam_selesai'] . ')' : '' ?>
                        <?= !empty($grp['pengawas']) ? ' | Pengawas: ' . htmlspecialchars($grp['pengawas']) : '' ?>
                    </span>
                    <span>Hadir: <?= (int)$grp['total_hadir'] ?> / <?= (int)$grp['total_peserta'] ?> | Tidak Absen: <?= (int)$grp['total_tidak_hadir'] ?></span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="center" style="width:28px;">No</th>
                            <th style="width:95px;">No. Peserta</th>
                            <th>Nama Calon Siswa</th>
                            <th class="center" style="width:55px;">Jenjang</th>
                            <th style="width:110px;">No. WA Ortu</th>
                            <th class="center" style="width:95px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grp['tidak_hadir_items'])): ?>
                            <tr><td colspan="6" class="center" style="color:#15803d;font-weight:700;">Seluruh peserta pada sesi &amp; ruangan ini hadir.</td></tr>
                        <?php else: ?>
                            <?php foreach ($grp['tidak_hadir_items'] as $idx => $th): ?>
                                <tr>
                                    <td class="center"><?= $idx + 1 ?></td>
                                    <td><?= htmlspecialchars($th['nomor_peserta']) ?></td>
                                    <td><strong><?= htmlspecialchars($th['nama_lengkap']) ?></strong></td>
                                    <td class="center"><?= htmlspecialchars($th['jenjang']) ?></td>
                                    <td><?= htmlspecialchars($th['target_phone'] ?: '-') ?></td>
                                    <td class="center" style="color:#be123c;font-weight:700;">Belum Absen QR</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
