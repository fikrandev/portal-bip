<?php
/**
 * View Form Tambah Siswa Pindahan (Mutasi Masuk) SPMB
 * Portal BIP
 */
$activeGroup = $activeGroup ?? null;
$groups = $groups ?? [];
$autoNomor = $autoNomor ?? 'PNDH-' . date('Y') . '-0001';
?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb / Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= url('kelola-spmb') ?>" class="hover:text-blue-600">SPMB</a>
                <span>&bull;</span>
                <a href="<?= url('kelola-spmb/pindahan') ?>" class="hover:text-blue-600">Pindahan</a>
                <span>&bull;</span>
                <span class="text-slate-800 font-semibold">Formulir Mutasi Masuk</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Pendaftaran Siswa Pindahan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                Input data calon siswa transfer / pindahan masuk dari sekolah lain ke SIT Bina Insan Palu.
            </p>
        </div>
        <div>
            <a href="<?= url('kelola-spmb/pindahan') ?>" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                &larr; Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Active Group Notice -->
    <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-500/10 via-indigo-500/10 to-transparent border border-blue-500/20 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
            </div>
            <div>
                <p class="text-xs text-blue-900 font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                    Tahun Group SPMB Aktif: <span class="underline decoration-blue-500"><?= e($activeGroup['nama_group'] ?? 'SPMB 2026/2027') ?></span>
                </p>
                <p class="text-[11px] text-slate-600 mt-0.5">
                    Data siswa pindahan ini terhubung langsung ke group aktif (TA <?= e($activeGroup['tahun_ajaran'] ?? '2026/2027') ?>).
                </p>
            </div>
        </div>
        <a href="<?= url('kelola-spmb/pengaturan') ?>" class="text-xs font-bold text-blue-700 hover:text-blue-800 shrink-0 hover:underline">
            Pengaturan Group &rarr;
        </a>
    </div>

    <!-- Main Form -->
    <form action="<?= url('kelola-spmb/pindahan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>
        <input type="hidden" name="jenis_pendaftaran" value="Pindahan">

        <!-- 1. Data Pendaftaran & Kepindahan -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 inline-flex items-center justify-center text-xs">1</span>
                Informasi Kepindahan & Pendaftaran
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Group SPMB</label>
                    <select name="group_id" id="group_id" required onchange="onGroupChangePindahan()"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 outline-none bg-white font-semibold">
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g['id'] ?>" 
                                    data-biaya="<?= $g['biaya_pindahan'] ?? $g['biaya_formulir'] ?? 0 ?>"
                                    data-biaya-pindahan="<?= $g['biaya_pindahan'] ?? $g['biaya_formulir'] ?? 0 ?>"
                                    data-gelombang="<?= e($g['gelombang_default']) ?>"
                                    data-patokan="<?= e($g['tanggal_patokan_umur'] ?? '2027-07-01') ?>"
                                    data-patokan-indo="<?= SpmbModel::formatTglIndo($g['tanggal_patokan_umur'] ?? '2027-07-01') ?>"
                                    <?= ($g['is_active'] == 1 || ($activeGroup && $g['id'] == $activeGroup['id'])) ? 'selected' : '' ?>>
                                <?= e($g['nama_group']) ?> <?= ($g['is_active'] == 1) ? '(AKTIF)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nomor Pendaftaran Pindahan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nomor_pendaftaran" required value="<?= e($autoNomor) ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 font-mono font-bold text-blue-800 bg-blue-50/50 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Daftar</label>
                    <input type="date" name="tanggal_daftar" value="<?= date('Y-m-d') ?>" required
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Gelombang</label>
                    <input type="text" name="gelombang" id="gelombang_input" value="<?= e($activeGroup['gelombang_default'] ?? 'Gelombang 1') ?>" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <!-- Detail Kepindahan -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Sekolah Asal <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="asal_sekolah" required placeholder="Contoh: SD Negeri 2 Palu / SMP IT Bina Insani..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 outline-none font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NPSN Sekolah Asal</label>
                    <input type="text" name="npsn_asal" placeholder="Nomor Pokok Sekolah Nasional..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No. Surat Pindah / Rekomendasi</label>
                    <input type="text" name="nomor_surat_pindah" placeholder="Nomor surat resmi..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang Tujuan <span class="text-rose-500">*</span></label>
                    <select name="jenjang" required 
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white font-bold text-slate-800">
                        <option value="PAUD" selected>PAUD</option>
                        <option value="SD">SD</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Kelas Tujuan Masuk <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="kelas_tujuan" required placeholder="Contoh: Kelas 2, Kelas 5, Kelas 8..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 outline-none font-bold text-blue-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Pendaftar</label>
                    <select name="kategori_pendaftar" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white font-semibold text-slate-800">
                        <option value="Umum (Luar Bina Insan)" selected>🌐 Umum (Luar Bina Insan)</option>
                        <option value="Keluarga Bina Insan (Adik / Saudara)">👨‍👩‍👦 Keluarga Bina Insan (Adik / Saudara)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pindah</label>
                    <input type="text" name="alasan_pindah" placeholder="Contoh: Ikut kepindahan tugas orang tua ke Kota Palu..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
            </div>
        </div>

        <!-- 2. Biodata Calon Siswa -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 inline-flex items-center justify-center text-xs">2</span>
                Identitas Calon Siswa Pindahan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama Lengkap Siswa <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_lengkap" required placeholder="Nama lengkap siswa..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-blue-500 outline-none font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NISN Asal</label>
                    <input type="text" name="nisn" maxlength="20" placeholder="NISN aktif..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NIK Siswa</label>
                    <input type="text" name="nik" maxlength="20" placeholder="16 digit NIK..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" placeholder="Tempat lahir..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="pindahan_tanggal_lahir" onchange="hitungUmurPindahan(this.value)" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Umur Siswa <span id="label_patokan_pindahan" class="text-[10px] font-normal text-slate-500">(Per <?= !empty($activeGroup['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($activeGroup['tanggal_patokan_umur']) : '1 Juli 2027' ?>)</span>
                        </label>
                        <span id="badge_umur_pindahan" class="hidden text-[10px] font-bold text-blue-700 bg-blue-100 px-2 py-0.5 rounded-md">Otomatis</span>
                    </div>
                    <input type="text" name="umur" id="pindahan_umur" readonly placeholder="Otomatis terhitung..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 outline-none cursor-default">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Agama</label>
                    <select name="agama" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white">
                        <option value="Islam" selected>Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili Palu</label>
                    <textarea name="alamat" rows="2" placeholder="Alamat tinggal saat ini di Palu..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WhatsApp Siswa</label>
                    <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Foto Calon Siswa</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500">
                </div>
            </div>
        </div>

        <!-- 3. Orang Tua -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 inline-flex items-center justify-center text-xs">3</span>
                Data Orang Tua & Kontak
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                    <p class="text-xs font-bold text-slate-800">Data Ayah Kandung</p>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Ayah</label>
                        <input type="text" name="nama_ayah" placeholder="Nama ayah..." class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" placeholder="PNS, TNI/Polri, Swasta..." class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">No. HP / WA Ayah</label>
                        <input type="text" name="no_hp_ayah" placeholder="08xxxxxxxxxx" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                </div>

                <div class="space-y-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                    <p class="text-xs font-bold text-slate-800">Data Ibu Kandung</p>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Nama Ibu</label>
                        <input type="text" name="nama_ibu" placeholder="Nama ibu..." class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" placeholder="Pekerjaan ibu..." class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">No. HP / WA Ibu</label>
                        <input type="text" name="no_hp_ibu" placeholder="08xxxxxxxxxx" class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-200 bg-white outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Administrasi & Status -->
        <div class="bg-white p-6 rounded-2xl border border-primary-100/70 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 pb-2 border-b border-slate-100">
                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 inline-flex items-center justify-center text-xs">4</span>
                Administrasi & Status Awal
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Pembayaran <span class="text-rose-500">*</span></label>
                    <select name="jenis_pembayaran" id="create_jenis_pembayaran" onchange="onCreateJenisPembayaranChange()" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white font-semibold">
                        <option value="Tunai" selected>💵 Tunai</option>
                        <option value="Transfer">💳 Transfer</option>
                        <option value="Free">🎁 Free (Gratis)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Formulir Pindahan</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-xs font-bold text-slate-400">Rp</span>
                        <input type="text" id="create_biaya_display" readonly 
                               value="<?= number_format($activeGroup['biaya_pindahan'] ?? $activeGroup['biaya_formulir'] ?? 250000, 0, ',', '.') ?>" 
                               class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-100 font-mono font-bold text-slate-700 outline-none cursor-not-allowed select-none">
                    </div>
                    <input type="hidden" name="biaya_pendaftaran" id="create_biaya_hidden" value="<?= (int)($activeGroup['biaya_pindahan'] ?? $activeGroup['biaya_formulir'] ?? 250000) ?>">
                    <input type="hidden" name="status_pembayaran" id="create_status_bayar_hidden" value="Lunas">
                    <input type="hidden" name="metode_pembayaran" id="create_metode_bayar_hidden" value="Tunai">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Kelengkapan Berkas</label>
                    <select name="status_berkas" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white">
                        <option value="Belum Lengkap" selected>Belum Lengkap</option>
                        <option value="Lengkap">Lengkap</option>
                        <option value="Perlu Perbaikan">Perlu Perbaikan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Seleksi</label>
                    <select name="status_kelulusan" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none bg-white font-semibold">
                        <option value="Menunggu Seleksi" selected>Menunggu Seleksi</option>
                        <option value="Lolos Berkas">Lolos Berkas</option>
                        <option value="Lulus / Diterima">Lulus / Diterima</option>
                        <option value="Cadangan">Cadangan</option>
                        <option value="Tidak Lulus">Tidak Lulus</option>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan berkas surat pindah, raport asal, dll..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 outline-none"></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 p-4 bg-white rounded-2xl border border-primary-100/70 shadow-sm">
            <a href="<?= url('kelola-spmb/pindahan') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-bold shadow-md shadow-blue-600/20 transition-all transform hover:-translate-y-0.5">
                Simpan & Daftarkan Siswa Pindahan
            </button>
        </div>
    </form>
