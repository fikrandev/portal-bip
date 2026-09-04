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
$router->get('/kelola-kelas', [KelasController::class, 'index'], [[]]);
$router->get('/kelola-kelas/create', [KelasController::class, 'create'], [[]]);
$router->post('/kelola-kelas/store', [KelasController::class, 'store'], [[]]);
$router->get('/kelola-kelas/edit/{id}', [KelasController::class, 'edit'], [[]]);
$router->post('/kelola-kelas/update/{id}', [KelasController::class, 'update'], [[]]);
$router->post('/kelola-kelas/delete/{id}', [KelasController::class, 'delete'], [[]]);
$router->post('/kelola-kelas/copy', [KelasController::class, 'copyClasses'], [[]]);
$router->post('/kelola-kelas/sync-dapodik', [KelasController::class, 'syncDapodikOnline'], [[]]);

// -- Kelola Pegawai --
$router->get('/kelola-pegawai/statistik', [PegawaiController::class, 'statistik'], [[]]);
$router->get('/kelola-pegawai', [PegawaiController::class, 'index'], [[]]);
$router->get('/kelola-pegawai/create', [PegawaiController::class, 'create'], [[]]);
$router->post('/kelola-pegawai/store', [PegawaiController::class, 'store'], [[]]);
$router->get('/kelola-pegawai/edit/{id}', [PegawaiController::class, 'edit'], [[]]);
$router->post('/kelola-pegawai/update/{id}', [PegawaiController::class, 'update'], [[]]);
$router->post('/kelola-pegawai/delete/{id}', [PegawaiController::class, 'delete'], [[]]);
$router->get('/kelola-pegawai/export', [PegawaiController::class, 'export'], [[]]);
$router->get('/kelola-pegawai/template', [PegawaiController::class, 'downloadTemplate'], [[]]);
$router->post('/kelola-pegawai/import', [PegawaiController::class, 'import'], [[]]);
$router->get('/kelola-pegawai/keluar', [PegawaiController::class, 'keluar'], [[]]);
$router->get('/kelola-pegawai/cetak-cv/{id}', [PegawaiController::class, 'cetakCv'], [[]]);

