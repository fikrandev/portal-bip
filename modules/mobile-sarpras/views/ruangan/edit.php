<div class="px-4 pt-4 space-y-6 pb-20">
    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100">
        <form action="<?= url('kelola-sarpras/ruangan/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="return validateMobileRuanganForm()">
            <?= CSRF::field() ?>
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <input type="hidden" name="return_to" value="<?= url('mobile-sarpras/ruangan') ?>">

            <div class="space-y-4">
                <!-- Gedung / Bangunan Lokasi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Gedung / Bangunan <span class="text-rose-500">*</span></label>
                    <select name="bangunan_id" id="mobile_rng_bangunan" required onchange="onBangunanMobileChange(this.value)" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 outline-none focus:border-purple-500">
                        <option value="">-- Pilih Gedung --</option>
                        <?php foreach ($bangunanList as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $b['id'] == $item['bangunan_id'] ? 'selected' : '' ?>><?= e($b['nama_bangunan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 1. Unit Dulu, Baru 2. Jenis Ruang -->
                <div class="grid grid-cols-2 gap-3 p-3 bg-purple-50/50 rounded-2xl border border-purple-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">1. Unit / Jenjang <span class="text-rose-500">*</span></label>
                        <select name="unit" id="mobile_rng_unit" onchange="onUnitOrJenisMobileChange()" class="w-full px-3 py-2.5 bg-white border border-purple-200 rounded-xl text-sm font-bold text-slate-800 outline-none focus:border-purple-500">
                            <?php $currUnit = $item['unit'] ?? 'SD'; ?>
                            <option value="SD" <?= $currUnit === 'SD' ? 'selected' : '' ?>>SD</option>
                            <option value="SMP" <?= $currUnit === 'SMP' ? 'selected' : '' ?>>SMP</option>
                            <option value="SMA" <?= $currUnit === 'SMA' ? 'selected' : '' ?>>SMA</option>
                            <option value="PAUD" <?= $currUnit === 'PAUD' ? 'selected' : '' ?>>PAUD</option>
                            <option value="Yayasan" <?= $currUnit === 'Yayasan' ? 'selected' : '' ?>>Yayasan</option>
                            <option value="Semua" <?= $currUnit === 'Semua' ? 'selected' : '' ?>>Semua / Umum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">2. Jenis Ruang <span class="text-rose-500">*</span></label>
                        <select name="jenis_ruangan" id="mobile_rng_jenis" onchange="onUnitOrJenisMobileChange()" class="w-full px-3 py-2.5 bg-white border border-purple-200 rounded-xl text-sm font-bold text-slate-800 outline-none focus:border-purple-500">
                            <?php $currJenis = $item['jenis_ruangan'] ?? 'Ruang Kelas'; ?>
                            <option value="Ruang Kelas" <?= $currJenis === 'Ruang Kelas' ? 'selected' : '' ?>>Ruang Kelas (Belajar)</option>
                            <option value="Ruang Laboratorium" <?= $currJenis === 'Ruang Laboratorium' ? 'selected' : '' ?>>Ruang Laboratorium</option>
                            <option value="Ruang Kantor" <?= $currJenis === 'Ruang Kantor' ? 'selected' : '' ?>>Ruang Kantor / Administrasi</option>
                            <option value="Ruang Guru" <?= $currJenis === 'Ruang Guru' ? 'selected' : '' ?>>Ruang Guru</option>
                            <option value="Ruang Pimpinan" <?= $currJenis === 'Ruang Pimpinan' ? 'selected' : '' ?>>Ruang Kepala Sekolah / Pimpinan</option>
                            <option value="Ruang Perpustakaan" <?= $currJenis === 'Ruang Perpustakaan' ? 'selected' : '' ?>>Ruang Perpustakaan</option>
                            <option value="Ruang UKS" <?= $currJenis === 'Ruang UKS' ? 'selected' : '' ?>>Ruang UKS / Medis</option>
                            <option value="Ruang Ibadah" <?= $currJenis === 'Ruang Ibadah' ? 'selected' : '' ?>>Ruang Ibadah / Masjid</option>
                            <option value="Ruang Aula" <?= $currJenis === 'Ruang Aula' ? 'selected' : '' ?>>Ruang Aula / Serbaguna</option>
                            <option value="Ruang Konseling / BK" <?= $currJenis === 'Ruang Konseling / BK' ? 'selected' : '' ?>>Ruang BK</option>
                            <option value="Ruang OSIS" <?= $currJenis === 'Ruang OSIS' ? 'selected' : '' ?>>Ruang OSIS</option>
                            <option value="Gudang" <?= $currJenis === 'Gudang' ? 'selected' : '' ?>>Gudang / Logistik</option>
                            <option value="Toilet" <?= $currJenis === 'Toilet' ? 'selected' : '' ?>>Toilet / Sanitasi</option>
                            <option value="Kantin" <?= $currJenis === 'Kantin' ? 'selected' : '' ?>>Kantin / Dapur</option>
                            <option value="Lainnya" <?= $currJenis === 'Lainnya' ? 'selected' : '' ?>>Fasilitas Lainnya</option>
                        </select>
                    </div>
                </div>

                <!-- Nama Ruangan (Otomatis Dropdown dari Siswa jika Ruang Kelas) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700">
                            Nama Ruangan / Fasilitas <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-1.5">
                            <span id="mobile_nama_badge" class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-purple-100 text-purple-700">
                                Kelas: <?= e($currUnit) ?>
                            </span>
                            <button type="button" id="mobile_toggle_manual_btn" onclick="toggleMobileManualNama()" class="text-[11px] font-semibold text-purple-600 hover:text-purple-800 transition-colors flex items-center gap-1">
                                <span id="mobile_toggle_manual_icon">✏️</span>
                                <span id="mobile_toggle_manual_text">Ketik Manual</span>
                            </button>
                        </div>
                    </div>

                    <!-- Mode Dropdown Kelas Siswa -->
                    <div id="mobile_wrapper_nama_select">
                        <select id="mobile_rng_nama_select" onchange="onMobileNamaSelectChange(this.value)" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-800 outline-none focus:border-purple-500 focus:bg-white">
                            <option value="">-- Pilih Nama Kelas --</option>
                        </select>
                    </div>

                    <!-- Mode Text Biasa -->
                    <div id="mobile_wrapper_nama_text" class="hidden">
                        <input type="text" id="mobile_rng_nama_text" value="<?= e($item['nama_ruangan'] ?? '') ?>" oninput="onMobileNamaTextInput(this.value)" placeholder="Cth: Ruang Kelas 1A, Lab Komputer..." class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500 focus:bg-white">
                    </div>

                    <!-- Hidden input yang dikirim ke server -->
                    <input type="hidden" name="nama_ruangan" id="mobile_rng_nama" required value="<?= e($item['nama_ruangan'] ?? '') ?>">

                    <p id="mobile_nama_subtext" class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Pilihan kelas otomatis dari data <strong>Kelola Siswa</strong>.</span>
                    </p>
                </div>

                <!-- Posisi Lantai & Kode Ruangan -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Posisi Lantai <span class="text-rose-500">*</span></label>
                        <select name="lantai" id="mobile_rng_lantai" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-800 outline-none focus:border-purple-500">
                            <option value="<?= (int)($item['lantai'] ?? 1) ?>" selected>Lantai <?= (int)($item['lantai'] ?? 1) ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Ruangan</label>
                        <input type="text" name="kode_ruangan" id="mobile_rng_kode" value="<?= e($item['kode_ruangan'] ?? '') ?>" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500 font-mono" placeholder="Auto jika kosong">
                    </div>
                </div>
            </div>
            
            <hr class="border-slate-100 my-4">

            <!-- Dimensi & Kapasitas -->
            <div class="space-y-3 p-3.5 bg-purple-50/50 rounded-2xl border border-purple-100">
                <p class="text-xs font-bold text-purple-800 uppercase tracking-wider">Dimensi & Kapasitas</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Panjang (m)</label>
                        <input type="number" step="0.01" id="rng_panjang" name="panjang" value="<?= floatval($item['panjang'] ?? 0) > 0 ? floatval($item['panjang']) : '' ?>" placeholder="0.00" oninput="calculateLuasRng()" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-sm font-bold outline-none focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Lebar (m)</label>
                        <input type="number" step="0.01" id="rng_lebar" name="lebar" value="<?= floatval($item['lebar'] ?? 0) > 0 ? floatval($item['lebar']) : '' ?>" placeholder="0.00" oninput="calculateLuasRng()" class="w-full px-3 py-2 bg-white border border-purple-200 rounded-xl text-sm font-bold outline-none focus:border-purple-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-purple-900 mb-1">Luas Ruang (m²)</label>
                        <input type="number" step="0.01" id="rng_luas" name="luas" value="<?= floatval($item['luas'] ?? 0) > 0 ? floatval($item['luas']) : '' ?>" placeholder="0.00" class="w-full px-3 py-2 bg-purple-100 border border-purple-300 rounded-xl text-sm font-black text-purple-900 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kapasitas (Org)</label>
                        <input type="number" name="kapasitas" id="mobile_rng_kapasitas" min="0" value="<?= (int)($item['kapasitas'] ?? 30) ?>" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold outline-none focus:border-purple-500">
                    </div>
                </div>
            </div>

            <!-- Penanggung Jawab & Keterangan -->
            <div class="space-y-4 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Penanggung Jawab (Opsional)</label>
                    <input type="text" name="penanggung_jawab" value="<?= e($item['penanggung_jawab'] ?? '') ?>" list="mobile_pegawai_list" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500" placeholder="Pilih atau ketik nama guru / pegawai">
                    <datalist id="mobile_pegawai_list">
                        <?php if (!empty($pegawaiList)): ?>
                            <?php foreach ($pegawaiList as $p): 
                                $namaPJ = $p['nama'];
                                if (!empty($p['gelar']) && !str_contains($p['nama'], $p['gelar'])) {
                                    $namaPJ .= ', ' . $p['gelar'];
                                }
                                $ket = !empty($p['unit_tugas']) ? " ({$p['unit_tugas']})" : (!empty($p['jabatan']) ? " ({$p['jabatan']})" : "");
                            ?>
                                <option value="<?= e($namaPJ) ?>"><?= e($namaPJ . $ket) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Fungsi (Opsional)</label>
                    <input type="text" name="keterangan" value="<?= e($item['keterangan'] ?? '') ?>" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-purple-500" placeholder="Keterangan tambahan">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 active:scale-[0.99] text-white font-bold rounded-2xl py-3.5 shadow-lg shadow-purple-600/25 flex items-center justify-center gap-2 transition-all">
                    <i data-lucide="save" class="w-5 h-5"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const bangunanDataMobile = <?= json_encode($bangunanList ?? []) ?>;
const kelasDataByUnitMobile = <?= json_encode($kelasListByUnit ?? []) ?>;
const initialItemNamaRuangan = <?= json_encode($item['nama_ruangan'] ?? '') ?>;
const initialItemLantai = <?= (int)($item['lantai'] ?? 1) ?>;
const initialItemBangunanId = <?= json_encode($item['bangunan_id'] ?? '') ?>;

let isMobileManualNama = false;

function calculateLuasRng() {
    const p = parseFloat(document.getElementById('rng_panjang').value) || 0;
    const l = parseFloat(document.getElementById('rng_lebar').value) || 0;
    document.getElementById('rng_luas').value = (p * l > 0) ? (p * l).toFixed(2) : '';
}

function onBangunanMobileChange(bangunanId, selectedLantai = null) {
    const sel = document.getElementById('mobile_rng_lantai');
    if (!sel) return;
    const targetLantai = selectedLantai !== null ? parseInt(selectedLantai) : parseInt(sel.value || 1);
    sel.innerHTML = '';
    
    let maxLt = 1;
    if (bangunanId) {
        const found = bangunanDataMobile.find(b => String(b.id) === String(bangunanId));
        if (found && found.jumlah_lantai) {
            maxLt = Math.max(1, parseInt(found.jumlah_lantai));
        }
    }
    if (targetLantai > maxLt) maxLt = targetLantai;
    
    for (let i = 1; i <= maxLt; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = `Lantai ${i}`;
        if (i === targetLantai) opt.selected = true;
        sel.appendChild(opt);
    }
}

function onUnitOrJenisMobileChange(preferValue = '') {
    const unitSel = document.getElementById('mobile_rng_unit');
    const jenisSel = document.getElementById('mobile_rng_jenis');
    const unit = unitSel ? (unitSel.value || 'SD') : 'SD';
    const jenis = jenisSel ? (jenisSel.value || 'Ruang Kelas') : 'Ruang Kelas';
    const isKelas = (jenis === 'Ruang Kelas');

    const wrapperSelect = document.getElementById('mobile_wrapper_nama_select');
    const wrapperText = document.getElementById('mobile_wrapper_nama_text');
    const selectElem = document.getElementById('mobile_rng_nama_select');
    const textElem = document.getElementById('mobile_rng_nama_text');
    const finalInput = document.getElementById('mobile_rng_nama');
    const toggleBtn = document.getElementById('mobile_toggle_manual_btn');
    const badge = document.getElementById('mobile_nama_badge');
    const subtext = document.getElementById('mobile_nama_subtext');

    const kelasList = kelasDataByUnitMobile[unit] || kelasDataByUnitMobile[unit.toUpperCase()] || [];

    if (isKelas) {
        if (toggleBtn) toggleBtn.classList.remove('hidden');

        if (!isMobileManualNama) {
            // MODE DROPDOWN KELAS SISWA
            wrapperSelect.classList.remove('hidden');
            wrapperText.classList.add('hidden');
            if (badge) {
                badge.textContent = `Kelas: ${unit}`;
                badge.classList.remove('hidden');
            }
            if (toggleBtn) {
                document.getElementById('mobile_toggle_manual_icon').textContent = '✏️';
                document.getElementById('mobile_toggle_manual_text').textContent = 'Ketik Manual';
            }

            selectElem.innerHTML = '';
            const defOpt = document.createElement('option');
            defOpt.value = '';
            defOpt.textContent = `-- Pilih Nama Kelas (${unit}) --`;
            selectElem.appendChild(defOpt);

            let hasMatched = false;
            const targetVal = preferValue || (finalInput ? finalInput.value : '');

            if (kelasList.length > 0) {
                kelasList.forEach(k => {
                    const opt = document.createElement('option');
                    opt.value = k;
                    opt.textContent = k;
                    if (targetVal && (k === targetVal || k.toLowerCase() === targetVal.toLowerCase())) {
                        opt.selected = true;
                        hasMatched = true;
                    }
                    selectElem.appendChild(opt);
                });
            } else {
                const emptyOpt = document.createElement('option');
                emptyOpt.value = '';
                emptyOpt.textContent = `(Belum ada kelas siswa di ${unit})`;
                emptyOpt.disabled = true;
                selectElem.appendChild(emptyOpt);
            }

            const customOpt = document.createElement('option');
            customOpt.value = '__MANUAL__';
            customOpt.textContent = '✏️ + Ketik Nama Kelas Lainnya...';
            selectElem.appendChild(customOpt);

            if (hasMatched) {
                finalInput.value = selectElem.value;
                textElem.value = selectElem.value;
            } else if (!targetVal) {
                finalInput.value = '';
                textElem.value = '';
            }

            if (subtext) {
                subtext.innerHTML = `<svg class="w-3.5 h-3.5 text-purple-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> <span>Pilihan kelas otomatis dari data <strong>Kelola Siswa (${unit})</strong>.</span>`;
            }
        } else {
            // MODE KETIK MANUAL RUANG KELAS
            wrapperSelect.classList.add('hidden');
            wrapperText.classList.remove('hidden');
            if (badge) {
                badge.textContent = `Manual: ${unit}`;
                badge.classList.remove('hidden');
            }
            if (toggleBtn) {
                document.getElementById('mobile_toggle_manual_icon').textContent = '📋';
                document.getElementById('mobile_toggle_manual_text').textContent = 'Pilih Dropdown';
            }
            textElem.placeholder = `Cth: Ruang Kelas 1A, Kelas Khusus...`;
            if (subtext) {
                subtext.innerHTML = `<svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> <span>Mode ketik manual aktif. Silakan isi nama kelas kustom.</span>`;
            }
        }
    } else {
        // BUKAN RUANG KELAS
        isMobileManualNama = true;
        wrapperSelect.classList.add('hidden');
        wrapperText.classList.remove('hidden');
        if (toggleBtn) toggleBtn.classList.add('hidden');
        if (badge) badge.classList.add('hidden');
        textElem.placeholder = `Cth: ${jenis}, Lab Komputer, dll...`;
        if (subtext) {
            subtext.innerHTML = `<svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> <span>Masukkan nama ruangan sesuai fungsi peruntukan.</span>`;
        }
    }
}

function onMobileNamaSelectChange(val) {
    if (val === '__MANUAL__') {
        toggleMobileManualNama(true);
        return;
    }
    const finalInput = document.getElementById('mobile_rng_nama');
    const textElem = document.getElementById('mobile_rng_nama_text');
    if (finalInput) finalInput.value = val;
    if (textElem) textElem.value = val;
}

function onMobileNamaTextInput(val) {
    const finalInput = document.getElementById('mobile_rng_nama');
    if (finalInput) finalInput.value = val;
}

function toggleMobileManualNama(forceManual = null) {
    if (forceManual !== null) {
        isMobileManualNama = forceManual;
    } else {
        isMobileManualNama = !isMobileManualNama;
    }
    const finalInput = document.getElementById('mobile_rng_nama');
    onUnitOrJenisMobileChange(finalInput ? finalInput.value : '');
    if (isMobileManualNama) {
        const textElem = document.getElementById('mobile_rng_nama_text');
        if (textElem) textElem.focus();
    }
}

function validateMobileRuanganForm() {
    const finalInput = document.getElementById('mobile_rng_nama');
    const val = (finalInput ? finalInput.value : '').trim();
    if (!val) {
        alert('Silakan pilih atau isi Nama Ruangan / Fasilitas terlebih dahulu!');
        const textElem = document.getElementById('mobile_rng_nama_text');
        const selectElem = document.getElementById('mobile_rng_nama_select');
        const wrapperSelect = document.getElementById('mobile_wrapper_nama_select');
        if (wrapperSelect && !wrapperSelect.classList.contains('hidden')) {
            selectElem.focus();
        } else if (textElem) {
            textElem.focus();
        }
        return false;
    }
    return true;
}

// Inisialisasi awal saat edit
document.addEventListener('DOMContentLoaded', function() {
    const unit = document.getElementById('mobile_rng_unit')?.value || 'SD';
    const jenis = document.getElementById('mobile_rng_jenis')?.value || 'Ruang Kelas';
    const kelasList = kelasDataByUnitMobile[unit] || kelasDataByUnitMobile[unit.toUpperCase()] || [];

    if (jenis === 'Ruang Kelas' && kelasList.includes(initialItemNamaRuangan)) {
        isMobileManualNama = false;
    } else {
        isMobileManualNama = true;
    }

    onBangunanMobileChange(initialItemBangunanId, initialItemLantai);
    onUnitOrJenisMobileChange(initialItemNamaRuangan);

    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>
