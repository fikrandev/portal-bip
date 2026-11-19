<?php
/**
 * View Cetak PDF / Cetak Laporan Rekapitulasi Presensi & Kehadiran Pegawai
 * Portal BIP - SIT Bina Insan Palu
 * 
 * Sesuai kebutuhan resmi:
 * Kolom: No, Nama Pegawai, NIY, Unit / Jabatan, Jumlah Hari Kerja, Jumlah Hadir, 
 *        Hadir Tanpa Terlambat, Terlambat, Izin, Sakit, Cuti, Alpa, Total Kehadiran (%)
 */

$rekapRows = $rekapPeriodeData['rows'] ?? [];
$summary = $rekapPeriodeData['summary'] ?? [];
$startDate = $rekapPeriodeData['start_date'] ?? date('Y-m-01');
$endDate = $rekapPeriodeData['end_date'] ?? date('Y-m-d');
$hariKerja = (int)($rekapPeriodeData['jumlah_hari_kerja'] ?? 1);
$unitNama = !empty($targetUnit) ? $targetUnit : (!empty($rekapPeriodeData['target_unit']) ? $rekapPeriodeData['target_unit'] : 'Seluruh Unit');

// Format tanggal Indonesia
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

function formatTglIndo(string $ymd, array $bulanIndo): string {
    $ts = strtotime($ymd);
    if (!$ts) return $ymd;
    $d = date('j', $ts);
    $m = (int)date('n', $ts);
    $y = date('Y', $ts);
    return "{$d} " . ($bulanIndo[$m] ?? '') . " {$y}";
}

$periodeFmt = formatTglIndo($startDate, $bulanIndo) . ' s/d ' . formatTglIndo($endDate, $bulanIndo);
$cetakFmt   = formatTglIndo(date('Y-m-d'), $bulanIndo);

