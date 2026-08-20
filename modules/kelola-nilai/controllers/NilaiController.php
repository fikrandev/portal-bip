<?php

class NilaiController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Nilai';
        $breadcrumbs = [['label' => 'Kelola Nilai']];
        
        ob_start();
        include MODULES_PATH . '/kelola-nilai/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
