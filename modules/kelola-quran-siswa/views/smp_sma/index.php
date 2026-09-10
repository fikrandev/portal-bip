<?php
/**
 * Qur'an Siswa SMP & SMA IT - Main Portal View
 */
$activeJenjang = 'SMP_SMA';
include MODULES_PATH . '/kelola-quran-siswa/views/partials/top_tabs.php';
?>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-100 text-indigo-800 border border-indigo-300">
                    Jenjang SMP & SMA IT
                </span>
                <span class="text-xs text-slate-400">Tingkat Menengah & Atas (Kelas 7–12)</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <span>🎓</span> Qur'an Siswa SMP & SMA IT
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Tahfidz Multi-Juz (Juz 1–30), Muroja'ah Kubro, Ujian Tasmi' Sekali Duduk, Munaqasyah, dan Penyiapan Wisuda Tahfidz.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Cetak -->
            <a href="<?= url('kelola-quran-siswa/cetak?jenjang=SMP_SMA') ?>" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-xs transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.327 2.45-6.079 5.28-6.079 2.83 0 5.52 2.752 5.28 6.079m-10.56 0A5.998 5.998 0 0 0 12 19.5c2.83 0 5.28-2.343 5.28-5.671m-10.56 0C6.72 10.5 9.17 8.157 12 8.157m0 0a5.998 5.998 0 0 1 5.28 5.672M6 20.25h12M9 3.75h6" />
                </svg>
                <span>Cetak Rekap SMP/SMA</span>
            </a>

            <!-- Tombol Setoran -->
            <button type="button" onclick="openModalSetoran('SMP_SMA')"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Catat Setoran / Tasmi'</span>
            </button>
        </div>
    </div>

    <!-- Stats SMP & SMA -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total Santri SMP/SMA -->
        <div class="bg-white rounded-2xl p-4 border border-indigo-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700">Total Santri Remaja</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-indigo-950"><?= number_format($stats['total_siswa'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-800">SMP & SMA</span>
            </div>
        </div>

        <!-- Total Setoran / Tasmi -->
        <div class="bg-white rounded-2xl p-4 border border-blue-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700">Setoran & Tasmi'</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-blue-700"><?= number_format($stats['total_setoran'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-800">Riwayat</span>
            </div>
        </div>

        <!-- Peserta Tasmi Sekali Duduk -->
        <div class="bg-white rounded-2xl p-4 border border-violet-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-violet-700">Tasmi' Sekali Duduk</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-violet-700"><?= number_format($stats['total_tasmi'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-violet-50 text-violet-800">Peserta</span>
            </div>
        </div>

        <!-- Mutqin -->
        <div class="bg-white rounded-2xl p-4 border border-emerald-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Capaian Mutqin ⭐</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-emerald-700"><?= number_format($stats['total_mutqin'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800">Mumtaz</span>
            </div>
        </div>
    </div>

    <!-- Banner Program Unggulan Tasmi' -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-5 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-indigo-900/50 shadow-md">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl shrink-0">
                🎙️
            </div>
            <div>
                <h4 class="text-sm font-extrabold text-white">Program Tasmi' Sekali Duduk & Ujian Munaqasyah</h4>
                <p class="text-xs text-indigo-200/80 mt-0.5">Ujian memperdengarkan hafalan secara langsung 1/4 Juz, 1/2 Juz, 1 Juz hingga 5 Juz sekali duduk tanpa kesalahan.</p>
            </div>
        </div>
        <button type="button" onclick="openModalSetoran('SMP_SMA')" 
                class="px-4 py-2 rounded-xl bg-indigo-500 hover:bg-indigo-600 text-white font-bold text-xs whitespace-nowrap self-start sm:self-auto transition-colors">
            + Catat Nilai Tasmi'
        </button>
    </div>

    <!-- Main Content: Dua Kolom (Daftar Santri & Riwayat Setoran SMP/SMA) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri (7 Kolom): Santri SMP/SMA -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span>👥</span> Santri Tahfidz SMP & SMA
                    </h3>
                    <p class="text-[11px] text-slate-400">Target kelulusan mutqin dan capaian juz santri</p>
                </div>
                <span class="text-xs font-bold text-indigo-800 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200">
                    <?= count($students) ?> Santri
                </span>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/40 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-5">Nama Santri</th>
                            <th class="py-3 px-4">Jenjang & Kelas</th>
                            <th class="py-3 px-4">Setoran Terakhir</th>
                            <th class="py-3 px-4 text-center">Total Setoran</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada data santri SMP/SMA.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <tr class="hover:bg-indigo-50/40 transition-colors">
                                    <td class="py-3 px-5">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                        <div class="text-[10px] text-slate-400">NIS: <?= htmlspecialchars($s['nis'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <?= htmlspecialchars($s['jenjang']) ?>
                                        </span>
                                        <span class="text-slate-600 font-medium ml-1"><?= htmlspecialchars($s['kelas'] ?? '-') ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">
                                        <?php if (!empty($s['latest_surah'])): ?>
                                            <div class="font-semibold text-indigo-900">
                                                <?= htmlspecialchars($s['latest_surah']) ?>
                                                <?php if ($s['latest_ayat']): ?>: <?= $s['latest_ayat'] ?><?php endif; ?>
                                            </div>
                                            <div class="text-[10px] text-slate-400"><?= date('d/m/Y', strtotime($s['latest_tanggal'])) ?></div>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-[11px] italic">Belum ada setoran</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap font-bold text-slate-800">
                                        <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs">
                                            <?= intval($s['setoran_count'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-5 text-right whitespace-nowrap">
                                        <button type="button" onclick="openModalSetoran('SMP_SMA', '<?= $s['id'] ?>')" 
                                                class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] shadow-xs transition-all flex items-center gap-1 ml-auto">
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

        <!-- Kolom Kanan (5 Kolom): Log Riwayat Setoran SMP/SMA -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span>📋</span> Riwayat Tahfidz SMP & SMA
                </h3>
                <span class="text-xs text-slate-400 font-medium">Terbaru</span>
            </div>

            <div class="divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[600px] custom-scrollbar">
                <?php if (empty($setoranList)): ?>
                    <div class="py-10 text-center text-slate-400 text-xs px-4">
                        Belum ada catatan setoran atau tasmi' santri SMP/SMA.
                    </div>
                <?php else: ?>
                    <?php foreach ($setoranList as $log): ?>
                        <div class="p-4 hover:bg-slate-50/80 transition-colors space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($log['nama_lengkap']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($log['kelas'] ?? 'SMP/SMA') ?> • <?= date('d M Y', strtotime($log['tanggal'])) ?></div>
                                </div>
                                <div>
                                    <?= QuranHelper::getStatusBadge($log['status_lulus']) ?>
                                </div>
                            </div>

                            <div class="bg-indigo-50/50 p-2.5 rounded-xl border border-indigo-100 text-xs">
                                <div class="flex items-center justify-between text-slate-800 font-semibold">
                                    <span>📗 <?= htmlspecialchars($log['surah_nama'] ?? 'Surah ' . $log['surah_nomor']) ?> : <?= $log['ayat_awal'] ?: '1' ?>–<?= $log['ayat_akhir'] ?: '-' ?></span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-white text-indigo-700 border border-indigo-200 font-bold"><?= QuranHelper::getJenisSetoranLabel($log['jenis_setoran']) ?></span>
                                </div>
                                <?php if ($log['juz']): ?>
                                    <div class="text-[10px] text-indigo-600 font-bold mt-0.5">Juz <?= $log['juz'] ?></div>
                                <?php endif; ?>
                                <?php if (!empty($log['catatan'])): ?>
                                    <p class="text-[11px] text-slate-600 mt-1 italic">"<?= htmlspecialchars($log['catatan']) ?>"</p>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center justify-between pt-1 text-[10px] text-slate-400">
                                <span>Kelancaran: <strong><?= $log['nilai_kelancaran'] ?></strong>, Tajwid: <strong><?= $log['nilai_tajwid'] ?></strong></span>
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
