<?php
require 'core/Database.php';
$db = Database::getInstance();
$modules = $db->findAll("SELECT * FROM modules");
print_r($modules);
