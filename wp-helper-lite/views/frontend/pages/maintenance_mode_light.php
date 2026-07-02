<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Maintenance Mode Template — Light Minimal
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
    <title><?php echo !empty($whp_maintenance_title) ? esc_html($whp_maintenance_title) : 'Site Under Maintenance'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg:           #f8fafc;
            --bg-card:      #ffffff;
            --navy:         #0f172a;
            --navy-mid:     #1e3a5f;
            --blue-500:     #3b82f6;
            --blue-100:     #dbeafe;
            --gray-400:     #94a3b8;
            --gray-500:     #64748b;
            --gray-200:     #e2e8f0;
            --gray-100:     #f1f5f9;
            --pulse-color:  #bfdbfe;
            --shadow-sm:    0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.05);
            --shadow-md:    0 4px 16px rgba(15,23,42,.08), 0 2px 6px rgba(15,23,42,.04);
            --shadow-lg:    0 20px 50px rgba(15,23,42,.10), 0 8px 20px rgba(15,23,42,.06);
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: var(--bg);
            color: var(--navy);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            overflow-x: hidden;
        }

        /* Subtle dot-pattern background */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0.45;
            pointer-events: none;
            z-index: 0;
        }

        /* Soft radial glow top-center */
        body::after {
            content: '';
            position: fixed;
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            width: 700px;
            height: 500px;
            background: radial-gradient(ellipse at center, rgba(191,219,254,0.45) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* ─── Layout ─── */
        .page-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 620px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        /* ─── Icon section ─── */
        .icon-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            animation: fadeDown 0.7s ease-out both;
        }

        .icon-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Three concentric pulse rings */
        .pulse-ring {
            position: absolute;
            border-radius: 50%;
            background: var(--pulse-color);
            opacity: 0;
            animation: pulseRing 3.6s ease-out infinite;
        }

        .pulse-ring:nth-child(1) {
            width: 120px; height: 120px;
            animation-delay: 0s;
        }
        .pulse-ring:nth-child(2) {
            width: 120px; height: 120px;
            animation-delay: 0.9s;
        }
        .pulse-ring:nth-child(3) {
            width: 120px; height: 120px;
            animation-delay: 1.8s;
        }

        @keyframes pulseRing {
            0%   { transform: scale(0.85); opacity: 0.55; }
            50%  { transform: scale(1.25); opacity: 0.18; }
            100% { transform: scale(1.6);  opacity: 0;    }
        }

        .icon-bg {
            position: absolute;
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(145deg, #dbeafe, #eff6ff);
            box-shadow: var(--shadow-md), 0 0 0 6px rgba(219,234,254,0.45);
        }

        .wrench-svg {
            position: relative;
            z-index: 2;
            width: 46px;
            height: 46px;
            color: var(--blue-500);
            filter: drop-shadow(0 2px 8px rgba(59,130,246,0.25));
        }

        /* ─── Card ─── */
        .content-card {
            background: var(--bg-card);
            border: 1px solid var(--gray-200);
            border-radius: 24px;
            padding: 2.5rem 2.75rem;
            width: 100%;
            box-shadow: var(--shadow-lg);
            text-align: center;
            animation: fadeUp 0.75s 0.15s ease-out both;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--blue-100);
            color: var(--blue-500);
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            margin-bottom: 1.25rem;
        }

        .badge-dot {
            width: 7px;
            height: 7px;
            background: var(--blue-500);
            border-radius: 50%;
            animation: blink 1.4s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.2; }
        }

        .main-heading {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -0.03em;
            line-height: 1.2;
            margin-bottom: 0.55rem;
        }

        .sub-heading {
            font-size: 1rem;
            font-weight: 500;
            color: var(--blue-500);
            margin-bottom: 1.4rem;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0 auto 1.4rem;
            width: 100%;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gray-200), transparent);
        }

        .divider-dot {
            width: 5px;
            height: 5px;
            background: var(--gray-400);
            border-radius: 50%;
        }

        .description {
            font-size: 0.975rem;
            color: var(--gray-500);
            line-height: 1.75;
        }

        /* ─── Countdown ─── */
        .countdown-section {
            margin-top: 2rem;
            padding-top: 1.75rem;
            border-top: 1px solid var(--gray-200);
        }

        .countdown-label-top {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--gray-400);
            margin-bottom: 1rem;
        }

        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
        }

        .countdown-box {
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: 14px;
            min-width: 72px;
            padding: 0.9rem 0.5rem 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            box-shadow: var(--shadow-sm), inset 0 1px 0 rgba(255,255,255,0.9);
        }

        .countdown-num {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--navy);
            line-height: 1;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
        }

        .countdown-unit {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--blue-500);
        }

        /* Separator dots between boxes */
        .countdown-sep {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 6px;
            padding-bottom: 0.5rem;
        }

        .countdown-sep span {
            display: block;
            width: 5px;
            height: 5px;
            background: var(--gray-400);
            border-radius: 50%;
        }

        /* ─── Footer ─── */
        .footer {
            font-size: 0.8rem;
            color: var(--gray-400);
            text-align: center;
            animation: fadeUp 0.75s 0.3s ease-out both;
        }

        .footer a {
            color: var(--blue-500);
            text-decoration: none;
        }

        /* ─── Animations ─── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Responsive ─── */
        @media (max-width: 480px) {
            .content-card {
                padding: 2rem 1.5rem;
            }

            .main-heading {
                font-size: 1.6rem;
            }

            .countdown-box {
                min-width: 60px;
                padding: 0.75rem 0.4rem 0.6rem;
            }

            .countdown-num {
                font-size: 1.4rem;
            }

            .countdown-sep {
                gap: 4px;
            }
        }

        @media (max-width: 360px) {
            .countdown-sep {
                display: none;
            }

            .countdown-box {
                min-width: 54px;
            }
        }
    </style>
