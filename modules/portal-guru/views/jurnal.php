<?php
/**
 * Jurnal Mengajar Harian Guru Screen
 * Tersinkronisasi otomatis dengan Jadwal Pelajaran (Kelola Perangkat Pembelajaran)
 */
?>

<!-- Header with Back Button -->
<div class="px-5 pt-4 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-bold text-slate-800 text-base leading-tight">Jurnal Mengajar</h2>
            <p class="text-[10px] text-slate-400 font-medium">Sinkron dengan Jadwal KBM</p>
        </div>
    </div>
    <a href="<?= url('mobile/absensi-kelas') ?>" class="w-9 h-9 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center press-bounce shadow-xs" title="Absensi Kelas">
        <i data-lucide="check-square" class="w-4 h-4"></i>
    </a>
</div>

<div class="p-4 space-y-4">

    <!-- Tab Switcher: Jadwal & Isi Jurnal vs Riwayat -->
    <div class="flex p-1 bg-slate-200/70 rounded-2xl">
        <button type="button" onclick="switchMainTab('jadwal')" id="btn-tab-jadwal" class="flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-600 shadow-sm transition-all">
            📅 Jadwal & Isi Jurnal
        </button>
        <button type="button" onclick="switchMainTab('history')" id="btn-tab-history" class="flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all">
            📋 Riwayat Jurnal (<?= count($recentJournals) ?>)
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 1: JADWAL & INPUT JURNAL                         -->
    <!-- ======================================================== -->
    <div id="section-jadwal" class="space-y-3.5">
        
        <!-- Day Selector Pill Horizontal Scroll -->
        <div class="overflow-x-auto pb-1 scrollbar-none flex items-center gap-1.5 text-xs">
            <?php 
                $dayList = ['Hari Ini' => $todayName, 'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu'];
            ?>
            <?php foreach ($dayList as $label => $dayValue): ?>
            <button type="button" 
                    onclick="filterScheduleByDay('<?= e($dayValue) ?>', this)" 
                    class="day-filter-btn px-3 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all press-bounce <?= ($label === 'Hari Ini') ? 'bg-primary-600 text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200' ?>"
                    data-day="<?= e($dayValue) ?>">
                <?= e($label) ?><?= ($label === 'Hari Ini') ? " ({$todayName})" : "" ?>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Banner Info Tanggal -->
        <div class="flex items-center justify-between px-3.5 py-2.5 bg-gradient-to-r from-primary-50 to-sky-50 rounded-2xl border border-primary-100 text-xs">
            <span class="font-bold text-primary-900 flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-4 h-4 text-primary-600"></i>
                <span id="current-selected-day-label"><?= e($todayName) ?></span>, <?= e($todayDate) ?>
            </span>
            <span class="text-[10px] font-bold text-primary-700 bg-white px-2 py-0.5 rounded-full border border-primary-200 shadow-xs" id="schedule-counter-badge">
                <?= count($todaySchedule) ?> Jam KBM
            </span>
        </div>

        <!-- Schedule Cards Container -->
        <div id="schedule-list-container" class="space-y-3">
            <?php if (!empty($allSchedules)): ?>
                <?php foreach ($allSchedules as $sch): ?>
                <?php 
                    $isToday = ($sch['hari'] === $todayName);
                    $displayClass = $isToday ? 'block' : 'hidden';
                ?>
                <div class="schedule-item-card bg-white rounded-3xl p-4 shadow-sm border <?= $sch['has_jurnal'] ? 'border-emerald-200 bg-emerald-50/20' : 'border-slate-100' ?> space-y-3 <?= $displayClass ?>"
                     data-day="<?= e($sch['hari']) ?>"
                     data-id="<?= e($sch['id']) ?>"
                     data-kelas="<?= e($sch['class']) ?>"
                     data-mapel="<?= e($sch['subject']) ?>"
                     data-jam="<?= e($sch['jam_ke']) ?>"
                     data-jp="<?= e($sch['jumlah_jp']) ?>"
                     data-time="<?= e($sch['time']) ?>">
                    
                    <!-- Top Metadata -->
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-xl text-[11px] font-black bg-primary-100 text-primary-800">
                                <?= e($sch['class']) ?>
                            </span>
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                Jam ke-<?= e($sch['jam_ke']) ?> &bull; <?= e($sch['jumlah_jp']) ?> JP
                            </span>
                        </div>

                        <?php if ($sch['has_jurnal']): ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Jurnal Terisi
                        </span>
                        <?php else: ?>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            Belum Diisi
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- Subject & Info -->
                    <div>
                        <h4 class="font-black text-sm text-slate-900 leading-snug"><?= e($sch['subject']) ?></h4>
                        <div class="flex items-center gap-3 text-xs text-slate-500 mt-1">
                            <span class="flex items-center gap-1"><i data-lucide="clock" class="w-3.5 h-3.5 text-primary-500"></i> <?= e($sch['time']) ?></span>
                            <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i> <?= e($sch['room']) ?></span>
                        </div>
                    </div>

                    <!-- If already filled, show snippet -->
                    <?php if ($sch['has_jurnal']): ?>
                    <div class="p-2.5 bg-emerald-50/70 rounded-2xl border border-emerald-100 text-xs">
                        <p class="text-[10px] text-emerald-700 font-bold">Materi Terlaksana:</p>
                        <p class="font-semibold text-slate-800 mt-0.5 line-clamp-2"><?= e($sch['jurnal_materi']) ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Action Button -->
                    <div class="pt-1 border-t border-slate-50 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-400 font-medium">Hari <?= e($sch['hari']) ?></span>

                        <?php if ($sch['has_jurnal']): ?>
                        <button type="button" 
                                onclick="openEditJournalModal(<?= $sch['jurnal_id'] ?>, '<?= e($sch['class']) ?>', '<?= e($sch['subject']) ?>', '<?= e($sch['jam_ke']) ?>', <?= $sch['jumlah_jp'] ?>)"
                                class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl text-xs press-bounce flex items-center gap-1.5 transition-all">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-primary-600"></i> Edit Jurnal
                        </button>
                        <?php else: ?>
                        <button type="button" 
                                onclick="openFillJournalModal(<?= $sch['id'] ?>, '<?= e($sch['class']) ?>', '<?= e($sch['subject']) ?>', '<?= e($sch['jam_ke']) ?>', <?= $sch['jumlah_jp'] ?>, '<?= e($sch['time']) ?>')"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-2xl text-xs press-bounce flex items-center gap-1.5 shadow-sm shadow-primary-500/25 transition-all">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Isi Jurnal
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="bg-white rounded-3xl p-6 text-center border border-slate-100 space-y-2">
                    <div class="w-12 h-12 bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="calendar-x" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Belum Ada Jadwal Mengajar</h3>
                    <p class="text-xs text-slate-500 max-w-xs mx-auto">
                        Jadwal Anda belum diinput atau digenerate di modul Perangkat Pembelajaran oleh bagian Kurikulum.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Empty state for day filter -->
            <div id="day-empty-state" class="hidden bg-white rounded-3xl p-6 text-center border border-dashed border-slate-200 space-y-1">
                <p class="text-xs font-bold text-slate-700">Tidak ada jadwal KBM pada hari ini</p>
                <p class="text-[11px] text-slate-400">Silakan pilih hari lain pada tombol filter di atas.</p>
            </div>
        </div>

        <!-- Tombol Input Mandiri (Jika Guru Mengajar Tambahan / Pengganti) -->
        <div class="pt-2 text-center">
            <button type="button" onclick="openManualJournalModal()" class="text-xs font-bold text-primary-600 hover:text-primary-700 inline-flex items-center gap-1.5 p-2 rounded-xl hover:bg-primary-50 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i> Input Jurnal Tambahan / Guru Pengganti
            </button>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- SECTION 2: RIWAYAT JURNAL MENGAJAR                       -->
    <!-- ======================================================== -->
    <div id="section-history" class="hidden space-y-3">
        <?php if (!empty($recentJournals)): ?>
            <?php foreach ($recentJournals as $j): ?>
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-primary-100 text-primary-800">
                            <?= e($j['class']) ?>
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium"><?= e($j['date']) ?></span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        ✓ <?= e($j['status']) ?> (<?= e($j['jumlah_jp']) ?> JP)
                    </span>
                </div>

                <div>
                    <h4 class="font-bold text-xs text-slate-800 leading-snug"><?= e($j['topic']) ?></h4>
                    <p class="text-[10px] text-slate-400 font-medium mt-0.5"><?= e($j['hours']) ?> &bull; <?= e($j['subject']) ?></p>
                </div>

                <?php if (!empty($j['summary'])): ?>
                <p class="text-xs text-slate-600 bg-slate-50 p-2.5 rounded-2xl border border-slate-100 leading-relaxed">
                    <?= e($j['summary']) ?>
                </p>
                <?php endif; ?>

                <div class="flex items-center justify-between text-[10px] text-slate-500 pt-1 border-t border-slate-50">
                    <span class="flex items-center gap-1"><i data-lucide="users" class="w-3 h-3 text-primary-600"></i> <?= e($j['attendance']) ?></span>
                    <button type="button" 
                            onclick="showJournalDetailSheet('<?= e($j['kode']) ?>', '<?= e($j['class']) ?>', '<?= e(addslashes($j['topic'])) ?>', '<?= e(addslashes($j['kegiatan'])) ?>', '<?= e(addslashes($j['catatan'])) ?>', <?= $j['jumlah_jp'] ?>, '<?= e($j['date']) ?>')" 
                            class="font-bold text-primary-600 hover:text-primary-700 flex items-center gap-0.5">
                        Lihat Rincian &rarr;
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-8 text-center border border-slate-100 space-y-2">
                <i data-lucide="book-open" class="w-10 h-10 text-slate-300 mx-auto"></i>
                <h4 class="font-bold text-slate-800 text-sm">Belum Ada Riwayat Jurnal</h4>
                <p class="text-xs text-slate-400">Jurnal yang Anda isi akan otomatis tersimpan dan direkap di sini.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL BOTTOM SHEET: FORM ISI / EDIT JURNAL              -->
