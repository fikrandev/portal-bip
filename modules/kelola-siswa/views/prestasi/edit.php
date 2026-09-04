<?php
/**
 * Edit Prestasi Siswa - Portal BIP
 */
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <a href="<?= url('kelola-siswa/prestasi') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            <span>Kembali ke Daftar Prestasi</span>
        </a>
    </div>

    <!-- Card Form -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="text-2xl">✏️</span> Edit Data Prestasi Siswa
            </h1>
            <p class="text-xs text-slate-500 mt-1">Perbarui rincian penghargaan dan kejuaraan yang diraih oleh peserta didik.</p>
        </div>

        <form action="<?= url('kelola-siswa/prestasi/update/' . $prestasi['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Pilih Siswa -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Peserta Didik <span class="text-rose-500">*</span></label>
                <select name="siswa_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                    <?php foreach ($allSiswa as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($prestasi['siswa_id'] == $s['id']) ? 'selected' : '' ?>>
                            <?= e($s['nama_lengkap'] ?: $s['nama']) ?> (<?= e($s['jenjang'] ?: 'SD') ?> - <?= e($s['kelas'] ?: '-') ?> | NISN: <?= e($s['nisn'] ?: '-') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Grid: Nama Prestasi & Peringkat/Juara -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Prestasi / Lomba <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_prestasi" value="<?= e($prestasi['nama_prestasi']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Peringkat / Juara <span class="text-rose-500">*</span></label>
                    <input type="text" name="peringkat" value="<?= e($prestasi['peringkat']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>
            </div>

            <!-- Grid: Bidang, Tingkat, Tahun -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Bidang Prestasi <span class="text-rose-500">*</span></label>
                    <select name="bidang" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                        <?php foreach (['Akademik', 'Keagamaan & Tahfidz', 'Olahraga', 'Seni & Budaya', 'Teknologi / Robotik', 'Bahasa / Debat', 'Lainnya'] as $b): ?>
                            <option value="<?= $b ?>" <?= $prestasi['bidang'] === $b ? 'selected' : '' ?>><?= $b ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tingkat Prestasi <span class="text-rose-500">*</span></label>
                    <select name="tingkat" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                        <?php foreach (['Sekolah/Internal', 'Kecamatan', 'Kota/Kabupaten', 'Provinsi', 'Nasional', 'Internasional'] as $t): ?>
                            <option value="<?= $t ?>" <?= $prestasi['tingkat'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun <span class="text-rose-500">*</span></label>
                    <input type="text" name="tahun" value="<?= e($prestasi['tahun'] ?: date('Y')) ?>" maxlength="4" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>
            </div>

            <!-- Grid: Penyelenggara, Tanggal, Guru Pendamping -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Lembaga Penyelenggara</label>
                    <input type="text" name="penyelenggara" value="<?= e($prestasi['penyelenggara']) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Perolehan</label>
                    <input type="date" name="tanggal_peroleh" value="<?= e($prestasi['tanggal_peroleh']) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Guru Pembimbing / Pendamping</label>
                    <input type="text" name="guru_pendamping" value="<?= e($prestasi['guru_pendamping']) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>
            </div>

            <!-- Grid: No Sertifikat & Upload File -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Piagam / Sertifikat</label>
                    <input type="text" name="nomor_sertifikat" value="<?= e($prestasi['nomor_sertifikat']) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Scan Piagam Baru (Opsional)</label>
                    <input type="file" name="file_sertifikat" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors">
                    <?php if (!empty($prestasi['file_sertifikat'])): ?>
                        <p class="text-[11px] text-indigo-600 mt-1">Berkas saat ini: <a href="<?= asset('uploads/prestasi_siswa/' . $prestasi['file_sertifikat']) ?>" target="_blank" class="underline font-bold">Lihat Piagam</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Keterangan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Keterangan / Catatan Prestasi</label>
                <textarea name="keterangan" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-amber-500 transition-colors"><?= e($prestasi['keterangan']) ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('kelola-siswa/prestasi') ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-sm shadow-amber-600/30 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
