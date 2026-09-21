<?php
/**
 * Qur'an PAUD - Lembar Cetak Rekapitulasi Nilai Siap Cetak A4
 */
$filterKategori = $_GET['kategori'] ?? '';
$filterKelas = $_GET['kelas'] ?? '';
$katLabel = ($filterKategori === 'tahsin') ? 'a. Tahsin Al-Qur\'an (Iqro)' : (($filterKategori === 'tahfidz') ? 'b. Tahfidz (Surah Pendek)' : 'Seluruh Kategori (Tahsin & Tahfidz)');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Nilai Qur'an PAUD - <?= SYS_APP_NAME ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #111827;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header-kop {
            display: flex;
            align-items: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 8px;
            margin-bottom: 18px;
        }
        .logo-box {
            width: 70px;
            height: 70px;
            margin-right: 15px;
            text-align: center;
        }
        .logo-box img {
            max-width: 100%;
            max-height: 100%;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-text h3 {
            margin: 2px 0;
            font-size: 12pt;
            font-weight: normal;
        }
        .kop-text p {
            margin: 0;
            font-size: 9pt;
            font-style: italic;
        }
        .title-doc {
            text-align: center;
            margin: 15px 0;
        }
        .title-doc h4 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .title-doc span {
            font-size: 10pt;
            display: block;
            margin-top: 3px;
        }
        .meta-info {
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-info td {
            padding: 2px 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .stars { color: #d97706; font-size: 11pt; }
        .ttd-box {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .ttd-col {
            width: 220px;
            text-align: center;
        }
        .ttd-space {
            height: 70px;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- Floating Print Button -->
    <div class="no-print" style="position: fixed; top: 15px; right: 15px; z-index: 999; background: #0f172a; padding: 10px 16px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <button onclick="window.print()" style="background: #f59e0b; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: bold; font-size: 12px; cursor: pointer;">
            🖨️ Cetak Dokumen A4
        </button>
        <button onclick="window.close()" style="background: #334155; color: #fff; border: none; padding: 8px 12px; border-radius: 8px; font-size: 12px; cursor: pointer; margin-left: 6px;">
            Tutup
        </button>
    </div>

    <!-- KOP SURAT -->
    <div class="header-kop">
        <?php if (!empty(SYS_APP_LOGO) && file_exists(BASE_PATH . '/' . ltrim(SYS_APP_LOGO, '/'))): ?>
            <div class="logo-box">
                <img src="<?= url(ltrim(SYS_APP_LOGO, '/')) ?>" alt="Logo">
            </div>
        <?php endif; ?>
        <div class="kop-text">
            <h2><?= SYS_APP_NAME ?></h2>
            <h3>UNIT PENDIDIKAN ANAK USIA DINI (PAUD / TK)</h3>
            <p>Jalan Pendidikan No. 1, Kota Palu, Sulawesi Tengah &bull; Portal Informasi Akademik &amp; Qur'an</p>
        </div>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="title-doc">
        <h4>LEMBAR REKAPITULASI CAPAIAN AL-QUR'AN SANTRI PAUD</h4>
        <span>Tahun Ajaran <?= defined('SYS_TAHUN_AKADEMIK_NAME') ? SYS_TAHUN_AKADEMIK_NAME : '2026/2027 Ganjil' ?></span>
    </div>

    <!-- META FILTER -->
    <div class="meta-info">
        <table>
            <tr>
                <td style="width: 15%;"><strong>Kategori Target</strong></td>
                <td style="width: 35%;">: <?= $katLabel ?></td>
                <td style="width: 15%;"><strong>Tanggal Cetak</strong></td>
                <td style="width: 35%;">: <?= date('d F Y') ?></td>
            </tr>
            <tr>
                <td><strong>Kelas / Rombel</strong></td>
                <td>: <?= htmlspecialchars(!empty($filterKelas) ? $filterKelas : 'Seluruh Rombel PAUD') ?></td>
                <td><strong>Total Santri</strong></td>
                <td>: <?= count($rekapList) ?> Santri Cilik</td>
            </tr>
        </table>
    </div>

    <!-- TABEL DATA -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Santri Cilik</th>
                <th style="width: 12%;">Kelas</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 18%;">Materi Terakhir</th>
                <th style="width: 10%;">Mutu / Makhraj</th>
                <th style="width: 10%;">Bintang ⭐</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rekapList)): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Belum ada data nilai untuk dicetak.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rekapList as $idx => $r): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td>
                            <strong><?= htmlspecialchars($r['nama_lengkap']) ?></strong>
                            <div style="font-size: 8pt; color: #555;">NIS: <?= htmlspecialchars($r['nis'] ?: '-') ?></div>
                        </td>
                        <td class="text-center"><?= htmlspecialchars($r['kelas']) ?></td>
                        <td class="text-center"><?= $r['kategori'] === 'tahsin' ? 'Tahsin' : 'Tahfidz' ?></td>
                        <td><?= htmlspecialchars($r['materi_dinilai']) ?></td>
                        <td class="text-center">K: <?= $r['nilai_kelancaran'] ?> / M: <?= $r['nilai_makhraj'] ?></td>
                        <td class="text-center stars"><?= str_repeat('★', $r['bintang']) ?></td>
                        <td class="text-center"><strong><?= htmlspecialchars($r['status_lulus']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="ttd-box">
        <div class="ttd-col">
            <div>Mengetahui,</div>
            <div style="font-weight: bold;">Kepala Sekolah PAUD / TK</div>
            <div class="ttd-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">Ustadzah Hj. Nurhayati, S.Pd.I</div>
            <div style="font-size: 9pt;">NIP. 198506142010012015</div>
        </div>

        <div class="ttd-col">
            <div>Palu, <?= date('d F Y') ?></div>
            <div style="font-weight: bold;">Guru Pembimbing Qur'an</div>
            <div class="ttd-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">Ustadzah Fatimah, S.Pd.I</div>
            <div style="font-size: 9pt;">Pengampu Qur'an PAUD</div>
        </div>
    </div>

</body>
</html>
