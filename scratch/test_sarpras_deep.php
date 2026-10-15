<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/modules/kelola-sarpras/models/SarprasModel.php';
require_once BASE_PATH . '/modules/kelola-sarpras/controllers/SarprasController.php';

$pdo = Database::getInstance()->getConnection();

echo "=== DIAGNOSTIK MENDALAM INPUT INVENTARIS AMBIL FOTO ===\n\n";

// 1. Cek Schema sarpras_barang detail
$stmt = $pdo->query("SHOW COLUMNS FROM sarpras_barang");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "1. Cek Kolom-kolom sarpras_barang:\n";
foreach ($cols as $c) {
    echo sprintf(" - %-18s | Type: %-25s | Null: %-3s | Default: %s\n", $c['Field'], $c['Type'], $c['Null'], var_export($c['Default'], true));
}

// 2. Cek SQL Mode MySQL saat ini
$stmt = $pdo->query("SELECT @@sql_mode");
echo "\n2. SQL Mode: " . $stmt->fetchColumn() . "\n";

// 3. Cek Next Code Generator
echo "\n3. Test generateKodeBarangCustom(1, 1, 1):\n";
$code = SarprasModel::generateKodeBarangCustom(1, 1, 1);
echo "   Hasil: $code\n";

// 4. Cek apakah kode sudah ada di database
$stmt = $pdo->prepare("SELECT id, kode_barang FROM sarpras_barang WHERE kode_barang = ?");
$stmt->execute([$code]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existing) {
    echo "   [WARNING] Kode $code SUDAH ADA di DB! (ID: {$existing['id']})\n";
} else {
    echo "   [OK] Kode $code belum ada di DB.\n";
}

// 5. Test Dekode Base64 Foto dari Kamera (dengan newline, whitespace, dan MIME varian)
echo "\n5. Test regex handlePhotoUploads:\n";
$reflection = new ReflectionClass('SarprasController');
$methodHandlePhotos = $reflection->getMethod('handlePhotoUploads');
$methodHandlePhotos->setAccessible(true);

// Varian 1: normal base64
$v1 = 'data:image/jpeg;base64,' . base64_encode(str_repeat('A', 1000));
$_POST = ['foto_captured' => [$v1]];
$res1 = $methodHandlePhotos->invoke(null, []);
echo "   Varian 1 (Normal): " . ($res1 ? "SUKSES (" . substr($res1, 0, 50) . "...)" : "GAGAL") . "\n";

// Varian 2: base64 dengan newline \r\n (sangat umum dari FileReader mobile / curl)
$v2 = "data:image/jpeg;base64,\r\n" . chunk_split(base64_encode(str_repeat('B', 1000)), 76, "\r\n");
$_POST = ['foto_captured' => [$v2]];
$res2 = $methodHandlePhotos->invoke(null, []);
echo "   Varian 2 (Dengan Newline \\r\\n): " . ($res2 ? "SUKSES" : "GAGAL [!!! REGEX TIDAK COCOK DENGAN NEWLINE !!!]") . "\n";

// Varian 3: base64 dengan MIME parameter (contoh: charset=utf-8)
$v3 = 'data:image/jpeg;charset=utf-8;base64,' . base64_encode(str_repeat('C', 1000));
$_POST = ['foto_captured' => [$v3]];
$res3 = $methodHandlePhotos->invoke(null, []);
echo "   Varian 3 (Dengan MIME params): " . ($res3 ? "SUKSES" : "GAGAL [!!! REGEX TIDAK COCOK DENGAN MIME PARAMS !!!]") . "\n";

// Varian 4: input tanggal_perolehan kosong
echo "\n6. Test Insert dengan tanggal_perolehan kosong '':\n";
try {
    $db = Database::getInstance();
    $sql = "INSERT INTO sarpras_barang (kode_barang, nama_barang, kategori_id, ruangan_id, unit, tanggal_perolehan) VALUES (?, ?, ?, ?, ?, ?)";
    $db->query($sql, ['TEST.TGL.001', 'Test Tgl', 1, 1, 'SD', '']);
    echo "   Insert '' ke DATE: SUKSES (Tolerant)\n";
    $db->query("DELETE FROM sarpras_barang WHERE kode_barang = 'TEST.TGL.001'");
} catch (Throwable $e) {
    echo "   Insert '' ke DATE: GAGAL! " . $e->getMessage() . "\n";
}

// Varian 5: input ruangan_id = 0 atau NULL
echo "\n7. Test Insert dengan ruangan_id = NULL:\n";
try {
    $db = Database::getInstance();
    $sql = "INSERT INTO sarpras_barang (kode_barang, nama_barang, kategori_id, ruangan_id, unit) VALUES (?, ?, ?, ?, ?)";
    $db->query($sql, ['TEST.RUANG.001', 'Test Ruang', 1, null, 'SD']);
    echo "   Insert NULL ke ruangan_id: SUKSES\n";
    $db->query("DELETE FROM sarpras_barang WHERE kode_barang = 'TEST.RUANG.001'");
} catch (Throwable $e) {
    echo "   Insert NULL ke ruangan_id: GAGAL! " . $e->getMessage() . "\n";
}

// Varian 6: kolom foto diisi string > 64KB (misal jika base64 mentah tersimpan atau json array panjang)
echo "\n8. Test Insert ke kolom foto string > 64KB (misal 70KB data):\n";
try {
    $db = Database::getInstance();
    $longStr = str_repeat('X', 70000);
    $sql = "INSERT INTO sarpras_barang (kode_barang, nama_barang, kategori_id, ruangan_id, unit, foto) VALUES (?, ?, ?, ?, ?, ?)";
    $db->query($sql, ['TEST.FOTO.001', 'Test Foto Long', 1, 1, 'SD', $longStr]);
    echo "   Insert >64KB ke foto TEXT: SUKSES\n";
    $db->query("DELETE FROM sarpras_barang WHERE kode_barang = 'TEST.FOTO.001'");
} catch (Throwable $e) {
    echo "   Insert >64KB ke foto TEXT: GAGAL! " . $e->getMessage() . "\n";
}
