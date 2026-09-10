<?php
/**
 * Qur'an Siswa - Central Hub Dashboard View
 * Displays 3 Education Level Portals (PAUD, SD, SMP & SMA)
 */
include MODULES_PATH . '/kelola-quran-siswa/views/partials/top_tabs.php';
?>

<div class="space-y-6">

    <!-- Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-primary-950 to-slate-900 text-white p-6 sm:p-8 shadow-xl border border-primary-800/40">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-emerald-300 text-xs font-bold mb-3 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Modul Qur'an Terintegrasi Bina Insan Palu</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight">
                    Pusat Manajemen Qur'an & Tahfidz Siswa
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                    Sistem pemantauan capaian bacaan Al-Qur'an, Iqro', ziyadah hafalan baru, serta muroja'ah yang terbagi ke dalam 3 kurikulum jenjang: <strong>PAUD / TK</strong>, <strong>Sekolah Dasar (SD)</strong>, dan <strong>SMP & SMA</strong>.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="openModalSetoran('SD')" 
                        class="px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-xs sm:text-sm font-bold shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>Input Setoran Cepat</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 3 Portals Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Portal 1: PAUD / TK -->
        <div class="group bg-white rounded-3xl border border-amber-200/80 p-6 shadow-sm hover:shadow-xl hover:border-amber-400 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-amber-500/5 rounded-full group-hover:scale-125 transition-transform"></div>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center text-2xl shadow-md shadow-amber-500/20">
                        🧸
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                        Usia Dini
                    </span>
                </div>
                
                <h2 class="text-xl font-extrabold text-slate-900 group-hover:text-amber-600 transition-colors">
                    Qur'an Siswa PAUD / TK
                </h2>
                <p class="text-xs text-slate-500 mt-1.5 line-clamp-2">
                    Fokus pengenalan huruf hijaiyah, metode Iqro' / Tilawati Jilid 1–6, hafalan surah-surah pendek Juz 30, dan doa harian.
                </p>

                <!-- Stats summary -->
                <div class="grid grid-cols-2 gap-2.5 mt-5 p-3.5 rounded-2xl bg-amber-50/50 border border-amber-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Total Santri</span>
                        <div class="text-xl font-black text-amber-950 mt-0.5"><?= number_format($counts['PAUD'] ?? 0) ?></div>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Setoran Iqro'</span>
                        <div class="text-xl font-black text-amber-950 mt-0.5"><?= number_format($stats['paud_setoran'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-amber-100/60 flex items-center justify-between">
                <a href="<?= url('kelola-quran-siswa-paud') ?>" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-md shadow-amber-500/20 text-center transition-all flex items-center justify-center gap-2 group-hover:gap-3">
                    <span>Masuk Portal PAUD</span>
                    <svg class="w-4 h-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Portal 2: SD IT -->
        <div class="group bg-white rounded-3xl border border-emerald-200/80 p-6 shadow-sm hover:shadow-xl hover:border-emerald-400 transition-all duration-300 flex flex-col justify-between relative overflow-hidden ring-2 ring-emerald-500/20">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-emerald-500/5 rounded-full group-hover:scale-125 transition-transform"></div>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center text-2xl shadow-md shadow-emerald-500/20">
                        🎒
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        Dasar IT (1–6)
                    </span>
                </div>
                
                <h2 class="text-xl font-extrabold text-slate-900 group-hover:text-emerald-600 transition-colors">
                    Qur'an Siswa SD
                </h2>
                <p class="text-xs text-slate-500 mt-1.5 line-clamp-2">
                    Fokus Ziyadah hafalan baru & Muroja'ah Juz 30 (Juz 'Amma), Juz 29, tilawah Al-Qur'an, dan penuntasan tajwid dasar.
                </p>

                <!-- Stats summary -->
                <div class="grid grid-cols-2 gap-2.5 mt-5 p-3.5 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Total Siswa SD</span>
                        <div class="text-xl font-black text-emerald-950 mt-0.5"><?= number_format($counts['SD'] ?? 0) ?></div>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Setoran Hafalan</span>
                        <div class="text-xl font-black text-emerald-950 mt-0.5"><?= number_format($stats['sd_setoran'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-emerald-100/60 flex items-center justify-between">
                <a href="<?= url('kelola-quran-siswa-sd') ?>" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-500/20 text-center transition-all flex items-center justify-center gap-2 group-hover:gap-3">
                    <span>Masuk Portal SD</span>
                    <svg class="w-4 h-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Portal 3: SMP & SMA IT -->
        <div class="group bg-white rounded-3xl border border-indigo-200/80 p-6 shadow-sm hover:shadow-xl hover:border-indigo-400 transition-all duration-300 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-indigo-500/5 rounded-full group-hover:scale-125 transition-transform"></div>
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-blue-600 text-white flex items-center justify-center text-2xl shadow-md shadow-indigo-500/20">
                        🎓
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-indigo-50 text-indigo-800 border border-indigo-200">
                        Menengah & Lanjutan
                    </span>
                </div>
                
                <h2 class="text-xl font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">
                    Qur'an Siswa SMP & SMA
                </h2>
                <p class="text-xs text-slate-500 mt-1.5 line-clamp-2">
                    Fokus Tahfidz Lanjutan (Juz 1–30), Tasmi' Sekali Duduk (1/4, 1/2, 1 Juz), Munaqasyah, dan Target Mutqin Wisuda.
                </p>

                <!-- Stats summary -->
                <div class="grid grid-cols-2 gap-2.5 mt-5 p-3.5 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Total Santri</span>
                        <div class="text-xl font-black text-indigo-950 mt-0.5"><?= number_format($counts['SMP_SMA'] ?? 0) ?></div>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Peserta Tasmi'</span>
                        <div class="text-xl font-black text-indigo-950 mt-0.5"><?= number_format($stats['smp_sma_setoran'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-indigo-100/60 flex items-center justify-between">
                <a href="<?= url('kelola-quran-siswa-smp-sma') ?>" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-500/20 text-center transition-all flex items-center justify-center gap-2 group-hover:gap-3">
                    <span>Masuk Portal SMP & SMA</span>
                    <svg class="w-4 h-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
        </div>

    </div>

    <!-- Live Activity: Riwayat Setoran Terkini Seluruh Unit -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="text-emerald-600">⚡</span>
                <h3 class="text-sm font-extrabold text-slate-800">Aktivitas Setoran Terkini (Seluruh Jenjang)</h3>
            </div>
            <span class="text-xs text-slate-400 font-medium">Diperbarui real-time</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/40 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-6">Tanggal</th>
                        <th class="py-3 px-6">Nama Siswa</th>
                        <th class="py-3 px-6">Jenjang & Kelas</th>
                        <th class="py-3 px-6">Kegiatan</th>
                        <th class="py-3 px-6">Materi Setoran</th>
                        <th class="py-3 px-6 text-center">Mutu</th>
                        <th class="py-3 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($recentSetoran)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada riwayat setoran yang dicatat. Klik tombol <strong>+ Input Setoran Cepat</strong> untuk memulai.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentSetoran as $row): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-6 font-medium text-slate-600 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($row['tanggal'])) ?>
                                </td>
                                <td class="py-3.5 px-6 font-bold text-slate-900">
                                    <?= htmlspecialchars($row['nama_lengkap']) ?>
                                </td>
                                <td class="py-3.5 px-6 whitespace-nowrap">
                                    <?= QuranHelper::getJenjangBadge($row['jenjang']) ?>
                                    <span class="text-slate-500 ml-1 text-[11px]"><?= htmlspecialchars($row['kelas'] ?? '') ?></span>
                                </td>
                                <td class="py-3.5 px-6 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700">
                                        <?= QuranHelper::getJenisSetoranLabel($row['jenis_setoran']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 font-medium text-slate-800">
                                    <?php if ($row['jenis_setoran'] === 'iqro'): ?>
                                        📖 Iqro' Jilid <?= $row['iqro_jilid'] ?> (Hal. <?= $row['iqro_halaman'] ?: '-' ?>)
                                    <?php else: ?>
                                        📗 <?= htmlspecialchars($row['surah_nama'] ?? 'Surah ' . $row['surah_nomor']) ?>
                                        <?php if ($row['ayat_awal']): ?>
                                            : Ayat <?= $row['ayat_awal'] ?>–<?= $row['ayat_akhir'] ?>
                                        <?php endif; ?>
                                        <?php if ($row['juz']): ?>
                                            <span class="text-slate-400 text-[10px]">(Juz <?= $row['juz'] ?>)</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                    <span class="inline-flex gap-1 text-[10px] font-bold">
                                        <span title="Kelancaran" class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">K: <?= $row['nilai_kelancaran'] ?></span>
                                        <span title="Tajwid" class="px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200">T: <?= $row['nilai_tajwid'] ?></span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                    <?= QuranHelper::getStatusBadge($row['status_lulus']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Setoran Included -->
<?php include MODULES_PATH . '/kelola-quran-siswa/views/partials/modal_setoran.php'; ?>
