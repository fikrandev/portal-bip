<?php
/**
 * Cetak Laporan Rekap / Data Harian Mutaba'ah Ibadah Harian Guru
 */
$appName = defined('SYS_APP_NAME') ? SYS_APP_NAME : 'Portal BIP';
$mode = $mode ?? 'rekap';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mutaba'ah Ibadah Guru - <?= e($appName) ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #1e293b; margin: 20px; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 4px 0 0; font-size: 11px; color: #475569; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; text-align: center; font-size: 10px; text-transform: uppercase; }
        .center { text-align: center; }
        .badge-ok { color: #047857; font-weight: bold; }
        .badge-no { color: #94a3b8; }
        .actions { margin-bottom: 14px; display: flex; gap: 8px; }
        .btn { padding: 7px 14px; border-radius: 8px; border: none; background: #059669; color: #fff; font-weight: bold; cursor: pointer; font-size: 12px; text-decoration: none; }
        .btn-back { background: #e2e8f0; color: #334155; }
        @media print { .actions { display: none; } body { margin: 8mm; } }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="btn" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <a href="<?= url('kelola-ibadah-guru?tab=' . ($mode === 'harian' ? 'harian' : 'rekap')) ?>" class="btn btn-back">← Kembali</a>
    </div>

    <div class="header">
        <h1>Laporan <?= $mode === 'harian' ? 'Monitoring Harian' : 'Rekapitulasi Periode' ?> Mutaba'ah Ibadah Guru</h1>
        <p><?= e($appName) ?> &bull; Sinkronisasi Portal Guru & Portal Web</p>
    </div>

    <div class="meta">
        <div>
            <?php if ($mode === 'harian'): ?>
                Tanggal Monitoring: <strong><?= date('d F Y', strtotime($tanggal)) ?></strong>
            <?php else: ?>
                Periode Rekap: <strong><?= date('d M Y', strtotime($startDate)) ?> s/d <?= date('d M Y', strtotime($endDate)) ?></strong> (<?= (int)$totalDaysInRange ?> Hari)
            <?php endif; ?>
        </div>
        <div>Dicetak pada: <strong><?= date('d/m/Y H:i') ?> WITA</strong></div>
    </div>

    <?php if ($mode === 'harian'): ?>
    <table>
        <thead>
            <tr>
                <th style="width:32px;">No</th>
                <th>Nama Guru</th>
                <th style="width:40px;">L/P</th>
                <th>Unit</th>
                <th>Subuh</th>
                <th>Dzuhur</th>
                <th>Ashar</th>
                <th>Maghrib</th>
                <th>Isya</th>
                <th>Tilawah</th>
                <th>Dzikir</th>
                <th>Tadabbur</th>
                <th>Skor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dailyRows as $i => $dr):
                $st = $dr['state'];
            ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><?= e($dr['nama']) ?></td>
                <td class="center"><?= e($dr['gender']) ?></td>
                <td><?= e($dr['unit_tugas']) ?></td>
                <?php foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w):
                    $si = $st['sholat'][$w];
                ?>
                <td class="center">
                    <?php if ($si['checked']): ?>
                        <span class="badge-ok">✓ <?= $si['loc'] === 'masjid' ? 'Masjid' : 'Rumah' ?></span>
                    <?php else: ?>
                        <span class="badge-no">-</span>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
                <td><?= $st['tilawah_checked'] ? ('✓ ' . e($st['tilawah_surah'] ?: 'Ya')) : '-' ?></td>
                <td class="center"><?= ($st['dzikir_istighfar'] && $st['dzikir_sholawat']) ? '2/2 ✓' : (($st['dzikir_istighfar'] || $st['dzikir_sholawat']) ? '1/2' : '-') ?></td>
                <td><?= $st['tadabbur_checked'] ? ('✓ ' . e($st['tadabbur_notes'] ?: 'Ya')) : '-' ?></td>
                <td class="center"><strong><?= (int)$dr['komponen_done'] ?>/4</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th style="width:32px;">No</th>
                <th>Nama Guru</th>
                <th>NIY</th>
                <th style="width:40px;">L/P</th>
                <th>Unit</th>
                <th>Hari Mengisi</th>
                <th>Total Sholat</th>
                <th>Sholat 5/5 (Hari)</th>
                <th>Rumah / Masjid</th>
                <th>Tilawah</th>
                <th>Dzikir</th>
                <th>Tadabbur</th>
                <th>Keaktifan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rekapRows as $i => $rr): ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><?= e($rr['nama']) ?></td>
                <td class="center"><?= e($rr['niy']) ?></td>
                <td class="center"><?= e($rr['gender']) ?></td>
                <td><?= e($rr['unit_tugas']) ?></td>
                <td class="center"><?= $rr['hari_mengisi'] ?> / <?= $rr['total_hari'] ?></td>
                <td class="center"><strong><?= $rr['total_sholat'] ?></strong> (<?= $rr['persen_sholat'] ?>%)</td>
                <td class="center"><?= $rr['hari_sholat_lengkap'] ?> hr</td>
                <td class="center">Rumah: <?= $rr['sholat_rumah'] ?> | Masjid: <?= $rr['sholat_masjid'] ?></td>
                <td class="center"><?= $rr['hari_tilawah'] ?> hr</td>
                <td class="center"><?= $rr['hari_dzikir'] ?> hr</td>
                <td class="center"><?= $rr['hari_tadabbur'] ?> hr</td>
                <td class="center"><strong><?= $rr['persen_keaktifan'] ?>%</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
