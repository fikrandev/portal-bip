<?php

class AbsenSiswaController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Absen Siswa';
        $breadcrumbs = [['label' => 'Kelola Absen Siswa']];
        
        ob_start();
        include MODULES_PATH . '/kelola-absen-siswa/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
