<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-black bg-teal-50 text-teal-700 border border-teal-200">
                    Edit Wadah Nilai
                </span>
                <?php if (!empty($group['is_active'])): ?>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        ● Wadah Aktif
                    </span>
                <?php else: ?>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        ○ Nonaktif
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight mt-1.5">Edit Grup Nilai</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui informasi nama wadah, unit, status keaktifan, dan keterkaitan CP &amp; ATP</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-nilai/group') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs sm:text-sm shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <form action="<?= url('kelola-nilai/group/update/' . $group['id']) ?>" method="POST" id="formEditGroupNilai">
            <?= CSRF::field() ?>
            
            <div class="p-6 sm:p-8 space-y-8">

                <!-- Step 1: Status & Pengaturan Wadah -->
                <div>
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-black flex items-center justify-center">1</span>
                        Status &amp; Informasi Utama Wadah
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Status Keaktifan Wadah -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-600 uppercase">Status Keaktifan Wadah <span class="text-red-500">*</span></label>
                            <p class="text-xs text-slate-400">Pilih apakah wadah ini aktif digunakan atau dinonaktifkan sementara</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl pt-1">
                                <!-- Option Aktif -->
                                <label class="status-option relative flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all <?= !empty($group['is_active']) ? 'border-teal-500 bg-teal-50/40 ring-2 ring-teal-500/20' : 'border-slate-200 bg-white hover:border-slate-300' ?>">
                                    <input type="radio" name="is_active" value="1" class="sr-only status-radio" <?= !empty($group['is_active']) ? 'checked' : '' ?> onchange="onStatusChanged(this)">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0 mt-0.5">
                                        ✓
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-800">Wadah Aktif</span>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-100 text-emerald-800">Aktif</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Wadah aktif, penginputan dan perubahan nilai siswa dapat dilakukan.</p>
                                    </div>
                                    <div class="status-check text-teal-600 <?= !empty($group['is_active']) ? 'block' : 'hidden' ?>">
                                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                    </div>
                                </label>

                                <!-- Option Nonaktif -->
                                <label class="status-option relative flex items-start gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all <?= empty($group['is_active']) ? 'border-amber-500 bg-amber-50/30 ring-2 ring-amber-500/20' : 'border-slate-200 bg-white hover:border-slate-300' ?>">
                                    <input type="radio" name="is_active" value="0" class="sr-only status-radio" <?= empty($group['is_active']) ? 'checked' : '' ?> onchange="onStatusChanged(this)">
                                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg shrink-0 mt-0.5">
                                        ⏸
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-800">Nonaktifkan Wadah</span>
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black bg-slate-100 text-slate-600">Nonaktif</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Wadah ditutup sementara/diarsipkan. Data nilai tetap aman tersimpan.</p>
                                    </div>
                                    <div class="status-check text-amber-600 <?= empty($group['is_active']) ? 'block' : 'hidden' ?>">
                                        <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Judul Grup -->
                        <div class="space-y-2 max-w-2xl">
                            <label class="block text-xs font-bold text-slate-600 uppercase">Nama Wadah Grup Nilai <span class="text-red-500">*</span></label>
                            <input type="text" name="judul" value="<?= e($group['judul']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50" placeholder="Cth: Penilaian Semester Ganjil 2026/2027" required>
                        </div>
                        
                        <!-- Visual Unit Selector -->
                        <div class="pt-2">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-3">Pilih Unit Satuan Pendidikan <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-3xl">
                                <?php foreach ($unit_list as $uKey => $uInfo): 
                                    $isSelectedUnit = ($group['unit'] === $uKey);
                                ?>
                                    <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-teal-400 unit-card <?= $isSelectedUnit ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-500/20 shadow-sm' : 'border-slate-200 bg-white hover:bg-slate-50/80' ?>">
                                        <input type="radio" name="unit" value="<?= $uKey ?>" class="sr-only unit-radio" <?= $isSelectedUnit ? 'checked' : '' ?> onchange="onUnitChanged(this.value, this)" required>
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl mb-1.5 <?= $uInfo['bg_soft'] ?>">
                                            <?= $uInfo['icon'] ?>
                                        </div>
                                        <span class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></span>
                                        <span class="text-[10px] text-slate-500 text-center leading-tight mt-0.5"><?= e($uInfo['name']) ?></span>
                                        <div class="unit-check-indicator absolute top-2 right-2 <?= $isSelectedUnit ? 'block' : 'hidden' ?> text-teal-600">
                                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Pilih Kaitan CP & ATP -->
                <div class="pt-2 border-t border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-black flex items-center justify-center">2</span>
                        Pilih Kaitan Dokumen CP &amp; ATP
                    </h3>
                    
                    <!-- Empty State (before unit selected) -->
                    <div id="cpatp_empty" class="text-center py-8 hidden">
                        <div class="w-14 h-14 mx-auto bg-slate-100 rounded-full flex items-center justify-center text-2xl mb-3">📋</div>
                        <p class="text-sm font-semibold text-slate-600">Pilih Unit Sekolah terlebih dahulu</p>
                        <p class="text-xs text-slate-400 mt-1">Daftar CP &amp; ATP akan muncul sesuai unit yang dipilih</p>
                    </div>

                    <!-- No Match State -->
                    <div id="cpatp_nomatch" class="text-center py-8 hidden">
                        <div class="w-14 h-14 mx-auto bg-amber-50 rounded-full flex items-center justify-center text-2xl mb-3">⚠️</div>
                        <p class="text-sm font-semibold text-slate-600">Tidak ada CP &amp; ATP aktif untuk unit ini</p>
                        <p class="text-xs text-slate-400 mt-1">Silakan buat CP &amp; ATP di modul Perangkat Pembelajaran terlebih dahulu</p>
                    </div>

                    <!-- CP ATP Cards -->
                    <div id="cpatp_container" class="space-y-3">
                        <?php foreach ($cpatp_groups as $cg): 
                            $isSelectedCpatp = ((int)$group['cpatp_id'] === (int)$cg['id']);
                        ?>
                            <label class="cpatp-card relative flex items-start gap-4 p-4 rounded-2xl border-2 transition-all cursor-pointer <?= $isSelectedCpatp ? 'border-teal-500 bg-teal-50/30 ring-2 ring-teal-500/20' : 'border-slate-200 bg-slate-50/50 hover:border-teal-400 hover:bg-teal-50/20' ?>"
                                   data-unit="<?= e($cg['unit']) ?>">
                                <input type="radio" name="cpatp_id" value="<?= $cg['id'] ?>" 
                                       class="mt-0.5 w-4 h-4 text-teal-600 border-slate-300 focus:ring-teal-500" 
                                       <?= $isSelectedCpatp ? 'checked' : '' ?> required>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap mb-1.5">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-indigo-100 text-indigo-800 border border-indigo-200"><?= e($cg['unit']) ?></span>
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"><?= e($cg['nama_tahun'] ?? '') ?> - <?= e($cg['semester']) ?></span>
                                        <?php if (!empty($cg['mata_pelajaran'])): ?>
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200"><?= e($cg['mata_pelajaran']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($cg['tingkat_kelas'])): ?>
                                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200">Kelas <?= e($cg['tingkat_kelas']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="font-bold text-slate-800 text-sm leading-snug"><?= e($cg['judul']) ?></h4>
                                    <?php if (!empty($cg['guru_nama'])): ?>
                                        <p class="text-[11px] text-slate-500 mt-1">👨‍🏫 <?= e($cg['guru_nama']) ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="cpatp-check <?= $isSelectedCpatp ? 'flex' : 'hidden' ?> w-6 h-6 rounded-full bg-teal-600 text-white items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Footer Actions -->
            <div class="px-6 sm:px-8 py-5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <a href="<?= url('kelola-nilai/group') ?>" class="px-5 py-2.5 rounded-2xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-sm transition-colors shadow-sm">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-md shadow-teal-500/20 transition-all inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function onStatusChanged(radioEl) {
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.classList.remove('border-teal-500', 'bg-teal-50/40', 'ring-2', 'ring-teal-500/20', 'border-amber-500', 'bg-amber-50/30', 'ring-amber-500/20');
        opt.classList.add('border-slate-200', 'bg-white');
        const chk = opt.querySelector('.status-check');
        if (chk) chk.classList.add('hidden');
    });

    const parent = radioEl.closest('.status-option');
    if (parent) {
        parent.classList.remove('border-slate-200', 'bg-white');
        if (radioEl.value === '1') {
            parent.classList.add('border-teal-500', 'bg-teal-50/40', 'ring-2', 'ring-teal-500/20');
        } else {
            parent.classList.add('border-amber-500', 'bg-amber-50/30', 'ring-2', 'ring-amber-500/20');
        }
        const chk = parent.querySelector('.status-check');
        if (chk) chk.classList.remove('hidden');
    }
}

