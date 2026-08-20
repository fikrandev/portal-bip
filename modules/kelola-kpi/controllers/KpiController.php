<?php

class KpiController
{
    public static function index(): void
    {
        $pageTitle = 'Kelola KPI';
        $breadcrumbs = [['label' => 'Kelola KPI']];
        
        ob_start();
        include MODULES_PATH . '/kelola-kpi/views/index.php';
        $content = ob_get_clean();
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
