<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="E-Arsip — Sistem Manajemen Arsip Elektronik">
    <title>{{ $title ?? 'Login' }} — E-Arsip</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-void: #090e17;
            --bg-surface: rgba(17, 24, 39, 0.75);
            --bg-input: rgba(15, 23, 42, 0.85);
            --border-subtle: rgba(255, 255, 255, 0.1);
            --border-focus: #00E5FF;
            --accent-primary: #00E5FF;
            --accent-glow: rgba(0, 229, 255, 0.28);
            --accent-secondary: #3b82f6;
            --gradient-brand: linear-gradient(135deg, #00E5FF 0%, #3b82f6 50%, #8b5cf6 100%);
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --danger: #f43f5e;
            --danger-bg: rgba(244, 63, 94, 0.12);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-display: 'Space Grotesk', sans-serif;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-void);
            color: var(--text-main);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
            position: relative;
            overflow-x: hidden;
            background-image: 
                radial-gradient(ellipse 65% 55% at 50% -10%, rgba(0, 229, 255, 0.15) 0%, transparent 60%),
                radial-gradient(ellipse 50% 50% at 85% 90%, rgba(59, 130, 246, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 15% 85%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
        }

        /* Ambient animated glow background */
        .ambient-grid {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.55;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            margin: auto;
            position: relative;
            z-index: 10;
        }

        /* Brand cluster header */
        .auth-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .auth-badge-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(0, 229, 255, 0.2), rgba(59, 130, 246, 0.1));
            border: 1px solid rgba(0, 229, 255, 0.35);
            box-shadow: 0 0 24px rgba(0, 229, 255, 0.25);
            font-size: 1.75rem;
            margin-bottom: 14px;
        }

        .auth-header h1 {
            font-family: var(--font-display);
            font-size: clamp(1.65rem, 5vw, 2.15rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            background: var(--gradient-brand);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .auth-header p {
            color: var(--text-secondary);
            font-size: clamp(0.825rem, 2.5vw, 0.925rem);
            font-weight: 500;
            line-height: 1.4;
        }

        /* Glassmorphism Card */
        .auth-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
            padding: clamp(20px, 6vw, 36px);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.45),
                0 0 0 1px rgba(255, 255, 255, 0.05),
                0 0 35px rgba(0, 229, 255, 0.08);
            position: relative;
            overflow: hidden;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--gradient-brand);
        }

        /* Form elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 7px;
            letter-spacing: 0.02em;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .form-input {
            width: 100%;
            min-height: 46px;
            padding: 12px 14px 12px 42px;
            background: var(--bg-input);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            color: var(--text-main);
            font-size: 0.925rem;
            font-family: inherit;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .form-input.has-toggle {
            padding-right: 44px;
        }

        .form-input:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--accent-glow);
            background: rgba(15, 23, 42, 0.98);
        }

        .input-wrapper:focus-within .input-icon {
            color: var(--accent-primary);
        }

        .password-toggle-btn {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: var(--text-muted);
            padding: 8px;
            cursor: pointer;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }

        .password-toggle-btn:hover {
            color: var(--text-main);
        }

        .form-error {
            color: var(--danger);
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Primary Action Button */
        .btn-primary {
            width: 100%;
            min-height: 48px;
            padding: 12px 20px;
            background: var(--gradient-brand);
            color: #0b111e;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: inherit;
            border: none;
            border-radius: var(--radius-md);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 18px rgba(0, 229, 255, 0.3);
            margin-top: 6px;
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-1.5px);
            box-shadow: 0 8px 25px rgba(0, 229, 255, 0.45);
            filter: brightness(1.06);
        }

        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-primary:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        /* Footer */
        .auth-footer {
            text-align: center;
            margin-top: 22px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s ease;
        }

        .auth-footer a:hover {
            text-decoration: underline;
            opacity: 0.85;
        }

        .copyright-tag {
            text-align: center;
            margin-top: 28px;
            font-size: 0.75rem;
            color: var(--text-muted);
            letter-spacing: 0.02em;
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 480px) {
            body {
                padding: 16px 12px;
            }

            .auth-container {
                max-width: 100%;
            }

            .auth-card {
                padding: 24px 18px;
                border-radius: 16px;
            }

            .auth-header {
                margin-bottom: 22px;
            }

            .auth-badge-icon {
                width: 50px;
                height: 50px;
                font-size: 1.5rem;
                margin-bottom: 10px;
            }

            .form-input {
                font-size: 16px; /* Prevents auto-zoom on iOS Safari */
            }
        }
    </style>

    @livewireStyles
</head>
<body>
    <div class="ambient-grid"></div>

    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-badge-icon">📂</div>
            <h1>E-Arsip</h1>
            <p>Sistem Manajemen Arsip Digital & Surat</p>
        </div>

        {{ $slot }}

        <div class="copyright-tag">
            &copy; {{ date('Y') }} Suku Dinas Sumber Daya Air &bull; E-Arsip
        </div>
    </div>

    @livewireScripts
</body>
</html>
