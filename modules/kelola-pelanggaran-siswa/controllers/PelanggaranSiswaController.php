<?php

class PelanggaranSiswaController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Pelanggaran Siswa';
        $breadcrumbs = [['label' => 'Kelola Pelanggaran Siswa']];
        
        ob_start();
        include MODULES_PATH . '/kelola-pelanggaran-siswa/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
