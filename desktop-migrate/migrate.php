<?php
/**
 * Script Migrasi Database Portal BIP
 * 
 * Jalankan script ini via terminal:
 * php desktop-migrate/migrate.php
 */

// Menentukan base path agar bisa memuat file config jika diperlukan
define('BASE_PATH', dirname(__DIR__));

$host = 'localhost';
$dbname = 'db_portal_bip';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Terhubung ke database {$dbname}...\n";

    // ========================================================================
    // TULIS KODE MIGRASI / PERUBAHAN TABEL DI BAWAH INI
    // ========================================================================

    /*
    Contoh: Menambah kolom baru pada tabel
    $sql = "ALTER TABLE nama_tabel ADD nama_kolom VARCHAR(100) NULL AFTER kolom_lain";
    $pdo->exec($sql);
    echo "Kolom berhasil ditambahkan.\n";
    */
    
    // ========================================================================
    
    echo "Migrasi selesai!\n";

} catch (PDOException $e) {
    die("Koneksi / Eksekusi gagal: " . $e->getMessage() . "\n");
}
