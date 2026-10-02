<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';
$db = Database::getInstance();
try {
    $db->query("ALTER TABLE sarpras_barang ADD COLUMN dipakai INT DEFAULT 0 AFTER jumlah;");
    echo "Added dipakai column. ";
} catch (Exception $e) {
    echo "Column dipakai might already exist. ";
}
$db->query("UPDATE sarpras_barang b SET dipakai = COALESCE((SELECT SUM(jumlah) FROM sarpras_distribusi d WHERE d.barang_id = b.id), 0) + COALESCE((SELECT SUM(jumlah) FROM sarpras_peminjaman p WHERE p.barang_id = b.id AND p.status IN ('Dipinjam')), 0);");
echo 'Fixed dipakai counts';
