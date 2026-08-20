<?php

class RaportController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Raport';
        $breadcrumbs = [['label' => 'Kelola Raport']];
        
        ob_start();
        include MODULES_PATH . '/kelola-raport/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
