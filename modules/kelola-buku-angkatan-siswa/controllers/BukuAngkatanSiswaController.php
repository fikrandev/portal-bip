<?php

class BukuAngkatanSiswaController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Buku Angkatan Siswa';
        $breadcrumbs = [['label' => 'Kelola Buku Angkatan Siswa']];
        
        ob_start();
        include MODULES_PATH . '/kelola-buku-angkatan-siswa/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
