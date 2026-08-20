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
        $pageTitle = 'Tambah Peran';
        $breadcrumbs = [['label' => 'Kelola Peran', 'url' => url('roles')], ['label' => 'Tambah']];
        $db = Database::getInstance();
        $permissions = $db->findAll("SELECT p.*, m.name as module_name FROM permissions p LEFT JOIN modules m ON p.module_id = m.id ORDER BY m.sort_order, p.id");
        
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
            $roleId = $db->insert('roles', [
                'name' => trim($_POST['name']),
                'slug' => trim($_POST['slug']),
                'description' => trim($_POST['description'] ?? ''),
            ]);
            if (!empty($_POST['permissions'])) {
                foreach ($_POST['permissions'] as $permId) {
                    $db->insert('role_permissions', ['role_id' => $roleId, 'permission_id' => $permId]);
                }
            }
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
        
        $pageTitle = 'Edit Peran';
        $breadcrumbs = [['label' => 'Kelola Peran', 'url' => url('roles')], ['label' => 'Edit']];
        $permissions = $db->findAll("SELECT p.*, m.name as module_name FROM permissions p LEFT JOIN modules m ON p.module_id = m.id ORDER BY m.sort_order, p.id");
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
            $db->update('roles', [
                'name' => trim($_POST['name']),
                'description' => trim($_POST['description'] ?? ''),
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