// Logo
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Presensi Pegawai — <?= e($unitNama) ?> (<?= date('d-m-Y', strtotime($startDate)) ?> sd <?= date('d-m-Y', strtotime($endDate)) ?>)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #f8fafc;
            line-height: 1.4;
            padding: 24px;
        }
        .container {
            max-width: 1120px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 36px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }

        /* Toolbar Screen Only */
        .toolbar {
            max-width: 1120px;
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 18px;
            background: #0f172a;
            color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);
        }
        .toolbar-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        .toolbar-actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.15s ease;
        }
        .btn-print {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }
        .btn-close {
            background: rgba(255, 255, 255, 0.12);
            color: #f1f5f9;
        }
        .btn-close:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
        }

        /* Kop Surat */
        .kop-wrapper {
            display: flex;
            align-items: center;
            gap: 18px;
            padding-bottom: 12px;
            border-bottom: 3px double #0f172a;
            margin-bottom: 20px;
        }
        .kop-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-logo-placeholder {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            background: #2563eb;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            flex-shrink: 0;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-yayasan {
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .kop-lembaga {
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }
        .kop-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }

        /* Header Laporan */
        .report-header {
            text-align: center;
            margin-bottom: 22px;
        }
        .report-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-meta {
            margin-top: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 11px;
            color: #475569;
        }
        .report-meta strong {
            color: #0f172a;
        }

        /* Summary Badges */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-bottom: 18px;
        }
        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
            text-align: center;
        }
        .summary-label {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 2px;
        }
        .summary-value.highlight {
            color: #2563eb;
        }

        /* Table */
        table.rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        table.rekap-table th, 
        table.rekap-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: middle;
        }
        table.rekap-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        table.rekap-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        table.rekap-table td.text-center {
            text-align: center;
        }
        table.rekap-table td.text-right {
            text-align: right;
        }
        table.rekap-table tfoot th {
            background: #e2e8f0;
            font-weight: 800;
            font-size: 10.5px;
        }
        .pegawai-nama {
            font-weight: 700;
            color: #0f172a;
        }
        .pegawai-sub {
            font-size: 9.5px;
            color: #64748b;
        }
        .badge-pct {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 800;
            font-size: 10px;
        }
        .badge-pct-high {
            background: #dcfce7;
            color: #15803d;
        }
        .badge-pct-mid {
            background: #e0f2fe;
            color: #0369a1;
        }
        .badge-pct-low {
            background: #ffe4e6;
            color: #be123c;
        }

        /* Signatures */
        .signature-area {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sig-block {
            text-align: center;
            width: 240px;
        }
        .sig-title {
            font-size: 11px;
            color: #475569;
            margin-bottom: 60px;
        }
        .sig-name {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            padding-bottom: 2px;
            min-width: 180px;
        }
        .sig-nip {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }

        /* Print Settings */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                font-size: 10px !important;
            }
            .container {
                max-width: 100% !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 1.2cm 1cm 1.2cm 1cm;
            }
            table.rekap-table {
                font-size: 9.5px !important;
            }
            table.rekap-table th, table.rekap-table td {
                padding: 4px 6px !important;
            }
            .summary-card {
                padding: 4px 6px !important;
            }
            .signature-area {
                margin-top: 28px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Layar (Tidak Muncul Saat Dicetak) -->
    <div class="toolbar no-print">
        <div class="toolbar-info">
            <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.144-1.28-3.093l-.408-.637c-.368-.574-.532-.83-.532-1.127 0-.482.352-.892.83-.974.776-.133 1.57-.213 2.378-.238m10.584 6.069c.24-1.076.672-2.144 1.28-3.093l.408-.637c.368-.574.532-.83.532-1.127 0-.482-.352-.892-.83-.974a24.28 24.28 0 0 0-2.378-.238M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v9.75a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25" />
            </svg>
            <span>Pratinjau Cetak / PDF Laporan Rekap Presensi</span>
        </div>
        <div class="toolbar-actions">
            <button type="button" onclick="window.print()" class="btn btn-print">
                <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.144-1.28-3.093l-.408-.637c-.368-.574-.532-.83-.532-1.127 0-.482.352-.892.83-.974.776-.133 1.57-.213 2.378-.238m10.584 6.069c.24-1.076.672-2.144 1.28-3.093l.408-.637c.368-.574.532-.83.532-1.127 0-.482-.352-.892-.83-.974a24.28 24.28 0 0 0-2.378-.238M6.75 6.75h10.5a2.25 2.25 0 0 1 2.25 2.25v9.75a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V9a2.25 2.25 0 0 1 2.25-2.25Zm0 0V4.5a2.25 2.25 0 0 1 2.25-2.25h6a2.25 2.25 0 0 1 2.25 2.25v2.25" />
                </svg>
                <span>Cetak / Simpan PDF (Ctrl+P)</span>
            </button>
            <button type="button" onclick="window.close(); if(!window.closed){ window.history.back(); }" class="btn btn-close">
                <span>✕ Tutup</span>
            </button>
        </div>
    </div>

    <!-- Lembar Dokumen Resmi -->
    <div class="container">
        
        <!-- KOP SURAT RESMI SIT BINA INSAN PALU -->
        <div class="kop-wrapper">
            <?php if ($logoUrl): ?>
                <img src="<?= e($logoUrl) ?>" alt="Logo SIT Bina Insan" class="kop-logo">
            <?php else: ?>
                <div class="kop-logo-placeholder">BI</div>
            <?php endif; ?>
            <div class="kop-text">
                <div class="kop-yayasan">Yayasan Bina Insan Palu — Sulawesi Tengah</div>
                <div class="kop-lembaga">SEKOLAH ISLAM TERPADU BINA INSAN PALU</div>
                <div class="kop-sub">Kampus Terpadu: Jl. Banteng No. 1, Tatura Selatan, Palu Selatan, Kota Palu | Telp: (0451) 481-999 | Email: info@binainsanpalu.sch.id</div>
            </div>
        </div>

        <!-- JUDUL LAPORAN -->
        <div class="report-header">
            <h1 class="report-title">REKAPITULASI PRESENSI & KEHADIRAN PEGAWAI</h1>
            <div class="report-meta">
                <span>Unit Tugas: <strong><?= e($unitNama) ?></strong></span>
                <span>Periode: <strong><?= e($periodeFmt) ?></strong></span>
                <span>Jumlah Hari Kerja: <strong><?= $hariKerja ?> Hari</strong></span>
                <span>Dicetak Pada: <strong><?= e($cetakFmt) ?></strong></span>
            </div>
        </div>

        <!-- KPI SUMMARY BAR -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total Pegawai</div>
                <div class="summary-value"><?= number_format($summary['total_pegawai'] ?? count($rekapRows)) ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Rata-rata Hadir</div>
                <div class="summary-value highlight"><?= number_format($summary['rata_rata_kehadiran'] ?? 0, 1) ?>%</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Tepat Waktu</div>
                <div class="summary-value" style="color: #16a34a;"><?= number_format($summary['total_tepat_waktu_semua'] ?? 0) ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Terlambat</div>
                <div class="summary-value" style="color: #d97706;"><?= number_format($summary['total_terlambat_semua'] ?? 0) ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Izin / Sakit / Cuti</div>
                <div class="summary-value" style="color: #0284c7;"><?= number_format(($summary['total_izin_semua'] ?? 0) + ($summary['total_sakit_semua'] ?? 0) + ($summary['total_cuti_semua'] ?? 0)) ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Alpa</div>
                <div class="summary-value" style="color: #dc2626;"><?= number_format($summary['total_alpa_semua'] ?? 0) ?></div>
            </div>
        </div>

        <!-- TABEL REKAP RESMI -->
        <table class="rekap-table">
            <thead>
                <tr>
                    <th style="width: 32px;">No</th>
                    <th style="text-align: left; min-width: 170px;">Nama Pegawai</th>
                    <th style="width: 105px;">NIY</th>
                    <th style="width: 85px;">Unit Tugas</th>
                    <th style="width: 105px; text-align: left;">Jabatan</th>
                    <th style="width: 58px;" title="Jumlah Hari Kerja Efektif">Hari Kerja</th>
                    <th style="width: 55px;" title="Jumlah Hadir (Total)">Hadir Total</th>
                    <th style="width: 65px;" title="Jumlah Hadir Tanpa Terlambat">Hadir Tepat</th>
                    <th style="width: 58px;" title="Terlambat">Terlambat</th>
                    <th style="width: 45px;" title="Izin">Izin</th>
                    <th style="width: 45px;" title="Sakit">Sakit</th>
                    <th style="width: 45px;" title="Cuti">Cuti</th>
                    <th style="width: 45px;" title="Alpa / Tanpa Keterangan">Alpa</th>
                    <th style="width: 65px;" title="Persentase Total Kehadiran">Total (%)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekapRows)): ?>
                    <tr>
                        <td colspan="14" class="text-center" style="padding: 24px; color: #64748b;">
                            Tidak ada data presensi pegawai untuk unit dan periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1; 
                    foreach ($rekapRows as $r): 
                        $pct = (float)$r['total_kehadiran'];
                        $badgeClass = 'badge-pct-high';
                        if ($pct < 75) {
                            $badgeClass = 'badge-pct-low';
                        } elseif ($pct < 90) {
                            $badgeClass = 'badge-pct-mid';
                        }
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <div class="pegawai-nama"><?= e($r['nama']) ?></div>
                            </td>
                            <td class="text-center" style="font-family: monospace; font-size: 10px;"><?= e($r['niy']) ?></td>
                            <td class="text-center"><strong><?= e($r['unit_tugas']) ?></strong></td>
                            <td><?= e($r['jabatan']) ?></td>
                            <td class="text-center"><strong><?= $r['jumlah_hari_kerja'] ?></strong></td>
                            <td class="text-center" style="font-weight: 700; color: #0f172a;"><?= $r['jumlah_hadir'] ?></td>
                            <td class="text-center" style="color: #16a34a; font-weight: 700;"><?= $r['hadir_tanpa_terlambat'] ?></td>
                            <td class="text-center" style="<?= $r['terlambat'] > 0 ? 'color: #d97706; font-weight: 700;' : 'color: #94a3b8;' ?>"><?= $r['terlambat'] ?></td>
                            <td class="text-center" style="<?= $r['izin'] > 0 ? 'color: #0284c7; font-weight: 700;' : 'color: #94a3b8;' ?>"><?= $r['izin'] ?></td>
                            <td class="text-center" style="<?= $r['sakit'] > 0 ? 'color: #7c3aed; font-weight: 700;' : 'color: #94a3b8;' ?>"><?= $r['sakit'] ?></td>
                            <td class="text-center" style="<?= $r['cuti'] > 0 ? 'color: #0d9488; font-weight: 700;' : 'color: #94a3b8;' ?>"><?= $r['cuti'] ?></td>
                            <td class="text-center" style="<?= $r['alpa'] > 0 ? 'color: #dc2626; font-weight: 700;' : 'color: #94a3b8;' ?>"><?= $r['alpa'] ?></td>
                            <td class="text-center">
                                <span class="badge-pct <?= $badgeClass ?>"><?= number_format($pct, 1) ?>%</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-right" style="padding-right: 12px;">TOTAL / RATA-RATA</th>
                    <th class="text-center"><?= $hariKerja ?></th>
                    <th class="text-center"><?= number_format($summary['total_hadir_semua'] ?? 0) ?></th>
                    <th class="text-center" style="color: #16a34a;"><?= number_format($summary['total_tepat_waktu_semua'] ?? 0) ?></th>
                    <th class="text-center" style="color: #d97706;"><?= number_format($summary['total_terlambat_semua'] ?? 0) ?></th>
                    <th class="text-center"><?= number_format($summary['total_izin_semua'] ?? 0) ?></th>
                    <th class="text-center"><?= number_format($summary['total_sakit_semua'] ?? 0) ?></th>
                    <th class="text-center"><?= number_format($summary['total_cuti_semua'] ?? 0) ?></th>
                    <th class="text-center" style="color: #dc2626;"><?= number_format($summary['total_alpa_semua'] ?? 0) ?></th>
                    <th class="text-center" style="color: #2563eb;"><?= number_format($summary['rata_rata_kehadiran'] ?? 0, 1) ?>%</th>
                </tr>
            </tfoot>
        </table>

        <!-- TANDA TANGAN PEJABAT PENGESAH -->
        <div class="signature-area">
            <div class="sig-block">
                <div class="sig-title">
                    Mengetahui,<br>
                    <strong>Kepala Unit / Pimpinan Sekolah</strong>
                </div>
                <div class="sig-name">( .................................................... )</div>
                <div class="sig-nip">NIY. ........................................</div>
            </div>

            <div class="sig-block">
                <div class="sig-title">
                    Palu, <?= e($cetakFmt) ?><br>
                    <strong>Penanggung Jawab Kepegawaian & SDM</strong>
                </div>
                <div class="sig-name"><?= e(Auth::user()['full_name'] ?? 'Bagian Kepegawaian') ?></div>
                <div class="sig-nip">SIT Bina Insan Palu</div>
            </div>
        </div>

    </div>

</body>
</html>
