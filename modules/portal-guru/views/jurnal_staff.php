<?php
/**
 * Jurnal Kerja Staff (Support System) Mobile Screen
 * Dirancang khusus untuk Tenaga Kependidikan, IT, Sarpras, TU, dan Divisi Support System.
 * Fitur:
 * - Menampilkan daftar jam kerja harian dinamis (sesi tilawah, aktifitas, istirahat, laporan)
 * - Klik jam untuk membuka popup/modal pengisian tugas & keterangan kerja
 * - Dropdown tugas otomatis menyesuaikan dengan jabatan staff di master_jabatan / jurnal_staff_tugas
 * - Mendukung pilihan tugas custom / input manual jika tugas belum terdaftar
 * - Rekap progres pengisian harian dan riwayat jurnal sebelumnya
 */
?>

<!-- Header with Back Button -->
<div class="px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs">
    <div class="flex items-center gap-2.5">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="font-bold text-slate-900 text-base leading-tight flex items-center gap-1.5">
                <span>Jurnal Kerja Staff</span>
                <span class="text-xs">💼</span>
            </h2>
            <p class="text-[10px] text-slate-400 font-medium truncate max-w-[210px]">
                <?= e($jabatanName) ?> &bull; Support System
            </p>
        </div>
    </div>
    <div class="flex items-center gap-1.5">
        <a href="<?= url('mobile/rekap-absen') ?>" class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center press-bounce shadow-xs" title="Rekap Kehadiran">
            <i data-lucide="calendar" class="w-4 h-4"></i>
        </a>
    </div>
</div>

