<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Maintenance Mode Template — Space Rocket Launching Soon
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
    <title><?php echo !empty($whp_maintenance_title) ? esc_html($whp_maintenance_title) : 'Site Launching Soon'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg:           #0a0813;
            --text-main:    #ffffff;
            --text-muted:   #9fa6c0;
            --rocket-glow:  #f43f5e;
            --rocket-accent: #38bdf8;
            --card-bg:      rgba(15, 12, 30, 0.6);
            --border-glow:  rgba(56, 189, 248, 0.15);
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: linear-gradient(135deg, #0f0c1b 0%, #05020a 100%);
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

        /* Twinkling stars effect */
        .stars {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100%; height: 100%;
            display: block;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><circle cx="20" cy="30" r="1.5" fill="white" opacity="0.6"/><circle cx="120" cy="50" r="1" fill="white" opacity="0.4"/><circle cx="70" cy="110" r="1.8" fill="white" opacity="0.8"/><circle cx="180" cy="130" r="1.2" fill="white" opacity="0.5"/><circle cx="40" cy="170" r="1" fill="white" opacity="0.3"/><circle cx="150" cy="180" r="1.5" fill="white" opacity="0.7"/></svg>') repeat;
            z-index: 0;
            opacity: 0.8;
            animation: starsMove 180s linear infinite;
        }

        @keyframes starsMove {
            from { background-position: 0 0; }
            to { background-position: 0 1000px; }
        }

        /* Nebula glow */
        .nebula {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(244,63,94,0.06) 0%, rgba(56,189,248,0.06) 50%, transparent 70%);
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
            filter: blur(40px);
        }

        .page-wrap {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 640px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2.5rem;
        }

        /* ─── Rocket Animation ─── */
        .rocket-container {
            position: relative;
            width: 120px;
            height: 120px;
            animation: floatRocket 4s ease-in-out infinite;
        }

        @keyframes floatRocket {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50%      { transform: translateY(-12px) rotate(2deg); }
        }

        /* Animated Flame */
        .flame {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 16px;
            height: 35px;
            background: linear-gradient(to bottom, #ff7e47, #f43f5e 60%, transparent);
            border-radius: 50% 50% 20% 20%;
            filter: blur(1px);
            animation: burn 0.15s infinite alternate;
            transform-origin: center top;
            box-shadow: 0 0 12px rgba(244,63,94,0.8);
        }

        @keyframes burn {
            0% { transform: translateX(-50%) scaleY(1); opacity: 0.9; }
            100% { transform: translateX(-50%) scaleY(1.2) scaleX(0.95); opacity: 1; }
        }

        /* ─── Card ─── */
        .content-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-glow);
            backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            width: 100%;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255,255,255,0.05);
            text-align: center;
        }

        .main-heading {
            font-size: 2.1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.25;
            background: linear-gradient(135deg, #ffffff 40%, var(--rocket-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.6rem;
        }

        .sub-heading {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--rocket-accent);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .divider {
            height: 1px;
            width: 120px;
            background: linear-gradient(90deg, transparent, var(--rocket-accent), transparent);
            margin: 0 auto 1.75rem;
        }

        .description {
            font-size: 1.05rem;
            color: var(--text-muted);
            line-height: 1.7;
        }

        /* ─── Countdown ─── */
        .countdown-section {
            margin-top: 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }

        .countdown-label-top {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--text-muted);
        }

        .countdown-timer {
            display: flex;
            gap: 0.85rem;
        }

        .countdown-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 14px;
            min-width: 76px;
            padding: 0.95rem 0.5rem 0.8rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.15);
        }

        .countdown-num {
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--text-main);
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
            line-height: 1.1;
        }

        .countdown-unit {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--rocket-accent);
            margin-top: 0.4rem;
            font-weight: 600;
        }

        /* ─── Footer ─── */
        .footer {
            font-size: 0.82rem;
            color: rgba(159, 166, 192, 0.4);
            letter-spacing: 0.03em;
        }

        @media (max-width: 480px) {
            .content-card {
                padding: 2rem 1.5rem;
            }
            .main-heading {
                font-size: 1.7rem;
            }
            .countdown-box {
                min-width: 66px;
            }
            .countdown-num {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="stars"></div>
    <div class="nebula"></div>

    <div class="page-wrap">
        
        <?php if (!empty($whp_maintenance_logo)): ?>
            <!-- Site Logo replaces rocket when set -->
            <div style="margin-bottom: 0.5rem; text-align:center;">
                <img src="<?php echo esc_url($whp_maintenance_logo); ?>" alt="Logo" height="80" style="max-height: 80px; max-width: 220px; object-fit: contain;">
            </div>
        <?php else: ?>
        <!-- Rocket Vector Graphic — shown only when no logo -->
        <div class="rocket-container">
            <svg viewBox="0 0 64 64" width="90" height="90" style="display:block; margin:0 auto;">
                <!-- Wings -->
                <path d="M16 42 C12 46 8 50 12 54 C16 54 22 48 22 44 Z" fill="var(--rocket-glow)" />
                <path d="M48 42 C52 46 56 50 52 54 C48 54 42 48 42 44 Z" fill="var(--rocket-glow)" />
                
                <!-- Main Body -->
                <path d="M32 6 C42 16 42 38 38 48 L26 48 C22 38 22 16 32 6 Z" fill="#e2e8f0" />
                <path d="M32 6 C37 16 38 38 38 48 L32 48 Z" fill="#cbd5e1" /> <!-- Shadow side -->
                
                <!-- Nose cone -->
                <path d="M32 6 C36 12 37 18 37 20 L27 20 C27 18 28 12 32 6 Z" fill="var(--rocket-glow)" />
                
                <!-- Porthole window -->
                <circle cx="32" cy="28" r="6" fill="#1e293b" />
                <circle cx="32" cy="28" r="4.5" fill="var(--rocket-accent)" />
                <path d="M30 25 A 3.5 3.5 0 0 1 34.5 29" fill="none" stroke="#fff" stroke-width="1" stroke-linecap="round" />
                
                <!-- Fins / Thruster cap -->
                <path d="M28 48 L36 48 L34 52 L30 52 Z" fill="#64748b" />
            </svg>
            <div class="flame"></div>
        </div>
        <?php endif; ?>

        <div class="content-card">
            <h1 class="main-heading">
                <?php echo !empty($whp_maintenance_heading) ? esc_html($whp_maintenance_heading) : 'LAUNCHING SOON'; ?>
            </h1>

            <?php if (!empty($whp_maintenance_heading_sub)): ?>
                <p class="sub-heading"><?php echo esc_html($whp_maintenance_heading_sub); ?></p>
            <?php endif; ?>

            <div class="divider"></div>

            <p class="description">
                <?php echo !empty($whp_maintenance_desc)
                    ? nl2br(esc_html($whp_maintenance_desc))
                    : 'We are fueling our engines and getting ready for liftoff! Something amazing is under construction here. Stay tuned.';
                ?>
            </p>

            <?php if (!empty($whp_maintenance_countdown)): ?>
                <div class="countdown-section" id="countdown-wrap">
                    <p class="countdown-label-top">Đếm ngược thời gian</p>
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
            <?php require __DIR__ . '/_social_links.php'; ?>
        </div>

        <footer class="footer">
            &copy; <?php echo date('Y'); ?> Made in Vietnam by MWP Team. All Rights Reserved.
        </footer>
    </div>
</body>
</html>
