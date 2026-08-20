<?php
/**
 * Kelas Controller
 * CRUD for Kelas (Classes)
 */

class KelasController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Kelas';
        $breadcrumbs = [['label' => 'Kelola Kelas']];
        
        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        
        $tahunAkademikList = $db->findAll("SELECT id, nama_tahun, is_active FROM tahun_akademik ORDER BY tanggal_mulai DESC");
        
        $activeTa = null;
        foreach ($tahunAkademikList as $ta) {
            if ($ta['is_active']) { $activeTa = $ta['id']; break; }
        }
        
        $filterTa = isset($_GET['ta']) && !empty($_GET['ta']) ? (int) $_GET['ta'] : $activeTa;
        
        try {
            $where = 'tahun_akademik_id = ?';
            $params = [$filterTa];
            if ($search) {
                $where .= " AND (nama_kelas LIKE ? OR wali_kelas LIKE ?)";
                $s = "%{$search}%";
                $params[] = $s;
                $params[] = $s;
            }
            $total = $db->find("SELECT COUNT(*) as total FROM kelas WHERE {$where}", $params)['total'] ?? 0;
            $kelas = $db->findAll("SELECT * FROM kelas WHERE {$where} ORDER BY nama_kelas ASC LIMIT {$limit} OFFSET {$offset}", $params);
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
            $total = 0;
            $kelas = [];
        }
        
        $totalPages = max(1, ceil($total / $limit));

        // Stats
        $totalKelas = $db->count('kelas', 'tahun_akademik_id = ?', [$filterTa]) ?? 0;
        $totalAktif = $db->count('kelas', 'is_active = 1 AND tahun_akademik_id = ?', [$filterTa]) ?? 0;
        
        ob_start();
        include MODULES_PATH . '/kelola-kelas/views/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-kelas/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $pageTitle = 'Tambah Kelas';
        $breadcrumbs = [['label' => 'Kelola Kelas', 'url' => url('kelola-kelas')], ['label' => 'Tambah']];
        
        $db = Database::getInstance();
        $tahunAkademikList = $db->findAll("SELECT id, nama_tahun, is_active FROM tahun_akademik ORDER BY tanggal_mulai DESC");
        
        ob_start();
        include MODULES_PATH . '/kelola-kelas/views/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-kelas/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-kelas'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('nama_kelas', 'Nama Kelas')
            ->required('tahun_akademik_id', 'Tahun Ajaran');
        
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        // Cek duplikasi di tahun ajaran yang sama
        $exist = $db->find("SELECT id FROM kelas WHERE nama_kelas = ? AND tahun_akademik_id = ?", [trim($_POST['nama_kelas']), $_POST['tahun_akademik_id']]);
        if ($exist) {
            Response::backWithErrors(['nama_kelas' => ['Nama kelas sudah ada di tahun ajaran ini.']], $_POST);
            return;
        }

        $db->insert('kelas', [
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'nama_kelas' => trim($_POST['nama_kelas']),
            'wali_kelas' => trim($_POST['wali_kelas'] ?? ''),
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ]);
        
        Response::withSuccess(url('kelola-kelas'), 'Data kelas berhasil ditambahkan.');
    }

    public static function edit(string $id): void
    {
        $db = Database::getInstance();
        $kelas = $db->find("SELECT * FROM kelas WHERE id = ?", [$id]);
        if (!$kelas) { Response::withError(url('kelola-kelas'), 'Data kelas tidak ditemukan.'); return; }
        
        $pageTitle = 'Edit Kelas';
        $breadcrumbs = [['label' => 'Kelola Kelas', 'url' => url('kelola-kelas')], ['label' => 'Edit']];
        
        $tahunAkademikList = $db->findAll("SELECT id, nama_tahun, is_active FROM tahun_akademik ORDER BY tanggal_mulai DESC");
        
        ob_start();
        include MODULES_PATH . '/kelola-kelas/views/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-kelas/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-kelas'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('nama_kelas', 'Nama Kelas')
            ->required('tahun_akademik_id', 'Tahun Ajaran');
        
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        // Cek duplikasi
        $exist = $db->find("SELECT id FROM kelas WHERE nama_kelas = ? AND tahun_akademik_id = ? AND id != ?", [trim($_POST['nama_kelas']), $_POST['tahun_akademik_id'], $id]);
        if ($exist) {
            Response::backWithErrors(['nama_kelas' => ['Nama kelas sudah ada di tahun ajaran ini.']], $_POST);
            return;
        }

        $db->update('kelas', [
            'tahun_akademik_id' => (int) $_POST['tahun_akademik_id'],
            'nama_kelas' => trim($_POST['nama_kelas']),
            'wali_kelas' => trim($_POST['wali_kelas'] ?? ''),
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ], 'id = ?', [$id]);
        
        Response::withSuccess(url('kelola-kelas'), 'Data kelas berhasil diperbarui.');
    }

    public static function delete(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-kelas'), 'Token tidak valid.'); return; }
        $db = Database::getInstance();
        $db->delete('kelas', 'id = ?', [$id]);
        Response::withSuccess(url('kelola-kelas'), 'Data kelas berhasil dihapus.');
    }

    public static function copyClasses(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-kelas'), 'Token tidak valid.'); return; }

        $from_ta = (int) ($_POST['from_ta'] ?? 0);
        $to_ta = (int) ($_POST['to_ta'] ?? 0);

        if (!$from_ta || !$to_ta) {
            Response::withError(url('kelola-kelas'), 'Tahun ajaran asal dan tujuan harus dipilih.');
            return;
        }

        if ($from_ta === $to_ta) {
            Response::withError(url('kelola-kelas'), 'Tahun ajaran asal dan tujuan tidak boleh sama.');
            return;
        }

        $db = Database::getInstance();
        
        // Cek kelas yang ada di from_ta
        $sourceClasses = $db->findAll("SELECT nama_kelas, wali_kelas, is_active FROM kelas WHERE tahun_akademik_id = ?", [$from_ta]);
        
        if (empty($sourceClasses)) {
            Response::withError(url('kelola-kelas'), 'Tidak ada kelas di tahun ajaran asal.');
            return;
        }

        $copiedCount = 0;
        foreach ($sourceClasses as $kelas) {
            // Cek apakah sudah ada di to_ta
            $exist = $db->find("SELECT id FROM kelas WHERE nama_kelas = ? AND tahun_akademik_id = ?", [$kelas['nama_kelas'], $to_ta]);
            if (!$exist) {
                $db->insert('kelas', [
                    'tahun_akademik_id' => $to_ta,
                    'nama_kelas' => $kelas['nama_kelas'],
                    'wali_kelas' => $kelas['wali_kelas'],
                    'is_active' => $kelas['is_active']
                ]);
                $copiedCount++;
            }
        }

        Response::withSuccess(url('kelola-kelas') . '?ta=' . $to_ta, "Berhasil menyalin {$copiedCount} kelas.");
    }
}
