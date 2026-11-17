<?php
/**
 * Lembar Verifikasi & Bukti Pengajuan Pembelian Baru Sarpras
 * Portal BIP
 */
$sekolahName = defined('SYS_APP_NAME') ? SYS_APP_NAME : 'PORTAL BIP';
$sekolahSub = defined('SYS_APP_TAGLINE') ? SYS_APP_TAGLINE : 'Sistem Informasi Manajemen Sekolah Terpadu';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Pembelian - <?= e($item['kode_pengajuan']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
        }
        body {
            margin: 0;
            padding: 20px;
            font-size: 12px;
            background: #fff;
        }
        .header-box {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-text {
            flex: 1;
            text-align: center;
        }
        .header-text h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header-text p {
            margin: 3px 0 0;
            font-size: 11px;
            color: #64748b;
        }
        .title-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-box h2 {
            margin: 0;
            font-size: 15px;
            text-decoration: underline;
            text-transform: uppercase;
            font-weight: 700;
        }
        .title-box .kode {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-top: 4px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .info-table td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 12px;
        }
        .info-table td.label {
            width: 25%;
            font-weight: 600;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .info-table td.val {
            border: 1px solid #e2e8f0;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .ttd-grid {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .ttd-grid td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .ttd-space {
            height: 70px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 999;
        }
        .btn-print {
            background: #0f766e;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
</div>

<div class="header-box">
    <div class="header-text">
        <h1><?= e($sekolahName) ?></h1>
        <p><?= e($sekolahSub) ?> &bull; Bagian Sarana &amp; Prasarana (SARPRAS)</p>
    </div>
</div>

<div class="title-box">
    <h2>FORMULIR PENGAJUAN PENGADAAN / PEMBELIAN BARU SARPRAS</h2>
    <div class="kode">Nomor Tiket: <strong><?= e($item['kode_pengajuan']) ?></strong></div>
</div>

<table class="info-table">
    <tr>
        <td class="label">Tanggal Pengajuan</td>
        <td class="val"><?= date('d F Y', strtotime($item['tanggal_pengajuan'])) ?></td>
        <td class="label">Unit Pemohon</td>
        <td class="val"><strong><?= e($item['unit'] ?? 'Umum') ?></strong></td>
    </tr>
    <tr>
        <td class="label">Nama Pemohon</td>
        <td class="val"><?= e($item['pemohon_nama'] ?? $item['pengaju'] ?? 'Staff') ?></td>
        <td class="label">Rencana Penempatan</td>
        <td class="val"><?= !empty($item['nama_ruangan']) ? e($item['nama_ruangan']) : 'Gudang / Fasilitas Bersama' ?></td>
    </tr>
    <tr>
        <td class="label">Nama Barang / Pengadaan</td>
        <td class="val" colspan="3"><strong><?= e($item['nama_barang']) ?></strong></td>
    </tr>
    <tr>
        <td class="label">Kategori Aset</td>
        <td class="val"><?= e($item['nama_kategori'] ?: 'Umum / Sarpras') ?></td>
        <td class="label">Jumlah &amp; Satuan</td>
        <td class="val"><strong><?= number_format($item['jumlah']) ?> <?= e($item['satuan'] ?? 'Unit') ?></strong></td>
    </tr>
    <tr>
        <td class="label">Estimasi Harga Satuan</td>
        <td class="val">Rp <?= number_format($item['estimasi_harga'], 0, ',', '.') ?></td>
        <td class="label">Total Estimasi Anggaran</td>
        <td class="val"><strong style="color: #047857; font-size: 13px;">Rp <?= number_format($item['total_estimasi'] ?: ($item['jumlah'] * $item['estimasi_harga']), 0, ',', '.') ?></strong></td>
    </tr>
    <tr>
        <td class="label">Realisasi Harga Beli</td>
        <td class="val" colspan="3">
            <strong><?= $item['realisasi_harga'] > 0 ? 'Rp ' . number_format($item['realisasi_harga'], 0, ',', '.') : 'Belum Terealisasi' ?></strong>
        </td>
    </tr>
    <tr>
        <td class="label">Spesifikasi / Rincian</td>
        <td class="val" colspan="3" style="min-height: 40px;">
            <?= nl2br(e($item['spesifikasi'] ?: '-')) ?>
        </td>
    </tr>
    <tr>
        <td class="label">Alasan / Urgensi Kebutuhan</td>
        <td class="val" colspan="3" style="min-height: 40px;">
            <?= nl2br(e($item['alasan_kebutuhan'] ?: $item['keperluan'] ?: '-')) ?>
        </td>
    </tr>
    <tr>
        <td class="label">Status Verifikasi</td>
        <td class="val">
            <?php if (($item['status_verifikasi'] ?? '') === 'Disetujui'): ?>
                <span class="badge badge-success">✓ Disetujui</span>
            <?php elseif (($item['status_verifikasi'] ?? '') === 'Ditolak'): ?>
                <span class="badge badge-danger">✕ Ditolak</span>
            <?php else: ?>
                <span class="badge badge-warning">⏳ Menunggu Verifikasi</span>
            <?php endif; ?>
        </td>
        <td class="label">Status Pengadaan</td>
        <td class="val">
            <span class="badge badge-info"><?= e($item['status'] ?? 'Menunggu') ?></span>
        </td>
    </tr>
    <?php if (!empty($item['verified_by_name'])): ?>
    <tr>
        <td class="label">Diverifikasi Oleh</td>
        <td class="val"><?= e($item['verified_by_name']) ?></td>
        <td class="label">Waktu Verifikasi</td>
        <td class="val"><?= !empty($item['verified_at']) ? date('d/m/Y H:i', strtotime($item['verified_at'])) : '-' ?></td>
    </tr>
    <tr>
        <td class="label">Catatan Verifikasi</td>
        <td class="val" colspan="3"><?= nl2br(e($item['catatan_verifikasi'] ?: '-')) ?></td>
    </tr>
    <?php endif; ?>
</table>

<table class="ttd-grid">
    <tr>
        <td>
            Pemohon Pengadaan,
            <div class="ttd-space"></div>
            <div class="ttd-name"><?= e($item['pemohon_nama']) ?></div>
            <div>Staf / Pemohon</div>
        </td>
        <td>
            Bagian Sarpras,
            <div class="ttd-space"></div>
            <div class="ttd-name"><?= e($item['verified_by_name'] ?: '(...........................)') ?></div>
            <div>Staf Inventaris / Sarpras</div>
        </td>
        <td>
            Kepala Sekolah / Yayasan,
            <div class="ttd-space"></div>
            <div class="ttd-name">(...........................)</div>
            <div>Pejabat Berwenang / Pengesah</div>
        </td>
    </tr>
</table>

</body>
</html>
