<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Pindah — <?= e($keluar['nama_lengkap'] ?: $keluar['nama']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            line-height: 1.5;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 15pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 13pt;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .header p {
            font-size: 10pt;
            margin: 0;
        }
        .title {
            text-align: center;
            margin: 20px 0;
        }
        .title h3 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
        }
        .title p {
            font-size: 11pt;
            margin: 2px 0 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .col-num { width: 5%; }
        .col-label { width: 35%; }
        .col-colon { width: 3%; }
        .col-val { width: 57%; font-weight: 500; }
        
        .signature-table {
            margin-top: 40px;
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
            📄 Surat Keterangan Pindah Sekolah: <?= e($keluar['nama_lengkap'] ?: $keluar['nama']) ?>
        </div>
        <div>
            <button onclick="window.print()" style="background:#e11d48; color:#fff; border:none; padding:8px 16px; border-radius:6px; font-weight:bold; cursor:pointer; margin-right:8px;">
                🖨️ Cetak Surat (Print)
            </button>
            <button onclick="window.close()" style="background:#64748b; color:#fff; border:none; padding:8px 14px; border-radius:6px; font-weight:bold; cursor:pointer;">
                ✕ Tutup
            </button>
        </div>
    </div>

    <!-- Kop Surat -->
    <div class="header">
        <h1>YAYASAN BINA INSAN PALU</h1>
        <h2>SATUAN PENDIDIKAN <?= strtoupper(e($keluar['jenjang'] ?: 'SD')) ?> IT BINA INSAN PALU</h2>
        <p>Alamat: Jl. Guru Tua No. 12, Kel. Kalukubula, Kec. Sigi Biromaru, Kota Palu, Sulawesi Tengah</p>
        <p>Email: admin@binainsanpalu.sch.id | Website: www.binainsanpalu.sch.id</p>
    </div>

    <div class="title">
        <h3>SURAT KETERANGAN PINDAH SEKOLAH</h3>
        <p>Nomor: <?= e($keluar['nomor_surat'] ?: '421.2/' . sprintf('%03d', $keluar['id']) . '/' . ($keluar['jenjang'] ?: 'SD') . '-BIP/' . date('m/Y')) ?></p>
    </div>

    <p style="text-align: justify; margin-bottom: 12px;">
        Yang bertanda tangan di bawah ini, Kepala Satuan Pendidikan <strong><?= e($keluar['jenjang'] ?: 'SD') ?> IT Bina Insan Palu</strong> menerangkan dengan sesungguhnya bahwa:
    </p>

    <table>
        <tr>
            <td class="col-num">1.</td>
            <td class="col-label">Nama Lengkap Siswa</td>
            <td class="col-colon">:</td>
            <td class="col-val" style="text-transform:uppercase; font-weight:bold;"><?= e($keluar['nama_lengkap'] ?: $keluar['nama']) ?></td>
        </tr>
        <tr>
            <td class="col-num">2.</td>
            <td class="col-label">Nomor Induk Siswa (NIS)</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($keluar['nis'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">3.</td>
            <td class="col-label">Nomor Induk Siswa Nasional (NISN)</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($keluar['nisn'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">4.</td>
            <td class="col-label">Jenis Kelamin</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= ($keluar['jenis_kelamin'] === 'L' || $keluar['jenis_kelamin'] === 'Laki-Laki') ? 'Laki-Laki' : 'Perempuan' ?></td>
        </tr>
        <tr>
            <td class="col-num">5.</td>
            <td class="col-label">Tempat, Tanggal Lahir</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($keluar['tempat_lahir'] ?: '-') ?>, <?= $keluar['tgl_lahir'] ? date('d F Y', strtotime($keluar['tgl_lahir'])) : '-' ?></td>
        </tr>
        <tr>
            <td class="col-num">6.</td>
            <td class="col-label">Tingkat / Kelas Terakhir</td>
            <td class="col-colon">:</td>
            <td class="col-val">Kelas <?= e($keluar['kelas_terakhir'] ?: $keluar['kelas'] ?: '-') ?> (Tahun Ajaran <?= e($keluar['tahun_ajaran'] ?: '-') ?>)</td>
        </tr>
        <tr>
            <td class="col-num">7.</td>
            <td class="col-label">Nama Orang Tua / Wali</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($keluar['nama_ayah'] ?: $keluar['nama_ibu'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="col-num">8.</td>
            <td class="col-label">Alamat Orang Tua</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($keluar['alamat'] ?: '-') ?></td>
        </tr>
    </table>

    <p style="text-align: justify; margin-top: 15px; margin-bottom: 10px;">
        Sesuai dengan surat permohonan kepindahan dari Orang Tua / Wali siswa tertanggal <strong><?= date('d F Y', strtotime($keluar['tanggal_keluar'])) ?></strong>, yang bersangkutan terhitung mulai tanggal tersebut telah dinyatakan <strong>PINDAH (MUTASI KELUAR)</strong> dari <?= e($keluar['jenjang'] ?: 'SD') ?> IT Bina Insan Palu ke:
    </p>

    <table>
        <tr>
            <td class="col-num">•</td>
            <td class="col-label">Sekolah Tujuan</td>
            <td class="col-colon">:</td>
            <td class="col-val" style="font-weight:bold;"><?= e($keluar['sekolah_tujuan'] ?: 'Sekolah yang dituju') ?></td>
        </tr>
        <tr>
            <td class="col-num">•</td>
            <td class="col-label">Alasan Kepindahan</td>
            <td class="col-colon">:</td>
            <td class="col-val"><?= e($keluar['alasan_keluar'] ?: 'Mengikuti perpindahan tempat tinggal orang tua') ?></td>
        </tr>
    </table>

    <p style="text-align: justify; margin-top: 15px;">
        Demikian Surat Keterangan Pindah ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    <!-- Signature -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                Mengetahui,<br>
                Orang Tua / Wali Siswa,
                <br><br><br><br><br>
                ( <strong><?= e($keluar['nama_ayah'] ?: $keluar['nama_ibu'] ?: '.........................................') ?></strong> )
            </td>
            <td style="width: 50%;">
                Palu, <?= date('d F Y', strtotime($keluar['tanggal_keluar'])) ?><br>
                Kepala Satuan Pendidikan,
                <br><br><br><br><br>
                ( <strong>......................................................</strong> )<br>
                NIP/NIY: .......................................
            </td>
        </tr>
    </table>

</body>
</html>
