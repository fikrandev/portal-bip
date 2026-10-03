<?php
/**
 * Form Edit Aset Sarpras
 * Portal BIP
 */
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb & Title -->
    <div>
        <a href="<?= url('kelola-sarpras/barang') ?>" class="text-xs font-bold text-primary-600 hover:underline flex items-center gap-1">
            &larr; Kembali ke Inventaris
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1 flex items-center gap-2">
            <span>Edit Aset: <?= e($barang['nama_barang']) ?></span>
            <span class="text-xs px-2.5 py-0.5 rounded-lg font-mono bg-primary-100 text-primary-800"><?= e($barang['kode_barang']) ?></span>
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">
            Perbarui data inventaris, penempatan ruangan, atau mutasi kondisi fisik barang
        </p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-sarpras/barang/update/' . $barang['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
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
                        <input type="text" name="nama_barang" value="<?= e($barang['nama_barang']) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Barang (Terkunci)</label>
                        <input type="text" value="<?= e($barang['kode_barang']) ?>" readonly disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono text-slate-500 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Golongan <span class="text-rose-500">*</span></label>
                        <select name="golongan_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                            <option value="">-- Pilih Golongan --</option>
                            <?php foreach ($golonganList as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= isset($barang['golongan_id']) && $barang['golongan_id'] == $g['id'] ? 'selected' : '' ?>><?= e($g['kode']) ?> - <?= e($g['nama_golongan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Kelompok <span class="text-rose-500">*</span></label>
                        <select name="kelompok_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                            <option value="">-- Pilih Kelompok --</option>
                            <?php foreach ($kelompokList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= isset($barang['kelompok_id']) && $barang['kelompok_id'] == $k['id'] ? 'selected' : '' ?>><?= e($k['kode']) ?> - <?= e($k['nama_kelompok']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Asal Anggaran <span class="text-rose-500">*</span></label>
                        <select name="asal_anggaran_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                            <option value="">-- Pilih Anggaran --</option>
                            <?php foreach ($asalAnggaranList as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= isset($barang['asal_anggaran_id']) && $barang['asal_anggaran_id'] == $a['id'] ? 'selected' : '' ?>><?= e($a['kode']) ?> - <?= e($a['nama']) ?></option>
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
                        <input type="text" name="merk_model" value="<?= e($barang['merk_model'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Seri / Serial Number</label>
                        <input type="text" name="nomor_seri" value="<?= e($barang['nomor_seri'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah</label>
                            <input type="number" name="jumlah" value="<?= $barang['jumlah'] ?>" min="1" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                            <input type="text" name="satuan" value="<?= e($barang['satuan']) ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kondisi Fisik</label>
                        <select name="kondisi" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-semibold">
                            <option value="Baik" <?= $barang['kondisi'] === 'Baik' ? 'selected' : '' ?>>Baik (100% Layak Pakai)</option>
                            <option value="Rusak Ringan" <?= $barang['kondisi'] === 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan (Perlu Servis Ringan)</option>
                            <option value="Rusak Berat" <?= $barang['kondisi'] === 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat (Tidak Berfungsi)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Ketersediaan</label>
                        <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-semibold">
                            <option value="Tersedia" <?= $barang['status'] === 'Tersedia' ? 'selected' : '' ?>>Tersedia di Ruangan</option>
                            <option value="Dipinjam" <?= $barang['status'] === 'Dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam</option>
                            <option value="Dalam Perbaikan" <?= $barang['status'] === 'Dalam Perbaikan' ? 'selected' : '' ?>>Dalam Perbaikan / Servis</option>
                            <option value="Dihapuskan" <?= $barang['status'] === 'Dihapuskan' ? 'selected' : '' ?>>Dihapuskan / Afkir</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Perolehan <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_perolehan" value="<?= e($barang['tanggal_perolehan'] ?? date('Y-m-d')) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Perolehan Satuan (Rp)</label>
                        <input type="number" name="harga_perolehan" value="<?= $barang['harga_perolehan'] ?>" min="0" step="500" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none font-bold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Masa Manfaat (Tahun)</label>
                        <input type="number" name="masa_manfaat" value="<?= e($barang['masa_manfaat'] ?? 5) ?>" min="0" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
                    </div>

                    <div class="sm:col-span-3 pt-2">
                        <?php 
                        $uploaderId = 'foto_barang_edit';
                        $existingFotos = SarprasModel::getFotoList($barang['foto'] ?? '');
                        $isMobile = false;
                        include BASE_PATH . '/modules/kelola-sarpras/views/barang/_foto_uploader.php';
                        ?>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                        <input type="text" name="keterangan" value="<?= e($barang['keterangan'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:border-primary-500 outline-none">
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
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
