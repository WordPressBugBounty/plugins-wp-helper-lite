<?php
/**
 * Shared body content — used by Classic, Modern, and Premium layouts.
 * Variables must be set by _common-vars.php before including this file.
 */
defined('ABSPATH') || exit;
?>

    <!-- ============================================================
         ORDER INFO CARD
         ============================================================ -->
    <div class="whp-ty__card">
        <div class="whp-ty__card-header">
            <div>
                <p class="whp-ty__card-title">
                    <?php esc_html_e( 'Thông tin đơn hàng', 'whp' ); ?>
                </p>
            </div>
        </div>
        <div class="whp-ty__card-body">

            <?php if ( $order_number ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Mã đơn hàng', 'whp' ); ?></span>
                <span class="whp-ty__payment-value">#<?php echo esc_html( $order_number ); ?></span>
            </div>
            <?php endif; ?>

            <?php if ( $order_date ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Ngày đặt hàng', 'whp' ); ?></span>
                <span class="whp-ty__payment-value"><?php echo esc_html( $order_date ); ?></span>
            </div>
            <?php endif; ?>

            <?php if ( $payment_title ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Phương thức', 'whp' ); ?></span>
                <span class="whp-ty__payment-value"><?php echo esc_html( $payment_title ); ?></span>
            </div>
            <?php endif; ?>

            <div class="whp-ty__section-divider"></div>

            <div style="text-align:center;padding:4px 0 2px">
                <span class="whp-ty__status-badge">
                    <?php echo esc_html( wc_get_order_status_name( $order_status ) ); ?>
                </span>
            </div>

        </div>
    </div>

    <!-- ============================================================
         VAT INVOICE CARD — shown when customer requested VAT invoice
         ============================================================ -->
    <?php
    if ( $order && empty( $is_admin_preview ) ) :
        $vat_requested = get_post_meta( $order->get_id(), 'mb_hpwc_invoice_vat_input', true );
        if ( $vat_requested ) :
            $vat_company = get_post_meta( $order->get_id(), 'billing_vat_company', true );
            $vat_tax     = get_post_meta( $order->get_id(), 'billing_vat_tax_code', true );
            $vat_addr    = get_post_meta( $order->get_id(), 'billing_vat_company_address', true );
    ?>
    <div class="whp-ty__card">
        <div class="whp-ty__card-header">
            <div>
                <p class="whp-ty__card-title" style="display:flex;align-items:center;gap:7px">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="flex-shrink:0"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/><line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="16" x2="12" y2="16"/></svg>
                    <?php esc_html_e( 'Thông tin xuất hóa đơn VAT', 'whp' ); ?>
                </p>
            </div>
        </div>
        <div class="whp-ty__card-body">
            <?php if ( $vat_company ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Tên công ty', 'whp' ); ?></span>
                <span class="whp-ty__payment-value"><?php echo esc_html( $vat_company ); ?></span>
            </div>
            <?php endif; ?>
            <?php if ( $vat_tax ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Mã số thuế', 'whp' ); ?></span>
                <span class="whp-ty__payment-value"><?php echo esc_html( $vat_tax ); ?></span>
            </div>
            <?php endif; ?>
            <?php if ( $vat_addr ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Địa chỉ', 'whp' ); ?></span>
                <span class="whp-ty__payment-value"><?php echo esc_html( $vat_addr ); ?></span>
            </div>
            <?php endif; ?>
            <div style="margin-top:10px;padding:10px 12px;background:#f0fdf4;border-radius:8px;font-size:12.5px;color:#15803d;display:flex;align-items:center;gap:7px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?php esc_html_e( 'Chúng tôi đã ghi nhận yêu cầu xuất hóa đơn VAT và sẽ gửi cho bạn sau khi xác nhận đơn hàng.', 'whp' ); ?>
            </div>
            <div style="margin-top:8px;padding:10px 12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:12.5px;color:#92400e;display:flex;align-items:flex-start;gap:7px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php esc_html_e( 'Lưu ý: Giá sản phẩm hiện chưa bao gồm thuế VAT. Phí VAT sẽ được tính thêm và chúng tôi sẽ liên hệ xác nhận với bạn trước khi xuất hóa đơn.', 'whp' ); ?>
            </div>
        </div>
    </div>
    <?php endif; endif; ?>

    <!-- ============================================================
         TIMELINE CARD (if enabled)
         ============================================================ -->
    <?php if ( $show_timeline ) : ?>
    <?php
    $step2_class = 'is-pending';
    if ( in_array( $order_status, [ 'pending', 'on-hold' ], true ) ) {
        $step2_class = 'is-current';
    } elseif ( in_array( $order_status, [ 'processing', 'completed' ], true ) ) {
        $step2_class = 'is-done';
    }
    $step3_class = 'is-pending';
    if ( 'processing' === $order_status ) {
        $step3_class = 'is-current';
    } elseif ( 'completed' === $order_status ) {
        $step3_class = 'is-done';
    }
    $step4_class = 'is-pending';
    if ( 'completed' === $order_status ) {
        $step4_class = 'is-done';
    }
    $step2_label = $is_cod
        ? __( 'Xác nhận COD', 'whp' )
        : __( 'Xác nhận TT', 'whp' );
    ?>
    <div class="whp-ty__card" data-rp="timeline">
        <div class="whp-ty__card-header">
            <div>
                <p class="whp-ty__card-title"><?php esc_html_e( 'Tình trạng đơn hàng', 'whp' ); ?></p>
            </div>
        </div>
        <div class="whp-ty__card-body" style="padding-top:14px">
            <div class="whp-ty__steps-h" role="list">
                <div class="whp-ty__sh-step is-done" role="listitem">
                    <div class="whp-ty__sh-dot" aria-hidden="true"></div>
                    <div class="whp-ty__sh-label"><?php esc_html_e( 'Đặt hàng', 'whp' ); ?></div>
                </div>
                <div class="whp-ty__sh-step <?php echo esc_attr( $step2_class ); ?>" role="listitem">
                    <div class="whp-ty__sh-dot" aria-hidden="true">
                        <?php echo 'is-done' === $step2_class ? '' : '2'; ?>
                    </div>
                    <div class="whp-ty__sh-label"><?php echo esc_html( $step2_label ); ?></div>
                </div>
                <div class="whp-ty__sh-step <?php echo esc_attr( $step3_class ); ?>" role="listitem">
                    <div class="whp-ty__sh-dot" aria-hidden="true">
                        <?php echo 'is-done' === $step3_class ? '' : '3'; ?>
                    </div>
                    <div class="whp-ty__sh-label"><?php esc_html_e( 'Đang xử lý', 'whp' ); ?></div>
                </div>
                <div class="whp-ty__sh-step <?php echo esc_attr( $step4_class ); ?>" role="listitem">
                    <div class="whp-ty__sh-dot" aria-hidden="true">
                        <?php echo 'is-done' === $step4_class ? '' : '4'; ?>
                    </div>
                    <div class="whp-ty__sh-label"><?php esc_html_e( 'Giao hàng', 'whp' ); ?></div>
                </div>
            </div>
            <?php if ( $order_tracking_url ) : ?>
            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #f1f5f9;text-align:center">
                <button
                    class="whp-ty-copy-btn whp-ty__btn whp-ty__btn--ghost"
                    data-copy="<?php echo esc_attr( $order_tracking_url ); ?>"
                    type="button"
                    style="font-size:13px;gap:6px;width:100%;justify-content:center"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    <?php esc_html_e( 'Sao chép đường dẫn theo dõi đơn hàng', 'whp' ); ?>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================================
         PAYMENT / WALLET CARD — QR centered at top, bank rows below
         ============================================================ -->
    <?php if ( $has_wallet ) : ?>
    <div class="whp-ty__card">
        <div class="whp-ty__card-header">
            <div>
                <p class="whp-ty__card-title">
                    <?php esc_html_e( 'Thông tin chuyển khoản', 'whp' ); ?>
                </p>
                <?php if ( $wallet_label ) : ?>
                <p class="whp-ty__card-desc"><?php echo esc_html( $wallet_label ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="whp-ty__card-body">

            <!-- QR — full size, centered at top of card (only shown when URL exists) -->
            <?php if ( $qr_url ) : ?>
            <div style="text-align:center;padding:4px 0 16px">
                <div style="display:inline-flex;align-items:center;justify-content:center;
                     background:color-mix(in srgb,var(--whp-accent) 6%,#fff);
                     border:1.5px solid color-mix(in srgb,var(--whp-accent) 20%,#e2e8f0);
                     border-radius:14px;padding:14px">
                    <img src="<?php echo esc_url( $qr_url ); ?>"
                         alt="<?php echo esc_attr( $wallet_label ); ?>"
                         style="width:160px;height:160px;object-fit:contain;border-radius:10px;display:block"
                         loading="lazy">
                </div>
                <div style="font-size:11px;color:#94a3b8;margin-top:8px;font-weight:500">
                    <?php esc_html_e( 'Quét mã QR để chuyển khoản', 'whp' ); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Ngân hàng -->
            <?php if ( $wallet_label ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Ngân hàng', 'whp' ); ?></span>
                <span class="whp-ty__payment-value" style="font-weight:700;display:flex;align-items:center;gap:6px">
                    <?php echo esc_html( $wallet_label ); ?>
                    <button type="button" class="whp-ty-copy-btn"
                            data-copy="<?php echo esc_attr( $wallet_label ); ?>"
                            aria-label="<?php esc_attr_e( 'Sao chép ngân hàng', 'whp' ); ?>">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </span>
            </div>
            <?php endif; ?>

            <!-- Số tài khoản -->
            <?php if ( $acct_number ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Số tài khoản', 'whp' ); ?></span>
                <span class="whp-ty__payment-value">
                    <?php echo esc_html( $acct_number ); ?>
                    <?php if ( $copy_account ) : ?>
                    <button type="button" class="whp-ty-copy-btn"
                            data-copy="<?php echo esc_attr( $acct_number ); ?>"
                            aria-label="<?php esc_attr_e( 'Sao chép số tài khoản', 'whp' ); ?>">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>

            <!-- Tên tài khoản -->
            <?php if ( $wallet_name ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Tên tài khoản', 'whp' ); ?></span>
                <span class="whp-ty__payment-value" style="font-weight:600;display:flex;align-items:center;gap:6px">
                    <?php echo esc_html( $wallet_name ); ?>
                    <button type="button" class="whp-ty-copy-btn"
                            data-copy="<?php echo esc_attr( $wallet_name ); ?>"
                            aria-label="<?php esc_attr_e( 'Sao chép tên tài khoản', 'whp' ); ?>">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </span>
            </div>
            <?php endif; ?>

            <!-- Nội dung CK — amber highlight row -->
            <?php if ( $transfer_cont ) : ?>
            <div class="whp-ty__payment-row" style="background:#fffbeb;border:1px dashed #f59e0b">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Nội dung CK', 'whp' ); ?></span>
                <span class="whp-ty__payment-value" style="color:#d97706;font-weight:700">
                    <?php echo esc_html( $transfer_cont ); ?>
                    <?php if ( $copy_content ) : ?>
                    <button type="button" class="whp-ty-copy-btn"
                            data-copy="<?php echo esc_attr( $transfer_cont ); ?>"
                            aria-label="<?php esc_attr_e( 'Sao chép nội dung', 'whp' ); ?>"
                            style="color:#d97706;background:#fef3c7;border-color:#fcd34d">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                    <?php endif; ?>
                </span>
            </div>
            <?php endif; ?>

            <!-- Số tiền -->
            <?php if ( $order ) : ?>
            <div class="whp-ty__payment-row">
                <span class="whp-ty__payment-label"><?php esc_html_e( 'Số tiền', 'whp' ); ?></span>
                <span class="whp-ty__payment-value"
                      style="font-size:14px;font-weight:900;color:var(--whp-accent);display:flex;align-items:center;gap:6px">
                    <?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?>
                    <button type="button" class="whp-ty-copy-btn"
                            data-copy="<?php echo esc_attr( $order->get_total() ); ?>"
                            aria-label="<?php esc_attr_e( 'Sao chép số tiền', 'whp' ); ?>">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                </span>
            </div>
            <?php endif; ?>

            <!-- Expired message -->
            <div id="whp-ty-expired-msg"
                 style="display:none;margin-top:12px;font-size:13px;color:#ef4444;text-align:center">
                <?php esc_html_e( 'Thời gian thanh toán đã hết. Đơn hàng của bạn đã bị hủy.', 'whp' ); ?>
            </div>

        </div>
    </div>

    <?php endif; ?>

    <!-- ============================================================
         ON-HOLD BANNER — payment confirmed by customer, awaiting admin
         Pre-rendered (hidden when pending) so JS can show instantly after confirm
         ============================================================ -->
    <?php if ( $has_wallet ) : ?>
    <?php $onhold_display = 'on-hold' === $order_status ? 'flex' : 'none'; ?>
    <div id="whp-ty-onhold-banner" style="display:<?php echo $onhold_display; ?>;align-items:flex-start;gap:12px;padding:14px 18px;background:#fffbeb;border:1.5px solid #fcd34d;border-radius:12px;margin-bottom:4px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px" aria-hidden="true">
            <circle cx="12" cy="12" r="10" fill="#f59e0b" fill-opacity=".15" stroke="#f59e0b" stroke-width="1.5"/>
            <path d="M12 8v4m0 4h.01" stroke="#d97706" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <div>
            <strong style="font-size:13px;color:#92400e;display:block;margin-bottom:2px;">Chờ xác nhận thanh toán</strong>
            <span style="font-size:12.5px;color:#b45309;line-height:1.5;">Chúng tôi đã nhận thông báo chuyển khoản của bạn và đang xác nhận. Đơn hàng sẽ được xử lý sớm nhất.</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================================
         AI VERIFICATION RESULT — shown when OCR is on and receipt was uploaded
         ============================================================ -->
    <?php
    $ocr_active_fe  = function_exists('whp_get_setting') && whp_get_setting('whp_aipay_ocr_enable') === '1';
    $receipt_meta   = $order ? $order->get_meta('_whp_transfer_receipt') : '';
    $ai_result_meta = $order ? $order->get_meta('_whp_ai_verify_result') : [];
    if ( $has_wallet && empty( $is_admin_preview ) && $ocr_active_fe && $receipt_meta ) :
        $ai_verdict = $ai_result_meta['verdict'] ?? '';
        $ai_conf    = isset($ai_result_meta['confidence']) ? (int)$ai_result_meta['confidence'] : 0;
        $ai_reason  = $ai_result_meta['verdict_reason'] ?? '';
        $ai_flags   = $ai_result_meta['risk_flags'] ?? [];
    ?>
    <div id="whp-ty-ai-result-wrap">
        <?php if ( $ai_verdict === 'valid' ) : ?>
        <div class="whp-ty-ai-result whp-ty-ai-result--ok">
            <div class="whp-ty-ai-result__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="whp-ty-ai-result__body">
                <div class="whp-ty-ai-result__title">AI đã xác minh thanh toán</div>
                <?php if ($ai_conf) : ?>
                <div class="whp-ty-ai-result__sub">Độ tin cậy: <?php echo esc_html($ai_conf); ?>%</div>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ( in_array($ai_verdict, ['invalid','suspicious'], true) ) : ?>
        <div class="whp-ty-ai-result whp-ty-ai-result--fail">
            <div class="whp-ty-ai-result__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="whp-ty-ai-result__body">
                <div class="whp-ty-ai-result__title">Biên lai cần xem lại</div>
                <?php if ($ai_reason) : ?>
                <div class="whp-ty-ai-result__sub"><?php echo esc_html($ai_reason); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php else : ?>
        <!-- No AI result yet — JS will auto-trigger if conditions met -->
        <div id="whp-ty-ai-pending" style="display:none">
            <div class="whp-ty-ai-result whp-ty-ai-result--scanning">
                <div class="whp-ty-ai-result__icon whp-ty-ai-result__icon--spin">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </div>
                <div class="whp-ty-ai-result__body">
                    <div class="whp-ty-ai-result__title">AI đang xác minh biên lai...</div>
                    <div class="whp-ty-ai-result__sub" id="whp-ty-ai-pending-pct">0%</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <style>
    .whp-ty-ai-result { display:flex; align-items:center; gap:12px; padding:12px 16px; border-radius:10px; margin-bottom:8px; }
    .whp-ty-ai-result--ok  { background:#f0fdf4; border:1px solid #86efac; }
    .whp-ty-ai-result--fail{ background:#fef2f2; border:1px solid #fca5a5; }
    .whp-ty-ai-result--scanning { background:#eff6ff; border:1px solid #93c5fd; }
    .whp-ty-ai-result__icon { width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .whp-ty-ai-result--ok   .whp-ty-ai-result__icon { background:#dcfce7; }
    .whp-ty-ai-result--fail .whp-ty-ai-result__icon { background:#fee2e2; }
    .whp-ty-ai-result--scanning .whp-ty-ai-result__icon { background:#dbeafe; }
    .whp-ty-ai-result__icon--spin svg { animation:whp-spin 1s linear infinite; }
    @keyframes whp-spin { to { transform:rotate(360deg); } }
    .whp-ty-ai-result__title { font-size:13.5px; font-weight:700; line-height:1.3; }
    .whp-ty-ai-result--ok   .whp-ty-ai-result__title { color:#15803d; }
    .whp-ty-ai-result--fail .whp-ty-ai-result__title { color:#b91c1c; }
    .whp-ty-ai-result--scanning .whp-ty-ai-result__title { color:#1d4ed8; }
    .whp-ty-ai-result__sub { font-size:12px; color:#64748b; margin-top:2px; }
    </style>
    <?php endif; ?>

    <!-- ============================================================
         COUNTDOWN
         ============================================================ -->
    <?php if ( $show_countdown ) : ?>
    <div class="whp-ty__countdown" id="whp-ty-countdown" data-rp="countdown">
        <div class="whp-ty__countdown-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <?php printf(
                esc_html__( 'Vui lòng chuyển khoản trong vòng %d phút', 'whp' ),
                (int) $countdown_min
            ); ?>
        </div>
        <div class="whp-ty__countdown-clock" id="whp-ty-countdown-display">--:--:--</div>
        <div class="whp-ty__countdown-sublabel"><?php esc_html_e( 'Giờ · Phút · Giây', 'whp' ); ?></div>
    </div>
    <?php endif; ?>

    <!-- ============================================================
         TRANSFER BUTTON — full width, outside card
         ============================================================ -->
    <?php if ( $show_transfer && $has_wallet && $order && in_array( $order_status, [ 'pending', 'on-hold' ], true ) ) : ?>
    <div class="whp-ty__transfer-section" data-rp="transfer">
        <button type="button" id="whp-ty-transfer-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                 stroke-linejoin="round" style="margin-right:8px" aria-hidden="true">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <?php esc_html_e( 'Tôi đã chuyển khoản', 'whp' ); ?>
        </button>
        <div id="whp-ty-transfer-success" style="display:none">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                 stroke-linejoin="round" style="margin-right:6px" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <?php esc_html_e( 'Cảm ơn! Chúng tôi đã nhận thông báo và sẽ xác nhận sớm nhất.', 'whp' ); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============================================================
         TRUST BADGES — text-only pills
         ============================================================ -->
    <?php if ( $trust_badges ) : ?>
    <div class="whp-ty__trust-pills" data-rp="trust">
        <span class="whp-ty__trust-pill">
            <svg class="whp-ty__trust-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 1.5L2 4v4c0 3.3 2.5 6.2 6 7 3.5-.8 6-3.7 6-7V4L8 1.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><polyline points="5.5,8.5 7,10 10.5,6.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php echo esc_html( __( 'Bảo mật', 'whp' ) ); ?>
        </span>
        <span class="whp-ty__trust-pill">
            <svg class="whp-ty__trust-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="1" y="5" width="11" height="7" rx="1.2" stroke="currentColor" stroke-width="1.4"/><path d="M12 7h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2" stroke="currentColor" stroke-width="1.4"/><circle cx="4.5" cy="13.5" r="1.2" stroke="currentColor" stroke-width="1.2"/><circle cx="9.5" cy="13.5" r="1.2" stroke="currentColor" stroke-width="1.2"/></svg>
            <?php echo esc_html( __( 'Free ship', 'whp' ) ); ?>
        </span>
        <span class="whp-ty__trust-pill">
            <svg class="whp-ty__trust-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2.5 9A5.5 5.5 0 1 0 4 5H2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><polyline points="2,2 2,5 5,5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php echo esc_html( __( 'Đổi trả', 'whp' ) ); ?>
        </span>
        <span class="whp-ty__trust-pill">
            <svg class="whp-ty__trust-icon" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="6.2" stroke="currentColor" stroke-width="1.4"/><polyline points="8,4.5 8,8.5 10.5,8.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php echo esc_html( __( '24/7', 'whp' ) ); ?>
        </span>
    </div>
    <?php endif; ?>

    <!-- ============================================================
         ACTION BUTTONS — vertical column, outlined
         ============================================================ -->
    <div class="whp-ty__actions">

        <?php if ( $btn_continue ) : ?>
        <a href="<?php echo esc_url( $shop_url ); ?>" class="whp-ty__btn" data-rp="btn-continue">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </svg>
            <?php echo esc_html( $btn_continue ); ?>
        </a>
        <?php endif; ?>

        <?php if ( $btn_view_order ) : ?>
        <a href="<?php echo esc_url( $orders_url ); ?>" class="whp-ty__btn" data-rp="btn-view">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <?php echo esc_html( $btn_view_order ); ?>
        </a>
        <?php endif; ?>

        <?php if ( $show_support && $btn_contact ) : ?>
        <button type="button" id="whp-ty-support-btn" class="whp-ty__btn" data-rp="btn-contact">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <?php echo esc_html( $btn_contact ); ?>
        </button>
        <?php endif; ?>

        <?php if ( $btn_invoice ) :
            $inv_items = $order ? array_values( $order->get_items() ) : [];
            $inv_name  = ! empty( $inv_items ) ? $inv_items[0]->get_name() : '';
            $inv_title = $inv_name
                ? mb_substr( sanitize_text_field( $inv_name ), 0, 40 ) . ' - DH' . $order_number
                : 'DH' . $order_number;
        ?>
        <button type="button" id="whp-ty-invoice-btn" class="whp-ty__btn" data-rp="btn-invoice"
                data-print-title="<?php echo esc_attr( $inv_title ); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" aria-hidden="true">
                <polyline points="6 9 6 2 18 2 18 9"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <rect x="6" y="14" width="12" height="8"/>
            </svg>
            <?php echo esc_html( $btn_invoice ); ?>
        </button>
        <?php endif; ?>

    </div>

    <!-- Email note -->
    <p class="whp-ty__email-note">
        <?php esc_html_e( 'Chúng tôi sẽ gửi thông tin đơn hàng đến email của bạn.', 'whp' ); ?>
    </p>


    <!-- ============================================================
         SUPPORT MODAL — contact channels
         ============================================================ -->
    <?php if ( $show_support && empty( $is_admin_preview ) ) : ?>
    <div id="whp-ty-modal" class="whp-ty__modal" role="dialog" aria-modal="true"
         aria-labelledby="whp-ty-modal-title">
        <div class="whp-ty__modal-box">
            <button type="button" id="whp-ty-modal-close" class="whp-ty__modal-close"
                    aria-label="<?php esc_attr_e( 'Đóng', 'whp' ); ?>">&times;</button>
            <div class="whp-ty__modal-hd">
                <h2 class="whp-ty__modal-title" id="whp-ty-modal-title">
                    <?php esc_html_e( 'Liên hệ hỗ trợ', 'whp' ); ?>
                </h2>
            </div>
            <p style="font-size:13px;color:#64748b;margin:0 0 18px">
                <?php esc_html_e( 'Chọn kênh liên hệ phù hợp để được hỗ trợ nhanh nhất.', 'whp' ); ?>
            </p>
            <div class="whp-ty__contact-chs">
            <?php
            // Zalo
            if ( $contact_channels['zalo']['en'] && $contact_channels['zalo']['val'] !== '' ) :
                $zalo_num = preg_replace( '/\D/', '', $contact_channels['zalo']['val'] );
            ?>
            <a href="https://zalo.me/<?php echo esc_attr( $zalo_num ); ?>" target="_blank" rel="noopener" class="whp-ty__contact-ch">
                <span class="whp-ty__contact-ch-icon" style="background:#e6f7ff;color:#0068ff">
                    <svg width="22" height="22" viewBox="0 0 48 48" fill="currentColor"><path d="M24 4C13 4 4 12.95 4 24c0 5.3 1.9 10.1 5 13.8L4 44l6.5-4.8C13.9 41.5 18.8 43 24 43c11 0 20-8.95 20-19S35 4 24 4z"/><path fill="#fff" d="M14.5 28.5l5-7.5 3.2 4.3 4.3-5.8 5 8H14.5z"/></svg>
                </span>
                <span class="whp-ty__contact-ch-info">
                    <span class="whp-ty__contact-ch-name">Zalo</span>
                    <span class="whp-ty__contact-ch-val"><?php echo esc_html( $contact_channels['zalo']['val'] ); ?></span>
                </span>
                <svg class="whp-ty__contact-ch-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <?php endif; ?>

            <?php
            // Facebook
            if ( $contact_channels['fb']['en'] && $contact_channels['fb']['val'] !== '' ) :
            ?>
            <a href="<?php echo esc_url( $contact_channels['fb']['val'] ); ?>" target="_blank" rel="noopener" class="whp-ty__contact-ch">
                <span class="whp-ty__contact-ch-icon" style="background:#e8f0fe;color:#1877f2">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </span>
                <span class="whp-ty__contact-ch-info">
                    <span class="whp-ty__contact-ch-name">Facebook</span>
                    <span class="whp-ty__contact-ch-val"><?php echo esc_html( $contact_channels['fb']['val'] ); ?></span>
                </span>
                <svg class="whp-ty__contact-ch-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <?php endif; ?>

            <?php
            // Messenger
            if ( $contact_channels['msg']['en'] && $contact_channels['msg']['val'] !== '' ) :
            ?>
            <a href="<?php echo esc_url( $contact_channels['msg']['val'] ); ?>" target="_blank" rel="noopener" class="whp-ty__contact-ch">
                <span class="whp-ty__contact-ch-icon" style="background:#f3e8ff;color:#a855f7">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.145 2 11.243c0 3.063 1.53 5.789 3.924 7.565v3.692l3.579-1.966C10.287 20.836 11.128 21 12 21c5.523 0 10-4.145 10-9.757C22 6.145 17.523 2 12 2zm1 13.143l-2.548-2.714-4.973 2.714 5.468-5.802 2.61 2.714 4.912-2.714-5.47 5.802z"/></svg>
                </span>
                <span class="whp-ty__contact-ch-info">
                    <span class="whp-ty__contact-ch-name">Messenger</span>
                    <span class="whp-ty__contact-ch-val"><?php echo esc_html( $contact_channels['msg']['val'] ); ?></span>
                </span>
                <svg class="whp-ty__contact-ch-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <?php endif; ?>

            <?php
            // Email
            if ( $contact_channels['email']['en'] && $contact_channels['email']['val'] !== '' ) :
            ?>
            <a href="mailto:<?php echo esc_attr( $contact_channels['email']['val'] ); ?>" class="whp-ty__contact-ch">
                <span class="whp-ty__contact-ch-icon" style="background:#fee2e2;color:#ef4444">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </span>
                <span class="whp-ty__contact-ch-info">
                    <span class="whp-ty__contact-ch-name">Email</span>
                    <span class="whp-ty__contact-ch-val"><?php echo esc_html( $contact_channels['email']['val'] ); ?></span>
                </span>
                <svg class="whp-ty__contact-ch-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <?php endif; ?>

            <?php if ( ! $has_any_contact_channel ) : ?>
            <p style="text-align:center;color:#94a3b8;font-size:13px;padding:16px 0">
                <?php esc_html_e( 'Chưa cấu hình kênh liên hệ. Vui lòng liên hệ quản trị viên.', 'whp' ); ?>
            </p>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

<!-- ============================================================
     PRINT INVOICE (screen: hidden, print: only this shows)
     ============================================================ -->
<?php if ( $order && empty( $is_admin_preview ) ) :
    $inv_items_all   = array_values( $order->get_items() );
    $inv_order_date  = $order->get_date_created()
        ? $order->get_date_created()->date_i18n( get_option( 'date_format' ) )
        : '';
    $inv_method      = $order->get_payment_method_title();
    $inv_status      = wc_get_order_status_name( $order->get_status() );
    $inv_bill_name   = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
?>
<div class="whp-ty__print-invoice" aria-hidden="true">
    <div class="whp-ty__invoice-inner">

        <div class="whp-ty__invoice-hdr">
            <div>
                <div class="whp-ty__invoice-store"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
                <h1 class="whp-ty__invoice-h1">HÓA ĐƠN</h1>
            </div>
            <div>
                <div class="whp-ty__invoice-num">#<?php echo esc_html( $order_number ); ?></div>
                <div class="whp-ty__invoice-date"><?php echo esc_html( $inv_order_date ); ?></div>
            </div>
        </div>

        <div class="whp-ty__invoice-info">
            <div>
                <div class="whp-ty__invoice-lbl">KHÁCH HÀNG</div>
                <div class="whp-ty__invoice-val">
                    <strong><?php echo esc_html( $inv_bill_name ); ?></strong><br>
                    <?php if ( $order->get_billing_email() ) : ?>
                    <?php echo esc_html( $order->get_billing_email() ); ?><br>
                    <?php endif; ?>
                    <?php if ( $order->get_billing_phone() ) : ?>
                    <?php echo esc_html( $order->get_billing_phone() ); ?><br>
                    <?php endif; ?>
                    <?php
                    $inv_addr = $order->get_billing_address_1();
                    if ( $order->get_billing_city() ) $inv_addr .= ', ' . $order->get_billing_city();
                    echo esc_html( $inv_addr );
                    ?>
                </div>
            </div>
            <div>
                <div class="whp-ty__invoice-lbl">THANH TOÁN</div>
                <div class="whp-ty__invoice-val">
                    Phương thức: <strong><?php echo esc_html( $inv_method ); ?></strong><br>
                    Trạng thái: <strong><?php echo esc_html( $inv_status ); ?></strong>
                </div>
            </div>
        </div>

        <table class="whp-ty__invoice-tbl">
            <thead>
                <tr>
                    <th class="whp-ty__invoice-th" style="text-align:left">Sản phẩm</th>
                    <th class="whp-ty__invoice-th" style="text-align:center;width:48px">SL</th>
                    <th class="whp-ty__invoice-th" style="text-align:right">Đơn giá</th>
                    <th class="whp-ty__invoice-th" style="text-align:right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $inv_items_all as $inv_item ) :
                    $inv_product = $inv_item->get_product();
                    $inv_qty     = (int) $inv_item->get_quantity();
                    $inv_unit    = $inv_qty > 0 ? $inv_item->get_subtotal() / $inv_qty : 0;
                ?>
                <tr>
                    <td class="whp-ty__invoice-td">
                        <?php echo esc_html( $inv_item->get_name() ); ?>
                        <?php if ( $inv_product && $inv_product->get_sku() ) : ?>
                        <div class="whp-ty__invoice-sku">SKU: <?php echo esc_html( $inv_product->get_sku() ); ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="whp-ty__invoice-td" style="text-align:center"><?php echo $inv_qty; ?></td>
                    <td class="whp-ty__invoice-td" style="text-align:right"><?php echo wp_kses_post( wc_price( $inv_unit ) ); ?></td>
                    <td class="whp-ty__invoice-td" style="text-align:right;font-weight:600"><?php echo wp_kses_post( wc_price( $inv_item->get_subtotal() ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="whp-ty__invoice-totals">
            <?php if ( $order->get_total_discount() > 0 ) : ?>
            <div class="whp-ty__invoice-trow">
                <span>Giảm giá</span>
                <span>-<?php echo wp_kses_post( wc_price( $order->get_total_discount() ) ); ?></span>
            </div>
            <?php endif; ?>
            <?php if ( $order->get_shipping_total() > 0 ) : ?>
            <div class="whp-ty__invoice-trow">
                <span>Phí vận chuyển</span>
                <span><?php echo wp_kses_post( wc_price( $order->get_shipping_total() ) ); ?></span>
            </div>
            <?php endif; ?>
            <div class="whp-ty__invoice-trow whp-ty__invoice-trow--grand">
                <span>TỔNG CỘNG</span>
                <span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
            </div>
        </div>

        <div class="whp-ty__invoice-foot">
            Cảm ơn bạn đã mua hàng! &bull; <?php echo esc_html( get_bloginfo( 'name' ) ); ?> &bull; <?php echo esc_url( home_url() ); ?>
        </div>

    </div>
</div>
<?php endif; ?>

<?php if ( empty( $is_admin_preview ) ) : ?>
<?php include __DIR__ . '/_transfer-form-modal.php'; ?>
<?php endif; ?>
