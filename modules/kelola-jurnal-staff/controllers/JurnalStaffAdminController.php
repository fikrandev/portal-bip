<?php
/**
 * JurnalStaffAdminController
 * Controller Modul Kelola Jurnal Staff (Support System)
 * Mengelola 4 Fitur Utama:
 * 1. Dashboard: Statistik, Tren 7 Hari, Rekap Per Jabatan, Feed Terbaru
 * 2. Data Jurnal: Tabel Rekapitulasi Jurnal Staff, Filter Periode/Jabatan, Input/Edit/Hapus/Detail
 * 3. Pengaturan Jam: Manajemen Slot Jam Kerja (07.00-08.00 Tilawah, 08.00-09.00 Aktifitas, dll)
 * 4. Pengaturan Tugas: Daftar Tugas per Jabatan Referensi (master_jabatan), misal Divisi IT tambah tugas
 */

require_once MODULES_PATH . '/kelola-jurnal-staff/models/JurnalStaffModel.php';

class JurnalStaffAdminController
{
    /**
     * Halaman Utama Modul (4 Tab Terpadu)
     */
    public static function index(): void
    {
        JurnalStaffModel::ensureTablesExist();

        // 1. Parameter Tab Aktif
        $activeTab = trim($_GET['tab'] ?? 'dashboard');
        if (!in_array($activeTab, ['dashboard', 'data', 'jam', 'tugas'], true)) {
            $activeTab = 'dashboard';
        }

        // Pengaturan Periode Rekap
        $periodeConfig = JurnalStaffModel::getPengaturanPeriode();

        // 2. Filter Global & Tab 2 (Data Jurnal)
        $search    = trim($_GET['search'] ?? '');
        $startDate = trim($_GET['start_date'] ?? '');
        $endDate   = trim($_GET['end_date'] ?? '');
        $jabatan   = trim($_GET['jabatan'] ?? '');
        $pegawaiId = !empty($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : null;
        $status    = trim($_GET['status'] ?? '');
        $page      = max(1, (int)($_GET['page'] ?? 1));

        // Rentang periode efektif
        $filterStartDate = $startDate !== '' ? $startDate : ($periodeConfig['periode_awal'] ?: date('Y-m-01'));
        $filterEndDate   = $endDate !== '' ? $endDate : ($periodeConfig['periode_akhir'] ?: date('Y-m-t'));
        if (strtotime($filterStartDate) > strtotime($filterEndDate)) {
            $tmp = $filterStartDate;
            $filterStartDate = $filterEndDate;
            $filterEndDate = $tmp;
        }

        // 3. Tab 1: Data Dashboard
        $dashboardStats = JurnalStaffModel::getDashboardStats($filterStartDate, $filterEndDate);

        // 4. Tab 2: Data Jurnal List
        $jurnalFilters = [
            'search'     => $search,
            'start_date' => $filterStartDate,
            'end_date'   => $filterEndDate,
            'jabatan'    => $jabatan,
            'pegawai_id' => $pegawaiId,
            'status'     => $status,
        ];
        $jurnalData = JurnalStaffModel::getJurnalList($jurnalFilters, $page, 20);

        // 5. Tab 3: Pengaturan Jam List
        $jamList = JurnalStaffModel::getJamList(false);

        // 6. Tab 4: Pengaturan Tugas
        $masterJabatanList = JurnalStaffModel::getMasterJabatanList();
        $tugasCounts = JurnalStaffModel::getTugasCountPerJabatan();

        // Jabatan aktif yang sedang dipilih di tab tugas
        $selectedJabatanId = !empty($_GET['jabatan_id']) ? (int)$_GET['jabatan_id'] : null;
        if (!$selectedJabatanId && !empty($masterJabatanList)) {
            // Default pilih divisi IT atau jabatan pertama
            foreach ($masterJabatanList as $mj) {
                if (stripos($mj['nama'], 'it') !== false || stripos($mj['nama'], 'media') !== false) {
                    $selectedJabatanId = (int)$mj['id'];
                    break;
                }
            }
            if (!$selectedJabatanId) {
                $selectedJabatanId = (int)$masterJabatanList[0]['id'];
            }
        }

        $selectedJabatan = $selectedJabatanId ? JurnalStaffModel::getJabatanById($selectedJabatanId) : null;
        $tugasList = $selectedJabatanId ? JurnalStaffModel::getTugasByJabatan($selectedJabatanId, false) : [];

        // Data referensi untuk dropdown modal
        $staffList = JurnalStaffModel::getStaffList();
        $activeJamOptions = JurnalStaffModel::getJamList(true);

        $pageTitle = 'Kelola Jurnal Staff (Support System)';
        $breadcrumbs = [
            ['label' => 'Kepegawaian', 'url' => url('kelola-pegawai')],
            ['label' => 'Kelola Jurnal Staff']
        ];
        $customSidebar = MODULES_PATH . '/kelola-jurnal-staff/views/sidebar.php';

        ob_start();
        include MODULES_PATH . '/kelola-jurnal-staff/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Cetak / Export Rekapitulasi Jurnal Staff
     */
    public static function cetak(): void
    {
        JurnalStaffModel::ensureTablesExist();

        $search    = trim($_GET['search'] ?? '');
        $startDate = trim($_GET['start_date'] ?? date('Y-m-01'));
        $endDate   = trim($_GET['end_date'] ?? date('Y-m-t'));
        $jabatan   = trim($_GET['jabatan'] ?? '');
        $pegawaiId = !empty($_GET['pegawai_id']) ? (int)$_GET['pegawai_id'] : null;
        $status    = trim($_GET['status'] ?? '');

        $filters = [
            'search'     => $search,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'jabatan'    => $jabatan,
            'pegawai_id' => $pegawaiId,
            'status'     => $status,
        ];

        $jurnalList = JurnalStaffModel::getJurnalForExport($filters);
        $totalSelesai = count(array_filter($jurnalList, fn($j) => $j['status'] === 'selesai'));

        include MODULES_PATH . '/kelola-jurnal-staff/views/cetak.php';
    }

    /**
     * Simpan Pengaturan Periode Rekap
     */
    public static function savePengaturanPeriode(): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        $awal = trim($_POST['periode_awal'] ?? '');
        $akhir = trim($_POST['periode_akhir'] ?? '');
        $keterangan = trim($_POST['keterangan'] ?? '');
        $returnTab = trim($_POST['return_tab'] ?? 'data');

        if ($awal === '' || $akhir === '') {
            Response::redirect(url('kelola-jurnal-staff?tab=' . $returnTab), 'Tanggal awal dan akhir periode wajib diisi.', 'error');
            return;
        }

        try {
            JurnalStaffModel::savePengaturanPeriode($awal, $akhir, $keterangan);
            Response::redirect(
                url('kelola-jurnal-staff?tab=' . $returnTab),
                "Periode rekap berhasil disimpan: " . date('d M Y', strtotime($awal)) . " s/d " . date('d M Y', strtotime($akhir)),
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=' . $returnTab), $e->getMessage(), 'error');
        }
    }

    /**
     * =========================================================================
     * ACTIONS DATA JURNAL
     * =========================================================================
     */

    public static function storeJurnal(): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            $file = isset($_FILES['foto_bukti']) ? $_FILES['foto_bukti'] : null;
            JurnalStaffModel::storeJurnal($_POST, $file);
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Catatan jurnal staff berhasil ditambahkan.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Gagal menyimpan: ' . $e->getMessage(), 'error');
        }
    }

