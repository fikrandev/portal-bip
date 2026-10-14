<?php
/**
 * SignatureCrypto - Enkripsi & Keamanan Tanda Tangan Digital SPMB
 * Portal BIP
 *
 * Menggunakan Authenticated Encryption AES-256-CBC dengan HMAC-SHA256 (Encrypt-then-MAC)
 * untuk memastikan data tanda tangan digital tersimpan secara aman, rahasia,
 * dan tahan terhadap manipulasi/pemalsuan data.
 */

class SignatureCrypto
{
    /**
     * Dapatkan cryptographic secret key dari sistem
     */
    private static function getKey(): string
    {
        $dbPass = defined('DB_PASS') ? DB_PASS : '';
        $dbName = defined('DB_NAME') ? DB_NAME : 'portal_bip';
        $appKey = defined('APP_KEY') ? APP_KEY : 'portal_bip_spmb_secret_salt_2026';
        
        // Turunkan kunci 256-bit (32 bytes) menggunakan PBKDF2/SHA256
        return hash_pbkdf2('sha256', $dbPass . '|' . $dbName . '|' . $appKey, 'spmb_ttd_crypto_salt_998', 1000, 32, true);
    }

    /**
     * Enkripsi data URL tanda tangan dan metadata penandatangan
     *
     * @param string $signatureDataUrl Base64 PNG data URL dari Canvas
     * @param array $meta Metadata (nama, peran, pendaftar_id, no_pendaftaran, ip, dll)
     * @return string Encrypted Base64 string
     */
    public static function encrypt(string $signatureDataUrl, array $meta = []): string
    {
        $timestamp = date('c');
        $signatureHash = hash('sha256', $signatureDataUrl . '|' . ($meta['pendaftar_id'] ?? 0));

        $payload = [
            'signature_data_url' => $signatureDataUrl,
            'signer_role'        => $meta['role'] ?? 'Orang Tua / Wali',
            'signer_name'        => $meta['name'] ?? '',
            'pendaftar_id'       => $meta['pendaftar_id'] ?? 0,
            'nomor_pendaftaran'  => $meta['nomor_pendaftaran'] ?? '',
            'ip_address'         => $meta['ip_address'] ?? ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
            'user_agent'         => $meta['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'),
            'signed_at'          => $timestamp,
            'checksum_sha256'    => $signatureHash,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new Exception("Gagal mengonversi data tanda tangan ke JSON.");
        }

        $key = self::getKey();
        $iv = random_bytes(16); // 16 bytes IV for AES-256-CBC

        $ciphertext = openssl_encrypt($json, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new Exception("Enkripsi OpenSSL gagal: " . openssl_error_string());
        }

        // HMAC-SHA256 untuk integritas (Encrypt-then-MAC)
        $hmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);

        // Paket: 16 bytes IV + 32 bytes HMAC + Ciphertext
        return base64_encode($iv . $hmac . $ciphertext);
    }

    /**
     * Dekripsi tanda tangan dan verifikasi keasliannya
     *
     * @param string|null $encryptedPayload String terenkripsi dari database
     * @return array|null Decrypted payload array atau null jika tidak valid
     */
    public static function decrypt(?string $encryptedPayload): ?array
    {
        if (empty($encryptedPayload)) {
            return null;
        }

        // Cek jika data masih dalam format plaintext Base64 data URL (fallback backward compatibility)
        if (str_starts_with($encryptedPayload, 'data:image/')) {
            return [
                'signature_data_url' => $encryptedPayload,
                'signer_role'        => 'Orang Tua',
                'signer_name'        => '-',
                'signed_at'          => null,
                'ip_address'         => '-',
                'checksum_sha256'    => hash('sha256', $encryptedPayload),
                'is_legacy'          => true,
                'is_verified'        => true,
            ];
        }

        $raw = base64_decode($encryptedPayload, true);
        if ($raw === false || strlen($raw) < 49) {
            return null;
        }

        $key = self::getKey();
        $iv = substr($raw, 0, 16);
        $hmac = substr($raw, 16, 32);
        $ciphertext = substr($raw, 48);

        // Verifikasi HMAC
        $expectedHmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);
        if (!hash_equals($expectedHmac, $hmac)) {
            error_log("[SignatureCrypto] HMAC verification failed! Tanda tangan mungkin telah dimanipulasi.");
            return null;
        }

        $decryptedJson = openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($decryptedJson === false) {
            return null;
        }

        $data = json_decode($decryptedJson, true);
        if (!is_array($data) || empty($data['signature_data_url'])) {
            return null;
        }

        $data['is_verified'] = true;
        $data['png_base64'] = $data['signature_data_url'];
        return $data;
    }

    /**
     * Periksa apakah string tanda tangan terenkripsi valid
     */
    public static function isValid(?string $encryptedPayload): bool
    {
        return self::decrypt($encryptedPayload) !== null;
    }
}
