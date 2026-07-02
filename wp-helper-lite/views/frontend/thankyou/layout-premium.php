<?php
/**
 * WP Helper Lite — Thank You Page: Premium Layout
 * @package WP_Helper_Lite
 */
if ( ! defined( 'ABSPATH' ) ) exit;

include __DIR__ . '/_common-vars.php';
?>
<style>
:root {
    --whp-accent: <?php echo esc_attr( $accent ); ?>;
    --whp-accent2: <?php echo esc_attr( $settings['whp_woo_thankyou_color2'] ?? '#f3f0ff' ); ?>;
    --whp-bg: <?php echo esc_attr( $settings['whp_woo_thankyou_bg'] ?? '#f1f5f9' ); ?>;
    --whp-font: <?php echo esc_attr( $font_family ); ?>;
}

@keyframes whpShimmer {
    0%   { background-position: -400px 0; }
    100% { background-position:  400px 0; }
}
@keyframes whpFadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ===== Premium cards — shadow + fade-up ===== */
.whp-ty--premium .whp-ty__card {
    border-radius: 14px;
    box-shadow: 0 8px 28px rgba(15,23,42,.10), 0 0 0 1px rgba(0,0,0,.04);
    margin-bottom: 14px;
    animation: whpFadeUp .3s ease both;
    overflow: hidden;
}
.whp-ty--premium .whp-ty__card:nth-child(2) { animation-delay:.05s; }
.whp-ty--premium .whp-ty__card:nth-child(3) { animation-delay:.10s; }
.whp-ty--premium .whp-ty__card:nth-child(4) { animation-delay:.15s; }

/* ===== Card header — left accent border ===== */
.whp-ty--premium .whp-ty__card-header {
    display: flex;
    align-items: flex-start;
    border-bottom: none !important;
    background: transparent !important;
    border-left: 3px solid var(--whp-accent);
    padding: 0 0 0 9px;
    margin: 14px 16px 10px;
}
.whp-ty--premium .whp-ty__card-title {
    font-size: 12.5px !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    margin: 0 !important;
    padding: 0 !important;
}
.whp-ty--premium .whp-ty__card-desc {
    font-size: 11px !important;
    color: #94a3b8 !important;
    margin: 2px 0 0 !important;
}
.whp-ty--premium .whp-ty__card-icon { display: none !important; }

/* ===== Card body ===== */
.whp-ty--premium .whp-ty__card-body {
    padding: 0 16px 16px;
}

/* ===== Order status badge ===== */
.whp-ty__status-badge {
    display: inline-block;
    font-size: 12px;
    font-weight: 700;
    color: var(--whp-accent);
    background: color-mix(in srgb, var(--whp-accent) 10%, #fff);
    border: 1.5px solid color-mix(in srgb, var(--whp-accent) 25%, transparent);
    border-radius: 20px;
    padding: 5px 16px;
}

/* ===== Section divider ===== */
.whp-ty--premium .whp-ty__section-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 10px 0;
}

/* ===== Transfer button — full width, shimmer on hover ===== */
.whp-ty--premium #whp-ty-transfer-btn {
    width: 100%;
    max-width: 100%;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: .3px;
    padding: 14px 24px;
    justify-content: center;
    background: linear-gradient(
        90deg,
        var(--whp-accent) 0%,
        color-mix(in srgb, var(--whp-accent) 80%, #000) 50%,
        var(--whp-accent) 100%
    );
    background-size: 400px 100%;
    box-shadow: 0 6px 18px color-mix(in srgb, var(--whp-accent) 38%, transparent);
    margin-bottom: 0;
}
.whp-ty--premium #whp-ty-transfer-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 24px color-mix(in srgb, var(--whp-accent) 44%, transparent);
    animation: whpShimmer 1.2s linear infinite;
}
.whp-ty--premium #whp-ty-transfer-success {
    border-radius: 12px;
    padding: 16px;
    font-size: 14px;
}

/* ===== Countdown — clean centered ===== */
.whp-ty--premium .whp-ty__countdown {
    background: color-mix(in srgb, var(--whp-accent) 6%, #fff);
    border: 1.5px solid color-mix(in srgb, var(--whp-accent) 18%, transparent);
    border-radius: 14px;
    padding: 16px 20px;
    text-align: center;
    margin-bottom: 14px;
}
.whp-ty--premium .whp-ty__countdown-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
.whp-ty__countdown-clock {
    font-size: 28px;
    font-weight: 900;
    color: var(--whp-accent);
    letter-spacing: 3px;
    font-variant-numeric: tabular-nums;
    line-height: 1.2;
    margin: 4px 0;
}
.whp-ty__countdown-sublabel {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
    margin-top: 2px;
}
.whp-ty__countdown--warning .whp-ty__countdown-clock { color: #ef4444; }

/* ===== Trust badges — text-only pills ===== */
.whp-ty--premium .whp-ty__trust-pills {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 5px;
    margin-bottom: 14px;
}
.whp-ty__trust-pill {
    display: inline-flex;
    align-items: center;
    font-size: 10.5px;
    font-weight: 600;
    color: #475569;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 4px 11px;
}

/* ===== Action buttons — vertical column, outlined ===== */
.whp-ty--premium .whp-ty__actions {
    flex-direction: column !important;
    gap: 8px !important;
    margin-top: 0;
    margin-bottom: 14px;
}
.whp-ty--premium .whp-ty__actions .whp-ty__btn,
.whp-ty--premium .whp-ty__actions a.whp-ty__btn {
    width: 100% !important;
    justify-content: center !important;
    min-height: 44px !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    border: 2px solid var(--whp-accent) !important;
    color: var(--whp-accent) !important;
    background: #fff !important;
    border-radius: 12px !important;
    box-shadow: none !important;
}
.whp-ty--premium .whp-ty__actions .whp-ty__btn:hover,
.whp-ty--premium .whp-ty__actions a.whp-ty__btn:hover {
    background: color-mix(in srgb, var(--whp-accent) 8%, #fff) !important;
}
.whp-ty--premium .whp-ty__actions .whp-ty__btn svg { color: var(--whp-accent) !important; }

/* ===== Email note ===== */
.whp-ty__email-note {
    text-align: center;
    font-size: 12px;
    color: #94a3b8;
    margin: 2px 0 14px;
}

/* ===== Transfer section ===== */
.whp-ty__transfer-section { margin-bottom: 14px; }
</style>

<div class="whp-ty whp-ty--premium">

    <!-- ============================================================
         BANNER — white card, check icon, gradient pill badge
         ============================================================ -->
    <div class="whp-ty__banner">
        <div class="whp-ty__banner-check">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                 stroke="var(--whp-accent)" stroke-width="2.8" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <h1 class="whp-ty__banner-title">
            <?php esc_html_e( 'Cảm ơn bạn!', 'whp' ); ?>
        </h1>
        <p class="whp-ty__banner-meta">
            <?php esc_html_e( 'Đơn hàng đã được đặt thành công.', 'whp' ); ?>
        </p>
    </div>

    <?php include __DIR__ . '/_body-content.php'; ?>

</div><!-- /.whp-ty.whp-ty--premium -->
