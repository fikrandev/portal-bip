<?php
/**
 * Prota - Edit View
 * Form Edit Matriks TP, ATP, JML JP, SMT, & Penilaian Harian
 */
$selectedUnit = old('unit', $item['unit'] ?? 'SD');
$protaRows = $konten['prota_rows'] ?? [];

// Fallback jika data lama format materi_list
if (empty($protaRows) && !empty($konten['materi_list'])) {
    $legacyList = $konten['materi_list'];
    $rNo = 1;
    foreach ($legacyList as $l) {
        $jp1 = (int)($l['jp_smt1'] ?? 0);
        $jp2 = (int)($l['jp_smt2'] ?? 0);
        $smt = ($jp2 > 0 && $jp1 == 0) ? 2 : 1;
        $atpJp = $smt == 1 ? ($jp1 ?: 2) : ($jp2 ?: 2);

        $protaRows[] = [
            'no' => $rNo++,
            'tp' => $l['cp_kd'] ?: $l['materi_pokok'],
            'atp_list' => [
                ['atp' => $l['materi_pokok'], 'jp' => $atpJp]
            ],
            'penilaian_harian' => [
                'label' => 'Penilaian Harian',
                'jp' => 4
            ],
            'semester' => $smt
        ];
    }
}

if (empty($protaRows)) {
    $protaRows[] = [
        'no' => 1,
        'tp' => '',
        'atp_list' => [
            ['atp' => '', 'jp' => 2]
        ],
        'penilaian_harian' => [
            'label' => 'Penilaian Harian',
            'jp' => 4
        ],
        'semester' => 1
    ];
}

