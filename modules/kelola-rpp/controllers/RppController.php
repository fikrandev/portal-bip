<?php

class RppController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola RPP';
        $breadcrumbs = [['label' => 'Kelola RPP']];
        
        ob_start();
        include MODULES_PATH . '/kelola-rpp/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
