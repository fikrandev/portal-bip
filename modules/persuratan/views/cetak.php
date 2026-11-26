<?php
/**
 * View Cetak Surat Resmi dengan KOP Masing-Masing Unit
 * Portal BIP
 *
 * @var array $surat
 * @var array $kop
 */
$bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$ts = strtotime($surat['tanggal_surat'] ?? date('Y-m-d'));
$tglIndo = date('j', $ts) . ' ' . ($bulanIndo[(int)date('n', $ts)] ?? '') . ' ' . date('Y', $ts);

$kopGambar = !empty($kop['kop_gambar']) ? url('public/' . ltrim($kop['kop_gambar'], '/')) : '';
$capGambar = !empty($kop['cap_gambar']) ? url('public/' . ltrim($kop['cap_gambar'], '/')) : '';
$ttdGambar = !empty($surat['ttd_gambar']) ? url('public/' . ltrim($surat['ttd_gambar'], '/')) : '';
$isApproved = (($surat['approval_status'] ?? '') === 'approved');
$logoKiri = !empty($kop['logo_kiri']) ? url('public/' . ltrim($kop['logo_kiri'], '/')) : (defined('SYS_APP_LOGO') && SYS_APP_LOGO ? url(ltrim(SYS_APP_LOGO, '/')) : '');
$logoKanan = !empty($kop['logo_kanan']) ? url('public/' . ltrim($kop['logo_kanan'], '/')) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Surat - <?= htmlspecialchars($surat['nomor_surat']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #111827; background: #f1f5f9; padding: 24px; line-height: 1.5; }
        .toolbar { max-width: 210mm; margin: 0 auto 14px auto; display: flex; justify-content: flex-end; gap: 8px; }
        .btn { padding: 8px 16px; border: none; border-radius: 8px; font-family: sans-serif; font-size: 12px; font-weight: 700; cursor: pointer; }
        .btn-print { background: #0284c7; color: #fff; }
        .btn-close { background: #e2e8f0; color: #334155; }
        .sheet { max-width: 210mm; min-height: 297mm; margin: 0 auto; background: #fff; padding: 18mm 20mm; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .kop { display: flex; align-items: center; gap: 16px; border-bottom: 3px double #0f172a; padding-bottom: 10px; margin-bottom: 18px; }
        .kop-logo { width: 70px; height: 70px; object-fit: contain; flex-shrink: 0; }
        .kop-text { flex: 1; text-align: center; }
        .kop-b1 { font-size: 12pt; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
        .kop-b2 { font-size: 15pt; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; }
        .kop-sub { font-size: 10pt; font-style: italic; margin-top: 2px; }
        .kop-addr { font-size: 9.5pt; color: #334155; margin-top: 2px; }
        .meta-row { display: flex; justify-content: space-between; margin-bottom: 18px; }
        .meta-table td { padding: 2px 6px 2px 0; vertical-align: top; }
        .isi { min-height: 120mm; white-space: pre-line; text-align: justify; margin: 16px 0 32px 0; }
        .ttd-wrap { display: flex; justify-content: flex-end; margin-top: 24px; }
        .ttd-box { width: 260px; text-align: left; }
        .ttd-space { position: relative; height: 78px; margin: 4px 0; }
        .ttd-cap-img { position: absolute; left: -18px; top: 0; height: 78px; opacity: 0.85; pointer-events: none; }
        .ttd-sign-img { position: relative; height: 72px; max-width: 220px; object-fit: contain; z-index: 2; }
        .kop-banner-img { width: 100%; max-height: 150px; object-fit: contain; display: block; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none; padding: 12mm 16mm; min-height: auto; }
            @page { size: A4 portrait; margin: 10mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button class="btn btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
        <button class="btn btn-close" onclick="window.close()">Tutup</button>
    </div>

    <div class="sheet">
        <?php if ($kopGambar): ?>
            <div class="kop" style="display:block; padding-bottom: 8px;">
                <img src="<?= htmlspecialchars($kopGambar) ?>" alt="KOP Surat" class="kop-banner-img">
            </div>
        <?php else: ?>
            <div class="kop">
                <?php if ($logoKiri): ?><img src="<?= htmlspecialchars($logoKiri) ?>" alt="Logo" class="kop-logo"><?php endif; ?>
                <div class="kop-text">
                    <div class="kop-b1"><?= htmlspecialchars($kop['baris_1'] ?? '') ?></div>
                    <div class="kop-b2"><?= htmlspecialchars($kop['baris_2'] ?? '') ?></div>
                    <?php if (!empty($kop['sub_judul'])): ?><div class="kop-sub"><?= htmlspecialchars($kop['sub_judul']) ?></div><?php endif; ?>
                    <?php if (!empty($kop['alamat'])): ?><div class="kop-addr"><?= htmlspecialchars($kop['alamat']) ?></div><?php endif; ?>
                    <?php if (!empty($kop['kontak'])): ?><div class="kop-addr"><?= htmlspecialchars($kop['kontak']) ?></div><?php endif; ?>
                </div>
                <?php if ($logoKanan): ?><img src="<?= htmlspecialchars($logoKanan) ?>" alt="Logo" class="kop-logo"><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="meta-row">
            <table class="meta-table">
                <tr><td>Nomor</td><td>:</td><td><strong><?= htmlspecialchars($surat['nomor_surat']) ?></strong></td></tr>
                <tr><td>Lampiran</td><td>:</td><td><?= htmlspecialchars($surat['lampiran_ket'] ?: '-') ?></td></tr>
                <tr><td>Perihal</td><td>:</td><td><strong><?= htmlspecialchars($surat['perihal']) ?></strong></td></tr>
            </table>
            <div>Palu, <?= htmlspecialchars($tglIndo) ?></div>
        </div>

        <?php if (!empty($surat['tujuan'])): ?>
            <div style="margin-bottom: 14px;">
                Kepada Yth.<br>
                <strong><?= htmlspecialchars($surat['tujuan']) ?></strong><br>
                di Tempat
            </div>
        <?php endif; ?>

        <div class="isi"><?= htmlspecialchars($surat['isi_ringkas'] ?: "Assalamu'alaikum Warahmatullahi Wabarakatuh.\n\nDengan hormat, bersama surat ini kami sampaikan hal sebagaimana tercantum pada perihal di atas.\n\nDemikian surat ini kami sampaikan, atas perhatian dan kerja samanya kami ucapkan terima kasih.\n\nWassalamu'alaikum Warahmatullahi Wabarakatuh.") ?></div>

        <div class="ttd-wrap">
            <div class="ttd-box">
                <div><?= htmlspecialchars($surat['approver_jabatan'] ?: ($kop['penandatangan_jabatan'] ?: 'Pimpinan ' . $surat['unit_nama'])) ?></div>
                <div class="ttd-space">
                    <?php if ($isApproved && $capGambar): ?>
                        <img src="<?= htmlspecialchars($capGambar) ?>" alt="Cap Unit" class="ttd-cap-img">
                    <?php endif; ?>
                    <?php if ($isApproved && $ttdGambar): ?>
                        <img src="<?= htmlspecialchars($ttdGambar) ?>" alt="TTD Digital" class="ttd-sign-img">
                    <?php endif; ?>
                </div>
                <div style="font-weight: 700; text-decoration: underline;">
                    <?= htmlspecialchars($surat['penandatangan'] ?: ($surat['approver_nama'] ?: ($kop['penandatangan_nama'] ?: '........................................'))) ?>
                </div>
                <?php if (!empty($surat['approver_niy']) || !empty($kop['penandatangan_niy'])): ?>
                    <div>NIY. <?= htmlspecialchars($surat['approver_niy'] ?: $kop['penandatangan_niy']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
