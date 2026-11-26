<?php
// Only display errors if APP_DEBUG is explicitly enabled, otherwise hide for security
if (getenv('APP_DEBUG') === 'true' || getenv('APP_DEBUG') === '1') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}
/**
 * =====================================================
 * Portal BIP - Front Controller (Entry Point)
 * =====================================================
 * 
 * All requests are routed through this file via .htaccess.
 * Loads configuration, core classes, and dispatches routes.
 */

// ── Define Base Path ─────────────────────────────────
define('BASE_PATH', __DIR__);

// ── Load Configuration ───────────────────────────────
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/session.php';

// ── Load Core Classes ────────────────────────────────
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/RBAC.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Middleware.php';
require_once BASE_PATH . '/core/CSRF.php';
require_once BASE_PATH . '/core/Validator.php';
require_once BASE_PATH . '/core/Response.php';
require_once BASE_PATH . '/core/ExcelHelper.php';
require_once BASE_PATH . '/core/ModalHelper.php';
require_once BASE_PATH . '/core/DropdownHelper.php';
require_once BASE_PATH . '/core/KartuHelper.php';
require_once BASE_PATH . '/core/WaGateway.php';
require_once BASE_PATH . '/core/SignatureCrypto.php';
require_once BASE_PATH . '/core/AuditLog.php';
require_once BASE_PATH . '/core/AutoMigrate.php';

// ── Auto Migrate Database (Pastikan struktur database server selalu mutakhir) ──
AutoMigrate::run();

// ── Global System Settings (Session-Cached 300s) ───
try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        unset($_SESSION['_sys_boot_cache'], $_SESSION['_dash_stats_cache']);
    }
    $now = time();
    if (
        isset($_SESSION['_sys_boot_cache']) &&
        is_array($_SESSION['_sys_boot_cache']) &&
        ($now - ($_SESSION['_sys_boot_cache']['ts'] ?? 0)) < 300
    ) {
        $globalSettings = $_SESSION['_sys_boot_cache']['globalSettings'] ?? [];
        $activeAcademicYear = $_SESSION['_sys_boot_cache']['activeAcademicYear'] ?? null;
    } else {
        $dbSettings = Database::getInstance();
        $sysSettings = $dbSettings->findAll("SELECT setting_key, setting_value FROM settings");
        $globalSettings = [];
        foreach ($sysSettings as $row) {
            $globalSettings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Fetch active academic year
        $activeAcademicYear = $dbSettings->find("SELECT id, nama_tahun FROM tahun_akademik WHERE is_active = 1 LIMIT 1");
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['_sys_boot_cache'] = [
                'ts' => $now,
                'globalSettings' => $globalSettings,
                'activeAcademicYear' => $activeAcademicYear,
            ];
        }
    }
} catch (Exception $e) {
    $globalSettings = [];
    $activeAcademicYear = null;
}

define('SYS_APP_NAME', !empty($globalSettings['app_name']) ? $globalSettings['app_name'] : APP_NAME);
define('SYS_APP_LOGO', $globalSettings['app_logo'] ?? '');
define('SYS_APP_FAVICON', $globalSettings['app_favicon'] ?? '');
define('SYS_TAHUN_AKADEMIK_ID', $activeAcademicYear['id'] ?? null);
define('SYS_TAHUN_AKADEMIK_NAME', $activeAcademicYear['nama_tahun'] ?? 'Belum Diatur');

// ── Security Headers ────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ── Helper Functions ────────────────────────────────

/**
 * Escape output for XSS prevention
 */
function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate full URL
 */
function url(string $path = ''): string {
    return $path !== '' ? BASE_URL . '/' . ltrim($path, '/') : BASE_URL;
}

/**
 * Redirect to path or absolute URL (optionally with flash message)
 */
function redirect(string $path, ?string $message = null, string $type = 'success'): void {
    $targetUrl = (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) ? $path : url($path);
    Response::redirect($targetUrl, $message, $type);
}

/**
 * Generate asset URL
 */
function asset(string $path): string {
    return BASE_URL . '/public/' . ltrim($path, '/');
}

/**
 * Get active Favicon URL with auto cache-busting
 */
function getFaviconUrl(): string {
    if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
        $cand = BASE_PATH . '/' . ltrim(SYS_APP_FAVICON, '/');
        if (file_exists($cand)) {
            return url(ltrim(SYS_APP_FAVICON, '/')) . '?v=' . filemtime($cand);
        }
    }
    if (file_exists(BASE_PATH . '/favicon.ico')) {
        return url('favicon.ico') . '?v=' . filemtime(BASE_PATH . '/favicon.ico');
    }
    if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
        $cand = BASE_PATH . '/' . ltrim(SYS_APP_LOGO, '/');
        if (file_exists($cand)) {
            return url(ltrim(SYS_APP_LOGO, '/')) . '?v=' . filemtime($cand);
        }
    }
    return url('favicon.ico');
}

/**
 * Render standard HTML <link> tags for favicon across all browsers
 */
function renderFaviconTags(): string {
    $favUrl = getFaviconUrl();
    $cleanPath = parse_url($favUrl, PHP_URL_PATH) ?? '';
    $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
    $mime = ($ext === 'png') ? 'image/png' : (($ext === 'svg') ? 'image/svg+xml' : 'image/x-icon');
    $escapedUrl = htmlspecialchars($favUrl, ENT_QUOTES, 'UTF-8');

    $html  = '<link rel="shortcut icon" href="' . $escapedUrl . '">' . "\n";
    $html .= '    <link rel="icon" type="' . $mime . '" href="' . $escapedUrl . '">' . "\n";
    $html .= '    <link rel="apple-touch-icon" href="' . $escapedUrl . '">';
    return $html;
}

/**
 * Get old input value (for form repopulation)
 */
function old(string $field, mixed $default = ''): string {
    return Response::oldInput($field, (string)($default ?? ''));
}

/**
 * Check if there's a validation error for a field
 */
function hasError(string $field): bool {
    $errors = $_SESSION['validation_errors'] ?? [];
    return isset($errors[$field]);
}

/**
 * Get validation error message for a field
 */
function getError(string $field): string {
    $errors = $_SESSION['validation_errors'] ?? [];
    return isset($errors[$field]) ? $errors[$field][0] : '';
}

// ── Initialize Router ───────────────────────────────
// Determine base path from URL (for subdirectory installs like /portal-bip)
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = dirname($scriptName);
if ($basePath === '\\' || $basePath === '/' || $basePath === '.') {
    $basePath = '';
}

$router = new Router($basePath);

// ── Lazy Autoload Controllers ───────────────────────
spl_autoload_register(static function (string $class): void {
    static $controllerMap = [
        'AuthController'            => '/modules/auth/controllers/AuthController.php',
        'DashboardController'       => '/modules/dashboard/controllers/DashboardController.php',
        'UserController'            => '/modules/users/controllers/UserController.php',
        'RoleController'            => '/modules/roles/controllers/RoleController.php',
        'ModuleController'          => '/modules/modules-manager/controllers/ModuleController.php',
        'SiswaController'           => '/modules/kelola-siswa/controllers/SiswaController.php',
        'KelasController'           => '/modules/kelola-kelas/controllers/KelasController.php',
        'PegawaiController'         => '/modules/kelola-pegawai/controllers/PegawaiController.php',
        'SettingsController'        => '/modules/pengaturan-sistem/controllers/SettingsController.php',
        'PortalGuruController'      => '/modules/portal-guru/controllers/PortalGuruController.php',
        'PortalGuruAuthController'  => '/modules/portal-guru/controllers/PortalGuruAuthController.php',
        'PerangkatController'       => '/modules/kelola-perangkat-pembelajaran/controllers/PerangkatController.php',
        'JadwalController'          => '/modules/kelola-perangkat-pembelajaran/controllers/JadwalController.php',
        'NilaiController'           => '/modules/kelola-nilai/controllers/NilaiController.php',
        'RaportController'          => '/modules/kelola-raport/controllers/RaportController.php',
        'AbsenSiswaController'      => '/modules/kelola-absen-siswa/controllers/AbsenSiswaController.php',
        'AbsenPegawaiController'    => '/modules/kelola-absen-pegawai/controllers/AbsenPegawaiController.php',
        'QuranSiswaController'      => '/modules/kelola-quran-siswa/controllers/QuranSiswaController.php',
        'UjianController'           => '/modules/kelola-ujian/controllers/UjianController.php',
        'QuranPegawaiController'    => '/modules/kelola-quran-pegawai/controllers/QuranPegawaiController.php',
        'CutiController'            => '/modules/kelola-cuti/controllers/CutiController.php',
        'KpiController'             => '/modules/kelola-kpi/controllers/KpiController.php',
        'IbadahGuruController'      => '/modules/kelola-ibadah-guru/controllers/IbadahGuruController.php',
        'SpmbController'            => '/modules/kelola-spmb/controllers/SpmbController.php',
        'PelanggaranSiswaController'=> '/modules/kelola-pelanggaran-siswa/controllers/PelanggaranSiswaController.php',
        'IzinGuruController'        => '/modules/kelola-izin-guru/controllers/IzinGuruController.php',
        'BukuAngkatanSiswaController'=> '/modules/kelola-buku-angkatan-siswa/controllers/BukuAngkatanSiswaController.php',
        'SarprasController'         => '/modules/kelola-sarpras/controllers/SarprasController.php',
        'JurnalGuruAdminController' => '/modules/kelola-jurnal-guru/controllers/JurnalGuruAdminController.php',
        'JurnalStaffAdminController' => '/modules/kelola-jurnal-staff/controllers/JurnalStaffAdminController.php',
        'MobileSarprasController'   => '/modules/mobile-sarpras/controllers/MobileSarprasController.php',
        'MobileSarprasAuthController' => '/modules/mobile-sarpras/controllers/MobileSarprasAuthController.php',
        'PersuratanController'      => '/modules/persuratan/controllers/PersuratanController.php',
    ];
    if (isset($controllerMap[$class])) {
        require_once BASE_PATH . $controllerMap[$class];
    }
});

// ── Define Routes ───────────────────────────────────

// -- Auth Routes (Guest only) --
$router->get('/login', [AuthController::class, 'showLogin'], [[Middleware::class, 'guestOnly']]);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// -- Public Routes --
$router->get('/validasi-kartu/{id}', [SiswaController::class, 'validasiKartu']);
$router->get('/spmb/biodata/download-contoh-dapodik', [SpmbController::class, 'downloadContohDapodik']);
$router->get('/spmb/biodata/{token}', [SpmbController::class, 'biodataPublic']);
$router->post('/spmb/biodata/{token}/save-draft', [SpmbController::class, 'biodataSaveDraft']);
$router->post('/spmb/biodata/{token}/upload-berkas', [SpmbController::class, 'biodataUploadSingleBerkas']);
$router->post('/spmb/biodata/{token}/submit', [SpmbController::class, 'biodataSubmit']);
$router->get('/spmb/komitmen/{token}', [SpmbController::class, 'komitmenPublic']);
$router->post('/spmb/komitmen/{token}/submit', [SpmbController::class, 'komitmenSubmit']);
$router->get('/spmb/komitmen/{token}/cetak', [SpmbController::class, 'komitmenCetakPublic']);
$router->get('/spmb/psikolog/{token}', [SpmbController::class, 'screeningPsikologPublicView']);
$router->get('/spmb/psikolog/{token}/download', [SpmbController::class, 'screeningPsikologPublicDownload']);
$router->get('/spmb/psikolog/{token}/raw', [SpmbController::class, 'screeningPsikologPublicRaw']);
$router->get('/spmb/observasi/{token}', [SpmbController::class, 'observasiAwalPublicForm']);
$router->post('/spmb/observasi/{token}/submit', [SpmbController::class, 'observasiAwalPublicSubmit']);
$router->get('/spmb/observasi/{token}/join-group', [SpmbController::class, 'observasiAwalPublicJoinGroup']);
$router->post('/spmb/observasi/{token}/join-group', [SpmbController::class, 'observasiAwalPublicJoinGroup']);

// -- Token-Protected Cron Webhook: Pengingat WA Jurnal Staff (09:00, 13:00, 17:00 WITA) --
$router->any('/cron/jurnal-staff-reminder', [JurnalStaffAdminController::class, 'cronReminder']);

// -- Dashboard (requires auth) --
$router->get('/', function() {
    Response::redirect(url('dashboard'));
}, [[]]);

$router->get('/dashboard', [DashboardController::class, 'index'], [[]]);

// -- Users Management --
$router->get('/users', [UserController::class, 'index'], [Middleware::permissionRequired('users.view')]);
$router->get('/users/create', [UserController::class, 'create'], [Middleware::permissionRequired('users.create')]);
$router->post('/users/store', [UserController::class, 'store'], [Middleware::permissionRequired('users.create')]);
$router->get('/users/edit/{id}', [UserController::class, 'edit'], [Middleware::permissionRequired('users.update')]);
$router->post('/users/update/{id}', [UserController::class, 'update'], [Middleware::permissionRequired('users.update')]);
$router->post('/users/delete/{id}', [UserController::class, 'delete'], [Middleware::permissionRequired('users.delete')]);

// -- Roles Management --
$router->get('/roles', [RoleController::class, 'index'], [Middleware::permissionRequired('roles.view')]);
$router->get('/roles/create', [RoleController::class, 'create'], [Middleware::permissionRequired('roles.create')]);
$router->post('/roles/store', [RoleController::class, 'store'], [Middleware::permissionRequired('roles.create')]);
$router->get('/roles/copy/{id}', [RoleController::class, 'copy'], [Middleware::permissionRequired('roles.create')]);
$router->post('/roles/duplicate/{id}', [RoleController::class, 'duplicate'], [Middleware::permissionRequired('roles.create')]);
$router->get('/roles/edit/{id}', [RoleController::class, 'edit'], [Middleware::permissionRequired('roles.update')]);
$router->post('/roles/update/{id}', [RoleController::class, 'update'], [Middleware::permissionRequired('roles.update')]);
$router->post('/roles/delete/{id}', [RoleController::class, 'delete'], [Middleware::permissionRequired('roles.delete')]);
$router->post('/switch-jenjang', [RoleController::class, 'switchActiveJenjang'], [Middleware::auth()]);
$router->get('/switch-jenjang', [RoleController::class, 'switchActiveJenjang'], [Middleware::auth()]);

// -- Module Manager --
$router->get('/modules-manager', [ModuleController::class, 'index'], [Middleware::permissionRequired('modules.view')]);
$router->get('/modules-manager/create', [ModuleController::class, 'create'], [Middleware::permissionRequired('modules.create')]);
$router->post('/modules-manager/store', [ModuleController::class, 'store'], [Middleware::permissionRequired('modules.create')]);
$router->get('/modules-manager/edit/{id}', [ModuleController::class, 'edit'], [Middleware::permissionRequired('modules.update')]);
$router->post('/modules-manager/update/{id}', [ModuleController::class, 'update'], [Middleware::permissionRequired('modules.update')]);
$router->post('/modules-manager/delete/{id}', [ModuleController::class, 'delete'], [Middleware::permissionRequired('modules.delete')]);

// -- Kelola Siswa --
$router->get('/kelola-siswa/statistik', [SiswaController::class, 'statistik'], [Middleware::permissionRequired('siswa.dashboard')]);
$router->get('/kelola-siswa', [SiswaController::class, 'index'], [[Middleware::class, 'authRequired']]);
$router->get('/kelola-siswa/create', [SiswaController::class, 'create'], [Middleware::permissionRequired('siswa.data.create')]);
$router->post('/kelola-siswa/store', [SiswaController::class, 'store'], [Middleware::permissionRequired('siswa.data.create')]);
$router->get('/kelola-siswa/detail/{id}', [SiswaController::class, 'detail'], [Middleware::permissionRequired('siswa.data.view')]);
$router->get('/kelola-siswa/edit/{id}', [SiswaController::class, 'edit'], [Middleware::permissionRequired('siswa.data.update')]);
$router->post('/kelola-siswa/update/{id}', [SiswaController::class, 'update'], [Middleware::permissionRequired('siswa.data.update')]);
$router->post('/kelola-siswa/delete/{id}', [SiswaController::class, 'delete'], [Middleware::permissionRequired('siswa.data.delete')]);
$router->get('/kelola-siswa/cetak/{id}', [SiswaController::class, 'cetak'], [Middleware::permissionRequired('siswa.data.export')]);
$router->post('/kelola-siswa/sync', [SiswaController::class, 'syncJurnal'], [Middleware::permissionRequired('siswa.data.create')]);
$router->post('/kelola-siswa/sync-dapodik', [SiswaController::class, 'syncDapodikOnline'], [Middleware::permissionRequired('siswa.data.create')]);
$router->get('/kelola-siswa/export', [SiswaController::class, 'export'], [Middleware::permissionRequired('siswa.data.export')]);
$router->get('/kelola-siswa/foto', [SiswaController::class, 'foto'], [Middleware::permissionRequired('siswa.foto.view')]);
$router->post('/kelola-siswa/upload-foto', [SiswaController::class, 'uploadFoto'], [Middleware::permissionRequired('siswa.foto.create')]);
$router->post('/kelola-siswa/upload-foto-zip', [SiswaController::class, 'uploadFotoZip'], [Middleware::permissionRequired('siswa.foto.create')]);
$router->get('/kelola-siswa/cetak-kartu-massal', [SiswaController::class, 'cetakKartuMassal'], [Middleware::permissionRequired('siswa.foto.export')]);
$router->get('/kelola-siswa/cetak-kartu/{id}', [SiswaController::class, 'cetakKartu'], [Middleware::permissionRequired('siswa.foto.export')]);
$router->post('/kelola-siswa/upload-template-kartu', [SiswaController::class, 'uploadTemplateKartu'], [Middleware::permissionRequired('siswa.foto.update')]);

