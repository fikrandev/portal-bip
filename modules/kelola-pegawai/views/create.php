<?php 
/** 
 * Create Pegawai Form - Modern Responsive Layout
 * Portal BIP - Bina Insan Palu
 */ 
?>
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-primary-900 via-primary-800 to-indigo-900 rounded-3xl p-6 sm:p-8 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-white text-2xl font-bold shadow-inner">
                ➕
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Tambah Data Pegawai Baru</h1>
                <p class="text-xs text-primary-200/90 mt-0.5">Lengkapi identitas, data kepegawaian, alamat, keluarga, dan pendidikan pegawai.</p>
            </div>
        </div>
        <a href="<?= url('kelola-pegawai') ?>" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl text-xs transition-colors self-start sm:self-center">
            ← Kembali
        </a>
    </div>

    <form action="<?= url('kelola-pegawai/store') ?>" method="POST" enctype="multipart/form-data" id="form-create-pegawai" class="space-y-6">
        <?= CSRF::field() ?>
        
        <!-- Hidden Real File Input for Cropper -->
        <input type="file" id="foto-input" name="foto" accept="image/png, image/jpeg, image/jpg" class="hidden">

        <!-- 1. Data Pribadi & Kontak -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-sm font-bold">1</span>
                        Identitas Pribadi & Kontak
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Informasi kependudukan dan kontak komunikasi utama pegawai.</p>
                </div>

                <!-- Foto Profile Trigger -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center flex-shrink-0" id="foto-preview-container">
                        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <label for="foto-input" class="px-3 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold rounded-lg cursor-pointer transition-colors">
                        Unggah Foto
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- Nama Lengkap -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="<?= old('nama') ?>" required placeholder="Nama lengkap tanpa gelar" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                </div>

                <!-- Gelar -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Gelar Akademik</label>
                    <input type="text" name="gelar" value="<?= old('gelar') ?>" placeholder="Contoh: S.Pd, M.Kom" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                </div>

                <!-- NIY -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIY (No. Induk Yayasan)</label>
                    <input type="text" name="niy" value="<?= old('niy') ?>" placeholder="Nomor Induk Yayasan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                </div>

                <!-- NIK -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIK (KTP 16 Digit)</label>
                    <input type="text" name="nik" maxlength="16" value="<?= old('nik') ?>" placeholder="16 digit NIK" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all font-mono">
                </div>

                <!-- NPWP -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NPWP</label>
                    <input type="text" name="npwp" value="<?= old('npwp') ?>" placeholder="Nomor Pokok Wajib Pajak" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all font-mono">
                </div>

                <!-- No WA -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp / HP</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-xs font-bold">📱</span>
                        <input type="text" name="no_wa" value="<?= old('no_wa') ?>" placeholder="08xxxxxxxxxx" class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                    </div>
                </div>

                <!-- Email -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Pribadi / Dinas</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-xs font-bold">✉️</span>
                        <input type="email" name="email" value="<?= old('email') ?>" placeholder="nama@email.com" class="w-full pl-9 pr-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                    </div>
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                        <option value="L" <?= old('jenis_kelamin') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= old('jenis_kelamin') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <!-- Status Menikah -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Pernikahan</label>
                    <select name="status_nikah" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                        <option value="">-- Pilih Status --</option>
                        <option value="Belum Menikah" <?= old('status_nikah') === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                        <option value="Menikah" <?= old('status_nikah') === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                        <option value="Cerai Hidup" <?= old('status_nikah') === 'Cerai Hidup' ? 'selected' : '' ?>>Cerai Hidup</option>
                        <option value="Cerai Mati" <?= old('status_nikah') === 'Cerai Mati' ? 'selected' : '' ?>>Cerai Mati</option>
                    </select>
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= old('tempat_lahir') ?>" placeholder="Kota / Kabupaten" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                </div>

                <!-- Nama Ibu Kandung -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Ibu Kandung</label>
                    <input type="text" name="nama_ibu" value="<?= old('nama_ibu') ?>" placeholder="Nama lengkap ibu" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                </div>
            </div>
        </div>

        <!-- 2. Data Kepegawaian & Penugasan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="border-b border-slate-100 pb-4 mb-6">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">2</span>
                    Data Kepegawaian & Penugasan
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Penempatan unit tugas, posisi jabatan, status kerja, dan tanggal masuk kerja.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- Unit Tugas -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Unit Tugas / Sekolah <span class="text-rose-500">*</span></label>
                    <select name="unit_tugas" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                        <option value="">-- Pilih Unit Tugas --</option>
                        <?php 
                        $defaultUnits = ['PAUD', 'SD', 'SMP', 'SMA', 'Yayasan'];
                        $units = !empty($unitList) ? array_column($unitList, 'nama') : $defaultUnits;
                        foreach ($units as $u): ?>
                            <option value="<?= e($u) ?>" <?= old('unit_tugas') === $u ? 'selected' : '' ?>><?= e($u) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jabatan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jabatan / Posisi <span class="text-rose-500">*</span></label>
                    <select name="jabatan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                        <option value="">-- Pilih Jabatan --</option>
                        <?php 
                        $defaultJabs = ['Wali Kelas', 'Guru Mapel', 'Wakakum', 'Kepala Sekolah', 'Kepala Divisi IT', 'Staff Tata Usaha', 'Bendahara', 'Security', 'Cleaning Service'];
                        $jabs = !empty($jabatanList) ? array_column($jabatanList, 'nama') : $defaultJabs;
                        foreach ($jabs as $j): ?>
                            <option value="<?= e($j) ?>" <?= old('jabatan') === $j ? 'selected' : '' ?>><?= e($j) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Pegawai -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Status Pegawai <span class="text-rose-500">*</span>
                    </label>
                    <select name="status_pegawai" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                        <option value="Tetap" <?= old('status_pegawai', 'Tetap') === 'Tetap' ? 'selected' : '' ?>>🟢 Tetap</option>
                        <option value="Kontrak" <?= old('status_pegawai') === 'Kontrak' ? 'selected' : '' ?>>🟡 Kontrak</option>
                        <option value="Training" <?= old('status_pegawai') === 'Training' ? 'selected' : '' ?>>🔵 Training</option>
                    </select>
                </div>

                <!-- Jenis Pegawai -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Pegawai</label>
                    <select name="jenis_pegawai" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                        <option value="">-- Pilih Jenis Pegawai --</option>
                        <?php 
                        $defaultJenis = ['Guru', 'Support System', 'Tenaga Kependidikan'];
                        $jns = !empty($jenisPegawaiList) ? array_column($jenisPegawaiList, 'nama') : $defaultJenis;
                        foreach ($jns as $jn): ?>
                            <option value="<?= e($jn) ?>" <?= old('jenis_pegawai') === $jn ? 'selected' : '' ?>><?= e($jn) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Dapodik -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Dapodik</label>
                    <select name="status_dapodik" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                        <option value="Belum Terdaftar" <?= old('status_dapodik') === 'Belum Terdaftar' ? 'selected' : '' ?>>Belum Terdaftar</option>
                        <option value="Sudah Terdaftar" <?= old('status_dapodik') === 'Sudah Terdaftar' ? 'selected' : '' ?>>Sudah Terdaftar</option>
                    </select>
                </div>

                <!-- Tanggal Masuk Kerja -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Masuk Kerja <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_masuk" value="<?= old('tanggal_masuk', date('Y-m-d')) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all">
                </div>

                <!-- Status Aktif Checkbox -->
                <div class="lg:col-span-3 pt-2">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-bold text-slate-900">Status Keaktifan Pegawai</span>
                            <p class="text-xs text-slate-500">Pegawai baru langsung berstatus aktif dan dapat mengakses sistem sesuai hak akses.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Alamat & Kontak Darurat -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-sm font-bold">3</span>
                    Alamat & Kontak Darurat
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Informasi tempat tinggal dan nomor darurat yang dapat dihubungi.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Alamat KTP -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-200 flex items-center gap-2">
                        <span>🪪 Alamat Sesuai KTP</span>
                    </h3>
                    <div class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_ktp" rows="2" placeholder="Nama jalan, RT/RW, No..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all"><?= old('alamat_ktp') ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Kelurahan / Desa</label>
                                <input type="text" name="kel_ktp" value="<?= old('kel_ktp') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Kecamatan</label>
                                <input type="text" name="kec_ktp" value="<?= old('kec_ktp') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kab_kota_ktp" value="<?= old('kab_kota_ktp') ?>" placeholder="Contoh: Kota Palu" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                        </div>
                    </div>
                </div>

                <!-- Domisili Sekarang -->
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="flex items-center justify-between pb-2 mb-4 border-b border-slate-200">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                            <span>🏠 Domisili Sekarang</span>
                        </h3>
                        <button type="button" id="btn-copy-ktp" class="px-2.5 py-1 bg-primary-100 hover:bg-primary-200 text-primary-800 font-bold rounded-lg text-[11px] transition-all flex items-center gap-1">
                            <span>📋 Salin dari KTP</span>
                        </button>
                    </div>
                    <div class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_domisili" id="alamat_domisili" rows="2" placeholder="Nama jalan, RT/RW, No..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all"><?= old('alamat_domisili') ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Kelurahan / Desa</label>
                                <input type="text" name="kel_domisili" id="kel_domisili" value="<?= old('kel_domisili') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1">Kecamatan</label>
                                <input type="text" name="kec_domisili" id="kec_domisili" value="<?= old('kec_domisili') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kab_kota_domisili" id="kab_kota_domisili" value="<?= old('kab_kota_domisili') ?>" placeholder="Contoh: Kota Palu" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kontak Darurat 1 & 2 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div class="p-5 rounded-2xl bg-amber-50/40 border border-amber-200/70">
                    <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                        Kontak Darurat Utama
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="kontak_darurat_1_nama" value="<?= old('kontak_darurat_1_nama') ?>" placeholder="Nama kerabat/keluarga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-amber-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hubungan</label>
                            <input type="text" name="kontak_darurat_1_hubungan" value="<?= old('kontak_darurat_1_hubungan') ?>" placeholder="Contoh: Suami / Istri / Ayah / Ibu" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-amber-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="kontak_darurat_1_no_hp" value="<?= old('kontak_darurat_1_no_hp') ?>" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-amber-500 transition-all">
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-indigo-50/40 border border-indigo-200/70">
                    <h3 class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-indigo-500 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                        Kontak Darurat Cadangan
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="kontak_darurat_2_nama" value="<?= old('kontak_darurat_2_nama') ?>" placeholder="Nama kerabat/keluarga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hubungan</label>
                            <input type="text" name="kontak_darurat_2_hubungan" value="<?= old('kontak_darurat_2_hubungan') ?>" placeholder="Contoh: Saudara Kandung / Kerabat" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="kontak_darurat_2_no_hp" value="<?= old('kontak_darurat_2_no_hp') ?>" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Susunan Keluarga -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">4</span>
                        Susunan Anggota Keluarga
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Data pasangan (suami/istri), anak, atau orang tua pegawai.</p>
                </div>
                <button type="button" id="btn-add-keluarga" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 self-start">
                    <span>+ Tambah Anggota</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-700 font-bold">
                            <th class="text-left py-3 px-2 w-32">Hubungan</th>
                            <th class="text-left py-3 px-2 min-w-[140px]">Nama Lengkap</th>
                            <th class="text-left py-3 px-2 w-20">L/P</th>
                            <th class="text-left py-3 px-2 min-w-[200px]">Tempat & Tanggal Lahir</th>
                            <th class="text-left py-3 px-2 w-28">Pendidikan</th>
                            <th class="text-left py-3 px-2 min-w-[120px]">Pekerjaan</th>
                            <th class="text-left py-3 px-2 min-w-[110px]">No. HP</th>
                            <th class="text-center py-3 px-2 w-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="keluarga-list" class="divide-y divide-slate-100">
                        <tr class="keluarga-row hover:bg-slate-50/60">
                            <td class="p-2">
                                <select name="keluarga_hubungan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="Suami">Suami</option>
                                    <option value="Istri">Istri</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Ayah">Ayah</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Mertua">Mertua</option>
                                    <option value="Saudara Kandung">Saudara Kandung</option>
                                </select>
                            </td>
                            <td class="p-2"><input type="text" name="keluarga_nama[]" placeholder="Nama lengkap" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
                            <td class="p-2">
                                <select name="keluarga_jk[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="L">L</option>
                                    <option value="P">P</option>
                                </select>
                            </td>
                            <td class="p-2">
                                <div class="grid grid-cols-2 gap-1.5">
                                    <input type="text" name="keluarga_tempat_lahir[]" placeholder="Tempat" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <input type="date" name="keluarga_tgl_lahir[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                </div>
                            </td>
                            <td class="p-2">
                                <select name="keluarga_pendidikan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="">-</option>
                                    <option value="Belum Sekolah">Belum Sekolah</option>
                                    <option value="SD">SD</option>
                                    <option value="SMP">SMP</option>
                                    <option value="SMA/SMK">SMA/SMK</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                </select>
                            </td>
                            <td class="p-2"><input type="text" name="keluarga_pekerjaan[]" placeholder="Pekerjaan" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
                            <td class="p-2"><input type="text" name="keluarga_no_hp[]" placeholder="08xxx" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
                            <td class="p-2 text-center">
                                <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Keahlian & Skill -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">5</span>
                        Keahlian & Keterampilan (Skill & Competencies)
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kompetensi khusus, IT, bahasa, atau keagamaan yang dimiliki pegawai.</p>
                </div>
                <button type="button" id="btn-add-skill" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 self-start">
                    <span>+ Tambah Keahlian</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-700 font-bold">
                            <th class="text-left py-3 px-2 min-w-[160px]">Nama Skill / Keahlian</th>
                            <th class="text-left py-3 px-2 w-48">Kategori</th>
                            <th class="text-left py-3 px-2 w-36">Tingkat Penguasaan</th>
                            <th class="text-left py-3 px-2 min-w-[200px]">Keterangan / Portofolio Singkat</th>
                            <th class="text-center py-3 px-2 w-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="skill-list" class="divide-y divide-slate-100">
                        <tr class="skill-row hover:bg-slate-50/60">
                            <td class="p-2"><input type="text" name="skill_nama[]" placeholder="Contoh: Kurikulum Merdeka / Desain Grafis / Excel" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
                            <td class="p-2">
                                <select name="skill_kategori[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                    <option value="Pedagogik & Pembelajaran">Pedagogik & Pembelajaran</option>
                                    <option value="IT & Teknologi" selected>IT & Teknologi</option>
                                    <option value="Bahasa & Komunikasi">Bahasa & Komunikasi</option>
                                    <option value="Kepemimpinan & Manajemen">Kepemimpinan & Manajemen</option>
                                    <option value="Keagamaan & Al-Qur'an">Keagamaan & Al-Qur'an</option>
                                    <option value="Seni & Olahraga">Seni & Olahraga</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </td>
                            <td class="p-2">
                                <select name="skill_tingkat[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-indigo-700">
                                    <option value="Pemula">Pemula</option>
                                    <option value="Menengah" selected>Menengah</option>
                                    <option value="Mahir">Mahir</option>
                                    <option value="Ahli">Ahli / Pakar</option>
                                </select>
                            </td>
                            <td class="p-2"><input type="text" name="skill_deskripsi[]" placeholder="Catatan sertifikasi / portofolio" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
                            <td class="p-2 text-center">
                                <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. Riwayat Pendidikan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm font-bold">6</span>
                        Riwayat Pendidikan Formal
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Jenjang pendidikan, sekolah/universitas, jurusan, dan tahun kelulusan.</p>
                </div>
                <button type="button" id="btn-add-pendidikan" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1.5 self-start">
                    <span>+ Tambah Pendidikan</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-700 font-bold">
                            <th class="text-left py-3 px-2 w-36">Jenjang</th>
                            <th class="text-left py-3 px-2 min-w-[200px]">Nama Institusi / Sekolah / Kampus</th>
                            <th class="text-left py-3 px-2 min-w-[160px]">Jurusan / Program Studi</th>
                            <th class="text-center py-3 px-2 w-28">Tahun Lulus</th>
                            <th class="text-center py-3 px-2 w-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pendidikan-list" class="divide-y divide-slate-100">
                        <tr class="pendidikan-row hover:bg-slate-50/60">
                            <td class="p-2">
                                <select name="pendidikan_jenjang[]" class="w-full px-2.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium">
                                    <option value="">Pilih Jenjang</option>
                                    <option value="SD">SD/Sederajat</option>
                                    <option value="SMP">SMP/Sederajat</option>
                                    <option value="SMA">SMA/SMK/Sederajat</option>
                                    <option value="D1">D1</option>
                                    <option value="D2">D2</option>
                                    <option value="D3">D3</option>
                                    <option value="D4">D4</option>
                                    <option value="S1" selected>S1 (Sarjana)</option>
                                    <option value="S2">S2 (Magister)</option>
                                    <option value="S3">S3 (Doktor)</option>
                                </select>
                            </td>
                            <td class="p-2"><input type="text" name="pendidikan_institusi[]" placeholder="Nama Universitas / Sekolah" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
                            <td class="p-2"><input type="text" name="pendidikan_jurusan[]" placeholder="Contoh: Pendidikan Bahasa Inggris" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs"></td>
                            <td class="p-2"><input type="text" name="pendidikan_tahun[]" placeholder="2020" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-center font-mono font-bold"></td>
                            <td class="p-2 text-center">
                                <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="<?= url('kelola-pegawai') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors">
                Batal
            </a>
            <button type="submit" class="px-8 py-2.5 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-primary-600/25 transition-all">
                💾 Simpan Data Pegawai Baru
            </button>
        </div>
    </form>
