<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$db = Database::getInstance();

echo "=== FOREIGN KEYS ON sarpras_barang ===\n";
$fks = $db->findAll("
    SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sarpras_barang' AND REFERENCED_TABLE_NAME IS NOT NULL
");
print_r($fks);

echo "\n=== RUANGAN LIST ===\n";
$ruangan = $db->findAll("SELECT id, nama_ruangan FROM sarpras_ruangan");
print_r($ruangan);

echo "\n=== KATEGORI LIST ===\n";
$kat = $db->findAll("SELECT id, nama_kategori FROM sarpras_kategori");
print_r($kat);

echo "\n=== GOLONGAN LIST ===\n";
$gol = $db->findAll("SELECT id, kode, nama_golongan FROM sarpras_golongan");
print_r($gol);

echo "\n=== KELOMPOK LIST ===\n";
$kel = $db->findAll("SELECT id, kode, nama_kelompok FROM sarpras_kode_kelompok");
print_r($kel);

echo "\n=== ASAL ANGGARAN LIST ===\n";
$ang = $db->findAll("SELECT id, kode, nama FROM sarpras_asal_anggaran");
print_r($ang);
