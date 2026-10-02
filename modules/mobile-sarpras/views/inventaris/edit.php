<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/barang/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">`n            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <?= CSRF::field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras/inventaris') ?>">

            <!-- Golongan & Kelompok -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Golongan <span class="text-rose-500">*</span></label>
                    <select name="golongan_id" id="selGolongan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($golonganList as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= $g['id'] == $item['golongan_id'] ? 'selected' : '' ?> ><?= e($g['kode']) ?> - <?= e($g['nama_golongan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok <span class="text-rose-500">*</span></label>
                    <select name="kelompok_id" id="selKelompok" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($kelompokList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $k['id'] == $item['kelompok_id'] ? 'selected' : '' ?> ><?= e($k['kode']) ?> - <?= e($k['nama_kelompok']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_barang" value="<?= e($item['nama_barang']) ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" placeholder="Cth: Kursi Kantor">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Merek / Model</label>
                    <input type="text" name="merk_model" value="<?= e($item['merk_model']) ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <!-- Detail Angka -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah <span class="text-rose-500">*</span></label>
                    <input type="number" name="jumlah" value="<?= (int)$item['jumlah'] ?>" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                    <select name="satuan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <?php foreach ($satuanList as $s): ?>
                            <option value="<?= e($s['nama_satuan']) ?>" <?= $s['nama_satuan'] == $item['satuan'] ? 'selected' : '' ?> ><?= e($s['nama_satuan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                    <input type="text" id="inputHarga" name="harga_perolehan_formatted" value="<?= number_format($item['harga_perolehan'], 0, ',', '.') ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 font-bold">
                    <input type="hidden" name="harga_perolehan" id="hiddenHarga" value="<?= $item['harga_perolehan'] ?>">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                    <select name="asal_anggaran_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($asalAnggaranList as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $a['id'] == $item['asal_anggaran_id'] ? 'selected' : '' ?> ><?= e($a['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tgl Perolehan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_perolehan" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Masa Manfaat (Tahun)</label>
                    <input type="number" name="masa_manfaat" value="5" min="0" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi</label>
                    <select name="kondisi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan" <?= $item['kondisi'] == 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                        <option value="Rusak Berat" <?= $item['kondisi'] == 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Barang</label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-700">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl py-3 shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Simpan Inventaris
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Format Rupiah
    const inputHarga = document.getElementById('inputHarga');
    const hiddenHarga = document.getElementById('hiddenHarga');
    
    inputHarga.addEventListener('input', function(e) {
        let value = this.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        this.value = rupiah;
        
        // Update hidden input with raw number
        hiddenHarga.value = value.replace(/\./g, '').replace(',', '.');
    });
</script>


