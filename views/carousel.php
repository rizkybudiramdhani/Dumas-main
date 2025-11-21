<div id="header-carousel" class="hero-section">
    <div class="hero-inner">
        <!-- Single Hero Slide - Anti Narkoba -->
        <div class="hero-item active">
            <video class="w-100 hero-video" muted loop playsinline preload="auto" autoplay>
                <source src="video/ditbinmas.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="video-overlay"></div>

            <div class="hero-caption d-flex flex-column align-items-center justify-content-center">
                <div class="title mx-5 px-5 animated slideInDown">
                    <div class="title-center">
                        <h5 class="hero-subtitle">Polda Sumut</h5>
                        <h1 class="display-1 hero-title">BERSAMA BERANTAS NARKOBA</h1>
                    </div>
                </div>

                <!-- Badge Tim -->
                <div class="team-badges mb-4 animated slideInDown">
                    <span class="badge-team">Ditresnarkoba</span>
                    <span class="team-separator">•</span>
                    <span class="badge-team">Ditsamapta</span>
                    <span class="team-separator">•</span>
                    <span class="badge-team">Ditbinmas</span>
                </div>

                <p class="fs-5 mb-5 hero-description animated slideInDown">
                    Ditresnarkoba, Ditsamapta, dan Ditbinmas Polda Sumut siap menerima laporan Anda.<br>
                    Lindungi keluarga dan lingkungan dari bahaya narkoba.
                </p>

                <button class="btn btn-danger-solid border-0 py-3 px-5 btn-lapor-hero" id="btnLaporHero" data-logged-in="<?= isset($_SESSION['user_id']) ? '1' : '0' ?>">
                    <i class="bi bi-megaphone-fill"></i> LAPORKAN SEKARANG!
                </button>
            </div>
        </div>
    </div>
<!-- Modal Login -->
<div class="modal fade" id="modalLogin" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #1a1a1a; border-radius: 20px; border: 1px solid #333; color: #ffffff;">
            <div class="modal-header" style="background: #1E40AF; border-radius: 20px 20px 0 0; padding: 25px; border-bottom: 3px solid #FFD700;">
                <h5 class="modal-title" style="color: #FFD700; font-weight: 700;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login Diperlukan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div id="loginAlertContainer"></div>
                <p class="text-center mb-4" style="color: #e0e0e0;">
                    Silakan login terlebih dahulu untuk melakukan pengaduan.
                </p>
                <div class="d-grid gap-3">
                    <a href="login.php" class="btn" style="background: #FFD700; color: #1E40AF; font-weight: 700; border-radius: 12px; padding: 15px;">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login Sekarang
                    </a>
                    
                </div>
            </div>
        </div>
    </div>
</div>
    <style>
        /* Hero Section Styling - Single Video */
        .hero-section {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: visible;
        }

        .hero-inner {
            width: 100%;
            height: 100vh;
        }

        .hero-item {
            width: 100%;
            height: 100vh;
            position: relative;
        }

        .hero-video {
            width: 100%;
            height: 100vh;
            object-fit: cover;
            display: block;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(26, 26, 46, 0.65);
            z-index: 1;
        }

        .hero-caption {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            padding: 30px;
            width: 100%;
            text-align: center;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #FFD700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: #FFFFFF;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7);
            line-height: 1.2;
        }

        /* Badge Tim Styling */
        .team-badges {
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .badge-team {
            color: #FFD700;
            font-size: 1.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .team-separator {
            color: #FFD700;
            font-size: 1.3rem;
            opacity: 0.6;
        }

        .hero-description {
            font-size: 1.3rem;
            line-height: 1.8;
            color: #FFFFFF;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
            max-width: 800px;
            margin: 0 auto;
        }

        /* Button Styling - Solid Red */
        .btn-danger-solid {
            background-color: #DC2626;
            color: #FFFFFF;
            font-size: 1.1rem;
            padding: 12px 40px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            cursor: pointer;
        }

        .btn-danger-solid:hover {
            background-color: #B91C1C;
            color: #FFFFFF;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.5);
        }

        .btn-lapor-hero {
            animation: pulseButton 2s infinite;
        }

        @keyframes pulseButton {

            0%,
            100% {
                transform: scale(1);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            }

            50% {
                transform: scale(1.05);
                box-shadow: 0 8px 25px rgba(220, 38, 38, 0.5);
            }
        }

        .btn-lapor-hero:hover {
            animation: none;
        }

        /* Animations */
        .animated {
            animation-duration: 1s;
            animation-fill-mode: both;
        }

        @keyframes slideInDown {
            from {
                transform: translate3d(0, -100%, 0);
                opacity: 0;
            }

            to {
                transform: translate3d(0, 0, 0);
                opacity: 1;
            }
        }

        .slideInDown {
            animation-name: slideInDown;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-video {
                height: 70vh;
            }

            .hero-title {
                font-size: 2.5rem !important;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .hero-description {
                font-size: 1rem;
                padding: 0 20px;
            }

            .hero-caption {
                padding: 15px;
            }

            .btn-danger-solid {
                font-size: 0.9rem;
                padding: 10px 30px;
            }

            .badge-team {
                font-size: 1rem;
                letter-spacing: 1px;
            }

            .team-separator {
                font-size: 1rem;
            }
        }

        @media (max-width: 576px) {
            .hero-video {
                height: 60vh;
            }

            .hero-title {
                font-size: 2rem !important;
            }

            .hero-description {
                font-size: 0.9rem;
            }

            .team-badges {
                gap: 10px;
            }

            .badge-team {
                font-size: 0.9rem;
                letter-spacing: 1px;
            }

            .team-separator {
                font-size: 0.9rem;
            }
        }

        .hero-video:not([src]) {
            background: #1a1a2e;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.querySelector('.hero-video');

            // Auto-play video dengan error handling
            if (video) {
                const playPromise = video.play();

                if (playPromise !== undefined) {
                    playPromise.catch(() => {
                        // Jika autoplay diblok browser, play saat user click
                        document.addEventListener('click', function playOnClick() {
                            video.play();
                            document.removeEventListener('click', playOnClick);
                        }, {
                            once: true
                        });
                    });
                }

                // Pause video saat tab tidak aktif
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        video.pause();
                    } else {
                        video.play();
                    }
                });

                // Error handling
                video.addEventListener('error', (e) => {
                    console.error('Error loading video:', e);
                });
            }

        });
    </script>