// Buku Induk Siswa
$router->get('/kelola-siswa/buku-induk', [SiswaController::class, 'bukuInduk'], [Middleware::permissionRequired('siswa.buku_induk.view')]);
$router->get('/kelola-siswa/buku-induk/export', [SiswaController::class, 'exportBukuInduk'], [Middleware::permissionRequired('siswa.buku_induk.export')]);
$router->get('/kelola-siswa/buku-induk/{id}', [SiswaController::class, 'detailBukuInduk'], [Middleware::permissionRequired('siswa.buku_induk.view')]);
$router->get('/kelola-siswa/buku-induk/{id}/cetak', [SiswaController::class, 'cetakBukuInduk'], [Middleware::permissionRequired('siswa.buku_induk.export')]);

// Prestasi Siswa
$router->get('/kelola-siswa/prestasi', [SiswaController::class, 'prestasi'], [Middleware::permissionRequired('siswa.prestasi.view')]);
$router->get('/kelola-siswa/prestasi/create', [SiswaController::class, 'createPrestasi'], [Middleware::permissionRequired('siswa.prestasi.create')]);
$router->post('/kelola-siswa/prestasi/store', [SiswaController::class, 'storePrestasi'], [Middleware::permissionRequired('siswa.prestasi.create')]);
$router->get('/kelola-siswa/prestasi/edit/{id}', [SiswaController::class, 'editPrestasi'], [Middleware::permissionRequired('siswa.prestasi.update')]);
$router->post('/kelola-siswa/prestasi/update/{id}', [SiswaController::class, 'updatePrestasi'], [Middleware::permissionRequired('siswa.prestasi.update')]);
$router->post('/kelola-siswa/prestasi/delete/{id}', [SiswaController::class, 'deletePrestasi'], [Middleware::permissionRequired('siswa.prestasi.delete')]);
$router->get('/kelola-siswa/prestasi/siswa/{id}', [SiswaController::class, 'timelinePrestasiSiswa'], [Middleware::permissionRequired('siswa.prestasi.view')]);

// Siswa Keluar & Mutasi
$router->get('/kelola-siswa/keluar', [SiswaController::class, 'siswaKeluar'], [Middleware::permissionRequired('siswa.keluar.view')]);
$router->get('/kelola-siswa/keluar/create', [SiswaController::class, 'createSiswaKeluar'], [Middleware::permissionRequired('siswa.keluar.create')]);
$router->post('/kelola-siswa/keluar/store', [SiswaController::class, 'storeSiswaKeluar'], [Middleware::permissionRequired('siswa.keluar.create')]);
$router->get('/kelola-siswa/keluar/edit/{id}', [SiswaController::class, 'editSiswaKeluar'], [Middleware::permissionRequired('siswa.keluar.update')]);
$router->post('/kelola-siswa/keluar/update/{id}', [SiswaController::class, 'updateSiswaKeluar'], [Middleware::permissionRequired('siswa.keluar.update')]);
$router->post('/kelola-siswa/keluar/delete/{id}', [SiswaController::class, 'deleteSiswaKeluar'], [Middleware::permissionRequired('siswa.keluar.delete')]);
$router->post('/kelola-siswa/keluar/reaktivasi/{id}', [SiswaController::class, 'reaktivasiSiswa'], [Middleware::permissionRequired('siswa.keluar.update')]);
$router->get('/kelola-siswa/keluar/cetak/{id}', [SiswaController::class, 'cetakSuratPindah'], [Middleware::permissionRequired('siswa.keluar.export')]);

// -- Kelola Kelas --
$router->get('/kelola-kelas', [KelasController::class, 'index'], [Middleware::permissionRequired('kelas.view')]);
$router->get('/kelola-kelas/create', [KelasController::class, 'create'], [Middleware::permissionRequired('kelas.create')]);
$router->post('/kelola-kelas/store', [KelasController::class, 'store'], [Middleware::permissionRequired('kelas.create')]);
$router->get('/kelola-kelas/edit/{id}', [KelasController::class, 'edit'], [Middleware::permissionRequired('kelas.update')]);
$router->post('/kelola-kelas/update/{id}', [KelasController::class, 'update'], [Middleware::permissionRequired('kelas.update')]);
$router->post('/kelola-kelas/delete/{id}', [KelasController::class, 'delete'], [Middleware::permissionRequired('kelas.delete')]);
$router->post('/kelola-kelas/copy', [KelasController::class, 'copyClasses'], [Middleware::permissionRequired('kelas.create')]);
$router->post('/kelola-kelas/sync-dapodik', [KelasController::class, 'syncDapodikOnline'], [Middleware::permissionRequired('kelas.create')]);

// -- Kelola Pegawai --
$router->get('/kelola-pegawai/statistik', [PegawaiController::class, 'statistik'], [Middleware::permissionRequired('pegawai.dashboard')]);
$router->get('/kelola-pegawai', [PegawaiController::class, 'index'], [[Middleware::class, 'authRequired']]);
$router->get('/kelola-pegawai/create', [PegawaiController::class, 'create'], [Middleware::permissionRequired('pegawai.data.create')]);
$router->post('/kelola-pegawai/store', [PegawaiController::class, 'store'], [Middleware::permissionRequired('pegawai.data.create')]);
$router->get('/kelola-pegawai/edit/{id}', [PegawaiController::class, 'edit'], [Middleware::permissionRequired('pegawai.data.update')]);
$router->post('/kelola-pegawai/update/{id}', [PegawaiController::class, 'update'], [Middleware::permissionRequired('pegawai.data.update')]);
$router->post('/kelola-pegawai/delete/{id}', [PegawaiController::class, 'delete'], [Middleware::permissionRequired('pegawai.data.delete')]);
$router->get('/kelola-pegawai/export', [PegawaiController::class, 'export'], [Middleware::permissionRequired('pegawai.data.export')]);
$router->get('/kelola-pegawai/template', [PegawaiController::class, 'downloadTemplate'], [Middleware::permissionRequired('pegawai.data.view')]);
$router->post('/kelola-pegawai/import', [PegawaiController::class, 'import'], [Middleware::permissionRequired('pegawai.data.create')]);
$router->get('/kelola-pegawai/keluar', [PegawaiController::class, 'keluar'], [Middleware::permissionRequired('pegawai.keluar.view')]);
$router->get('/kelola-pegawai/cetak-cv/{id}', [PegawaiController::class, 'cetakCv'], [Middleware::permissionRequired('pegawai.data.export')]);
$router->post('/kelola-pegawai/promosi-status', [PegawaiController::class, 'promosiStatus'], [Middleware::permissionRequired('pegawai.data.update')]);
$router->get('/kelola-pegawai/cetak-sk-status/{id}', [PegawaiController::class, 'cetakSkStatus'], [Middleware::permissionRequired(['pegawai.dashboard', 'pegawai.data.view'])]);

// Isi Jurnal & Data Jurnal Saya (Personal Pegawai/Guru yang Login)
$router->get('/kelola-pegawai/jurnal', [PegawaiController::class, 'jurnalSaya'], [[Middleware::class, 'authRequired']]);
$router->post('/kelola-pegawai/jurnal/store', [PegawaiController::class, 'storeJurnalSaya'], [[Middleware::class, 'authRequired']]);
$router->get('/kelola-pegawai/jurnal/api-siswa', [PegawaiController::class, 'apiSiswaKelasJurnal'], [[Middleware::class, 'authRequired']]);

