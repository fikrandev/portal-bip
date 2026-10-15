<?php
/**
 * View Cetak Tanda Bukti Pendaftaran Calon Siswa Daftar Tunggu SPMB
 * Yayasan Bina Insan Palu (Portal BIP)
 */
$item = $pendaftar ?? [];

$logoUrl = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
} elseif (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $logoUrl = url(ltrim(SYS_APP_FAVICON, '/'));
}

// Cek Kop Gambar Banner dari Pengaturan SPMB (Bisa per Jenjang PAUD-SMA atau Global)
$jenjangItem = $item['jenjang'] ?? 'SD';
$kopGambarSetting = SpmbModel::getKopUnit($jenjangItem);
$kopGambarUrl = !empty($kopGambarSetting) ? url(ltrim($kopGambarSetting, '/')) : '';

// Nama Akun Petugas yang Sedang Login
$currentUser = Auth::user();
$namaPetugasLogin = $currentUser['full_name'] 
    ?? ($_SESSION['user_name'] 
    ?? ($_SESSION['user']['full_name'] 
    ?? 'Panitia SPMB'));

if (!function_exists('spmb_terbilang')) {
    function spmb_terbilang($angka): string {
        $angka = (float)$angka;
        if ($angka <= 0) return 'Nol';
        
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        
        $helper = function($n) use (&$helper, $baca) {
            $n = (float)$n;
            if ($n < 12) {
                return ' ' . $baca[(int)$n];
            } elseif ($n < 20) {
                return $helper($n - 10) . ' Belas';
            } elseif ($n < 100) {
                $sisa = $n % 10;
                return $helper((int)($n / 10)) . ' Puluh' . ($sisa > 0 ? $helper($sisa) : '');
            } elseif ($n < 200) {
                $sisa = $n - 100;
                return ' Seratus' . ($sisa > 0 ? $helper($sisa) : '');
            } elseif ($n < 1000) {
                $sisa = $n % 100;
                return $helper((int)($n / 100)) . ' Ratus' . ($sisa > 0 ? $helper($sisa) : '');
            } elseif ($n < 2000) {
                $sisa = $n - 1000;
                return ' Seribu' . ($sisa > 0 ? $helper($sisa) : '');
            } elseif ($n < 1000000) {
                $sisa = $n % 1000;
                return $helper((int)($n / 1000)) . ' Ribu' . ($sisa > 0 ? $helper($sisa) : '');
            } elseif ($n < 1000000000) {
                $sisa = fmod($n, 1000000);
                return $helper((int)($n / 1000000)) . ' Juta' . ($sisa > 0 ? $helper($sisa) : '');
            } elseif ($n < 1000000000000) {
                $sisa = fmod($n, 1000000000);
                return $helper((int)($n / 1000000000)) . ' Miliar' . ($sisa > 0 ? $helper($sisa) : '');
            }
            return ' Angka Terlalu Besar';
        };
        
        return trim(preg_replace('/\s+/', ' ', $helper($angka)));
    }
}

if (!function_exists('spmb_format_tgl_indo')) {
    function spmb_format_tgl_indo($dateStr): string {
        if (empty($dateStr)) return '-';
        $time = strtotime($dateStr);
        if (!$time) return '-';
        $bulanIndo = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $d = date('j', $time);
        $m = (int)date('n', $time);
        $y = date('Y', $time);
        return $d . ' ' . ($bulanIndo[$m] ?? date('F', $time)) . ' ' . $y;
    }
}

