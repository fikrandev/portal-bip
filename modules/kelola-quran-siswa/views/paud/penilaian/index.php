<?php
/**
 * Qur'an PAUD - Fitur 3: Input Penilaian
 * Halaman Pemilihan Wadah Grup Target yang Akan Dinilai
 * Referensi UI/UX: Modul Kelola Nilai (Clean & Professional)
 */
?>

<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Input Penilaian Santri
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Qur'an PAUD / TK
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2.5">
                <span>Pilih Wadah Grup Target</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pilih grup target pembelajaran di bawah untuk membuka lembar penilaian santri (tahsin atau tahfidz)
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-quran-siswa-paud') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                Kembali
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

    <!-- Group List Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">
                    Daftar Wadah Grup Target Siap Dinilai
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Klik tombol "Buka Lembar Penilaian" pada baris grup yang ingin dinilai
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Nama Wadah Grup Target</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Target Capaian</th>
                        <th class="py-3.5 px-4 text-center">Progress Penilaian</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($groups)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </div>
                                <p class="font-bold text-slate-700 text-sm">Belum Ada Grup Target Aktif</p>
                                <p class="text-xs text-slate-400 mt-1">Buat grup target pembelajaran terlebih dahulu pada menu Pengaturan Target.</p>
                                <a href="<?= url('kelola-quran-siswa-paud/target/create') ?>" 
                                   class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition-colors">
                                    <span>+ Buat Grup Target Baru</span>
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groups as $idx => $g): 
                            $isTahsin = ($g['kategori'] === 'tahsin');
                            $targetData = json_decode($g['target_materi'] ?? '{}', true) ?: [];
                            $totalSiswa = (int)($g['total_siswa_kelas'] ?? 0);
                            $totalDinilai = (int)($g['total_dinilai'] ?? 0);
                            $pct = $totalSiswa > 0 ? min(100, round(($totalDinilai / $totalSiswa) * 100)) : 0;
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400"><?= $idx + 1 ?></td>
                                <td class="py-3.5 px-4">
                                    <?php if ($isTahsin): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            a. Tahsin (Iqro')
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            b. Tahfidz (Surah)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-800 block text-sm"><?= htmlspecialchars($g['nama_grup']) ?></span>
                                    <span class="text-[11px] text-slate-400">Guru: <?= htmlspecialchars($g['guru_nama'] ?: 'Guru PAUD') ?></span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        <?= htmlspecialchars($g['kelas']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    <?php if ($isTahsin): ?>
                                        <span>Iqro' Jilid <?= $targetData['jilid'] ?? 1 ?> (Hal. <?= $targetData['halaman_awal'] ?? 1 ?>–<?= $targetData['halaman_target'] ?? 30 ?>)</span>
                                    <?php else: ?>
                                        <span><?= !empty($targetData['surah']) ? implode(', ', array_slice($targetData['surah'], 0, 3)) : 'Surah Pendek' ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="max-w-[140px] mx-auto">
                                        <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                            <span class="text-slate-500"><?= $totalDinilai ?>/<?= $totalSiswa ?></span>
                                            <span class="text-slate-800"><?= $pct ?>%</span>
                                        </div>
                                        <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-300 <?= $isTahsin ? 'bg-amber-500' : 'bg-emerald-500' ?>" style="width: <?= $pct ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="<?= url('kelola-quran-siswa-paud/penilaian/' . $g['id']) ?>" 
                                       class="px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition-colors inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                        <span>Buka Lembar Penilaian</span>
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
