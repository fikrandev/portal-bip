<?php
$content = file_get_contents('index.php');
$content = preg_replace_callback('/(\$router->(?:get|post)\(\'\/mobile-sarpras.*?), \[\[Middleware::class, \'authRequired\'\]\]\);/', function($m) { 
    return $m[1] . ', [Middleware::permissionRequired(\'sarpras.mobile\')]);'; 
}, $content);
file_put_contents('index.php', $content);
echo "Replaced in index.php\n";
