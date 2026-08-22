<?php /** Create Pegawai Form */ ?>
<div class="max-w-5xl mx-auto">
    <form action="<?= url('kelola-pegawai/store') ?>" method="POST" enctype="multipart/form-data" id="form-create-pegawai">
        <?= CSRF::field() ?>
        
        <!-- Data Pribadi -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8 mb-6">
            <h2 class="text-lg font-bold text-primary-900 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                Data Pribadi
            </h2>
            
            <!-- Semua Input 1 Kolom Vertikal Rapi -->
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Foto Pegawai</label>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center" id="foto-preview-container">
                            <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="foto-input" name="foto" accept="image/png, image/jpeg, image/jpg" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                            <p class="text-xs text-slate-500 mt-1">Format: JPG, JPEG, PNG.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="<?= old('nama') ?>" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Gelar</label>
                    <input type="text" name="gelar" value="<?= old('gelar') ?>" placeholder="Contoh: S.Pd, M.Kom" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">NIY (Nomor Induk Yayasan)</label>
                    <input type="text" name="niy" value="<?= old('niy') ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">NIK KTP</label>
                    <input type="text" name="nik" value="<?= old('nik') ?>" placeholder="16 digit NIK" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- Field Baru: NPWP, Email, No WhatsApp -->
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">NPWP</label>
                    <input type="text" name="npwp" value="<?= old('npwp') ?>" placeholder="Nomor Pokok Wajib Pajak" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Email</label>
                    <input type="email" name="email" value="<?= old('email') ?>" placeholder="nama@email.com" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">No. WhatsApp</label>
                    <input type="text" name="no_wa" value="<?= old('no_wa') ?>" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="L" <?= old('jenis_kelamin') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= old('jenis_kelamin') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Status Menikah</label>
                    <select name="status_nikah" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="">Pilih</option>
                        <option value="Belum Menikah" <?= old('status_nikah') === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                        <option value="Menikah" <?= old('status_nikah') === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                        <option value="Cerai Hidup" <?= old('status_nikah') === 'Cerai Hidup' ? 'selected' : '' ?>>Cerai Hidup</option>
                        <option value="Cerai Mati" <?= old('status_nikah') === 'Cerai Mati' ? 'selected' : '' ?>>Cerai Mati</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= old('tempat_lahir') ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir') ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Ibu Kandung</label>
                    <input type="text" name="nama_ibu" value="<?= old('nama_ibu') ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">
                        Tanggal Masuk Kerja <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_masuk" value="<?= old('tanggal_masuk', date('Y-m-d')) ?>" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    <p class="text-xs text-slate-400 mt-1">Tanggal pertama kali mulai bekerja di yayasan / sekolah (dasar perhitungan masa kerja).</p>
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent">
                        <span class="text-sm font-medium text-primary-800">Pegawai Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Alamat KTP & Domisili -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8 mb-6">
            <h2 class="text-lg font-bold text-primary-900 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                Informasi Alamat
            </h2>
            
            <div class="grid grid-cols-1 gap-8">
                <!-- KTP -->
                <div>
                    <h3 class="text-sm font-bold text-primary-700 border-b border-primary-100 pb-2 mb-4">Sesuai KTP</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_ktp" rows="2" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm"><?= old('alamat_ktp') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kelurahan / Desa</label>
                            <input type="text" name="kel_ktp" value="<?= old('kel_ktp') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kecamatan</label>
                            <input type="text" name="kec_ktp" value="<?= old('kec_ktp') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kab_kota_ktp" value="<?= old('kab_kota_ktp') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                    </div>
                </div>

                <!-- Domisili -->
                <div>
                    <div class="flex items-center justify-between border-b border-primary-100 pb-2 mb-4">
                        <h3 class="text-sm font-bold text-primary-700">Domisili Sekarang</h3>
                        <button type="button" id="btn-copy-ktp" class="text-xs text-primary-600 hover:text-primary-800 font-medium">Sama dengan KTP</button>
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_domisili" id="alamat_domisili" rows="2" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm"><?= old('alamat_domisili') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kelurahan / Desa</label>
                            <input type="text" name="kel_domisili" id="kel_domisili" value="<?= old('kel_domisili') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kecamatan</label>
                            <input type="text" name="kec_domisili" id="kec_domisili" value="<?= old('kec_domisili') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kab_kota_domisili" id="kab_kota_domisili" value="<?= old('kab_kota_domisili') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kontak Darurat (2 Kontak) -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8 mb-6">
            <h2 class="text-lg font-bold text-primary-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                </svg>
                Kontak Darurat (Emergency Contact)
            </h2>
            <p class="text-xs text-slate-500 mb-6">Daftarkan 2 kontak keluarga/kerabat yang dapat dihubungi saat keadaan mendesak.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kontak Darurat 1 -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-primary-600 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                        Kontak Darurat 1
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="kontak_darurat_1_nama" value="<?= old('kontak_darurat_1_nama') ?>" placeholder="Nama kerabat/keluarga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hubungan</label>
                            <input type="text" name="kontak_darurat_1_hubungan" value="<?= old('kontak_darurat_1_hubungan') ?>" placeholder="Contoh: Suami / Istri / Ayah / Ibu / Kakak" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="kontak_darurat_1_no_hp" value="<?= old('kontak_darurat_1_no_hp') ?>" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                    </div>
                </div>

                <!-- Kontak Darurat 2 -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                        Kontak Darurat 2
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="kontak_darurat_2_nama" value="<?= old('kontak_darurat_2_nama') ?>" placeholder="Nama kerabat/keluarga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hubungan</label>
                            <input type="text" name="kontak_darurat_2_hubungan" value="<?= old('kontak_darurat_2_hubungan') ?>" placeholder="Contoh: Saudara Kandung / Paman / Kerabat" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="kontak_darurat_2_no_hp" value="<?= old('kontak_darurat_2_no_hp') ?>" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Susunan Anggota Keluarga -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-primary-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Susunan Anggota Keluarga
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Data pasangan (suami/istri), anak-anak, atau orang tua pegawai.</p>
                </div>
                <button type="button" id="btn-add-keluarga" class="px-4 py-2 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl text-xs font-bold transition-colors">
                    + Tambah Anggota Keluarga
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700 w-32">Hubungan</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700">Nama Lengkap</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700 w-20">L/P</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700">Tempat & Tgl Lahir</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700 w-24">Pendidikan</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700">Pekerjaan</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700 w-28">No. HP</th>
                            <th class="text-center py-2.5 px-2 font-bold text-slate-700 w-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="keluarga-list" class="divide-y divide-slate-100">
                        <!-- Default Blank Row -->
                        <tr class="hover:bg-slate-50/60">
                            <td class="p-2">
                                <select name="keluarga_hubungan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                    <option value="Suami">Suami</option>
                                    <option value="Istri">Istri</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Ayah">Ayah</option>
                                    <option value="Ibu">Ibu</option>
                                    <option value="Mertua">Mertua</option>
                                    <option value="Saudara Kandung">Saudara Kandung</option>
                                </select>
                            </td>
                            <td class="p-2"><input type="text" name="keluarga_nama[]" placeholder="Nama lengkap" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
                            <td class="p-2">
                                <select name="keluarga_jk[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                    <option value="L">L</option>
                                    <option value="P">P</option>
                                </select>
                            </td>
                            <td class="p-2">
                                <div class="grid grid-cols-2 gap-1">
                                    <input type="text" name="keluarga_tempat_lahir[]" placeholder="Tempat" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                    <input type="date" name="keluarga_tgl_lahir[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                </div>
                            </td>
                            <td class="p-2">
                                <select name="keluarga_pendidikan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
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
                            <td class="p-2"><input type="text" name="keluarga_pekerjaan[]" placeholder="Pekerjaan" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
                            <td class="p-2"><input type="text" name="keluarga_no_hp[]" placeholder="08xxx" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
                            <td class="p-2 text-center">
                                <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Keahlian & Keterampilan Pegawai (Skill) -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-primary-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                        </svg>
                        Keahlian & Keterampilan (Skill & Competencies)
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kompetensi khusus, keahlian IT, pedagogik, bahasa, dsb yang akan tercantum pada CV.</p>
                </div>
                <button type="button" id="btn-add-skill" class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-xl text-xs font-bold transition-colors">
                    + Tambah Keahlian / Skill
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700">Nama Skill / Keahlian</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700 w-44">Kategori</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700 w-32">Tingkat Penguasaan</th>
                            <th class="text-left py-2.5 px-2 font-bold text-slate-700">Keterangan / Portofolio Singkat</th>
                            <th class="text-center py-2.5 px-2 font-bold text-slate-700 w-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="skill-list" class="divide-y divide-slate-100">
                        <!-- Default Blank Row -->
                        <tr class="hover:bg-slate-50/60">
                            <td class="p-2"><input type="text" name="skill_nama[]" placeholder="Contoh: Kurikulum Merdeka / Desain Canva / Microsoft Excel" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs font-medium"></td>
                            <td class="p-2">
                                <select name="skill_kategori[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                    <option value="Pedagogik & Pembelajaran">Pedagogik & Pembelajaran</option>
                                    <option value="IT & Teknologi">IT & Teknologi</option>
                                    <option value="Bahasa & Komunikasi">Bahasa & Komunikasi</option>
                                    <option value="Kepemimpinan & Manajemen">Kepemimpinan & Manajemen</option>
                                    <option value="Keagamaan & Al-Qur'an">Keagamaan & Al-Qur'an</option>
                                    <option value="Seni & Olahraga">Seni & Olahraga</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </td>
                            <td class="p-2">
                                <select name="skill_tingkat[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs font-semibold text-indigo-700">
                                    <option value="Pemula">Pemula</option>
                                    <option value="Menengah" selected>Menengah</option>
                                    <option value="Mahir">Mahir</option>
                                    <option value="Ahli">Ahli / Pakar</option>
                                </select>
                            </td>
                            <td class="p-2"><input type="text" name="skill_deskripsi[]" placeholder="Catatan singkat / sertifikasi pendukung" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
                            <td class="p-2 text-center">
                                <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Pendidikan -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-primary-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>
                    Riwayat Pendidikan
                </h2>
                <button type="button" id="btn-add-pendidikan" class="px-4 py-2 bg-primary-50 text-primary-600 hover:bg-primary-100 rounded-lg text-sm font-semibold transition-colors">
                    + Tambah Riwayat
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="table-pendidikan">
                    <thead>
                        <tr class="border-b border-slate-200">
                            <th class="text-left py-2 font-semibold text-slate-600">Jenjang</th>
                            <th class="text-left py-2 font-semibold text-slate-600">Nama Institusi</th>
                            <th class="text-left py-2 font-semibold text-slate-600">Jurusan</th>
                            <th class="text-left py-2 font-semibold text-slate-600 w-24">Thn Lulus</th>
                            <th class="text-center py-2 font-semibold text-slate-600 w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pendidikan-list">
                        <!-- Baris Pertama (Default) -->
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-2">
                                <select name="pendidikan_jenjang[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm">
                                    <option value="">Pilih</option>
                                    <option value="SD">SD/Sederajat</option>
                                    <option value="SMP">SMP/Sederajat</option>
                                    <option value="SMA">SMA/Sederajat</option>
                                    <option value="D1">D1</option>
                                    <option value="D2">D2</option>
                                    <option value="D3">D3</option>
                                    <option value="D4">D4</option>
                                    <option value="S1">S1</option>
                                    <option value="S2">S2</option>
                                    <option value="S3">S3</option>
                                </select>
                            </td>
                            <td class="py-2 pr-2"><input type="text" name="pendidikan_institusi[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm"></td>
                            <td class="py-2 pr-2"><input type="text" name="pendidikan_jurusan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm"></td>
                            <td class="py-2 pr-2"><input type="text" name="pendidikan_tahun[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm text-center"></td>
                            <td class="py-2 text-center">
                                <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 transition-all duration-200">Simpan Data Pegawai</button>
            <a href="<?= url('kelola-pegawai') ?>" class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-full transition-all">Batal</a>
        </div>
    </form>
