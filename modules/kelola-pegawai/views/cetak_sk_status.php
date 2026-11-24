<?php
/**
 * Cetak Dokumen Surat Keputusan (SK) Pengangkatan / Kenaikan Status Pegawai
 * Format Resmi F4 (Folio 215mm x 330mm) - Times New Roman 12pt
 */

if (!function_exists('tgl_indo_sk')) {
    function tgl_indo_sk(?string $tanggal): string {
        if (empty($tanggal) || $tanggal === '0000-00-00') return '-';
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $pecahkan = explode('-', date('Y-m-d', strtotime($tanggal)));
        return (int)$pecahkan[2] . ' ' . ($bulan[(int)$pecahkan[1]] ?? '') . ' ' . $pecahkan[0];
    }
}

$namaInstansi = $settings['app_name'] ?? 'SEKOLAH ISLAM TERPADU BINA INSAN PARIPURNA';
$logoUrl = !empty($settings['app_logo']) ? url(ltrim($settings['app_logo'], '/')) : url('public/img/logo.svg');
$kopImg = !empty($settings['sk_kop_image']) ? url(ltrim($settings['sk_kop_image'], '/')) : '';
$footerImg = !empty($settings['sk_footer_image']) ? url(ltrim($settings['sk_footer_image'], '/')) : '';

