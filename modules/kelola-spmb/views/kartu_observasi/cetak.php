<?php
/**
 * View Cetak Kartu / ID Card Observasi SPMB dengan QR Code
 * Ukuran Standar KTP / CR-80: 85.6mm x 54mm (10 Kartu per Lembar A4)
 */
$logoUrl = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
} elseif (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $logoUrl = url(ltrim(SYS_APP_FAVICON, '/'));
}

function getKartuThemeByJenjang(string $jenjang): array {
    $j = strtoupper(trim($jenjang));
    if (str_contains($j, 'PAUD') || str_contains($j, 'TK') || str_contains($j, 'KB')) {
        return [
            'head_bg'     => 'linear-gradient(135deg, #facc15, #eab308)',
            'head_text'   => '#422006',
            'border'      => '#ca8a04',
            'badge_bg'    => '#fef9c3',
            'badge_text'  => '#854d0e',
            'badge_bdr'   => '#fde047',
        ];
    }
    if (str_contains($j, 'SMA') || str_contains($j, 'MA') || str_contains($j, 'SMK')) {
        // Abu-abu celana seragam SMA
        return [
            'head_bg'     => 'linear-gradient(135deg, #64748b, #94a3b8)',
            'head_text'   => '#ffffff',
            'border'      => '#64748b',
            'badge_bg'    => '#f1f5f9',
            'badge_text'  => '#334155',
            'badge_bdr'   => '#cbd5e1',
        ];
    }
    if (str_contains($j, 'SMP') || str_contains($j, 'MTS')) {
        // Biru Tua (Navy)
        return [
            'head_bg'     => 'linear-gradient(135deg, #0f172a, #1e3a8a)',
            'head_text'   => '#ffffff',
            'border'      => '#1e3a8a',
            'badge_bg'    => '#e0e7ff',
            'badge_text'  => '#1e3a8a',
            'badge_bdr'   => '#a5b4fc',
        ];
    }
    // Default / SD: Biru
    return [
        'head_bg'     => 'linear-gradient(135deg, #1d4ed8, #3b82f6)',
        'head_text'   => '#ffffff',
        'border'      => '#2563eb',
        'badge_bg'    => '#eff6ff',
        'badge_text'  => '#1d4ed8',
        'badge_bdr'   => '#bfdbfe',
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Observasi SPMB (Ukuran KTP)</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 16px;
        }
        .toolbar {
            max-width: 190mm;
            margin: 0 auto 14px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f172a;
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .grid {
            width: 180mm;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 85.6mm);
            justify-content: space-between;
            row-gap: 4.5mm;
            column-gap: 6mm;
        }
        /* Ukuran Standar KTP / CR-80: 85.6mm x 54mm */
        .card {
            width: 85.6mm;
            height: 54mm;
            background: #fff;
            border: 1.2pt solid #2563eb;
            border-radius: 3.2mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
            break-inside: avoid;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }
        .card-head {
            padding: 2.2mm 3mm;
            display: flex;
            align-items: center;
            gap: 2.2mm;
            flex-shrink: 0;
        }
        .card-head img {
            width: 7.5mm;
            height: 7.5mm;
            object-fit: contain;
            background: #fff;
            border-radius: 1.6mm;
            padding: 0.5mm;
            flex-shrink: 0;
        }
        .card-title {
            font-size: 8pt;
            font-weight: 800;
            letter-spacing: 0.2px;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .card-sub {
            font-size: 6.5pt;
            opacity: 0.9;
            line-height: 1.15;
        }
        .card-body {
            flex: 1;
            padding: 2.2mm 3mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2.5mm;
            overflow: hidden;
        }
        .info {
            flex: 1;
            min-width: 0;
            font-size: 7.3pt;
            line-height: 1.32;
        }
        .nama {
            font-size: 9pt;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 1mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .badge {
            display: inline-block;
            padding: 0.2mm 1.6mm;
            border-radius: 1.2mm;
            font-size: 6.8pt;
            font-weight: 800;
        }
        .jadwal-box {
            margin-top: 1.4mm;
            padding: 1.3mm 2mm;
            border-radius: 1.8mm;
            background: #f8fafc;
            border: 0.6pt solid #e2e8f0;
            font-size: 6.8pt;
            line-height: 1.32;
        }
        .jadwal-box div {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .qr-wrap {
            text-align: center;
            flex-shrink: 0;
        }
        .qr-code-box {
            width: 24mm;
            height: 24mm;
            padding: 1.2mm;
            background: #fff;
            border: 0.7pt solid #cbd5e1;
            border-radius: 2mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .qr-code-box img,
        .qr-code-box canvas {
            width: 100% !important;
            height: 100% !important;
        }
        .qr-label {
            font-size: 5.5pt;
            font-family: monospace;
            color: #475569;
            margin-top: 0.8mm;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .card { box-shadow: none; }
            .grid { width: 180mm; margin: 0 auto; }
            @page { size: A4 portrait; margin: 7mm 10mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div style="font-size:13px;font-weight:700;">Pratinjau Cetak Kartu Observasi — Ukuran KTP 85.6 × 54 mm (<?= count($items) ?> Kartu)</div>
        <div style="display:flex;gap:8px;align-items:center;">
            <button type="button" id="btnDownloadPdfAll" class="btn" style="background:#059669;color:#fff;" onclick="downloadAllCardsPdf()">Download PDF Semua Kartu</button>
            <button type="button" class="btn btn-print" onclick="window.print()">Cetak / Print PDF</button>
        </div>
    </div>

    <div id="kartuPdfContainer" class="grid">
        <?php foreach ($items as $it):
            $theme = getKartuThemeByJenjang((string)($it['jenjang'] ?? ''));
        ?>
            <div class="card" style="border-color: <?= $theme['border'] ?>;">
                <div class="card-head" style="background: <?= $theme['head_bg'] ?>; color: <?= $theme['head_text'] ?>;">
                    <?php if ($logoUrl): ?><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo"><?php endif; ?>
                    <div style="min-width:0;">
                        <div class="card-title">Kartu Peserta Observasi SPMB</div>
                        <div class="card-sub"><?= htmlspecialchars($activeGroup['nama_group'] ?? 'SIT Bina Insan Palu') ?></div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="info">
                        <div class="nama" title="<?= htmlspecialchars($it['nama_lengkap']) ?>"><?= htmlspecialchars($it['nama_lengkap']) ?></div>
                        <div>No. Peserta: <strong><?= htmlspecialchars($it['nomor_peserta']) ?></strong></div>
                        <div>
                            Jenjang:
                            <span class="badge" style="background: <?= $theme['badge_bg'] ?>; color: <?= $theme['badge_text'] ?>; border: 0.6pt solid <?= $theme['badge_bdr'] ?>;">
                                <?= htmlspecialchars($it['jenjang']) ?>
                            </span>
                        </div>
                        <div class="jadwal-box">
                            <div><strong>Ruang:</strong> <?= htmlspecialchars($it['nama_ruangan'] ?: 'Belum Diatur') ?></div>
                            <div><strong>Tgl:</strong> <?= !empty($it['tgl_sesi']) ? date('d/m/Y', strtotime($it['tgl_sesi'])) : '-' ?></div>
                            <div><strong>Sesi:</strong> <?= !empty($it['sesi_nama']) ? htmlspecialchars($it['sesi_nama'] . ' (' . $it['jam_mulai'] . '-' . $it['jam_selesai'] . ')') : '-' ?></div>
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
            width: 82,
            height: 82,
            correctLevel: QRCode.CorrectLevel.M
        });
    });

    function downloadAllCardsPdf() {
        const btn = document.getElementById('btnDownloadPdfAll');
        const el = document.getElementById('kartuPdfContainer');
        if (!el || typeof html2pdf === 'undefined') {
            window.print();
            return;
        }
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Membuat PDF...';
        }
        const opt = {
            margin:       [7, 10, 7, 10],
            filename:     'Kartu_Observasi_SPMB_<?= date('Ymd_His') ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak:    { mode: ['avoid-all', 'css'] }
        };
        html2pdf().set(opt).from(el).save().then(() => {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Download PDF Semua Kartu';
            }
        }).catch(() => {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Download PDF Semua Kartu';
            }
            window.print();
        });
    }

    <?php if (!empty($_GET['auto_download'])): ?>
    window.addEventListener('load', () => {
        setTimeout(() => downloadAllCardsPdf(), 600);
    });
    <?php endif; ?>
    </script>
</body>
</html>
