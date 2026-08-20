<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- SEO Meta -->
    <title><?= e($pageTitle ?? SYS_APP_NAME) ?> — <?= SYS_APP_NAME ?></title>
    <meta name="description" content="<?= e($pageDescription ?? APP_DESCRIPTION) ?>">
    <meta name="robots" content="noindex, nofollow">
    
    <?php if (defined('SYS_APP_FAVICON') && SYS_APP_FAVICON): ?>
    <link rel="icon" href="<?= url(ltrim(SYS_APP_FAVICON, '/')) ?>">
    <?php endif; ?>
    
    <!-- CSRF Token for AJAX -->
    <?= CSRF::meta() ?>
    
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS 4 (CDN for development) -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <!-- Tailwind Custom Theme -->
    <style type="text/tailwindcss">
        @theme {
            --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
            --color-primary-50: #eff6ff;
            --color-primary-100: #dbeafe;
            --color-primary-200: #bfdbfe;
            --color-primary-300: #93c5fd;
            --color-primary-400: #60a5fa;
            --color-primary-500: #3b82f6;
            --color-primary-600: #2563eb;
            --color-primary-700: #1d4ed8;
            --color-primary-800: #1e40af;
            --color-primary-900: #1e3a8a;
            --color-primary-950: #172554;
        }
        
        @layer base {
            /* Fix chrome autofill background and retain border radius */
            input:-webkit-autofill,
            input:-webkit-autofill:hover, 
            input:-webkit-autofill:focus, 
            input:-webkit-autofill:active{
                -webkit-box-shadow: 0 0 0 30px white inset !important;
                border-radius: 9999px !important;
            }
        }
    </style>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    
    <?php if (isset($extraCss)): ?>
        <?= $extraCss ?>
    <?php endif; ?>
</head>
