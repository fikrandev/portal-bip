<?php
/**
 * View: Detail Wadah Absen Mapel (Buka Wadah Absen)
 * Alur terstruktur seperti Kelola Nilai:
 * Pilih Guru Pengampu (untuk Admin) -> Muncul seluruh Mata Pelajaran & Kelas yang diampu -> Klik "⚡ Input Absen"
 */
$unit_list = [
    'PAUD' => ['name' => 'PAUD / TK', 'badge' => 'bg-pink-50 text-pink-700 border-pink-200', 'icon' => '🧸'],
    'SD'   => ['name' => 'SD (Sekolah Dasar)', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => '🎒'],
    'SMP'  => ['name' => 'SMP (Sekolah Menengah Pertama)', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => '📚'],
    'SMA'  => ['name' => 'SMA / SMK', 'badge' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => '🎓']
];

$rowUnit = $group['unit'] ?? 'SD';
$uBadge = $unit_list[$rowUnit]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-300';
$uIcon = $unit_list[$rowUnit]['icon'] ?? '🏫';
$totalPenugasan = count($penugasanWithStats);
?>

<div class="space-y-6">
    <!-- Header Wadah -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1.5">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold border <?= $uBadge ?>">
                    <span><?= $uIcon ?></span>
                    <span>Unit <?= htmlspecialchars($rowUnit) ?></span>
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    <?= htmlspecialchars($group['nama_tahun'] ?? '2026/2027') ?> &bull; Semester <?= htmlspecialchars($group['semester'] ?? 'Ganjil') ?>
                </span>
                <?php if ($isAdmin): ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Mode Administrator
                    </span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-teal-50 text-teal-700 border border-teal-200">
                        Mode Guru Pengampu
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-800 tracking-tight">
                <?= htmlspecialchars($group['judul']) ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                <?= $isAdmin ? 'Pilih guru pengampu di bawah ini untuk melihat daftar kelas yang diampu, lalu klik tombol "Input Absen".' : 'Daftar mata pelajaran dan kelas yang Anda ampu pada wadah ini.' ?>
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <a href="<?= url('kelola-absen-siswa/mapel') ?>" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Daftar Wadah</span>
            </a>
            <a href="<?= url('kelola-absen-siswa/rekap?mode=mapel') ?>" class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rekap Absen</span>
            </a>
        </div>
    </div>

    <!-- Panel Pemilihan Guru (Admin) & Profil Guru Aktif -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            <!-- Informasi Guru Terpilih -->
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-black text-xl flex items-center justify-center shadow-md shadow-emerald-500/20 shrink-0">
                    <?= strtoupper(mb_substr($selectedGuru['nama'] ?? 'G', 0, 1)) ?>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Guru Pengampu Terpilih
                        </span>
                        <?php if (!empty($selectedGuru['niy'])): ?>
                            <span class="text-[11px] text-slate-400 font-semibold">NIY. <?= htmlspecialchars($selectedGuru['niy']) ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 mt-1">
                        <?= htmlspecialchars($selectedGuru['nama'] ?? 'Pilih Guru') ?><?= !empty($selectedGuru['gelar']) ? ', ' . htmlspecialchars($selectedGuru['gelar']) : '' ?>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Mengampu <strong><?= $totalPenugasan ?> Rombel/Kelas</strong> pada tahun ajaran ini
                    </p>
                </div>
            </div>

            <!-- Selector Dropdown Guru (Khusus Mode Admin) -->
            <?php if ($isAdmin): ?>
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3 sm:p-3.5 flex flex-col sm:flex-row sm:items-center gap-3">
                    <label class="text-xs font-bold text-slate-600 shrink-0 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Ganti Guru:</span>
                    </label>
                    <select onchange="window.location.href = '<?= url('kelola-absen-siswa/input/' . $groupId) ?>?guru_id=' + this.value" 
                            class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none min-w-[240px]">
                        <?php foreach ($guruList as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= $selectedGuruId == $g['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['nama']) ?><?= !empty($g['gelar']) ? ', ' . htmlspecialchars($g['gelar']) : '' ?> (<?= $g['total_kelas'] ?> Kelas)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Daftar Mata Pelajaran & Kelas yang Dia Ampu (Siap Diabsen) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span>Mata Pelajaran & Kelas yang Diampu</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                        <?= $totalPenugasan ?>
                    </span>
                </h3>
                <p class="text-xs text-slate-400">Klik tombol hijau <strong>Input Absen</strong> pada kelas yang ingin Anda catat presensinya.</p>
            </div>
        </div>

        <?php if (empty($penugasanWithStats)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-12 text-center shadow-sm">
                <div class="w-16 h-16 mx-auto bg-slate-100 rounded-2xl flex items-center justify-center text-3xl mb-3">
                    👨‍🏫
                </div>
                <h4 class="text-base font-bold text-slate-800">Belum Ada Penugasan Mengajar</h4>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                    Guru <strong><?= htmlspecialchars($selectedGuru['nama'] ?? 'ini') ?></strong> belum memiliki penugasan mengajar rombel di data master sekolah.
                </p>
                <?php if ($isAdmin): ?>
                    <p class="text-xs text-slate-400 mt-2">Silakan pilih guru lain melalui dropdown pilihan di atas.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Grid Card Kelas & Mapel -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($penugasanWithStats as $item): 
                    $cleanKelas = $item['clean_kelas'];
                    $mapel = $item['mata_pelajaran'];
                    $sesiCount = $item['sesi_count'];
                    $inputUrl = url('kelola-absen-siswa/mapel/input?group_id=' . $groupId . '&guru_id=' . $selectedGuruId . '&mapel=' . urlencode($mapel) . '&kelas=' . urlencode($cleanKelas));
                    $rekapUrl = url('kelola-absen-siswa/rekap?mode=mapel&guru_id=' . $selectedGuruId . '&mapel=' . urlencode($mapel) . '&kelas=' . urlencode($cleanKelas));
                ?>
                    <div class="bg-white rounded-3xl border border-slate-200/80 hover:border-emerald-300 hover:shadow-md transition-all p-5 flex flex-col justify-between">
                        <div>
                            <!-- Header Kartu -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Kelas <?= htmlspecialchars($cleanKelas) ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold text-slate-500 bg-slate-100 border border-slate-200/60">
                                    <?= $item['jumlah_jp'] ?> JP
                                </span>
                            </div>

                            <!-- Mata Pelajaran -->
                            <h4 class="text-base font-bold text-slate-800 leading-snug">
                                <?= htmlspecialchars($mapel) ?>
                            </h4>
                            <div class="text-xs font-semibold text-slate-400 mt-1 flex items-center gap-2">
                                <span><?= htmlspecialchars($item['nama_kelas']) ?></span>
                                <span>&bull;</span>
                                <span><?= $item['total_siswa'] ?> Siswa</span>
                            </div>

                            <!-- Info Sesi Presensi -->
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <?php if ($sesiCount > 0): ?>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="inline-flex items-center gap-1 font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/60">
                                            <span>✓</span>
                                            <span><?= $sesiCount ?> Pertemuan Dicatat</span>
                                        </span>
                                        <?php if (!empty($item['last_session'])): ?>
                                            <span class="text-[10px] text-slate-400 font-semibold">
                                                Pert. <?= $item['last_session']['pertemuan_ke'] ?> (<?= date('d/m', strtotime($item['last_session']['tanggal'])) ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-xs text-slate-400 font-medium italic">
                                        <span>○</span>
                                        <span>Belum ada pertemuan dicatat</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <a href="<?= $rekapUrl ?>" class="text-xs font-bold text-slate-500 hover:text-emerald-700 hover:underline px-2 py-1">
                                Lihat Rekap &rarr;
                            </a>
                            <a href="<?= $inputUrl ?>" 
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 hover:scale-[1.02] transition-all">
                                <span>⚡</span>
                                <span>Input Absen</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
