<?php
/**
 * Modal Verifikasi & Pengembalian Berkas Calon Siswa (Lengkap Dokumen & Biodata)
 * SPMB SIT Bina Insan Palu
 * Digunakan bersama oleh: Siswa Baru, Pindahan, Detail Siswa
 */
?>
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL VERIFIKASI & PENGEMBALIAN BERKAS (LENGKAP DOKUMEN & BIODATA)          -->
<!-- ═════════════════════════════════════════════════════════════════════════ -->
<div id="modalVerifikasiBerkas" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-2 sm:p-4 overflow-y-auto">
    <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col my-auto max-h-[94vh] animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Modal Header -->
        <div class="px-6 py-4.5 bg-gradient-to-r from-sky-700 via-blue-700 to-indigo-800 text-white flex items-center justify-between shrink-0 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center text-white shrink-0 border border-white/20 text-lg shadow-inner">
                    📁
                </div>
                <div>
                    <h3 class="text-base font-extrabold tracking-tight flex items-center gap-2">
                        <span>Verifikasi & Pengembalian Berkas Pendaftar</span>
                    </h3>
                    <p class="text-[11px] text-sky-100/90 mt-0.5">
                        Pemeriksaan seluruh data isian orang tua, validasi dokumen upload asli, dan konfirmasi Surat Kesanggupan
                    </p>
                </div>
            </div>
            <button type="button" onclick="tutupModalVerifikasi()" class="p-1.5 rounded-xl text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Loading State -->
        <div id="modalVerifikasiLoading" class="p-16 text-center space-y-3">
            <div class="w-10 h-10 border-4 border-sky-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-xs font-semibold text-slate-500">Memuat seluruh berkas dan data isian calon siswa...</p>
        </div>

        <!-- Modal Form Body -->
        <form id="formVerifikasiBerkas" action="<?= url('kelola-spmb/verifikasi-berkas/submit') ?>" method="POST" enctype="multipart/form-data" class="hidden flex-col flex-1 overflow-hidden" onsubmit="submitFormVerifikasiBerkas(event)">
            <?= CSRF::field() ?>
            <input type="hidden" name="pendaftar_id" id="vb_pendaftar_id" value="">

            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5 text-xs text-slate-700 custom-scrollbar">
                
                <!-- 1. Header Ringkasan Identitas Pendaftar -->
                <div class="p-4 bg-gradient-to-br from-slate-50 to-sky-50/60 rounded-2xl border border-sky-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-[11px] font-bold px-2.5 py-0.5 rounded-md bg-sky-100 text-sky-800" id="vb_nomor_pendaftaran">-</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-200 text-slate-800" id="vb_jenjang">-</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-white text-slate-600 border border-slate-200" id="vb_gelombang">-</span>
                        </div>
                        <h4 class="text-base font-extrabold text-slate-900" id="vb_nama_lengkap">-</h4>
                        <div class="text-[11px] text-slate-500" id="vb_kategori_info">-</div>
                    </div>
                    <div class="text-left sm:text-right sm:border-l sm:border-slate-200 sm:pl-4 space-y-1">
                        <div class="text-[10px] text-slate-400 uppercase font-semibold">Status Berkas Saat Ini</div>
                        <div id="vb_current_status_badge">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                ⏳ Belum Dikembalikan
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2. SECTION UTAMA: Berkas Dokumen Pendukung yang Diunggah Orang Tua -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4.5 space-y-3.5 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-100 pb-2.5">
                        <div>
                            <span class="font-extrabold text-slate-800 flex items-center gap-1.5 text-xs">
                                <span>📂</span>
                                <span>Berkas Dokumen Pendukung yang Diunggah Orang Tua:</span>
                            </span>
                            <p class="text-[10.5px] text-slate-500 mt-0.5">
                                Klik tombol <strong>"Lihat File"</strong> untuk memvalidasi langsung kesesuaian dokumen asli dengan file upload
                            </p>
                        </div>
                        <span class="text-[10px] font-semibold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-100 shrink-0">
                            Format JPG / PNG / PDF
                        </span>
                    </div>

                    <!-- Grid 6 Berkas Dokumen -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 text-xs">
                        <!-- 1. Screenshot Dapodik -->
                        <div id="vb_card_dapodik" class="p-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 text-center space-y-1.5 flex flex-col justify-between"></div>

                        <!-- 2. Kartu Keluarga -->
                        <div id="vb_card_kk" class="p-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 text-center space-y-1.5 flex flex-col justify-between"></div>

                        <!-- 3. Akta Lahir -->
                        <div id="vb_card_akta" class="p-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 text-center space-y-1.5 flex flex-col justify-between"></div>

                        <!-- 4. KTP Ayah -->
                        <div id="vb_card_ktp_ayah" class="p-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 text-center space-y-1.5 flex flex-col justify-between"></div>

                        <!-- 5. KTP Ibu -->
                        <div id="vb_card_ktp_ibu" class="p-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 text-center space-y-1.5 flex flex-col justify-between"></div>

                        <!-- 6. Pas Foto Siswa -->
                        <div id="vb_card_foto" class="p-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 text-center space-y-1.5 flex flex-col justify-between"></div>
                    </div>
                </div>

                <!-- 3. SECTION LENGKAP: Seluruh Data Isian Formulir Orang Tua -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                    <!-- Section Header with Collapse Toggle -->
                    <div class="p-4 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between cursor-pointer" onclick="toggleDetailData()">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📋</span>
                            <div>
                                <h5 class="font-extrabold text-slate-800 text-xs">
                                    Seluruh Rincian Data yang Diisi Orang Tua (Pengecekan Ulang)
                                </h5>
                                <p class="text-[10.5px] text-slate-500">
                                    Data lengkap biodata calon siswa, riwayat asal sekolah, data ayah & ibu kandung
                                </p>
                            </div>
                        </div>
                        <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold text-[10.5px] hover:bg-slate-100 shadow-2xs transition-colors">
                            <span id="vb_toggle_detail_icon">▼</span>
                            <span id="vb_toggle_detail_text">Tutup Rincian</span>
                        </button>
                    </div>

                    <!-- Collapsible Content Body -->
                    <div id="vb_detail_content_box" class="p-4 sm:p-5 space-y-4">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            
                            <!-- Sub-Card A: Data Calon Siswa -->
                            <div class="p-4 rounded-2xl bg-slate-50/60 border border-slate-200/80 space-y-3">
                                <div class="flex items-center gap-1.5 pb-2 border-b border-slate-200 text-sky-800 font-extrabold text-xs">
                                    <span>👤</span>
                                    <span>Biodata Lengkap Calon Siswa</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2.5 text-[11px]">
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Nama Lengkap & Panggilan:</span>
                                        <span class="font-bold text-slate-800" id="vb_det_nama">-</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Jenis Kelamin & Agama:</span>
                                        <span class="font-semibold text-slate-800" id="vb_det_jk_agama">-</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">NIK Siswa:</span>
                                        <span class="font-mono font-bold text-slate-800" id="vb_det_nik">-</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">No. Kartu Keluarga (KK):</span>
                                        <span class="font-mono font-bold text-slate-800" id="vb_det_no_kk">-</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">NISN:</span>
                                        <span class="font-mono font-bold text-slate-800" id="vb_det_nisn">-</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Tempat & Tanggal Lahir:</span>
                                        <span class="font-semibold text-slate-800" id="vb_det_ttl">-</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Urutan Anak:</span>
                                        <span class="font-semibold text-slate-800" id="vb_det_anak_ke">-</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px]">Fisik & Golongan Darah:</span>
                                        <span class="font-semibold text-slate-800" id="vb_det_fisik">-</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-slate-400 block text-[10px]">Riwayat Penyakit:</span>
                                        <span class="font-semibold text-slate-800" id="vb_det_penyakit">-</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-slate-400 block text-[10px]">Asal Sekolah & NPSN:</span>
                                        <span class="font-semibold text-slate-800" id="vb_det_sekolah">-</span>
                                    </div>
                                    <div class="col-span-2 pt-1 border-t border-slate-200">
                                        <span class="text-slate-400 block text-[10px]">Alamat Lengkap & Tempat Tinggal:</span>
                                        <span class="font-semibold text-slate-800 leading-relaxed" id="vb_det_alamat">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Sub-Card B: Data Orang Tua (Ayah & Ibu & Wali) -->
                            <div class="space-y-3">
                                <!-- Data Ayah -->
                                <div class="p-3.5 rounded-2xl bg-sky-50/50 border border-sky-100 text-[11px] space-y-1.5">
                                    <div class="font-extrabold text-sky-900 flex items-center justify-between border-b border-sky-100 pb-1 text-xs">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-sky-500"></span> Data Ayah Kandung
                                        </span>
                                        <span id="vb_det_ayah_status" class="text-[10px] font-semibold text-sky-700">-</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Nama:</span>
                                            <span class="font-bold text-slate-800" id="vb_det_ayah_nama">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">NIK:</span>
                                            <span class="font-mono font-semibold text-slate-700" id="vb_det_ayah_nik">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Pendidikan / Lahir:</span>
                                            <span class="font-semibold text-slate-700" id="vb_det_ayah_pendidikan">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Pekerjaan & Kantor:</span>
                                            <span class="font-semibold text-slate-700" id="vb_det_ayah_pekerjaan">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Penghasilan:</span>
                                            <span class="font-semibold text-slate-700" id="vb_det_ayah_penghasilan">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">No. HP / WA:</span>
                                            <span class="font-bold text-sky-800" id="vb_det_ayah_hp">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Ibu -->
                                <div class="p-3.5 rounded-2xl bg-rose-50/50 border border-rose-100 text-[11px] space-y-1.5">
                                    <div class="font-extrabold text-rose-900 flex items-center justify-between border-b border-rose-100 pb-1 text-xs">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span> Data Ibu Kandung
                                        </span>
                                        <span id="vb_det_ibu_status" class="text-[10px] font-semibold text-rose-700">-</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Nama:</span>
                                            <span class="font-bold text-slate-800" id="vb_det_ibu_nama">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">NIK:</span>
                                            <span class="font-mono font-semibold text-slate-700" id="vb_det_ibu_nik">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Pendidikan / Lahir:</span>
                                            <span class="font-semibold text-slate-700" id="vb_det_ibu_pendidikan">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Pekerjaan & Kantor:</span>
                                            <span class="font-semibold text-slate-700" id="vb_det_ibu_pekerjaan">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">Penghasilan:</span>
                                            <span class="font-semibold text-slate-700" id="vb_det_ibu_penghasilan">-</span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block text-[10px]">No. HP / WA:</span>
                                            <span class="font-bold text-rose-800" id="vb_det_ibu_hp">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Wali (Opsional) -->
                                <div id="vb_det_wali_box" class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[11px] hidden">
                                    <div class="font-bold text-slate-700 pb-1 border-b border-slate-200">Data Wali Murid</div>
                                    <div class="mt-1" id="vb_det_wali_text">-</div>
                                </div>
                            </div>
                        </div>

                        <!-- Checklist Kesesuaian Data -->
                        <label class="flex items-start gap-3 p-3.5 bg-emerald-50/80 hover:bg-emerald-50 rounded-2xl border border-emerald-300/80 cursor-pointer transition-colors shadow-2xs">
                            <input type="checkbox" name="is_data_sesuai" id="vb_is_data_sesuai" value="1" 
                                   class="w-4 h-4 mt-0.5 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <div class="text-xs">
                                <div class="font-bold text-emerald-950 flex items-center gap-1.5">
                                    <span>✓</span>
                                    <span>Seluruh Data Isian Pendaftar Sesuai dengan Dokumen Asli (KK, Akta Lahir, KTP, dan Dapodik)</span>
                                </div>
                                <div class="text-[11px] text-emerald-800/85 mt-0.5 leading-relaxed">
                                    Centang kotak ini jika NIK, Nama Lengkap, Tempat/Tanggal Lahir, identitas orang tua, dan dokumen telah diverifikasi sesuai berkas fisik.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 4. Status Surat Komitmen Orang Tua -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-800 flex items-center gap-1.5 text-xs">
                            <span>📜</span>
                            <span>Status Surat Pernyataan Komitmen Unit:</span>
                        </span>
                        <div id="vb_status_komitmen_badge">-</div>
                    </div>
                    <div id="vb_komitmen_actions" class="text-[11px] text-slate-500 flex flex-wrap items-center gap-2 pt-1">
                        <!-- Injected via JS: tombol cetak komitmen / link kirim WA -->
                    </div>
                </div>

                <!-- 5. Upload Surat Kesanggupan & Status Berkas -->
                <div class="bg-white rounded-2xl border border-slate-200 p-4.5 space-y-4 shadow-2xs">
                    <div class="border-b border-slate-100 pb-2">
                        <span class="font-bold text-slate-800 flex items-center gap-1.5 text-xs">
                            <span>📄</span>
                            <span>Dokumen Surat Kesanggupan & Status Pengembalian:</span>
                        </span>
                    </div>

                    <!-- Upload File Surat Kesanggupan -->
                    <div class="space-y-1.5">
                        <label class="block font-bold text-slate-800">
                            Upload File Surat Kesanggupan <span class="text-slate-400 font-normal">(PDF / JPG / PNG / WEBP - Maks. 5 MB)</span>
                        </label>
                        <input type="file" 
                               name="surat_kesanggupan" 
                               id="vb_file_kesanggupan"
                               accept=".pdf, image/png, image/jpeg, image/jpg, image/webp"
                               class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer border border-slate-200 bg-white rounded-xl p-1.5 focus:outline-none">
                        
                        <!-- Existing File Container -->
                        <div id="vb_existing_kesanggupan_box" class="hidden mt-2 p-3 bg-sky-50/80 rounded-xl border border-sky-200 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sky-900 font-semibold text-xs">
                                <span>📎</span>
                                <span>Surat Kesanggupan Telah Diunggah</span>
                            </div>
                            <a id="vb_link_kesanggupan" href="#" target="_blank" 
                               class="px-3 py-1 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-[11px] transition-colors flex items-center gap-1">
                                <span>👁️</span>
                                <span>Lihat File</span>
                            </a>
                        </div>
                    </div>

                    <!-- Pilihan Status Berkas -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">
                                Status Pengembalian Berkas <span class="text-rose-500">*</span>
                            </label>
                            <select name="status_berkas" id="vb_status_berkas" required 
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white font-bold text-slate-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none text-xs">
                                <option value="Lengkap">✓ Lengkap / Berkas Diterima</option>
                                <option value="Perlu Perbaikan">⚠️ Perlu Perbaikan / Kurang Lengkap</option>
                                <option value="Belum Dikembalikan">⏳ Belum Dikembalikan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">
                                Petugas Verifikasi Loket
                            </label>
                            <input type="text" 
                                   id="vb_petugas" 
                                   value="<?= e($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Petugas SPMB') ?>" 
                                   readonly 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-600 text-xs outline-none">
                        </div>
                    </div>

                    <!-- Catatan Verifikasi -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            Catatan Petugas / Kelengkapan Berkas
                        </label>
                        <textarea name="catatan_berkas" 
                                  id="vb_catatan_berkas" 
                                  rows="2" 
                                  placeholder="Contoh: Dokumen asli telah diperiksa. Surat kesanggupan telah ditandatangani bermeterai oleh orang tua..."
                                  class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-100 outline-none"></textarea>
                    </div>

                    <!-- Tanggal Verifikasi Riwayat -->
                    <div id="vb_riwayat_verifikasi" class="text-[10.5px] text-slate-400 italic hidden">
                        Terakhir diverifikasi pada: <span id="vb_tgl_kembali_text">-</span> oleh <span id="vb_petugas_text">-</span>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Buttons -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                <button type="button" onclick="tutupModalVerifikasi()" 
                        class="px-4 py-2.5 rounded-xl bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition-colors">
                    Batal
                </button>
                <button type="submit" id="btnSubmitVerifikasi"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md shadow-blue-600/25 transition-all transform hover:-translate-y-0.5">
                    <span>💾</span>
                    <span>Simpan & Konfirmasi Berkas</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Global Dropdown Titik 3 Management (Bulletproof Fixed Positioning) ──────────
