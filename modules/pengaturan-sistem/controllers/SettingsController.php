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
        
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [trim($_POST[$key]), $key]);
            }
        }
        
        // Handle file uploads
        $uploadDir = BASE_PATH . '/public/uploads/settings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Logo
        if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['app_logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'svg'])) {
                $filename = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['app_logo']['tmp_name'], $uploadDir . $filename)) {
                    $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = 'app_logo'", ['/public/uploads/settings/' . $filename]);
                }
            }
        }
        
        // Favicon
        if (isset($_FILES['app_favicon']) && $_FILES['app_favicon']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['app_favicon']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'ico', 'svg'])) {
                $filename = 'favicon_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['app_favicon']['tmp_name'], $uploadDir . $filename)) {
                    $db->query("UPDATE settings SET setting_value = ? WHERE setting_key = 'app_favicon'", ['/public/uploads/settings/' . $filename]);
                }
            }
        }
        
        $db->query("INSERT INTO audit_logs (user_id, action, entity_type, created_at) VALUES (?, ?, ?, NOW())", [Auth::id(), 'UPDATE', 'settings']);
        
        $_SESSION['flash_success'] = 'Pengaturan berhasil diperbarui.';
        
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
}
