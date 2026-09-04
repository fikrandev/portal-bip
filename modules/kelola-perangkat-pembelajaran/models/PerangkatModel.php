<?php
/**
 * Perangkat Pembelajaran Model
 * 
 * Handles all database operations for Perangkat Pembelajaran:
 * - Kalender Pendidikan (Kaldik)
 * - Hari Efektif Sekolah (HES)
 * - Hari Efektif Belajar (HEB)
 * - Program Tahunan (Prota)
 * - Program Semester (Prosem)
 * - RPP / Modul Ajar
 * - Approval & Verification Logs
 */

class PerangkatModel
{
    private static function ensureTables(): void
    {
        $db = Database::getInstance();
        $db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `perangkat_pembelajaran` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `tahun_akademik_id` INT NOT NULL,
                `semester` ENUM('Ganjil','Genap') NOT NULL DEFAULT 'Ganjil',
                `unit` VARCHAR(20) NOT NULL DEFAULT 'SD',
                `kaldik_id` BIGINT UNSIGNED DEFAULT NULL,
                `guru_id` BIGINT UNSIGNED DEFAULT NULL,
                `guru_nama` VARCHAR(150) NOT NULL,
                `guru_nip` VARCHAR(50) DEFAULT NULL,
                `tipe` ENUM('kaldik','hes','heb','prota','prosem','rpp') NOT NULL,
                `judul` VARCHAR(255) NOT NULL,
                `mata_pelajaran` VARCHAR(100) DEFAULT NULL,
                `tingkat_kelas` VARCHAR(50) DEFAULT NULL,
                `fase` VARCHAR(20) DEFAULT NULL,
                `alokasi_waktu` VARCHAR(100) DEFAULT NULL,
                `konten_json` LONGTEXT DEFAULT NULL,
                `file_lampiran` VARCHAR(255) DEFAULT NULL,
                `status` ENUM('draft','diajukan','disetujui','ditolak') NOT NULL DEFAULT 'draft',
                `catatan_revisi` TEXT DEFAULT NULL,
                `approved_by` BIGINT UNSIGNED DEFAULT NULL,
                `approved_at` DATETIME DEFAULT NULL,
                `created_by` BIGINT UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_perangkat_tipe` (`tipe`),
                KEY `idx_perangkat_unit` (`unit`),
                KEY `idx_perangkat_kaldik_id` (`kaldik_id`),
                KEY `idx_perangkat_status` (`status`),
                KEY `idx_perangkat_ta_smt` (`tahun_akademik_id`, `semester`),
                KEY `idx_perangkat_guru` (`guru_id`),
                KEY `idx_perangkat_created_by` (`created_by`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // Schema migration guard
        try {
            $db->getConnection()->exec("ALTER TABLE `perangkat_pembelajaran` MODIFY COLUMN `tipe` VARCHAR(30) NOT NULL");
            $cols = $db->findAll("SHOW COLUMNS FROM `perangkat_pembelajaran` LIKE 'unit'");
            if (empty($cols)) {
                $db->getConnection()->exec("ALTER TABLE `perangkat_pembelajaran` ADD COLUMN `unit` VARCHAR(20) NOT NULL DEFAULT 'SD' AFTER `semester`");
                $db->getConnection()->exec("ALTER TABLE `perangkat_pembelajaran` ADD INDEX `idx_perangkat_unit` (`unit`)");
            }
            $colsKaldik = $db->findAll("SHOW COLUMNS FROM `perangkat_pembelajaran` LIKE 'kaldik_id'");
            if (empty($colsKaldik)) {
                $db->getConnection()->exec("ALTER TABLE `perangkat_pembelajaran` ADD COLUMN `kaldik_id` BIGINT UNSIGNED DEFAULT NULL AFTER `unit`");
                $db->getConnection()->exec("ALTER TABLE `perangkat_pembelajaran` ADD INDEX `idx_perangkat_kaldik_id` (`kaldik_id`)");
            }
            $colsActive = $db->findAll("SHOW COLUMNS FROM `perangkat_pembelajaran` LIKE 'is_active'");
            if (empty($colsActive)) {
                $db->getConnection()->exec("ALTER TABLE `perangkat_pembelajaran` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `unit`");
                $db->getConnection()->exec("ALTER TABLE `perangkat_pembelajaran` ADD INDEX `idx_perangkat_is_active` (`is_active`)");
            }
        } catch (Exception $e) {
            // Silently continue if alter fails
        }

        $db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `perangkat_approval_logs` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `perangkat_id` BIGINT UNSIGNED NOT NULL,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `user_nama` VARCHAR(150) NOT NULL,
                `aksi` ENUM('ajukan','setujui','tolak','revisi','draft') NOT NULL,
                `catatan` TEXT DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_pal_perangkat` (`perangkat_id`),
                KEY `idx_pal_user` (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    /**
     * Get 4 Standard Units with visual metadata
     */
    public static function getUnitList(): array
    {
        return [
            'PAUD' => [
                'name' => 'PAUD / TK',
                'alias' => 'PAUD',
                'desc' => 'Pendidikan Anak Usia Dini & TK',
                'icon' => '🧸',
                'badge' => 'bg-pink-100 text-pink-800 border-pink-300',
                'accent' => 'pink',
                'bg' => 'bg-pink-50',
                'bg_soft' => 'bg-pink-100 text-pink-700',
                'border' => 'border-pink-200'
            ],
            'SD' => [
                'name' => 'SD (Sekolah Dasar)',
                'alias' => 'SD',
                'desc' => 'Fase A (Kls 1-2), B (Kls 3-4), C (Kls 5-6)',
                'icon' => '🎒',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'accent' => 'emerald',
                'bg' => 'bg-emerald-50',
                'bg_soft' => 'bg-emerald-100 text-emerald-700',
                'border' => 'border-emerald-200'
            ],
            'SMP' => [
                'name' => 'SMP (Sekolah Menengah Pertama)',
                'alias' => 'SMP',
                'desc' => 'Fase D (Kelas VII, VIII, IX)',
                'icon' => '📚',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-300',
                'accent' => 'blue',
                'bg' => 'bg-blue-50',
                'bg_soft' => 'bg-blue-100 text-blue-700',
                'border' => 'border-blue-200'
            ],
            'SMA' => [
                'name' => 'SMA / SMK',
                'alias' => 'SMA',
                'desc' => 'Fase E (Kelas X), Fase F (Kelas XI-XII)',
                'icon' => '🎓',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-300',
                'accent' => 'purple',
                'bg' => 'bg-purple-50',
                'bg_soft' => 'bg-purple-100 text-purple-700',
                'border' => 'border-purple-200'
            ]
        ];
    }

    /**
     * Get Kaldik List by Unit (Sorted with Active first)
     */
    public static function getKaldikListByUnit(?string $unit = null, ?int $taId = null, ?string $semester = null, bool $onlyActive = false): array
    {
        self::ensureTables();
        $db = Database::getInstance();
        $where = ["tipe = 'kaldik'"];
        $params = [];

        if (!empty($unit)) {
            $where[] = "unit = ?";
            $params[] = $unit;
        }
        if (!empty($taId)) {
            $where[] = "tahun_akademik_id = ?";
            $params[] = (int) $taId;
        }
        if (!empty($semester)) {
            $where[] = "semester = ?";
            $params[] = $semester;
        }
        if ($onlyActive) {
            $where[] = "is_active = 1";
        }

        $whereClause = implode(' AND ', $where);
        return $db->findAll("SELECT id, judul, unit, semester, tahun_akademik_id, is_active, status, konten_json FROM perangkat_pembelajaran WHERE {$whereClause} ORDER BY is_active DESC, created_at DESC", $params);
    }

    /**
     * Toggle Active State of Kaldik Group
     */
    public static function toggleActive(int $id): bool
    {
        self::ensureTables();
        $db = Database::getInstance();
        $item = self::getById($id);
        if (!$item) return false;

        $newActive = empty($item['is_active']) ? 1 : 0;
        $db->update('perangkat_pembelajaran', ['is_active' => $newActive], 'id = ?', [$id]);
        return (bool)$newActive;
    }

    /**
     * Set a Kaldik as the only active one for a given unit
     */
    public static function setActiveOnly(int $id, string $unit, ?int $taId = null): void
    {
        self::ensureTables();
        $db = Database::getInstance();
        
        // Deactivate other kaldiks of the same unit
        $sql = "UPDATE perangkat_pembelajaran SET is_active = 0 WHERE tipe = 'kaldik' AND unit = ?";
        $params = [$unit];
        if (!empty($taId)) {
            $sql .= " AND tahun_akademik_id = ?";
            $params[] = (int)$taId;
        }
        $db->getConnection()->prepare($sql)->execute($params);

        // Activate the selected kaldik
        $db->update('perangkat_pembelajaran', ['is_active' => 1], 'id = ?', [$id]);
    }

    /**
     * Add an agenda item directly to a Kaldik's konten_json
     */
    public static function addAgenda(int $kaldikId, array $agendaData): bool
    {
        self::ensureTables();
        $db = Database::getInstance();
        $item = self::getById($kaldikId);
        if (!$item) return false;

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        if (!isset($konten['agendas']) || !is_array($konten['agendas'])) {
            $konten['agendas'] = [];
        }

        $konten['agendas'][] = [
            'tanggal_mulai' => $agendaData['tanggal_mulai'] ?? '',
            'tanggal_selesai' => $agendaData['tanggal_selesai'] ?? '',
            'kegiatan' => $agendaData['kegiatan'] ?? '',
            'kategori' => $agendaData['kategori'] ?? 'kbm',
            'semester' => $agendaData['semester'] ?? 'Ganjil',
            'keterangan' => $agendaData['keterangan'] ?? ''
        ];

        return self::update($kaldikId, ['konten_json' => $konten]);
    }

    /**
     * Delete an agenda item by index from Kaldik's konten_json
     */
    public static function deleteAgenda(int $kaldikId, int $agendaIndex): bool
    {
        self::ensureTables();
        $db = Database::getInstance();
        $item = self::getById($kaldikId);
        if (!$item) return false;

        $konten = !empty($item['konten_json']) ? json_decode($item['konten_json'], true) : [];
        if (!isset($konten['agendas']) || !is_array($konten['agendas']) || !isset($konten['agendas'][$agendaIndex])) {
            return false;
        }

        array_splice($konten['agendas'], $agendaIndex, 1);
        return self::update($kaldikId, ['konten_json' => $konten]);
    }

    /**
     * Get paginated perangkat by filters
     */
    public static function getAll(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        self::ensureTables();
        $db = Database::getInstance();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['tipe'])) {
            $where[] = 'p.tipe = ?';
            $params[] = $filters['tipe'];
        }

        if (!empty($filters['unit'])) {
            $where[] = 'p.unit = ?';
            $params[] = $filters['unit'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $where[] = 'p.is_active = ?';
            $params[] = (int) $filters['is_active'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['tahun_akademik_id'])) {
            $where[] = 'p.tahun_akademik_id = ?';
            $params[] = (int) $filters['tahun_akademik_id'];
        }

        if (!empty($filters['semester'])) {
            $where[] = 'p.semester = ?';
            $params[] = $filters['semester'];
        }

        if (!empty($filters['guru_id'])) {
            $where[] = 'p.guru_id = ?';
            $params[] = (int) $filters['guru_id'];
        }

        if (!empty($filters['mata_pelajaran'])) {
            $where[] = 'p.mata_pelajaran = ?';
            $params[] = $filters['mata_pelajaran'];
        }

        if (!empty($filters['tingkat_kelas'])) {
            $where[] = 'p.tingkat_kelas = ?';
            $params[] = $filters['tingkat_kelas'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(p.judul LIKE ? OR p.guru_nama LIKE ? OR p.mata_pelajaran LIKE ? OR p.tingkat_kelas LIKE ?)';
            $s = '%' . trim($filters['search']) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        // Filter by user role if not verifier/admin
        if (!empty($filters['my_only']) && !empty($filters['user_id'])) {
            $where[] = '(p.created_by = ? OR p.guru_id = ?)';
            $params[] = (int) $filters['user_id'];
            $params[] = (int) $filters['user_id'];
        }

        $whereClause = implode(' AND ', $where);
        $orderBy = !empty($filters['tipe']) && $filters['tipe'] === 'kaldik' 
            ? 'p.is_active DESC, p.updated_at DESC' 
            : 'p.updated_at DESC';

        $sql = "
            SELECT p.*, 
                   ta.nama_tahun,
                   u.full_name AS approver_name
            FROM perangkat_pembelajaran p
            LEFT JOIN tahun_akademik ta ON p.tahun_akademik_id = ta.id
            LEFT JOIN users u ON p.approved_by = u.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $db->findAll($sql, $params);
    }

    /**
     * Count records for pagination
     */
    public static function countAll(array $filters = []): int
    {
        self::ensureTables();
        $db = Database::getInstance();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['tipe'])) {
            $where[] = 'p.tipe = ?';
            $params[] = $filters['tipe'];
        }

        if (!empty($filters['unit'])) {
            $where[] = 'p.unit = ?';
            $params[] = $filters['unit'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $where[] = 'p.is_active = ?';
            $params[] = (int) $filters['is_active'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['tahun_akademik_id'])) {
            $where[] = 'p.tahun_akademik_id = ?';
            $params[] = (int) $filters['tahun_akademik_id'];
        }

        if (!empty($filters['semester'])) {
            $where[] = 'p.semester = ?';
            $params[] = $filters['semester'];
        }

        if (!empty($filters['guru_id'])) {
            $where[] = 'p.guru_id = ?';
            $params[] = (int) $filters['guru_id'];
        }

        if (!empty($filters['mata_pelajaran'])) {
            $where[] = 'p.mata_pelajaran = ?';
            $params[] = $filters['mata_pelajaran'];
        }

        if (!empty($filters['tingkat_kelas'])) {
            $where[] = 'p.tingkat_kelas = ?';
            $params[] = $filters['tingkat_kelas'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(p.judul LIKE ? OR p.guru_nama LIKE ? OR p.mata_pelajaran LIKE ? OR p.tingkat_kelas LIKE ?)';
            $s = '%' . trim($filters['search']) . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['my_only']) && !empty($filters['user_id'])) {
            $where[] = '(p.created_by = ? OR p.guru_id = ?)';
            $params[] = (int) $filters['user_id'];
            $params[] = (int) $filters['user_id'];
        }

        $whereClause = implode(' AND ', $where);
        $res = $db->find("SELECT COUNT(*) AS total FROM perangkat_pembelajaran p WHERE {$whereClause}", $params);
        return (int) ($res['total'] ?? 0);
    }

    /**
     * Get single record by ID
     */
    public static function getById(int $id): ?array
    {
        self::ensureTables();
        $db = Database::getInstance();
        $sql = "
            SELECT p.*, 
                   ta.nama_tahun,
                   u.full_name AS approver_name,
                   c.full_name AS creator_name
            FROM perangkat_pembelajaran p
            LEFT JOIN tahun_akademik ta ON p.tahun_akademik_id = ta.id
            LEFT JOIN users u ON p.approved_by = u.id
            LEFT JOIN users c ON p.created_by = c.id
            WHERE p.id = ?
            LIMIT 1
        ";
        return $db->find($sql, [$id]);
    }

    /**
     * Create new perangkat
     */
    public static function create(array $data): int
    {
        self::ensureTables();
        $db = Database::getInstance();

        if (isset($data['konten_json']) && is_array($data['konten_json'])) {
            $data['konten_json'] = json_encode($data['konten_json'], JSON_UNESCAPED_UNICODE);
        }

        $id = (int) $db->insert('perangkat_pembelajaran', $data);

        // Initial creation log
        $userId = $data['created_by'] ?? (class_exists('Auth') ? Auth::id() : null) ?? 1;
        $userName = (class_exists('Auth') ? Auth::name() : null) ?? 'Pengguna';
        self::addLog($id, $userId, $userName, 'draft', 'Dokumen dibuat sebagai draft.');

        return $id;
    }

    /**
     * Update existing perangkat
     */
    public static function update(int $id, array $data): bool
    {
        self::ensureTables();
        $db = Database::getInstance();

        if (isset($data['konten_json']) && is_array($data['konten_json'])) {
            $data['konten_json'] = json_encode($data['konten_json'], JSON_UNESCAPED_UNICODE);
        }

        $affected = $db->update('perangkat_pembelajaran', $data, 'id = ?', [$id]);
        return $affected >= 0;
    }

    /**
     * Delete perangkat
     */
    public static function delete(int $id): bool
    {
        self::ensureTables();
        $db = Database::getInstance();
        
        // Remove file if exists
        $item = self::getById($id);
        if ($item && !empty($item['file_lampiran'])) {
            $filePath = BASE_PATH . '/' . ltrim($item['file_lampiran'], '/');
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        $db->delete('perangkat_approval_logs', 'perangkat_id = ?', [$id]);
        return $db->delete('perangkat_pembelajaran', 'id = ?', [$id]) > 0;
    }

    /**
     * Update status (Approval / Reject / Submit / Draft)
     */
    public static function updateStatus(int $id, string $status, ?string $catatan = null, ?int $userId = null, ?string $userNama = null): bool
    {
        self::ensureTables();
        $db = Database::getInstance();
        $userId = $userId ?? (class_exists('Auth') ? Auth::id() : null) ?? 1;
        $userNama = $userNama ?? (class_exists('Auth') ? Auth::name() : null) ?? 'Pengguna';

        $data = [
            'status' => $status,
            'catatan_revisi' => ($status === 'ditolak') ? $catatan : null
        ];

        if ($status === 'disetujui') {
            $data['approved_by'] = $userId;
            $data['approved_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'draft' || $status === 'diajukan') {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        $db->update('perangkat_pembelajaran', $data, 'id = ?', [$id]);

        $aksiMap = [
            'draft' => 'draft',
            'diajukan' => 'ajukan',
            'disetujui' => 'setujui',
            'ditolak' => 'tolak'
        ];
        $aksi = $aksiMap[$status] ?? 'revisi';

        self::addLog($id, $userId, $userNama, $aksi, $catatan);
        return true;
    }

    /**
     * Add log for approval workflow
     */
    public static function addLog(int $perangkatId, int $userId, string $userNama, string $aksi, ?string $catatan = null): void
    {
        self::ensureTables();
        $db = Database::getInstance();
        $db->insert('perangkat_approval_logs', [
            'perangkat_id' => $perangkatId,
            'user_id' => $userId,
            'user_nama' => $userNama,
            'aksi' => $aksi,
            'catatan' => $catatan
        ]);
    }

    /**
     * Get logs for a perangkat
     */
    public static function getLogs(int $perangkatId): array
    {
        self::ensureTables();
        $db = Database::getInstance();
        return $db->findAll("
            SELECT * FROM perangkat_approval_logs
            WHERE perangkat_id = ?
            ORDER BY created_at DESC
        ", [$perangkatId]);
    }

    /**
     * Get Dashboard Stats
     */
    public static function getDashboardStats(?int $tahunAkademikId = null, ?string $semester = null, ?string $unit = null): array
    {
        self::ensureTables();
        $db = Database::getInstance();
        
        $where = ['1=1'];
        $params = [];

        if ($tahunAkademikId) {
            $where[] = 'tahun_akademik_id = ?';
            $params[] = $tahunAkademikId;
        }
        if ($semester) {
            $where[] = 'semester = ?';
            $params[] = $semester;
        }
        if ($unit) {
            $where[] = 'unit = ?';
            $params[] = $unit;
        }

        $whereClause = implode(' AND ', $where);

        $totalAll = $db->find("SELECT COUNT(*) as cnt FROM perangkat_pembelajaran WHERE {$whereClause}", $params)['cnt'] ?? 0;
        $totalDraft = $db->find("SELECT COUNT(*) as cnt FROM perangkat_pembelajaran WHERE status = 'draft' AND {$whereClause}", $params)['cnt'] ?? 0;
        $totalDiajukan = $db->find("SELECT COUNT(*) as cnt FROM perangkat_pembelajaran WHERE status = 'diajukan' AND {$whereClause}", $params)['cnt'] ?? 0;
        $totalDisetujui = $db->find("SELECT COUNT(*) as cnt FROM perangkat_pembelajaran WHERE status = 'disetujui' AND {$whereClause}", $params)['cnt'] ?? 0;
        $totalDitolak = $db->find("SELECT COUNT(*) as cnt FROM perangkat_pembelajaran WHERE status = 'ditolak' AND {$whereClause}", $params)['cnt'] ?? 0;

        // Breakdown by type
        $types = ['kaldik', 'hes', 'heb', 'prota', 'prosem', 'rpp'];
        $typeCounts = [];
        foreach ($types as $t) {
            $typeCounts[$t] = $db->find("SELECT COUNT(*) as cnt FROM perangkat_pembelajaran WHERE tipe = ? AND {$whereClause}", array_merge([$t], $params))['cnt'] ?? 0;
        }

        // Breakdown by unit
        $units = ['PAUD', 'SD', 'SMP', 'SMA'];
        $unitCounts = [];
        foreach ($units as $u) {
            $unitCounts[$u] = $db->find("SELECT COUNT(*) as cnt FROM perangkat_pembelajaran WHERE unit = ? AND {$whereClause}", array_merge([$u], $params))['cnt'] ?? 0;
        }

        return [
            'total' => (int) $totalAll,
            'draft' => (int) $totalDraft,
            'diajukan' => (int) $totalDiajukan,
            'disetujui' => (int) $totalDisetujui,
            'ditolak' => (int) $totalDitolak,
            'by_type' => $typeCounts,
            'by_unit' => $unitCounts
        ];
    }

    /**
     * Check if user can approve/reject
     */
    public static function canApprove(): bool
    {
        if (!class_exists('Auth') || !Auth::check()) {
            return false;
        }

        if (Auth::isSuperAdmin()) return true;
        
        $roles = Auth::roles();
        foreach ($roles as $r) {
            $rLower = strtolower($r);
            if (strpos($rLower, 'admin') !== false || 
                strpos($rLower, 'kepala sekolah') !== false || 
                strpos($rLower, 'kurikulum') !== false ||
                strpos($rLower, 'waka') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get active teachers/pegawai list
     */
    public static function getGuruList(): array
    {
        $db = Database::getInstance();
        try {
            return $db->findAll("
                SELECT id, nama, niy AS nip, jabatan, unit_tugas, email, no_wa
                FROM pegawai
                WHERE is_active = 1
                ORDER BY nama ASC
            ");
        } catch (Exception $e) {
            return $db->findAll("
                SELECT id, full_name AS nama, '' AS nip, 'Guru' AS jabatan, 'Sekolah' AS unit_tugas, email, phone AS no_wa
                FROM users
                WHERE is_active = 1
                ORDER BY full_name ASC
            ");
        }
    }

    /**
     * Detect matching teacher for the current logged-in user
     */
    public static function getLoggedInGuru(): ?array
    {
        if (!class_exists('Auth') || !Auth::check()) {
            return null;
        }

        $userId = Auth::id();
        $userName = trim(Auth::name() ?? '');
        $userObj = Auth::user();
        $userEmail = trim($userObj['email'] ?? '');
        $userPhone = trim($userObj['phone'] ?? '');

        $db = Database::getInstance();

        try {
            // 1. Match by email in pegawai
            if (!empty($userEmail)) {
                $p = $db->find("SELECT id, nama, niy AS nip, jabatan, unit_tugas FROM pegawai WHERE is_active = 1 AND email = ? LIMIT 1", [$userEmail]);
                if ($p) return $p;
            }

            // 2. Match by exact or partial name in pegawai
            if (!empty($userName) && strtolower($userName) !== 'super admin' && strtolower($userName) !== 'administrator') {
                $p = $db->find("SELECT id, nama, niy AS nip, jabatan, unit_tugas FROM pegawai WHERE is_active = 1 AND (nama = ? OR nama LIKE ?) LIMIT 1", [$userName, "%{$userName}%"]);
                if ($p) return $p;
            }

            // 3. Match by phone/no_wa
            if (!empty($userPhone)) {
                $p = $db->find("SELECT id, nama, niy AS nip, jabatan, unit_tugas FROM pegawai WHERE is_active = 1 AND (no_wa = ? OR kontak_darurat_1_no_hp = ?) LIMIT 1", [$userPhone, $userPhone]);
                if ($p) return $p;
            }
        } catch (Exception $e) {
            // Ignore DB error
        }

        // Return user info if not in pegawai table (e.g. Admin / Super Admin)
        return [
            'id' => null,
            'nama' => $userName ?: 'Administrator',
            'nip' => '',
            'jabatan' => 'Administrator',
            'unit_tugas' => 'Semua Unit',
            'is_admin' => true
        ];
    }

    /**
     * Normalize teacher's unit_tugas string into PAUD, SD, SMP, or SMA
     */
    public static function normalizeUnit(?string $unit): string
    {
        if (empty($unit)) return 'SD';
        $u = strtoupper(trim($unit));
        if (strpos($u, 'PAUD') !== false || strpos($u, 'TK') !== false || strpos($u, 'KB') !== false || strpos($u, 'RA') !== false) return 'PAUD';
        if (strpos($u, 'SD') !== false || strpos($u, 'MI') !== false) return 'SD';
        if (strpos($u, 'SMP') !== false || strpos($u, 'MTS') !== false) return 'SMP';
        if (strpos($u, 'SMA') !== false || strpos($u, 'SMK') !== false || strpos($u, 'MA') !== false) return 'SMA';
        return in_array($u, ['PAUD', 'SD', 'SMP', 'SMA']) ? $u : 'SD';
    }

    /**
     * Get Academic Years
     */
    public static function getTahunAkademikList(): array
    {
        $db = Database::getInstance();
        try {
            return $db->findAll("
                SELECT id, nama_tahun, is_active
                FROM tahun_akademik
                ORDER BY is_active DESC, nama_tahun DESC
            ");
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get Classes list
     */
    public static function getKelasList(?int $tahunAkademikId = null): array
    {
        $db = Database::getInstance();
        try {
            $sql = "SELECT id, nama_kelas, wali_kelas FROM kelas WHERE is_active = 1";
            $params = [];
            if ($tahunAkademikId) {
                $sql .= " AND tahun_akademik_id = ?";
                $params[] = $tahunAkademikId;
            }
            $sql .= " ORDER BY nama_kelas ASC";
            return $db->findAll($sql, $params);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Handle File Upload
     */
    public static function uploadFile(array $file, string $subfolder = 'perangkat'): ?string
    {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            return null;
        }

        $uploadDir = BASE_PATH . '/storage/uploads/' . $subfolder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'doc_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'storage/uploads/' . $subfolder . '/' . $fileName;
        }

        return null;
    }
}
