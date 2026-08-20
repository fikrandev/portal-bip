<?php
/**
 * Pegawai Controller
 */

class PegawaiController
{
    public static function index(): void
    {
        $pageTitle = 'Data Pegawai';
        $breadcrumbs = [['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')], ['label' => 'Data Pegawai']];
        
        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 10;
        $offset = ($page - 1) * $limit;
        
        $search = trim($_GET['search'] ?? '');
        $filterUnit = trim($_GET['unit_tugas'] ?? '');
        $filterJabatan = trim($_GET['jabatan'] ?? '');
        
        try {
            $where = '1=1';
            $params = [];
            
            if ($search) {
                $where .= " AND nama LIKE ?";
                $params[] = "%{$search}%";
            }
            if ($filterUnit) {
                $where .= " AND unit_tugas = ?";
                $params[] = $filterUnit;
            }
            if ($filterJabatan) {
                $where .= " AND jabatan = ?";
                $params[] = $filterJabatan;
            }
            
            $total = $db->find("SELECT COUNT(*) as total FROM pegawai WHERE {$where}", $params)['total'] ?? 0;
            $pegawai = $db->findAll("SELECT * FROM pegawai WHERE {$where} ORDER BY nama ASC LIMIT {$limit} OFFSET {$offset}", $params);
            
            // Get unique unit_tugas and jabatan for filter dropdowns
            $unitTugasList = $db->findAll("SELECT DISTINCT unit_tugas FROM pegawai WHERE unit_tugas IS NOT NULL AND unit_tugas != '' ORDER BY unit_tugas ASC");
            $jabatanList = $db->findAll("SELECT DISTINCT jabatan FROM pegawai WHERE jabatan IS NOT NULL AND jabatan != '' ORDER BY jabatan ASC");
            
        } catch (Exception $e) {
            // Create tables if they don't exist
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `pegawai` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `foto` VARCHAR(255) DEFAULT NULL,
                    `niy` VARCHAR(50) DEFAULT NULL,
                    `nik` VARCHAR(50) DEFAULT NULL,
                    `nama` VARCHAR(100) NOT NULL,
                    `gelar` VARCHAR(50) DEFAULT NULL,
                    `jenis_kelamin` ENUM('L','P') NOT NULL DEFAULT 'L',
                    `status_nikah` VARCHAR(50) DEFAULT NULL,
                    `tempat_lahir` VARCHAR(100) DEFAULT NULL,
                    `tanggal_lahir` DATE DEFAULT NULL,
                    `nama_ibu` VARCHAR(100) DEFAULT NULL,
                    `unit_tugas` VARCHAR(100) DEFAULT NULL,
                    `jabatan` VARCHAR(100) DEFAULT NULL,
                    `status_kerja` VARCHAR(50) DEFAULT NULL,
                    `jenis_pegawai` VARCHAR(50) DEFAULT NULL,
                    `status_dapodik` VARCHAR(50) DEFAULT NULL,
                    `tmt` DATE DEFAULT NULL,
                    `alamat_ktp` TEXT DEFAULT NULL,
                    `kab_kota_ktp` VARCHAR(100) DEFAULT NULL,
                    `kec_ktp` VARCHAR(100) DEFAULT NULL,
                    `kel_ktp` VARCHAR(100) DEFAULT NULL,
                    `alamat_domisili` TEXT DEFAULT NULL,
                    `kab_kota_domisili` VARCHAR(100) DEFAULT NULL,
                    `kec_domisili` VARCHAR(100) DEFAULT NULL,
                    `kel_domisili` VARCHAR(100) DEFAULT NULL,
                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $db->getConnection()->exec("
                CREATE TABLE IF NOT EXISTS `pegawai_pendidikan` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `pegawai_id` BIGINT UNSIGNED NOT NULL,
                    `jenjang` VARCHAR(50) NOT NULL,
                    `institusi` VARCHAR(150) NOT NULL,
                    `jurusan` VARCHAR(100) DEFAULT NULL,
                    `tahun_lulus` VARCHAR(4) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_peg_pend_pegawai` (`pegawai_id`),
                    CONSTRAINT `fk_pegawai_pendidikan` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $total = 0;
            $pegawai = [];
            $unitTugasList = [];
            $jabatanList = [];
        }
        
        $totalPages = max(1, ceil($total / $limit));
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $db = Database::getInstance();
        $unitTugasList = [];
        $jabatanList = [];
        $statusKerjaList = [];
        $jenisPegawaiList = [];
        try {
            $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
            $jabatanList = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");
            $statusKerjaList = $db->findAll("SELECT * FROM master_status_kerja ORDER BY nama ASC");
            $jenisPegawaiList = $db->findAll("SELECT * FROM master_jenis_pegawai ORDER BY nama ASC");
        } catch (Exception $e) {}

        $pageTitle = 'Tambah Data Pegawai';
        $breadcrumbs = [['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')], ['label' => 'Tambah Pegawai']];
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('nama', 'Nama Lengkap');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        
        // Handle Foto Upload
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/pegawai/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $filename = 'pegawai_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                    $fotoPath = '/public/uploads/pegawai/' . $filename;
                }
            }
        }

        try {
            $db->beginTransaction();
            
            $pegawaiId = $db->insert('pegawai', [
                'foto' => $fotoPath,
                'niy' => trim($_POST['niy'] ?? ''),
                'nik' => trim($_POST['nik'] ?? ''),
                'nama' => trim($_POST['nama']),
                'gelar' => trim($_POST['gelar'] ?? ''),
                'jenis_kelamin' => $_POST['jenis_kelamin'] ?? 'L',
                'status_nikah' => trim($_POST['status_nikah'] ?? ''),
                'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
                'tanggal_lahir' => empty($_POST['tanggal_lahir']) ? null : $_POST['tanggal_lahir'],
                'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
                'unit_tugas' => trim($_POST['unit_tugas'] ?? ''),
                'jabatan' => trim($_POST['jabatan'] ?? ''),
                'status_kerja' => trim($_POST['status_kerja'] ?? ''),
                'jenis_pegawai' => trim($_POST['jenis_pegawai'] ?? ''),
                'status_dapodik' => trim($_POST['status_dapodik'] ?? ''),
                'tmt' => empty($_POST['tmt']) ? null : $_POST['tmt'],
                'alamat_ktp' => trim($_POST['alamat_ktp'] ?? ''),
                'kab_kota_ktp' => trim($_POST['kab_kota_ktp'] ?? ''),
                'kec_ktp' => trim($_POST['kec_ktp'] ?? ''),
                'kel_ktp' => trim($_POST['kel_ktp'] ?? ''),
                'alamat_domisili' => trim($_POST['alamat_domisili'] ?? ''),
                'kab_kota_domisili' => trim($_POST['kab_kota_domisili'] ?? ''),
                'kec_domisili' => trim($_POST['kec_domisili'] ?? ''),
                'kel_domisili' => trim($_POST['kel_domisili'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ]);

            // Save Riwayat Pendidikan
            if (!empty($_POST['pendidikan_jenjang']) && is_array($_POST['pendidikan_jenjang'])) {
                foreach ($_POST['pendidikan_jenjang'] as $index => $jenjang) {
                    if (!empty($jenjang)) {
                        $db->insert('pegawai_pendidikan', [
                            'pegawai_id' => $pegawaiId,
                            'jenjang' => $jenjang,
                            'institusi' => trim($_POST['pendidikan_institusi'][$index] ?? ''),
                            'jurusan' => trim($_POST['pendidikan_jurusan'][$index] ?? ''),
                            'tahun_lulus' => trim($_POST['pendidikan_tahun'][$index] ?? '')
                        ]);
                    }
                }
            }

            $db->commit();
            Response::withSuccess(url('kelola-pegawai'), 'Data pegawai berhasil ditambahkan.');
        } catch (Exception $e) {
            $db->rollback();
            Response::withError(url('kelola-pegawai'), 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public static function edit(string $id): void
    {
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        if (!$pegawai) { Response::withError(url('kelola-pegawai'), 'Data pegawai tidak ditemukan.'); return; }
        
        $pendidikan = $db->findAll("SELECT * FROM pegawai_pendidikan WHERE pegawai_id = ? ORDER BY id ASC", [$id]);
        
        $unitTugasList = [];
        $jabatanList = [];
        $statusKerjaList = [];
        $jenisPegawaiList = [];
        try {
            $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
            $jabatanList = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");
            $statusKerjaList = $db->findAll("SELECT * FROM master_status_kerja ORDER BY nama ASC");
            $jenisPegawaiList = $db->findAll("SELECT * FROM master_jenis_pegawai ORDER BY nama ASC");
        } catch (Exception $e) {}
        
        $pageTitle = 'Edit Data Pegawai';
        $breadcrumbs = [['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')], ['label' => 'Edit Pegawai']];
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('nama', 'Nama Lengkap');
            
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        if (!$pegawai) { Response::withError(url('kelola-pegawai'), 'Data pegawai tidak ditemukan.'); return; }
        
        // Handle Foto Upload
        $fotoPath = $pegawai['foto'];
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/pegawai/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $filename = 'pegawai_' . time() . '_' . rand(100, 999) . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                    // Delete old photo
                    if (!empty($pegawai['foto']) && file_exists(BASE_PATH . $pegawai['foto'])) {
                        @unlink(BASE_PATH . $pegawai['foto']);
                    }
                    $fotoPath = '/public/uploads/pegawai/' . $filename;
                }
            }
        }

        try {
            $db->beginTransaction();
            
            $db->update('pegawai', [
                'foto' => $fotoPath,
                'niy' => trim($_POST['niy'] ?? ''),
                'nik' => trim($_POST['nik'] ?? ''),
                'nama' => trim($_POST['nama']),
                'gelar' => trim($_POST['gelar'] ?? ''),
                'jenis_kelamin' => $_POST['jenis_kelamin'] ?? 'L',
                'status_nikah' => trim($_POST['status_nikah'] ?? ''),
                'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
                'tanggal_lahir' => empty($_POST['tanggal_lahir']) ? null : $_POST['tanggal_lahir'],
                'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
                'unit_tugas' => trim($_POST['unit_tugas'] ?? ''),
                'jabatan' => trim($_POST['jabatan'] ?? ''),
                'status_kerja' => trim($_POST['status_kerja'] ?? ''),
                'jenis_pegawai' => trim($_POST['jenis_pegawai'] ?? ''),
                'status_dapodik' => trim($_POST['status_dapodik'] ?? ''),
                'tmt' => empty($_POST['tmt']) ? null : $_POST['tmt'],
                'alamat_ktp' => trim($_POST['alamat_ktp'] ?? ''),
                'kab_kota_ktp' => trim($_POST['kab_kota_ktp'] ?? ''),
                'kec_ktp' => trim($_POST['kec_ktp'] ?? ''),
                'kel_ktp' => trim($_POST['kel_ktp'] ?? ''),
                'alamat_domisili' => trim($_POST['alamat_domisili'] ?? ''),
                'kab_kota_domisili' => trim($_POST['kab_kota_domisili'] ?? ''),
                'kec_domisili' => trim($_POST['kec_domisili'] ?? ''),
                'kel_domisili' => trim($_POST['kel_domisili'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ], 'id = ?', [$id]);

            // Recreate Riwayat Pendidikan
            $db->delete('pegawai_pendidikan', 'pegawai_id = ?', [$id]);
            
            if (!empty($_POST['pendidikan_jenjang']) && is_array($_POST['pendidikan_jenjang'])) {
                foreach ($_POST['pendidikan_jenjang'] as $index => $jenjang) {
                    if (!empty($jenjang)) {
                        $db->insert('pegawai_pendidikan', [
                            'pegawai_id' => $id,
                            'jenjang' => $jenjang,
                            'institusi' => trim($_POST['pendidikan_institusi'][$index] ?? ''),
                            'jurusan' => trim($_POST['pendidikan_jurusan'][$index] ?? ''),
                            'tahun_lulus' => trim($_POST['pendidikan_tahun'][$index] ?? '')
                        ]);
                    }
                }
            }

            $db->commit();
            Response::withSuccess(url('kelola-pegawai'), 'Data pegawai berhasil diperbarui.');
        } catch (Exception $e) {
            $db->rollback();
            Response::withError(url('kelola-pegawai'), 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public static function delete(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai'), 'Token tidak valid.'); return; }
        
        $db = Database::getInstance();
        $pegawai = $db->find("SELECT * FROM pegawai WHERE id = ?", [$id]);
        
        if ($pegawai) {
            if (!empty($pegawai['foto']) && file_exists(BASE_PATH . $pegawai['foto'])) {
                @unlink(BASE_PATH . $pegawai['foto']);
            }
            $db->delete('pegawai', 'id = ?', [$id]);
        }
        
        Response::withSuccess(url('kelola-pegawai'), 'Data pegawai berhasil dihapus.');
    }

    // ==========================================
    // PENUGASAN PEGAWAI
    // ==========================================

    public static function penugasan(): void
    {
        $pageTitle = 'Penugasan Pegawai';
        $breadcrumbs = [['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')], ['label' => 'Penugasan Pegawai']];
        
        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : 10;
        $offset = ($page - 1) * $limit;
        
        $search = trim($_GET['search'] ?? '');
        $filterUnit = trim($_GET['unit_tugas'] ?? '');
        
        $where = '1=1';
        $params = [];
        
        if ($search) {
            $where .= " AND (p.nama LIKE ? OR pp.no_sk LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($filterUnit) {
            $where .= " AND pp.unit_tugas_id = ?";
            $params[] = $filterUnit;
        }
        
        $sqlCount = "SELECT COUNT(*) as total FROM pegawai_penugasan pp JOIN pegawai p ON pp.pegawai_id = p.id WHERE {$where}";
        $total = $db->find($sqlCount, $params)['total'] ?? 0;
        
        $sqlData = "
            SELECT pp.*, p.nama as nama_pegawai, p.niy, mut.nama as nama_unit, mj.nama as nama_jabatan 
            FROM pegawai_penugasan pp 
            JOIN pegawai p ON pp.pegawai_id = p.id 
            JOIN master_unit_tugas mut ON pp.unit_tugas_id = mut.id 
            JOIN master_jabatan mj ON pp.jabatan_id = mj.id 
            WHERE {$where} 
            ORDER BY pp.tmt_mulai DESC, p.nama ASC 
            LIMIT {$limit} OFFSET {$offset}
        ";
        $penugasan = $db->findAll($sqlData, $params);
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/index.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function createPenugasan(): void
    {
        $pageTitle = 'Tambah Penugasan';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Penugasan Pegawai', 'url' => url('kelola-pegawai/penugasan')],
            ['label' => 'Tambah']
        ];
        
        $db = Database::getInstance();
        $pegawaiList = $db->findAll("SELECT id, nama, niy FROM pegawai ORDER BY nama ASC");
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        $jabatanList = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/create.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function storePenugasan(): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan'), 'Token tidak valid.'); return; }
        
        // Basic validation
        $rules = [
            'pegawai_id' => 'required',
            'no_sk' => 'required',
            'tanggal_sk' => 'required',
            'unit_tugas_id' => 'required',
            'jabatan_id' => 'required',
            'tmt_mulai' => 'required'
        ];
        
        $errors = Request::validate($rules);
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . url('kelola-pegawai/penugasan/create'));
            exit;
        }

        $db = Database::getInstance();
        
        // Handle file upload
        $file_sk = null;
        if (isset($_FILES['file_sk']) && $_FILES['file_sk']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sk/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $fileExt = strtolower(pathinfo($_FILES['file_sk']['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                Response::withError(url('kelola-pegawai/penugasan/create'), 'Format file SK harus PDF/JPG/PNG.');
                return;
            }
            
            $fileName = 'SK_' . time() . '_' . rand(100,999) . '.' . $fileExt;
            if (move_uploaded_file($_FILES['file_sk']['tmp_name'], $uploadDir . $fileName)) {
                $file_sk = '/public/uploads/sk/' . $fileName;
            }
        }
        
        $db->insert('pegawai_penugasan', [
            'pegawai_id' => $_POST['pegawai_id'],
            'no_sk' => trim($_POST['no_sk']),
            'tanggal_sk' => $_POST['tanggal_sk'],
            'unit_tugas_id' => $_POST['unit_tugas_id'],
            'jabatan_id' => $_POST['jabatan_id'],
            'tmt_mulai' => $_POST['tmt_mulai'],
            'tst_selesai' => empty($_POST['tst_selesai']) ? null : $_POST['tst_selesai'],
            'file_sk' => $file_sk,
            'status' => $_POST['status'] ?? 'Aktif',
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ]);
        
        Response::withSuccess(url('kelola-pegawai/penugasan'), 'Penugasan berhasil ditambahkan.');
    }

    public static function editPenugasan(string $id): void
    {
        $db = Database::getInstance();
        $penugasan = $db->find("SELECT * FROM pegawai_penugasan WHERE id = ?", [$id]);
        
        if (!$penugasan) {
            Response::withError(url('kelola-pegawai/penugasan'), 'Penugasan tidak ditemukan.');
            return;
        }
        
        $pageTitle = 'Edit Penugasan';
        $breadcrumbs = [
            ['label' => 'Kelola Pegawai', 'url' => url('kelola-pegawai')],
            ['label' => 'Penugasan Pegawai', 'url' => url('kelola-pegawai/penugasan')],
            ['label' => 'Edit']
        ];
        
        $pegawaiList = $db->findAll("SELECT id, nama, niy FROM pegawai ORDER BY nama ASC");
        $unitTugasList = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        $jabatanList = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");
        
        ob_start();
        include MODULES_PATH . '/kelola-pegawai/views/penugasan/edit.php';
        $content = ob_get_clean();
        $customSidebar = MODULES_PATH . '/kelola-pegawai/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function updatePenugasan(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan'), 'Token tidak valid.'); return; }
        
        $rules = [
            'pegawai_id' => 'required',
            'no_sk' => 'required',
            'tanggal_sk' => 'required',
            'unit_tugas_id' => 'required',
            'jabatan_id' => 'required',
            'tmt_mulai' => 'required'
        ];
        
        $errors = Request::validate($rules);
        if (!empty($errors)) {
            $_SESSION['validation_errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . url('kelola-pegawai/penugasan/edit/' . $id));
            exit;
        }

        $db = Database::getInstance();
        $oldPenugasan = $db->find("SELECT file_sk FROM pegawai_penugasan WHERE id = ?", [$id]);
        
        $file_sk = $oldPenugasan['file_sk'];
        if (isset($_FILES['file_sk']) && $_FILES['file_sk']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/uploads/sk/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $fileExt = strtolower(pathinfo($_FILES['file_sk']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $fileName = 'SK_' . time() . '_' . rand(100,999) . '.' . $fileExt;
                if (move_uploaded_file($_FILES['file_sk']['tmp_name'], $uploadDir . $fileName)) {
                    // Delete old file
                    if ($file_sk && file_exists(BASE_PATH . $file_sk)) {
                        @unlink(BASE_PATH . $file_sk);
                    }
                    $file_sk = '/public/uploads/sk/' . $fileName;
                }
            }
        }
        
        $db->update('pegawai_penugasan', [
            'pegawai_id' => $_POST['pegawai_id'],
            'no_sk' => trim($_POST['no_sk']),
            'tanggal_sk' => $_POST['tanggal_sk'],
            'unit_tugas_id' => $_POST['unit_tugas_id'],
            'jabatan_id' => $_POST['jabatan_id'],
            'tmt_mulai' => $_POST['tmt_mulai'],
            'tst_selesai' => empty($_POST['tst_selesai']) ? null : $_POST['tst_selesai'],
            'file_sk' => $file_sk,
            'status' => $_POST['status'] ?? 'Aktif',
            'keterangan' => trim($_POST['keterangan'] ?? '')
        ], 'id = ?', [$id]);
        
        Response::withSuccess(url('kelola-pegawai/penugasan'), 'Penugasan berhasil diperbarui.');
    }

    public static function deletePenugasan(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('kelola-pegawai/penugasan'), 'Token tidak valid.'); return; }
        
        $db = Database::getInstance();
        $penugasan = $db->find("SELECT file_sk FROM pegawai_penugasan WHERE id = ?", [$id]);
        
        if ($penugasan) {
            if ($penugasan['file_sk'] && file_exists(BASE_PATH . $penugasan['file_sk'])) {
                @unlink(BASE_PATH . $penugasan['file_sk']);
            }
            $db->delete('pegawai_penugasan', 'id = ?', [$id]);
        }
        
        Response::withSuccess(url('kelola-pegawai/penugasan'), 'Penugasan berhasil dihapus.');
    }
}
