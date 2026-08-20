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
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 7200); // 2 hours
ini_set('session.cookie_lifetime', 0);   // Until browser close

// HTTPS only in production
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}

// Set session name
session_name('PORTAL_BIP_SESSION');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
