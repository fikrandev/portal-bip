<?php
/**
 * Portal BIP - Kartu Siswa Generator & Download Helper
 * 
 * Generates high-resolution ID card PNGs (650 x 1011 px / 54mm x 86mm standard)
 * Supports single PNG direct download and mass ZIP packaging.
 * Filename format: nisn-nama siswa.png
 */

require_once __DIR__ . '/phpqrcode.php';

class KartuHelper
{
    public const CARD_WIDTH = 650;
    public const CARD_HEIGHT = 1011;

    /**
     * Get clean filename for student card: nisn-nama siswa.png
     */
    public static function getStudentCardFilename(array $siswa, string $ext = 'png'): string
    {
        $nisn = trim((string)($siswa['nisn'] ?? ''));
        if ($nisn === '') {
            $nisn = trim((string)($siswa['nis'] ?? ''));
        }
        if ($nisn === '') {
            $nisn = trim((string)($siswa['id_siswa'] ?? ''));
        }

        $nama = trim((string)($siswa['nama_lengkap'] ?? ($siswa['nama'] ?? 'Siswa')));
        // Normalize name: title case or uppercase
        $cleanNama = preg_replace('/[\/\\\\:*?"<>|]/', '', $nama);
        $cleanNama = trim(preg_replace('/\s+/', ' ', $cleanNama));

        $cleanNisn = preg_replace('/[\/\\\\:*?"<>|]/', '', $nisn);
        $cleanNisn = trim(preg_replace('/\s+/', '', $cleanNisn));

        if ($cleanNisn !== '') {
            $filename = "{$cleanNisn}-{$cleanNama}";
        } else {
            $filename = $cleanNama;
        }

        if ($filename === '') {
            $filename = 'kartu_siswa_' . ($siswa['id'] ?? time());
        }

        return "{$filename}.{$ext}";
    }

