<?php
/**
 * Qur'an Siswa SD IT - Main Portal View
 */
$activeJenjang = 'SD';
include MODULES_PATH . '/kelola-quran-siswa/views/partials/top_tabs.php';
?>

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                    Jenjang SD IT
                </span>
                <span class="text-xs text-slate-400">Kelas 1 s/d 6</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2.5">
                <span>🎒</span> Qur'an Siswa SD (Sekolah Dasar)
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Fokus Ziyadah hafalan baru, Muroja'ah berkala Juz 30 & Juz 29, penuntasan makhorijul huruf dan tajwid dasar.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Cetak -->
            <a href="<?= url('kelola-quran-siswa/cetak?jenjang=SD' . (!empty($filterKelas) ? '&kelas=' . urlencode($filterKelas) : '')) ?>" target="_blank"
               class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-xs transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-3.327 2.45-6.079 5.28-6.079 2.83 0 5.52 2.752 5.28 6.079m-10.56 0A5.998 5.998 0 0 0 12 19.5c2.83 0 5.28-2.343 5.28-5.671m-10.56 0C6.72 10.5 9.17 8.157 12 8.157m0 0a5.998 5.998 0 0 1 5.28 5.672M6 20.25h12M9 3.75h6" />
                </svg>
                <span>Cetak Rekap SD</span>
            </a>

            <!-- Export -->
            <a href="<?= url('kelola-quran-siswa/export?jenjang=SD' . (!empty($filterKelas) ? '&kelas=' . urlencode($filterKelas) : '')) ?>"
               class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-xs transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh Excel/CSV</span>
            </a>

            <!-- Tombol Setoran -->
            <button type="button" onclick="openModalSetoran('SD')"
                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>+ Catat Setoran SD</span>
            </button>
        </div>
    </div>

    <!-- Stats SD -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total Siswa SD -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Siswa SD</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-slate-800"><?= number_format($stats['total_siswa'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Terdaftar</span>
            </div>
        </div>

        <!-- Total Setoran SD -->
        <div class="bg-white rounded-2xl p-4 border border-emerald-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Total Setoran Tercatat</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-emerald-700"><?= number_format($stats['total_setoran'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800">Riwayat</span>
            </div>
        </div>

        <!-- Mutqin -->
        <div class="bg-white rounded-2xl p-4 border border-teal-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700">Capaian Mutqin ⭐</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-teal-700"><?= number_format($stats['total_mutqin'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-teal-50 text-teal-800">Sempurna</span>
            </div>
        </div>

        <!-- Perlu Bimbingan / Ulang -->
        <div class="bg-white rounded-2xl p-4 border border-amber-200/70 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Perlu Bimbingan / Ulang</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-2xl font-black text-amber-700"><?= number_format($stats['total_ulang'] ?? 0) ?></span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800">Evaluasi</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <form action="<?= url('kelola-quran-siswa-sd') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <!-- Search -->
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Cari Nama / NISN</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($searchQuery ?? '') ?>" placeholder="Ketik nama siswa atau NISN..." 
                           class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
            </div>

            <!-- Filter Kelas -->
            <div>
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Pilih Kelas / Rombel</label>
                <select name="kelas" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Kelas SD</option>
                    <?php if (!empty($kelasList)): ?>
                        <?php foreach ($kelasList as $kls): ?>
                            <option value="<?= htmlspecialchars($kls) ?>" <?= ($filterKelas === $kls) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kls) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Submit -->
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-colors">
                    Terapkan Filter
                </button>
                <?php if (!empty($searchQuery) || !empty($filterKelas)): ?>
                    <a href="<?= url('kelola-quran-siswa-sd') ?>" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs" title="Reset Filter">
                        ✕
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Main Content: Dua Kolom (Daftar Siswa & Riwayat Setoran SD) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri (7 Kolom): Daftar Siswa SD & Progres Setoran -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <span>👥</span> Data Capaian Siswa SD
                    </h3>
                    <p class="text-[11px] text-slate-400">Pilih tombol setor untuk input cepat setoran siswa</p>
                </div>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    <?= count($students) ?> Siswa Ditemukan
                </span>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/40 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3 px-5">Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Setoran Terakhir</th>
                            <th class="py-3 px-4 text-center">Total Setor</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada data siswa SD yang sesuai dengan filter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $s): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-5">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($s['nama_lengkap']) ?></div>
                                        <div class="text-[10px] text-slate-400">NISN: <?= htmlspecialchars($s['nisn'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-slate-600 font-medium">
                                        <?= htmlspecialchars($s['kelas'] ?? '-') ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">
                                        <?php if (!empty($s['latest_surah'])): ?>
                                            <div class="font-semibold text-emerald-800">
                                                <?= htmlspecialchars($s['latest_surah']) ?>
                                                <?php if ($s['latest_ayat']): ?>: <?= $s['latest_ayat'] ?><?php endif; ?>
                                            </div>
                                            <div class="text-[10px] text-slate-400"><?= date('d/m/Y', strtotime($s['latest_tanggal'])) ?></div>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-[11px] italic">Belum ada setoran</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap font-bold text-slate-800">
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-xs">
                                            <?= intval($s['setoran_count'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-5 text-right whitespace-nowrap">
                                        <button type="button" onclick="openModalSetoran('SD', '<?= $s['id'] ?>')" 
                                                class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs transition-all flex items-center gap-1 ml-auto">
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

            <!-- Pagination info -->
            <?php if ($totalPages > 1): ?>
                <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= $totalStudents ?> Siswa)</span>
                    <div class="flex gap-1">
                        <?php if ($page > 1): ?>
                            <a href="<?= url('kelola-quran-siswa-sd?page=' . ($page - 1) . (!empty($filterKelas) ? '&kelas=' . urlencode($filterKelas) : '') . (!empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '')) ?>" 
                               class="px-3 py-1 rounded-lg border border-slate-200 hover:bg-slate-50">Sebelumnya</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?= url('kelola-quran-siswa-sd?page=' . ($page + 1) . (!empty($filterKelas) ? '&kelas=' . urlencode($filterKelas) : '') . (!empty($searchQuery) ? '&search=' . urlencode($searchQuery) : '')) ?>" 
                               class="px-3 py-1 rounded-lg border border-slate-200 hover:bg-slate-50">Selanjutnya</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Kanan (5 Kolom): Log Riwayat Setoran SD Terbaru -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <span>📋</span> Riwayat Setoran SD
                </h3>
                <span class="text-xs text-slate-400 font-medium">Terbaru</span>
            </div>

            <div class="divide-y divide-slate-100 flex-1 overflow-y-auto max-h-[600px] custom-scrollbar">
                <?php if (empty($setoranList)): ?>
                    <div class="py-10 text-center text-slate-400 text-xs px-4">
                        Belum ada catatan setoran untuk siswa SD. Gunakan tombol setor untuk mencatat hafalan baru.
                    </div>
                <?php else: ?>
                    <?php foreach ($setoranList as $log): ?>
                        <div class="p-4 hover:bg-slate-50/80 transition-colors space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($log['nama_lengkap']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($log['kelas'] ?? 'SD') ?> • <?= date('d M Y', strtotime($log['tanggal'])) ?></div>
                                </div>
                                <div>
                                    <?= QuranHelper::getStatusBadge($log['status_lulus']) ?>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-xs">
                                <div class="flex items-center justify-between text-slate-800 font-semibold">
                                    <span>📗 <?= htmlspecialchars($log['surah_nama'] ?? 'Surah ' . $log['surah_nomor']) ?> : <?= $log['ayat_awal'] ?: '1' ?>–<?= $log['ayat_akhir'] ?: '-' ?></span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-white text-slate-500 border border-slate-200 font-bold"><?= QuranHelper::getJenisSetoranLabel($log['jenis_setoran']) ?></span>
                                </div>
                                <?php if (!empty($log['catatan'])): ?>
                                    <p class="text-[11px] text-slate-500 mt-1 italic">"<?= htmlspecialchars($log['catatan']) ?>"</p>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center justify-between pt-1 text-[10px] text-slate-400">
                                <span>Nilai: Kelancaran <strong><?= $log['nilai_kelancaran'] ?></strong>, Tajwid <strong><?= $log['nilai_tajwid'] ?></strong></span>
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
