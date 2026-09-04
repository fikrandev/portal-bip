<?php
/**
 * Rincian Hari Efektif (HEB & HES Auto-Generated) - View
 * Displays all classes & subjects assigned to the selected teacher vertically stacked.
 */
?>
<div class="space-y-6">

    <!-- Header & Tab Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="<?= url('kelola-perangkat-pembelajaran/rincian-hari-efektif') ?>" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white font-black text-xs shadow-sm flex items-center gap-1.5">
                    <span>⚡ Rincian Hari Efektif (Auto-Generated)</span>
                </a>
                <a href="<?= url('kelola-perangkat-pembelajaran/heb') ?>" class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold text-xs transition-colors">
                    📘 Arsip HEB
                </a>
                <a href="<?= url('kelola-perangkat-pembelajaran/hes') ?>" class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold text-xs transition-colors">
                    📗 Arsip HES
                </a>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Rincian Hari Efektif (HEB & HES)</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Auto-Calculated
                </span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Kalkulasi otomatis hari efektif sekolah dan pekan belajar efektif guru untuk <strong>seluruh kelas yang diampu</strong> berdasarkan Kaldik dan Jadwal.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <?php if (!empty($resultsList)): ?>
                <a href="<?= url('kelola-perangkat-pembelajaran/rincian-hari-efektif/cetak?' . http_build_query([
                    'unit' => $selectedUnit,
                    'tahun_ajaran' => $selectedTaNama,
                    'semester' => $selectedSemester,
                    'guru_id' => $selectedGuruId,
                    'kelas' => $selectedKelas,
                    'mapel' => $selectedMapel
                ])) ?>" target="_blank" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-gradient-to-r from-sky-600 to-indigo-600 hover:from-sky-700 hover:to-indigo-700 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-sky-500/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" />
                    </svg>
                    <span>Cetak <?= count($resultsList) > 1 ? 'Semua Dokumen (' . count($resultsList) . ' Lembar)' : 'Dokumen Resmi' ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter & Selection Controls -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <!-- Quick Unit Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
            <span class="font-bold text-slate-400 uppercase text-[10px] whitespace-nowrap mr-1">Pilih Unit:</span>
            <?php foreach ($unitList as $uKey => $uInfo): ?>
                <?php $isActiveUnit = ($selectedUnit === $uKey); ?>
                <a href="<?= url('kelola-perangkat-pembelajaran/rincian-hari-efektif?' . http_build_query(array_merge($_GET, ['unit' => $uKey, 'guru_id' => '', 'kelas' => '', 'mapel' => '']))) ?>" class="px-3.5 py-1.5 rounded-xl font-bold transition-all whitespace-nowrap inline-flex items-center gap-1.5 <?= $isActiveUnit ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <span><?= $uInfo['icon'] ?></span>
                    <span>Unit <?= $uKey ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="GET" action="<?= url('kelola-perangkat-pembelajaran/rincian-hari-efektif') ?>" id="formFilter" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 pt-3 border-t border-slate-100 text-xs">
            <input type="hidden" name="unit" value="<?= e($selectedUnit) ?>">

            <!-- Tahun Ajaran -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Tahun Ajaran</label>
                <select name="tahun_ajaran" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <?php foreach ($taList as $ta): ?>
                        <option value="<?= e($ta['nama_tahun']) ?>" <?= ($selectedTaNama === $ta['nama_tahun']) ? 'selected' : '' ?>>
                            <?= e($ta['nama_tahun']) ?> <?= !empty($ta['is_active']) ? '(Aktif)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Semester -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Semester</label>
                <select name="semester" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="Ganjil" <?= ($selectedSemester === 'Ganjil') ? 'selected' : '' ?>>Semester Ganjil (Jul - Des)</option>
                    <option value="Genap" <?= ($selectedSemester === 'Genap') ? 'selected' : '' ?>>Semester Genap (Jan - Jun)</option>
                </select>
            </div>

            <!-- Guru Pengampu -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Guru Pengampu</label>
                <select name="guru_id" onchange="onGuruChanged(this.value)" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Guru Pengampu --</option>
                    <?php foreach ($guruList as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= ($selectedGuruId == $g['id']) ? 'selected' : '' ?>>
                            <?= e($g['nama']) ?><?= !empty($g['gelar']) ? ', ' . e($g['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Rombel / Kelas (Opsional Filter) -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Filter Rombel / Kelas</label>
                <select name="kelas" id="selectKelas" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Tampilkan Semua Kelas (<?= count($kelasList) ?>) --</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= e($k) ?>" <?= ($selectedKelas === $k) ? 'selected' : '' ?>>
                            <?= e($k) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Mata Pelajaran (Opsional Filter) -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Filter Mata Pelajaran</label>
                <select name="mapel" id="selectMapel" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Tampilkan Semua Mapel (<?= count($mapelList) ?>) --</option>
                    <?php foreach ($mapelList as $m): ?>
                        <option value="<?= e($m) ?>" <?= ($selectedMapel === $m) ? 'selected' : '' ?>>
                            <?= e($m) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <!-- Live Calculated Document Outputs (Vertical List of All Classes) -->
    <?php if (empty($resultsList)): ?>
        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm">
            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">Silakan Pilih Guru Pengampu</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                Pilih guru pengampu di atas untuk langsung menampilkan seluruh rincian hari efektif sekolah (HES) dan hari efektif KBM (HEB) pada seluruh kelas yang diampu secara lengkap.
            </p>
        </div>
    <?php else: ?>

        <!-- Summary Bar of Loaded Sheets -->
        <div class="flex items-center justify-between px-2 text-xs font-bold text-slate-600">
            <span>Menampilkan <strong><?= count($resultsList) ?> Dokumen Rincian Hari Efektif</strong> untuk <strong><?= e($resultsList[0]['guru']['nama']) ?></strong></span>
            <span class="text-slate-400 font-medium">Scroll ke bawah untuk melihat seluruh kelas</span>
        </div>

        <!-- Stack of Printable Cards -->
        <div class="space-y-8">
            <?php foreach ($resultsList as $idx => $result): ?>
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden p-6 sm:p-10 max-w-4xl mx-auto space-y-6 relative group">
                    
                    <!-- Card Top Tool Bar -->
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-black flex items-center justify-center text-[11px]">
                                <?= $idx + 1 ?>
                            </span>
                            <span class="font-extrabold text-slate-800">
                                <?= e($result['nama_kelas']) ?> • <span class="text-indigo-600"><?= e($result['mata_pelajaran']) ?></span>
                            </span>
                        </div>
                        <a href="<?= url('kelola-perangkat-pembelajaran/rincian-hari-efektif/cetak?' . http_build_query([
                            'unit' => $selectedUnit,
                            'tahun_ajaran' => $selectedTaNama,
                            'semester' => $selectedSemester,
                            'guru_id' => $selectedGuruId,
                            'kelas' => $result['nama_kelas'],
                            'mapel' => $result['mata_pelajaran']
                        ])) ?>" target="_blank" class="px-3 py-1 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-700 hover:text-sky-700 font-bold transition-all flex items-center gap-1.5" title="Cetak lembar ini">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
                            <span>Cetak Lembar Ini</span>
                        </a>
                    </div>

                    <!-- Dokumen Header Box (Sesuai Format Resmi Sekolah) -->
                    <div class="border-2 border-slate-900 rounded-xl overflow-hidden grid grid-cols-1 md:grid-cols-12 bg-white text-xs">
                        
                        <!-- Logo Kolom (Lebih Ramping / Pendek) -->
                        <div class="md:col-span-2 p-3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r-2 border-slate-900 bg-sky-50/30">
                            <?php if (!empty($result['logo_url'])): ?>
                                <img src="<?= url(ltrim($result['logo_url'], '/')) ?>" class="max-h-16 max-w-[90%] object-contain" alt="Logo">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-400 to-cyan-500 flex items-center justify-center text-white font-black text-lg shadow-sm">
                                    BIP
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Box Tengah: Mata Pelajaran (Lebih Luas) -->
                        <div class="md:col-span-5 p-4 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r-2 border-slate-900 bg-sky-100/50 text-center">
                            <span class="text-xs font-bold text-slate-700 uppercase">Mata Pelajaran</span>
                            <span class="text-base font-black text-slate-950 uppercase mt-0.5 tracking-wide">
                                <?= e($result['mata_pelajaran']) ?>
                            </span>
                        </div>

                        <!-- Box Kanan: Data Pengajar, Kelas, Semester, TA (Lebih Luas) -->
                        <div class="md:col-span-5 p-3 bg-sky-200/40 divide-y divide-slate-800/20 text-[11px]">
                            <div class="grid grid-cols-12 py-1">
                                <span class="col-span-4 font-bold text-slate-800">Pengajar</span>
                                <span class="col-span-8 font-extrabold text-slate-950">: <?= e($result['guru']['nama']) ?></span>
                            </div>
                            <div class="grid grid-cols-12 py-1">
                                <span class="col-span-4 font-bold text-slate-800">Kelas</span>
                                <span class="col-span-8 font-extrabold text-slate-950">: <?= e($result['nama_kelas']) ?></span>
                            </div>
                            <div class="grid grid-cols-12 py-1">
                                <span class="col-span-4 font-bold text-slate-800">Semester</span>
                                <span class="col-span-8 font-extrabold text-slate-950">: <?= e($result['semester']) ?></span>
                            </div>
                            <div class="grid grid-cols-12 py-1">
                                <span class="col-span-4 font-bold text-slate-800">Tahun Ajaran</span>
                                <span class="col-span-8 font-extrabold text-slate-950">: <?= e(trim(preg_replace('/\s*(Ganjil|Genap)/i', '', $result['tahun_ajaran']))) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Judul Dokumen -->
                    <div class="text-center pt-2">
                        <h2 class="text-lg sm:text-xl font-black text-slate-950 tracking-wider uppercase underline underline-offset-4 decoration-2">
                            RINCIAN HARI EFEKTIF
                        </h2>
                        <p class="text-xs font-bold text-slate-800 mt-2 text-left">
                            <?= e($result['durasi_label']) ?>
                        </p>
                    </div>

                    <!-- I. Rincian Hari Efektif Sekolah -->
                    <div class="space-y-2">
                        <h3 class="text-xs font-extrabold text-slate-900">
                            I. &nbsp; Rincian Hari Efektif Sekolah
                        </h3>
                        <div class="border border-slate-900 rounded-lg overflow-hidden">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-sky-100/80 text-slate-900 font-extrabold border-b border-slate-900 text-center">
                                    <tr>
                                        <th class="py-2 px-3 border-r border-slate-900 w-12">NO</th>
                                        <th class="py-2 px-4 border-r border-slate-900">BULAN</th>
                                        <th class="py-2 px-4 border-r border-slate-900 w-32">JUMLAH HARI</th>
                                        <th class="py-2 px-4">KETERANGAN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 text-[11px]">
                                    <?php foreach ($result['hes']['rows'] as $r): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-1.5 px-3 border-r border-slate-900 text-center font-bold"><?= $r['no'] ?>.</td>
                                            <td class="py-1.5 px-4 border-r border-slate-900 font-extrabold text-slate-900 uppercase"><?= e($r['bulan']) ?></td>
                                            <td class="py-1.5 px-4 border-r border-slate-900 text-center font-extrabold text-slate-900"><?= $r['jumlah_hari'] ?></td>
                                            <td class="py-1.5 px-4 text-slate-700"><?= e($r['keterangan']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="border-t-2 border-slate-900 font-black text-xs bg-slate-50">
                                    <tr>
                                        <td colspan="2" class="py-2 px-4 border-r border-slate-900 text-center uppercase tracking-wider">JUMLAH</td>
                                        <td class="py-2 px-4 border-r border-slate-900 text-center text-rose-600 font-black text-sm"><?= $result['hes']['total_hari'] ?></td>
                                        <td class="py-2 px-4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- II. RINCIAN HARI EFEKTIF KBM -->
                    <div class="space-y-2 pt-2">
                        <h3 class="text-xs font-extrabold text-slate-900">
                            II. RINCIAN HARI EFEKTIF KBM
                        </h3>
                        <div class="border border-slate-900 rounded-lg overflow-hidden">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-sky-100/80 text-slate-900 font-extrabold border-b border-slate-900 text-center">
                                    <tr>
                                        <th class="py-2 px-4 border-r border-slate-900">BULAN</th>
                                        <th class="py-2 px-3 border-r border-slate-900 w-24">HARI</th>
                                        <th class="py-2 px-3 border-r border-slate-900 w-24">PEKAN</th>
                                        <th class="py-2 px-3 border-r border-slate-900 w-24">JAM-PEL</th>
                                        <th class="py-2 px-4">KET</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800 text-[11px]">
                                    <?php foreach ($result['heb']['rows'] as $r): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-1.5 px-4 border-r border-slate-900 font-extrabold text-slate-900 uppercase"><?= e($r['bulan']) ?></td>
                                            <td class="py-1.5 px-3 border-r border-slate-900 text-center font-extrabold text-slate-900"><?= $r['hari'] ?></td>
                                            <td class="py-1.5 px-3 border-r border-slate-900 text-center font-extrabold text-slate-900"><?= $r['pekan'] ?></td>
                                            <td class="py-1.5 px-3 border-r border-slate-900 text-center font-extrabold text-slate-900"><?= $r['jam_pel'] ?></td>
                                            <td class="py-1.5 px-4 text-slate-800 font-bold text-[10px] uppercase"><?= e($r['keterangan']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="border-t-2 border-slate-900 font-black text-xs bg-slate-50">
                                    <tr>
                                        <td class="py-2 px-4 border-r border-slate-900 text-center uppercase tracking-wider">JUMLAH</td>
                                        <td class="py-2 px-3 border-r border-slate-900 text-center font-black text-slate-950"><?= $result['heb']['total_hari'] ?></td>
                                        <td class="py-2 px-3 border-r border-slate-900 text-center font-black text-slate-950"><?= $result['heb']['total_pekan'] ?></td>
                                        <td class="py-2 px-3 border-r border-slate-900 text-center font-black text-slate-950"><?= $result['heb']['total_jam_pel'] ?></td>
                                        <td class="py-2 px-4"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Ringkasan Bawah & Tanda Tangan -->
                    <div class="pt-4 grid grid-cols-1 sm:grid-cols-12 gap-6 text-xs font-bold text-slate-900">
                        <!-- Kolom Kiri: Ringkasan Jumlah -->
                        <div class="sm:col-span-7 space-y-1.5">
                            <div class="grid grid-cols-12">
                                <span class="col-span-7">Jumlah Hari Efektif Belajar</span>
                                <span class="col-span-5">: &nbsp;<?= $result['summary']['jumlah_hari_efektif'] ?></span>
                            </div>
                            <div class="grid grid-cols-12">
                                <span class="col-span-7">Banyaknya Jam Efektif</span>
                                <span class="col-span-5">: &nbsp;<?= $result['summary']['banyaknya_jam_efektif'] ?></span>
                            </div>
                            <div class="grid grid-cols-12">
                                <span class="col-span-7">Banyaknya Pekan Efektif</span>
                                <span class="col-span-5">: &nbsp;<?= $result['summary']['banyaknya_pekan_efektif'] ?></span>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Tanda Tangan Mengetahui Kepala Sekolah -->
                        <div class="sm:col-span-5 text-center sm:text-right pr-4">
                            <p class="font-extrabold">Mengetahui,</p>
                            <p class="font-extrabold">Kepala Sekolah</p>
                            <div class="h-16"></div>
                            <p class="font-black underline underline-offset-2 uppercase tracking-wide">
                                <?= e($result['kepala_sekolah']['nama']) ?>
                            </p>
                            <?php if (!empty($result['kepala_sekolah']['nip'])): ?>
                                <p class="text-[11px] font-semibold text-slate-600 mt-0.5">
                                    NIP. <?= e($result['kepala_sekolah']['nip']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<script>
function onGuruChanged(guruId) {
    // Reset filter kelas & mapel so it displays all classes of the selected teacher downwards
    document.getElementById('selectKelas').value = '';
    document.getElementById('selectMapel').value = '';
    document.getElementById('formFilter').submit();
}
</script>
