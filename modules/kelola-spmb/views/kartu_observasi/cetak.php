<?php
/**
 * View Cetak Kartu / ID Card Observasi SPMB dengan QR Code (A4 2x4 Grid)
 */
$logoUrl = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
} elseif (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $logoUrl = url(ltrim(SYS_APP_FAVICON, '/'));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Observasi SPMB</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; color: #0f172a; padding: 18px; }
        .toolbar { max-width: 210mm; margin: 0 auto 14px auto; display: flex; justify-content: space-between; align-items: center; background: #0f172a; color: #fff; padding: 10px 16px; border-radius: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .btn-print { background: #2563eb; color: #fff; }
        .grid { max-width: 210mm; margin: 0 auto; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .card { background: #fff; border: 1.5px solid #1e3a8a; border-radius: 12px; overflow: hidden; page-break-inside: avoid; break-inside: avoid; }
        .card-head { background: linear-gradient(135deg, #1e3a8a, #2563eb); color: #fff; padding: 8px 12px; display: flex; align-items: center; gap: 8px; }
        .card-head img { width: 28px; height: 28px; object-fit: contain; background: #fff; border-radius: 6px; padding: 2px; }
        .card-title { font-size: 11px; font-weight: 800; letter-spacing: .3px; text-transform: uppercase; }
        .card-sub { font-size: 9px; opacity: .85; }
        .card-body { padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .info { flex: 1; min-width: 0; font-size: 10.5px; line-height: 1.45; }
        .nama { font-size: 13px; font-weight: 800; color: #0f172a; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 5px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 9.5px; font-weight: 700; }
        .jadwal-box { margin-top: 6px; padding: 5px 8px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; font-size: 9.5px; }
        .qr-wrap { text-align: center; shrink: 0; }
        .qr-code-box { width: 84px; height: 84px; padding: 4px; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .qr-label { font-size: 8px; font-family: monospace; color: #475569; margin-top: 3px; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div style="font-size:13px;font-weight:700;">Pratinjau Cetak Kartu Observasi (<?= count($items) ?> Kartu)</div>
        <button class="btn btn-print" onclick="window.print()">Cetak Kartu / PDF</button>
    </div>

    <div class="grid">
        <?php foreach ($items as $it): ?>
            <div class="card">
                <div class="card-head">
                    <?php if ($logoUrl): ?><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo"><?php endif; ?>
                    <div>
                        <div class="card-title">Kartu Peserta Observasi SPMB</div>
                        <div class="card-sub"><?= htmlspecialchars($activeGroup['nama_group'] ?? 'SIT Bina Insan Palu') ?></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="info">
                        <div class="nama"><?= htmlspecialchars($it['nama_lengkap']) ?></div>
                        <div>No. Peserta: <strong><?= htmlspecialchars($it['nomor_peserta']) ?></strong></div>
                        <div>Jenjang: <span class="badge"><?= htmlspecialchars($it['jenjang']) ?></span></div>
                        <div class="jadwal-box">
                            <div><strong>Ruangan:</strong> <?= htmlspecialchars($it['nama_ruangan'] ?: 'Belum Diatur') ?></div>
                            <div><strong>Tanggal:</strong> <?= !empty($it['tgl_sesi']) ? date('d/m/Y', strtotime($it['tgl_sesi'])) : '-' ?></div>
                            <div><strong>Jam Sesi:</strong> <?= !empty($it['sesi_nama']) ? htmlspecialchars($it['sesi_nama'] . ' (' . $it['jam_mulai'] . '-' . $it['jam_selesai'] . ')') : '-' ?></div>
                        </div>
                    </div>
                    <div class="qr-wrap">
                        <div class="qr-code-box" data-qr="<?= htmlspecialchars($it['qr_token']) ?>"></div>
                        <div class="qr-label"><?= htmlspecialchars($it['qr_token']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
    document.querySelectorAll('.qr-code-box').forEach(el => {
        new QRCode(el, {
            text: el.getAttribute('data-qr') || '',
            width: 74,
            height: 74,
            correctLevel: QRCode.CorrectLevel.M
        });
    });
    </script>
</body>
</html>
