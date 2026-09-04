<?php
/**
 * Edit Grup Jadwal - View
 */
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Action Bar -->
    <div class="flex items-center justify-between">
        <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            <span>Kembali ke Daftar Grup Jadwal</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="text-2xl">✏️</span> Edit Informasi Grup Jadwal
            </h1>
            <p class="text-xs text-slate-500 mt-1">Perbarui nama grup, tahun ajaran, semester, atau status keaktifan jadwal.</p>
        </div>

        <form action="<?= url('kelola-perangkat-pembelajaran/jadwal/update/' . $grup['id']) ?>" method="POST" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Nama Grup Jadwal -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Versi / Grup Jadwal <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_grup" value="<?= e($grup['nama_grup']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
            </div>

            <!-- Grid: Tahun Ajaran, Semester, Unit -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="tahun_ajaran" value="<?= e($grup['tahun_ajaran']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                        <option value="Ganjil" <?= $grup['semester'] === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                        <option value="Genap" <?= $grup['semester'] === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Unit <span class="text-rose-500">*</span></label>
                    <select name="jenjang" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                        <option value="PAUD" <?= $grup['jenjang'] === 'PAUD' ? 'selected' : '' ?>>PAUD</option>
                        <option value="SD" <?= $grup['jenjang'] === 'SD' ? 'selected' : '' ?>>SD</option>
                        <option value="SMP" <?= $grup['jenjang'] === 'SMP' ? 'selected' : '' ?>>SMP</option>
                        <option value="SMA" <?= $grup['jenjang'] === 'SMA' ? 'selected' : '' ?>>SMA</option>
                        <option value="SEMUA" <?= $grup['jenjang'] === 'SEMUA' ? 'selected' : '' ?>>Semua Unit</option>
                    </select>
                </div>
            </div>

            <!-- Hubungkan dengan Grup Penugasan SK Guru -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Sumber Data SK Penugasan Guru</label>
                <select name="penugasan_grup_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors">
                    <option value="">-- Gunakan Semua Data Penugasan Aktif --</option>
                    <?php foreach ($penugasanGrupList as $pg): ?>
                        <option value="<?= $pg['id'] ?>" <?= ($grup['penugasan_grup_id'] == $pg['id']) ? 'selected' : '' ?>><?= e($pg['nama_grup']) ?> (SK: <?= e($pg['no_sk'] ?: '-') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Toggle Aktifkan -->
            <div class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <input type="checkbox" name="is_active" id="is_active" value="1" <?= !empty($grup['is_active']) ? 'checked' : '' ?> class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                <label for="is_active" class="text-xs text-slate-700 font-semibold cursor-pointer">
                    Jadikan grup jadwal ini sebagai <strong>Jadwal Resmi Aktif</strong> untuk seluruh sistem sekolah.
                </label>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tambahan</label>
                <textarea name="keterangan" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-indigo-500 transition-colors"><?= e($grup['keterangan']) ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('kelola-perangkat-pembelajaran/jadwal') ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-sm shadow-indigo-600/30 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