$targetGroupId = $konten['prota_group_id'] ?? null;
?>
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Edit Program Tahunan (Prota)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui alokasi waktu dan pemetaan TP & ATP per semester</p>
        </div>
        <a href="<?= $targetGroupId ? url("kelola-perangkat-pembelajaran/prota/group/{$targetGroupId}") : url('kelola-perangkat-pembelajaran/prota') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/prota/update/{$item['id']}") ?>" class="space-y-6" id="protaForm">
        <?= CSRF::field() ?>

        <!-- Identitas Dokumen & Guru Picker -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> 1. Identitas Guru & Sekolah
            </h2>

            <!-- Searchable Live Search Guru Picker -->
            <?php
            $picker_label = 'Guru Pengampu / Penyusun Prota';
            $picker_accent = 'indigo';
            $selected_guru_id = old('guru_id', $item['guru_id']);
            $selected_guru_nama = old('guru_nama', $item['guru_nama']);
            $selected_guru_nip = old('guru_nip', $item['guru_nip']);
            include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/guru_picker.php';
            ?>

            <!-- Visual Unit Selector -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Unit Satuan Pendidikan <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($unit_list as $uKey => $uInfo): 
                        $isChecked = ($selectedUnit === $uKey);
                    ?>
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-indigo-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio">
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
                    <input type="text" name="judul" required value="<?= old('judul', $item['judul']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="mata_pelajaran" required value="<?= old('mata_pelajaran', $item['mata_pelajaran']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat / Kelas <span class="text-rose-500">*</span></label>
                    <input type="text" name="tingkat_kelas" required value="<?= old('tingkat_kelas', $item['tingkat_kelas']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fase Kurikulum</label>
                    <select name="fase" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                        <option value="">Pilih Fase</option>
                        <?php foreach (['A (SD 1-2)', 'B (SD 3-4)', 'C (SD 5-6)', 'D (SMP 7-9)', 'E (SMA 10)', 'F (SMA 11-12)'] as $f): ?>
                            <option value="<?= $f ?>" <?= ($item['fase'] === $f) ? 'selected' : '' ?>><?= $f ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($item['tahun_akademik_id'] == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabel Matriks TP, ATP, JML JP, SMT Sesuai Format Kurikulum Merdeka -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> 2. Matriks Program Tahunan (TP, ATP, JP, SMT)
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Sesuai format resmi Kurikulum Merdeka: Kolom NO, TP, ATP, JML JP, SMT, dan baris Penilaian Harian</p>
                </div>
                <button type="button" onclick="addTpBlock()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition-colors self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>+ Tambah Lingkup Materi / TP</span>
                </button>
            </div>

            <!-- TP Container -->
            <div id="tpContainer" class="space-y-4">
                <?php foreach ($protaRows as $tpIdx => $tpItem): 
                    $smtVal = (int)($tpItem['semester'] ?? 1);
                    $atpArr = $tpItem['atp_list'] ?? [];
                    if (empty($atpArr)) {
                        $atpArr[] = ['atp' => '', 'jp' => 2];
                    }
                    $ph = $tpItem['penilaian_harian'] ?? ['label' => 'Penilaian Harian', 'jp' => 4];
                ?>
                <div class="tp-block rounded-2xl border-2 border-slate-200/80 p-4 bg-slate-50/40 space-y-3" data-tp-idx="<?= $tpIdx ?>">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center tp-number"><?= ($tpIdx + 1) ?></span>
                            <span class="text-xs font-bold text-slate-800 uppercase">Tujuan Pembelajaran (TP)</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <label class="text-[11px] font-bold text-slate-600">Semester:</label>
                                <select name="prota_rows[<?= $tpIdx ?>][semester]" onchange="calculateTotals()" class="px-2.5 py-1 rounded-xl border border-slate-200 text-xs font-bold bg-white text-indigo-700">
                                    <option value="1" <?= ($smtVal === 1) ? 'selected' : '' ?>>Semester 1 (Ganjil)</option>
                                    <option value="2" <?= ($smtVal === 2) ? 'selected' : '' ?>>Semester 2 (Genap)</option>
                                </select>
                            </div>
                            <button type="button" onclick="removeTpBlock(this)" class="p-1 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Blok TP">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- TP Input -->
                    <div>
                        <textarea name="prota_rows[<?= $tpIdx ?>][tp]" rows="2" placeholder="Tuliskan Tujuan Pembelajaran utama..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 bg-white" required><?= e($tpItem['tp']) ?></textarea>
                    </div>

                    <!-- Sub Table ATP -->
                    <div class="space-y-2 bg-white rounded-xl p-3 border border-slate-200/80">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 border-b border-slate-100 pb-1.5">
                            <span>Alur Tujuan Pembelajaran (ATP / Sub-Materi)</span>
                            <span class="w-20 text-center">JML JP</span>
                        </div>
                        <div class="atp-list space-y-2" data-tp-idx="<?= $tpIdx ?>">
                            <?php foreach ($atpArr as $aIdx => $a): ?>
                            <div class="atp-row flex items-center gap-2">
                                <input type="text" name="prota_rows[<?= $tpIdx ?>][atp_list][<?= $aIdx ?>][atp]" value="<?= e($a['atp']) ?>" placeholder="Tuliskan butir capaian ATP..." class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs bg-slate-50/50 focus:bg-white" required>
                                <input type="number" name="prota_rows[<?= $tpIdx ?>][atp_list][<?= $aIdx ?>][jp]" value="<?= (int)($a['jp'] ?? 2) ?>" min="1" onchange="calculateTotals()" class="w-20 px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-center font-bold bg-slate-50/50 focus:bg-white atp-jp" required>
                                <button type="button" onclick="removeAtpRow(this)" class="p-1 text-slate-300 hover:text-rose-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="pt-1">
                            <button type="button" onclick="addAtpRow(this, <?= $tpIdx ?>)" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                                <span>+ Tambah Butir ATP</span>
                            </button>
                        </div>
                    </div>

                    <!-- Penilaian Harian Row -->
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/60 border border-amber-200/60 text-xs">
                        <div class="flex items-center gap-2 flex-1">
                            <span class="font-bold text-amber-800">📝 Baris Asesmen:</span>
                            <input type="text" name="prota_rows[<?= $tpIdx ?>][ph_label]" value="<?= e($ph['label'] ?? 'Penilaian Harian') ?>" class="px-2.5 py-1 rounded-lg border border-amber-200 text-xs bg-white text-slate-800 font-semibold w-56">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-bold text-amber-900">Alokasi JP:</span>
                            <input type="number" name="prota_rows[<?= $tpIdx ?>][ph_jp]" value="<?= (int)($ph['jp'] ?? 4) ?>" min="0" onchange="calculateTotals()" class="w-16 px-2.5 py-1 rounded-lg border border-amber-300 text-xs text-center font-black bg-white text-amber-900 ph-jp">
                            <span class="text-[11px] text-amber-700 font-bold">JP</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Rekapitulasi Alokasi JP Bar -->
            <div class="p-4 rounded-2xl bg-indigo-900 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📊</span>
                    <div>
                        <h3 class="font-bold text-xs uppercase tracking-wider text-indigo-200">Rekapitulasi Total Alokasi Jam Pelajaran (JP)</h3>
                        <p class="text-[11px] text-indigo-300">Dihitung otomatis dari seluruh butir ATP & Penilaian Harian</p>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <div class="text-center px-3 py-1.5 rounded-xl bg-indigo-800/80 border border-indigo-700">
                        <div class="text-[10px] text-indigo-300 uppercase font-bold">Semester 1</div>
                        <div class="text-sm font-black text-white" id="rekapSmt1">0 JP</div>
                    </div>
                    <div class="text-center px-3 py-1.5 rounded-xl bg-indigo-800/80 border border-indigo-700">
                        <div class="text-[10px] text-indigo-300 uppercase font-bold">Semester 2</div>
                        <div class="text-sm font-black text-white" id="rekapSmt2">0 JP</div>
                    </div>
                    <div class="text-center px-4 py-1.5 rounded-xl bg-emerald-500 text-white shadow-sm font-black">
                        <div class="text-[10px] text-emerald-100 uppercase">Total 1 Tahun</div>
                        <div class="text-base" id="rekapTotal">0 JP</div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= $targetGroupId ? url("kelola-perangkat-pembelajaran/prota/group/{$targetGroupId}") : url('kelola-perangkat-pembelajaran/prota') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                    Batal
                </a>
                <button type="submit" name="simpan" value="draft" class="px-5 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition-colors">
                    Simpan Perubahan
                </button>
                <button type="submit" name="ajukan" value="1" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <span>Simpan & Ajukan Verifikasi</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let tpCount = <?= count($protaRows) ?>;

function addTpBlock() {
    const container = document.getElementById('tpContainer');
    const idx = tpCount++;

    const div = document.createElement('div');
    div.className = 'tp-block rounded-2xl border-2 border-slate-200/80 p-4 bg-slate-50/40 space-y-3';
    div.setAttribute('data-tp-idx', idx);

    div.innerHTML = `
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-xl bg-indigo-600 text-white font-black text-xs flex items-center justify-center tp-number">${container.children.length + 1}</span>
                <span class="text-xs font-bold text-slate-800 uppercase">Tujuan Pembelajaran (TP)</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <label class="text-[11px] font-bold text-slate-600">Semester:</label>
                    <select name="prota_rows[${idx}][semester]" onchange="calculateTotals()" class="px-2.5 py-1 rounded-xl border border-slate-200 text-xs font-bold bg-white text-indigo-700">
                        <option value="1">Semester 1 (Ganjil)</option>
                        <option value="2">Semester 2 (Genap)</option>
                    </select>
                </div>
                <button type="button" onclick="removeTpBlock(this)" class="p-1 rounded-lg text-rose-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus Blok TP">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div>
            <textarea name="prota_rows[${idx}][tp]" rows="2" placeholder="Tuliskan Tujuan Pembelajaran utama..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 bg-white" required></textarea>
        </div>

        <div class="space-y-2 bg-white rounded-xl p-3 border border-slate-200/80">
            <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 border-b border-slate-100 pb-1.5">
                <span>Alur Tujuan Pembelajaran (ATP / Sub-Materi)</span>
                <span class="w-20 text-center">JML JP</span>
            </div>
            <div class="atp-list space-y-2" data-tp-idx="${idx}">
                <div class="atp-row flex items-center gap-2">
                    <input type="text" name="prota_rows[${idx}][atp_list][0][atp]" placeholder="Tuliskan butir capaian ATP..." class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs bg-slate-50/50 focus:bg-white" required>
                    <input type="number" name="prota_rows[${idx}][atp_list][0][jp]" value="2" min="1" onchange="calculateTotals()" class="w-20 px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-center font-bold bg-slate-50/50 focus:bg-white atp-jp" required>
                    <button type="button" onclick="removeAtpRow(this)" class="p-1 text-slate-300 hover:text-rose-500">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
            <div class="pt-1">
                <button type="button" onclick="addAtpRow(this, ${idx})" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1">
                    <span>+ Tambah Butir ATP</span>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/60 border border-amber-200/60 text-xs">
            <div class="flex items-center gap-2 flex-1">
                <span class="font-bold text-amber-800">📝 Baris Asesmen:</span>
                <input type="text" name="prota_rows[${idx}][ph_label]" value="Penilaian Harian" class="px-2.5 py-1 rounded-lg border border-amber-200 text-xs bg-white text-slate-800 font-semibold w-56">
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold text-amber-900">Alokasi JP:</span>
                <input type="number" name="prota_rows[${idx}][ph_jp]" value="4" min="0" onchange="calculateTotals()" class="w-16 px-2.5 py-1 rounded-lg border border-amber-300 text-xs text-center font-black bg-white text-amber-900 ph-jp">
                <span class="text-[11px] text-amber-700 font-bold">JP</span>
            </div>
        </div>
    `;

    container.appendChild(div);
    renumberTpBlocks();
    calculateTotals();
}

function removeTpBlock(btn) {
    const block = btn.closest('.tp-block');
    const container = document.getElementById('tpContainer');
    if (container.children.length > 1) {
        block.remove();
        renumberTpBlocks();
        calculateTotals();
    } else {
        alert('Minimal harus ada 1 blok Tujuan Pembelajaran (TP).');
    }
}

function renumberTpBlocks() {
    const blocks = document.querySelectorAll('.tp-block');
    blocks.forEach((b, i) => {
        const numSpan = b.querySelector('.tp-number');
        if (numSpan) numSpan.textContent = (i + 1);
    });
}

function addAtpRow(btn, tpIdx) {
    const list = btn.closest('.space-y-2').querySelector('.atp-list');
    const rowIdx = list.children.length;

    const row = document.createElement('div');
    row.className = 'atp-row flex items-center gap-2';
    row.innerHTML = `
        <input type="text" name="prota_rows[${tpIdx}][atp_list][${rowIdx}][atp]" placeholder="Tuliskan butir capaian ATP..." class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 text-xs bg-slate-50/50 focus:bg-white" required>
        <input type="number" name="prota_rows[${tpIdx}][atp_list][${rowIdx}][jp]" value="2" min="1" onchange="calculateTotals()" class="w-20 px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-center font-bold bg-slate-50/50 focus:bg-white atp-jp" required>
        <button type="button" onclick="removeAtpRow(this)" class="p-1 text-slate-300 hover:text-rose-500">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    `;
    list.appendChild(row);
    calculateTotals();
}

function removeAtpRow(btn) {
    const row = btn.closest('.atp-row');
    const list = row.closest('.atp-list');
    if (list.children.length > 1) {
        row.remove();
        calculateTotals();
    } else {
        alert('Setiap TP minimal memiliki 1 butir ATP.');
    }
}

function calculateTotals() {
    let smt1 = 0;
    let smt2 = 0;

    document.querySelectorAll('.tp-block').forEach(block => {
        const smtSelect = block.querySelector('select[name$="[semester]"]');
        const smt = smtSelect ? parseInt(smtSelect.value, 10) : 1;

        let blockJp = 0;
        block.querySelectorAll('.atp-jp').forEach(inp => {
            blockJp += parseInt(inp.value, 10) || 0;
        });

        const phInp = block.querySelector('.ph-jp');
        if (phInp) {
            blockJp += parseInt(phInp.value, 10) || 0;
        }

        if (smt === 1) {
            smt1 += blockJp;
        } else {
            smt2 += blockJp;
        }
    });

    const total = smt1 + smt2;
    document.getElementById('rekapSmt1').textContent = smt1 + ' JP';
    document.getElementById('rekapSmt2').textContent = smt2 + ' JP';
    document.getElementById('rekapTotal').textContent = total + ' JP';
}

document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();
});
</script>
