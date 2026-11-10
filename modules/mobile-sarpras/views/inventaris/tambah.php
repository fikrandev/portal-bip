<?php
$hasSelectedRuangan = !empty($selectedRuangan) && !empty($selectedRuangan['id']);
$defaultUnit = $hasSelectedRuangan && !empty($selectedRuangan['unit']) ? $selectedRuangan['unit'] : 'SD';
$redirectTarget = $hasSelectedRuangan
    ? url('mobile-sarpras/ruangan/detail/' . $selectedRuangan['id'])
    : url('mobile-sarpras/inventaris');
?>
<div class="px-4 pt-4 pb-24 space-y-4">
    <?php if ($hasSelectedRuangan): ?>
        <!-- Banner Info Ruangan Tujuan (Auto Distribusi) -->
        <div class="bg-emerald-600 text-white rounded-3xl p-4 shadow-md shadow-emerald-600/20 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <a href="<?= url('mobile-sarpras/ruangan/detail/' . $selectedRuangan['id']) ?>" class="w-10 h-10 rounded-2xl bg-white/15 flex items-center justify-center shrink-0 active:scale-95">
                    <i data-lucide="arrow-left" class="w-5 h-5 text-white"></i>
                </a>
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-100 block">Auto Masuk Inventaris &amp; Ruangan</span>
                    <h3 class="text-sm font-bold truncate"><?= e($selectedRuangan['nama_ruangan']) ?></h3>
                    <p class="text-[11px] text-emerald-100 truncate">
                        <?= e($selectedRuangan['nama_bangunan'] ?? $selectedRuangan['lokasi_gedung'] ?? '-') ?> &bull; Lt. <?= (int)($selectedRuangan['lantai'] ?? 1) ?>
                    </p>
                </div>
            </div>
            <div class="w-10 h-10 rounded-2xl bg-white/15 flex items-center justify-center shrink-0">
                <i data-lucide="door-open" class="w-5 h-5 text-white"></i>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('mobile-sarpras/inventaris/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= CSRF::field() ?>
            <input type="hidden" name="redirect_to" value="<?= $redirectTarget ?>">
            <input type="hidden" name="auto_distribusi" value="1">

            <?php if ($hasSelectedRuangan): ?>
                <input type="hidden" name="ruangan_id" value="<?= (int)$selectedRuangan['id'] ?>">
            <?php else: ?>
                <!-- Pilihan Ruangan Opsional jika tambah dari menu Inventaris umum -->
                <div class="bg-emerald-50/70 rounded-2xl p-3.5 border border-emerald-200/80">
                    <label class="block text-xs font-bold text-emerald-900 mb-1">
                        Penempatan Langsung ke Kelas / Ruangan <span class="text-[10px] font-normal text-emerald-700">(Opsional)</span>
                    </label>
                    <select name="ruangan_id" id="selRuanganMobile" class="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl text-xs outline-none focus:border-emerald-500 font-semibold text-slate-800">
                        <option value="">-- Simpan di Stok Gudang / Inventaris Saja --</option>
                        <?php if (!empty($ruanganList)): ?>
                            <?php foreach ($ruanganList as $r): ?>
                                <option value="<?= $r['id'] ?>" data-unit="<?= e($r['unit'] ?? 'SD') ?>">
                                    <?= e($r['nama_ruangan']) ?> (<?= e($r['nama_bangunan'] ?? $r['lokasi_gedung'] ?? '-') ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <p class="text-[10px] text-emerald-700 mt-1">Jika dipilih, barang otomatis masuk Inventaris sekaligus terdistribusi ke ruangan tersebut.</p>
                </div>
            <?php endif; ?>

            <!-- Preview Kode Barang -->
            <div class="bg-blue-50 rounded-2xl p-3.5 border border-blue-100 flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Preview Kode Barang</p>
                    <p class="text-base font-mono font-bold text-blue-950 mt-0.5" id="previewKodeMobile">... . ... . ... . 001</p>
                </div>
                <i data-lucide="qr-code" class="w-6 h-6 text-blue-500"></i>
            </div>

            <!-- Golongan, Kelompok, Asal Anggaran -->
            <div class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Golongan <span class="text-rose-500">*</span></label>
                    <select name="golongan_id" id="selGolongan" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
                        <option value="" data-kode="00">-- Pilih Golongan --</option>
                        <?php foreach ($golonganList as $g): ?>
                            <option value="<?= $g['id'] ?>" data-kode="<?= e($g['kode']) ?>"><?= e($g['kode']) ?> - <?= e($g['nama_golongan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelompok <span class="text-rose-500">*</span></label>
                    <select name="kelompok_id" id="selKelompok" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
                        <option value="" data-kode="00">-- Pilih Kelompok --</option>
                        <?php foreach ($kelompokList as $k): ?>
                            <option value="<?= $k['id'] ?>" data-golongan="<?= $k['golongan_id'] ?>" data-kode="<?= e($k['kode']) ?>"><?= e($k['kode']) ?> - <?= e($k['nama_kelompok']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                    <select name="asal_anggaran_id" id="selAnggaran" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
                        <option value="" data-kode="00">-- Pilih Asal Anggaran --</option>
                        <?php foreach ($asalAnggaranList as $a): ?>
                            <option value="<?= $a['id'] ?>" data-kode="<?= e($a['kode']) ?>"><?= e($a['kode']) ?> - <?= e($a['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Unit & Kategori -->
            <div class="grid grid-cols-1 gap-3.5 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Unit / Jenjang <span class="text-rose-500">*</span></label>
                    <select name="unit" id="selUnitMobile" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500 font-semibold text-slate-800">
                        <?php foreach (['SD', 'SMP', 'SMA', 'PAUD', 'Yayasan', 'Semua'] as $u): ?>
                            <option value="<?= $u ?>" <?= $u === $defaultUnit ? 'selected' : '' ?>><?= $u === 'Semua' ? 'Semua / Fasilitas Bersama' : $u ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis / Kategori Sarpras <span class="text-rose-500">*</span></label>
                    <select name="kategori_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500 font-semibold text-slate-800">
                        <option value="">-- Pilih Jenis / Kategori --</option>
                        <?php foreach ($kategoriList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= e($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr class="border-slate-100 my-2">

            <!-- Identitas Barang -->
            <div class="space-y-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Barang <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_barang" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500" placeholder="Contoh: Meja Siswa / Kursi Guru / AC">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Merk / Model</label>
                        <input type="text" name="merk_model" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500" placeholder="Cth: Olympic">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Seri</label>
                        <input type="text" name="nomor_seri" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono outline-none focus:border-blue-500" placeholder="Opsional">
                    </div>
                </div>
            </div>

            <!-- Detail Angka -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah (Qty) <span class="text-rose-500">*</span></label>
                    <input type="number" name="jumlah" value="1" min="1" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan <span class="text-rose-500">*</span></label>
                    <select name="satuan" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
                        <?php foreach ($satuanList as $s): ?>
                            <option value="<?= e($s['nama_satuan']) ?>"><?= e($s['nama_satuan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Perolehan (Rp)</label>
                    <input type="text" id="inputHarga" name="harga_perolehan_formatted" value="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500 font-bold text-right">
                    <input type="hidden" name="harga_perolehan" id="hiddenHarga" value="0">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tgl Perolehan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_perolehan" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Masa Manfaat (Thn)</label>
                    <input type="number" name="masa_manfaat" value="5" min="0" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi</label>
                    <select name="kondisi" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Posisi di Ruangan</label>
                <input type="text" name="keterangan" placeholder="Contoh: Pojok depan kelas, Meja Guru..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none focus:border-blue-500">
            </div>

            <div class="pt-2">
                <?php 
                $uploaderId = 'foto_inventaris_tambah';
                $existingFotos = [];
                $isMobile = true;
                include BASE_PATH . '/modules/kelola-sarpras/views/barang/_foto_uploader.php';
                ?>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full <?= $hasSelectedRuangan ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-bold rounded-2xl py-3.5 text-xs shadow-md flex items-center justify-center gap-2 active:scale-98 transition-all">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span><?= $hasSelectedRuangan ? 'Simpan ke Inventaris & Ruangan Ini' : 'Simpan Data Inventaris' ?></span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Format Rupiah
    const inputHarga = document.getElementById('inputHarga');
    const hiddenHarga = document.getElementById('hiddenHarga');
    
    if (inputHarga && hiddenHarga) {
        inputHarga.addEventListener('input', function() {
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
            hiddenHarga.value = value.replace(/\./g, '').replace(',', '.');
        });
    }

    // Auto-select unit jika user memilih ruangan dari dropdown opsional
    const selRuanganMobile = document.getElementById('selRuanganMobile');
    const selUnitMobile = document.getElementById('selUnitMobile');
    if (selRuanganMobile && selUnitMobile) {
        selRuanganMobile.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const unit = opt?.getAttribute('data-unit');
            if (unit) {
                selUnitMobile.value = unit;
            }
        });
    }

    // Dependent Golongan & Kelompok + Preview Kode
    const selGol = document.getElementById('selGolongan');
    const selKel = document.getElementById('selKelompok');
    const selAng = document.getElementById('selAnggaran');
    const previewKodeMobile = document.getElementById('previewKodeMobile');

    function updatePreviewKodeMobile() {
        const kGol = selGol?.options[selGol.selectedIndex]?.dataset?.kode || '...';
        const kKel = selKel?.options[selKel.selectedIndex]?.dataset?.kode || '...';
        const kAng = selAng?.options[selAng.selectedIndex]?.dataset?.kode || '...';
        if (previewKodeMobile) {
            previewKodeMobile.textContent = `${kGol}.${kKel}.${kAng}.001`;
        }
    }

    if (selGol && selKel) {
        const masterOptions = Array.from(selKel.options)
            .filter(opt => opt.value !== '')
            .map(opt => ({
                value: opt.value,
                text: opt.textContent,
                golonganId: opt.getAttribute('data-golongan') || '',
                kode: opt.getAttribute('data-kode') || ''
            }));

        function applyFilter(selectedKelompokId = null) {
            const golId = selGol.value;
            const targetId = (selectedKelompokId !== null && selectedKelompokId !== undefined) ? String(selectedKelompokId) : selKel.value;

            selKel.innerHTML = '';

            if (!golId) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.dataset.kode = '...';
                opt.textContent = '-- Pilih Golongan Terlebih Dahulu --';
                selKel.appendChild(opt);
                selKel.disabled = true;
                selKel.value = '';
                updatePreviewKodeMobile();
                return;
            }

            const filtered = masterOptions.filter(item => String(item.golonganId) === String(golId));

            if (filtered.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.dataset.kode = '...';
                opt.textContent = '-- Belum Ada Kelompok di Golongan Ini --';
                selKel.appendChild(opt);
                selKel.disabled = true;
                selKel.value = '';
            } else {
                selKel.disabled = false;
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.dataset.kode = '...';
                placeholder.textContent = '-- Pilih Kelompok --';
                selKel.appendChild(placeholder);

                let hasSelected = false;
                filtered.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.value;
                    opt.dataset.golongan = item.golonganId;
                    opt.dataset.kode = item.kode;
                    opt.textContent = item.text;
                    if (targetId && String(item.value) === targetId) {
                        opt.selected = true;
                        hasSelected = true;
                    }
                    selKel.appendChild(opt);
                });

                selKel.value = (hasSelected && targetId) ? targetId : '';
            }
            updatePreviewKodeMobile();
        }

        selGol.addEventListener('change', function() {
            applyFilter('');
        });
        selKel.addEventListener('change', updatePreviewKodeMobile);
        if (selAng) selAng.addEventListener('change', updatePreviewKodeMobile);

        applyFilter(selKel.value || null);
    }
</script>
