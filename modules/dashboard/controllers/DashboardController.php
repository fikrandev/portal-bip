<?php
/**
 * Dashboard Controller
 * Main portal page showing accessible module cards
 */

class DashboardController
{
    /**
     * Show dashboard with module cards
     */
    public static function index(): void
    {
        $pageTitle = 'Dashboard';
        $pageDescription = 'Portal utama ' . APP_NAME;
        
        // Get accessible modules (skip "Dashboard" itself from cards)
        $allModules = RBAC::getAccessibleModules();
        $modules = array_filter($allModules, fn($m) => $m['slug'] !== 'dashboard');
        $modules = array_values($modules); // re-index
        $isPersonalScope = class_exists('Auth') && Auth::isPersonalScope();
        
        // Get stats (120s session-cached combined query — for Personal scope, build per-module progress dashboard)
        $totalUsers = 0;
        $totalModules = count($modules);
        $totalRoles = 0;
        $personalModuleWidgets = [];
        $personalOverallProgress = 0;

        if ($isPersonalScope) {
            $personalData = self::getPersonalDashboardProgress($modules);
            $personalModuleWidgets = $personalData['widgets'] ?? [];
            $personalOverallProgress = $personalData['overall_progress'] ?? 0;
        } else {
            $now = time();
            if (
                isset($_SESSION['_dash_stats_cache']) &&
                is_array($_SESSION['_dash_stats_cache']) &&
                ($now - ($_SESSION['_dash_stats_cache']['ts'] ?? 0)) < 120
            ) {
                $totalUsers = (int)$_SESSION['_dash_stats_cache']['users'];
                $totalModules = (int)$_SESSION['_dash_stats_cache']['modules'];
                $totalRoles = (int)$_SESSION['_dash_stats_cache']['roles'];
            } else {
                $db = Database::getInstance();
                $statsRow = $db->find(
                    "SELECT
                        (SELECT COUNT(*) FROM users WHERE is_active = 1) AS total_users,
                        (SELECT COUNT(*) FROM modules WHERE is_active = 1) AS total_modules,
                        (SELECT COUNT(*) FROM roles) AS total_roles"
                );
                $totalUsers = (int)($statsRow['total_users'] ?? 0);
                $totalModules = (int)($statsRow['total_modules'] ?? 0);
                $totalRoles = (int)($statsRow['total_roles'] ?? 0);
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['_dash_stats_cache'] = [
                        'ts' => $now,
                        'users' => $totalUsers,
                        'modules' => $totalModules,
                        'roles' => $totalRoles,
                    ];
                }
            }
        }
        
        // Extra JS for dashboard
        $extraJs = '<script src="' . asset('js/dashboard.js') . '"></script>';
        
        $hideSidebar = !$isPersonalScope;
        