<div class="p-4 space-y-4">

    <!-- 1. Date Selector & Daily Progress Card -->
    <div class="bg-gradient-to-br from-slate-900 via-primary-950 to-emerald-950 rounded-3xl p-4 text-white shadow-lg shadow-primary-950/20 space-y-3">
        <!-- Tanggal Bar -->
        <div class="flex items-center justify-between gap-2">
            <div>
                <p class="text-[10px] uppercase font-bold tracking-wider text-primary-200">Tanggal Jurnal:</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-sm font-black text-amber-300">
                        <?php
                            $ts = strtotime($selectedDate);
                            $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                            $namaHari = $dayNames[date('l', $ts)] ?? 'Senin';
                            echo e($namaHari . ', ' . date('d M Y', $ts));
                        ?>
                    </span>
                    <?php if ($selectedDate === date('Y-m-d')): ?>
                        <span class="px-2 py-0.2 rounded-full text-[9px] font-bold bg-emerald-500/30 text-emerald-300 border border-emerald-400/30">Hari Ini</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Date Changer -->
            <div class="flex items-center gap-1">
                <?php
                    $prevDate = date('Y-m-d', strtotime('-1 day', $ts));
                    $nextDate = date('Y-m-d', strtotime('+1 day', $ts));
                ?>
                <a href="<?= url('mobile/jurnal') ?>?tanggal=<?= $prevDate ?>" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center press-bounce text-xs" title="Hari Sebelumnya">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
                <?php if ($selectedDate !== date('Y-m-d')): ?>
                    <a href="<?= url('mobile/jurnal') ?>" class="px-2 py-1 rounded-xl bg-primary-600 text-white text-[10px] font-bold press-bounce">
                        Hari Ini
                    </a>
                <?php endif; ?>
                <a href="<?= url('mobile/jurnal') ?>?tanggal=<?= $nextDate ?>" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center press-bounce text-xs" title="Hari Berikutnya">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
                <label class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center press-bounce text-xs cursor-pointer relative" title="Pilih Tanggal">
                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    <input type="date" value="<?= e($selectedDate) ?>" onchange="location.href='<?= url('mobile/jurnal') ?>?tanggal=' + this.value" class="absolute inset-0 opacity-0 cursor-pointer">
                </label>
            </div>
        </div>

        <!-- Progress Bar Pengisian -->
        <div class="bg-white/10 rounded-2xl p-2.5 backdrop-blur-xs border border-white/10 space-y-1.5">
            <div class="flex items-center justify-between text-xs">
                <span class="font-medium text-slate-200 text-[11px]">Progres Pengisian Sesi:</span>
                <span class="font-black <?= $totalTerisi === $totalSesi && $totalSesi > 0 ? 'text-emerald-300' : 'text-amber-300' ?>">
                    <?= $totalTerisi ?> / <?= $totalSesi ?> Sesi Terisi (<?= $persenTerisi ?>%)
                </span>
            </div>
            <div class="w-full h-2 rounded-full bg-black/30 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-amber-400 via-emerald-400 to-emerald-500 transition-all duration-500" style="width: <?= min(100, $persenTerisi) ?>%"></div>
            </div>
        </div>
    </div>

    <!-- 2. Tab Switcher: Sesi Jam vs Riwayat Jurnal -->
    <div class="flex p-1 bg-slate-200/70 rounded-2xl gap-1">
        <button type="button" onclick="switchStaffTab('jam')" id="btn-tab-jam" class="flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-700 shadow-sm transition-all flex items-center justify-center gap-1.5">
            <span>🕒</span>
            <span>Jadwal Jam Kerja</span>
        </button>
        <button type="button" onclick="switchStaffTab('riwayat')" id="btn-tab-riwayat" class="flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5">
            <span>📋</span>
            <span>Riwayat (<?= count($recentStaffJournals) ?>)</span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 1: DAFTAR SLOT JAM KERJA (GRID 3 KOLOM)          -->
    <!-- ======================================================== -->
    <div id="section-staff-jam" class="space-y-3 pb-24">
        
        <div class="flex items-center justify-between px-1">
            <div>
                <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                    <span>Pilih Jam Kerja:</span>
                </span>
                <p class="text-[10px] text-slate-400 font-medium">Bisa klik beberapa jam sekaligus</p>
            </div>
            
            <div class="flex items-center gap-1 text-[10px]">
                <button type="button" onclick="selectAllJam()" class="px-2.5 py-1 rounded-xl bg-primary-50 text-primary-700 font-bold hover:bg-primary-100 transition-all press-bounce border border-primary-100">
                    Pilih Semua
                </button>
                <button type="button" onclick="clearSelectedJam()" class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 transition-all press-bounce border border-slate-200">
                    Reset
                </button>
            </div>
        </div>

        <?php if (!empty($jamList)): ?>
            <div class="grid grid-cols-3 gap-2 sm:gap-2.5" id="grid-jam-staff">
                <?php foreach ($jamList as $idx => $jam): ?>
                    <?php
                        $jamId = (int)$jam['id'];
                        $rawMulai = substr((string)$jam['jam_mulai'], 0, 5);
                        $rawSelesai = substr((string)$jam['jam_selesai'], 0, 5);
                        $rentangStr = $rawMulai . ' - ' . $rawSelesai;
                        $rentangDisplay = str_replace(':', '.', $rawMulai) . ' - ' . str_replace(':', '.', $rawSelesai);

                        // Cek apakah sudah diisi
                        $existing = $journalByJamId[$jamId] ?? ($journalByJamId[$rentangStr] ?? null);
                        $isFilled = !empty($existing);
                        
                        $tipe = strtolower($jam['tipe'] ?? 'aktifitas');
                        $badgeBg = 'bg-slate-100 text-slate-700 border-slate-200';
                        $iconEmoji = '💼';
                        if ($tipe === 'tilawah') {
                            $badgeBg = 'bg-purple-100 text-purple-800 border-purple-200';
                            $iconEmoji = '📖';
                        } elseif ($tipe === 'istirahat') {
                            $badgeBg = 'bg-amber-100 text-amber-800 border-amber-200';
                            $iconEmoji = '🍽️';
                        } elseif ($tipe === 'laporan' || stripos($jam['nama_sesi'], 'laporan') !== false) {
                            $badgeBg = 'bg-blue-100 text-blue-800 border-blue-200';
                            $iconEmoji = '📊';
                        }
                    ?>
                    
                    <div id="card-jam-<?= $jamId ?>"
                         onclick="toggleSelectJam(<?= $jamId ?>)"
                         class="staff-jam-card group relative rounded-2xl p-2.5 border transition-all duration-200 cursor-pointer flex flex-col justify-between select-none press-bounce <?= $isFilled ? 'border-emerald-200 bg-emerald-50/25 hover:border-emerald-300' : 'border-slate-200/90 bg-white hover:border-primary-300 shadow-2xs' ?>"
                         data-jam-id="<?= $jamId ?>"
                         data-jam-rentang="<?= e($rentangStr) ?>"
                         data-jam-display="<?= e($rentangDisplay) ?> WITA"
                         data-nama-sesi="<?= e($jam['nama_sesi']) ?>"
                         data-is-filled="<?= $isFilled ? '1' : '0' ?>"
                         data-jurnal-id="<?= $isFilled ? (int)$existing['id'] : 0 ?>"
                         data-tugas-id="<?= $isFilled ? (int)($existing['tugas_id'] ?? 0) : 0 ?>"
                         data-aktivitas="<?= $isFilled ? htmlspecialchars((string)$existing['aktivitas'], ENT_QUOTES) : '' ?>"
                         data-uraian="<?= $isFilled ? htmlspecialchars((string)$existing['uraian_hasil'], ENT_QUOTES) : '' ?>"
                         data-kendala="<?= $isFilled ? htmlspecialchars((string)($existing['kendala'] ?? ''), ENT_QUOTES) : '' ?>"
                         data-status="<?= $isFilled ? htmlspecialchars((string)($existing['status'] ?? 'selesai'), ENT_QUOTES) : 'selesai' ?>"
                         data-foto="<?= $isFilled ? htmlspecialchars((string)($existing['foto_bukti'] ?? ''), ENT_QUOTES) : '' ?>">
                        
                        <!-- Top Bar: Sesi Badge + Checkbox Circle -->
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded-lg border <?= $badgeBg ?> leading-none flex items-center gap-0.5">
                                <span><?= $iconEmoji ?></span>
                                <span>S<?= $idx + 1 ?></span>
                            </span>
                            <div class="jam-check-box w-5 h-5 rounded-full border border-slate-300 bg-white flex items-center justify-center text-[10px] font-black text-transparent transition-all shadow-2xs">
                                ✓
                            </div>
                        </div>

                        <!-- Jam & Nama Sesi -->
                        <div class="my-2">
                            <div class="text-[11px] font-black text-slate-900 tracking-tight leading-none">
                                <?= e($rentangDisplay) ?>
                            </div>
                            <p class="text-[9.5px] font-semibold text-slate-600 truncate mt-1 leading-tight" title="<?= e($jam['nama_sesi']) ?>">
                                <?= e($jam['nama_sesi']) ?>
                            </p>
                        </div>

                        <!-- Status Terisi / Kosong -->
                        <div class="pt-1.5 border-t <?= $isFilled ? 'border-emerald-100' : 'border-slate-100' ?>">
                            <?php if ($isFilled): ?>
                                <div class="flex flex-col gap-0.5">
                                    <span class="inline-flex items-center gap-0.5 text-[8.5px] font-bold text-emerald-800 bg-emerald-100/90 px-1.5 py-0.5 rounded-md w-fit">
                                        ✓ Terisi
                                    </span>
                                    <span class="text-[8.5px] text-slate-500 truncate font-medium mt-0.5" title="<?= e($existing['aktivitas']) ?>">
                                        <?= e($existing['aktivitas']) ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center justify-between">
                                    <span class="inline-block text-[8.5px] font-medium text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-md">
                                        Belum
                                    </span>
                                    <span class="text-[9px] text-primary-500 font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                        +
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-8 text-center space-y-3 border border-slate-100">
                <span class="text-4xl">🕒</span>
                <h4 class="font-bold text-slate-800 text-sm">Belum Ada Pengaturan Jam</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto">
                    Admin belum menyetel slot jam kerja di modul Kelola Jurnal Staff.
                </p>
            </div>
        <?php endif; ?>

    </div>

    <!-- ======================================================== -->
    <!-- SECTION 2: RIWAYAT JURNAL STAFF (TAB 2)                  -->
    <!-- ======================================================== -->
    <div id="section-staff-riwayat" class="hidden space-y-3">
        <div class="flex items-center justify-between px-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                30 Jurnal Terakhir Anda:
            </span>
        </div>

        <?php if (!empty($recentStaffJournals)): ?>
            <div class="space-y-2.5">
                <?php foreach ($recentStaffJournals as $rj): ?>
                    <?php
                        $rTs = strtotime($rj['tanggal']);
                        $rHari = $dayNames[date('l', $rTs)] ?? 'Senin';
                        $tglFmt = $rHari . ', ' . date('d M Y', $rTs);
                        $statusClass = ($rj['status'] === 'selesai') ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : (($rj['status'] === 'proses') ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200');
                    ?>
                    <div class="bg-white rounded-3xl p-3.5 shadow-sm border border-slate-100 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <span class="font-extrabold text-xs text-slate-900"><?= e($tglFmt) ?></span>
                                <span class="text-[10px] text-slate-400 font-mono ml-1.5"><?= e($rj['jam_rentang']) ?></span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold border <?= $statusClass ?>">
                                <?= ucfirst(e($rj['status'])) ?>
                            </span>
                        </div>

                        <div>
                            <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                <span class="text-primary-600">🎯</span>
                                <span><?= e($rj['aktivitas']) ?></span>
                            </h4>
                            <p class="text-[10.5px] text-slate-600 mt-1 leading-relaxed whitespace-pre-line">
                                <?= e($rj['uraian_hasil']) ?>
                            </p>
                            <?php if (!empty($rj['kendala'])): ?>
                                <p class="text-[10px] text-amber-700 bg-amber-50 p-1.5 rounded-xl border border-amber-100 mt-1.5">
                                    <strong>Kendala:</strong> <?= e($rj['kendala']) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($rj['foto_bukti'])): ?>
                                <div class="mt-2">
                                    <a href="<?= asset($rj['foto_bukti']) ?>" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-primary-600 hover:underline">
                                        <span>📷</span> Lihat Bukti Kegiatan
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                            <span class="font-mono"><?= e($rj['kode_jurnal']) ?></span>
                            <button type="button" 
                                    onclick="openModalJurnalStaff(<?= htmlspecialchars(json_encode([
                                        'jam_id' => (int)($rj['jam_id'] ?? 0),
                                        'jam_rentang' => (string)$rj['jam_rentang'],
                                        'jam_display' => (string)$rj['jam_rentang'] . ' WITA',
                                        'nama_sesi' => 'Sesi ' . $rj['jam_rentang'],
                                        'tipe' => 'aktifitas',
                                        'is_filled' => true,
                                        'jurnal_id' => (int)$rj['id'],
                                        'tugas_id' => (int)($rj['tugas_id'] ?? 0),
                                        'aktivitas' => (string)$rj['aktivitas'],
                                        'uraian_hasil' => (string)$rj['uraian_hasil'],
                                        'kendala' => (string)($rj['kendala'] ?? ''),
                                        'status' => (string)$rj['status'],
                                        'foto_bukti' => (string)($rj['foto_bukti'] ?? ''),
                                        'tanggal' => (string)$rj['tanggal']
                                    ]), ENT_QUOTES, 'UTF-8') ?>)" 
                                    class="text-primary-600 font-bold hover:underline flex items-center gap-0.5">
                                <span>✏️</span> Edit Jurnal Ini
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-3xl p-8 text-center space-y-2 border border-slate-100">
                <span class="text-3xl">📝</span>
                <h4 class="font-bold text-slate-800 text-xs">Belum Ada Riwayat Jurnal</h4>
                <p class="text-[11px] text-slate-400">Anda belum pernah mengisi jurnal staff sebelumnya.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ======================================================== -->
