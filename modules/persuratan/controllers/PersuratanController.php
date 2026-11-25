<?php
/**
 * PersuratanController - Controller Modul Persuratan Yayasan & Persuratan Sekolah
 * Portal BIP
 */

require_once BASE_PATH . '/modules/persuratan/models/PersuratanModel.php';

class PersuratanController
{
    public static function yayasanIndex(): void
    {
        self::renderModule('yayasan');
    }

    public static function sekolahIndex(): void
    {
        self::renderModule('sekolah');
    }

    private static function detectScopeFromRequest(): string
    {
        $scope = strtolower(trim((string)($_POST['scope'] ?? $_GET['scope'] ?? '')));
        if ($scope === 'yayasan' || $scope === 'sekolah') {
            return $scope;
        }
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        return strpos($uri, 'persuratan-yayasan') !== false ? 'yayasan' : 'sekolah';
    }

    private static function baseUrlForScope(string $scope): string
    {
        return $scope === 'yayasan' ? 'persuratan-yayasan' : 'persuratan-sekolah';
    }

    private static function renderModule(string $scope): void
    {
        PersuratanModel::ensureSchema();

        $activeTab = trim((string)($_GET['tab'] ?? 'dashboard'));
        $allowedTabs = ['dashboard', 'masuk', 'keluar', 'data', 'pengaturan'];
        if (!in_array($activeTab, $allowedTabs, true)) {
            $activeTab = 'dashboard';
        }

        $units = PersuratanModel::getUnitsForScope($scope);
        $baseRoute = self::baseUrlForScope($scope);
        $moduleLabel = $scope === 'yayasan' ? 'Persuratan Yayasan' : 'Persuratan Sekolah';

        $filters = [
            'arah'       => $activeTab === 'masuk' ? 'masuk' : ($activeTab === 'keluar' ? 'keluar' : trim((string)($_GET['arah'] ?? ''))),
            'unit_nama'  => trim((string)($_GET['unit_nama'] ?? '')),
            'jenis_id'   => trim((string)($_GET['jenis_id'] ?? '')),
            'start_date' => trim((string)($_GET['start_date'] ?? '')),
            'end_date'   => trim((string)($_GET['end_date'] ?? '')),
            'search'     => trim((string)($_GET['search'] ?? '')),
        ];

        $stats = PersuratanModel::getDashboardStats($scope);
        $suratList = ($activeTab === 'dashboard') ? [] : PersuratanModel::getSuratList($scope, $filters);
        $jenisList = PersuratanModel::getJenisList($scope, false);
        $jenisAktif = PersuratanModel::getJenisList($scope, true);
        $kopList = PersuratanModel::getKopList($scope);
        $activeKopUnit = trim((string)($_GET['kop_unit'] ?? ($units[0] ?? 'Yayasan')));
        if (!in_array($activeKopUnit, $units, true)) {
            $activeKopUnit = $units[0] ?? 'Yayasan';
        }

        $pageTitle = $moduleLabel;
        $breadcrumbs = [
            ['label' => $moduleLabel, 'url' => url($baseRoute)],
            ['label' => match($activeTab) {
                'masuk'      => 'Surat Masuk',
                'keluar'     => 'Surat Keluar',
                'data'       => 'Data Surat',
                'pengaturan' => 'Pengaturan Surat',
                default      => 'Dashboard',
            }]
        ];
        $customSidebar = BASE_PATH . '/modules/persuratan/views/sidebar.php';

        ob_start();
        include BASE_PATH . '/modules/persuratan/views/index.php';
        $content = ob_get_clean();

        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function storeSurat(): void
    {
        CSRF::validate();
        $scope = self::detectScopeFromRequest();
        $returnTab = trim((string)($_POST['return_tab'] ?? 'data'));
        $redirect = url(self::baseUrlForScope($scope) . '?tab=' . urlencode($returnTab));

        try {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (empty(trim((string)($_POST['perihal'] ?? '')))) {
                Response::redirect($redirect, 'Perihal surat wajib diisi.', 'error');
            }
            PersuratanModel::saveSurat($scope, $_POST, $_FILES['file_lampiran'] ?? null, $id);
            Response::redirect($redirect, $id ? 'Data surat berhasil diperbarui.' : 'Data surat berhasil disimpan.', 'success');
        } catch (Throwable $e) {
            Response::redirect($redirect, 'Gagal menyimpan surat: ' . $e->getMessage(), 'error');
        }
    }

    public static function deleteSurat(): void
    {
        CSRF::validate();
        $scope = self::detectScopeFromRequest();
        $returnTab = trim((string)($_POST['return_tab'] ?? 'data'));
        $redirect = url(self::baseUrlForScope($scope) . '?tab=' . urlencode($returnTab));

        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            PersuratanModel::deleteSurat($scope, $id);
            Response::redirect($redirect, 'Data surat berhasil dihapus.', 'success');
        }
        Response::redirect($redirect, 'ID surat tidak valid.', 'error');
    }

