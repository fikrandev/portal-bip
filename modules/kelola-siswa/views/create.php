<?php
/**
 * Create Siswa Form View - Portal BIP
 * Multi-section Form with 50+ Dapodik / BIP Fields
 */
?>

<div class="max-w-6xl space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                <span>👤</span> Tambah Data Siswa Baru
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Lengkapi identitas siswa, penempatan akademik, alamat, dan data orang tua</p>
        </div>
        <a href="<?= url('kelola-siswa') ?>" class="px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors flex items-center gap-1.5">
            ← Kembali ke Daftar
        </a>
    </div>

    <!-- Form Body -->
    <form action="<?= url('kelola-siswa/store') ?>" method="POST" id="form-create-siswa" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- BAGIAN 1: IDENTITAS PRIBADI SISWA -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs">1</span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Identitas Pribadi Siswa</h2>
                </div>
                <span class="text-[11px] text-slate-400 font-medium">* Wajib diisi</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Nama Lengkap -->
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_lengkap" required placeholder="Contoh: Muhammad Maheer Alkhalifi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- ID Siswa (Auto/Custom) -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">ID Siswa (BIP)</label>
                    <input type="text" name="id_siswa" placeholder="Auto-generate jika kosong" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        <option value="L">Laki-Laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>

                <!-- NIS -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">NIS (Nomor Induk Siswa)</label>
                    <input type="text" name="nis" placeholder="Contoh: 133907" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- NISN -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">NISN (Nasional)</label>
                    <input type="text" name="nisn" placeholder="Nomor NISN Dapodik..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" placeholder="Contoh: Palu" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tanggal Lahir</label>
                    <input type="date" name="tgl_lahir" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- NIK Siswa -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">NIK Siswa (16 Digit)</label>
                    <input type="text" name="no_nik" maxlength="20" placeholder="7271xxxxxxxxxxxx" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- No KK -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nomor Kartu Keluarga (KK)</label>
                    <input type="text" name="no_kk" maxlength="20" placeholder="7271xxxxxxxxxxxx" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- No Registrasi Akta -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">No. Registrasi Akta Lahir</label>
                    <input type="text" name="no_registrasi_akta" placeholder="Contoh: 7271-LU-xxxxxxx" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Anak Ke- -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Anak Ke-</label>
                    <input type="number" name="anak_ke" min="1" value="1" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Kebutuhan Khusus -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kebutuhan Khusus Siswa</label>
                    <input type="text" name="kebutuhan_khusus" placeholder="Tidak ada / Tunanetra / dll" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Riwayat Alergi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Riwayat Alergi (Makanan/Obat)</label>
                    <input type="text" name="nama_alergi" placeholder="Contoh: Alergi udang, susu sapi..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Tinggi & Berat Badan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tinggi Badan (cm)</label>
                    <input type="text" name="tinggi_badan" placeholder="Contoh: 120" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Berat Badan (kg)</label>
                    <input type="text" name="berat_badan" placeholder="Contoh: 25" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Asal Sekolah & Alamat Sekolah Asal -->
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Asal Sekolah Sebelumnya</label>
                    <input type="text" name="asal_sekolah" placeholder="Nama TK / SD sebelumnya jika pindahan..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: DATA AKADEMIK & PENEMPATAN KELAS -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center font-bold text-xs">2</span>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Data Akademik & Penempatan Kelas</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Jenjang Sekolah -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Jenjang Satuan Pendidikan <span class="text-rose-500">*</span></label>
                    <select name="jenjang" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        <option value="PAUD">🌱 PAUD / TK</option>
                        <option value="SD" selected>🎒 SD (Sekolah Dasar)</option>
                        <option value="SMP">📚 SMP</option>
                        <option value="SMA">🏛️ SMA</option>
                    </select>
                </div>

                <!-- Kelas -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Rombongan Belajar / Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="kelas" required placeholder="Contoh: 1A, KB, 7B, 10 MIPA" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Tahun Ajaran -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tahun Ajaran</label>
                    <input type="text" name="tahun_ajaran" value="2026/2027" placeholder="Contoh: 2026/2027" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- Semester -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Semester</label>
                    <select name="semester" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        <option value="Ganjil">Semester Ganjil</option>
                        <option value="Genap">Semester Genap</option>
                    </select>
                </div>

                <!-- Status Dapodik -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Status Sinkron Dapodik</label>
                    <select name="dapodik" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        <option value="Belum">Belum Masuk Dapodik</option>
                        <option value="Sudah">Sudah Masuk Dapodik</option>
                    </select>
                </div>

                <!-- Status Keaktifan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Status Keaktifan Siswa</label>
                    <select name="is_active" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                        <option value="1">Aktif Belajar</option>
                        <option value="0">Non-Aktif / Pindah / Lulus</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- BAGIAN 3: ALAMAT, TEMPAT TINGGAL & KONTAK -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs">3</span>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Alamat, Domisili & Kontak Siswa</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Alamat Jalan -->
                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Alamat Tempat Tinggal (Jalan/No/Kompleks)</label>
                    <input type="text" name="alamat" placeholder="Contoh: Jl. D. Talaga No. 3 B" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                </div>

                <!-- RT / RW -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">RT / RW</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="rt" placeholder="RT" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <input type="text" name="rw" placeholder="RW" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <!-- Dusun / Lingkungan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Dusun / Lingkungan</label>
                    <input type="text" name="dusun" placeholder="Nama dusun/kompleks" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Kelurahan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kelurahan / Desa</label>
                    <input type="text" name="kelurahan" placeholder="Contoh: Siranindi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Kecamatan -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kecamatan</label>
                    <input type="text" name="kecamatan" placeholder="Contoh: Palu Barat" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Kota / Kabupaten -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kota / Kabupaten</label>
                    <input type="text" name="kota" value="Kota Palu" placeholder="Kota Palu" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Provinsi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Provinsi</label>
                    <input type="text" name="provinsi" value="Sulawesi Tengah" placeholder="Sulawesi Tengah" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Kode Pos -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kode Pos</label>
                    <input type="text" name="kode_pos" placeholder="Contoh: 94221" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Tempat Tinggal -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tinggal Bersama</label>
                    <select name="tempat_tinggal" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="Bersama Orang Tua">Bersama Orang Tua</option>
                        <option value="Wali">Wali / Kerabat</option>
                        <option value="Kos">Kos</option>
                        <option value="Asrama">Asrama / Pesantren</option>
                    </select>
                </div>

                <!-- Moda Transportasi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Moda Transportasi ke Sekolah</label>
                    <select name="moda_transportasi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="Sepeda Motor">Sepeda Motor</option>
                        <option value="Mobil Pribadi">Mobil Pribadi</option>
                        <option value="Antar Jemput">Antar Jemput Sekolah</option>
                        <option value="Jalan Kaki">Jalan Kaki</option>
                        <option value="Sepeda">Sepeda</option>
                        <option value="Angkutan Umum">Angkutan Umum</option>
                    </select>
                </div>

                <!-- No HP / WhatsApp -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">No. WhatsApp / HP Ortu</label>
                    <input type="text" name="no_hp" placeholder="Contoh: 0811xxxxxxxx" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Email Siswa / Ortu</label>
                    <input type="email" name="email" placeholder="nama@email.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
                </div>
            </div>
        </div>

        <!-- BAGIAN 4: DATA ORANG TUA (AYAH & IBU) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- DATA AYAH -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-xs">👨</span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Data Lengkap Ayah Kandung</h2>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Lengkap Ayah</label>
                        <input type="text" name="nama_ayah" placeholder="Nama ayah kandung..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">NIK Ayah</label>
                            <input type="text" name="nik_ayah" maxlength="20" placeholder="NIK Ayah (16 digit)" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tahun Lahir Ayah</label>
                            <input type="text" name="tahun_lahir_ayah" maxlength="4" placeholder="Contoh: 1985" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pendidikan Terakhir</label>
                            <select name="pendidikan_ayah" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <option value="SD Sederajat">SD Sederajat</option>
                                <option value="SMP Sederajat">SMP Sederajat</option>
                                <option value="SMA Sederajat">SMA Sederajat</option>
                                <option value="D3">D3</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pekerjaan Ayah</label>
                            <input type="text" name="pekerjaan_ayah" placeholder="PNS / Swasta / Wiraswasta" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kantor / Instansi</label>
                            <input type="text" name="kantor_ayah" placeholder="Tempat kerja..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Jabatan Ayah</label>
                            <input type="text" name="jabatan_ayah" placeholder="Jabatan..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Rentang Penghasilan Bulanan</label>
                        <select name="penghasilan_ayah" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- Pilih Rentang --</option>
                            <option value="Kurang dari 1.000.000">Kurang dari 1.000.000</option>
                            <option value="1.000.000 - 1.999.999">1.000.000 - 1.999.999</option>
                            <option value="2.000.000 - 4.999.999">2.000.000 - 4.999.999</option>
                            <option value="5.000.000 - 9.999.999">5.000.000 - 9.999.999</option>
                            <option value="10.000.000 - 20.000.000">10.000.000 - 20.000.000</option>
                            <option value="Lebih dari 20.000.000">Lebih dari 20.000.000</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- DATA IBU -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-xl bg-pink-100 text-pink-800 flex items-center justify-center font-bold text-xs">👩</span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Data Lengkap Ibu Kandung</h2>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nama Lengkap Ibu</label>
                        <input type="text" name="nama_ibu" placeholder="Nama ibu kandung..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">NIK Ibu</label>
                            <input type="text" name="nik_ibu" maxlength="20" placeholder="NIK Ibu (16 digit)" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tahun Lahir Ibu</label>
                            <input type="text" name="tahun_lahir_ibu" maxlength="4" placeholder="Contoh: 1988" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pendidikan Terakhir</label>
                            <select name="pendidikan_ibu" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <option value="">-- Pilih --</option>
                                <option value="SD Sederajat">SD Sederajat</option>
                                <option value="SMP Sederajat">SMP Sederajat</option>
                                <option value="SMA Sederajat">SMA Sederajat</option>
                                <option value="D3">D3</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Pekerjaan Ibu</label>
                            <input type="text" name="pekerjaan_ibu" placeholder="Ibu Rumah Tangga / PNS / dll" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kantor / Instansi</label>
                            <input type="text" name="kantor_ibu" placeholder="Tempat kerja..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Jabatan Ibu</label>
                            <input type="text" name="jabatan_ibu" placeholder="Jabatan..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Rentang Penghasilan Bulanan</label>
                        <select name="penghasilan_ibu" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="">-- Pilih Rentang --</option>
                            <option value="Tidak Berpenghasilan">Tidak Berpenghasilan</option>
                            <option value="Kurang dari 1.000.000">Kurang dari 1.000.000</option>
                            <option value="1.000.000 - 1.999.999">1.000.000 - 1.999.999</option>
                            <option value="2.000.000 - 4.999.999">2.000.000 - 4.999.999</option>
                            <option value="5.000.000 - 9.999.999">5.000.000 - 9.999.999</option>
                            <option value="Lebih dari 10.000.000">Lebih dari 10.000.000</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Bar -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="<?= url('kelola-siswa') ?>" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Batal
            </a>
            <button type="submit" class="px-7 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-500/25 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <span>Simpan Data Siswa</span>
            </button>
        </div>
    </form>
</div>
