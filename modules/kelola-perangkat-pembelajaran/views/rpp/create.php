<?php
/**
 * RPP / Modul Ajar - Create View
 */
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Buat RPP / Modul Ajar</h1>
            <p class="text-xs sm:text-sm text-slate-500">Penyusunan rencana pelaksanaan pembelajaran Kurikulum Merdeka & K13</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/rpp') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            ← Kembali
        </a>
    </div>

    <form method="POST" action="<?= url('kelola-perangkat-pembelajaran/rpp/store') ?>" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- I. IDENTITAS MODUL / RPP & UNIT -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> I. Informasi Umum & Identitas Modul
            </h2>

            <!-- Searchable Live Search Guru Picker (At Atas) -->
            <?php
            $picker_label = 'Guru Pengampu / Penyusun Modul Ajar';
            $picker_accent = 'rose';
            $selected_guru_id = old('guru_id');
            $selected_guru_nama = old('guru_nama');
            $selected_guru_nip = old('guru_nip');
            include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/guru_picker.php';
            ?>

            <!-- Visual Unit Selector -->
            <div class="pt-2">
                <?php $selectedUnit = old('unit', $_GET['unit'] ?? ($teacherUnit ?? 'SD')); ?>
                <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Unit Satuan Pendidikan <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($unit_list as $uKey => $uInfo): 
                        $isChecked = ($selectedUnit === $uKey);
                    ?>
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-rose-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-rose-600 bg-rose-50/40 ring-2 ring-rose-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio" onchange="updateUnitSelection(this)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl mb-1.5 <?= $uInfo['bg_soft'] ?>">
                                <?= $uInfo['icon'] ?>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></span>
                            <span class="text-[10px] text-slate-500 text-center leading-tight mt-0.5"><?= e($uInfo['name']) ?></span>
                            <div class="unit-check-indicator absolute top-2 right-2 <?= $isChecked ? 'block text-rose-600' : 'hidden' ?>">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul RPP / Modul Ajar <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" required placeholder="Contoh: Modul Ajar Biologi Fase E - Struktur & Fungsi Sel" value="<?= old('judul') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="mata_pelajaran" required placeholder="Contoh: Biologi, Matematika..." value="<?= old('mata_pelajaran') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat / Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="tingkat_kelas" required placeholder="Contoh: Kelas X (Sepuluh)" value="<?= old('tingkat_kelas') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fase Kurikulum</label>
                    <select name="fase" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                        <option value="">Pilih Fase (Opsional)</option>
                        <option value="A (SD 1-2)">Fase A (SD Kelas 1-2)</option>
                        <option value="B (SD 3-4)">Fase B (SD Kelas 3-4)</option>
                        <option value="C (SD 5-6)">Fase C (SD Kelas 5-6)</option>
                        <option value="D (SMP 7-9)">Fase D (SMP Kelas 7-9)</option>
                        <option value="E (SMA 10)" selected>Fase E (SMA Kelas 10)</option>
                        <option value="F (SMA 11-12)">Fase F (SMA Kelas 11-12)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($filter_ta == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                        <option value="Ganjil" <?= $filter_semester === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                        <option value="Genap" <?= $filter_semester === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pertemuan Ke-</label>
                    <input type="text" name="pertemuan_ke" placeholder="Contoh: 1 dan 2 (2 Pertemuan)" value="1" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alokasi Waktu <span class="text-rose-500">*</span></label>
                    <input type="text" name="alokasi_waktu" required placeholder="Contoh: 2 x 45 Menit (2 JP)" value="2 x 45 Menit (2 JP)" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Model Pembelajaran</label>
                    <select name="model_pembelajaran" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                        <option value="Problem Based Learning (PBL)">Problem Based Learning (PBL)</option>
                        <option value="Project Based Learning (PjBL)">Project Based Learning (PjBL)</option>
                        <option value="Discovery / Inquiry Learning">Discovery / Inquiry Learning</option>
                        <option value="Cooperative Learning">Cooperative Learning</option>
                        <option value="Differentiated Instruction">Diferensiasi (Differentiated)</option>
                        <option value="Direct Instruction">Direct Instruction / Ceramah Interaktif</option>
                    </select>
                </div>
            </div>

            <!-- Dimensi Profil Pelajar Pancasila -->
            <div class="pt-3 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Dimensi Profil Pelajar Pancasila (P3):</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="profil_pancasila[]" value="Beriman, Bertakwa & Berakhlak Mulia" class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-slate-700 font-medium">Beriman & Bertakwa</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="profil_pancasila[]" value="Bernalar Kritis" checked class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-slate-700 font-medium">Bernalar Kritis</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="profil_pancasila[]" value="Kreatif" checked class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-slate-700 font-medium">Kreatif</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="profil_pancasila[]" value="Gotong Royong" checked class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-slate-700 font-medium">Gotong Royong</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="profil_pancasila[]" value="Mandiri" checked class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-slate-700 font-medium">Mandiri</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="profil_pancasila[]" value="Berkebinekaan Global" class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="text-slate-700 font-medium">Berkebinekaan Global</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Sarana, Prasarana & Media Pembelajaran</label>
                <input type="text" name="sarana_prasarana" placeholder="Contoh: Laptop, Proyektor LCD, Lembar Kerja Peserta Didik (LKPD), Jaringan Internet..." value="Laptop, Proyektor LCD, LKPD, Buku Paket Siswa" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
            </div>
        </div>

        <!-- II. KOMPONEN INTI -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> II. Komponen Inti Pembelajaran
            </h2>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tujuan Pembelajaran (TP) <span class="text-rose-500">*</span></label>
                <textarea name="tujuan_pembelajaran" rows="3" required placeholder="Contoh: Peserta didik mampu menganalisis struktur dan fungsi komponen sel serta menyajikan hasil studi komparasi sel hewan dan tumbuhan melalui diskusi kelompok secara kritis." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">1. Peserta didik mampu mengidentifikasi komponen dan organel sel melalui pengamatan gambar/video secara tepat.
2. Peserta didik mampu membedakan struktur sel hewan dan sel tumbuhan melalui kegiatan diskusi LKPD.</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pemahaman Bermakna</label>
                    <textarea name="pemahaman_bermakna" rows="3" placeholder="Pemahaman yang didapatkan siswa setelah belajar..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">Sel merupakan unit fungsional terkecil dari kehidupan. Memahami struktur sel membantu manusia memahami fungsi organ tubuh dan penanganan penyakit di tingkat seluler.</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pertanyaan Pemantik</label>
                    <textarea name="pertanyaan_pemantik" rows="3" placeholder="Pertanyaan untuk memicu rasa ingin tahu siswa..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">1. Mengapa tubuh manusia bisa bertambah tinggi dan besar?
2. Apakah sel hewan dan sel tumbuhan memiliki bentuk dan perlindungan yang sama?</textarea>
                </div>
            </div>
        </div>

        <!-- III. LANGKAH-LANGKAH PEMBELAJARAN -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> III. Kegiatan Pembelajaran (Sintaks)
            </h2>

            <!-- Pendahuluan -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> A. Kegiatan Pendahuluan
                    </h3>
                    <input type="text" name="waktu_pendahuluan" placeholder="10 Menit" value="15 Menit" class="w-24 px-2 py-1 rounded-xl border border-slate-200 text-[11px] font-bold text-center bg-white">
                </div>
                <textarea name="kegiatan_pendahuluan" rows="3" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-white">1. Guru membuka pembelajaran dengan salam, berdoa, dan memeriksa kehadiran siswa.
2. Guru memberikan apersepsi dengan menampilkan gambar dinding bata yang diibaratkan sebagai susunan sel.
3. Guru menyampaikan tujuan pembelajaran dan garis besar kegiatan yang akan dilakukan.</textarea>
            </div>

            <!-- Inti -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> B. Kegiatan Inti (Sintaks Model Pembelajaran)
                    </h3>
                    <input type="text" name="waktu_inti" placeholder="65 Menit" value="60 Menit" class="w-24 px-2 py-1 rounded-xl border border-slate-200 text-[11px] font-bold text-center bg-white">
                </div>
                <textarea name="kegiatan_inti" rows="6" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-white">Fase 1 (Orientasi Masalah): Guru memutarkan video animasi kerja sel dan memberikan pertanyaan pemantik.
Fase 2 (Organisasi Belajar): Guru membagi peserta didik ke dalam kelompok (4-5 orang) dan membagikan LKPD.
Fase 3 (Penyelidikan Mandiri & Kelompok): Peserta didik berdiskusi dan menggali informasi dari bahan ajar untuk melengkapi tabel komparasi organel sel.
Fase 4 (Pengembangan & Penyajian Karya): Masing-masing perwakilan kelompok mempresentasikan hasil diskusi di depan kelas.
Fase 5 (Analisis & Evaluasi): Guru memberikan penguatan, konfirmasi jawaban, dan feedback konstruktif.</textarea>
            </div>

            <!-- Penutup -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> C. Kegiatan Penutup
                    </h3>
                    <input type="text" name="waktu_penutup" placeholder="15 Menit" value="15 Menit" class="w-24 px-2 py-1 rounded-xl border border-slate-200 text-[11px] font-bold text-center bg-white">
                </div>
                <textarea name="kegiatan_penutup" rows="3" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-white">1. Guru bersama peserta didik menyimpulkan materi pembelajaran hari ini.
2. Peserta didik melakukan refleksi singkat tentang proses belajar yang dirasakan.
3. Guru memberikan tugas membaca materi pertemuan berikutnya dan menutup pembelajaran dengan doa.</textarea>
            </div>
        </div>

        <!-- IV. ASESMEN & EVALUASI -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span> IV. Asesmen, Remedial & Pengayaan
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Asesmen Formatif (Proses & Sikap)</label>
                    <textarea name="asesmen_formatif" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">Observasi keaktifan diskusi kelompok, penilaian unjuk kerja presentasi, dan kelengkapan pengisian LKPD.</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Asesmen Sumatif (Hasil Akhir)</label>
                    <textarea name="asesmen_sumatif" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">Tes tulis berupa soal pilihan ganda dan uraian singkat di akhir bab materi.</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kegiatan Pengayaan</label>
                    <textarea name="pengayaan" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">Diberikan artikel ilmiah terkini tentang teknologi rekayasa genetika tingkat seluler bagi siswa yang telah tuntas.</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kegiatan Remedial</label>
                    <textarea name="remedial" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">Bimbingan perorangan atau tugas rangkuman terbimbing pada organel sel yang belum dipahami.</textarea>
                </div>
            </div>
        </div>

        <!-- Berkas Lampiran LKPD / Modul Ajar Lengkap -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Lampiran LKPD & Bahan Ajar (Opsional)
            </h2>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Berkas LKPD / Bahan Ajar / Rubrik Asesmen (PDF / Word / PPT / ZIP)</label>
                <input type="file" name="file_lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" name="draft" value="1" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Simpan Sebagai Draft
            </button>
            <button type="submit" name="ajukan" value="1" class="px-6 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-lg shadow-rose-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                Simpan & Ajukan Persetujuan
            </button>
        </div>
    </form>
</div>

<script>
function updateUnitSelection(radio) {
    document.querySelectorAll('.unit-card').forEach(card => {
        card.classList.remove('border-rose-600', 'bg-rose-50/40', 'ring-2', 'ring-rose-500/20', 'shadow-sm');
        card.classList.add('border-slate-200', 'bg-white');
        const indicator = card.querySelector('.unit-check-indicator');
        if (indicator) {
            indicator.classList.add('hidden');
            indicator.classList.remove('block');
        }
    });

    const selectedCard = radio.closest('.unit-card');
    if (selectedCard) {
        selectedCard.classList.remove('border-slate-200', 'bg-white');
        selectedCard.classList.add('border-rose-600', 'bg-rose-50/40', 'ring-2', 'ring-rose-500/20', 'shadow-sm');
        const indicator = selectedCard.querySelector('.unit-check-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            indicator.classList.add('block');
        }
    }
}
</script>
