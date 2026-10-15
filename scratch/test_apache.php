<?php
$c = file_get_contents('E:/xampp/apache/conf/httpd.conf');
preg_match_all('/(Listen\s+\d+|DocumentRoot\s+"[^"]+")/i', $c, $m);
print_r($m[0]);

if (file_exists('E:/xampp/apache/conf/extra/httpd-vhosts.conf')) {
    echo "\n=== VHOSTS ===\n";
    echo file_get_contents('E:/xampp/apache/conf/extra/httpd-vhosts.conf');
}
