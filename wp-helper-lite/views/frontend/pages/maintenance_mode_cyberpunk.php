<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Maintenance Mode Template — Cyberpunk Neon Retro
 * Variables expected (set before require):
 *   $whp_maintenance_title
 *   $whp_maintenance_heading
 *   $whp_maintenance_heading_sub
 *   $whp_maintenance_desc
 *   $whp_maintenance_countdown  (datetime string "YYYY-MM-DDTHH:MM" or empty)
 *   $favicon_url
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (!empty($favicon_url)): ?>
        <link rel="shortcut icon" href="<?php echo esc_url($favicon_url); ?>" type="image/x-icon">
    <?php endif; ?>
    <title><?php echo !empty($whp_maintenance_title) ? esc_html($whp_maintenance_title) : 'Đang bảo trì'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg:           #050014;
            --neon-pink:    #ff007f;
            --neon-cyan:    #00f0ff;
            --neon-purple:  #9d4edd;
            --text-main:    #ffffff;
            --text-muted:   #8a829e;
            --card-border:  rgba(255, 0, 127, 0.2);
            --card-bg:      rgba(10, 3, 26, 0.7);
        }

        body {
            font-family: 'Be Vietnam Pro', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background-color: var(--bg);
            background-image: 
                radial-gradient(ellipse at 50% 30%, rgba(157, 78, 221, 0.18) 0%, transparent 60%),
                linear-gradient(to bottom, #050014 60%, #1a0033 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            overflow-x: hidden;
            position: relative;
        }

        /* Cyberpunk grid background */
        body::before {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 350px;
            background-image: 
                linear-gradient(var(--neon-pink) 1px, transparent 1px),
                linear-gradient(90deg, var(--neon-pink) 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: center bottom;
            transform: perspective(200px) rotateX(70deg);
            transform-origin: center bottom;
            opacity: 0.15;
            z-index: 1;
            mask-image: linear-gradient(to top, rgba(0,0,0,1) 20%, rgba(0,0,0,0) 100%);
            -webkit-mask-image: linear-gradient(to top, rgba(0,0,0,1) 20%, rgba(0,0,0,0) 100%);
            pointer-events: none;
        }

        .page-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 660px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2.2rem;
        }

        /* ─── Cyberpunk HUD Header ─── */
        .hud-header {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            animation: glitchFade 0.6s ease-out;
        }

        .cyber-icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 0 12px rgba(255, 0, 127, 0.5)) drop-shadow(0 0 24px rgba(0, 240, 255, 0.25));
        }

        @keyframes glitchFade {
            0% { opacity: 0; transform: scale(0.95); }
            50% { opacity: 0.8; }
            100% { opacity: 1; transform: scale(1); }
        }

        /* ─── Card ─── */
        .content-card {
            background: var(--card-bg);
            border: 1.5px solid var(--card-border);
            border-top: 4px solid var(--neon-pink);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            padding: 3rem 2.5rem;
            width: 100%;
            box-shadow: 
                0 15px 45px rgba(0, 0, 0, 0.5),
                0 0 25px rgba(255, 0, 127, 0.08);
            position: relative;
            overflow: hidden;
        }

        /* Tech corner elements */
        .content-card::before, .content-card::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            border: 2px solid var(--neon-cyan);
            pointer-events: none;
        }
        .content-card::before {
            bottom: 12px;
            left: 12px;
            border-right: none;
            border-top: none;
        }
        .content-card::after {
            bottom: 12px;
            right: 12px;
            border-left: none;
            border-top: none;
        }

        .content-card {
            text-align: center;
        }

        .main-heading {
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: -0.01em;
            line-height: 1.2;
            color: #ffffff;
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.15);
            margin-bottom: 0.7rem;
            text-transform: uppercase;
        }

        .sub-heading {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--neon-pink);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            text-shadow: 0 0 8px rgba(255, 0, 127, 0.3);
        }

        .divider {
            height: 2px;
            width: 100px;
            background: linear-gradient(90deg, transparent, var(--neon-pink), var(--neon-cyan), transparent);
            margin: 0 auto 1.5rem;
        }

        .description {
            font-size: 1.02rem;
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* ─── Countdown ─── */
        .countdown-section {
            margin-top: 2.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .countdown-label-top {
            font-family: 'Be Vietnam Pro', monospace;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--neon-cyan);
            text-shadow: 0 0 4px rgba(0, 240, 255, 0.3);
        }

        .countdown-timer {
            display: flex;
            gap: 0.75rem;
        }

        .countdown-box {
            background: rgba(10, 3, 26, 0.9);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            min-width: 78px;
            padding: 0.9rem 0.5rem 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 
                inset 0 0 10px rgba(255, 0, 127, 0.05),
                0 0 8px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        /* Corner dots for boxes */
        .countdown-box::before {
            content: '';
            position: absolute;
            top: 4px; right: 4px;
            width: 3px; height: 3px;
            background-color: var(--neon-cyan);
            border-radius: 50%;
            box-shadow: 0 0 3px var(--neon-cyan);
        }

        .countdown-num {
            font-family: 'Be Vietnam Pro', monospace;
            font-size: 2.1rem;
            font-weight: 700;
            color: var(--neon-cyan);
            text-shadow: 0 0 8px rgba(0, 240, 255, 0.6);
            line-height: 1;
        }

        .countdown-unit {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            margin-top: 0.45rem;
            font-weight: 600;
        }

        /* ─── Footer ─── */
        .footer {
            font-family: 'Be Vietnam Pro', monospace;
            font-size: 0.8rem;
            color: rgba(138, 130, 158, 0.45);
            letter-spacing: 0.08em;
        }

        @media (max-width: 480px) {
            .content-card {
                padding: 2.2rem 1.4rem;
            }
            .main-heading {
                font-size: 1.75rem;
            }
            .countdown-box {
                min-width: 66px;
            }
            .countdown-num {
                font-size: 1.7rem;
            }
        }
    </style>
</head>
<body>

    <div class="page-wrap">
        
        <?php if (!empty($whp_maintenance_logo)): ?>
            <!-- Custom logo thay thế cyber icon -->
            <div style="margin-bottom: 20px;">
                <img src="<?php echo esc_url($whp_maintenance_logo); ?>" alt="Logo" height="80" style="max-height: 80px; max-width: 220px; object-fit: contain; filter: drop-shadow(0 0 12px rgba(255, 0, 127, 0.35));">
            </div>
        <?php else: ?>

        <!-- Cyber Icon — chỉ hiện khi không có custom logo -->
        <div class="cyber-icon-wrap">
            <svg viewBox="0 0 80 80" width="88" height="88" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Outer hexagon ring -->
                <polygon points="40,4 72,22 72,58 40,76 8,58 8,22" fill="rgba(255,0,127,0.06)" stroke="#ff007f" stroke-width="1.5" opacity="0.7"/>
                <!-- Inner ring cyan -->
                <polygon points="40,12 64,26 64,54 40,68 16,54 16,26" fill="none" stroke="#00f0ff" stroke-width="1" opacity="0.4"/>
                <!-- Corner dots -->
                <circle cx="40" cy="4"  r="2" fill="#ff007f" opacity="0.9"/>
                <circle cx="72" cy="22" r="2" fill="#ff007f" opacity="0.9"/>
                <circle cx="72" cy="58" r="2" fill="#ff007f" opacity="0.9"/>
                <circle cx="40" cy="76" r="2" fill="#ff007f" opacity="0.9"/>
                <circle cx="8"  cy="58" r="2" fill="#ff007f" opacity="0.9"/>
                <circle cx="8"  cy="22" r="2" fill="#ff007f" opacity="0.9"/>
                <!-- Wrench icon centered -->
                <g transform="translate(40,40)">
                    <path d="M-10,-18 C-6,-22 6,-22 10,-18 L6,-12 L10,-6 L-2,6 L-8,4 L-12,-2 Z" fill="none" stroke="#ff007f" stroke-width="2" stroke-linejoin="round"/>
                    <rect x="-4" y="4" width="8" height="14" rx="2" fill="none" stroke="#ff007f" stroke-width="2"/>
                    <line x1="-8" y1="4"  x2="-4" y2="4"  stroke="#ff007f" stroke-width="1.5"/>
                    <line x1="4"  y1="4"  x2="8"  y2="4"  stroke="#ff007f" stroke-width="1.5"/>
                    <!-- Circuit dots -->
                    <circle cx="-14" cy="0"  r="1.5" fill="#00f0ff" opacity="0.8"/>
                    <circle cx="14"  cy="0"  r="1.5" fill="#00f0ff" opacity="0.8"/>
                    <circle cx="0"   cy="-16" r="1.5" fill="#00f0ff" opacity="0.8"/>
                </g>
            </svg>
        </div>
        <?php endif; ?>

        <div class="content-card">
            <h1 class="main-heading">
                <?php echo !empty($whp_maintenance_heading) ? esc_html($whp_maintenance_heading) : 'Đang nâng cấp hệ thống'; ?>
            </h1>

            <?php if (!empty($whp_maintenance_heading_sub)): ?>
                <p class="sub-heading"><?php echo esc_html($whp_maintenance_heading_sub); ?></p>
            <?php endif; ?>

            <div class="divider"></div>

            <p class="description">
                <?php echo !empty($whp_maintenance_desc)
                    ? nl2br(esc_html($whp_maintenance_desc))
                    : 'Hệ thống đang được nâng cấp và cải thiện. Vui lòng quay lại sau ít phút. Chúng tôi sẽ sớm trở lại!';
                ?>
            </p>

            <?php if (!empty($whp_maintenance_countdown)): ?>
                <div class="countdown-section" id="countdown-wrap">
                    <p class="countdown-label-top">◈ Thời gian hoàn tất</p>
                    <div class="countdown-timer">
                        <div class="countdown-box">
                            <span class="countdown-num" id="cd-days">00</span>
                            <span class="countdown-unit">Ngày</span>
                        </div>
                        <div class="countdown-box">
                            <span class="countdown-num" id="cd-hours">00</span>
                            <span class="countdown-unit">Giờ</span>
                        </div>
                        <div class="countdown-box">
                            <span class="countdown-num" id="cd-mins">00</span>
                            <span class="countdown-unit">Phút</span>
                        </div>
                        <div class="countdown-box">
                            <span class="countdown-num" id="cd-secs">00</span>
                            <span class="countdown-unit">Giây</span>
                        </div>
                    </div>
                </div>

                <script>
                (function () {
                    var target = new Date("<?php echo esc_js($whp_maintenance_countdown); ?>").getTime();
                    if (isNaN(target)) {
                        var el = document.getElementById('countdown-wrap');
                        if (el) el.style.display = 'none';
                        return;
                    }

                    function pad(n) { return String(n).padStart(2, '0'); }

                    function tick() {
                        var now  = Date.now();
                        var diff = target - now;

                        if (diff <= 0) {
                            clearInterval(timer);
                            var el = document.getElementById('countdown-wrap');
                            if (el) el.style.display = 'none';
                            return;
                        }

                        var d = Math.floor(diff / 86400000);
                        var h = Math.floor((diff % 86400000) / 3600000);
                        var m = Math.floor((diff % 3600000)  / 60000);
                        var s = Math.floor((diff % 60000)    / 1000);

                        document.getElementById('cd-days').textContent  = pad(d);
                        document.getElementById('cd-hours').textContent = pad(h);
                        document.getElementById('cd-mins').textContent  = pad(m);
                        document.getElementById('cd-secs').textContent  = pad(s);
                    }

                    tick();
                    var timer = setInterval(tick, 1000);
                }());
                </script>
            <?php endif; ?>
            <?php $_social_theme = 'dark'; require __DIR__ . '/_social_links.php'; ?>
        </div>

        <footer class="footer">
            &copy; <?php echo date('Y'); ?> Made in Vietnam by MWP Team. All Rights Reserved.
        </footer>
    </div>
</body>
</html>
