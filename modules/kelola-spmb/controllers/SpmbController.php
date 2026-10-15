<?php
/**
 * SpmbController - Controller Utama Modul Kelola SPMB
 * Portal BIP
 *
 * Menu Sidebar:
 * 1. Dashboard
 * 2. Siswa Baru
 * 3. Pindahan
 * 4. Pengaturan Tahun Group SPMB
 */

require_once BASE_PATH . '/modules/kelola-spmb/models/SpmbModel.php';

class SpmbController
{
    /**
     * Helper render view dengan layout standard dan custom sidebar SPMB
     */
    private static function view(string $viewPath, array $data = [], string $pageTitle = 'Kelola SPMB', array $breadcrumbs = []): void
    {
        extract($data);
        $customSidebar = BASE_PATH . '/modules/kelola-spmb/views/sidebar.php';

        ob_start();
        include BASE_PATH . '/modules/kelola-spmb/views/' . $viewPath . '.php';
        $content = ob_get_clean();

        include TEMPLATES_PATH . '/layouts/app.php';
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. DASHBOARD SPMB
    // ═════════════════════════════════════════════════════════════════════════

    public static function index(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;
        $stats = SpmbModel::getDashboardStats($groupId);

        self::view(
            'dashboard',
            [
                'activeGroup' => $activeGroup,
                'stats' => $stats
            ],
            'Dashboard SPMB',
            [['label' => 'Kelola SPMB', 'url' => url('kelola-spmb')], ['label' => 'Dashboard']]
        );
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 2. SISWA BARU
    // ═════════════════════════════════════════════════════════════════════════

    public static function siswaBaruIndex(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;
        $groups = SpmbModel::getAllGroups();
        $autoNomor = SpmbModel::generateNomorPendaftaran('Siswa Baru', $activeGroup['tahun_ajaran'] ?? null, 'PAUD');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'jenis_pendaftaran' => 'Siswa Baru',
            'group_id' => !empty($_GET['group_id']) ? (int)$_GET['group_id'] : $groupId,
            'jenjang' => $_GET['jenjang'] ?? '',
            'kategori_pendaftar' => $_GET['kategori_pendaftar'] ?? '',
            'status_kelulusan' => $_GET['status_kelulusan'] ?? '',
            'status_pembayaran' => $_GET['status_pembayaran'] ?? '',
            'status_berkas' => $_GET['status_berkas'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $totalRows = SpmbModel::countPendaftar($filters);
        $pendaftarList = SpmbModel::getPendaftarList($filters, $perPage, $offset);
        $totalPages = ceil($totalRows / $perPage);

        self::view(
            'siswa_baru/index',
            [
                'activeGroup'   => $activeGroup,
                'groups'        => $groups,
                'autoNomor'     => $autoNomor,
                'pendaftarList' => $pendaftarList,
                'filters'       => $filters,
                'currentPage'   => $page,
                'totalPages'    => $totalPages,
                'perPage'       => $perPage,
                'totalRows'     => $totalRows
            ],
            'Data Calon Siswa Baru SPMB',
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Siswa Baru']]
        );
    }

    public static function getNomorAjax(): void
    {
        $jenjang = $_GET['jenjang'] ?? 'PAUD';
        $activeGroup = SpmbModel::getActiveGroup();
        $nomor = SpmbModel::generateNomorPendaftaran('Siswa Baru', $activeGroup['tahun_ajaran'] ?? null, $jenjang);
        Response::json(['success' => true, 'nomor' => $nomor]);
    }

    public static function siswaBaruCreate(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groups = SpmbModel::getAllGroups();
        $autoNomor = SpmbModel::generateNomorPendaftaran('Siswa Baru', $activeGroup['tahun_ajaran'] ?? null, 'PAUD');

        self::view(
            'siswa_baru/create',
            [
                'activeGroup' => $activeGroup,
                'groups'      => $groups,
                'autoNomor'   => $autoNomor
            ],
            'Pendaftaran Calon Siswa Baru',
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Siswa Baru', 'url' => url('kelola-spmb/siswa-baru')], ['label' => 'Tambah']]
        );
    }

    public static function siswaBaruStore(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['is_ajax']) || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if (!CSRF::validate(false)) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Sesi telah kedaluwarsa atau token CSRF tidak valid. Silakan muat ulang halaman.'], 403);
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Sesi telah kedaluwarsa atau token tidak valid.', 'error');
        }

        $data = $_POST;
        $data['jenis_pendaftaran'] = 'Siswa Baru';
        $data['created_by'] = $_SESSION['user']['id'] ?? null;

        // Handle upload foto
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $targetDir = BASE_PATH . '/public/uploads/spmb';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $fileName = 'foto_' . time() . '_' . uniqid() . '.' . $ext;
                $targetFile = $targetDir . '/' . $fileName;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                    $data['foto'] = 'uploads/spmb/' . $fileName;
                }
            }
        }

        try {
            $id = SpmbModel::createPendaftar($data);
            $pendaftar = SpmbModel::getPendaftarById($id);

            // Kirim notifikasi WA otomatis jika diaktifkan di pengaturan
            $opts = SpmbModel::getSpmbOptions();
            if (!empty($opts['wa_notif_enabled'])) {
                try {
                    SpmbModel::sendWaBiodataNotification($id);
                } catch (Throwable $e) {
                    error_log("Gagal kirim notif WA SPMB: " . $e->getMessage());
                }
            }

            if ($isAjax) {
                Response::json([
                    'success'   => true,
                    'message'   => 'Calon siswa baru berhasil didaftarkan!',
                    'id'        => $id,
                    'nomor'     => $pendaftar['nomor_pendaftaran'] ?? ($data['nomor_pendaftaran'] ?? ''),
                    'nama'      => $pendaftar['nama_lengkap'] ?? ($data['nama_lengkap'] ?? ''),
                    'cetak_url' => url('kelola-spmb/siswa-baru/cetak/' . $id . '?autoprint=1'),
                    'redirect'  => url('kelola-spmb/siswa-baru')
                ]);
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Calon siswa baru berhasil didaftarkan!', 'success');
        } catch (Throwable $e) {
            if ($isAjax) {
                Response::json([
                    'success' => false,
                    'message' => 'Gagal mendaftarkan calon siswa: ' . $e->getMessage()
                ], 400);
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Gagal mendaftarkan siswa: ' . $e->getMessage(), 'error');
        }
    }

    public static function siswaBaruEdit(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Data calon siswa tidak ditemukan.', 'error');
        }

        $groups = SpmbModel::getAllGroups();

        self::view(
            'siswa_baru/edit',
            [
                'pendaftar' => $pendaftar,
                'groups'    => $groups
            ],
            'Edit Siswa Baru - ' . $pendaftar['nama_lengkap'],
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Siswa Baru', 'url' => url('kelola-spmb/siswa-baru')], ['label' => 'Edit']]
        );
    }

    public static function siswaBaruUpdate(int $id): void
    {
        CSRF::validate();

        $data = $_POST;

        // Handle upload foto baru jika ada
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $targetDir = BASE_PATH . '/public/uploads/spmb';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $fileName = 'foto_' . time() . '_' . uniqid() . '.' . $ext;
                $targetFile = $targetDir . '/' . $fileName;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFile)) {
                    $data['foto'] = 'uploads/spmb/' . $fileName;
                }
            }
        }

        try {
            SpmbModel::updatePendaftar($id, $data);
            Response::redirect(url('kelola-spmb/siswa-baru/detail/' . $id), 'Data calon siswa baru berhasil diperbarui.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/siswa-baru/edit/' . $id), 'Gagal memperbarui data: ' . $e->getMessage(), 'error');
        }
    }

    public static function siswaBaruDetail(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Data calon siswa tidak ditemukan.', 'error');
        }

        self::view(
            'siswa_baru/detail',
            ['pendaftar' => $pendaftar],
            'Biodata - ' . $pendaftar['nama_lengkap'],
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Siswa Baru', 'url' => url('kelola-spmb/siswa-baru')], ['label' => 'Detail']]
        );
    }

    public static function siswaBaruDelete(int $id): void
    {
        try {
            SpmbModel::deletePendaftar($id);
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Data pendaftar berhasil dihapus.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Gagal menghapus data: ' . $e->getMessage(), 'error');
        }
    }

    public static function siswaBaruStatus(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
        if (!CSRF::validate(false)) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Token CSRF tidak valid.', 'error');
        }
        $status = $_POST['status_kelulusan'] ?? 'Menunggu Seleksi';
        SpmbModel::updateStatusPendaftar($id, $status);
        if ($isAjax) {
            Response::json(['success' => true, 'message' => "Status seleksi berhasil diubah menjadi: $status", 'status' => $status]);
        }
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? url('kelola-spmb/siswa-baru');
        Response::redirect($redirectUrl, "Status seleksi berhasil diubah menjadi: $status", 'success');
    }

    /**
     * Update Massal Status Kelulusan Siswa Baru (Terima Massal)
     */
    public static function siswaBaruBulkStatus(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
        if (!CSRF::validate(false)) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Token CSRF tidak valid.', 'error');
        }

        $mode = $_POST['mode'] ?? 'lengkap';
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;

        try {
            if ($mode === 'selected') {
                $ids = $_POST['ids'] ?? [];
                if (empty($ids) || !is_array($ids)) {
                    Response::json(['success' => false, 'message' => 'Tidak ada calon siswa yang dipilih.'], 400);
                }
                $count = SpmbModel::bulkUpdateStatus($ids, 'Lulus / Diterima');
                $msg = "Sebanyak $count calon siswa terpilih berhasil ditetapkan statusnya menjadi: Lulus / Diterima!";
            } else {
                $count = SpmbModel::bulkAcceptBerkasLengkap($groupId, null, 'Siswa Baru');
                $msg = "Sebanyak $count calon siswa baru yang berkasnya lengkap berhasil ditetapkan statusnya menjadi: Lulus / Diterima!";
            }

            if ($isAjax) {
                Response::json(['success' => true, 'message' => $msg, 'count' => $count]);
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), $msg, 'success');
        } catch (Throwable $e) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()], 500);
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Gagal: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Cetak Dokumen Pengumuman Hasil Kelulusan SPMB Resmi (Format PDF)
     */
    public static function pengumumanKelulusanCetak(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        include BASE_PATH . '/modules/kelola-spmb/views/pengumuman/cetak_kelulusan.php';
        exit;
    }

    /**
     * Kirim Notifikasi WhatsApp Kelulusan Personal (Japri) ke Orang Tua Siswa
     */
    public static function kirimWaKelulusan(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['is_ajax']);
        if (!CSRF::validate(false)) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            }
            Response::redirect(url('kelola-spmb/siswa-baru/detail/' . $id), 'Token CSRF tidak valid.', 'error');
        }

        $res = SpmbModel::sendWaKelulusanNotification($id);

        if ($isAjax) {
            Response::json($res);
        }

        if (!empty($res['success'])) {
            $_SESSION['flash_success'] = 'Pesan WhatsApp pengumuman kelulusan berhasil dikirim ke orang tua!';
        } else {
            $_SESSION['flash_error'] = 'Gagal mengirim WhatsApp: ' . ($res['message'] ?? 'Kesalahan gateway.');
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? url('kelola-spmb/siswa-baru/detail/' . $id);
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Kirim Notifikasi WhatsApp Kelulusan Massal (Broadcast Japri)
     */
    public static function kirimWaKelulusanBulk(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
        if (!CSRF::validate(false)) {
            Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
        }

        $activeGroup = SpmbModel::getActiveGroup();
        $jenjang = trim($_POST['jenjang'] ?? '');
        $jenis = trim($_POST['jenis'] ?? '');

        $list = SpmbModel::getDaftarKelulusan([
            'group_id' => $activeGroup['id'] ?? null,
            'jenjang'  => $jenjang,
            'jenis_pendaftaran' => $jenis
        ]);

        if (empty($list)) {
            Response::json(['success' => false, 'message' => 'Tidak ada calon siswa dengan status Lulus / Diterima untuk dikirimi WhatsApp.'], 400);
        }

        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($list as $s) {
            $res = SpmbModel::sendWaKelulusanNotification((int)$s['id']);
            $targetPhone = !empty($s['no_wa']) ? $s['no_wa'] : (!empty($s['no_hp']) ? $s['no_hp'] : '-');
            if (!empty($res['success'])) {
                $successCount++;
                $results[] = [
                    'id'      => $s['id'],
                    'nama'    => $s['nama_lengkap'],
                    'phone'   => $targetPhone,
                    'success' => true,
                    'message' => 'Terkirim'
                ];
            } else {
                $failCount++;
                $results[] = [
                    'id'      => $s['id'],
                    'nama'    => $s['nama_lengkap'],
                    'phone'   => $targetPhone,
                    'success' => false,
                    'message' => $res['message'] ?? 'Gagal'
                ];
            }
        }

        Response::json([
            'success'      => true,
            'message'      => "Broadcast selesai! Berhasil: {$successCount}, Gagal: {$failCount}.",
            'successCount' => $successCount,
            'failCount'    => $failCount,
            'results'      => $results
        ]);
    }

    /**
     * Dapatkan Teks Pengumuman Resmi Format WhatsApp untuk Grup Orang Tua
     */
    public static function getTeksPengumumanGrupAjax(): void
    {
        $jenjang = trim($_GET['jenjang'] ?? 'SD');
        $jenis = trim($_GET['jenis'] ?? 'Siswa Baru');
        $activeGroup = SpmbModel::getActiveGroup();

        $text = SpmbModel::generateTeksPengumumanGrup($jenjang, $activeGroup['id'] ?? null, $jenis);
        Response::json([
            'success' => true,
            'jenjang' => $jenjang,
            'text'    => $text
        ]);
    }

    public static function siswaBaruCetak(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            exit('Data pendaftar tidak ditemukan.');
        }

        include BASE_PATH . '/modules/kelola-spmb/views/siswa_baru/cetak.php';
        exit;
    }

    public static function siswaBaruExport(): void
    {
        $filters = [
            'jenis_pendaftaran' => 'Siswa Baru',
            'jenjang' => $_GET['jenjang'] ?? '',
            'kategori_pendaftar' => $_GET['kategori_pendaftar'] ?? '',
            'status_kelulusan' => $_GET['status_kelulusan'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        $data = SpmbModel::getPendaftarList($filters, 5000, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Siswa_Baru_SPMB_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['No', 'No. Pendaftaran', 'Nama Lengkap', 'Jenis Kelamin', 'Jenjang', 'Kategori Pendaftar', 'NISN', 'Asal Sekolah', 'Nama Ayah', 'Nama Ibu', 'No HP', 'Status Bayar', 'Tgl Daftar']);

        $no = 1;
        foreach ($data as $r) {
            fputcsv($out, [
                $no++,
                $r['nomor_pendaftaran'],
                $r['nama_lengkap'],
                $r['jenis_kelamin'],
                $r['jenjang'],
                $r['kategori_pendaftar'] ?: ($r['jalur_pendaftaran'] ?: 'Umum (Luar Bina Insan)'),
                $r['nisn'],
                $r['asal_sekolah'],
                $r['nama_ayah'],
                $r['nama_ibu'],
                $r['no_hp'],
                $r['status_pembayaran'],
                $r['tanggal_daftar']
            ]);
        }
        fclose($out);
        exit;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 3. SISWA PINDAHAN
    // ═════════════════════════════════════════════════════════════════════════

    public static function pindahanIndex(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;
        $groups = SpmbModel::getAllGroups();
        $autoNomor = SpmbModel::generateNomorPendaftaran('Pindahan', $activeGroup['tahun_ajaran'] ?? null, 'PAUD');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'jenis_pendaftaran' => 'Pindahan',
            'group_id' => !empty($_GET['group_id']) ? (int)$_GET['group_id'] : $groupId,
            'jenjang' => $_GET['jenjang'] ?? '',
            'kategori_pendaftar' => $_GET['kategori_pendaftar'] ?? '',
            'status_kelulusan' => $_GET['status_kelulusan'] ?? '',
            'status_pembayaran' => $_GET['status_pembayaran'] ?? '',
            'status_berkas' => $_GET['status_berkas'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $totalRows = SpmbModel::countPendaftar($filters);
        $pendaftarList = SpmbModel::getPendaftarList($filters, $perPage, $offset);
        $totalPages = ceil($totalRows / $perPage);

        self::view(
            'pindahan/index',
            [
                'activeGroup'   => $activeGroup,
                'groups'        => $groups,
                'autoNomor'     => $autoNomor,
                'pendaftarList' => $pendaftarList,
                'filters'       => $filters,
                'currentPage'   => $page,
                'totalPages'    => $totalPages,
                'perPage'       => $perPage,
                'totalRows'     => $totalRows
            ],
            'Data Siswa Pindahan SPMB',
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Pindahan']]
        );
    }

    public static function pindahanGetNomorAjax(): void
    {
        $jenjang = $_GET['jenjang'] ?? 'PAUD';
        $activeGroup = SpmbModel::getActiveGroup();
        $nomor = SpmbModel::generateNomorPendaftaran('Pindahan', $activeGroup['tahun_ajaran'] ?? null, $jenjang);
        Response::json(['success' => true, 'nomor' => $nomor]);
    }

    public static function pindahanCreate(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groups = SpmbModel::getAllGroups();
        $autoNomor = SpmbModel::generateNomorPendaftaran('Pindahan', $activeGroup['tahun_ajaran'] ?? null);

        self::view(
            'pindahan/create',
            [
                'activeGroup' => $activeGroup,
                'groups'      => $groups,
                'autoNomor'   => $autoNomor
            ],
            'Pendaftaran Siswa Pindahan',
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Pindahan', 'url' => url('kelola-spmb/pindahan')], ['label' => 'Tambah']]
        );
    }

    public static function pindahanStore(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['is_ajax']) || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if (!CSRF::validate(false)) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Sesi telah kedaluwarsa atau token CSRF tidak valid. Silakan muat ulang halaman.'], 403);
            }
            Response::redirect(url('kelola-spmb/pindahan'), 'Sesi telah kedaluwarsa atau token tidak valid.', 'error');
        }

        $data = $_POST;
        $data['jenis_pendaftaran'] = 'Pindahan';
        $data['created_by'] = $_SESSION['user']['id'] ?? null;

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $targetDir = BASE_PATH . '/public/uploads/spmb';
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                $fileName = 'foto_pindah_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . '/' . $fileName)) {
                    $data['foto'] = 'uploads/spmb/' . $fileName;
                }
            }
        }

        try {
            $id = SpmbModel::createPendaftar($data);
            $pendaftar = SpmbModel::getPendaftarById($id);

            // Kirim notifikasi WA otomatis jika diaktifkan di pengaturan
            $opts = SpmbModel::getSpmbOptions();
            if (!empty($opts['wa_notif_enabled'])) {
                try {
                    SpmbModel::sendWaBiodataNotification($id);
                } catch (Throwable $e) {
                    error_log("Gagal kirim notif WA SPMB Pindahan: " . $e->getMessage());
                }
            }

            if ($isAjax) {
                Response::json([
                    'success'   => true,
                    'message'   => 'Data siswa pindahan berhasil didaftarkan!',
                    'id'        => $id,
                    'nomor'     => $pendaftar['nomor_pendaftaran'] ?? ($data['nomor_pendaftaran'] ?? ''),
                    'nama'      => $pendaftar['nama_lengkap'] ?? ($data['nama_lengkap'] ?? ''),
                    'cetak_url' => url('kelola-spmb/pindahan/cetak/' . $id . '?autoprint=1'),
                    'redirect'  => url('kelola-spmb/pindahan')
                ]);
            }

            Response::redirect(url('kelola-spmb/pindahan/detail/' . $id), 'Data siswa pindahan berhasil didaftarkan!', 'success');
        } catch (Throwable $e) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Gagal mendaftarkan siswa: ' . $e->getMessage()], 400);
            }
            Response::redirect(url('kelola-spmb/pindahan/create'), 'Gagal mendaftarkan siswa: ' . $e->getMessage(), 'error');
        }
    }

    public static function pindahanEdit(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            Response::redirect(url('kelola-spmb/pindahan'), 'Data siswa pindahan tidak ditemukan.', 'error');
        }

        $groups = SpmbModel::getAllGroups();

        self::view(
            'pindahan/edit',
            [
                'pendaftar' => $pendaftar,
                'groups'    => $groups
            ],
            'Edit Siswa Pindahan - ' . $pendaftar['nama_lengkap'],
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Pindahan', 'url' => url('kelola-spmb/pindahan')], ['label' => 'Edit']]
        );
    }

    public static function pindahanUpdate(int $id): void
    {
        CSRF::validate();
        $data = $_POST;

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $targetDir = BASE_PATH . '/public/uploads/spmb';
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                $fileName = 'foto_pindah_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . '/' . $fileName)) {
                    $data['foto'] = 'uploads/spmb/' . $fileName;
                }
            }
        }

        try {
            SpmbModel::updatePendaftar($id, $data);
            Response::redirect(url('kelola-spmb/pindahan/detail/' . $id), 'Data siswa pindahan berhasil diperbarui.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/pindahan/edit/' . $id), 'Gagal memperbarui data: ' . $e->getMessage(), 'error');
        }
    }

    public static function pindahanDetail(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            Response::redirect(url('kelola-spmb/pindahan'), 'Data siswa pindahan tidak ditemukan.', 'error');
        }

        self::view(
            'pindahan/detail',
            ['pendaftar' => $pendaftar],
            'Biodata Pindahan - ' . $pendaftar['nama_lengkap'],
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Pindahan', 'url' => url('kelola-spmb/pindahan')], ['label' => 'Detail']]
        );
    }

    public static function pindahanDelete(int $id): void
    {
        try {
            SpmbModel::deletePendaftar($id);
            Response::redirect(url('kelola-spmb/pindahan'), 'Data siswa pindahan berhasil dihapus.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/pindahan'), 'Gagal menghapus data: ' . $e->getMessage(), 'error');
        }
    }

    public static function pindahanStatus(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
        if (!CSRF::validate(false)) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            }
            Response::redirect(url('kelola-spmb/pindahan'), 'Token CSRF tidak valid.', 'error');
        }
        $status = $_POST['status_kelulusan'] ?? 'Menunggu Seleksi';
        SpmbModel::updateStatusPendaftar($id, $status);
        if ($isAjax) {
            Response::json(['success' => true, 'message' => "Status kelulusan berhasil diubah menjadi: $status", 'status' => $status]);
        }
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? url('kelola-spmb/pindahan');
        Response::redirect($redirectUrl, "Status kelulusan berhasil diubah menjadi: $status", 'success');
    }

    /**
     * Update Massal Status Kelulusan Siswa Pindahan (Terima Massal)
     */
    public static function pindahanBulkStatus(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
        if (!CSRF::validate(false)) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            }
            Response::redirect(url('kelola-spmb/pindahan'), 'Token CSRF tidak valid.', 'error');
        }

        $mode = $_POST['mode'] ?? 'lengkap';
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;

        try {
            if ($mode === 'selected') {
                $ids = $_POST['ids'] ?? [];
                if (empty($ids) || !is_array($ids)) {
                    Response::json(['success' => false, 'message' => 'Tidak ada calon siswa yang dipilih.'], 400);
                }
                $count = SpmbModel::bulkUpdateStatus($ids, 'Lulus / Diterima');
                $msg = "Sebanyak $count calon siswa pindahan terpilih berhasil ditetapkan menjadi: Lulus / Diterima!";
            } else {
                $count = SpmbModel::bulkAcceptBerkasLengkap($groupId, null, 'Pindahan');
                $msg = "Sebanyak $count calon siswa pindahan yang berkasnya lengkap berhasil ditetapkan menjadi: Lulus / Diterima!";
            }

            if ($isAjax) {
                Response::json(['success' => true, 'message' => $msg, 'count' => $count]);
            }
            Response::redirect(url('kelola-spmb/pindahan'), $msg, 'success');
        } catch (Throwable $e) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()], 500);
            }
            Response::redirect(url('kelola-spmb/pindahan'), 'Gagal: ' . $e->getMessage(), 'error');
        }
    }

    public static function pindahanCetak(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) exit('Data tidak ditemukan');

        include BASE_PATH . '/modules/kelola-spmb/views/pindahan/cetak.php';
        exit;
    }

    public static function pindahanExport(): void
    {
        $filters = [
            'jenis_pendaftaran' => 'Pindahan',
            'jenjang' => $_GET['jenjang'] ?? '',
            'status_kelulusan' => $_GET['status_kelulusan'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        $data = SpmbModel::getPendaftarList($filters, 5000, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Siswa_Pindahan_SPMB_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['No', 'No. Pendaftaran', 'Nama Lengkap', 'Sekolah Asal', 'NPSN Asal', 'Kelas Tujuan', 'Jenjang', 'No Surat Pindah', 'Alasan Pindah', 'No HP', 'Status Seleksi', 'Tgl Daftar']);

        $no = 1;
        foreach ($data as $r) {
            fputcsv($out, [
                $no++,
                $r['nomor_pendaftaran'],
                $r['nama_lengkap'],
                $r['asal_sekolah'],
                $r['npsn_asal'],
                $r['kelas_tujuan'],
                $r['jenjang'],
                $r['nomor_surat_pindah'],
                $r['alasan_pindah'],
                $r['no_hp'],
                $r['status_kelulusan'],
                $r['tanggal_daftar']
            ]);
        }
        fclose($out);
        exit;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 4. DAFTAR TUNGGU
    // ═════════════════════════════════════════════════════════════════════════

    public static function daftarTungguIndex(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;
        $groups = SpmbModel::getAllGroups();
        $autoNomor = SpmbModel::generateNomorPendaftaran('Daftar Tunggu', $activeGroup['tahun_ajaran'] ?? null, 'PAUD');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $filters = [
            'jenis_pendaftaran' => 'Daftar Tunggu',
            'group_id' => !empty($_GET['group_id']) ? (int)$_GET['group_id'] : $groupId,
            'jenjang' => $_GET['jenjang'] ?? '',
            'kategori_pendaftar' => $_GET['kategori_pendaftar'] ?? '',
            'status_kelulusan' => $_GET['status_kelulusan'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $totalRows = SpmbModel::countPendaftar($filters);
        $pendaftarList = SpmbModel::getPendaftarList($filters, $perPage, $offset);
        $totalPages = ceil($totalRows / $perPage);

        self::view(
            'daftar_tunggu/index',
            [
                'activeGroup'   => $activeGroup,
                'groups'        => $groups,
                'autoNomor'     => $autoNomor,
                'pendaftarList' => $pendaftarList,
                'filters'       => $filters,
                'currentPage'   => $page,
                'totalPages'    => $totalPages,
                'perPage'       => $perPage,
                'totalRows'     => $totalRows
            ],
            'Data Calon Siswa Daftar Tunggu SPMB',
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Daftar Tunggu']]
        );
    }

    public static function daftarTungguGetNomorAjax(): void
    {
        $jenjang = $_GET['jenjang'] ?? 'PAUD';
        $activeGroup = SpmbModel::getActiveGroup();
        $nomor = SpmbModel::generateNomorPendaftaran('Daftar Tunggu', $activeGroup['tahun_ajaran'] ?? null, $jenjang);
        Response::json(['success' => true, 'nomor' => $nomor]);
    }

    public static function daftarTungguCreate(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groups = SpmbModel::getAllGroups();
        $autoNomor = SpmbModel::generateNomorPendaftaran('Daftar Tunggu', $activeGroup['tahun_ajaran'] ?? null, 'PAUD');

        self::view(
            'daftar_tunggu/create',
            [
                'activeGroup' => $activeGroup,
                'groups'      => $groups,
                'autoNomor'   => $autoNomor
            ],
            'Pendaftaran Calon Siswa Daftar Tunggu',
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Daftar Tunggu', 'url' => url('kelola-spmb/daftar-tunggu')], ['label' => 'Tambah']]
        );
    }

    public static function daftarTungguStore(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['is_ajax']) || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        if (!CSRF::validate(false)) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Sesi telah kedaluwarsa atau token CSRF tidak valid. Silakan muat ulang halaman.'], 403);
            }
            Response::redirect(url('kelola-spmb/daftar-tunggu'), 'Sesi telah kedaluwarsa atau token tidak valid.', 'error');
        }

        $data = $_POST;
        $data['jenis_pendaftaran'] = 'Daftar Tunggu';
        $data['created_by'] = $_SESSION['user']['id'] ?? null;

        // Default kategori_pendaftar jika kosong
        if (empty($data['kategori_pendaftar'])) {
            $data['kategori_pendaftar'] = 'Yayasan';
        }

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $targetDir = BASE_PATH . '/public/uploads/spmb';
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                $fileName = 'foto_dt_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . '/' . $fileName)) {
                    $data['foto'] = 'uploads/spmb/' . $fileName;
                }
            }
        }

        try {
            $id = SpmbModel::createPendaftar($data);
            $pendaftar = SpmbModel::getPendaftarById($id);

            // Kirim notifikasi WA otomatis jika diaktifkan di pengaturan
            $opts = SpmbModel::getSpmbOptions();
            if (!empty($opts['wa_notif_enabled'])) {
                try {
                    SpmbModel::sendWaBiodataNotification($id);
                } catch (Throwable $e) {
                    error_log("Gagal kirim notif WA SPMB Daftar Tunggu: " . $e->getMessage());
                }
            }

            if ($isAjax) {
                Response::json([
                    'success'   => true,
                    'message'   => 'Data calon siswa daftar tunggu berhasil disimpan!',
                    'id'        => $id,
                    'nomor'     => $pendaftar['nomor_pendaftaran'] ?? ($data['nomor_pendaftaran'] ?? ''),
                    'nama'      => $pendaftar['nama_lengkap'] ?? ($data['nama_lengkap'] ?? ''),
                    'cetak_url' => url('kelola-spmb/daftar-tunggu/cetak/' . $id . '?autoprint=1'),
                    'redirect'  => url('kelola-spmb/daftar-tunggu')
                ]);
            }

            Response::redirect(url('kelola-spmb/daftar-tunggu/detail/' . $id), 'Data calon siswa daftar tunggu berhasil disimpan!', 'success');
        } catch (Throwable $e) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Gagal menyimpan calon siswa daftar tunggu: ' . $e->getMessage()], 400);
            }
            Response::redirect(url('kelola-spmb/daftar-tunggu/create'), 'Gagal mendaftar: ' . $e->getMessage(), 'error');
        }
    }

    public static function daftarTungguEdit(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            Response::redirect(url('kelola-spmb/daftar-tunggu'), 'Data tidak ditemukan.', 'error');
        }

        $groups = SpmbModel::getAllGroups();

        self::view(
            'daftar_tunggu/edit',
            [
                'pendaftar' => $pendaftar,
                'groups'    => $groups
            ],
            'Edit Calon Siswa Daftar Tunggu - ' . $pendaftar['nama_lengkap'],
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Daftar Tunggu', 'url' => url('kelola-spmb/daftar-tunggu')], ['label' => 'Edit']]
        );
    }

    public static function daftarTungguUpdate(int $id): void
    {
        CSRF::validate();
        $data = $_POST;

        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $targetDir = BASE_PATH . '/public/uploads/spmb';
                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                $fileName = 'foto_dt_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetDir . '/' . $fileName)) {
                    $data['foto'] = 'uploads/spmb/' . $fileName;
                }
            }
        }

        try {
            SpmbModel::updatePendaftar($id, $data);
            Response::redirect(url('kelola-spmb/daftar-tunggu/detail/' . $id), 'Data calon siswa daftar tunggu berhasil diperbarui.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/daftar-tunggu/edit/' . $id), 'Gagal memperbarui data: ' . $e->getMessage(), 'error');
        }
    }

    public static function daftarTungguDetail(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            Response::redirect(url('kelola-spmb/daftar-tunggu'), 'Data tidak ditemukan.', 'error');
        }

        self::view(
            'daftar_tunggu/detail',
            ['pendaftar' => $pendaftar],
            'Detail Daftar Tunggu - ' . $pendaftar['nama_lengkap'],
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Daftar Tunggu', 'url' => url('kelola-spmb/daftar-tunggu')], ['label' => 'Detail']]
        );
    }

    public static function daftarTungguDelete(int $id): void
    {
        try {
            SpmbModel::deletePendaftar($id);
            Response::redirect(url('kelola-spmb/daftar-tunggu'), 'Data calon siswa daftar tunggu berhasil dihapus.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/daftar-tunggu'), 'Gagal menghapus data: ' . $e->getMessage(), 'error');
        }
    }

    public static function daftarTungguStatus(int $id): void
    {
        CSRF::validate();
        $status = $_POST['status_kelulusan'] ?? 'Cadangan';
        SpmbModel::updateStatusPendaftar($id, $status);
        Response::redirect(url('kelola-spmb/daftar-tunggu/detail/' . $id), "Status berhasil diubah menjadi: $status", 'success');
    }

    public static function daftarTungguCetak(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) exit('Data tidak ditemukan');

        include BASE_PATH . '/modules/kelola-spmb/views/daftar_tunggu/cetak.php';
        exit;
    }

    public static function daftarTungguExport(): void
    {
        $filters = [
            'jenis_pendaftaran' => 'Daftar Tunggu',
            'jenjang' => $_GET['jenjang'] ?? '',
            'kategori_pendaftar' => $_GET['kategori_pendaftar'] ?? '',
            'status_kelulusan' => $_GET['status_kelulusan'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        $data = SpmbModel::getPendaftarList($filters, 5000, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Daftar_Tunggu_SPMB_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['No', 'No. Pendaftaran', 'Nama Lengkap', 'Jenjang', 'Rekomendasi', 'Nama Rekomendasi', 'Asal Sekolah', 'Nama Ayah', 'No HP', 'Status Seleksi', 'Tgl Daftar']);

        $no = 1;
        foreach ($data as $r) {
            fputcsv($out, [
                $no++,
                $r['nomor_pendaftaran'],
                $r['nama_lengkap'],
                $r['jenjang'],
                $r['kategori_pendaftar'],
                $r['nama_rekomendasi'] ?? '-',
                $r['asal_sekolah'],
                $r['nama_ayah'],
                $r['no_hp'],
                $r['status_kelulusan'],
                $r['tanggal_daftar']
            ]);
        }
        fclose($out);
        exit;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 5. PENGATURAN TAHUN GROUP SPMB
    // ═════════════════════════════════════════════════════════════════════════

    public static function pengaturanIndex(): void
    {
        $groups = SpmbModel::getAllGroups();
        $activeGroup = SpmbModel::getActiveGroup();
        $kopGambar = SpmbModel::getSetting('spmb_kop_gambar');
        $kopGambar = SpmbModel::getSetting('spmb_kop_gambar');
        $spmbOptions = SpmbModel::getSpmbOptions();
        $allKopUnits = SpmbModel::getAllKopUnits();

        self::view(
            'pengaturan/index',
            [
                'groups'      => $groups,
                'activeGroup' => $activeGroup,
                'kopGambar'   => $kopGambar,
                'allKopUnits' => $allKopUnits,
                'spmbOptions' => $spmbOptions
            ],
            'Pengaturan SPMB',
            [['label' => 'SPMB', 'url' => url('kelola-spmb')], ['label' => 'Pengaturan']]
        );
    }

    public static function pengaturanUploadKop(): void
    {
        CSRF::validate();

        $unit = strtoupper(trim($_POST['unit'] ?? 'GLOBAL'));

        if (empty($_FILES['kop_gambar']['name']) || $_FILES['kop_gambar']['error'] !== UPLOAD_ERR_OK) {
            Response::redirect(url('kelola-spmb/pengaturan'), 'Pilih file gambar kop surat terlebih dahulu.', 'error');
            return;
        }

        $file = $_FILES['kop_gambar'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            Response::redirect(url('kelola-spmb/pengaturan'), 'Format file tidak didukung. Harap gunakan file gambar JPG, PNG, atau WEBP.', 'error');
            return;
        }

        if ($file['size'] > 4 * 1024 * 1024) {
            Response::redirect(url('kelola-spmb/pengaturan'), 'Ukuran file terlalu besar. Maksimal 4MB.', 'error');
            return;
        }

        $targetDir = BASE_PATH . '/public/uploads/spmb';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $isUnitSpecific = in_array($unit, ['PAUD', 'SD', 'SMP', 'SMA']);
        $oldFile = null;
        if ($isUnitSpecific) {
            $spec1 = SpmbModel::getSetting('spmb_kop_' . strtolower($unit));
            $spec2 = SpmbModel::getSetting('kop_unit_' . $unit);
            $tpl = SpmbModel::getSuratTemplate($unit);
            $spec3 = $tpl['kop_gambar'] ?? null;
            $globalKop = SpmbModel::getSetting('spmb_kop_gambar');
            
            // Pilih file yang benar-benar milik unit dan bukan file global
            if (!empty($spec1) && $spec1 !== $globalKop) $oldFile = $spec1;
            elseif (!empty($spec2) && $spec2 !== $globalKop) $oldFile = $spec2;
            elseif (!empty($spec3) && $spec3 !== $globalKop) $oldFile = $spec3;
        } else {
            $oldFile = SpmbModel::getSetting('spmb_kop_gambar');
        }

        if (!empty($oldFile) && file_exists(BASE_PATH . '/public/' . ltrim($oldFile, '/'))) {
            @unlink(BASE_PATH . '/public/' . ltrim($oldFile, '/'));
        }

        $prefix = $isUnitSpecific ? 'kop_' . strtolower($unit) . '_' : 'kop_spmb_';
        $fileName = $prefix . time() . '_' . uniqid() . '.' . $ext;
        $targetPath = $targetDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $savedPath = 'uploads/spmb/' . $fileName;
            if ($isUnitSpecific) {
                SpmbModel::setKopUnit($unit, $savedPath);
                Response::redirect(url('kelola-spmb/pengaturan?tab_kop=' . strtolower($unit)), "Kop formulir cetak untuk Unit $unit berhasil diunggah dan disimpan!", 'success');
            } else {
                SpmbModel::setSetting('spmb_kop_gambar', $savedPath);
                Response::redirect(url('kelola-spmb/pengaturan?tab_kop=global'), 'Kop gambar utama SPMB berhasil diunggah dan disimpan!', 'success');
            }
        } else {
            Response::redirect(url('kelola-spmb/pengaturan'), 'Gagal mengunggah file ke server. Periksa izin folder uploads.', 'error');
        }
    }

    public static function pengaturanHapusKop(): void
    {
        CSRF::validate();

        $unit = strtoupper(trim($_POST['unit'] ?? 'GLOBAL'));
        $isUnitSpecific = in_array($unit, ['PAUD', 'SD', 'SMP', 'SMA']);

        $oldFile = null;
        if ($isUnitSpecific) {
            $spec1 = SpmbModel::getSetting('spmb_kop_' . strtolower($unit));
            $spec2 = SpmbModel::getSetting('kop_unit_' . $unit);
            $tpl = SpmbModel::getSuratTemplate($unit);
            $spec3 = $tpl['kop_gambar'] ?? null;
            $globalKop = SpmbModel::getSetting('spmb_kop_gambar');

            if (!empty($spec1) && $spec1 !== $globalKop) $oldFile = $spec1;
            elseif (!empty($spec2) && $spec2 !== $globalKop) $oldFile = $spec2;
            elseif (!empty($spec3) && $spec3 !== $globalKop) $oldFile = $spec3;
        } else {
            $oldFile = SpmbModel::getSetting('spmb_kop_gambar');
        }

        if (!empty($oldFile) && file_exists(BASE_PATH . '/public/' . ltrim($oldFile, '/'))) {
            @unlink(BASE_PATH . '/public/' . ltrim($oldFile, '/'));
        }

        if ($isUnitSpecific) {
            SpmbModel::setKopUnit($unit, null);
            Response::redirect(url('kelola-spmb/pengaturan?tab_kop=' . strtolower($unit)), "Kop formulir Unit $unit berhasil dihapus dan kembali ke standar.", 'success');
        } else {
            SpmbModel::setSetting('spmb_kop_gambar', '');
            Response::redirect(url('kelola-spmb/pengaturan?tab_kop=global'), 'Kop gambar utama berhasil dihapus. Dokumen akan kembali menggunakan kop teks bawaan.', 'success');
        }
    }

    public static function pengaturanStore(): void
    {
        CSRF::validate();
        $data = $_POST;

        try {
            SpmbModel::createGroup($data);
            Response::redirect(url('kelola-spmb/pengaturan'), 'Group SPMB baru berhasil dibuat!', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/pengaturan'), 'Gagal membuat group: ' . $e->getMessage(), 'error');
        }
    }

    public static function pengaturanUpdate(int $id): void
    {
        CSRF::validate();
        $data = $_POST;

        try {
            SpmbModel::updateGroup($id, $data);
            Response::redirect(url('kelola-spmb/pengaturan'), 'Data Group SPMB berhasil diperbarui!', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/pengaturan'), 'Gagal memperbarui group: ' . $e->getMessage(), 'error');
        }
    }

    public static function pengaturanSetActive(int $id): void
    {
        try {
            SpmbModel::setActiveGroup($id);
            $group = SpmbModel::getGroupById($id);
            $groupName = $group['nama_group'] ?? 'SPMB';
            Response::redirect(url('kelola-spmb/pengaturan'), "Group \"$groupName\" sekarang telah diaktifkan!", 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/pengaturan'), 'Gagal mengaktifkan group: ' . $e->getMessage(), 'error');
        }
    }

    public static function pengaturanDelete(int $id): void
    {
        try {
            SpmbModel::deleteGroup($id);
            Response::redirect(url('kelola-spmb/pengaturan'), 'Group SPMB berhasil dihapus.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-spmb/pengaturan'), 'Gagal menghapus group: ' . $e->getMessage(), 'error');
        }
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 6. FORMULIR BIODATA LENGKAP PUBLIK (TOKEN-BASED)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Halaman Publik Pengisian Biodata Lengkap Calon Siswa (Tanpa Login / Berbasis Token)
     */
    public static function biodataPublic(string $token): void
    {
        $pendaftar = SpmbModel::getPendaftarByBiodataToken($token);
        if (!$pendaftar) {
            http_response_code(404);
            $pageTitle = 'Tautan Tidak Valid - SPMB SIT Bina Insan Palu';
            include MODULES_PATH . '/kelola-spmb/views/biodata_invalid.php';
            exit;
        }

        $options = SpmbModel::getSpmbOptions();
        $pageTitle = 'Formulir Biodata Lengkap - ' . $pendaftar['nama_lengkap'];

        include MODULES_PATH . '/kelola-spmb/views/biodata_lengkap.php';
        exit;
    }

    /**
     * Simpan Draft Pengisian Biodata (Bisa Dicicil per Tab)
     */
    public static function biodataSaveDraft(string $token): void
    {
        header('Content-Type: application/json');
        $pendaftar = SpmbModel::getPendaftarByBiodataToken($token);
        if (!$pendaftar) {
            echo json_encode(['success' => false, 'message' => 'Tautan pengisian tidak valid.']);
            exit;
        }

        $data = $_POST;

        // Proses upload berkas jika ada file yang dikirim
        $fileFields = [
            'file_screenshot_dapodik' => 'dapodik',
            'berkas_kk'               => 'kk',
            'berkas_akta'             => 'akta',
            'berkas_ktp_ayah'         => 'ktp_ayah',
            'berkas_ktp_ibu'          => 'ktp_ibu'
        ];

        foreach ($fileFields as $fieldName => $prefix) {
            if (!empty($_FILES[$fieldName]['name']) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
                $saved = self::saveCompressedUpload($_FILES[$fieldName], $prefix, 500);
                if ($saved) {
                    $data[$fieldName] = $saved;
                }
            }
        }

        $savedOk = SpmbModel::updateBiodataByToken($token, $data, false);

        if ($savedOk) {
            echo json_encode([
                'success'    => true,
                'message'    => 'Draft berhasil disimpan!',
                'last_saved' => date('H:i:s'),
                'uploaded'   => array_intersect_key($data, $fileFields)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan draft ke database.']);
        }
        exit;
    }

    /**
     * Submit Final Formulir Biodata Lengkap
     */
    public static function biodataSubmit(string $token): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['is_ajax']);
        $pendaftar = SpmbModel::getPendaftarByBiodataToken($token);

        if (!$pendaftar) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tautan pengisian tidak valid.']);
                exit;
            }
            Response::redirect(url('spmb/biodata/' . $token), 'Tautan tidak valid.', 'error');
        }

        $data = $_POST;

        // Validasi persetujuan keamanan data wajib dicentang
        if (empty($data['persetujuan_data'])) {
            $msg = 'Anda wajib membaca dan mencentang persetujuan keamanan data sebelum mengirimkan formulir.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Response::redirect(url('spmb/biodata/' . $token . '?tab=5'), $msg, 'error');
        }

        // Sinkronisasi kota / kabupaten
        if (!empty($data['kabupaten']) && empty($data['kota'])) {
            $data['kota'] = $data['kabupaten'];
        } elseif (!empty($data['kota']) && empty($data['kabupaten'])) {
            $data['kabupaten'] = $data['kota'];
        }

        // Validasi seluruh data teks wajib
        $requiredText = [
            // Tahap 1: Data Siswa
            'nama_lengkap'       => 'Nama Lengkap Calon Siswa (Tahap 1)',
            'jenis_kelamin'      => 'Jenis Kelamin (Tahap 1)',
            'nik'                => 'NIK Calon Siswa (Tahap 1)',
            'no_kk'              => 'No. Kartu Keluarga (Tahap 1)',
            'tempat_lahir'       => 'Tempat Lahir (Tahap 1)',
            'tanggal_lahir'      => 'Tanggal Lahir (Tahap 1)',
            'no_akta_lahir'      => 'No. Registrasi Akta Kelahiran (Tahap 1)',
            'anak_ke'            => 'Anak ke-berapa (Tahap 1)',
            // Tahap 3: Alamat & Tempat Tinggal
            'alamat'             => 'Alamat Jalan Lengkap (Tahap 3)',
            'kelurahan'          => 'Kelurahan / Desa (Tahap 3)',
            'kecamatan'          => 'Kecamatan (Tahap 3)',
            'kota'               => 'Kabupaten / Kota (Tahap 3)',
            'provinsi'           => 'Provinsi (Tahap 3)',
            'tempat_tinggal'     => 'Status Tempat Tinggal (Tahap 3)',
            'moda_transportasi'  => 'Moda Transportasi (Tahap 3)',
            // Tahap 4: Orang Tua & Kontak
            'nama_ibu'           => 'Nama Lengkap Ibu Kandung (Tahap 4)',
            'nik_ibu'            => 'NIK Ibu (Tahap 4)',
            'tahun_lahir_ibu'    => 'Tahun Lahir Ibu (Tahap 4)',
            'pendidikan_ibu'     => 'Pendidikan Ibu (Tahap 4)',
            'pekerjaan_ibu'      => 'Pekerjaan Ibu (Tahap 4)',
            'penghasilan_ibu'    => 'Penghasilan Ibu (Tahap 4)',
            'nama_ayah'          => 'Nama Lengkap Ayah Kandung (Tahap 4)',
            'nik_ayah'           => 'NIK Ayah (Tahap 4)',
            'tahun_lahir_ayah'   => 'Tahun Lahir Ayah (Tahap 4)',
            'pendidikan_ayah'    => 'Pendidikan Ayah (Tahap 4)',
            'pekerjaan_ayah'     => 'Pekerjaan Ayah (Tahap 4)',
            'penghasilan_ayah'   => 'Penghasilan Ayah (Tahap 4)',
            'no_wa'              => 'No. WhatsApp Utama (Tahap 4)',
        ];

        $missingFields = [];
        foreach ($requiredText as $f => $label) {
            if (!isset($data[$f]) || trim((string)$data[$f]) === '') {
                $missingFields[] = $label;
            }
        }

        // Proses upload berkas jika ada file yang dikirim
        $fileFields = [
            'file_screenshot_dapodik' => 'dapodik',
            'berkas_kk'               => 'kk',
            'berkas_akta'             => 'akta',
            'berkas_ktp_ayah'         => 'ktp_ayah',
            'berkas_ktp_ibu'          => 'ktp_ibu'
        ];

        foreach ($fileFields as $fieldName => $prefix) {
            if (!empty($_FILES[$fieldName]['name']) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
                $saved = self::saveCompressedUpload($_FILES[$fieldName], $prefix, 500);
                if ($saved) {
                    $data[$fieldName] = $saved;
                }
            }
        }

        // Validasi 4 berkas wajib (KK, Akta, KTP Ayah, KTP Ibu)
        $requiredDocs = [
            'berkas_kk'       => 'Foto Kartu Keluarga (KK) (Tahap 5)',
            'berkas_akta'     => 'Foto Akta Kelahiran (Tahap 5)',
            'berkas_ktp_ayah' => 'Foto KTP Ayah Kandung (Tahap 5)',
            'berkas_ktp_ibu'  => 'Foto KTP Ibu Kandung (Tahap 5)',
        ];

        foreach ($requiredDocs as $docKey => $docLabel) {
            $hasSavedInDb = !empty($pendaftar[$docKey]);
            $hasNewUpload = !empty($data[$docKey]);
            if (!$hasSavedInDb && !$hasNewUpload) {
                $missingFields[] = $docLabel;
            }
        }

        if (!empty($missingFields)) {
            $firstFew = array_slice($missingFields, 0, 4);
            $msg = 'Formulir belum dapat dikirimkan karena data belum lengkap: ' . implode(', ', $firstFew);
            if (count($missingFields) > 4) {
                $msg .= ' dan ' . (count($missingFields) - 4) . ' data lainnya. Silakan lengkapi terlebih dahulu.';
            }
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $msg,
                    'missing' => $missingFields
                ]);
                exit;
            }
            Response::redirect(url('spmb/biodata/' . $token), $msg, 'error');
        }

        $submitOk = SpmbModel::updateBiodataByToken($token, $data, true);

        if ($submitOk) {
            $redirectUrl = url('spmb/komitmen/' . $token . '?ref=biodata_selesai');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'message'  => 'Biodata lengkap berhasil disimpan! Mengarahkan ke Surat Pernyataan Komitmen Orang Tua...',
                    'redirect' => $redirectUrl
                ]);
                exit;
            }
            header('Location: ' . $redirectUrl);
            exit;
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memproses data.']);
                exit;
            }
            Response::redirect(url('spmb/biodata/' . $token), 'Gagal mengirimkan formulir.', 'error');
        }
    }

    /**
     * Unduh Contoh Screenshot Profil Dapodik
     */
    public static function downloadContohDapodik(): void
    {
        $opts = SpmbModel::getSpmbOptions();
        $filePath = !empty($opts['template_dapodik']) ? BASE_PATH . '/public/' . ltrim($opts['template_dapodik'], '/') : null;

        if ($filePath && file_exists($filePath)) {
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            $mime = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : (in_array($ext, ['png']) ? 'image/png' : 'application/octet-stream');
            header('Content-Type: ' . $mime);
            header('Content-Disposition: inline; filename="Contoh_Screenshot_Dapodik.' . $ext . '"');
            readfile($filePath);
            exit;
        }

        // Jika belum ada file yang diunggah admin, generate gambar petunjuk dinamis
        header('Content-Type: image/png');
        $img = imagecreatetruecolor(800, 450);
        $bg = imagecolorallocate($img, 15, 23, 42); // slate-900
        $teal = imagecolorallocate($img, 16, 185, 129); // emerald-500
        $white = imagecolorallocate($img, 255, 255, 255);
        $gray = imagecolorallocate($img, 148, 163, 184);

        imagefilledrectangle($img, 0, 0, 800, 450, $bg);
        imagerectangle($img, 10, 10, 789, 439, $teal);
        imagestring($img, 5, 220, 120, "CONTOH SCREENSHOT PROFIL DAPODIK", $teal);
        imagestring($img, 4, 180, 170, "Pastikan screenshot menampilkan NISN, NIK,", $white);
        imagestring($img, 4, 180, 195, "Nama Lengkap Siswa, dan Nama Ibu Kandung", $white);
        imagestring($img, 3, 200, 240, "Contoh gambar belum diunggah oleh Admin di Pengaturan SPMB.", $gray);
        imagestring($img, 3, 270, 350, "Portal SPMB SIT Bina Insan Palu", $teal);

        imagepng($img);
        imagedestroy($img);
        exit;
    }

    /**
     * Kirim Ulang Link WA Pengisian Biodata oleh Admin
     */
    public static function kirimWaBiodata(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['is_ajax']);
        if (!CSRF::validate(false)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
                exit;
            }
            Response::redirect(url('kelola-spmb/siswa-baru/detail/' . $id), 'Token CSRF tidak valid.', 'error');
        }

        $res = SpmbModel::sendWaBiodataNotification($id);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($res);
            exit;
        }

        if ($res['success']) {
            $_SESSION['flash_success'] = 'Tautan pengisian biodata berhasil dikirim ke WhatsApp pendaftar!';
        } else {
            $_SESSION['flash_error'] = 'Gagal mengirim WhatsApp: ' . ($res['message'] ?? 'Kesalahan gateway.');
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? url('kelola-spmb/siswa-baru/detail/' . $id);
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Simpan Pengaturan Opsi Dropdown & Notifikasi WA SPMB
     */
    public static function pengaturanSaveOptions(): void
    {
        CSRF::validate();

        $keys = [
            'spmb_batas_pengisian_biodata',
            'spmb_opt_alergi',
            'spmb_opt_tempat_tinggal',
            'spmb_opt_transportasi',
            'spmb_opt_pendidikan',
            'spmb_opt_pekerjaan',
            'spmb_opt_penghasilan',
            'spmb_opt_kebutuhan_khusus',
            'spmb_wa_notif_enabled',
            'spmb_wa_template_biodata'
        ];

        $db = Database::getInstance();
        foreach ($keys as $k) {
            $val = trim($_POST[$k] ?? '');
            if ($k === 'spmb_wa_notif_enabled') {
                $val = !empty($_POST[$k]) ? '1' : '0';
            } elseif ($k === 'spmb_batas_pengisian_biodata') {
                // Konversi format datetime-local (YYYY-MM-DDTHH:mm) ke standar SQL (YYYY-MM-DD HH:mm:ss)
                if (!empty($val)) {
                    $val = str_replace('T', ' ', $val);
                    if (strlen($val) === 16) {
                        $val .= ':00';
                    }
                } else {
                    $val = date('Y-07-31 23:59:59');
                }
            }
            $exists = $db->find("SELECT 1 FROM `settings` WHERE `setting_key` = ?", [$k]);
            if ($exists) {
                $db->query("UPDATE `settings` SET `setting_value` = ? WHERE `setting_key` = ?", [$val, $k]);
            } else {
                $db->query("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)", [$k, $val]);
            }
        }

        $_SESSION['flash_success'] = 'Pengaturan opsi master biodata & template WhatsApp SPMB berhasil disimpan!';
        header('Location: ' . url('kelola-spmb/pengaturan?tab=biodata'));
        exit;
    }

    /**
     * Upload File Contoh Screenshot Profil Dapodik
     */
    public static function pengaturanUploadContohDapodik(): void
    {
        CSRF::validate();

        if (empty($_FILES['contoh_dapodik']['name']) || $_FILES['contoh_dapodik']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Silakan pilih file contoh gambar screenshot Dapodik.';
            header('Location: ' . url('kelola-spmb/pengaturan?tab=biodata'));
            exit;
        }

        $saved = self::saveCompressedUpload($_FILES['contoh_dapodik'], 'contoh_dapodik', 1024);
        if ($saved) {
            $db = Database::getInstance();
            $exists = $db->find("SELECT 1 FROM `settings` WHERE `setting_key` = 'spmb_template_dapodik'");
            if ($exists) {
                $db->query("UPDATE `settings` SET `setting_value` = ? WHERE `setting_key` = 'spmb_template_dapodik'", [$saved]);
            } else {
                $db->query("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES ('spmb_template_dapodik', ?)", [$saved]);
            }
            $_SESSION['flash_success'] = 'Contoh screenshot Dapodik berhasil diunggah!';
        } else {
            $_SESSION['flash_error'] = 'Gagal mengunggah file. Pastikan format JPG, PNG, atau WEBP.';
        }

        header('Location: ' . url('kelola-spmb/pengaturan?tab=biodata'));
        exit;
    }

    /**
     * Helper: Simpan File Berkas Upload Aman & Terkompresi Maksimal 500 KB (GD)
     */
    public static function saveCompressedUpload(array $file, string $prefix, int $maxKB = 500): ?string
    {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($ext, $allowed)) {
            return null;
        }

        $targetDir = BASE_PATH . '/public/uploads/spmb/berkas';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $safeName = $prefix . '_' . time() . '_' . bin2hex(random_bytes(10)) . '.' . ($ext === 'pdf' ? 'pdf' : 'jpg');
        $destPath = $targetDir . '/' . $safeName;

        // Jika file PDF, pindahkan langsung
        if ($ext === 'pdf') {
            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                return 'uploads/spmb/berkas/' . $safeName;
            }
            return null;
        }

        // Jika file gambar: proses kompresi otomatis <= 500 KB menggunakan GD
        $tmp = $file['tmp_name'];
        $raw = file_get_contents($tmp);
        $src = @imagecreatefromstring($raw);

        if (!$src) {
            if (move_uploaded_file($tmp, $destPath)) {
                return 'uploads/spmb/berkas/' . $safeName;
            }
            return null;
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Batasi dimensi maksimal 1800px agar proporsional dan tidak memberatkan memori
        $maxDim = 1800;
        if ($origW > $maxDim || $origH > $maxDim) {
            if ($origW >= $origH) {
                $newW = $maxDim;
                $newH = (int)($origH * ($maxDim / $origW));
            } else {
                $newH = $maxDim;
                $newW = (int)($origW * ($maxDim / $origH));
            }
            $resized = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $resized;
        }

        // Kompresi bertahap hingga file di bawah 500 KB
        $quality = 85;
        imagejpeg($src, $destPath, $quality);
        $fSize = filesize($destPath);

        while ($fSize > ($maxKB * 1024) && $quality > 35) {
            $quality -= 10;
            imagejpeg($src, $destPath, $quality);
            clearstatcache(true, $destPath);
            $fSize = filesize($destPath);
        }

        imagedestroy($src);
        return 'uploads/spmb/berkas/' . $safeName;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 7. PENGATURAN SURAT PERNYATAAN KOMITMEN PER UNIT
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Halaman Pengaturan Surat Pernyataan Komitmen per Unit (PAUD, SD, SMP, SMA)
     */
    public static function suratPernyataanIndex(): void
    {
        $unit = strtoupper(trim($_GET['unit'] ?? 'SD'));
        if (!in_array($unit, ['PAUD', 'SD', 'SMP', 'SMA'])) {
            $unit = 'SD';
        }

        $allTemplates = SpmbModel::getAllSuratTemplates();
        $activeTemplate = $allTemplates[$unit] ?? SpmbModel::getSuratTemplate($unit);
        $globalKop = SpmbModel::getSetting('spmb_kop_gambar');

        self::view(
            'surat_pernyataan/index',
            [
                'unit'           => $unit,
                'activeTemplate' => $activeTemplate,
                'allTemplates'   => $allTemplates,
                'globalKop'      => $globalKop,
            ],
            "Pengaturan Surat Pernyataan Komitmen - Unit $unit",
            [['label' => 'Kelola SPMB', 'url' => url('kelola-spmb')], ['label' => 'Surat Pernyataan']]
        );
    }

    /**
     * Simpan Perubahan Template Surat Pernyataan Komitmen Unit
     */
    public static function suratPernyataanUpdate(): void
    {
        CSRF::validate();
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));
        if (!in_array($jenjang, ['PAUD', 'SD', 'SMP', 'SMA'])) {
            $jenjang = 'SD';
        }

        $data = [
            'judul_surat'   => trim($_POST['judul_surat'] ?? 'PERNYATAAN KOMITMEN ORANG TUA/WALI MURID'),
            'nama_sekolah'  => trim($_POST['nama_sekolah'] ?? ($jenjang . ' Islam Terpadu Bina Insan Palu')),
            'isi_template'  => $_POST['isi_template'] ?? '',
            'catatan_bawah' => trim($_POST['catatan_bawah'] ?? '')
        ];

        try {
            SpmbModel::saveSuratTemplate($jenjang, $data);
            $_SESSION['flash_success'] = "Template Surat Pernyataan Komitmen Unit $jenjang berhasil disimpan!";
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = "Gagal menyimpan template: " . $e->getMessage();
        }

        header('Location: ' . url('kelola-spmb/surat-pernyataan?unit=' . $jenjang));
        exit;
    }

    /**
     * Upload Gambar Banner Kop Khusus Unit (PAUD, SD, SMP, SMA)
     */
    public static function suratPernyataanUploadKop(): void
    {
        CSRF::validate();
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));
        if (!in_array($jenjang, ['PAUD', 'SD', 'SMP', 'SMA'])) {
            $jenjang = 'SD';
        }

        if (empty($_FILES['kop_gambar']['name']) || $_FILES['kop_gambar']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Pilih file gambar banner kop surat unit terlebih dahulu.';
            header('Location: ' . url('kelola-spmb/surat-pernyataan?unit=' . $jenjang));
            exit;
        }

        $saved = self::saveCompressedUpload($_FILES['kop_gambar'], 'kop_unit_' . strtolower($jenjang), 1024);
        if ($saved) {
            SpmbModel::updateKopTemplate($jenjang, $saved);
            $_SESSION['flash_success'] = "Gambar banner kop surat unit $jenjang berhasil diunggah!";
        } else {
            $_SESSION['flash_error'] = "Gagal mengunggah banner kop unit. Pastikan format PNG/JPG/WEBP.";
        }

        header('Location: ' . url('kelola-spmb/surat-pernyataan?unit=' . $jenjang));
        exit;
    }

    /**
     * Hapus Banner Kop Khusus Unit (Fallback ke Kop Utama SPMB)
     */
    public static function suratPernyataanHapusKop(): void
    {
        CSRF::validate();
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));
        if (!in_array($jenjang, ['PAUD', 'SD', 'SMP', 'SMA'])) {
            $jenjang = 'SD';
        }

        SpmbModel::updateKopTemplate($jenjang, null);
        $_SESSION['flash_success'] = "Banner kop unit $jenjang telah direset ke kop utama SPMB.";
        header('Location: ' . url('kelola-spmb/surat-pernyataan?unit=' . $jenjang));
        exit;
    }

    /**
     * Reset Template Surat Unit ke Standar Sistem
     */
    public static function suratPernyataanReset(): void
    {
        CSRF::validate();
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));
        if (!in_array($jenjang, ['PAUD', 'SD', 'SMP', 'SMA'])) {
            $jenjang = 'SD';
        }

        // Hapus template dan biarkan AutoMigrate mengisi ulang
        Database::getInstance()->query("DELETE FROM `spmb_surat_template` WHERE `jenjang` = ?", [$jenjang]);
        AutoMigrate::run(true);

        $_SESSION['flash_success'] = "Template Surat Komitmen unit $jenjang berhasil direset ke standar sistem!";
        header('Location: ' . url('kelola-spmb/surat-pernyataan?unit=' . $jenjang));
        exit;
    }

    /**
     * Cetak Surat Pernyataan Komitmen oleh Admin
     */
    public static function suratPernyataanCetakAdmin(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            http_response_code(404);
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Data calon siswa tidak ditemukan.', 'error');
        }

        $renderedDoc   = SpmbModel::renderSuratKomitmen($pendaftar);
        $signatureData = SpmbModel::getDecryptedSignature($pendaftar);
        $template      = $renderedDoc;
        $renderedContent = $renderedDoc['isi_rendered'] ?? '';
        $kopUrl        = !empty($renderedDoc['kop_gambar']) ? url($renderedDoc['kop_gambar']) : '';
        $decryptedAyah = $signatureData['ayah'] ?? null;
        $decryptedIbu  = $signatureData['ibu'] ?? null;
        $pageTitle     = 'Surat Pernyataan Komitmen - ' . $pendaftar['nama_lengkap'];

        include MODULES_PATH . '/kelola-spmb/views/surat_pernyataan/cetak.php';
        exit;
    }

    /**
     * Reset Tanda Tangan Komitmen oleh Admin
     */
    public static function suratPernyataanResetTtd(int $id): void
    {
        CSRF::validate();
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Data calon siswa tidak ditemukan.', 'error');
        }

        SpmbModel::resetKomitmenSignature($id);
        $_SESSION['flash_success'] = "Tanda tangan komitmen untuk {$pendaftar['nama_lengkap']} berhasil direset. Orang tua dapat menandatangani ulang.";

        $ref = $_SERVER['HTTP_REFERER'] ?? url('kelola-spmb/siswa-baru/detail/' . $id);
        header('Location: ' . $ref);
        exit;
    }

    /**
     * Kirim Tautan TTD Komitmen via WhatsApp ke Orang Tua
     */
    public static function kirimWaKomitmen(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['is_ajax']);
        if (!CSRF::validate(false)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
                exit;
            }
            Response::redirect(url('kelola-spmb/siswa-baru/detail/' . $id), 'Token CSRF tidak valid.', 'error');
        }

        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan.']);
                exit;
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Data calon siswa tidak ditemukan.', 'error');
        }

        $phone = $pendaftar['no_hp'] ?: ($pendaftar['no_hp_ayah'] ?: ($pendaftar['no_hp_ibu'] ?: ''));
        $linkKomitmen = url('spmb/komitmen/' . $pendaftar['biodata_token']);

        $msg = "Assalamu'alaikum Wr. Wb. Bpk/Ibu Orang Tua/Wali dari ananda *{$pendaftar['nama_lengkap']}* (No. Registrasi: {$pendaftar['nomor_pendaftaran']}).\n\n"
             . "Sebagai tahapan akhir administrasi pendaftaran di *{$pendaftar['jenjang']} SIT Bina Insan Palu*, mohon untuk membaca dan menandatangani *Surat Pernyataan Komitmen Orang Tua/Wali Murid* secara digital melalui tautan resmi berikut:\n\n"
             . "👉 {$linkKomitmen}\n\n"
             . "Mohon ditandatangani segera menggunakan layar HP / Tablet Anda.\n\n"
             . "Terima kasih.\nPanitia SPMB SIT Bina Insan Palu";

        $sent = false;
        $errMsg = '';

        if (class_exists('WaGateway') && !empty($phone)) {
            try {
                $res = WaGateway::sendMessage($phone, $msg);
                $sent = !empty($res['success']);
                $errMsg = $res['message'] ?? '';
            } catch (Throwable $e) {
                $errMsg = $e->getMessage();
            }
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $sent,
                'message' => $sent ? 'Tautan Surat Komitmen berhasil dikirim via WhatsApp!' : ('Gagal mengirim WA: ' . $errMsg),
                'link'    => $linkKomitmen
            ]);
            exit;
        }

        if ($sent) {
            $_SESSION['flash_success'] = 'Tautan Surat Komitmen berhasil dikirim ke WhatsApp orang tua!';
        } else {
            $_SESSION['flash_error'] = 'Gagal mengirim WhatsApp: ' . $errMsg;
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? url('kelola-spmb/siswa-baru/detail/' . $id);
        header('Location: ' . $ref);
        exit;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 7.5. PENGEMBALIAN & VERIFIKASI BERKAS PENDAFTAR
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Ambil data pendaftar untuk Modal Verifikasi & Pengembalian Berkas (AJAX)
     */
    public static function getVerifikasiBerkasAjax(int $id): void
    {
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            Response::json(['success' => false, 'message' => 'Data calon siswa tidak ditemukan.'], 404);
        }

        $kesanggupanUrl = !empty($pendaftar['surat_kesanggupan']) ? asset($pendaftar['surat_kesanggupan']) : null;
        $isPdf = !empty($pendaftar['surat_kesanggupan']) && strtolower(pathinfo($pendaftar['surat_kesanggupan'], PATHINFO_EXTENSION)) === 'pdf';

        Response::json([
            'success' => true,
            'data' => [
                'id'                     => (int)$pendaftar['id'],
                'nomor_pendaftaran'      => $pendaftar['nomor_pendaftaran'],
                'nama_lengkap'           => $pendaftar['nama_lengkap'],
                'nama_panggilan'         => $pendaftar['nama_panggilan'] ?: '-',
                'jenjang'                => $pendaftar['jenjang'],
                'jenis_pendaftaran'      => $pendaftar['jenis_pendaftaran'] ?? 'Siswa Baru',
                'gelombang'              => $pendaftar['gelombang'] ?? 'Gelombang 1',
                'kategori_pendaftar'     => $pendaftar['kategori_pendaftar'] ?: ($pendaftar['jalur_pendaftaran'] ?: 'Umum'),
                'jenis_kelamin'          => $pendaftar['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan',
                'tempat_lahir'           => $pendaftar['tempat_lahir'] ?? '-',
                'tanggal_lahir'          => !empty($pendaftar['tanggal_lahir']) ? SpmbModel::formatTglIndo($pendaftar['tanggal_lahir']) : '-',
                'tanggal_lahir_raw'      => $pendaftar['tanggal_lahir'] ?? null,
                'nik'                    => $pendaftar['nik'] ?: '-',
                'nisn'                   => $pendaftar['nisn'] ?: '-',
                'no_kk'                  => $pendaftar['no_kk'] ?: '-',
                'agama'                  => !empty($pendaftar['agama']) ? $pendaftar['agama'] : 'Islam',
                'anak_ke'                => !empty($pendaftar['anak_ke']) ? $pendaftar['anak_ke'] : '1',
                'jumlah_saudara'         => !empty($pendaftar['jumlah_saudara']) ? $pendaftar['jumlah_saudara'] : '0',
                'status_keluarga'        => !empty($pendaftar['status_keluarga']) ? $pendaftar['status_keluarga'] : '-',
                'golongan_darah'         => !empty($pendaftar['golongan_darah']) ? $pendaftar['golongan_darah'] : '-',
                'tinggi_badan'           => !empty($pendaftar['tinggi_badan']) ? $pendaftar['tinggi_badan'] : '-',
                'berat_badan'            => !empty($pendaftar['berat_badan']) ? $pendaftar['berat_badan'] : '-',
                'riwayat_penyakit'       => !empty($pendaftar['riwayat_penyakit']) ? $pendaftar['riwayat_penyakit'] : 'Tidak ada',
                // Kontak & Alamat
                'no_hp'                  => !empty($pendaftar['no_hp']) ? $pendaftar['no_hp'] : (!empty($pendaftar['no_hp_ayah']) ? $pendaftar['no_hp_ayah'] : (!empty($pendaftar['no_hp_ibu']) ? $pendaftar['no_hp_ibu'] : '-')),
                'no_wa'                  => !empty($pendaftar['no_wa']) ? $pendaftar['no_wa'] : '-',
                'email'                  => !empty($pendaftar['email']) ? $pendaftar['email'] : '-',
                'alamat'                 => !empty($pendaftar['alamat']) ? $pendaftar['alamat'] : '-',
                'rt'                     => !empty($pendaftar['rt']) ? $pendaftar['rt'] : '-',
                'rw'                     => !empty($pendaftar['rw']) ? $pendaftar['rw'] : '-',
                'kelurahan'              => !empty($pendaftar['kelurahan']) ? $pendaftar['kelurahan'] : '-',
                'kecamatan'              => !empty($pendaftar['kecamatan']) ? $pendaftar['kecamatan'] : '-',
                'kota'                   => !empty($pendaftar['kota']) ? $pendaftar['kota'] : 'Palu',
                'provinsi'               => !empty($pendaftar['provinsi']) ? $pendaftar['provinsi'] : 'Sulawesi Tengah',
                'kode_pos'               => !empty($pendaftar['kode_pos']) ? $pendaftar['kode_pos'] : '-',
                'tempat_tinggal'         => !empty($pendaftar['tempat_tinggal']) ? $pendaftar['tempat_tinggal'] : '-',
                // Asal Sekolah
                'asal_sekolah'           => !empty($pendaftar['asal_sekolah']) ? $pendaftar['asal_sekolah'] : '-',
                'npsn_asal'              => !empty($pendaftar['npsn_asal']) ? $pendaftar['npsn_asal'] : '-',
                'alamat_sekolah_asal'    => !empty($pendaftar['alamat_sekolah_asal']) ? $pendaftar['alamat_sekolah_asal'] : '-',
                'kelas_tujuan'           => !empty($pendaftar['kelas_tujuan']) ? $pendaftar['kelas_tujuan'] : '-',
                // Orang Tua Ayah
                'nama_ayah'              => !empty($pendaftar['nama_ayah']) ? $pendaftar['nama_ayah'] : '-',
                'nik_ayah'               => !empty($pendaftar['nik_ayah']) ? $pendaftar['nik_ayah'] : '-',
                'status_ayah'            => !empty($pendaftar['status_ayah']) ? $pendaftar['status_ayah'] : 'Masih Hidup',
                'tahun_lahir_ayah'       => !empty($pendaftar['tahun_lahir_ayah']) ? $pendaftar['tahun_lahir_ayah'] : '-',
                'pendidikan_ayah'        => !empty($pendaftar['pendidikan_ayah']) ? $pendaftar['pendidikan_ayah'] : '-',
                'pekerjaan_ayah'         => !empty($pendaftar['pekerjaan_ayah']) ? $pendaftar['pekerjaan_ayah'] : '-',
                'kantor_ayah'            => !empty($pendaftar['kantor_ayah']) ? $pendaftar['kantor_ayah'] : '-',
                'jabatan_ayah'           => !empty($pendaftar['jabatan_ayah']) ? $pendaftar['jabatan_ayah'] : '-',
                'penghasilan_ayah'       => !empty($pendaftar['penghasilan_ayah']) ? $pendaftar['penghasilan_ayah'] : '-',
                'no_hp_ayah'             => !empty($pendaftar['no_hp_ayah']) ? $pendaftar['no_hp_ayah'] : '-',
                // Orang Tua Ibu
                'nama_ibu'               => !empty($pendaftar['nama_ibu']) ? $pendaftar['nama_ibu'] : '-',
                'nik_ibu'                => !empty($pendaftar['nik_ibu']) ? $pendaftar['nik_ibu'] : '-',
                'status_ibu'             => !empty($pendaftar['status_ibu']) ? $pendaftar['status_ibu'] : 'Masih Hidup',
                'tahun_lahir_ibu'        => !empty($pendaftar['tahun_lahir_ibu']) ? $pendaftar['tahun_lahir_ibu'] : '-',
                'pendidikan_ibu'         => !empty($pendaftar['pendidikan_ibu']) ? $pendaftar['pendidikan_ibu'] : '-',
                'pekerjaan_ibu'          => !empty($pendaftar['pekerjaan_ibu']) ? $pendaftar['pekerjaan_ibu'] : '-',
                'kantor_ibu'             => !empty($pendaftar['kantor_ibu']) ? $pendaftar['kantor_ibu'] : '-',
                'jabatan_ibu'            => !empty($pendaftar['jabatan_ibu']) ? $pendaftar['jabatan_ibu'] : '-',
                'penghasilan_ibu'        => !empty($pendaftar['penghasilan_ibu']) ? $pendaftar['penghasilan_ibu'] : '-',
                'no_hp_ibu'              => !empty($pendaftar['no_hp_ibu']) ? $pendaftar['no_hp_ibu'] : '-',
                // Wali
                'nama_wali'              => !empty($pendaftar['nama_wali']) ? $pendaftar['nama_wali'] : '-',
                'nik_wali'               => !empty($pendaftar['nik_wali']) ? $pendaftar['nik_wali'] : '-',
                'hubungan_wali'          => !empty($pendaftar['hubungan_wali']) ? $pendaftar['hubungan_wali'] : '-',
                'pekerjaan_wali'         => !empty($pendaftar['pekerjaan_wali']) ? $pendaftar['pekerjaan_wali'] : '-',
                'penghasilan_wali'       => !empty($pendaftar['penghasilan_wali']) ? $pendaftar['penghasilan_wali'] : '-',
                'no_hp_wali'             => !empty($pendaftar['no_hp_wali']) ? $pendaftar['no_hp_wali'] : '-',
                // Berkas-Berkas File Terupload
                'file_screenshot_dapodik'     => $pendaftar['file_screenshot_dapodik'] ?? null,
                'file_screenshot_dapodik_url' => !empty($pendaftar['file_screenshot_dapodik']) ? asset($pendaftar['file_screenshot_dapodik']) : null,
                'berkas_kk'                   => $pendaftar['berkas_kk'] ?? null,
                'berkas_kk_url'               => !empty($pendaftar['berkas_kk']) ? asset($pendaftar['berkas_kk']) : null,
                'berkas_akta'                 => $pendaftar['berkas_akta'] ?? null,
                'berkas_akta_url'             => !empty($pendaftar['berkas_akta']) ? asset($pendaftar['berkas_akta']) : null,
                'berkas_ktp_ayah'             => $pendaftar['berkas_ktp_ayah'] ?? null,
                'berkas_ktp_ayah_url'         => !empty($pendaftar['berkas_ktp_ayah']) ? asset($pendaftar['berkas_ktp_ayah']) : null,
                'berkas_ktp_ibu'              => $pendaftar['berkas_ktp_ibu'] ?? null,
                'berkas_ktp_ibu_url'          => !empty($pendaftar['berkas_ktp_ibu']) ? asset($pendaftar['berkas_ktp_ibu']) : null,
                'foto'                        => $pendaftar['foto'] ?? null,
                'foto_url'                    => !empty($pendaftar['foto']) ? asset($pendaftar['foto']) : null,
                // Status Komitmen
                'status_ttd_komitmen'    => $pendaftar['status_ttd_komitmen'] ?? 'Belum TTD',
                'ttd_submitted_at'       => !empty($pendaftar['ttd_submitted_at']) ? date('d/m/Y H:i', strtotime($pendaftar['ttd_submitted_at'])) : null,
                'link_cetak_komitmen'    => url('kelola-spmb/surat-pernyataan/cetak/' . $pendaftar['id']),
                'link_ttd_public'        => url('spmb/komitmen/' . ($pendaftar['biodata_token'] ?? '')),
                // Status Berkas
                'status_berkas'          => $pendaftar['status_berkas'] ?? 'Belum Dikembalikan',
                'is_data_sesuai'         => (int)($pendaftar['is_data_sesuai'] ?? 0),
                'surat_kesanggupan'      => $kesanggupanUrl,
                'surat_kesanggupan_raw'  => $pendaftar['surat_kesanggupan'],
                'is_pdf_kesanggupan'     => $isPdf,
                'tanggal_kembali_berkas' => !empty($pendaftar['tanggal_kembali_berkas']) ? date('d/m/Y H:i', strtotime($pendaftar['tanggal_kembali_berkas'])) : null,
                'catatan_berkas'         => $pendaftar['catatan_berkas'] ?? '',
                'petugas_verifikasi_berkas' => $pendaftar['petugas_verifikasi_berkas'] ?? ''
            ]
        ]);
    }

    /**
     * Simpan Verifikasi & Upload Surat Kesanggupan Pengembalian Berkas
     */
    public static function submitVerifikasiBerkas(): void
    {
        CSRF::validate();

        $id = (int)($_POST['pendaftar_id'] ?? 0);
        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
                Response::json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'], 404);
            }
            Response::redirect(url('kelola-spmb/siswa-baru'), 'Data pendaftar tidak ditemukan.', 'error');
        }

        $statusBerkas = trim($_POST['status_berkas'] ?? 'Lengkap');
        if (!in_array($statusBerkas, ['Belum Dikembalikan', 'Lengkap', 'Perlu Perbaikan'])) {
            $statusBerkas = 'Lengkap';
        }

        $isDataSesuai = !empty($_POST['is_data_sesuai']) ? 1 : 0;
        $catatanBerkas = trim($_POST['catatan_berkas'] ?? '');
        $petugas = !empty($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : (!empty($_SESSION['username']) ? $_SESSION['username'] : 'Petugas SPMB');

        $payload = [
            'status_berkas'             => $statusBerkas,
            'is_data_sesuai'            => $isDataSesuai,
            'catatan_berkas'            => $catatanBerkas,
            'petugas_verifikasi_berkas' => $petugas
        ];

        // Upload Surat Kesanggupan jika ada file baru diunggah
        if (!empty($_FILES['surat_kesanggupan']['name']) && $_FILES['surat_kesanggupan']['error'] === UPLOAD_ERR_OK) {
            $saved = self::saveCompressedUpload($_FILES['surat_kesanggupan'], 'kesanggupan_' . $id, 1024);
            if ($saved) {
                $payload['surat_kesanggupan'] = $saved;
            }
        }

        try {
            SpmbModel::saveVerifikasiBerkas($id, $payload);
            $msg = "Pengembalian berkas untuk ananda {$pendaftar['nama_lengkap']} berhasil disimpan (Status: $statusBerkas)!";

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
                Response::json([
                    'success' => true,
                    'message' => $msg,
                    'status_berkas' => $statusBerkas
                ]);
            }

            $_SESSION['flash_success'] = $msg;
        } catch (Throwable $e) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
                Response::json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
            }
            $_SESSION['flash_error'] = 'Gagal menyimpan verifikasi berkas: ' . $e->getMessage();
        }

        $ref = $_SERVER['HTTP_REFERER'] ?? url('kelola-spmb/siswa-baru');
        header('Location: ' . $ref);
        exit;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 8. HALAMAN PUBLIK TTD DIGITAL KOMITMEN ORANG TUA (TOKEN-BASED)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Halaman Publik Penandatanganan Surat Pernyataan Komitmen Orang Tua
     */
    public static function komitmenPublic(string $token): void
    {
        $pendaftar = SpmbModel::getPendaftarByBiodataToken($token);
        if (!$pendaftar) {
            http_response_code(404);
            $pageTitle = 'Tautan Tidak Valid - SPMB SIT Bina Insan Palu';
            include MODULES_PATH . '/kelola-spmb/views/biodata_invalid.php';
            exit;
        }

        $renderedDoc   = SpmbModel::renderSuratKomitmen($pendaftar);
        $signatureData = SpmbModel::getDecryptedSignature($pendaftar);
        $template      = $renderedDoc;
        $renderedContent = $renderedDoc['isi_rendered'] ?? '';
        $kopUrl        = !empty($renderedDoc['kop_gambar']) ? url($renderedDoc['kop_gambar']) : '';
        $sudahTtd      = ($pendaftar['status_ttd_komitmen'] ?? '') === 'Sudah TTD';
        $statusTtd     = $pendaftar['status_ttd_komitmen'] ?? 'Belum TTD';
        $decryptedAyah = $signatureData['ayah'] ?? null;
        $decryptedIbu  = $signatureData['ibu'] ?? null;
        $pageTitle     = 'Surat Pernyataan Komitmen Orang Tua - ' . $pendaftar['nama_lengkap'];

        include MODULES_PATH . '/kelola-spmb/views/komitmen_public.php';
        exit;
    }

    /**
     * Proses Submit Penandatanganan Komitmen Digital
     */
    public static function komitmenSubmit(string $token): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['is_ajax']);
        $pendaftar = SpmbModel::getPendaftarByBiodataToken($token);

        if (!$pendaftar) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Tautan tidak valid.']);
                exit;
            }
            Response::redirect(url('spmb/komitmen/' . $token), 'Tautan tidak valid.', 'error');
        }

        $ttdAyah = trim($_POST['ttd_ayah_data'] ?? ($_POST['ttd_ayah'] ?? ''));
        $ttdIbu  = trim($_POST['ttd_ibu_data'] ?? ($_POST['ttd_ibu'] ?? ''));

        // Validasi: Minimal 1 tanda tangan harus diisi
        if (empty($ttdAyah) && empty($ttdIbu)) {
            $msg = 'Wajib membubuhkan tanda tangan digital pada area tanda tangan sebelum mengirimkan formulir.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            Response::redirect(url('spmb/komitmen/' . $token), $msg, 'error');
        }

        // Simpan tanda tangan terenkripsi
        $saved = SpmbModel::saveKomitmenSignature($pendaftar['id'], $_POST);

        if ($saved) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'  => true,
                    'message'  => 'Alhamdulillah! Surat Pernyataan Komitmen Orang Tua berhasil ditandatangani dan diverifikasi.',
                    'redirect' => url('spmb/komitmen/' . $token . '?status=selesai')
                ]);
                exit;
            }
            header('Location: ' . url('spmb/komitmen/' . $token . '?status=selesai'));
            exit;
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Gagal memproses tanda tangan digital.']);
                exit;
            }
            Response::redirect(url('spmb/komitmen/' . $token), 'Gagal menyimpan tanda tangan.', 'error');
        }
    }

    /**
     * Unduh / Cetak Publik Surat Pernyataan Komitmen yang Telah Ditandatangani
     */
    public static function komitmenCetakPublic(string $token): void
    {
        $pendaftar = SpmbModel::getPendaftarByBiodataToken($token);
        if (!$pendaftar) {
            http_response_code(404);
            Response::redirect(url('/'), 'Tautan tidak valid.', 'error');
        }

        $renderedDoc   = SpmbModel::renderSuratKomitmen($pendaftar);
        $signatureData = SpmbModel::getDecryptedSignature($pendaftar);
        $template      = $renderedDoc;
        $renderedContent = $renderedDoc['isi_rendered'] ?? '';
        $kopUrl        = !empty($renderedDoc['kop_gambar']) ? url($renderedDoc['kop_gambar']) : '';
        $decryptedAyah = $signatureData['ayah'] ?? null;
        $decryptedIbu  = $signatureData['ibu'] ?? null;
        $pageTitle     = 'Cetak Surat Pernyataan Komitmen - ' . $pendaftar['nama_lengkap'];

        include MODULES_PATH . '/kelola-spmb/views/surat_pernyataan/cetak.php';
        exit;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 9. HASIL SCREENING PSIKOLOG (GALERI FILE PDF)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Halaman Utama Galeri File Hasil Screening Psikolog Siswa
     */
    public static function screeningPsikologIndex(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;

        $filters = [
            'group_id'           => $groupId,
            'jenjang'            => trim($_GET['unit'] ?? ($_GET['jenjang'] ?? '')),
            'jenis_pendaftaran'  => trim($_GET['jenis_pendaftaran'] ?? ''),
            'status_kelulusan'   => 'Lulus / Diterima', // Khusus Hasil Psikolog: Hanya siswa yang Lulus / Diterima
            'status_screening'   => trim($_GET['status_screening'] ?? ''),
            'status_rekomendasi' => trim($_GET['status_rekomendasi'] ?? ''),
            'search'             => trim($_GET['search'] ?? '')
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 24; // 24 kartu per halaman agar pas di grid 2, 3, atau 4 kolom
        $offset = ($page - 1) * $limit;

        $totalItems = SpmbModel::countScreeningPsikolog($filters);
        $totalPages = max(1, ceil($totalItems / $limit));
        $items = SpmbModel::getScreeningPsikologList($filters, $limit, $offset);
        $stats = SpmbModel::getScreeningPsikologStats($groupId);

        // Ambil list ringkas pendaftar (hanya yang Lulus/Diterima) untuk autocomplete di modal upload
        $allPendaftar = Database::getInstance()->findAll("
            SELECT id, nomor_pendaftaran, nama_lengkap, jenjang, jenis_pendaftaran, file_screening_psikolog
            FROM `spmb_pendaftar`
            WHERE group_id = ? AND status_kelulusan = 'Lulus / Diterima'
            ORDER BY nama_lengkap ASC
        ", [$groupId]);

        self::view(
            'screening_psikolog/index',
            [
                'activeGroup'   => $activeGroup,
                'items'         => $items,
                'stats'         => $stats,
                'filters'       => $filters,
                'page'          => $page,
                'totalPages'    => $totalPages,
                'totalItems'    => $totalItems,
                'limit'         => $limit,
                'allPendaftar'  => $allPendaftar
            ],
            'Hasil Screening Psikolog',
            [['label' => 'Kelola SPMB', 'url' => url('kelola-spmb')], ['label' => 'Hasil Psikolog']]
        );
    }

    /**
     * Handler Upload File PDF Hasil Screening Psikolog
     */
    public static function screeningPsikologUpload(): void
    {
        CSRF::validate();

        $pendaftarId = (int)($_POST['pendaftar_id'] ?? 0);
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if ($pendaftarId <= 0) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Pilih data siswa terlebih dahulu.'], 400);
            }
            $_SESSION['flash_error'] = 'Pilih data siswa terlebih dahulu.';
            Response::redirect(url('kelola-spmb/screening-psikolog'));
            return;
        }

        $pendaftar = SpmbModel::getPendaftarById($pendaftarId);
        if (!$pendaftar) {
            if ($isAjax) {
                Response::json(['success' => false, 'message' => 'Data calon siswa tidak ditemukan.'], 404);
            }
            $_SESSION['flash_error'] = 'Data calon siswa tidak ditemukan.';
            Response::redirect(url('kelola-spmb/screening-psikolog'));
            return;
        }

        $existing = SpmbModel::getScreeningByPendaftarId($pendaftarId);
        $uploadedPath = null;
        $originalName = null;
        $fileSize = 0;

        // Cek file upload PDF
        if (!empty($_FILES['file_pdf']['name'])) {
            $file = $_FILES['file_pdf'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errMsg = 'Terjadi kesalahan saat mengunggah berkas PDF (kode: ' . $file['error'] . ').';
                if ($isAjax) Response::json(['success' => false, 'message' => $errMsg], 400);
                $_SESSION['flash_error'] = $errMsg;
                Response::redirect(url('kelola-spmb/screening-psikolog'));
                return;
            }

            // Validasi format ekstensi
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $errMsg = 'Format berkas tidak valid! Hanya file berekstensi .PDF yang diperbolehkan.';
                if ($isAjax) Response::json(['success' => false, 'message' => $errMsg], 400);
                $_SESSION['flash_error'] = $errMsg;
                Response::redirect(url('kelola-spmb/screening-psikolog'));
                return;
            }

            // Validasi ukuran maks 20 MB
            if ($file['size'] > 20 * 1024 * 1024) {
                $errMsg = 'Ukuran berkas terlalu besar! Maksimal 20 MB.';
                if ($isAjax) Response::json(['success' => false, 'message' => $errMsg], 400);
                $_SESSION['flash_error'] = $errMsg;
                Response::redirect(url('kelola-spmb/screening-psikolog'));
                return;
            }

            $uploadDir = BASE_PATH . '/public/uploads/spmb/psikolog/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }

            // Hapus file lama jika ada penggantian
            if ($existing && !empty($existing['file_pdf'])) {
                $oldPath = BASE_PATH . '/public/' . ltrim($existing['file_pdf'], '/');
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $safeNo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pendaftar['nomor_pendaftaran']);
            $newFileName = 'psikolog_' . $safeNo . '_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.pdf';
            $targetPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $errMsg = 'Gagal menyimpan berkas PDF ke direktori server.';
                if ($isAjax) Response::json(['success' => false, 'message' => $errMsg], 500);
                $_SESSION['flash_error'] = $errMsg;
                Response::redirect(url('kelola-spmb/screening-psikolog'));
                return;
            }

            $uploadedPath = 'uploads/spmb/psikolog/' . $newFileName;
            $originalName = pathinfo($file['name'], PATHINFO_BASENAME);
            $fileSize = $file['size'];
        }

        $statusRekomendasi = trim($_POST['status_rekomendasi'] ?? 'Direkomendasikan');
        if (!in_array($statusRekomendasi, ['Direkomendasikan', 'Dipertimbangkan', 'Tidak Direkomendasikan', 'Belum Ada Hasil'])) {
            $statusRekomendasi = 'Direkomendasikan';
        }

        $tanggalScreening = !empty($_POST['tanggal_screening']) ? trim($_POST['tanggal_screening']) : date('Y-m-d');
        $namaPsikolog     = trim($_POST['nama_psikolog'] ?? '');
        $catatan          = trim($_POST['catatan'] ?? '');

        // Jika tidak upload file baru dan belum ada file sebelumnya
        if (!$uploadedPath && (!$existing || empty($existing['file_pdf']))) {
            $errMsg = 'Silakan pilih file PDF hasil screening psikolog terlebih dahulu.';
            if ($isAjax) Response::json(['success' => false, 'message' => $errMsg], 400);
            $_SESSION['flash_error'] = $errMsg;
            Response::redirect(url('kelola-spmb/screening-psikolog'));
            return;
        }

        $dataSave = [
            'status_rekomendasi' => $statusRekomendasi,
            'catatan'            => $catatan ?: null,
            'nama_psikolog'      => $namaPsikolog ?: null,
            'tanggal_screening'  => $tanggalScreening,
            'uploaded_by'        => Auth::id() ?: null
        ];

        if ($uploadedPath) {
            $dataSave['file_pdf']           = $uploadedPath;
            $dataSave['file_name_original'] = $originalName;
            $dataSave['file_size']          = $fileSize;
        }

        SpmbModel::saveScreeningPsikolog($pendaftarId, $dataSave);

        $successMsg = "Alhamdulillah! File PDF hasil screening psikolog untuk siswa '{$pendaftar['nama_lengkap']}' ({$pendaftar['nomor_pendaftaran']}) berhasil disimpan!";

        if ($isAjax) {
            Response::json([
                'success' => true,
                'message' => $successMsg,
                'pendaftar_id' => $pendaftarId
            ]);
            return;
        }

        $_SESSION['flash_success'] = $successMsg;
        $returnUrl = !empty($_POST['return_url']) ? $_POST['return_url'] : url('kelola-spmb/screening-psikolog');
        Response::redirect($returnUrl);
    }

    /**
     * Hapus Berkas Hasil Screening Psikolog
     */
    public static function screeningPsikologHapus(int $id): void
    {
        CSRF::validate();
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $pendaftar = SpmbModel::getPendaftarById($id);
        if (!$pendaftar) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
            $_SESSION['flash_error'] = 'Data calon siswa tidak ditemukan.';
            Response::redirect(url('kelola-spmb/screening-psikolog'));
            return;
        }

        SpmbModel::deleteScreeningPsikolog($id);

        $msg = "File PDF hasil screening psikolog siswa '{$pendaftar['nama_lengkap']}' berhasil dihapus.";
        if ($isAjax) {
            Response::json(['success' => true, 'message' => $msg]);
            return;
        }

        $_SESSION['flash_success'] = $msg;
        Response::redirect(url('kelola-spmb/screening-psikolog'));
    }

    /**
     * Tampilkan File PDF Langsung Inline di Browser (Preview)
     */
    public static function screeningPsikologView(int $id): void
    {
        $screening = SpmbModel::getScreeningByPendaftarId($id);
        if (!$screening || empty($screening['file_pdf'])) {
            http_response_code(404);
            die("File PDF hasil screening psikolog belum tersedia untuk siswa ini.");
        }

        $fullPath = BASE_PATH . '/public/' . ltrim($screening['file_pdf'], '/');
        if (!file_exists($fullPath)) {
            http_response_code(404);
            die("Berkas PDF tidak ditemukan pada server penyimpanan.");
        }

        $filename = $screening['file_name_original'] ?: basename($fullPath);

        // Bersihkan output buffer sebelum kirim file biner
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . str_replace('"', '', $filename) . '"');
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=86400');
        readfile($fullPath);
        exit;
    }

    /**
     * Download File PDF dengan Nama Rapi
     */
    public static function screeningPsikologDownload(int $id): void
    {
        $screening = SpmbModel::getScreeningByPendaftarId($id);
        if (!$screening || empty($screening['file_pdf'])) {
            http_response_code(404);
            die("File PDF hasil screening psikolog belum tersedia untuk siswa ini.");
        }

        $fullPath = BASE_PATH . '/public/' . ltrim($screening['file_pdf'], '/');
        if (!file_exists($fullPath)) {
            http_response_code(404);
            die("Berkas PDF tidak ditemukan pada server penyimpanan.");
        }

        $cleanNomor = preg_replace('/[^a-zA-Z0-9_-]/', '_', $screening['nomor_pendaftaran']);
        $cleanNama  = preg_replace('/[^a-zA-Z0-9_\s-]/', '', $screening['nama_lengkap']);
        $cleanNama  = str_replace(' ', '_', $cleanNama);
        $downloadName = "Hasil_Screening_Psikolog_{$cleanNomor}_{$cleanNama}.pdf";

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    /**
     * Export Rekapan Status Hasil Screening Psikolog ke CSV / Excel
     */
    public static function screeningPsikologExport(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;

        $filters = [
            'group_id'           => $groupId,
            'jenjang'            => trim($_GET['unit'] ?? ($_GET['jenjang'] ?? '')),
            'jenis_pendaftaran'  => trim($_GET['jenis_pendaftaran'] ?? ''),
            'status_kelulusan'   => 'Lulus / Diterima',
            'status_screening'   => trim($_GET['status_screening'] ?? ''),
            'status_rekomendasi' => trim($_GET['status_rekomendasi'] ?? ''),
            'search'             => trim($_GET['search'] ?? '')
        ];

        // Ambil semua tanpa pagination
        $items = SpmbModel::getScreeningPsikologList($filters, 10000, 0);

        $filename = 'Rekap_Screening_Psikolog_' . date('Ymd_His') . '.csv';

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // BOM untuk Excel UTF-8
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'No',
            'No. Pendaftaran',
            'Nama Lengkap',
            'Unit / Jenjang',
            'Jenis Pendaftaran',
            'Status Kelulusan SPMB',
            'Status Berkas PDF',
            'Hasil Rekomendasi Psikolog',
            'Nama Psikolog / Lembaga',
            'Tanggal Screening',
            'Catatan / Rekomendasi',
            'Tanggal Upload'
        ]);

        $no = 1;
        foreach ($items as $it) {
            $hasPdf = !empty($it['file_pdf']);
            fputcsv($out, [
                $no++,
                $it['nomor_pendaftaran'],
                $it['nama_lengkap'],
                $it['jenjang'],
                $it['jenis_pendaftaran'],
                $it['status_kelulusan'] ?: 'Menunggu Seleksi',
                $hasPdf ? 'Sudah Diunggah (PDF)' : 'Belum Diunggah',
                $hasPdf ? ($it['status_rekomendasi'] ?: 'Direkomendasikan') : '-',
                $it['nama_psikolog'] ?: '-',
                $it['tanggal_screening'] ? date('d/m/Y', strtotime($it['tanggal_screening'])) : '-',
                $it['catatan_psikolog'] ?: '-',
                $it['screening_created_at'] ? date('d/m/Y H:i', strtotime($it['screening_created_at'])) : '-'
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Dapatkan Data Siswa Penerima Broadcast WA Hasil Psikolog (AJAX)
     */
    public static function screeningPsikologBroadcastData(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $filters = [
            'group_id'           => $activeGroup['id'] ?? null,
            'jenjang'            => trim($_GET['jenjang'] ?? ''),
            'status_rekomendasi' => trim($_GET['status_rekomendasi'] ?? ''),
            'status_wa'          => trim($_GET['status_wa'] ?? 'belum'),
            'limit'              => !empty($_GET['limit']) ? (int)$_GET['limit'] : 0,
        ];

        $items = SpmbModel::getDaftarScreeningPsikologBroadcast($filters);

        Response::json([
            'success'         => true,
            'total'           => count($items),
            'items'           => $items,
            'defaultTemplate' => SpmbModel::getDefaultWaPsikologTemplate()
        ]);
    }

    /**
     * Kirim Pesan WhatsApp Berkas Hasil Psikolog Satuan (Bisa dipanggil loop AJAX broadcast atau tombol tunggal)
     */
    public static function screeningPsikologSendWaSingle(int $id = 0): void
    {
        if (!CSRF::validate(false)) {
            Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            return;
        }

        $pendaftarId = $id > 0 ? $id : (int)($_POST['pendaftar_id'] ?? 0);
        if ($pendaftarId <= 0) {
            Response::json(['success' => false, 'message' => 'ID calon siswa tidak valid.'], 400);
            return;
        }

        $customTemplate = !empty($_POST['pesan_custom']) ? trim($_POST['pesan_custom']) : null;

        $res = SpmbModel::sendWaScreeningPsikolog($pendaftarId, $customTemplate);

        $httpCode = !empty($res['success']) ? 200 : 400;
        Response::json($res, $httpCode);
    }

    /**
     * Halaman Publik Hasil Screening Psikolog (Akses Tanpa Login via WhatsApp / Token)
     */
    public static function screeningPsikologPublicView(string $token): void
    {
        $token = trim($token);
        $pendaftar = SpmbModel::getPendaftarByPsikologToken($token);

        if (!$pendaftar || empty($pendaftar['file_pdf'])) {
            http_response_code(404);
            $pageTitle = 'Berkas Tidak Ditemukan - SPMB SIT Bina Insan Palu';
            include MODULES_PATH . '/kelola-spmb/views/biodata_invalid.php';
            exit;
        }

        $fullPath = BASE_PATH . '/public/' . ltrim($pendaftar['file_pdf'], '/');
        if (!file_exists($fullPath)) {
            http_response_code(404);
            $pageTitle = 'Berkas Belum Tersedia - SPMB SIT Bina Insan Palu';
            include MODULES_PATH . '/kelola-spmb/views/biodata_invalid.php';
            exit;
        }

        $pageTitle = 'Hasil Screening Psikolog - ' . $pendaftar['nama_lengkap'];
        include MODULES_PATH . '/kelola-spmb/views/screening_psikolog/public_view.php';
        exit;
    }

    /**
     * Unduh Berkas PDF Hasil Screening Psikolog (Akses Publik via Token)
     */
    public static function screeningPsikologPublicDownload(string $token): void
    {
        $token = trim($token);
        $pendaftar = SpmbModel::getPendaftarByPsikologToken($token);

        if (!$pendaftar || empty($pendaftar['file_pdf'])) {
            http_response_code(404);
            die("Berkas PDF hasil screening psikolog belum tersedia atau tautan tidak valid.");
        }

        $fullPath = BASE_PATH . '/public/' . ltrim($pendaftar['file_pdf'], '/');
        if (!file_exists($fullPath)) {
            http_response_code(404);
            die("Berkas PDF tidak ditemukan pada server penyimpanan.");
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pendaftar['nama_lengkap']);
        $safeNo   = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pendaftar['nomor_pendaftaran']);
        $downloadName = "Hasil_Psikolog_{$safeNo}_{$safeName}.pdf";

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=86400');
        readfile($fullPath);
        exit;
    }

    /**
     * Streaming PDF Inline Langsung (Akses Publik via Token)
     */
    public static function screeningPsikologPublicRaw(string $token): void
    {
        $token = trim($token);
        $pendaftar = SpmbModel::getPendaftarByPsikologToken($token);

        if (!$pendaftar || empty($pendaftar['file_pdf'])) {
            http_response_code(404);
            die("Berkas PDF belum tersedia.");
        }

        $fullPath = BASE_PATH . '/public/' . ltrim($pendaftar['file_pdf'], '/');
        if (!file_exists($fullPath)) {
            http_response_code(404);
            die("Berkas PDF fisik tidak ditemukan.");
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pendaftar['nama_lengkap']);
        $filename = "Hasil_Psikolog_{$safeName}.pdf";

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=86400');
        readfile($fullPath);
        exit;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 7. OBSERVASI AWAL SPMB
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Halaman Utama Rekap & Hasil Observasi Awal Calon Siswa (Admin)
     */
    public static function observasiAwalIndex(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $filters = [
            'group_id'         => $groupId,
            'jenjang'          => trim($_GET['jenjang'] ?? ''),
            'status_observasi' => trim($_GET['status_observasi'] ?? ''),
            'search'           => trim($_GET['search'] ?? '')
        ];

        $totalItems = SpmbModel::countDaftarHasilObservasiAwal($filters);
        $totalPages = max(1, ceil($totalItems / $limit));
        $items = SpmbModel::getDaftarHasilObservasiAwal($filters, $limit, $offset);
        $stats = SpmbModel::getObservasiStats($groupId);

        self::view(
            'observasi_awal/index',
            [
                'activeGroup'   => $activeGroup,
                'items'         => $items,
                'stats'         => $stats,
                'filters'       => $filters,
                'page'          => $page,
                'totalPages'    => $totalPages,
                'totalItems'    => $totalItems,
                'limit'         => $limit,
            ],
            'Hasil Observasi Awal Siswa',
            [['label' => 'Kelola SPMB', 'url' => url('kelola-spmb')], ['label' => 'Observasi Awal']]
        );
    }

    /**
     * Halaman Pengaturan Instrumen Pertanyaan & Group Observasi Awal (Admin)
     */
    public static function observasiAwalPengaturan(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $jenjang = strtoupper(trim($_GET['jenjang'] ?? 'SD'));
        $validJenjang = ['PAUD', 'SD', 'SMP', 'SMA'];
        if (!in_array($jenjang, $validJenjang)) {
            $jenjang = 'SD';
        }

        $groups = SpmbModel::getObservasiGroups($jenjang, false);
        $allQuestions = SpmbModel::getObservasiPertanyaanList($jenjang, null, false);

        // Petakan pertanyaan per group
        $questionsByGroup = [];
        foreach ($allQuestions as $q) {
            $gId = (int)$q['group_id'];
            $questionsByGroup[$gId][] = $q;
        }

        self::view(
            'observasi_awal/pengaturan',
            [
                'activeGroup'      => $activeGroup,
                'currentJenjang'   => $jenjang,
                'validJenjang'     => $validJenjang,
                'groups'           => $groups,
                'questionsByGroup' => $questionsByGroup,
            ],
            'Pengaturan Pertanyaan Observasi Awal - ' . $jenjang,
            [
                ['label' => 'Kelola SPMB', 'url' => url('kelola-spmb')],
                ['label' => 'Observasi Awal', 'url' => url('kelola-spmb/observasi-awal')],
                ['label' => 'Pengaturan Pertanyaan']
            ]
        );
    }

    /**
     * Simpan / Perbarui Group Pertanyaan
     */
    public static function observasiAwalGroupStore(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));

        if (!CSRF::validate(false)) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            $_SESSION['flash_error'] = 'Token CSRF tidak valid.';
            Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
            return;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $namaGroup = trim($_POST['nama_group'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $urutan = (int)($_POST['urutan'] ?? 1);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($namaGroup)) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Nama group pertanyaan wajib diisi.'], 400);
            $_SESSION['flash_error'] = 'Nama group pertanyaan wajib diisi.';
            Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
            return;
        }

        $savedId = SpmbModel::saveObservasiGroup([
            'jenjang'    => $jenjang,
            'nama_group' => $namaGroup,
            'deskripsi'  => $deskripsi,
            'urutan'     => $urutan,
            'is_active'  => $isActive
        ], $id);

        $msg = $id ? 'Group pertanyaan berhasil diperbarui.' : 'Group pertanyaan baru berhasil ditambahkan.';
        if ($isAjax) {
            Response::json(['success' => true, 'message' => $msg, 'id' => $savedId]);
        }
        $_SESSION['flash_success'] = $msg;
        Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
    }

    /**
     * Hapus Group Pertanyaan
     */
    public static function observasiAwalGroupDelete(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));

        if (!CSRF::validate(false)) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            $_SESSION['flash_error'] = 'Token CSRF tidak valid.';
            Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
            return;
        }

        SpmbModel::deleteObservasiGroup($id);

        if ($isAjax) {
            Response::json(['success' => true, 'message' => 'Group pertanyaan dan butir pertanyaan di dalamnya berhasil dihapus.']);
        }
        $_SESSION['flash_success'] = 'Group pertanyaan berhasil dihapus.';
        Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
    }

    /**
     * Simpan / Perbarui Butir Pertanyaan
     */
    public static function observasiAwalPertanyaanStore(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));

        if (!CSRF::validate(false)) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            $_SESSION['flash_error'] = 'Token CSRF tidak valid.';
            Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
            return;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $groupId = (int)($_POST['group_id'] ?? 0);
        $pertanyaan = trim($_POST['pertanyaan'] ?? '');
        $indikator = trim($_POST['indikator'] ?? '');
        $urutan = (int)($_POST['urutan'] ?? 1);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($groupId <= 0 || empty($pertanyaan)) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Pilih group dan isi teks pertanyaan dengan benar.'], 400);
            $_SESSION['flash_error'] = 'Pilih group dan isi teks pertanyaan dengan benar.';
            Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
            return;
        }

        // Susun opsi jawaban kustom dari array form
        $opsi = [];
        $opsiLabels = $_POST['opsi_label'] ?? [];
        $opsiSkors  = $_POST['opsi_skor'] ?? [];

        if (is_array($opsiLabels)) {
            foreach ($opsiLabels as $idx => $label) {
                $trimmed = trim($label);
                if ($trimmed !== '') {
                    $skor = isset($opsiSkors[$idx]) ? (int)$opsiSkors[$idx] : 0;
                    $opsi[] = [
                        'label' => $trimmed,
                        'skor'  => $skor
                    ];
                }
            }
        }

        // Jika opsi kosong, berikan opsi standar 4 skala
        if (empty($opsi)) {
            $opsi = [
                ['label' => 'Sangat Sesuai', 'skor' => 4],
                ['label' => 'Sesuai', 'skor' => 3],
                ['label' => 'Cukup Sesuai', 'skor' => 2],
                ['label' => 'Kurang / Tidak Sesuai', 'skor' => 1],
            ];
        }

        $savedId = SpmbModel::saveObservasiPertanyaan([
            'group_id'     => $groupId,
            'jenjang'      => $jenjang,
            'pertanyaan'   => $pertanyaan,
            'indikator'    => $indikator,
            'tipe_jawaban' => 'skala',
            'opsi_jawaban' => $opsi,
            'urutan'       => $urutan,
            'is_active'    => $isActive
        ], $id);

        $msg = $id ? 'Butir pertanyaan berhasil diperbarui.' : 'Butir pertanyaan baru berhasil ditambahkan.';
        if ($isAjax) {
            Response::json(['success' => true, 'message' => $msg, 'id' => $savedId]);
        }
        $_SESSION['flash_success'] = $msg;
        Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
    }

    /**
     * Hapus Butir Pertanyaan
     */
    public static function observasiAwalPertanyaanDelete(int $id): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $jenjang = strtoupper(trim($_POST['jenjang'] ?? 'SD'));

        if (!CSRF::validate(false)) {
            if ($isAjax) Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            $_SESSION['flash_error'] = 'Token CSRF tidak valid.';
            Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
            return;
        }

        SpmbModel::deleteObservasiPertanyaan($id);

        if ($isAjax) {
            Response::json(['success' => true, 'message' => 'Butir pertanyaan berhasil dihapus.']);
        }
        $_SESSION['flash_success'] = 'Butir pertanyaan berhasil dihapus.';
        Response::redirect(url('kelola-spmb/observasi-awal/pengaturan?jenjang=' . $jenjang));
    }

    /**
     * Ambil Detail Jawaban Observasi Calon Siswa (AJAX)
     */
    public static function observasiAwalDetail(int $id): void
    {
        $detail = SpmbModel::getObservasiDetail($id);
        if (!$detail) {
            Response::json(['success' => false, 'message' => 'Data observasi tidak ditemukan.'], 404);
            return;
        }

        Response::json([
            'success' => true,
            'data'    => $detail
        ]);
    }

    /**
     * Kirim Tautan Observasi Awal ke WhatsApp Orang Tua (Satuan)
     */
    public static function observasiAwalSendWaSingle(int $id = 0): void
    {
        if (!CSRF::validate(false)) {
            Response::json(['success' => false, 'message' => 'Token CSRF tidak valid.'], 403);
            return;
        }

        $pendaftarId = $id > 0 ? $id : (int)($_POST['pendaftar_id'] ?? 0);
        if ($pendaftarId <= 0) {
            Response::json(['success' => false, 'message' => 'ID calon siswa tidak valid.'], 400);
            return;
        }

        $customTemplate = !empty($_POST['pesan_custom']) ? trim($_POST['pesan_custom']) : null;
        $res = SpmbModel::sendWaLinkObservasi($pendaftarId, $customTemplate);

        $httpCode = !empty($res['success']) ? 200 : 400;
        Response::json($res, $httpCode);
    }

    /**
     * Dapatkan Data Siswa Penerima Broadcast Link Observasi (AJAX)
     */
    public static function observasiAwalBroadcastData(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $filters = [
            'group_id'         => $activeGroup['id'] ?? null,
            'jenjang'          => trim($_GET['jenjang'] ?? ''),
            'status_observasi' => trim($_GET['status_observasi'] ?? 'belum'),
        ];

        $items = SpmbModel::getDaftarHasilObservasiAwal($filters, 1000, 0);

        Response::json([
            'success'         => true,
            'total'           => count($items),
            'items'           => $items,
            'defaultTemplate' => SpmbModel::getDefaultWaObservasiTemplate()
        ]);
    }

    /**
     * Export Rekap Hasil Observasi Awal ke CSV / Excel
     */
    public static function observasiAwalExport(): void
    {
        $activeGroup = SpmbModel::getActiveGroup();
        $groupId = $activeGroup['id'] ?? null;

        $filters = [
            'group_id'         => $groupId,
            'jenjang'          => trim($_GET['jenjang'] ?? ''),
            'status_observasi' => trim($_GET['status_observasi'] ?? ''),
            'search'           => trim($_GET['search'] ?? '')
        ];

        $items = SpmbModel::getDaftarHasilObservasiAwal($filters, 10000, 0);
        $filename = 'Rekap_Observasi_Awal_' . date('Ymd_His') . '.csv';

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'No',
            'No. Pendaftaran',
            'Nama Lengkap',
            'Unit / Jenjang',
            'Jenis Pendaftaran',
            'Status Observasi',
            'Total Skor',
            'Skor Maksimal',
            'Persentase',
            'Nama Pengisi',
            'Hubungan Pengisi',
            'No. WA Pengisi',
            'Tanggal Observasi',
            'Tautan Kuesioner Observasi'
        ]);

        $no = 1;
        foreach ($items as $it) {
            $isSudah = ($it['status_observasi'] === 'Sudah Mengisi');
            fputcsv($out, [
                $no++,
                $it['nomor_pendaftaran'],
                $it['nama_lengkap'],
                $it['jenjang'],
                $it['jenis_pendaftaran'],
                $isSudah ? 'Sudah Mengisi' : 'Belum Mengisi',
                $isSudah ? $it['total_skor'] : '-',
                $isSudah ? $it['skor_maksimal'] : '-',
                $isSudah ? ($it['persentase'] . '%') : '-',
                $isSudah ? ($it['nama_pengisi'] ?: '-') : '-',
                $isSudah ? ($it['hubungan_pengisi'] ?: '-') : '-',
                $it['target_phone'] ?: '-',
                $it['submitted_at'] ? date('d/m/Y H:i', strtotime($it['submitted_at'])) : '-',
                $it['public_url']
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Cetak Lembar Observasi Awal Calon Siswa (A4 Print / PDF Preview)
     */
    public static function observasiAwalCetak(int $id): void
    {
        $detail = SpmbModel::getObservasiDetail($id);
        if (!$detail) {
            http_response_code(404);
            die("Data observasi awal tidak ditemukan.");
        }

        $pendaftar = $detail['pendaftar'];
        $respon = $detail['respon'];
        $groupBreakdown = $detail['group_breakdown'];
        $answers = $detail['answers'];

        include MODULES_PATH . '/kelola-spmb/views/observasi_awal/cetak.php';
        exit;
    }

    /**
     * Formulir Publik Observasi Awal yang diisi oleh Orang Tua / Wali
     */
    public static function observasiAwalPublicForm(string $token): void
    {
        $token = trim($token);
        $pendaftar = SpmbModel::getPendaftarByObservasiToken($token);

        if (!$pendaftar) {
            http_response_code(404);
            include MODULES_PATH . '/kelola-spmb/views/biodata_invalid.php';
            exit;
        }

        $jenjang = $pendaftar['jenjang'] ?? 'SD';
        $structure = SpmbModel::getObservasiFormStructure($jenjang);
        $detail = SpmbModel::getObservasiDetail($pendaftar['id']);

        $sudahMengisi = ($pendaftar['status_observasi'] === 'Sudah Mengisi' && !empty($detail['respon']));

        include MODULES_PATH . '/kelola-spmb/views/observasi_awal/public_form.php';
        exit;
    }

    /**
     * Submit Formulir Publik Observasi Awal oleh Orang Tua / Wali
     */
    public static function observasiAwalPublicSubmit(string $token): void
    {
        $token = trim($token);
        $pendaftar = SpmbModel::getPendaftarByObservasiToken($token);

        if (!$pendaftar) {
            http_response_code(404);
            Response::json(['success' => false, 'message' => 'Data pendaftar tidak valid atau token kedaluwarsa.'], 404);
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $res = SpmbModel::submitObservasiRespon($pendaftar['id'], $_POST, $ip, $ua);

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            Response::json($res, $res['success'] ? 200 : 400);
            return;
        }

        if ($res['success']) {
            $_SESSION['flash_success'] = $res['message'];
            Response::redirect(url('spmb/observasi/' . $token . '?sukses=1'));
        } else {
            $_SESSION['flash_error'] = $res['message'];
            Response::redirect(url('spmb/observasi/' . $token));
        }
    }
}


