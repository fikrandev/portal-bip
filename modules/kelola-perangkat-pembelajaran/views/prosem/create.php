<?php
/**
 * Prosem - Create View
 */
?>
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Buat Program Semester (Prosem)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Matriks pembagian materi dan asesmen mingguan per bulan</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/prosem') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            ← Kembali
        </a>
    </div>

    <form method="POST" action="<?= url('kelola-perangkat-pembelajaran/prosem/store') ?>" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- Identitas Utama & Unit Selector -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Identitas & Unit Prosem
            </h2>

            <!-- Searchable Live Search Guru Picker (At Atas) -->
            <?php
            $picker_label = 'Guru Pengampu / Penyusun Prosem';
            $picker_accent = 'purple';
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
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-purple-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-purple-600 bg-purple-50/40 ring-2 ring-purple-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio" onchange="updateUnitSelection(this)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl mb-1.5 <?= $uInfo['bg_soft'] ?>">
                                <?= $uInfo['icon'] ?>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></span>
                            <span class="text-[10px] text-slate-500 text-center leading-tight mt-0.5"><?= e($uInfo['name']) ?></span>
                            <div class="unit-check-indicator absolute top-2 right-2 <?= $isChecked ? 'block text-purple-600' : 'hidden' ?>">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
                <div class="lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen Prosem <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" required placeholder="Contoh: Program Semester (Prosem) Bahasa Inggris Kelas IX Semester Ganjil" value="<?= old('judul') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="mata_pelajaran" required placeholder="Contoh: Bahasa Inggris, Matematika..." value="<?= old('mata_pelajaran') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat / Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="tingkat_kelas" required placeholder="Contoh: Kelas IX / Kelas XII IPA" value="<?= old('tingkat_kelas') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fase Kurikulum</label>
                    <select name="fase" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50">
                        <option value="">Pilih Fase (Opsional)</option>
                        <option value="A (SD 1-2)">Fase A (SD Kelas 1-2)</option>
                        <option value="B (SD 3-4)">Fase B (SD Kelas 3-4)</option>
                        <option value="C (SD 5-6)">Fase C (SD Kelas 5-6)</option>
                        <option value="D (SMP 7-9)">Fase D (SMP Kelas 7-9)</option>
                        <option value="E (SMA 10)">Fase E (SMA Kelas 10)</option>
                        <option value="F (SMA 11-12)">Fase F (SMA Kelas 11-12)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($filter_ta == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" id="semester-prosem" onchange="gantiSemesterProsem(this.value)" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50">
                        <option value="Ganjil">Semester Ganjil (Juli - Desember)</option>
                        <option value="Genap">Semester Genap (Januari - Juni)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Matriks Mingguan Prosem Interaktif -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Matriks Distribusi Pekan KBM
                    </h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Isikan alokasi JP pada pekan yang sesuai (misal: 3) atau kosongkan jika tidak ada pertemuan</p>
                </div>
                <button type="button" onclick="tambahBarisProsem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Tambah Materi / Agenda
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 border-collapse" id="tabel-prosem">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 text-center font-bold text-[11px]" id="header-bulan-row">
                            <th rowspan="2" class="py-2.5 px-2 border border-slate-200 w-8">No</th>
                            <th rowspan="2" class="py-2.5 px-3 border border-slate-200 w-56 text-left">Materi Pokok / TP / Asesmen</th>
                            <th rowspan="2" class="py-2.5 px-2 border border-slate-200 w-16">Alokasi JP</th>
                            <th colspan="5" class="py-1 px-1 border border-slate-200 header-b1">Juli</th>
                            <th colspan="5" class="py-1 px-1 border border-slate-200 header-b2">Agustus</th>
                            <th colspan="5" class="py-1 px-1 border border-slate-200 header-b3">September</th>
                            <th colspan="5" class="py-1 px-1 border border-slate-200 header-b4">Oktober</th>
                            <th colspan="5" class="py-1 px-1 border border-slate-200 header-b5">November</th>
                            <th colspan="5" class="py-1 px-1 border border-slate-200 header-b6">Desember</th>
                            <th rowspan="2" class="py-2.5 px-2 border border-slate-200 w-8">Aksi</th>
                        </tr>
                        <tr class="bg-slate-50 text-slate-500 text-[10px] text-center font-semibold">
                            <?php for ($m = 1; $m <= 6; $m++): ?>
                                <?php for ($w = 1; $w <= 5; $w++): ?>
                                    <th class="py-1 px-0.5 border border-slate-200 w-6"><?= $w ?></th>
                                <?php endfor; ?>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium" id="prosem-body">
                        <!-- Default Sample Rows -->
                        <?php
                        $sampleProsem = [
                            ['materi' => 'Bab 1: Narrative Text (Fairy Tale)', 'jp' => 12, 'active' => ['b1_w3' => 3, 'b1_w4' => 3, 'b2_w1' => 3, 'b2_w2' => 3]],
                            ['materi' => 'Sumatif Harian 1 & Remedial', 'jp' => 3, 'active' => ['b2_w3' => 3]],
                            ['materi' => 'Bab 2: Procedure Text (Recipes & Manuals)', 'jp' => 12, 'active' => ['b2_w4' => 3, 'b3_w1' => 3, 'b3_w2' => 3, 'b3_w4' => 3]],
                            ['materi' => 'Sumatif Tengah Semester (STS)', 'jp' => 3, 'active' => ['b3_w3' => 3]],
                            ['materi' => 'Bab 3: Report Text (Animals & Technology)', 'jp' => 15, 'active' => ['b4_w1' => 3, 'b4_w2' => 3, 'b4_w3' => 3, 'b4_w4' => 3, 'b4_w5' => 3]],
                            ['materi' => 'Bab 4: Song Lyrics & Figurative Language', 'jp' => 9, 'active' => ['b5_w1' => 3, 'b5_w2' => 3, 'b5_w3' => 3]],
                            ['materi' => 'Sumatif Akhir Semester (SAS) & Remedial', 'jp' => 6, 'active' => ['b5_w4' => 3, 'b6_w1' => 3]]
                        ];
                        foreach ($sampleProsem as $i => $row): ?>
                            <tr class="prosem-row">
                                <td class="py-1.5 px-2 text-center text-slate-400 border border-slate-200"><?= $i + 1 ?></td>
                                <td class="py-1.5 px-2 border border-slate-200">
                                    <input type="text" name="materi_pokok[]" value="<?= e($row['materi']) ?>" required placeholder="Materi pokok..." class="w-full px-2 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                </td>
                                <td class="py-1.5 px-2 text-center border border-slate-200">
                                    <input type="number" name="alokasi_jp[]" value="<?= $row['jp'] ?>" min="0" oninput="hitungProsemTotal()" class="prosem-jp-input w-14 px-1.5 py-1 rounded-lg border border-slate-200 text-xs text-center font-extrabold text-purple-800 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                </td>
                                <?php for ($m = 1; $m <= 6; $m++): ?>
                                    <?php for ($w = 1; $w <= 5; $w++): ?>
                                        <?php
                                        $cellKey = "b{$m}_w{$w}";
                                        $val = $row['active'][$cellKey] ?? '';
                                        ?>
                                        <td class="py-1 px-0.5 text-center border border-slate-200">
                                            <input type="text" name="matriks_b<?= $m ?>_w<?= $w ?>[]" value="<?= $val ?>" maxlength="2" placeholder="" class="w-6 h-6 text-center text-[10px] font-bold rounded <?= !empty($val) ? 'bg-purple-100 text-purple-900 border border-purple-300' : 'bg-white text-slate-600 border border-slate-100 hover:bg-slate-50' ?> focus:outline-none focus:ring-1 focus:ring-purple-500">
                                        </td>
                                    <?php endfor; ?>
                                <?php endfor; ?>
                                <td class="py-1.5 px-1 text-center border border-slate-200">
                                    <button type="button" onclick="hapusBarisProsem(this)" class="p-1 text-slate-400 hover:text-rose-500 transition-colors" title="Hapus Baris">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-purple-50/70 font-extrabold text-purple-950 border-t-2 border-purple-600">
                            <td colspan="2" class="py-2.5 px-4 text-right uppercase tracking-wider text-xs">Total Alokasi Semester:</td>
                            <td class="py-2.5 px-2 text-center text-sm font-mono text-purple-900" id="sum-prosem-jp">60 JP</td>
                            <td colspan="31" class="py-2.5 px-4 text-xs font-semibold text-purple-800">Distribusi KBM Efektif Selama 1 Semester</td>
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
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Dokumen Matriks Prosem (Word / PDF / Excel)</label>
                <input type="file" name="file_lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" name="draft" value="1" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Simpan Sebagai Draft
            </button>
            <button type="submit" name="ajukan" value="1" class="px-6 py-2.5 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-lg shadow-purple-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                Simpan & Ajukan Persetujuan
            </button>
        </div>
    </form>
</div>

<script>
const bulanGanjilHeaders = ['Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const bulanGenapHeaders = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];

function gantiSemesterProsem(smt) {
    const list = smt === 'Genap' ? bulanGenapHeaders : bulanGanjilHeaders;
    for (let i = 1; i <= 6; i++) {
        const th = document.querySelector(`.header-b${i}`);
        if (th) th.innerText = list[i - 1];
    }
}

function tambahBarisProsem() {
    const tbody = document.getElementById('prosem-body');
    const tr = document.createElement('tr');
    tr.className = 'prosem-row';
    const no = tbody.querySelectorAll('.prosem-row').length + 1;

    let cells = '';
    for (let m = 1; m <= 6; m++) {
        for (let w = 1; w <= 5; w++) {
            cells += `
                <td class="py-1 px-0.5 text-center border border-slate-200">
                    <input type="text" name="matriks_b${m}_w${w}[]" maxlength="2" placeholder="" class="w-6 h-6 text-center text-[10px] font-bold rounded bg-white text-slate-600 border border-slate-100 hover:bg-slate-50 focus:outline-none focus:ring-1 focus:ring-purple-500">
                </td>
            `;
        }
    }

    tr.innerHTML = `
        <td class="py-1.5 px-2 text-center text-slate-400 border border-slate-200">${no}</td>
        <td class="py-1.5 px-2 border border-slate-200">
            <input type="text" name="materi_pokok[]" required placeholder="Materi pokok..." class="w-full px-2 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-800 bg-slate-50 focus:outline-none focus:ring-1 focus:ring-purple-500">
        </td>
        <td class="py-1.5 px-2 text-center border border-slate-200">
            <input type="number" name="alokasi_jp[]" value="0" min="0" oninput="hitungProsemTotal()" class="prosem-jp-input w-14 px-1.5 py-1 rounded-lg border border-slate-200 text-xs text-center font-extrabold text-purple-800 focus:outline-none focus:ring-1 focus:ring-purple-500">
        </td>
        ${cells}
        <td class="py-1.5 px-1 text-center border border-slate-200">
            <button type="button" onclick="hapusBarisProsem(this)" class="p-1 text-slate-400 hover:text-rose-500 transition-colors" title="Hapus Baris">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    hitungProsemTotal();
}

function hapusBarisProsem(btn) {
    const row = btn.closest('tr');
    if (document.querySelectorAll('.prosem-row').length > 1) {
        row.remove();
        hitungProsemTotal();
    } else {
        alert('Minimal harus ada 1 baris materi.');
    }
}

function hitungProsemTotal() {
    const inputs = document.querySelectorAll('.prosem-jp-input');
    let sum = 0;
    inputs.forEach(inp => {
        sum += parseInt(inp.value) || 0;
    });
    document.getElementById('sum-prosem-jp').innerText = sum + ' JP';
}

function updateUnitSelection(radio) {
    document.querySelectorAll('.unit-card').forEach(card => {
        card.classList.remove('border-purple-600', 'bg-purple-50/40', 'ring-2', 'ring-purple-500/20', 'shadow-sm');
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
        selectedCard.classList.add('border-purple-600', 'bg-purple-50/40', 'ring-2', 'ring-purple-500/20', 'shadow-sm');
        const indicator = selectedCard.querySelector('.unit-check-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            indicator.classList.add('block');
        }
    }
}

document.addEventListener('DOMContentLoaded', hitungProsemTotal);
</script>
