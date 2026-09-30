<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/ruangan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras') ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Gedung / Bangunan <span class="text-rose-500">*</span></label>
                    <select name="bangunan_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                        <option value="">-- Pilih Gedung --</option>
                        <?php foreach ($bangunanList as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= e($b['nama_bangunan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Ruangan <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_ruangan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500" placeholder="Cth: Ruang Kelas 1A">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Ruangan</label>
                    <input type="text" name="kode_ruangan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500 font-mono" placeholder="Kosongkan jika auto">
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kapasitas (Orang)</label>
                    <input type="number" name="kapasitas" min="0" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Luas (m²)</label>
                    <input type="number" name="luas_ruangan" min="0" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Lantai Ke-</label>
                    <input type="number" name="lantai_ke" value="1" min="1" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Fungsi Ruangan</label>
                    <select name="fungsi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                        <option value="Kelas">Ruang Kelas</option>
                        <option value="Kantor">Ruang Kantor</option>
                        <option value="Laboratorium">Laboratorium</option>
                        <option value="Perpustakaan">Perpustakaan</option>
                        <option value="Gudang">Gudang</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Kondisi</label>
                    <input type="text" name="keterangan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl py-3 shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Simpan Ruangan
                </button>
            </div>
        </form>
    </div>
</div>
