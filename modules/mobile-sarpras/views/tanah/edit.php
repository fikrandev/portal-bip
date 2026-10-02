<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/tanah/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">`n            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <?= CSRF::field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras/tanah') ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lahan / Tanah <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_tanah" value="<?= e($item['nama_tanah']) ?>" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500" placeholder="Cth: Lahan Kampus BIP">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Sertifikat Resmi</label>
                    <input type="text" name="no_sertifikat" value="<?= e($item['no_sertifikat']) ?>" placeholder="Cth: SHM No. 00412" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Kepemilikan</label>
                    <select name="status_kepemilikan" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500">
                        <option value="SHM" <?= $item['status_kepemilikan'] == 'SHM' ? 'selected' : '' ?>>SHM (Sertifikat Hak Milik)</option>
                        <option value="HGB" <?= $item['status_kepemilikan'] == 'HGB' ? 'selected' : '' ?>>HGB (Hak Guna Bangunan)</option>
                        <option value="Hak Pakai" <?= $item['status_kepemilikan'] == 'Hak Pakai' ? 'selected' : '' ?>>Hak Pakai</option>
                        <option value="Wakaf" <?= $item['status_kepemilikan'] == 'Wakaf' ? 'selected' : '' ?>>Tanah Wakaf</option>
                        <option value="Hibah" <?= $item['status_kepemilikan'] == 'Hibah' ? 'selected' : '' ?>>Tanah Hibah</option>
                        <option value="Lainnya" <?= $item['status_kepemilikan'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <div class="space-y-3 p-3 bg-blue-50/50 rounded-xl border border-blue-100">
                <p class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-2">Dimensi & Luas</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Panjang (m)</label>
                        <input type="number" step="0.01" id="tambah_panjang" name="panjang" value="<?= floatval($item['panjang']) ?>" placeholder="0.00" oninput="calculateLuas()" class="w-full px-3 py-2.5 bg-white border border-blue-200 rounded-xl text-sm font-bold outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lebar (m)</label>
                        <input type="number" step="0.01" id="tambah_lebar" name="lebar" value="<?= floatval($item['lebar']) ?>" placeholder="0.00" oninput="calculateLuas()" class="w-full px-3 py-2.5 bg-white border border-blue-200 rounded-xl text-sm font-bold outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-blue-900 mb-1">Luas Total (m²) <span class="text-blue-500 font-normal">(Otomatis)</span></label>
                    <input type="number" step="0.01" id="tambah_luas" name="luas" value="<?= floatval($item['luas']) ?>" placeholder="0.00" class="w-full px-3 py-2.5 bg-blue-100 border border-blue-300 rounded-xl text-sm font-black text-blue-900 outline-none">
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Perolehan</label>
                        <input type="number" name="tahun_perolehan" value="<?= e($item['tahun_perolehan']) ?>" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga (Rp)</label>
                        <input type="text" name="harga_perolehan" value="<?= number_format($item['harga_perolehan'], 0, ',', '.') ?>" placeholder="0" onkeyup="formatRupiahInput(this)" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500 font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat / Lokasi</label>
                    <textarea name="alamat_lokasi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500" rows="3"><?= e($item['alamat_lokasi']) ?></textarea>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto / Sertifikat (Opsional)</label>
                    <input type="file" name="gambar_sertifikat[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan Tambahan</label>
                    <textarea name="keterangan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500" rows="2"><?= e($item['keterangan']) ?></textarea>
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl py-3 shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Update Tanah
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function calculateLuas() {
        const p = parseFloat(document.getElementById('tambah_panjang').value) || 0;
        const l = parseFloat(document.getElementById('tambah_lebar').value) || 0;
        document.getElementById('tambah_luas').value = (p * l).toFixed(2);
    }
    
    function formatRupiahInput(input) {
        let value = input.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        input.value = rupiah;
    }
</script>




