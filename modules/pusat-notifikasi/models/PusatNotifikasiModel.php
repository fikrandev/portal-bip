<?php
/**
 * PusatNotifikasiModel
 * Mengelola notifikasi Push (suara bawaan perangkat) & Popup berkala (setiap N menit)
 * untuk Portal Web & Portal Mobile dengan filter jenis akun serta RBAC unit.
 */
class PusatNotifikasiModel
{
    private static bool $schemaChecked = false;

    private static function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Pastikan tabel & registrasi modul/permission tersedia secara idempoten
     */
    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;

        try {
            $pdo = self::db()->getConnection();
            $hasTable = $pdo->query("SHOW TABLES LIKE 'pusat_notifikasi'")->fetchColumn();
            if ($hasTable !== false) {
                $hasReads = $pdo->query("SHOW TABLES LIKE 'pusat_notifikasi_reads'")->fetchColumn();
                if ($hasReads !== false) {
                    return;
                }
            }

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `pusat_notifikasi` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `judul` VARCHAR(180) NOT NULL,
                    `pesan` TEXT NOT NULL,
                    `link_url` VARCHAR(500) DEFAULT NULL,
                    `link_label` VARCHAR(80) DEFAULT 'Buka Tautan',
                    `tipe` ENUM('push', 'popup') NOT NULL DEFAULT 'push',
                    `prioritas` ENUM('info', 'success', 'warning', 'urgent') NOT NULL DEFAULT 'info',
                    `target_platform` ENUM('both', 'web', 'mobile') NOT NULL DEFAULT 'both',
                    `target_jenis_akun` VARCHAR(60) NOT NULL DEFAULT 'all',
                    `target_role_ids` VARCHAR(255) DEFAULT NULL,
                    `target_units` VARCHAR(255) NOT NULL DEFAULT 'ALL',
                    `popup_interval_menit` INT(11) NOT NULL DEFAULT 5,
                    `pakai_suara` TINYINT(1) NOT NULL DEFAULT 1,
                    `status` ENUM('draft', 'aktif', 'nonaktif') NOT NULL DEFAULT 'draft',
                    `mulai_at` DATETIME DEFAULT NULL,
                    `berakhir_at` DATETIME DEFAULT NULL,
                    `sent_at` DATETIME DEFAULT NULL,
                    `sent_count` INT(11) NOT NULL DEFAULT 0,
                    `push_device_sent` INT(11) NOT NULL DEFAULT 0,
                    `created_by` BIGINT UNSIGNED DEFAULT NULL,
                    `created_by_name` VARCHAR(120) DEFAULT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_pn_status_tipe` (`status`, `tipe`),
                    KEY `idx_pn_sent` (`sent_at`),
                    KEY `idx_pn_created` (`created_by`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `pusat_notifikasi_reads` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `notifikasi_id` BIGINT UNSIGNED NOT NULL,
                    `user_id` BIGINT UNSIGNED NOT NULL,
                    `platform` ENUM('web', 'mobile') NOT NULL DEFAULT 'web',
                    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                    `read_at` DATETIME DEFAULT NULL,
                    `clicked_at` DATETIME DEFAULT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_pnr_notif_user` (`notifikasi_id`, `user_id`),
                    KEY `idx_pnr_user_read` (`user_id`, `is_read`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Throwable $e) {
            error_log('PusatNotifikasiModel ensureSchema error: ' . $e->getMessage());
        }
    }

    /**
     * Normalisasi kode unit/jenjang secara konsisten
     */
    public static function normalizeUnit(?string $unit): string
    {
        $u = strtoupper(trim((string)$unit));
        if ($u === '') {
            return '';
        }
        if (in_array($u, ['TK', 'KB', 'PAUD/TK', 'TK/PAUD', 'PAUD IT', 'TK IT'], true)) {
            return 'PAUD';
        }
        if (preg_match('/^(SD|SMP|SMA)\s*IT$/', $u, $m) || preg_match('/^(SDIT|SMPIT|SMAIT)$/', $u, $m2)) {
            if (!empty($m[1])) {
                return $m[1];
            }
            if (!empty($m2[1])) {
                return substr($m2[1], 0, -2);
            }
        }
        return $u;
    }

