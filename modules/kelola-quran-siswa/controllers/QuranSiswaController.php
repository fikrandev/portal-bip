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
     * Portal Qur'an Siswa PAUD / TK - /kelola-quran-siswa-paud
     */
    public static function paudIndex(): void
    {
        $pageTitle = "Qur'an Siswa PAUD / TK";
        $breadcrumbs = [
            ['label' => "Qur'an Siswa", 'url' => url('kelola-quran-siswa')],
            ['label' => "Siswa PAUD"]
        ];
        $activeJenjang = 'PAUD';

        $db = Database::getInstance();
        $counts = self::getJenjangStudentCounts();

        // Ambil santri PAUD beserta capaian terakhir
        $students = $db->findAll("
            SELECT s.id, s.nama_lengkap, s.nis, s.nisn, s.kelas, s.jenjang,
                   (SELECT COUNT(*) FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'PAUD') as setoran_count,
                   (SELECT CASE 
                               WHEN qs.jenis_setoran = 'iqro' THEN CONCAT('Iqro Jilid ', qs.iqro_jilid, ' (Hal. ', COALESCE(qs.iqro_halaman, '-'), ')')
                               ELSE qs.surah_nama 
                           END 
                    FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'PAUD' ORDER BY qs.tanggal DESC, qs.id DESC LIMIT 1) as latest_materi,
                   (SELECT qs.tanggal FROM quran_siswa_setoran qs WHERE qs.siswa_id = s.id AND qs.jenjang = 'PAUD' ORDER BY qs.tanggal DESC, qs.id DESC LIMIT 1) as latest_tanggal
            FROM siswa s
            WHERE UPPER(s.jenjang) IN ('PAUD','TK') AND s.is_active = 1
            ORDER BY s.nama_lengkap ASC
        ");

        // Riwayat Setoran PAUD
        $setoranList = $db->findAll("
            SELECT qs.*, s.nama_lengkap, s.kelas
            FROM quran_siswa_setoran qs
            JOIN siswa s ON qs.siswa_id = s.id
            WHERE qs.jenjang = 'PAUD'
            ORDER BY qs.tanggal DESC, qs.id DESC
            LIMIT 30
        ");

        // Stats PAUD
        $stats = [
            'total_siswa' => $counts['PAUD'],
            'total_iqro' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'PAUD' AND jenis_setoran = 'iqro'")->fetchColumn(),
            'total_surah' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'PAUD' AND jenis_setoran != 'iqro'")->fetchColumn(),
            'total_mutqin' => (int)$db->query("SELECT COUNT(*) FROM quran_siswa_setoran WHERE jenjang = 'PAUD' AND status_lulus = 'mutqin'")->fetchColumn(),
        ];

        // All active students for modal form
        $allStudents = $db->findAll("
            SELECT id, nama_lengkap, kelas, 'PAUD' as jenjang
            FROM siswa 
            WHERE UPPER(jenjang) IN ('PAUD','TK') AND is_active = 1 
            ORDER BY nama_lengkap ASC
        ");

        ob_start();
        include MODULES_PATH . '/kelola-quran-siswa/views/paud/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
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
