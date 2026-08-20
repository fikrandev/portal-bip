<?php

class QuranPegawaiController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Qur'an Pegawai';
        $breadcrumbs = [['label' => 'Kelola Qur'an Pegawai']];
        
        ob_start();
        include MODULES_PATH . '/kelola-quran-pegawai/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
