<?php

class QuranSiswaController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Qur'an Siswa';
        $breadcrumbs = [['label' => 'Kelola Qur'an Siswa']];
        
        ob_start();
        include MODULES_PATH . '/kelola-quran-siswa/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
