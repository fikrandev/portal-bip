<?php
/**
 * Auth Controller
 * Handles login and logout
 */

class AuthController
{
    /**
     * Show login form
     */
    public static function showLogin(): void
    {
        $pageTitle = 'Masuk';
        $pageDescription = 'Masuk ke Portal BIP';
        
        ob_start();
        include MODULES_PATH . '/auth/views/login.php';
        $content = ob_get_clean();
        
        include TEMPLATES_PATH . '/layouts/auth.php';
    }

    /**
     * Process login
     */
    public static function login(): void
    {
        // Validate CSRF
        if (!CSRF::validate()) {
            Response::withError(url('login'), 'Token keamanan tidak valid. Silakan coba lagi.');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        $validator = Validator::make($_POST)
            ->required('username', 'Username')
            ->required('password', 'Password');

        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }

        // Attempt login
        $result = Auth::login($username, $password);

        if ($result['success']) {
            $intended = $_SESSION['intended_url'] ?? url('dashboard');
            unset($_SESSION['intended_url']);
            Response::redirect($intended);
        } else {
            Response::withError(url('login'), $result['message']);
        }
    }

    /**
     * Logout
     */
    public static function logout(): void
    {
        Auth::logout();
        Response::withSuccess(url('login'), 'Anda telah berhasil keluar.');
    }
}