</div>

<!-- ================= CROPPER JS MODAL ================= -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div id="cropper-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
    <div class="bg-white rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-sm">Sesuaikan Posisi & Ukuran Foto</h3>
            <button type="button" onclick="closeCropper()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 bg-slate-100 flex justify-center items-center h-[380px]">
            <img id="cropper-image" src="" class="max-h-full max-w-full">
        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white">
            <button type="button" onclick="closeCropper()" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
            <button type="button" id="btn-save-cropper" class="px-5 py-2.5 text-xs font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors shadow-md">Terapkan Foto</button>
        </div>
    </div>
</div>

<script>
// Logic Salin KTP ke Domisili
document.getElementById('btn-copy-ktp')?.addEventListener('click', function() {
    const alamatKtp = document.querySelector('textarea[name="alamat_ktp"]')?.value || '';
    const kelKtp = document.querySelector('input[name="kel_ktp"]')?.value || '';
    const kecKtp = document.querySelector('input[name="kec_ktp"]')?.value || '';
    const kabKotaKtp = document.querySelector('input[name="kab_kota_ktp"]')?.value || '';

    const domAlamat = document.getElementById('alamat_domisili');
    const domKel = document.getElementById('kel_domisili');
    const domKec = document.getElementById('kec_domisili');
    const domKab = document.getElementById('kab_kota_domisili');

    if (domAlamat) domAlamat.value = alamatKtp;
    if (domKel) domKel.value = kelKtp;
    if (domKec) domKec.value = kecKtp;
    if (domKab) domKab.value = kabKotaKtp;

    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '✅ Berhasil Disalin!';
    setTimeout(() => { btn.innerHTML = originalText; }, 2000);
});

