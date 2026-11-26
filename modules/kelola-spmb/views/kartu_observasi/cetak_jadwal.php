<?php
/**
 * View Cetak & Download PDF Daftar Peserta & Jadwal Sesi Observasi Per Ruangan
 * Portal BIP - Modul Kelola SPMB
 */
$jadwalGroups = $jadwalGroups ?? [];
$logoUrl = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
} elseif (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $logoUrl = url(ltrim(SYS_APP_FAVICON, '/'));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak PDF Jadwal Sesi &amp; Ruangan Observasi SPMB</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #0f172a; background: #f1f5f9; padding: 16px; }
        .toolbar { max-width: 210mm; margin: 0 auto 14px auto; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 8px; background: #0f172a; color: #fff; padding: 10px 14px; border-radius: 10px; }
        .toolbar-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
        .toolbar-select { padding: 6px 10px; border-radius: 7px; border: 1px solid #334155; background: #1e293b; color: #fff; font-size: 11.5px; font-weight: 600; outline: none; }
        .btn { padding: 7px 14px; border: none; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-pdf { background: #059669; color: #fff; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-card { background: #4f46e5; color: #fff; }
        .sheet { max-width: 210mm; margin: 0 auto; background: #fff; padding: 14mm 14mm; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
        .header { display: flex; align-items: center; gap: 12px; border-bottom: 2.5px double #0f172a; padding-bottom: 10px; margin-bottom: 14px; }
        .header img { width: 46px; height: 46px; object-fit: contain; }
        .header-title { font-size: 14px; font-weight: 800; text-transform: uppercase; }
        .header-sub { font-size: 11px; color: #475569; margin-top: 2px; }
        .grp { margin-bottom: 18px; page-break-inside: avoid; }
        .grp-head { background: #1e3a8a; color: #fff; padding: 7px 10px; border-radius: 6px 6px 0 0; display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 11.5px; }
        .grp-meta { background: #eff6ff; border: 1px solid #94a3b8; border-top: none; border-bottom: none; padding: 5px 10px; font-size: 10.5px; color: #1e3a8a; display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #94a3b8; padding: 5px 7px; text-align: left; }
        th { background: #f1f5f9; font-size: 10px; text-transform: uppercase; }
        .center { text-align: center; }
        .empty { text-align: center; padding: 36px; color: #64748b; font-size: 13px; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none; padding: 8mm; }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <form method="GET" action="<?= url('kelola-spmb/kartu-observasi/cetak-jadwal') ?>" class="toolbar-filter">
            <span style="font-size:12px;font-weight:700;">Jadwal Sesi:</span>
            <select name="jenjang" onchange="this.form.submit()" class="toolbar-select">
                <option value="">Semua Jenjang</option>
                <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $jOpt): ?>
                    <option value="<?= $jOpt ?>" <?= ($selJenjang ?? '') === $jOpt ? 'selected' : '' ?>><?= $jOpt ?></option>
                <?php endforeach; ?>
            </select>
            <select name="ruangan_id" onchange="this.form.submit()" class="toolbar-select">
                <option value="assigned">Semua Sesi &amp; Ruangan Terjadwal</option>
                <?php foreach (($ruanganList ?? []) as $rg): ?>
                    <option value="<?= (int)$rg['id'] ?>" <?= (string)($selRuangan ?? '') === (string)$rg['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($rg['nama_ruangan']) ?> | <?= htmlspecialchars($rg['sesi_nama']) ?> (<?= date('d/m', strtotime($rg['tanggal'])) ?> <?= htmlspecialchars($rg['jam_mulai']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div style="display:flex;gap:6px;align-items:center;">
            <a href="<?= url('kelola-spmb/kartu-observasi/cetak?' . http_build_query(array_filter(['jenjang' => $selJenjang ?? '', 'ruangan_id' => $selRuangan ?: 'assigned']))) ?>" class="btn btn-card">Lihat Kartu Sesi Ini</a>
            <button type="button" id="btnDlJadwalPdf" class="btn btn-pdf" onclick="downloadJadwalPdf()">Download PDF</button>
            <button type="button" class="btn btn-print" onclick="window.print()">Cetak / Print</button>
        </div>
    </div>

    <div id="jadwalPdfContainer" class="sheet">
        <div class="header">
            <?php if ($logoUrl): ?><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo"><?php endif; ?>
            <div>
                <div class="header-title">Jadwal Peserta Observasi Awal Per Ruangan &amp; Sesi</div>
                <div class="header-sub"><?= htmlspecialchars($activeGroup['nama_group'] ?? 'SPMB SIT Bina Insan Palu') ?> &bull; Dicetak: <?= date('d/m/Y H:i') ?></div>
            </div>
        </div>

        <?php if (empty($jadwalGroups)): ?>
            <div class="empty">Belum ada peserta yang diatur sesi &amp; ruangannya untuk filter ini.</div>
        <?php else: ?>
            <?php foreach ($jadwalGroups as $grp): ?>
                <div class="grp">
                    <div class="grp-head">
                        <span><?= htmlspecialchars($grp['nama_ruangan']) ?> — <?= htmlspecialchars($grp['sesi_nama']) ?></span>
                        <span><?= !empty($grp['tgl_sesi']) ? date('d/m/Y', strtotime($grp['tgl_sesi'])) : '-' ?> | <?= htmlspecialchars($grp['jam_mulai']) ?> - <?= htmlspecialchars($grp['jam_selesai']) ?> WITA</span>
                    </div>
                    <div class="grp-meta">
                        <span>Pengawas / Observer: <strong><?= htmlspecialchars($grp['pengawas'] ?: '-') ?></strong></span>
                        <span>Jumlah Peserta: <strong><?= count($grp['items']) ?> Siswa</strong></span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th class="center" style="width:46px;">No. Urut</th>
                                <th style="width:90px;">No. Peserta</th>
                                <th>Nama Calon Siswa</th>
                                <th class="center" style="width:52px;">Jenjang</th>
                                <th style="width:105px;">No. WA Ortu</th>
                                <th class="center" style="width:80px;">Absen QR</th>
                                <th class="center" style="width:72px;">Paraf</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grp['items'] as $idx => $it):
                                $urut = !empty($it['urutan_sesi']) ? (int)$it['urutan_sesi'] : ($idx + 1);
                            ?>
                                <tr>
                                    <td class="center"><strong><?= str_pad((string)$urut, 2, '0', STR_PAD_LEFT) ?></strong></td>
                                    <td><?= htmlspecialchars($it['nomor_peserta']) ?></td>
                                    <td><strong><?= htmlspecialchars($it['nama_lengkap']) ?></strong></td>
                                    <td class="center"><?= htmlspecialchars($it['jenjang']) ?></td>
                                    <td><?= htmlspecialchars($it['target_phone'] ?: '-') ?></td>
                                    <td class="center" style="color:<?= ($it['status_absen'] === 'Hadir') ? '#15803d' : '#64748b' ?>;font-weight:700;">
                                        <?= htmlspecialchars($it['status_absen']) ?>
                                    </td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
    function downloadJadwalPdf() {
        const btn = document.getElementById('btnDlJadwalPdf');
        const el = document.getElementById('jadwalPdfContainer');
        if (!el || typeof html2pdf === 'undefined') { window.print(); return; }
        if (btn) { btn.disabled = true; btn.textContent = 'Membuat PDF...'; }
        html2pdf().set({
            margin: [8, 8, 8, 8],
            filename: 'Jadwal_Sesi_Observasi_SPMB_<?= date('Ymd_His') ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['avoid-all', 'css'] }
        }).from(el).save().finally(() => {
            if (btn) { btn.disabled = false; btn.textContent = 'Download PDF'; }
        });
    }
    </script>
</body>
</html>
