<?php
/**
 * Proses Siswa Keluar / Mutasi - Portal BIP
 */
?>
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <a href="<?= url('kelola-siswa/keluar') ?>" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            <span>Kembali ke Data Siswa Keluar</span>
        </a>
    </div>

    <!-- Card Form -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 space-y-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="text-2xl">📤</span> Proses Siswa Keluar / Mutasi
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Catat status siswa yang pindah sekolah, lulus, atau mengundurkan diri. Status siswa akan otomatis diubah menjadi Non-Aktif.
            </p>
        </div>

        <form action="<?= url('kelola-siswa/keluar/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Pilih Siswa Aktif -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Siswa yang Keluar / Mutasi <span class="text-rose-500">*</span></label>
                <select name="siswa_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                    <option value="">-- Pilih Siswa Aktif --</option>
                    <?php foreach ($allSiswaAktif as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (!empty($selectedSiswaId) && $selectedSiswaId == $s['id']) ? 'selected' : '' ?>>
                            <?= e($s['nama_lengkap'] ?: $s['nama']) ?> (<?= e($s['jenjang'] ?: 'SD') ?> - <?= e($s['kelas'] ?: '-') ?> | NISN: <?= e($s['nisn'] ?: '-') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Grid: Jenis Keluar & Tanggal Keluar -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Status Keluar <span class="text-rose-500">*</span></label>
                    <select name="jenis_keluar" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                        <option value="Mutasi Keluar" selected>Mutasi Keluar (Pindah Sekolah)</option>
                        <option value="Lulus">Lulus / Alumni</option>
                        <option value="Mengundurkan Diri">Mengundurkan Diri</option>
                        <option value="Dikeluarkan">Dikeluarkan (Drop Out)</option>
                        <option value="Wafat">Wafat</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Keluar / Mutasi <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_keluar" value="<?= date('Y-m-d') ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
            </div>

            <!-- Grid: Tahun Ajaran & Sekolah Tujuan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Ajaran Saat Keluar</label>
                    <input type="text" name="tahun_ajaran" value="2026/2027" placeholder="Contoh: 2026/2027" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Sekolah / Institusi Tujuan (Jika Pindah)</label>
                    <input type="text" name="sekolah_tujuan" placeholder="Nama sekolah tujuan kepindahan siswa" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
            </div>

            <!-- Grid: Nomor Surat & File Scan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Surat Keterangan Pindah / SKL</label>
                    <input type="text" name="nomor_surat" placeholder="Contoh: 421.2/089/SD-BIP/VIII/2026" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Scan Surat Permohonan / Pindah (Opsional)</label>
                    <input type="file" name="file_surat" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
            </div>

            <!-- Alasan Keluar -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Alasan Keluar / Kepindahan</label>
                <textarea name="alasan_keluar" rows="2" placeholder="Contoh: Mengikuti perpindahan tugas orang tua ke luar kota..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors"></textarea>
            </div>

            <!-- Catatan Tambahan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tambahan</label>
                <textarea name="catatan" rows="2" placeholder="Catatan berkas, administrasi, atau rekam jejak..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('kelola-siswa/keluar') ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition-all">
                    Proses Siswa Keluar
                </button>
            </div>
        </form>
    </div>

</div>
