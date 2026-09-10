<?php
/**
 * Edit RPP / Modul Ajar (JSIT Format)
 */
$selectedUnit = old('unit', $item['unit'] ?? 'SD');
$konten = $konten ?? (!empty($item['konten_json']) ? json_decode($item['konten_json'], true) : []);
$groupId = $konten['rpp_group_id'] ?? 0;
$kktpRows = $konten['kktp_rows'] ?? [];
if (empty($kktpRows)) {
    $kktpRows = [
        ['indikator' => '', 'waktu' => '']
    ];
}
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Edit RPP / Modul Ajar (JSIT)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui rencana pelaksanaan pembelajaran Kurikulum Merdeka Pendekatan TERPADU & INTROFLEX</p>
        </div>
        <a href="<?= ($groupId > 0) ? url("kelola-perangkat-pembelajaran/rpp/group/{$groupId}") : url('kelola-perangkat-pembelajaran/rpp') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/rpp/update/{$item['id']}") ?>" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>
        <input type="hidden" name="rpp_group_id" value="<?= (int)$groupId ?>">

        <!-- I. IDENTITAS MODUL / RPP & GURU -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> I. Informasi Umum & Identitas Modul Ajar
            </h2>

            <!-- Searchable Live Search Guru Picker -->
            <?php
            $picker_label = 'Guru Pengampu / Penyusun Modul Ajar';
            $picker_accent = 'teal';
            $selected_guru_id = old('guru_id', $item['guru_id'] ?? null);
            $selected_guru_nama = old('guru_nama', $item['guru_nama'] ?? (Auth::name() ?? 'Administrator'));
            $selected_guru_nip = old('guru_nip', $item['guru_nip'] ?? '');
            include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/guru_picker.php';
            ?>

            <!-- Unit Satuan Pendidikan -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Unit Satuan Pendidikan <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($unit_list as $uKey => $uInfo): 
                        $isChecked = ($selectedUnit === $uKey);
                    ?>
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-teal-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl mb-1.5 <?= $uInfo['bg_soft'] ?>">
                                <?= $uInfo['icon'] ?>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></span>
                            <span class="text-[10px] text-slate-500 text-center leading-tight mt-0.5"><?= e($uInfo['name']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul / Topik / Bab Pembelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" required value="<?= old('judul', $item['judul']) ?>" placeholder="Contoh: Bab 1: Tumbuhan Sumber Kehidupan di Bumi" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50" required>
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= (old('tahun_akademik_id', $item['tahun_akademik_id']) == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50" required>
                        <option value="Ganjil" <?= (old('semester', $item['semester']) === 'Ganjil') ? 'selected' : '' ?>>Semester Ganjil</option>
                        <option value="Genap" <?= (old('semester', $item['semester']) === 'Genap') ? 'selected' : '' ?>>Semester Genap</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="mata_pelajaran" required value="<?= old('mata_pelajaran', $item['mata_pelajaran']) ?>" placeholder="Contoh: IPAS, Matematika, PAI" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat / Kelas (Rombel) <span class="text-rose-500">*</span></label>
                    <input type="text" name="tingkat_kelas" required value="<?= old('tingkat_kelas', $item['tingkat_kelas']) ?>" placeholder="Contoh: III (Tiga) Abdurrahman" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fase</label>
                    <select name="fase" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                        <option value="A" <?= old('fase', $item['fase']) === 'A' ? 'selected' : '' ?>>Fase A (Kelas 1-2)</option>
                        <option value="B" <?= old('fase', $item['fase']) === 'B' ? 'selected' : '' ?>>Fase B (Kelas 3-4)</option>
                        <option value="C" <?= old('fase', $item['fase']) === 'C' ? 'selected' : '' ?>>Fase C (Kelas 5-6)</option>
                        <option value="D" <?= old('fase', $item['fase']) === 'D' ? 'selected' : '' ?>>Fase D (SMP / Kelas 7-9)</option>
                        <option value="E" <?= old('fase', $item['fase']) === 'E' ? 'selected' : '' ?>>Fase E (SMA / Kelas 10)</option>
                        <option value="F" <?= old('fase', $item['fase']) === 'F' ? 'selected' : '' ?>>Fase F (SMA / Kelas 11-12)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Model Pembelajaran</label>
                    <input type="text" name="model_pembelajaran" value="<?= old('model_pembelajaran', $konten['model_pembelajaran'] ?? 'Problem Based Learning (PBL)') ?>" placeholder="Contoh: Problem Based Learning / Discovery Learning" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alokasi Waktu</label>
                    <input type="text" name="alokasi_waktu" value="<?= old('alokasi_waktu', $item['alokasi_waktu'] ?? '2 x 35 Menit (Pertemuan 1)') ?>" placeholder="Contoh: 2 x 35 Menit (Pertemuan 1)" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Waktu Pelaksanaan</label>
                    <input type="text" name="waktu_pelaksanaan" value="<?= old('waktu_pelaksanaan', $konten['waktu_pelaksanaan'] ?? '22 - 25 Juli 2025') ?>" placeholder="Contoh: 22 - 25 Juli 2025" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>
            </div>
        </div>

        <!-- II. CAPAIAN PEMBELAJARAN (CP) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> II. Capaian Pembelajaran (CP)
            </h2>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Teks Capaian Pembelajaran</label>
                <textarea name="cp_text" rows="3" placeholder="Masukkan narasi Capaian Pembelajaran (CP) untuk materi/bab ini..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50"><?= old('cp_text', $konten['cp_text'] ?? ($konten['cp'] ?? '')) ?></textarea>
            </div>
        </div>

        <!-- III. TUJUAN PEMBELAJARAN (3 RANAH) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> III. Tujuan Pembelajaran (3 Ranah)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl border border-amber-200 bg-amber-50/30 space-y-2">
                    <label class="block text-xs font-bold text-amber-900 uppercase">1. Attitude / Sikap</label>
                    <textarea name="tp_attitude" rows="4" placeholder="Contoh: Peserta didik menunjukkan sikap bersyukur, bernalar kritis, dan gotong royong..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"><?= old('tp_attitude', $konten['tp_attitude'] ?? '') ?></textarea>
                </div>
                <div class="p-4 rounded-2xl border border-blue-200 bg-blue-50/30 space-y-2">
                    <label class="block text-xs font-bold text-blue-900 uppercase">2. Skill / Keterampilan</label>
                    <textarea name="tp_skill" rows="4" placeholder="Contoh: Peserta didik mampu mengamati, mencatat, dan mengomunikasikan hasil observasi..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"><?= old('tp_skill', $konten['tp_skill'] ?? '') ?></textarea>
                </div>
                <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50/30 space-y-2">
                    <label class="block text-xs font-bold text-emerald-900 uppercase">3. Knowledge / Pengetahuan</label>
                    <textarea name="tp_knowledge" rows="4" placeholder="Contoh: Peserta didik dapat menjelaskan fungsi bagian tubuh hewan dan daur hidupnya..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-teal-500 focus:outline-none"><?= old('tp_knowledge', $konten['tp_knowledge'] ?? ($konten['tujuan_pembelajaran'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- IV. KATA KUNCI & PERTANYAAN PEMANTIK -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> IV. Kata Kunci & Pertanyaan Pemantik
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Kunci / Konten Pokok</label>
                    <input type="text" name="kata_kunci" value="<?= old('kata_kunci', $konten['kata_kunci'] ?? '') ?>" placeholder="Contoh: Fotosintesis, Klorofil, Akar, Daun" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pertanyaan Pemantik</label>
                    <textarea name="pertanyaan_pemantik" rows="2" placeholder="Contoh: Bagaimana tumbuhan dapat membuat makanannya sendiri tanpa bergerak mencari makan?" class="w-full px-4 py-2 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50"><?= old('pertanyaan_pemantik', $konten['pertanyaan_pemantik'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- V. PENGETAHUAN PENDUKUNG & ASESMEN DIAGNOSIS -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> V. Pengetahuan Pendukung & Asesmen Diagnosis Kognitif
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pengetahuan Pendukung / Prasyarat</label>
                    <textarea name="pengetahuan_pendukung" rows="2" placeholder="Contoh: Mengenal jenis-jenis tumbuhan di lingkungan sekitar dan kebutuhan dasar makhluk hidup." class="w-full px-4 py-2 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50"><?= old('pengetahuan_pendukung', $konten['pengetahuan_pendukung'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Asesmen Diagnosis Kognitif</label>
                    <textarea name="asesmen_diagnosis" rows="2" placeholder="Contoh: Tanya jawab lisan di awal pembelajaran mengenai apa yang dibutuhkan tanaman untuk tumbuh." class="w-full px-4 py-2 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50"><?= old('asesmen_diagnosis', $konten['asesmen_diagnosis'] ?? ($konten['asesmen_diagnostik'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- VI. KKTP & ALOKASI PERTEMUAN -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> VI. Kriteria Ketercapaian Tujuan Pembelajaran (KKTP)
                </h2>
                <button type="button" onclick="addKktpRow()" class="px-3 py-1.5 rounded-xl bg-teal-50 text-teal-700 hover:bg-teal-100 font-bold text-xs transition-colors">
                    + Tambah Baris KKTP
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse" id="kktpTable">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 w-12 text-center">No</th>
                            <th class="py-2.5 px-3">KKTP / Indikator Ketercapaian</th>
                            <th class="py-2.5 px-3 w-64">Waktu / Alokasi Pertemuan / Tanggal</th>
                            <th class="py-2.5 px-3 w-12 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="kktpBody">
                        <?php foreach ($kktpRows as $rIdx => $kr): ?>
                            <tr class="kktp-row">
                                <td class="py-2 px-3 text-center text-slate-400 row-num"><?= $rIdx + 1 ?></td>
                                <td class="py-2 px-3">
                                    <input type="text" name="kktp_indikator[]" value="<?= e($kr['indikator'] ?? ($kr['kktp'] ?? '')) ?>" placeholder="Contoh: Mengidentifikasi bagian-bagian tubuh tumbuhan" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="kktp_waktu[]" value="<?= e($kr['waktu'] ?? ($kr['pekan'] ?? '')) ?>" placeholder="Contoh: Pertemuan <?= $rIdx + 1 ?>" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <button type="button" onclick="removeKktpRow(this)" class="text-slate-400 hover:text-rose-600 font-bold text-base">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- VII. MEDIA, SARANA, METODE & SUMBER BELAJAR -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> VII. Media, Sarana, Metode & Sumber Belajar
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Media</label>
                    <textarea name="media" rows="2" placeholder="Contoh: PPT Interaktif, Video Pembelajaran, Gambar Hewan, LKPD" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50/50 focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('media', $konten['media'] ?? 'PPT Interaktif, Video Pembelajaran, Gambar Hewan/Tumbuhan, LKPD') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sarana</label>
                    <textarea name="sarana" rows="2" placeholder="Contoh: LCD Proyektor, Laptop, Papan Tulis, Speaker" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50/50 focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('sarana', $konten['sarana'] ?? ($konten['sarana_prasarana'] ?? 'LCD Proyektor, Laptop, Papan Tulis, Lingkungan Sekolah')) ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Metode</label>
                    <textarea name="metode" rows="2" placeholder="Contoh: Diskusi, Pengamatan, Tanya Jawab, Penugasan" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50/50 focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('metode', $konten['metode'] ?? 'Diskusi Kelompok, Observasi, Tanya Jawab, Eksplorasi') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sumber Belajar</label>
                    <textarea name="sumber_belajar" rows="2" placeholder="Contoh: Buku Guru & Siswa IPAS Kelas 3, Modul JSIT" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50/50 focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('sumber_belajar', $konten['sumber_belajar'] ?? 'Buku Guru & Siswa Kemendikbudristek, Modul JSIT, Lingkungan Sekitar') ?></textarea>
                </div>
            </div>
        </div>

        <!-- VIII. PELAKSANAAN PEMBELAJARAN PENDEKATAN TERPADU (JSIT) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> VIII. Pelaksanaan Pembelajaran Pendekatan TERPADU (JSIT)
            </h2>

            <div class="space-y-4">
                <!-- 1. Opener -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">1. Pembukaan (Opener)</label>
                        <input type="text" name="opener_waktu" value="<?= old('opener_waktu', $konten['opener_waktu'] ?? '10 menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="opener_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('opener_kegiatan', $konten['opener_kegiatan'] ?? ($konten['kegiatan_pendahuluan'] ?? ('1. Guru mengucapkan salam dan menyapa peserta didik dengan hangat.' . "\n" . '2. Berdoa bersama dan membaca ikrar / ayat suci Al-Qur\'an.' . "\n" . '3. Memeriksa kehadiran dan kesiapan belajar (Apersepsi & Ice Breaking).' . "\n" . '4. Menyampaikan tujuan pembelajaran dan motivasi pentingnya materi.'))) ?></textarea>
                </div>

                <!-- 2. Telaah -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">2. Telaah</label>
                        <input type="text" name="telaah_waktu" value="<?= old('telaah_waktu', $konten['telaah_waktu'] ?? '20 Menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="telaah_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('telaah_kegiatan', $konten['telaah_kegiatan'] ?? 'Peserta didik mengamati gambar / tayangan video pembelajaran mengenai materi yang disajikan oleh guru secara teliti dan terarah.') ?></textarea>
                </div>

                <!-- 3. Eksplorasi -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">3. Eksplorasi</label>
                        <input type="text" name="eksplorasi_waktu" value="<?= old('eksplorasi_waktu', $konten['eksplorasi_waktu'] ?? '20 Menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="eksplorasi_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('eksplorasi_kegiatan', $konten['eksplorasi_kegiatan'] ?? 'Peserta didik dibagi menjadi kelompok kecil untuk melakukan penyelidikan langsung, membaca sumber bahan ajar, dan mengumpulkan informasi terkait topik materi.') ?></textarea>
                </div>

                <!-- 4. Rumuskan -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">4. Rumuskan</label>
                        <input type="text" name="rumuskan_waktu" value="<?= old('rumuskan_waktu', $konten['rumuskan_waktu'] ?? '20 Menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="rumuskan_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('rumuskan_kegiatan', $konten['rumuskan_kegiatan'] ?? 'Peserta didik mendiskusikan hasil temuan dalam kelompok dan merumuskan jawaban serta kesimpulan pada Lembar Kerja Peserta Didik (LKPD).') ?></textarea>
                </div>

                <!-- 5. Presentasikan -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">5. Presentasikan</label>
                        <input type="text" name="presentasikan_waktu" value="<?= old('presentasikan_waktu', $konten['presentasikan_waktu'] ?? '20 Menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="presentasikan_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('presentasikan_kegiatan', $konten['presentasikan_kegiatan'] ?? 'Setiap perwakilan kelompok mempresentasikan hasil diskusi di depan kelas, kelompok lain menyimak dan memberikan apresiasi atau tanggapan santun.') ?></textarea>
                </div>

                <!-- 6. Aplikasikan -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">6. Aplikasikan</label>
                        <input type="text" name="aplikasikan_waktu" value="<?= old('aplikasikan_waktu', $konten['aplikasikan_waktu'] ?? '10 Menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="aplikasikan_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('aplikasikan_kegiatan', $konten['aplikasikan_kegiatan'] ?? 'Peserta didik mengerjakan soal latihan mandiri untuk memperkuat pemahaman terhadap konsep yang telah dipelajari.') ?></textarea>
                </div>

                <!-- 7. Kaitkan dan Simpulkan -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">7. Kaitkan dan Simpulkan</label>
                        <input type="text" name="kaitkan_waktu" value="<?= old('kaitkan_waktu', $konten['kaitkan_waktu'] ?? '2 Menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="kaitkan_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('kaitkan_kegiatan', $konten['kaitkan_kegiatan'] ?? 'Guru bersama peserta didik menarik benang merah dan menyimpulkan poin-poin utama materi pembelajaran hari ini.') ?></textarea>
                </div>

                <!-- 8. Duniawi -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">8. Duniawi</label>
                        <input type="text" name="duniawi_waktu" value="<?= old('duniawi_waktu', $konten['duniawi_waktu'] ?? '2 Menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="duniawi_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('duniawi_kegiatan', $konten['duniawi_kegiatan'] ?? 'Membiasakan diri merawat makhluk hidup di sekitar dan memanfaatkan ilmu untuk kebaikan sesama.') ?></textarea>
                </div>

                <!-- 9. Ukhrowi -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-800 uppercase">9. Ukhrowi</label>
                        <input type="text" name="ukhrowi_waktu" value="<?= old('ukhrowi_waktu', $konten['ukhrowi_waktu'] ?? '3 Menit') ?>" class="w-24 px-2 py-1 rounded-lg border border-slate-200 text-xs text-center bg-white font-semibold">
                    </div>
                    <textarea name="ukhrowi_kegiatan" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('ukhrowi_kegiatan', $konten['ukhrowi_kegiatan'] ?? 'Merenungi kebesaran Allah SWT yang menciptakan alam semesta dengan penuh hikmah dan keteraturan (Tadabbur Ayat Kauniyah).') ?></textarea>
                </div>

                <!-- 10. Closure / Penutup -->
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/40 space-y-3">
                    <div class="font-bold text-xs text-slate-800 uppercase">10. Closure / Penutup</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-semibold text-slate-700">Refleksi</label>
                                <input type="text" name="refleksi_waktu" value="<?= old('refleksi_waktu', $konten['refleksi_waktu'] ?? '2 Menit') ?>" class="w-20 px-2 py-0.5 rounded border border-slate-200 text-[11px] text-center bg-white font-semibold">
                            </div>
                            <textarea name="refleksi_kegiatan" rows="2" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('refleksi_kegiatan', $konten['refleksi_kegiatan'] ?? 'Peserta didik menyampaikan perasaan dan hal menarik yang dipelajari hari ini.') ?></textarea>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-semibold text-slate-700">Kegiatan Penutup</label>
                                <input type="text" name="penutup_waktu" value="<?= old('penutup_waktu', $konten['penutup_waktu'] ?? '2 Menit') ?>" class="w-20 px-2 py-0.5 rounded border border-slate-200 text-[11px] text-center bg-white font-semibold">
                            </div>
                            <textarea name="penutup_kegiatan" rows="2" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('penutup_kegiatan', $konten['penutup_kegiatan'] ?? ($konten['kegiatan_penutup'] ?? 'Membaca doa penutup majelis (Kafaratul Majlis), motivasi, dan salam penutup.')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IX. PENILAIAN TERPADU -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> IX. Penilaian Terpadu (AfL, AaL, AoL)
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 w-28">Ranah</th>
                            <th class="py-2.5 px-3">Tujuan Pembelajaran</th>
                            <th class="py-2.5 px-3">Assessment for Learning (AfL)</th>
                            <th class="py-2.5 px-3">Assessment as Learning (AaL)</th>
                            <th class="py-2.5 px-3">Assessment of Learning (AoL)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-2.5 px-3 font-bold text-amber-900 bg-amber-50/40">Sikap / Attitude</td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_sikap_tp" value="<?= old('penilaian_sikap_tp', $konten['penilaian_sikap_tp'] ?? 'Berakhlak mulia & mandiri') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_sikap_afl" value="<?= old('penilaian_sikap_afl', $konten['penilaian_sikap_afl'] ?? ($konten['asesmen_formatif'] ?? 'Observasi selama diskusi')) ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_sikap_aal" value="<?= old('penilaian_sikap_aal', $konten['penilaian_sikap_aal'] ?? 'Penilaian diri / teman sejawat') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_sikap_aol" value="<?= old('penilaian_sikap_aol', $konten['penilaian_sikap_aol'] ?? 'Jurnal catatan guru') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold text-blue-900 bg-blue-50/40">Keterampilan / Skill</td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_skill_tp" value="<?= old('penilaian_skill_tp', $konten['penilaian_skill_tp'] ?? 'Mengomunikasikan hasil karya') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_skill_afl" value="<?= old('penilaian_skill_afl', $konten['penilaian_skill_afl'] ?? 'Unjuk kerja saat presentasi') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_skill_aal" value="<?= old('penilaian_skill_aal', $konten['penilaian_skill_aal'] ?? 'Checklist kriteria tugas') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_skill_aol" value="<?= old('penilaian_skill_aol', $konten['penilaian_skill_aol'] ?? 'Rubrik penilaian LKPD') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold text-emerald-900 bg-emerald-50/40">Pengetahuan / Knowledge</td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_knowledge_tp" value="<?= old('penilaian_knowledge_tp', $konten['penilaian_knowledge_tp'] ?? 'Memahami materi pokok') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_knowledge_afl" value="<?= old('penilaian_knowledge_afl', $konten['penilaian_knowledge_afl'] ?? 'Tanya jawab interaktif') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_knowledge_aal" value="<?= old('penilaian_knowledge_aal', $konten['penilaian_knowledge_aal'] ?? 'Kuis latihan mandiri') ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                            <td class="py-2.5 px-3"><input type="text" name="penilaian_knowledge_aol" value="<?= old('penilaian_knowledge_aol', $konten['penilaian_knowledge_aol'] ?? ($konten['asesmen_sumatif'] ?? 'Tes formatif / sumatif')) ?>" class="w-full px-2 py-1 rounded border border-slate-200 text-xs"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- X. PENERAPAN INTROFLEX (JSIT) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> X. Penerapan Framework INTROFLEX (JSIT)
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">1. Individualisasi</label>
                    <textarea name="introflex_individualisasi" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50/50 focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('introflex_individualisasi', $konten['introflex_individualisasi'] ?? 'Menyapa siswa dengan ramah menyebutkan nama, memberikan bimbingan sesuai kecepatan belajar.') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">2. Interaksi</label>
                    <textarea name="introflex_interaksi" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50/50 focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('introflex_interaksi', $konten['introflex_interaksi'] ?? 'Membangun diskusi kelompok aktif, saling menghargai pendapat antar siswa dan guru.') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">3. Observasi</label>
                    <textarea name="introflex_observasi" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50/50 focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('introflex_observasi', $konten['introflex_observasi'] ?? 'Mengamati keterlibatan dan antusiasme setiap siswa saat proses belajar mengajar berlangsung.') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">4. Refleksi</label>
                    <textarea name="introflex_refleksi" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50/50 focus:outline-none focus:ring-1 focus:ring-teal-500"><?= old('introflex_refleksi', $konten['introflex_refleksi'] ?? ($konten['refleksi_guru_siswa'] ?? 'Memberikan feedback positif langsung dan mengevaluasi pemahaman siswa di akhir sesi.')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- XI. PENGESAHAN & PEJABAT -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span> XI. Pejabat Pengesahan Dokumen
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kepala Sekolah (Menyetujui)</label>
                    <input type="text" name="kepala_sekolah_nama" value="<?= old('kepala_sekolah_nama', $konten['kepala_sekolah_nama'] ?? 'Feni, S.Pd.I') ?>" class="w-full px-4 py-2 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                    <input type="text" name="kepala_sekolah_nip" value="<?= old('kepala_sekolah_nip', $konten['kepala_sekolah_nip'] ?? '') ?>" placeholder="NIP Kepala Sekolah (opsional)" class="w-full px-4 py-2 mt-2 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pemeriksa RPP / Tim Kurikulum</label>
                    <?php
                    $valPemeriksa = $konten['pemeriksa_nama'] ?? '';
                    if ($valPemeriksa === 'Tim Kurikulum SDIT Bina Insan' || $valPemeriksa === 'Tim Kurikulum') {
                        $valPemeriksa = (class_exists('Auth') && Auth::name() && Auth::name() !== 'Guest') ? Auth::name() : '';
                    }
                    ?>
                    <input type="text" name="pemeriksa_nama" value="<?= old('pemeriksa_nama', $valPemeriksa) ?>" class="w-full px-4 py-2 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                    <input type="text" name="pemeriksa_nip" value="<?= old('pemeriksa_nip', $konten['pemeriksa_nip'] ?? '') ?>" placeholder="NIP Pemeriksa (opsional)" class="w-full px-4 py-2 mt-2 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="<?= ($groupId > 0) ? url("kelola-perangkat-pembelajaran/rpp/group/{$groupId}") : url('kelola-perangkat-pembelajaran/rpp') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                <span>Perbarui Dokumen RPP</span>
            </button>
        </div>
    </form>
</div>

<script>
function addKktpRow() {
    const tbody = document.getElementById('kktpBody');
    const rowCount = tbody.querySelectorAll('.kktp-row').length + 1;
    const tr = document.createElement('tr');
    tr.className = 'kktp-row';
    tr.innerHTML = `
        <td class="py-2 px-3 text-center text-slate-400 row-num">${rowCount}</td>
        <td class="py-2 px-3">
            <input type="text" name="kktp_indikator[]" value="" placeholder="Contoh: Mengidentifikasi bagian-bagian tubuh tumbuhan" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500">
        </td>
        <td class="py-2 px-3">
            <input type="text" name="kktp_waktu[]" value="" placeholder="Contoh: Pertemuan ${rowCount} (Pekan ${rowCount})" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-teal-500">
        </td>
        <td class="py-2 px-3 text-center">
            <button type="button" onclick="removeKktpRow(this)" class="text-slate-400 hover:text-rose-600 font-bold text-base">&times;</button>
        </td>
    `;
    tbody.appendChild(tr);
}

function removeKktpRow(btn) {
    const tbody = document.getElementById('kktpBody');
    if (tbody.querySelectorAll('.kktp-row').length > 1) {
        btn.closest('tr').remove();
        tbody.querySelectorAll('.kktp-row').forEach((row, idx) => {
            row.querySelector('.row-num').innerText = idx + 1;
        });
    }
}
</script>
