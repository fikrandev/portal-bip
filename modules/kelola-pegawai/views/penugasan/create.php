<?php
/**
 * View Tambah Anggota Penugasan dalam Grup SK (Mendukung Multi-Entry / Batch 1 SK)
 */

$pegawaiOptionsJs = array_map(static function ($p) {
    return [
        'id' => (int)$p['id'],
        'nama' => (string)($p['nama'] ?? ''),
        'gelar' => (string)($p['gelar'] ?? ''),
        'niy' => (string)(!empty($p['niy']) ? $p['niy'] : 'Non-NIY'),
        'foto' => !empty($p['foto']) ? url(ltrim((string)$p['foto'], '/')) : '',
    ];
}, $pegawaiList ?? []);

$unitTugasOptionsJs = array_map(static function ($ut) {
    return [
        'id' => (int)$ut['id'],
        'nama' => (string)($ut['nama'] ?? ''),
    ];
}, $unitTugasList ?? []);

$jabatanOptionsJs = array_map(static function ($jb) {
    return [
        'id' => (int)$jb['id'],
        'nama' => (string)($jb['nama'] ?? ''),
    ];
}, $jabatanList ?? []);

$oldItems = [];
if (!empty($_SESSION['old_input']['items']) && is_array($_SESSION['old_input']['items'])) {
    foreach (array_values($_SESSION['old_input']['items']) as $it) {
        if (!is_array($it)) continue;
        $it['__has_explicit_guru'] = true;
        $oldItems[] = $it;
    }
} elseif (
    !empty($_SESSION['old_input']['pegawai_id']) ||
    !empty($_SESSION['old_input']['unit_tugas_id']) ||
    !empty($_SESSION['old_input']['jabatan_id'])
) {
    $oldItems[] = [
        'pegawai_id' => $_SESSION['old_input']['pegawai_id'] ?? '',
        'unit_tugas_id' => $_SESSION['old_input']['unit_tugas_id'] ?? '',
        'jabatan_id' => $_SESSION['old_input']['jabatan_id'] ?? '',
        'wali_kelas_nama' => $_SESSION['old_input']['wali_kelas_nama'] ?? '',
        'is_guru' => $_SESSION['old_input']['is_guru'] ?? 0,
        'mapel_nama' => $_SESSION['old_input']['mapel_nama'] ?? [],
        'mapel_kelas' => $_SESSION['old_input']['mapel_kelas'] ?? [],
        'mapel_jp' => $_SESSION['old_input']['mapel_jp'] ?? [],
        'mapel_keterangan' => $_SESSION['old_input']['mapel_keterangan'] ?? [],
        '__has_explicit_guru' => true,
    ];
}
?>

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grup['id']) ?>" class="p-2 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl text-slate-600 transition-colors shadow-xs shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Tambah Anggota Penugasan</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Grup: <strong class="text-primary-700"><?= e($grup['nama_grup']) ?></strong> (Semester <?= e($grup['semester']) ?>)
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            <span id="badge-total-anggota" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-primary-50 text-primary-700 border border-primary-200/80 shadow-xs">
                1 Pegawai
            </span>
        </div>
    </div>

    <!-- Form Input Card -->
    <div class="bg-white rounded-3xl border border-primary-100/60 shadow-sm p-6 sm:p-8 relative">
        <form id="form-penugasan-bulk"
              action="<?= url('kelola-pegawai/penugasan/grup/' . $grup['id'] . '/store') ?>"
              method="POST"
              enctype="multipart/form-data"
              onsubmit="return validatePenugasanForm(event)">
            <?= CSRF::field() ?>

            <div class="space-y-6">
                <!-- SECTION 1: DAFTAR ANGGOTA PENUGASAN (REPEATER) -->
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                        <div>
                            <h2 class="text-base font-bold text-slate-800">Daftar Anggota Penugasan</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Tambahkan satu atau beberapa pegawai sekaligus untuk Nomor SK yang sama.</p>
                        </div>

                        <button type="button"
                                onclick="addMemberCard()"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary-50 hover:bg-primary-100 text-primary-700 font-bold text-xs rounded-xl border border-primary-200/80 transition-all shadow-xs cursor-pointer shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span>Tambah Pegawai</span>
                        </button>
                    </div>

                    <div id="penugasan-members-container" class="space-y-4">
                        <!-- Dynamic member cards rendered via JS -->
                    </div>

                    <button type="button"
                            onclick="addMemberCard()"
                            class="w-full py-3 px-4 rounded-2xl border-2 border-dashed border-primary-200 hover:border-primary-400 bg-primary-50/30 hover:bg-primary-50/70 text-primary-700 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Tambah Pegawai Lagi (SK yang Sama)</span>
                    </button>
                </div>

                <!-- SECTION 2: DATA SK & PERIODE TUGAS BERSAMA -->
                <div class="rounded-2xl border border-slate-200/90 bg-slate-50/30 p-4 sm:p-5 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200/70">
                        <div>
                            <h2 class="text-sm sm:text-base font-bold text-slate-800">Data SK &amp; Periode Tugas</h2>
                            <p class="text-[11px] text-slate-500 mt-0.5">Informasi surat dan masa berlaku tugas di bawah ini otomatis diterapkan untuk seluruh anggota yang ditambahkan.</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/70 self-start sm:self-auto shrink-0">
                            Berlaku untuk Semua Anggota
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nomor SK -->
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-1.5">Nomor SK Penugasan</label>
                            <input type="text"
                                   name="no_sk"
                                   value="<?= old('no_sk', $grup['no_sk'] ?? '') ?>"
                                   placeholder="Nomor SK individu / ikuti grup"
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                            <p class="text-[11px] text-slate-400 mt-1">Otomatis terisi dari SK grup (dapat disesuaikan jika diperlukan).</p>
                        </div>

                        <!-- Tanggal SK -->
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal SK</label>
                            <input type="date"
                                   name="tanggal_sk"
                                   value="<?= old('tanggal_sk', $grup['tanggal_sk'] ?? date('Y-m-d')) ?>"
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        </div>

                        <!-- TMT Mulai -->
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal Mulai Tugas (TMT) <span class="text-rose-500">*</span></label>
                            <input type="date"
                                   name="tmt_mulai"
                                   value="<?= old('tmt_mulai', $grup['tmt_mulai'] ?? date('Y-m-d')) ?>"
                                   required
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                        </div>

                        <!-- TST Selesai -->
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-1.5">Tanggal Selesai Tugas (TST)</label>
                            <input type="date"
                                   name="tst_selesai"
                                   value="<?= old('tst_selesai', $grup['tst_selesai'] ?? '') ?>"
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                            <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika masa tugas tidak dibatasi.</p>
                        </div>

                        <!-- Status Penugasan -->
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 mb-1.5">Status Penugasan</label>
                            <select name="status" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm">
                                <option value="Aktif" <?= old('status', 'Aktif') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="Tidak Aktif" <?= old('status') === 'Tidak Aktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                            </select>
                        </div>

                        <!-- Keterangan -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-primary-900 mb-1.5">Keterangan Tambahan</label>
                            <textarea name="keterangan"
                                      rows="2"
                                      placeholder="Catatan tambahan mengenai penugasan ini..."
                                      class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 focus:border-primary-500 transition-colors text-sm"><?= old('keterangan') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="<?= url('kelola-pegawai/penugasan/grup/' . $grup['id']) ?>" class="px-5 py-2.5 text-center text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                            id="btn-submit-penugasan"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-primary-600/25 transition-all cursor-pointer">
                        Simpan Penugasan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const MASTER_PEGAWAI_DATA = <?= json_encode($pegawaiOptionsJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const MASTER_UNIT_DATA = <?= json_encode($unitTugasOptionsJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const MASTER_JABATAN_DATA = <?= json_encode($jabatanOptionsJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const MASTER_MAPEL_DATA = <?= json_encode($masterMapel ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const MASTER_KELAS_DATA = <?= json_encode($kelasListWithUnit ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const OLD_PENUGASAN_ITEMS = <?= json_encode($oldItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

let memberCardSeq = 0;

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function showPenugasanAlert(message) {
    if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
        Swal.fire({
            icon: 'warning',
            title: 'Periksa Isian Penugasan',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#2563eb'
        });
    } else {
        alert(message);
    }
}

function buildPegawaiSelectOptions(selectedId) {
    let html = '<option value="">-- Pilih Pegawai --</option>';
    const selStr = selectedId !== undefined && selectedId !== null ? String(selectedId) : '';
    MASTER_PEGAWAI_DATA.forEach(p => {
        const displayNama = p.gelar ? `${p.nama}, ${p.gelar}` : p.nama;
        const subtext = p.gelar ? `Gelar: ${p.gelar}` : '';
        const isSel = selStr !== '' && String(p.id) === selStr ? 'selected' : '';
        html += `<option value="${escapeHtml(p.id)}" data-badge="${escapeHtml(p.niy)}" data-image="${escapeHtml(p.foto)}" data-subtext="${escapeHtml(subtext)}" ${isSel}>${escapeHtml(displayNama)}</option>`;
    });
    return html;
}

function buildUnitSelectOptions(selectedId) {
    let html = '<option value="">-- Pilih Unit Tugas --</option>';
    const selStr = selectedId !== undefined && selectedId !== null ? String(selectedId) : '';
    MASTER_UNIT_DATA.forEach(ut => {
        const isSel = selStr !== '' && String(ut.id) === selStr ? 'selected' : '';
        html += `<option value="${escapeHtml(ut.id)}" data-unit-name="${escapeHtml(ut.nama)}" ${isSel}>${escapeHtml(ut.nama)}</option>`;
    });
    return html;
}

function buildJabatanSelectOptions(selectedId) {
    let html = '<option value="">-- Pilih Jabatan --</option>';
    const selStr = selectedId !== undefined && selectedId !== null ? String(selectedId) : '';
    MASTER_JABATAN_DATA.forEach(jb => {
        const isSel = selStr !== '' && String(jb.id) === selStr ? 'selected' : '';
        html += `<option value="${escapeHtml(jb.id)}" data-nama="${escapeHtml(String(jb.nama).toLowerCase())}" ${isSel}>${escapeHtml(jb.nama)}</option>`;
    });
    return html;
}

function buildWaliKelasSelectOptions(selectedKelas) {
    let html = '<option value="">-- Pilih Kelas Siswa --</option>';
    const selStr = selectedKelas ? String(selectedKelas) : '';
    MASTER_KELAS_DATA.forEach(k => {
        const namaKelas = k.nama_kelas || '';
        const unit = k.unit || '';
        const isSel = selStr !== '' && namaKelas === selStr ? 'selected' : '';
        html += `<option value="${escapeHtml(namaKelas)}" data-unit="${escapeHtml(unit)}" ${isSel}>${escapeHtml(namaKelas)} (Unit ${escapeHtml(unit)})</option>`;
    });
    return html;
}

function buildMapelSelectOptions(selectedVal) {
    let html = '<option value="">-- Pilih Mapel --</option>';
    const selStr = selectedVal ? String(selectedVal) : '';
    MASTER_MAPEL_DATA.forEach(item => {
        const val = typeof item === 'object' ? (item.nama_mapel || '') : String(item || '');
        const badge = typeof item === 'object' ? (item.kode_mapel || '') : '';
        const unit = typeof item === 'object' ? (item.unit || '') : '';
        const subtext = typeof item === 'object' ? (item.kelompok || '') : '';
        const isSel = selStr !== '' && selStr === val ? 'selected' : '';
        html += `<option value="${escapeHtml(val)}" data-badge="${escapeHtml(badge)}" data-unit="${escapeHtml(unit)}" data-subtext="${escapeHtml(subtext)}" ${isSel}>${escapeHtml(val)}</option>`;
    });
    return html;
}

function buildKelasSelectOptions(selectedVals) {
    let html = '';
    let selectedArr = [];
    if (Array.isArray(selectedVals)) {
        selectedArr = selectedVals.map(String);
    } else if (selectedVals && typeof selectedVals === 'object') {
        selectedArr = Object.values(selectedVals).map(String);
    } else if (selectedVals) {
        selectedArr = String(selectedVals).split(',').map(s => s.trim()).filter(Boolean);
    }
    MASTER_KELAS_DATA.forEach(item => {
        const val = item.nama_kelas || '';
        const unit = item.unit || '';
        const isSel = selectedArr.includes(String(val)) ? 'selected' : '';
        html += `<option value="${escapeHtml(val)}" data-unit="${escapeHtml(unit)}" ${isSel}>${escapeHtml(val)}</option>`;
    });
    return html;
}

function addMemberCard(data = {}) {
    const container = document.getElementById('penugasan-members-container');
    if (!container) return null;

    const mIdx = memberCardSeq++;
    const card = document.createElement('div');
    card.className = 'penugasan-member-card repeater-row rounded-2xl border border-slate-200/90 bg-slate-50/40 p-4 sm:p-5 space-y-4 relative transition-colors';
    card.dataset.memberIdx = String(mIdx);
    card.dataset.selectedUnit = '';
    card.dataset.mapelRowSeq = '0';

    const pegawaiVal = data.pegawai_id || '';
    const unitVal = data.unit_tugas_id || '';
    const jabatanVal = data.jabatan_id || '';
    const waliVal = data.wali_kelas_nama || '';

    card.innerHTML = `
        <!-- Card Header -->
        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200/70">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="member-number-badge inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-primary-600 text-white font-extrabold text-xs shrink-0">#1</span>
                <span class="member-title-preview font-bold text-slate-800 text-sm truncate">Anggota Penugasan</span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button"
                        onclick="duplicateMemberCard(this)"
                        title="Tambah baris baru dengan Unit &amp; Jabatan yang sama"
                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white hover:bg-primary-50 text-slate-600 hover:text-primary-700 border border-slate-200 hover:border-primary-200 text-[11px] font-bold transition-colors cursor-pointer">
                    <span>Duplikat Unit &amp; Jabatan</span>
                </button>
                <button type="button"
                        onclick="removeMemberCard(this)"
                        class="btn-remove-member hidden items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200/80 text-[11px] font-bold transition-colors cursor-pointer">
                    <span>Hapus</span>
                </button>
            </div>
        </div>

        <!-- 3-Column Primary Grid: Pegawai, Unit Tugas, Jabatan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="relative">
                <label class="block text-xs font-semibold text-primary-900 mb-1.5">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <select name="items[${mIdx}][pegawai_id]"
                        data-field="pegawai_id"
                        class="searchable-select w-full"
                        data-placeholder="-- Pilih Pegawai --"
                        data-search-placeholder="Cari nama pegawai, NIY..."
                        onchange="onMemberPegawaiChanged(this)">
                    ${buildPegawaiSelectOptions(pegawaiVal)}
                </select>
            </div>

            <div class="relative">
                <label class="block text-xs font-semibold text-primary-900 mb-1.5">Unit Tugas <span class="text-rose-500">*</span></label>
                <select name="items[${mIdx}][unit_tugas_id]"
                        data-field="unit_tugas_id"
                        class="searchable-select w-full"
                        data-placeholder="-- Pilih Unit Tugas --"
                        data-search-placeholder="Cari unit tugas..."
                        onchange="onMemberUnitChanged(this)">
                    ${buildUnitSelectOptions(unitVal)}
                </select>
            </div>

            <div class="relative">
                <label class="block text-xs font-semibold text-primary-900 mb-1.5">Jabatan / Posisi <span class="text-rose-500">*</span></label>
                <select name="items[${mIdx}][jabatan_id]"
                        data-field="jabatan_id"
                        class="searchable-select w-full"
                        data-placeholder="-- Pilih Jabatan --"
                        data-search-placeholder="Cari jabatan..."
                        onchange="onMemberJabatanChanged(this)">
                    ${buildJabatanSelectOptions(jabatanVal)}
                </select>
            </div>
        </div>

        <!-- Section Khusus Wali Kelas (Per Card) -->
        <div class="member-wali-section border border-amber-200 bg-amber-50/50 rounded-2xl p-4 shadow-xs transition-all relative" style="display: none;">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs shadow-xs shrink-0">🏫</div>
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800">Penugasan Wali Kelas</h3>
                    <p class="text-[11px] text-slate-500">Pilih kelas yang diampu sebagai Wali Kelas (tersinkron otomatis dengan Unit Tugas).</p>
                </div>
            </div>
            <div class="relative">
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Kelas / Rombel yang Diampu <span class="text-rose-500">*</span></label>
                <select name="items[${mIdx}][wali_kelas_nama]"
                        data-field="wali_kelas_nama"
                        class="searchable-select w-full"
                        data-placeholder="-- Pilih Kelas Siswa --"
                        data-search-placeholder="Cari kelas (misal: Kelas 7A, 1B)...">
                    ${buildWaliKelasSelectOptions(waliVal)}
                </select>
            </div>
        </div>

        <!-- Section Khusus Tugas Mengajar Guru (Per Card) -->
        <div class="member-guru-box border border-indigo-100 bg-gradient-to-br from-indigo-50/40 via-white to-blue-50/30 rounded-2xl p-4 shadow-xs transition-all relative">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-indigo-100/70 mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">📚</div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800">Tugas Mengajar &amp; Mata Pelajaran (Khusus Guru / Pendidik)</h3>
                        <p class="text-[11px] text-slate-500">Tentukan mata pelajaran, kelas/rombel yang diampu (bisa pilih banyak), dan JP.</p>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer select-none shrink-0">
                    <input type="checkbox"
                           name="items[${mIdx}][is_guru]"
                           value="1"
                           class="member-toggle-is-guru sr-only peer"
                           onchange="toggleMemberGuruSection(this, this.checked)">
                    <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ml-2 text-xs font-bold text-slate-700">Tugas Mengajar</span>
                </label>
            </div>

            <div class="member-mapel-section space-y-3" style="display: none;">
                <div class="hidden sm:grid sm:grid-cols-12 gap-2.5 px-3 py-2 bg-indigo-100/70 rounded-xl text-indigo-950 font-bold text-xs">
                    <div class="col-span-4">Mata Pelajaran <span class="text-rose-500">*</span></div>
                    <div class="col-span-4">Kelas / Rombel (Bisa Pilih Banyak) <span class="text-rose-500">*</span></div>
                    <div class="col-span-2 text-center">JP / Kelas <span class="text-rose-500">*</span></div>
                    <div class="col-span-2">Keterangan / Aksi</div>
                </div>

                <div class="member-mapel-rows space-y-3"></div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-indigo-100/70">
                    <button type="button"
                            onclick="addMemberMapelRow(this.closest('.penugasan-member-card'))"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200/80 transition-all shadow-xs cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span>Tambah Mata Pelajaran &amp; Kelas</span>
                    </button>

                    <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-indigo-200 text-xs font-bold text-slate-700 shadow-xs">
                        <span>Total Beban Mengajar:</span>
                        <span class="member-total-jp px-2.5 py-0.5 rounded-lg bg-indigo-600 text-white font-extrabold text-xs">0 JP / Minggu</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.appendChild(card);

    if (window.SearchableSelect && typeof window.SearchableSelect.init === 'function') {
        window.SearchableSelect.init(card);
    }

    const pegawaiSel = card.querySelector('select[data-field="pegawai_id"]');
    const unitSel = card.querySelector('select[data-field="unit_tugas_id"]');
    const jabatanSel = card.querySelector('select[data-field="jabatan_id"]');

    if (pegawaiVal && pegawaiSel) onMemberPegawaiChanged(pegawaiSel);
    if (unitVal && unitSel) onMemberUnitChanged(unitSel);
    if (jabatanVal && jabatanSel) onMemberJabatanChanged(jabatanSel);

    // Pulihkan state guru & detail mapel bila berasal dari old_input
    if (data.__has_explicit_guru) {
        const toggle = card.querySelector('.member-toggle-is-guru');
        const mapelContainer = card.querySelector('.member-mapel-rows');
        const isGuru = !!Number(data.is_guru);
        if (toggle) {
            toggle.checked = isGuru;
            toggleMemberGuruSection(toggle, isGuru);
        }
        if (isGuru && data.mapel_nama && typeof data.mapel_nama === 'object' && mapelContainer) {
            const keys = Object.keys(data.mapel_nama);
            if (keys.length > 0) {
                mapelContainer.innerHTML = '';
                card.dataset.mapelRowSeq = '0';
                keys.forEach(k => {
                    addMemberMapelRow(card, {
                        mata_pelajaran: data.mapel_nama[k] || '',
                        nama_kelas: (data.mapel_kelas && data.mapel_kelas[k]) || [],
                        jumlah_jp: (data.mapel_jp && data.mapel_jp[k]) || 2,
                        keterangan: (data.mapel_keterangan && data.mapel_keterangan[k]) || ''
                    });
                });
            }
        }
    }

    updateMemberSummaryUI();
    return card;
}

function duplicateMemberCard(btn) {
    const sourceCard = btn.closest('.penugasan-member-card');
    if (!sourceCard) return;

    const unitSel = sourceCard.querySelector('select[data-field="unit_tugas_id"]');
    const jabatanSel = sourceCard.querySelector('select[data-field="jabatan_id"]');

    const newCard = addMemberCard({
        pegawai_id: '',
        unit_tugas_id: unitSel ? unitSel.value : '',
        jabatan_id: jabatanSel ? jabatanSel.value : ''
    });

    if (newCard && typeof newCard.scrollIntoView === 'function') {
        newCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function removeMemberCard(btn) {
    const container = document.getElementById('penugasan-members-container');
    if (!container) return;
    const cards = container.querySelectorAll('.penugasan-member-card');
    if (cards.length <= 1) return;

    const card = btn.closest('.penugasan-member-card');
    if (card) {
        card.remove();
        updateMemberSummaryUI();
    }
}

function updateMemberSummaryUI() {
    const container = document.getElementById('penugasan-members-container');
    if (!container) return;
    const cards = container.querySelectorAll('.penugasan-member-card');
    const count = cards.length;

    cards.forEach((card, idx) => {
        const badge = card.querySelector('.member-number-badge');
        if (badge) badge.textContent = '#' + (idx + 1);

        const removeBtn = card.querySelector('.btn-remove-member');
        if (removeBtn) {
            if (count <= 1) {
                removeBtn.classList.add('hidden');
                removeBtn.classList.remove('inline-flex');
            } else {
                removeBtn.classList.remove('hidden');
                removeBtn.classList.add('inline-flex');
            }
        }
    });

    const totalBadge = document.getElementById('badge-total-anggota');
    if (totalBadge) {
        totalBadge.textContent = `${count} Pegawai`;
    }

    const submitBtn = document.getElementById('btn-submit-penugasan');
    if (submitBtn) {
        submitBtn.textContent = count > 1
            ? `Simpan ${count} Penugasan Sekaligus`
            : 'Simpan Penugasan';
    }
}

function onMemberPegawaiChanged(selectEl) {
    const card = selectEl.closest('.penugasan-member-card');
    if (!card) return;
    const titleEl = card.querySelector('.member-title-preview');
    if (!titleEl) return;

    const opt = selectEl.options[selectEl.selectedIndex];
    titleEl.textContent = (opt && opt.value) ? opt.textContent.trim() : 'Anggota Penugasan';
}

function onMemberUnitChanged(selectEl) {
    const card = selectEl.closest('.penugasan-member-card');
    if (!card) return;

    const opt = selectEl.options[selectEl.selectedIndex];
    const unitName = (opt && opt.value)
        ? ((opt.getAttribute('data-unit-name') || opt.textContent || '').trim())
        : '';

    card.dataset.selectedUnit = unitName;
    filterMemberDropdownsByUnit(card, unitName);
}

function filterMemberDropdownsByUnit(card, unitName) {
    if (!card || !window.SearchableSelect || typeof window.SearchableSelect.filterByUnit !== 'function') return;

    const waliSel = card.querySelector('select[data-field="wali_kelas_nama"]');
    if (waliSel) window.SearchableSelect.filterByUnit(waliSel, unitName);

    const mapelSelects = card.querySelectorAll('.member-mapel-rows select[data-mapel-field="mapel_nama"]');
    mapelSelects.forEach(sel => window.SearchableSelect.filterByUnit(sel, unitName));

    const kelasSelects = card.querySelectorAll('.member-mapel-rows select[data-mapel-field="mapel_kelas"]');
    kelasSelects.forEach(sel => window.SearchableSelect.filterByUnit(sel, unitName));
}

function onMemberJabatanChanged(selectEl) {
    const card = selectEl.closest('.penugasan-member-card');
    if (!card) return;

    const opt = selectEl.options[selectEl.selectedIndex];
    const namaJabatan = (opt && opt.value)
        ? ((opt.getAttribute('data-nama') || opt.textContent || '').toLowerCase())
        : '';

    const secWali = card.querySelector('.member-wali-section');
    const selectWali = card.querySelector('select[data-field="wali_kelas_nama"]');
    if (namaJabatan.includes('wali kelas')) {
        if (secWali) secWali.style.display = 'block';
    } else {
        if (secWali) secWali.style.display = 'none';
        if (selectWali) {
            selectWali.value = '';
            if (window.SearchableSelect && typeof window.SearchableSelect.refresh === 'function') {
                window.SearchableSelect.refresh(selectWali);
            }
        }
    }

    const isTeachingRole = ['guru', 'pendidik', 'pengajar', 'ustadz', 'ustadzah', 'wali kelas']
        .some(term => namaJabatan.includes(term));
    if (isTeachingRole) {
        const toggle = card.querySelector('.member-toggle-is-guru');
        if (toggle) {
            toggle.checked = true;
            toggleMemberGuruSection(toggle, true);
        }
    }
}

function toggleMemberGuruSection(elOrToggle, show) {
    const card = elOrToggle.closest('.penugasan-member-card');
    if (!card) return;

    const sec = card.querySelector('.member-mapel-section');
    const toggle = card.querySelector('.member-toggle-is-guru');
    if (toggle) toggle.checked = !!show;

    if (show) {
        if (sec) sec.style.display = 'block';
        const container = card.querySelector('.member-mapel-rows');
        if (container && container.children.length === 0) {
            addMemberMapelRow(card);
        }
    } else {
        if (sec) sec.style.display = 'none';
    }
    calculateMemberTotalJp(card);
}

function addMemberMapelRow(card, rowData = {}) {
    if (!card) return;
    const container = card.querySelector('.member-mapel-rows');
    if (!container) return;

    const mIdx = card.dataset.memberIdx;
    const rIdx = parseInt(card.dataset.mapelRowSeq || '0', 10);
    card.dataset.mapelRowSeq = String(rIdx + 1);

    const row = document.createElement('div');
    row.className = 'repeater-row grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-center bg-white p-3 sm:p-0 rounded-2xl sm:rounded-none border sm:border-0 border-slate-200/80 shadow-xs sm:shadow-none relative';

    const mapelVal = rowData.mata_pelajaran || '';
    const kelasVal = rowData.nama_kelas || [];
    const jpVal = rowData.jumlah_jp || 2;
    const ketVal = rowData.keterangan || '';

    row.innerHTML = `
        <div class="sm:col-span-4 relative">
            <label class="sm:hidden block text-[11px] font-bold text-slate-600 mb-1">Mata Pelajaran <span class="text-rose-500">*</span></label>
            <select name="items[${mIdx}][mapel_nama][${rIdx}]"
                    data-mapel-field="mapel_nama"
                    class="searchable-select w-full"
                    data-placeholder="-- Pilih Mapel --"
                    data-search-placeholder="Cari mata pelajaran..."
                    data-size="sm">
                ${buildMapelSelectOptions(mapelVal)}
            </select>
        </div>
        <div class="sm:col-span-4 relative">
            <label class="sm:hidden block text-[11px] font-bold text-slate-600 mb-1">Kelas / Rombel <span class="text-rose-500">*</span></label>
            <select name="items[${mIdx}][mapel_kelas][${rIdx}][]"
                    data-mapel-field="mapel_kelas"
                    multiple
                    class="searchable-select w-full"
                    data-placeholder="-- Pilih Kelas (Bisa Lebih dari 1) --"
                    data-search-placeholder="Cari &amp; centang kelas..."
                    data-size="sm"
                    data-multiple="true"
                    onchange="calculateMemberTotalJp(this)">
                ${buildKelasSelectOptions(kelasVal)}
            </select>
        </div>
        <div class="sm:col-span-2 relative">
            <label class="sm:hidden block text-[11px] font-bold text-slate-600 mb-1">JP / Kelas <span class="text-rose-500">*</span></label>
            <input type="number"
                   name="items[${mIdx}][mapel_jp][${rIdx}]"
                   data-mapel-field="mapel_jp"
                   min="1"
                   max="40"
                   value="${escapeHtml(jpVal)}"
                   required
                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-800 text-xs font-bold text-center focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-xs"
                   oninput="calculateMemberTotalJp(this)">
        </div>
        <div class="sm:col-span-2 flex items-center gap-1.5 relative">
            <input type="text"
                   name="items[${mIdx}][mapel_keterangan][${rIdx}]"
                   value="${escapeHtml(ketVal)}"
                   placeholder="Ket. (opsional)"
                   class="flex-1 min-w-0 px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-700 text-xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all shadow-xs">
            <button type="button"
                    onclick="removeMemberMapelRow(this)"
                    class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-colors shrink-0 cursor-pointer"
                    title="Hapus Baris">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
            </button>
        </div>
    `;

    container.appendChild(row);

    if (window.SearchableSelect && typeof window.SearchableSelect.init === 'function') {
        window.SearchableSelect.init(row);
        const unitName = card.dataset.selectedUnit || '';
        if (unitName) {
            row.querySelectorAll('select.searchable-select').forEach(sel => {
                window.SearchableSelect.filterByUnit(sel, unitName);
            });
        }
    }

    calculateMemberTotalJp(card);
}

function removeMemberMapelRow(btn) {
    const row = btn.closest('.repeater-row');
    const card = btn.closest('.penugasan-member-card');
    if (!row || !card) return;
    const container = card.querySelector('.member-mapel-rows');
    if (!container) return;

    if (container.children.length > 1) {
        row.remove();
    } else {
        row.querySelectorAll('select').forEach(s => {
            if (s.multiple) {
                Array.from(s.options).forEach(o => { o.selected = false; });
            } else {
                s.value = '';
            }
            if (window.SearchableSelect && typeof window.SearchableSelect.refresh === 'function') {
                window.SearchableSelect.refresh(s);
            }
        });
        const jp = row.querySelector('input[data-mapel-field="mapel_jp"]');
        if (jp) jp.value = 2;
        const ket = row.querySelector('input[type="text"]');
        if (ket) ket.value = '';
    }
    calculateMemberTotalJp(card);
}

function calculateMemberTotalJp(el) {
    const card = el && el.classList && el.classList.contains('penugasan-member-card')
        ? el
        : (el && el.closest ? el.closest('.penugasan-member-card') : null);
    if (!card) return;

    const toggle = card.querySelector('.member-toggle-is-guru');
    const badge = card.querySelector('.member-total-jp');
    if (!badge) return;

    if (!toggle || !toggle.checked) {
        badge.textContent = '0 JP / Minggu';
        return;
    }

    const rows = card.querySelectorAll('.member-mapel-rows .repeater-row');
    let total = 0;
    rows.forEach(row => {
        const jpInp = row.querySelector('input[data-mapel-field="mapel_jp"]');
        const kelasSelect = row.querySelector('select[data-mapel-field="mapel_kelas"]');
        const jpVal = parseInt(jpInp ? jpInp.value : 0, 10) || 0;

        let classCount = 1;
        if (kelasSelect) {
            const selectedClasses = Array.from(kelasSelect.options).filter(o => o.selected && o.value !== '');
            classCount = selectedClasses.length > 0 ? selectedClasses.length : 1;
        }
        total += (jpVal * classCount);
    });
    badge.textContent = total + ' JP / Minggu';
}

function highlightInvalidControl(controlEl) {
    if (!controlEl) return;
    const wrapper = controlEl.nextElementSibling;
    const targetEl = (wrapper && wrapper.classList && wrapper.classList.contains('searchable-select-wrapper'))
        ? (wrapper.querySelector('.searchable-select-trigger') || wrapper)
        : controlEl;

    if (typeof targetEl.scrollIntoView === 'function') {
        targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    if (typeof targetEl.focus === 'function') {
        try { targetEl.focus({ preventScroll: true }); } catch (err) { targetEl.focus(); }
    }
    targetEl.classList.add('border-rose-400', 'ring-2', 'ring-rose-500/20');
    setTimeout(() => {
        targetEl.classList.remove('border-rose-400', 'ring-2', 'ring-rose-500/20');
    }, 2500);
}

function validatePenugasanForm(event) {
    const container = document.getElementById('penugasan-members-container');
    const cards = container ? container.querySelectorAll('.penugasan-member-card') : [];
    if (!cards.length) {
        if (event) event.preventDefault();
        showPenugasanAlert('Minimal 1 anggota penugasan harus diisi.');
        return false;
    }

    const seenTriples = new Set();

    for (let i = 0; i < cards.length; i++) {
        const card = cards[i];
        const noLabel = `Anggota #${i + 1}`;
        const pegawaiSel = card.querySelector('select[data-field="pegawai_id"]');
        const unitSel = card.querySelector('select[data-field="unit_tugas_id"]');
        const jabatanSel = card.querySelector('select[data-field="jabatan_id"]');

        if (!pegawaiSel || !pegawaiSel.value) {
            if (event) event.preventDefault();
            highlightInvalidControl(pegawaiSel);
            showPenugasanAlert(`${noLabel}: Silakan pilih Pegawai terlebih dahulu.`);
            return false;
        }
        if (!unitSel || !unitSel.value) {
            if (event) event.preventDefault();
            highlightInvalidControl(unitSel);
            showPenugasanAlert(`${noLabel}: Silakan pilih Unit Tugas terlebih dahulu.`);
            return false;
        }
        if (!jabatanSel || !jabatanSel.value) {
            if (event) event.preventDefault();
            highlightInvalidControl(jabatanSel);
            showPenugasanAlert(`${noLabel}: Silakan pilih Jabatan / Posisi terlebih dahulu.`);
            return false;
        }

        const tripleKey = `${pegawaiSel.value}|${unitSel.value}|${jabatanSel.value}`;
        if (seenTriples.has(tripleKey)) {
            if (event) event.preventDefault();
            highlightInvalidControl(pegawaiSel);
            showPenugasanAlert(`${noLabel}: Kombinasi Pegawai, Unit Tugas, dan Jabatan yang sama sudah ada pada daftar.`);
            return false;
        }
        seenTriples.add(tripleKey);

        const secWali = card.querySelector('.member-wali-section');
        if (secWali && secWali.style.display !== 'none') {
            const waliSel = card.querySelector('select[data-field="wali_kelas_nama"]');
            if (!waliSel || !waliSel.value) {
                if (event) event.preventDefault();
                highlightInvalidControl(waliSel);
                showPenugasanAlert(`${noLabel}: Silakan pilih Kelas yang diampu sebagai Wali Kelas.`);
                return false;
            }
        }

        const toggleGuru = card.querySelector('.member-toggle-is-guru');
        if (toggleGuru && toggleGuru.checked) {
            const mapelRows = card.querySelectorAll('.member-mapel-rows .repeater-row');
            for (let r = 0; r < mapelRows.length; r++) {
                const mRow = mapelRows[r];
                const mapelSel = mRow.querySelector('select[data-mapel-field="mapel_nama"]');
                const kelasSel = mRow.querySelector('select[data-mapel-field="mapel_kelas"]');
                const selectedClasses = kelasSel ? Array.from(kelasSel.options).filter(o => o.selected && o.value !== '') : [];

                if (!mapelSel || !mapelSel.value) {
                    if (event) event.preventDefault();
                    highlightInvalidControl(mapelSel);
                    showPenugasanAlert(`${noLabel}: Mata Pelajaran pada baris #${r + 1} belum dipilih.`);
                    return false;
                }
                if (selectedClasses.length === 0) {
                    if (event) event.preventDefault();
                    highlightInvalidControl(kelasSel);
                    showPenugasanAlert(`${noLabel}: Kelas / Rombel pada baris mata pelajaran #${r + 1} wajib dipilih minimal 1.`);
                    return false;
                }
            }
        }
    }

    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('penugasan-members-container');
    if (!container || container.children.length > 0) return;

    memberCardSeq = 0;
    if (Array.isArray(OLD_PENUGASAN_ITEMS) && OLD_PENUGASAN_ITEMS.length > 0) {
        OLD_PENUGASAN_ITEMS.forEach(item => addMemberCard(item));
    } else {
        addMemberCard();
    }
});
</script>
