<?php
/**
 * Pegawai Controller
 */

class PegawaiController
{
    /**
     * Dashboard & Monitoring Kepegawaian
     */
    public static function statistik(): void
    {
        $db = Database::getInstance();
        $pageTitle = 'Dashboard & Monitoring Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Dashboard Monitoring']
        ];

        $filterUnit = trim($_GET['unit_tugas'] ?? '');
        $filterStatusKerja = trim($_GET['status_kerja'] ?? '');
        $filterJenisPegawai = trim($_GET['jenis_pegawai'] ?? '');
        $filterDapodik = trim($_GET['status_dapodik'] ?? '');
        $filterMasaKerja = trim($_GET['masa_kerja'] ?? '');

        $where = "is_active = 1";
        $params = [];

        if (!empty($filterUnit)) {
            $where .= " AND unit_tugas = ?";
            $params[] = $filterUnit;
        }
        if (!empty($filterStatusKerja)) {
            $where .= " AND status_kerja = ?";
            $params[] = $filterStatusKerja;
        }
        if (!empty($filterJenisPegawai)) {
            $where .= " AND jenis_pegawai = ?";
            $params[] = $filterJenisPegawai;
        }
        if (!empty($filterDapodik)) {
            if ($filterDapodik === 'Sudah Terdaftar') {
                $where .= " AND (status_dapodik IS NOT NULL AND status_dapodik != '' AND status_dapodik != 'Belum Terdaftar')";
            } else {
                $where .= " AND (status_dapodik IS NULL OR status_dapodik = '' OR status_dapodik = 'Belum Terdaftar')";
            }
        }
        if (!empty($filterMasaKerja)) {
            if ($filterMasaKerja === '<1') {
                $where .= " AND ((tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) < 1) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) < 1) OR (tanggal_masuk IS NULL AND tmt IS NULL))";
            } elseif ($filterMasaKerja === '1-3') {
                $where .= " AND ((tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) < 3) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) < 3))";
            } elseif ($filterMasaKerja === '3-5') {
                $where .= " AND ((tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) >= 3 AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) < 5) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) >= 3 AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) < 5))";
            } elseif ($filterMasaKerja === '5-10') {
                $where .= " AND ((tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) >= 5 AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) <= 10) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) >= 5 AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) <= 10))";
            } elseif ($filterMasaKerja === '>10') {
                $where .= " AND ((tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) > 10) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) > 10))";
            }
        }

        // 1. Single Fast SQL Aggregation for KPI Numbers and Chart Buckets
        $kpiRow = $db->find("
            SELECT 
                COUNT(1) as total_aktif,
                SUM(CASE WHEN jenis_kelamin = 'P' THEN 1 ELSE 0 END) as wanita_aktif,
                SUM(CASE WHEN jenis_kelamin = 'L' OR jenis_kelamin IS NULL THEN 1 ELSE 0 END) as pria_aktif,
                SUM(CASE WHEN jenis_pegawai LIKE '%Guru%' OR jabatan LIKE '%Guru%' THEN 1 ELSE 0 END) as total_guru,
                SUM(CASE WHEN status_dapodik IS NOT NULL AND status_dapodik != '' AND status_dapodik != 'Belum Terdaftar' THEN 1 ELSE 0 END) as dapodik_sudah,
                -- Masa Kerja Ranges
                SUM(CASE WHEN (tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) < 1) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) < 1) OR (tanggal_masuk IS NULL AND tmt IS NULL) THEN 1 ELSE 0 END) as masa_lt_1,
                SUM(CASE WHEN (tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) < 3) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) >= 1 AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) < 3) THEN 1 ELSE 0 END) as masa_1_3,
                SUM(CASE WHEN (tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) >= 3 AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) < 5) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) >= 3 AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) < 5) THEN 1 ELSE 0 END) as masa_3_5,
                SUM(CASE WHEN (tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) >= 5 AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) <= 10) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) >= 5 AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) <= 10) THEN 1 ELSE 0 END) as masa_5_10,
                SUM(CASE WHEN (tanggal_masuk IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_masuk, CURDATE()) > 10) OR (tanggal_masuk IS NULL AND tmt IS NOT NULL AND TIMESTAMPDIFF(YEAR, tmt, CURDATE()) > 10) THEN 1 ELSE 0 END) as masa_gt_10,
                -- Usia Ranges
                SUM(CASE WHEN tanggal_lahir IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) < 25 THEN 1 ELSE 0 END) as usia_lt_25,
                SUM(CASE WHEN tanggal_lahir IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 25 AND TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) <= 35 THEN 1 ELSE 0 END) as usia_25_35,
                SUM(CASE WHEN tanggal_lahir IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 36 AND TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) <= 45 THEN 1 ELSE 0 END) as usia_36_45,
                SUM(CASE WHEN tanggal_lahir IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) >= 46 AND TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) <= 55 THEN 1 ELSE 0 END) as usia_46_55,
                SUM(CASE WHEN tanggal_lahir IS NOT NULL AND TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) > 55 THEN 1 ELSE 0 END) as usia_gt_55
            FROM pegawai
            WHERE {$where}
        ", $params);

        $totalAktif = intval($kpiRow['total_aktif'] ?? 0);
        $priaAktif = intval($kpiRow['pria_aktif'] ?? 0);
        $wanitaAktif = intval($kpiRow['wanita_aktif'] ?? 0);
        $totalGuru = intval($kpiRow['total_guru'] ?? 0);
        $totalTendik = max(0, $totalAktif - $totalGuru);
        $dapodikSudah = intval($kpiRow['dapodik_sudah'] ?? 0);
        $dapodikBelum = max(0, $totalAktif - $dapodikSudah);

        // Penugasan Aktif
        $totalDitugaskan = $db->find("
            SELECT COUNT(DISTINCT pp.pegawai_id) as total
            FROM pegawai_penugasan pp
            JOIN penugasan_grup pg ON pp.grup_id = pg.id
            JOIN pegawai p ON pp.pegawai_id = p.id
            WHERE pg.is_active = 1 AND pp.status = 'Aktif' AND p.is_active = 1
        ")['total'] ?? 0;
        $totalDitugaskan = intval($totalDitugaskan);
        $totalBelumDitugaskan = max(0, $totalAktif - $totalDitugaskan);

        $totalPrestasi = $db->find("SELECT COUNT(1) as c FROM pegawai_prestasi")['c'] ?? 0;
        $totalPelatihan = $db->find("SELECT COUNT(1) as c FROM pegawai_pelatihan")['c'] ?? 0;
        $totalJP = $db->find("SELECT COALESCE(SUM(jumlah_jam), 0) as s FROM pegawai_pelatihan")['s'] ?? 0;

        $kpi = [
            'total_aktif' => $totalAktif,
            'pria_aktif' => $priaAktif,
            'wanita_aktif' => $wanitaAktif,
            'total_guru' => $totalGuru,
            'total_tendik' => $totalTendik,
            'total_ditugaskan' => $totalDitugaskan,
            'total_belum_ditugaskan' => $totalBelumDitugaskan,
            'dapodik_sudah' => $dapodikSudah,
            'dapodik_belum' => $dapodikBelum,
            'total_prestasi' => intval($totalPrestasi),
            'total_pelatihan' => intval($totalPelatihan),
            'total_jp' => intval($totalJP)
        ];

        // 2. Chart Grouping Calculations directly in SQL
        $unitRows = $db->findAll("
            SELECT COALESCE(NULLIF(unit_tugas, ''), 'Belum Ditentukan') as unit_name, COUNT(1) as total 
            FROM pegawai 
            WHERE {$where} 
            GROUP BY unit_name 
            ORDER BY total DESC
        ", $params);
        $unitCounts = !empty($unitRows) ? array_column($unitRows, 'total', 'unit_name') : [];

        $statusRows = $db->findAll("
            SELECT COALESCE(NULLIF(status_pegawai, ''), NULLIF(status_kerja, ''), 'Lainnya') as status_name, COUNT(1) as total 
            FROM pegawai 
            WHERE {$where} 
            GROUP BY status_name 
            ORDER BY total DESC
        ", $params);
        $statusCounts = !empty($statusRows) ? array_column($statusRows, 'total', 'status_name') : [];

        $masaCounts = [
            intval($kpiRow['masa_lt_1'] ?? 0),
            intval($kpiRow['masa_1_3'] ?? 0),
            intval($kpiRow['masa_3_5'] ?? 0),
            intval($kpiRow['masa_5_10'] ?? 0),
            intval($kpiRow['masa_gt_10'] ?? 0)
        ];

        $usiaCounts = [
            intval($kpiRow['usia_lt_25'] ?? 0),
            intval($kpiRow['usia_25_35'] ?? 0),
            intval($kpiRow['usia_36_45'] ?? 0),
            intval($kpiRow['usia_46_55'] ?? 0),
            intval($kpiRow['usia_gt_55'] ?? 0)
        ];

        // Pendidikan Terakhir
        $pendidikanCounts = ['SMA/SMK' => 0, 'D3' => 0, 'S1' => 0, 'S2' => 0, 'S3' => 0];
        $allPendidikan = $db->findAll("
            SELECT pp.jenjang, COUNT(1) as total
            FROM pegawai_pendidikan pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            WHERE p.is_active = 1
            GROUP BY pp.jenjang
        ");
        foreach ($allPendidikan as $pend) {
            $j = strtoupper(trim($pend['jenjang']));
            $cnt = intval($pend['total']);
            if (isset($pendidikanCounts[$j])) {
                $pendidikanCounts[$j] += $cnt;
            } elseif (stripos($j, 'S1') !== false || stripos($j, 'Sarjana') !== false) {
                $pendidikanCounts['S1'] += $cnt;
            } elseif (stripos($j, 'S2') !== false || stripos($j, 'Magister') !== false) {
                $pendidikanCounts['S2'] += $cnt;
            } elseif (stripos($j, 'S3') !== false || stripos($j, 'Doktor') !== false) {
                $pendidikanCounts['S3'] += $cnt;
            } elseif (stripos($j, 'D3') !== false || stripos($j, 'Diploma') !== false) {
                $pendidikanCounts['D3'] += $cnt;
            } else {
                $pendidikanCounts['SMA/SMK'] += $cnt;
            }
        }

        $chartData = [
            'unit' => [
                'labels' => array_keys($unitCounts),
                'data' => array_values($unitCounts)
            ],
            'status_kerja' => [
                'labels' => array_keys($statusCounts),
                'data' => array_values($statusCounts)
            ],
            'masa_kerja' => $masaCounts,
            'usia' => $usiaCounts,
            'pendidikan' => [
                'labels' => array_keys($pendidikanCounts),
                'data' => array_values($pendidikanCounts)
            ]
        ];

        // Top 5 Pegawai Berprestasi
        $topPrestasi = $db->findAll("
            SELECT p.id, p.nama, p.gelar, p.unit_tugas, p.jabatan, COUNT(pp.id) as total_prestasi
            FROM pegawai p
            JOIN pegawai_prestasi pp ON pp.pegawai_id = p.id
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY total_prestasi DESC
            LIMIT 5
        ");

        // Top 5 Pegawai Pelatihan
        $topPelatihan = $db->findAll("
            SELECT p.id, p.nama, p.gelar, p.unit_tugas, COUNT(pp.id) as total_pelatihan, COALESCE(SUM(pp.jumlah_jam), 0) as total_jp
            FROM pegawai p
            JOIN pegawai_pelatihan pp ON pp.pegawai_id = p.id
            WHERE p.is_active = 1
            GROUP BY p.id
            ORDER BY total_jp DESC, total_pelatihan DESC
            LIMIT 5
        ");

        // Filter Options List
        $unitList = $db->findAll("SELECT DISTINCT nama FROM master_unit_tugas ORDER BY nama ASC");
        $statusKerjaList = $db->findAll("SELECT DISTINCT nama FROM master_status_kerja ORDER BY nama ASC");
        $jenisPegawaiList = $db->findAll("SELECT DISTINCT nama FROM master_jenis_pegawai ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/statistik.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function index(): void
    {
        $pageTitle = 'Data Pegawai';
        $breadcrumbs = [['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')], ['label' => 'Data Pegawai']];
        
        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 10;
        $offset = ($page - 1) * $limit;
        
        $search = trim($_GET['search'] ?? '');
        $filterUnit = trim($_GET['unit_tugas'] ?? '');
        $filterJabatan = trim($_GET['jabatan'] ?? '');
        $filterStatusPegawai = trim($_GET['status_pegawai'] ?? '');
        
        try {
            $where = '1=1';
            $params = [];
            
            if ($search) {
                $where .= " AND nama LIKE ?";
                $params[] = "%{$search}%";
            }
            if ($filterUnit) {
                $where .= " AND unit_tugas = ?";
                $params[] = $filterUnit;
            }
            if ($filterJabatan) {
                $where .= " AND jabatan = ?";
                $params[] = $filterJabatan;
            }
            if ($filterStatusPegawai) {
                $where .= " AND (status_pegawai = ? OR status_kerja = ?)";
                $params[] = $filterStatusPegawai;
                $params[] = $filterStatusPegawai;
            }
            
            $total = $db->find("SELECT COUNT(1) as total FROM pegawai WHERE {$where}", $params)['total'] ?? 0;
            $pegawai = $db->findAll("SELECT id, foto, niy, nik, npwp, email, no_wa, nama, gelar, jenis_kelamin, status_nikah, tempat_lahir, tanggal_lahir, nama_ibu, unit_tugas, jabatan, status_kerja, status_pegawai, jenis_pegawai, status_dapodik, tanggal_masuk, tmt, is_active FROM pegawai WHERE {$where} ORDER BY nama ASC LIMIT {$limit} OFFSET {$offset}", $params);
            
            // Fast lookup from master tables with fallback to distinct scan
            $unitTugasList = $db->findAll("SELECT nama AS unit_tugas FROM master_unit_tugas ORDER BY nama ASC");
            if (empty($unitTugasList)) {
                $unitTugasList = $db->findAll("SELECT DISTINCT unit_tugas FROM pegawai WHERE unit_tugas IS NOT NULL AND unit_tugas != '' ORDER BY unit_tugas ASC");
            }
            $jabatanList = $db->findAll("SELECT nama AS jabatan FROM master_jabatan ORDER BY nama ASC");
            if (empty($jabatanList)) {
                $jabatanList = $db->findAll("SELECT DISTINCT jabatan FROM pegawai WHERE jabatan IS NOT NULL AND jabatan != '' ORDER BY jabatan ASC");
            }
            
        } catch (Exception $e) {
            // Create tables if they don't exist
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `pegawai` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `foto` VARCHAR(255) DEFAULT NULL,
                    `niy` VARCHAR(50) DEFAULT NULL,
                    `nik` VARCHAR(50) DEFAULT NULL,
                    `npwp` VARCHAR(50) DEFAULT NULL,
                    `email` VARCHAR(100) DEFAULT NULL,
                    `no_wa` VARCHAR(30) DEFAULT NULL,
                    `nama` VARCHAR(100) NOT NULL,
                    `gelar` VARCHAR(50) DEFAULT NULL,
                    `jenis_kelamin` ENUM('L','P') NOT NULL DEFAULT 'L',
                    `status_nikah` VARCHAR(50) DEFAULT NULL,
                    `tempat_lahir` VARCHAR(100) DEFAULT NULL,
                    `tanggal_lahir` DATE DEFAULT NULL,
                    `nama_ibu` VARCHAR(100) DEFAULT NULL,
                    `unit_tugas` VARCHAR(100) DEFAULT NULL,
                    `jabatan` VARCHAR(100) DEFAULT NULL,
                    `status_kerja` VARCHAR(50) DEFAULT NULL,
                    `jenis_pegawai` VARCHAR(50) DEFAULT NULL,
                    `status_dapodik` VARCHAR(50) DEFAULT NULL,
                    `tanggal_masuk` DATE DEFAULT NULL,
                    `tmt` DATE DEFAULT NULL,
                    `alamat_ktp` TEXT DEFAULT NULL,
                    `kab_kota_ktp` VARCHAR(100) DEFAULT NULL,
                    `kec_ktp` VARCHAR(100) DEFAULT NULL,
                    `kel_ktp` VARCHAR(100) DEFAULT NULL,
                    `alamat_domisili` TEXT DEFAULT NULL,
                    `kab_kota_domisili` VARCHAR(100) DEFAULT NULL,
                    `kec_domisili` VARCHAR(100) DEFAULT NULL,
                    `kel_domisili` VARCHAR(100) DEFAULT NULL,
                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `pegawai_pendidikan` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `pegawai_id` BIGINT UNSIGNED NOT NULL,
                    `jenjang` VARCHAR(50) NOT NULL,
                    `institusi` VARCHAR(150) NOT NULL,
                    `jurusan` VARCHAR(100) DEFAULT NULL,
                    `tahun_lulus` VARCHAR(4) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_peg_pend_pegawai` (`pegawai_id`),
                    CONSTRAINT `fk_pegawai_pendidikan` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $total = 0;
            $pegawai = [];
            $unitTugasList = [];
            $jabatanList = [];
        }
        
        $totalPages = max(1, ceil($total / $limit));
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Data Pegawai Keluar / Non-Aktif
     */
    public static function keluar(): void
    {
        $pageTitle = 'Data Pegawai Keluar / Non-Aktif';
        $breadcrumbs = [['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')], ['label' => 'Pegawai Keluar']];
        
        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 10;
        $offset = ($page - 1) * $limit;
        
        $search = trim($_GET['search'] ?? '');
        $filterUnit = trim($_GET['unit_tugas'] ?? '');
        $filterJabatan = trim($_GET['jabatan'] ?? '');
        $filterStatusPegawai = trim($_GET['status_pegawai'] ?? '');
        
        $where = 'is_active = 0';
        $params = [];
        
        if ($search) {
            $where .= " AND nama LIKE ?";
            $params[] = "%{$search}%";
        }
        if ($filterUnit) {
            $where .= " AND unit_tugas = ?";
            $params[] = $filterUnit;
        }
        if ($filterJabatan) {
            $where .= " AND jabatan = ?";
            $params[] = $filterJabatan;
        }
        if ($filterStatusPegawai) {
            $where .= " AND (status_pegawai = ? OR status_kerja = ?)";
            $params[] = $filterStatusPegawai;
            $params[] = $filterStatusPegawai;
        }
        
        $total = $db->find("SELECT COUNT(*) as total FROM pegawai WHERE {$where}", $params)['total'] ?? 0;
        $pegawai = $db->findAll("SELECT * FROM pegawai WHERE {$where} ORDER BY nama ASC LIMIT {$limit} OFFSET {$offset}", $params);
        $totalPages = max(1, ceil($total / $limit));
        
        $unitTugasList = $db->findAll("SELECT DISTINCT unit_tugas FROM pegawai WHERE unit_tugas IS NOT NULL AND unit_tugas != '' ORDER BY unit_tugas ASC");
        $jabatanList = $db->findAll("SELECT DISTINCT jabatan FROM pegawai WHERE jabatan IS NOT NULL AND jabatan != '' ORDER BY jabatan ASC");

        $isPegawaiKeluarView = true;
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $db = Database::getInstance();
        $unitList = $db->findAll("SELECT DISTINCT nama FROM master_unit_tugas ORDER BY nama ASC");
        $jabatanList = $db->findAll("SELECT DISTINCT nama FROM master_jabatan ORDER BY nama ASC");
        $statusKerjaList = $db->findAll("SELECT DISTINCT nama FROM master_status_kerja ORDER BY nama ASC");
        $jenisPegawaiList = $db->findAll("SELECT DISTINCT nama FROM master_jenis_pegawai ORDER BY nama ASC");

        $pageTitle = 'Tambah Data Pegawai';
        $breadcrumbs = [['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')], ['label' => 'Tambah Pegawai']];
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('nama', 'Nama Lengkap');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        
        // Handle Foto Upload
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/pegawai/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $filename = 'pegawai_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                    $fotoPath = '/public/uploads/pegawai/' . $filename;
                }
            }
        }

        try {
            $db->beginTransaction();
            
            $pegawaiId = $db->insert('pegawai', [
                'foto' => $fotoPath,
                'niy' => trim($_POST['niy'] ?? ''),
                'nik' => trim($_POST['nik'] ?? ''),
                'npwp' => trim($_POST['npwp'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'no_wa' => trim($_POST['no_wa'] ?? ''),
                'nama' => trim($_POST['nama']),
                'gelar' => trim($_POST['gelar'] ?? ''),
                'jenis_kelamin' => $_POST['jenis_kelamin'] ?? 'L',
                'status_nikah' => trim($_POST['status_nikah'] ?? ''),
                'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
                'tanggal_lahir' => empty($_POST['tanggal_lahir']) ? null : $_POST['tanggal_lahir'],
                'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
                'unit_tugas' => trim($_POST['unit_tugas'] ?? ''),
                'jabatan' => trim($_POST['jabatan'] ?? ''),
                'status_kerja' => trim($_POST['status_pegawai'] ?? ($_POST['status_kerja'] ?? 'Tetap')),
                'status_pegawai' => trim($_POST['status_pegawai'] ?? ($_POST['status_kerja'] ?? 'Tetap')),
                'jenis_pegawai' => trim($_POST['jenis_pegawai'] ?? ''),
                'status_dapodik' => trim($_POST['status_dapodik'] ?? ''),
                'tanggal_masuk' => !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : (!empty($_POST['tmt']) ? $_POST['tmt'] : null),
                'tmt' => !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : (!empty($_POST['tmt']) ? $_POST['tmt'] : null),
                'alamat_ktp' => trim($_POST['alamat_ktp'] ?? ''),
                'kab_kota_ktp' => trim($_POST['kab_kota_ktp'] ?? ''),
                'kec_ktp' => trim($_POST['kec_ktp'] ?? ''),
                'kel_ktp' => trim($_POST['kel_ktp'] ?? ''),
                'alamat_domisili' => trim($_POST['alamat_domisili'] ?? ''),
                'kab_kota_domisili' => trim($_POST['kab_kota_domisili'] ?? ''),
                'kec_domisili' => trim($_POST['kec_domisili'] ?? ''),
                'kel_domisili' => trim($_POST['kel_domisili'] ?? ''),
                'kontak_darurat_1_nama' => trim($_POST['kontak_darurat_1_nama'] ?? ''),
                'kontak_darurat_1_hubungan' => trim($_POST['kontak_darurat_1_hubungan'] ?? ''),
                'kontak_darurat_1_no_hp' => trim($_POST['kontak_darurat_1_no_hp'] ?? ''),
                'kontak_darurat_2_nama' => trim($_POST['kontak_darurat_2_nama'] ?? ''),
                'kontak_darurat_2_hubungan' => trim($_POST['kontak_darurat_2_hubungan'] ?? ''),
                'kontak_darurat_2_no_hp' => trim($_POST['kontak_darurat_2_no_hp'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);

            // Save Riwayat Pendidikan
            if (!empty($_POST['pendidikan_jenjang']) && is_array($_POST['pendidikan_jenjang'])) {
                foreach ($_POST['pendidikan_jenjang'] as $index => $jenjang) {
                    if (!empty($jenjang)) {
                        $db->insert('pegawai_pendidikan', [
                            'pegawai_id' => $pegawaiId,
                            'jenjang' => $jenjang,
                            'institusi' => trim($_POST['pendidikan_institusi'][$index] ?? ''),
                            'jurusan' => trim($_POST['pendidikan_jurusan'][$index] ?? ''),
                            'tahun_lulus' => trim($_POST['pendidikan_tahun'][$index] ?? '')
                        ]);
                    }
                }
            }

            // Save Anggota Keluarga
            if (!empty($_POST['keluarga_nama']) && is_array($_POST['keluarga_nama'])) {
                foreach ($_POST['keluarga_nama'] as $kIdx => $kNama) {
                    if (!empty(trim($kNama))) {
                        $db->insert('pegawai_keluarga', [
                            'pegawai_id' => $pegawaiId,
                            'hubungan' => trim($_POST['keluarga_hubungan'][$kIdx] ?? 'Keluarga'),
                            'nama' => trim($kNama),
                            'jenis_kelamin' => $_POST['keluarga_jk'][$kIdx] ?? 'L',
                            'tempat_lahir' => trim($_POST['keluarga_tempat_lahir'][$kIdx] ?? ''),
                            'tanggal_lahir' => !empty($_POST['keluarga_tgl_lahir'][$kIdx]) ? $_POST['keluarga_tgl_lahir'][$kIdx] : null,
                            'pendidikan_terakhir' => trim($_POST['keluarga_pendidikan'][$kIdx] ?? ''),
                            'pekerjaan' => trim($_POST['keluarga_pekerjaan'][$kIdx] ?? ''),
                            'no_hp' => trim($_POST['keluarga_no_hp'][$kIdx] ?? '')
                        ]);
                    }
                }
            }

            // Save Keahlian & Skill Pegawai
            if (!empty($_POST['skill_nama']) && is_array($_POST['skill_nama'])) {
                foreach ($_POST['skill_nama'] as $sIdx => $sNama) {
                    if (!empty(trim($sNama))) {
                        $db->insert('pegawai_skill', [
                            'pegawai_id' => $pegawaiId,
                            'nama_skill' => trim($sNama),
                            'kategori' => trim($_POST['skill_kategori'][$sIdx] ?? 'Teknis & IT'),
                            'tingkat_keahlian' => trim($_POST['skill_tingkat'][$sIdx] ?? 'Menengah'),
                            'deskripsi' => trim($_POST['skill_deskripsi'][$sIdx] ?? '')
                        ]);
                    }
                }
            }

            $db->commit();
            Response::withSuccess(url('kelola-pegawai'), 'Data pegawai berhasil ditambahkan.');
        } catch (Exception $e) {
            $db->rollback();
            Response::withError(url('kelola-pegawai'), 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public static function edit(string $id): void
    {
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        if (!$pegawai) { Response::withError(url('kelola-pegawai'), 'Data pegawai tidak ditemukan.'); return; }
        
        $pendidikan = $db->findAll("SELECT * FROM pegawai_pendidikan WHERE pegawai_id = ? ORDER BY id ASC", [$id]);
        $keluargaList = $db->findAll("SELECT * FROM pegawai_keluarga WHERE pegawai_id = ? ORDER BY id ASC", [$id]);
        $skillList = $db->findAll("SELECT * FROM pegawai_skill WHERE pegawai_id = ? ORDER BY id ASC", [$id]);
        $karirList = $db->findAll("SELECT * FROM pegawai_karir WHERE pegawai_id = ? ORDER BY tmt_mulai DESC, id DESC", [$id]);
        $prestasiList = $db->findAll("SELECT * FROM pegawai_prestasi WHERE pegawai_id = ? ORDER BY tahun DESC, id DESC", [$id]);
        $pelatihanList = $db->findAll("SELECT * FROM pegawai_pelatihan WHERE pegawai_id = ? ORDER BY tahun DESC, tanggal_mulai DESC, id DESC", [$id]);
        
        $activePenugasan = $db->find("
            SELECT pp.*, pg.nama_grup, pg.no_sk, pg.tanggal_sk,
                   mut.nama as nama_unit, mj.nama as nama_jabatan
            FROM pegawai_penugasan pp
            JOIN penugasan_grup pg ON pp.grup_id = pg.id
            LEFT JOIN master_unit_tugas mut ON pp.unit_tugas_id = mut.id
            LEFT JOIN master_jabatan mj ON pp.jabatan_id = mj.id
            WHERE pp.pegawai_id = ? AND pg.is_active = 1 AND pp.status = 'Aktif'
            ORDER BY pp.id DESC
            LIMIT 1
        ", [$id]);

        $unitList = $db->findAll("SELECT DISTINCT nama FROM master_unit_tugas ORDER BY nama ASC");
        $jabatanList = $db->findAll("SELECT DISTINCT nama FROM master_jabatan ORDER BY nama ASC");
        $statusKerjaList = $db->findAll("SELECT DISTINCT nama FROM master_status_kerja ORDER BY nama ASC");
        $jenisPegawaiList = $db->findAll("SELECT DISTINCT nama FROM master_jenis_pegawai ORDER BY nama ASC");

        $pageTitle = 'Edit Data Pegawai';
        $breadcrumbs = [['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')], ['label' => 'Edit Pegawai']];
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/edit/' . $id), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('nama', 'Nama Lengkap');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        if (!$pegawai) { Response::withError(url('kelola-pegawai'), 'Data pegawai tidak ditemukan.'); return; }
        
        // Handle Foto Upload
        $fotoPath = $pegawai['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/pegawai/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $filename = 'pegawai_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                    // Delete old photo
                    if (!empty($pegawai['foto']) && file_exists(BASE_PATH . $pegawai['foto'])) {
                        @unlink(BASE_PATH . $pegawai['foto']);
                    }
                    $fotoPath = '/public/uploads/pegawai/' . $filename;
                }
            }
        }

        try {
            $db->beginTransaction();
            
            $updateData = [
                'foto' => $fotoPath,
                'niy' => trim($_POST['niy'] ?? ''),
                'nik' => trim($_POST['nik'] ?? ''),
                'npwp' => trim($_POST['npwp'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'no_wa' => trim($_POST['no_wa'] ?? ''),
                'nama' => trim($_POST['nama']),
                'gelar' => trim($_POST['gelar'] ?? ''),
                'jenis_kelamin' => $_POST['jenis_kelamin'] ?? 'L',
                'status_nikah' => trim($_POST['status_nikah'] ?? ''),
                'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
                'tanggal_lahir' => empty($_POST['tanggal_lahir']) ? null : $_POST['tanggal_lahir'],
                'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
                'alamat_ktp' => trim($_POST['alamat_ktp'] ?? ''),
                'kab_kota_ktp' => trim($_POST['kab_kota_ktp'] ?? ''),
                'kec_ktp' => trim($_POST['kec_ktp'] ?? ''),
                'kel_ktp' => trim($_POST['kel_ktp'] ?? ''),
                'alamat_domisili' => trim($_POST['alamat_domisili'] ?? ''),
                'kab_kota_domisili' => trim($_POST['kab_kota_domisili'] ?? ''),
                'kec_domisili' => trim($_POST['kec_domisili'] ?? ''),
                'kel_domisili' => trim($_POST['kel_domisili'] ?? ''),
                'kontak_darurat_1_nama' => trim($_POST['kontak_darurat_1_nama'] ?? ''),
                'kontak_darurat_1_hubungan' => trim($_POST['kontak_darurat_1_hubungan'] ?? ''),
                'kontak_darurat_1_no_hp' => trim($_POST['kontak_darurat_1_no_hp'] ?? ''),
                'kontak_darurat_2_nama' => trim($_POST['kontak_darurat_2_nama'] ?? ''),
                'kontak_darurat_2_hubungan' => trim($_POST['kontak_darurat_2_hubungan'] ?? ''),
                'kontak_darurat_2_no_hp' => trim($_POST['kontak_darurat_2_no_hp'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            // If penugasan fields are provided in POST (e.g. API or other caller), update them; otherwise preserve existing
            if (isset($_POST['unit_tugas'])) $updateData['unit_tugas'] = trim($_POST['unit_tugas']);
            if (isset($_POST['jabatan'])) $updateData['jabatan'] = trim($_POST['jabatan']);
            if (isset($_POST['status_pegawai'])) {
                $st = trim($_POST['status_pegawai']);
                $updateData['status_pegawai'] = $st;
                $updateData['status_kerja'] = $st;
            } elseif (isset($_POST['status_kerja'])) {
                $st = trim($_POST['status_kerja']);
                $updateData['status_pegawai'] = $st;
                $updateData['status_kerja'] = $st;
            }
            if (isset($_POST['jenis_pegawai'])) $updateData['jenis_pegawai'] = trim($_POST['jenis_pegawai']);
            if (isset($_POST['status_dapodik'])) $updateData['status_dapodik'] = trim($_POST['status_dapodik']);
            if (isset($_POST['tanggal_masuk'])) {
                $tglMasuk = !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : null;
                $updateData['tanggal_masuk'] = $tglMasuk;
                $updateData['tmt'] = $tglMasuk;
            } elseif (isset($_POST['tmt'])) {
                $tglMasuk = !empty($_POST['tmt']) ? $_POST['tmt'] : null;
                $updateData['tanggal_masuk'] = $tglMasuk;
                $updateData['tmt'] = $tglMasuk;
            }

            $db->update('pegawai', $updateData, 'id = ?', [$id]);

            // Sinkronisasi dengan Penugasan Aktif jika unit_tugas atau jabatan diubah
            if (!empty($_POST['unit_tugas']) || !empty($_POST['jabatan'])) {
                $activePenugasan = $db->find("
                    SELECT pp.id 
                    FROM pegawai_penugasan pp
                    JOIN penugasan_grup pg ON pp.grup_id = pg.id
                    WHERE pp.pegawai_id = ? AND pg.is_active = 1 AND pp.status = 'Aktif'
                    ORDER BY pp.id DESC LIMIT 1
                ", [$id]);

                if ($activePenugasan) {
                    $updatePp = [];
                    if (!empty($_POST['unit_tugas'])) {
                        $uRow = $db->find("SELECT id FROM master_unit_tugas WHERE nama = ?", [trim($_POST['unit_tugas'])]);
                        if ($uRow) $updatePp['unit_tugas_id'] = $uRow['id'];
                    }
                    if (!empty($_POST['jabatan'])) {
                        $jRow = $db->find("SELECT id FROM master_jabatan WHERE nama = ?", [trim($_POST['jabatan'])]);
                        if ($jRow) $updatePp['jabatan_id'] = $jRow['id'];
                    }
                    if (!empty($updatePp)) {
                        $db->update('pegawai_penugasan', $updatePp, 'id = ?', [$activePenugasan['id']]);
                    }
                }
            }
            $db->delete('pegawai_pendidikan', 'pegawai_id = ?', [$id]);
            
            if (!empty($_POST['pendidikan_jenjang']) && is_array($_POST['pendidikan_jenjang'])) {
                foreach ($_POST['pendidikan_jenjang'] as $index => $jenjang) {
                    if (!empty($jenjang)) {
                        $db->insert('pegawai_pendidikan', [
                            'pegawai_id' => $id,
                            'jenjang' => $jenjang,
                            'institusi' => trim($_POST['pendidikan_institusi'][$index] ?? ''),
                            'jurusan' => trim($_POST['pendidikan_jurusan'][$index] ?? ''),
                            'tahun_lulus' => trim($_POST['pendidikan_tahun'][$index] ?? '')
                        ]);
                    }
                }
            }

            // Recreate Anggota Keluarga
            $db->delete('pegawai_keluarga', 'pegawai_id = ?', [$id]);
            if (!empty($_POST['keluarga_nama']) && is_array($_POST['keluarga_nama'])) {
                foreach ($_POST['keluarga_nama'] as $kIdx => $kNama) {
                    if (!empty(trim($kNama))) {
                        $db->insert('pegawai_keluarga', [
                            'pegawai_id' => $id,
                            'hubungan' => trim($_POST['keluarga_hubungan'][$kIdx] ?? 'Keluarga'),
                            'nama' => trim($kNama),
                            'jenis_kelamin' => $_POST['keluarga_jk'][$kIdx] ?? 'L',
                            'tempat_lahir' => trim($_POST['keluarga_tempat_lahir'][$kIdx] ?? ''),
                            'tanggal_lahir' => !empty($_POST['keluarga_tgl_lahir'][$kIdx]) ? $_POST['keluarga_tgl_lahir'][$kIdx] : null,
                            'pendidikan_terakhir' => trim($_POST['keluarga_pendidikan'][$kIdx] ?? ''),
                            'pekerjaan' => trim($_POST['keluarga_pekerjaan'][$kIdx] ?? ''),
                            'no_hp' => trim($_POST['keluarga_no_hp'][$kIdx] ?? '')
                        ]);
                    }
                }
            }

            // Recreate Keahlian & Skill Pegawai
            $db->delete('pegawai_skill', 'pegawai_id = ?', [$id]);
            if (!empty($_POST['skill_nama']) && is_array($_POST['skill_nama'])) {
                foreach ($_POST['skill_nama'] as $sIdx => $sNama) {
                    if (!empty(trim($sNama))) {
                        $db->insert('pegawai_skill', [
                            'pegawai_id' => $id,
                            'nama_skill' => trim($sNama),
                            'kategori' => trim($_POST['skill_kategori'][$sIdx] ?? 'Teknis & IT'),
                            'tingkat_keahlian' => trim($_POST['skill_tingkat'][$sIdx] ?? 'Menengah'),
                            'deskripsi' => trim($_POST['skill_deskripsi'][$sIdx] ?? '')
                        ]);
                    }
                }
            }

            $db->commit();
            $activeTab = !empty($_POST['active_tab']) ? '?tab=' . urlencode($_POST['active_tab']) : '';
            Response::withSuccess(url('kelola-pegawai/edit/' . $id . $activeTab), 'Data pegawai berhasil diperbarui.');
        } catch (Exception $e) {
            $db->rollback();
            $activeTab = !empty($_POST['active_tab']) ? '?tab=' . urlencode($_POST['active_tab']) : '';
            Response::withError(url('kelola-pegawai/edit/' . $id . $activeTab), 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public static function delete(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai'), 'Token tidak valid.'); return; }
        
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        
        if ($pegawai) {
            if (!empty($pegawai['foto']) && file_exists(BASE_PATH . $pegawai['foto'])) {
                @unlink(BASE_PATH . $pegawai['foto']);
            }
            $db->delete('pegawai', 'id = ?', [$id]);
        }
        
        Response::withSuccess(url('kelola-pegawai'), 'Data pegawai berhasil dihapus.');
    }

    // =========================================================================
    // GRUP PENUGASAN (SK PEMBAGIAN TUGAS PERIODE)
    // =========================================================================

    /**
     * Sinkronisasi data penugasan dari SEMUA grup aktif ke tabel pegawai (Multi-Aktif: Yayasan, PAUD, SD, SMP, SMA, dll.)
     */
    private static function syncAllActiveGroupsToPegawai(): void
    {
        $db = Database::getInstance();
        
        // Ambil seluruh penugasan pegawai berstatus 'Aktif' dari SEMUA grup penugasan yang aktif
        $assignments = $db->findAll("
            SELECT pp.pegawai_id, pp.tmt_mulai, mut.nama AS nama_unit, mj.nama AS nama_jabatan, pg.nama_grup, pg.id as grup_id
            FROM pegawai_penugasan pp
            JOIN penugasan_grup pg ON pg.id = pp.grup_id
            LEFT JOIN master_unit_tugas mut ON mut.id = pp.unit_tugas_id
            LEFT JOIN master_jabatan mj ON mj.id = pp.jabatan_id
            WHERE pg.is_active = 1 AND pp.status = 'Aktif'
            ORDER BY pg.id ASC, pp.id ASC
        ");

        // Map penugasan per pegawai ID
        $pegawaiMap = [];
        foreach ($assignments as $a) {
            $pId = (int)$a['pegawai_id'];
            if (!$pId) continue;
            // Jika ada multi penugasan, simpan penugasan yang valid
            $pegawaiMap[$pId] = [
                'unit_tugas' => $a['nama_unit'] ?? null,
                'jabatan' => $a['nama_jabatan'] ?? null,
            ];
        }

        // Terapkan ke tabel pegawai
        $allPegawai = $db->findAll("SELECT id FROM pegawai");
        foreach ($allPegawai as $p) {
            $pId = (int)$p['id'];
            if (isset($pegawaiMap[$pId])) {
                $db->update('pegawai', [
                    'unit_tugas' => $pegawaiMap[$pId]['unit_tugas'],
                    'jabatan' => $pegawaiMap[$pId]['jabatan']
                ], 'id = ?', [$pId]);
            } else {
                $db->update('pegawai', [
                    'unit_tugas' => null,
                    'jabatan' => null
                ], 'id = ?', [$pId]);
            }
        }
    }

    private static function syncGroupToPegawai(int $grupId = 0): void
    {
        self::syncAllActiveGroupsToPegawai();
    }

    /**
     * Daftar Grup Penugasan
     */
    public static function penugasan(): void
    {
        $db = Database::getInstance();
        $pageTitle = 'Grup Penugasan Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Grup Penugasan']
        ];
        
        $search = trim($_GET['search'] ?? '');
        $where = "1=1";
        $params = [];
        if ($search) {
            $where .= " AND (pg.nama_grup LIKE ? OR pg.no_sk LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql = "
            SELECT pg.*, 
                   COUNT(pp.id) as total_pegawai,
                   SUM(CASE WHEN pp.status = 'Aktif' THEN 1 ELSE 0 END) as total_aktif
            FROM penugasan_grup pg
            LEFT JOIN pegawai_penugasan pp ON pp.grup_id = pg.id
            WHERE {$where}
            GROUP BY pg.id
            ORDER BY pg.is_active DESC, pg.created_at DESC
        ";
        $grupList = $db->findAll($sql, $params);
        $activeGrups = $db->findAll("
            SELECT pg.*, COUNT(pp.id) as total_pegawai
            FROM penugasan_grup pg
            LEFT JOIN pegawai_penugasan pp ON pp.grup_id = pg.id
            WHERE pg.is_active = 1
            GROUP BY pg.id
            ORDER BY pg.nama_grup ASC
        ");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function createGrup(): void
    {
        $pageTitle = 'Tambah Grup Penugasan';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Grup Penugasan', 'url' => url('kelola-pegawai/penugasan')],
            ['label' => 'Tambah Grup']
        ];

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/grup_create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function storeGrup(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan'), 'Token tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('nama_grup', 'Nama Grup Penugasan');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        $db = Database::getInstance();
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        // Upload Berkas Kop Surat jika ada
        $file_kop = null;
        if (isset($_FILES['file_kop']) && $_FILES['file_kop']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/kop/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_kop']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                $fileName = 'KOP_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_kop']['tmp_name'], $uploadDir . $fileName)) {
                    $file_kop = '/public/uploads/kop/' . $fileName;
                }
            }
        }

        // Upload Berkas Footer Surat jika ada
        $file_footer = null;
        if (isset($_FILES['file_footer']) && $_FILES['file_footer']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/footer/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_footer']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                $fileName = 'FOOTER_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_footer']['tmp_name'], $uploadDir . $fileName)) {
                    $file_footer = '/public/uploads/footer/' . $fileName;
                }
            }
        }

        $grupId = $db->insert('penugasan_grup', [
            'nama_grup' => trim($_POST['nama_grup']),
            'semester' => $_POST['semester'] ?? 'Ganjil',
            'no_sk' => trim($_POST['no_sk'] ?? ''),
            'tanggal_sk' => !empty($_POST['tanggal_sk']) ? $_POST['tanggal_sk'] : null,
            'tmt_mulai' => !empty($_POST['tmt_mulai']) ? $_POST['tmt_mulai'] : null,
            'tst_selesai' => !empty($_POST['tst_selesai']) ? $_POST['tst_selesai'] : null,
            'penandatangan_nama' => trim($_POST['penandatangan_nama'] ?? ''),
            'penandatangan_jabatan' => trim($_POST['penandatangan_jabatan'] ?? ''),
            'penandatangan_nip' => trim($_POST['penandatangan_nip'] ?? ''),
            'kota_sk' => trim($_POST['kota_sk'] ?? 'Palu'),
            'file_kop' => $file_kop,
            'file_footer' => $file_footer,
            'menimbang' => trim($_POST['menimbang'] ?? ''),
            'mengingat' => trim($_POST['mengingat'] ?? ''),
            'is_active' => $isActive,
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ]);

        if ($isActive) {
            self::syncGroupToPegawai($grupId);
        }

        Response::withSuccess(url('kelola-pegawai/penugasan/grup/' . $grupId), 'Grup penugasan berhasil dibuat. Silakan tambahkan anggota penugasan pegawai.');
    }

    public static function editGrup(string $id): void
    {
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup penugasan tidak ditemukan.');
            return;
        }

        $pageTitle = 'Edit Grup Penugasan';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Grup Penugasan', 'url' => url('kelola-pegawai/penugasan')],
            ['label' => 'Edit Grup']
        ];

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/grup_edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function updateGrup(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan'), 'Token tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('nama_grup', 'Nama Grup Penugasan');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup penugasan tidak ditemukan.');
            return;
        }

        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        // Upload Berkas Kop Surat jika ada
        $file_kop = $grup['file_kop'];
        if (!empty($_POST['hapus_file_kop']) && $_POST['hapus_file_kop'] == '1') {
            if ($file_kop && file_exists(BASE_PATH . $file_kop)) @unlink(BASE_PATH . $file_kop);
            $file_kop = null;
        }
        if (isset($_FILES['file_kop']) && $_FILES['file_kop']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/kop/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_kop']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                $fileName = 'KOP_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_kop']['tmp_name'], $uploadDir . $fileName)) {
                    if ($file_kop && file_exists(BASE_PATH . $file_kop)) @unlink(BASE_PATH . $file_kop);
                    $file_kop = '/public/uploads/kop/' . $fileName;
                }
            }
        }

        // Upload Berkas Footer Surat jika ada
        $file_footer = $grup['file_footer'] ?? null;
        if (!empty($_POST['hapus_file_footer']) && $_POST['hapus_file_footer'] == '1') {
            if ($file_footer && file_exists(BASE_PATH . $file_footer)) @unlink(BASE_PATH . $file_footer);
            $file_footer = null;
        }
        if (isset($_FILES['file_footer']) && $_FILES['file_footer']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/footer/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_footer']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                $fileName = 'FOOTER_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_footer']['tmp_name'], $uploadDir . $fileName)) {
                    if ($file_footer && file_exists(BASE_PATH . $file_footer)) @unlink(BASE_PATH . $file_footer);
                    $file_footer = '/public/uploads/footer/' . $fileName;
                }
            }
        }

        $db->update('penugasan_grup', [
            'nama_grup' => trim($_POST['nama_grup']),
            'semester' => $_POST['semester'] ?? 'Ganjil',
            'no_sk' => trim($_POST['no_sk'] ?? ''),
            'tanggal_sk' => !empty($_POST['tanggal_sk']) ? $_POST['tanggal_sk'] : null,
            'tmt_mulai' => !empty($_POST['tmt_mulai']) ? $_POST['tmt_mulai'] : null,
            'tst_selesai' => !empty($_POST['tst_selesai']) ? $_POST['tst_selesai'] : null,
            'penandatangan_nama' => trim($_POST['penandatangan_nama'] ?? ''),
            'penandatangan_jabatan' => trim($_POST['penandatangan_jabatan'] ?? ''),
            'penandatangan_nip' => trim($_POST['penandatangan_nip'] ?? ''),
            'kota_sk' => trim($_POST['kota_sk'] ?? 'Palu'),
            'file_kop' => $file_kop,
            'file_footer' => $file_footer,
            'menimbang' => trim($_POST['menimbang'] ?? ''),
            'mengingat' => trim($_POST['mengingat'] ?? ''),
            'is_active' => $isActive,
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ], 'id = ?', [$id]);

        self::syncAllActiveGroupsToPegawai();

        Response::withSuccess(url('kelola-pegawai/penugasan'), 'Grup penugasan berhasil diperbarui.');
    }

    /**
     * Cetak Dokumen SK Penugasan Grup (Kop, Badan SK, Lampiran Anggota, Tanda Tangan)
     */
    public static function cetakSkGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup penugasan tidak ditemukan.');
            return;
        }

        // Ambil seluruh anggota pegawai yang ditugaskan dalam grup ini
        $penugasan = $db->findAll("
            SELECT pp.*, 
                   p.nama AS nama_pegawai, p.niy, p.nik, p.gelar, p.foto,
                   mut.nama AS nama_unit,
                   mj.nama AS nama_jabatan
            FROM pegawai_penugasan pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            LEFT JOIN master_unit_tugas mut ON pp.unit_tugas_id = mut.id
            LEFT JOIN master_jabatan mj ON pp.jabatan_id = mj.id
            WHERE pp.grup_id = ?
            ORDER BY mut.nama ASC, mj.nama ASC, p.nama ASC
        ", [$id]);

        // Ambil data rincian tugas mengajar guru (mapel, kelas, JP)
        $teachingRows = $db->findAll("
            SELECT * FROM pegawai_penugasan_mengajar 
            WHERE grup_id = ? 
            ORDER BY nama_kelas ASC, mata_pelajaran ASC
        ", [$id]);

        $tugasMengajarMap = [];
        foreach ($teachingRows as $tr) {
            $tugasMengajarMap[$tr['penugasan_id']][] = $tr;
        }

        // Ambil pengaturan sistem untuk identitas kop surat
        $settingsRows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $pageTitle = 'Cetak SK Penugasan - ' . ($grup['nama_grup'] ?? 'Grup Penugasan');

        include MODULES_PATH . '/kelola-pegawai/views/penugasan/cetak_sk.php';
    }

    /**
     * Update Cepat Metadata SK & Penandatangan (AJAX / POST dari halaman Cetak SK)
     */
    public static function updateSkMeta(string $id): void
    {
        if (!CSRF::validate()) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 400);
                return;
            }
            Response::withError(url('kelola-pegawai/penugasan/grup/' . $id . '/cetak'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$grup) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
                Response::json(['success' => false, 'message' => 'Grup penugasan tidak ditemukan.'], 404);
                return;
            }
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup penugasan tidak ditemukan.');
            return;
        }

        // Upload Berkas Kop Surat jika ada
        $file_kop = $grup['file_kop'];
        if (!empty($_POST['hapus_file_kop']) && $_POST['hapus_file_kop'] == '1') {
            if ($file_kop && file_exists(BASE_PATH . $file_kop)) @unlink(BASE_PATH . $file_kop);
            $file_kop = null;
        }
        if (isset($_FILES['file_kop']) && $_FILES['file_kop']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/kop/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_kop']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                $fileName = 'KOP_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_kop']['tmp_name'], $uploadDir . $fileName)) {
                    if ($file_kop && file_exists(BASE_PATH . $file_kop)) @unlink(BASE_PATH . $file_kop);
                    $file_kop = '/public/uploads/kop/' . $fileName;
                }
            }
        }

        // Upload Berkas Footer Surat jika ada
        $file_footer = $grup['file_footer'] ?? null;
        if (!empty($_POST['hapus_file_footer']) && $_POST['hapus_file_footer'] == '1') {
            if ($file_footer && file_exists(BASE_PATH . $file_footer)) @unlink(BASE_PATH . $file_footer);
            $file_footer = null;
        }
        if (isset($_FILES['file_footer']) && $_FILES['file_footer']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/footer/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_footer']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['png', 'jpg', 'jpeg', 'svg', 'webp'])) {
                $fileName = 'FOOTER_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_footer']['tmp_name'], $uploadDir . $fileName)) {
                    if ($file_footer && file_exists(BASE_PATH . $file_footer)) @unlink(BASE_PATH . $file_footer);
                    $file_footer = '/public/uploads/footer/' . $fileName;
                }
            }
        }

        $db->update('penugasan_grup', [
            'no_sk' => trim($_POST['no_sk'] ?? ($grup['no_sk'] ?? '')),
            'tanggal_sk' => !empty($_POST['tanggal_sk']) ? $_POST['tanggal_sk'] : $grup['tanggal_sk'],
            'penandatangan_nama' => trim($_POST['penandatangan_nama'] ?? ''),
            'penandatangan_jabatan' => trim($_POST['penandatangan_jabatan'] ?? ''),
            'penandatangan_nip' => trim($_POST['penandatangan_nip'] ?? ''),
            'kota_sk' => trim($_POST['kota_sk'] ?? 'Palu'),
            'file_kop' => $file_kop,
            'file_footer' => $file_footer,
            'menimbang' => trim($_POST['menimbang'] ?? ''),
            'mengingat' => trim($_POST['mengingat'] ?? ''),
        ], 'id = ?', [$id]);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            Response::json(['success' => true, 'message' => 'Pengaturan SK, kop, dan footer berhasil disimpan.']);
            return;
        }

        Response::withSuccess(url('kelola-pegawai/penugasan/grup/' . $id . '/cetak'), 'Pengaturan SK berhasil diperbarui.');
    }

    public static function toggleAktifGrup(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan'), 'Token tidak valid.'); return; }

        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup penugasan tidak ditemukan.');
            return;
        }

        $newStatus = $grup['is_active'] ? 0 : 1;
        $db->update('penugasan_grup', ['is_active' => $newStatus], 'id = ?', [$id]);

        self::syncAllActiveGroupsToPegawai();

        $msg = $newStatus 
            ? "Grup '{$grup['nama_grup']}' berhasil diaktifkan! Grup ini kini aktif bersama grup unit lainnya."
            : "Grup '{$grup['nama_grup']}' berhasil dinonaktifkan.";
        Response::withSuccess(url('kelola-pegawai/penugasan'), $msg);
    }

    public static function setAktifGrup(string $id): void
    {
        self::toggleAktifGrup($id);
    }

    public static function ensurePenugasanMengajarTable(): void
    {
        $db = Database::getInstance();
        $db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `pegawai_penugasan_mengajar` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `penugasan_id` INT NOT NULL,
                `grup_id` INT DEFAULT NULL,
                `pegawai_id` BIGINT UNSIGNED NOT NULL,
                `mata_pelajaran` VARCHAR(150) NOT NULL,
                `kelas_id` BIGINT UNSIGNED DEFAULT NULL,
                `nama_kelas` VARCHAR(100) NOT NULL,
                `jumlah_jp` INT NOT NULL DEFAULT 2,
                `keterangan` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`penugasan_id`),
                INDEX (`grup_id`),
                INDEX (`pegawai_id`),
                INDEX (`mata_pelajaran`),
                INDEX (`nama_kelas`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        try {
            $cols = $db->findAll("SHOW COLUMNS FROM `pegawai_penugasan` LIKE 'is_guru'");
            if (empty($cols)) {
                $db->getConnection()->exec("ALTER TABLE `pegawai_penugasan` ADD COLUMN `is_guru` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`");
            }
            $colsJp = $db->findAll("SHOW COLUMNS FROM `pegawai_penugasan` LIKE 'total_jp'");
            if (empty($colsJp)) {
                $db->getConnection()->exec("ALTER TABLE `pegawai_penugasan` ADD COLUMN `total_jp` INT NOT NULL DEFAULT 0 AFTER `is_guru`");
            }
            $colsSummary = $db->findAll("SHOW COLUMNS FROM `pegawai_penugasan` LIKE 'mapel_ajar_summary'");
            if (empty($colsSummary)) {
                $db->getConnection()->exec("ALTER TABLE `pegawai_penugasan` ADD COLUMN `mapel_ajar_summary` TEXT DEFAULT NULL AFTER `total_jp`");
            }
            $colsWali = $db->findAll("SHOW COLUMNS FROM `pegawai_penugasan` LIKE 'wali_kelas_nama'");
            if (empty($colsWali)) {
                $db->getConnection()->exec("ALTER TABLE `pegawai_penugasan` ADD COLUMN `wali_kelas_nama` VARCHAR(100) DEFAULT NULL AFTER `jabatan_id`");
            }
        } catch (Exception $e) {
            // Silently continue if column already exists
        }
    }

    public static function getMasterMataPelajaran(?string $unit = null): array
    {
        $db = Database::getInstance();
        self::ensurePenugasanMengajarTable();
        if (class_exists('SettingsController')) {
            SettingsController::ensureMasterPembelajaranTable();
        }

        try {
            $where = "is_active = 1";
            $params = [];
            if ($unit && $unit !== 'Semua Unit' && $unit !== 'semua' && $unit !== 'Yayasan') {
                $where .= " AND (unit = ? OR unit = 'Semua Unit')";
                $params[] = $unit;
            }
            $rows = $db->findAll("SELECT * FROM master_mata_pelajaran WHERE {$where} ORDER BY urutan ASC, nama_mapel ASC", $params);
            if (!empty($rows)) {
                return $rows;
            }
        } catch (Exception $e) {}

        // Fallback default list
        $defaults = [
            'Al-Qur\'an Hadits', 'Aqidah Akhlak', 'Fiqih', 'Sejarah Kebudayaan Islam (SKI)',
            'Bahasa Arab', 'Tahfidz Al-Qur\'an', 'Pendidikan Agama Islam & Budi Pekerti',
            'Pendidikan Pancasila & Kewarganegaraan (PPKn)', 'Bahasa Indonesia', 'Bahasa Inggris',
            'Matematika', 'Ilmu Pengetahuan Alam (IPA)', 'Ilmu Pengetahuan Sosial (IPS)',
            'Biologi', 'Fisika', 'Kimia', 'Ekonomi', 'Geografi', 'Sosiologi', 'Sejarah',
            'Informatika / TIK', 'Pendidikan Jasmani, Olahraga, & Kesehatan (PJOK)',
            'Seni Budaya & Prakarya (SBdP)', 'Prakarya & Kewirausahaan', 'Bimbingan Konseling (BK)',
            'Tematik / Guru Kelas', 'Muatan Lokal / Bahasa Daerah'
        ];
        return array_map(fn($m) => ['id' => null, 'kode_mapel' => '', 'nama_mapel' => $m, 'unit' => 'Semua Unit', 'kelompok' => 'Umum'], $defaults);
    }

    /**
     * Ambil seluruh daftar kelas yang ada dari data siswa riil dan master kelas,
     * dikelompokkan dengan unit/jenjang masing-masing (SD, SMP, SMA, PAUD)
     */
    public static function getKelasListWithUnit(): array
    {
        $db = Database::getInstance();
        $list = [];
        $addedNames = [];

        // 1. Ambil dari master kelas jika ada
        try {
            $masterKelas = $db->findAll("SELECT id, nama_kelas, tahun_akademik_id FROM kelas WHERE is_active = 1 ORDER BY nama_kelas ASC");
            foreach ($masterKelas as $mk) {
                $nama = trim($mk['nama_kelas']);
                if ($nama && !isset($addedNames[strtolower($nama)])) {
                    $unit = self::detectUnitFromKelasName($nama);
                    $list[] = [
                        'id' => $mk['id'],
                        'nama_kelas' => $nama,
                        'unit' => $unit,
                        'label' => $nama
                    ];
                    $addedNames[strtolower($nama)] = true;
                }
            }
        } catch (Exception $e) {}

        // 2. Ambil dari data siswa yang memiliki penempatan kelas
        try {
            $siswaKelas = $db->findAll("
                SELECT DISTINCT jenjang, kelas 
                FROM siswa 
                WHERE kelas IS NOT NULL AND kelas != '' 
                ORDER BY jenjang ASC, kelas ASC
            ");
            foreach ($siswaKelas as $sk) {
                $rawKelas = trim($sk['kelas']);
                if (!$rawKelas) continue;

                $formattedNama = (stripos($rawKelas, 'kelas') === false && stripos($rawKelas, 'tk') === false && stripos($rawKelas, 'paud') === false && stripos($rawKelas, 'kb') === false)
                    ? 'Kelas ' . $rawKelas 
                    : $rawKelas;

                $jenjang = strtoupper(trim($sk['jenjang'] ?? ''));
                $unit = !empty($jenjang) ? $jenjang : self::detectUnitFromKelasName($formattedNama);

                if (!isset($addedNames[strtolower($formattedNama)])) {
                    $list[] = [
                        'id' => null,
                        'nama_kelas' => $formattedNama,
                        'unit' => $unit,
                        'label' => $formattedNama
                    ];
                    $addedNames[strtolower($formattedNama)] = true;
                }
            }
        } catch (Exception $e) {}

        // 3. Fallback jika data kelas dan siswa masih kosong
        if (empty($list)) {
            $defaults = [
                ['PAUD A', 'PAUD'], ['PAUD B', 'PAUD'], ['TK A', 'PAUD'], ['TK B', 'PAUD'],
                ['Kelas 1A', 'SD'], ['Kelas 1B', 'SD'], ['Kelas 2A', 'SD'], ['Kelas 2B', 'SD'],
                ['Kelas 3A', 'SD'], ['Kelas 3B', 'SD'], ['Kelas 4A', 'SD'], ['Kelas 4B', 'SD'],
                ['Kelas 5A', 'SD'], ['Kelas 5B', 'SD'], ['Kelas 6A', 'SD'], ['Kelas 6B', 'SD'],
                ['Kelas 7A', 'SMP'], ['Kelas 7B', 'SMP'], ['Kelas 7C', 'SMP'],
                ['Kelas 8A', 'SMP'], ['Kelas 8B', 'SMP'], ['Kelas 8C', 'SMP'],
                ['Kelas 9A', 'SMP'], ['Kelas 9B', 'SMP'], ['Kelas 9C', 'SMP'],
                ['Kelas 10 IPA', 'SMA'], ['Kelas 10 IPS', 'SMA'],
                ['Kelas 11 IPA', 'SMA'], ['Kelas 11 IPS', 'SMA'],
                ['Kelas 12 IPA', 'SMA'], ['Kelas 12 IPS', 'SMA']
            ];
            foreach ($defaults as $def) {
                $list[] = [
                    'id' => null,
                    'nama_kelas' => $def[0],
                    'unit' => $def[1],
                    'label' => $def[0]
                ];
            }
        }

        // Urutkan berdasarkan unit dan nama kelas
        usort($list, function($a, $b) {
            $unitOrder = ['PAUD' => 1, 'TK' => 1, 'SD' => 2, 'SMP' => 3, 'SMA' => 4];
            $uA = $unitOrder[$a['unit']] ?? 9;
            $uB = $unitOrder[$b['unit']] ?? 9;
            if ($uA !== $uB) return $uA - $uB;
            return strnatcasecmp($a['nama_kelas'], $b['nama_kelas']);
        });

        return $list;
    }

    private static function detectUnitFromKelasName(string $namaKelas): string
    {
        $upper = strtoupper($namaKelas);
        if (strpos($upper, 'TK') !== false || strpos($upper, 'PAUD') !== false || strpos($upper, 'KB') !== false || strpos($upper, 'RA') !== false) {
            return 'PAUD';
        }
        if (preg_match('/\b(10|11|12|X|XI|XII|MIPA|IPS)\b/i', $namaKelas)) {
            return 'SMA';
        }
        if (preg_match('/\b(7|8|9|VII|VIII|IX)\b/i', $namaKelas)) {
            return 'SMP';
        }
        if (preg_match('/\b(1|2|3|4|5|6|I|II|III|IV|V|VI)\b/i', $namaKelas)) {
            return 'SD';
        }
        return 'Semua Unit';
    }

    /**
     * Helper: Ambil seluruh rincian beban tugas mengajar guru (Mapel, Kelas, JP) dari grup penugasan aktif
     * Siap digunakan oleh Jurnal Mengajar Guru & Administrasi Perangkat Pembelajaran
     */
    public static function getGuruTugasMengajar(int $pegawaiId, ?int $grupId = null): array
    {
        self::ensurePenugasanMengajarTable();
        $db = Database::getInstance();
        
        if ($grupId) {
            return $db->findAll("
                SELECT ppm.*, pp.no_sk, pp.tmt_mulai, pg.nama_grup, pg.semester
                FROM pegawai_penugasan_mengajar ppm
                JOIN pegawai_penugasan pp ON ppm.penugasan_id = pp.id
                JOIN penugasan_grup pg ON ppm.grup_id = pg.id
                WHERE ppm.pegawai_id = ? AND ppm.grup_id = ? AND pp.status = 'Aktif'
                ORDER BY ppm.nama_kelas ASC, ppm.mata_pelajaran ASC
            ", [$pegawaiId, $grupId]);
        }
        
        return $db->findAll("
            SELECT ppm.*, pp.no_sk, pp.tmt_mulai, pg.nama_grup, pg.semester
            FROM pegawai_penugasan_mengajar ppm
            JOIN pegawai_penugasan pp ON ppm.penugasan_id = pp.id
            JOIN penugasan_grup pg ON ppm.grup_id = pg.id
            WHERE ppm.pegawai_id = ? AND pg.is_active = 1 AND pp.status = 'Aktif'
            ORDER BY pg.nama_grup ASC, ppm.nama_kelas ASC, ppm.mata_pelajaran ASC
        ", [$pegawaiId]);
    }

    public static function salinGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan'), 'Token tidak valid.'); return; }

        $db = Database::getInstance();
        $sourceGrup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$sourceGrup) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup sumber tidak ditemukan.');
            return;
        }

        $namaBaru = trim($_POST['nama_grup_baru'] ?? ('Salinan ' . $sourceGrup['nama_grup']));

        // Buat grup baru (default tidak aktif)
        $newGrupId = $db->insert('penugasan_grup', [
            'nama_grup' => $namaBaru,
            'semester' => $sourceGrup['semester'],
            'no_sk' => $sourceGrup['no_sk'],
            'tanggal_sk' => $sourceGrup['tanggal_sk'],
            'tmt_mulai' => $sourceGrup['tmt_mulai'],
            'tst_selesai' => $sourceGrup['tst_selesai'],
            'penandatangan_nama' => $sourceGrup['penandatangan_nama'] ?? null,
            'penandatangan_jabatan' => $sourceGrup['penandatangan_jabatan'] ?? null,
            'penandatangan_nip' => $sourceGrup['penandatangan_nip'] ?? null,
            'kota_sk' => $sourceGrup['kota_sk'] ?? 'Palu',
            'file_kop' => $sourceGrup['file_kop'] ?? null,
            'file_footer' => $sourceGrup['file_footer'] ?? null,
            'menimbang' => $sourceGrup['menimbang'] ?? null,
            'mengingat' => $sourceGrup['mengingat'] ?? null,
            'is_active' => 0,
            'keterangan' => 'Disalin dari ' . $sourceGrup['nama_grup']
        ]);

        // Salin seluruh data pegawai_penugasan
        $oldItems = $db->findAll("SELECT * FROM pegawai_penugasan WHERE grup_id = ?", [$id]);
        foreach ($oldItems as $item) {
            $insertedPenugasanId = $db->insert('pegawai_penugasan', [
                'grup_id' => $newGrupId,
                'pegawai_id' => $item['pegawai_id'],
                'no_sk' => $item['no_sk'],
                'tanggal_sk' => $item['tanggal_sk'],
                'unit_tugas_id' => $item['unit_tugas_id'],
                'jabatan_id' => $item['jabatan_id'],
                'wali_kelas_nama' => $item['wali_kelas_nama'] ?? null,
                'tmt_mulai' => $item['tmt_mulai'],
                'tst_selesai' => $item['tst_selesai'],
                'file_sk' => $item['file_sk'],
                'status' => $item['status'],
                'is_guru' => $item['is_guru'] ?? 0,
                'total_jp' => $item['total_jp'] ?? 0,
                'mapel_ajar_summary' => $item['mapel_ajar_summary'] ?? null,
                'keterangan' => $item['keterangan']
            ]);

            // Salin rincian tugas mengajar
            $oldTeaching = $db->findAll("SELECT * FROM pegawai_penugasan_mengajar WHERE penugasan_id = ?", [$item['id']]);
            foreach ($oldTeaching as $ot) {
                $db->insert('pegawai_penugasan_mengajar', [
                    'penugasan_id' => $insertedPenugasanId,
                    'grup_id' => $newGrupId,
                    'pegawai_id' => $ot['pegawai_id'],
                    'mata_pelajaran' => $ot['mata_pelajaran'],
                    'kelas_id' => $ot['kelas_id'] ?? null,
                    'nama_kelas' => $ot['nama_kelas'],
                    'jumlah_jp' => $ot['jumlah_jp'],
                    'keterangan' => $ot['keterangan'] ?? null
                ]);
            }

            self::syncPenugasanToKarir((int)$insertedPenugasanId);
        }

        Response::withSuccess(url('kelola-pegawai/penugasan/grup/' . $newGrupId), "Grup baru '{$namaBaru}' berhasil dibuat dengan " . count($oldItems) . " penugasan tersalin.");
    }

    public static function deleteGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan'), 'Token tidak valid.'); return; }

        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if ($grup) {
            // Hapus file SK penugasan di grup ini
            $items = $db->findAll("SELECT file_sk FROM pegawai_penugasan WHERE grup_id = ?", [$id]);
            foreach ($items as $item) {
                if ($item['file_sk'] && file_exists(BASE_PATH . $item['file_sk'])) {
                    @unlink(BASE_PATH . $item['file_sk']);
                }
            }

            $db->query("DELETE FROM pegawai_karir WHERE penugasan_id IN (SELECT id FROM pegawai_penugasan WHERE grup_id = ?) AND is_otomatis = 1", [$id]);
            $db->query("DELETE FROM pegawai_penugasan_mengajar WHERE grup_id = ?", [$id]);
            $db->delete('pegawai_penugasan', 'grup_id = ?', [$id]);
            $db->delete('penugasan_grup', 'id = ?', [$id]);

            // Jika yang dihapus grup aktif, sinkronkan ulang seluruh grup aktif yang tersisa
            if ($grup['is_active']) {
                self::syncAllActiveGroupsToPegawai();
            }
        }

        Response::withSuccess(url('kelola-pegawai/penugasan'), 'Grup penugasan dan seluruh anggotanya berhasil dihapus.');
    }

    // =========================================================================
    // ANGGOTA PENUGASAN PEGAWAI DALAM GRUP
    // =========================================================================

    /**
     * Detail Anggota Penugasan dalam Grup
     */
    public static function detailGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup penugasan tidak ditemukan.');
            return;
        }

        $search = trim($_GET['search'] ?? '');
        $filterUnit = trim($_GET['unit_tugas'] ?? '');

        $where = "pp.grup_id = ?";
        $params = [$id];

        if ($search) {
            $where .= " AND (p.nama LIKE ? OR p.niy LIKE ? OR pp.no_sk LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($filterUnit) {
            $where .= " AND pp.unit_tugas_id = ?";
            $params[] = $filterUnit;
        }

        $sql = "
            SELECT pp.*, p.nama as nama_pegawai, p.niy, p.foto, p.gelar,
                   mut.nama as nama_unit, mj.nama as nama_jabatan
            FROM pegawai_penugasan pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            LEFT JOIN master_unit_tugas mut ON pp.unit_tugas_id = mut.id
            LEFT JOIN master_jabatan mj ON pp.jabatan_id = mj.id
            WHERE {$where}
            ORDER BY mut.nama ASC, mj.nama ASC, p.nama ASC
        ";
        $penugasan = $db->findAll($sql, $params);
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        $allGroups = $db->findAll("SELECT id, nama_grup FROM penugasan_grup WHERE id != ? ORDER BY created_at DESC", [$id]);

        // Ambil data rincian tugas mengajar guru (mapel, kelas, JP)
        $teachingRows = $db->findAll("
            SELECT * FROM pegawai_penugasan_mengajar 
            WHERE grup_id = ? 
            ORDER BY nama_kelas ASC, mata_pelajaran ASC
        ", [$id]);

        $tugasMengajarMap = [];
        foreach ($teachingRows as $tr) {
            $tugasMengajarMap[$tr['penugasan_id']][] = $tr;
        }

        $pageTitle = 'Penugasan: ' . $grup['nama_grup'];
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Grup Penugasan', 'url' => url('kelola-pegawai/penugasan')],
            ['label' => $grup['nama_grup']]
        ];

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/detail_grup.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function createPenugasanGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup tidak ditemukan.');
            return;
        }

        $pageTitle = 'Tambah Anggota Penugasan';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Grup Penugasan', 'url' => url('kelola-pegawai/penugasan')],
            ['label' => $grup['nama_grup'], 'url' => url('kelola-pegawai/penugasan/grup/' . $id)],
            ['label' => 'Tambah Anggota']
        ];

        $pegawaiList = $db->findAll("SELECT id, nama, niy, gelar, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        $jabatanList = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");

        $kelasListWithUnit = self::getKelasListWithUnit();
        $masterMapel = self::getMasterMataPelajaran();

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function storePenugasanGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan/grup/' . $id), 'Token tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('pegawai_id', 'Pegawai')
            ->required('unit_tugas_id', 'Unit Tugas')
            ->required('jabatan_id', 'Jabatan');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        $db = Database::getInstance();
        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$id]);
        if (!$grup) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Grup tidak ditemukan.');
            return;
        }

        // Upload SK jika ada
        $file_sk = null;
        if (isset($_FILES['file_sk']) && $_FILES['file_sk']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sk/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_sk']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SK_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sk']['tmp_name'], $uploadDir . $fileName)) {
                    $file_sk = '/public/uploads/sk/' . $fileName;
                }
            }
        }

        $no_sk = trim($_POST['no_sk'] ?? ($grup['no_sk'] ?? ''));
        $tanggal_sk = !empty($_POST['tanggal_sk']) ? $_POST['tanggal_sk'] : ($grup['tanggal_sk'] ?? date('Y-m-d'));
        $tmt_mulai = !empty($_POST['tmt_mulai']) ? $_POST['tmt_mulai'] : ($grup['tmt_mulai'] ?? date('Y-m-d'));
        $tst_selesai = !empty($_POST['tst_selesai']) ? $_POST['tst_selesai'] : ($grup['tst_selesai'] ?? null);
        $status = $_POST['status'] ?? 'Aktif';
        $waliKelasNama = trim($_POST['wali_kelas_nama'] ?? '');

        // Proses Beban Tugas Mengajar (Mapel, Kelas, JP)
        $isGuru = !empty($_POST['is_guru']) ? 1 : 0;
        $mapelNames = $_POST['mapel_nama'] ?? [];
        $mapelKelas = $_POST['mapel_kelas'] ?? [];
        $mapelJp = $_POST['mapel_jp'] ?? [];
        $mapelKet = $_POST['mapel_keterangan'] ?? [];

        $teachingItems = [];
        $totalJp = 0;
        $summaryParts = [];

        if ($isGuru && is_array($mapelNames)) {
            foreach ($mapelNames as $idx => $mName) {
                $mName = trim($mName);
                $rawKelas = $mapelKelas[$idx] ?? [];
                $classList = is_array($rawKelas) ? $rawKelas : (!empty($rawKelas) ? explode(',', $rawKelas) : []);
                $classList = array_values(array_filter(array_map('trim', $classList)));
                $mJp = max(1, intval($mapelJp[$idx] ?? 2));
                $mKeterangan = trim($mapelKet[$idx] ?? '');

                if ($mName !== '' && !empty($classList)) {
                    foreach ($classList as $kName) {
                        $teachingItems[] = [
                            'mata_pelajaran' => $mName,
                            'nama_kelas' => $kName,
                            'jumlah_jp' => $mJp,
                            'keterangan' => $mKeterangan
                        ];
                        $totalJp += $mJp;
                    }
                    $joinedClasses = implode(', ', $classList);
                    $subTotalJp = $mJp * count($classList);
                    $summaryParts[] = "{$mName} ({$joinedClasses}: {$subTotalJp} JP)";
                }
            }
        }

        $mapelSummary = !empty($summaryParts) ? implode(', ', $summaryParts) : null;
        if (empty($teachingItems)) {
            $isGuru = 0;
            $totalJp = 0;
            $mapelSummary = null;
        }

        $newPenugasanId = $db->insert('pegawai_penugasan', [
            'grup_id' => $id,
            'pegawai_id' => $_POST['pegawai_id'],
            'no_sk' => $no_sk,
            'tanggal_sk' => $tanggal_sk,
            'unit_tugas_id' => $_POST['unit_tugas_id'],
            'jabatan_id' => $_POST['jabatan_id'],
            'wali_kelas_nama' => !empty($waliKelasNama) ? $waliKelasNama : null,
            'tmt_mulai' => $tmt_mulai,
            'tst_selesai' => $tst_selesai,
            'file_sk' => $file_sk,
            'status' => $status,
            'is_guru' => $isGuru,
            'total_jp' => $totalJp,
            'mapel_ajar_summary' => $mapelSummary,
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ]);

        // Simpan baris detail tugas mengajar
        foreach ($teachingItems as $ti) {
            $db->insert('pegawai_penugasan_mengajar', [
                'penugasan_id' => $newPenugasanId,
                'grup_id' => $id,
                'pegawai_id' => $_POST['pegawai_id'],
                'mata_pelajaran' => $ti['mata_pelajaran'],
                'nama_kelas' => $ti['nama_kelas'],
                'jumlah_jp' => $ti['jumlah_jp'],
                'keterangan' => $ti['keterangan']
            ]);
        }

        // Sinkronisasi otomatis ke riwayat karir pegawai
        self::syncPenugasanToKarir((int)$newPenugasanId);

        // Jika grup ini sedang aktif, sinkronkan ke pegawai
        if ($grup['is_active']) {
            self::syncAllActiveGroupsToPegawai();
        }

        Response::withSuccess(url('kelola-pegawai/penugasan/grup/' . $id), 'Pegawai berhasil ditambahkan ke dalam grup penugasan.');
    }

    public static function editPenugasanGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        $db = Database::getInstance();
        $penugasan = $db->find("SELECT * FROM pegawai_penugasan WHERE id = ?", [$id]);
        if (!$penugasan) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Penugasan tidak ditemukan.');
            return;
        }

        $grup = $db->find("SELECT * FROM penugasan_grup WHERE id = ?", [$penugasan['grup_id']]);
        $grupId = $penugasan['grup_id'];

        $pageTitle = 'Edit Penugasan Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Grup Penugasan', 'url' => url('kelola-pegawai/penugasan')],
            ['label' => $grup['nama_grup'] ?? 'Detail Grup', 'url' => url('kelola-pegawai/penugasan/grup/' . $grupId)],
            ['label' => 'Edit']
        ];

        $pegawaiList = $db->findAll("SELECT id, nama, niy, gelar, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        $jabatanList = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");

        $kelasListWithUnit = self::getKelasListWithUnit();
        $masterMapel = self::getMasterMataPelajaran();

        $rawTugasMengajar = $db->findAll("SELECT * FROM pegawai_penugasan_mengajar WHERE penugasan_id = ? ORDER BY id ASC", [$id]);
        $tugasMengajar = [];
        foreach ($rawTugasMengajar as $tm) {
            $k = $tm['mata_pelajaran'] . '___' . $tm['jumlah_jp'] . '___' . ($tm['keterangan'] ?? '');
            if (!isset($tugasMengajar[$k])) {
                $tugasMengajar[$k] = [
                    'mata_pelajaran' => $tm['mata_pelajaran'],
                    'nama_kelas' => [$tm['nama_kelas']],
                    'jumlah_jp' => (int)$tm['jumlah_jp'],
                    'keterangan' => $tm['keterangan'] ?? ''
                ];
            } else {
                $tugasMengajar[$k]['nama_kelas'][] = $tm['nama_kelas'];
            }
        }
        $tugasMengajar = array_values($tugasMengajar);

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function updatePenugasanGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        $db = Database::getInstance();
        $penugasan = $db->find("SELECT * FROM pegawai_penugasan WHERE id = ?", [$id]);
        if (!$penugasan) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Data tidak ditemukan.');
            return;
        }

        $grupId = $penugasan['grup_id'];
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan/grup/' . $grupId), 'Token tidak valid.'); return; }

        $validator = Validator::make($_POST)
            ->required('pegawai_id', 'Pegawai')
            ->required('unit_tugas_id', 'Unit Tugas')
            ->required('jabatan_id', 'Jabatan');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        $file_sk = $penugasan['file_sk'];
        if (isset($_FILES['file_sk']) && $_FILES['file_sk']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sk/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_sk']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SK_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sk']['tmp_name'], $uploadDir . $fileName)) {
                    if ($file_sk && file_exists(BASE_PATH . $file_sk)) @unlink(BASE_PATH . $file_sk);
                    $file_sk = '/public/uploads/sk/' . $fileName;
                }
            }
        }

        $status = $_POST['status'] ?? 'Aktif';
        $tmt_mulai = !empty($_POST['tmt_mulai']) ? $_POST['tmt_mulai'] : $penugasan['tmt_mulai'];
        $waliKelasNama = trim($_POST['wali_kelas_nama'] ?? '');

        // Proses Beban Tugas Mengajar (Mapel, Kelas, JP)
        $isGuru = !empty($_POST['is_guru']) ? 1 : 0;
        $mapelNames = $_POST['mapel_nama'] ?? [];
        $mapelKelas = $_POST['mapel_kelas'] ?? [];
        $mapelJp = $_POST['mapel_jp'] ?? [];
        $mapelKet = $_POST['mapel_keterangan'] ?? [];

        $teachingItems = [];
        $totalJp = 0;
        $summaryParts = [];

        if ($isGuru && is_array($mapelNames)) {
            foreach ($mapelNames as $idx => $mName) {
                $mName = trim($mName);
                $rawKelas = $mapelKelas[$idx] ?? [];
                $classList = is_array($rawKelas) ? $rawKelas : (!empty($rawKelas) ? explode(',', $rawKelas) : []);
                $classList = array_values(array_filter(array_map('trim', $classList)));
                $mJp = max(1, intval($mapelJp[$idx] ?? 2));
                $mKeterangan = trim($mapelKet[$idx] ?? '');

                if ($mName !== '' && !empty($classList)) {
                    foreach ($classList as $kName) {
                        $teachingItems[] = [
                            'mata_pelajaran' => $mName,
                            'nama_kelas' => $kName,
                            'jumlah_jp' => $mJp,
                            'keterangan' => $mKeterangan
                        ];
                        $totalJp += $mJp;
                    }
                    $joinedClasses = implode(', ', $classList);
                    $subTotalJp = $mJp * count($classList);
                    $summaryParts[] = "{$mName} ({$joinedClasses}: {$subTotalJp} JP)";
                }
            }
        }

        $mapelSummary = !empty($summaryParts) ? implode(', ', $summaryParts) : null;
        if (empty($teachingItems)) {
            $isGuru = 0;
            $totalJp = 0;
            $mapelSummary = null;
        }

        $db->update('pegawai_penugasan', [
            'pegawai_id' => $_POST['pegawai_id'],
            'no_sk' => trim($_POST['no_sk'] ?? ''),
            'tanggal_sk' => !empty($_POST['tanggal_sk']) ? $_POST['tanggal_sk'] : null,
            'unit_tugas_id' => $_POST['unit_tugas_id'],
            'jabatan_id' => $_POST['jabatan_id'],
            'wali_kelas_nama' => !empty($waliKelasNama) ? $waliKelasNama : null,
            'tmt_mulai' => $tmt_mulai,
            'tst_selesai' => empty($_POST['tst_selesai']) ? null : $_POST['tst_selesai'],
            'file_sk' => $file_sk,
            'status' => $status,
            'is_guru' => $isGuru,
            'total_jp' => $totalJp,
            'mapel_ajar_summary' => $mapelSummary,
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ], 'id = ?', [$id]);

        // Perbarui baris detail tugas mengajar
        $db->delete('pegawai_penugasan_mengajar', 'penugasan_id = ?', [$id]);
        foreach ($teachingItems as $ti) {
            $db->insert('pegawai_penugasan_mengajar', [
                'penugasan_id' => $id,
                'grup_id' => $grupId,
                'pegawai_id' => $_POST['pegawai_id'],
                'mata_pelajaran' => $ti['mata_pelajaran'],
                'nama_kelas' => $ti['nama_kelas'],
                'jumlah_jp' => $ti['jumlah_jp'],
                'keterangan' => $ti['keterangan']
            ]);
        }

        // Sinkronisasi otomatis ke riwayat karir pegawai
        self::syncPenugasanToKarir((int)$id);

        // Jika grup ini aktif, sinkronkan ke pegawai
        $grup = $db->find("SELECT is_active FROM penugasan_grup WHERE id = ?", [$grupId]);
        if ($grup && $grup['is_active']) {
            self::syncAllActiveGroupsToPegawai();
        }

        Response::withSuccess(url('kelola-pegawai/penugasan/grup/' . $grupId), 'Penugasan pegawai berhasil diperbarui.');
    }

    public static function deletePenugasanGrup(string $id): void
    {
        self::ensurePenugasanMengajarTable();
        $db = Database::getInstance();
        $penugasan = $db->find("SELECT * FROM pegawai_penugasan WHERE id = ?", [$id]);
        if (!$penugasan) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Data tidak ditemukan.');
            return;
        }

        $grupId = $penugasan['grup_id'];
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan/grup/' . $grupId), 'Token tidak valid.'); return; }

        if ($penugasan['file_sk'] && file_exists(BASE_PATH . $penugasan['file_sk'])) {
            @unlink(BASE_PATH . $penugasan['file_sk']);
        }

        // Hapus dari riwayat karir otomatis
        $db->delete('pegawai_karir', 'penugasan_id = ? AND is_otomatis = 1', [$id]);
        $db->delete('pegawai_penugasan_mengajar', 'penugasan_id = ?', [$id]);
        $db->delete('pegawai_penugasan', 'id = ?', [$id]);

        // Jika grup ini aktif, perbarui status pegawai
        $grup = $db->find("SELECT is_active FROM penugasan_grup WHERE id = ?", [$grupId]);
        if ($grup && $grup['is_active']) {
            self::syncAllActiveGroupsToPegawai();
        }

        Response::withSuccess(url('kelola-pegawai/penugasan/grup/' . $grupId), 'Penugasan pegawai berhasil dihapus dari grup.');
    }

    /**
     * Export Pegawai Data to CSV / Excel
     */
    public static function export(): void
    {
        $db = Database::getInstance();
        $search = $_GET['search'] ?? '';
        $unit_tugas = $_GET['unit_tugas'] ?? '';
        $jabatan = $_GET['jabatan'] ?? '';

        $sql = "SELECT * FROM pegawai WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (nama LIKE ? OR niy LIKE ? OR nik LIKE ? OR npwp LIKE ? OR email LIKE ? OR no_wa LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($unit_tugas)) {
            $sql .= " AND unit_tugas = ?";
            $params[] = $unit_tugas;
        }

        if (!empty($jabatan)) {
            $sql .= " AND jabatan = ?";
            $params[] = $jabatan;
        }

        $sql .= " ORDER BY nama ASC";
        $data = $db->findAll($sql, $params);

        $headers = [
            'No',
            'NIY',
            'NIK KTP',
            'NPWP',
            'Nama Lengkap',
            'Gelar',
            'Jenis Kelamin',
            'Status Menikah',
            'Status Pegawai',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Nama Ibu Kandung',
            'Email',
            'No WhatsApp',
            'Unit Tugas',
            'Jabatan',
            'Status Kerja',
            'Jenis Pegawai',
            'Status Dapodik',
            'Tanggal Masuk Kerja',
            'Masa Kerja',
            'Alamat KTP',
            'Kelurahan KTP',
            'Kecamatan KTP',
            'Kab/Kota KTP',
            'Alamat Domisili',
            'Kelurahan Domisili',
            'Kecamatan Domisili',
            'Kab/Kota Domisili',
            'Status'
        ];

        $rows = [];
        $no = 1;
        foreach ($data as $p) {
            $tglMasuk = !empty($p['tanggal_masuk']) ? $p['tanggal_masuk'] : (!empty($p['tmt']) ? $p['tmt'] : '');
            $masaKerja = '-';
            if (!empty($tglMasuk)) {
                $diff = (new DateTime($tglMasuk))->diff(new DateTime());
                if ($diff->invert == 0) {
                    $parts = [];
                    if ($diff->y > 0) $parts[] = $diff->y . ' Thn';
                    if ($diff->m > 0) $parts[] = $diff->m . ' Bln';
                    $masaKerja = empty($parts) ? '< 1 Bln' : implode(' ', $parts);
                }
            }

            $rows[] = [
                'no' => $no++,
                'niy' => $p['niy'] ?? '',
                'nik' => $p['nik'] ? "'" . $p['nik'] : '',
                'npwp' => $p['npwp'] ?? '',
                'nama' => $p['nama'] ?? '',
                'gelar' => $p['gelar'] ?? '',
                'jenis_kelamin' => ($p['jenis_kelamin'] === 'P') ? 'Perempuan' : 'Laki-laki',
                'status_nikah' => $p['status_nikah'] ?? '',
                'status_pegawai' => $p['status_pegawai'] ?? ($p['status_kerja'] ?? 'Tetap'),
                'tempat_lahir' => $p['tempat_lahir'] ?? '',
                'tanggal_lahir' => $p['tanggal_lahir'] ?? '',
                'nama_ibu' => $p['nama_ibu'] ?? '',
                'email' => $p['email'] ?? '',
                'no_wa' => $p['no_wa'] ? "'" . $p['no_wa'] : '',
                'unit_tugas' => $p['unit_tugas'] ?? '',
                'jabatan' => $p['jabatan'] ?? '',
                'status_kerja' => $p['status_kerja'] ?? ($p['status_pegawai'] ?? 'Tetap'),
                'jenis_pegawai' => $p['jenis_pegawai'] ?? '',
                'status_dapodik' => $p['status_dapodik'] ?? '',
                'tanggal_masuk' => $tglMasuk,
                'masa_kerja' => $masaKerja,
                'alamat_ktp' => $p['alamat_ktp'] ?? '',
                'kel_ktp' => $p['kel_ktp'] ?? '',
                'kec_ktp' => $p['kec_ktp'] ?? '',
                'kab_kota_ktp' => $p['kab_kota_ktp'] ?? '',
                'alamat_domisili' => $p['alamat_domisili'] ?? '',
                'kel_domisili' => $p['kel_domisili'] ?? '',
                'kec_domisili' => $p['kec_domisili'] ?? '',
                'kab_kota_domisili' => $p['kab_kota_domisili'] ?? '',
                'is_active' => (!empty($p['is_active'])) ? 'Aktif' : 'Nonaktif'
            ];
        }

        ExcelHelper::exportXLS('Data_Pegawai_' . date('Ymd_His') . '.xls', $headers, $rows, 'Data Pegawai');
    }

    /**
     * Download Excel Import Template for Pegawai (.xls)
     */
    public static function downloadTemplate(): void
    {
        $headers = [
            'Nama Lengkap',
            'Gelar',
            'NIY',
            'NIK',
            'NPWP',
            'Email',
            'No WhatsApp',
            'Jenis Kelamin (L/P)',
            'Status Menikah',
            'Status Pegawai (Tetap/Kontrak/Training)',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Nama Ibu Kandung',
            'Tanggal Masuk Kerja (YYYY-MM-DD)',
            'Unit Tugas',
            'Jabatan',
            'Alamat KTP',
            'Kelurahan KTP',
            'Kecamatan KTP',
            'Kab Kota KTP',
            'Alamat Domisili',
            'Kelurahan Domisili',
            'Kecamatan Domisili',
            'Kab Kota Domisili'
        ];

        $sampleRows = [
            [
                'Ahmad Dahlan',
                'S.Pd',
                'NIY-001',
                '7201011205900001',
                '12.345.678.9-001.000',
                'ahmad.dahlan@example.com',
                '081234567890',
                'L',
                'Menikah',
                'Tetap',
                'Palu',
                '1990-05-12',
                'Siti Aminah',
                '2018-07-15',
                'SMA',
                'Guru Mapel',
                'Jl. Sam Ratulangi No. 10',
                'Besusu Barat',
                'Palu Timur',
                'Kota Palu',
                'Jl. Sam Ratulangi No. 10',
                'Besusu Barat',
                'Palu Timur',
                'Kota Palu'
            ],
            [
                'Nurul Hidayah',
                'M.Pd',
                'NIY-002',
                '7201014508920002',
                '98.765.432.1-002.000',
                'nurul.hidayah@example.com',
                '081298765432',
                'P',
                'Belum Menikah',
                'Kontrak',
                'Donggala',
                '1992-08-15',
                'Fatimah',
                '2021-01-10',
                'SMP',
                'Wali Kelas',
                'Jl. Diponegoro No. 45',
                'Lere',
                'Palu Barat',
                'Kota Palu',
                'Jl. Diponegoro No. 45',
                'Lere',
                'Palu Barat',
                'Kota Palu'
            ],
            [
                'Rizky Pratama',
                'S.Kom',
                'NIY-003',
                '7201012304950003',
                '87.654.321.0-003.000',
                'rizky.pratama@example.com',
                '085241009988',
                'L',
                'Belum Menikah',
                'Training',
                'Palu',
                '1995-04-23',
                'Aisyah',
                '2026-02-01',
                'Yayasan',
                'Staf IT',
                'Jl. Veteran No. 12',
                'Tondo',
                'Mantikulore',
                'Kota Palu',
                'Jl. Veteran No. 12',
                'Tondo',
                'Mantikulore',
                'Kota Palu'
            ]
        ];

        ExcelHelper::downloadTemplate('Template_Import_Pegawai.xls', $headers, $sampleRows, 'Template Import Pegawai');
    }

    /**
     * Import Pegawai Data from uploaded Excel (.xlsx / .xls) or CSV file
     */
    public static function import(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai'), 'Token CSRF tidak valid. Silakan coba lagi.');
            return;
        }

        if (!isset($_FILES['file_import']) || $_FILES['file_import']['error'] !== UPLOAD_ERR_OK) {
            Response::withError(url('kelola-pegawai'), 'Silakan pilih file Excel (.xlsx / .xls) atau CSV yang valid untuk diimport.');
            return;
        }

        try {
            $parsed = ExcelHelper::parseUpload($_FILES['file_import']);
            $rows = $parsed['rows'] ?? [];

            if (empty($rows)) {
                Response::withError(url('kelola-pegawai'), 'File Excel kosong atau format data tidak dapat dibaca.');
                return;
            }

            $db = Database::getInstance();
            $successCount = 0;
            $skippedCount = 0;

            // Helper to parse dates from Excel (numeric serial or text)
            $parseDate = function($rawVal): ?string {
                if (empty($rawVal)) return null;
                $val = trim((string)$rawVal);
                if ($val === '' || $val === '-' || $val === '0') return null;
                // If Excel numeric serial date (e.g. 1000 to 100000)
                if (is_numeric($val) && intval($val) > 1000 && intval($val) < 100000) {
                    $unix = (intval($val) - 25569) * 86400;
                    return gmdate('Y-m-d', $unix);
                }
                // Handle DD/MM/YYYY or DD-MM-YYYY
                if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $val, $m)) {
                    return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
                }
                $t = strtotime($val);
                return $t ? date('Y-m-d', $t) : null;
            };

            foreach ($rows as $row) {
                // Map columns loosely based on normalized keys
                $mapped = [];
                foreach ($row as $key => $val) {
                    if ($key === '_raw') continue;
                    $cleanKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $key)));
                    $mapped[$cleanKey] = trim((string)$val);
                }

                // Identify Nama
                $nama = $mapped['namalengkap'] ?? $mapped['nama'] ?? $mapped['namapegawai'] ?? '';
                if (empty($nama)) {
                    $skippedCount++;
                    continue;
                }

                // Identify fields
                $gelar = $mapped['gelar'] ?? '';
                $niy = $mapped['niy'] ?? $mapped['noinduk'] ?? $mapped['nomorinduk'] ?? '';
                $nik = preg_replace('/[^0-9]/', '', $mapped['nik'] ?? $mapped['nikktp'] ?? $mapped['noktp'] ?? '');
                $npwp = $mapped['npwp'] ?? '';
                $email = filter_var($mapped['email'] ?? '', FILTER_VALIDATE_EMAIL) ? ($mapped['email'] ?? '') : null;
                $no_wa = $mapped['nowhatsapp'] ?? $mapped['nowa'] ?? $mapped['whatsapp'] ?? $mapped['nohp'] ?? $mapped['telepon'] ?? '';
                
                // Jenis Kelamin
                $jkRaw = strtoupper($mapped['jeniskelaminlp'] ?? $mapped['jeniskelamin'] ?? $mapped['jk'] ?? $mapped['gender'] ?? 'L');
                $jenis_kelamin = (str_starts_with($jkRaw, 'P') || str_contains($jkRaw, 'PEREMPUAN')) ? 'P' : 'L';

                // Status Nikah
                $status_nikah = $mapped['statusmenikah'] ?? $mapped['statusnikah'] ?? $mapped['statuspernikahan'] ?? 'Belum Menikah';

                // Status Pegawai
                $statusPegawaiRaw = $mapped['statuspegawaitetapkontraktraining'] ?? $mapped['statuspegawai'] ?? $mapped['statuskerja'] ?? 'Tetap';
                $status_pegawai = 'Tetap';
                if (stripos($statusPegawaiRaw, 'kontrak') !== false) {
                    $status_pegawai = 'Kontrak';
                } elseif (stripos($statusPegawaiRaw, 'train') !== false) {
                    $status_pegawai = 'Training';
                }

                $tempat_lahir = $mapped['tempatlahir'] ?? '';
                $tanggal_lahir = $parseDate($mapped['tanggallahiryyyymmdd'] ?? $mapped['tanggallahir'] ?? $mapped['tgllahir'] ?? null);
                $tanggal_masuk = $parseDate($mapped['tanggalmasukkerjayyyymmdd'] ?? $mapped['tanggalmasukkerja'] ?? $mapped['tanggalmasuk'] ?? $mapped['tmt'] ?? null);

                $unit_tugas = $mapped['unittugas'] ?? $mapped['unit'] ?? null;
                $jabatan = $mapped['jabatan'] ?? $mapped['posisi'] ?? null;

                $nama_ibu = $mapped['namaibukandung'] ?? $mapped['namaibu'] ?? '';
                $alamat_ktp = $mapped['alamatktp'] ?? '';
                $kel_ktp = $mapped['kelurahanktp'] ?? $mapped['kelktp'] ?? '';
                $kec_ktp = $mapped['kecamatanktp'] ?? $mapped['kecktp'] ?? '';
                $kab_kota_ktp = $mapped['kabkotaktp'] ?? $mapped['kabupatenktp'] ?? $mapped['kotaktp'] ?? '';
                $alamat_domisili = $mapped['alamatdomisili'] ?? $alamat_ktp;
                $kel_domisili = $mapped['kelurahandomisili'] ?? $mapped['keldomisili'] ?? $kel_ktp;
                $kec_domisili = $mapped['kecamatandomisili'] ?? $mapped['kecdomisili'] ?? $kec_ktp;
                $kab_kota_domisili = $mapped['kabkotadomisili'] ?? $mapped['kabupatendomisili'] ?? $mapped['kotadomisili'] ?? $kab_kota_ktp;

                // Insert to database
                $db->insert('pegawai', [
                    'nama' => $nama,
                    'gelar' => !empty($gelar) ? $gelar : null,
                    'niy' => !empty($niy) ? $niy : null,
                    'nik' => !empty($nik) ? $nik : null,
                    'npwp' => !empty($npwp) ? $npwp : null,
                    'email' => !empty($email) ? $email : null,
                    'no_wa' => !empty($no_wa) ? $no_wa : null,
                    'jenis_kelamin' => $jenis_kelamin,
                    'status_nikah' => !empty($status_nikah) ? $status_nikah : null,
                    'status_pegawai' => $status_pegawai,
                    'status_kerja' => $status_pegawai,
                    'unit_tugas' => !empty($unit_tugas) ? $unit_tugas : null,
                    'jabatan' => !empty($jabatan) ? $jabatan : null,
                    'tanggal_masuk' => $tanggal_masuk,
                    'tmt' => $tanggal_masuk,
                    'tempat_lahir' => !empty($tempat_lahir) ? $tempat_lahir : null,
                    'tanggal_lahir' => $tanggal_lahir,
                    'nama_ibu' => !empty($nama_ibu) ? $nama_ibu : null,
                    'alamat_ktp' => !empty($alamat_ktp) ? $alamat_ktp : null,
                    'kel_ktp' => !empty($kel_ktp) ? $kel_ktp : null,
                    'kec_ktp' => !empty($kec_ktp) ? $kec_ktp : null,
                    'kab_kota_ktp' => !empty($kab_kota_ktp) ? $kab_kota_ktp : null,
                    'alamat_domisili' => !empty($alamat_domisili) ? $alamat_domisili : null,
                    'kel_domisili' => !empty($kel_domisili) ? $kel_domisili : null,
                    'kec_domisili' => !empty($kec_domisili) ? $kec_domisili : null,
                    'kab_kota_domisili' => !empty($kab_kota_domisili) ? $kab_kota_domisili : null,
                    'is_active' => 1
                ]);

                $successCount++;
            }

            if ($successCount > 0) {
                $msg = "Berhasil mengimport {$successCount} data pegawai dari file Excel.";
                if ($skippedCount > 0) {
                    $msg .= " ({$skippedCount} baris kosong dilewati).";
                }
                Response::withSuccess(url('kelola-pegawai'), $msg);
            } else {
                Response::withError(url('kelola-pegawai'), 'Tidak ada data pegawai yang valid untuk diimport.');
            }

        } catch (Exception $e) {
            Response::withError(url('kelola-pegawai'), 'Gagal mengimport data: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // RIWAYAT KARIR PEGAWAI & GURU (OTOMATIS DARI SK PENUGASAN & MANUAL)
    // =========================================================================

    /**
     * Sinkronisasi data satu penugasan ke tabel pegawai_karir
     */
    public static function syncPenugasanToKarir(int $penugasanId): void
    {
        $db = Database::getInstance();
        $penugasan = $db->find("
            SELECT pp.*, pg.nama_grup, pg.penandatangan_nama, mut.nama AS nama_unit, mj.nama AS nama_jabatan
            FROM pegawai_penugasan pp
            LEFT JOIN penugasan_grup pg ON pp.grup_id = pg.id
            LEFT JOIN master_unit_tugas mut ON pp.unit_tugas_id = mut.id
            LEFT JOIN master_jabatan mj ON pp.jabatan_id = mj.id
            WHERE pp.id = ?
        ", [$penugasanId]);

        if (!$penugasan) return;

        $existing = $db->find("SELECT id FROM pegawai_karir WHERE penugasan_id = ?", [$penugasanId]);
        $data = [
            'pegawai_id' => $penugasan['pegawai_id'],
            'penugasan_id' => $penugasan['id'],
            'tipe_karir' => 'Penugasan SK',
            'unit_tugas' => $penugasan['nama_unit'] ?? null,
            'unit_tugas_id' => $penugasan['unit_tugas_id'],
            'jabatan' => $penugasan['nama_jabatan'] ?? 'Staff',
            'jabatan_id' => $penugasan['jabatan_id'],
            'no_sk' => $penugasan['no_sk'] ?? null,
            'tanggal_sk' => $penugasan['tanggal_sk'] ?? null,
            'tmt_mulai' => $penugasan['tmt_mulai'],
            'tst_selesai' => $penugasan['tst_selesai'] ?? null,
            'penandatangan_sk' => $penugasan['penandatangan_nama'] ?? null,
            'file_sk' => $penugasan['file_sk'] ?? null,
            'status' => $penugasan['status'] ?? 'Aktif',
            'keterangan' => !empty($penugasan['nama_grup']) ? 'Otomatis dari penugasan grup: ' . $penugasan['nama_grup'] : 'Otomatis dari penugasan SK',
            'is_otomatis' => 1
        ];

        if ($existing) {
            $db->update('pegawai_karir', $data, 'id = ?', [$existing['id']]);
        } else {
            $db->insert('pegawai_karir', $data);
        }
    }

    /**
     * Halaman Utama Riwayat Karir Seluruh Pegawai
     */
    public static function karir(): void
    {
        $db = Database::getInstance();
        $pageTitle = 'Riwayat Karir Pegawai & Guru';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Riwayat Karir']
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        $filterPegawai = trim($_GET['pegawai_id'] ?? '');
        $filterUnit = trim($_GET['unit_tugas'] ?? '');
        $filterSumber = trim($_GET['sumber'] ?? '');

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (p.nama LIKE ? OR p.niy LIKE ? OR p.nik LIKE ? OR pk.jabatan LIKE ? OR pk.unit_tugas LIKE ? OR pk.no_sk LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s, $s]);
        }

        if (!empty($filterPegawai)) {
            $where .= " AND pk.pegawai_id = ?";
            $params[] = $filterPegawai;
        }

        if (!empty($filterUnit)) {
            $where .= " AND pk.unit_tugas = ?";
            $params[] = $filterUnit;
        }

        if ($filterSumber === 'otomatis') {
            $where .= " AND pk.is_otomatis = 1";
        } elseif ($filterSumber === 'manual') {
            $where .= " AND (pk.is_otomatis = 0 OR pk.is_otomatis IS NULL)";
        }

        // Statistik Ringkas
        $stats = [
            'total' => $db->find("SELECT COUNT(*) as c FROM pegawai_karir")['c'] ?? 0,
            'aktif' => $db->find("SELECT COUNT(*) as c FROM pegawai_karir WHERE status = 'Aktif'")['c'] ?? 0,
            'otomatis' => $db->find("SELECT COUNT(*) as c FROM pegawai_karir WHERE is_otomatis = 1")['c'] ?? 0,
            'manual' => $db->find("SELECT COUNT(*) as c FROM pegawai_karir WHERE is_otomatis = 0 OR is_otomatis IS NULL")['c'] ?? 0,
        ];

        $total = $db->find("
            SELECT COUNT(*) as c 
            FROM pegawai_karir pk
            JOIN pegawai p ON pk.pegawai_id = p.id
            WHERE {$where}
        ", $params)['c'] ?? 0;

        $karirList = $db->findAll("
            SELECT pk.*, 
                   p.nama AS nama_pegawai, p.gelar AS gelar_pegawai, p.niy AS niy_pegawai, p.nik AS nik_pegawai, p.foto AS foto_pegawai
            FROM pegawai_karir pk
            JOIN pegawai p ON pk.pegawai_id = p.id
            WHERE {$where}
            ORDER BY pk.tmt_mulai DESC, pk.id DESC
            LIMIT {$limit} OFFSET {$offset}
        ", $params);

        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/karir/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Form Tambah Riwayat Karir Manual
     */
    public static function createKarir(): void
    {
        $db = Database::getInstance();
        $pageTitle = 'Tambah Riwayat Karir Manual';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Riwayat Karir', 'url' => url('kelola-pegawai/karir')],
            ['label' => 'Tambah Manual']
        ];

        $selectedPegawaiId = $_GET['pegawai_id'] ?? null;
        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        $jabatanList = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/karir/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan Riwayat Karir Manual
     */
    public static function storeKarir(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/karir'), 'Token tidak valid.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('pegawai_id', 'Pegawai')
            ->required('tipe_karir', 'Tipe Karir')
            ->required('tmt_mulai', 'TMT Mulai');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        $db = Database::getInstance();

        // Tentukan Unit Tugas
        $unitTugasNama = '';
        $unitTugasId = null;
        if (!empty($_POST['unit_tugas_id']) && $_POST['unit_tugas_id'] !== 'custom') {
            $unitTugasId = (int)$_POST['unit_tugas_id'];
            $u = $db->find("SELECT nama FROM master_unit_tugas WHERE id = ?", [$unitTugasId]);
            $unitTugasNama = $u['nama'] ?? '';
        } elseif (!empty($_POST['custom_unit_tugas'])) {
            $unitTugasNama = trim($_POST['custom_unit_tugas']);
        }

        // Tentukan Jabatan
        $jabatanNama = '';
        $jabatanId = null;
        if (!empty($_POST['jabatan_id']) && $_POST['jabatan_id'] !== 'custom') {
            $jabatanId = (int)$_POST['jabatan_id'];
            $j = $db->find("SELECT nama FROM master_jabatan WHERE id = ?", [$jabatanId]);
            $jabatanNama = $j['nama'] ?? '';
        } elseif (!empty($_POST['custom_jabatan'])) {
            $jabatanNama = trim($_POST['custom_jabatan']);
        }

        if (empty($jabatanNama)) {
            Response::withError(url('kelola-pegawai/karir/create'), 'Nama jabatan wajib diisi.');
            return;
        }

        // Upload Berkas SK jika ada
        $file_sk = null;
        if (isset($_FILES['file_sk']) && $_FILES['file_sk']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sk/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_sk']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SK_KARIR_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sk']['tmp_name'], $uploadDir . $fileName)) {
                    $file_sk = '/public/uploads/sk/' . $fileName;
                }
            }
        }

        $db->insert('pegawai_karir', [
            'pegawai_id' => $_POST['pegawai_id'],
            'penugasan_id' => null,
            'tipe_karir' => $_POST['tipe_karir'],
            'unit_tugas' => $unitTugasNama,
            'unit_tugas_id' => $unitTugasId,
            'jabatan' => $jabatanNama,
            'jabatan_id' => $jabatanId,
            'no_sk' => trim($_POST['no_sk'] ?? ''),
            'tanggal_sk' => !empty($_POST['tanggal_sk']) ? $_POST['tanggal_sk'] : null,
            'tmt_mulai' => $_POST['tmt_mulai'],
            'tst_selesai' => !empty($_POST['tst_selesai']) ? $_POST['tst_selesai'] : null,
            'penandatangan_sk' => trim($_POST['penandatangan_sk'] ?? ''),
            'file_sk' => $file_sk,
            'status' => $_POST['status'] ?? 'Aktif',
            'keterangan' => trim($_POST['keterangan'] ?? ''),
            'is_otomatis' => 0
        ]);

        Response::withSuccess(url('kelola-pegawai/karir'), 'Riwayat karir pegawai berhasil ditambahkan.');
    }

    /**
     * Form Edit Riwayat Karir
     */
    public static function editKarir(string $id): void
    {
        $db = Database::getInstance();
        $karir = $db->find("
            SELECT pk.*, p.nama AS nama_pegawai, p.gelar AS gelar_pegawai
            FROM pegawai_karir pk
            JOIN pegawai p ON pk.pegawai_id = p.id
            WHERE pk.id = ?
        ", [$id]);

        if (!$karir) {
            Response::withError(url('kelola-pegawai/karir'), 'Data riwayat karir tidak ditemukan.');
            return;
        }

        $pageTitle = 'Edit Riwayat Karir - ' . $karir['nama_pegawai'];
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Riwayat Karir', 'url' => url('kelola-pegawai/karir')],
            ['label' => 'Edit']
        ];

        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        $jabatanList = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/karir/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Update Riwayat Karir
     */
    public static function updateKarir(string $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/karir'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $karir = $db->find("SELECT * FROM pegawai_karir WHERE id = ?", [$id]);
        if (!$karir) {
            Response::withError(url('kelola-pegawai/karir'), 'Data riwayat karir tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('pegawai_id', 'Pegawai')
            ->required('tipe_karir', 'Tipe Karir')
            ->required('tmt_mulai', 'TMT Mulai');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        // Tentukan Unit Tugas
        $unitTugasNama = $karir['unit_tugas'];
        $unitTugasId = $karir['unit_tugas_id'];
        if (!empty($_POST['unit_tugas_id']) && $_POST['unit_tugas_id'] !== 'custom') {
            $unitTugasId = (int)$_POST['unit_tugas_id'];
            $u = $db->find("SELECT nama FROM master_unit_tugas WHERE id = ?", [$unitTugasId]);
            $unitTugasNama = $u['nama'] ?? '';
        } elseif (!empty($_POST['custom_unit_tugas'])) {
            $unitTugasNama = trim($_POST['custom_unit_tugas']);
            $unitTugasId = null;
        }

        // Tentukan Jabatan
        $jabatanNama = $karir['jabatan'];
        $jabatanId = $karir['jabatan_id'];
        if (!empty($_POST['jabatan_id']) && $_POST['jabatan_id'] !== 'custom') {
            $jabatanId = (int)$_POST['jabatan_id'];
            $j = $db->find("SELECT nama FROM master_jabatan WHERE id = ?", [$jabatanId]);
            $jabatanNama = $j['nama'] ?? '';
        } elseif (!empty($_POST['custom_jabatan'])) {
            $jabatanNama = trim($_POST['custom_jabatan']);
            $jabatanId = null;
        }

        // Handle File SK
        $file_sk = $karir['file_sk'];
        if (isset($_FILES['file_sk']) && $_FILES['file_sk']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sk/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_sk']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SK_KARIR_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sk']['tmp_name'], $uploadDir . $fileName)) {
                    if ($file_sk && file_exists(BASE_PATH . $file_sk)) @unlink(BASE_PATH . $file_sk);
                    $file_sk = '/public/uploads/sk/' . $fileName;
                }
            }
        }

        $db->update('pegawai_karir', [
            'pegawai_id' => $_POST['pegawai_id'],
            'tipe_karir' => $_POST['tipe_karir'],
            'unit_tugas' => $unitTugasNama,
            'unit_tugas_id' => $unitTugasId,
            'jabatan' => $jabatanNama,
            'jabatan_id' => $jabatanId,
            'no_sk' => trim($_POST['no_sk'] ?? ''),
            'tanggal_sk' => !empty($_POST['tanggal_sk']) ? $_POST['tanggal_sk'] : null,
            'tmt_mulai' => $_POST['tmt_mulai'],
            'tst_selesai' => !empty($_POST['tst_selesai']) ? $_POST['tst_selesai'] : null,
            'penandatangan_sk' => trim($_POST['penandatangan_sk'] ?? ''),
            'file_sk' => $file_sk,
            'status' => $_POST['status'] ?? 'Aktif',
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ], 'id = ?', [$id]);

        Response::withSuccess(url('kelola-pegawai/karir'), 'Riwayat karir pegawai berhasil diperbarui.');
    }

    /**
     * Hapus Riwayat Karir
     */
    public static function deleteKarir(string $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/karir'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $karir = $db->find("SELECT * FROM pegawai_karir WHERE id = ?", [$id]);
        if ($karir) {
            if ($karir['file_sk'] && file_exists(BASE_PATH . $karir['file_sk'])) {
                @unlink(BASE_PATH . $karir['file_sk']);
            }
            $db->delete('pegawai_karir', 'id = ?', [$id]);
        }

        Response::withSuccess(url('kelola-pegawai/karir'), 'Data riwayat karir berhasil dihapus.');
    }

    /**
     * Timeline Perjalanan Karir Seorang Pegawai
     */
    public static function timelinePegawai(string $id): void
    {
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        if (!$pegawai) {
            Response::withError(url('kelola-pegawai/karir'), 'Data pegawai tidak ditemukan.');
            return;
        }

        $pageTitle = 'Perjalanan Karir - ' . $pegawai['nama'];
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Riwayat Karir', 'url' => url('kelola-pegawai/karir')],
            ['label' => $pegawai['nama']]
        ];

        $karirList = $db->findAll("
            SELECT * FROM pegawai_karir 
            WHERE pegawai_id = ? 
            ORDER BY tmt_mulai DESC, id DESC
        ", [$id]);

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/karir/timeline.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    // =========================================================================
    // PRESTASI & PENGHARGAAN PEGAWAI / GURU
    // =========================================================================

    /**
     * Halaman Utama Prestasi Pegawai & Guru
     */
    public static function prestasi(): void
    {
        $db = Database::getInstance();
        $pageTitle = 'Prestasi & Penghargaan Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Prestasi Pegawai']
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        $filterPegawai = trim($_GET['pegawai_id'] ?? '');
        $filterTingkat = trim($_GET['tingkat'] ?? '');
        $filterTahun = trim($_GET['tahun'] ?? '');

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (p.nama LIKE ? OR p.niy LIKE ? OR pp.nama_prestasi LIKE ? OR pp.penyelenggara LIKE ? OR pp.peringkat LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filterPegawai)) {
            $where .= " AND pp.pegawai_id = ?";
            $params[] = $filterPegawai;
        }

        if (!empty($filterTingkat)) {
            $where .= " AND pp.tingkat = ?";
            $params[] = $filterTingkat;
        }

        if (!empty($filterTahun)) {
            $where .= " AND pp.tahun = ?";
            $params[] = $filterTahun;
        }

        // Statistik
        $stats = [
            'total' => $db->find("SELECT COUNT(*) as c FROM pegawai_prestasi")['c'] ?? 0,
            'nasional_intl' => $db->find("SELECT COUNT(*) as c FROM pegawai_prestasi WHERE tingkat IN ('Nasional', 'Internasional')")['c'] ?? 0,
            'provinsi' => $db->find("SELECT COUNT(*) as c FROM pegawai_prestasi WHERE tingkat = 'Provinsi'")['c'] ?? 0,
            'kota_kab' => $db->find("SELECT COUNT(*) as c FROM pegawai_prestasi WHERE tingkat IN ('Kota/Kabupaten', 'Kecamatan', 'Sekolah/Internal')")['c'] ?? 0,
        ];

        $total = $db->find("
            SELECT COUNT(*) as c 
            FROM pegawai_prestasi pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            WHERE {$where}
        ", $params)['c'] ?? 0;

        $prestasiList = $db->findAll("
            SELECT pp.*, 
                   p.nama AS nama_pegawai, p.gelar AS gelar_pegawai, p.niy AS niy_pegawai, p.nik AS nik_pegawai, p.foto AS foto_pegawai, p.unit_tugas AS unit_pegawai
            FROM pegawai_prestasi pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            WHERE {$where}
            ORDER BY pp.tahun DESC, pp.tanggal_peroleh DESC, pp.id DESC
            LIMIT {$limit} OFFSET {$offset}
        ", $params);

        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/prestasi/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Form Tambah Prestasi Pegawai
     */
    public static function createPrestasi(): void
    {
        $db = Database::getInstance();
        $pageTitle = 'Tambah Prestasi Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Prestasi Pegawai', 'url' => url('kelola-pegawai/prestasi')],
            ['label' => 'Tambah Prestasi']
        ];

        $selectedPegawaiId = $_GET['pegawai_id'] ?? null;
        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/prestasi/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan Prestasi Pegawai
     */
    public static function storePrestasi(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/prestasi'), 'Token tidak valid.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('pegawai_id', 'Pegawai')
            ->required('nama_prestasi', 'Nama Prestasi')
            ->required('peringkat', 'Peringkat')
            ->required('tingkat', 'Tingkat')
            ->required('kategori', 'Kategori')
            ->required('penyelenggara', 'Penyelenggara')
            ->required('tahun', 'Tahun');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        $db = Database::getInstance();

        // Upload Berkas Sertifikat jika ada
        $file_sertifikat = null;
        if (isset($_FILES['file_sertifikat']) && $_FILES['file_sertifikat']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/prestasi/sertifikat/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SERTIFIKAT_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $uploadDir . $fileName)) {
                    $file_sertifikat = '/public/uploads/prestasi/sertifikat/' . $fileName;
                }
            }
        }

        // Upload Foto Dokumentasi jika ada
        $foto_dokumentasi = null;
        if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/prestasi/foto/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['foto_dokumentasi']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fileName = 'FOTO_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['foto_dokumentasi']['tmp_name'], $uploadDir . $fileName)) {
                    $foto_dokumentasi = '/public/uploads/prestasi/foto/' . $fileName;
                }
            }
        }

        $db->insert('pegawai_prestasi', [
            'pegawai_id' => $_POST['pegawai_id'],
            'nama_prestasi' => trim($_POST['nama_prestasi']),
            'tingkat' => $_POST['tingkat'],
            'kategori' => $_POST['kategori'],
            'peringkat' => trim($_POST['peringkat']),
            'penyelenggara' => trim($_POST['penyelenggara']),
            'tahun' => trim($_POST['tahun']),
            'tanggal_peroleh' => !empty($_POST['tanggal_peroleh']) ? $_POST['tanggal_peroleh'] : null,
            'nomor_sertifikat' => trim($_POST['nomor_sertifikat'] ?? ''),
            'file_sertifikat' => $file_sertifikat,
            'foto_dokumentasi' => $foto_dokumentasi,
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ]);

        Response::withSuccess(url('kelola-pegawai/prestasi'), 'Prestasi pegawai berhasil ditambahkan.');
    }

    /**
     * Form Edit Prestasi Pegawai
     */
    public static function editPrestasi(string $id): void
    {
        $db = Database::getInstance();
        $prestasi = $db->find("
            SELECT pp.*, p.nama AS nama_pegawai, p.gelar AS gelar_pegawai
            FROM pegawai_prestasi pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            WHERE pp.id = ?
        ", [$id]);

        if (!$prestasi) {
            Response::withError(url('kelola-pegawai/prestasi'), 'Data prestasi tidak ditemukan.');
            return;
        }

        $pageTitle = 'Edit Prestasi - ' . $prestasi['nama_prestasi'];
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Prestasi Pegawai', 'url' => url('kelola-pegawai/prestasi')],
            ['label' => 'Edit']
        ];

        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/prestasi/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Update Prestasi Pegawai
     */
    public static function updatePrestasi(string $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/prestasi'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $prestasi = $db->find("SELECT * FROM pegawai_prestasi WHERE id = ?", [$id]);
        if (!$prestasi) {
            Response::withError(url('kelola-pegawai/prestasi'), 'Data prestasi tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('pegawai_id', 'Pegawai')
            ->required('nama_prestasi', 'Nama Prestasi')
            ->required('peringkat', 'Peringkat')
            ->required('tingkat', 'Tingkat')
            ->required('kategori', 'Kategori')
            ->required('penyelenggara', 'Penyelenggara')
            ->required('tahun', 'Tahun');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        // Upload Berkas Sertifikat jika ada
        $file_sertifikat = $prestasi['file_sertifikat'];
        if (isset($_FILES['file_sertifikat']) && $_FILES['file_sertifikat']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/prestasi/sertifikat/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SERTIFIKAT_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $uploadDir . $fileName)) {
                    if ($file_sertifikat && file_exists(BASE_PATH . $file_sertifikat)) @unlink(BASE_PATH . $file_sertifikat);
                    $file_sertifikat = '/public/uploads/prestasi/sertifikat/' . $fileName;
                }
            }
        }

        // Upload Foto Dokumentasi jika ada
        $foto_dokumentasi = $prestasi['foto_dokumentasi'];
        if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/prestasi/foto/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['foto_dokumentasi']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fileName = 'FOTO_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['foto_dokumentasi']['tmp_name'], $uploadDir . $fileName)) {
                    if ($foto_dokumentasi && file_exists(BASE_PATH . $foto_dokumentasi)) @unlink(BASE_PATH . $foto_dokumentasi);
                    $foto_dokumentasi = '/public/uploads/prestasi/foto/' . $fileName;
                }
            }
        }

        $db->update('pegawai_prestasi', [
            'pegawai_id' => $_POST['pegawai_id'],
            'nama_prestasi' => trim($_POST['nama_prestasi']),
            'tingkat' => $_POST['tingkat'],
            'kategori' => $_POST['kategori'],
            'peringkat' => trim($_POST['peringkat']),
            'penyelenggara' => trim($_POST['penyelenggara']),
            'tahun' => trim($_POST['tahun']),
            'tanggal_peroleh' => !empty($_POST['tanggal_peroleh']) ? $_POST['tanggal_peroleh'] : null,
            'nomor_sertifikat' => trim($_POST['nomor_sertifikat'] ?? ''),
            'file_sertifikat' => $file_sertifikat,
            'foto_dokumentasi' => $foto_dokumentasi,
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ], 'id = ?', [$id]);

        Response::withSuccess(url('kelola-pegawai/prestasi'), 'Data prestasi pegawai berhasil diperbarui.');
    }

    /**
     * Hapus Prestasi Pegawai
     */
    public static function deletePrestasi(string $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/prestasi'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $prestasi = $db->find("SELECT * FROM pegawai_prestasi WHERE id = ?", [$id]);
        if ($prestasi) {
            if ($prestasi['file_sertifikat'] && file_exists(BASE_PATH . $prestasi['file_sertifikat'])) {
                @unlink(BASE_PATH . $prestasi['file_sertifikat']);
            }
            if ($prestasi['foto_dokumentasi'] && file_exists(BASE_PATH . $prestasi['foto_dokumentasi'])) {
                @unlink(BASE_PATH . $prestasi['foto_dokumentasi']);
            }
            $db->delete('pegawai_prestasi', 'id = ?', [$id]);
        }

        Response::withSuccess(url('kelola-pegawai/prestasi'), 'Data prestasi berhasil dihapus.');
    }

    /**
     * Portofolio / Galeri Prestasi Per Pegawai
     */
    public static function prestasiPegawai(string $id): void
    {
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        if (!$pegawai) {
            Response::withError(url('kelola-pegawai/prestasi'), 'Data pegawai tidak ditemukan.');
            return;
        }

        $pageTitle = 'Portofolio Prestasi - ' . $pegawai['nama'];
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Prestasi Pegawai', 'url' => url('kelola-pegawai/prestasi')],
            ['label' => $pegawai['nama']]
        ];

        $prestasiList = $db->findAll("
            SELECT * FROM pegawai_prestasi 
            WHERE pegawai_id = ? 
            ORDER BY tahun DESC, tanggal_peroleh DESC, id DESC
        ", [$id]);

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/prestasi/detail_pegawai.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    // =========================================================================
    // RIWAYAT PELATIHAN, DIKLAT, WORKSHOP & SERTIFIKASI PEGAWAI / GURU
    // =========================================================================

    /**
     * Halaman Utama Riwayat Pelatihan Seluruh Pegawai
     */
    public static function pelatihan(): void
    {
        $db = Database::getInstance();
        $pageTitle = 'Riwayat Pelatihan & Diklat Pegawai';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Riwayat Pelatihan']
        ];

        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        $filterPegawai = trim($_GET['pegawai_id'] ?? '');
        $filterJenis = trim($_GET['jenis'] ?? '');
        $filterTahun = trim($_GET['tahun'] ?? '');

        $where = "1=1";
        $params = [];

        if (!empty($search)) {
            $where .= " AND (p.nama LIKE ? OR p.niy LIKE ? OR pp.nama_pelatihan LIKE ? OR pp.penyelenggara LIKE ? OR pp.tempat LIKE ?)";
            $s = "%{$search}%";
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filterPegawai)) {
            $where .= " AND pp.pegawai_id = ?";
            $params[] = $filterPegawai;
        }

        if (!empty($filterJenis)) {
            $where .= " AND pp.jenis_pelatihan = ?";
            $params[] = $filterJenis;
        }

        if (!empty($filterTahun)) {
            $where .= " AND pp.tahun = ?";
            $params[] = $filterTahun;
        }

        // Statistik
        $stats = [
            'total' => $db->find("SELECT COUNT(*) as c FROM pegawai_pelatihan")['c'] ?? 0,
            'total_jp' => $db->find("SELECT COALESCE(SUM(jumlah_jam), 0) as s FROM pegawai_pelatihan")['s'] ?? 0,
            'sertifikasi' => $db->find("SELECT COUNT(*) as c FROM pegawai_pelatihan WHERE jenis_pelatihan = 'Sertifikasi Keahlian / Profesi'")['c'] ?? 0,
            'diklat_workshop' => $db->find("SELECT COUNT(*) as c FROM pegawai_pelatihan WHERE jenis_pelatihan IN ('Diklat Fungsional', 'Bimtek & Workshop')")['c'] ?? 0,
        ];

        $total = $db->find("
            SELECT COUNT(*) as c 
            FROM pegawai_pelatihan pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            WHERE {$where}
        ", $params)['c'] ?? 0;

        $pelatihanList = $db->findAll("
            SELECT pp.*, 
                   p.nama AS nama_pegawai, p.gelar AS gelar_pegawai, p.niy AS niy_pegawai, p.nik AS nik_pegawai, p.foto AS foto_pegawai, p.unit_tugas AS unit_pegawai
            FROM pegawai_pelatihan pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            WHERE {$where}
            ORDER BY pp.tahun DESC, pp.tanggal_mulai DESC, pp.id DESC
            LIMIT {$limit} OFFSET {$offset}
        ", $params);

        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/pelatihan/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Form Tambah Pelatihan Pegawai
     */
    public static function createPelatihan(): void
    {
        $db = Database::getInstance();
        $pageTitle = 'Tambah Riwayat Pelatihan';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Riwayat Pelatihan', 'url' => url('kelola-pegawai/pelatihan')],
            ['label' => 'Tambah Pelatihan']
        ];

        $selectedPegawaiId = $_GET['pegawai_id'] ?? null;
        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/pelatihan/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan Riwayat Pelatihan Pegawai
     */
    public static function storePelatihan(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/pelatihan'), 'Token tidak valid.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('pegawai_id', 'Pegawai')
            ->required('nama_pelatihan', 'Nama Pelatihan')
            ->required('jenis_pelatihan', 'Jenis Pelatihan')
            ->required('penyelenggara', 'Penyelenggara')
            ->required('tanggal_mulai', 'Tanggal Mulai')
            ->required('tahun', 'Tahun Pelaksanaan');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        $db = Database::getInstance();

        // Upload Berkas Sertifikat jika ada
        $file_sertifikat = null;
        if (isset($_FILES['file_sertifikat']) && $_FILES['file_sertifikat']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/pelatihan/sertifikat/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SERTIFIKAT_DIKLAT_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $uploadDir . $fileName)) {
                    $file_sertifikat = '/public/uploads/pelatihan/sertifikat/' . $fileName;
                }
            }
        }

        // Upload Foto Dokumentasi jika ada
        $foto_dokumentasi = null;
        if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/pelatihan/foto/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['foto_dokumentasi']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fileName = 'FOTO_DIKLAT_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['foto_dokumentasi']['tmp_name'], $uploadDir . $fileName)) {
                    $foto_dokumentasi = '/public/uploads/pelatihan/foto/' . $fileName;
                }
            }
        }

        $db->insert('pegawai_pelatihan', [
            'pegawai_id' => $_POST['pegawai_id'],
            'nama_pelatihan' => trim($_POST['nama_pelatihan']),
            'jenis_pelatihan' => $_POST['jenis_pelatihan'],
            'penyelenggara' => trim($_POST['penyelenggara']),
            'tempat' => trim($_POST['tempat'] ?? ''),
            'tahun' => trim($_POST['tahun']),
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null,
            'jumlah_jam' => !empty($_POST['jumlah_jam']) ? (int)$_POST['jumlah_jam'] : 0,
            'nomor_sertifikat' => trim($_POST['nomor_sertifikat'] ?? ''),
            'peran' => $_POST['peran'] ?? 'Peserta',
            'file_sertifikat' => $file_sertifikat,
            'foto_dokumentasi' => $foto_dokumentasi,
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ]);

        Response::withSuccess(url('kelola-pegawai/pelatihan'), 'Riwayat pelatihan pegawai berhasil ditambahkan.');
    }

    /**
     * Form Edit Pelatihan Pegawai
     */
    public static function editPelatihan(string $id): void
    {
        $db = Database::getInstance();
        $pelatihan = $db->find("
            SELECT pp.*, p.nama AS nama_pegawai, p.gelar AS gelar_pegawai
            FROM pegawai_pelatihan pp
            JOIN pegawai p ON pp.pegawai_id = p.id
            WHERE pp.id = ?
        ", [$id]);

        if (!$pelatihan) {
            Response::withError(url('kelola-pegawai/pelatihan'), 'Data pelatihan tidak ditemukan.');
            return;
        }

        $pageTitle = 'Edit Pelatihan - ' . $pelatihan['nama_pelatihan'];
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Riwayat Pelatihan', 'url' => url('kelola-pegawai/pelatihan')],
            ['label' => 'Edit']
        ];

        $pegawaiList = $db->findAll("SELECT id, nama, gelar, niy, nik, foto FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/pelatihan/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Update Pelatihan Pegawai
     */
    public static function updatePelatihan(string $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/pelatihan'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $pelatihan = $db->find("SELECT * FROM pegawai_pelatihan WHERE id = ?", [$id]);
        if (!$pelatihan) {
            Response::withError(url('kelola-pegawai/pelatihan'), 'Data pelatihan tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('pegawai_id', 'Pegawai')
            ->required('nama_pelatihan', 'Nama Pelatihan')
            ->required('jenis_pelatihan', 'Jenis Pelatihan')
            ->required('penyelenggara', 'Penyelenggara')
            ->required('tanggal_mulai', 'Tanggal Mulai')
            ->required('tahun', 'Tahun Pelaksanaan');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        // Upload Berkas Sertifikat jika ada
        $file_sertifikat = $pelatihan['file_sertifikat'];
        if (isset($_FILES['file_sertifikat']) && $_FILES['file_sertifikat']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/pelatihan/sertifikat/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['file_sertifikat']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SERTIFIKAT_DIKLAT_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sertifikat']['tmp_name'], $uploadDir . $fileName)) {
                    if ($file_sertifikat && file_exists(BASE_PATH . $file_sertifikat)) @unlink(BASE_PATH . $file_sertifikat);
                    $file_sertifikat = '/public/uploads/pelatihan/sertifikat/' . $fileName;
                }
            }
        }

        // Upload Foto Dokumentasi jika ada
        $foto_dokumentasi = $pelatihan['foto_dokumentasi'];
        if (isset($_FILES['foto_dokumentasi']) && $_FILES['foto_dokumentasi']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/pelatihan/foto/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $fileExt = strtolower(pathinfo($_FILES['foto_dokumentasi']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                $fileName = 'FOTO_DIKLAT_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['foto_dokumentasi']['tmp_name'], $uploadDir . $fileName)) {
                    if ($foto_dokumentasi && file_exists(BASE_PATH . $foto_dokumentasi)) @unlink(BASE_PATH . $foto_dokumentasi);
                    $foto_dokumentasi = '/public/uploads/pelatihan/foto/' . $fileName;
                }
            }
        }

        $db->update('pegawai_pelatihan', [
            'pegawai_id' => $_POST['pegawai_id'],
            'nama_pelatihan' => trim($_POST['nama_pelatihan']),
            'jenis_pelatihan' => $_POST['jenis_pelatihan'],
            'penyelenggara' => trim($_POST['penyelenggara']),
            'tempat' => trim($_POST['tempat'] ?? ''),
            'tahun' => trim($_POST['tahun']),
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null,
            'jumlah_jam' => !empty($_POST['jumlah_jam']) ? (int)$_POST['jumlah_jam'] : 0,
            'nomor_sertifikat' => trim($_POST['nomor_sertifikat'] ?? ''),
            'peran' => $_POST['peran'] ?? 'Peserta',
            'file_sertifikat' => $file_sertifikat,
            'foto_dokumentasi' => $foto_dokumentasi,
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ], 'id = ?', [$id]);

        Response::withSuccess(url('kelola-pegawai/pelatihan'), 'Data riwayat pelatihan pegawai berhasil diperbarui.');
    }

    /**
     * Hapus Riwayat Pelatihan Pegawai
     */
    public static function deletePelatihan(string $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('kelola-pegawai/pelatihan'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $pelatihan = $db->find("SELECT * FROM pegawai_pelatihan WHERE id = ?", [$id]);
        if ($pelatihan) {
            if ($pelatihan['file_sertifikat'] && file_exists(BASE_PATH . $pelatihan['file_sertifikat'])) {
                @unlink(BASE_PATH . $pelatihan['file_sertifikat']);
            }
            if ($pelatihan['foto_dokumentasi'] && file_exists(BASE_PATH . $pelatihan['foto_dokumentasi'])) {
                @unlink(BASE_PATH . $pelatihan['foto_dokumentasi']);
            }
            $db->delete('pegawai_pelatihan', 'id = ?', [$id]);
        }

        Response::withSuccess(url('kelola-pegawai/pelatihan'), 'Data riwayat pelatihan berhasil dihapus.');
    }

    /**
     * Portofolio Pelatihan & Diklat Per Pegawai
     */
    public static function pelatihanPegawai(string $id): void
    {
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        if (!$pegawai) {
            Response::withError(url('kelola-pegawai/pelatihan'), 'Data pegawai tidak ditemukan.');
            return;
        }

        $pageTitle = 'Portofolio Pelatihan - ' . $pegawai['nama'];
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Riwayat Pelatihan', 'url' => url('kelola-pegawai/pelatihan')],
            ['label' => $pegawai['nama']]
        ];

        $pelatihanList = $db->findAll("
            SELECT * FROM pegawai_pelatihan 
            WHERE pegawai_id = ? 
            ORDER BY tahun DESC, tanggal_mulai DESC, id DESC
        ", [$id]);

        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/pelatihan/detail_pegawai.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Cetak Curriculum Vitae (CV) Pegawai Format F4
     */
    public static function cetakCv(string $id): void
    {
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);

        if (!$pegawai) {
            Response::withError(url('kelola-pegawai'), 'Data pegawai tidak ditemukan.');
            return;
        }

        $pageTitle = 'Curriculum Vitae - ' . $pegawai['nama'];

        // 1. Pendidikan
        $pendidikan = $db->findAll("SELECT * FROM pegawai_pendidikan WHERE pegawai_id = ? ORDER BY id ASC", [$id]);

        // 2. Susunan Anggota Keluarga
        $keluargaList = $db->findAll("SELECT * FROM pegawai_keluarga WHERE pegawai_id = ? ORDER BY id ASC", [$id]);

        // 3. Keahlian & Keterampilan (Skill)
        $skillList = $db->findAll("SELECT * FROM pegawai_skill WHERE pegawai_id = ? ORDER BY kategori ASC, id ASC", [$id]);

        // 4. Riwayat Karir
        $karirList = $db->findAll("SELECT * FROM pegawai_karir WHERE pegawai_id = ? ORDER BY tmt_mulai DESC, id DESC", [$id]);

        // 5. Riwayat Prestasi
        $prestasiList = $db->findAll("SELECT * FROM pegawai_prestasi WHERE pegawai_id = ? ORDER BY tahun DESC, tanggal_peroleh DESC, id DESC", [$id]);

        // 6. Riwayat Pelatihan
        $pelatihanList = $db->findAll("SELECT * FROM pegawai_pelatihan WHERE pegawai_id = ? ORDER BY tahun DESC, tanggal_mulai DESC, id DESC", [$id]);

        // 5. Penugasan Aktif
        $activePenugasan = $db->find("
            SELECT pp.*, pg.nama_grup, pg.no_sk, pg.tanggal_sk, pg.penandatangan_nama, pg.penandatangan_jabatan
            FROM pegawai_penugasan pp
            JOIN penugasan_grup pg ON pp.grup_id = pg.id
            WHERE pp.pegawai_id = ? AND pg.is_active = 1 AND pp.status = 'Aktif'
            LIMIT 1
        ", [$id]);

        // Settings
        $sysSettings = $db->findAll("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        foreach ($sysSettings as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $penandatanganNama = $activePenugasan['penandatangan_nama'] ?? ($settings['penandatangan_sk_nama'] ?? 'H. Ahmad Dahlan, S.Pd., M.M.');
        $penandatanganJabatan = $activePenugasan['penandatangan_jabatan'] ?? ($settings['penandatangan_sk_jabatan'] ?? 'Ketua Yayasan Bina Insan Paripurna');

        include MODULES_PATH . '/kelola-pegawai/views/cetak_cv.php';
    }
}

