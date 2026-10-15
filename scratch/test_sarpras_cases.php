<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/core/Response.php';
require_once BASE_PATH . '/modules/kelola-sarpras/models/SarprasModel.php';
require_once BASE_PATH . '/modules/kelola-sarpras/controllers/SarprasController.php';

$db = Database::getInstance();

echo "Testing SarprasController::barangStore cases...\n";

// Dummy realistic canvas base64 image (JPEG header)
$sampleJpgData = base64_encode(file_get_contents(BASE_PATH . '/icon-192.png'));
$capturedBase64 = 'data:image/jpeg;base64,' . $sampleJpgData;

// Test case 1: Real form post simulation as sent by browser
$testPayloads = [
    'standard_camera' => [
        'nama_barang' => 'Laptop Lenovo ThinkPad ' . rand(100, 999),
        'unit' => 'SD',
        'kategori_id' => '1',
        'golongan_id' => '1',
        'kelompok_id' => '1',
        'asal_anggaran_id' => '1',
        'merk_model' => 'ThinkPad T480',
        'nomor_seri' => 'PF-123456',
        'jumlah' => '1',
        'satuan' => 'Unit',
        'harga_perolehan_formatted' => '5.500.000',
        'harga_perolehan' => '5500000',
        'tanggal_perolehan' => date('Y-m-d'),
        'masa_manfaat' => '5',
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'foto_captured' => [$capturedBase64]
    ],
    'camera_with_no_ruangan' => [
        'nama_barang' => 'Kursi Guru ' . rand(100, 999),
        'unit' => 'SMP',
        'kategori_id' => '2',
        'golongan_id' => '1',
        'kelompok_id' => '1',
        'asal_anggaran_id' => '1',
        'merk_model' => 'Informa',
        'nomor_seri' => '',
        'jumlah' => '5',
        'satuan' => 'Pcs',
        'harga_perolehan_formatted' => '350.000',
        'harga_perolehan' => '350000',
        'tanggal_perolehan' => date('Y-m-d'),
        'masa_manfaat' => '3',
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        'foto_captured' => [$capturedBase64]
    ],
    'mobile_form_exact' => [
        'redirect_to' => '/portal-bip/mobile-sarpras/inventaris',
        'unit' => 'SD',
        'kategori_id' => '1',
        'golongan_id' => '1',
        'kelompok_id' => '1',
        'nama_barang' => 'Printer Epson L3210 ' . rand(100, 999),
        'merk_model' => 'Epson',
        'jumlah' => '1',
        'satuan' => 'Unit',
        'harga_perolehan_formatted' => '2.400.000',
        'harga_perolehan' => '2400000',
        'asal_anggaran_id' => '1',
        'tanggal_perolehan' => date('Y-m-d'),
        'masa_manfaat' => '5',
        'kondisi' => 'Baik',
        'foto_captured' => [$capturedBase64]
    ],
    'large_camera_photo_3mb' => [
        'nama_barang' => 'Proyektor BenQ ' . rand(100, 999),
        'unit' => 'SMA',
        'kategori_id' => '1',
        'golongan_id' => '1',
        'kelompok_id' => '1',
        'asal_anggaran_id' => '1',
        'merk_model' => 'BenQ MX550',
        'nomor_seri' => 'SN-BENQ-888',
        'jumlah' => '1',
        'satuan' => 'Unit',
        'harga_perolehan_formatted' => '4.800.000',
        'harga_perolehan' => '4800000',
        'tanggal_perolehan' => date('Y-m-d'),
        'masa_manfaat' => '5',
        'kondisi' => 'Baik',
        'status' => 'Tersedia',
        // 3MB of base64 data
        'foto_captured' => ['data:image/jpeg;base64,' . str_repeat('/9j/4AAQSkZJRgABAQEASABIAAD/2wBD', 100000)]
    ]
];

$reflection = new ReflectionClass('SarprasController');
$methodHandlePhotos = $reflection->getMethod('handlePhotoUploads');
$methodHandlePhotos->setAccessible(true);

foreach ($testPayloads as $caseName => $payload) {
    echo "\n----------------------------------------\n";
    echo "Running Case: $caseName\n";
    $_POST = $payload;
    
    $namaBarang = trim($_POST['nama_barang'] ?? '');
    $golonganId = (int)($_POST['golongan_id'] ?? 0);
    $kelompokId = (int)($_POST['kelompok_id'] ?? 0);
    $asalAnggaranId = (int)($_POST['asal_anggaran_id'] ?? 0);
    $kategoriId = (int)($_POST['kategori_id'] ?? 1);
    $ruanganId = (int)($_POST['ruangan_id'] ?? 1);
    $unit = trim($_POST['unit'] ?? 'SD');
    $jumlah = max(1, (int)($_POST['jumlah'] ?? 1));
    $satuan = trim($_POST['satuan'] ?? 'Unit');
    $kondisi = trim($_POST['kondisi'] ?? 'Baik');
    $status = trim($_POST['status'] ?? 'Tersedia');
    $merkModel = trim($_POST['merk_model'] ?? '');
    $nomorSeri = trim($_POST['nomor_seri'] ?? '');
    $tanggalPerolehan = trim($_POST['tanggal_perolehan'] ?? date('Y-m-d'));
    $masaManfaat = (int)($_POST['masa_manfaat'] ?? 0);
    $hargaRaw = $_POST['harga_perolehan'] ?? '0';
    $harga = (float)$hargaRaw;
    $keterangan = trim($_POST['keterangan'] ?? '');

    try {
        $kodeBarang = SarprasModel::generateKodeBarangCustom($golonganId, $kelompokId, $asalAnggaranId);
        echo "Generated Kode: $kodeBarang\n";

        $fotoPath = $methodHandlePhotos->invoke(null, []);
        echo "Foto Path: " . substr((string)$fotoPath, 0, 100) . "\n";

        $insertedId = SarprasModel::insertBarang([
            'kode_barang' => $kodeBarang,
            'nama_barang' => $namaBarang,
            'golongan_id' => $golonganId,
            'kelompok_id' => $kelompokId,
            'asal_anggaran_id' => $asalAnggaranId,
            'kategori_id' => $kategoriId,
            'ruangan_id' => $ruanganId,
            'unit' => $unit,
            'merk_model' => $merkModel ?: null,
            'nomor_seri' => $nomorSeri ?: null,
            'jumlah' => $jumlah,
            'satuan' => $satuan,
            'kondisi' => $kondisi,
            'status' => $status,
            'tanggal_perolehan' => $tanggalPerolehan,
            'masa_manfaat' => $masaManfaat,
            'harga_perolehan' => $harga,
            'foto' => $fotoPath,
            'keterangan' => $keterangan ?: null
        ]);
        echo "SUCCESS! Inserted ID: $insertedId\n";
    } catch (Throwable $e) {
        echo "FAILED with Exception: " . $e->getMessage() . "\n";
        echo "Trace:\n" . $e->getTraceAsString() . "\n";
    }
}
