<?php /** Edit Kelas Form */ ?>
<div class="max-w-xl">
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-kelas/update/' . $kelas['id']) ?>" method="POST" id="form-edit-kelas">
            <?= CSRF::field() ?>
            
            <div class="mb-5">
                <label for="tahun_akademik_id" class="block text-sm font-semibold text-primary-800 mb-1.5">Tahun Ajaran <span class="text-rose-500">*</span></label>
                <select id="tahun_akademik_id" name="tahun_akademik_id" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                    <option value="">-- Pilih Tahun Ajaran --</option>
                    <?php foreach ($tahunAkademikList as $ta): ?>
                        <option value="<?= $ta['id'] ?>" <?= ($ta['id'] == $kelas['tahun_akademik_id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?> <?= $ta['is_active'] ? '(Aktif)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-5">
                <label for="nama_kelas" class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Kelas <span class="text-rose-500">*</span></label>
                <input type="text" id="nama_kelas" name="nama_kelas" value="<?= e($kelas['nama_kelas']) ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
            </div>
            <div class="mb-5">
                <label for="wali_kelas" class="block text-sm font-semibold text-primary-800 mb-1.5">Wali Kelas</label>
                <input type="text" id="wali_kelas" name="wali_kelas" value="<?= e($kelas['wali_kelas'] ?? '') ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
            </div>
            <div class="mb-6">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" <?= $kelas['is_active'] ? 'checked' : '' ?> class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent ">
                    <span class="text-sm font-medium text-primary-800">Kelas aktif</span>
                </label>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transform active:scale-[0.98] transition-all duration-200 text-sm tracking-wide">Perbarui Kelas</button>
                <a href="<?= url('kelola-kelas') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-full transition-colors text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
