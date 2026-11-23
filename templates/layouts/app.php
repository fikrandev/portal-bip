<?php
/**
 * Main Application Layout
 * Sidebar + Topbar + Content area
 * Used for all authenticated pages
 */
$hideSidebar = $hideSidebar ?? false;
$customSidebar = $customSidebar ?? null;
$isPersonalScopeLayout = class_exists('Auth') && Auth::check() && Auth::isPersonalScope();
if ($isPersonalScopeLayout) {
    $hideSidebar = false;
    $customSidebar = TEMPLATES_PATH . '/partials/sidebar_personal.php';
}
$isPortalPjax = !empty($_SERVER['HTTP_X_PORTAL_PJAX']);

if ($isPortalPjax):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle ?? SYS_APP_NAME) ?> — <?= SYS_APP_NAME ?></title>
    <?= CSRF::meta() ?>
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
<body>
    <div id="portal-sidebar-slot">
    <?php if (!$hideSidebar): ?>
        <div id="sidebar-overlay"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300 cursor-pointer"
             onclick="toggleSidebar()"
             aria-hidden="true"></div>
        <?php
        if ($customSidebar && file_exists($customSidebar)) {
            include $customSidebar;
        } else {
            include TEMPLATES_PATH . '/partials/sidebar.php';
        }
        ?>
    <?php endif; ?>
    </div>
    <div id="portal-shell-wrapper" class="<?= $hideSidebar ? 'w-full' : 'lg:ml-[280px]' ?> min-h-screen flex flex-col transition-all duration-300">
        <?php include TEMPLATES_PATH . '/partials/topbar.php'; ?>
        <main id="main-content" class="flex-1 p-4 sm:p-6 lg:p-8" role="main">
            <?php include TEMPLATES_PATH . '/partials/alert.php'; ?>
            <?= $content ?? '' ?>
        </main>
    </div>
    <div id="portal-extra-js" style="display:none;">
    <?php if (isset($extraJs)): ?>
        <?= $extraJs ?>
    <?php endif; ?>
    </div>
</body>
</html>
<?php
    return;
endif;
?>
<?php include TEMPLATES_PATH . '/partials/header.php'; ?>

<body class="font-sans bg-primary-50/30 text-slate-800 antialiased">
    
    <!-- Top Slim PJAX Progress Bar -->
    <div id="portal-pjax-bar" aria-hidden="true"></div>

    <!-- Skip to main content (Accessibility) -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-2xl focus:outline-none">
        Langsung ke konten utama
    </a>

    <div id="portal-sidebar-slot">
    <?php
        if (!$hideSidebar): ?>
            <!-- Global Mobile Sidebar Overlay -->
            <div id="sidebar-overlay" 
                 class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300 cursor-pointer"
                 onclick="toggleSidebar()"
                 aria-hidden="true"></div>
            <?php
            if ($customSidebar && file_exists($customSidebar)) {
                include $customSidebar;
            } else {
                include TEMPLATES_PATH . '/partials/sidebar.php';
            }
        endif;
    ?>
    </div>

    <!-- Main Content Area -->
    <div id="portal-shell-wrapper" class="<?= $hideSidebar ? 'w-full' : 'lg:ml-[280px]' ?> min-h-screen flex flex-col transition-all duration-300">
        
        <!-- Top Navigation Bar -->
        <?php include TEMPLATES_PATH . '/partials/topbar.php'; ?>
        
        <!-- Page Content -->
        <main id="main-content" class="flex-1 p-4 sm:p-6 lg:p-8" role="main">
            
            <!-- Flash Messages -->
            <?php include TEMPLATES_PATH . '/partials/alert.php'; ?>
            
            <!-- Page Content (injected by controllers) -->
            <?= $content ?? '' ?>
            
        </main>

        <!-- Footer -->
        <footer class="px-4 sm:px-6 lg:px-8 py-4 border-t border-primary-100/60 bg-white/50">
            <p class="text-xs text-center text-slate-400">
                &copy; <?= date('Y') ?> <?= SYS_APP_NAME ?>. Seluruh hak cipta dilindungi.
            </p>
        </footer>
    </div>

<?php include TEMPLATES_PATH . '/partials/footer.php'; ?>
