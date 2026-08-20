<?php

class IzinGuruController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Izin Guru';
        $breadcrumbs = [['label' => 'Kelola Izin Guru']];
        
        ob_start();
        include MODULES_PATH . '/kelola-izin-guru/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
