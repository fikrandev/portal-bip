<?php
/**
 * HES - Edit View with Unit Isolation & Kaldik Acuan per Unit
 */
$bulanRows = $konten['bulan_rows'] ?? [];
$totalHK = $konten['total_hk'] ?? 0;
$totalHL = $konten['total_hl'] ?? 0;
$totalHE = $konten['total_he'] ?? 0;
$selectedUnit = old('unit', $item['unit'] ?? 'SD');
$selectedKaldikId = old('kaldik_id', $item['kaldik_id'] ?? '');
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Edit Hari Efektif Sekolah (HES)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Perbarui rincian perhitungan hari efektif dan hari libur per unit</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/hes') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
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

    <form method="POST" action="<?= url("kelola-perangkat-pembelajaran/hes/update/{$item['id']}") ?>" enctype="multipart/form-data" class="space-y-6">
        <?= CSRF::field() ?>

        <!-- Identitas Utama & Unit Selector -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span> Identitas & Unit HES
            </h2>

            <!-- Searchable Live Search Guru Picker (At Atas) -->
            <?php
            $picker_label = 'Penyusun / Penanggung Jawab HES';
            $picker_accent = 'teal';
            $selected_guru_id = old('guru_id', $item['guru_id'] ?? null);
            $selected_guru_nama = old('guru_nama', $item['guru_nama'] ?? null);
            $selected_guru_nip = old('guru_nip', $item['guru_nip'] ?? null);
            include BASE_PATH . '/modules/kelola-perangkat-pembelajaran/views/partials/guru_picker.php';
            ?>

            <!-- Visual Unit Selector -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-slate-700 mb-2">Pilih Unit Satuan Pendidikan <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <?php foreach ($unit_list as $uKey => $uInfo): 
                        $isChecked = ($selectedUnit === $uKey);
                    ?>
                        <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all hover:border-teal-400 hover:bg-slate-50/80 unit-card <?= $isChecked ? 'border-teal-600 bg-teal-50/40 ring-2 ring-teal-500/20 shadow-sm' : 'border-slate-200 bg-white' ?>">
                            <input type="radio" name="unit" value="<?= $uKey ?>" <?= $isChecked ? 'checked' : '' ?> class="sr-only unit-radio" onchange="onUnitChanged(this.value, this)">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-2xl mb-1.5 <?= $uInfo['bg_soft'] ?>">
                                <?= $uInfo['icon'] ?>
                            </div>
                            <span class="text-xs font-bold text-slate-800">Unit <?= $uKey ?></span>
                            <span class="text-[10px] text-slate-500 text-center leading-tight mt-0.5"><?= e($uInfo['name']) ?></span>
                            <div class="unit-check-indicator absolute top-2 right-2 <?= $isChecked ? 'block text-teal-600' : 'hidden' ?>">
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Kaldik Acuan Unit (Isolasi Kaldik Berdasarkan Unit Terpilih) -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <span>📅</span> Acuan Kalender Pendidikan Unit
                        <span id="unit-kaldik-label" class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-teal-100 text-teal-800">Unit <?= e($selectedUnit) ?></span>
                    </label>
                    <span class="text-[10px] text-slate-400">Hanya menampilkan Kaldik dari Unit yang dipilih</span>
                </div>

                <select name="kaldik_id" id="kaldik-select" onchange="onKaldikSelected(this)" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-white">
                    <option value="">-- Pilih Kaldik Acuan (Opsional) --</option>
                </select>

                <div id="kaldik-info-box" class="hidden p-3 rounded-xl bg-teal-50/80 border border-teal-200 text-xs text-teal-900 flex items-center justify-between">
                    <div>
                        <span class="font-bold" id="kaldik-info-title">Judul Kaldik</span>
                        <p class="text-[11px] text-teal-700" id="kaldik-info-sub">Penyusun & Semester</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-200 text-teal-900" id="kaldik-info-status">Disetujui</span>
                </div>

                <div id="kaldik-empty-alert" class="hidden p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                    <span>⚠️ Belum ada Kalender Pendidikan untuk unit ini. Anda dapat membuat HES secara mandiri atau <a href="<?= url('kelola-perangkat-pembelajaran/kaldik/create') ?>" target="_blank" class="font-bold underline text-amber-900">buat Kaldik terlebih dahulu</a>.</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Dokumen <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" required value="<?= e($item['judul']) ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" id="ta-select" onchange="filterKaldikOptions()" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($item['tahun_akademik_id'] == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" id="semester-select" onchange="filterKaldikOptions()" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                        <option value="Ganjil" <?= $item['semester'] === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil</option>
                        <option value="Genap" <?= $item['semester'] === 'Genap' ? 'selected' : '' ?>>Semester Genap</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Dasar Kebijakan</label>
                <textarea name="deskripsi" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50"><?= e($konten['deskripsi'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Tabel Perhitungan HES Bulanan Interaktif -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span> Tabel Rincian Hari Efektif Sekolah
                </h2>
                <span class="text-xs text-slate-400 font-medium">* Perhitungan hari efektif = Hari Kalender - Hari Libur</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="tabel-hes">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <th class="py-3 px-3 w-10 text-center">No</th>
                            <th class="py-3 px-4 w-40 font-bold">Bulan</th>
                            <th class="py-3 px-4 w-32">Hari Kalender (HK)</th>
                            <th class="py-3 px-4 w-32">Hari Libur (HL)</th>
                            <th class="py-3 px-4 w-36 font-bold text-teal-800">Hari Efektif (HE)</th>
                            <th class="py-3 px-4">Keterangan Hari Libur / Kegiatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium" id="hes-body">
                        <?php foreach ($bulanRows as $i => $b): ?>
                            <tr class="hes-row">
                                <td class="py-2.5 px-3 text-center text-slate-400"><?= $i + 1 ?></td>
                                <td class="py-2.5 px-4 font-bold text-slate-800">
                                    <input type="text" name="bulan_nama[]" value="<?= e($b['bulan']) ?>" readonly class="bg-transparent font-bold text-slate-800 text-xs w-full focus:outline-none">
                                </td>
                                <td class="py-2.5 px-4">
                                    <input type="number" name="bulan_hk[]" value="<?= (int)($b['hari_kalender'] ?? 0) ?>" min="0" max="31" oninput="hitungHES()" class="hk-input w-24 px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 text-center font-bold focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </td>
                                <td class="py-2.5 px-4">
                                    <input type="number" name="bulan_hl[]" value="<?= (int)($b['hari_libur'] ?? 0) ?>" min="0" max="31" oninput="hitungHES()" class="hl-input w-24 px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 text-center font-bold focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </td>
                                <td class="py-2.5 px-4 font-bold text-teal-700">
                                    <input type="number" name="bulan_he[]" value="<?= (int)($b['hari_efektif'] ?? 0) ?>" readonly class="he-input w-24 px-3 py-1.5 rounded-xl border border-teal-200 text-xs bg-teal-50 text-center font-extrabold text-teal-800 focus:outline-none">
                                </td>
                                <td class="py-2.5 px-4">
                                    <input type="text" name="bulan_ket[]" value="<?= e($b['keterangan'] ?? '') ?>" placeholder="Rincian libur/kegiatan..." class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-teal-50/70 font-extrabold text-teal-950 border-t-2 border-teal-600">
                            <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider text-xs">Total Semester:</td>
                            <td class="py-3 px-4 text-center text-sm" id="sum-hk"><?= $totalHK ?></td>
                            <td class="py-3 px-4 text-center text-sm text-rose-700" id="sum-hl"><?= $totalHL ?></td>
                            <td class="py-3 px-4 text-center text-base text-teal-800" id="sum-he"><?= $totalHE ?></td>
                            <td class="py-3 px-4 text-xs font-semibold text-teal-700">Hari Kerja Efektif</td>
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
                <?php if (!empty($item['file_lampiran'])): ?>
                    <div class="mb-3 p-3 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs text-teal-800">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                            <span>Berkas terlampir saat ini: <strong><?= basename($item['file_lampiran']) ?></strong></span>
                        </div>
                        <a href="<?= url($item['file_lampiran']) ?>" target="_blank" class="text-xs font-bold text-teal-700 hover:underline">Unduh Berkas</a>
                    </div>
                <?php endif; ?>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Ganti Berkas / SK HES (PDF / Word / Excel / JPG)</label>
                <input type="file" name="file_lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
                <p class="text-[11px] text-slate-400 mt-1">Kosongkan bila tidak ingin mengubah file lampiran.</p>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" name="draft" value="1" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Simpan Perubahan
            </button>
            <?php if ($item['status'] !== 'disetujui'): ?>
                <button type="submit" name="ajukan" value="1" class="px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-lg shadow-teal-500/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                    Simpan & Ajukan Persetujuan
                </button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
const allKaldikData = <?= json_encode($all_kaldiks ?? []) ?>;
let currentUnit = '<?= e($selectedUnit) ?>';
let initialKaldikId = '<?= e($selectedKaldikId) ?>';

function onUnitChanged(unit, radio) {
    currentUnit = unit;

    document.querySelectorAll('.unit-card').forEach(card => {
        card.classList.remove('border-teal-600', 'bg-teal-50/40', 'ring-2', 'ring-teal-500/20', 'shadow-sm');
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
        selectedCard.classList.add('border-teal-600', 'bg-teal-50/40', 'ring-2', 'ring-teal-500/20', 'shadow-sm');
        const indicator = selectedCard.querySelector('.unit-check-indicator');
        if (indicator) {
            indicator.classList.remove('hidden');
            indicator.classList.add('block');
        }
    }

    document.getElementById('unit-kaldik-label').innerText = 'Unit ' + unit;
    filterKaldikOptions();
}

function filterKaldikOptions() {
    const select = document.getElementById('kaldik-select');
    const emptyAlert = document.getElementById('kaldik-empty-alert');
    const infoBox = document.getElementById('kaldik-info-box');

    // Filter kaldiks only for current unit
    const filtered = allKaldikData.filter(k => (k.unit || 'SD') === currentUnit);

    select.innerHTML = '<option value="">-- Pilih Kaldik Acuan Unit ' + currentUnit + ' (Opsional) --</option>';

    if (filtered.length === 0) {
        emptyAlert.classList.remove('hidden');
        infoBox.classList.add('hidden');
    } else {
        emptyAlert.classList.add('hidden');
        filtered.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.id;
            const activeTag = (k.is_active == 1) ? '🟢 [AKTIF] ' : '⚪ [ARSIP] ';
            if (initialKaldikId && String(k.id) === String(initialKaldikId)) {
                opt.selected = true;
            }
            opt.innerText = `${activeTag}[Unit ${k.unit || 'SD'}] ${k.judul} (${k.guru_nama || 'Admin'})`;
            opt.setAttribute('data-title', k.judul);
            opt.setAttribute('data-guru', k.guru_nama || 'Admin');
            opt.setAttribute('data-semester', k.semester || 'Ganjil & Genap');
            opt.setAttribute('data-status', k.status || 'Draft');
            select.appendChild(opt);
        });

        if (select.value) {
            onKaldikSelected(select);
        }
    }
}

