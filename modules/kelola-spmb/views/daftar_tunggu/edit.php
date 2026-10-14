<?php
/**
 * View Edit Calon Siswa Daftar Tunggu SPMB
 * Portal BIP
 */
$item = $pendaftar ?? [];
$groups = $groups ?? [];
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= url('kelola-spmb') ?>" class="hover:text-amber-600">SPMB</a>
                <span>&bull;</span>
                <a href="<?= url('kelola-spmb/daftar-tunggu') ?>" class="hover:text-amber-600">Daftar Tunggu</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Edit Data</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Edit Siswa Daftar Tunggu</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                No. Pendaftaran: <span class="font-mono font-bold text-amber-700"><?= e($item['nomor_pendaftaran'] ?? '-') ?></span>
            </p>
        </div>
        <div>
            <a href="<?= url('kelola-spmb/daftar-tunggu/detail/' . $item['id']) ?>" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                &larr; Batal & Kembali
            </a>
        </div>
    </div>

    <!-- Form Edit -->
    <form action="<?= url('kelola-spmb/daftar-tunggu/update/' . $item['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>
        <input type="hidden" name="jenis_pendaftaran" value="Daftar Tunggu">

        <!-- 1. Data Pendaftaran & Rekomendasi -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 inline-flex items-center justify-center text-xs">1</span>
                Informasi Pendaftaran & Rekomendasi
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Group SPMB</label>
                    <select name="group_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none bg-white font-semibold">
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ($item['group_id'] == $g['id']) ? 'selected' : '' ?>>
                                <?= e($g['nama_group']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No. Pendaftaran</label>
                    <input type="text" value="<?= e($item['nomor_pendaftaran']) ?>" disabled
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-100 font-mono font-bold text-slate-600 cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang</label>
                    <select name="jenjang" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none bg-white font-bold text-slate-800">
                        <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $j): ?>
                            <option value="<?= $j ?>" <?= ($item['jenjang'] === $j) ? 'selected' : '' ?>><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Daftar</label>
                    <input type="date" name="tanggal_daftar" value="<?= e($item['tanggal_daftar']) ?>" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rekomendasi <span class="text-rose-500">*</span></label>
                    <?php $katPilihan = $item['kategori_pendaftar'] ?: 'Yayasan'; ?>
                    <select name="kategori_pendaftar" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none bg-white font-semibold text-slate-800">
                        <option value="Yayasan" <?= ($katPilihan === 'Yayasan') ? 'selected' : '' ?>>🏢 Yayasan</option>
                        <option value="Dinas" <?= ($katPilihan === 'Dinas') ? 'selected' : '' ?>>🏛️ Dinas</option>
                        <option value="Lainnya" <?= ($katPilihan === 'Lainnya') ? 'selected' : '' ?>>📌 Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Rekomendasi <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_rekomendasi" required value="<?= e($item['nama_rekomendasi'] ?? '') ?>" 
                           placeholder="Contoh: Bpk. H. Ahmad / Dinas Pendidikan"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none font-semibold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Gelombang</label>
                    <input type="text" name="gelombang" value="<?= e($item['gelombang'] ?? 'Gelombang 1') ?>"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Seleksi</label>
                    <select name="status_kelulusan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none bg-white font-bold text-amber-800">
                        <?php foreach (['Cadangan', 'Menunggu Seleksi', 'Lolos Berkas', 'Lulus / Diterima', 'Tidak Lulus', 'Dibatalkan'] as $stOpt): ?>
                            <option value="<?= $stOpt ?>" <?= (($item['status_kelulusan'] ?? 'Cadangan') === $stOpt) ? 'selected' : '' ?>><?= $stOpt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Asal Sekolah Sebelumnya</label>
                    <input type="text" name="asal_sekolah" value="<?= e($item['asal_sekolah'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>
            </div>
        </div>

        <!-- 2. Biodata Calon Siswa -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 inline-flex items-center justify-center text-xs">2</span>
                Identitas Calon Siswa
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_lengkap" value="<?= e($item['nama_lengkap']) ?>" required 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Panggilan</label>
                    <input type="text" name="nama_panggilan" value="<?= e($item['nama_panggilan'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none bg-white">
                        <option value="L" <?= ($item['jenis_kelamin'] === 'L') ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($item['jenis_kelamin'] === 'P') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NISN</label>
                    <input type="text" name="nisn" value="<?= e($item['nisn'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NIK Siswa</label>
                    <input type="text" name="nik" value="<?= e($item['nik'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= e($item['tempat_lahir'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="edit_tgl_lahir" value="<?= e($item['tanggal_lahir'] ?? '') ?>" onchange="hitungUmurEdit(this.value)"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Umur Siswa</label>
                    <input type="text" name="umur" id="edit_umur" value="<?= e($item['umur'] ?? '') ?>" 
                           placeholder="Otomatis dihitung..."
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Agama</label>
                    <input type="text" name="agama" value="<?= e($item['agama'] ?? 'Islam') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Anak Ke-</label>
                    <input type="number" name="anak_ke" value="<?= e($item['anak_ke'] ?? '') ?>" min="1" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Saudara</label>
                    <input type="number" name="jumlah_saudara" value="<?= e($item['jumlah_saudara'] ?? '') ?>" min="0" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none">
                </div>
                <div class="sm:col-span-2 lg:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Tempat Tinggal</label>
                    <textarea name="alamat" rows="2" 
                              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none"><?= e($item['alamat'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- 3. Data Orang Tua -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 inline-flex items-center justify-center text-xs">3</span>
                Identitas Orang Tua / Wali
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Ayah -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider border-b border-slate-200 pb-1">Data Ayah</h3>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nama Ayah</label>
                        <input type="text" name="nama_ayah" value="<?= e($item['nama_ayah'] ?? '') ?>" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:border-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">No. HP / WA Ayah</label>
                        <input type="text" name="no_hp_ayah" value="<?= e($item['no_hp_ayah'] ?? '') ?>" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-mono focus:border-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" value="<?= e($item['pekerjaan_ayah'] ?? '') ?>" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:border-amber-500 outline-none">
                    </div>
                </div>

                <!-- Ibu -->
                <div class="space-y-3 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <h3 class="font-bold text-slate-800 text-xs uppercase tracking-wider border-b border-slate-200 pb-1">Data Ibu</h3>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Nama Ibu</label>
                        <input type="text" name="nama_ibu" value="<?= e($item['nama_ibu'] ?? '') ?>" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:border-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">No. HP / WA Ibu</label>
                        <input type="text" name="no_hp_ibu" value="<?= e($item['no_hp_ibu'] ?? '') ?>" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-mono focus:border-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" value="<?= e($item['pekerjaan_ibu'] ?? '') ?>" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:border-amber-500 outline-none">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Utama</label>
                <input type="text" name="no_hp" value="<?= e($item['no_hp'] ?? '') ?>" required 
                       class="w-full sm:w-1/2 px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-mono focus:border-amber-500 outline-none">
            </div>
        </div>

        <!-- 4. Pasfoto & Catatan -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-700 inline-flex items-center justify-center text-xs">4</span>
                Pasfoto & Catatan Tambahan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ganti Pasfoto Siswa</label>
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" 
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                    <?php if (!empty($item['foto'])): ?>
                        <div class="mt-2 flex items-center gap-2 text-xs text-slate-500">
                            <img src="<?= url($item['foto']) ?>" class="w-10 h-10 rounded-lg object-cover border border-slate-200">
                            <span>Pasfoto saat ini telah terpasang.</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Panitia SPMB</label>
                    <textarea name="catatan" rows="3" placeholder="Tambahkan catatan khusus verifikasi atau rekomendasi..."
                              class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-amber-500 outline-none"><?= e($item['catatan'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="<?= url('kelola-spmb/daftar-tunggu/detail/' . $item['id']) ?>" 
               class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors">
                Batal
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-white font-bold text-xs shadow-md shadow-amber-500/20 transition-all transform hover:-translate-y-0.5">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
const PATOKAN_UMUR_EDIT = <?= json_encode($activeGroup['tanggal_patokan_umur'] ?? '2027-07-01') ?>;

function hitungUmurEdit(tglLahirStr) {
    const inputUmur = document.getElementById('edit_umur');
    if (!tglLahirStr || !inputUmur) return;

    try {
        const dLahir = new Date(tglLahirStr);
        const dPatokan = new Date(PATOKAN_UMUR_EDIT);
        if (isNaN(dLahir.getTime())) return;
        if (dLahir > dPatokan) {
            inputUmur.value = 'Tanggal belum tiba';
            return;
        }

        let years = dPatokan.getFullYear() - dLahir.getFullYear();
        let months = dPatokan.getMonth() - dLahir.getMonth();
        let days = dPatokan.getDate() - dLahir.getDate();
        if (days < 0) months--;
        if (months < 0) { years--; months += 12; }

        let hasil = years + ' Tahun';
        if (months > 0) hasil += ' ' + months + ' Bulan';
        inputUmur.value = hasil;
    } catch (e) {
        inputUmur.value = '';
    }
}
</script>
