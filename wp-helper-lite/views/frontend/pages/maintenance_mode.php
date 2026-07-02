<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$favicon_url = get_site_icon_url();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon_url); ?>" type="image/x-icon">
    <title><?= $whp_maintenance_title != '' ? esc_html($whp_maintenance_title) : 'Bảo trì hệ thống' ?></title>
    <!-- Be Vietnam Pro — Vietnamese-optimised font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --primary-glow: #00f2fe;
            --secondary-glow: #818cf8;
            --accent-glow: #4f46e5;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: radial-gradient(circle at center, #111827 0%, #090d16 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
            padding: 2rem 1rem;
        }

        /* Tech grid & glow backgrounds */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
            background-size: 30px 30px;
            background-position: center;
            pointer-events: none;
            z-index: 1;
        }

        .ambient-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 242, 254, 0.08) 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(79, 70, 229, 0.05) 0%, transparent 75%);
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
        }

        .container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 680px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2.5rem;
        }

        /* Logo Styling */
        .logo-wrap {
            margin-bottom: 0.5rem;
            animation: fadeInDown 0.8s ease-out;
        }
        .site-logo {
            max-height: 80px;
            max-width: 240px;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
        }

        /* Neon Gear Styling */
        .glow-gear-wrap {
            position: relative;
            width: 160px;
            height: 160px;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: pulseGlow 3s ease-in-out infinite;
        }

        .neon-gear {
            filter: drop-shadow(0 0 15px rgba(0, 242, 254, 0.6))
                    drop-shadow(0 0 30px rgba(79, 70, 229, 0.3));
        }

        .gear-rotate {
            transform-origin: 50px 50px;
            animation: rotateGear 12s linear infinite;
        }

        @keyframes rotateGear {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); filter: brightness(1); }
            50% { transform: scale(1.03); filter: brightness(1.15); }
        }

        /* Content Info */
        .content-card {
            background: rgba(15, 23, 42, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease-out;
        }

        .maintenance-title {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.25;
            background: linear-gradient(135deg, #ffffff 30%, var(--primary-glow) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .maintenance-subtitle {
            font-size: 1.2rem;
            color: var(--secondary-glow);
            font-weight: 600;
            margin-bottom: 1.5rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .divider {
            height: 1px;
            width: 80px;
            background: linear-gradient(90deg, transparent, var(--primary-glow), transparent);
            margin: 0 auto 1.5rem;
        }

        .maintenance-desc {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }

        /* Countdown styling */
        .countdown-wrap {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 1s ease-in;
        }

        .countdown-title {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--text-muted);
            font-weight: 600;
        }

        .countdown-timer {
            display: flex;
            gap: 1rem;
        }

        .countdown-box {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            min-width: 75px;
            padding: 0.85rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .countdown-num {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-glow);
            text-shadow: 0 0 10px rgba(0, 242, 254, 0.4);
            line-height: 1.1;
        }

        .countdown-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-top: 0.35rem;
            font-weight: 500;
        }

        /* Footer info */
        .footer-info {
            font-size: 0.85rem;
            color: rgba(148, 163, 184, 0.5);
            margin-top: 1rem;
            letter-spacing: 0.02em;
        }

        /* Animations */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 600px) {
            .content-card {
                padding: 1.75rem;
            }
            .maintenance-title {
                font-size: 1.75rem;
            }
            .countdown-box {
                min-width: 65px;
                padding: 0.65rem;
            }
            .countdown-num {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="ambient-glow-2"></div>

    <div class="container">
        
        <?php if (!empty($whp_maintenance_logo)): ?>
            <!-- Site Logo replaces gear when set -->
            <div class="logo-wrap">
                <img class="site-logo" src="<?php echo esc_url($whp_maintenance_logo); ?>" alt="Site Logo" height="80">
            </div>
        <?php else: ?>
        <!-- Neon Glow Gear — shown only when no logo -->
        <div class="glow-gear-wrap">
            <svg class="neon-gear" viewBox="0 0 100 100" width="130" height="130">
                <!-- Inner tech rings -->
                <circle cx="50" cy="50" r="45" stroke="rgba(0, 242, 254, 0.1)" stroke-width="1" fill="none" />
                <circle cx="50" cy="50" r="32" stroke="rgba(79, 70, 229, 0.15)" stroke-dasharray="4,4" stroke-width="1" fill="none" />
                
                <!-- Rotating gear -->
                <g class="gear-rotate">
                    <!-- Hub and Rim -->
                    <circle cx="50" cy="50" r="16" fill="none" stroke="var(--primary-glow)" stroke-width="2.5" />
                    <circle cx="50" cy="50" r="34" fill="none" stroke="var(--primary-glow)" stroke-width="3" />
                    
                    <!-- Spokes (Horizontal and Vertical) -->
                    <path d="M50 16 L50 34 M50 66 L50 84 M16 50 L34 50 M66 50 L84 50" fill="none" stroke="var(--primary-glow)" stroke-width="2.5" stroke-linecap="round" />
                    
                    <!-- Diagonal Spokes -->
                    <g transform="rotate(45 50 50)" fill="none" stroke="var(--primary-glow)" stroke-width="2.5" stroke-linecap="round">
                        <path d="M50 16 L50 34 M50 66 L50 84 M16 50 L34 50 M66 50 L84 50" />
                    </g>
                    
                    <!-- 8 Teeth -->
                    <g fill="var(--primary-glow)" stroke="var(--primary-glow)" stroke-width="1" stroke-linejoin="round">
                        <path d="M46 16 L54 16 L56 6 L44 6 Z" />
                        <path d="M46 84 L54 84 L56 94 L44 94 Z" />
                        <path d="M16 46 L16 54 L6 56 L6 44 Z" />
                        <path d="M84 46 L84 54 L94 56 L94 44 Z" />
                        <!-- Diagonal teeth -->
                        <g transform="rotate(45 50 50)">
                            <path d="M46 16 L54 16 L56 6 L44 6 Z" />
                            <path d="M46 84 L54 84 L56 94 L44 94 Z" />
                            <path d="M16 46 L16 54 L6 56 L6 44 Z" />
                            <path d="M84 46 L84 54 L94 56 L94 44 Z" />
                        </g>
                    </g>
                    
                    <!-- Center Dot -->
                    <circle cx="50" cy="50" r="7" fill="var(--secondary-glow)" stroke="var(--secondary-glow)" stroke-width="1" />
                    <circle cx="50" cy="50" r="3" fill="#0b0f19" />
                </g>
            </svg>
        </div>
        <?php endif; ?>

        <!-- Content Card -->
        <div class="content-card">
            <h1 class="maintenance-title">
                <?php echo !empty($whp_maintenance_heading) ? esc_html($whp_maintenance_heading) : 'HỆ THỐNG ĐANG BẢO TRÌ'; ?>
            </h1>
            
            <?php if (!empty($whp_maintenance_heading_sub)): ?>
                <h2 class="maintenance-subtitle">
                    <?php echo esc_html($whp_maintenance_heading_sub); ?>
                </h2>
            <?php endif; ?>

            <div class="divider"></div>

            <p class="maintenance-desc">
                <?php echo !empty($whp_maintenance_desc) ? nl2br(esc_html($whp_maintenance_desc)) : 'Chúng tôi đang tiến hành nâng cấp hệ thống để mang lại trải nghiệm tốt nhất cho bạn. Vui lòng quay lại sau.'; ?>
            </p>

            <?php if (!empty($whp_maintenance_countdown)): ?>
                <!-- Countdown Clock Wrap -->
                <div class="countdown-wrap" id="countdown-wrap">
                    <div class="countdown-title">Thời gian quay trở lại</div>
                    <div class="countdown-timer">
                        <div class="countdown-box">
                            <span class="countdown-num" id="days">00</span>
                            <span class="countdown-label">Ngày</span>
                        </div>
                        <div class="countdown-box">
                            <span class="countdown-num" id="hours">00</span>
                            <span class="countdown-label">Giờ</span>
                        </div>
                        <div class="countdown-box">
                            <span class="countdown-num" id="minutes">00</span>
                            <span class="countdown-label">Phút</span>
                        </div>
                        <div class="countdown-box">
                            <span class="countdown-num" id="seconds">00</span>
                            <span class="countdown-label">Giây</span>
                        </div>
                    </div>
                </div>

                <script>
                    (function() {
                        var targetDate = new Date("<?php echo esc_js($whp_maintenance_countdown); ?>").getTime();
                        
                        if (isNaN(targetDate)) {
                            document.getElementById('countdown-wrap').style.display = 'none';
                            return;
                        }

                        function updateCountdown() {
                            var now = new Date().getTime();
                            var diff = targetDate - now;

                            if (diff <= 0) {
                                document.getElementById('countdown-wrap').style.display = 'none';
                                clearInterval(interval);
                                return;
                            }

                            var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                            var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((diff % (1000 * 60)) / 1000);

                            document.getElementById('days').innerText = String(days).padStart(2, '0');
                            document.getElementById('hours').innerText = String(hours).padStart(2, '0');
                            document.getElementById('minutes').innerText = String(minutes).padStart(2, '0');
                            document.getElementById('seconds').innerText = String(seconds).padStart(2, '0');
                        }

                        updateCountdown();
                        var interval = setInterval(updateCountdown, 1000);
                    })();
                </script>
            <?php endif; ?>
            <?php require __DIR__ . '/_social_links.php'; ?>
        </div>

        <div class="footer-info">
            &copy; <?php echo date('Y'); ?> Made in Vietnam by MWP Team. All Rights Reserved.
        </div>

    </div>
</body>
</html>