</head>
<body>

<div class="page-wrap">

    <?php if (!empty($whp_maintenance_logo)): ?>
    <!-- Site Logo replaces icon when set -->
    <div style="text-align:center; margin-bottom:8px;">
        <img src="<?php echo esc_url($whp_maintenance_logo); ?>" alt="Site Logo" height="80" style="max-height:80px; max-width:220px; object-fit:contain;">
    </div>
    <?php else: ?>
    <!-- Wrench icon with pulse rings — shown only when no logo -->
    <div class="icon-section">
        <div class="icon-wrapper">
            <div class="pulse-ring"></div>
            <div class="pulse-ring"></div>
            <div class="pulse-ring"></div>
            <div class="icon-bg"></div>
            <svg class="wrench-svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.75"
                 stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.77 3.77z"/>
            </svg>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main card -->
    <div class="content-card" role="main">
        <div class="badge">
            <span class="badge-dot" aria-hidden="true"></span>
            Maintenance
        </div>

        <h1 class="main-heading">
            <?php echo !empty($whp_maintenance_heading) ? esc_html($whp_maintenance_heading) : 'We\'ll be back soon'; ?>
        </h1>

        <?php if (!empty($whp_maintenance_heading_sub)): ?>
            <p class="sub-heading"><?php echo esc_html($whp_maintenance_heading_sub); ?></p>
        <?php endif; ?>

        <div class="divider" aria-hidden="true">
            <span class="divider-line"></span>
            <span class="divider-dot"></span>
            <span class="divider-line"></span>
        </div>

        <p class="description">
            <?php echo !empty($whp_maintenance_desc)
                ? nl2br(esc_html($whp_maintenance_desc))
                : 'We\'re performing scheduled maintenance to improve your experience. Please check back in a little while — we\'ll be up and running before you know it.';
            ?>
        </p>

        <?php if (!empty($whp_maintenance_countdown)): ?>
            <div class="countdown-section" id="countdown-section" role="timer" aria-live="polite" aria-label="Countdown to completion">
                <p class="countdown-label-top">Thời gian quay trở lại</p>
                <div class="countdown-timer">
                    <div class="countdown-box">
                        <span class="countdown-num" id="cd-days">00</span>
                        <span class="countdown-unit">Ngày</span>
                    </div>
                    <div class="countdown-sep" aria-hidden="true"><span></span><span></span></div>
                    <div class="countdown-box">
                        <span class="countdown-num" id="cd-hours">00</span>
                        <span class="countdown-unit">Giờ</span>
                    </div>
                    <div class="countdown-sep" aria-hidden="true"><span></span><span></span></div>
                    <div class="countdown-box">
                        <span class="countdown-num" id="cd-mins">00</span>
                        <span class="countdown-unit">Phút</span>
                    </div>
                    <div class="countdown-sep" aria-hidden="true"><span></span><span></span></div>
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
                    var el = document.getElementById('countdown-section');
                    if (el) el.style.display = 'none';
                    return;
                }

                function pad(n) { return String(n).padStart(2, '0'); }

                function tick() {
                    var now  = Date.now();
                    var diff = target - now;

                    if (diff <= 0) {
                        clearInterval(timer);
                        var el = document.getElementById('countdown-section');
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
        <?php $_social_theme = 'light'; require __DIR__ . '/_social_links.php'; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        &copy; <?php echo date('Y'); ?> Made in Vietnam by MWP Team. All Rights Reserved.
    </footer>

</div>

</body>
</html>
