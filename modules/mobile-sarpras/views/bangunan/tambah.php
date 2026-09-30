<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/bangunan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras') ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Berada di Lahan <span class="text-rose-500">*</span></label>
                    <select name="tanah_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                        <option value="">-- Pilih Lahan --</option>
                        <?php foreach ($tanahList as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['nama_tanah']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Gedung / Bangunan <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_bangunan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500" placeholder="Cth: Gedung Utama">
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Luas Bangunan (m²) <span class="text-rose-500">*</span></label>
                    <input type="number" name="luas_bangunan" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-amber-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Pengadaan</label>
                    <input type="number" name="tahun_pengadaan" value="<?= date('Y') ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nilai Aset (Rp)</label>
                    <input type="text" id="inputHargaBangunan" name="nilai_aset_formatted" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500 font-bold">
                    <input type="hidden" name="nilai_aset" id="hiddenHargaBangunan" value="0">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                    <select name="asal_anggaran_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($asalAnggaranList as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= e($a['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Lantai</label>
                    <input type="number" name="jumlah_lantai" value="1" min="1" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Kondisi</label>
                    <input type="text" name="keterangan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-amber-500" placeholder="Kondisi baik, lantai 2 bocor, dll">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl py-3 shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Simpan Bangunan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Format Rupiah
    const inputHargaBgn = document.getElementById('inputHargaBangunan');
    const hiddenHargaBgn = document.getElementById('hiddenHargaBangunan');
    
    inputHargaBgn.addEventListener('input', function(e) {
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
        hiddenHargaBgn.value = value.replace(/\./g, '').replace(',', '.');
    });
</script>