<!-- FLOATING ACTION BAR: MUNCUL SAAT MINIMAL 1 JAM TERPILIH   -->
<!-- ======================================================== -->
<div id="floating-jurnal-bar" 
     class="fixed bottom-20 inset-x-4 sm:max-w-md sm:mx-auto z-40 transition-all duration-300 transform translate-y-32 opacity-0 pointer-events-none">
    <div class="bg-slate-900/95 text-white backdrop-blur-md p-3 rounded-2xl shadow-2xl border border-white/10 flex items-center justify-between gap-2.5">
        
        <!-- Left: Counter & Summary Jam -->
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-md shadow-emerald-500/30" id="floating-count-badge">
                0
            </div>
            <div class="min-w-0">
                <div class="font-extrabold text-xs text-white leading-tight flex items-center gap-1.5">
                    <span id="floating-count-text">0 Jam Terpilih</span>
                </div>
                <p class="text-[10px] text-slate-300 truncate max-w-[130px] sm:max-w-[180px]" id="floating-jam-summary">
                    Ketuk jam untuk memilih
                </p>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" onclick="clearSelectedJam()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 flex items-center justify-center text-xs press-bounce transition-all" title="Batal Pilih">
                ✕
            </button>
            <button type="button" onclick="openModalFromFloatingBar()" class="px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-bold text-xs shadow-md shadow-emerald-500/25 press-bounce flex items-center gap-1.5 transition-all">
                <span>📝</span>
                <span id="floating-btn-text">Isi Jurnal</span>
            </button>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL POPUP FORM ISI JURNAL STAFF (BOTTOM SHEET STYLE)  -->