<!-- ======================================================== -->
<div id="journal-input-modal" class="fixed inset-0 z-50 bg-slate-950/40 backdrop-blur-xs flex flex-col justify-end opacity-0 pointer-events-none transition-all duration-300">
    <div id="journal-input-sheet" class="bg-white rounded-t-[2.5rem] p-5 sm:p-6 max-h-[90vh] overflow-y-auto space-y-4 shadow-2xl transform translate-y-full transition-transform duration-300 max-w-lg mx-auto w-full border-t border-slate-100">
        
        <!-- Sheet Handle -->
        <div class="w-10 h-1 bg-slate-200 rounded-full mx-auto"></div>

        <!-- Sheet Header -->
        <div class="flex items-center justify-between">
            <div>
                <h3 id="form-modal-title" class="font-black text-slate-900 text-base leading-tight">Isi Jurnal Mengajar</h3>
                <p id="form-modal-subtitle" class="text-xs text-primary-600 font-semibold mt-0.5">Kelas & Jadwal Terisi Otomatis</p>
            </div>
            <button type="button" onclick="closeJournalModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs">
                ✕
            </button>
        </div>

        <!-- Form Isi Jurnal -->
        <form id="form-isi-jurnal" onsubmit="handleJournalSave(event)" class="space-y-3.5">
            <?= CSRF::field() ?>
            <input type="hidden" name="jadwal_id" id="input_jadwal_id" value="">
            <input type="hidden" name="jurnal_id" id="input_jurnal_id" value="">
            <input type="hidden" name="kelas" id="input_kelas" value="">
            <input type="hidden" name="mata_pelajaran" id="input_mapel" value="">
            <input type="hidden" name="tanggal" id="input_tanggal" value="<?= date('Y-m-d') ?>">
            <input type="hidden" name="jam_ke" id="input_jam_ke" value="1">
            <input type="hidden" name="jumlah_jp" id="input_jumlah_jp" value="2">
            <input type="hidden" name="kehadiran_summary" id="input_kehadiran" value="Semua Siswa Hadir">

            <!-- Strip Info Ringkas (Disabled / Non-Editable) -->
            <div class="p-3 bg-slate-50 border border-slate-100 rounded-2xl flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-slate-200/80 rounded-xl text-xs font-bold text-slate-800 shadow-2xs">
                        <i data-lucide="book-open" class="w-3.5 h-3.5 text-primary-600"></i>
                        <span id="badge_mapel">-</span>
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-slate-200/80 rounded-xl text-xs font-bold text-indigo-700 shadow-2xs">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-indigo-600"></i>
                        <span id="badge_kelas">-</span>
                    </span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="px-2.5 py-1 bg-slate-200/80 text-slate-700 font-bold text-[11px] rounded-xl">
                        Jam Ke-<span id="badge_jam_ke">1</span>
                    </span>
                    <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 font-black text-[11px] rounded-xl border border-indigo-200/70">
                        <span id="badge_jumlah_jp">2</span> JP
                    </span>
                </div>
            </div>

            <!-- Materi / Topik Pokok (Wajib) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                    Materi Pokok / Bahasan <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       name="materi_pokok" 
                       id="input_materi" 
                       required 
                       placeholder="Contoh: QS. An-Naba Ayat 1-15 (Hukum Mad Thobi'i)" 
                       class="w-full px-3.5 py-2.5 text-xs bg-slate-50 rounded-2xl border border-slate-200 font-semibold text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all">
            </div>

            <!-- Uraian Aktivitas Belajar (Ringkas) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Uraian Aktivitas Belajar</label>
                <textarea name="uraian_kegiatan" 
                          id="input_kegiatan" 
                          rows="2" 
                          placeholder="Ringkasan kegiatan murid di kelas..." 
                          class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition-all leading-relaxed"></textarea>
                
                <!-- Quick Tags -->
                <div class="flex flex-wrap gap-1 mt-1">
                    <button type="button" onclick="appendTagModal('Talaqqi dan Simak Bacaan')" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-primary-50 text-primary-700 hover:bg-primary-100">+ Talaqqi</button>
                    <button type="button" onclick="appendTagModal('Setoran Hafalan Mandiri')" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-primary-50 text-primary-700 hover:bg-primary-100">+ Setoran</button>
                    <button type="button" onclick="appendTagModal('Latihan Soal & Diskusi')" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-primary-50 text-primary-700 hover:bg-primary-100">+ Latihan Soal</button>
                    <button type="button" onclick="appendTagModal('Kuis Evaluasi Formatif')" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-purple-50 text-purple-700 hover:bg-purple-100">+ Kuis Formatif</button>
                </div>
            </div>

            <!-- Presensi Siswa Interaktif -->
            <div class="space-y-1.5 pt-0.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <label class="text-[11px] font-bold text-slate-700">Presensi Siswa</label>
                        <span id="presensi_counter_badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            Memuat siswa...
                        </span>
                    </div>
                    <button type="button" 
                            onclick="setAllStudentsPresent()" 
                            class="px-2.5 py-1 text-[10px] font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl transition-all border border-emerald-200 flex items-center gap-1 press-bounce">
                        <i data-lucide="check-check" class="w-3 h-3 text-emerald-600"></i>
                        <span>Hadir Semua</span>
                    </button>
                </div>

                <!-- Daftar Checkbox Siswa -->
                <div id="presensi-students-container" class="max-h-44 overflow-y-auto divide-y divide-slate-100 border border-slate-200/80 rounded-2xl bg-white shadow-2xs p-1">
                    <div id="presensi-loading" class="py-6 text-center text-slate-400 text-xs">
                        <div class="w-5 h-5 border-2 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto mb-1.5"></div>
                        Memuat daftar nama murid...
                    </div>
                    <div id="presensi-students-list" class="space-y-0.5 hidden"></div>
                </div>
                <p class="text-[10px] text-slate-400 italic">Hilangkan centang pada nama murid yang tidak hadir (sakit/izin/alfa).</p>
            </div>

            <!-- Catatan / Evaluasi (Opsional) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-700 mb-1">Catatan / Evaluasi (Opsional)</label>
                <input type="text" name="catatan_kelas" id="input_catatan" placeholder="Catatan siswa berprestasi / kejadian khusus..." class="w-full px-3.5 py-2 text-xs bg-slate-50 rounded-2xl border border-slate-200 text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-primary-500 outline-none transition-all">
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex items-center gap-2.5">
                <button type="submit" id="btn-save-journal" class="flex-1 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-2xl shadow-lg shadow-primary-600/25 flex items-center justify-center gap-2 press-bounce transition-all">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Jurnal Mengajar</span>
                </button>
                <button type="button" onclick="closeJournalModal()" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl press-bounce transition-all">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Inisialisasi ikon Lucide
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) lucide.createIcons();
    });

    // Switch main tabs (Jadwal vs Riwayat)
    function switchMainTab(tab) {
        const secJadwal = document.getElementById('section-jadwal');
        const secHistory = document.getElementById('section-history');
        const btnJadwal = document.getElementById('btn-tab-jadwal');
        const btnHist = document.getElementById('btn-tab-history');

        if (tab === 'jadwal') {
            secJadwal.classList.remove('hidden');
            secHistory.classList.add('hidden');
            btnJadwal.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-600 shadow-sm transition-all';
            btnHist.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all';
        } else {
            secJadwal.classList.add('hidden');
            secHistory.classList.remove('hidden');
            btnHist.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-600 shadow-sm transition-all';
            btnJadwal.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all';
        }
        if (window.lucide) lucide.createIcons();
    }

    // Filter jadwal berdasarkan hari
    function filterScheduleByDay(day, btn) {
        // Update button styles
        document.querySelectorAll('.day-filter-btn').forEach(b => {
            b.className = 'day-filter-btn px-3 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all press-bounce bg-white text-slate-600 border border-slate-200';
        });
        btn.className = 'day-filter-btn px-3 py-1.5 rounded-xl font-bold whitespace-nowrap transition-all press-bounce bg-primary-600 text-white shadow-xs';

        document.getElementById('current-selected-day-label').innerText = day;

        // Show/hide cards
        const cards = document.querySelectorAll('.schedule-item-card');
        let visibleCount = 0;
        cards.forEach(card => {
            if (card.getAttribute('data-day') === day) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        const emptyState = document.getElementById('day-empty-state');
        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }

        document.getElementById('schedule-counter-badge').innerText = visibleCount + ' Jam KBM';
    }

    // Buka Modal Isi Jurnal dari Jadwal
    function openFillJournalModal(jadwalId, kelas, mapel, jamKe, jumlahJp, timeStr) {
        document.getElementById('input_jadwal_id').value = jadwalId;
        document.getElementById('input_jurnal_id').value = '';
        document.getElementById('input_kelas').value = kelas;
        document.getElementById('input_mapel').value = mapel;
        document.getElementById('input_jam_ke').value = jamKe;
        document.getElementById('input_jumlah_jp').value = jumlahJp || 2;
        document.getElementById('input_materi').value = '';
        document.getElementById('input_kegiatan').value = '';
        document.getElementById('input_catatan').value = '';

        // Update Badges Ringkas (Read-Only / Disabled)
        document.getElementById('badge_mapel').textContent = mapel;
        document.getElementById('badge_kelas').textContent = kelas;
        document.getElementById('badge_jam_ke').textContent = jamKe;
        document.getElementById('badge_jumlah_jp').textContent = jumlahJp || 2;

        document.getElementById('form-modal-title').innerText = 'Isi Jurnal Mengajar';
        document.getElementById('form-modal-subtitle').innerText = `${kelas} • ${mapel} (${timeStr})`;

        // Muat Daftar Siswa Otomatis untuk Presensi
        loadClassStudents(kelas);

        showModalSheet();
    }

    // Buka Modal Edit Jurnal
    function openEditJournalModal(jurnalId, kelas, mapel, jamKe, jumlahJp) {
        AndroidUI.showCenterLoading('Memuat data jurnal...');
        
        fetch('<?= url("mobile/api/jurnal/detail/") ?>' + jurnalId)
            .then(res => res.json())
            .then(res => {
                AndroidUI.hideCenterLoading();
                if (res && res.success && res.data) {
                    const d = res.data;
                    document.getElementById('input_jadwal_id').value = d.jadwal_id || '';
                    document.getElementById('input_jurnal_id').value = d.id;
                    document.getElementById('input_kelas').value = d.kelas;
                    document.getElementById('input_mapel').value = d.mata_pelajaran;
                    document.getElementById('input_tanggal').value = d.tanggal;
                    document.getElementById('input_jam_ke').value = d.jam_ke;
                    document.getElementById('input_jumlah_jp').value = d.jumlah_jp || 2;
                    document.getElementById('input_materi').value = d.materi_pokok || '';
                    document.getElementById('input_kegiatan').value = d.uraian_kegiatan || '';
                    document.getElementById('input_kehadiran').value = d.kehadiran_summary || '';
                    document.getElementById('input_catatan').value = d.catatan_kelas || '';

                    // Update Badges Ringkas
                    document.getElementById('badge_mapel').textContent = d.mata_pelajaran;
                    document.getElementById('badge_kelas').textContent = d.kelas;
                    document.getElementById('badge_jam_ke').textContent = d.jam_ke;
                    document.getElementById('badge_jumlah_jp').textContent = d.jumlah_jp || 2;

                    document.getElementById('form-modal-title').innerText = 'Edit Jurnal Pembelajaran';
                    document.getElementById('form-modal-subtitle').innerText = `${d.kelas} • Kode: ${d.kode_jurnal}`;

                    loadClassStudents(d.kelas, d.kehadiran_summary);

                    showModalSheet();
                } else {
                    AndroidUI.toast('Gagal memuat detail jurnal.', 'error');
                }
            })
            .catch(() => {
                AndroidUI.hideCenterLoading();
                AndroidUI.toast('Terjadi kendala jaringan.', 'error');
            });
    }

    // Modal Input Mandiri
    function openManualJournalModal() {
        const defaultClass = '<?= $classes[0] ?? "Kelas 1 Abu Bakar" ?>';
        const defaultMapel = '<?= $subjects[0] ?? "Tahsin & Al-Quran" ?>';

        document.getElementById('input_jadwal_id').value = '';
        document.getElementById('input_jurnal_id').value = '';
        document.getElementById('input_kelas').value = defaultClass;
        document.getElementById('input_mapel').value = defaultMapel;
        document.getElementById('input_jam_ke').value = '1';
        document.getElementById('input_jumlah_jp').value = '2';
        document.getElementById('input_materi').value = '';
        document.getElementById('input_kegiatan').value = '';
        document.getElementById('input_catatan').value = '';

        document.getElementById('badge_mapel').textContent = defaultMapel;
        document.getElementById('badge_kelas').textContent = defaultClass;
        document.getElementById('badge_jam_ke').textContent = '1';
        document.getElementById('badge_jumlah_jp').textContent = '2';

        document.getElementById('form-modal-title').innerText = 'Input Jurnal Mandiri';
        document.getElementById('form-modal-subtitle').innerText = `${defaultClass} • KBM Tambahan`;

        loadClassStudents(defaultClass);

        showModalSheet();
    }

    // ── Presensi Siswa: Fetch & Interaktif Checkbox ───────────────
    let currentClassStudents = [];

    function loadClassStudents(kelas, existingAttendanceSummary = null) {
        const loadingEl = document.getElementById('presensi-loading');
        const listEl = document.getElementById('presensi-students-list');
        const counterBadge = document.getElementById('presensi_counter_badge');

        loadingEl.classList.remove('hidden');
        listEl.classList.add('hidden');
        listEl.innerHTML = '';
        counterBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600';
        counterBadge.innerText = 'Memuat siswa...';

        fetch('<?= url("mobile/api/siswa-kelas") ?>?kelas=' + encodeURIComponent(kelas))
            .then(res => res.json())
            .then(res => {
                loadingEl.classList.add('hidden');
                listEl.classList.remove('hidden');

                if (res && res.success && Array.isArray(res.data) && res.data.length > 0) {
                    currentClassStudents = res.data;
                    
                    let html = '';
                    currentClassStudents.forEach((s, idx) => {
                        let isChecked = true;
                        if (existingAttendanceSummary && existingAttendanceSummary.includes('Tidak Hadir:')) {
                            if (existingAttendanceSummary.toLowerCase().includes(s.nama_lengkap.toLowerCase())) {
                                isChecked = false;
                            }
                        }

                        html += `
                            <label class="flex items-center justify-between px-3 py-2 hover:bg-slate-50/90 rounded-xl cursor-pointer select-none transition-colors border-b border-slate-50 last:border-b-0">
                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                    <input type="checkbox" 
                                           ${isChecked ? 'checked' : ''} 
                                           class="student-cb w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer shrink-0" 
                                           data-id="${s.id}" 
                                           data-name="${s.nama_lengkap.replace(/"/g, '&quot;')}" 
                                           onchange="onStudentAttendanceToggle(this)">
                                    <span class="text-xs font-semibold text-slate-800 truncate">${idx + 1}. ${s.nama_lengkap}</span>
                                </div>
                                <span class="status-tag px-2 py-0.5 rounded-lg text-[10px] font-bold ${isChecked ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100'} shrink-0">
                                    ${isChecked ? 'Hadir' : 'Tidak Hadir'}
                                </span>
                            </label>
                        `;
                    });

                    listEl.innerHTML = html;
                    updateAttendanceSummary();
                } else {
                    listEl.innerHTML = `
                        <div class="py-4 text-center text-xs text-slate-400">
                            Tidak ada data murid terdaftar di kelas ini.
                        </div>
                    `;
                    counterBadge.innerText = '0 Murid';
                    document.getElementById('input_kehadiran').value = 'Semua Siswa Hadir';
                }
                if (window.lucide) lucide.createIcons();
            })
            .catch(() => {
                loadingEl.classList.add('hidden');
                listEl.classList.remove('hidden');
                listEl.innerHTML = `
                    <div class="py-4 text-center text-xs text-amber-600 font-medium">
                        Presensi otomatis ditandai Hadir Lengkap.
                    </div>
                `;
                counterBadge.innerText = 'Semua Hadir';
                document.getElementById('input_kehadiran').value = 'Semua Siswa Hadir Lengkap';
            });
    }

    function setAllStudentsPresent() {
        const checkboxes = document.querySelectorAll('.student-cb');
        checkboxes.forEach(cb => {
            cb.checked = true;
            const tag = cb.closest('label').querySelector('.status-tag');
            if (tag) {
                tag.className = 'status-tag px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 shrink-0';
                tag.innerText = 'Hadir';
            }
        });
        updateAttendanceSummary();
        AndroidUI.toast('Semua murid ditandai Hadir ✓', 'info');
    }

    function onStudentAttendanceToggle(checkbox) {
        const tag = checkbox.closest('label').querySelector('.status-tag');
        if (tag) {
            if (checkbox.checked) {
                tag.className = 'status-tag px-2 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100 shrink-0';
                tag.innerText = 'Hadir';
            } else {
                tag.className = 'status-tag px-2 py-0.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-100 shrink-0';
                tag.innerText = 'Tidak Hadir';
            }
        }
        updateAttendanceSummary();
    }

    function updateAttendanceSummary() {
        const checkboxes = document.querySelectorAll('.student-cb');
        const total = checkboxes.length;
        if (total === 0) return;

        let presentCount = 0;
        let absentNames = [];

        checkboxes.forEach(cb => {
            if (cb.checked) {
                presentCount++;
            } else {
                absentNames.push(cb.getAttribute('data-name'));
            }
        });

        const absentCount = total - presentCount;
        const counterBadge = document.getElementById('presensi_counter_badge');
        const inputKehadiran = document.getElementById('input_kehadiran');

        if (absentCount === 0) {
            counterBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
            counterBadge.innerText = `${total}/${total} Hadir (Lengkap)`;
            inputKehadiran.value = `${total}/${total} Hadir Lengkap`;
        } else {
            counterBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200';
            counterBadge.innerText = `${presentCount}/${total} Hadir • ${absentCount} Tidak Hadir`;
            inputKehadiran.value = `${presentCount}/${total} Hadir (${absentCount} Tidak Hadir: ${absentNames.join(', ')})`;
        }
    }

    function showModalSheet() {
        const modal = document.getElementById('journal-input-modal');
        const sheet = document.getElementById('journal-input-sheet');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        sheet.classList.remove('translate-y-full');
        if (window.lucide) lucide.createIcons();
    }

    function closeJournalModal() {
        const modal = document.getElementById('journal-input-modal');
        const sheet = document.getElementById('journal-input-sheet');
        modal.classList.add('opacity-0', 'pointer-events-none');
        sheet.classList.add('translate-y-full');
    }

    function appendTagModal(text) {
        const el = document.getElementById('input_kegiatan');
        if (el) {
            el.value = el.value ? el.value + '\n• ' + text : '• ' + text;
        }
    }

    // Handle AJAX Save Jurnal
    function handleJournalSave(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btn-save-journal');
        
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Menyimpan Jurnal...</span>`;
        if (window.lucide) lucide.createIcons();

        const formData = new FormData(form);

        fetch('<?= url("mobile/jurnal/simpan") ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Simpan Jurnal Mengajar</span>`;
            if (window.lucide) lucide.createIcons();

            if (res && res.success) {
                closeJournalModal();
                AndroidUI.toast(res.message || 'Jurnal pembelajaran berhasil disimpan!', 'success');
                setTimeout(() => {
                    if (window.MobileRouter) {
                        window.MobileRouter.reload();
                    } else {
                        window.location.reload();
                    }
                }, 400);
            } else {
                AndroidUI.toast(res.message || 'Gagal menyimpan jurnal.', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<i data-lucide="check" class="w-4 h-4"></i><span>Simpan Jurnal Mengajar</span>`;
            if (window.lucide) lucide.createIcons();
            AndroidUI.toast('Terjadi kesalahan koneksi server.', 'error');
        });
    }

    // Detail Bottom Sheet
    function showJournalDetailSheet(kode, kelas, topic, kegiatan, catatan, jp, date) {
        AndroidUI.bottomSheet({
            title: `${kelas} • ${jp} JP`,
            subtitle: `Kode Jurnal: ${kode} (${date})`,
            icon: '📖',
            iconBg: 'bg-primary-100 text-primary-600',
            content: `
                <div class="space-y-3 text-xs text-left">
                    <div>
                        <p class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Topik / Bahasan:</p>
                        <h4 class="font-black text-slate-900 text-sm mt-0.5 leading-snug">${topic}</h4>
                    </div>
                    <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 space-y-1">
                        <p class="text-[11px] text-slate-500 font-bold">Uraian Kegiatan:</p>
                        <p class="text-slate-700 leading-relaxed whitespace-pre-line">${kegiatan || 'Sesuai kurikulum modul ajar.'}</p>
                    </div>
                    ${catatan ? `
                    <div class="bg-amber-50/70 p-3 rounded-2xl border border-amber-100 space-y-0.5">
                        <p class="text-[10px] text-amber-800 font-bold">Catatan Khusus:</p>
                        <p class="text-slate-700 leading-relaxed">${catatan}</p>
                    </div>` : ''}
                </div>
            `,
            actions: [
                {
                    text: 'Tutup Rincian',
                    className: 'w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-2xl text-center'
                }
            ]
        });
    }

    // Auto-open modal jika URL memuat param jadwal_id
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const jId = urlParams.get('jadwal_id');
        const editId = urlParams.get('edit_id');

        if (jId) {
            const card = document.querySelector(`.schedule-item-card[data-id="${jId}"]`);
            if (card) {
                openFillJournalModal(
                    jId,
                    card.getAttribute('data-kelas'),
                    card.getAttribute('data-mapel'),
                    card.getAttribute('data-jam'),
                    parseInt(card.getAttribute('data-jp') || 2),
                    card.getAttribute('data-time')
                );
            }
        } else if (editId) {
            openEditJournalModal(parseInt(editId));
        }
    });
</script>