// -- Kelola Penugasan & Grup SK --
$router->get('/kelola-pegawai/penugasan', [PegawaiController::class, 'penugasan'], [[]]);
$router->get('/kelola-pegawai/penugasan/grup/create', [PegawaiController::class, 'createGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/grup/store', [PegawaiController::class, 'storeGrup'], [[]]);
$router->get('/kelola-pegawai/penugasan/grup/edit/{id}', [PegawaiController::class, 'editGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/grup/update/{id}', [PegawaiController::class, 'updateGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/grup/delete/{id}', [PegawaiController::class, 'deleteGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/grup/set-aktif/{id}', [PegawaiController::class, 'setAktifGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/grup/toggle-aktif/{id}', [PegawaiController::class, 'toggleAktifGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/grup/salin/{id}', [PegawaiController::class, 'salinGrup'], [[]]);

// Detail Anggota Penugasan dalam Grup
$router->get('/kelola-pegawai/penugasan/grup/{id}', [PegawaiController::class, 'detailGrup'], [[]]);
$router->get('/kelola-pegawai/penugasan/grup/{id}/cetak', [PegawaiController::class, 'cetakSkGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/grup/{id}/update-sk-meta', [PegawaiController::class, 'updateSkMeta'], [[]]);
$router->get('/kelola-pegawai/penugasan/grup/{id}/create', [PegawaiController::class, 'createPenugasanGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/grup/{id}/store', [PegawaiController::class, 'storePenugasanGrup'], [[]]);
$router->get('/kelola-pegawai/penugasan/detail/edit/{id}', [PegawaiController::class, 'editPenugasanGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/detail/update/{id}', [PegawaiController::class, 'updatePenugasanGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/detail/delete/{id}', [PegawaiController::class, 'deletePenugasanGrup'], [[]]);

// Aliases for Penugasan member actions
$router->get('/kelola-pegawai/penugasan/{id}/edit', [PegawaiController::class, 'editPenugasanGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/{id}/update', [PegawaiController::class, 'updatePenugasanGrup'], [[]]);
$router->post('/kelola-pegawai/penugasan/{id}/delete', [PegawaiController::class, 'deletePenugasanGrup'], [[]]);

// -- Riwayat Karir Pegawai & Guru (Otomatis dari SK & Manual) --
$router->get('/kelola-pegawai/karir', [PegawaiController::class, 'karir'], [[]]);
$router->get('/kelola-pegawai/karir/create', [PegawaiController::class, 'createKarir'], [[]]);
$router->post('/kelola-pegawai/karir/store', [PegawaiController::class, 'storeKarir'], [[]]);
$router->get('/kelola-pegawai/karir/edit/{id}', [PegawaiController::class, 'editKarir'], [[]]);
$router->post('/kelola-pegawai/karir/update/{id}', [PegawaiController::class, 'updateKarir'], [[]]);
$router->post('/kelola-pegawai/karir/delete/{id}', [PegawaiController::class, 'deleteKarir'], [[]]);
$router->get('/kelola-pegawai/karir/pegawai/{id}', [PegawaiController::class, 'timelinePegawai'], [[]]);

// -- Prestasi & Penghargaan Pegawai / Guru --
$router->get('/kelola-pegawai/prestasi', [PegawaiController::class, 'prestasi'], [[]]);
$router->get('/kelola-pegawai/prestasi/create', [PegawaiController::class, 'createPrestasi'], [[]]);
$router->post('/kelola-pegawai/prestasi/store', [PegawaiController::class, 'storePrestasi'], [[]]);
$router->get('/kelola-pegawai/prestasi/edit/{id}', [PegawaiController::class, 'editPrestasi'], [[]]);
$router->post('/kelola-pegawai/prestasi/update/{id}', [PegawaiController::class, 'updatePrestasi'], [[]]);
$router->post('/kelola-pegawai/prestasi/delete/{id}', [PegawaiController::class, 'deletePrestasi'], [[]]);
$router->get('/kelola-pegawai/prestasi/pegawai/{id}', [PegawaiController::class, 'prestasiPegawai'], [[]]);

// -- Riwayat Pelatihan, Diklat & Workshop Pegawai / Guru --
$router->get('/kelola-pegawai/pelatihan', [PegawaiController::class, 'pelatihan'], [[]]);
$router->get('/kelola-pegawai/pelatihan/create', [PegawaiController::class, 'createPelatihan'], [[]]);
$router->post('/kelola-pegawai/pelatihan/store', [PegawaiController::class, 'storePelatihan'], [[]]);
$router->get('/kelola-pegawai/pelatihan/edit/{id}', [PegawaiController::class, 'editPelatihan'], [[]]);
$router->post('/kelola-pegawai/pelatihan/update/{id}', [PegawaiController::class, 'updatePelatihan'], [[]]);
$router->post('/kelola-pegawai/pelatihan/delete/{id}', [PegawaiController::class, 'deletePelatihan'], [[]]);
$router->get('/kelola-pegawai/pelatihan/pegawai/{id}', [PegawaiController::class, 'pelatihanPegawai'], [[]]);

// -- Kelola Perangkat Pembelajaran --
$router->get('/kelola-perangkat-pembelajaran', [PerangkatController::class, 'dashboard'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/dashboard', [PerangkatController::class, 'dashboard'], [[]]);
$router->get('/kelola-rpp', function() {
    Response::redirect(url('kelola-perangkat-pembelajaran'));
});

// Jadwal Pelajaran & Auto-Generator
$router->get('/kelola-perangkat-pembelajaran/jadwal', [JadwalController::class, 'index'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/create', [JadwalController::class, 'create'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/store', [JadwalController::class, 'store'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/edit/{id}', [JadwalController::class, 'edit'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/update/{id}', [JadwalController::class, 'update'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/delete/{id}', [JadwalController::class, 'delete'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/set-active/{id}', [JadwalController::class, 'setActive'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{id}', [JadwalController::class, 'pengaturanJp'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/pengaturan-jp/{id}', [JadwalController::class, 'simpanPengaturanJp'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-add/{id}', [JadwalController::class, 'tambahSlotKhusus'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-delete/{id}/{slotId}', [JadwalController::class, 'hapusSlotWaktu'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-edit/{id}/{slotId}', [JadwalController::class, 'editSlotWaktu'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/slot-sync-day/{id}/{hari}', [JadwalController::class, 'syncHari'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/generate/{id}', [JadwalController::class, 'generate'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/jadwal/run-generate/{id}', [JadwalController::class, 'runGenerate'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/matriks/{id}', [JadwalController::class, 'matriks'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/cetak-kelas/{id}', [JadwalController::class, 'cetakKelas'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/cetak-guru/{id}', [JadwalController::class, 'cetakGuru'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/jadwal/export/{id}', [JadwalController::class, 'export'], [[]]);

// Kalender Pendidikan (Kaldik)
$router->get('/kelola-perangkat-pembelajaran/kaldik', [PerangkatController::class, 'kaldik'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/create', [PerangkatController::class, 'createKaldik'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/store', [PerangkatController::class, 'storeKaldik'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/edit/{id}', [PerangkatController::class, 'editKaldik'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/update/{id}', [PerangkatController::class, 'updateKaldik'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/detail/{id}', [PerangkatController::class, 'detailKaldik'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/kaldik/cetak/{id}', [PerangkatController::class, 'cetakKaldik'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/toggle-active/{id}', [PerangkatController::class, 'toggleActiveKaldik'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/agenda/add/{id}', [PerangkatController::class, 'addKaldikAgenda'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/kaldik/agenda/delete/{id}', [PerangkatController::class, 'deleteKaldikAgenda'], [[]]);

// Rincian Hari Efektif (HEB & HES Auto-Generated)
$router->get('/kelola-perangkat-pembelajaran/rincian-hari-efektif', [PerangkatController::class, 'rincianHariEfektif'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/rincian-hari-efektif/cetak', [PerangkatController::class, 'cetakRincianHariEfektif'], [[]]);

// Hari Efektif Sekolah (HES)
$router->get('/kelola-perangkat-pembelajaran/hes', [PerangkatController::class, 'hes'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/hes/create', [PerangkatController::class, 'createHes'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/hes/store', [PerangkatController::class, 'storeHes'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/hes/edit/{id}', [PerangkatController::class, 'editHes'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/hes/update/{id}', [PerangkatController::class, 'updateHes'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/hes/detail/{id}', [PerangkatController::class, 'detailHes'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/hes/cetak/{id}', [PerangkatController::class, 'cetakHes'], [[]]);

// Hari Efektif Belajar (HEB)
$router->get('/kelola-perangkat-pembelajaran/heb', [PerangkatController::class, 'heb'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/heb/create', [PerangkatController::class, 'createHeb'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/heb/store', [PerangkatController::class, 'storeHeb'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/heb/edit/{id}', [PerangkatController::class, 'editHeb'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/heb/update/{id}', [PerangkatController::class, 'updateHeb'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/heb/detail/{id}', [PerangkatController::class, 'detailHeb'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/heb/cetak/{id}', [PerangkatController::class, 'cetakHeb'], [[]]);

// Capaian Pembelajaran & Alur Tujuan Pembelajaran (CP & ATP)
$router->get('/kelola-perangkat-pembelajaran/cpatp', [PerangkatController::class, 'cpatp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/ajax-penugasan/{guruId}', [PerangkatController::class, 'getPenugasanAjax'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/ajax-jadwal/{guruId}', [PerangkatController::class, 'getJadwalHariAjax'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/group/create', [PerangkatController::class, 'createCpatpGroup'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/group/store', [PerangkatController::class, 'storeCpatpGroup'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/group/delete/{id}', [PerangkatController::class, 'deleteCpatpGroup'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/group/{id}', [PerangkatController::class, 'cpatpDetailGroup'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/create/{groupId}', [PerangkatController::class, 'createCpatp'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/store/{groupId}', [PerangkatController::class, 'storeCpatp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/edit/{id}', [PerangkatController::class, 'editCpatp'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/update/{id}', [PerangkatController::class, 'updateCpatp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/detail/{id}', [PerangkatController::class, 'detailCpatp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/cpatp/cetak/{id}', [PerangkatController::class, 'cetakCpatp'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/cpatp/delete/{id}', [PerangkatController::class, 'deleteCpatp'], [[]]);

// Program Tahunan (Prota)
$router->get('/kelola-perangkat-pembelajaran/prota', [PerangkatController::class, 'prota'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prota/create', [PerangkatController::class, 'createProta'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prota/store', [PerangkatController::class, 'storeProta'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prota/edit/{id}', [PerangkatController::class, 'editProta'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prota/update/{id}', [PerangkatController::class, 'updateProta'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prota/detail/{id}', [PerangkatController::class, 'detailProta'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prota/cetak/{id}', [PerangkatController::class, 'cetakProta'], [[]]);

// Program Semester (Prosem)
$router->get('/kelola-perangkat-pembelajaran/prosem', [PerangkatController::class, 'prosem'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/create', [PerangkatController::class, 'createProsemGroup'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/store', [PerangkatController::class, 'storeProsemGroup'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/delete/{id}', [PerangkatController::class, 'deleteProsemGroup'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/{id}', [PerangkatController::class, 'prosemDetailGroup'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prosem/group/{id}/sync', [PerangkatController::class, 'syncProsemFromCpatp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prosem/group/{id}/cetak-semua', [PerangkatController::class, 'cetakSemuaProsemGroup'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prosem/create', [PerangkatController::class, 'createProsem'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prosem/create/{groupId}', [PerangkatController::class, 'createProsem'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prosem/store', [PerangkatController::class, 'storeProsem'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prosem/store/{groupId}', [PerangkatController::class, 'storeProsem'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prosem/edit/{id}', [PerangkatController::class, 'editProsem'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prosem/update/{id}', [PerangkatController::class, 'updateProsem'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prosem/detail/{id}', [PerangkatController::class, 'detailProsem'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/prosem/cetak/{id}', [PerangkatController::class, 'cetakProsem'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/prosem/delete/{id}', [PerangkatController::class, 'deleteProsem'], [[]]);

// RPP / Modul Ajar
$router->get('/kelola-perangkat-pembelajaran/rpp', [PerangkatController::class, 'rpp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/rpp/create', [PerangkatController::class, 'createRpp'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/rpp/store', [PerangkatController::class, 'storeRpp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/rpp/edit/{id}', [PerangkatController::class, 'editRpp'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/rpp/update/{id}', [PerangkatController::class, 'updateRpp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/rpp/detail/{id}', [PerangkatController::class, 'detailRpp'], [[]]);
$router->get('/kelola-perangkat-pembelajaran/rpp/cetak/{id}', [PerangkatController::class, 'cetakRpp'], [[]]);

// Pusat Verifikasi & Lifecycle Actions
$router->get('/kelola-perangkat-pembelajaran/verifikasi', [PerangkatController::class, 'verifikasi'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/approve/{id}', [PerangkatController::class, 'approve'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/reject/{id}', [PerangkatController::class, 'reject'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/submit/{id}', [PerangkatController::class, 'submitReview'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/draft/{id}', [PerangkatController::class, 'draft'], [[]]);
$router->post('/kelola-perangkat-pembelajaran/delete/{id}', [PerangkatController::class, 'delete'], [[]]);

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
$router->get('/mobile', [PortalGuruController::class, 'beranda']);
$router->get('/portal-guru', [PortalGuruController::class, 'beranda']);
$router->get('/guru', [PortalGuruController::class, 'beranda']);
$router->get('/mobile/absen', [PortalGuruController::class, 'absen']);
$router->get('/mobile/jurnal', [PortalGuruController::class, 'jurnal']);
$router->get('/mobile/kelas', [PortalGuruController::class, 'kelas']);
$router->get('/mobile/absensi-kelas', [PortalGuruController::class, 'absensiKelas']);
$router->get('/mobile/murid', [PortalGuruController::class, 'murid']);
$router->get('/mobile/profil', [PortalGuruController::class, 'profil']);
$router->get('/mobile/notifikasi', [PortalGuruController::class, 'notifikasi']);
$router->get('/mobile/materi', [PortalGuruController::class, 'materi']);
$router->get('/mobile/buat-tugas', [PortalGuruController::class, 'buatTugas']);
$router->get('/mobile/pesan-kelas', [PortalGuruController::class, 'pesanKelas']);
$router->get('/mobile/bank-soal', [PortalGuruController::class, 'bankSoal']);
$router->get('/mobile/quran', [PortalGuruController::class, 'quran']);
$router->get('/mobile/dzikir', [PortalGuruController::class, 'dzikir']);
$router->get('/mobile/keterlambatan-siswa', [PortalGuruController::class, 'keterlambatanSiswa']);
$router->get('/mobile/izin', [PortalGuruController::class, 'izin']);
$router->get('/mobile/cuti', [PortalGuruController::class, 'cuti']);

// -- PWA Manifest & Service Worker Routes --
$router->get('/manifest.json', function() {
    header('Content-Type: application/manifest+json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: public, max-age=300');
    
    $manifestPath = PUBLIC_PATH . '/manifest.json';
    if (file_exists($manifestPath)) {
        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);
        if (is_array($manifest)) {
            $manifest['start_url'] = url('mobile?utm_source=pwa_installer');
            $manifest['scope'] = url('') . '/';
            $manifest['id'] = url('mobile');
            if (!empty($manifest['icons'])) {
                foreach ($manifest['icons'] as &$icon) {
                    if (!str_starts_with($icon['src'], 'http')) {
                        $icon['src'] = url('public/' . ltrim($icon['src'], '/'));
                    }
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

// -- Database Migration Routes --
$router->get('/desktop-migrate', function() {
    require_once BASE_PATH . '/desktop-migrate/migrate.php';
    exit;
});
$router->get('/mobile-migrate', function() {
    require_once BASE_PATH . '/mobile-migrate/migrate.php';
    exit;
});

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