        ob_start();
        include MODULES_PATH . '/dashboard/views/index.php';
        $content = ob_get_clean();
        
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Kumpulkan metrik progress personal dari setiap modul yang dapat diakses pengguna.
     * Di-cache 45 detik di session agar sangat ringan dan cepat (bisa di-refresh dengan ?refresh=1).
     */
    private static function getPersonalDashboardProgress(array $modules): array
    {
        $pegawaiId = Auth::personalPegawaiId();
        $userId = (int)(Auth::id() ?? 0);
        $slugList = array_map(fn($m) => $m['slug'] ?? '', $modules);
        $cacheKey = '_personal_dash_prog_' . $userId . '_' . $pegawaiId . '_' . md5(implode(',', $slugList));
        $forceRefresh = !empty($_GET['refresh']);
        $now = time();

        if (
            !$forceRefresh &&
            isset($_SESSION[$cacheKey]) &&
            is_array($_SESSION[$cacheKey]) &&
            ($now - (int)($_SESSION[$cacheKey]['ts'] ?? 0)) < 45
        ) {
            return $_SESSION[$cacheKey]['data'];
        }

        $db = Database::getInstance();
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $dayOfMonth = max(1, (int)date('j'));

        $widgets = [];
        $progressValues = [];

        foreach ($modules as $module) {
            $slug = $module['slug'] ?? '';
            $name = $module['name'] ?? $slug;
            $iconSvg = $module['icon_svg'] ?? '';
            $group = $module['module_group'] ?? 'Modul';
            $route = $module['route'] ?? $slug;

            $widget = [
                'slug'           => $slug,
                'name'           => $name,
                'group'          => $group,
                'icon_svg'       => $iconSvg,
                'route'          => $route,
                'color'          => 'primary',
                'progress_pct'   => 0,
                'progress_label' => 'Ringkasan Modul',
                'status_badge'   => 'Aktif',
                'headline_value' => '-',
                'headline_sub'   => '',
                'metrics'        => [],
                'breakdown'      => [],
                'actions'        => [],
            ];

            try {
                switch ($slug) {
                    case 'kelola-pegawai':
                        $pRow = $db->find("SELECT * FROM pegawai WHERE id = ? LIMIT 1", [$pegawaiId]);
                        $fieldsToCheck = ['nik', 'niy', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'no_wa', 'email', 'alamat', 'pendidikan_terakhir', 'foto'];
                        $filledCount = 0;
                        if ($pRow) {
                            foreach ($fieldsToCheck as $f) {
                                if (!empty(trim((string)($pRow[$f] ?? '')))) {
                                    $filledCount++;
                                }
                            }
                        }
                        $pct = $pRow ? (int)round(($filledCount / count($fieldsToCheck)) * 100) : 0;

                        $cntPrestasi = 0;
                        $cntPelatihan = 0;
                        $cntKarir = 0;
                        try {
                            $cntPrestasi = (int)($db->find("SELECT COUNT(*) AS c FROM pegawai_prestasi WHERE pegawai_id = ?", [$pegawaiId])['c'] ?? 0);
                        } catch (Throwable $e) {}
                        try {
                            $cntPelatihan = (int)($db->find("SELECT COUNT(*) AS c FROM pegawai_pelatihan WHERE pegawai_id = ?", [$pegawaiId])['c'] ?? 0);
                        } catch (Throwable $e) {}
                        try {
                            $cntKarir = (int)($db->find("SELECT COUNT(*) AS c FROM pegawai_karir WHERE pegawai_id = ?", [$pegawaiId])['c'] ?? 0);
                        } catch (Throwable $e) {}

                        $widget['color'] = 'indigo';
                        $widget['progress_pct'] = $pct;
                        $widget['progress_label'] = 'Kelengkapan Data & Profil';
                        $widget['status_badge'] = $pct >= 90 ? 'Profil Lengkap' : ($pct >= 60 ? 'Cukup Lengkap' : 'Perlu Dilengkapi');
                        $widget['headline_value'] = $pct . '%';
                        $widget['headline_sub'] = $filledCount . ' dari ' . count($fieldsToCheck) . ' atribut profil utama terisi';
                        $widget['metrics'] = [
                            ['label' => 'Status Kerja', 'value' => ($pRow['status_kerja'] ?? '-') ?: '-'],
                            ['label' => 'Prestasi',     'value' => $cntPrestasi . ' Data'],
                            ['label' => 'Pelatihan',    'value' => $cntPelatihan . ' Diklat'],
                            ['label' => 'Riwayat Karir','value' => $cntKarir . ' Riwayat'],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Biodata & Kontak Utama', 'pct' => $pct, 'text' => $filledCount . '/' . count($fieldsToCheck) . ' Terisi'],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Biodata Saya', 'url' => url('kelola-pegawai')],
                            ['label' => 'Prestasi',     'url' => url('kelola-pegawai/prestasi')],
                            ['label' => 'Pelatihan',    'url' => url('kelola-pegawai/pelatihan')],
                        ];
                        $progressValues[] = $pct;
                        break;

                    case 'kelola-perangkat-pembelajaran':
                        $ppParams = [];
                        $ppCond = Auth::personalPerangkatSqlCondition('pp', $ppParams);
                        $ppStats = $db->find("
                            SELECT
                                COUNT(*) AS total_docs,
                                SUM(CASE WHEN LOWER(pp.tipe) = 'cpatp' THEN 1 ELSE 0 END) AS cnt_cpatp,
                                SUM(CASE WHEN LOWER(pp.tipe) = 'prota' THEN 1 ELSE 0 END) AS cnt_prota,
                                SUM(CASE WHEN LOWER(pp.tipe) = 'prosem' THEN 1 ELSE 0 END) AS cnt_prosem,
                                SUM(CASE WHEN LOWER(pp.tipe) = 'rpp' THEN 1 ELSE 0 END) AS cnt_rpp,
                                SUM(CASE WHEN LOWER(pp.status) = 'disetujui' THEN 1 ELSE 0 END) AS cnt_approved,
                                SUM(CASE WHEN LOWER(pp.status) = 'diajukan' THEN 1 ELSE 0 END) AS cnt_pending,
                                SUM(CASE WHEN LOWER(pp.status) = 'revisi' THEN 1 ELSE 0 END) AS cnt_revisi,
                                SUM(CASE WHEN LOWER(pp.status) = 'draft' OR pp.status IS NULL OR pp.status = '' THEN 1 ELSE 0 END) AS cnt_draft
                            FROM perangkat_pembelajaran pp
                            WHERE pp.is_active = 1 AND ({$ppCond})
                        ", $ppParams) ?: [];

                        $cntCpatp    = (int)($ppStats['cnt_cpatp'] ?? 0);
                        $cntProta    = (int)($ppStats['cnt_prota'] ?? 0);
                        $cntProsem   = (int)($ppStats['cnt_prosem'] ?? 0);
                        $cntRpp      = (int)($ppStats['cnt_rpp'] ?? 0);
                        $totalDocs   = (int)($ppStats['total_docs'] ?? 0);
                        $cntApproved = (int)($ppStats['cnt_approved'] ?? 0);
                        $cntPending  = (int)($ppStats['cnt_pending'] ?? 0);
                        $cntRevisi   = (int)($ppStats['cnt_revisi'] ?? 0);

                        $typesFilled = ($cntCpatp > 0 ? 1 : 0) + ($cntProta > 0 ? 1 : 0) + ($cntProsem > 0 ? 1 : 0) + ($cntRpp > 0 ? 1 : 0);
                        $typePct = (int)round(($typesFilled / 4) * 100);
                        $approvePct = $totalDocs > 0 ? (int)round(($cntApproved / $totalDocs) * 100) : 0;
                        $overallPpPct = $totalDocs > 0 ? (int)round(($typePct * 0.6) + ($approvePct * 0.4)) : 0;

                        $cntJadwal = 0;
                        try {
                            $cntJadwal = (int)($db->find("SELECT COUNT(*) AS c FROM jadwal_pelajaran WHERE guru_id = ?", [$pegawaiId])['c'] ?? 0);
                        } catch (Throwable $e) {}

                        $widget['color'] = 'blue';
                        $widget['progress_pct'] = $overallPpPct;
                        $widget['progress_label'] = 'Kelengkapan & Verifikasi Perangkat';
                        $widget['status_badge'] = $typesFilled === 4 ? '4/4 Jenis Terisi' : ($typesFilled . '/4 Jenis Dokumen');
                        $widget['headline_value'] = $totalDocs . ' Dokumen';
                        $widget['headline_sub'] = $cntApproved . ' Disetujui • ' . $cntPending . ' Menunggu • ' . $cntRevisi . ' Revisi';
                        $widget['metrics'] = [
                            ['label' => 'CP & ATP',         'value' => $cntCpatp . ' Dok'],
                            ['label' => 'Prota',            'value' => $cntProta . ' Dok'],
                            ['label' => 'Prosem',           'value' => $cntProsem . ' Dok'],
                            ['label' => 'RPP / Modul Ajar', 'value' => $cntRpp . ' Dok'],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Kelengkapan 4 Jenis Perangkat', 'pct' => $typePct,    'text' => $typesFilled . '/4 Jenis'],
                            ['label' => 'Dokumen Disetujui Validator',   'pct' => $approvePct, 'text' => $cntApproved . '/' . $totalDocs . ' Disetujui'],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Jadwal (' . $cntJadwal . ' Sesi)', 'url' => url('kelola-perangkat-pembelajaran/jadwal')],
                            ['label' => 'CP & ATP',  'url' => url('kelola-perangkat-pembelajaran/cpatp')],
                            ['label' => 'Prosem',    'url' => url('kelola-perangkat-pembelajaran/prosem')],
                            ['label' => 'Prota',     'url' => url('kelola-perangkat-pembelajaran/prota')],
                            ['label' => 'RPP',       'url' => url('kelola-perangkat-pembelajaran/rpp')],
                        ];
                        $progressValues[] = $overallPpPct;
                        break;

                    case 'kelola-absen-pegawai':
                        $todayLog = null;
                        $monthLog = ['total' => 0, 'hadir' => 0, 'terlambat' => 0, 'izin_sakit' => 0, 'alpha' => 0];
                        try {
                            $todayLog = $db->find("SELECT * FROM absen_pegawai_log WHERE pegawai_id = ? AND tanggal = ? LIMIT 1", [$pegawaiId, $today]);
                            $mRow = $db->find("
                                SELECT
                                    COUNT(*) AS total,
                                    SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
                                    SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) AS terlambat,
                                    SUM(CASE WHEN status IN ('Izin','Sakit','Cuti','Dinas Luar') THEN 1 ELSE 0 END) AS izin_sakit,
                                    SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) AS alpha
                                FROM absen_pegawai_log
                                WHERE pegawai_id = ? AND (tanggal BETWEEN ? AND ?)
                            ", [$pegawaiId, $monthStart, $today]);
                            if ($mRow) {
                                $monthLog = $mRow;
                            }
                        } catch (Throwable $e) {}

                        // Hitung estimasi hari kerja berjalan bulan ini (Senin-Sabtu)
                        $workDaysElapsed = 0;
                        $curTs = strtotime($monthStart);
                        $endTs = strtotime($today);
                        while ($curTs <= $endTs) {
                            if ((int)date('N', $curTs) <= 6) {
                                $workDaysElapsed++;
                            }
                            $curTs = strtotime('+1 day', $curTs);
                        }
                        $workDaysElapsed = max(1, $workDaysElapsed);

                        $hadirCount = (int)($monthLog['hadir'] ?? 0) + (int)($monthLog['terlambat'] ?? 0);
                        $tepatWaktu = (int)($monthLog['hadir'] ?? 0);
                        $terlambat  = (int)($monthLog['terlambat'] ?? 0);
                        $izinSakit  = (int)($monthLog['izin_sakit'] ?? 0);
                        $pctHadir   = min(100, (int)round((($hadirCount + $izinSakit) / $workDaysElapsed) * 100));
                        $pctDisiplin = $hadirCount > 0 ? (int)round(($tepatWaktu / $hadirCount) * 100) : 100;

                        $todayStatusText = 'Belum Absen Hari Ini';
                        if ($todayLog) {
                            $jm = !empty($todayLog['jam_masuk']) ? substr($todayLog['jam_masuk'], 0, 5) : '-';
                            $jp = !empty($todayLog['jam_pulang']) ? substr($todayLog['jam_pulang'], 0, 5) : 'Belum Pulang';
                            $todayStatusText = ($todayLog['status'] ?? 'Hadir') . " (Masuk: {$jm} • Pulang: {$jp})";
                        }

                        $widget['color'] = 'emerald';
                        $widget['progress_pct'] = $pctHadir;
                        $widget['progress_label'] = 'Tingkat Kehadiran Bulan Ini';
                        $widget['status_badge'] = $todayLog ? ('Hari Ini: ' . ($todayLog['status'] ?? 'Hadir')) : 'Hari Ini: Belum Absen';
                        $widget['headline_value'] = $pctHadir . '%';
                        $widget['headline_sub'] = $todayStatusText;
                        $widget['metrics'] = [
                            ['label' => 'Tepat Waktu', 'value' => $tepatWaktu . ' Hari'],
                            ['label' => 'Terlambat',   'value' => $terlambat . ' Hari'],
                            ['label' => 'Izin/Sakit',  'value' => $izinSakit . ' Hari'],
                            ['label' => 'Hari Kerja',  'value' => $workDaysElapsed . ' Hari'],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Kehadiran Bulan Ini', 'pct' => $pctHadir,    'text' => $hadirCount . '/' . $workDaysElapsed . ' Hari Kerja'],
                            ['label' => 'Ketepatan Waktu',     'pct' => $pctDisiplin, 'text' => $tepatWaktu . '/' . max(1, $hadirCount) . ' Tepat Waktu'],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Dashboard Kehadiran', 'url' => url('kelola-absen-pegawai?tab=dashboard')],
                            ['label' => 'Lokasi & Jam Kerja',  'url' => url('kelola-absen-pegawai?tab=pegawai')],
                            ['label' => 'Rekap Presensi',      'url' => url('kelola-absen-pegawai?tab=rekap')],
                        ];
                        $progressValues[] = $pctHadir;
                        break;

                    case 'kelola-absen-siswa':
                        $asParams = [$monthStart, $monthEnd];
                        $asCond = Auth::kelasSqlCondition('p.kelas', $asParams) . ' AND (' . Auth::personalPegawaiSqlCondition('p.guru_id', $asParams) . ')';
                        $asMonth = $db->find("
                            SELECT
                                COUNT(DISTINCT p.id) AS total_sesi,
                                COUNT(d.id) AS total_records,
                                SUM(CASE WHEN d.status = 'H' THEN 1 ELSE 0 END) AS hadir,
                                SUM(CASE WHEN d.status = 'S' THEN 1 ELSE 0 END) AS sakit,
                                SUM(CASE WHEN d.status = 'I' THEN 1 ELSE 0 END) AS izin,
                                SUM(CASE WHEN d.status = 'A' THEN 1 ELSE 0 END) AS alpa
                            FROM presensi_kelas p
                            LEFT JOIN presensi_kelas_detail d ON d.presensi_kelas_id = p.id
                            WHERE (p.tanggal BETWEEN ? AND ?) AND ({$asCond})
                        ", $asParams) ?: [];

                        $tdParams = [$today];
                        $tdCond = Auth::kelasSqlCondition('p.kelas', $tdParams) . ' AND (' . Auth::personalPegawaiSqlCondition('p.guru_id', $tdParams) . ')';
                        $sesiToday = (int)($db->find("SELECT COUNT(*) AS c FROM presensi_kelas p WHERE p.tanggal = ? AND ({$tdCond})", $tdParams)['c'] ?? 0);

                        $totalSesi    = (int)($asMonth['total_sesi'] ?? 0);
                        $totalRecords = (int)($asMonth['total_records'] ?? 0);
                        $siswaHadir   = (int)($asMonth['hadir'] ?? 0);
                        $siswaSakit   = (int)($asMonth['sakit'] ?? 0);
                        $siswaIzin    = (int)($asMonth['izin'] ?? 0);
                        $siswaAlpa    = (int)($asMonth['alpa'] ?? 0);
                        $rateHadir    = $totalRecords > 0 ? (int)round(($siswaHadir / $totalRecords) * 100) : 0;

                        $widget['color'] = 'teal';
                        $widget['progress_pct'] = $rateHadir;
                        $widget['progress_label'] = 'Rata-Rata Kehadiran Siswa Bulan Ini';
                        $widget['status_badge'] = $sesiToday > 0 ? ($sesiToday . ' Sesi Hari Ini') : '0 Sesi Hari Ini';
                        $widget['headline_value'] = $totalSesi . ' Sesi';
                        $widget['headline_sub'] = 'Tingkat kehadiran siswa: ' . $rateHadir . '% dari ' . $totalRecords . ' presensi';
                        $widget['metrics'] = [
                            ['label' => 'Siswa Hadir', 'value' => $siswaHadir],
                            ['label' => 'Sakit',       'value' => $siswaSakit],
                            ['label' => 'Izin',        'value' => $siswaIzin],
                            ['label' => 'Alpa',        'value' => $siswaAlpa],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Persentase Kehadiran Siswa (H)', 'pct' => $rateHadir, 'text' => $rateHadir . '% Hadir'],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Input Absen Mapel', 'url' => url('kelola-absen-siswa/mapel')],
                            ['label' => 'Input Absen Kelas', 'url' => url('kelola-absen-siswa/kelas')],
                            ['label' => 'Rekap Absen Siswa', 'url' => url('kelola-absen-siswa/rekap')],
                        ];
                        $progressValues[] = $rateHadir;
                        break;

                    case 'kelola-jurnal-guru':
                        $jgMonth = $db->find("
                            SELECT
                                COUNT(*) AS total_jurnal,
                                SUM(CASE WHEN tanggal = ? THEN 1 ELSE 0 END) AS today_jurnal,
                                SUM(CASE WHEN status IN ('selesai','tervalidasi') THEN 1 ELSE 0 END) AS selesai_cnt
                            FROM jurnal_mengajar
                            WHERE guru_id = ? AND (tanggal BETWEEN ? AND ?)
                        ", [$today, $pegawaiId, $monthStart, $monthEnd]) ?: [];

                        $totalJurnal = (int)($jgMonth['total_jurnal'] ?? 0);
                        $todayJurnal = (int)($jgMonth['today_jurnal'] ?? 0);
                        $selesaiCnt  = (int)($jgMonth['selesai_cnt'] ?? 0);

                        $hariMap = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                        $hariIni = $hariMap[date('l')] ?? 'Senin';
                        $jadwalToday = 0;
                        $jadwalWeek  = 0;
                        try {
                            $jadwalToday = (int)($db->find("SELECT COUNT(*) AS c FROM jadwal_pelajaran WHERE guru_id = ? AND hari = ?", [$pegawaiId, $hariIni])['c'] ?? 0);
                            $jadwalWeek  = (int)($db->find("SELECT COUNT(*) AS c FROM jadwal_pelajaran WHERE guru_id = ?", [$pegawaiId])['c'] ?? 0);
                        } catch (Throwable $e) {}

                        $estTargetMonth = max(1, (int)round(($jadwalWeek / 6) * $dayOfMonth));
                        $pctJurnal = $jadwalWeek > 0
                            ? min(100, (int)round(($totalJurnal / $estTargetMonth) * 100))
                            : ($totalJurnal > 0 ? 100 : 0);
                        $pctToday = $jadwalToday > 0 ? min(100, (int)round(($todayJurnal / $jadwalToday) * 100)) : ($todayJurnal > 0 ? 100 : 0);

                        $widget['color'] = 'amber';
                        $widget['progress_pct'] = $pctJurnal;
                        $widget['progress_label'] = 'Capaian Pengisian Jurnal Mengajar';
                        $widget['status_badge'] = "Hari Ini: {$todayJurnal}/{$jadwalToday} Sesi";
                        $widget['headline_value'] = $totalJurnal . ' Jurnal';
                        $widget['headline_sub'] = $selesaiCnt . ' jurnal selesai bulan ini • ' . $jadwalWeek . ' jadwal/minggu';
                        $widget['metrics'] = [
                            ['label' => 'Hari Ini',      'value' => $todayJurnal . '/' . $jadwalToday],
                            ['label' => 'Bulan Ini',     'value' => $totalJurnal . ' Sesi'],
                            ['label' => 'Selesai',       'value' => $selesaiCnt . ' Jurnal'],
                            ['label' => 'Jadwal/Minggu', 'value' => $jadwalWeek . ' Sesi'],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Target Jurnal Hari Ini',  'pct' => $pctToday,  'text' => $todayJurnal . '/' . $jadwalToday . ' Sesi'],
                            ['label' => 'Progres Jurnal Bulan Ini','pct' => $pctJurnal, 'text' => $totalJurnal . ' Jurnal Terisi'],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Isi Jurnal Saya', 'url' => url('kelola-jurnal-guru?tab=jurnal_saya')],
                            ['label' => 'Data Jurnal',     'url' => url('kelola-jurnal-guru?tab=data')],
                            ['label' => 'Matriks Harian',  'url' => url('kelola-jurnal-guru?tab=matrix')],
                        ];
                        $progressValues[] = $pctJurnal;
                        break;

                    case 'kelola-jurnal-staff':
                        $jsStats = ['total_bulan' => 0, 'today_cnt' => 0, 'hari_isi' => 0];
                        $targetJamSesi = 1;
                        try {
                            $jsStats = $db->find("
                                SELECT
                                    COUNT(*) AS total_bulan,
                                    SUM(CASE WHEN tanggal = ? THEN 1 ELSE 0 END) AS today_cnt,
                                    COUNT(DISTINCT tanggal) AS hari_isi
                                FROM jurnal_staff
                                WHERE pegawai_id = ? AND (tanggal BETWEEN ? AND ?)
                            ", [$today, $pegawaiId, $monthStart, $monthEnd]) ?: $jsStats;
                            $targetJamSesi = max(1, (int)($db->find("SELECT COUNT(*) AS c FROM jurnal_staff_jam WHERE is_active = 1 AND tipe != 'istirahat'")['c'] ?? 1));
                        } catch (Throwable $e) {}

                        $todayCnt   = (int)($jsStats['today_cnt'] ?? 0);
                        $totalBulan = (int)($jsStats['total_bulan'] ?? 0);
                        $hariIsi    = (int)($jsStats['hari_isi'] ?? 0);
                        $pctToday   = min(100, (int)round(($todayCnt / $targetJamSesi) * 100));
                        $workDays   = max(1, (int)round($dayOfMonth * 6 / 7));
                        $pctMonth   = min(100, (int)round(($hariIsi / $workDays) * 100));

                        $widget['color'] = 'cyan';
                        $widget['progress_pct'] = $pctMonth;
                        $widget['progress_label'] = 'Disiplin Pengisian Jurnal Harian Staff';
                        $widget['status_badge'] = "Hari Ini: {$todayCnt}/{$targetJamSesi} Sesi";
                        $widget['headline_value'] = $totalBulan . ' Catatan';
                        $widget['headline_sub'] = "Aktif mengisi di {$hariIsi} hari kerja bulan ini";
                        $widget['metrics'] = [
                            ['label' => 'Sesi Hari Ini', 'value' => $todayCnt . '/' . $targetJamSesi],
                            ['label' => 'Total Catatan', 'value' => $totalBulan],
                            ['label' => 'Hari Terisi',   'value' => $hariIsi . ' Hari'],
                            ['label' => 'Capaian',       'value' => $pctMonth . '%'],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Sesi Kerja Hari Ini',     'pct' => $pctToday, 'text' => $todayCnt . '/' . $targetJamSesi . ' Sesi'],
                            ['label' => 'Kehadiran Jurnal Bulanan','pct' => $pctMonth, 'text' => $hariIsi . '/' . $workDays . ' Hari Kerja'],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Dashboard Jurnal',       'url' => url('kelola-jurnal-staff?tab=dashboard')],
                            ['label' => 'Data, Rekap & Matriks',  'url' => url('kelola-jurnal-staff?tab=data')],
                        ];
                        $progressValues[] = $pctMonth;
                        break;

                    case 'kelola-nilai':
                        $nParams = [];
                        $nUCond = Auth::unitSqlCondition('g.unit', $nParams);
                        $nStats = $db->find("
                            SELECT
                                COUNT(DISTINCT g.id) AS total_groups,
                                COUNT(nd.id) AS total_nilai,
                                COUNT(DISTINCT nd.siswa_id) AS total_siswa,
                                COALESCE(ROUND(AVG(CASE WHEN nd.nilai > 0 THEN nd.nilai END), 1), 0) AS avg_nilai
                            FROM nilai_group g
                            LEFT JOIN nilai_detail nd ON nd.group_id = g.id
                            WHERE g.deleted_at IS NULL AND g.is_active = 1 AND ({$nUCond})
                        ", $nParams) ?: [];

                        $totGroups = (int)($nStats['total_groups'] ?? 0);
                        $totNilai  = (int)($nStats['total_nilai'] ?? 0);
                        $totSiswa  = (int)($nStats['total_siswa'] ?? 0);
                        $avgNilai  = (float)($nStats['avg_nilai'] ?? 0);
                        $pctNilai  = $totGroups > 0 ? ($totNilai > 0 ? min(100, (int)round($avgNilai)) : 25) : 0;

                        $widget['color'] = 'purple';
                        $widget['progress_pct'] = $pctNilai;
                        $widget['progress_label'] = 'Progres & Rata-Rata Penilaian Siswa';
                        $widget['status_badge'] = $totGroups . ' Grup Penilaian';
                        $widget['headline_value'] = $avgNilai > 0 ? (string)$avgNilai : '0';
                        $widget['headline_sub'] = "Rata-rata nilai dari {$totSiswa} siswa ({$totNilai} entri nilai)";
                        $widget['metrics'] = [
                            ['label' => 'Grup Nilai',    'value' => $totGroups],
                            ['label' => 'Siswa Dinilai', 'value' => $totSiswa],
                            ['label' => 'Entri Nilai',   'value' => $totNilai],
                            ['label' => 'Rata-Rata',     'value' => $avgNilai],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Indeks Rata-Rata Nilai Siswa', 'pct' => min(100, (int)round($avgNilai)), 'text' => 'Rerata ' . $avgNilai],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Input Nilai Siswa', 'url' => url('kelola-nilai/group')],
                            ['label' => 'Rekap Nilai',       'url' => url('kelola-nilai/rekap')],
                        ];
                        $progressValues[] = $pctNilai;
                        break;

                    case 'kelola-ibadah-guru':
                        $ibToday = null;
                        $ibMonthDays = 0;
                        try {
                            $ibToday = $db->find("SELECT * FROM mutabaah_ibadah_guru WHERE pegawai_id = ? AND tanggal = ? LIMIT 1", [$pegawaiId, $today]);
                            $ibMonthDays = (int)($db->find("SELECT COUNT(DISTINCT tanggal) AS c FROM mutabaah_ibadah_guru WHERE pegawai_id = ? AND (tanggal BETWEEN ? AND ?)", [$pegawaiId, $monthStart, $today])['c'] ?? 0);
                        } catch (Throwable $e) {}

                        $todayDone = 0;
                        if ($ibToday) {
                            foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w) {
                                if (!empty($ibToday[$w . '_done'])) $todayDone++;
                            }
                            if (!empty($ibToday['tilawah_checked'])) $todayDone++;
                            if (!empty($ibToday['dzikir_istighfar']) || !empty($ibToday['dzikir_sholawat'])) $todayDone++;
                            if (!empty($ibToday['tadabbur_checked'])) $todayDone++;
                        }
                        $pctTodayIb = (int)round(($todayDone / 8) * 100);
                        $pctMonthIb = min(100, (int)round(($ibMonthDays / $dayOfMonth) * 100));

                        $widget['color'] = 'emerald';
                        $widget['progress_pct'] = $pctMonthIb;
                        $widget['progress_label'] = 'Konsistensi Mutaba\'ah Ibadah Bulanan';
                        $widget['status_badge'] = "Hari Ini: {$todayDone}/8 Amalan";
                        $widget['headline_value'] = $pctMonthIb . '%';
                        $widget['headline_sub'] = "Terisi {$ibMonthDays} dari {$dayOfMonth} hari di bulan ini";
                        $widget['metrics'] = [
                            ['label' => 'Hari Ini',    'value' => $todayDone . '/8 Item'],
                            ['label' => 'Hari Terisi', 'value' => $ibMonthDays . ' Hari'],
                            ['label' => 'Target Bulan','value' => $dayOfMonth . ' Hari'],
                            ['label' => 'Konsistensi', 'value' => $pctMonthIb . '%'],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Amalan Ibadah Hari Ini', 'pct' => $pctTodayIb, 'text' => $todayDone . '/8 Amalan'],
                            ['label' => 'Konsistensi Bulan Ini',  'pct' => $pctMonthIb, 'text' => $ibMonthDays . '/' . $dayOfMonth . ' Hari'],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Isi Ibadah Saya', 'url' => url('kelola-ibadah-guru?tab=ibadah_saya')],
                            ['label' => 'Rekap & Matriks', 'url' => url('kelola-ibadah-guru?tab=rekap')],
                        ];
                        $progressValues[] = $pctMonthIb;
                        break;

                    case 'kelola-cuti':
                        $saldoRow = null;
                        $cutiStats = ['total' => 0, 'pending' => 0, 'approved' => 0];
                        try {
                            $saldoRow = $db->find("SELECT * FROM cuti_saldo_pegawai WHERE pegawai_id = ? LIMIT 1", [$pegawaiId]);
                            $cutiStats = $db->find("
                                SELECT
                                    COUNT(*) AS total,
                                    SUM(CASE WHEN LOWER(status) IN ('pending','menunggu') THEN 1 ELSE 0 END) AS pending,
                                    SUM(CASE WHEN LOWER(status) IN ('disetujui','approved') THEN 1 ELSE 0 END) AS approved
                                FROM cuti_pengajuan
                                WHERE pegawai_id = ?
                            ", [$pegawaiId]) ?: $cutiStats;
                        } catch (Throwable $e) {}

                        $sisaTahunan = (int)($saldoRow['saldo_tahunan'] ?? 0);
                        $terpakai    = (int)($saldoRow['total_terpakai_tahunan'] ?? 0);
                        $totalKuota  = max(1, $sisaTahunan + $terpakai);
                        $pctSisa     = min(100, (int)round(($sisaTahunan / $totalKuota) * 100));

                        $widget['color'] = 'rose';
                        $widget['progress_pct'] = $pctSisa;
                        $widget['progress_label'] = 'Sisa Saldo Kuota Cuti Tahunan';
                        $widget['status_badge'] = ((int)($cutiStats['pending'] ?? 0)) > 0 ? ($cutiStats['pending'] . ' Pengajuan Menunggu') : 'Status Normal';
                        $widget['headline_value'] = $sisaTahunan . ' Hari';
                        $widget['headline_sub'] = "Terpakai {$terpakai} hari • " . ((int)($cutiStats['approved'] ?? 0)) . " pengajuan disetujui";
                        $widget['metrics'] = [
                            ['label' => 'Sisa Saldo', 'value' => $sisaTahunan . ' Hari'],
                            ['label' => 'Terpakai',   'value' => $terpakai . ' Hari'],
                            ['label' => 'Menunggu',   'value' => ((int)($cutiStats['pending'] ?? 0)) . ' Ajuan'],
                            ['label' => 'Disetujui',  'value' => ((int)($cutiStats['approved'] ?? 0)) . ' Ajuan'],
                        ];
                        $widget['breakdown'] = [
                            ['label' => 'Ketersediaan Kuota Cuti Tahunan', 'pct' => $pctSisa, 'text' => $sisaTahunan . ' Hari Tersisa'],
                        ];
                        $widget['actions'] = [
                            ['label' => 'Saldo Cuti Saya', 'url' => url('kelola-cuti?tab=pegawai')],
                            ['label' => 'Ajukan Cuti',     'url' => url('kelola-cuti?tab=pengajuan')],
                        ];
                        $progressValues[] = $pctSisa;
                        break;

                    default:
                        $widget['color'] = 'primary';
                        $widget['progress_pct'] = 100;
                        $widget['progress_label'] = 'Akses Modul Aktif';
                        $widget['status_badge'] = 'Siap Digunakan';
                        $widget['headline_value'] = 'Aktif';
                        $widget['headline_sub'] = $module['description'] ?? 'Akses data dan fitur modul melalui menu di bawah atau sidebar.';
                        $widget['actions'] = [
                            ['label' => 'Buka ' . $name, 'url' => url($route)],
                        ];
                        break;
                }
            } catch (Throwable $e) {
                $widget['headline_value'] = 'Aktif';
                $widget['headline_sub'] = $module['description'] ?? '';
                $widget['actions'] = [
                    ['label' => 'Buka ' . $name, 'url' => url($route)],
                ];
            }

            $widgets[] = $widget;
        }

        $overallProgress = !empty($progressValues)
            ? (int)round(array_sum($progressValues) / count($progressValues))
            : 100;

        $result = [
            'widgets'          => $widgets,
            'overall_progress' => $overallProgress,
        ];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[$cacheKey] = [
                'ts'   => $now,
                'data' => $result,
            ];
        }

        return $result;
    }
}
