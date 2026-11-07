<?php
/**
 * View Cetak: Rekapitulasi Hasil Observasi Awal & Analisis Harapan Orang Tua (Format A4 Resmi)
 * Modul SPMB - SIT Bina Insan Palu
 *
 * @var array $items
 * @var array $stats
 * @var array $analisisHarapan
 * @var string $jenjang
 * @var string $statusObservasi
 * @var string $search
 * @var array $activeGroup
 */

$filterJenjang = !empty($jenjang) && strtoupper($jenjang) !== 'ALL' ? strtoupper($jenjang) : 'Semua Unit (PAUD, SD, SMP, SMA)';
$namaUnitLabel = (!empty($jenjang) && strtoupper($jenjang) !== 'ALL') ? SpmbModel::getNamaSekolahByJenjang($jenjang) : 'Sekolah Islam Terpadu Bina Insan Palu';
$tahunAjaran   = $activeGroup['tahun_ajaran'] ?? ($activeGroup['nama_group'] ?? '2026/2027');

$sbLogo = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}
$unitKopPath = class_exists('SpmbModel') && !empty($jenjang) && $jenjang !== 'ALL' ? SpmbModel::getKopUnit($jenjang) : null;
$unitKopUrl  = !empty($unitKopPath) ? SpmbModel::getKopUrl($unitKopPath) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Observasi Awal & Harapan Orang Tua - <?= htmlspecialchars($filterJenjang) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

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
            font-size: 9.5pt;
            line-height: 1.45;
            color: #1e293b;
            background: #e2e8f0;
            padding: 20px 0;
        }

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
            gap: 8px;
        }

        .bar-badge {
            background: #0284c7;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .btn-print {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 7px 18px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover { background: #0369a1; }

        .btn-close {
            background: #334155;
            color: #ffffff;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            margin-left: 8px;
        }

        .btn-close:hover { background: #475569; }

        /* A4 Page Layout */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 40px auto 20px;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            padding: 14mm 16mm;
            position: relative;
        }

        /* Kop Surat */
        .kop-area {
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .kop-logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
        }

        .kop-text {
            flex: 1;
        }

        .kop-text h1 {
            font-size: 13.5pt;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.2px;
            text-transform: uppercase;
        }

        .kop-text h2 {
            font-size: 10.5pt;
            font-weight: 700;
            color: #0369a1;
        }

        .kop-text p {
            font-size: 8pt;
            color: #475569;
            margin-top: 2px;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin-bottom: 16px;
        }

        .doc-title h3 {
            font-size: 12pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .doc-title p {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Mini Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        .stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
            background: #f8fafc;
            text-align: center;
        }

        .stat-card .val {
            font-size: 13pt;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .stat-card .lbl {
            font-size: 7.5pt;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            margin-top: 2px;
        }

        /* Box Kesimpulan Analisis Harapan */
        .analysis-box {
            border: 1.5px solid #0284c7;
            background: #f0f9ff;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 16px;
            page-break-inside: avoid;
        }

        .analysis-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #bae6fd;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .analysis-title {
            font-size: 10pt;
            font-weight: 800;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .analysis-badge {
            font-size: 8pt;
            font-weight: 700;
            background: #0284c7;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .analysis-body {
            font-size: 8.5pt;
            line-height: 1.55;
            color: #0f172a;
            text-align: justify;
        }

        .analysis-body strong {
            color: #0369a1;
        }

        /* Topik Distribusi Bars */
        .topic-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px 12px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #bae6fd;
        }

        .topic-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8pt;
        }

        .topic-name {
            font-weight: 600;
            color: #334155;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .topic-bullet {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .topic-pct {
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            color: #0f172a;
        }

        /* Rekomendasi Bar */
        .rekomendasi-box {
            margin-top: 8px;
            padding: 6px 10px;
            background: #ffffff;
            border-left: 3px solid #059669;
            border-radius: 4px;
            font-size: 8pt;
            color: #065f46;
        }

        /* Kutipan Pilihan Box */
        .quotes-section {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #bae6fd;
        }

        .quotes-title {
            font-size: 8pt;
            font-weight: 700;
            color: #0369a1;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .quotes-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }

        .quote-item {
            background: #ffffff;
            border: 1px solid #e0f2fe;
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 7.5pt;
            line-height: 1.35;
            color: #334155;
            font-style: italic;
        }

        .quote-author {
            display: block;
            margin-top: 3px;
            font-weight: 700;
            font-style: normal;
            font-size: 7pt;
            color: #0284c7;
        }

        /* Tabel Data Calon Siswa */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 18px;
        }

        .rekap-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .rekap-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            line-height: 1.35;
        }

        .rekap-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        .badge-kategori {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 7pt;
        }

        .badge-mandiri { background: #dcfce7; color: #166534; }
        .badge-baik    { background: #e0f2fe; color: #075985; }
        .badge-cukup   { background: #fef3c7; color: #92400e; }
        .badge-kurang  { background: #fee2e2; color: #991b1b; }
        .badge-belum   { background: #f1f5f9; color: #64748b; }

        /* Tanda Tangan */
        .ttd-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            text-align: center;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .ttd-grid p {
            font-size: 8.5pt;
            color: #1e293b;
        }

        .ttd-space {
            height: 60px;
        }

        .ttd-name {
            font-weight: 700;
            text-decoration: underline;
        }

        /* Footer */
        .doc-footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-size: 7.5pt;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .no-print-bar {
                display: none !important;
            }

            .page-sheet {
                width: 100% !important;
                margin: 0 !important;
                padding: 10mm 12mm !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Navigasi Cetak (Hanya tampil di layar) -->
    <div class="no-print-bar">
        <div class="bar-title">
            <span>Rekapitulasi Observasi Awal SPMB</span>
            <span class="bar-badge"><?= htmlspecialchars($filterJenjang) ?></span>
            <span style="font-size: 11px; color: #94a3b8;">T.A. <?= htmlspecialchars($tahunAjaran) ?></span>
        </div>
        <div>
            <button class="btn-print" onclick="window.print()">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak Rekap (A4)
            </button>
            <button class="btn-close" onclick="window.close()">Tutup</button>
        </div>
    </div>

    <!-- Lembar Halaman Cetak -->
    <div class="page-sheet">

        <!-- Kop Surat Resmi -->
        <?php if (!empty($unitKopUrl)): ?>
            <div style="margin-bottom: 14px; border-bottom: 2px solid #0f172a; padding-bottom: 6px;">
                <img src="<?= htmlspecialchars($unitKopUrl) ?>" alt="Kop Surat" style="width: 100%; max-height: 105px; object-fit: contain;">
            </div>
        <?php else: ?>
            <div class="kop-area">
                <?php if (!empty($sbLogo)): ?>
                    <img src="<?= htmlspecialchars($sbLogo) ?>" alt="Logo SIT BIP" class="kop-logo">
                <?php else: ?>
                    <div style="width: 65px; height: 65px; background: #0284c7; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 18pt;">
                        BIP
                    </div>
                <?php endif; ?>
                <div class="kop-text">
                    <h1>Sekolah Islam Terpadu Bina Insan Palu</h1>
                    <h2>Panitia Sistem Penerimaan Murid Baru (SPMB)</h2>
                    <p>Jl. Agatis No. 1, Kel. Nunu, Kec. Tatanga, Kota Palu, Sulawesi Tengah | Telp/WA: 0811-4560-1234</p>
                    <p>Jenjang: PAUD IT - SD IT - SMP IT - SMA IT Bina Insan | Website: binainsanpalu.sch.id</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h3>Rekapitulasi Observasi Awal & Analisis Harapan Orang Tua</h3>
            <p>Unit: <?= htmlspecialchars($filterJenjang) ?> &bull; Tahun Ajaran <?= htmlspecialchars($tahunAjaran) ?> &bull; Dicetak: <?= date('d F Y, H:i') ?> WITA</p>
        </div>

        <!-- Ringkasan Statistik Siswa -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="val"><?= count($items) ?></div>
                <div class="lbl">Total Calon Murid</div>
            </div>
            <div class="stat-card">
                <div class="val" style="color: #059669;">
                    <?= count(array_filter($items, fn($i) => ($i['status_observasi'] ?? '') === 'Sudah Mengisi')) ?>
                </div>
                <div class="lbl">Sudah Mengisi Observasi</div>
            </div>
            <div class="stat-card">
                <div class="val" style="color: #d97706;">
                    <?= $analisisHarapan['total_responden'] ?>
                </div>
                <div class="lbl">Tercatat Harapan Orang Tua</div>
            </div>
            <div class="stat-card">
                <div class="val" style="color: #7c3aed;">
                    <?= $stats['rata_persentase'] ?? 0 ?>%
                </div>
                <div class="lbl">Rata-rata Skor Observasi</div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- KOTAK ANALISIS & KESIMPULAN HARAPAN ORANG TUA -->
        <!-- =================================================================== -->
        <div class="analysis-box">
            <div class="analysis-header">
                <span class="analysis-title">&#128218; Kesimpulan Analisis Harapan Orang Tua Calon Murid Baru</span>
                <span class="analysis-badge"><?= $analisisHarapan['total_responden'] ?> Responden Harapan</span>
            </div>

            <!-- Sintesis Naratif Otomatis -->
            <div class="analysis-body">
                <p>
                    <?= nl2br(preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', htmlspecialchars($analisisHarapan['kesimpulan_utama']))) ?>
                </p>
            </div>

            <!-- Distribusi Tematik Harapan -->
            <?php if (!empty($analisisHarapan['kategori'])): ?>
                <div class="topic-grid">
                    <?php foreach ($analisisHarapan['kategori'] as $k): ?>
                        <div class="topic-item">
                            <span class="topic-name">
                                <span class="topic-bullet" style="background-color: <?= htmlspecialchars($k['color']) ?>;"></span>
                                <?= htmlspecialchars($k['label']) ?>
                            </span>
                            <span class="topic-pct" style="color: <?= htmlspecialchars($k['color']) ?>;">
                                <?= $k['persentase'] ?>% (<?= $k['count'] ?>)
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Rekomendasi Pedagogis -->
            <?php if (!empty($analisisHarapan['rekomendasi'])): ?>
                <div class="rekomendasi-box">
                    <strong>Rekomendasi Tindak Lanjut Sekolah & Guru:</strong> <?= htmlspecialchars($analisisHarapan['rekomendasi']) ?>
                </div>
            <?php endif; ?>

            <!-- Kutipan Pilihan Representatif -->
            <?php if (!empty($analisisHarapan['kutipan_pilihan'])): ?>
                <div class="quotes-section">
                    <div class="quotes-title">Kutipan Pilihan Harapan Orang Tua:</div>
                    <div class="quotes-list">
                        <?php foreach ($analisisHarapan['kutipan_pilihan'] as $q): ?>
                            <div class="quote-item">
                                "<?= htmlspecialchars($q['teks']) ?>"
                                <span class="quote-author">&bull; <?= htmlspecialchars($q['nama_siswa']) ?> (<?= htmlspecialchars($q['jenjang']) ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- =================================================================== -->
        <!-- TABEL REKAPITULASI HASIL OBSERVASI SELURUH CALON SISWA -->
        <!-- =================================================================== -->
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 4%; text-align: center;">No</th>
                    <th style="width: 14%;">No. Pendaftaran & Nama</th>
                    <th style="width: 6%; text-align: center;">Unit</th>
                    <th style="width: 12%; text-align: center;">Hasil Observasi</th>
                    <th style="width: 14%;">Pengisi & Hubungan</th>
                    <th style="width: 25%;">Catatan Tambahan Ortu</th>
                    <th style="width: 25%;">Harapan Orang Tua</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 18px; color: #94a3b8;">
                            Tidak ada data calon murid baru ditemukan untuk filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1;
                    foreach ($items as $it): 
                        $isSudah = (($it['status_observasi'] ?? '') === 'Sudah Mengisi');
                        $katHasil = $it['kategori_hasil'] ?? 'Menunggu';
                        
                        $badgeClass = 'badge-belum';
                        if ($katHasil === 'Sangat Baik / Mandiri') {
                            $badgeClass = 'badge-mandiri';
                        } elseif ($katHasil === 'Baik / Berkembang') {
                            $badgeClass = 'badge-baik';
                        } elseif ($katHasil === 'Cukup') {
                            $badgeClass = 'badge-cukup';
                        } elseif ($katHasil === 'Perlu Pendampingan') {
                            $badgeClass = 'badge-kurang';
                        }
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: 600;"><?= $no++ ?></td>
                            <td>
                                <strong style="color: #0f172a; font-size: 8.5pt;"><?= htmlspecialchars($it['nama_lengkap']) ?></strong>
                                <div style="font-family: 'JetBrains Mono', monospace; font-size: 7pt; color: #64748b;">
                                    <?= htmlspecialchars($it['nomor_pendaftaran']) ?>
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #0369a1;">
                                <?= htmlspecialchars($it['jenjang']) ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($isSudah): ?>
                                    <span class="badge-kategori <?= $badgeClass ?>"><?= htmlspecialchars($katHasil) ?></span>
                                    <div style="font-size: 7.5pt; font-weight: 700; color: #334155; margin-top: 2px;">
                                        <?= (int)($it['total_skor'] ?? 0) ?>/<?= (int)($it['skor_maksimal'] ?? 0) ?> (<?= (float)($it['persentase'] ?? 0) ?>%)
                                    </div>
                                <?php else: ?>
                                    <span class="badge-kategori badge-belum">Belum Mengisi</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isSudah): ?>
                                    <span style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($it['nama_pengisi'] ?: '-') ?></span>
                                    <div style="font-size: 7pt; color: #64748b;"><?= htmlspecialchars($it['hubungan_pengisi'] ?: 'Wali') ?></div>
                                <?php else: ?>
                                    <span style="color: #94a3b8;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($it['catatan_orang_tua'])): ?>
                                    <span style="font-size: 7.5pt; color: #334155;">
                                        <?= nl2br(htmlspecialchars($it['catatan_orang_tua'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 7pt;">(Tidak ada catatan)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($it['harapan_orang_tua'])): ?>
                                    <span style="font-size: 7.5pt; font-weight: 600; color: #065f46;">
                                        "<?= nl2br(htmlspecialchars($it['harapan_orang_tua'])) ?>"
                                    </span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 7pt;">(Belum mengisi harapan)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Kolom Tanda Tangan -->
        <div class="ttd-grid">
            <div>
                <p>Mengetahui,</p>
                <p><strong>Ketua Panitia SPMB</strong></p>
                <div class="ttd-space"></div>
                <p class="ttd-name">Panitia SPMB SIT BIP</p>
            </div>
            <div>
                <p>Palu, <?= date('d F Y') ?></p>
                <p><strong>Tim Observasi & Psikologi</strong></p>
                <div class="ttd-space"></div>
                <p class="ttd-name">(............................................)</p>
            </div>
            <div>
                <p>Palu, <?= date('d F Y') ?></p>
                <p><strong>Koordinator Pendidikan SIT</strong></p>
                <div class="ttd-space"></div>
                <p class="ttd-name">(............................................)</p>
            </div>
        </div>

        <!-- Footer Dokumen -->
        <div class="doc-footer">
            <span>Dicetak otomatis melalui binainsanpalu.sch.id &bull; SPMB SIT Bina Insan Palu</span>
            <span>Dokumen Rekapitulasi Observasi Awal & Analisis Harapan Murid Baru</span>
        </div>

    </div>

</body>
</html>
