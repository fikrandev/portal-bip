<?php
/**
 * JurnalGuruAdminController
 * Modul Kelola Jurnal Guru (Desktop Admin)
 * Monitoring pengisian jurnal KBM dan akumulasi Jam Pelajaran (JP) guru
 */

class JurnalGuruAdminController
{
    /**
     * Halaman Utama Rekap & Monitoring Jurnal KBM
     */
    public static function index(): void
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // 1. Ambil Parameter Filter
        $search    = trim($_GET['search'] ?? '');
        $guruId    = !empty($_GET['guru_id']) ? (int)$_GET['guru_id'] : null;
        $kelas     = trim($_GET['kelas'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate   = trim($_GET['end_date'] ?? '');

        // 2. Bangun Query Filter
        $whereClauses = ['1=1'];
        $params = [];

        if ($search !== '') {
            $whereClauses[] = '(jm.kode_jurnal LIKE ? OR jm.nama_guru LIKE ? OR p.nama LIKE ? OR jm.materi_pokok LIKE ? OR jm.mata_pelajaran LIKE ? OR jm.kelas LIKE ?)';
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($guruId !== null) {
            $whereClauses[] = 'jm.guru_id = ?';
            $params[] = $guruId;
        }

        if ($kelas !== '') {
            $whereClauses[] = 'jm.kelas = ?';
            $params[] = $kelas;
        }

        if ($status !== '' && in_array($status, ['draft', 'selesai', 'tervalidasi'], true)) {
            $whereClauses[] = 'jm.status = ?';
            $params[] = $status;
        }

        if ($startDate !== '') {
            $whereClauses[] = 'jm.tanggal >= ?';
            $params[] = $startDate;
        }

        if ($endDate !== '') {
            $whereClauses[] = 'jm.tanggal <= ?';
            $params[] = $endDate;
        }

        $whereSql = implode(' AND ', $whereClauses);

        // 3. KPI Statistics
        // Total Jurnal Filtered & Total JP
        $stmtKpi = $pdo->prepare("
            SELECT 
                COUNT(*) as total_count,
                COALESCE(SUM(jm.jumlah_jp), 0) as total_jp,
                COUNT(DISTINCT jm.guru_id) as total_guru_aktif
            FROM jurnal_mengajar jm
            LEFT JOIN pegawai p ON jm.guru_id = p.id
            WHERE {$whereSql}
        ");
        $stmtKpi->execute($params);
        $kpiFiltered = $stmtKpi->fetch(PDO::FETCH_ASSOC);

        // KPI Hari Ini (All Teachers)
        $stmtToday = $pdo->query("
            SELECT 
                COUNT(*) as count_today,
                COALESCE(SUM(jumlah_jp), 0) as jp_today,
                COUNT(DISTINCT guru_id) as guru_today
            FROM jurnal_mengajar 
            WHERE tanggal = CURDATE()
        ");
        $kpiToday = $stmtToday->fetch(PDO::FETCH_ASSOC);

        // KPI Bulan Ini (All Teachers)
        $stmtMonth = $pdo->query("
            SELECT 
                COUNT(*) as count_month,
                COALESCE(SUM(jumlah_jp), 0) as jp_month
            FROM jurnal_mengajar 
            WHERE YEAR(tanggal) = YEAR(CURDATE()) AND MONTH(tanggal) = MONTH(CURDATE())
        ");
        $kpiMonth = $stmtMonth->fetch(PDO::FETCH_ASSOC);

        // 4. Pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $totalRows = (int)($kpiFiltered['total_count'] ?? 0);
        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        // 5. Data Jurnal List
        $queryList = "
            SELECT 
                jm.*,
                COALESCE(p.nama, jm.nama_guru, 'Guru') as guru_nama_lengkap,
                p.niy,
                p.nik,
                p.foto as guru_foto,
                jp.jam_mulai,
                jp.jam_selesai,
                jp.ruangan
            FROM jurnal_mengajar jm
            LEFT JOIN pegawai p ON jm.guru_id = p.id
            LEFT JOIN jadwal_pelajaran jp ON jm.jadwal_id = jp.id
            WHERE {$whereSql}
            ORDER BY jm.tanggal DESC, jm.jam_ke ASC, jm.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmtList = $pdo->prepare($queryList);
        $stmtList->execute($params);
        $jurnalList = $stmtList->fetchAll(PDO::FETCH_ASSOC);

        // 6. Data Dropdown Filter
        $guruOptions = $pdo->query("
            SELECT id, nama, niy, nik 
            FROM pegawai 
            WHERE status_pegawai != 'Keluar' OR status_pegawai IS NULL
            ORDER BY nama ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $kelasOptions = $pdo->query("
            SELECT DISTINCT nama_kelas 
            FROM kelas 
            ORDER BY nama_kelas ASC
        ")->fetchAll(PDO::FETCH_COLUMN);

        if (empty($kelasOptions)) {
            $kelasOptions = $pdo->query("
                SELECT DISTINCT kelas 
                FROM jurnal_mengajar 
                WHERE kelas IS NOT NULL AND kelas != ''
                ORDER BY kelas ASC
            ")->fetchAll(PDO::FETCH_COLUMN);
        }

        $pageTitle = 'Kelola Jurnal Guru & Jam Pelajaran (JP)';
        $breadcrumbs = [
            ['label' => 'Administrasi Guru', 'url' => url('kelola-perangkat-pembelajaran')],
            ['label' => 'Kelola Jurnal Guru']
        ];

        ob_start();
        include MODULES_PATH . '/kelola-jurnal-guru/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Detail Jurnal via JSON (untuk Modal Popup)
     */
    public static function apiDetail(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $stmt = $pdo->prepare("
                SELECT 
                    jm.*,
                    COALESCE(p.nama, jm.nama_guru, 'Guru') as guru_nama_lengkap,
                    p.niy,
                    p.nik,
                    p.email as guru_email,
                    p.foto as guru_foto,
                    jp.jam_mulai,
                    jp.jam_selesai,
                    jp.ruangan,
                    jg.nama_grup as jadwal_grup_nama
                FROM jurnal_mengajar jm
                LEFT JOIN pegawai p ON jm.guru_id = p.id
                LEFT JOIN jadwal_pelajaran jp ON jm.jadwal_id = jp.id
                LEFT JOIN jadwal_grup jg ON jp.grup_id = jg.id
                WHERE jm.id = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Data jurnal tidak ditemukan']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $item
            ]);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Update Status Jurnal (Validasi / Batal Validasi)
     */
    public static function updateStatus(int $id): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-guru'), 'Token keamanan tidak valid', 'error');
            return;
        }

        $newStatus = trim($_POST['status'] ?? '');
        if (!in_array($newStatus, ['draft', 'selesai', 'tervalidasi'], true)) {
            Response::redirect(url('kelola-jurnal-guru'), 'Status tidak valid', 'error');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare("UPDATE jurnal_mengajar SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        $statusLabel = [
            'tervalidasi' => 'divalidasi',
            'selesai' => 'diselesaikan',
            'draft' => 'dijadikan draft'
        ][$newStatus] ?? 'diperbarui';

        Response::redirect(url('kelola-jurnal-guru'), "Status jurnal berhasil {$statusLabel}!", 'success');
    }

    /**
     * Hapus Jurnal
     */
    public static function delete(int $id): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-guru'), 'Token keamanan tidak valid', 'error');
            return;
        }

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        // Cari data foto jika ada
        $stmtFind = $pdo->prepare("SELECT foto_kegiatan FROM jurnal_mengajar WHERE id = ?");
        $stmtFind->execute([$id]);
        $row = $stmtFind->fetch(PDO::FETCH_ASSOC);

        if ($row && !empty($row['foto_kegiatan'])) {
            $filePath = BASE_PATH . '/public/' . ltrim($row['foto_kegiatan'], '/');
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        $stmtDel = $pdo->prepare("DELETE FROM jurnal_mengajar WHERE id = ?");
        $stmtDel->execute([$id]);

        Response::redirect(url('kelola-jurnal-guru'), 'Jurnal KBM berhasil dihapus.', 'success');
    }

    /**
     * Cetak Laporan Rekapitulasi Jurnal & Jam Mengajar (JP)
     */
    public static function cetak(): void
    {
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $search    = trim($_GET['search'] ?? '');
        $guruId    = !empty($_GET['guru_id']) ? (int)$_GET['guru_id'] : null;
        $kelas     = trim($_GET['kelas'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $startDate = trim($_GET['start_date'] ?? date('Y-m-01'));
        $endDate   = trim($_GET['end_date'] ?? date('Y-m-t'));

        $whereClauses = ['1=1'];
        $params = [];

        if ($search !== '') {
            $whereClauses[] = '(jm.kode_jurnal LIKE ? OR jm.nama_guru LIKE ? OR p.nama LIKE ? OR jm.materi_pokok LIKE ? OR jm.mata_pelajaran LIKE ? OR jm.kelas LIKE ?)';
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($guruId !== null) {
            $whereClauses[] = 'jm.guru_id = ?';
            $params[] = $guruId;
        }

        if ($kelas !== '') {
            $whereClauses[] = 'jm.kelas = ?';
            $params[] = $kelas;
        }

        if ($status !== '' && in_array($status, ['draft', 'selesai', 'tervalidasi'], true)) {
            $whereClauses[] = 'jm.status = ?';
            $params[] = $status;
        }

        if ($startDate !== '') {
            $whereClauses[] = 'jm.tanggal >= ?';
            $params[] = $startDate;
        }

        if ($endDate !== '') {
            $whereClauses[] = 'jm.tanggal <= ?';
            $params[] = $endDate;
        }

        $whereSql = implode(' AND ', $whereClauses);

        // Fetch Jurnal
        $stmt = $pdo->prepare("
            SELECT 
                jm.*,
                COALESCE(p.nama, jm.nama_guru, 'Guru') as guru_nama_lengkap,
                p.niy,
                p.nik,
                jp.jam_mulai,
                jp.jam_selesai,
                jp.ruangan
            FROM jurnal_mengajar jm
            LEFT JOIN pegawai p ON jm.guru_id = p.id
            LEFT JOIN jadwal_pelajaran jp ON jm.jadwal_id = jp.id
            WHERE {$whereSql}
            ORDER BY jm.tanggal ASC, jm.jam_ke ASC, jm.id ASC
        ");
        $stmt->execute($params);
        $jurnalList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Rekap per Guru
        $rekapPerGuru = [];
        $totalJP = 0;
        foreach ($jurnalList as $item) {
            $gId = $item['guru_id'] ?? 0;
            $gName = $item['guru_nama_lengkap'] ?? 'Guru';
            if (!isset($rekapPerGuru[$gId])) {
                $rekapPerGuru[$gId] = [
                    'nama' => $gName,
                    'niy' => $item['niy'] ?? ($item['nik'] ?? '-'),
                    'total_pertemuan' => 0,
                    'total_jp' => 0
                ];
            }
            $rekapPerGuru[$gId]['total_pertemuan']++;
            $rekapPerGuru[$gId]['total_jp'] += (int)($item['jumlah_jp'] ?? 2);
            $totalJP += (int)($item['jumlah_jp'] ?? 2);
        }

        // Info Guru terpilih jika filter single teacher
        $selectedGuru = null;
        if ($guruId !== null) {
            $stmtG = $pdo->prepare("SELECT * FROM pegawai WHERE id = ?");
            $stmtG->execute([$guruId]);
            $selectedGuru = $stmtG->fetch(PDO::FETCH_ASSOC);
        }

        include MODULES_PATH . '/kelola-jurnal-guru/views/cetak.php';
    }
}

