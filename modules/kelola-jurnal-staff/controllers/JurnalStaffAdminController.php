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
require_once MODULES_PATH . '/kelola-jurnal-staff/services/JurnalStaffReminderService.php';

class JurnalStaffAdminController
{
    /**
     * Halaman Utama Modul (5 Tab Terpadu)
     */
    public static function index(): void
    {
        JurnalStaffModel::ensureTablesExist();
        JurnalStaffReminderService::ensureTablesExist();
        JurnalStaffReminderService::syncWaServerConfig();

        // 1. Parameter Tab Aktif
        $activeTab = trim($_GET['tab'] ?? 'dashboard');
        if (!in_array($activeTab, ['dashboard', 'data', 'jam', 'tugas', 'pengingat'], true)) {
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

        // 4. Tab 2: Data Jurnal List, Rekap Persentase & Matrix Ceklis Harian
        $dataSubTab = trim($_GET['subtab'] ?? 'data');
        if (!in_array($dataSubTab, ['data', 'rekap', 'matrix'], true)) {
            $dataSubTab = 'data';
        }

        $jurnalFilters = [
            'search'     => $search,
            'start_date' => $filterStartDate,
            'end_date'   => $filterEndDate,
            'jabatan'    => $jabatan,
            'pegawai_id' => $pegawaiId,
            'status'     => $status,
        ];
        $jurnalData = JurnalStaffModel::getJurnalList($jurnalFilters, $page, 20);
        $rekapMatrixData = JurnalStaffModel::getRekapAndMatrixData($jurnalFilters);

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

        // 7. Tab 5: Pengingat WA Otomatis Jurnal Staff
        // Jalankan auto-dispatch jika ada slot jadwal hari ini yang sudah tiba namun belum tereksekusi
        JurnalStaffReminderService::autoDispatchDueSlotIfNeeded(true);

        $reminderSettings = JurnalStaffReminderService::getSettings();
        $previewSlot = trim($_GET['preview_slot'] ?? '');
        if ($previewSlot === '') {
            $dueSlotNow = JurnalStaffReminderService::getCurrentDueSlot($reminderSettings);
            $schedules = $reminderSettings['schedules_array'];
            $previewSlot = $dueSlotNow ?? ($schedules[0] ?? '09:00');
        }
        $reminderEval = JurnalStaffReminderService::evaluateAllStaffForDate(date('Y-m-d'), $previewSlot, $reminderSettings);
        $reminderLogs = JurnalStaffReminderService::getRecentLogs(30);
        $waConnection = WaGateway::checkConnection();

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
     * Cetak / Export Rekapitulasi Jurnal Staff (Mendukung mode: data | rekap | matrix | lengkap)
     */
    public static function cetak(): void
    {
        JurnalStaffModel::ensureTablesExist();

        $mode      = trim($_GET['mode'] ?? 'rekap');
        if (!in_array($mode, ['data', 'rekap', 'matrix', 'lengkap'], true)) {
            $mode = 'rekap';
        }

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
        $rekapMatrixData = JurnalStaffModel::getRekapAndMatrixData($filters);

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
            $tanggalInput = !empty($_POST['tanggal']) ? trim((string)$_POST['tanggal']) : date('Y-m-d');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalInput)) {
                require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
                $liburInfo = AbsenPegawaiModel::getLiburInfoForDate($tanggalInput);
                if ($liburInfo !== null) {
                    throw new Exception('Tanggal tersebut adalah hari libur (' . $liburInfo['nama_libur'] . '). Tidak ada pengisian jurnal staff pada hari Minggu atau hari libur.');
                }
            }

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

    /**
     * =========================================================================
     * PENGINGAT WA OTOMATIS JURNAL STAFF (CRON + MANUAL)
     * =========================================================================
     */

    /**
     * Endpoint Publik Token-Protected untuk Cron / wa-server Auto Scheduler
     * URL: /cron/jurnal-staff-reminder?key=SECRET&slot=09:00
     */
    public static function cronReminder(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        JurnalStaffReminderService::ensureTablesExist();
        $settings = JurnalStaffReminderService::getSettings();

        $submittedKey = trim((string)($_GET['key'] ?? ($_POST['key'] ?? ($_SERVER['HTTP_X_CRON_KEY'] ?? ''))));
        $expectedKey = trim((string)($settings['jurnal_staff_wa_cron_key'] ?? ''));

        if ($expectedKey === '' || $submittedKey === '' || !hash_equals($expectedKey, $submittedKey)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Akses ditolak: Cron key tidak valid.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $slot = trim((string)($_GET['slot'] ?? ($_POST['slot'] ?? '')));
        $date = trim((string)($_GET['date'] ?? ($_POST['date'] ?? '')));
        $source = trim((string)($_GET['source'] ?? ($_POST['source'] ?? 'cron')));
        $force = !empty($_GET['force']) || !empty($_POST['force']);
        $dryRun = !empty($_GET['dry_run']) || !empty($_POST['dry_run']);

        $result = JurnalStaffReminderService::runScheduledDispatch(
            $slot !== '' ? $slot : null,
            $date !== '' ? $date : null,
            $source !== '' ? $source : 'cron',
            $force,
            $dryRun
        );

        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Simpan Pengaturan Pengingat WA Jurnal Staff
     */
    public static function savePengingatSettings(): void
    {
        if (!CSRF::validate()) {
            Response::redirect(url('kelola-jurnal-staff?tab=pengingat'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffReminderService::saveSettings($_POST);
            Response::redirect(
                url('kelola-jurnal-staff?tab=pengingat'),
                'Pengaturan pengingat WA otomatis Jurnal Staff berhasil disimpan & disinkronkan.',
                'success'
            );
        } catch (Throwable $e) {
            Response::redirect(
                url('kelola-jurnal-staff?tab=pengingat'),
                'Gagal menyimpan pengaturan: ' . $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Trigger Kirim Pengingat Sekarang (Bulk untuk seluruh staff yang belum lengkap)
     */
    public static function runPengingatNow(): void
    {
        $isAjax = !empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (!CSRF::check()) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(419);
                echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.']);
                exit;
            }
            Response::redirect(url('kelola-jurnal-staff?tab=pengingat'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        $slot = trim((string)($_POST['slot'] ?? ''));
        $force = !empty($_POST['force']);

        try {
            $res = JurnalStaffReminderService::runScheduledDispatch(
                $slot !== '' ? $slot : null,
                date('Y-m-d'),
                'manual',
                $force,
                false
            );

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }

            $type = !empty($res['success']) ? 'success' : 'error';
            Response::redirect(url('kelola-jurnal-staff?tab=pengingat&preview_slot=' . urlencode($slot)), $res['message'] ?? 'Proses selesai.', $type);
        } catch (Throwable $e) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Gagal menjalankan pengingat: ' . $e->getMessage()]);
                exit;
            }
            Response::redirect(url('kelola-jurnal-staff?tab=pengingat'), 'Gagal: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Kirim Pengingat Manual untuk 1 Orang Staff (AJAX)
     */
    public static function sendSinglePengingat(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!CSRF::check()) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Silakan refresh halaman.']);
            exit;
        }

        $pegawaiId = (int)($_POST['pegawai_id'] ?? 0);
        $slot = trim((string)($_POST['slot'] ?? ''));

        if ($pegawaiId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID pegawai tidak valid.']);
            exit;
        }

        try {
            $res = JurnalStaffReminderService::sendSingleStaffReminder($pegawaiId, $slot !== '' ? $slot : null, date('Y-m-d'));
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Uji Coba Kirim Pesan Pengingat (Kondisi Belum Isi / Sebagian)
     */
    public static function testPengingatWa(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!CSRF::check()) {
            http_response_code(419);
            echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Silakan refresh halaman.']);
            exit;
        }

        $targetPhone = trim((string)($_POST['target_phone'] ?? ''));
        $kategori = trim((string)($_POST['kategori'] ?? 'belum_isi'));
        $sampleName = trim((string)($_POST['sample_name'] ?? 'Rekan Staff'));
        $sampleJabatan = trim((string)($_POST['sample_jabatan'] ?? 'Staff IT & Media'));

        if ($targetPhone === '') {
            echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp uji coba wajib diisi.']);
            exit;
        }

        try {
            $res = JurnalStaffReminderService::sendTestPreview($targetPhone, $kategori, $sampleName, $sampleJabatan);
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Simpan Daftar Staff Penerima Notifikasi WA Jurnal Staff (Checklist)
     */
    public static function savePengingatRecipients(): void
    {
        $isAjax = !empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if (!CSRF::check()) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(419);
                echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Silakan muat ulang halaman.']);
                exit;
            }
            Response::redirect(url('kelola-jurnal-staff?tab=pengingat'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        $rawEnabled = $_POST['enabled_ids'] ?? [];
        if (!is_array($rawEnabled)) {
            $rawEnabled = [$rawEnabled];
        }

        try {
            $res = JurnalStaffReminderService::saveRecipientPreferences($rawEnabled);
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            Response::redirect(
                url('kelola-jurnal-staff?tab=pengingat'),
                $res['message'] ?? 'Daftar penerima notifikasi WA berhasil disimpan.',
                'success'
            );
        } catch (Throwable $e) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Gagal menyimpan daftar penerima: ' . $e->getMessage()]);
                exit;
            }
            Response::redirect(
                url('kelola-jurnal-staff?tab=pengingat'),
                'Gagal menyimpan daftar penerima: ' . $e->getMessage(),
                'error'
            );
        }
    }

    /**
     * Buat Ulang Cron Key Endpoint Pengingat WA
     */
    public static function regenerateCronKey(): void
    {
        if (!CSRF::validate()) {
            Response::redirect(url('kelola-jurnal-staff?tab=pengingat'), 'Token keamanan tidak valid.', 'error');
            return;
        }

        try {
            JurnalStaffReminderService::regenerateCronKey();
            Response::redirect(url('kelola-jurnal-staff?tab=pengingat'), 'Cron secret key baru berhasil dibuat & disinkronkan.', 'success');
        } catch (Throwable $e) {
            Response::redirect(url('kelola-jurnal-staff?tab=pengingat'), 'Gagal membuat ulang key: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * API Live Preview Evaluasi Pengingat WA per Slot & Tanggal
     */
    public static function apiPreviewPengingat(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $slot = trim((string)($_GET['slot'] ?? '17:00'));
        $date = trim((string)($_GET['date'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        try {
            $eval = JurnalStaffReminderService::evaluateAllStaffForDate($date, $slot);
            echo json_encode([
                'success' => true,
                'data'    => $eval,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Endpoint Heartbeat Auto-Scheduler (mengecek & mengirim otomatis slot jadwal yang sudah tiba)
     */
    public static function apiAutoCheckPengingat(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $res = JurnalStaffReminderService::autoDispatchDueSlotIfNeeded(true);
            echo json_encode([
                'success'   => true,
                'triggered' => ($res !== null),
                'result'    => $res,
                'time_wita' => date('H:i:s'),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
