<?php 
/** 
 * Edit Pegawai Form - Modern Tabbed & Bulletproof Layout
 * Portal BIP - Bina Insan Palu
 */ 
// 1. Ambil data penugasan aktif (jika ada) untuk sinkronisasi jabatan & unit
$penugasanUnit = $activePenugasan['nama_unit'] ?? null;
$penugasanJabatan = $activePenugasan['nama_jabatan'] ?? null;

$curStatusPegawai = old('status_pegawai', $pegawai['status_pegawai'] ?? ($pegawai['status_kerja'] ?? 'Tetap'));
$curUnitTugas = old('unit_tugas', $penugasanUnit ?: ($pegawai['unit_tugas'] ?? ''));
$curJabatan = old('jabatan', $penugasanJabatan ?: ($pegawai['jabatan'] ?? ''));
$curJenisPegawai = old('jenis_pegawai', $pegawai['jenis_pegawai'] ?? '');
$curStatusDapodik = old('status_dapodik', $pegawai['status_dapodik'] ?? '');
$tglMasuk = old('tanggal_masuk', $pegawai['tanggal_masuk'] ?? ($pegawai['tmt'] ?? ''));

// Hitung Masa Kerja
$masaKerjaStr = '-';
if (!empty($tglMasuk)) {
    try {
        $diff = (new DateTime($tglMasuk))->diff(new DateTime());
        if ($diff->invert == 0) {
            $masaKerjaStr = ($diff->y > 0 ? $diff->y . ' thn ' : '') . $diff->m . ' bln';
        }
    } catch (Exception $e) {}
}
?>

