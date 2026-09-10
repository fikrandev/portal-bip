<?php
/**
 * Qur'an Siswa PAUD / TK - Main Portal View
 */
$activeJenjang = 'PAUD';
include MODULES_PATH . '/kelola-quran-siswa/views/partials/top_tabs.php';
?>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 border border-amber-300">
                    Jenjang PAUD / TK
                </span>
                <span class="text-xs text-slate-400">Pendidikan Anak Usia Dini</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <span>🧸</span> Qur'an Siswa PAUD / TK
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Metode Iqro' / Tilawati Jilid 1–6, hafalan surah-surah pendek Juz 'Amma, doa harian, dan pembinaan adab Al-Qur'an.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Cetak -->
            <a href="<?= url('kelola-quran-siswa/cetak?jenjang=PAUD') ?>" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-xs transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.327 2.45-6.079 5.28-6.079 2.83 0 5.52 2.752 5.28 6.079m-10.56 0A5.998 5.998 0 0 0 12 19.5c2.83 0 5.28-2.343 5.28-5.671m-10.56 0C6.72 10.5 9.17 8.157 12 8.157m0 0a5.998 5.998 0 0 1 5.28 5.672M6 20.25h12M9 3.75h6" />
                </svg>
                <span>Cetak Rekap PAUD</span>
            </a>

            <!-- Tombol Setoran -->
            <button type="button" onclick="openModalSetoran('PAUD')"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Catat Setoran PAUD</span>
            </button>
        </div>
    </div>

    <!-- Stats PAUD -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total Santri PAUD -->
        <div class="bg-white rounded-2xl p-4 border border-amber-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Total Santri Cilik</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-amber-950"><?= number_format($stats['total_siswa'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800">Santri</span>
            </div>
        </div>

        <!-- Setoran Iqro -->
        <div class="bg-white rounded-2xl p-4 border border-orange-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-orange-700">Setoran Iqro' / Tilawati</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-orange-700"><?= number_format($stats['total_iqro'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-orange-50 text-orange-800">Jilid 1–6</span>
            </div>
        </div>

        <!-- Hafalan Surah Pendek -->
        <div class="bg-white rounded-2xl p-4 border border-emerald-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Hafalan Surah Pendek</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-emerald-700"><?= number_format($stats['total_surah'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800">Juz 'Amma</span>
            </div>
        </div>

        <!-- Mutqin -->
        <div class="bg-white rounded-2xl p-4 border border-teal-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700">Lulus / Mutqin ⭐</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-teal-700"><?= number_format($stats['total_mutqin'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-800">Mumtaz</span>
            </div>
        </div>
    </div>

    <!-- Papan Tahapan Iqro (Visual Roadmap) -->
    <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/10 rounded-3xl border border-amber-200 p-5">
        <h4 class="text-xs font-extrabold text-amber-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
            <span>✨</span> Tahapan Pembelajaran Iqro' & Tilawati Usia Dini
        </h4>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
            <?php foreach (QuranHelper::getIqroLevels() as $lvl => $desc): ?>
                <div class="bg-white rounded-2xl p-3 border border-amber-200/60 shadow-xs text-center flex flex-col justify-between">
                    <div>
                        <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-black text-xs mx-auto flex items-center justify-center">
                            <?= $lvl ?>
                        </div>
                        <div class="font-extrabold text-xs text-slate-800 mt-2">Jilid <?= $lvl ?></div>
                        <p class="text-[10px] text-slate-500 mt-0.5 leading-tight"><?= htmlspecialchars(explode('(', $desc)[1] ?? '') ? rtrim(explode('(', $desc)[1], ')') : '' ?></p>
                    </div>
                    <button type="button" onclick="openModalSetoran('PAUD')" class="mt-2 text-[10px] font-bold text-amber-600 hover:text-amber-800">
                        + Catat
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Content: Dua Kolom (Daftar Santri PAUD & Riwayat Setoran) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri (7 Kolom): Santri PAUD -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span>👶</span> Santri Cilik PAUD & TK
                    </h3>
                    <p class="text-[11px] text-slate-400">Daftar santri dan capaian pembelajaran Iqro / surah pendek</p>
                </div>
                <span class="text-xs font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                    <?= count($students) ?> Santri
                </span>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/40 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-5">Nama Santri</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Capaian Terkini</th>
                            <th class="py-3 px-4 text-center">Setoran</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada data santri PAUD/TK.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <tr class="hover:bg-amber-50/40 transition-colors">
                                    <td class="py-3 px-5">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                        <div class="text-[10px] text-slate-400">NIS: <?= htmlspecialchars($s['nis'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-slate-600 font-medium">
                                        <?= htmlspecialchars($s['kelas'] ?? 'PAUD') ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">
                                        <?php if (!empty($s['latest_materi'])): ?>
                                            <div class="font-semibold text-amber-800">
                                                <?= htmlspecialchars($s['latest_materi']) ?>
                                            </div>
                                            <div class="text-[10px] text-slate-400"><?= date('d/m/Y', strtotime($s['latest_tanggal'])) ?></div>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-[11px] italic">Memulai Jilid 1</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap font-bold text-slate-800">
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs">
                                            <?= intval($s['setoran_count'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-5 text-right whitespace-nowrap">
                                        <button type="button" onclick="openModalSetoran('PAUD', '<?= $s['id'] ?>')" 
                                                class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-xs transition-all flex items-center gap-1 ml-auto">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                            <span>+ Setor</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolom Kanan (5 Kolom): Log Riwayat Setoran PAUD -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span>📖</span> Riwayat Bacaan PAUD
                </h3>
                <span class="text-xs text-slate-400 font-medium">Terbaru</span>
            </div>

            <div class="divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[600px] custom-scrollbar">
                <?php if (empty($setoranList)): ?>
                    <div class="py-10 text-center text-slate-400 text-xs px-4">
                        Belum ada catatan bacaan untuk santri PAUD. Gunakan tombol setor untuk mencatat jilid Iqro atau surah pendek.
                    </div>
                <?php else: ?>
                    <?php foreach ($setoranList as $log): ?>
                        <div class="p-4 hover:bg-slate-50/80 transition-colors space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($log['nama_lengkap']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($log['kelas'] ?? 'PAUD') ?> • <?= date('d M Y', strtotime($log['tanggal'])) ?></div>
                                </div>
                                <div>
                                    <?= QuranHelper::getStatusBadge($log['status_lulus']) ?>
                                </div>
                            </div>

                            <div class="bg-amber-50/60 p-2.5 rounded-xl border border-amber-100 text-xs">
                                <div class="flex items-center justify-between text-slate-800 font-semibold">
                                    <?php if ($log['jenis_setoran'] === 'iqro'): ?>
                                        <span>📖 Iqro' Jilid <?= $log['iqro_jilid'] ?> (Halaman <?= $log['iqro_halaman'] ?: '-' ?>)</span>
                                    <?php else: ?>
                                        <span>📗 <?= htmlspecialchars($log['surah_nama'] ?? 'Surah') ?> : Ayat <?= $log['ayat_awal'] ?: '1' ?>–<?= $log['ayat_akhir'] ?: '-' ?></span>
                                    <?php endif; ?>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-white text-amber-800 border border-amber-200 font-bold"><?= QuranHelper::getJenisSetoranLabel($log['jenis_setoran']) ?></span>
                                </div>
                                <?php if (!empty($log['catatan'])): ?>
                                    <p class="text-[11px] text-slate-600 mt-1 italic">"<?= htmlspecialchars($log['catatan']) ?>"</p>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center justify-between pt-1 text-[10px] text-slate-400">
                                <span>Mutu: Kelancaran <strong><?= $log['nilai_kelancaran'] ?></strong>, Makhorij <strong><?= $log['nilai_makhorij'] ?></strong></span>
                                <form action="<?= url('kelola-quran-siswa/setoran/delete/' . $log['id']) ?>" method="POST" onsubmit="return confirm('Hapus catatan setoran ini?');">
                                    <?= CSRF::field() ?>
                                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold">Hapus</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- Modal Setoran Included -->
<?php include MODULES_PATH . '/kelola-quran-siswa/views/partials/modal_setoran.php'; ?>