</div>

<script>
// Logic KTP ke Domisili
document.getElementById('btn-copy-ktp').addEventListener('click', function() {
    document.querySelector('textarea[name="alamat_domisili"]').value = document.querySelector('textarea[name="alamat_ktp"]').value;
    document.querySelector('input[name="kab_kota_domisili"]').value = document.querySelector('input[name="kab_kota_ktp"]').value;
    document.querySelector('input[name="kec_domisili"]').value = document.querySelector('input[name="kec_ktp"]').value;
    document.querySelector('input[name="kel_domisili"]').value = document.querySelector('input[name="kel_ktp"]').value;
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Tersalin!';
    btn.classList.add('text-emerald-500');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('text-emerald-500');
    }, 2000);
});

// Repeater Riwayat Pendidikan
document.getElementById('btn-add-pendidikan').addEventListener('click', function() {
    const tbody = document.getElementById('pendidikan-list');
    const tr = document.createElement('tr');
    tr.className = 'border-b border-slate-100';
    tr.innerHTML = `
        <td class="py-2 pr-2">
            <select name="pendidikan_jenjang[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm">
                <option value="">Pilih</option>
                <option value="SD">SD/Sederajat</option>
                <option value="SMP">SMP/Sederajat</option>
                <option value="SMA">SMA/Sederajat</option>
                <option value="D1">D1</option>
                <option value="D2">D2</option>
                <option value="D3">D3</option>
                <option value="D4">D4</option>
                <option value="S1">S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
            </select>
        </td>
        <td class="py-2 pr-2"><input type="text" name="pendidikan_institusi[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm"></td>
        <td class="py-2 pr-2"><input type="text" name="pendidikan_jurusan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm"></td>
        <td class="py-2 pr-2"><input type="text" name="pendidikan_tahun[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm text-center"></td>
        <td class="py-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
        </td>
    `;
    tbody.appendChild(tr);
});

