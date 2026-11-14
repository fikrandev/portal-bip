<?php
/**
 * Partial View: Isi Jurnal & Data Jurnal Saya (Personal Pegawai/Guru yang Login)
 * Digunakan oleh:
 * 1. Modul Kepegawaian (/kelola-pegawai/jurnal)
 * 2. Modul Kelola Jurnal Guru Tab Personal (/kelola-jurnal-guru?tab=jurnal_saya)
 */

$myTeacher         = $myTeacher ?? [];
$myTodayName       = $myTodayName ?? 'Senin';
$myTodayDate       = $myTodayDate ?? date('Y-m-d');
$myAllSchedules    = $myAllSchedules ?? [];
$myTodaySchedules  = $myTodaySchedules ?? [];
$myTeacherClasses  = $myTeacherClasses ?? [];
$myClassOptions    = $myClassOptions ?? [];
$mySubjectOptions  = $mySubjectOptions ?? [];
$myJournalList     = $myJournalList ?? [];
$myKpi             = $myKpi ?? ['jadwal_hari_ini' => 0, 'terisi_hari_ini' => 0, 'total_sesi_saya' => 0, 'total_jp_saya' => 0, 'kelas_diampu' => 0];
$myFilters         = $myFilters ?? ['search' => '', 'kelas' => '', 'start_date' => '', 'end_date' => ''];
$myPreviewGuruList = $myPreviewGuruList ?? [];

$personalFormAction   = $personalFormAction ?? url('kelola-pegawai/jurnal/store');
$personalFilterAction = $personalFilterAction ?? url('kelola-pegawai/jurnal');
$personalReturnUrl    = $personalReturnUrl ?? 'kelola-pegawai/jurnal';
$personalTabMode      = !empty($personalTabMode);

$fSearchName = $personalTabMode ? 'my_search' : 'search';
$fKelasName  = $personalTabMode ? 'my_kelas' : 'kelas';
$fStartName  = $personalTabMode ? 'my_start_date' : 'start_date';
$fEndName    = $personalTabMode ? 'my_end_date' : 'end_date';
$fGuruParam  = $personalTabMode ? 'my_guru_id' : 'guru_id';
$currentPreviewId = !empty($myTeacher['pegawai_id']) && !empty($myTeacher['is_preview']) ? (int)$myTeacher['pegawai_id'] : ((Auth::isSuperAdmin() && !empty($_GET[$fGuruParam])) ? (int)$_GET[$fGuruParam] : 0);

