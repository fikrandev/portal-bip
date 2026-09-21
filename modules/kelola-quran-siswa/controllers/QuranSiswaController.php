<?php
/**
 * Portal BIP - Quran Siswa Controller
 * Comprehensive controller managing 3 Education Level Portals:
 * 1. Qur'an Siswa SD (Sekolah Dasar)
 * 2. Qur'an Siswa PAUD (Pendidikan Anak Usia Dini / TK)
 * 3. Qur'an Siswa SMP & SMA (Sekolah Menengah Pertama & Atas)
 */

require_once BASE_PATH . '/core/QuranHelper.php';

class QuranSiswaController
{
    /**
     * Central Hub Dashboard - /kelola-quran-siswa
     */
    public static function index(): void
    {
        $pageTitle = "Pusat Qur'an Siswa";
        $breadcrumbs = [['label' => "Qur'an Siswa", 'url' => url('kelola-quran-siswa')]];
        $activeJenjang = 'HUB';

        $db = Database::getInstance();

        // 1. Hitung jumlah siswa per jenjang
        $counts = [
            'PAUD' => (int)$db->query("SELECT COUNT(*) FROM siswa WHERE UPPER(jenjang) IN ('PAUD','TK') AND is_active = 1")->fetchColumn(),
            'SD' => (int)$db->query("SELECT COUNT(*) FROM siswa WHERE (UPPER(jenjang) = 'SD' OR jenjang IS NULL OR jenjang = '') AND is_active = 1")->fetchColumn(),
            'SMP_SMA' => (int)$db->query("SELECT COUNT(*) FROM siswa WHERE UPPER(jenjang) IN ('SMP','SMA') AND is_active = 1")->fetchColumn(),
        ];

        // 2. Hitung jumlah setoran per jenjang
        $stats = [
            'paud_setoran' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'PAUD'")->fetchColumn(),
            'sd_setoran' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'SD'")->fetchColumn(),
            'smp_sma_setoran' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'SMP_SMA'")->fetchColumn(),
        ];

        // 3. Riwayat setoran terkini seluruh unit (15 data terakhir)
        $recentSetoran = $db->findAll("
            SELECT qs.*, s.nama_lengkap, s.kelas
            FROM quran_siswa_setoran qs
            JOIN siswa s ON qs.siswa_id = s.id
            ORDER BY qs.tanggal DESC, qs.id DESC
            LIMIT 15
        ");

        // 4. Seluruh siswa aktif untuk modal dropdown
        $allStudents = $db->findAll("
            SELECT id, nama_lengkap, kelas, 
                   CASE 
                       WHEN UPPER(jenjang) IN ('PAUD','TK') THEN 'PAUD'
                       WHEN UPPER(jenjang) IN ('SMP','SMA') THEN 'SMP_SMA'
                       ELSE 'SD'
                   END as jenjang
            FROM siswa 
            WHERE is_active = 1 
            ORDER BY nama_lengkap ASC
        ");

        ob_start();
        include MODULES_PATH . '/kelola-quran-siswa/views/hub.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Portal Qur'an Siswa SD - /kelola-quran-siswa-sd
     */
    public static function sdIndex(): void
    {
        $pageTitle = "Qur'an Siswa SD";
        $breadcrumbs = [
            ['label' => "Qur'an Siswa", 'url' => url('kelola-quran-siswa')],
            ['label' => "Siswa SD"]
        ];
        $activeJenjang = 'SD';

        $db = Database::getInstance();

        // Counts per jenjang untuk tab navigasi
        $counts = self::getJenjangStudentCounts();

        // Filters
        $searchQuery = trim($_GET['search'] ?? '');
        $filterKelas = trim($_GET['kelas'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Where clause siswa SD
        $where = "(UPPER(s.jenjang) = 'SD' OR s.jenjang IS NULL OR s.jenjang = '') AND s.is_active = 1";
        $params = [];

        if ($searchQuery !== '') {
            $where .= " AND (s.nama_lengkap LIKE ? OR s.nisn LIKE ? OR s.nis LIKE ?)";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
            $params[] = "%$searchQuery%";
        }

        if ($filterKelas !== '') {
            $where .= " AND s.kelas = ?";
            $params[] = $filterKelas;
        }

        // Total siswa SD sesuai filter
        $countStmt = $db->query("SELECT COUNT(*) FROM siswa s WHERE $where", $params);
        $totalStudents = (int)$countStmt->fetchColumn();
        $totalPages = ceil($totalStudents / $limit);

        // Ambil siswa SD beserta setoran terakhir & total setoran
        $studentsQuery = "
            SELECT s.id, s.nama_lengkap, s.nis, s.nisn, s.kelas, s.jenjang,
                   (SELECT COUNT(*) FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'SD') as setoran_count,
                   (SELECT qs.surah_nama FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'SD' ORDER BY qs.tanggal DESC, qs.id DESC LIMIT 1) as latest_surah,
                   (SELECT CONCAT(qs.ayat_awal, '-', qs.ayat_akhir) FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'SD' ORDER BY qs.tanggal DESC, qs.id DESC LIMIT 1) as latest_ayat,
                   (SELECT qs.tanggal FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'SD' ORDER BY qs.tanggal DESC, qs.id DESC LIMIT 1) as latest_tanggal
            FROM siswa s
            WHERE $where
            ORDER BY s.kelas ASC, s.nama_lengkap ASC
            LIMIT $limit OFFSET $offset
        ";
        $students = $db->findAll($studentsQuery, $params);

        // Riwayat Setoran SD Terbaru (Kolom Kanan)
        $setoranList = $db->findAll("
            SELECT qs.*, s.nama_lengkap, s.kelas
            FROM quran_siswa_setoran qs
            JOIN siswa s ON qs.siswa_id = s.id
            WHERE qs.jenjang = 'SD'
            ORDER BY qs.tanggal DESC, qs.id DESC
            LIMIT 30
        ");

        // Stats SD
        $stats = [
            'total_siswa' => $counts['SD'],
            'total_setoran' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'SD'")->fetchColumn(),
            'total_mutqin' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'SD' AND status_lulus = 'mutqin'")->fetchColumn(),
            'total_ulang' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'SD' AND status_lulus IN ('ulang','perlu_bimbingan')")->fetchColumn(),
        ];

        // Daftar kelas SD untuk filter dropdown
        $kelasList = $db->query("
            SELECT DISTINCT kelas FROM siswa 
            WHERE (UPPER(jenjang) = 'SD' OR jenjang IS NULL OR jenjang = '') AND kelas IS NOT NULL AND kelas != '' 
            ORDER BY kelas ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        // Seluruh siswa aktif untuk modal form
        $allStudents = $db->findAll("
            SELECT id, nama_lengkap, kelas, 'SD' as jenjang
            FROM siswa 
            WHERE (UPPER(jenjang) = 'SD' OR jenjang IS NULL OR jenjang = '') AND is_active = 1 
            ORDER BY nama_lengkap ASC
        ");

        ob_start();
        include MODULES_PATH . '/kelola-quran-siswa/views/sd/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Helper Render View Khusus Qur'an PAUD dengan Custom Sidebar (4 Menu Utama)
     */
    private static function renderPaudView(string $viewPath, array $data = []): void
    {
        extract($data);
        $customSidebar = MODULES_PATH . '/kelola-quran-siswa/views/paud/sidebar.php';
        ob_start();
        include MODULES_PATH . '/kelola-quran-siswa/views/paud/' . $viewPath . '.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Fitur 1: Dashboard Qur'an PAUD / TK - /kelola-quran-siswa-paud
     */
    public static function paudIndex(): void
    {
        $pageTitle = "Dashboard Qur'an PAUD / TK";
        $breadcrumbs = [
            ['label' => "Qur'an Siswa", 'url' => url('kelola-quran-siswa')],
            ['label' => "Qur'an PAUD"]
        ];

        $db = Database::getInstance();
        $counts = self::getJenjangStudentCounts();

        // 1. Ambil grup target pembelajaran aktif
        $groups = $db->findAll("
            SELECT g.*, 
                   (SELECT COUNT(DISTINCT qpn.siswa_id) FROM quran_paud_nilai qpn WHERE qpn.group_id = g.id) as total_santri_dinilai
            FROM quran_paud_group g
            WHERE g.deleted_at IS NULL AND g.is_active = 1
            ORDER BY g.id DESC
        ");

        // 2. Ambil riwayat penilaian santri PAUD terkini
        $recentNilai = $db->findAll("
            SELECT qpn.*, s.nama_lengkap, s.nis, s.kelas, g.kategori, g.nama_grup
            FROM quran_paud_nilai qpn
            JOIN siswa s ON qpn.siswa_id = s.id
            JOIN quran_paud_group g ON qpn.group_id = g.id
            ORDER BY qpn.tanggal_penilaian DESC, qpn.id DESC
            LIMIT 10
        ");

        // 3. Stats Dashboard PAUD
        $stats = [
            'total_siswa' => $counts['PAUD'] ?? 0,
            'total_grup_aktif' => count($groups),
            'total_nilai' => (int)$db->query("SELECT COUNT(*) FROM quran_paud_nilai")->fetchColumn(),
            'total_mutqin' => (int)$db->query("SELECT COUNT(*) FROM quran_paud_nilai WHERE status_lulus = 'Mutqin'")->fetchColumn(),
        ];

        self::renderPaudView('index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'groups' => $groups,
            'recentNilai' => $recentNilai,
            'stats' => $stats
        ]);
    }

    /**
     * Fitur 2: Pengaturan Target - Daftar Grup / Wadah Target
     */
    public static function paudTargetList(): void
    {
        $pageTitle = "Pengaturan Target Qur'an PAUD";
        $breadcrumbs = [
            ['label' => "Qur'an PAUD", 'url' => url('kelola-quran-siswa-paud')],
            ['label' => "Pengaturan Target"]
        ];

        $db = Database::getInstance();
        $filterStatus = $_GET['status'] ?? '';
        $filterKelas = $_GET['kelas'] ?? '';

        $where = "g.deleted_at IS NULL";
        $params = [];

        if ($filterStatus !== '') {
            $where .= " AND g.is_active = ?";
            $params[] = (int)$filterStatus;
        }

        if (!empty($filterKelas)) {
            $where .= " AND g.kelas = ?";
            $params[] = $filterKelas;
        }

        $groups = $db->findAll("
            SELECT g.*, 
                   (SELECT COUNT(DISTINCT qpn.siswa_id) FROM quran_paud_nilai qpn WHERE qpn.group_id = g.id) as total_santri_dinilai
            FROM quran_paud_group g
            WHERE $where
            ORDER BY g.id DESC
        ", $params);

        $countAll = (int)$db->query("SELECT COUNT(*) FROM quran_paud_group WHERE deleted_at IS NULL")->fetchColumn();
        $countActive = (int)$db->query("SELECT COUNT(*) FROM quran_paud_group WHERE deleted_at IS NULL AND is_active = 1")->fetchColumn();
        $countInactive = (int)$db->query("SELECT COUNT(*) FROM quran_paud_group WHERE deleted_at IS NULL AND is_active = 0")->fetchColumn();

        $kelasList = $db->query("
            SELECT DISTINCT kelas FROM siswa 
            WHERE UPPER(jenjang) IN ('PAUD','TK') AND kelas IS NOT NULL AND kelas != '' 
            ORDER BY kelas ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        self::renderPaudView('target/index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'groups' => $groups,
            'countAll' => $countAll,
            'countActive' => $countActive,
            'countInactive' => $countInactive,
            'kelasList' => $kelasList
        ]);
    }

    /**
     * Fitur 2: Pengaturan Target - Form Buat Grup Target Baru
     */
    public static function paudTargetCreate(): void
    {
        $pageTitle = "Buat Grup Target Baru";
        $breadcrumbs = [
            ['label' => "Qur'an PAUD", 'url' => url('kelola-quran-siswa-paud')],
            ['label' => "Pengaturan Target", 'url' => url('kelola-quran-siswa-paud/target')],
            ['label' => "Buat Grup Baru"]
        ];

        $db = Database::getInstance();

        // Ambil daftar kelas PAUD
        $kelasList = $db->query("
            SELECT DISTINCT kelas FROM siswa 
            WHERE UPPER(jenjang) IN ('PAUD','TK') AND kelas IS NOT NULL AND kelas != '' 
            ORDER BY kelas ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($kelasList)) {
            $kelasList = ['TK A - Al-Kautsar', 'TK B - Ar-Rahman', 'Kelompok Bermain (KB)'];
        }

        // Ambil tahun akademik aktif dari sistem & daftar seluruh tahun akademik
        $taAktif = $db->find("SELECT id, nama_tahun, is_active FROM tahun_akademik WHERE is_active = 1 LIMIT 1");
        $taList = $db->findAll("SELECT id, nama_tahun, is_active FROM tahun_akademik ORDER BY tanggal_mulai DESC");

        // Ambil guru / ustadzah pengampu
        $guruList = $db->findAll("SELECT id, nama FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        self::renderPaudView('target/create', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'kelasList' => $kelasList,
            'guruList' => $guruList,
            'taAktif' => $taAktif,
            'taList' => $taList
        ]);
    }

    /**
     * Fitur 2: Pengaturan Target - Simpan Grup Target Baru (POST)
     * Hanya menyimpan identitas grup (nama grup, tahun akademik aktif sistem, status), lalu redirect ke atur target materi
     */
    public static function paudTargetStore(): void
    {
        if (class_exists('CSRF') && !CSRF::validate()) {
            Response::withError(url('kelola-quran-siswa-paud/target'), 'Sesi tidak valid.');
            return;
        }

        $namaGrup = trim($_POST['nama_grup'] ?? '');
        $taId = !empty($_POST['tahun_akademik_id']) ? intval($_POST['tahun_akademik_id']) : (defined('SYS_TAHUN_AKADEMIK_ID') ? SYS_TAHUN_AKADEMIK_ID : 1);
        $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
        $semester = in_array($_POST['semester'] ?? '', ['Ganjil', 'Genap']) ? $_POST['semester'] : 'Ganjil';
        $kelas = 'PAUD / TK';
        $guruId = null;
        $guruNama = '';
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if (empty($namaGrup)) {
            Response::withError(url('kelola-quran-siswa-paud/target/create'), 'Mohon isi Nama Group Target.');
            return;
        }

        // Inisialisasi format target materi (Tahsin & Tahfidz) standar awal
        $targetMateri = json_encode([
            'tahsin' => [
                'metode' => "Iqro'",
                'jilid' => 1,
                'halaman_awal' => 1,
                'halaman_target' => 30,
                'fokus' => ''
            ],
            'tahfidz' => [
                'surah' => [],
                'doa' => [],
                'hadits' => [],
                'fokus' => ''
            ]
        ], JSON_UNESCAPED_UNICODE);

        $db = Database::getInstance();
        $db->query("
            INSERT INTO quran_paud_group (
                nama_grup, kategori, tahun_akademik_id, semester, kelas,
                guru_id, guru_nama, target_materi, deskripsi, is_active, created_by
            ) VALUES (?, 'all', ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $namaGrup, $taId, $semester, $kelas,
            $guruId, $guruNama, $targetMateri, $deskripsi, $isActive, Auth::id() ?: null
        ]);

        $newId = (int)$db->lastInsertId();
        if (!$newId) {
            $lastRow = $db->find("SELECT id FROM quran_paud_group WHERE nama_grup = ? ORDER BY id DESC LIMIT 1", [$namaGrup]);
            $newId = (int)($lastRow['id'] ?? 0);
        }

        Response::withSuccess(url('kelola-quran-siswa-paud/target/manage/' . $newId), 'Alhamdulillah, group target berhasil dibuat! Silakan tentukan target materi Tahsin & Tahfidz di bawah ini.');
    }

    /**
     * Fitur 2: Masuk ke Dalam Grup Target - Kelola & Atur Target Materi (Tahsin & Tahfidz)
     */
    public static function paudTargetManage(string|int $id = 0): void
    {
        $id = intval($id ?: ($_GET['id'] ?? 0));
        $db = Database::getInstance();
        $group = $db->find("SELECT * FROM quran_paud_group WHERE id = ? AND deleted_at IS NULL", [$id]);

        if (!$group) {
            Response::withError(url('kelola-quran-siswa-paud/target'), 'Grup target tidak ditemukan.');
            return;
        }

        $pageTitle = "Kelola Target: " . $group['nama_grup'];
        $breadcrumbs = [
            ['label' => "Qur'an PAUD", 'url' => url('kelola-quran-siswa-paud')],
            ['label' => "Pengaturan Target", 'url' => url('kelola-quran-siswa-paud/target')],
            ['label' => $group['nama_grup']]
        ];

        // Ambil info tahun akademik
        $ta = $db->find("SELECT id, nama_tahun, is_active FROM tahun_akademik WHERE id = ?", [$group['tahun_akademik_id']]);

        // Decode target materi
        $rawTarget = json_decode($group['target_materi'] ?? '{}', true) ?: [];
        if (isset($rawTarget['tahsin']) || isset($rawTarget['tahfidz'])) {
            $tahsin = $rawTarget['tahsin'] ?? [];
            $tahfidz = $rawTarget['tahfidz'] ?? [];
        } else {
            $isLegTahsin = ($group['kategori'] === 'tahsin');
            $tahsin = $isLegTahsin ? $rawTarget : [];
            $tahfidz = !$isLegTahsin ? $rawTarget : [];
        }

        // Ambil seluruh santri jenjang PAUD/TK beserta data penilaian terkini
        $santriList = $db->findAll("
            SELECT s.id, s.nama_lengkap, s.nis, s.nisn, s.jenis_kelamin, s.foto, s.kelas,
                   p.id as penilaian_id, p.status_lulus, p.materi_dinilai, p.nilai_kelancaran, p.nilai_makhraj, p.nilai_adab, p.bintang, p.updated_at as tgl_nilai
            FROM siswa s
            LEFT JOIN quran_paud_nilai p ON p.siswa_id = s.id AND p.group_id = ?
            WHERE UPPER(s.jenjang) IN ('PAUD','TK') AND s.is_active = 1
            ORDER BY s.nama_lengkap ASC
        ", [$id]);

        self::renderPaudView('target/manage', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'group' => $group,
            'ta' => $ta,
            'tahsin' => $tahsin,
            'tahfidz' => $tahfidz,
            'santriList' => $santriList
        ]);
    }

    /**
     * Fitur 2: Simpan Target Materi di Dalam Grup (Tahsin & Tahfidz)
     */
    public static function paudTargetSaveMateri(string|int $id = 0): void
    {
        if (class_exists('CSRF') && !CSRF::validate()) {
            Response::withError(url('kelola-quran-siswa-paud/target'), 'Sesi tidak valid.');
            return;
        }

        $id = intval($id ?: ($_POST['id'] ?? 0));
        $db = Database::getInstance();
        $group = $db->find("SELECT * FROM quran_paud_group WHERE id = ? AND deleted_at IS NULL", [$id]);

        if (!$group) {
            Response::withError(url('kelola-quran-siswa-paud/target'), 'Grup target tidak ditemukan.');
            return;
        }

        $tahfidzSurah = $_POST['tahfidz_surah'] ?? [];
        $tahfidzDoa = $_POST['tahfidz_doa'] ?? [];
        $tahfidzHadits = $_POST['tahfidz_hadits'] ?? [];

        $targetMateri = json_encode([
            'tahsin' => [
                'metode' => trim($_POST['tahsin_metode'] ?? "Iqro'"),
                'jilid' => intval($_POST['tahsin_jilid'] ?? 1),
                'halaman_awal' => intval($_POST['tahsin_halaman_awal'] ?? 1),
                'halaman_target' => intval($_POST['tahsin_halaman_target'] ?? 30),
                'fokus' => trim($_POST['tahsin_fokus'] ?? '')
            ],
            'tahfidz' => [
                'surah' => is_array($tahfidzSurah) ? array_values($tahfidzSurah) : [],
                'doa' => is_array($tahfidzDoa) ? array_values($tahfidzDoa) : [],
                'hadits' => is_array($tahfidzHadits) ? array_values($tahfidzHadits) : [],
                'fokus' => trim($_POST['tahfidz_fokus'] ?? '')
            ]
        ], JSON_UNESCAPED_UNICODE);

        $db->query("UPDATE quran_paud_group SET target_materi = ?, updated_at = NOW() WHERE id = ?", [$targetMateri, $id]);

        Response::withSuccess(url('kelola-quran-siswa-paud/target/manage/' . $id), 'Alhamdulillah, target materi pembelajaran (Tahsin & Tahfidz) untuk grup ini berhasil disimpan!');
    }

    /**
     * Fitur 2: Pengaturan Target - Edit Info Grup Target
     */
    public static function paudTargetEdit(string|int $id = 0): void
    {
        $id = intval($id ?: ($_GET['id'] ?? 0));
        $db = Database::getInstance();
        $group = $db->find("SELECT * FROM quran_paud_group WHERE id = ? AND deleted_at IS NULL", [$id]);

        if (!$group) {
            Response::withError(url('kelola-quran-siswa-paud/target'), 'Grup target tidak ditemukan.');
            return;
        }

        $pageTitle = "Edit Info Grup Target";
        $breadcrumbs = [
            ['label' => "Qur'an PAUD", 'url' => url('kelola-quran-siswa-paud')],
            ['label' => "Pengaturan Target", 'url' => url('kelola-quran-siswa-paud/target')],
            ['label' => "Edit Info Grup"]
        ];

        $kelasList = $db->query("
            SELECT DISTINCT kelas FROM siswa 
            WHERE UPPER(jenjang) IN ('PAUD','TK') AND kelas IS NOT NULL AND kelas != '' 
            ORDER BY kelas ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        $taAktif = $db->find("SELECT id, nama_tahun, is_active FROM tahun_akademik WHERE is_active = 1 LIMIT 1");
        $taList = $db->findAll("SELECT id, nama_tahun, is_active FROM tahun_akademik ORDER BY tanggal_mulai DESC");
        $guruList = $db->findAll("SELECT id, nama FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");

        self::renderPaudView('target/edit', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'group' => $group,
            'kelasList' => $kelasList,
            'guruList' => $guruList,
            'taAktif' => $taAktif,
            'taList' => $taList
        ]);
    }

    /**
     * Fitur 2: Pengaturan Target - Update Info Grup Target (POST)
     */
    public static function paudTargetUpdate(string|int $id = 0): void
    {
        if (class_exists('CSRF') && !CSRF::validate()) {
            Response::withError(url('kelola-quran-siswa-paud/target'), 'Sesi tidak valid.');
            return;
        }

        $id = intval($id ?: ($_POST['id'] ?? 0));
        $namaGrup = trim($_POST['nama_grup'] ?? '');
        $taId = !empty($_POST['tahun_akademik_id']) ? intval($_POST['tahun_akademik_id']) : (defined('SYS_TAHUN_AKADEMIK_ID') ? SYS_TAHUN_AKADEMIK_ID : 1);
        $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;

        if (empty($namaGrup)) {
            Response::withError(url('kelola-quran-siswa-paud/target/edit/' . $id), 'Mohon isi Nama Group Target.');
            return;
        }

        $db = Database::getInstance();
        $db->query("
            UPDATE quran_paud_group SET
                nama_grup = ?, tahun_akademik_id = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ", [$namaGrup, $taId, $isActive, $id]);

        Response::withSuccess(url('kelola-quran-siswa-paud/target'), 'Informasi group target berhasil diperbarui.');
    }

    /**
     * Fitur 2: Toggle Status Aktif Grup Target
     */
    public static function paudTargetToggleStatus(string|int $id = 0): void
    {
        $id = intval($id ?: ($_POST['id'] ?? 0));
        $db = Database::getInstance();
        $db->query("UPDATE quran_paud_group SET is_active = IF(is_active = 1, 0, 1), updated_at = NOW() WHERE id = ?", [$id]);
        Response::withSuccess(url('kelola-quran-siswa-paud/target'), 'Status aktif grup berhasil diubah.');
    }

    /**
     * Fitur 2: Hapus Grup Target
     */
    public static function paudTargetDelete(string|int $id = 0): void
    {
        $id = intval($id ?: ($_POST['id'] ?? 0));
        $db = Database::getInstance();
        $db->query("UPDATE quran_paud_group SET deleted_at = NOW() WHERE id = ?", [$id]);
        Response::withSuccess(url('kelola-quran-siswa-paud/target'), 'Grup target berhasil dihapus.');
    }

    /**
     * Fitur 3: Input Penilaian Qur'an PAUD - Halaman Utama / Daftar Grup Aktif
     */
    public static function paudPenilaianIndex(): void
    {
        $pageTitle = "Input Penilaian Qur'an PAUD";
        $breadcrumbs = [
            ['label' => "Qur'an PAUD", 'url' => url('kelola-quran-siswa-paud')],
            ['label' => "Input Penilaian"]
        ];

        $db = Database::getInstance();
        $groups = $db->findAll("
            SELECT g.*, 
                   (SELECT COUNT(DISTINCT qpn.siswa_id) FROM quran_paud_nilai qpn WHERE qpn.group_id = g.id) as total_santri_dinilai
            FROM quran_paud_group g
            WHERE g.deleted_at IS NULL AND g.is_active = 1
            ORDER BY g.id DESC
        ");

        self::renderPaudView('penilaian/index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'groups' => $groups
        ]);
    }

    /**
     * Fitur 3: Input Penilaian - Form Input Nilai Santri per Grup Target
     */
    public static function paudPenilaianForm(string|int $groupId = 0): void
    {
        $groupId = intval($groupId ?: ($_GET['groupId'] ?? 0));
        $db = Database::getInstance();

        $group = $db->find("SELECT * FROM quran_paud_group WHERE id = ? AND deleted_at IS NULL", [$groupId]);
        if (!$group) {
            Response::withError(url('kelola-quran-siswa-paud/penilaian'), 'Grup target tidak ditemukan.');
            return;
        }

        $pageTitle = "Lembar Penilaian: " . $group['nama_grup'];
        $breadcrumbs = [
            ['label' => "Qur'an PAUD", 'url' => url('kelola-quran-siswa-paud')],
            ['label' => "Input Penilaian", 'url' => url('kelola-quran-siswa-paud/penilaian')],
            ['label' => $group['nama_grup']]
        ];

        // Ambil santri jenjang PAUD/TK
        if (empty($group['kelas']) || in_array($group['kelas'], ['PAUD / TK', 'Semua Kelas', 'Semua Kelas PAUD'])) {
            $students = $db->findAll("
                SELECT id, nis, nisn, nama_lengkap, kelas 
                FROM siswa 
                WHERE UPPER(jenjang) IN ('PAUD','TK') AND is_active = 1 
                ORDER BY nama_lengkap ASC
            ");
        } else {
            $students = $db->findAll("
                SELECT id, nis, nisn, nama_lengkap, kelas 
                FROM siswa 
                WHERE kelas = ? AND is_active = 1 
                ORDER BY nama_lengkap ASC
            ", [$group['kelas']]);
            if (empty($students)) {
                $students = $db->findAll("
                    SELECT id, nis, nisn, nama_lengkap, kelas 
                    FROM siswa 
                    WHERE UPPER(jenjang) IN ('PAUD','TK') AND is_active = 1 
                    ORDER BY nama_lengkap ASC
                ");
            }
        }

        // Ambil nilai yang sudah pernah diinput untuk grup ini
        $existingNilaiRaw = $db->findAll("SELECT * FROM quran_paud_nilai WHERE group_id = ?", [$groupId]);
        $existingNilai = [];
        foreach ($existingNilaiRaw as $row) {
            $existingNilai[$row['siswa_id']] = $row;
        }

        self::renderPaudView('penilaian/input', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'group' => $group,
            'students' => $students,
            'existingNilai' => $existingNilai
        ]);
    }

    /**
     * Fitur 3: Input Penilaian - Simpan Penilaian Santri (POST)
     */
    public static function paudPenilaianStore(string|int $groupId = 0): void
    {
        if (class_exists('CSRF') && !CSRF::validate()) {
            Response::withError(url('kelola-quran-siswa-paud/penilaian'), 'Sesi tidak valid.');
            return;
        }

        $groupId = intval($groupId ?: ($_POST['group_id'] ?? 0));
        $tanggalPenilaian = trim($_POST['tanggal_penilaian'] ?? date('Y-m-d'));
        $materiDefault = trim($_POST['materi_default'] ?? 'Iqro Jilid 1');
        $nilaiData = $_POST['nilai'] ?? [];

        $db = Database::getInstance();
        $group = $db->find("SELECT * FROM quran_paud_group WHERE id = ?", [$groupId]);
        $isTahsin = ($group && $group['kategori'] === 'tahsin');

        foreach ($nilaiData as $siswaId => $row) {
            $siswaId = intval($siswaId);
            if (!$siswaId) continue;

            $materi = !empty($row['materi_dinilai']) ? trim($row['materi_dinilai']) : $materiDefault;
            $kelancaran = in_array($row['nilai_kelancaran'] ?? '', ['A','B','C','D']) ? $row['nilai_kelancaran'] : 'A';
            $makhraj = in_array($row['nilai_makhraj'] ?? '', ['A','B','C','D']) ? $row['nilai_makhraj'] : 'A';
            $adab = in_array($row['nilai_adab'] ?? '', ['A','B','C','D']) ? $row['nilai_adab'] : 'A';
            $bintang = max(1, min(5, intval($row['bintang'] ?? 5)));
            $statusLulus = in_array($row['status_lulus'] ?? '', ['Mutqin','Lancar','Ulang','Perlu Bimbingan']) ? $row['status_lulus'] : 'Lancar';
            $catatan = trim($row['catatan'] ?? '');

            // Simpan atau perbarui nilai di quran_paud_nilai
            $exist = $db->find("SELECT id FROM quran_paud_nilai WHERE group_id = ? AND siswa_id = ?", [$groupId, $siswaId]);
            if ($exist) {
                $db->query("
                    UPDATE quran_paud_nilai SET
                        tanggal_penilaian = ?, materi_dinilai = ?, nilai_kelancaran = ?,
                        nilai_makhraj = ?, nilai_adab = ?, bintang = ?, status_lulus = ?,
                        catatan = ?, updated_at = NOW()
                    WHERE id = ?
                ", [$tanggalPenilaian, $materi, $kelancaran, $makhraj, $adab, $bintang, $statusLulus, $catatan, $exist['id']]);
            } else {
                $db->query("
                    INSERT INTO quran_paud_nilai (
                        group_id, siswa_id, tanggal_penilaian, materi_dinilai,
                        nilai_kelancaran, nilai_makhraj, nilai_adab, bintang, status_lulus, catatan
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$groupId, $siswaId, $tanggalPenilaian, $materi, $kelancaran, $makhraj, $adab, $bintang, $statusLulus, $catatan]);
            }

            // Sinkronisasi otomatis ke quran_siswa_target
            $db->query("
                INSERT INTO quran_siswa_target (siswa_id, jenjang, capaian_terakhir, status, updated_at)
                VALUES (?, 'PAUD', ?, 'Aktif', NOW())
                ON DUPLICATE KEY UPDATE capaian_terakhir = VALUES(capaian_terakhir), updated_at = NOW()
            ", [$siswaId, $materi . " ($statusLulus)"]);
        }

        Response::withSuccess(url('kelola-quran-siswa-paud/penilaian/' . $groupId), 'Alhamdulillah, penilaian santri Qur\'an PAUD berhasil disimpan!');
    }

    /**
     * Fitur 4: Rekap Nilai - Halaman Rekapitulasi & Laporan Capaian Santri
     */
    public static function paudRekapIndex(): void
    {
        $pageTitle = "Rekap Nilai Qur'an PAUD";
        $breadcrumbs = [
            ['label' => "Qur'an PAUD", 'url' => url('kelola-quran-siswa-paud')],
            ['label' => "Rekap Nilai"]
        ];

        $db = Database::getInstance();
        $filterKategori = trim($_GET['kategori'] ?? '');
        $filterKelas = trim($_GET['kelas'] ?? '');
        $filterStatus = trim($_GET['status'] ?? '');

        $where = "1=1";
        $params = [];

        if (!empty($filterKategori)) {
            $where .= " AND g.kategori = ?";
            $params[] = $filterKategori;
        }

        if (!empty($filterKelas)) {
            $where .= " AND s.kelas = ?";
            $params[] = $filterKelas;
        }

        if (!empty($filterStatus)) {
            $where .= " AND qpn.status_lulus = ?";
            $params[] = $filterStatus;
        }

        $rekapList = $db->findAll("
            SELECT qpn.*, s.nama_lengkap, s.nis, s.kelas, g.nama_grup, g.kategori
            FROM quran_paud_nilai qpn
            JOIN siswa s ON qpn.siswa_id = s.id
            JOIN quran_paud_group g ON qpn.group_id = g.id
            WHERE $where
            ORDER BY qpn.tanggal_penilaian DESC, s.nama_lengkap ASC
        ", $params);

        // Stats Rekap
        $counts = self::getJenjangStudentCounts();
        $avgBintang = (float)$db->query("SELECT COALESCE(AVG(bintang), 5) FROM quran_paud_nilai")->fetchColumn();
        $totalMutqin = (int)$db->query("SELECT COUNT(*) FROM quran_paud_nilai WHERE status_lulus = 'Mutqin'")->fetchColumn();

        $rekapStats = [
            'total_siswa' => $counts['PAUD'] ?? 0,
            'total_penilaian' => count($rekapList),
            'avg_bintang' => $avgBintang,
            'total_mutqin' => $totalMutqin
        ];

        $kelasList = $db->query("
            SELECT DISTINCT kelas FROM siswa 
            WHERE UPPER(jenjang) IN ('PAUD','TK') AND kelas IS NOT NULL AND kelas != '' 
            ORDER BY kelas ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        self::renderPaudView('rekap/index', [
            'pageTitle' => $pageTitle,
            'breadcrumbs' => $breadcrumbs,
            'rekapList' => $rekapList,
            'rekapStats' => $rekapStats,
            'kelasList' => $kelasList
        ]);
    }

    /**
     * Fitur 4: Cetak Rekapitulasi Nilai Qur'an PAUD Siap Cetak A4
     */
    public static function paudRekapCetak(): void
    {
        $db = Database::getInstance();
        $filterKategori = trim($_GET['kategori'] ?? '');
        $filterKelas = trim($_GET['kelas'] ?? '');
        $filterStatus = trim($_GET['status'] ?? '');

        $where = "1=1";
        $params = [];

        if (!empty($filterKategori)) {
            $where .= " AND g.kategori = ?";
            $params[] = $filterKategori;
        }

        if (!empty($filterKelas)) {
            $where .= " AND s.kelas = ?";
            $params[] = $filterKelas;
        }

        if (!empty($filterStatus)) {
            $where .= " AND qpn.status_lulus = ?";
            $params[] = $filterStatus;
        }

        $rekapList = $db->findAll("
            SELECT qpn.*, s.nama_lengkap, s.nis, s.kelas, g.nama_grup, g.kategori
            FROM quran_paud_nilai qpn
            JOIN siswa s ON qpn.siswa_id = s.id
            JOIN quran_paud_group g ON qpn.group_id = g.id
            WHERE $where
            ORDER BY qpn.tanggal_penilaian DESC, s.nama_lengkap ASC
        ", $params);

        include MODULES_PATH . '/kelola-quran-siswa/views/paud/rekap/cetak.php';
        exit;
    }

    /**
     * Fitur 4: Export Rekap Nilai ke Spreadsheet CSV / XLS
     */
    public static function paudRekapExport(): void
    {
        $db = Database::getInstance();
        $filterKategori = trim($_GET['kategori'] ?? '');
        $filterKelas = trim($_GET['kelas'] ?? '');

        $where = "1=1";
        $params = [];

        if (!empty($filterKategori)) {
            $where .= " AND g.kategori = ?";
            $params[] = $filterKategori;
        }

        if (!empty($filterKelas)) {
            $where .= " AND s.kelas = ?";
            $params[] = $filterKelas;
        }

        $rows = $db->findAll("
            SELECT qpn.tanggal_penilaian, s.nis, s.nama_lengkap, s.kelas,
                   g.kategori, qpn.materi_dinilai, qpn.nilai_kelancaran,
                   qpn.nilai_makhraj, qpn.nilai_adab, qpn.bintang, qpn.status_lulus, qpn.catatan
            FROM quran_paud_nilai qpn
            JOIN siswa s ON qpn.siswa_id = s.id
            JOIN quran_paud_group g ON qpn.group_id = g.id
            WHERE $where
            ORDER BY qpn.tanggal_penilaian DESC, s.nama_lengkap ASC
        ", $params);

        $filename = 'rekap_nilai_quran_paud_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Tanggal', 'NIS', 'Nama Santri', 'Kelas', 'Kategori', 'Materi Dinilai', 'Kelancaran', 'Makhraj', 'Adab', 'Bintang (1-5)', 'Status', 'Catatan']);

        foreach ($rows as $r) {
            fputcsv($output, [
                $r['tanggal_penilaian'],
                $r['nis'],
                $r['nama_lengkap'],
                $r['kelas'],
                $r['kategori'] === 'tahsin' ? 'a. Tahsin' : 'b. Tahfidz',
                $r['materi_dinilai'],
                $r['nilai_kelancaran'],
                $r['nilai_makhraj'],
                $r['nilai_adab'],
                $r['bintang'],
                $r['status_lulus'],
                $r['catatan']
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * Portal Qur'an Siswa SMP & SMA IT - /kelola-quran-siswa-smp-sma
     */
    public static function smpSmaIndex(): void
    {
        $pageTitle = "Qur'an Siswa SMP & SMA";
        $breadcrumbs = [
            ['label' => "Qur'an Siswa", 'url' => url('kelola-quran-siswa')],
            ['label' => "Siswa SMP & SMA"]
        ];
        $activeJenjang = 'SMP_SMA';

        $db = Database::getInstance();
        $counts = self::getJenjangStudentCounts();

        // Ambil santri SMP & SMA beserta capaian
        $students = $db->findAll("
            SELECT s.id, s.nama_lengkap, s.nis, s.nisn, s.kelas, s.jenjang,
                   (SELECT COUNT(*) FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'SMP_SMA') as setoran_count,
                   (SELECT qs.surah_nama FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'SMP_SMA' ORDER BY qs.tanggal DESC, qs.id DESC LIMIT 1) as latest_surah,
                   (SELECT CONCAT(qs.ayat_awal, '-', qs.ayat_akhir) FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'SMP_SMA' ORDER BY qs.tanggal DESC, qs.id DESC LIMIT 1) as latest_ayat,
                   (SELECT qs.tanggal FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'SMP_SMA' ORDER BY qs.tanggal DESC, qs.id DESC LIMIT 1) as latest_tanggal
            FROM siswa s
            WHERE UPPER(s.jenjang) IN ('SMP','SMA') AND s.is_active = 1
            ORDER BY s.jenjang ASC, s.nama_lengkap ASC
        ");

        // Riwayat Setoran SMP/SMA
        $setoranList = $db->findAll("
            SELECT qs.*, s.nama_lengkap, s.kelas
            FROM quran_siswa_setoran qs
            JOIN siswa s ON qs.siswa_id = s.id
            WHERE qs.jenjang = 'SMP_SMA'
            ORDER BY qs.tanggal DESC, qs.id DESC
            LIMIT 30
        ");

        // Stats SMP & SMA
        $stats = [
            'total_siswa' => $counts['SMP_SMA'],
            'total_setoran' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'SMP_SMA'")->fetchColumn(),
            'total_tasmi' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'SMP_SMA' AND jenis_setoran = 'tasmi'")->fetchColumn(),
            'total_mutqin' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'SMP_SMA' AND status_lulus = 'mutqin'")->fetchColumn(),
        ];

        // All active students for modal form
        $allStudents = $db->findAll("
            SELECT id, nama_lengkap, kelas, 'SMP_SMA' as jenjang
            FROM siswa 
            WHERE UPPER(jenjang) IN ('SMP','SMA') AND is_active = 1 
            ORDER BY nama_lengkap ASC
        ");

        ob_start();
        include MODULES_PATH . '/kelola-quran-siswa/views/smp_sma/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Simpan Setoran Baru (POST)
     */
    public static function storeSetoran(): void
    {
        if (class_exists('CSRF') && !CSRF::validate()) {
            Response::withError(url('kelola-quran-siswa'), 'Sesi tidak valid. Silakan coba kembali.');
            return;
        }

        $returnUrl = $_POST['return_url'] ?? url('kelola-quran-siswa');
        $siswaId = intval($_POST['siswa_id'] ?? 0);
        $jenjang = trim($_POST['jenjang'] ?? 'SD');
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $jenisSetoran = trim($_POST['jenis_setoran'] ?? 'ziyadah');

        if (!$siswaId) {
            Response::withError($returnUrl, 'Mohon pilih siswa yang menyetor.');
            return;
        }

        // Validate & prepare values
        $iqroJilid = !empty($_POST['iqro_jilid']) ? intval($_POST['iqro_jilid']) : null;
        $iqroHalaman = !empty($_POST['iqro_halaman']) ? intval($_POST['iqro_halaman']) : null;
        $surahNomor = !empty($_POST['surah_nomor']) ? intval($_POST['surah_nomor']) : null;
        $surahNama = trim($_POST['surah_nama'] ?? '');
        $ayatAwal = !empty($_POST['ayat_awal']) ? intval($_POST['ayat_awal']) : null;
        $ayatAkhir = !empty($_POST['ayat_akhir']) ? intval($_POST['ayat_akhir']) : null;
        $juz = !empty($_POST['juz']) ? intval($_POST['juz']) : null;
        $halamanQuran = !empty($_POST['halaman_quran']) ? intval($_POST['halaman_quran']) : null;

        $nilaiKelancaran = in_array($_POST['nilai_kelancaran'] ?? '', ['A','B','C','D']) ? $_POST['nilai_kelancaran'] : 'A';
        $nilaiTajwid = in_array($_POST['nilai_tajwid'] ?? '', ['A','B','C','D']) ? $_POST['nilai_tajwid'] : 'A';
        $nilaiMakhorij = in_array($_POST['nilai_makhorij'] ?? '', ['A','B','C','D']) ? $_POST['nilai_makhorij'] : 'A';
        $statusLulus = in_array($_POST['status_lulus'] ?? '', ['lancar','ulang','mutqin','perlu_bimbingan']) ? $_POST['status_lulus'] : 'lancar';
        $catatan = trim($_POST['catatan'] ?? '');

        // Otomatis nama surah jika kosong tapi surah_nomor diisi
        if ($surahNomor && empty($surahNama)) {
            $surahData = QuranHelper::getSurah($surahNomor);
            if ($surahData) {
                $surahNama = $surahData['nama'];
                if (!$juz) $juz = $surahData['juz'];
            }
        }

        $guruId = Auth::id() ?: null;
        $db = Database::getInstance();

        $stmt = $db->query("
            INSERT INTO quran_siswa_setoran (
                siswa_id, guru_id, jenjang, tanggal, jenis_setoran,
                iqro_jilid, iqro_halaman, surah_nomor, surah_nama,
                ayat_awal, ayat_akhir, juz, halaman_quran,
                nilai_kelancaran, nilai_tajwid, nilai_makhorij, status_lulus, catatan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $siswaId, $guruId, $jenjang, $tanggal, $jenisSetoran,
            $iqroJilid, $iqroHalaman, $surahNomor, $surahNama,
            $ayatAwal, $ayatAkhir, $juz, $halamanQuran,
            $nilaiKelancaran, $nilaiTajwid, $nilaiMakhorij, $statusLulus, $catatan
        ]);

        // Perbarui target / capaian terakhir siswa
        $capaianStr = $jenisSetoran === 'iqro' 
            ? "Iqro Jilid $iqroJilid (Hal. $iqroHalaman)"
            : "$surahNama Ayat $ayatAwal-$ayatAkhir (Juz $juz)";

        $db->query("
            INSERT INTO quran_siswa_target (siswa_id, jenjang, capaian_terakhir, status, updated_at)
            VALUES (?, ?, ?, 'Aktif', NOW())
            ON DUPLICATE KEY UPDATE capaian_terakhir = VALUES(capaian_terakhir), updated_at = NOW()
        ", [$siswaId, $jenjang, $capaianStr]);

        Response::withSuccess($returnUrl, 'Alhamdulillah, catatan setoran Al-Qur\'an berhasil disimpan!');
    }

    /**
     * Hapus Catatan Setoran (POST)
     */
    public static function deleteSetoran(array $params = []): void
    {
        $id = intval($params['id'] ?? $_GET['id'] ?? 0);
        $returnUrl = $_POST['return_url'] ?? url('kelola-quran-siswa');

        if (!$id) {
            Response::withError($returnUrl, 'ID setoran tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $db->query("DELETE FROM quran_siswa_setoran WHERE id = ?", [$id]);

        Response::withSuccess($returnUrl, 'Catatan setoran berhasil dihapus.');
    }

    /**
     * Cetak Rekapitulasi Mutaba'ah (GET)
     */
    public static function cetakRekap(): void
    {
        $jenjang = trim($_GET['jenjang'] ?? 'SD');
        $filterKelas = trim($_GET['kelas'] ?? '');
        $db = Database::getInstance();

        $where = "qs.jenjang = ?";
        $params = [$jenjang];

        if ($filterKelas !== '') {
            $where .= " AND s.kelas = ?";
            $params[] = $filterKelas;
        }

        $setoranList = $db->findAll("
            SELECT qs.*, s.nama_lengkap, s.kelas
            FROM quran_siswa_setoran qs
            JOIN siswa s ON qs.siswa_id = s.id
            WHERE $where
            ORDER BY qs.tanggal DESC, s.nama_lengkap ASC
        ", $params);

        include MODULES_PATH . '/kelola-quran-siswa/views/cetak_rekap.php';
    }

    /**
     * Unduh Data Rekap Setoran CSV / Excel (GET)
     */
    public static function exportExcel(): void
    {
        $jenjang = trim($_GET['jenjang'] ?? 'SD');
        $filterKelas = trim($_GET['kelas'] ?? '');
        $db = Database::getInstance();

        $where = "qs.jenjang = ?";
        $params = [$jenjang];

        if ($filterKelas !== '') {
            $where .= " AND s.kelas = ?";
            $params[] = $filterKelas;
        }

        $rows = $db->findAll("
            SELECT qs.tanggal, s.nisn, s.nama_lengkap, s.kelas, qs.jenis_setoran,
                   qs.iqro_jilid, qs.iqro_halaman, qs.surah_nama, qs.ayat_awal, qs.ayat_akhir, qs.juz,
                   qs.nilai_kelancaran, qs.nilai_tajwid, qs.nilai_makhorij, qs.status_lulus, qs.catatan
            FROM quran_siswa_setoran qs
            JOIN siswa s ON qs.siswa_id = s.id
            WHERE $where
            ORDER BY qs.tanggal DESC
        ", $params);

        $filename = 'rekap_quran_' . strtolower($jenjang) . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Tanggal', 'NISN', 'Nama Siswa', 'Kelas', 'Jenis Setoran', 'Materi', 'Juz', 'Kelancaran', 'Tajwid', 'Makhorij', 'Status', 'Catatan']);

        foreach ($rows as $r) {
            $materi = $r['jenis_setoran'] === 'iqro' 
                ? "Iqro Jilid {$r['iqro_jilid']} (Hal {$r['iqro_halaman']})"
                : "{$r['surah_nama']} : {$r['ayat_awal']}-{$r['ayat_akhir']}";

            fputcsv($output, [
                $r['tanggal'],
                $r['nisn'],
                $r['nama_lengkap'],
                $r['kelas'],
                $r['jenis_setoran'],
                $materi,
                $r['juz'],
                $r['nilai_kelancaran'],
                $r['nilai_tajwid'],
                $r['nilai_makhorij'],
                $r['status_lulus'],
                $r['catatan']
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * Helper: Hitung jumlah siswa per jenjang
     */
    private static function getJenjangStudentCounts(): array
    {
        $db = Database::getInstance();
        return [
            'PAUD' => (int)$db->query("SELECT COUNT(*) FROM siswa WHERE UPPER(jenjang) IN ('PAUD','TK') AND is_active = 1")->fetchColumn(),
            'SD' => (int)$db->query("SELECT COUNT(*) FROM siswa WHERE (UPPER(jenjang) = 'SD' OR jenjang IS NULL OR jenjang = '') AND is_active = 1")->fetchColumn(),
            'SMP_SMA' => (int)$db->query("SELECT COUNT(*) FROM siswa WHERE UPPER(jenjang) IN ('SMP','SMA') AND is_active = 1")->fetchColumn(),
        ];
    }
}
