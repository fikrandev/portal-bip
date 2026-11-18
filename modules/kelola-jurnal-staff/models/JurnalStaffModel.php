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
                  `aktivitas` VARCHAR(255) NOT NULL,
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

        // Total Staff Support System spesifik (atau non-guru)
        $totalSupportStaff = (int)$pdo->query("
            SELECT COUNT(*) FROM pegawai 
            WHERE is_active = 1 
              AND (jenis_pegawai = 'Support System' OR (jabatan IS NOT NULL AND jabatan != '' AND jabatan NOT LIKE '%Guru%' AND jabatan NOT LIKE '%Wali Kelas%'))
        ")->fetchColumn();
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

        // 7. Tren 7 Hari Terakhir
        $trend7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $ts = strtotime($d);
            $dayNameIndo = self::getNamaHariIndo(date('l', $ts));
            $cnt = (int)$pdo->query("SELECT COUNT(*) FROM jurnal_staff WHERE tanggal = '{$d}'")->fetchColumn();
            $trend7Days[] = [
                'date'       => $d,
                'label'      => $dayNameIndo . ', ' . date('d/m', $ts),
                'short_date' => date('d M', $ts),
                'count'      => $cnt,
                'is_today'   => ($d === $today)
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
            'persen_hari_ini'         => $totalSupportStaff > 0 ? round(($staffIsiHariIni / $totalSupportStaff) * 100) : 0,
            'total_jurnal_periode'    => $totalJurnalPeriode,
            'total_selesai_periode'   => $totalSelesaiPeriode,
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
            throw new Exception("Data pegawai tidak ditemukan.");
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

        $tugasId = !empty($data['tugas_id']) ? (int)$data['tugas_id'] : null;
        $aktivitas = trim($data['aktivitas'] ?? '');
        if ($aktivitas === '' && $tugasId) {
            $tRow = self::getTugasById($tugasId);
            if ($tRow) {
                $aktivitas = $tRow['nama_tugas'];
            }
        }
        if ($aktivitas === '') {
            throw new Exception("Aktivitas / uraian tugas wajib diisi.");
        }

        $uraianHasil = trim($data['uraian_hasil'] ?? '');
        if ($uraianHasil === '') {
            throw new Exception("Uraian hasil kerja wajib diisi.");
        }

        $kendala = !empty($data['kendala']) ? trim($data['kendala']) : null;
        $status = in_array($data['status'] ?? '', ['selesai', 'proses', 'tertunda'], true) ? $data['status'] : 'selesai';

        // Kode jurnal unik: JSTF-YYYYMMDD-XXXX
        $randStr = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $kodeJurnal = 'JSTF-' . date('Ymd', strtotime($tanggal)) . '-' . $randStr;

        // Upload foto jika ada
        $fotoBukti = null;
        if ($file && !empty($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $fotoBukti = self::uploadBuktiFile($file);
        }

        $db->query("
            INSERT INTO jurnal_staff (
                kode_jurnal, pegawai_id, nama_staff, jabatan, unit_tugas,
                tanggal, jam_id, jam_rentang, tugas_id, aktivitas,
                uraian_hasil, kendala, foto_bukti, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $kodeJurnal,
            $pegawaiId,
            $pegawai['nama'],
            $pegawai['jabatan'] ?: 'Staff',
            $pegawai['unit_tugas'] ?: 'Bina Insan Palu',
            $tanggal,
            $jamId,
            $jamRentang,
            $tugasId,
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
        $tugasId = !empty($data['tugas_id']) ? (int)$data['tugas_id'] : $existing['tugas_id'];
        $aktivitas = !empty($data['aktivitas']) ? trim($data['aktivitas']) : $existing['aktivitas'];
        $uraianHasil = !empty($data['uraian_hasil']) ? trim($data['uraian_hasil']) : $existing['uraian_hasil'];
        $kendala = isset($data['kendala']) ? trim($data['kendala']) : $existing['kendala'];
        $status = in_array($data['status'] ?? '', ['selesai', 'proses', 'tertunda'], true) ? $data['status'] : $existing['status'];

        $fotoBukti = $existing['foto_bukti'];
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
                tanggal = ?, jam_id = ?, jam_rentang = ?, tugas_id = ?,
                aktivitas = ?, uraian_hasil = ?, kendala = ?, foto_bukti = ?, status = ?
            WHERE id = ?
        ", [
            $tanggal,
            $jamId,
            $jamRentang,
            $tugasId,
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

    public static function getStaffList(): array
    {
        $db = Database::getInstance();
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
        return $res ?: null;
    }

    /**
     * Helper upload foto bukti
     */
    private static function uploadBuktiFile(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes, true)) {
            return null;
        }

        $uploadDir = BASE_PATH . '/public/uploads/jurnal-staff/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $filename = 'bukti_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
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
}