<div class="max-w-6xl mx-auto space-y-6">

    <!-- 1. Header Banner & Profil Ringkas -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-primary-900 via-primary-800 to-indigo-900 px-6 sm:px-8 py-6 text-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <!-- Profil Info -->
                <div class="flex items-center gap-5">
                    <!-- Avatar Preview Container -->
                    <div class="relative flex-shrink-0" style="width: 80px; height: 80px;">
                        <div class="w-20 h-20 rounded-2xl bg-white/15 backdrop-blur-md border-2 border-white/40 overflow-hidden shadow-md flex items-center justify-center" id="foto-banner-preview" style="width: 80px; height: 80px;">
                            <?php if (!empty($pegawai['foto'])): ?>
                                <img src="<?= url(ltrim($pegawai['foto'], '/')) ?>" alt="Foto" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-2xl font-black text-white/90 tracking-wider">
                                    <?= strtoupper(substr($pegawai['nama'], 0, 2)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <label for="foto-input" class="absolute -bottom-1.5 -right-1.5 w-7 h-7 rounded-full bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center cursor-pointer shadow-md transition-all text-xs" title="Ganti Foto" style="width: 28px; height: 28px;">
                            📷
                        </label>
                    </div>

                    <!-- Informasi Identitas Utama -->
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                                <?= e($pegawai['nama']) ?><?= !empty($pegawai['gelar']) ? ', ' . e($pegawai['gelar']) : '' ?>
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold shadow-sm <?= $pegawai['is_active'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-rose-500/20 text-rose-300 border border-rose-400/30' ?>">
                                <?= $pegawai['is_active'] ? '● Aktif' : '○ Non-Aktif' ?>
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-primary-200/90 font-medium">
                            <?php if(!empty($pegawai['niy'])): ?>
                                <span><strong class="text-primary-300">NIY:</strong> <?= e($pegawai['niy']) ?></span>
                            <?php endif; ?>
                            <?php if(!empty($curUnitTugas)): ?>
                                <span><strong class="text-primary-300">Unit:</strong> <?= e($curUnitTugas) ?></span>
                            <?php endif; ?>
                            <?php if(!empty($curJabatan)): ?>
                                <span><strong class="text-primary-300">Jabatan:</strong> <?= e($curJabatan) ?></span>
                            <?php endif; ?>
                            <span><strong class="text-primary-300">Masa Kerja:</strong> <?= $masaKerjaStr ?></span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mt-2.5">
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold <?= $curStatusPegawai === 'Tetap' ? 'bg-emerald-500 text-white' : ($curStatusPegawai === 'Kontrak' ? 'bg-amber-500 text-white' : 'bg-indigo-500 text-white') ?>">
                                Status: <?= e($curStatusPegawai) ?>
                            </span>
                            <?php if(!empty($activePenugasan)): ?>
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 flex items-center gap-1">
                                    <span>📜 Penugasan: <?= e($activePenugasan['nama_grup']) ?></span>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tombol Action Cepat -->
                <div class="flex items-center gap-2.5 self-start md:self-center">
                    <a href="<?= url('kelola-pegawai/cetak-cv/' . $pegawai['id']) ?>" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs shadow-md transition-all">
                        <span>🖨️ Cetak CV (F4)</span>
                    </a>
                    <a href="<?= url('kelola-pegawai') ?>" class="px-4 py-2.5 bg-white/15 hover:bg-white/25 text-white font-semibold rounded-xl text-xs transition-colors">
                        ← Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Tab Navigation Bar -->
        <div class="bg-slate-50 border-t border-slate-200 px-4 sm:px-6 py-2 overflow-x-auto flex items-center gap-2">
            <button type="button" onclick="switchTab('tab-pribadi')" id="btn-tab-pribadi" class="tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-white text-primary-900 shadow-sm border border-slate-200">
                👤 Data Pribadi
            </button>
            <button type="button" onclick="switchTab('tab-kepegawaian')" id="btn-tab-kepegawaian" class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-primary-800 hover:bg-white/60">
                🏢 Kepegawaian & Penugasan
            </button>
            <button type="button" onclick="switchTab('tab-alamat')" id="btn-tab-alamat" class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-primary-800 hover:bg-white/60">
                📍 Alamat & Kontak Darurat
            </button>
            <button type="button" onclick="switchTab('tab-keluarga')" id="btn-tab-keluarga" class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-primary-800 hover:bg-white/60">
                👨‍👩‍👧 Keluarga (<?= count($keluargaList ?? []) ?>)
            </button>
            <button type="button" onclick="switchTab('tab-skill')" id="btn-tab-skill" class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-primary-800 hover:bg-white/60">
                ⚡ Keahlian (<?= count($skillList ?? []) ?>)
            </button>
            <button type="button" onclick="switchTab('tab-pendidikan')" id="btn-tab-pendidikan" class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-primary-800 hover:bg-white/60">
                🎓 Pendidikan (<?= count($pendidikan ?? []) ?>)
            </button>
            <button type="button" onclick="switchTab('tab-karir')" id="btn-tab-karir" class="tab-btn px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-primary-800 hover:bg-white/60">
                📜 Karir & Prestasi
            </button>
        </div>
    </div>

    <!-- 2. Form Utama Edit Pegawai -->
    <form action="<?= url('kelola-pegawai/update/' . $pegawai['id']) ?>" method="POST" enctype="multipart/form-data" id="form-edit-pegawai">
        <?= CSRF::field() ?>
        <input type="hidden" name="active_tab" id="active-tab-input" value="tab-pribadi">
        
        <!-- Real File Input -->
        <input type="file" id="foto-input" name="foto" accept="image/png, image/jpeg, image/jpg" class="hidden">

        <!-- ================= TAB 1: DATA PRIBADI ================= -->
        <div id="tab-pribadi" class="tab-content">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center text-xs font-bold">1</span>
                        Identitas Pribadi & Kontak Pegawai
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Informasi kependudukan dan kontak komunikasi utama pegawai.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <!-- Nama Lengkap -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" id="input-nama" value="<?= e(old('nama', $pegawai['nama'])) ?>" required placeholder="Nama lengkap tanpa gelar" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>

                    <!-- Gelar -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Gelar Akademik</label>
                        <input type="text" name="gelar" value="<?= e(old('gelar', $pegawai['gelar'] ?? '')) ?>" placeholder="Contoh: S.Pd, M.Kom" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>

                    <!-- NIY -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIY (No. Induk Yayasan)</label>
                        <input type="text" name="niy" value="<?= e(old('niy', $pegawai['niy'] ?? '')) ?>" placeholder="Nomor Induk Yayasan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>

                    <!-- NIK -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIK (KTP 16 Digit)</label>
                        <input type="text" name="nik" maxlength="16" value="<?= e(old('nik', $pegawai['nik'] ?? '')) ?>" placeholder="16 digit NIK" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all font-mono">
                    </div>

                    <!-- NPWP -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">NPWP</label>
                        <input type="text" name="npwp" value="<?= e(old('npwp', $pegawai['npwp'] ?? '')) ?>" placeholder="Nomor Pokok Wajib Pajak" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all font-mono">
                    </div>

                    <!-- No WA -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. WhatsApp / HP</label>
                        <input type="text" name="no_wa" value="<?= e(old('no_wa', $pegawai['no_wa'] ?? '')) ?>" placeholder="08xxxxxxxxxx" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>

                    <!-- Email -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Pribadi / Dinas</label>
                        <input type="email" name="email" value="<?= e(old('email', $pegawai['email'] ?? '')) ?>" placeholder="nama@email.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                            <option value="L" <?= old('jenis_kelamin', $pegawai['jenis_kelamin']) === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= old('jenis_kelamin', $pegawai['jenis_kelamin']) === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>

                    <!-- Status Menikah -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Pernikahan</label>
                        <select name="status_nikah" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                            <option value="">-- Pilih Status --</option>
                            <option value="Belum Menikah" <?= old('status_nikah', $pegawai['status_nikah']) === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                            <option value="Menikah" <?= old('status_nikah', $pegawai['status_nikah']) === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                            <option value="Cerai Hidup" <?= old('status_nikah', $pegawai['status_nikah']) === 'Cerai Hidup' ? 'selected' : '' ?>>Cerai Hidup</option>
                            <option value="Cerai Mati" <?= old('status_nikah', $pegawai['status_nikah']) === 'Cerai Mati' ? 'selected' : '' ?>>Cerai Mati</option>
                        </select>
                    </div>

                    <!-- Tempat Lahir -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="<?= e(old('tempat_lahir', $pegawai['tempat_lahir'] ?? '')) ?>" placeholder="Kota / Kabupaten" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?= old('tanggal_lahir', $pegawai['tanggal_lahir'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>

                    <!-- Nama Ibu Kandung -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Ibu Kandung</label>
                        <input type="text" name="nama_ibu" value="<?= e(old('nama_ibu', $pegawai['nama_ibu'] ?? '')) ?>" placeholder="Nama lengkap ibu" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 2: KEPEGAWAIAN & PENUGASAN ================= -->
        <div id="tab-kepegawaian" class="tab-content hidden space-y-6">
            <!-- Info Sinkronisasi Penugasan Aktif -->
            <div class="p-5 rounded-2xl <?= !empty($activePenugasan) ? 'bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200' : 'bg-slate-50 border border-slate-200' ?>">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl mt-0.5"><?= !empty($activePenugasan) ? '📜' : 'ℹ️' ?></span>
                        <div>
                            <h3 class="text-xs font-bold <?= !empty($activePenugasan) ? 'text-emerald-950' : 'text-slate-800' ?> uppercase tracking-wider">
                                <?= !empty($activePenugasan) ? 'Sinkronisasi Penugasan & SK Aktif' : 'Status Penugasan' ?>
                            </h3>
                            <?php if(!empty($activePenugasan)): ?>
                                <p class="text-xs text-emerald-800 mt-1 leading-relaxed">
                                    Pegawai ini terdaftar aktif pada grup SK <strong><?= e($activePenugasan['nama_grup']) ?></strong> (No. SK: <strong><?= e($activePenugasan['no_sk'] ?: 'Belum Ada No SK') ?></strong>).
                                    Unit tugas dan jabatan saat ini disesuaikan otomatis dengan SK penugasan aktif.
                                </p>
                            <?php else: ?>
                                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                    Pegawai belum terdaftar pada SK penugasan aktif semester ini. Anda dapat menetapkan penugasan melalui modul <a href="<?= url('kelola-pegawai/penugasan') ?>" class="text-primary-600 underline font-bold" target="_blank">Penugasan & SK Grup</a>.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if(!empty($activePenugasan)): ?>
                        <a href="<?= url('kelola-pegawai/penugasan/grup/' . $activePenugasan['grup_id']) ?>" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shrink-0 transition-colors shadow-sm">
                            Lihat SK Grup →
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">2</span>
                        Data Kepegawaian, Unit & Penugasan
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Penempatan unit tugas, posisi jabatan, status kerja, dan status dapodik.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <!-- Unit Tugas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Unit Tugas / Sekolah <span class="text-rose-500">*</span>
                            <?php if(!empty($penugasanUnit)): ?>
                                <span class="text-[10px] text-emerald-600 font-bold lowercase">(dari SK: <?= e($penugasanUnit) ?>)</span>
                            <?php endif; ?>
                        </label>
                        <select name="unit_tugas" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                            <option value="">-- Pilih Unit Tugas --</option>
                            <?php 
                            $defaultUnits = ['PAUD', 'SD', 'SMP', 'SMA', 'Yayasan'];
                            $units = !empty($unitList) ? array_column($unitList, 'nama') : $defaultUnits;
                            foreach ($units as $u): ?>
                                <option value="<?= e($u) ?>" <?= $curUnitTugas === $u ? 'selected' : '' ?>><?= e($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Jabatan / Posisi <span class="text-rose-500">*</span>
                            <?php if(!empty($penugasanJabatan)): ?>
                                <span class="text-[10px] text-emerald-600 font-bold lowercase">(dari SK: <?= e($penugasanJabatan) ?>)</span>
                            <?php endif; ?>
                        </label>
                        <select name="jabatan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                            <option value="">-- Pilih Jabatan --</option>
                            <?php 
                            $defaultJabs = ['Wali Kelas', 'Guru Mapel', 'Wakakum', 'Kepala Sekolah', 'Kepala Divisi IT', 'Staff Tata Usaha', 'Bendahara', 'Security', 'Cleaning Service'];
                            $jabs = !empty($jabatanList) ? array_column($jabatanList, 'nama') : $defaultJabs;
                            foreach ($jabs as $j): ?>
                                <option value="<?= e($j) ?>" <?= $curJabatan === $j ? 'selected' : '' ?>><?= e($j) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status Pegawai -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Status Pegawai <span class="text-rose-500">*</span>
                        </label>
                        <select name="status_pegawai" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-bold focus:bg-white focus:border-primary-500 transition-all">
                            <option value="Tetap" <?= $curStatusPegawai === 'Tetap' ? 'selected' : '' ?>>🟢 Tetap</option>
                            <option value="Kontrak" <?= $curStatusPegawai === 'Kontrak' ? 'selected' : '' ?>>🟡 Kontrak</option>
                            <option value="Training" <?= $curStatusPegawai === 'Training' ? 'selected' : '' ?>>🔵 Training</option>
                        </select>
                    </div>

                    <!-- Jenis Pegawai -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Pegawai</label>
                        <select name="jenis_pegawai" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                            <option value="">-- Pilih Jenis Pegawai --</option>
                            <?php 
                            $defaultJenis = ['Guru', 'Support System', 'Tenaga Kependidikan'];
                            $jns = !empty($jenisPegawaiList) ? array_column($jenisPegawaiList, 'nama') : $defaultJenis;
                            foreach ($jns as $jn): ?>
                                <option value="<?= e($jn) ?>" <?= $curJenisPegawai === $jn ? 'selected' : '' ?>><?= e($jn) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Status Dapodik -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Dapodik</label>
                        <select name="status_dapodik" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                            <option value="Belum Terdaftar" <?= ($curStatusDapodik === 'Belum Terdaftar' || empty($curStatusDapodik)) ? 'selected' : '' ?>>Belum Terdaftar</option>
                            <option value="Sudah Terdaftar" <?= $curStatusDapodik === 'Sudah Terdaftar' ? 'selected' : '' ?>>Sudah Terdaftar</option>
                        </select>
                    </div>

                    <!-- Tanggal Masuk Kerja -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Masuk Kerja</label>
                        <input type="date" name="tanggal_masuk" value="<?= $tglMasuk ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-sm font-medium focus:bg-white focus:border-primary-500 transition-all">
                    </div>

                    <!-- Status Aktif Checkbox -->
                    <div class="lg:col-span-3 pt-2">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-slate-900">Status Keaktifan Pegawai</span>
                                <p class="text-xs text-slate-500">Jika dinonaktifkan, pegawai akan dipindahkan ke daftar Pegawai Keluar dan akun tidak dapat login.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" <?= old('is_active', $pegawai['is_active']) ? 'checked' : '' ?> class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 3: ALAMAT & KONTAK DARURAT ================= -->
        <div id="tab-alamat" class="tab-content hidden">
            <div class="space-y-6">
                <!-- Alamat KTP & Domisili -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                    <div class="border-b border-slate-100 pb-4 mb-6">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-primary-50 text-primary-600 flex items-center justify-center text-xs font-bold">3</span>
                            Informasi Alamat Tempat Tinggal
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Alamat resmi sesuai KTP dan alamat domisili tempat tinggal saat ini.</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Alamat Sesuai KTP -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-4 pb-2 border-b border-slate-200 flex items-center gap-2">
                                <span>🪪 Alamat Sesuai KTP</span>
                            </h3>
                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Alamat Lengkap (Jalan, RT/RW, No. Rumah)</label>
                                    <textarea name="alamat_ktp" rows="2" placeholder="Nama jalan, RT/RW, No..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all"><?= e(old('alamat_ktp', $pegawai['alamat_ktp'] ?? '')) ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 mb-1">Kelurahan / Desa</label>
                                        <input type="text" name="kel_ktp" value="<?= e(old('kel_ktp', $pegawai['kel_ktp'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 mb-1">Kecamatan</label>
                                        <input type="text" name="kec_ktp" value="<?= e(old('kec_ktp', $pegawai['kec_ktp'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Kabupaten / Kota</label>
                                    <input type="text" name="kab_kota_ktp" value="<?= e(old('kab_kota_ktp', $pegawai['kab_kota_ktp'] ?? '')) ?>" placeholder="Contoh: Kota Palu" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Domisili Sekarang -->
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between pb-2 mb-4 border-b border-slate-200">
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                    <span>🏠 Domisili Sekarang</span>
                                </h3>
                                <button type="button" id="btn-copy-ktp" class="px-2.5 py-1 bg-primary-100 hover:bg-primary-200 text-primary-800 font-bold rounded-lg text-[11px] transition-all">
                                    📋 Salin dari KTP
                                </button>
                            </div>
                            <div class="space-y-3.5">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Alamat Lengkap</label>
                                    <textarea name="alamat_domisili" id="alamat_domisili" rows="2" placeholder="Nama jalan, RT/RW, No..." class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all"><?= e(old('alamat_domisili', $pegawai['alamat_domisili'] ?? '')) ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 mb-1">Kelurahan / Desa</label>
                                        <input type="text" name="kel_domisili" id="kel_domisili" value="<?= e(old('kel_domisili', $pegawai['kel_domisili'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-600 mb-1">Kecamatan</label>
                                        <input type="text" name="kec_domisili" id="kec_domisili" value="<?= e(old('kec_domisili', $pegawai['kec_domisili'] ?? '')) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 mb-1">Kabupaten / Kota</label>
                                    <input type="text" name="kab_kota_domisili" id="kab_kota_domisili" value="<?= e(old('kab_kota_domisili', $pegawai['kab_kota_domisili'] ?? '')) ?>" placeholder="Contoh: Kota Palu" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-primary-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kontak Darurat -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                    <div class="border-b border-slate-100 pb-4 mb-6">
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold">4</span>
                            Kontak Darurat (Emergency Contact)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Daftarkan 2 kontak keluarga/kerabat terdekat yang dapat dihubungi saat keadaan mendesak.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kontak 1 -->
                        <div class="p-5 rounded-2xl bg-amber-50/40 border border-amber-200/70">
                            <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                                Kontak Darurat Utama
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                                    <input type="text" name="kontak_darurat_1_nama" value="<?= e(old('kontak_darurat_1_nama', $pegawai['kontak_darurat_1_nama'] ?? '')) ?>" placeholder="Nama kerabat/keluarga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-amber-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Hubungan</label>
                                    <input type="text" name="kontak_darurat_1_hubungan" value="<?= e(old('kontak_darurat_1_hubungan', $pegawai['kontak_darurat_1_hubungan'] ?? '')) ?>" placeholder="Contoh: Suami / Istri / Ayah / Ibu / Kakak" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-amber-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                                    <input type="text" name="kontak_darurat_1_no_hp" value="<?= e(old('kontak_darurat_1_no_hp', $pegawai['kontak_darurat_1_no_hp'] ?? '')) ?>" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-amber-500 transition-all">
                                </div>
                            </div>
                        </div>

                        <!-- Kontak 2 -->
                        <div class="p-5 rounded-2xl bg-indigo-50/40 border border-indigo-200/70">
                            <h3 class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-indigo-500 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                                Kontak Darurat Cadangan
                            </h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                                    <input type="text" name="kontak_darurat_2_nama" value="<?= e(old('kontak_darurat_2_nama', $pegawai['kontak_darurat_2_nama'] ?? '')) ?>" placeholder="Nama kerabat/keluarga" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-indigo-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Hubungan</label>
                                    <input type="text" name="kontak_darurat_2_hubungan" value="<?= e(old('kontak_darurat_2_hubungan', $pegawai['kontak_darurat_2_hubungan'] ?? '')) ?>" placeholder="Contoh: Saudara Kandung / Paman / Kerabat" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-indigo-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                                    <input type="text" name="kontak_darurat_2_no_hp" value="<?= e(old('kontak_darurat_2_no_hp', $pegawai['kontak_darurat_2_no_hp'] ?? '')) ?>" placeholder="08xxxxxxxxxx" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs focus:border-indigo-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= TAB 4: KELUARGA ================= -->
        <div id="tab-keluarga" class="tab-content hidden">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">5</span>
                            Susunan Anggota Keluarga
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Data pasangan (suami/istri), anak, atau orang tua pegawai.</p>
                    </div>
                    <button type="button" id="btn-add-keluarga" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all self-start">
                        + Tambah Anggota
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
                            <?php if (empty($keluargaList)): ?>
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
                            <?php else: ?>
                                <?php foreach ($keluargaList as $kItem): ?>
                                    <tr class="keluarga-row hover:bg-slate-50/60">
                                        <td class="p-2">
                                            <select name="keluarga_hubungan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                                <?php foreach (['Suami', 'Istri', 'Anak', 'Ayah', 'Ibu', 'Mertua', 'Saudara Kandung', 'Lainnya'] as $hub): ?>
                                                    <option value="<?= $hub ?>" <?= ($kItem['hubungan'] ?? '') === $hub ? 'selected' : '' ?>><?= $hub ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="p-2"><input type="text" name="keluarga_nama[]" value="<?= e($kItem['nama'] ?? '') ?>" placeholder="Nama lengkap" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
                                        <td class="p-2">
                                            <select name="keluarga_jk[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                                <option value="L" <?= ($kItem['jenis_kelamin'] ?? 'L') === 'L' ? 'selected' : '' ?>>L</option>
                                                <option value="P" <?= ($kItem['jenis_kelamin'] ?? 'L') === 'P' ? 'selected' : '' ?>>P</option>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <div class="grid grid-cols-2 gap-1.5">
                                                <input type="text" name="keluarga_tempat_lahir[]" value="<?= e($kItem['tempat_lahir'] ?? '') ?>" placeholder="Tempat" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                                <input type="date" name="keluarga_tgl_lahir[]" value="<?= e($kItem['tanggal_lahir'] ?? '') ?>" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            <select name="keluarga_pendidikan[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                                <option value="">-</option>
                                                <?php foreach (['Belum Sekolah', 'SD', 'SMP', 'SMA/SMK', 'Diploma', 'S1', 'S2', 'S3'] as $pend): ?>
                                                    <option value="<?= $pend ?>" <?= ($kItem['pendidikan_terakhir'] ?? '') === $pend ? 'selected' : '' ?>><?= $pend ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="p-2"><input type="text" name="keluarga_pekerjaan[]" value="<?= e($kItem['pekerjaan'] ?? '') ?>" placeholder="Pekerjaan" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
                                        <td class="p-2"><input type="text" name="keluarga_no_hp[]" value="<?= e($kItem['no_hp'] ?? '') ?>" placeholder="08xxx" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
                                        <td class="p-2 text-center">
                                            <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 5: SKILL / KEAHLIAN ================= -->
        <div id="tab-skill" class="tab-content hidden">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold">6</span>
                            Keahlian & Keterampilan (Skill & Competencies)
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kompetensi khusus, IT, bahasa, keagamaan yang tercantum pada portofolio/CV.</p>
                    </div>
                    <button type="button" id="btn-add-skill" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all self-start">
                        + Tambah Keahlian
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
                            <?php if (empty($skillList)): ?>
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
                            <?php else: ?>
                                <?php foreach ($skillList as $sItem): ?>
                                    <tr class="skill-row hover:bg-slate-50/60">
                                        <td class="p-2"><input type="text" name="skill_nama[]" value="<?= e($sItem['nama_skill'] ?? '') ?>" placeholder="Nama skill" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
                                        <td class="p-2">
                                            <select name="skill_kategori[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs">
                                                <?php foreach (['Pedagogik & Pembelajaran', 'IT & Teknologi', 'Bahasa & Komunikasi', 'Kepemimpinan & Manajemen', "Keagamaan & Al-Qur'an", 'Seni & Olahraga', 'Lainnya'] as $kat): ?>
                                                    <option value="<?= $kat ?>" <?= ($sItem['kategori'] ?? '') === $kat ? 'selected' : '' ?>><?= $kat ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="p-2">
                                            <select name="skill_tingkat[]" class="w-full px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-indigo-700">
                                                <?php foreach (['Pemula', 'Menengah', 'Mahir', 'Ahli'] as $tkt): ?>
                                                    <option value="<?= $tkt ?>" <?= ($sItem['tingkat_keahlian'] ?? 'Menengah') === $tkt ? 'selected' : '' ?>><?= $tkt ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="p-2"><input type="text" name="skill_deskripsi[]" value="<?= e($sItem['deskripsi'] ?? '') ?>" placeholder="Catatan singkat" class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs"></td>
                                        <td class="p-2 text-center">
                                            <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 6: PENDIDIKAN ================= -->
        <div id="tab-pendidikan" class="tab-content hidden">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-xs font-bold">7</span>
                            Riwayat Pendidikan Formal
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Jenjang pendidikan, sekolah/universitas, jurusan, dan tahun kelulusan.</p>
                    </div>
                    <button type="button" id="btn-add-pendidikan" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all self-start">
                        + Tambah Pendidikan
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
                            <?php if(empty($pendidikan)): ?>
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
                            <?php else: ?>
                                <?php foreach($pendidikan as $p_edu): ?>
                                    <tr class="pendidikan-row hover:bg-slate-50/60">
                                        <td class="p-2">
                                            <select name="pendidikan_jenjang[]" class="w-full px-2.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium">
                                                <option value="">Pilih</option>
                                                <?php foreach(['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'] as $j): ?>
                                                    <option value="<?= $j ?>" <?= $p_edu['jenjang'] === $j ? 'selected' : '' ?>><?= $j ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="p-2"><input type="text" name="pendidikan_institusi[]" value="<?= e($p_edu['institusi']) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs font-medium"></td>
                                        <td class="p-2"><input type="text" name="pendidikan_jurusan[]" value="<?= e($p_edu['jurusan']) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs"></td>
                                        <td class="p-2"><input type="text" name="pendidikan_tahun[]" value="<?= e($p_edu['tahun_lulus']) ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-xs text-center font-mono font-bold"></td>
                                        <td class="p-2 text-center">
                                            <button type="button" onclick="this.closest('tr').remove()" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center mx-auto text-xs font-bold">✕</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= TAB 7: RIWAYAT KARIR & PRESTASI ================= -->
        <div id="tab-karir" class="tab-content hidden space-y-6">
            <!-- 1. Riwayat Karir & SK -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span>🏛️ Riwayat Karir & SK Penugasan</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Tersinkronisasi otomatis dari SK penugasan serta mendukung pencatatan manual.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="<?= url('kelola-pegawai/karir/create?pegawai_id=' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 rounded-lg text-xs font-bold transition-colors">
                            + Tambah Karir
                        </a>
                        <a href="<?= url('kelola-pegawai/karir/pegawai/' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                            Linimasa →
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold uppercase">
                                <th class="text-left py-2.5 px-3">Jabatan</th>
                                <th class="text-left py-2.5 px-3">Unit Tugas</th>
                                <th class="text-left py-2.5 px-3">No. SK / Periode</th>
                                <th class="text-center py-2.5 px-3">Status</th>
                                <th class="text-center py-2.5 px-3">Sumber</th>
                                <th class="text-right py-2.5 px-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($karirList)): ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400 italic">Belum ada riwayat karir yang tercatat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($karirList as $k): ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-2.5 px-3 font-bold text-slate-900"><?= e($k['jabatan']) ?></td>
                                        <td class="py-2.5 px-3 text-slate-700"><?= e($k['unit_tugas'] ?: 'Yayasan') ?></td>
                                        <td class="py-2.5 px-3 text-slate-600">
                                            <span class="font-medium text-slate-800"><?= e($k['no_sk'] ?: 'Tanpa No SK') ?></span><br>
                                            <span class="text-[11px] text-slate-400">
                                                <?= !empty($k['tmt_mulai']) ? date('d/m/Y', strtotime($k['tmt_mulai'])) : '-' ?> s/d <?= !empty($k['tst_selesai']) ? date('d/m/Y', strtotime($k['tst_selesai'])) : 'Sekarang' ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold <?= $k['status'] === 'Aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                                <?= e($k['status']) ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold <?= !empty($k['is_otomatis']) ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' ?>">
                                                <?= !empty($k['is_otomatis']) ? '🤖 SK' : '✍️ Manual' ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right">
                                            <a href="<?= url('kelola-pegawai/karir/edit/' . $k['id']) ?>" class="p-1.5 text-slate-500 hover:text-amber-600 font-bold" title="Edit">✏️</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 2. Prestasi Pegawai -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span>🏆 Prestasi & Penghargaan Pegawai</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Rekam jejak kejuaraan dan penghargaan guru/staf.</p>
                    </div>
                    <a href="<?= url('kelola-pegawai/prestasi/create?pegawai_id=' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold shadow-sm transition-colors">
                        + Tambah Prestasi
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold uppercase">
                                <th class="text-left py-2.5 px-3">Nama Prestasi</th>
                                <th class="text-center py-2.5 px-3">Peringkat & Kategori</th>
                                <th class="text-center py-2.5 px-3">Tingkat</th>
                                <th class="text-left py-2.5 px-3">Penyelenggara / Tahun</th>
                                <th class="text-right py-2.5 px-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($prestasiList)): ?>
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400 italic">Belum ada data prestasi.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($prestasiList as $pr): ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-2.5 px-3 font-bold text-slate-900"><?= e($pr['nama_prestasi']) ?></td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                                <?= e($pr['peringkat']) ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10.5px] font-semibold bg-purple-50 text-purple-700"><?= e($pr['tingkat']) ?></span>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-700">
                                            <?= e($pr['penyelenggara']) ?> (<?= e($pr['tahun']) ?>)
                                        </td>
                                        <td class="py-2.5 px-3 text-right">
                                            <a href="<?= url('kelola-pegawai/prestasi/edit/' . $pr['id']) ?>" class="p-1.5 text-slate-500 hover:text-amber-600 font-bold" title="Edit">✏️</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. Diklat & Pelatihan -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <span>📚 Pelatihan, Diklat & Sertifikasi</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">Rekam jejak bimtek, workshop, dan diklat keahlian.</p>
                    </div>
                    <a href="<?= url('kelola-pegawai/pelatihan/create?pegawai_id=' . $pegawai['id']) ?>" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow-sm transition-colors">
                        + Tambah Pelatihan
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-bold uppercase">
                                <th class="text-left py-2.5 px-3">Nama Pelatihan</th>
                                <th class="text-center py-2.5 px-3">Jenis & Peran</th>
                                <th class="text-left py-2.5 px-3">Penyelenggara</th>
                                <th class="text-center py-2.5 px-3">Durasi</th>
                                <th class="text-right py-2.5 px-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($pelatihanList)): ?>
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400 italic">Belum ada data pelatihan.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pelatihanList as $pl): ?>
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="py-2.5 px-3 font-bold text-slate-900"><?= e($pl['nama_pelatihan']) ?></td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700"><?= e($pl['jenis_pelatihan']) ?></span>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-700"><?= e($pl['penyelenggara']) ?></td>
                                        <td class="py-2.5 px-3 text-center font-bold text-emerald-700"><?= $pl['jumlah_jam'] ? $pl['jumlah_jam'] . ' JP' : '-' ?></td>
                                        <td class="py-2.5 px-3 text-right">
                                            <a href="<?= url('kelola-pegawai/pelatihan/edit/' . $pl['id']) ?>" class="p-1.5 text-slate-500 hover:text-indigo-600 font-bold" title="Edit">✏️</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. Sticky Action Bar Footer -->
        <div class="sticky bottom-4 z-30 bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 shadow-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Pastikan data telah diperiksa dengan benar sebelum menyimpan.</span>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <a href="<?= url('kelola-pegawai') ?>" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition-colors text-center">
                    Batal
                </a>
                <button type="submit" id="btn-submit-pegawai" class="px-8 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <span>💾 Simpan Perubahan Pegawai</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- ================= JAVASCRIPT LOGIC ================= -->
<script>
// Tab Switcher Logic
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.className = 'tab-btn px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-primary-800 hover:bg-white/60';
    });

    const activeTab = document.getElementById(tabId);
    if (activeTab) {
        activeTab.classList.remove('hidden');
    }

    const activeBtn = document.getElementById('btn-' + tabId);
    if (activeBtn) {
        activeBtn.className = 'tab-btn px-4 py-2 rounded-xl text-xs font-bold bg-white text-primary-900 shadow-sm border border-slate-200';
    }

    const activeTabInput = document.getElementById('active-tab-input');
    if (activeTabInput) {
        activeTabInput.value = tabId;
    }
}

// Inisialisasi Tab dari Parameter URL (misal: ?tab=tab-kepegawaian)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab');
    if (initialTab && document.getElementById(initialTab)) {
        switchTab(initialTab);
    }
});

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
    btn.innerHTML = '✅ Disalin!';
    setTimeout(() => { btn.innerHTML = originalText; }, 2000);
});

