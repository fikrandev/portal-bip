<?php
/**
 * Dashboard Perangkat Pembelajaran
 */
?>
<div class="space-y-6">

    <!-- Top Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 via-teal-700 to-primary-900 p-6 sm:p-8 text-white shadow-xl shadow-emerald-950/10 border border-emerald-500/30">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-20 top-0 w-32 h-32 bg-emerald-400/20 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/20 backdrop-blur-md text-xs font-semibold text-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Tahun Ajaran <?= e(SYS_TAHUN_AKADEMIK_NAME) ?>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Perangkat Pembelajaran Guru</h1>
                <p class="text-sm text-emerald-100/90 max-w-2xl leading-relaxed">
                    Kelola dan susun perangkat kurikulum terpadu: Kaldik, HES, HEB, Prota, Prosem, dan Modul Ajar / RPP lengkap dengan alur verifikasi & pengesahan resmi.
                </p>
            </div>

            <!-- Quick Action Dropdown / Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?= url('kelola-perangkat-pembelajaran/verifikasi') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white/15 hover:bg-white/25 border border-white/20 text-white text-sm font-semibold transition-all backdrop-blur-sm">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                    Pusat Verifikasi
                    <?php if (!empty($stats['diajukan']) && $stats['diajukan'] > 0): ?>
                        <span class="px-2 py-0.5 rounded-full bg-amber-400 text-amber-950 font-bold text-xs"><?= $stats['diajukan'] ?></span>
                    <?php endif; ?>
                </a>

                <div class="relative group">
                    <button type="button" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white text-emerald-950 hover:bg-emerald-50 text-sm font-bold shadow-lg shadow-black/10 transition-all">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Buat Dokumen
                        <svg class="w-4 h-4 text-slate-500 group-hover:rotate-180 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 hidden group-hover:block z-50 animate-in fade-in slide-in-from-top-2">
                        <a href="<?= url('kelola-perangkat-pembelajaran/kaldik/create') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Kalender Pendidikan (Kaldik)
                        </a>
                        <a href="<?= url('kelola-perangkat-pembelajaran/hes/create') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span> Hari Efektif Sekolah (HES)
                        </a>
                        <a href="<?= url('kelola-perangkat-pembelajaran/heb/create') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-cyan-500"></span> Hari Efektif Belajar (HEB)
                        </a>
                        <a href="<?= url('kelola-perangkat-pembelajaran/prota/create') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Program Tahunan (Prota)
                        </a>
                        <a href="<?= url('kelola-perangkat-pembelajaran/prosem/create') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span> Program Semester (Prosem)
                        </a>
                        <a href="<?= url('kelola-perangkat-pembelajaran/rpp/create') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> RPP / Modul Ajar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Total -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-700 font-bold text-xl flex-shrink-0">
                📁
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Total Dokumen</p>
                <h3 class="text-2xl font-extrabold text-slate-800"><?= $stats['total'] ?></h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Semua jenis perangkat</p>
            </div>
        </div>

        <!-- Menunggu Persetujuan -->
        <div class="bg-white rounded-3xl p-5 border border-amber-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow bg-gradient-to-br from-amber-50/40 to-white">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-xl flex-shrink-0">
                ⏳
            </div>
            <div>
                <p class="text-xs font-medium text-amber-700">Menunggu Verifikasi</p>
                <h3 class="text-2xl font-extrabold text-amber-900"><?= $stats['diajukan'] ?></h3>
                <p class="text-[11px] text-amber-600/80 mt-0.5">Perlu ditinjau</p>
            </div>
        </div>

        <!-- Disetujui -->
        <div class="bg-white rounded-3xl p-5 border border-emerald-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow bg-gradient-to-br from-emerald-50/40 to-white">
            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-xl flex-shrink-0">
                ✅
            </div>
            <div>
                <p class="text-xs font-medium text-emerald-700">Disetujui</p>
                <h3 class="text-2xl font-extrabold text-emerald-900"><?= $stats['disetujui'] ?></h3>
                <p class="text-[11px] text-emerald-600/80 mt-0.5">Sah & terverifikasi</p>
            </div>
        </div>

        <!-- Ditolak / Revisi -->
        <div class="bg-white rounded-3xl p-5 border border-rose-200/80 shadow-sm flex items-center gap-4 hover:shadow-md transition-shadow bg-gradient-to-br from-rose-50/40 to-white">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 flex items-center justify-center text-rose-600 font-bold text-xl flex-shrink-0">
                ⚠️
            </div>
            <div>
                <p class="text-xs font-medium text-rose-700">Perlu Revisi</p>
                <h3 class="text-2xl font-extrabold text-rose-900"><?= $stats['ditolak'] ?></h3>
                <p class="text-[11px] text-rose-600/80 mt-0.5">Catatan perbaikan</p>
            </div>
        </div>
    </div>

    <!-- 4 Unit Breakdown Cards -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Distribusi Unit Satuan Pendidikan
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Ringkasan perangkat pembelajaran per jenjang pendidikan</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <?php foreach ($unit_list as $uKey => $uInfo): ?>
                <?php $countUnit = $stats['by_unit'][$uKey] ?? 0; ?>
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-emerald-300 hover:shadow-md transition-all flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl <?= $uInfo['bg_soft'] ?>">
                            <?= $uInfo['icon'] ?>
                        </div>
                        <span class="text-lg font-black text-slate-800"><?= $countUnit ?></span>
                    </div>
                    <div class="mt-3">
                        <h4 class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></h4>
                        <p class="text-[10px] text-slate-500"><?= e($uInfo['name']) ?></p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                        <a href="<?= url("kelola-perangkat-pembelajaran/kaldik?unit={$uKey}") ?>" class="font-bold text-emerald-600 hover:underline">Kaldik</a>
                        <span class="text-slate-300">•</span>
                        <a href="<?= url("kelola-perangkat-pembelajaran/hes?unit={$uKey}") ?>" class="font-bold text-teal-600 hover:underline">HES</a>
                        <span class="text-slate-300">•</span>
                        <a href="<?= url("kelola-perangkat-pembelajaran/heb?unit={$uKey}") ?>" class="font-bold text-cyan-600 hover:underline">HEB</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 6 Sub-module Cards Grid -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Modul Perangkat Pembelajaran</h2>
                <p class="text-xs text-slate-500">Pilih modul administrasi yang ingin disusun atau ditinjau</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <!-- 1. Kaldik -->
            <div class="group bg-white rounded-3xl p-6 border border-slate-200/80 hover:border-emerald-400 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                            </svg>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                            <?= $stats['by_type']['kaldik'] ?? 0 ?> Dokumen
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">Kalender Pendidikan (Kaldik)</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Susun agenda akademik, jadwal awal semester, STS/PTS, SAS/PAS, libur nasional & kegiatan sekolah per bulan.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="<?= url('kelola-perangkat-pembelajaran/kaldik') ?>" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                        Buka Kaldik →
                    </a>
                    <a href="<?= url('kelola-perangkat-pembelajaran/kaldik/create') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-emerald-100 text-slate-600 hover:text-emerald-800 transition-colors" title="Buat Kaldik Baru">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </a>
                </div>
            </div>

            <!-- 2. HES -->
            <div class="group bg-white rounded-3xl p-6 border border-slate-200/80 hover:border-teal-400 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-teal-500/10 text-teal-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                            <?= $stats['by_type']['hes'] ?? 0 ?> Dokumen
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 group-hover:text-teal-700 transition-colors">Hari Efektif Sekolah (HES)</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Hitung jumlah hari efektif sekolah, hari libur umum, dan kegiatan per bulan untuk Semester Ganjil & Genap.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="<?= url('kelola-perangkat-pembelajaran/hes') ?>" class="text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1">
                        Buka HES →
                    </a>
                    <a href="<?= url('kelola-perangkat-pembelajaran/hes/create') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-teal-100 text-slate-600 hover:text-teal-800 transition-colors" title="Buat HES Baru">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </a>
                </div>
            </div>

            <!-- 3. HEB -->
            <div class="group bg-white rounded-3xl p-6 border border-slate-200/80 hover:border-cyan-400 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                            </svg>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                            <?= $stats['by_type']['heb'] ?? 0 ?> Dokumen
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 group-hover:text-cyan-700 transition-colors">Hari Efektif Belajar (HEB)</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Hitung pekan efektif KBM & total Jam Pelajaran (JP) efektif per mata pelajaran dan tingkat kelas.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="<?= url('kelola-perangkat-pembelajaran/heb') ?>" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center gap-1">
                        Buka HEB →
                    </a>
                    <a href="<?= url('kelola-perangkat-pembelajaran/heb/create') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-cyan-100 text-slate-600 hover:text-cyan-800 transition-colors" title="Buat HEB Baru">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </a>
                </div>
            </div>

            <!-- 4. Prota -->
            <div class="group bg-white rounded-3xl p-6 border border-slate-200/80 hover:border-indigo-400 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                            <?= $stats['by_type']['prota'] ?? 0 ?> Dokumen
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 group-hover:text-indigo-700 transition-colors">Program Tahunan (Prota)</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Petakan Capaian Pembelajaran (CP) / KD dan distribusi alokasi waktu JP untuk Semester 1 dan Semester 2.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="<?= url('kelola-perangkat-pembelajaran/prota') ?>" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                        Buka Prota →
                    </a>
                    <a href="<?= url('kelola-perangkat-pembelajaran/prota/create') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-800 transition-colors" title="Buat Prota Baru">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </a>
                </div>
            </div>

            <!-- 5. Prosem -->
            <div class="group bg-white rounded-3xl p-6 border border-slate-200/80 hover:border-purple-400 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                            </svg>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                            <?= $stats['by_type']['prosem'] ?? 0 ?> Dokumen
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 group-hover:text-purple-700 transition-colors">Program Semester (Prosem)</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Matriks distribusi pokok materi dan asesmen mingguan per bulan dalam satu semester secara terstruktur.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="<?= url('kelola-perangkat-pembelajaran/prosem') ?>" class="text-xs font-bold text-purple-600 hover:text-purple-700 flex items-center gap-1">
                        Buka Prosem →
                    </a>
                    <a href="<?= url('kelola-perangkat-pembelajaran/prosem/create') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-purple-100 text-slate-600 hover:text-purple-800 transition-colors" title="Buat Prosem Baru">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </a>
                </div>
            </div>

            <!-- 6. RPP / Modul Ajar -->
            <div class="group bg-white rounded-3xl p-6 border border-slate-200/80 hover:border-rose-400 hover:shadow-lg transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                            <?= $stats['by_type']['rpp'] ?? 0 ?> Dokumen
                        </span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 group-hover:text-rose-700 transition-colors">RPP / Modul Ajar</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Rencana Pelaksanaan Pembelajaran & Modul Ajar lengkap: Tujuan, Profil Pelajar Pancasila, Skenario, Asesmen & Lampiran.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <a href="<?= url('kelola-perangkat-pembelajaran/rpp') ?>" class="text-xs font-bold text-rose-600 hover:text-rose-700 flex items-center gap-1">
                        Buka RPP →
                    </a>
                    <a href="<?= url('kelola-perangkat-pembelajaran/rpp/create') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-800 transition-colors" title="Buat RPP Baru">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- Filter & Recent Submissions Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-5">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Aktivitas & Pengajuan Terbaru</h3>
                <p class="text-xs text-slate-500">Daftar berkas perangkat pembelajaran yang baru diperbarui</p>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="<?= url('kelola-perangkat-pembelajaran') ?>" class="flex flex-wrap items-center gap-2">
                <select name="ta" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium bg-slate-50 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Tahun Ajaran</option>
                    <?php foreach ($ta_list as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= $filter_ta == $ta['id'] ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                    <?php endforeach; ?>
                </select>

                <select name="semester" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium bg-slate-50 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Semua Semester</option>
                    <option value="Ganjil" <?= $filter_semester === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                    <option value="Genap" <?= $filter_semester === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                </select>
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200/60">
                        <th class="py-3 px-4 rounded-l-xl">Dokumen & Judul</th>
                        <th class="py-3 px-4">Jenis</th>
                        <th class="py-3 px-4">Guru Pengampu</th>
                        <th class="py-3 px-4">Tahun / Semester</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($recent_items)): ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="text-3xl">📭</span>
                                    <p class="text-sm font-semibold text-slate-600">Belum ada dokumen perangkat ajar</p>
                                    <p class="text-xs text-slate-400">Silakan klik "Buat Dokumen" untuk memulai penyusunan perangkat baru.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_items as $item): ?>
                            <?php
                            $tipeBadge = [
                                'kaldik' => ['label' => 'Kaldik', 'class' => 'bg-emerald-100 text-emerald-800'],
                                'hes' => ['label' => 'HES', 'class' => 'bg-teal-100 text-teal-800'],
                                'heb' => ['label' => 'HEB', 'class' => 'bg-cyan-100 text-cyan-800'],
                                'prota' => ['label' => 'Prota', 'class' => 'bg-indigo-100 text-indigo-800'],
                                'prosem' => ['label' => 'Prosem', 'class' => 'bg-purple-100 text-purple-800'],
                                'rpp' => ['label' => 'RPP', 'class' => 'bg-rose-100 text-rose-800']
                            ][$item['tipe']] ?? ['label' => strtoupper($item['tipe']), 'class' => 'bg-slate-100 text-slate-800'];

                            $statusBadge = [
                                'draft' => ['label' => 'Draft', 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                'diajukan' => ['label' => 'Menunggu Review', 'class' => 'bg-amber-100 text-amber-800 border-amber-300'],
                                'disetujui' => ['label' => 'Disetujui', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300'],
                                'ditolak' => ['label' => 'Perlu Revisi', 'class' => 'bg-rose-100 text-rose-800 border-rose-300']
                            ][$item['status']] ?? ['label' => ucfirst($item['status']), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];
                            ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800"><?= e($item['judul']) ?></div>
                                    <?php if (!empty($item['mata_pelajaran'])): ?>
                                        <div class="text-[11px] text-slate-500"><?= e($item['mata_pelajaran']) ?> <?= !empty($item['tingkat_kelas']) ? '• ' . e($item['tingkat_kelas']) : '' ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase <?= $tipeBadge['class'] ?>">
                                        <?= $tipeBadge['label'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-700"><?= e($item['guru_nama']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= date('d M Y H:i', strtotime($item['updated_at'])) ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-700"><?= e($item['nama_tahun'] ?? 'Tahun Aktif') ?></div>
                                    <div class="text-[10px] text-slate-400">Semester <?= e($item['semester']) ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border <?= $statusBadge['class'] ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $item['status'] === 'disetujui' ? 'bg-emerald-500' : ($item['status'] === 'diajukan' ? 'bg-amber-500' : ($item['status'] === 'ditolak' ? 'bg-rose-500' : 'bg-slate-400')) ?>"></span>
                                        <?= $statusBadge['label'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?= url("kelola-perangkat-pembelajaran/{$item['tipe']}/detail/{$item['id']}") ?>" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                        </a>
                                        <a href="<?= url("kelola-perangkat-pembelajaran/{$item['tipe']}/edit/{$item['id']}") ?>" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors" title="Edit Dokumen">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </a>
                                        <a href="<?= url("kelola-perangkat-pembelajaran/{$item['tipe']}/cetak/{$item['id']}") ?>" target="_blank" class="p-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700 transition-colors" title="Cetak Dokumen">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.75A2.25 2.25 0 0 0 16.5 1.5h-9A2.25 2.25 0 0 0 5.25 3.75v3.536m10.5 0A22.5 22.5 0 0 0 12 7.5a22.5 22.5 0 0 0-3.75-.214" /></svg>
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

</div>
