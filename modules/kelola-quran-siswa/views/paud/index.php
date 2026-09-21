<?php
/**
 * Dashboard Qur'an PAUD / TK
 * Referensi UI/UX: Modul Kelola Nilai (Clean, Professional, Data-Driven)
 */
?>

<div class="space-y-6">
    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Jenjang PAUD / TK
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Tahsin &amp; Tahfidz
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5 flex items-center gap-2.5">
                <span>Dashboard Qur'an PAUD</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Pusat monitoring target capaian pembelajaran Al-Qur'an, tahsin (Iqro'/Tilawati), dan tahfidz surah pendek usia dini
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Daftar Target</span>
            </a>
            <a href="<?= url('kelola-quran-siswa-paud/rekap') ?>" class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Rekap Nilai</span>
            </a>
            <a href="<?= url('kelola-quran-siswa-paud/target/create') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Buat Grup Target</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Summary Cards (Style Kelola Nilai) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Wadah Grup Target -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-teal-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Wadah Grup Target</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($stats['total_grup_aktif'] ?? count($groups ?? [])) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-emerald-700 font-bold">● Grup Aktif</span>
                <span class="text-slate-400">Tahsin &amp; Tahfidz</span>
            </div>
        </div>

        <!-- Total Nilai Diinput -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-indigo-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Penilaian</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($stats['total_nilai'] ?? 0) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Rekam Capaian Santri</span>
                <span class="text-indigo-600 font-bold">Tersimpan</span>
            </div>
        </div>

        <!-- Santri Terdata -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Santri PAUD Terdata</p>
                    <h3 class="text-3xl font-black text-slate-800 mt-1"><?= number_format($stats['total_siswa'] ?? 0) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">TK A &amp; TK B</span>
                <span class="text-amber-600 font-bold">Aktif Belajar</span>
            </div>
        </div>

        <!-- Capaian Mutqin -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-emerald-300 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Capaian Mutqin</p>
                    <h3 class="text-3xl font-black text-emerald-600 mt-1"><?= number_format($stats['total_mutqin'] ?? 0) ?></h3>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-500">Lulus / Tuntas Target</span>
                <span class="text-emerald-700 font-bold">Mumtaz</span>
            </div>
        </div>
    </div>

    <!-- Section: Wadah Grup Target Aktif (Table Style) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800">
                    Wadah Grup Target Pembelajaran Aktif
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Kelompok capaian tahsin dan tahfidz santri yang siap untuk diinputkan penilaian
                </p>
            </div>
            <a href="<?= url('kelola-quran-siswa-paud/target') ?>" class="text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1">
                <span>Kelola Semua Grup Target</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Nama Wadah Grup Target</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Semester</th>
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
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                </div>
                                <p class="font-bold text-slate-700 text-sm">Belum Ada Grup Target</p>
                                <p class="text-xs text-slate-400 mt-0.5">Buat grup target terlebih dahulu pada menu Pengaturan Target.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groups as $idx => $g): 
                            $rawTarget = json_decode($g['target_materi'] ?? '{}', true) ?: [];
                            if (isset($rawTarget['tahsin']) || isset($rawTarget['tahfidz'])) {
                                $tahsin = $rawTarget['tahsin'] ?? [];
                                $tahfidz = $rawTarget['tahfidz'] ?? [];
                            } else {
                                $isLegTahsin = ($g['kategori'] === 'tahsin');
                                $tahsin = $isLegTahsin ? $rawTarget : [];
                                $tahfidz = !$isLegTahsin ? $rawTarget : [];
                            }
                        ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400"><?= $idx + 1 ?></td>
                                <td class="py-3 px-4">
                                    <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $g['id']) ?>" class="font-bold text-slate-800 hover:text-teal-700 block text-sm transition-colors group-hover:underline">
                                        <?= htmlspecialchars($g['nama_grup']) ?>
                                    </a>
                                    <span class="text-[11px] text-slate-400">Pengampu: <?= htmlspecialchars($g['guru_nama'] ?: 'Guru PAUD') ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        <?= htmlspecialchars($g['kelas']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 font-bold text-[11px] border border-indigo-100">
                                        Semester <?= htmlspecialchars($g['semester'] ?? 'Ganjil') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-700">
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
                                <td class="py-3 px-4 text-slate-700">
                                    <?php if (!empty($tahfidz['surah'])): ?>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                <?= count($tahfidz['surah']) ?> Surah
                                            </span>
                                            <span class="text-[11px] text-slate-600 truncate max-w-[160px]">
                                                <?= implode(', ', array_slice($tahfidz['surah'], 0, 2)) . (count($tahfidz['surah']) > 2 ? '...' : '') ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $g['id']) ?>" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 underline">
                                            + Atur Tahfidz
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="font-bold text-slate-800"><?= (int)($g['total_santri_dinilai'] ?? 0) ?></span>
                                    <span class="text-slate-400"> Santri</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($g['is_active']): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Aktif</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <a href="<?= url('kelola-quran-siswa-paud/target/manage/' . $g['id']) ?>" 
                                           class="px-2.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 font-bold text-xs shadow-sm transition-colors inline-flex items-center gap-1" title="Atur Target Tahsin & Tahfidz">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                            <span>Atur Target</span>
                                        </a>
                                        <a href="<?= url('kelola-quran-siswa-paud/penilaian/' . $g['id']) ?>" 
                                           class="px-2.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition-colors inline-flex items-center gap-1" title="Input Penilaian Santri">
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
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section: Riwayat Penilaian Terkini (Table Style) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800">
                    Riwayat Penilaian Terkini
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Daftar 10 catatan penilaian dan capaian santri PAUD/TK terbaru
                </p>
            </div>
            <a href="<?= url('kelola-quran-siswa-paud/rekap') ?>" class="text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1">
                <span>Lihat Rekap Lengkap</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                </svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">Tanggal</th>
                        <th class="py-3.5 px-4">Santri</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Grup Target &amp; Capaian</th>
                        <th class="py-3.5 px-4 text-center">Rating</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Catatan Guru</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($recentNilai)): ?>
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400">
                                <p class="font-bold text-slate-700 text-sm">Belum Ada Riwayat Penilaian</p>
                                <p class="text-xs text-slate-400 mt-0.5">Penilaian yang diinput akan muncul di tabel ini.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentNilai as $nIdx => $rn): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400"><?= $nIdx + 1 ?></td>
                                <td class="py-3 px-4 text-slate-500 whitespace-nowrap">
                                    <?= date('d/m/Y', strtotime($rn['tanggal_penilaian'])) ?>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-800 block text-sm"><?= htmlspecialchars($rn['nama_lengkap']) ?></span>
                                    <span class="text-[11px] text-slate-400">NIS: <?= htmlspecialchars($rn['nis'] ?: '-') ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                        <?= htmlspecialchars($rn['kelas']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-800 block"><?= htmlspecialchars($rn['nama_grup']) ?></span>
                                    <span class="text-[11px] text-teal-700 font-medium">
                                        <?= htmlspecialchars($rn['materi_capaian'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center gap-0.5 text-amber-500">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <span class="<?= $s <= ($rn['nilai_bintang'] ?? 0) ? 'text-amber-500' : 'text-slate-200' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="block text-[10px] text-slate-400 font-bold"><?= (int)($rn['nilai_bintang'] ?? 0) ?>/5</span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($rn['status_lulus'] === 'Mutqin'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Mutqin
                                        </span>
                                    <?php elseif ($rn['status_lulus'] === 'Lulus'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            Lulus
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            Mengulang
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-xs italic">
                                    <?= htmlspecialchars($rn['catatan_guru'] ?: '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
