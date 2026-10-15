<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query("SHOW TRIGGERS WHERE `Table` = 'sarpras_barang'");
$triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Triggers: " . count($triggers) . "\n";
print_r($triggers);
