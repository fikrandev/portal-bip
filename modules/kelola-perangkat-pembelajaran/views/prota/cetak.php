<?php
/**
 * Prota - Cetak / Print View Layout
 */
$materiRows = $konten['materi_rows'] ?? [];
$totalSmt1 = $konten['total_jp_smt1'] ?? 0;
$totalSmt2 = $konten['total_jp_smt2'] ?? 0;
$totalTahun = $konten['total_jp_tahun'] ?? ($totalSmt1 + $totalSmt2);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Program Tahunan - <?= e($item['judul']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt; color: #000; }
            .print-page { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            @page { size: A4 portrait; margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8">

    <!-- Print Action Bar -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-slate-800">Pratinjau Cetak Program Tahunan (Prota)</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.close()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                Tutup
            </button>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 flex items-center gap-1.5">
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
            <h1 class="text-xl font-bold uppercase tracking-wider mt-1">PROGRAM TAHUNAN (PROTA)</h1>
            <p class="text-xs text-slate-600 mt-0.5">TAHUN AJARAN <?= strtoupper(e($item['nama_tahun'])) ?></p>
        </div>

        <!-- Metadata Table -->
        <div class="mb-6 grid grid-cols-2 gap-4 text-xs">
            <table class="w-full">
                <tr>
                    <td class="py-1 w-32 font-semibold">Mata Pelajaran</td>
                    <td class="py-1">: <strong><?= e($item['mata_pelajaran']) ?></strong></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Tingkat / Kelas</td>
                    <td class="py-1">: <?= e($item['tingkat_kelas']) ?> <?= !empty($item['fase']) ? '(' . e($item['fase']) . ')' : '' ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Tahun Ajaran</td>
                    <td class="py-1">: <?= e($item['nama_tahun']) ?></td>
                </tr>
            </table>
            <table class="w-full">
                <tr>
                    <td class="py-1 w-32 font-semibold">Guru Pengampu</td>
                    <td class="py-1">: <?= e($item['guru_nama']) ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Total Alokasi JP</td>
                    <td class="py-1">: <strong><?= $totalTahun ?> Jam Pelajaran (JP)</strong></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Status Dokumen</td>
                    <td class="py-1">: <strong><?= strtoupper(e($item['status'])) ?></strong></td>
                </tr>
            </table>
        </div>

        <?php if (!empty($konten['capaian_umum'])): ?>
            <div class="mb-6 p-3 border border-black rounded text-xs bg-slate-50">
                <p class="font-bold mb-1">Capaian Pembelajaran (CP) Umum:</p>
                <p class="leading-relaxed"><?= nl2br(e($konten['capaian_umum'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Tabel Pemetaan Prota -->
        <div class="mb-8">
            <table class="w-full text-left text-xs border border-black border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-black text-center font-bold">
                        <th class="py-2.5 px-3 border-r border-black w-10">NO</th>
                        <th class="py-2.5 px-4 border-r border-black w-1/4 text-left">CAPAIAN / ELEMEN (CP/KD)</th>
                        <th class="py-2.5 px-4 border-r border-black w-1/3 text-left">MATERI POKOK / TOPIK</th>
                        <th class="py-2.5 px-3 border-r border-black w-20">SMT 1</th>
                        <th class="py-2.5 px-3 border-r border-black w-20">SMT 2</th>
                        <th class="py-2.5 px-4">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/40">
                    <?php if (empty($materiRows)): ?>
                        <tr><td colspan="6" class="py-4 text-center">Tidak ada rincian materi.</td></tr>
                    <?php else: ?>
                        <?php foreach ($materiRows as $i => $m): ?>
                            <tr class="border-b border-black/40">
                                <td class="py-2 px-3 text-center border-r border-black font-semibold"><?= $i + 1 ?></td>
                                <td class="py-2 px-4 border-r border-black font-medium text-slate-700"><?= e($m['cp_kd'] ?? '-') ?></td>
                                <td class="py-2 px-4 border-r border-black font-bold"><?= e($m['materi_pokok']) ?></td>
                                <td class="py-2 px-3 border-r border-black text-center font-mono font-bold"><?= (int)($m['jp_smt1'] ?? 0) ?> JP</td>
                                <td class="py-2 px-3 border-r border-black text-center font-mono font-bold"><?= (int)($m['jp_smt2'] ?? 0) ?> JP</td>
                                <td class="py-2 px-4 text-slate-700"><?= e($m['keterangan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-slate-100 font-extrabold border-t-2 border-black">
                        <td colspan="3" class="py-2.5 px-4 text-right border-r border-black uppercase">JUMLAH ALOKASI JP:</td>
                        <td class="py-2.5 px-3 text-center border-r border-black font-mono font-black"><?= $totalSmt1 ?> JP</td>
                        <td class="py-2.5 px-3 text-center border-r border-black font-mono font-black"><?= $totalSmt2 ?> JP</td>
                        <td class="py-2.5 px-4 text-slate-800 font-bold">Total: <?= $totalTahun ?> JP</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan Pengesahan -->
        <div class="mt-12 grid grid-cols-2 text-center text-xs break-inside-avoid">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala Sekolah</p>
                <div class="h-20 flex items-center justify-center">
                    <?php if ($item['status'] === 'disetujui'): ?>
                        <span class="text-[10px] text-indigo-800 font-bold border border-indigo-600/50 bg-indigo-50 px-2 py-1 rounded">TERVERIFIKASI SISTEM</span>
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
