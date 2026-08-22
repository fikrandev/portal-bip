<?php
/**
 * Riwayat Pelatihan & Diklat Pegawai / Guru - Edit View
 */
?>
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-primary-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                    </svg>
                </div>
                <span>Edit Riwayat Pelatihan</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Perbarui data keikutsertaan pelatihan untuk <span class="font-bold text-slate-800"><?= e($pelatihan['nama_pegawai']) ?></span>.
            </p>
        </div>
        <a href="<?= url('kelola-pegawai/pelatihan') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition-colors">
            ← Kembali ke Daftar Pelatihan
        </a>
    </div>

    <!-- Form Box -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-pegawai/pelatihan/update/' . $pelatihan['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Pegawai Pilihan with Live Search -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Pegawai / Guru Peserta <span class="text-rose-500">*</span>
                </label>
                <select name="pegawai_id" required class="searchable-select w-full" data-placeholder="-- Pilih Pegawai --" data-search-placeholder="Cari nama pegawai, NIY, NIK...">
                    <?php foreach ($pegawaiList as $p): ?>
                        <option value="<?= e($p['id']) ?>" 
                                data-badge="<?= e($p['niy'] ?: ($p['nik'] ? 'NIK: ' . $p['nik'] : 'Non-NIY')) ?>" 
                                data-image="<?= !empty($p['foto']) ? url(ltrim($p['foto'], '/')) : '' ?>"
                                data-subtext="<?= !empty($p['gelar']) ? 'Gelar: ' . e($p['gelar']) : '' ?>"
                                <?= (old('pegawai_id', $pelatihan['pegawai_id']) == $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nama Pelatihan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Nama Kegiatan Pelatihan / Diklat / Workshop <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_pelatihan" value="<?= e(old('nama_pelatihan', $pelatihan['nama_pelatihan'])) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Jenis Pelatihan -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Jenis / Kategori Pelatihan <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis_pelatihan" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                        <option value="Bimtek & Workshop" <?= old('jenis_pelatihan', $pelatihan['jenis_pelatihan']) === 'Bimtek & Workshop' ? 'selected' : '' ?>>Bimtek & Workshop</option>
                        <option value="Diklat Fungsional" <?= old('jenis_pelatihan', $pelatihan['jenis_pelatihan']) === 'Diklat Fungsional' ? 'selected' : '' ?>>Diklat Fungsional</option>
                        <option value="Pelatihan Teknis/Manajerial" <?= old('jenis_pelatihan', $pelatihan['jenis_pelatihan']) === 'Pelatihan Teknis/Manajerial' ? 'selected' : '' ?>>Pelatihan Teknis/Manajerial</option>
                        <option value="Seminar / Webinar" <?= old('jenis_pelatihan', $pelatihan['jenis_pelatihan']) === 'Seminar / Webinar' ? 'selected' : '' ?>>Seminar / Webinar</option>
                        <option value="Sertifikasi Keahlian / Profesi" <?= old('jenis_pelatihan', $pelatihan['jenis_pelatihan']) === 'Sertifikasi Keahlian / Profesi' ? 'selected' : '' ?>>Sertifikasi Keahlian / Profesi</option>
                        <option value="Kursus / Pelatihan Mandiri" <?= old('jenis_pelatihan', $pelatihan['jenis_pelatihan']) === 'Kursus / Pelatihan Mandiri' ? 'selected' : '' ?>>Kursus / Pelatihan Mandiri</option>
                        <option value="In House Training" <?= old('jenis_pelatihan', $pelatihan['jenis_pelatihan']) === 'In House Training' ? 'selected' : '' ?>>In House Training (Internal Yayasan)</option>
                    </select>
                </div>

                <!-- Peran Pegawai -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Peran dalam Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <select name="peran" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                        <option value="Peserta" <?= old('peran', $pelatihan['peran']) === 'Peserta' ? 'selected' : '' ?>>Peserta</option>
                        <option value="Narasumber / Pemateri" <?= old('peran', $pelatihan['peran']) === 'Narasumber / Pemateri' ? 'selected' : '' ?>>Narasumber / Pemateri</option>
                        <option value="Fasilitator / Moderator" <?= old('peran', $pelatihan['peran']) === 'Fasilitator / Moderator' ? 'selected' : '' ?>>Fasilitator / Moderator</option>
                        <option value="Panitia" <?= old('peran', $pelatihan['peran']) === 'Panitia' ? 'selected' : '' ?>>Panitia</option>
                    </select>
                </div>

                <!-- Penyelenggara -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Lembaga / Instansi Penyelenggara <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="penyelenggara" value="<?= e(old('penyelenggara', $pelatihan['penyelenggara'])) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Lokasi / Tempat -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tempat / Media Pelatihan
                    </label>
                    <input type="text" name="tempat" value="<?= e(old('tempat', $pelatihan['tempat'])) ?>" placeholder="Contoh: Kota Palu / Online (Zoom)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai" value="<?= old('tanggal_mulai', $pelatihan['tanggal_mulai']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tanggal Selesai
                    </label>
                    <input type="date" name="tanggal_selesai" value="<?= old('tanggal_selesai', $pelatihan['tanggal_selesai'] ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Jumlah Jam Pelajaran (JP) -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Jumlah Jam Pelatihan (JP)
                    </label>
                    <input type="number" name="jumlah_jam" min="0" value="<?= old('jumlah_jam', $pelatihan['jumlah_jam']) ?>" placeholder="Contoh: 32" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Tahun -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tahun Pelaksanaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="tahun" min="2000" max="<?= date('Y') + 1 ?>" value="<?= old('tahun', $pelatihan['tahun']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>
            </div>

            <!-- Nomor Sertifikat -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Nomor Sertifikat / STTPL / Piagam Kelulusan
                </label>
                <input type="text" name="nomor_sertifikat" value="<?= e(old('nomor_sertifikat', $pelatihan['nomor_sertifikat'] ?? '')) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
            </div>

            <!-- Upload Berkas Sertifikat & Foto Dokumentasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <label class="block text-sm font-bold text-slate-800 mb-1">
                        📜 Berkas Sertifikat / STTPL (PDF/Gambar)
                    </label>
                    <?php if (!empty($pelatihan['file_sertifikat'])): ?>
                        <div class="flex items-center gap-2 mb-2 p-1.5 bg-white rounded-lg border border-slate-200">
                            <span class="text-xs text-emerald-600 font-semibold">✓ Sertifikat aktif</span>
                            <a href="<?= url(ltrim($pelatihan['file_sertifikat'], '/')) ?>" target="_blank" class="text-xs text-indigo-600 hover:underline">
                                (Lihat File)
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="file_sertifikat" accept="application/pdf,image/png,image/jpeg,image/jpg" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs focus:border-indigo-500">
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <label class="block text-sm font-bold text-slate-800 mb-1">
                        📷 Foto Dokumentasi Kegiatan (Gambar)
                    </label>
                    <?php if (!empty($pelatihan['foto_dokumentasi'])): ?>
                        <div class="flex items-center gap-2 mb-2 p-1.5 bg-white rounded-lg border border-slate-200">
                            <img src="<?= url(ltrim($pelatihan['foto_dokumentasi'], '/')) ?>" alt="Dokumentasi" class="h-8 w-8 object-cover rounded">
                            <a href="<?= url(ltrim($pelatihan['foto_dokumentasi'], '/')) ?>" target="_blank" class="text-xs text-indigo-600 hover:underline">
                                (Lihat Foto)
                            </a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="foto_dokumentasi" accept="image/png,image/jpeg,image/jpg,image/webp" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs focus:border-indigo-500">
                </div>
            </div>

            <!-- Keterangan Tambahan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Materi Pokok / Catatan Kompetensi
                </label>
                <textarea name="keterangan" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors"><?= e(old('keterangan', $pelatihan['keterangan'] ?? '')) ?></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-pegawai/pelatihan') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/25 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
