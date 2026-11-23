<?php
/**
 * JurnalStaffReminderService
 * Layanan Pengingat Pengisian Jurnal Kerja Staff via WhatsApp Gateway
 *
 * Fitur:
 * - Mengelola pengaturan jadwal otomatis (09:00, 13:00, 17:00 WITA), hari kerja aktif, dan template pesan
 * - Mengevaluasi status pengisian jurnal setiap staff Support System pada tanggal & cutoff jam tertentu
 * - Membedakan 2 kondisi pesan:
 *   1. Belum mengisi sama sekali pada hari tersebut ('belum_isi')
 *   2. Sudah mengisi beberapa sesi, namun ada jam wajib yang belum diisi ('sebagian')
 * - Mengirim pesan via WaGateway::send(..., ['include_footer' => false]) dan mencatat ke jurnal_staff_wa_log
 * - Menyinkronkan konfigurasi scheduler ke wa-server/scheduler-config.json agar berjalan otomatis di background
 */

require_once MODULES_PATH . '/kelola-jurnal-staff/models/JurnalStaffModel.php';

class JurnalStaffReminderService
{
    public const DEFAULT_SCHEDULES = '09:00,13:00,17:00';
    public const DEFAULT_ACTIVE_DAYS = '1,2,3,4,5,6'; // Senin - Sabtu
    public const TIMEZONE = 'Asia/Makassar';

    public const DEFAULT_TPL_EMPTY = "Assalamu'alaikum {nama}\nKamu belum isi jurnal hari ini, jangan lupa diisi ya.\n\nLink isi jurnal:\n{link_jurnal}\n\n_Pesan by Portal BIP_";

    public const DEFAULT_TPL_PARTIAL = "Assalamu'alaikum {nama}\nKamu belum isi jurnal di jam:\n{list_jam}\n\nLink isi jurnal:\n{link_jurnal}\n\n_Pesan by Portal BIP_";

    private static bool $tablesEnsured = false;

