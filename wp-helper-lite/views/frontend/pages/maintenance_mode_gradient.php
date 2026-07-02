<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Maintenance Mode Template — Gradient Bold
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
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --sky-300:    #7dd3fc;
            --sky-400:    #38bdf8;
            --sky-200:    #bae6fd;
            --white:      #ffffff;
            --white-dim:  rgba(255,255,255,0.72);
            --white-muted:rgba(255,255,255,0.45);
            --accent:     #e879f9;
            --accent-2:   #818cf8;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Be Vietnam Pro', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 100%);
            background-attachment: fixed;
            color: var(--white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            overflow-x: hidden;
            position: relative;
        }

        /* ─── Animated geometric background shapes ─── */
        .bg-shapes {
            position: fixed;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.045);
        }

        /* Shape 1 — large circle top-left */
        .shape-1 {
            width: 520px;
            height: 520px;
            top: -160px;
            left: -140px;
            background: rgba(125,211,252,0.04);
            animation: driftA 32s ease-in-out infinite alternate;
        }

        /* Shape 2 — medium circle bottom-right */
        .shape-2 {
            width: 380px;
            height: 380px;
            bottom: -100px;
            right: -80px;
            background: rgba(232,121,249,0.04);
            animation: driftB 26s ease-in-out infinite alternate;
        }

        /* Shape 3 — small rotated square top-right */
        .shape-3 {
            width: 180px;
            height: 180px;
            top: 8%;
            right: 6%;
            border-radius: 24px;
            background: rgba(99,102,241,0.05);
            border: 1px solid rgba(255,255,255,0.06);
            animation: spinFloat 38s linear infinite;
        }

        /* Shape 4 — tiny circle mid-left */
        .shape-4 {
            width: 110px;
            height: 110px;
            top: 52%;
            left: 4%;
            background: rgba(56,189,248,0.05);
            animation: driftC 20s ease-in-out infinite alternate;
        }

        /* Shape 5 — ring bottom-left */
        .shape-5 {
            width: 260px;
            height: 260px;
            bottom: 5%;
            left: 10%;
            background: transparent;
            border: 1px solid rgba(125,211,252,0.07);
            animation: driftD 28s ease-in-out infinite alternate;
        }

        /* Fine dot-grid overlay */
        .bg-dots {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 36px 36px;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes driftA {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(60px, 40px) scale(1.07); }
        }
        @keyframes driftB {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(-50px, -30px) scale(1.05); }
        }
        @keyframes driftC {
            from { transform: translate(0, 0); }
            to   { transform: translate(30px, -40px); }
        }
        @keyframes driftD {
            from { transform: translate(0, 0) rotate(0deg); }
            to   { transform: translate(40px, -20px) rotate(15deg); }
        }
        @keyframes spinFloat {
            from { transform: rotate(0deg) translateY(0); }
            to   { transform: rotate(360deg) translateY(-20px); }
        }

        /* ─── Page layout ─── */
        .page-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 660px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2.25rem;
        }

        /* ─── Status badge ─── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 999px;
            padding: 0.4rem 1.1rem;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--sky-300);
            animation: fadeDown 0.7s ease-out both;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--sky-400);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--sky-400);
            animation: pulse 1.6s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 8px var(--sky-400); }
            50%       { opacity: 0.4; box-shadow: 0 0 3px var(--sky-400); }
        }

        /* ─── Rocket icon ─── */
        .rocket-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: fadeDown 0.7s 0.1s ease-out both;
        }

        /* Glow halo behind rocket */
        .rocket-wrap::before {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(125,211,252,0.18) 0%, transparent 70%);
            pointer-events: none;
        }

        .rocket-svg {
            width: 88px;
            height: 88px;
            filter: drop-shadow(0 4px 20px rgba(125,211,252,0.45));
            animation: rocketFloat 3.2s ease-in-out infinite;
        }

        @keyframes rocketFloat {
            0%   { transform: translateY(0px)   rotate(-2deg); }
            50%  { transform: translateY(-14px) rotate(2deg); }
            100% { transform: translateY(0px)   rotate(-2deg); }
        }

        /* ─── Heading block ─── */
        .heading-block {
            text-align: center;
            animation: fadeUp 0.75s 0.15s ease-out both;
        }

        .main-heading {
            font-size: clamp(2.2rem, 5vw, 3.2rem);
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: -0.035em;
            color: var(--white);
            margin-bottom: 0.7rem;
        }

        /* Gradient accent on selected words via a span injected via CSS — we wrap key text */
        .heading-accent {
            background: linear-gradient(90deg, var(--sky-300), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sub-heading {
            font-size: 1.05rem;
            font-weight: 500;
            color: var(--sky-300);
            line-height: 1.6;
        }

        /* ─── Main glass card ─── */
        .glass-card {
            background: rgba(255,255,255,0.055);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 28px;
            padding: 2.5rem 2.75rem;
            width: 100%;
            box-shadow:
                0 25px 60px rgba(0,0,0,0.45),
                inset 0 1px 0 rgba(255,255,255,0.1);
            text-align: center;
            animation: fadeUp 0.75s 0.25s ease-out both;
        }

        .divider {
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, var(--sky-400), var(--accent));
            border-radius: 999px;
            margin: 0 auto 1.5rem;
        }

        .description {
            font-size: 1rem;
            color: var(--white-dim);
            line-height: 1.8;
        }

        /* ─── Countdown ─── */
        .countdown-section {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.9rem;
        }

        .countdown-label-top {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--white-muted);
        }

        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 0.7rem;
        }

        /* Glassmorphism countdown boxes */
        .countdown-box {
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            min-width: 82px;
            padding: 1.1rem 0.5rem 0.85rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            box-shadow:
                0 8px 32px rgba(0,0,0,0.3),
                inset 0 1px 0 rgba(255,255,255,0.1);
        }

        .countdown-num {
            font-size: 2rem;
            font-weight: 800;
            color: var(--white);
            line-height: 1;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
        }

        .countdown-unit {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--sky-300);
        }

        .countdown-sep {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding-bottom: 0.5rem;
        }

        .countdown-sep span {
            display: block;
            width: 5px;
            height: 5px;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
        }

        /* ─── Footer ─── */
        .footer {
            font-size: 0.82rem;
            color: var(--white-muted);
            text-align: center;
            animation: fadeUp 0.75s 0.4s ease-out both;
        }

        .footer a {
            color: var(--sky-300);
            text-decoration: none;
        }

        /* ─── Animations ─── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Responsive ─── */
        @media (max-width: 520px) {
            .glass-card {
                padding: 2rem 1.5rem;
            }

            .countdown-box {
                min-width: 68px;
                padding: 0.85rem 0.4rem 0.7rem;
            }

            .countdown-num {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 380px) {
            .countdown-sep {
                display: none;
            }

            .countdown-box {
                min-width: 60px;
            }
        }
    </style>
</head>
<body>

<!-- Background decorations -->
<div class="bg-shapes" aria-hidden="true">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="shape shape-5"></div>
</div>
<div class="bg-dots" aria-hidden="true"></div>

<div class="page-wrap">

    <!-- Status badge -->
    <div class="status-badge" role="status">
        <span class="status-dot" aria-hidden="true"></span>
        Scheduled Maintenance
    </div>

    <?php if (!empty($whp_maintenance_logo)): ?>
    <!-- Site Logo replaces icon when set -->
    <div style="text-align:center; margin-bottom:12px;">
        <img src="<?php echo esc_url($whp_maintenance_logo); ?>" alt="Site Logo" height="80" style="max-height:80px; max-width:220px; object-fit:contain;">
    </div>
    <?php else: ?>
    <!-- Rocket icon — shown only when no logo -->
    <div class="rocket-wrap">
        <svg class="rocket-svg" viewBox="0 0 24 24" fill="none"
             stroke="#7dd3fc" stroke-width="1.5"
             stroke-linecap="round" stroke-linejoin="round"
             aria-hidden="true">
            <!-- Rocket body -->
            <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z" fill="rgba(125,211,252,0.15)"/>
            <path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z" fill="rgba(125,211,252,0.1)"/>
            <path d="M9 12H4s.5-1 1-4c2 0 3 .5 3 .5"/>
            <path d="M15 15v5c0 3-1 4-4 4 0-2 .5-3 .5-3"/>
            <!-- Flame exhaust -->
            <path d="M12 8a1.5 1.5 0 0 1-1.5-1.5" stroke="#e879f9" stroke-width="1"/>
        </svg>
    </div>
    <?php endif; ?>

    <!-- Heading block -->
    <div class="heading-block">
        <h1 class="main-heading">
            <?php
            $raw_heading = !empty($whp_maintenance_heading) ? $whp_maintenance_heading : 'Launching something great';
            // Wrap last word in gradient accent span
            $words = explode(' ', $raw_heading);
            if (count($words) > 1) {
                $last  = array_pop($words);
                echo esc_html(implode(' ', $words)) . ' <span class="heading-accent">' . esc_html($last) . '</span>';
            } else {
                echo '<span class="heading-accent">' . esc_html($raw_heading) . '</span>';
            }
            ?>
        </h1>

        <?php if (!empty($whp_maintenance_heading_sub)): ?>
            <p class="sub-heading"><?php echo esc_html($whp_maintenance_heading_sub); ?></p>
        <?php endif; ?>
    </div>

    <!-- Main glass card -->
    <div class="glass-card" role="main">
        <div class="divider" aria-hidden="true"></div>

        <p class="description">
            <?php echo !empty($whp_maintenance_desc)
                ? nl2br(esc_html($whp_maintenance_desc))
                : 'We\'re currently performing scheduled maintenance to upgrade our systems and deliver a better experience. Thank you for your patience — we\'ll be back online shortly.';
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
        <?php require __DIR__ . '/_social_links.php'; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        &copy; <?php echo date('Y'); ?> Made in Vietnam by MWP Team. All Rights Reserved.
    </footer>

</div>

</body>
</html>
