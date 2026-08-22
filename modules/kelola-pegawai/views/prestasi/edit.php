<?php
/**
 * Prestasi & Penghargaan Pegawai / Guru - Edit View
 */
?>
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/25">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                </div>
                <span>Edit Data Prestasi</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Perbarui data pencapaian untuk <span class="font-bold text-slate-800"><?= e($prestasi['nama_pegawai']) ?></span>.
            </p>
        </div>
        <a href="<?= url('kelola-pegawai/prestasi') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition-colors">
            ← Kembali ke Daftar Prestasi
        </a>
    </div>

    <!-- Form Box -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-pegawai/prestasi/update/' . $prestasi['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Pegawai Pilihan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Pegawai / Guru yang Berprestasi <span class="text-rose-500">*</span>
                </label>
                <select name="pegawai_id" required class="searchable-select w-full" data-placeholder="-- Pilih Pegawai --" data-search-placeholder="Cari nama pegawai, NIY, NIK...">
                    <?php foreach ($pegawaiList as $p): ?>
                        <option value="<?= e($p['id']) ?>" 
                                data-badge="<?= e($p['niy'] ?: ($p['nik'] ? 'NIK: ' . $p['nik'] : 'Non-NIY')) ?>" 
                                data-image="<?= !empty($p['foto']) ? url(ltrim($p['foto'], '/')) : '' ?>"
                                data-subtext="<?= !empty($p['gelar']) ? 'Gelar: ' . e($p['gelar']) : '' ?>"
                                <?= (old('pegawai_id', $prestasi['pegawai_id']) == $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nama Prestasi / Penghargaan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Nama Prestasi / Kegiatan / Kejuaraan <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_prestasi" value="<?= e(old('nama_prestasi', $prestasi['nama_prestasi'])) ?>" required placeholder="Contoh: Juara 1 Lomba Guru Inovatif & Pembelajaran Digital" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Peringkat / Capaian -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Peringkat / Penghargaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="peringkat" value="<?= e(old('peringkat', $prestasi['peringkat'])) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors">
                </div>

                <!-- Tingkat Prestasi -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tingkat Prestasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="tingkat" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors">
                        <option value="Sekolah/Internal" <?= old('tingkat', $prestasi['tingkat']) === 'Sekolah/Internal' ? 'selected' : '' ?>>Sekolah / Internal</option>
                        <option value="Kecamatan" <?= old('tingkat', $prestasi['tingkat']) === 'Kecamatan' ? 'selected' : '' ?>>Kecamatan</option>
                        <option value="Kota/Kabupaten" <?= old('tingkat', $prestasi['tingkat']) === 'Kota/Kabupaten' ? 'selected' : '' ?>>Kota / Kabupaten</option>
                        <option value="Provinsi" <?= old('tingkat', $prestasi['tingkat']) === 'Provinsi' ? 'selected' : '' ?>>Provinsi</option>
                        <option value="Nasional" <?= old('tingkat', $prestasi['tingkat']) === 'Nasional' ? 'selected' : '' ?>>Nasional</option>
                        <option value="Internasional" <?= old('tingkat', $prestasi['tingkat']) === 'Internasional' ? 'selected' : '' ?>>Internasional</option>
                    </select>
                </div>

                <!-- Kategori Prestasi -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Kategori Prestasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="kategori" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors">
                        <option value="Akademik & Sains" <?= old('kategori', $prestasi['kategori']) === 'Akademik & Sains' ? 'selected' : '' ?>>Akademik & Sains</option>
                        <option value="Pedagogik & Pembelajaran" <?= old('kategori', $prestasi['kategori']) === 'Pedagogik & Pembelajaran' ? 'selected' : '' ?>>Pedagogik & Pembelajaran</option>
                        <option value="Inovasi & Teknologi Pendidikan" <?= old('kategori', $prestasi['kategori']) === 'Inovasi & Teknologi Pendidikan' ? 'selected' : '' ?>>Inovasi & Teknologi Pendidikan</option>
                        <option value="Karya Tulis / Penelitian" <?= old('kategori', $prestasi['kategori']) === 'Karya Tulis / Penelitian' ? 'selected' : '' ?>>Karya Tulis / Penelitian</option>
                        <option value="Keagamaan & Tahfidz" <?= old('kategori', $prestasi['kategori']) === 'Keagamaan & Tahfidz' ? 'selected' : '' ?>>Keagamaan & Tahfidz</option>
                        <option value="Seni, Bahasa & Budaya" <?= old('kategori', $prestasi['kategori']) === 'Seni, Bahasa & Budaya' ? 'selected' : '' ?>>Seni, Bahasa & Budaya</option>
                        <option value="Olahraga & Kebugaran" <?= old('kategori', $prestasi['kategori']) === 'Olahraga & Kebugaran' ? 'selected' : '' ?>>Olahraga & Kebugaran</option>
                        <option value="Manajerial & Kelembagaan" <?= old('kategori', $prestasi['kategori']) === 'Manajerial & Kelembagaan' ? 'selected' : '' ?>>Manajerial & Kelembagaan</option>
                        <option value="Lainnya" <?= old('kategori', $prestasi['kategori']) === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <!-- Penyelenggara -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Lembaga / Penyelenggara <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="penyelenggara" value="<?= e(old('penyelenggara', $prestasi['penyelenggara'])) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors">
                </div>

                <!-- Tahun -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tahun Capaian <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="tahun" min="2000" max="<?= date('Y') + 1 ?>" value="<?= old('tahun', $prestasi['tahun']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors">
                </div>

                <!-- Tanggal Perolehan -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tanggal Perolehan / Sertifikat
                    </label>
                    <input type="date" name="tanggal_peroleh" value="<?= old('tanggal_peroleh', $prestasi['tanggal_peroleh'] ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors">
                </div>
            </div>

            <!-- Nomor Sertifikat -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Nomor Piagam / Sertifikat
                </label>
                <input type="text" name="nomor_sertifikat" value="<?= e(old('nomor_sertifikat', $prestasi['nomor_sertifikat'] ?? '')) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors">
            </div>

            <!-- Upload Berkas Sertifikat & Foto Dokumentasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <label class="block text-sm font-bold text-slate-800 mb-1">
                        📜 Berkas Sertifikat / Piagam (PDF/Gambar)
                    </label>
                    <?php if (!empty($prestasi['file_sertifikat'])): ?>
                        <div class="flex items-center gap-2 mb-2 p-1.5 bg-white rounded-lg border border-slate-200">
                            <span class="text-xs text-emerald-600 font-semibold">✓ Sertifikat aktif</span>
                            <a href="<?= url(ltrim($prestasi['file_sertifikat'], '/')) ?>" target="_blank" class="text-xs text-primary-600 hover:underline">
                                (Lihat File)
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="file_sertifikat" accept="application/pdf,image/png,image/jpeg,image/jpg" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs focus:border-amber-500">
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <label class="block text-sm font-bold text-slate-800 mb-1">
                        📷 Foto Dokumentasi / Penyerahan
                    </label>
                    <?php if (!empty($prestasi['foto_dokumentasi'])): ?>
                        <div class="flex items-center gap-2 mb-2 p-1.5 bg-white rounded-lg border border-slate-200">
                            <img src="<?= url(ltrim($prestasi['foto_dokumentasi'], '/')) ?>" alt="Dokumentasi" class="h-8 w-8 object-cover rounded">
                            <a href="<?= url(ltrim($prestasi['foto_dokumentasi'], '/')) ?>" target="_blank" class="text-xs text-primary-600 hover:underline">
                                (Lihat Foto)
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="foto_dokumentasi" accept="image/png,image/jpeg,image/jpg,image/webp" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs focus:border-amber-500">
                </div>
            </div>

            <!-- Keterangan Tambahan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Deskripsi / Catatan Prestasi
                </label>
                <textarea name="keterangan" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-amber-500 transition-colors"><?= e(old('keterangan', $prestasi['keterangan'] ?? '')) ?></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-pegawai/prestasi') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-7 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-sm shadow-lg shadow-amber-500/25 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
