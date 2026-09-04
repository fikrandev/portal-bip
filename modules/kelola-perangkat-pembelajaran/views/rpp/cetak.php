<?php
/**
 * RPP / Modul Ajar - Cetak / Print View Layout (Standard Kemendikbud)
 */
$profilPancasila = $konten['profil_pancasila'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Modul Ajar / RPP - <?= e($item['judul']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt; color: #000; }
            .print-page { padding: 0 !important; margin: 0 !important; max-width: 100% !important; border: none !important; }
            @page { size: A4 portrait; margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8">

    <!-- Print Action Bar -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-slate-800">Pratinjau Cetak Modul Ajar / RPP</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.close()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                Tutup
            </button>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                Cetak Sekarang (Print)
            </button>
        </div>
    </div>

    <!-- Printable Paper Layout -->
    <div class="print-page max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-xl border border-slate-200/60 text-black">
        
        <!-- Header / Kop Dokumen -->
        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <h2 class="text-lg font-extrabold uppercase tracking-wide"><?= e(SYS_APP_NAME) ?></h2>
            <h1 class="text-xl font-bold uppercase tracking-wider mt-1">RENCANA PELAKSANAAN PEMBELAJARAN (MODUL AJAR)</h1>
            <p class="text-xs text-slate-600 mt-0.5">TAHUN AJARAN <?= strtoupper(e($item['nama_tahun'])) ?> • SEMESTER <?= strtoupper(e($item['semester'])) ?></p>
        </div>

        <!-- I. INFORMASI UMUM -->
        <div class="mb-6">
            <h2 class="font-bold uppercase text-xs border-b border-black pb-1 mb-3">I. INFORMASI UMUM</h2>
            <table class="w-full text-xs">
                <tr>
                    <td class="py-1 w-44 font-semibold">Nama Penyusun / Guru</td>
                    <td class="py-1">: <?= e($item['guru_nama']) ?> <?= !empty($item['guru_nip']) ? '(NIP. ' . e($item['guru_nip']) . ')' : '' ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Mata Pelajaran</td>
                    <td class="py-1">: <strong><?= e($item['mata_pelajaran']) ?></strong></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Fase / Kelas</td>
                    <td class="py-1">: <?= !empty($item['fase']) ? 'Fase ' . e($item['fase']) . ' / ' : '' ?><?= e($item['tingkat_kelas']) ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Alokasi Waktu / Pertemuan</td>
                    <td class="py-1">: <?= e($item['alokasi_waktu']) ?> (Pertemuan ke-<?= e($konten['pertemuan_ke'] ?? '1') ?>)</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Model Pembelajaran</td>
                    <td class="py-1">: <?= e($konten['model_pembelajaran'] ?? 'Problem Based Learning') ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Profil Pelajar Pancasila</td>
                    <td class="py-1">: <?= !empty($profilPancasila) ? implode(', ', $profilPancasila) : '-' ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Sarana & Prasarana</td>
                    <td class="py-1">: <?= e($konten['sarana_prasarana'] ?? '-') ?></td>
                </tr>
            </table>
        </div>

        <!-- II. KOMPONEN INTI -->
        <div class="mb-6 text-xs space-y-3">
            <h2 class="font-bold uppercase text-xs border-b border-black pb-1 mb-2">II. KOMPONEN INTI</h2>
            
            <div>
                <p class="font-bold">A. Tujuan Pembelajaran (TP):</p>
                <div class="pl-4 pt-1 whitespace-pre-line leading-relaxed"><?= e($konten['tujuan_pembelajaran'] ?? '-') ?></div>
            </div>

            <?php if (!empty($konten['pemahaman_bermakna'])): ?>
                <div>
                    <p class="font-bold">B. Pemahaman Bermakna:</p>
                    <div class="pl-4 pt-1 whitespace-pre-line leading-relaxed"><?= e($konten['pemahaman_bermakna']) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($konten['pertanyaan_pemantik'])): ?>
                <div>
                    <p class="font-bold">C. Pertanyaan Pemantik:</p>
                    <div class="pl-4 pt-1 whitespace-pre-line leading-relaxed"><?= e($konten['pertanyaan_pemantik']) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- III. KEGIATAN PEMBELAJARAN -->
        <div class="mb-6 text-xs space-y-3">
            <h2 class="font-bold uppercase text-xs border-b border-black pb-1 mb-2">III. KEGIATAN PEMBELAJARAN</h2>
            
            <div class="border border-black p-3 rounded">
                <p class="font-bold uppercase mb-1">1. Kegiatan Pendahuluan (<?= e($konten['waktu_pendahuluan'] ?? '15 Menit') ?>)</p>
                <div class="pl-2 whitespace-pre-line leading-relaxed text-slate-800"><?= e($konten['kegiatan_pendahuluan'] ?? '-') ?></div>
            </div>

            <div class="border border-black p-3 rounded">
                <p class="font-bold uppercase mb-1">2. Kegiatan Inti (<?= e($konten['waktu_inti'] ?? '60 Menit') ?>)</p>
                <div class="pl-2 whitespace-pre-line leading-relaxed text-slate-800"><?= e($konten['kegiatan_inti'] ?? '-') ?></div>
            </div>

            <div class="border border-black p-3 rounded">
                <p class="font-bold uppercase mb-1">3. Kegiatan Penutup (<?= e($konten['waktu_penutup'] ?? '15 Menit') ?>)</p>
                <div class="pl-2 whitespace-pre-line leading-relaxed text-slate-800"><?= e($konten['kegiatan_penutup'] ?? '-') ?></div>
            </div>
        </div>

        <!-- IV. ASESMEN & EVALUASI -->
        <div class="mb-8 text-xs space-y-2">
            <h2 class="font-bold uppercase text-xs border-b border-black pb-1 mb-2">IV. ASESMEN, REMEDIAL & PENGAYAAN</h2>
            
            <table class="w-full border border-black border-collapse">
                <tr>
                    <td class="p-2 border border-black font-semibold w-44">Asesmen Formatif</td>
                    <td class="p-2 border border-black"><?= e($konten['asesmen_formatif'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="p-2 border border-black font-semibold">Asesmen Sumatif</td>
                    <td class="p-2 border border-black"><?= e($konten['asesmen_sumatif'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="p-2 border border-black font-semibold">Pengayaan</td>
                    <td class="p-2 border border-black"><?= e($konten['pengayaan'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="p-2 border border-black font-semibold">Remedial</td>
                    <td class="p-2 border border-black"><?= e($konten['remedial'] ?? '-') ?></td>
                </tr>
            </table>
        </div>

        <!-- Tanda Tangan Pengesahan -->
        <div class="mt-12 grid grid-cols-2 text-center text-xs break-inside-avoid">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala Sekolah</p>
                <div class="h-20 flex items-center justify-center">
                    <?php if ($item['status'] === 'disetujui'): ?>
                        <span class="text-[10px] text-rose-800 font-bold border border-rose-600/50 bg-rose-50 px-2 py-1 rounded">TERVERIFIKASI SISTEM</span>
                    <?php endif; ?>
                </div>
                <p class="font-bold underline tracking-wide"><?= e($item['approver_name'] ?? '................................................') ?></p>
                <p class="text-[10px] text-slate-500">NIP. ........................................</p>
            </div>

            <div>
                <p>Palu, <?= date('d F Y', strtotime($item['created_at'])) ?></p>
                <p class="font-bold">Guru Pengampu Mata Pelajaran</p>
                <div class="h-20"></div>
                <p class="font-bold underline tracking-wide"><?= e($item['guru_nama']) ?></p>
                <p class="text-[10px] text-slate-500"><?= !empty($item['guru_nip']) ? 'NIP. ' . e($item['guru_nip']) : 'NIP. ........................................' ?></p>
            </div>
        </div>

    </div>

</body>
</html>
