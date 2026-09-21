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
function e(?string $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
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
require_once BASE_PATH . '/modules/portal-guru/controllers/PortalGuruController.php';
require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/controllers/PerangkatController.php';
require_once BASE_PATH . '/modules/kelola-perangkat-pembelajaran/controllers/JadwalController.php';
require_once BASE_PATH . '/modules/kelola-nilai/controllers/NilaiController.php';
require_once BASE_PATH . '/modules/kelola-raport/controllers/RaportController.php';
require_once BASE_PATH . '/modules/kelola-absen-siswa/controllers/AbsenSiswaController.php';
require_once BASE_PATH . '/modules/kelola-absen-pegawai/controllers/AbsenPegawaiController.php';
require_once BASE_PATH . '/modules/kelola-quran-siswa/controllers/QuranSiswaController.php';
require_once BASE_PATH . '/modules/kelola-ujian/controllers/UjianController.php';
require_once BASE_PATH . '/modules/kelola-quran-pegawai/controllers/QuranPegawaiController.php';
require_once BASE_PATH . '/modules/kelola-cuti/controllers/CutiController.php';
require_once BASE_PATH . '/modules/kelola-kpi/controllers/KpiController.php';
require_once BASE_PATH . '/modules/kelola-ibadah-guru/controllers/IbadahGuruController.php';
require_once BASE_PATH . '/modules/kelola-spmb/controllers/SpmbController.php';
require_once BASE_PATH . '/modules/kelola-pelanggaran-siswa/controllers/PelanggaranSiswaController.php';
require_once BASE_PATH . '/modules/kelola-izin-guru/controllers/IzinGuruController.php';
require_once BASE_PATH . '/modules/kelola-buku-angkatan-siswa/controllers/BukuAngkatanSiswaController.php';

// ── Define Routes ───────────────────────────────────

// -- Auth Routes (Guest only) --
$router->get('/login', [AuthController::class, 'showLogin'], [[Middleware::class, 'guestOnly']]);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// -- Public Routes --
$router->get('/validasi-kartu/{id}', [SiswaController::class, 'validasiKartu']);

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
$router->get('/kelola-siswa/statistik', [SiswaController::class, 'statistik'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa', [SiswaController::class, 'index'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/create', [SiswaController::class, 'create'], [Middleware::permissionRequired('siswa.create')]);
$router->post('/kelola-siswa/store', [SiswaController::class, 'store'], [Middleware::permissionRequired('siswa.create')]);
$router->get('/kelola-siswa/detail/{id}', [SiswaController::class, 'detail'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/edit/{id}', [SiswaController::class, 'edit'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/update/{id}', [SiswaController::class, 'update'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/delete/{id}', [SiswaController::class, 'delete'], [Middleware::permissionRequired('siswa.delete')]);
$router->get('/kelola-siswa/cetak/{id}', [SiswaController::class, 'cetak'], [Middleware::permissionRequired('siswa.view')]);
$router->post('/kelola-siswa/sync', [SiswaController::class, 'syncJurnal'], [Middleware::permissionRequired('siswa.create')]);
$router->post('/kelola-siswa/sync-dapodik', [SiswaController::class, 'syncDapodikOnline'], [Middleware::permissionRequired('siswa.create')]);
$router->get('/kelola-siswa/export', [SiswaController::class, 'export'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/foto', [SiswaController::class, 'foto'], [Middleware::permissionRequired('siswa.view')]);
$router->post('/kelola-siswa/upload-foto', [SiswaController::class, 'uploadFoto'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/upload-foto-zip', [SiswaController::class, 'uploadFotoZip'], [Middleware::permissionRequired('siswa.update')]);
$router->get('/kelola-siswa/cetak-kartu-massal', [SiswaController::class, 'cetakKartuMassal'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/cetak-kartu/{id}', [SiswaController::class, 'cetakKartu'], [Middleware::permissionRequired('siswa.view')]);
$router->post('/kelola-siswa/upload-template-kartu', [SiswaController::class, 'uploadTemplateKartu'], [Middleware::permissionRequired('siswa.update')]);

// Buku Induk Siswa
$router->get('/kelola-siswa/buku-induk', [SiswaController::class, 'bukuInduk'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/buku-induk/export', [SiswaController::class, 'exportBukuInduk'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/buku-induk/{id}', [SiswaController::class, 'detailBukuInduk'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/buku-induk/{id}/cetak', [SiswaController::class, 'cetakBukuInduk'], [Middleware::permissionRequired('siswa.view')]);

// Prestasi Siswa
$router->get('/kelola-siswa/prestasi', [SiswaController::class, 'prestasi'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/prestasi/create', [SiswaController::class, 'createPrestasi'], [Middleware::permissionRequired('siswa.create')]);
$router->post('/kelola-siswa/prestasi/store', [SiswaController::class, 'storePrestasi'], [Middleware::permissionRequired('siswa.create')]);
$router->get('/kelola-siswa/prestasi/edit/{id}', [SiswaController::class, 'editPrestasi'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/prestasi/update/{id}', [SiswaController::class, 'updatePrestasi'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/prestasi/delete/{id}', [SiswaController::class, 'deletePrestasi'], [Middleware::permissionRequired('siswa.delete')]);
$router->get('/kelola-siswa/prestasi/siswa/{id}', [SiswaController::class, 'timelinePrestasiSiswa'], [Middleware::permissionRequired('siswa.view')]);

// Siswa Keluar & Mutasi
$router->get('/kelola-siswa/keluar', [SiswaController::class, 'siswaKeluar'], [Middleware::permissionRequired('siswa.view')]);
$router->get('/kelola-siswa/keluar/create', [SiswaController::class, 'createSiswaKeluar'], [Middleware::permissionRequired('siswa.create')]);
$router->post('/kelola-siswa/keluar/store', [SiswaController::class, 'storeSiswaKeluar'], [Middleware::permissionRequired('siswa.create')]);
$router->get('/kelola-siswa/keluar/edit/{id}', [SiswaController::class, 'editSiswaKeluar'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/keluar/update/{id}', [SiswaController::class, 'updateSiswaKeluar'], [Middleware::permissionRequired('siswa.update')]);
$router->post('/kelola-siswa/keluar/delete/{id}', [SiswaController::class, 'deleteSiswaKeluar'], [Middleware::permissionRequired('siswa.delete')]);
$router->post('/kelola-siswa/keluar/reaktivasi/{id}', [SiswaController::class, 'reaktivasiSiswa'], [Middleware::permissionRequired('siswa.update')]);
$router->get('/kelola-siswa/keluar/cetak/{id}', [SiswaController::class, 'cetakSuratPindah'], [Middleware::permissionRequired('siswa.view')]);

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
$router->get('/kelola-pegawai/statistik', [PegawaiController::class, 'statistik'], [Middleware::permissionRequired('pegawai.view')]);
$router->get('/kelola-pegawai', [PegawaiController::class, 'index'], [Middleware::permissionRequired('pegawai.view')]);
$router->get('/kelola-pegawai/create', [PegawaiController::class, 'create'], [Middleware::permissionRequired('pegawai.create')]);
$router->post('/kelola-pegawai/store', [PegawaiController::class, 'store'], [Middleware::permissionRequired('pegawai.create')]);
$router->get('/kelola-pegawai/edit/{id}', [PegawaiController::class, 'edit'], [Middleware::permissionRequired('pegawai.update')]);
$router->post('/kelola-pegawai/update/{id}', [PegawaiController::class, 'update'], [Middleware::permissionRequired('pegawai.update')]);
$router->post('/kelola-pegawai/delete/{id}', [PegawaiController::class, 'delete'], [Middleware::permissionRequired('pegawai.delete')]);
$router->get('/kelola-pegawai/export', [PegawaiController::class, 'export'], [Middleware::permissionRequired('pegawai.export')]);
$router->get('/kelola-pegawai/template', [PegawaiController::class, 'downloadTemplate'], [Middleware::permissionRequired('pegawai.view')]);
$router->post('/kelola-pegawai/import', [PegawaiController::class, 'import'], [Middleware::permissionRequired('pegawai.create')]);
$router->get('/kelola-pegawai/keluar', [PegawaiController::class, 'keluar'], [Middleware::permissionRequired('pegawai.view')]);
$router->get('/kelola-pegawai/cetak-cv/{id}', [PegawaiController::class, 'cetakCv'], [Middleware::permissionRequired('pegawai.export')]);

// -- Kelola Penugasan & Grup SK --
$router->get('/kelola-pegawai/penugasan', [PegawaiController::class, 'penugasan'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->get('/kelola-pegawai/penugasan/grup/create', [PegawaiController::class, 'createGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/grup/store', [PegawaiController::class, 'storeGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->get('/kelola-pegawai/penugasan/grup/edit/{id}', [PegawaiController::class, 'editGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/grup/update/{id}', [PegawaiController::class, 'updateGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/grup/delete/{id}', [PegawaiController::class, 'deleteGrup'], [Middleware::permissionRequired('pegawai.delete')]);
$router->post('/kelola-pegawai/penugasan/grup/set-aktif/{id}', [PegawaiController::class, 'setAktifGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/grup/toggle-aktif/{id}', [PegawaiController::class, 'toggleAktifGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/grup/salin/{id}', [PegawaiController::class, 'salinGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);

// Detail Anggota Penugasan dalam Grup
$router->get('/kelola-pegawai/penugasan/grup/{id}', [PegawaiController::class, 'detailGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->get('/kelola-pegawai/penugasan/grup/{id}/cetak', [PegawaiController::class, 'cetakSkGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/grup/{id}/update-sk-meta', [PegawaiController::class, 'updateSkMeta'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->get('/kelola-pegawai/penugasan/grup/{id}/create', [PegawaiController::class, 'createPenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/grup/{id}/store', [PegawaiController::class, 'storePenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->get('/kelola-pegawai/penugasan/detail/edit/{id}', [PegawaiController::class, 'editPenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/detail/update/{id}', [PegawaiController::class, 'updatePenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/detail/delete/{id}', [PegawaiController::class, 'deletePenugasanGrup'], [Middleware::permissionRequired('pegawai.delete')]);

// Aliases for Penugasan member actions
$router->get('/kelola-pegawai/penugasan/{id}/edit', [PegawaiController::class, 'editPenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/{id}/update', [PegawaiController::class, 'updatePenugasanGrup'], [Middleware::permissionRequired('pegawai.penugasan')]);
$router->post('/kelola-pegawai/penugasan/{id}/delete', [PegawaiController::class, 'deletePenugasanGrup'], [Middleware::permissionRequired('pegawai.delete')]);

// -- Riwayat Karir Pegawai & Guru (Otomatis dari SK & Manual) --
$router->get('/kelola-pegawai/karir', [PegawaiController::class, 'karir'], [Middleware::permissionRequired('pegawai.view')]);
$router->get('/kelola-pegawai/karir/create', [PegawaiController::class, 'createKarir'], [Middleware::permissionRequired('pegawai.create')]);
$router->post('/kelola-pegawai/karir/store', [PegawaiController::class, 'storeKarir'], [Middleware::permissionRequired('pegawai.create')]);
$router->get('/kelola-pegawai/karir/edit/{id}', [PegawaiController::class, 'editKarir'], [Middleware::permissionRequired('pegawai.update')]);
$router->post('/kelola-pegawai/karir/update/{id}', [PegawaiController::class, 'updateKarir'], [Middleware::permissionRequired('pegawai.update')]);
$router->post('/kelola-pegawai/karir/delete/{id}', [PegawaiController::class, 'deleteKarir'], [Middleware::permissionRequired('pegawai.delete')]);
$router->get('/kelola-pegawai/karir/pegawai/{id}', [PegawaiController::class, 'timelinePegawai'], [Middleware::permissionRequired('pegawai.view')]);

// -- Prestasi & Penghargaan Pegawai / Guru --
$router->get('/kelola-pegawai/prestasi', [PegawaiController::class, 'prestasi'], [Middleware::permissionRequired('pegawai.view')]);
$router->get('/kelola-pegawai/prestasi/create', [PegawaiController::class, 'createPrestasi'], [Middleware::permissionRequired('pegawai.create')]);
$router->post('/kelola-pegawai/prestasi/store', [PegawaiController::class, 'storePrestasi'], [Middleware::permissionRequired('pegawai.create')]);
$router->get('/kelola-pegawai/prestasi/edit/{id}', [PegawaiController::class, 'editPrestasi'], [Middleware::permissionRequired('pegawai.update')]);
$router->post('/kelola-pegawai/prestasi/update/{id}', [PegawaiController::class, 'updatePrestasi'], [Middleware::permissionRequired('pegawai.update')]);
$router->post('/kelola-pegawai/prestasi/delete/{id}', [PegawaiController::class, 'deletePrestasi'], [Middleware::permissionRequired('pegawai.delete')]);
$router->get('/kelola-pegawai/prestasi/pegawai/{id}', [PegawaiController::class, 'prestasiPegawai'], [Middleware::permissionRequired('pegawai.view')]);

// -- Riwayat Pelatihan, Diklat & Workshop Pegawai / Guru --
$router->get('/kelola-pegawai/pelatihan', [PegawaiController::class, 'pelatihan'], [Middleware::permissionRequired('pegawai.view')]);
$router->get('/kelola-pegawai/pelatihan/create', [PegawaiController::class, 'createPelatihan'], [Middleware::permissionRequired('pegawai.create')]);
$router->post('/kelola-pegawai/pelatihan/store', [PegawaiController::class, 'storePelatihan'], [Middleware::permissionRequired('pegawai.create')]);
$router->get('/kelola-pegawai/pelatihan/edit/{id}', [PegawaiController::class, 'editPelatihan'], [Middleware::permissionRequired('pegawai.update')]);
$router->post('/kelola-pegawai/pelatihan/update/{id}', [PegawaiController::class, 'updatePelatihan'], [Middleware::permissionRequired('pegawai.update')]);
$router->post('/kelola-pegawai/pelatihan/delete/{id}', [PegawaiController::class, 'deletePelatihan'], [Middleware::permissionRequired('pegawai.delete')]);
$router->get('/kelola-pegawai/pelatihan/pegawai/{id}', [PegawaiController::class, 'pelatihanPegawai'], [Middleware::permissionRequired('pegawai.view')]);

// -- Kelola Perangkat Pembelajaran --
$router->get('/kelola-perangkat-pembelajaran', [PerangkatController::class, 'dashboard'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/dashboard', [PerangkatController::class, 'dashboard'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-rpp', function() {
    Response::redirect(url('kelola-perangkat-pembelajaran'));
});

// Jadwal Pelajaran & Auto-Generator
$router->get('/kelola-perangkat-pembelajaran/jadwal', [JadwalController::class, 'index'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/create', [JadwalController::class, 'create'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/store', [JadwalController::class, 'store'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/edit/{id}', [JadwalController::class, 'edit'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/update/{id}', [JadwalController::class, 'update'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/delete/{id}', [JadwalController::class, 'delete'], [Middleware::permissionRequired('perangkat.delete')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/set-active/{id}', [JadwalController::class, 'setActive'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{id}', [JadwalController::class, 'pengaturanJp'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{id}', [JadwalController::class, 'simpanPengaturanJp'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-add/{id}', [JadwalController::class, 'tambahSlotKhusus'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-delete/{id}/{slotId}', [JadwalController::class, 'hapusSlotWaktu'], [Middleware::permissionRequired('perangkat.delete')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-edit/{id}/{slotId}', [JadwalController::class, 'editSlotWaktu'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-sync-day/{id}/{hari}', [JadwalController::class, 'syncHari'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/generate/{id}', [JadwalController::class, 'generate'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/run-generate/{id}', [JadwalController::class, 'runGenerate'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/matriks/{id}', [JadwalController::class, 'matriks'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/cetak-kelas/{id}', [JadwalController::class, 'cetakKelas'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/cetak-guru/{id}', [JadwalController::class, 'cetakGuru'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/export/{id}', [JadwalController::class, 'export'], [Middleware::permissionRequired('perangkat.view')]);

// Kalender Pendidikan (Kaldik)
$router->get('/kelola-perangkat-pembelajaran/kaldik', [PerangkatController::class, 'kaldik'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/create', [PerangkatController::class, 'createKaldik'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/store', [PerangkatController::class, 'storeKaldik'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/edit/{id}', [PerangkatController::class, 'editKaldik'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/update/{id}', [PerangkatController::class, 'updateKaldik'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/detail/{id}', [PerangkatController::class, 'detailKaldik'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/cetak/{id}', [PerangkatController::class, 'cetakKaldik'], [Middleware::permissionRequired('perangkat.view')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/toggle-active/{id}', [PerangkatController::class, 'toggleActiveKaldik'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/agenda/add/{id}', [PerangkatController::class, 'addKaldikAgenda'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/agenda/delete/{id}', [PerangkatController::class, 'deleteKaldikAgenda'], [Middleware::permissionRequired('perangkat.delete')]);

// Rincian Hari Efektif (HEB & HES Auto-Generated)
$router->get('/kelola-perangkat-pembelajaran/rincian-hari-efektif', [PerangkatController::class, 'rincianHariEfektif'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/rincian-hari-efektif/cetak', [PerangkatController::class, 'cetakRincianHariEfektif'], [Middleware::permissionRequired('perangkat.view')]);

// Hari Efektif Sekolah (HES)
$router->get('/kelola-perangkat-pembelajaran/hes', [PerangkatController::class, 'hes'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/hes/create', [PerangkatController::class, 'createHes'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/hes/store', [PerangkatController::class, 'storeHes'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/hes/edit/{id}', [PerangkatController::class, 'editHes'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/hes/update/{id}', [PerangkatController::class, 'updateHes'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/hes/detail/{id}', [PerangkatController::class, 'detailHes'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/hes/cetak/{id}', [PerangkatController::class, 'cetakHes'], [Middleware::permissionRequired('perangkat.view')]);

// Hari Efektif Belajar (HEB)
$router->get('/kelola-perangkat-pembelajaran/heb', [PerangkatController::class, 'heb'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/heb/create', [PerangkatController::class, 'createHeb'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/heb/store', [PerangkatController::class, 'storeHeb'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/heb/edit/{id}', [PerangkatController::class, 'editHeb'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/heb/update/{id}', [PerangkatController::class, 'updateHeb'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/heb/detail/{id}', [PerangkatController::class, 'detailHeb'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/heb/cetak/{id}', [PerangkatController::class, 'cetakHeb'], [Middleware::permissionRequired('perangkat.view')]);

// Capaian Pembelajaran & Alur Tujuan Pembelajaran (CP & ATP)
$router->get('/kelola-perangkat-pembelajaran/cpatp', [PerangkatController::class, 'cpatp'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/ajax-penugasan/{guruId}', [PerangkatController::class, 'getPenugasanAjax'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/ajax-jadwal/{guruId}', [PerangkatController::class, 'getJadwalHariAjax'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/group/create', [PerangkatController::class, 'createCpatpGroup'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/group/store', [PerangkatController::class, 'storeCpatpGroup'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/group/delete/{id}', [PerangkatController::class, 'deleteCpatpGroup'], [Middleware::permissionRequired('perangkat.delete')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/group/{id}', [PerangkatController::class, 'cpatpDetailGroup'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/create/{groupId}', [PerangkatController::class, 'createCpatp'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/store/{groupId}', [PerangkatController::class, 'storeCpatp'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/edit/{id}', [PerangkatController::class, 'editCpatp'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/update/{id}', [PerangkatController::class, 'updateCpatp'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/detail/{id}', [PerangkatController::class, 'detailCpatp'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/cetak/{id}', [PerangkatController::class, 'cetakCpatp'], [Middleware::permissionRequired('perangkat.view')]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/delete/{id}', [PerangkatController::class, 'deleteCpatp'], [Middleware::permissionRequired('perangkat.delete')]);

// Program Tahunan (Prota)
$router->get('/kelola-perangkat-pembelajaran/prota', [PerangkatController::class, 'prota'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/prota/group/create', [PerangkatController::class, 'createProtaGroup'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/prota/group/store', [PerangkatController::class, 'storeProtaGroup'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/prota/group/delete/{id}', [PerangkatController::class, 'deleteProtaGroup'], [Middleware::permissionRequired('perangkat.delete')]);
$router->get('/kelola-perangkat-pembelajaran/prota/group/{id}', [PerangkatController::class, 'protaDetailGroup'], [Middleware::permissionRequired('perangkat.view')]);
$router->post('/kelola-perangkat-pembelajaran/prota/group/{id}/sync', [PerangkatController::class, 'syncProtaFromProsem'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/prota/group/{id}/cetak-semua', [PerangkatController::class, 'cetakSemuaProtaGroup'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/prota/create', [PerangkatController::class, 'createProta'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/prota/create/{groupId}', [PerangkatController::class, 'createProta'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/prota/store', [PerangkatController::class, 'storeProta'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/prota/store/{groupId}', [PerangkatController::class, 'storeProta'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/prota/edit/{id}', [PerangkatController::class, 'editProta'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/prota/update/{id}', [PerangkatController::class, 'updateProta'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/prota/detail/{id}', [PerangkatController::class, 'detailProta'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/prota/cetak/{id}', [PerangkatController::class, 'cetakProta'], [Middleware::permissionRequired('perangkat.view')]);
$router->post('/kelola-perangkat-pembelajaran/prota/delete/{id}', [PerangkatController::class, 'deleteProta'], [Middleware::permissionRequired('perangkat.delete')]);

// Program Semester (Prosem)
$router->get('/kelola-perangkat-pembelajaran/prosem', [PerangkatController::class, 'prosem'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/create', [PerangkatController::class, 'createProsemGroup'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/store', [PerangkatController::class, 'storeProsemGroup'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/delete/{id}', [PerangkatController::class, 'deleteProsemGroup'], [Middleware::permissionRequired('perangkat.delete')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/{id}', [PerangkatController::class, 'prosemDetailGroup'], [Middleware::permissionRequired('perangkat.view')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/{id}/sync', [PerangkatController::class, 'syncProsemFromCpatp'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/{id}/cetak-semua', [PerangkatController::class, 'cetakSemuaProsemGroup'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/create', [PerangkatController::class, 'createProsem'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/create/{groupId}', [PerangkatController::class, 'createProsem'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/store', [PerangkatController::class, 'storeProsem'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/store/{groupId}', [PerangkatController::class, 'storeProsem'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/edit/{id}', [PerangkatController::class, 'editProsem'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/update/{id}', [PerangkatController::class, 'updateProsem'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/detail/{id}', [PerangkatController::class, 'detailProsem'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/prosem/cetak/{id}', [PerangkatController::class, 'cetakProsem'], [Middleware::permissionRequired('perangkat.view')]);
$router->post('/kelola-perangkat-pembelajaran/prosem/delete/{id}', [PerangkatController::class, 'deleteProsem'], [Middleware::permissionRequired('perangkat.delete')]);

// RPP / Modul Ajar
$router->get('/kelola-perangkat-pembelajaran/rpp', [PerangkatController::class, 'rpp'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/group/create', [PerangkatController::class, 'createRppGroup'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/group/store', [PerangkatController::class, 'storeRppGroup'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/group/delete/{id}', [PerangkatController::class, 'deleteRppGroup'], [Middleware::permissionRequired('perangkat.delete')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/group/{id}', [PerangkatController::class, 'rppDetailGroup'], [Middleware::permissionRequired('perangkat.view')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/group/{id}/sync', [PerangkatController::class, 'syncRppFromCpatp'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/group/{id}/cetak-semua', [PerangkatController::class, 'cetakSemuaRppGroup'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/create', [PerangkatController::class, 'createRpp'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/create/{groupId}', [PerangkatController::class, 'createRpp'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/store', [PerangkatController::class, 'storeRpp'], [Middleware::permissionRequired('perangkat.create')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/store/{groupId}', [PerangkatController::class, 'storeRpp'], [Middleware::permissionRequired('perangkat.create')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/edit/{id}', [PerangkatController::class, 'editRpp'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/update/{id}', [PerangkatController::class, 'updateRpp'], [Middleware::permissionRequired('perangkat.update')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/detail/{id}', [PerangkatController::class, 'detailRpp'], [Middleware::permissionRequired('perangkat.view')]);
$router->get('/kelola-perangkat-pembelajaran/rpp/cetak/{id}', [PerangkatController::class, 'cetakRpp'], [Middleware::permissionRequired('perangkat.view')]);
$router->post('/kelola-perangkat-pembelajaran/rpp/delete/{id}', [PerangkatController::class, 'deleteRpp'], [Middleware::permissionRequired('perangkat.delete')]);

// Pusat Verifikasi & Lifecycle Actions (Wewenang Khusus Approve)
$router->get('/kelola-perangkat-pembelajaran/verifikasi', [PerangkatController::class, 'verifikasi'], [Middleware::permissionRequired('perangkat.approve')]);
$router->post('/kelola-perangkat-pembelajaran/approve/{id}', [PerangkatController::class, 'approve'], [Middleware::permissionRequired('perangkat.approve')]);
$router->post('/kelola-perangkat-pembelajaran/reject/{id}', [PerangkatController::class, 'reject'], [Middleware::permissionRequired('perangkat.approve')]);
$router->post('/kelola-perangkat-pembelajaran/submit/{id}', [PerangkatController::class, 'submitReview'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/draft/{id}', [PerangkatController::class, 'draft'], [Middleware::permissionRequired('perangkat.update')]);
$router->post('/kelola-perangkat-pembelajaran/delete/{id}', [PerangkatController::class, 'delete'], [Middleware::permissionRequired('perangkat.delete')]);

// Base Routes Modul Tambahan Lainnya
$router->get('/kelola-nilai', [NilaiController::class, 'index'], [Middleware::permissionRequired('nilai.view')]);
$router->get('/kelola-nilai/group', [NilaiController::class, 'groupList'], [Middleware::permissionRequired('nilai.view')]);
$router->get('/kelola-nilai/group/create', [NilaiController::class, 'groupCreate'], [Middleware::permissionRequired('nilai.create')]);
$router->post('/kelola-nilai/group/store', [NilaiController::class, 'groupStore'], [Middleware::permissionRequired('nilai.create')]);
$router->get('/kelola-nilai/group/edit/{id}', [NilaiController::class, 'groupEdit'], [Middleware::permissionRequired('nilai.update')]);
$router->post('/kelola-nilai/group/update/{id}', [NilaiController::class, 'groupUpdate'], [Middleware::permissionRequired('nilai.update')]);
$router->post('/kelola-nilai/group/toggle-status/{id}', [NilaiController::class, 'groupToggleStatus'], [Middleware::permissionRequired('nilai.update')]);
$router->post('/kelola-nilai/group/delete/{id}', [NilaiController::class, 'groupDelete'], [Middleware::permissionRequired('nilai.delete')]);
$router->get('/kelola-nilai/input/{groupId}', [NilaiController::class, 'inputNilai'], [Middleware::permissionRequired('nilai.view')]);
$router->get('/kelola-nilai/input/{groupId}/guru/{guruId}', [NilaiController::class, 'guruDetail'], [Middleware::permissionRequired('nilai.view')]);
$router->get('/kelola-nilai/input/{groupId}/doc/{cpatpDocId}', [NilaiController::class, 'inputNilaiForm'], [Middleware::permissionRequired('nilai.create')]);
$router->post('/kelola-nilai/store/{groupId}/doc/{cpatpDocId}', [NilaiController::class, 'storeNilaiForm'], [Middleware::permissionRequired('nilai.create')]);
$router->post('/kelola-nilai/autosave/{groupId}/doc/{cpatpDocId}', [NilaiController::class, 'autoSaveNilai'], [Middleware::permissionRequired('nilai.create')]);
$router->get('/kelola-nilai/cetak/{groupId}/doc/{cpatpDocId}', [NilaiController::class, 'cetakNilai'], [Middleware::permissionRequired('nilai.view')]);
$router->get('/kelola-nilai/rekap', [NilaiController::class, 'rekap'], [Middleware::permissionRequired('nilai.rekap')]);
$router->get('/kelola-nilai/rekap/cetak', [NilaiController::class, 'rekapCetak'], [Middleware::permissionRequired('nilai.rekap')]);
$router->get('/kelola-nilai/rekap/export', [NilaiController::class, 'rekapExport'], [Middleware::permissionRequired('nilai.export')]);
$router->get('/kelola-raport', [RaportController::class, 'index'], [Middleware::permissionRequired('raport.view')]);
// Modul Kelola Absen Siswa
$router->get('/kelola-absen-siswa', [AbsenSiswaController::class, 'index'], [Middleware::permissionRequired('absen_siswa.view')]);

// Grup / Wadah Absen
$router->get('/kelola-absen-siswa/group', [AbsenSiswaController::class, 'groupList'], [Middleware::permissionRequired('absen_siswa.view')]);
$router->get('/kelola-absen-siswa/group/create', [AbsenSiswaController::class, 'groupCreate'], [Middleware::permissionRequired('absen_siswa.create')]);
$router->post('/kelola-absen-siswa/group/store', [AbsenSiswaController::class, 'groupStore'], [Middleware::permissionRequired('absen_siswa.create')]);
$router->get('/kelola-absen-siswa/group/edit/{id}', [AbsenSiswaController::class, 'groupEdit'], [Middleware::permissionRequired('absen_siswa.update')]);
$router->post('/kelola-absen-siswa/group/update/{id}', [AbsenSiswaController::class, 'groupUpdate'], [Middleware::permissionRequired('absen_siswa.update')]);
$router->post('/kelola-absen-siswa/group/toggle-status/{id}', [AbsenSiswaController::class, 'groupToggleStatus'], [Middleware::permissionRequired('absen_siswa.update')]);
$router->post('/kelola-absen-siswa/group/delete/{id}', [AbsenSiswaController::class, 'groupDelete'], [Middleware::permissionRequired('absen_siswa.delete')]);

// Buka Wadah Absen (Pilih Guru -> Muncul Semua Kelas yang Dia Ampu)
$router->get('/kelola-absen-siswa/input/{groupId}', [AbsenSiswaController::class, 'wadahDetail'], [Middleware::permissionRequired('absen_siswa.view')]);
$router->get('/kelola-absen-siswa/mapel/wadah/{groupId}', [AbsenSiswaController::class, 'wadahDetail'], [Middleware::permissionRequired('absen_siswa.view')]);

// Input Absen Mapel
$router->get('/kelola-absen-siswa/mapel', [AbsenSiswaController::class, 'absenMapel'], [Middleware::permissionRequired('absen_siswa.view')]);
$router->get('/kelola-absen-siswa/mapel/input', [AbsenSiswaController::class, 'absenMapelForm'], [Middleware::permissionRequired('absen_siswa.create')]);
$router->post('/kelola-absen-siswa/mapel/store', [AbsenSiswaController::class, 'absenMapelStore'], [Middleware::permissionRequired('absen_siswa.create')]);
$router->post('/kelola-absen-siswa/mapel/autosave', [AbsenSiswaController::class, 'absenMapelAutosave'], [Middleware::permissionRequired('absen_siswa.create')]);
$router->get('/kelola-absen-siswa/mapel/cetak', [AbsenSiswaController::class, 'absenMapelCetak'], [Middleware::permissionRequired('absen_siswa.view')]);

// Input Absen Kelas (Wali Kelas / Harian)
$router->get('/kelola-absen-siswa/kelas', [AbsenSiswaController::class, 'absenKelas'], [Middleware::permissionRequired('absen_siswa.view')]);
$router->get('/kelola-absen-siswa/kelas/input', [AbsenSiswaController::class, 'absenKelasForm'], [Middleware::permissionRequired('absen_siswa.create')]);
$router->post('/kelola-absen-siswa/kelas/store', [AbsenSiswaController::class, 'absenKelasStore'], [Middleware::permissionRequired('absen_siswa.create')]);
$router->post('/kelola-absen-siswa/kelas/autosave', [AbsenSiswaController::class, 'absenKelasAutosave'], [Middleware::permissionRequired('absen_siswa.create')]);
$router->get('/kelola-absen-siswa/kelas/cetak', [AbsenSiswaController::class, 'absenKelasCetak'], [Middleware::permissionRequired('absen_siswa.view')]);

// Rekap Absen Mapel + Kelas
$router->get('/kelola-absen-siswa/rekap', [AbsenSiswaController::class, 'rekap'], [Middleware::permissionRequired('absen_siswa.rekap')]);
$router->get('/kelola-absen-siswa/rekap/cetak', [AbsenSiswaController::class, 'rekapCetak'], [Middleware::permissionRequired('absen_siswa.rekap')]);
$router->get('/kelola-absen-siswa/rekap/export', [AbsenSiswaController::class, 'rekapExport'], [Middleware::permissionRequired('absen_siswa.export')]);
$router->get('/kelola-absen-pegawai', [AbsenPegawaiController::class, 'index'], [Middleware::permissionRequired('absen_pegawai.view')]);
// Modul Qur'an Siswa (Hub + 3 Jenjang: SD, PAUD, SMP & SMA)
$router->get('/kelola-quran-siswa', [QuranSiswaController::class, 'index'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa-sd', [QuranSiswaController::class, 'sdIndex'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa/sd', [QuranSiswaController::class, 'sdIndex'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa-paud', [QuranSiswaController::class, 'paudIndex'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa/paud', [QuranSiswaController::class, 'paudIndex'], [Middleware::permissionRequired('quran_siswa.view')]);

// Fitur 2: Pengaturan Target Qur'an PAUD (a. Tahsin, b. Tahfidz)
$router->get('/kelola-quran-siswa-paud/target', [QuranSiswaController::class, 'paudTargetList'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa-paud/target/create', [QuranSiswaController::class, 'paudTargetCreate'], [Middleware::permissionRequired('quran_siswa.create')]);
$router->post('/kelola-quran-siswa-paud/target/store', [QuranSiswaController::class, 'paudTargetStore'], [Middleware::permissionRequired('quran_siswa.create')]);
$router->get('/kelola-quran-siswa-paud/target/manage/{id}', [QuranSiswaController::class, 'paudTargetManage'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa-paud/target/{id}', [QuranSiswaController::class, 'paudTargetManage'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->post('/kelola-quran-siswa-paud/target/manage/{id}/store', [QuranSiswaController::class, 'paudTargetSaveMateri'], [Middleware::permissionRequired('quran_siswa.update')]);
$router->get('/kelola-quran-siswa-paud/target/edit/{id}', [QuranSiswaController::class, 'paudTargetEdit'], [Middleware::permissionRequired('quran_siswa.update')]);
$router->post('/kelola-quran-siswa-paud/target/update/{id}', [QuranSiswaController::class, 'paudTargetUpdate'], [Middleware::permissionRequired('quran_siswa.update')]);
$router->post('/kelola-quran-siswa-paud/target/toggle-status/{id}', [QuranSiswaController::class, 'paudTargetToggleStatus'], [Middleware::permissionRequired('quran_siswa.update')]);
$router->post('/kelola-quran-siswa-paud/target/delete/{id}', [QuranSiswaController::class, 'paudTargetDelete'], [Middleware::permissionRequired('quran_siswa.delete')]);

// Fitur 3: Input Penilaian Qur'an PAUD
$router->get('/kelola-quran-siswa-paud/penilaian', [QuranSiswaController::class, 'paudPenilaianIndex'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa-paud/penilaian/{groupId}', [QuranSiswaController::class, 'paudPenilaianForm'], [Middleware::permissionRequired('quran_siswa.create')]);
$router->post('/kelola-quran-siswa-paud/penilaian/store/{groupId}', [QuranSiswaController::class, 'paudPenilaianStore'], [Middleware::permissionRequired('quran_siswa.create')]);

// Fitur 4: Rekap Nilai Qur'an PAUD
$router->get('/kelola-quran-siswa-paud/rekap', [QuranSiswaController::class, 'paudRekapIndex'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa-paud/rekap/cetak', [QuranSiswaController::class, 'paudRekapCetak'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa-paud/rekap/export', [QuranSiswaController::class, 'paudRekapExport'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa-smp-sma', [QuranSiswaController::class, 'smpSmaIndex'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa/smp-sma', [QuranSiswaController::class, 'smpSmaIndex'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->post('/kelola-quran-siswa/setoran/store', [QuranSiswaController::class, 'storeSetoran'], [Middleware::permissionRequired('quran_siswa.create')]);
$router->post('/kelola-quran-siswa/setoran/delete/{id}', [QuranSiswaController::class, 'deleteSetoran'], [Middleware::permissionRequired('quran_siswa.delete')]);
$router->get('/kelola-quran-siswa/cetak', [QuranSiswaController::class, 'cetakRekap'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-quran-siswa/export', [QuranSiswaController::class, 'exportExcel'], [Middleware::permissionRequired('quran_siswa.view')]);
$router->get('/kelola-ujian', [UjianController::class, 'index'], [Middleware::permissionRequired('ujian.view')]);
$router->get('/kelola-quran-pegawai', [QuranPegawaiController::class, 'index'], [Middleware::permissionRequired('quran_pegawai.view')]);
$router->get('/kelola-cuti', [CutiController::class, 'index'], [Middleware::permissionRequired('cuti.view')]);
$router->get('/kelola-kpi', [KpiController::class, 'index'], [Middleware::permissionRequired('kpi.view')]);
$router->get('/kelola-ibadah-guru', [IbadahGuruController::class, 'index'], [Middleware::permissionRequired('ibadah_guru.view')]);
$router->get('/kelola-spmb', [SpmbController::class, 'index'], [Middleware::permissionRequired('spmb.view')]);
$router->get('/kelola-pelanggaran-siswa', [PelanggaranSiswaController::class, 'index'], [Middleware::permissionRequired('pelanggaran_siswa.view')]);
$router->get('/kelola-izin-guru', [IzinGuruController::class, 'index'], [Middleware::permissionRequired('izin_guru.view')]);
$router->get('/kelola-buku-angkatan-siswa', [BukuAngkatanSiswaController::class, 'index'], [Middleware::permissionRequired('buku_angkatan.view')]);

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

$router->get('/pengaturan-sistem/master-pembelajaran', [SettingsController::class, 'masterPembelajaran'], [Middleware::permissionRequired('settings.view')]);
$router->post('/pengaturan-sistem/master-pembelajaran/store', [SettingsController::class, 'storeMataPelajaran'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/master-pembelajaran/update/{id}', [SettingsController::class, 'updateMataPelajaran'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/master-pembelajaran/toggle-aktif/{id}', [SettingsController::class, 'toggleAktifMataPelajaran'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/master-pembelajaran/delete/{id}', [SettingsController::class, 'deleteMataPelajaran'], [Middleware::permissionRequired('settings.delete')]);
$router->post('/pengaturan-sistem/master-pembelajaran/bulk-delete', [SettingsController::class, 'bulkDeleteMataPelajaran'], [Middleware::permissionRequired('settings.delete')]);
$router->post('/pengaturan-sistem/master-pembelajaran/delete-all', [SettingsController::class, 'deleteAllMataPelajaran'], [Middleware::permissionRequired('settings.delete')]);

$router->get('/pengaturan-sistem/reset-data', [SettingsController::class, 'resetDataView'], [Middleware::permissionRequired('settings.view')]);
$router->post('/pengaturan-sistem/update', [SettingsController::class, 'update'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/reset-data/process', [SettingsController::class, 'resetData'], [Middleware::permissionRequired('settings.update')]);
$router->post('/pengaturan-sistem/reset', [SettingsController::class, 'resetData'], [Middleware::permissionRequired('settings.reset')]);

// -- Portal Guru Mobile (PWA) --
$router->get('/mobile', [PortalGuruController::class, 'beranda'], [[Middleware::class, 'authRequired']]);
$router->get('/portal-guru', [PortalGuruController::class, 'beranda'], [[Middleware::class, 'authRequired']]);
$router->get('/guru', [PortalGuruController::class, 'beranda'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/absen', [PortalGuruController::class, 'absen'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/jurnal', [PortalGuruController::class, 'jurnal'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/kelas', [PortalGuruController::class, 'kelas'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/absensi-kelas', [PortalGuruController::class, 'absensiKelas'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/murid', [PortalGuruController::class, 'murid'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/profil', [PortalGuruController::class, 'profil'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/notifikasi', [PortalGuruController::class, 'notifikasi'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/materi', [PortalGuruController::class, 'materi'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/buat-tugas', [PortalGuruController::class, 'buatTugas'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/pesan-kelas', [PortalGuruController::class, 'pesanKelas'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/bank-soal', [PortalGuruController::class, 'bankSoal'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/quran', [PortalGuruController::class, 'quran'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/dzikir', [PortalGuruController::class, 'dzikir'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/keterlambatan-siswa', [PortalGuruController::class, 'keterlambatanSiswa'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/izin', [PortalGuruController::class, 'izin'], [[Middleware::class, 'authRequired']]);
$router->get('/mobile/cuti', [PortalGuruController::class, 'cuti'], [[Middleware::class, 'authRequired']]);

// -- PWA Dynamic Icon Route (Always Prioritize App Icon Uploaded from Pengaturan Sistem) --
$router->get('/pwa-icon.png', function() {
    $activeIcon = '';
    if (defined('SYS_APP_FAVICON') && !empty(SYS_APP_FAVICON)) {
        $cand = BASE_PATH . '/' . ltrim(SYS_APP_FAVICON, '/');
        if (file_exists($cand)) $activeIcon = $cand;
    }
    if (empty($activeIcon) && defined('SYS_APP_LOGO') && !empty(SYS_APP_LOGO)) {
        $cand = BASE_PATH . '/' . ltrim(SYS_APP_LOGO, '/');
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

    header('Content-Type: image/png');
    readfile($activeIcon);
    exit;
});

// -- PWA Manifest & Service Worker Routes --
$router->get('/manifest.json', function() {
    header('Content-Type: application/manifest+json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    $manifestPath = PUBLIC_PATH . '/manifest.json';
    if (file_exists($manifestPath)) {
        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);
        if (is_array($manifest)) {
            $manifest['name'] = (defined('SYS_APP_NAME') && SYS_APP_NAME ? SYS_APP_NAME : 'Portal BIP') . ' - Portal Guru';
            $manifest['short_name'] = 'Portal Guru';
            $manifest['start_url'] = url('mobile?utm_source=pwa');
            $manifest['scope'] = rtrim(url(''), '/') . '/';
            $manifest['id'] = 'portal-guru-bip-app';

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
});
$router->get('/sw.js', function() {
    header('Content-Type: application/javascript; charset=utf-8');
    header('Service-Worker-Allowed: /');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    readfile(PUBLIC_PATH . '/sw.js');
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

// -- API Routes (JSON) --
$router->get('/api/quran/surat', [PortalGuruController::class, 'apiSuratList']);
$router->get('/api/quran/surat/{nomor}', [PortalGuruController::class, 'apiSuratDetail']);
$router->get('/api/quran/page/{page}', [PortalGuruController::class, 'apiPageDetail']);
$router->get('/api/modules', function() {
    Middleware::authRequired();
    $modules = RBAC::getAccessibleModules();
    Response::json(['success' => true, 'data' => $modules]);
});

// ── Dispatch ────────────────────────────────────────
$router->dispatch();