function onUnitChanged(unitVal, radioEl) {
    // Reset unit cards styling
    document.querySelectorAll('.unit-card').forEach(card => {
        card.classList.remove('border-teal-600', 'bg-teal-50/40', 'ring-2', 'ring-teal-500/20', 'shadow-sm');
        card.classList.add('border-slate-200', 'bg-white');
        const ind = card.querySelector('.unit-check-indicator');
        if (ind) { ind.classList.remove('block'); ind.classList.add('hidden'); }
    });
    
    // Highlight selected unit card
    const parent = radioEl.closest('.unit-card');
    if (parent) {
        parent.classList.remove('border-slate-200', 'bg-white');
        parent.classList.add('border-teal-600', 'bg-teal-50/40', 'ring-2', 'ring-teal-500/20', 'shadow-sm');
        const ind = parent.querySelector('.unit-check-indicator');
        if (ind) { ind.classList.remove('hidden'); ind.classList.add('block'); }
    }
    
    // Filter CP ATP cards
    filterCpatpByUnit(unitVal);
}

function filterCpatpByUnit(unit) {
    const cards = document.querySelectorAll('.cpatp-card');
    const container = document.getElementById('cpatp_container');
    const empty = document.getElementById('cpatp_empty');
    const nomatch = document.getElementById('cpatp_nomatch');
    
    if (!unit) {
        container.classList.add('hidden');
        nomatch.classList.add('hidden');
        empty.classList.remove('hidden');
        return;
    }
    
    let visibleCount = 0;
    
    cards.forEach(card => {
        const cardUnit = card.dataset.unit;
        if (cardUnit === unit) {
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.classList.add('hidden');
        }
    });
    
    empty.classList.add('hidden');
    
    if (visibleCount > 0) {
        container.classList.remove('hidden');
        nomatch.classList.add('hidden');
    } else {
        container.classList.add('hidden');
        nomatch.classList.remove('hidden');
    }
    
    updateCheckmarks();
}

function updateCheckmarks() {
    document.querySelectorAll('.cpatp-card').forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        const check = card.querySelector('.cpatp-check');
        if (radio && radio.checked) {
            card.classList.remove('border-slate-200');
            card.classList.add('border-teal-500', 'bg-teal-50/30', 'ring-2', 'ring-teal-500/20');
            if (check) {
                check.classList.remove('hidden');
                check.classList.add('flex');
            }
        } else {
            card.classList.add('border-slate-200');
            card.classList.remove('border-teal-500', 'bg-teal-50/30', 'ring-2', 'ring-teal-500/20');
            if (check) {
                check.classList.add('hidden');
                check.classList.remove('flex');
            }
        }
    });
}

// Listen for CP ATP radio changes
document.querySelectorAll('.cpatp-card input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', updateCheckmarks);
});

// Init on load
document.addEventListener('DOMContentLoaded', () => {
    const checkedUnit = document.querySelector('.unit-radio:checked');
    if (checkedUnit) {
        filterCpatpByUnit(checkedUnit.value);
    }
    updateCheckmarks();
});
</script>
