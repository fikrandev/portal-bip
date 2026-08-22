<?php
/**
 * Riwayat Pelatihan & Diklat Pegawai / Guru - Create View
 */
?>
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-primary-950 tracking-tight flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-primary-700 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <span>Tambah Riwayat Pelatihan</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Catat keikutsertaan guru/pegawai dalam diklat fungsional, workshop, bimtek, seminar, atau sertifikasi keahlian.
            </p>
        </div>
        <a href="<?= url('kelola-pegawai/pelatihan') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs transition-colors">
            ← Kembali ke Daftar Pelatihan
        </a>
    </div>

    <!-- Form Box -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 sm:p-8">
        <form action="<?= url('kelola-pegawai/pelatihan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Pegawai Pilihan with Live Search -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Pilih Pegawai / Guru Peserta <span class="text-rose-500">*</span>
                </label>
                <select name="pegawai_id" required class="searchable-select w-full" data-placeholder="-- Pilih Pegawai --" data-search-placeholder="Cari nama pegawai, NIY, NIK...">
                    <option value="">-- Pilih Pegawai --</option>
                    <?php foreach ($pegawaiList as $p): ?>
                        <option value="<?= e($p['id']) ?>" 
                                data-badge="<?= e($p['niy'] ?: ($p['nik'] ? 'NIK: ' . $p['nik'] : 'Non-NIY')) ?>" 
                                data-image="<?= !empty($p['foto']) ? url(ltrim($p['foto'], '/')) : '' ?>"
                                data-subtext="<?= !empty($p['gelar']) ? 'Gelar: ' . e($p['gelar']) : '' ?>"
                                <?= (old('pegawai_id', $selectedPegawaiId ?? '') == $p['id']) ? 'selected' : '' ?>>
                            <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (hasError('pegawai_id')): ?>
                    <p class="text-rose-500 text-xs mt-1"><?= getError('pegawai_id') ?></p>
                <?php endif; ?>
            </div>

            <!-- Nama Pelatihan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Nama Kegiatan Pelatihan / Diklat / Workshop <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama_pelatihan" value="<?= e(old('nama_pelatihan')) ?>" required placeholder="Contoh: Bimbingan Teknis Implementasi Kurikulum Merdeka Berbasis AI" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Jenis Pelatihan -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Jenis / Kategori Pelatihan <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis_pelatihan" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                        <option value="Bimtek & Workshop" <?= old('jenis_pelatihan', 'Bimtek & Workshop') === 'Bimtek & Workshop' ? 'selected' : '' ?>>Bimtek & Workshop</option>
                        <option value="Diklat Fungsional" <?= old('jenis_pelatihan') === 'Diklat Fungsional' ? 'selected' : '' ?>>Diklat Fungsional</option>
                        <option value="Pelatihan Teknis/Manajerial" <?= old('jenis_pelatihan') === 'Pelatihan Teknis/Manajerial' ? 'selected' : '' ?>>Pelatihan Teknis/Manajerial</option>
                        <option value="Seminar / Webinar" <?= old('jenis_pelatihan') === 'Seminar / Webinar' ? 'selected' : '' ?>>Seminar / Webinar</option>
                        <option value="Sertifikasi Keahlian / Profesi" <?= old('jenis_pelatihan') === 'Sertifikasi Keahlian / Profesi' ? 'selected' : '' ?>>Sertifikasi Keahlian / Profesi</option>
                        <option value="Kursus / Pelatihan Mandiri" <?= old('jenis_pelatihan') === 'Kursus / Pelatihan Mandiri' ? 'selected' : '' ?>>Kursus / Pelatihan Mandiri</option>
                        <option value="In House Training" <?= old('jenis_pelatihan') === 'In House Training' ? 'selected' : '' ?>>In House Training (Internal Yayasan)</option>
                    </select>
                </div>

                <!-- Peran Pegawai -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Peran dalam Kegiatan <span class="text-rose-500">*</span>
                    </label>
                    <select name="peran" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                        <option value="Peserta" <?= old('peran', 'Peserta') === 'Peserta' ? 'selected' : '' ?>>Peserta</option>
                        <option value="Narasumber / Pemateri" <?= old('peran') === 'Narasumber / Pemateri' ? 'selected' : '' ?>>Narasumber / Pemateri</option>
                        <option value="Fasilitator / Moderator" <?= old('peran') === 'Fasilitator / Moderator' ? 'selected' : '' ?>>Fasilitator / Moderator</option>
                        <option value="Panitia" <?= old('peran') === 'Panitia' ? 'selected' : '' ?>>Panitia</option>
                    </select>
                </div>

                <!-- Penyelenggara -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Lembaga / Instansi Penyelenggara <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="penyelenggara" value="<?= e(old('penyelenggara')) ?>" required placeholder="Contoh: Balai Guru Penggerak Sulteng / Kemendikbudristek" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Lokasi / Tempat -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tempat / Media Pelatihan
                    </label>
                    <input type="text" name="tempat" value="<?= e(old('tempat', 'Kota Palu')) ?>" placeholder="Contoh: Kota Palu / Online (Zoom Cloud Meeting)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Tanggal Mulai -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_mulai" value="<?= old('tanggal_mulai', date('Y-m-d')) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Tanggal Selesai -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tanggal Selesai
                    </label>
                    <input type="date" name="tanggal_selesai" value="<?= old('tanggal_selesai', date('Y-m-d')) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>

                <!-- Jumlah Jam Pelajaran (JP) -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Jumlah Jam Pelatihan (JP)
                    </label>
                    <input type="number" name="jumlah_jam" min="0" value="<?= old('jumlah_jam', '32') ?>" placeholder="Contoh: 32" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                    <p class="text-[11px] text-slate-400 mt-1">Masukkan angka JP (Jam Pelajaran) jika tercantum di sertifikat.</p>
                </div>

                <!-- Tahun -->
                <div>
                    <label class="block text-sm font-bold text-slate-800 mb-1.5">
                        Tahun Pelaksanaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="tahun" min="2000" max="<?= date('Y') + 1 ?>" value="<?= old('tahun', date('Y')) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
                </div>
            </div>

            <!-- Nomor Sertifikat -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Nomor Sertifikat / STTPL / Piagam Kelulusan
                </label>
                <input type="text" name="nomor_sertifikat" value="<?= e(old('nomor_sertifikat')) ?>" placeholder="Contoh: 0892/BGP-ST/DIK/2026" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors">
            </div>

            <!-- Upload Berkas Sertifikat & Foto Dokumentasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <label class="block text-sm font-bold text-slate-800 mb-1">
                        📜 Berkas Sertifikat / STTPL (PDF/Gambar)
                    </label>
                    <p class="text-[11px] text-slate-500 mb-2">Scan sertifikat tanda tamat / kelulusan diklat.</p>
                    <input type="file" name="file_sertifikat" accept="application/pdf,image/png,image/jpeg,image/jpg" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs focus:border-indigo-500">
                </div>

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <label class="block text-sm font-bold text-slate-800 mb-1">
                        📷 Foto Dokumentasi Kegiatan (Gambar)
                    </label>
                    <p class="text-[11px] text-slate-500 mb-2">Dokumentasi saat workshop atau pelatihan berlangsung.</p>
                    <input type="file" name="foto_dokumentasi" accept="image/png,image/jpeg,image/jpg,image/webp" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs focus:border-indigo-500">
                </div>
            </div>

            <!-- Keterangan Tambahan -->
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-1.5">
                    Materi Pokok / Catatan Kompetensi
                </label>
                <textarea name="keterangan" rows="3" placeholder="Ringkasan materi utama, hasil proyek, atau catatan kompetensi yang diperoleh..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 transition-colors"><?= e(old('keterangan')) ?></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-pegawai/pelatihan') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-7 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-lg shadow-indigo-600/25 transition-all">
                    Simpan Riwayat Pelatihan
                </button>
            </div>
        </form>
    </div>

</div>
