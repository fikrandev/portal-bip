<?php
/**
 * Cetak Rekap Mutaba'ah Al-Qur'an Siswa (A4 Printable)
 */
$jenjangLabel = match ($jenjang) {
    'PAUD' => 'PAUD / TK IT BINA INSAN',
    'SMP_SMA' => 'SMP & SMA IT BINA INSAN',
    default => 'SD IT BINA INSAN',
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Mutaba'ah Qur'an - <?= htmlspecialchars($jenjangLabel) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt; }
            @page { size: A4 portrait; margin: 15mm 10mm 15mm 10mm; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen font-sans p-4 sm:p-8">

    <!-- Action Bar (No Print) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-2">
            <span class="text-sm font-bold text-slate-800">Pratinjau Cetak Mutaba'ah</span>
            <span class="text-xs text-slate-400">Siap cetak format A4</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.history.back()" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">
                Kembali
            </button>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.327 2.45-6.079 5.28-6.079 2.83 0 5.52 2.752 5.28 6.079m-10.56 0A5.998 5.998 0 0 0 12 19.5c2.83 0 5.28-2.343 5.28-5.671m-10.56 0C6.72 10.5 9.17 8.157 12 8.157m0 0a5.998 5.998 0 0 1 5.28 5.672M6 20.25h12M9 3.75h6" />
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- Paper Container -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-md border border-slate-200/80">
        
        <!-- Kop Surat -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 text-center relative">
            <h2 class="text-xl font-black text-slate-900 tracking-wide uppercase">YAYASAN BINA INSAN PALU</h2>
            <h1 class="text-lg font-extrabold text-emerald-800 tracking-tight mt-0.5"><?= htmlspecialchars($jenjangLabel) ?></h1>
            <p class="text-xs text-slate-600 mt-1">Jl. Bina Insan No. 1, Kota Palu, Sulawesi Tengah • Telp: (0451) 123456</p>
            <p class="text-[11px] text-slate-500 italic">Portal Akademik & Terintegrasi Qur'an Siswa</p>
        </div>

        <!-- Judul Dokumen -->
        <div class="text-center mb-6">
            <h3 class="text-base font-black text-slate-900 uppercase tracking-wider underline">
                REKAPITULASI MUTABA'AH & CAPAIAN AL-QUR'AN SISWA
            </h3>
            <div class="flex justify-center gap-6 text-xs text-slate-600 mt-2">
                <span>Jenjang: <strong><?= htmlspecialchars($jenjang) ?></strong></span>
                <?php if (!empty($filterKelas)): ?>
                    <span>Kelas: <strong><?= htmlspecialchars($filterKelas) ?></strong></span>
                <?php endif; ?>
                <span>Tanggal Cetak: <strong><?= date('d F Y') ?></strong></span>
            </div>
        </div>

        <!-- Tabel Rekap -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full border-collapse border border-slate-300 text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 font-bold">
                        <th class="border border-slate-300 py-2 px-3 text-center w-10">No</th>
                        <th class="border border-slate-300 py-2 px-3 text-left w-24">Tanggal</th>
                        <th class="border border-slate-300 py-2 px-3 text-left">Nama Siswa</th>
                        <th class="border border-slate-300 py-2 px-3 text-left">Kelas</th>
                        <th class="border border-slate-300 py-2 px-3 text-left">Kegiatan</th>
                        <th class="border border-slate-300 py-2 px-3 text-left">Materi / Surah</th>
                        <th class="border border-slate-300 py-2 px-2 text-center w-16">Tajwid</th>
                        <th class="border border-slate-300 py-2 px-2 text-center w-16">Lancar</th>
                        <th class="border border-slate-300 py-2 px-3 text-center w-24">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($setoranList)): ?>
                        <tr>
                            <td colspan="9" class="border border-slate-300 py-6 text-center text-slate-400 italic">
                                Belum ada data setoran yang tercatat pada filter ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($setoranList as $row): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="border border-slate-300 py-2 px-3 text-center"><?= $no++ ?></td>
                                <td class="border border-slate-300 py-2 px-3"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td class="border border-slate-300 py-2 px-3 font-semibold text-slate-900"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                                <td class="border border-slate-300 py-2 px-3"><?= htmlspecialchars($row['kelas'] ?? '-') ?></td>
                                <td class="border border-slate-300 py-2 px-3"><?= QuranHelper::getJenisSetoranLabel($row['jenis_setoran']) ?></td>
                                <td class="border border-slate-300 py-2 px-3 font-medium">
                                    <?php if ($row['jenis_setoran'] === 'iqro'): ?>
                                        Iqro' Jilid <?= $row['iqro_jilid'] ?> (Hal. <?= $row['iqro_halaman'] ?: '-' ?>)
                                    <?php else: ?>
                                        <?= htmlspecialchars($row['surah_nama'] ?? 'Surah ' . $row['surah_nomor']) ?>
                                        <?php if ($row['ayat_awal']): ?>: <?= $row['ayat_awal'] ?>–<?= $row['ayat_akhir'] ?><?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="border border-slate-300 py-2 px-2 text-center font-bold"><?= $row['nilai_tajwid'] ?></td>
                                <td class="border border-slate-300 py-2 px-2 text-center font-bold"><?= $row['nilai_kelancaran'] ?></td>
                                <td class="border border-slate-300 py-2 px-3 text-center capitalize font-semibold">
                                    <?= htmlspecialchars($row['status_lulus']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="grid grid-cols-2 gap-8 text-xs text-center mt-12 pt-6">
            <div>
                <p class="text-slate-500">Mengetahui,</p>
                <p class="font-bold text-slate-900 mt-1">Kepala Unit Sekolah</p>
                <div class="h-20"></div>
                <p class="font-extrabold text-slate-900 underline">( ___________________________ )</p>
                <p class="text-[10px] text-slate-400">NIP/NIY: .......................................</p>
            </div>
            <div>
                <p class="text-slate-500">Palu, <?= date('d F Y') ?></p>
                <p class="font-bold text-slate-900 mt-1">Koordinator / Pembimbing Qur'an</p>
                <div class="h-20"></div>
                <p class="font-extrabold text-slate-900 underline">( ___________________________ )</p>
                <p class="text-[10px] text-slate-400">Ustadz / Ustadzah</p>
            </div>
        </div>

    </div>

</body>
</html>
