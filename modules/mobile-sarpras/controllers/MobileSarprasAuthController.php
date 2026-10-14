<?php
/**
 * Mobile Sarpras Auth Controller
 * 
 * Mendukung sesi login permanen ("Ingat Selamanya") via Multi-layer Persistent Token:
 * 1. Long-lived secure cookie (10 tahun) dengan SameSite=Lax (kompatibel PWA standalone).
 * 2. Token database terenkripsi (sarpras_remember_tokens).
 * 3. Client-side storage fallback (localStorage) untuk ketahanan pada iOS Safari / Android PWA.
 */

class MobileSarprasAuthController
{
    public const TOKEN_COOKIE_NAME = 'sarpras_mobile_token';
    public const TOKEN_LIFETIME = 315360000; // 10 tahun (detik)

    /**
     * Pastikan tabel sarpras_remember_tokens sudah tersedia di database
     */
    public static function ensureTable(): void
    {
        static $checked = false;
        if ($checked) return;
        try {
            $db = Database::getInstance();
            $db->query("CREATE TABLE IF NOT EXISTS `sarpras_remember_tokens` (
                `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `token_hash` VARCHAR(64) NOT NULL,
                `device_name` VARCHAR(150) DEFAULT NULL,
                `user_agent` VARCHAR(255) DEFAULT NULL,
                `ip_address` VARCHAR(45) DEFAULT NULL,
                `expires_at` DATETIME NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `last_used_at` DATETIME DEFAULT NULL,
                INDEX `idx_srt_user` (`user_id`),
                INDEX `idx_srt_hash` (`token_hash`),
                INDEX `idx_srt_expires` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            $checked = true;
        } catch (Throwable $e) {
            error_log('ensureTable sarpras_remember_tokens error: ' . $e->getMessage());
        }
    }

    /**
     * Coba auto-login dari cookie persistent remember token
     */
    public static function tryAutoLogin(): bool
    {
        if (Auth::check()) {
            return true;
        }

        $token = $_COOKIE[self::TOKEN_COOKIE_NAME] ?? null;
        if (empty($token) && !empty($_SESSION['active_sarpras_token'])) {
            $token = $_SESSION['active_sarpras_token'];
        }

        if (empty($token)) {
            return false;
        }

        self::ensureTable();

        try {
            $tokenHash = hash('sha256', $token);
            $db = Database::getInstance();
            $record = $db->find(
                "SELECT user_id, expires_at FROM `sarpras_remember_tokens` 
                 WHERE `token_hash` = ? AND `expires_at` > NOW() LIMIT 1",
                [$tokenHash]
            );

            if (!$record) {
                self::clearRememberCookie();
                return false;
            }

            $userId = (int)$record['user_id'];
            if (Auth::loginUsingId($userId)) {
                if (!RBAC::hasPermission('sarpras.mobile')) {
                    Auth::logout();
                    self::clearRememberCookie();
                    return false;
                }

                // Perbarui waktu penggunaan terakhir
                $db->update(
                    'sarpras_remember_tokens',
                    ['last_used_at' => date('Y-m-d H:i:s')],
                    'token_hash = ?',
                    [$tokenHash]
                );

                // Perpanjang cookie
                self::setRememberCookie($token);
                $_SESSION['active_sarpras_token'] = $token;

                return true;
            }
        } catch (Throwable $e) {
            error_log('tryAutoLogin error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Terbitkan persistent token baru untuk user
     */
    public static function issueRememberToken(int $userId): string
    {
        self::ensureTable();

        try {
            $plainToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $plainToken);
            $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_LIFETIME);

            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

            $db = Database::getInstance();
            $db->insert('sarpras_remember_tokens', [
                'user_id'      => $userId,
                'token_hash'   => $tokenHash,
                'device_name'  => self::detectDeviceName(),
                'user_agent'   => $userAgent,
                'ip_address'   => $ipAddress,
                'expires_at'   => $expiresAt,
                'created_at'   => date('Y-m-d H:i:s'),
                'last_used_at' => date('Y-m-d H:i:s'),
            ]);

            self::setRememberCookie($plainToken);
            $_SESSION['active_sarpras_token'] = $plainToken;

            return $plainToken;
        } catch (Throwable $e) {
            error_log('issueRememberToken error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Set long-lived persistent cookie
     */
    public static function setRememberCookie(string $token): void
    {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        setcookie(self::TOKEN_COOKIE_NAME, $token, [
            'expires'  => time() + self::TOKEN_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Hapus cookie persistent remember
     */
    public static function clearRememberCookie(): void
    {
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

        setcookie(self::TOKEN_COOKIE_NAME, '', [
            'expires'  => time() - 86400,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        unset($_SESSION['active_sarpras_token']);
    }

    /**
     * Deteksi nama perangkat untuk kemudahan tracking
     */
    private static function detectDeviceName(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (stripos($ua, 'iPhone') !== false) return 'iPhone';
        if (stripos($ua, 'iPad') !== false) return 'iPad';
        if (stripos($ua, 'Android') !== false) return 'Android Phone';
        if (stripos($ua, 'Windows') !== false) return 'Windows PC';
        if (stripos($ua, 'Macintosh') !== false) return 'Mac';
        return 'Mobile Device';
    }

    /**
     * Middleware: Mengharuskan otentikasi Sarpras Mobile
     */
    public static function authRequired(): bool
    {
        if (!Auth::check()) {
            // Coba auto-login dari token selamanya
            if (self::tryAutoLogin()) {
                return true;
            }

            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/mobile-sarpras/login');
            exit;
        }

        if (!RBAC::hasPermission('sarpras.mobile')) {
            http_response_code(403);
            include TEMPLATES_PATH . '/errors/403.php';
            exit;
        }

        // Jika user sudah login tapi belum memiliki cookie persistent, buatkan otomatis
        if (empty($_COOKIE[self::TOKEN_COOKIE_NAME])) {
            self::issueRememberToken((int)Auth::id());
        }

        return true;
    }

    /**
     * Middleware: Hanya untuk guest (belum login)
     */
    public static function guestOnly(): bool
    {
        if (self::tryAutoLogin() || Auth::check()) {
            header('Location: ' . BASE_URL . '/mobile-sarpras');
            exit;
        }
        return true;
    }

    /**
     * Tampilkan form login
     */
    public static function showLogin(): void
    {
        if (self::tryAutoLogin() || Auth::check()) {
            header('Location: ' . BASE_URL . '/mobile-sarpras');
            exit;
        }
        
        $pageTitle = 'Login Sarpras Mobile';
        
        // Bersihkan output buffer
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            ob_clean();
        }
        
        include MODULES_PATH . '/mobile-sarpras/views/login.php';
    }

    /**
     * Proses login username & password
     */
    public static function login(): void
    {
        if (!CSRF::validate()) {
            $_SESSION['flash_error'] = 'Token keamanan tidak valid. Silakan coba lagi.';
            header('Location: ' . BASE_URL . '/mobile-sarpras/login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $result = Auth::login($username, $password);

        if ($result['success']) {
            if (!RBAC::hasPermission('sarpras.mobile')) {
                Auth::logout();
                $_SESSION['flash_error'] = 'Akun Anda tidak memiliki hak akses untuk Sarpras Mobile.';
                header('Location: ' . BASE_URL . '/mobile-sarpras/login');
                exit;
            }

            // Buat token sesi selamanya (10 tahun)
            $token = self::issueRememberToken((int)Auth::id());
            $_SESSION['active_sarpras_token'] = $token;

            $intended = $_SESSION['intended_url'] ?? url('mobile-sarpras');
            unset($_SESSION['intended_url']);
            
            // Pastikan selalu ke mobile-sarpras
            if (strpos($intended, 'mobile-sarpras') === false) {
                $intended = url('mobile-sarpras');
            }
            
            header('Location: ' . $intended);
            exit;
        } else {
            $_SESSION['flash_error'] = $result['message'];
            header('Location: ' . BASE_URL . '/mobile-sarpras/login');
            exit;
        }
    }

    /**
     * API Endpoint: Auto-login menggunakan token dari client-side (localStorage fallback)
     */
    public static function tokenLogin(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $inputRaw = file_get_contents('php://input');
        $input = json_decode($inputRaw, true) ?: [];
        $token = trim($input['token'] ?? ($_POST['token'] ?? ''));

        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Token tidak ditemukan.']);
            exit;
        }

        self::ensureTable();

        try {
            $tokenHash = hash('sha256', $token);
            $db = Database::getInstance();
            $record = $db->find(
                "SELECT user_id, expires_at FROM `sarpras_remember_tokens` 
                 WHERE `token_hash` = ? AND `expires_at` > NOW() LIMIT 1",
                [$tokenHash]
            );

            if (!$record) {
                echo json_encode(['success' => false, 'message' => 'Token sudah tidak valid atau kedaluwarsa.']);
                exit;
            }

            $userId = (int)$record['user_id'];
            if (Auth::loginUsingId($userId)) {
                if (!RBAC::hasPermission('sarpras.mobile')) {
                    Auth::logout();
                    echo json_encode(['success' => false, 'message' => 'Tidak memiliki hak akses Sarpras Mobile.']);
                    exit;
                }

                // Update token & perbarui cookie
                $db->update(
                    'sarpras_remember_tokens',
                    ['last_used_at' => date('Y-m-d H:i:s')],
                    'token_hash = ?',
                    [$tokenHash]
                );

                self::setRememberCookie($token);
                $_SESSION['active_sarpras_token'] = $token;

                echo json_encode([
                    'success'  => true,
                    'message'  => 'Sesi berhasil dipulihkan otomatis.',
                    'redirect' => url('mobile-sarpras')
                ]);
                exit;
            }
        } catch (Throwable $e) {
            error_log('tokenLogin error: ' . $e->getMessage());
        }

        echo json_encode(['success' => false, 'message' => 'Gagal memulihkan sesi.']);
        exit;
    }

    /**
     * Logout dari Sarpras Mobile
     */
    public static function logout(): void
    {
        $token = $_COOKIE[self::TOKEN_COOKIE_NAME] ?? ($_SESSION['active_sarpras_token'] ?? null);
        if ($token) {
            try {
                $db = Database::getInstance();
                $db->delete('sarpras_remember_tokens', 'token_hash = ?', [hash('sha256', $token)]);
            } catch (Throwable $e) {
                error_log('logout token deletion error: ' . $e->getMessage());
            }
        }

        self::clearRememberCookie();
        Auth::logout();

        // Redirect dengan flag logged_out agar client-side localStorage juga dibersihkan
        header('Location: ' . BASE_URL . '/mobile-sarpras/login?logged_out=1');
        exit;
    }
}
