<?php
/**
 * Portal BIP - Middleware
 * 
 * Route middleware for authentication and authorization checks.
 */

class Middleware
{
    /**
     * Require authentication — redirects to login if not logged in
     */
    public static function authRequired(): bool
    {
        if (!Auth::check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            $_SESSION['flash_warning'] = 'Silakan login terlebih dahulu.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        return true;
    }

    /**
     * Require a specific role
     * 
     * @param string $roleSlug
     * @return callable
     */
    public static function roleRequired(string $roleSlug): callable
    {
        return function () use ($roleSlug) {
            Middleware::authRequired();
            if (!Auth::hasRole($roleSlug)) {
                http_response_code(403);
                include TEMPLATES_PATH . '/errors/403.php';
                exit;
            }
            return true;
        };
    }

    /**
     * Require a specific permission
     * 
     * @param string $permissionSlug
     * @return callable
     */
    public static function permissionRequired(string $permissionSlug): callable
    {
        return function () use ($permissionSlug) {
            Middleware::authRequired();
            if (!RBAC::hasPermission($permissionSlug)) {
                http_response_code(403);
                include TEMPLATES_PATH . '/errors/403.php';
                exit;
            }
            return true;
        };
    }

    /**
     * Guest only — redirect to dashboard if already logged in
     */
    public static function guestOnly(): bool
    {
        if (Auth::check()) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        return true;
    }

    /**
     * CSRF validation for POST requests
     */
    public static function csrfProtection(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CSRF::validate()) {
                http_response_code(419);
                $_SESSION['flash_error'] = 'Token keamanan tidak valid. Silakan coba lagi.';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
                exit;
            }
        }
        return true;
    }
}
