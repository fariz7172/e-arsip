<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="E-Arsip — Sistem Manajemen Arsip Elektronik">
    <title>{{ $title ?? 'E-Arsip' }} — Arsip Digital</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <!-- Compressor.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ===========================
           COLOR SYSTEM
           60% #2F2FE4 — Primary Blue
           30% #F5F5F5 — Light Surface
           10% #162E93 — Dark Blue
        =========================== */
        :root {
            /* Primary Palette */
            --primary:          #2F2FE4;
            --primary-hover:    #2424c4;
            --primary-light:    #E8E8FF;
            --primary-lighter:  #F0F0FF;
            --primary-glow:     rgba(47, 47, 228, 0.18);
            --dark-blue:        #162E93;
            --dark-blue-hover:  #0f2070;

            /* Surfaces */
            --bg-app:           #F0F2F7;
            --bg-surface:       #F5F5F5;
            --bg-card:          #FFFFFF;
            --bg-card-hover:    #FAFAFF;
            --bg-input:         #FFFFFF;

            /* Borders */
            --border-color:     #E2E4EE;
            --border-focus:     #2F2FE4;

            /* Text */
            --text-primary:     #0F1123;
            --text-secondary:   #4B5280;
            --text-muted:       #8E95B5;
            --text-on-primary:  #FFFFFF;

            /* Semantic */
            --success:          #10B981;
            --success-bg:       #ECFDF5;
            --success-border:   rgba(16, 185, 129, 0.25);
            --warning:          #F59E0B;
            --warning-bg:       #FFFBEB;
            --warning-border:   rgba(245, 158, 11, 0.25);
            --danger:           #EF4444;
            --danger-bg:        #FEF2F2;
            --danger-border:    rgba(239, 68, 68, 0.25);
            --info:             #3B82F6;
            --info-bg:          #EFF6FF;
            --info-border:      rgba(59, 130, 246, 0.25);

            /* Gradients */
            --gradient-sidebar: linear-gradient(175deg, #162E93 0%, #2F2FE4 100%);
            --gradient-primary: linear-gradient(135deg, #2F2FE4, #162E93);
            --gradient-accent:  linear-gradient(135deg, #4F46E5, #2F2FE4);
            --gradient-success: linear-gradient(135deg, #10B981, #059669);
            --gradient-warning: linear-gradient(135deg, #F59E0B, #D97706);
            --gradient-danger:  linear-gradient(135deg, #EF4444, #DC2626);

            /* Shadows */
            --shadow-xs:  0 1px 2px rgba(15,17,35,0.06);
            --shadow-sm:  0 2px 8px rgba(15,17,35,0.08);
            --shadow-md:  0 4px 16px rgba(15,17,35,0.10);
            --shadow-lg:  0 8px 32px rgba(15,17,35,0.12);
            --shadow-xl:  0 16px 48px rgba(15,17,35,0.16);
            --shadow-primary: 0 4px 20px rgba(47, 47, 228, 0.30);
            --shadow-primary-lg: 0 8px 32px rgba(47, 47, 228, 0.35);

            /* Radius */
            --radius-xs:  4px;
            --radius-sm:  8px;
            --radius-md:  12px;
            --radius-lg:  16px;
            --radius-xl:  20px;
            --radius-2xl: 24px;
            --radius-full: 9999px;

            /* Sidebar */
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --topbar-height: 64px;

            /* Transitions */
            --transition-fast: all 0.15s ease;
            --transition-base: all 0.25s ease;
            --transition-slow: all 0.35s cubic-bezier(0.4,0,0.2,1);
        }

        /* ===========================
           BASE
        =========================== */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-app);
            color: var(--text-primary);
            min-height: 100vh;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ===========================
           APP LAYOUT
        =========================== */
        .app-layout {
            display: flex;
            min-height: 100vh;
        }

        /* ===========================
           SIDEBAR
        =========================== */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--gradient-sidebar);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: var(--transition-slow);
            overflow: hidden;
            box-shadow: 4px 0 24px rgba(22, 46, 147, 0.25);
        }

        /* Sidebar subtle pattern overlay */
        .sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.06) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.04) 0%, transparent 50%);
            pointer-events: none;
        }

        /* ---- Brand ---- */
        .sidebar-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            position: relative;
            flex-shrink: 0;
        }

        .sidebar-brand-inner {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            backdrop-filter: blur(8px);
        }

        .sidebar-logo svg {
            color: white;
            width: 22px;
            height: 22px;
        }

        .sidebar-brand-text .brand-name {
            font-size: 1.1rem;
            font-weight: 800;
            color: white;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .sidebar-brand-text .brand-sub {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.55);
            font-weight: 500;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-top: 1px;
        }

        /* ---- Sidebar Toggle (collapse) ---- */
        .sidebar-collapse-btn {
            position: absolute;
            top: 20px;
            right: -14px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: white;
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            transition: var(--transition-base);
            z-index: 10;
            color: var(--primary);
        }

        .sidebar-collapse-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            transform: scale(1.1);
        }

        .sidebar-collapse-btn svg {
            width: 14px;
            height: 14px;
            transition: var(--transition-base);
        }

        /* ---- Nav ---- */
        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.2) transparent;
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

        .nav-section {
            margin-bottom: 8px;
        }

        .nav-section-title {
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(255,255,255,0.4);
            padding: 8px 12px 6px;
            white-space: nowrap;
            overflow: hidden;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: var(--radius-md);
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: var(--transition-base);
            margin-bottom: 2px;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .nav-link .nav-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: var(--transition-base);
            background: transparent;
        }

        .nav-link .nav-icon svg {
            width: 18px;
            height: 18px;
        }

        .nav-link .nav-label {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-link .nav-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: var(--radius-full);
            flex-shrink: 0;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.10);
            color: white;
        }

        .nav-link:hover .nav-icon {
            background: rgba(255,255,255,0.12);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.18);
            color: white;
            font-weight: 600;
        }

        .nav-link.active .nav-icon {
            background: rgba(255,255,255,0.22);
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: white;
            border-radius: 0 3px 3px 0;
        }

        .nav-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 12px 0;
        }

        /* ---- Sidebar Footer / User ---- */
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,0.12);
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 8px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: var(--transition-base);
        }

        .sidebar-user:hover {
            background: rgba(255,255,255,0.10);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            background: rgba(255,255,255,0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: white;
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .user-info-text {
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }

        .user-name {
            font-size: 0.825rem;
            font-weight: 600;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .user-role {
            font-size: 0.68rem;
            color: rgba(255,255,255,0.5);
            text-transform: capitalize;
        }

        .logout-form {
            flex-shrink: 0;
        }

        .logout-btn {
            background: rgba(255,255,255,0.12);
            border: none;
            cursor: pointer;
            font-family: inherit;
            color: rgba(255,255,255,0.7);
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-base);
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            color: #fca5a5;
        }

        .logout-btn svg {
            width: 16px;
            height: 16px;
        }

        /* ===========================
           MAIN WRAPPER
        =========================== */
        .main-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
            transition: var(--transition-slow);
        }

        /* ===========================
           TOPBAR
        =========================== */
        .topbar {
            height: var(--topbar-height);
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: var(--shadow-xs);
        }

        /* Mobile menu toggle */
        .topbar-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            align-items: center;
            justify-content: center;
            transition: var(--transition-fast);
            flex-shrink: 0;
        }

        .topbar-menu-btn:hover {
            background: var(--primary-light);
            color: var(--primary);
        }

        .topbar-menu-btn svg {
            width: 20px;
            height: 20px;
        }

        /* Page title on topbar (breadcrumb area) */
        .topbar-title-area {
            flex: 1;
            min-width: 0;
        }

        .topbar-page-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .topbar-breadcrumb a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition-fast);
        }

        .topbar-breadcrumb a:hover {
            color: var(--primary);
        }

        .topbar-breadcrumb .sep {
            opacity: 0.5;
        }

        /* Topbar right actions */
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .topbar-search {
            position: relative;
        }

        .topbar-search-input {
            width: 220px;
            padding: 8px 14px 8px 36px;
            background: var(--bg-surface);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-full);
            color: var(--text-primary);
            font-size: 0.825rem;
            font-family: inherit;
            outline: none;
            transition: var(--transition-base);
        }

        .topbar-search-input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px var(--primary-glow);
            width: 260px;
        }

        .topbar-search-input::placeholder { color: var(--text-muted); }

        .topbar-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 15px;
            height: 15px;
        }

        .topbar-icon-btn {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-md);
            background: var(--bg-surface);
            border: 1.5px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            transition: var(--transition-base);
            text-decoration: none;
            position: relative;
        }

        .topbar-icon-btn:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .topbar-icon-btn svg {
            width: 18px;
            height: 18px;
        }

        /* Topbar user avatar */
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 10px 4px 4px;
            border-radius: var(--radius-full);
            background: var(--bg-surface);
            border: 1.5px solid var(--border-color);
            cursor: pointer;
            transition: var(--transition-base);
        }

        .topbar-user:hover {
            border-color: var(--primary);
            background: var(--primary-lighter);
        }

        .topbar-avatar {
            width: 30px;
            height: 30px;
            border-radius: var(--radius-full);
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            color: white;
        }

        .topbar-user-name {
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* ===========================
           MAIN CONTENT
        =========================== */
        .main-content {
            flex: 1;
            padding: 28px 32px;
            min-width: 0;
        }

        /* ===========================
           FLASH MESSAGES
        =========================== */
        .flash-message {
            padding: 14px 18px;
            border-radius: var(--radius-md);
            margin-bottom: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
            border-left: 4px solid;
        }

        .flash-message svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .flash-success {
            background: var(--success-bg);
            color: #065F46;
            border-left-color: var(--success);
        }

        .flash-error {
            background: var(--danger-bg);
            color: #991B1B;
            border-left-color: var(--danger);
        }

        /* ===========================
           PAGE HEADER
        =========================== */
        .page-header {
            margin-bottom: 28px;
        }

        .page-header-inner {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 4px;
        }

        /* ===========================
           BREADCRUMB
        =========================== */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 20px;
            font-size: 0.825rem;
        }

        .breadcrumb a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition-fast);
        }

        .breadcrumb a:hover {
            color: var(--primary);
        }

        .breadcrumb .separator {
            color: var(--border-color);
        }

        .breadcrumb .current {
            color: var(--text-primary);
            font-weight: 600;
        }

        /* ===========================
           CARDS
        =========================== */
        .card {
            background: var(--bg-card);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            transition: var(--transition-base);
            box-shadow: var(--shadow-xs);
            min-width: 0;
        }

        .card:hover {
            border-color: rgba(47, 47, 228, 0.2);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        /* ===========================
           BUTTONS
        =========================== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: var(--radius-md);
            font-size: 0.85rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: var(--transition-base);
            border: none;
            text-decoration: none;
            white-space: nowrap;
            position: relative;
        }

        .btn svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: var(--shadow-primary);
        }

        .btn-primary:hover {
            box-shadow: var(--shadow-primary-lg);
            transform: translateY(-1px);
            opacity: 0.92;
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: var(--shadow-primary);
        }

        .btn-secondary {
            background: var(--bg-card);
            color: var(--text-secondary);
            border: 1.5px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
            box-shadow: 0 4px 12px rgba(239,68,68,0.25);
        }

        .btn-danger:hover {
            background: #DC2626;
            transform: translateY(-1px);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-secondary);
            border: none;
            padding: 8px 12px;
        }

        .btn-ghost:hover {
            background: var(--primary-light);
            color: var(--primary);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.78rem;
            border-radius: var(--radius-sm);
        }

        .btn-icon {
            padding: 8px;
            width: 36px;
            height: 36px;
            justify-content: center;
            border-radius: var(--radius-md);
        }

        .btn-icon svg { width: 16px; height: 16px; }

        /* ===========================
           FORM ELEMENTS
        =========================== */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            background: var(--bg-input);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: inherit;
            transition: var(--transition-base);
            outline: none;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: var(--text-muted);
        }

        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-error {
            color: var(--danger);
            font-size: 0.78rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* File Upload */
        .file-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-md);
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition-base);
            position: relative;
            background: var(--bg-surface);
        }

        .file-upload-area:hover {
            border-color: var(--primary);
            background: var(--primary-lighter);
        }

        .file-upload-area input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-icon { font-size: 2.5rem; margin-bottom: 12px; }
        .file-upload-text { color: var(--text-secondary); font-size: 0.9rem; }
        .file-upload-hint { color: var(--text-muted); font-size: 0.75rem; margin-top: 4px; }

        /* ===========================
           TABLES
        =========================== */
        .table-container {
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: var(--bg-surface);
        }

        thead th {
            text-align: left;
            padding: 12px 16px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            border-bottom: 1.5px solid var(--border-color);
            white-space: nowrap;
        }

        tbody td {
            padding: 14px 16px;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr {
            transition: var(--transition-fast);
        }

        tbody tr:hover {
            background: var(--primary-lighter);
        }

        /* ===========================
           BADGES
        =========================== */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: var(--radius-full);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .badge-accent  { background: var(--primary-light); color: var(--primary); }
        .badge-success { background: var(--success-bg); color: #065F46; border: 1px solid var(--success-border); }
        .badge-warning { background: var(--warning-bg); color: #92400E; border: 1px solid var(--warning-border); }
        .badge-danger  { background: var(--danger-bg); color: #991B1B; border: 1px solid var(--danger-border); }
        .badge-info    { background: var(--info-bg); color: #1E40AF; border: 1px solid var(--info-border); }
        .badge-dark    { background: #F1F5F9; color: var(--text-secondary); }

        /* ===========================
           STATS GRID
        =========================== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: var(--transition-base);
            box-shadow: var(--shadow-xs);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .stat-card:nth-child(1)::after { background: var(--gradient-primary); }
        .stat-card:nth-child(2)::after { background: var(--gradient-success); }
        .stat-card:nth-child(3)::after { background: var(--gradient-warning); }
        .stat-card:nth-child(4)::after { background: var(--gradient-danger); }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: rgba(47,47,228,0.2);
        }

        .stat-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .stat-icon-wrap {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon-wrap svg { width: 20px; height: 20px; }
        .stat-icon-1 { background: var(--primary-light); color: var(--primary); }
        .stat-icon-2 { background: var(--success-bg); color: var(--success); }
        .stat-icon-3 { background: var(--warning-bg); color: var(--warning); }
        .stat-icon-4 { background: var(--danger-bg); color: var(--danger); }

        .stat-value {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            letter-spacing: -0.04em;
        }

        /* ===========================
           GRID LAYOUTS
        =========================== */
        .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }

        /* ===========================
           BUNDLE CARDS
        =========================== */
        .bundle-card {
            background: var(--bg-card);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 22px;
            text-decoration: none;
            color: inherit;
            transition: var(--transition-base);
            display: block;
            box-shadow: var(--shadow-xs);
            position: relative;
            overflow: hidden;
        }

        .bundle-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: var(--transition-base);
        }

        .bundle-card:hover {
            border-color: rgba(47,47,228,0.3);
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }

        .bundle-card:hover::before {
            opacity: 1;
        }

        .bundle-card-icon {
            width: 46px;
            height: 46px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            background: var(--primary-light);
            color: var(--primary);
            flex-shrink: 0;
        }

        .bundle-card-icon svg { width: 22px; height: 22px; }

        .bundle-card-title {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--text-primary);
            line-height: 1.3;
        }

        .bundle-card-code {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-family: 'Courier New', monospace;
            font-weight: 600;
            background: var(--bg-surface);
            padding: 2px 8px;
            border-radius: var(--radius-xs);
            display: inline-block;
        }

        .bundle-card-stats {
            display: flex;
            gap: 12px;
            margin-top: 14px;
            padding-top: 14px;
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
        }

        .bundle-card-stat {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .bundle-card-stat strong {
            color: var(--text-primary);
            font-weight: 700;
        }

        /* ===========================
           KATEGORI ITEM
        =========================== */
        .kategori-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: var(--bg-card);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            margin-bottom: 8px;
            text-decoration: none;
            color: inherit;
            transition: var(--transition-base);
        }

        .kategori-item:hover {
            border-color: var(--primary);
            background: var(--primary-lighter);
            transform: translateX(4px);
        }

        .kategori-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kategori-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .kategori-icon svg { width: 18px; height: 18px; }
        .kategori-name { font-weight: 600; font-size: 0.9rem; color: var(--text-primary); }
        .kategori-desc { font-size: 0.78rem; color: var(--text-muted); margin-top: 1px; }

        /* ===========================
           DOKUMEN ITEM
        =========================== */
        .dokumen-item {
            background: var(--bg-card);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 18px;
            margin-bottom: 10px;
            transition: var(--transition-base);
        }

        .dokumen-item:hover {
            border-color: rgba(47,47,228,0.25);
            box-shadow: var(--shadow-sm);
        }

        .dokumen-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .dokumen-title { font-weight: 700; font-size: 0.95rem; color: var(--text-primary); }

        .dokumen-meta {
            color: var(--text-muted);
            font-size: 0.78rem;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 4px;
        }

        .file-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }

        .file-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            background: var(--bg-surface);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 0.72rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition-fast);
            font-weight: 500;
        }

        .file-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .file-chip svg { width: 13px; height: 13px; }

        /* ===========================
           FILE PREVIEW
        =========================== */
        .file-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 14px;
        }

        .file-preview-card {
            background: var(--bg-card);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            overflow: hidden;
            transition: var(--transition-base);
        }

        .file-preview-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .file-preview-thumb {
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-surface);
            color: var(--text-muted);
        }

        .file-preview-thumb svg { width: 36px; height: 36px; }
        .file-preview-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .file-preview-info { padding: 10px 12px; }
        .file-preview-name { font-size: 0.78rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .file-preview-size { font-size: 0.68rem; color: var(--text-muted); margin-top: 2px; }
        .file-preview-actions { display: flex; gap: 6px; margin-top: 8px; }

        /* ===========================
           SEARCH
        =========================== */
        .search-container {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            width: 16px;
            height: 16px;
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 11px 16px 11px 44px;
            background: var(--bg-card);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: var(--transition-base);
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .search-input::placeholder { color: var(--text-muted); }

        /* ===========================
           MODAL
        =========================== */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15,17,35,0.5);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 200;
            animation: fadeIn 0.2s ease;
            padding: 20px;
        }

        .modal {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 560px;
            padding: 32px;
            animation: slideUp 0.3s cubic-bezier(0.34,1.56,0.64,1);
            box-shadow: var(--shadow-xl);
        }

        .modal-lg { max-width: 800px; }
        .modal-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 20px; color: var(--text-primary); }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }

        /* ===========================
           EMPTY STATE
        =========================== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            width: 72px;
            height: 72px;
            border-radius: var(--radius-xl);
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .empty-state-icon svg { width: 36px; height: 36px; }
        .empty-state-text { font-size: 1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
        .empty-state-hint { font-size: 0.85rem; color: var(--text-muted); }

        /* ===========================
           LOADING
        =========================== */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-color);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        /* ===========================
           OVERLAY / SIDEBAR MOBILE
        =========================== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,17,35,0.4);
            backdrop-filter: blur(2px);
            z-index: 90;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* ===========================
           ANIMATIONS
        =========================== */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ===========================
           UTILITIES
        =========================== */
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        .mb-6 { margin-bottom: 24px; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-sm { font-size: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .text-muted { color: var(--text-muted); }
        .font-bold { font-weight: 700; }
        .w-full { width: 100%; }
        .hidden { display: none; }

        /* ===========================
           CUSTOM PAGINATION
        =========================== */
        .custom-pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 4px;
        }
        .custom-pagination .page-item {
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: var(--bg-card);
            color: var(--text-primary);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .custom-pagination button.page-item:hover {
            background: var(--primary-light);
            color: var(--primary);
            border-color: var(--primary);
        }
        .custom-pagination .page-item.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .custom-pagination .page-item.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f8fafc;
        }

        /* ===========================
           RESPONSIVE — TABLET (≤1024px)
        =========================== */
        @media (max-width: 1024px) {
            :root {
                --sidebar-width: 70px;
            }

            .sidebar-brand-text,
            .nav-section-title,
            .nav-link .nav-label,
            .nav-link .nav-badge,
            .sidebar-user .user-info-text {
                opacity: 0;
                width: 0;
                overflow: hidden;
                pointer-events: none;
            }

            .sidebar-brand-inner {
                justify-content: center;
            }

            .sidebar-logo {
                margin: 0;
            }

            .nav-link {
                justify-content: center;
                padding: 10px;
            }

            .nav-link .nav-icon {
                width: 40px;
                height: 40px;
            }

            .sidebar-collapse-btn {
                display: none;
            }

            .sidebar-user {
                justify-content: center;
                padding: 8px;
            }

            .logout-btn {
                display: none;
            }

            .topbar-menu-btn {
                display: flex;
            }

            .topbar-search-input {
                width: 160px;
            }

            .topbar-search-input:focus {
                width: 200px;
            }

            .main-content {
                padding: 20px 24px;
            }
        }

        /* ===========================
           RESPONSIVE — MOBILE (≤768px)
        =========================== */
        @media (max-width: 768px) {
            :root {
                --sidebar-width: 0px;
            }

            .sidebar {
                width: 270px;
                transform: translateX(-100%);
                box-shadow: none;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
                box-shadow: 8px 0 40px rgba(22,46,147,0.25);
            }

            /* Restore sidebar content on mobile */
            .sidebar.mobile-open .sidebar-brand-text,
            .sidebar.mobile-open .nav-section-title,
            .sidebar.mobile-open .nav-link .nav-label,
            .sidebar.mobile-open .nav-link .nav-badge,
            .sidebar.mobile-open .sidebar-user .user-info-text {
                opacity: 1;
                width: auto;
                pointer-events: auto;
            }

            .sidebar.mobile-open .sidebar-brand-inner {
                justify-content: flex-start;
            }

            .sidebar.mobile-open .nav-link {
                justify-content: flex-start;
                padding: 10px 12px;
            }

            .sidebar.mobile-open .sidebar-user {
                justify-content: flex-start;
                padding: 10px 8px;
            }

            .sidebar.mobile-open .logout-btn {
                display: flex;
            }

            .sidebar-overlay {
                display: block;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .topbar-menu-btn {
                display: flex;
            }

            .topbar {
                padding: 0 16px;
                gap: 10px;
            }

            .topbar-search {
                display: none;
            }

            .topbar-user-name {
                display: none;
            }

            .main-content {
                padding: 16px;
            }

            .grid-2,
            .grid-3,
            .grid-4 {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .page-header h1 {
                font-size: 1.4rem;
            }

            .stat-value {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .topbar-actions .topbar-icon-btn:not(:last-child) {
                display: none;
            }
        }
    </style>

    @livewireStyles
</head>
<body>
    <div class="app-layout">

        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

        <!-- ===================== SIDEBAR ===================== -->
        <aside class="sidebar" id="sidebar">
            <!-- Brand -->
            <div class="sidebar-brand">
                <div class="sidebar-brand-inner">
                    <div class="sidebar-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <div class="sidebar-brand-text">
                        <div class="brand-name">E-Arsip</div>
                        <div class="brand-sub">Arsip Elektronik</div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Menu Utama</div>

                    <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" title="Dashboard">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                            </svg>
                        </div>
                        <span class="nav-label">Dashboard</span>
                    </a>

                    <a href="/bundles" class="nav-link {{ request()->is('bundles*') ? 'active' : '' }}" title="Bundle Arsip">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                            </svg>
                        </div>
                        <span class="nav-label">Bundle Arsip</span>
                    </a>

                    <a href="/pencarian" class="nav-link {{ request()->is('pencarian') ? 'active' : '' }}" title="Pencarian">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                            </svg>
                        </div>
                        <span class="nav-label">Pencarian</span>
                    </a>

                    <a href="/payments" class="nav-link {{ request()->is('payments*') ? 'active' : '' }}" title="Tarik SPP/SPM">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2" ry="2"/><line x1="2" y1="10" x2="22" y2="10"/>
                            </svg>
                        </div>
                        <span class="nav-label">Tarik SPP/SPM</span>
                    </a>

                    <a href="/laporan-spn" class="nav-link {{ request()->is('laporan-spn*') ? 'active' : '' }}" title="Laporan SPN">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                            </svg>
                        </div>
                        <span class="nav-label">Laporan Berkas</span>
                    </a>
                    
                    <!-- Buku Agenda Dropdown -->
                    <div x-data="{ open: {{ request()->is('surat-masuk*') || request()->is('surat-keluar*') ? 'true' : 'false' }} }" style="margin-top: 16px;">
                        <button @click="open = !open" class="nav-link" style="width: 100%; border: none; background: transparent; cursor: pointer; padding-right: 12px; margin-bottom: 0;">
                            <div class="nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                                </svg>
                            </div>
                            <span class="nav-label" style="text-align: left;">Buku Agenda</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" style="transition: transform 0.2s; flex-shrink: 0;" :style="open ? 'transform: rotate(180deg);' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        
                        <div x-show="open" style="display: none; padding-left: 36px; margin-top: 2px;">
                            <a href="/surat-masuk" class="nav-link {{ request()->is('surat-masuk*') ? 'active' : '' }}" title="Surat Masuk" style="padding: 6px 12px; font-size: 0.8rem;">
                                <span style="margin-right: 8px; opacity: 0.5;">•</span>
                                <span class="nav-label">Surat Masuk</span>
                            </a>
        
                            <a href="/surat-keluar" class="nav-link {{ request()->is('surat-keluar*') ? 'active' : '' }}" title="Surat Keluar" style="padding: 6px 12px; font-size: 0.8rem;">
                                <span style="margin-right: 8px; opacity: 0.5;">•</span>
                                <span class="nav-label">Surat Keluar</span>
                            </a>
                        </div>
                    </div>

                    <!-- Lainnya Dropdown -->
                    <div x-data="{ open: {{ request()->is('pdf-converter*') || request()->is('pdf-compressor*') ? 'true' : 'false' }} }" style="margin-top: 8px;">
                        <button @click="open = !open" class="nav-link" style="width: 100%; border: none; background: transparent; cursor: pointer; padding-right: 12px; margin-bottom: 0;">
                            <div class="nav-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/>
                                </svg>
                            </div>
                            <span class="nav-label" style="text-align: left;">Lainnya</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" style="transition: transform 0.2s; flex-shrink: 0;" :style="open ? 'transform: rotate(180deg);' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        
                        <div x-show="open" style="display: none; padding-left: 36px; margin-top: 2px;">
                            <a href="/pdf-converter" class="nav-link {{ request()->is('pdf-converter*') ? 'active' : '' }}" title="Konverter PDF" style="padding: 6px 12px; font-size: 0.8rem;">
                                <span style="margin-right: 8px; opacity: 0.5;">•</span>
                                <span class="nav-label">Konverter PDF</span>
                            </a>
        
                            <a href="/pdf-compressor" class="nav-link {{ request()->is('pdf-compressor*') ? 'active' : '' }}" title="Kompresor PDF" style="padding: 6px 12px; font-size: 0.8rem;">
                                <span style="margin-right: 8px; opacity: 0.5;">•</span>
                                <span class="nav-label">Kompres PDF</span>
                            </a>
                        </div>
                    </div>
                </div>

                @auth
                @if(auth()->user()->isAdmin())
                <div class="nav-divider"></div>
                <div class="nav-section">
                    <div class="nav-section-title">Admin</div>
                    <a href="/bundles/create" class="nav-link {{ request()->is('bundles/create') ? 'active' : '' }}" title="Buat Bundle">
                        <div class="nav-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/>
                            </svg>
                        </div>
                        <span class="nav-label">Buat Bundle</span>
                    </a>
                </div>
                @endif
                @endauth
            </nav>

            <!-- User Footer -->
            @auth
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="user-info-text">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ auth()->user()->role }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="logout-btn" title="Logout">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </aside>

        <!-- ===================== MAIN WRAPPER ===================== -->
        <div class="main-wrapper" id="mainWrapper">

            <!-- ===== TOPBAR ===== -->
            <header class="topbar">
                <!-- Mobile/Tablet menu toggle -->
                <button class="topbar-menu-btn" onclick="toggleSidebar()" id="menuBtn" aria-label="Toggle menu">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>

                <!-- Page Title Area -->
                <div class="topbar-title-area">
                    <div class="topbar-page-title">{{ $title ?? 'E-Arsip' }}</div>
                </div>

                <!-- Right Actions -->
                <div class="topbar-actions">
                    <!-- Search (hidden on mobile) -->
                    <form action="/laporan-spn" method="GET" style="display: flex; gap: 12px; align-items: center;">
                        <div class="topbar-search">
                            <svg class="topbar-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                            </svg>
                            <input type="text" name="search" class="topbar-search-input" placeholder="Cari No SPM..." value="{{ request('search') }}">
                        </div>

                        <!-- Tombol Cari -->
                        <button type="submit" class="topbar-icon-btn" title="Cari Data" style="cursor: pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 18px; height: 18px; color: var(--text-secondary);">
                                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                            </svg>
                        </button>
                    </form>

                    @auth
                    <!-- User Pill -->
                    <div class="topbar-user">
                        <div class="topbar-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="topbar-user-name">{{ auth()->user()->name }}</span>
                    </div>
                    @endauth
                </div>
            </header>

            <!-- ===== MAIN CONTENT ===== -->
            <main class="main-content">
                @if(session('success'))
                    <div class="flash-message flash-success">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="flash-message flash-error">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Sidebar state (tablet: collapse/expand, mobile: drawer)
        let isMobileOpen = false;

        function toggleSidebar() {
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                isMobileOpen = !isMobileOpen;
                document.getElementById('sidebar').classList.toggle('mobile-open', isMobileOpen);
                document.getElementById('sidebarOverlay').classList.toggle('active', isMobileOpen);
            }
        }

        function closeSidebar() {
            isMobileOpen = false;
            document.getElementById('sidebar').classList.remove('mobile-open');
            document.getElementById('sidebarOverlay').classList.remove('active');
        }

        // Close sidebar on resize to desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && isMobileOpen) {
                closeSidebar();
            }
        });

        // Auto-dismiss flash messages
        setTimeout(() => {
            document.querySelectorAll('.flash-message').forEach(el => {
                el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-8px)';
                setTimeout(() => el.remove(), 500);
            });
        }, 4000);
    </script>
</body>
</html>