function toggleActionMenu(id, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    const targetMenu = document.getElementById('menu-' + id);
    const button = document.getElementById('btn-action-' + id) 
                || document.getElementById('btn-action-pindahan-' + id) 
                || (event ? (event.currentTarget || event.target.closest('button')) : null);
    if (!targetMenu || !button) return;

    const isHidden = targetMenu.classList.contains('hidden');
    closeAllActionMenus();

    if (isHidden) {
        // Pindahkan ke document.body agar tidak pernah terpotong oleh overflow tabel/card
        if (targetMenu.parentNode !== document.body) {
            document.body.appendChild(targetMenu);
        }

        targetMenu.classList.remove('hidden');
        targetMenu.style.position = 'fixed';
        targetMenu.style.zIndex = '99999';
        targetMenu.style.width = '230px';

        const btnRect = button.getBoundingClientRect();
        const menuHeight = targetMenu.offsetHeight || 330;
        const menuWidth = targetMenu.offsetWidth || 230;

        const spaceBelow = window.innerHeight - btnRect.bottom;
        const spaceAbove = btnRect.top;

        // Tentukan apakah buka ke bawah atau ke atas
        if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
            // Buka ke atas (Upwards)
            let topPos = btnRect.top - menuHeight - 6;
            if (topPos < 10) {
                topPos = 10;
                targetMenu.style.maxHeight = Math.max(160, btnRect.top - 16) + 'px';
                targetMenu.style.overflowY = 'auto';
            } else {
                targetMenu.style.maxHeight = '';
                targetMenu.style.overflowY = '';
            }
            targetMenu.style.top = topPos + 'px';
        } else {
            // Buka ke bawah (Downwards)
            let topPos = btnRect.bottom + 6;
            if (topPos + menuHeight > window.innerHeight) {
                targetMenu.style.maxHeight = Math.max(160, window.innerHeight - topPos - 12) + 'px';
                targetMenu.style.overflowY = 'auto';
            } else {
                targetMenu.style.maxHeight = '';
                targetMenu.style.overflowY = '';
            }
            targetMenu.style.top = topPos + 'px';
        }

        // Posisi Horizontal: Rata kanan dengan tombol titik 3
        let rightPos = window.innerWidth - btnRect.right;
        if (rightPos < 10) rightPos = 10;
        if (rightPos + menuWidth > window.innerWidth) {
            targetMenu.style.right = '10px';
        } else {
            targetMenu.style.right = rightPos + 'px';
        }
        targetMenu.style.left = 'auto';
    }
}

