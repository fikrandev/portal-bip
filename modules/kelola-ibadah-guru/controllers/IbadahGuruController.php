<?php

class IbadahGuruController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola Ibadah Guru';
        $breadcrumbs = [['label' => 'Kelola Ibadah Guru']];
        
        ob_start();
        include MODULES_PATH . '/kelola-ibadah-guru/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
