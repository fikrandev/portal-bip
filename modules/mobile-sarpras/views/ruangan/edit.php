<div class="px-4 pt-4 space-y-6">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/ruangan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">`n            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_to" value="<?= url('mobile-sarpras/ruangan') ?>">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Gedung / Bangunan <span class="text-rose-500">*</span></label>
                    <select name="bangunan_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                        <option value="">-- Pilih Gedung --</option>
                        <?php foreach ($bangunanList as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $b['id'] == $item['bangunan_id'] ? 'selected' : '' ?> ><?= e($b['nama_bangunan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Ruangan / Fasilitas <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_ruangan" value="<?= e($item['nama_ruangan']) ?>" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500" placeholder="Cth: Ruang Kelas 1A">
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Ruangan <span class="text-rose-500">*</span></label>
                        <select name="jenis_ruangan" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                            <option value="Ruang Kelas" <?= $item['jenis_ruangan'] == 'Ruang Kelas' ? 'selected' : '' ?>>Ruang Kelas (Belajar)</option>
                            <option value="Ruang Laboratorium">Ruang Laboratorium (Komputer/IPA/Bahasa)</option>
                            <option value="Ruang Kantor">Ruang Kantor / Administrasi</option>
                            <option value="Ruang Guru" <?= $item['jenis_ruangan'] == 'Ruang Guru' ? 'selected' : '' ?>>Ruang Guru</option>
                            <option value="Ruang Pimpinan">Ruang Kepala Sekolah / Pimpinan</option>
                            <option value="Ruang Perpustakaan">Ruang Perpustakaan</option>
                            <option value="Ruang UKS">Ruang UKS / Medis</option>
                            <option value="Ruang Ibadah">Ruang Ibadah / Masjid / Musholla</option>
                            <option value="Ruang Aula">Ruang Aula / Serbaguna</option>
                            <option value="Ruang Konseling / BK">Ruang Bimbingan Konseling (BK)</option>
                            <option value="Ruang OSIS">Ruang OSIS / Ekstrakurikuler</option>
                            <option value="Gudang">Gudang / Logistik</option>
                            <option value="Toilet" <?= $item['jenis_ruangan'] == 'Toilet' ? 'selected' : '' ?>>Toilet / Sanitasi</option>
                            <option value="Kantin">Kantin / Dapur</option>
                            <option value="Lainnya" <?= $item['jenis_ruangan'] == 'Lainnya' ? 'selected' : '' ?>>Fasilitas Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Posisi Lantai</label>
                        <input type="number" name="lantai" value="1" min="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                    </div>
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <div class="space-y-3 p-3 bg-purple-50/50 rounded-xl border border-purple-100">
                <p class="text-xs font-bold text-purple-800 uppercase tracking-wider mb-2">Dimensi & Luas</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Panjang (m)</label>
                        <input type="number" step="0.01" id="rng_panjang" name="panjang" value="<?= floatval($item['panjang']) ?>" placeholder="0.00" oninput="calculateLuasRng()" class="w-full px-3 py-2.5 bg-white border border-purple-200 rounded-xl text-sm font-bold outline-none focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lebar (m)</label>
                        <input type="number" step="0.01" id="rng_lebar" name="lebar" value="<?= floatval($item['lebar']) ?>" placeholder="0.00" oninput="calculateLuasRng()" class="w-full px-3 py-2.5 bg-white border border-purple-200 rounded-xl text-sm font-bold outline-none focus:border-purple-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-purple-900 mb-1">Luas Ruang (m²) <span class="text-purple-500 font-normal">(Otomatis)</span></label>
                    <input type="number" step="0.01" id="rng_luas" name="luas" value="<?= floatval($item['luas']) ?>" placeholder="0.00" class="w-full px-3 py-2.5 bg-purple-100 border border-purple-300 rounded-xl text-sm font-black text-purple-900 outline-none">
                </div>
            </div>

            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Unit / Jenjang</label>
                        <select name="unit" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500">
                            <option value="SD" <?= $item['unit'] == 'SD' ? 'selected' : '' ?>>SD</option>
                            <option value="SMP" <?= $item['unit'] == 'SMP' ? 'selected' : '' ?>>SMP</option>
                            <option value="SMA" <?= $item['unit'] == 'SMA' ? 'selected' : '' ?>>SMA</option>
                            <option value="PAUD">PAUD</option>
                            <option value="Yayasan">Yayasan</option>
                            <option value="Semua" <?= $item['unit'] == 'Semua' ? 'selected' : '' ?>>Semua / Umum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kapasitas (Org)</label>
                        <input type="number" name="kapasitas" min="0" value="30" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Ruangan (Opsional)</label>
                    <input type="text" name="kode_ruangan" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500 font-mono" placeholder="Otomatis jika kosong">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Penanggung Jawab (Opsional)</label>
                    <input type="text" name="penanggung_jawab" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500" placeholder="Nama Guru / Pegawai">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Fungsi</label>
                    <input type="text" name="keterangan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500" placeholder="Keterangan tambahan">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl py-3 shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-5 h-5"></i> Update Ruangan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function calculateLuasRng() {
        const p = parseFloat(document.getElementById('rng_panjang').value) || 0;
        const l = parseFloat(document.getElementById('rng_lebar').value) || 0;
        document.getElementById('rng_luas').value = (p * l).toFixed(2);
    }
</script>


