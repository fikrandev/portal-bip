<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/barang/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="redirect_to" value="<?= url('mobile-sarpras/inventaris') ?>">

            <!-- Unit & Kategori -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unit / Jenjang <span class="text-rose-500">*</span></label>
                    <select name="unit" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 font-medium">
                        <?php foreach (['SD', 'SMP', 'SMA', 'PAUD', 'Yayasan', 'Semua'] as $u): ?>
                            <option value="<?= $u ?>" <?= $u === 'SD' ? 'selected' : '' ?>><?= $u === 'Semua' ? 'Semua / Fasilitas Bersama' : $u ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis / Kategori Sarpras <span class="text-rose-500">*</span></label>
                    <select name="kategori_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 font-medium">
                        <option value="">-- Pilih Jenis / Kategori --</option>
                        <?php foreach ($kategoriList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= e($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Golongan & Kelompok -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Golongan <span class="text-rose-500">*</span></label>
                    <select name="golongan_id" id="selGolongan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($golonganList as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= e($g['kode']) ?> - <?= e($g['nama_golongan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok <span class="text-rose-500">*</span></label>
                    <select name="kelompok_id" id="selKelompok" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($kelompokList as $k): ?>
                            <option value="<?= $k['id'] ?>" data-golongan="<?= $k['golongan_id'] ?>"><?= e($k['kode']) ?> - <?= e($k['nama_kelompok']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_barang" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500" placeholder="Cth: Kursi Kantor">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Merek / Model</label>
                    <input type="text" name="merk_model" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <!-- Detail Angka -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah <span class="text-rose-500">*</span></label>
                    <input type="number" name="jumlah" value="1" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                    <select name="satuan" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <?php foreach ($satuanList as $s): ?>
                            <option value="<?= e($s['nama_satuan']) ?>"><?= e($s['nama_satuan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Satuan (Rp)</label>
                    <input type="text" id="inputHarga" name="harga_perolehan_formatted" value="0" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 font-bold">
                    <input type="hidden" name="harga_perolehan" id="hiddenHarga" value="0">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                    <select name="asal_anggaran_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($asalAnggaranList as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= e($a['nama']) ?></option>
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
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
                </div>

                <div class="pt-2">
                    <?php 
                    $uploaderId = 'foto_inventaris_tambah';
                    $existingFotos = [];
                    $isMobile = true;
                    include BASE_PATH . '/modules/kelola-sarpras/views/barang/_foto_uploader.php';
                    ?>
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

    // Dependent Golongan & Kelompok
    const selGol = document.getElementById('selGolongan');
    const selKel = document.getElementById('selKelompok');
    if (selGol && selKel) {
        const masterOptions = Array.from(selKel.options)
            .filter(opt => opt.value !== '')
            .map(opt => ({
                value: opt.value,
                text: opt.textContent,
                golonganId: opt.getAttribute('data-golongan') || ''
            }));

        function applyFilter(selectedKelompokId = null) {
            const golId = selGol.value;
            const targetId = (selectedKelompokId !== null && selectedKelompokId !== undefined) ? String(selectedKelompokId) : selKel.value;

            selKel.innerHTML = '';

            if (!golId) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- Pilih Golongan Terlebih Dahulu --';
                selKel.appendChild(opt);
                selKel.disabled = true;
                selKel.value = '';
                return;
            }

            const filtered = masterOptions.filter(item => String(item.golonganId) === String(golId));

            if (filtered.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- Belum Ada Kelompok di Golongan Ini --';
                selKel.appendChild(opt);
                selKel.disabled = true;
                selKel.value = '';
            } else {
                selKel.disabled = false;
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '-- Pilih Kelompok --';
                selKel.appendChild(placeholder);

                let hasSelected = false;
                filtered.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.value;
                    opt.dataset.golongan = item.golonganId;
                    opt.textContent = item.text;
                    if (targetId && String(item.value) === targetId) {
                        opt.selected = true;
                        hasSelected = true;
                    }
                    selKel.appendChild(opt);
                });

                if (hasSelected && targetId) {
                    selKel.value = targetId;
                } else {
                    selKel.value = '';
                }
            }
        }

        selGol.addEventListener('change', function() {
            applyFilter('');
        });

        applyFilter(selKel.value || null);
    }
</script>
