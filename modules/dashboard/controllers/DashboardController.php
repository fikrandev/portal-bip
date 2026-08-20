<?php
/**
 * Dashboard Controller
 * Main portal page showing accessible module cards
 */

class DashboardController
{
    /**
     * Show dashboard with module cards
     */
    public static function index(): void
    {
        $pageTitle = 'Dashboard';
        $pageDescription = 'Portal utama ' . APP_NAME;
        
        // Get accessible modules (skip "Dashboard" itself from cards)
        $allModules = RBAC::getAccessibleModules();
        $modules = array_filter($allModules, fn($m) => $m['slug'] !== 'dashboard');
        $modules = array_values($modules); // re-index
        
        // Get stats
        $db = Database::getInstance();
        $totalUsers = $db->count('users', 'is_active = 1');
        $totalModules = $db->count('modules', 'is_active = 1');
        $totalRoles = $db->count('roles');
        
        // Extra JS for dashboard
        $extraJs = '<script src="' . asset('js/dashboard.js') . '"></script>';
        
        $hideSidebar = true;
        
        ob_start();
        include MODULES_PATH . '/dashboard/views/index.php';
        $content = ob_get_clean();
        
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