// Repeater Anggota Keluarga
document.getElementById('btn-add-keluarga').addEventListener('click', function() {
    const tbody = document.getElementById('keluarga-list');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60';
    tr.innerHTML = `
        <td class="p-2">
            <select name="keluarga_hubungan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                <option value="Suami">Suami</option>
                <option value="Istri">Istri</option>
                <option value="Anak">Anak</option>
                <option value="Ayah">Ayah</option>
                <option value="Ibu">Ibu</option>
                <option value="Mertua">Mertua</option>
                <option value="Saudara Kandung">Saudara Kandung</option>
            </select>
        </td>
        <td class="p-2"><input type="text" name="keluarga_nama[]" placeholder="Nama lengkap" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
        <td class="p-2">
            <select name="keluarga_jk[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                <option value="L">L</option>
                <option value="P">P</option>
            </select>
        </td>
        <td class="p-2">
            <div class="grid grid-cols-2 gap-1">
                <input type="text" name="keluarga_tempat_lahir[]" placeholder="Tempat" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                <input type="date" name="keluarga_tgl_lahir[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
            </div>
        </td>
        <td class="p-2">
            <select name="keluarga_pendidikan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
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
        <td class="p-2"><input type="text" name="keluarga_pekerjaan[]" placeholder="Pekerjaan" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
        <td class="p-2"><input type="text" name="keluarga_no_hp[]" placeholder="08xxx" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
        <td class="p-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
});

// Repeater Keahlian / Skill
document.getElementById('btn-add-skill').addEventListener('click', function() {
    const tbody = document.getElementById('skill-list');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-slate-50/60';
    tr.innerHTML = `
        <td class="p-2"><input type="text" name="skill_nama[]" placeholder="Nama skill / keahlian" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs font-medium"></td>
        <td class="p-2">
            <select name="skill_kategori[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                <option value="Pedagogik & Pembelajaran">Pedagogik & Pembelajaran</option>
                <option value="IT & Teknologi">IT & Teknologi</option>
                <option value="Bahasa & Komunikasi">Bahasa & Komunikasi</option>
                <option value="Kepemimpinan & Manajemen">Kepemimpinan & Manajemen</option>
                <option value="Keagamaan & Al-Qur'an">Keagamaan & Al-Qur'an</option>
                <option value="Seni & Olahraga">Seni & Olahraga</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </td>
        <td class="p-2">
            <select name="skill_tingkat[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs font-semibold text-indigo-700">
                <option value="Pemula">Pemula</option>
                <option value="Menengah" selected>Menengah</option>
                <option value="Mahir">Mahir</option>
                <option value="Ahli">Ahli / Pakar</option>
            </select>
        </td>
        <td class="p-2"><input type="text" name="skill_deskripsi[]" placeholder="Catatan singkat" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
        <td class="p-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
});
</script>

