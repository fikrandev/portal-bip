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
    <!-- SECTION 1: DAFTAR SLOT JAM KERJA                         -->
    <!-- ======================================================== -->
    <div id="section-staff-jam" class="space-y-3">
        
        <div class="flex items-center justify-between px-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                <span>Pilih Jam untuk Mengisi:</span>
            </span>
            <span class="text-[10px] text-slate-400 font-medium">Ketuk kartu jam di bawah</span>
        </div>

        <?php if (!empty($jamList)): ?>
            <div class="space-y-2.5">
                <?php foreach ($jamList as $idx => $jam): ?>
                    <?php
                        $jamId = (int)$jam['id'];
                        $rawMulai = substr((string)$jam['jam_mulai'], 0, 5);
                        $rawSelesai = substr((string)$jam['jam_selesai'], 0, 5);
                        $rentangStr = $rawMulai . ' - ' . $rawSelesai;
                        $rentangDisplay = str_replace(':', '.', $rawMulai) . ' - ' . str_replace(':', '.', $rawSelesai) . ' WITA';

                        // Cek apakah sudah diisi
                        $existing = $journalByJamId[$jamId] ?? ($journalByJamId[$rentangStr] ?? null);
                        $isFilled = !empty($existing);
                        
                        $tipe = strtolower($jam['tipe'] ?? 'aktifitas');
                        $badgeBg = 'bg-slate-100 text-slate-700';
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
                    
                    <div onclick="openModalJurnalStaff(<?= htmlspecialchars(json_encode([
                            'jam_id' => $jamId,
                            'jam_rentang' => $rentangStr,
                            'jam_display' => $rentangDisplay,
                            'nama_sesi' => $jam['nama_sesi'],
                            'tipe' => $tipe,
                            'is_filled' => $isFilled,
                            'jurnal_id' => $isFilled ? (int)$existing['id'] : 0,
                            'tugas_id' => $isFilled ? (int)($existing['tugas_id'] ?? 0) : 0,
                            'aktivitas' => $isFilled ? (string)$existing['aktivitas'] : '',
                            'uraian_hasil' => $isFilled ? (string)$existing['uraian_hasil'] : '',
                            'kendala' => $isFilled ? (string)($existing['kendala'] ?? '') : '',
                            'status' => $isFilled ? (string)($existing['status'] ?? 'selesai') : 'selesai',
                            'foto_bukti' => $isFilled ? (string)($existing['foto_bukti'] ?? '') : '',
                            'tanggal' => $selectedDate
                        ]), ENT_QUOTES, 'UTF-8') ?>)"
                         class="staff-jam-card bg-white rounded-3xl p-3.5 shadow-sm border transition-all cursor-pointer press-bounce <?= $isFilled ? 'border-emerald-200 bg-emerald-50/20 hover:border-emerald-300' : 'border-slate-100 hover:border-primary-300 hover:shadow-md' ?>">
                        
                        <div class="flex items-start gap-3">
                            <!-- Left: Jam Indicator Badge -->
                            <div class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center shrink-0 border <?= $isFilled ? 'bg-emerald-500 text-white border-emerald-400 shadow-sm shadow-emerald-500/20' : 'bg-slate-50 text-slate-700 border-slate-200/80' ?>">
                                <span class="text-sm"><?= $isFilled ? '✓' : $iconEmoji ?></span>
                                <span class="text-[8.5px] font-black leading-none mt-0.5 <?= $isFilled ? 'text-white' : 'text-slate-500' ?>">Sesi <?= $idx + 1 ?></span>
                            </div>

                            <!-- Middle: Jam, Sesi & Keterangan -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-1.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-extrabold text-slate-900 text-xs tracking-tight">
                                            <?= e($rentangDisplay) ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Status Pill -->
                                    <?php if ($isFilled): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1">
                                            <span>✓</span> Terisi
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-slate-100 text-slate-500 border border-slate-200/80">
                                            Belum Diisi
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-[11px] font-semibold text-slate-700 mt-0.5 truncate">
                                    <?= e($jam['nama_sesi']) ?>
                                </p>

                                <?php if ($isFilled): ?>
                                    <!-- Detail jika sudah diisi -->
                                    <div class="mt-2 p-2 rounded-xl bg-white border border-emerald-100 space-y-1 shadow-2xs">
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="text-emerald-700 font-bold truncate text-[11px]">
                                                🎯 <?= e($existing['aktivitas']) ?>
                                            </span>
                                        </div>
                                        <p class="text-[10px] text-slate-600 line-clamp-2 leading-relaxed">
                                            <?= e($existing['uraian_hasil']) ?>
                                        </p>
                                        <?php if (!empty($existing['kendala'])): ?>
                                            <p class="text-[9.5px] text-amber-700 font-medium truncate">
                                                ⚠️ Kendala: <?= e($existing['kendala']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <!-- Panduan jika belum diisi -->
                                    <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                                        <span>👉</span>
                                        <span class="hover:underline text-primary-600 font-medium">Ketuk untuk pilih tugas & isi uraian</span>
                                    </p>
                                <?php endif; ?>
                            </div>
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
                    08:00 - 09:00 WITA &bull; Aktifitas Kerja Sesi 1
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
            <input type="hidden" name="jam_rentang" id="inp_jam_rentang" value="">
            <input type="hidden" name="tanggal" id="inp_tanggal" value="<?= e($selectedDate) ?>">

            <!-- Info Bar Tanggal -->
            <div class="p-2.5 rounded-2xl bg-primary-50/70 border border-primary-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-600 font-medium">Tanggal Tugas:</span>
                <span class="font-bold text-primary-900" id="modal-tanggal-badge">
                    <?= e($namaHari . ', ' . date('d M Y', strtotime($selectedDate))) ?>
                </span>
            </div>

            <!-- 1. Dropdown Pilihan Tugas / Job Desk -->
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
                <input type="text" name="aktivitas_custom" id="inp_aktivitas_custom" placeholder="Contoh: Koordinasi pengadaan toner printer TU"
                       class="w-full px-3.5 py-2.5 rounded-2xl border border-amber-300 bg-amber-50/50 font-medium text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all text-xs">
            </div>

            <!-- 2. Keterangan / Uraian Hasil Kerja -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-800">
                    Keterangan & Hasil Kerja <span class="text-rose-500">*</span>
                </label>
                <textarea name="uraian_hasil" id="inp_uraian_hasil" rows="3" required
                          placeholder="Jelaskan apa yang Anda kerjakan pada jam ini dan hasil/progres yang telah dicapai..."
                          class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-white font-normal text-slate-800 focus:outline-hidden focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs leading-relaxed"></textarea>
            </div>

            <!-- 3. Kendala (Opsional) -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700">
                    Kendala / Hambatan <span class="text-slate-400 font-normal">(Opsional)</span>
                </label>
                <input type="text" name="kendala" id="inp_kendala" placeholder="Misal: Menunggu konfirmasi vendor, jaringan sempat down, dll."
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
    // Tab Switching: Jam Kerja vs Riwayat
    function switchStaffTab(tab) {
        const secJam = document.getElementById('section-staff-jam');
        const secRiwayat = document.getElementById('section-staff-riwayat');
        const btnJam = document.getElementById('btn-tab-jam');
        const btnRiwayat = document.getElementById('btn-tab-riwayat');

        if (tab === 'jam') {
            secJam.classList.remove('hidden');
            secRiwayat.classList.add('hidden');
            btnJam.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-700 shadow-sm transition-all flex items-center justify-center gap-1.5';
            btnRiwayat.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5';
        } else {
            secJam.classList.add('hidden');
            secRiwayat.classList.remove('hidden');
            btnRiwayat.className = 'flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-700 shadow-sm transition-all flex items-center justify-center gap-1.5';
            btnJam.className = 'flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5';
        }
        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }

    // Buka Modal Pengisian Jurnal Staff
    function openModalJurnalStaff(data) {
        const modal = document.getElementById('modal-jurnal-staff');
        if (!modal) return;

        document.getElementById('inp_jam_id').value = data.jam_id || '';
        document.getElementById('inp_jam_rentang').value = data.jam_rentang || '';
        document.getElementById('inp_jurnal_id').value = data.jurnal_id || '';
        document.getElementById('inp_tanggal').value = data.tanggal || '<?= e($selectedDate) ?>';

        // Label Jam & Sesi
        const subText = `${data.jam_display || data.jam_rentang} • ${data.nama_sesi || 'Sesi Kerja'}`;
        document.getElementById('modal-jam-sub').textContent = subText;
        document.getElementById('modal-title-text').innerHTML = data.is_filled 
            ? `<span>Edit Jurnal Staff</span> <span class="text-xs">✏️</span>` 
            : `<span>Isi Jurnal Staff</span> <span class="text-xs">📝</span>`;

        // Prepopulate data jika sedang edit
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

        // Setup Dropdown Tugas
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
            // Gunakan custom task
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
                // Reload halaman dengan tanggal yang sama
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
