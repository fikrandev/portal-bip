<?php
/**
 * WaGateway - Centralized WhatsApp Gateway Service
 * Portal BIP - Yayasan Bina Insan Palu
 * 
 * Server: WA Gateway eportal (Self-Hosted QR Scan Multi-Device)
 * Dapat digunakan oleh seluruh modul di Portal BIP untuk mengirim pesan WhatsApp otomatis
 * tanpa biaya API pihak ketiga.
 * 
 * Contoh Penggunaan di Modul Lain:
 *   WaGateway::send('08123456789', 'Pesan notifikasi...', ['module' => 'kelola-spmb']);
 *   WaGateway::sendBulk(['0812...', '0852...'], 'Pengumuman...');
 */

class WaGateway
{
    const LOCAL_SERVER_PORT = 8001;
    const LOCAL_SERVER_URL  = 'http://127.0.0.1:8001';

    /**
     * Ambil seluruh konfigurasi WA Gateway dari tabel settings
     */
    public static function getSettings(): array
    {
        $db = Database::getInstance();
        $rows = $db->findAll("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'wa_gateway_%'");
        
        $settings = [
            'wa_gateway_enabled'       => '1',
            'wa_gateway_server_name'   => 'WA Gateway eportal',
            'wa_gateway_provider'      => 'self_hosted',
            'wa_gateway_api_url'       => self::LOCAL_SERVER_URL,
            'wa_gateway_api_key'       => '',
            'wa_gateway_sender_number' => '',
            'wa_gateway_footer_note'   => 'Pesan otomatis dikirim melalui Portal BIP.'
        ];

        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }

        // Pastikan default provider self_hosted jika belum diatur
        if (empty($settings['wa_gateway_provider'])) {
            $settings['wa_gateway_provider'] = 'self_hosted';
        }

