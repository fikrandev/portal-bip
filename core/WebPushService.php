<?php
/**
 * WebPushService — Pure PHP Web Push Engine (RFC 8291 aes128gcm + RFC 8292 VAPID ES256)
 *
 * Fitur:
 * - Zero external Composer dependency (menggunakan ekstensi bawaan PHP: openssl + curl)
 * - Auto-generate & simpan pasangan kunci VAPID (EC prime256v1) di tabel `settings`
 * - Mengelola langganan push per perangkat (user_id / pegawai_id) di `web_push_subscriptions`
 * - Enkripsi payload standar RFC 8291 (`aes128gcm`) agar notifikasi muncul realtime
 *   dengan suara default bawaan masing-masing perangkat (Android, iOS PWA 16.4+, Desktop)
 * - Pencatatan & deduplikasi pengiriman di `web_push_logs`
 */
class WebPushService
{
    private static bool $tablesEnsured = false;

    public static function ensureTablesExist(): void
    {
        if (self::$tablesEnsured) {
            return;
        }

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `web_push_subscriptions` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` INT(11) DEFAULT NULL,
                    `pegawai_id` INT(11) DEFAULT NULL,
                    `endpoint` VARCHAR(512) NOT NULL,
                    `endpoint_hash` CHAR(64) NOT NULL,
                    `p256dh` VARCHAR(255) NOT NULL,
                    `auth_token` VARCHAR(128) NOT NULL,
                    `user_agent` VARCHAR(255) DEFAULT NULL,
                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `last_sent_at` DATETIME DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_push_endpoint_hash` (`endpoint_hash`),
                    KEY `idx_push_user` (`user_id`, `is_active`),
                    KEY `idx_push_pegawai` (`pegawai_id`, `is_active`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `web_push_logs` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `pegawai_id` INT(11) DEFAULT NULL,
                    `user_id` INT(11) DEFAULT NULL,
                    `topic` VARCHAR(60) NOT NULL DEFAULT 'jurnal_staff_reminder',
                    `tanggal` DATE NOT NULL,
                    `slot_waktu` VARCHAR(15) NOT NULL DEFAULT '',
                    `title` VARCHAR(180) NOT NULL,
                    `body` TEXT NOT NULL,
                    `url` VARCHAR(255) DEFAULT NULL,
                    `status` ENUM('success', 'failed') NOT NULL DEFAULT 'success',
                    `http_code` INT(11) DEFAULT NULL,
                    `error_message` TEXT DEFAULT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_push_dedup` (`topic`, `pegawai_id`, `tanggal`, `slot_waktu`, `status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            self::$tablesEnsured = true;
        } catch (\Throwable $e) {
            error_log('WebPushService ensureTablesExist error: ' . $e->getMessage());
        }
    }

    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        $norm = strtr(trim($data), '-_', '+/');
        $pad = strlen($norm) % 4;
        if ($pad > 0) {
            $norm .= str_repeat('=', 4 - $pad);
        }
        $decoded = base64_decode($norm, true);
        return $decoded === false ? '' : $decoded;
    }

    /**
     * Buat pasangan kunci EC prime256v1 dengan fallback openssl.cnf untuk Windows XAMPP
     */
    public static function generateEcKeyPair(): array
    {
        $cfg = [
            'curve_name'       => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ];

        $res = @openssl_pkey_new($cfg);
        if (!$res) {
            $candidates = [
                getenv('OPENSSL_CONF') ?: '',
                'E:/xampp/apache/conf/openssl.cnf',
                'E:/xampp/php/extras/ssl/openssl.cnf',
                'C:/xampp/apache/conf/openssl.cnf',
                'C:/xampp/php/extras/ssl/openssl.cnf',
                '/etc/ssl/openssl.cnf',
            ];
            foreach ($candidates as $cnf) {
                if ($cnf !== '' && is_file($cnf)) {
                    $cfg['config'] = $cnf;
                    $res = @openssl_pkey_new($cfg);
                    if ($res) {
                        break;
                    }
                }
            }
        }

        if (!$res) {
            throw new \RuntimeException('Gagal membuat kunci EC P-256 (OpenSSL): ' . (openssl_error_string() ?: 'unknown'));
        }

        $details = openssl_pkey_get_details($res);
        if (!is_array($details) || empty($details['ec']['x']) || empty($details['ec']['y'])) {
            throw new \RuntimeException('Detail kunci EC P-256 tidak lengkap.');
        }

        $x = str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT);
        $y = str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT);
        $rawPublic = "\x04" . $x . $y;

        $privatePem = '';
        $exported = @openssl_pkey_export($res, $privatePem, null, $cfg);
        if (!$exported || $privatePem === '') {
            throw new \RuntimeException('Gagal mengekspor private key EC P-256.');
        }

        return [
            'resource'    => $res,
            'raw_public'  => $rawPublic,
            'public_b64'  => self::base64UrlEncode($rawPublic),
            'private_pem' => $privatePem,
        ];
    }

    /**
     * Ambil atau auto-generate pasangan kunci VAPID dari tabel settings
     */
    public static function getVapidKeys(): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $rows = $db->findAll(
            "SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('web_push_vapid_public', 'web_push_vapid_private_pem', 'web_push_vapid_subject')"
        );
        $map = [];
        foreach ($rows as $r) {
            $map[$r['setting_key']] = (string)($r['setting_value'] ?? '');
        }

        $pub = trim($map['web_push_vapid_public'] ?? '');
        $priv = trim($map['web_push_vapid_private_pem'] ?? '');
        $sub = trim($map['web_push_vapid_subject'] ?? '');

        if ($sub === '') {
            $host = 'binainsanpalu.sch.id';
            if (defined('BASE_URL')) {
                $parsedHost = parse_url((string)BASE_URL, PHP_URL_HOST);
                if (!empty($parsedHost) && $parsedHost !== 'localhost' && !filter_var($parsedHost, FILTER_VALIDATE_IP)) {
                    $host = $parsedHost;
                }
            }
            $sub = 'mailto:admin@' . $host;
            self::upsertSetting($db, 'web_push_vapid_subject', $sub);
        }

        if ($pub === '' || $priv === '') {
            $pair = self::generateEcKeyPair();
            $pub = $pair['public_b64'];
            $priv = $pair['private_pem'];
            self::upsertSetting($db, 'web_push_vapid_public', $pub);
            self::upsertSetting($db, 'web_push_vapid_private_pem', $priv);
        }

        return [
            'public_key'  => $pub,
            'private_pem' => $priv,
            'subject'     => $sub,
        ];
    }

    public static function getPublicKey(): string
    {
        $vapid = self::getVapidKeys();
        return $vapid['public_key'];
    }

    private static function upsertSetting(Database $db, string $key, string $val): void
    {
        $rows = $db->findAll("SELECT id FROM settings WHERE setting_key = ? ORDER BY id ASC", [$key]);
        if (!empty($rows)) {
            $primaryId = (int)$rows[0]['id'];
            if (count($rows) > 1) {
                $db->query("DELETE FROM settings WHERE setting_key = ? AND id != ?", [$key, $primaryId]);
            }
            $db->query("UPDATE settings SET setting_value = ? WHERE id = ?", [$val, $primaryId]);
        } else {
            $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $val]);
        }
    }

    /**
     * Konversi tanda tangan ASN.1 DER ECDSA menjadi raw 64-byte (r || s) untuk JWT ES256
     */
    public static function derSignatureToRaw(string $der): string
    {
        $len = strlen($der);
        if ($len < 8 || ord($der[0]) !== 0x30) {
            throw new \RuntimeException('Format DER ECDSA tidak valid.');
        }

        $offset = 2;
        if ((ord($der[1]) & 0x80) !== 0) {
            $lenBytes = ord($der[1]) & 0x7f;
            $offset += $lenBytes;
        }

        if ($offset >= $len || ord($der[$offset]) !== 0x02) {
            throw new \RuntimeException('Integer R tidak ditemukan pada DER ECDSA.');
        }
        $rLen = ord($der[$offset + 1]);
        $r = substr($der, $offset + 2, $rLen);
        $offset += 2 + $rLen;

        if ($offset >= $len || ord($der[$offset]) !== 0x02) {
            throw new \RuntimeException('Integer S tidak ditemukan pada DER ECDSA.');
        }
        $sLen = ord($der[$offset + 1]);
        $s = substr($der, $offset + 2, $sLen);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");

        if (strlen($r) > 32 || strlen($s) > 32) {
            throw new \RuntimeException('Panjang komponen R/S ECDSA melebihi 32 byte.');
        }

        return str_pad($r, 32, "\x00", STR_PAD_LEFT) . str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }

    /**
     * Bungkus raw 65-byte uncompressed EC P-256 public key ke format PEM SubjectPublicKeyInfo
     */
    public static function rawPublicKeyToPem(string $raw65): string
    {
        if (strlen($raw65) !== 65 || ord($raw65[0]) !== 0x04) {
            throw new \RuntimeException('Public key P-256 harus 65 byte uncompressed.');
        }
        $derPrefix = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200');
        $spki = $derPrefix . $raw65;
        return "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($spki), 64, "\n")
            . "-----END PUBLIC KEY-----\n";
    }

    /**
     * Buat header Authorization VAPID (RFC 8292) untuk endpoint push
     */
    public static function createVapidAuthHeader(string $endpoint, array $vapid): string
    {
        $parts = parse_url($endpoint);
        if (empty($parts['scheme']) || empty($parts['host'])) {
            throw new \RuntimeException('Endpoint Web Push tidak valid.');
        }
        $aud = $parts['scheme'] . '://' . $parts['host'] . (!empty($parts['port']) ? ':' . $parts['port'] : '');

        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims = self::base64UrlEncode(json_encode([
            'aud' => $aud,
            'exp' => time() + 43200,
            'sub' => $vapid['subject'] ?? 'mailto:admin@binainsanpalu.sch.id',
        ], JSON_UNESCAPED_SLASHES));

        $signingInput = $header . '.' . $claims;
        $privKey = openssl_pkey_get_private($vapid['private_pem']);
        if (!$privKey) {
            throw new \RuntimeException('Gagal memuat VAPID private key.');
        }

        $derSig = '';
        if (!openssl_sign($signingInput, $derSig, $privKey, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Gagal menandatangani JWT VAPID.');
        }

        $rawSig = self::derSignatureToRaw($derSig);
        $jwt = $signingInput . '.' . self::base64UrlEncode($rawSig);

        return 'vapid t=' . $jwt . ', k=' . $vapid['public_key'];
    }

    /**
     * Enkripsi payload Web Push sesuai standar RFC 8291 (Content-Encoding: aes128gcm)
     */
    public static function encryptPayload(string $plaintext, string $userPubB64, string $userAuthB64): string
    {
        $uaPublic = self::base64UrlDecode($userPubB64);
        $authSecret = self::base64UrlDecode($userAuthB64);

        if (strlen($uaPublic) !== 65 || ord($uaPublic[0]) !== 0x04) {
            throw new \RuntimeException('Kunci p256dh subscriber tidak valid.');
        }
        if (strlen($authSecret) !== 16) {
            throw new \RuntimeException('Kunci auth subscriber tidak valid.');
        }

        $localPair = self::generateEcKeyPair();
        $asPublic = $localPair['raw_public'];

        $uaPubPem = self::rawPublicKeyToPem($uaPublic);
        $sharedSecret = @openssl_pkey_derive($uaPubPem, $localPair['resource'], 32);
        if (!is_string($sharedSecret) || strlen($sharedSecret) !== 32) {
            throw new \RuntimeException('Gagal menghitung ECDH shared secret.');
        }

        // RFC 8291 Section 3.3: IKM derivation
        $prkKey = hash_hmac('sha256', $sharedSecret, $authSecret, true);
        $keyInfo = "WebPush: info\x00" . $uaPublic . $asPublic . "\x01";
        $ikm = hash_hmac('sha256', $keyInfo, $prkKey, true);

        // RFC 8188 Section 2.2: CEK (16 bytes) & Nonce (12 bytes)
        $salt = random_bytes(16);
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        $cek = substr(hash_hmac('sha256', "Content-Encoding: aes128gcm\x00\x01", $prk, true), 0, 16);
        $nonce = substr(hash_hmac('sha256', "Content-Encoding: nonce\x00\x01", $prk, true), 0, 12);

        // Encrypt plaintext + final record delimiter 0x02
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
        if ($ciphertext === false || strlen($tag) !== 16) {
            throw new \RuntimeException('Gagal mengenkripsi payload AES-128-GCM.');
        }

        // RFC 8188 header: salt(16) || rs(4) || idlen(1=65) || keyid(65) || ciphertext || tag(16)
        return $salt . pack('N', 4096) . chr(65) . $asPublic . $ciphertext . $tag;
    }

    /**
     * Simpan atau perbarui subscription push perangkat user
     */
    public static function subscribe(
        ?int $userId,
        ?int $pegawaiId,
        string $endpoint,
        string $p256dh,
        string $authToken,
        string $userAgent = ''
    ): array {
        self::ensureTablesExist();

        $endpoint = trim($endpoint);
        $p256dh = trim($p256dh);
        $authToken = trim($authToken);
        if ($endpoint === '' || $p256dh === '' || $authToken === '') {
            return ['success' => false, 'message' => 'Data langganan push tidak lengkap.'];
        }
        if (!preg_match('#^https://#i', $endpoint)) {
            return ['success' => false, 'message' => 'Endpoint push harus menggunakan HTTPS.'];
        }

        $hash = hash('sha256', $endpoint);
        $ua = substr(trim($userAgent), 0, 250);
        $db = Database::getInstance();

        $existing = $db->find("SELECT id FROM web_push_subscriptions WHERE endpoint_hash = ? LIMIT 1", [$hash]);
        if ($existing) {
            $db->query("
                UPDATE web_push_subscriptions
                SET user_id = ?, pegawai_id = ?, endpoint = ?, p256dh = ?, auth_token = ?, user_agent = ?, is_active = 1, updated_at = NOW()
                WHERE id = ?
            ", [$userId ?: null, $pegawaiId ?: null, $endpoint, $p256dh, $authToken, $ua, (int)$existing['id']]);
        } else {
            $db->query("
                INSERT INTO web_push_subscriptions (user_id, pegawai_id, endpoint, endpoint_hash, p256dh, auth_token, user_agent, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ", [$userId ?: null, $pegawaiId ?: null, $endpoint, $hash, $p256dh, $authToken, $ua]);
        }

        return ['success' => true, 'message' => 'Push Notification berhasil diaktifkan pada perangkat ini.'];
    }

    /**
     * Nonaktifkan langganan push perangkat
     */
    public static function unsubscribe(string $endpoint, ?int $userId = null): bool
    {
        self::ensureTablesExist();
        $endpoint = trim($endpoint);
        $db = Database::getInstance();

        if ($endpoint !== '') {
            $hash = hash('sha256', $endpoint);
            $db->query("UPDATE web_push_subscriptions SET is_active = 0, updated_at = NOW() WHERE endpoint_hash = ?", [$hash]);
            return true;
        }
        if ($userId && $userId > 0) {
            $db->query("UPDATE web_push_subscriptions SET is_active = 0, updated_at = NOW() WHERE user_id = ?", [$userId]);
            return true;
        }
        return false;
    }

    /**
     * Cek apakah user/pegawai memiliki langganan push aktif
     */
    public static function hasActiveSubscription(?int $userId, ?int $pegawaiId): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $conds = [];
        $params = [];
        if ($userId && $userId > 0) {
            $conds[] = 'user_id = ?';
            $params[] = $userId;
        }
        if ($pegawaiId && $pegawaiId > 0) {
            $conds[] = 'pegawai_id = ?';
            $params[] = $pegawaiId;
        }
        if (empty($conds)) {
            return false;
        }

        $row = $db->find(
            "SELECT 1 FROM web_push_subscriptions WHERE is_active = 1 AND (" . implode(' OR ', $conds) . ") LIMIT 1",
            $params
        );
        return (bool)$row;
    }

    /**
     * Cek apakah notifikasi push untuk topic + pegawai + tanggal + slot sudah berhasil dikirim
     */
    public static function wasAlreadySent(string $topic, int $pegawaiId, string $tanggal, string $slotWaktu): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $row = $db->find(
            "SELECT 1 FROM web_push_logs WHERE topic = ? AND pegawai_id = ? AND tanggal = ? AND slot_waktu = ? AND status = 'success' LIMIT 1",
            [$topic, $pegawaiId, $tanggal, $slotWaktu]
        );
        return (bool)$row;
    }

    /**
     * Kirim satu push notification mentah ke 1 subscription endpoint
     */
    public static function sendRawPush(array $sub, array $payload): array
    {
        $endpoint = trim((string)($sub['endpoint'] ?? ''));
        $p256dh = trim((string)($sub['p256dh'] ?? ''));
        $auth = trim((string)($sub['auth_token'] ?? ''));

        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            return ['success' => false, 'http_code' => 0, 'message' => 'Subscription tidak valid.'];
        }

        try {
            $vapid = self::getVapidKeys();
            $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $encrypted = self::encryptPayload($jsonBody, $p256dh, $auth);
            $authHeader = self::createVapidAuthHeader($endpoint, $vapid);

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $encrypted,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 12,
                CURLOPT_CONNECTTIMEOUT => 6,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/octet-stream',
                    'Content-Encoding: aes128gcm',
                    'Content-Length: ' . strlen($encrypted),
                    'TTL: 86400',
                    'Urgency: high',
                    'Authorization: ' . $authHeader,
                ],
            ]);

            $resp = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($resp === false) {
                return ['success' => false, 'http_code' => $httpCode, 'message' => 'cURL error: ' . $curlErr];
            }

            $db = Database::getInstance();
            if ($httpCode >= 200 && $httpCode < 300) {
                if (!empty($sub['id'])) {
                    $db->query("UPDATE web_push_subscriptions SET last_sent_at = NOW() WHERE id = ?", [(int)$sub['id']]);
                }
                return ['success' => true, 'http_code' => $httpCode, 'message' => 'Terkirim'];
            }

            // 404 / 410 = subscription sudah expired/dicabut oleh browser -> nonaktifkan otomatis
            if ($httpCode === 404 || $httpCode === 410) {
                if (!empty($sub['id'])) {
                    $db->query("UPDATE web_push_subscriptions SET is_active = 0, updated_at = NOW() WHERE id = ?", [(int)$sub['id']]);
                }
                return ['success' => false, 'http_code' => $httpCode, 'expired' => true, 'message' => 'Subscription perangkat sudah tidak berlaku (HTTP ' . $httpCode . ').'];
            }

            return [
                'success'   => false,
                'http_code' => $httpCode,
                'message'   => 'Push server merespon HTTP ' . $httpCode . (trim((string)$resp) !== '' ? ': ' . substr(trim((string)$resp), 0, 180) : ''),
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'http_code' => 0, 'message' => $e->getMessage()];
        }
    }

    /**
     * Kirim push notification ke seluruh perangkat aktif milik pegawai_id tertentu
     */
    public static function sendToPegawai(int $pegawaiId, array $payload, array $meta = []): array
    {
        self::ensureTablesExist();
        if ($pegawaiId <= 0) {
            return ['success' => false, 'sent' => 0, 'failed' => 0, 'message' => 'pegawai_id tidak valid.'];
        }

        $db = Database::getInstance();
        $subs = $db->findAll(
            "SELECT * FROM web_push_subscriptions WHERE is_active = 1 AND pegawai_id = ?",
            [$pegawaiId]
        );

        // Fallback cari user_id berdasarkan email/nama pegawai jika subscription belum sempat menyimpan pegawai_id
        if (empty($subs)) {
            try {
                $peg = $db->find("SELECT email, nama FROM pegawai WHERE id = ? LIMIT 1", [$pegawaiId]);
                if ($peg) {
                    $matchedUserIds = [];
                    if (!empty($peg['email'])) {
                        $uRows = $db->findAll("SELECT id FROM users WHERE LOWER(email) = LOWER(?)", [trim((string)$peg['email'])]);
                        foreach ($uRows as $ur) $matchedUserIds[] = (int)$ur['id'];
                    }
                    if (empty($matchedUserIds) && !empty($peg['nama'])) {
                        $uRows = $db->findAll("SELECT id FROM users WHERE LOWER(full_name) = LOWER(?)", [trim((string)$peg['nama'])]);
                        foreach ($uRows as $ur) $matchedUserIds[] = (int)$ur['id'];
                    }
                    if (!empty($matchedUserIds)) {
                        $ph = implode(',', array_fill(0, count($matchedUserIds), '?'));
                        $subs = $db->findAll(
                            "SELECT * FROM web_push_subscriptions WHERE is_active = 1 AND user_id IN ({$ph})",
                            $matchedUserIds
                        );
                    }
                }
            } catch (\Throwable $e) {}
        }

        return self::dispatchToSubscriptions($subs, $payload, array_merge(['pegawai_id' => $pegawaiId], $meta));
    }

    /**
     * Kirim push notification ke seluruh perangkat aktif milik user_id / pegawai_id (misal tes notifikasi)
     */
    public static function sendToUser(int $userId, ?int $pegawaiId, array $payload, array $meta = []): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $conds = [];
        $params = [];
        if ($userId > 0) {
            $conds[] = 'user_id = ?';
            $params[] = $userId;
        }
        if ($pegawaiId && $pegawaiId > 0) {
            $conds[] = 'pegawai_id = ?';
            $params[] = $pegawaiId;
        }
        if (empty($conds)) {
            return ['success' => false, 'sent' => 0, 'failed' => 0, 'message' => 'Identitas pengguna tidak ditemukan.'];
        }

        $subs = $db->findAll(
            "SELECT * FROM web_push_subscriptions WHERE is_active = 1 AND (" . implode(' OR ', $conds) . ")",
            $params
        );

        return self::dispatchToSubscriptions($subs, $payload, array_merge([
            'user_id'    => $userId ?: null,
            'pegawai_id' => $pegawaiId ?: null,
        ], $meta));
    }

    private static function dispatchToSubscriptions(array $subs, array $payload, array $meta): array
    {
        if (empty($subs)) {
            return ['success' => false, 'sent' => 0, 'failed' => 0, 'no_subscription' => true, 'message' => 'Belum ada perangkat aktif yang menyalakan Push Notification.'];
        }

        $sent = 0;
        $failed = 0;
        $lastCode = 0;
        $lastErr = '';

        foreach ($subs as $sub) {
            $res = self::sendRawPush($sub, $payload);
            $lastCode = (int)($res['http_code'] ?? 0);
            if (!empty($res['success'])) {
                $sent++;
            } else {
                $failed++;
                $lastErr = (string)($res['message'] ?? 'Gagal kirim push');
            }
        }

        $ok = ($sent > 0);
        try {
            $db = Database::getInstance();
            $db->insert('web_push_logs', [
                'pegawai_id'    => !empty($meta['pegawai_id']) ? (int)$meta['pegawai_id'] : null,
                'user_id'       => !empty($meta['user_id']) ? (int)$meta['user_id'] : null,
                'topic'         => substr((string)($meta['topic'] ?? 'general'), 0, 60),
                'tanggal'       => (string)($meta['tanggal'] ?? date('Y-m-d')),
                'slot_waktu'    => substr((string)($meta['slot_waktu'] ?? ''), 0, 15),
                'title'         => substr((string)($payload['title'] ?? 'Portal BIP'), 0, 180),
                'body'          => (string)($payload['body'] ?? ''),
                'url'           => substr((string)($payload['url'] ?? '/mobile'), 0, 255),
                'status'        => $ok ? 'success' : 'failed',
                'http_code'     => $lastCode ?: null,
                'error_message' => $ok ? null : ($lastErr !== '' ? $lastErr : null),
            ]);
        } catch (\Throwable $e) {
            error_log('WebPushService log error: ' . $e->getMessage());
        }

        return [
            'success' => $ok,
            'sent'    => $sent,
            'failed'  => $failed,
            'message' => $ok
                ? "Push Notification terkirim ke {$sent} perangkat."
                : ('Gagal mengirim Push Notification: ' . ($lastErr ?: 'unknown error')),
        ];
    }
}
