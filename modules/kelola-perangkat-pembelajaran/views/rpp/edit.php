<?php
/**
 * RPP / Modul Ajar - Edit View
 */
$profilPancasila = $konten['profil_pancasila'] ?? [];
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Edit RPP / Modul Ajar</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui komponen rencana pelaksanaan pembelajaran</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/rpp') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            ← Kembali
        </a>
    </div>

    <?php if ($item['status'] === 'ditolak'): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 flex items-start gap-3">
            <div class="text-rose-600 text-xl font-bold">⚠️</div>
            <div>
                <h4 class="text-xs font-bold text-rose-900">Dokumen Memerlukan Revisi</h4>
                <p class="text-xs text-rose-700 mt-0.5">Catatan Verifikator: <?= e($item['catatan_revisi'] ?? 'Lakukan perbaikan sesuai arahan.') ?></p>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/rpp/update/{$item['id']}") ?>" enctype="multipart/form-data" class="space-y-6">
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
            $selected_guru_id = old('guru_id', $item['guru_id'] ?? null);
            $selected_guru_nama = old('guru_nama', $item['guru_nama'] ?? null);
            $selected_guru_nip = old('guru_nip', $item['guru_nip'] ?? null);
            include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/guru_picker.php';
            ?>

            <!-- Visual Unit Selector -->
            <div class="pt-2">
                <?php $selectedUnit = old('unit', $item['unit'] ?? 'SD'); ?>
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
                    <input type="text" name="judul" required value="<?= e($item['judul']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="mata_pelajaran" required value="<?= e($item['mata_pelajaran']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat / Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="tingkat_kelas" required value="<?= e($item['tingkat_kelas']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fase Kurikulum</label>
                    <select name="fase" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                        <option value="">Pilih Fase (Opsional)</option>
                        <option value="A (SD 1-2)" <?= ($konten['fase'] ?? '') === 'A (SD 1-2)' ? 'selected' : '' ?>>Fase A (SD Kelas 1-2)</option>
                        <option value="B (SD 3-4)" <?= ($konten['fase'] ?? '') === 'B (SD 3-4)' ? 'selected' : '' ?>>Fase B (SD Kelas 3-4)</option>
                        <option value="C (SD 5-6)" <?= ($konten['fase'] ?? '') === 'C (SD 5-6)' ? 'selected' : '' ?>>Fase C (SD Kelas 5-6)</option>
                        <option value="D (SMP 7-9)" <?= ($konten['fase'] ?? '') === 'D (SMP 7-9)' ? 'selected' : '' ?>>Fase D (SMP Kelas 7-9)</option>
                        <option value="E (SMA 10)" <?= ($konten['fase'] ?? '') === 'E (SMA 10)' ? 'selected' : '' ?>>Fase E (SMA Kelas 10)</option>
                        <option value="F (SMA 11-12)" <?= ($konten['fase'] ?? '') === 'F (SMA 11-12)' ? 'selected' : '' ?>>Fase F (SMA Kelas 11-12)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($item['tahun_akademik_id'] == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                        <option value="Ganjil" <?= $item['semester'] === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                        <option value="Genap" <?= $item['semester'] === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pertemuan Ke-</label>
                    <input type="text" name="pertemuan_ke" value="<?= e($konten['pertemuan_ke'] ?? '1') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alokasi Waktu <span class="text-rose-500">*</span></label>
                    <input type="text" name="alokasi_waktu" required value="<?= e($item['alokasi_waktu'] ?? '2 x 45 Menit') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                </div>

                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Model Pembelajaran</label>
                    <select name="model_pembelajaran" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
                        <?php
                        $curModel = $konten['model_pembelajaran'] ?? 'Problem Based Learning (PBL)';
                        $models = [
                            'Problem Based Learning (PBL)',
                            'Project Based Learning (PjBL)',
                            'Discovery / Inquiry Learning',
                            'Cooperative Learning',
                            'Differentiated Instruction',
                            'Direct Instruction'
                        ];
                        foreach ($models as $m): ?>
                            <option value="<?= $m ?>" <?= $curModel === $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Dimensi Profil Pelajar Pancasila -->
            <div class="pt-3 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Dimensi Profil Pelajar Pancasila (P3):</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                    <?php
                    $dimensiP3 = [
                        'Beriman, Bertakwa & Berakhlak Mulia',
                        'Bernalar Kritis',
                        'Kreatif',
                        'Gotong Royong',
                        'Mandiri',
                        'Berkebinekaan Global'
                    ];
                    foreach ($dimensiP3 as $p3): ?>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="profil_pancasila[]" value="<?= $p3 ?>" <?= in_array($p3, $profilPancasila) ? 'checked' : '' ?> class="rounded text-rose-600 focus:ring-rose-500">
                            <span class="text-slate-700 font-medium"><?= $p3 ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Sarana, Prasarana & Media Pembelajaran</label>
                <input type="text" name="sarana_prasarana" value="<?= e($konten['sarana_prasarana'] ?? '') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50">
            </div>
        </div>

        <!-- II. KOMPONEN INTI -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> II. Komponen Inti Pembelajaran
            </h2>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tujuan Pembelajaran (TP) <span class="text-rose-500">*</span></label>
                <textarea name="tujuan_pembelajaran" rows="3" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50"><?= e($konten['tujuan_pembelajaran'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pemahaman Bermakna</label>
                    <textarea name="pemahaman_bermakna" rows="3" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50"><?= e($konten['pemahaman_bermakna'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pertanyaan Pemantik</label>
                    <textarea name="pertanyaan_pemantik" rows="3" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50"><?= e($konten['pertanyaan_pemantik'] ?? '') ?></textarea>
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
                    <input type="text" name="waktu_pendahuluan" value="<?= e($konten['waktu_pendahuluan'] ?? '15 Menit') ?>" class="w-24 px-2 py-1 rounded-xl border border-slate-200 text-[11px] font-bold text-center bg-white">
                </div>
                <textarea name="kegiatan_pendahuluan" rows="3" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-white"><?= e($konten['kegiatan_pendahuluan'] ?? '') ?></textarea>
            </div>

            <!-- Inti -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> B. Kegiatan Inti (Sintaks Model Pembelajaran)
                    </h3>
                    <input type="text" name="waktu_inti" value="<?= e($konten['waktu_inti'] ?? '60 Menit') ?>" class="w-24 px-2 py-1 rounded-xl border border-slate-200 text-[11px] font-bold text-center bg-white">
                </div>
                <textarea name="kegiatan_inti" rows="6" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-white"><?= e($konten['kegiatan_inti'] ?? '') ?></textarea>
            </div>

            <!-- Penutup -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> C. Kegiatan Penutup
                    </h3>
                    <input type="text" name="waktu_penutup" value="<?= e($konten['waktu_penutup'] ?? '15 Menit') ?>" class="w-24 px-2 py-1 rounded-xl border border-slate-200 text-[11px] font-bold text-center bg-white">
                </div>
                <textarea name="kegiatan_penutup" rows="3" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-white"><?= e($konten['kegiatan_penutup'] ?? '') ?></textarea>
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
                    <textarea name="asesmen_formatif" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50"><?= e($konten['asesmen_formatif'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Asesmen Sumatif (Hasil Akhir)</label>
                    <textarea name="asesmen_sumatif" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50"><?= e($konten['asesmen_sumatif'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kegiatan Pengayaan</label>
                    <textarea name="pengayaan" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50"><?= e($konten['pengayaan'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kegiatan Remedial</label>
                    <textarea name="remedial" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50/50"><?= e($konten['remedial'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Berkas Lampiran LKPD / Modul Ajar Lengkap -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Berkas Lampiran
            </h2>
            <div>
                <?php if (!empty($item['file_lampiran'])): ?>
                    <div class="mb-3 p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs text-slate-700">
                            <span>📄 Berkas saat ini:</span>
                            <a href="<?= url($item['file_lampiran']) ?>" target="_blank" class="font-bold text-rose-600 hover:underline">Unduh Berkas Tersimpan</a>
                        </div>
                    </div>
                <?php endif; ?>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Berkas (Opsional)</label>
                <input type="file" name="file_lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" name="draft" value="1" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Simpan Perubahan
            </button>
            <button type="submit" name="ajukan" value="1" class="px-6 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-lg shadow-rose-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                Simpan & Ajukan Ulang
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
