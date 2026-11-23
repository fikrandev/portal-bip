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
$isHoliday = !empty($isHoliday);
$canFill = isset($canFill) ? (bool)$canFill : ($isToday && !$isHoliday);
$totalLiburPeriode = (int)($totalLiburPeriode ?? 0);
?>

<!-- Header with Back Button -->
<div class="px-3.5 sm:px-4 pt-3.5 pb-3 flex items-center justify-between bg-white border-b border-slate-100 sticky top-0 z-30 shadow-xs max-w-full overflow-hidden">
    <div class="flex items-center gap-2.5 min-w-0 flex-1">
        <a href="<?= url('mobile') ?>" class="w-9 h-9 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center press-bounce shrink-0">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div class="min-w-0 flex-1">
            <h2 class="font-bold text-slate-900 text-sm sm:text-base leading-tight flex items-center gap-1.5 truncate">
                <span class="truncate">Jurnal Kerja Staff</span>
                <span class="text-xs shrink-0">💼</span>
            </h2>
            <p class="text-[10px] text-slate-400 font-medium truncate">
                <?= e($jabatanName) ?> &bull; Support System
            </p>
        </div>
    </div>
    <div class="flex items-center gap-1.5 shrink-0 ml-2">
        <a href="<?= url('mobile/rekap-absen') ?>" class="w-9 h-9 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center press-bounce shadow-xs" title="Rekap Kehadiran">
            <i data-lucide="calendar" class="w-4 h-4"></i>
        </a>
    </div>
</div>

