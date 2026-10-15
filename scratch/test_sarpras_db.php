<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$db = Database::getInstance();
foreach ($db->findAll('DESCRIBE sarpras_ruangan') as $col) {
    echo sprintf("%-20s %-20s %-10s %-10s %s\n", $col['Field'], $col['Type'], $col['Null'], $col['Key'], $col['Default'] ?? 'NULL');
}
