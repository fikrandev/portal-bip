<?php
/**
 * Daftar Grup Nilai — Table View
 * Terstruktur seperti Daftar Wadah Grup Perangkat Pembelajaran
 */
$unit_list = [
    'PAUD' => [
        'name' => 'PAUD / TK',
        'badge' => 'bg-pink-50 text-pink-700 border-pink-200',
        'icon' => '🧸'
    ],
    'SD' => [
        'name' => 'SD (Sekolah Dasar)',
        'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'icon' => '🎒'
    ],
    'SMP' => [
        'name' => 'SMP (Sekolah Menengah Pertama)',
        'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
        'icon' => '📚'
    ],
    'SMA' => [
        'name' => 'SMA / SMK',
        'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
        'icon' => '🎓'
    ]
];

$curStatus = $_GET['status'] ?? '';
$curUnit = $_GET['unit'] ?? '';
?>
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Manajemen Wadah Nilai
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">Daftar Grup Nilai</h1>
            <p class="text-xs sm:text-sm text-slate-500">Kelola wadah nilai berdasarkan kelas, mata pelajaran, dan kaitan CP ATP</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-nilai') ?>" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Kembali
            </a>
            <a href="<?= url('kelola-nilai/group/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>+ Buat Grup Nilai Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-400 mr-1 uppercase">Status:</span>
            <a href="<?= url('kelola-nilai/group' . (!empty($curUnit) ? '?unit=' . urlencode($curUnit) : '')) ?>" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= empty($curStatus) ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Semua (<?= $totalAll ?? count($groups) ?>)
            </a>
            <a href="<?= url('kelola-nilai/group?status=aktif' . (!empty($curUnit) ? '&unit=' . urlencode($curUnit) : '')) ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $curStatus === 'aktif' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span class="w-2 h-2 rounded-full <?= $curStatus === 'aktif' ? 'bg-white' : 'bg-emerald-500' ?>"></span>
                <span>Aktif (<?= $totalActive ?? 0 ?>)</span>
            </a>
            <a href="<?= url('kelola-nilai/group?status=nonaktif' . (!empty($curUnit) ? '&unit=' . urlencode($curUnit) : '')) ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $curStatus === 'nonaktif' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span class="w-2 h-2 rounded-full <?= $curStatus === 'nonaktif' ? 'bg-white' : 'bg-slate-400' ?>"></span>
                <span>Nonaktif (<?= $totalInactive ?? 0 ?>)</span>
            </a>
        </div>

        <!-- Unit Filter Dropdown / Form -->
        <form method="GET" action="<?= url('kelola-nilai/group') ?>" class="flex items-center gap-2">
            <?php if (!empty($curStatus)): ?>
                <input type="hidden" name="status" value="<?= e($curStatus) ?>">
            <?php endif; ?>
            <label class="text-xs font-bold text-slate-400 uppercase">Unit:</label>
            <select name="unit" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 font-semibold text-slate-700 focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <option value="">Semua Unit Sekolah</option>
                <?php foreach ($unit_list as $uk => $uv): ?>
                    <option value="<?= $uk ?>" <?= $curUnit === $uk ? 'selected' : '' ?>>Unit <?= $uk ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($curUnit) || !empty($curStatus)): ?>
                <a href="<?= url('kelola-nilai/group') ?>" title="Reset Filter" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Unit Sekolah</th>
                        <th class="py-3.5 px-4">Judul Wadah Grup Nilai</th>
                        <th class="py-3.5 px-4">Tahun Ajaran &amp; Smt</th>
                        <th class="py-3.5 px-4">Kaitan CP &amp; ATP</th>
                        <th class="py-3.5 px-4 text-center">Data Nilai</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($groups)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-14 h-14 rounded-2xl bg-teal-50 flex items-center justify-center mx-auto mb-3 text-2xl">
                                    📁
                                </div>
                                <p class="font-bold text-slate-700 text-sm">Belum Ada Wadah Grup Nilai</p>
                                <p class="text-xs text-slate-400 mt-1">
                                    <?= !empty($curStatus) || !empty($curUnit) ? 'Tidak ditemukan wadah grup nilai dengan filter terpilih.' : 'Silakan buat wadah grup nilai baru untuk memulai penginputan nilai.' ?>
                                </p>
                                <a href="<?= url('kelola-nilai/group/create') ?>" class="inline-flex items-center gap-1.5 px-4 py-2 mt-4 bg-teal-50 text-teal-700 font-bold text-xs rounded-xl hover:bg-teal-100 border border-teal-200 transition-colors">
                                    + Buat Grup Nilai Baru
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groups as $idx => $g):
                            $rowUnit = $g['unit'] ?? 'SD';
                            $uBadge = $unit_list[$rowUnit]['badge'] ?? 'bg-slate-100 text-slate-700 border-slate-300';
                            $uIcon = $unit_list[$rowUnit]['icon'] ?? '🏫';
                            $totalInput = (int)($g['total_input'] ?? 0);
                            $isActive = !empty($g['is_active']);
                        ?>
                            <tr class="transition-colors group <?= $isActive ? 'hover:bg-teal-50/20' : 'bg-slate-50/50 hover:bg-slate-100/50' ?>">
                                <!-- No -->
                                <td class="py-3.5 px-4 text-center font-bold text-slate-400">
                                    <?= $idx + 1 ?>
                                </td>

                                <!-- Unit Sekolah -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-bold border <?= $uBadge ?>">
                                        <span><?= $uIcon ?></span>
                                        <span>Unit <?= e($rowUnit) ?></span>
                                    </span>
                                </td>

                                <!-- Judul Wadah / Grup Nilai -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold leading-snug <?= $isActive ? 'text-slate-900' : 'text-slate-600' ?>">
                                        <a href="<?= url('kelola-nilai/input/' . $g['id']) ?>" class="hover:text-teal-600 transition-colors text-sm">
                                            <?= e($g['judul'] ?: 'Grup Penilaian') ?>
                                        </a>
                                        <?php if (!$isActive): ?>
                                            <span class="ml-1 px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-slate-200 text-slate-600">NONAKTIF</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] font-semibold text-slate-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                        <span>Wadah Penilaian</span>
                                        <span>&bull;</span>
                                        <span class="text-teal-700 font-bold"><?= !empty($g['jenis_penilaian']) ? e($g['jenis_penilaian']) : 'Formatif & Sumatif' ?></span>
                                        <?php if (!empty($g['nama_kelas'])): ?>
                                            <span>&bull;</span>
                                            <span>Kelas <?= e($g['nama_kelas']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($g['mata_pelajaran'])): ?>
                                            <span>&bull;</span>
                                            <span><?= e($g['mata_pelajaran']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Tahun Ajaran & Smt -->
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800"><?= e($g['nama_tahun'] ?: '2026/2027') ?></div>
                                    <div class="mt-0.5">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold <?= ($g['semester'] === 'Ganjil' || $g['semester'] === '1') ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' ?>">
                                            Semester <?= e($g['semester'] ?: '-') ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Kaitan CP & ATP -->
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($g['cpatp_judul'])): ?>
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-bold max-w-[220px]" title="<?= e($g['cpatp_judul']) ?>">
                                            <span>🔗</span>
                                            <span class="truncate"><?= e($g['cpatp_judul']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-[11px] text-slate-400 font-medium italic">Tidak ditautkan</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Total Nilai Diinput -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold <?= $totalInput > 0 ? 'bg-teal-50 text-teal-700 border border-teal-200' : 'bg-slate-100 text-slate-500 border border-slate-200' ?>">
                                        <?= $totalInput ?> Nilai Diinput
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Wadah Aktif</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Buka Wadah -->
                                        <a href="<?= url('kelola-nilai/input/' . $g['id']) ?>" title="Buka Wadah Nilai" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold transition-colors">
                                            <span>Buka</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                        </a>

                                        <!-- Edit Wadah -->
                                        <a href="<?= url('kelola-nilai/group/edit/' . $g['id']) ?>" title="Edit Wadah Nilai" class="p-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>

                                        <!-- Toggle Status Aktif / Nonaktif -->
                                        <form method="POST" action="<?= url("kelola-nilai/group/toggle-status/{$g['id']}") ?>" class="inline" onsubmit="return confirm('<?= $isActive ? 'Nonaktifkan wadah grup nilai ini? Wadah akan ditutup sementara.' : 'Aktifkan kembali wadah grup nilai ini?' ?>');">
                                            <?= CSRF::field() ?>
                                            <?php if ($isActive): ?>
                                                <button type="submit" title="Nonaktifkan Wadah" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" title="Aktifkan Kembali Wadah" class="p-1.5 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-700 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- Hapus Wadah (Soft Delete) -->
                                        <form method="POST" action="<?= url("kelola-nilai/group/delete/{$g['id']}") ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus wadah grup nilai ini? Data nilai di dalamnya tidak akan ditampilkan.');" class="inline">
                                            <?= CSRF::field() ?>
                                            <button type="submit" title="Hapus Wadah Nilai" class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
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
