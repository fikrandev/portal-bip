<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/bangunan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">`n            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_to" value="<?= url('mobile-sarpras/bangunan') ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Berada di Lahan <span class="text-rose-500">*</span></label>
                    <select name="tanah_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                        <option value="">-- Pilih Lahan --</option>
                        <?php foreach ($tanahList as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $t['id'] == $item['tanah_id'] ? 'selected' : '' ?> ><?= e($t['nama_tanah']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Gedung / Bangunan <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_bangunan" value="<?= e($item['nama_bangunan']) ?>" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500" placeholder="Cth: Gedung Utama">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Lantai</label>
                        <input type="number" name="jumlah_lantai" min="1" value="<?= (int)$item['jumlah_lantai'] ?>" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Bangunan</label>
                        <select name="kondisi_bangunan" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                            <option value="Baik" <?= $item['kondisi_bangunan'] == 'Baik' ? 'selected' : '' ?>>Baik (Sangat Layak)</option>
                            <option value="Rusak Ringan" <?= $item['kondisi_bangunan'] == 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                            <option value="Rusak Berat" <?= $item['kondisi_bangunan'] == 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <div class="space-y-3 p-3 bg-amber-50/50 rounded-xl border border-amber-100">
                <p class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-2">Dimensi & Luas</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Panjang (m)</label>
                        <input type="number" step="0.01" id="bgn_panjang" name="panjang" value="<?= floatval($item['panjang']) ?>" placeholder="0.00" oninput="calculateLuasBgn()" class="w-full px-3 py-2.5 bg-white border border-amber-200 rounded-xl text-sm font-bold outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lebar (m)</label>
                        <input type="number" step="0.01" id="bgn_lebar" name="lebar" value="<?= floatval($item['lebar']) ?>" placeholder="0.00" oninput="calculateLuasBgn()" class="w-full px-3 py-2.5 bg-white border border-amber-200 rounded-xl text-sm font-bold outline-none focus:border-amber-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-amber-900 mb-1">Luas Total (m²) <span class="text-amber-500 font-normal">(Otomatis)</span></label>
                    <input type="number" step="0.01" id="bgn_luas" name="luas_bangunan" value="<?= floatval($item['luas_bangunan']) ?>" placeholder="0.00" class="w-full px-3 py-2.5 bg-amber-100 border border-amber-300 rounded-xl text-sm font-black text-amber-900 outline-none">
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Dibangun</label>
                        <input type="number" name="tahun_dibangun" value="<?= date('Y') ?>" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Masa Manfaat</label>
                        <input type="number" name="masa_manfaat" min="1" value="20" placeholder="Tahun" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sumber Dana</label>
                        <select name="sumber_dana" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                            <option value="Yayasan" selected>Yayasan</option>
                            <option value="BOS">Dana BOS</option>
                            <option value="Pemerintah / DAK">Pemerintah / DAK</option>
                            <option value="Hibah / CSR">Hibah / CSR</option>
                            <option value="Komite">Komite Sekolah</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga (Rp)</label>
                        <input type="text" name="biaya_pembangunan" value="<?= number_format($item['biaya_pembangunan'], 0, ',', '.') ?>" placeholder="0" onkeyup="formatRupiahInput(this)" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500 font-bold">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Bangunan (Opsional)</label>
                    <input type="file" name="foto_bangunan" accept="image/*" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl py-3 shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Update Bangunan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function calculateLuasBgn() {
        const p = parseFloat(document.getElementById('bgn_panjang').value) || 0;
        const l = parseFloat(document.getElementById('bgn_lebar').value) || 0;
        document.getElementById('bgn_luas').value = (p * l).toFixed(2);
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



