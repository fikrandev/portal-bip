<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap mb-1.5">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Penilaian 1 Mapel &bull; 1 Semester
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Unit <?= e($group['unit'] ?? '-') ?>
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Semester <?= e($group['semester'] ?? '-') ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-3">
                <span class="w-10 h-10 rounded-2xl bg-gradient-to-br from-teal-400 to-indigo-600 flex items-center justify-center text-white font-black text-base shadow-md shadow-teal-500/20 shrink-0">
                    <?= strtoupper(mb_substr($guruNama ?? 'G', 0, 1)) ?>
                </span>
                <span><?= e($guruNama) ?></span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                <?= e($group['judul'] ?? 'Daftar Grup Nilai') ?> &bull; Tahun Ajaran <?= e($group['nama_tahun'] ?? '-') ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-nilai/input/' . $groupId) ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-gradient-to-r from-teal-50/80 via-indigo-50/50 to-sky-50/60 border border-teal-200/70 rounded-3xl p-5 flex items-start gap-4 shadow-xs">
        <div class="w-11 h-11 rounded-2xl bg-teal-100/80 text-teal-800 flex items-center justify-center text-xl shrink-0 shadow-xs border border-teal-200/50">
            📋
        </div>
        <div class="min-w-0">
            <h3 class="text-sm font-bold text-slate-800 mb-0.5">Lembar Penilaian Semester Kurikulum Merdeka</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Setiap kelas memiliki lembar penilaian semester tersendiri yang memuat seluruh Capaian Pembelajaran (CP &rarr; TP &rarr; ATP), Nilai Akhir Formatif, Sumatif Lingkup Materi, SAS, hingga Nilai Rapor. Klik tombol <strong>Input Nilai</strong> pada kelas yang ingin Anda nilai.
            </p>
        </div>
    </div>

    <!-- Mapel List grouped -->
    <?php if (empty($mapelGroup)): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-12 text-center">
            <div class="w-20 h-20 mx-auto bg-slate-100 rounded-full flex items-center justify-center text-4xl mb-4">📭</div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Tidak Ada Dokumen CP &amp; ATP</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">
                Guru ini belum memiliki dokumen CP &amp; ATP yang aktif untuk unit dan tahun ajaran yang sesuai.
            </p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($mapelGroup as $mapel => $kelasList): ?>
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <!-- Mapel Header -->
                    <div class="px-6 py-4 bg-gradient-to-r from-slate-50 via-slate-50/90 to-teal-50/30 border-b border-slate-200/80 flex items-center justify-between gap-4 flex-wrap">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center text-lg shrink-0 shadow-md shadow-teal-500/20">
                                📖
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-base"><?= e($mapel) ?></h3>
                                <p class="text-xs text-slate-500 font-medium">Mata Pelajaran Semester <?= e($group['semester'] ?? '-') ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold shadow-xs">
                                <?= count($kelasList) ?> Kelas Terdaftar
                            </span>
                        </div>
                    </div>

                    <!-- Kelas Items -->
                    <div class="divide-y divide-slate-100">
                        <?php foreach ($kelasList as $item): ?>
                            <div class="p-5 sm:px-6 sm:py-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors">
                                <div class="flex items-start sm:items-center gap-4 min-w-0">
                                    <!-- Class Badge / Icon -->
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-50 to-teal-50 border border-teal-200/60 text-teal-700 flex items-center justify-center text-2xl shrink-0 shadow-xs">
                                        🏫
                                    </div>
                                    
                                    <!-- Class Details -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                                                <?= e($item['kelas']) ?>
                                            </h4>
                                            <?php if (!empty($item['status'])): ?>
                                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <?= ucfirst(e($item['status'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex items-center gap-2 mt-2 flex-wrap text-xs">
                                            <!-- Fase -->
                                            <?php if (!empty($item['fase'])): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold bg-purple-50 text-purple-700 border border-purple-200/70">
                                                    🏷️ <?= e($item['fase']) ?>
                                                </span>
                                            <?php endif; ?>

                                            <!-- Semester -->
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold bg-blue-50 text-blue-700 border border-blue-200/70">
                                                📅 Semester <?= e($group['semester'] ?? '-') ?>
                                            </span>

                                            <!-- Lingkup Materi (CP) -->
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold bg-amber-50 text-amber-800 border border-amber-200/70">
                                                📊 <?= $item['cp_count'] ?> Lingkup Materi (CP)
                                            </span>

                                            <!-- Total TP -->
                                            <?php if (!empty($item['tp_count'])): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold bg-teal-50 text-teal-700 border border-teal-200/70">
                                                    🎯 <?= $item['tp_count'] ?> TP
                                                </span>
                                            <?php endif; ?>

                                            <!-- Siswa -->
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-semibold bg-slate-100 text-slate-700 border border-slate-200/80">
                                                👥 <?= (int)($item['total_siswa'] ?? 0) ?> Siswa
                                            </span>

                                            <!-- Progress Nilai jika sudah diisi -->
                                            <?php if (!empty($item['nilai_count']) && $item['nilai_count'] > 0): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                                                    ✓ <?= (int)$item['nilai_count'] ?> Siswa Dinilai
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="shrink-0 flex items-center self-end md:self-auto pt-2 md:pt-0">
                                    <a href="<?= url("kelola-nilai/input/{$groupId}/doc/{$item['doc_id']}") ?>" 
                                       class="inline-flex items-center gap-2.5 px-6 py-3 rounded-2xl bg-teal-600 hover:bg-teal-700 active:scale-95 text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-600/25 hover:shadow-lg hover:shadow-teal-600/35 transition-all">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                        <span>Input Nilai</span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
