<?php /** Create Siswa Form */ ?>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-siswa/store') ?>" method="POST" id="form-create-siswa">
            <?= CSRF::field() ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="nis" class="block text-sm font-semibold text-primary-800 mb-1.5">NIS <span class="text-rose-500">*</span></label>
                    <input type="text" id="nis" name="nis" value="<?= old('nis') ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label for="nama" class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="<?= old('nama') ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-semibold text-primary-800 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                        <option value="L" <?= old('jenis_kelamin') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= old('jenis_kelamin') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label for="kelas" class="block text-sm font-semibold text-primary-800 mb-1.5">Kelas</label>
                    <select id="kelas" name="kelas" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach($kelasList ?? [] as $k): ?>
                            <option value="<?= e($k['nama_kelas']) ?>" <?= old('kelas') === $k['nama_kelas'] ? 'selected' : '' ?>><?= e($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="tempat_lahir" class="block text-sm font-semibold text-primary-800 mb-1.5">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" value="<?= old('tempat_lahir') ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-semibold text-primary-800 mb-1.5">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?= old('tanggal_lahir') ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
            </div>
            <div class="mb-5">
                <label for="alamat" class="block text-sm font-semibold text-primary-800 mb-1.5">Alamat</label>
                <textarea id="alamat" name="alamat" rows="2" class="w-full px-4 py-3 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium resize-y min-h-[100px]"><?= old('alamat') ?></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="telepon" class="block text-sm font-semibold text-primary-800 mb-1.5">Telepon</label>
                    <input type="text" id="telepon" name="telepon" value="<?= old('telepon') ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-primary-800 mb-1.5">Email</label>
                    <input type="email" id="email" name="email" value="<?= old('email') ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-[10px] text-slate-900 placeholder:text-slate-400 outline-none focus:outline-none focus:ring-0 hover:border-primary-500 focus:border-primary-500 transition-colors text-sm font-medium">
                </div>
            </div>
            <div class="mb-6">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent ">
                    <span class="text-sm font-medium text-primary-800">Siswa aktif</span>
                </label>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transform active:scale-[0.98] transition-all duration-200 text-sm tracking-wide">Simpan Siswa</button>
                <a href="<?= url('kelola-siswa') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-full transition-colors text-sm">Batal</a>
            </div>
        </form>
    </div>
</div>
