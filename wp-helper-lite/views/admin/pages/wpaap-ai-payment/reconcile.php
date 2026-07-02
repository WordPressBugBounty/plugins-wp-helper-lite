<?php
/**
 * AI Payment — Đối soát ngân hàng (Bank Reconciliation) sub-tab
 *
 * Placeholder for v3.0 banking reconciliation features.
 * Integrations: Casso, SePay, PayOS
 *
 * @package WP_Helper_Lite
 * @since   3.0.0 (placeholder)
 */

defined('ABSPATH') || exit;

function wpaap_aipay_reconcile_layout()
{
    ?>
    <style>
    /* ── Reconcile page scoped styles ── */
    .wpaap-recon-wrap {
        max-width: 1200px;
        margin: 28px auto 48px;
        padding: 0 16px;
        box-sizing: border-box;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* ── Core-principle banner ── */
    .wpaap-recon-principle {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 28px;
        display: flex;
        align-items: flex-start;
        gap: 18px;
        box-shadow: 0 4px 20px rgba(15,23,42,0.18);
        position: relative;
        overflow: hidden;
    }
    .wpaap-recon-principle::before {
        content: "";
        position: absolute;
        inset: 0;
        background: repeating-linear-gradient(
            -45deg,
            transparent,
            transparent 18px,
            rgba(255,255,255,0.025) 18px,
            rgba(255,255,255,0.025) 36px
        );
        pointer-events: none;
    }
    .wpaap-recon-principle-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(239,68,68,0.18);
        border: 1px solid rgba(239,68,68,0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .wpaap-recon-principle-body { position: relative; z-index: 1; }
    .wpaap-recon-principle-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #ef4444;
        margin-bottom: 8px;
    }
    .wpaap-recon-principle-text {
        font-size: 15px;
        font-weight: 700;
        color: #f1f5f9;
        line-height: 1.55;
        margin: 0 0 6px;
    }
    .wpaap-recon-principle-sub {
        font-size: 13px;
        color: rgba(241,245,249,0.65);
        margin: 0;
        line-height: 1.6;
    }

    /* ── Verification flow ── */
    .wpaap-recon-flow {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 28px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    .wpaap-recon-flow-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        margin-bottom: 18px;
    }
    .wpaap-recon-flow-steps {
        display: flex;
        align-items: center;
        gap: 0;
        flex-wrap: wrap;
        row-gap: 12px;
    }
    .wpaap-recon-step {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 130px;
    }
    .wpaap-recon-step-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .wpaap-recon-step-info { flex: 1; }
    .wpaap-recon-step-name {
        font-size: 13px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .wpaap-recon-step-desc {
        font-size: 11.5px;
        color: #64748b;
        line-height: 1.4;
        margin-top: 2px;
    }
    .wpaap-recon-arrow {
        color: #cbd5e1;
        font-size: 18px;
        padding: 0 4px;
        flex-shrink: 0;
        align-self: center;
    }
    @media (max-width: 600px) {
        .wpaap-recon-arrow { display: none; }
        .wpaap-recon-step { min-width: 100%; flex: none; }
    }

    /* Step colour variants */
    .wpaap-recon-step--ocr .wpaap-recon-step-num {
        background: #eff6ff; color: #2563eb; border: 1.5px solid #bfdbfe;
    }
    .wpaap-recon-step--fraud .wpaap-recon-step-num {
        background: #fff7ed; color: #ea580c; border: 1.5px solid #fed7aa;
    }
    .wpaap-recon-step--bank .wpaap-recon-step-num {
        background: #f0fdf4; color: #16a34a; border: 1.5px solid #bbf7d0;
    }
    .wpaap-recon-step--confirm .wpaap-recon-step-num {
        background: #f5f3ff; color: #7c3aed; border: 1.5px solid #ddd6fe;
    }

    /* ── Integration cards grid ── */
    .wpaap-recon-section-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        margin-bottom: 16px;
        padding-left: 2px;
    }
    .wpaap-recon-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    @media (max-width: 780px) {
        .wpaap-recon-cards { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 500px) {
        .wpaap-recon-cards { grid-template-columns: 1fr; }
    }

    .wpaap-recon-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px 20px 20px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: box-shadow 0.2s, border-color 0.2s;
        position: relative;
        overflow: hidden;
    }
    .wpaap-recon-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        border-color: #c7d2fe;
    }
    .wpaap-recon-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }
    .wpaap-recon-logo {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .wpaap-recon-logo--casso  { background: #eff6ff; border: 1px solid #bfdbfe; }
    .wpaap-recon-logo--sepay  { background: #fff7ed; border: 1px solid #fed7aa; }
    .wpaap-recon-logo--payos  { background: #f0fdf4; border: 1px solid #bbf7d0; }

    .wpaap-recon-badge-soon {
        font-size: 10.5px;
        font-weight: 700;
        color: #7c3aed;
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        border-radius: 20px;
        padding: 3px 9px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .wpaap-recon-card-name {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px;
    }
    .wpaap-recon-card-tagline {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin: 0 0 2px;
    }
    .wpaap-recon-card-desc {
        font-size: 13px;
        color: #475569;
        line-height: 1.6;
        margin: 0;
        flex: 1;
    }
    .wpaap-recon-card-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 4px;
    }
    .wpaap-recon-chip {
        font-size: 11px;
        font-weight: 600;
        background: #f1f5f9;
        color: #475569;
        border-radius: 6px;
        padding: 3px 8px;
        border: 1px solid #e2e8f0;
    }

    /* ── Enterprise notice ── */
    .wpaap-recon-enterprise {
        background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%);
        border: 1px solid #fde68a;
        border-radius: 14px;
        padding: 20px 24px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .wpaap-recon-enterprise-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #fef08a;
        border: 1px solid #fde047;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 18px;
    }
    .wpaap-recon-enterprise-title {
        font-size: 14px;
        font-weight: 800;
        color: #713f12;
        margin-bottom: 5px;
    }
    .wpaap-recon-enterprise-body {
        font-size: 13px;
        color: #92400e;
        line-height: 1.6;
        margin: 0;
    }
    </style>

    <div class="wpaap-recon-wrap">

        <!-- Core Principle Banner -->
        <div class="wpaap-recon-principle">
            <div class="wpaap-recon-principle-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                     stroke="#ef4444" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div class="wpaap-recon-principle-body">
                <div class="wpaap-recon-principle-label"><?php esc_html_e( 'Nguyên tắc bảo mật cốt lõi', 'whp' ); ?></div>
                <p class="wpaap-recon-principle-text"><?php echo wp_kses_post( __( 'Không xác nhận thanh toán chỉ dựa trên ảnh biên lai.<br>Nguồn xác thực cuối cùng PHẢI là giao dịch thực tế từ ngân hàng.', 'whp' ) ); ?></p>
                <p class="wpaap-recon-principle-sub"><?php esc_html_e( 'Ảnh biên lai chỉ là gợi ý ban đầu. Hệ thống sẽ luôn đối soát với dữ liệu ngân hàng thực trước khi xác nhận đơn hàng.', 'whp' ); ?></p>
            </div>
        </div>

        <!-- Verification Flow -->
        <div class="wpaap-recon-flow">
            <div class="wpaap-recon-flow-title"><?php esc_html_e( 'Luồng xác minh thanh toán', 'whp' ); ?></div>
            <div class="wpaap-recon-flow-steps">

                <div class="wpaap-recon-step wpaap-recon-step--ocr">
                    <div class="wpaap-recon-step-num">1</div>
                    <div class="wpaap-recon-step-info">
                        <div class="wpaap-recon-step-name"><?php esc_html_e( 'Biên lai (OCR)', 'whp' ); ?></div>
                        <div class="wpaap-recon-step-desc"><?php esc_html_e( 'Trích xuất số tiền, STK, thời gian từ ảnh', 'whp' ); ?></div>
                    </div>
                </div>

                <div class="wpaap-recon-arrow">&#8594;</div>

                <div class="wpaap-recon-step wpaap-recon-step--fraud">
                    <div class="wpaap-recon-step-num">2</div>
                    <div class="wpaap-recon-step-info">
                        <div class="wpaap-recon-step-name">AI Fraud Score</div>
                        <div class="wpaap-recon-step-desc"><?php esc_html_e( 'Phát hiện ảnh giả, chỉnh sửa Photoshop', 'whp' ); ?></div>
                    </div>
                </div>

                <div class="wpaap-recon-arrow">&#8594;</div>

                <div class="wpaap-recon-step wpaap-recon-step--bank">
                    <div class="wpaap-recon-step-num">3</div>
                    <div class="wpaap-recon-step-info">
                        <div class="wpaap-recon-step-name"><?php esc_html_e( 'Đối soát ngân hàng', 'whp' ); ?></div>
                        <div class="wpaap-recon-step-desc"><?php esc_html_e( 'Khớp với sao kê thực từ Casso / SePay / PayOS', 'whp' ); ?></div>
                    </div>
                </div>

                <div class="wpaap-recon-arrow">&#8594;</div>

                <div class="wpaap-recon-step wpaap-recon-step--confirm">
                    <div class="wpaap-recon-step-num">4</div>
                    <div class="wpaap-recon-step-info">
                        <div class="wpaap-recon-step-name"><?php esc_html_e( 'Xác nhận thanh toán', 'whp' ); ?></div>
                        <div class="wpaap-recon-step-desc"><?php esc_html_e( 'Auto-confirm đơn hàng khi đủ điều kiện', 'whp' ); ?></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Integration Cards -->
        <div class="wpaap-recon-section-label"><?php esc_html_e( 'Tích hợp ngân hàng được hỗ trợ', 'whp' ); ?></div>
        <div class="wpaap-recon-cards">

            <!-- Casso -->
            <div class="wpaap-recon-card">
                <div class="wpaap-recon-card-top">
                    <div class="wpaap-recon-logo wpaap-recon-logo--casso">&#127981;</div>
                    <span class="wpaap-recon-badge-soon"><?php esc_html_e( 'Sắp ra mắt', 'whp' ); ?></span>
                </div>
                <div>
                    <p class="wpaap-recon-card-name">Casso</p>
                    <p class="wpaap-recon-card-tagline">Auto-receive transaction webhooks</p>
                    <p class="wpaap-recon-card-desc"><?php esc_html_e( 'Nhận webhook tự động từ các ngân hàng Việt Nam qua Casso. Giao dịch tiền về ngay lập tức được đẩy vào hệ thống để đối soát và xác nhận đơn hàng.', 'whp' ); ?></p>
                </div>
                <div class="wpaap-recon-card-chips">
                    <span class="wpaap-recon-chip">Webhook real-time</span>
                    <span class="wpaap-recon-chip"><?php esc_html_e( '30+ ngân hàng VN', 'whp' ); ?></span>
                    <span class="wpaap-recon-chip">Auto-confirm</span>
                </div>
            </div>

            <!-- SePay -->
            <div class="wpaap-recon-card">
                <div class="wpaap-recon-card-top">
                    <div class="wpaap-recon-logo wpaap-recon-logo--sepay">&#128179;</div>
                    <span class="wpaap-recon-badge-soon"><?php esc_html_e( 'Sắp ra mắt', 'whp' ); ?></span>
                </div>
                <div>
                    <p class="wpaap-recon-card-name">SePay</p>
                    <p class="wpaap-recon-card-tagline">Automatic bank statement sync</p>
                    <p class="wpaap-recon-card-desc"><?php esc_html_e( 'Cổng thanh toán với đồng bộ sao kê ngân hàng tự động. Hỗ trợ tạo mã QR động và đối soát thanh toán theo nội dung chuyển khoản.', 'whp' ); ?></p>
                </div>
                <div class="wpaap-recon-card-chips">
                    <span class="wpaap-recon-chip"><?php esc_html_e( 'QR động', 'whp' ); ?></span>
                    <span class="wpaap-recon-chip"><?php esc_html_e( 'Sao kê tự động', 'whp' ); ?></span>
                    <span class="wpaap-recon-chip"><?php esc_html_e( 'Nội dung CK', 'whp' ); ?></span>
                </div>
            </div>

            <!-- PayOS -->
            <div class="wpaap-recon-card">
                <div class="wpaap-recon-card-top">
                    <div class="wpaap-recon-logo wpaap-recon-logo--payos">&#9989;</div>
                    <span class="wpaap-recon-badge-soon"><?php esc_html_e( 'Sắp ra mắt', 'whp' ); ?></span>
                </div>
                <div>
                    <p class="wpaap-recon-card-name">PayOS</p>
                    <p class="wpaap-recon-card-tagline">Real-time transaction verification</p>
                    <p class="wpaap-recon-card-desc"><?php esc_html_e( 'Hệ thống thanh toán với xác minh giao dịch theo thời gian thực. Tích hợp API ngân hàng trực tiếp giúp xác nhận chính xác từng khoản tiền vào.', 'whp' ); ?></p>
                </div>
                <div class="wpaap-recon-card-chips">
                    <span class="wpaap-recon-chip"><?php esc_html_e( 'API ngân hàng', 'whp' ); ?></span>
                    <span class="wpaap-recon-chip">Real-time verify</span>
                    <span class="wpaap-recon-chip"><?php esc_html_e( 'VietQR chuẩn', 'whp' ); ?></span>
                </div>
            </div>

        </div>

        <!-- Enterprise Tier Notice -->
        <div class="wpaap-recon-enterprise">
            <div class="wpaap-recon-enterprise-icon">&#11088;</div>
            <div>
                <div class="wpaap-recon-enterprise-title"><?php esc_html_e( 'Yêu cầu gói Enterprise', 'whp' ); ?></div>
                <p class="wpaap-recon-enterprise-body"><?php echo wp_kses_post( sprintf( __( 'Tính năng đối soát ngân hàng yêu cầu gói %s. Tích hợp với Casso, SePay và PayOS cần API key riêng và được bảo mật bằng mã hóa server-side. Liên hệ đội ngũ để được tư vấn và đăng ký danh sách chờ ra mắt sớm.', 'whp' ), '<strong>WP Helper Lite Enterprise (v3.0)</strong>' ) ); ?></p>
            </div>
        </div>

    </div>
    <?php
}
