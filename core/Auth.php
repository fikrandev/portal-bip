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
     * Record a failed login attempt
     */
    private static function recordFailedAttempt(string $username): void
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        $key = md5($username . ($_SERVER['REMOTE_ADDR'] ?? ''));
        
        if (!isset($_SESSION['login_attempts'][$key])) {
            $_SESSION['login_attempts'][$key] = [
                'count' => 0,
                'first_attempt' => time()
            ];
        }

        $_SESSION['login_attempts'][$key]['count']++;
        $_SESSION['login_attempts'][$key]['last_attempt'] = time();
    }

    /**
     * Check if login is locked out
     */
    private static function isLockedOut(string $username): bool
    {
        $key = md5($username . ($_SERVER['REMOTE_ADDR'] ?? ''));
        
        if (!isset($_SESSION['login_attempts'][$key])) {
            return false;
        }

        $attempts = $_SESSION['login_attempts'][$key];
        
        if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
            $lockoutEnd = $attempts['last_attempt'] + (LOCKOUT_DURATION * 60);
            if (time() < $lockoutEnd) {
                return true;
            }
            // Lockout expired, clear
            self::clearFailedAttempts($username);
        }

        return false;
    }

    /**
     * Clear failed login attempts
     */
    private static function clearFailedAttempts(string $username): void
    {
        $key = md5($username . ($_SERVER['REMOTE_ADDR'] ?? ''));
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
