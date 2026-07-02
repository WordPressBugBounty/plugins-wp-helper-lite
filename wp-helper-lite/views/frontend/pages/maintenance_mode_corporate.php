<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Maintenance Mode Template — Corporate / Business
 * Variables expected (set before require):
 *   $whp_maintenance_title
 *   $whp_maintenance_heading
 *   $whp_maintenance_heading_sub
 *   $whp_maintenance_desc
 *   $whp_maintenance_countdown  (datetime string "YYYY-MM-DDTHH:MM" or empty)
 *   $whp_maintenance_logo       (URL string or empty)
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
    <title><?php echo !empty($whp_maintenance_title) ? esc_html($whp_maintenance_title) : 'Website Đang Bảo Trì'; ?></title>

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
            --bg:           #f4f6f9;
            --bg-card:      #ffffff;
            --navy:         #1e3a5f;
            --navy-light:   #2d5282;
            --blue:         #2563eb;
            --blue-light:   #3b82f6;
            --blue-50:      #eff6ff;
            --blue-100:     #dbeafe;
            --blue-200:     #bfdbfe;
            --gray-200:     #e2e8f0;
            --gray-300:     #cbd5e1;
            --gray-400:     #94a3b8;
            --gray-500:     #64748b;
            --white:        #ffffff;
            --shadow-card:  0 10px 40px -8px rgba(30,58,95,0.12), 0 4px 12px -4px rgba(30,58,95,0.08);
            --shadow-sm:    0 1px 3px rgba(30,58,95,0.07), 0 1px 2px rgba(30,58,95,0.05);
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
            padding: 3rem 1rem;
            overflow-x: hidden;
        }

        /* Subtle top border stripe */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--navy) 0%, var(--blue) 50%, var(--navy) 100%);
            z-index: 100;
        }

        /* ─── Layout ─── */
        .page-wrap {
            width: 100%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        /* ─── Logo area ─── */
        .header-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeDown 0.6s ease-out both;
        }

        .header-logo img {
            max-height: 56px;
            max-width: 220px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(30,58,95,0.10));
        }

        /* ─── Main Card ─── */
        .content-card {
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid rgba(226,232,240,0.9);
            box-shadow: var(--shadow-card);
            padding: 3rem 3.25rem;
            width: 100%;
            text-align: center;
            animation: fadeUp 0.7s 0.1s ease-out both;
        }

        /* ─── Icon ─── */
        .icon-section {
            display: flex;
            justify-content: center;
            margin-bottom: 1.75rem;
        }

        .icon-bg {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: var(--blue-50);
            border: 1px solid var(--blue-100);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm), 0 0 0 6px rgba(219,234,254,0.35);
        }

        .icon-svg {
            width: 40px;
            height: 40px;
            color: var(--blue);
        }

        /* ─── Status badge ─── */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: var(--blue-50);
            border: 1px solid var(--blue-100);
            color: var(--blue);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.3rem 0.85rem;
            border-radius: 999px;
            margin-bottom: 1.25rem;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background: var(--blue);
            border-radius: 50%;
            animation: blink 1.5s ease-in-out infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.25; }
        }

        /* ─── Typography ─── */
        .main-heading {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -0.03em;
            line-height: 1.2;
            margin-bottom: 0.6rem;
        }

        .sub-heading {
            font-size: 1rem;
            font-weight: 500;
            color: var(--blue);
            margin-bottom: 1.5rem;
        }

        /* ─── Divider ─── */
        .divider {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin: 0 auto 1.5rem;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: var(--gray-200);
        }

        .divider-dots {
            display: flex;
            gap: 4px;
        }

        .divider-dots span {
            display: block;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--gray-300);
        }

        /* ─── Description ─── */
        .description {
            font-size: 0.95rem;
            color: var(--gray-500);
            line-height: 1.75;
            margin-bottom: 2rem;
        }

        /* ─── Progress bar ─── */
        .progress-section {
            margin-bottom: 0.5rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .progress-label-text {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--gray-400);
        }

        .progress-label-pct {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--blue);
        }

        .progress-track {
            width: 100%;
            height: 8px;
            background: var(--gray-200);
            border-radius: 99px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            width: 72%;
            height: 100%;
            background: linear-gradient(90deg, var(--navy) 0%, var(--blue) 100%);
            border-radius: 99px;
            position: relative;
            animation: progressGrow 1.4s 0.4s cubic-bezier(0.4, 0, 0.2, 1) both;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.4) 50%, transparent 100%);
            animation: shimmer 2.2s 1.8s infinite linear;
        }

        @keyframes progressGrow {
            from { width: 0%; }
            to   { width: 72%; }
        }

        @keyframes shimmer {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(200%); }
        }

        /* ─── Countdown ─── */
        .countdown-section {
            margin-top: 2rem;
            padding-top: 1.75rem;
            border-top: 1px solid var(--gray-200);
        }

        .countdown-label-top {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--gray-400);
            margin-bottom: 1.1rem;
        }

        .countdown-timer {
            display: flex;
            justify-content: center;
            gap: 0.65rem;
        }

        .countdown-box {
            background: var(--bg);
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            min-width: 72px;
            padding: 0.85rem 0.5rem 0.7rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            box-shadow: var(--shadow-sm);
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
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--blue);
        }

        .countdown-sep {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding-bottom: 0.4rem;
        }

        .countdown-sep span {
            display: block;
            width: 4px;
            height: 4px;
            background: var(--gray-400);
            border-radius: 50%;
        }

        /* ─── Footer ─── */
        .page-footer {
            font-size: 0.78rem;
            color: var(--gray-400);
            text-align: center;
            animation: fadeUp 0.7s 0.3s ease-out both;
        }

        .page-footer a {
            color: var(--blue);
            text-decoration: none;
        }

        /* ─── Animations ─── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Responsive ─── */
        @media (max-width: 560px) {
            .content-card {
                padding: 2.25rem 1.75rem;
                border-radius: 14px;
            }

            .main-heading {
                font-size: 1.65rem;
            }

            .countdown-box {
                min-width: 62px;
                padding: 0.75rem 0.4rem 0.6rem;
            }

            .countdown-num {
                font-size: 1.45rem;
            }
        }

        @media (max-width: 400px) {
            .content-card {
                padding: 2rem 1.25rem;
            }

            .countdown-timer {
                gap: 0.4rem;
            }

            .countdown-sep {
                display: none;
            }

            .countdown-box {
                min-width: 56px;
            }
        }
    </style>
