<?php
/**
 * IbadahGuruController
 * Modul Kelola & Rekap Mutaba'ah Ibadah Harian Guru (Portal Web).
 * Tersinkronisasi langsung dengan Portal Guru Mobile (/mobile) melalui tabel mutabaah_ibadah_guru.
 */

class IbadahGuruController
{
    /**
     * Resolve data pegawai (guru) dari akun user yang sedang login (atau preview untuk Super Admin)
     */
    private static function resolvePersonalTeacher(?int $previewGuruId = null): array
    {
        $db = Database::getInstance();
        $user = Auth::user();
        $uid = (int)($user['id'] ?? 0);
        $pegawai = null;

        if ($previewGuruId && $previewGuruId > 0 && Auth::isSuperAdmin()) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ? LIMIT 1", [$previewGuruId]);
        }

        if (
            !$pegawai &&
            !empty($_SESSION['portal_guru_pegawai_id']) &&
            (int)($_SESSION['portal_guru_cache_uid'] ?? 0) === $uid
        ) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ? LIMIT 1", [(int)$_SESSION['portal_guru_pegawai_id']]);
        }

        if (!$pegawai && !empty($user['email'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE email = ? LIMIT 1", [$user['email']]);
        }
        if (!$pegawai && !empty($user['full_name'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE nama = ? OR nama LIKE ? LIMIT 1", [$user['full_name'], '%' . $user['full_name'] . '%']);
        }
        if (!$pegawai && !empty($user['phone'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE no_wa = ? LIMIT 1", [$user['phone']]);
        }
        if (!$pegawai && !empty($user['username'])) {
            $cleanName = str_replace(['.', '_', '-'], ' ', $user['username']);
            $pegawai = $db->find("SELECT * FROM pegawai WHERE nama LIKE ? LIMIT 1", ['%' . $cleanName . '%']);
        }

        if (!$pegawai && (Auth::isSuperAdmin() || $uid === 1)) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE id = 17 LIMIT 1");
        }

        if ($pegawai) {
            $rawJk = strtoupper(trim((string)($pegawai['jenis_kelamin'] ?? 'P')));
            $gender = ($rawJk === 'L' || str_starts_with($rawJk, 'LAKI')) ? 'L' : 'P';
            $foto = !empty($pegawai['foto']) ? Auth::resolvePhotoUrl($pegawai['foto']) : Auth::avatar();

            return [
                'id' => (int)$pegawai['id'],
                'nama' => trim(($pegawai['nama'] ?? 'Guru') . (!empty($pegawai['gelar']) ? ', ' . $pegawai['gelar'] : '')),
                'nama_polos' => (string)($pegawai['nama'] ?? 'Guru'),
                'niy' => $pegawai['niy'] ?: ($pegawai['nik'] ?: '-'),
                'jabatan' => $pegawai['jabatan'] ?: 'Tenaga Pendidik',
                'unit_tugas' => $pegawai['unit_tugas'] ?: '-',
                'gender' => $gender,
                'avatar_url' => $foto,
            ];
        }

        return [
            'id' => $uid > 0 ? $uid : 0,
            'nama' => $user['full_name'] ?? 'Guru',
            'nama_polos' => $user['full_name'] ?? 'Guru',
            'niy' => '-',
            'jabatan' => Auth::isSuperAdmin() ? 'Super Administrator' : 'Tenaga Pendidik',
            'unit_tugas' => '-',
            'gender' => 'P',
            'avatar_url' => Auth::avatar(),
        ];
    }

    /**
     * Format baris mutabaah menjadi struktur konsisten UI
     */
    private static function formatStateRow(?array $row, string $gender = 'P'): array
    {
        $normGender = (strtoupper(trim($gender)) === 'L') ? 'L' : 'P';
        $defaultLoc = $normGender === 'P' ? 'rumah' : 'masjid';

        $state = [
            'id' => (int)($row['id'] ?? 0),
            'gender' => $normGender,
            'sholat' => [],
            'sholat_count' => 0,
            'tilawah_checked' => !empty($row['tilawah_checked']),
            'tilawah_surah' => (string)($row['tilawah_surah'] ?? ''),
            'dzikir_istighfar' => !empty($row['dzikir_istighfar']),
            'dzikir_sholawat' => !empty($row['dzikir_sholawat']),
            'tadabbur_checked' => !empty($row['tadabbur_checked']),
            'tadabbur_notes' => (string)($row['tadabbur_notes'] ?? ''),
        ];

        $done = 0;
        foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w) {
            $chk = !empty($row['sholat_' . $w]);
            if ($chk) {
                $done++;
            }
            $loc = $row['sholat_' . $w . '_loc'] ?? $defaultLoc;
            if ($normGender === 'P') {
                $loc = 'rumah';
            } elseif ($loc !== 'masjid' && $loc !== 'rumah') {
                $loc = $defaultLoc;
            }
            $state['sholat'][$w] = [
                'checked' => $chk,
                'loc' => $loc,
            ];
        }
        $state['sholat_count'] = $done;
        return $state;
    }

    /**
     * Helper hitung skor kepatuhan harian (0..4) dari 1 baris mutabaah
     */
    private static function countComponentsDone(array $row): int
    {
        $sholatCount = (!empty($row['sholat_subuh']) ? 1 : 0)
            + (!empty($row['sholat_dzuhur']) ? 1 : 0)
            + (!empty($row['sholat_ashar']) ? 1 : 0)
            + (!empty($row['sholat_maghrib']) ? 1 : 0)
            + (!empty($row['sholat_isya']) ? 1 : 0);

        $score = 0;
        if ($sholatCount === 5) $score++;
        if (!empty($row['tilawah_checked'])) $score++;
        if (!empty($row['dzikir_istighfar']) && !empty($row['dzikir_sholawat'])) $score++;
        if (!empty($row['tadabbur_checked'])) $score++;
        return $score;
    }

    /**
     * Bangun seluruh dataset untuk halaman Kelola Ibadah Guru & Cetak
     */
    private static function buildDataContext(): array
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $canViewAll = RBAC::hasPermission('ibadah_guru.view')
            || RBAC::canAccessModule('kelola-ibadah-guru')
            || Auth::isSuperAdmin();
        $canManage = RBAC::hasPermission('ibadah_guru.create')
            || RBAC::hasPermission('ibadah_guru.update')
            || RBAC::hasPermission('ibadah_guru.approve')
            || Auth::isSuperAdmin();
        $canDelete = RBAC::hasPermission('ibadah_guru.delete') || Auth::isSuperAdmin();

        $defaultTab = $canViewAll ? 'dashboard' : 'ibadah_saya';
        $activeTab = trim((string)($_GET['tab'] ?? $defaultTab));
        if (!in_array($activeTab, ['ibadah_saya', 'dashboard', 'harian', 'rekap'], true)) {
            $activeTab = $defaultTab;
        }
        if (!$canViewAll && $activeTab !== 'ibadah_saya') {
            $activeTab = 'ibadah_saya';
        }

        $previewGuruId = (Auth::isSuperAdmin() && !empty($_GET['my_guru_id'])) ? (int)$_GET['my_guru_id'] : null;
        $myTeacher = self::resolvePersonalTeacher($previewGuruId);

        // Filter Umum
        $tanggal      = trim((string)($_GET['tanggal'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $tanggal = date('Y-m-d');
        }
        $search       = trim((string)($_GET['search'] ?? ''));
        $filterUnit   = trim((string)($_GET['unit'] ?? ''));
        $filterGender = strtoupper(trim((string)($_GET['gender'] ?? '')));
        if (!in_array($filterGender, ['', 'L', 'P'], true)) {
            $filterGender = '';
        }
        $filterStatus = trim((string)($_GET['status_isi'] ?? ''));

        $startDate = trim((string)($_GET['start_date'] ?? date('Y-m-01')));
        $endDate   = trim((string)($_GET['end_date'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) $startDate = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate))   $endDate = date('Y-m-d');
        if (strtotime($startDate) > strtotime($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        // 1. Personal Data (Isi Ibadah Saya)
        $myDate = date('Y-m-d');
        $myRawRow = $myTeacher['id'] > 0
            ? $db->find("SELECT * FROM mutabaah_ibadah_guru WHERE guru_id = ? AND tanggal = ? LIMIT 1", [$myTeacher['id'], $myDate])
            : null;
        $myTodayState = self::formatStateRow($myRawRow ?: null, $myTeacher['gender']);

        $myHistoryRows = [];
        if ($myTeacher['id'] > 0) {
            try {
                $myHistoryRows = $db->findAll(
                    "SELECT * FROM mutabaah_ibadah_guru WHERE guru_id = ? ORDER BY tanggal DESC LIMIT 14",
                    [$myTeacher['id']]
                );
            } catch (Throwable $e) {}
        }

        // 2. Data Semua Guru Aktif + Mutabaah pada $tanggal terpilih
        $allTeachers = [];
        try {
            $allTeachers = $pdo->query("
                SELECT p.id, p.nama, p.gelar, p.niy, p.nik, p.jenis_kelamin, p.unit_tugas, p.jabatan, p.foto
                FROM pegawai p
                WHERE (p.status_pegawai != 'Keluar' OR p.status_pegawai IS NULL) AND p.is_active = 1
                ORDER BY p.nama ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}

        $unitOptions = [];
        foreach ($allTeachers as $t) {
            $u = trim((string)($t['unit_tugas'] ?? ''));
            if ($u !== '' && !in_array($u, $unitOptions, true)) {
                $unitOptions[] = $u;
            }
        }
        sort($unitOptions);

        $dailyMap = [];
        try {
            $stmtDaily = $pdo->prepare("SELECT * FROM mutabaah_ibadah_guru WHERE tanggal = ?");
            $stmtDaily->execute([$tanggal]);
            foreach ($stmtDaily->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $dailyMap[(int)$r['guru_id']] = $r;
            }
        } catch (Throwable $e) {}

        // Hitung KPI Tanggal Terpilih
        $kpiDaily = [
            'total_guru' => count($allTeachers),
            'sudah_isi' => 0,
            'belum_isi' => 0,
            'sholat_lengkap' => 0,
            'total_sholat' => 0,
            'sholat_rumah' => 0,
            'sholat_masjid' => 0,
            'tilawah_selesai' => 0,
            'dzikir_lengkap' => 0,
            'tadabbur_selesai' => 0,
            'empat_sempurna' => 0,
        ];

        $dailyRows = [];
        foreach ($allTeachers as $t) {
            $tId = (int)$t['id'];
            $rawJk = strtoupper(trim((string)($t['jenis_kelamin'] ?? 'P')));
            $tGender = ($rawJk === 'L' || str_starts_with($rawJk, 'LAKI')) ? 'L' : 'P';
            $mRow = $dailyMap[$tId] ?? null;
            $hasFilled = !empty($mRow);

            $formatted = self::formatStateRow($mRow, $tGender);
            $komponenDone = $mRow ? self::countComponentsDone($mRow) : 0;

            if ($hasFilled) {
                $kpiDaily['sudah_isi']++;
                $kpiDaily['total_sholat'] += $formatted['sholat_count'];
                if ($formatted['sholat_count'] === 5) $kpiDaily['sholat_lengkap']++;
                foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w) {
                    if ($formatted['sholat'][$w]['checked']) {
                        if ($formatted['sholat'][$w]['loc'] === 'masjid') {
                            $kpiDaily['sholat_masjid']++;
                        } else {
                            $kpiDaily['sholat_rumah']++;
                        }
                    }
                }
                if ($formatted['tilawah_checked']) $kpiDaily['tilawah_selesai']++;
                if ($formatted['dzikir_istighfar'] && $formatted['dzikir_sholawat']) $kpiDaily['dzikir_lengkap']++;
                if ($formatted['tadabbur_checked']) $kpiDaily['tadabbur_selesai']++;
                if ($komponenDone === 4) $kpiDaily['empat_sempurna']++;
            } else {
                $kpiDaily['belum_isi']++;
            }

            // Terapkan filter untuk tabel Data Harian
            $namaFull = trim($t['nama'] . (!empty($t['gelar']) ? ', ' . $t['gelar'] : ''));
            if ($search !== '') {
                $hay = strtolower($namaFull . ' ' . ($t['niy'] ?? '') . ' ' . ($t['unit_tugas'] ?? '') . ' ' . ($t['jabatan'] ?? ''));
                if (strpos($hay, strtolower($search)) === false) {
                    continue;
                }
            }
            if ($filterUnit !== '' && trim((string)$t['unit_tugas']) !== $filterUnit) {
                continue;
            }
            if ($filterGender !== '' && $tGender !== $filterGender) {
                continue;
            }
            if ($filterStatus === 'sudah' && !$hasFilled) {
                continue;
            }
            if ($filterStatus === 'belum' && $hasFilled) {
                continue;
            }

            $dailyRows[] = [
                'pegawai_id' => $tId,
                'mutabaah_id' => (int)($mRow['id'] ?? 0),
                'nama' => $namaFull,
                'niy' => $t['niy'] ?: ($t['nik'] ?: '-'),
                'unit_tugas' => $t['unit_tugas'] ?: '-',
                'jabatan' => $t['jabatan'] ?: '-',
                'gender' => $tGender,
                'avatar_url' => !empty($t['foto']) ? Auth::resolvePhotoUrl($t['foto']) : null,
                'has_filled' => $hasFilled,
                'state' => $formatted,
                'komponen_done' => $komponenDone,
                'updated_at' => $mRow['updated_at'] ?? null,
            ];
        }

        // 3. Rekap Periode & Matriks ($startDate .. $endDate)
        $periodDates = [];
        $curTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        $dayGuard = 0;
        while ($curTs <= $endTs && $dayGuard < 45) {
            $periodDates[] = date('Y-m-d', $curTs);
            $curTs = strtotime('+1 day', $curTs);
            $dayGuard++;
        }
        $totalDaysInRange = max(1, (int)((strtotime($endDate) - strtotime($startDate)) / 86400) + 1);

        $periodRecords = [];
        try {
            $stmtPeriod = $pdo->prepare("
                SELECT * FROM mutabaah_ibadah_guru
                WHERE tanggal >= ? AND tanggal <= ?
                ORDER BY tanggal ASC
            ");
            $stmtPeriod->execute([$startDate, $endDate]);
            $periodRecords = $stmtPeriod->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}

        $byTeacherPeriod = [];
        $matrixMap = [];
        foreach ($periodRecords as $pr) {
            $gId = (int)$pr['guru_id'];
            $dt = (string)$pr['tanggal'];
            $byTeacherPeriod[$gId][] = $pr;
            $matrixMap[$gId][$dt] = [
                'done' => self::countComponentsDone($pr),
                'sholat_count' => (!empty($pr['sholat_subuh']) ? 1 : 0)
                    + (!empty($pr['sholat_dzuhur']) ? 1 : 0)
                    + (!empty($pr['sholat_ashar']) ? 1 : 0)
                    + (!empty($pr['sholat_maghrib']) ? 1 : 0)
                    + (!empty($pr['sholat_isya']) ? 1 : 0),
            ];
        }

        $rekapRows = [];
        foreach ($allTeachers as $t) {
            $tId = (int)$t['id'];
            $rawJk = strtoupper(trim((string)($t['jenis_kelamin'] ?? 'P')));
            $tGender = ($rawJk === 'L' || str_starts_with($rawJk, 'LAKI')) ? 'L' : 'P';
            $namaFull = trim($t['nama'] . (!empty($t['gelar']) ? ', ' . $t['gelar'] : ''));

            if ($search !== '') {
                $hay = strtolower($namaFull . ' ' . ($t['niy'] ?? '') . ' ' . ($t['unit_tugas'] ?? ''));
                if (strpos($hay, strtolower($search)) === false) {
                    continue;
                }
            }
            if ($filterUnit !== '' && trim((string)$t['unit_tugas']) !== $filterUnit) {
                continue;
            }
            if ($filterGender !== '' && $tGender !== $filterGender) {
                continue;
            }

            $rowsT = $byTeacherPeriod[$tId] ?? [];
            $hariMengisi = count($rowsT);
            $totalSholat = 0;
            $hariSholatLengkap = 0;
            $sholatRumah = 0;
            $sholatMasjid = 0;
            $hariTilawah = 0;
            $hariDzikir = 0;
            $hariTadabbur = 0;

            foreach ($rowsT as $r) {
                $sc = 0;
                foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w) {
                    if (!empty($r['sholat_' . $w])) {
                        $sc++;
                        $totalSholat++;
                        $loc = ($tGender === 'P') ? 'rumah' : ($r['sholat_' . $w . '_loc'] ?? 'masjid');
                        if ($loc === 'masjid') {
                            $sholatMasjid++;
                        } else {
                            $sholatRumah++;
                        }
                    }
                }
                if ($sc === 5) $hariSholatLengkap++;
                if (!empty($r['tilawah_checked'])) $hariTilawah++;
                if (!empty($r['dzikir_istighfar']) && !empty($r['dzikir_sholawat'])) $hariDzikir++;
                if (!empty($r['tadabbur_checked'])) $hariTadabbur++;
            }

            $persenSholat = min(100, round(($totalSholat / ($totalDaysInRange * 5)) * 100, 1));
            $persenKeaktifan = min(100, round(($hariMengisi / $totalDaysInRange) * 100, 1));

            $rekapRows[] = [
                'pegawai_id' => $tId,
                'nama' => $namaFull,
                'niy' => $t['niy'] ?: ($t['nik'] ?: '-'),
                'unit_tugas' => $t['unit_tugas'] ?: '-',
                'jabatan' => $t['jabatan'] ?: '-',
                'gender' => $tGender,
                'hari_mengisi' => $hariMengisi,
                'total_hari' => $totalDaysInRange,
                'total_sholat' => $totalSholat,
                'hari_sholat_lengkap' => $hariSholatLengkap,
                'sholat_rumah' => $sholatRumah,
                'sholat_masjid' => $sholatMasjid,
                'hari_tilawah' => $hariTilawah,
                'hari_dzikir' => $hariDzikir,
                'hari_tadabbur' => $hariTadabbur,
                'persen_sholat' => $persenSholat,
                'persen_keaktifan' => $persenKeaktifan,
            ];
        }

        // Urutkan rekap dari keaktifan & total sholat terbanyak
        usort($rekapRows, static function ($a, $b) {
            if ($b['hari_mengisi'] !== $a['hari_mengisi']) {
                return $b['hari_mengisi'] <=> $a['hari_mengisi'];
            }
            if ($b['total_sholat'] !== $a['total_sholat']) {
                return $b['total_sholat'] <=> $a['total_sholat'];
            }
            return strcmp($a['nama'], $b['nama']);
        });

        // Preview Guru List untuk Super Admin
        $previewGuruList = Auth::isSuperAdmin() ? $allTeachers : [];

        return [
            'activeTab' => $activeTab,
            'canViewAll' => $canViewAll,
            'canManage' => $canManage,
            'canDelete' => $canDelete,
            'myTeacher' => $myTeacher,
            'myDate' => $myDate,
            'myTodayState' => $myTodayState,
            'myHistoryRows' => $myHistoryRows,
            'tanggal' => $tanggal,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalDaysInRange' => $totalDaysInRange,
            'search' => $search,
            'filterUnit' => $filterUnit,
            'filterGender' => $filterGender,
            'filterStatus' => $filterStatus,
            'unitOptions' => $unitOptions,
            'allTeachers' => $allTeachers,
            'kpiDaily' => $kpiDaily,
            'dailyRows' => $dailyRows,
            'periodDates' => $periodDates,
            'matrixMap' => $matrixMap,
            'rekapRows' => $rekapRows,
            'previewGuruList' => $previewGuruList,
        ];
    }

    /**
     * Halaman Utama Kelola & Rekap Ibadah Harian Guru
     */
    public static function index(): void
    {
        $ctx = self::buildDataContext();
        extract($ctx);

        $pageTitle = 'Rekap & Mutaba\'ah Ibadah Guru';
        $breadcrumbs = [
            ['label' => 'Kepegawaian', 'url' => url('kelola-pegawai')],
            ['label' => 'Kelola Ibadah Guru']
        ];

        ob_start();
        include MODULES_PATH . '/kelola-ibadah-guru/views/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-ibadah-guru/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan / Perbarui Data Ibadah Harian (dari Tab Isi Ibadah Saya maupun Modal Admin)
     */
    public static function store(): void
    {
        $returnTab = trim((string)($_POST['return_tab'] ?? 'ibadah_saya'));
        if (!in_array($returnTab, ['ibadah_saya', 'dashboard', 'harian', 'rekap'], true)) {
            $returnTab = 'ibadah_saya';
        }
        $tanggalPost = trim((string)($_POST['tanggal'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalPost)) {
            $tanggalPost = date('Y-m-d');
        }

        $redirectQuery = ['tab' => $returnTab];
        if ($returnTab === 'harian' || $returnTab === 'dashboard') {
            $redirectQuery['tanggal'] = $tanggalPost;
        }
        if (!empty($_POST['preview_guru_id'])) {
            $redirectQuery['my_guru_id'] = (int)$_POST['preview_guru_id'];
        }
        $redirectUrl = url('kelola-ibadah-guru?' . http_build_query($redirectQuery));

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect($redirectUrl, 'Token keamanan tidak valid. Silakan muat ulang halaman.', 'error');
            return;
        }

        $canManage = RBAC::hasPermission('ibadah_guru.create')
            || RBAC::hasPermission('ibadah_guru.update')
            || RBAC::hasPermission('ibadah_guru.approve')
            || Auth::isSuperAdmin();

        $myTeacher = self::resolvePersonalTeacher(
            (Auth::isSuperAdmin() && !empty($_POST['preview_guru_id'])) ? (int)$_POST['preview_guru_id'] : null
        );
        $requestedGuruId = !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : $myTeacher['id'];
        $guruId = ($canManage && $requestedGuruId > 0) ? $requestedGuruId : $myTeacher['id'];

        if ($guruId <= 0) {
            Response::redirect($redirectUrl, 'Identitas guru tidak valid.', 'error');
            return;
        }

        $db = Database::getInstance();
        $pegawaiRow = $db->find("SELECT id, nama, jenis_kelamin FROM pegawai WHERE id = ? LIMIT 1", [$guruId]);
        $rawJk = strtoupper(trim((string)($pegawaiRow['jenis_kelamin'] ?? $myTeacher['gender'] ?? 'P')));
        $gender = ($rawJk === 'L' || str_starts_with($rawJk, 'LAKI')) ? 'L' : 'P';

        // Kumpulkan status 5 sholat & lokasi.
        // Aturan wajib: Guru Perempuan ('P') -> seluruh sholat otomatis 'rumah'.
        $sholatData = [];
        foreach (['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $w) {
            $chk = !empty($_POST['sholat_' . $w]) ? 1 : 0;
            $locRaw = trim((string)($_POST['sholat_' . $w . '_loc'] ?? ($gender === 'P' ? 'rumah' : 'masjid')));
            $loc = ($gender === 'P') ? 'rumah' : ($locRaw === 'masjid' ? 'masjid' : 'rumah');
            $sholatData[$w] = ['checked' => $chk, 'loc' => $loc];
        }

        $tilawahChecked = !empty($_POST['tilawah_checked']) ? 1 : 0;
        $tilawahSurah = mb_substr(trim((string)($_POST['tilawah_surah'] ?? '')), 0, 100);
        if ($tilawahChecked && $tilawahSurah === '') {
            $tilawahSurah = 'Tilawah Harian';
        }
        $dzikirIstighfar = !empty($_POST['dzikir_istighfar']) ? 1 : 0;
        $dzikirSholawat = !empty($_POST['dzikir_sholawat']) ? 1 : 0;
        $tadabburChecked = !empty($_POST['tadabbur_checked']) ? 1 : 0;
        $tadabburNotes = trim((string)($_POST['tadabbur_notes'] ?? ''));

        try {
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO mutabaah_ibadah_guru (
                    guru_id, tanggal, gender,
                    sholat_subuh, sholat_subuh_loc,
                    sholat_dzuhur, sholat_dzuhur_loc,
                    sholat_ashar, sholat_ashar_loc,
                    sholat_maghrib, sholat_maghrib_loc,
                    sholat_isya, sholat_isya_loc,
                    tilawah_checked, tilawah_surah,
                    dzikir_istighfar, dzikir_sholawat,
                    tadabbur_checked, tadabbur_notes
                ) VALUES (
                    ?, ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?
                ) ON DUPLICATE KEY UPDATE
                    gender = VALUES(gender),
                    sholat_subuh = VALUES(sholat_subuh),
                    sholat_subuh_loc = VALUES(sholat_subuh_loc),
                    sholat_dzuhur = VALUES(sholat_dzuhur),
                    sholat_dzuhur_loc = VALUES(sholat_dzuhur_loc),
                    sholat_ashar = VALUES(sholat_ashar),
                    sholat_ashar_loc = VALUES(sholat_ashar_loc),
                    sholat_maghrib = VALUES(sholat_maghrib),
                    sholat_maghrib_loc = VALUES(sholat_maghrib_loc),
                    sholat_isya = VALUES(sholat_isya),
                    sholat_isya_loc = VALUES(sholat_isya_loc),
                    tilawah_checked = VALUES(tilawah_checked),
                    tilawah_surah = VALUES(tilawah_surah),
                    dzikir_istighfar = VALUES(dzikir_istighfar),
                    dzikir_sholawat = VALUES(dzikir_sholawat),
                    tadabbur_checked = VALUES(tadabbur_checked),
                    tadabbur_notes = VALUES(tadabbur_notes),
                    updated_at = CURRENT_TIMESTAMP
            ");

            $stmt->execute([
                $guruId,
                $tanggalPost,
                $gender,
                $sholatData['subuh']['checked'], $sholatData['subuh']['loc'],
                $sholatData['dzuhur']['checked'], $sholatData['dzuhur']['loc'],
                $sholatData['ashar']['checked'], $sholatData['ashar']['loc'],
                $sholatData['maghrib']['checked'], $sholatData['maghrib']['loc'],
                $sholatData['isya']['checked'], $sholatData['isya']['loc'],
                $tilawahChecked,
                $tilawahSurah !== '' ? $tilawahSurah : null,
                $dzikirIstighfar,
                $dzikirSholawat,
                $tadabburChecked,
                $tadabburNotes !== '' ? $tadabburNotes : null,
            ]);

            $targetNama = $pegawaiRow['nama'] ?? 'Guru';
            Response::redirect($redirectUrl, "Data ibadah harian {$targetNama} (" . date('d/m/Y', strtotime($tanggalPost)) . ") berhasil disimpan.", 'success');
        } catch (Throwable $e) {
            error_log('IbadahGuruController::store error: ' . $e->getMessage());
            Response::redirect($redirectUrl, 'Gagal menyimpan data ibadah: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Hapus catatan mutabaah harian (Khusus Admin berizin delete)
     */
    public static function delete(string $id): void
    {
        $backUrl = url('kelola-ibadah-guru?tab=harian&tanggal=' . urlencode($_POST['tanggal'] ?? date('Y-m-d')));
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect($backUrl, 'Token keamanan tidak valid.', 'error');
            return;
        }

        $recordId = (int)$id;
        if ($recordId > 0) {
            try {
                Database::getInstance()->delete('mutabaah_ibadah_guru', 'id = ?', [$recordId]);
                Response::redirect($backUrl, 'Catatan mutaba\'ah ibadah berhasil dihapus.', 'success');
                return;
            } catch (Throwable $e) {
                Response::redirect($backUrl, 'Gagal menghapus catatan: ' . $e->getMessage(), 'error');
                return;
            }
        }
        Response::redirect($backUrl, 'Data tidak ditemukan.', 'error');
    }

    /**
     * Halaman Cetak Rekap Mutaba'ah Ibadah Harian Guru
     */
    public static function cetak(): void
    {
        $ctx = self::buildDataContext();
        $mode = trim((string)($_GET['mode'] ?? ($ctx['activeTab'] === 'harian' ? 'harian' : 'rekap')));
        if (!in_array($mode, ['harian', 'rekap'], true)) {
            $mode = 'rekap';
        }
        extract($ctx);
        include MODULES_PATH . '/kelola-ibadah-guru/views/cetak.php';
    }
}
