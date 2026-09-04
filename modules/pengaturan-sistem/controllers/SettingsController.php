<?php
/**
 * Settings Controller (Refactored for split tabs)
 */

class SettingsController
{
    /**
     * Helper to load settings from DB
     */
    private static function getSettings(): array
    {
        $db = Database::getInstance();
        $settingsRows = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
        
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        // Define default values if not exists
        $settings['academic_year'] = $settings['academic_year'] ?? '';
        $settings['app_name'] = $settings['app_name'] ?? 'Portal BIP';
        $settings['data_period_start'] = $settings['data_period_start'] ?? '';
        $settings['data_period_end'] = $settings['data_period_end'] ?? '';
        $settings['app_logo'] = $settings['app_logo'] ?? '';
        $settings['app_favicon'] = $settings['app_favicon'] ?? '';

        // Default Unit Info & Kepala Sekolah (PAUD, SD, SMP, SMA)
        $units = ['PAUD', 'SD', 'SMP', 'SMA'];
        foreach ($units as $u) {
            $defaultNama = ($u === 'SD') ? 'SD ISLAM TERPADU BINA INSAN PALU' : "{$u} IT BINA INSAN PALU";
            $settings["nama_sekolah_{$u}"] = $settings["nama_sekolah_{$u}"] ?? $defaultNama;
            $settings["logo_unit_{$u}"] = $settings["logo_unit_{$u}"] ?? '';
            $settings["kepala_sekolah_{$u}"] = $settings["kepala_sekolah_{$u}"] ?? ($u === 'SD' ? 'FENI, S.Pd.I' : '');
            $settings["nip_kepala_sekolah_{$u}"] = $settings["nip_kepala_sekolah_{$u}"] ?? '';
        }
        
        return $settings;
    }

    /**
     * Default index -> redirects to identitas
     */
    public static function index(): void
    {
        header('Location: ' . url('pengaturan-sistem/identitas'));
        exit;
    }

    /**
     * Identitas Sekolah
     */
    public static function identitas(): void
    {
        $pageTitle = 'Identitas Sekolah';
        $breadcrumbs = [['label' => 'Pengaturan', 'url' => url('pengaturan-sistem')], ['label' => 'Identitas Sekolah']];
        $settings = self::getSettings();
        
        $extraJs = '
        <script>
            function previewImage(input, imgId) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = document.getElementById(imgId);
                        img.src = e.target.result;
                        img.classList.remove("hidden");
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>';

        ob_start();
        include MODULES_PATH . '/pengaturan-sistem/views/identitas.php';
        $content = ob_get_clean();
        
        $customSidebar = MODULES_PATH . '/pengaturan-sistem/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Akademik List & View
     */
    public static function akademik(): void
    {
        $pageTitle = 'Tahun Akademik';
        $breadcrumbs = [['label' => 'Pengaturan', 'url' => url('pengaturan-sistem')], ['label' => 'Tahun Akademik']];
        $settings = self::getSettings();
        
        $db = Database::getInstance();
        $tahun_akademik = $db->findAll("SELECT * FROM tahun_akademik ORDER BY created_at DESC");

        ob_start();
        include MODULES_PATH . '/pengaturan-sistem/views/akademik.php';
        $content = ob_get_clean();
        
        $customSidebar = MODULES_PATH . '/pengaturan-sistem/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Store new Academic Year
     */
    public static function storeAkademik(): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/akademik'));
            exit;
        }

        $db = Database::getInstance();
        $nama_tahun = trim($_POST['nama_tahun'] ?? '');
        $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
        $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';

        if (empty($nama_tahun) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
            $_SESSION['flash_error'] = 'Semua field wajib diisi.';
            header('Location: ' . url('pengaturan-sistem/akademik'));
            exit;
        }

        // If this is the first entry, make it active
        $count = $db->count('tahun_akademik');
        $is_active = ($count === 0) ? 1 : 0;

        $db->insert('tahun_akademik', [
            'nama_tahun' => $nama_tahun,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
            'is_active' => $is_active
        ]);

        $db->query("INSERT INTO audit_logs (user_id, action, entity_type, created_at) VALUES (?, ?, ?, NOW())", [Auth::id(), 'CREATE', 'tahun_akademik']);
        $_SESSION['flash_success'] = 'Tahun Akademik berhasil ditambahkan.';
        header('Location: ' . url('pengaturan-sistem/akademik'));
        exit;
    }

