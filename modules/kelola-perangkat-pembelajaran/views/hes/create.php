<?php
/**
 * HES - Create View with Unit Isolation & Kaldik Acuan per Unit
 */
$selectedUnit = old('unit', $_GET['unit'] ?? 'SD');
?>
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Buat Hari Efektif Sekolah (HES)</h1>
            <p class="text-xs sm:text-sm text-slate-500">Hitung hari kerja efektif sekolah dan hari libur per semester berdasarkan Kaldik Unit</p>
        </div>
        <a href="<?= url('kelola-perangkat-pembelajaran/hes') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
            ← Kembali
        </a>
    </div>

    <form method="POST" action="<?= url('kelola-perangkat-pembelajaran/hes/store') ?>" enctype="multipart/form-data" class="space-y-6">
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
                    <!-- Options populated via JS filtered by unit -->
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
                    <input type="text" name="judul" required placeholder="Contoh: Rincian Hari Efektif Sekolah (HES) Semester Ganjil TP 2026/2027" value="<?= old('judul') ?>" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                    <select name="tahun_akademik_id" id="ta-select" onchange="filterKaldikOptions()" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                        <?php foreach ($ta_list as $ta): ?>
                            <option value="<?= $ta['id'] ?>" <?= ($filter_ta == $ta['id']) ? 'selected' : '' ?>><?= e($ta['nama_tahun']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                    <select name="semester" id="semester-select" onchange="onSemesterChanged(this.value)" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50">
                        <option value="Ganjil" <?= $filter_semester === 'Ganjil' ? 'selected' : '' ?>>Semester Ganjil (Juli - Desember)</option>
                        <option value="Genap" <?= $filter_semester === 'Genap' ? 'selected' : '' ?>>Semester Genap (Januari - Juni)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan / Dasar Kebijakan</label>
                <textarea name="deskripsi" rows="2" placeholder="Pedoman kalender pendidikan dinas dan yayasan..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-teal-500 focus:outline-none bg-slate-50/50"></textarea>
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
                        <?php
                        $defaultBulanGanjil = [
                            ['nama' => 'Juli', 'hk' => 31, 'hl' => 17, 'ket' => 'Libur Semester & MPLS'],
                            ['nama' => 'Agustus', 'hk' => 31, 'hl' => 7, 'ket' => 'HUT Kemerdekaan RI'],
                            ['nama' => 'September', 'hk' => 30, 'hl' => 6, 'ket' => 'Sumatif Tengah Semester'],
                            ['nama' => 'Oktober', 'hk' => 31, 'hl' => 5, 'ket' => 'KBM Reguler'],
                            ['nama' => 'November', 'hk' => 30, 'hl' => 5, 'ket' => 'KBM Reguler'],
                            ['nama' => 'Desember', 'hk' => 31, 'hl' => 15, 'ket' => 'SAS & Libur Semester']
                        ];
                        foreach ($defaultBulanGanjil as $i => $b):
                            $he = max(0, $b['hk'] - $b['hl']);
                        ?>
                            <tr class="hes-row">
                                <td class="py-2.5 px-3 text-center text-slate-400"><?= $i + 1 ?></td>
                                <td class="py-2.5 px-4 font-bold text-slate-800">
                                    <input type="text" name="bulan_nama[]" value="<?= $b['nama'] ?>" readonly class="bg-transparent font-bold text-slate-800 text-xs w-full focus:outline-none">
                                </td>
                                <td class="py-2.5 px-4">
                                    <input type="number" name="bulan_hk[]" value="<?= $b['hk'] ?>" min="0" max="31" oninput="hitungHES()" class="hk-input w-24 px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 text-center font-bold focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </td>
                                <td class="py-2.5 px-4">
                                    <input type="number" name="bulan_hl[]" value="<?= $b['hl'] ?>" min="0" max="31" oninput="hitungHES()" class="hl-input w-24 px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 text-center font-bold focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </td>
                                <td class="py-2.5 px-4 font-bold text-teal-700">
                                    <input type="number" name="bulan_he[]" value="<?= $he ?>" readonly class="he-input w-24 px-3 py-1.5 rounded-xl border border-teal-200 text-xs bg-teal-50 text-center font-extrabold text-teal-800 focus:outline-none">
                                </td>
                                <td class="py-2.5 px-4">
                                    <input type="text" name="bulan_ket[]" value="<?= e($b['ket']) ?>" placeholder="Rincian libur/kegiatan..." class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-teal-50/70 font-extrabold text-teal-950 border-t-2 border-teal-600">
                            <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider text-xs">Total Semester:</td>
                            <td class="py-3 px-4 text-center text-sm" id="sum-hk">184</td>
                            <td class="py-3 px-4 text-center text-sm text-rose-700" id="sum-hl">55</td>
                            <td class="py-3 px-4 text-center text-base text-teal-800" id="sum-he">129</td>
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
                <label class="block text-xs font-semibold text-slate-700 mb-1">Unggah Dokumen HES Asli / SK (PDF / Word / Excel / JPG)</label>
                <input type="file" name="file_lampiran" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer">
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4">
            <button type="submit" name="draft" value="1" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Simpan Sebagai Draft
            </button>
            <button type="submit" name="ajukan" value="1" class="px-6 py-2.5 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-lg shadow-teal-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                Simpan & Ajukan Persetujuan
            </button>
        </div>
    </form>
</div>

<!-- Embed All Kaldik Data for Dynamic Filtering per Unit -->
<script>
const allKaldikData = <?= json_encode($all_kaldiks ?? []) ?>;
let currentUnit = '<?= e($selectedUnit) ?>';

function onUnitChanged(unit, radio) {
    currentUnit = unit;

    // Update UI styling for radio cards
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

    let activeOption = null;

    if (filtered.length === 0) {
        emptyAlert.classList.remove('hidden');
        infoBox.classList.add('hidden');
    } else {
        emptyAlert.classList.add('hidden');
        filtered.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.id;
            const activeTag = (k.is_active == 1) ? '🟢 [AKTIF] ' : '⚪ [ARSIP] ';
            opt.innerText = `${activeTag}[Unit ${k.unit || 'SD'}] ${k.judul} (${k.guru_nama || 'Admin'})`;
            opt.setAttribute('data-title', k.judul);
            opt.setAttribute('data-guru', k.guru_nama || 'Admin');
            opt.setAttribute('data-semester', k.semester || 'Ganjil & Genap');
            opt.setAttribute('data-status', k.status || 'Draft');
            opt.setAttribute('data-active', k.is_active || '0');
            
            if (k.is_active == 1 && !activeOption) {
                opt.selected = true;
                activeOption = opt;
            }
            select.appendChild(opt);
        });

        if (activeOption) {
            onKaldikSelected(select);
        }
    }
}

