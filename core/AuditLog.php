<?php
/**
 * Portal BIP - Audit Log Helper
 * 
 * Provides easy logging of user actions throughout the application.
 */

class AuditLog
{
    /**
     * Record an audit log entry
     * 
     * @param string $action E.g. LOGIN, LOGOUT, CREATE, UPDATE, DELETE, SET_ACTIVE, RESET_DATA
     * @param string $entityType E.g. user, settings, spmb_peserta, pegawai, etc.
     * @param int|null $entityId ID of the affected record
     * @param mixed $oldValue Previous value (array, object, or string)
     * @param mixed $newValue New value (array, object, or string)
     * @param int|null $userId User ID override (defaults to Auth::id())
     * @return bool
     */
    public static function log(
        string $action,
        string $entityType = 'system',
        ?int $entityId = null,
        $oldValue = null,
        $newValue = null,
        ?int $userId = null
    ): bool {
        try {
            $db = Database::getInstance();
            $effectiveUserId = $userId;
            if ($effectiveUserId === null && class_exists('Auth')) {
                $effectiveUserId = Auth::id();
            }

            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            // Support proxied IPs if available
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = trim($forwarded[0]);
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            if ($userAgent && mb_strlen($userAgent) > 490) {
                $userAgent = mb_substr($userAgent, 0, 490);
            }

            $oldStr = null;
            if ($oldValue !== null) {
                $oldStr = is_string($oldValue) ? $oldValue : json_encode($oldValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            $newStr = null;
            if ($newValue !== null) {
                $newStr = is_string($newValue) ? $newValue : json_encode($newValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            $db->insert('audit_logs', [
                'user_id'     => $effectiveUserId,
                'action'      => strtoupper(trim($action)),
                'entity_type' => trim($entityType),
                'entity_id'   => $entityId,
                'old_value'   => $oldStr,
                'new_value'   => $newStr,
                'ip_address'  => $ip,
                'user_agent'  => $userAgent,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (Throwable $e) {
            error_log('AuditLog record failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper to parse user agent into human friendly browser and OS
     */
    public static function parseUserAgent(?string $ua): array
    {
        if (empty($ua)) {
            return [
                'browser' => 'Tidak Diketahui',
                'os'      => 'Tidak Diketahui',
                'device'  => 'Desktop',
                'label'   => 'Tidak Diketahui'
            ];
        }

        // Detect OS
        $os = 'Lainnya';
        if (stripos($ua, 'Windows NT 10.0') !== false) {
            $os = 'Windows 10/11';
        } elseif (stripos($ua, 'Windows NT 6.3') !== false) {
            $os = 'Windows 8.1';
        } elseif (stripos($ua, 'Windows NT 6.1') !== false) {
            $os = 'Windows 7';
        } elseif (stripos($ua, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($ua, 'iPhone') !== false) {
            $os = 'iOS (iPhone)';
        } elseif (stripos($ua, 'iPad') !== false) {
            $os = 'iPadOS';
        } elseif (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS X') !== false) {
            $os = 'macOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $os = 'Linux';
        }

        // Detect Browser
        $browser = 'Browser';
        if (stripos($ua, 'Edg/') !== false || stripos($ua, 'Edge/') !== false) {
            $browser = 'Microsoft Edge';
        } elseif (stripos($ua, 'Chrome/') !== false && stripos($ua, 'Chromium') === false) {
            $browser = 'Google Chrome';
        } elseif (stripos($ua, 'Firefox/') !== false) {
            $browser = 'Mozilla Firefox';
        } elseif (stripos($ua, 'Safari/') !== false && stripos($ua, 'Chrome/') === false) {
            $browser = 'Apple Safari';
        } elseif (stripos($ua, 'OPR/') !== false || stripos($ua, 'Opera/') !== false) {
            $browser = 'Opera';
        }

        // Device type
        $device = 'Desktop';
        if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
            $device = 'Mobile';
        } elseif (stripos($ua, 'iPad') !== false || stripos($ua, 'Tablet') !== false) {
            $device = 'Tablet';
        }

        return [
            'browser' => $browser,
            'os'      => $os,
            'device'  => $device,
            'label'   => "{$browser} ({$os})"
        ];
    }
}
