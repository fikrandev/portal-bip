<?php
/**
 * Portal BIP - Session Configuration
 * 
 * Secure session settings with httponly, samesite, and regeneration.
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

// ── Session Configuration ───────────────────────────
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax'); // 'Lax' allows cookie preservation on PWA launch and top-level navigation
ini_set('session.gc_maxlifetime', 315360000); // 10 years
ini_set('session.cookie_lifetime', 315360000);   // 10 years

// Save sessions inside project storage/sessions if possible to prevent OS /tmp cleanup wipes
$sessionDir = BASE_PATH . '/storage/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0755, true);
}
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    session_save_path($sessionDir);
}

// HTTPS only in production (supports reverse proxy & Cloudflare)
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

if ($isSecure) {
    ini_set('session.cookie_secure', '1');
}

// Set session name
session_name('PORTAL_BIP_SESSION');

// Apply explicit cookie parameters before starting session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 315360000,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
