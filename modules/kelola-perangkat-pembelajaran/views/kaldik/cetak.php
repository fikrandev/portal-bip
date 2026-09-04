<?php
/**
 * Kaldik - Cetak / Print View Layout
 */
$agendas = $konten['agendas'] ?? [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kalender Pendidikan - <?= e($item['judul']) ?></title>
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

    <!-- Print Action Bar (Hidden on print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-slate-800">Pratinjau Cetak Kalender Pendidikan</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.close()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                Tutup
            </button>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 flex items-center gap-1.5">
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
            <h1 class="text-xl font-bold uppercase tracking-wider mt-1">KALENDER PENDIDIKAN & AGENDA AKADEMIK</h1>
            <p class="text-xs text-slate-600 mt-0.5">UNIT <?= strtoupper(e($item['unit'] ?? 'SD')) ?> • TAHUN AJARAN <?= strtoupper(e($item['nama_tahun'])) ?> • SEMESTER <?= strtoupper(e($item['semester'])) ?></p>
        </div>

        <!-- Metadata Table -->
        <div class="mb-6 grid grid-cols-2 gap-4 text-xs">
            <table class="w-full">
                <tr>
                    <td class="py-1 w-32 font-semibold">Judul Dokumen</td>
                    <td class="py-1">: <?= e($item['judul']) ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Unit Sekolah</td>
                    <td class="py-1">: <strong>Unit <?= e($item['unit'] ?? 'SD') ?></strong></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Tahun Ajaran</td>
                    <td class="py-1">: <?= e($item['nama_tahun']) ?></td>
                </tr>
            </table>
            <table class="w-full">
                <tr>
                    <td class="py-1 w-32 font-semibold">Semester</td>
                    <td class="py-1">: <?= e($item['semester']) ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Penyusun</td>
                    <td class="py-1">: <?= e($item['guru_nama']) ?></td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold">Status Pengesahan</td>
                    <td class="py-1">: <strong><?= strtoupper(e($item['status'])) ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- Matriks Kalender Bulanan -->
        <div class="mb-8 break-inside-avoid">
            <h3 class="text-xs font-bold uppercase tracking-wider text-black border-b border-black pb-1 mb-4">I. MATRIKS KALENDER PENDIDIKAN</h3>
            <?php
            $nama_tahun = $item['nama_tahun'] ?? '';
            $semester = $item['semester'] ?? 'all';
            $is_print = true;
            include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/matrix_kaldik.php';
            ?>
        </div>

        <!-- Tabel Agenda -->
        <div class="mb-8 break-inside-avoid">
            <h3 class="text-xs font-bold uppercase tracking-wider text-black border-b border-black pb-1 mb-4">II. DAFTAR AGENDA & KEGIATAN AKADEMIK</h3>
            <table class="w-full text-left text-xs border border-black border-collapse">
                <thead>
                    <tr class="bg-slate-100 border-b border-black text-center font-bold">
                        <th class="py-2.5 px-3 border-r border-black w-12">NO</th>
                        <th class="py-2.5 px-4 border-r border-black w-44">TANGGAL</th>
                        <th class="py-2.5 px-4 border-r border-black">KEGIATAN / AGENDA AKADEMIK</th>
                        <th class="py-2.5 px-4 border-r border-black w-32">KATEGORI</th>
                        <th class="py-2.5 px-4 w-40">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/40">
                    <?php if (empty($agendas)): ?>
                        <tr><td colspan="5" class="py-4 text-center">Tidak ada agenda.</td></tr>
                    <?php else: ?>
                        <?php foreach ($agendas as $i => $ag): ?>
                            <tr class="border-b border-black/40">
                                <td class="py-2 px-3 text-center border-r border-black font-semibold"><?= $i + 1 ?></td>
                                <td class="py-2 px-4 border-r border-black whitespace-nowrap font-mono text-[11px]">
                                    <?= !empty($ag['tanggal_mulai']) ? date('d/m/Y', strtotime($ag['tanggal_mulai'])) : '-' ?>
                                    <?= (!empty($ag['tanggal_selesai']) && $ag['tanggal_selesai'] !== $ag['tanggal_mulai']) ? ' s.d. ' . date('d/m/Y', strtotime($ag['tanggal_selesai'])) : '' ?>
                                </td>
                                <td class="py-2 px-4 border-r border-black font-semibold"><?= e($ag['kegiatan']) ?></td>
                                <td class="py-2 px-4 border-r border-black uppercase text-[10px] font-bold"><?= e(str_replace('_', ' ', $ag['kategori'] ?? 'kegiatan')) ?></td>
                                <td class="py-2 px-4 text-slate-700"><?= e($ag['keterangan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan Pengesahan -->
        <div class="mt-12 grid grid-cols-2 text-center text-xs break-inside-avoid">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold">Kepala Sekolah</p>
                <div class="h-20 flex items-center justify-center">
                    <?php if ($item['status'] === 'disetujui'): ?>
                        <span class="text-[10px] text-emerald-700 font-bold border border-emerald-500/50 bg-emerald-50 px-2 py-1 rounded">TERVERIFIKASI SISTEM</span>
                    <?php endif; ?>
                </div>
                <p class="font-bold underline tracking-wide"><?= e($item['approver_name'] ?? '................................................') ?></p>
                <p class="text-[10px] text-slate-500">NIP. ........................................</p>
            </div>

            <div>
                <p>Palu, <?= date('d F Y', strtotime($item['created_at'])) ?></p>
                <p class="font-bold">Guru / Penyusun</p>
                <div class="h-20"></div>
                <p class="font-bold underline tracking-wide"><?= e($item['guru_nama']) ?></p>
                <p class="text-[10px] text-slate-500"><?= !empty($item['guru_nip']) ? 'NIP. ' . e($item['guru_nip']) : 'NIP. ........................................' ?></p>
            </div>
        </div>

    </div>

</body>
</html>
