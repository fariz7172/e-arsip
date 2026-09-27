<?php

use Livewire\Volt\Component;

new #[\Livewire\Attributes\Layout('layouts.maintenance')] #[\Livewire\Attributes\Title('404 Pemeliharaan Sistem — E-Arsip SIDAJU')] class extends Component {
    // Komponen Maintenance 404 E-Arsip SIDAJU
}; ?>

<div class="maintenance-container">
    <style>
        /* Typography & Layout Tokens */
        .maintenance-container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 40px;
            position: relative;
        }

        /* Top Navigation Header */
        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.36);
        }

        .brand-cluster {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(0, 229, 255, 0.2), rgba(56, 189, 248, 0.05));
            border: 1px solid var(--border-glow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            box-shadow: 0 0 16px rgba(0, 229, 255, 0.25);
        }

        .brand-meta h2 {
            font-family: var(--font-display);
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: -0.01em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-meta p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .status-badge-cluster {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(245, 158, 11, 0.12);
            border: 1px solid var(--border-amber);
            color: var(--accent-amber);
            letter-spacing: 0.04em;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--accent-amber);
            box-shadow: 0 0 8px var(--accent-amber);
            animation: pulse-ring 2s infinite cubic-bezier(0.45, 0, 0.55, 1);
        }

        @keyframes pulse-ring {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(1.3); }
        }

        /* Hero Main Grid */
        .hero-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.95fr;
            gap: 32px;
            align-items: center;
        }

        @media (max-width: 980px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
        }

        /* Left Hero Card */
        .content-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 44px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
            position: relative;
            overflow: hidden;
        }

        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-cyan), var(--accent-amber), transparent);
        }

        .tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            background: rgba(0, 229, 255, 0.1);
            border: 1px solid rgba(0, 229, 255, 0.3);
            border-radius: 8px;
            color: var(--accent-cyan);
            font-family: var(--font-mono);
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }

        .giant-404 {
            font-family: var(--font-display);
            font-size: clamp(4.5rem, 9vw, 7.5rem);
            font-weight: 800;
            line-height: 0.95;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, #FFFFFF 0%, #E0F2FE 40%, #38BDF8 70%, #00E5FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 12px;
            text-shadow: 0 10px 30px rgba(0, 229, 255, 0.2);
            display: flex;
            align-items: baseline;
            gap: 16px;
        }

        .status-chip {
            font-family: var(--font-mono);
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent-amber);
            -webkit-text-fill-color: var(--accent-amber);
            background: rgba(245, 158, 11, 0.15);
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid rgba(245, 158, 11, 0.3);
            letter-spacing: 0.05em;
        }

        .main-headline {
            font-family: var(--font-display);
            font-size: clamp(1.4rem, 2.8vw, 1.85rem);
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.3;
            margin-bottom: 16px;
        }

        .body-desc {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.65;
            margin-bottom: 28px;
        }

        /* Official URL Highlight Box */
        .official-url-card {
            background: linear-gradient(145deg, rgba(14, 28, 48, 0.9), rgba(10, 20, 36, 0.95));
            border: 1.5px solid var(--accent-cyan);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 0 30px rgba(0, 229, 255, 0.15);
            margin-bottom: 28px;
            position: relative;
        }

        .url-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--font-mono);
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--accent-amber);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .url-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: rgba(6, 11, 19, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 18px;
            flex-wrap: wrap;
        }

        .target-url {
            font-family: var(--font-mono);
            font-size: clamp(0.95rem, 2vw, 1.15rem);
            font-weight: 700;
            color: var(--accent-cyan);
            letter-spacing: 0.02em;
            word-break: break-all;
        }

        .action-cluster {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn-launch {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #00E5FF 0%, #0284C7 100%);
            color: #060B13;
            font-family: var(--font-display);
            font-size: 0.95rem;
            font-weight: 700;
            border-radius: 12px;
            text-decoration: none;
            box-shadow: 0 4px 20px rgba(0, 229, 255, 0.35);
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
            cursor: pointer;
            border: none;
        }

        .btn-launch:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 28px rgba(0, 229, 255, 0.5);
            color: #000;
        }

        .btn-copy {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            color: var(--text-primary);
            font-family: var(--font-display);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-copy:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-cyan);
            color: var(--accent-cyan);
        }

        /* Right Telemetry Console Card */
        .telemetry-card {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 36px 32px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .telemetry-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .telemetry-title {
            font-family: var(--font-mono);
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .telemetry-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent-cyan);
            box-shadow: 0 0 10px var(--accent-cyan);
        }

        .data-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 10px 14px;
            background: var(--bg-card-inner);
            border: 1px solid rgba(255, 255, 255, 0.04);
            border-radius: 10px;
        }

        .data-label {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-val {
            font-family: var(--font-mono);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .data-badge-ok {
            color: var(--accent-emerald);
            background: rgba(16, 185, 129, 0.12);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        .data-badge-maint {
            color: var(--accent-amber);
            background: rgba(245, 158, 11, 0.12);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
        }

        /* Interactive 3D Guide Message */
        .interactive-guide {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 12px;
            background: rgba(0, 229, 255, 0.06);
            border: 1px solid rgba(0, 229, 255, 0.2);
            font-size: 0.8rem;
            color: var(--accent-sky);
        }

        /* Bottom Footer */
        .page-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            font-size: 0.8rem;
            color: var(--text-dark);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: var(--text-muted);
        }

        /* Floating Toast */
        #toast-notification {
            position: fixed;
            bottom: 32px;
            right: 32px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.95), rgba(5, 150, 105, 0.95));
            color: #FFFFFF;
            padding: 14px 22px;
            border-radius: 14px;
            font-family: var(--font-display);
            font-size: 0.9rem;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 20px rgba(16, 185, 129, 0.4);
            z-index: 1000;
            display: none;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>

    <!-- Top Navigation -->
    <header class="top-nav anime-fade">
        <div class="brand-cluster">
            <div class="brand-icon">📁</div>
            <div class="brand-meta">
                <h2>E-ARSIP SIDAJU <span style="font-size: 0.75rem; color: var(--accent-cyan); font-family: var(--font-mono); font-weight: 600;">v2.6</span></h2>
                <p>Subbagian Tata Usaha Suku Dinas Sumber Daya Air Kota Administrasi Jakarta Utara</p>
            </div>
        </div>

        <div class="status-badge-cluster">
            <div class="pill-badge">
                <span class="pulse-dot"></span>
                <span>SYSTEM MAINTENANCE</span>
            </div>
        </div>
    </header>

    <!-- Hero Asymmetric Grid -->
    <main class="hero-grid">
        <!-- Main Message Card -->
        <article class="content-card anime-card">
            <div class="tag-pill anime-element">
                <span>⚡</span>
                <span>STATUS 404 • INFRASTRUCTURE MIGRATION</span>
            </div>

            <div class="giant-404 anime-element">
                <span>404</span>
                <span class="status-chip">MAINTENANCE</span>
            </div>

            <h1 class="main-headline anime-element">
                Halaman Ini Sedang Dalam Pemeliharaan Sistem
            </h1>

            <p class="body-desc anime-element">
                Akses layanan aplikasi <strong>E-Arsip</strong> pada subdomain ini sedang dialihkan demi integrasi keamanan, optimalisasi basis data, dan konsolidasi ekosistem tunggal <strong>SIDAJU (Sistem Informasi Pengendalian Administrasi Kinerja)</strong>.
            </p>

            <!-- Required Official URL Card -->
            <div class="official-url-card anime-element">
                <div class="url-label">
                    <span>📢</span>
                    <span>Pemberitahuan Resmi Perpindahan Akses:</span>
                </div>
                <div style="font-size: 1.05rem; font-weight: 700; color: #FFFFFF; margin-bottom: 12px; font-family: var(--font-display);">
                    Untuk Akses Gunakan URL RESMI:
                </div>
                <div class="url-box">
                    <span class="target-url" id="official-url-text">https://e-arsip.sdaju.web.id/</span>
                    <button type="button" class="btn-copy" id="btn-copy-url" title="Salin URL ke Clipboard">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span>Salin URL</span>
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-cluster anime-element">
                <a href="https://e-arsip.sdaju.web.id/" target="_blank" rel="noopener noreferrer" class="btn-launch" id="btn-launch-url">
                    <span>Buka Portal Resmi E-Arsip</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="7" y1="17" x2="17" y2="7"></line>
                        <polyline points="7 7 17 7 17 17"></polyline>
                    </svg>
                </a>
            </div>
        </article>

        <!-- Right System Telemetry Card -->
        <aside class="telemetry-card anime-card">
            <div class="telemetry-header">
                <div class="telemetry-title">
                    <span class="telemetry-indicator"></span>
                    <span>TELEMETRI & INFORMASI PENGALIHAN</span>
                </div>
                <span style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--accent-cyan);">LIVE</span>
            </div>

            <div class="data-row">
                <span class="data-label">Portal Tujuan Resmi</span>
                <span class="data-val">
                    <span style="color: var(--accent-cyan);">e-arsip.sdaju.web.id</span>
                    <span class="data-badge-ok">ACTIVE</span>
                </span>
            </div>

            <div class="data-row">
                <span class="data-label">Status Server Saat Ini</span>
                <span class="data-val">
                    <span>Peralihan Layanan (404)</span>
                    <span class="data-badge-maint">MAINTENANCE</span>
                </span>
            </div>

            <div class="data-row">
                <span class="data-label">Protokol Keamanan</span>
                <span class="data-val">
                    <span>SSL/TLS 1.3 End-to-End</span>
                    <span class="data-badge-ok">SECURE</span>
                </span>
            </div>

            <div class="data-row">
                <span class="data-label">Instansi Penyelenggara</span>
                <span class="data-val" style="font-size: 0.8rem; line-height: 1.4;">
                    Subbag Tata Usaha Sudin SDA Jakut
                </span>
            </div>

            <div class="data-row">
                <span class="data-label">Modul Terintegrasi SIDAJU</span>
                <span class="data-val" style="font-size: 0.78rem; color: var(--text-secondary); line-height: 1.4;">
                    Inventory • E-SPP/SPM • E-Arsip • E-Reses • Asset Management
                </span>
            </div>

            <div class="interactive-guide">
                <span style="font-size: 1.1rem;">🖱️</span>
                <span>Objek 3D di latar belakang interaktif merespons gerakan kursor / mouse Anda.</span>
            </div>
        </aside>
    </main>

    <!-- Bottom Footer -->
    <footer class="page-footer anime-fade">
        <div class="footer-brand">
            <span>© 2026 E-Arsip SIDAJU • Pemerintah Provinsi DKI Jakarta</span>
        </div>
        <div style="font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-dark);">
            Sistem Informasi Pengendalian Administrasi Kinerja Subbag TU Sudin SDA Jakarta Utara
        </div>
    </footer>

    <!-- Toast Notification Element -->
    <div id="toast-notification">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span>URL Resmi Berhasil Disalin ke Clipboard!</span>
    </div>

    <!-- Scripts: 3D Scene (Three.js) & Choreographed Animations (Anime.js) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ================================================================
            // 1. CHOREOGRAPHED ENTRANCE WITH ANIME.JS
            // ================================================================
            if (typeof anime !== 'undefined') {
                const timeline = anime.timeline({
                    easing: 'easeOutExpo'
                });

                timeline
                    .add({
                        targets: '.top-nav',
                        translateY: [-30, 0],
                        opacity: [0, 1],
                        duration: 800
                    })
                    .add({
                        targets: '.anime-card',
                        translateY: [40, 0],
                        opacity: [0, 1],
                        duration: 900,
                        delay: anime.stagger(150)
                    }, '-=500')
                    .add({
                        targets: '.anime-element',
                        translateY: [25, 0],
                        opacity: [0, 1],
                        duration: 750,
                        delay: anime.stagger(80)
                    }, '-=600')
                    .add({
                        targets: '.page-footer',
                        opacity: [0, 1],
                        duration: 600
                    }, '-=400');

                // Subtle floating breathing animation for 404 tag
                anime({
                    targets: '.status-chip',
                    translateY: [-3, 3],
                    duration: 2200,
                    direction: 'alternate',
                    loop: true,
                    easing: 'easeInOutQuad'
                });
            }

            // ================================================================
            // 2. CLIPBOARD COPY INTERACTION & TOAST FEEDBACK
            // ================================================================
            const copyBtn = document.getElementById('btn-copy-url');
            const toast = document.getElementById('toast-notification');
            const urlText = 'https://e-arsip.sdaju.web.id/';

            if (copyBtn) {
                copyBtn.addEventListener('click', () => {
                    navigator.clipboard.writeText(urlText).then(() => {
                        // Trigger Anime.js button bounce
                        if (typeof anime !== 'undefined') {
                            anime({
                                targets: copyBtn,
                                scale: [1, 0.92, 1],
                                duration: 300,
                                easing: 'easeOutQuad'
                            });
                        }

                        // Show Toast Notification with Anime.js
                        if (toast) {
                            toast.style.display = 'flex';
                            if (typeof anime !== 'undefined') {
                                anime({
                                    targets: toast,
                                    translateY: [40, 0],
                                    opacity: [0, 1],
                                    duration: 400,
                                    easing: 'spring(1, 80, 10, 0)',
                                    complete: () => {
                                        setTimeout(() => {
                                            anime({
                                                targets: toast,
                                                translateY: [0, 40],
                                                opacity: [1, 0],
                                                duration: 400,
                                                easing: 'easeInQuad',
                                                complete: () => {
                                                    toast.style.display = 'none';
                                                }
                                            });
                                        }, 3000);
                                    }
                                });
                            } else {
                                setTimeout(() => { toast.style.display = 'none'; }, 3000);
                            }
                        }
                    }).catch(err => {
                        console.error('Gagal menyalin URL:', err);
                    });
                });
            }

            // ================================================================
            // 3. BESPOKE 3D WEBGL GRAPHICS (THREE.JS)
            // ================================================================
            const canvas = document.getElementById('webgl-canvas');
            if (canvas && typeof THREE !== 'undefined') {
                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 1000);
                camera.position.z = 24;

                const renderer = new THREE.WebGLRenderer({
                    canvas: canvas,
                    alpha: true,
                    antialias: true,
                    powerPreference: 'high-performance'
                });
                renderer.setSize(window.innerWidth, window.innerHeight);
                renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

                // 3D Lighting
                const ambientLight = new THREE.AmbientLight(0x0a1628, 1.8);
                scene.add(ambientLight);

                const dirLightCyan = new THREE.DirectionalLight(0x00e5ff, 2.5);
                dirLightCyan.position.set(12, 15, 10);
                scene.add(dirLightCyan);

                const pointLightAmber = new THREE.PointLight(0xf59e0b, 3.0, 40);
                pointLightAmber.position.set(-10, -8, 8);
                scene.add(pointLightAmber);

                // Central 3D Group
                const coreGroup = new THREE.Group();
                coreGroup.position.set(6, 0, 0); // Position slightly to the right side
                scene.add(coreGroup);

                // Responsive positioning for smaller screens
                function adjustCorePosition() {
                    if (window.innerWidth < 980) {
                        coreGroup.position.set(0, 4, -8);
                    } else {
                        coreGroup.position.set(5.5, 0.5, 0);
                    }
                }
                adjustCorePosition();

                // 1. Inner Faceted Icosahedron (Data Core)
                const coreGeometry = new THREE.IcosahedronGeometry(4.2, 1);
                const coreMaterial = new THREE.MeshStandardMaterial({
                    color: 0x092347,
                    emissive: 0x003355,
                    metalness: 0.85,
                    roughness: 0.25,
                    wireframe: false,
                    flatShading: true
                });
                const coreMesh = new THREE.Mesh(coreGeometry, coreMaterial);
                coreGroup.add(coreMesh);

                // 2. Wireframe Outer Cage
                const wireGeometry = new THREE.IcosahedronGeometry(4.35, 1);
                const wireMaterial = new THREE.MeshBasicMaterial({
                    color: 0x00e5ff,
                    wireframe: true,
                    transparent: true,
                    opacity: 0.45
                });
                const wireMesh = new THREE.Mesh(wireGeometry, wireMaterial);
                coreGroup.add(wireMesh);

                // 3. Dual Gyroscope Orbital Rings
                const ring1Geom = new THREE.TorusGeometry(6.2, 0.05, 16, 100);
                const ring1Mat = new THREE.MeshStandardMaterial({
                    color: 0x38bdf8,
                    emissive: 0x00e5ff,
                    emissiveIntensity: 0.4,
                    metalness: 0.9,
                    roughness: 0.1
                });
                const ring1 = new THREE.Mesh(ring1Geom, ring1Mat);
                ring1.rotation.x = Math.PI / 3;
                coreGroup.add(ring1);

                const ring2Geom = new THREE.TorusGeometry(7.2, 0.04, 16, 100);
                const ring2Mat = new THREE.MeshStandardMaterial({
                    color: 0xf59e0b,
                    emissive: 0xf59e0b,
                    emissiveIntensity: 0.3,
                    metalness: 0.9,
                    roughness: 0.1
                });
                const ring2 = new THREE.Mesh(ring2Geom, ring2Mat);
                ring2.rotation.y = Math.PI / 4;
                coreGroup.add(ring2);

                // 4. Floating Archival Document Shards
                const shardCount = 12;
                const shards = [];
                for (let i = 0; i < shardCount; i++) {
                    const shardGeom = new THREE.BoxGeometry(0.7, 1.0, 0.04);
                    const shardMat = new THREE.MeshStandardMaterial({
                        color: i % 2 === 0 ? 0x00e5ff : 0xf59e0b,
                        metalness: 0.7,
                        roughness: 0.3,
                        transparent: true,
                        opacity: 0.75
                    });
                    const shard = new THREE.Mesh(shardGeom, shardMat);
                    
                    const angle = (i / shardCount) * Math.PI * 2;
                    const radius = 7.8 + Math.random() * 2.5;
                    shard.position.set(
                        Math.cos(angle) * radius,
                        (Math.random() - 0.5) * 4.5,
                        Math.sin(angle) * radius
                    );
                    shard.rotation.set(Math.random() * Math.PI, Math.random() * Math.PI, 0);
                    coreGroup.add(shard);
                    shards.push({ mesh: shard, angle: angle, speed: 0.004 + Math.random() * 0.003, radius: radius });
                }

                // 5. Background Cyber Particle Field
                const particleCount = 650;
                const particleGeom = new THREE.BufferGeometry();
                const positions = new Float32Array(particleCount * 3);
                const colors = new Float32Array(particleCount * 3);

                for (let i = 0; i < particleCount * 3; i += 3) {
                    positions[i] = (Math.random() - 0.5) * 65;
                    positions[i + 1] = (Math.random() - 0.5) * 45;
                    positions[i + 2] = (Math.random() - 0.5) * 40;

                    // Cyber Cyan & Amber Particles
                    if (Math.random() > 0.3) {
                        colors[i] = 0.0;     // R
                        colors[i + 1] = 0.9; // G
                        colors[i + 2] = 1.0; // B
                    } else {
                        colors[i] = 0.96;   // R
                        colors[i + 1] = 0.62;// G
                        colors[i + 2] = 0.07;// B
                    }
                }

                particleGeom.setAttribute('position', new THREE.BufferAttribute(positions, 3));
                particleGeom.setAttribute('color', new THREE.BufferAttribute(colors, 3));

                const particleMat = new THREE.PointsMaterial({
                    size: 0.12,
                    vertexColors: true,
                    transparent: true,
                    opacity: 0.65
                });
                const particles = new THREE.Points(particleGeom, particleMat);
                scene.add(particles);

                // Mouse Parallax Logic
                let mouseX = 0;
                let mouseY = 0;
                let targetX = 0;
                let targetY = 0;

                window.addEventListener('mousemove', (e) => {
                    mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
                    mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
                });

                // Window Resize Handler
                window.addEventListener('resize', () => {
                    camera.aspect = window.innerWidth / window.innerHeight;
                    camera.updateProjectionMatrix();
                    renderer.setSize(window.innerWidth, window.innerHeight);
                    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
                    adjustCorePosition();
                });

                // Animation Render Loop
                const clock = new THREE.Clock();

                function animate() {
                    requestAnimationFrame(animate);
                    const elapsedTime = clock.getElapsedTime();

                    // Smooth parallax dampening
                    targetX += (mouseX - targetX) * 0.05;
                    targetY += (mouseY - targetY) * 0.05;

                    // Rotate Central Core
                    coreMesh.rotation.y = elapsedTime * 0.2;
                    coreMesh.rotation.x = elapsedTime * 0.15;
                    wireMesh.rotation.y = elapsedTime * 0.2;
                    wireMesh.rotation.x = elapsedTime * 0.15;

                    // Rotate Gyroscope Rings
                    ring1.rotation.z = elapsedTime * 0.35 + targetX * 0.5;
                    ring2.rotation.x = elapsedTime * -0.28 + targetY * 0.5;

                    // Orbit Shards
                    shards.forEach(s => {
                        s.angle += s.speed;
                        s.mesh.position.x = Math.cos(s.angle) * s.radius;
                        s.mesh.position.z = Math.sin(s.angle) * s.radius;
                        s.mesh.rotation.x += 0.01;
                        s.mesh.rotation.y += 0.015;
                    });

                    // Drift Particles
                    particles.rotation.y = elapsedTime * 0.03;
                    particles.rotation.x = targetY * 0.1;

                    // Parallax tilt on core group
                    coreGroup.rotation.y = targetX * 0.4;
                    coreGroup.rotation.x = -targetY * 0.3;

                    renderer.render(scene, camera);
                }

                animate();
            }
        });
    </script>
</div>