$teacherName = $myTeacher['name'] ?? Auth::name();
$nameParts = explode(' ', trim($teacherName));
$teacherInitials = '';
foreach (array_slice($nameParts, 0, 2) as $part) {
    if ($part !== '') {
        $teacherInitials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
}
if ($teacherInitials === '') $teacherInitials = 'GR';

$dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$weeklyGrouped = [];
foreach ($dayOrder as $d) {
    $weeklyGrouped[$d] = [];
}
foreach ($myAllSchedules as $schItem) {
    $dName = $schItem['hari'] ?? 'Senin';
    if (!isset($weeklyGrouped[$dName])) {
        $weeklyGrouped[$dName] = [];
    }
    $weeklyGrouped[$dName][] = $schItem;
}

$personalJournalsJsonMap = [];
foreach ($myJournalList as $jrRow) {
    $personalJournalsJsonMap[(int)$jrRow['id']] = [
        'id' => (int)$jrRow['id'],
        'jadwal_id' => !empty($jrRow['jadwal_id']) ? (int)$jrRow['jadwal_id'] : null,
        'kode_jurnal' => $jrRow['kode_jurnal'] ?? ('JRN-' . $jrRow['id']),
        'nama_guru' => $jrRow['nama_guru'] ?? $teacherName,
        'tanggal' => $jrRow['tanggal'] ?? $myTodayDate,
        'tanggal_fmt' => !empty($jrRow['tanggal']) ? date('d M Y', strtotime($jrRow['tanggal'])) : '-',
        'is_today' => (($jrRow['tanggal'] ?? '') === $myTodayDate),
        'kelas' => $jrRow['kelas'] ?? '-',
        'mata_pelajaran' => $jrRow['mata_pelajaran'] ?? '-',
        'jam_ke' => $jrRow['jam_ke'] ?? '1-2',
        'jumlah_jp' => (int)($jrRow['jumlah_jp'] ?? 2),
        'materi_pokok' => $jrRow['materi_pokok'] ?? '-',
        'uraian_kegiatan' => $jrRow['uraian_kegiatan'] ?? '',
        'catatan_kelas' => $jrRow['catatan_kelas'] ?? '',
        'kehadiran_summary' => $jrRow['kehadiran_summary'] ?? 'Semua Siswa Hadir',
        'status' => ucfirst($jrRow['status'] ?? 'selesai'),
        'ruangan' => $jrRow['ruangan'] ?? '',
    ];
}
?>

<div class="space-y-6">

    <!-- ===================================================================== -->
    <!-- PROFIL AKUN LOGIN & TOMBOL CEPAT ISI JURNAL HARI INI                   -->
    <!-- ===================================================================== -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
            <div class="flex items-start sm:items-center gap-4 min-w-0">
                <?php if (!empty($myTeacher['avatar'])): ?>
                    <img src="<?= e($myTeacher['avatar']) ?>"
                         alt="<?= e($teacherName) ?>"
                         class="w-16 h-16 rounded-2xl object-cover border-2 border-primary-100 shadow-sm shrink-0">
                <?php else: ?>
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-600 to-indigo-700 text-white font-black text-xl flex items-center justify-center shadow-md shrink-0">
                        <?= e($teacherInitials) ?>
                    </div>
                <?php endif; ?>

                <div class="min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <?= !empty($myTeacher['is_preview']) ? 'Pratinjau Data Guru' : 'Akun Sedang Login' ?>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-primary-50 text-primary-700 border border-primary-100">
                            TA <?= e($myTeacher['academicYear'] ?? 'Aktif') ?>
                        </span>
                    </div>

                    <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight truncate">
                        <?= e($teacherName) ?>
                    </h2>
                    <p class="text-xs text-slate-500 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span><strong>Jabatan:</strong> <?= e($myTeacher['role'] ?? 'Guru') ?></span>
                        <span>&bull;</span>
                        <span class="font-mono"><strong>NIY/NIK:</strong> <?= e($myTeacher['nip'] ?? '-') ?></span>
                        <?php if (!empty($myTeacher['unit_tugas']) && $myTeacher['unit_tugas'] !== '-'): ?>
                            <span>&bull;</span>
                            <span><strong>Unit:</strong> <?= e($myTeacher['unit_tugas']) ?></span>
                        <?php endif; ?>
                    </p>

                    <?php if (!empty($myTeacherClasses)): ?>
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <?php foreach ($myTeacherClasses as $cName => $cInfo): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold <?= !empty($cInfo['is_wali']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-slate-100 text-slate-700 border border-slate-200/70' ?>">
                                    <?= e($cName) ?>
                                    <span class="opacity-70 font-medium">(<?= !empty($cInfo['is_wali']) ? 'Wali Kelas' : 'Mengajar' ?>)</span>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2.5 shrink-0">
                <?php if (Auth::isSuperAdmin() && !empty($myPreviewGuruList)): ?>
                    <form method="GET" action="<?= $personalFilterAction ?>" class="flex items-center gap-2">
                        <?php if ($personalTabMode): ?>
                            <input type="hidden" name="tab" value="jurnal_saya">
                        <?php endif; ?>
                        <select name="<?= e($fGuruParam) ?>"
                                onchange="this.form.submit()"
                                title="Super Admin: Pilih akun guru untuk pratinjau/isi jurnal"
                                class="px-3 py-2.5 text-xs font-bold rounded-2xl border border-slate-200 bg-slate-50 hover:bg-white text-slate-700 focus:border-primary-500 outline-none max-w-[230px]">
                            <option value="">-- Akun Login / Default --</option>
                            <?php foreach ($myPreviewGuruList as $gOpt): ?>
                                <option value="<?= (int)$gOpt['id'] ?>" <?= ((int)($myTeacher['pegawai_id'] ?? 0) === (int)$gOpt['id']) ? 'selected' : '' ?>>
                                    <?= e($gOpt['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                <?php endif; ?>

                <button type="button"
                        onclick="openPersonalJurnalModal()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>Isi Jurnal Hari Ini</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 4 KARTU KPI PERSONAL PENGGUNA YANG LOGIN                              -->
    <!-- ===================================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-primary-600 uppercase tracking-wider">Jadwal Hari Ini</span>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                    <?= (int)($myKpi['jadwal_hari_ini'] ?? 0) ?> <span class="text-sm font-bold text-slate-400">Sesi</span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    <?= e($myTodayName) ?>, <?= date('d M Y') ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-xl shrink-0">
                📅
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Jurnal Terisi Hari Ini</span>
                <div class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1">
                    <?= (int)($myKpi['terisi_hari_ini'] ?? 0) ?>
                    <span class="text-sm font-bold text-slate-400">/ <?= (int)($myKpi['jadwal_hari_ini'] ?? 0) ?> Sesi</span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    <?php if ((int)($myKpi['jadwal_hari_ini'] ?? 0) > 0 && (int)($myKpi['terisi_hari_ini'] ?? 0) >= (int)($myKpi['jadwal_hari_ini'] ?? 0)): ?>
                        <span class="text-emerald-600 font-bold">Semua jadwal hari ini lengkap ✓</span>
                    <?php else: ?>
                        Pengisian khusus tanggal hari ini
                    <?php endif; ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                ✓
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Total Jurnal Saya</span>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                    <?= number_format((int)($myKpi['total_sesi_saya'] ?? 0)) ?> <span class="text-sm font-bold text-slate-400">Pertemuan</span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    Data riwayat milik <?= e($teacherName) ?>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                📖
            </div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Total JP & Kelas</span>
                <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">
                    <?= number_format((int)($myKpi['total_jp_saya'] ?? 0)) ?> <span class="text-sm font-bold text-slate-400">JP</span>
                </div>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    Mengampu <strong class="text-slate-700"><?= (int)($myKpi['kelas_diampu'] ?? 0) ?> Kelas</strong> aktif
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                ⏱️
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- PANEL 1: JADWAL MENGAJAR SAYA HARI INI (PENGISIAN CEPAT 1-KLIK)       -->
    <!-- ===================================================================== -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                    <span>🗓️ Jadwal Mengajar Saya Hari Ini</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary-50 text-primary-700 border border-primary-100">
                        <?= e($myTodayName) ?>, <?= date('d M Y') ?>
                    </span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Klik <strong>Isi Jurnal & Absen</strong> pada jadwal hari ini untuk mencatat materi KBM sekaligus absensi siswa kelas tersebut.
                </p>
            </div>

            <button type="button"
                    onclick="toggleWeeklyScheduleAccordion()"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 text-xs font-bold transition-all self-start sm:self-auto">
                <span>📅 Lihat Jadwal Mingguan</span>
                <span id="weekly-chevron-icon" class="transition-transform">▾</span>
            </button>
        </div>

        <?php if (!empty($myTodaySchedules)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($myTodaySchedules as $sch): ?>
                    <?php
                    $isFilled = !empty($sch['has_jurnal']);
                    $schPayload = json_encode([
                        'jadwal_id' => (int)$sch['id'],
                        'jurnal_id' => !empty($sch['jurnal_id']) ? (int)$sch['jurnal_id'] : null,
                        'class' => $sch['class'] ?? '',
                        'subject' => $sch['subject'] ?? '',
                        'jam_ke' => $sch['jam_ke'] ?? '1-2',
                        'jumlah_jp' => (int)($sch['jumlah_jp'] ?? 2),
                        'materi' => $sch['jurnal_materi'] ?? '',
                        'kegiatan' => $sch['jurnal_kegiatan'] ?? '',
                        'catatan' => $sch['jurnal_catatan'] ?? '',
                        'kehadiran' => $sch['jurnal_kehadiran'] ?? 'Semua Siswa Hadir',
                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    ?>
                    <div class="rounded-2xl border p-4 flex flex-col justify-between gap-3 transition-all <?= $isFilled ? 'bg-emerald-50/40 border-emerald-200/90' : 'bg-slate-50/70 border-slate-200 hover:border-primary-300' ?>">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-indigo-100 text-indigo-800 mb-1">
                                        <?= e($sch['class'] ?? '-') ?>
                                    </span>
                                    <h4 class="font-bold text-slate-900 text-sm leading-snug">
                                        <?= e($sch['subject'] ?? '-') ?>
                                    </h4>
                                </div>
                                <?php if ($isFilled): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 shrink-0">
                                        ✓ Sudah Diisi
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200 shrink-0">
                                        Belum Diisi
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600">
                                <span>🕒 <?= e($sch['time'] ?? '-') ?></span>
                                <span>&bull;</span>
                                <span><strong>Jam ke-<?= e($sch['jam_ke'] ?? '-') ?></strong> (<?= (int)($sch['jumlah_jp'] ?? 2) ?> JP)</span>
                                <?php if (!empty($sch['room'])): ?>
                                    <span>&bull;</span>
                                    <span>📍 <?= e($sch['room']) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($isFilled && !empty($sch['jurnal_materi'])): ?>
                                <div class="p-2.5 rounded-xl bg-white border border-emerald-100 text-[11px] space-y-0.5">
                                    <p class="font-bold text-slate-800 truncate">Materi: <?= e($sch['jurnal_materi']) ?></p>
                                    <p class="text-emerald-700 font-semibold truncate">👥 <?= e($sch['jurnal_kehadiran'] ?: 'Lengkap Hadir') ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button type="button"
                                onclick='openPersonalJurnalFromSchedule(<?= $schPayload ?>)'
                                class="w-full py-2.5 px-3 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 <?= $isFilled ? 'bg-white border border-emerald-300 text-emerald-700 hover:bg-emerald-50' : 'bg-primary-600 hover:bg-primary-700 text-white shadow-sm shadow-primary-600/20' ?>">
                            <?php if ($isFilled): ?>
                                <span>✏️ Edit Jurnal & Absen Hari Ini</span>
                            <?php else: ?>
                                <span>✍️ Isi Jurnal & Absen Sekarang</span>
                            <?php endif; ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-6 text-center space-y-2">
                <p class="text-sm font-bold text-slate-700">Tidak ada jadwal tatap muka terjadwal khusus hari <?= e($myTodayName) ?> pada grup jadwal aktif.</p>
                <p class="text-xs text-slate-500">Jika Anda mengajar kelas pengganti atau kegiatan KBM tambahan hari ini, klik tombol di bawah untuk mengisi jurnal manual.</p>
                <div class="pt-1">
                    <button type="button"
                            onclick="openPersonalJurnalModal()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold">
                        <span>+ Isi Jurnal Manual Hari Ini</span>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Accordion Jadwal Mingguan Lengkap -->
        <div id="personal-weekly-schedule-panel" class="hidden pt-4 border-t border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Seluruh Jadwal Mingguan (Senin - Sabtu)</h4>
                <span class="text-[11px] text-slate-400">Hanya jadwal hari <?= e($myTodayName) ?> yang aktif diisi</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                <?php foreach ($weeklyGrouped as $dayLabel => $dayRows): ?>
                    <?php $isTodayDay = (strcasecmp($dayLabel, $myTodayName) === 0); ?>
                    <div class="rounded-2xl border p-3.5 <?= $isTodayDay ? 'border-primary-300 bg-primary-50/30' : 'border-slate-200 bg-slate-50/50' ?>">
                        <div class="flex items-center justify-between mb-2 pb-1.5 border-b border-slate-200/70">
                            <span class="font-black text-xs <?= $isTodayDay ? 'text-primary-700' : 'text-slate-700' ?>">
                                <?= e($dayLabel) ?>
                            </span>
                            <?php if ($isTodayDay): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-600 text-white">Hari Ini</span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Terkunci (Khusus Hari H)</span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($dayRows)): ?>
                            <div class="space-y-1.5">
                                <?php foreach ($dayRows as $dSch): ?>
                                    <div class="p-2 rounded-xl bg-white border border-slate-100 text-xs flex items-center justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-800 truncate"><?= e($dSch['subject'] ?? '-') ?></p>
                                            <p class="text-[11px] text-slate-500 truncate">
                                                <?= e($dSch['class'] ?? '-') ?> &bull; Jam <?= e($dSch['jam_ke'] ?? '-') ?> (<?= (int)($dSch['jumlah_jp'] ?? 2) ?> JP)
                                            </p>
                                        </div>
                                        <?php if ($isTodayDay): ?>
                                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-lg shrink-0">Aktif</span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-slate-400 shrink-0">🔒</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-[11px] text-slate-400 italic py-2 text-center">Tidak ada jadwal</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- PANEL 2: RIWAYAT & DATA JURNAL MENGAJAR SAYA (AKUN YANG LOGIN)        -->
    <!-- ===================================================================== -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900">
                        📚 Data Jurnal Mengajar Saya
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan seluruh riwayat jurnal mengajar pribadi atas nama <strong><?= e($teacherName) ?></strong>.
                    </p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 self-start sm:self-auto">
                    <?= count($myJournalList) ?> Data Ditampilkan
                </span>
            </div>

            <!-- Filter Bar Riwayat Jurnal Saya -->
            <form method="GET" action="<?= $personalFilterAction ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <?php if ($personalTabMode): ?>
                    <input type="hidden" name="tab" value="jurnal_saya">
                <?php endif; ?>
                <?php if ($currentPreviewId > 0): ?>
                    <input type="hidden" name="<?= e($fGuruParam) ?>" value="<?= $currentPreviewId ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Kata Kunci</label>
                    <input type="text"
                           name="<?= e($fSearchName) ?>"
                           value="<?= e($myFilters['search'] ?? '') ?>"
                           placeholder="Materi, kegiatan, mapel..."
                           class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 focus:border-primary-500 outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Kelas Diampu</label>
                    <select name="<?= e($fKelasName) ?>" class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 bg-white focus:border-primary-500 outline-none">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($myClassOptions as $kOpt): ?>
                            <option value="<?= e($kOpt) ?>" <?= (($myFilters['kelas'] ?? '') === $kOpt) ? 'selected' : '' ?>>
                                <?= e($kOpt) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date"
                           name="<?= e($fStartName) ?>"
                           value="<?= e($myFilters['start_date'] ?? '') ?>"
                           class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 focus:border-primary-500 outline-none">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date"
                           name="<?= e($fEndName) ?>"
                           value="<?= e($myFilters['end_date'] ?? '') ?>"
                           class="w-full px-3 py-2 text-xs sm:text-sm rounded-xl border border-slate-200 focus:border-primary-500 outline-none">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm rounded-xl transition-all cursor-pointer">
                        Filter
                    </button>
                    <?php if (($myFilters['search'] ?? '') !== '' || ($myFilters['kelas'] ?? '') !== '' || ($myFilters['start_date'] ?? '') !== '' || ($myFilters['end_date'] ?? '') !== ''): ?>
                        <?php
                        $resetHref = $personalTabMode
                            ? url('kelola-jurnal-guru?tab=jurnal_saya' . ($currentPreviewId > 0 ? '&my_guru_id=' . $currentPreviewId : ''))
                            : url('kelola-pegawai/jurnal' . ($currentPreviewId > 0 ? '?guru_id=' . $currentPreviewId : ''));
                        ?>
                        <a href="<?= $resetHref ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs sm:text-sm rounded-xl">
                            Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200/80 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="px-4 py-3.5">Kode & Tanggal</th>
                        <th class="px-4 py-3.5">Kelas & Jam KBM</th>
                        <th class="px-4 py-3.5">Mata Pelajaran</th>
                        <th class="px-4 py-3.5">Materi & Kegiatan</th>
                        <th class="px-4 py-3.5">Presensi Siswa</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    <?php if (!empty($myJournalList)): ?>
                        <?php foreach ($myJournalList as $row): ?>
                            <?php
                            $rowId = (int)$row['id'];
                            $isRowToday = (($row['tanggal'] ?? '') === $myTodayDate);
                            $st = strtolower((string)($row['status'] ?? 'selesai'));
                            $stBadge = $st === 'tervalidasi'
                                ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                : ($st === 'draft' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-blue-100 text-blue-800 border-blue-200');
                            ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-900">
                                            <?= !empty($row['tanggal']) ? date('d M Y', strtotime($row['tanggal'])) : '-' ?>
                                        </span>
                                        <?php if ($isRowToday): ?>
                                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">Hari Ini</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-[11px] font-mono text-slate-400 mt-0.5">
                                        <?= e($row['kode_jurnal'] ?? ('JRN-' . $rowId)) ?>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        <?= e($row['kelas'] ?? '-') ?>
                                    </span>
                                    <div class="text-[11px] text-slate-500 mt-1">
                                        Jam ke-<?= e($row['jam_ke'] ?? '1') ?> &bull; <strong><?= (int)($row['jumlah_jp'] ?? 2) ?> JP</strong>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 align-top">
                                    <span class="font-bold text-slate-800"><?= e($row['mata_pelajaran'] ?? '-') ?></span>
                                    <?php if (!empty($row['ruangan'])): ?>
                                        <p class="text-[11px] text-slate-400 mt-0.5">📍 <?= e($row['ruangan']) ?></p>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-3.5 align-top max-w-xs">
                                    <p class="font-bold text-slate-900 line-clamp-1"><?= e($row['materi_pokok'] ?? '-') ?></p>
                                    <?php if (!empty($row['uraian_kegiatan'])): ?>
                                        <p class="text-xs text-slate-500 line-clamp-2 mt-0.5"><?= e($row['uraian_kegiatan']) ?></p>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-3.5 align-top">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        👥 <?= e($row['kehadiran_summary'] ?: 'Semua Siswa Hadir') ?>
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 align-top text-center whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-bold border <?= $stBadge ?>">
                                        <?= e(ucfirst($st)) ?>
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 align-top text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                                onclick="openPersonalDetailModal(<?= $rowId ?>)"
                                                class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all cursor-pointer">
                                            Detail
                                        </button>
                                        <?php if ($isRowToday): ?>
                                            <button type="button"
                                                    onclick="editPersonalJurnalToday(<?= $rowId ?>)"
                                                    class="px-2.5 py-1.5 rounded-xl bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold border border-primary-200 transition-all cursor-pointer">
                                                Edit
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="max-w-sm mx-auto space-y-2">
                                    <div class="text-3xl">📝</div>
                                    <p class="font-bold text-slate-800 text-sm">Belum ada riwayat jurnal mengajar ditemukan</p>
                                    <p class="text-xs text-slate-500">
                                        Klik tombol <strong>Isi Jurnal Hari Ini</strong> di atas atau pada jadwal mengajar hari ini untuk mulai mencatat kegiatan belajar mengajar.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: FORM ISI JURNAL & CHECKLIST PRESENSI SISWA REAL-TIME               -->
<!-- ========================================================================= -->
<div id="modal-personal-jurnal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl border border-slate-100 space-y-4 my-8 max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 id="personal-modal-title" class="font-black text-slate-900 text-base sm:text-lg">
                    Isi Jurnal Mengajar & Presensi Siswa
                </h3>
                <p class="text-xs text-slate-500">
                    Guru: <strong><?= e($teacherName) ?></strong> &bull; Berlaku khusus hari ini (<strong><?= e($myTodayName) ?>, <?= date('d/m/Y') ?></strong>)
                </p>
            </div>
            <button type="button" onclick="closePersonalJurnalModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold cursor-pointer">✕</button>
        </div>

        <form method="POST" action="<?= $personalFormAction ?>" class="space-y-4 text-xs sm:text-sm">
            <?= CSRF::field() ?>
            <input type="hidden" name="return_url" value="<?= e($personalReturnUrl) ?>">
            <input type="hidden" name="preview_guru_id" value="<?= $currentPreviewId > 0 ? $currentPreviewId : '' ?>">
            <input type="hidden" name="jurnal_id" id="personal_form_jurnal_id" value="">
            <input type="hidden" name="jadwal_id" id="personal_form_jadwal_id" value="">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mengajar (Hari H) *</label>
                    <input type="date"
                           name="tanggal"
                           id="personal_form_tanggal"
                           value="<?= e($myTodayDate) ?>"
                           min="<?= e($myTodayDate) ?>"
                           max="<?= e($myTodayDate) ?>"
                           readonly
                           required
                           class="w-full px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50/60 text-emerald-900 font-bold outline-none cursor-not-allowed">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kelas *</label>
                    <input type="text"
                           name="kelas"
                           id="personal_form_kelas"
                           required
                           list="personal-datalist-kelas"
                           onchange="onPersonalClassChanged(this.value)"
                           placeholder="Pilih atau ketik kelas..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                    <datalist id="personal-datalist-kelas">
                        <?php foreach ($myClassOptions as $kOpt): ?>
                            <option value="<?= e($kOpt) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran *</label>
                    <input type="text"
                           name="mata_pelajaran"
                           id="personal_form_mapel"
                           required
                           list="personal-datalist-mapel"
                           onchange="onPersonalMapelChanged()"
                           placeholder="Pilih / ketik mapel..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                    <datalist id="personal-datalist-mapel">
                        <?php foreach ($mySubjectOptions as $mOpt): ?>
                            <option value="<?= e($mOpt) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <?php if (!empty($myClassOptions)): ?>
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-[11px] font-bold text-slate-500">Kelas Anda:</span>
                    <?php foreach (array_slice($myClassOptions, 0, 8) as $quickClass): ?>
                        <button type="button"
                                onclick="selectPersonalQuickClass(<?= json_encode($quickClass, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-100 hover:bg-primary-50 hover:text-primary-700 text-slate-700 transition-all cursor-pointer">
                            <?= e($quickClass) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Checklist Presensi Siswa Real-Time -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl space-y-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs text-slate-800">📋 Checklist Presensi Siswa Kelas</span>
                        <span id="badge-personal-student-count" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">Pilih kelas dahulu</span>
                    </div>
                    <button type="button"
                            id="btn-personal-all-present"
                            onclick="setPersonalAllStudentsPresent()"
                            class="hidden text-[11px] font-bold px-2.5 py-1 rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-800 transition-all cursor-pointer">
                        ✓ Set Semua Hadir
                    </button>
                </div>

                <div id="container-personal-students" class="hidden max-h-52 overflow-y-auto space-y-1.5 bg-white rounded-xl p-2.5 border border-slate-200/80">
                    <!-- Populated via JS -->
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Ke</label>
                    <input type="text" name="jam_ke" id="personal_form_jam_ke" value="1-2" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah JP</label>
                    <input type="number" name="jumlah_jp" id="personal_form_jumlah_jp" value="2" min="1" max="12" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Rekap Kehadiran Siswa</label>
                    <input type="text" name="kehadiran_summary" id="personal_form_kehadiran" value="Semua Siswa Hadir" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Materi Pokok / Bahasan *</label>
                <input type="text"
                       name="materi_pokok"
                       id="personal_form_materi"
                       required
                       placeholder="Contoh: Bab 3 Operasi Hitung Bilangan Bulat"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Uraian Aktivitas / Kegiatan Belajar</label>
                <textarea name="uraian_kegiatan"
                          id="personal_form_kegiatan"
                          rows="2"
                          placeholder="Penjelasan materi, diskusi kelompok, dan evaluasi latihan soal..."
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Evaluasi Kelas (Opsional)</label>
                <input type="text"
                       name="catatan_kelas"
                       id="personal_form_catatan"
                       placeholder="Catatan perkembangan siswa atau tindak lanjut..."
                       class="w-full px-3.5 py-2 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-primary-500 outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closePersonalJurnalModal()" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 cursor-pointer">
                    Simpan Jurnal & Presensi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL JURNAL SAYA                                                 -->
<!-- ========================================================================= -->
<div id="modal-personal-jurnal-detail" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 backdrop-blur-xs p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4 my-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-black text-slate-900 text-base">Detail Jurnal Mengajar</h3>
                <p id="personal-detail-kode" class="text-xs font-mono text-primary-600 font-bold">-</p>
            </div>
            <button type="button" onclick="closePersonalDetailModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center font-bold cursor-pointer">✕</button>
        </div>

        <div id="personal-detail-body" class="space-y-3 text-xs sm:text-sm"></div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
            <button type="button" id="btn-personal-detail-edit" class="hidden px-4 py-2 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs cursor-pointer">
                ✏️ Edit Jurnal Hari Ini
            </button>
            <button type="button" onclick="closePersonalDetailModal()" class="px-4 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    const personalJournalsMap = <?= json_encode($personalJournalsJsonMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function personalEscapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function toggleWeeklyScheduleAccordion() {
        const panel = document.getElementById('personal-weekly-schedule-panel');
        const icon = document.getElementById('weekly-chevron-icon');
        if (!panel) return;
        const isHidden = panel.classList.toggle('hidden');
        if (icon) {
            icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }

    function openPersonalJurnalModal(resetFields = true) {
        const modal = document.getElementById('modal-personal-jurnal');
        if (!modal) return;
        if (resetFields) {
            document.getElementById('personal-modal-title').textContent = 'Isi Jurnal Mengajar & Presensi Siswa';
            document.getElementById('personal_form_jurnal_id').value = '';
            document.getElementById('personal_form_jadwal_id').value = '';
            document.getElementById('personal_form_kelas').value = '';
            document.getElementById('personal_form_mapel').value = '';
            document.getElementById('personal_form_jam_ke').value = '1-2';
            document.getElementById('personal_form_jumlah_jp').value = '2';
            document.getElementById('personal_form_kehadiran').value = 'Semua Siswa Hadir';
            document.getElementById('personal_form_materi').value = '';
            document.getElementById('personal_form_kegiatan').value = '';
            document.getElementById('personal_form_catatan').value = '';
            onPersonalClassChanged('');
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePersonalJurnalModal() {
        const modal = document.getElementById('modal-personal-jurnal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openPersonalJurnalFromSchedule(sch) {
        openPersonalJurnalModal(false);
        const isEdit = !!sch.jurnal_id;
        document.getElementById('personal-modal-title').textContent = isEdit
            ? 'Edit Jurnal & Presensi Hari Ini'
            : 'Isi Jurnal Mengajar & Presensi Siswa';
        document.getElementById('personal_form_jurnal_id').value = sch.jurnal_id || '';
        document.getElementById('personal_form_jadwal_id').value = sch.jadwal_id || '';
        document.getElementById('personal_form_kelas').value = sch.class || '';
        document.getElementById('personal_form_mapel').value = sch.subject || '';
        document.getElementById('personal_form_jam_ke').value = sch.jam_ke || '1-2';
        document.getElementById('personal_form_jumlah_jp').value = sch.jumlah_jp || 2;
        document.getElementById('personal_form_kehadiran').value = sch.kehadiran || 'Semua Siswa Hadir';
        document.getElementById('personal_form_materi').value = sch.materi || '';
        document.getElementById('personal_form_kegiatan').value = sch.kegiatan || '';
        document.getElementById('personal_form_catatan').value = sch.catatan || '';
        onPersonalClassChanged(sch.class || '', sch.subject || '');
    }

    function editPersonalJurnalToday(id) {
        const row = personalJournalsMap[id];
        if (!row) return;
        openPersonalJurnalFromSchedule({
            jurnal_id: row.id,
            jadwal_id: row.jadwal_id || '',
            class: row.kelas,
            subject: row.mata_pelajaran,
            jam_ke: row.jam_ke,
            jumlah_jp: row.jumlah_jp,
            materi: row.materi_pokok === '-' ? '' : row.materi_pokok,
            kegiatan: row.uraian_kegiatan || '',
            catatan: row.catatan_kelas || '',
            kehadiran: row.kehadiran_summary || 'Semua Siswa Hadir'
        });
    }

    function selectPersonalQuickClass(className) {
        const kInput = document.getElementById('personal_form_kelas');
        if (!kInput) return;
        kInput.value = className;
        onPersonalClassChanged(className);
    }

    function onPersonalMapelChanged() {
        const cls = (document.getElementById('personal_form_kelas')?.value || '').trim();
        if (cls !== '') {
            onPersonalClassChanged(cls);
        }
    }

    function onPersonalClassChanged(className, explicitMapel = null) {
        className = (className || '').trim();
        const container = document.getElementById('container-personal-students');
        const badge = document.getElementById('badge-personal-student-count');
        const btnAll = document.getElementById('btn-personal-all-present');

        if (!className) {
            if (container) {
                container.innerHTML = '';
                container.classList.add('hidden');
            }
            if (badge) {
                badge.innerText = 'Pilih kelas dahulu';
                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600';
            }
            if (btnAll) btnAll.classList.add('hidden');
            return;
        }

        const mapel = explicitMapel !== null ? explicitMapel : (document.getElementById('personal_form_mapel')?.value || '');
        const tanggal = document.getElementById('personal_form_tanggal')?.value || '';

        if (badge) {
            badge.innerText = 'Memuat siswa...';
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 animate-pulse';
        }

        const apiUrl = '<?= url("kelola-pegawai/jurnal/api-siswa") ?>?kelas='
            + encodeURIComponent(className)
            + '&mapel=' + encodeURIComponent(mapel)
            + '&tanggal=' + encodeURIComponent(tanggal);

        fetch(apiUrl)
            .then(r => r.json())
            .then(res => {
                if (res && res.success && Array.isArray(res.data) && res.data.length > 0) {
                    if (badge) {
                        badge.innerText = `${res.data.length} Siswa Terdaftar`;
                        badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800';
                    }
                    if (btnAll) btnAll.classList.remove('hidden');

                    const activeClasses = {
                        'H': 'bg-emerald-500 text-white shadow-xs',
                        'S': 'bg-blue-500 text-white shadow-xs',
                        'I': 'bg-amber-500 text-white shadow-xs',
                        'A': 'bg-rose-500 text-white shadow-xs'
                    };
                    const inactiveClass = 'bg-slate-200 text-slate-600';

                    let html = '';
                    res.data.forEach((s, idx) => {
                        const st = ['H', 'S', 'I', 'A'].includes(s.saved_status) ? s.saved_status : 'H';
                        html += `
                            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 hover:bg-slate-100/70 border border-slate-100 transition-all text-xs personal-student-row" data-student-id="${s.id}">
                                <input type="hidden" name="siswa_status[${s.id}]" id="pers_stat_val_${s.id}" value="${st}">
                                <div class="min-w-0 pr-2">
                                    <span class="font-bold text-slate-800 truncate block">${idx + 1}. ${personalEscapeHtml(s.nama)}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">NISN: ${personalEscapeHtml(s.nisn || '-')} &bull; ${personalEscapeHtml(s.jenis_kelamin || 'L')}</span>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <button type="button" onclick="setPersonalStudentStatus(${s.id}, 'H', this)" class="pers-btn-stat px-2 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer ${st === 'H' ? activeClasses['H'] : inactiveClass}">H</button>
                                    <button type="button" onclick="setPersonalStudentStatus(${s.id}, 'S', this)" class="pers-btn-stat px-2 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer ${st === 'S' ? activeClasses['S'] : inactiveClass}">S</button>
                                    <button type="button" onclick="setPersonalStudentStatus(${s.id}, 'I', this)" class="pers-btn-stat px-2 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer ${st === 'I' ? activeClasses['I'] : inactiveClass}">I</button>
                                    <button type="button" onclick="setPersonalStudentStatus(${s.id}, 'A', this)" class="pers-btn-stat px-2 py-1 rounded-lg text-[10px] font-bold transition-all cursor-pointer ${st === 'A' ? activeClasses['A'] : inactiveClass}">A</button>
                                </div>
                            </div>
                        `;
                    });

                    if (container) {
                        container.innerHTML = html;
                        container.classList.remove('hidden');
                    }
                    updatePersonalAttendanceSummary();
                } else {
                    if (badge) {
                        badge.innerText = '0 Siswa';
                        badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500';
                    }
                    if (btnAll) btnAll.classList.add('hidden');
                    if (container) {
                        container.innerHTML = '<p class="text-xs text-slate-400 text-center py-2">Tidak ada data siswa terdaftar di kelas ini.</p>';
                        container.classList.remove('hidden');
                    }
                }
            })
            .catch(() => {
                if (badge) {
                    badge.innerText = 'Gagal memuat siswa';
                    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700';
                }
            });
    }

    function setPersonalStudentStatus(studentId, status, btn) {
        const inputVal = document.getElementById(`pers_stat_val_${studentId}`);
        if (inputVal) inputVal.value = status;

        const parent = btn.parentElement;
        parent.querySelectorAll('.pers-btn-stat').forEach(b => {
            b.className = 'pers-btn-stat px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-200 text-slate-600 transition-all cursor-pointer';
        });

        const activeColors = {
            'H': 'bg-emerald-500 text-white shadow-xs',
            'S': 'bg-blue-500 text-white shadow-xs',
            'I': 'bg-amber-500 text-white shadow-xs',
            'A': 'bg-rose-500 text-white shadow-xs'
        };
        btn.className = `pers-btn-stat px-2 py-1 rounded-lg text-[10px] font-bold ${activeColors[status]} transition-all cursor-pointer`;
        updatePersonalAttendanceSummary();
    }

    function setPersonalAllStudentsPresent() {
        document.querySelectorAll('.personal-student-row').forEach(row => {
            const sId = row.getAttribute('data-student-id');
            const hBtn = row.querySelector('.pers-btn-stat:first-child');
            if (hBtn) {
                setPersonalStudentStatus(sId, 'H', hBtn);
            }
        });
    }

    function updatePersonalAttendanceSummary() {
        let h = 0, s = 0, i = 0, a = 0;
        document.querySelectorAll('.personal-student-row input[type="hidden"]').forEach(inp => {
            const v = inp.value;
            if (v === 'H') h++;
            else if (v === 'S') s++;
            else if (v === 'I') i++;
            else if (v === 'A') a++;
        });

        const total = h + s + i + a;
        const inputKehadiran = document.getElementById('personal_form_kehadiran');
        if (!inputKehadiran || total === 0) return;

        if (s + i + a === 0) {
            inputKehadiran.value = `${total}/${total} Hadir Lengkap`;
        } else {
            const parts = [`${h} Hadir`];
            if (s > 0) parts.push(`${s} Sakit`);
            if (i > 0) parts.push(`${i} Izin`);
            if (a > 0) parts.push(`${a} Alpa`);
            inputKehadiran.value = `${total} Siswa (${parts.join(', ')})`;
        }
    }

    function openPersonalDetailModal(id) {
        const d = personalJournalsMap[id];
        const modal = document.getElementById('modal-personal-jurnal-detail');
        const body = document.getElementById('personal-detail-body');
        const kodeEl = document.getElementById('personal-detail-kode');
        const editBtn = document.getElementById('btn-personal-detail-edit');
        if (!d || !modal || !body) return;

        if (kodeEl) kodeEl.textContent = d.kode_jurnal;
        body.innerHTML = `
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Nama Guru</p>
                    <p class="font-bold text-slate-900 mt-0.5">${personalEscapeHtml(d.nama_guru)}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Tanggal & Jam</p>
                    <p class="font-bold text-slate-900 mt-0.5">${personalEscapeHtml(d.tanggal_fmt)}</p>
                    <p class="text-[11px] text-primary-600 font-bold">Jam ke-${personalEscapeHtml(d.jam_ke)} (${d.jumlah_jp} JP)</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Kelas</p>
                    <p class="font-bold text-indigo-700 mt-0.5">${personalEscapeHtml(d.kelas)}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Mata Pelajaran</p>
                    <p class="font-bold text-slate-800 mt-0.5">${personalEscapeHtml(d.mata_pelajaran)}</p>
                </div>
            </div>
            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 space-y-2.5">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Materi Pokok / Bahasan</p>
                    <p class="font-bold text-slate-900 mt-0.5">${personalEscapeHtml(d.materi_pokok)}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Uraian Kegiatan Belajar</p>
                    <p class="text-slate-700 mt-0.5 whitespace-pre-line">${personalEscapeHtml(d.uraian_kegiatan || '-')}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Presensi Siswa</p>
                    <p class="font-bold text-emerald-700 mt-0.5">👥 ${personalEscapeHtml(d.kehadiran_summary)}</p>
                </div>
                ${d.catatan_kelas ? `
                <div class="pt-1.5 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-amber-700 uppercase">Catatan / Evaluasi Kelas</p>
                    <p class="text-slate-700 mt-0.5">${personalEscapeHtml(d.catatan_kelas)}</p>
                </div>` : ''}
            </div>
        `;

        if (editBtn) {
            if (d.is_today) {
                editBtn.classList.remove('hidden');
                editBtn.onclick = function() {
                    closePersonalDetailModal();
                    editPersonalJurnalToday(d.id);
                };
            } else {
                editBtn.classList.add('hidden');
                editBtn.onclick = null;
            }
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePersonalDetailModal() {
        const modal = document.getElementById('modal-personal-jurnal-detail');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
