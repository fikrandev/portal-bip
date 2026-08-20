<?php

class AbsenPegawaiController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Absen Pegawai';
        $breadcrumbs = [['label' => 'Kelola Absen Pegawai']];
        
        ob_start();
        include MODULES_PATH . '/kelola-absen-pegawai/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