function closeAllActionMenus() {
    document.querySelectorAll('.action-menu-dropdown').forEach(function(el) {
        el.classList.add('hidden');
        el.style.maxHeight = '';
        el.style.overflowY = '';
    });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-menu-container') && !e.target.closest('.action-menu-dropdown')) {
        closeAllActionMenus();
    }
});

// Tutup menu jika user scroll atau resize window agar posisi menu tidak melayang terpisah
window.addEventListener('scroll', function() {
    closeAllActionMenus();
}, { passive: true });

window.addEventListener('resize', function() {
    closeAllActionMenus();
}, { passive: true });

// ── Helper Toggle Detail Data Box ───────────────────────────────────────────
function toggleDetailData() {
    const box = document.getElementById('vb_detail_content_box');
    const icon = document.getElementById('vb_toggle_detail_icon');
    const text = document.getElementById('vb_toggle_detail_text');
    if (!box) return;

    if (box.classList.contains('hidden')) {
        box.classList.remove('hidden');
        if (icon) icon.textContent = '▼';
        if (text) text.textContent = 'Tutup Rincian';
    } else {
        box.classList.add('hidden');
        if (icon) icon.textContent = '▶';
        if (text) text.textContent = 'Tampilkan Rincian';
    }
}

// Helper Render Kartu Dokumen Upload
function renderDocCard(containerId, title, icon, fileUrl) {
    const el = document.getElementById(containerId);
    if (!el) return;
    if (fileUrl) {
        el.className = 'p-3 rounded-2xl border border-emerald-200 bg-emerald-50/40 text-center space-y-1.5 flex flex-col justify-between shadow-2xs hover:border-emerald-300 transition-all';
        el.innerHTML = `
            <div>
                <div class="text-2xl">${icon}</div>
                <div class="font-bold text-slate-800 text-[11px] mt-0.5 leading-tight">${title}</div>
                <div class="mt-1"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">✓ Terunggah</span></div>
            </div>
            <a href="${fileUrl}" target="_blank" class="w-full inline-flex items-center justify-center gap-1 py-1.5 px-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[10.5px] font-bold transition-all shadow-xs">
                <span>👁️</span><span>Lihat File</span>
            </a>
        `;
    } else {
        el.className = 'p-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/70 text-center space-y-1.5 flex flex-col justify-between';
        el.innerHTML = `
            <div>
                <div class="text-2xl opacity-40">${icon}</div>
                <div class="font-semibold text-slate-600 text-[11px] mt-0.5 leading-tight">${title}</div>
                <div class="mt-1"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9.5px] font-medium bg-slate-200 text-slate-600">⏳ Belum Diunggah</span></div>
            </div>
            <span class="text-[10px] text-slate-400 italic py-1">Tidak ada file</span>
        `;
    }
}

