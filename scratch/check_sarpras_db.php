<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query("SHOW CREATE TABLE sarpras_barang");
echo $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'] . "\n\n";

$stmt = $pdo->query("SHOW FULL COLUMNS FROM sarpras_barang");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['Field']} | {$row['Type']} | Null: {$row['Null']} | Default: {$row['Default']}\n";
}
