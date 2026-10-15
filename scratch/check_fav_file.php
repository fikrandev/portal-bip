<?php
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Database.php';

$pdo = Database::getInstance()->getConnection();
$fav = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'app_favicon'")->fetchColumn();
echo "app_favicon in db: $fav\n";

$logo = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'app_logo'")->fetchColumn();
echo "app_logo in db: $logo\n";

$favPath = BASE_PATH . '/' . ltrim($fav, '/');
echo "Favicon path: $favPath (exists: " . (file_exists($favPath) ? "YES" : "NO") . ")\n";

$logoPath = BASE_PATH . '/' . ltrim($logo, '/');
echo "Logo path: $logoPath (exists: " . (file_exists($logoPath) ? "YES" : "NO") . ")\n";

echo "Listing public/uploads/settings:\n";
print_r(glob(BASE_PATH . '/public/uploads/settings/*.*'));
