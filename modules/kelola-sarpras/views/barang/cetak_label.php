<?php
/**
 * Cetak Label / Stiker Inventaris Aset Sarpras
 * Portal BIP
 */
$qrText = url('kelola-sarpras/barang?search=' . urlencode($barang['kode_barang']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Label Aset - <?= e($barang['kode_barang']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 30px;
            display: flex;
            justify-content: center;
        }
        .label-card {
            width: 400px;
            border: 2px solid #2563eb;
            border-radius: 12px;
            background: #ffffff;
            padding: 14px 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            box-sizing: border-box;
            position: relative;
        }
        .header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .logo-text h2 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .logo-text p {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #2563eb;
            font-weight: bold;
        }
        .body-content {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .qr-box {
            width: 80px;
            height: 80px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #f8fafc;
            padding: 4px;
        }
        .qr-box img {
            max-width: 100%;
            max-height: 100%;
        }
        .info-table {
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
        }
        .info-table table {
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2.5px 0;
            vertical-align: top;
        }
        .info-table td.label {
            color: #64748b;
            width: 85px;
            font-weight: 600;
        }
        .info-table td.colon {
            width: 10px;
            font-weight: 600;
            color: #64748b;
        }
        .info-table td.val {
            font-weight: 700;
            word-break: break-word;
        }
        .kode-box {
            margin-top: 10px;
            padding: 6px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            text-align: center;
            font-family: monospace;
            font-size: 12px;
            font-weight: 800;
            color: #1d4ed8;
            letter-spacing: 0.5px;
        }
        .actions {
            margin-top: 20px;
            text-align: center;
        }
        .btn-print {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            body { background: transparent; padding: 0; }
            .actions { display: none; }
            .label-card { box-shadow: none; border-color: #000; }
            .header { border-color: #000; }
            .kode-box { background: none; border-color: #000; color: #000; }
        }
    </style>
</head>
<body>

<div>
    <div class="label-card">
        <div class="header">
            <div class="logo-text">
                <h2><?= SYS_APP_NAME ?></h2>
                <p>LABEL INVENTARIS ASET &amp; SARPRAS</p>
            </div>
        </div>

        <div class="body-content">
            <div class="qr-box">
                <!-- QR Code points to the asset search URL -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($qrText) ?>" alt="QR Code">
            </div>

            <div class="info-table">
                <table>
                    <tr>
                        <td class="label">Nama Aset</td>
                        <td class="colon">:</td>
                        <td class="val"><?= e($barang['nama_barang']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Kategori</td>
                        <td class="colon">:</td>
                        <td class="val"><?= e($barang['nama_kategori'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Ruangan</td>
                        <td class="colon">:</td>
                        <td class="val"><?= e($barang['nama_ruangan'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Unit / Tahun</td>
                        <td class="colon">:</td>
                        <td class="val"><?= e($barang['unit']) ?> / <?= e($barang['tahun_pengadaan'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="kode-box">
            <?= e($barang['kode_barang']) ?>
        </div>
    </div>

    <div class="actions">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Label Sekarang</button>
    </div>
</div>

</body>
</html>
