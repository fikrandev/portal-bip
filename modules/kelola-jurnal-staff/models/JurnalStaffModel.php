<?php
/**
 * JurnalStaffModel
 * Model data untuk Modul Kelola Jurnal Staff (Support System)
 * Mengelola:
 * 1. Data Jurnal Kerja Harian Staff
 * 2. Pengaturan Jam / Slot Sesi (misal 07.00 - 08.00 Tilawah, 08.00 - 09.00 Aktifitas)
 * 3. Pengaturan Tugas / Job Desk per Jabatan referensi (master_jabatan)
 * 4. Pengaturan Periode Rekapitulasi
 */

class JurnalStaffModel
{
    public const AKTIVITAS_SEPARATOR = ' • ';

    /**
     * Memastikan semua tabel yang dibutuhkan modul ini sudah ada.
     * Fallback otomatis jika database belum dimigrasi di server.
     */
    public static function ensureTablesExist(): void
    {
        static $checked = false;
        if ($checked) return;

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            // 1. jurnal_staff_jam
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `jurnal_staff_jam` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `jam_mulai` TIME NOT NULL,
                  `jam_selesai` TIME NOT NULL,
                  `nama_sesi` VARCHAR(100) NOT NULL,
                  `tipe` ENUM('tilawah', 'aktifitas', 'istirahat', 'lainnya') NOT NULL DEFAULT 'aktifitas',
                  `keterangan` VARCHAR(255) DEFAULT NULL,
                  `urutan` INT(11) NOT NULL DEFAULT 1,
                  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 2. jurnal_staff_tugas
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `jurnal_staff_tugas` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `jabatan_id` INT(11) NOT NULL,
                  `nama_jabatan` VARCHAR(100) NOT NULL,
                  `nama_tugas` VARCHAR(255) NOT NULL,
                  `deskripsi` TEXT DEFAULT NULL,
                  `kategori_tugas` ENUM('rutin', 'berkala', 'insidental') NOT NULL DEFAULT 'rutin',
                  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_jabatan` (`jabatan_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 3. jurnal_staff
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `jurnal_staff` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `kode_jurnal` VARCHAR(35) NOT NULL,
                  `pegawai_id` INT(11) NOT NULL,
                  `nama_staff` VARCHAR(150) NOT NULL,
                  `jabatan` VARCHAR(100) NOT NULL,
                  `unit_tugas` VARCHAR(100) DEFAULT NULL,
                  `tanggal` DATE NOT NULL,
                  `jam_id` INT(11) DEFAULT NULL,
                  `jam_rentang` VARCHAR(50) NOT NULL,
                  `tugas_id` INT(11) DEFAULT NULL,
                  `tugas_ids` VARCHAR(255) DEFAULT NULL,
                  `aktivitas` TEXT NOT NULL,
                  `uraian_hasil` TEXT NOT NULL,
                  `kendala` TEXT DEFAULT NULL,
                  `foto_bukti` VARCHAR(255) DEFAULT NULL,
                  `status` ENUM('selesai', 'proses', 'tertunda') NOT NULL DEFAULT 'selesai',
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `idx_kode` (`kode_jurnal`),
                  KEY `idx_pegawai_tgl` (`pegawai_id`, `tanggal`),
                  KEY `idx_tanggal` (`tanggal`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Sinkronisasi kolom multi-tugas pada instalasi lama
            try {
                $colTugasIds = $pdo->query("SHOW COLUMNS FROM `jurnal_staff` LIKE 'tugas_ids'")->fetch(PDO::FETCH_ASSOC);
                if (!$colTugasIds) {
                    $pdo->exec("ALTER TABLE `jurnal_staff` ADD COLUMN `tugas_ids` VARCHAR(255) DEFAULT NULL AFTER `tugas_id`");
                    $pdo->exec("UPDATE `jurnal_staff` SET `tugas_ids` = CAST(`tugas_id` AS CHAR) WHERE `tugas_id` IS NOT NULL AND (`tugas_ids` IS NULL OR `tugas_ids` = '')");
                }
                $colAkt = $pdo->query("SHOW COLUMNS FROM `jurnal_staff` LIKE 'aktivitas'")->fetch(PDO::FETCH_ASSOC);
                if ($colAkt && stripos((string)($colAkt['Type'] ?? ''), 'varchar') !== false) {
                    $pdo->exec("ALTER TABLE `jurnal_staff` MODIFY COLUMN `aktivitas` TEXT NOT NULL");
                }
            } catch (Throwable $eCol) {}

            // 4. jurnal_staff_pengaturan
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `jurnal_staff_pengaturan` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `periode_awal` DATE DEFAULT NULL,
                  `periode_akhir` DATE DEFAULT NULL,
                  `keterangan` VARCHAR(255) DEFAULT NULL,
                  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Cek apakah default jam kerja ada
            $jamCount = (int)$pdo->query("SELECT COUNT(*) FROM jurnal_staff_jam")->fetchColumn();
            if ($jamCount === 0) {
                $defaultJams = [
                    ['07:00:00', '08:00:00', 'Tilawah & Dzikir Pagi', 'tilawah', 'Membaca Al-Qur\'an, Dzikir Pagi Al-Ma\'tsurat, dan briefing pagi', 1],
                    ['08:00:00', '09:00:00', 'Aktifitas Kerja Sesi 1', 'aktifitas', 'Pelaksanaan tugas utama / job desk divisi', 2],
                    ['09:00:00', '10:00:00', 'Aktifitas Kerja Sesi 2', 'aktifitas', 'Pelaksanaan tugas utama / job desk divisi', 3],
                    ['10:00:00', '11:00:00', 'Aktifitas Kerja Sesi 3', 'aktifitas', 'Pelaksanaan tugas utama / job desk divisi', 4],
                    ['11:00:00', '12:00:00', 'Aktifitas Kerja Sesi 4', 'aktifitas', 'Pelaksanaan tugas operasional divisi', 5],
                    ['12:00:00', '13:00:00', 'Istirahat & Sholat Dzuhur', 'istirahat', 'ISHOMA (Istirahat, Sholat, Makan Siang)', 6],
                    ['13:00:00', '14:00:00', 'Aktifitas Kerja Sesi 5', 'aktifitas', 'Tugas tindak lanjut dan koordinasi tim', 7],
                    ['14:00:00', '15:00:00', 'Aktifitas Kerja Sesi 6', 'aktifitas', 'Penyelesaian tugas harian operasional', 8],
                    ['15:00:00', '16:00:00', 'Evaluasi & Laporan Harian', 'aktifitas', 'Pencatatan laporan hasil kerja dan evaluasi harian', 9],
                ];
                $stmt = $pdo->prepare("INSERT INTO jurnal_staff_jam (jam_mulai, jam_selesai, nama_sesi, tipe, keterangan, urutan, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
                foreach ($defaultJams as $j) {
                    $stmt->execute($j);
                }
            }

            // Cek apakah ada record pengaturan periode
            $cfgCount = (int)$pdo->query("SELECT COUNT(*) FROM jurnal_staff_pengaturan")->fetchColumn();
            if ($cfgCount === 0) {
                $pdo->prepare("INSERT INTO jurnal_staff_pengaturan (periode_awal, periode_akhir, keterangan) VALUES (?, ?, 'Default Periode Rekap Jurnal Staff')")
                    ->execute([date('Y-m-01'), date('Y-m-t')]);
            }

            $checked = true;
        } catch (Throwable $e) {
            error_log('Error ensureTablesExist JurnalStaff: ' . $e->getMessage());
        }
    }

    /**
     * Dashboard Statistics
     */
    public static function getDashboardStats(?string $startDate = null, ?string $endDate = null): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $today = date('Y-m-d');
        $sDate = $startDate ?: date('Y-m-01');
        $eDate = $endDate ?: date('Y-m-t');

        // 1. Total Staff Terdaftar (Aktif)
        $totalStaff = (int)$pdo->query("
            SELECT COUNT(*) FROM pegawai 
            WHERE is_active = 1
        ")->fetchColumn();

        // Total Staff Support System spesifik (berdasarkan jenis_pegawai atau master_jabatan kategori support_system)
        $supportParams = [];
        $supportCond = self::buildSupportStaffCondition($supportParams);
        $stmtSup = $pdo->prepare("SELECT COUNT(*) FROM pegawai p WHERE p.is_active = 1 AND ({$supportCond})");
        $stmtSup->execute($supportParams);
        $totalSupportStaff = (int)$stmtSup->fetchColumn();
        if ($totalSupportStaff === 0) {
            $totalSupportStaff = $totalStaff;
        }

        // 2. Jurnal Hari Ini
        $jurnalHariIni = (int)$pdo->query("
            SELECT COUNT(*) FROM jurnal_staff 
            WHERE tanggal = '{$today}'
        ")->fetchColumn();

        // 3. Staff yang Mengisi Jurnal Hari Ini
        $staffIsiHariIni = (int)$pdo->query("
            SELECT COUNT(DISTINCT pegawai_id) FROM jurnal_staff 
            WHERE tanggal = '{$today}'
        ")->fetchColumn();

        // 4. Staff yang Belum Mengisi Hari Ini
        $staffBelumIsiHariIni = max(0, $totalSupportStaff - $staffIsiHariIni);

        // 5. Total Jurnal dalam Periode Terpilih
        $stmtPeriode = $pdo->prepare("SELECT COUNT(*) FROM jurnal_staff WHERE tanggal BETWEEN ? AND ?");
        $stmtPeriode->execute([$sDate, $eDate]);
        $totalJurnalPeriode = (int)$stmtPeriode->fetchColumn();

        // 6. Total Selesai dalam Periode
        $stmtSelesai = $pdo->prepare("SELECT COUNT(*) FROM jurnal_staff WHERE status = 'selesai' AND tanggal BETWEEN ? AND ?");
        $stmtSelesai->execute([$sDate, $eDate]);
        $totalSelesaiPeriode = (int)$stmtSelesai->fetchColumn();

        // 7. Tren 7 Hari Terakhir & Hari Libur Efektif (sinkron Absen Pegawai)
        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        $todayLibur = AbsenPegawaiModel::getLiburInfoForDate($today);

        $evalEndPeriode = (strtotime($eDate) > strtotime($today)) ? $today : $eDate;
        $workInfo = (strtotime($sDate) <= strtotime($evalEndPeriode))
            ? AbsenPegawaiModel::hitungHariKerjaRange($sDate, $evalEndPeriode)
            : ['jumlah_hari_kerja' => 0, 'jumlah_libur_khusus' => 0, 'jumlah_minggu' => 0];
        $hariKerjaPeriode = (int)$workInfo['jumlah_hari_kerja'];
        $liburPeriode = (int)$workInfo['jumlah_libur_khusus'];

        $activeJamCount = (int)$pdo->query("SELECT COUNT(*) FROM jurnal_staff_jam WHERE is_active = 1")->fetchColumn();
        if ($activeJamCount <= 0) {
            $activeJamCount = 8;
        }
        $targetSesiPeriode = $totalSupportStaff * $hariKerjaPeriode * $activeJamCount;
        $persenPeriode = $targetSesiPeriode > 0
            ? min(100, round(($totalJurnalPeriode / $targetSesiPeriode) * 100, 1))
            : 0;

        $trend7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $ts = strtotime($d);
            $dayNameIndo = self::getNamaHariIndo(date('l', $ts));
            $dayLibur = AbsenPegawaiModel::getLiburInfoForDate($d);
            $cnt = (int)$pdo->query("SELECT COUNT(*) FROM jurnal_staff WHERE tanggal = '{$d}'")->fetchColumn();
            $trend7Days[] = [
                'date'        => $d,
                'label'       => $dayNameIndo . ', ' . date('d/m', $ts),
                'short_date'  => date('d M', $ts),
                'count'       => $cnt,
                'is_today'    => ($d === $today),
                'is_libur'    => ($dayLibur !== null),
                'libur_label' => $dayLibur ? ($dayLibur['nama_libur'] ?? 'Libur') : null,
            ];
        }

        // 8. Rekap Jurnal per Divisi / Jabatan
        $rekapJabatan = $pdo->query("
            SELECT 
                COALESCE(NULLIF(TRIM(jabatan), ''), 'Tanpa Jabatan') as nama_jabatan,
                COUNT(*) as total_jurnal,
                COUNT(DISTINCT pegawai_id) as total_staff_aktif,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as total_selesai
            FROM jurnal_staff
            GROUP BY COALESCE(NULLIF(TRIM(jabatan), ''), 'Tanpa Jabatan')
            ORDER BY total_jurnal DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        // 9. Aktivitas Jurnal Terbaru (10 record)
        $latestJurnal = $pdo->query("
            SELECT js.*, p.foto, p.niy
            FROM jurnal_staff js
            LEFT JOIN pegawai p ON js.pegawai_id = p.id
            ORDER BY js.created_at DESC, js.id DESC
            LIMIT 10
        ")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'total_staff'             => $totalStaff,
            'total_support_staff'     => $totalSupportStaff,
            'jurnal_hari_ini'         => $jurnalHariIni,
            'staff_isi_hari_ini'      => $staffIsiHariIni,
            'staff_belum_isi_hari_ini'=> $staffBelumIsiHariIni,
            'persen_hari_ini'         => $todayLibur !== null ? 0 : ($totalSupportStaff > 0 ? round(($staffIsiHariIni / $totalSupportStaff) * 100) : 0),
            'total_jurnal_periode'    => $totalJurnalPeriode,
            'total_selesai_periode'   => $totalSelesaiPeriode,
            'today_libur'             => $todayLibur,
            'hari_kerja_periode'      => $hariKerjaPeriode,
            'libur_periode'           => $liburPeriode,
            'target_sesi_periode'     => $targetSesiPeriode,
            'persen_periode'          => $persenPeriode,
            'trend_7_days'            => $trend7Days,
            'rekap_jabatan'           => $rekapJabatan,
            'latest_jurnal'           => $latestJurnal,
        ];
    }

    /**
     * Mengambil daftar jurnal staff dengan filter
     */
    public static function getJurnalList(array $filters = [], int $page = 1, int $limit = 25): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $where[] = '(js.kode_jurnal LIKE ? OR js.nama_staff LIKE ? OR js.jabatan LIKE ? OR js.aktivitas LIKE ? OR js.uraian_hasil LIKE ? OR js.kendala LIKE ?)';
            $params = array_merge($params, [$s, $s, $s, $s, $s, $s]);
        }

        if (!empty($filters['start_date'])) {
            $where[] = 'js.tanggal >= ?';
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = 'js.tanggal <= ?';
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['pegawai_id'])) {
            $where[] = 'js.pegawai_id = ?';
            $params[] = (int)$filters['pegawai_id'];
        }

        if (!empty($filters['jabatan'])) {
            $where[] = 'js.jabatan = ?';
            $params[] = $filters['jabatan'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'js.status = ?';
            $params[] = $filters['status'];
        }

        $whereSql = implode(' AND ', $where);

        // Count Total
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM jurnal_staff js WHERE {$whereSql}");
        $countStmt->execute($params);
        $totalRows = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $limit;
        $totalPages = $limit > 0 ? (int)ceil($totalRows / $limit) : 1;

        // Data List
        $sql = "
            SELECT js.*, p.foto, p.niy, jm.nama_sesi, jm.tipe as jam_tipe
            FROM jurnal_staff js
            LEFT JOIN pegawai p ON js.pegawai_id = p.id
            LEFT JOIN jurnal_staff_jam jm ON js.jam_id = jm.id
            WHERE {$whereSql}
            ORDER BY js.tanggal DESC, js.created_at DESC, js.id DESC
            LIMIT {$limit} OFFSET {$offset}
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data'        => $rows,
            'total'       => $totalRows,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => $totalPages,
        ];
    }

