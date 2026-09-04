<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembar Buku Induk — <?= e($siswa['nama_lengkap'] ?: $siswa['nama']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.4;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 2px 0;
        }
        .header p {
            font-size: 9pt;
            margin: 0;
        }
        .title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 15px 0 20px 0;
            text-transform: uppercase;
        }
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-top: 15px;
            margin-bottom: 5px;
            background: #f0f0f0;
            padding: 3px 6px;
            border-left: 4px solid #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        td {
            padding: 3px 4px;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .col-num { width: 5%; }
        .col-label { width: 35%; }
        .col-colon { width: 3%; }
        .col-val { width: 57%; font-weight: 500; }
        
        .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px dashed #000;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 9pt;
            color: #555;
            float: right;
            margin: 10px 0 10px 15px;
        }
        .signature-table {
            margin-top: 30px;
            width: 100%;
        }
        .signature-table td {
            text-align: center;
            padding: 10px;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <!-- No Print Action Bar -->
    <div class="no-print" style="background:#1e293b; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-radius:8px;">
        <div style="font-family:sans-serif; font-size:13px; font-weight:bold;">
            📄 Cetak Lembar Buku Induk: <?= e($siswa['nama_lengkap'] ?: $siswa['nama']) ?>
        </div>
        <div>
            <button onclick="window.print()" style="background:#10b981; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:bold; cursor:pointer; margin-right:8px;">
                🖨️ Cetak Dokumen (Print)
            </button>
            <button onclick="window.close()" style="background:#64748b; color:#fff; border:none; padding:8px 14px; border-radius:6px; font-weight:bold; cursor:pointer;">
                ✕ Tutup
            </button>
        </div>
    </div>

    <!-- Kop Sekolah -->
    <div class="header">
        <h1>YAYASAN BINA INSAN PALU</h1>
        <h2>LEMBAR BUKU INDUK PESERTA DIDIK</h2>
        <p>Satuan Pendidikan: <?= e($siswa['jenjang']) ?> IT Bina Insan Palu | Tahun Pelajaran: <?= e($siswa['tahun_ajaran'] ?: '2026/2027') ?></p>
    </div>

    <div class="title">LEMBAR BUKU INDUK SISWA</div>

    <!-- Photo Box -->
    <div class="photo-box">
        <?php if (!empty($siswa['foto'])): ?>
            <img src="<?= asset('uploads/siswa/' . $siswa['foto']) ?>" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
            Pas Foto<br>3 x 4 cm
        <?php endif; ?>
    </div>

    <!-- Section A: Keterangan Pribadi -->
    <div class="section-title">A. KETERANGAN PRIBADI SISWA</div>
    <table>
        <tr>
            <td class="col-num">1.</td>
            <td class="col-label">Nomor Induk Siswa (NIS)</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['nis'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">2.</td>
            <td class="col-label">Nomor Induk Siswa Nasional (NISN)</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['nisn'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">3.</td>
            <td class="col-label">Nama Lengkap Siswa</td>
            <td class="col-colon">:</td>
            <td class="col-val" style="text-transform:uppercase; font-weight:bold;"><?= e($siswa['nama_lengkap'] ?: $siswa['nama']) ?></td>
        </tr>
        <tr>
            <td class="col-num">4.</td>
            <td class="col-label">Jenis Kelamin</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= ($siswa['jenis_kelamin'] === 'L' || $siswa['jenis_kelamin'] === 'Laki-Laki') ? 'Laki-Laki' : 'Perempuan' ?></td>
        </tr>
        <tr>
            <td class="col-num">5.</td>
            <td class="col-label">Tempat, Tanggal Lahir</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['tempat_lahir'] ?: '-') ?>, <?= $siswa['tgl_lahir'] ? date('d F Y', strtotime($siswa['tgl_lahir'])) : '-' ?></td>
        </tr>
        <tr>
            <td class="col-num">6.</td>
            <td class="col-label">NIK / No. KTP Anak</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['no_nik'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">7.</td>
            <td class="col-label">Nomor Kartu Keluarga (KK)</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['no_kk'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">8.</td>
            <td class="col-label">No. Registrasi Akta Kelahiran</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['no_registrasi_akta'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">9.</td>
            <td class="col-label">Anak Ke / Dari</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['anak_ke'] ?: '1') ?></td>
        </tr>
        <tr>
            <td class="col-num">10.</td>
            <td class="col-label">Kebutuhan Khusus / Alergi</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['kebutuhan_khusus'] ?: 'Tidak Ada') ?> <?= !empty($siswa['nama_alergi']) ? ' (Alergi: ' . e($siswa['nama_alergi']) . ')' : '' ?></td>
        </tr>
        <tr>
            <td class="col-num">11.</td>
            <td class="col-label">Tinggi / Berat Badan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['tinggi_badan'] ?: '-') ?> cm / <?= e($siswa['berat_badan'] ?: '-') ?> kg</td>
        </tr>
    </table>

    <div style="clear:both;"></div>

    <!-- Section B: Keterangan Tempat Tinggal -->
    <div class="section-title">B. KETERANGAN TEMPAT TINGGAL & KONTAK</div>
    <table>
        <tr>
            <td class="col-num">12.</td>
            <td class="col-label">Alamat Lengkap</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['alamat'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">13.</td>
            <td class="col-label">RT / RW / Dusun</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['rt'] ?: '-') ?> / <?= e($siswa['rw'] ?: '-') ?> / <?= e($siswa['dusun'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">14.</td>
            <td class="col-label">Kelurahan / Kecamatan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['kelurahan'] ?: '-') ?> / <?= e($siswa['kecamatan'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">15.</td>
            <td class="col-label">Kabupaten/Kota & Provinsi</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['kota'] ?: 'Kota Palu') ?>, <?= e($siswa['provinsi'] ?: 'Sulawesi Tengah') ?> (Kode Pos: <?= e($siswa['kode_pos'] ?: '-') ?>)</td>
        </tr>
        <tr>
            <td class="col-num">16.</td>
            <td class="col-label">Tinggal Bersama / Transportasi</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['tempat_tinggal'] ?: 'Orang Tua') ?> / <?= e($siswa['moda_transportasi'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">17.</td>
            <td class="col-label">Nomor Telepon / HP Orang Tua</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['no_hp'] ?: '-') ?></td>
        </tr>
    </table>

    <!-- Section C: Keterangan Orang Tua Kandung -->
    <div class="section-title">C. KETERANGAN ORANG TUA KANDUNG / WALI</div>
    <table>
        <tr>
            <td class="col-num">18.</td>
            <td class="col-label">Nama Ayah Kandung</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['nama_ayah'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">19.</td>
            <td class="col-label">NIK Ayah / Tahun Lahir</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['nik_ayah'] ?: '-') ?> / <?= e($siswa['tahun_lahir_ayah'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">20.</td>
            <td class="col-label">Pendidikan & Pekerjaan Ayah</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['pendidikan_ayah'] ?: '-') ?> / <?= e($siswa['pekerjaan_ayah'] ?: '-') ?> (<?= e($siswa['jabatan_ayah'] ?: '-') ?>)</td>
        </tr>
        <tr>
            <td class="col-num">21.</td>
            <td class="col-label">Penghasilan Bulanan Ayah</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['penghasilan_ayah'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">22.</td>
            <td class="col-label">Nama Ibu Kandung</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['nama_ibu'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">23.</td>
            <td class="col-label">NIK Ibu / Tahun Lahir</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['nik_ibu'] ?: '-') ?> / <?= e($siswa['tahun_lahir_ibu'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">24.</td>
            <td class="col-label">Pendidikan & Pekerjaan Ibu</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['pendidikan_ibu'] ?: '-') ?> / <?= e($siswa['pekerjaan_ibu'] ?: '-') ?> (<?= e($siswa['jabatan_ibu'] ?: '-') ?>)</td>
        </tr>
        <tr>
            <td class="col-num">25.</td>
            <td class="col-label">Penghasilan Bulanan Ibu</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['penghasilan_ibu'] ?: '-') ?></td>
        </tr>
    </table>

    <!-- Section D: Riwayat Masuk -->
    <div class="section-title">D. PENERIMAAN DI SEKOLAH INI</div>
    <table>
        <tr>
            <td class="col-num">26.</td>
            <td class="col-label">Diterima di Satuan Pendidikan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><strong><?= e($siswa['jenjang']) ?> IT Bina Insan Palu</strong></td>
        </tr>
        <tr>
            <td class="col-num">27.</td>
            <td class="col-label">Diterima di Kelas / Rombel</td>
            <td class="col-colon">:</td>
            <td class="col-val">Kelas <?= e($siswa['kelas'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">28.</td>
            <td class="col-label">Tahun Ajaran / Semester</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['tahun_ajaran'] ?: '2026/2027') ?> / <?= e($siswa['semester'] ?: 'Ganjil') ?></td>
        </tr>
        <tr>
            <td class="col-num">29.</td>
            <td class="col-label">Asal Sekolah Sebelumnya</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($siswa['asal_sekolah'] ?: '-') ?></td>
        </tr>
    </table>

    <!-- Signature -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <br>
                Orang Tua / Wali Siswa,
                <br><br><br><br>
                ( <strong><?= e($siswa['nama_ayah'] ?: $siswa['nama_ibu'] ?: '.........................................') ?></strong> )
            </td>
            <td style="width: 50%;">
                Palu, <?= date('d F Y') ?><br>
                Kepala Satuan Pendidikan,
                <br><br><br><br>
                ( <strong>......................................................</strong> )<br>
                NIP/NIY: .......................................
            </td>
        </tr>
    </table>

</body>
</html>
