<?php
/**
 * Cetak Curriculum Vitae (CV) / Biodata Resmi Pegawai & Guru
 * Kertas: F4 (215mm x 330mm)
 * Font: Times New Roman 11-12pt
 */

if (!function_exists('tgl_indo')) {
    function tgl_indo(?string $tanggal): string {
        if (empty($tanggal) || $tanggal === '0000-00-00') return '-';
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
        return (int)$pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
    }
}

$namaInstansi = $settings['app_name'] ?? 'YAYASAN BINA INSAN PARIPURNA';
$logoUrl = !empty($settings['app_logo']) ? url(ltrim($settings['app_logo'], '/')) : url('public/img/logo.svg');

// Hitung Masa Kerja & Usia
$tglMasuk = !empty($pegawai['tanggal_masuk']) ? $pegawai['tanggal_masuk'] : (!empty($pegawai['tmt']) ? $pegawai['tmt'] : null);
$masaKerja = '-';
if (!empty($tglMasuk)) {
    $diff = (new DateTime($tglMasuk))->diff(new DateTime());
    if ($diff->invert == 0) {
        $parts = [];
        if ($diff->y > 0) $parts[] = $diff->y . ' Tahun';
        if ($diff->m > 0) $parts[] = $diff->m . ' Bulan';
        $masaKerja = empty($parts) ? '< 1 Bulan' : implode(' ', $parts);
    }
}

