<?php
/**
 * View Cetak Surat Keputusan & Pengumuman Hasil Kelulusan SPMB Resmi
 * Yayasan Bina Insan Palu (Portal BIP)
 * Digunakan untuk ekspor PDF resmi yang dibagikan ke grup orang tua calon murid baru
 */

$selectedJenjang = strtoupper(trim($_GET['jenjang'] ?? 'SEMUA'));
$selectedJenis   = trim($_GET['jenis'] ?? 'Siswa Baru');
$activeGroup     = $activeGroup ?? SpmbModel::getActiveGroup();
$tahunAjaran     = $activeGroup['tahun_ajaran'] ?? ($activeGroup['nama_group'] ?? date('Y') . '/' . (date('Y') + 1));
$gelombang       = $activeGroup['gelombang_default'] ?? 'Gelombang 1';

$list = SpmbModel::getDaftarKelulusan([
    'group_id'          => $activeGroup['id'] ?? null,
    'jenjang'           => $selectedJenjang,
    'jenis_pendaftaran' => ($selectedJenis === 'Semua') ? '' : $selectedJenis
]);

$logoUrl = '';
if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
    $logoUrl = url(ltrim(SYS_APP_LOGO, '/'));
} elseif (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
    $logoUrl = url(ltrim(SYS_APP_FAVICON, '/'));
}

$kopGambarUrl = '';
if ($selectedJenjang !== 'SEMUA') {
    $kopSetting = SpmbModel::getKopUnit($selectedJenjang);
    if (!empty($kopSetting)) {
        $kopGambarUrl = url(ltrim($kopSetting, '/'));
    }
}
if (empty($kopGambarUrl)) {
    $kopGlobal = SpmbModel::getSetting('spmb_kop_gambar');
    if (!empty($kopGlobal)) {
        $kopGambarUrl = url(ltrim($kopGlobal, '/'));
    }
}

$namaSekolah = ($selectedJenjang !== 'SEMUA') ? SpmbModel::getNamaSekolahByJenjang($selectedJenjang) : 'SIT Bina Insan Palu';
$tglSekarangIndo = SpmbModel::formatTglIndo(date('Y-m-d'));
$nomorSk = 'SPMB/BIP/' . ($selectedJenjang !== 'SEMUA' ? $selectedJenjang : 'YAYASAN') . '/' . date('Y/m') . '/' . sprintf('%03d', rand(10, 99));

