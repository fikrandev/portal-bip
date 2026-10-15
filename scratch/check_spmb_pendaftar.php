<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->query("SHOW COLUMNS FROM spmb_pendaftar");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "=== KOLOM spmb_pendaftar ===\n";
foreach ($cols as $c) {
    echo "{$c['Field']} | {$c['Type']} | Null: {$c['Null']} | Default: {$c['Default']}\n";
}

$stmt = $pdo->query("SELECT COUNT(*) as total FROM spmb_pendaftar");
echo "\nTotal Pendaftar: " . $stmt->fetchColumn() . "\n";

$stmt = $pdo->query("SELECT id, nomor_pendaftaran, nama_lengkap, unit, jenis_pendaftaran, status_pendaftaran, status_kelulusan FROM spmb_pendaftar LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
