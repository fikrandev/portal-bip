<?php
/**
 * Script Pembersih Data Dummy / Bawaan Sarpras
 * Portal BIP
 *
 * Menghapus seluruh data operasional (Tanah, Bangunan, Ruangan, Barang, Distribusi,
 * Peminjaman, Pemeliharaan, Maintenance, Pengajuan) agar fresh 0 baris,
 * namun tetap mempertahankan master referensi (Kategori, Satuan, Golongan, Kelompok, Asal Anggaran).
 */

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "=== PEMBERSIHAN DATA SARPRAS (FRESH RESET) ===" . PHP_EOL;

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    $tablesToWipe = [
        'sarpras_peminjaman',
        'sarpras_pemeliharaan',
        'sarpras_maintenance',
        'sarpras_distribusi',
        'sarpras_pengajuan',
        'sarpras_barang',
        'sarpras_ruangan',
        'sarpras_bangunan',
        'sarpras_tanah'
    ];

    foreach ($tablesToWipe as $tbl) {
        $check = $pdo->query("SHOW TABLES LIKE '$tbl'")->fetch();
        if ($check) {
            $pdo->exec("TRUNCATE TABLE `$tbl`;");
            echo "✓ Tabel `$tbl` berhasil dikosongkan (AUTO_INCREMENT reset ke 1)." . PHP_EOL;
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // Hapus foto barang dummy jika ada di uploads/sarpras
    $uploadDir = BASE_PATH . '/public/uploads/sarpras';
    if (is_dir($uploadDir)) {
        $files = glob($uploadDir . '/sarpras_*.*');
        foreach ($files as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }
        echo "✓ File gambar dummy inventaris berhasil dibersihkan." . PHP_EOL;
    }

    echo "=== SELESAI: Data Sarpras sekarang 100% Kosong dan Fresh! ===" . PHP_EOL;

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
