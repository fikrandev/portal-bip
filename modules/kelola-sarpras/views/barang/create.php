<?php
/**
 * Form Tambah Aset Sarpras Baru
 * Portal BIP
 */
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Title -->
    <div>
        <a href="<?= url('kelola-sarpras/barang') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
            &larr; Kembali ke Inventaris
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1">
            Tambah Aset &amp; Barang Sarpras
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">
            Daftarkan inventaris baru ke dalam basis data sarana dan prasarana sekolah
        </p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-sarpras/barang/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Bagian 1: Identitas Barang -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center text-xs">1</span>
                    <span>Informasi Utama Barang</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Barang / Aset <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_barang" required placeholder="Contoh: Proyektor Epson EB-X500, Meja Siswa Single..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Golongan <span class="text-rose-500">*</span></label>
                        <select name="golongan_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                            <option value="">-- Pilih Golongan --</option>
                            <?php foreach ($golonganList as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= e($g['kode']) ?> - <?= e($g['nama_golongan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Kelompok <span class="text-rose-500">*</span></label>
                        <select name="kelompok_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                            <option value="">-- Pilih Kelompok --</option>
                            <?php foreach ($kelompokList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= e($k['kode']) ?> - <?= e($k['nama_kelompok']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                        <select name="asal_anggaran_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                            <option value="">-- Pilih Anggaran --</option>
                            <?php foreach ($asalAnggaranList as $a): ?>
                                <option value="<?= $a['id'] ?>"><?= e($a['kode']) ?> - <?= e($a['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bagian 2: Spesifikasi & Stok -->
            <div>
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-primary-100 text-primary-700 flex items-center justify-center text-xs">2</span>
                    <span>Spesifikasi, Kuantitas &amp; Kondisi</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Merk / Model</label>
                        <input type="text" name="merk_model" placeholder="Contoh: Epson, Informa, Asus..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Seri / Serial Number</label>
                        <input type="text" name="nomor_seri" placeholder="SN-XXXXX..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah</label>
                            <input type="number" name="jumlah" value="1" min="1" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                            <input type="text" name="satuan" value="Unit" placeholder="Unit / Pcs / Set" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Fisik</label>
                        <select name="kondisi" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-semibold">
                            <option value="Baik">Baik (100% Layak Pakai)</option>
                            <option value="Rusak Ringan">Rusak Ringan (Perlu Servis Ringan)</option>
                            <option value="Rusak Berat">Rusak Berat (Tidak Berfungsi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Ketersediaan</label>
                        <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-semibold">
                            <option value="Tersedia">Tersedia di Ruangan</option>
                            <option value="Dipinjam">Sedang Dipinjam</option>
                            <option value="Dalam Perbaikan">Dalam Perbaikan / Servis</option>
                            <option value="Dihapuskan">Dihapuskan / Afkir</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_perolehan" value="<?= date('Y-m-d') ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Perolehan Satuan (Rp)</label>
                        <input type="number" name="harga_perolehan" value="0" min="0" step="500" placeholder="0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Masa Manfaat (Tahun)</label>
                        <input type="number" name="masa_manfaat" value="5" min="0" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Foto Barang (Opsional)</label>
                        <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 focus:bg-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                        <input type="text" name="keterangan" placeholder="Keterangan kelengkapan, garansi, dsb..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('kelola-sarpras/barang') ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    <span>Simpan Data Aset</span>
                </button>
            </div>
        </form>
    </div>
</div>
