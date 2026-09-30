<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/tanah/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras') ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lahan / Tanah <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_tanah" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500" placeholder="Cth: Lahan Lapangan Basket">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat / Lokasi <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500" rows="3"></textarea>
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Luas (m²) <span class="text-rose-500">*</span></label>
                    <input type="number" name="luas" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Pengadaan</label>
                    <input type="number" name="tahun_pengadaan" value="<?= date('Y') ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Aset (Rp)</label>
                    <input type="text" id="inputHargaTanah" name="nilai_aset_formatted" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500 font-bold">
                    <input type="hidden" name="nilai_aset" id="hiddenHargaTanah" value="0">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                    <select name="asal_anggaran_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($asalAnggaranList as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= e($a['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No Sertifikat</label>
                    <input type="text" name="no_sertifikat" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Status Sertifikat</label>
                    <input type="text" name="keterangan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl py-3 shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Simpan Tanah
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Format Rupiah
    const inputHargaTnh = document.getElementById('inputHargaTanah');
    const hiddenHargaTnh = document.getElementById('hiddenHargaTanah');
    
    inputHargaTnh.addEventListener('input', function(e) {
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
        hiddenHargaTnh.value = value.replace(/\./g, '').replace(',', '.');
    });
</script>
