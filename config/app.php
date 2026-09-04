<?php
/**
 * Portal BIP - Application Configuration
 * 
 * Defines all application-wide constants and settings.
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    die('Direct access not permitted');
}

// ── Application ──────────────────────────────────────
define('APP_NAME', 'Portal BIP');
define('APP_VERSION', '1.0.0');
define('APP_DESCRIPTION', 'Portal Manajemen Informasi Terpadu');
define('APP_TIMEZONE', 'Asia/Makassar');

// ── URL Configuration ────────────────────────────────
// Auto-detect base URL (supports direct domain, subdirectory, HTTP, HTTPS, and reverse proxies)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

$protocol = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
if ($scriptDir === '/' || $scriptDir === '.') {
    $scriptDir = '';
}
$baseUrl = rtrim($protocol . '://' . $host . $scriptDir, '/');
define('BASE_URL', $baseUrl);

// ── Path Configuration ──────────────────────────────
define('CONFIG_PATH', BASE_PATH . '/config');
define('CORE_PATH', BASE_PATH . '/core');
define('MODULES_PATH', BASE_PATH . '/modules');
define('TEMPLATES_PATH', BASE_PATH . '/templates');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('LOG_PATH', STORAGE_PATH . '/logs');

// ── Security ─────────────────────────────────────────
define('CSRF_TOKEN_NAME', '_csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 15); // minutes
define('PASSWORD_MIN_LENGTH', 8);

// ── Pagination ───────────────────────────────────────
define('ITEMS_PER_PAGE', 15);

// ── Date Format ──────────────────────────────────────
define('DATE_FORMAT', 'd/m/Y');
define('DATETIME_FORMAT', 'd/m/Y H:i');

// ── Set Timezone ─────────────────────────────────────
date_default_timezone_set(APP_TIMEZONE);

// ── Error Reporting ──────────────────────────────────
// Set to 0 in production
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', LOG_PATH . '/php_errors.log');

// ── PHP Calendar Polyfill (Fallback if ext-calendar not installed) ──
if (!defined('CAL_GREGORIAN')) {
    define('CAL_GREGORIAN', 0);
}
if (!function_exists('cal_days_in_month')) {
    function cal_days_in_month($calendar, $month, $year) {
        return (int)date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
    }
}
