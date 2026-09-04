<?php
/**
 * Prota - Create View
 */
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Buat Program Tahunan (Prota)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Susun alokasi waktu dan pemetaan materi pokok selama 1 tahun ajaran</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/prota') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            ← Kembali
        </a>
    </div>

    <form method="POST" action="<?= url('kelola-perangkat-pembelajaran/prota/store') ?>" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- Identitas Utama & Unit Selector -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Identitas & Unit Prota
            </h2>

            <!-- Searchable Live Search Guru Picker (At Atas) -->
            <?php
            $picker_label = 'Guru Pengampu / Penyusun Prota';
            $picker_accent = 'indigo';
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
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-indigo-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio" onchange="updateUnitSelection(this)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl mb-1.5 <?= $uInfo['bg_soft'] ?>">
                                <?= $uInfo['icon'] ?>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></span>
                            <span class="text-[10px] text-slate-500 text-center leading-tight mt-0.5"><?= e($uInfo['name']) ?></span>
                            <div class="unit-check-indicator absolute top-2 right-2 <?= $isChecked ? 'block text-indigo-600' : 'hidden' ?>">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen Prota <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" required placeholder="Contoh: Program Tahunan (Prota) Bahasa Indonesia Kelas VIII TP 2026/2027" value="<?= old('judul') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="mata_pelajaran" required placeholder="Contoh: Bahasa Indonesia, IPA, Matematika..." value="<?= old('mata_pelajaran') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat / Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="tingkat_kelas" required placeholder="Contoh: Kelas VIII / Kelas XI IPA" value="<?= old('tingkat_kelas') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fase Kurikulum</label>
                    <select name="fase" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                        <option value="">Pilih Fase (Opsional)</option>
                        <option value="A (SD 1-2)">Fase A (SD Kelas 1-2)</option>
                        <option value="B (SD 3-4)">Fase B (SD Kelas 3-4)</option>
                        <option value="C (SD 5-6)">Fase C (SD Kelas 5-6)</option>
                        <option value="D (SMP 7-9)">Fase D (SMP Kelas 7-9)</option>
                        <option value="E (SMA 10)">Fase E (SMA Kelas 10)</option>
                        <option value="F (SMA 11-12)">Fase F (SMA Kelas 11-12)</option>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($filter_ta == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Capaian Pembelajaran (CP) Umum</label>
                <textarea name="capaian_umum" rows="2" placeholder="Deskripsi umum capaian pembelajaran pada fase ini..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50"></textarea>
            </div>
        </div>

        <!-- Tabel Materi & Alokasi Waktu Dinamis -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Pemetaan Materi & Alokasi Waktu
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Tambahkan baris bab/topik materi dan isikan alokasi JP per semester</p>
                </div>
                <button type="button" onclick="tambahBarisMateri()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah Materi
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="tabel-prota">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <th class="py-2.5 px-3 w-10 text-center">No</th>
                            <th class="py-2.5 px-3 w-1/4 font-bold">Capaian / Elemen (CP/KD)</th>
                            <th class="py-2.5 px-3 w-1/3 font-bold">Materi Pokok / Bab / Topik</th>
                            <th class="py-2.5 px-3 text-center w-24">JP Smt 1</th>
                            <th class="py-2.5 px-3 text-center w-24">JP Smt 2</th>
                            <th class="py-2.5 px-3">Keterangan</th>
                            <th class="py-2.5 px-2 text-center w-10">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium" id="prota-body">
                        <!-- Default Sample Rows -->
                        <?php
                        $defaultMateri = [
                            ['cp' => 'Elemen 1: Menyimak & Berbicara', 'materi' => 'Bab 1: Menulis Teks Laporan Hasil Observasi', 'jp1' => 18, 'jp2' => 0, 'ket' => 'Semester Ganjil'],
                            ['cp' => 'Elemen 2: Membaca & Memirsa', 'materi' => 'Bab 2: Membuat Iklan, Slogan, dan Poster', 'jp1' => 15, 'jp2' => 0, 'ket' => 'Semester Ganjil'],
                            ['cp' => 'Elemen 3: Menulis', 'materi' => 'Bab 3: Menulis Artikel Ilmiah Populer', 'jp1' => 18, 'jp2' => 0, 'ket' => 'Semester Ganjil'],
                            ['cp' => 'Elemen 4: Merefleksi', 'materi' => 'Bab 4: Mengulas Karya Fiksi dan Cerpen', 'jp1' => 0, 'jp2' => 18, 'ket' => 'Semester Genap'],
                            ['cp' => 'Elemen 5: Berkreasi', 'materi' => 'Bab 5: Menciptakan Puisi dan Musikalisasi', 'jp1' => 0, 'jp2' => 18, 'ket' => 'Semester Genap'],
                            ['cp' => 'Elemen 6: Komunikasi', 'materi' => 'Bab 6: Berpidato dan Menyampaikan Gagasan', 'jp1' => 0, 'jp2' => 15, 'ket' => 'Semester Genap']
                        ];
                        foreach ($defaultMateri as $i => $m): ?>
                            <tr class="prota-row">
                                <td class="py-2 px-2 text-center text-slate-400"><?= $i + 1 ?></td>
                                <td class="py-2 px-2">
                                    <input type="text" name="cp_kd[]" value="<?= e($m['cp']) ?>" placeholder="CP / Elemen..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="py-2 px-2">
                                    <input type="text" name="materi_pokok[]" value="<?= e($m['materi']) ?>" required placeholder="Materi pokok..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <input type="number" name="jp_smt1[]" value="<?= $m['jp1'] ?>" min="0" oninput="hitungProta()" class="jp1-input w-20 px-2 py-1.5 rounded-xl border border-slate-200 text-xs text-center font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <input type="number" name="jp_smt2[]" value="<?= $m['jp2'] ?>" min="0" oninput="hitungProta()" class="jp2-input w-20 px-2 py-1.5 rounded-xl border border-slate-200 text-xs text-center font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="py-2 px-2">
                                    <input type="text" name="materi_ket[]" value="<?= e($m['ket']) ?>" placeholder="Keterangan..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <button type="button" onclick="hapusBarisMateri(this)" class="p-1 text-slate-400 hover:text-rose-500 transition-colors" title="Hapus Baris">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-indigo-50/70 font-extrabold text-indigo-950 border-t-2 border-indigo-600">
                            <td colspan="3" class="py-3 px-4 text-right uppercase tracking-wider text-xs">Total Alokasi Waktu:</td>
                            <td class="py-3 px-3 text-center text-sm font-mono text-indigo-900" id="sum-jp1">51 JP</td>
                            <td class="py-3 px-3 text-center text-sm font-mono text-indigo-900" id="sum-jp2">51 JP</td>
                            <td colspan="2" class="py-3 px-4 text-xs font-bold text-indigo-800" id="sum-jptotal">Total 1 Tahun: 102 JP</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Berkas Lampiran Tambahan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span> Berkas Lampiran (Opsional)
            </h2>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Dokumen Prota (Word / PDF / Excel)</label>
                <input type="file" name="file_lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" name="draft" value="1" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Simpan Sebagai Draft
            </button>
            <button type="submit" name="ajukan" value="1" class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                Simpan & Ajukan Persetujuan
            </button>
        </div>
    </form>
</div>

<script>
function tambahBarisMateri() {
    const tbody = document.getElementById('prota-body');
    const tr = document.createElement('tr');
    tr.className = 'prota-row';
    const no = tbody.querySelectorAll('.prota-row').length + 1;
    tr.innerHTML = `
        <td class="py-2 px-2 text-center text-slate-400">${no}</td>
        <td class="py-2 px-2">
            <input type="text" name="cp_kd[]" placeholder="CP / Elemen..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </td>
        <td class="py-2 px-2">
            <input type="text" name="materi_pokok[]" required placeholder="Materi pokok..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </td>
        <td class="py-2 px-2 text-center">
            <input type="number" name="jp_smt1[]" value="0" min="0" oninput="hitungProta()" class="jp1-input w-20 px-2 py-1.5 rounded-xl border border-slate-200 text-xs text-center font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </td>
        <td class="py-2 px-2 text-center">
            <input type="number" name="jp_smt2[]" value="0" min="0" oninput="hitungProta()" class="jp2-input w-20 px-2 py-1.5 rounded-xl border border-slate-200 text-xs text-center font-bold focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </td>
        <td class="py-2 px-2">
            <input type="text" name="materi_ket[]" placeholder="Keterangan..." class="w-full px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-indigo-500">
        </td>
        <td class="py-2 px-2 text-center">
            <button type="button" onclick="hapusBarisMateri(this)" class="p-1 text-slate-400 hover:text-rose-500 transition-colors" title="Hapus Baris">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    hitungProta();
}

function hapusBarisMateri(btn) {
    const row = btn.closest('tr');
    if (document.querySelectorAll('.prota-row').length > 1) {
        row.remove();
        hitungProta();
    } else {
        alert('Minimal harus ada 1 baris materi.');
    }
}

function hitungProta() {
    const rows = document.querySelectorAll('.prota-row');
    let sumJP1 = 0;
    let sumJP2 = 0;

    rows.forEach(r => {
        const jp1 = parseInt(r.querySelector('.jp1-input').value) || 0;
        const jp2 = parseInt(r.querySelector('.jp2-input').value) || 0;
        sumJP1 += jp1;
        sumJP2 += jp2;
    });

    const sumTotal = sumJP1 + sumJP2;
    document.getElementById('sum-jp1').innerText = sumJP1 + ' JP';
    document.getElementById('sum-jp2').innerText = sumJP2 + ' JP';
    document.getElementById('sum-jptotal').innerText = 'Total 1 Tahun: ' + sumTotal + ' JP';
}

function updateUnitSelection(radio) {
    document.querySelectorAll('.unit-card').forEach(card => {
        card.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'ring-2', 'ring-indigo-500/20', 'shadow-sm');
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
        selectedCard.classList.add('border-indigo-600', 'bg-indigo-50/40', 'ring-2', 'ring-indigo-500/20', 'shadow-sm');
        const indicator = selectedCard.querySelector('.unit-check-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            indicator.classList.add('block');
        }
    }
}

document.addEventListener('DOMContentLoaded', hitungProta);
</script>
