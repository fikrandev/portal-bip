<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query("SELECT id, username, email FROM users LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
