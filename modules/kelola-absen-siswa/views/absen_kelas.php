<?php
/**
 * View: Input Absen Kelas (Wali Kelas / Harian)
 * Admin flow: Pilih kelas dari daftar kelas aktif -> input presensi harian
 * Wali Kelas flow: Otomatis mendeteksi kelas binaannya
 */
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Presensi Rombel / Wali Kelas
                </span>
                <?php if ($isAdmin): ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Mode Administrator (Pilih Kelas)
                    </span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Mode Wali Kelas
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">Input Absen Kelas Harian</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pencatatan kehadiran seluruh siswa harian di rombongan belajar oleh Wali Kelas atau Petugas Piket</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-absen-siswa') ?>" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Kembali ke Dashboard
            </a>
            <a href="<?= url('kelola-absen-siswa/rekap?mode=kelas') ?>" class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rekap Absen Kelas</span>
            </a>
        </div>
    </div>

    <!-- Filter & Pemilihan Kelas Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
        <form method="GET" action="<?= url('kelola-absen-siswa/kelas') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <!-- Pilih Kelas -->
            <div class="md:col-span-2 space-y-1">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Pilih Rombongan Belajar / Kelas:</label>
                <select name="kelas" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-700 bg-slate-50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= htmlspecialchars($k['kelas']) ?>" <?= $selectedKelas === $k['kelas'] ? 'selected' : '' ?>>
                            Kelas <?= htmlspecialchars($k['kelas']) ?> (Jenjang <?= htmlspecialchars($k['jenjang']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pilih Tanggal Presensi -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal Presensi:</label>
                <input type="date" name="tanggal" value="<?= $tanggal ?>" onchange="this.form.submit()" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-700 bg-slate-50 focus:ring-2 focus:ring-teal-500 focus:outline-none">
            </div>
        </form>
    </div>

    <!-- Kelas Highlight Card -->
    <?php if (!empty($selectedKelas)): ?>
    <div class="bg-gradient-to-r from-teal-600 to-emerald-700 rounded-3xl p-6 text-white shadow-lg shadow-teal-600/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-3xl shrink-0">
                🏫
            </div>
            <div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/20 text-teal-100 border border-white/20">
                    Rombongan Belajar Aktif
                </span>
                <h2 class="text-xl sm:text-2xl font-black mt-1">
                    Kelas <?= htmlspecialchars($selectedKelas) ?>
                </h2>
                <p class="text-xs text-teal-100/80 mt-0.5">
                    Tanggal Presensi: <strong><?= date('d F Y', strtotime($tanggal)) ?></strong>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-absen-siswa/kelas/input?kelas=' . urlencode($selectedKelas) . '&tanggal=' . $tanggal) ?>" 
               class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-white text-teal-800 hover:bg-teal-50 font-black text-xs sm:text-sm shadow-md transition-all">
                <span>⚡ Buka Form Presensi Hari Ini &rarr;</span>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Riwayat Sesi Presensi Harian Kelas Ini -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Riwayat Presensi Harian Kelas <?= htmlspecialchars($selectedKelas) ?></h3>
                <p class="text-xs text-slate-400">Daftar hari yang telah dicatat presensi hariannya</p>
            </div>
            <a href="<?= url('kelola-absen-siswa/rekap?mode=kelas&kelas=' . urlencode($selectedKelas)) ?>" class="text-xs font-bold text-teal-600 hover:text-teal-700 hover:underline">
                Lihat Matriks Bulanan &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Tanggal Presensi</th>
                        <th class="py-3 px-4">Hari</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Catatan Wali Kelas</th>
                        <th class="py-3 px-4 text-center">Kehadiran</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($recentKelasSessions)): ?>
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400">
                                <p class="font-bold text-slate-600">Belum ada riwayat presensi harian untuk kelas ini</p>
                                <p class="text-[11px] mt-0.5">Klik tombol "Buka Form Presensi Hari Ini" di atas untuk mulai mencatat presensi.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentKelasSessions as $rks): 
                            $rate = $rks['total_siswa'] > 0 ? round(($rks['total_hadir'] / $rks['total_siswa']) * 100) : 0;
                            $dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][date('w', strtotime($rks['tanggal']))];
                        ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    <?= date('d M Y', strtotime($rks['tanggal'])) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 font-semibold">
                                    <?= $dayName ?>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-700">
                                    <?= htmlspecialchars($rks['kelas']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 max-w-xs truncate">
                                    <?= htmlspecialchars($rks['catatan'] ?: '-') ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $rate >= 85 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                                        <?= $rks['total_hadir'] ?>/<?= $rks['total_siswa'] ?> (<?= $rate ?>%)
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="<?= url('kelola-absen-siswa/kelas/input?kelas=' . urlencode($rks['kelas']) . '&tanggal=' . $rks['tanggal']) ?>" 
                                       class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-teal-50 hover:text-teal-700 text-slate-600 font-bold text-[11px] transition-all">
                                        Buka Presensi
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
