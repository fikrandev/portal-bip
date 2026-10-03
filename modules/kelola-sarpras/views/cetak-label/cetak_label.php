<?php
/**
 * Cetak Label Barcode & QR Code Inventaris Sarpras
 * Portal BIP - Yayasan Bina Insan Palu
 */

// Helper to generate QR Code as base64 in-memory (No internet dependency)
if (!function_exists('getQrCodeDataUri')) {
    function getQrCodeDataUri(string $text): string {
        static $qrCache = [];
        if (isset($qrCache[$text])) {
            return $qrCache[$text];
        }

        if (file_exists(BASE_PATH . '/core/phpqrcode.php')) {
            require_once BASE_PATH . '/core/phpqrcode.php';
            ob_start();
            @QRcode::png($text, false, QR_ECLEVEL_M, 4, 1);
            $pngData = ob_get_clean();
            if (!empty($pngData)) {
                $qrCache[$text] = 'data:image/png;base64,' . base64_encode($pngData);
                return $qrCache[$text];
            }
        }
        
        // Fallback to online API if needed
        return 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($text);
    }
}

$format = $_GET['format'] ?? 'barcode'; // 'barcode', 'qr', 'both'
$mode = $_GET['mode'] ?? 'per_unit';     // 'per_unit' or 'per_item'
$size = $_GET['size'] ?? 'standard';     // 'standard', 'compact', 'thermal'
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode &amp; QR Sarpras - <?= defined('SYS_APP_NAME') ? SYS_APP_NAME : 'Portal BIP' ?></title>
    
    <!-- Local JsBarcode with CDN fallback -->
    <script src="<?= asset('js/jsbarcode.all.min.js') ?>"></script>
    <script>
        if (typeof JsBarcode === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"><\/script>');
        }
    </script>

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #eff6ff;
            --primary-border: #3b82f6;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            padding-bottom: 40px;
        }

        /* Top Sticky Action Bar */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .toolbar-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toolbar-title h1 {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .toolbar-title span {
            font-size: 12px;
            color: #64748b;
        }

        .toolbar-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-group {
            display: inline-flex;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .btn-toggle {
            background: transparent;
            border: none;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-toggle.active {
            background: #ffffff;
            color: #1e3a8a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            border: 1px solid transparent;
        }

        .btn-print {
            background: #1e3a8a;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.25);
        }

        .btn-print:hover {
            background: #1e40af;
        }

        .btn-back {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #334155;
        }

        .btn-back:hover {
            background: #f8fafc;
        }

        /* Label Container Grid */
        .labels-wrapper {
            padding: 24px;
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            gap: 16px;
        }

        /* Layout Grid Types */
        .labels-wrapper.layout-standard {
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        }

        .labels-wrapper.layout-compact {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        .labels-wrapper.layout-thermal {
            grid-template-columns: 1fr;
            max-width: 320px;
        }

        /* Label Card Design */
        .label-card {
            background: #ffffff;
            border: 2px solid #2563eb;
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            page-break-inside: avoid;
            break-inside: avoid;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Header Instansi */
        .label-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .instansi-title {
            font-size: 11px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .instansi-sub {
            font-size: 8.5px;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 0.3px;
            margin-top: 1px;
        }

        .label-unit-badge {
            font-size: 8px;
            font-weight: 800;
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        /* Label Main Body */
        .label-body {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 8px;
        }

        .code-visual-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px;
        }

        .code-visual-box.qr-only {
            width: 75px;
            height: 75px;
        }

        .code-visual-box.qr-only img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .code-visual-box.barcode-only {
            width: 100%;
            padding: 6px 4px 2px 4px;
            margin-bottom: 6px;
        }

        .code-visual-box.barcode-only svg {
            width: 100%;
            max-height: 48px;
        }

        /* Info Table */
        .label-info-table {
            flex: 1;
            font-size: 9.5px;
            line-height: 1.35;
            color: #1e293b;
        }

        .label-info-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .label-info-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .label-info-table td.lbl {
            color: #64748b;
            font-weight: 600;
            width: 70px;
            white-space: nowrap;
        }

        .label-info-table td.col {
            width: 8px;
            color: #64748b;
            font-weight: 600;
        }

        .label-info-table td.val {
            font-weight: 700;
            word-break: break-word;
        }

        /* Barcode Row (when format = barcode or both) */
        .barcode-full-row {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed #cbd5e1;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .barcode-full-row svg {
            max-width: 100%;
            height: auto;
        }

        /* Monospace Asset Code Box */
        .label-footer-code {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            text-align: center;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: 11px;
            font-weight: 800;
            color: #1d4ed8;
            padding: 4px;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* Print Specific Rules */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .toolbar {
                display: none !important;
            }

            .labels-wrapper {
                padding: 0 !important;
                max-width: none !important;
                gap: 8px !important;
            }

            .label-card {
                box-shadow: none !important;
                border-color: #000000 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .label-header {
                border-color: #000000 !important;
            }

            .instansi-sub {
                color: #000000 !important;
            }

            .label-unit-badge {
                border: 1px solid #000 !important;
                color: #000 !important;
                background: none !important;
            }

            .label-footer-code {
                background: none !important;
                border-color: #000000 !important;
                color: #000000 !important;
            }

            .barcode-full-row {
                border-color: #000000 !important;
            }
        }
    </style>
</head>
<body>

<!-- Sticky Toolbar -->
<div class="toolbar">
    <div class="toolbar-title">
        <span style="font-size: 20px;">🖨️</span>
        <div>
            <h1>Cetak Label Inventaris Sarpras</h1>
            <span><?= count($items) ?> Master Aset Terpilih</span>
        </div>
    </div>

    <div class="toolbar-controls">
        <!-- Live Format Toggle -->
        <div class="btn-group">
            <button type="button" class="btn-toggle <?= $format === 'barcode' ? 'active' : '' ?>" onclick="switchFormat('barcode')">
                🏷️ Barcode 1D
            </button>
            <button type="button" class="btn-toggle <?= $format === 'qr' ? 'active' : '' ?>" onclick="switchFormat('qr')">
                📱 QR Code
            </button>
            <button type="button" class="btn-toggle <?= $format === 'both' ? 'active' : '' ?>" onclick="switchFormat('both')">
                📄 Kombinasi (Barcode+QR)
            </button>
        </div>

        <!-- Live Layout Toggle -->
        <div class="btn-group">
            <button type="button" class="btn-toggle <?= $size === 'standard' ? 'active' : '' ?>" onclick="switchLayout('standard')">
                Standar (2 Kolom)
            </button>
            <button type="button" class="btn-toggle <?= $size === 'compact' ? 'active' : '' ?>" onclick="switchLayout('compact')">
                Ramping (3 Kolom)
            </button>
            <button type="button" class="btn-toggle <?= $size === 'thermal' ? 'active' : '' ?>" onclick="switchLayout('thermal')">
                Thermal (1 Kolom)
            </button>
        </div>

        <!-- Action Buttons -->
        <button type="button" onclick="window.print()" class="btn-action btn-print">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0v2.796c0 1.18.91 2.164 2.09 2.201a51.964 51.964 0 0 0 6.32 0c1.18-.037 2.09-1.022 2.09-2.201V9.456Z" />
            </svg>
            <span>Cetak Sekarang (Ctrl+P)</span>
        </button>

        <a href="<?= url('kelola-sarpras/cetak-label') ?>" class="btn-action btn-back">
            &larr; Kembali
        </a>
    </div>
</div>

<!-- Main Labels Container -->
<div class="labels-wrapper layout-<?= e($size) ?>" id="labelsContainer">
    <?php foreach ($items as $item): ?>
        <?php 
            $qty = ($mode === 'per_item') ? 1 : max(1, (int)$item['jumlah']);
            for ($i = 0; $i < $qty; $i++): 
                $uniqueCode = ($mode === 'per_unit' && (int)$item['jumlah'] > 1) 
                    ? $item['kode_barang'] . '-' . sprintf('%03d', $i + 1)
                    : $item['kode_barang'];

                $qrPayload = url('kelola-sarpras/barang?search=' . urlencode($item['kode_barang']));
                $qrDataUri = getQrCodeDataUri($qrPayload);
        ?>
        <div class="label-card">
            <!-- Header Instansi -->
            <div class="label-header">
                <div>
                    <div class="instansi-title"><?= defined('SYS_APP_NAME') ? SYS_APP_NAME : 'YAYASAN BINA INSAN PALU' ?></div>
                    <div class="instansi-sub">LABEL INVENTARIS SARANA &amp; PRASARANA</div>
                </div>
                <?php if (!empty($item['unit'])): ?>
                    <span class="label-unit-badge"><?= e($item['unit']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Body Details -->
            <div class="label-body">
                <!-- QR Code Box (visible in 'qr' and 'both' modes) -->
                <div class="code-visual-box qr-only qr-elem" style="<?= ($format === 'barcode') ? 'display: none;' : '' ?>">
                    <img src="<?= $qrDataUri ?>" alt="QR Code">
                </div>

                <!-- Text Info -->
                <div class="label-info-table">
                    <table>
                        <tr>
                            <td class="lbl">Nama Aset</td>
                            <td class="col">:</td>
                            <td class="val"><?= e($item['nama_barang']) ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Merk/Model</td>
                            <td class="col">:</td>
                            <td class="val"><?= e($item['merk'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="lbl">Ruangan</td>
                            <td class="col">:</td>
                            <td class="val"><?= e($item['nama_ruangan']) ?></td>
                        </tr>
                        <?php if (!empty($item['nomor_seri'])): ?>
                        <tr>
                            <td class="lbl">No. Seri</td>
                            <td class="col">:</td>
                            <td class="val"><?= e($item['nomor_seri']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="lbl">Kondisi</td>
                            <td class="col">:</td>
                            <td class="val"><?= e($item['kondisi'] ?? 'Baik') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Barcode SVG Row (visible in 'barcode' and 'both' modes) -->
            <div class="barcode-full-row barcode-elem" style="<?= ($format === 'qr') ? 'display: none;' : '' ?>">
                <svg class="barcode-target"
                     jsbarcode-format="CODE128"
                     jsbarcode-value="<?= htmlspecialchars($uniqueCode) ?>"
                     jsbarcode-text="<?= htmlspecialchars($uniqueCode) ?>"
                     jsbarcode-width="1.45"
                     jsbarcode-height="38"
                     jsbarcode-fontsize="10"
                     jsbarcode-fontoptions="bold"
                     jsbarcode-margin="0">
                </svg>
            </div>

            <!-- Human Readable Footer Code (visible in QR-only mode) -->
            <div class="label-footer-code qr-footer-elem" style="<?= ($format !== 'qr') ? 'display: none;' : '' ?>">
                <?= e($uniqueCode) ?>
            </div>
        </div>
        <?php endfor; ?>
    <?php endforeach; ?>
</div>

<script>
    // Initialize Barcodes
    function initBarcodes() {
        if (typeof JsBarcode !== 'undefined') {
            try {
                JsBarcode(".barcode-target").init();
            } catch (err) {
                console.error("Error rendering barcodes:", err);
            }
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        initBarcodes();
    });

    // Switch Format dynamically
    function switchFormat(fmt) {
        document.querySelectorAll('.btn-group:first-child .btn-toggle').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        const qrElements = document.querySelectorAll('.qr-elem');
        const barcodeElements = document.querySelectorAll('.barcode-elem');
        const qrFooterElements = document.querySelectorAll('.qr-footer-elem');

        if (fmt === 'barcode') {
            qrElements.forEach(el => el.style.display = 'none');
            barcodeElements.forEach(el => el.style.display = 'flex');
            qrFooterElements.forEach(el => el.style.display = 'none');
        } else if (fmt === 'qr') {
            qrElements.forEach(el => el.style.display = 'flex');
            barcodeElements.forEach(el => el.style.display = 'none');
            qrFooterElements.forEach(el => el.style.display = 'block');
        } else if (fmt === 'both') {
            qrElements.forEach(el => el.style.display = 'flex');
            barcodeElements.forEach(el => el.style.display = 'flex');
            qrFooterElements.forEach(el => el.style.display = 'none');
        }
        initBarcodes();
    }

    // Switch Layout dynamically
    function switchLayout(layout) {
        document.querySelectorAll('.btn-group:nth-child(2) .btn-toggle').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        const container = document.getElementById('labelsContainer');
        container.classList.remove('layout-standard', 'layout-compact', 'layout-thermal');
        container.classList.add('layout-' + layout);
        initBarcodes();
    }
</script>

</body>
</html>
