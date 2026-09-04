<?php
/**
 * Edit Siswa Keluar / Mutasi - Portal BIP
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
                <span class="text-2xl">✏️</span> Edit Catatan Siswa Keluar / Mutasi
            </h1>
            <p class="text-xs text-slate-500 mt-1">Perbarui rincian kepindahan atau status kelulusan peserta didik.</p>
        </div>

        <form action="<?= url('kelola-siswa/keluar/update/' . $keluar['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?= CSRF::field() ?>

            <!-- Siswa Info (Readonly) -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <p class="text-[11px] font-bold text-slate-500 uppercase">Peserta Didik</p>
                <h3 class="text-base font-extrabold text-slate-900 mt-0.5"><?= e($keluar['nama_lengkap'] ?: $keluar['nama']) ?></h3>
                <p class="text-xs text-slate-600">NISN: <?= e($keluar['nisn'] ?: '-') ?> | Jenjang: <?= e($keluar['jenjang'] ?: 'SD') ?> (Kelas: <?= e($keluar['kelas'] ?: '-') ?>)</p>
            </div>

            <!-- Grid: Jenis Keluar & Tanggal Keluar -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Status Keluar <span class="text-rose-500">*</span></label>
                    <select name="jenis_keluar" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                        <?php foreach (['Mutasi Keluar', 'Lulus', 'Mengundurkan Diri', 'Dikeluarkan', 'Wafat', 'Lainnya'] as $j): ?>
                            <option value="<?= $j ?>" <?= $keluar['jenis_keluar'] === $j ? 'selected' : '' ?>><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal Keluar / Mutasi <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_keluar" value="<?= e($keluar['tanggal_keluar']) ?>" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
            </div>

            <!-- Grid: Tahun Ajaran & Sekolah Tujuan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Tahun Ajaran Saat Keluar</label>
                    <input type="text" name="tahun_ajaran" value="<?= e($keluar['tahun_ajaran']) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Sekolah / Institusi Tujuan</label>
                    <input type="text" name="sekolah_tujuan" value="<?= e($keluar['sekolah_tujuan']) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
            </div>

            <!-- Grid: Nomor Surat & File Scan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nomor Surat Keterangan Pindah / SKL</label>
                    <input type="text" name="nomor_surat" value="<?= e($keluar['nomor_surat']) ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Upload Scan Surat Baru (Opsional)</label>
                    <input type="file" name="file_surat" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors">
                    <?php if (!empty($keluar['file_surat'])): ?>
                        <p class="text-[11px] text-rose-600 mt-1">Berkas saat ini: <a href="<?= asset('uploads/mutasi_siswa/' . $keluar['file_surat']) ?>" target="_blank" class="underline font-bold">Lihat Surat</a></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Alasan Keluar -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Alasan Keluar / Kepindahan</label>
                <textarea name="alasan_keluar" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors"><?= e($keluar['alasan_keluar']) ?></textarea>
            </div>

            <!-- Catatan Tambahan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Tambahan</label>
                <textarea name="catatan" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:bg-white focus:border-rose-500 transition-colors"><?= e($keluar['catatan']) ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= url('kelola-siswa/keluar') ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-600/30 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
