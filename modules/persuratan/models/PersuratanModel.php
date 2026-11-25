<?php
/**
 * PersuratanModel - Model Terpadu Modul Persuratan Yayasan & Persuratan Sekolah
 * Portal BIP - SIT Bina Insan Palu
 */

class PersuratanModel
{
    private static bool $schemaInitialized = false;

    private static function db(): Database
    {
        return Database::getInstance();
    }

    /**
     * Pastikan tabel persuratan & registrasi modul/RBAC tersedia
     */
    public static function ensureSchema(): void
    {
        if (self::$schemaInitialized) {
            return;
        }
        self::$schemaInitialized = true;

        $pdo = self::db()->getConnection();

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `persuratan_kop` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `scope` ENUM('yayasan','sekolah') NOT NULL DEFAULT 'sekolah',
                `unit_nama` VARCHAR(50) NOT NULL,
                `kode_unit` VARCHAR(30) NOT NULL DEFAULT 'BIP',
                `baris_1` VARCHAR(200) NOT NULL DEFAULT 'YAYASAN BINA INSAN PALU',
                `baris_2` VARCHAR(200) NOT NULL DEFAULT 'SEKOLAH ISLAM TERPADU BINA INSAN PALU',
                `sub_judul` VARCHAR(255) DEFAULT NULL,
                `alamat` TEXT DEFAULT NULL,
                `kontak` VARCHAR(255) DEFAULT NULL,
                `logo_kiri` VARCHAR(255) DEFAULT NULL,
                `logo_kanan` VARCHAR(255) DEFAULT NULL,
                `penandatangan_nama` VARCHAR(150) DEFAULT NULL,
                `penandatangan_jabatan` VARCHAR(150) DEFAULT NULL,
                `penandatangan_niy` VARCHAR(60) DEFAULT NULL,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_scope_unit` (`scope`, `unit_nama`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `persuratan_jenis` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `scope` ENUM('yayasan','sekolah') NOT NULL DEFAULT 'sekolah',
                `kode_surat` VARCHAR(30) NOT NULL,
                `nama_jenis` VARCHAR(120) NOT NULL,
                `format_nomor` VARCHAR(120) NOT NULL DEFAULT '{no}/{kode}/{unit}/{romawi}/{tahun}',
                `keterangan` VARCHAR(255) DEFAULT NULL,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_scope_kode` (`scope`, `kode_surat`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `persuratan_surat` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `scope` ENUM('yayasan','sekolah') NOT NULL DEFAULT 'sekolah',
                `arah` ENUM('masuk','keluar') NOT NULL DEFAULT 'masuk',
                `unit_nama` VARCHAR(50) NOT NULL DEFAULT 'Yayasan',
                `jenis_id` INT(11) DEFAULT NULL,
                `kode_surat` VARCHAR(30) DEFAULT NULL,
                `nomor_urut` INT(11) DEFAULT NULL,
                `nomor_surat` VARCHAR(120) NOT NULL,
                `tanggal_surat` DATE NOT NULL,
                `tanggal_diterima` DATE DEFAULT NULL,
                `pengirim` VARCHAR(180) DEFAULT NULL,
                `tujuan` VARCHAR(180) DEFAULT NULL,
                `perihal` VARCHAR(255) NOT NULL,
                `sifat_surat` ENUM('Biasa','Penting','Segera','Rahasia') NOT NULL DEFAULT 'Biasa',
                `isi_ringkas` TEXT DEFAULT NULL,
                `disposisi` TEXT DEFAULT NULL,
                `penandatangan` VARCHAR(150) DEFAULT NULL,
                `lampiran_ket` VARCHAR(150) DEFAULT NULL,
                `file_lampiran` VARCHAR(255) DEFAULT NULL,
                `status_surat` ENUM('Diterima','Diproses','Draft','Terkirim','Arsip') NOT NULL DEFAULT 'Arsip',
                `created_by` INT(11) DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_scope_arah` (`scope`, `arah`),
                KEY `idx_tanggal` (`tanggal_surat`),
                KEY `idx_unit` (`unit_nama`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::seedDefaults();
        self::ensureModulesAndPermissions();
    }

    private static function seedDefaults(): void
    {
        $kopDefaults = [
            ['yayasan', 'Yayasan', 'YYS-BIP', 'YAYASAN BINA INSAN PALU', 'PENGURUS YAYASAN BINA INSAN PALU', 'Lembaga Pendidikan & Dakwah Islam Terpadu', 'Jl. Banteng No. 1, Kel. Tatura Selatan, Kec. Palu Selatan, Kota Palu', 'Telp: (0451) 481-999 | Email: yayasan@binainsanpalu.sch.id', 'Ketua Yayasan Bina Insan Palu'],
            ['sekolah', 'PAUD', 'PAUD-BIP', 'YAYASAN BINA INSAN PALU', 'PAUD ISLAM TERPADU BINA INSAN PALU', 'Kelompok Bermain & Taman Kanak-Kanak Islam Terpadu', 'Jl. Banteng No. 1, Kel. Tatura Selatan, Kec. Palu Selatan, Kota Palu', 'Telp: (0451) 481-999 | Email: paud@binainsanpalu.sch.id', 'Kepala PAUD IT Bina Insan Palu'],
            ['sekolah', 'SD', 'SD-BIP', 'YAYASAN BINA INSAN PALU', 'SD ISLAM TERPADU BINA INSAN PALU', 'Sekolah Dasar Islam Terpadu Terakreditasi A', 'Jl. Banteng No. 1, Kel. Tatura Selatan, Kec. Palu Selatan, Kota Palu', 'Telp: (0451) 481-999 | Email: sd@binainsanpalu.sch.id', 'Kepala SD IT Bina Insan Palu'],
            ['sekolah', 'SMP', 'SMP-BIP', 'YAYASAN BINA INSAN PALU', 'SMP ISLAM TERPADU BINA INSAN PALU', 'Sekolah Menengah Pertama Islam Terpadu', 'Jl. Banteng No. 1, Kel. Tatura Selatan, Kec. Palu Selatan, Kota Palu', 'Telp: (0451) 481-999 | Email: smp@binainsanpalu.sch.id', 'Kepala SMP IT Bina Insan Palu'],
            ['sekolah', 'SMA', 'SMA-BIP', 'YAYASAN BINA INSAN PALU', 'SMA ISLAM TERPADU BINA INSAN PALU', 'Sekolah Menengah Atas Islam Terpadu', 'Jl. Banteng No. 1, Kel. Tatura Selatan, Kec. Palu Selatan, Kota Palu', 'Telp: (0451) 481-999 | Email: sma@binainsanpalu.sch.id', 'Kepala SMA IT Bina Insan Palu'],
        ];

        $stmtKop = self::db()->getConnection()->prepare("
            INSERT IGNORE INTO `persuratan_kop`
            (`scope`, `unit_nama`, `kode_unit`, `baris_1`, `baris_2`, `sub_judul`, `alamat`, `kontak`, `penandatangan_jabatan`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($kopDefaults as $kd) {
            $stmtKop->execute($kd);
        }

        $jenisDefaults = [
            ['SK', 'Surat Keputusan', '{no}/{kode}/{unit}/{romawi}/{tahun}', 'Keputusan resmi pimpinan / yayasan'],
            ['ST', 'Surat Tugas', '{no}/{kode}/{unit}/{romawi}/{tahun}', 'Penugasan pegawai / guru / panitia'],
            ['UND', 'Surat Undangan', '{no}/{kode}/{unit}/{romawi}/{tahun}', 'Undangan rapat, kegiatan, atau wali murid'],
            ['SP', 'Surat Pengantar / Permohonan', '{no}/{kode}/{unit}/{romawi}/{tahun}', 'Permohonan izin, kerjasama, atau pengantar resmi'],
            ['SE', 'Surat Edaran', '{no}/{kode}/{unit}/{romawi}/{tahun}', 'Pemberitahuan / edaran umum'],
            ['KET', 'Surat Keterangan', '{no}/{kode}/{unit}/{romawi}/{tahun}', 'Keterangan aktif, siswa, atau pegawai'],
            ['MOU', 'Nota Kesepahaman / Kerjasama', '{no}/{kode}/{unit}/{romawi}/{tahun}', 'Perjanjian kerjasama antar lembaga'],
        ];

        $stmtJenis = self::db()->getConnection()->prepare("
            INSERT IGNORE INTO `persuratan_jenis` (`scope`, `kode_surat`, `nama_jenis`, `format_nomor`, `keterangan`)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach (['yayasan', 'sekolah'] as $sc) {
            foreach ($jenisDefaults as $jd) {
                $stmtJenis->execute([$sc, $jd[0], $jd[1], $jd[2], $jd[3]]);
            }
        }
    }

    public static function ensureModulesAndPermissions(): void
    {
        try {
            $pdo = self::db()->getConnection();
            $iconSvg = '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>';

            $mods = [
                [
                    'slug' => 'persuratan-yayasan',
                    'name' => 'Persuratan Yayasan',
                    'desc' => 'Administrasi surat masuk, surat keluar, arsip data surat, KOP, jenis & kode surat Yayasan.',
                    'group' => 'Kesekretariatan',
                    'color' => '#0284c7',
                    'route' => '/persuratan-yayasan',
                    'sort' => 24,
                    'perms' => [
                        ['persuratan_yayasan.view', 'Lihat Modul Persuratan Yayasan'],
                        ['persuratan_yayasan.manage', 'Kelola Surat & Pengaturan Persuratan Yayasan'],
                    ]
                ],
                [
                    'slug' => 'persuratan-sekolah',
                    'name' => 'Persuratan Sekolah',
                    'desc' => 'Administrasi surat masuk, surat keluar, arsip data surat, KOP tiap unit (PAUD/SD/SMP/SMA), jenis & kode surat.',
                    'group' => 'Kesekretariatan',
                    'color' => '#059669',
                    'route' => '/persuratan-sekolah',
                    'sort' => 25,
                    'perms' => [
                        ['persuratan_sekolah.view', 'Lihat Modul Persuratan Sekolah'],
                        ['persuratan_sekolah.manage', 'Kelola Surat & Pengaturan Persuratan Sekolah'],
                    ]
                ],
            ];

            $adminRoleIds = $pdo->query("SELECT id FROM roles WHERE LOWER(name) IN ('super admin','admin','operator')")->fetchAll(PDO::FETCH_COLUMN);

            foreach ($mods as $m) {
                $modRow = self::db()->find("SELECT id FROM modules WHERE slug = ? LIMIT 1", [$m['slug']]);
                if (!$modRow) {
                    self::db()->query(
                        "INSERT INTO modules (name, slug, description, module_group, icon_svg, color, route, sort_order, is_active, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())",
                        [$m['name'], $m['slug'], $m['desc'], $m['group'], $iconSvg, $m['color'], $m['route'], $m['sort']]
                    );
                    $modId = (int)self::db()->lastInsertId();
                } else {
                    $modId = (int)$modRow['id'];
                }

                foreach ($m['perms'] as [$permSlug, $permName]) {
                    $pRow = self::db()->find("SELECT id FROM permissions WHERE slug = ? LIMIT 1", [$permSlug]);
                    if (!$pRow) {
                        self::db()->query(
                            "INSERT INTO permissions (name, slug, description, module_id, created_at) VALUES (?, ?, ?, ?, NOW())",
                            [$permName, $permSlug, $permName, $modId]
                        );
                        $permId = (int)self::db()->lastInsertId();
                    } else {
                        $permId = (int)$pRow['id'];
                    }

                    foreach ($adminRoleIds as $rid) {
                        self::db()->query(
                            "INSERT IGNORE INTO role_permissions (role_id, permission_id, granted_at) VALUES (?, ?, NOW())",
                            [(int)$rid, $permId]
                        );
                    }
                }
            }
        } catch (Throwable $e) {
            // Non-fatal if RBAC tables differ
        }
    }

    public static function getUnitsForScope(string $scope): array
    {
        if ($scope === 'yayasan') {
            return ['Yayasan'];
        }
        $all = ['PAUD', 'SD', 'SMP', 'SMA'];
        if (class_exists('Auth') && Auth::isJenjangRestricted()) {
            $filtered = array_values(array_filter($all, fn($u) => Auth::canAccessJenjang($u)));
            return !empty($filtered) ? $filtered : $all;
        }
        return $all;
    }

    public static function getDashboardStats(string $scope): array
    {
        self::ensureSchema();
        $units = self::getUnitsForScope($scope);
        $placeholders = implode(',', array_fill(0, count($units), '?'));
        $params = array_merge([$scope], $units);

        $row = self::db()->find("
            SELECT
                COUNT(*) AS total_surat,
                SUM(CASE WHEN arah = 'masuk' THEN 1 ELSE 0 END) AS total_masuk,
                SUM(CASE WHEN arah = 'keluar' THEN 1 ELSE 0 END) AS total_keluar,
                SUM(CASE WHEN DATE_FORMAT(tanggal_surat, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m') THEN 1 ELSE 0 END) AS bulan_ini,
                SUM(CASE WHEN arah = 'masuk' AND status_surat IN ('Diterima','Diproses') THEN 1 ELSE 0 END) AS masuk_pending
            FROM `persuratan_surat`
            WHERE scope = ? AND unit_nama IN ($placeholders)
        ", $params) ?: [];

        $byUnit = self::db()->findAll("
            SELECT unit_nama,
                   SUM(CASE WHEN arah = 'masuk' THEN 1 ELSE 0 END) AS masuk,
                   SUM(CASE WHEN arah = 'keluar' THEN 1 ELSE 0 END) AS keluar,
                   COUNT(*) AS total
            FROM `persuratan_surat`
            WHERE scope = ? AND unit_nama IN ($placeholders)
            GROUP BY unit_nama
            ORDER BY total DESC
        ", $params);

        $byJenis = self::db()->findAll("
            SELECT COALESCE(NULLIF(j.nama_jenis, ''), NULLIF(s.kode_surat, ''), 'Umum') AS jenis_label,
                   COALESCE(s.kode_surat, j.kode_surat, '-') AS kode,
                   COUNT(*) AS total
            FROM `persuratan_surat` s
            LEFT JOIN `persuratan_jenis` j ON j.id = s.jenis_id
            WHERE s.scope = ? AND s.unit_nama IN ($placeholders)
            GROUP BY jenis_label, kode
            ORDER BY total DESC
            LIMIT 8
        ", $params);

        $recent = self::db()->findAll("
            SELECT s.*, j.nama_jenis
            FROM `persuratan_surat` s
            LEFT JOIN `persuratan_jenis` j ON j.id = s.jenis_id
            WHERE s.scope = ? AND s.unit_nama IN ($placeholders)
            ORDER BY s.tanggal_surat DESC, s.id DESC
            LIMIT 8
        ", $params);

        return [
            'total_surat'   => (int)($row['total_surat'] ?? 0),
            'total_masuk'   => (int)($row['total_masuk'] ?? 0),
            'total_keluar'  => (int)($row['total_keluar'] ?? 0),
            'bulan_ini'     => (int)($row['bulan_ini'] ?? 0),
            'masuk_pending' => (int)($row['masuk_pending'] ?? 0),
            'by_unit'       => $byUnit,
            'by_jenis'      => $byJenis,
            'recent'        => $recent,
        ];
    }

    public static function getSuratList(string $scope, array $filters = []): array
    {
        self::ensureSchema();
        $units = self::getUnitsForScope($scope);
        $placeholders = implode(',', array_fill(0, count($units), '?'));

        $where = ["s.scope = ?", "s.unit_nama IN ($placeholders)"];
        $params = array_merge([$scope], $units);

        if (!empty($filters['arah']) && in_array($filters['arah'], ['masuk', 'keluar'], true)) {
            $where[] = "s.arah = ?";
            $params[] = $filters['arah'];
        }
        if (!empty($filters['unit_nama'])) {
            $where[] = "s.unit_nama = ?";
            $params[] = trim((string)$filters['unit_nama']);
        }
        if (!empty($filters['jenis_id'])) {
            $where[] = "s.jenis_id = ?";
            $params[] = (int)$filters['jenis_id'];
        }
        if (!empty($filters['start_date'])) {
            $where[] = "s.tanggal_surat >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $where[] = "s.tanggal_surat <= ?";
            $params[] = $filters['end_date'];
        }
        if (!empty($filters['search'])) {
            $term = '%' . trim((string)$filters['search']) . '%';
            $where[] = "(s.nomor_surat LIKE ? OR s.perihal LIKE ? OR s.pengirim LIKE ? OR s.tujuan LIKE ? OR s.kode_surat LIKE ?)";
            array_push($params, $term, $term, $term, $term, $term);
        }

        $whereSql = implode(' AND ', $where);
        return self::db()->findAll("
            SELECT s.*, j.nama_jenis
            FROM `persuratan_surat` s
            LEFT JOIN `persuratan_jenis` j ON j.id = s.jenis_id
            WHERE $whereSql
            ORDER BY s.tanggal_surat DESC, s.id DESC
            LIMIT 500
        ", $params);
    }

    public static function getSuratById(int $id): ?array
    {
        self::ensureSchema();
        return self::db()->find("
            SELECT s.*, j.nama_jenis
            FROM `persuratan_surat` s
            LEFT JOIN `persuratan_jenis` j ON j.id = s.jenis_id
            WHERE s.id = ? LIMIT 1
        ", [$id]) ?: null;
    }

    public static function generateNomorSurat(string $scope, string $unitNama, ?int $jenisId, ?string $tanggal = null): array
    {
        self::ensureSchema();
        $ts = $tanggal ? strtotime($tanggal) : time();
        if ($ts === false) $ts = time();

        $tahun = date('Y', $ts);
        $bulan = (int)date('n', $ts);
        $romawiMap = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $romawi = $romawiMap[$bulan] ?? 'I';

        $kop = self::getKopByUnit($scope, $unitNama);
        $kodeUnit = $kop['kode_unit'] ?? strtoupper($unitNama);

        $jenis = $jenisId ? self::db()->find("SELECT * FROM `persuratan_jenis` WHERE id = ? LIMIT 1", [$jenisId]) : null;
        $kodeSurat = $jenis['kode_surat'] ?? 'SRT';
        $format = $jenis['format_nomor'] ?? '{no}/{kode}/{unit}/{romawi}/{tahun}';

        $maxRow = self::db()->find("
            SELECT COALESCE(MAX(nomor_urut), 0) AS max_no
            FROM `persuratan_surat`
            WHERE scope = ? AND arah = 'keluar' AND unit_nama = ? AND YEAR(tanggal_surat) = ?
        ", [$scope, $unitNama, (int)$tahun]);
        $nextNo = ((int)($maxRow['max_no'] ?? 0)) + 1;
        $noPadded = str_pad((string)$nextNo, 3, '0', STR_PAD_LEFT);

        $nomorSurat = strtr($format, [
            '{no}'     => $noPadded,
            '{kode}'   => $kodeSurat,
            '{unit}'   => $kodeUnit,
            '{romawi}' => $romawi,
            '{tahun}'  => $tahun,
        ]);

        return [
            'nomor_urut'  => $nextNo,
            'kode_surat'  => $kodeSurat,
            'nomor_surat' => $nomorSurat,
        ];
    }

    private static function uploadFile(?array $file, string $subDir = 'persuratan'): ?string
    {
        if (!$file || empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];
        if (!in_array($ext, $allowed, true)) {
            return null;
        }
        $relDir = 'uploads/' . $subDir;
        $absDir = BASE_PATH . '/public/' . $relDir;
        if (!is_dir($absDir)) {
            mkdir($absDir, 0755, true);
        }
        $filename = $subDir . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $absDir . '/' . $filename)) {
            return $relDir . '/' . $filename;
        }
        return null;
    }

    public static function saveSurat(string $scope, array $data, ?array $file = null, ?int $id = null): int
    {
        self::ensureSchema();
        $arah = ($data['arah'] ?? 'masuk') === 'keluar' ? 'keluar' : 'masuk';
        $unitNama = trim((string)($data['unit_nama'] ?? ($scope === 'yayasan' ? 'Yayasan' : 'SD')));
        $jenisId = !empty($data['jenis_id']) ? (int)$data['jenis_id'] : null;
        $tanggalSurat = !empty($data['tanggal_surat']) ? $data['tanggal_surat'] : date('Y-m-d');
        $tanggalDiterima = !empty($data['tanggal_diterima']) ? $data['tanggal_diterima'] : null;
        $nomorSurat = trim((string)($data['nomor_surat'] ?? ''));
        $nomorUrut = !empty($data['nomor_urut']) ? (int)$data['nomor_urut'] : null;

        $jenisRow = $jenisId ? self::db()->find("SELECT kode_surat FROM `persuratan_jenis` WHERE id = ? LIMIT 1", [$jenisId]) : null;
        $kodeSurat = trim((string)($data['kode_surat'] ?? ($jenisRow['kode_surat'] ?? '')));

        if ($arah === 'keluar' && $nomorSurat === '') {
            $gen = self::generateNomorSurat($scope, $unitNama, $jenisId, $tanggalSurat);
            $nomorSurat = $gen['nomor_surat'];
            $nomorUrut = $gen['nomor_urut'];
            if ($kodeSurat === '') $kodeSurat = $gen['kode_surat'];
        } elseif ($arah === 'keluar' && $nomorUrut === null && !$id) {
            $gen = self::generateNomorSurat($scope, $unitNama, $jenisId, $tanggalSurat);
            $nomorUrut = $gen['nomor_urut'];
        }

        $sifat = in_array($data['sifat_surat'] ?? '', ['Biasa','Penting','Segera','Rahasia'], true) ? $data['sifat_surat'] : 'Biasa';
        $statusDefault = $arah === 'masuk' ? 'Diterima' : 'Terkirim';
        $status = in_array($data['status_surat'] ?? '', ['Diterima','Diproses','Draft','Terkirim','Arsip'], true)
            ? $data['status_surat'] : $statusDefault;

        $uploadedFile = self::uploadFile($file, 'persuratan');

        if ($id && $id > 0) {
            $existing = self::getSuratById($id);
            $filePath = $uploadedFile ?: ($existing['file_lampiran'] ?? null);
            self::db()->query("
                UPDATE `persuratan_surat` SET
                    `arah` = ?, `unit_nama` = ?, `jenis_id` = ?, `kode_surat` = ?,
                    `nomor_surat` = ?, `tanggal_surat` = ?, `tanggal_diterima` = ?,
                    `pengirim` = ?, `tujuan` = ?, `perihal` = ?, `sifat_surat` = ?,
                    `isi_ringkas` = ?, `disposisi` = ?, `penandatangan` = ?,
                    `lampiran_ket` = ?, `file_lampiran` = ?, `status_surat` = ?
                WHERE `id` = ? AND `scope` = ?
            ", [
                $arah, $unitNama, $jenisId, $kodeSurat ?: null,
                $nomorSurat, $tanggalSurat, $tanggalDiterima,
                trim((string)($data['pengirim'] ?? '')) ?: null,
                trim((string)($data['tujuan'] ?? '')) ?: null,
                trim((string)($data['perihal'] ?? '')),
                $sifat,
                trim((string)($data['isi_ringkas'] ?? '')) ?: null,
                trim((string)($data['disposisi'] ?? '')) ?: null,
                trim((string)($data['penandatangan'] ?? '')) ?: null,
                trim((string)($data['lampiran_ket'] ?? '')) ?: null,
                $filePath,
                $status,
                $id, $scope
            ]);
            return $id;
        }

        self::db()->query("
            INSERT INTO `persuratan_surat` (
                `scope`, `arah`, `unit_nama`, `jenis_id`, `kode_surat`, `nomor_urut`, `nomor_surat`,
                `tanggal_surat`, `tanggal_diterima`, `pengirim`, `tujuan`, `perihal`, `sifat_surat`,
                `isi_ringkas`, `disposisi`, `penandatangan`, `lampiran_ket`, `file_lampiran`, `status_surat`, `created_by`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $scope, $arah, $unitNama, $jenisId, $kodeSurat ?: null, $nomorUrut, $nomorSurat,
            $tanggalSurat, $tanggalDiterima,
            trim((string)($data['pengirim'] ?? '')) ?: null,
            trim((string)($data['tujuan'] ?? '')) ?: null,
            trim((string)($data['perihal'] ?? '')),
            $sifat,
            trim((string)($data['isi_ringkas'] ?? '')) ?: null,
            trim((string)($data['disposisi'] ?? '')) ?: null,
            trim((string)($data['penandatangan'] ?? '')) ?: null,
            trim((string)($data['lampiran_ket'] ?? '')) ?: null,
            $uploadedFile,
            $status,
            class_exists('Auth') ? (int)(Auth::id() ?? 0) : null
        ]);

        return (int)self::db()->lastInsertId();
    }

    public static function deleteSurat(string $scope, int $id): bool
    {
        self::ensureSchema();
        $row = self::getSuratById($id);
        if ($row && !empty($row['file_lampiran'])) {
            $abs = BASE_PATH . '/public/' . ltrim($row['file_lampiran'], '/');
            if (is_file($abs)) @unlink($abs);
        }
        self::db()->query("DELETE FROM `persuratan_surat` WHERE id = ? AND scope = ?", [$id, $scope]);
        return true;
    }

    public static function getJenisList(string $scope, bool $onlyActive = false): array
    {
        self::ensureSchema();
        $sql = "SELECT * FROM `persuratan_jenis` WHERE scope = ?" . ($onlyActive ? " AND is_active = 1" : "") . " ORDER BY kode_surat ASC";
        return self::db()->findAll($sql, [$scope]);
    }

    public static function saveJenis(string $scope, array $data, ?int $id = null): int
    {
        self::ensureSchema();
        $kode = strtoupper(trim((string)($data['kode_surat'] ?? '')));
        $nama = trim((string)($data['nama_jenis'] ?? ''));
        $fmt  = trim((string)($data['format_nomor'] ?? '{no}/{kode}/{unit}/{romawi}/{tahun}')) ?: '{no}/{kode}/{unit}/{romawi}/{tahun}';
        $ket  = trim((string)($data['keterangan'] ?? '')) ?: null;
        $akt  = isset($data['is_active']) ? 1 : 0;

        if ($id && $id > 0) {
            self::db()->query("
                UPDATE `persuratan_jenis` SET kode_surat = ?, nama_jenis = ?, format_nomor = ?, keterangan = ?, is_active = ?
                WHERE id = ? AND scope = ?
            ", [$kode, $nama, $fmt, $ket, $akt, $id, $scope]);
            return $id;
        }
        self::db()->query("
            INSERT INTO `persuratan_jenis` (scope, kode_surat, nama_jenis, format_nomor, keterangan, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [$scope, $kode, $nama, $fmt, $ket, $akt]);
        return (int)self::db()->lastInsertId();
    }

    public static function deleteJenis(string $scope, int $id): bool
    {
        self::ensureSchema();
        self::db()->query("DELETE FROM `persuratan_jenis` WHERE id = ? AND scope = ?", [$id, $scope]);
        return true;
    }

    public static function getKopList(string $scope): array
    {
        self::ensureSchema();
        $units = self::getUnitsForScope($scope);
        $placeholders = implode(',', array_fill(0, count($units), '?'));
        $rows = self::db()->findAll(
            "SELECT * FROM `persuratan_kop` WHERE scope = ? AND unit_nama IN ($placeholders) ORDER BY id ASC",
            array_merge([$scope], $units)
        );
        $map = [];
        foreach ($rows as $r) {
            $map[$r['unit_nama']] = $r;
        }
        return $map;
    }

    public static function getKopByUnit(string $scope, string $unitNama): array
    {
        self::ensureSchema();
        $row = self::db()->find(
            "SELECT * FROM `persuratan_kop` WHERE scope = ? AND unit_nama = ? LIMIT 1",
            [$scope, $unitNama]
        );
        if ($row) return $row;

        return [
            'scope' => $scope,
            'unit_nama' => $unitNama,
            'kode_unit' => strtoupper($unitNama) . '-BIP',
            'baris_1' => 'YAYASAN BINA INSAN PALU',
            'baris_2' => strtoupper($unitNama) . ' ISLAM TERPADU BINA INSAN PALU',
            'sub_judul' => '',
            'alamat' => 'Jl. Banteng No. 1, Kel. Tatura Selatan, Palu Selatan, Kota Palu',
            'kontak' => 'Telp: (0451) 481-999 | Email: info@binainsanpalu.sch.id',
            'logo_kiri' => null,
            'logo_kanan' => null,
            'penandatangan_nama' => '',
            'penandatangan_jabatan' => 'Pimpinan ' . $unitNama,
            'penandatangan_niy' => '',
        ];
    }

    public static function saveKop(string $scope, array $data, array $files = []): bool
    {
        self::ensureSchema();
        $unitNama = trim((string)($data['unit_nama'] ?? ($scope === 'yayasan' ? 'Yayasan' : 'SD')));
        $existing = self::db()->find("SELECT * FROM `persuratan_kop` WHERE scope = ? AND unit_nama = ? LIMIT 1", [$scope, $unitNama]);

        $logoKiri = self::uploadFile($files['logo_kiri'] ?? null, 'persuratan_kop') ?: ($existing['logo_kiri'] ?? null);
        $logoKanan = self::uploadFile($files['logo_kanan'] ?? null, 'persuratan_kop') ?: ($existing['logo_kanan'] ?? null);

        $params = [
            strtoupper(trim((string)($data['kode_unit'] ?? $unitNama))),
            trim((string)($data['baris_1'] ?? 'YAYASAN BINA INSAN PALU')),
            trim((string)($data['baris_2'] ?? '')),
            trim((string)($data['sub_judul'] ?? '')) ?: null,
            trim((string)($data['alamat'] ?? '')) ?: null,
            trim((string)($data['kontak'] ?? '')) ?: null,
            $logoKiri,
            $logoKanan,
            trim((string)($data['penandatangan_nama'] ?? '')) ?: null,
            trim((string)($data['penandatangan_jabatan'] ?? '')) ?: null,
            trim((string)($data['penandatangan_niy'] ?? '')) ?: null,
        ];

        if ($existing) {
            self::db()->query("
                UPDATE `persuratan_kop` SET
                    kode_unit = ?, baris_1 = ?, baris_2 = ?, sub_judul = ?,
                    alamat = ?, kontak = ?, logo_kiri = ?, logo_kanan = ?,
                    penandatangan_nama = ?, penandatangan_jabatan = ?, penandatangan_niy = ?
                WHERE id = ?
            ", array_merge($params, [(int)$existing['id']]));
        } else {
            self::db()->query("
                INSERT INTO `persuratan_kop`
                (scope, unit_nama, kode_unit, baris_1, baris_2, sub_judul, alamat, kontak, logo_kiri, logo_kanan, penandatangan_nama, penandatangan_jabatan, penandatangan_niy)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", array_merge([$scope, $unitNama], $params));
        }
        return true;
    }
}