    /**
     * Pecah string unit dipisah koma menjadi array unit ternormalisasi unik
     */
    public static function parseUnitsList(?string $unitsStr): array
    {
        $raw = explode(',', (string)$unitsStr);
        $out = [];
        foreach ($raw as $item) {
            $norm = self::normalizeUnit($item);
            if ($norm !== '' && !in_array($norm, $out, true)) {
                $out[] = $norm;
            }
        }
        return $out;
    }

    /**
     * Ambil daftar unit tersedia dari master_unit_tugas
     */
    public static function getAvailableUnits(): array
    {
        $units = [];
        try {
            $rows = self::db()->findAll("SELECT nama FROM `master_unit_tugas` ORDER BY id ASC");
            foreach ($rows as $r) {
                $norm = self::normalizeUnit($r['nama'] ?? '');
                if ($norm !== '' && $norm !== 'ALL' && !in_array($norm, $units, true)) {
                    $units[] = $norm;
                }
            }
        } catch (Throwable $e) {}

        if (empty($units)) {
            $units = ['PAUD', 'SD', 'SMP', 'SMA', 'YAYASAN', 'STAFF'];
        }
        return $units;
    }

    /**
     * Hak akses unit user yang sedang mengelola Pusat Notifikasi (RBAC Unit)
     */
    public static function getUserAllowedUnitsForManage(): array
    {
        if (class_exists('Auth') && Auth::isSuperAdmin()) {
            return [
                'can_all' => true,
                'units'   => self::getAvailableUnits(),
            ];
        }

        if (class_exists('Auth') && !Auth::isJenjangRestricted()) {
            return [
                'can_all' => true,
                'units'   => self::getAvailableUnits(),
            ];
        }

        $allowed = [];
        if (class_exists('Auth')) {
            foreach (Auth::getAllowedJenjang() as $j) {
                $norm = self::normalizeUnit($j);
                if ($norm !== '' && !in_array($norm, $allowed, true)) {
                    $allowed[] = $norm;
                }
            }
        }

        return [
            'can_all' => false,
            'units'   => $allowed,
        ];
    }