function onKaldikSelected(select) {
    const infoBox = document.getElementById('kaldik-info-box');
    const selectedOpt = select.options[select.selectedIndex];
    if (select.value && selectedOpt) {
        infoBox.classList.remove('hidden');
        document.getElementById('kaldik-info-title').innerText = selectedOpt.getAttribute('data-title') || '';
        document.getElementById('kaldik-info-sub').innerText = `Penyusun: ${selectedOpt.getAttribute('data-guru')} • Semester ${selectedOpt.getAttribute('data-semester')}`;
        document.getElementById('kaldik-info-status').innerText = selectedOpt.getAttribute('data-status') || 'Draft';
    } else {
        infoBox.classList.add('hidden');
    }
}

function hitungHES() {
    const rows = document.querySelectorAll('.hes-row');
    let sumHK = 0;
    let sumHL = 0;
    let sumHE = 0;

    rows.forEach(r => {
        const hk = parseInt(r.querySelector('.hk-input').value) || 0;
        const hl = parseInt(r.querySelector('.hl-input').value) || 0;
        const he = Math.max(0, hk - hl);

        r.querySelector('.he-input').value = he;

        sumHK += hk;
        sumHL += hl;
        sumHE += he;
    });

    document.getElementById('sum-hk').innerText = sumHK;
    document.getElementById('sum-hl').innerText = sumHL;
    document.getElementById('sum-he').innerText = sumHE;
}

function updateGuruInfo(select) {
    const opt = select.options[select.selectedIndex];
    document.getElementById('guru_nama').value = opt.getAttribute('data-nama') || '';
    document.getElementById('guru_nip').value = opt.getAttribute('data-nip') || '';
}

document.addEventListener('DOMContentLoaded', () => {
    hitungHES();
    filterKaldikOptions();
});
</script>
