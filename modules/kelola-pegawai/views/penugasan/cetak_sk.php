<?php
/**
 * Cetak Dokumen Surat Keputusan (SK) Penugasan Pegawai
 * Format Resmi dengan Kop Surat, Konsiderans, Diktum, Tabel Lampiran Nama-Nama Pegawai, dan Pengesahan / Tanda Tangan
 */

// Helper format tanggal Indonesia
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

$namaInstansi = $settings['app_name'] ?? 'SEKOLAH ISLAM TERPADU BINA INSAN PARIPURNA';
$logoUrl = !empty($settings['app_logo']) ? url(ltrim($settings['app_logo'], '/')) : url('public/img/logo.svg');
$noSk = !empty($grup['no_sk']) ? $grup['no_sk'] : '001/SK-TUGAS/YYS/' . date('Y');
$tanggalSk = !empty($grup['tanggal_sk']) ? tgl_indo($grup['tanggal_sk']) : tgl_indo(date('Y-m-d'));
$tmtMulai = !empty($grup['tmt_mulai']) ? tgl_indo($grup['tmt_mulai']) : tgl_indo(date('Y-m-d'));
$tstSelesai = !empty($grup['tst_selesai']) ? tgl_indo($grup['tst_selesai']) : null;
$kotaSk = (!empty($grup['kota_sk']) && $grup['kota_sk'] !== 'Makassar') ? $grup['kota_sk'] : 'Palu';
$penandatanganNama = !empty($grup['penandatangan_nama']) ? $grup['penandatangan_nama'] : 'H. Ahmad Dahlan, S.Pd., M.M.';
$penandatanganJabatan = !empty($grup['penandatangan_jabatan']) ? $grup['penandatangan_jabatan'] : 'Ketua Yayasan Bina Insan Paripurna';
$penandatanganNip = !empty($grup['penandatangan_nip']) ? $grup['penandatangan_nip'] : 'NIY. 19850101 201001 1 001';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* CSS Khusus Dokumen Cetak SK - Kertas F4 & Font 12pt Times New Roman */
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
            padding: 15mm 20mm 15mm 20mm;
            margin: 20px auto;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 12pt;
            line-height: 1.45;
        }

        .paper-content {
            flex: 1 0 auto;
        }

        /* Kop Surat Rapat Kiri Kanan & Bawah */
        .sk-kop-container {
            width: 100%;
            margin-top: 0;
            margin-bottom: 6px;
            padding: 0;
        }

        .sk-kop-img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            object-fit: cover;
        }

        /* Footer Surat Rapat Kiri Kanan */
        .sk-footer-container {
            width: 100%;
            margin-top: 6px;
            padding: 0;
        }

        .sk-footer-img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            object-fit: cover;
        }

        .double-line {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 5px;
            margin-top: 4px;
            margin-bottom: 10px;
        }

        .text-sk-justify {
            text-align: justify;
            text-justify: inter-word;
            line-height: 1.45;
        }

        .table-sk {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            font-family: "Times New Roman", Times, Georgia, serif;
            font-size: 11pt;
        }

        .table-sk th, .table-sk td {
            border: 1px solid #000;
            padding: 5px 6px;
        }

        .page-break {
            page-break-before: always;
            break-before: page;
        }

        /* Mode Cetak Printer F4 (Folio 215mm x 330mm) */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-family: "Times New Roman", Times, Georgia, serif !important;
                font-size: 12pt !important;
                line-height: 1.45 !important;
            }
            .no-print {
                display: none !important;
            }
            .paper {
                width: 100% !important;
                min-height: 330mm !important;
                margin: 0 !important;
                padding: 12mm 15mm 12mm 15mm !important;
                box-shadow: none !important;
                border: none !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                font-size: 12pt !important;
                line-height: 1.45 !important;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
            }
            @page {
                size: 215mm 330mm; /* Standar Kertas F4 / Folio */
                margin: 0;
            }
        }
    </style>
