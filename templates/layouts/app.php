<?php
/**
 * Main Application Layout
 * Sidebar + Topbar + Content area
 * Used for all authenticated pages
 */
?>
<?php include TEMPLATES_PATH . '/partials/header.php'; ?>

<body class="font-sans bg-primary-50/30 text-slate-800 antialiased">
    
    <!-- Skip to main content (Accessibility) -->
    <a href="#main-content" 
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-primary-600 focus:text-white focus:rounded-2xl focus:outline-none">
        Langsung ke konten utama
    </a>

    <?php 
        $hideSidebar = $hideSidebar ?? false;
        $customSidebar = $customSidebar ?? null;
        
        if (!$hideSidebar) {
            if ($customSidebar && file_exists($customSidebar)) {
                include $customSidebar;
            } else {
                include TEMPLATES_PATH . '/partials/sidebar.php';
            }
        }
    ?>

    <!-- Main Content Area -->
    <div class="<?= $hideSidebar ? 'w-full' : 'lg:ml-[280px]' ?> min-h-screen flex flex-col transition-all duration-300">
        
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