<div class="p-3.5 sm:p-4 space-y-3.5 sm:space-y-4 max-w-full overflow-x-hidden min-w-0">

    <!-- 0. Progres Ketuntasan Jurnal Berdasarkan Periode (Mirip Rekap Absen) -->
    <div class="relative overflow-hidden bg-gradient-to-br from-primary-700 via-primary-800 to-indigo-900 rounded-3xl p-4 text-white shadow-xl shadow-primary-900/20 border border-primary-500/30">
        <!-- Background Glow Accent -->
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-indigo-400/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 space-y-3">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-white/15 flex items-center justify-center text-xs">📊</span>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-primary-200">Progres Jurnal Periode</span>
                        <p class="text-[11px] font-bold text-white/90">
                            <?= date('d M', strtotime($periodeAwal)) ?> - <?= date('d M Y', strtotime($periodeAkhir)) ?>
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black <?= $persenJurnalPeriode >= 100 ? 'bg-emerald-400 text-emerald-950 shadow-xs' : 'bg-white/20 text-white' ?>">
                        <?= $persenJurnalPeriode >= 100 ? '✅ 100% Tuntas' : $persenJurnalPeriode . '%' ?>
                    </span>
                </div>
            </div>

            <!-- Big Percentage & Progress Bar -->
            <div class="space-y-1.5 pt-1">
                <div class="flex items-end justify-between">
                    <div>
                        <span class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-none">
                            <?= $persenJurnalPeriode ?>%
                        </span>
                        <span class="text-[11px] text-primary-200 font-semibold ml-1">Keterisian Periode</span>
                    </div>
                    <span class="text-[11px] font-bold text-primary-100">
                        <?= $sesiTerisiPeriode ?> / <?= $targetSesiPeriode ?> Sesi
                    </span>
                </div>
                <div class="w-full h-2.5 rounded-full bg-white/20 overflow-hidden p-0.5 backdrop-blur-xs">
                    <div class="h-full rounded-full <?= $persenJurnalPeriode >= 100 ? 'bg-gradient-to-r from-emerald-300 to-emerald-400' : 'bg-gradient-to-r from-sky-300 to-white' ?> transition-all duration-700 shadow-sm" style="width: <?= min(100, $persenJurnalPeriode) ?>%"></div>
                </div>
            </div>

            <!-- 2 Mini Stats Badges -->
            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-white/10 text-[10.5px]">
                <div class="bg-white/10 rounded-xl p-2 flex items-center gap-2">
                    <span class="text-base">📅</span>
                    <div class="min-w-0">
                        <p class="text-[9px] text-primary-200 font-semibold leading-tight">Hari Kerja Aktif</p>
                        <p class="font-extrabold text-white truncate">
                            <?php if ($totalHariKerjaPeriode > 0): ?>
                                <?= $hariTerisiPeriode ?> / <?= $totalHariKerjaPeriode ?> Hari
                            <?php else: ?>
                                Hari Libur
                            <?php endif; ?>
                        </p>
                        <?php if ($totalLiburPeriode > 0): ?>
                            <p class="text-[8.5px] text-primary-200/90 truncate">(<?= $totalLiburPeriode ?> hari libur dikecualikan)</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="bg-white/10 rounded-xl p-2 flex items-center gap-2">
                    <span class="text-base">🎯</span>
                    <div class="min-w-0">
                        <p class="text-[9px] text-primary-200 font-semibold leading-tight">Status Periode</p>
                        <p class="font-extrabold text-white truncate">
                            <?= $totalHariKerjaPeriode === 0 ? 'Libur' : ($persenJurnalPeriode >= 100 ? 'Sempurna' : ($persenJurnalPeriode >= 80 ? 'Optimal' : 'Perlu Diisi')) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 1. Date Selector & Daily Status Card -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-3.5 sm:p-4 text-slate-800 shadow-sm space-y-3 max-w-full overflow-hidden min-w-0">
        <!-- Tanggal Bar -->
        <div class="flex flex-wrap items-center justify-between gap-2 min-w-0">
            <div class="min-w-0">
                <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Tanggal Jurnal:</p>
                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap min-w-0">
                    <span class="text-xs sm:text-sm font-black text-slate-900 truncate max-w-[160px] sm:max-w-none">
                        <?php
                            $ts = strtotime($selectedDate);
                            $dayNames = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                            $namaHari = $dayNames[date('l', $ts)] ?? 'Senin';
                            echo e($namaHari . ', ' . date('d M Y', $ts));
                        ?>
                    </span>
                    <?php if ($isHoliday): ?>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-sky-100 text-sky-800 border border-sky-200 shrink-0">
                            🏖️ <?= (!empty($liburInfo['is_sunday']) && empty($liburInfo['is_custom_libur'])) ? 'Hari Minggu (Libur)' : 'Libur: ' . e($liburInfo['nama_libur'] ?? 'Hari Libur') ?>
                        </span>
                    <?php elseif ($isToday): ?>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">🟢 Hari Ini (Bisa Diisi)</span>
                    <?php elseif ($isPastDate): ?>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0">🔒 Tanggal Lampau (Terkunci)</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200 shrink-0">⏳ Mendatang (Terkunci)</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Date Changer -->
            <div class="flex items-center gap-1 shrink-0">
                <?php
                    $prevDate = date('Y-m-d', strtotime('-1 day', $ts));
                    $nextDate = date('Y-m-d', strtotime('+1 day', $ts));
                ?>
                <a href="<?= url('mobile/jurnal') ?>?tanggal=<?= $prevDate ?>" class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center press-bounce text-xs" title="Hari Sebelumnya">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </a>
                <?php if (!$isToday): ?>
                    <a href="<?= url('mobile/jurnal') ?>" class="px-2.5 py-1 rounded-xl bg-primary-600 text-white text-[10px] font-bold press-bounce shadow-xs">
                        Hari Ini
                    </a>
                <?php endif; ?>
                <a href="<?= url('mobile/jurnal') ?>?tanggal=<?= $nextDate ?>" class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center press-bounce text-xs" title="Hari Berikutnya">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </a>
                <label class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center press-bounce text-xs cursor-pointer relative" title="Pilih Tanggal">
                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    <input type="date" value="<?= e($selectedDate) ?>" onchange="location.href='<?= url('mobile/jurnal') ?>?tanggal=' + this.value" class="absolute inset-0 opacity-0 cursor-pointer">
                </label>
            </div>
        </div>

        <?php if ($isHoliday): ?>
            <div class="p-3 rounded-2xl bg-sky-50/90 border border-sky-200 text-sky-900 text-xs flex items-start gap-2.5">
                <span class="text-base shrink-0">🏖️</span>
                <p class="text-[11px] leading-relaxed">
                    <strong><?= e($liburInfo['nama_libur'] ?? 'Hari Libur') ?>:</strong> Tidak ada pengisian jurnal kerja pada tanggal ini dan tidak mengurangi perhitungan persentase ketuntasan periode.
                </p>
            </div>
        <?php else: ?>
            <?php if (!$isToday): ?>
                <div class="p-2.5 rounded-2xl bg-amber-50/90 border border-amber-200 text-amber-900 text-xs flex items-start gap-2">
                    <span class="text-sm shrink-0">🔒</span>
                    <p class="text-[11px] leading-relaxed">
                        <strong>Perhatian:</strong> Pengisian dan pengeditan jurnal kerja hanya diizinkan pada <strong>Hari Ini</strong>. Jurnal tanggal lampau tidak dapat diubah (hanya dapat dilihat rinciannya).
                    </p>
                </div>
            <?php endif; ?>

            <!-- Progress Bar Pengisian Harian -->
            <div class="bg-slate-50 rounded-2xl p-2.5 border border-slate-100 space-y-1.5">
                <div class="flex items-center justify-between text-xs gap-1.5 flex-wrap">
                    <span class="font-medium text-slate-500 text-[10.5px]">Sesi Pada Tanggal Ini:</span>
                    <span class="font-black text-[11px] <?= $totalTerisi === $totalSesi && $totalSesi > 0 ? 'text-emerald-700' : 'text-slate-800' ?>">
                        <?= $totalTerisi ?> / <?= $totalSesi ?> Sesi Terisi (<?= $persenTerisi ?>%)
                    </span>
                </div>
                <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full rounded-full <?= $persenTerisi >= 100 ? 'bg-emerald-500' : 'bg-primary-600' ?> transition-all duration-500" style="width: <?= min(100, $persenTerisi) ?>%"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- 2. Tab Switcher: Sesi Jam vs Riwayat Jurnal -->
    <div class="flex p-1 bg-slate-200/70 rounded-2xl gap-1 max-w-full min-w-0">
        <button type="button" onclick="switchStaffTab('jam')" id="btn-tab-jam" class="flex-1 py-2 text-xs font-bold rounded-xl bg-white text-primary-700 shadow-sm transition-all flex items-center justify-center gap-1.5 truncate">
            <span>🕒</span>
            <span class="truncate">Jadwal Jam Kerja</span>
        </button>
        <button type="button" onclick="switchStaffTab('riwayat')" id="btn-tab-riwayat" class="flex-1 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5 truncate">
            <span>📋</span>
            <span class="truncate">Riwayat (<?= count($recentStaffJournals) ?>)</span>
        </button>
    </div>

    <!-- ======================================================== -->
    <!-- SECTION 1: DAFTAR SLOT JAM KERJA (GRID 3 KOLOM)          -->
    <!-- ======================================================== -->
    <div id="section-staff-jam" class="space-y-3 pb-24 max-w-full overflow-x-hidden min-w-0">
        
        <div class="flex items-center justify-between px-1">
            <div>
                <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                    <span>Pilih Jam Kerja:</span>
                </span>
                <p class="text-[10px] text-slate-400 font-medium">
                    <?= $isHoliday ? 'Tanggal Libur (Tidak Ada Pengisian)' : ($canFill ? 'Bisa klik beberapa jam sekaligus' : 'Mode Terkunci (Hanya Lihat Rincian)') ?>
                </p>
            </div>
            
            <div class="flex items-center gap-1 text-[10px]">
                <?php if ($canFill): ?>
                    <button type="button" onclick="selectAllJam()" class="px-2.5 py-1 rounded-xl bg-primary-50 text-primary-700 font-bold hover:bg-primary-100 transition-all press-bounce border border-primary-100">
                        Pilih Semua
                    </button>
                    <button type="button" onclick="clearSelectedJam()" class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 transition-all press-bounce border border-slate-200">
                        Reset
                    </button>
                <?php elseif ($isHoliday): ?>
                    <span class="px-2.5 py-1 rounded-xl bg-sky-50 text-sky-700 border border-sky-200 font-bold text-[9.5px] flex items-center gap-1">
                        <span>🏖️</span> Libur
                    </span>
                <?php else: ?>
                    <span class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 font-bold text-[9.5px] flex items-center gap-1">
                        <span>🔒</span> Terkunci
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isHoliday): ?>
            <div class="bg-white rounded-3xl p-6 text-center space-y-3 border border-sky-100 shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-sky-50 border border-sky-200 flex items-center justify-center mx-auto text-3xl">
                    🏖️
                </div>
                <div class="space-y-1">
                    <h4 class="font-extrabold text-slate-800 text-sm">
                        <?= (!empty($liburInfo['is_sunday']) && empty($liburInfo['is_custom_libur'])) ? 'Hari Minggu — Libur Rutin' : 'Hari Libur: ' . e($liburInfo['nama_libur'] ?? 'Hari Libur') ?>
                    </h4>
                    <p class="text-xs text-slate-500 max-w-xs mx-auto leading-relaxed">
                        Tidak ada pengisian jurnal kerja pada tanggal ini. Hari libur otomatis dikecualikan agar perhitungan persentase ketuntasan jurnal tetap akurat.
                    </p>
                    <?php if (!empty($liburInfo['keterangan']) && !empty($liburInfo['is_custom_libur'])): ?>
                        <p class="text-[11px] text-sky-700 font-semibold pt-1">
                            Catatan: <?= e($liburInfo['keterangan']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($journalsOnDate)): ?>
                <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 space-y-2">
                    <p class="text-[11px] font-bold text-slate-600">Catatan Tersimpan di Tanggal Ini:</p>
                    <?php foreach ($journalsOnDate as $jo): ?>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[11px]">
                            <div class="font-bold text-slate-800"><?= e($jo['jam_rentang']) ?> &bull; <?= e($jo['aktivitas']) ?></div>
                            <div class="text-slate-500 mt-0.5"><?= e($jo['uraian_hasil']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php elseif (!empty($jamList)): ?>
            <div class="grid grid-cols-3 gap-2 sm:gap-2.5 max-w-full min-w-0" id="grid-jam-staff">
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
                         onclick="<?= $canFill ? 'toggleSelectJam(' . $jamId . ')' : ($isFilled ? 'openStaffReadOnlyFromCard(' . $jamId . ')' : 'showStaffLockedNotice()') ?>"
                         class="staff-jam-card group relative rounded-2xl p-2 sm:p-2.5 border transition-all duration-200 cursor-pointer flex flex-col justify-between select-none press-bounce min-w-0 overflow-hidden <?= $isFilled ? 'border-emerald-200 bg-emerald-50/25 hover:border-emerald-300' : 'border-slate-200/90 bg-white hover:border-primary-300 shadow-2xs' ?> <?= !$canFill ? 'opacity-90' : '' ?>"
                         data-jam-id="<?= $jamId ?>"
                         data-jam-rentang="<?= e($rentangStr) ?>"
                         data-jam-display="<?= e($rentangDisplay) ?> WITA"
                         data-nama-sesi="<?= e($jam['nama_sesi']) ?>"
                         data-is-filled="<?= $isFilled ? '1' : '0' ?>"
                         data-jurnal-id="<?= $isFilled ? (int)$existing['id'] : 0 ?>"
                         data-tugas-id="<?= $isFilled ? (int)($existing['tugas_id'] ?? 0) : 0 ?>"
                         data-tugas-ids="<?= $isFilled ? htmlspecialchars((string)($existing['tugas_ids'] ?? ($existing['tugas_id'] ?? '')), ENT_QUOTES) : '' ?>"
                         data-aktivitas="<?= $isFilled ? htmlspecialchars((string)$existing['aktivitas'], ENT_QUOTES) : '' ?>"
                         data-uraian="<?= $isFilled ? htmlspecialchars((string)$existing['uraian_hasil'], ENT_QUOTES) : '' ?>"
                         data-kendala="<?= $isFilled ? htmlspecialchars((string)($existing['kendala'] ?? ''), ENT_QUOTES) : '' ?>"
                         data-status="<?= $isFilled ? htmlspecialchars((string)($existing['status'] ?? 'selesai'), ENT_QUOTES) : 'selesai' ?>"
                         data-foto="<?= $isFilled ? htmlspecialchars((string)($existing['foto_bukti'] ?? ''), ENT_QUOTES) : '' ?>">
                        
                        <!-- Top Bar: Sesi Badge + Checkbox Circle -->
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded-lg border <?= $badgeBg ?> leading-none flex items-center gap-0.5 shrink-0">
                                <span><?= $iconEmoji ?></span>
                                <span>S<?= $idx + 1 ?></span>
                            </span>
                            <div class="jam-check-box w-5 h-5 rounded-full border border-slate-300 bg-white flex items-center justify-center text-[10px] font-black text-transparent transition-all shadow-2xs shrink-0">
                                ✓
                            </div>
                        </div>

                        <!-- Jam & Nama Sesi -->
                        <div class="my-1.5 sm:my-2 min-w-0 overflow-hidden">
                            <div class="text-[10px] sm:text-[11px] font-black text-slate-900 tracking-tight leading-none truncate">
                                <?= e($rentangDisplay) ?>
                            </div>
                            <p class="text-[9px] sm:text-[9.5px] font-semibold text-slate-600 truncate mt-1 leading-tight" title="<?= e($jam['nama_sesi']) ?>">
                                <?= e($jam['nama_sesi']) ?>
                            </p>
                        </div>

                        <!-- Status Terisi / Kosong -->
                        <div class="pt-1.5 border-t <?= $isFilled ? 'border-emerald-100' : 'border-slate-100' ?>">
                            <?php if ($isFilled): ?>
                                <?php $cardAktParts = JurnalStaffModel::splitAktivitasList($existing['aktivitas'] ?? ''); ?>
                                <div class="flex flex-col gap-0.5">
                                    <div class="flex items-center gap-1 flex-wrap">
                                        <span class="inline-flex items-center gap-0.5 text-[8.5px] font-bold text-emerald-800 bg-emerald-100/90 px-1.5 py-0.5 rounded-md w-fit">
                                            ✓ Terisi
                                        </span>
                                        <?php if (count($cardAktParts) > 1): ?>
                                            <span class="inline-flex items-center text-[8px] font-extrabold text-primary-700 bg-primary-50 border border-primary-200 px-1 py-0.5 rounded-md">
                                                <?= count($cardAktParts) ?> Tugas
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-[8.5px] text-slate-500 truncate font-medium mt-0.5" title="<?= e($existing['aktivitas']) ?>">
                                        <?= e($cardAktParts[0] ?? $existing['aktivitas']) ?>
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
    <div id="section-staff-riwayat" class="hidden space-y-3 max-w-full overflow-x-hidden min-w-0">
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
                        $riwayatAktParts = JurnalStaffModel::splitAktivitasList($rj['aktivitas'] ?? '');
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
                            <?php if (count($riwayatAktParts) > 1): ?>
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-primary-50 text-primary-700 border border-primary-100 text-[10px] font-extrabold">
                                        <span>🎯</span>
                                        <span><?= count($riwayatAktParts) ?> Tugas Dikerjakan:</span>
                                    </span>
                                    <ul class="space-y-1 pl-1">
                                        <?php foreach ($riwayatAktParts as $rap): ?>
                                            <li class="font-bold text-xs text-slate-800 flex items-start gap-1.5">
                                                <span class="text-primary-500 mt-0.5">•</span>
                                                <span><?= e($rap) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <h4 class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                    <span class="text-primary-600">🎯</span>
                                    <span><?= e($rj['aktivitas']) ?></span>
                                </h4>
                            <?php endif; ?>
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
                            <?php if ($rj['tanggal'] === $todayDate): ?>
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
                                            'tugas_ids' => (string)($rj['tugas_ids'] ?? ($rj['tugas_id'] ?? '')),
                                            'aktivitas' => (string)$rj['aktivitas'],
                                            'uraian_hasil' => (string)$rj['uraian_hasil'],
                                            'kendala' => (string)($rj['kendala'] ?? ''),
                                            'status' => (string)$rj['status'],
                                            'foto_bukti' => (string)($rj['foto_bukti'] ?? ''),
                                            'tanggal' => (string)$rj['tanggal'],
                                            'is_readonly' => false
                                        ]), ENT_QUOTES, 'UTF-8') ?>)" 
                                        class="text-primary-600 font-bold hover:underline flex items-center gap-0.5">
                                    <span>✏️</span> Edit Jurnal Ini
                                </button>
                            <?php else: ?>
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
                                            'tugas_ids' => (string)($rj['tugas_ids'] ?? ($rj['tugas_id'] ?? '')),
                                            'aktivitas' => (string)$rj['aktivitas'],
                                            'uraian_hasil' => (string)$rj['uraian_hasil'],
                                            'kendala' => (string)($rj['kendala'] ?? ''),
                                            'status' => (string)$rj['status'],
                                            'foto_bukti' => (string)($rj['foto_bukti'] ?? ''),
                                            'tanggal' => (string)$rj['tanggal'],
                                            'is_readonly' => true
                                        ]), ENT_QUOTES, 'UTF-8') ?>)" 
                                        class="text-slate-500 hover:text-slate-800 font-bold hover:underline flex items-center gap-0.5">
                                    <span>👁️</span> Lihat Rincian (Terkunci)
                                </button>
                            <?php endif; ?>
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
     style="bottom: calc(5.25rem + env(safe-area-inset-bottom, 0px));"
     class="fixed left-0 right-0 max-w-md w-full mx-auto px-3.5 sm:px-4 z-40 transition-all duration-300 transform translate-y-32 opacity-0 pointer-events-none overflow-hidden">
    <div class="bg-slate-900/95 text-white backdrop-blur-md p-3 rounded-2xl shadow-2xl border border-white/10 flex items-center justify-between gap-2.5">
        
        <!-- Left: Counter & Summary Jam -->
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-8 h-8 rounded-xl bg-primary-600 text-white font-black text-xs flex items-center justify-center shrink-0 shadow-md shadow-primary-600/30" id="floating-count-badge">
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
            <button type="button" onclick="openModalFromFloatingBar()" class="px-3.5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-md shadow-primary-600/25 press-bounce flex items-center gap-1.5 transition-all">
                <span>📝</span>
                <span id="floating-btn-text">Isi Jurnal</span>
            </button>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL POPUP FORM ISI JURNAL STAFF (BOTTOM SHEET STYLE)  -->
