<?php
define('BASE_PATH', dirname(__DIR__));
require 'config/database.php';
require 'core/Database.php';
$db = Database::getInstance();

try {
    $db->beginTransaction();
    
    // Check module
    $module = $db->find("SELECT * FROM modules WHERE slug = 'kelola-sarpras'");
    if (!$module) {
        $moduleId = $db->insert('modules', [
            'name' => 'Kelola Sarpras',
            'slug' => 'kelola-sarpras',
            'sort_order' => 20,
            'icon_svg' => '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.75M5.25 9h3.75m-3.75 3h3.75m-3.75 3h3.75m3.75-6h3.75m-3.75 3h3.75m-3.75 3h3.75M9 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>',
            'color' => 'indigo'
        ]);
    } else {
        $moduleId = $module['id'];
    }

    $perms = [
        ['name' => 'Lihat Data Sarpras', 'slug' => 'sarpras.view', 'description' => 'Melihat data inventaris, ruangan, dan peminjaman'],
        ['name' => 'Tambah Data Sarpras', 'slug' => 'sarpras.create', 'description' => 'Menambah aset, barang, dan ruangan'],
        ['name' => 'Edit Data Sarpras', 'slug' => 'sarpras.edit', 'description' => 'Mengubah data aset dan kondisi'],
        ['name' => 'Hapus Data Sarpras', 'slug' => 'sarpras.delete', 'description' => 'Menghapus data sarpras'],
        ['name' => 'Persetujuan (Approve) Sarpras', 'slug' => 'sarpras.approve', 'description' => 'Menyetujui peminjaman dan pengajuan'],
        ['name' => 'Akses Mobile Sarpras', 'slug' => 'sarpras.mobile', 'description' => 'Mengakses fitur Sarpras via Aplikasi Mobile']
    ];

    foreach ($perms as $p) {
        $exists = $db->find("SELECT * FROM permissions WHERE slug = ?", [$p['slug']]);
        if (!$exists) {
            $db->insert('permissions', [
                'module_id' => $moduleId,
                'name' => $p['name'],
                'slug' => $p['slug'],
                'description' => $p['description']
            ]);
            echo "Inserted permission: {$p['slug']}\n";
        } else {
            echo "Permission already exists: {$p['slug']}\n";
        }
    }

    $db->commit();
    echo "Done!";
} catch (Exception $e) {
    $db->rollback();
    echo "Error: " . $e->getMessage();
}
