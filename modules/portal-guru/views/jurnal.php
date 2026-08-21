<?php
/**
 * Jurnal Mengajar Harian Guru Screen
 * Compact mobile size with Button Loading, Center Data Loading, and Smooth SVG Checkmark Modals.
 */
?>

<!-- Header with Back Button -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
        <i data-lucide="arrow-left" class="w-5 h-5"></i>
    </a>
    <h2 class="font-bold text-slate-800 text-base">Jurnal Mengajar</h2>
    <a href="<?= url('mobile/absensi-kelas') ?>" class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
        <i data-lucide="check-square" class="w-4 h-4"></i>
    </a>
</div>

<div class="p-4 space-y-4">

    <!-- Tab Switcher (Form / Riwayat) -->
    <div class="flex p-1 bg-slate-200/70 rounded-2xl">
        <button type="button" onclick="switchJournalTab('form')" id="tab-btn-form" class="flex-1 py-2 text-xs font-bold rounded-xl bg-white text-blue-600 shadow-sm transition-all">
            📝 Input Jurnal Baru
        </button>
        <button type="button" onclick="switchJournalTab('history')" id="tab-btn-history" class="flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all">
            📋 Riwayat Jurnal
        </button>
    </div>

    <!-- TAB 1: FORM INPUT JURNAL -->
    <div id="journal-tab-form" class="space-y-4">
        
        <form onsubmit="submitJournalForm(event)" class="space-y-4">
            
            <!-- Card 1: Informasi Kelas & Jadwal -->
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
                <h3 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span> Informasi Kelas & Jadwal
                </h3>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Pilih Kelas</label>
                        <select name="kelas" class="w-full px-3 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($classes as $c): ?>
                            <option value="<?= e($c) ?>" <?= $c === 'Kelas 7A' ? 'selected' : '' ?>><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Mata Pelajaran</label>
                        <select name="mapel" class="w-full px-3 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($subjects as $s): ?>
                            <option value="<?= e($s) ?>"><?= e($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Mengajar</label>
                        <input type="date" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Jam Pelajaran</label>
                        <select name="jam_ke" class="w-full px-3 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1-2">Jam ke 1-2 (08.00 - 09.30)</option>
                            <option value="3-4">Jam ke 3-4 (09.45 - 11.15)</option>
                            <option value="5-6">Jam ke 5-6 (11.30 - 13.00)</option>
                            <option value="7-8">Jam ke 7-8 (13.30 - 15.00)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Card 2: Materi & Aktivitas Pembelajaran -->
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
                <h3 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span> Materi & Capaian Pembelajaran
                </h3>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Topik / Judul Pembelajaran</label>
                    <input type="text" name="topik" value="Teorema Pythagoras & Segitiga Siku-Siku" required placeholder="Contoh: Operasi Aljabar Satu Variabel" class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Kegiatan & Capaian Belajar</label>
                    <textarea name="kegiatan" rows="3" placeholder="Tuliskan ringkasan aktivitas siswa di kelas..." class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 leading-relaxed"></textarea>
                </div>

                <!-- Quick Tags helper -->
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <button type="button" onclick="appendTag('Diskusi Kelompok & Presentasi')" class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">+ Diskusi Kelompok</button>
                    <button type="button" onclick="appendTag('Praktikum / Eksperimen')" class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100">+ Praktikum</button>
                    <button type="button" onclick="appendTag('Kuis Evaluasi Formatif')" class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100">+ Kuis Formatif</button>
                </div>
            </div>

            <!-- Card 3: Rekap Kehadiran Siswa Kelas -->
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Presensi Siswa Kelas
                    </h3>
                    <a href="<?= url('mobile/absensi-kelas') ?>" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-0.5">
                        Absensi Detail <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid grid-cols-4 gap-2 text-center">
                    <div class="bg-emerald-50 p-2.5 rounded-2xl border border-emerald-100">
                        <span class="text-base font-black text-emerald-700">30</span>
                        <p class="text-[10px] font-bold text-emerald-600">Hadir</p>
                    </div>
                    <div class="bg-blue-50 p-2.5 rounded-2xl border border-blue-100">
                        <span class="text-base font-black text-blue-700">1</span>
                        <p class="text-[10px] font-bold text-blue-600">Sakit</p>
                    </div>
                    <div class="bg-amber-50 p-2.5 rounded-2xl border border-amber-100">
                        <span class="text-base font-black text-amber-700">1</span>
                        <p class="text-[10px] font-bold text-amber-600">Izin</p>
                    </div>
                    <div class="bg-rose-50 p-2.5 rounded-2xl border border-rose-100">
                        <span class="text-base font-black text-rose-700">0</span>
                        <p class="text-[10px] font-bold text-rose-600">Alpa</p>
                    </div>
                </div>
            </div>

            <!-- Card 4: Dokumentasi Foto Kelas -->
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-3">
                <h3 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span> Foto Dokumentasi Pembelajaran
                </h3>

                <label class="block cursor-pointer">
                    <input type="file" accept="image/*" onchange="previewJournalPhoto(this)" class="hidden">
                    <div id="journal-photo-dropzone" class="border-2 border-dashed border-slate-200 hover:border-blue-400 bg-slate-50 hover:bg-blue-50/50 rounded-2xl p-4 text-center transition-all">
                        <i data-lucide="image-plus" class="w-8 h-8 mx-auto text-slate-400 mb-1.5"></i>
                        <p class="text-xs font-bold text-slate-700">Ambil atau Upload Foto Kelas</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Format JPG / PNG maksimal 5MB</p>
                    </div>
                    <div id="journal-photo-preview" class="hidden relative rounded-2xl overflow-hidden border border-slate-200 aspect-video">
                        <img id="journal-img-tag" src="" alt="Preview Foto" loading="lazy" class="w-full h-full object-cover">
                        <button type="button" onclick="removeJournalPhoto(event)" class="absolute top-2 right-2 w-7 h-7 bg-black/60 text-white rounded-full flex items-center justify-center text-xs">✕</button>
                    </div>
                </label>
            </div>

            <!-- Card 5: Catatan Khusus Guru -->
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-2">
                <label class="block text-[11px] font-bold text-slate-600">Catatan / Evaluasi Pembelajaran (Opsional)</label>
                <textarea name="catatan" rows="2" placeholder="Catatan perkembangan belajar siswa..." class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <!-- Submit Action Buttons with Button Loading -->
            <div class="space-y-2 pt-1">
                <button type="submit" id="btn-submit-journal" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2 press-bounce">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Simpan Jurnal Mengajar
                </button>
                <button type="button" onclick="saveDraftJournal(this)" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-2xl flex items-center justify-center gap-1.5 press-bounce">
                    <i data-lucide="save" class="w-3.5 h-3.5"></i> Simpan sebagai Draf
                </button>
            </div>

        </form>

    </div>

    <!-- TAB 2: RIWAYAT JURNAL -->
    <div id="journal-tab-history" class="hidden space-y-3">
        <?php foreach ($recentJournals as $j): ?>
        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-2.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-blue-100 text-blue-800">
                        <?= e($j['class']) ?>
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium"><?= e($j['date']) ?></span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ✓ <?= e($j['status']) ?>
                </span>
            </div>

            <div>
                <h4 class="font-bold text-xs text-slate-800 leading-snug"><?= e($j['topic']) ?></h4>
                <p class="text-[10px] text-slate-400 font-medium mt-0.5"><?= e($j['hours']) ?> • <?= e($j['subject']) ?></p>
            </div>

            <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-2xl border border-slate-100 leading-relaxed">
                <?= e($j['summary']) ?>
            </p>

            <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1 border-t border-slate-50">
                <span class="flex items-center gap-1"><i data-lucide="users" class="w-3 h-3 text-emerald-600"></i> <?= e($j['attendance']) ?></span>
                <button onclick="showJournalDetailSheet('<?= e($j['id']) ?>', '<?= e($j['class']) ?>', '<?= e($j['topic']) ?>', '<?= e($j['summary']) ?>')" class="font-bold text-blue-600 hover:text-blue-700">Detail & Foto &rarr;</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
    function submitJournalForm(event) {
        event.preventDefault();
        
        AndroidUI.confirm({
            title: 'Simpan Jurnal Mengajar?',
            subtitle: 'Laporan harian mengajar kelas',
            icon: '📝',
            iconBg: 'bg-blue-100 text-blue-600',
            message: 'Apakah seluruh materi dan ringkasan presensi kelas sudah benar?',
            confirmText: 'Ya, Simpan',
            cancelText: 'Edit Lagi',
            onConfirm: () => {
                window.handleJournalSubmit(event);
            }
        });
    }

    function saveDraftJournal(btn) {
        AndroidUI.setButtonLoading(btn, 'Menyimpan Draf...');
        setTimeout(() => {
            AndroidUI.resetButton(btn);
            AndroidUI.toast('Draf jurnal tersimpan offline di perangkat Anda!', 'info');
        }, 500);
    }

    function showJournalDetailSheet(id, kelas, topic, summary) {
        AndroidUI.bottomSheet({
            title: `${id} • ${kelas}`,
            subtitle: 'Detail Jurnal Pembelajaran',
            icon: '📖',
            iconBg: 'bg-blue-100 text-blue-600',
            content: `
                <div class="space-y-2.5 text-xs text-left">
                    <div>
                        <p class="text-slate-400 font-medium">Topik / Bab Pembelajaran:</p>
                        <h4 class="font-bold text-slate-900 text-sm mt-0.5">${topic}</h4>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <p class="text-slate-400 font-medium mb-1">Rincian Kegiatan & Evaluasi:</p>
                        <p class="text-slate-700 leading-relaxed">${summary}</p>
                    </div>
                </div>
            `,
            actions: [
                {
                    text: 'Tutup',
                    className: 'w-full py-2.5 bg-slate-100 text-slate-700 font-bold text-xs rounded-2xl text-center'
                }
            ]
        });
    }

    function switchJournalTab(tab) {
        const formView = document.getElementById('journal-tab-form');
        const histView = document.getElementById('journal-tab-history');
        const btnForm = document.getElementById('tab-btn-form');
        const btnHist = document.getElementById('tab-btn-history');

        // Center screen loading indicator when loading history data
        AndroidUI.showCenterLoading(tab === 'history' ? 'Memuat riwayat jurnal...' : 'Membuka form jurnal...');

        setTimeout(() => {
            AndroidUI.hideCenterLoading();
            if (tab === 'form') {
                formView.classList.remove('hidden');
                histView.classList.add('hidden');
                btnForm.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-blue-600 shadow-sm transition-all';
                btnHist.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all';
            } else {
                formView.classList.add('hidden');
                histView.classList.remove('hidden');
                btnHist.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-blue-600 shadow-sm transition-all';
                btnForm.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all';
            }
        }, 250);
    }

    function appendTag(tagText) {
        const textarea = document.querySelector('textarea[name="kegiatan"]');
        if (textarea) {
            textarea.value = textarea.value ? textarea.value + '\n• ' + tagText : '• ' + tagText;
        }
    }

    function previewJournalPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('journal-img-tag').src = e.target.result;
                document.getElementById('journal-photo-dropzone').classList.add('hidden');
                document.getElementById('journal-photo-preview').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeJournalPhoto(e) {
        e.preventDefault();
        document.getElementById('journal-img-tag').src = '';
        document.getElementById('journal-photo-dropzone').classList.remove('hidden');
        document.getElementById('journal-photo-preview').classList.add('hidden');
    }
</script>
