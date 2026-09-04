<?php
/**
 * Cetak Biodata Siswa - Portal BIP
 * Official Printable Student Profile (Standar Dapodik & BIP)
 */
$namaSiswa = $siswa['nama_lengkap'] ?: $siswa['nama'];
$jenjang = strtoupper($siswa['jenjang'] ?? 'SD');
$isLaki = ($siswa['jenis_kelamin'] === 'L' || $siswa['jenis_kelamin'] === 'Laki-Laki');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Biodata_Siswa_<?= preg_replace('/[^a-zA-Z0-9]/', '_', $namaSiswa) ?></title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header-kop h2 {
            margin: 0;
            font-size: 14pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-kop h1 {
            margin: 2px 0;
            font-size: 16pt;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .header-kop p {
            margin: 0;
            font-size: 9pt;
            font-style: italic;
        }
        .doc-title {
            text-align: center;
            margin: 15px 0 12px 0;
        }
        .doc-title h3 {
            margin: 0;
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .doc-title span {
            font-size: 10pt;
            color: #333;
        }
        .section-header {
            font-weight: bold;
            font-size: 11pt;
            background: #f0f0f0;
            padding: 4px 8px;
            border-left: 4px solid #000;
            margin: 12px 0 6px 0;
            text-transform: uppercase;
        }
        table.bio-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10.5pt;
        }
        table.bio-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        table.bio-table td.label {
            width: 25%;
        }
        table.bio-table td.colon {
            width: 2%;
            text-align: center;
        }
        table.bio-table td.value {
            width: 73%;
            font-weight: 500;
        }
        .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px dashed #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9pt;
            color: #777;
            text-align: center;
            float: right;
            margin-left: 15px;
            margin-bottom: 10px;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
        }
        .signatures td {
            text-align: center;
            vertical-align: top;
            width: 50%;
            font-size: 11pt;
        }
        .sign-space {
            height: 65px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- Print Action Bar (Hidden on print) -->
    <div class="no-print" style="margin-bottom: 20px; text-align: right; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
        <button onclick="window.print();" style="padding: 8px 18px; background: #059669; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Formulir (Print / PDF)
        </button>
        <button onclick="window.close();" style="padding: 8px 14px; background: #64748b; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: 8px;">
            Tutup
        </button>
    </div>

    <!-- Kop Surat -->
    <div class="header-kop">
        <h2>Yayasan Bina Insan Palu</h2>
        <h1>Sistem Informasi Akademik & Portal Terpadu</h1>
        <p>Jl. Banteng No. 1, Kota Palu, Sulawesi Tengah • Telp: (0451) 481xxx • Email: info@binainsanpalu.sch.id</p>
    </div>

    <!-- Document Title -->
    <div class="doc-title">
        <h3>LEMBAR BIODATA INDUK SISWA (DAPODIK)</h3>
        <span>Tahun Ajaran: <?= e($siswa['tahun_ajaran'] ?: '2026/2027') ?> • Semester: <?= e($siswa['semester'] ?: 'Ganjil') ?></span>
    </div>

    <!-- Photo Box -->
    <div class="photo-box">
        Pas Foto<br>3 x 4 cm
    </div>

    <!-- I. IDENTITAS SISWA -->
    <div class="section-header">I. Data Identitas Peserta Didik</div>
    <table class="bio-table">
        <tr>
            <td class="label">1. Nama Lengkap</td>
            <td class="colon">:</td>
            <td class="value"><strong><?= strtoupper(e($namaSiswa)) ?></strong></td>
        </tr>
        <tr>
            <td class="label">2. NIS / NISN</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['nis'] ?: '-') ?> / <?= e($siswa['nisn'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">3. NIK / No. Kartu Keluarga</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['no_nik'] ?: '-') ?> / <?= e($siswa['no_kk'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">4. No. Registrasi Akta Lahir</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['no_registrasi_akta'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">5. Jenis Kelamin</td>
            <td class="colon">:</td>
            <td class="value"><?= $isLaki ? 'Laki-Laki' : 'Perempuan' ?></td>
        </tr>
        <tr>
            <td class="label">6. Tempat, Tanggal Lahir</td>
            <td class="colon">:</td>
            <td class="value">
                <?= e($siswa['tempat_lahir'] ?: '-') ?>, 
                <?= !empty($siswa['tgl_lahir']) ? date('d F Y', strtotime($siswa['tgl_lahir'])) : (!empty($siswa['tanggal_lahir']) ? date('d F Y', strtotime($siswa['tanggal_lahir'])) : '-') ?> 
                (Usia: <?= e($siswa['umur'] ?? '0') ?> Tahun)
            </td>
        </tr>
        <tr>
            <td class="label">7. Anak Ke-</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['anak_ke'] ?: '1') ?></td>
        </tr>
        <tr>
            <td class="label">8. Kebutuhan Khusus / Alergi</td>
            <td class="colon">:</td>
            <td class="value">
                Kebutuhan: <?= e($siswa['kebutuhan_khusus'] ?: 'Tidak Ada') ?> • 
                Alergi: <?= e($siswa['nama_alergi'] ?: ($siswa['alergi'] ?: 'Tidak Ada')) ?>
            </td>
        </tr>
        <tr>
            <td class="label">9. Tinggi & Berat Badan</td>
            <td class="colon">:</td>
            <td class="value">
                Tinggi: <?= e($siswa['tinggi_badan'] ? $siswa['tinggi_badan'] . ' cm' : '-') ?> • 
                Berat: <?= e($siswa['berat_badan'] ? $siswa['berat_badan'] . ' kg' : '-') ?>
            </td>
        </tr>
        <tr>
            <td class="label">10. Asal Sekolah</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['asal_sekolah'] ?: '-') ?></td>
        </tr>
    </table>

    <!-- II. DATA AKADEMIK -->
    <div class="section-header">II. Data Akademik & Rombel</div>
    <table class="bio-table">
        <tr>
            <td class="label">1. Jenjang Satuan</td>
            <td class="colon">:</td>
            <td class="value"><strong><?= e($jenjang) ?></strong></td>
        </tr>
        <tr>
            <td class="label">2. Rombel / Kelas</td>
            <td class="colon">:</td>
            <td class="value"><strong>Kelas <?= e($siswa['kelas'] ?: '-') ?></strong></td>
        </tr>
        <tr>
            <td class="label">3. Status Dapodik</td>
            <td class="colon">:</td>
            <td class="value"><?= ($siswa['dapodik'] === 'Sudah') ? 'Terdaftar di Dapodik' : 'Belum Terdaftar' ?></td>
        </tr>
        <tr>
            <td class="label">4. Status Keaktifan</td>
            <td class="colon">:</td>
            <td class="value"><?= !empty($siswa['is_active']) ? 'Aktif Belajar' : 'Non-Aktif' ?></td>
        </tr>
    </table>

    <!-- III. ALAMAT & KONTAK -->
    <div class="section-header">III. Alamat Domisili & Tempat Tinggal</div>
    <table class="bio-table">
        <tr>
            <td class="label">1. Alamat Tempat Tinggal</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['alamat'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">2. RT / RW / Dusun</td>
            <td class="colon">:</td>
            <td class="value">RT <?= e($siswa['rt'] ?: '-') ?> / RW <?= e($siswa['rw'] ?: '-') ?> • Dusun: <?= e($siswa['dusun'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">3. Kelurahan / Kecamatan</td>
            <td class="colon">:</td>
            <td class="value">Kel. <?= e($siswa['kelurahan'] ?: '-') ?>, Kec. <?= e($siswa['kecamatan'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">4. Kota / Provinsi / Kode Pos</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['kota'] ?: 'Palu') ?>, <?= e($siswa['provinsi'] ?: 'Sulawesi Tengah') ?> (<?= e($siswa['kode_pos'] ?: '-') ?>)</td>
        </tr>
        <tr>
            <td class="label">5. Tinggal Bersama / Transportasi</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['tempat_tinggal'] ?: 'Orang Tua') ?> • Moda: <?= e($siswa['moda_transportasi'] ?: 'Sepeda Motor') ?></td>
        </tr>
        <tr>
            <td class="label">6. No. Telepon / WhatsApp</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['no_hp'] ?: ($siswa['telepon'] ?: '-')) ?></td>
        </tr>
    </table>

    <!-- IV. DATA ORANG TUA -->
    <div class="section-header">IV. Data Orang Tua / Wali</div>
    <table class="bio-table">
        <tr>
            <td class="label">1. Nama Ayah Kandung</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['nama_ayah'] ?: '-') ?> (NIK: <?= e($siswa['nik_ayah'] ?: '-') ?>)</td>
        </tr>
        <tr>
            <td class="label">2. Pekerjaan & Pendidikan Ayah</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['pekerjaan_ayah'] ?: '-') ?> • <?= e($siswa['pendidikan_ayah'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">3. Penghasilan Bulanan Ayah</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['penghasilan_ayah'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">4. Nama Ibu Kandung</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['nama_ibu'] ?: '-') ?> (NIK: <?= e($siswa['nik_ibu'] ?: '-') ?>)</td>
        </tr>
        <tr>
            <td class="label">5. Pekerjaan & Pendidikan Ibu</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['pekerjaan_ibu'] ?: '-') ?> • <?= e($siswa['pendidikan_ibu'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="label">6. Penghasilan Bulanan Ibu</td>
            <td class="colon">:</td>
            <td class="value"><?= e($siswa['penghasilan_ibu'] ?: '-') ?></td>
        </tr>
    </table>

    <!-- Signatures -->
    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                Orang Tua / Wali Siswa
                <div class="sign-space"></div>
                <strong>( <?= e($siswa['nama_ayah'] ?: ($siswa['nama_ibu'] ?: '..........................................')) ?> )</strong>
            </td>
            <td>
                Palu, <?= date('d F Y') ?><br>
                Petugas Pendataan / Dapodik
                <div class="sign-space"></div>
                <strong>( .......................................... )</strong>
            </td>
        </tr>
    </table>

</body>
</html>
