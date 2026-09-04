<?php
define("BASE_PATH", __DIR__);
require "config/database.php";
require "core/Database.php";

$db = Database::getInstance();
$rows = $db->findAll("SELECT * FROM settings WHERE setting_key LIKE '%sekolah%' OR setting_key LIKE '%logo%' OR setting_key LIKE '%kepala%'");
echo "=== CURRENT SETTINGS KEYS ===\n";
foreach ($rows as $r) {
    echo "{$r['setting_key']} => '{$r['setting_value']}'\n";
}