$usia = '-';
if (!empty($pegawai['tanggal_lahir'])) {
    $diffU = (new DateTime($pegawai['tanggal_lahir']))->diff(new DateTime());
    $usia = $diffU->y . ' Tahun';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV - <?= e($pegawai['nama']) ?><?= !empty($pegawai['gelar']) ? ', ' . e($pegawai['gelar']) : '' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* CSS Khusus Cetak Dokumen CV Resmi - Kertas F4 (215mm x 330mm) */
        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: "Times New Roman", Times, Georgia, serif;
            font-size: 11pt;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .cv-sheet {
            width: 215mm;
            min-height: 330mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 18mm 18mm 20mm 18mm;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;
        }

        .section-header {
            font-family: "Times New Roman", Times, serif;
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e3a8a;
            border-bottom: 1.5px solid #1e3a8a;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .table-data td {
            padding: 3px 4px;
            vertical-align: top;
            font-size: 11pt;
        }

        .table-bordered {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 6px;
        }

        .table-bordered th, .table-bordered td {
            border: 1px solid #334155;
            padding: 4.5px 6px;
            font-size: 10pt;
            vertical-align: middle;
        }

        .table-bordered th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            font-size: 9.5pt;
            color: #0f172a;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .cv-sheet {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 12mm 15mm 15mm 15mm !important;
                box-shadow: none !important;
                page-break-after: auto;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: 215mm 330mm; /* F4 / Folio Size */
                margin: 12mm 15mm 12mm 15mm;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body class="py-6">

    <!-- Floating Print Action Toolbar -->
    <div class="no-print fixed top-5 right-5 z-50 flex items-center gap-2.5 bg-slate-900/90 backdrop-blur text-white px-4 py-2.5 rounded-2xl shadow-2xl border border-slate-700">
        <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-all shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.285.642-2.502 1.927-2.742a2.025 2.025 0 0 1 2.378 1.488m0 0a2.025 2.025 0 0 1 1.488 2.378c-.24 1.285-1.457 2.167-2.742 1.927a2.025 2.025 0 0 1-1.488-2.378Zm0 0-2.488 4.31m9.22-4.31a2.025 2.025 0 0 1 2.378-1.488c1.285.24 2.167 1.457 1.927 2.742a2.025 2.025 0 0 1-2.378 1.488c-1.285-.24-2.167-1.457-1.927-2.742Zm0 0 2.488 4.31M12 4.5v15" />
            </svg>
            <span>🖨️ Cetak / Simpan PDF</span>
        </button>
        <button onclick="window.close()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs transition-all">
            ✕ Tutup
        </button>
    </div>

    <!-- CV Document Sheet Container -->
    <div class="cv-sheet">

        <!-- Kop Header CV -->
        <div class="border-b-2 border-slate-900 pb-3 mb-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <?php if (!empty($logoUrl)): ?>
                        <img src="<?= $logoUrl ?>" alt="Logo" class="h-16 w-16 object-contain">
                    <?php endif; ?>
                    <div>
                        <h2 class="text-[13pt] font-extrabold tracking-wide uppercase text-slate-950">
                            <?= e($namaInstansi) ?>
                        </h2>
                        <p class="text-[10pt] font-semibold text-slate-700">
                            PORTAL SISTEM INFORMASI MANAJEMEN KEPEGAWAIAN
                        </p>
                        <p class="text-[9pt] italic text-slate-500">
                            Jl. Pendidikan No. 1, Kota Palu, Sulawesi Tengah • Telp: (0451) 456789
                        </p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="inline-block px-3 py-1 bg-slate-900 text-white font-bold text-[10pt] uppercase tracking-wider rounded">
                        CURRICULUM VITAE
                    </span>
                    <p class="text-[8.5pt] text-slate-500 font-mono mt-1">ID: BIP-PEG-<?= str_pad($pegawai['id'], 4, '0', STR_PAD_LEFT) ?></p>
                </div>
            </div>
        </div>

        <!-- 1. DATA PRIBADI & PROFIL PEGAWAI -->
        <div class="section-header">
            <span>I. DATA PRIBADI & PROFIL PEGAWAI</span>
        </div>

        <div class="flex items-start gap-5 mb-2">
            <!-- Pas Foto Formal -->
            <div class="w-32 shrink-0 text-center">
                <div class="w-32 h-40 bg-slate-100 border-2 border-slate-800 rounded p-1 flex items-center justify-center overflow-hidden shadow-sm">
                    <?php if (!empty($pegawai['foto'])): ?>
                        <img src="<?= url(ltrim($pegawai['foto'], '/')) ?>" alt="Pas Foto" class="w-full h-full object-cover rounded">
                    <?php else: ?>
                        <div class="text-slate-400 text-xs font-semibold text-center">
                            <span class="text-2xl block mb-1">👤</span>
                            PAS FOTO<br>3 x 4
                        </div>
                    <?php endif; ?>
                </div>
                <span class="text-[9pt] font-bold text-slate-600 block mt-1">
                    <?= $pegawai['is_active'] ? 'STATUS: AKTIF' : 'STATUS: NONAKTIF' ?>
                </span>
            </div>

            <!-- Tabel Detail Pribadi -->
            <div class="flex-1">
                <table class="table-data w-full">
                    <tr>
                        <td class="w-44 font-bold">Nama Lengkap & Gelar</td>
                        <td class="w-3 text-center">:</td>
                        <td class="font-bold text-slate-950 uppercase">
                            <?= e($pegawai['nama']) ?><?= !empty($pegawai['gelar']) ? ', ' . e($pegawai['gelar']) : '' ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">NIY (No. Induk Yayasan)</td>
                        <td class="text-center">:</td>
                        <td class="font-mono font-semibold"><?= e($pegawai['niy'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold">NIK (KTP)</td>
                        <td class="text-center">:</td>
                        <td class="font-mono"><?= e($pegawai['nik'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold">NPWP</td>
                        <td class="text-center">:</td>
                        <td class="font-mono"><?= e($pegawai['npwp'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold">Tempat, Tanggal Lahir</td>
                        <td class="text-center">:</td>
                        <td>
                            <?= e($pegawai['tempat_lahir'] ?: '-') ?>, 
                            <?= !empty($pegawai['tanggal_lahir']) ? tgl_indo($pegawai['tanggal_lahir']) : '-' ?> 
                            (Usia: <?= $usia ?>)
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Jenis Kelamin / Status</td>
                        <td class="text-center">:</td>
                        <td>
                            <?= ($pegawai['jenis_kelamin'] === 'P') ? 'Perempuan' : 'Laki-laki' ?> 
                            • <?= e($pegawai['status_nikah'] ?: 'Belum Menikah') ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Nama Ibu Kandung</td>
                        <td class="text-center">:</td>
                        <td><?= e($pegawai['nama_ibu'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="font-bold">Kontak Pribadi</td>
                        <td class="text-center">:</td>
                        <td>
                            <?= e($pegawai['no_wa'] ?: '-') ?> 
                            <?= !empty($pegawai['email']) ? ' • ' . e($pegawai['email']) : '' ?>
                        </td>
                    </tr>
                    <?php if (!empty($pegawai['kontak_darurat_1_nama']) || !empty($pegawai['kontak_darurat_2_nama'])): ?>
                    <tr>
                        <td class="font-bold">Kontak Darurat</td>
                        <td class="text-center">:</td>
                        <td>
                            <?php if (!empty($pegawai['kontak_darurat_1_nama'])): ?>
                                1. <strong><?= e($pegawai['kontak_darurat_1_nama']) ?></strong> (<?= e($pegawai['kontak_darurat_1_hubungan'] ?: 'Keluarga') ?>) - <?= e($pegawai['kontak_darurat_1_no_hp'] ?: '-') ?>
                            <?php endif; ?>
                            <?php if (!empty($pegawai['kontak_darurat_2_nama'])): ?>
                                <br>2. <strong><?= e($pegawai['kontak_darurat_2_nama']) ?></strong> (<?= e($pegawai['kontak_darurat_2_hubungan'] ?: 'Kerabat') ?>) - <?= e($pegawai['kontak_darurat_2_no_hp'] ?: '-') ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="font-bold">Tanggal Masuk Kerja</td>
                        <td class="text-center">:</td>
                        <td class="font-semibold text-slate-900">
                            <?= !empty($tglMasuk) ? tgl_indo($tglMasuk) : '-' ?> 
                            <span class="text-slate-600 font-normal">(Masa Kerja: <strong><?= $masaKerja ?></strong>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-bold">Alamat KTP & Domisili</td>
                        <td class="text-center">:</td>
                        <td>
                            <?= e($pegawai['alamat_ktp'] ?: '-') ?>
                            <?= !empty($pegawai['kel_ktp']) ? ', Kel. ' . e($pegawai['kel_ktp']) : '' ?>
                            <?= !empty($pegawai['kec_ktp']) ? ', Kec. ' . e($pegawai['kec_ktp']) : '' ?>
                            <?= !empty($pegawai['kab_kota_ktp']) ? ', ' . e($pegawai['kab_kota_ktp']) : '' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- 2. RIWAYAT PENDIDIKAN FORMAL -->
        <div class="section-header">
            <span>II. RIWAYAT PENDIDIKAN FORMAL</span>
        </div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th class="w-24">Jenjang</th>
                    <th>Nama Institusi / Sekolah / Perguruan Tinggi</th>
                    <th>Jurusan / Program Studi</th>
                    <th class="w-24">Tahun Lulus</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendidikan)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-2 italic text-slate-500">Data riwayat pendidikan belum diinput.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($pendidikan as $pend): ?>
                        <tr>
                            <td class="text-center font-bold"><?= $no++ ?></td>
                            <td class="text-center font-semibold"><?= e($pend['jenjang']) ?></td>
                            <td class="font-semibold"><?= e($pend['institusi']) ?></td>
                            <td><?= e($pend['jurusan'] ?: '-') ?></td>
                            <td class="text-center font-mono"><?= e($pend['tahun_lulus'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 3. SUSUNAN ANGGOTA KELUARGA -->
        <div class="section-header">
            <span>III. SUSUNAN ANGGOTA KELUARGA</span>
        </div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th class="w-28">Hubungan</th>
                    <th>Nama Lengkap Anggota Keluarga</th>
                    <th class="w-14">L/P</th>
                    <th>Tempat & Tgl Lahir (Usia)</th>
                    <th class="w-24">Pendidikan</th>
                    <th>Pekerjaan</th>
                    <th class="w-28">No. HP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($keluargaList)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-2 italic text-slate-500">Belum ada data anggota keluarga yang tercatat.</td>
                    </tr>
                <?php else: ?>
                    <?php $noK = 1; foreach ($keluargaList as $kItem): ?>
                        <?php
                        $usiaK = '';
                        if (!empty($kItem['tanggal_lahir'])) {
                            $dU = (new DateTime($kItem['tanggal_lahir']))->diff(new DateTime());
                            $usiaK = ' (' . $dU->y . ' Thn)';
                        }
                        ?>
                        <tr>
                            <td class="text-center font-bold"><?= $noK++ ?></td>
                            <td class="font-bold text-slate-900"><?= e($kItem['hubungan']) ?></td>
                            <td class="font-semibold"><?= e($kItem['nama']) ?></td>
                            <td class="text-center"><?= ($kItem['jenis_kelamin'] === 'P') ? 'P' : 'L' ?></td>
                            <td>
                                <?= e($kItem['tempat_lahir'] ?: '-') ?><?= !empty($kItem['tanggal_lahir']) ? ', ' . tgl_indo($kItem['tanggal_lahir']) . $usiaK : '' ?>
                            </td>
                            <td class="text-center"><?= e($kItem['pendidikan_terakhir'] ?: '-') ?></td>
                            <td><?= e($kItem['pekerjaan'] ?: '-') ?></td>
                            <td class="font-mono text-[9pt]"><?= e($kItem['no_hp'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 4. KEAHLIAN & KETERAMPILAN (SKILL & COMPETENCIES) -->
        <div class="section-header">
            <span>IV. KEAHLIAN & KETERAMPILAN (SKILL & KOMPETENSI)</span>
        </div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th class="w-44">Kategori Bidang</th>
                    <th>Nama Keahlian / Kompetensi</th>
                    <th class="w-32">Tingkat Penguasaan</th>
                    <th>Keterangan / Portofolio Singkat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($skillList)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-2 italic text-slate-500">Belum ada catatan keahlian / keterampilan.</td>
                    </tr>
                <?php else: ?>
                    <?php $noS = 1; foreach ($skillList as $sItem): ?>
                        <tr>
                            <td class="text-center font-bold"><?= $noS++ ?></td>
                            <td class="font-semibold text-slate-700"><?= e($sItem['kategori']) ?></td>
                            <td class="font-bold text-slate-950"><?= e($sItem['nama_skill']) ?></td>
                            <td class="text-center font-bold">
                                <?= e($sItem['tingkat_keahlian']) ?>
                            </td>
                            <td class="text-[9.5pt]"><?= e($sItem['deskripsi'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 5. TUGAS & AMANAH AKTIF SAAT INI -->
        <div class="section-header">
            <span>V. PENUGASAN & JABATAN AKTIF (TERKINI)</span>
        </div>
        <div class="bg-slate-50 border border-slate-300 rounded p-3 text-[10.5pt]">
            <div class="grid grid-cols-2 gap-x-6 gap-y-1.5">
                <div>
                    <span class="text-slate-600 font-bold block text-[9.5pt]">UNIT TUGAS PENEMPATAN:</span>
                    <p class="font-extrabold text-slate-900 uppercase text-[11pt]"><?= e($pegawai['unit_tugas'] ?: 'Yayasan Bina Insan Paripurna') ?></p>
                </div>
                <div>
                    <span class="text-slate-600 font-bold block text-[9.5pt]">JABATAN / AMANAH TUGAS:</span>
                    <p class="font-extrabold text-indigo-900 uppercase text-[11pt]"><?= e($pegawai['jabatan'] ?: 'Guru / Staf') ?></p>
                </div>
                <div class="mt-1">
                    <span class="text-slate-600 font-bold block text-[9.5pt]">IKATAN STATUS KERJA:</span>
                    <p class="font-semibold text-slate-800"><?= e($pegawai['status_kerja'] ?: 'Pegawai Tetap') ?> (<?= e($pegawai['jenis_pegawai'] ?: 'Pendidik') ?>)</p>
                </div>
                <div class="mt-1">
                    <span class="text-slate-600 font-bold block text-[9.5pt]">STATUS DAPODIK:</span>
                    <p class="font-semibold text-slate-800"><?= e($pegawai['status_dapodik'] ?: 'Terdaftar') ?></p>
                </div>
            </div>
            <?php if (!empty($activePenugasan)): ?>
                <div class="mt-2.5 pt-2 border-t border-slate-200 text-[10pt] text-slate-700">
                    <strong>Dasar SK Penugasan Terkini:</strong> <?= e($activePenugasan['no_sk'] ?: 'SK Penugasan Yayasan') ?> 
                    • Periode: <?= tgl_indo($activePenugasan['tmt_mulai']) ?> <?= !empty($activePenugasan['tst_selesai']) ? 's/d ' . tgl_indo($activePenugasan['tst_selesai']) : '(Berlaku Aktif)' ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- 6. RIWAYAT KARIR & PENUGASAN -->
        <div class="section-header">
            <span>VI. RIWAYAT KARIR & PENUGASAN</span>
        </div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th>Unit Tugas</th>
                    <th>Jabatan / Amanah</th>
                    <th>Nomor SK / Dasar Tugas</th>
                    <th class="w-36">Periode Penugasan</th>
                    <th>Penandatangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($karirList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-2 italic text-slate-500">Belum ada riwayat karir terdahulu.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($karirList as $k): ?>
                        <tr>
                            <td class="text-center font-bold"><?= $no++ ?></td>
                            <td class="font-semibold"><?= e($k['unit_tugas'] ?: 'Yayasan BIP') ?></td>
                            <td class="font-bold text-slate-900"><?= e($k['jabatan']) ?></td>
                            <td class="font-mono text-[9pt]"><?= e($k['no_sk'] ?: '-') ?></td>
                            <td class="text-center text-[9.5pt]">
                                <?= !empty($k['tmt_mulai']) ? date('d/m/Y', strtotime($k['tmt_mulai'])) : '-' ?>
                                <?= !empty($k['tst_selesai']) ? ' s/d ' . date('d/m/Y', strtotime($k['tst_selesai'])) : ' s/d Sekarang' ?>
                            </td>
                            <td class="text-[9.5pt]"><?= e($k['penandatangan_sk'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 7. RIWAYAT PRESTASI & PENGHARGAAN -->
        <div class="section-header">
            <span>VII. RIWAYAT PRESTASI & PENGHARGAAN</span>
        </div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th>Nama Prestasi / Kegiatan Kejuaraan</th>
                    <th class="w-24">Tingkat</th>
                    <th class="w-28">Peringkat/Juara</th>
                    <th>Penyelenggara</th>
                    <th class="w-20">Tahun</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($prestasiList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-2 italic text-slate-500">Belum ada catatan prestasi atau penghargaan.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($prestasiList as $pr): ?>
                        <tr>
                            <td class="text-center font-bold"><?= $no++ ?></td>
                            <td class="font-bold"><?= e($pr['nama_prestasi']) ?></td>
                            <td class="text-center"><?= e($pr['tingkat']) ?></td>
                            <td class="text-center font-bold text-slate-900"><?= e($pr['peringkat']) ?></td>
                            <td><?= e($pr['penyelenggara']) ?></td>
                            <td class="text-center font-mono font-bold"><?= e($pr['tahun']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 8. RIWAYAT PELATIHAN, DIKLAT & SERTIFIKASI -->
        <div class="section-header">
            <span>VIII. RIWAYAT PELATIHAN, DIKLAT & SERTIFIKASI</span>
        </div>
        <table class="table-bordered">
            <thead>
                <tr>
                    <th class="w-10">No</th>
                    <th>Nama Kegiatan Pelatihan / Diklat / Workshop</th>
                    <th class="w-32">Kategori & Peran</th>
                    <th>Penyelenggara</th>
                    <th class="w-24">Waktu / JP</th>
                    <th>No. Sertifikat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pelatihanList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-2 italic text-slate-500">Belum ada catatan kegiatan pelatihan atau diklat.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($pelatihanList as $pl): ?>
                        <tr>
                            <td class="text-center font-bold"><?= $no++ ?></td>
                            <td class="font-bold text-slate-900"><?= e($pl['nama_pelatihan']) ?></td>
                            <td class="text-center text-[9pt]">
                                <span class="font-semibold"><?= e($pl['jenis_pelatihan']) ?></span><br>
                                <span class="text-slate-500">(<?= e($pl['peran']) ?>)</span>
                            </td>
                            <td><?= e($pl['penyelenggara']) ?></td>
                            <td class="text-center text-[9.5pt]">
                                <?= date('d/m/Y', strtotime($pl['tanggal_mulai'])) ?><br>
                                <?php if (!empty($pl['jumlah_jam']) && $pl['jumlah_jam'] > 0): ?>
                                    <strong class="text-slate-800"><?= $pl['jumlah_jam'] ?> JP</strong>
                                <?php endif; ?>
                            </td>
                            <td class="font-mono text-[9pt]"><?= e($pl['nomor_sertifikat'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 9. PENGESAHAN & TANDA TANGAN -->
        <div class="mt-8 pt-4 border-t border-slate-300 text-[11pt]">
            <p class="mb-4 text-justify">
                Demikian Curriculum Vitae (Daftar Riwayat Hidup) ini saya buat dengan sebenarnya sesuai dengan data dan dokumen resmi yang sah, untuk dapat dipergunakan sebagaimana mestinya.
            </p>

            <div class="grid grid-cols-2 gap-8 text-center mt-6">
                <!-- Tanda Tangan Pegawai -->
                <div>
                    <p class="text-slate-600">Pegawai / Guru yang Bersangkutan,</p>
                    <div class="h-20"></div>
                    <p class="font-bold underline text-slate-950 uppercase">
                        <?= e($pegawai['nama']) ?><?= !empty($pegawai['gelar']) ? ', ' . e($pegawai['gelar']) : '' ?>
                    </p>
                    <p class="text-[9.5pt] font-mono text-slate-600">NIY: <?= e($pegawai['niy'] ?: '-') ?></p>
                </div>

                <!-- Mengetahui Pimpinan Yayasan / Lembaga -->
                <div>
                    <p class="text-slate-600">Kota Palu, <?= tgl_indo(date('Y-m-d')) ?></p>
                    <p class="text-slate-600 font-semibold">Mengetahui, Pimpinan Yayasan / Lembaga</p>
                    <div class="h-16"></div>
                    <p class="font-bold underline text-slate-950 uppercase">
                        <?= e($penandatanganNama ?? 'H. Ahmad Dahlan, S.Pd., M.M.') ?>
                    </p>
                    <p class="text-[9.5pt] font-mono text-slate-600"><?= e($penandatanganJabatan ?? 'Ketua Yayasan Bina Insan Paripurna') ?></p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