</div>

<script>
function getSelectedGroupPatokanPindahan() {
    const groupSelect = document.getElementById('group_id');
    if (!groupSelect) return '2027-07-01';
    const opt = groupSelect.options[groupSelect.selectedIndex];
    return opt?.getAttribute('data-patokan') || '2027-07-01';
}

function getSelectedGroupPatokanIndoPindahan() {
    const groupSelect = document.getElementById('group_id');
    if (!groupSelect) return '1 Juli 2027';
    const opt = groupSelect.options[groupSelect.selectedIndex];
    return opt?.getAttribute('data-patokan-indo') || '1 Juli 2027';
}

let currentGroupBiayaPindahan = <?= (int)($activeGroup['biaya_pindahan'] ?? $activeGroup['biaya_formulir'] ?? 250000) ?>;

function onCreateJenisPembayaranChange() {
    const jenisSelect = document.getElementById('create_jenis_pembayaran');
    const displayInput = document.getElementById('create_biaya_display');
    const hiddenBiaya = document.getElementById('create_biaya_hidden');
    const hiddenStatus = document.getElementById('create_status_bayar_hidden');
    const hiddenMetode = document.getElementById('create_metode_bayar_hidden');

    if (!jenisSelect || !displayInput || !hiddenBiaya) return;
    const val = jenisSelect.value;

    if (val === 'Free') {
        displayInput.value = '0';
        hiddenBiaya.value = 0;
        if (hiddenStatus) hiddenStatus.value = 'Gratis';
        if (hiddenMetode) hiddenMetode.value = 'Gratis';
    } else {
        displayInput.value = new Intl.NumberFormat('id-ID').format(currentGroupBiayaPindahan);
        hiddenBiaya.value = currentGroupBiayaPindahan;
        if (hiddenStatus) hiddenStatus.value = 'Lunas';
        if (hiddenMetode) hiddenMetode.value = val;
    }
}