</head>
<body>

<div class="page-wrap">

    <!-- Main card -->
    <main class="content-card" role="main">

        <?php if (!empty($whp_maintenance_logo)): ?>
        <!-- Site Logo replaces briefcase when set -->
        <div class="icon-section" aria-hidden="true">
            <img src="<?php echo esc_url($whp_maintenance_logo); ?>"
                 alt="<?php bloginfo('name'); ?>"
                 height="72"
                 style="max-height:72px; max-width:200px; object-fit:contain; display:block; margin:0 auto;">
        </div>
        <?php else: ?>
        <!-- Briefcase icon — shown only when no logo -->
        <div class="icon-section" aria-hidden="true">
            <div class="icon-bg">
                <svg class="icon-svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.6"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
        </div>
        <?php endif; ?>

        <!-- Status badge -->
        <div class="status-badge">
            <span class="status-dot" aria-hidden="true"></span>
            Scheduled Maintenance
        </div>

        <!-- Headings -->
        <h1 class="main-heading">
            <?php echo !empty($whp_maintenance_heading) ? esc_html($whp_maintenance_heading) : 'Website Đang Nâng Cấp'; ?>
        </h1>

        <?php if (!empty($whp_maintenance_heading_sub)): ?>
            <p class="sub-heading"><?php echo esc_html($whp_maintenance_heading_sub); ?></p>
        <?php endif; ?>

        <!-- Divider -->
        <div class="divider" aria-hidden="true">
            <span class="divider-line"></span>
            <span class="divider-dots">
                <span></span><span></span><span></span>
            </span>
            <span class="divider-line"></span>
        </div>

        <!-- Description -->
        <p class="description">
            <?php echo !empty($whp_maintenance_desc)
                ? nl2br(esc_html($whp_maintenance_desc))
                : 'Chúng tôi đang thực hiện bảo trì theo lịch để cải thiện hiệu suất và độ ổn định của hệ thống. Website sẽ hoạt động trở lại trong thời gian sớm nhất. Xin cảm ơn sự kiên nhẫn của quý khách.';
            ?>
        </p>

        <!-- CSS-only progress bar -->
        <div class="progress-section" role="progressbar" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100" aria-label="Tiến độ nâng cấp hệ thống">
            <div class="progress-label">
                <span class="progress-label-text">Tiến độ</span>
                <span class="progress-label-pct">72%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill"></div>
            </div>
        </div>

        <?php if (!empty($whp_maintenance_countdown)): ?>
            <!-- Countdown timer -->
            <div class="countdown-section" id="countdown-section" role="timer" aria-live="polite" aria-label="Thời gian còn lại">
                <p class="countdown-label-top">Dự kiến hoàn thành sau</p>
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

    </main>

    <!-- Footer -->
    <footer class="page-footer">
        &copy; <?php echo date('Y'); ?> Made in Vietnam by MWP Team. All Rights Reserved.
    </footer>

</div>

</body>
</html>
