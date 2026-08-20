<?php

class CutiController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Cuti';
        $breadcrumbs = [['label' => 'Kelola Cuti']];
        
        ob_start();
        include MODULES_PATH . '/kelola-cuti/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
