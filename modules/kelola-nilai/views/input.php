<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Input Nilai Siswa
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5"><?= e($group['judul']) ?></h1>
            <p class="text-xs sm:text-sm text-slate-500">
                <?= e($group['mata_pelajaran']) ?> &bull; Kelas <?= e($group['nama_kelas']) ?> &bull; <?= e($group['jenis_penilaian']) ?>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-nilai/group') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                <span>Kembali</span>
            </a>
            <button form="form-input-nilai" type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-teal-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Semua Nilai</span>
            </button>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-4 flex items-start gap-4">
        <div class="text-2xl mt-1">📝</div>
        <div>
            <h3 class="text-sm font-bold text-teal-900 mb-1"><?= e($group['judul']) ?></h3>
            <div class="text-[11px] text-teal-700 space-y-1">
                <p>Tahun Ajaran: <strong><?= e($group['nama_tahun']) ?> (<?= e($group['semester']) ?>)</strong></p>
                <p>Unit: <strong><?= e($group['unit']) ?></strong></p>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= url('kelola-nilai/store/' . $group['id']) ?>" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-6">
        <?= CSRF::field() ?>
        
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-700">
                    <tr>
                        <th class="px-4 py-3 font-semibold w-12 text-center">No</th>
                        <th class="px-4 py-3 font-semibold w-24">NIS</th>
                        <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                        <th class="px-4 py-3 font-semibold w-32">Nilai (Angka)</th>
                        <th class="px-4 py-3 font-semibold">Catatan / Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($siswa)): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 text-xs">
                                Belum ada data siswa di kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($siswa as $idx => $s): 
                            $nilai = $mapNilai[$s['id']]['nilai'] ?? '';
                            $catatan = $mapNilai[$s['id']]['catatan'] ?? '';
                        ?>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3 text-center text-xs font-medium text-slate-500"><?= $idx + 1 ?></td>
                                <td class="px-4 py-3 font-medium text-xs text-slate-700"><?= e($s['nis']) ?></td>
                                <td class="px-4 py-3 font-semibold text-sm text-slate-800"><?= e($s['nama']) ?></td>
                                <td class="px-4 py-3">
                                    <input type="number" step="0.01" min="0" max="100" name="nilai[<?= $s['id'] ?>]" value="<?= e($nilai) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" placeholder="0-100">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" name="catatan[<?= $s['id'] ?>]" value="<?= e($catatan) ?>" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none" placeholder="Opsional...">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($siswa)): ?>
            <div class="flex items-center justify-end">
                <button type="submit" class="px-8 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Nilai
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>
