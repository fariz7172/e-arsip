<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="404 Pemeliharaan Sistem E-Arsip SIDAJU - Akses dialihkan ke URL Resmi">
    <title>{{ $title ?? '404 Pemeliharaan Sistem — E-Arsip SIDAJU' }}</title>

    <!-- Google Fonts: Space Grotesk, Plus Jakarta Sans, JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- CDN Libraries: Three.js & Anime.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js"></script>

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg-void: #060B13;
            --bg-card: rgba(13, 24, 43, 0.72);
            --bg-card-inner: rgba(17, 34, 59, 0.55);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-glow: rgba(0, 229, 255, 0.35);
            --border-amber: rgba(245, 158, 11, 0.4);
            --accent-cyan: #00E5FF;
            --accent-sky: #38BDF8;
            --accent-amber: #F59E0B;
            --accent-emerald: #10B981;
            --text-primary: #FFFFFF;
            --text-secondary: #CBD5E1;
            --text-muted: #94A3B8;
            --text-dark: #64748B;
            --font-display: 'Space Grotesk', sans-serif;
            --font-body: 'Plus Jakarta Sans', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        body {
            font-family: var(--font-body);
            background-color: var(--bg-void);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            -webkit-font-smoothing: antialiased;
        }

        /* 3D Canvas Background */
        #webgl-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
            pointer-events: auto;
        }

        /* Ambient Lighting Overlay */
        .ambient-glow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 2;
            background: 
                radial-gradient(circle at 15% 20%, rgba(0, 229, 255, 0.09) 0%, transparent 45%),
                radial-gradient(circle at 85% 75%, rgba(245, 158, 11, 0.07) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(6, 11, 19, 0.2) 0%, rgba(6, 11, 19, 0.85) 100%);
        }

        /* Content Container */
        .page-wrapper {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 32px 24px;
        }

        @media (max-width: 768px) {
            .page-wrapper {
                padding: 20px 16px;
            }
        }
    </style>

    @livewireStyles
</head>
<body>
    <!-- Interactive Three.js 3D Background -->
    <canvas id="webgl-canvas"></canvas>
    
    <!-- Ambient Gradient Overlay -->
    <div class="ambient-glow"></div>

    <!-- Main View Content -->
    <div class="page-wrapper">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
