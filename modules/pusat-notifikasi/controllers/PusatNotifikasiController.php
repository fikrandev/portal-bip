<?php
/**
 * PusatNotifikasiController
 * Mengelola modul Pusat Notifikasi (Pengaturan) + API Feed/Read/Click/Push Subscribe untuk Web & Mobile.
 */
require_once MODULES_PATH . '/pusat-notifikasi/models/PusatNotifikasiModel.php';

class PusatNotifikasiController
{
    public static function index(): void
    {
        PusatNotifikasiModel::ensureSchema();

        $filters = [
            'search'   => trim((string)($_GET['search'] ?? '')),
            'tipe'     => trim((string)($_GET['tipe'] ?? '')),
            'platform' => trim((string)($_GET['platform'] ?? '')),
            'status'   => trim((string)($_GET['status'] ?? '')),
            'unit'     => trim((string)($_GET['unit'] ?? '')),
        ];

        $items = PusatNotifikasiModel::getList($filters);
        $stats = PusatNotifikasiModel::getStats();
        $roles = PusatNotifikasiModel::getRolesList(false);
        $manageRoles = PusatNotifikasiModel::getRolesList(true);
        $unitScope = PusatNotifikasiModel::getUserAllowedUnitsForManage();

        $canCreate = RBAC::hasPermission('notifikasi.create');
        $canUpdate = RBAC::hasPermission('notifikasi.update');
        $canSend   = RBAC::hasPermission('notifikasi.send');
        $canDelete = RBAC::hasPermission('notifikasi.delete');

        $pageTitle = 'Pusat Notifikasi';
        $breadcrumbs = [
            ['label' => 'Pengaturan'],
            ['label' => 'Pusat Notifikasi'],
        ];
        $customSidebar = MODULES_PATH . '/pengaturan-sistem/views/sidebar.php';

        ob_start();
        include MODULES_PATH . '/pusat-notifikasi/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Validasi & sanitasi input form buat/edit notifikasi sesuai hak akses unit RBAC
     */
    private static function extractValidatedPayload(array $post, array $unitScope): array
    {
        $judul = trim((string)($post['judul'] ?? ''));
        $pesan = trim((string)($post['pesan'] ?? ''));
        if ($judul === '' || $pesan === '') {
            return ['error' => 'Judul dan isi pesan notifikasi wajib diisi.'];
        }
        if (mb_strlen($judul) > 180) {
            $judul = mb_substr($judul, 0, 180);
        }

        $tipe = ($post['tipe'] ?? 'push') === 'popup' ? 'popup' : 'push';
        $rawPrioritas = trim((string)($post['prioritas'] ?? 'info'));
        $prioritas = in_array($rawPrioritas, ['info', 'success', 'warning', 'urgent'], true)
            ? $rawPrioritas
            : 'info';
        $rawPlatform = trim((string)($post['target_platform'] ?? 'both'));
        $platform = in_array($rawPlatform, ['both', 'web', 'mobile'], true)
            ? $rawPlatform
            : 'both';

        $allowedJenis = ['all', 'guru', 'support_system', 'otorisasi_admin', 'otorisasi_personal', 'role'];
        $rawJenisAkun = trim((string)($post['target_jenis_akun'] ?? 'all'));
        $roleIdsStr = null;

        if (preg_match('/^role:(\d+)$/', $rawJenisAkun, $m)) {
            $singleRoleId = (int)$m[1];
            $allRoles = PusatNotifikasiModel::getRolesList(false);
            $matchedRole = null;
            foreach ($allRoles as $ar) {
                if ((int)$ar['id'] === $singleRoleId) {
                    $matchedRole = $ar;
                    break;
                }
            }
            if (!$matchedRole) {
                return ['error' => 'Peran (role) yang dipilih tidak ditemukan di database.'];
            }
            if (empty($unitScope['can_all'])) {
                $rUnits = PusatNotifikasiModel::parseUnitsList($matchedRole['allowed_jenjang'] ?? '');
                if (!empty($rUnits) && empty(array_intersect($rUnits, $unitScope['units'] ?? []))) {
                    return ['error' => 'Anda tidak memiliki hak akses unit untuk memilih peran ' . $matchedRole['name'] . '.'];
                }
            }
            $jenisAkun = 'role';
            $roleIdsStr = (string)$singleRoleId;
        } else {
            $jenisAkun = in_array($rawJenisAkun, $allowedJenis, true) ? $rawJenisAkun : 'all';
            if ($jenisAkun === 'role') {
                $rawRoles = isset($post['target_role_ids']) && is_array($post['target_role_ids'])
                    ? $post['target_role_ids']
                    : [];
                $cleanRoles = array_values(array_unique(array_filter(array_map('intval', $rawRoles), fn($v) => $v > 0)));
                if (empty($cleanRoles)) {
                    return ['error' => 'Pilih minimal satu peran (role) tujuan untuk notifikasi.'];
                }
                $roleIdsStr = implode(',', $cleanRoles);
            }
        }

        // Validasi link opsional agar aman (hanya http/https atau path internal diawali /)
        $linkUrl = trim((string)($post['link_url'] ?? ''));
        if ($linkUrl !== '') {
            if (!preg_match('#^https?://#i', $linkUrl) && strpos($linkUrl, '/') !== 0) {
                if (strpos($linkUrl, 'javascript:') !== false || strpos($linkUrl, 'data:') !== false) {
                    return ['error' => 'Format tautan (URL) tidak diizinkan.'];
                }
                $linkUrl = '/' . ltrim($linkUrl, '/');
            }
            if (mb_strlen($linkUrl) > 500) {
                return ['error' => 'Panjang URL tautan maksimal 500 karakter.'];
            }
        } else {
            $linkUrl = null;
        }

        $linkLabel = trim((string)($post['link_label'] ?? ''));
        $linkLabel = $linkLabel !== '' ? mb_substr($linkLabel, 0, 80) : 'Buka Tautan';

        // Enforce RBAC Unit Scope
        $selectedAll = !empty($post['target_unit_all']);
        $rawUnits = isset($post['target_units']) && is_array($post['target_units']) ? $post['target_units'] : [];
        $cleanUnits = [];
        foreach ($rawUnits as $u) {
            $norm = PusatNotifikasiModel::normalizeUnit((string)$u);
            if ($norm !== '' && $norm !== 'ALL' && !in_array($norm, $cleanUnits, true)) {
                $cleanUnits[] = $norm;
            }
        }

        if (!empty($unitScope['can_all'])) {
            $targetUnitsStr = ($selectedAll || empty($cleanUnits)) ? 'ALL' : implode(',', $cleanUnits);
        } else {
            $allowedUnits = $unitScope['units'] ?? [];
            $permittedUnits = array_values(array_intersect($cleanUnits, $allowedUnits));
            if ($selectedAll) {
                $permittedUnits = $allowedUnits;
            }
            if (empty($permittedUnits)) {
                return ['error' => 'Akun Anda dibatasi per-unit. Pilih minimal satu unit yang Anda kelola (' . implode(', ', $allowedUnits) . ').'];
            }
            $targetUnitsStr = implode(',', $permittedUnits);
        }

        $intervalMenit = (int)($post['popup_interval_menit'] ?? 5);
        $intervalMenit = max(1, min(1440, $intervalMenit));
        $pakaiSuara = !empty($post['pakai_suara']) ? 1 : 0;

        $berakhirAt = trim((string)($post['berakhir_at'] ?? ''));
        $berakhirDb = null;
        if ($berakhirAt !== '') {
            $ts = strtotime($berakhirAt);
            if ($ts !== false) {
                $berakhirDb = date('Y-m-d H:i:s', $ts);
            }
        }

        return [
            'data' => [
                'judul'                => $judul,
                'pesan'                => $pesan,
                'link_url'             => $linkUrl,
                'link_label'           => $linkLabel,
                'tipe'                 => $tipe,
                'prioritas'            => $prioritas,
                'target_platform'      => $platform,
                'target_jenis_akun'    => $jenisAkun,
                'target_role_ids'      => $roleIdsStr,
                'target_units'         => $targetUnitsStr,
                'popup_interval_menit' => $intervalMenit,
                'pakai_suara'          => $pakaiSuara,
                'berakhir_at'          => $berakhirDb,
            ],
        ];
    }

    public static function store(): void
    {
        CSRF::validate();
        $redirect = url('pusat-notifikasi');
        $unitScope = PusatNotifikasiModel::getUserAllowedUnitsForManage();

        $parsed = self::extractValidatedPayload($_POST, $unitScope);
        if (!empty($parsed['error'])) {
            Response::redirect($redirect, $parsed['error'], 'error');
        }

        $data = $parsed['data'];
        $data['status'] = 'draft';
        $data['created_by'] = Auth::id();
        $data['created_by_name'] = Auth::name() ?: 'Admin';

        $newId = PusatNotifikasiModel::create($data);
        $actionMode = trim((string)($_POST['action_mode'] ?? 'draft'));

        if ($actionMode === 'send' && RBAC::hasPermission('notifikasi.send')) {
            $sendRes = PusatNotifikasiModel::sendNotification($newId);
            Response::redirect($redirect, $sendRes['message'] ?? 'Notifikasi berhasil dikirim.', 'success');
        }

        Response::redirect($redirect, 'Notifikasi berhasil disimpan sebagai draft.', 'success');
    }

    public static function update(int $id): void
    {
        CSRF::validate();
        $redirect = url('pusat-notifikasi');
        $existing = PusatNotifikasiModel::findById($id);
        if (!$existing) {
            Response::redirect($redirect, 'Data notifikasi tidak ditemukan.', 'error');
        }
        if (!PusatNotifikasiModel::canUserManageNotification($existing)) {
            Response::redirect($redirect, 'Anda tidak memiliki hak akses unit untuk mengubah notifikasi ini.', 'error');
        }

        $unitScope = PusatNotifikasiModel::getUserAllowedUnitsForManage();
        $parsed = self::extractValidatedPayload($_POST, $unitScope);
        if (!empty($parsed['error'])) {
            Response::redirect($redirect, $parsed['error'], 'error');
        }

        $data = $parsed['data'];
        PusatNotifikasiModel::update($id, $data);

        $actionMode = trim((string)($_POST['action_mode'] ?? 'save'));
        if ($actionMode === 'send' && RBAC::hasPermission('notifikasi.send')) {
            $sendRes = PusatNotifikasiModel::sendNotification($id);
            Response::redirect($redirect, $sendRes['message'] ?? 'Notifikasi diperbarui & berhasil dikirim.', 'success');
        }

        Response::redirect($redirect, 'Perubahan notifikasi berhasil disimpan.', 'success');
    }

    public static function send(int $id): void
    {
        CSRF::validate();
        $redirect = url('pusat-notifikasi');
        $existing = PusatNotifikasiModel::findById($id);
        if (!$existing) {
            Response::redirect($redirect, 'Data notifikasi tidak ditemukan.', 'error');
        }
        if (!PusatNotifikasiModel::canUserManageNotification($existing)) {
            Response::redirect($redirect, 'Anda tidak memiliki hak akses unit untuk mengirim notifikasi ini.', 'error');
        }

        $res = PusatNotifikasiModel::sendNotification($id);
        Response::redirect($redirect, $res['message'] ?? 'Notifikasi berhasil dikirim.', !empty($res['success']) ? 'success' : 'error');
    }

    public static function toggleStatus(int $id): void
    {
        CSRF::validate();
        $redirect = url('pusat-notifikasi');
        $existing = PusatNotifikasiModel::findById($id);
        if (!$existing) {
            Response::redirect($redirect, 'Data notifikasi tidak ditemukan.', 'error');
        }
        if (!PusatNotifikasiModel::canUserManageNotification($existing)) {
            Response::redirect($redirect, 'Anda tidak memiliki hak akses unit untuk mengubah status notifikasi ini.', 'error');
        }

        $nextStatus = ($existing['status'] ?? '') === 'aktif' ? 'nonaktif' : 'aktif';
        PusatNotifikasiModel::toggleStatus($id, $nextStatus);
        $label = $nextStatus === 'aktif' ? 'diaktifkan kembali' : 'dihentikan / dinonaktifkan';
        Response::redirect($redirect, "Notifikasi berhasil {$label}.", 'success');
    }

    public static function delete(int $id): void
    {
        CSRF::validate();
        $redirect = url('pusat-notifikasi');
        $existing = PusatNotifikasiModel::findById($id);
        if (!$existing) {
            Response::redirect($redirect, 'Data notifikasi tidak ditemukan.', 'error');
        }
        if (!PusatNotifikasiModel::canUserManageNotification($existing)) {
            Response::redirect($redirect, 'Anda tidak memiliki hak akses unit untuk menghapus notifikasi ini.', 'error');
        }

        PusatNotifikasiModel::delete($id);
        Response::redirect($redirect, 'Notifikasi berhasil dihapus.', 'success');
    }

    public static function readers(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $existing = PusatNotifikasiModel::findById($id);
        if (!$existing || !PusatNotifikasiModel::canUserManageNotification($existing)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Akses ditolak atau data tidak ditemukan.']);
            exit;
        }
        $readers = PusatNotifikasiModel::getReaders($id);
        echo json_encode([
            'success' => true,
            'judul'   => $existing['judul'],
            'readers' => $readers,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * API Client Polling: Ambil feed Push & Popup aktif untuk user login (Web & Mobile)
     */
    public static function apiFeed(): void
    {
        $userId = (int)(Auth::id() ?? 0);
        $canManage = $userId > 0 && class_exists('RBAC') && RBAC::hasPermission('notifikasi.view');
        // Lepaskan session lock segera agar polling tidak memblokir navigasi tab lain
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Sesi berakhir.']);
            exit;
        }

        try {
            $platform = trim((string)($_GET['platform'] ?? 'web'));
            $platform = ($platform === 'mobile') ? 'mobile' : 'web';

            $feed = PusatNotifikasiModel::getFeedForUser($userId, $platform);

            require_once BASE_PATH . '/core/WebPushService.php';
            $profile = PusatNotifikasiModel::resolveUserProfile($userId);
            $vapidPub = '';
            $subscribed = false;
            try {
                $vapidPub = WebPushService::getPublicKey();
                $subscribed = WebPushService::hasActiveSubscription($userId, $profile['pegawai_id'] ?? null);
            } catch (Throwable $e) {}

            echo json_encode([
                'success'          => true,
                'user_id'          => $userId,
                'platform'         => $platform,
                'push_items'       => $feed['push_items'],
                'popup_items'      => $feed['popup_items'],
                'inbox_items'      => $feed['inbox_items'],
                'unread_count'     => $feed['unread_count'],
                'vapid_public_key' => $vapidPub,
                'push_subscribed'  => $subscribed,
                'can_manage'       => $canManage,
                'manage_url'       => function_exists('url') ? url('pusat-notifikasi') : '/pusat-notifikasi',
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal memuat feed notifikasi: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * API Client: Tandai 1 atau seluruh notifikasi sudah dibaca
     */
    public static function apiMarkRead(): void
    {
        $userId = (int)(Auth::id() ?? 0);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Content-Type: application/json; charset=utf-8');

        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $notifId = !empty($input['notifikasi_id']) ? (int)$input['notifikasi_id'] : null;
        $isAll = !empty($input['all']);
        $platform = (($input['platform'] ?? 'web') === 'mobile') ? 'mobile' : 'web';

        PusatNotifikasiModel::markRead($userId, $isAll ? null : $notifId, $platform, false);
        $feed = PusatNotifikasiModel::getFeedForUser($userId, $platform);

        echo json_encode([
            'success'      => true,
            'unread_count' => $feed['unread_count'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * API Client: Catat klik tautan notifikasi sekaligus tandai sudah dibaca
     */
    public static function apiMarkClick(): void
    {
        $userId = (int)(Auth::id() ?? 0);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Content-Type: application/json; charset=utf-8');

        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        $input = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $notifId = !empty($input['notifikasi_id']) ? (int)$input['notifikasi_id'] : 0;
        $platform = (($input['platform'] ?? 'web') === 'mobile') ? 'mobile' : 'web';

        if ($notifId > 0) {
            PusatNotifikasiModel::markRead($userId, $notifId, $platform, true);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * API Client: Simpan Web Push subscription dari Portal Web maupun Portal Mobile
     */
    public static function apiWebPushSubscribe(): void
    {
        $userId = (int)(Auth::id() ?? 0);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Content-Type: application/json; charset=utf-8');

        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        try {
            require_once BASE_PATH . '/core/WebPushService.php';
            $input = json_decode((string)file_get_contents('php://input'), true);
            if (!is_array($input)) {
                $input = $_POST;
            }

            $endpoint = trim((string)($input['endpoint'] ?? ''));
            $p256dh = trim((string)($input['keys']['p256dh'] ?? $input['p256dh'] ?? ''));
            $authToken = trim((string)($input['keys']['auth'] ?? $input['auth'] ?? ''));

            $profile = PusatNotifikasiModel::resolveUserProfile($userId);
            $pegawaiId = $profile['pegawai_id'] ?? null;
            $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');

            $res = WebPushService::subscribe($userId, $pegawaiId, $endpoint, $p256dh, $authToken, $ua);
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan subscription push: ' . $e->getMessage()]);
            exit;
        }
    }
}
