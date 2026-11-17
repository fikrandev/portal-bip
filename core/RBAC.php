<?php
/**
 * Portal BIP - RBAC (Role-Based Access Control)
 * 
 * Handles permission checking, module access control,
 * and permission caching for performance.
 */

class RBAC
{
    private static ?array $cachedPermissions = null;
    private static ?array $cachedModules = null;
    private static array $cachedModuleAccess = [];

    /**
     * Check if current user has a specific permission
     * 
     * @param string $permissionSlug  e.g., 'siswa.view', 'siswa.create'
     * @return bool
     */
    public static function hasPermission(string $permissionSlug): bool
    {
        // Super admin has all permissions
        if (Auth::isSuperAdmin()) {
            return true;
        }

        $permissions = self::getUserPermissions();
        if (in_array($permissionSlug, $permissions, true)) {
            return true;
        }

        $parts = explode('.', $permissionSlug);

        // 2-part legacy check (e.g. 'siswa.view', 'spmb.siswa_baru', 'sarpras.create')
        if (count($parts) === 2) {
            [$prefix, $actionOrFeature] = $parts;

            // Cek jika ini merupakan slug fitur SPMB tanpa .view (misal 'spmb.siswa_baru' -> 'spmb.siswa_baru.view')
            if (in_array($prefix . '.' . $actionOrFeature . '.view', $permissions, true)) {
                return true;
            }

            // Cek jika user memiliki izin granular '<prefix>.<fitur>.<action>' untuk aksi umum
            $dotPrefix = $prefix . '.';
            $dotAction = '.' . $actionOrFeature;
            foreach ($permissions as $p) {
                if (str_starts_with($p, $dotPrefix) && str_ends_with($p, $dotAction)) {
                    return true;
                }
            }

            // Jika cek 'spmb.view', cukup punya akses dashboard/fitur spmb
            if ($permissionSlug === 'spmb.view') {
                foreach ($permissions as $p) {
                    if (str_starts_with($p, 'spmb.')) {
                        return true;
                    }
                }
            }
        } elseif (count($parts) === 3) {
            // 3-part granular check (e.g. 'siswa.data.view' atau 'spmb.siswa_baru.view')
            [$prefix, $feature, $action] = $parts;
            // Fallback jika session masih menyimpan permission legacy 2-part
            if (in_array($prefix . '.' . $action, $permissions, true)) {
                return true;
            }
            if ($action === 'view' && in_array($prefix . '.' . $feature, $permissions, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if current user has any of the listed permissions
     */
    public static function hasAnyPermission(array $slugs): bool
    {
        if (Auth::isSuperAdmin()) {
            return true;
        }
        foreach ($slugs as $slug) {
            if (self::hasPermission($slug)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Alias for hasPermission()
     */
    public static function can(string $permissionSlug): bool
    {
        return self::hasPermission($permissionSlug);
    }

    /**
     * Check if current user has permission to access a specific dashboard feature
     * 
     * @param string $featureSlug e.g. 'siswa.dashboard' or 'siswa'
     * @return bool
     */
    public static function canAccessDashboardFeature(string $featureSlug): bool
    {
        if (Auth::isSuperAdmin()) {
            return true;
        }

        $permSlug = strpos($featureSlug, '.') !== false ? $featureSlug : $featureSlug . '.dashboard';
        return self::hasPermission($permSlug);
    }

    /**
     * Check if current user can access a module by slug
     * 
     * @param string $moduleSlug  e.g., 'kelola-siswa'
     * @return bool
     */
    public static function canAccessModule(string $moduleSlug): bool
    {
        if (Auth::isSuperAdmin()) {
            return true;
        }

        if (isset(self::$cachedModuleAccess[$moduleSlug])) {
            return self::$cachedModuleAccess[$moduleSlug];
        }

        $userId = Auth::id();
        if (!$userId) {
            return false;
        }

        // Reuse cached accessible modules list to avoid redundant multi-table JOINs
        $modules = self::getAccessibleModules();
        $hasAccess = false;
        foreach ($modules as $m) {
            if (($m['slug'] ?? '') === $moduleSlug) {
                $hasAccess = true;
                break;
            }
        }

        self::$cachedModuleAccess[$moduleSlug] = $hasAccess;
        return $hasAccess;
    }

    /**
     * Get all permissions for the current user (cached)
     * 
     * @return array  List of permission slugs
     */
    public static function getUserPermissions(): array
    {
        if (self::$cachedPermissions !== null) {
            return self::$cachedPermissions;
        }

        $userId = Auth::id();
        if (!$userId) {
            return [];
        }

        // Check session cache first
        if (isset($_SESSION['user_permissions']) && 
            isset($_SESSION['permissions_cached_at']) &&
            (time() - $_SESSION['permissions_cached_at']) < 300) { // 5 min cache
            self::$cachedPermissions = $_SESSION['user_permissions'];
            return self::$cachedPermissions;
        }

        $db = Database::getInstance();
        $rows = $db->findAll(
            "SELECT DISTINCT p.slug 
             FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             JOIN user_roles ur ON rp.role_id = ur.role_id
             WHERE ur.user_id = ?",
            [$userId]
        );

        self::$cachedPermissions = array_column($rows, 'slug');

        // Store in session for caching
        $_SESSION['user_permissions'] = self::$cachedPermissions;
        $_SESSION['permissions_cached_at'] = time();

        return self::$cachedPermissions;
    }

    /**
     * Get all modules accessible by the current user
     * 
     * @return array  List of module records
     */
    public static function getAccessibleModules(): array
    {
        if (self::$cachedModules !== null) {
            return self::$cachedModules;
        }

        $userId = Auth::id();
        if (!$userId) {
            return [];
        }

        $isSuperAdmin = Auth::isSuperAdmin();
        if (
            isset($_SESSION['rbac_modules_cache'], $_SESSION['rbac_modules_ts'], $_SESSION['rbac_modules_uid']) &&
            (int)$_SESSION['rbac_modules_uid'] === (int)$userId &&
            ($_SESSION['rbac_modules_sa'] ?? false) === $isSuperAdmin &&
            (time() - (int)$_SESSION['rbac_modules_ts']) < 300
        ) {
            self::$cachedModules = $_SESSION['rbac_modules_cache'];
            return self::$cachedModules;
        }

        $db = Database::getInstance();

        // Super admin sees all active modules
        if ($isSuperAdmin) {
            $modules = $db->findAll(
                "SELECT * FROM modules WHERE is_active = 1 ORDER BY sort_order ASC"
            );
        } else {
            $modules = $db->findAll(
                "SELECT DISTINCT m.* 
                 FROM modules m
                 JOIN module_permissions mp ON m.id = mp.module_id
                 JOIN role_permissions rp ON mp.permission_id = rp.permission_id
                 JOIN user_roles ur ON rp.role_id = ur.role_id
                 WHERE ur.user_id = ? AND m.is_active = 1
                 ORDER BY m.sort_order ASC",
                [$userId]
            );
        }

        self::$cachedModules = $modules;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['rbac_modules_cache'] = $modules;
            $_SESSION['rbac_modules_ts'] = time();
            $_SESSION['rbac_modules_uid'] = (int)$userId;
            $_SESSION['rbac_modules_sa'] = $isSuperAdmin;
        }

        return $modules;
    }

    /**
     * Get all roles for a specific user
     * 
     * @param int $userId
     * @return array
     */
    public static function getUserRoles(int $userId): array
    {
        $db = Database::getInstance();
        return $db->findAll(
            "SELECT r.* FROM roles r 
             JOIN user_roles ur ON r.id = ur.role_id 
             WHERE ur.user_id = ?",
            [$userId]
        );
    }

    /**
     * Assign a role to a user
     */
    public static function assignRole(int $userId, int $roleId): bool
    {
        $db = Database::getInstance();
        try {
            $db->insert('user_roles', [
                'user_id'     => $userId,
                'role_id'     => $roleId,
                'assigned_by' => Auth::id(),
            ]);
            self::clearCache();
            return true;
        } catch (PDOException $e) {
            // Duplicate entry is OK (already assigned)
            if ($e->getCode() == 23000) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * Revoke a role from a user
     */
    public static function revokeRole(int $userId, int $roleId): bool
    {
        $db = Database::getInstance();
        $db->delete('user_roles', 'user_id = ? AND role_id = ?', [$userId, $roleId]);
        self::clearCache();
        return true;
    }

    /**
     * Clear cached permissions (call after role/permission changes)
     */
    public static function clearCache(): void
    {
        self::$cachedPermissions = null;
        self::$cachedModules = null;
        self::$cachedModuleAccess = [];
        unset(
            $_SESSION['user_permissions'],
            $_SESSION['permissions_cached_at'],
            $_SESSION['rbac_modules_cache'],
            $_SESSION['rbac_modules_ts'],
            $_SESSION['rbac_modules_uid'],
            $_SESSION['rbac_modules_sa'],
            $_SESSION['user_allowed_jenjang'],
            $_SESSION['user_is_jenjang_restricted']
        );
    }

    /**
     * Get allowed jenjang configured for a specific role
     */
    public static function getRoleAllowedJenjang(int $roleId): array
    {
        $db = Database::getInstance();
        $row = $db->find("SELECT allowed_jenjang FROM roles WHERE id = ?", [$roleId]);
        if (!empty($row['allowed_jenjang'])) {
            return array_filter(array_map('trim', explode(',', strtoupper($row['allowed_jenjang']))));
        }
        return [];
    }

    /**
     * Get allowed jenjang configured for a specific user
     */
    public static function getUserAllowedJenjang(int $userId): array
    {
        $db = Database::getInstance();
        $user = $db->find(
            "SELECT u.allowed_jenjang as user_allowed_jenjang,
                    GROUP_CONCAT(r.allowed_jenjang) as role_allowed_jenjangs
             FROM users u
             LEFT JOIN user_roles ur ON u.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id
             WHERE u.id = ?
             GROUP BY u.id",
            [$userId]
        );
        if (!$user) {
            return [];
        }
        if (!empty($user['user_allowed_jenjang'])) {
            return array_filter(array_map('trim', explode(',', strtoupper($user['user_allowed_jenjang']))));
        }
        if (!empty($user['role_allowed_jenjangs'])) {
            return array_values(array_unique(array_filter(array_map('trim', explode(',', strtoupper($user['role_allowed_jenjangs']))))));
        }
        return [];
    }

    /**
     * Require a permission or return 403
     */
    public static function requirePermission(string $permissionSlug): void
    {
        if (!self::hasPermission($permissionSlug)) {
            http_response_code(403);
            include TEMPLATES_PATH . '/errors/403.php';
            exit;
        }
    }

    /**
     * Require module access or return 403
     */
    public static function requireModuleAccess(string $moduleSlug): void
    {
        if (!self::canAccessModule($moduleSlug)) {
            http_response_code(403);
            include TEMPLATES_PATH . '/errors/403.php';
            exit;
        }
    }

    /**
     * Redirect user to the first permitted sub-feature route, or render 403 Forbidden.
     *
     * @param array $candidates Map of permissionSlug => target relative URL path
     */
    public static function redirectToFirstAllowedOr403(array $candidates): void
    {
        foreach ($candidates as $permSlug => $targetPath) {
            if (self::hasPermission($permSlug)) {
                header('Location: ' . url(ltrim($targetPath, '/')));
                exit;
            }
        }
        http_response_code(403);
        include TEMPLATES_PATH . '/errors/403.php';
        exit;
    }
}