    public static function cetakSurat(int $id): void
    {
        $surat = PersuratanModel::getSuratById($id);
        if (!$surat) {
            http_response_code(404);
            echo 'Data surat tidak ditemukan.';
            exit;
        }
        $kop = PersuratanModel::getKopByUnit($surat['scope'], $surat['unit_nama']);
        include BASE_PATH . '/modules/persuratan/views/cetak.php';
    }

    public static function apiNextNomor(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $scope = self::detectScopeFromRequest();
        $unit = trim((string)($_GET['unit_nama'] ?? ($scope === 'yayasan' ? 'Yayasan' : 'SD')));
        $jenisId = !empty($_GET['jenis_id']) ? (int)$_GET['jenis_id'] : null;
        $tanggal = trim((string)($_GET['tanggal'] ?? date('Y-m-d')));

        $gen = PersuratanModel::generateNomorSurat($scope, $unit, $jenisId, $tanggal);
        echo json_encode(['success' => true] + $gen);
        exit;
    }

    public static function storeJenis(): void
    {
        CSRF::validate();
        $scope = self::detectScopeFromRequest();
        $redirect = url(self::baseUrlForScope($scope) . '?tab=pengaturan');

        try {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            if (empty(trim((string)($_POST['kode_surat'] ?? ''))) || empty(trim((string)($_POST['nama_jenis'] ?? '')))) {
                Response::redirect($redirect, 'Kode surat dan nama jenis surat wajib diisi.', 'error');
            }
            PersuratanModel::saveJenis($scope, $_POST, $id);
            Response::redirect($redirect, 'Jenis & kode surat berhasil disimpan.', 'success');
        } catch (Throwable $e) {
            Response::redirect($redirect, 'Gagal menyimpan jenis surat: ' . $e->getMessage(), 'error');
        }
    }

    public static function deleteJenis(): void
    {
        CSRF::validate();
        $scope = self::detectScopeFromRequest();
        $redirect = url(self::baseUrlForScope($scope) . '?tab=pengaturan');
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            PersuratanModel::deleteJenis($scope, $id);
            Response::redirect($redirect, 'Jenis surat berhasil dihapus.', 'success');
        }
        Response::redirect($redirect, 'ID jenis surat tidak valid.', 'error');
    }

    public static function saveKop(): void
    {
        CSRF::validate();
        $scope = self::detectScopeFromRequest();
        $unit = trim((string)($_POST['unit_nama'] ?? ($scope === 'yayasan' ? 'Yayasan' : 'SD')));
        $redirect = url(self::baseUrlForScope($scope) . '?tab=pengaturan&kop_unit=' . urlencode($unit));

        try {
            PersuratanModel::saveKop($scope, $_POST, $_FILES);
            Response::redirect($redirect, 'Pengaturan KOP surat unit ' . $unit . ' berhasil disimpan.', 'success');
        } catch (Throwable $e) {
            Response::redirect($redirect, 'Gagal menyimpan KOP surat: ' . $e->getMessage(), 'error');
        }
    }

    public static function exportCsv(): void
    {
        $scope = self::detectScopeFromRequest();
        $filters = [
            'arah'       => trim((string)($_GET['arah'] ?? '')),
            'unit_nama'  => trim((string)($_GET['unit_nama'] ?? '')),
            'jenis_id'   => trim((string)($_GET['jenis_id'] ?? '')),
            'start_date' => trim((string)($_GET['start_date'] ?? '')),
            'end_date'   => trim((string)($_GET['end_date'] ?? '')),
            'search'     => trim((string)($_GET['search'] ?? '')),
        ];
        $rows = PersuratanModel::getSuratList($scope, $filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Data_Surat_' . ucfirst($scope) . '_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['No', 'Arah', 'Unit', 'Jenis', 'Kode', 'Nomor Surat', 'Tanggal Surat', 'Pengirim', 'Tujuan', 'Perihal', 'Sifat', 'Status']);
        foreach ($rows as $i => $r) {
            fputcsv($out, [
                $i + 1,
                strtoupper($r['arah']),
                $r['unit_nama'],
                $r['nama_jenis'] ?: '-',
                $r['kode_surat'] ?: '-',
                $r['nomor_surat'],
                $r['tanggal_surat'],
                $r['pengirim'] ?: '-',
                $r['tujuan'] ?: '-',
                $r['perihal'],
                $r['sifat_surat'],
                $r['status_surat'],
            ]);
        }
        fclose($out);
        exit;
    }
}
