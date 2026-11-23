<?php
/**
 * CLI Cron Script - Pengingat Pengisian Jurnal Staff via WA Gateway
 *
 * Contoh penggunaan:
 *   php cron/jurnal_staff_reminder.php
 *   php cron/jurnal_staff_reminder.php --slot=09:00
 *   php cron/jurnal_staff_reminder.php --slot=13:00 --force --dry-run
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden: CLI execution only.');
}

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/WaGateway.php';
require_once BASE_PATH . '/modules/kelola-jurnal-staff/models/JurnalStaffModel.php';
require_once BASE_PATH . '/modules/kelola-jurnal-staff/services/JurnalStaffReminderService.php';

$opts = getopt('', ['slot::', 'date::', 'force', 'dry-run']);

$slot = isset($opts['slot']) && is_string($opts['slot']) ? trim($opts['slot']) : null;
$date = isset($opts['date']) && is_string($opts['date']) ? trim($opts['date']) : null;
$force = array_key_exists('force', $opts);
$dryRun = array_key_exists('dry-run', $opts);

$result = JurnalStaffReminderService::runScheduledDispatch(
    $slot !== '' ? $slot : null,
    $date !== '' ? $date : null,
    'cli_cron',
    $force,
    $dryRun
);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
