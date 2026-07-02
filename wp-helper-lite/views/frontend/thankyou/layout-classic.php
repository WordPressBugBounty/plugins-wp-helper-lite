<?php defined('ABSPATH') || exit;
include __DIR__ . '/_common-vars.php';
?>
<div class="whp-ty whp-ty--classic">

    <!-- BANNER: horizontal row, flat, border-only with accent circles -->
    <div class="whp-ty__banner">
        <div class="whp-ty__banner-check">
            <svg width="38" height="38" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <circle cx="19" cy="19" r="19" fill="var(--whp-accent)" opacity=".12"/>
                <circle cx="19" cy="19" r="14" fill="var(--whp-accent)" opacity=".18"/>
                <polyline points="11,19 16,25 27,13" stroke="var(--whp-accent)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div>
            <h1 class="whp-ty__banner-title" style="font-size:17px;font-weight:700;color:#111827">
                <?php esc_html_e( 'Đặt hàng thành công!', 'whp' ); ?>
            </h1>
            <p class="whp-ty__banner-meta">
                <?php if ( $order_number ) : ?>
                <?php esc_html_e( 'Đơn hàng', 'whp' ); ?> #<?php echo esc_html( $order_number ); ?>
                <?php if ( $order_date ) : ?> &bull; <?php echo esc_html( $order_date ); ?><?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <?php include __DIR__ . '/_body-content.php'; ?>

</div>
