<?php
/**
 * Module Controller
 * CRUD for dynamic module management
 */

class ModuleController
{
    public static function index(): void
    {
        $pageTitle = 'Manajemen Modul';
        $breadcrumbs = [['label' => 'Manajemen Modul']];
        $db = Database::getInstance();
        $modules = $db->findAll("SELECT * FROM modules ORDER BY sort_order ASC");
        
        ob_start();
        include MODULES_PATH . '/modules-manager/views/index.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function create(): void
    {
        $pageTitle = 'Tambah Modul';
        $breadcrumbs = [['label' => 'Manajemen Modul', 'url' => url('modules-manager')], ['label' => 'Tambah']];
        
        $db = Database::getInstance();
        $groups = $db->findAll("SELECT DISTINCT module_group FROM modules WHERE module_group IS NOT NULL AND module_group != '' ORDER BY module_group ASC");
        
        ob_start();
        include MODULES_PATH . '/modules-manager/views/create.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function store(): void
    {
        if (!CSRF::validate()) { Response::withError(url('modules-manager'), 'Token tidak valid.'); return; }
        
        $validator = Validator::make($_POST)
            ->required('name', 'Nama Modul')
            ->required('slug', 'Slug')
            ->required('route', 'Route');
        if ($validator->fails()) { Response::backWithErrors($validator->errors(), $_POST); return; }
        
        $db = Database::getInstance();
        $db->insert('modules', [
            'name'         => trim($_POST['name']),
            'slug'         => trim($_POST['slug']),
            'description'  => trim($_POST['description'] ?? ''),
            'module_group' => trim($_POST['module_group'] ?? '') ?: null,
            'icon_svg'     => $_POST['icon_svg'] ?? '',
            'color'        => $_POST['color'] ?? '#0EA5E9',
            'route'        => trim($_POST['route']),
            'sort_order'   => intval($_POST['sort_order'] ?? 0),
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ]);
        Response::withSuccess(url('modules-manager'), 'Modul berhasil ditambahkan.');
    }

    public static function edit(string $id): void
    {
        $db = Database::getInstance();
        $module = $db->find("SELECT * FROM modules WHERE id = ?", [$id]);
        if (!$module) { Response::withError(url('modules-manager'), 'Modul tidak ditemukan.'); return; }
        
        $groups = $db->findAll("SELECT DISTINCT module_group FROM modules WHERE module_group IS NOT NULL AND module_group != '' ORDER BY module_group ASC");
        
        $pageTitle = 'Edit Modul';
        $breadcrumbs = [['label' => 'Manajemen Modul', 'url' => url('modules-manager')], ['label' => 'Edit']];
        
        ob_start();
        include MODULES_PATH . '/modules-manager/views/edit.php';
        $content = ob_get_clean();
        $customSidebar = TEMPLATES_PATH . '/partials/sidebar_settings.php';
        include TEMPLATES_PATH . '/layouts/app.php';
    }

    public static function update(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('modules-manager'), 'Token tidak valid.'); return; }
        
        $db = Database::getInstance();
        $db->update('modules', [
            'name'         => trim($_POST['name']),
            'description'  => trim($_POST['description'] ?? ''),
            'module_group' => trim($_POST['module_group'] ?? '') ?: null,
            'icon_svg'     => $_POST['icon_svg'] ?? '',
            'color'        => $_POST['color'] ?? '#0EA5E9',
            'route'        => trim($_POST['route']),
            'sort_order'   => intval($_POST['sort_order'] ?? 0),
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
        ], 'id = ?', [$id]);
        Response::withSuccess(url('modules-manager'), 'Modul berhasil diperbarui.');
    }

    public static function delete(string $id): void
    {
        if (!CSRF::validate()) { Response::withError(url('modules-manager'), 'Token tidak valid.'); return; }
        $db = Database::getInstance();
        $db->delete('modules', 'id = ?', [$id]);
        Response::withSuccess(url('modules-manager'), 'Modul berhasil dihapus.');
    }
}
