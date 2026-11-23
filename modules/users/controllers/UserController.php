<?php
/**
 * User Controller
 * CRUD operations for user management
 */

class UserController
{
    private static function ensureOtorisasiColumns(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $pdo = Database::getInstance()->getConnection();
            $col1 = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'jenis_otorisasi'")->fetch();
            if (!$col1) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `jenis_otorisasi` ENUM('admin', 'personal') NOT NULL DEFAULT 'admin' AFTER `allowed_jenjang`");
            }
            $col2 = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'pegawai_id'")->fetch();
            if (!$col2) {
                $pdo->exec("ALTER TABLE `users` ADD COLUMN `pegawai_id` INT(11) DEFAULT NULL AFTER `id`");
            }
        } catch (Throwable $e) {
            // Ignore if already exists
        }
    }

    private static function resolvePegawaiIdForUser(?int $pegawaiId, string $fullName, string $usernameOrEmail, string $phone = ''): ?int
    {
        if ($pegawaiId && $pegawaiId > 0) {
            return $pegawaiId;
        }
        try {
            $db = Database::getInstance();
            $emailTrim = trim($usernameOrEmail);
            if ($emailTrim !== '' && strpos($emailTrim, '@') !== false) {
                $p = $db->find("SELECT id FROM pegawai WHERE LOWER(TRIM(email)) = LOWER(?) ORDER BY is_active DESC, id ASC LIMIT 1", [$emailTrim]);
                if ($p && !empty($p['id'])) return (int)$p['id'];
            }
            $nameTrim = trim($fullName);
            $baseName = trim(explode(',', $nameTrim)[0]);
            if ($baseName !== '') {
                $p = $db->find(
                    "SELECT id FROM pegawai 
                     WHERE LOWER(TRIM(nama)) = LOWER(?) 
                        OR LOWER(TRIM(nama)) = LOWER(?) 
                        OR LOWER(CONCAT(TRIM(nama), ' ', COALESCE(TRIM(gelar), ''))) LIKE LOWER(?)
                        OR LOWER(TRIM(nama)) LIKE LOWER(?)
                     ORDER BY is_active DESC, id ASC LIMIT 1",
                    [$nameTrim, $baseName, '%' . $nameTrim . '%', '%' . $baseName . '%']
                );
                if ($p && !empty($p['id'])) return (int)$p['id'];
            }
            $phoneTrim = trim($phone);
            if ($phoneTrim !== '') {
                $p = $db->find("SELECT id FROM pegawai WHERE TRIM(no_wa) = ? ORDER BY is_active DESC, id ASC LIMIT 1", [$phoneTrim]);
                if ($p && !empty($p['id'])) return (int)$p['id'];
            }
        } catch (Throwable $e) {}
        return null;
    }

    public static function index(): void
    {
        self::ensureOtorisasiColumns();
        $pageTitle = 'Kelola Pengguna';
        $breadcrumbs = [['label' => 'Kelola Pengguna']];
        
        $db = Database::getInstance();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        $search = trim($_GET['search'] ?? '');
        
        $where = '1=1';
        $params = [];
        if ($search) {
            $where .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $total = $db->find("SELECT COUNT(*) as total FROM users u WHERE {$where}", $params)['total'];
        $users = $db->findAll(
            "SELECT u.*, GROUP_CONCAT(r.name SEPARATOR ', ') as role_names 
             FROM users u 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE {$where} 
             GROUP BY u.id 
             ORDER BY u.created_at DESC 
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
        
        $totalPages = ceil($total / $limit);
        
        ob_start();
        include MODULES_PATH . '/users/views/index.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $pageTitle = 'Tambah Pengguna';
        $breadcrumbs = [
            ['label' => 'Kelola Pengguna', 'url' => url('users')],
            ['label' => 'Tambah']
        ];
        $db = Database::getInstance();
        $roles = $db->findAll("SELECT * FROM roles ORDER BY name ASC");
        $pegawaiList = $db->findAll("SELECT id, nama, email, no_wa, niy FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        
        ob_start();
        include MODULES_PATH . '/users/views/create.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('users'), 'Token keamanan tidak valid.');
            return;
        }
        
        $validator = Validator::make($_POST)
            ->required('full_name', 'Nama lengkap')
            ->required('username', 'Username')
            ->unique('username', 'users', 'username', null, 'Username')
            ->required('password', 'Password')
            ->minLength('password', 8, 'Password')
            ->confirmed('password', 'password_confirmation', 'Password');
        
        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }
        
        $db = Database::getInstance();
        $db->beginTransaction();
        
        try {
            $allowedJenjang = null;
            if (!empty($_POST['allowed_jenjang']) && is_array($_POST['allowed_jenjang'])) {
                $cleanJenjang = array_filter(array_map('trim', $_POST['allowed_jenjang']));
                if (!empty($cleanJenjang)) {
                    $allowedJenjang = implode(',', array_map('strtoupper', $cleanJenjang));
                }
            }

            $jenisOtorisasi = strtolower(trim((string)($_POST['jenis_otorisasi'] ?? 'admin'))) === 'personal' ? 'personal' : 'admin';
            self::ensureOtorisasiColumns();
            $rawPegawaiId = !empty($_POST['pegawai_id']) ? (int)$_POST['pegawai_id'] : null;
            $pegawaiId = self::resolvePegawaiIdForUser(
                $rawPegawaiId,
                trim((string)($_POST['full_name'] ?? '')),
                trim((string)($_POST['username'] ?? '')),
                trim((string)($_POST['phone'] ?? ''))
            );

            $insertData = [
                'uuid'            => self::generateUUID(),
                'full_name'       => trim($_POST['full_name']),
                'email'           => trim($_POST['username']), // set email to username
                'username'        => trim($_POST['username']),
                'password'        => password_hash($_POST['password'], PASSWORD_BCRYPT),
                'phone'           => trim((string)($_POST['phone'] ?? '')),
                'allowed_jenjang' => $allowedJenjang,
                'jenis_otorisasi' => $jenisOtorisasi,
                'pegawai_id'      => $pegawaiId,
                'is_active'       => isset($_POST['is_active']) ? 1 : 0,
            ];

            $userId = $db->insert('users', $insertData);
            
            // Assign roles
            if (!empty($_POST['roles'])) {
                foreach ($_POST['roles'] as $roleId) {
                    RBAC::assignRole((int) $userId, (int) $roleId);
                }
            }
            
            $db->commit();
            Response::withSuccess(url('users'), 'Pengguna berhasil ditambahkan.');
        } catch (Exception $e) {
            $db->rollback();
            error_log('User create error: ' . $e->getMessage());
            Response::withError(url('users/create'), 'Gagal menambahkan pengguna.');
        }
    }

    public static function edit(string $id): void
    {
        $db = Database::getInstance();
        $user = $db->find("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            Response::withError(url('users'), 'Pengguna tidak ditemukan.');
            return;
        }
        
        $pageTitle = 'Edit Pengguna';
        $breadcrumbs = [
            ['label' => 'Kelola Pengguna', 'url' => url('users')],
            ['label' => 'Edit']
        ];
        $roles = $db->findAll("SELECT * FROM roles ORDER BY name ASC");
        $pegawaiList = $db->findAll("SELECT id, nama, email, no_wa, niy FROM pegawai WHERE is_active = 1 ORDER BY nama ASC");
        $userRoleIds = array_column(RBAC::getUserRoles((int) $id), 'id');
        
        ob_start();
        include MODULES_PATH . '/users/views/edit.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(string $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('users'), 'Token keamanan tidak valid.');
            return;
        }
        
        $db = Database::getInstance();
        $user = $db->find("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            Response::withError(url('users'), 'Pengguna tidak ditemukan.');
            return;
        }

        $validator = Validator::make($_POST)
            ->required('full_name', 'Nama lengkap')
            ->required('username', 'Username')
            ->unique('username', 'users', 'username', (int) $id, 'Username');
        
        if (!empty($_POST['password'])) {
            $validator->minLength('password', 8, 'Password');
        }
        
        if ($validator->fails()) {
            Response::backWithErrors($validator->errors(), $_POST);
            return;
        }
        
        $db->beginTransaction();
        try {
            $allowedJenjang = null;
            if (!empty($_POST['allowed_jenjang']) && is_array($_POST['allowed_jenjang'])) {
                $cleanJenjang = array_filter(array_map('trim', $_POST['allowed_jenjang']));
                if (!empty($cleanJenjang)) {
                    $allowedJenjang = implode(',', array_map('strtoupper', $cleanJenjang));
                }
            }

            $jenisOtorisasi = strtolower(trim((string)($_POST['jenis_otorisasi'] ?? 'admin'))) === 'personal' ? 'personal' : 'admin';
            self::ensureOtorisasiColumns();
            $rawPegawaiId = !empty($_POST['pegawai_id']) ? (int)$_POST['pegawai_id'] : (!empty($user['pegawai_id']) ? (int)$user['pegawai_id'] : null);
            $pegawaiId = self::resolvePegawaiIdForUser(
                $rawPegawaiId,
                trim((string)($_POST['full_name'] ?? '')),
                trim((string)($_POST['username'] ?? '')),
                trim((string)($_POST['phone'] ?? ($user['phone'] ?? '')))
            );

            $data = [
                'full_name'       => trim($_POST['full_name']),
                'email'           => trim($_POST['username']), // set email to username
                'username'        => trim($_POST['username']),
                'phone'           => trim((string)($_POST['phone'] ?? ($user['phone'] ?? ''))),
                'allowed_jenjang' => $allowedJenjang,
                'jenis_otorisasi' => $jenisOtorisasi,
                'pegawai_id'      => $pegawaiId,
                'is_active'       => isset($_POST['is_active']) ? 1 : 0,
            ];
            if (!empty($_POST['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            }
            
            $db->update('users', $data, 'id = ?', [$id]);
            
            // Update roles
            $db->delete('user_roles', 'user_id = ?', [$id]);
            if (!empty($_POST['roles'])) {
                foreach ($_POST['roles'] as $roleId) {
                    RBAC::assignRole((int) $id, (int) $roleId);
                }
            }
            
            $db->commit();
            Response::withSuccess(url('users'), 'Pengguna berhasil diperbarui.');
        } catch (Exception $e) {
            $db->rollback();
            error_log('User update error: ' . $e->getMessage());
            Response::withError(url('users/edit/' . $id), 'Gagal memperbarui pengguna.');
        }
    }

    public static function delete(string $id): void
    {
        if (!CSRF::validate()) {
            Response::withError(url('users'), 'Token keamanan tidak valid.');
            return;
        }
        
        if ((int) $id === Auth::id()) {
            Response::withError(url('users'), 'Anda tidak dapat menghapus akun sendiri.');
            return;
        }
        
        $db = Database::getInstance();
        $db->delete('users', 'id = ?', [$id]);
        Response::withSuccess(url('users'), 'Pengguna berhasil dihapus.');
    }

    private static function generateUUID(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