if (!function_exists('renderKwitansiCardDaftarTunggu')) {
    function renderKwitansiCardDaftarTunggu(array $item, string $logoUrl, string $kopGambarUrl, string $lembarLabel = 'LEMBAR 1 : UNTUK PENDAFTAR / ORANG TUA', bool $isCompact = true): void {
        $tglDaftarIndo = spmb_format_tgl_indo($item['tanggal_daftar'] ?? date('Y-m-d'));
        
        $currentUser = Auth::user();
        $namaPetugasLogin = $currentUser['full_name'] 
            ?? ($_SESSION['user_name'] 
            ?? ($_SESSION['user']['full_name'] 
            ?? 'Panitia SPMB'));
            
        $namaOrtu = $item['nama_ayah'] ?: ($item['nama_ibu'] ?: ($item['nama_wali'] ?: '-'));
        $hpKontak = $item['no_hp_ayah'] ?: ($item['no_hp_ibu'] ?: ($item['no_hp'] ?: ''));
        ?>
        <div class="kwitansi-card <?= $isCompact ? 'compact' : '' ?>">

            <!-- KOP SURAT (GAMBAR DARI PENGATURAN / TEKS DEFAULT) -->
            <?php if (!empty($kopGambarUrl)): ?>
                <div class="k-kop-img-wrap">
                    <img src="<?= $kopGambarUrl ?>" alt="Kop Surat Resmi SPMB" class="k-kop-banner">
                    <div class="k-header-badge-float">
                        <span class="lembar-tag"><?= e($lembarLabel) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="k-header">
                    <?php if (!empty($logoUrl)): ?>
                        <img src="<?= $logoUrl ?>" alt="Logo" class="k-logo">
                    <?php endif; ?>
                    <div class="k-header-center">
                        <h2>YAYASAN BINA INSAN PALU</h2>
                        <h3>PANITIA PENERIMAAN MURID BARU (SPMB)</h3>
                        <div class="k-sub-instansi">TK IT &bull; SD IT &bull; SMP IT &bull; SMA IT BINA INSAN PALU</div>
                        <div class="k-address">Jl. Garuda No. 34, Kota Palu, Sulawesi Tengah | WA: 0812-3456-7890 | portal-bip.sch.id</div>
                    </div>
                    <div class="k-header-badge">
                        <span class="lembar-tag"><?= e($lembarLabel) ?></span>
                    </div>
                </div>
                <div class="k-divider-double"></div>
            <?php endif; ?>

            <!-- Header Info & Nomor -->
            <div class="k-meta-row">
                <div class="k-title-box">
                    <h1>TANDA BUKTI DAFTAR TUNGGU</h1>
                    <div class="k-title-sub">PENERIMAAN MURID BARU (SPMB) TP <?= e($item['group_tahun_ajaran'] ?? '2026/2027') ?></div>
                </div>
                <table class="k-meta-table">
                    <tr>
                        <td class="km-label">No. Pendaftaran</td>
                        <td class="km-colon">:</td>
                        <td class="km-val font-mono"><strong class="highlight-reg"><?= e($item['nomor_pendaftaran'] ?? '-') ?></strong></td>
                    </tr>
                    <tr>
                        <td class="km-label">Tanggal Daftar</td>
                        <td class="km-colon">:</td>
                        <td class="km-val"><?= $tglDaftarIndo ?></td>
                    </tr>
                    <tr>
                        <td class="km-label">Rekomendasi</td>
                        <td class="km-colon">:</td>
                        <td class="km-val">
                            <strong><?= e($item['kategori_pendaftar'] ?? 'Yayasan') ?></strong>
                            <?php if (!empty($item['nama_rekomendasi'])): ?>
                                (<?= e($item['nama_rekomendasi']) ?>)
                            <?php endif; ?>
                            / <?= e($item['gelombang'] ?? 'Gelombang 1') ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Detail Data Pendaftar -->
            <table class="k-content-table">
                <tr>
                    <td class="kc-label">Nama Calon Siswa</td>
                    <td class="kc-colon">:</td>
                    <td class="kc-value">
                        <div class="siswa-highlight">
                            <strong><?= strtoupper(e($item['nama_lengkap'] ?? '-')) ?></strong>
                            <span class="detail-inline">
                                (JK: <?= ($item['jenis_kelamin'] ?? 'L') === 'L' ? 'Laki-laki' : 'Perempuan' ?> &bull; 
                                Umur: <?= e($item['umur'] ?: '-') ?>)
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="kc-label">Jenjang Dituju</td>
                    <td class="kc-colon">:</td>
                    <td class="kc-value font-semibold">
                        <?= e($item['jenjang'] ?? '-') ?> IT BINA INSAN PALU
                        <span class="detail-inline" style="font-weight: normal; color: #475569;">
                            (Kelompok: <?= e($item['nama_group'] ?? 'SPMB') ?> &bull; Status: <?= e($item['status_kelulusan'] ?? 'Cadangan') ?>)
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="kc-label">Asal Sekolah</td>
                    <td class="kc-colon">:</td>
                    <td class="kc-value">
                        <?= e($item['asal_sekolah'] ?: '-') ?>
                    </td>
                </tr>
                <tr>
                    <td class="kc-label">Orang Tua / Wali</td>
                    <td class="kc-colon">:</td>
                    <td class="kc-value">
                        <?= e($namaOrtu) ?>
                        <?php if (!empty($hpKontak)): ?>
                            <span class="detail-inline">(No. HP / WA: <?= e($hpKontak) ?>)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <!-- Footer / Catatan Pendaftaran & Tanda Tangan -->
            <div class="k-footer-row">
                <div class="k-nominal-col">
                    <div class="k-note-box">
                        <div class="k-note-title">📌 Ketentuan Daftar Tunggu (Waiting List):</div>
                        <ul class="k-note-list">
                            <li>Lembar ini merupakan bukti pendaftaran sah jalur Daftar Tunggu / Rekomendasi SIT Bina Insan Palu.</li>
                            <li>Calon siswa masuk dalam prioritas seleksi & konfirmasi kuota sesuai rekomendasi instansi/yayasan.</li>
                            <li>Informasi ketersediaan bangku dan jadwal observasi akan dihubungi langsung oleh panitia SPMB.</li>
                        </ul>
                    </div>
                </div>

                <div class="k-sig-col ortu-col">
                    <div class="sig-title">Orang Tua / Wali Calon Siswa,</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( <?= e($item['nama_ayah'] ?: ($item['nama_ibu'] ?: ($item['nama_wali'] ?: '........................................'))) ?> )</div>
                </div>

                <div class="k-sig-col panitia-col">
                    <div class="sig-title">Palu, <?= $tglDaftarIndo ?></div>
                    <div class="sig-sub">Panitia Penerimaan Murid Baru</div>
                    <div class="sig-space"></div>
                    <div class="sig-name">( <?= e($namaPetugasLogin) ?> )</div>
                </div>
            </div>

            <div class="k-footer-print-time">
                Dicetak melalui Portal BIP &bull; <?= date('d/m/Y H:i') ?> WITA
            </div>
        </div>
        <?php
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Bukti Daftar Tunggu SPMB - <?= e($item['nomor_pendaftaran'] ?? '') ?> - <?= e($item['nama_lengkap'] ?? '') ?></title>
    <!-- Favicon -->
    <?= function_exists('renderFaviconTags') ? renderFaviconTags() : '' ?>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.45;
        }

        /* ── Toolbar Atas ── */
        .print-toolbar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            border-radius: 12px;
            padding: 12px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .toolbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toolbar-icon {
            font-size: 22px;
        }
        .toolbar-brand strong {
            display: block;
            font-size: 14px;
            color: #0f172a;
        }
        .toolbar-sub {
            font-size: 11px;
            color: #64748b;
        }
        .toolbar-modes {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            padding: 4px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .mode-btn {
            background: transparent;
            border: none;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            color: #475569;
            transition: all 0.15s;
        }
        .mode-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .mode-btn.active {
            background: #d97706;
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(217, 119, 6, 0.25);
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print {
            background: #d97706;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.25);
            transition: background 0.15s;
        }
        .btn-print:hover {
            background: #b45309;
        }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-close:hover {
            background: #cbd5e1;
        }

        /* ── Container Kertas A4 ── */
        .paper-container {
            max-width: 860px;
            margin: 0 auto;
        }

        /* ── Kartu Kwitansi ── */
        .kwitansi-card {
            background: #ffffff;
            border: 1.5px solid #d97706;
            border-radius: 12px;
            padding: 16px 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        .kwitansi-card.compact {
            padding: 12px 18px;
        }

        /* KOP GAMBAR BANNER */
        .k-kop-img-wrap {
            position: relative;
            margin: -4px -4px 10px -4px;
            border-radius: 8px;
            overflow: hidden;
            border-bottom: 2px solid #0f172a;
        }
        .k-kop-banner {
            width: 100%;
            height: auto;
            max-height: 110px;
            object-fit: cover;
            display: block;
        }
        .k-header-badge-float {
            position: absolute;
            right: 10px;
            top: 10px;
        }

        /* KOP SURAT TEKS DEFAULT */
        .k-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-bottom: 8px;
        }
        .k-logo {
            width: 55px;
            height: 55px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .k-header-center {
            text-align: center;
            flex-grow: 1;
        }
        .k-header-center h2 {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
        .k-header-center h3 {
            margin: 2px 0 0 0;
            font-size: 11px;
            font-weight: 700;
            color: #d97706;
            letter-spacing: 0.5px;
        }
        .k-sub-instansi {
            font-size: 9.5px;
            font-weight: 700;
            color: #334155;
            margin-top: 1px;
        }
        .k-address {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }
        .k-header-badge {
            flex-shrink: 0;
            text-align: right;
        }
        .lembar-tag {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            font-size: 9px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.3px;
        }
        .k-divider-double {
            height: 3px;
            border-top: 2px solid #0f172a;
            border-bottom: 1px solid #0f172a;
            margin-bottom: 10px;
        }

        /* Meta Row */
        .k-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #cbd5e1;
        }
        .k-title-box h1 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            color: #92400e;
            letter-spacing: 0.5px;
        }
        .k-title-sub {
            font-size: 10px;
            color: #475569;
            font-weight: 600;
            margin-top: 2px;
        }
        .k-meta-table {
            border-collapse: collapse;
            font-size: 11px;
        }
        .k-meta-table td {
            padding: 1.5px 3px;
        }
        .km-label {
            color: #64748b;
            font-weight: 600;
        }
        .km-colon {
            color: #94a3b8;
            font-weight: bold;
        }
        .km-val {
            color: #0f172a;
            font-weight: 600;
        }
        .highlight-reg {
            color: #b45309;
            font-weight: 800;
            background: #fef3c7;
            padding: 1px 6px;
            border-radius: 4px;
        }

        /* Content Table */
        .k-content-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
            margin-bottom: 10px;
        }
        .k-content-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .kc-label {
            width: 130px;
            color: #475569;
            font-weight: 600;
        }
        .kc-colon {
            width: 12px;
            color: #94a3b8;
            font-weight: bold;
        }
        .kc-value {
            color: #0f172a;
        }
        .siswa-highlight strong {
            font-size: 12.5px;
            color: #0f172a;
            letter-spacing: 0.3px;
        }
        .detail-inline {
            font-size: 10.5px;
            color: #64748b;
            margin-left: 4px;
        }

        /* Footer & Signatures */
        .k-footer-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }
        .k-nominal-col {
            flex: 1.5;
        }
        .k-note-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 8px 10px;
        }
        .k-note-title {
            font-size: 10px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 3px;
        }
        .k-note-list {
            margin: 0;
            padding-left: 14px;
            font-size: 9px;
            color: #78350f;
            line-height: 1.35;
        }
        .k-sig-col {
            flex: 1;
            text-align: center;
            font-size: 10.5px;
        }
        .sig-title {
            font-weight: 600;
            color: #334155;
        }
        .sig-sub {
            font-size: 9.5px;
            color: #64748b;
        }
        .sig-space {
            height: 44px;
        }
        .sig-name {
            font-weight: 700;
            color: #0f172a;
            border-top: 1px dotted #94a3b8;
            display: inline-block;
            padding-top: 2px;
            min-width: 140px;
        }
        .k-footer-print-time {
            margin-top: 8px;
            text-align: right;
            font-size: 8.5px;
            color: #94a3b8;
            font-style: italic;
        }

        /* Perforation Divider */
        .perforation-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 14px 0;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
            user-select: none;
        }
        .scissors {
            font-size: 13px;
        }
        .perf-line {
            flex: 1;
            border-top: 1.5px dashed #cbd5e1;
        }
        .perf-text {
            background: #f1f5f9;
            padding: 2px 10px;
            border-radius: 999px;
            border: 1px dashed #cbd5e1;
            letter-spacing: 0.5px;
            font-size: 8.5px;
        }

        /* ── Hidden Mode Class ── */
        .hidden-mode {
            display: none !important;
        }

        /* Print Media Query */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .paper-container {
                max-width: 100% !important;
                margin: 0 !important;
            }
            .kwitansi-card {
                border-color: #000000 !important;
                box-shadow: none !important;
            }
            .perf-line {
                border-top: 1.5px dashed #000000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- ── Print Toolbar ── -->
    <div class="no-print print-toolbar">
        <div class="toolbar-brand">
            <span class="toolbar-icon">📋</span>
            <div>
                <strong>Tanda Bukti Daftar Tunggu SPMB</strong>
                <span class="toolbar-sub"><?= e($item['nomor_pendaftaran'] ?? '') ?> &bull; <?= e($item['nama_lengkap'] ?? '') ?></span>
            </div>
        </div>

        <div class="toolbar-modes">
            <button type="button" class="mode-btn active" data-mode="kwitansi-2" onclick="setMode('kwitansi-2')" title="Cetak Bukti Pendaftaran 2 Rangkap (Lembar Pendaftar & Lembar Panitia dalam 1 lembar A4)">
                📋 Bukti (2 Rangkap)
            </button>
            <button type="button" class="mode-btn" data-mode="kwitansi-1" onclick="setMode('kwitansi-1')" title="Cetak Bukti Pendaftaran 1 Rangkap">
                📋 Bukti (1 Rangkap)
            </button>
        </div>

        <div class="toolbar-actions">
            <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
            <button class="btn-close" onclick="window.close()">✕ Tutup</button>
        </div>
    </div>

    <!-- ── Paper Container ── -->
    <div class="paper-container">

        <!-- 1. MODE: BUKTI PENDAFTARAN 2 RANGKAP (DEFAULT) -->
        <div id="view-kwitansi-2">
            <?php renderKwitansiCardDaftarTunggu($item, $logoUrl, $kopGambarUrl, 'LEMBAR 1 : UNTUK PENDAFTAR / ORANG TUA', true); ?>

            <div class="perforation-divider">
                <span class="scissors">✂</span>
                <span class="perf-line"></span>
                <span class="perf-text">GUNTING / POTONG DI SINI</span>
                <span class="perf-line"></span>
                <span class="scissors">✂</span>
            </div>

            <?php renderKwitansiCardDaftarTunggu($item, $logoUrl, $kopGambarUrl, 'LEMBAR 2 : ARSIP PANITIA SPMB', true); ?>
        </div>

        <!-- 2. MODE: BUKTI PENDAFTARAN 1 RANGKAP -->
        <div id="view-kwitansi-1" class="hidden-mode">
            <?php renderKwitansiCardDaftarTunggu($item, $logoUrl, $kopGambarUrl, 'TANDA BUKTI DAFTAR TUNGGU SPMB', false); ?>
        </div>
    </div>

    <script>
        function setMode(mode) {
            document.querySelectorAll('.mode-btn').forEach(btn => {
                if (btn.getAttribute('data-mode') === mode) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            const views = {
                'kwitansi-2': document.getElementById('view-kwitansi-2'),
                'kwitansi-1': document.getElementById('view-kwitansi-1')
            };

            for (const key in views) {
                if (views[key]) {
                    if (key === mode) {
                        views[key].classList.remove('hidden-mode');
                    } else {
                        views[key].classList.add('hidden-mode');
                    }
                }
            }
        }

        const urlParams = new URLSearchParams(window.location.search);
        const modeParam = urlParams.get('mode');
        if (modeParam && ['kwitansi-2', 'kwitansi-1'].includes(modeParam)) {
            setMode(modeParam);
        }

        if (urlParams.get('autoprint') === '1') {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 400);
            });
        }
    </script>
</body>
</html>
