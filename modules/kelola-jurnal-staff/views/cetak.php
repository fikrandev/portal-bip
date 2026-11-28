<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan &amp; Rekapitulasi Jurnal Staff - Bina Insan Palu</title>
    <?php
        $mode = $mode ?? ($_GET['mode'] ?? 'rekap');
        if (!in_array($mode, ['data', 'rekap', 'matrix', 'lengkap'], true)) {
            $mode = 'rekap';
        }
        $rmData = $rekapMatrixData ?? [];
        $rekapStaffList = $rmData['rekap_staff_list'] ?? [];
        $matrixDates = $rmData['matrix_dates'] ?? [];
        $matrixRows = $rmData['matrix_rows'] ?? [];
        $rmSummary = $rmData['summary'] ?? [];

        $baseParams = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'jabatan'    => $jabatan ?? '',
            'pegawai_id' => $pegawaiId ?? '',
            'status'     => $status ?? '',
            'search'     => $search ?? '',
        ];
        $isLandscape = ($mode === 'matrix');
    ?>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            color: #000;
            background-color: #f1f5f9;
            padding: 20px;
            line-height: 1.35;
        }

        .paper {
            background: #fff;
            max-width: <?= $isLandscape ? '1280px' : '960px' ?>;
            margin: 0 auto;
            padding: 28px 36px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
        }

        /* Toolbar Non-Print */
        .print-btn-bar {
            max-width: <?= $isLandscape ? '1280px' : '960px' ?>;
            margin: 0 auto 14px auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            background: #1e293b;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 12px;
        }

        .mode-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .mode-link {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            color: #cbd5e1;
            background: rgba(255,255,255,0.08);
            font-weight: 600;
            font-size: 12px;
        }

        .mode-link.active {
            background: #2563eb;
            color: #fff;
        }

        .btn {
            padding: 7px 15px;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 6px;
            border: none;
            text-decoration: none;
        }

        .btn-print {
            background-color: #10b981;
            color: #fff;
        }

        .btn-close {
            background-color: #475569;
            color: #fff;
        }

        /* Kop Surat Formal */
        .header-kop {
            text-align: center;
            border-bottom: 2.5px double #000;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .header-kop h2 {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-kop h3 {
            font-size: 12.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .header-kop p {
            font-size: 9.5pt;
            color: #222;
            margin-top: 3px;
        }

        /* Judul Laporan */
        .title-section {
            text-align: center;
            margin-bottom: 14px;
        }

        .title-section h1 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
        }

        .title-section p {
            font-size: 10pt;
            margin-top: 3px;
        }

        /* Meta Filter Box */
        .meta-box {
            display: flex;
            justify-content: space-between;
            font-size: 9.5pt;
            margin-bottom: 14px;
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        .section-heading {
            font-size: 10.5pt;
            font-weight: bold;
            margin: 14px 0 6px 0;
            text-transform: uppercase;
        }

        /* Tabel Laporan */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 18px;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: middle;
        }

        table.report-table th {
            background-color: #e2e8f0;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8.5pt;
        }

        table.matrix-table th,
        table.matrix-table td {
            padding: 3px 4px;
            font-size: 8pt;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .badge-status {
            display: inline-block;
            padding: 1px 5px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
            border: 1px solid #666;
        }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 240px;
            text-align: center;
            font-size: 10pt;
        }

        .signature-space {
            height: 60px;
        }

        @page {
            size: <?= $isLandscape ? 'A4 landscape' : 'A4 portrait' ?>;
            margin: 12mm;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .paper {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .print-btn-bar {
                display: none !important;
            }
            table.report-table th {
                background-color: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Non-Print -->
    <div class="print-btn-bar">
        <div class="mode-tabs">
            <a href="<?= url('kelola-jurnal-staff/cetak?' . http_build_query(array_merge($baseParams, ['mode' => 'rekap']))) ?>"
               class="mode-link <?= $mode === 'rekap' ? 'active' : '' ?>">
                📊 Rekap Persentase
            </a>
            <a href="<?= url('kelola-jurnal-staff/cetak?' . http_build_query(array_merge($baseParams, ['mode' => 'matrix']))) ?>"
               class="mode-link <?= $mode === 'matrix' ? 'active' : '' ?>">
                🗓️ Matrix Ceklis (✓/✕)
            </a>
            <a href="<?= url('kelola-jurnal-staff/cetak?' . http_build_query(array_merge($baseParams, ['mode' => 'data']))) ?>"
               class="mode-link <?= $mode === 'data' ? 'active' : '' ?>">
                📋 Data Jurnal
            </a>
            <a href="<?= url('kelola-jurnal-staff/cetak?' . http_build_query(array_merge($baseParams, ['mode' => 'lengkap']))) ?>"
               class="mode-link <?= $mode === 'lengkap' ? 'active' : '' ?>">
                📑 Lengkap (Rekap + Data)
            </a>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-print">🖨️ Cetak / Simpan PDF</button>
            <button onclick="window.close()" class="btn btn-close">Tutup</button>
        </div>
    </div>

    <div class="paper">
        <!-- Kop Surat -->
        <div class="header-kop">
            <h2>YAYASAN BINA INSAN PALU</h2>
            <h3>LAPORAN MONITORING AKTIVITAS &amp; JURNAL KERJA STAFF</h3>
            <p>Jl. Banteng No. 12, Kel. Birobuli Selatan, Kec. Palu Selatan, Kota Palu, Sulawesi Tengah</p>
        </div>

        <!-- Judul Laporan -->
        <div class="title-section">
            <?php if ($mode === 'rekap'): ?>
                <h1>REKAPITULASI &amp; PERSENTASE KETERISIAN JURNAL KERJA STAFF</h1>
            <?php elseif ($mode === 'matrix'): ?>
                <h1>MATRIX CEKLIS HARIAN PENGISIAN JURNAL KERJA STAFF (✓ / ✕)</h1>
            <?php elseif ($mode === 'data'): ?>
                <h1>DAFTAR RINCIAN CATATAN JURNAL KERJA STAFF</h1>
            <?php else: ?>
                <h1>LAPORAN LENGKAP REKAPITULASI &amp; RINCIAN JURNAL KERJA STAFF</h1>
            <?php endif; ?>
            <p>Periode: <strong><?= date('d F Y', strtotime($startDate)) ?></strong> s/d <strong><?= date('d F Y', strtotime($endDate)) ?></strong></p>
        </div>

        <!-- Meta Info -->
        <div class="meta-box">
            <div>
                <strong>Jabatan / Divisi:</strong> <?= htmlspecialchars($jabatan ?: 'Semua Divisi / Unit') ?><br>
                <strong>Hari Kerja Efektif Periode:</strong> <?= (int)($rmSummary['hari_kerja_elapsed'] ?? 0) ?> Hari Kerja (Sen-Jum: <?= (int)($rmSummary['sesi_per_hari'] ?? 8) ?> Sesi &bull; Sabtu: <?= (int)($rmSummary['sesi_per_hari_sabtu'] ?? 5) ?> Sesi s/d 12.00)
            </div>
            <div style="text-align: right;">
                <strong>Total Staff:</strong> <?= (int)($rmSummary['total_staff'] ?? count($rekapStaffList)) ?> Orang &bull;
                <strong>Rata-rata Keterisian:</strong> <?= number_format((float)($rmSummary['avg_persentase'] ?? 0), 1) ?>%<br>
                <strong>Total Sesi Terisi:</strong> <?= number_format((int)($rmSummary['total_terisi_sesi'] ?? 0)) ?> / <?= number_format((int)($rmSummary['total_target_sesi'] ?? 0)) ?> Sesi (<?= count($jurnalList) ?> Catatan)
            </div>
        </div>

        <!-- =============================================================== -->
        <!-- BAGIAN 1: TABEL REKAPITULASI PERSENTASE (mode = rekap | lengkap) -->
        <!-- =============================================================== -->
        <?php if ($mode === 'rekap' || $mode === 'lengkap'): ?>
            <?php if ($mode === 'lengkap'): ?>
                <div class="section-heading">I. Rekapitulasi Persentase Keterisian Jurnal per Staff</div>
            <?php endif; ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 32px;">No</th>
                        <th>Nama Staff</th>
                        <th style="width: 90px;">NIY</th>
                        <th>Jabatan / Divisi</th>
                        <th style="width: 68px;">Hari Kerja</th>
                        <th style="width: 68px;">Hari Terisi (✓)</th>
                        <th style="width: 68px;">Hari Kosong (✕)</th>
                        <th style="width: 85px;">Sesi Terisi / Target</th>
                        <th style="width: 68px;">Sesi Kosong</th>
                        <th style="width: 80px;">Persentase (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rekapStaffList)): ?>
                        <tr>
                            <td colspan="10" class="text-center" style="padding: 20px; color: #666;">
                                Tidak ada data staff pada kriteria filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekapStaffList as $idx => $rs): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><strong><?= htmlspecialchars($rs['nama']) ?></strong></td>
                                <td class="text-center"><?= htmlspecialchars($rs['niy'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($rs['jabatan']) ?></td>
                                <td class="text-center">
                                    <?= (int)$rs['hari_kerja_efektif'] ?>
                                    <?php if ($rs['hari_cuti_izin'] > 0): ?>
                                        <br><span style="font-size: 7.5pt; color: #555;">(Izin/Cuti: <?= (int)$rs['hari_cuti_izin'] ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center font-bold"><?= (int)$rs['hari_terisi'] ?></td>
                                <td class="text-center"><?= (int)$rs['hari_kosong'] ?></td>
                                <td class="text-center font-bold"><?= (int)$rs['terisi_sesi'] ?> / <?= (int)$rs['target_sesi'] ?></td>
                                <td class="text-center"><?= (int)$rs['kosong_sesi'] ?></td>
                                <td class="text-center font-bold"><?= number_format((float)$rs['persentase'], 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background-color: #f8fafc; font-weight: bold;">
                            <td colspan="7" class="text-right">TOTAL / RATA-RATA KESELURUHAN</td>
                            <td class="text-center"><?= number_format((int)($rmSummary['total_terisi_sesi'] ?? 0)) ?> / <?= number_format((int)($rmSummary['total_target_sesi'] ?? 0)) ?></td>
                            <td class="text-center"><?= number_format((int)($rmSummary['total_kosong_sesi'] ?? 0)) ?></td>
                            <td class="text-center"><?= number_format((float)($rmSummary['avg_persentase'] ?? 0), 1) ?>%</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- =============================================================== -->
        <!-- BAGIAN 2: MATRIX CEKLIS HARIAN (mode = matrix)                  -->
        <!-- =============================================================== -->
        <?php if ($mode === 'matrix'): ?>
            <table class="report-table matrix-table">
                <thead>
                    <tr>
                        <th style="width: 26px;">No</th>
                        <th style="min-width: 150px;">Nama Staff &amp; Jabatan</th>
                        <?php foreach ($matrixDates as $md): ?>
                            <th style="width: 24px; <?= ($md['is_sunday'] || $md['is_holiday']) ? 'background-color: #fee2e2;' : '' ?>">
                                <?= htmlspecialchars($md['day_num']) ?><br>
                                <span style="font-size: 6.5pt; font-weight: normal;"><?= htmlspecialchars($md['hari_short']) ?></span>
                            </th>
                        <?php endforeach; ?>
                        <th style="width: 38px;">Hari ✓</th>
                        <th style="width: 38px;">Hari ✕</th>
                        <th style="width: 55px;">Sesi ✓</th>
                        <th style="width: 45px;">%</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($matrixRows)): ?>
                        <tr>
                            <td colspan="<?= count($matrixDates) + 6 ?>" class="text-center" style="padding: 20px;">
                                Tidak ada data matrix pada periode ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($matrixRows as $mIdx => $mRow): ?>
                            <tr>
                                <td class="text-center"><?= $mIdx + 1 ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($mRow['nama']) ?></strong><br>
                                    <span style="font-size: 7.5pt; color: #444;"><?= htmlspecialchars($mRow['jabatan']) ?></span>
                                </td>
                                <?php foreach ($matrixDates as $md): ?>
                                    <?php
                                        $dStr = $md['date'];
                                        $cell = $mRow['dates'][$dStr] ?? ['status' => 'holiday', 'filled_count' => 0];
                                        $st = $cell['status'];
                                    ?>
                                    <td class="text-center" style="<?= ($md['is_sunday'] || $md['is_holiday']) ? 'background-color: #f8fafc; color: #94a3b8;' : '' ?>">
                                        <?php if ($st === 'filled'): ?>
                                            <strong>✓</strong>
                                        <?php elseif ($st === 'partial'): ?>
                                            <strong>✓<?= (int)$cell['filled_count'] ?></strong>
                                        <?php elseif ($st === 'unfilled'): ?>
                                            <span style="color: #dc2626; font-weight: bold;">✕</span>
                                        <?php elseif ($st === 'excused'): ?>
                                            <span style="font-weight: bold;">C</span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-center font-bold"><?= (int)$mRow['hari_terisi'] ?></td>
                                <td class="text-center"><?= (int)$mRow['hari_kosong'] ?></td>
                                <td class="text-center"><?= (int)$mRow['terisi_sesi'] ?>/<?= (int)$mRow['target_sesi'] ?></td>
                                <td class="text-center font-bold"><?= number_format((float)$mRow['persentase'], 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <p style="font-size: 8.5pt; color: #333; margin-bottom: 12px;">
                <strong>Keterangan Simbol:</strong>
                <strong>✓</strong> = Terisi Lengkap (<?= (int)($rmSummary['sesi_per_hari'] ?? 8) ?> Sesi) &bull;
                <strong>✓N</strong> = Terisi Sebagian (N Sesi) &bull;
                <strong>✕</strong> = Tidak Terisi (0 Sesi) &bull;
                <strong>C</strong> = Cuti / Izin / Sakit / Dinas Luar &bull;
                <strong>-</strong> = Hari Minggu / Libur Nasional &amp; Sekolah / Belum Berlangsung
            </p>
        <?php endif; ?>

        <!-- =============================================================== -->
        <!-- BAGIAN 3: RINCIAN DATA JURNAL (mode = data | lengkap)           -->
        <!-- =============================================================== -->
        <?php if ($mode === 'data' || $mode === 'lengkap'): ?>
            <?php if ($mode === 'lengkap'): ?>
                <div class="section-heading">II. Rincian Catatan Jurnal Kerja Staff</div>
            <?php endif; ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">No</th>
                        <th style="width: 82px;">Tanggal</th>
                        <th style="width: 82px;">Sesi Jam</th>
                        <th style="width: 150px;">Nama Staff &amp; Jabatan</th>
                        <th>Aktivitas &amp; Job Desk</th>
                        <th>Uraian Hasil Kerja</th>
                        <th style="width: 72px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jurnalList)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 22px; color: #666;">
                                Tidak ada data jurnal kerja staff pada periode dan kriteria ini.
                            </td>
                        </tr>
                    <?php else:
                        $no = 1;
                        foreach ($jurnalList as $row):
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                            <td class="text-center" style="font-family: monospace; font-size: 8.5pt;">
                                <?= htmlspecialchars($row['jam_rentang']) ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($row['nama_staff']) ?></strong><br>
                                <span style="font-size: 8pt; color: #444;"><?= htmlspecialchars($row['jabatan']) ?></span>
                            </td>
                            <td>
                                <?php
                                    $aktParts = JurnalStaffModel::splitAktivitasList($row['aktivitas'] ?? '');
                                    if (count($aktParts) > 1):
                                ?>
                                    <div style="margin-bottom: 3px;">
                                        <?php foreach ($aktParts as $aIdx => $aItem): ?>
                                            <div style="margin-bottom: 2px;"><strong><?= ($aIdx + 1) ?>.</strong> <?= htmlspecialchars($aItem) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($row['aktivitas']) ?></strong><br>
                                <?php endif; ?>
                                <span style="font-size: 7.5pt; color: #666; font-family: monospace;"><?= htmlspecialchars($row['kode_jurnal']) ?></span>
                            </td>
                            <td>
                                <?= nl2br(htmlspecialchars($row['uraian_hasil'])) ?>
                                <?php if (!empty($row['kendala'])): ?>
                                    <div style="margin-top: 4px; font-size: 8pt; color: #854d0e;">
                                        <em>Kendala: <?= htmlspecialchars($row['kendala']) ?></em>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge-status">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <div class="signature-box">
                <p>Mengetahui,</p>
                <p><strong>Kepala Sekolah / Manajer Terkait</strong></p>
                <div class="signature-space"></div>
                <p>_____________________________</p>
                <p style="font-size: 9pt;">NIP / NIY.</p>
            </div>

            <div class="signature-box">
                <p>Palu, <?= date('d F Y') ?></p>
                <p><strong>Koordinator Administrasi &amp; HRD</strong></p>
                <div class="signature-space"></div>
                <p>_____________________________</p>
                <p style="font-size: 9pt;">NIP / NIY.</p>
            </div>
        </div>
    </div>

</body>
</html>