// Repeater Pendidikan
document.getElementById('btn-add-pendidikan')?.addEventListener('click', function() {
    const tbody = document.getElementById('pendidikan-list');
    const tr = document.createElement('tr');
    tr.className = 'pendidikan-row hover:bg-slate-50/60';
    tr.innerHTML = `
        <td class="p-2">
            <select name="pendidikan_jenjang[]" class="w-full px-2.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium">
                <option value="">Pilih</option>
                <option value="SD">SD</option>
                <option value="SMP">SMP</option>
                <option value="SMA">SMA</option>
                <option value="D1">D1</option>
                <option value="D2">D2</option>
                <option value="D3">D3</option>
                <option value="D4">D4</option>
                <option value="S1" selected>S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
            </select>
        </td>
        <td class="p-2"><input type="text" name="pendidikan_institusi[]" placeholder="Nama Universitas / Sekolah" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
        <td class="p-2"><input type="text" name="pendidikan_jurusan[]" placeholder="Jurusan" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs"></td>
        <td class="p-2"><input type="text" name="pendidikan_tahun[]" placeholder="2024" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-center font-mono font-bold"></td>
        <td class="p-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
});

// Repeater Keluarga
document.getElementById('btn-add-keluarga')?.addEventListener('click', function() {
    const tbody = document.getElementById('keluarga-list');
    const tr = document.createElement('tr');
    tr.className = 'keluarga-row hover:bg-slate-50/60';
    tr.innerHTML = `
        <td class="p-2">
            <select name="keluarga_hubungan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                <option value="Suami">Suami</option>
                <option value="Istri">Istri</option>
                <option value="Anak" selected>Anak</option>
                <option value="Ayah">Ayah</option>
                <option value="Ibu">Ibu</option>
                <option value="Mertua">Mertua</option>
                <option value="Saudara Kandung">Saudara Kandung</option>
            </select>
        </td>
        <td class="p-2"><input type="text" name="keluarga_nama[]" placeholder="Nama lengkap" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
        <td class="p-2">
            <select name="keluarga_jk[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                <option value="L">L</option>
                <option value="P">P</option>
            </select>
        </td>
        <td class="p-2">
            <div class="grid grid-cols-2 gap-1.5">
                <input type="text" name="keluarga_tempat_lahir[]" placeholder="Tempat" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                <input type="date" name="keluarga_tgl_lahir[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
            </div>
        </td>
        <td class="p-2">
            <select name="keluarga_pendidikan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                <option value="">-</option>
                <option value="Belum Sekolah">Belum Sekolah</option>
                <option value="SD">SD</option>
                <option value="SMP">SMP</option>
                <option value="SMA/SMK">SMA/SMK</option>
                <option value="Diploma">Diploma</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
            </select>
        </td>
        <td class="p-2"><input type="text" name="keluarga_pekerjaan[]" placeholder="Pekerjaan" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
        <td class="p-2"><input type="text" name="keluarga_no_hp[]" placeholder="08xxx" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
        <td class="p-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
});

