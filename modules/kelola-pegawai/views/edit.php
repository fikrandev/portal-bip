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
            
            <div class="grid grid-cols-1 gap-5 mb-5">
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
            </div>
            
            <div class="grid grid-cols-1 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Gelar</label>
                    <input type="text" name="gelar" value="<?= e(old('gelar', $pegawai['gelar'] ?? '')) ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">NIY (Nomor Induk Yayasan)</label>
                    <input type="text" name="niy" value="<?= e(old('niy', $pegawai['niy'] ?? '')) ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">NIK KTP</label>
                    <input type="text" name="nik" value="<?= e(old('nik', $pegawai['nik'] ?? '')) ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 mb-5">
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
            </div>
            
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Nama Ibu Kandung</label>
                    <input type="text" name="nama_ibu" value="<?= e(old('nama_ibu', $pegawai['nama_ibu'] ?? '')) ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>
                <div class="flex items-center mt-6">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" <?= old('is_active', $pegawai['is_active']) ? 'checked' : '' ?> class="rounded border-primary-300 text-primary-500 focus:ring-0 focus:ring-transparent">
                        <span class="text-sm font-medium text-primary-800">Pegawai Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Data Penugasan -->
        <div class="bg-white rounded-2xl border border-primary-100/60 shadow-sm p-6 sm:p-8 mb-6">
            <h2 class="text-lg font-bold text-primary-900 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z"/></svg>
                Penugasan & Status
            </h2>
            
            <div class="grid grid-cols-1 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Unit Tugas</label>
                    <select name="unit_tugas" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="">Pilih Unit Tugas</option>
                        <?php foreach($unitTugasList ?? [] as $u): ?>
                            <option value="<?= e($u['nama']) ?>" <?= old('unit_tugas', $pegawai['unit_tugas']) === $u['nama'] ? 'selected' : '' ?>><?= e($u['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Jabatan</label>
                    <select name="jabatan" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="">Pilih Jabatan</option>
                        <?php foreach($jabatanList ?? [] as $j): ?>
                            <option value="<?= e($j['nama']) ?>" <?= old('jabatan', $pegawai['jabatan']) === $j['nama'] ? 'selected' : '' ?>><?= e($j['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Terhitung Mulai Tanggal (TMT)</label>
                    <input type="date" name="tmt" value="<?= old('tmt', $pegawai['tmt'] ?? '') ?>" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>
            </div>
            
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Status Kerja</label>
                    <select name="status_kerja" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="">Pilih Status Kerja</option>
                        <?php foreach($statusKerjaList ?? [] as $sk): ?>
                            <option value="<?= e($sk['nama']) ?>" <?= old('status_kerja', $pegawai['status_kerja']) === $sk['nama'] ? 'selected' : '' ?>><?= e($sk['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Jenis Pegawai</label>
                    <select name="jenis_pegawai" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="">Pilih Jenis Pegawai</option>
                        <?php foreach($jenisPegawaiList ?? [] as $jp): ?>
                            <option value="<?= e($jp['nama']) ?>" <?= old('jenis_pegawai', $pegawai['jenis_pegawai']) === $jp['nama'] ? 'selected' : '' ?>><?= e($jp['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary-800 mb-1.5">Status Dapodik</label>
                    <select name="status_dapodik" class="w-full px-4 py-2 bg-white border border-slate-300 rounded-[10px] text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="">Pilih</option>
                        <option value="Sudah Masuk" <?= old('status_dapodik', $pegawai['status_dapodik']) === 'Sudah Masuk' ? 'selected' : '' ?>>Sudah Masuk</option>
                        <option value="Belum Masuk" <?= old('status_dapodik', $pegawai['status_dapodik']) === 'Belum Masuk' ? 'selected' : '' ?>>Belum Masuk</option>
                    </select>
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
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_ktp" rows="2" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm"><?= e(old('alamat_ktp', $pegawai['alamat_ktp'] ?? '')) ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-primary-800 mb-1">Kelurahan / Desa</label>
                                <input type="text" name="kel_ktp" value="<?= e(old('kel_ktp', $pegawai['kel_ktp'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-primary-800 mb-1">Kecamatan</label>
                                <input type="text" name="kec_ktp" value="<?= e(old('kec_ktp', $pegawai['kec_ktp'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                            </div>
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
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Alamat Lengkap</label>
                            <textarea name="alamat_domisili" id="alamat_domisili" rows="2" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm"><?= e(old('alamat_domisili', $pegawai['alamat_domisili'] ?? '')) ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-primary-800 mb-1">Kelurahan / Desa</label>
                                <input type="text" name="kel_domisili" id="kel_domisili" value="<?= e(old('kel_domisili', $pegawai['kel_domisili'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-primary-800 mb-1">Kecamatan</label>
                                <input type="text" name="kec_domisili" id="kec_domisili" value="<?= e(old('kec_domisili', $pegawai['kec_domisili'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-primary-800 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kab_kota_domisili" id="kab_kota_domisili" value="<?= e(old('kab_kota_domisili', $pegawai['kab_kota_domisili'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-slate-900 focus:border-primary-500 text-sm">
                        </div>
                    </div>
                </div>
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

        <div class="flex items-center gap-3">
            <button type="submit" class="px-8 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-full shadow-lg shadow-primary-600/30 transition-all duration-200">Perbarui Data Pegawai</button>
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