<!-- ======================================================== -->
<div id="modal-jurnal-staff" class="hidden fixed inset-0 z-[9999] overflow-hidden select-none" style="touch-action: none; overscroll-behavior: none;">
    <!-- Backdrop Scrim -->
    <div id="modal-jurnal-staff-scrim" onclick="closeModalJurnalStaff()" class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity duration-250 opacity-0 pointer-events-none"></div>

    <!-- Sheet Wrapper -->
    <div class="fixed inset-x-0 bottom-0 max-w-md mx-auto w-full pointer-events-none z-10">
        <form id="form-jurnal-staff" onsubmit="submitJurnalStaff(event)" enctype="multipart/form-data"
              style="touch-action: pan-y; overscroll-behavior: contain;"
              class="bg-white rounded-t-[32px] shadow-2xl pointer-events-auto border-t border-slate-200/80 flex flex-col max-h-[88vh] w-full transform translate-y-full transition-transform duration-250 ease-out overflow-hidden text-xs">

            <!-- 1. FIXED HEADER (shrink-0) -->
            <div class="px-5 pt-3.5 pb-3 border-b border-slate-100 shrink-0 bg-white" style="touch-action: none;">
                <div class="w-10 h-1.5 bg-slate-200 rounded-full mx-auto mb-3"></div>
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0 pr-2">
                        <h3 class="font-extrabold text-slate-900 text-sm leading-tight flex items-center gap-1.5 truncate" id="modal-title-text">
                            <span>Isi Jurnal Staff</span>
                            <span class="text-xs">📝</span>
                        </h3>
                        <p class="text-[11px] text-primary-700 font-bold mt-0.5 truncate" id="modal-jam-sub">
                            08:00 - 09:00 WITA &bull; Aktifitas Kerja
                        </p>
                    </div>
                    <button type="button" onclick="closeModalJurnalStaff()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center press-bounce text-xs font-bold shrink-0">
                        ✕
                    </button>
                </div>
            </div>

            <!-- 2. SCROLLABLE MIDDLE BODY (flex-1 min-h-0 overflow-y-auto) -->
            <div class="staff-modal-scroll flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-4 sm:p-5 space-y-3.5 no-scrollbar select-text" style="touch-action: pan-y; overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">

                <!-- Hidden Fields -->
                <input type="hidden" name="jurnal_id" id="inp_jurnal_id" value="">
                <input type="hidden" name="jam_id" id="inp_jam_id" value="">
                <input type="hidden" name="jam_ids" id="inp_jam_ids" value="">
                <input type="hidden" name="jam_rentang" id="inp_jam_rentang" value="">
                <input type="hidden" name="tanggal" id="inp_tanggal" value="<?= e($selectedDate) ?>">
                <input type="hidden" name="tugas_id" id="inp_tugas_id" value="">
                <input type="hidden" name="tugas_ids" id="inp_tugas_ids" value="">

                <!-- Info Bar Tanggal & Jam Terpilih -->
                <div class="p-3 rounded-2xl bg-primary-50/70 border border-primary-100 space-y-2 text-[11px]">
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
                            <span class="font-extrabold text-primary-700 bg-primary-100 px-2 py-0.5 rounded-full" id="modal-selected-count-badge">
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

                <!-- Readonly Banner jika mode lihat rincian -->
                <div id="modal-readonly-banner" class="hidden p-3 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold flex items-start gap-2.5">
                    <span class="text-base shrink-0">🔒</span>
                    <div class="space-y-0.5">
                        <p class="font-bold">Mode Baca Saja (Terkunci)</p>
                        <p class="text-[11px] text-amber-800 leading-snug" id="modal-readonly-text">
                            Jurnal ini berada pada tanggal lampau dan telah dikunci. Pengisian dan perubahan hanya diizinkan pada hari ini.
                        </p>
                    </div>
                </div>

                <!-- 1. Pilihan Tugas / Job Desk Sesuai Jabatan Penugasan (Bisa Lebih Dari 1) -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <label class="block text-xs font-bold text-slate-800">
                            Pilih Tugas / Job Desk <span class="text-rose-500">*</span>
                            <span class="text-[10px] font-semibold text-primary-600">(Bisa &gt; 1)</span>
                        </label>
                        <button type="button" id="btn-toggle-custom-tugas" onclick="toggleCustomTugasBox()"
                                class="text-[10.5px] font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-2.5 py-1 rounded-xl transition-colors press-bounce">
                            ✍️ + Tulis Manual
                        </button>
                    </div>

                    <div id="wrapper-select-tugas-dropdown" class="relative">
                        <select id="modal_select_tugas" onchange="handleSelectTugasChange(this)"
                                class="searchable-select w-full px-3.5 py-3 rounded-2xl border border-slate-200 bg-white font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"
                                data-placeholder="-- Cari &amp; Tambah Tugas (Bisa Lebih Dari 1) --"
                                data-search-placeholder="Cari nama tugas / job desk...">
                            <option value="">-- Cari &amp; Tambah Tugas (Bisa Lebih Dari 1) --</option>

                            <?php if (!empty($tugasJabatan)): ?>
                                <optgroup label="⭐ Tugas Jabatan (<?= e($jabatanName) ?>)">
                                    <?php foreach ($tugasJabatan as $tj): ?>
                                        <option value="<?= (int)$tj['id'] ?>"
                                                data-nama="<?= e($tj['nama_tugas']) ?>"
                                                data-deskripsi="<?= e($tj['deskripsi'] ?? '') ?>"
                                                data-subtext="<?= e($tj['deskripsi'] ?? '') ?>"
                                                <?= !empty($tj['kategori_tugas']) ? 'data-badge="' . e(strtoupper($tj['kategori_tugas'])) . '"' : '' ?>>
                                            <?= e($tj['nama_tugas']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>

                            <optgroup label="✍️ Input Mandiri">
                                <option value="custom"
                                        data-always-show="true"
                                        data-badge="MANUAL"
                                        data-subtext="Tulis nama tugas / aktivitas di luar daftar job desk">[+ Tulis Tugas Sendiri / Aktivitas Lainnya]</option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Daftar Chip Tugas Terpilih -->
                    <div id="selected-tugas-container" class="p-3 rounded-2xl bg-slate-50 border border-slate-200/90 space-y-2">
                        <div class="flex items-center justify-between text-[10.5px]">
                            <span class="font-bold text-slate-700 flex items-center gap-1">
                                <span>🎯</span>
                                <span>Daftar Tugas Terpilih:</span>
                            </span>
                            <span id="selected-tugas-count" class="px-2 py-0.5 rounded-full bg-primary-100 text-primary-800 font-extrabold text-[10px]">
                                0 Tugas
                            </span>
                        </div>
                        <div id="selected-tugas-list" class="flex flex-wrap gap-1.5"></div>
                        <p id="selected-tugas-empty" class="text-[10.5px] text-slate-400 italic">
                            Belum ada tugas dipilih. Pilih 1 atau lebih tugas dari daftar di atas.
                        </p>
                    </div>

                    <?php if (empty($tugasJabatan)): ?>
                        <p class="text-[10.5px] text-amber-700 bg-amber-50 border border-amber-200/80 rounded-xl px-3 py-1.5 mt-1">
                            Belum ada rincian tugas terjadwal untuk jabatan <strong><?= e($jabatanName) ?></strong>. Gunakan tombol <em>+ Tulis Manual</em> atau hubungi Admin.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Input Tugas Custom (Muncul jika klik + Tulis Manual atau pilih Tulis Sendiri) -->
                <div id="container-tugas-custom" class="space-y-1.5 hidden p-3 rounded-2xl bg-amber-50/70 border border-amber-200">
                    <label class="block text-[11px] font-bold text-amber-900">
                        Ketik Nama Tugas / Aktivitas Manual:
                    </label>
                    <div class="flex items-center gap-1.5">
                        <input type="text" name="aktivitas_custom" id="inp_aktivitas_custom"
                               onkeydown="if(event.key==='Enter'){event.preventDefault();addCustomTugasChip();}"
                               placeholder="Contoh: Koordinasi teknis maintenance WiFi &amp; Server"
                               class="flex-1 min-w-0 px-3 py-2.5 rounded-xl border border-amber-300 bg-white font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all text-xs">
                        <button type="button" onclick="addCustomTugasChip()"
                                class="px-3.5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shrink-0 press-bounce shadow-xs">
                            + Tambah
                        </button>
                    </div>
                    <p class="text-[10px] text-amber-700">
                        Tekan <strong>+ Tambah</strong> untuk memasukkan ke daftar tugas terpilih (bisa tambah lebih dari 1).
                    </p>
                </div>

                <!-- 2. Keterangan / Uraian Hasil Kerja -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-800">
                        Keterangan & Hasil Kerja <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="uraian_hasil" id="inp_uraian_hasil" rows="3" required
                              placeholder="Jelaskan apa yang Anda kerjakan dan hasil/progres yang telah dicapai..."
                              class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-white font-normal text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs leading-relaxed"></textarea>
                </div>

                <!-- 3. Kendala (Opsional) -->
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-700">
                        Kendala / Hambatan <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input type="text" name="kendala" id="inp_kendala" placeholder="Misal: Menunggu material, jaringan sempat down, dll."
                           class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-white font-normal text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-xs">
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

                <!-- 5. Foto Bukti Kegiatan (Opsional - Kamera Browser & Galeri) -->
                <div class="space-y-2 pt-1">
                    <label class="block text-xs font-bold text-slate-700 flex items-center justify-between">
                        <span>Foto Bukti Kegiatan <span class="text-slate-400 font-normal">(Opsional)</span></span>
                        <span id="label-foto-info" class="text-[10px] text-slate-400 font-normal">Kamera Langsung / Galeri</span>
                    </label>

                    <input type="hidden" name="foto_captured" id="inp_foto_captured" value="">
                    <input type="file" name="foto_bukti" id="inp_foto_bukti" accept="image/*" onchange="handleFotoPreview(this, 'gallery')" class="hidden">
                    <input type="file" id="inp_foto_camera_fallback" accept="image/*" capture="environment" onchange="handleFotoPreview(this, 'camera_device')" class="hidden">

                    <div class="grid grid-cols-5 gap-2" id="container-upload-buttons">
                        <button type="button" onclick="openStaffLiveCamera()" id="btn-open-live-camera"
                                class="col-span-3 px-3 py-2.5 rounded-2xl bg-primary-50 hover:bg-primary-100 border border-primary-200/80 text-primary-700 flex items-center justify-center gap-2 transition-all press-bounce">
                            <i data-lucide="camera" class="w-4 h-4 shrink-0"></i>
                            <span class="text-xs font-bold truncate" id="btn-foto-text">Buka Kamera Langsung</span>
                        </button>
                        <button type="button" onclick="openStaffGalleryPicker()" id="btn-open-gallery"
                               class="col-span-2 px-3 py-2.5 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 hover:bg-slate-100 text-slate-600 flex items-center justify-center gap-1.5 cursor-pointer transition-all press-bounce">
                            <i data-lucide="image" class="w-4 h-4 shrink-0 text-slate-500"></i>
                            <span class="text-xs font-semibold truncate">Galeri / File</span>
                        </button>
                    </div>

                    <div id="preview-foto-container" class="hidden">
                        <div class="flex items-center gap-3 p-2 rounded-2xl bg-slate-50 border border-slate-200">
                            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                                <img id="img-foto-preview" src="" alt="Bukti" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p id="label-foto-captured-name" class="text-xs font-bold text-slate-700 truncate">Foto siap diupload</p>
                                <p class="text-[10px] text-slate-400">Siap dilampirkan saat simpan jurnal</p>
                            </div>
                            <button type="button" onclick="clearStaffFotoPreview()" id="btn-clear-foto"
                                    class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 transition-all press-bounce"
                                    title="Hapus pilihan foto">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- 3. FIXED STICKY FOOTER (shrink-0) -->
            <div class="shrink-0 px-5 py-3.5 bg-white border-t border-slate-100 flex items-center gap-2.5 safe-bottom">
                <button type="button" onclick="closeModalJurnalStaff()" id="btn-cancel-modal" class="w-1/3 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs press-bounce transition-all">
                    Batal
                </button>
                <button type="submit" id="btn-submit-jurnal-staff" class="w-2/3 py-3 rounded-2xl bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold text-xs shadow-md shadow-primary-600/25 press-bounce flex items-center justify-center gap-2 transition-all">
                    <span>💾</span>
                    <span id="btn-submit-text">Simpan Jurnal</span>
                </button>
            </div>

        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL KAMERA LANGSUNG BROWSER (MIRIP KAMERA SARPRAS)    -->
<!-- ======================================================== -->
<div id="staff-camera-modal" class="hidden fixed inset-0 bg-black select-none flex-col overflow-hidden" style="z-index: 10005; touch-action: none;">
    <!-- Header Kamera -->
    <div class="flex items-center justify-between px-4 py-3.5 bg-black/80 text-white border-b border-white/10 safe-top shrink-0">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
            <span class="text-xs font-bold tracking-wide truncate">Kamera Bukti Kegiatan</span>
            <span id="staff-cam-facing-badge" class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-white/15 text-white/90 shrink-0">Belakang</span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" onclick="switchStaffCamera()" id="btn-staff-switch-cam"
                    class="w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center transition-all press-bounce"
                    title="Ganti Kamera Depan/Belakang">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
            <button type="button" onclick="closeStaffLiveCamera()" id="btn-staff-close-cam"
                    class="w-9 h-9 rounded-full bg-white/15 hover:bg-rose-500/80 text-white flex items-center justify-center transition-all press-bounce"
                    title="Tutup Kamera">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Live Video Viewfinder -->
    <div class="relative flex-1 bg-black overflow-hidden flex items-center justify-center">
        <video id="staff-cam-video" autoplay playsinline muted class="w-full h-full object-cover"></video>

        <!-- Framing Guides (Sudut Bidang Foto) -->
        <div class="pointer-events-none absolute inset-6 border border-white/15 rounded-2xl">
            <span class="absolute -top-px -left-px w-6 h-6 border-t-2 border-l-2 border-white/75 rounded-tl-xl"></span>
            <span class="absolute -top-px -right-px w-6 h-6 border-t-2 border-r-2 border-white/75 rounded-tr-xl"></span>
            <span class="absolute -bottom-px -left-px w-6 h-6 border-b-2 border-l-2 border-white/75 rounded-bl-xl"></span>
            <span class="absolute -bottom-px -right-px w-6 h-6 border-b-2 border-r-2 border-white/75 rounded-br-xl"></span>
        </div>

        <!-- Flash Effect Saat Jepret -->
        <div id="staff-cam-flash" class="pointer-events-none absolute inset-0 bg-white opacity-0 transition-opacity duration-150"></div>

        <!-- Fallback/Error Panel jika Kamera Web diblokir browser -->
        <div id="staff-cam-error-view" class="hidden absolute inset-0 bg-slate-950/95 flex flex-col items-center justify-center p-6 text-center z-30">
            <div class="w-14 h-14 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-white font-bold text-base mb-1">Kamera Browser Terbatas</h3>
            <p id="staff-cam-error-msg" class="text-slate-300 text-xs max-w-xs mb-4">
                Browser belum mengizinkan akses kamera langsung atau memerlukan HTTPS. Buka kamera bawaan perangkat di bawah ini.
            </p>
            <div class="flex flex-col gap-2.5 w-full max-w-xs">
                <button type="button" onclick="closeStaffLiveCamera(); document.getElementById('inp_foto_camera_fallback').click();"
                        class="w-full py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs shadow-lg active:scale-95 transition-all">
                    📷 Buka Kamera Perangkat
                </button>
                <button type="button" onclick="closeStaffLiveCamera(); openStaffGalleryPicker();"
                        class="w-full py-2.5 px-4 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs active:scale-95 transition-all">
                    🖼️ Pilih dari Galeri
                </button>
                <button type="button" onclick="closeStaffLiveCamera();"
                        class="w-full py-2 text-slate-400 hover:text-white text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Shutter Footer -->
    <div class="px-6 py-5 bg-black/90 border-t border-white/10 flex items-center justify-between safe-bottom shrink-0">
        <button type="button" onclick="closeStaffLiveCamera(); openStaffGalleryPicker();"
                class="w-20 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/90 text-[11px] font-semibold text-center press-bounce transition-all">
            Galeri
        </button>

        <button type="button" onclick="snapStaffPhoto()" id="btn-staff-shutter"
                class="rounded-full border-4 border-white/90 bg-white/15 flex items-center justify-center active:scale-95 transition-transform shadow-lg"
                style="width:70px;height:70px;"
                title="Ambil Foto">
            <span class="rounded-full bg-white shadow-md block" style="width:52px;height:52px;"></span>
        </button>

        <button type="button" onclick="closeStaffLiveCamera()"
                class="w-20 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/90 text-[11px] font-semibold text-center press-bounce transition-all">
            Batal
        </button>
    </div>

    <canvas id="staff-cam-canvas" class="hidden"></canvas>
</div>

<script>
    // Bersihkan modal lama di document.body jika ada dari navigasi SPA sebelumnya
    try {
        const detachedModals = document.querySelectorAll('body > #modal-jurnal-staff, body > #staff-camera-modal');
        detachedModals.forEach(el => {
            if (document.querySelector('#mobile-main-view #' + el.id)) {
                el.remove();
            }
        });
    } catch (e) {}

    // State management multi-select jam & multi-select tugas
    let selectedJamMap = {};
    let selectedTugasList = [];
    let isModalTugasReadOnly = false;

    function renderSelectedTugasChips() {
        const listEl = document.getElementById('selected-tugas-list');
        const countEl = document.getElementById('selected-tugas-count');
        const emptyEl = document.getElementById('selected-tugas-empty');
        const inpTugasId = document.getElementById('inp_tugas_id');
        const inpTugasIds = document.getElementById('inp_tugas_ids');

        const masterIds = selectedTugasList
            .filter(it => it.id && Number(it.id) > 0)
            .map(it => Number(it.id));

        if (inpTugasId) inpTugasId.value = masterIds.length > 0 ? String(masterIds[0]) : '';
        if (inpTugasIds) inpTugasIds.value = masterIds.join(',');
        if (countEl) countEl.textContent = `${selectedTugasList.length} Tugas`;

        if (!listEl) return;
        listEl.innerHTML = '';

        if (selectedTugasList.length === 0) {
            if (emptyEl) emptyEl.classList.remove('hidden');
            return;
        }
        if (emptyEl) emptyEl.classList.add('hidden');

        selectedTugasList.forEach((it, idx) => {
            const chip = document.createElement('div');
            chip.className = it.custom
                ? 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-amber-50 text-amber-900 border border-amber-200 text-[11px] font-bold shadow-2xs max-w-full'
                : 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-white text-primary-900 border border-primary-200 text-[11px] font-bold shadow-2xs max-w-full';

            const iconSpan = document.createElement('span');
            iconSpan.className = 'shrink-0';
            iconSpan.textContent = it.custom ? '✍️' : '🎯';

            const textSpan = document.createElement('span');
            textSpan.className = 'leading-snug break-words';
            textSpan.textContent = it.nama;

            chip.appendChild(iconSpan);
            chip.appendChild(textSpan);

            if (!isModalTugasReadOnly) {
                const rmBtn = document.createElement('button');
                rmBtn.type = 'button';
                rmBtn.className = 'w-4 h-4 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 flex items-center justify-center text-[10px] font-black shrink-0 ml-0.5 transition-colors';
                rmBtn.textContent = '✕';
                rmBtn.title = 'Hapus tugas ini';
                rmBtn.onclick = function() {
                    removeSelectedTugasChip(idx);
                };
                chip.appendChild(rmBtn);
            }

            listEl.appendChild(chip);
        });
    }

    function removeSelectedTugasChip(idx) {
        if (isModalTugasReadOnly) return;
        if (idx < 0 || idx >= selectedTugasList.length) return;
        selectedTugasList.splice(idx, 1);
        renderSelectedTugasChips();
    }

    function addMasterTugasChip(rawId, rawNama, rawDeskripsi) {
        const id = parseInt(rawId, 10) || null;
        const nama = String(rawNama || '').trim();
        if (!nama) return;

        const already = selectedTugasList.some(
            it => (id && it.id === id) || it.nama.toLowerCase() === nama.toLowerCase()
        );
        if (already) {
            if (typeof AndroidUI !== 'undefined' && typeof AndroidUI.toast === 'function') {
                AndroidUI.toast('Tugas tersebut sudah ada di daftar pilihan.', 'info');
            }
            return;
        }

        selectedTugasList.push({ id: id, nama: nama, custom: false });
        renderSelectedTugasChips();

        const inpUraian = document.getElementById('inp_uraian_hasil');
        const desc = String(rawDeskripsi || '').trim();
        if (inpUraian && desc && (!inpUraian.value || inpUraian.value.trim() === '')) {
            inpUraian.value = `Melaksanakan ${nama}: ${desc}.`;
        }
    }

    function toggleCustomTugasBox(forceShow) {
        if (isModalTugasReadOnly) return;
        const containerCustom = document.getElementById('container-tugas-custom');
        const inpCustom = document.getElementById('inp_aktivitas_custom');
        if (!containerCustom) return;

        const shouldShow = (typeof forceShow === 'boolean')
            ? forceShow
            : containerCustom.classList.contains('hidden');

        if (shouldShow) {
            containerCustom.classList.remove('hidden');
            if (inpCustom) setTimeout(() => inpCustom.focus(), 40);
        } else {
            containerCustom.classList.add('hidden');
        }
    }

    function addCustomTugasChip() {
        if (isModalTugasReadOnly) return;
        const inpCustom = document.getElementById('inp_aktivitas_custom');
        const containerCustom = document.getElementById('container-tugas-custom');
        if (!inpCustom) return false;

        const raw = inpCustom.value.trim();
        if (!raw) return false;

        const parts = raw.split(/\s*•\s*/).map(s => s.trim()).filter(Boolean);
        let added = false;
        parts.forEach(part => {
            const exists = selectedTugasList.some(it => it.nama.toLowerCase() === part.toLowerCase());
            if (!exists) {
                selectedTugasList.push({ id: null, nama: part, custom: true });
                added = true;
            }
        });

        inpCustom.value = '';
        if (containerCustom) containerCustom.classList.add('hidden');
        renderSelectedTugasChips();
        return added;
    }

    function hydrateSelectedTugasFromData(data, isReadOnly) {
        selectedTugasList = [];
        isModalTugasReadOnly = Boolean(isReadOnly);

        const selectTugas = document.getElementById('modal_select_tugas');
        const byId = {};
        const byLower = {};
        if (selectTugas && selectTugas.options) {
            for (let i = 0; i < selectTugas.options.length; i++) {
                const opt = selectTugas.options[i];
                if (!opt.value || opt.value === 'custom') continue;
                const nm = (opt.dataset.nama || opt.textContent || '').trim();
                byId[String(opt.value)] = { id: Number(opt.value), nama: nm };
                if (nm) byLower[nm.toLowerCase()] = { id: Number(opt.value), nama: nm };
            }
        }

        const rawIdsStr = String(data.tugas_ids || data.tugas_id || '');
        const idList = rawIdsStr
            .split(/[\s,;]+/)
            .map(s => parseInt(s, 10))
            .filter(n => !isNaN(n) && n > 0);
        const aktParts = String(data.aktivitas || '')
            .split(/\s*•\s*/)
            .map(s => s.trim())
            .filter(Boolean);

        const seenNames = new Set();
        const seenIds = new Set();

        if (aktParts.length > 0) {
            aktParts.forEach(part => {
                const low = part.toLowerCase();
                if (seenNames.has(low)) return;
                if (byLower[low]) {
                    seenNames.add(low);
                    seenIds.add(byLower[low].id);
                    selectedTugasList.push({ id: byLower[low].id, nama: byLower[low].nama, custom: false });
                } else {
                    seenNames.add(low);
                    selectedTugasList.push({ id: null, nama: part, custom: true });
                }
            });
        }

        idList.forEach(id => {
            if (!seenIds.has(id) && byId[String(id)]) {
                const item = byId[String(id)];
                const low = item.nama.toLowerCase();
                if (!seenNames.has(low)) {
                    seenIds.add(id);
                    seenNames.add(low);
                    selectedTugasList.push({ id: item.id, nama: item.nama, custom: false });
                }
            }
        });

        renderSelectedTugasChips();
    }

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
                tugas_ids: card.dataset.tugasIds || '',
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
        card.classList.add('border-primary-500', 'ring-2', 'ring-primary-500/30', 'bg-primary-50/60');
        card.classList.remove('border-slate-200/90', 'border-emerald-200', 'bg-white', 'bg-emerald-50/25');
        const check = card.querySelector('.jam-check-box');
        if (check) {
            check.className = 'jam-check-box w-5 h-5 rounded-full border border-primary-600 bg-primary-600 flex items-center justify-center text-[10px] font-black text-white transition-all shadow-xs';
        }
    }

    // Kembalikan style normal kartu saat unselected
    function unstyleCard(card) {
        card.classList.remove('border-primary-500', 'ring-2', 'ring-primary-500/30', 'bg-primary-50/60');
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

    // Tampilkan notifikasi jika mencoba memilih jam di tanggal selain hari ini
    function showStaffLockedNotice() {
        if (typeof AndroidUI !== 'undefined' && typeof AndroidUI.toast === 'function') {
            AndroidUI.toast('Jurnal kerja hanya dapat diisi pada hari ini (Hari berjalan). Tanggal kemarin/lampau telah dikunci.', 'warning');
        } else {
            alert('Jurnal kerja hanya dapat diisi pada hari ini (Hari berjalan). Tanggal kemarin/lampau telah dikunci.');
        }
    }

    // Buka rincian jurnal mode baca saja dari kartu
    function openStaffReadOnlyFromCard(jamId) {
        const card = document.getElementById('card-jam-' + jamId);
        if (!card) return;
        const d = card.dataset;
        openModalJurnalStaff({
            jam_id: parseInt(d.jamId) || jamId,
            jam_rentang: d.jamRentang,
            jam_display: d.jamDisplay,
            nama_sesi: d.namaSesi,
            is_filled: true,
            jurnal_id: parseInt(d.jurnalId) || 0,
            tugas_id: parseInt(d.tugasId) || 0,
            tugas_ids: d.tugasIds || '',
            aktivitas: d.aktivitas || '',
            uraian_hasil: d.uraian || '',
            kendala: d.kendala || '',
            status: d.status || 'selesai',
            foto_bukti: d.foto || '',
            tanggal: '<?= e($selectedDate) ?>',
            is_readonly: true
        });
    }

    // Pilih seluruh jam
    function selectAllJam() {
        if ('<?= $isToday ? '1' : '0' ?>' !== '1') {
            showStaffLockedNotice();
            return;
        }
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
                    tugas_ids: card.dataset.tugasIds || '',
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
            tugas_ids: filledItem.tugas_ids || '',
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

    // Pastikan modal form & modal kamera terpasang langsung di document.body tanpa duplikat antar navigasi SPA
    function mountStaffModalElement(id) {
        const els = document.querySelectorAll('#' + id);
        if (!els.length) return null;
        let keeper = els[0];
        for (let i = 0; i < els.length; i++) {
            if (els[i].parentElement !== document.body) {
                keeper = els[i];
                break;
            }
        }
        els.forEach(el => {
            if (el !== keeper) el.remove();
        });
        if (keeper.parentElement !== document.body) {
            document.body.appendChild(keeper);
        }
        return keeper;
    }

    function ensureStaffModalMounted() {
        mountStaffModalElement('modal-jurnal-staff');
        mountStaffModalElement('staff-camera-modal');
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    // Buka Modal Pengisian Jurnal Staff
    function openModalJurnalStaff(data) {
        ensureStaffModalMounted();
        const modal = document.getElementById('modal-jurnal-staff');
        const scrim = document.getElementById('modal-jurnal-staff-scrim');
        const sheet = document.getElementById('form-jurnal-staff');
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
                pill.className = 'px-2 py-0.5 rounded-lg bg-white text-primary-700 border border-primary-200 text-[10px] font-bold flex items-center gap-1';
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

        // Reset foto preview & state kamera/upload
        const inpCaptured = document.getElementById('inp_foto_captured');
        const inpBukti = document.getElementById('inp_foto_bukti');
        const inpFallback = document.getElementById('inp_foto_camera_fallback');
        const lblCapturedName = document.getElementById('label-foto-captured-name');
        if (inpCaptured) inpCaptured.value = '';
        if (inpBukti) inpBukti.value = '';
        if (inpFallback) inpFallback.value = '';

        previewFoto.classList.add('hidden');
        imgFoto.src = '';
        if (btnFotoText) btnFotoText.textContent = 'Buka Kamera Langsung';
        if (lblCapturedName) lblCapturedName.textContent = 'Foto siap diupload';
        if (data.foto_bukti) {
            previewFoto.classList.remove('hidden');
            imgFoto.src = '<?= asset('') ?>' + data.foto_bukti.replace(/^\/+/, '');
            if (btnFotoText) btnFotoText.textContent = 'Ambil Ulang Kamera';
            if (lblCapturedName) lblCapturedName.textContent = 'Foto bukti tersimpan';
        }

        // Setup Multi-Tugas & Dropdown Tugas Sesuai Divisi
        const isReadOnly = Boolean(data.is_readonly);
        selectTugas.value = '';
        containerCustom.classList.add('hidden');
        inpCustom.value = '';
        inpCustom.required = false;
        hydrateSelectedTugasFromData(data, isReadOnly);

        // Set radio status
        const statusRadios = document.querySelectorAll('.status-radio');
        const currentStatus = (data.status || 'selesai').toLowerCase();
        statusRadios.forEach(radio => {
            radio.checked = (radio.value === currentStatus);
        });
        updateStatusPillStyle();

        // Penyesuaian Mode Baca Saja (Read-Only) jika tanggal lampau / terkunci
        const readonlyBanner = document.getElementById('modal-readonly-banner');
        const containerUpload = document.getElementById('container-upload-buttons');
        const btnSubmit = document.getElementById('btn-submit-jurnal-staff');
        const btnCancel = document.getElementById('btn-cancel-modal');
        const btnClearFoto = document.getElementById('btn-clear-foto');
        const labelFotoInfo = document.getElementById('label-foto-info');
        const btnToggleCustom = document.getElementById('btn-toggle-custom-tugas');
        const wrapperSelectTugas = document.getElementById('wrapper-select-tugas-dropdown');

        if (isReadOnly) {
            if (readonlyBanner) readonlyBanner.classList.remove('hidden');
            if (containerUpload) containerUpload.classList.add('hidden');
            if (btnSubmit) btnSubmit.classList.add('hidden');
            if (btnClearFoto) btnClearFoto.classList.remove('hidden'), btnClearFoto.classList.add('hidden');
            if (btnToggleCustom) btnToggleCustom.classList.add('hidden');
            if (wrapperSelectTugas) wrapperSelectTugas.classList.add('hidden');
            if (labelFotoInfo) labelFotoInfo.textContent = 'Bukti Tersimpan';
            if (btnCancel) {
                btnCancel.className = 'w-full py-3 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs press-bounce transition-all';
                btnCancel.textContent = 'Tutup Rincian';
            }
            selectTugas.disabled = true;
            inpCustom.disabled = true;
            inpUraian.disabled = true;
            inpKendala.disabled = true;
            statusRadios.forEach(r => r.disabled = true);
            document.getElementById('modal-title-text').innerHTML = `<span>Rincian Jurnal Staff</span> <span class="text-xs">🔒</span>`;
        } else {
            if (readonlyBanner) readonlyBanner.classList.add('hidden');
            if (containerUpload) containerUpload.classList.remove('hidden');
            if (btnSubmit) btnSubmit.classList.remove('hidden');
            if (btnClearFoto) btnClearFoto.classList.remove('hidden');
            if (btnToggleCustom) btnToggleCustom.classList.remove('hidden');
            if (wrapperSelectTugas) wrapperSelectTugas.classList.remove('hidden');
            if (labelFotoInfo) labelFotoInfo.textContent = 'Kamera Langsung / Galeri';
            if (btnCancel) {
                btnCancel.className = 'w-1/3 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs press-bounce transition-all';
                btnCancel.textContent = 'Batal';
            }
            selectTugas.disabled = false;
            inpCustom.disabled = false;
            inpUraian.disabled = false;
            inpKendala.disabled = false;
            statusRadios.forEach(r => r.disabled = false);
        }

        if (window.SearchableSelect && typeof window.SearchableSelect.refresh === 'function') {
            window.SearchableSelect.refresh(selectTugas);
        }

        // Tampilkan modal bottom sheet dengan transisi halus
        modal.classList.remove('hidden');
        void modal.offsetWidth;
        if (scrim) {
            scrim.classList.remove('opacity-0', 'pointer-events-none');
            scrim.classList.add('opacity-100', 'pointer-events-auto');
        }
        if (sheet) {
            sheet.classList.remove('translate-y-full');
        }
        lockStaffModalScroll();

        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }

    // Kunci halaman di belakang modal agar modal tidak bisa ikut tergeser (iOS/Android)
    function lockStaffModalScroll() {
        document.body.classList.add('overflow-hidden');
        document.body.style.touchAction = 'none';
        document.documentElement.style.overscrollBehavior = 'none';
        const mainView = document.getElementById('mobile-main-view');
        if (mainView) {
            if (mainView.dataset.staffPrevOverflow === undefined) {
                mainView.dataset.staffPrevOverflow = mainView.style.overflow || '';
            }
            mainView.style.overflow = 'hidden';
            mainView.style.touchAction = 'none';
        }
        const modal = document.getElementById('modal-jurnal-staff');
        if (modal && !modal.dataset.touchGuard) {
            modal.dataset.touchGuard = '1';
            let startY = 0;
            modal.addEventListener('touchstart', (e) => {
                startY = e.touches && e.touches[0] ? e.touches[0].clientY : 0;
            }, { passive: true });
            modal.addEventListener('touchmove', (e) => {
                const scroller = e.target.closest
                    ? (e.target.closest('.searchable-select-options') || e.target.closest('.staff-modal-scroll'))
                    : null;
                if (!scroller) {
                    e.preventDefault();
                    return;
                }
                const y = e.touches && e.touches[0] ? e.touches[0].clientY : 0;
                const dy = y - startY;
                const atTop = scroller.scrollTop <= 0;
                const atBottom = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 1;
                if ((atTop && dy > 0) || (atBottom && dy < 0) || scroller.scrollHeight <= scroller.clientHeight) {
                    e.preventDefault();
                }
            }, { passive: false });
        }
    }

    function unlockStaffModalScroll() {
        document.body.classList.remove('overflow-hidden');
        document.body.style.overflow = '';
        document.body.style.touchAction = '';
        document.documentElement.style.overscrollBehavior = '';
        const mainView = document.getElementById('mobile-main-view');
        if (mainView && mainView.dataset.staffPrevOverflow !== undefined) {
            mainView.style.overflow = mainView.dataset.staffPrevOverflow;
            mainView.style.touchAction = '';
            delete mainView.dataset.staffPrevOverflow;
        }
    }
    window.unlockStaffModalScroll = unlockStaffModalScroll;

    // Tutup Modal
    function closeModalJurnalStaff() {
        if (typeof closeStaffLiveCamera === 'function') {
            closeStaffLiveCamera();
        }
        if (window.SearchableSelect && typeof window.SearchableSelect.closeAll === 'function') {
            window.SearchableSelect.closeAll();
        }
        const modal = document.getElementById('modal-jurnal-staff');
        const scrim = document.getElementById('modal-jurnal-staff-scrim');
        const sheet = document.getElementById('form-jurnal-staff');

        // Reset status disabled form elements
        const selectTugas = document.getElementById('modal_select_tugas');
        const inpCustom = document.getElementById('inp_aktivitas_custom');
        const inpUraian = document.getElementById('inp_uraian_hasil');
        const inpKendala = document.getElementById('inp_kendala');
        const statusRadios = document.querySelectorAll('.status-radio');
        if (selectTugas) selectTugas.disabled = false;
        if (selectTugas && window.SearchableSelect && typeof window.SearchableSelect.refresh === 'function') {
            window.SearchableSelect.refresh(selectTugas);
        }
        if (inpCustom) inpCustom.disabled = false;
        if (inpUraian) inpUraian.disabled = false;
        if (inpKendala) inpKendala.disabled = false;
        if (statusRadios) statusRadios.forEach(r => r.disabled = false);

        if (scrim) {
            scrim.classList.remove('opacity-100', 'pointer-events-auto');
            scrim.classList.add('opacity-0', 'pointer-events-none');
        }
        if (sheet) {
            sheet.classList.add('translate-y-full');
        }
        unlockStaffModalScroll();

        setTimeout(() => {
            if (modal && sheet && sheet.classList.contains('translate-y-full')) {
                modal.classList.add('hidden');
            }
        }, 250);
    }

    // Event Handler saat dropdown tugas diubah (menambah ke daftar chip tugas terpilih)
    function handleSelectTugasChange(selectEl) {
        if (!selectEl || isModalTugasReadOnly) return;
        const val = selectEl.value;
        if (!val) return;

        if (val === 'custom') {
            toggleCustomTugasBox(true);
        } else {
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (selectedOpt) {
                const nama = selectedOpt.dataset.nama || selectedOpt.textContent || '';
                const deskripsi = selectedOpt.dataset.deskripsi || '';
                addMasterTugasChip(val, nama, deskripsi);
            }
        }

        // Reset dropdown ke placeholder agar staff bisa langsung menambah tugas kedua/ketiga
        selectEl.value = '';
        if (window.SearchableSelect && typeof window.SearchableSelect.refresh === 'function') {
            window.SearchableSelect.refresh(selectEl);
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

    // Global variable penampung hasil foto kamera / upload
    window.__staffCamStream = window.__staffCamStream || null;
    window.__staffCamFacing = window.__staffCamFacing || 'environment';
    window.__staffCamReqId = window.__staffCamReqId || 0;
    window.__staffCapturedBlob = window.__staffCapturedBlob || null;
    window.__staffCapturedFileName = window.__staffCapturedFileName || null;

    // Helper pemicu pemilih galeri file
    function openStaffGalleryPicker() {
        const inpBukti = document.getElementById('inp_foto_bukti');
        if (inpBukti) inpBukti.click();
    }
    window.openStaffGalleryPicker = openStaffGalleryPicker;

    // Terapkan hasil foto (kamera browser / galeri) ke preview UI
    function applyStaffCapturedDataUrl(dataUrl, displayName, source) {
        const inpCaptured = document.getElementById('inp_foto_captured');
        const previewContainer = document.getElementById('preview-foto-container');
        const imgPreview = document.getElementById('img-foto-preview');
        const btnText = document.getElementById('btn-foto-text');
        const lblName = document.getElementById('label-foto-captured-name');

        if (source === 'camera') {
            if (inpCaptured) inpCaptured.value = dataUrl || '';
            const inpBukti = document.getElementById('inp_foto_bukti');
            if (inpBukti) inpBukti.value = '';
        } else {
            // Jika dari galeri / kamera native fallback, kosongkan string base64 berat
            if (inpCaptured) inpCaptured.value = '';
        }

        if (imgPreview) imgPreview.src = dataUrl || '';
        if (previewContainer) previewContainer.classList.remove('hidden');
        if (btnText) btnText.textContent = 'Ambil Ulang Kamera';
        if (lblName) lblName.textContent = displayName || 'Foto siap dilampirkan';
    }

    // Hapus pilihan foto bukti
    function clearStaffFotoPreview() {
        const inpCaptured = document.getElementById('inp_foto_captured');
        const inpBukti = document.getElementById('inp_foto_bukti');
        const inpFallback = document.getElementById('inp_foto_camera_fallback');
        const previewContainer = document.getElementById('preview-foto-container');
        const imgPreview = document.getElementById('img-foto-preview');
        const btnText = document.getElementById('btn-foto-text');
        const lblName = document.getElementById('label-foto-captured-name');

        if (inpCaptured) inpCaptured.value = '';
        if (inpBukti) inpBukti.value = '';
        if (inpFallback) inpFallback.value = '';
        window.__staffCapturedBlob = null;
        window.__staffCapturedFileName = null;

        if (imgPreview) imgPreview.src = '';
        if (previewContainer) previewContainer.classList.add('hidden');
        if (btnText) btnText.textContent = 'Buka Kamera Langsung';
        if (lblName) lblName.textContent = 'Foto siap diupload';
    }

    // Hentikan dan lepaskan resource stream video & kamera WebRTC secara tuntas (Android/iOS/PC)
    function stopStaffLiveCameraStream() {
        window.__staffCamReqId = (window.__staffCamReqId || 0) + 1;
        if (window.__staffCamStream) {
            try {
                window.__staffCamStream.getTracks().forEach(t => {
                    try { t.stop(); } catch (e) {}
                });
            } catch (err) {}
            window.__staffCamStream = null;
        }
        const video = document.getElementById('staff-cam-video');
        if (video) {
            if (video.srcObject && typeof video.srcObject.getTracks === 'function') {
                try {
                    video.srcObject.getTracks().forEach(t => {
                        try { t.stop(); } catch (e) {}
                    });
                } catch (e) {}
            }
            try { video.pause(); } catch (err) {}
            video.srcObject = null;
            try { video.load(); } catch (err) {}
        }
    }
    window.stopStaffLiveCameraStream = stopStaffLiveCameraStream;

    // Buka Kamera Browser Langsung (getUserMedia mirip Sarpras, dengan fallback kamera perangkat jika HTTPS/izin tidak ada)
    async function openStaffLiveCamera(requestedFacing) {
        stopStaffLiveCameraStream();
        ensureStaffModalMounted();

        const isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
        const hasGetUserMedia = !!(navigator.mediaDevices && typeof navigator.mediaDevices.getUserMedia === 'function');

        // Jika koneksi tidak aman (HTTP biasa) atau browser tidak punya getUserMedia, langsung picu kamera bawaan HP secara sinkron
        if (!isSecure || !hasGetUserMedia) {
            const fallbackInp = document.getElementById('inp_foto_camera_fallback');
            if (fallbackInp) {
                fallbackInp.value = '';
                fallbackInp.click();
            } else {
                openStaffGalleryPicker();
            }
            return;
        }

        const camModal = document.getElementById('staff-camera-modal');
        const video = document.getElementById('staff-cam-video');
        const badge = document.getElementById('staff-cam-facing-badge');
        const errorView = document.getElementById('staff-cam-error-view');
        if (!camModal || !video) return;

        if (errorView) errorView.classList.add('hidden');

        const facing = requestedFacing || window.__staffCamFacing || 'environment';
        window.__staffCamFacing = facing;

        if (badge) {
            badge.textContent = facing === 'user' ? 'Depan' : 'Belakang';
        }

        camModal.classList.remove('hidden');
        camModal.classList.add('flex');

        const reqId = ++window.__staffCamReqId;

        let stream = null;
        try {
            // Tingkat 1: Resolusi ideal 720p (mirip Sarpras, ramah semua sensor HP)
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: facing },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });
        } catch (err1) {
            console.warn('Live camera ideal constraints failed, trying facingMode only:', err1);
            try {
                // Tingkat 2: Tanpa batasan resolusi
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: facing },
                    audio: false
                });
            } catch (err2) {
                try {
                    // Tingkat 3: Video paling dasar
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                } catch (err3) {
                    console.error('Live camera browser failed completely:', err3);
                    if (reqId === window.__staffCamReqId && !camModal.classList.contains('hidden')) {
                        if (errorView) {
                            const isDenied = err3 && (err3.name === 'NotAllowedError' || err3.name === 'PermissionDeniedError');
                            const errMsg = document.getElementById('staff-cam-error-msg');
                            if (errMsg) {
                                errMsg.textContent = isDenied
                                    ? 'Izin kamera browser belum diberikan. Silakan buka kamera bawaan perangkat di bawah ini.'
                                    : 'Kamera web tidak dapat diakses (' + (err3.message || 'Sedang digunakan aplikasi lain') + '). Silakan buka kamera bawaan perangkat di bawah ini.';
                            }
                            errorView.classList.remove('hidden');
                        }
                    }
                    return;
                }
            }
        }

        if (reqId !== window.__staffCamReqId || camModal.classList.contains('hidden')) {
            if (stream) stream.getTracks().forEach(t => t.stop());
            return;
        }

        window.__staffCamStream = stream;
        video.srcObject = stream;
        video.setAttribute('playsinline', '');
        video.setAttribute('muted', '');

        try {
            await video.play();
        } catch (errPlay) {
            console.warn('Video play warning:', errPlay);
        }
    }

    function closeStaffLiveCamera() {
        stopStaffLiveCameraStream();
        const camModal = document.getElementById('staff-camera-modal');
        if (camModal) {
            camModal.classList.add('hidden');
            camModal.classList.remove('flex');
        }
        const errorView = document.getElementById('staff-cam-error-view');
        if (errorView) errorView.classList.add('hidden');
    }
    window.closeStaffLiveCamera = closeStaffLiveCamera;

    function switchStaffCamera() {
        const nextFacing = window.__staffCamFacing === 'user' ? 'environment' : 'user';
        openStaffLiveCamera(nextFacing);
    }

    function snapStaffPhoto() {
        const video = document.getElementById('staff-cam-video');
        const canvas = document.getElementById('staff-cam-canvas') || document.createElement('canvas');
        const flash = document.getElementById('staff-cam-flash');
        if (!video || !video.videoWidth || !video.videoHeight) {
            if (typeof AndroidUI !== 'undefined') {
                AndroidUI.toast('Kamera masih memuat gambar, tunggu sebentar...', 'warning');
            }
            return;
        }

        if (flash) {
            flash.classList.remove('opacity-0');
            flash.classList.add('opacity-90');
            setTimeout(() => {
                flash.classList.remove('opacity-90');
                flash.classList.add('opacity-0');
            }, 140);
        }

        const vw = video.videoWidth;
        const vh = video.videoHeight;
        const maxDim = 1280;
        let targetW = vw;
        let targetH = vh;
        if (targetW > maxDim || targetH > maxDim) {
            if (targetW > targetH) {
                targetH = Math.round((targetH * maxDim) / targetW);
                targetW = maxDim;
            } else {
                targetW = Math.round((targetW * maxDim) / targetH);
                targetH = maxDim;
            }
        }

        canvas.width = targetW;
        canvas.height = targetH;
        const ctx = canvas.getContext('2d');

        // Jika kamera depan, mirror agar hasil alami
        if (window.__staffCamFacing === 'user') {
            ctx.translate(targetW, 0);
            ctx.scale(-1, 1);
        }

        ctx.drawImage(video, 0, 0, targetW, targetH);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const fileName = `Foto_Kamera_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.jpg`;

        // Simpan juga sebagai Blob native untuk FormData multipart
        try {
            canvas.toBlob((blob) => {
                window.__staffCapturedBlob = blob;
                window.__staffCapturedFileName = fileName;
            }, 'image/jpeg', 0.85);
        } catch (e) {
            window.__staffCapturedBlob = null;
        }

        applyStaffCapturedDataUrl(dataUrl, fileName, 'camera');

        setTimeout(() => {
            closeStaffLiveCamera();
        }, 130);

        if (typeof AndroidUI !== 'undefined') {
            AndroidUI.toast('Foto berhasil diambil dari kamera!', 'success');
        }
    }

    // Preview & Kompresi Foto Bukti saat pilih dari Galeri / Fallback Kamera Native
    function handleFotoPreview(input, source) {
        const file = input && input.files && input.files[0];
        if (!file) return;

        // Jika bersumber dari fallback kamera native HP
        if (source === 'camera_device') {
            window.__staffCapturedBlob = file;
            window.__staffCapturedFileName = file.name;
        } else {
            // Galeri asli
            window.__staffCapturedBlob = null;
            window.__staffCapturedFileName = null;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            applyStaffCapturedDataUrl(e.target.result, file.name, source || 'gallery');
        };
        reader.readAsDataURL(file);
    }

    // Submit AJAX Form Jurnal Staff
    async function submitJurnalStaff(e) {
        e.preventDefault();

        // Validasi penguncian tanggal & hari libur
        if ('<?= $canFill ? '1' : '0' ?>' !== '1') {
            const lockMsg = '<?= $isHoliday ? ('Hari ini adalah hari libur (' . e(addslashes($liburInfo['nama_libur'] ?? 'Hari Libur')) . '). Tidak ada pengisian jurnal pada hari libur.') : 'Jurnal kerja hanya dapat diisi atau diubah pada hari ini.' ?>';
            if (typeof AndroidUI !== 'undefined') {
                AndroidUI.toast(lockMsg, 'warning');
            } else {
                alert(lockMsg);
            }
            return;
        }

        const form = document.getElementById('form-jurnal-staff');
        const btnSubmit = document.getElementById('btn-submit-jurnal-staff');
        const btnText = document.getElementById('btn-submit-text');

        const selectTugas = document.getElementById('modal_select_tugas');
        const inpCustom = document.getElementById('inp_aktivitas_custom');

        // Jika masih ada teks tugas manual di kolom input yang belum diklik "+ Tambah", otomatis masukkan ke daftar
        if (inpCustom && inpCustom.value.trim() !== '') {
            addCustomTugasChip();
        }
        if (selectTugas && selectTugas.value && selectTugas.value !== 'custom') {
            handleSelectTugasChange(selectTugas);
        }

        if (selectedTugasList.length === 0) {
            if (typeof AndroidUI !== 'undefined') AndroidUI.toast('Silakan pilih minimal 1 tugas terlebih dahulu.', 'warning');
            const wrapper = selectTugas ? selectTugas.nextElementSibling : null;
            const triggerBtn = wrapper && wrapper.classList.contains('searchable-select-wrapper')
                ? wrapper.querySelector('.searchable-select-trigger')
                : null;
            if (triggerBtn) {
                triggerBtn.focus();
                triggerBtn.classList.add('border-rose-400', 'ring-2', 'ring-rose-500/20');
                setTimeout(() => triggerBtn.classList.remove('border-rose-400', 'ring-2', 'ring-rose-500/20'), 2000);
            } else if (selectTugas) {
                selectTugas.focus();
            }
            return;
        }

        const namaAktivitas = selectedTugasList.map(it => it.nama).join(' • ');
        const masterIds = selectedTugasList
            .filter(it => it.id && Number(it.id) > 0)
            .map(it => Number(it.id));
        const tugasIdsStr = masterIds.join(',');
        const tugasId = masterIds.length > 0 ? String(masterIds[0]) : '';

        const formData = new FormData(form);
        formData.set('aktivitas', namaAktivitas);
        formData.set('tugas_id', tugasId);
        formData.set('tugas_ids', tugasIdsStr);

        // Pastikan jam_ids dikirim
        const jamIdsVal = document.getElementById('inp_jam_ids').value;
        if (jamIdsVal) {
            formData.set('jam_ids', jamIdsVal);
        }

        // Pengelolaan Foto Bukti:
        // Prioritas 1: Blob dari Kamera Live Browser
        if (window.__staffCapturedBlob) {
            formData.set('foto_bukti', window.__staffCapturedBlob, window.__staffCapturedFileName || 'bukti_kamera.jpg');
            formData.delete('foto_captured');
        } else {
            // Prioritas 2: File asli dari Galeri atau Kamera Native
            const inpBukti = document.getElementById('inp_foto_bukti');
            const inpFallback = document.getElementById('inp_foto_camera_fallback');
            if (inpBukti && inpBukti.files && inpBukti.files[0]) {
                formData.set('foto_bukti', inpBukti.files[0]);
                formData.delete('foto_captured');
            } else if (inpFallback && inpFallback.files && inpFallback.files[0]) {
                formData.set('foto_bukti', inpFallback.files[0]);
                formData.delete('foto_captured');
            }
        }

        // State loading di tombol
        if (typeof AndroidUI !== 'undefined' && typeof AndroidUI.setButtonLoading === 'function') {
            AndroidUI.setButtonLoading(btnSubmit, 'Menyimpan...');
        } else {
            btnSubmit.disabled = true;
            if (btnText) btnText.textContent = 'Menyimpan...';
        }

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
                stopStaffLiveCameraStream();
                setTimeout(() => {
                    window.location.reload();
                }, 350);
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
            if (typeof AndroidUI !== 'undefined' && typeof AndroidUI.resetButton === 'function') {
                AndroidUI.resetButton(btnSubmit);
            } else {
                btnSubmit.disabled = false;
                if (btnText) btnText.textContent = 'Simpan Jurnal';
            }
        }
    }

    // Auto-switch ke tab sesuai parameter URL (?tab=riwayat)
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'riwayat') {
                switchStaffTab('riwayat');
            }
        } catch (e) {}
    });
</script>