    /**
     * Generate complete GD Image of Student Card
     */
    public static function generateCard(array $siswa): \GdImage
    {
        $w = self::CARD_WIDTH;
        $h = self::CARD_HEIGHT;

        // 1. Determine Jenjang
        $jenjang = strtolower(trim((string)($siswa['jenjang'] ?? 'sd')));
        if (!in_array($jenjang, ['paud', 'tk', 'sd', 'smp', 'sma'])) {
            $jenjang = 'sd';
        }
        if ($jenjang === 'tk') {
            $jenjang = 'paud';
        }

        // 2. Load Template Background
        $templatePath = BASE_PATH . '/public/uploads/templates/kartu/template_' . $jenjang . '.png';
        if (file_exists($templatePath)) {
            $card = @imagecreatefrompng($templatePath);
            if ($card) {
                // If template size differs from standard, resample to 650x1011
                $tw = imagesx($card);
                $th = imagesy($card);
                if ($tw !== $w || $th !== $h) {
                    $scaled = imagecreatetruecolor($w, $h);
                    imagealphablending($scaled, false);
                    imagesavealpha($scaled, true);
                    imagecopyresampled($scaled, $card, 0, 0, 0, 0, $w, $h, $tw, $th);
                    imagedestroy($card);
                    $card = $scaled;
                }
            }
        }

        if (!isset($card) || !$card) {
            // Elegant fallback template with school gradient
            $card = imagecreatetruecolor($w, $h);
            imagealphablending($card, true);
            imagesavealpha($card, true);

            // Background gradient
            for ($y = 0; $y < $h; $y++) {
                $factor = $y / $h;
                $r = (int)(219 * (1 - $factor) + 255 * $factor);
                $g = (int)(234 * (1 - $factor) + 255 * $factor);
                $b = (int)(254 * (1 - $factor) + 255 * $factor);
                $lineColor = imagecolorallocate($card, $r, $g, $b);
                imageline($card, 0, $y, $w, $y, $lineColor);
            }

            // Top Header Box
            $headerColor = imagecolorallocate($card, 14, 165, 233);
            imagefilledrectangle($card, 0, 0, $w, 200, $headerColor);

            $whiteColor = imagecolorallocate($card, 255, 255, 255);
            $fontBold = self::getFontPath(true);
            imagettftext($card, 24, 0, 160, 115, $whiteColor, $fontBold, 'KARTU SISWA');
            imagettftext($card, 14, 0, 190, 155, $whiteColor, $fontBold, strtoupper($jenjang) . ' BINA INSAN');
        }

        imagealphablending($card, true);
        imagesavealpha($card, true);

        // Common Colors
        $navyColor = imagecolorallocate($card, 30, 58, 138);  // #1e3a8a
        $blueColor = imagecolorallocate($card, 59, 130, 246);  // #3b82f6
        $whiteColor = imagecolorallocate($card, 255, 255, 255);
        $fontBold = self::getFontPath(true);
        $fontRegular = self::getFontPath(false);

        // 3. Render Student Photo
        $photoW = 288;
        $photoH = 288;
        $photoX = (int)(($w - $photoW) / 2);
        $photoY = 328;
        $photoRadius = 36;

        $photoImg = self::loadStudentPhoto($siswa);
        if ($photoImg) {
            $roundedPhoto = self::createRoundedPhoto($photoImg, $photoW, $photoH, $photoRadius);
            imagecopy($card, $roundedPhoto, $photoX, $photoY, 0, 0, $photoW, $photoH);
            imagedestroy($roundedPhoto);
            imagedestroy($photoImg);
        } else {
            // Placeholder Photo
            $placeholder = self::createPlaceholderPhoto($photoW, $photoH, $photoRadius, $fontRegular);
            imagecopy($card, $placeholder, $photoX, $photoY, 0, 0, $photoW, $photoH);
            imagedestroy($placeholder);
        }

        // 4. Render Student Name
        $namaLengkap = strtoupper(trim((string)($siswa['nama_lengkap'] ?? ($siswa['nama'] ?? 'NAMA SISWA'))));
        $maxTextW = 560; // Safe width within card margins
        $namaY = 660;

        // Auto wrap or scale font size for student name
        $fontSize = 22;
        $bbox = imagettfbbox($fontSize, 0, $fontBold, $namaLengkap);
        $textW = abs($bbox[2] - $bbox[0]);

        if ($textW > $maxTextW) {
            // Try wrapping into 2 lines
            $words = explode(' ', $namaLengkap);
            if (count($words) > 1) {
                $mid = (int)ceil(count($words) / 2);
                $line1 = implode(' ', array_slice($words, 0, $mid));
                $line2 = implode(' ', array_slice($words, $mid));

                // Scale down slightly if needed
                $fontSize = 19;
                $bbox1 = imagettfbbox($fontSize, 0, $fontBold, $line1);
                $bbox2 = imagettfbbox($fontSize, 0, $fontBold, $line2);
                $textW1 = abs($bbox1[2] - $bbox1[0]);
                $textW2 = abs($bbox2[2] - $bbox2[0]);

                $x1 = (int)(($w - $textW1) / 2);
                $x2 = (int)(($w - $textW2) / 2);

                imagettftext($card, $fontSize, 0, $x1, 642, $navyColor, $fontBold, $line1);
                imagettftext($card, $fontSize, 0, $x2, 672, $navyColor, $fontBold, $line2);
                $namaY = 672; // Update baseline for NISN
            } else {
                // Single long word: scale down
                while ($textW > $maxTextW && $fontSize > 13) {
                    $fontSize -= 1;
                    $bbox = imagettfbbox($fontSize, 0, $fontBold, $namaLengkap);
                    $textW = abs($bbox[2] - $bbox[0]);
                }
                $x = (int)(($w - $textW) / 2);
                imagettftext($card, $fontSize, 0, $x, $namaY, $navyColor, $fontBold, $namaLengkap);
            }
        } else {
            $x = (int)(($w - $textW) / 2);
            imagettftext($card, $fontSize, 0, $x, $namaY, $navyColor, $fontBold, $namaLengkap);
        }

        // 5. Render NISN
        $nisnVal = trim((string)($siswa['nisn'] ?? ''));
        if ($nisnVal !== '') {
            $nisnText = "(NISN : {$nisnVal})";
        } elseif (!empty($siswa['nis'])) {
            $nisnText = "(NIS : {$siswa['nis']})";
        } else {
            $nisnText = "(NISN : -)";
        }

        $fontSizeNisn = 15;
        $bboxNisn = imagettfbbox($fontSizeNisn, 0, $fontRegular, $nisnText);
        $nisnW = abs($bboxNisn[2] - $bboxNisn[0]);
        $nisnX = (int)(($w - $nisnW) / 2);
        $nisnY = $namaY + 38;
        imagettftext($card, $fontSizeNisn, 0, $nisnX, $nisnY, $blueColor, $fontRegular, $nisnText);

        // 6. Render QR Code
        $identifier = !empty($siswa['nisn']) ? $siswa['nisn'] : (!empty($siswa['nis']) ? $siswa['nis'] : ($siswa['id_siswa'] ?? ($siswa['id'] ?? '1')));
        $qrUrl = function_exists('url') ? url('validasi-kartu/' . $identifier) : ((defined('BASE_URL') ? BASE_URL : 'http://localhost') . '/validasi-kartu/' . $identifier);
        $qrSize = 220;
        $qrX = (int)(($w - $qrSize) / 2);
        $qrY = 742;

        self::drawQrCode($card, $qrUrl, $qrX, $qrY, $qrSize);

        return $card;
    }

