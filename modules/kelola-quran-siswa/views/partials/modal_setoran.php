<?php
/**
 * Modal Setoran Al-Qur'an Siswa (Universal for PAUD, SD, SMP & SMA)
 * Variables expected:
 * $allStudents: list of students or loaded dynamically
 * $surahList: 114 surahs from QuranHelper
 * $iqroLevels: 1-6 levels from QuranHelper
 */
?>

<!-- Modal Setoran -->
<div id="modal-setoran" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity" onclick="closeModalSetoran()"></div>

    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-100">
            
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 px-6 py-4 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-lg">
                        📖
                    </div>
                    <div>
                        <h3 class="text-base font-bold leading-tight" id="modal-title">Input Setoran Qur'an Siswa</h3>
                        <p class="text-xs text-emerald-100/90" id="modal-subtitle">Pencatatan ziyadah, muroja'ah & tilawah/iqro</p>
                    </div>
                </div>
                <button type="button" onclick="closeModalSetoran()" class="p-1.5 rounded-full hover:bg-white/20 text-white/80 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form action="<?= url('kelola-quran-siswa/setoran/store') ?>" method="POST" class="p-6 space-y-4">
                <?= CSRF::field() ?>
                <input type="hidden" name="return_url" id="modal-return-url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '') ?>">

                <!-- 1. Pilihan Jenjang & Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang Unit Siswa <span class="text-rose-500">*</span></label>
                        <select name="jenjang" id="setoran-jenjang" onchange="onJenjangChange(this.value)" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-slate-50">
                            <option value="PAUD">🧸 PAUD / TK</option>
                            <option value="SD" selected>🎒 Sekolah Dasar (SD IT)</option>
                            <option value="SMP_SMA">🎓 SMP & SMA IT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Setoran <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>

                <!-- 2. Pilih Siswa -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Siswa / Santri <span class="text-rose-500">*</span></label>
                    <select name="siswa_id" id="setoran-siswa-id" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Pilih Siswa --</option>
                        <?php if (!empty($allStudents)): ?>
                            <?php foreach ($allStudents as $s): ?>
                                <option value="<?= $s['id'] ?>" data-jenjang="<?= strtoupper($s['jenjang'] ?? 'SD') ?>" data-kelas="<?= htmlspecialchars($s['kelas'] ?? '') ?>">
                                    <?= htmlspecialchars($s['nama_lengkap']) ?> (<?= htmlspecialchars($s['kelas'] ?? 'Tanpa Kelas') ?> - <?= htmlspecialchars($s['jenjang'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- 3. Jenis Kegiatan Setoran -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Setoran <span class="text-rose-500">*</span></label>
                    <select name="jenis_setoran" id="setoran-jenis" onchange="onJenisSetoranChange(this.value)" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="ziyadah">Ziyadah (Hafalan Baru)</option>
                        <option value="murojaah">Muroja'ah (Pengulangan Hafalan)</option>
                        <option value="iqro" id="opt-jenis-iqro">Iqro' / Tilawati (Khusus PAUD/Tahsin Awal)</option>
                        <option value="tasmi" id="opt-jenis-tasmi">Tasmi' Sekali Duduk</option>
                        <option value="munaqasyah" id="opt-jenis-munaqasyah">Ujian Munaqasyah Tahfidz</option>
                    </select>
                </div>

                <!-- 4. Bagian Khusus IQRO (Hanya aktif jika jenis = iqro) -->
                <div id="section-iqro" class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-3 hidden">
                    <span class="text-xs font-extrabold text-amber-800 flex items-center gap-1.5">
                        <span>📖</span> Detail Pembelajaran Iqro' / Tilawati
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Jilid Iqro'</label>
                            <select name="iqro_jilid" id="setoran-iqro-jilid" class="w-full px-3 py-2 rounded-xl border border-amber-300 text-xs bg-white">
                                <option value="">-- Pilih Jilid --</option>
                                <?php foreach (QuranHelper::getIqroLevels() as $lvl => $desc): ?>
                                    <option value="<?= $lvl ?>"><?= htmlspecialchars($desc) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-amber-900 mb-1">Halaman Iqro'</label>
                            <input type="number" name="iqro_halaman" placeholder="Contoh: 15" min="1" max="100" 
                                   class="w-full px-3 py-2 rounded-xl border border-amber-300 text-xs bg-white">
                        </div>
                    </div>
                </div>

                <!-- 5. Bagian Al-Qur'an (Surah, Ayat, Juz) -->
                <div id="section-quran" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <span class="text-xs font-extrabold text-slate-800 flex items-center gap-1.5">
                        <span>📗</span> Rincian Surah & Ayat
                    </span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Surah</label>
                            <select name="surah_nomor" id="setoran-surah" onchange="onSurahChange(this)"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                                <option value="">-- Pilih Surah --</option>
                                <?php foreach (QuranHelper::getSurahList() as $num => $sur): ?>
                                    <option value="<?= $num ?>" data-nama="<?= htmlspecialchars($sur['nama']) ?>" data-ayat="<?= $sur['ayat'] ?>" data-juz="<?= $sur['juz'] ?>">
                                        <?= $num ?>. <?= htmlspecialchars($sur['nama']) ?> (<?= $sur['arab'] ?>) - Juz <?= $sur['juz'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="surah_nama" id="setoran-surah-nama" value="">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Juz</label>
                            <input type="number" name="juz" id="setoran-juz" min="1" max="30" placeholder="1-30" 
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Ayat Awal</label>
                            <input type="number" name="ayat_awal" id="setoran-ayat-awal" min="1" placeholder="1"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Ayat Akhir</label>
                            <input type="number" name="ayat_akhir" id="setoran-ayat-akhir" min="1" placeholder="20"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Halaman Mushaf (Opsional)</label>
                            <input type="number" name="halaman_quran" min="1" max="604" placeholder="1-604"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                        </div>
                    </div>
                </div>

                <!-- 6. Penilaian Kualitas (Tajwid, Makhorij, Kelancaran) & Status -->
                <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200/70 space-y-3">
                    <span class="text-xs font-extrabold text-emerald-950 flex items-center gap-1.5">
                        <span>⭐</span> Penilaian Mutu Bacaan & Status Kelulusan
                    </span>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Kelancaran</label>
                            <select name="nilai_kelancaran" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white font-bold text-emerald-800">
                                <option value="A">A (Sangat Lancar)</option>
                                <option value="B">B (Lancar)</option>
                                <option value="C">C (Kurang Lancar)</option>
                                <option value="D">D (Bata-bata)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Tajwid</label>
                            <select name="nilai_tajwid" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white font-bold text-emerald-800">
                                <option value="A">A (Sangat Baik)</option>
                                <option value="B">B (Baik)</option>
                                <option value="C">C (Cukup)</option>
                                <option value="D">D (Perlu Bimbingan)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Makhorij</label>
                            <select name="nilai_makhorij" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white font-bold text-emerald-800">
                                <option value="A">A (Fashih)</option>
                                <option value="B">B (Baik)</option>
                                <option value="C">C (Cukup)</option>
                                <option value="D">D (Kurang)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">Status Hasil</label>
                            <select name="status_lulus" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white font-extrabold text-teal-800">
                                <option value="lancar">✓ Lancar (Lanjut)</option>
                                <option value="mutqin">⭐ Mutqin (Sempurna)</option>
                                <option value="ulang">⟳ Mengulang</option>
                                <option value="perlu_bimbingan">! Perlu Bimbingan</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 7. Catatan Ustadz / Ustadzah -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pembimbing / Evaluasi Tajwid</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan perbaikan ayat tertentu, mad/ghunnah, motivasi siswa..." 
                              class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeModalSetoran()" 
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Simpan Setoran</span>
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
function openModalSetoran(defaultJenjang = 'SD', preselectedSiswaId = null) {
    const modal = document.getElementById('modal-setoran');
    if (!modal) return;
    
    // Set jenjang
    const selJenjang = document.getElementById('setoran-jenjang');
    if (selJenjang) {
        selJenjang.value = defaultJenjang;
        onJenjangChange(defaultJenjang);
    }

    // Filter siswa dropdown by jenjang
    filterSiswaByJenjang(defaultJenjang, preselectedSiswaId);

    modal.classList.remove('hidden');
}

function closeModalSetoran() {
    const modal = document.getElementById('modal-setoran');
    if (modal) modal.classList.add('hidden');
}

function onJenjangChange(jenjang) {
    const subTitle = document.getElementById('modal-subtitle');
    const optIqro = document.getElementById('opt-jenis-iqro');
    const optTasmi = document.getElementById('opt-jenis-tasmi');
    const optMunaqasyah = document.getElementById('opt-jenis-munaqasyah');
    const selJenis = document.getElementById('setoran-jenis');

    if (jenjang === 'PAUD') {
        if (subTitle) subTitle.innerText = "Qur'an Siswa PAUD / TK - Iqro, Tilawati & Surah Pendek";
        if (optIqro) optIqro.style.display = 'block';
        if (selJenis) selJenis.value = 'iqro';
        onJenisSetoranChange('iqro');
    } else if (jenjang === 'SMP_SMA') {
        if (subTitle) subTitle.innerText = "Qur'an Siswa SMP & SMA - Tahfidz Multi-Juz & Tasmi'";
        if (optIqro) optIqro.style.display = 'none';
        if (selJenis && selJenis.value === 'iqro') selJenis.value = 'ziyadah';
        onJenisSetoranChange('ziyadah');
    } else {
        if (subTitle) subTitle.innerText = "Qur'an Siswa SD IT - Ziyadah, Muroja'ah & Tilawah";
        if (optIqro) optIqro.style.display = 'block';
        if (selJenis && selJenis.value === 'iqro') selJenis.value = 'ziyadah';
        onJenisSetoranChange('ziyadah');
    }

    filterSiswaByJenjang(jenjang);
}

function onJenisSetoranChange(jenis) {
    const secIqro = document.getElementById('section-iqro');
    const secQuran = document.getElementById('section-quran');

    if (jenis === 'iqro') {
        if (secIqro) secIqro.classList.remove('hidden');
        if (secQuran) secQuran.classList.add('hidden');
    } else {
        if (secIqro) secIqro.classList.add('hidden');
        if (secQuran) secQuran.classList.remove('hidden');
    }
}

function onSurahChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    const surahNama = opt.getAttribute('data-nama') || '';
    const ayatMax = opt.getAttribute('data-ayat') || '';
    const juz = opt.getAttribute('data-juz') || '';

    const inputNama = document.getElementById('setoran-surah-nama');
    if (inputNama) inputNama.value = surahNama;

    const inputJuz = document.getElementById('setoran-juz');
    if (inputJuz && juz) inputJuz.value = juz;

    const inputAyatAkhir = document.getElementById('setoran-ayat-akhir');
    if (inputAyatAkhir && ayatMax) {
        inputAyatAkhir.setAttribute('max', ayatMax);
        inputAyatAkhir.placeholder = 'Maks ' + ayatMax;
    }
}

function filterSiswaByJenjang(jenjang, selectedId = null) {
    const select = document.getElementById('setoran-siswa-id');
    if (!select) return;

    const options = select.querySelectorAll('option');
    let firstMatch = null;

    options.forEach(opt => {
        if (!opt.value) return;
        const optJenjang = opt.getAttribute('data-jenjang') || 'SD';
        
        let match = false;
        if (jenjang === 'PAUD' && (optJenjang === 'PAUD' || optJenjang === 'TK')) {
            match = true;
        } else if (jenjang === 'SMP_SMA' && (optJenjang === 'SMP' || optJenjang === 'SMA')) {
            match = true;
        } else if (jenjang === 'SD' && optJenjang === 'SD') {
            match = true;
        }

        if (match) {
            opt.style.display = '';
            if (!firstMatch) firstMatch = opt.value;
        } else {
            opt.style.display = 'none';
        }
    });

    if (selectedId) {
        select.value = selectedId;
    } else if (firstMatch) {
        select.value = firstMatch;
    }
}
</script>