    /**
     * Cek apakah user login berhak mengelola (edit/kirim/toggle/hapus) record notifikasi ini
     */
    public static function canUserManageNotification(array $notif): bool
    {
        $scope = self::getUserAllowedUnitsForManage();
        if (!empty($scope['can_all'])) {
            return true;
        }

        $uid = class_exists('Auth') ? (int)(Auth::id() ?? 0) : 0;
        if ($uid > 0 && (int)($notif['created_by'] ?? 0) === $uid) {
            return true;
        }

        $targetUnits = trim((string)($notif['target_units'] ?? 'ALL'));
        if ($targetUnits === '' || strtoupper($targetUnits) === 'ALL') {
            return false;
        }

        $notifUnits = self::parseUnitsList($targetUnits);
        if (empty($notifUnits) || empty($scope['units'])) {
            return false;
        }

        foreach ($notifUnits as $nu) {
            if (!in_array($nu, $scope['units'], true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Daftar seluruh peran/role untuk target spesifik role
     */
    public static function getRolesList(): array
    {
        try {
            return self::db()->findAll("SELECT id, name, slug, allowed_jenjang FROM `roles` ORDER BY id ASC");
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Ambil daftar notifikasi dengan filter & pembatasan RBAC unit
     */
    public static function getList(array $filters = []): array
    {
        self::ensureSchema();
        $scope = self::getUserAllowedUnitsForManage();

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $term = '%' . trim((string)$filters['search']) . '%';
            $where[] = "(n.judul LIKE ? OR n.pesan LIKE ? OR n.created_by_name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }
        if (!empty($filters['tipe']) && in_array($filters['tipe'], ['push', 'popup'], true)) {
            $where[] = "n.tipe = ?";
            $params[] = $filters['tipe'];
        }
        if (!empty($filters['platform']) && in_array($filters['platform'], ['both', 'web', 'mobile'], true)) {
            $where[] = "n.target_platform = ?";
            $params[] = $filters['platform'];
        }
        if (!empty($filters['status']) && in_array($filters['status'], ['draft', 'aktif', 'nonaktif'], true)) {
            $where[] = "n.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['unit'])) {
            $uFilter = self::normalizeUnit((string)$filters['unit']);
            if ($uFilter === 'ALL') {
                $where[] = "UPPER(n.target_units) = 'ALL'";
            } elseif ($uFilter !== '') {
                $where[] = "(FIND_IN_SET(?, REPLACE(UPPER(n.target_units), ' ', '')) > 0)";
                $params[] = $uFilter;
            }
        }

        $sql = "
            SELECT n.*,
                   COALESCE(rc.read_count, 0) AS read_count,
                   COALESCE(rc.click_count, 0) AS click_count
            FROM `pusat_notifikasi` n
            LEFT JOIN (
                SELECT notifikasi_id,
                       SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) AS read_count,
                       SUM(CASE WHEN clicked_at IS NOT NULL THEN 1 ELSE 0 END) AS click_count
                FROM `pusat_notifikasi_reads`
                GROUP BY notifikasi_id
            ) rc ON rc.notifikasi_id = n.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY n.id DESC
        ";

        $rows = self::db()->findAll($sql, $params);
        if (!empty($scope['can_all'])) {
            return $rows;
        }

        $uid = class_exists('Auth') ? (int)(Auth::id() ?? 0) : 0;
        $allowedUnits = $scope['units'];
        return array_values(array_filter($rows, function ($row) use ($uid, $allowedUnits) {
            if ($uid > 0 && (int)($row['created_by'] ?? 0) === $uid) {
                return true;
            }
            $nUnits = self::parseUnitsList((string)($row['target_units'] ?? 'ALL'));
            if (in_array('ALL', $nUnits, true)) {
                return false;
            }
            return count(array_intersect($nUnits, $allowedUnits)) > 0;
        }));
    }

    /**
     * Ringkasan statistik kartu atas halaman Pusat Notifikasi
     */
    public static function getStats(): array
    {
        self::ensureSchema();
        $scope = self::getUserAllowedUnitsForManage();
        $list = self::getList();

        $total = count($list);
        $pushSent = 0;
        $popupAktif = 0;
        foreach ($list as $it) {
            if (($it['tipe'] ?? '') === 'push' && !empty($it['sent_at'])) {
                $pushSent++;
            }
            if (($it['tipe'] ?? '') === 'popup' && ($it['status'] ?? '') === 'aktif') {
                $popupAktif++;
            }
        }

        $activeDevices = 0;
        try {
            require_once BASE_PATH . '/core/WebPushService.php';
            WebPushService::ensureTablesExist();
            if (!empty($scope['can_all'])) {
                $row = self::db()->find("SELECT COUNT(DISTINCT endpoint_hash) AS cnt FROM `web_push_subscriptions` WHERE is_active = 1");
                $activeDevices = (int)($row['cnt'] ?? 0);
            } else {
                $subs = self::db()->findAll("SELECT DISTINCT endpoint_hash, user_id, pegawai_id FROM `web_push_subscriptions` WHERE is_active = 1");
                $allowedUnits = $scope['units'];
                $matchedHashes = [];
                foreach ($subs as $s) {
                    $uId = !empty($s['user_id']) ? (int)$s['user_id'] : 0;
                    if ($uId > 0) {
                        $prof = self::resolveUserProfile($uId);
                        if (count(array_intersect($prof['units'], $allowedUnits)) > 0) {
                            $matchedHashes[$s['endpoint_hash']] = true;
                        }
                    }
                }
                $activeDevices = count($matchedHashes);
            }
        } catch (Throwable $e) {}

        return [
            'total'          => $total,
            'push_sent'      => $pushSent,
            'popup_aktif'    => $popupAktif,
            'active_devices' => $activeDevices,
        ];
    }

    public static function findById(int $id): ?array
    {
        self::ensureSchema();
        $row = self::db()->find("SELECT * FROM `pusat_notifikasi` WHERE id = ? LIMIT 1", [$id]);
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        self::ensureSchema();
        return self::db()->insert('pusat_notifikasi', $data);
    }

    public static function update(int $id, array $data): bool
    {
        self::ensureSchema();
        return self::db()->update('pusat_notifikasi', $data, 'id = ?', [$id]) >= 0;
    }

    public static function toggleStatus(int $id, string $newStatus): bool
    {
        self::ensureSchema();
        if (!in_array($newStatus, ['draft', 'aktif', 'nonaktif'], true)) {
            return false;
        }
        $upd = ['status' => $newStatus];
        if ($newStatus === 'aktif') {
            $existing = self::findById($id);
            if ($existing && empty($existing['mulai_at'])) {
                $upd['mulai_at'] = date('Y-m-d H:i:s');
            }
        }
        return self::db()->update('pusat_notifikasi', $upd, 'id = ?', [$id]) >= 0;
    }

    public static function delete(int $id): bool
    {
        self::ensureSchema();
        self::db()->query("DELETE FROM `pusat_notifikasi_reads` WHERE notifikasi_id = ?", [$id]);
        self::db()->query("DELETE FROM `pusat_notifikasi` WHERE id = ?", [$id]);
        return true;
    }

    /**
     * Daftar akun yang sudah membaca atau mengklik tautan notifikasi
     */
    public static function getReaders(int $notifikasiId): array
    {
        self::ensureSchema();
        return self::db()->findAll("
            SELECT r.*, u.full_name, u.email, u.username
            FROM `pusat_notifikasi_reads` r
            JOIN `users` u ON u.id = r.user_id
            WHERE r.notifikasi_id = ?
            ORDER BY COALESCE(r.clicked_at, r.read_at, r.created_at) DESC
        ", [$notifikasiId]);
    }

    /**
     * Resolve profil lengkap user (pegawai, jenis_pegawai, jenis_otorisasi, roles, units)
     */
    public static function resolveUserProfile(int $userId): array
    {
        $user = self::db()->find("
            SELECT id, pegawai_id, full_name, email, allowed_jenjang,
                   COALESCE(jenis_otorisasi, 'admin') AS jenis_otorisasi, is_active
            FROM `users`
            WHERE id = ?
            LIMIT 1
        ", [$userId]);

        if (!$user) {
            return [
                'user_id'         => $userId,
                'pegawai_id'      => null,
                'jenis_pegawai'   => '',
                'jenis_otorisasi' => 'admin',
                'role_ids'        => [],
                'role_slugs'      => [],
                'units'           => [],
                'is_active'       => false,
            ];
        }

        $roleRows = self::db()->findAll("
            SELECT r.id, r.slug, r.allowed_jenjang
            FROM `user_roles` ur
            JOIN `roles` r ON r.id = ur.role_id
            WHERE ur.user_id = ?
        ", [$userId]);

        $pegawai = null;
        if (!empty($user['pegawai_id'])) {
            $pegawai = self::db()->find("SELECT id, unit_tugas, jenis_pegawai FROM `pegawai` WHERE id = ? LIMIT 1", [(int)$user['pegawai_id']]);
        }
        if (!$pegawai && !empty($user['email'])) {
            $pegawai = self::db()->find("SELECT id, unit_tugas, jenis_pegawai FROM `pegawai` WHERE LOWER(email) = LOWER(?) LIMIT 1", [trim((string)$user['email'])]);
        }
        if (!$pegawai && !empty($user['full_name'])) {
            $pegawai = self::db()->find("SELECT id, unit_tugas, jenis_pegawai FROM `pegawai` WHERE LOWER(nama) = LOWER(?) LIMIT 1", [trim((string)$user['full_name'])]);
        }

        $roleIds = [];
        $roleSlugs = [];
        $units = [];

        foreach ($roleRows as $rr) {
            $roleIds[] = (int)$rr['id'];
            $roleSlugs[] = strtolower(trim((string)$rr['slug']));
            foreach (self::parseUnitsList($rr['allowed_jenjang'] ?? '') as $ru) {
                if (!in_array($ru, $units, true)) {
                    $units[] = $ru;
                }
            }
        }

        foreach (self::parseUnitsList($user['allowed_jenjang'] ?? '') as $uu) {
            if (!in_array($uu, $units, true)) {
                $units[] = $uu;
            }
        }

        $jpNormalized = '';
        if ($pegawai) {
            foreach (self::parseUnitsList($pegawai['unit_tugas'] ?? '') as $pu) {
                if (!in_array($pu, $units, true)) {
                    $units[] = $pu;
                }
            }
            $rawJp = strtolower(trim((string)($pegawai['jenis_pegawai'] ?? '')));
            if (strpos($rawJp, 'guru') !== false) {
                $jpNormalized = 'guru';
            } elseif (strpos($rawJp, 'support') !== false || strpos($rawJp, 'staff') !== false || strpos($rawJp, 'tendik') !== false) {
                $jpNormalized = 'support_system';
            }
        }

        return [
            'user_id'         => $userId,
            'pegawai_id'      => $pegawai ? (int)$pegawai['id'] : (!empty($user['pegawai_id']) ? (int)$user['pegawai_id'] : null),
            'jenis_pegawai'   => $jpNormalized,
            'jenis_otorisasi' => strtolower(trim((string)($user['jenis_otorisasi'] ?? 'admin'))) ?: 'admin',
            'role_ids'        => $roleIds,
            'role_slugs'      => $roleSlugs,
            'units'           => $units,
            'is_active'       => !empty($user['is_active']),
        ];
    }

    /**
     * Cek apakah suatu notifikasi aktif cocok dengan profil akun & platform penerima
     */
    public static function doesNotificationMatchUser(array $notif, array $profile, string $platform = 'web'): bool
    {
        if (($notif['status'] ?? '') !== 'aktif') {
            return false;
        }

        $nowTs = time();
        if (!empty($notif['mulai_at'])) {
            $mulaiTs = strtotime((string)$notif['mulai_at']);
            if ($mulaiTs !== false && $mulaiTs > $nowTs) {
                return false;
            }
        }
        if (!empty($notif['berakhir_at'])) {
            $endTs = strtotime((string)$notif['berakhir_at']);
            if ($endTs !== false && $endTs < $nowTs) {
                return false;
            }
        }

        $tPlatform = strtolower(trim((string)($notif['target_platform'] ?? 'both')));
        if ($tPlatform !== 'both' && $tPlatform !== $platform) {
            return false;
        }

        $jenisAkun = strtolower(trim((string)($notif['target_jenis_akun'] ?? 'all')));
        switch ($jenisAkun) {
            case 'all':
                break;
            case 'guru':
                if (($profile['jenis_pegawai'] ?? '') !== 'guru') {
                    return false;
                }
                break;
            case 'support_system':
                if (($profile['jenis_pegawai'] ?? '') !== 'support_system') {
                    return false;
                }
                break;
            case 'otorisasi_admin':
                if (($profile['jenis_otorisasi'] ?? 'admin') !== 'admin') {
                    return false;
                }
                break;
            case 'otorisasi_personal':
                if (($profile['jenis_otorisasi'] ?? 'admin') !== 'personal') {
                    return false;
                }
                break;
            case 'role':
                $targetRoles = array_values(array_filter(array_map('intval', explode(',', (string)($notif['target_role_ids'] ?? '')))));
                if (empty($targetRoles) || empty(array_intersect($targetRoles, $profile['role_ids'] ?? []))) {
                    return false;
                }
                break;
            default:
                break;
        }

        $targetUnitsRaw = strtoupper(trim((string)($notif['target_units'] ?? 'ALL')));
        if ($targetUnitsRaw !== '' && $targetUnitsRaw !== 'ALL') {
            $targetUnits = self::parseUnitsList($targetUnitsRaw);
            $userUnits = $profile['units'] ?? [];
            if (empty($targetUnits) || empty($userUnits) || empty(array_intersect($targetUnits, $userUnits))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kumpulkan semua akun aktif yang cocok dengan aturan target suatu notifikasi (batch queries tanpa N+1)
     */
    public static function getMatchedTargetUsers(array $notif): array
    {
        $users = self::db()->findAll("
            SELECT id, pegawai_id, full_name, email, allowed_jenjang,
                   COALESCE(jenis_otorisasi, 'admin') AS jenis_otorisasi
            FROM `users`
            WHERE is_active = 1
        ");

        if (empty($users)) {
            return ['user_ids' => [], 'pegawai_ids' => [], 'total_users' => 0];
        }

        $roleLinks = self::db()->findAll("
            SELECT ur.user_id, r.id AS role_id, r.slug, r.allowed_jenjang
            FROM `user_roles` ur
            JOIN `roles` r ON r.id = ur.role_id
        ");
        $rolesByUser = [];
        foreach ($roleLinks as $rl) {
            $rolesByUser[(int)$rl['user_id']][] = $rl;
        }

        $pegawaiRows = self::db()->findAll("SELECT id, email, nama, unit_tugas, jenis_pegawai FROM `pegawai`");
        $pegById = [];
        $pegByEmail = [];
        $pegByNama = [];
        foreach ($pegawaiRows as $pr) {
            $pegById[(int)$pr['id']] = $pr;
            if (!empty($pr['email'])) {
                $pegByEmail[strtolower(trim((string)$pr['email']))] = $pr;
            }
            if (!empty($pr['nama'])) {
                $pegByNama[strtolower(trim((string)$pr['nama']))] = $pr;
            }
        }

        // Untuk pengecekan match di sini, anggap platform sesuai target notifikasi (kecuali status sementara draft saat preview)
        $notifCheck = $notif;
        $notifCheck['status'] = 'aktif';
        $notifCheck['mulai_at'] = null;
        $checkPlatform = ($notif['target_platform'] ?? 'both') === 'mobile' ? 'mobile' : 'web';

        $matchedUserIds = [];
        $matchedPegawaiIds = [];

        foreach ($users as $u) {
            $uid = (int)$u['id'];
            $pegawai = null;
            if (!empty($u['pegawai_id']) && isset($pegById[(int)$u['pegawai_id']])) {
                $pegawai = $pegById[(int)$u['pegawai_id']];
            } elseif (!empty($u['email']) && isset($pegByEmail[strtolower(trim((string)$u['email']))])) {
                $pegawai = $pegByEmail[strtolower(trim((string)$u['email']))];
            } elseif (!empty($u['full_name']) && isset($pegByNama[strtolower(trim((string)$u['full_name']))])) {
                $pegawai = $pegByNama[strtolower(trim((string)$u['full_name']))];
            }

            $roleIds = [];
            $roleSlugs = [];
            $units = [];
            foreach (($rolesByUser[$uid] ?? []) as $rr) {
                $roleIds[] = (int)$rr['role_id'];
                $roleSlugs[] = strtolower(trim((string)$rr['slug']));
                foreach (self::parseUnitsList($rr['allowed_jenjang'] ?? '') as $ru) {
                    if (!in_array($ru, $units, true)) {
                        $units[] = $ru;
                    }
                }
            }
            foreach (self::parseUnitsList($u['allowed_jenjang'] ?? '') as $uu) {
                if (!in_array($uu, $units, true)) {
                    $units[] = $uu;
                }
            }

            $jpNormalized = '';
            $resolvedPid = !empty($u['pegawai_id']) ? (int)$u['pegawai_id'] : null;
            if ($pegawai) {
                $resolvedPid = (int)$pegawai['id'];
                foreach (self::parseUnitsList($pegawai['unit_tugas'] ?? '') as $pu) {
                    if (!in_array($pu, $units, true)) {
                        $units[] = $pu;
                    }
                }
                $rawJp = strtolower(trim((string)($pegawai['jenis_pegawai'] ?? '')));
                if (strpos($rawJp, 'guru') !== false) {
                    $jpNormalized = 'guru';
                } elseif (strpos($rawJp, 'support') !== false || strpos($rawJp, 'staff') !== false || strpos($rawJp, 'tendik') !== false) {
                    $jpNormalized = 'support_system';
                }
            }

            $profile = [
                'user_id'         => $uid,
                'pegawai_id'      => $resolvedPid,
                'jenis_pegawai'   => $jpNormalized,
                'jenis_otorisasi' => strtolower(trim((string)($u['jenis_otorisasi'] ?? 'admin'))) ?: 'admin',
                'role_ids'        => $roleIds,
                'role_slugs'      => $roleSlugs,
                'units'           => $units,
                'is_active'       => true,
            ];

            if (self::doesNotificationMatchUser($notifCheck, $profile, $checkPlatform)) {
                $matchedUserIds[] = $uid;
                if ($resolvedPid) {
                    $matchedPegawaiIds[] = $resolvedPid;
                }
            }
        }

        return [
            'user_ids'    => array_values(array_unique($matchedUserIds)),
            'pegawai_ids' => array_values(array_unique($matchedPegawaiIds)),
            'total_users' => count(array_unique($matchedUserIds)),
        ];
    }

    /**
     * Aktifkan & kirim notifikasi ke target akun + perangkat Web Push (dengan bunyi default)
     */
    public static function sendNotification(int $id): array
    {
        self::ensureSchema();
        $notif = self::findById($id);
        if (!$notif) {
            return ['success' => false, 'message' => 'Data notifikasi tidak ditemukan.'];
        }

        $now = date('Y-m-d H:i:s');
        $updInit = [
            'status'   => 'aktif',
            'mulai_at' => !empty($notif['mulai_at']) ? $notif['mulai_at'] : $now,
            'sent_at'  => $now,
        ];
        self::db()->update('pusat_notifikasi', $updInit, 'id = ?', [$id]);
        $notif = array_merge($notif, $updInit);

        $targets = self::getMatchedTargetUsers($notif);

        $defaultUrl = (($notif['target_platform'] ?? 'both') === 'web') ? '/dashboard' : '/mobile/notifikasi';
        $targetLink = trim((string)($notif['link_url'] ?? ''));
        if ($targetLink === '') {
            $targetLink = function_exists('url') ? url(ltrim($defaultUrl, '/')) : $defaultUrl;
        } elseif (!preg_match('#^https?://#i', $targetLink) && function_exists('url')) {
            $targetLink = url(ltrim($targetLink, '/'));
        }

        $iconUrl = function_exists('url') ? url('public/images/pwa/icon-192.png') : '/public/images/pwa/icon-192.png';
        $payload = [
            'title'       => (string)$notif['judul'],
            'body'        => (string)$notif['pesan'],
            'url'         => $targetLink,
            'tag'         => 'pusat-notif-' . $id,
            'notif_id'    => $id,
            'tipe'        => (string)$notif['tipe'],
            'pakai_suara' => !empty($notif['pakai_suara']),
            'icon'        => $iconUrl,
            'badge'       => $iconUrl,
        ];

        $pushSent = 0;
        $pushFailed = 0;
        $totalDevices = 0;
        if (!empty($targets['user_ids']) || !empty($targets['pegawai_ids'])) {
            require_once BASE_PATH . '/core/WebPushService.php';
            $pushRes = WebPushService::sendBroadcastToTargets(
                $targets['user_ids'],
                $targets['pegawai_ids'],
                $payload,
                [
                    'topic'      => 'pusat_notifikasi_' . ($notif['tipe'] ?? 'push'),
                    'tanggal'    => date('Y-m-d'),
                    'slot_waktu' => date('H:i'),
                    'created_by' => $notif['created_by'] ?? null,
                ]
            );
            $pushSent = (int)($pushRes['sent'] ?? 0);
            $pushFailed = (int)($pushRes['failed'] ?? 0);
            $totalDevices = (int)($pushRes['total_devices'] ?? 0);
        }

        self::db()->update('pusat_notifikasi', [
            'sent_count'       => (int)$targets['total_users'],
            'push_device_sent' => $pushSent,
            'sent_at'          => $now,
        ], 'id = ?', [$id]);

        return [
            'success'       => true,
            'total_users'   => (int)$targets['total_users'],
            'push_sent'     => $pushSent,
            'push_failed'   => $pushFailed,
            'total_devices' => $totalDevices,
            'message'       => "Notifikasi berhasil dikirim ke {$targets['total_users']} akun target"
                . ($pushSent > 0 ? " (Web Push real-time terkirim ke {$pushSent} perangkat)." : ' (tampil instan di aplikasi saat dibuka/aktif).'),
        ];
    }

    /**
     * Ambil feed notifikasi aktif yang cocok untuk user & platform tertentu ('web' atau 'mobile')
     */
    public static function getFeedForUser(int $userId, string $platform = 'web'): array
    {
        self::ensureSchema();
        $platform = ($platform === 'mobile') ? 'mobile' : 'web';
        $profile = self::resolveUserProfile($userId);

        if (empty($profile['is_active'])) {
            return [
                'push_items'   => [],
                'popup_items'  => [],
                'inbox_items'  => [],
                'unread_count' => 0,
            ];
        }

        $rows = self::db()->findAll("
            SELECT n.*,
                   COALESCE(r.is_read, 0) AS is_read,
                   r.read_at,
                   r.clicked_at
            FROM `pusat_notifikasi` n
            LEFT JOIN `pusat_notifikasi_reads` r
                   ON r.notifikasi_id = n.id AND r.user_id = ?
            WHERE n.status = 'aktif'
              AND (n.mulai_at IS NULL OR n.mulai_at <= NOW())
              AND (n.berakhir_at IS NULL OR n.berakhir_at >= NOW())
              AND n.target_platform IN ('both', ?)
            ORDER BY COALESCE(n.sent_at, n.created_at) DESC, n.id DESC
            LIMIT 80
        ", [$userId, $platform]);

        $pushItems = [];
        $popupItems = [];
        $inboxItems = [];
        $unreadCount = 0;

        foreach ($rows as $row) {
            if (!self::doesNotificationMatchUser($row, $profile, $platform)) {
                continue;
            }

            $isRead = !empty($row['is_read']);
            if (!$isRead) {
                $unreadCount++;
            }

            $rawLink = trim((string)($row['link_url'] ?? ''));
            $resolvedLink = '';
            if ($rawLink !== '') {
                if (preg_match('#^https?://#i', $rawLink)) {
                    $resolvedLink = $rawLink;
                } elseif (strpos($rawLink, '/') === 0) {
                    $resolvedLink = function_exists('url') ? url(ltrim($rawLink, '/')) : $rawLink;
                }
            }

            $item = [
                'id'                   => (int)$row['id'],
                'judul'                => (string)$row['judul'],
                'pesan'                => (string)$row['pesan'],
                'link_url'             => $resolvedLink,
                'link_label'           => trim((string)($row['link_label'] ?? '')) ?: 'Buka Tautan',
                'tipe'                 => (string)$row['tipe'],
                'prioritas'            => (string)($row['prioritas'] ?? 'info'),
                'popup_interval_menit' => max(1, (int)($row['popup_interval_menit'] ?? 5)),
                'pakai_suara'          => !empty($row['pakai_suara']),
                'target_units'         => (string)($row['target_units'] ?? 'ALL'),
                'created_by_name'      => (string)($row['created_by_name'] ?? 'Admin'),
                'sent_at'              => $row['sent_at'] ?? $row['created_at'],
                'time_ago'             => self::formatTimeAgo($row['sent_at'] ?? $row['created_at']),
                'is_read'              => $isRead,
            ];

            if (count($inboxItems) < 30) {
                $inboxItems[] = $item;
            }
            if ($item['tipe'] === 'push' && count($pushItems) < 20) {
                $pushItems[] = $item;
            }
            if ($item['tipe'] === 'popup' && count($popupItems) < 10) {
                $popupItems[] = $item;
            }
        }

        return [
            'push_items'   => $pushItems,
            'popup_items'  => $popupItems,
            'inbox_items'  => $inboxItems,
            'unread_count' => $unreadCount,
        ];
    }

    /**
     * Tandai notifikasi sebagai sudah dibaca (atau semua notifikasi aktif pada feed user)
     */
    public static function markRead(int $userId, ?int $notifikasiId, string $platform = 'web', bool $isClick = false): bool
    {
        self::ensureSchema();
        $platform = ($platform === 'mobile') ? 'mobile' : 'web';

        if ($notifikasiId !== null && $notifikasiId > 0) {
            self::db()->query("
                INSERT INTO `pusat_notifikasi_reads` (notifikasi_id, user_id, platform, is_read, read_at, clicked_at)
                VALUES (?, ?, ?, 1, NOW(), ?)
                ON DUPLICATE KEY UPDATE
                    platform = VALUES(platform),
                    is_read = 1,
                    read_at = COALESCE(read_at, NOW()),
                    clicked_at = IF(? = 1, NOW(), clicked_at)
            ", [
                $notifikasiId,
                $userId,
                $platform,
                $isClick ? date('Y-m-d H:i:s') : null,
                $isClick ? 1 : 0,
            ]);
            return true;
        }

        $feed = self::getFeedForUser($userId, $platform);
        $stmt = self::db()->getConnection()->prepare("
            INSERT INTO `pusat_notifikasi_reads` (notifikasi_id, user_id, platform, is_read, read_at)
            VALUES (?, ?, ?, 1, NOW())
            ON DUPLICATE KEY UPDATE
                platform = VALUES(platform),
                is_read = 1,
                read_at = COALESCE(read_at, NOW())
        ");
        foreach ($feed['inbox_items'] as $item) {
            if (empty($item['is_read'])) {
                $stmt->execute([(int)$item['id'], $userId, $platform]);
            }
        }
        return true;
    }

    public static function formatTimeAgo(?string $datetime): string
    {
        if (empty($datetime)) {
            return 'Baru saja';
        }
        $ts = strtotime($datetime);
        if ($ts === false) {
            return $datetime;
        }
        $diff = time() - $ts;
        if ($diff < 60) {
            return 'Baru saja';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . ' menit lalu';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . ' jam lalu';
        }
        if ($diff < 604800) {
            return floor($diff / 86400) . ' hari lalu';
        }
        return date('d/m/Y H:i', $ts);
    }
}
