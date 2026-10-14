<?php
/**
 * View Cetak Tanda Bukti Pendaftaran SPMB
 * Yayasan Bina Insan Palu (Portal BIP)
 */
$item = $pendaftar ?? [];

require_once BASE_PATH . '/modules/kelola-spmb/views/partials/formulir_peserta_didik_dapodik.php';

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

if (!function_exists('renderKwitansiCard')) {
    function renderKwitansiCard(array $item, string $logoUrl, string $kopGambarUrl, string $lembarLabel = 'LEMBAR 1 : UNTUK PENDAFTAR / ORANG TUA', bool $isCompact = true): void {
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
                    <h1>TANDA BUKTI PENDAFTARAN</h1>
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
                        <td class="km-label">Kategori / Gel.</td>
                        <td class="km-colon">:</td>
                        <td class="km-val"><?= e($item['kategori_pendaftar'] ?: ($item['jalur_pendaftaran'] ?: 'Umum (Luar Bina Insan)')) ?> / <?= e($item['gelombang'] ?? 'Gelombang 1') ?></td>
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
                                NISN: <?= e($item['nisn'] ?: '-') ?>)
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
                            (Kelompok: <?= e($item['nama_group'] ?? 'SPMB') ?> &bull; Jenis: <?= e($item['jenis_pendaftaran'] ?? 'Siswa Baru') ?>)
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
                        <div class="k-note-title">📌 Informasi & Ketentuan Pendaftaran:</div>
                        <ul class="k-note-list">
                            <li>Lembar ini merupakan bukti pendaftaran sah calon murid baru SIT Bina Insan Palu.</li>
                            <li>Bawa dan tunjukkan bukti pendaftaran ini saat verifikasi berkas atau observasi/tes.</li>
                            <li>Jadwal tahapan seleksi dan pengumuman dapat dipantau melalui panitia SPMB.</li>
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
    <title>Tanda Bukti Pendaftaran SPMB - <?= e($item['nomor_pendaftaran'] ?? '') ?> - <?= e($item['nama_lengkap'] ?? '') ?></title>
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
            font-size: 24px;
        }
        .toolbar-brand strong {
            display: block;
            font-size: 14px;
            color: #0f172a;
        }
        .toolbar-sub {
            display: block;
            font-size: 11px;
            color: #64748b;
        }
        .toolbar-modes {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border-radius: 8px;
            padding: 3px;
            gap: 2px;
        }
        .mode-btn {
            border: none;
            background: transparent;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        .mode-btn:hover {
            color: #0284c7;
        }
        .mode-btn.active {
            background: #ffffff;
            color: #0284c7;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3);
            transition: all 0.2s;
        }
        .btn-print:hover {
            background: #0369a1;
        }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-close:hover {
            background: #cbd5e1;
        }

        /* ── Container Kertas ── */
        .paper-container {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.12);
            padding: 24px;
            border-radius: 4px;
        }

        /* ── Kwitansi Card ── */
        .kwitansi-card {
            border: 2px solid #334155;
            border-radius: 6px;
            padding: 16px 20px;
            background: #ffffff;
            position: relative;
            margin-bottom: 0;
        }
        .kwitansi-card.compact {
            padding: 12px 16px;
            font-size: 11px;
        }

        /* ── Kop Gambar Banner ── */
        .k-kop-img-wrap {
            position: relative;
            width: 100%;
            text-align: center;
            margin-bottom: 6px;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 4px;
        }
        .k-kop-banner {
            width: 100%;
            max-height: 90px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        .compact .k-kop-banner {
            max-height: 72px;
        }
        .k-header-badge-float {
            position: absolute;
            top: 2px;
            right: 0;
        }

        /* ── Kop Surat Standar (Teks) ── */
        .k-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
        }
        .k-logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
        }
        .compact .k-logo {
            width: 48px;
            height: 48px;
        }
        .k-header-center {
            flex: 1;
            text-align: center;
        }
        .k-header-center h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .compact .k-header-center h2 {
            font-size: 13px;
        }
        .k-header-center h3 {
            margin: 2px 0;
            font-size: 12px;
            font-weight: 700;
            color: #0284c7;
        }
        .compact .k-header-center h3 {
            font-size: 11px;
        }
        .k-sub-instansi {
            font-size: 10px;
            font-weight: 700;
            color: #334155;
            margin-top: 1px;
        }
        .k-address {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 1px;
        }

        .k-header-badge {
            text-align: right;
            align-self: flex-start;
        }
        .lembar-tag {
            display: inline-block;
            font-size: 9px;
            font-weight: 800;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .k-divider-double {
            border-top: 2.5px solid #1e293b;
            border-bottom: 1px solid #1e293b;
            height: 3px;
            margin: 6px 0 10px 0;
        }

        /* Meta Row */
        .k-meta-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 8px;
            gap: 15px;
        }
        .k-title-box h1 {
            margin: 0;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #0f172a;
            text-decoration: underline;
        }
        .compact .k-title-box h1 {
            font-size: 13.5px;
        }
        .k-title-sub {
            font-size: 10px;
            font-weight: 600;
            color: #475569;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .k-meta-table {
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .compact .k-meta-table {
            font-size: 9.5px;
        }
        .k-meta-table td {
            padding: 1.5px 3px;
            vertical-align: middle;
        }
        .km-label {
            color: #475569;
            width: 85px;
        }
        .km-colon {
            width: 8px;
            text-align: center;
        }
        .km-val {
            color: #0f172a;
        }
        .highlight-reg {
            background: #e0f2fe;
            color: #0369a1;
            padding: 1px 5px;
            border-radius: 3px;
            border: 1px solid #bae6fd;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }

        /* Content Table */
        .k-content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .compact .k-content-table {
            margin-bottom: 8px;
        }
        .k-content-table td {
            padding: 4px 4px;
            vertical-align: top;
        }
        .compact .k-content-table td {
            padding: 2.5px 3px;
        }
        .kc-label {
            width: 22%;
            color: #334155;
            font-weight: 600;
            font-size: 11px;
        }
        .compact .kc-label {
            font-size: 10px;
            width: 20%;
        }
        .kc-colon {
            width: 2%;
            text-align: center;
            font-weight: bold;
        }
        .kc-value {
            width: 76%;
            color: #0f172a;
        }
        .font-semibold {
            font-weight: 700;
        }

        .terbilang-box {
            background: #f8fafc;
            border: 1px dashed #94a3b8;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 700;
            color: #1e293b;
            font-size: 11px;
        }
        .compact .terbilang-box {
            padding: 3px 6px;
            font-size: 10px;
        }

        .siswa-highlight {
            font-size: 11.5px;
        }
        .compact .siswa-highlight {
            font-size: 10.5px;
        }
        .detail-inline {
            font-size: 10px;
            color: #475569;
            margin-left: 4px;
        }

        /* Footer Row */
        .k-footer-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed #cbd5e1;
        }
        .k-nominal-col {
            flex: 1.35;
        }
        .k-note-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #0284c7;
            border-radius: 6px;
            padding: 7px 12px;
            max-width: 410px;
        }
        .compact .k-note-box {
            padding: 5px 9px;
            max-width: 380px;
        }
        .k-note-title {
            font-size: 9.5px;
            font-weight: 700;
            color: #0369a1;
            margin-bottom: 3px;
        }
        .compact .k-note-title {
            font-size: 9px;
        }
        .k-note-list {
            margin: 0;
            padding-left: 15px;
            font-size: 9px;
            color: #475569;
            line-height: 1.35;
        }
        .compact .k-note-list {
            font-size: 8.5px;
            line-height: 1.3;
        }

        /* Signatures (Ruang Bersih Tanpa Cap) */
        .k-sig-col {
            flex: 0.9;
            text-align: center;
            font-size: 10.5px;
        }
        .compact .k-sig-col {
            font-size: 9.5px;
        }
        .sig-title {
            font-weight: 600;
            color: #1e293b;
        }
        .sig-sub {
            font-size: 9px;
            color: #64748b;
        }
        .sig-space {
            height: 48px;
        }
        .compact .sig-space {
            height: 40px;
        }
        .sig-name {
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px dotted #94a3b8;
            padding-bottom: 2px;
            display: inline-block;
            min-width: 140px;
        }

        .k-footer-print-time {
            text-align: right;
            font-size: 8px;
            color: #94a3b8;
            margin-top: 6px;
        }

        /* Garis Potong 2 Rangkap */
        .perforation-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 14px 0;
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .perf-line {
            flex: 1;
            border-top: 1.5px dashed #cbd5e1;
        }
        .scissors {
            font-size: 13px;
        }

        /* ── Biodata Form Styles ── */
        .biodata-container {
            border: 1px solid #cbd5e1;
            padding: 24px;
        }
        .b-kop-img-wrap {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 3px double #1f2937;
            padding-bottom: 8px;
        }
        .b-kop-banner {
            width: 100%;
            max-height: 100px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        .b-header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #1f2937;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .b-header img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-right: 18px;
        }
        .b-header-text {
            flex: 1;
            text-align: center;
        }
        .b-header-text h2 {
            margin: 0;
            font-size: 17px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .b-header-text h3 {
            margin: 2px 0;
            font-size: 14px;
            font-weight: bold;
        }
        .b-header-text p {
            margin: 2px 0;
            font-size: 11px;
            color: #4b5563;
        }
        .b-title-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .b-title-box h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .b-title-box p {
            margin: 3px 0 0;
            font-size: 11.5px;
            color: #374151;
        }
        .section-title {
            background: #f1f5f9;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 11.5px;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 8px;
            border-left: 4px solid #0284c7;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.data-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11.5px;
        }
        table.data-table td.label {
            width: 28%;
            color: #4b5563;
        }
        table.data-table td.colon {
            width: 2%;
            text-align: center;
        }
        table.data-table td.value {
            width: 70%;
            font-weight: 500;
        }
        .photo-box {
            width: 85px;
            height: 110px;
            border: 1px dashed #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            float: right;
            margin-left: 15px;
            margin-bottom: 15px;
        }
        .signature-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 10px;
        }
        .sig-block {
            width: 42%;
            text-align: center;
            font-size: 11px;
        }

        .hidden-mode {
            display: none !important;
        }
        .page-break {
            page-break-after: always;
            break-after: page;
        }

        /* ── Hidden Mode ── */
        .hidden-mode {
            display: none !important;
        }

        /* ── Media Print Optimization ── */
        @media print {
            @page {
                size: 215mm 330mm; /* Kertas F4 / Folio Resmi (21.5cm x 33cm) */
                margin: 10mm;      /* Margin 1cm (kiri, kanan, bawah, atas) */
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .hidden-mode {
                display: none !important;
            }
            .paper-container {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
            .kwitansi-card {
                border-color: #000000 !important;
            }
            .page-break {
                page-break-after: always;
                break-after: page;
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
                <strong>Tanda Bukti Pendaftaran SPMB</strong>
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
            <button type="button" class="mode-btn" data-mode="biodata" onclick="setMode('biodata')" title="Cetak Formulir Biodata Siswa Standar Dapodik Resmi (Semua Unit)">
                📄 Formulir Biodata Dapodik (3 Hal)
            </button>
            <button type="button" class="mode-btn" data-mode="semua" onclick="setMode('semua')" title="Cetak Bukti Pendaftaran + Formulir Biodata Dapodik">
                📑 Lengkap (Bukti + Biodata)
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
            <?php renderKwitansiCard($item, $logoUrl, $kopGambarUrl, 'LEMBAR 1 : UNTUK PENDAFTAR / ORANG TUA', true); ?>

            <div class="perforation-divider">
                <span class="scissors">✂</span>
                <span class="perf-line"></span>
                <span class="perf-text">GUNTING / POTONG DI SINI</span>
                <span class="perf-line"></span>
                <span class="scissors">✂</span>
            </div>

            <?php renderKwitansiCard($item, $logoUrl, $kopGambarUrl, 'LEMBAR 2 : ARSIP PANITIA SPMB', true); ?>
        </div>

        <!-- 2. MODE: BUKTI PENDAFTARAN 1 RANGKAP -->
        <div id="view-kwitansi-1" class="hidden-mode">
            <?php renderKwitansiCard($item, $logoUrl, $kopGambarUrl, 'TANDA BUKTI PENDAFTARAN SPMB', false); ?>
        </div>

        <!-- 3. MODE: FORMULIR BIODATA PENDAFTARAN (STANDAR DAPODIK RESMI SEMUA UNIT) -->
        <div id="view-biodata" class="hidden-mode">
            <?php renderDapodikFormulirPesertaDidik($item); ?>
        </div>

        <!-- 4. MODE: LENGKAP (BUKTI PENDAFTARAN + FORMULIR BIODATA DAPODIK) -->
        <div id="view-semua" class="hidden-mode">
            <?php renderKwitansiCard($item, $logoUrl, $kopGambarUrl, 'LEMBAR PENDAFTAR & ARSIP SPMB', false); ?>
            
            <div class="page-break" style="margin-top: 30px;"></div>

            <?php renderDapodikFormulirPesertaDidik($item); ?>
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
                'kwitansi-1': document.getElementById('view-kwitansi-1'),
                'biodata': document.getElementById('view-biodata'),
                'semua': document.getElementById('view-semua')
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
        if (modeParam && ['kwitansi-2', 'kwitansi-1', 'biodata', 'semua'].includes(modeParam)) {
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
