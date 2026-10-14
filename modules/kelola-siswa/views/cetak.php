<?php
/**
 * Cetak Biodata Siswa - Portal BIP
 * Official Printable Student Profile (Standar Dapodik Resmi Kemdikbud)
 * Yayasan Bina Insan Palu (Portal BIP)
 */
require_once BASE_PATH . '/modules/kelola-spmb/views/partials/formulir_peserta_didik_dapodik.php';

$namaSiswa = $siswa['nama_lengkap'] ?: ($siswa['nama'] ?? 'Siswa');
$jenjang = strtoupper($siswa['jenjang'] ?? 'SD');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir_Biodata_Dapodik_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $namaSiswa) ?></title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e2e8f0;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
        }
        .toolbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .toolbar-icon {
            font-size: 20px;
        }
        .toolbar-title {
            font-size: 13px;
            font-weight: 700;
            color: #f8fafc;
        }
        .toolbar-sub {
            font-size: 11px;
            color: #94a3b8;
            margin-left: 8px;
        }
        .toolbar-actions {
            display: flex;
            gap: 8px;
        }
        .btn-print {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover {
            background: #1e40af;
        }
        .btn-close {
            background: #334155;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-close:hover {
            background: #475569;
        }
        .paper-container {
            max-width: 215mm;
            margin: 20px auto 40px auto;
            padding: 0;
            background: transparent;
            box-shadow: none;
        }
        @media print {
            @page {
                size: 215mm 330mm; /* Kertas F4 / Folio Resmi (21.5cm x 33cm) */
                margin: 10mm;      /* Margin 1cm (kiri, kanan, bawah, atas) */
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .paper-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- ── Print Toolbar ── -->
    <div class="no-print print-toolbar">
        <div class="toolbar-brand">
            <span class="toolbar-icon">📄</span>
            <div>
                <span class="toolbar-title">Formulir Peserta Didik (Standar Dapodik Kemdikbud)</span>
                <span class="toolbar-sub"><?= e($jenjang) ?> &bull; <?= e($namaSiswa) ?> (NIS: <?= e($siswa['nis'] ?: ($siswa['nisn'] ?: '-')) ?>)</span>
            </div>
        </div>
        <div class="toolbar-actions">
            <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
            <button class="btn-close" onclick="window.close()">✕ Tutup</button>
        </div>
    </div>

    <!-- ── Paper Container ── -->
    <div class="paper-container">
        <?php renderDapodikFormulirPesertaDidik($siswa); ?>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('autoprint') === '1') {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 400);
            });
        }
    </script>
</body>
</html>
