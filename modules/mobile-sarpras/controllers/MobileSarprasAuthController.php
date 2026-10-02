<?php
/**
 * Mobile Sarpras Auth Controller
 */

class MobileSarprasAuthController
{
    public static function authRequired(): bool
    {
        if (!Auth::check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/mobile-sarpras/login');
            exit;
        }

        if (!RBAC::hasPermission('sarpras.mobile')) {
            http_response_code(403);
            include TEMPLATES_PATH . '/errors/403.php';
            exit;
        }

        return true;
    }

    public static function guestOnly(): bool
    {
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/mobile-sarpras');
            exit;
        }
        return true;
    }

    public static function showLogin(): void
    {
        if (Auth::check()) {
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

    public static function logout(): void
    {
        Auth::logout();
        header('Location: ' . BASE_URL . '/mobile-sarpras/login');
        exit;
    }
}
