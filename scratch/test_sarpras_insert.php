<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/modules/kelola-sarpras/models/SarprasModel.php';
require_once BASE_PATH . '/modules/kelola-sarpras/controllers/SarprasController.php';

// Create a dummy base64 1x1 png or jpeg
$dummyBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents(BASE_PATH . '/icon-192.png'));

$_POST = [
    'nama_barang' => 'Test Barang Foto ' . time(),
    'golongan_id' => 1,
    'kelompok_id' => 1,
    'asal_anggaran_id' => 1,
    'kategori_id' => 1,
    'unit' => 'SD',
    'jumlah' => 1,
    'satuan' => 'Unit',
    'kondisi' => 'Baik',
    'status' => 'Tersedia',
    'merk_model' => 'Test Model',
    'nomor_seri' => 'SN-12345',
    'tanggal_perolehan' => date('Y-m-d'),
    'masa_manfaat' => 5,
    'harga_perolehan' => '150000',
    'foto_captured' => [$dummyBase64]
];

echo "Simulating SarprasController::barangStore without redirect...\n";

// Let's test SarprasModel::generateKodeBarangCustom
$kode = SarprasModel::generateKodeBarangCustom(1, 1, 1);
echo "Generated Kode: $kode\n";

// Let's test handlePhotoUploads
$reflection = new ReflectionClass('SarprasController');
$method = $reflection->getMethod('handlePhotoUploads');
$method->setAccessible(true);
$fotoPath = $method->invoke(null, []);
echo "Foto Path: $fotoPath\n";

try {
    $insertedId = SarprasModel::insertBarang([
        'kode_barang' => $kode,
        'nama_barang' => $_POST['nama_barang'],
        'golongan_id' => $_POST['golongan_id'],
        'kelompok_id' => $_POST['kelompok_id'],
        'asal_anggaran_id' => $_POST['asal_anggaran_id'],
        'kategori_id' => $_POST['kategori_id'],
        'ruangan_id' => (int)($_POST['ruangan_id'] ?? 1),
        'unit' => $_POST['unit'],
        'merk_model' => $_POST['merk_model'],
        'nomor_seri' => $_POST['nomor_seri'],
        'jumlah' => $_POST['jumlah'],
        'satuan' => $_POST['satuan'],
        'kondisi' => $_POST['kondisi'],
        'status' => $_POST['status'],
        'tanggal_perolehan' => $_POST['tanggal_perolehan'],
        'masa_manfaat' => $_POST['masa_manfaat'],
        'harga_perolehan' => (float)$_POST['harga_perolehan'],
        'foto' => $fotoPath,
        'keterangan' => null
    ]);
    echo "SUCCESS! Inserted ID: $insertedId\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
}
