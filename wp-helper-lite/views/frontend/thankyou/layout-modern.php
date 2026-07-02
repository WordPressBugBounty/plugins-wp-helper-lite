<?php defined('ABSPATH') || exit;
include __DIR__ . '/_common-vars.php';
?>
<div class="whp-ty whp-ty--modern">

    <!-- BANNER: full gradient, centered, large, airy -->
    <div class="whp-ty__banner">
        <div class="whp-ty__banner-check">
            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="28" cy="28" r="28" fill="rgba(255,255,255,0.15)"/>
                <circle cx="28" cy="28" r="22" fill="rgba(255,255,255,0.22)"/>
                <polyline points="17,28 24,36 39,20" stroke="white" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h1 class="whp-ty__banner-title" style="font-size:22px;font-weight:800;margin:14px 0 6px;">
            <?php esc_html_e( 'Đặt hàng thành công!', 'whp' ); ?>
        </h1>
        <p class="whp-ty__banner-meta">
            <?php if ( $order_number ) : ?>
            <?php esc_html_e( 'Đơn hàng', 'whp' ); ?> #<?php echo esc_html( $order_number ); ?>
            <?php if ( $order_date ) : ?> &bull; <?php echo esc_html( $order_date ); ?><?php endif; ?>
            <?php endif; ?>
        </p>
    </div>

    <?php include __DIR__ . '/_body-content.php'; ?>

</div>
