<?php ini_set('display_errors', '1');
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

// ── Global System Settings ──────────────────────────
try {
    $dbSettings = Database::getInstance();
    $sysSettings = $dbSettings->findAll("SELECT setting_key, setting_value FROM settings");
    $globalSettings = [];
    foreach ($sysSettings as $row) {
        $globalSettings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Fetch active academic year
    $activeAcademicYear = $dbSettings->find("SELECT id, nama_tahun FROM tahun_akademik WHERE is_active = 1 LIMIT 1");
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
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate full URL
 */
function url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL
 */
function asset(string $path): string {
    return BASE_URL . '/public/' . ltrim($path, '/');
}

/**
 * Get old input value (for form repopulation)
 */
function old(string $field, string $default = ''): string {
    return Response::oldInput($field, $default);
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
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);
if ($basePath === '\\' || $basePath === '/') {
    $basePath = '';
}

$router = new Router($basePath);

// ── Load Controllers ────────────────────────────────
require_once BASE_PATH . '/modules/auth/controllers/AuthController.php';
require_once BASE_PATH . '/modules/dashboard/controllers/DashboardController.php';
require_once BASE_PATH . '/modules/users/controllers/UserController.php';
require_once BASE_PATH . '/modules/roles/controllers/RoleController.php';
require_once BASE_PATH . '/modules/modules-manager/controllers/ModuleController.php';
require_once BASE_PATH . '/modules/kelola-siswa/controllers/SiswaController.php';
require_once BASE_PATH . '/modules/kelola-kelas/controllers/KelasController.php';
require_once BASE_PATH . '/modules/kelola-pegawai/controllers/PegawaiController.php';
require_once BASE_PATH . '/modules/pengaturan-sistem/controllers/SettingsController.php';

// ── Define Routes ───────────────────────────────────

// -- Auth Routes (Guest only) --
$router->get('/login', [AuthController::class, 'showLogin'], [[Middleware::class, 'guestOnly']]);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

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
$router->get('/roles/edit/{id}', [RoleController::class, 'edit'], [Middleware::permissionRequired('roles.update')]);
$router->post('/roles/update/{id}', [RoleController::class, 'update'], [Middleware::permissionRequired('roles.update')]);
$router->post('/roles/delete/{id}', [RoleController::class, 'delete'], [Middleware::permissionRequired('roles.delete')]);

// -- Module Manager --
$router->get('/modules-manager', [ModuleController::class, 'index'], [Middleware::permissionRequired('modules.view')]);
$router->get('/modules-manager/create', [ModuleController::class, 'create'], [Middleware::permissionRequired('modules.create')]);
$router->post('/modules-manager/store', [ModuleController::class, 'store'], [Middleware::permissionRequired('modules.create')]);
$router->get('/modules-manager/edit/{id}', [ModuleController::class, 'edit'], [Middleware::permissionRequired('modules.update')]);
$router->post('/modules-manager/update/{id}', [ModuleController::class, 'update'], [Middleware::permissionRequired('modules.update')]);
$router->post('/modules-manager/delete/{id}', [ModuleController::class, 'delete'], [Middleware::permissionRequired('modules.delete')]);

// -- Kelola Siswa --
$router->get('/kelola-siswa', [SiswaController::class, 'index'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/create', [SiswaController::class, 'create'], [Middleware::permissionRequired('siswa.create')]);
$router->post('/kelola-siswa/store', [SiswaController::class, 'store'], [Middleware::permissionRequired('siswa.create')]);
$router->get('/kelola-siswa/edit/{id}', [SiswaController::class, 'edit'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/update/{id}', [SiswaController::class, 'update'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/delete/{id}', [SiswaController::class, 'delete'], [Middleware::permissionRequired('siswa.delete')]);

// -- Kelola Kelas --
$router->get('/kelola-kelas', [KelasController::class, 'index'], [[]]);
$router->get('/kelola-kelas/create', [KelasController::class, 'create'], [[]]);
$router->post('/kelola-kelas/store', [KelasController::class, 'store'], [[]]);
$router->get('/kelola-kelas/edit/{id}', [KelasController::class, 'edit'], [[]]);
$router->post('/kelola-kelas/update/{id}', [KelasController::class, 'update'], [[]]);
$router->post('/kelola-kelas/delete/{id}', [KelasController::class, 'delete'], [[]]);
$router->post('/kelola-kelas/copy', [KelasController::class, 'copyClasses'], [[]]);

// -- Kelola Pegawai --
$router->get('/kelola-pegawai', [PegawaiController::class, 'index'], [[]]);
$router->get('/kelola-pegawai/create', [PegawaiController::class, 'create'], [[]]);
$router->post('/kelola-pegawai/store', [PegawaiController::class, 'store'], [[]]);
$router->get('/kelola-pegawai/edit/{id}', [PegawaiController::class, 'edit'], [[]]);
$router->post('/kelola-pegawai/update/{id}', [PegawaiController::class, 'update'], [[]]);
$router->post('/kelola-pegawai/delete/{id}', [PegawaiController::class, 'delete'], [[]]);

$router->get('/kelola-pegawai/penugasan', [PegawaiController::class, 'penugasan'], [[]]);
$router->get('/kelola-pegawai/penugasan/create', [PegawaiController::class, 'createPenugasan'], [[]]);
$router->post('/kelola-pegawai/penugasan/store', [PegawaiController::class, 'storePenugasan'], [[]]);
$router->get('/kelola-pegawai/penugasan/edit/{id}', [PegawaiController::class, 'editPenugasan'], [[]]);
$router->post('/kelola-pegawai/penugasan/update/{id}', [PegawaiController::class, 'updatePenugasan'], [[]]);
$router->post('/kelola-pegawai/penugasan/delete/{id}', [PegawaiController::class, 'deletePenugasan'], [[]]);

// Pengaturan Sistem
$router->get('/pengaturan-sistem', [SettingsController::class, 'index'], [Middleware::permissionRequired('settings.view')]);
$router->get('/pengaturan-sistem/identitas', [SettingsController::class, 'identitas'], [Middleware::permissionRequired('settings.view')]);
$router->get('/pengaturan-sistem/akademik', [SettingsController::class, 'akademik'], [Middleware::permissionRequired('settings.view')]);
$router->post('/pengaturan-sistem/akademik/store', [SettingsController::class, 'storeAkademik'], [Middleware::permissionRequired('settings.update')]);
$router->get('/pengaturan-sistem/akademik/edit/{id}', [SettingsController::class, 'editAkademik'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/akademik/update/{id}', [SettingsController::class, 'updateAkademik'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/akademik/set-aktif/{id}', [SettingsController::class, 'setAktifAkademik'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/akademik/delete/{id}', [SettingsController::class, 'deleteAkademik'], [Middleware::permissionRequired('settings.delete')]);

$router->get('/pengaturan-sistem/master-pegawai', [SettingsController::class, 'masterPegawai'], [Middleware::permissionRequired('settings.view')]);
$router->post('/pengaturan-sistem/master-pegawai/unit-tugas/store', [SettingsController::class, 'storeUnitTugas'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/master-pegawai/unit-tugas/delete/{id}', [SettingsController::class, 'deleteUnitTugas'], [Middleware::permissionRequired('settings.delete')]);
$router->post('/pengaturan-sistem/master-pegawai/jabatan/store', [SettingsController::class, 'storeJabatan'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/master-pegawai/jabatan/delete/{id}', [SettingsController::class, 'deleteJabatan'], [Middleware::permissionRequired('settings.delete')]);
$router->post('/pengaturan-sistem/master-pegawai/status-kerja/store', [SettingsController::class, 'storeStatusKerja'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/master-pegawai/status-kerja/delete/{id}', [SettingsController::class, 'deleteStatusKerja'], [Middleware::permissionRequired('settings.delete')]);
$router->post('/pengaturan-sistem/master-pegawai/jenis-pegawai/store', [SettingsController::class, 'storeJenisPegawai'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/master-pegawai/jenis-pegawai/delete/{id}', [SettingsController::class, 'deleteJenisPegawai'], [Middleware::permissionRequired('settings.delete')]);

$router->get('/pengaturan-sistem/reset-data', [SettingsController::class, 'resetDataView'], [Middleware::permissionRequired('settings.view')]);
$router->post('/pengaturan-sistem/update', [SettingsController::class, 'update'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/reset-data/process', [SettingsController::class, 'resetData'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/reset', [SettingsController::class, 'resetData'], [Middleware::permissionRequired('settings.reset')]);

// -- API Routes (JSON) --
$router->get('/api/modules', function() {
    Middleware::authRequired();
    $modules = RBAC::getAccessibleModules();
    Response::json(['success' => true, 'data' => $modules]);
});

// ── Dispatch ────────────────────────────────────────
$router->dispatch();
