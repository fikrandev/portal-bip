<?php
/**
 * View Edit Penugasan Pegawai dalam Grup SK
 */
$existingIsGuru = !empty($penugasan['is_guru']) || !empty($tugasMengajar);
$existingWaliKelas = $penugasan['wali_kelas_nama'] ?? '';
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grupId) ?>" class="p-2 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-slate-600 transition-colors shadow-xs">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Edit Penugasan Pegawai</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Grup: <strong class="text-primary-700"><?= e($grup['nama_grup'] ?? '-') ?></strong></p>
            </div>
        </div>
    </div>

    <!-- Form Input Card -->
    <div class="bg-white rounded-3xl border border-primary-100/60 shadow-sm p-6 sm:p-8 relative">
        <form action="<?= url('kelola-pegawai/penugasan/detail/update/' . $penugasan['id']) ?>" method="POST" enctype="multipart/form-data">
            <?= CSRF::field() ?>

            <div class="space-y-5">
                <!-- Pegawai -->
                <div class="relative">
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Pegawai <span class="text-rose-500">*</span></label>
                    <select name="pegawai_id" required class="searchable-select w-full" data-placeholder="-- Pilih Pegawai --" data-search-placeholder="Cari nama pegawai, NIY...">
                        <?php foreach ($pegawaiList as $p): ?>
                            <option value="<?= e($p['id']) ?>" 
                                    data-badge="<?= e($p['niy'] ?: 'Non-NIY') ?>" 
                                    data-image="<?= !empty($p['foto']) ? url(ltrim($p['foto'], '/')) : '' ?>"
                                    data-subtext="<?= !empty($p['gelar']) ? 'Gelar: ' . e($p['gelar']) : '' ?>"
                                    <?= old('pegawai_id', $penugasan['pegawai_id']) == $p['id'] ? 'selected' : '' ?>>
                                <?= e($p['nama']) ?><?= !empty($p['gelar']) ? ', ' . e($p['gelar']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Unit Tugas -->
                <div class="relative">
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Unit Tugas <span class="text-rose-500">*</span></label>
                    <select name="unit_tugas_id" id="select-unit-tugas" required class="searchable-select w-full" data-placeholder="-- Pilih Unit Tugas --" data-search-placeholder="Cari unit tugas..." onchange="onUnitChanged(this)">
                        <?php foreach ($unitTugasList as $ut): ?>
                            <option value="<?= e($ut['id']) ?>" data-unit-name="<?= e($ut['nama']) ?>" <?= old('unit_tugas_id', $penugasan['unit_tugas_id']) == $ut['id'] ? 'selected' : '' ?>>
                                <?= e($ut['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Jabatan -->
                <div class="relative">
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Jabatan / Posisi <span class="text-rose-500">*</span></label>
                    <select name="jabatan_id" id="select-jabatan" required class="searchable-select w-full" data-placeholder="-- Pilih Jabatan --" data-search-placeholder="Cari jabatan..." onchange="onJabatanChanged(this)">
                        <?php foreach ($jabatanList as $jb): ?>
                            <option value="<?= e($jb['id']) ?>" data-nama="<?= strtolower(e($jb['nama'])) ?>" <?= old('jabatan_id', $penugasan['jabatan_id']) == $jb['id'] ? 'selected' : '' ?>>
                                <?= e($jb['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Section Khusus Wali Kelas -->
                <div id="section-wali-kelas" class="border border-amber-200 bg-amber-50/40 rounded-2xl p-4 sm:p-5 shadow-xs transition-all relative" style="<?= !empty($existingWaliKelas) ? 'display: block;' : 'display: none;' ?>">
                    <div class="flex items-center gap-2.5 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            🏫
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Penugasan Wali Kelas</h3>
                            <p class="text-[11px] text-slate-500">Pilih kelas yang diampu sebagai Wali Kelas (tersinkron otomatis dengan Unit Sekolah siswa).</p>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Kelas / Rombel yang Diampu <span class="text-rose-500">*</span></label>
                        <select name="wali_kelas_nama" id="select-wali-kelas" class="searchable-select w-full" data-placeholder="-- Pilih Kelas Siswa --" data-search-placeholder="Cari kelas (misal: Kelas 7A, 1B)...">
                            <option value="">-- Pilih Kelas Siswa --</option>
                            <?php foreach ($kelasListWithUnit as $k): ?>
                                <option value="<?= e($k['nama_kelas']) ?>" data-unit="<?= e($k['unit']) ?>" <?= old('wali_kelas_nama', $existingWaliKelas) === $k['nama_kelas'] ? 'selected' : '' ?>>
                                    <?= e($k['nama_kelas']) ?> (Unit <?= e($k['unit']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Section Khusus Tugas Mengajar Guru -->
                <div class="border border-indigo-100 bg-gradient-to-br from-indigo-50/40 via-white to-blue-50/30 rounded-2xl p-4 sm:p-5 shadow-xs transition-all relative">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-indigo-100/70 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                                📚
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800">Tugas Mengajar & Mata Pelajaran (Khusus Guru / Pendidik)</h3>
                                <p class="text-[11px] text-slate-500">Tentukan mata pelajaran, kelas/rombel yang diampu (bisa pilih banyak), dan JP.</p>
                            </div>
                        </div>
                        
                        <label class="relative inline-flex items-center cursor-pointer select-none shrink-0">
                            <input type="checkbox" name="is_guru" id="toggle-is-guru" value="1" <?= $existingIsGuru ? 'checked' : '' ?> class="sr-only peer" onchange="toggleGuruSection(this.checked)">
                            <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-2 text-xs font-bold text-slate-700">Tugas Mengajar</span>
                        </label>
                    </div>

                    <div id="section-tugas-mengajar" class="space-y-3" style="<?= $existingIsGuru ? 'display: block;' : 'display: none;' ?>">
                        <!-- Repeater Header (Desktop) -->
                        <div class="hidden sm:grid sm:grid-cols-12 gap-2.5 px-3 py-2 bg-indigo-100/70 rounded-xl text-indigo-950 font-bold text-xs">
                            <div class="col-span-4">Mata Pelajaran <span class="text-rose-500">*</span></div>
                            <div class="col-span-4">Kelas / Rombel (Bisa Pilih Banyak) <span class="text-rose-500">*</span></div>
                            <div class="col-span-2 text-center">JP / Kelas <span class="text-rose-500">*</span></div>
                            <div class="col-span-2">Keterangan / Aksi</div>
                        </div>

                        <!-- Repeater Rows Container (Flexible Grid, No Overflow Clipping) -->
                        <div id="repeater-mapel-rows" class="space-y-3">
                            <!-- Dynamic rows will be inserted here -->
                        </div>

                        <!-- Action Row & Total Summary -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-indigo-100/70">
                            <button type="button" onclick="addMapelRow()" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200/80 transition-all shadow-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                <span>Tambah Mata Pelajaran & Kelas</span>
                            </button>

                            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-indigo-200 text-xs font-bold text-slate-700 shadow-xs">
                                <span>Total Beban Mengajar:</span>
                                <span id="badge-total-jp" class="px-2.5 py-0.5 rounded-lg bg-indigo-600 text-white font-extrabold text-xs"><?= (int)($penugasan['total_jp'] ?? 0) ?> JP / Minggu</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nomor SK -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Nomor SK Penugasan</label>
                    <input type="text" name="no_sk" value="<?= e(old('no_sk', $penugasan['no_sk'] ?? '')) ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- Tanggal SK -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal SK</label>
                    <input type="date" name="tanggal_sk" value="<?= e(old('tanggal_sk', $penugasan['tanggal_sk'] ?? '')) ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- TMT Mulai -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal Mulai Tugas (TMT) <span class="text-rose-500">*</span></label>
                    <input type="date" name="tmt_mulai" value="<?= e(old('tmt_mulai', $penugasan['tmt_mulai'] ?? '')) ?>" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                </div>

                <!-- TST Selesai -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal Selesai Tugas (TST)</label>
                    <input type="date" name="tst_selesai" value="<?= e(old('tst_selesai', $penugasan['tst_selesai'] ?? '')) ?>" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika masa tugas tidak dibatasi.</p>
                </div>

                <!-- Status Penugasan -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Status Penugasan</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        <option value="Aktif" <?= old('status', $penugasan['status']) === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Tidak Aktif" <?= old('status', $penugasan['status']) === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>

                <!-- Keterangan -->
                <div>
                    <label class="block text-sm font-semibold text-primary-900 mb-1.5">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="3" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm"><?= e(old('keterangan', $penugasan['keterangan'] ?? '')) ?></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grupId) ?>" class="px-5 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-600/25 transition-all">
                        Perbarui Penugasan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const initialTeachingItems = <?= json_encode($tugasMengajar ?? []) ?>;
const MASTER_MAPEL_DATA = <?= json_encode($masterMapel ?? []) ?>;
const MASTER_KELAS_DATA = <?= json_encode($kelasListWithUnit ?? []) ?>;
let currentSelectedUnit = '';
let repeaterRowCount = 0;

function onUnitChanged(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    currentSelectedUnit = (opt && opt.getAttribute('data-unit-name')) ? opt.getAttribute('data-unit-name').trim() : (opt ? opt.textContent.trim() : '');
    
    // Filter dropdown wali kelas jika ada
    const waliSelect = document.getElementById('select-wali-kelas');
    if (waliSelect && window.SearchableSelect) {
        SearchableSelect.filterByUnit(waliSelect, currentSelectedUnit);
    }

    // Filter seluruh baris dropdown mapel dan kelas di repeater
    filterRepeaterDropdownsByUnit(currentSelectedUnit);
}

function filterRepeaterDropdownsByUnit(unitName) {
    if (!window.SearchableSelect) return;
    const mapelSelects = document.querySelectorAll('#repeater-mapel-rows select[name="mapel_nama[]"]');
    mapelSelects.forEach(sel => SearchableSelect.filterByUnit(sel, unitName));
    const kelasSelects = document.querySelectorAll('#repeater-mapel-rows select[name^="mapel_kelas"]');
    kelasSelects.forEach(sel => SearchableSelect.filterByUnit(sel, unitName));
}

function onJabatanChanged(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    const namaJabatan = (opt && opt.getAttribute('data-nama')) ? opt.getAttribute('data-nama').toLowerCase() : (opt ? opt.textContent.toLowerCase() : '');
    
    // Check Wali Kelas
    const secWali = document.getElementById('section-wali-kelas');
    const selectWali = document.getElementById('select-wali-kelas');
    if (namaJabatan.includes('wali kelas')) {
        secWali.style.display = 'block';
        if (selectWali) selectWali.setAttribute('required', 'required');
        toggleGuruSection(true);
    } else {
        secWali.style.display = 'none';
        if (selectWali) {
            selectWali.removeAttribute('required');
        }
    }

    // Auto-detect posisi mengajar
    const isTeachingRole = ['guru', 'pendidik', 'pengajar', 'ustadz', 'ustadzah', 'wali kelas'].some(term => namaJabatan.includes(term));
    if (isTeachingRole) {
        toggleGuruSection(true);
    }
}

function toggleGuruSection(show) {
    const sec = document.getElementById('section-tugas-mengajar');
    const toggle = document.getElementById('toggle-is-guru');
    toggle.checked = show;
    if (show) {
        sec.style.display = 'block';
        const container = document.getElementById('repeater-mapel-rows');
        if (container.children.length === 0) {
            addMapelRow();
        }
    } else {
        sec.style.display = 'none';
    }
    calculateTotalJp();
}

function buildMapelSelectOptions(selectedVal) {
    let html = '<option value="">-- Pilih Mapel --</option>';
    MASTER_MAPEL_DATA.forEach(item => {
        const val = typeof item === 'object' ? item.nama_mapel : item;
        const badge = typeof item === 'object' ? (item.kode_mapel || '') : '';
        const unit = typeof item === 'object' ? (item.unit || '') : '';
        const subtext = typeof item === 'object' ? (item.kelompok || '') : '';
        const isSel = (String(selectedVal) === String(val)) ? 'selected' : '';
        html += `<option value="${val}" data-badge="${badge}" data-unit="${unit}" data-subtext="${subtext}" ${isSel}>${val}</option>`;
    });
    return html;
}

function buildKelasSelectOptions(selectedVals) {
    let html = '';
    const selectedArr = Array.isArray(selectedVals) ? selectedVals.map(String) : (selectedVals ? [String(selectedVals)] : []);
    MASTER_KELAS_DATA.forEach(item => {
        const val = item.nama_kelas;
        const unit = item.unit || '';
        const isSel = selectedArr.includes(String(val)) ? 'selected' : '';
        html += `<option value="${val}" data-unit="${unit}" ${isSel}>${val}</option>`;
    });
    return html;
}

function addMapelRow(data = {}) {
    const container = document.getElementById('repeater-mapel-rows');
    const row = document.createElement('div');
    const rowIdx = repeaterRowCount++;
    row.className = 'repeater-row grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center bg-white p-3 sm:p-0 rounded-2xl sm:rounded-none border sm:border-0 border-slate-200/80 shadow-xs sm:shadow-none relative';
    
    const mapelVal = data.mata_pelajaran || '';
    const kelasVal = data.nama_kelas || [];
    const jpVal = data.jumlah_jp || 2;
    const ketVal = data.keterangan || '';

    row.innerHTML = `
        <div class="sm:col-span-4 relative">
            <label class="sm:hidden block text-[11px] font-bold text-slate-600 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
            <select name="mapel_nama[]" class="searchable-select w-full" data-placeholder="-- Pilih Mapel --" data-search-placeholder="Cari mata pelajaran..." data-size="sm" required>
                ${buildMapelSelectOptions(mapelVal)}
            </select>
        </div>
        <div class="sm:col-span-4 relative">
            <label class="sm:hidden block text-[11px] font-bold text-slate-600 mb-1">Kelas / Rombel <span class="text-rose-500">*</span></label>
            <select name="mapel_kelas[${rowIdx}][]" multiple class="searchable-select w-full" data-placeholder="-- Pilih Kelas (Bisa Lebih dari 1) --" data-search-placeholder="Cari & centang kelas..." data-size="sm" data-multiple="true" required onchange="calculateTotalJp()">
                ${buildKelasSelectOptions(kelasVal)}
            </select>
        </div>
        <div class="sm:col-span-2 relative">
            <label class="sm:hidden block text-[11px] font-bold text-slate-600 mb-1">JP / Kelas <span class="text-rose-500">*</span></label>
            <input type="number" name="mapel_jp[]" min="1" max="40" value="${jpVal}" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 text-xs font-bold text-center focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-xs" oninput="calculateTotalJp()">
        </div>
        <div class="sm:col-span-2 flex items-center gap-1.5 relative">
            <input type="text" name="mapel_keterangan[]" value="${ketVal}" placeholder="Ket. (opsional)" class="flex-1 min-w-0 px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-700 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-xs">
            <button type="button" onclick="removeMapelRow(this)" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors shrink-0" title="Hapus Baris">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </button>
        </div>
    `;
    container.appendChild(row);

    // Inisialisasi Searchable Select pada baris baru
    if (window.SearchableSelect) {
        window.SearchableSelect.init(row);
        if (currentSelectedUnit) {
            const selects = row.querySelectorAll('select.searchable-select');
            selects.forEach(sel => window.SearchableSelect.filterByUnit(sel, currentSelectedUnit));
        }
    }

    calculateTotalJp();
}

function removeMapelRow(btn) {
    const row = btn.closest('.repeater-row');
    const container = document.getElementById('repeater-mapel-rows');
    if (container.children.length > 1) {
        row.remove();
    } else {
        // Reset nilai baris pertama
        const selects = row.querySelectorAll('select');
        selects.forEach(s => {
            if (s.multiple) {
                Array.from(s.options).forEach(o => o.selected = false);
            } else {
                s.value = '';
            }
            if (window.SearchableSelect) window.SearchableSelect.refresh(s);
        });
        const jp = row.querySelector('input[name="mapel_jp[]"]');
        if (jp) jp.value = 2;
        const ket = row.querySelector('input[name="mapel_keterangan[]"]');
        if (ket) ket.value = '';
    }
    calculateTotalJp();
}

function calculateTotalJp() {
    const isGuru = document.getElementById('toggle-is-guru').checked;
    if (!isGuru) {
        document.getElementById('badge-total-jp').textContent = '0 JP / Minggu';
        return;
    }

    const rows = document.querySelectorAll('#repeater-mapel-rows .repeater-row');
    let total = 0;
    rows.forEach(row => {
        const jpInp = row.querySelector('input[name="mapel_jp[]"]');
        const kelasSelect = row.querySelector('select[name^="mapel_kelas"]');
        const jpVal = parseInt(jpInp ? jpInp.value : 0, 10) || 0;
        
        let classCount = 1;
        if (kelasSelect) {
            const selectedClasses = Array.from(kelasSelect.options).filter(o => o.selected && o.value !== '');
            classCount = selectedClasses.length > 0 ? selectedClasses.length : 1;
        }
        total += (jpVal * classCount);
    });
    document.getElementById('badge-total-jp').textContent = total + ' JP / Minggu';
}

document.addEventListener('DOMContentLoaded', () => {
    const selUnit = document.getElementById('select-unit-tugas');
    if (selUnit && selUnit.value) {
        onUnitChanged(selUnit);
    }
    const selJabatan = document.getElementById('select-jabatan');
    if (selJabatan && selJabatan.value) {
        onJabatanChanged(selJabatan);
    }

    if (Array.isArray(initialTeachingItems) && initialTeachingItems.length > 0) {
        initialTeachingItems.forEach(item => addMapelRow(item));
        toggleGuruSection(true);
    } else if (document.getElementById('toggle-is-guru').checked) {
        addMapelRow();
    }
});
</script>