$currentUser = Auth::user();
$namaPetugasLogin = $currentUser['full_name'] 
    ?? ($_SESSION['user_name'] 
    ?? ($_SESSION['user']['full_name'] 
    ?? 'Panitia SPMB'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman_Kelulusan_SPMB_<?= htmlspecialchars($selectedJenjang) ?>_<?= date('Ymd') ?></title>
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
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
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
            font-size: 26px;
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
        .toolbar-filters {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .filter-select {
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            color: #334155;
            cursor: pointer;
        }
        .btn-action {
            border: none;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-print {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3);
        }
        .btn-print:hover {
            background: #0369a1;
        }
        .btn-wa {
            background: #16a34a;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3);
        }
        .btn-wa:hover {
            background: #15803d;
        }
        .btn-close {
            background: #e2e8f0;
            color: #334155;
        }
        .btn-close:hover {
            background: #cbd5e1;
        }

        /* ── Container Kertas F4/A4 Resmi ── */
        .paper-page {
            max-width: 215mm;
            min-height: 297mm;
            margin: 0 auto 30px auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.12);
            padding: 14mm 15mm 15mm 15mm;
            border-radius: 2px;
            position: relative;
        }

        /* ── Kop Surat ── */
        .kop-banner {
            width: 100%;
            max-height: 110px;
            object-fit: contain;
            display: block;
            margin-bottom: 12px;
        }
        .kop-header {
            display: flex;
            align-items: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 10px;
            margin-bottom: 16px;
            gap: 16px;
        }
        .kop-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            flex-shrink: 0;
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-yayasan {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #334155;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .kop-nama-sekolah {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .kop-alamat {
            font-size: 10px;
            color: #475569;
            line-height: 1.35;
        }

        /* ── Judul Dokumen ── */
        .doc-title-box {
            text-align: center;
            margin: 16px 0 14px 0;
        }
        .doc-title-main {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .doc-title-sub {
            font-size: 12px;
            font-weight: 700;
            color: #0369a1;
            text-transform: uppercase;
        }
        .doc-nomor {
            font-size: 10.5px;
            font-weight: 600;
            color: #475569;
            margin-top: 3px;
            font-family: monospace;
        }

        .intro-text {
            font-size: 11px;
            color: #1e293b;
            text-align: justify;
            margin-bottom: 14px;
            line-height: 1.5;
        }

        /* ── Tabel Calon Siswa Diterima ── */
        .table-lulus {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 18px;
        }
        .table-lulus th {
            background: #0284c7;
            color: #ffffff;
            font-weight: 700;
            padding: 7px 8px;
            border: 1px solid #0369a1;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.3px;
        }
        .table-lulus td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .table-lulus tr:nth-child(even) td {
            background: #f8fafc;
        }
        .badge-jenjang {
            display: inline-block;
            padding: 1px 6px;
            font-size: 9.5px;
            font-weight: 700;
            border-radius: 4px;
            background: #e0f2fe;
            color: #0369a1;
        }
        .badge-status-fix {
            display: inline-block;
            padding: 2px 7px;
            font-size: 9.5px;
            font-weight: 800;
            border-radius: 999px;
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        /* ── Catatan Informasi Daftar Ulang ── */
        .info-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 4px solid #0284c7;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 10.5px;
            line-height: 1.5;
        }
        .info-box strong {
            color: #0f172a;
        }
        .info-box ol {
            margin: 4px 0 0 16px;
            padding: 0;
        }

        /* ── Blok Tanda Tangan ── */
        .ttd-container {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            page-break-inside: avoid;
            font-size: 11px;
        }
        .ttd-box {
            text-align: center;
            width: 200px;
        }
        .ttd-space {
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-style: italic;
            color: #94a3b8;
            font-size: 10px;
        }
        .ttd-name {
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            padding-bottom: 1px;
            display: inline-block;
        }
        .ttd-jabatan {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── Dokumen Footer ── */
        .doc-footer {
            margin-top: 30px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: #94a3b8;
        }

        /* ── Media Print ── */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .paper-page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 10mm 15mm !important;
                max-width: 100% !important;
                min-height: 0 !important;
            }
            @page {
                size: 215mm 330mm; /* F4 / Folio Resmi */
                margin: 10mm;
            }
            .table-lulus tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- ── Toolbar Atas (No-Print) ── -->
    <div class="no-print print-toolbar">
        <div class="toolbar-brand">
            <span class="toolbar-icon">🎓</span>
            <div>
                <strong>Pengumuman Hasil Kelulusan SPMB</strong>
                <span class="toolbar-sub">Tahun Ajaran <?= e($tahunAjaran) ?> &bull; Total Diterima: <?= count($list) ?> Calon Siswa</span>
            </div>
        </div>

        <div class="toolbar-filters">
            <!-- Filter Jenjang -->
            <select class="filter-select" onchange="filterJenjang(this.value)" title="Pilih Jenjang Sekolah">
                <?php foreach (['SEMUA' => 'Semua Jenjang', 'PAUD' => 'Unit PAUD IT', 'SD' => 'Unit SD IT', 'SMP' => 'Unit SMP IT', 'SMA' => 'Unit SMA IT'] as $jKey => $jLbl): ?>
                    <option value="<?= $jKey ?>" <?= ($selectedJenjang === $jKey) ? 'selected' : '' ?>><?= $jLbl ?></option>
                <?php endforeach; ?>
            </select>

            <!-- Filter Jenis Pendaftaran -->
            <select class="filter-select" onchange="filterJenis(this.value)" title="Pilih Jenis Pendaftaran">
                <option value="Siswa Baru" <?= ($selectedJenis === 'Siswa Baru') ? 'selected' : '' ?>>Siswa Baru</option>
                <option value="Pindahan" <?= ($selectedJenis === 'Pindahan') ? 'selected' : '' ?>>Pindahan</option>
                <option value="Semua" <?= ($selectedJenis === 'Semua') ? 'selected' : '' ?>>Semua Jenis</option>
            </select>

            <!-- Tombol Cetak / PDF -->
            <button type="button" class="btn-action btn-print" onclick="window.print()" title="Cetak atau Simpan ke Format PDF">
                <span>🖨️</span>
                <span>Cetak / PDF</span>
            </button>

            <!-- Tombol Salin Teks Grup WA -->
            <button type="button" class="btn-action btn-wa" onclick="salinTeksPengumumanWa()" title="Salin Teks Resmi untuk Dibagikan ke Grup WhatsApp Orang Tua">
                <span>💬</span>
                <span>Salin Teks Grup WA</span>
            </button>

            <!-- Tombol Tutup -->
            <button type="button" class="btn-action btn-close" onclick="window.close()">✕ Tutup</button>
        </div>
    </div>

    <!-- ── Halaman Kertas Pengumuman Resmi ── -->
    <div class="paper-page">
        
        <!-- Kop Surat -->
        <?php if (!empty($kopGambarUrl)): ?>
            <img src="<?= $kopGambarUrl ?>" alt="Kop Surat Resmi" class="kop-banner">
        <?php else: ?>
            <div class="kop-header">
                <?php if ($logoUrl): ?>
                    <img src="<?= $logoUrl ?>" alt="Logo SIT Bina Insan Palu" class="kop-logo">
                <?php endif; ?>
                <div class="kop-text">
                    <div class="kop-yayasan">YAYASAN BINA INSAN PALU</div>
                    <div class="kop-nama-sekolah"><?= e(strtoupper($namaSekolah)) ?></div>
                    <div class="kop-alamat">
                        Jl. Bina Insan No. 01, Kota Palu, Sulawesi Tengah &bull; Telp/WA: (0451) 481234 / 0822-XXXX-XXXX<br>
                        Website: binainsanpalu.sch.id &bull; Email: spmb@binainsanpalu.sch.id
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Judul Dokumen Pengumuman -->
        <div class="doc-title-box">
            <div class="doc-title-main">SURAT KEPUTUSAN PANITIA SELEKSI PENERIMAAN MURID BARU</div>
            <div class="doc-title-sub">PENETAPAN HASIL SELEKSI DAN KELULUSAN CALON MURID BARU T.A. <?= e($tahunAjaran) ?></div>
            <div class="doc-nomor">Nomor: <?= $nomorSk ?></div>
        </div>

        <!-- Teks Pengantar -->
        <div class="intro-text">
            Berdasarkan hasil verifikasi berkas administrasi formulir pendaftaran, kesesuaian dokumen kependudukan (KK, Akta Lahir, KTP), serta pemenuhan surat komitmen orang tua/wali murid, Panitia SPMB Yayasan Bina Insan Palu menetapkan bahwa calon murid baru yang tercantum dalam daftar di bawah ini dinyatakan:
            <div style="text-align: center; margin: 10px 0;">
                <span class="badge-status-fix" style="font-size: 13px; padding: 4px 18px; letter-spacing: 1px;">
                    🎉 RESMI DITERIMA
                </span>
            </div>
            sebagai peserta didik baru di lingkungan Sekolah Islam Terpadu (SIT) Bina Insan Palu Tahun Ajaran <?= e($tahunAjaran) ?>:
        </div>

        <!-- Tabel Daftar Calon Murid Baru yang Diterima -->
        <table class="table-lulus">
            <thead>
                <tr>
                    <th style="width: 32px; text-align: center;">No</th>
                    <th style="width: 105px;">No. Pendaftaran</th>
                    <th>Nama Lengkap Siswa</th>
                    <th style="width: 38px; text-align: center;">L/P</th>
                    <th style="width: 55px; text-align: center;">Jenjang</th>
                    <th>Asal Sekolah</th>
                    <th style="width: 110px;">Kategori / Jalur</th>
                    <th style="width: 80px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 24px; color: #64748b; font-style: italic;">
                            Belum ada calon murid baru dengan status "Lulus / Diterima" pada filter ini.<br>
                            Gunakan tombol <strong>"Terima Siswa (Berkas Lengkap)"</strong> di halaman SPMB untuk menetapkan kelulusan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1;
                    foreach ($list as $row): 
                        $lp = ($row['jenis_kelamin'] === 'L') ? 'L' : 'P';
                        $kategori = !empty($row['kategori_pendaftar']) ? $row['kategori_pendaftar'] : (!empty($row['jalur_pendaftaran']) ? $row['jalur_pendaftaran'] : 'Reguler');
                    ?>
                        <tr>
                            <td style="text-align: center; font-weight: bold; color: #64748b;"><?= $no++ ?></td>
                            <td style="font-family: monospace; font-weight: bold; color: #0369a1;"><?= e($row['nomor_pendaftaran']) ?></td>
                            <td style="font-weight: bold; color: #0f172a; text-transform: uppercase;">
                                <?= e($row['nama_lengkap']) ?>
                                <?php if (!empty($row['nisn'])): ?>
                                    <span style="font-size: 9px; color: #64748b; font-weight: normal; display: block;">NISN: <?= e($row['nisn']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center; font-weight: bold; color: #334155;"><?= $lp ?></td>
                            <td style="text-align: center;">
                                <span class="badge-jenjang"><?= e($row['jenjang']) ?></span>
                            </td>
                            <td style="color: #334155;"><?= e($row['asal_sekolah'] ?: '-') ?></td>
                            <td style="font-size: 9.5px; color: #475569;"><?= e($kategori) ?></td>
                            <td style="text-align: center;">
                                <span class="badge-status-fix">Diterima</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Catatan Informasi Daftar Ulang -->
        <div class="info-box">
            <strong>📌 KETENTUAN DAN TAHAPAN SELANJUTNYA (DAFTAR ULANG):</strong>
            <ol>
                <li>Bagi calon murid baru yang dinyatakan <strong>DITERIMA</strong>, orang tua/wali wajib melakukan proses <strong>Daftar Ulang</strong> dan konfirmasi pemesanan seragam.</li>
                <li>Daftar ulang dapat dilakukan langsung di Sekretariat SPMB SIT Bina Insan Palu pada hari kerja (Senin - Sabtu, Pukul 08.00 - 15.00 WITA).</li>
                <li>Membawa bukti tanda pendaftaran dan kelengkapan administrasi yang telah diverifikasi oleh panitia.</li>
                <li>Bagi orang tua yang membutuhkan informasi tambahan, silakan menghubungi Sekretariat SPMB SIT Bina Insan Palu.</li>
            </ol>
        </div>

        <!-- Blok Tanda Tangan -->
        <div class="ttd-container">
            <div class="ttd-box">
                <div>Mengetahui,</div>
                <div style="font-weight: 700; color: #0f172a;">Kepala <?= e($namaSekolah) ?></div>
                <div class="ttd-space">
                    <span style="border: 1px dashed #cbd5e1; padding: 4px 8px; border-radius: 4px; font-size: 9px;">Cap & TTD Resmi</span>
                </div>
                <div class="ttd-name">( ............................................ )</div>
                <div class="ttd-jabatan">NIP / NIY: -</div>
            </div>

            <div class="ttd-box">
                <div>Ditetapkan di: <strong>Palu</strong></div>
                <div>Pada Tanggal: <strong><?= $tglSekarangIndo ?></strong></div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 2px;">Ketua Panitia SPMB</div>
                <div class="ttd-space">
                    <span style="border: 1px dashed #cbd5e1; padding: 4px 8px; border-radius: 4px; font-size: 9px;">Tanda Tangan Panitia</span>
                </div>
                <div class="ttd-name"><?= e($namaPetugasLogin) ?></div>
                <div class="ttd-jabatan">Panitia SPMB BIP TA <?= e($tahunAjaran) ?></div>
            </div>
        </div>

        <!-- Dokumen Footer -->
        <div class="doc-footer">
            <span>Dicetak secara otomatis melalui Portal BIP &bull; <?= date('d/m/Y H:i') ?> WITA</span>
            <span>Halaman Resmi Hasil Seleksi SPMB &bull; Yayasan Bina Insan Palu</span>
        </div>
    </div>

    <!-- Hidden Container for WA Group Text Template -->
    <div id="rawWaText" style="display: none;"><?= htmlspecialchars(SpmbModel::generateTeksPengumumanGrup($selectedJenjang, $activeGroup['id'] ?? null, $selectedJenis)) ?></div>

    <script>
        function filterJenjang(jenjang) {
            const url = new URL(window.location.href);
            url.searchParams.set('jenjang', jenjang);
            window.location.href = url.toString();
        }

        function filterJenis(jenis) {
            const url = new URL(window.location.href);
            url.searchParams.set('jenis', jenis);
            window.location.href = url.toString();
        }

        function salinTeksPengumumanWa() {
            const text = document.getElementById('rawWaText').innerText;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('✓ Berhasil menyalin Teks Pengumuman Resmi!\n\nAnda dapat langsung menempelkannya (paste) di grup WhatsApp orang tua.');
                }).catch(() => {
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        }

        function fallbackCopy(text) {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            alert('✓ Berhasil menyalin Teks Pengumuman Resmi!\n\nAnda dapat langsung menempelkannya di grup WhatsApp orang tua.');
        }
    </script>
</body>
</html>
