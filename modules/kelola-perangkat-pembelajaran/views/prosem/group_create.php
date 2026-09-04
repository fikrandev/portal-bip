<?php
/**
 * Buat Grup Program Semester (Prosem) Baru
 * Terhubung dengan Grup CP & ATP untuk Auto-Generate Matriks Mingguan
 */
$selectedUnit = old('unit', $_GET['unit'] ?? 'SD');
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Buat Grup Program Semester (Prosem)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Wadah grup dokumen Prosem terintegrasi dengan data capaian dari Grup CP & ATP</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/prosem') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            &larr; Kembali
        </a>
    </div>

    <form method="POST" action="<?= url('kelola-perangkat-pembelajaran/prosem/group/store') ?>" class="space-y-6">
        <?= CSRF::field() ?>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span> Identitas & Unit Wadah Prosem
            </h2>

            <!-- Visual Unit Selector -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Unit Satuan Pendidikan <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($unit_list as $uKey => $uInfo): 
                        $isChecked = ($selectedUnit === $uKey);
                    ?>
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-purple-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-purple-600 bg-purple-50/40 ring-2 ring-purple-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio" onchange="onUnitChanged(this.value, this)">
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
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Wadah / Grup Prosem <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" id="judul_grup" required value="<?= old('judul') ?>" placeholder="Contoh: Kumpulan Program Semester (Prosem) Tahun 2026/2027 SD" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" id="ta_select" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50" required onchange="filterCpatpOptions()">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($filter_ta == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" id="semester_select" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-purple-500 focus:outline-none bg-slate-50/50" required onchange="filterCpatpOptions()">
                        <option value="Ganjil">Semester Ganjil (Juli - Desember)</option>
                        <option value="Genap">Semester Genap (Januari - Juni)</option>
                    </select>
                </div>

                <!-- Dropdown Pilih Grup CP & ATP Sumber Data -->
                <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-purple-900">Pilih Grup CP & ATP Sumber (Auto-Generate Matriks Prosem)</label>
                        <span class="text-[10px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-lg border border-purple-200">Sangat Direkomendasikan</span>
                    </div>
                    <select name="cpatp_group_id" id="cpatp_group_id" class="w-full px-4 py-3 rounded-2xl border-2 border-purple-200 text-xs font-semibold focus:ring-2 focus:ring-purple-500 focus:outline-none bg-purple-50/30 text-purple-950">
                        <option value="">-- Pilih Grup CP & ATP untuk Generate Matriks Otomatis --</option>
                        <?php foreach ($cpatp_groups ?? [] as $cg): ?>
                            <option value="<?= $cg['id'] ?>" 
                                    data-unit="<?= e($cg['unit']) ?>" 
                                    data-semester="<?= e($cg['semester']) ?>"
                                    class="cpatp-opt">
                                [Unit <?= e($cg['unit']) ?>] <?= e($cg['judul']) ?> - <?= e($cg['nama_tahun']) ?> (Smt <?= e($cg['semester']) ?>) — <?= (int)$cg['doc_count'] ?> Dokumen CP ATP
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="mt-2.5 p-3 rounded-2xl bg-amber-50/80 border border-amber-200/80 flex items-start gap-2.5 text-xs text-amber-800">
                        <span class="text-base">💡</span>
                        <div class="text-[11px] leading-relaxed">
                            <strong>Otomatisasi Penuh:</strong> Bila Anda memilih Grup CP & ATP, sistem akan langsung membaca seluruh dokumen CP & ATP di dalam grup tersebut. Setiap Tujuan Pembelajaran (TP) dan KKTP beserta <strong>Bulan dan Pekan</strong> yang sudah ditentukan guru akan langsung di-generate otomatis ke dalam <strong>Matriks Distribusi Pekan KBM Prosem (30 Pekan)</strong>.
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="<?= url('kelola-perangkat-pembelajaran/prosem') ?>" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs shadow-md shadow-purple-600/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>Simpan & Generate Prosem</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function onUnitChanged(unitVal, radioEl) {
    document.querySelectorAll('.unit-card').forEach(card => {
        card.classList.remove('border-purple-600', 'bg-purple-50/40', 'ring-2', 'ring-purple-500/20', 'shadow-sm');
        card.classList.add('border-slate-200', 'bg-white');
        const ind = card.querySelector('.unit-check-indicator');
        if (ind) { ind.classList.remove('block'); ind.classList.add('hidden'); }
    });
    const parent = radioEl.closest('.unit-card');
    if (parent) {
        parent.classList.remove('border-slate-200', 'bg-white');
        parent.classList.add('border-purple-600', 'bg-purple-50/40', 'ring-2', 'ring-purple-500/20', 'shadow-sm');
        const ind = parent.querySelector('.unit-check-indicator');
        if (ind) { ind.classList.remove('hidden'); ind.classList.add('block'); }
    }
    filterCpatpOptions();
}

function filterCpatpOptions() {
    const selectedUnit = document.querySelector('input[name="unit"]:checked')?.value || 'SD';
    const selectedSemester = document.getElementById('semester_select')?.value || 'Ganjil';
    const select = document.getElementById('cpatp_group_id');
    if (!select) return;

    let matchedCount = 0;
    Array.from(select.options).forEach((opt, idx) => {
        if (idx === 0) return;
        const u = opt.dataset.unit;
        const s = opt.dataset.semester;
        if (u === selectedUnit && s === selectedSemester) {
            opt.style.display = '';
            matchedCount++;
        } else {
            opt.style.display = 'none';
        }
    });

    const currentOpt = select.options[select.selectedIndex];
    if (currentOpt && currentOpt.style.display === 'none') {
        select.value = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    filterCpatpOptions();
});
</script>