// Repeater Skill
document.getElementById('btn-add-skill')?.addEventListener('click', function() {
    const tbody = document.getElementById('skill-list');
    const tr = document.createElement('tr');
    tr.className = 'skill-row hover:bg-slate-50/60';
    tr.innerHTML = `
        <td class="p-2"><input type="text" name="skill_nama[]" placeholder="Nama skill / kompetensi" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
        <td class="p-2">
            <select name="skill_kategori[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                <option value="Pedagogik & Pembelajaran">Pedagogik & Pembelajaran</option>
                <option value="IT & Teknologi" selected>IT & Teknologi</option>
                <option value="Bahasa & Komunikasi">Bahasa & Komunikasi</option>
                <option value="Kepemimpinan & Manajemen">Kepemimpinan & Manajemen</option>
                <option value="Keagamaan & Al-Qur'an">Keagamaan & Al-Qur'an</option>
                <option value="Seni & Olahraga">Seni & Olahraga</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </td>
        <td class="p-2">
            <select name="skill_tingkat[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-indigo-700">
                <option value="Pemula">Pemula</option>
                <option value="Menengah" selected>Menengah</option>
                <option value="Mahir">Mahir</option>
                <option value="Ahli">Ahli / Pakar</option>
            </select>
        </td>
        <td class="p-2"><input type="text" name="skill_deskripsi[]" placeholder="Catatan singkat / sertifikasi" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
        <td class="p-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
});

// Photo Cropper
let cropper = null;
const fotoInput = document.getElementById('foto-input');
const cropperModal = document.getElementById('cropper-modal');
const cropperImage = document.getElementById('cropper-image');
const previewContainer = document.getElementById('foto-preview-container');

fotoInput?.addEventListener('change', function(e) {
    const files = e.target.files;
    if (files && files.length > 0) {
        const url = URL.createObjectURL(files[0]);
        cropperImage.src = url;
        cropperModal.classList.remove('hidden');

        if (cropper) { cropper.destroy(); }
        cropper = new Cropper(cropperImage, {
            aspectRatio: 1,
            viewMode: 2,
            dragMode: 'move',
            autoCropArea: 0.9,
            restore: false,
            guides: true,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
        });
    }
});

function closeCropper() {
    cropperModal.classList.add('hidden');
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

document.getElementById('btn-save-cropper')?.addEventListener('click', function() {
    if (!cropper) return;
    
    const canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    if (previewContainer) {
        previewContainer.innerHTML = `<img src="${canvas.toDataURL('image/jpeg', 0.9)}" class="w-full h-full object-cover rounded-xl">`;
    }
    
    canvas.toBlob(function(blob) {
        const file = new File([blob], 'pegawai_photo.jpg', { type: "image/jpeg", lastModified: new Date().getTime() });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fotoInput.files = dataTransfer.files;
        closeCropper();
    }, 'image/jpeg', 0.9);
});
</script>
