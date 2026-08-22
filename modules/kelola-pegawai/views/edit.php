<?php /** Edit Pegawai Form */ ?>
<div class="max-w-5xl mx-auto">
    <form action="<?= url('kelola-pegawai/update/' . $pegawai['id']) ?>" method="POST" enctype="multipart/form-data" id="form-edit-pegawai">
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
                            <?php if(!empty($pegawai['foto'])): ?>
                                <img src="<?= url(ltrim($pegawai['foto'], '/')) ?>" alt="Foto" class="w-full h-full object-cover">
                            <?php else: ?>
                                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="foto-input" name="foto" accept="image/png, image/jpeg, image/jpg" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                            <p class="text-xs text-slate-500 mt-1">Biarkan kosong jika tidak ingin mengubah foto. Format: JPG, JPEG, PNG.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama" value="<?= e(old('nama', $pegawai['nama'])) ?>" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Gelar</label>
                    <input type="text" name="gelar" value="<?= e(old('gelar', $pegawai['gelar'] ?? '')) ?>" placeholder="Contoh: S.Pd, M.Kom" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">NIY (Nomor Induk Yayasan)</label>
                    <input type="text" name="niy" value="<?= e(old('niy', $pegawai['niy'] ?? '')) ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">NIK KTP</label>
                    <input type="text" name="nik" value="<?= e(old('nik', $pegawai['nik'] ?? '')) ?>" placeholder="16 digit NIK" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- Field Baru: NPWP, Email, No WhatsApp -->
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">NPWP</label>
                    <input type="text" name="npwp" value="<?= e(old('npwp', $pegawai['npwp'] ?? '')) ?>" placeholder="Nomor Pokok Wajib Pajak" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Email</label>
                    <input type="email" name="email" value="<?= e(old('email', $pegawai['email'] ?? '')) ?>" placeholder="nama@email.com" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">No. WhatsApp</label>
                    <input type="text" name="no_wa" value="<?= e(old('no_wa', $pegawai['no_wa'] ?? '')) ?>" placeholder="08xxxxxxxxxx" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="L" <?= old('jenis_kelamin', $pegawai['jenis_kelamin']) === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= old('jenis_kelamin', $pegawai['jenis_kelamin']) === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Status Menikah</label>
                    <select name="status_nikah" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="">Pilih</option>
                        <option value="Belum Menikah" <?= old('status_nikah', $pegawai['status_nikah']) === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                        <option value="Menikah" <?= old('status_nikah', $pegawai['status_nikah']) === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                        <option value="Cerai Hidup" <?= old('status_nikah', $pegawai['status_nikah']) === 'Cerai Hidup' ? 'selected' : '' ?>>Cerai Hidup</option>
                        <option value="Cerai Mati" <?= old('status_nikah', $pegawai['status_nikah']) === 'Cerai Mati' ? 'selected' : '' ?>>Cerai Mati</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="<?= e(old('tempat_lahir', $pegawai['tempat_lahir'] ?? '')) ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir', $pegawai['tanggal_lahir'] ?? '') ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Ibu Kandung</label>
                    <input type="text" name="nama_ibu" value="<?= e(old('nama_ibu', $pegawai['nama_ibu'] ?? '')) ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">
                        Tanggal Masuk Kerja <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_masuk" value="<?= old('tanggal_masuk', $pegawai['tanggal_masuk'] ?? ($pegawai['tmt'] ?? '')) ?>" required class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    <p class="text-xs text-slate-400 mt-1">Tanggal pertama kali mulai bekerja di yayasan / sekolah (dasar perhitungan masa kerja).</p>
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?= old('is_active', $pegawai['is_active']) ? 'checked' : '' ?> class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent">
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
                            <textarea name="alamat_ktp" rows="2" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm"><?= e(old('alamat_ktp', $pegawai['alamat_ktp'] ?? '')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kelurahan / Desa</label>
                            <input type="text" name="kel_ktp" value="<?= e(old('kel_ktp', $pegawai['kel_ktp'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kecamatan</label>
                            <input type="text" name="kec_ktp" value="<?= e(old('kec_ktp', $pegawai['kec_ktp'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kab_kota_ktp" value="<?= e(old('kab_kota_ktp', $pegawai['kab_kota_ktp'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
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
                            <textarea name="alamat_domisili" id="alamat_domisili" rows="2" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm"><?= old('alamat_domisili', $pegawai['alamat_domisili'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kelurahan / Desa</label>
                            <input type="text" name="kel_domisili" id="kel_domisili" value="<?= old('kel_domisili', $pegawai['kel_domisili'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kecamatan</label>
                            <input type="text" name="kec_domisili" id="kec_domisili" value="<?= old('kec_domisili', $pegawai['kec_domisili'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kab_kota_domisili" id="kab_kota_domisili" value="<?= old('kab_kota_domisili', $pegawai['kab_kota_domisili'] ?? '') ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
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
                            <input type="text" name="kontak_darurat_1_nama" value="<?= e(old('kontak_darurat_1_nama', $pegawai['kontak_darurat_1_nama'] ?? '')) ?>" placeholder="Nama kerabat/keluarga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hubungan</label>
                            <input type="text" name="kontak_darurat_1_hubungan" value="<?= e(old('kontak_darurat_1_hubungan', $pegawai['kontak_darurat_1_hubungan'] ?? '')) ?>" placeholder="Contoh: Suami / Istri / Ayah / Ibu / Kakak" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="kontak_darurat_1_no_hp" value="<?= e(old('kontak_darurat_1_no_hp', $pegawai['kontak_darurat_1_no_hp'] ?? '')) ?>" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
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
                            <input type="text" name="kontak_darurat_2_nama" value="<?= e(old('kontak_darurat_2_nama', $pegawai['kontak_darurat_2_nama'] ?? '')) ?>" placeholder="Nama kerabat/keluarga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Hubungan</label>
                            <input type="text" name="kontak_darurat_2_hubungan" value="<?= e(old('kontak_darurat_2_hubungan', $pegawai['kontak_darurat_2_hubungan'] ?? '')) ?>" placeholder="Contoh: Saudara Kandung / Paman / Kerabat" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="kontak_darurat_2_no_hp" value="<?= e(old('kontak_darurat_2_no_hp', $pegawai['kontak_darurat_2_no_hp'] ?? '')) ?>" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-xs">
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
                        <?php if (empty($keluargaList)): ?>
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
                        <?php else: ?>
                            <?php foreach ($keluargaList as $kItem): ?>
                                <tr class="hover:bg-slate-50/60">
                                    <td class="p-2">
                                        <select name="keluarga_hubungan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                            <?php foreach (['Suami', 'Istri', 'Anak', 'Ayah', 'Ibu', 'Mertua', 'Saudara Kandung', 'Lainnya'] as $hub): ?>
                                                <option value="<?= $hub ?>" <?= ($kItem['hubungan'] ?? '') === $hub ? 'selected' : '' ?>><?= $hub ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-2"><input type="text" name="keluarga_nama[]" value="<?= e($kItem['nama'] ?? '') ?>" placeholder="Nama lengkap" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
                                    <td class="p-2">
                                        <select name="keluarga_jk[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                            <option value="L" <?= ($kItem['jenis_kelamin'] ?? 'L') === 'L' ? 'selected' : '' ?>>L</option>
                                            <option value="P" <?= ($kItem['jenis_kelamin'] ?? 'L') === 'P' ? 'selected' : '' ?>>P</option>
                                        </select>
                                    </td>
                                    <td class="p-2">
                                        <div class="grid grid-cols-2 gap-1">
                                            <input type="text" name="keluarga_tempat_lahir[]" value="<?= e($kItem['tempat_lahir'] ?? '') ?>" placeholder="Tempat" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                            <input type="date" name="keluarga_tgl_lahir[]" value="<?= e($kItem['tanggal_lahir'] ?? '') ?>" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                        </div>
                                    </td>
                                    <td class="p-2">
                                        <select name="keluarga_pendidikan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                            <option value="">-</option>
                                            <?php foreach (['Belum Sekolah', 'SD', 'SMP', 'SMA/SMK', 'Diploma', 'S1', 'S2', 'S3'] as $pend): ?>
                                                <option value="<?= $pend ?>" <?= ($kItem['pendidikan_terakhir'] ?? '') === $pend ? 'selected' : '' ?>><?= $pend ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-2"><input type="text" name="keluarga_pekerjaan[]" value="<?= e($kItem['pekerjaan'] ?? '') ?>" placeholder="Pekerjaan" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
                                    <td class="p-2"><input type="text" name="keluarga_no_hp[]" value="<?= e($kItem['no_hp'] ?? '') ?>" placeholder="08xxx" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
                                    <td class="p-2 text-center">
                                        <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1">✕</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                        <?php if (empty($skillList)): ?>
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
                        <?php else: ?>
                            <?php foreach ($skillList as $sItem): ?>
                                <tr class="hover:bg-slate-50/60">
                                    <td class="p-2"><input type="text" name="skill_nama[]" value="<?= e($sItem['nama_skill'] ?? '') ?>" placeholder="Nama skill" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs font-medium"></td>
                                    <td class="p-2">
                                        <select name="skill_kategori[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs">
                                            <?php foreach (['Pedagogik & Pembelajaran', 'IT & Teknologi', 'Bahasa & Komunikasi', 'Kepemimpinan & Manajemen', "Keagamaan & Al-Qur'an", 'Seni & Olahraga', 'Lainnya'] as $kat): ?>
                                                <option value="<?= $kat ?>" <?= ($sItem['kategori'] ?? '') === $kat ? 'selected' : '' ?>><?= $kat ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-2">
                                        <select name="skill_tingkat[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs font-semibold text-indigo-700">
                                            <?php foreach (['Pemula', 'Menengah', 'Mahir', 'Ahli'] as $tkt): ?>
                                                <option value="<?= $tkt ?>" <?= ($sItem['tingkat_keahlian'] ?? 'Menengah') === $tkt ? 'selected' : '' ?>><?= $tkt ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-2"><input type="text" name="skill_deskripsi[]" value="<?= e($sItem['deskripsi'] ?? '') ?>" placeholder="Catatan singkat" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs"></td>
                                    <td class="p-2 text-center">
                                        <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1">✕</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
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
                        <?php if(empty($pendidikan)): ?>
                            <!-- Baris Pertama (Default jika kosong) -->
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
                        <?php else: ?>
                            <?php foreach($pendidikan as $p_edu): ?>
                                <tr class="border-b border-slate-100">
                                    <td class="py-2 pr-2">
                                        <select name="pendidikan_jenjang[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm">
                                            <option value="">Pilih</option>
                                            <option value="SD" <?= $p_edu['jenjang'] === 'SD' ? 'selected' : '' ?>>SD/Sederajat</option>
                                            <option value="SMP" <?= $p_edu['jenjang'] === 'SMP' ? 'selected' : '' ?>>SMP/Sederajat</option>
                                            <option value="SMA" <?= $p_edu['jenjang'] === 'SMA' ? 'selected' : '' ?>>SMA/Sederajat</option>
                                            <option value="D1" <?= $p_edu['jenjang'] === 'D1' ? 'selected' : '' ?>>D1</option>
                                            <option value="D2" <?= $p_edu['jenjang'] === 'D2' ? 'selected' : '' ?>>D2</option>
                                            <option value="D3" <?= $p_edu['jenjang'] === 'D3' ? 'selected' : '' ?>>D3</option>
                                            <option value="D4" <?= $p_edu['jenjang'] === 'D4' ? 'selected' : '' ?>>D4</option>
                                            <option value="S1" <?= $p_edu['jenjang'] === 'S1' ? 'selected' : '' ?>>S1</option>
                                            <option value="S2" <?= $p_edu['jenjang'] === 'S2' ? 'selected' : '' ?>>S2</option>
                                            <option value="S3" <?= $p_edu['jenjang'] === 'S3' ? 'selected' : '' ?>>S3</option>
                                        </select>
                                    </td>
                                    <td class="py-2 pr-2"><input type="text" name="pendidikan_institusi[]" value="<?= e($p_edu['institusi']) ?>" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm"></td>
                                    <td class="py-2 pr-2"><input type="text" name="pendidikan_jurusan[]" value="<?= e($p_edu['jurusan']) ?>" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm"></td>
                                    <td class="py-2 pr-2"><input type="text" name="pendidikan_tahun[]" value="<?= e($p_edu['tahun_lulus']) ?>" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-sm text-center"></td>
                                    <td class="py-2 text-center">
                                        <button type="button" onclick="this.closest('tr').remove()" class="text-rose-500 hover:text-rose-700 p-1"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Karir & Penugasan Pegawai -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-primary-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                        <span>Riwayat Karir & Penugasan Pegawai</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tersinkronisasi otomatis dari SK penugasan serta mendukung pencatatan manual.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= url('kelola-pegawai/karir/pegawai/' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                        Lihat Linimasa Lengkap →
                    </a>
                    <a href="<?= url('kelola-pegawai/karir/create?pegawai_id=' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 rounded-lg text-xs font-bold transition-colors">
                        + Tambah Karir Manual
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                            <th class="text-left py-2.5">Jabatan / Posisi</th>
                            <th class="text-left py-2.5">Unit Tugas</th>
                            <th class="text-left py-2.5">No SK / Periode</th>
                            <th class="text-center py-2.5">Status</th>
                            <th class="text-center py-2.5">Sumber</th>
                            <th class="text-right py-2.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($karirList)): ?>
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 italic">
                                    Belum ada riwayat karir yang tercatat untuk pegawai ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($karirList as $k): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-2.5 font-bold text-slate-900"><?= e($k['jabatan']) ?></td>
                                    <td class="py-2.5 text-slate-700"><?= e($k['unit_tugas'] ?: 'Yayasan') ?></td>
                                    <td class="py-2.5 text-slate-600">
                                        <span class="font-medium text-slate-800"><?= e($k['no_sk'] ?: 'Tanpa No SK') ?></span><br>
                                        <span class="text-[11px] text-slate-400">
                                            <?= !empty($k['tmt_mulai']) ? date('d/m/Y', strtotime($k['tmt_mulai'])) : '-' ?> s/d <?= !empty($k['tst_selesai']) ? date('d/m/Y', strtotime($k['tst_selesai'])) : 'Sekarang' ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold <?= $k['status'] === 'Aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                            <?= e($k['status']) ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold <?= !empty($k['is_otomatis']) ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' ?>">
                                            <?= !empty($k['is_otomatis']) ? '🤖 SK' : '✍️ Manual' ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="<?= url('kelola-pegawai/karir/edit/' . $k['id']) ?>" class="p-1 text-slate-500 hover:text-amber-600" title="Edit">
                                                ✏️
                                            </a>
                                            <a href="<?= url('kelola-pegawai/karir/pegawai/' . $pegawai['id']) ?>" class="p-1 text-slate-500 hover:text-primary-600" title="Timeline">
                                                🔍
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Prestasi & Penghargaan Pegawai -->
        <div class="bg-white rounded-2xl border border-amber-200/80 shadow-sm p-6 sm:p-8 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-amber-950 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.388c0-.535-.474-1.036-1.073-1.036H8.573c-.599 0-1.073.501-1.073 1.036v3.388m9 0h-9M12 11.25V3m0 8.25 3-3m-3 3-3-3" />
                        </svg>
                        <span>Prestasi & Penghargaan Pegawai</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rekam jejak kejuaraan, sertifikasi kompetensi, dan penghargaan guru/staf.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= url('kelola-pegawai/prestasi/pegawai/' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                        Lihat Portofolio Lengkap →
                    </a>
                    <a href="<?= url('kelola-pegawai/prestasi/create?pegawai_id=' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold shadow-sm transition-colors">
                        + Tambah Prestasi
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                            <th class="text-left py-2.5">Nama Prestasi & Penghargaan</th>
                            <th class="text-center py-2.5">Peringkat & Kategori</th>
                            <th class="text-center py-2.5">Tingkat</th>
                            <th class="text-left py-2.5">Penyelenggara / Tahun</th>
                            <th class="text-center py-2.5">Dokumen</th>
                            <th class="text-right py-2.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($prestasiList)): ?>
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 italic">
                                    Belum ada data prestasi atau penghargaan yang tercatat untuk pegawai ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($prestasiList as $pr): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-2.5 font-bold text-slate-900">
                                        <?= e($pr['nama_prestasi']) ?>
                                        <?php if (!empty($pr['nomor_sertifikat'])): ?>
                                            <span class="block text-[10.5px] font-normal text-slate-400 font-mono">No: <?= e($pr['nomor_sertifikat']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            🏆 <?= e($pr['peringkat']) ?>
                                        </span>
                                        <span class="block text-[10px] text-slate-400 mt-0.5"><?= e($pr['kategori']) ?></span>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold bg-purple-50 text-purple-700">
                                            <?= e($pr['tingkat']) ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-slate-700">
                                        <span class="font-semibold text-slate-800"><?= e($pr['penyelenggara']) ?></span>
                                        <span class="block text-[10.5px] text-slate-400">Tahun <?= e($pr['tahun']) ?></span>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <?php if (!empty($pr['file_sertifikat'])): ?>
                                                <a href="<?= url(ltrim($pr['file_sertifikat'], '/')) ?>" target="_blank" class="p-1 bg-emerald-50 text-emerald-700 rounded text-[11px] font-semibold" title="Sertifikat">
                                                    📜
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($pr['foto_dokumentasi'])): ?>
                                                <a href="<?= url(ltrim($pr['foto_dokumentasi'], '/')) ?>" target="_blank" class="p-1 bg-sky-50 text-sky-700 rounded text-[11px] font-semibold" title="Foto">
                                                    📷
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="<?= url('kelola-pegawai/prestasi/edit/' . $pr['id']) ?>" class="p-1 text-slate-500 hover:text-amber-600" title="Edit">
                                                ✏️
                                            </a>
                                            <a href="<?= url('kelola-pegawai/prestasi/pegawai/' . $pegawai['id']) ?>" class="p-1 text-slate-500 hover:text-amber-600" title="Portofolio">
                                                🔍
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Pelatihan, Diklat & Sertifikasi Pegawai -->
        <div class="bg-white rounded-2xl border border-indigo-200/80 shadow-sm p-6 sm:p-8 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-indigo-950 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                        </svg>
                        <span>Riwayat Pelatihan, Diklat & Sertifikasi Pegawai</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Rekam jejak workshop, bimtek, seminar, diklat fungsional, dan sertifikasi keahlian.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= url('kelola-pegawai/pelatihan/pegawai/' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                        Lihat Portofolio Lengkap →
                    </a>
                    <a href="<?= url('kelola-pegawai/pelatihan/create?pegawai_id=' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-sm transition-colors">
                        + Tambah Pelatihan
                    </a>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-bold text-slate-500 uppercase">
                            <th class="text-left py-2.5">Nama Pelatihan / Diklat</th>
                            <th class="text-center py-2.5">Jenis & Peran</th>
                            <th class="text-left py-2.5">Penyelenggara</th>
                            <th class="text-center py-2.5">Durasi & Waktu</th>
                            <th class="text-center py-2.5">Dokumen</th>
                            <th class="text-right py-2.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php if (empty($pelatihanList)): ?>
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 italic">
                                    Belum ada data pelatihan atau diklat yang tercatat untuk pegawai ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pelatihanList as $pl): ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-2.5 font-bold text-slate-900">
                                        <?= e($pl['nama_pelatihan']) ?>
                                        <?php if (!empty($pl['nomor_sertifikat'])): ?>
                                            <span class="block text-[10.5px] font-normal text-slate-400 font-mono">No: <?= e($pl['nomor_sertifikat']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <?= e($pl['jenis_pelatihan']) ?>
                                        </span>
                                        <span class="block text-[10px] text-slate-400 mt-0.5">Peran: <?= e($pl['peran']) ?></span>
                                    </td>
                                    <td class="py-2.5 text-slate-700">
                                        <span class="font-semibold text-slate-800"><?= e($pl['penyelenggara']) ?></span>
                                        <?php if (!empty($pl['tempat'])): ?>
                                            <span class="block text-[10.5px] text-slate-400">📍 <?= e($pl['tempat']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <?php if (!empty($pl['jumlah_jam']) && $pl['jumlah_jam'] > 0): ?>
                                            <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-bold rounded-lg text-[11px]">
                                                <?= $pl['jumlah_jam'] ?> JP
                                            </span>
                                        <?php endif; ?>
                                        <span class="block text-[10.5px] text-slate-400 mt-0.5">
                                            <?= date('d/m/Y', strtotime($pl['tanggal_mulai'])) ?>
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <?php if (!empty($pl['file_sertifikat'])): ?>
                                                <a href="<?= url(ltrim($pl['file_sertifikat'], '/')) ?>" target="_blank" class="p-1 bg-emerald-50 text-emerald-700 rounded text-[11px] font-semibold" title="Sertifikat">
                                                    📜
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($pl['foto_dokumentasi'])): ?>
                                                <a href="<?= url(ltrim($pl['foto_dokumentasi'], '/')) ?>" target="_blank" class="p-1 bg-sky-50 text-sky-700 rounded text-[11px] font-semibold" title="Foto">
                                                    📷
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="<?= url('kelola-pegawai/pelatihan/edit/' . $pl['id']) ?>" class="p-1 text-slate-500 hover:text-indigo-600" title="Edit">
                                                ✏️
                                            </a>
                                            <a href="<?= url('kelola-pegawai/pelatihan/pegawai/' . $pegawai['id']) ?>" class="p-1 text-slate-500 hover:text-indigo-600" title="Portofolio">
                                                🔍
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button type="submit" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 transition-all duration-200">Perbarui Data Pegawai</button>
                <a href="<?= url('kelola-pegawai') ?>" class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-full transition-all">Batal</a>
            </div>

            <a href="<?= url('kelola-pegawai/cetak-cv/' . $pegawai['id']) ?>" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-full shadow-lg shadow-indigo-600/25 transition-all text-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.285.642-2.502 1.927-2.742a2.025 2.025 0 0 1 2.378 1.488m0 0a2.025 2.025 0 0 1 1.488 2.378c-.24 1.285-1.457 2.167-2.742 1.927a2.025 2.025 0 0 1-1.488-2.378Zm0 0-2.488 4.31m9.22-4.31a2.025 2.025 0 0 1 2.378-1.488c1.285.24 2.167 1.457 1.927 2.742a2.025 2.025 0 0 1-2.378 1.488c-1.285-.24-2.167-1.457-1.927-2.742Zm0 0 2.488 4.31M12 4.5v15" />
                </svg>
                <span>🖨️ Cetak CV Pegawai (F4)</span>
            </a>
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
    tr.className = 'border-b border-slate-100 hover:bg-slate-50/50 transition-colors group';
    tr.innerHTML = `
        <td class="p-4 align-top">
            <select name="pendidikan_jenjang[]" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:border-primary-500 transition-colors">
                <option value="">Pilih</option>
                <option value="SD/Sederajat">SD/Sederajat</option>
                <option value="SMP/Sederajat">SMP/Sederajat</option>
                <option value="SMA/Sederajat">SMA/Sederajat</option>
                <option value="D1/D2/D3">D1/D2/D3</option>
                <option value="D4/S1">D4/S1</option>
                <option value="S2">S2</option>
                <option value="S3">S3</option>
            </select>
        </td>
        <td class="p-4 align-top">
            <input type="text" name="pendidikan_institusi[]" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:border-primary-500 transition-colors">
        </td>
        <td class="p-4 align-top">
            <input type="text" name="pendidikan_jurusan[]" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:border-primary-500 transition-colors">
        </td>
        <td class="p-4 align-top">
            <input type="number" name="pendidikan_tahun[]" required min="1950" max="${new Date().getFullYear()}" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:border-primary-500 transition-colors">
        </td>
        <td class="p-4 align-top text-center">
            <button type="button" class="btn-remove-pendidikan text-rose-400 hover:text-rose-600 p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
});

document.getElementById('pendidikan-list').addEventListener('click', function(e) {
    if (e.target.closest('.btn-remove-pendidikan')) {
        e.target.closest('tr').remove();
    }
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
        <td class="p-2"><input type="text" name="skill_nama[]" placeholder="Nama skill" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded text-xs font-medium"></td>
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
