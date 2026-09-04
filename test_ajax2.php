<?php
define('BASE_PATH', __DIR__);
require_once 'core/Database.php';
require_once 'modules/kelola-perangkat-pembelajaran/controllers/PerangkatController.php';

// Mock DB_HOST etc just in case index.php hasn't set them, wait, index.php does.
// So let's include config.php!
// Wait, the project root has 'core', 'modules'. Where is config?
// Usually config is loaded in index.php. Let's look at index.php!
