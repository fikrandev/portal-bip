<?php
/**
 * View Cetak & Download PDF Hasil Observasi Siswa (Per Siswa, Semua Per Siswa, atau Rekap Unit)
 * Portal BIP - Modul Kelola SPMB
 */
$mode = $mode ?? 'single';
$jenjang = $jenjang ?? 'SD';
$records = $records ?? [];
$observedItems = $observedItems ?? [];
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
    <title>Hasil Observasi Siswa — Unit <?= htmlspecialchars($jenjang) ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #0f172a; background: #f1f5f9; padding: 16px; line-height: 1.45; }
        .toolbar { max-width: 210mm; margin: 0 auto 14px auto; display: flex; justify-content: space-between; align-items: center; background: #0f172a; color: #fff; padding: 10px 16px; border-radius: 10px; }
        .btn { padding: 7px 14px; border: none; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .btn-pdf { background: #059669; color: #fff; }
        .btn-print { background: #2563eb; color: #fff; }
        .sheet { max-width: 210mm; margin: 0 auto 16px auto; background: #fff; padding: 14mm 15mm; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.06); page-break-after: always; }
        .sheet:last-child { page-break-after: auto; }
        .kop { display: flex; align-items: center; gap: 12px; border-bottom: 3px double #0f172a; padding-bottom: 8px; margin-bottom: 14px; }
        .kop img { width: 48px; height: 48px; object-fit: contain; }
        .kop-title { font-size: 14px; font-weight: 800; text-transform: uppercase; }
        .kop-sub { font-size: 10.5px; color: #475569; }
        .meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px 16px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #94a3b8; padding: 5px 7px; text-align: left; vertical-align: top; }
        th { background: #e2e8f0; font-size: 10px; text-transform: uppercase; }
        .center { text-align: center; }
        .kesimpulan-box { background: #eff6ff; border: 1px solid #93c5fd; border-radius: 8px; padding: 10px 12px; margin-bottom: 12px; }
        .kesimpulan-title { font-weight: 800; color: #1e3a8a; font-size: 11.5px; margin-bottom: 4px; text-transform: uppercase; }
        .ttd-row { display: flex; justify-content: space-between; margin-top: 24px; }
        .ttd-box { width: 200px; text-align: center; }
        .ttd-space { height: 52px; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { box-shadow: none; margin: 0; padding: 8mm; }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <div style="font-size:13px;font-weight:700;">
            <?= $mode === 'rekap' ? 'Rekap Hasil Observasi Siswa — Unit ' . htmlspecialchars($jenjang) : 'Lembar Hasil Observasi Siswa — Unit ' . htmlspecialchars($jenjang) ?>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="button" id="btnDlObsPdf" class="btn btn-pdf" onclick="downloadObsPdf()">Download PDF</button>
            <button type="button" class="btn btn-print" onclick="window.print()">Cetak / Print</button>
        </div>
    </div>

    <div id="obsPdfRoot">
        <?php if ($mode === 'rekap'): ?>
            <div class="sheet">
                <div class="kop">
                    <?php if ($logoUrl): ?><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo"><?php endif; ?>
                    <div>
                        <div class="kop-title">Rekapitulasi Hasil Observasi Calon Siswa — Unit <?= htmlspecialchars($jenjang) ?></div>
                        <div class="kop-sub"><?= htmlspecialchars($activeGroup['nama_group'] ?? 'SPMB SIT Bina Insan Palu') ?> &bull; Dicetak: <?= date('d/m/Y H:i') ?></div>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="center" style="width:28px;">No</th>
                            <th style="width:85px;">No. Daftar</th>
                            <th style="width:140px;">Nama Calon Siswa</th>
                            <th class="center" style="width:72px;">Total Nilai</th>
                            <th class="center" style="width:105px;">Kategori</th>
                            <th>Kesimpulan Sistem</th>
                            <th style="width:85px;">Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($observedItems)): ?>
                            <tr><td colspan="7" class="center" style="padding:20px;color:#64748b;">Belum ada siswa yang selesai diobservasi pada unit ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($observedItems as $i => $it):
                                $terjawabIt = (int)($it['jumlah_terjawab'] ?? 0);
                                $totalSoalIt = (int)($it['total_soal_unit'] ?? 0);
                                $isFullIt = ($totalSoalIt === 0 || $terjawabIt >= $totalSoalIt);
                            ?>
                                <tr>
                                    <td class="center"><?= $i + 1 ?></td>
                                    <td><?= htmlspecialchars($it['nomor_pendaftaran']) ?></td>
                                    <td><strong><?= htmlspecialchars($it['nama_lengkap']) ?></strong></td>
                                    <td class="center">
                                        <strong><?= rtrim(rtrim(number_format((float)$it['total_nilai'], 2, '.', ''), '0'), '.') ?>/<?= rtrim(rtrim(number_format((float)$it['nilai_maksimal'], 2, '.', ''), '0'), '.') ?></strong><br>
                                        (<?= number_format((float)$it['persentase'], 1) ?>%)
                                        <?php if (!empty($it['detail_nilai'])): ?>
                                            <div style="font-size:9px;color:#334155;margin-top:3px;">
                                                <?= htmlspecialchars(implode(', ', array_map(fn($dn) => $dn['aspek'] . ': ' . rtrim(rtrim(number_format((float)$dn['nilai'], 2, '.', ''), '0'), '.') . '/' . rtrim(rtrim(number_format((float)$dn['maks'], 2, '.', ''), '0'), '.'), $it['detail_nilai']))) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="center">
                                        <strong><?= $isFullIt ? htmlspecialchars($it['kategori_hasil'] ?: '-') : ('Belum Lengkap (' . $terjawabIt . '/' . $totalSoalIt . ')') ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($isFullIt && !empty($it['kesimpulan_sistem'])): ?>
                                            <?= htmlspecialchars($it['kesimpulan_sistem']) ?>
                                        <?php else: ?>
                                            <span style="color:#94a3b8;font-style:italic;">Penilaian belum selesai (<?= $terjawabIt ?>/<?= $totalSoalIt ?> soal).</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($it['petugas_nama'] ?: '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?php if (empty($records)): ?>
                <div class="sheet" style="text-align:center;padding:40px;color:#64748b;">Belum ada hasil observasi siswa untuk dicetak.</div>
            <?php else: ?>
                <?php foreach ($records as $rec):
                    $p = $rec['pendaftar'];
                    $h = $rec['hasil'];
                    $jList = $rec['jawaban_list'] ?? [];
                    $catatanAspekMap = $rec['catatan_aspek_map'] ?? [];
                ?>
                    <div class="sheet">
                        <div class="kop">
                            <?php if ($logoUrl): ?><img src="<?= htmlspecialchars($logoUrl) ?>" alt="Logo"><?php endif; ?>
                            <div>
                                <div class="kop-title">Laporan Hasil Observasi Calon Siswa — Unit <?= htmlspecialchars($p['jenjang']) ?></div>
                                <div class="kop-sub"><?= htmlspecialchars($p['nama_group'] ?? ($activeGroup['nama_group'] ?? 'SPMB SIT Bina Insan Palu')) ?></div>
                            </div>
                        </div>

                        <div class="meta-grid">
                            <div><strong>Nama Lengkap:</strong> <?= htmlspecialchars($p['nama_lengkap']) ?></div>
                            <div><strong>No. Pendaftaran:</strong> <?= htmlspecialchars($p['nomor_pendaftaran']) ?></div>
                            <div><strong>Unit / Jenjang:</strong> <?= htmlspecialchars($p['jenjang']) ?></div>
                            <div><strong>Asal Sekolah:</strong> <?= htmlspecialchars($p['asal_sekolah'] ?: '-') ?></div>
                            <div><strong>Petugas Observer:</strong> <?= htmlspecialchars($h['petugas_nama'] ?? '-') ?></div>
                            <div><strong>Tanggal Observasi:</strong> <?= !empty($h['observed_at']) ? date('d/m/Y H:i', strtotime($h['observed_at'])) : '-' ?></div>
                        </div>

                        <?php
                        $jByAspek = [];
                        foreach ($jList as $jwItem) {
                            $aKey = trim((string)($jwItem['aspek_snapshot'] ?? 'Umum')) ?: 'Umum';
                            $jByAspek[$aKey][] = $jwItem;
                        }
                        ?>
                        <table>
                            <thead>
                                <tr>
                                    <th class="center" style="width:28px;">No</th>
                                    <th>Pertanyaan / Indikator</th>
                                    <th style="width:120px;">Jawaban</th>
                                    <th class="center" style="width:55px;">Nilai</th>
                                    <th style="width:130px;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $aNum = 0; foreach ($jByAspek as $aName => $aRows):
                                    $aNum++;
                                    $aVal = array_sum(array_map(fn($x) => (float)$x['nilai'], $aRows));
                                    $aMax = array_sum(array_map(fn($x) => (float)$x['nilai_maksimal'], $aRows));
                                ?>
                                    <tr style="background:#eff6ff;font-weight:700;color:#1e3a8a;">
                                        <td class="center"><?= chr(64 + $aNum) ?></td>
                                        <td colspan="2">ASPEK: <?= htmlspecialchars(strtoupper($aName)) ?> (<?= count($aRows) ?> Pertanyaan)</td>
                                        <td class="center"><?= rtrim(rtrim(number_format($aVal, 2, '.', ''), '0'), '.') ?>/<?= rtrim(rtrim(number_format($aMax, 2, '.', ''), '0'), '.') ?></td>
                                        <td class="center"><?= $aMax > 0 ? number_format(($aVal / $aMax) * 100, 0) . '%' : '-' ?></td>
                                    </tr>
                                    <?php foreach ($aRows as $idx => $jw): ?>
                                        <tr>
                                            <td class="center"><?= $idx + 1 ?></td>
                                            <td><?= htmlspecialchars($jw['pertanyaan_snapshot']) ?></td>
                                            <td><strong><?= htmlspecialchars($jw['jawaban_dipilih']) ?></strong></td>
                                            <td class="center"><strong><?= rtrim(rtrim(number_format((float)$jw['nilai'], 2, '.', ''), '0'), '.') ?></strong></td>
                                            <td><?= htmlspecialchars($jw['keterangan'] ?: '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (!empty($catatanAspekMap[$aName])): ?>
                                        <tr style="background:#f8fafc;">
                                            <td colspan="5" style="font-size:10px;color:#334155;">
                                                <strong>Catatan Petugas (Aspek <?= htmlspecialchars($aName) ?>):</strong> <?= htmlspecialchars($catatanAspekMap[$aName]) ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <?php
                                $totalSoalRec = count($rec['soal_list'] ?? []);
                                $activeSoalRecIds = array_map(fn($s) => (int)$s['id'], $rec['soal_list'] ?? []);
                                $answeredRecCount = 0;
                                foreach ($jList as $jItemCheck) {
                                    if (empty($activeSoalRecIds) || in_array((int)$jItemCheck['soal_id'], $activeSoalRecIds, true)) {
                                        $answeredRecCount++;
                                    }
                                }
                                $isRecComplete = ($totalSoalRec === 0 || $answeredRecCount >= $totalSoalRec);
                                ?>
                                <tr style="background:#f1f5f9;font-weight:700;">
                                    <td colspan="3" style="text-align:right;">TOTAL NILAI / PERSENTASE</td>
                                    <td class="center"><?= rtrim(rtrim(number_format((float)($h['total_nilai'] ?? 0), 2, '.', ''), '0'), '.') ?> / <?= rtrim(rtrim(number_format((float)($h['nilai_maksimal'] ?? 0), 2, '.', ''), '0'), '.') ?></td>
                                    <td class="center">
                                        <?= number_format((float)($h['persentase'] ?? 0), 1) ?>%
                                        (<?= $isRecComplete ? htmlspecialchars($h['kategori_hasil'] ?? '-') : ('Belum Lengkap: ' . $answeredRecCount . '/' . $totalSoalRec) ?>)
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        <?php if ($isRecComplete && !empty($h['kesimpulan_sistem'])): ?>
                            <div class="kesimpulan-box">
                                <div class="kesimpulan-title">Kesimpulan Sistem (Otomatis)</div>
                                <div><?= htmlspecialchars($h['kesimpulan_sistem']) ?></div>
                                <?php if (!empty($h['catatan_petugas'])): ?>
                                    <div style="margin-top:6px;padding-top:6px;border-top:1px dashed #93c5fd;">
                                        <strong>Catatan Petugas:</strong> <?= htmlspecialchars($h['catatan_petugas']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php elseif (!empty($h['catatan_petugas'])): ?>
                            <div class="kesimpulan-box">
                                <div class="kesimpulan-title">Catatan Petugas</div>
                                <div><?= htmlspecialchars($h['catatan_petugas']) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="ttd-row">
                            <div class="ttd-box">
                                <div>Mengetahui,<br>Panitia SPMB Unit <?= htmlspecialchars($p['jenjang']) ?></div>
                                <div class="ttd-space"></div>
                                <div style="font-weight:700;">( ........................................ )</div>
                            </div>
                            <div class="ttd-box">
                                <div>Palu, <?= !empty($h['observed_at']) ? date('d/m/Y', strtotime($h['observed_at'])) : date('d/m/Y') ?><br>Petugas Observer</div>
                                <div class="ttd-space"></div>
                                <div style="font-weight:700;text-decoration:underline;"><?= htmlspecialchars($h['petugas_nama'] ?? '........................................') ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
    function downloadObsPdf() {
        const btn = document.getElementById('btnDlObsPdf');
        const el = document.getElementById('obsPdfRoot');
        if (!el || typeof html2pdf === 'undefined') { window.print(); return; }
        if (btn) { btn.disabled = true; btn.textContent = 'Membuat PDF...'; }
        html2pdf().set({
            margin: [6, 6, 6, 6],
            filename: 'Hasil_Observasi_Siswa_<?= preg_replace('/[^A-Za-z0-9]/', '_', $jenjang) ?>_<?= date('Ymd_His') ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] }
        }).from(el).save().finally(() => {
            if (btn) { btn.disabled = false; btn.textContent = 'Download PDF'; }
        });
    }
    </script>
</body>
</html>
