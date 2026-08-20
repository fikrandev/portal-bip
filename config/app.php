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
// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
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