    /**
     * Ambil data jurnal untuk cetak / rekap lengkap
     */
    public static function getJurnalForExport(array $filters = []): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $where[] = '(js.kode_jurnal LIKE ? OR js.nama_staff LIKE ? OR js.jabatan LIKE ? OR js.aktivitas LIKE ?)';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }

        if (!empty($filters['start_date'])) {
            $where[] = 'js.tanggal >= ?';
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $where[] = 'js.tanggal <= ?';
            $params[] = $filters['end_date'];
        }

        if (!empty($filters['pegawai_id'])) {
            $where[] = 'js.pegawai_id = ?';
            $params[] = (int)$filters['pegawai_id'];
        }

        if (!empty($filters['jabatan'])) {
            $where[] = 'js.jabatan = ?';
            $params[] = $filters['jabatan'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'js.status = ?';
            $params[] = $filters['status'];
        }

        $whereSql = implode(' AND ', $where);

        $sql = "
            SELECT js.*, p.foto, p.niy, jm.nama_sesi
            FROM jurnal_staff js
            LEFT JOIN pegawai p ON js.pegawai_id = p.id
            LEFT JOIN jurnal_staff_jam jm ON js.jam_id = jm.id
            WHERE {$whereSql}
            ORDER BY js.tanggal DESC, js.created_at DESC, js.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Parse daftar tugas_ids (string "1,2,3", JSON array, atau array PHP) menjadi array int positif unik sesuai urutan pilih
     */
    public static function parseTugasIds($raw): array
    {
        if ($raw === null || $raw === '' || $raw === false) {
            return [];
        }

        $items = [];
        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw)) {
            $trimmed = trim($raw);
            if ($trimmed === '' || strtolower($trimmed) === 'custom') {
                return [];
            }
            if ($trimmed[0] === '[') {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    $items = $decoded;
                }
            }
            if (empty($items)) {
                $items = preg_split('/[\s,;]+/', $trimmed, -1, PREG_SPLIT_NO_EMPTY);
            }
        } elseif (is_numeric($raw)) {
            $items = [$raw];
        }

        $ids = [];
        $seen = [];
        foreach ($items as $it) {
            if (is_numeric($it)) {
                $id = (int)$it;
                if ($id > 0 && !isset($seen[$id])) {
                    $seen[$id] = true;
                    $ids[] = $id;
                }
            }
        }
        return $ids;
    }

    /**
     * Pecah string aktivitas multi-tugas (dipisahkan oleh " • ") menjadi array daftar nama tugas
     * Aman terhadap nama tugas yang mengandung tanda koma (,)
     */
    public static function splitAktivitasList(?string $aktivitas): array
    {
        $raw = trim((string)$aktivitas);
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/\s*•\s*/u', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $clean = [];
        $seen = [];
        foreach ($parts as $p) {
            $t = trim($p);
            if ($t !== '') {
                $key = mb_strtolower($t, 'UTF-8');
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $clean[] = $t;
                }
            }
        }
        return !empty($clean) ? $clean : [$raw];
    }

    /**
     * Normalisasi payload tugas (mendukung 1 tugas, multi-tugas dari master, maupun kombinasi tugas kustom)
     */
    public static function resolveTugasPayload(array $data): array
    {
        $tugasIdsArr = [];
        if (array_key_exists('tugas_ids', $data) && $data['tugas_ids'] !== '' && $data['tugas_ids'] !== null) {
            $tugasIdsArr = self::parseTugasIds($data['tugas_ids']);
        }
        if (empty($tugasIdsArr) && !empty($data['tugas_id']) && $data['tugas_id'] !== 'custom') {
            $tugasIdsArr = self::parseTugasIds($data['tugas_id']);
        }

        $primaryTugasId = !empty($tugasIdsArr) ? (int)$tugasIdsArr[0] : null;
        $tugasIdsStr = !empty($tugasIdsArr) ? implode(',', $tugasIdsArr) : null;

        $rawAktivitas = '';
        if (!empty($data['aktivitas'])) {
            if (is_array($data['aktivitas'])) {
                $rawAktivitas = implode(self::AKTIVITAS_SEPARATOR, array_filter(array_map('trim', $data['aktivitas']), static fn($v) => $v !== ''));
            } else {
                $rawAktivitas = trim((string)$data['aktivitas']);
            }
        }

        $parts = self::splitAktivitasList($rawAktivitas);

        // Jika aktivitas belum terisi tetapi ada tugasIdsArr, ambil nama_tugas dari DB sesuai urutan
        if (empty($parts) && !empty($tugasIdsArr)) {
            foreach ($tugasIdsArr as $tid) {
                $tRow = self::getTugasById($tid);
                if ($tRow && !empty($tRow['nama_tugas'])) {
                    $parts[] = trim((string)$tRow['nama_tugas']);
                }
            }
        }

        // Tambahkan aktivitas_custom jika dikirim terpisah dan belum termasuk di daftar
        $customText = trim((string)($data['aktivitas_custom'] ?? ''));
        if ($customText !== '') {
            $customParts = self::splitAktivitasList($customText);
            $existingLower = array_map(static fn($x) => mb_strtolower($x, 'UTF-8'), $parts);
            foreach ($customParts as $cp) {
                if (!in_array(mb_strtolower($cp, 'UTF-8'), $existingLower, true)) {
                    $parts[] = $cp;
                    $existingLower[] = mb_strtolower($cp, 'UTF-8');
                }
            }
        }

        $finalAktivitas = implode(self::AKTIVITAS_SEPARATOR, $parts);

        return [
            'tugas_id'        => $primaryTugasId,
            'tugas_ids'       => $tugasIdsStr,
            'tugas_ids_array' => $tugasIdsArr,
            'aktivitas'       => $finalAktivitas,
            'aktivitas_list'  => $parts,
        ];
    }

    /**
     * Ambil 1 data jurnal by ID
     */
    public static function getJurnalById(int $id): ?array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $res = $db->find("
            SELECT js.*, p.foto, p.niy, p.email, p.no_wa, jm.nama_sesi, jm.tipe as jam_tipe
            FROM jurnal_staff js
            LEFT JOIN pegawai p ON js.pegawai_id = p.id
            LEFT JOIN jurnal_staff_jam jm ON js.jam_id = jm.id
            WHERE js.id = ?
        ", [$id]);

        return $res ?: null;
    }

    /**
     * Simpan jurnal staff baru
     */
    public static function storeJurnal(array $data, ?array $file = null): int
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $pegawaiId = (int)($data['pegawai_id'] ?? 0);
        $pegawai = self::getStaffById($pegawaiId);
        if (!$pegawai) {
            $pegawai = [
                'id' => $pegawaiId,
                'nama' => $data['nama_staff'] ?? 'Staff Support',
                'jabatan' => $data['jabatan'] ?? 'Staff',
                'unit_tugas' => $data['unit_tugas'] ?? 'Support System',
                'foto' => null
            ];
        }

        $tanggal = !empty($data['tanggal']) ? trim($data['tanggal']) : date('Y-m-d');
        $jamId = !empty($data['jam_id']) ? (int)$data['jam_id'] : null;
        $jamRentang = trim($data['jam_rentang'] ?? '');

        // Jika jam rentang belum terisi, coba ambil dari jurnal_staff_jam
        if ($jamRentang === '' && $jamId) {
            $jamRow = self::getJamById($jamId);
            if ($jamRow) {
                $jamRentang = date('H:i', strtotime($jamRow['jam_mulai'])) . ' - ' . date('H:i', strtotime($jamRow['jam_selesai']));
            }
        }
        if ($jamRentang === '') {
            $jamRentang = '08:00 - 09:00';
        }

        $resolvedTugas = self::resolveTugasPayload($data);
        $tugasId = $resolvedTugas['tugas_id'];
        $tugasIdsStr = $resolvedTugas['tugas_ids'];
        $aktivitas = $resolvedTugas['aktivitas'];

        if ($aktivitas === '') {
            throw new Exception("Aktivitas / uraian tugas wajib diisi.");
        }

        $uraianHasil = trim($data['uraian_hasil'] ?? '');
        if ($uraianHasil === '') {
            throw new Exception("Uraian hasil kerja wajib diisi.");
        }

        $kendala = !empty($data['kendala']) ? trim($data['kendala']) : null;
        $status = in_array($data['status'] ?? '', ['selesai', 'proses', 'tertunda'], true) ? $data['status'] : 'selesai';

        // Kode jurnal unik: JSTF-YYYYMMDD-XXXXXX
        $randStr = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $jamSuffix = $jamId ? ('-J' . $jamId) : '';
        $kodeJurnal = 'JSTF-' . date('Ymd', strtotime($tanggal)) . '-' . $randStr . $jamSuffix;

        // Upload foto jika ada
        $fotoBukti = !empty($data['foto_bukti']) ? $data['foto_bukti'] : null;
        if ($file && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $uploaded = self::uploadBuktiFile($file);
            if ($uploaded) {
                $fotoBukti = $uploaded;
            }
        }

        $db->query("
            INSERT INTO jurnal_staff (
                kode_jurnal, pegawai_id, nama_staff, jabatan, unit_tugas,
                tanggal, jam_id, jam_rentang, tugas_id, tugas_ids, aktivitas,
                uraian_hasil, kendala, foto_bukti, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $kodeJurnal,
            $pegawaiId,
            $pegawai['nama'] ?? 'Staff',
            $pegawai['jabatan'] ?: 'Staff',
            $pegawai['unit_tugas'] ?: 'Bina Insan Palu',
            $tanggal,
            $jamId,
            $jamRentang,
            $tugasId,
            $tugasIdsStr,
            $aktivitas,
            $uraianHasil,
            $kendala,
            $fotoBukti,
            $status
        ]);

        return (int)$db->lastInsertId();
    }

    /**
     * Update jurnal staff
     */
    public static function updateJurnal(int $id, array $data, ?array $file = null): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $existing = self::getJurnalById($id);
        if (!$existing) {
            throw new Exception("Catatan jurnal tidak ditemukan.");
        }

        $tanggal = !empty($data['tanggal']) ? trim($data['tanggal']) : $existing['tanggal'];
        $jamId = !empty($data['jam_id']) ? (int)$data['jam_id'] : $existing['jam_id'];
        $jamRentang = !empty($data['jam_rentang']) ? trim($data['jam_rentang']) : $existing['jam_rentang'];

        $hasTugasPayload = array_key_exists('tugas_ids', $data) || array_key_exists('tugas_id', $data) || array_key_exists('aktivitas', $data);
        if ($hasTugasPayload) {
            $resolvedTugas = self::resolveTugasPayload($data);
            $tugasId = (array_key_exists('tugas_ids', $data) || array_key_exists('tugas_id', $data))
                ? $resolvedTugas['tugas_id']
                : ($existing['tugas_id'] ?? null);
            $tugasIdsStr = (array_key_exists('tugas_ids', $data) || array_key_exists('tugas_id', $data))
                ? $resolvedTugas['tugas_ids']
                : ($existing['tugas_ids'] ?? null);
            $aktivitas = $resolvedTugas['aktivitas'] !== '' ? $resolvedTugas['aktivitas'] : $existing['aktivitas'];
        } else {
            $tugasId = $existing['tugas_id'];
            $tugasIdsStr = $existing['tugas_ids'] ?? ($existing['tugas_id'] ? (string)$existing['tugas_id'] : null);
            $aktivitas = $existing['aktivitas'];
        }

        $uraianHasil = !empty($data['uraian_hasil']) ? trim($data['uraian_hasil']) : $existing['uraian_hasil'];
        $kendala = isset($data['kendala']) ? trim($data['kendala']) : $existing['kendala'];
        $status = in_array($data['status'] ?? '', ['selesai', 'proses', 'tertunda'], true) ? $data['status'] : $existing['status'];

        $fotoBukti = isset($data['foto_bukti']) ? $data['foto_bukti'] : $existing['foto_bukti'];
        if ($file && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $newFoto = self::uploadBuktiFile($file);
            if ($newFoto) {
                // Hapus foto lama jika ada
                if ($fotoBukti) {
                    $oldPath = BASE_PATH . '/public/' . ltrim($fotoBukti, '/');
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $fotoBukti = $newFoto;
            }
        }

        $db->query("
            UPDATE jurnal_staff SET
                tanggal = ?, jam_id = ?, jam_rentang = ?, tugas_id = ?, tugas_ids = ?,
                aktivitas = ?, uraian_hasil = ?, kendala = ?, foto_bukti = ?, status = ?
            WHERE id = ?
        ", [
            $tanggal,
            $jamId,
            $jamRentang,
            $tugasId,
            $tugasIdsStr,
            $aktivitas,
            $uraianHasil,
            $kendala,
            $fotoBukti,
            $status,
            $id
        ]);

        return true;
    }

    /**
     * Hapus jurnal staff
     */
    public static function deleteJurnal(int $id): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $existing = self::getJurnalById($id);
        if ($existing && !empty($existing['foto_bukti'])) {
            $oldPath = BASE_PATH . '/public/' . ltrim($existing['foto_bukti'], '/');
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }
        $db->query("DELETE FROM jurnal_staff WHERE id = ?", [$id]);
        return true;
    }

    /**
     * Update status jurnal
     */
    public static function updateStatusJurnal(int $id, string $status): bool
    {
        self::ensureTablesExist();
        if (!in_array($status, ['selesai', 'proses', 'tertunda'], true)) {
            throw new Exception("Status tidak valid.");
        }
        $db = Database::getInstance();
        $db->query("UPDATE jurnal_staff SET status = ? WHERE id = ?", [$status, $id]);
        return true;
    }

    /**
     * =========================================================================
     * PENGATURAN JAM / SESI KERJA STAFF
     * =========================================================================
     */

    public static function getJamList(bool $onlyActive = false): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $sql = "SELECT * FROM jurnal_staff_jam";
        if ($onlyActive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY urutan ASC, jam_mulai ASC";
        return $db->findAll($sql);
    }

    public static function getJamById(int $id): ?array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $res = $db->find("SELECT * FROM jurnal_staff_jam WHERE id = ?", [$id]);
        return $res ?: null;
    }

    public static function storeJam(array $data): int
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $jamMulai = trim($data['jam_mulai'] ?? '');
        $jamSelesai = trim($data['jam_selesai'] ?? '');
        $namaSesi = trim($data['nama_sesi'] ?? '');
        $tipe = in_array($data['tipe'] ?? '', ['tilawah', 'aktifitas', 'istirahat', 'lainnya'], true) ? $data['tipe'] : 'aktifitas';
        $keterangan = trim($data['keterangan'] ?? '');
        $urutan = (int)($data['urutan'] ?? 1);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if ($jamMulai === '' || $jamSelesai === '') {
            throw new Exception("Jam mulai dan jam selesai wajib diisi.");
        }
        if ($namaSesi === '') {
            throw new Exception("Nama sesi jam kerja wajib diisi.");
        }

        // Format to H:i:s
        $jamMulai = date('H:i:s', strtotime($jamMulai));
        $jamSelesai = date('H:i:s', strtotime($jamSelesai));

        $db->query("
            INSERT INTO jurnal_staff_jam (jam_mulai, jam_selesai, nama_sesi, tipe, keterangan, urutan, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ", [$jamMulai, $jamSelesai, $namaSesi, $tipe, $keterangan, $urutan, $isActive]);

        return (int)$db->lastInsertId();
    }

    public static function updateJam(int $id, array $data): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $jamMulai = trim($data['jam_mulai'] ?? '');
        $jamSelesai = trim($data['jam_selesai'] ?? '');
        $namaSesi = trim($data['nama_sesi'] ?? '');
        $tipe = in_array($data['tipe'] ?? '', ['tilawah', 'aktifitas', 'istirahat', 'lainnya'], true) ? $data['tipe'] : 'aktifitas';
        $keterangan = trim($data['keterangan'] ?? '');
        $urutan = (int)($data['urutan'] ?? 1);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if ($jamMulai === '' || $jamSelesai === '') {
            throw new Exception("Jam mulai dan jam selesai wajib diisi.");
        }
        if ($namaSesi === '') {
            throw new Exception("Nama sesi jam kerja wajib diisi.");
        }

        $jamMulai = date('H:i:s', strtotime($jamMulai));
        $jamSelesai = date('H:i:s', strtotime($jamSelesai));

        $db->query("
            UPDATE jurnal_staff_jam SET
                jam_mulai = ?, jam_selesai = ?, nama_sesi = ?, tipe = ?,
                keterangan = ?, urutan = ?, is_active = ?
            WHERE id = ?
        ", [$jamMulai, $jamSelesai, $namaSesi, $tipe, $keterangan, $urutan, $isActive, $id]);

        return true;
    }

    public static function deleteJam(int $id): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $db->query("DELETE FROM jurnal_staff_jam WHERE id = ?", [$id]);
        return true;
    }

    public static function toggleJam(int $id): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $db->query("UPDATE jurnal_staff_jam SET is_active = 1 - is_active WHERE id = ?", [$id]);
        return true;
    }

    /**
     * =========================================================================
     * PENGATURAN TUGAS / JOB DESK PER JABATAN REFERENSI
     * =========================================================================
     */

    public static function getMasterJabatanList(string $kategori = 'support_system'): array
    {
        $db = Database::getInstance();
        try {
            $hasKategori = $db->find("SHOW COLUMNS FROM master_jabatan LIKE 'kategori'");
            if ($hasKategori) {
                if ($kategori === 'all') {
                    return $db->findAll("SELECT id, nama, kategori FROM master_jabatan ORDER BY nama ASC");
                }
                return $db->findAll("SELECT id, nama, kategori FROM master_jabatan WHERE kategori = ? ORDER BY nama ASC", [$kategori]);
            }
        } catch (Throwable $e) {}

        return $db->findAll("SELECT id, nama FROM master_jabatan ORDER BY nama ASC");
    }

    public static function getJabatanById(int $id): ?array
    {
        $db = Database::getInstance();
        try {
            $hasKategori = $db->find("SHOW COLUMNS FROM master_jabatan LIKE 'kategori'");
            if ($hasKategori) {
                $res = $db->find("SELECT id, nama, kategori FROM master_jabatan WHERE id = ?", [$id]);
                return $res ?: null;
            }
        } catch (Throwable $e) {}

        $res = $db->find("SELECT id, nama FROM master_jabatan WHERE id = ?", [$id]);
        return $res ?: null;
    }

    public static function getTugasCountPerJabatan(): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $rows = $db->findAll("
            SELECT jabatan_id, COUNT(*) as count_tugas,
                   SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as count_active
            FROM jurnal_staff_tugas
            GROUP BY jabatan_id
        ");
        $res = [];
        foreach ($rows as $r) {
            $res[(int)$r['jabatan_id']] = [
                'count_tugas'  => (int)$r['count_tugas'],
                'count_active' => (int)$r['count_active'],
            ];
        }
        return $res;
    }

    public static function getTugasByJabatan(int $jabatanId, bool $onlyActive = false): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $sql = "SELECT * FROM jurnal_staff_tugas WHERE jabatan_id = ?";
        if ($onlyActive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY kategori_tugas ASC, id ASC";
        return $db->findAll($sql, [$jabatanId]);
    }

    public static function getTugasById(int $id): ?array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $res = $db->find("SELECT * FROM jurnal_staff_tugas WHERE id = ?", [$id]);
        return $res ?: null;
    }

    public static function storeTugas(array $data): int
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $jabatanId = (int)($data['jabatan_id'] ?? 0);
        $jabatan = self::getJabatanById($jabatanId);
        if (!$jabatan) {
            throw new Exception("Jabatan referensi tidak ditemukan.");
        }

        $namaTugas = trim($data['nama_tugas'] ?? '');
        if ($namaTugas === '') {
            throw new Exception("Nama tugas wajib diisi.");
        }

        $deskripsi = trim($data['deskripsi'] ?? '');
        $kategori = in_array($data['kategori_tugas'] ?? '', ['rutin', 'berkala', 'insidental'], true) ? $data['kategori_tugas'] : 'rutin';
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $db->query("
            INSERT INTO jurnal_staff_tugas (jabatan_id, nama_jabatan, nama_tugas, deskripsi, kategori_tugas, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [$jabatanId, $jabatan['nama'], $namaTugas, $deskripsi, $kategori, $isActive]);

        return (int)$db->lastInsertId();
    }

    public static function updateTugas(int $id, array $data): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();

        $existing = self::getTugasById($id);
        if (!$existing) {
            throw new Exception("Tugas tidak ditemukan.");
        }

        $jabatanId = !empty($data['jabatan_id']) ? (int)$data['jabatan_id'] : (int)$existing['jabatan_id'];
        $jabatan = self::getJabatanById($jabatanId);
        $namaJabatan = $jabatan ? $jabatan['nama'] : $existing['nama_jabatan'];

        $namaTugas = trim($data['nama_tugas'] ?? '');
        if ($namaTugas === '') {
            throw new Exception("Nama tugas wajib diisi.");
        }

        $deskripsi = trim($data['deskripsi'] ?? '');
        $kategori = in_array($data['kategori_tugas'] ?? '', ['rutin', 'berkala', 'insidental'], true) ? $data['kategori_tugas'] : 'rutin';
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : (int)$existing['is_active'];

        $db->query("
            UPDATE jurnal_staff_tugas SET
                jabatan_id = ?, nama_jabatan = ?, nama_tugas = ?,
                deskripsi = ?, kategori_tugas = ?, is_active = ?
            WHERE id = ?
        ", [$jabatanId, $namaJabatan, $namaTugas, $deskripsi, $kategori, $isActive, $id]);

        return true;
    }

    public static function deleteTugas(int $id): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $db->query("DELETE FROM jurnal_staff_tugas WHERE id = ?", [$id]);
        return true;
    }

    public static function toggleTugas(int $id): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $db->query("UPDATE jurnal_staff_tugas SET is_active = 1 - is_active WHERE id = ?", [$id]);
        return true;
    }

    /**
     * =========================================================================
     * PENGATURAN PERIODE
     * =========================================================================
     */

    public static function getPengaturanPeriode(): array
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $cfg = $db->find("SELECT * FROM jurnal_staff_pengaturan LIMIT 1");
        if (!$cfg) {
            return [
                'periode_awal'  => date('Y-m-01'),
                'periode_akhir' => date('Y-m-t'),
                'keterangan'    => 'Default Periode Rekap Jurnal Staff'
            ];
        }
        return $cfg;
    }

    public static function savePengaturanPeriode(string $awal, string $akhir, ?string $keterangan = null): bool
    {
        self::ensureTablesExist();
        $db = Database::getInstance();
        $cfg = $db->find("SELECT id FROM jurnal_staff_pengaturan LIMIT 1");
        if ($cfg) {
            $db->query("
                UPDATE jurnal_staff_pengaturan 
                SET periode_awal = ?, periode_akhir = ?, keterangan = ?
                WHERE id = ?
            ", [$awal, $akhir, $keterangan, $cfg['id']]);
        } else {
            $db->query("
                INSERT INTO jurnal_staff_pengaturan (periode_awal, periode_akhir, keterangan)
                VALUES (?, ?, ?)
            ", [$awal, $akhir, $keterangan]);
        }
        return true;
    }

    /**
     * =========================================================================
     * REFERENSI PEGAWAI / STAFF
     * =========================================================================
     */

    /**
     * Build collation-safe SQL condition identifying Support System employees
     */
    private static function buildSupportStaffCondition(array &$params): string
    {
        $jabs = self::getMasterJabatanList('support_system');
        $names = [];
        foreach ($jabs as $j) {
            $n = strtolower(trim((string)($j['nama'] ?? '')));
            if ($n !== '') {
                $names[] = $n;
            }
        }
        $names = array_values(array_unique($names));
        if (!empty($names)) {
            $placeholders = implode(',', array_fill(0, count($names), '?'));
            foreach ($names as $n) {
                $params[] = $n;
            }
            return "(p.jenis_pegawai = 'Support System' OR LOWER(TRIM(p.jabatan)) IN ({$placeholders}))";
        }
        return "(p.jenis_pegawai = 'Support System' OR (p.jabatan IS NOT NULL AND p.jabatan != '' AND p.jabatan NOT LIKE '%Guru%' AND p.jabatan NOT LIKE '%Wali Kelas%'))";
    }

    public static function getStaffList(): array
    {
        $db = Database::getInstance();
        $params = [];
        $cond = self::buildSupportStaffCondition($params);
        $rows = $db->findAll("
            SELECT p.id, p.nama, p.niy, p.jabatan, p.unit_tugas, p.jenis_pegawai, p.foto
            FROM pegawai p
            WHERE p.is_active = 1 AND ({$cond})
            ORDER BY p.nama ASC
        ", $params);
        if (!empty($rows)) {
            return $rows;
        }

        return $db->findAll("
            SELECT id, nama, niy, jabatan, unit_tugas, jenis_pegawai, foto
            FROM pegawai
            WHERE is_active = 1
            ORDER BY nama ASC
        ");
    }

    public static function getStaffById(int $id): ?array
    {
        $db = Database::getInstance();
        $res = $db->find("
            SELECT id, nama, niy, jabatan, unit_tugas, jenis_pegawai, foto
            FROM pegawai
            WHERE id = ?
        ", [$id]);
        if ($res) {
            return $res;
        }

        // Fallback jika ID adalah ID user dari tabel users (misal admin / akun login staf)
        $user = $db->find("SELECT id, username, full_name, role, email FROM users WHERE id = ? LIMIT 1", [$id]);
        if ($user) {
            if (!empty($user['email'])) {
                $p = $db->find("SELECT id, nama, niy, jabatan, unit_tugas, jenis_pegawai, foto FROM pegawai WHERE email = ? LIMIT 1", [$user['email']]);
                if ($p) return $p;
            }
            if (!empty($user['full_name'])) {
                $p = $db->find("SELECT id, nama, niy, jabatan, unit_tugas, jenis_pegawai, foto FROM pegawai WHERE nama = ? LIMIT 1", [$user['full_name']]);
                if ($p) return $p;
            }
            return [
                'id' => (int)$user['id'],
                'nama' => !empty($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Staff'),
                'niy' => '-',
                'jabatan' => ucfirst($user['role'] ?? 'Staff'),
                'unit_tugas' => 'Support System',
                'jenis_pegawai' => 'staff',
                'foto' => null
            ];
        }

        return null;
    }

    /**
     * Helper upload foto bukti (Public agar bisa dipakai controller mobile)
     */
    public static function uploadBuktiFile(array $file): ?string
    {
        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $mime = null;
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = @finfo_file($finfo, $file['tmp_name']);
                @finfo_close($finfo);
            }
        }
        if (!$mime && function_exists('mime_content_type')) {
            $mime = @mime_content_type($file['tmp_name']);
        }
        if (!$mime) {
            $mime = strtolower((string)($file['type'] ?? ''));
        }

        $rawExt = pathinfo($file['name'] ?? '', PATHINFO_EXTENSION);
        $ext = strtolower($rawExt ?: '');
        if (!$ext) {
            $ext = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
        }

        $validMime = !empty($mime) && in_array($mime, $allowedTypes, true);
        $validExt = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
        if (!$validMime && !$validExt) {
            return null;
        }

        $uploadDir = BASE_PATH . '/public/uploads/jurnal-staff/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        @chmod($uploadDir, 0777);

        $safeExt = in_array($ext, ['jpg', 'jpeg'], true) ? 'jpg' : ($ext === 'webp' ? 'webp' : 'png');
        $filename = 'bukti_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $safeExt;
        $dest = $uploadDir . $filename;

        if (@move_uploaded_file($file['tmp_name'], $dest)) {
            return 'uploads/jurnal-staff/' . $filename;
        }

        return null;
    }

    /**
     * Helper simpan foto bukti dari data URL Base64 hasil kamera browser
     */
    public static function saveBase64Bukti(string $dataUrl): ?string
    {
        $dataUrl = trim($dataUrl);
        if (empty($dataUrl) || strpos($dataUrl, 'base64,') === false) {
            return null;
        }

        $parts = explode('base64,', $dataUrl, 2);
        $header = strtolower($parts[0] ?? '');
        $base64 = trim($parts[1] ?? '');

        $ext = 'jpg';
        if (strpos($header, 'png') !== false) {
            $ext = 'png';
        } elseif (strpos($header, 'webp') !== false) {
            $ext = 'webp';
        }

        $binary = base64_decode($base64, true);
        if ($binary === false || strlen($binary) === 0 || strlen($binary) > 10 * 1024 * 1024) {
            return null;
        }

        $uploadDir = BASE_PATH . '/public/uploads/jurnal-staff/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        @chmod($uploadDir, 0777);

        $filename = 'bukti_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $uploadDir . $filename;

        if (@file_put_contents($dest, $binary) !== false) {
            return 'uploads/jurnal-staff/' . $filename;
        }

        return null;
    }

    private static function getNamaHariIndo(string $englishDay): string
    {
        $map = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        return $map[$englishDay] ?? $englishDay;
    }

    private static function getNamaHariShortIndo(string $englishDay): string
    {
        $map = [
            'Sunday'    => 'Min',
            'Monday'    => 'Sen',
            'Tuesday'   => 'Sel',
            'Wednesday' => 'Rab',
            'Thursday'  => 'Kam',
            'Friday'    => 'Jum',
            'Saturday'  => 'Sab',
        ];
        return $map[$englishDay] ?? substr($englishDay, 0, 3);
    }

    private static function getBulanShortIndo(int $monthNum): string
    {
        $map = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];
        return $map[$monthNum] ?? '';
    }

    /**
     * Normalisasi string rentang jam "08.00 - 09.00" atau "08:00:00 - 09:00:00" menjadi "08:00-09:00"
     */
    public static function normalizeRentangJam(string $rentang): string
    {
        $clean = trim($rentang);
        if ($clean === '') {
            return '';
        }
        if (preg_match('/(\d{1,2})[:.](\d{2})(?::\d{2})?\s*-\s*(\d{1,2})[:.](\d{2})(?::\d{2})?/', $clean, $m)) {
            return sprintf('%02d:%02d-%02d:%02d', (int)$m[1], (int)$m[2], (int)$m[3], (int)$m[4]);
        }
        return strtolower(preg_replace('/\s+/', '', str_replace('.', ':', $clean)));
    }

    /**
     * Menghitung Rekap Jurnal (Persentase per Staff) & Matrix Ceklis Harian (Terisi / Sebagian / Tidak Terisi / Libur / Cuti)
     * Sinkron otomatis dengan Hari Minggu, Hari Libur di Absen Pegawai, dan data Cuti/Izin.
     */
    public static function getRekapAndMatrixData(array $filters = []): array
    {
        self::ensureTablesExist();
        require_once MODULES_PATH . '/kelola-absen-pegawai/models/AbsenPegawaiModel.php';
        require_once MODULES_PATH . '/kelola-jurnal-staff/services/JurnalStaffReminderService.php';

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $today = date('Y-m-d');
        $startDate = !empty($filters['start_date']) ? trim((string)$filters['start_date']) : date('Y-m-01');
        $endDate   = !empty($filters['end_date']) ? trim((string)$filters['end_date']) : date('Y-m-t');
        if (strtotime($startDate) > strtotime($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $filterJabatan   = trim((string)($filters['jabatan'] ?? ''));
        $filterPegawaiId = !empty($filters['pegawai_id']) ? (int)$filters['pegawai_id'] : null;
        $filterSearch    = trim((string)($filters['search'] ?? ''));
        $filterStatus    = trim((string)($filters['status'] ?? ''));

        // 1. Ambil daftar slot jam kerja aktif
        $remSettings = JurnalStaffReminderService::getSettings();
        $excludeIstirahat = (($remSettings['jurnal_staff_wa_exclude_istirahat'] ?? '0') === '1');
        $activeJams = self::getJamList(true);
        if ($excludeIstirahat) {
            $activeJams = array_values(array_filter(
                $activeJams,
                static fn($j) => strtolower((string)($j['tipe'] ?? '')) !== 'istirahat'
            ));
        }

        $requiredJamMapById = [];
        $requiredJamMapByRentang = [];
        $requiredJamLabels = [];
        foreach ($activeJams as $jm) {
            $jid = (int)$jm['id'];
            $lbl = date('H.i', strtotime($jm['jam_mulai'])) . ' - ' . date('H.i', strtotime($jm['jam_selesai']));
            $norm = self::normalizeRentangJam($jm['jam_mulai'] . ' - ' . $jm['jam_selesai']);
            $requiredJamMapById[$jid] = [
                'id'        => $jid,
                'label'     => $lbl,
                'nama_sesi' => $jm['nama_sesi'] ?? '',
                'norm'      => $norm,
            ];
            if ($norm !== '') {
                $requiredJamMapByRentang[$norm] = $jid;
            }
            $requiredJamLabels[$jid] = $lbl . (!empty($jm['nama_sesi']) ? ' (' . $jm['nama_sesi'] . ')' : '');
        }
        $sesiPerHari = count($requiredJamMapById);
        if ($sesiPerHari <= 0) {
            $sesiPerHari = 8;
        }

        // 2. Ambil daftar staff support system (lengkap dengan jabatan efektif & no_wa)
        $allSupportStaff = JurnalStaffReminderService::getTargetSupportStaff();
        if (empty($allSupportStaff)) {
            $allSupportStaff = self::getStaffList();
        }

        // Filter daftar staff sesuai filter jabatan, pegawai_id, dan search
        $filteredStaff = [];
        foreach ($allSupportStaff as $st) {
            $pid = (int)($st['id'] ?? 0);
            if ($pid <= 0) continue;

            if ($filterPegawaiId !== null && $pid !== $filterPegawaiId) {
                continue;
            }
            $stJab = trim((string)($st['jabatan'] ?? ''));
            if ($filterJabatan !== '' && strcasecmp($stJab, $filterJabatan) !== 0) {
                continue;
            }
            if ($filterSearch !== '') {
                $haystack = strtolower(($st['nama'] ?? '') . ' ' . $stJab . ' ' . ($st['niy'] ?? '') . ' ' . ($st['unit_tugas'] ?? ''));
                if (strpos($haystack, strtolower($filterSearch)) === false) {
                    // Cek apakah ada jurnal milik staff ini yang cocok dengan kata kunci search
                    // (akan dicek di bawah bila tidak cocok di profil)
                    $st['_check_journal_search'] = true;
                }
            }
            $filteredStaff[$pid] = $st;
        }

        // 3. Bangun daftar tanggal dalam rentang periode (maks 62 hari agar matriks tetap responsif)
        $liburMap = AbsenPegawaiModel::getLiburMapInRange($startDate, $endDate);
        $allDates = [];
        $matrixDates = [];
        $curTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        $hariKerjaElapsedCount = 0;
        $hariKerjaTotalCount = 0;
        $hariLiburKhususCount = 0;
        $hariMingguCount = 0;

        while ($curTs <= $endTs) {
            $dStr = date('Y-m-d', $curTs);
            $w = (int)date('w', $curTs);
            $isSunday = ($w === 0);
            $liburInfo = AbsenPegawaiModel::getLiburInfoForDate($dStr, $liburMap);
            $isHoliday = ($liburInfo !== null);
            $isFuture = ($dStr > $today);
            $isToday = ($dStr === $today);

            if ($isSunday) {
                $hariMingguCount++;
            } elseif ($isHoliday) {
                $hariLiburKhususCount++;
            } else {
                $hariKerjaTotalCount++;
                if (!$isFuture) {
                    $hariKerjaElapsedCount++;
                }
            }

            $engDay = date('l', $curTs);
            $dateMeta = [
                'date'         => $dStr,
                'day_num'      => date('d', $curTs),
                'month_short'  => self::getBulanShortIndo((int)date('n', $curTs)),
                'hari_indo'    => self::getNamaHariIndo($engDay),
                'hari_short'   => self::getNamaHariShortIndo($engDay),
                'is_sunday'    => $isSunday,
                'is_holiday'   => $isHoliday,
                'holiday_name' => $liburInfo['nama_libur'] ?? null,
                'is_future'    => $isFuture,
                'is_today'     => $isToday,
                'is_workday'   => (!$isSunday && !$isHoliday),
            ];
            $allDates[$dStr] = $dateMeta;
            $matrixDates[] = $dateMeta;

            $curTs = strtotime('+1 day', $curTs);
        }

        // 4. Ambil seluruh catatan jurnal staff pada periode tersebut
        $jWhere = ['js.tanggal BETWEEN ? AND ?'];
        $jParams = [$startDate, $endDate];
        if ($filterPegawaiId !== null) {
            $jWhere[] = 'js.pegawai_id = ?';
            $jParams[] = $filterPegawaiId;
        }
        if ($filterStatus !== '') {
            $jWhere[] = 'js.status = ?';
            $jParams[] = $filterStatus;
        }
        if ($filterSearch !== '') {
            $sLike = '%' . $filterSearch . '%';
            $jWhere[] = '(js.kode_jurnal LIKE ? OR js.nama_staff LIKE ? OR js.jabatan LIKE ? OR js.aktivitas LIKE ? OR js.uraian_hasil LIKE ?)';
            $jParams = array_merge($jParams, [$sLike, $sLike, $sLike, $sLike, $sLike]);
        }

        $jSql = "
            SELECT js.id, js.pegawai_id, js.tanggal, js.jam_id, js.jam_rentang, js.aktivitas, js.status
            FROM jurnal_staff js
            WHERE " . implode(' AND ', $jWhere) . "
            ORDER BY js.tanggal ASC, js.id ASC
        ";
        $jStmt = $pdo->prepare($jSql);
        $jStmt->execute($jParams);
        $journalRows = $jStmt->fetchAll(PDO::FETCH_ASSOC);

        // Kelompokkan jurnal per pegawai_id & tanggal
        $journalsByStaffDate = [];
        $staffHasMatchingJournal = [];
        foreach ($journalRows as $jr) {
            $pid = (int)$jr['pegawai_id'];
            $tgl = $jr['tanggal'];
            $journalsByStaffDate[$pid][$tgl][] = $jr;
            $staffHasMatchingJournal[$pid] = true;
        }

        // Jika ada filterSearch, buang staff yang tidak cocok profil maupun jurnalnya
        if ($filterSearch !== '') {
            foreach ($filteredStaff as $pid => $st) {
                if (!empty($st['_check_journal_search']) && empty($staffHasMatchingJournal[$pid])) {
                    unset($filteredStaff[$pid]);
                }
            }
        }

        // 5. Hitung rekap per staff dan sel matrix per tanggal
        $rekapStaffList = [];
        $matrixRows = [];
        $unfilledDaysList = [];

        $summaryTotalTargetSesi = 0;
        $summaryTotalTerisiSesi = 0;
        $summaryTotalKosongSesi = 0;
        $summaryTotalCatatan = 0;
        $summaryStaffLengkap = 0;
        $summaryStaffSebagian = 0;
        $summaryStaffKosong = 0;

        foreach ($filteredStaff as $pid => $st) {
            $hariKerjaStaff = 0;
            $hariCutiIzinStaff = 0;
            $hariLengkapStaff = 0;
            $hariSebagianStaff = 0;
            $hariKosongStaff = 0;
            $terisiSesiStaff = 0;
            $totalCatatanStaff = 0;
            $totalTugasStaff = 0;

            $staffDatesMatrix = [];

            foreach ($allDates as $dStr => $dMeta) {
                $dayRows = $journalsByStaffDate[$pid][$dStr] ?? [];
                $filledReqIds = [];
                $extraSlots = 0;
                $dayActivities = [];
                $dayCatatanCount = count($dayRows);
                $totalCatatanStaff += $dayCatatanCount;

                foreach ($dayRows as $dr) {
                    $aktParts = self::splitAktivitasList($dr['aktivitas'] ?? '');
                    $totalTugasStaff += max(1, count($aktParts));
                    foreach ($aktParts as $ap) {
                        if (!in_array($ap, $dayActivities, true)) {
                            $dayActivities[] = $ap;
                        }
                    }

                    $jid = !empty($dr['jam_id']) ? (int)$dr['jam_id'] : 0;
                    if ($jid > 0 && isset($requiredJamMapById[$jid])) {
                        $filledReqIds[$jid] = true;
                    } else {
                        $normR = self::normalizeRentangJam((string)($dr['jam_rentang'] ?? ''));
                        if ($normR !== '' && isset($requiredJamMapByRentang[$normR])) {
                            $filledReqIds[$requiredJamMapByRentang[$normR]] = true;
                        } else {
                            $extraSlots++;
                        }
                    }
                }

                $filledCount = min($sesiPerHari, count($filledReqIds) + $extraSlots);
                $missingLabels = [];
                foreach ($requiredJamLabels as $rJid => $rLbl) {
                    if (!isset($filledReqIds[$rJid])) {
                        $missingLabels[] = $rLbl;
                    }
                }

                if (!$dMeta['is_workday']) {
                    // Hari Minggu atau Hari Libur Nasional/Sekolah
                    $cellStatus = $filledCount > 0 ? ($filledCount >= $sesiPerHari ? 'filled' : 'partial') : 'holiday';
                    $terisiSesiStaff += $filledCount;
                    $staffDatesMatrix[$dStr] = [
                        'status'         => $cellStatus,
                        'filled_count'   => $filledCount,
                        'expected_count' => 0,
                        'missing_count'  => 0,
                        'details'        => !empty($dayActivities) ? implode(' • ', array_slice($dayActivities, 0, 4)) : ($dMeta['holiday_name'] ?? 'Hari Libur'),
                        'missing_jams'   => [],
                        'holiday_name'   => $dMeta['holiday_name'] ?? 'Hari Libur',
                    ];
                    continue;
                }

                if ($dMeta['is_future']) {
                    // Hari kerja yang belum berlangsung
                    $cellStatus = $filledCount > 0 ? ($filledCount >= $sesiPerHari ? 'filled' : 'partial') : 'future';
                    $terisiSesiStaff += $filledCount;
                    $staffDatesMatrix[$dStr] = [
                        'status'         => $cellStatus,
                        'filled_count'   => $filledCount,
                        'expected_count' => $sesiPerHari,
                        'missing_count'  => 0,
                        'details'        => !empty($dayActivities) ? implode(' • ', array_slice($dayActivities, 0, 4)) : 'Belum berlangsung',
                        'missing_jams'   => [],
                    ];
                    continue;
                }

                // Cek apakah staff sedang Cuti / Izin / Sakit / Dinas Luar pada tanggal ini
                $excusedReason = JurnalStaffReminderService::getStaffExcusedReason($pid, $dStr);
                if ($excusedReason !== null && $filledCount === 0) {
                    $hariCutiIzinStaff++;
                    $staffDatesMatrix[$dStr] = [
                        'status'         => 'excused',
                        'excused_reason' => $excusedReason,
                        'filled_count'   => 0,
                        'expected_count' => 0,
                        'missing_count'  => 0,
                        'details'        => 'Dikecualikan (' . $excusedReason . ')',
                        'missing_jams'   => [],
                    ];
                    continue;
                }

                // Hari kerja aktif yang wajib diisi
                $hariKerjaStaff++;
                $terisiSesiStaff += $filledCount;
                $missingCount = max(0, $sesiPerHari - $filledCount);

                if ($filledCount >= $sesiPerHari) {
                    $cellStatus = 'filled';
                    $hariLengkapStaff++;
                } elseif ($filledCount > 0) {
                    $cellStatus = 'partial';
                    $hariSebagianStaff++;
                    $unfilledDaysList[] = [
                        'pegawai_id'     => $pid,
                        'nama'           => $st['nama'],
                        'niy'            => $st['niy'] ?? '-',
                        'jabatan'        => $st['jabatan'] ?? 'Staff',
                        'unit_tugas'     => $st['unit_tugas'] ?? 'Yayasan',
                        'no_wa'          => $st['no_wa_formatted'] ?? ($st['no_wa_raw'] ?? ''),
                        'tanggal'        => $dStr,
                        'hari'           => $dMeta['hari_indo'],
                        'status_hari'    => 'sebagian',
                        'filled_count'   => $filledCount,
                        'expected_count' => $sesiPerHari,
                        'missing_count'  => $missingCount,
                        'missing_jams'   => $missingLabels,
                    ];
                } else {
                    $cellStatus = 'unfilled';
                    $hariKosongStaff++;
                    $unfilledDaysList[] = [
                        'pegawai_id'     => $pid,
                        'nama'           => $st['nama'],
                        'niy'            => $st['niy'] ?? '-',
                        'jabatan'        => $st['jabatan'] ?? 'Staff',
                        'unit_tugas'     => $st['unit_tugas'] ?? 'Yayasan',
                        'no_wa'          => $st['no_wa_formatted'] ?? ($st['no_wa_raw'] ?? ''),
                        'tanggal'        => $dStr,
                        'hari'           => $dMeta['hari_indo'],
                        'status_hari'    => 'kosong',
                        'filled_count'   => 0,
                        'expected_count' => $sesiPerHari,
                        'missing_count'  => $sesiPerHari,
                        'missing_jams'   => $missingLabels,
                    ];
                }

                $staffDatesMatrix[$dStr] = [
                    'status'         => $cellStatus,
                    'filled_count'   => $filledCount,
                    'expected_count' => $sesiPerHari,
                    'missing_count'  => $missingCount,
                    'details'        => !empty($dayActivities) ? implode(' • ', array_slice($dayActivities, 0, 4)) : 'Belum mengisi jurnal',
                    'missing_jams'   => $missingLabels,
                ];
            }

            $targetSesiStaff = $hariKerjaStaff * $sesiPerHari;
            $cappedTerisiSesi = $targetSesiStaff > 0 ? min($targetSesiStaff, $terisiSesiStaff) : $terisiSesiStaff;
            $kosongSesiStaff = max(0, $targetSesiStaff - $cappedTerisiSesi);
            $hariTerisiStaff = $hariLengkapStaff + $hariSebagianStaff;

            $persentaseSesi = $targetSesiStaff > 0
                ? min(100.0, round(($cappedTerisiSesi / $targetSesiStaff) * 100, 1))
                : 0.0;
            $persentaseHari = $hariKerjaStaff > 0
                ? min(100.0, round(($hariTerisiStaff / $hariKerjaStaff) * 100, 1))
                : 0.0;

            $summaryTotalTargetSesi += $targetSesiStaff;
            $summaryTotalTerisiSesi += $cappedTerisiSesi;
            $summaryTotalKosongSesi += $kosongSesiStaff;
            $summaryTotalCatatan += $totalCatatanStaff;

            if ($persentaseSesi >= 80) {
                $summaryStaffLengkap++;
            } elseif ($persentaseSesi > 0) {
                $summaryStaffSebagian++;
            } else {
                $summaryStaffKosong++;
            }

            $rowSummary = [
                'pegawai_id'         => $pid,
                'nama'               => $st['nama'],
                'niy'                => $st['niy'] ?? '-',
                'jabatan'            => $st['jabatan'] ?: 'Staff Support System',
                'unit_tugas'         => $st['unit_tugas'] ?: 'Yayasan',
                'foto'               => $st['foto'] ?? null,
                'no_wa'              => $st['no_wa_formatted'] ?? ($st['no_wa_raw'] ?? ''),
                'hari_kerja_periode' => $hariKerjaElapsedCount,
                'hari_cuti_izin'     => $hariCutiIzinStaff,
                'hari_kerja_efektif' => $hariKerjaStaff,
                'sesi_per_hari'      => $sesiPerHari,
                'target_sesi'        => $targetSesiStaff,
                'terisi_sesi'        => $cappedTerisiSesi,
                'kosong_sesi'        => $kosongSesiStaff,
                'total_catatan'      => $totalCatatanStaff,
                'total_tugas'        => $totalTugasStaff,
                'hari_lengkap'       => $hariLengkapStaff,
                'hari_sebagian'      => $hariSebagianStaff,
                'hari_terisi'        => $hariTerisiStaff,
                'hari_kosong'        => $hariKosongStaff,
                'persentase'         => $persentaseSesi,
                'persentase_hari'    => $persentaseHari,
            ];

            $rekapStaffList[] = $rowSummary;
            $matrixRows[] = array_merge($rowSummary, [
                'dates' => $staffDatesMatrix,
            ]);
        }

        // Urutkan Rekap Persentase dari persentase tertinggi -> terisi_sesi tertinggi -> nama A-Z
        usort($rekapStaffList, static function (array $a, array $b): int {
            if ($b['persentase'] !== $a['persentase']) {
                return $b['persentase'] <=> $a['persentase'];
            }
            if ($b['terisi_sesi'] !== $a['terisi_sesi']) {
                return $b['terisi_sesi'] <=> $a['terisi_sesi'];
            }
            return strcasecmp($a['nama'], $b['nama']);
        });

        // Urutkan daftar hari belum lengkap dari tanggal terbaru
        usort($unfilledDaysList, static function (array $a, array $b): int {
            $cmpDate = strcmp($b['tanggal'], $a['tanggal']);
            if ($cmpDate !== 0) return $cmpDate;
            return strcasecmp($a['nama'], $b['nama']);
        });

        $avgPersentase = $summaryTotalTargetSesi > 0
            ? min(100.0, round(($summaryTotalTerisiSesi / $summaryTotalTargetSesi) * 100, 1))
            : 0.0;

        return [
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'sesi_per_hari'     => $sesiPerHari,
            'active_jams'       => $activeJams,
            'rekap_staff_list'  => $rekapStaffList,
            'matrix_dates'      => $matrixDates,
            'matrix_rows'       => $matrixRows,
            'unfilled_days'     => $unfilledDaysList,
            'summary'           => [
                'total_staff'          => count($rekapStaffList),
                'hari_kerja_total'     => $hariKerjaTotalCount,
                'hari_kerja_elapsed'   => $hariKerjaElapsedCount,
                'hari_libur_khusus'    => $hariLiburKhususCount,
                'hari_minggu'          => $hariMingguCount,
                'sesi_per_hari'        => $sesiPerHari,
                'total_target_sesi'    => $summaryTotalTargetSesi,
                'total_terisi_sesi'    => $summaryTotalTerisiSesi,
                'total_kosong_sesi'    => $summaryTotalKosongSesi,
                'total_catatan'        => $summaryTotalCatatan,
                'avg_persentase'       => $avgPersentase,
                'staff_baik'           => $summaryStaffLengkap,
                'staff_sebagian'       => $summaryStaffSebagian,
                'staff_kosong'         => $summaryStaffKosong,
                'unfilled_days_count'  => count($unfilledDaysList),
            ],
        ];
    }
}
