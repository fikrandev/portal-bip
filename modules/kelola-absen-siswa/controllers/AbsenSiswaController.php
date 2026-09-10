<?php
/**
 * Controller Modul Kelola Absen Siswa
 * Menangani Dashboard Statistik, Wadah Grup Absen, Input Absen Mapel, Input Absen Kelas, Rekapitulasi & Cetak A4
 */

class AbsenSiswaController
{
    private static function renderView(string $viewName, array $data = []): void
    {
        extract($data);
        $customSidebar = MODULES_PATH . '/kelola-absen-siswa/views/sidebar.php';
        
        ob_start();
        include MODULES_PATH . '/kelola-absen-siswa/views/' . $viewName . '.php';
        $content = ob_get_clean();
        
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Dashboard Utama Kelola Absen Siswa
     */
    public static function index(): void
    {
        $db = \Database::getInstance();
        $today = date('Y-m-d');

        // Statistik Utama
        $totalSiswa = (int)$db->query("SELECT COUNT(*) FROM siswa WHERE is_active = 1")->fetchColumn();
        $totalGroups = (int)$db->query("SELECT COUNT(*) FROM absen_group WHERE deleted_at IS NULL AND is_active = 1")->fetchColumn();
        
        // Presensi Hari Ini
        $todayStats = $db->query("
            SELECT 
                COUNT(d.id) as total_presensi,
                SUM(CASE WHEN d.status = 'H' THEN 1 ELSE 0 END) as total_h,
                SUM(CASE WHEN d.status = 'S' THEN 1 ELSE 0 END) as total_s,
                SUM(CASE WHEN d.status = 'I' THEN 1 ELSE 0 END) as total_i,
                SUM(CASE WHEN d.status = 'A' THEN 1 ELSE 0 END) as total_a,
                SUM(CASE WHEN d.status = 'T' THEN 1 ELSE 0 END) as total_t
            FROM presensi_kelas p
            JOIN presensi_kelas_detail d ON d.presensi_kelas_id = p.id
            WHERE p.tanggal = '$today'
        ")->fetch() ?: ['total_presensi' => 0, 'total_h' => 0, 'total_s' => 0, 'total_i' => 0, 'total_a' => 0, 'total_t' => 0];

        $todayTotal = (int)($todayStats['total_presensi'] ?? 0);
        $todayHadir = (int)($todayStats['total_h'] ?? 0);
        $todaySakit = (int)($todayStats['total_s'] ?? 0);
        $todayIzin = (int)($todayStats['total_i'] ?? 0);
        $todayAlpa = (int)($todayStats['total_a'] ?? 0);
        $todayTerlambat = (int)($todayStats['total_t'] ?? 0);
        $attendanceRate = $todayTotal > 0 ? round(($todayHadir / $todayTotal) * 100, 1) : 0;

        // Overall Monthly Stats (Bulan ini)
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');
        $monthStats = $db->query("
            SELECT 
                COUNT(d.id) as total_presensi,
                SUM(CASE WHEN d.status = 'H' THEN 1 ELSE 0 END) as total_h,
                SUM(CASE WHEN d.status = 'S' THEN 1 ELSE 0 END) as total_s,
                SUM(CASE WHEN d.status = 'I' THEN 1 ELSE 0 END) as total_i,
                SUM(CASE WHEN d.status = 'A' THEN 1 ELSE 0 END) as total_a,
                SUM(CASE WHEN d.status = 'T' THEN 1 ELSE 0 END) as total_t
            FROM presensi_kelas p
            JOIN presensi_kelas_detail d ON d.presensi_kelas_id = p.id
            WHERE p.tanggal BETWEEN '$monthStart' AND '$monthEnd'
        ")->fetch() ?: ['total_presensi' => 0, 'total_h' => 0, 'total_s' => 0, 'total_i' => 0, 'total_a' => 0, 'total_t' => 0];

        // Chart 1: Tren Kehadiran 7 Hari Terakhir
        $chartDates = [];
        $chartHadir = [];
        $chartTidakHadir = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chartDates[] = date('d M', strtotime($d));

            $st = $db->query("
                SELECT 
                    SUM(CASE WHEN d.status = 'H' THEN 1 ELSE 0 END) as h,
                    SUM(CASE WHEN d.status IN ('S','I','A','T') THEN 1 ELSE 0 END) as non_h
                FROM presensi_kelas p
                JOIN presensi_kelas_detail d ON d.presensi_kelas_id = p.id
                WHERE p.tanggal = '$d'
            ")->fetch();
            $chartHadir[] = (int)($st['h'] ?? 0);
            $chartTidakHadir[] = (int)($st['non_h'] ?? 0);
        }

        // Chart 2: Komparasi Kehadiran per Kelas (Top 8 Kelas)
        $kelasChartRaw = $db->query("
            SELECT p.kelas,
                   SUM(CASE WHEN d.status = 'H' THEN 1 ELSE 0 END) as hadir,
                   COUNT(d.id) as total
            FROM presensi_kelas p
            JOIN presensi_kelas_detail d ON d.presensi_kelas_id = p.id
            WHERE p.kelas IS NOT NULL AND p.kelas != ''
            GROUP BY p.kelas
            ORDER BY total DESC
            LIMIT 8
        ")->fetchAll();

        $kelasChartLabels = [];
        $kelasChartRates = [];
        foreach ($kelasChartRaw as $kc) {
            $kelasChartLabels[] = $kc['kelas'];
            $rate = $kc['total'] > 0 ? round(($kc['hadir'] / $kc['total']) * 100, 1) : 0;
            $kelasChartRates[] = $rate;
        }

        // Presensi Sesi Terbaru (Recent Activities)
        $recentSessions = $db->query("
            SELECT p.*, 
                   pg.nama as nama_guru,
                   (SELECT COUNT(*) FROM presensi_kelas_detail d WHERE d.presensi_kelas_id = p.id) as total_siswa,
                   (SELECT COUNT(*) FROM presensi_kelas_detail d WHERE d.presensi_kelas_id = p.id AND d.status = 'H') as total_hadir
            FROM presensi_kelas p
            LEFT JOIN pegawai pg ON p.guru_id = pg.id
            ORDER BY p.id DESC
            LIMIT 8
        ")->fetchAll();

        self::renderView('index', [
            'pageTitle' => 'Dashboard Kelola Absen Siswa',
            'breadcrumbs' => [['label' => 'Kelola Absen Siswa']],
            'totalSiswa' => $totalSiswa,
            'totalGroups' => $totalGroups,
            'todayTotal' => $todayTotal,
            'todayHadir' => $todayHadir,
            'todaySakit' => $todaySakit,
            'todayIzin' => $todayIzin,
            'todayAlpa' => $todayAlpa,
            'todayTerlambat' => $todayTerlambat,
            'attendanceRate' => $attendanceRate,
            'monthStats' => $monthStats,
            'chartDates' => $chartDates,
            'chartHadir' => $chartHadir,
            'chartTidakHadir' => $chartTidakHadir,
            'kelasChartLabels' => $kelasChartLabels,
            'kelasChartRates' => $kelasChartRates,
            'recentSessions' => $recentSessions
        ]);
    }

    // =========================================================================
    // WADAH / GROUP ABSEN MANAGEMENT
    // =========================================================================

    public static function groupList(): void
    {
        $db = \Database::getInstance();
        $filter_status = $_GET['status'] ?? '';
        $filter_unit = $_GET['unit'] ?? '';

        $sql = "
            SELECT g.*, 
                   ta.nama_tahun,
                   pg.nama as nama_guru,
                   (SELECT COUNT(*) FROM presensi_kelas pk WHERE pk.group_id = g.id) as total_sesi
            FROM absen_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            LEFT JOIN pegawai pg ON g.guru_id = pg.id
            WHERE g.deleted_at IS NULL
        ";
        $params = [];

        if ($filter_status === 'aktif') {
            $sql .= " AND g.is_active = 1";
        } elseif ($filter_status === 'nonaktif') {
            $sql .= " AND g.is_active = 0";
        }

        if (!empty($filter_unit)) {
            $sql .= " AND g.unit = ?";
            $params[] = $filter_unit;
        }

        $sql .= " ORDER BY g.id DESC";
        $groups = $db->findAll($sql, $params);

        // Counts for tabs
        $totalAll = (int)$db->query("SELECT COUNT(*) FROM absen_group WHERE deleted_at IS NULL" . (!empty($filter_unit) ? " AND unit = '$filter_unit'" : ""))->fetchColumn();
        $totalActive = (int)$db->query("SELECT COUNT(*) FROM absen_group WHERE deleted_at IS NULL AND is_active = 1" . (!empty($filter_unit) ? " AND unit = '$filter_unit'" : ""))->fetchColumn();
        $totalInactive = (int)$db->query("SELECT COUNT(*) FROM absen_group WHERE deleted_at IS NULL AND is_active = 0" . (!empty($filter_unit) ? " AND unit = '$filter_unit'" : ""))->fetchColumn();

        self::renderView('group_list', [
            'pageTitle' => 'Daftar Wadah Grup Absen',
            'breadcrumbs' => [
                ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
                ['label' => 'Wadah Grup Absen']
            ],
            'groups' => $groups,
            'totalAll' => $totalAll,
            'totalActive' => $totalActive,
            'totalInactive' => $totalInactive
        ]);
    }

    public static function groupCreate(): void
    {
        $db = \Database::getInstance();
        $tahunList = $db->findAll("SELECT * FROM tahun_akademik ORDER BY id DESC");
        $kelasList = $db->findAll("SELECT DISTINCT kelas, jenjang FROM siswa WHERE is_active = 1 AND kelas IS NOT NULL ORDER BY jenjang, kelas");
        $guruList = $db->findAll("SELECT id, nama, gelar FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        
        self::renderView('group_create', [
            'pageTitle' => 'Buat Wadah Grup Absen Baru',
            'breadcrumbs' => [
                ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
                ['label' => 'Grup Absen', 'url' => url('kelola-absen-siswa/group')],
                ['label' => 'Buat Baru']
            ],
            'tahunList' => $tahunList,
            'kelasList' => $kelasList,
            'guruList' => $guruList
        ]);
    }

    public static function groupStore(): void
    {
        $db = \Database::getInstance();
        $unit = trim($_POST['unit'] ?? 'SD');
        $tahun_akademik_id = (int)($_POST['tahun_akademik_id'] ?? 1);
        $semester = trim($_POST['semester'] ?? 'Ganjil');
        $tipe = trim($_POST['tipe'] ?? 'mapel');
        $kelas = trim($_POST['kelas'] ?? '');
        $mata_pelajaran = trim($_POST['mata_pelajaran'] ?? '');
        $guru_id = !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null;
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tanggal_mulai = !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null;
        $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (empty($judul)) {
            $judul = "Presensi $unit " . ($tipe === 'mapel' ? "$mata_pelajaran ($kelas)" : "Kelas $kelas") . " - Sem $semester";
        }

        $db->insert('absen_group', [
            'unit' => $unit,
            'tahun_akademik_id' => $tahun_akademik_id,
            'semester' => $semester,
            'tipe' => $tipe,
            'kelas' => $kelas,
            'mata_pelajaran' => $mata_pelajaran,
            'guru_id' => $guru_id,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'is_active' => $is_active,
            'created_by' => class_exists('Auth') ? Auth::id() : null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['flash_success'] = 'Wadah Grup Absen berhasil dibuat.';
        header('Location: ' . url('kelola-absen-siswa/mapel'));
        exit;
    }

    public static function groupEdit($id): void
    {
        $db = \Database::getInstance();
        $group = $db->find("SELECT * FROM absen_group WHERE id = ? AND deleted_at IS NULL", [(int)$id]);
        if (!$group) {
            $_SESSION['flash_error'] = 'Wadah Grup Absen tidak ditemukan.';
            header('Location: ' . url('kelola-absen-siswa/mapel'));
            exit;
        }

        $tahunList = $db->findAll("SELECT * FROM tahun_akademik ORDER BY id DESC");
        $kelasList = $db->findAll("SELECT DISTINCT kelas, jenjang FROM siswa WHERE is_active = 1 AND kelas IS NOT NULL ORDER BY jenjang, kelas");
        $guruList = $db->findAll("SELECT id, nama, gelar FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        self::renderView('group_edit', [
            'pageTitle' => 'Edit Wadah Grup Absen',
            'breadcrumbs' => [
                ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
                ['label' => 'Input Absen Mapel', 'url' => url('kelola-absen-siswa/mapel')],
                ['label' => 'Edit Wadah']
            ],
            'group' => $group,
            'tahunList' => $tahunList,
            'kelasList' => $kelasList,
            'guruList' => $guruList
        ]);
    }

    public static function groupUpdate($id): void
    {
        $db = \Database::getInstance();
        $id = (int)$id;
        $group = $db->find("SELECT * FROM absen_group WHERE id = ? AND deleted_at IS NULL", [$id]);
        if (!$group) {
            $_SESSION['flash_error'] = 'Wadah Grup Absen tidak ditemukan.';
            header('Location: ' . url('kelola-absen-siswa/mapel'));
            exit;
        }

        $unit = trim($_POST['unit'] ?? 'SD');
        $tahun_akademik_id = (int)($_POST['tahun_akademik_id'] ?? 1);
        $semester = trim($_POST['semester'] ?? 'Ganjil');
        $tipe = trim($_POST['tipe'] ?? 'mapel');
        $kelas = trim($_POST['kelas'] ?? '');
        $mata_pelajaran = trim($_POST['mata_pelajaran'] ?? '');
        $guru_id = !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null;
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tanggal_mulai = !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null;
        $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $db->update('absen_group', [
            'unit' => $unit,
            'tahun_akademik_id' => $tahun_akademik_id,
            'semester' => $semester,
            'tipe' => $tipe,
            'kelas' => $kelas,
            'mata_pelajaran' => $mata_pelajaran,
            'guru_id' => $guru_id,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'is_active' => $is_active,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);

        $_SESSION['flash_success'] = 'Wadah Grup Absen berhasil diperbarui.';
        header('Location: ' . url('kelola-absen-siswa/mapel'));
        exit;
    }

    public static function groupToggleStatus($id): void
    {
        $db = \Database::getInstance();
        $id = (int)$id;
        $group = $db->find("SELECT id, is_active FROM absen_group WHERE id = ? AND deleted_at IS NULL", [$id]);
        
        if (!$group) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Grup tidak ditemukan']);
            exit;
        }

        $newStatus = $group['is_active'] ? 0 : 1;
        $db->update('absen_group', [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'is_active' => $newStatus, 'message' => 'Status berhasil diubah']);
        exit;
    }

    public static function groupDelete($id): void
    {
        $db = \Database::getInstance();
        $id = (int)$id;
        $group = $db->find("SELECT id FROM absen_group WHERE id = ? AND deleted_at IS NULL", [$id]);

        if ($group) {
            $db->update('absen_group', [
                'deleted_at' => date('Y-m-d H:i:s'),
                'is_active' => 0
            ], 'id = ?', [$id]);
            $_SESSION['flash_success'] = 'Wadah Grup Absen berhasil dihapus.';
        }

        header('Location: ' . url('kelola-absen-siswa/mapel'));
        exit;
    }

    // =========================================================================
    // INPUT ABSEN MAPEL (GURU & ADMIN FLOW + WADAH GRUP ABSEN)
    // =========================================================================

    public static function absenMapel(): void
    {
        $db = \Database::getInstance();
        $isAdmin = class_exists('Auth') && Auth::isSuperAdmin();

        $filter_status = $_GET['status'] ?? '';
        $filter_unit = $_GET['unit'] ?? '';

        // 1. Ambil list semua guru
        $guruList = $db->findAll("
            SELECT DISTINCT p.id, p.nama, p.gelar 
            FROM pegawai p
            JOIN pegawai_penugasan_mengajar m ON m.pegawai_id = p.id
            ORDER BY p.nama ASC
        ");
        if (empty($guruList)) {
            $guruList = $db->findAll("SELECT id, nama, gelar FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        }

        // 2. Deteksi Guru yang dipilih atau sedang login
        $selectedGuruId = !empty($_GET['guru_id']) ? (int)$_GET['guru_id'] : 0;
        if (!$selectedGuruId && !$isAdmin && class_exists('Auth')) {
            $userPegawai = $db->find("SELECT id FROM pegawai WHERE email = ? OR nama = ?", [Auth::user()['email'] ?? '', Auth::name() ?? '']);
            if ($userPegawai) {
                $selectedGuruId = (int)$userPegawai['id'];
            }
        }
        if (!$isAdmin && !$selectedGuruId && !empty($guruList)) {
            $selectedGuruId = (int)$guruList[0]['id'];
        }

        // 3. Ambil data guru terpilih
        $selectedGuru = null;
        if ($selectedGuruId) {
            $selectedGuru = $db->find("SELECT id, nama, gelar, jabatan FROM pegawai WHERE id = ?", [$selectedGuruId]);
        }

        // 4. Ambil Wadah Grup Absen Mapel
        $sqlGroups = "
            SELECT g.*, 
                   ta.nama_tahun,
                   pg.nama as nama_guru,
                   pg.gelar as gelar_guru,
                   (SELECT COUNT(*) FROM presensi_kelas pk WHERE pk.group_id = g.id OR (pk.kelas = g.kelas AND pk.mata_pelajaran = g.mata_pelajaran AND pk.tipe_presensi = 'mapel')) as total_sesi
            FROM absen_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            LEFT JOIN pegawai pg ON g.guru_id = pg.id
            WHERE g.deleted_at IS NULL AND g.tipe = 'mapel'
        ";
        $paramsGroups = [];

        if (!$isAdmin && $selectedGuruId) {
            $sqlGroups .= " AND (g.guru_id = ? OR g.guru_id IS NULL)";
            $paramsGroups[] = $selectedGuruId;
        } elseif ($isAdmin && $selectedGuruId) {
            $sqlGroups .= " AND g.guru_id = ?";
            $paramsGroups[] = $selectedGuruId;
        }

        if ($filter_status === 'aktif') {
            $sqlGroups .= " AND g.is_active = 1";
        } elseif ($filter_status === 'nonaktif') {
            $sqlGroups .= " AND g.is_active = 0";
        }

        if (!empty($filter_unit)) {
            $sqlGroups .= " AND g.unit = ?";
            $paramsGroups[] = $filter_unit;
        }

        $sqlGroups .= " ORDER BY g.id DESC";
        $groups = $db->findAll($sqlGroups, $paramsGroups);

        $countWhere = " WHERE deleted_at IS NULL AND tipe = 'mapel'";
        if (!empty($filter_unit)) {
            $countWhere .= " AND unit = " . $db->getConnection()->quote($filter_unit);
        }
        if (!$isAdmin && $selectedGuruId) {
            $countWhere .= " AND (guru_id = $selectedGuruId OR guru_id IS NULL)";
        } elseif ($isAdmin && $selectedGuruId) {
            $countWhere .= " AND guru_id = $selectedGuruId";
        }

        $totalAll = (int)$db->query("SELECT COUNT(*) FROM absen_group $countWhere")->fetchColumn();
        $totalActive = (int)$db->query("SELECT COUNT(*) FROM absen_group $countWhere AND is_active = 1")->fetchColumn();
        $totalInactive = (int)$db->query("SELECT COUNT(*) FROM absen_group $countWhere AND is_active = 0")->fetchColumn();

        self::renderView('absen_mapel', [
            'pageTitle' => 'Input Absen Mapel Siswa',
            'breadcrumbs' => [
                ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
                ['label' => 'Input Absen Mapel']
            ],
            'isAdmin' => $isAdmin,
            'guruList' => $guruList,
            'selectedGuruId' => $selectedGuruId,
            'selectedGuru' => $selectedGuru,
            'groups' => $groups,
            'totalAll' => $totalAll,
            'totalActive' => $totalActive,
            'totalInactive' => $totalInactive
        ]);
    }

    /**
     * Buka Wadah Absen Mapel
     * Menampilkan pilihan guru pengampu (untuk admin) dan seluruh rombel/kelas yang diampunya
     * Setiap rombel/kelas memiliki tombol langsung "⚡ Input Absen"
     */
    public static function wadahDetail($groupId): void
    {
        $db = \Database::getInstance();
        $groupId = (int)$groupId;
        $group = $db->find("
            SELECT g.*, ta.nama_tahun, pg.nama as nama_guru, pg.gelar as gelar_guru
            FROM absen_group g
            LEFT JOIN tahun_akademik ta ON g.tahun_akademik_id = ta.id
            LEFT JOIN pegawai pg ON g.guru_id = pg.id
            WHERE g.id = ? AND g.deleted_at IS NULL
        ", [$groupId]);

        if (!$group) {
            $_SESSION['flash_error'] = 'Wadah Grup Absen tidak ditemukan.';
            header('Location: ' . url('kelola-absen-siswa/mapel'));
            exit;
        }

        $isAdmin = (class_exists('Auth') && (Auth::isSuperAdmin() || Auth::hasRole('admin') || Auth::hasRole('super_admin'))) 
                || (class_exists('RBAC') && RBAC::hasPermission('absen_siswa.create'));

        // 1. Ambil daftar guru yang memiliki penugasan mengajar
        $guruList = $db->findAll("
            SELECT DISTINCT p.id, p.nama, p.gelar, p.niy, p.foto,
                   (SELECT COUNT(DISTINCT m.nama_kelas) FROM pegawai_penugasan_mengajar m WHERE m.pegawai_id = p.id AND m.nama_kelas IS NOT NULL AND m.nama_kelas != '') as total_kelas,
                   (SELECT COUNT(DISTINCT m.mata_pelajaran) FROM pegawai_penugasan_mengajar m WHERE m.pegawai_id = p.id AND m.mata_pelajaran IS NOT NULL AND m.mata_pelajaran != '') as total_mapel
            FROM pegawai p
            JOIN pegawai_penugasan_mengajar m ON m.pegawai_id = p.id
            ORDER BY p.nama ASC
        ");
        if (empty($guruList)) {
            $guruList = $db->findAll("SELECT id, nama, gelar, niy, foto, 0 as total_kelas, 0 as total_mapel FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        }

        // 2. Deteksi Guru Terpilih
        $selectedGuruId = !empty($_GET['guru_id']) ? (int)$_GET['guru_id'] : 0;
        
        // Jika bukan admin dan user adalah guru login
        if (!$selectedGuruId && !$isAdmin && class_exists('Auth')) {
            $userPegawai = $db->find("SELECT id FROM pegawai WHERE email = ? OR nama = ?", [Auth::user()['email'] ?? '', Auth::name() ?? '']);
            if ($userPegawai) {
                $selectedGuruId = (int)$userPegawai['id'];
            }
        }
        
        // Jika wadah memiliki guru_id spesifik dan belum dipilih
        if (!$selectedGuruId && !empty($group['guru_id'])) {
            $selectedGuruId = (int)$group['guru_id'];
        }

        // Fallback untuk admin: pilih guru pertama dalam daftar
        if (!$selectedGuruId && !empty($guruList)) {
            $selectedGuruId = (int)$guruList[0]['id'];
        }

        // 3. Info Guru Terpilih
        $selectedGuru = null;
        if ($selectedGuruId) {
            $selectedGuru = $db->find("SELECT id, nama, gelar, niy, foto, jabatan FROM pegawai WHERE id = ?", [$selectedGuruId]);
        }

        // 4. Ambil Semua Penugasan Mengajar (Mata Pelajaran & Kelas) Guru Ini
        $penugasanRows = [];
        if ($selectedGuruId) {
            $penugasanRows = $db->findAll("
                SELECT DISTINCT nama_kelas, mata_pelajaran, jumlah_jp
                FROM pegawai_penugasan_mengajar 
                WHERE pegawai_id = ? AND nama_kelas IS NOT NULL AND nama_kelas != ''
                ORDER BY mata_pelajaran ASC, nama_kelas ASC
            ", [$selectedGuruId]);
        }

        // Hitung statistik sesi dan siswa untuk tiap kelas
        $penugasanWithStats = [];
        foreach ($penugasanRows as $pen) {
            $cleanKelas = preg_replace('/^Kelas\s+/i', '', $pen['nama_kelas']);
            
            // Total pertemuan yang sudah dicatat
            $sesiCount = (int)$db->query("
                SELECT COUNT(*) FROM presensi_kelas 
                WHERE guru_id = {$selectedGuruId} 
                  AND mata_pelajaran = " . $db->getConnection()->quote($pen['mata_pelajaran']) . "
                  AND (kelas = " . $db->getConnection()->quote($cleanKelas) . " OR kelas = " . $db->getConnection()->quote($pen['nama_kelas']) . ")
                  AND tipe_presensi = 'mapel'
            ")->fetchColumn();

            // Total siswa di kelas
            $totalSiswa = (int)$db->query("
                SELECT COUNT(*) FROM siswa 
                WHERE (kelas = " . $db->getConnection()->quote($cleanKelas) . " OR kelas = " . $db->getConnection()->quote($pen['nama_kelas']) . " OR kelas = " . $db->getConnection()->quote('Kelas ' . $cleanKelas) . ")
                  AND is_active = 1
            ")->fetchColumn();

            // Sesi terakhir yang dicatat
            $lastSession = $db->find("
                SELECT tanggal, pertemuan_ke FROM presensi_kelas 
                WHERE guru_id = ? AND mata_pelajaran = ? AND (kelas = ? OR kelas = ?) AND tipe_presensi = 'mapel'
                ORDER BY tanggal DESC, pertemuan_ke DESC LIMIT 1
            ", [$selectedGuruId, $pen['mata_pelajaran'], $cleanKelas, $pen['nama_kelas']]);

            $penugasanWithStats[] = [
                'nama_kelas' => $pen['nama_kelas'],
                'clean_kelas' => $cleanKelas,
                'mata_pelajaran' => $pen['mata_pelajaran'],
                'jumlah_jp' => $pen['jumlah_jp'] ?? 2,
                'sesi_count' => $sesiCount,
                'total_siswa' => $totalSiswa,
                'last_session' => $lastSession
            ];
        }

        self::renderView('wadah_detail', [
            'pageTitle' => 'Input Absen: ' . $group['judul'],
            'breadcrumbs' => [
                ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
                ['label' => 'Input Absen Mapel', 'url' => url('kelola-absen-siswa/mapel')],
                ['label' => $group['judul']]
            ],
            'group' => $group,
            'groupId' => $groupId,
            'isAdmin' => $isAdmin,
            'guruList' => $guruList,
            'selectedGuruId' => $selectedGuruId,
            'selectedGuru' => $selectedGuru,
            'penugasanWithStats' => $penugasanWithStats
        ]);
    }

    public static function absenMapelForm(): void
    {
        $db = \Database::getInstance();
        $group_id = (int)($_GET['group_id'] ?? 0);
        $guru_id = (int)($_GET['guru_id'] ?? 0);
        $mapel = trim($_GET['mapel'] ?? '');
        $kelas = trim($_GET['kelas'] ?? '');
        $tanggal = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
        $pertemuan_ke = (int)($_GET['pertemuan_ke'] ?? 1);

        if (!$guru_id || empty($mapel) || empty($kelas)) {
            $_SESSION['flash_error'] = 'Silakan pilih guru, mata pelajaran, dan kelas terlebih dahulu.';
            header('Location: ' . url('kelola-absen-siswa/mapel'));
            exit;
        }

        // Info Guru
        $guru = $db->find("SELECT id, nama, gelar FROM pegawai WHERE id = ?", [$guru_id]);

        // Bersihkan nama kelas agar cocok dengan tabel siswa (e.g. 'Kelas 3 Abdurrahman' -> '3 Abdurrahman')
        $cleanKelas = preg_replace('/^Kelas\s+/i', '', $kelas);

        // Ambil daftar siswa kelas tersebut
        $siswaList = $db->findAll("
            SELECT id, nis, nisn, nama, jenis_kelamin, kelas
            FROM siswa
            WHERE is_active = 1 AND (kelas = ? OR kelas = ?)
            ORDER BY nama ASC
        ", [$cleanKelas, $kelas]);

        // Cari apakah sesi presensi untuk parameter ini sudah pernah dibuat
        $session = $db->find("
            SELECT * FROM presensi_kelas 
            WHERE guru_id = ? AND mata_pelajaran = ? AND (kelas = ? OR kelas = ?) AND tanggal = ? AND pertemuan_ke = ? AND tipe_presensi = 'mapel'
        ", [$guru_id, $mapel, $cleanKelas, $kelas, $tanggal, $pertemuan_ke]);

        $attendanceMap = [];
        if ($session) {
            $details = $db->findAll("SELECT * FROM presensi_kelas_detail WHERE presensi_kelas_id = ?", [$session['id']]);
            foreach ($details as $dt) {
                $attendanceMap[$dt['siswa_id']] = [
                    'status' => $dt['status'],
                    'catatan' => $dt['catatan']
                ];
            }
        }

        $breadcrumbs = [
            ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
            ['label' => 'Input Absen Mapel', 'url' => url('kelola-absen-siswa/mapel')]
        ];
        if ($group_id) {
            $breadcrumbs[] = ['label' => 'Wadah Absen', 'url' => url('kelola-absen-siswa/input/' . $group_id . '?guru_id=' . $guru_id)];
        }
        $breadcrumbs[] = ['label' => 'Form Presensi'];

        self::renderView('absen_mapel_form', [
            'pageTitle' => 'Input Presensi Mapel: ' . htmlspecialchars($mapel),
            'breadcrumbs' => $breadcrumbs,
            'group_id' => $group_id,
            'guru' => $guru,
            'mapel' => $mapel,
            'kelas' => $kelas,
            'cleanKelas' => $cleanKelas,
            'tanggal' => $tanggal,
            'pertemuan_ke' => $pertemuan_ke,
            'session' => $session,
            'siswaList' => $siswaList,
            'attendanceMap' => $attendanceMap
        ]);
    }

    public static function absenMapelStore(): void
    {
        $db = \Database::getInstance();
        $group_id = (int)($_POST['group_id'] ?? 0);
        $guru_id = (int)($_POST['guru_id'] ?? 0);
        $mapel = trim($_POST['mapel'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $cleanKelas = preg_replace('/^Kelas\s+/i', '', $kelas);
        $tanggal = !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');
        $pertemuan_ke = (int)($_POST['pertemuan_ke'] ?? 1);
        $jam_ke = trim($_POST['jam_ke'] ?? '1-2');
        $jp = (int)($_POST['jp'] ?? 2);
        $materi = trim($_POST['materi'] ?? '');
        $catatan_sesi = trim($_POST['catatan_sesi'] ?? '');
        $statusArr = $_POST['status'] ?? [];
        $catatanArr = $_POST['catatan'] ?? [];

        // 1. Simpan atau Update Sesi Presensi
        $session = $db->find("
            SELECT id FROM presensi_kelas 
            WHERE guru_id = ? AND mata_pelajaran = ? AND (kelas = ? OR kelas = ?) AND tanggal = ? AND pertemuan_ke = ? AND tipe_presensi = 'mapel'
        ", [$guru_id, $mapel, $cleanKelas, $kelas, $tanggal, $pertemuan_ke]);

        if ($session) {
            $sessionId = (int)$session['id'];
            $db->update('presensi_kelas', [
                'group_id' => $group_id ?: null,
                'jam_ke' => $jam_ke,
                'jp' => $jp,
                'materi' => $materi,
                'catatan' => $catatan_sesi,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$sessionId]);
        } else {
            $sessionId = $db->insert('presensi_kelas', [
                'group_id' => $group_id ?: null,
                'guru_id' => $guru_id,
                'tipe_presensi' => 'mapel',
                'kelas' => $cleanKelas,
                'mata_pelajaran' => $mapel,
                'tanggal' => $tanggal,
                'pertemuan_ke' => $pertemuan_ke,
                'jam_ke' => $jam_ke,
                'jp' => $jp,
                'materi' => $materi,
                'catatan' => $catatan_sesi,
                'role_mengajar' => 'PIC',
                'created_by' => class_exists('Auth') ? Auth::id() : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 2. Simpan Detail Siswa
        $pdo = $db->getConnection();
        $stmtInsert = $pdo->prepare("
            INSERT INTO presensi_kelas_detail (presensi_kelas_id, siswa_id, nama_siswa, status, catatan)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), catatan = VALUES(catatan)
        ");

        foreach ($statusArr as $siswaId => $st) {
            $siswaId = (int)$siswaId;
            $namaSiswa = trim($_POST['nama_siswa'][$siswaId] ?? '');
            $cat = trim($catatanArr[$siswaId] ?? '');
            $stmtInsert->execute([$sessionId, $siswaId, $namaSiswa, $st, $cat]);
        }

        $_SESSION['flash_success'] = 'Data presensi mapel berhasil disimpan.';
        header("Location: " . url("kelola-absen-siswa/mapel/input?group_id=$group_id&guru_id=$guru_id&mapel=" . urlencode($mapel) . "&kelas=" . urlencode($kelas) . "&tanggal=$tanggal&pertemuan_ke=$pertemuan_ke"));
        exit;
    }

    public static function absenMapelAutosave(): void
    {
        header('Content-Type: application/json');
        try {
            $db = \Database::getInstance();
            $data = json_decode(file_get_contents('php://input'), true);

            $group_id = (int)($data['group_id'] ?? 0);
            $guru_id = (int)($data['guru_id'] ?? 0);
            $mapel = trim($data['mapel'] ?? '');
            $kelas = trim($data['kelas'] ?? '');
            $cleanKelas = preg_replace('/^Kelas\s+/i', '', $kelas);
            $tanggal = !empty($data['tanggal']) ? $data['tanggal'] : date('Y-m-d');
            $pertemuan_ke = (int)($data['pertemuan_ke'] ?? 1);
            $jam_ke = trim($data['jam_ke'] ?? '1-2');
            $jp = (int)($data['jp'] ?? 2);
            $materi = trim($data['materi'] ?? '');
            $siswa_id = (int)($data['siswa_id'] ?? 0);
            $nama_siswa = trim($data['nama_siswa'] ?? '');
            $status = in_array($data['status'] ?? '', ['H','S','I','A','T']) ? $data['status'] : 'H';
            $catatan = trim($data['catatan'] ?? '');

            if (!$guru_id || !$siswa_id) {
                echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
                exit;
            }

            // Cari / Buat Sesi
            $session = $db->find("
                SELECT id FROM presensi_kelas 
                WHERE guru_id = ? AND mata_pelajaran = ? AND (kelas = ? OR kelas = ?) AND tanggal = ? AND pertemuan_ke = ? AND tipe_presensi = 'mapel'
            ", [$guru_id, $mapel, $cleanKelas, $kelas, $tanggal, $pertemuan_ke]);

            if ($session) {
                $sessionId = (int)$session['id'];
                if ($group_id) {
                    $db->update('presensi_kelas', ['group_id' => $group_id], 'id = ?', [$sessionId]);
                }
            } else {
                $sessionId = $db->insert('presensi_kelas', [
                    'group_id' => $group_id ?: null,
                    'guru_id' => $guru_id,
                    'tipe_presensi' => 'mapel',
                    'kelas' => $cleanKelas,
                    'mata_pelajaran' => $mapel,
                    'tanggal' => $tanggal,
                    'pertemuan_ke' => $pertemuan_ke,
                    'jam_ke' => $jam_ke,
                    'jp' => $jp,
                    'materi' => $materi,
                    'role_mengajar' => 'PIC',
                    'created_by' => class_exists('Auth') ? Auth::id() : null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Simpan / Update Detail Siswa
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO presensi_kelas_detail (presensi_kelas_id, siswa_id, nama_siswa, status, catatan)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status), catatan = VALUES(catatan)
            ");
            $stmt->execute([$sessionId, $siswa_id, $nama_siswa, $status, $catatan]);

            echo json_encode(['success' => true, 'session_id' => $sessionId, 'message' => 'Presensi tersimpan']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public static function absenMapelCetak(): void
    {
        $db = \Database::getInstance();
        $guru_id = (int)($_GET['guru_id'] ?? 0);
        $mapel = trim($_GET['mapel'] ?? '');
        $kelas = trim($_GET['kelas'] ?? '');
        $cleanKelas = preg_replace('/^Kelas\s+/i', '', $kelas);
        $tanggal = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
        $pertemuan_ke = (int)($_GET['pertemuan_ke'] ?? 1);

        $guru = $db->find("SELECT * FROM pegawai WHERE id = ?", [$guru_id]);
        $session = $db->find("
            SELECT * FROM presensi_kelas 
            WHERE guru_id = ? AND mata_pelajaran = ? AND (kelas = ? OR kelas = ?) AND tanggal = ? AND pertemuan_ke = ? AND tipe_presensi = 'mapel'
        ", [$guru_id, $mapel, $cleanKelas, $kelas, $tanggal, $pertemuan_ke]);

        $siswaList = $db->findAll("
            SELECT s.id, s.nis, s.nama, s.jenis_kelamin,
                   COALESCE(d.status, 'A') as status,
                   d.catatan
            FROM siswa s
            LEFT JOIN presensi_kelas_detail d ON d.siswa_id = s.id AND d.presensi_kelas_id = ?
            WHERE s.is_active = 1 AND (s.kelas = ? OR s.kelas = ?)
            ORDER BY s.nama ASC
        ", [$session['id'] ?? 0, $cleanKelas, $kelas]);

        include MODULES_PATH . '/kelola-absen-siswa/views/cetak_mapel.php';
        exit;
    }

    // =========================================================================
    // INPUT ABSEN KELAS (WALI KELAS & ADMIN FLOW)
    // =========================================================================

    public static function absenKelas(): void
    {
        $db = \Database::getInstance();
        $isAdmin = class_exists('Auth') && Auth::isSuperAdmin();

        // Daftar Kelas Aktif
        $kelasList = $db->findAll("
            SELECT DISTINCT kelas, jenjang 
            FROM siswa 
            WHERE is_active = 1 AND kelas IS NOT NULL AND kelas != ''
            ORDER BY jenjang, kelas
        ");

        $selectedKelas = trim($_GET['kelas'] ?? '');
        if (empty($selectedKelas) && !empty($kelasList)) {
            $selectedKelas = $kelasList[0]['kelas'];
        }

        $tanggal = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

        // Riwayat Presensi Harian Kelas Ini
        $recentKelasSessions = [];
        if (!empty($selectedKelas)) {
            $recentKelasSessions = $db->findAll("
                SELECT p.*,
                       (SELECT COUNT(*) FROM presensi_kelas_detail d WHERE d.presensi_kelas_id = p.id) as total_siswa,
                       (SELECT COUNT(*) FROM presensi_kelas_detail d WHERE d.presensi_kelas_id = p.id AND d.status = 'H') as total_hadir
                FROM presensi_kelas p
                WHERE (p.kelas = ? OR p.kelas = ?) AND p.tipe_presensi = 'kelas'
                ORDER BY p.tanggal DESC, p.id DESC
                LIMIT 10
            ", [$selectedKelas, "Kelas $selectedKelas"]);
        }

        self::renderView('absen_kelas', [
            'pageTitle' => 'Input Absen Kelas (Harian / Wali Kelas)',
            'breadcrumbs' => [
                ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
                ['label' => 'Input Absen Kelas']
            ],
            'isAdmin' => $isAdmin,
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'tanggal' => $tanggal,
            'recentKelasSessions' => $recentKelasSessions
        ]);
    }

    public static function absenKelasForm(): void
    {
        $db = \Database::getInstance();
        $kelas = trim($_GET['kelas'] ?? '');
        $cleanKelas = preg_replace('/^Kelas\s+/i', '', $kelas);
        $tanggal = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

        if (empty($kelas)) {
            $_SESSION['flash_error'] = 'Silakan pilih kelas terlebih dahulu.';
            header('Location: ' . url('kelola-absen-siswa/kelas'));
            exit;
        }

        // Ambil daftar siswa kelas tersebut
        $siswaList = $db->findAll("
            SELECT id, nis, nisn, nama, jenis_kelamin, kelas
            FROM siswa
            WHERE is_active = 1 AND (kelas = ? OR kelas = ?)
            ORDER BY nama ASC
        ", [$cleanKelas, $kelas]);

        // Cek sesi harian kelas
        $session = $db->find("
            SELECT * FROM presensi_kelas 
            WHERE (kelas = ? OR kelas = ?) AND tanggal = ? AND tipe_presensi = 'kelas'
        ", [$cleanKelas, $kelas, $tanggal]);

        $attendanceMap = [];
        if ($session) {
            $details = $db->findAll("SELECT * FROM presensi_kelas_detail WHERE presensi_kelas_id = ?", [$session['id']]);
            foreach ($details as $dt) {
                $attendanceMap[$dt['siswa_id']] = [
                    'status' => $dt['status'],
                    'catatan' => $dt['catatan']
                ];
            }
        }

        self::renderView('absen_kelas_form', [
            'pageTitle' => 'Presensi Harian Kelas ' . htmlspecialchars($cleanKelas),
            'breadcrumbs' => [
                ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
                ['label' => 'Input Absen Kelas', 'url' => url('kelola-absen-siswa/kelas?kelas=' . urlencode($cleanKelas))],
                ['label' => 'Form Presensi Harian']
            ],
            'kelas' => $kelas,
            'cleanKelas' => $cleanKelas,
            'tanggal' => $tanggal,
            'session' => $session,
            'siswaList' => $siswaList,
            'attendanceMap' => $attendanceMap
        ]);
    }

    public static function absenKelasStore(): void
    {
        $db = \Database::getInstance();
        $kelas = trim($_POST['kelas'] ?? '');
        $cleanKelas = preg_replace('/^Kelas\s+/i', '', $kelas);
        $tanggal = !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');
        $catatan_sesi = trim($_POST['catatan_sesi'] ?? '');
        $statusArr = $_POST['status'] ?? [];
        $catatanArr = $_POST['catatan'] ?? [];

        // 1. Simpan atau Update Sesi Presensi Kelas
        $session = $db->find("
            SELECT id FROM presensi_kelas 
            WHERE (kelas = ? OR kelas = ?) AND tanggal = ? AND tipe_presensi = 'kelas'
        ", [$cleanKelas, $kelas, $tanggal]);

        if ($session) {
            $sessionId = (int)$session['id'];
            $db->update('presensi_kelas', [
                'catatan' => $catatan_sesi,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$sessionId]);
        } else {
            $sessionId = $db->insert('presensi_kelas', [
                'guru_id' => class_exists('Auth') ? Auth::id() : 1,
                'tipe_presensi' => 'kelas',
                'kelas' => $cleanKelas,
                'mata_pelajaran' => 'Presensi Harian / Wali Kelas',
                'tanggal' => $tanggal,
                'pertemuan_ke' => 1,
                'jam_ke' => 'Harian',
                'jp' => 1,
                'catatan' => $catatan_sesi,
                'role_mengajar' => 'PIC',
                'created_by' => class_exists('Auth') ? Auth::id() : null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 2. Simpan Detail Siswa
        $pdo = $db->getConnection();
        $stmtInsert = $pdo->prepare("
            INSERT INTO presensi_kelas_detail (presensi_kelas_id, siswa_id, nama_siswa, status, catatan)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), catatan = VALUES(catatan)
        ");

        foreach ($statusArr as $siswaId => $st) {
            $siswaId = (int)$siswaId;
            $namaSiswa = trim($_POST['nama_siswa'][$siswaId] ?? '');
            $cat = trim($catatanArr[$siswaId] ?? '');
            $stmtInsert->execute([$sessionId, $siswaId, $namaSiswa, $st, $cat]);
        }

        $_SESSION['flash_success'] = 'Data presensi harian kelas berhasil disimpan.';
        header("Location: " . url("kelola-absen-siswa/kelas/input?kelas=" . urlencode($cleanKelas) . "&tanggal=$tanggal"));
        exit;
    }

    public static function absenKelasAutosave(): void
    {
        header('Content-Type: application/json');
        try {
            $db = \Database::getInstance();
            $data = json_decode(file_get_contents('php://input'), true);

            $kelas = trim($data['kelas'] ?? '');
            $cleanKelas = preg_replace('/^Kelas\s+/i', '', $kelas);
            $tanggal = !empty($data['tanggal']) ? $data['tanggal'] : date('Y-m-d');
            $siswa_id = (int)($data['siswa_id'] ?? 0);
            $nama_siswa = trim($data['nama_siswa'] ?? '');
            $status = in_array($data['status'] ?? '', ['H','S','I','A','T']) ? $data['status'] : 'H';
            $catatan = trim($data['catatan'] ?? '');

            if (empty($cleanKelas) || !$siswa_id) {
                echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap']);
                exit;
            }

            // Sesi
            $session = $db->find("
                SELECT id FROM presensi_kelas 
                WHERE (kelas = ? OR kelas = ?) AND tanggal = ? AND tipe_presensi = 'kelas'
            ", [$cleanKelas, $kelas, $tanggal]);

            if ($session) {
                $sessionId = (int)$session['id'];
            } else {
                $sessionId = $db->insert('presensi_kelas', [
                    'guru_id' => class_exists('Auth') ? Auth::id() : 1,
                    'tipe_presensi' => 'kelas',
                    'kelas' => $cleanKelas,
                    'mata_pelajaran' => 'Presensi Harian / Wali Kelas',
                    'tanggal' => $tanggal,
                    'pertemuan_ke' => 1,
                    'jam_ke' => 'Harian',
                    'jp' => 1,
                    'role_mengajar' => 'PIC',
                    'created_by' => class_exists('Auth') ? Auth::id() : null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Detail
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO presensi_kelas_detail (presensi_kelas_id, siswa_id, nama_siswa, status, catatan)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE status = VALUES(status), catatan = VALUES(catatan)
            ");
            $stmt->execute([$sessionId, $siswa_id, $nama_siswa, $status, $catatan]);

            echo json_encode(['success' => true, 'session_id' => $sessionId, 'message' => 'Presensi harian tersimpan']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public static function absenKelasCetak(): void
    {
        $db = \Database::getInstance();
        $kelas = trim($_GET['kelas'] ?? '');
        $cleanKelas = preg_replace('/^Kelas\s+/i', '', $kelas);
        $tanggal = !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

        $session = $db->find("
            SELECT * FROM presensi_kelas 
            WHERE (kelas = ? OR kelas = ?) AND tanggal = ? AND tipe_presensi = 'kelas'
        ", [$cleanKelas, $kelas, $tanggal]);

        $siswaList = $db->findAll("
            SELECT s.id, s.nis, s.nama, s.jenis_kelamin,
                   COALESCE(d.status, 'A') as status,
                   d.catatan
            FROM siswa s
            LEFT JOIN presensi_kelas_detail d ON d.siswa_id = s.id AND d.presensi_kelas_id = ?
            WHERE s.is_active = 1 AND (s.kelas = ? OR s.kelas = ?)
            ORDER BY s.nama ASC
        ", [$session['id'] ?? 0, $cleanKelas, $kelas]);

        include MODULES_PATH . '/kelola-absen-siswa/views/cetak_kelas.php';
        exit;
    }

    // =========================================================================
    // REKAP ABSEN MAPEL + KELAS
    // =========================================================================

    public static function rekap(): void
    {
        $data = self::prepareRekapData();

        self::renderView('rekap', array_merge($data, [
            'pageTitle' => 'Rekapitulasi Absen Siswa',
            'breadcrumbs' => [
                ['label' => 'Kelola Absen', 'url' => url('kelola-absen-siswa')],
                ['label' => 'Rekap Absen']
            ]
        ]));
    }

    private static function prepareRekapData(): array
    {
        $db = \Database::getInstance();
        $mode = $_GET['mode'] ?? 'kelas'; // 'kelas', 'mapel', 'siswa'
        $selectedKelas = trim($_GET['kelas'] ?? '');
        $selectedMapel = trim($_GET['mapel'] ?? '');
        $selectedBulan = $_GET['bulan'] ?? date('Y-m');

        // Daftar Kelas & Mapel untuk Dropdown
        $kelasList = $db->findAll("
            SELECT DISTINCT kelas, jenjang 
            FROM siswa 
            WHERE is_active = 1 AND kelas IS NOT NULL AND kelas != ''
            ORDER BY jenjang, kelas
        ");
        if (empty($selectedKelas) && !empty($kelasList)) {
            $selectedKelas = $kelasList[0]['kelas'];
        }
        $cleanKelas = preg_replace('/^Kelas\s+/i', '', $selectedKelas);

        $mapelList = $db->findAll("
            SELECT DISTINCT mata_pelajaran 
            FROM pegawai_penugasan_mengajar 
            WHERE mata_pelajaran IS NOT NULL AND mata_pelajaran != ''
            ORDER BY mata_pelajaran ASC
        ");
        if (empty($selectedMapel) && !empty($mapelList)) {
            $selectedMapel = $mapelList[0]['mata_pelajaran'];
        }

        // Daftar Siswa di Kelas Terpilih
        $siswaList = $db->findAll("
            SELECT id, nis, nisn, nama, jenis_kelamin, kelas
            FROM siswa
            WHERE is_active = 1 AND (kelas = ? OR kelas = ?)
            ORDER BY nama ASC
        ", [$cleanKelas, $selectedKelas]);

        $rekapData = [];
        $sessions = [];

        if ($mode === 'kelas') {
            // Mode Rekap Absen Kelas Bulanan (Sesi Harian)
            $startDate = $selectedBulan . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));

            $sessions = $db->findAll("
                SELECT id, tanggal, pertemuan_ke
                FROM presensi_kelas
                WHERE (kelas = ? OR kelas = ?) AND tipe_presensi = 'kelas' AND tanggal BETWEEN ? AND ?
                ORDER BY tanggal ASC
            ", [$cleanKelas, $selectedKelas, $startDate, $endDate]);

            // Ambil detail presensi untuk sesi-sesi ini
            $sessionIds = array_column($sessions, 'id');
            $rawDetails = [];
            if (!empty($sessionIds)) {
                $placeholders = implode(',', array_fill(0, count($sessionIds), '?'));
                $rawDetails = $db->findAll("
                    SELECT presensi_kelas_id, siswa_id, status
                    FROM presensi_kelas_detail
                    WHERE presensi_kelas_id IN ($placeholders)
                ", $sessionIds);
            }

            $matrix = [];
            foreach ($rawDetails as $rd) {
                $matrix[$rd['siswa_id']][$rd['presensi_kelas_id']] = $rd['status'];
            }

            foreach ($siswaList as $s) {
                $sid = $s['id'];
                $h = 0; $s_count = 0; $i_count = 0; $a = 0; $t = 0;
                $daily = [];

                foreach ($sessions as $ses) {
                    $st = $matrix[$sid][$ses['id']] ?? '-';
                    $daily[$ses['id']] = $st;
                    if ($st === 'H') $h++;
                    elseif ($st === 'S') $s_count++;
                    elseif ($st === 'I') $i_count++;
                    elseif ($st === 'A') $a++;
                    elseif ($st === 'T') $t++;
                }

                $totalSesi = count($sessions);
                $pct = $totalSesi > 0 ? round(($h / $totalSesi) * 100, 1) : 0;

                $rekapData[] = [
                    'siswa' => $s,
                    'daily' => $daily,
                    'h' => $h,
                    's' => $s_count,
                    'i' => $i_count,
                    'a' => $a,
                    't' => $t,
                    'total' => $totalSesi,
                    'persen' => $pct
                ];
            }
        } elseif ($mode === 'mapel') {
            // Mode Rekap Absen Mapel (Matriks Pertemuan)
            $sessions = $db->findAll("
                SELECT id, tanggal, pertemuan_ke, jam_ke, materi
                FROM presensi_kelas
                WHERE (kelas = ? OR kelas = ?) AND mata_pelajaran = ? AND tipe_presensi = 'mapel'
                ORDER BY pertemuan_ke ASC, tanggal ASC
            ", [$cleanKelas, $selectedKelas, $selectedMapel]);

            $sessionIds = array_column($sessions, 'id');
            $rawDetails = [];
            if (!empty($sessionIds)) {
                $placeholders = implode(',', array_fill(0, count($sessionIds), '?'));
                $rawDetails = $db->findAll("
                    SELECT presensi_kelas_id, siswa_id, status
                    FROM presensi_kelas_detail
                    WHERE presensi_kelas_id IN ($placeholders)
                ", $sessionIds);
            }

            $matrix = [];
            foreach ($rawDetails as $rd) {
                $matrix[$rd['siswa_id']][$rd['presensi_kelas_id']] = $rd['status'];
            }

            foreach ($siswaList as $s) {
                $sid = $s['id'];
                $h = 0; $s_count = 0; $i_count = 0; $a = 0; $t = 0;
                $pertemuan = [];

                foreach ($sessions as $ses) {
                    $st = $matrix[$sid][$ses['id']] ?? '-';
                    $pertemuan[$ses['id']] = $st;
                    if ($st === 'H') $h++;
                    elseif ($st === 'S') $s_count++;
                    elseif ($st === 'I') $i_count++;
                    elseif ($st === 'A') $a++;
                    elseif ($st === 'T') $t++;
                }

                $totalSesi = count($sessions);
                $pct = $totalSesi > 0 ? round(($h / $totalSesi) * 100, 1) : 0;

                $rekapData[] = [
                    'siswa' => $s,
                    'pertemuan' => $pertemuan,
                    'h' => $h,
                    's' => $s_count,
                    'i' => $i_count,
                    'a' => $a,
                    't' => $t,
                    'total' => $totalSesi,
                    'persen' => $pct
                ];
            }
        } else {
            // Mode Siswa (Kartu Presensi Siswa Individual)
            $selectedSiswaId = (int)($_GET['siswa_id'] ?? ($siswaList[0]['id'] ?? 0));
            $targetSiswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$selectedSiswaId]);

            $studentHistory = [];
            if ($targetSiswa) {
                $studentHistory = $db->findAll("
                    SELECT p.tanggal, p.tipe_presensi, p.mata_pelajaran, p.pertemuan_ke, d.status, d.catatan
                    FROM presensi_kelas_detail d
                    JOIN presensi_kelas p ON d.presensi_kelas_id = p.id
                    WHERE d.siswa_id = ?
                    ORDER BY p.tanggal DESC, p.id DESC
                ", [$selectedSiswaId]);
            }

            return [
                'mode' => $mode,
                'kelasList' => $kelasList,
                'selectedKelas' => $selectedKelas,
                'cleanKelas' => $cleanKelas,
                'mapelList' => $mapelList,
                'selectedMapel' => $selectedMapel,
                'selectedBulan' => $selectedBulan,
                'siswaList' => $siswaList,
                'selectedSiswaId' => $selectedSiswaId,
                'targetSiswa' => $targetSiswa,
                'studentHistory' => $studentHistory
            ];
        }

        return [
            'mode' => $mode,
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas,
            'cleanKelas' => $cleanKelas,
            'mapelList' => $mapelList,
            'selectedMapel' => $selectedMapel,
            'selectedBulan' => $selectedBulan,
            'siswaList' => $siswaList,
            'sessions' => $sessions,
            'rekapData' => $rekapData
        ];
    }

    public static function rekapCetak(): void
    {
        $data = self::prepareRekapData();
        include MODULES_PATH . '/kelola-absen-siswa/views/rekap_cetak.php';
        exit;
    }

    public static function rekapExport(): void
    {
        $data = self::prepareRekapData();
        $mode = $data['mode'];
        $cleanKelas = $data['cleanKelas'];

        $filename = "Rekap_Absen_" . ucfirst($mode) . "_" . str_replace(' ', '_', $cleanKelas) . "_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM untuk Excel UTF-8
        fputs($out, "\xEF\xBB\xBF");

        if ($mode === 'kelas') {
            $header = ['No', 'NIS', 'Nama Siswa', 'L/P'];
            foreach ($data['sessions'] as $ses) {
                $header[] = date('d/m', strtotime($ses['tanggal']));
            }
            $header = array_merge($header, ['Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)', 'Terlambat (T)', 'Total Sesi', '% Kehadiran']);
            fputcsv($out, $header);

            $no = 1;
            foreach ($data['rekapData'] as $row) {
                $line = [
                    $no++,
                    $row['siswa']['nis'] ?? '-',
                    $row['siswa']['nama'],
                    $row['siswa']['jenis_kelamin'] ?? '-'
                ];
                foreach ($data['sessions'] as $ses) {
                    $line[] = $row['daily'][$ses['id']] ?? '-';
                }
                $line[] = $row['h'];
                $line[] = $row['s'];
                $line[] = $row['i'];
                $line[] = $row['a'];
                $line[] = $row['t'];
                $line[] = $row['total'];
                $line[] = $row['persen'] . '%';
                fputcsv($out, $line);
            }
        } elseif ($mode === 'mapel') {
            $header = ['No', 'NIS', 'Nama Siswa', 'L/P'];
            foreach ($data['sessions'] as $ses) {
                $header[] = 'P' . $ses['pertemuan_ke'] . ' (' . date('d/m', strtotime($ses['tanggal'])) . ')';
            }
            $header = array_merge($header, ['Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)', 'Terlambat (T)', 'Total Sesi', '% Kehadiran']);
            fputcsv($out, $header);

            $no = 1;
            foreach ($data['rekapData'] as $row) {
                $line = [
                    $no++,
                    $row['siswa']['nis'] ?? '-',
                    $row['siswa']['nama'],
                    $row['siswa']['jenis_kelamin'] ?? '-'
                ];
                foreach ($data['sessions'] as $ses) {
                    $line[] = $row['pertemuan'][$ses['id']] ?? '-';
                }
                $line[] = $row['h'];
                $line[] = $row['s'];
                $line[] = $row['i'];
                $line[] = $row['a'];
                $line[] = $row['t'];
                $line[] = $row['total'];
                $line[] = $row['persen'] . '%';
                fputcsv($out, $line);
            }
        }

        fclose($out);
        exit;
    }
}
