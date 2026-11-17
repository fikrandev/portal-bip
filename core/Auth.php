<?php
/**
 * Portal BIP - Authentication Helper
 * 
 * Manages user login, logout, session state, and login throttling.
 */

class Auth
{
    private static ?array $currentUser = null;
    private static ?string $cachedAvatar = null;
    private static bool $avatarResolved = false;
    private static ?string $cachedJabatan = null;
    private static bool $jabatanResolved = false;
    private static ?array $cachedPegawai = null;
    private static bool $pegawaiResolved = false;

    /**
     * Attempt to log in a user
     * 
     * @param string $username
     * @param string $password
     * @return array ['success' => bool, 'message' => string]
     */
    public static function login(string $username, string $password): array
    {
        $db = Database::getInstance();

        // Check login attempts (throttling)
        if (self::isLockedOut($username)) {
            return [
                'success' => false,
                'message' => 'Akun terkunci sementara. Coba lagi dalam ' . LOCKOUT_DURATION . ' menit.'
            ];
        }

        // Find user by username or email
        $user = $db->find(
            "SELECT u.*, GROUP_CONCAT(r.slug) as role_slugs,
                    GROUP_CONCAT(r.allowed_jenjang) as role_allowed_jenjangs 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1 
             GROUP BY u.id",
            [$username, $username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            self::recordFailedAttempt($username);
            return [
                'success' => false,
                'message' => 'Username atau password salah.'
            ];
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store user in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_uuid'] = $user['uuid'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_roles'] = $user['role_slugs'] ? explode(',', $user['role_slugs']) : [];
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';

        // Resolve and store effective allowed jenjang
        self::resolveAndStoreJenjang($user);

        // Reset static cache & avatar/jabatan cache
        self::$currentUser = null;
        self::$cachedAvatar = null;
        self::$avatarResolved = false;
        self::$cachedJabatan = null;
        self::$jabatanResolved = false;
        self::$cachedPegawai = null;
        self::$pegawaiResolved = false;
        unset($_SESSION['user_avatar_resolved'], $_SESSION['user_avatar_resolved_ts'], $_SESSION['user_avatar_resolved_uid']);
        unset($_SESSION['user_jabatan_resolved'], $_SESSION['user_jabatan_resolved_ts'], $_SESSION['user_jabatan_resolved_uid']);

        // Update last login
        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        // Clear failed attempts
        self::clearFailedAttempts($username);

        // Log the login
        self::logAudit('LOGIN', 'user', $user['id']);

        return [
            'success' => true,
            'message' => 'Login berhasil.'
        ];
    }

    /**
     * Log in a user directly by user ID (used for persistent auto-login / remember tokens)
     *
     * @param int $userId
     * @return bool
     */
    public static function loginUsingId(int $userId): bool
    {
        $db = Database::getInstance();
        $user = $db->find(
            "SELECT u.*, GROUP_CONCAT(r.slug) as role_slugs,
                    GROUP_CONCAT(r.allowed_jenjang) as role_allowed_jenjangs 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE u.id = ? AND u.is_active = 1 
             GROUP BY u.id",
            [$userId]
        );

        if (!$user) {
            return false;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerate session ID
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_uuid'] = $user['uuid'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_roles'] = $user['role_slugs'] ? explode(',', $user['role_slugs']) : [];
        $_SESSION['login_time'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';

        // Resolve and store effective allowed jenjang
        self::resolveAndStoreJenjang($user);

        // Reset static cache & avatar/jabatan cache
        self::$currentUser = null;
        self::$cachedAvatar = null;
        self::$avatarResolved = false;
        self::$cachedJabatan = null;
        self::$jabatanResolved = false;
        self::$cachedPegawai = null;
        self::$pegawaiResolved = false;
        unset($_SESSION['user_avatar_resolved'], $_SESSION['user_avatar_resolved_ts'], $_SESSION['user_avatar_resolved_uid']);
        unset($_SESSION['user_jabatan_resolved'], $_SESSION['user_jabatan_resolved_ts'], $_SESSION['user_jabatan_resolved_uid']);

        // Update last login
        $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        // Audit log
        self::logAudit('LOGIN_PERSISTENT', 'user', $user['id']);

        return true;
    }

    /**
     * Log out the current user
     */
    public static function logout(): void
    {
        if (self::check()) {
            self::logAudit('LOGOUT', 'user', $_SESSION['user_id'] ?? null);
        }

        $_SESSION = [];
        self::$currentUser = null;

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current authenticated user data
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$currentUser === null) {
            $db = Database::getInstance();
            self::$currentUser = $db->find(
                "SELECT id, uuid, full_name, email, username, avatar, phone, is_active, last_login 
                 FROM users WHERE id = ?",
                [$_SESSION['user_id']]
            );
        }

        return self::$currentUser;
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user's full name
     */
    public static function name(): string
    {
        return $_SESSION['user_name'] ?? 'Guest';
    }

    /**
     * Get current user's roles
     */
    public static function roles(): array
    {
        return $_SESSION['user_roles'] ?? [];
    }

    /**
     * Check if user has a specific role
     */
    public static function hasRole(string $roleSlug): bool
    {
        $roles = self::roles();
        return in_array($roleSlug, $roles, true) || in_array('super_admin', $roles, true);
    }

    /**
     * Check if current user is super admin
     */
    public static function isSuperAdmin(): bool
    {
        return in_array('super_admin', self::roles(), true);
    }

    /**
     * Get user initials for avatar fallback
     */
    public static function initials(): string
    {
        $name = self::name();
        $parts = explode(' ', $name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
        return $initials;
    }

    /**
     * Get user avatar URL, falling back to linked pegawai foto or null
     */
    public static function avatar(): ?string
    {
        if (self::$avatarResolved) {
            return self::$cachedAvatar;
        }

        $user = self::user();
        if (!$user) {
            self::$avatarResolved = true;
            self::$cachedAvatar = null;
            return null;
        }

        $uid = (int)($user['id'] ?? 0);
        if (
            isset($_SESSION['user_avatar_resolved'], $_SESSION['user_avatar_resolved_ts'], $_SESSION['user_avatar_resolved_uid']) &&
            (int)$_SESSION['user_avatar_resolved_uid'] === $uid &&
            (time() - (int)$_SESSION['user_avatar_resolved_ts']) < 300
        ) {
            self::$cachedAvatar = $_SESSION['user_avatar_resolved'] !== '' ? $_SESSION['user_avatar_resolved'] : null;
            self::$avatarResolved = true;
            return self::$cachedAvatar;
        }

        $resolved = null;

        // 1. Cek avatar di tabel users
        if (!empty($user['avatar'])) {
            $resolved = self::resolvePhotoUrl($user['avatar']);
        }

        // 2. Cek foto di tabel pegawai yang terhubung (by email, nama, phone, atau username)
        if ($resolved === null) {
            $db = Database::getInstance();
            $pegawai = null;
            if (!empty($user['email'])) {
                $pegawai = $db->find("SELECT foto FROM pegawai WHERE email = ? AND foto IS NOT NULL AND foto != '' LIMIT 1", [$user['email']]);
            }
            if (!$pegawai && !empty($user['full_name'])) {
                $pegawai = $db->find("SELECT foto FROM pegawai WHERE (nama = ? OR nama LIKE ?) AND foto IS NOT NULL AND foto != '' LIMIT 1", [$user['full_name'], '%' . $user['full_name'] . '%']);
            }
            if (!$pegawai && !empty($user['phone'])) {
                $pegawai = $db->find("SELECT foto FROM pegawai WHERE no_wa = ? AND foto IS NOT NULL AND foto != '' LIMIT 1", [$user['phone']]);
            }
            if (!$pegawai && !empty($user['username'])) {
                $cleanName = str_replace(['.', '_', '-'], ' ', $user['username']);
                $pegawai = $db->find("SELECT foto FROM pegawai WHERE nama LIKE ? AND foto IS NOT NULL AND foto != '' LIMIT 1", ['%' . $cleanName . '%']);
            }

            if ($pegawai && !empty($pegawai['foto'])) {
                $resolved = self::resolvePhotoUrl($pegawai['foto']);
            }
        }

        self::$cachedAvatar = $resolved;
        self::$avatarResolved = true;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['user_avatar_resolved'] = $resolved ?? '';
            $_SESSION['user_avatar_resolved_ts'] = time();
            $_SESSION['user_avatar_resolved_uid'] = $uid;
        }

        return $resolved;
    }

    /**
     * Find linked pegawai row for the current authenticated user
     */
    public static function findLinkedPegawai(?array $user = null): ?array
    {
        $user = $user ?? self::user();
        if (!$user) {
            return null;
        }
        if (self::$pegawaiResolved && self::$cachedPegawai !== null) {
            return self::$cachedPegawai;
        }

        $db = Database::getInstance();
        $pegawai = null;
        if (!empty($user['email'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE email = ? ORDER BY is_active DESC, id ASC LIMIT 1", [$user['email']]);
        }
        if (!$pegawai && !empty($user['full_name'])) {
            $pegawai = $db->find(
                "SELECT * FROM pegawai WHERE (nama = ? OR nama LIKE ?) ORDER BY is_active DESC, id ASC LIMIT 1",
                [$user['full_name'], '%' . $user['full_name'] . '%']
            );
        }
        if (!$pegawai && !empty($user['phone'])) {
            $pegawai = $db->find("SELECT * FROM pegawai WHERE no_wa = ? ORDER BY is_active DESC, id ASC LIMIT 1", [$user['phone']]);
        }
        if (!$pegawai && !empty($user['username'])) {
            $cleanName = str_replace(['.', '_', '-'], ' ', $user['username']);
            if (strlen(trim($cleanName)) >= 3) {
                $pegawai = $db->find(
                    "SELECT * FROM pegawai WHERE nama LIKE ? ORDER BY is_active DESC, id ASC LIMIT 1",
                    ['%' . trim($cleanName) . '%']
                );
            }
        }

        self::$cachedPegawai = $pegawai ?: null;
        self::$pegawaiResolved = true;
        return self::$cachedPegawai;
    }

    /**
     * Get active jabatan from penugasan pegawai (fallback: pegawai.jabatan -> role name -> user name)
     */
    public static function jabatan(): string
    {
        if (self::$jabatanResolved && self::$cachedJabatan !== null) {
            return self::$cachedJabatan;
        }

        $user = self::user();
        if (!$user) {
            self::$jabatanResolved = true;
            self::$cachedJabatan = 'Pengguna';
            return 'Pengguna';
        }

        $uid = (int)($user['id'] ?? 0);
        if (
            isset($_SESSION['user_jabatan_resolved'], $_SESSION['user_jabatan_resolved_ts'], $_SESSION['user_jabatan_resolved_uid']) &&
            (int)$_SESSION['user_jabatan_resolved_uid'] === $uid &&
            (time() - (int)$_SESSION['user_jabatan_resolved_ts']) < 300 &&
            $_SESSION['user_jabatan_resolved'] !== ''
        ) {
            self::$cachedJabatan = (string)$_SESSION['user_jabatan_resolved'];
            self::$jabatanResolved = true;
            return self::$cachedJabatan;
        }

        $resolved = '';
        try {
            $db = Database::getInstance();
            $pegawai = self::findLinkedPegawai($user);
            if ($pegawai && !empty($pegawai['id'])) {
                $assign = $db->find(
                    "SELECT mj.nama AS jabatan_nama, mu.nama AS unit_nama
                     FROM pegawai_penugasan pp
                     JOIN penugasan_grup pg ON pp.grup_id = pg.id
                     LEFT JOIN master_jabatan mj ON mj.id = pp.jabatan_id
                     LEFT JOIN master_unit_tugas mu ON mu.id = pp.unit_tugas_id
                     WHERE pp.pegawai_id = ?
                     ORDER BY pg.is_active DESC, (pp.status = 'Aktif') DESC, pp.id DESC
                     LIMIT 1",
                    [(int)$pegawai['id']]
                );
                if ($assign && !empty($assign['jabatan_nama'])) {
                    $jab = trim((string)$assign['jabatan_nama']);
                    $unit = trim((string)($assign['unit_nama'] ?? ''));
                    if (
                        $unit !== '' &&
                        in_array(strtoupper($unit), ['PAUD', 'TK', 'SD', 'SMP', 'SMA'], true) &&
                        stripos($jab, $unit) === false
                    ) {
                        $resolved = $jab . ' ' . $unit;
                    } else {
                        $resolved = $jab;
                    }
                } elseif (!empty($pegawai['jabatan'])) {
                    $resolved = trim((string)$pegawai['jabatan']);
                }
            }

            if ($resolved === '') {
                $roleRow = $db->find(
                    "SELECT GROUP_CONCAT(r.name ORDER BY r.id ASC SEPARATOR ', ') AS role_names
                     FROM user_roles ur
                     JOIN roles r ON r.id = ur.role_id
                     WHERE ur.user_id = ?",
                    [$uid]
                );
                if ($roleRow && !empty($roleRow['role_names'])) {
                    $resolved = trim((string)$roleRow['role_names']);
                }
            }
        } catch (Throwable $e) {
            // Silent fallback
        }

        if ($resolved === '') {
            $resolved = self::name();
        }

        self::$cachedJabatan = $resolved;
        self::$jabatanResolved = true;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['user_jabatan_resolved'] = $resolved;
            $_SESSION['user_jabatan_resolved_ts'] = time();
            $_SESSION['user_jabatan_resolved_uid'] = $uid;
        }

        return $resolved;
    }

    /**
     * Helper resolve URL foto relatif ke web path yang valid
     */
    public static function resolvePhotoUrl(?string $path): ?string
    {
        if (empty($path)) return null;
        $clean = trim($path);
        if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://')) {
            return $clean;
        }
        $trimmed = ltrim($clean, '/');
        if (str_starts_with($trimmed, 'public/')) {
            return url($trimmed);
        }
        return url('public/' . $trimmed);
    }

    /**
     * Ensure login_attempts table exists in database
     */
    private static function ensureAttemptsTable(): void
    {
        static $checked = false;
        if ($checked) return;

        $stampFile = BASE_PATH . '/storage/logs/.login_attempts_table.stamp';
        if (file_exists($stampFile)) {
            $checked = true;
            return;
        }

        try {
            $db = Database::getInstance();
            $db->query("CREATE TABLE IF NOT EXISTS `login_attempts` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `ip_address` VARCHAR(45) NOT NULL,
                `username` VARCHAR(100) NOT NULL,
                `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_la_ip_time` (`ip_address`, `attempted_at`),
                INDEX `idx_la_user_time` (`username`, `attempted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $checked = true;
            @file_put_contents($stampFile, (string)time());
        } catch (Throwable $e) {
            // Proceed gracefully if DB table creation is not permitted
        }
    }

    /**
     * Record a failed login attempt
     */
    private static function recordFailedAttempt(string $username): void
    {
        self::ensureAttemptsTable();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        try {
            $db = Database::getInstance();
            $db->insert('login_attempts', [
                'ip_address'   => $ip,
                'username'     => $username,
                'attempted_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Throwable $e) {
            // Fallback to session if database write fails
            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = [];
            }
            $key = md5($username . $ip);
            if (!isset($_SESSION['login_attempts'][$key])) {
                $_SESSION['login_attempts'][$key] = [
                    'count' => 0,
                    'first_attempt' => time()
                ];
            }
            $_SESSION['login_attempts'][$key]['count']++;
            $_SESSION['login_attempts'][$key]['last_attempt'] = time();
        }
    }

    /**
     * Check if login is locked out
     */
    private static function isLockedOut(string $username): bool
    {
        self::ensureAttemptsTable();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $lockoutSince = date('Y-m-d H:i:s', time() - (LOCKOUT_DURATION * 60));

        try {
            $db = Database::getInstance();
            $row = $db->find(
                "SELECT COUNT(*) as attempts FROM `login_attempts` 
                 WHERE (ip_address = ? OR username = ?) AND attempted_at >= ?",
                [$ip, $username, $lockoutSince]
            );
            $attempts = (int)($row['attempts'] ?? 0);
            if ($attempts >= MAX_LOGIN_ATTEMPTS) {
                return true;
            }
        } catch (Throwable $e) {
            // Fallback to session check if database check fails
            $key = md5($username . $ip);
            if (isset($_SESSION['login_attempts'][$key])) {
                $sessionAttempts = $_SESSION['login_attempts'][$key];
                if ($sessionAttempts['count'] >= MAX_LOGIN_ATTEMPTS) {
                    $lockoutEnd = $sessionAttempts['last_attempt'] + (LOCKOUT_DURATION * 60);
                    if (time() < $lockoutEnd) {
                        return true;
                    }
                    unset($_SESSION['login_attempts'][$key]);
                }
            }
        }

        return false;
    }

    /**
     * Clear failed login attempts
     */
    private static function clearFailedAttempts(string $username): void
    {
        self::ensureAttemptsTable();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        try {
            $db = Database::getInstance();
            $db->delete('login_attempts', 'ip_address = ? OR username = ?', [$ip, $username]);
            
            // Periodically clean up records older than 1 day to keep table lean
            $oldThreshold = date('Y-m-d H:i:s', time() - 86400);
            $db->delete('login_attempts', 'attempted_at < ?', [$oldThreshold]);
        } catch (Throwable $e) {
            // Fallback
        }

        $key = md5($username . $ip);
        unset($_SESSION['login_attempts'][$key]);
    }

    /**
     * Log an audit entry
     * 
     * @param string $action
     * @param string $entityType
     * @param int|null $entityId
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public static function logAudit(
        string $action, 
        string $entityType = 'system', 
        ?int $entityId = null,
        $oldValue = null,
        $newValue = null
    ): void {
        try {
            $db = Database::getInstance();
            $db->insert('audit_logs', [
                'user_id'    => self::id(),
                'action'     => strtoupper(trim($action)),
                'entity_type'=> trim($entityType),
                'entity_id'  => $entityId,
                'old_value'  => is_array($oldValue) ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : $oldValue,
                'new_value'  => is_array($newValue) ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : $newValue,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            error_log('Audit log error: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RBAC JENJANG FILTERING
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolves and stores effective allowed jenjang into session
     */
    public static function resolveAndStoreJenjang(array $user): void
    {
        $roleSlugs = !empty($user['role_slugs']) ? explode(',', $user['role_slugs']) : [];
        $isSuperAdmin = in_array('super_admin', $roleSlugs, true);

        // Super admin has full access to all jenjang
        if ($isSuperAdmin) {
            $_SESSION['user_allowed_jenjang'] = ['PAUD', 'SD', 'SMP', 'SMA'];
            $_SESSION['user_is_jenjang_restricted'] = false;
            if (empty($_SESSION['user_active_jenjang'])) {
                $_SESSION['user_active_jenjang'] = 'PAUD';
            }
            return;
        }

        $allValid = ['PAUD', 'SD', 'SMP', 'SMA'];
        $allowed = [];

        // 1. Check user-level allowed_jenjang
        if (!empty($user['allowed_jenjang'])) {
            $raw = explode(',', strtoupper($user['allowed_jenjang']));
            foreach ($raw as $j) {
                $j = trim($j);
                if (in_array($j, $allValid, true)) {
                    $allowed[] = $j;
                }
            }
        }

        // 2. If user-level is empty, check role-level allowed_jenjang
        if (empty($allowed) && !empty($user['role_allowed_jenjangs'])) {
            $raw = explode(',', strtoupper($user['role_allowed_jenjangs']));
            foreach ($raw as $j) {
                $j = trim($j);
                if (in_array($j, $allValid, true)) {
                    $allowed[] = $j;
                }
            }
        }

        $allowed = array_values(array_unique($allowed));

        // If specific jenjang is assigned, user is restricted!
        if (!empty($allowed) && count($allowed) < count($allValid)) {
            $_SESSION['user_allowed_jenjang'] = $allowed;
            $_SESSION['user_is_jenjang_restricted'] = true;
            if (empty($_SESSION['user_active_jenjang']) || !in_array($_SESSION['user_active_jenjang'], $allowed, true)) {
                $_SESSION['user_active_jenjang'] = $allowed[0];
            }
        } else {
            // Unrestricted (all jenjang)
            $_SESSION['user_allowed_jenjang'] = !empty($allowed) ? $allowed : $allValid;
            $_SESSION['user_is_jenjang_restricted'] = false;
            if (empty($_SESSION['user_active_jenjang'])) {
                $_SESSION['user_active_jenjang'] = 'PAUD';
            }
        }
    }

    /**
     * Get user's allowed jenjang list e.g. ['PAUD'], ['SD'], ['PAUD', 'SD'], or ['PAUD', 'SD', 'SMP', 'SMA']
     */
    public static function getAllowedJenjang(): array
    {
        if (!isset($_SESSION['user_allowed_jenjang']) && self::check()) {
            $db = Database::getInstance();
            $user = $db->find(
                "SELECT u.*, GROUP_CONCAT(r.slug) as role_slugs,
                        GROUP_CONCAT(r.allowed_jenjang) as role_allowed_jenjangs
                 FROM users u 
                 LEFT JOIN user_roles ur ON u.id = ur.user_id 
                 LEFT JOIN roles r ON ur.role_id = r.id 
                 WHERE u.id = ? AND u.is_active = 1 
                 GROUP BY u.id",
                [self::id()]
            );
            if ($user) {
                self::resolveAndStoreJenjang($user);
            }
        }
        return $_SESSION['user_allowed_jenjang'] ?? ['PAUD', 'SD', 'SMP', 'SMA'];
    }

    /**
     * Check if user is restricted to specific jenjang (not super admin and not all)
     */
    public static function isJenjangRestricted(): bool
    {
        if (self::isSuperAdmin()) {
            return false;
        }
        if (!isset($_SESSION['user_is_jenjang_restricted']) && self::check()) {
            self::getAllowedJenjang(); // triggers resolve
        }
        return (bool)($_SESSION['user_is_jenjang_restricted'] ?? false);
    }

    /**
     * Get current active jenjang (e.g. 'PAUD', 'SD', 'SMP', 'SMA')
     */
    public static function getActiveJenjang(): string
    {
        if (!isset($_SESSION['user_active_jenjang']) && self::check()) {
            self::getAllowedJenjang();
        }
        return $_SESSION['user_active_jenjang'] ?? (self::getAllowedJenjang()[0] ?? 'PAUD');
    }

    /**
     * Set active jenjang if permitted
     */
    public static function setActiveJenjang(string $jenjang): bool
    {
        $jenjang = strtoupper(trim($jenjang));
        $allowed = self::getAllowedJenjang();
        if (self::isSuperAdmin() || in_array($jenjang, $allowed, true)) {
            $_SESSION['user_active_jenjang'] = $jenjang;
            return true;
        }
        return false;
    }

    /**
     * Check if user can access a specific jenjang
     */
    public static function canAccessJenjang(string $jenjang): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        if (!self::isJenjangRestricted()) {
            return true;
        }
        $norm = strtoupper(trim($jenjang));
        if ($norm === 'TK' || str_contains($norm, 'PAUD')) {
            $norm = 'PAUD';
        } elseif (preg_match('/^(SD|SMP|SMA)\s*IT$/', $norm, $m)) {
            $norm = $m[1];
        }
        return in_array($norm, self::getAllowedJenjang(), true);
    }

    /**
     * Expanded allowed jenjang list (includes 'TK' alias when 'PAUD' is allowed)
     */
    public static function expandedAllowedJenjang(): array
    {
        $allowed = array_map('strtoupper', self::getAllowedJenjang());
        if (in_array('PAUD', $allowed, true) && !in_array('TK', $allowed, true)) {
            $allowed[] = 'TK';
        }
        return array_values(array_unique($allowed));
    }

    /**
     * Build a SQL condition filtering a jenjang column by the current user's allowed jenjang
     */
    public static function jenjangSqlCondition(string $column, array &$params): string
    {
        if (!self::isJenjangRestricted()) {
            return '1=1';
        }
        $allowed = self::expandedAllowedJenjang();
        if (empty($allowed)) {
            return '1=0';
        }
        $placeholders = implode(',', array_fill(0, count($allowed), '?'));
        foreach ($allowed as $aj) {
            $params[] = $aj;
        }
        return "UPPER(TRIM({$column})) IN ({$placeholders})";
    }

    /**
     * Append RBAC jenjang filter + optional user-selected jenjang onto a WHERE clause
     */
    public static function applyJenjangFilter(string &$where, array &$params, string $column = 'jenjang', string $requestedJenjang = ''): void
    {
        $req = strtoupper(trim($requestedJenjang));
        if (self::isJenjangRestricted()) {
            if ($req !== '' && self::canAccessJenjang($req)) {
                if ($req === 'PAUD') {
                    $where .= " AND UPPER(TRIM({$column})) IN ('PAUD', 'TK')";
                } else {
                    $where .= " AND UPPER(TRIM({$column})) = ?";
                    $params[] = $req;
                }
            } else {
                $cond = self::jenjangSqlCondition($column, $params);
                $where .= " AND ({$cond})";
            }
        } elseif ($req !== '') {
            if ($req === 'PAUD') {
                $where .= " AND UPPER(TRIM({$column})) IN ('PAUD', 'TK')";
            } else {
                $where .= " AND UPPER(TRIM({$column})) = ?";
                $params[] = $req;
            }
        }
    }

    /**
     * Build a SQL condition filtering a unit / unit_tugas column (PAUD/SD/SMP/SMA + optional IT/Yayasan variants)
     */
    public static function unitSqlCondition(string $column, array &$params, bool $allowGlobalValues = false): string
    {
        if (!self::isJenjangRestricted()) {
            return '1=1';
        }
        $allowed = self::getAllowedJenjang();
        if (empty($allowed)) {
            return '1=0';
        }

        $variants = [];
        foreach ($allowed as $aj) {
            $u = strtoupper(trim($aj));
            if ($u === 'PAUD') {
                array_push($variants, 'PAUD', 'TK', 'PAUD IT', 'TK IT', 'PAUD / TK', 'PAUD/TK');
            } else {
                array_push($variants, $u, $u . ' IT', $u . 'IT');
            }
        }
        if ($allowGlobalValues) {
            array_push($variants, 'SEMUA UNIT', 'SEMUA', 'UMUM', 'YAYASAN');
        }
        $variants = array_values(array_unique($variants));
        $placeholders = implode(',', array_fill(0, count($variants), '?'));
        foreach ($variants as $v) {
            $params[] = $v;
        }
        $expr = "UPPER(TRIM({$column})) IN ({$placeholders})";
        if ($allowGlobalValues) {
            return "({$expr} OR {$column} IS NULL OR TRIM({$column}) = '')";
        }
        return $expr;
    }

    /**
     * Infer jenjang (PAUD/SD/SMP/SMA) from a class name string
     */
    public static function inferJenjangFromClassName(string $kelasName): string
    {
        $clean = strtoupper(trim($kelasName));
        if ($clean === '') {
            return '';
        }
        if (preg_match('/^(TK|KB|PAUD|PG|TPA|KELOMPOK)/i', $clean)) {
            return 'PAUD';
        }
        if (preg_match('/^(?:KELAS\s*)?(10|11|12|X|XI|XII)(?:\b|[^0-9])/i', $clean)) {
            return 'SMA';
        }
        if (preg_match('/^(?:KELAS\s*)?(7|8|9|VII|VIII|IX)(?:\b|[^0-9])/i', $clean)) {
            return 'SMP';
        }
        if (preg_match('/^(?:KELAS\s*)?(1|2|3|4|5|6|I|II|III|IV|V|VI)(?:\b|[^0-9])/i', $clean)) {
            return 'SD';
        }
        return '';
    }

    /**
     * Build a SQL condition filtering a class-name column by the user's allowed jenjang
     */
    public static function kelasSqlCondition(string $kelasCol, array &$params): string
    {
        if (!self::isJenjangRestricted()) {
            return '1=1';
        }
        $allowed = self::getAllowedJenjang();
        if (empty($allowed)) {
            return '1=0';
        }

        $orParts = [];
        // 1. Subquery match against siswa.jenjang when students exist in that class
        $expanded = self::expandedAllowedJenjang();
        $inPh = implode(',', array_fill(0, count($expanded), '?'));
        foreach ($expanded as $aj) {
            $params[] = $aj;
        }
        $orParts[] = "EXISTS (SELECT 1 FROM siswa _sj WHERE TRIM(_sj.kelas) = TRIM({$kelasCol}) AND UPPER(TRIM(_sj.jenjang)) IN ({$inPh}))";

        // 2. Pattern fallback for classes without students yet
        foreach ($allowed as $aj) {
            $u = strtoupper(trim($aj));
            if ($u === 'PAUD') {
                $orParts[] = "UPPER(TRIM({$kelasCol})) REGEXP '^(TK|KB|PAUD|PG|TPA|KELOMPOK)'";
            } elseif ($u === 'SD') {
                $orParts[] = "UPPER(TRIM({$kelasCol})) REGEXP '^(KELAS[[:space:]]*)?[1-6]([^0-9]|$)'";
            } elseif ($u === 'SMP') {
                $orParts[] = "UPPER(TRIM({$kelasCol})) REGEXP '^(KELAS[[:space:]]*)?[7-9]([^0-9]|$)'";
            } elseif ($u === 'SMA') {
                $orParts[] = "UPPER(TRIM({$kelasCol})) REGEXP '^(KELAS[[:space:]]*)?(10|11|12)([^0-9]|$)'";
            }
        }

        return '(' . implode(' OR ', $orParts) . ')';
    }

    /**
     * Filter a list of class strings or associative rows by user's allowed jenjang
     */
    public static function filterKelasList(array $rows, ?string $kelasKey = 'kelas', ?string $jenjangKey = 'jenjang'): array
    {
        if (!self::isJenjangRestricted()) {
            return $rows;
        }
        $filtered = [];
        foreach ($rows as $row) {
            if (is_string($row)) {
                $inf = self::inferJenjangFromClassName($row);
                if ($inf === '' || self::canAccessJenjang($inf)) {
                    $filtered[] = $row;
                }
                continue;
            }
            if (is_array($row)) {
                $rowJenjang = $jenjangKey && !empty($row[$jenjangKey]) ? (string)$row[$jenjangKey] : '';
                if ($rowJenjang === '' && $kelasKey && !empty($row[$kelasKey])) {
                    $rowJenjang = self::inferJenjangFromClassName((string)$row[$kelasKey]);
                }
                if ($rowJenjang === '' || self::canAccessJenjang($rowJenjang)) {
                    $filtered[] = $row;
                }
            }
        }
        return array_values($filtered);
    }

    /**
     * Ensure penugasan_grup has nullable unit column for unit-scoped SK groups
     */
    public static function ensurePenugasanGrupUnitColumn(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        try {
            $db = Database::getInstance();
            $col = $db->findAll("SHOW COLUMNS FROM `penugasan_grup` LIKE 'unit'");
            if (empty($col)) {
                $db->getConnection()->exec("ALTER TABLE `penugasan_grup` ADD COLUMN `unit` VARCHAR(50) NULL DEFAULT NULL AFTER `nama_grup`");
            }
        } catch (Throwable $e) {
            // Silently ignore if table missing or migration not possible
        }
    }

    /**
     * Detect unit/jenjang keyword from a penugasan group title
     */
    public static function detectUnitFromGrupName(string $namaGrup): string
    {
        $upper = strtoupper(trim($namaGrup));
        if ($upper === '') {
            return '';
        }
        if (preg_match('/\b(PAUD|TK)\b/', $upper)) {
            return 'PAUD';
        }
        if (preg_match('/\b(SD|SMP|SMA|YAYASAN|STAFF)\b/', $upper, $m)) {
            return $m[1];
        }
        return '';
    }

    /**
     * Build a SQL condition filtering penugasan_grup rows by user's allowed jenjang
     */
    public static function penugasanGrupUnitSqlCondition(string $alias, array &$params): string
    {
        self::ensurePenugasanGrupUnitColumn();
        if (!self::isJenjangRestricted()) {
            return '1=1';
        }
        $allowed = self::getAllowedJenjang();
        if (empty($allowed)) {
            return '1=0';
        }

        $explicitCond = self::unitSqlCondition("{$alias}.unit", $params);
        $memberCond   = self::unitSqlCondition('mut_pg_f.nama', $params);

        $kwParts = [];
        foreach ($allowed as $aj) {
            $u = strtoupper(trim($aj));
            if ($u === 'PAUD') {
                $kwParts[] = 'PAUD';
                $kwParts[] = 'TK';
            } elseif ($u !== '') {
                $kwParts[] = preg_quote($u, '/');
            }
        }
        $allowedKwRegex = '(^|[^A-Z0-9])(' . implode('|', array_unique($kwParts)) . ')([^A-Z0-9]|$)';
        $anyUnitKwRegex = '(^|[^A-Z0-9])(PAUD|TK|SD|SMP|SMA|YAYASAN|STAFF)([^A-Z0-9]|$)';
        $params[] = $allowedKwRegex;
        $params[] = $anyUnitKwRegex;

        return "(
            ({$alias}.unit IS NOT NULL AND TRIM({$alias}.unit) != '' AND ({$explicitCond}))
            OR (
                ({$alias}.unit IS NULL OR TRIM({$alias}.unit) = '')
                AND (
                    EXISTS (
                        SELECT 1 FROM pegawai_penugasan pp_pg_f
                        JOIN master_unit_tugas mut_pg_f ON pp_pg_f.unit_tugas_id = mut_pg_f.id
                        WHERE pp_pg_f.grup_id = {$alias}.id AND ({$memberCond})
                    )
                    OR (
                        NOT EXISTS (SELECT 1 FROM pegawai_penugasan pp_pg_e WHERE pp_pg_e.grup_id = {$alias}.id)
                        AND (
                            UPPER(TRIM({$alias}.nama_grup)) REGEXP ?
                            OR UPPER(TRIM({$alias}.nama_grup)) NOT REGEXP ?
                        )
                    )
                )
            )
        )";
    }

    /**
     * Check whether current user can access a given penugasan_grup row
     */
    public static function canAccessPenugasanGrup(array $grup): bool
    {
        if (!self::isJenjangRestricted()) {
            return true;
        }
        $explicitUnit = trim((string)($grup['unit'] ?? ''));
        if ($explicitUnit !== '') {
            return self::canAccessJenjang($explicitUnit);
        }

        $grupId = (int)($grup['id'] ?? 0);
        if ($grupId > 0) {
            $db = Database::getInstance();
            $memberUnits = $db->findAll(
                "SELECT DISTINCT mut.nama
                 FROM pegawai_penugasan pp
                 JOIN master_unit_tugas mut ON pp.unit_tugas_id = mut.id
                 WHERE pp.grup_id = ?",
                [$grupId]
            );
            if (!empty($memberUnits)) {
                foreach ($memberUnits as $mu) {
                    if (self::canAccessJenjang((string)($mu['nama'] ?? ''))) {
                        return true;
                    }
                }
                return false;
            }
        }

        $detected = self::detectUnitFromGrupName((string)($grup['nama_grup'] ?? ''));
        if ($detected !== '') {
            return self::canAccessJenjang($detected);
        }
        return true;
    }
}
