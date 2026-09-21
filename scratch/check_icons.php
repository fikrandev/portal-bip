<?php
$files = [
    "public/uploads/settings/logo_1780981905.png",
    "public/uploads/settings/favicon_app.png",
    "public/uploads/settings/favicon_1780981905.ico",
    "public/images/pwa/icon-512.png"
];
foreach ($files as $f) {
    if (file_exists($f)) {
        echo "$f: size " . filesize($f) . " bytes\n";
        $info = @getimagesize($f);
        if ($info) {
            echo "  dim: {$info[0]}x{$info[1]}, mime: {$info['mime']}\n";
        }
    }
}
