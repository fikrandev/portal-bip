<?php
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/core/phpqrcode.php';

$width = 650;
$height = 1011;

// 1. Template
$templatePath = BASE_PATH . '/public/uploads/templates/kartu/template_sd.png';
if (file_exists($templatePath)) {
    $card = imagecreatefrompng($templatePath);
} else {
    $card = imagecreatetruecolor($width, $height);
    $bg = imagecolorallocate($card, 245, 247, 250);
    imagefilledrectangle($card, 0, 0, $width, $height, $bg);
}

// Enable alpha blending
imagealphablending($card, true);
imagesavealpha($card, true);

// Colors
$navyColor = imagecolorallocate($card, 30, 58, 138); // #1e3a8a
$blueColor = imagecolorallocate($card, 59, 130, 246); // #3b82f6
$grayColor = imagecolorallocate($card, 148, 163, 184); // #94a3b8
$whiteColor = imagecolorallocate($card, 255, 255, 255);
$borderColor = imagecolorallocate($card, 226, 232, 240);

// Fonts
$fontBold = BASE_PATH . '/public/fonts/DejaVuSans-Bold.ttf';
$fontRegular = BASE_PATH . '/public/fonts/DejaVuSans.ttf';

// 2. Foto Container: 288x288 px at X=181, Y=330
$photoX = 181;
$photoY = 330;
$photoW = 288;
$photoH = 288;
$photoRadius = 36;

// Create rounded photo canvas
$photoCanvas = imagecreatetruecolor($photoW, $photoH);
$trans = imagecolorallocatealpha($photoCanvas, 0, 0, 0, 127);
imagefill($photoCanvas, 0, 0, $trans);
imagesavealpha($photoCanvas, true);

// Draw placeholder or sample image
$bgPlaceholder = imagecolorallocate($photoCanvas, 241, 245, 249);
imagefilledrectangle($photoCanvas, 0, 0, $photoW, $photoH, $bgPlaceholder);

// Silhouette in placeholder
$userIconColor = imagecolorallocate($photoCanvas, 203, 213, 225);
imagefilledellipse($photoCanvas, 144, 110, 80, 80, $userIconColor);
imagefilledarc($photoCanvas, 144, 250, 170, 160, 180, 360, $userIconColor, IMG_ARC_PIE);

// Copy photo to card with rounded mask
imagecopy($card, $photoCanvas, $photoX, $photoY, 0, 0, $photoW, $photoH);
imagedestroy($photoCanvas);

// 3. Nama Siswa
$nama = "MUHAMMAD AL FATIH";
$fontSizeNama = 22; // pt
$bbox = imagettfbbox($fontSizeNama, 0, $fontBold, $nama);
$textW = abs($bbox[2] - $bbox[0]);
$textX = (int)(($width - $textW) / 2);
$namaY = 660;
imagettftext($card, $fontSizeNama, 0, $textX, $namaY, $navyColor, $fontBold, $nama);

// 4. NISN
$nisnText = "(NISN : 0123456789)";
$fontSizeNisn = 15;
$bboxNisn = imagettfbbox($fontSizeNisn, 0, $fontRegular, $nisnText);
$nisnW = abs($bboxNisn[2] - $bboxNisn[0]);
$nisnX = (int)(($width - $nisnW) / 2);
$nisnY = 705;
imagettftext($card, $fontSizeNisn, 0, $nisnX, $nisnY, $blueColor, $fontRegular, $nisnText);

// 5. QR Code
$qrSize = 220;
$qrX = (int)(($width - $qrSize) / 2);
$qrY = 740;

// Generate QR code to temp file
$tempQr = BASE_PATH . '/scratch/temp_sample_qr.png';
QRcode::png("http://localhost/portal-bip/validasi-kartu/0123456789", $tempQr, QR_ECLEVEL_M, 6, 1);

if (file_exists($tempQr)) {
    $qrImg = imagecreatefrompng($tempQr);
    $origQrW = imagesx($qrImg);
    $origQrH = imagesy($qrImg);
    
    // Draw white background card for QR
    imagefilledrectangle($card, $qrX - 4, $qrY - 4, $qrX + $qrSize + 4, $qrY + $qrSize + 4, $whiteColor);
    imagecopyresampled($card, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize, $origQrW, $origQrH);
    imagedestroy($qrImg);
    unlink($tempQr);
}

// Save test card
$outputFile = BASE_PATH . '/scratch/test_card_out.png';
imagepng($card, $outputFile, 9);
imagedestroy($card);

echo "Card created successfully at: " . $outputFile . "\n";
echo "Size: " . filesize($outputFile) . " bytes\n";
