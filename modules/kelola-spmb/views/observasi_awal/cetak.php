<?php
/**
 * View Cetak: Lembar Hasil Observasi Awal Calon Murid Baru (Format A4 Resmi)
 * Modul SPMB - SIT Bina Insan Palu
 *
 * @var array $pendaftar
 * @var array $respon
 * @var array $groupBreakdown
 * @var array $answers
 */

$namaAnak     = htmlspecialchars($pendaftar['nama_lengkap'] ?? '');
$nomorDaft    = htmlspecialchars($pendaftar['nomor_pendaftaran'] ?? '');
$jenjang      = htmlspecialchars($pendaftar['jenjang'] ?? 'SD');
$namaUnit     = SpmbModel::getNamaSekolahByJenjang($jenjang);
$namaPengisi  = htmlspecialchars($respon['nama_pengisi'] ?? '-');
$hubPengisi   = htmlspecialchars($respon['hubungan_pengisi'] ?? 'Orang Tua / Wali');
$tglIsi       = !empty($respon['submitted_at']) ? date('d F Y, H:i', strtotime($respon['submitted_at'])) : '-';

$totalSkor    = (int)($respon['total_skor'] ?? 0);
$skorMaks     = (int)($respon['skor_maksimal'] ?? 0);
$persen       = (float)($respon['persentase'] ?? 0);
$kategori     = htmlspecialchars($respon['kategori_hasil'] ?? 'Menunggu Observasi');

