<?php
/**
 * Qur'an PAUD - Daftar Pengaturan Target (Grup / Wadah Target)
 * Referensi UI/UX: Modul Kelola Nilai (group_list.php)
 */
$filterStatus = $_GET['status'] ?? '';
$filterKelas = $_GET['kelas'] ?? '';
?>

<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Manajemen Grup Target
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Qur'an PAUD / TK
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">
                Pengaturan Target Qur'an PAUD
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Wadah grup target pembelajaran santri yang memuat target <strong>Tahsin</strong> (Iqro') dan <strong>Tahfidz</strong> (Surah Pendek) per semester
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-quran-siswa-paud') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                Kembali ke Dashboard
            </a>
            <a href="<?= url('kelola-quran-siswa-paud/target/create') ?>" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Buat Grup Target Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-4 sm:p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold text-slate-400 mr-1 uppercase">Status:</span>
            
            <a href="<?= url('kelola-quran-siswa-paud/target') ?>" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $filterStatus === '' ? 'bg-teal-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Semua (<?= $countAll ?? count($groups) ?>)
            </a>

            <a href="<?= url('kelola-quran-siswa-paud/target?status=1') ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $filterStatus === '1' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span class="w-2 h-2 rounded-full <?= $filterStatus === '1' ? 'bg-white' : 'bg-emerald-500' ?>"></span>
                <span>Aktif (<?= $countActive ?? 0 ?>)</span>
            </a>

            <a href="<?= url('kelola-quran-siswa-paud/target?status=0') ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all <?= $filterStatus === '0' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <span class="w-2 h-2 rounded-full <?= $filterStatus === '0' ? 'bg-white' : 'bg-slate-400' ?>"></span>
                <span>Nonaktif (<?= $countInactive ?? 0 ?>)</span>
            </a>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Group Target</th>
                        <th class="py-3.5 px-4">Jenjang</th>
                        <th class="py-3.5 px-4">Target Tahsin</th>
                        <th class="py-3.5 px-4">Target Tahfidz</th>
                        <th class="py-3.5 px-4 text-center">Santri Dinilai</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($groups)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-slate-700 text-sm">Belum Ada Group Target</p>
                                <p class="text-xs text-slate-400 mt-1">Silakan klik tombol "+ Buat Grup Target Baru" di atas.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groups as $idx => $g): 
                            $rawTarget = json_decode($g['target_materi'] ?? '{}', true) ?: [];
                            
                            // Support new dual structure and legacy single structure
                            if (isset($rawTarget['tahsin']) || isset($rawTarget['tahfidz'])) {
                                $tahsin = $rawTarget['tahsin'] ?? [];
                                $tahfidz = $rawTarget['tahfidz'] ?? [];
                            } else {
                                // Legacy
                                $isLegTahsin = ($g['kategori'] === 'tahsin');
                                $tahsin = $isLegTahsin ? $rawTarget : [];
                                $tahfidz = !$isLegTahsin ? $rawTarget : [];
                            }
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400"><?= $idx + 1 ?></td>
                                <td class="py-3.5 px-4">
                                    <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $g['id']) ?>" class="font-bold text-slate-800 hover:text-teal-700 block text-sm transition-colors group-hover:underline">
                                        <?= htmlspecialchars($g['nama_grup']) ?>
                                    </a>
                                    <span class="text-[11px] text-slate-400">Tahun Ajaran <?= htmlspecialchars($g['semester'] ?? 'Ganjil') ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-[11px] border border-indigo-100">
                                        PAUD / TK
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <?php if (!empty($tahsin['jilid'])): ?>
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                <?= htmlspecialchars($tahsin['metode'] ?? "Iqro'") ?> Jilid <?= $tahsin['jilid'] ?>
                                            </span>
                                            <span class="text-[11px] text-slate-500">Hal. <?= $tahsin['halaman_awal'] ?? 1 ?>–<?= $tahsin['halaman_target'] ?? $tahsin['halaman_akhir'] ?? 30 ?></span>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $g['id']) ?>" class="text-[11px] font-semibold text-amber-600 hover:text-amber-700 underline">
                                            + Atur Tahsin
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700">
                                    <?php if (!empty($tahfidz['surah'])): ?>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <?= count($tahfidz['surah']) ?> Surah
                                            </span>
                                            <span class="text-[11px] text-slate-600 truncate max-w-[180px]">
                                                <?= implode(', ', array_slice($tahfidz['surah'], 0, 2)) . (count($tahfidz['surah']) > 2 ? '...' : '') ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $g['id']) ?>" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 underline">
                                            + Atur Tahfidz
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="font-bold text-slate-800"><?= (int)($g['total_santri_dinilai'] ?? 0) ?></span>
                                    <span class="text-slate-400"> Santri</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($g['is_active']): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Aktif</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $g['id']) ?>" 
                                           class="px-2.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 font-bold text-xs shadow-sm transition-colors inline-flex items-center gap-1" title="Atur Target Tahsin & Tahfidz">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            <span>Atur Target</span>
                                        </a>
                                        <a href="<?= url('kelola-quran-siswa-paud/penilaian/' . $g['id']) ?>" 
                                           class="px-2.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition-colors inline-flex items-center gap-1" title="Input Penilaian">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                            <span>Nilai</span>
                                        </a>
                                        <a href="<?= url('kelola-quran-siswa-paud/target/edit/' . $g['id']) ?>" 
                                           class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Edit Info Grup">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="<?= url('kelola-quran-siswa-paud/target/delete/' . $g['id']) ?>" onsubmit="return confirm('Yakin ingin menghapus grup target ini?');" class="inline">
                                            <?= class_exists('CSRF') ? CSRF::field() : '' ?>
                                            <button type="submit" class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors" title="Hapus Grup">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
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
