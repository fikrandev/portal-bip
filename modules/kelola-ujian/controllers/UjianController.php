<?php

class UjianController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Ujian';
        $breadcrumbs = [['label' => 'Kelola Ujian']];
        
        ob_start();
        include MODULES_PATH . '/kelola-ujian/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