<!-- ======================================================== -->
<div id="modal-jurnal-staff" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 hidden transition-opacity duration-200">
    <div class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col animate-in slide-in-from-bottom-6 duration-200">
        
        <!-- Modal Header -->
        <div class="px-5 pt-4 pb-3 border-b border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/50">
            <div>
                <h3 class="font-extrabold text-slate-900 text-sm leading-tight flex items-center gap-1.5" id="modal-title-text">
                    <span>Isi Jurnal Staff</span>
                    <span class="text-xs">📝</span>
                </h3>
                <p class="text-[11px] text-primary-700 font-bold mt-0.5" id="modal-jam-sub">
                    08:00 - 09:00 WITA &bull; Aktifitas Kerja
                </p>
            </div>
            <button type="button" onclick="closeModalJurnalStaff()" class="w-8 h-8 rounded-full bg-slate-200/70 text-slate-600 hover:bg-slate-300 flex items-center justify-center press-bounce text-sm">
                ✕
            </button>
        </div>

        <!-- Modal Body (Scrollable Form) -->
        <form id="form-jurnal-staff" onsubmit="submitJurnalStaff(event)" class="p-5 space-y-4 overflow-y-auto flex-1 text-xs" enctype="multipart/form-data">
            
            <!-- Hidden Fields -->
            <input type="hidden" name="jurnal_id" id="inp_jurnal_id" value="">
            <input type="hidden" name="jam_id" id="inp_jam_id" value="">
            <input type="hidden" name="jam_ids" id="inp_jam_ids" value="">
            <input type="hidden" name="jam_rentang" id="inp_jam_rentang" value="">
            <input type="hidden" name="tanggal" id="inp_tanggal" value="<?= e($selectedDate) ?>">

            <!-- Info Bar Tanggal & Jam Terpilih -->
            <div class="p-3 rounded-2xl bg-gradient-to-br from-primary-50 to-emerald-50/50 border border-primary-100 space-y-2 text-[11px]">
                <div class="flex items-center justify-between">
                    <span class="text-slate-600 font-medium">Tanggal Tugas:</span>
                    <span class="font-bold text-primary-900" id="modal-tanggal-badge">
                        <?= e($namaHari . ', ' . date('d M Y', strtotime($selectedDate))) ?>
                    </span>
                </div>
                
                <div id="modal-selected-jam-box" class="pt-2 border-t border-primary-100/70 space-y-1.5">
                    <div class="flex items-center justify-between text-[10.5px]">
                        <span class="text-slate-700 font-bold flex items-center gap-1">
                            <span>🕒</span> Jam Kerja Terpilih:
                        </span>
                        <span class="font-extrabold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full" id="modal-selected-count-badge">
                            1 Sesi
                        </span>
                    </div>
                    <div id="modal-selected-jam-pills" class="flex flex-wrap gap-1">
                        <!-- Dynamic pills injected via JS -->
                    </div>
                    <p class="text-[9.5px] text-slate-500 italic leading-snug">
                        * Tugas dan keterangan yang Anda simpan akan otomatis terisi untuk seluruh jam di atas.
                    </p>
                </div>
            </div>

            <!-- 1. Dropdown Pilihan Tugas / Job Desk Sesuai Divisi -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-800">
                    Pilih Tugas / Job Desk <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select id="modal_select_tugas" name="tugas_id" onchange="handleSelectTugasChange(this)" required
                            class="w-full px-3.5 py-3 rounded-2xl border border-slate-200 bg-white font-medium text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs">
                        <option value="">-- Pilih Tugas Dari Daftar --</option>
                        
                        <?php if (!empty($tugasJabatan)): ?>
                            <optgroup label="⭐ Tugas Jabatan Saya (<?= e($jabatanName) ?>)">
                                <?php foreach ($tugasJabatan as $tj): ?>
                                    <option value="<?= (int)$tj['id'] ?>" data-nama="<?= e($tj['nama_tugas']) ?>" data-deskripsi="<?= e($tj['deskripsi'] ?? '') ?>">
                                        <?= e($tj['nama_tugas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>

                        <?php if (!empty($allTugas)): ?>
                            <?php
                                // Kelompokkan tugas divisi lainnya
                                $otherTugas = array_filter($allTugas, fn($t) => (int)$t['jabatan_id'] !== (int)$jabatanId);
                            ?>
                            <?php if (!empty($otherTugas)): ?>
                                <optgroup label="📂 Tugas Divisi Lainnya">
                                    <?php foreach ($otherTugas as $ot): ?>
                                        <option value="<?= (int)$ot['id'] ?>" data-nama="<?= e($ot['nama_tugas']) ?>" data-deskripsi="<?= e($ot['deskripsi'] ?? '') ?>">
                                            [<?= e($ot['nama_jabatan']) ?>] <?= e($ot['nama_tugas']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endif; ?>

                        <optgroup label="✍️ Input Mandiri">
                            <option value="custom">[+ Tulis Tugas Sendiri / Aktivitas Lainnya]</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            <!-- Input Tugas Custom (Muncul jika pilih Tulis Sendiri) -->
            <div id="container-tugas-custom" class="space-y-1 hidden">
                <label class="block text-xs font-bold text-amber-800">
                    Ketik Nama Tugas / Aktivitas Baru <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="aktivitas_custom" id="inp_aktivitas_custom" placeholder="Contoh: Koordinasi teknis maintenance WiFi & Server"
                       class="w-full px-3.5 py-2.5 rounded-2xl border border-amber-300 bg-amber-50/50 font-medium text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all text-xs">
            </div>

            <!-- 2. Keterangan / Uraian Hasil Kerja -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-800">
                    Keterangan & Hasil Kerja <span class="text-rose-500">*</span>
                </label>
                <textarea name="uraian_hasil" id="inp_uraian_hasil" rows="3" required
                          placeholder="Jelaskan apa yang Anda kerjakan dan hasil/progres yang telah dicapai..."
                          class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-white font-normal text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs leading-relaxed"></textarea>
            </div>

            <!-- 3. Kendala (Opsional) -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">
                    Kendala / Hambatan <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <input type="text" name="kendala" id="inp_kendala" placeholder="Misal: Menunggu material, jaringan sempat down, dll."
                       class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-white font-normal text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs">
            </div>

            <!-- 4. Status Penyelesaian -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-800">Status Pekerjaan:</label>
                <div class="grid grid-cols-3 gap-2" id="group-status-pills">
                    <label class="status-option-label flex items-center justify-center gap-1.5 p-2 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 font-bold text-[11px] cursor-pointer press-bounce">
                        <input type="radio" name="status" value="selesai" checked class="hidden status-radio" onchange="updateStatusPillStyle()">
                        <span>✓ Selesai</span>
                    </label>
                    <label class="status-option-label flex items-center justify-center gap-1.5 p-2 rounded-2xl border border-slate-200 bg-white text-slate-600 font-medium text-[11px] cursor-pointer press-bounce">
                        <input type="radio" name="status" value="proses" class="hidden status-radio" onchange="updateStatusPillStyle()">
                        <span>⏳ Proses</span>
                    </label>
                    <label class="status-option-label flex items-center justify-center gap-1.5 p-2 rounded-2xl border border-slate-200 bg-white text-slate-600 font-medium text-[11px] cursor-pointer press-bounce">
                        <input type="radio" name="status" value="tertunda" class="hidden status-radio" onchange="updateStatusPillStyle()">
                        <span>⏸️ Pending</span>
                    </label>
                </div>
            </div>

            <!-- 5. Foto Bukti Kegiatan (Opsional) -->
            <div class="space-y-1.5 pt-1">
                <label class="block text-xs font-bold text-slate-700 flex items-center justify-between">
                    <span>Foto Bukti Kegiatan <span class="text-slate-400 font-normal">(Opsional)</span></span>
                    <span id="label-foto-info" class="text-[10px] text-slate-400 font-normal">JPG/PNG maks 5MB</span>
                </label>
                <div class="flex items-center gap-2.5">
                    <label class="flex-1 px-3 py-2.5 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 hover:bg-slate-100 flex items-center justify-center gap-2 cursor-pointer transition-all press-bounce">
                        <i data-lucide="camera" class="w-4 h-4 text-slate-500"></i>
                        <span class="text-xs font-semibold text-slate-600" id="btn-foto-text">Ambil Foto / Upload</span>
                        <input type="file" name="foto_bukti" id="inp_foto_bukti" accept="image/*" onchange="handleFotoPreview(this)" class="hidden">
                    </label>
                    <div id="preview-foto-container" class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 hidden">
                        <img id="img-foto-preview" src="" alt="Bukti" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="pt-2 flex items-center gap-2">
                <button type="button" onclick="closeModalJurnalStaff()" class="flex-1 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs press-bounce transition-all">
                    Batal
                </button>
                <button type="submit" id="btn-submit-jurnal-staff" class="flex-2 py-3 rounded-2xl bg-gradient-to-r from-primary-600 to-emerald-600 hover:from-primary-700 hover:to-emerald-700 text-white font-bold text-xs shadow-md shadow-primary-600/30 press-bounce flex items-center justify-center gap-2 transition-all">
                    <span>💾</span>
                    <span id="btn-submit-text">Simpan Jurnal</span>
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    // State management multi-select jam
    let selectedJamMap = {};

    // Toggle pemilihan jam saat kartu diklik
    function toggleSelectJam(jamId) {
        const card = document.getElementById('card-jam-' + jamId);
        if (!card) return;

        if (selectedJamMap[jamId]) {
            delete selectedJamMap[jamId];
            unstyleCard(card);
        } else {
            selectedJamMap[jamId] = {
                jam_id: jamId,
                jam_rentang: card.dataset.jamRentang,
                jam_display: card.dataset.jamDisplay,
                nama_sesi: card.dataset.namaSesi,
                is_filled: card.dataset.isFilled === '1',
                jurnal_id: parseInt(card.dataset.jurnalId) || 0,
                tugas_id: parseInt(card.dataset.tugasId) || 0,
                aktivitas: card.dataset.aktivitas || '',
                uraian_hasil: card.dataset.uraian || '',
                kendala: card.dataset.kendala || '',
                status: card.dataset.status || 'selesai',
                foto_bukti: card.dataset.foto || ''
            };
            styleCardSelected(card);
        }
        updateFloatingBar();
    }

    // Terapkan style aktif terpilih pada kartu
    function styleCardSelected(card) {
        card.classList.add('border-emerald-500', 'ring-2', 'ring-emerald-500/30', 'bg-emerald-50/60');
        card.classList.remove('border-slate-200/90', 'border-emerald-200', 'bg-white', 'bg-emerald-50/25');
        const check = card.querySelector('.jam-check-box');
        if (check) {
            check.className = 'jam-check-box w-5 h-5 rounded-full border border-emerald-500 bg-emerald-500 flex items-center justify-center text-[10px] font-black text-white transition-all shadow-xs';
        }
    }

    // Kembalikan style normal kartu saat unselected
    function unstyleCard(card) {
        card.classList.remove('border-emerald-500', 'ring-2', 'ring-emerald-500/30', 'bg-emerald-50/60');
        const isFilled = card.dataset.isFilled === '1';
        if (isFilled) {
            card.classList.add('border-emerald-200', 'bg-emerald-50/25');
        } else {
            card.classList.add('border-slate-200/90', 'bg-white');
        }
        const check = card.querySelector('.jam-check-box');
        if (check) {
            check.className = 'jam-check-box w-5 h-5 rounded-full border border-slate-300 bg-white flex items-center justify-center text-[10px] font-black text-transparent transition-all shadow-2xs';
        }
    }

    // Pilih seluruh jam
    function selectAllJam() {
        const cards = document.querySelectorAll('.staff-jam-card');
        cards.forEach(card => {
            const jamId = parseInt(card.dataset.jamId);
            if (jamId) {
                selectedJamMap[jamId] = {
                    jam_id: jamId,
                    jam_rentang: card.dataset.jamRentang,
                    jam_display: card.dataset.jamDisplay,
                    nama_sesi: card.dataset.namaSesi,
                    is_filled: card.dataset.isFilled === '1',
                    jurnal_id: parseInt(card.dataset.jurnalId) || 0,
                    tugas_id: parseInt(card.dataset.tugasId) || 0,
                    aktivitas: card.dataset.aktivitas || '',
                    uraian_hasil: card.dataset.uraian || '',
                    kendala: card.dataset.kendala || '',
                    status: card.dataset.status || 'selesai',
                    foto_bukti: card.dataset.foto || ''
                };
                styleCardSelected(card);
            }
        });
        updateFloatingBar();
    }

    // Bersihkan semua pilihan jam
    function clearSelectedJam() {
        const cards = document.querySelectorAll('.staff-jam-card');
        cards.forEach(card => unstyleCard(card));
        selectedJamMap = {};
        updateFloatingBar();
    }

    // Update tampilan tombol melayang (Floating Bar)
    function updateFloatingBar() {
        const bar = document.getElementById('floating-jurnal-bar');
        const countBadge = document.getElementById('floating-count-badge');
        const countText = document.getElementById('floating-count-text');
        const summary = document.getElementById('floating-jam-summary');
        const btnText = document.getElementById('floating-btn-text');
        if (!bar) return;

        const keys = Object.keys(selectedJamMap);
        const count = keys.length;

        if (count > 0) {
            bar.classList.remove('translate-y-32', 'opacity-0', 'pointer-events-none');
            bar.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');

            countBadge.textContent = count;
            countText.textContent = `${count} Jam Terpilih`;
            btnText.textContent = `Isi Jurnal (${count} Jam)`;

            // Rangkum rentang waktu
            const rentangList = keys.map(k => selectedJamMap[k].jam_rentang.replace(/:00/g, ''));
            summary.textContent = rentangList.join(', ');
        } else {
            bar.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
            bar.classList.add('translate-y-32', 'opacity-0', 'pointer-events-none');
        }
    }

    // Buka modal dari tombol melayang
    function openModalFromFloatingBar() {
        const keys = Object.keys(selectedJamMap);
        if (keys.length === 0) {
            if (typeof AndroidUI !== 'undefined') AndroidUI.toast('Pilih minimal 1 jam terlebih dahulu.', 'warning');
            return;
        }

        const items = keys.map(k => selectedJamMap[k]);
        const jamIds = keys;
        const firstItem = items[0];

        // Cek jika ada item yang sudah terisi untuk prefill form
        const filledItem = items.find(it => it.is_filled && it.aktivitas) || firstItem;

        openModalJurnalStaff({
            jam_ids: jamIds,
            jam_id: firstItem.jam_id,
            jam_rentang: firstItem.jam_rentang,
            jam_display: items.map(it => it.jam_rentang).join(', '),
            nama_sesi: `${items.length} Sesi Jam Terpilih`,
            is_filled: items.some(it => it.is_filled),
            jurnal_id: filledItem.jurnal_id || 0,
            tugas_id: filledItem.tugas_id || 0,
            aktivitas: filledItem.aktivitas || '',
            uraian_hasil: filledItem.uraian_hasil || '',
            kendala: filledItem.kendala || '',
            status: filledItem.status || 'selesai',
            foto_bukti: filledItem.foto_bukti || '',
            tanggal: '<?= e($selectedDate) ?>',
            items: items
        });
    }

    // Tab Switching: Jam Kerja vs Riwayat
    function switchStaffTab(tab) {
        const secJam = document.getElementById('section-staff-jam');
        const secRiwayat = document.getElementById('section-staff-riwayat');
        const btnJam = document.getElementById('btn-tab-jam');
        const btnRiwayat = document.getElementById('btn-tab-riwayat');
        const floatingBar = document.getElementById('floating-jurnal-bar');

        if (tab === 'jam') {
            secJam.classList.remove('hidden');
            secRiwayat.classList.add('hidden');
            btnJam.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-700 shadow-sm transition-all flex items-center justify-center gap-1.5';
            btnRiwayat.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5';
            updateFloatingBar();
        } else {
            secJam.classList.add('hidden');
            secRiwayat.classList.remove('hidden');
            btnRiwayat.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-700 shadow-sm transition-all flex items-center justify-center gap-1.5';
            btnJam.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5';
            if (floatingBar) {
                floatingBar.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
                floatingBar.classList.add('translate-y-32', 'opacity-0', 'pointer-events-none');
            }
        }
        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }

    // Buka Modal Pengisian Jurnal Staff
    function openModalJurnalStaff(data) {
        const modal = document.getElementById('modal-jurnal-staff');
        if (!modal) return;

        // Hidden input IDs
        const jamIdsVal = (data.jam_ids && data.jam_ids.length) 
            ? data.jam_ids.join(',') 
            : (data.jam_id ? String(data.jam_id) : '');
        document.getElementById('inp_jam_ids').value = jamIdsVal;
        document.getElementById('inp_jam_id').value = data.jam_id || '';
        document.getElementById('inp_jam_rentang').value = data.jam_rentang || '';
        document.getElementById('inp_jurnal_id').value = data.jurnal_id || '';
        document.getElementById('inp_tanggal').value = data.tanggal || '<?= e($selectedDate) ?>';

        // Render Pills Jam Terpilih di dalam modal
        const pillsContainer = document.getElementById('modal-selected-jam-pills');
        const countBadge = document.getElementById('modal-selected-count-badge');
        const items = data.items || [{ jam_rentang: data.jam_rentang, nama_sesi: data.nama_sesi }];
        
        if (pillsContainer) {
            pillsContainer.innerHTML = '';
            if (countBadge) {
                countBadge.textContent = `${items.length} Sesi Jam`;
            }
            items.forEach(it => {
                const pill = document.createElement('span');
                pill.className = 'px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold flex items-center gap-1';
                pill.innerHTML = `<span>🕒</span> <span>${(it.jam_rentang || data.jam_rentang).replace(/:00/g, '')}</span>`;
                pillsContainer.appendChild(pill);
            });
        }

        // Label Jam & Sesi di Header Modal
        const isMultiple = items.length > 1;
        document.getElementById('modal-jam-sub').textContent = isMultiple
            ? `${items.length} Sesi Jam Terpilih Sekaligus`
            : `${data.jam_display || data.jam_rentang} • ${data.nama_sesi || 'Sesi Kerja'}`;
            
        document.getElementById('modal-title-text').innerHTML = isMultiple
            ? `<span>Isi Jurnal (${items.length} Jam Sekaligus)</span> <span class="text-xs">📝</span>`
            : (data.is_filled ? `<span>Edit Jurnal Staff</span> <span class="text-xs">✏️</span>` : `<span>Isi Jurnal Staff</span> <span class="text-xs">📝</span>`);

        // Prepopulate data form
        const selectTugas = document.getElementById('modal_select_tugas');
        const inpCustom = document.getElementById('inp_aktivitas_custom');
        const containerCustom = document.getElementById('container-tugas-custom');
        const inpUraian = document.getElementById('inp_uraian_hasil');
        const inpKendala = document.getElementById('inp_kendala');
        const previewFoto = document.getElementById('preview-foto-container');
        const imgFoto = document.getElementById('img-foto-preview');
        const btnFotoText = document.getElementById('btn-foto-text');

        inpUraian.value = data.uraian_hasil || '';
        inpKendala.value = data.kendala || '';

        // Reset foto preview
        previewFoto.classList.add('hidden');
        imgFoto.src = '';
        btnFotoText.textContent = 'Ambil Foto / Upload';
        if (data.foto_bukti) {
            previewFoto.classList.remove('hidden');
            imgFoto.src = '<?= asset('') ?>' + data.foto_bukti.replace(/^\/+/, '');
            btnFotoText.textContent = 'Ganti Foto Bukti';
        }

        // Setup Dropdown Tugas Sesuai Divisi
        let matchedOption = false;
        if (data.tugas_id) {
            for (let i = 0; i < selectTugas.options.length; i++) {
                if (selectTugas.options[i].value == data.tugas_id) {
                    selectTugas.selectedIndex = i;
                    matchedOption = true;
                    break;
                }
            }
        }
        if (!matchedOption && data.aktivitas) {
            for (let i = 0; i < selectTugas.options.length; i++) {
                if (selectTugas.options[i].dataset.nama && selectTugas.options[i].dataset.nama.trim().toLowerCase() === data.aktivitas.trim().toLowerCase()) {
                    selectTugas.selectedIndex = i;
                    matchedOption = true;
                    break;
                }
            }
        }

        if (!matchedOption && data.aktivitas) {
            selectTugas.value = 'custom';
            containerCustom.classList.remove('hidden');
            inpCustom.value = data.aktivitas;
            inpCustom.required = true;
        } else if (!matchedOption) {
            selectTugas.value = '';
            containerCustom.classList.add('hidden');
            inpCustom.value = '';
            inpCustom.required = false;
        } else {
            containerCustom.classList.add('hidden');
            inpCustom.value = '';
            inpCustom.required = false;
        }

        // Set radio status
        const statusRadios = document.querySelectorAll('.status-radio');
        const currentStatus = (data.status || 'selesai').toLowerCase();
        statusRadios.forEach(radio => {
            radio.checked = (radio.value === currentStatus);
        });
        updateStatusPillStyle();

        // Tampilkan modal
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }

    // Tutup Modal
    function closeModalJurnalStaff() {
        const modal = document.getElementById('modal-jurnal-staff');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // Event Handler saat dropdown tugas diubah
    function handleSelectTugasChange(selectEl) {
        const containerCustom = document.getElementById('container-tugas-custom');
        const inpCustom = document.getElementById('inp_aktivitas_custom');
        const inpUraian = document.getElementById('inp_uraian_hasil');

        if (selectEl.value === 'custom') {
            containerCustom.classList.remove('hidden');
            inpCustom.required = true;
            inpCustom.focus();
        } else {
            containerCustom.classList.add('hidden');
            inpCustom.required = false;
            inpCustom.value = '';

            // Jika ada deskripsi bawaan tugas dan uraian masih kosong, bantu berikan template ringkas
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.deskripsi && (!inpUraian.value || inpUraian.value.trim() === '')) {
                inpUraian.value = `Melaksanakan ${selectedOpt.dataset.nama}: ${selectedOpt.dataset.deskripsi}.`;
            }
        }
    }

    // Update style chip status radio
    function updateStatusPillStyle() {
        const labels = document.querySelectorAll('.status-option-label');
        labels.forEach(lbl => {
            const radio = lbl.querySelector('.status-radio');
            if (radio && radio.checked) {
                if (radio.value === 'selesai') {
                    lbl.className = 'status-option-label flex items-center justify-center gap-1.5 p-2 rounded-2xl border border-emerald-300 bg-emerald-100 text-emerald-900 font-bold text-[11px] shadow-xs press-bounce cursor-pointer';
                } else if (radio.value === 'proses') {
                    lbl.className = 'status-option-label flex items-center justify-center gap-1.5 p-2 rounded-2xl border border-amber-300 bg-amber-100 text-amber-900 font-bold text-[11px] shadow-xs press-bounce cursor-pointer';
                } else {
                    lbl.className = 'status-option-label flex items-center justify-center gap-1.5 p-2 rounded-2xl border border-slate-300 bg-slate-200 text-slate-800 font-bold text-[11px] shadow-xs press-bounce cursor-pointer';
                }
            } else {
                lbl.className = 'status-option-label flex items-center justify-center gap-1.5 p-2 rounded-2xl border border-slate-200 bg-white text-slate-600 font-medium text-[11px] press-bounce cursor-pointer hover:bg-slate-50';
            }
        });
    }

    // Preview Foto Bukti
    function handleFotoPreview(input) {
        const file = input.files && input.files[0];
        const previewContainer = document.getElementById('preview-foto-container');
        const imgPreview = document.getElementById('img-foto-preview');
        const btnText = document.getElementById('btn-foto-text');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imgPreview.src = e.target.result;
                previewContainer.classList.remove('hidden');
                btnText.textContent = file.name.substring(0, 16) + '...';
            };
            reader.readAsDataURL(file);
        }
    }

    // Submit AJAX Form Jurnal Staff
    async function submitJurnalStaff(e) {
        e.preventDefault();
        const form = document.getElementById('form-jurnal-staff');
        const btnSubmit = document.getElementById('btn-submit-jurnal-staff');
        const btnText = document.getElementById('btn-submit-text');

        const selectTugas = document.getElementById('modal_select_tugas');
        const inpCustom = document.getElementById('inp_aktivitas_custom');

        let namaAktivitas = '';
        let tugasId = '';

        if (selectTugas.value === 'custom') {
            namaAktivitas = inpCustom.value.trim();
            if (!namaAktivitas) {
                if (typeof AndroidUI !== 'undefined') AndroidUI.toast('Ketik nama tugas / aktivitas Anda.', 'warning');
                inpCustom.focus();
                return;
            }
        } else if (selectTugas.value) {
            tugasId = selectTugas.value;
            const opt = selectTugas.options[selectTugas.selectedIndex];
            namaAktivitas = opt.dataset.nama || opt.text;
        } else {
            if (typeof AndroidUI !== 'undefined') AndroidUI.toast('Silakan pilih tugas terlebih dahulu.', 'warning');
            selectTugas.focus();
            return;
        }

        const formData = new FormData(form);
        formData.set('aktivitas', namaAktivitas);
        formData.set('tugas_id', tugasId);

        // Pastikan jam_ids dikirim
        const jamIdsVal = document.getElementById('inp_jam_ids').value;
        if (jamIdsVal) {
            formData.set('jam_ids', jamIdsVal);
        }

        // State loading
        btnSubmit.disabled = true;
        btnText.textContent = 'Menyimpan...';

        try {
            const targetUrl = '<?= url('mobile/jurnal-staff/simpan') ?>';
            const res = await fetch(targetUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const json = await res.json();

            if (json && json.success) {
                if (typeof AndroidUI !== 'undefined') {
                    AndroidUI.toast(json.message || 'Jurnal berhasil disimpan!', 'success');
                } else {
                    alert(json.message || 'Jurnal berhasil disimpan!');
                }
                closeModalJurnalStaff();
                // Reload halaman dengan tanggal yang sama agar semua slot jam terisi langsung muncul
                setTimeout(() => {
                    location.reload();
                }, 400);
            } else {
                const msg = (json && json.message) || 'Gagal menyimpan jurnal kerja.';
                if (typeof AndroidUI !== 'undefined') {
                    AndroidUI.alert({
                        title: 'Gagal Menyimpan',
                        subtitle: 'Validasi Jurnal Staff',
                        message: msg,
                        icon: '⚠️',
                        iconBg: 'bg-rose-100 text-rose-700'
                    });
                } else {
                    alert(msg);
                }
            }
        } catch (err) {
            console.error('Error submit jurnal staff:', err);
            if (typeof AndroidUI !== 'undefined') {
                AndroidUI.toast('Terjadi kesalahan koneksi saat menyimpan.', 'danger');
            } else {
                alert('Terjadi kesalahan jaringan.');
            }
        } finally {
            btnSubmit.disabled = false;
            btnText.textContent = 'Simpan Jurnal';
        }
    }
</script>
