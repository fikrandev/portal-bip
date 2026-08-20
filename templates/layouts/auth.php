<?php
/**
 * Auth Layout
 * Centered card layout for login / forgot password pages
 * Full-screen gradient background with floating shapes
 */
?>
<?php include TEMPLATES_PATH . '/partials/header.php'; ?>

<body class="font-sans antialiased min-h-screen flex items-center justify-center relative overflow-hidden">
    
    <!-- Solid Blue Background with Crisp Circles -->
    <div class="absolute inset-0 bg-primary-600"></div>
    
    <!-- Decorative Crisp Circles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-[600px] h-[600px] bg-white/5 rounded-full"></div>
        <div class="absolute top-48 right-32 w-64 h-64 bg-white/5 rounded-full"></div>
        <div class="absolute bottom-12 right-64 w-[400px] h-[400px] bg-white/5 rounded-full"></div>
        <div class="absolute -bottom-48 -left-20 w-[500px] h-[500px] bg-white/5 rounded-full"></div>
        <div class="absolute top-1/4 left-16 w-80 h-80 bg-white/5 rounded-full"></div>
    </div>

    <!-- Auth Card -->
    <div class="relative z-10 w-full max-w-md mx-4">
        
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-2xl p-10 pt-12 pb-8">
            
            <!-- Logo -->
            <div class="text-center mb-8 flex flex-col items-center">
                <?php if (defined('SYS_APP_LOGO') && SYS_APP_LOGO): ?>
                <div class="w-20 h-20 flex items-center justify-center mb-4">
                    <img src="<?= url(ltrim(SYS_APP_LOGO, '/')) ?>" alt="Logo" class="max-w-full max-h-full object-contain">
                </div>
                <?php else: ?>
                <div class="w-16 h-16 rounded-full bg-slate-50 flex items-center justify-center border border-slate-200 mb-4 overflow-hidden">
                    <svg class="w-10 h-10 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.315 48.315 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z"/>
                    </svg>
                </div>
                <?php endif; ?>
                <h1 class="text-2xl font-bold text-primary-700 tracking-tight"><?= SYS_APP_NAME ?></h1>
            </div>

            <!-- Flash Messages -->
            <?php include TEMPLATES_PATH . '/partials/alert.php'; ?>
            
            <!-- Content (injected by controllers) -->
            <?= $content ?? '' ?>
            
            <!-- Footer -->
            <div class="text-center text-slate-400 text-[10px] mt-8 flex flex-col items-center gap-1 font-medium">
                <p>&copy; <?= date('Y') ?> <?= SYS_APP_NAME ?>. All rights reserved.</p>
                <p class="flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    v<?= APP_VERSION ?>
                </p>
            </div>
        </div>
    </div>

<?php include TEMPLATES_PATH . '/partials/footer.php'; ?>
