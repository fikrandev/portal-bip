<?php
/**
 * Qur'an PAUD - Fitur 4: Rekap Nilai
 * Halaman Rekapitulasi Nilai & Capaian Santri PAUD
 * Referensi UI/UX: Modul Kelola Nilai (rekap.php)
 */
$filterKategori = $_GET['kategori'] ?? '';
$filterKelas = $_GET['kelas'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$printUrl = url('kelola-quran-siswa-paud/rekap/cetak' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
$exportUrl = url('kelola-quran-siswa-paud/rekap/export' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Laporan Capaian Santri
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Jenjang PAUD / TK
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2">
                <span>Rekapitulasi Nilai Qur'an PAUD</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Rekapitulasi hasil penilaian capaian tahsin (Iqro'/Tilawati), tahfidz surah pendek, rating, dan status kelulusan
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= url('kelola-quran-siswa-paud') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="<?= $exportUrl ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Ekspor Excel</span>
            </a>
            <a href="<?= $printUrl ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md shadow-slate-800/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Rekap A4</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Mini Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Total Santri PAUD</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-black text-slate-800"><?= (int)($rekapStats['total_siswa'] ?? 0) ?></span>
                <span class="text-xs font-bold text-slate-500">Santri</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Penilaian Terinput</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-black text-indigo-600"><?= (int)($rekapStats['total_penilaian'] ?? 0) ?></span>
                <span class="text-xs font-bold text-indigo-600">Nilai</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Rata-Rata Rating</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-black text-amber-600"><?= number_format((float)($rekapStats['avg_bintang'] ?? 4.8), 1) ?></span>
                <span class="text-xs font-bold text-amber-600">Skala 5.0</span>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Capaian Mutqin</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-black text-emerald-600"><?= (int)($rekapStats['total_mutqin'] ?? 0) ?></span>
                <span class="text-xs font-bold text-emerald-600">Santri</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-sm space-y-4">
        <form action="<?= url('kelola-quran-siswa-paud/rekap') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            
            <!-- Kategori -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Kategori Pembelajaran</label>
                <select name="kategori" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">Semua Kategori</option>
                    <option value="tahsin" <?= $filterKategori === 'tahsin' ? 'selected' : '' ?>>a. Tahsin (Iqro')</option>
                    <option value="tahfidz" <?= $filterKategori === 'tahfidz' ? 'selected' : '' ?>>b. Tahfidz (Surah)</option>
                </select>
            </div>

            <!-- Kelas -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Pilih Kelas</label>
                <select name="kelas" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">Semua Kelas PAUD</option>
                    <?php if (!empty($kelasList)): ?>
                        <?php foreach ($kelasList as $kls): ?>
                            <option value="<?= htmlspecialchars($kls) ?>" <?= $filterKelas === $kls ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kls) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Status Kelulusan -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Status Kelulusan</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="Mutqin" <?= $filterStatus === 'Mutqin' ? 'selected' : '' ?>>Mutqin (Mumtaz)</option>
                    <option value="Lulus" <?= $filterStatus === 'Lulus' ? 'selected' : '' ?>>Lulus (Jayyid)</option>
                    <option value="Mengulang" <?= $filterStatus === 'Mengulang' ? 'selected' : '' ?>>Mengulang</option>
                </select>
            </div>

            <!-- Pencarian Santri -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Cari Santri / NIS</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Ketik nama santri..."
                           class="w-full px-3.5 py-2.5 pl-9 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50/50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </div>
            </div>

        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">
                    Data Rekapitulasi Penilaian
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Ditemukan <?= count($rekapNilai) ?> data penilaian santri
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Santri</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Wadah Grup Target</th>
                        <th class="py-3.5 px-4">Capaian Terakhir</th>
                        <th class="py-3.5 px-4 text-center">Kelancaran</th>
                        <th class="py-3.5 px-4 text-center">Makhraj</th>
                        <th class="py-3.5 px-4 text-center">Rating</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($rekapNilai)): ?>
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <p class="font-bold text-slate-700 text-sm">Tidak Ada Data Penilaian</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan filter di atas atau input nilai baru pada menu Input Penilaian.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rekapNilai as $idx => $r): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400"><?= $idx + 1 ?></td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-800 block text-sm"><?= htmlspecialchars($r['nama_lengkap']) ?></span>
                                    <span class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($r['nis'] ?: '-') ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        <?= htmlspecialchars($r['kelas']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-800 block"><?= htmlspecialchars($r['nama_grup']) ?></span>
                                    <span class="text-[10px] text-slate-400 uppercase font-semibold">
                                        <?= $r['kategori'] === 'tahsin' ? 'a. Tahsin' : 'b. Tahfidz' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-800 font-semibold">
                                    <?= htmlspecialchars($r['materi_capaian'] ?: '-') ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="font-bold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700">
                                        <?= htmlspecialchars($r['nilai_kelancaran'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="font-bold px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700">
                                        <?= htmlspecialchars($r['nilai_makhraj'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-0.5 text-amber-500">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <span class="<?= $s <= ($r['nilai_bintang'] ?? 0) ? 'text-amber-500' : 'text-slate-200' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="block text-[10px] text-slate-400 font-bold"><?= (int)($r['nilai_bintang'] ?? 0) ?>/5</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($r['status_lulus'] === 'Mutqin'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Mutqin
                                        </span>
                                    <?php elseif ($r['status_lulus'] === 'Lulus'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            Lulus
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Mengulang
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-xs italic">
                                    <?= htmlspecialchars($r['catatan_guru'] ?: '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
