<?php
/**
 * Portal BIP - Authentication Helper
 * 
 * Manages user login, logout, session state, and login throttling.
 */

class Auth
{
    private static ?array $currentUser = null;

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
            "SELECT u.*, GROUP_CONCAT(r.slug) as role_slugs 
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
     * Log out the current user
     */
    public static function logout(): void
    {
        if (self::check()) {
            self::logAudit('LOGOUT', 'user', $_SESSION['user_id'] ?? null);
        }

        $_SESSION = [];

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
     * Ensure login_attempts table exists in database
     */
    private static function ensureAttemptsTable(): void
    {
        static $checked = false;
        if ($checked) return;
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
     */
    private static function logAudit(string $action, string $entityType, ?int $entityId): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('audit_logs', [
                'user_id'    => self::id(),
                'action'     => $action,
                'entity_type'=> $entityType,
                'entity_id'  => $entityId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Exception $e) {
            error_log('Audit log error: ' . $e->getMessage());
        }
    }
}
