<?php
/**
 * Cetak Semua RPP dalam Grup (JSIT SDIT Bina Insan Palu)
 * Layout: Portrait A4 Sesuai 100% Format Standar Sekolah dengan Page Breaks
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kumpulan RPP - <?= e($group['judul'] ?? 'Grup RPP') ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 12mm 12mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .rpp-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            page-break-after: always;
        }
        .rpp-container:last-child {
            page-break-after: auto;
        }
        /* Kop Header */
        .kop-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            text-align: center;
            padding-bottom: 8px;
            margin-bottom: 12px;
            border-bottom: 2.5px solid #000;
        }
        .kop-logo {
            width: 65px;
            height: auto;
            object-fit: contain;
        }
        .kop-title {
            text-align: center;
        }
        .kop-title h1 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-title h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }
        .kop-title h3 {
            font-size: 11pt;
            font-weight: bold;
            margin: 2px 0 0 0;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10.5pt;
        }
        table.bordered, table.bordered th, table.bordered td {
            border: 1px solid #000;
        }
        table.identitas-table td {
            padding: 3px 6px;
            vertical-align: top;
        }
        .bg-yellow {
            background-color: #ffff00 !important;
            font-weight: bold;
            text-transform: uppercase;
        }
        .bg-yellow-header {
            background-color: #ffff00 !important;
            font-weight: bold;
            padding: 4px 8px;
            border: 1px solid #000;
            text-align: center;
            font-size: 10.5pt;
        }
        .table-cell-pad {
            padding: 5px 8px;
            vertical-align: top;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
        @media screen {
            body {
                background: #e2e8f0;
                padding: 20px;
            }
            .rpp-container {
                background: #fff;
                padding: 20mm 15mm;
                margin-bottom: 25px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                border-radius: 4px;
            }
            .print-btn-bar {
                position: fixed;
                top: 15px;
                right: 20px;
                z-index: 999;
                display: flex;
                gap: 10px;
            }
            .print-btn {
                background: #0d9488;
                color: #fff;
                padding: 10px 18px;
                border: none;
                border-radius: 8px;
                font-weight: bold;
                font-size: 13px;
                cursor: pointer;
                box-shadow: 0 4px 6px rgba(0,0,0,0.15);
            }
            .print-btn:hover { background: #0f766e; }
        }
    </style>
</head>
<body>

    <div class="no-print print-btn-bar">
        <button class="print-btn" onclick="window.print()">🖨️ Cetak Semua RPP (A4 Portrait)</button>
        <button class="print-btn" style="background:#475569;" onclick="window.close()">Tutup</button>
    </div>

    <?php if (empty($items)): ?>
        <div class="rpp-container" style="text-align: center; padding: 40px;">
            <p>Tidak ada dokumen RPP yang ditemukan dalam wadah grup ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($items as $item): 
            $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
            $kktpRows = $konten['kktp_rows'] ?? [];
            $tahunAjaran = $item['nama_tahun'] ?? '2025/2026';
            $semesterLabel = ($item['semester'] === 'Ganjil') ? 'I (Ganjil)' : 'II (Genap)';
        ?>
            <div class="rpp-container">
                
                <!-- KOP SEKOLAH (dari Pengaturan Sistem) -->
                <?php
                $namaSekolah = $unitProfile['nama_lembaga'] ?? 'SD ISLAM TERPADU BINA INSAN PALU';
                $logoSekolah = !empty($unitProfile['logo_url']) ? url($unitProfile['logo_url']) : url('public/assets/images/logo.png');
                ?>
                <div class="kop-header">
                    <img src="<?= $logoSekolah ?>" alt="Logo Sekolah" class="kop-logo" onerror="this.style.display='none'">
                    <div class="kop-title">
                        <h1><?= e($namaSekolah) ?></h1>
                        <h2>RENCANA PELAKSANAAN PEMBELAJARAN</h2>
                        <h3>Tahun Ajaran <?= e($tahunAjaran) ?> <?= e($item['semester'] ?? '') ?></h3>
                    </div>
                </div>

                <!-- IDENTITAS MODUL TABLE -->
                <table class="bordered identitas-table">
                    <tr>
                        <td style="width: 22%; font-weight: bold;">Nama Guru Mapel</td>
                        <td style="width: 28%;"><?= e($item['guru_nama'] ?: 'Widya Ningrum, S.Pd') ?></td>
                        <td style="width: 22%; font-weight: bold;">Model Pembelajaran</td>
                        <td style="width: 28%;"><?= e($konten['model_pembelajaran'] ?? 'Problem Based Learning') ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Mata Pelajaran</td>
                        <td><?= e($item['mata_pelajaran'] ?: 'IPAS') ?></td>
                        <td style="font-weight: bold;">Alokasi Waktu</td>
                        <td><?= e($item['alokasi_waktu'] ?: '2 x 35 Menit (Pertemuan 1)') ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Semester</td>
                        <td><?= e($semesterLabel) ?></td>
                        <td style="font-weight: bold;">Waktu Pelaksanaan</td>
                        <td><?= e($konten['waktu_pelaksanaan'] ?? '22 - 25 Juli 2025') ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold;">Fase</td>
                        <td><?= e($item['fase'] ?: 'B') ?></td>
                        <td style="font-weight: bold;">Kelas (Rombel)</td>
                        <td><?= e($item['tingkat_kelas'] ?: 'III (Tiga) Abdurrahman') ?></td>
                    </tr>
                </table>

                <!-- CAPAIAN PEMBELAJARAN (CP) -->
                <table class="bordered">
                    <tr>
                        <td class="bg-yellow-header">Capaian Pembelajaran (CP)</td>
                    </tr>
                    <tr>
                        <td class="table-cell-pad" style="text-align: justify;">
                            <?= nl2br(e($konten['cp_text'] ?? ($konten['cp'] ?? 'Peserta didik memahami konsep materi dan mengidentifikasi keterkaitannya dalam kehidupan sehari-hari.'))) ?>
                        </td>
                    </tr>
                </table>

                <!-- TUJUAN PEMBELAJARAN (3 RANAH WITH YELLOW HEADERS) -->
                <table class="bordered">
                    <tr>
                        <td colspan="2" class="bg-yellow-header">Tujuan Pembelajaran</td>
                    </tr>
                    <tr>
                        <td class="bg-yellow table-cell-pad text-center" style="width: 22%; vertical-align: middle;">Attitude/Sikap</td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['tp_attitude'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="bg-yellow table-cell-pad text-center" style="vertical-align: middle;">Skill/Keterampilan</td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['tp_skill'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="bg-yellow table-cell-pad text-center" style="vertical-align: middle;">Knowledge/Pengetahuan</td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['tp_knowledge'] ?? ($konten['tujuan_pembelajaran'] ?? '-'))) ?></td>
                    </tr>
                </table>

                <!-- KATA KUNCI & PERTANYAAN PEMANTIK -->
                <table class="bordered">
                    <tr>
                        <td class="bg-yellow-header" style="width: 50%;">Kata Kunci / Konten</td>
                        <td class="bg-yellow-header" style="width: 50%;">Pertanyaan Pemantik</td>
                    </tr>
                    <tr>
                        <td class="table-cell-pad"><?= e($konten['kata_kunci'] ?? '-') ?></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['pertanyaan_pemantik'] ?? '-')) ?></td>
                    </tr>
                </table>

                <!-- PENGETAHUAN PENDUKUNG & ASESMEN DIAGNOSIS KOGNITIF -->
                <table class="bordered">
                    <tr>
                        <td class="bg-yellow-header" style="width: 50%;">Pengetahuan Pendukung</td>
                        <td class="bg-yellow-header" style="width: 50%;">Asesmen Diagnosis Kognitif</td>
                    </tr>
                    <tr>
                        <td class="table-cell-pad"><?= nl2br(e($konten['pengetahuan_pendukung'] ?? '-')) ?></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['asesmen_diagnosis'] ?? ($konten['asesmen_diagnostik'] ?? '-'))) ?></td>
                    </tr>
                </table>

                <!-- KKTP -->
                <table class="bordered">
                    <tr>
                        <td colspan="3" class="bg-yellow-header">Kriteria Ketercapaian Tujuan Pembelajaran (KKTP)</td>
                    </tr>
                    <tr class="bg-yellow text-center" style="font-size: 10pt;">
                        <th style="width: 8%; padding: 4px;">No</th>
                        <th style="width: 62%; padding: 4px;">KKTP</th>
                        <th style="width: 30%; padding: 4px;">Waktu Pelaksanaan</th>
                    </tr>
                    <?php if (empty($kktpRows)): ?>
                        <tr>
                            <td class="text-center">1</td>
                            <td class="table-cell-pad">Peserta didik mampu menguasai indikator pembelajaran topik ini.</td>
                            <td class="table-cell-pad text-center">Pertemuan 1</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kktpRows as $rIdx => $kr): ?>
                            <tr>
                                <td class="text-center"><?= $rIdx + 1 ?></td>
                                <td class="table-cell-pad"><?= e($kr['indikator'] ?? ($kr['kktp'] ?? '-')) ?></td>
                                <td class="table-cell-pad text-center"><?= e($kr['waktu'] ?? ($kr['pekan'] ?? '-')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>

                <!-- MEDIA, SARANA, METODE & SUMBER BELAJAR -->
                <table class="bordered">
                    <tr>
                        <td class="bg-yellow-header" style="width: 25%;">Media</td>
                        <td class="bg-yellow-header" style="width: 25%;">Sarana</td>
                        <td class="bg-yellow-header" style="width: 25%;">Metode</td>
                        <td class="bg-yellow-header" style="width: 25%;">Sumber Belajar</td>
                    </tr>
                    <tr>
                        <td class="table-cell-pad"><?= nl2br(e($konten['media'] ?? 'PPT Interaktif, Video Pembelajaran, LKPD')) ?></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['sarana'] ?? ($konten['sarana_prasarana'] ?? 'LCD Proyektor, Laptop, Papan Tulis'))) ?></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['metode'] ?? 'Diskusi, Pengamatan, Tanya Jawab')) ?></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['sumber_belajar'] ?? 'Buku Siswa Kemendikbud, Modul JSIT')) ?></td>
                    </tr>
                </table>

                <!-- PELAKSANAAN PEMBELAJARAN PENDEKATAN TERPADU -->
                <table class="bordered">
                    <tr>
                        <td colspan="3" class="bg-yellow-header">Pelaksanaan Pembelajaran Pendekatan TERPADU</td>
                    </tr>
                    <tr class="bg-yellow text-center" style="font-size: 10pt;">
                        <th style="width: 8%; padding: 4px;">No</th>
                        <th style="width: 28%; padding: 4px;">Tahap / Kegiatan</th>
                        <th style="width: 64%; padding: 4px;">Deskripsi Kegiatan</th>
                    </tr>
                    <tr>
                        <td class="text-center">1</td>
                        <td class="table-cell-pad text-bold">Pembukaan (Opener)<br><small style="font-weight:normal;"><?= e($konten['opener_waktu'] ?? '10 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['opener_kegiatan'] ?? ($konten['kegiatan_pendahuluan'] ?? '-'))) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">2</td>
                        <td class="table-cell-pad text-bold">Telaah<br><small style="font-weight:normal;"><?= e($konten['telaah_waktu'] ?? '20 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['telaah_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">3</td>
                        <td class="table-cell-pad text-bold">Eksplorasi<br><small style="font-weight:normal;"><?= e($konten['eksplorasi_waktu'] ?? '20 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['eksplorasi_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">4</td>
                        <td class="table-cell-pad text-bold">Rumuskan<br><small style="font-weight:normal;"><?= e($konten['rumuskan_waktu'] ?? '20 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['rumuskan_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">5</td>
                        <td class="table-cell-pad text-bold">Presentasikan<br><small style="font-weight:normal;"><?= e($konten['presentasikan_waktu'] ?? '20 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['presentasikan_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">6</td>
                        <td class="table-cell-pad text-bold">Aplikasikan<br><small style="font-weight:normal;"><?= e($konten['aplikasikan_waktu'] ?? '10 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['aplikasikan_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">7</td>
                        <td class="table-cell-pad text-bold">Kaitkan dan Simpulkan<br><small style="font-weight:normal;"><?= e($konten['kaitkan_waktu'] ?? '2 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['kaitkan_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">8</td>
                        <td class="table-cell-pad text-bold">Duniawi<br><small style="font-weight:normal;"><?= e($konten['duniawi_waktu'] ?? '2 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['duniawi_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center">9</td>
                        <td class="table-cell-pad text-bold">Ukhrowi<br><small style="font-weight:normal;"><?= e($konten['ukhrowi_waktu'] ?? '3 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['ukhrowi_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-center" rowspan="2">10</td>
                        <td class="table-cell-pad text-bold">Closure: Refleksi<br><small style="font-weight:normal;"><?= e($konten['refleksi_waktu'] ?? '2 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['refleksi_kegiatan'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <td class="table-cell-pad text-bold">Closure: Kegiatan Penutup<br><small style="font-weight:normal;"><?= e($konten['penutup_waktu'] ?? '2 Menit') ?></small></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['penutup_kegiatan'] ?? ($konten['kegiatan_penutup'] ?? '-'))) ?></td>
                    </tr>
                </table>

                <!-- PENILAIAN TERPADU -->
                <table class="bordered">
                    <tr>
                        <td colspan="5" class="bg-yellow-header">Penilaian Terpadu</td>
                    </tr>
                    <tr class="bg-yellow text-center" style="font-size: 10pt;">
                        <th style="width: 18%; padding: 4px;">Ranah</th>
                        <th style="width: 26%; padding: 4px;">Tujuan Pembelajaran</th>
                        <th style="width: 18%; padding: 4px;">AfL</th>
                        <th style="width: 18%; padding: 4px;">AaL</th>
                        <th style="width: 20%; padding: 4px;">AoL</th>
                    </tr>
                    <tr>
                        <td class="table-cell-pad text-bold">Attitude / Sikap</td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_sikap_tp'] ?? 'Berakhlak mulia & mandiri') ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_sikap_afl'] ?? ($konten['asesmen_formatif'] ?? 'Observasi')) ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_sikap_aal'] ?? 'Penilaian Diri') ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_sikap_aol'] ?? 'Jurnal Catatan') ?></td>
                    </tr>
                    <tr>
                        <td class="table-cell-pad text-bold">Skill / Keterampilan</td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_skill_tp'] ?? 'Mengomunikasikan hasil kerja') ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_skill_afl'] ?? 'Unjuk Kerja') ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_skill_aal'] ?? 'Checklist Tugas') ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_skill_aol'] ?? 'Rubrik LKPD') ?></td>
                    </tr>
                    <tr>
                        <td class="table-cell-pad text-bold">Knowledge / Pengetahuan</td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_knowledge_tp'] ?? 'Memahami konsep materi') ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_knowledge_afl'] ?? 'Tanya Jawab') ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_knowledge_aal'] ?? 'Kuis Mandiri') ?></td>
                        <td class="table-cell-pad"><?= e($konten['penilaian_knowledge_aol'] ?? ($konten['asesmen_sumatif'] ?? 'Tes Tertulis')) ?></td>
                    </tr>
                </table>

                <!-- PENERAPAN INTROFLEX -->
                <table class="bordered">
                    <tr>
                        <td colspan="4" class="bg-yellow-header">Penerapan INTROFLEX</td>
                    </tr>
                    <tr class="bg-yellow text-center" style="font-size: 10pt;">
                        <th style="width: 25%; padding: 4px;">Individualisasi</th>
                        <th style="width: 25%; padding: 4px;">Interaksi</th>
                        <th style="width: 25%; padding: 4px;">Observasi</th>
                        <th style="width: 25%; padding: 4px;">Refleksi</th>
                    </tr>
                    <tr>
                        <td class="table-cell-pad"><?= nl2br(e($konten['introflex_individualisasi'] ?? 'Menyapa siswa menyebut nama dan bimbingan sesuai kebutuhan.')) ?></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['introflex_interaksi'] ?? 'Membangun diskusi aktif dalam kelompok dan kelas.')) ?></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['introflex_observasi'] ?? 'Mengamati keaktifan dan antusiasme siswa.')) ?></td>
                        <td class="table-cell-pad"><?= nl2br(e($konten['introflex_refleksi'] ?? ($konten['refleksi_guru_siswa'] ?? 'Memberikan umpan balik dan evaluasi pemahaman.'))) ?></td>
                    </tr>
                </table>

                <!-- TANDA TANGAN PENGESAHAN 3 KOLOM -->
                <?php
                // Kepala Sekolah dari Pengaturan Sistem
                $ksNama = $unitProfile['kepala_sekolah']['nama'] ?? $konten['kepala_sekolah_nama'] ?? 'Kepala Sekolah';
                $ksNip = $unitProfile['kepala_sekolah']['nip'] ?? $konten['kepala_sekolah_nip'] ?? '';
                
                // Pemeriksa = siapa yang approve RPP ini, fallback ke user login sekarang
                $loggedInUser = (class_exists('Auth') && Auth::name() && Auth::name() !== 'Guest') ? Auth::name() : '';
                $rawPemeriksa = $konten['pemeriksa_nama'] ?? '';
                if ($rawPemeriksa === 'Tim Kurikulum SDIT Bina Insan' || $rawPemeriksa === 'Tim Kurikulum') {
                    $rawPemeriksa = '';
                }
                $pemeriksaNama = !empty($item['approver_name']) ? $item['approver_name'] : (!empty($rawPemeriksa) ? $rawPemeriksa : (!empty($loggedInUser) ? $loggedInUser : 'Belum Diperiksa'));
                $pemeriksaNip = $item['approver_nip'] ?? $konten['pemeriksa_nip'] ?? '';
                ?>
                <div style="margin-top: 25px; page-break-inside: avoid;">
                    <table style="border: none; width: 100%; text-align: center; font-size: 10.5pt;">
                        <tr>
                            <td style="width: 33%; border: none; vertical-align: top;">
                                Mengetahui,<br>
                                <strong>Kepala Sekolah</strong>
                                <div style="height: 60px;"></div>
                                <strong><u><?= e($ksNama) ?></u></strong><br>
                                <?php if (!empty($ksNip)): ?>
                                    <span>NIP: <?= e($ksNip) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="width: 33%; border: none; vertical-align: top;">
                                Diperiksa oleh,<br>
                                <strong>Pemeriksa RPP</strong>
                                <div style="height: 60px;"></div>
                                <strong><u><?= e($pemeriksaNama) ?></u></strong><br>
                                <?php if (!empty($pemeriksaNip)): ?>
                                    <span>NIP: <?= e($pemeriksaNip) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="width: 33%; border: none; vertical-align: top;">
                                Palu, <?= date('d F Y', strtotime($item['created_at'])) ?><br>
                                <strong>Guru Mata Pelajaran</strong>
                                <div style="height: 60px;"></div>
                                <strong><u><?= e($item['guru_nama'] ?: 'Guru Mapel') ?></u></strong><br>
                                <?php if (!empty($item['guru_nip'])): ?>
                                    <span>NIP: <?= e($item['guru_nip']) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
