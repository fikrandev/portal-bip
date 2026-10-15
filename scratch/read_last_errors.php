<?php
$lines = file('storage/logs/php_errors.log');
$last50 = array_slice($lines, -50);
echo implode('', $last50);