    /**
     * Set Academic Year Active
     */
    public static function setAktifAkademik(int $id): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/akademik'));
            exit;
        }

        $db = Database::getInstance();
        
        // Reset all to inactive
        $db->query("UPDATE tahun_akademik SET is_active = 0");
        
        // Set selected to active
        $db->query("UPDATE tahun_akademik SET is_active = 1 WHERE id = ?", [$id]);
        
        $db->query("INSERT INTO audit_logs (user_id, action, entity_type, new_value, created_at) VALUES (?, ?, ?, ?, NOW())", [Auth::id(), 'SET_ACTIVE', 'tahun_akademik', json_encode(['id' => $id])]);
        $_SESSION['flash_success'] = 'Tahun Akademik berhasil diaktifkan.';
        header('Location: ' . url('pengaturan-sistem/akademik'));
        exit;
    }

    /**
     * Edit Academic Year View
     */
    public static function editAkademik(int $id): void
    {
        $db = Database::getInstance();
        $ta = $db->find("SELECT * FROM tahun_akademik WHERE id = ?", [$id]);
        
        if (!$ta) {
            $_SESSION['flash_error'] = 'Data tidak ditemukan.';
            header('Location: ' . url('pengaturan-sistem/akademik'));
            exit;
        }

        $pageTitle = 'Edit Tahun Akademik';
        $breadcrumbs = [['label' => 'Pengaturan', 'url' => url('pengaturan-sistem')], ['label' => 'Tahun Akademik', 'url' => url('pengaturan-sistem/akademik')], ['label' => 'Edit']];
        
        ob_start();
        include MODULES_PATH . '/pengaturan-sistem/views/edit-akademik.php';
        $content = ob_get_clean();
        
        $customSidebar = MODULES_PATH . '/pengaturan-sistem/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Update Academic Year
     */
    public static function updateAkademik(int $id): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/akademik'));
            exit;
        }

        $db = Database::getInstance();
        $nama_tahun = trim($_POST['nama_tahun'] ?? '');
        $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
        $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';

        if (empty($nama_tahun) || empty($tanggal_mulai) || empty($tanggal_selesai)) {
            $_SESSION['flash_error'] = 'Semua field wajib diisi.';
            header('Location: ' . url('pengaturan-sistem/akademik/edit/' . $id));
            exit;
        }

        $db->update('tahun_akademik', [
            'nama_tahun' => $nama_tahun,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai
        ], 'id = ?', [$id]);

        $db->query("INSERT INTO audit_logs (user_id, action, entity_type, created_at) VALUES (?, ?, ?, NOW())", [Auth::id(), 'UPDATE', 'tahun_akademik']);
        $_SESSION['flash_success'] = 'Tahun Akademik berhasil diperbarui.';
        header('Location: ' . url('pengaturan-sistem/akademik'));
        exit;
    }

    /**
     * Delete Academic Year
     */
    public static function deleteAkademik(int $id): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/akademik'));
            exit;
        }

        $db = Database::getInstance();
        $year = $db->find("SELECT * FROM tahun_akademik WHERE id = ?", [$id]);
        
        if (!$year) {
            $_SESSION['flash_error'] = 'Data tidak ditemukan.';
            header('Location: ' . url('pengaturan-sistem/akademik'));
            exit;
        }

        if ($year['is_active']) {
            $_SESSION['flash_error'] = 'Tidak dapat menghapus tahun akademik yang sedang aktif!';
            header('Location: ' . url('pengaturan-sistem/akademik'));
            exit;
        }

        $db->delete('tahun_akademik', 'id = ?', [$id]);
        
        $db->query("INSERT INTO audit_logs (user_id, action, entity_type, created_at) VALUES (?, ?, ?, NOW())", [Auth::id(), 'DELETE', 'tahun_akademik']);
        $_SESSION['flash_success'] = 'Tahun Akademik berhasil dihapus.';
        header('Location: ' . url('pengaturan-sistem/akademik'));
        exit;
    }

    /**
     * Master Pegawai (Unit Tugas & Jabatan) View
     */
    public static function masterPegawai(): void
    {
        $pageTitle = 'Referensi Pegawai';
        $breadcrumbs = [['label' => 'Pengaturan', 'url' => url('pengaturan-sistem')], ['label' => 'Referensi Pegawai']];
        
        $db = Database::getInstance();
        
        // Auto create tables if not exists
        try {
            $db->find("SELECT 1 FROM master_unit_tugas LIMIT 1");
        } catch (Exception $e) {
            $db->getConnection()->exec("CREATE TABLE IF NOT EXISTS `master_unit_tugas` (`id` INT AUTO_INCREMENT PRIMARY KEY, `nama` VARCHAR(100) NOT NULL, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP)");
        }
        try {
            $db->find("SELECT 1 FROM master_jabatan LIMIT 1");
        } catch (Exception $e) {
            $db->getConnection()->exec("CREATE TABLE IF NOT EXISTS `master_jabatan` (`id` INT AUTO_INCREMENT PRIMARY KEY, `nama` VARCHAR(100) NOT NULL, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP)");
        }
        
        $unit_tugas = $db->findAll("SELECT * FROM master_unit_tugas ORDER BY nama ASC");
        $jabatan = $db->findAll("SELECT * FROM master_jabatan ORDER BY nama ASC");
        $status_kerja = $db->findAll("SELECT * FROM master_status_kerja ORDER BY nama ASC");
        $jenis_pegawai = $db->findAll("SELECT * FROM master_jenis_pegawai ORDER BY nama ASC");

        ob_start();
        include MODULES_PATH . '/pengaturan-sistem/views/master-pegawai.php';
        $content = ob_get_clean();
        
        $customSidebar = MODULES_PATH . '/pengaturan-sistem/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Store Unit Tugas
     */
    public static function storeUnitTugas(): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        if (empty($nama)) {
            $_SESSION['flash_error'] = 'Nama Unit Tugas wajib diisi.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $db = Database::getInstance();
        $db->insert('master_unit_tugas', ['nama' => $nama]);
        
        $_SESSION['flash_success'] = 'Unit Tugas berhasil ditambahkan.';
        header('Location: ' . url('pengaturan-sistem/master-pegawai'));
        exit;
    }

    /**
     * Delete Unit Tugas
     */
    public static function deleteUnitTugas(int $id): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $db = Database::getInstance();
        $db->delete('master_unit_tugas', 'id = ?', [$id]);
        
        $_SESSION['flash_success'] = 'Unit Tugas berhasil dihapus.';
        header('Location: ' . url('pengaturan-sistem/master-pegawai'));
        exit;
    }

    /**
     * Store Jabatan
     */
    public static function storeJabatan(): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        if (empty($nama)) {
            $_SESSION['flash_error'] = 'Nama Jabatan wajib diisi.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $db = Database::getInstance();
        $db->insert('master_jabatan', ['nama' => $nama]);
        
        $_SESSION['flash_success'] = 'Jabatan berhasil ditambahkan.';
        header('Location: ' . url('pengaturan-sistem/master-pegawai'));
        exit;
    }

    /**
     * Delete Jabatan
     */
    public static function deleteJabatan(int $id): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $db = Database::getInstance();
        $db->delete('master_jabatan', 'id = ?', [$id]);
        
        $_SESSION['flash_success'] = 'Jabatan berhasil dihapus.';
        header('Location: ' . url('pengaturan-sistem/master-pegawai'));
        exit;
    }

    /**
     * Store Status Kerja
     */
    public static function storeStatusKerja(): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        if (empty($nama)) {
            $_SESSION['flash_error'] = 'Nama Status Kerja wajib diisi.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $db = Database::getInstance();
        $db->insert('master_status_kerja', ['nama' => $nama]);
        $_SESSION['flash_success'] = 'Status Kerja berhasil ditambahkan.';
        header('Location: ' . url('pengaturan-sistem/master-pegawai'));
        exit;
    }

    /**
     * Delete Status Kerja
     */
    public static function deleteStatusKerja(int $id): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $db = Database::getInstance();
        $db->delete('master_status_kerja', 'id = ?', [$id]);
        $_SESSION['flash_success'] = 'Status Kerja berhasil dihapus.';
        header('Location: ' . url('pengaturan-sistem/master-pegawai'));
        exit;
    }

    /**
     * Store Jenis Pegawai
     */
    public static function storeJenisPegawai(): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $nama = trim($_POST['nama'] ?? '');
        if (empty($nama)) {
            $_SESSION['flash_error'] = 'Nama Jenis Pegawai wajib diisi.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $db = Database::getInstance();
        $db->insert('master_jenis_pegawai', ['nama' => $nama]);
        $_SESSION['flash_success'] = 'Jenis Pegawai berhasil ditambahkan.';
        header('Location: ' . url('pengaturan-sistem/master-pegawai'));
        exit;
    }

    /**
     * Delete Jenis Pegawai
     */
    public static function deleteJenisPegawai(int $id): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/master-pegawai'));
            exit;
        }

        $db = Database::getInstance();
        $db->delete('master_jenis_pegawai', 'id = ?', [$id]);
        $_SESSION['flash_success'] = 'Jenis Pegawai berhasil dihapus.';
        header('Location: ' . url('pengaturan-sistem/master-pegawai'));
        exit;
    }

    /**
     * Reset Data View
     */
    public static function resetDataView(): void
    {
        $pageTitle = 'Reset Data';
        $breadcrumbs = [['label' => 'Pengaturan', 'url' => url('pengaturan-sistem')], ['label' => 'Reset Data']];
        
        $extraJs = '
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function toggleSelectAll(source) {
                checkboxes = document.getElementsByName("tables[]");
                for(var i=0, n=checkboxes.length; i<n; i++) {
                    checkboxes[i].checked = source.checked;
                }
            }
            
            function confirmReset(event) {
                event.preventDefault();
                let selected = false;
                let checkboxes = document.getElementsByName("tables[]");
                for(var i=0, n=checkboxes.length; i<n; i++) {
                    if(checkboxes[i].checked) selected = true;
                }
                
                if(!selected) {
                    Swal.fire({icon: "error", title: "Oops...", text: "Pilih setidaknya satu data untuk direset!"});
                    return;
                }
                
                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Data yang dicentang akan dihapus permanen dan tidak dapat dikembalikan!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ef4444",
                    cancelButtonColor: "#64748b",
                    confirmButtonText: "Ya, Hapus Data!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById("form-reset-data").submit();
                    }
                });
            }
        </script>';

        ob_start();
        include MODULES_PATH . '/pengaturan-sistem/views/reset_data.php';
        $content = ob_get_clean();
        
        $customSidebar = MODULES_PATH . '/pengaturan-sistem/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    /**
     * Update settings (Identitas or Akademik based on form inputs)
     */
    public static function update(): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem'));
            exit;
        }
        $db = Database::getInstance();
        $keys = ['app_name'];
        $units = ['PAUD', 'SD', 'SMP', 'SMA'];
        foreach ($units as $u) {
            $keys[] = "nama_sekolah_{$u}";
            $keys[] = "kepala_sekolah_{$u}";
            $keys[] = "nip_kepala_sekolah_{$u}";
        }
        
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                $exists = $db->find("SELECT 1 FROM settings WHERE setting_key = ?", [$key]);
                if ($exists) {
                    $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$val, $key]);
                } else {
                    $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$key, $val]);
                }
            }
        }
        
        // Handle file uploads
        $uploadDir = BASE_PATH . '/public/uploads/settings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Global Logo
        if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['app_logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg'])) {
                $filename = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['app_logo']['tmp_name'], $uploadDir . $filename)) {
                    $val = '/public/uploads/settings/' . $filename;
                    $exists = $db->find("SELECT 1 FROM settings WHERE setting_key = 'app_logo'");
                    if ($exists) {
                        $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = 'app_logo'", [$val]);
                    } else {
                        $db->query("INSERT INTO settings (setting_key, setting_value) VALUES ('app_logo', ?)", [$val]);
                    }
                }
            }
        }
        
        // Favicon
        if (isset($_FILES['app_favicon']) && $_FILES['app_favicon']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['app_favicon']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'ico', 'svg'])) {
                $filename = 'favicon_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['app_favicon']['tmp_name'], $uploadDir . $filename)) {
                    $val = '/public/uploads/settings/' . $filename;
                    $exists = $db->find("SELECT 1 FROM settings WHERE setting_key = 'app_favicon'");
                    if ($exists) {
                        $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = 'app_favicon'", [$val]);
                    } else {
                        $db->query("INSERT INTO settings (setting_key, setting_value) VALUES ('app_favicon', ?)", [$val]);
                    }
                }
            }
        }

        // Unit Logos (PAUD, SD, SMP, SMA)
        foreach ($units as $u) {
            $fileKey = "logo_unit_{$u}";
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg'])) {
                    $filename = "logo_" . strtolower($u) . "_" . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $uploadDir . $filename)) {
                        $val = '/public/uploads/settings/' . $filename;
                        $exists = $db->find("SELECT 1 FROM settings WHERE setting_key = ?", [$fileKey]);
                        if ($exists) {
                            $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$val, $fileKey]);
                        } else {
                            $db->query("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$fileKey, $val]);
                        }
                    }
                }
            }
        }
        
        $db->query("INSERT INTO audit_logs (user_id, action, entity_type, created_at) VALUES (?, ?, ?, NOW())", [Auth::id(), 'UPDATE', 'settings']);
        
        $_SESSION['flash_success'] = 'Pengaturan dan identitas unit berhasil diperbarui.';
        
        // Redirect back to referring page
        $referer = $_SERVER['HTTP_REFERER'] ?? url('pengaturan-sistem/identitas');
        header('Location: ' . $referer);
        exit;
    }

    /**
     * Reset Data logic
     */
    public static function resetData(): void
    {
        if (!CSRF::validate()) { 
            $_SESSION['flash_error'] = 'Token tidak valid.';
            header('Location: ' . url('pengaturan-sistem/reset-data'));
            exit;
        }
        
        if (!isset($_POST['tables']) || !is_array($_POST['tables'])) {
            $_SESSION['flash_error'] = 'Tidak ada data yang dipilih untuk direset.';
            header('Location: ' . url('pengaturan-sistem/reset-data'));
            exit;
        }
        
        $tables = $_POST['tables'];
        $db = Database::getInstance();
        
        try {
            $db->query("SET FOREIGN_KEY_CHECKS = 0");
            foreach ($tables as $table) {
                switch ($table) {
                    case 'siswa':
                        try { $db->query("TRUNCATE TABLE `siswa`"); } catch(Exception $e){}
                        break;
                    case 'guru':
                        try { $db->query("TRUNCATE TABLE `guru`"); } catch(Exception $e){}
                        break;
                    case 'kelas':
                        try { $db->query("TRUNCATE TABLE `kelas`"); } catch(Exception $e){}
                        break;
                    case 'users':
                        try { 
                            $db->query("DELETE FROM `users` WHERE `id` != 1"); 
                            $db->query("DELETE FROM `user_roles` WHERE `user_id` != 1");
                        } catch(Exception $e){}
                        break;
                    case 'audit_logs':
                        try { $db->query("TRUNCATE TABLE `audit_logs`"); } catch(Exception $e){}
                        break;
                }
            }
            $db->query("SET FOREIGN_KEY_CHECKS = 1");
            
            $db->query("INSERT INTO audit_logs (user_id, action, entity_type, new_value, created_at) VALUES (?, ?, ?, ?, NOW())",
                [Auth::id(), 'RESET_DATA', 'system', json_encode(['tables_reset' => $tables])]
            );
            
            $_SESSION['flash_success'] = 'Data yang dipilih berhasil direset.';
        } catch (Exception $e) {
            $db->execute("SET FOREIGN_KEY_CHECKS = 1");
            $_SESSION['flash_error'] = 'Terjadi kesalahan saat mereset data: ' . $e->getMessage();
        }
        
        header('Location: ' . url('pengaturan-sistem/reset-data'));
        exit;
    }

    // =========================================================================
    // REFERENSI PEMBELAJARAN (MASTER MATA PELAJARAN)
    // =========================================================================

    public static function ensureMasterPembelajaranTable(): void
    {
        $db = Database::getInstance();
        $db->getConnection()->exec("
            CREATE TABLE IF NOT EXISTS `master_mata_pelajaran` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `kode_mapel` VARCHAR(30) DEFAULT NULL,
                `nama_mapel` VARCHAR(150) NOT NULL,
                `unit` VARCHAR(50) NOT NULL DEFAULT 'Semua Unit',
                `kelompok` VARCHAR(100) NOT NULL DEFAULT 'Umum',
                `urutan` INT NOT NULL DEFAULT 0,
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`unit`),
                INDEX (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $count = $db->find("SELECT COUNT(*) as c FROM `master_mata_pelajaran`")['c'] ?? 0;
        if ($count == 0) {
            $defaultMapels = [
                // PAI & Keislaman
                ['PAI-01', 'Al-Qur\'an Hadits', 'Semua Unit', 'Keagamaan / PAI', 1],
                ['PAI-02', 'Aqidah Akhlak', 'Semua Unit', 'Keagamaan / PAI', 2],
                ['PAI-03', 'Fiqih', 'Semua Unit', 'Keagamaan / PAI', 3],
                ['PAI-04', 'Sejarah Kebudayaan Islam (SKI)', 'Semua Unit', 'Keagamaan / PAI', 4],
                ['PAI-05', 'Bahasa Arab', 'Semua Unit', 'Keagamaan / PAI', 5],
                ['PAI-06', 'Tahfidz Al-Qur\'an', 'Semua Unit', 'Keagamaan / PAI', 6],
                ['PAI-07', 'Pendidikan Agama Islam & Budi Pekerti', 'Semua Unit', 'Keagamaan / PAI', 7],

                // Nasional & Umum
                ['UM-01', 'Pendidikan Pancasila & Kewarganegaraan (PPKn)', 'Semua Unit', 'Umum', 8],
                ['UM-02', 'Bahasa Indonesia', 'Semua Unit', 'Umum', 9],
                ['UM-03', 'Bahasa Inggris', 'Semua Unit', 'Umum', 10],
                ['UM-04', 'Matematika', 'Semua Unit', 'Umum', 11],
                ['UM-05', 'Ilmu Pengetahuan Alam (IPA)', 'SMP', 'Umum', 12],
                ['UM-06', 'Ilmu Pengetahuan Sosial (IPS)', 'SMP', 'Umum', 13],
                ['UM-07', 'Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'SD', 'Umum', 14],
                ['UM-08', 'Pendidikan Jasmani, Olahraga, & Kesehatan (PJOK)', 'Semua Unit', 'Umum', 15],
                ['UM-09', 'Seni Budaya & Prakarya (SBdP)', 'Semua Unit', 'Umum', 16],
                ['UM-10', 'Informatika / TIK', 'Semua Unit', 'Umum', 17],
                ['UM-11', 'Prakarya & Kewirausahaan', 'SMP', 'Umum', 18],
                ['UM-12', 'Bimbingan Konseling (BK)', 'Semua Unit', 'Pengembangan Diri', 19],

                // Khusus SMA / Peminatan
                ['SMA-01', 'Biologi', 'SMA', 'Peminatan MIPA', 20],
                ['SMA-02', 'Fisika', 'SMA', 'Peminatan MIPA', 21],
                ['SMA-03', 'Kimia', 'SMA', 'Peminatan MIPA', 22],
                ['SMA-04', 'Ekonomi', 'SMA', 'Peminatan IPS', 23],
                ['SMA-05', 'Geografi', 'SMA', 'Peminatan IPS', 24],
                ['SMA-06', 'Sosiologi', 'SMA', 'Peminatan IPS', 25],
                ['SMA-07', 'Sejarah', 'SMA', 'Peminatan IPS', 26],

                // Khusus SD & PAUD
                ['SD-01', 'Tematik / Guru Kelas', 'SD', 'Umum', 27],
                ['PAUD-01', 'Pengembangan Nilai Agama & Moral', 'PAUD', 'Umum', 28],
                ['PAUD-02', 'Pengembangan Fisik Motorik & Kognitif', 'PAUD', 'Umum', 29],
                ['PAUD-03', 'Pengembangan Bahasa & Sosial Emosional', 'PAUD', 'Umum', 30],

                // Muatan Lokal
                ['MULOK-01', 'Muatan Lokal / Bahasa Daerah (Kaili)', 'Semua Unit', 'Muatan Lokal', 31]
            ];

            foreach ($defaultMapels as $dm) {
                $db->insert('master_mata_pelajaran', [
                    'kode_mapel' => $dm[0],
                    'nama_mapel' => $dm[1],
                    'unit' => $dm[2],
                    'kelompok' => $dm[3],
                    'urutan' => $dm[4],
                    'is_active' => 1
                ]);
            }
        }
    }

    public static function masterPembelajaran(): void
    {
        self::ensureMasterPembelajaranTable();
        $pageTitle = 'Referensi Pembelajaran';
        $breadcrumbs = [['label' => 'Pengaturan', 'url' => url('pengaturan-sistem')], ['label' => 'Referensi Pembelajaran']];
        
        $db = Database::getInstance();
        $unitFilter = trim($_GET['unit'] ?? '');
        $search = trim($_GET['search'] ?? '');

        $where = "1=1";
        $params = [];

        if ($unitFilter && $unitFilter !== 'semua') {
            $where .= " AND (unit = ? OR unit = 'Semua Unit')";
            $params[] = $unitFilter;
        }

        if ($search) {
            $where .= " AND (nama_mapel LIKE ? OR kode_mapel LIKE ? OR kelompok LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $mataPelajaran = $db->findAll("SELECT * FROM master_mata_pelajaran WHERE {$where} ORDER BY urutan ASC, nama_mapel ASC", $params);
        $totalMapel = $db->find("SELECT COUNT(*) as c FROM master_mata_pelajaran")['c'] ?? 0;
        $totalAktif = $db->find("SELECT COUNT(*) as c FROM master_mata_pelajaran WHERE is_active = 1")['c'] ?? 0;

        ob_start();
        include MODULES_PATH . '/pengaturan-sistem/views/master-pembelajaran.php';
        $content = ob_get_clean();
        
        $customSidebar = MODULES_PATH . '/pengaturan-sistem/views/sidebar.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function storeMataPelajaran(): void
    {
        self::ensureMasterPembelajaranTable();
        if (!CSRF::validate()) { 
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Token tidak valid.');
            return;
        }

        $namaMapel = trim($_POST['nama_mapel'] ?? '');
        if (empty($namaMapel)) {
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Nama mata pelajaran wajib diisi.');
            return;
        }

        $db = Database::getInstance();
        $db->insert('master_mata_pelajaran', [
            'kode_mapel' => trim($_POST['kode_mapel'] ?? '') ?: null,
            'nama_mapel' => $namaMapel,
            'unit' => trim($_POST['unit'] ?? 'Semua Unit'),
            'kelompok' => trim($_POST['kelompok'] ?? 'Umum'),
            'urutan' => max(0, intval($_POST['urutan'] ?? 0)),
            'is_active' => !empty($_POST['is_active']) ? 1 : 0
        ]);

        Response::withSuccess(url('pengaturan-sistem/master-pembelajaran'), 'Mata pelajaran berhasil ditambahkan.');
    }

    public static function updateMataPelajaran(string $id): void
    {
        self::ensureMasterPembelajaranTable();
        if (!CSRF::validate()) { 
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Token tidak valid.');
            return;
        }

        $namaMapel = trim($_POST['nama_mapel'] ?? '');
        if (empty($namaMapel)) {
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Nama mata pelajaran wajib diisi.');
            return;
        }

        $db = Database::getInstance();
        $db->update('master_mata_pelajaran', [
            'kode_mapel' => trim($_POST['kode_mapel'] ?? '') ?: null,
            'nama_mapel' => $namaMapel,
            'unit' => trim($_POST['unit'] ?? 'Semua Unit'),
            'kelompok' => trim($_POST['kelompok'] ?? 'Umum'),
            'urutan' => max(0, intval($_POST['urutan'] ?? 0)),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ], 'id = ?', [$id]);

        Response::withSuccess(url('pengaturan-sistem/master-pembelajaran'), 'Mata pelajaran berhasil diperbarui.');
    }

    public static function toggleAktifMataPelajaran(string $id): void
    {
        self::ensureMasterPembelajaranTable();
        if (!CSRF::validate()) { 
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $mapel = $db->find("SELECT * FROM master_mata_pelajaran WHERE id = ?", [$id]);
        if ($mapel) {
            $newStatus = $mapel['is_active'] ? 0 : 1;
            $db->update('master_mata_pelajaran', ['is_active' => $newStatus], 'id = ?', [$id]);
            $msg = $newStatus ? "Mata pelajaran '{$mapel['nama_mapel']}' diaktifkan." : "Mata pelajaran '{$mapel['nama_mapel']}' dinonaktifkan.";
            Response::withSuccess(url('pengaturan-sistem/master-pembelajaran'), $msg);
        } else {
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Data tidak ditemukan.');
        }
    }

    public static function deleteMataPelajaran(string $id): void
    {
        self::ensureMasterPembelajaranTable();
        if (!CSRF::validate()) { 
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Token tidak valid.');
            return;
        }

        $db = Database::getInstance();
        $db->delete('master_mata_pelajaran', 'id = ?', [$id]);
        Response::withSuccess(url('pengaturan-sistem/master-pembelajaran'), 'Mata pelajaran berhasil dihapus.');
    }

    public static function bulkDeleteMataPelajaran(): void
    {
        self::ensureMasterPembelajaranTable();
        if (!CSRF::validate()) { 
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Token tidak valid.');
            return;
        }

        $ids = $_POST['ids'] ?? [];
        if (empty($ids) || !is_array($ids)) {
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Tidak ada mata pelajaran yang dipilih.');
            return;
        }

        $cleanIds = array_filter(array_map('intval', $ids));
        if (empty($cleanIds)) {
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'ID mata pelajaran tidak valid.');
            return;
        }

        $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
        $db = Database::getInstance();
        $db->query("DELETE FROM master_mata_pelajaran WHERE id IN ($placeholders)", $cleanIds);

        Response::withSuccess(url('pengaturan-sistem/master-pembelajaran'), count($cleanIds) . ' mata pelajaran terpilih berhasil dihapus.');
    }

    public static function deleteAllMataPelajaran(): void
    {
        self::ensureMasterPembelajaranTable();
        if (!CSRF::validate()) { 
            Response::withError(url('pengaturan-sistem/master-pembelajaran'), 'Token tidak valid.');
            return;
        }

        $unitFilter = trim($_POST['unit'] ?? '');
        $db = Database::getInstance();

        if ($unitFilter && $unitFilter !== 'semua' && $unitFilter !== 'Semua Unit') {
            $db->query("DELETE FROM master_mata_pelajaran WHERE unit = ?", [$unitFilter]);
            Response::withSuccess(url('pengaturan-sistem/master-pembelajaran?unit=' . urlencode($unitFilter)), "Seluruh mata pelajaran untuk unit '{$unitFilter}' berhasil dihapus.");
        } else {
            $db->query("DELETE FROM master_mata_pelajaran");
            Response::withSuccess(url('pengaturan-sistem/master-pembelajaran'), 'Seluruh data master mata pelajaran berhasil dihapus.');
        }
    }
}