    public static function updateJurnal(int $id): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            $file = isset($_FILES['foto_bukti']) ? $_FILES['foto_bukti'] : null;
            JurnalStaffModel::updateJurnal($id, $_POST, $file);
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Catatan jurnal staff berhasil diperbarui.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Gagal memperbarui: ' . $e->getMessage(), 'error');
        }
    }

    public static function deleteJurnal(int $id): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::deleteJurnal($id);
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Catatan jurnal staff berhasil dihapus.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Gagal menghapus: ' . $e->getMessage(), 'error');
        }
    }

    public static function updateStatusJurnal(int $id): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        $status = trim($_POST['status'] ?? 'selesai');
        try {
            JurnalStaffModel::updateStatusJurnal($id, $status);
            Response::redirect(url('kelola-jurnal-staff?tab=data'), 'Status jurnal staff berhasil diperbarui.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=data'), $e->getMessage(), 'error');
        }
    }

    /**
     * =========================================================================
     * ACTIONS PENGATURAN JAM
     * =========================================================================
     */

    public static function storeJam(): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::storeJam($_POST);
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Pengaturan jam kerja berhasil ditambahkan.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Gagal menambahkan jam: ' . $e->getMessage(), 'error');
        }
    }

    public static function updateJam(int $id): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::updateJam($id, $_POST);
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Pengaturan jam kerja berhasil diperbarui.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Gagal memperbarui jam: ' . $e->getMessage(), 'error');
        }
    }

    public static function deleteJam(int $id): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::deleteJam($id);
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Pengaturan jam kerja berhasil dihapus.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Gagal menghapus jam: ' . $e->getMessage(), 'error');
        }
    }

    public static function toggleJam(int $id): void
    {
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::toggleJam($id);
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), 'Status aktif jam kerja berhasil diubah.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=jam'), $e->getMessage(), 'error');
        }
    }

    /**
     * =========================================================================
     * ACTIONS PENGATURAN TUGAS (JOB DESK JABATAN)
     * =========================================================================
     */

    public static function storeTugas(): void
    {
        $jabatanId = (int)($_POST['jabatan_id'] ?? 0);
        $returnUrl = url('kelola-jurnal-staff?tab=tugas' . ($jabatanId ? '&jabatan_id=' . $jabatanId : ''));

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect($returnUrl, 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::storeTugas($_POST);
            Response::redirect($returnUrl, 'Tugas jabatan baru berhasil ditambahkan.', 'success');
        } catch (Throwable $e) {
            Response::redirect($returnUrl, 'Gagal menambahkan tugas: ' . $e->getMessage(), 'error');
        }
    }

    public static function updateTugas(int $id): void
    {
        $jabatanId = (int)($_POST['jabatan_id'] ?? 0);
        $returnUrl = url('kelola-jurnal-staff?tab=tugas' . ($jabatanId ? '&jabatan_id=' . $jabatanId : ''));

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect($returnUrl, 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::updateTugas($id, $_POST);
            Response::redirect($returnUrl, 'Tugas jabatan berhasil diperbarui.', 'success');
        } catch (Throwable $e) {
            Response::redirect($returnUrl, 'Gagal memperbarui tugas: ' . $e->getMessage(), 'error');
        }
    }

    public static function deleteTugas(int $id): void
    {
        $jabatanId = (int)($_POST['jabatan_id'] ?? 0);
        $returnUrl = url('kelola-jurnal-staff?tab=tugas' . ($jabatanId ? '&jabatan_id=' . $jabatanId : ''));

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect($returnUrl, 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::deleteTugas($id);
            Response::redirect($returnUrl, 'Tugas jabatan berhasil dihapus.', 'success');
        } catch (Throwable $e) {
            Response::redirect($returnUrl, 'Gagal menghapus tugas: ' . $e->getMessage(), 'error');
        }
    }

    public static function toggleTugas(int $id): void
    {
        $jabatanId = (int)($_POST['jabatan_id'] ?? 0);
        $returnUrl = url('kelola-jurnal-staff?tab=tugas' . ($jabatanId ? '&jabatan_id=' . $jabatanId : ''));

        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Response::redirect($returnUrl, 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffModel::toggleTugas($id);
            Response::redirect($returnUrl, 'Status aktif tugas berhasil diubah.', 'success');
        } catch (Throwable $e) {
            Response::redirect($returnUrl, $e->getMessage(), 'error');
        }
    }

    /**
     * =========================================================================
     * API ENDPOINTS (JSON)
     * =========================================================================
     */

    public static function apiTugasByJabatan(int $jabatanId): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $tugas = JurnalStaffModel::getTugasByJabatan($jabatanId, true);
            echo json_encode([
                'success' => true,
                'data'    => $tugas
            ]);
        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public static function apiDetailJurnal(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $jurnal = JurnalStaffModel::getJurnalById($id);
            if (!$jurnal) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
                exit;
            }
            echo json_encode([
                'success' => true,
                'data'    => $jurnal
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
