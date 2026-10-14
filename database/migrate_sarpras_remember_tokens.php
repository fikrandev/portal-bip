<?php
/**
 * Migration: Create sarpras_remember_tokens table for persistent mobile login
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `sarpras_remember_tokens` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
} catch (Throwable $e) {
    error_log('Migration sarpras_remember_tokens error: ' . $e->getMessage());
}
