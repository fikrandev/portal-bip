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
        
        // Get stats (120s session-cached combined query)
        $now = time();
        if (
            isset($_SESSION['_dash_stats_cache']) &&
            is_array($_SESSION['_dash_stats_cache']) &&
            ($now - ($_SESSION['_dash_stats_cache']['ts'] ?? 0)) < 120
        ) {
            $totalUsers = (int)$_SESSION['_dash_stats_cache']['users'];
            $totalModules = (int)$_SESSION['_dash_stats_cache']['modules'];
            $totalRoles = (int)$_SESSION['_dash_stats_cache']['roles'];
        } else {
            $db = Database::getInstance();
            $statsRow = $db->find(
                "SELECT
                    (SELECT COUNT(*) FROM users WHERE is_active = 1) AS total_users,
                    (SELECT COUNT(*) FROM modules WHERE is_active = 1) AS total_modules,
                    (SELECT COUNT(*) FROM roles) AS total_roles"
            );
            $totalUsers = (int)($statsRow['total_users'] ?? 0);
            $totalModules = (int)($statsRow['total_modules'] ?? 0);
            $totalRoles = (int)($statsRow['total_roles'] ?? 0);
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['_dash_stats_cache'] = [
                    'ts' => $now,
                    'users' => $totalUsers,
                    'modules' => $totalModules,
                    'roles' => $totalRoles,
                ];
            }
        }
        
        // Extra JS for dashboard
        $extraJs = '<script src="' . asset('js/dashboard.js') . '"></script>';
        
        $hideSidebar = true;
        
        ob_start();
        include MODULES_PATH . '/dashboard/views/index.php';
        $content = ob_get_clean();
        
        include TEMPLATES_PATH . '/layouts/app.php';
    }
}
