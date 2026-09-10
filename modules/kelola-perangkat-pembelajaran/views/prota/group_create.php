<?php
/**
 * Buat Grup Program Tahunan (Prota) Baru
 * Terhubung dengan Grup Prosem (Bisa Centang 2: Ganjil & Genap)
 */
$selectedUnit = old('unit', $_GET['unit'] ?? 'SD');
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Buat Grup Program Tahunan (Prota)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Wadah grup dokumen Prota terpadu dari Program Semester (Prosem) Ganjil & Genap</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/prota') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form method="POST" action="<?= url('kelola-perangkat-pembelajaran/prota/group/store') ?>" class="space-y-6">
        <?= CSRF::field() ?>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> Identitas & Unit Wadah Prota
            </h2>

            <!-- Visual Unit Selector -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Unit Satuan Pendidikan <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($unit_list as $uKey => $uInfo): 
                        $isChecked = ($selectedUnit === $uKey);
                    ?>
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-indigo-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-indigo-600 bg-indigo-50/40 ring-2 ring-indigo-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio" onchange="onUnitChanged(this.value, this)">
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
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Wadah / Grup Prota <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" id="judul_grup" required value="<?= old('judul', "Program Tahunan (Prota) {$selectedUnit} TP 2026/2027") ?>" placeholder="Contoh: Program Tahunan (Prota) SD TP 2026/2027" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" id="ta_select" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-slate-50/50" required onchange="filterProsemOptions()">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($filter_ta == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PILIH SUMBER PROGRAM SEMESTER (PROSEM) BISA CENTANG 2 (GANJIL & GENAP) -->
                <div class="sm:col-span-2 pt-2 border-t border-slate-100 space-y-3">
                    <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200 flex items-center justify-between gap-3 text-xs text-indigo-950">
                        <div class="flex items-center gap-2">
                            <span class="text-base">ℹ️</span>
                            <span class="text-[11px] font-medium leading-relaxed">
                                <strong>Program Tahunan (Prota):</strong> Berlaku 1 tahun ajaran penuh sehingga <strong>tidak memerlukan pilihan semester</strong>. Anda cukup memilih dan mencentang sumber Grup Program Semester (Prosem) di bawah (bisa centang 2: Ganjil & Genap).
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <div>
                            <label class="block text-xs font-bold text-indigo-950">Pilih Sumber Program Semester (Prosem)</label>
                            <p class="text-[11px] text-slate-500">Centang Program Semester yang akan digunakan (bisa centang 2: Semester Ganjil & Semester Genap)</p>
                        </div>
                        <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-xl border border-indigo-200 self-start sm:self-auto">
                            Auto-Generate Per Guru & Mapel
                        </span>
                    </div>

                    <div class="space-y-2.5" id="prosemContainer">
                        <?php if (empty($prosem_groups)): ?>
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                                ⚠️ Belum ada data Grup Program Semester (Prosem) di sistem. Anda tetap dapat membuat wadah Prota dan menginput dokumen secara manual di dalamnya.
                            </div>
                        <?php else: ?>
                            <?php foreach ($prosem_groups as $pg): ?>
                                <label class="prosem-opt relative flex items-start gap-3.5 p-4 rounded-2xl border border-slate-200 hover:border-indigo-400 bg-slate-50/50 hover:bg-indigo-50/20 cursor-pointer transition-all"
                                       data-unit="<?= e($pg['unit']) ?>"
                                       data-ta="<?= (int)$pg['tahun_akademik_id'] ?>"
                                       data-semester="<?= e($pg['semester']) ?>">
                                    <input type="checkbox" name="prosem_group_ids[]" value="<?= $pg['id'] ?>" 
                                           class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-bold text-slate-800"><?= e($pg['judul']) ?></span>
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold <?= ($pg['semester'] === 'Ganjil') ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-300' ?>">
                                                Semester <?= e($pg['semester']) ?>
                                            </span>
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                Unit <?= e($pg['unit']) ?>
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 text-[11px] text-slate-500 mt-1">
                                            <span>Tahun Ajaran: <strong><?= e($pg['nama_tahun']) ?></strong></span>
                                            <span>&bull;</span>
                                            <span>Total Dokumen: <strong><?= (int)$pg['doc_count'] ?> Mapel</strong></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-indigo-50/80 border border-indigo-200 flex items-start gap-3 text-xs text-indigo-950">
                        <span class="text-xl">✨</span>
                        <div class="text-[11px] leading-relaxed">
                            <strong>Alur Otomatisasi Terpadu:</strong> Sistem akan membaca setiap mata pelajaran dan guru dari Program Semester yang dicentang. Bila Anda mencentang Semester Ganjil dan Semester Genap, sistem otomatis menggabungkannya ke dalam <strong>1 Dokumen Program Tahunan (Prota) per Guru & per Mapel</strong> lengkap dengan kolom <strong>NO, TP, ATP, JML JP, dan SMT (1 & 2)</strong>!
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-perangkat-pembelajaran/prota') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <span>Simpan & Susun Dokumen Prota</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function onUnitChanged(unit, element) {
    document.querySelectorAll('.unit-card').forEach(c => {
        c.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'ring-2', 'ring-indigo-500/20', 'shadow-sm');
        c.classList.add('border-slate-200', 'bg-white');
    });
    document.querySelectorAll('.unit-check-indicator').forEach(i => i.classList.add('hidden'));

    const card = element.closest('.unit-card');
    if (card) {
        card.classList.remove('border-slate-200', 'bg-white');
        card.classList.add('border-indigo-600', 'bg-indigo-50/40', 'ring-2', 'ring-indigo-500/20', 'shadow-sm');
        const check = card.querySelector('.unit-check-indicator');
        if (check) check.classList.remove('hidden');
    }

    const taSelect = document.getElementById('ta_select');
    const taText = taSelect ? taSelect.options[taSelect.selectedIndex]?.text || '2026/2027' : '2026/2027';
    const judulInput = document.getElementById('judul_grup');
    if (judulInput) {
        judulInput.value = 'Program Tahunan (Prota) ' + unit + ' TP ' + taText;
    }

    filterProsemOptions();
}

function filterProsemOptions() {
    const selectedUnitRadio = document.querySelector('input[name="unit"]:checked');
    const unit = selectedUnitRadio ? selectedUnitRadio.value : 'SD';
    const taId = document.getElementById('ta_select')?.value;

    const items = document.querySelectorAll('.prosem-opt');
    let visibleCount = 0;

    items.forEach(item => {
        const itemUnit = item.getAttribute('data-unit');
        const itemTa = item.getAttribute('data-ta');
        const matchUnit = !unit || itemUnit === unit;
        const matchTa = !taId || itemTa === taId;

        if (matchUnit && matchTa) {
            item.style.display = 'flex';
            visibleCount++;
        } else {
            item.style.display = 'none';
            const cb = item.querySelector('input[type="checkbox"]');
            if (cb) cb.checked = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    filterProsemOptions();
});
</script>
