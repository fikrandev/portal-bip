<?php
define("BASE_PATH", dirname(__DIR__));
require_once BASE_PATH . "/config/app.php";
require_once BASE_PATH . "/modules/pengaturan-sistem/controllers/SettingsController.php";

$candidates = [
    BASE_PATH . "/public/uploads/settings/logo_1780981905.png",
    BASE_PATH . "/public/uploads/settings/favicon_app.png",
    BASE_PATH . "/public/uploads/settings/favicon_1780981905.ico"
];

$src = null;
foreach ($candidates as $c) {
    if (file_exists($c)) {
        $src = $c;
        break;
    }
}

if ($src) {
    $ok = SettingsController::syncAppIconToPwa($src);
    echo "PWA Icon sync result using [$src]: " . ($ok ? "OK" : "ERROR") . "\n";
} else {
    echo "No source icon found.\n";
}