        return $settings;
    }

    /**
     * Cek apakah server gateway aktif
     */
    public static function isEnabled(): bool
    {
        $cfg = self::getSettings();
        $enabled = ($cfg['wa_gateway_enabled'] === '1' || $cfg['wa_gateway_enabled'] === 1);
        if (!$enabled) return false;

        $provider = strtolower(trim($cfg['wa_gateway_provider']));
        if ($provider === 'self_hosted' || $provider === 'baileys' || empty($provider)) {
            // Mode mandiri hanya butuh diaktifkan
            return true;
        }

        // Mode provider eksternal (Fonnte/Wablas) butuh API Key
        return !empty(trim($cfg['wa_gateway_api_key']));
    }

    /**
     * Nama Server Gateway (Default: WA Gateway eportal)
     */
    public static function getServerName(): string
    {
        $cfg = self::getSettings();
        return !empty($cfg['wa_gateway_server_name']) ? $cfg['wa_gateway_server_name'] : 'WA Gateway eportal';
    }

    /**
     * Normalisasi nomor telepon ke format standar WhatsApp internasional Indonesia (628xxx)
     */
    public static function formatPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (empty($clean)) {
            return '';
        }

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Kirim Pesan WhatsApp ke satu nomor tujuan
     * 
     * @param string $targetPhone Nomor WA tujuan (0812... / 62812...)
     * @param string $message Isi teks pesan
     * @param array $options Opsi tambahan:
     *   - 'module': string (e.g. 'kelola-spmb', 'kelola-absen-siswa', 'system')
     *   - 'include_footer': bool (default: true)
     *   - 'media_url': string url lampiran gambar/file jika ada
     *   - 'user_id': int ID user pengirim jika ada
     * @return array [success => bool, message => string, target => string, response => mixed, log_id => int]
     */
    public static function send(string $targetPhone, string $message, array $options = []): array
    {
        $cfg = self::getSettings();
        $serverName = self::getServerName();
        $module = $options['module'] ?? 'system';
        $userId = $options['user_id'] ?? (class_exists('Auth') ? Auth::id() : null);
        
        $formattedTarget = self::formatPhone($targetPhone);
        if (empty($formattedTarget) || strlen($formattedTarget) < 9) {
            $err = 'Nomor WhatsApp tujuan tidak valid: ' . htmlspecialchars($targetPhone);
            self::saveLog($targetPhone, $message, $module, 'failed', ['error' => $err], $userId);
            return [
                'success' => false,
                'message' => $err,
                'target'  => $targetPhone
            ];
        }

        // Cek status aktif
        if (!self::isEnabled()) {
            $err = "Server {$serverName} sedang NONAKTIF pada pengaturan sistem.";
            self::saveLog($formattedTarget, $message, $module, 'failed', ['error' => $err], $userId);
            return [
                'success' => false,
                'message' => $err,
                'target'  => $formattedTarget
            ];
        }

        // Susun pesan lengkap (tambahkan footer note jika diinginkan)
        $fullMessage = trim($message);
        $includeFooter = $options['include_footer'] ?? true;
        if ($includeFooter && !empty($cfg['wa_gateway_footer_note'])) {
            $fullMessage .= "\n\n_" . trim($cfg['wa_gateway_footer_note']) . "_";
        }

        $provider = strtolower(trim($cfg['wa_gateway_provider'] ?? 'self_hosted'));
        $apiUrl = trim($cfg['wa_gateway_api_url'] ?? self::LOCAL_SERVER_URL);
        $apiKey = trim($cfg['wa_gateway_api_key'] ?? '');
        $mediaUrl = $options['media_url'] ?? '';

        $curlResult = self::dispatchHttp($provider, $apiUrl, $apiKey, $formattedTarget, $fullMessage, $mediaUrl);

        $isSuccess = $curlResult['success'];
        $statusStr = $isSuccess ? 'success' : 'failed';
        $statusMsg = $isSuccess 
            ? "Pesan berhasil dikirim via {$serverName}." 
            : "Gagal mengirim pesan via {$serverName}: " . ($curlResult['error'] ?? 'Terjadi kesalahan pada gateway.');

        $logId = self::saveLog(
            $formattedTarget, 
            $fullMessage, 
            $module, 
            $statusStr, 
            $curlResult['raw'] ?? ['error' => $curlResult['error'] ?? 'Unknown'],
            $userId
        );

        return [
            'success'   => $isSuccess,
            'message'   => $statusMsg,
            'target'    => $formattedTarget,
            'http_code' => $curlResult['http_code'] ?? 0,
            'response'  => $curlResult['raw'] ?? null,
            'log_id'    => $logId
        ];
    }

    /**
     * Kirim Pesan WhatsApp ke banyak nomor tujuan sekaligus (Bulk Send)
     */
    public static function sendBulk(array $targets, string $message, array $options = []): array
    {
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($targets as $phone) {
            $res = self::send((string)$phone, $message, $options);
            $results[] = $res;
            if ($res['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }
        }

        return [
            'total'   => count($targets),
            'success' => $successCount,
            'failed'  => $failedCount,
            'results' => $results
        ];
    }

    /**
     * Cek Status Koneksi Gateway Server (Live Ping / Health Check)
     */
    public static function checkConnection(): array
    {
        $cfg = self::getSettings();
        $serverName = self::getServerName();
        $provider = strtolower(trim($cfg['wa_gateway_provider'] ?? 'self_hosted'));

        if ($cfg['wa_gateway_enabled'] !== '1' && $cfg['wa_gateway_enabled'] !== 1) {
            return [
                'connected'   => false,
                'status'      => 'disabled',
                'server_name' => $serverName,
                'provider'    => $provider,
                'message'     => "Server {$serverName} saat ini dalam status NONAKTIF.",
                'raw'         => null
            ];
        }

        // 1. Mode Mandiri (Self-Hosted Baileys QR Scan)
        if ($provider === 'self_hosted' || $provider === 'baileys') {
            return self::checkLocalServerStatus();
        }

        // 2. Mode Fonnte
        if ($provider === 'fonnte') {
            if (empty($cfg['wa_gateway_api_key'])) {
                return [
                    'connected'   => false,
                    'status'      => 'disconnected',
                    'server_name' => $serverName,
                    'provider'    => 'Fonnte',
                    'message'     => 'API Key Fonnte belum diisi pada form konfigurasi.',
                    'raw'         => null
                ];
            }

            $ch = curl_init('https://api.fonnte.com/device');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_HTTPHEADER     => ["Authorization: " . trim($cfg['wa_gateway_api_key'])],
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode((string)$resp, true);
            if ($code === 200 && is_array($data) && ($data['status'] ?? false)) {
                $devName = $data['name'] ?? $data['device'] ?? $data['sender'] ?? 'Device Terhubung';
                return [
                    'connected'   => true,
                    'status'      => 'connected',
                    'server_name' => $serverName,
                    'provider'    => 'Fonnte',
                    'message'     => "Server terhubung aktif! ({$devName})",
                    'device_info' => $data,
                    'raw'         => $data
                ];
            }
        }

        // Fallback default
        return [
            'connected'   => true,
            'status'      => 'connected',
            'server_name' => $serverName,
            'provider'    => ucfirst($provider),
            'message'     => "Server {$serverName} siap memproses pengiriman pesan.",
            'raw'         => null
        ];
    }

    /**
     * Cek status langsung ke server Node.js lokal (Port 8001)
     */
    public static function checkLocalServerStatus(): array
    {
        $serverName = self::getServerName();
        $ch = curl_init(self::LOCAL_SERVER_URL . '/api/status');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 2,
            CURLOPT_CONNECTTIMEOUT => 2
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($code === 200 && !empty($resp)) {
            $data = json_decode((string)$resp, true);
            if (is_array($data)) {
                $status = $data['status'] ?? 'unknown';
                $isConnected = ($status === 'connected');
                $phone = $data['phone'] ?? null;
                $formattedPhone = $phone ? ('+' . $phone) : null;

                $msg = match ($status) {
                    'connected'   => "Terhubung! Akun WhatsApp {$formattedPhone} aktif pada server {$serverName}.",
                    'qr_ready'    => "QR Code siap di-scan. Silakan scan menggunakan WhatsApp pada HP Anda.",
                    'connecting'  => "Sedang menghubungkan ke server WhatsApp...",
                    'disconnected'=> "Perangkat terputus. QR Code baru sedang disiapkan.",
                    default       => "Status server: {$status}"
                };

                return [
                    'connected'   => $isConnected,
                    'status'      => $status,
                    'server_name' => $serverName,
                    'provider'    => 'Self-Hosted (Scan QR)',
                    'phone'       => $phone,
                    'name'        => $data['name'] ?? $serverName,
                    'has_qr'      => !empty($data['has_qr']),
                    'message'     => $msg,
                    'server_online' => true,
                    'raw'         => $data
                ];
            }
        }

        // Server lokal offline
        return [
            'connected'     => false,
            'status'        => 'offline',
            'server_name'   => $serverName,
            'provider'      => 'Self-Hosted (Scan QR)',
            'phone'         => null,
            'name'          => null,
            'has_qr'        => false,
            'server_online' => false,
            'message'       => "Server WA Gateway lokal belum berjalan di port " . self::LOCAL_SERVER_PORT . ".",
            'raw'           => ['error' => $err ?: 'Connection refused']
        ];
    }

    /**
     * Ambil data QR Code terbaru dari server lokal (data URL PNG base64)
     */
    public static function getLocalQrCode(): array
    {
        $ch = curl_init(self::LOCAL_SERVER_URL . '/api/qr');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_CONNECTTIMEOUT => 2
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && !empty($resp)) {
            $data = json_decode((string)$resp, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return [
            'success' => false,
            'status'  => 'offline',
            'message' => 'Server WA Gateway lokal offline atau belum siap.'
        ];
    }

    /**
     * Logout dan reset session WhatsApp di server lokal
     */
    public static function logoutLocalServer(): array
    {
        $ch = curl_init(self::LOCAL_SERVER_URL . '/api/logout');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => '{}',
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 5
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && !empty($resp)) {
            $data = json_decode((string)$resp, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return [
            'success' => false,
            'message' => 'Gagal menghubungi server lokal untuk logout.'
        ];
    }

    /**
     * Coba nyalakan server Node.js lokal di background jika offline
     */
    public static function startLocalServer(): array
    {
        $status = self::checkLocalServerStatus();
        if ($status['server_online']) {
            return [
                'success' => true,
                'message' => 'Server WA Gateway eportal sudah aktif berjalan.'
            ];
        }

        $serverDir = realpath(BASE_PATH . '/wa-server');
        if (!$serverDir || !file_exists($serverDir . '/server.js')) {
            return [
                'success' => false,
                'message' => 'Folder wa-server tidak ditemukan di ' . BASE_PATH . '/wa-server'
            ];
        }

        // Jalankan background via WScript / VBScript atau cmd start /B
        try {
            $vbsFile = $serverDir . '/start-background.vbs';
            if (file_exists($vbsFile)) {
                pclose(popen("wscript \"" . $vbsFile . "\"", "r"));
            } else {
                pclose(popen("cd /d \"" . $serverDir . "\" && start /B node server.js >nul 2>&1", "r"));
            }

            // Tunggu 1.5 detik
            usleep(1500000);

            $newStatus = self::checkLocalServerStatus();
            if ($newStatus['server_online']) {
                return [
                    'success' => true,
                    'message' => 'Server WA Gateway eportal berhasil dijalankan di background!'
                ];
            }

            return [
                'success' => true,
                'message' => 'Perintah start telah dikirim. Server sedang menginisialisasi, silakan refresh sebentar lagi.'
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal menyalakan server otomatis: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Dispatch HTTP Request ke Provider yang dipilih
     */
    private static function dispatchHttp(string $provider, string $url, string $apiKey, string $target, string $message, string $mediaUrl = ''): array
    {
        $headers = [];
        $payload = [];
        $isJson = false;

        switch ($provider) {
            case 'self_hosted':
            case 'baileys':
                $targetUrl = !empty($url) ? rtrim($url, '/') . '/api/send' : self::LOCAL_SERVER_URL . '/api/send';
                $headers = [
                    "Content-Type: application/json"
                ];
                $isJson = true;
                $payload = [
                    'number'  => $target,
                    'message' => $message
                ];
                break;

            case 'wablas':
                $targetUrl = !empty($url) ? $url : 'https://phone.wablas.com/api/send-message';
                $headers = [
                    "Authorization: {$apiKey}"
                ];
                $payload = [
                    'phone'   => $target,
                    'message' => $message
                ];
                if (!empty($mediaUrl)) {
                    $payload['image'] = $mediaUrl;
                }
                break;

            case 'custom':
                $targetUrl = !empty($url) ? $url : 'http://localhost:8000/send-message';
                $headers = [
                    "Authorization: Bearer {$apiKey}",
                    "Content-Type: application/json"
                ];
                $isJson = true;
                $payload = [
                    'number'  => $target,
                    'target'  => $target,
                    'phone'   => $target,
                    'message' => $message,
                    'url'     => $mediaUrl
                ];
                break;

            case 'fonnte':
            default:
                $targetUrl = !empty($url) ? $url : 'https://api.fonnte.com/send';
                $headers = [
                    "Authorization: {$apiKey}"
                ];
                $payload = [
                    'target'      => $target,
                    'message'     => $message,
                    'countryCode' => '62'
                ];
                if (!empty($mediaUrl)) {
                    $payload['url'] = $mediaUrl;
                }
                break;
        }

        $ch = curl_init($targetUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $isJson ? json_encode($payload) : $payload,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errNo) {
            return [
                'success'   => false,
                'http_code' => $httpCode,
                'error'     => "cURL Error ({$errNo}): {$errMsg}",
                'raw'       => null
            ];
        }

        $decoded = json_decode((string)$response, true);
        $isSuccess = ($httpCode >= 200 && $httpCode < 300);

        if ($isSuccess && is_array($decoded)) {
            if (isset($decoded['status'])) {
                $isSuccess = ($decoded['status'] === true || $decoded['status'] === 'true' || $decoded['status'] === 'success');
            } elseif (isset($decoded['success'])) {
                $isSuccess = ($decoded['success'] === true || $decoded['success'] === 'true');
            }
        }

        return [
            'success'   => $isSuccess,
            'http_code' => $httpCode,
            'error'     => $isSuccess ? null : ($decoded['message'] ?? ($decoded['reason'] ?? $response)),
            'raw'       => is_array($decoded) ? $decoded : ['response' => $response]
        ];
    }

    /**
     * Simpan Riwayat Pengiriman ke Tabel Database
     */
    private static function saveLog(string $target, string $message, string $module, string $status, mixed $responseData, ?int $userId = null): int
    {
        try {
            $db = Database::getInstance();
            $logData = [
                'phone_number'  => $target,
                'message'       => $message,
                'module'        => $module ?: 'system',
                'status'        => in_array($status, ['success', 'failed', 'pending']) ? $status : 'pending',
                'response_data' => is_string($responseData) ? $responseData : json_encode($responseData, JSON_UNESCAPED_UNICODE),
                'sent_by'       => $userId
            ];

            return (int)$db->insert('wa_gateway_logs', $logData);
        } catch (\Throwable $e) {
            error_log("Gagal menyimpan log WA: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Ambil Log Riwayat Pengiriman Terbaru
     */
    public static function getRecentLogs(int $limit = 25, ?string $module = null): array
    {
        try {
            $db = Database::getInstance();
            $where = "1=1";
            $params = [];

            if (!empty($module)) {
                $where .= " AND module = ?";
                $params[] = $module;
            }

            return $db->findAll("
                SELECT l.*, u.full_name as sender_name 
                FROM wa_gateway_logs l
                LEFT JOIN users u ON l.sent_by = u.id
                WHERE {$where}
                ORDER BY l.created_at DESC 
                LIMIT {$limit}
            ", $params);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Hapus Semua Log Riwayat Pesan
     */
    public static function clearLogs(): bool
    {
        try {
            $db = Database::getInstance();
            $db->query("TRUNCATE TABLE wa_gateway_logs");
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