</head>
<body class="antialiased min-h-screen text-slate-900 pb-16">

    <!-- ============================================================== -->
    <!-- TOOLBAR AKSI ATAS (NO PRINT) -->
    <!-- ============================================================== -->
    <div class="no-print sticky top-0 z-50 bg-slate-900/95 backdrop-blur text-white px-4 sm:px-8 py-3.5 shadow-xl border-b border-slate-800 flex flex-wrap items-center justify-between gap-4 font-sans">
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grup['id']) ?>" class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg text-xs font-semibold text-slate-200 transition-colors">
                ← Kembali ke Grup
            </a>
            <div class="hidden sm:block border-l border-slate-700 h-5"></div>
            <div>
                <h1 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>📄 Cetak SK Penugasan:</span>
                    <span class="text-amber-400"><?= e($grup['nama_grup']) ?></span>
                </h1>
                <p class="text-[11px] text-slate-400"><?= count($penugasan) ?> Pegawai Ditugaskan • Format F4 (Folio) • Times New Roman 12pt Rata Kiri Kanan</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button type="button" onclick="toggleDrawer()" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-bold transition-all border border-slate-700">
                <span>⚙️ Atur Kop, Footer & Penandatangan</span>
            </button>

            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg shadow-amber-500/25 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                <span>🖨️ Cetak / Print Dokumen</span>
            </button>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- DRAWER / PANEL PENGATURAN SK CEPAT (NO PRINT) -->
    <!-- ============================================================== -->
    <div id="settingsDrawer" class="no-print hidden bg-slate-800 text-white border-b border-slate-700 px-6 py-5 font-sans transition-all">
        <div class="max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-amber-400 flex items-center gap-2">
                    <span>✍️ Pengaturan Surat Keputusan & Penandatangan</span>
                </h3>
                <button type="button" onclick="toggleDrawer()" class="text-slate-400 hover:text-white text-xs">✕ Tutup</button>
            </div>
            
            <form id="formSkMeta" action="<?= url('kelola-pegawai/penugasan/grup/' . $grup['id'] . '/update-sk-meta') ?>" method="POST" enctype="multipart/form-data" onsubmit="submitSkMeta(event, this)">
                <?= CSRF::field() ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <!-- Upload Gambar Kop Surat -->
                    <div class="sm:col-span-2 bg-slate-900/90 p-3.5 rounded-xl border border-amber-500/30">
                        <label class="block font-bold text-amber-300 mb-1">🖼️ Unggah Gambar Kop Surat (Header)</label>
                        <p class="text-[11px] text-slate-400 mb-2">Banner Kop Surat di bagian paling atas dokumen SK (rapat kiri-kanan).</p>
                        <?php if (!empty($grup['file_kop'])): ?>
                            <div class="flex items-center gap-2 mb-2 p-1.5 bg-slate-800 rounded-lg border border-slate-700">
                                <img src="<?= url(ltrim($grup['file_kop'], '/')) ?>" alt="Kop Surat" class="h-8 max-w-[120px] object-contain bg-white rounded p-0.5">
                                <span class="text-[10px] text-emerald-400 font-semibold">✓ Kop aktif</span>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="file_kop" accept="image/png,image/jpeg,image/jpg,image/webp" class="w-full px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white text-xs focus:border-amber-400">
                    </div>

                    <!-- Upload Gambar Footer Surat -->
                    <div class="sm:col-span-2 bg-slate-900/90 p-3.5 rounded-xl border border-sky-500/30">
                        <label class="block font-bold text-sky-300 mb-1">📑 Unggah Gambar Footer Surat (Footer)</label>
                        <p class="text-[11px] text-slate-400 mb-2">Banner Footer resmi di bagian paling bawah dokumen SK.</p>
                        <?php if (!empty($grup['file_footer'])): ?>
                            <div class="flex items-center gap-2 mb-2 p-1.5 bg-slate-800 rounded-lg border border-slate-700">
                                <img src="<?= url(ltrim($grup['file_footer'], '/')) ?>" alt="Footer Surat" class="h-8 max-w-[120px] object-contain bg-white rounded p-0.5">
                                <span class="text-[10px] text-emerald-400 font-semibold">✓ Footer aktif</span>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="file_footer" accept="image/png,image/jpeg,image/jpg,image/webp" class="w-full px-3 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white text-xs focus:border-sky-400">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nomor SK</label>
                        <input type="text" name="no_sk" value="<?= e($grup['no_sk'] ?? '') ?>" placeholder="001/SK-TUGAS/YYS/<?= date('Y') ?>" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:border-amber-400">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tanggal SK</label>
                        <input type="date" name="tanggal_sk" value="<?= e($grup['tanggal_sk'] ?? date('Y-m-d')) ?>" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:border-amber-400">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Kota Penetapan</label>
                        <input type="text" name="kota_sk" value="<?= e((!empty($grup['kota_sk']) && $grup['kota_sk'] !== 'Makassar') ? $grup['kota_sk'] : 'Palu') ?>" placeholder="Palu" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:border-amber-400">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Nama Penandatangan</label>
                        <input type="text" name="penandatangan_nama" value="<?= e($grup['penandatangan_nama'] ?? '') ?>" placeholder="H. Ahmad Dahlan, S.Pd., M.M." class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:border-amber-400">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-semibold text-slate-300 mb-1">Jabatan Penandatangan</label>
                        <input type="text" name="penandatangan_jabatan" value="<?= e($grup['penandatangan_jabatan'] ?? 'Ketua Yayasan Bina Insan Paripurna') ?>" placeholder="Ketua Yayasan Bina Insan Paripurna" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:border-amber-400">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-semibold text-slate-300 mb-1">NIP / NIY Penandatangan</label>
                        <input type="text" name="penandatangan_nip" value="<?= e($grup['penandatangan_nip'] ?? '') ?>" placeholder="NIY. 19850101 201001 1 001" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-xs focus:border-amber-400">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-4 pt-3 border-t border-slate-700">
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs transition-colors">
                        💾 Simpan & Perbarui Dokumen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- LEMBAR 1: SURAT KEPUTUSAN UTAMA (KERTAS F4) -->
    <!-- ============================================================== -->
    <div class="paper">
        <div class="paper-content">
            <!-- KOP SURAT RESMI (RAPAT KIRI KANAN & BAWAH) -->
            <?php if (!empty($grup['file_kop'])): ?>
                <div class="sk-kop-container">
                    <img src="<?= url(ltrim($grup['file_kop'], '/')) ?>" alt="Kop Surat Resmi" class="sk-kop-img">
                </div>
            <?php else: ?>
                <div class="text-center relative">
                    <!-- Logo Instansi -->
                    <div class="absolute left-0 top-0 w-24 h-24 flex items-center justify-center">
                        <img src="<?= $logoUrl ?>" alt="Logo" class="max-w-full max-h-full object-contain" onerror="this.style.display='none'">
                    </div>

                    <!-- Teks Kop Surat -->
                    <div class="px-24">
                        <h3 class="text-[13pt] font-bold tracking-wider text-black uppercase">YAYASAN BINA INSAN PARIPURNA</h3>
                        <h2 class="text-[15pt] font-extrabold tracking-wide text-black uppercase mt-0.5"><?= e($namaInstansi) ?></h2>
                        <p class="text-[10pt] font-medium text-slate-800 mt-1">
                            PAUD • SEKOLAH DASAR (SD) • SEKOLAH MENENGAH PERTAMA (SMP) • SMA IT
                        </p>
                        <p class="text-[9.5pt] text-slate-700 mt-0.5">
                            Kota Palu, Sulawesi Tengah | Telp: (0451) 889977
                        </p>
                        <p class="text-[9.5pt] text-slate-700">
                            Email: sekretariat@binainsanparipurna.sch.id • Website: www.binainsanparipurna.sch.id
                        </p>
                    </div>
                </div>

                <!-- Garis Ganda Kop Surat -->
                <div class="double-line"></div>
            <?php endif; ?>

            <!-- JUDUL SK -->
            <div class="text-center my-3.5">
                <h1 class="text-[13pt] font-bold uppercase tracking-wider text-black leading-snug">
                    SURAT KEPUTUSAN<br>
                    <?= strtoupper(e($penandatanganJabatan)) ?>
                </h1>
                <p class="text-[12pt] font-bold text-black mt-1">
                    NOMOR: <?= e($noSk) ?>
                </p>
                <p class="text-[12pt] font-bold uppercase text-black mt-2">
                    TENTANG
                </p>
                <p class="text-[12pt] font-bold uppercase text-black mt-1 max-w-xl mx-auto leading-snug">
                    PENETAPAN PEMBAGIAN TUGAS DAN PENUGASAN PEGAWAI<br>
                    <?= strtoupper(e($grup['nama_grup'])) ?>
                </p>
            </div>

            <!-- PEMBUKA / MUKADIMAH -->
            <p class="text-[12pt] italic text-sk-justify text-slate-900 mb-3.5 leading-relaxed">
                Dengan senantiasa memohon rahmat, taufiq, dan ridho Allah Subhanahu Wa Ta'ala, <?= e($penandatanganJabatan) ?>:
            </p>

            <!-- KONSIDERANS: MENIMBANG & MENGINGAT -->
            <div class="text-[12pt] space-y-2.5 leading-relaxed mb-3.5">
                <!-- Menimbang -->
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">Menimbang</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        <ol class="list-[lower-alpha] list-inside space-y-1">
                            <li>bahwa demi kelancaran, efektivitas, dan ketertiban proses pembelajaran dan administrasi lembaga, perlu ditetapkan pembagian tugas dan penugasan pegawai;</li>
                            <li>bahwa nama-nama yang tercantum dalam lampiran keputusan ini dipandang cakap dan memenuhi syarat untuk diserahi tugas dan tanggung jawab sebagaimana dimaksud;</li>
                            <li>bahwa berdasarkan pertimbangan sebagaimana dimaksud pada huruf a dan b, perlu menetapkan Surat Keputusan tentang Penugasan Pegawai.</li>
                        </ol>
                    </div>
                </div>

                <!-- Mengingat -->
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">Mengingat</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Undang-Undang Nomor 20 Tahun 2003 tentang Sistem Pendidikan Nasional;</li>
                            <li>Undang-Undang Nomor 14 Tahun 2005 tentang Guru dan Dosen;</li>
                            <li>Anggaran Dasar dan Anggaran Rumah Tangga Yayasan Bina Insan Paripurna;</li>
                            <li>Peraturan Kepegawaian dan Kode Etik Pegawai di lingkungan Yayasan Bina Insan Paripurna.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- DIKTUM MEMUTUSKAN -->
            <div class="text-center my-2.5">
                <p class="text-[12pt] font-bold uppercase tracking-widest text-black underline underline-offset-4">
                    MEMUTUSKAN:
                </p>
            </div>

            <div class="text-[12pt] space-y-2 leading-relaxed mb-4">
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">Menetapkan</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify font-bold uppercase">
                        KEPUTUSAN TENTANG PENUGASAN PEGAWAI PADA <?= strtoupper(e($grup['nama_grup'])) ?>
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">PERTAMA</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        Menugaskan nama-nama pegawai sebagaimana tercantum dalam <strong>Lampiran Surat Keputusan</strong> ini dengan unit tugas, jabatan, serta rincian penugasan masing-masing.
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">KEDUA</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        Pegawai yang bersangkutan wajib melaksanakan tugas dengan penuh rasa tanggung jawab, menjunjung tinggi kode etik, amanah, dan berpedoman pada visi serta misi lembaga.
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">KETIGA</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        Surat Keputusan ini berlaku terhitung mulai tanggal <strong><?= $tmtMulai ?></strong> <?= $tstSelesai ? 'sampai dengan tanggal <strong>' . $tstSelesai . '</strong>' : 'sampai dengan adanya ketetapan penugasan baru' ?>.
                    </div>
                </div>
                <div class="flex items-start">
                    <div class="w-28 shrink-0 font-bold">KEEMPAT</div>
                    <div class="w-4 shrink-0 text-center font-bold">:</div>
                    <div class="flex-1 text-sk-justify">
                        Apabila di kemudian hari terdapat kekeliruan dalam penetapan Surat Keputusan ini, maka akan diadakan perbaikan dan penyesuaian sebagaimana mestinya.
                    </div>
                </div>
            </div>

            <!-- LEMBAR TANDA TANGAN / PENGESAHAN (FOOTER TTD) -->
            <div class="mt-4 pt-1 flex justify-end text-[12pt]">
                <div class="w-80 text-left">
                    <p>Ditetapkan di : <?= e($kotaSk) ?></p>
                    <p>Pada tanggal : <?= $tanggalSk ?></p>
                    
                    <div class="mt-2 font-bold text-black">
                        <p><?= e($penandatanganJabatan) ?>,</p>
                    </div>

                    <!-- Tanda Tangan & QR Code Autentikasi -->
                    <div class="my-2 flex items-center gap-3">
                        <div class="w-16 h-16 border border-slate-300 p-1 rounded bg-slate-50 flex flex-col items-center justify-center shrink-0" title="Validasi Dokumen Digital">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode(url('kelola-pegawai/penugasan/grup/' . $grup['id'])) ?>" alt="QR" class="w-full h-full object-contain">
                        </div>
                        <div class="text-[9pt] text-slate-600 leading-tight">
                            <p class="font-bold text-slate-800">DOKUMEN RESMI</p>
                            <p>Telah ditandatangani secara sah & tercatat pada sistem kepegawaian.</p>
                        </div>
                    </div>

                    <div class="mt-1">
                        <p class="font-bold text-black text-[12pt] underline underline-offset-2"><?= e($penandatanganNama) ?></p>
                        <p class="text-slate-800 text-[11pt] mt-0.5"><?= e($penandatanganNip) ?></p>
                    </div>
                </div>
            </div>

            <!-- TEMBUSAN -->
            <div class="mt-3.5 pt-2 border-t border-slate-300 text-[10pt] text-slate-700">
                <p class="font-bold mb-0.5">Tembusan Yth :</p>
                <ol class="list-decimal list-inside space-y-0.5">
                    <li>Pembina Yayasan Bina Insan Paripurna</li>
                    <li>Kepala Divisi Kepegawaian & Keuangan</li>
                    <li>Masing-masing Pegawai yang bersangkutan</li>
                    <li>Arsip / Dokumen</li>
                </ol>
            </div>
        </div>

        <!-- FOOTER SURAT RESMI (BANNER GAMBAR DI BAWAH SK) -->
        <?php if (!empty($grup['file_footer'])): ?>
            <div class="sk-footer-container">
                <img src="<?= url(ltrim($grup['file_footer'], '/')) ?>" alt="Footer Surat Resmi" class="sk-footer-img">
            </div>
        <?php endif; ?>

    </div>

    <!-- ============================================================== -->
    <!-- LEMBAR 2+: LAMPIRAN DAFTAR NAMA PEGAWAI YANG DI-SK-KAN (F4) -->
    <!-- ============================================================== -->
    <div class="paper page-break">
        <div class="paper-content">
            <!-- HEADER LAMPIRAN -->
            <div class="flex justify-between items-start text-[11pt] mb-4 pb-2.5 border-b-2 border-black">
                <div>
                    <span class="inline-block px-2 py-0.5 bg-slate-100 font-bold text-[9pt] rounded mb-1 border border-slate-300">LAMPIRAN RESMI</span>
                    <h3 class="font-bold text-[12pt] text-black">SURAT KEPUTUSAN PENUGASAN PEGAWAI</h3>
                    <p class="text-slate-800 text-[11pt]"><?= e($grup['nama_grup']) ?></p>
                </div>
                <div class="text-right text-[11pt] space-y-0.5">
                    <p><span class="text-slate-600">Nomor :</span> <span class="font-bold text-black"><?= e($noSk) ?></span></p>
                    <p><span class="text-slate-600">Tanggal :</span> <span class="font-bold text-black"><?= $tanggalSk ?></span></p>
                </div>
            </div>

            <div class="text-center mb-3.5">
                <h2 class="text-[13pt] font-bold uppercase text-black tracking-wide">
                    DAFTAR NAMA PEGAWAI DAN JABATAN PENUGASAN
                </h2>
                <p class="text-[11pt] text-slate-700 mt-0.5">Semester <?= e($grup['semester']) ?> • Periode TMT: <?= $tmtMulai ?> <?= $tstSelesai ? 's/d ' . $tstSelesai : '' ?></p>
            </div>

            <!-- TABEL NAMA-NAMA YANG DI-SK-KAN -->
            <div class="overflow-x-auto mb-4">
                <table class="w-full table-sk text-[11pt]">
                    <thead>
                        <tr class="bg-slate-100 font-bold text-black">
                            <th class="p-2 text-center w-10">NO</th>
                            <th class="p-2 text-left">NAMA PEGAWAI & GELAR</th>
                            <th class="p-2 text-center w-40">NIY / NIP</th>
                            <th class="p-2 text-left">JABATAN PENUGASAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-300">
                        <?php if (empty($penugasan)): ?>
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-500 italic">
                                    Belum ada data pegawai yang ditugaskan dalam grup penugasan ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($penugasan as $row): ?>
                                <tr>
                                    <td class="p-2 text-center"><?= $no++ ?></td>
                                    <td class="p-2 font-semibold">
                                        <?= e($row['nama_pegawai']) ?><?= !empty($row['gelar']) ? ', ' . e($row['gelar']) : '' ?>
                                    </td>
                                    <td class="p-2 text-center text-[10.5pt]">
                                        <?= e($row['niy'] ?: ($row['nik'] ?: '-')) ?>
                                    </td>
                                    <td class="p-2 font-bold">
                                        <?= e($row['nama_jabatan'] ?? '-') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between text-[11pt] text-slate-700 mb-5">
                <p>Total Pegawai Di-SK-kan: <strong class="text-black font-bold"><?= count($penugasan) ?> Orang</strong></p>
                <p class="italic text-[10pt]">Dokumen ini merupakan bagian tidak terpisahkan dari SK Nomor: <?= e($noSk) ?></p>
            </div>

            <!-- TANDA TANGAN PENGESAHAN LAMPIRAN -->
            <div class="flex justify-end text-[12pt]">
                <div class="w-80 text-left">
                    <p>Ditetapkan di : <?= e($kotaSk) ?></p>
                    <p>Pada tanggal : <?= $tanggalSk ?></p>
                    
                    <div class="mt-2 font-bold text-black">
                        <p><?= e($penandatanganJabatan) ?>,</p>
                    </div>

                    <!-- Tanda Tangan Space -->
                    <div class="h-16 flex items-center">
                        <div class="w-14 h-14 border border-slate-300 p-0.5 rounded bg-slate-50 opacity-80" title="Validasi Lampiran">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=<?= urlencode(url('kelola-pegawai/penugasan/grup/' . $grup['id'])) ?>" alt="QR" class="w-full h-full object-contain">
                        </div>
                    </div>

                    <div class="mt-1">
                        <p class="font-bold text-black text-[12pt] underline underline-offset-2"><?= e($penandatanganNama) ?></p>
                        <p class="text-slate-800 text-[11pt] mt-0.5"><?= e($penandatanganNip) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER SURAT RESMI (BANNER GAMBAR DI BAWAH LAMPIRAN) -->
        <?php if (!empty($grup['file_footer'])): ?>
            <div class="sk-footer-container">
                <img src="<?= url(ltrim($grup['file_footer'], '/')) ?>" alt="Footer Surat Resmi" class="sk-footer-img">
            </div>
        <?php endif; ?>

    </div>

    <!-- Script Interaktif untuk Live Edit & AJAX Update -->
    <script>
        function toggleDrawer() {
            const drawer = document.getElementById('settingsDrawer');
            if (drawer) {
                drawer.classList.toggle('hidden');
            }
        }

        async function submitSkMeta(e, form) {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Menyimpan...';
            btn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const res = await response.json();
                if (res.success) {
                    window.location.reload();
                } else {
                    alert(res.message || 'Gagal menyimpan pengaturan SK.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                // Fallback to normal form submit if fetch fails
                form.submit();
            }
        }
    </script>
</body>
</html>