    /**
     * Pastikan tabel jurnal_staff_wa_log dan default settings tersedia
     */
    public static function ensureTablesExist(): void
    {
        if (self::$tablesEnsured) {
            return;
        }

        JurnalStaffModel::ensureTablesExist();

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `jurnal_staff_wa_log` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `pegawai_id` INT(11) NOT NULL,
                  `nama_staff` VARCHAR(150) NOT NULL,
                  `no_wa` VARCHAR(35) NOT NULL,
                  `tanggal` DATE NOT NULL,
                  `slot_waktu` VARCHAR(10) NOT NULL,
                  `kategori` ENUM('belum_isi', 'sebagian') NOT NULL,
                  `jam_belum_list` TEXT DEFAULT NULL,
                  `pesan` TEXT NOT NULL,
                  `status` ENUM('success', 'failed', 'skipped') NOT NULL DEFAULT 'success',
                  `error_message` TEXT DEFAULT NULL,
                  `wa_log_id` INT(11) DEFAULT NULL,
                  `trigger_source` VARCHAR(30) NOT NULL DEFAULT 'cron',
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_tgl_slot` (`tanggal`, `slot_waktu`),
                  KEY `idx_pegawai_tgl_slot` (`pegawai_id`, `tanggal`, `slot_waktu`, `status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $defaultBaseUrl = '';
            if (php_sapi_name() !== 'cli' && !empty($_SERVER['HTTP_HOST']) && defined('BASE_URL')) {
                $defaultBaseUrl = rtrim((string)BASE_URL, '/');
            }

            $defaults = self::getDefaultSettingsMap($defaultBaseUrl);
            $stmtCheck = $pdo->prepare("SELECT 1 FROM `settings` WHERE `setting_key` = ? LIMIT 1");
            $stmtInsert = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES (?, ?)");

            foreach ($defaults as $key => $val) {
                $stmtCheck->execute([$key]);
                if (!$stmtCheck->fetch()) {
                    $stmtInsert->execute([$key, $val]);
                }
            }

            self::$tablesEnsured = true;
        } catch (\Throwable $e) {
            error_log('Error ensureTablesExist JurnalStaffReminderService: ' . $e->getMessage());
        }
    }

    /**
     * Peta default settings pengingat WA Jurnal Staff
     */
    private static function getDefaultSettingsMap(string $defaultBaseUrl = ''): array
    {
        return [
            'jurnal_staff_wa_enabled'           => '1',
            'jurnal_staff_wa_schedules'         => self::DEFAULT_SCHEDULES,
            'jurnal_staff_wa_active_days'       => self::DEFAULT_ACTIVE_DAYS,
            'jurnal_staff_wa_base_url'          => $defaultBaseUrl,
            'jurnal_staff_wa_cron_key'          => bin2hex(random_bytes(16)),
            'jurnal_staff_wa_exclude_istirahat' => '0',
            'jurnal_staff_wa_skip_cuti_izin'    => '1',
            'jurnal_staff_wa_tpl_empty'         => self::DEFAULT_TPL_EMPTY,
            'jurnal_staff_wa_tpl_partial'       => self::DEFAULT_TPL_PARTIAL,
        ];
    }

    /**
     * Ambil seluruh pengaturan pengingat WA Jurnal Staff
     */
    public static function getSettings(): array
    {
        self::ensureTablesExist();

        $settings = [
            'jurnal_staff_wa_enabled'           => '1',
            'jurnal_staff_wa_schedules'         => self::DEFAULT_SCHEDULES,
            'jurnal_staff_wa_active_days'       => self::DEFAULT_ACTIVE_DAYS,
            'jurnal_staff_wa_base_url'          => '',
            'jurnal_staff_wa_cron_key'          => '',
            'jurnal_staff_wa_exclude_istirahat' => '0',
            'jurnal_staff_wa_skip_cuti_izin'    => '1',
            'jurnal_staff_wa_tpl_empty'         => self::DEFAULT_TPL_EMPTY,
            'jurnal_staff_wa_tpl_partial'       => self::DEFAULT_TPL_PARTIAL,
        ];

        try {
            $db = Database::getInstance();
            $rows = $db->findAll("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'jurnal_staff_wa_%'");
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = (string)($row['setting_value'] ?? '');
            }

            // Pastikan cron_key selalu ada
            if (empty($settings['jurnal_staff_wa_cron_key'])) {
                $newKey = bin2hex(random_bytes(16));
                self::upsertSetting($db, 'jurnal_staff_wa_cron_key', $newKey);
                $settings['jurnal_staff_wa_cron_key'] = $newKey;
            }

            // Auto-capture base URL saat diakses via web jika belum diisi
            if (trim($settings['jurnal_staff_wa_base_url']) === '' && php_sapi_name() !== 'cli' && !empty($_SERVER['HTTP_HOST']) && defined('BASE_URL')) {
                $capturedUrl = rtrim((string)BASE_URL, '/');
                if ($capturedUrl !== '') {
                    self::upsertSetting($db, 'jurnal_staff_wa_base_url', $capturedUrl);
                    $settings['jurnal_staff_wa_base_url'] = $capturedUrl;
                }
            }
        } catch (\Throwable $e) {
            error_log('Error getSettings JurnalStaffReminderService: ' . $e->getMessage());
        }

        $settings['schedules_array'] = self::parseSchedulesString($settings['jurnal_staff_wa_schedules']);
        $settings['jurnal_staff_wa_schedules'] = implode(',', $settings['schedules_array']);

        $settings['active_days_array'] = self::parseActiveDaysString($settings['jurnal_staff_wa_active_days']);

        if (trim($settings['jurnal_staff_wa_tpl_empty']) === '') {
            $settings['jurnal_staff_wa_tpl_empty'] = self::DEFAULT_TPL_EMPTY;
        }
        if (trim($settings['jurnal_staff_wa_tpl_partial']) === '') {
            $settings['jurnal_staff_wa_tpl_partial'] = self::DEFAULT_TPL_PARTIAL;
        }

        $effectiveBaseUrl = trim($settings['jurnal_staff_wa_base_url']);
        if ($effectiveBaseUrl === '') {
            $envAppUrl = getenv('APP_URL');
            if ($envAppUrl !== false && trim((string)$envAppUrl) !== '') {
                $effectiveBaseUrl = trim((string)$envAppUrl);
            } elseif (defined('BASE_URL') && trim((string)BASE_URL) !== '') {
                $effectiveBaseUrl = trim((string)BASE_URL);
            } else {
                $effectiveBaseUrl = 'http://localhost/portal-bip';
            }
        }
        $effectiveBaseUrl = rtrim($effectiveBaseUrl, '/');

        $settings['effective_base_url'] = $effectiveBaseUrl;
        $settings['jurnal_link'] = $effectiveBaseUrl . '/mobile/jurnal';

        return $settings;
    }

    /**
     * Parse string jadwal "09:00,13:00,17:00" menjadi array terurut ['09:00', ...]
     */
    public static function parseSchedulesString(string $raw): array
    {
        $parts = preg_split('/[\s,;]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY);
        $valid = [];
        foreach ($parts as $p) {
            $p = str_replace('.', ':', trim($p));
            if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $p, $m)) {
                $valid[] = sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
            }
        }
        $valid = array_values(array_unique($valid));
        sort($valid);

        if (empty($valid)) {
            return ['09:00', '13:00', '17:00'];
        }
        return $valid;
    }

    /**
     * Parse string hari aktif "1,2,3,4,5,6" menjadi array int 1..7
     */
    public static function parseActiveDaysString(string $raw, bool $fallbackIfEmpty = true): array
    {
        $parts = preg_split('/[\s,;]+/', trim($raw), -1, PREG_SPLIT_NO_EMPTY);
        $days = [];
        foreach ($parts as $p) {
            if (is_numeric($p)) {
                $d = (int)$p;
                if ($d >= 1 && $d <= 7) {
                    $days[] = $d;
                }
            }
        }
        $days = array_values(array_unique($days));
        sort($days);

        if (empty($days) && $fallbackIfEmpty) {
            return [1, 2, 3, 4, 5, 6];
        }
        return $days;
    }

    /**
     * Simpan pengaturan pengingat WA ke tabel settings dan sinkronkan ke wa-server
     */
    public static function saveSettings(array $input): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $enabled = !empty($input['jurnal_staff_wa_enabled']) && $input['jurnal_staff_wa_enabled'] !== '0' ? '1' : '0';
        $schedulesArr = self::parseSchedulesString((string)($input['jurnal_staff_wa_schedules'] ?? self::DEFAULT_SCHEDULES));
        $schedulesStr = implode(',', $schedulesArr);

        $rawDays = $input['jurnal_staff_wa_active_days'] ?? [];
        if (is_array($rawDays)) {
            $daysArr = self::parseActiveDaysString(implode(',', $rawDays), false);
        } else {
            $daysArr = self::parseActiveDaysString((string)$rawDays, false);
        }
        $daysStr = implode(',', $daysArr);

        $baseUrl = rtrim(trim((string)($input['jurnal_staff_wa_base_url'] ?? '')), '/');
        if ($baseUrl !== '' && !preg_match('#^https?://#i', $baseUrl)) {
            $baseUrl = 'https://' . ltrim($baseUrl, '/');
        }

        $excludeIstirahat = !empty($input['jurnal_staff_wa_exclude_istirahat']) && $input['jurnal_staff_wa_exclude_istirahat'] !== '0' ? '1' : '0';
        $skipCutiIzin = !empty($input['jurnal_staff_wa_skip_cuti_izin']) && $input['jurnal_staff_wa_skip_cuti_izin'] !== '0' ? '1' : '0';

        $tplEmpty = trim((string)($input['jurnal_staff_wa_tpl_empty'] ?? ''));
        if ($tplEmpty === '') {
            $tplEmpty = self::DEFAULT_TPL_EMPTY;
        }

        $tplPartial = trim((string)($input['jurnal_staff_wa_tpl_partial'] ?? ''));
        if ($tplPartial === '') {
            $tplPartial = self::DEFAULT_TPL_PARTIAL;
        }

        $updates = [
            'jurnal_staff_wa_enabled'           => $enabled,
            'jurnal_staff_wa_schedules'         => $schedulesStr,
            'jurnal_staff_wa_active_days'       => $daysStr,
            'jurnal_staff_wa_base_url'          => $baseUrl,
            'jurnal_staff_wa_exclude_istirahat' => $excludeIstirahat,
            'jurnal_staff_wa_skip_cuti_izin'    => $skipCutiIzin,
            'jurnal_staff_wa_tpl_empty'         => $tplEmpty,
            'jurnal_staff_wa_tpl_partial'       => $tplPartial,
        ];

        foreach ($updates as $k => $v) {
            self::upsertSetting($db, $k, $v);
        }

        self::syncWaServerConfig();
        return self::getSettings();
    }

    /**
     * Buat ulang secret key untuk endpoint cron
     */
    public static function regenerateCronKey(): string
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $newKey = bin2hex(random_bytes(16));
        self::upsertSetting($db, 'jurnal_staff_wa_cron_key', $newKey);
        self::syncWaServerConfig();
        return $newKey;
    }

    /**
     * Sinkronisasi file konfigurasi scheduler bawaan wa-server (Node.js)
     */
    public static function syncWaServerConfig(): void
    {
        try {
            $settings = self::getSettings();
            $configPath = BASE_PATH . '/wa-server/scheduler-config.json';
            $dir = dirname($configPath);
            if (!is_dir($dir)) {
                return;
            }

            $payload = [
                'enabled'       => ($settings['jurnal_staff_wa_enabled'] === '1'),
                'timezone'      => self::TIMEZONE,
                'schedules'     => $settings['schedules_array'],
                'active_days'   => self::parseActiveDaysString($settings['jurnal_staff_wa_active_days'], false),
                'portal_url'    => $settings['effective_base_url'],
                'cron_endpoint' => $settings['effective_base_url'] . '/cron/jurnal-staff-reminder',
                'cron_key'      => $settings['jurnal_staff_wa_cron_key'],
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            @file_put_contents($configPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $e) {
            error_log('Error syncWaServerConfig: ' . $e->getMessage());
        }
    }

    private static function upsertSetting(Database $db, string $key, string $val): void
    {
        $exists = $db->find("SELECT 1 FROM settings WHERE setting_key = ? LIMIT 1", [$key]);
        if ($exists) {
            $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$val, $key]);
        } else {
            $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $val]);
        }
    }

    /**
     * Ambil daftar pegawai aktif yang masuk kategori Support System (bukan Guru KBM)
     * Mengikuti aturan deteksi yang konsisten dengan PortalGuruController::formatTeacherArray()
     */
    public static function getTargetSupportStaff(): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $pegawaiRows = $db->findAll("
            SELECT id, nama, niy, jabatan, unit_tugas, jenis_pegawai, no_wa, email, foto
            FROM pegawai
            WHERE is_active = 1
            ORDER BY nama ASC
        ");

        // Peta penugasan aktif per pegawai (mengutamakan grup penugasan aktif)
        $penugasanMap = [];
        try {
            $penRows = $db->findAll("
                SELECT pp.pegawai_id, mj.nama AS jabatan_nama, mj.kategori AS jabatan_kategori,
                       COALESCE(pg.is_active, 0) AS grup_active, pp.id AS pp_id
                FROM pegawai_penugasan pp
                INNER JOIN master_jabatan mj ON mj.id = pp.jabatan_id
                LEFT JOIN penugasan_grup pg ON pg.id = pp.grup_id
                WHERE pp.status = 'Aktif'
                ORDER BY COALESCE(pg.is_active, 0) DESC, pp.id DESC
            ");
            foreach ($penRows as $pr) {
                $pid = (int)$pr['pegawai_id'];
                if ($pid > 0 && !isset($penugasanMap[$pid])) {
                    $penugasanMap[$pid] = [
                        'jabatan_nama'     => trim((string)($pr['jabatan_nama'] ?? '')),
                        'jabatan_kategori' => strtolower(trim((string)($pr['jabatan_kategori'] ?? ''))),
                    ];
                }
            }
        } catch (\Throwable $e) {}

        // Daftar nama jabatan kategori support_system di master_jabatan
        $supportJabNames = [];
        try {
            $jabs = JurnalStaffModel::getMasterJabatanList('support_system');
            foreach ($jabs as $j) {
                $jn = strtolower(trim((string)($j['nama'] ?? '')));
                if ($jn !== '') {
                    $supportJabNames[$jn] = true;
                }
            }
        } catch (\Throwable $e) {}

        // Fallback nomor WA dari tabel users (jika pegawai.no_wa kosong)
        $userPhonesByEmail = [];
        $userPhonesByName = [];
        try {
            $userRows = $db->findAll("SELECT email, full_name, phone FROM users WHERE phone IS NOT NULL AND TRIM(phone) != ''");
            foreach ($userRows as $ur) {
                $ph = trim((string)$ur['phone']);
                if ($ph === '') continue;
                $em = strtolower(trim((string)($ur['email'] ?? '')));
                $fn = strtolower(trim((string)($ur['full_name'] ?? '')));
                if ($em !== '' && !isset($userPhonesByEmail[$em])) {
                    $userPhonesByEmail[$em] = $ph;
                }
                if ($fn !== '' && !isset($userPhonesByName[$fn])) {
                    $userPhonesByName[$fn] = $ph;
                }
            }
        } catch (\Throwable $e) {}

        $supportKeywords = ['IT', 'Media', 'Sarpras', 'Tata Usaha', 'Tendik', 'Staff', 'Staf', 'Keamanan', 'Satpam', 'Kebersihan', 'Cleaning', 'Bisnis', 'Sosial', 'Keuangan', 'Admin', 'Operator'];

        $targets = [];
        foreach ($pegawaiRows as $p) {
            $pid = (int)$p['id'];
            $role = trim((string)($p['jabatan'] ?? ''));
            $penugasanKategori = '';

            if (isset($penugasanMap[$pid])) {
                if ($penugasanMap[$pid]['jabatan_nama'] !== '') {
                    $role = $penugasanMap[$pid]['jabatan_nama'];
                }
                $penugasanKategori = $penugasanMap[$pid]['jabatan_kategori'];
            }

            $jenisPegawai = trim((string)($p['jenis_pegawai'] ?? ''));

            $isSupport = false;
            if ($penugasanKategori === 'support_system') {
                $isSupport = true;
            } elseif ($penugasanKategori === 'guru') {
                $isSupport = false;
            } elseif (strcasecmp($jenisPegawai, 'Support System') === 0 || stripos($jenisPegawai, 'support') !== false) {
                $isSupport = true;
            } elseif (strcasecmp($jenisPegawai, 'Guru') === 0 || stripos($jenisPegawai, 'guru') !== false) {
                $isSupport = false;
            } else {
                if ($role !== '' && isset($supportJabNames[strtolower($role)])) {
                    $isSupport = true;
                } else {
                    foreach ($supportKeywords as $kw) {
                        if (stripos($role, $kw) !== false) {
                            $isSupport = true;
                            break;
                        }
                    }
                }
            }

            if (!$isSupport) {
                continue;
            }

            $rawWa = trim((string)($p['no_wa'] ?? ''));
            if ($rawWa === '') {
                $em = strtolower(trim((string)($p['email'] ?? '')));
                $nm = strtolower(trim((string)($p['nama'] ?? '')));
                if ($em !== '' && isset($userPhonesByEmail[$em])) {
                    $rawWa = $userPhonesByEmail[$em];
                } elseif ($nm !== '' && isset($userPhonesByName[$nm])) {
                    $rawWa = $userPhonesByName[$nm];
                }
            }

            $formattedWa = $rawWa !== '' ? WaGateway::formatPhone($rawWa) : '';

            $targets[] = [
                'id'              => $pid,
                'nama'            => trim((string)$p['nama']),
                'niy'             => $p['niy'] ?? '-',
                'jabatan'         => $role ?: 'Staff Support System',
                'unit_tugas'      => $p['unit_tugas'] ?? 'Yayasan',
                'no_wa_raw'       => $rawWa,
                'no_wa_formatted' => $formattedWa,
                'has_valid_wa'    => ($formattedWa !== '' && strlen($formattedWa) >= 9),
                'foto'            => $p['foto'] ?? null,
            ];
        }

        return $targets;
    }

    /**
     * Cek apakah pegawai sedang cuti / izin / sakit yang disetujui pada tanggal tertentu
     */
    public static function getStaffExcusedReason(int $pegawaiId, string $date): ?string
    {
        $db = Database::getInstance();

        // 1. Modul kelola-cuti (cuti_pengajuan)
        try {
            $rows = $db->findAll("
                SELECT id, kategori_cuti, tanggal_mulai, tanggal_selesai, tanggal_cuti_list
                FROM cuti_pengajuan
                WHERE pegawai_id = ? AND status = 'Disetujui'
                  AND ((? BETWEEN tanggal_mulai AND tanggal_selesai) OR (tanggal_cuti_list IS NOT NULL AND tanggal_cuti_list LIKE ?))
            ", [$pegawaiId, $date, '%' . $date . '%']);

            foreach ($rows as $r) {
                $listRaw = trim((string)($r['tanggal_cuti_list'] ?? ''));
                if ($listRaw !== '') {
                    $listArr = json_decode($listRaw, true);
                    if (is_array($listArr) && !empty($listArr)) {
                        if (in_array($date, $listArr, true)) {
                            return 'Cuti';
                        }
                        continue;
                    }
                }
                if ($date >= $r['tanggal_mulai'] && $date <= $r['tanggal_selesai']) {
                    return 'Cuti';
                }
            }
        } catch (\Throwable $e) {}

        // 2. Legacy cuti_guru
        try {
            $cg = $db->find("
                SELECT jenis_cuti FROM cuti_guru
                WHERE pegawai_id = ? AND status = 'Disetujui' AND ? BETWEEN tanggal_mulai AND tanggal_selesai
                LIMIT 1
            ", [$pegawaiId, $date]);
            if ($cg) {
                return 'Cuti';
            }
        } catch (\Throwable $e) {}

        // 3. Izin tidak masuk / dinas luar (izin_guru)
        try {
            $ig = $db->find("
                SELECT jenis_izin FROM izin_guru
                WHERE pegawai_id = ? AND status = 'Disetujui'
                  AND jenis_izin IN ('Tidak Masuk', 'Dinas Luar')
                  AND ? BETWEEN tanggal_mulai AND tanggal_selesai
                LIMIT 1
            ", [$pegawaiId, $date]);
            if ($ig) {
                return $ig['jenis_izin'] === 'Dinas Luar' ? 'Dinas Luar' : 'Izin';
            }
        } catch (\Throwable $e) {}

        // 4. Log presensi harian (absen_pegawai_log)
        try {
            $ab = $db->find("
                SELECT status FROM absen_pegawai_log
                WHERE pegawai_id = ? AND tanggal = ? AND status IN ('Izin', 'Sakit', 'Cuti', 'Dinas Luar')
                LIMIT 1
            ", [$pegawaiId, $date]);
            if ($ab && !empty($ab['status'])) {
                return (string)$ab['status'];
            }
        } catch (\Throwable $e) {}

        return null;
    }

    /**
     * Ambil daftar slot jam wajib diisi hingga cutoff waktu pengingat tertentu
     */
    public static function getRequiredJamSlotsForCutoff(string $cutoffTime, bool $excludeIstirahat = false): array
    {
        $activeJams = JurnalStaffModel::getJamList(true);
        if ($excludeIstirahat) {
            $activeJams = array_values(array_filter(
                $activeJams,
                static fn($j) => strtolower((string)($j['tipe'] ?? '')) !== 'istirahat'
            ));
        }

        if (empty($activeJams)) {
            return [];
        }

        $cleanCutoff = strtolower(trim($cutoffTime));
        if ($cleanCutoff === '' || $cleanCutoff === 'all' || $cleanCutoff === 'semua') {
            return $activeJams;
        }

        $cleanCutoff = str_replace('.', ':', $cleanCutoff);
        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $cleanCutoff, $m)) {
            $cutoffNorm = sprintf('%02d:%02d:00', (int)$m[1], (int)$m[2]);
        } elseif (preg_match('/^([01]?\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $cleanCutoff, $m)) {
            $cutoffNorm = sprintf('%02d:%02d:%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
        } else {
            return $activeJams;
        }

        $cutoffSec = strtotime('1970-01-01 ' . $cutoffNorm);
        $maxEndSec = 0;
        foreach ($activeJams as $j) {
            $endSec = strtotime('1970-01-01 ' . $j['jam_selesai']);
            if ($endSec > $maxEndSec) {
                $maxEndSec = $endSec;
            }
        }

        // Pada 17:00 atau setelah jam kerja berakhir, seluruh sesi aktif pada hari tsb wajib dicek
        if ($cutoffNorm >= '17:00:00' || ($maxEndSec > 0 && $cutoffSec >= $maxEndSec)) {
            return $activeJams;
        }

        $filtered = [];
        foreach ($activeJams as $j) {
            $endSec = strtotime('1970-01-01 ' . $j['jam_selesai']);
            if ($endSec <= $cutoffSec) {
                $filtered[] = $j;
            }
        }

        // Fallback jika cutoff lebih awal dari seluruh jam_selesai (misal tes manual jam 07:30)
        if (empty($filtered)) {
            foreach ($activeJams as $j) {
                $startSec = strtotime('1970-01-01 ' . $j['jam_mulai']);
                if ($startSec <= $cutoffSec) {
                    $filtered[] = $j;
                }
            }
        }

        return $filtered;
    }

    /**
     * Normalisasi string rentang jam seperti "08.00 - 09.00" atau "08:00:00 - 09:00:00" menjadi "08:00-09:00"
     */
    public static function normalizeRentang(string $rentang): string
    {
        $clean = trim($rentang);
        if ($clean === '') {
            return '';
        }

        if (preg_match('/(\d{1,2})[:.](\d{2})(?::\d{2})?\s*-\s*(\d{1,2})[:.](\d{2})(?::\d{2})?/', $clean, $m)) {
            return sprintf('%02d:%02d-%02d:%02d', (int)$m[1], (int)$m[2], (int)$m[3], (int)$m[4]);
        }

        $clean = str_replace('.', ':', $clean);
        return preg_replace('/\s+/', '', $clean);
    }

    /**
     * Evaluasi status pengisian jurnal seluruh staff Support System pada tanggal dan slot cutoff tertentu
     */
    public static function evaluateAllStaffForDate(string $date, string $slotTime, ?array $settings = null): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        if ($settings === null) {
            $settings = self::getSettings();
        }

        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        $liburInfo = AbsenPegawaiModel::getLiburInfoForDate($date);
        $isHoliday = ($liburInfo !== null);

        $excludeIstirahat = ($settings['jurnal_staff_wa_exclude_istirahat'] === '1');
        $skipCutiIzin = ($settings['jurnal_staff_wa_skip_cuti_izin'] === '1');

        $allActiveJams = JurnalStaffModel::getJamList(true);
        if ($excludeIstirahat) {
            $allActiveJams = array_values(array_filter(
                $allActiveJams,
                static fn($j) => strtolower((string)($j['tipe'] ?? '')) !== 'istirahat'
            ));
        }

        $requiredJams = self::getRequiredJamSlotsForCutoff($slotTime, $excludeIstirahat);
        $staffList = self::getTargetSupportStaff();

        // Ambil seluruh catatan jurnal staff pada tanggal target
        $journalRows = $db->findAll("
            SELECT pegawai_id, jam_id, jam_rentang
            FROM jurnal_staff
            WHERE tanggal = ?
        ", [$date]);

        $filledByStaff = [];
        foreach ($journalRows as $jr) {
            $pid = (int)$jr['pegawai_id'];
            if (!isset($filledByStaff[$pid])) {
                $filledByStaff[$pid] = [
                    'jam_ids'   => [],
                    'rentangs'  => [],
                    'row_count' => 0,
                ];
            }
            $filledByStaff[$pid]['row_count']++;
            if (!empty($jr['jam_id'])) {
                $filledByStaff[$pid]['jam_ids'][(int)$jr['jam_id']] = true;
            }
            $normRentang = self::normalizeRentang((string)($jr['jam_rentang'] ?? ''));
            if ($normRentang !== '') {
                $filledByStaff[$pid]['rentangs'][$normRentang] = true;
            }
        }

        // Ambil riwayat WA terkirim hari ini per staff & slot
        $waLogsRows = $db->findAll("
            SELECT pegawai_id, slot_waktu, status
            FROM jurnal_staff_wa_log
            WHERE tanggal = ? AND status = 'success'
        ", [$date]);

        $sentMap = [];
        foreach ($waLogsRows as $wl) {
            $pid = (int)$wl['pegawai_id'];
            $sw = trim((string)$wl['slot_waktu']);
            if ($pid > 0 && $sw !== '') {
                $sentMap[$pid][$sw] = true;
            }
        }

        $summary = [
            'total_staff'    => count($staffList),
            'needs_reminder' => 0,
            'belum_isi'      => 0,
            'sebagian'       => 0,
            'lengkap'        => 0,
            'cuti_izin'      => 0,
            'no_wa'          => 0,
            'already_sent'   => 0,
        ];

        $items = [];
        foreach ($staffList as $staff) {
            $pid = (int)$staff['id'];
            $staffFilled = $filledByStaff[$pid] ?? ['jam_ids' => [], 'rentangs' => [], 'row_count' => 0];
            $filledTodayCount = (int)$staffFilled['row_count'];

            $missingJams = [];
            $filledRequiredCount = 0;

            foreach ($requiredJams as $rj) {
                $rjId = (int)$rj['id'];
                $rjNorm = substr((string)$rj['jam_mulai'], 0, 5) . '-' . substr((string)$rj['jam_selesai'], 0, 5);
                $isFilled = isset($staffFilled['jam_ids'][$rjId]) || isset($staffFilled['rentangs'][$rjNorm]);

                if ($isFilled) {
                    $filledRequiredCount++;
                } else {
                    $disp = str_replace(':', '.', substr((string)$rj['jam_mulai'], 0, 5))
                          . ' - '
                          . str_replace(':', '.', substr((string)$rj['jam_selesai'], 0, 5));
                    $missingJams[] = [
                        'id'          => $rjId,
                        'rentang'     => $disp,
                        'nama_sesi'   => trim((string)$rj['nama_sesi']),
                        'label'       => $disp . ($rj['nama_sesi'] ? ' (' . trim((string)$rj['nama_sesi']) . ')' : ''),
                    ];
                }
            }

            $excusedReason = self::getStaffExcusedReason($pid, $date);
            $alreadySentForSlot = !empty($sentMap[$pid][$slotTime]);
            $sentSlotsToday = isset($sentMap[$pid]) ? array_keys($sentMap[$pid]) : [];

            $needsReminder = false;
            $category = 'lengkap';

            if ($skipCutiIzin && $excusedReason !== null) {
                $category = 'cuti_izin';
                $summary['cuti_izin']++;
            } elseif ($isHoliday) {
                $category = 'libur';
                $summary['lengkap']++;
            } elseif (empty($requiredJams) || count($missingJams) === 0) {
                $category = 'lengkap';
                $summary['lengkap']++;
            } elseif ($filledTodayCount === 0) {
                $category = 'belum_isi';
                $needsReminder = true;
                $summary['belum_isi']++;
                $summary['needs_reminder']++;
            } else {
                $category = 'sebagian';
                $needsReminder = true;
                $summary['sebagian']++;
                $summary['needs_reminder']++;
            }

            if (!$staff['has_valid_wa']) {
                $summary['no_wa']++;
            }
            if ($alreadySentForSlot) {
                $summary['already_sent']++;
            }

            $previewMessage = $needsReminder
                ? self::renderMessage($category, $staff, $missingJams, $date, $slotTime, $settings)
                : '';

            $items[] = [
                'pegawai_id'            => $pid,
                'nama'                  => $staff['nama'],
                'niy'                   => $staff['niy'],
                'jabatan'               => $staff['jabatan'],
                'unit_tugas'            => $staff['unit_tugas'],
                'no_wa_raw'             => $staff['no_wa_raw'],
                'no_wa_formatted'       => $staff['no_wa_formatted'],
                'has_valid_wa'          => $staff['has_valid_wa'],
                'filled_today_count'    => $filledTodayCount,
                'total_active_jams'     => count($allActiveJams),
                'required_count'        => count($requiredJams),
                'filled_required_count' => $filledRequiredCount,
                'missing_jams'          => $missingJams,
                'excused_reason'        => $excusedReason,
                'category'              => $category,
                'needs_reminder'        => $needsReminder,
                'already_sent_for_slot' => $alreadySentForSlot,
                'sent_slots_today'      => $sentSlotsToday,
                'preview_message'       => $previewMessage,
            ];
        }

        $summary['is_holiday'] = $isHoliday;

        return [
            'date'          => $date,
            'slot'          => $slotTime,
            'is_holiday'    => $isHoliday,
            'holiday_info'  => $liburInfo,
            'required_jams' => $requiredJams,
            'summary'       => $summary,
            'items'         => $items,
        ];
    }

    /**
     * Render pesan WhatsApp sesuai kondisi ('belum_isi' vs 'sebagian')
     */
    public static function renderMessage(
        string $category,
        array $staff,
        array $missingJams,
        string $date,
        string $slotTime,
        array $settings
    ): string {
        $template = ($category === 'sebagian')
            ? (string)($settings['jurnal_staff_wa_tpl_partial'] ?? self::DEFAULT_TPL_PARTIAL)
            : (string)($settings['jurnal_staff_wa_tpl_empty'] ?? self::DEFAULT_TPL_EMPTY);

        if (trim($template) === '') {
            $template = ($category === 'sebagian') ? self::DEFAULT_TPL_PARTIAL : self::DEFAULT_TPL_EMPTY;
        }

        $listLines = [];
        foreach ($missingJams as $mj) {
            if (is_array($mj)) {
                $listLines[] = '• ' . ($mj['label'] ?? ($mj['rentang'] ?? ''));
            } else {
                $listLines[] = '• ' . (string)$mj;
            }
        }
        $listJamStr = !empty($listLines) ? implode("\n", $listLines) : '-';

        $nama = trim((string)($staff['nama'] ?? 'Rekan Staff'));
        $linkJurnal = trim((string)($settings['jurnal_link'] ?? ''));
        if ($linkJurnal === '') {
            $base = defined('BASE_URL') ? rtrim((string)BASE_URL, '/') : 'http://localhost/portal-bip';
            $linkJurnal = $base . '/mobile/jurnal';
        }

        $tanggalIndo = self::formatTanggalIndo($date);
        $jamPengingat = ($slotTime === 'all' || $slotTime === 'semua' || $slotTime === '') ? date('H:i') : $slotTime;

        $replacements = [
            '{nama}'          => $nama,
            '{list_jam}'      => $listJamStr,
            '{link_jurnal}'   => $linkJurnal,
            '{tanggal}'       => $tanggalIndo,
            '{jam_pengingat}' => $jamPengingat,
        ];

        $message = strtr($template, $replacements);
        return trim($message);
    }

    /**
     * Format tanggal ke Bahasa Indonesia: "Senin, 23 Nov 2026"
     */
    public static function formatTanggalIndo(string $date): string
    {
        $ts = strtotime($date);
        if ($ts === false) {
            return $date;
        }
        $hariMap = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $bulanMap = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        $hari = $hariMap[date('l', $ts)] ?? date('l', $ts);
        $bulan = $bulanMap[(int)date('n', $ts)] ?? date('M', $ts);
        return $hari . ', ' . date('d', $ts) . ' ' . $bulan . ' ' . date('Y', $ts);
    }

    /**
     * Eksekusi pengiriman pengingat terjadwal (dipanggil oleh cron / wa-server / manual admin)
     */
    public static function runScheduledDispatch(
        ?string $slotTime = null,
        ?string $date = null,
        string $triggerSource = 'cron',
        bool $force = false,
        bool $dryRun = false
    ): array {
        @set_time_limit(180);
        self::ensureTablesExist();
        $settings = self::getSettings();

        $date = ($date !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($date)))
            ? trim($date)
            : date('Y-m-d');

        if (!$force && $settings['jurnal_staff_wa_enabled'] !== '1') {
            return [
                'success' => true,
                'skipped' => true,
                'reason'  => 'Pengingat otomatis WA Jurnal Staff sedang dinonaktifkan di pengaturan.',
                'date'    => $date,
                'slot'    => $slotTime,
                'sent'    => 0,
            ];
        }

        $dayOfWeek = (int)date('N', strtotime($date));
        if (!$force && !in_array($dayOfWeek, $settings['active_days_array'], true)) {
            return [
                'success' => true,
                'skipped' => true,
                'reason'  => 'Hari ini (' . self::formatTanggalIndo($date) . ') bukan hari kerja aktif pengingat.',
                'date'    => $date,
                'slot'    => $slotTime,
                'sent'    => 0,
            ];
        }

        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        $liburInfo = AbsenPegawaiModel::getLiburInfoForDate($date);
        if (!$force && $liburInfo !== null) {
            return [
                'success' => true,
                'skipped' => true,
                'reason'  => 'Hari ini (' . self::formatTanggalIndo($date) . ') adalah hari libur (' . $liburInfo['nama_libur'] . '). Pengingat WA otomatis dilewati.',
                'date'    => $date,
                'slot'    => $slotTime,
                'sent'    => 0,
            ];
        }

        // Resolusi slot waktu dari parameter atau waktu WITA saat ini (toleransi ±5 menit)
        $resolvedSlot = trim((string)$slotTime);
        if ($resolvedSlot === '') {
            $nowMinutes = ((int)date('G')) * 60 + (int)date('i');
            $matchedSlot = null;
            $bestDiff = 9999;

            foreach ($settings['schedules_array'] as $sched) {
                [$sh, $sm] = array_map('intval', explode(':', $sched));
                $schedMin = $sh * 60 + $sm;
                $diff = abs($nowMinutes - $schedMin);
                if ($diff <= 5 && $diff < $bestDiff) {
                    $bestDiff = $diff;
                    $matchedSlot = $sched;
                }
            }

            if ($matchedSlot !== null) {
                $resolvedSlot = $matchedSlot;
            } elseif (!$force) {
                return [
                    'success' => true,
                    'skipped' => true,
                    'reason'  => 'Waktu saat ini (' . date('H:i') . ' WITA) di luar jendela jadwal (' . implode(', ', $settings['schedules_array']) . ').',
                    'date'    => $date,
                    'slot'    => date('H:i'),
                    'sent'    => 0,
                ];
            } else {
                $resolvedSlot = date('H:i');
            }
        }

        // Cek koneksi WA Gateway terlebih dahulu sebelum loop pengiriman
        if (!$dryRun) {
            $conn = WaGateway::checkConnection();
            if (empty($conn['connected'])) {
                return [
                    'success' => false,
                    'skipped' => false,
                    'message' => 'WA Gateway belum terhubung: ' . ($conn['message'] ?? 'Server WA offline.'),
                    'date'    => $date,
                    'slot'    => $resolvedSlot,
                    'sent'    => 0,
                ];
            }
        }

        $eval = self::evaluateAllStaffForDate($date, $resolvedSlot, $settings);
        $db = Database::getInstance();

        $sentCount = 0;
        $failedCount = 0;
        $skippedAlreadySent = 0;
        $skippedNoWa = 0;
        $details = [];

        foreach ($eval['items'] as $item) {
            if (!$item['needs_reminder']) {
                continue;
            }

            if (!$force && $item['already_sent_for_slot']) {
                $skippedAlreadySent++;
                $details[] = [
                    'pegawai_id' => $item['pegawai_id'],
                    'nama'       => $item['nama'],
                    'kategori'   => $item['category'],
                    'status'     => 'skipped_already_sent',
                ];
                continue;
            }

            if (!$item['has_valid_wa']) {
                $skippedNoWa++;
                $details[] = [
                    'pegawai_id' => $item['pegawai_id'],
                    'nama'       => $item['nama'],
                    'kategori'   => $item['category'],
                    'status'     => 'skipped_no_wa',
                ];
                continue;
            }

            $missingLabels = array_map(static fn($mj) => $mj['label'], $item['missing_jams']);
            $jamBelumStr = implode(', ', $missingLabels);
            $pesan = $item['preview_message'];

            if ($dryRun) {
                $sentCount++;
                $details[] = [
                    'pegawai_id' => $item['pegawai_id'],
                    'nama'       => $item['nama'],
                    'no_wa'      => $item['no_wa_formatted'],
                    'kategori'   => $item['category'],
                    'status'     => 'dry_run',
                    'pesan'      => $pesan,
                ];
                continue;
            }

            $res = WaGateway::send($item['no_wa_formatted'], $pesan, [
                'module'         => 'jurnal-staff-reminder',
                'include_footer' => false,
                'user_id'        => null,
            ]);

            $isOk = !empty($res['success']);
            if ($isOk) {
                $sentCount++;
            } else {
                $failedCount++;
            }

            try {
                $db->insert('jurnal_staff_wa_log', [
                    'pegawai_id'     => $item['pegawai_id'],
                    'nama_staff'     => $item['nama'],
                    'no_wa'          => $item['no_wa_formatted'],
                    'tanggal'        => $date,
                    'slot_waktu'     => substr($resolvedSlot, 0, 10),
                    'kategori'       => $item['category'],
                    'jam_belum_list' => $jamBelumStr !== '' ? $jamBelumStr : null,
                    'pesan'          => $pesan,
                    'status'         => $isOk ? 'success' : 'failed',
                    'error_message'  => $isOk ? null : (string)($res['message'] ?? 'Gagal kirim'),
                    'wa_log_id'      => !empty($res['log_id']) ? (int)$res['log_id'] : null,
                    'trigger_source' => substr($triggerSource, 0, 30),
                ]);
            } catch (\Throwable $e) {
                error_log('Gagal simpan log jurnal_staff_wa_log: ' . $e->getMessage());
            }

            $details[] = [
                'pegawai_id' => $item['pegawai_id'],
                'nama'       => $item['nama'],
                'no_wa'      => $item['no_wa_formatted'],
                'kategori'   => $item['category'],
                'status'     => $isOk ? 'success' : 'failed',
                'message'    => $res['message'] ?? '',
            ];

            // Jeda 0.8 detik antar pengiriman agar aman dari rate-limit WhatsApp
            usleep(800000);
        }

        return [
            'success'              => ($failedCount === 0 || $sentCount > 0),
            'skipped'              => false,
            'dry_run'              => $dryRun,
            'date'                 => $date,
            'slot'                 => $resolvedSlot,
            'trigger_source'       => $triggerSource,
            'total_staff'          => $eval['summary']['total_staff'],
            'needs_reminder'       => $eval['summary']['needs_reminder'],
            'sent'                 => $sentCount,
            'failed'               => $failedCount,
            'skipped_lengkap'      => $eval['summary']['lengkap'],
            'skipped_cuti_izin'    => $eval['summary']['cuti_izin'],
            'skipped_already_sent' => $skippedAlreadySent,
            'skipped_no_wa'        => $skippedNoWa,
            'message'              => $dryRun
                ? "Dry-run selesai: {$sentCount} pesan siap dikirim pada slot {$resolvedSlot}."
                : "Pengingat slot {$resolvedSlot} selesai: {$sentCount} terkirim, {$failedCount} gagal, {$skippedAlreadySent} sudah pernah dikirim, {$skippedNoWa} tanpa no WA.",
            'details'              => $details,
        ];
    }

    /**
     * Kirim pengingat manual untuk satu pegawai staff tertentu
     */
    public static function sendSingleStaffReminder(int $pegawaiId, ?string $slotTime = null, ?string $date = null): array
    {
        self::ensureTablesExist();
        $settings = self::getSettings();
        $date = ($date !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($date)))
            ? trim($date)
            : date('Y-m-d');
        $effectiveSlot = ($slotTime !== null && trim($slotTime) !== '') ? trim($slotTime) : date('H:i');

        $eval = self::evaluateAllStaffForDate($date, $effectiveSlot, $settings);
        $target = null;
        foreach ($eval['items'] as $item) {
            if ((int)$item['pegawai_id'] === $pegawaiId) {
                $target = $item;
                break;
            }
        }

        if (!$target) {
            return [
                'success' => false,
                'message' => 'Pegawai Support System tidak ditemukan atau sudah nonaktif.'
            ];
        }

        if (!$target['needs_reminder']) {
            $reason = $target['category'] === 'cuti_izin'
                ? "Staff {$target['nama']} sedang dalam status {$target['excused_reason']}."
                : "Jurnal kerja {$target['nama']} untuk slot {$effectiveSlot} sudah terisi lengkap.";
            return [
                'success' => false,
                'message' => $reason
            ];
        }

        if (!$target['has_valid_wa']) {
            return [
                'success' => false,
                'message' => "Nomor WhatsApp {$target['nama']} belum diisi atau tidak valid."
            ];
        }

        $pesan = $target['preview_message'];
        $res = WaGateway::send($target['no_wa_formatted'], $pesan, [
            'module'         => 'jurnal-staff-reminder',
            'include_footer' => false,
            'user_id'        => class_exists('Auth') ? Auth::id() : null,
        ]);

        $isOk = !empty($res['success']);
        $missingLabels = array_map(static fn($mj) => $mj['label'], $target['missing_jams']);

        try {
            $db = Database::getInstance();
            $db->insert('jurnal_staff_wa_log', [
                'pegawai_id'     => $target['pegawai_id'],
                'nama_staff'     => $target['nama'],
                'no_wa'          => $target['no_wa_formatted'],
                'tanggal'        => $date,
                'slot_waktu'     => substr($effectiveSlot ?: 'manual', 0, 10),
                'kategori'       => $target['category'],
                'jam_belum_list' => !empty($missingLabels) ? implode(', ', $missingLabels) : null,
                'pesan'          => $pesan,
                'status'         => $isOk ? 'success' : 'failed',
                'error_message'  => $isOk ? null : (string)($res['message'] ?? 'Gagal kirim'),
                'wa_log_id'      => !empty($res['log_id']) ? (int)$res['log_id'] : null,
                'trigger_source' => 'single',
            ]);
        } catch (\Throwable $e) {
            error_log('Gagal simpan log single WA jurnal staff: ' . $e->getMessage());
        }

        return [
            'success'  => $isOk,
            'message'  => $isOk
                ? "Pengingat WhatsApp berhasil dikirim ke {$target['nama']} (+{$target['no_wa_formatted']})."
                : ("Gagal mengirim ke {$target['nama']}: " . ($res['message'] ?? 'Error gateway')),
            'kategori' => $target['category'],
            'target'   => $target['no_wa_formatted'],
        ];
    }

    /**
     * Kirim pesan uji coba (contoh kondisi belum isi sama sekali atau sebagian)
     */
    public static function sendTestPreview(string $targetPhone, string $kategori = 'belum_isi', string $sampleName = 'Rekan Staff'): array
    {
        self::ensureTablesExist();
        $settings = self::getSettings();
        $cleanKategori = ($kategori === 'sebagian') ? 'sebagian' : 'belum_isi';

        $sampleMissing = [];
        if ($cleanKategori === 'sebagian') {
            $jams = self::getRequiredJamSlotsForCutoff('13:00', $settings['jurnal_staff_wa_exclude_istirahat'] === '1');
            $sampleSlice = array_slice($jams, 1, 2);
            if (empty($sampleSlice)) {
                $sampleSlice = [
                    ['jam_mulai' => '09:00:00', 'jam_selesai' => '10:00:00', 'nama_sesi' => 'Aktifitas Kerja Sesi 2'],
                    ['jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00', 'nama_sesi' => 'Aktifitas Kerja Sesi 3'],
                ];
            }
            foreach ($sampleSlice as $sj) {
                $disp = str_replace(':', '.', substr((string)$sj['jam_mulai'], 0, 5))
                      . ' - '
                      . str_replace(':', '.', substr((string)$sj['jam_selesai'], 0, 5));
                $sampleMissing[] = [
                    'rentang'   => $disp,
                    'nama_sesi' => $sj['nama_sesi'],
                    'label'     => $disp . ' (' . $sj['nama_sesi'] . ')',
                ];
            }
        }

        $pesan = self::renderMessage(
            $cleanKategori,
            ['nama' => trim($sampleName) ?: 'Rekan Staff'],
            $sampleMissing,
            date('Y-m-d'),
            date('H:i'),
            $settings
        );

        $res = WaGateway::send($targetPhone, $pesan, [
            'module'         => 'jurnal-staff-reminder-test',
            'include_footer' => false,
            'user_id'        => class_exists('Auth') ? Auth::id() : null,
        ]);

        return [
            'success' => !empty($res['success']),
            'message' => $res['message'] ?? '',
            'preview' => $pesan,
            'target'  => $res['target'] ?? $targetPhone,
        ];
    }

    /**
     * Ambil log pengiriman pengingat WA Jurnal Staff terbaru
     */
    public static function getRecentLogs(int $limit = 30): array
    {
        self::ensureTablesExist();
        try {
            $db = Database::getInstance();
            $safeLimit = max(1, min(200, $limit));
            return $db->findAll("
                SELECT *
                FROM jurnal_staff_wa_log
                ORDER BY created_at DESC, id DESC
                LIMIT {$safeLimit}
            ");
        } catch (\Throwable $e) {
            return [];
        }
    }
}
