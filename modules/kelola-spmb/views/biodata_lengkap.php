<?php
/**
 * View: Formulir Pengisian Biodata Lengkap Calon Siswa (Publik / Token-based)
 * Modul SPMB - SIT Bina Insan Palu
 * 
 * Variabel dari controller:
 * @var array $pendaftar Data calon siswa dari tabel spmb_pendaftar
 * @var array $options   Daftar opsi master dari tabel settings
 * @var string $pageTitle
 */

$isSubmitted = ($pendaftar['biodata_status'] ?? '') === 'lengkap';
$token = htmlspecialchars($pendaftar['biodata_token'] ?? '');
$activeTab = (int)($_GET['tab'] ?? ($pendaftar['biodata_step'] ?? 1));
if ($activeTab < 1 || $activeTab > 5) $activeTab = 1;

$batasPengisian = !empty($options['batas_pengisian']) ? $options['batas_pengisian'] : date('Y-12-31 23:59:59');
$batasTimestamp = strtotime($batasPengisian);
$batasFormatted = date('d M Y, H:i', strtotime($batasPengisian)) . ' WITA';

// Helper untuk value lama / default
function val($arr, $key, $default = '') {
    return htmlspecialchars($arr[$key] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Formulir Biodata Lengkap') ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-deeper: #1e40af;
            --primary-light: #f0f9ff;
            --primary-border: #bae6fd;
            --slate-950: #020617;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --white: #ffffff;
            --accent-cyan: #38bdf8;
            --accent-amber: #d97706;
            --danger: #dc2626;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background: #f8fafc;
            color: var(--slate-800);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
            padding-top: 48px; /* Ruang untuk fixed countdown header */
        }

        /* ── Fixed Countdown Top Header ────────────────────────── */
        .fixed-countdown-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 48px;
            z-index: 9999;
            background: linear-gradient(90deg, #091e42 0%, #0f2b6a 50%, #0284c7 100%);
            border-bottom: 1.5px solid rgba(56, 189, 248, 0.45);
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.25);
            color: #ffffff;
            display: flex;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        .countdown-bar-inner {
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            padding: 0 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .countdown-label-group {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .countdown-pulse-icon {
            position: relative;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(56, 189, 248, 0.2);
            border: 1px solid rgba(56, 189, 248, 0.5);
            color: #38bdf8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .pulse-ring {
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            border: 2px solid #38bdf8;
            animation: pulseGlow 2s infinite ease-out;
            opacity: 0;
        }

        @keyframes pulseGlow {
            0% { transform: scale(0.9); opacity: 0.8; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .countdown-text {
            display: flex;
            align-items: baseline;
            gap: 6px;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .countdown-title {
            font-weight: 700;
            color: #ffffff;
        }

        .countdown-date-hint {
            font-size: 11px;
            color: #bae6fd;
            font-weight: 500;
        }

        .countdown-ticker {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .cd-box {
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(56, 189, 248, 0.4);
            border-radius: 6px;
            padding: 2px 6px;
            display: flex;
            align-items: baseline;
            gap: 2px;
            min-width: 38px;
            justify-content: center;
        }

        .cd-number {
            font-family: 'JetBrains Mono', 'Plus Jakarta Sans', monospace;
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
        }

        .cd-text {
            font-size: 9px;
            font-weight: 700;
            color: #38bdf8;
            text-transform: uppercase;
        }

        .cd-colon {
            color: #38bdf8;
            font-weight: 800;
            font-size: 12px;
        }

        .countdown-expired-badge {
            background: #ef4444;
            color: #ffffff;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        /* ── Top Header ────────────────────────────────────────── */
        .header-bar {
            background: linear-gradient(135deg, #091e42 0%, #0f2b6a 50%, #0284c7 100%);
            color: #ffffff;
            padding: 24px 16px 40px;
            position: relative;
            box-shadow: 0 4px 20px rgba(2, 132, 199, 0.25);
        }

        .header-bar::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 0;
            right: 0;
            height: 30px;
            background: #f8fafc;
            border-top-left-radius: 28px;
            border-top-right-radius: 28px;
        }

        .header-container {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #ffffff;
            flex-shrink: 0;
        }

        .brand-titles h1 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .brand-titles p {
            font-size: 12px;
            color: #bae6fd;
            font-weight: 500;
        }

        .reg-badge {
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        /* ── Main Container ────────────────────────────────────── */
        .main-wrapper {
            max-width: 900px;
            width: 100%;
            margin: 0 auto;
            padding: 0 16px 40px;
            position: relative;
            z-index: 10;
            flex: 1;
        }

        /* ── Student Summary Card ──────────────────────────────── */
        .summary-banner {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-lg);
            padding: 16px 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .student-meta {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .student-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary-light);
            border: 2px solid var(--primary-border);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
        }

        .student-details h2 {
            font-size: 16px;
            font-weight: 700;
            color: var(--slate-900);
        }

        .student-tags {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--slate-500);
            margin-top: 2px;
            flex-wrap: wrap;
        }

        .badge-pill {
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 11px;
        }
        .badge-pill.status-draft { background: #fef3c7; color: #b45309; }
        .badge-pill.status-lengkap { background: #e0f2fe; color: #0369a1; }
        .badge-pill.status-baru { background: #f1f5f9; color: #475569; }

        /* ── Mobile Step Progress Card (Shown on mobile) ───────── */
        .mobile-step-card {
            display: none;
            background: #ffffff;
            border: 1.5px solid #dbeafe;
            border-radius: var(--radius-md);
            padding: 12px 14px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.08);
        }

        .mobile-step-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }

        .mobile-step-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .mobile-step-name {
            font-size: 12px;
            font-weight: 800;
            color: var(--slate-800);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex: 1;
        }

        .mobile-step-arrows {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }

        .m-step-arrow {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: var(--slate-100);
            border: 1px solid var(--slate-200);
            color: var(--slate-700);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .m-step-arrow:hover:not(:disabled) {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }

        .m-step-arrow:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .mobile-step-progress-track {
            width: 100%;
            height: 6px;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .mobile-step-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #38bdf8, #0284c7);
            border-radius: 4px;
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* ── Stepper Navigation (Full Horizontal Scrollable) ───── */
        .stepper-container-wrapper {
            position: relative;
            margin-bottom: 24px;
        }

        .stepper-nav {
            display: flex;
            background: #ffffff;
            border: 1.5px solid #dbeafe;
            border-radius: var(--radius-lg);
            padding: 8px 6px;
            box-shadow: 0 2px 10px rgba(2, 132, 199, 0.08);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
            scroll-snap-type: x mandatory;
            touch-action: pan-x;
            scrollbar-width: thin;
            scrollbar-color: #38bdf8 #f1f5f9;
            gap: 8px;
        }

        .stepper-nav::-webkit-scrollbar {
            height: 6px;
            display: block;
        }

        .stepper-nav::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 10px;
        }

        .stepper-nav::-webkit-scrollbar-thumb {
            background: #bae6fd;
            border-radius: 10px;
        }

        .stepper-nav::-webkit-scrollbar-thumb:hover {
            background: #38bdf8;
        }

        .tab-btn {
            flex: 0 0 auto;
            flex-shrink: 0;
            scroll-snap-align: center;
            min-width: 155px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 14px;
            border: none;
            background: transparent;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-600);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            text-decoration: none;
        }

        .tab-btn .step-num {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--slate-100);
            color: var(--slate-600);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }

        .tab-btn.active .step-num {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        .tab-btn.completed:not(.active) .step-num {
            background: var(--primary-light);
            color: var(--primary);
        }

        /* ── Form Card ─────────────────────────────────────────── */
        .form-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-xl);
            padding: 28px 24px;
            box-shadow: var(--shadow-md);
            position: relative;
        }

        @media (min-width: 768px) {
            .form-card { padding: 36px 32px; }
        }

        .tab-section-header {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--slate-100);
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .tab-section-title h3 {
            font-size: 18px;
            font-weight: 800;
            color: var(--slate-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tab-section-title p {
            font-size: 13px;
            color: var(--slate-500);
            margin-top: 4px;
        }

        /* ── Grid System ───────────────────────────────────────── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px 20px;
        }

        @media (min-width: 640px) {
            .form-grid.grid-2 { grid-template-columns: repeat(2, 1fr); }
            .form-grid.grid-3 { grid-template-columns: repeat(3, 1fr); }
            .form-grid.grid-4 { grid-template-columns: repeat(4, 1fr); }
            .col-span-2 { grid-column: span 2; }
            .col-span-3 { grid-column: span 3; }
            .col-span-4 { grid-column: span 4; }
        }

        /* ── Form Controls ─────────────────────────────────────── */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-700);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-label .req {
            color: var(--danger);
            font-weight: 700;
        }

        .form-label .hint {
            font-size: 11px;
            font-weight: 400;
            color: var(--slate-400);
            margin-left: auto;
        }

        .form-control, .form-select {
            width: 100%;
            height: 44px;
            padding: 8px 14px;
            border: 1.5px solid var(--slate-200);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--slate-800);
            background-color: #ffffff;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }

        .form-control:read-only, .form-control:disabled {
            background-color: var(--slate-50);
            color: var(--slate-500);
            cursor: not-allowed;
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-group .form-control {
            padding-right: 48px;
        }

        .input-group-addon {
            position: absolute;
            right: 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-400);
            pointer-events: none;
        }

        /* ── Tab 3: GPS Geolocation Box ────────────────────────── */
        .gps-banner {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1.5px dashed #93c5fd;
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .gps-desc {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            max-width: 540px;
        }

        .gps-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #2563eb;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .gps-desc h4 {
            font-size: 14px;
            font-weight: 700;
            color: #1e3a8a;
        }

        .gps-desc p {
            font-size: 12px;
            color: #3b82f6;
            margin-top: 2px;
            line-height: 1.4;
        }

        .btn-gps {
            padding: 10px 18px;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25);
            transition: all 0.2s;
            white-space: nowrap;
        }

        .btn-gps:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* ── File Upload Dropzones ─────────────────────────────── */
        .upload-card {
            border: 2px dashed var(--slate-200);
            border-radius: var(--radius-lg);
            padding: 20px;
            text-align: center;
            background: var(--slate-50);
            transition: all 0.2s ease;
            position: relative;
        }

        .upload-card:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .upload-icon {
            font-size: 28px;
            color: var(--slate-400);
            margin-bottom: 8px;
        }

        .upload-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-800);
        }

        .upload-subtitle {
            font-size: 11px;
            color: var(--slate-500);
            margin-top: 2px;
            margin-bottom: 12px;
        }

        .upload-input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-preview-box {
            display: none;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed var(--slate-200);
            text-align: left;
        }

        .preview-img {
            max-height: 120px;
            border-radius: 8px;
            border: 1px solid var(--slate-200);
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }

        .file-info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary-dark);
            background: var(--primary-light);
            padding: 4px 10px;
            border-radius: 6px;
            margin-top: 8px;
        }

        /* ── Tab 5: Privacy & Terms Scroll Box ─────────────────── */
        .privacy-box-container {
            background: var(--slate-50);
            border: 1.5px solid var(--slate-200);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 24px;
        }

        .privacy-scroll-area {
            max-height: 220px;
            overflow-y: auto;
            padding: 16px;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-md);
            font-size: 13px;
            line-height: 1.6;
            color: var(--slate-700);
            margin-bottom: 16px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03);
        }

        .privacy-scroll-area h4 {
            font-size: 14px;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 8px;
        }

        .privacy-scroll-area ol, .privacy-scroll-area ul {
            padding-left: 20px;
            margin-bottom: 12px;
        }

        .privacy-scroll-area li {
            margin-bottom: 6px;
        }

        .scroll-indicator {
            font-size: 12px;
            font-weight: 600;
            color: var(--accent-amber);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .scroll-indicator.completed {
            color: var(--primary);
        }

        .checkbox-container {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 18px;
            background: #ffffff;
            border: 1.5px solid var(--slate-200);
            border-radius: var(--radius-md);
            cursor: pointer;
            user-select: none;
            transition: all 0.25s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .checkbox-container:hover {
            border-color: var(--primary);
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .checkbox-container.agreed {
            background: #f0fdf4;
            border-color: #86efac;
            box-shadow: 0 2px 8px rgba(22, 163, 74, 0.08);
        }

        .checkbox-container input[type="checkbox"] {
            width: 22px;
            height: 22px;
            accent-color: #16a34a;
            margin-top: 2px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .checkbox-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-800);
            line-height: 1.5;
        }

        /* ── Modal Persetujuan Keamanan Data ─────────────────── */
        .privacy-modal-card {
            background: #ffffff;
            border-radius: var(--radius-xl);
            max-width: 600px;
            width: 100%;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-xl);
            text-align: left;
            animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            margin: auto;
        }

        .privacy-modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .privacy-modal-body {
            padding: 20px 22px;
            overflow-y: auto;
            font-size: 13px;
            line-height: 1.6;
            color: var(--slate-700);
            flex: 1;
        }

        .privacy-modal-footer {
            padding: 14px 22px;
            border-top: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            background: #f8fafc;
        }

        /* ── Modal Konfirmasi Data Final ─────────────────────── */
        .confirm-modal-card {
            background: #ffffff;
            border-radius: var(--radius-xl);
            max-width: 680px;
            width: 100%;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            text-align: left;
            animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            margin: auto;
        }

        .confirm-modal-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
        }

        .confirm-modal-body {
            padding: 20px 22px;
            overflow-y: auto;
            font-size: 13px;
            color: var(--slate-700);
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .confirm-modal-footer {
            padding: 14px 22px;
            border-top: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #f8fafc;
        }

        .confirm-section-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-md);
            padding: 14px 16px;
            transition: all 0.2s ease;
        }

        .confirm-section-card:hover {
            border-color: var(--primary-border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .confirm-section-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px dashed var(--slate-200);
            padding-bottom: 6px;
        }

        .confirm-section-title i {
            color: var(--primary);
            margin-right: 6px;
        }

        .confirm-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 14px;
        }

        @media (max-width: 600px) {
            .confirm-grid {
                grid-template-columns: 1fr;
            }
            .confirm-modal-footer {
                flex-direction: column-reverse;
            }
            .confirm-modal-footer .btn-action {
                width: 100%;
            }
        }

        .confirm-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .confirm-item.full-width {
            grid-column: 1 / -1;
        }

        .confirm-label {
            font-size: 10.5px;
            font-weight: 600;
            color: var(--slate-400);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .confirm-val {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--slate-800);
            word-break: break-word;
            line-height: 1.4;
        }

        .confirm-val-highlight {
            color: var(--primary-dark);
            font-size: 14px;
            font-weight: 800;
        }

        .file-status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 10px;
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 8px;
            font-size: 12px;
            gap: 10px;
        }

        .file-status-name {
            font-weight: 700;
            color: var(--slate-800);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .file-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .file-badge-pill.success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .file-badge-pill.warning {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        /* ── Modal Data Belum Lengkap ─────────────────────────── */
        .incomplete-modal-card {
            background: #ffffff;
            border-radius: var(--radius-xl);
            max-width: 600px;
            width: 100%;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            text-align: left;
            animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            margin: auto;
        }

        .incomplete-tab-box {
            background: #ffffff;
            border: 1px solid #fed7aa;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .incomplete-tab-title {
            font-size: 13px;
            font-weight: 800;
            color: #9a3412;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #fed7aa;
        }

        .incomplete-items-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .incomplete-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--slate-700);
            background: #fff7ed;
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ffedd5;
            gap: 8px;
        }

        .btn-jump-field {
            padding: 3px 8px;
            border-radius: 6px;
            background: #ea580c;
            color: #ffffff;
            border: none;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            transition: all 0.2s;
            flex-shrink: 0;
        }

        .btn-jump-field:hover {
            background: #c2410c;
        }

        .field-missing-highlight {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
            box-shadow: 0 0 0 3.5px rgba(239, 68, 68, 0.25) !important;
            animation: shakeInput 0.4s ease-in-out;
        }

        @keyframes shakeInput {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        /* ── Bottom Action Bar ─────────────────────────────────── */
        .action-bar {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid var(--slate-100);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-prev {
            background: var(--slate-100);
            color: var(--slate-700);
        }
        .btn-prev:hover { background: var(--slate-200); }

        .btn-draft {
            background: #ffffff;
            border: 1.5px solid var(--slate-200);
            color: var(--slate-700);
        }
        .btn-draft:hover {
            border-color: var(--slate-400);
            background: var(--slate-50);
        }

        .btn-next {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.25);
        }
        .btn-next:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-submit {
            background: linear-gradient(135deg, #0284c7 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        }
        .btn-submit:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.45);
        }
        .btn-submit:disabled {
            background: var(--slate-300);
            box-shadow: none;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* ── Modal Popups ──────────────────────────────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 16px;
        }

        .modal-overlay.show { display: flex; }

        .modal-card {
            background: #ffffff;
            border-radius: var(--radius-xl);
            max-width: 480px;
            width: 100%;
            padding: 28px;
            box-shadow: var(--shadow-xl);
            text-align: center;
            animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.92); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-icon-badge {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        .modal-icon-badge.warning { background: #fef3c7; color: #d97706; }
        .modal-icon-badge.success { background: #dcfce7; color: #15803d; }
        .modal-icon-badge.info { background: #e0f2fe; color: #0284c7; }

        @keyframes gpsPulseRadar {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.45); }
            70% { transform: scale(1.06); box-shadow: 0 0 0 16px rgba(2, 132, 199, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
        }
        .gps-pulse-anim {
            animation: gpsPulseRadar 1.8s infinite;
        }

        .gps-highlight-field {
            animation: gpsHighlightGlow 2.5s ease-out;
        }
        @keyframes gpsHighlightGlow {
            0% { border-color: #0284c7 !important; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.35) !important; background-color: #f0f9ff !important; }
            50% { border-color: #0284c7 !important; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.2) !important; background-color: #f0f9ff !important; }
            100% { border-color: var(--slate-200); box-shadow: none; background-color: #ffffff; }
        }

        .modal-card h3 {
            font-size: 18px;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 8px;
        }

        .modal-card p {
            font-size: 13px;
            color: var(--slate-600);
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .modal-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        /* ── Toast Notification ────────────────────────────────── */
        .toast-notif {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: var(--slate-900);
            color: #ffffff;
            padding: 12px 20px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: var(--shadow-xl);
            z-index: 2000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            max-width: 90vw;
        }

        .toast-notif.show {
            transform: translateY(0);
            opacity: 1;
        }

        /* ── Status Selesai Banner ─────────────────────────────── */
        .submitted-banner {
            background: #f0f9ff;
            border: 1.5px solid #bae6fd;
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .submitted-banner .icon {
            font-size: 32px;
            color: #0284c7;
        }

        .submitted-banner h4 {
            font-size: 16px;
            font-weight: 800;
            color: #0369a1;
        }

        .submitted-banner p {
            font-size: 13px;
            color: #075985;
            margin-top: 2px;
        }

        /* ── Parent Section Headers ────────────────────────────── */
        .subheading-divider {
            grid-column: 1 / -1;
            padding-bottom: 8px;
            margin-top: 12px;
            border-bottom: 2px solid var(--slate-100);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 800;
            color: var(--slate-800);
        }

        .subheading-divider i { color: var(--primary); }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .mobile-step-card { display: block; }
        }

        @media (max-width: 640px) {
            .countdown-date-hint { display: none; }
            .countdown-title { font-size: 11px; }
            .countdown-ticker .cd-box { padding: 2px 4px; min-width: 22px; }
            .countdown-ticker .cd-number { font-size: 11px; }
            .countdown-ticker .cd-text { font-size: 8px; }
            .countdown-ticker .cd-colon { font-size: 10px; margin: 0 1px; }
            .fixed-countdown-bar { height: 44px; }
            body { padding-top: 44px; }
            .header-bar { padding: 18px 14px 34px; }
            .brand-titles h1 { font-size: 16px; }
            .summary-banner { flex-direction: column; align-items: flex-start; }
            .stepper-nav { border-radius: var(--radius-md); padding: 6px 4px; }
            .tab-btn { min-width: 138px; font-size: 12px; padding: 9px 10px; gap: 8px; }
            .action-bar { flex-direction: column; }
            .btn-action { width: 100%; }
        }
    </style>
</head>
<body>

    <!-- ── Fixed Top Countdown Bar ───────────────────────────────── -->
    <aside class="fixed-countdown-bar" id="fixedCountdownBar" aria-label="Batas Waktu Pengisian Formulir">
        <div class="countdown-bar-inner">
            <div class="countdown-label-group">
                <span class="countdown-pulse-icon">
                    <span class="pulse-ring"></span>
                    <i class="fa-solid fa-hourglass-half"></i>
                </span>
                <div class="countdown-text">
                    <span class="countdown-title">Batas Pengisian Formulir:</span>
                    <span class="countdown-date-hint" title="<?= htmlspecialchars($batasFormatted) ?>">
                        s.d. <?= htmlspecialchars($batasFormatted) ?>
                    </span>
                </div>
            </div>

            <div class="countdown-ticker" id="countdownTicker">
                <div class="cd-box">
                    <span class="cd-number" id="cdDays">00</span>
                    <span class="cd-text">Hari</span>
                </div>
                <span class="cd-colon">:</span>
                <div class="cd-box">
                    <span class="cd-number" id="cdHours">00</span>
                    <span class="cd-text">Jam</span>
                </div>
                <span class="cd-colon">:</span>
                <div class="cd-box">
                    <span class="cd-number" id="cdMinutes">00</span>
                    <span class="cd-text">Mnt</span>
                </div>
                <span class="cd-colon">:</span>
                <div class="cd-box">
                    <span class="cd-number" id="cdSeconds">00</span>
                    <span class="cd-text">Dtk</span>
                </div>
            </div>

            <div class="countdown-expired-badge" id="countdownExpiredBadge" style="display:none;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Waktu Pengisian Berakhir</span>
            </div>
        </div>
    </aside>

    <!-- Header Bar -->
    <header class="header-bar">
        <div class="header-container">
            <div class="brand-info">
                <div class="brand-logo-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="brand-titles">
                    <h1>SPMB SIT BINA INSAN PALU</h1>
                    <p>Formulir Pengisian Biodata Lengkap Calon Siswa</p>
                </div>
            </div>
            <div class="reg-badge">
                <i class="fa-solid fa-id-card-clip"></i>
                <span><?= val($pendaftar, 'nomor_pendaftaran') ?></span>
            </div>
        </div>
    </header>

    <!-- Main Wrapper -->
    <main class="main-wrapper">

        <!-- Student Summary Banner -->
        <div class="summary-banner">
            <div class="student-meta">
                <div class="student-avatar">
                    <?= mb_strtoupper(mb_substr($pendaftar['nama_lengkap'] ?? 'C', 0, 1)) ?>
                </div>
                <div class="student-details">
                    <h2><?= val($pendaftar, 'nama_lengkap') ?></h2>
                    <div class="student-tags">
                        <span><i class="fa-solid fa-school"></i> <?= val($pendaftar, 'jenjang') ?> <?= !empty($pendaftar['program']) ? '(' . val($pendaftar, 'program') . ')' : '' ?></span>
                        <span>•</span>
                        <span><i class="fa-solid fa-user-tag"></i> <?= val($pendaftar, 'jenis_pendaftar', 'Siswa Baru') ?></span>
                    </div>
                </div>
            </div>
            <div>
                <?php if ($isSubmitted): ?>
                    <span class="badge-pill status-lengkap"><i class="fa-solid fa-circle-check"></i> Biodata Lengkap & Terverifikasi</span>
                <?php elseif (($pendaftar['biodata_status'] ?? '') === 'draft'): ?>
                    <span class="badge-pill status-draft"><i class="fa-solid fa-clock-rotate-left"></i> Draft Tersimpan (Bisa Dilanjutkan)</span>
                <?php else: ?>
                    <span class="badge-pill status-baru"><i class="fa-solid fa-pen-to-square"></i> Belum Selesai Diisi</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isSubmitted && !empty($_GET['status']) && $_GET['status'] === 'selesai'): ?>
            <!-- Success Flash Alert -->
            <div class="submitted-banner">
                <i class="fa-solid fa-circle-check icon"></i>
                <div>
                    <h4>Alhamdulillah! Pengisian Biodata Lengkap Berhasil Dikirim</h4>
                    <p>Terima kasih, Bapak/Ibu. Data dan berkas pendukung ananda telah kami terima secara aman di sistem SPMB SIT Bina Insan Palu. Anda dapat melihat kembali isian di bawah ini.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ── Penanda Wajib: Surat Pernyataan Komitmen Orang Tua ── -->
        <?php
        $statusTtdKomitmen = $pendaftar['status_ttd_komitmen'] ?? 'Belum TTD';
        $tokenKomitmen = $pendaftar['biodata_token'] ?? '';
        $linkKomitmen = url('spmb/komitmen/' . $tokenKomitmen);
        ?>
        <?php if ($statusTtdKomitmen === 'Sudah TTD'): ?>
            <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1.5px solid #6ee7b7; border-radius: var(--radius-lg); padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #059669; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 15px; font-weight: 800; color: #065f46;">Surat Pernyataan Komitmen Telah Ditandatangani</h4>
                        <p style="font-size: 12px; color: #047857; margin-top: 2px;">
                            Tanda tangan Ayah & Ibu ananda telah terekam secara sah & terenkripsi (AES-256-CBC) pada <?= !empty($pendaftar['ttd_submitted_at']) ? date('d/m/Y H:i', strtotime($pendaftar['ttd_submitted_at'])) . ' WITA' : '-' ?>.
                        </p>
                    </div>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="<?= $linkKomitmen ?>" target="_blank" style="padding: 10px 18px; background: #059669; color: #ffffff; border-radius: 10px; font-weight: 700; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>Lihat Dokumen</span>
                    </a>
                    <a href="<?= $linkKomitmen ?>/cetak" target="_blank" style="padding: 10px 16px; background: #ffffff; color: #065f46; border: 1.5px solid #6ee7b7; border-radius: 10px; font-weight: 700; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak PDF</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border: 2px solid #fda4af; border-radius: var(--radius-lg); padding: 20px 22px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: 0 4px 14px rgba(225, 29, 72, 0.1);">
                <div style="display: flex; align-items: flex-start; gap: 14px; max-width: 620px;">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #e11d48; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 10px rgba(225, 29, 72, 0.3);">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="background: #be123c; color: #ffffff; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Wajib Dilengkapi</span>
                            <h4 style="font-size: 15px; font-weight: 800; color: #9f1239;">Tanda Tangani Surat Pernyataan Komitmen Orang Tua</h4>
                        </div>
                        <p style="font-size: 12.5px; color: #be123c; margin-top: 4px; line-height: 1.5;">
                            Sebagai syarat wajib pendaftaran SPMB SIT Bina Insan Palu, Bapak/Ibu wajib menandatangani Surat Pernyataan Komitmen secara digital (di layar HP / laptop).
                        </p>
                    </div>
                </div>
                <div>
                    <a href="<?= $linkKomitmen ?>" style="padding: 12px 22px; background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); color: #ffffff; border-radius: 12px; font-weight: 800; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);">
                        <i class="fa-solid fa-pen-nib"></i>
                        <span>Tanda Tangani Sekarang &rarr;</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Mobile Step Progress Indicator -->
        <div class="mobile-step-card" id="mobileStepCard">
            <div class="mobile-step-header">
                <div class="mobile-step-badge">
                    <span>Tahap <strong id="mStepNum"><?= $activeTab ?></strong>/5</span>
                </div>
                <span class="mobile-step-name" id="mStepName">Data Calon Siswa</span>
                <div class="mobile-step-arrows">
                    <button type="button" class="m-step-arrow" id="mPrevBtn" onclick="prevTabMobile()" title="Sebelumnya">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="m-step-arrow" id="mNextBtn" onclick="nextTabMobile()" title="Selanjutnya">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="mobile-step-progress-track">
                <div class="mobile-step-progress-bar" id="mProgressBar" style="width: <?= $activeTab * 20 ?>%;"></div>
            </div>
        </div>

        <!-- Stepper Tab Navigation Wrapper (Responsive & Touch Scrollable) -->
        <div class="stepper-container-wrapper">
            <nav class="stepper-nav" aria-label="Tahapan Biodata">
                <button type="button" class="tab-btn <?= $activeTab === 1 ? 'active' : '' ?>" onclick="switchTab(1)">
                    <span class="step-num">1</span>
                    <span>Data Calon Siswa</span>
                </button>
                <button type="button" class="tab-btn <?= $activeTab === 2 ? 'active' : '' ?>" onclick="switchTab(2)">
                    <span class="step-num">2</span>
                    <span>Sekolah Sebelumnya</span>
                </button>
                <button type="button" class="tab-btn <?= $activeTab === 3 ? 'active' : '' ?>" onclick="switchTab(3)">
                    <span class="step-num">3</span>
                    <span>Tempat Tinggal</span>
                </button>
                <button type="button" class="tab-btn <?= $activeTab === 4 ? 'active' : '' ?>" onclick="switchTab(4)">
                    <span class="step-num">4</span>
                    <span>Data Orang Tua</span>
                </button>
                <button type="button" class="tab-btn <?= $activeTab === 5 ? 'active' : '' ?>" onclick="switchTab(5)">
                    <span class="step-num">5</span>
                    <span>Berkas & Keamanan</span>
                </button>
            </nav>
        </div>

        <!-- Main Form -->
        <form id="biodataForm" enctype="multipart/form-data" method="POST" action="<?= htmlspecialchars(url('spmb/biodata/' . $token . '/submit')) ?>">
            <input type="hidden" name="biodata_step" id="biodataStepInput" value="<?= $activeTab ?>">

            <!-- ══════════════════════════════════════════════════════════════════
                 TAB 1: DATA DIRI CALON SISWA
                 ══════════════════════════════════════════════════════════════════ -->
            <div class="form-card tab-content" id="tabContent1" style="<?= $activeTab === 1 ? '' : 'display:none;' ?>">
                <div class="tab-section-header">
                    <div class="tab-section-title">
                        <h3><i class="fa-solid fa-child" style="color:var(--primary);"></i> Bagian 1: Data Diri Calon Siswa</h3>
                        <p>Pastikan identitas anak diisi sesuai dengan data resmi pada Akta Kelahiran dan Kartu Keluarga.</p>
                    </div>
                </div>

                <div class="form-grid grid-2">
                    <!-- 1. Nama Lengkap -->
                    <div class="form-group col-span-2">
                        <label class="form-label">1. Nama Lengkap Calon Siswa <span class="req">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= val($pendaftar, 'nama_lengkap') ?>" required placeholder="Sesuai Akta Kelahiran">
                    </div>

                    <!-- 2. Jenis Kelamin -->
                    <div class="form-group">
                        <label class="form-label">2. Jenis Kelamin <span class="req">*</span></label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="L" <?= ($pendaftar['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="P" <?= ($pendaftar['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>

                    <!-- 3. NIK / No KITAS -->
                    <div class="form-group">
                        <label class="form-label">3. NIK / No. KITAS <span class="req">*</span> <span class="hint">16 digit angka</span></label>
                        <input type="text" name="nik" id="nikInput" class="form-control" value="<?= val($pendaftar, 'nik') ?>" maxlength="16" required placeholder="Contoh: 7271012304160001">
                    </div>

                    <!-- 4. No KK -->
                    <div class="form-group">
                        <label class="form-label">4. No. Kartu Keluarga (KK) <span class="req">*</span> <span class="hint">16 digit angka</span></label>
                        <input type="text" name="no_kk" class="form-control" value="<?= val($pendaftar, 'no_kk') ?>" maxlength="16" required placeholder="Lihat bagian atas Kartu Keluarga">
                    </div>

                    <!-- 5. Tempat Lahir -->
                    <div class="form-group">
                        <label class="form-label">5. Tempat Lahir <span class="req">*</span></label>
                        <input type="text" name="tempat_lahir" class="form-control" value="<?= val($pendaftar, 'tempat_lahir') ?>" required placeholder="Contoh: Palu">
                    </div>

                    <!-- 6. Tanggal Lahir -->
                    <div class="form-group">
                        <label class="form-label">6. Tanggal Lahir <span class="req">*</span></label>
                        <input type="date" name="tanggal_lahir" id="tglLahirInput" class="form-control" value="<?= val($pendaftar, 'tanggal_lahir') ?>" required onchange="hitungUmur()">
                    </div>

                    <!-- 7. Umur (Auto Terhitung) -->
                    <div class="form-group">
                        <label class="form-label">7. Umur <span class="hint">Auto terhitung per <?= !empty($pendaftar['tanggal_patokan_umur']) ? SpmbModel::formatTglIndo($pendaftar['tanggal_patokan_umur']) : '1 Juli 2027' ?></span></label>
                        <input type="text" id="umurAuto" class="form-control" readonly placeholder="Akan terhitung otomatis...">
                    </div>

                    <!-- 8. Registrasi Akta Kelahiran -->
                    <div class="form-group">
                        <label class="form-label">8. No. Registrasi Akta Kelahiran <span class="req">*</span></label>
                        <input type="text" name="no_akta_lahir" class="form-control" value="<?= val($pendaftar, 'no_akta_lahir') ?>" placeholder="Contoh: 7271-LU-12042018-0005">
                    </div>

                    <!-- 9. Anak ke berapa -->
                    <div class="form-group">
                        <label class="form-label">9. Anak ke-berapa <span class="req">*</span></label>
                        <div class="input-group">
                            <input type="number" name="anak_ke" class="form-control" value="<?= val($pendaftar, 'anak_ke', 1) ?>" min="1" max="20" required>
                            <span class="input-group-addon">dari bersaudara</span>
                        </div>
                    </div>

                    <!-- 10. Alergi (Dropdown Master) -->
                    <div class="form-group">
                        <label class="form-label">10. Riwayat Alergi</label>
                        <select name="alergi" class="form-select">
                            <option value="">-- Pilih Riwayat Alergi --</option>
                            <?php foreach ($options['alergi'] ?? [] as $optAlergi): ?>
                                <option value="<?= htmlspecialchars($optAlergi) ?>" <?= ($pendaftar['alergi'] ?? '') === $optAlergi ? 'selected' : '' ?>><?= htmlspecialchars($optAlergi) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 11. Tinggi Badan -->
                    <div class="form-group">
                        <label class="form-label">11. Tinggi Badan</label>
                        <div class="input-group">
                            <input type="number" name="tinggi_badan" class="form-control" value="<?= val($pendaftar, 'tinggi_badan') ?>" min="30" max="220" placeholder="Contoh: 115">
                            <span class="input-group-addon">cm</span>
                        </div>
                    </div>

                    <!-- 12. Berat Badan -->
                    <div class="form-group">
                        <label class="form-label">12. Berat Badan</label>
                        <div class="input-group">
                            <input type="number" name="berat_badan" class="form-control" value="<?= val($pendaftar, 'berat_badan') ?>" min="5" max="150" placeholder="Contoh: 21">
                            <span class="input-group-addon">kg</span>
                        </div>
                    </div>

                    <!-- 13. Lingkar Kepala -->
                    <div class="form-group">
                        <label class="form-label">13. Lingkar Kepala</label>
                        <div class="input-group">
                            <input type="number" name="lingkar_kepala" class="form-control" value="<?= val($pendaftar, 'lingkar_kepala') ?>" min="20" max="70" placeholder="Contoh: 51">
                            <span class="input-group-addon">cm</span>
                        </div>
                    </div>

                    <!-- 14. Hobi -->
                    <div class="form-group">
                        <label class="form-label">14. Hobi / Minat</label>
                        <input type="text" name="hobi" class="form-control" value="<?= val($pendaftar, 'hobi') ?>" placeholder="Contoh: Menggambar, Berenang, Membaca Al-Qur'an">
                    </div>

                    <!-- 15. Cita-Cita -->
                    <div class="form-group">
                        <label class="form-label">15. Cita-Cita</label>
                        <input type="text" name="cita_cita" class="form-control" value="<?= val($pendaftar, 'cita_cita') ?>" placeholder="Contoh: Dokter, Arsitek, Hafidz Qur'an, Guru">
                    </div>
                </div>

                <!-- Action Bar Tab 1 -->
                <div class="action-bar">
                    <div></div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" class="btn-action btn-draft" onclick="simpanDraftTab(1)">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Draft
                        </button>
                        <button type="button" class="btn-action btn-next" onclick="nextTab(2)">
                            Lanjut: Sekolah Sebelumnya <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 TAB 2: DATA SEKOLAH SEBELUMNYA
                 ══════════════════════════════════════════════════════════════════ -->
            <div class="form-card tab-content" id="tabContent2" style="<?= $activeTab === 2 ? '' : 'display:none;' ?>">
                <div class="tab-section-header">
                    <div class="tab-section-title">
                        <h3><i class="fa-solid fa-school" style="color:var(--primary);"></i> Bagian 2: Data Sekolah Sebelumnya</h3>
                        <p>Diisi apabila anak berasal dari PAUD/TK/SD sebelumnya (atau untuk siswa pindahan).</p>
                    </div>
                </div>

                <div class="form-grid grid-2">
                    <!-- 1. Nama Sekolah Asal -->
                    <div class="form-group col-span-2">
                        <label class="form-label">1. Nama Sekolah Asal</label>
                        <input type="text" name="sekolah_asal" class="form-control" value="<?= val($pendaftar, 'sekolah_asal') ?>" placeholder="Contoh: TK Islam Terpadu Bina Insan Palu / Kosongkan jika belum sekolah">
                    </div>

                    <!-- 2. Alamat Sekolah Asal -->
                    <div class="form-group col-span-2">
                        <label class="form-label">2. Alamat Sekolah Asal</label>
                        <textarea name="alamat_sekolah_asal" class="form-control" style="height:76px; resize:vertical;" placeholder="Jl. ... Kota ..."><?= val($pendaftar, 'alamat_sekolah_asal') ?></textarea>
                    </div>

                    <!-- 3. NISN -->
                    <div class="form-group">
                        <label class="form-label">3. NISN (Nomor Induk Siswa Nasional) <span class="hint">10 digit angka jika ada</span></label>
                        <input type="text" name="nisn" class="form-control" value="<?= val($pendaftar, 'nisn') ?>" maxlength="10" placeholder="Contoh: 3167891234">
                    </div>

                    <!-- 4. Screenshot Dapodik -->
                    <div class="form-group col-span-2">
                        <label class="form-label">
                            4. Screenshot Profil Dapodik / EMIS
                            <span class="hint">Format JPG/PNG/PDF (Maks. 500 KB)</span>
                        </label>
                        
                        <div style="margin-bottom:12px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                            <a href="<?= htmlspecialchars(url('spmb/biodata/download-contoh-dapodik')) ?>" target="_blank" class="btn-action" style="padding:7px 14px; font-size:12px; background:#e0f2fe; color:#0369a1; border-radius:8px;">
                                <i class="fa-solid fa-file-image"></i> Unduh / Lihat Contoh Screenshot Dapodik
                            </a>
                            <span style="font-size:12px; color:var(--slate-500);"><i class="fa-solid fa-circle-info"></i> Bisa diminta ke operator sekolah asal jika ada.</span>
                        </div>

                        <div class="upload-card">
                            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                            <div class="upload-title">Klik atau Seret Screenshot Profil Dapodik ke Sini</div>
                            <div class="upload-subtitle">Foto otomatis dikompres maksimal 500 KB</div>
                            <input type="file" name="file_screenshot_dapodik" class="upload-input" accept="image/*,.pdf" onchange="previewUpload(this, 'previewDapodik')">
                            
                            <div id="previewDapodik" class="upload-preview-box" <?= !empty($pendaftar['file_screenshot_dapodik']) ? 'style="display:block;"' : '' ?>>
                                <?php if (!empty($pendaftar['file_screenshot_dapodik'])): ?>
                                    <div class="file-info-badge">
                                        <i class="fa-solid fa-file-check"></i> File sudah tersimpan: <?= htmlspecialchars(basename($pendaftar['file_screenshot_dapodik'])) ?>
                                    </div>
                                    <div style="margin-top:6px;">
                                        <a href="<?= htmlspecialchars(asset($pendaftar['file_screenshot_dapodik'])) ?>" target="_blank" style="font-size:12px; color:var(--primary); font-weight:600;">Lihat File Tersimpan</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar Tab 2 -->
                <div class="action-bar">
                    <button type="button" class="btn-action btn-prev" onclick="switchTab(1)">
                        <i class="fa-solid fa-arrow-left"></i> Kembali: Data Siswa
                    </button>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" class="btn-action btn-draft" onclick="simpanDraftTab(2)">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Draft
                        </button>
                        <button type="button" class="btn-action btn-next" onclick="nextTab(3)">
                            Lanjut: Tempat Tinggal <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 TAB 3: TEMPAT TINGGAL & ALAMAT
                 ══════════════════════════════════════════════════════════════════ -->
            <div class="form-card tab-content" id="tabContent3" style="<?= $activeTab === 3 ? '' : 'display:none;' ?>">
                <div class="tab-section-header">
                    <div class="tab-section-title">
                        <h3><i class="fa-solid fa-house-user" style="color:var(--primary);"></i> Bagian 3: Tempat Tinggal & Koordinat Rumah</h3>
                        <p>Titik koordinat dan alamat lengkap digunakan untuk pemetaan zonasi dan data EMIS/Dapodik.</p>
                    </div>
                </div>

                <!-- GPS Banner Button -->
                <div class="gps-banner">
                    <div class="gps-desc">
                        <div class="gps-icon">
                            <i class="fa-solid fa-location-crosshairs"></i>
                        </div>
                        <div>
                            <h4>Deteksi Alamat & Titik Koordinat Rumah via GPS</h4>
                            <p>Klik tombol untuk mendeteksi koordinat lintang & bujur serta auto-fill kelurahan, kecamatan, dan kota secara akurat.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-gps" onclick="openGpsModal()">
                        <i class="fa-solid fa-location-dot"></i> Cek Alamat
                    </button>
                </div>

                <div class="form-grid grid-2">
                    <!-- RT & RW -->
                    <div class="form-group">
                        <label class="form-label">RT <span class="hint">Contoh: 002</span></label>
                        <input type="text" name="rt" class="form-control" value="<?= val($pendaftar, 'rt') ?>" placeholder="000" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">RW <span class="hint">Contoh: 004</span></label>
                        <input type="text" name="rw" class="form-control" value="<?= val($pendaftar, 'rw') ?>" placeholder="000" maxlength="5">
                    </div>

                    <!-- Dusun / Lingkungan -->
                    <div class="form-group col-span-2">
                        <label class="form-label">Nama Dusun / Kompleks / Lingkungan</label>
                        <input type="text" name="dusun" id="inputDusun" class="form-control" value="<?= val($pendaftar, 'dusun') ?>" placeholder="Contoh: Perumahan Dosen UNTAD / BTN Bumi Rovy">
                    </div>

                    <!-- Alamat Jalan Lengkap -->
                    <div class="form-group col-span-2">
                        <label class="form-label">Alamat Jalan Lengkap <span class="req">*</span></label>
                        <textarea name="alamat" id="inputAlamat" class="form-control" style="height:76px; resize:vertical;" required placeholder="Nama jalan, nomor rumah, gang..."><?= val($pendaftar, 'alamat') ?></textarea>
                    </div>

                    <!-- Kelurahan (Auto Terisi) -->
                    <div class="form-group">
                        <label class="form-label">Kelurahan / Desa <span class="req">*</span> <span class="hint">Auto terisi via GPS</span></label>
                        <input type="text" name="kelurahan" id="inputKelurahan" class="form-control" value="<?= val($pendaftar, 'kelurahan') ?>" required placeholder="Contoh: Tondo">
                    </div>

                    <!-- Kecamatan (Auto Terisi) -->
                    <div class="form-group">
                        <label class="form-label">Kecamatan <span class="req">*</span> <span class="hint">Auto terisi via GPS</span></label>
                        <input type="text" name="kecamatan" id="inputKecamatan" class="form-control" value="<?= val($pendaftar, 'kecamatan') ?>" required placeholder="Contoh: Mantikulore">
                    </div>

                    <!-- Kab/Kota (Auto Terisi) -->
                    <div class="form-group">
                        <label class="form-label">Kabupaten / Kota <span class="req">*</span> <span class="hint">Auto terisi via GPS</span></label>
                        <input type="text" name="kabupaten" id="inputKabupaten" class="form-control" value="<?= val($pendaftar, 'kabupaten', 'Kota Palu') ?>" required placeholder="Contoh: Kota Palu">
                    </div>

                    <!-- Provinsi (Auto Terisi) -->
                    <div class="form-group">
                        <label class="form-label">Provinsi <span class="req">*</span> <span class="hint">Auto terisi via GPS</span></label>
                        <input type="text" name="provinsi" id="inputProvinsi" class="form-control" value="<?= val($pendaftar, 'provinsi', 'Sulawesi Tengah') ?>" required placeholder="Contoh: Sulawesi Tengah">
                    </div>

                    <!-- Kode Pos (Auto Terisi) -->
                    <div class="form-group">
                        <label class="form-label">Kode Pos <span class="hint">Auto terisi</span></label>
                        <input type="text" name="kode_pos" id="inputKodePos" class="form-control" value="<?= val($pendaftar, 'kode_pos') ?>" placeholder="Contoh: 94118" maxlength="6">
                    </div>

                    <!-- Tempat Tinggal (Dropdown Pengaturan) -->
                    <div class="form-group">
                        <label class="form-label">Tempat Tinggal <span class="req">*</span></label>
                        <select name="tempat_tinggal" class="form-select" required>
                            <option value="">-- Pilih Status Tempat Tinggal --</option>
                            <?php foreach ($options['tempat_tinggal'] ?? [] as $optTinggal): ?>
                                <option value="<?= htmlspecialchars($optTinggal) ?>" <?= ($pendaftar['tempat_tinggal'] ?? '') === $optTinggal ? 'selected' : '' ?>><?= htmlspecialchars($optTinggal) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Moda Transportasi (Dropdown Pengaturan) -->
                    <div class="form-group">
                        <label class="form-label">Moda Transportasi ke Sekolah <span class="req">*</span></label>
                        <select name="moda_transportasi" class="form-select" required>
                            <option value="">-- Pilih Moda Transportasi --</option>
                            <?php foreach ($options['transportasi'] ?? [] as $optTrans): ?>
                                <option value="<?= htmlspecialchars($optTrans) ?>" <?= ($pendaftar['moda_transportasi'] ?? '') === $optTrans ? 'selected' : '' ?>><?= htmlspecialchars($optTrans) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Koordinat Lintang & Bujur (Auto Terisi) -->
                    <div class="form-group">
                        <label class="form-label">Lintang (Latitude) <span class="hint">Auto terisi GPS</span></label>
                        <input type="text" name="lintang" id="inputLintang" class="form-control" value="<?= val($pendaftar, 'lintang') ?>" placeholder="-0.891234">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bujur (Longitude) <span class="hint">Auto terisi GPS</span></label>
                        <input type="text" name="bujur" id="inputBujur" class="form-control" value="<?= val($pendaftar, 'bujur') ?>" placeholder="119.876543">
                    </div>
                </div>

                <!-- Action Bar Tab 3 -->
                <div class="action-bar">
                    <button type="button" class="btn-action btn-prev" onclick="switchTab(2)">
                        <i class="fa-solid fa-arrow-left"></i> Kembali: Sekolah Sebelumnya
                    </button>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" class="btn-action btn-draft" onclick="simpanDraftTab(3)">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Draft
                        </button>
                        <button type="button" class="btn-action btn-next" onclick="nextTab(4)">
                            Lanjut: Data Orang Tua <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 TAB 4: DATA ORANG TUA (IBU & AYAH) + KONTAK
                 ══════════════════════════════════════════════════════════════════ -->
            <div class="form-card tab-content" id="tabContent4" style="<?= $activeTab === 4 ? '' : 'display:none;' ?>">
                <div class="tab-section-header">
                    <div class="tab-section-title">
                        <h3><i class="fa-solid fa-people-roof" style="color:var(--primary);"></i> Bagian 4: Data Orang Tua & Kontak</h3>
                        <p>Isi data kedua orang tua (Ibu Kandung dan Ayah Kandung) serta nomor kontak aktif.</p>
                    </div>
                </div>

                <div class="form-grid grid-2">
                    <!-- SUBHEADER DATA IBU -->
                    <div class="subheading-divider">
                        <i class="fa-solid fa-person-dress"></i>
                        <span>A. Data Ibu Kandung</span>
                    </div>

                    <!-- 1. Nama Ibu -->
                    <div class="form-group col-span-2">
                        <label class="form-label">Nama Lengkap Ibu Kandung <span class="req">*</span> <span class="hint">Sesuai Akta Kelahiran Anak</span></label>
                        <input type="text" name="nama_ibu" class="form-control" value="<?= val($pendaftar, 'nama_ibu') ?>" required placeholder="Nama lengkap tanpa gelar">
                    </div>

                    <!-- 2. NIK Ibu -->
                    <div class="form-group">
                        <label class="form-label">NIK Ibu <span class="req">*</span> <span class="hint">16 digit KTP</span></label>
                        <input type="text" name="nik_ibu" class="form-control" value="<?= val($pendaftar, 'nik_ibu') ?>" maxlength="16" required placeholder="Contoh: 727101...">
                    </div>

                    <!-- 3. Tahun Lahir Ibu -->
                    <div class="form-group">
                        <label class="form-label">Tahun Lahir Ibu <span class="req">*</span> <span class="hint">4 digit tahun</span></label>
                        <input type="number" name="tahun_lahir_ibu" class="form-control" value="<?= val($pendaftar, 'tahun_lahir_ibu') ?>" min="1950" max="2010" required placeholder="Contoh: 1988">
                    </div>

                    <!-- 4. Pendidikan Terakhir Ibu -->
                    <div class="form-group">
                        <label class="form-label">Pendidikan Terakhir Ibu <span class="req">*</span></label>
                        <select name="pendidikan_ibu" class="form-select" required>
                            <option value="">-- Pilih Pendidikan Ibu --</option>
                            <?php foreach ($options['pendidikan'] ?? [] as $optPend): ?>
                                <option value="<?= htmlspecialchars($optPend) ?>" <?= ($pendaftar['pendidikan_ibu'] ?? '') === $optPend ? 'selected' : '' ?>><?= htmlspecialchars($optPend) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 5. Pekerjaan Ibu -->
                    <div class="form-group">
                        <label class="form-label">Pekerjaan Ibu <span class="req">*</span></label>
                        <select name="pekerjaan_ibu" class="form-select" required>
                            <option value="">-- Pilih Pekerjaan Ibu --</option>
                            <?php foreach ($options['pekerjaan'] ?? [] as $optPek): ?>
                                <option value="<?= htmlspecialchars($optPek) ?>" <?= ($pendaftar['pekerjaan_ibu'] ?? '') === $optPek ? 'selected' : '' ?>><?= htmlspecialchars($optPek) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 6. Nama Kantor / Usaha Ibu -->
                    <div class="form-group">
                        <label class="form-label">Nama Kantor / Usaha Ibu</label>
                        <input type="text" name="kantor_ibu" class="form-control" value="<?= val($pendaftar, 'kantor_ibu') ?>" placeholder="Nama instansi/perusahaan/toko">
                    </div>

                    <!-- 7. Jabatan Ibu -->
                    <div class="form-group">
                        <label class="form-label">Jabatan Ibu</label>
                        <input type="text" name="jabatan_ibu" class="form-control" value="<?= val($pendaftar, 'jabatan_ibu') ?>" placeholder="Contoh: Staff, Manajer, Pemilik Usaha">
                    </div>

                    <!-- 8. Penghasilan Bulanan Ibu -->
                    <div class="form-group">
                        <label class="form-label">Penghasilan Bulanan Ibu <span class="req">*</span></label>
                        <select name="penghasilan_ibu" class="form-select" required>
                            <option value="">-- Pilih Rentang Penghasilan --</option>
                            <?php foreach ($options['penghasilan'] ?? [] as $optGaji): ?>
                                <option value="<?= htmlspecialchars($optGaji) ?>" <?= ($pendaftar['penghasilan_ibu'] ?? '') === $optGaji ? 'selected' : '' ?>><?= htmlspecialchars($optGaji) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 9. Berkebutuhan Khusus Ibu -->
                    <div class="form-group">
                        <label class="form-label">Kebutuhan Khusus Ibu</label>
                        <select name="kebutuhan_khusus_ibu" class="form-select">
                            <option value="">-- Tidak Ada / Normal --</option>
                            <?php foreach ($options['kebutuhan_khusus'] ?? [] as $optKhusus): ?>
                                <option value="<?= htmlspecialchars($optKhusus) ?>" <?= ($pendaftar['kebutuhan_khusus_ibu'] ?? '') === $optKhusus ? 'selected' : '' ?>><?= htmlspecialchars($optKhusus) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- SUBHEADER DATA AYAH -->
                    <div class="subheading-divider" style="margin-top:24px;">
                        <i class="fa-solid fa-person"></i>
                        <span>B. Data Ayah Kandung</span>
                    </div>

                    <!-- 1. Nama Ayah -->
                    <div class="form-group col-span-2">
                        <label class="form-label">Nama Lengkap Ayah Kandung <span class="req">*</span></label>
                        <input type="text" name="nama_ayah" class="form-control" value="<?= val($pendaftar, 'nama_ayah') ?>" required placeholder="Nama lengkap tanpa gelar">
                    </div>

                    <!-- 2. NIK Ayah -->
                    <div class="form-group">
                        <label class="form-label">NIK Ayah <span class="req">*</span> <span class="hint">16 digit KTP</span></label>
                        <input type="text" name="nik_ayah" class="form-control" value="<?= val($pendaftar, 'nik_ayah') ?>" maxlength="16" required placeholder="Contoh: 727101...">
                    </div>

                    <!-- 3. Tahun Lahir Ayah -->
                    <div class="form-group">
                        <label class="form-label">Tahun Lahir Ayah <span class="req">*</span> <span class="hint">4 digit tahun</span></label>
                        <input type="number" name="tahun_lahir_ayah" class="form-control" value="<?= val($pendaftar, 'tahun_lahir_ayah') ?>" min="1940" max="2010" required placeholder="Contoh: 1985">
                    </div>

                    <!-- 4. Pendidikan Terakhir Ayah -->
                    <div class="form-group">
                        <label class="form-label">Pendidikan Terakhir Ayah <span class="req">*</span></label>
                        <select name="pendidikan_ayah" class="form-select" required>
                            <option value="">-- Pilih Pendidikan Ayah --</option>
                            <?php foreach ($options['pendidikan'] ?? [] as $optPend): ?>
                                <option value="<?= htmlspecialchars($optPend) ?>" <?= ($pendaftar['pendidikan_ayah'] ?? '') === $optPend ? 'selected' : '' ?>><?= htmlspecialchars($optPend) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 5. Pekerjaan Ayah -->
                    <div class="form-group">
                        <label class="form-label">Pekerjaan Ayah <span class="req">*</span></label>
                        <select name="pekerjaan_ayah" class="form-select" required>
                            <option value="">-- Pilih Pekerjaan Ayah --</option>
                            <?php foreach ($options['pekerjaan'] ?? [] as $optPek): ?>
                                <option value="<?= htmlspecialchars($optPek) ?>" <?= ($pendaftar['pekerjaan_ayah'] ?? '') === $optPek ? 'selected' : '' ?>><?= htmlspecialchars($optPek) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 6. Nama Kantor / Usaha Ayah -->
                    <div class="form-group">
                        <label class="form-label">Nama Kantor / Usaha Ayah</label>
                        <input type="text" name="kantor_ayah" class="form-control" value="<?= val($pendaftar, 'kantor_ayah') ?>" placeholder="Nama instansi/perusahaan/toko">
                    </div>

                    <!-- 7. Jabatan Ayah -->
                    <div class="form-group">
                        <label class="form-label">Jabatan Ayah</label>
                        <input type="text" name="jabatan_ayah" class="form-control" value="<?= val($pendaftar, 'jabatan_ayah') ?>" placeholder="Contoh: Staff, Manajer, Pemilik Usaha">
                    </div>

                    <!-- 8. Penghasilan Bulanan Ayah -->
                    <div class="form-group">
                        <label class="form-label">Penghasilan Bulanan Ayah <span class="req">*</span></label>
                        <select name="penghasilan_ayah" class="form-select" required>
                            <option value="">-- Pilih Rentang Penghasilan --</option>
                            <?php foreach ($options['penghasilan'] ?? [] as $optGaji): ?>
                                <option value="<?= htmlspecialchars($optGaji) ?>" <?= ($pendaftar['penghasilan_ayah'] ?? '') === $optGaji ? 'selected' : '' ?>><?= htmlspecialchars($optGaji) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 9. Berkebutuhan Khusus Ayah -->
                    <div class="form-group">
                        <label class="form-label">Kebutuhan Khusus Ayah</label>
                        <select name="kebutuhan_khusus_ayah" class="form-select">
                            <option value="">-- Tidak Ada / Normal --</option>
                            <?php foreach ($options['kebutuhan_khusus'] ?? [] as $optKhusus): ?>
                                <option value="<?= htmlspecialchars($optKhusus) ?>" <?= ($pendaftar['kebutuhan_khusus_ayah'] ?? '') === $optKhusus ? 'selected' : '' ?>><?= htmlspecialchars($optKhusus) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- SUBHEADER KONTAK -->
                    <div class="subheading-divider" style="margin-top:24px;">
                        <i class="fa-solid fa-address-book"></i>
                        <span>C. Kontak Orang Tua / Wali</span>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label">Email Aktif</label>
                        <input type="email" name="email" class="form-control" value="<?= val($pendaftar, 'email') ?>" placeholder="orangtua@gmail.com">
                    </div>

                    <!-- No HP -->
                    <div class="form-group">
                        <label class="form-label">No. HP Biasa / Telepon</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= val($pendaftar, 'no_hp') ?>" placeholder="081234567890">
                    </div>

                    <!-- No WhatsApp -->
                    <div class="form-group col-span-2">
                        <label class="form-label">No. WhatsApp Utama <span class="req">*</span> <span class="hint">Untuk notifikasi berkas & pengumuman</span></label>
                        <input type="text" name="no_wa" class="form-control" value="<?= val($pendaftar, 'no_wa', $pendaftar['no_hp'] ?? '') ?>" required placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <!-- Action Bar Tab 4 -->
                <div class="action-bar">
                    <button type="button" class="btn-action btn-prev" onclick="switchTab(3)">
                        <i class="fa-solid fa-arrow-left"></i> Kembali: Tempat Tinggal
                    </button>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" class="btn-action btn-draft" onclick="simpanDraftTab(4)">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Draft
                        </button>
                        <button type="button" class="btn-action btn-next" onclick="nextTab(5)">
                            Lanjut: Berkas & Keamanan <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════════
                 TAB 5: BERKAS PENDUKUNG & PERSETUJUAN KEAMANAN DATA
                 ══════════════════════════════════════════════════════════════════ -->
            <div class="form-card tab-content" id="tabContent5" style="<?= $activeTab === 5 ? '' : 'display:none;' ?>">
                <div class="tab-section-header">
                    <div class="tab-section-title">
                        <h3><i class="fa-solid fa-shield-halved" style="color:var(--primary);"></i> Bagian 5: Berkas Pendukung & Keamanan Data</h3>
                        <p>Unggah foto dokumen resmi (otomatis terkompresi maks 500 KB) dan baca persetujuan keamanan data.</p>
                    </div>
                </div>

                <!-- Upload Cards 2x2 Grid -->
                <div class="form-grid grid-2" style="margin-bottom:32px;">
                    <!-- 1. Kartu Keluarga -->
                    <div class="upload-card">
                        <i class="fa-solid fa-users upload-icon"></i>
                        <div class="upload-title">1. Foto Kartu Keluarga (KK) <span class="req">*</span></div>
                        <div class="upload-subtitle">Pastikan seluruh nomor NIK dan tulisan terbaca jelas (Maks. 500 KB)</div>
                        <input type="file" name="berkas_kk" class="upload-input" accept="image/*,.pdf" onchange="previewUpload(this, 'previewKk')">
                        <div id="previewKk" class="upload-preview-box" <?= !empty($pendaftar['berkas_kk']) ? 'style="display:block;"' : '' ?>>
                            <?php if (!empty($pendaftar['berkas_kk'])): ?>
                                <div class="file-info-badge"><i class="fa-solid fa-check-circle"></i> KK Tersimpan</div>
                                <div style="margin-top:6px;"><a href="<?= htmlspecialchars(asset($pendaftar['berkas_kk'])) ?>" target="_blank" style="font-size:12px; color:var(--primary); font-weight:600;">Lihat Berkas</a></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 2. Akta Kelahiran -->
                    <div class="upload-card">
                        <i class="fa-solid fa-certificate upload-icon"></i>
                        <div class="upload-title">2. Foto Akta Kelahiran <span class="req">*</span></div>
                        <div class="upload-subtitle">Nomor registrasi akta dan nama orang tua terbaca jelas (Maks. 500 KB)</div>
                        <input type="file" name="berkas_akta" class="upload-input" accept="image/*,.pdf" onchange="previewUpload(this, 'previewAkta')">
                        <div id="previewAkta" class="upload-preview-box" <?= !empty($pendaftar['berkas_akta']) ? 'style="display:block;"' : '' ?>>
                            <?php if (!empty($pendaftar['berkas_akta'])): ?>
                                <div class="file-info-badge"><i class="fa-solid fa-check-circle"></i> Akta Tersimpan</div>
                                <div style="margin-top:6px;"><a href="<?= htmlspecialchars(asset($pendaftar['berkas_akta'])) ?>" target="_blank" style="font-size:12px; color:var(--primary); font-weight:600;">Lihat Berkas</a></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 3. KTP Ayah -->
                    <div class="upload-card">
                        <i class="fa-solid fa-id-card upload-icon"></i>
                        <div class="upload-title">3. Foto KTP Ayah Kandung <span class="req">*</span></div>
                        <div class="upload-subtitle">Foto bagian depan e-KTP Ayah (Maks. 500 KB)</div>
                        <input type="file" name="berkas_ktp_ayah" class="upload-input" accept="image/*,.pdf" onchange="previewUpload(this, 'previewKtpAyah')">
                        <div id="previewKtpAyah" class="upload-preview-box" <?= !empty($pendaftar['berkas_ktp_ayah']) ? 'style="display:block;"' : '' ?>>
                            <?php if (!empty($pendaftar['berkas_ktp_ayah'])): ?>
                                <div class="file-info-badge"><i class="fa-solid fa-check-circle"></i> KTP Ayah Tersimpan</div>
                                <div style="margin-top:6px;"><a href="<?= htmlspecialchars(asset($pendaftar['berkas_ktp_ayah'])) ?>" target="_blank" style="font-size:12px; color:var(--primary); font-weight:600;">Lihat Berkas</a></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 4. KTP Ibu -->
                    <div class="upload-card">
                        <i class="fa-solid fa-id-card upload-icon"></i>
                        <div class="upload-title">4. Foto KTP Ibu Kandung <span class="req">*</span></div>
                        <div class="upload-subtitle">Foto bagian depan e-KTP Ibu (Maks. 500 KB)</div>
                        <input type="file" name="berkas_ktp_ibu" class="upload-input" accept="image/*,.pdf" onchange="previewUpload(this, 'previewKtpIbu')">
                        <div id="previewKtpIbu" class="upload-preview-box" <?= !empty($pendaftar['berkas_ktp_ibu']) ? 'style="display:block;"' : '' ?>>
                            <?php if (!empty($pendaftar['berkas_ktp_ibu'])): ?>
                                <div class="file-info-badge"><i class="fa-solid fa-check-circle"></i> KTP Ibu Tersimpan</div>
                                <div style="margin-top:6px;"><a href="<?= htmlspecialchars(asset($pendaftar['berkas_ktp_ibu'])) ?>" target="_blank" style="font-size:12px; color:var(--primary); font-weight:600;">Lihat Berkas</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════════
                     KEAMANAN DATA & PERSETUJUAN (MODAL POPUP)
                     ══════════════════════════════════════════════════════════════ -->
                <div class="privacy-box-container">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px; flex-wrap:wrap;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; border-radius:12px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div>
                                <h4 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">
                                    Pernyataan & Persetujuan Keamanan Data
                                </h4>
                                <p style="font-size:12px; color:var(--slate-500); margin:2px 0 0 0;">
                                    Tujuan dokumen KTP & Kartu Keluarga untuk Dapodik & EMIS resmi SIT Bina Insan Palu
                                </p>
                            </div>
                        </div>
                        <button type="button" onclick="openPrivacyModal(event)" style="padding:7px 14px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; font-size:12px; font-weight:700; color:#0284c7; display:inline-flex; align-items:center; gap:6px; cursor:pointer; transition:all 0.2s;">
                            <i class="fa-solid fa-file-shield"></i> Baca Lembar Penjelasan
                        </button>
                    </div>

                    <!-- Checkbox Persetujuan Interaktif (Klik Membuka Modal) -->
                    <div class="checkbox-container <?= !empty($pendaftar['persetujuan_data']) ? 'agreed' : '' ?>" id="checkboxWrapper" onclick="onCheckboxContainerClick(event)">
                        <input type="checkbox" name="persetujuan_data" id="persetujuanCheckbox" value="1" <?= !empty($pendaftar['persetujuan_data']) ? 'checked' : '' ?> onclick="onCheckboxInputClick(event)" onchange="syncCheckboxState()">
                        <div style="flex:1;">
                            <span class="checkbox-label" style="display:block; line-height:1.5;">
                                Saya telah membaca seluruh penjelasan tujuan pengumpulan dokumen (KTP & KK), memahami kebijakan privasi data, dan menyatakan bahwa seluruh data yang saya isikan adalah benar dan sah.
                            </span>
                            <div id="agreedBadge" style="<?= !empty($pendaftar['persetujuan_data']) ? 'display:flex;' : 'display:none;' ?> align-items:center; gap:6px; margin-top:8px; font-size:12px; font-weight:700; color:#15803d;">
                                <i class="fa-solid fa-circle-check"></i> Persetujuan Telah Diberikan &bull; <a href="javascript:void(0)" onclick="openPrivacyModal(event)" style="color:#0284c7; text-decoration:underline;">Lihat Rincian Pernyataan</a>
                            </div>
                            <div id="unagreedHint" style="<?= empty($pendaftar['persetujuan_data']) ? 'display:block;' : 'display:none;' ?> margin-top:6px; font-size:11px; color:#64748b;">
                                <i class="fa-solid fa-circle-info" style="color:#0284c7;"></i> Klik kotak ini untuk membaca & menyetujui Lembar Pernyataan Keamanan Data
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar Tab 5 (Final Submit) -->
                <div class="action-bar">
                    <button type="button" class="btn-action btn-prev" onclick="switchTab(4)">
                        <i class="fa-solid fa-arrow-left"></i> Kembali: Data Orang Tua
                    </button>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" class="btn-action btn-draft" onclick="simpanDraftTab(5)">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Draft
                        </button>
                        <button type="button" id="btnFinalSubmit" class="btn-action btn-submit" disabled onclick="submitFinalForm()">
                            <i class="fa-solid fa-paper-plane"></i> Kirim Formulir Final
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- Modal Persetujuan & Keamanan Data (KTP & KK) -->
    <div class="modal-overlay" id="privacyModal">
        <div class="privacy-modal-card">
            <!-- Modal Header -->
            <div class="privacy-modal-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:12px; background:#dbeafe; color:#0284c7; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">
                            Pernyataan Keamanan Data (KTP & KK)
                        </h3>
                        <p style="font-size:11px; color:var(--slate-500); margin:2px 0 0 0;">
                            SIT Bina Insan Palu &bull; Kebijakan Privasi & Ketentuan Dokumen
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closePrivacyModal()" style="width:32px; height:32px; border-radius:8px; background:transparent; border:none; color:var(--slate-400); font-size:22px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; line-height:1;">
                    &times;
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="privacy-modal-body">
                <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:12px 14px; margin-bottom:16px; font-size:12px; color:#0369a1; display:flex; gap:10px; align-items:flex-start;">
                    <i class="fa-solid fa-circle-info" style="margin-top:2px; font-size:14px; color:#0284c7; flex-shrink:0;"></i>
                    <div style="line-height:1.5;">
                        Mohon luangkan waktu sejenak untuk membaca tujuan pengumpulan berkas identitas dan komitmen kerahasiaan data kami sebelum menyetujui.
                    </div>
                </div>

                <!-- Bagian 1: Tujuan -->
                <div style="margin-bottom:18px;">
                    <h4 style="font-size:13px; font-weight:800; color:var(--slate-900); margin:0 0 8px 0; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-clipboard-check" style="color:var(--primary);"></i> 1. Tujuan Pengumpulan Data Kependudukan (KTP & KK)
                    </h4>
                    <ol style="padding-left:20px; margin:0; line-height:1.6;">
                        <li style="margin-bottom:8px;">
                            <strong>Sinkronisasi Resmi Dapodik & EMIS:</strong> Kementerian Pendidikan dan Kementerian Agama Republik Indonesia mewajibkan pencocokan data NIK anak, Nomor Kartu Keluarga (KK), serta NIK kedua orang tua untuk menerbitkan Nomor Induk Siswa Nasional (NISN) resmi dan mendaftarkan siswa baru ke pangkalan data nasional.
                        </li>
                        <li style="margin-bottom:8px;">
                            <strong>Verifikasi Keabsahan Usia & Hubungan Keluarga:</strong> Dokumen Akta Kelahiran dan Kartu Keluarga diperlukan panitia untuk memverifikasi kesesuaian batas usia minimum masuk jenjang pendidikan serta memastikan wali yang mendaftarkan adalah orang tua sah.
                        </li>
                        <li style="margin-bottom:8px;">
                            <strong>Penyaluran Bantuan Operasional Sekolah (BOS):</strong> Validitas data kependudukan merupakan prasyarat verifikasi penerima dana BOS dari pemerintah pusat.
                        </li>
                    </ol>
                </div>

                <!-- Bagian 2: Komitmen Privasi -->
                <div style="margin-bottom:18px;">
                    <h4 style="font-size:13px; font-weight:800; color:var(--slate-900); margin:0 0 8px 0; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-lock" style="color:#0284c7;"></i> 2. Komitmen Perlindungan Privasi & Keamanan Data Sekolah
                    </h4>
                    <ul style="padding-left:20px; margin:0; list-style-type:disc; line-height:1.6;">
                        <li style="margin-bottom:8px;">
                            <strong>Penyimpanan Terenkripsi:</strong> Seluruh berkas foto KTP dan Kartu Keluarga disimpan pada server terproteksi dengan nama file acak terenkripsi dan tidak dapat diakses secara publik.
                        </li>
                        <li style="margin-bottom:8px;">
                            <strong>Akses Terbatas:</strong> Dokumen hanya dapat diakses oleh Panitia SPMB dan Operator Data Sekolah yang telah terikat pakta integritas kerahasiaan data.
                        </li>
                        <li style="margin-bottom:8px;">
                            <strong>Tidak Ada Penyalahgunaan / Pihak Ketiga:</strong> SIT Bina Insan Palu menjamin bahwa data identitas dan dokumen kependudukan Anda TIDAK AKAN PERNAH dijual, dipinjamkan, atau dialihkan kepada pihak komersial maupun pihak ketiga mana pun.
                        </li>
                    </ul>
                </div>

                <!-- Bagian 3: Pernyataan Persetujuan -->
                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 14px; font-size:12px; color:#166534; line-height:1.5;">
                    <div style="font-weight:700; margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                        <i class="fa-solid fa-certificate" style="color:#16a34a;"></i> Pernyataan Orang Tua / Wali Siswa:
                    </div>
                    Dengan menekan tombol <strong>"Saya Mengerti & Setujui"</strong> di bawah ini, saya menyatakan telah membaca seluruh penjelasan di atas secara tuntas, memahami tujuan pengumpulan dokumen, serta menyatakan bahwa seluruh data yang saya isikan adalah benar dan sah.
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="privacy-modal-footer">
                <button type="button" class="btn-action btn-prev" onclick="closePrivacyModal()" style="padding:9px 18px; font-size:13px;">
                    Batal
                </button>
                <button type="button" class="btn-action btn-next" onclick="confirmPrivacyModal()" style="padding:9px 22px; font-size:13px; background:linear-gradient(135deg, #16a34a, #15803d); border-color:#16a34a; box-shadow:0 2px 8px rgba(22, 163, 74, 0.3);">
                    <i class="fa-solid fa-check-double"></i> Saya Mengerti & Setujui
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi GPS Titik Rumah -->
    <div class="modal-overlay" id="gpsModal">
        <div class="modal-card" id="gpsModalCard">
            <!-- State 1: Peringatan Berada di Rumah -->
            <div id="gpsModalPrompt">
                <div class="modal-icon-badge warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3>Peringatan Lokasi Rumah</h3>
                <p>
                    <strong>Pastikan Anda sedang berada di rumah saat ini!</strong><br>
                    Sistem akan membaca titik koordinat GPS posisi Anda untuk memastikan data zonasi rumah dan sinkronisasi Dapodik/EMIS akurat.
                </p>
                <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:10px; padding:10px 14px; margin-bottom:20px; font-size:12px; color:#92400e; text-align:left; line-height:1.4;">
                    <i class="fa-solid fa-circle-info" style="color:#d97706; margin-right:6px;"></i>
                    Pastikan <strong>fitur Lokasi/GPS</strong> di perangkat Anda telah aktif, lalu klik <strong>"Izinkan" (Allow)</strong> jika browser memunculkan permintaan akses.
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-action btn-prev" onclick="closeGpsModal()" style="padding:10px 18px;">
                        Batal
                    </button>
                    <button type="button" class="btn-action btn-next" onclick="executeGpsDetection()" style="padding:10px 22px;">
                        <i class="fa-solid fa-check"></i> Oke, Saya di Rumah
                    </button>
                </div>
            </div>

            <!-- State 2: Sedang Loading Deteksi Koordinat -->
            <div id="gpsModalLoading" style="display:none;">
                <div class="modal-icon-badge info gps-pulse-anim">
                    <i class="fa-solid fa-satellite-dish fa-bounce"></i>
                </div>
                <h3 id="gpsLoadingTitle">Mencari Titik Koordinat GPS...</h3>
                <p id="gpsLoadingDesc" style="min-height:44px; margin-bottom:16px;">
                    Menghubungkan ke satelit GPS perangkat Anda...<br>
                    <span style="font-size:12px; color:#64748b;">(Harap klik <strong>'Izinkan / Allow'</strong> jika muncul dialog izin lokasi di browser)</span>
                </p>
                <div style="height:6px; background:#e2e8f0; border-radius:10px; overflow:hidden; margin:0 auto 20px; max-width:320px;">
                    <div id="gpsProgressFill" style="width:35%; height:100%; background:linear-gradient(90deg, #0284c7, #38bdf8); transition:width 0.4s ease;"></div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-action btn-prev" onclick="cancelGpsDetection()" style="padding:8px 16px; font-size:12px;">
                        Batal
                    </button>
                </div>
            </div>

            <!-- State 3: Berhasil Ambil Koordinat -->
            <div id="gpsModalSuccess" style="display:none;">
                <div class="modal-icon-badge success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 style="color:#15803d;">Koordinat Berhasil Diambil!</h3>
                <p id="gpsSuccessDesc" style="color:#166534; font-size:13px; margin-bottom:12px;">
                    Titik rumah dan wilayah kelurahan berhasil terisi otomatis ke formulir.
                </p>
            </div>

            <!-- State 4: Error / Izin Ditolak -->
            <div id="gpsModalError" style="display:none;">
                <div class="modal-icon-badge" style="background:#fee2e2; color:#dc2626;">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <h3 style="color:#b91c1c;">Gagal Membaca Lokasi</h3>
                <p id="gpsErrorDesc" style="color:#991b1b; font-size:12px; margin-bottom:20px; line-height:1.4;">
                    Izin lokasi ditolak atau sinyal GPS tidak terdeteksi. Silakan aktifkan izin lokasi di browser atau masukkan alamat secara manual.
                </p>
                <div class="modal-actions">
                    <button type="button" class="btn-action btn-prev" onclick="closeGpsModal()" style="padding:10px 18px;">
                        Tutup / Isi Manual
                    </button>
                    <button type="button" class="btn-action btn-next" onclick="retryGpsDetection()" style="padding:10px 18px;">
                        <i class="fa-solid fa-rotate-right"></i> Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Data Final Sebelum Kirim -->
    <div class="modal-overlay" id="finalConfirmModal">
        <div class="confirm-modal-card">
            <!-- Modal Header -->
            <div class="confirm-modal-header">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg, #0284c7, #1d4ed8); color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; box-shadow:0 3px 10px rgba(2, 132, 199, 0.3);">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">
                            Konfirmasi Data Pendaftaran Final
                        </h3>
                        <p style="font-size:11px; color:var(--slate-500); margin:2px 0 0 0;">
                            SIT Bina Insan Palu &bull; Periksa ringkasan data sebelum dikirimkan
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeFinalConfirmModal()" style="width:32px; height:32px; border-radius:8px; background:transparent; border:none; color:var(--slate-400); font-size:22px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; line-height:1;" title="Tutup">
                    &times;
                </button>
            </div>

            <!-- Modal Body (Scrollable Summary) -->
            <div class="confirm-modal-body" id="finalConfirmModalBody">
                <!-- Alert Info -->
                <div style="background:#f0f9ff; border:1px solid #bae6fd; border-radius:10px; padding:11px 14px; font-size:12px; color:#0369a1; display:flex; gap:10px; align-items:center;">
                    <i class="fa-solid fa-circle-info" style="font-size:15px; color:#0284c7; flex-shrink:0;"></i>
                    <div style="line-height:1.5;">
                        Mohon periksa kembali kebenaran data di bawah ini. Pastikan identitas calon siswa, orang tua, dan dokumen kependudukan telah sesuai.
                    </div>
                </div>

                <!-- Kontainer Ringkasan yang Digenerate JS -->
                <div id="confirmSummaryContent" style="display:flex; flex-direction:column; gap:12px;"></div>

                <!-- Warning Penguncian Data -->
                <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:11px 14px; font-size:12px; color:#92400e; display:flex; gap:10px; align-items:flex-start;">
                    <i class="fa-solid fa-lock" style="font-size:14px; color:#d97706; margin-top:2px; flex-shrink:0;"></i>
                    <div style="line-height:1.5;">
                        <strong>Catatan Penguncian Data:</strong> Setelah formulir ini dikirim resmi, data akan masuk proses verifikasi Panitia SPMB dan tidak dapat diubah secara mandiri lagi.
                    </div>
                </div>

                <!-- Error Banner jika Submit Gagal -->
                <div id="confirmErrorBanner" style="display:none; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:11px 14px; border-radius:10px; font-size:12px; align-items:center; gap:8px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size:14px; color:#dc2626; flex-shrink:0;"></i>
                    <span id="confirmErrorText">Gagal mengirim formulir.</span>
                </div>
            </div>

            <!-- Modal Footer Actions -->
            <div class="confirm-modal-footer" id="finalConfirmModalFooter">
                <button type="button" id="btnCancelFinalSubmit" class="btn-action btn-prev" onclick="closeFinalConfirmModal()" style="padding:10px 18px; font-size:13px;">
                    <i class="fa-solid fa-pen-to-square"></i> Periksa / Edit Kembali
                </button>
                <button type="button" id="btnExecuteFinalSubmit" class="btn-action btn-submit" onclick="executeFinalSubmit()" style="padding:10px 22px; font-size:13px; background:linear-gradient(135deg, #16a34a, #15803d); border-color:#16a34a; box-shadow:0 3px 10px rgba(22, 163, 74, 0.35);">
                    <i class="fa-solid fa-paper-plane"></i> Ya, Simpan & Kirim Final
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Peringatan Data Belum Lengkap -->
    <div class="modal-overlay" id="incompleteDataModal">
        <div class="incomplete-modal-card">
            <!-- Modal Header -->
            <div class="confirm-modal-header" style="background:#fff7ed; border-bottom:1px solid #fed7aa;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg, #f97316, #dc2626); color:#ffffff; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; box-shadow:0 3px 10px rgba(234, 88, 12, 0.3);">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 style="font-size:15px; font-weight:800; color:#9a3412; margin:0;">
                            Data Pendaftaran Belum Lengkap
                        </h3>
                        <p style="font-size:11.5px; color:#c2410c; margin:2px 0 0 0;" id="incompleteSubtitle">
                            Seluruh kolom bertanda (*) dan berkas wajib dilengkapi sebelum disubmit.
                        </p>
                    </div>
                </div>
                <button type="button" onclick="closeIncompleteModal()" style="width:32px; height:32px; border-radius:8px; background:transparent; border:none; color:var(--slate-400); font-size:22px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s; line-height:1;" title="Tutup">
                    &times;
                </button>
            </div>

            <!-- Modal Body -->
            <div class="confirm-modal-body" style="padding:18px 20px;">
                <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:11px 14px; font-size:12px; color:#991b1b; display:flex; gap:10px; align-items:flex-start;">
                    <i class="fa-solid fa-circle-exclamation" style="font-size:14px; color:#dc2626; margin-top:2px; flex-shrink:0;"></i>
                    <div style="line-height:1.5;">
                        Formulir <strong>belum dapat dikirimkan</strong>. Silakan lengkapi data dan unggah berkas yang tercantum di bawah ini terlebih dahulu.
                    </div>
                </div>

                <!-- Daftar Kolom yang Belum Lengkap -->
                <div id="incompleteListContent" style="display:flex; flex-direction:column; gap:8px;"></div>
            </div>

            <!-- Modal Footer -->
            <div class="confirm-modal-footer">
                <button type="button" class="btn-action btn-prev" onclick="closeIncompleteModal()" style="padding:9px 18px; font-size:13px;">
                    Tutup
                </button>
                <button type="button" class="btn-action btn-next" onclick="goToFirstMissingField()" style="padding:9px 22px; font-size:13px; background:linear-gradient(135deg, #ea580c, #c2410c); border-color:#ea580c; box-shadow:0 3px 10px rgba(234, 88, 12, 0.3);">
                    <i class="fa-solid fa-pen-to-square"></i> Lengkapi Kolom Pertama
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-notif" id="toastNotif">
        <i class="fa-solid fa-circle-check" style="color:#10b981; font-size:16px;"></i>
        <span id="toastText">Draft berhasil disimpan</span>
    </div>

    <!-- JavaScript Interactivity -->
    <script>
        const TOKEN = <?= json_encode($token) ?>;
        const SAVE_DRAFT_URL = <?= json_encode(url('spmb/biodata/' . $token . '/save-draft')) ?>;
        const SUBMIT_URL = <?= json_encode(url('spmb/biodata/' . $token . '/submit')) ?>;
        const DEADLINE_TIMESTAMP = <?= (int)$batasTimestamp ?>;
        const STEP_NAMES = [
            'Data Calon Siswa',
            'Sekolah Sebelumnya',
            'Tempat Tinggal',
            'Data Orang Tua',
            'Berkas & Keamanan'
        ];
        let isScrolledFull = false;

        // Auto initialization saat load
        window.addEventListener('DOMContentLoaded', () => {
            updateCountdownTimer();
            setInterval(updateCountdownTimer, 1000);
            hitungUmur();
            checkInitialScroll();
            // Inisialisasi tab aktif dan posisikan tombolnya di tengah
            switchTab(<?= (int)$activeTab ?>);
        });

        // 0. Realtime Countdown Timer Function
        function updateCountdownTimer() {
            if (!DEADLINE_TIMESTAMP) return;
            const now = Math.floor(Date.now() / 1000);
            const diff = DEADLINE_TIMESTAMP - now;

            const daysEl = document.getElementById('cdDays');
            const hoursEl = document.getElementById('cdHours');
            const minsEl = document.getElementById('cdMinutes');
            const secsEl = document.getElementById('cdSeconds');
            const tickerEl = document.getElementById('countdownTicker');
            const expiredEl = document.getElementById('countdownExpiredBadge');

            if (diff <= 0) {
                if (daysEl) daysEl.textContent = '00';
                if (hoursEl) hoursEl.textContent = '00';
                if (minsEl) minsEl.textContent = '00';
                if (secsEl) secsEl.textContent = '00';
                if (tickerEl) tickerEl.style.display = 'none';
                if (expiredEl) expiredEl.style.display = 'inline-flex';
                return;
            }

            if (tickerEl) tickerEl.style.display = 'flex';
            if (expiredEl) expiredEl.style.display = 'none';

            const days = Math.floor(diff / 86400);
            const hours = Math.floor((diff % 86400) / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minsEl) minsEl.textContent = String(minutes).padStart(2, '0');
            if (secsEl) secsEl.textContent = String(seconds).padStart(2, '0');
        }

        // 1. Tab Switching Function (Smooth Centering & Mobile Progress Sync)
        function switchTab(step) {
            step = parseInt(step, 10) || 1;
            if (step < 1) step = 1;
            if (step > 5) step = 5;

            document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            const targetContent = document.getElementById('tabContent' + step);
            if (targetContent) targetContent.style.display = 'block';

            const tabBtns = document.querySelectorAll('.stepper-nav .tab-btn');
            const activeBtn = tabBtns[step - 1];
            if (activeBtn) {
                activeBtn.classList.add('active');
                // Scroll tombol aktif agar selalu terlihat di tengah layar mobile
                activeBtn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
            }

            const stepInput = document.getElementById('biodataStepInput');
            if (stepInput) stepInput.value = step;

            // Update Mobile Stepper Card
            const mStepNum = document.getElementById('mStepNum');
            const mStepName = document.getElementById('mStepName');
            const mProgressBar = document.getElementById('mProgressBar');
            const mPrevBtn = document.getElementById('mPrevBtn');
            const mNextBtn = document.getElementById('mNextBtn');

            if (mStepNum) mStepNum.textContent = step;
            if (mStepName) mStepName.textContent = STEP_NAMES[step - 1] || ('Tahap ' + step);
            if (mProgressBar) mProgressBar.style.width = (step * 20) + '%';
            if (mPrevBtn) mPrevBtn.disabled = (step === 1);
            if (mNextBtn) mNextBtn.disabled = (step === 5);

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function prevTabMobile() {
            const cur = parseInt(document.getElementById('biodataStepInput')?.value || '1', 10);
            if (cur > 1) switchTab(cur - 1);
        }

        function nextTabMobile() {
            const cur = parseInt(document.getElementById('biodataStepInput')?.value || '1', 10);
            if (cur < 5) nextTab(cur + 1);
        }

        function nextTab(nextStep) {
            // Simpan draft sebelum berpindah tab
            const currentStep = nextStep - 1;
            simpanDraftTab(currentStep, () => {
                switchTab(nextStep);
            });
        }

        // 2. Auto Hitung Umur Berdasarkan Patokan Umur Group SPMB
        const PATOKAN_UMUR_BIODATA = '<?= e($pendaftar['tanggal_patokan_umur'] ?? '2027-07-01') ?>';

        function hitungUmur() {
            const inputTgl = document.getElementById('tglLahirInput');
            const displayUmur = document.getElementById('umurAuto');
            if (!inputTgl || !inputTgl.value) return;

            const tglParts = inputTgl.value.split('-');
            if (tglParts.length < 3) return;
            const tglLahir = new Date(parseInt(tglParts[0], 10), parseInt(tglParts[1], 10) - 1, parseInt(tglParts[2], 10));

            const patokanParts = PATOKAN_UMUR_BIODATA.split('-');
            const patokan = new Date(parseInt(patokanParts[0], 10), parseInt(patokanParts[1], 10) - 1, parseInt(patokanParts[2], 10));

            if (isNaN(tglLahir.getTime()) || isNaN(patokan.getTime())) return;

            if (tglLahir > patokan) {
                displayUmur.value = 'Tanggal belum tiba';
                return;
            }

            let years = patokan.getFullYear() - tglLahir.getFullYear();
            let months = patokan.getMonth() - tglLahir.getMonth();
            let days = patokan.getDate() - tglLahir.getDate();

            if (days < 0) {
                months--;
            }
            if (months < 0) {
                years--;
                months += 12;
            }

            if (years < 0) {
                displayUmur.value = 'Tanggal belum tiba';
                return;
            }

            let hasil = years + " Tahun";
            if (months > 0) {
                hasil += " " + months + " Bulan";
            }
            displayUmur.value = hasil;
        }

        // 3. GPS Detection & Reverse Geocoding dengan Interactive Loading Modal
        let isGpsDetecting = false;

        function openGpsModal() {
            // Reset modal states to Prompt
            const p = document.getElementById('gpsModalPrompt');
            const l = document.getElementById('gpsModalLoading');
            const s = document.getElementById('gpsModalSuccess');
            const e = document.getElementById('gpsModalError');
            const prog = document.getElementById('gpsProgressFill');

            if (p) p.style.display = 'block';
            if (l) l.style.display = 'none';
            if (s) s.style.display = 'none';
            if (e) e.style.display = 'none';
            if (prog) prog.style.width = '35%';

            document.getElementById('gpsModal').classList.add('show');
        }

        function closeGpsModal() {
            document.getElementById('gpsModal').classList.remove('show');
            isGpsDetecting = false;
        }

        function cancelGpsDetection() {
            closeGpsModal();
        }

        function retryGpsDetection() {
            executeGpsDetection();
        }

        function executeGpsDetection() {
            isGpsDetecting = true;

            // Switch to loading state inside modal
            const p = document.getElementById('gpsModalPrompt');
            const l = document.getElementById('gpsModalLoading');
            const s = document.getElementById('gpsModalSuccess');
            const e = document.getElementById('gpsModalError');
            const prog = document.getElementById('gpsProgressFill');
            const titleEl = document.getElementById('gpsLoadingTitle');
            const descEl = document.getElementById('gpsLoadingDesc');

            if (p) p.style.display = 'none';
            if (e) e.style.display = 'none';
            if (s) s.style.display = 'none';
            if (l) l.style.display = 'block';

            if (titleEl) titleEl.textContent = "Mencari Titik Koordinat GPS...";
            if (descEl) descEl.innerHTML = "Menghubungkan ke satelit GPS perangkat Anda...<br><span style='font-size:12px; color:#64748b;'>Harap klik <strong>'Izinkan / Allow'</strong> jika muncul dialog izin lokasi di browser.</span>";
            if (prog) prog.style.width = '40%';

            if (!navigator.geolocation) {
                showGpsError("Perangkat atau browser Anda tidak mendukung fitur Geolocation.");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                async (position) => {
                    if (!isGpsDetecting) return; // User already cancelled

                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    const inputLat = document.getElementById('inputLintang');
                    const inputLng = document.getElementById('inputBujur');
                    if (inputLat) inputLat.value = lat.toFixed(7);
                    if (inputLng) inputLng.value = lng.toFixed(7);

                    // Update loading state: reverse geocoding
                    if (titleEl) titleEl.textContent = "Koordinat Ditemukan!";
                    if (descEl) descEl.innerHTML = `Lintang: <strong>${lat.toFixed(5)}</strong>, Bujur: <strong>${lng.toFixed(5)}</strong><br>Sedang membaca nama kelurahan, kecamatan, dan kota...`;
                    if (prog) prog.style.width = '75%';

                    // Reverse Geocoding via OpenStreetMap Nominatim
                    let reverseSuccess = false;
                    try {
                        const controller = new AbortController();
                        const timeoutId = setTimeout(() => controller.abort(), 6000);
                        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, {
                            signal: controller.signal
                        });
                        clearTimeout(timeoutId);

                        if (res.ok) {
                            const data = await res.json();
                            const addr = data.address || {};

                            if (addr.village || addr.suburb || addr.quarter) {
                                document.getElementById('inputKelurahan').value = addr.village || addr.suburb || addr.quarter;
                            }
                            if (addr.city_district || addr.municipality) {
                                document.getElementById('inputKecamatan').value = addr.city_district || addr.municipality;
                            }
                            if (addr.city || addr.town || addr.county) {
                                document.getElementById('inputKabupaten').value = addr.city || addr.town || addr.county;
                            }
                            if (addr.state) {
                                document.getElementById('inputProvinsi').value = addr.state;
                            }
                            if (addr.postcode) {
                                document.getElementById('inputKodePos').value = addr.postcode;
                            }
                            if (data.display_name && !document.getElementById('inputAlamat').value) {
                                document.getElementById('inputAlamat').value = data.display_name;
                            }
                            reverseSuccess = true;
                        }
                    } catch (err) {
                        console.warn("Reverse geocode fetch error/timeout:", err);
                    }

                    if (prog) prog.style.width = '100%';

                    // Tampilkan State Sukses di dalam modal
                    if (l) l.style.display = 'none';
                    if (s) s.style.display = 'block';

                    const successDesc = document.getElementById('gpsSuccessDesc');
                    if (successDesc) {
                        if (reverseSuccess) {
                            successDesc.textContent = "Titik koordinat dan alamat wilayah berhasil terisi otomatis!";
                        } else {
                            successDesc.textContent = "Titik koordinat GPS berhasil disimpan! Silakan lengkapi nama jalan & kelurahan.";
                        }
                    }

                    // Berikan efek highlight border glowing biru pada input yang terisi
                    ['inputLintang', 'inputBujur', 'inputKelurahan', 'inputKecamatan', 'inputKabupaten', 'inputProvinsi'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) {
                            el.classList.add('gps-highlight-field');
                            setTimeout(() => el.classList.remove('gps-highlight-field'), 3000);
                        }
                    });

                    // Tutup modal secara otomatis setelah 1.3 detik dengan toast konfirmasi
                    setTimeout(() => {
                        closeGpsModal();
                        showToast("Titik koordinat GPS & alamat berhasil didapatkan!", "success");
                    }, 1300);
                },
                (err) => {
                    let msg = "Gagal membaca lokasi. Pastikan fitur GPS aktif di perangkat Anda.";
                    if (err.code === 1) {
                        msg = "Izin akses lokasi ditolak oleh browser/pengguna. Silakan aktifkan izin lokasi di pengaturan browser Anda.";
                    } else if (err.code === 2) {
                        msg = "Sinyal posisi GPS tidak tersedia. Silakan coba beberapa saat lagi atau masukkan secara manual.";
                    } else if (err.code === 3) {
                        msg = "Waktu pencarian sinyal GPS habis (timeout). Silakan klik tombol Coba Lagi.";
                    }
                    showGpsError(msg);
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        function showGpsError(msg) {
            const l = document.getElementById('gpsModalLoading');
            const e = document.getElementById('gpsModalError');
            const errDesc = document.getElementById('gpsErrorDesc');

            if (l) l.style.display = 'none';
            if (e) e.style.display = 'block';
            if (errDesc) errDesc.textContent = msg;
            isGpsDetecting = false;
        }

        // 4. Client-side Image Compression (Canvas <= 500 KB)
        async function compressImageFile(file, maxKB = 500) {
            if (!file.type.startsWith('image/')) return file; // PDF don't compress via canvas

            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        let width = img.width;
                        let height = img.height;
                        const maxDim = 1600;

                        if (width > maxDim || height > maxDim) {
                            if (width > height) {
                                height = Math.round((height * maxDim) / width);
                                width = maxDim;
                            } else {
                                width = Math.round((width * maxDim) / height);
                                height = maxDim;
                            }
                        }

                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        let quality = 0.8;
                        canvas.toBlob((blob) => {
                            if (blob) {
                                const compressedFile = new File([blob], file.name.replace(/\.[^/.]+$/, ".jpg"), {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(compressedFile);
                            } else {
                                resolve(file);
                            }
                        }, 'image/jpeg', quality);
                    };
                };
            });
        }

        // 5. File Upload Preview & Compress
        async function previewUpload(input, previewBoxId) {
            const previewBox = document.getElementById(previewBoxId);
            if (!input.files || !input.files[0]) return;

            const file = input.files[0];
            const sizeKB = Math.round(file.size / 1024);

            previewBox.style.display = 'block';
            previewBox.innerHTML = `
                <div class="file-info-badge">
                    <i class="fa-solid fa-spinner fa-spin"></i> Memproses ${file.name} (${sizeKB} KB)...
                </div>
            `;

            // Auto-compress
            const compressed = await compressImageFile(file, 500);
            const compSizeKB = Math.round(compressed.size / 1024);

            previewBox.innerHTML = `
                <div class="file-info-badge">
                    <i class="fa-solid fa-circle-check"></i> Siap diupload: ${compressed.name} (~${compSizeKB} KB)
                </div>
            `;
        }

        // 6. Privacy Agreement Modal & Checkbox Handler
        function openPrivacyModal(e) {
            if (e && e.stopPropagation) e.stopPropagation();
            const modal = document.getElementById('privacyModal');
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        function closePrivacyModal() {
            const modal = document.getElementById('privacyModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function confirmPrivacyModal() {
            const chk = document.getElementById('persetujuanCheckbox');
            if (chk) {
                chk.checked = true;
            }
            syncCheckboxState();
            closePrivacyModal();
            showToast("Pernyataan keamanan data berhasil disetujui", "success");
        }

        function onCheckboxInputClick(e) {
            const chk = document.getElementById('persetujuanCheckbox');
            if (!chk) return;

            // Jika awalnya tidak checked, saat diklik input menjadi checked.
            // Buka modal untuk konfirmasi dan membaca lembar persetujuan:
            if (chk.checked) {
                chk.checked = false; // Kembalikan ke false sementara sebelum dikonfirmasi di modal
                openPrivacyModal();
            } else {
                // Jika tadinya checked dan sekarang user ingin membatalkan/uncheck
                syncCheckboxState();
            }
        }

        function onCheckboxContainerClick(e) {
            // Jika yang diklik adalah input checkbox atau link, biarkan handler input yang bekerja
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'A' || e.target.closest('a')) return;

            const chk = document.getElementById('persetujuanCheckbox');
            if (!chk) return;

            openPrivacyModal();
        }

        function syncCheckboxState() {
            const chk = document.getElementById('persetujuanCheckbox');
            const wrapper = document.getElementById('checkboxWrapper');
            const btn = document.getElementById('btnFinalSubmit');
            const agreedBadge = document.getElementById('agreedBadge');
            const unagreedHint = document.getElementById('unagreedHint');

            if (!chk) return;

            if (chk.checked) {
                if (wrapper) wrapper.classList.add('agreed');
                if (agreedBadge) agreedBadge.style.display = 'flex';
                if (unagreedHint) unagreedHint.style.display = 'none';
                if (btn) btn.disabled = false;
            } else {
                if (wrapper) wrapper.classList.remove('agreed');
                if (agreedBadge) agreedBadge.style.display = 'none';
                if (unagreedHint) unagreedHint.style.display = 'block';
                if (btn) btn.disabled = true;
            }
        }

        function toggleSubmitButton() {
            syncCheckboxState();
        }

        function checkInitialScroll() {
            syncCheckboxState();
        }

        // Tutup modal jika tombol ESC ditekan atau klik backdrop
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePrivacyModal();
                closeFinalConfirmModal();
                closeIncompleteModal();
            }
        });

        const privModalOverlay = document.getElementById('privacyModal');
        if (privModalOverlay) {
            privModalOverlay.addEventListener('click', function(e) {
                if (e.target === privModalOverlay) {
                    closePrivacyModal();
                }
            });
        }

        const finalConfirmModalOverlay = document.getElementById('finalConfirmModal');
        if (finalConfirmModalOverlay) {
            finalConfirmModalOverlay.addEventListener('click', function(e) {
                if (e.target === finalConfirmModalOverlay) {
                    closeFinalConfirmModal();
                }
            });
        }

        const incompleteModalOverlay = document.getElementById('incompleteDataModal');
        if (incompleteModalOverlay) {
            incompleteModalOverlay.addEventListener('click', function(e) {
                if (e.target === incompleteModalOverlay) {
                    closeIncompleteModal();
                }
            });
        }

        // 7. Simpan Draft AJAX (Bisa Dicicil Kapan Saja)
        async function simpanDraftTab(tabNumber, callback = null) {
            showToast("Menyimpan draft...", "info");

            const form = document.getElementById('biodataForm');
            const formData = new FormData(form);
            formData.append('biodata_step', tabNumber);

            try {
                const response = await fetch(SAVE_DRAFT_URL, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const res = await response.json();
                if (res.success) {
                    showToast("Draft tersimpan pukul " + (res.last_saved || "baru saja"), "success");
                    if (typeof callback === 'function') callback();
                } else {
                    showToast(res.message || "Gagal menyimpan draft", "error");
                }
            } catch (err) {
                console.error("Draft save error:", err);
                showToast("Terjadi kesalahan koneksi", "error");
            }
        }

        // 8. Modal Konfirmasi Data Final
        const PENDAFTAR_STATIC = {
            nomor_pendaftaran: <?= json_encode($pendaftar['nomor_pendaftaran'] ?? '-') ?>,
            jenjang: <?= json_encode($pendaftar['jenjang'] ?? '-') ?>,
            kategori_pendaftar: <?= json_encode($pendaftar['kategori_pendaftar'] ?? '-') ?>,
            jalur_pendaftaran: <?= json_encode($pendaftar['jalur_pendaftaran'] ?? 'Reguler') ?>
        };

        const EXISTING_FILES = {
            berkas_kk: <?= !empty($pendaftar['berkas_kk']) ? 'true' : 'false' ?>,
            berkas_akta: <?= !empty($pendaftar['berkas_akta']) ? 'true' : 'false' ?>,
            berkas_ktp_ayah: <?= !empty($pendaftar['berkas_ktp_ayah']) ? 'true' : 'false' ?>,
            berkas_ktp_ibu: <?= !empty($pendaftar['berkas_ktp_ibu']) ? 'true' : 'false' ?>,
            file_screenshot_dapodik: <?= !empty($pendaftar['file_screenshot_dapodik']) ? 'true' : 'false' ?>
        };

        function getFormVal(name, defaultVal = '-') {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return defaultVal;
            if (el.tagName === 'SELECT') {
                const opt = el.options[el.selectedIndex];
                return (opt && opt.value) ? opt.text.trim() : defaultVal;
            }
            return (el.value && el.value.trim()) ? el.value.trim() : defaultVal;
        }

        function getFileStatusInfo(fieldName) {
            const input = document.querySelector(`input[name="${fieldName}"]`);
            if (input && input.files && input.files.length > 0) {
                const fName = input.files[0].name;
                const shortName = fName.length > 20 ? fName.substring(0, 18) + '...' : fName;
                return {
                    isOk: true,
                    text: `File baru: ${shortName}`
                };
            }
            if (EXISTING_FILES[fieldName]) {
                return {
                    isOk: true,
                    text: 'Sudah tersimpan di server'
                };
            }
            return {
                isOk: false,
                text: 'Belum diunggah'
            };
        }

        function openFinalConfirmModal() {
            const modal = document.getElementById('finalConfirmModal');
            const summaryContainer = document.getElementById('confirmSummaryContent');
            const errBanner = document.getElementById('confirmErrorBanner');
            const btnExec = document.getElementById('btnExecuteFinalSubmit');
            const btnCancel = document.getElementById('btnCancelFinalSubmit');
            const footer = document.getElementById('finalConfirmModalFooter');

            if (!modal || !summaryContainer) return;

            if (errBanner) errBanner.style.display = 'none';
            if (footer) footer.style.display = 'flex';
            if (btnExec) {
                btnExec.disabled = false;
                btnExec.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Ya, Simpan & Kirim Final';
            }
            if (btnCancel) btnCancel.disabled = false;

            // Data Calon Siswa
            const namaSiswa = getFormVal('nama_lengkap');
            const nikSiswa = getFormVal('nik');
            const noKk = getFormVal('no_kk');
            const jkVal = getFormVal('jenis_kelamin');
            const tempatLahir = getFormVal('tempat_lahir');
            const tglLahir = getFormVal('tanggal_lahir');
            const umurVal = document.getElementById('umurAuto')?.value || '-';
            const asalSekolah = getFormVal('sekolah_asal');
            const nisnVal = getFormVal('nisn');

            // Data Alamat
            const alamat = getFormVal('alamat');
            const rt = getFormVal('rt', '-');
            const rw = getFormVal('rw', '-');
            const dusun = getFormVal('dusun', '-');
            const kel = getFormVal('kelurahan');
            const kec = getFormVal('kecamatan');
            const kab = getFormVal('kabupaten');
            const prov = getFormVal('provinsi');
            const lat = getFormVal('lintang', '');
            const lng = getFormVal('bujur', '');
            const tempatTinggal = getFormVal('tempat_tinggal');
            const transportasi = getFormVal('moda_transportasi');

            // Data Orang Tua & Kontak
            const namaIbu = getFormVal('nama_ibu');
            const nikIbu = getFormVal('nik_ibu');
            const pekIbu = getFormVal('pekerjaan_ibu');
            const namaAyah = getFormVal('nama_ayah');
            const nikAyah = getFormVal('nik_ayah');
            const pekAyah = getFormVal('pekerjaan_ayah');
            const noWa = getFormVal('no_wa');

            // Berkas Dokumen
            const fileKk = getFileStatusInfo('berkas_kk');
            const fileAkta = getFileStatusInfo('berkas_akta');
            const fileKtpAyah = getFileStatusInfo('berkas_ktp_ayah');
            const fileKtpIbu = getFileStatusInfo('berkas_ktp_ibu');
            const fileDapodik = getFileStatusInfo('file_screenshot_dapodik');

            summaryContainer.innerHTML = `
                <!-- 1. Calon Siswa -->
                <div class="confirm-section-card">
                    <div class="confirm-section-title">
                        <span><i class="fa-solid fa-child"></i> 1. Identitas Calon Siswa</span>
                        <span style="font-size:11px; font-weight:700; color:var(--primary); background:#e0f2fe; padding:2px 8px; border-radius:6px;">
                            ${PENDAFTAR_STATIC.jenjang} &bull; ${PENDAFTAR_STATIC.nomor_pendaftaran}
                        </span>
                    </div>
                    <div class="confirm-grid">
                        <div class="confirm-item full-width">
                            <span class="confirm-label">Nama Lengkap</span>
                            <span class="confirm-val confirm-val-highlight">${namaSiswa}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Kategori Pendaftar</span>
                            <span class="confirm-val">${PENDAFTAR_STATIC.kategori_pendaftar}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Jenis Kelamin</span>
                            <span class="confirm-val">${jkVal}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">NIK Anak</span>
                            <span class="confirm-val" style="font-family:'JetBrains Mono',monospace;">${nikSiswa}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">No. Kartu Keluarga (KK)</span>
                            <span class="confirm-val" style="font-family:'JetBrains Mono',monospace;">${noKk}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Tempat, Tanggal Lahir</span>
                            <span class="confirm-val">${tempatLahir}, ${tglLahir}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Usia Terhitung</span>
                            <span class="confirm-val" style="color:#0284c7;">${umurVal}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Sekolah Asal</span>
                            <span class="confirm-val">${asalSekolah}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">NISN</span>
                            <span class="confirm-val">${nisnVal}</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Alamat & Lokasi -->
                <div class="confirm-section-card">
                    <div class="confirm-section-title">
                        <span><i class="fa-solid fa-house-user"></i> 2. Alamat & Zonasi Tempat Tinggal</span>
                        ${(lat && lng) ? '<span style="font-size:11px; color:#15803d; font-weight:700;"><i class="fa-solid fa-circle-check"></i> GPS Terdeteksi</span>' : '<span style="font-size:11px; color:#b45309;"><i class="fa-solid fa-circle-info"></i> GPS Manual</span>'}
                    </div>
                    <div class="confirm-grid">
                        <div class="confirm-item full-width">
                            <span class="confirm-label">Alamat Lengkap</span>
                            <span class="confirm-val">${alamat} (RT ${rt} / RW ${rw}${dusun !== '-' ? ', ' + dusun : ''})</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Kelurahan / Kecamatan</span>
                            <span class="confirm-val">${kel}, Kec. ${kec}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Kab/Kota & Provinsi</span>
                            <span class="confirm-val">${kab}, ${prov}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Status Tempat Tinggal</span>
                            <span class="confirm-val">${tempatTinggal}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Transportasi</span>
                            <span class="confirm-val">${transportasi}</span>
                        </div>
                        ${(lat && lng) ? `
                        <div class="confirm-item full-width">
                            <span class="confirm-label">Koordinat GPS</span>
                            <span class="confirm-val" style="font-size:11.5px; color:#0369a1; font-family:'JetBrains Mono',monospace;">Lat: ${lat}, Lng: ${lng}</span>
                        </div>` : ''}
                    </div>
                </div>

                <!-- 3. Orang Tua & Kontak -->
                <div class="confirm-section-card">
                    <div class="confirm-section-title">
                        <span><i class="fa-solid fa-people-roof"></i> 3. Data Orang Tua & Kontak</span>
                    </div>
                    <div class="confirm-grid">
                        <div class="confirm-item">
                            <span class="confirm-label">Ibu Kandung</span>
                            <span class="confirm-val">${namaIbu}</span>
                            <span style="font-size:11px; color:var(--slate-500);">${pekIbu} &bull; NIK: ${nikIbu}</span>
                        </div>
                        <div class="confirm-item">
                            <span class="confirm-label">Ayah Kandung</span>
                            <span class="confirm-val">${namaAyah}</span>
                            <span style="font-size:11px; color:var(--slate-500);">${pekAyah} &bull; NIK: ${nikAyah}</span>
                        </div>
                        <div class="confirm-item full-width">
                            <span class="confirm-label">WhatsApp Utama (Notifikasi)</span>
                            <span class="confirm-val" style="color:#15803d; font-size:14px; font-weight:800;">
                                <i class="fa-brands fa-whatsapp"></i> ${noWa}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 4. Berkas Pendukung -->
                <div class="confirm-section-card">
                    <div class="confirm-section-title">
                        <span><i class="fa-solid fa-folder-closed"></i> 4. Status Berkas Dokumen</span>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        <div class="file-status-row">
                            <span class="file-status-name"><i class="fa-solid fa-users" style="color:var(--primary);"></i> 1. Kartu Keluarga (KK)</span>
                            <span class="file-badge-pill ${fileKk.isOk ? 'success' : 'warning'}">
                                <i class="fa-solid ${fileKk.isOk ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i> ${fileKk.text}
                            </span>
                        </div>
                        <div class="file-status-row">
                            <span class="file-status-name"><i class="fa-solid fa-certificate" style="color:var(--primary);"></i> 2. Akta Kelahiran</span>
                            <span class="file-badge-pill ${fileAkta.isOk ? 'success' : 'warning'}">
                                <i class="fa-solid ${fileAkta.isOk ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i> ${fileAkta.text}
                            </span>
                        </div>
                        <div class="file-status-row">
                            <span class="file-status-name"><i class="fa-solid fa-id-card" style="color:var(--primary);"></i> 3. KTP Ayah Kandung</span>
                            <span class="file-badge-pill ${fileKtpAyah.isOk ? 'success' : 'warning'}">
                                <i class="fa-solid ${fileKtpAyah.isOk ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i> ${fileKtpAyah.text}
                            </span>
                        </div>
                        <div class="file-status-row">
                            <span class="file-status-name"><i class="fa-solid fa-id-card" style="color:var(--primary);"></i> 4. KTP Ibu Kandung</span>
                            <span class="file-badge-pill ${fileKtpIbu.isOk ? 'success' : 'warning'}">
                                <i class="fa-solid ${fileKtpIbu.isOk ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i> ${fileKtpIbu.text}
                            </span>
                        </div>
                        ${fileDapodik.isOk ? `
                        <div class="file-status-row">
                            <span class="file-status-name"><i class="fa-solid fa-file-image" style="color:var(--primary);"></i> 5. Profil Dapodik/EMIS</span>
                            <span class="file-badge-pill success">
                                <i class="fa-solid fa-circle-check"></i> ${fileDapodik.text}
                            </span>
                        </div>` : ''}
                    </div>
                </div>
            `;

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeFinalConfirmModal() {
            const modal = document.getElementById('finalConfirmModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        async function executeFinalSubmit() {
            const btnExec = document.getElementById('btnExecuteFinalSubmit');
            const btnCancel = document.getElementById('btnCancelFinalSubmit');
            const errBanner = document.getElementById('confirmErrorBanner');
            const errText = document.getElementById('confirmErrorText');
            const modalBody = document.getElementById('finalConfirmModalBody');
            const modalFooter = document.getElementById('finalConfirmModalFooter');

            if (errBanner) errBanner.style.display = 'none';

            if (btnExec) {
                btnExec.disabled = true;
                btnExec.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan & Mengirim Data...';
            }
            if (btnCancel) btnCancel.disabled = true;

            const form = document.getElementById('biodataForm');
            const formData = new FormData(form);
            formData.append('is_ajax', '1');

            try {
                const response = await fetch(SUBMIT_URL, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const res = await response.json();
                if (res.success) {
                    if (modalFooter) modalFooter.style.display = 'none';
                    if (modalBody) {
                        modalBody.innerHTML = `
                            <div style="text-align:center; padding:36px 16px;">
                                <div class="modal-icon-badge success" style="margin:0 auto 18px; width:72px; height:72px; font-size:32px;">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <h3 style="color:#15803d; font-size:19px; font-weight:800; margin-bottom:8px;">
                                    Alhamdulillah! Formulir Berhasil Dikirim
                                </h3>
                                <p style="color:#166534; font-size:13.5px; line-height:1.6; margin-bottom:0;">
                                    Seluruh isian data dan dokumen telah berhasil disimpan secara resmi ke sistem Panitia SPMB SIT Bina Insan Palu.<br>
                                    <span style="display:inline-block; margin-top:10px; color:var(--slate-500); font-size:12px;">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Mengalihkan ke tanda bukti pendaftaran...
                                    </span>
                                </p>
                            </div>
                        `;
                    }
                    setTimeout(() => {
                        window.location.href = res.redirect || window.location.href;
                    }, 1200);
                } else {
                    if (errBanner) {
                        errBanner.style.display = 'flex';
                        if (errText) errText.innerText = res.message || 'Gagal mengirim formulir.';
                    }
                    if (btnExec) {
                        btnExec.disabled = false;
                        btnExec.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Ya, Simpan & Kirim Final';
                    }
                    if (btnCancel) btnCancel.disabled = false;
                }
            } catch (err) {
                console.error("Submit error:", err);
                if (errBanner) {
                    errBanner.style.display = 'flex';
                    if (errText) errText.innerText = 'Terjadi gangguan koneksi internet saat mengirim. Silakan coba kembali.';
                }
                if (btnExec) {
                    btnExec.disabled = false;
                    btnExec.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Ya, Simpan & Kirim Final';
                }
                if (btnCancel) btnCancel.disabled = false;
            }
        }

        // 9. Sistem Validasi Kelengkapan Seluruh Kolom Wajib
        const REQUIRED_CONFIG = [
            // Tahap 1: Data Siswa
            { step: 1, stepName: 'Tahap 1: Data Diri Calon Siswa', name: 'nama_lengkap', label: 'Nama Lengkap Calon Siswa' },
            { step: 1, stepName: 'Tahap 1: Data Diri Calon Siswa', name: 'jenis_kelamin', label: 'Jenis Kelamin' },
            { step: 1, stepName: 'Tahap 1: Data Diri Calon Siswa', name: 'nik', label: 'NIK / No. KITAS Anak' },
            { step: 1, stepName: 'Tahap 1: Data Diri Calon Siswa', name: 'no_kk', label: 'No. Kartu Keluarga (KK)' },
            { step: 1, stepName: 'Tahap 1: Data Diri Calon Siswa', name: 'tempat_lahir', label: 'Tempat Lahir' },
            { step: 1, stepName: 'Tahap 1: Data Diri Calon Siswa', name: 'tanggal_lahir', label: 'Tanggal Lahir' },
            { step: 1, stepName: 'Tahap 1: Data Diri Calon Siswa', name: 'no_akta_lahir', label: 'No. Registrasi Akta Kelahiran' },
            { step: 1, stepName: 'Tahap 1: Data Diri Calon Siswa', name: 'anak_ke', label: 'Anak ke-berapa' },

            // Tahap 3: Tempat Tinggal & Zonasi
            { step: 3, stepName: 'Tahap 3: Tempat Tinggal & Zonasi', name: 'alamat', label: 'Alamat Jalan Lengkap' },
            { step: 3, stepName: 'Tahap 3: Tempat Tinggal & Zonasi', name: 'kelurahan', label: 'Kelurahan / Desa' },
            { step: 3, stepName: 'Tahap 3: Tempat Tinggal & Zonasi', name: 'kecamatan', label: 'Kecamatan' },
            { step: 3, stepName: 'Tahap 3: Tempat Tinggal & Zonasi', name: 'kabupaten', label: 'Kabupaten / Kota' },
            { step: 3, stepName: 'Tahap 3: Tempat Tinggal & Zonasi', name: 'provinsi', label: 'Provinsi' },
            { step: 3, stepName: 'Tahap 3: Tempat Tinggal & Zonasi', name: 'tempat_tinggal', label: 'Status Tempat Tinggal' },
            { step: 3, stepName: 'Tahap 3: Tempat Tinggal & Zonasi', name: 'moda_transportasi', label: 'Moda Transportasi ke Sekolah' },

            // Tahap 4: Orang Tua & Kontak
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'nama_ibu', label: 'Nama Lengkap Ibu Kandung' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'nik_ibu', label: 'NIK Ibu Kandung' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'tahun_lahir_ibu', label: 'Tahun Lahir Ibu' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'pendidikan_ibu', label: 'Pendidikan Terakhir Ibu' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'pekerjaan_ibu', label: 'Pekerjaan Ibu' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'penghasilan_ibu', label: 'Penghasilan Bulanan Ibu' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'nama_ayah', label: 'Nama Lengkap Ayah Kandung' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'nik_ayah', label: 'NIK Ayah Kandung' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'tahun_lahir_ayah', label: 'Tahun Lahir Ayah' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'pendidikan_ayah', label: 'Pendidikan Terakhir Ayah' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'pekerjaan_ayah', label: 'Pekerjaan Ayah' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'penghasilan_ayah', label: 'Penghasilan Bulanan Ayah' },
            { step: 4, stepName: 'Tahap 4: Data Orang Tua & Kontak', name: 'no_wa', label: 'Nomor WhatsApp Utama' },

            // Tahap 5: Berkas Pendukung & Keamanan
            { step: 5, stepName: 'Tahap 5: Berkas Pendukung & Keamanan', isFile: true, name: 'berkas_kk', label: 'Foto Kartu Keluarga (KK)' },
            { step: 5, stepName: 'Tahap 5: Berkas Pendukung & Keamanan', isFile: true, name: 'berkas_akta', label: 'Foto Akta Kelahiran' },
            { step: 5, stepName: 'Tahap 5: Berkas Pendukung & Keamanan', isFile: true, name: 'berkas_ktp_ayah', label: 'Foto KTP Ayah Kandung' },
            { step: 5, stepName: 'Tahap 5: Berkas Pendukung & Keamanan', isFile: true, name: 'berkas_ktp_ibu', label: 'Foto KTP Ibu Kandung' }
        ];

        let currentMissingFields = [];

        function validateAllRequiredFields() {
            const missing = [];

            REQUIRED_CONFIG.forEach(cfg => {
                if (cfg.isFile) {
                    const input = document.querySelector(`input[name="${cfg.name}"]`);
                    const hasNewFile = input && input.files && input.files.length > 0;
                    const hasSaved = EXISTING_FILES[cfg.name];
                    if (!hasNewFile && !hasSaved) {
                        missing.push(cfg);
                    }
                } else {
                    const el = document.querySelector(`[name="${cfg.name}"]`);
                    const val = el ? el.value.trim() : '';
                    if (!val) {
                        missing.push(cfg);
                    }
                }
            });

            return missing;
        }

        function showIncompleteDataModal(missingList) {
            currentMissingFields = missingList;
            const modal = document.getElementById('incompleteDataModal');
            const listEl = document.getElementById('incompleteListContent');
            const subTitle = document.getElementById('incompleteSubtitle');
            if (!modal || !listEl) return;

            if (subTitle) {
                subTitle.textContent = `Terdapat ${missingList.length} data wajib atau berkas yang belum diisi / diunggah.`;
            }

            // Kelompokkan per Tahap
            const groups = {};
            missingList.forEach(item => {
                if (!groups[item.step]) {
                    groups[item.step] = {
                        stepName: item.stepName,
                        step: item.step,
                        items: []
                    };
                }
                groups[item.step].items.push(item);
            });

            let html = '';
            for (const stepKey in groups) {
                const grp = groups[stepKey];
                html += `
                    <div class="incomplete-tab-box">
                        <div class="incomplete-tab-title">
                            <span><i class="fa-solid fa-list-check"></i> ${grp.stepName} (${grp.items.length} belum diisi)</span>
                            <button type="button" class="btn-jump-field" onclick="goToMissingField(${grp.step}, '${grp.items[0].name}')">
                                Buka ${grp.stepName.split(':')[0]} <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                        <ul class="incomplete-items-list">
                `;
                grp.items.forEach(item => {
                    html += `
                        <li class="incomplete-item">
                            <span><i class="fa-solid ${item.isFile ? 'fa-file-arrow-up' : 'fa-circle-dot'}" style="color:#ea580c; font-size:11px; margin-right:6px;"></i> ${item.label}</span>
                            <button type="button" class="btn-jump-field" style="background:#fff; color:#c2410c; border:1px solid #fed7aa;" onclick="goToMissingField(${item.step}, '${item.name}')">
                                Isi Kolom <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </button>
                        </li>
                    `;
                });
                html += `
                        </ul>
                    </div>
                `;
            }

            listEl.innerHTML = html;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeIncompleteModal() {
            const modal = document.getElementById('incompleteDataModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function goToMissingField(step, fieldName) {
            closeIncompleteModal();
            switchTab(step);

            setTimeout(() => {
                const targetEl = document.querySelector(`[name="${fieldName}"]`);
                if (targetEl) {
                    const uploadCard = targetEl.closest('.upload-card');
                    if (uploadCard) {
                        uploadCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        uploadCard.classList.add('field-missing-highlight');
                        setTimeout(() => uploadCard.classList.remove('field-missing-highlight'), 4500);
                    } else {
                        targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        targetEl.focus();
                        targetEl.classList.add('field-missing-highlight');
                        targetEl.addEventListener('input', () => targetEl.classList.remove('field-missing-highlight'), { once: true });
                        targetEl.addEventListener('change', () => targetEl.classList.remove('field-missing-highlight'), { once: true });
                        setTimeout(() => targetEl.classList.remove('field-missing-highlight'), 4500);
                    }
                }
            }, 250);
        }

        function goToFirstMissingField() {
            if (currentMissingFields && currentMissingFields.length > 0) {
                const first = currentMissingFields[0];
                goToMissingField(first.step, first.name);
            } else {
                closeIncompleteModal();
            }
        }

        // 10. Tombol Trigger Final Submit (Membuka Modal Konfirmasi)
        function submitFinalForm() {
            const chk = document.getElementById('persetujuanCheckbox');
            if (!chk || !chk.checked) {
                openPrivacyModal();
                showToast("Silakan baca dan setujui lembar pernyataan keamanan data terlebih dahulu.", "info");
                return;
            }

            // Validasi apakah seluruh kolom wajib dan berkas sudah lengkap
            const missing = validateAllRequiredFields();
            if (missing.length > 0) {
                showIncompleteDataModal(missing);
                return;
            }

            // Buka modal kustom konfirmasi data jika seluruh data sudah lengkap
            openFinalConfirmModal();
        }

        // 11. Toast Notification Helper
        function showToast(msg, type = "success") {
            const toast = document.getElementById('toastNotif');
            const text = document.getElementById('toastText');
            if (!toast || !text) return;

            text.innerText = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3500);
        }
    </script>
</body>
</html>