$sbLogo = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $sbLogo = url(ltrim(SYS_APP_LOGO, '/'));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Observasi Awal - <?= $namaAnak ?> (<?= $nomorDaft ?>)</title>

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
            font-size: 10pt;
            line-height: 1.5;
            color: #1e293b;
            background: #e2e8f0;
            padding: 20px 0;
        }

        .no-print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 50px;
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

        /* A4 Page Layout */
        .page-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 40px auto 20px;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            padding: 15mm 18mm;
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
            width: 65px;
            height: 65px;
            object-fit: contain;
        }

        .kop-text h1 {
            font-size: 14pt;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.2px;
            text-transform: uppercase;
        }

        .kop-text h2 {
            font-size: 11pt;
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
            text-decoration: underline;
        }

        .doc-title p {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 2px;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Grid Data Calon Siswa */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9pt;
        }

        .data-table td {
            padding: 4px 8px;
            vertical-align: top;
        }

        .data-label {
            width: 22%;
            font-weight: 600;
            color: #475569;
        }

        .data-sep {
            width: 2%;
            font-weight: 700;
            color: #94a3b8;
        }

        .data-val {
            width: 26%;
            font-weight: 700;
            color: #0f172a;
        }

        /* Skor Highlight Box */
        .skor-box {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .skor-box-title {
            font-size: 9pt;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
        }

        .skor-badge {
            font-size: 11pt;
            font-weight: 800;
            color: #0284c7;
        }

        /* Breakdown Group Table */
        .group-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 8.5pt;
        }

        .group-table th, .group-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
        }

        .group-table th {
            background: #f1f5f9;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8pt;
            color: #334155;
        }

        .group-table td.center {
            text-align: center;
        }

        .group-table td.right {
            text-align: right;
        }

        /* Jawaban Detail List */
        .answers-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 8pt;
        }

        .answers-table th, .answers-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 8px;
        }

        .answers-table th {
            background: #f8fafc;
            font-weight: 700;
            color: #475569;
        }

        .group-section-row {
            background: #f1f5f9;
            font-weight: 800;
            color: #0369a1;
        }

        /* Catatan Ortu */
        .catatan-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 8.5pt;
            color: #92400e;
            margin-bottom: 20px;
        }

        /* Tanda Tangan */
        .ttd-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            text-align: center;
            font-size: 8.5pt;
            margin-top: 24px;
            page-break-inside: avoid;
        }

        .ttd-space {
            height: 60px;
        }

        .ttd-name {
            font-weight: 700;
            text-decoration: underline;
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
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: auto;
                padding: 10mm 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Navigasi Cetak -->
    <div class="no-print-bar">
        <div class="bar-title">
            Lembar Observasi Awal: <?= $namaAnak ?> (<?= $nomorDaft ?>)
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn-print" onclick="window.print()">
                🖨️ Cetak Dokumen / Simpan PDF
            </button>
            <button type="button" class="btn-close" onclick="window.close()">
                Tutup
            </button>
        </div>
    </div>

    <!-- Sheet A4 -->
    <div class="page-sheet">
        <!-- Kop Surat -->
        <div class="kop-area">
            <?php if ($sbLogo): ?>
                <img src="<?= $sbLogo ?>" alt="Logo SIT BIP" class="kop-logo">
            <?php else: ?>
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #0284c7; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 16pt;">
                    BIP
                </div>
            <?php endif; ?>
            <div class="kop-text">
                <h1>YAYASAN BINA INSAN PALU</h1>
                <h2><?= strtoupper($namaUnit) ?></h2>
                <p>Jl. Garuda No. 34, Birobuli Utara, Palu Selatan, Kota Palu, Sulawesi Tengah &bull; Telp: (0451) 481234 &bull; Email: spmb@binainsanpalu.sch.id</p>
            </div>
        </div>

        <!-- Judul Dokumen -->
        <div class="doc-title">
            <h3>LEMBAR HASIL OBSERVASI AWAL CALON MURID BARU</h3>
            <p>No. Registrasi: <?= $nomorDaft ?> &bull; Unit: <?= $jenjang ?> &bull; Tgl Cetak: <?= date('d/m/Y H:i') ?></p>
        </div>

        <!-- Tabel Data Siswa & Responden -->
        <table class="data-table">
            <tr>
                <td class="data-label">Nama Calon Murid</td>
                <td class="data-sep">:</td>
                <td class="data-val"><?= $namaAnak ?></td>
                <td class="data-label">Nama Pengisi</td>
                <td class="data-sep">:</td>
                <td class="data-val"><?= $namaPengisi ?></td>
            </tr>
            <tr>
                <td class="data-label">No. Pendaftaran</td>
                <td class="data-sep">:</td>
                <td class="data-val"><?= $nomorDaft ?></td>
                <td class="data-label">Hubungan Keluarga</td>
                <td class="data-sep">:</td>
                <td class="data-val"><?= $hubPengisi ?></td>
            </tr>
            <tr>
                <td class="data-label">Unit / Jenjang</td>
                <td class="data-sep">:</td>
                <td class="data-val"><?= $jenjang ?> (<?= $namaUnit ?>)</td>
                <td class="data-label">Waktu Pengisian</td>
                <td class="data-sep">:</td>
                <td class="data-val"><?= $tglIsi ?></td>
            </tr>
        </table>

        <!-- Ringkasan Nilai Observasi -->
        <div class="skor-box">
            <div>
                <span class="skor-box-title">Status Observasi:</span>
                <span style="font-size: 11pt; font-weight: 800; color: #059669; margin-left: 8px;">
                    Sudah Diisi Lengkap
                </span>
            </div>
            <div>
                <span class="skor-box-title">Total Skor Capaian:</span>
                <span class="skor-badge"><?= $totalSkor ?> / <?= $skorMaks ?> (<?= $persen ?>%)</span>
            </div>
        </div>

        <!-- Tabel Rekap Capaian per Aspek / Group -->
        <?php if (!empty($groupBreakdown)): ?>
            <table class="group-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Aspek / Group Observasi</th>
                        <th style="width: 15%; text-align: center;">Skor Diperoleh</th>
                        <th style="width: 15%; text-align: center;">Skor Maksimal</th>
                        <th style="width: 15%; text-align: center;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $noG = 1;
                    foreach ($groupBreakdown as $gb): 
                    ?>
                        <tr>
                            <td class="center"><?= $noG++ ?></td>
                            <td style="font-weight: 700;"><?= htmlspecialchars($gb['nama_group']) ?></td>
                            <td class="center font-bold"><?= $gb['total_skor'] ?></td>
                            <td class="center"><?= $gb['skor_maks'] ?></td>
                            <td class="center font-bold" style="color: #0284c7;"><?= $gb['persen'] ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Tabel Rincian Butir Jawaban -->
        <table class="answers-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th>Butir Pertanyaan Observasi</th>
                    <th style="width: 25%; text-align: center;">Pilihan Jawaban</th>
                    <th style="width: 10%; text-align: center;">Skor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($answers)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 12px; color: #94a3b8;">
                            Belum ada rincian jawaban yang tersimpan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $currGrpName = '';
                    $noQ = 1;
                    foreach ($answers as $a): 
                        if ($a['nama_group'] !== $currGrpName):
                            $currGrpName = $a['nama_group'];
                    ?>
                            <tr class="group-section-row">
                                <td colspan="4" style="padding: 4px 8px; font-weight: 800;">
                                    Aspek: <?= htmlspecialchars($currGrpName) ?>
                                </td>
                            </tr>
                    <?php endif; ?>
                        <tr>
                            <td style="text-align: center;"><?= $noQ++ ?></td>
                            <td>
                                <span style="font-weight: 600;"><?= htmlspecialchars($a['pertanyaan']) ?></span>
                                <?php if (!empty($a['indikator'])): ?>
                                    <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">
                                        Indikator: <?= htmlspecialchars($a['indikator']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-weight: 700; color: #0369a1;">
                                <?= htmlspecialchars($a['pilihan_jawaban']) ?>
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                <?= (int)$a['skor'] ?> / <?= (int)$a['skor_maksimal'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Catatan Khusus Orang Tua -->
        <?php if (!empty($respon['catatan_orang_tua'])): ?>
            <div class="catatan-box">
                <strong>Catatan Khusus Orang Tua/Wali:</strong><br>
                <em>"<?= nl2br(htmlspecialchars($respon['catatan_orang_tua'])) ?>"</em>
            </div>
        <?php endif; ?>

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
                <p><strong>Orang Tua / Wali Murid</strong></p>
                <div class="ttd-space"></div>
                <p class="ttd-name"><?= $namaPengisi !== '-' ? $namaPengisi : '(............................................)' ?></p>
            </div>
            <div>
                <p>Palu, <?= date('d F Y') ?></p>
                <p><strong>Tim Observasi & Psikologi</strong></p>
                <div class="ttd-space"></div>
                <p class="ttd-name">(............................................)</p>
            </div>
        </div>
    </div>

</body>
</html>
