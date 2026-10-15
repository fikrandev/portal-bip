<?php
$data = 'data:image/jpeg;base64,' . str_repeat('A', 10 * 1024 * 1024); // 10MB string
$res = preg_match('/^data:image\/(\w+);base64,(.+)$/', $data, $matches);
echo "preg_match result: " . var_export($res, true) . "\n";
echo "preg_last_error: " . preg_last_error_msg() . "\n";
