<?php
/**
 * Siswa Controller
 * Example feature module: Student Management CRUD
 */

class SiswaController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Siswa';
        $breadcrumbs = [['label' => 'Kelola Siswa']];
        
        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        
        // Check if siswa table exists, if not show empty state
        try {
            $where = '1=1';
            $params = [];
            if ($search) {
                $where .= " AND (nama LIKE ? OR nis LIKE ? OR kelas LIKE ?)";
                $s = "%{$search}%";
                $params = [$s, $s, $s];
            }
            $total = $db->find("SELECT COUNT(*) as total FROM siswa WHERE {$where}", $params)['total'] ?? 0;
            $siswa = $db->findAll("SELECT * FROM siswa WHERE {$where} ORDER BY nama ASC LIMIT {$limit} OFFSET {$offset}", $params);
        } catch (Exception $e) {
            // Table doesn't exist yet — create it
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `siswa` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `nis` VARCHAR(20) NOT NULL,
                    `nama` VARCHAR(100) NOT NULL,
                    `jenis_kelamin` ENUM('L','P') NOT NULL DEFAULT 'L',
                    `tempat_lahir` VARCHAR(100) DEFAULT NULL,
                    `tanggal_lahir` DATE DEFAULT NULL,
                    `alamat` TEXT DEFAULT NULL,
                    `kelas` VARCHAR(20) DEFAULT NULL,
                    `telepon` VARCHAR(20) DEFAULT NULL,
                    `email` VARCHAR(150) DEFAULT NULL,
                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_siswa_nis` (`nis`),
                    KEY `idx_siswa_kelas` (`kelas`),
                    KEY `idx_siswa_nama` (`nama`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $total = 0;
            $siswa = [];
        }
        
        $totalPages = max(1, ceil($total / $limit));

        // Stats
        $totalSiswa = $db->count('siswa') ?? 0;
        $totalLaki = $db->count('siswa', "jenis_kelamin = 'L'") ?? 0;
        $totalPerempuan = $db->count('siswa', "jenis_kelamin = 'P'") ?? 0;
        $totalAktif = $db->count('siswa', 'is_active = 1') ?? 0;
        
        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $pageTitle = 'Tambah Siswa';
        $breadcrumbs = [['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')], ['label' => 'Tambah']];
        
        $db = Database::getInstance();
        try {
            $kelasList = $db->findAll("SELECT id, nama_kelas FROM kelas WHERE is_active = 1 ORDER BY nama_kelas ASC");
        } catch (Exception $e) {
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `kelas` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `nama_kelas` VARCHAR(100) NOT NULL,
                    `wali_kelas` VARCHAR(100) DEFAULT NULL,
                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $kelasList = [];
        }
        
        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-siswa'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('nis', 'NIS')
            ->required('nama', 'Nama')
            ->required('jenis_kelamin', 'Jenis Kelamin')
            ->unique('nis', 'siswa', 'nis', null, 'NIS');
        
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        $db->insert('siswa', [
            'nis'            => trim($_POST['nis']),
            'nama'           => trim($_POST['nama']),
            'jenis_kelamin'  => $_POST['jenis_kelamin'],
            'tempat_lahir'   => trim($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir'  => $_POST['tanggal_lahir'] ?: null,
            'alamat'         => trim($_POST['alamat'] ?? ''),
            'kelas'          => trim($_POST['kelas'] ?? ''),
            'telepon'        => trim($_POST['telepon'] ?? ''),
            'email'          => trim($_POST['email'] ?? ''),
            'is_active'      => isset($_POST['is_active']) ? 1 : 0,
        ]);
        
        Response::withSuccess(url('kelola-siswa'), 'Data siswa berhasil ditambahkan.');
    }

    public static function edit(string $id): void
    {
        $db = Database::getInstance();
        $siswa = $db->find("SELECT * FROM siswa WHERE id = ?", [$id]);
        if (!$siswa) { Response::withError(url('kelola-siswa'), 'Data siswa tidak ditemukan.'); return; }
        
        try {
            $kelasList = $db->findAll("SELECT id, nama_kelas FROM kelas WHERE is_active = 1 ORDER BY nama_kelas ASC");
        } catch (Exception $e) {
            $kelasList = [];
        }
        
        $pageTitle = 'Edit Siswa';
        $breadcrumbs = [['label' => 'Kelola Siswa', 'url' => url('kelola-siswa')], ['label' => 'Edit']];
        
        ob_start();
        include MODULES_PATH . '/kelola-siswa/views/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-siswa/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-siswa'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('nis', 'NIS')
            ->required('nama', 'Nama')
            ->unique('nis', 'siswa', 'nis', (int) $id, 'NIS');
        
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        $db->update('siswa', [
            'nis'            => trim($_POST['nis']),
            'nama'           => trim($_POST['nama']),
            'jenis_kelamin'  => $_POST['jenis_kelamin'] ?? 'L',
            'tempat_lahir'   => trim($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir'  => $_POST['tanggal_lahir'] ?: null,
            'alamat'         => trim($_POST['alamat'] ?? ''),
            'kelas'          => trim($_POST['kelas'] ?? ''),
            'telepon'        => trim($_POST['telepon'] ?? ''),
            'email'          => trim($_POST['email'] ?? ''),
            'is_active'      => isset($_POST['is_active']) ? 1 : 0,
        ], 'id = ?', [$id]);
        
        Response::withSuccess(url('kelola-siswa'), 'Data siswa berhasil diperbarui.');
    }

    public static function delete(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-siswa'), 'Token tidak valid.'); return; }
        $db = Database::getInstance();
        $db->delete('siswa', 'id = ?', [$id]);
        Response::withSuccess(url('kelola-siswa'), 'Data siswa berhasil dihapus.');
    }
}
