<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Guru Pengampu
                </span>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Unit <?= e($group['unit']) ?>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5"><?= e($group['judul']) ?></h1>
            <p class="text-xs sm:text-sm text-slate-500">
                <?= e($group['nama_tahun'] ?? '') ?> &bull; Semester <?= e($group['semester']) ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-nilai/group') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <?php if (empty($group['is_active'])): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <div class="text-xs text-amber-800">
                    <span class="font-bold">Wadah Nilai Berstatus Nonaktif:</span> Wadah grup nilai ini saat ini sedang dinonaktifkan sementara.
                </div>
            </div>
            <a href="<?= url('kelola-nilai/group/edit/' . $group['id']) ?>" class="px-3 py-1.5 rounded-xl bg-amber-200 hover:bg-amber-300 text-amber-900 font-bold text-xs transition-colors shrink-0">
                Ubah Status
            </a>
        </div>
    <?php endif; ?>

    <!-- Info Card -->
    <div class="bg-gradient-to-r from-teal-50 to-indigo-50 border border-teal-200/60 rounded-2xl p-5 flex items-start gap-4">
        <div class="w-12 h-12 rounded-2xl bg-teal-100 flex items-center justify-center text-2xl shrink-0">📋</div>
        <div class="min-w-0">
            <h3 class="text-sm font-bold text-teal-900 mb-1">Pilih Guru Pengampu</h3>
            <p class="text-[11px] text-teal-700 leading-relaxed">
                Klik salah satu guru di bawah untuk melihat mata pelajaran dan kelas yang diampu, kemudian input nilai per elemen CP.
            </p>
        </div>
    </div>

    <!-- Guru List -->
    <?php if (empty($guruMap)): ?>
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-12 text-center">
            <div class="w-20 h-20 mx-auto bg-slate-100 rounded-full flex items-center justify-center text-4xl mb-4">👨‍🏫</div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Belum Ada Guru Terkait</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">
                Belum ada dokumen CP & ATP yang terdaftar di grup ini. Pastikan dokumen CP & ATP sudah dibuat di modul Perangkat Pembelajaran dengan unit dan tahun ajaran yang sesuai.
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($guruMap as $gId => $guru): ?>
                <a href="<?= url("kelola-nilai/input/{$groupId}/guru/{$gId}") ?>" 
                   class="group bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:border-teal-300 hover:shadow-lg transition-all p-6 flex flex-col">
                    
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-400 to-indigo-500 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-teal-500/20 shrink-0 group-hover:scale-105 transition-transform">
                            <?= strtoupper(mb_substr($guru['guru_nama'], 0, 1)) ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-slate-800 text-sm leading-snug group-hover:text-teal-700 transition-colors truncate">
                                <?= e($guru['guru_nama']) ?>
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Guru Pengampu</p>
                        </div>
                        <div class="text-slate-300 group-hover:text-teal-500 transition-colors shrink-0 mt-1">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mt-auto pt-3 border-t border-slate-100">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-50 text-[11px] font-bold text-teal-700 border border-teal-200/60">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                            <?= $guru['total_mapel'] ?> Mapel
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-[11px] font-bold text-indigo-700 border border-indigo-200/60">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                            <?= $guru['total_kelas'] ?> Kelas
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-[11px] font-bold text-amber-700 border border-amber-200/60">
                            📄 <?= $guru['doc_count'] ?> Dok
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
