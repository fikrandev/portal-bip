<?php
/**
 * View: Input Absen Mapel
 * Terintegrasi langsung dengan Wadah Grup Absen:
 * Pengguna membuat / memilih wadah terlebih dahulu, kemudian melakukan input absen.
 */
$unit_list = [
    'PAUD' => ['name' => 'PAUD / TK', 'badge' => 'bg-pink-50 text-pink-700 border-pink-200', 'icon' => '🧸'],
    'SD'   => ['name' => 'SD (Sekolah Dasar)', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'icon' => '🎒'],
    'SMP'  => ['name' => 'SMP (Sekolah Menengah Pertama)', 'badge' => 'bg-blue-50 text-blue-700 border-blue-200', 'icon' => '📚'],
    'SMA'  => ['name' => 'SMA / SMK', 'badge' => 'bg-purple-50 text-purple-700 border-purple-200', 'icon' => '🎓']
];

$curStatus = $_GET['status'] ?? '';
$curUnit = $_GET['unit'] ?? '';
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Input Absen Mapel
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
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">Input Absen Mapel</h1>
            <p class="text-xs sm:text-sm text-slate-500">Pilih wadah grup absen mapel di bawah ini untuk mencatat presensi pertemuan, atau buat wadah baru</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="<?= url('kelola-absen-siswa') ?>" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Kembali
            </a>
            <a href="<?= url('kelola-absen-siswa/rekap?mode=mapel') ?>" class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Rekap Absen</span>
            </a>
            <a href="<?= url('kelola-absen-siswa/group/create?tipe=mapel' . ($selectedGuruId ? '&guru_id=' . $selectedGuruId : '')) ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-emerald-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Wadah Absen Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-400 mr-1 uppercase">Status:</span>
            <a href="<?= url('kelola-absen-siswa/mapel' . (!empty($curUnit) ? '?unit=' . urlencode($curUnit) : '') . ($selectedGuruId && $isAdmin ? (!empty($curUnit) ? '&' : '?') . 'guru_id=' . $selectedGuruId : '')) ?>" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= empty($curStatus) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Semua (<?= $totalAll ?? count($groups) ?>)
            </a>
            <a href="<?= url('kelola-absen-siswa/mapel?status=aktif' . (!empty($curUnit) ? '&unit=' . urlencode($curUnit) : '') . ($selectedGuruId && $isAdmin ? '&guru_id=' . $selectedGuruId : '')) ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $curStatus === 'aktif' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span class="w-2 h-2 rounded-full <?= $curStatus === 'aktif' ? 'bg-white' : 'bg-emerald-500' ?>"></span>
                <span>Aktif (<?= $totalActive ?? 0 ?>)</span>
            </a>
            <a href="<?= url('kelola-absen-siswa/mapel?status=nonaktif' . (!empty($curUnit) ? '&unit=' . urlencode($curUnit) : '') . ($selectedGuruId && $isAdmin ? '&guru_id=' . $selectedGuruId : '')) ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $curStatus === 'nonaktif' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span class="w-2 h-2 rounded-full <?= $curStatus === 'nonaktif' ? 'bg-white' : 'bg-slate-400' ?>"></span>
                <span>Nonaktif (<?= $totalInactive ?? 0 ?>)</span>
            </a>
        </div>

        <!-- Filter Guru (Admin) & Filter Unit -->
        <form method="GET" action="<?= url('kelola-absen-siswa/mapel') ?>" class="flex items-center gap-2 flex-wrap">
            <?php if (!empty($curStatus)): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($curStatus) ?>">
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <label class="text-xs font-bold text-slate-400 uppercase">Guru:</label>
                <select name="guru_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 font-semibold text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Guru</option>
                    <?php foreach ($guruList as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= $selectedGuruId == $g['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nama']) ?><?= !empty($g['gelar']) ? ', ' . htmlspecialchars($g['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>

            <label class="text-xs font-bold text-slate-400 uppercase">Unit:</label>
            <select name="unit" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 font-semibold text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <option value="">Semua Unit</option>
                <?php foreach ($unit_list as $uk => $uv): ?>
                    <option value="<?= $uk ?>" <?= $curUnit === $uk ? 'selected' : '' ?>>Unit <?= $uk ?></option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($curUnit) || !empty($curStatus)): ?>
                <a href="<?= url('kelola-absen-siswa/mapel' . ($selectedGuruId && $isAdmin ? '?guru_id=' . $selectedGuruId : '')) ?>" title="Reset Filter" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Data Table: Wadah Grup Absen Mapel -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-slate-800">Wadah Grup Absen Mapel</h3>
                <p class="text-xs text-slate-400">Pilih wadah presensi aktif di bawah ini untuk mencatat presensi kehadiran siswa</p>
            </div>
            <a href="<?= url('kelola-absen-siswa/group/create?tipe=mapel' . ($selectedGuruId ? '&guru_id=' . $selectedGuruId : '')) ?>" 
               class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                <span>+ Buat Wadah Baru</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Unit</th>
                        <th class="py-3.5 px-4">Judul Wadah Grup Mapel</th>
                        <th class="py-3.5 px-4">Mata Pelajaran &amp; Kelas</th>
                        <th class="py-3.5 px-4">Guru Pengampu</th>
                        <th class="py-3.5 px-4 text-center">Total Sesi</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($groups)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-3 text-2xl">
                                    📁
                                </div>
                                <p class="font-bold text-slate-700 text-sm">Belum Ada Wadah Grup Absen Mapel</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                                    Silakan buat wadah grup presensi terlebih dahulu untuk menentukan mata pelajaran, kelas, dan guru pengampu sebelum mencatat absen.
                                </p>
                                <a href="<?= url('kelola-absen-siswa/group/create?tipe=mapel' . ($selectedGuruId ? '&guru_id=' . $selectedGuruId : '')) ?>" 
                                   class="inline-flex items-center gap-1.5 px-5 py-2.5 mt-4 bg-emerald-600 text-white font-bold text-xs rounded-2xl hover:bg-emerald-700 shadow-md shadow-emerald-500/20 transition-all">
                                    + Buat Wadah Absen Mapel Sekarang
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groups as $idx => $g):
                            $rowUnit = $g['unit'] ?? 'SD';
                            $uBadge = $unit_list[$rowUnit]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-300';
                            $uIcon = $unit_list[$rowUnit]['icon'] ?? '🏫';
                            $totalSesi = (int)($g['total_sesi'] ?? 0);
                            $isActive = !empty($g['is_active']);
                            $cleanKelas = preg_replace('/^Kelas\s+/i', '', $g['kelas'] ?? '');
                            $inputUrl = url('kelola-absen-siswa/input/' . $g['id']);
                        ?>
                            <tr class="transition-colors group <?= $isActive ? 'hover:bg-emerald-50/20' : 'bg-slate-50/50 hover:bg-slate-100/50' ?>">
                                <!-- No -->
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    <?= $idx + 1 ?>
                                </td>

                                <!-- Unit Sekolah -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold border <?= $uBadge ?>">
                                        <span><?= $uIcon ?></span>
                                        <span>Unit <?= htmlspecialchars($rowUnit) ?></span>
                                    </span>
                                </td>

                                <!-- Judul Wadah -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold leading-snug <?= $isActive ? 'text-slate-900' : 'text-slate-600' ?>">
                                        <a href="<?= $inputUrl ?>" class="hover:text-emerald-600 transition-colors text-sm">
                                            <?= htmlspecialchars($g['judul'] ?: 'Wadah Absen Mapel') ?>
                                        </a>
                                        <?php if (!$isActive): ?>
                                            <span class="ml-1 px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-slate-200 text-slate-600">NONAKTIF</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] font-semibold text-slate-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                        <span><?= htmlspecialchars($g['nama_tahun'] ?: '2026/2027') ?></span>
                                        <span>&bull;</span>
                                        <span>Semester <?= htmlspecialchars($g['semester'] ?: 'Ganjil') ?></span>
                                    </div>
                                </td>

                                <!-- Mata Pelajaran & Kelas -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-xs">
                                        <?= htmlspecialchars($g['mata_pelajaran'] ?: '-') ?>
                                    </div>
                                    <div class="text-[11px] text-emerald-700 font-semibold mt-0.5">
                                        Kelas <?= htmlspecialchars($cleanKelas ?: '-') ?>
                                    </div>
                                </td>

                                <!-- Guru Pengampu -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-xs">
                                        <?= htmlspecialchars($g['nama_guru'] ?: 'Belum ditentukan') ?><?= !empty($g['gelar_guru']) ? ', ' . htmlspecialchars($g['gelar_guru']) : '' ?>
                                    </div>
                                </td>

                                <!-- Total Sesi -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $totalSesi > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' ?>">
                                        <?= $totalSesi ?> Pertemuan
                                    </span>
                                </td>

                                <!-- Status (Toggle 1-Click) -->
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button" 
                                            onclick="toggleGroupStatus(<?= $g['id'] ?>, this)"
                                            data-status="<?= $isActive ? '1' : '0' ?>"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border transition-all cursor-pointer <?= $isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-300 hover:bg-slate-200' ?>">
                                        <span class="w-2 h-2 rounded-full <?= $isActive ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                                        <span class="status-label"><?= $isActive ? 'Aktif' : 'Nonaktif' ?></span>
                                    </button>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Tombol Input Absen -->
                                        <a href="<?= $inputUrl ?>" 
                                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm shadow-emerald-500/20 transition-all">
                                            <span>⚡</span>
                                            <span>Input Absen</span>
                                        </a>

                                        <!-- Edit Wadah -->
                                        <a href="<?= url('kelola-absen-siswa/group/edit/' . $g['id']) ?>" 
                                           class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors" 
                                           title="Edit Wadah">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </a>

                                        <!-- Hapus Wadah -->
                                        <form method="POST" action="<?= url('kelola-absen-siswa/group/delete/' . $g['id']) ?>" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus wadah grup absen ini?')">
                                            <button type="submit" 
                                                    class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" 
                                                    title="Hapus Wadah">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function toggleGroupStatus(groupId, btn) {
    const currentStatus = btn.getAttribute('data-status');
    btn.disabled = true;
    btn.style.opacity = '0.5';

    fetch('<?= url('kelola-absen-siswa/group/toggle-status/') ?>' + groupId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.style.opacity = '1';
        if (res.success) {
            const isNowActive = res.is_active === 1;
            btn.setAttribute('data-status', isNowActive ? '1' : '0');
            const dot = btn.querySelector('.rounded-full');
            const label = btn.querySelector('.status-label');

            if (isNowActive) {
                btn.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border transition-all cursor-pointer bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100';
                dot.className = 'w-2 h-2 rounded-full bg-emerald-500';
                label.textContent = 'Aktif';
            } else {
                btn.className = 'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border transition-all cursor-pointer bg-slate-100 text-slate-500 border-slate-300 hover:bg-slate-200';
                dot.className = 'w-2 h-2 rounded-full bg-slate-400';
                label.textContent = 'Nonaktif';
            }
        } else {
            alert(res.message || 'Gagal mengubah status');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.style.opacity = '1';
        alert('Terjadi kesalahan jaringan.');
    });
}
</script>