$statusBaru = $skMeta['status_baru'] ?? ($pegawai['status_kerja'] ?? 'Kontrak');
$statusLama = $skMeta['status_lama'] ?? ($statusBaru === 'Tetap' ? 'Kontrak' : 'Training');
$noSk = !empty($skMeta['no_sk']) ? $skMeta['no_sk'] : ('SK-STATUS/' . date('m') . '/' . date('Y') . '/' . str_pad((string)$pegawai['id'], 3, '0', STR_PAD_LEFT));
$tanggalSk = !empty($skMeta['tanggal_sk']) ? tgl_indo_sk($skMeta['tanggal_sk']) : tgl_indo_sk(date('Y-m-d'));
$tmtMulai = !empty($skMeta['tmt_mulai']) ? tgl_indo_sk($skMeta['tmt_mulai']) : tgl_indo_sk(!empty($pegawai['tmt']) ? $pegawai['tmt'] : date('Y-m-d'));
$kotaSk = 'Palu';
$penandatanganNama = !empty($skMeta['penandatangan_nama']) ? $skMeta['penandatangan_nama'] : 'Ketua Yayasan Bina Insan Paripurna';
$penandatanganJabatan = !empty($skMeta['penandatangan_jabatan']) ? $skMeta['penandatangan_jabatan'] : 'Ketua Yayasan Bina Insan Paripurna';
$namaLengkap = trim(($pegawai['nama'] ?? '-') . (!empty($pegawai['gelar']) ? ', ' . $pegawai['gelar'] : ''));
$ttl = trim(($pegawai['tempat_lahir'] ?? '-') . (!empty($pegawai['tanggal_lahir']) ? ', ' . tgl_indo_sk($pegawai['tanggal_lahir']) : ''));
$judulTentang = ($statusBaru === 'Tetap')
    ? 'PENGANGKATAN PEGAWAI TETAP YAYASAN'
    : 'PENGANGKATAN PEGAWAI KONTRAK YAYASAN';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?= function_exists('renderFaviconTags') ? renderFaviconTags() : '' ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak SK Status - <?= e($namaLengkap) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f1f5f9;
            color: #000;
            font-family: "Times New Roman", Times, Georgia, serif;
            font-size: 12pt;
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .paper {
            background: #ffffff;
            width: 215mm;
            min-height: 330mm;
            padding: 15mm 20mm;
            margin: 20px auto;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .paper-content { flex: 1 0 auto; }
        .sk-kop-container { width: 100%; margin-bottom: 8px; }
        .sk-kop-img { width: 100%; height: auto; display: block; object-fit: cover; }
        .sk-footer-container { width: 100%; margin-top: 8px; }
        .sk-footer-img { width: 100%; height: auto; display: block; object-fit: cover; }
        .double-line {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 5px;
            margin: 6px 0 12px 0;
        }
        .text-sk-justify { text-align: justify; text-justify: inter-word; line-height: 1.5; }
        @media print {
            body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
            .no-print { display: none !important; }
            .paper { width: 100% !important; min-height: 330mm !important; margin: 0 !important; padding: 12mm 16mm !important; box-shadow: none !important; }
            @page { size: 215mm 330mm; margin: 0; }
        }
    </style>
</head>
<body class="antialiased min-h-screen pb-16">

    <!-- Toolbar Cetak -->
    <div class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur text-white px-4 sm:px-8 py-3.5 shadow-xl border-b border-slate-800 flex flex-wrap items-center justify-between gap-4 font-sans">
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-pegawai/statistik') ?>" class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg text-xs font-semibold text-slate-200 transition-colors">
                ← Kembali ke Dashboard
            </a>
            <div class="hidden sm:block border-l border-slate-700 h-5"></div>
            <div>
                <h1 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>📄 SK Perubahan Status Pegawai:</span>
                    <span class="text-amber-400"><?= e($namaLengkap) ?></span>
                </h1>
                <p class="text-[11px] text-slate-400"><?= e($statusLama) ?> → <?= e($statusBaru) ?> • Format F4 (Folio) • Times New Roman 12pt</p>
            </div>
        </div>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg shadow-amber-500/25 transition-all cursor-pointer">
            <span>🖨️ Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Lembar F4 SK -->
    <div class="paper">
        <div class="paper-content">
            <!-- Kop Surat -->
            <?php if (!empty($kopImg)): ?>
                <div class="sk-kop-container">
                    <img src="<?= $kopImg ?>" alt="Kop Surat" class="sk-kop-img">
                </div>
            <?php else: ?>
                <div class="flex items-center gap-4 pb-2">
                    <img src="<?= $logoUrl ?>" alt="Logo" class="w-16 h-16 object-contain">
                    <div class="flex-1 text-center">
                        <h2 class="text-base font-bold uppercase tracking-wide"><?= e($namaInstansi) ?></h2>
                        <p class="text-xs">Yayasan Bina Insan Paripurna &bull; Kota Palu, Sulawesi Tengah</p>
                    </div>
                </div>
                <div class="double-line"></div>
            <?php endif; ?>

            <!-- Judul SK -->
            <div class="text-center my-4">
                <h1 class="text-[13pt] font-bold uppercase tracking-wider leading-snug">
                    SURAT KEPUTUSAN<br>
                    <?= strtoupper(e($penandatanganJabatan)) ?>
                </h1>
                <p class="text-[12pt] font-bold mt-1">NOMOR: <?= e($noSk) ?></p>
                <p class="text-[12pt] font-bold uppercase mt-2">TENTANG</p>
                <p class="text-[12pt] font-bold uppercase mt-1 max-w-xl mx-auto leading-snug">
                    <?= e($judulTentang) ?><br>
                    DI LINGKUNGAN <?= strtoupper(e($namaInstansi)) ?>
                </p>
            </div>

            <!-- Mukadimah -->
            <p class="text-[12pt] italic text-sk-justify mb-3.5">
                Dengan senantiasa memohon rahmat, taufiq, dan ridho Allah Subhanahu Wa Ta'ala, <?= e($penandatanganJabatan) ?>:
            </p>

            <!-- Konsiderans -->
            <div class="text-[12pt] space-y-2.5 mb-4">
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">Menimbang</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        <ol class="list-[lower-alpha] list-inside space-y-1">
                            <li>bahwa berdasarkan hasil evaluasi kinerja, kedisiplinan, serta masa pengabdian pegawai yang bersangkutan selama berstatus sebagai Pegawai <?= e($statusLama) ?>, dipandang memenuhi syarat untuk ditetapkan kenaikan status kepegawaian menjadi Pegawai <?= e($statusBaru) ?>;</li>
                            <li>bahwa untuk tertib administrasi kepegawaian dan kepastian hukum status kerja, perlu ditetapkan melalui Surat Keputusan.</li>
                        </ol>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">Mengingat</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;</li>
                            <li>Anggaran Dasar dan Anggaran Rumah Tangga Yayasan Bina Insan Paripurna;</li>
                            <li>Peraturan Kepegawaian Yayasan Bina Insan Paripurna.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Diktum -->
            <div class="text-center my-3">
                <p class="text-[12pt] font-bold uppercase tracking-widest underline underline-offset-4">MEMUTUSKAN:</p>
            </div>

            <div class="text-[12pt] space-y-2.5 mb-5">
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">Menetapkan</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify font-bold uppercase">
                        KEPUTUSAN PENGANGKATAN STATUS KEPEGAWAIAN MENJADI PEGAWAI <?= strtoupper(e($statusBaru)) ?>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">PERTAMA</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        Terhitung mulai tanggal <strong><?= e($tmtMulai) ?></strong>, mengangkat / menetapkan perubahan status kepegawaian:
                        <table class="w-full mt-2 mb-2 text-[11.5pt]" style="border-collapse: collapse;">
                            <tr>
                                <td class="py-0.5 w-44">Nama Lengkap</td>
                                <td class="py-0.5 w-4">:</td>
                                <td class="py-0.5 font-bold"><?= e($namaLengkap) ?></td>
                            </tr>
                            <tr>
                                <td class="py-0.5">NIY / NIK</td>
                                <td class="py-0.5">:</td>
                                <td class="py-0.5"><?= e($pegawai['niy'] ?: ($pegawai['nik'] ?: '-')) ?></td>
                            </tr>
                            <tr>
                                <td class="py-0.5">Tempat, Tanggal Lahir</td>
                                <td class="py-0.5">:</td>
                                <td class="py-0.5"><?= e($ttl ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-0.5">Unit Tugas</td>
                                <td class="py-0.5">:</td>
                                <td class="py-0.5"><?= e($pegawai['unit_tugas'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-0.5">Jabatan</td>
                                <td class="py-0.5">:</td>
                                <td class="py-0.5"><?= e($pegawai['jabatan'] ?: '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-0.5">Status Sebelumnya</td>
                                <td class="py-0.5">:</td>
                                <td class="py-0.5">Pegawai <?= e($statusLama) ?></td>
                            </tr>
                            <tr>
                                <td class="py-0.5">Status Ditetapkan</td>
                                <td class="py-0.5">:</td>
                                <td class="py-0.5 font-bold uppercase">Pegawai <?= e($statusBaru) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">KEDUA</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        Pegawai yang bersangkutan berhak atas hak-hak kepegawaian sesuai ketentuan Pegawai <?= e($statusBaru) ?> dan wajib melaksanakan tugas dengan penuh amanah, profesional, serta menjunjung tinggi kode etik lembaga.
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">KETIGA</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        Surat Keputusan ini mulai berlaku sejak tanggal ditetapkan, dan apabila di kemudian hari terdapat kekeliruan akan diadakan perbaikan sebagaimana mestinya.
                    </div>
                </div>
            </div>

            <!-- Tanda Tangan -->
            <div class="flex justify-end mt-8">
                <div class="w-72 text-left text-[12pt]">
                    <p>Ditetapkan di : <?= e($kotaSk) ?></p>
                    <p class="mb-2">Pada tanggal : <?= e($tanggalSk) ?></p>
                    <p class="font-bold"><?= e($penandatanganJabatan) ?>,</p>
                    <div class="h-20"></div>
                    <p class="font-bold underline"><?= e($penandatanganNama) ?></p>
                </div>
            </div>
        </div>

        <?php if (!empty($footerImg)): ?>
            <div class="sk-footer-container">
                <img src="<?= $footerImg ?>" alt="Footer Surat" class="sk-footer-img">
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
