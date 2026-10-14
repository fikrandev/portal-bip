<?php
/**
 * View Publik: Penandatanganan Surat Pernyataan Komitmen Orang Tua/Wali Murid
 * Modul SPMB - SIT Bina Insan Palu
 * 
 * @var array  $pendaftar
 * @var array  $template
 * @var string $kopUrl
 * @var string $token
 * @var bool   $sudahTtd
 * @var string $statusTtd
 * @var array  $decryptedAyah
 * @var array  $decryptedIbu
 * @var string $renderedContent
 * @var string $pageTitle
 */

$namaAnak  = htmlspecialchars($pendaftar['nama_lengkap'] ?? '');
$namaAyah  = htmlspecialchars($pendaftar['nama_ayah'] ?? '');
$namaIbu   = htmlspecialchars($pendaftar['nama_ibu'] ?? '');
$nomorDaft = htmlspecialchars($pendaftar['nomor_pendaftaran'] ?? '');
$jenjang   = htmlspecialchars($pendaftar['jenjang'] ?? 'SD');
$sekolah   = htmlspecialchars($template['nama_sekolah'] ?? ('SIT Bina Insan Palu (' . $jenjang . ')'));
$cetakUrl  = url('spmb/komitmen/' . $token . '/cetak');
$biodataUrl = url('spmb/biodata/' . $token);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Surat Pernyataan Komitmen Orang Tua') ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-deeper: #0f172a;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-300: #cbd5e1;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --emerald-600: #059669;
            --rose-600: #e11d48;
            --amber-500: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: #f1f5f9;
            color: var(--slate-800);
            min-height: 100vh;
            line-height: 1.6;
            padding-bottom: 60px;
        }

        /* ── Top Navigation Bar ─────────────────────────────────── */
        .top-navbar {
            background: linear-gradient(135deg, #091e42 0%, #0f2b6a 50%, #0284c7 100%);
            color: #ffffff;
            padding: 16px 20px;
            box-shadow: 0 4px 20px rgba(2, 132, 199, 0.25);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top-navbar-inner {
            max-width: 960px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .nav-brand-text h1 {
            font-size: 15px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
        }

        .nav-brand-text p {
            font-size: 11px;
            color: #bae6fd;
            font-weight: 500;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .badge-reg {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* ── Container Layout ──────────────────────────────────── */
        .container {
            max-width: 960px;
            margin: 24px auto 0;
            padding: 0 16px;
        }

        /* ── Status Banners ────────────────────────────────────── */
        .alert-banner {
            border-radius: 16px;
            padding: 18px 22px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1.5px solid #6ee7b7;
            color: #065f46;
        }

        .alert-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1.5px solid #fcd34d;
            color: #92400e;
        }

        .alert-banner .icon {
            font-size: 26px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert-success .icon { color: #059669; }
        .alert-warning .icon { color: #d97706; }

        .alert-banner h3 {
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 3px;
        }

        .alert-banner p {
            font-size: 13px;
            line-height: 1.5;
            opacity: 0.95;
        }

        /* ── Word A4 Paper Preview ─────────────────────────────── */
        .paper-wrapper {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid var(--slate-200);
            margin-bottom: 32px;
        }

        .paper-kop-container {
            width: 100%;
            background: #ffffff;
            border-bottom: 2px solid var(--slate-900);
            text-align: center;
        }

        .paper-kop-banner {
            width: 100%;
            display: block;
            max-height: 180px;
            object-fit: cover;
        }

        .paper-kop-fallback {
            padding: 24px 32px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 3px double var(--slate-800);
        }

        .kop-logo-box {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .kop-logo-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
        }

        .kop-text h2 {
            font-size: 16px;
            font-weight: 900;
            color: var(--slate-900);
            letter-spacing: -0.3px;
        }

        .kop-text p {
            font-size: 11px;
            color: var(--slate-500);
        }

        .kop-npsn-box {
            text-align: right;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            color: var(--slate-600);
            line-height: 1.4;
        }

        .paper-body {
            padding: 40px 48px;
            color: #1e293b;
        }

        @media (max-width: 640px) {
            .paper-body { padding: 24px 18px; }
            .paper-kop-fallback { padding: 18px 16px; flex-direction: column; text-align: center; }
            .kop-npsn-box { text-align: center; }
        }

        .document-title-block {
            text-align: center;
            margin-bottom: 24px;
        }

        .document-title-block h2 {
            font-size: 17px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }

        .document-title-block h3 {
            font-size: 15px;
            font-weight: 800;
            color: #0284c7;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .bismillah-text {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--slate-600);
            margin: 18px 0 22px;
            text-transform: uppercase;
        }

        .rendered-content {
            font-size: 13.5px;
            line-height: 1.8;
            color: #1e293b;
        }

        .rendered-content p {
            margin-bottom: 14px;
        }

        .rendered-content ol, .rendered-content ul {
            padding-left: 24px;
            margin-bottom: 16px;
        }

        .rendered-content li {
            margin-bottom: 8px;
        }

        .rendered-content strong {
            font-weight: 700;
            color: #0f172a;
        }

        .rendered-content .auto-value {
            font-weight: 700;
            color: #0369a1;
            background: #f0f9ff;
            padding: 1px 6px;
            border-radius: 4px;
            border-bottom: 1.5px solid #0284c7;
        }

        /* ── Displaying Decrypted Signatures in Document ──────── */
        .signatures-display-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px dashed var(--slate-300);
            text-align: center;
        }

        .sig-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
        }

        .sig-col-role {
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 8px;
        }

        .sig-image-box {
            width: 220px;
            height: 110px;
            border: 1px dashed #94a3b8;
            border-radius: 10px;
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .sig-image-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .sig-badge-secured {
            position: absolute;
            bottom: 4px;
            right: 4px;
            font-size: 9px;
            font-weight: 800;
            background: rgba(5, 150, 105, 0.9);
            color: #ffffff;
            padding: 1px 6px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .sig-name {
            font-size: 14px;
            font-weight: 800;
            color: var(--slate-900);
            border-bottom: 1px solid var(--slate-800);
            padding-bottom: 2px;
            display: inline-block;
            min-width: 160px;
        }

        /* ── Signature Submission Form ─────────────────────────── */
        .signing-form-card {
            background: #ffffff;
            border-radius: 20px;
            border: 2px solid #bae6fd;
            padding: 32px 28px;
            box-shadow: 0 12px 30px -10px rgba(2, 132, 199, 0.15);
        }

        .form-heading {
            margin-bottom: 24px;
            text-align: center;
        }

        .form-heading h3 {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .form-heading p {
            font-size: 13px;
            color: var(--slate-500);
            margin-top: 4px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .pads-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 768px) {
            .pads-grid { grid-template-columns: 1fr; }
            .signatures-display-grid { grid-template-columns: 1fr; gap: 32px; }
        }

        .pad-panel {
            background: var(--slate-50);
            border: 1.5px solid var(--slate-200);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: all 0.2s;
        }

        .pad-panel:focus-within, .pad-panel.active {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.12);
        }

        .pad-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .pad-role-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--slate-900);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pad-status-tag {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            background: #fee2e2;
            color: #b91c1c;
        }

        .pad-status-tag.signed {
            background: #d1fae5;
            color: #065f46;
        }

        .canvas-container {
            position: relative;
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            height: 180px;
            touch-action: none;
            cursor: crosshair;
            overflow: hidden;
        }

        .canvas-container.has-drawn {
            border-style: solid;
            border-color: #0284c7;
        }

        .canvas-container canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .canvas-placeholder-hint {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #94a3b8;
            pointer-events: none;
            transition: opacity 0.2s;
        }

        .canvas-placeholder-hint i { font-size: 24px; }
        .canvas-placeholder-hint span { font-size: 12px; font-weight: 600; }

        .pad-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .btn-clear {
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 8px;
            background: #f1f5f9;
            color: var(--slate-600);
            border: 1px solid var(--slate-200);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.15s;
        }

        .btn-clear:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }

        .form-group-name {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-group-name label {
            font-size: 11px;
            font-weight: 700;
            color: var(--slate-600);
            text-transform: uppercase;
        }

        .form-group-name input {
            padding: 8px 12px;
            font-size: 13px;
            border-radius: 8px;
            border: 1px solid var(--slate-300);
            outline: none;
            font-weight: 600;
            color: var(--slate-900);
            transition: border-color 0.2s;
        }

        .form-group-name input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        /* ── Security / Encryption Notice ──────────────────────── */
        .security-badge-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
        }

        .security-badge-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #0f172a;
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .security-badge-text h4 {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .security-badge-text p {
            font-size: 11px;
            color: var(--slate-500);
            line-height: 1.4;
        }

        .agreement-box {
            background: #f8fafc;
            border: 1px solid var(--slate-200);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .agreement-box input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: #0284c7;
            cursor: pointer;
            flex-shrink: 0;
        }

        .agreement-box label {
            font-size: 12px;
            color: var(--slate-700);
            cursor: pointer;
            line-height: 1.5;
            font-weight: 600;
        }

        .submit-btn-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .btn-submit-ttd {
            padding: 14px 36px;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.3);
            transition: all 0.2s;
            width: 100%;
            max-width: 440px;
            justify-content: center;
        }

        .btn-submit-ttd:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(2, 132, 199, 0.4);
            background: linear-gradient(135deg, #0369a1 0%, #1d4ed8 100%);
        }

        .btn-submit-ttd:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ── Action Buttons for Already Signed ─────────────────── */
        .signed-actions-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .btn-action-primary {
            padding: 12px 24px;
            border-radius: 12px;
            background: #0284c7;
            color: #ffffff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
            transition: all 0.2s;
        }

        .btn-action-primary:hover {
            background: #0369a1;
            transform: translateY(-1px);
        }

        .btn-action-secondary {
            padding: 12px 20px;
            border-radius: 12px;
            background: #ffffff;
            color: var(--slate-700);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid var(--slate-300);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-action-secondary:hover {
            background: var(--slate-50);
            border-color: var(--slate-400);
        }
    </style>
</head>
<body>

    <!-- ── Top Navbar ────────────────────────────────────────────── -->
    <nav class="top-navbar">
        <div class="top-navbar-inner">
            <div class="nav-brand">
                <div class="nav-brand-logo">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div class="nav-brand-text">
                    <h1>SIT BINA INSAN PALU</h1>
                    <p>Surat Pernyataan Komitmen Orang Tua &bull; <?= $jenjang ?></p>
                </div>
            </div>
            <div class="nav-actions">
                <div class="badge-reg">
                    <i class="fa-solid fa-id-card"></i>
                    <span><?= $nomorDaft ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">

        <!-- ── Status Banners ────────────────────────────────────── -->
        <?php if ($sudahTtd): ?>
            <div class="alert-banner alert-success">
                <i class="fa-solid fa-circle-check icon"></i>
                <div style="flex: 1;">
                    <h3>Alhamdulillah, Surat Komitmen Ini Telah Ditandatangani & Sah</h3>
                    <p>
                        Tanda tangan digital Ayah & Ibu ananda <strong><?= $namaAnak ?></strong> telah terekam secara terenkripsi 
                        pada tanggal <strong><?= !empty($pendaftar['ttd_submitted_at']) ? date('d F Y, H:i', strtotime($pendaftar['ttd_submitted_at'])) . ' WITA' : '-' ?></strong>.
                        Dokumen ini berlaku resmi sebagai syarat penerimaan SPMB SIT Bina Insan Palu.
                    </p>
                    <div class="signed-actions-bar" style="justify-content: flex-start; margin-top: 14px;">
                        <a href="<?= $cetakUrl ?>" target="_blank" class="btn-action-primary">
                            <i class="fa-solid fa-print"></i>
                            <span>Cetak / Simpan PDF Dokumen</span>
                        </a>
                        <a href="<?= $biodataUrl ?>" class="btn-action-secondary">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Kembali ke Halaman Biodata</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert-banner alert-warning">
                <i class="fa-solid fa-triangle-exclamation icon"></i>
                <div>
                    <h3>Wajib Ditandatangani Secara Digital (Langkah Terakhir Pendaftaran)</h3>
                    <p>
                        Bapak/Ibu wali dari ananda <strong><?= $namaAnak ?></strong>, mohon membaca seluruh isi komitmen di bawah ini dengan seksama. 
                        Setelah membaca, silakan bubuhkan tanda tangan digital Ayah dan Ibu pada area yang telah disediakan di bawah lalu tekan tombol submit.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Word A4 Paper View ────────────────────────────────── -->
        <article class="paper-wrapper">
            <!-- Kop Surat -->
            <div class="paper-kop-container">
                <?php if (!empty($kopUrl)): ?>
                    <img src="<?= $kopUrl ?>" alt="Kop Surat <?= $jenjang ?>" class="paper-kop-banner">
                <?php else: ?>
                    <div class="paper-kop-fallback">
                        <div class="kop-logo-box">
                            <div class="kop-logo-icon">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <div class="kop-text">
                                <h2><?= htmlspecialchars($template['nama_sekolah'] ?? 'SD ISLAM TERPADU BINA INSAN PALU') ?></h2>
                                <p>Jl. Agatis No. 1 Kel. Nunu Kec. Tatanga, Palu 94222, Sulawesi Tengah</p>
                                <p>Email: sditbinainsanpalu2019@gmail.com</p>
                            </div>
                        </div>
                        <div class="kop-npsn-box">
                            <div>NPSN : 69979223</div>
                            <div>NAJSIT : 7060102003</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Isi Dokumen Word -->
            <div class="paper-body">
                <div class="document-title-block">
                    <h2><?= htmlspecialchars($template['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID') ?></h2>
                    <h3><?= htmlspecialchars($template['nama_sekolah'] ?? 'SD ISLAM TERPADU BINA INSAN PALU') ?></h3>
                </div>

                <div class="bismillah-text">BISMILLAHIRROHMANIRROHIM</div>

                <div class="rendered-content">
                    <?= $renderedContent ?>
                </div>

                <!-- Kolom Tanda Tangan Dalam Dokumen (Jika Sudah TTD) -->
                <?php if ($sudahTtd): ?>
                    <div class="signatures-display-grid">
                        <!-- Ayah -->
                        <div class="sig-col">
                            <div class="sig-col-role">Ayah / Wali</div>
                            <div class="sig-image-box">
                                <?php if (!empty($decryptedAyah['png_base64'])): ?>
                                    <img src="<?= $decryptedAyah['png_base64'] ?>" alt="TTD Ayah">
                                    <div class="sig-badge-secured" title="AES-256-CBC Encrypted & Verified">
                                        <i class="fa-solid fa-shield-halved"></i> Sah & Terenkripsi
                                    </div>
                                <?php else: ?>
                                    <span style="font-size: 11px; color: #94a3b8;">(Tanda tangan tidak tersedia)</span>
                                <?php endif; ?>
                            </div>
                            <div class="sig-name"><?= htmlspecialchars($pendaftar['ttd_nama_ayah'] ?: $pendaftar['nama_ayah'] ?: '-') ?></div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
                                Ditandatangani: <?= !empty($pendaftar['ttd_submitted_at']) ? date('d/m/Y H:i', strtotime($pendaftar['ttd_submitted_at'])) : '-' ?>
                            </div>
                        </div>

                        <!-- Ibu -->
                        <div class="sig-col">
                            <div class="sig-col-role">Ibu / Wali</div>
                            <div class="sig-image-box">
                                <?php if (!empty($decryptedIbu['png_base64'])): ?>
                                    <img src="<?= $decryptedIbu['png_base64'] ?>" alt="TTD Ibu">
                                    <div class="sig-badge-secured" title="AES-256-CBC Encrypted & Verified">
                                        <i class="fa-solid fa-shield-halved"></i> Sah & Terenkripsi
                                    </div>
                                <?php else: ?>
                                    <span style="font-size: 11px; color: #94a3b8;">(Tanda tangan tidak tersedia)</span>
                                <?php endif; ?>
                            </div>
                            <div class="sig-name"><?= htmlspecialchars($pendaftar['ttd_nama_ibu'] ?: $pendaftar['nama_ibu'] ?: '-') ?></div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
                                Ditandatangani: <?= !empty($pendaftar['ttd_submitted_at']) ? date('d/m/Y H:i', strtotime($pendaftar['ttd_submitted_at'])) : '-' ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($template['catatan_bawah'])): ?>
                    <div style="margin-top: 28px; padding-top: 14px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #64748b;">
                        <?= nl2br(htmlspecialchars($template['catatan_bawah'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </article>

        <!-- ── Digital Signature Canvas Form (Jika Belum TTD) ─────── -->
        <?php if (!$sudahTtd): ?>
            <section class="signing-form-card" id="formSigningSection">
                <div class="form-heading">
                    <h3>
                        <i class="fa-solid fa-signature" style="color: #0284c7;"></i>
                        <span>Bubuhkan Tanda Tangan Digital</span>
                    </h3>
                    <p>
                        Gunakan jari (di layar HP/tablet) atau kursor mouse (di laptop/komputer) untuk menandatangani di kotak bawah ini.
                    </p>
                </div>

                <form id="formTtdKomitmen" method="POST" action="<?= url('spmb/komitmen/' . $token . '/submit') ?>">
                    <input type="hidden" name="ttd_ayah_data" id="ttdAyahData">
                    <input type="hidden" name="ttd_ibu_data" id="ttdIbuData">

                    <div class="pads-grid">
                        <!-- ── Signature Pad Ayah ── -->
                        <div class="pad-panel" id="padPanelAyah">
                            <div class="pad-header">
                                <span class="pad-role-title">
                                    <i class="fa-solid fa-user-tie" style="color: #0284c7;"></i>
                                    <span>Tanda Tangan Ayah / Wali</span>
                                </span>
                                <span class="pad-status-tag" id="tagAyah">Belum TTD</span>
                            </div>

                            <div class="canvas-container" id="canvasContainerAyah">
                                <canvas id="canvasAyah"></canvas>
                                <div class="canvas-placeholder-hint" id="hintAyah">
                                    <i class="fa-solid fa-pen-nib"></i>
                                    <span>Tanda tangan Ayah di sini</span>
                                </div>
                            </div>

                            <div class="pad-controls">
                                <span style="font-size: 10px; color: #64748b;">Sentuh / geser jari Anda</span>
                                <button type="button" class="btn-clear" onclick="clearPad('Ayah')">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>Ulangi / Hapus</span>
                                </button>
                            </div>

                            <div class="form-group-name">
                                <label for="inputNamaAyah">Nama Terang Ayah / Wali</label>
                                <input type="text" id="inputNamaAyah" name="ttd_nama_ayah" value="<?= $namaAyah ?>" required placeholder="Ketik nama lengkap ayah...">
                            </div>
                        </div>

                        <!-- ── Signature Pad Ibu ── -->
                        <div class="pad-panel" id="padPanelIbu">
                            <div class="pad-header">
                                <span class="pad-role-title">
                                    <i class="fa-solid fa-user" style="color: #ec4899;"></i>
                                    <span>Tanda Tangan Ibu / Wali</span>
                                </span>
                                <span class="pad-status-tag" id="tagIbu">Belum TTD</span>
                            </div>

                            <div class="canvas-container" id="canvasContainerIbu">
                                <canvas id="canvasIbu"></canvas>
                                <div class="canvas-placeholder-hint" id="hintIbu">
                                    <i class="fa-solid fa-pen-nib"></i>
                                    <span>Tanda tangan Ibu di sini</span>
                                </div>
                            </div>

                            <div class="pad-controls">
                                <span style="font-size: 10px; color: #64748b;">Sentuh / geser jari Anda</span>
                                <button type="button" class="btn-clear" onclick="clearPad('Ibu')">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>Ulangi / Hapus</span>
                                </button>
                            </div>

                            <div class="form-group-name">
                                <label for="inputNamaIbu">Nama Terang Ibu / Wali</label>
                                <input type="text" id="inputNamaIbu" name="ttd_nama_ibu" value="<?= $namaIbu ?>" required placeholder="Ketik nama lengkap ibu...">
                            </div>
                        </div>
                    </div>

                    <!-- Security & Encryption Badge -->
                    <div class="security-badge-box">
                        <div class="security-badge-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="security-badge-text">
                            <h4>
                                <span>Keamanan Terjamin &bull; Terenkripsi AES-256-CBC</span>
                                <i class="fa-solid fa-lock" style="color: #0284c7; font-size: 11px;"></i>
                            </h4>
                            <p>
                                Tanda tangan digital Anda dienkripsi secara kriptografis dengan algoritma AES-256-CBC dan integritas HMAC-SHA256. 
                                Data tanda tangan tidak dapat dipalsukan atau diubah oleh pihak ketiga, serta terekam bersama IP dan stempel waktu sah.
                            </p>
                        </div>
                    </div>

                    <!-- Persetujuan Checkbox -->
                    <div class="agreement-box">
                        <input type="checkbox" id="chkAgree" required>
                        <label for="chkAgree">
                            Saya bertindak sebagai orang tua/wali murid dari <strong><?= $namaAnak ?></strong> dengan ini menyatakan telah membaca, 
                            memahami, dan menyetujui seluruh ketentuan Surat Pernyataan Komitmen di atas secara sadar, sukarela, dan bertanggung jawab.
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="submit-btn-row">
                        <button type="submit" class="btn-submit-ttd" id="btnSubmitTtd">
                            <i class="fa-solid fa-lock"></i>
                            <span>Kirim & Simpan Tanda Tangan Komitmen</span>
                        </button>
                        <span style="font-size: 11px; color: #64748b;">
                            <i class="fa-solid fa-circle-info"></i> Tanda tangan ini wajib diselesaikan untuk memvalidasi pendaftaran siswa baru.
                        </span>
                    </div>
                </form>
            </section>
        <?php endif; ?>

    </div>

    <!-- ── JavaScript for Signature Pads ─────────────────────────── -->
    <script>
        class SignaturePad {
            constructor(canvasId, hintId, tagId, containerId) {
                this.canvas = document.getElementById(canvasId);
                this.hint = document.getElementById(hintId);
                this.tag = document.getElementById(tagId);
                this.container = document.getElementById(containerId);
                this.ctx = this.canvas.getContext('2d');
                
                this.isDrawing = false;
                this.hasDrawn = false;
                this.lastX = 0;
                this.lastY = 0;

                this.initCanvasSize();
                this.bindEvents();

                window.addEventListener('resize', () => {
                    // Simpan gambar lama sebelum resize
                    const temp = this.canvas.toDataURL();
                    this.initCanvasSize();
                    if (this.hasDrawn) {
                        const img = new Image();
                        img.onload = () => this.ctx.drawImage(img, 0, 0);
                        img.src = temp;
                    }
                });
            }

            initCanvasSize() {
                const rect = this.container.getBoundingClientRect();
                const dpr = window.devicePixelRatio || 1;
                
                this.canvas.width = rect.width * dpr;
                this.canvas.height = rect.height * dpr;
                
                this.ctx.scale(dpr, dpr);
                this.ctx.lineWidth = 2.4;
                this.ctx.lineCap = 'round';
                this.ctx.lineJoin = 'round';
                this.ctx.strokeStyle = '#0f172a'; // Tinta pena biru tua/hitam pekat
            }

            bindEvents() {
                // Pointer Events (Support touch & mouse simultaneously)
                this.canvas.addEventListener('pointerdown', (e) => this.start(e));
                this.canvas.addEventListener('pointermove', (e) => this.draw(e));
                this.canvas.addEventListener('pointerup', () => this.stop());
                this.canvas.addEventListener('pointerleave', () => this.stop());
                this.canvas.addEventListener('pointercancel', () => this.stop());
            }

            getPos(e) {
                const rect = this.canvas.getBoundingClientRect();
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top
                };
            }

            start(e) {
                e.preventDefault();
                this.isDrawing = true;
                const pos = this.getPos(e);
                this.lastX = pos.x;
                this.lastY = pos.y;
                
                // Mulai titik
                this.ctx.beginPath();
                this.ctx.arc(pos.x, pos.y, 1.2, 0, Math.PI * 2);
                this.ctx.fillStyle = '#0f172a';
                this.ctx.fill();

                if (!this.hasDrawn) {
                    this.hasDrawn = true;
                    if (this.hint) this.hint.style.opacity = '0';
                    if (this.container) this.container.classList.add('has-drawn');
                    if (this.tag) {
                        this.tag.textContent = '✓ Terisi';
                        this.tag.classList.add('signed');
                    }
                }
            }

            draw(e) {
                if (!this.isDrawing) return;
                e.preventDefault();
                const pos = this.getPos(e);

                this.ctx.beginPath();
                this.ctx.moveTo(this.lastX, this.lastY);
                this.ctx.lineTo(pos.x, pos.y);
                this.ctx.stroke();

                this.lastX = pos.x;
                this.lastY = pos.y;
            }

            stop() {
                this.isDrawing = false;
            }

            clear() {
                const dpr = window.devicePixelRatio || 1;
                this.ctx.clearRect(0, 0, this.canvas.width / dpr, this.canvas.height / dpr);
                this.hasDrawn = false;
                if (this.hint) this.hint.style.opacity = '1';
                if (this.container) this.container.classList.remove('has-drawn');
                if (this.tag) {
                    this.tag.textContent = 'Belum TTD';
                    this.tag.classList.remove('signed');
                }
            }

            isEmpty() {
                return !this.hasDrawn;
            }

            toDataURL() {
                return this.canvas.toDataURL('image/png');
            }
        }

        let padAyah, padIbu;

        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('canvasAyah')) {
                padAyah = new SignaturePad('canvasAyah', 'hintAyah', 'tagAyah', 'canvasContainerAyah');
            }
            if (document.getElementById('canvasIbu')) {
                padIbu = new SignaturePad('canvasIbu', 'hintIbu', 'tagIbu', 'canvasContainerIbu');
            }

            const form = document.getElementById('formTtdKomitmen');
            if (form) {
                form.addEventListener('submit', (e) => {
                    if (padAyah.isEmpty()) {
                        e.preventDefault();
                        alert('Mohon bubuhkan tanda tangan Ayah terlebih dahulu.');
                        document.getElementById('padPanelAyah').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }

                    if (padIbu.isEmpty()) {
                        e.preventDefault();
                        alert('Mohon bubuhkan tanda tangan Ibu terlebih dahulu.');
                        document.getElementById('padPanelIbu').scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return false;
                    }

                    const chk = document.getElementById('chkAgree');
                    if (chk && !chk.checked) {
                        e.preventDefault();
                        alert('Mohon centang pernyataan persetujuan komitmen.');
                        chk.focus();
                        return false;
                    }

                    // Isi hidden inputs dengan base64 PNG
                    document.getElementById('ttdAyahData').value = padAyah.toDataURL();
                    document.getElementById('ttdIbuData').value = padIbu.toDataURL();

                    const btn = document.getElementById('btnSubmitTtd');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Mengenkripsi & Menyimpan Tanda Tangan...</span>';
                });
            }
        });

        function clearPad(role) {
            if (role === 'Ayah' && padAyah) padAyah.clear();
            if (role === 'Ibu' && padIbu) padIbu.clear();
        }
    </script>
</body>
</html>