// -- Kelola Penugasan & Grup SK --
$router->get('/kelola-pegawai/penugasan', [PegawaiController::class, 'penugasan'], [Middleware::permissionRequired('pegawai.penugasan.view')]);
$router->get('/kelola-pegawai/penugasan/grup/create', [PegawaiController::class, 'createGrup'], [Middleware::permissionRequired('pegawai.penugasan.create')]);
$router->post('/kelola-pegawai/penugasan/grup/store', [PegawaiController::class, 'storeGrup'], [Middleware::permissionRequired('pegawai.penugasan.create')]);
$router->get('/kelola-pegawai/penugasan/grup/edit/{id}', [PegawaiController::class, 'editGrup'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->post('/kelola-pegawai/penugasan/grup/update/{id}', [PegawaiController::class, 'updateGrup'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->post('/kelola-pegawai/penugasan/grup/delete/{id}', [PegawaiController::class, 'deleteGrup'], [Middleware::permissionRequired('pegawai.penugasan.delete')]);
$router->post('/kelola-pegawai/penugasan/grup/set-aktif/{id}', [PegawaiController::class, 'setAktifGrup'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->post('/kelola-pegawai/penugasan/grup/toggle-aktif/{id}', [PegawaiController::class, 'toggleAktifGrup'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->post('/kelola-pegawai/penugasan/grup/salin/{id}', [PegawaiController::class, 'salinGrup'], [Middleware::permissionRequired('pegawai.penugasan.create')]);

// Detail Anggota Penugasan dalam Grup
$router->get('/kelola-pegawai/penugasan/grup/{id}', [PegawaiController::class, 'detailGrup'], [Middleware::permissionRequired('pegawai.penugasan.view')]);
$router->get('/kelola-pegawai/penugasan/grup/{id}/cetak', [PegawaiController::class, 'cetakSkGrup'], [Middleware::permissionRequired('pegawai.penugasan.view')]);
$router->post('/kelola-pegawai/penugasan/grup/{id}/update-sk-meta', [PegawaiController::class, 'updateSkMeta'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->get('/kelola-pegawai/penugasan/grup/{id}/create', [PegawaiController::class, 'createPenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan.create')]);
$router->post('/kelola-pegawai/penugasan/grup/{id}/store', [PegawaiController::class, 'storePenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan.create')]);
$router->get('/kelola-pegawai/penugasan/detail/edit/{id}', [PegawaiController::class, 'editPenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->post('/kelola-pegawai/penugasan/detail/update/{id}', [PegawaiController::class, 'updatePenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->post('/kelola-pegawai/penugasan/detail/delete/{id}', [PegawaiController::class, 'deletePenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan.delete')]);

// Aliases for Penugasan member actions
$router->get('/kelola-pegawai/penugasan/{id}/edit', [PegawaiController::class, 'editPenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->post('/kelola-pegawai/penugasan/{id}/update', [PegawaiController::class, 'updatePenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan.update')]);
$router->post('/kelola-pegawai/penugasan/{id}/delete', [PegawaiController::class, 'deletePenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan.delete')]);

// -- Riwayat Karir Pegawai & Guru (Otomatis dari SK & Manual) --
$router->get('/kelola-pegawai/karir', [PegawaiController::class, 'karir'], [Middleware::permissionRequired('pegawai.karir.view')]);
$router->get('/kelola-pegawai/karir/create', [PegawaiController::class, 'createKarir'], [Middleware::permissionRequired('pegawai.karir.create')]);
$router->post('/kelola-pegawai/karir/store', [PegawaiController::class, 'storeKarir'], [Middleware::permissionRequired('pegawai.karir.create')]);
$router->get('/kelola-pegawai/karir/edit/{id}', [PegawaiController::class, 'editKarir'], [Middleware::permissionRequired('pegawai.karir.update')]);
$router->post('/kelola-pegawai/karir/update/{id}', [PegawaiController::class, 'updateKarir'], [Middleware::permissionRequired('pegawai.karir.update')]);
$router->post('/kelola-pegawai/karir/delete/{id}', [PegawaiController::class, 'deleteKarir'], [Middleware::permissionRequired('pegawai.karir.delete')]);
$router->get('/kelola-pegawai/karir/pegawai/{id}', [PegawaiController::class, 'timelinePegawai'], [Middleware::permissionRequired('pegawai.karir.view')]);

// -- Prestasi & Penghargaan Pegawai / Guru --
$router->get('/kelola-pegawai/prestasi', [PegawaiController::class, 'prestasi'], [Middleware::permissionRequired('pegawai.prestasi.view')]);
$router->get('/kelola-pegawai/prestasi/create', [PegawaiController::class, 'createPrestasi'], [Middleware::permissionRequired('pegawai.prestasi.create')]);
$router->post('/kelola-pegawai/prestasi/store', [PegawaiController::class, 'storePrestasi'], [Middleware::permissionRequired('pegawai.prestasi.create')]);
$router->get('/kelola-pegawai/prestasi/edit/{id}', [PegawaiController::class, 'editPrestasi'], [Middleware::permissionRequired('pegawai.prestasi.update')]);
$router->post('/kelola-pegawai/prestasi/update/{id}', [PegawaiController::class, 'updatePrestasi'], [Middleware::permissionRequired('pegawai.prestasi.update')]);
$router->post('/kelola-pegawai/prestasi/delete/{id}', [PegawaiController::class, 'deletePrestasi'], [Middleware::permissionRequired('pegawai.prestasi.delete')]);
$router->get('/kelola-pegawai/prestasi/pegawai/{id}', [PegawaiController::class, 'prestasiPegawai'], [Middleware::permissionRequired('pegawai.prestasi.view')]);

// -- Riwayat Pelatihan, Diklat & Workshop Pegawai / Guru --
$router->get('/kelola-pegawai/pelatihan', [PegawaiController::class, 'pelatihan'], [Middleware::permissionRequired('pegawai.pelatihan.view')]);
$router->get('/kelola-pegawai/pelatihan/create', [PegawaiController::class, 'createPelatihan'], [Middleware::permissionRequired('pegawai.pelatihan.create')]);
$router->post('/kelola-pegawai/pelatihan/store', [PegawaiController::class, 'storePelatihan'], [Middleware::permissionRequired('pegawai.pelatihan.create')]);
$router->get('/kelola-pegawai/pelatihan/edit/{id}', [PegawaiController::class, 'editPelatihan'], [Middleware::permissionRequired('pegawai.pelatihan.update')]);
$router->post('/kelola-pegawai/pelatihan/update/{id}', [PegawaiController::class, 'updatePelatihan'], [Middleware::permissionRequired('pegawai.pelatihan.update')]);
$router->post('/kelola-pegawai/pelatihan/delete/{id}', [PegawaiController::class, 'deletePelatihan'], [Middleware::permissionRequired('pegawai.pelatihan.delete')]);
$router->get('/kelola-pegawai/pelatihan/pegawai/{id}', [PegawaiController::class, 'pelatihanPegawai'], [Middleware::permissionRequired('pegawai.pelatihan.view')]);

// -- Kelola Perangkat Pembelajaran --
$router->get('/kelola-perangkat-pembelajaran', [PerangkatController::class, 'dashboard'], [[Middleware::class, 'authRequired']]);
$router->get('/kelola-perangkat-pembelajaran/dashboard', [PerangkatController::class, 'dashboard'], [Middleware::permissionRequired('perangkat.dashboard')]);
$router->get('/kelola-rpp', function() {
    Response::redirect(url('kelola-perangkat-pembelajaran'));
});

// Jadwal Pelajaran & Auto-Generator
$router->get('/kelola-perangkat-pembelajaran/jadwal', [JadwalController::class, 'index'], [Middleware::permissionRequired('perangkat.jadwal.view')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/create', [JadwalController::class, 'create'], [Middleware::permissionRequired('perangkat.jadwal.create')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/store', [JadwalController::class, 'store'], [Middleware::permissionRequired('perangkat.jadwal.create')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/edit/{id}', [JadwalController::class, 'edit'], [Middleware::permissionRequired('perangkat.jadwal.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/update/{id}', [JadwalController::class, 'update'], [Middleware::permissionRequired('perangkat.jadwal.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/delete/{id}', [JadwalController::class, 'delete'], [Middleware::permissionRequired('perangkat.jadwal.delete')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/set-active/{id}', [JadwalController::class, 'setActive'], [Middleware::permissionRequired('perangkat.jadwal.update')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{id}', [JadwalController::class, 'pengaturanJp'], [Middleware::permissionRequired('perangkat.jadwal.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{id}', [JadwalController::class, 'simpanPengaturanJp'], [Middleware::permissionRequired('perangkat.jadwal.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-add/{id}', [JadwalController::class, 'tambahSlotKhusus'], [Middleware::permissionRequired('perangkat.jadwal.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-delete/{id}/{slotId}', [JadwalController::class, 'hapusSlotWaktu'], [Middleware::permissionRequired('perangkat.jadwal.delete')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-edit/{id}/{slotId}', [JadwalController::class, 'editSlotWaktu'], [Middleware::permissionRequired('perangkat.jadwal.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-sync-day/{id}/{hari}', [JadwalController::class, 'syncHari'], [Middleware::permissionRequired('perangkat.jadwal.update')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/generate/{id}', [JadwalController::class, 'generate'], [Middleware::permissionRequired('perangkat.jadwal.create')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/run-generate/{id}', [JadwalController::class, 'runGenerate'], [Middleware::permissionRequired('perangkat.jadwal.create')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/matriks/{id}', [JadwalController::class, 'matriks'], [Middleware::permissionRequired('perangkat.jadwal.view')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/cetak-kelas/{id}', [JadwalController::class, 'cetakKelas'], [Middleware::permissionRequired('perangkat.jadwal.export')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/cetak-guru/{id}', [JadwalController::class, 'cetakGuru'], [Middleware::permissionRequired('perangkat.jadwal.export')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/export/{id}', [JadwalController::class, 'export'], [Middleware::permissionRequired('perangkat.jadwal.export')]);

// Kalender Pendidikan (Kaldik)
$router->get('/kelola-perangkat-pembelajaran/kaldik', [PerangkatController::class, 'kaldik'], [Middleware::permissionRequired('perangkat.kaldik.view')]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/create', [PerangkatController::class, 'createKaldik'], [Middleware::permissionRequired('perangkat.kaldik.create')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/store', [PerangkatController::class, 'storeKaldik'], [Middleware::permissionRequired('perangkat.kaldik.create')]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/edit/{id}', [PerangkatController::class, 'editKaldik'], [Middleware::permissionRequired('perangkat.kaldik.update')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/update/{id}', [PerangkatController::class, 'updateKaldik'], [Middleware::permissionRequired('perangkat.kaldik.update')]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/detail/{id}', [PerangkatController::class, 'detailKaldik'], [Middleware::permissionRequired('perangkat.kaldik.view')]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/cetak/{id}', [PerangkatController::class, 'cetakKaldik'], [Middleware::permissionRequired('perangkat.kaldik.view')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/toggle-active/{id}', [PerangkatController::class, 'toggleActiveKaldik'], [Middleware::permissionRequired('perangkat.kaldik.update')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/agenda/add/{id}', [PerangkatController::class, 'addKaldikAgenda'], [Middleware::permissionRequired('perangkat.kaldik.update')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/agenda/delete/{id}', [PerangkatController::class, 'deleteKaldikAgenda'], [Middleware::permissionRequired('perangkat.kaldik.delete')]);

// Rincian Hari Efektif (HEB & HES Auto-Generated)
$router->get('/kelola-perangkat-pembelajaran/rincian-hari-efektif', [PerangkatController::class, 'rincianHariEfektif'], [Middleware::permissionRequired('perangkat.hari_efektif.view')]);
$router->get('/kelola-perangkat-pembelajaran/rincian-hari-efektif/cetak', [PerangkatController::class, 'cetakRincianHariEfektif'], [Middleware::permissionRequired('perangkat.hari_efektif.view')]);

// Hari Efektif Sekolah (HES)
$router->get('/kelola-perangkat-pembelajaran/hes', [PerangkatController::class, 'hes'], [Middleware::permissionRequired('perangkat.hari_efektif.view')]);
$router->get('/kelola-perangkat-pembelajaran/hes/create', [PerangkatController::class, 'createHes'], [Middleware::permissionRequired('perangkat.hari_efektif.create')]);
$router->post('/kelola-perangkat-pembelajaran/hes/store', [PerangkatController::class, 'storeHes'], [Middleware::permissionRequired('perangkat.hari_efektif.create')]);
$router->get('/kelola-perangkat-pembelajaran/hes/edit/{id}', [PerangkatController::class, 'editHes'], [Middleware::permissionRequired('perangkat.hari_efektif.update')]);
$router->post('/kelola-perangkat-pembelajaran/hes/update/{id}', [PerangkatController::class, 'updateHes'], [Middleware::permissionRequired('perangkat.hari_efektif.update')]);
$router->get('/kelola-perangkat-pembelajaran/hes/detail/{id}', [PerangkatController::class, 'detailHes'], [Middleware::permissionRequired('perangkat.hari_efektif.view')]);
$router->get('/kelola-perangkat-pembelajaran/hes/cetak/{id}', [PerangkatController::class, 'cetakHes'], [Middleware::permissionRequired('perangkat.hari_efektif.view')]);

// Hari Efektif Belajar (HEB)
$router->get('/kelola-perangkat-pembelajaran/heb', [PerangkatController::class, 'heb'], [Middleware::permissionRequired('perangkat.hari_efektif.view')]);
$router->get('/kelola-perangkat-pembelajaran/heb/create', [PerangkatController::class, 'createHeb'], [Middleware::permissionRequired('perangkat.hari_efektif.create')]);
$router->post('/kelola-perangkat-pembelajaran/heb/store', [PerangkatController::class, 'storeHeb'], [Middleware::permissionRequired('perangkat.hari_efektif.create')]);
$router->get('/kelola-perangkat-pembelajaran/heb/edit/{id}', [PerangkatController::class, 'editHeb'], [Middleware::permissionRequired('perangkat.hari_efektif.update')]);
$router->post('/kelola-perangkat-pembelajaran/heb/update/{id}', [PerangkatController::class, 'updateHeb'], [Middleware::permissionRequired('perangkat.hari_efektif.update')]);
$router->get('/kelola-perangkat-pembelajaran/heb/detail/{id}', [PerangkatController::class, 'detailHeb'], [Middleware::permissionRequired('perangkat.hari_efektif.view')]);
$router->get('/kelola-perangkat-pembelajaran/heb/cetak/{id}', [PerangkatController::class, 'cetakHeb'], [Middleware::permissionRequired('perangkat.hari_efektif.view')]);

// Capaian Pembelajaran & Alur Tujuan Pembelajaran (CP & ATP)
$router->get('/kelola-perangkat-pembelajaran/cpatp', [PerangkatController::class, 'cpatp'], [Middleware::permissionRequired('perangkat.cpatp.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/ajax-penugasan/{guruId}', [PerangkatController::class, 'getPenugasanAjax'], [Middleware::permissionRequired('perangkat.cpatp.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/ajax-jadwal/{guruId}', [PerangkatController::class, 'getJadwalHariAjax'], [Middleware::permissionRequired('perangkat.cpatp.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/group/create', [PerangkatController::class, 'createCpatpGroup'], [Middleware::permissionRequired('perangkat.cpatp.create')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/group/store', [PerangkatController::class, 'storeCpatpGroup'], [Middleware::permissionRequired('perangkat.cpatp.create')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/group/delete/{id}', [PerangkatController::class, 'deleteCpatpGroup'], [Middleware::permissionRequired('perangkat.cpatp.delete')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/group/{id}', [PerangkatController::class, 'cpatpDetailGroup'], [Middleware::permissionRequired('perangkat.cpatp.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/create/{groupId}', [PerangkatController::class, 'createCpatp'], [Middleware::permissionRequired('perangkat.cpatp.create')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/store/{groupId}', [PerangkatController::class, 'storeCpatp'], [Middleware::permissionRequired('perangkat.cpatp.create')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/edit/{id}', [PerangkatController::class, 'editCpatp'], [Middleware::permissionRequired('perangkat.cpatp.update')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/update/{id}', [PerangkatController::class, 'updateCpatp'], [Middleware::permissionRequired('perangkat.cpatp.update')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/detail/{id}', [PerangkatController::class, 'detailCpatp'], [Middleware::permissionRequired('perangkat.cpatp.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/cetak/{id}', [PerangkatController::class, 'cetakCpatp'], [Middleware::permissionRequired('perangkat.cpatp.view')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/delete/{id}', [PerangkatController::class, 'deleteCpatp'], [Middleware::permissionRequired('perangkat.cpatp.delete')]);

// Program Tahunan (Prota)
$router->get('/kelola-perangkat-pembelajaran/prota', [PerangkatController::class, 'prota'], [Middleware::permissionRequired('perangkat.prota.view')]);
$router->get('/kelola-perangkat-pembelajaran/prota/group/create', [PerangkatController::class, 'createProtaGroup'], [Middleware::permissionRequired('perangkat.prota.create')]);
$router->post('/kelola-perangkat-pembelajaran/prota/group/store', [PerangkatController::class, 'storeProtaGroup'], [Middleware::permissionRequired('perangkat.prota.create')]);
$router->post('/kelola-perangkat-pembelajaran/prota/group/delete/{id}', [PerangkatController::class, 'deleteProtaGroup'], [Middleware::permissionRequired('perangkat.prota.delete')]);
$router->get('/kelola-perangkat-pembelajaran/prota/group/{id}', [PerangkatController::class, 'protaDetailGroup'], [Middleware::permissionRequired('perangkat.prota.view')]);
$router->post('/kelola-perangkat-pembelajaran/prota/group/{id}/sync', [PerangkatController::class, 'syncProtaFromProsem'], [Middleware::permissionRequired('perangkat.prota.update')]);
$router->get('/kelola-perangkat-pembelajaran/prota/group/{id}/cetak-semua', [PerangkatController::class, 'cetakSemuaProtaGroup'], [Middleware::permissionRequired('perangkat.prota.view')]);
$router->get('/kelola-perangkat-pembelajaran/prota/create', [PerangkatController::class, 'createProta'], [Middleware::permissionRequired('perangkat.prota.create')]);
$router->get('/kelola-perangkat-pembelajaran/prota/create/{groupId}', [PerangkatController::class, 'createProta'], [Middleware::permissionRequired('perangkat.prota.create')]);
$router->post('/kelola-perangkat-pembelajaran/prota/store', [PerangkatController::class, 'storeProta'], [Middleware::permissionRequired('perangkat.prota.create')]);
$router->post('/kelola-perangkat-pembelajaran/prota/store/{groupId}', [PerangkatController::class, 'storeProta'], [Middleware::permissionRequired('perangkat.prota.create')]);
$router->get('/kelola-perangkat-pembelajaran/prota/edit/{id}', [PerangkatController::class, 'editProta'], [Middleware::permissionRequired('perangkat.prota.update')]);
$router->post('/kelola-perangkat-pembelajaran/prota/update/{id}', [PerangkatController::class, 'updateProta'], [Middleware::permissionRequired('perangkat.prota.update')]);
$router->get('/kelola-perangkat-pembelajaran/prota/detail/{id}', [PerangkatController::class, 'detailProta'], [Middleware::permissionRequired('perangkat.prota.view')]);
$router->get('/kelola-perangkat-pembelajaran/prota/cetak/{id}', [PerangkatController::class, 'cetakProta'], [Middleware::permissionRequired('perangkat.prota.view')]);
$router->post('/kelola-perangkat-pembelajaran/prota/delete/{id}', [PerangkatController::class, 'deleteProta'], [Middleware::permissionRequired('perangkat.prota.delete')]);

// Program Semester (Prosem)
$router->get('/kelola-perangkat-pembelajaran/prosem', [PerangkatController::class, 'prosem'], [Middleware::permissionRequired('perangkat.prosem.view')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/create', [PerangkatController::class, 'createProsemGroup'], [Middleware::permissionRequired('perangkat.prosem.create')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/store', [PerangkatController::class, 'storeProsemGroup'], [Middleware::permissionRequired('perangkat.prosem.create')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/delete/{id}', [PerangkatController::class, 'deleteProsemGroup'], [Middleware::permissionRequired('perangkat.prosem.delete')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/{id}', [PerangkatController::class, 'prosemDetailGroup'], [Middleware::permissionRequired('perangkat.prosem.view')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/{id}/sync', [PerangkatController::class, 'syncProsemFromCpatp'], [Middleware::permissionRequired('perangkat.prosem.update')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/{id}/cetak-semua', [PerangkatController::class, 'cetakSemuaProsemGroup'], [Middleware::permissionRequired('perangkat.prosem.view')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/create', [PerangkatController::class, 'createProsem'], [Middleware::permissionRequired('perangkat.prosem.create')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/create/{groupId}', [PerangkatController::class, 'createProsem'], [Middleware::permissionRequired('perangkat.prosem.create')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/store', [PerangkatController::class, 'storeProsem'], [Middleware::permissionRequired('perangkat.prosem.create')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/store/{groupId}', [PerangkatController::class, 'storeProsem'], [Middleware::permissionRequired('perangkat.prosem.create')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/edit/{id}', [PerangkatController::class, 'editProsem'], [Middleware::permissionRequired('perangkat.prosem.update')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/update/{id}', [PerangkatController::class, 'updateProsem'], [Middleware::permissionRequired('perangkat.prosem.update')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/detail/{id}', [PerangkatController::class, 'detailProsem'], [Middleware::permissionRequired('perangkat.prosem.view')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/cetak/{id}', [PerangkatController::class, 'cetakProsem'], [Middleware::permissionRequired('perangkat.prosem.view')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/delete/{id}', [PerangkatController::class, 'deleteProsem'], [Middleware::permissionRequired('perangkat.prosem.delete')]);

// RPP / Modul Ajar
$router->get('/kelola-perangkat-pembelajaran/rpp', [PerangkatController::class, 'rpp'], [Middleware::permissionRequired('perangkat.rpp.view')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/group/create', [PerangkatController::class, 'createRppGroup'], [Middleware::permissionRequired('perangkat.rpp.create')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/group/store', [PerangkatController::class, 'storeRppGroup'], [Middleware::permissionRequired('perangkat.rpp.create')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/group/delete/{id}', [PerangkatController::class, 'deleteRppGroup'], [Middleware::permissionRequired('perangkat.rpp.delete')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/group/{id}', [PerangkatController::class, 'rppDetailGroup'], [Middleware::permissionRequired('perangkat.rpp.view')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/group/{id}/sync', [PerangkatController::class, 'syncRppFromCpatp'], [Middleware::permissionRequired('perangkat.rpp.update')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/group/{id}/cetak-semua', [PerangkatController::class, 'cetakSemuaRppGroup'], [Middleware::permissionRequired('perangkat.rpp.view')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/create', [PerangkatController::class, 'createRpp'], [Middleware::permissionRequired('perangkat.rpp.create')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/create/{groupId}', [PerangkatController::class, 'createRpp'], [Middleware::permissionRequired('perangkat.rpp.create')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/store', [PerangkatController::class, 'storeRpp'], [Middleware::permissionRequired('perangkat.rpp.create')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/store/{groupId}', [PerangkatController::class, 'storeRpp'], [Middleware::permissionRequired('perangkat.rpp.create')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/edit/{id}', [PerangkatController::class, 'editRpp'], [Middleware::permissionRequired('perangkat.rpp.update')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/update/{id}', [PerangkatController::class, 'updateRpp'], [Middleware::permissionRequired('perangkat.rpp.update')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/detail/{id}', [PerangkatController::class, 'detailRpp'], [Middleware::permissionRequired('perangkat.rpp.view')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/cetak/{id}', [PerangkatController::class, 'cetakRpp'], [Middleware::permissionRequired('perangkat.rpp.view')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/delete/{id}', [PerangkatController::class, 'deleteRpp'], [Middleware::permissionRequired('perangkat.rpp.delete')]);

// Pusat Verifikasi & Lifecycle Actions (Wewenang Khusus Approve)
$router->get('/kelola-perangkat-pembelajaran/verifikasi', [PerangkatController::class, 'verifikasi'], [Middleware::permissionRequired('perangkat.approve')]);
$router->post('/kelola-perangkat-pembelajaran/approve/{id}', [PerangkatController::class, 'approve'], [Middleware::permissionRequired('perangkat.approve')]);
$router->post('/kelola-perangkat-pembelajaran/reject/{id}', [PerangkatController::class, 'reject'], [Middleware::permissionRequired('perangkat.approve')]);
$router->post('/kelola-perangkat-pembelajaran/submit/{id}', [PerangkatController::class, 'submitReview'], [Middleware::permissionRequired('perangkat.rpp.update')]);
$router->post('/kelola-perangkat-pembelajaran/draft/{id}', [PerangkatController::class, 'draft'], [Middleware::permissionRequired('perangkat.rpp.update')]);
$router->post('/kelola-perangkat-pembelajaran/delete/{id}', [PerangkatController::class, 'delete'], [Middleware::permissionRequired('perangkat.rpp.delete')]);

// -- Kelola Jurnal Guru (KBM & Jam Pelajaran) --
$router->get('/kelola-jurnal-guru', [JurnalGuruAdminController::class, 'index'], [Middleware::permissionRequired('jurnal.view')]);
$router->get('/kelola-jurnal-guru/cetak', [JurnalGuruAdminController::class, 'cetak'], [Middleware::permissionRequired('jurnal.view')]);
$router->get('/kelola-jurnal-guru/api/detail/{id}', [JurnalGuruAdminController::class, 'apiDetail'], [Middleware::permissionRequired('jurnal.view')]);
$router->get('/kelola-jurnal-guru/api/guru-assignments/{id}', [JurnalGuruAdminController::class, 'apiGuruAssignments'], [Middleware::permissionRequired('jurnal.view')]);
$router->get('/kelola-jurnal-guru/api/siswa-kelas', [JurnalGuruAdminController::class, 'apiSiswaKelas'], [Middleware::permissionRequired('jurnal.view')]);
$router->post('/kelola-jurnal-guru/store', [JurnalGuruAdminController::class, 'store'], [Middleware::permissionRequired('jurnal.manage')]);
$router->post('/kelola-jurnal-guru/store-saya', [PegawaiController::class, 'storeJurnalSaya'], [[Middleware::class, 'authRequired']]);
$router->post('/kelola-jurnal-guru/status/{id}', [JurnalGuruAdminController::class, 'updateStatus'], [Middleware::permissionRequired('jurnal.manage')]);
$router->post('/kelola-jurnal-guru/delete/{id}', [JurnalGuruAdminController::class, 'delete'], [Middleware::permissionRequired('jurnal.delete')]);
$router->post('/kelola-jurnal-guru/pengaturan-periode', [JurnalGuruAdminController::class, 'savePengaturanPeriode'], [Middleware::permissionRequired('jurnal.manage')]);

// -- Kelola Jurnal Staff (Support System & Divisi) --
$router->get('/kelola-jurnal-staff', [JurnalStaffAdminController::class, 'index'], [Middleware::permissionRequired('jurnal_staff.view')]);
$router->get('/kelola-jurnal-staff/cetak', [JurnalStaffAdminController::class, 'cetak'], [Middleware::permissionRequired('jurnal_staff.view')]);
$router->get('/kelola-jurnal-staff/api/tugas/{jabatanId}', [JurnalStaffAdminController::class, 'apiTugasByJabatan'], [Middleware::permissionRequired('jurnal_staff.view')]);
$router->get('/kelola-jurnal-staff/api/detail/{id}', [JurnalStaffAdminController::class, 'apiDetailJurnal'], [Middleware::permissionRequired('jurnal_staff.view')]);

$router->post('/kelola-jurnal-staff/jurnal/store', [JurnalStaffAdminController::class, 'storeJurnal'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/jurnal/update/{id}', [JurnalStaffAdminController::class, 'updateJurnal'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/jurnal/delete/{id}', [JurnalStaffAdminController::class, 'deleteJurnal'], [Middleware::permissionRequired('jurnal_staff.delete')]);
$router->post('/kelola-jurnal-staff/jurnal/status/{id}', [JurnalStaffAdminController::class, 'updateStatusJurnal'], [Middleware::permissionRequired('jurnal_staff.manage')]);

$router->post('/kelola-jurnal-staff/jam/store', [JurnalStaffAdminController::class, 'storeJam'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/jam/update/{id}', [JurnalStaffAdminController::class, 'updateJam'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/jam/delete/{id}', [JurnalStaffAdminController::class, 'deleteJam'], [Middleware::permissionRequired('jurnal_staff.delete')]);
$router->post('/kelola-jurnal-staff/jam/toggle/{id}', [JurnalStaffAdminController::class, 'toggleJam'], [Middleware::permissionRequired('jurnal_staff.manage')]);

$router->post('/kelola-jurnal-staff/tugas/store', [JurnalStaffAdminController::class, 'storeTugas'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/tugas/update/{id}', [JurnalStaffAdminController::class, 'updateTugas'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/tugas/delete/{id}', [JurnalStaffAdminController::class, 'deleteTugas'], [Middleware::permissionRequired('jurnal_staff.delete')]);
$router->post('/kelola-jurnal-staff/tugas/toggle/{id}', [JurnalStaffAdminController::class, 'toggleTugas'], [Middleware::permissionRequired('jurnal_staff.manage')]);

$router->post('/kelola-jurnal-staff/pengaturan-periode', [JurnalStaffAdminController::class, 'savePengaturanPeriode'], [Middleware::permissionRequired('jurnal_staff.manage')]);

$router->post('/kelola-jurnal-staff/pengingat/save', [JurnalStaffAdminController::class, 'savePengingatSettings'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/pengingat/run', [JurnalStaffAdminController::class, 'runPengingatNow'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/pengingat/send-single', [JurnalStaffAdminController::class, 'sendSinglePengingat'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/pengingat/test', [JurnalStaffAdminController::class, 'testPengingatWa'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/pengingat/regenerate-key', [JurnalStaffAdminController::class, 'regenerateCronKey'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->post('/kelola-jurnal-staff/pengingat/recipients', [JurnalStaffAdminController::class, 'savePengingatRecipients'], [Middleware::permissionRequired('jurnal_staff.manage')]);
$router->get('/kelola-jurnal-staff/pengingat/preview', [JurnalStaffAdminController::class, 'apiPreviewPengingat'], [Middleware::permissionRequired('jurnal_staff.view')]);
$router->get('/kelola-jurnal-staff/pengingat/auto-check', [JurnalStaffAdminController::class, 'apiAutoCheckPengingat']);

// Background Auto-Scheduler Hook untuk Pengingat WA Jurnal Staff (berjalan otomatis saat jam jadwal tiba)
register_shutdown_function(static function (): void {
    try {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/cron/') !== false || strpos($uri, '/auto-check') !== false || strpos($uri, '.png') !== false || strpos($uri, '.js') !== false) {
            return;
        }
        $lockFile = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'portal_bip_jurnal_wa_autocheck.json';
        if (is_file($lockFile) && (time() - (int)@filemtime($lockFile)) < 35) {
            return;
        }
        if (function_exists('fastcgi_finish_request')) {
            @fastcgi_finish_request();
        }
        require_once MODULES_PATH . '/kelola-jurnal-staff/services/JurnalStaffReminderService.php';
        JurnalStaffReminderService::autoDispatchDueSlotIfNeeded(true);
    } catch (Throwable $e) {}
});

// Base Routes Modul Tambahan Lainnya
$router->get('/kelola-nilai', [NilaiController::class, 'index'], [[Middleware::class, 'authRequired']]);
$router->get('/kelola-nilai/group', [NilaiController::class, 'groupList'], [Middleware::permissionRequired('nilai.group.view')]);
$router->get('/kelola-nilai/group/create', [NilaiController::class, 'groupCreate'], [Middleware::permissionRequired('nilai.group.create')]);
$router->post('/kelola-nilai/group/store', [NilaiController::class, 'groupStore'], [Middleware::permissionRequired('nilai.group.create')]);
$router->get('/kelola-nilai/group/edit/{id}', [NilaiController::class, 'groupEdit'], [Middleware::permissionRequired('nilai.group.update')]);
$router->post('/kelola-nilai/group/update/{id}', [NilaiController::class, 'groupUpdate'], [Middleware::permissionRequired('nilai.group.update')]);
$router->post('/kelola-nilai/group/toggle-status/{id}', [NilaiController::class, 'groupToggleStatus'], [Middleware::permissionRequired('nilai.group.update')]);
$router->post('/kelola-nilai/group/delete/{id}', [NilaiController::class, 'groupDelete'], [Middleware::permissionRequired('nilai.group.delete')]);
$router->get('/kelola-nilai/input/{groupId}', [NilaiController::class, 'inputNilai'], [Middleware::permissionRequired('nilai.input.view')]);
$router->get('/kelola-nilai/input/{groupId}/guru/{guruId}', [NilaiController::class, 'guruDetail'], [Middleware::permissionRequired('nilai.input.view')]);
$router->get('/kelola-nilai/input/{groupId}/doc/{cpatpDocId}', [NilaiController::class, 'inputNilaiForm'], [Middleware::permissionRequired('nilai.input.create')]);
$router->post('/kelola-nilai/store/{groupId}/doc/{cpatpDocId}', [NilaiController::class, 'storeNilaiForm'], [Middleware::permissionRequired('nilai.input.create')]);
$router->post('/kelola-nilai/autosave/{groupId}/doc/{cpatpDocId}', [NilaiController::class, 'autoSaveNilai'], [Middleware::permissionRequired('nilai.input.update')]);
$router->get('/kelola-nilai/cetak/{groupId}/doc/{cpatpDocId}', [NilaiController::class, 'cetakNilai'], [Middleware::permissionRequired('nilai.input.view')]);
$router->get('/kelola-nilai/rekap', [NilaiController::class, 'rekap'], [Middleware::permissionRequired('nilai.rekap')]);
$router->get('/kelola-nilai/rekap/cetak', [NilaiController::class, 'rekapCetak'], [Middleware::permissionRequired('nilai.rekap')]);
$router->get('/kelola-nilai/rekap/export', [NilaiController::class, 'rekapExport'], [Middleware::permissionRequired('nilai.export')]);
$router->get('/kelola-raport', [RaportController::class, 'index'], [Middleware::permissionRequired('raport.view')]);
// Modul Kelola Absen Siswa
$router->get('/kelola-absen-siswa', [AbsenSiswaController::class, 'index'], [[Middleware::class, 'authRequired']]);

// Grup / Wadah Absen
$router->get('/kelola-absen-siswa/group', [AbsenSiswaController::class, 'groupList'], [Middleware::permissionRequired('absen_siswa.group.view')]);
$router->get('/kelola-absen-siswa/group/create', [AbsenSiswaController::class, 'groupCreate'], [Middleware::permissionRequired('absen_siswa.group.create')]);
$router->post('/kelola-absen-siswa/group/store', [AbsenSiswaController::class, 'groupStore'], [Middleware::permissionRequired('absen_siswa.group.create')]);
$router->get('/kelola-absen-siswa/group/edit/{id}', [AbsenSiswaController::class, 'groupEdit'], [Middleware::permissionRequired('absen_siswa.group.update')]);
$router->post('/kelola-absen-siswa/group/update/{id}', [AbsenSiswaController::class, 'groupUpdate'], [Middleware::permissionRequired('absen_siswa.group.update')]);
$router->post('/kelola-absen-siswa/group/toggle-status/{id}', [AbsenSiswaController::class, 'groupToggleStatus'], [Middleware::permissionRequired('absen_siswa.group.update')]);
$router->post('/kelola-absen-siswa/group/delete/{id}', [AbsenSiswaController::class, 'groupDelete'], [Middleware::permissionRequired('absen_siswa.group.delete')]);

// Buka Wadah Absen (Pilih Guru -> Muncul Semua Kelas yang Dia Ampu)
$router->get('/kelola-absen-siswa/input/{groupId}', [AbsenSiswaController::class, 'wadahDetail'], [Middleware::permissionRequired('absen_siswa.mapel.view')]);
$router->get('/kelola-absen-siswa/mapel/wadah/{groupId}', [AbsenSiswaController::class, 'wadahDetail'], [Middleware::permissionRequired('absen_siswa.mapel.view')]);

// Input Absen Mapel
$router->get('/kelola-absen-siswa/mapel', [AbsenSiswaController::class, 'absenMapel'], [Middleware::permissionRequired('absen_siswa.mapel.view')]);
$router->get('/kelola-absen-siswa/mapel/input', [AbsenSiswaController::class, 'absenMapelForm'], [Middleware::permissionRequired('absen_siswa.mapel.create')]);
$router->post('/kelola-absen-siswa/mapel/store', [AbsenSiswaController::class, 'absenMapelStore'], [Middleware::permissionRequired('absen_siswa.mapel.create')]);
$router->post('/kelola-absen-siswa/mapel/autosave', [AbsenSiswaController::class, 'absenMapelAutosave'], [Middleware::permissionRequired('absen_siswa.mapel.create')]);
$router->get('/kelola-absen-siswa/mapel/cetak', [AbsenSiswaController::class, 'absenMapelCetak'], [Middleware::permissionRequired('absen_siswa.mapel.view')]);

// Input Absen Kelas (Wali Kelas / Harian)
$router->get('/kelola-absen-siswa/kelas', [AbsenSiswaController::class, 'absenKelas'], [Middleware::permissionRequired('absen_siswa.kelas.view')]);
$router->get('/kelola-absen-siswa/kelas/input', [AbsenSiswaController::class, 'absenKelasForm'], [Middleware::permissionRequired('absen_siswa.kelas.create')]);
$router->post('/kelola-absen-siswa/kelas/store', [AbsenSiswaController::class, 'absenKelasStore'], [Middleware::permissionRequired('absen_siswa.kelas.create')]);
$router->post('/kelola-absen-siswa/kelas/autosave', [AbsenSiswaController::class, 'absenKelasAutosave'], [Middleware::permissionRequired('absen_siswa.kelas.create')]);
$router->get('/kelola-absen-siswa/kelas/cetak', [AbsenSiswaController::class, 'absenKelasCetak'], [Middleware::permissionRequired('absen_siswa.kelas.view')]);

// Rekap Absen Mapel + Kelas
$router->get('/kelola-absen-siswa/rekap', [AbsenSiswaController::class, 'rekap'], [Middleware::personalOrPermissionRequired(['absen_siswa.rekap', 'absen_siswa.dashboard', 'absen_siswa.mapel.view', 'absen_siswa.kelas.view', 'absen_siswa.view'])]);
$router->get('/kelola-absen-siswa/rekap/cetak', [AbsenSiswaController::class, 'rekapCetak'], [Middleware::personalOrPermissionRequired(['absen_siswa.rekap', 'absen_siswa.dashboard', 'absen_siswa.mapel.view', 'absen_siswa.kelas.view', 'absen_siswa.view'])]);
$router->get('/kelola-absen-siswa/rekap/export', [AbsenSiswaController::class, 'rekapExport'], [Middleware::personalOrPermissionRequired(['absen_siswa.export', 'absen_siswa.rekap', 'absen_siswa.dashboard', 'absen_siswa.mapel.view', 'absen_siswa.kelas.view', 'absen_siswa.view'])]);
// -- Kelola Lokasi & Radius Absen Pegawai --
$router->get('/kelola-absen-pegawai', [AbsenPegawaiController::class, 'index'], [Middleware::personalOrPermissionRequired(['absen_pegawai.view', 'absen_pegawai.dashboard', 'absen_pegawai.create', 'absen_pegawai.update', 'absen_pegawai.approve'])]);
$router->post('/kelola-absen-pegawai/pengaturan/save', [AbsenPegawaiController::class, 'savePengaturanGlobal'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/radius/update', [AbsenPegawaiController::class, 'updateRadiusQuick'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/pegawai/save', [AbsenPegawaiController::class, 'saveLokasiPegawai'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/pegawai/reset', [AbsenPegawaiController::class, 'resetLokasiPegawai'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/jam-kerja/save', [AbsenPegawaiController::class, 'saveJamKerjaPegawai'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/jam-kerja/reset', [AbsenPegawaiController::class, 'resetJamKerjaPegawai'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/unit/save', [AbsenPegawaiController::class, 'saveLokasiUnit'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/unit/reset', [AbsenPegawaiController::class, 'resetLokasiUnit'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/reset-semua', [AbsenPegawaiController::class, 'resetSemuaLokasi'], [Middleware::permissionRequired('absen_pegawai.delete')]);
$router->get('/kelola-absen-pegawai/export', [AbsenPegawaiController::class, 'exportCsv'], [Middleware::personalOrPermissionRequired(['absen_pegawai.view', 'absen_pegawai.dashboard', 'absen_pegawai.approve'])]);
$router->get('/kelola-absen-pegawai/cetak-pdf', [AbsenPegawaiController::class, 'cetakPdfRekap'], [Middleware::personalOrPermissionRequired(['absen_pegawai.view', 'absen_pegawai.dashboard', 'absen_pegawai.approve'])]);
$router->post('/kelola-absen-pegawai/log/update', [AbsenPegawaiController::class, 'updateLogPresensi'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/log/delete', [AbsenPegawaiController::class, 'deleteLogPresensi'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/catatan-terlambat/save', [AbsenPegawaiController::class, 'saveCatatanTerlambat'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/catatan-terlambat/delete', [AbsenPegawaiController::class, 'deleteCatatanTerlambat'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/libur/save', [AbsenPegawaiController::class, 'saveLibur'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/libur/delete', [AbsenPegawaiController::class, 'deleteLibur'], [Middleware::permissionRequired('absen_pegawai.update')]);
$router->post('/kelola-absen-pegawai/urutan-unit/save', [AbsenPegawaiController::class, 'saveUrutanUnit'], [Middleware::permissionRequired('absen_pegawai.update')]);
// Modul Qur'an Siswa (Hub + 3 Jenjang: SD, PAUD, SMP & SMA)
$router->get('/kelola-quran-siswa', [QuranSiswaController::class, 'index'], [[Middleware::class, 'authRequired']]);
$router->get('/kelola-quran-siswa-sd', [QuranSiswaController::class, 'sdIndex'], [Middleware::permissionRequired('quran_siswa.sd_smp.view')]);
$router->get('/kelola-quran-siswa/sd', [QuranSiswaController::class, 'sdIndex'], [Middleware::permissionRequired('quran_siswa.sd_smp.view')]);
$router->get('/kelola-quran-siswa-paud', [QuranSiswaController::class, 'paudIndex'], [Middleware::permissionRequired('quran_siswa.paud_target.view')]);
$router->get('/kelola-quran-siswa/paud', [QuranSiswaController::class, 'paudIndex'], [Middleware::permissionRequired('quran_siswa.paud_target.view')]);

// Fitur 2: Pengaturan Target Qur'an PAUD (a. Tahsin, b. Tahfidz)
$router->get('/kelola-quran-siswa-paud/target', [QuranSiswaController::class, 'paudTargetList'], [Middleware::permissionRequired('quran_siswa.paud_target.view')]);
$router->get('/kelola-quran-siswa-paud/target/create', [QuranSiswaController::class, 'paudTargetCreate'], [Middleware::permissionRequired('quran_siswa.paud_target.create')]);
$router->post('/kelola-quran-siswa-paud/target/store', [QuranSiswaController::class, 'paudTargetStore'], [Middleware::permissionRequired('quran_siswa.paud_target.create')]);
$router->get('/kelola-quran-siswa-paud/target/manage/{id}', [QuranSiswaController::class, 'paudTargetManage'], [Middleware::permissionRequired('quran_siswa.paud_target.view')]);
$router->get('/kelola-quran-siswa-paud/target/{id}', [QuranSiswaController::class, 'paudTargetManage'], [Middleware::permissionRequired('quran_siswa.paud_target.view')]);
$router->post('/kelola-quran-siswa-paud/target/manage/{id}/store', [QuranSiswaController::class, 'paudTargetSaveMateri'], [Middleware::permissionRequired('quran_siswa.paud_target.update')]);
$router->get('/kelola-quran-siswa-paud/target/edit/{id}', [QuranSiswaController::class, 'paudTargetEdit'], [Middleware::permissionRequired('quran_siswa.paud_target.update')]);
$router->post('/kelola-quran-siswa-paud/target/update/{id}', [QuranSiswaController::class, 'paudTargetUpdate'], [Middleware::permissionRequired('quran_siswa.paud_target.update')]);
$router->post('/kelola-quran-siswa-paud/target/toggle-status/{id}', [QuranSiswaController::class, 'paudTargetToggleStatus'], [Middleware::permissionRequired('quran_siswa.paud_target.update')]);
$router->post('/kelola-quran-siswa-paud/target/delete/{id}', [QuranSiswaController::class, 'paudTargetDelete'], [Middleware::permissionRequired('quran_siswa.paud_target.delete')]);

// Fitur 3: Input Penilaian Qur'an PAUD
$router->get('/kelola-quran-siswa-paud/penilaian', [QuranSiswaController::class, 'paudPenilaianIndex'], [Middleware::permissionRequired('quran_siswa.paud_penilaian.view')]);
$router->get('/kelola-quran-siswa-paud/penilaian/{groupId}', [QuranSiswaController::class, 'paudPenilaianForm'], [Middleware::permissionRequired('quran_siswa.paud_penilaian.create')]);
$router->post('/kelola-quran-siswa-paud/penilaian/store/{groupId}', [QuranSiswaController::class, 'paudPenilaianStore'], [Middleware::permissionRequired('quran_siswa.paud_penilaian.create')]);

// Fitur 4: Rekap Nilai Qur'an PAUD
$router->get('/kelola-quran-siswa-paud/rekap', [QuranSiswaController::class, 'paudRekapIndex'], [Middleware::permissionRequired('quran_siswa.paud_rekap.view')]);
$router->get('/kelola-quran-siswa-paud/rekap/cetak', [QuranSiswaController::class, 'paudRekapCetak'], [Middleware::permissionRequired('quran_siswa.paud_rekap.view')]);
$router->get('/kelola-quran-siswa-paud/rekap/export', [QuranSiswaController::class, 'paudRekapExport'], [Middleware::permissionRequired('quran_siswa.paud_rekap.view')]);
$router->get('/kelola-quran-siswa-smp-sma', [QuranSiswaController::class, 'smpSmaIndex'], [Middleware::permissionRequired('quran_siswa.sd_smp.view')]);
$router->get('/kelola-quran-siswa/smp-sma', [QuranSiswaController::class, 'smpSmaIndex'], [Middleware::permissionRequired('quran_siswa.sd_smp.view')]);
$router->post('/kelola-quran-siswa/setoran/store', [QuranSiswaController::class, 'storeSetoran'], [Middleware::permissionRequired('quran_siswa.sd_smp.create')]);
$router->post('/kelola-quran-siswa/setoran/delete/{id}', [QuranSiswaController::class, 'deleteSetoran'], [Middleware::permissionRequired('quran_siswa.sd_smp.delete')]);
$router->get('/kelola-quran-siswa/cetak', [QuranSiswaController::class, 'cetakRekap'], [Middleware::permissionRequired('quran_siswa.sd_smp.view')]);
$router->get('/kelola-quran-siswa/export', [QuranSiswaController::class, 'exportExcel'], [Middleware::permissionRequired('quran_siswa.sd_smp.view')]);
$router->get('/kelola-ujian', [UjianController::class, 'index'], [Middleware::permissionRequired('ujian.view')]);
$router->get('/kelola-quran-pegawai', [QuranPegawaiController::class, 'index'], [Middleware::permissionRequired('quran_pegawai.view')]);
// -- Kelola Cuti Pegawai --
$router->get('/kelola-cuti', [CutiController::class, 'index'], [Middleware::permissionRequired('cuti.pengajuan.view')]);
$router->post('/kelola-cuti/pengajuan/store', [CutiController::class, 'storePengajuan'], [Middleware::permissionRequired('cuti.pengajuan.create')]);
$router->post('/kelola-cuti/pengajuan/approve', [CutiController::class, 'approve'], [Middleware::permissionRequired('cuti.approve')]);
$router->post('/kelola-cuti/pengajuan/delete', [CutiController::class, 'deletePengajuan'], [Middleware::permissionRequired('cuti.approve')]);
$router->post('/kelola-cuti/jenis/store', [CutiController::class, 'storeJenisCuti'], [Middleware::permissionRequired('cuti.master.update')]);
$router->post('/kelola-cuti/jenis/delete', [CutiController::class, 'deleteJenisCuti'], [Middleware::permissionRequired('cuti.master.update')]);
$router->post('/kelola-cuti/saldo/adjust', [CutiController::class, 'adjustSaldo'], [Middleware::permissionRequired('cuti.master.update')]);
$router->post('/kelola-cuti/tambah-bulanan', [CutiController::class, 'triggerTambahBulanan'], [Middleware::permissionRequired('cuti.master.update')]);
$router->post('/kelola-cuti/hierarki/store', [CutiController::class, 'storeHierarki'], [Middleware::permissionRequired('cuti.master.update')]);
$router->post('/kelola-cuti/hierarki/delete', [CutiController::class, 'deleteHierarki'], [Middleware::permissionRequired('cuti.master.update')]);
$router->post('/kelola-cuti/hierarki/reset', [CutiController::class, 'resetHierarki'], [Middleware::permissionRequired('cuti.master.update')]);
$router->get('/kelola-cuti/cetak/{id}', [CutiController::class, 'cetakSurat'], [Middleware::permissionRequired('cuti.pengajuan.view')]);
$router->get('/kelola-cuti/export', [CutiController::class, 'exportCsv'], [Middleware::permissionRequired('cuti.export')]);
$router->get('/kelola-kpi', [KpiController::class, 'index'], [Middleware::permissionRequired('kpi.view')]);
$router->get('/kelola-ibadah-guru', [IbadahGuruController::class, 'index'], [[Middleware::class, 'authRequired']]);
$router->post('/kelola-ibadah-guru/store', [IbadahGuruController::class, 'store'], [[Middleware::class, 'authRequired']]);
$router->post('/kelola-ibadah-guru/delete/{id}', [IbadahGuruController::class, 'delete'], [Middleware::permissionRequired('ibadah_guru.delete')]);
$router->get('/kelola-ibadah-guru/cetak', [IbadahGuruController::class, 'cetak'], [[Middleware::class, 'authRequired']]);

// -- Modul Persuratan Yayasan & Persuratan Sekolah --
$router->get('/persuratan-yayasan', [PersuratanController::class, 'yayasanIndex'], [[Middleware::class, 'authRequired']]);
$router->get('/persuratan-sekolah', [PersuratanController::class, 'sekolahIndex'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/surat/store', [PersuratanController::class, 'storeSurat'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/surat/delete', [PersuratanController::class, 'deleteSurat'], [[Middleware::class, 'authRequired']]);
$router->get('/persuratan/cetak/{id}', [PersuratanController::class, 'cetakSurat'], [[Middleware::class, 'authRequired']]);
$router->get('/persuratan/api/next-nomor', [PersuratanController::class, 'apiNextNomor'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/jenis/store', [PersuratanController::class, 'storeJenis'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/jenis/delete', [PersuratanController::class, 'deleteJenis'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/kop/save', [PersuratanController::class, 'saveKop'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/tahun-aktif/save', [PersuratanController::class, 'saveTahunAktif'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/tahun/store', [PersuratanController::class, 'storeTahun'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/tahun/activate', [PersuratanController::class, 'activateTahun'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/tahun/delete', [PersuratanController::class, 'deleteTahun'], [[Middleware::class, 'authRequired']]);
$router->get('/persuratan/export', [PersuratanController::class, 'exportCsv'], [[Middleware::class, 'authRequired']]);
$router->post('/persuratan/surat/resend-wa', [PersuratanController::class, 'resendApprovalWa'], [[Middleware::class, 'authRequired']]);
$router->get('/persuratan/approve/{token}', [PersuratanController::class, 'approvePublicPage']);
$router->post('/persuratan/approve/{token}', [PersuratanController::class, 'submitPublicApproval']);
// -- Kelola SPMB (Seleksi Penerimaan Murid Baru) --
// 1. Dashboard & Entry Point
$router->get('/kelola-spmb', [SpmbController::class, 'index'], [[Middleware::class, 'authRequired']]);
$router->get('/kelola-spmb/dashboard', [SpmbController::class, 'index'], [Middleware::permissionRequired('spmb.dashboard')]);

// 2. Siswa Baru
$router->get('/kelola-spmb/siswa-baru', [SpmbController::class, 'siswaBaruIndex'], [Middleware::permissionRequired('spmb.siswa_baru.view')]);
$router->get('/kelola-spmb/siswa-baru/get-nomor', [SpmbController::class, 'getNomorAjax'], [Middleware::permissionRequired('spmb.siswa_baru.view')]);
$router->get('/kelola-spmb/siswa-baru/create', [SpmbController::class, 'siswaBaruCreate'], [Middleware::permissionRequired('spmb.siswa_baru.create')]);
$router->post('/kelola-spmb/siswa-baru/store', [SpmbController::class, 'siswaBaruStore'], [Middleware::permissionRequired('spmb.siswa_baru.create')]);
$router->get('/kelola-spmb/siswa-baru/edit/{id}', [SpmbController::class, 'siswaBaruEdit'], [Middleware::permissionRequired('spmb.siswa_baru.update')]);
$router->post('/kelola-spmb/siswa-baru/update/{id}', [SpmbController::class, 'siswaBaruUpdate'], [Middleware::permissionRequired('spmb.siswa_baru.update')]);
$router->get('/kelola-spmb/siswa-baru/detail/{id}', [SpmbController::class, 'siswaBaruDetail'], [Middleware::permissionRequired('spmb.siswa_baru.view')]);
$router->get('/kelola-spmb/siswa-baru/delete/{id}', [SpmbController::class, 'siswaBaruDelete'], [Middleware::permissionRequired('spmb.siswa_baru.delete')]);
$router->post('/kelola-spmb/siswa-baru/status/{id}', [SpmbController::class, 'siswaBaruStatus'], [Middleware::permissionRequired('spmb.siswa_baru.approve')]);
$router->post('/kelola-spmb/siswa-baru/bulk-status', [SpmbController::class, 'siswaBaruBulkStatus'], [Middleware::permissionRequired('spmb.siswa_baru.approve')]);
$router->get('/kelola-spmb/siswa-baru/cetak/{id}', [SpmbController::class, 'siswaBaruCetak'], [Middleware::permissionRequired('spmb.siswa_baru.view')]);
$router->get('/kelola-spmb/siswa-baru/export', [SpmbController::class, 'siswaBaruExport'], [Middleware::permissionRequired('spmb.siswa_baru.export')]);
$router->post('/kelola-spmb/kirim-wa-biodata/{id}', [SpmbController::class, 'kirimWaBiodata'], [Middleware::permissionRequired(['spmb.siswa_baru.update', 'spmb.pindahan.update', 'spmb.daftar_tunggu.update'])]);
$router->post('/kelola-spmb/update-status-biodata/{id}', [SpmbController::class, 'updateStatusBiodata'], [Middleware::permissionRequired(['spmb.siswa_baru.update', 'spmb.pindahan.update', 'spmb.daftar_tunggu.update'])]);

// 3. Pindahan
$router->get('/kelola-spmb/pindahan', [SpmbController::class, 'pindahanIndex'], [Middleware::permissionRequired('spmb.pindahan.view')]);
$router->get('/kelola-spmb/pindahan/get-nomor', [SpmbController::class, 'pindahanGetNomorAjax'], [Middleware::permissionRequired('spmb.pindahan.view')]);
$router->get('/kelola-spmb/pindahan/create', [SpmbController::class, 'pindahanCreate'], [Middleware::permissionRequired('spmb.pindahan.create')]);
$router->post('/kelola-spmb/pindahan/store', [SpmbController::class, 'pindahanStore'], [Middleware::permissionRequired('spmb.pindahan.create')]);
$router->get('/kelola-spmb/pindahan/edit/{id}', [SpmbController::class, 'pindahanEdit'], [Middleware::permissionRequired('spmb.pindahan.update')]);
$router->post('/kelola-spmb/pindahan/update/{id}', [SpmbController::class, 'pindahanUpdate'], [Middleware::permissionRequired('spmb.pindahan.update')]);
$router->get('/kelola-spmb/pindahan/detail/{id}', [SpmbController::class, 'pindahanDetail'], [Middleware::permissionRequired('spmb.pindahan.view')]);
$router->get('/kelola-spmb/pindahan/delete/{id}', [SpmbController::class, 'pindahanDelete'], [Middleware::permissionRequired('spmb.pindahan.delete')]);
$router->post('/kelola-spmb/pindahan/status/{id}', [SpmbController::class, 'pindahanStatus'], [Middleware::permissionRequired('spmb.pindahan.approve')]);
$router->post('/kelola-spmb/pindahan/bulk-status', [SpmbController::class, 'pindahanBulkStatus'], [Middleware::permissionRequired('spmb.pindahan.approve')]);
$router->get('/kelola-spmb/pindahan/cetak/{id}', [SpmbController::class, 'pindahanCetak'], [Middleware::permissionRequired('spmb.pindahan.view')]);
$router->get('/kelola-spmb/pindahan/export', [SpmbController::class, 'pindahanExport'], [Middleware::permissionRequired('spmb.pindahan.export')]);

// 4. Daftar Tunggu
$router->get('/kelola-spmb/daftar-tunggu', [SpmbController::class, 'daftarTungguIndex'], [Middleware::permissionRequired('spmb.daftar_tunggu.view')]);
$router->get('/kelola-spmb/daftar-tunggu/get-nomor', [SpmbController::class, 'daftarTungguGetNomorAjax'], [Middleware::permissionRequired('spmb.daftar_tunggu.view')]);
$router->get('/kelola-spmb/daftar-tunggu/create', [SpmbController::class, 'daftarTungguCreate'], [Middleware::permissionRequired('spmb.daftar_tunggu.create')]);
$router->post('/kelola-spmb/daftar-tunggu/store', [SpmbController::class, 'daftarTungguStore'], [Middleware::permissionRequired('spmb.daftar_tunggu.create')]);
$router->get('/kelola-spmb/daftar-tunggu/edit/{id}', [SpmbController::class, 'daftarTungguEdit'], [Middleware::permissionRequired('spmb.daftar_tunggu.update')]);
$router->post('/kelola-spmb/daftar-tunggu/update/{id}', [SpmbController::class, 'daftarTungguUpdate'], [Middleware::permissionRequired('spmb.daftar_tunggu.update')]);
$router->get('/kelola-spmb/daftar-tunggu/detail/{id}', [SpmbController::class, 'daftarTungguDetail'], [Middleware::permissionRequired('spmb.daftar_tunggu.view')]);
$router->get('/kelola-spmb/daftar-tunggu/delete/{id}', [SpmbController::class, 'daftarTungguDelete'], [Middleware::permissionRequired('spmb.daftar_tunggu.delete')]);
$router->post('/kelola-spmb/daftar-tunggu/status/{id}', [SpmbController::class, 'daftarTungguStatus'], [Middleware::permissionRequired('spmb.daftar_tunggu.approve')]);
$router->post('/kelola-spmb/daftar-tunggu/promosikan/{id}', [SpmbController::class, 'daftarTungguPromosikan'], [Middleware::permissionRequired('spmb.daftar_tunggu.update')]);
$router->get('/kelola-spmb/daftar-tunggu/cetak/{id}', [SpmbController::class, 'daftarTungguCetak'], [Middleware::permissionRequired('spmb.daftar_tunggu.view')]);
$router->get('/kelola-spmb/daftar-tunggu/export', [SpmbController::class, 'daftarTungguExport'], [Middleware::permissionRequired('spmb.daftar_tunggu.export')]);
$router->post('/kelola-spmb/daftar-tunggu/send-wa/{id}', [SpmbController::class, 'daftarTungguSendWaSingle'], [Middleware::permissionRequired('spmb.daftar_tunggu.update')]);
$router->post('/kelola-spmb/daftar-tunggu/broadcast-send-single', [SpmbController::class, 'daftarTungguSendWaSingle'], [Middleware::permissionRequired('spmb.daftar_tunggu.update')]);
$router->get('/kelola-spmb/daftar-tunggu/get-template-wa', [SpmbController::class, 'daftarTungguGetTemplateWa'], [Middleware::permissionRequired('spmb.daftar_tunggu.view')]);

// 5. Pengaturan Tahun Group SPMB
$router->get('/kelola-spmb/pengaturan', [SpmbController::class, 'pengaturanIndex'], [Middleware::permissionRequired('spmb.pengaturan.view')]);
$router->post('/kelola-spmb/pengaturan/store', [SpmbController::class, 'pengaturanStore'], [Middleware::permissionRequired('spmb.pengaturan.create')]);
$router->post('/kelola-spmb/pengaturan/update/{id}', [SpmbController::class, 'pengaturanUpdate'], [Middleware::permissionRequired('spmb.pengaturan.update')]);
$router->get('/kelola-spmb/pengaturan/set-active/{id}', [SpmbController::class, 'pengaturanSetActive'], [Middleware::permissionRequired('spmb.pengaturan.update')]);
$router->get('/kelola-spmb/pengaturan/delete/{id}', [SpmbController::class, 'pengaturanDelete'], [Middleware::permissionRequired('spmb.pengaturan.delete')]);
$router->post('/kelola-spmb/pengaturan/upload-kop', [SpmbController::class, 'pengaturanUploadKop'], [Middleware::permissionRequired('spmb.pengaturan.update')]);
$router->post('/kelola-spmb/pengaturan/hapus-kop', [SpmbController::class, 'pengaturanHapusKop'], [Middleware::permissionRequired('spmb.pengaturan.update')]);
$router->post('/kelola-spmb/pengaturan/save-options', [SpmbController::class, 'pengaturanSaveOptions'], [Middleware::permissionRequired('spmb.pengaturan.update')]);
$router->post('/kelola-spmb/pengaturan/upload-contoh-dapodik', [SpmbController::class, 'pengaturanUploadContohDapodik'], [Middleware::permissionRequired('spmb.pengaturan.update')]);

// 6. Surat Pernyataan
$router->get('/kelola-spmb/surat-pernyataan', [SpmbController::class, 'suratPernyataanIndex'], [Middleware::permissionRequired('spmb.surat_pernyataan.view')]);
$router->post('/kelola-spmb/surat-pernyataan/update', [SpmbController::class, 'suratPernyataanUpdate'], [Middleware::permissionRequired('spmb.surat_pernyataan.update')]);
$router->post('/kelola-spmb/surat-pernyataan/upload-kop', [SpmbController::class, 'suratPernyataanUploadKop'], [Middleware::permissionRequired('spmb.surat_pernyataan.update')]);
$router->post('/kelola-spmb/surat-pernyataan/hapus-kop', [SpmbController::class, 'suratPernyataanHapusKop'], [Middleware::permissionRequired('spmb.surat_pernyataan.update')]);
$router->post('/kelola-spmb/surat-pernyataan/reset', [SpmbController::class, 'suratPernyataanReset'], [Middleware::permissionRequired('spmb.surat_pernyataan.update')]);
$router->get('/kelola-spmb/surat-pernyataan/cetak/{id}', [SpmbController::class, 'suratPernyataanCetakAdmin'], [Middleware::permissionRequired('spmb.surat_pernyataan.view')]);
$router->post('/kelola-spmb/surat-pernyataan/reset-ttd/{id}', [SpmbController::class, 'suratPernyataanResetTtd'], [Middleware::permissionRequired('spmb.surat_pernyataan.update')]);
$router->post('/kelola-spmb/kirim-wa-komitmen/{id}', [SpmbController::class, 'kirimWaKomitmen'], [Middleware::permissionRequired('spmb.surat_pernyataan.update')]);
$router->get('/kelola-spmb/verifikasi-berkas/get/{id}', [SpmbController::class, 'getVerifikasiBerkasAjax'], [Middleware::permissionRequired(['spmb.siswa_baru.update', 'spmb.pindahan.update'])]);
$router->post('/kelola-spmb/verifikasi-berkas/submit', [SpmbController::class, 'submitVerifikasiBerkas'], [Middleware::permissionRequired(['spmb.siswa_baru.approve', 'spmb.pindahan.approve'])]);
$router->get('/kelola-spmb/pengumuman-kelulusan/cetak', [SpmbController::class, 'pengumumanKelulusanCetak'], [Middleware::permissionRequired(['spmb.siswa_baru.approve', 'spmb.pindahan.approve'])]);
$router->get('/kelola-spmb/pengumuman-kelulusan/get-teks-wa', [SpmbController::class, 'getTeksPengumumanGrupAjax'], [Middleware::permissionRequired(['spmb.siswa_baru.approve', 'spmb.pindahan.approve'])]);
$router->post('/kelola-spmb/kirim-wa-kelulusan/{id}', [SpmbController::class, 'kirimWaKelulusan'], [Middleware::permissionRequired(['spmb.siswa_baru.approve', 'spmb.pindahan.approve'])]);
$router->post('/kelola-spmb/kirim-wa-kelulusan-bulk', [SpmbController::class, 'kirimWaKelulusanBulk'], [Middleware::permissionRequired(['spmb.siswa_baru.approve', 'spmb.pindahan.approve'])]);

// 7. Screening Psikolog (Galeri File PDF)
$router->get('/kelola-spmb/screening-psikolog', [SpmbController::class, 'screeningPsikologIndex'], [Middleware::permissionRequired('spmb.psikolog.view')]);
$router->post('/kelola-spmb/screening-psikolog/upload', [SpmbController::class, 'screeningPsikologUpload'], [Middleware::permissionRequired('spmb.psikolog.create')]);
$router->post('/kelola-spmb/screening-psikolog/hapus/{id}', [SpmbController::class, 'screeningPsikologHapus'], [Middleware::permissionRequired('spmb.psikolog.delete')]);
$router->get('/kelola-spmb/screening-psikolog/view/{id}', [SpmbController::class, 'screeningPsikologView'], [Middleware::permissionRequired('spmb.psikolog.view')]);
$router->get('/kelola-spmb/screening-psikolog/download/{id}', [SpmbController::class, 'screeningPsikologDownload'], [Middleware::permissionRequired('spmb.psikolog.view')]);
$router->get('/kelola-spmb/screening-psikolog/export', [SpmbController::class, 'screeningPsikologExport'], [Middleware::permissionRequired('spmb.psikolog.export')]);
$router->get('/kelola-spmb/screening-psikolog/broadcast-data', [SpmbController::class, 'screeningPsikologBroadcastData'], [Middleware::permissionRequired('spmb.psikolog.update')]);
$router->post('/kelola-spmb/screening-psikolog/send-wa/{id}', [SpmbController::class, 'screeningPsikologSendWaSingle'], [Middleware::permissionRequired('spmb.psikolog.update')]);
$router->post('/kelola-spmb/screening-psikolog/broadcast-send-single', [SpmbController::class, 'screeningPsikologSendWaSingle'], [Middleware::permissionRequired('spmb.psikolog.update')]);

// 8. Observasi Awal SPMB
$router->get('/kelola-spmb/observasi-awal', [SpmbController::class, 'observasiAwalIndex'], [Middleware::permissionRequired('spmb.observasi.view')]);
$router->get('/kelola-spmb/observasi-awal/pengaturan', [SpmbController::class, 'observasiAwalPengaturan'], [Middleware::permissionRequired('spmb.observasi.create')]);
$router->post('/kelola-spmb/observasi-awal/group/store', [SpmbController::class, 'observasiAwalGroupStore'], [Middleware::permissionRequired('spmb.observasi.create')]);
$router->post('/kelola-spmb/observasi-awal/group/delete/{id}', [SpmbController::class, 'observasiAwalGroupDelete'], [Middleware::permissionRequired('spmb.observasi.delete')]);
$router->post('/kelola-spmb/observasi-awal/pertanyaan/store', [SpmbController::class, 'observasiAwalPertanyaanStore'], [Middleware::permissionRequired('spmb.observasi.create')]);
$router->post('/kelola-spmb/observasi-awal/pertanyaan/delete/{id}', [SpmbController::class, 'observasiAwalPertanyaanDelete'], [Middleware::permissionRequired('spmb.observasi.delete')]);
$router->get('/kelola-spmb/observasi-awal/detail/{id}', [SpmbController::class, 'observasiAwalDetail'], [Middleware::permissionRequired('spmb.observasi.view')]);
$router->post('/kelola-spmb/observasi-awal/send-wa/{id}', [SpmbController::class, 'observasiAwalSendWaSingle'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->get('/kelola-spmb/observasi-awal/broadcast-data', [SpmbController::class, 'observasiAwalBroadcastData'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->post('/kelola-spmb/observasi-awal/broadcast-send-single', [SpmbController::class, 'observasiAwalSendWaSingle'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->post('/kelola-spmb/observasi-awal/send-wa-group/{id}', [SpmbController::class, 'observasiAwalSendWaGroupSingle'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->get('/kelola-spmb/observasi-awal/broadcast-group-data', [SpmbController::class, 'observasiAwalBroadcastGroupData'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->post('/kelola-spmb/observasi-awal/broadcast-group-send', [SpmbController::class, 'observasiAwalBroadcastGroupSendSingle'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->post('/kelola-spmb/observasi-awal/toggle-group-status/{id}', [SpmbController::class, 'observasiAwalToggleGroupStatus'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->get('/kelola-spmb/observasi-awal/export', [SpmbController::class, 'observasiAwalExport'], [Middleware::permissionRequired('spmb.observasi.export')]);
$router->get('/kelola-spmb/observasi-awal/cetak-rekap', [SpmbController::class, 'observasiAwalCetakRekap'], [Middleware::permissionRequired('spmb.observasi.view')]);
$router->get('/kelola-spmb/observasi-awal/cetak/{id}', [SpmbController::class, 'observasiAwalCetak'], [Middleware::permissionRequired('spmb.observasi.view')]);

// 9. Kartu Observasi, QR Absen, Ruangan & Broadcast Jadwal Sesi
$router->get('/kelola-spmb/kartu-observasi', [SpmbController::class, 'kartuObservasiIndex'], [Middleware::permissionRequired('spmb.observasi.view')]);
$router->post('/kelola-spmb/kartu-observasi/sync', [SpmbController::class, 'kartuObservasiSync'], [Middleware::permissionRequired('spmb.observasi.create')]);
$router->post('/kelola-spmb/kartu-observasi/ruangan/store', [SpmbController::class, 'kartuObservasiRuanganStore'], [Middleware::permissionRequired('spmb.observasi.create')]);
$router->post('/kelola-spmb/kartu-observasi/ruangan/delete/{id}', [SpmbController::class, 'kartuObservasiRuanganDelete'], [Middleware::permissionRequired('spmb.observasi.delete')]);
$router->post('/kelola-spmb/kartu-observasi/bagi-otomatis', [SpmbController::class, 'kartuObservasiBagiOtomatis'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->post('/kelola-spmb/kartu-observasi/assign-manual', [SpmbController::class, 'kartuObservasiAssignManual'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->post('/kelola-spmb/kartu-observasi/scan', [SpmbController::class, 'kartuObservasiScan'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->post('/kelola-spmb/kartu-observasi/reset-absen/{id}', [SpmbController::class, 'kartuObservasiResetAbsen'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->get('/kelola-spmb/kartu-observasi/cetak', [SpmbController::class, 'kartuObservasiCetak'], [Middleware::permissionRequired('spmb.observasi.view')]);
$router->get('/kelola-spmb/kartu-observasi/broadcast-data', [SpmbController::class, 'kartuObservasiBroadcastData'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->post('/kelola-spmb/kartu-observasi/broadcast-send', [SpmbController::class, 'kartuObservasiBroadcastSendSingle'], [Middleware::permissionRequired('spmb.observasi.update')]);
$router->get('/kelola-spmb/kartu-observasi/laporan-cetak', [SpmbController::class, 'kartuObservasiLaporanCetak'], [Middleware::permissionRequired('spmb.observasi.view')]);
$router->get('/kelola-spmb/kartu-observasi/laporan-export', [SpmbController::class, 'kartuObservasiLaporanExport'], [Middleware::permissionRequired('spmb.observasi.view')]);
$router->get('/kelola-spmb/kartu-observasi/absen-qr/{token}', [SpmbController::class, 'kartuObservasiAbsenQrDirect']);
$router->get('/kelola-spmb/kartu-observasi/publik/{token}', [SpmbController::class, 'kartuObservasiPublik']);
$router->get('/kelola-pelanggaran-siswa', [PelanggaranSiswaController::class, 'index'], [Middleware::permissionRequired('pelanggaran_siswa.view')]);
$router->get('/kelola-izin-guru', [IzinGuruController::class, 'index'], [Middleware::permissionRequired('izin_guru.view')]);
$router->get('/kelola-buku-angkatan-siswa', [BukuAngkatanSiswaController::class, 'index'], [Middleware::permissionRequired('buku_angkatan.view')]);

// -- Kelola Sarpras (Sarana & Prasarana) --
$router->get('/kelola-sarpras', [SarprasController::class, 'index'], [[Middleware::class, 'authRequired']]);
$router->get('/kelola-sarpras/dashboard', [SarprasController::class, 'index'], [Middleware::permissionRequired('sarpras.dashboard')]);
$router->get('/kelola-sarpras/export', [SarprasController::class, 'exportExcel'], [Middleware::permissionRequired('sarpras.barang.export')]);
$router->get('/kelola-sarpras/cetak-label/print', [SarprasController::class, 'cetakLabelPrint'], [Middleware::permissionRequired('sarpras.label.view')]);
$router->get('/kelola-sarpras/cetak-label', [SarprasController::class, 'cetakLabelIndex'], [Middleware::permissionRequired('sarpras.label.view')]);

// Barang / Aset
$router->get('/kelola-sarpras/barang', [SarprasController::class, 'barangList'], [Middleware::permissionRequired('sarpras.barang.view')]);
$router->get('/kelola-sarpras/barang/create', [SarprasController::class, 'barangCreate'], [Middleware::permissionRequired('sarpras.barang.create')]);
$router->post('/kelola-sarpras/barang/store', [SarprasController::class, 'barangStore'], [Middleware::permissionRequired('sarpras.barang.create')]);
$router->get('/kelola-sarpras/barang/edit/{id}', [SarprasController::class, 'barangEdit'], [Middleware::permissionRequired('sarpras.barang.update')]);
$router->post('/kelola-sarpras/barang/update/{id}', [SarprasController::class, 'barangUpdate'], [Middleware::permissionRequired('sarpras.barang.update')]);
$router->post('/kelola-sarpras/barang/delete/{id}', [SarprasController::class, 'barangDelete'], [Middleware::permissionRequired('sarpras.barang.delete')]);

// Peminjaman
$router->get('/kelola-sarpras/peminjaman', [SarprasController::class, 'peminjamanList'], [Middleware::permissionRequired('sarpras.peminjaman.view')]);
$router->post('/kelola-sarpras/peminjaman/store', [SarprasController::class, 'peminjamanStore'], [Middleware::permissionRequired('sarpras.peminjaman.create')]);
$router->post('/kelola-sarpras/peminjaman/update/{id}', [SarprasController::class, 'peminjamanUpdate'], [Middleware::permissionRequired('sarpras.peminjaman.update')]);
$router->post('/kelola-sarpras/peminjaman/kembali/{id}', [SarprasController::class, 'peminjamanKembali'], [Middleware::permissionRequired('sarpras.peminjaman.update')]);

// Pemeliharaan
$router->get('/kelola-sarpras/pemeliharaan', [SarprasController::class, 'pemeliharaanList'], [Middleware::permissionRequired('sarpras.perbaikan.view')]);
$router->get('/kelola-sarpras/pemeliharaan/barang-by-ruangan', [SarprasController::class, 'getBarangByRuanganJson'], [Middleware::permissionRequired('sarpras.perbaikan.view')]);
$router->post('/kelola-sarpras/pemeliharaan/store', [SarprasController::class, 'pemeliharaanStore'], [Middleware::permissionRequired('sarpras.perbaikan.create')]);
$router->post('/kelola-sarpras/pemeliharaan/update-status/{id}', [SarprasController::class, 'pemeliharaanUpdate'], [Middleware::permissionRequired('sarpras.perbaikan.verify')]);
$router->post('/kelola-sarpras/pemeliharaan/verifikasi/{id}', [SarprasController::class, 'pemeliharaanVerifikasi'], [Middleware::permissionRequired('sarpras.perbaikan.verify')]);
$router->post('/kelola-sarpras/pemeliharaan/delete/{id}', [SarprasController::class, 'pemeliharaanDelete'], [Middleware::permissionRequired('sarpras.perbaikan.delete')]);
$router->get('/kelola-sarpras/pemeliharaan/cetak/{id}', [SarprasController::class, 'pemeliharaanCetak'], [Middleware::permissionRequired('sarpras.perbaikan.view')]);

// Pengajuan Pembelian Baru Sarpras
$router->get('/kelola-sarpras/pengajuan', [SarprasController::class, 'pengajuanList'], [Middleware::permissionRequired('sarpras.pengajuan.view')]);
$router->post('/kelola-sarpras/pengajuan/store', [SarprasController::class, 'pengajuanStore'], [Middleware::permissionRequired('sarpras.pengajuan.create')]);
$router->post('/kelola-sarpras/pengajuan/verifikasi/{id}', [SarprasController::class, 'pengajuanVerifikasi'], [Middleware::permissionRequired('sarpras.pengajuan.verify')]);
$router->post('/kelola-sarpras/pengajuan/delete/{id}', [SarprasController::class, 'pengajuanDelete'], [Middleware::permissionRequired('sarpras.pengajuan.delete')]);
$router->get('/kelola-sarpras/pengajuan/cetak/{id}', [SarprasController::class, 'pengajuanCetak'], [Middleware::permissionRequired('sarpras.pengajuan.view')]);

// Ruangan
$router->get('/kelola-sarpras/ruangan', [SarprasController::class, 'ruanganList'], [Middleware::permissionRequired('sarpras.aset.view')]);
$router->post('/kelola-sarpras/ruangan/store', [SarprasController::class, 'ruanganStore'], [Middleware::permissionRequired('sarpras.aset.create')]);
$router->post('/kelola-sarpras/ruangan/delete/{id}', [SarprasController::class, 'ruanganDelete'], [Middleware::permissionRequired('sarpras.aset.delete')]);
$router->get('/kelola-sarpras/ruangan/detail/{id}', [SarprasController::class, 'ruanganDetail'], [Middleware::permissionRequired('sarpras.aset.view')]);
$router->post('/kelola-sarpras/distribusi/store', [SarprasController::class, 'distribusiStore'], [Middleware::permissionRequired('sarpras.barang.create')]);
$router->post('/kelola-sarpras/distribusi/delete/{id}', [SarprasController::class, 'distribusiDelete'], [Middleware::permissionRequired('sarpras.barang.delete')]);

// Kategori
$router->get('/kelola-sarpras/kategori', [SarprasController::class, 'kategoriList'], [Middleware::permissionRequired('sarpras.referensi.view')]);
$router->post('/kelola-sarpras/kategori/store', [SarprasController::class, 'kategoriStore'], [Middleware::permissionRequired('sarpras.referensi.create')]);
$router->post('/kelola-sarpras/kategori/delete/{id}', [SarprasController::class, 'kategoriDelete'], [Middleware::permissionRequired('sarpras.referensi.delete')]);

// Penyusutan Aset
$router->get('/kelola-sarpras/penyusutan', [SarprasController::class, 'penyusutanList'], [Middleware::permissionRequired('sarpras.penyusutan.view')]);
$router->get('/kelola-sarpras/penyusutan/cetak', [SarprasController::class, 'penyusutanCetak'], [Middleware::permissionRequired('sarpras.penyusutan.view')]);
$router->get('/kelola-sarpras/penyusutan/export-excel', [SarprasController::class, 'penyusutanExportExcel'], [Middleware::permissionRequired('sarpras.penyusutan.view')]);

// Laporan Aset (Kondisi & Filter)
$router->get('/kelola-sarpras/laporan', [SarprasController::class, 'laporanList'], [Middleware::permissionRequired('sarpras.laporan.view')]);
$router->get('/kelola-sarpras/laporan/cetak', [SarprasController::class, 'laporanCetak'], [Middleware::permissionRequired('sarpras.laporan.export')]);

// Data Aset: Hierarki (Accordion Tanah -> Bangunan -> Ruang)
$router->get('/kelola-sarpras/data-aset', [SarprasController::class, 'dataAset'], [Middleware::permissionRequired('sarpras.aset.view')]);
$router->get('/kelola-sarpras/aset', [SarprasController::class, 'dataAset'], [Middleware::permissionRequired('sarpras.aset.view')]);

// Data Aset: Tanah
$router->get('/kelola-sarpras/tanah', [SarprasController::class, 'tanahList'], [Middleware::permissionRequired('sarpras.aset.view')]);
$router->post('/kelola-sarpras/tanah/store', [SarprasController::class, 'tanahStore'], [Middleware::permissionRequired('sarpras.aset.create')]);
$router->post('/kelola-sarpras/tanah/update/{id}', [SarprasController::class, 'tanahUpdate'], [Middleware::permissionRequired('sarpras.aset.update')]);
$router->post('/kelola-sarpras/tanah/delete/{id}', [SarprasController::class, 'tanahDelete'], [Middleware::permissionRequired('sarpras.aset.delete')]);
$router->get('/kelola-sarpras/tanah/cetak-pdf', [SarprasController::class, 'tanahCetakPdf'], [Middleware::permissionRequired('sarpras.aset.view')]);

// Data Aset: Bangunan
$router->get('/kelola-sarpras/bangunan', [SarprasController::class, 'bangunanList'], [Middleware::permissionRequired('sarpras.aset.view')]);
$router->post('/kelola-sarpras/bangunan/store', [SarprasController::class, 'bangunanStore'], [Middleware::permissionRequired('sarpras.aset.create')]);
$router->post('/kelola-sarpras/bangunan/update/{id}', [SarprasController::class, 'bangunanUpdate'], [Middleware::permissionRequired('sarpras.aset.update')]);
$router->post('/kelola-sarpras/bangunan/delete/{id}', [SarprasController::class, 'bangunanDelete'], [Middleware::permissionRequired('sarpras.aset.delete')]);

// Referensi Sarpras (Golongan, Kode Kelompok, Asal Anggaran)
$router->get('/kelola-sarpras/referensi', [SarprasController::class, 'referensiList'], [Middleware::permissionRequired('sarpras.referensi.view')]);
$router->post('/kelola-sarpras/referensi/golongan/store', [SarprasController::class, 'golonganStore'], [Middleware::permissionRequired('sarpras.referensi.create')]);
$router->post('/kelola-sarpras/referensi/golongan/delete/{id}', [SarprasController::class, 'golonganDelete'], [Middleware::permissionRequired('sarpras.referensi.delete')]);
$router->post('/kelola-sarpras/referensi/kelompok/store', [SarprasController::class, 'kelompokStore'], [Middleware::permissionRequired('sarpras.referensi.create')]);
$router->post('/kelola-sarpras/referensi/kelompok/delete/{id}', [SarprasController::class, 'kelompokDelete'], [Middleware::permissionRequired('sarpras.referensi.delete')]);
$router->post('/kelola-sarpras/referensi/asal-anggaran/store', [SarprasController::class, 'asalAnggaranStore'], [Middleware::permissionRequired('sarpras.referensi.create')]);
$router->post('/kelola-sarpras/referensi/asal-anggaran/delete/{id}', [SarprasController::class, 'asalAnggaranDelete'], [Middleware::permissionRequired('sarpras.referensi.delete')]);
$router->post('/kelola-sarpras/referensi/laporan/store', [SarprasController::class, 'pengaturanLaporanStore'], [Middleware::permissionRequired('sarpras.referensi.update')]);

// Pengaturan Sistem
$router->get('/pengaturan-sistem', [SettingsController::class, 'index'], [[Middleware::class, 'authRequired']]);
$router->get('/pengaturan-sistem/identitas', [SettingsController::class, 'identitas'], [Middleware::permissionRequired('settings.identitas.view')]);
$router->get('/pengaturan-sistem/akademik', [SettingsController::class, 'akademik'], [Middleware::permissionRequired('settings.identitas.view')]);
$router->post('/pengaturan-sistem/akademik/store', [SettingsController::class, 'storeAkademik'], [Middleware::permissionRequired('settings.identitas.update')]);
$router->get('/pengaturan-sistem/akademik/edit/{id}', [SettingsController::class, 'editAkademik'], [Middleware::permissionRequired('settings.identitas.update')]);
$router->post('/pengaturan-sistem/akademik/update/{id}', [SettingsController::class, 'updateAkademik'], [Middleware::permissionRequired('settings.identitas.update')]);
$router->post('/pengaturan-sistem/akademik/set-aktif/{id}', [SettingsController::class, 'setAktifAkademik'], [Middleware::permissionRequired('settings.identitas.update')]);
$router->post('/pengaturan-sistem/akademik/delete/{id}', [SettingsController::class, 'deleteAkademik'], [Middleware::permissionRequired('settings.identitas.update')]);

$router->get('/pengaturan-sistem/master-pegawai', [SettingsController::class, 'masterPegawai'], [Middleware::permissionRequired('settings.master.view')]);
$router->post('/pengaturan-sistem/master-pegawai/unit-tugas/store', [SettingsController::class, 'storeUnitTugas'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pegawai/unit-tugas/delete/{id}', [SettingsController::class, 'deleteUnitTugas'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pegawai/jabatan/store', [SettingsController::class, 'storeJabatan'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pegawai/jabatan/update-kategori/{id}', [SettingsController::class, 'updateKategoriJabatan'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pegawai/jabatan/delete/{id}', [SettingsController::class, 'deleteJabatan'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pegawai/status-kerja/store', [SettingsController::class, 'storeStatusKerja'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pegawai/status-kerja/delete/{id}', [SettingsController::class, 'deleteStatusKerja'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pegawai/jenis-pegawai/store', [SettingsController::class, 'storeJenisPegawai'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pegawai/jenis-pegawai/delete/{id}', [SettingsController::class, 'deleteJenisPegawai'], [Middleware::permissionRequired('settings.master.update')]);

$router->get('/pengaturan-sistem/master-pembelajaran', [SettingsController::class, 'masterPembelajaran'], [Middleware::permissionRequired('settings.master.view')]);
$router->post('/pengaturan-sistem/master-pembelajaran/store', [SettingsController::class, 'storeMataPelajaran'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pembelajaran/update/{id}', [SettingsController::class, 'updateMataPelajaran'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pembelajaran/toggle-aktif/{id}', [SettingsController::class, 'toggleAktifMataPelajaran'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pembelajaran/delete/{id}', [SettingsController::class, 'deleteMataPelajaran'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pembelajaran/bulk-delete', [SettingsController::class, 'bulkDeleteMataPelajaran'], [Middleware::permissionRequired('settings.master.update')]);
$router->post('/pengaturan-sistem/master-pembelajaran/delete-all', [SettingsController::class, 'deleteAllMataPelajaran'], [Middleware::permissionRequired('settings.master.update')]);

$router->get('/pengaturan-sistem/reset-data', [SettingsController::class, 'resetDataView'], [Middleware::permissionRequired('settings.reset')]);
$router->post('/pengaturan-sistem/update', [SettingsController::class, 'update'], [Middleware::permissionRequired('settings.identitas.update')]);
$router->post('/pengaturan-sistem/reset-data/process', [SettingsController::class, 'resetData'], [Middleware::permissionRequired('settings.reset')]);
$router->post('/pengaturan-sistem/reset', [SettingsController::class, 'resetData'], [Middleware::permissionRequired('settings.reset')]);

// -- Server WA Gateway eportal --
$router->get('/pengaturan-sistem/wa-gateway', [SettingsController::class, 'waGateway'], [Middleware::permissionRequired('settings.wa.view')]);
$router->get('/pengaturan-sistem/wa-gateway/status-ajax', [SettingsController::class, 'waStatusAjax'], [Middleware::permissionRequired('settings.wa.view')]);
$router->get('/pengaturan-sistem/wa-gateway/qr-ajax', [SettingsController::class, 'waQrAjax'], [Middleware::permissionRequired('settings.wa.view')]);
$router->post('/pengaturan-sistem/wa-gateway/logout-ajax', [SettingsController::class, 'waLogoutAjax'], [Middleware::permissionRequired('settings.wa.update')]);
$router->post('/pengaturan-sistem/wa-gateway/start-server', [SettingsController::class, 'waStartServerAjax'], [Middleware::permissionRequired('settings.wa.update')]);
$router->post('/pengaturan-sistem/wa-gateway/update', [SettingsController::class, 'updateWaGateway'], [Middleware::permissionRequired('settings.wa.update')]);
$router->post('/pengaturan-sistem/wa-gateway/test', [SettingsController::class, 'testSendWaGateway'], [Middleware::permissionRequired('settings.wa.update')]);
$router->post('/pengaturan-sistem/wa-gateway/clear-logs', [SettingsController::class, 'clearWaLogs'], [Middleware::permissionRequired('settings.wa.update')]);

// -- Log Aktivitas Sistem --
$router->get('/pengaturan-sistem/log-aktivitas', [SettingsController::class, 'logAktivitas'], [Middleware::permissionRequired('settings.logs.view')]);
$router->get('/pengaturan-sistem/log-aktivitas/export', [SettingsController::class, 'exportLogAktivitas'], [Middleware::permissionRequired('settings.logs.view')]);
$router->post('/pengaturan-sistem/log-aktivitas/clear', [SettingsController::class, 'clearLogAktivitas'], [Middleware::permissionRequired('settings.reset')]);

// -- Portal Guru Mobile (PWA) Auth & Routes --
$router->get('/mobile/login', [PortalGuruAuthController::class, 'showLogin']);
$router->post('/mobile/login', [PortalGuruAuthController::class, 'login']);
$router->get('/mobile/logout', [PortalGuruAuthController::class, 'logout']);
$router->post('/mobile/api/token-login', [PortalGuruAuthController::class, 'tokenLogin']);
$router->get('/mobile', [PortalGuruController::class, 'beranda'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/portal-guru', function() {
    header('Location: ' . url('mobile'), true, 302);
    exit;
});
$router->get('/guru', function() {
    header('Location: ' . url('mobile'), true, 302);
    exit;
});
$router->get('/mobile/absen', [PortalGuruController::class, 'absen'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/rekap-absen', [PortalGuruController::class, 'rekapAbsen'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/api/absen/today', [PortalGuruController::class, 'apiAbsenToday'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/api/absen/checkin', [PortalGuruController::class, 'apiCheckin'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/api/absen/checkout', [PortalGuruController::class, 'apiCheckout'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/jurnal', [PortalGuruController::class, 'jurnal'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/rekap-jurnal', [PortalGuruController::class, 'rekapJurnal'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/jurnal/simpan', [PortalGuruController::class, 'simpanJurnal'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/jurnal-staff/simpan', [PortalGuruController::class, 'simpanJurnalStaff'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/jurnal-staff/hapus', [PortalGuruController::class, 'hapusJurnalStaff'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/api/jurnal/detail/{id}', [PortalGuruController::class, 'apiJurnalDetail'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/api/siswa-kelas', [PortalGuruController::class, 'apiSiswaKelas'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/kelas', [PortalGuruController::class, 'kelas'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/absensi-kelas', [PortalGuruController::class, 'absensiKelas'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/absensi-kelas/simpan', [PortalGuruController::class, 'simpanAbsensiKelas'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/murid', [PortalGuruController::class, 'murid'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/profil', [PortalGuruController::class, 'profil'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/notifikasi', [PortalGuruController::class, 'notifikasi'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/materi', [PortalGuruController::class, 'materi'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/buat-tugas', [PortalGuruController::class, 'buatTugas'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/pesan-kelas', [PortalGuruController::class, 'pesanKelas'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/bank-soal', [PortalGuruController::class, 'bankSoal'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/quran', [PortalGuruController::class, 'quran'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/dzikir', [PortalGuruController::class, 'dzikir'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/tasbih', [PortalGuruController::class, 'tasbih'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/tasbih/simpan-kategori', [PortalGuruController::class, 'simpanKategoriTasbih'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/tasbih/hapus-kategori', [PortalGuruController::class, 'hapusKategoriTasbih'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/tasbih/simpan-sesi', [PortalGuruController::class, 'simpanSesiTasbih'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/api/tasbih/riwayat', [PortalGuruController::class, 'apiTasbihRiwayat'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/api/ibadah/today', [PortalGuruController::class, 'apiIbadahToday'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/ibadah/simpan', [PortalGuruController::class, 'simpanIbadah'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/keterlambatan-siswa', [PortalGuruController::class, 'keterlambatanSiswa'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/izin', [PortalGuruController::class, 'izin'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/izin/submit', [PortalGuruController::class, 'submitIzin'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->get('/mobile/cuti', [PortalGuruController::class, 'cuti'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/cuti/submit', [PortalGuruController::class, 'submitCuti'], [[PortalGuruAuthController::class, 'authRequired']]);
$router->post('/mobile/cuti/cancel', [PortalGuruController::class, 'cancelCuti'], [[PortalGuruAuthController::class, 'authRequired']]);

// ==============================================================================
// MOBILE SARPRAS PWA ROUTES
// ==============================================================================

$router->get('/mobile-sarpras/login', [MobileSarprasAuthController::class, 'showLogin'], [[MobileSarprasAuthController::class, 'guestOnly']]);
$router->post('/mobile-sarpras/login', [MobileSarprasAuthController::class, 'login']);
$router->get('/mobile-sarpras/logout', [MobileSarprasAuthController::class, 'logout']);
$router->post('/mobile-sarpras/api/token-login', [MobileSarprasAuthController::class, 'tokenLogin']);

$router->get('/mobile-sarpras', [MobileSarprasController::class, 'dashboard'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/scan', [MobileSarprasController::class, 'scan'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/detail/{id}', [MobileSarprasController::class, 'detail'], [[MobileSarprasAuthController::class, 'authRequired']]);

// Form & List (Reuse logic, but mobile views)
$router->get('/mobile-sarpras/inventaris', [MobileSarprasController::class, 'inventarisList'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/tanah', [MobileSarprasController::class, 'tanahList'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/bangunan', [MobileSarprasController::class, 'bangunanList'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/ruangan', [MobileSarprasController::class, 'ruanganList'], [[MobileSarprasAuthController::class, 'authRequired']]);

$router->get('/mobile-sarpras/inventaris/tambah', [MobileSarprasController::class, 'inventarisTambah'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/inventaris/edit/{id}', [MobileSarprasController::class, 'inventarisEdit'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/tanah/tambah', [MobileSarprasController::class, 'tanahTambah'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/tanah/edit/{id}', [MobileSarprasController::class, 'tanahEdit'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/bangunan/tambah', [MobileSarprasController::class, 'bangunanTambah'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/bangunan/edit/{id}', [MobileSarprasController::class, 'bangunanEdit'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/ruangan/tambah', [MobileSarprasController::class, 'ruanganTambah'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/ruangan/edit/{id}', [MobileSarprasController::class, 'ruanganEdit'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/ruangan/detail/{id}', [MobileSarprasController::class, 'ruanganDetail'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->post('/mobile-sarpras/distribusi/store', [SarprasController::class, 'distribusiStore'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->post('/mobile-sarpras/distribusi/delete/{id}', [SarprasController::class, 'distribusiDelete'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/maintenance', [MobileSarprasController::class, 'maintenanceList'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/maintenance/tambah', [MobileSarprasController::class, 'maintenanceTambah'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/peminjaman', [MobileSarprasController::class, 'peminjamanList'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/peminjaman/tambah', [MobileSarprasController::class, 'peminjamanTambah'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/pengajuan/tambah', [MobileSarprasController::class, 'pengajuanTambah'], [[MobileSarprasAuthController::class, 'authRequired']]);

$router->get('/mobile-sarpras/api/barang-by-kode', [MobileSarprasController::class, 'apiGetBarangByKode'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/api/scan-barang', [MobileSarprasController::class, 'apiScanBarang'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->get('/mobile-sarpras/api/barang-by-ruangan', [MobileSarprasController::class, 'apiGetBarangByRuangan'], [[MobileSarprasAuthController::class, 'authRequired']]);

$router->post('/mobile-sarpras/inventaris/store', [SarprasController::class, 'barangStore'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->post('/mobile-sarpras/inventaris/update/{id}', [SarprasController::class, 'barangUpdate'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->post('/mobile-sarpras/maintenance/store', [MobileSarprasController::class, 'maintenanceStore'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->post('/mobile-sarpras/peminjaman/store', [MobileSarprasController::class, 'peminjamanStore'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->post('/mobile-sarpras/peminjaman/update/{id}', [MobileSarprasController::class, 'peminjamanUpdate'], [[MobileSarprasAuthController::class, 'authRequired']]);
$router->post('/mobile-sarpras/pengajuan/store', [MobileSarprasController::class, 'pengajuanStore'], [[MobileSarprasAuthController::class, 'authRequired']]);

$router->post('/mobile-sarpras/action/subscribe-push', [MobileSarprasController::class, 'subscribePush'], [[MobileSarprasAuthController::class, 'authRequired']]);

$sarprasManifestHandler = function() {
    header('Content-Type: application/manifest+json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    $sarprasIconVer = file_exists(PUBLIC_PATH . '/images/pwa/sarpras-icon-192.png') 
        ? filemtime(PUBLIC_PATH . '/images/pwa/sarpras-icon-192.png') 
        : '1';

    $manifest = [
        'name' => 'Sarpras Mobile BIP',
        'short_name' => 'Sarpras Mobile',
        'description' => 'Aplikasi Manajemen Sarana dan Prasarana, Inventaris Aset & QR Scanner',
        'id' => 'portal-bip-sarpras-mobile',
        'start_url' => url('mobile-sarpras/') . '?utm_source=pwa_sarpras',
        'scope' => url('mobile-sarpras/'),
        'display' => 'standalone',
        'display_override' => ['window-controls-overlay', 'standalone', 'minimal-ui'],
        'orientation' => 'portrait-primary',
        'background_color' => '#f8fafc',
        'theme_color' => '#0284c7',
        'lang' => 'id',
        'dir' => 'ltr',
        'categories' => ['business', 'productivity', 'utilities'],
        'prefer_related_applications' => false,
        'icons' => [
            [
                'src' => url('pwa-sarpras-icon.png?size=192&v=' . $sarprasIconVer),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => url('pwa-sarpras-icon.png?size=192&maskable=1&v=' . $sarprasIconVer),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ],
            [
                'src' => url('pwa-sarpras-icon.png?size=512&v=' . $sarprasIconVer),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                'src' => url('pwa-sarpras-icon.png?size=512&maskable=1&v=' . $sarprasIconVer),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable'
            ],
            [
                'src' => url('pwa-sarpras-icon.png?size=180&v=' . $sarprasIconVer),
                'sizes' => '180x180',
                'type' => 'image/png'
            ]
        ],
        'shortcuts' => [
            [
                'name' => 'Scan QR Barang',
                'short_name' => 'Scan QR',
                'description' => 'Pindai barcode/QR label aset inventaris',
                'url' => url('mobile-sarpras/scan'),
                'icons' => [[ 'src' => url('pwa-sarpras-icon.png?size=192&v=' . $sarprasIconVer), 'sizes' => '192x192', 'type' => 'image/png' ]]
            ],
            [
                'name' => 'Daftar Inventaris',
                'short_name' => 'Inventaris',
                'description' => 'Cari dan kelola data aset barang',
                'url' => url('mobile-sarpras/inventaris'),
                'icons' => [[ 'src' => url('pwa-sarpras-icon.png?size=192&v=' . $sarprasIconVer), 'sizes' => '192x192', 'type' => 'image/png' ]]
            ],
            [
                'name' => 'Lapor Perbaikan',
                'short_name' => 'Perbaikan',
                'description' => 'Input laporan kerusakan sarpras baru',
                'url' => url('mobile-sarpras/maintenance/tambah'),
                'icons' => [[ 'src' => url('pwa-sarpras-icon.png?size=192&v=' . $sarprasIconVer), 'sizes' => '192x192', 'type' => 'image/png' ]]
            ],
            [
                'name' => 'Peminjaman Aset',
                'short_name' => 'Pinjam',
                'description' => 'Catat peminjaman barang inventaris',
                'url' => url('mobile-sarpras/peminjaman/tambah'),
                'icons' => [[ 'src' => url('pwa-sarpras-icon.png?size=192&v=' . $sarprasIconVer), 'sizes' => '192x192', 'type' => 'image/png' ]]
            ]
        ],
        'launch_handler' => [
            'client_mode' => 'auto'
        ]
    ];
    echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
};
$router->get('/mobile-sarpras/manifest.json', $sarprasManifestHandler);
$router->get('/manifest-sarpras.json', $sarprasManifestHandler);

// -- Dedicated Sarpras Mobile PWA Icon Route --
$router->get('/pwa-sarpras-icon.png', function() {
    $size = isset($_GET['size']) ? (int)$_GET['size'] : (isset($_GET['s']) ? (int)$_GET['s'] : 192);
    $isMaskable = !empty($_GET['maskable']);

    if ($size === 180) {
        $iconPath = PUBLIC_PATH . '/images/pwa/sarpras-apple-touch-icon.png';
    } elseif ($isMaskable) {
        $iconPath = ($size >= 512) 
            ? PUBLIC_PATH . '/images/pwa/sarpras-icon-maskable-512.png'
            : PUBLIC_PATH . '/images/pwa/sarpras-icon-maskable-192.png';
    } else {
        $iconPath = ($size >= 512) 
            ? PUBLIC_PATH . '/images/pwa/sarpras-icon-512.png'
            : PUBLIC_PATH . '/images/pwa/sarpras-icon-192.png';
    }

    if (!file_exists($iconPath)) {
        $iconPath = PUBLIC_PATH . '/images/pwa/sarpras-icon-192.png';
    }

    if (file_exists($iconPath)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($iconPath)) . ' GMT');
        readfile($iconPath);
        exit;
    }

    http_response_code(404);
    exit;
});

// -- PWA Dynamic Icon Route (Always Prioritize App Icon Uploaded from Pengaturan Sistem) --
$router->get('/pwa-icon.png', function() {
    $activeIcon = '';
    if (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
        $cand = BASE_PATH . '/' . ltrim(SYS_APP_LOGO, '/');
        if (file_exists($cand)) $activeIcon = $cand;
    }
    if (empty($activeIcon) && defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
        $cand = BASE_PATH . '/' . ltrim(SYS_APP_FAVICON, '/');
        if (file_exists($cand)) $activeIcon = $cand;
    }
    if (empty($activeIcon)) {
        // Fallback to uploaded settings folder
        $uploadDir = BASE_PATH . '/public/uploads/settings/';
        $favs = glob($uploadDir . 'favicon_*.*');
        if (!empty($favs)) $activeIcon = end($favs);
        if (empty($activeIcon)) {
            $logos = glob($uploadDir . 'logo_*.*');
            if (!empty($logos)) $activeIcon = end($logos);
        }
    }
    if (empty($activeIcon)) {
        $pwaIcon512 = PUBLIC_PATH . '/images/pwa/icon-512.png';
        if (file_exists($pwaIcon512)) $activeIcon = $pwaIcon512;
    }

    if (empty($activeIcon) || !file_exists($activeIcon)) {
        http_response_code(404);
        exit;
    }

    $reqSize = isset($_GET['size']) ? (int)$_GET['size'] : (isset($_GET['s']) ? (int)$_GET['s'] : 0);
    $isMaskable = !empty($_GET['maskable']);
    $ext = strtolower(pathinfo($activeIcon, PATHINFO_EXTENSION));

    // Direct output if no resize or maskable transformation needed
    if ($reqSize <= 0 && !$isMaskable && in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
        $mime = $ext === 'png' ? 'image/png' : ($ext === 'webp' ? 'image/webp' : 'image/jpeg');
        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=86400');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($activeIcon)) . ' GMT');
        readfile($activeIcon);
        exit;
    }

    // Dynamic resizing & maskable safe-padding using GD
    $targetSize = ($reqSize > 0 && $reqSize <= 1024) ? $reqSize : 512;
    $raw = file_get_contents($activeIcon);
    $srcImg = null;
    if ($ext === 'ico') {
        $pos = strpos($raw, "\x89PNG\r\n\x1a\n");
        if ($pos !== false) {
            $srcImg = @imagecreatefromstring(substr($raw, $pos));
        }
    }
    if (!$srcImg) {
        $srcImg = @imagecreatefromstring($raw);
    }

    if ($srcImg) {
        $dst = imagecreatetruecolor($targetSize, $targetSize);
        if ($isMaskable) {
            $bg = imagecolorallocate($dst, 255, 255, 255);
            imagefilledrectangle($dst, 0, 0, $targetSize, $targetSize, $bg);
            $inner = (int)($targetSize * 0.80);
            $offset = (int)(($targetSize - $inner) / 2);
            imagealphablending($dst, true);
            imagecopyresampled($dst, $srcImg, $offset, $offset, 0, 0, $inner, $inner, imagesx($srcImg), imagesy($srcImg));
        } else {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
            imagefilledrectangle($dst, 0, 0, $targetSize, $targetSize, $transparent);
            imagealphablending($dst, true);
            imagecopyresampled($dst, $srcImg, 0, 0, 0, 0, $targetSize, $targetSize, imagesx($srcImg), imagesy($srcImg));
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=86400');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($activeIcon)) . ' GMT');
        imagepng($dst, null, 8);
        imagedestroy($dst);
        imagedestroy($srcImg);
        exit;
    }

    // Fallback if GD is missing or fails: serve original with correct mime
    $mime = $ext === 'png' ? 'image/png' : ($ext === 'webp' ? 'image/webp' : ($ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/png'));
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=86400');
    readfile($activeIcon);
    exit;
});

// -- Dynamic Favicon Route (Always serve active favicon or fallback with proper headers) --
$router->get('/favicon.ico', function() {
    $favPath = '';
    if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
        $cand = BASE_PATH . '/' . ltrim(SYS_APP_FAVICON, '/');
        if (file_exists($cand)) $favPath = $cand;
    }
    if (empty($favPath)) {
        $uploadDir = BASE_PATH . '/public/uploads/settings/';
        $favs = glob($uploadDir . 'favicon_*.*');
        if (!empty($favs)) $favPath = end($favs);
    }
    if (empty($favPath) && file_exists(BASE_PATH . '/favicon.ico')) {
        $favPath = BASE_PATH . '/favicon.ico';
    }
    if (empty($favPath) && file_exists(PUBLIC_PATH . '/favicon.ico')) {
        $favPath = PUBLIC_PATH . '/favicon.ico';
    }
    if (empty($favPath) && defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
        $cand = BASE_PATH . '/' . ltrim(SYS_APP_LOGO, '/');
        if (file_exists($cand)) $favPath = $cand;
    }
    if (empty($favPath) || !file_exists($favPath)) {
        http_response_code(404);
        exit;
    }

    $ext = strtolower(pathinfo($favPath, PATHINFO_EXTENSION));
    $mime = ($ext === 'ico') ? 'image/x-icon' : ($ext === 'png' ? 'image/png' : ($ext === 'svg' ? 'image/svg+xml' : 'image/x-icon'));
    
    header('Content-Type: ' . $mime);
    header('Cache-Control: public, max-age=604800');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($favPath)) . ' GMT');
    readfile($favPath);
    exit;
});


// -- PWA Manifest & Service Worker Routes --
$portalGuruManifestHandler = function() {
    header('Content-Type: application/manifest+json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    $manifestPath = PUBLIC_PATH . '/manifest.json';
    if (file_exists($manifestPath)) {
        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);
        if (is_array($manifest)) {
            $manifest['id'] = 'portal-bip-guru-mobile';
            $manifest['name'] = 'Portal BIP Mobile';
            $manifest['short_name'] = 'Portal BIP';
            $manifest['description'] = 'Aplikasi Resmi Portal BIP untuk Presensi GPS, Jurnal Mengajar, Izin, Cuti, dan Layanan Akademik.';
            $manifest['start_url'] = url('mobile/') . '?utm_source=pwa_portal';
            $manifest['scope'] = url('mobile/');
            $manifest['launch_handler'] = ['client_mode' => 'auto'];

            // Check icon timestamp from settings for instant cache-busting
            $customIconPath = '';
            if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON) && file_exists(BASE_PATH . '/' . ltrim(SYS_APP_FAVICON, '/'))) {
                $customIconPath = BASE_PATH . '/' . ltrim(SYS_APP_FAVICON, '/');
            } elseif (defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO) && file_exists(BASE_PATH . '/' . ltrim(SYS_APP_LOGO, '/'))) {
                $customIconPath = BASE_PATH . '/' . ltrim(SYS_APP_LOGO, '/');
            }
            $iconVer = !empty($customIconPath) ? filemtime($customIconPath) : '1';

            // High-resolution exact icons from dynamic endpoint synced with Pengaturan Sistem
            $manifest['icons'] = [
                [
                    'src' => url('pwa-icon.png?size=192&v=' . $iconVer),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => url('pwa-icon.png?size=192&maskable=1&v=' . $iconVer),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => url('pwa-icon.png?size=512&v=' . $iconVer),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => url('pwa-icon.png?size=512&maskable=1&v=' . $iconVer),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => url('pwa-icon.png?size=180&v=' . $iconVer),
                    'sizes' => '180x180',
                    'type' => 'image/png'
                ]
            ];

            if (!empty($manifest['shortcuts'])) {
                foreach ($manifest['shortcuts'] as &$sc) {
                    $sc['icons'] = [[ 'src' => url('pwa-icon.png?size=192&v=' . $iconVer), 'sizes' => '192x192', 'type' => 'image/png' ]];
                }
            }

            if (!empty($manifest['screenshots'])) {
                foreach ($manifest['screenshots'] as &$sc) {
                    if (!str_starts_with($sc['src'], 'http')) {
                        $sc['src'] = url('public/' . ltrim($sc['src'], '/'));
                    }
                }
            }
            echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    readfile($manifestPath);
    exit;
};

$router->get('/manifest.json', $portalGuruManifestHandler);
$router->get('/mobile/manifest.json', $portalGuruManifestHandler);

$router->get('/sw.js', function() {
    header('Content-Type: application/javascript; charset=utf-8');
    header('Service-Worker-Allowed: /');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    readfile(PUBLIC_PATH . '/sw.js');
    exit;
});
$router->get('/sw-sarpras.js', function() {
    header('Content-Type: application/javascript; charset=utf-8');
    header('Service-Worker-Allowed: /');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    if (file_exists(PUBLIC_PATH . '/sw-sarpras.js')) {
        readfile(PUBLIC_PATH . '/sw-sarpras.js');
        exit;
    }
    echo <<<'JS'
const CACHE_NAME='sarpras-pwa-v8';
self.addEventListener('install',e=>{self.skipWaiting();});
self.addEventListener('activate',e=>{
    e.waitUntil(caches.keys().then(n=>Promise.all(n.filter(name=>name.startsWith('sarpras-') && name!==CACHE_NAME).map(name=>caches.delete(name)))).then(()=>self.clients.claim()));
});
self.addEventListener('fetch',e=>{
    if(e.request.method!=='GET'||!e.request.url.startsWith('http'))return;
    if(!e.request.url.includes('/mobile-sarpras') && !e.request.url.includes('sarpras-icon'))return;
    e.respondWith(fetch(e.request).then(r=>{if(r&&r.status===200){const c=r.clone();caches.open(CACHE_NAME).then(cache=>cache.put(e.request,c));}return r;}).catch(()=>caches.match(e.request)));
});
JS;
    exit;
});

// -- Database Migration Routes (Restricted to Super Admin) --
$router->get('/desktop-migrate', function() {
    require_once BASE_PATH . '/desktop-migrate/migrate.php';
    exit;
}, [Middleware::roleRequired('super_admin')]);

$router->get('/mobile-migrate', function() {
    require_once BASE_PATH . '/mobile-migrate/migrate.php';
    exit;
}, [Middleware::roleRequired('super_admin')]);

$router->any('/auto-migrate', function() {
    $res = AutoMigrate::run(true);
    
    if (isset($_GET['format']) && $_GET['format'] === 'json') {
        Response::json([
            'success' => empty($res['errors']),
            'results' => $res
        ]);
        exit;
    }
    
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Portal BIP — Sinkronisasi Database Otomatis</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style>body { font-family: 'Inter', sans-serif; }</style>
    </head>
    <body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-xl w-full bg-slate-800/90 backdrop-blur border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-2xl font-bold">
                    ⚡
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Sinkronisasi Database Portal BIP</h1>
                    <p class="text-xs text-slate-400">AutoMigrate Engine (Zero Downtime Synchronizer)</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-700/60 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Migrasi Baru Diterapkan:</span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold <?= !empty($res['applied']) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 text-slate-400' ?>">
                        <?= count($res['applied'] ?? []) ?> Task
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900/60 border border-slate-700/60 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sudah Up-to-Date (Skipped):</span>
                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/40">
                        <?= count($res['skipped'] ?? []) ?> Task
                    </span>
                </div>

                <?php if (!empty($res['errors'])): ?>
                <div class="p-4 rounded-2xl bg-rose-950/40 border border-rose-500/40 text-rose-300 text-xs space-y-1">
                    <div class="font-bold">⚠️ Terdapat <?= count($res['errors']) ?> kendala:</div>
                    <pre class="overflow-x-auto text-[11px] p-2 bg-slate-950/80 rounded-xl"><?= htmlspecialchars(print_r($res['errors'], true)) ?></pre>
                </div>
                <?php else: ?>
                <div class="p-4 rounded-2xl bg-emerald-950/30 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2">
                    <span>✓</span>
                    <span>Seluruh tabel dan struktur database 100% mutakhir dan sinkron!</span>
                </div>
                <?php endif; ?>

                <?php if (!empty($res['applied'])): ?>
                <div class="space-y-1.5 mt-2">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Detail Migrasi Baru:</div>
                    <div class="max-h-48 overflow-y-auto space-y-1 pr-1">
                        <?php foreach ($res['applied'] as $appItem): ?>
                            <div class="px-3 py-2 rounded-xl bg-slate-900/40 border border-slate-800 text-xs text-slate-300 font-mono flex items-center gap-2">
                                <span class="text-emerald-400">✓</span> <?= htmlspecialchars($appItem) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="mt-8 flex gap-3">
                <a href="<?= url('auto-migrate') ?>" class="flex-1 text-center px-4 py-2.5 rounded-2xl bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold transition-all">
                    Jalankan Ulang
                </a>
                <a href="<?= url('dashboard') ?>" class="flex-1 text-center px-4 py-2.5 rounded-2xl bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold shadow-lg shadow-primary-600/30 transition-all">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
});

// -- API Routes (JSON) --
$router->get('/api/quran/surat', [PortalGuruController::class, 'apiSuratList']);
$router->get('/api/quran/surat/{nomor}', [PortalGuruController::class, 'apiSuratDetail']);
$router->get('/api/quran/page/{page}', [PortalGuruController::class, 'apiPageDetail']);
$router->get('/api/quran/bookmark', [PortalGuruController::class, 'apiGetQuranBookmark']);
$router->post('/api/quran/bookmark', [PortalGuruController::class, 'apiSaveQuranBookmark']);
$router->get('/mobile/api/quran/bookmark', [PortalGuruController::class, 'apiGetQuranBookmark']);
$router->post('/mobile/api/quran/bookmark', [PortalGuruController::class, 'apiSaveQuranBookmark']);
$router->get('/api/modules', function() {
    Middleware::authRequired();
    $modules = RBAC::getAccessibleModules();
    Response::json(['success' => true, 'data' => $modules]);
});

// ── Dispatch ────────────────────────────────────────
$router->dispatch();

