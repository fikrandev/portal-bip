<?php
/**
 * View Cetak: Surat Pernyataan Komitmen Orang Tua/Wali Murid (Format A4 Resmi)
 * Modul SPMB - SIT Bina Insan Palu
 * 
 * @var array  $pendaftar
 * @var array  $template
 * @var string $renderedContent
 * @var string $kopUrl
 * @var array  $decryptedAyah
 * @var array  $decryptedIbu
 * @var string $pageTitle
 */

$namaAnak  = htmlspecialchars($pendaftar['nama_lengkap'] ?? '');
$namaAyah  = htmlspecialchars($pendaftar['ttd_nama_ayah'] ?: ($pendaftar['nama_ayah'] ?? ''));
$namaIbu   = htmlspecialchars($pendaftar['ttd_nama_ibu'] ?: ($pendaftar['nama_ibu'] ?? ''));
$nomorDaft = htmlspecialchars($pendaftar['nomor_pendaftaran'] ?? '');
$jenjang   = htmlspecialchars($pendaftar['jenjang'] ?? 'SD');
$sekolah   = htmlspecialchars($template['nama_sekolah'] ?? ('SIT Bina Insan Palu (' . $jenjang . ')'));
$tglTtd    = !empty($pendaftar['ttd_submitted_at']) 
    ? date('d F Y', strtotime($pendaftar['ttd_submitted_at'])) 
    : date('d F Y');
