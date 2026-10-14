<?php
/**
 * View Form Edit Calon Siswa Baru SPMB
 * Portal BIP
 */
$item = $pendaftar ?? [];
$groups = $groups ?? [];
?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= url('kelola-spmb') ?>" class="hover:text-sky-600">SPMB</a>
                <span>&bull;</span>
                <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="hover:text-sky-600">Siswa Baru</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Edit Data</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                Edit Pendaftar: <?= e($item['nama_lengkap']) ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                No. Pendaftaran: <span class="font-mono font-bold text-sky-700"><?= e($item['nomor_pendaftaran']) ?></span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('kelola-spmb/siswa-baru/detail/' . $item['id']) ?>" class="px-3.5 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 font-semibold text-xs transition-colors">
                Lihat Profil &rarr;
            </a>
            <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                Kembali
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="<?= url('kelola-spmb/siswa-baru/update/' . $item['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- 1. Data Group & Pendaftaran -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 inline-flex items-center justify-center text-xs">1</span>
                Informasi Group & Pendaftaran
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Group SPMB</label>
                    <select name="group_id" id="edit_group_id" onchange="onGroupChangeEdit()" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none bg-white font-semibold">
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>" 
                                    data-patokan="<?= e($g['tanggal_patokan_umur'] ?? '2027-07-01') ?>"
                                    data-patokan-indo="<?= SpmbModel::formatTglIndo($g['tanggal_patokan_umur'] ?? '2027-07-01') ?>"
                                    <?= ($item['group_id'] == $g['id']) ? 'selected' : '' ?>>
                                <?= e($g['nama_group']) ?> <?= ($g['is_active'] == 1) ? '(AKTIF)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Pendaftaran</label>
                    <input type="text" value="<?= e($item['nomor_pendaftaran']) ?>" disabled
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 font-mono font-bold bg-slate-50 text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Daftar</label>
                    <input type="date" name="tanggal_daftar" value="<?= e($item['tanggal_daftar']) ?>" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Gelombang</label>
                    <input type="text" name="gelombang" value="<?= e($item['gelombang']) ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang Tujuan</label>
                    <select name="jenjang" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none bg-white font-bold text-slate-800">
                        <?php foreach (['PAUD', 'SD', 'SMP', 'SMA'] as $j): ?>
                            <option value="<?= $j ?>" <?= ($item['jenjang'] === $j) ? 'selected' : '' ?>><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Pendaftar</label>
                    <?php 
                    $currKat = $item['kategori_pendaftar'] ?: ($item['jalur_pendaftaran'] ?: 'Umum (Luar Bina Insan)');
                    $isKeluargaVal = str_contains(strtolower($currKat), 'keluarga') || str_contains(strtolower($currKat), 'adik') || str_contains(strtolower($currKat), 'saudara');
                    ?>
                    <select name="kategori_pendaftar" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none bg-white font-semibold text-slate-800">
                        <option value="Umum (Luar Bina Insan)" <?= !$isKeluargaVal ? 'selected' : '' ?>>🌐 Umum (Luar Bina Insan)</option>
                        <option value="Keluarga Bina Insan (Adik / Saudara)" <?= $isKeluargaVal ? 'selected' : '' ?>>👨‍👩‍👦 Keluarga Bina Insan (Adik / Saudara)</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Asal Sekolah Sebelumnya</label>
                    <input type="text" name="asal_sekolah" value="<?= e($item['asal_sekolah'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>
            </div>
        </div>

        <!-- 2. Biodata Calon Siswa -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 inline-flex items-center justify-center text-xs">2</span>
                Identitas Calon Siswa
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_lengkap" value="<?= e($item['nama_lengkap']) ?>" required 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Panggilan</label>
                    <input type="text" name="nama_panggilan" value="<?= e($item['nama_panggilan'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none bg-white">
                        <option value="L" <?= ($item['jenis_kelamin'] === 'L') ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($item['jenis_kelamin'] === 'P') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NISN</label>
                    <input type="text" name="nisn" value="<?= e($item['nisn'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NIK Siswa</label>
                    <input type="text" name="nik" value="<?= e($item['nik'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= e($item['tempat_lahir'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="edit_tanggal_lahir" value="<?= e($item['tanggal_lahir'] ?? '') ?>" 
                           onchange="hitungUmurEdit(this.value)"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Umur Siswa <span id="label_patokan_edit" class="text-[10px] font-normal text-slate-500">(Per <?= !empty($item['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($item['tanggal_patokan_umur']) : '1 Juli 2027' ?>)</span>
                        </label>
                        <span id="badge_umur_edit" class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">Otomatis</span>
                    </div>
                    <input type="text" name="umur" id="edit_umur" value="<?= e($item['umur'] ?? '') ?>" readonly 
                           placeholder="Otomatis terhitung..."
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 outline-none cursor-default">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Agama</label>
                    <select name="agama" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none bg-white">
                        <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha'] as $ag): ?>
                            <option value="<?= $ag ?>" <?= (($item['agama'] ?? '') === $ag) ? 'selected' : '' ?>><?= $ag ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Anak Ke-</label>
                    <input type="number" name="anak_ke" value="<?= e($item['anak_ke'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Saudara</label>
                    <input type="number" name="jumlah_saudara" value="<?= e($item['jumlah_saudara'] ?? '') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Calon Siswa</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500">
                </div>
            </div>
        </div>

        <!-- 3. Alamat & Kontak -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 inline-flex items-center justify-center text-xs">3</span>
                Alamat & Kontak
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Tempat Tinggal</label>
                    <textarea name="alamat" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none"><?= e($item['alamat'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">RT / RW</label>
                    <div class="flex gap-2">
                        <input type="text" name="rt" value="<?= e($item['rt'] ?? '') ?>" placeholder="RT" class="w-1/2 px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                        <input type="text" name="rw" value="<?= e($item['rw'] ?? '') ?>" placeholder="RW" class="w-1/2 px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelurahan</label>
                    <input type="text" name="kelurahan" value="<?= e($item['kelurahan'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan</label>
                    <input type="text" name="kecamatan" value="<?= e($item['kecamatan'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten</label>
                    <input type="text" name="kota" value="<?= e($item['kota'] ?? 'Palu') ?>" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No. HP Siswa / WA</label>
                    <input type="text" name="no_hp" value="<?= e($item['no_hp'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="<?= e($item['email'] ?? '') ?>" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
            </div>
        </div>

        <!-- 4. Orang Tua -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 inline-flex items-center justify-center text-xs">4</span>
                Data Orang Tua & Wali
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                    <p class="text-xs font-bold text-slate-800">Data Ayah Kandung</p>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Ayah</label>
                        <input type="text" name="nama_ayah" value="<?= e($item['nama_ayah'] ?? '') ?>" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" value="<?= e($item['pekerjaan_ayah'] ?? '') ?>" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">No. HP / WA Ayah</label>
                        <input type="text" name="no_hp_ayah" value="<?= e($item['no_hp_ayah'] ?? '') ?>" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                </div>

                <div class="space-y-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                    <p class="text-xs font-bold text-slate-800">Data Ibu Kandung</p>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Ibu</label>
                        <input type="text" name="nama_ibu" value="<?= e($item['nama_ibu'] ?? '') ?>" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" value="<?= e($item['pekerjaan_ibu'] ?? '') ?>" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">No. HP / WA Ibu</label>
                        <input type="text" name="no_hp_ibu" value="<?= e($item['no_hp_ibu'] ?? '') ?>" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Status & Administrasi -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-sky-100 text-sky-700 inline-flex items-center justify-center text-xs">5</span>
                Administrasi & Status
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Pendaftaran (Rp)</label>
                    <input type="number" name="biaya_pendaftaran" value="<?= (float)($item['biaya_pendaftaran'] ?? 0) ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-sky-500 outline-none font-bold text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Pembayaran</label>
                    <select name="status_pembayaran" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white font-semibold">
                        <?php foreach (['Belum Lunas', 'Lunas', 'Gratis'] as $sp): ?>
                            <option value="<?= $sp ?>" <?= ($item['status_pembayaran'] === $sp) ? 'selected' : '' ?>><?= $sp ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Berkas</label>
                    <select name="status_berkas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white">
                        <?php foreach (['Belum Lengkap', 'Lengkap', 'Perlu Perbaikan'] as $sb): ?>
                            <option value="<?= $sb ?>" <?= ($item['status_berkas'] === $sb) ? 'selected' : '' ?>><?= $sb ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Seleksi / Kelulusan</label>
                    <select name="status_kelulusan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white font-bold text-slate-800">
                        <?php foreach (['Menunggu Seleksi', 'Lolos Berkas', 'Lulus / Diterima', 'Cadangan', 'Tidak Lulus', 'Dibatalkan'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($item['status_kelulusan'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
                    <textarea name="catatan" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none"><?= e($item['catatan'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 p-4 bg-white rounded-2xl border border-primary-100/70 shadow-sm">
            <a href="<?= url('kelola-spmb/siswa-baru') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white text-xs font-bold shadow-md shadow-sky-600/20 transition-all transform hover:-translate-y-0.5">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
function getSelectedGroupPatokanEdit() {
    const groupSelect = document.getElementById('edit_group_id');
    if (!groupSelect) return '2027-07-01';
    const opt = groupSelect.options[groupSelect.selectedIndex];
    return opt?.getAttribute('data-patokan') || '2027-07-01';
}

function getSelectedGroupPatokanIndoEdit() {
    const groupSelect = document.getElementById('edit_group_id');
    if (!groupSelect) return '1 Juli 2027';
    const opt = groupSelect.options[groupSelect.selectedIndex];
    return opt?.getAttribute('data-patokan-indo') || '1 Juli 2027';
}

function onGroupChangeEdit() {
    const patokanIndo = getSelectedGroupPatokanIndoEdit();
    const lbl = document.getElementById('label_patokan_edit');
    if (lbl) lbl.innerText = '(Per ' + patokanIndo + ')';

    const tglLahirInput = document.getElementById('edit_tanggal_lahir');
    if (tglLahirInput && tglLahirInput.value) {
        hitungUmurEdit(tglLahirInput.value);
    }
}

function hitungUmurEdit(tanggalLahirStr) {
    const umurInput = document.getElementById('edit_umur');
    if (!umurInput) return;

    if (!tanggalLahirStr) {
        umurInput.value = '';
        return;
    }

    const tglParts = tanggalLahirStr.split('-');
    if (tglParts.length < 3) return;
    const tglLahir = new Date(parseInt(tglParts[0], 10), parseInt(tglParts[1], 10) - 1, parseInt(tglParts[2], 10));

    const patokanStr = getSelectedGroupPatokanEdit();
    const patokanParts = patokanStr.split('-');
    const patokan = new Date(parseInt(patokanParts[0], 10), parseInt(patokanParts[1], 10) - 1, parseInt(patokanParts[2], 10));

    if (isNaN(tglLahir.getTime()) || isNaN(patokan.getTime())) {
        umurInput.value = '';
        return;
    }

    if (tglLahir > patokan) {
        umurInput.value = 'Tanggal belum tiba';
        return;
    }

    let years = patokan.getFullYear() - tglLahir.getFullYear();
    let months = patokan.getMonth() - tglLahir.getMonth();
    let days = patokan.getDate() - tglLahir.getDate();

    if (days < 0) {
        months--;
    }
    if (months < 0) {
        years--;
        months += 12;
    }

    if (years < 0) {
        umurInput.value = 'Tanggal belum tiba';
        return;
    }

    let hasil = years + ' Tahun';
    if (months > 0) {
        hasil += ' ' + months + ' Bulan';
    }

    umurInput.value = hasil;
}

// Auto recalculate on initial load if age input is empty
document.addEventListener('DOMContentLoaded', function() {
    const tglLahir = document.getElementById('edit_tanggal_lahir')?.value;
    const umurVal = document.getElementById('edit_umur')?.value;
    if (tglLahir && (!umurVal || umurVal.trim() === '')) {
        hitungUmurEdit(tglLahir);
    }
});
</script>