function onKaldikSelected(select) {
    const infoBox = document.getElementById('kaldik-info-box');
    const selectedOpt = select.options[select.selectedIndex];
    if (select.value) {
        infoBox.classList.remove('hidden');
        document.getElementById('kaldik-info-title').innerText = selectedOpt.getAttribute('data-title') || '';
        document.getElementById('kaldik-info-sub').innerText = `Penyusun: ${selectedOpt.getAttribute('data-guru')} • Semester ${selectedOpt.getAttribute('data-semester')}`;
        document.getElementById('kaldik-info-status').innerText = selectedOpt.getAttribute('data-status') || 'Draft';
    } else {
        infoBox.classList.add('hidden');
    }
}

function onSemesterChanged(smt) {
    gantiBulanSemester(smt);
    filterKaldikOptions();
}

const bulanGanjil = [
    { nama: 'Juli', hk: 31, hl: 17, ket: 'Libur Semester & MPLS' },
    { nama: 'Agustus', hk: 31, hl: 7, ket: 'HUT Kemerdekaan RI' },
    { nama: 'September', hk: 30, hl: 6, ket: 'Sumatif Tengah Semester' },
    { nama: 'Oktober', hk: 31, hl: 5, ket: 'KBM Reguler' },
    { nama: 'November', hk: 30, hl: 5, ket: 'KBM Reguler' },
    { nama: 'Desember', hk: 31, hl: 15, ket: 'SAS & Libur Semester' }
];

const bulanGenap = [
    { nama: 'Januari', hk: 31, hl: 6, ket: 'Tahun Baru & Awal Semester' },
    { nama: 'Februari', hk: 28, hl: 5, ket: 'Isra Miraj & Imlek' },
    { nama: 'Maret', hk: 31, hl: 10, ket: 'STS Genap & Awal Puasa' },
    { nama: 'April', hk: 30, hl: 12, ket: 'Hari Raya Idul Fitri' },
    { nama: 'Mei', hk: 31, hl: 7, ket: 'Kenaikan Isa Almasih & Waisak' },
    { nama: 'Juni', hk: 30, hl: 16, ket: 'SAS Genap & Libur Kenaikan Kelas' }
];

function gantiBulanSemester(smt) {
    const list = smt === 'Genap' ? bulanGenap : bulanGanjil;
    const tbody = document.getElementById('hes-body');
    tbody.innerHTML = '';

    list.forEach((b, i) => {
        const he = Math.max(0, b.hk - b.hl);
        const tr = document.createElement('tr');
        tr.className = 'hes-row';
        tr.innerHTML = `
            <td class="py-2.5 px-3 text-center text-slate-400">${i + 1}</td>
            <td class="py-2.5 px-4 font-bold text-slate-800">
                <input type="text" name="bulan_nama[]" value="${b.nama}" readonly class="bg-transparent font-bold text-slate-800 text-xs w-full focus:outline-none">
            </td>
            <td class="py-2.5 px-4">
                <input type="number" name="bulan_hk[]" value="${b.hk}" min="0" max="31" oninput="hitungHES()" class="hk-input w-24 px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 text-center font-bold focus:outline-none focus:ring-1 focus:ring-teal-500">
            </td>
            <td class="py-2.5 px-4">
                <input type="number" name="bulan_hl[]" value="${b.hl}" min="0" max="31" oninput="hitungHES()" class="hl-input w-24 px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 text-center font-bold focus:outline-none focus:ring-1 focus:ring-teal-500">
            </td>
            <td class="py-2.5 px-4 font-bold text-teal-700">
                <input type="number" name="bulan_he[]" value="${he}" readonly class="he-input w-24 px-3 py-1.5 rounded-xl border border-teal-200 text-xs bg-teal-50 text-center font-extrabold text-teal-800 focus:outline-none">
            </td>
            <td class="py-2.5 px-4">
                <input type="text" name="bulan_ket[]" value="${b.ket}" placeholder="Rincian libur/kegiatan..." class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 focus:outline-none focus:ring-1 focus:ring-teal-500">
            </td>
        `;
        tbody.appendChild(tr);
    });

    hitungHES();
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