    /**
     * Download single card directly as PNG
     */
    public static function downloadSingle(array $siswa): void
    {
        $card = self::generateCard($siswa);
        $filename = self::getStudentCardFilename($siswa, 'png');

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        imagepng($card, null, 8);
        imagedestroy($card);
        exit;
    }

    /**
     * Download mass cards as a ZIP containing individual PNG files
     */
    public static function downloadZip(array $siswaList, string $zipFilename = 'kartu_siswa.zip'): void
    {
        if (empty($siswaList)) {
            Response::withError(url('kelola-siswa/foto'), 'Tidak ada data siswa untuk diunduh.');
            return;
        }

        $tempZipDir = BASE_PATH . '/scratch';
        if (!is_dir($tempZipDir)) {
            mkdir($tempZipDir, 0777, true);
        }

        $tempZipFile = $tempZipDir . '/kartu_batch_' . uniqid() . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($tempZipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Response::withError(url('kelola-siswa/foto'), 'Gagal membuat arsip ZIP.');
            return;
        }

        $usedFilenames = [];

        foreach ($siswaList as $siswa) {
            $card = self::generateCard($siswa);

            // Buffer PNG binary data
            ob_start();
            imagepng($card, null, 8);
            $pngData = ob_get_clean();
            imagedestroy($card);

            $baseFilename = self::getStudentCardFilename($siswa, 'png');

            // Prevent duplicate file names inside ZIP
            if (isset($usedFilenames[$baseFilename])) {
                $usedFilenames[$baseFilename]++;
                $extPos = strrpos($baseFilename, '.');
                $namePart = substr($baseFilename, 0, $extPos);
                $finalFilename = "{$namePart}_(" . $usedFilenames[$baseFilename] . ").png";
            } else {
                $usedFilenames[$baseFilename] = 1;
                $finalFilename = $baseFilename;
            }

            $zip->addFromString($finalFilename, $pngData);
        }

        $zip->close();

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($tempZipFile));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($tempZipFile);
        @unlink($tempZipFile);
        exit;
    }

    /**
     * Load photo image resource if available
     */
    private static function loadStudentPhoto(array $siswa): ?\GdImage
    {
        $identifiers = array_filter([
            $siswa['nisn'] ?? null,
            $siswa['id_siswa'] ?? null,
            $siswa['nis'] ?? null,
            $siswa['foto'] ?? null,
            $siswa['id'] ?? null
        ]);

        $uploadDir = BASE_PATH . '/public/uploads/siswa/';
        $extensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($identifiers as $id) {
            // Strip any path traversal
            $cleanId = basename($id);
            foreach ($extensions as $ext) {
                $candidate = $uploadDir . $cleanId;
                if (!str_ends_with(strtolower($candidate), '.' . $ext)) {
                    $candidate .= '.' . $ext;
                }
                if (file_exists($candidate) && is_file($candidate)) {
                    $img = @imagecreatefromstring(file_get_contents($candidate));
                    if ($img) {
                        return $img;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Create rounded photo with aspect-fill cropping
     */
    private static function createRoundedPhoto(\GdImage $srcImg, int $targetW, int $targetH, int $radius): \GdImage
    {
        $sw = imagesx($srcImg);
        $sh = imagesy($srcImg);

        // Aspect Fill calculation
        $srcRatio = $sw / $sh;
        $targetRatio = $targetW / $targetH;

        if ($srcRatio > $targetRatio) {
            // Source is wider: crop sides
            $cropH = $sh;
            $cropW = (int)($sh * $targetRatio);
            $cropX = (int)(($sw - $cropW) / 2);
            $cropY = 0;
        } else {
            // Source is taller: crop top/bottom
            $cropW = $sw;
            $cropH = (int)($sw / $targetRatio);
            $cropX = 0;
            $cropY = (int)(($sh - $cropH) / 4); // Keep more top for face
        }

        $resampled = imagecreatetruecolor($targetW, $targetH);
        imagecopyresampled($resampled, $srcImg, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

        // Create rounded canvas with alpha
        $canvas = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        // Copy pixels within rounded rectangle mask
        for ($x = 0; $x < $targetW; $x++) {
            for ($y = 0; $y < $targetH; $y++) {
                if (self::isInsideRoundedRect($x, $y, $targetW, $targetH, $radius)) {
                    $rgb = imagecolorat($resampled, $x, $y);
                    imagesetpixel($canvas, $x, $y, $rgb);
                }
            }
        }

        imagedestroy($resampled);
        return $canvas;
    }

    /**
     * Create sleek placeholder for students with no photo
     */
    private static function createPlaceholderPhoto(int $w, int $h, int $radius, string $font): \GdImage
    {
        $canvas = imagecreatetruecolor($w, $h);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        $bg = imagecolorallocate($canvas, 241, 245, 249);
        $iconColor = imagecolorallocate($canvas, 203, 213, 225);
        $textColor = imagecolorallocate($canvas, 148, 163, 184);

        // Fill rounded rect
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                if (self::isInsideRoundedRect($x, $y, $w, $h, $radius)) {
                    imagesetpixel($canvas, $x, $y, $bg);
                }
            }
        }

        // Draw avatar silhouette
        imagefilledellipse($canvas, (int)($w / 2), 105, 84, 84, $iconColor);
        imagefilledarc($canvas, (int)($w / 2), 245, 175, 155, 180, 360, $iconColor, IMG_ARC_PIE);

        // Text "Belum Ada Foto"
        $txt = "Belum Ada Foto";
        $bbox = imagettfbbox(11, 0, $font, $txt);
        $tw = abs($bbox[2] - $bbox[0]);
        $tx = (int)(($w - $tw) / 2);
        imagettftext($canvas, 11, 0, $tx, 272, $textColor, $font, $txt);

        return $canvas;
    }

    /**
     * Draw QR Code with clean white rounded frame
     */
    private static function drawQrCode(\GdImage $card, string $url, int $x, int $y, int $size): void
    {
        $scratchDir = BASE_PATH . '/scratch';
        if (!is_dir($scratchDir)) {
            mkdir($scratchDir, 0777, true);
        }

        $tempQrPath = $scratchDir . '/qr_' . md5($url . microtime()) . '.png';
        QRcode::png($url, $tempQrPath, QR_ECLEVEL_M, 6, 1);

        if (file_exists($tempQrPath)) {
            $qrRaw = @imagecreatefrompng($tempQrPath);
            if ($qrRaw) {
                $white = imagecolorallocate($card, 255, 255, 255);
                $pad = 6;
                // White background badge for QR
                imagefilledrectangle($card, $x - $pad, $y - $pad, $x + $size + $pad, $y + $size + $pad, $white);
                imagecopyresampled($card, $qrRaw, $x, $y, 0, 0, $size, $size, imagesx($qrRaw), imagesy($qrRaw));
                imagedestroy($qrRaw);
            }
            @unlink($tempQrPath);
        }
    }

    /**
     * Check if (x, y) is inside rounded rectangle
     */
    private static function isInsideRoundedRect(int $x, int $y, int $w, int $h, int $r): bool
    {
        if ($x < $r && $y < $r) {
            return (($x - $r) ** 2 + ($y - $r) ** 2) <= ($r ** 2);
        }
        if ($x > ($w - $r) && $y < $r) {
            return (($x - ($w - $r)) ** 2 + ($y - $r) ** 2) <= ($r ** 2);
        }
        if ($x < $r && $y > ($h - $r)) {
            return (($x - $r) ** 2 + ($y - ($h - $r)) ** 2) <= ($r ** 2);
        }
        if ($x > ($w - $r) && $y > ($h - $r)) {
            return (($x - ($w - $r)) ** 2 + ($y - ($h - $r)) ** 2) <= ($r ** 2);
        }
        return true;
    }

    /**
     * Resolve font path with reliable fallbacks
     */
    private static function getFontPath(bool $bold = false): string
    {
        $filename = $bold ? 'DejaVuSans-Bold.ttf' : 'DejaVuSans.ttf';
        $bundled = BASE_PATH . '/public/fonts/' . $filename;
        if (file_exists($bundled)) {
            return $bundled;
        }

        // Fallback to Windows Fonts
        $winBold = 'C:/Windows/Fonts/arialbd.ttf';
        $winRegular = 'C:/Windows/Fonts/arial.ttf';
        if ($bold && file_exists($winBold)) {
            return $winBold;
        }
        if (file_exists($winRegular)) {
            return $winRegular;
        }

        return $bundled;
    }
}