function onGroupChangePindahan() {
    const opt = document.getElementById('group_id')?.selectedOptions[0];
    if (opt) {
        const gelombang = opt.getAttribute('data-gelombang');
        if (gelombang) document.getElementById('gelombang_input').value = gelombang;

        const biaya = opt.getAttribute('data-biaya-pindahan') || opt.getAttribute('data-biaya');
        if (biaya !== null) {
            currentGroupBiayaPindahan = parseFloat(biaya) || 0;
            onCreateJenisPembayaranChange();
        }
    }

    const patokanIndo = getSelectedGroupPatokanIndoPindahan();
    const lbl = document.getElementById('label_patokan_pindahan');
    if (lbl) lbl.innerText = '(Per ' + patokanIndo + ')';

    const tglLahirInput = document.getElementById('pindahan_tanggal_lahir');
    if (tglLahirInput && tglLahirInput.value) {
        hitungUmurPindahan(tglLahirInput.value);
    }
}

function hitungUmurPindahan(tanggalLahirStr) {
    const umurInput = document.getElementById('pindahan_umur');
    const badge = document.getElementById('badge_umur_pindahan');
    if (!umurInput) return;

    if (!tanggalLahirStr) {
        umurInput.value = '';
        if (badge) badge.classList.add('hidden');
        return;
    }

    const tglParts = tanggalLahirStr.split('-');
    if (tglParts.length < 3) return;
    const tglLahir = new Date(parseInt(tglParts[0], 10), parseInt(tglParts[1], 10) - 1, parseInt(tglParts[2], 10));

    const patokanStr = getSelectedGroupPatokanPindahan();
    const patokanParts = patokanStr.split('-');
    const patokan = new Date(parseInt(patokanParts[0], 10), parseInt(patokanParts[1], 10) - 1, parseInt(patokanParts[2], 10));

    if (isNaN(tglLahir.getTime()) || isNaN(patokan.getTime())) {
        umurInput.value = '';
        if (badge) badge.classList.add('hidden');
        return;
    }

    if (tglLahir > patokan) {
        umurInput.value = 'Tanggal belum tiba';
        if (badge) badge.classList.add('hidden');
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
        if (badge) badge.classList.add('hidden');
        return;
    }

    let hasil = years + ' Tahun';
    if (months > 0) {
        hasil += ' ' + months + ' Bulan';
    }

    umurInput.value = hasil;
    if (badge) {
        badge.innerText = hasil;
        badge.classList.remove('hidden');
    }
}
</script>