// ── Modal Verifikasi Berkas Handlers ─────────────────────────────────────────
function bukaModalVerifikasi(id) {
    closeAllActionMenus();
    const modal = document.getElementById('modalVerifikasiBerkas');
    const loading = document.getElementById('modalVerifikasiLoading');
    const form = document.getElementById('formVerifikasiBerkas');

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    loading.classList.remove('hidden');
    form.classList.add('hidden');
    form.classList.remove('flex');

    // Fetch data calon siswa via AJAX
    fetch('<?= url("kelola-spmb/verifikasi-berkas/get/") ?>' + id, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success || !res.data) {
            alert(res.message || 'Gagal memuat data pendaftar.');
            tutupModalVerifikasi();
            return;
        }

        const d = res.data;
        document.getElementById('vb_pendaftar_id').value = d.id;
        document.getElementById('vb_nomor_pendaftaran').textContent = d.nomor_pendaftaran;
        document.getElementById('vb_jenjang').textContent = d.jenjang;
        document.getElementById('vb_gelombang').textContent = d.gelombang;
        document.getElementById('vb_nama_lengkap').textContent = d.nama_lengkap;
        document.getElementById('vb_kategori_info').textContent = d.kategori_pendaftar + ' • ' + d.jenis_pendaftaran;

        // Render 6 Berkas Dokumen Upload Asli
        renderDocCard('vb_card_dapodik', 'Screenshot Dapodik', '📸', d.file_screenshot_dapodik_url);
        renderDocCard('vb_card_kk', 'Kartu Keluarga (KK)', '📄', d.berkas_kk_url);
        renderDocCard('vb_card_akta', 'Akta Kelahiran', '📜', d.berkas_akta_url);
        renderDocCard('vb_card_ktp_ayah', 'KTP Ayah', '🪪', d.berkas_ktp_ayah_url);
        renderDocCard('vb_card_ktp_ibu', 'KTP Ibu', '🪪', d.berkas_ktp_ibu_url);
        renderDocCard('vb_card_foto', 'Pas Foto Calon Siswa', '🖼️', d.foto_url);

        // Render Rincian Biodata Calon Siswa
        document.getElementById('vb_det_nama').textContent = d.nama_lengkap + (d.nama_panggilan && d.nama_panggilan !== '-' ? ' (' + d.nama_panggilan + ')' : '');
        document.getElementById('vb_det_jk_agama').textContent = d.jenis_kelamin + ' • ' + d.agama;
        document.getElementById('vb_det_nik').textContent = d.nik;
        document.getElementById('vb_det_no_kk').textContent = d.no_kk;
        document.getElementById('vb_det_nisn').textContent = d.nisn;
        document.getElementById('vb_det_ttl').textContent = d.tempat_lahir + ', ' + d.tanggal_lahir;
        document.getElementById('vb_det_anak_ke').textContent = 'Anak ke-' + d.anak_ke + ' dari ' + d.jumlah_saudara + ' bersaudara' + (d.status_keluarga && d.status_keluarga !== '-' ? ' (' + d.status_keluarga + ')' : '');
        document.getElementById('vb_det_fisik').textContent = 'Gol. Darah: ' + d.golongan_darah + ' • TB: ' + (d.tinggi_badan !== '-' ? d.tinggi_badan + ' cm' : '-') + ' • BB: ' + (d.berat_badan !== '-' ? d.berat_badan + ' kg' : '-');
        document.getElementById('vb_det_penyakit').textContent = d.riwayat_penyakit || 'Tidak ada riwayat penyakit berat';
        document.getElementById('vb_det_sekolah').textContent = d.asal_sekolah + (d.npsn_asal && d.npsn_asal !== '-' ? ' (NPSN: ' + d.npsn_asal + ')' : '') + (d.alamat_sekolah_asal && d.alamat_sekolah_asal !== '-' ? ' - ' + d.alamat_sekolah_asal : '');
        
        // Alamat lengkap
        let fullAlamat = d.alamat;
        if (d.rt && d.rt !== '-' || d.rw && d.rw !== '-') fullAlamat += ' (RT ' + d.rt + ' / RW ' + d.rw + ')';
        if (d.kelurahan && d.kelurahan !== '-') fullAlamat += ', Kel. ' + d.kelurahan;
        if (d.kecamatan && d.kecamatan !== '-') fullAlamat += ', Kec. ' + d.kecamatan;
        if (d.kota && d.kota !== '-') fullAlamat += ', ' + d.kota;
        if (d.tempat_tinggal && d.tempat_tinggal !== '-') fullAlamat += ' [Tinggal: ' + d.tempat_tinggal + ']';
        document.getElementById('vb_det_alamat').textContent = fullAlamat;

        // Render Rincian Ayah
        document.getElementById('vb_det_ayah_nama').textContent = d.nama_ayah;
        document.getElementById('vb_det_ayah_nik').textContent = d.nik_ayah;
        document.getElementById('vb_det_ayah_status').textContent = d.status_ayah + (d.tahun_lahir_ayah && d.tahun_lahir_ayah !== '-' ? ' (Lahir ' + d.tahun_lahir_ayah + ')' : '');
        document.getElementById('vb_det_ayah_pendidikan').textContent = d.pendidikan_ayah;
        document.getElementById('vb_det_ayah_pekerjaan').textContent = d.pekerjaan_ayah + (d.kantor_ayah && d.kantor_ayah !== '-' ? ' di ' + d.kantor_ayah : '') + (d.jabatan_ayah && d.jabatan_ayah !== '-' ? ' (' + d.jabatan_ayah + ')' : '');
        document.getElementById('vb_det_ayah_penghasilan').textContent = d.penghasilan_ayah;
        document.getElementById('vb_det_ayah_hp').textContent = d.no_hp_ayah;

        // Render Rincian Ibu
        document.getElementById('vb_det_ibu_nama').textContent = d.nama_ibu;
        document.getElementById('vb_det_ibu_nik').textContent = d.nik_ibu;
        document.getElementById('vb_det_ibu_status').textContent = d.status_ibu + (d.tahun_lahir_ibu && d.tahun_lahir_ibu !== '-' ? ' (Lahir ' + d.tahun_lahir_ibu + ')' : '');
        document.getElementById('vb_det_ibu_pendidikan').textContent = d.pendidikan_ibu;
        document.getElementById('vb_det_ibu_pekerjaan').textContent = d.pekerjaan_ibu + (d.kantor_ibu && d.kantor_ibu !== '-' ? ' di ' + d.kantor_ibu : '') + (d.jabatan_ibu && d.jabatan_ibu !== '-' ? ' (' + d.jabatan_ibu + ')' : '');
        document.getElementById('vb_det_ibu_penghasilan').textContent = d.penghasilan_ibu;
        document.getElementById('vb_det_ibu_hp').textContent = d.no_hp_ibu;

        // Render Wali jika ada
        const waliBox = document.getElementById('vb_det_wali_box');
        if (d.nama_wali && d.nama_wali !== '-' && d.nama_wali.trim() !== '') {
            waliBox.classList.remove('hidden');
            document.getElementById('vb_det_wali_text').textContent = d.nama_wali + ' (' + d.hubungan_wali + ') • NIK: ' + d.nik_wali + ' • Pekerjaan: ' + d.pekerjaan_wali + ' • No. HP: ' + d.no_hp_wali;
        } else {
            waliBox.classList.add('hidden');
        }

        // Checklist Kesesuaian Data
        document.getElementById('vb_is_data_sesuai').checked = (d.is_data_sesuai == 1);

        // Current Status Badge
        const badgeEl = document.getElementById('vb_current_status_badge');
        if (d.status_berkas === 'Lengkap') {
            badgeEl.innerHTML = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">✓ Lengkap / Diterima</span>';
        } else if (d.status_berkas === 'Perlu Perbaikan') {
            badgeEl.innerHTML = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">⚠️ Perlu Perbaikan</span>';
        } else {
            badgeEl.innerHTML = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">⏳ Belum Dikembalikan</span>';
        }

        // Status TTD Komitmen
        const komitmenBadge = document.getElementById('vb_status_komitmen_badge');
        const komitmenActions = document.getElementById('vb_komitmen_actions');
        if (d.status_ttd_komitmen === 'Sudah TTD') {
            komitmenBadge.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">✓ Sudah TTD Komitmen' + (d.ttd_submitted_at ? (' (' + d.ttd_submitted_at + ')') : '') + '</span>';
            komitmenActions.innerHTML = '<a href="' + d.link_cetak_komitmen + '" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-bold underline"><span>📜</span> Lihat Dokumen Komitmen Bertanda Tangan</a>';
        } else {
            komitmenBadge.innerHTML = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-300 animate-pulse">⚠️ Belum TTD Komitmen</span>';
            komitmenActions.innerHTML = '<span class="text-slate-500">Orang tua belum menandatangani surat komitmen unit.</span> ' +
                '<button type="button" onclick="salinLinkKomitmen(\'' + d.link_ttd_public + '\')" class="inline-flex items-center gap-1 text-sky-600 hover:text-sky-800 font-bold underline ml-1"><span>🔗</span> Salin Link TTD</button>';
        }

        // Existing Surat Kesanggupan
        const existBox = document.getElementById('vb_existing_kesanggupan_box');
        const linkKesanggupan = document.getElementById('vb_link_kesanggupan');
        document.getElementById('vb_file_kesanggupan').value = '';
        if (d.surat_kesanggupan) {
            existBox.classList.remove('hidden');
            linkKesanggupan.href = d.surat_kesanggupan;
        } else {
            existBox.classList.add('hidden');
        }

        // Form Fields
        document.getElementById('vb_status_berkas').value = d.status_berkas || 'Lengkap';
        document.getElementById('vb_catatan_berkas').value = d.catatan_berkas || '';

        // Riwayat Verifikasi
        const riwayatBox = document.getElementById('vb_riwayat_verifikasi');
        if (d.tanggal_kembali_berkas) {
            riwayatBox.classList.remove('hidden');
            document.getElementById('vb_tgl_kembali_text').textContent = d.tanggal_kembali_berkas;
            document.getElementById('vb_petugas_text').textContent = d.petugas_verifikasi_berkas || 'Petugas SPMB';
        } else {
            riwayatBox.classList.add('hidden');
        }

        loading.classList.add('hidden');
        form.classList.remove('hidden');
        form.classList.add('flex');
    })
    .catch(err => {
        alert('Terjadi kesalahan saat memuat data: ' + err.message);
        tutupModalVerifikasi();
    });
}

function tutupModalVerifikasi() {
    const modal = document.getElementById('modalVerifikasiBerkas');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('formVerifikasiBerkas').reset();
}

function submitFormVerifikasiBerkas(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitVerifikasi');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span> Menyimpan...';

    const form = document.getElementById('formVerifikasiBerkas');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (res.success) {
            tutupModalVerifikasi();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                alert(res.message);
                window.location.reload();
            }
        } else {
            alert(res.message || 'Gagal menyimpan verifikasi berkas.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Terjadi kesalahan jaringan: ' + err.message);
    });
}
</script>