<!-- Cropper Modal -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div id="cropper-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="font-bold text-slate-800">Sesuaikan Foto</h3>
            <button type="button" id="btn-close-cropper" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 bg-slate-100 flex justify-center items-center h-[400px]">
            <img id="cropper-image" src="" class="max-h-full max-w-full">
        </div>
        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white">
            <button type="button" id="btn-cancel-cropper" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
            <button type="button" id="btn-save-cropper" class="px-5 py-2.5 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-colors">Potong & Simpan</button>
        </div>
    </div>
</div>

<script>
let cropper;
const fotoInput = document.getElementById('foto-input');
const cropperModal = document.getElementById('cropper-modal');
const cropperImage = document.getElementById('cropper-image');
const fotoPreviewContainer = document.getElementById('foto-preview-container');

fotoInput.addEventListener('change', function(e) {
    const files = e.target.files;
    if (files && files.length > 0) {
        // Create an object URL and show modal
        const url = URL.createObjectURL(files[0]);
        cropperImage.src = url;
        cropperModal.classList.remove('hidden');
        
        // Initialize cropper after image is loaded
        if (cropper) { cropper.destroy(); }
        cropper = new Cropper(cropperImage, {
            aspectRatio: 1, // Square for profile picture
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

document.getElementById('btn-close-cropper').addEventListener('click', function() {
    fotoInput.value = ''; // Reset file input
    closeCropper();
});

document.getElementById('btn-cancel-cropper').addEventListener('click', function() {
    fotoInput.value = ''; // Reset file input
    closeCropper();
});

document.getElementById('btn-save-cropper').addEventListener('click', function() {
    if (!cropper) return;
    
    // Get cropped canvas
    const canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    // Update preview
    fotoPreviewContainer.innerHTML = `<img src="${canvas.toDataURL('image/jpeg', 0.9)}" class="w-full h-full object-cover rounded-full">`;
    
    // Convert canvas to Blob and replace the input file
    canvas.toBlob(function(blob) {
        const file = new File([blob], 'foto_cropped.jpg', { type: "image/jpeg", lastModified: new Date().getTime() });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fotoInput.files = dataTransfer.files;
        closeCropper();
    }, 'image/jpeg', 0.9);
});
</script>
