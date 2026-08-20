<?php

class SpmbController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola SPMB';
        $breadcrumbs = [['label' => 'Kelola SPMB']];
        
        ob_start();
        include MODULES_PATH . '/kelola-spmb/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
