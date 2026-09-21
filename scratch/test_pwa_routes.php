<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/portal-bip/manifest.json';
$_SERVER['SCRIPT_NAME'] = '/portal-bip/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';

ob_start();
// Load settings and test manifest generation
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once __DIR__ . '/../core/Database.php';

// Check icons in public/images/pwa
echo "PWA Icons in public/images/pwa/:\n";
foreach (glob(__DIR__ . '/../public/images/pwa/*.png') as $file) {
    $info = getimagesize($file);
    echo basename($file) . " -> " . ($info ? "{$info[0]}x{$info[1]}" : "unknown") . " (" . filesize($file) . " bytes)\n";
}

echo "\nUploaded settings logo:\n";
foreach (glob(__DIR__ . '/../public/uploads/settings/*.*') as $file) {
    $info = @getimagesize($file);
    echo basename($file) . " -> " . ($info ? "{$info[0]}x{$info[1]}" : "unknown") . "\n";
}