// Photo Preview
const fotoInput = document.getElementById('foto-input');
const bannerPreview = document.getElementById('foto-banner-preview');
fotoInput?.addEventListener('change', function(e) {
    const files = e.target.files;
    if (files && files.length > 0) {
        const url = URL.createObjectURL(files[0]);
        if (bannerPreview) {
            bannerPreview.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;
        }
    }
});

// Form Submit Handler with Loading State & Validation
const formEditPegawai = document.getElementById('form-edit-pegawai');
const btnSubmitPegawai = document.getElementById('btn-submit-pegawai');

if (formEditPegawai) {
    formEditPegawai.addEventListener('submit', function(e) {
        const inputNama = document.getElementById('input-nama');
        if (!inputNama || !inputNama.value.trim()) {
            e.preventDefault();
            switchTab('tab-pribadi');
            inputNama?.focus();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama Wajib Diisi',
                    text: 'Silakan isi Nama Lengkap Pegawai terlebih dahulu.',
                    confirmButtonText: 'Oke'
                });
            } else {
                alert('Nama Lengkap Pegawai wajib diisi.');
            }
            return;
        }

        // Tampilkan State Loading pada Tombol
        if (btnSubmitPegawai) {
            btnSubmitPegawai.disabled = true;
            btnSubmitPegawai.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="width:16px;height:16px;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Menyimpan Perubahan...</span>
            `;
        }

        // Tampilkan Modal Loading
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Menyimpan Perubahan Pegawai...',
                text: 'Mohon tunggu, sistem sedang memproses dan menyinkronkan data pegawai.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    });
}

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
</script>
