<?php
/**
 * Role Controller
 * CRUD operations for role management with permission matrix
 */

class RoleController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Peran';
        $breadcrumbs = [['label' => 'Kelola Peran']];
        
        $db = Database::getInstance();
        $roles = $db->findAll("SELECT r.*, (SELECT COUNT(*) FROM user_roles ur WHERE ur.role_id = r.id) as user_count FROM roles r ORDER BY r.id ASC");
        
        ob_start();
        include MODULES_PATH . '/roles/views/index.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $db = Database::getInstance();
        $fromId = !empty($_GET['from']) ? (int)$_GET['from'] : null;
        $sourceRole = null;
        $selectedPermIds = [];
        $sourceJenjang = [];

        if ($fromId) {
            $sourceRole = $db->find("SELECT * FROM roles WHERE id = ?", [$fromId]);
            if ($sourceRole) {
                $rawPerms = $db->findAll("SELECT permission_id FROM role_permissions WHERE role_id = ?", [$fromId]);
                $selectedPermIds = array_map('intval', array_column($rawPerms, 'permission_id'));
                if (!empty($sourceRole['allowed_jenjang'])) {
                    $sourceJenjang = explode(',', strtoupper($sourceRole['allowed_jenjang']));
                }
            }
        }

        $pageTitle = $sourceRole ? 'Duplikat Peran: ' . $sourceRole['name'] : 'Tambah Peran';
        $breadcrumbs = [['label' => 'Kelola Peran', 'url' => url('roles')], ['label' => $sourceRole ? 'Duplikat' : 'Tambah']];
        $permissions = $db->findAll("SELECT p.*, m.name as module_name, m.slug as module_slug, m.icon_svg, m.color FROM permissions p LEFT JOIN modules m ON p.module_id = m.id ORDER BY m.sort_order, m.id, p.id");
        
        ob_start();
        include MODULES_PATH . '/roles/views/create.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) { Response::withError(url('roles'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)->required('name', 'Nama Peran')->required('slug', 'Slug');
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
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

            $roleId = $db->insert('roles', [
                'name' => trim($_POST['name']),
                'slug' => trim($_POST['slug']),
                'description' => trim($_POST['description'] ?? ''),
                'allowed_jenjang' => $allowedJenjang,
            ]);
            if (!empty($_POST['permissions'])) {
                foreach ($_POST['permissions'] as $permId) {
                    $db->insert('role_permissions', ['role_id' => $roleId, 'permission_id' => $permId]);
                }
            }
            RBAC::clearCache();
            $db->commit();
            Response::withSuccess(url('roles'), 'Peran berhasil ditambahkan.');
        } catch (Exception $e) {
            $db->rollback();
            Response::withError(url('roles/create'), 'Gagal menambahkan peran.');
        }
    }

    public static function edit(string $id): void
    {
        $db = Database::getInstance();
        $role = $db->find("SELECT * FROM roles WHERE id = ?", [$id]);
        if (!$role) { Response::withError(url('roles'), 'Peran tidak ditemukan.'); return; }
        
        $pageTitle = 'Edit Peran: ' . $role['name'];
        $breadcrumbs = [['label' => 'Kelola Peran', 'url' => url('roles')], ['label' => 'Edit']];
        $permissions = $db->findAll("SELECT p.*, m.name as module_name, m.slug as module_slug, m.icon_svg, m.color FROM permissions p LEFT JOIN modules m ON p.module_id = m.id ORDER BY m.sort_order, m.id, p.id");
        $rolePermIds = array_column($db->findAll("SELECT permission_id FROM role_permissions WHERE role_id = ?", [$id]), 'permission_id');
        
        ob_start();
        include MODULES_PATH . '/roles/views/edit.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('roles'), 'Token tidak valid.'); return; }
        
        $db = Database::getInstance();
        $role = $db->find("SELECT * FROM roles WHERE id = ?", [$id]);
        if (!$role) { Response::withError(url('roles'), 'Peran tidak ditemukan.'); return; }
        
        $db->beginTransaction();
        try {
            $allowedJenjang = null;
            if (!empty($_POST['allowed_jenjang']) && is_array($_POST['allowed_jenjang'])) {
                $cleanJenjang = array_filter(array_map('trim', $_POST['allowed_jenjang']));
                if (!empty($cleanJenjang)) {
                    $allowedJenjang = implode(',', array_map('strtoupper', $cleanJenjang));
                }
            }

            $db->update('roles', [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'] ?? ''),
                'allowed_jenjang' => $allowedJenjang,
            ], 'id = ?', [$id]);
            
            $db->delete('role_permissions', 'role_id = ?', [$id]);
            if (!empty($_POST['permissions'])) {
                foreach ($_POST['permissions'] as $permId) {
                    $db->insert('role_permissions', ['role_id' => $id, 'permission_id' => $permId]);
                }
            }
            RBAC::clearCache();
            $db->commit();
            Response::withSuccess(url('roles'), 'Peran berhasil diperbarui.');
        } catch (Exception $e) {
            $db->rollback();
            Response::withError(url('roles/edit/' . $id), 'Gagal memperbarui peran.');
        }
    }

    public static function copy(string $id): void
    {
        Response::redirect(url('roles/create?from=' . (int)$id));
    }

    public static function duplicate(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('roles'), 'Token tidak valid.'); return; }
        
        $db = Database::getInstance();
        $sourceRole = $db->find("SELECT * FROM roles WHERE id = ?", [$id]);
        if (!$sourceRole) {
            Response::withError(url('roles'), 'Peran sumber tidak ditemukan.');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? ($sourceRole['description'] ?? ''));

        if (empty($name)) {
            $name = $sourceRole['name'] . ' (Salinan)';
        }
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $name));
        }

        // Pastikan slug unik
        $baseSlug = $slug;
        $counter = 1;
        while ($db->find("SELECT id FROM roles WHERE slug = ?", [$slug])) {
            $slug = $baseSlug . '_' . $counter;
            $counter++;
        }

        // Jenjang
        $allowedJenjang = null;
        if (isset($_POST['allowed_jenjang']) && is_array($_POST['allowed_jenjang'])) {
            $cleanJenjang = array_filter(array_map('trim', $_POST['allowed_jenjang']));
            if (!empty($cleanJenjang)) {
                $allowedJenjang = implode(',', array_map('strtoupper', $cleanJenjang));
            }
        } elseif (isset($_POST['allowed_jenjang']) && is_string($_POST['allowed_jenjang']) && trim($_POST['allowed_jenjang']) !== '') {
            $allowedJenjang = strtoupper(trim($_POST['allowed_jenjang']));
        } else {
            $allowedJenjang = $sourceRole['allowed_jenjang'] ?? null;
        }

        $db->beginTransaction();
        try {
            $newRoleId = $db->insert('roles', [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'allowed_jenjang' => $allowedJenjang,
                'is_system' => 0
            ]);

            // Duplikasi semua permission_id dari peran sumber
            $sourcePerms = $db->findAll("SELECT permission_id FROM role_permissions WHERE role_id = ?", [$id]);
            $countCopied = 0;
            foreach ($sourcePerms as $sp) {
                $db->insert('role_permissions', [
                    'role_id' => $newRoleId,
                    'permission_id' => $sp['permission_id']
                ]);
                $countCopied++;
            }

            RBAC::clearCache();
            $db->commit();

            Response::withSuccess(
                url('roles/edit/' . $newRoleId),
                "Peran '{$name}' berhasil disalin dari '{$sourceRole['name']}' ({$countCopied} hak akses telah otomatis diduplikasi). Silakan sesuaikan jika diperlukan."
            );
        } catch (Exception $e) {
            $db->rollback();
            Response::withError(url('roles'), 'Gagal menyalin peran: ' . $e->getMessage());
        }
    }

    public static function switchActiveJenjang(): void
    {
        if (!Auth::check()) {
            Response::redirect(url('login'));
            return;
        }

        $jenjang = trim($_POST['jenjang'] ?? $_GET['jenjang'] ?? '');
        if ($jenjang !== '') {
            Auth::setActiveJenjang($jenjang);
        }
        $redirect = $_SERVER['HTTP_REFERER'] ?? url('dashboard');
        Response::redirect($redirect);
    }

    public static function delete(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('roles'), 'Token tidak valid.'); return; }
        $db = Database::getInstance();
        $role = $db->find("SELECT * FROM roles WHERE id = ?", [$id]);
        if ($role && $role['is_system']) {
            Response::withError(url('roles'), 'Peran sistem tidak dapat dihapus.');
            return;
        }
        $db->delete('roles', 'id = ?', [$id]);
        Response::withSuccess(url('roles'), 'Peran berhasil dihapus.');
    }
}