$ipTtd     = htmlspecialchars($pendaftar['ttd_ip_address'] ?? '');
$sudahTtd  = ($pendaftar['status_ttd_komitmen'] ?? '') === 'Sudah TTD';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Cetak Surat Komitmen Orang Tua') ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.55;
            color: #1e293b;
            background: #e2e8f0;
            padding: 24px 0;
        }

        /* ── Toolbar Aksi Layar (Disembunyikan saat Print) ───────── */
        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 52px;
            background: #0f172a;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.25);
        }

        .bar-title {
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bar-badge {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .bar-badge.verified {
            background: #059669;
            color: #ffffff;
        }

        .bar-badge.pending {
            background: #d97706;
            color: #ffffff;
        }

        .bar-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-print {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn-print:hover { background: #0369a1; }

        .btn-close {
            background: #334155;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-close:hover { background: #475569; }

        /* ── A4 Sheet Container ─────────────────────────────────── */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 48px auto 24px;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            padding: 16mm 18mm 20mm;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* ── Kop Surat ─────────────────────────────────────────── */
        .kop-banner-wrap {
            margin-bottom: 14px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 4px;
        }

        .kop-image {
            width: 100%;
            display: block;
            max-height: 130px;
            object-fit: contain;
        }

        .kop-fallback {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .kop-logo-area {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .kop-logo-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #0284c7;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 20px;
        }

        .kop-header-text h1 {
            font-size: 14pt;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.2px;
            line-height: 1.2;
        }

        .kop-header-text p {
            font-size: 8.5pt;
            color: #475569;
            margin-top: 2px;
        }

        .kop-meta {
            text-align: right;
            font-size: 8.5pt;
            font-family: 'JetBrains Mono', monospace;
            color: #334155;
            line-height: 1.4;
        }

        /* ── Document Titles ───────────────────────────────────── */
        .doc-title-box {
            text-align: center;
            margin-bottom: 14px;
        }

        .doc-title-box h2 {
            font-size: 13pt;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            line-height: 1.3;
        }

        .doc-title-box h3 {
            font-size: 11.5pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #0369a1;
            margin-top: 2px;
        }

        .bismillah {
            text-align: center;
            font-size: 10pt;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin: 10px 0 14px;
            text-transform: uppercase;
            color: #334155;
        }

        /* ── Document Body Content ─────────────────────────────── */
        .doc-content {
            font-size: 10pt;
            line-height: 1.6;
            color: #0f172a;
            text-align: justify;
        }

        .doc-content p {
            margin-bottom: 10px;
        }

        .doc-content ol, .doc-content ul {
            padding-left: 20px;
            margin-bottom: 12px;
        }

        .doc-content li {
            margin-bottom: 6px;
        }

        .doc-content strong {
            font-weight: 700;
        }

        /* ── Signature Section ─────────────────────────────────── */
        .signature-section {
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .sig-date-row {
            text-align: right;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .sig-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .sig-col {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 14px;
        }

        .sig-title {
            font-size: 10pt;
            font-weight: 700;
            margin-bottom: 6px;
            color: #0f172a;
        }

        .sig-img-box {
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin-bottom: 6px;
        }

        .sig-img-box img {
            max-height: 85px;
            max-width: 190px;
            object-fit: contain;
        }

        .sig-empty-line {
            height: 70px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            color: #94a3b8;
            font-size: 8.5pt;
            font-style: italic;
        }

        .sig-name {
            font-size: 10.5pt;
            font-weight: 800;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            padding-bottom: 2px;
            display: inline-block;
            min-width: 160px;
        }

        .sig-meta-tag {
            font-size: 7.5pt;
            font-family: 'JetBrains Mono', monospace;
            color: #059669;
            margin-top: 4px;
            line-height: 1.2;
        }

        /* ── Footer ────────────────────────────────────────────── */
        .sheet-footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1.5px solid #0284c7;
            font-size: 7.5pt;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .footer-left {
            line-height: 1.35;
        }

        .footer-right {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            color: #0284c7;
            font-weight: 700;
        }

        /* ── Print Specific Styles ─────────────────────────────── */
        @media print {
            body {
                background: none;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .page-sheet {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 10mm 14mm;
                box-shadow: none;
                border: none;
            }

            @page {
                size: A4 portrait;
                margin: 10mm 10mm 10mm 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- ── Screen Control Bar (Hidden on Print) ────────────────── -->
    <div class="no-print-bar">
        <div class="bar-title">
            <span>Surat Pernyataan Komitmen &bull; <?= $namaAnak ?> (<?= $nomorDaft ?>)</span>
            <?php if ($sudahTtd): ?>
                <span class="bar-badge verified">✓ Terekam & Terenkripsi</span>
            <?php else: ?>
                <span class="bar-badge pending">⚠️ Belum TTD Digital</span>
            <?php endif; ?>
        </div>
        <div class="bar-buttons">
            <button type="button" onclick="window.print()" class="btn-print">
                🖨️ Cetak / Simpan PDF
            </button>
            <button type="button" onclick="window.close()" class="btn-close">
                Tutup
            </button>
        </div>
    </div>

    <!-- ── Print Sheet ───────────────────────────────────────── -->
    <div class="page-sheet">
        <div>
            <!-- Kop Banner / Fallback -->
            <div class="kop-banner-wrap">
                <?php if (!empty($kopUrl)): ?>
                    <img src="<?= $kopUrl ?>" alt="Kop Surat <?= $jenjang ?>" class="kop-image">
                <?php else: ?>
                    <div class="kop-fallback">
                        <div class="kop-logo-area">
                            <div class="kop-logo-circle">BIP</div>
                            <div class="kop-header-text">
                                <h1><?= htmlspecialchars($template['nama_sekolah'] ?? 'SD ISLAM TERPADU BINA INSAN PALU') ?></h1>
                                <p>Jl. Agatis No. 1 Kel. Nunu Kec. Tatanga, Palu 94222 Sulawesi Tengah</p>
                                <p>Email: sditbinainsanpalu2019@gmail.com | Blog: sditbinainsanpalu.blogspot.com</p>
                            </div>
                        </div>
                        <div class="kop-meta">
                            <div>NPSN: 69979223</div>
                            <div>NAJSIT: 7060102003</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Title Block -->
            <div class="doc-title-box">
                <h2><?= htmlspecialchars($template['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID') ?></h2>
                <h3><?= htmlspecialchars($template['nama_sekolah'] ?? 'SD ISLAM TERPADU BINA INSAN PALU') ?></h3>
            </div>

            <div class="bismillah">BISMILLAHIRROHMANIRROHIM</div>

            <!-- Isi Dokumen -->
            <div class="doc-content">
                <?= $renderedContent ?>
            </div>
        </div>

        <!-- Bagian Tanda Tangan & Penutup -->
        <div>
            <div class="signature-section">
                <div class="sig-date-row">
                    Palu, <?= $tglTtd ?><br>
                    <strong>Orang Tua/Wali Murid</strong>
                </div>

                <div class="sig-grid">
                    <!-- Ayah -->
                    <div class="sig-col">
                        <div class="sig-title">Ayah / Wali</div>
                        <div class="sig-img-box">
                            <?php if (!empty($decryptedAyah['png_base64'])): ?>
                                <img src="<?= $decryptedAyah['png_base64'] ?>" alt="TTD Ayah">
                            <?php else: ?>
                                <div class="sig-empty-line">( .................................................. )</div>
                            <?php endif; ?>
                        </div>
                        <div class="sig-name"><?= $namaAyah ?: '( .................................................. )' ?></div>
                        <?php if ($sudahTtd && !empty($decryptedAyah)): ?>
                            <div class="sig-meta-tag">
                                🔒 Encrypted AES-256 &bull; <?= !empty($pendaftar['ttd_submitted_at']) ? date('d/m/Y H:i', strtotime($pendaftar['ttd_submitted_at'])) : '' ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Ibu -->
                    <div class="sig-col">
                        <div class="sig-title">Ibu / Wali</div>
                        <div class="sig-img-box">
                            <?php if (!empty($decryptedIbu['png_base64'])): ?>
                                <img src="<?= $decryptedIbu['png_base64'] ?>" alt="TTD Ibu">
                            <?php else: ?>
                                <div class="sig-empty-line">( .................................................. )</div>
                            <?php endif; ?>
                        </div>
                        <div class="sig-name"><?= $namaIbu ?: '( .................................................. )' ?></div>
                        <?php if ($sudahTtd && !empty($decryptedIbu)): ?>
                            <div class="sig-meta-tag">
                                🔒 Encrypted AES-256 &bull; <?= !empty($pendaftar['ttd_submitted_at']) ? date('d/m/Y H:i', strtotime($pendaftar['ttd_submitted_at'])) : '' ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($template['catatan_bawah'])): ?>
                    <div style="margin-top: 14px; font-size: 8.5pt; color: #475569; font-style: italic;">
                        <strong>Catatan:</strong> <?= htmlspecialchars($template['catatan_bawah']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer Resmi -->
            <div class="sheet-footer">
                <div class="footer-left">
                    Jl. Agatis No. 1 Kel. Nunu Kec. Tatanga Palu 94222 Sulawesi Tengah<br>
                    Email: sditbinainsanpalu2019@gmail.com &bull; Website: sditbinainsanpalu.blogspot.com
                </div>
                <div class="footer-right">
                    No. Reg: <?= $nomorDaft ?><br>
                    <?= $sudahTtd ? 'DOKUMEN RESMI TERVERIFIKASI' : 'DRAFT SPMB' ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto prompt print jika ada query ?autoprint=1
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            window.addEventListener('load', () => window.print());
        }
    </script>
</body>
</html>
