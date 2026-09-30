<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('mobile-sarpras/pengajuan/store') ?>" method="POST" class="space-y-4">
            <?= CSRF::field() ?>
            
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="shopping-cart" class="w-8 h-8 text-emerald-600"></i>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Pengajuan Pembelian</h2>
                <p class="text-xs text-slate-500 mt-1">Ajukan barang atau aset baru</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_barang" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500" placeholder="Contoh: Proyektor Epson">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Spesifikasi Detail</label>
                    <textarea name="spesifikasi" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500" rows="3" placeholder="Contoh: Resolusi 1080p, 3000 lumens"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah <span class="text-rose-500">*</span></label>
                        <input type="number" name="jumlah" value="1" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Total Harga</label>
                        <input type="text" id="inputEstimasi" name="estimasi_harga" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-emerald-500 text-right">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keperluan <span class="text-rose-500">*</span></label>
                    <textarea name="keperluan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500" rows="2" placeholder="Mengapa barang ini dibutuhkan?"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pengaju <span class="text-rose-500">*</span></label>
                    <input type="text" name="pengaju" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-emerald-500">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl py-3 shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i> Ajukan Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Format Rupiah untuk estimasi
    const inputEstimasi = document.getElementById('inputEstimasi');
    inputEstimasi.addEventListener('input', function(e) {
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
    });
</script>
