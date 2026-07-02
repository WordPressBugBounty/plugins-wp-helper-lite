<?php if (!defined('ABSPATH')) exit; ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo __('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<style>
.mb-wph-page {
    font-family: inherit;
    max-width: 1200px;
    margin: 20px auto 40px;
    padding: 0 15px 40px;
    box-sizing: border-box;
}
.mb-adv-layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
@media (max-width: 820px) { .mb-adv-layout { grid-template-columns: 1fr; } .mb-adv-sidebar { display: none; } }
.mb-adv-sidebar { position: sticky; top: 32px; }
.mb-adv-sidebar-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 18px 20px; margin-bottom: 16px;
}
.mb-adv-sidebar-card h4 { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 10px; }
.mb-adv-guide-icon { width: 28px; height: 28px; border-radius: 7px; background: #eff2fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mb-adv-tip-icon  { width: 28px; height: 28px; border-radius: 7px; background: #fef9c3; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mb-adv-guide-text { font-size: 12.5px; color: #64748b; line-height: 1.6; }
.mb-adv-tips-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.mb-adv-tips-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; color: #475569; line-height: 1.5; }
.mb-adv-tips-list li::before { content: '✓'; color: #22c55e; font-weight: 700; flex-shrink: 0; margin-top: 1px; }

/* Header */
.mb-wph-header-card {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #f0f4ff 45%, #e8f0fd 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(56,88,233,0.1), 0 0 0 1px #e0e7ff;
    margin-bottom: 20px;
    overflow: hidden;
    min-height: 168px;
    display: flex;
    align-items: stretch;
}
.mb-wph-header-left {
    position: relative; z-index: 2;
    padding: 32px 36px;
    display: flex; flex-direction: column; justify-content: center; gap: 14px;
    max-width: 500px; flex-shrink: 0;
}
.mb-wph-header-title-row { display: flex; align-items: center; gap: 14px; }
.mb-wph-header-icon-box {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, #3858e9, #6b8af5);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 12px rgba(56,88,233,0.3);
}
.mb-wph-header-right {
    position: absolute; inset: 0 0 0 38%;
    overflow: hidden; pointer-events: none;
}
.mb-wph-page-subtitle { color: #64748b; font-size: 13.5px; line-height: 1.6; margin: 0; padding-left: 58px; max-width: 400px; }
.mb-wph-page-subtitle p { margin: 0; color: inherit; font-size: inherit; line-height: inherit; }
.mb-wph-header-illus { display: none; }

/* Cards */
.mb-wph-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 18px;
    overflow: hidden;
}
.mb-wph-card-inner { padding: 22px 24px; }
.mb-wph-section-card { border-left: 4px solid transparent; }
.mb-wph-section-card.accent-blue   { border-left-color: #3858e9; }
.mb-wph-section-card.accent-orange { border-left-color: #f97316; }
.mb-wph-section-card.accent-purple { border-left-color: #8b5cf6; }

/* Section header */
.mb-wph-section-header {
    display: flex; align-items: flex-start; gap: 14px;
    margin-bottom: 16px; padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-adv-section-num {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 15px; font-weight: 700; color: #fff;
}
.accent-blue   .mb-adv-section-num { background: #3858e9; box-shadow: 0 0 0 5px rgba(56,88,233,0.18),  0 0 0 10px rgba(56,88,233,0.08); }
.accent-orange .mb-adv-section-num { background: #f97316; box-shadow: 0 0 0 5px rgba(249,115,22,0.18), 0 0 0 10px rgba(249,115,22,0.08); }
.accent-purple .mb-adv-section-num { background: #8b5cf6; box-shadow: 0 0 0 5px rgba(139,92,246,0.18), 0 0 0 10px rgba(139,92,246,0.08); }
.mb-wph-section-header-text h3 {
    margin: 0 0 4px; font-size: 15px; font-weight: 700; color: #0f172a;
}
.accent-blue   .mb-wph-section-header-text h3 { color: #0f172a; }
.accent-orange .mb-wph-section-header-text h3 { color: #0f172a; }
.accent-purple .mb-wph-section-header-text h3 { color: #0f172a; }
.mb-wph-section-header-text p { margin: 0; font-size: 13px; color: #64748b; line-height: 1.6; }

/* Feature rows */
.mb-wph-feature-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid #f8fafc;
}
.mb-wph-feature-row:last-child { border-bottom: none; padding-bottom: 0; }
.mb-wph-feature-row:first-child { padding-top: 0; }
.mb-wph-feature-icon {
    width: 42px; height: 42px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mb-wph-feature-info { flex: 1; min-width: 0; }
.mb-wph-feature-name { display: block; font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 3px; }
.mb-wph-feature-desc { display: block; font-size: 12.5px; color: #64748b; line-height: 1.5; }

/* Toggle + label */
.mb-wph-feature-toggle { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.mb-wph-toggle-label { font-size: 12.5px; font-weight: 700; color: #94a3b8; transition: color 0.2s; min-width: 22px; text-align: right; }
.mb-wph-toggle-label.active { color: #22c55e; }
.mb-wph-switch {
    position: relative; display: inline-block;
    width: 48px; height: 26px;
}
.mb-wph-switch input { opacity: 0; width: 0; height: 0; }
.mb-wph-slider {
    position: absolute; inset: 0;
    background: #cbd5e1; border-radius: 26px;
    cursor: pointer; transition: background 0.25s;
}
.mb-wph-slider::after {
    content: ''; position: absolute;
    width: 18px; height: 18px;
    background: #fff; border-radius: 50%;
    left: 4px; top: 4px;
    transition: transform 0.25s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.18);
}
.mb-wph-switch input:checked + .mb-wph-slider { background: #22c55e; }
.mb-wph-switch input:checked + .mb-wph-slider::after { transform: translateX(22px); }

.mb-wph-field > label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 7px; }
.mb-wph-input {
    width: 100%; padding: 10px 14px;
    border: 1.5px solid #d1d5db; border-radius: 8px;
    font-size: 13.5px; color: #1e293b; background: #f8fafc;
    box-sizing: border-box; outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.mb-wph-input:focus { border-color: #3858e9; background: #fff; box-shadow: 0 0 0 3px rgba(56,88,233,0.1); }
.mb-wph-hint { margin: 6px 0 0; font-size: 12.5px; color: #94a3b8; line-height: 1.5; }
.mb-wph-hint a { color: #3858e9; text-decoration: none; }
.mb-wph-hint a:hover { text-decoration: underline; }

/* Code nav shortcuts */
.mb-code-nav-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
@media (max-width: 560px) { .mb-code-nav-grid { grid-template-columns: 1fr; } }
.mb-code-nav-item {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 14px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 10px; text-decoration: none;
    transition: all 0.2s; cursor: pointer;
}
.mb-code-nav-item:hover { background: #f1f5f9; border-color: #cbd5e1; transform: translateX(2px); }
.mb-code-nav-icon {
    width: 32px; height: 32px; border-radius: 8px;
    background: #f0eeff; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mb-code-nav-text { flex: 1; min-width: 0; }
.mb-code-nav-text strong { display: block; font-size: 13px; font-weight: 600; color: #0f172a; }
.mb-code-nav-badge {
    display: inline-block; background: #f1f5f9; border: 1px solid #e2e8f0;
    border-radius: 4px; padding: 1px 5px; font-size: 10.5px;
    font-family: monospace; color: #8b5cf6; font-weight: 700; margin-top: 2px;
}
.mb-code-nav-arrow { color: #94a3b8; font-size: 16px; flex-shrink: 0; }

/* Save bar */
.mb-wph-save-bar {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 16px 24px; display: flex;
    align-items: center; justify-content: space-between; margin-top: 8px;
}
.mb-wph-save-note { font-size: 12.5px; color: #64748b; display: flex; align-items: center; gap: 6px; }
.mb-wph-save-btn {
    background: linear-gradient(135deg, #3858e9 0%, #2563eb 100%);
    color: #fff; border: none; border-radius: 9px;
    padding: 11px 32px; font-size: 13.5px; font-weight: 600; cursor: pointer;
    box-shadow: 0 4px 14px rgba(56,88,233,0.35); transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 7px;
}
.mb-wph-save-btn:hover { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); transform: translateY(-1px); }
</style>

<form method="post" id="mb-woo-advance-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-header-left">
            <div class="mb-wph-header-title-row">
                <div class="mb-wph-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" fill="#fff" fill-opacity=".9"/><line x1="3" y1="6" x2="21" y2="6" stroke="#3858e9" stroke-width="1.8"/><path d="M16 10a4 4 0 0 1-8 0" stroke="#3858e9" stroke-width="1.8" fill="none"/></svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Tùy chỉnh cửa hàng nâng cao', 'whp'); ?></h1>
            </div>
            <div class="mb-wph-page-subtitle"><?php echo wp_kses_post(__($itemInfo['desc'] ?? 'Chức năng nâng cao giúp tối ưu cho cửa hàng của bạn và tăng trải nghiệm mua sắm cho khách hàng.', 'whp')); ?></div>
        </div>
        <div class="mb-wph-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="adv_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f0f4ff" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#edf2ff" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#dde8ff" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="adv_sh" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(56,88,233,0.18)"/>
                    </filter>
                    <filter id="adv_shSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(56,88,233,0.1)"/>
                    </filter>
                </defs>
                <rect width="680" height="168" fill="url(#adv_hbg)"/>
                <circle cx="590" cy="18" r="65" fill="#3858e9" fill-opacity=".05"/>
                <circle cx="645" cy="148" r="40" fill="#8b5cf6" fill-opacity=".05"/>
                <!-- Store / shop building -->
                <g filter="url(#adv_sh)">
                    <rect x="360" y="30" width="110" height="110" rx="8" fill="#fff"/>
                    <rect x="360" y="30" width="110" height="30" rx="8" fill="#3858e9"/>
                    <rect x="360" y="48" width="110" height="12" fill="#3858e9"/>
                    <text x="415" y="52" font-size="11" font-weight="700" fill="#fff" text-anchor="middle" font-family="sans-serif">STORE</text>
                    <rect x="375" y="72" width="24" height="32" rx="3" fill="#eff2fe"/>
                    <rect x="407" y="72" width="24" height="32" rx="3" fill="#eff2fe"/>
                    <rect x="439" y="72" width="24" height="32" rx="3" fill="#eff2fe"/>
                    <rect x="384" y="112" width="42" height="28" rx="3" fill="#dbeafe"/>
                    <rect x="391" y="118" width="12" height="10" rx="1" fill="#3858e9" fill-opacity=".4"/>
                    <line x1="360" y1="140" x2="470" y2="140" stroke="#e2e8f0" stroke-width="1.5"/>
                </g>
                <!-- Shopping cart -->
                <g filter="url(#adv_shSm)">
                    <circle cx="530" cy="84" r="38" fill="#fff"/>
                    <path d="M512 66h4l8 28h20l6-18h-28" stroke="#f97316" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="524" cy="98" r="3" fill="#f97316"/>
                    <circle cx="538" cy="98" r="3" fill="#f97316"/>
                </g>
                <!-- Package / box -->
                <g filter="url(#adv_shSm)" transform="translate(598,32)">
                    <rect width="52" height="52" rx="8" fill="#fff"/>
                    <path d="M8 20h36M26 20v28M8 20l8-12h20l8 12" stroke="#8b5cf6" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="16" y1="14" x2="36" y2="14" stroke="#8b5cf6" stroke-width="1.5" stroke-linecap="round"/>
                </g>
                <!-- Price tag -->
                <g filter="url(#adv_shSm)" transform="translate(604,108)">
                    <rect width="46" height="34" rx="6" fill="#fff"/>
                    <path d="M8 17h14M15 10v14" stroke="#22c55e" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="34" cy="17" r="7" fill="#dcfce7" stroke="#22c55e" stroke-width="1.5"/>
                    <text x="34" y="21" font-size="9" font-weight="700" fill="#16a34a" text-anchor="middle" font-family="sans-serif">$</text>
                </g>
                <!-- Telegram icon -->
                <g filter="url(#adv_shSm)" transform="translate(476,108)">
                    <circle cx="26" cy="26" r="26" fill="#fff"/>
                    <circle cx="26" cy="26" r="18" fill="#229ED9" fill-opacity=".15"/>
                    <path d="M14 26l24-10-9 24-5-10-10-4z" fill="#229ED9" fill-opacity=".8"/>
                    <path d="M26 30l-3-4 7-6" stroke="#229ED9" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                </g>
                <!-- Connection lines -->
                <line x1="470" y1="84" x2="492" y2="84" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="4 3"/>
                <line x1="568" y1="84" x2="598" y2="58" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="4 3"/>
                <line x1="568" y1="90" x2="604" y2="120" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="4 3"/>
                <!-- Dots -->
                <circle cx="492" cy="40" r="4" fill="#3858e9" fill-opacity=".2"/>
                <circle cx="580" cy="150" r="3.5" fill="#8b5cf6" fill-opacity=".2"/>
                <circle cx="650" cy="70" r="5" fill="#f97316" fill-opacity=".15"/>
                <circle cx="340" cy="100" r="3" fill="#3858e9" fill-opacity=".2"/>
            </svg>
        </div>
    </div>

    <!-- 2-column layout -->
    <div class="mb-adv-layout">
    <div class="mb-adv-main">

    <!-- Card 1: Tính năng cửa hàng -->
    <div class="mb-wph-card mb-wph-section-card accent-blue">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-adv-section-num">1</div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e('Tính năng cửa hàng', 'whp'); ?></h3>
                    <p><?php esc_html_e('Các tính năng nâng cao giúp tối ưu hoạt động bán hàng và trải nghiệm khách hàng.', 'whp'); ?></p>
                </div>
            </div>

            <!-- Tạo thông báo mua hàng -->
            <div class="mb-wph-feature-row">
                <div class="mb-wph-feature-icon" style="background:#eff2fe;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="mb-wph-feature-info">
                    <span class="mb-wph-feature-name"><?php esc_html_e('Tạo thông báo mua hàng', 'whp'); ?></span>
                    <span class="mb-wph-feature-desc"><?php esc_html_e('Tự động tạo thông báo "vừa có người mua sản phẩm này" để tăng tính thúc đẩy mua hàng.', 'whp'); ?></span>
                </div>
                <div class="mb-wph-feature-toggle">
                    <span class="mb-wph-toggle-label <?php echo $whp_woocommerce_advance_enable_notice_check === 'checked' ? 'active' : ''; ?>">
                        <?php echo $whp_woocommerce_advance_enable_notice_check === 'checked' ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                    </span>
                    <label class="mb-wph-switch">
                        <input type="checkbox" name="whp_woocommerce_advance_enable_notice"
                            value="1"
                            <?php echo esc_attr($whp_woocommerce_advance_enable_notice_check); ?>>
                        <span class="mb-wph-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Xuất hóa đơn VAT -->
            <div class="mb-wph-feature-row">
                <div class="mb-wph-feature-icon" style="background:#dcfce7;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                </div>
                <div class="mb-wph-feature-info">
                    <span class="mb-wph-feature-name"><?php esc_html_e('Xuất hóa đơn VAT', 'whp'); ?></span>
                    <span class="mb-wph-feature-desc"><?php esc_html_e('Thêm trường yêu cầu xuất hóa đơn VAT vào form thanh toán WooCommerce.', 'whp'); ?></span>
                </div>
                <div class="mb-wph-feature-toggle">
                    <span class="mb-wph-toggle-label <?php echo $whp_woocommerce_advance_enable_vat_check === 'checked' ? 'active' : ''; ?>">
                        <?php echo $whp_woocommerce_advance_enable_vat_check === 'checked' ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                    </span>
                    <label class="mb-wph-switch">
                        <input type="checkbox" name="whp_woocommerce_advance_enable_vat"
                            value="1"
                            <?php echo esc_attr($whp_woocommerce_advance_enable_vat_check); ?>>
                        <span class="mb-wph-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Rút gọn mô tả -->
            <div class="mb-wph-feature-row">
                <div class="mb-wph-feature-icon" style="background:#f5f3ff;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </div>
                <div class="mb-wph-feature-info">
                    <span class="mb-wph-feature-name"><?php esc_html_e('Rút gọn mô tả sản phẩm', 'whp'); ?></span>
                    <span class="mb-wph-feature-desc"><?php esc_html_e('Tự động rút gọn mô tả sản phẩm dài — hiển thị nút "Xem thêm" để người dùng mở rộng.', 'whp'); ?></span>
                </div>
                <div class="mb-wph-feature-toggle">
                    <span class="mb-wph-toggle-label <?php echo $whp_woocommerce_advance_enable_compact_desc_check === 'checked' ? 'active' : ''; ?>">
                        <?php echo $whp_woocommerce_advance_enable_compact_desc_check === 'checked' ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                    </span>
                    <label class="mb-wph-switch">
                        <input type="checkbox" name="whp_woocommerce_advance_enable_compact_desc"
                            value="1"
                            <?php echo esc_attr($whp_woocommerce_advance_enable_compact_desc_check); ?>>
                        <span class="mb-wph-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Lọc đơn hàng theo SĐT -->
            <div class="mb-wph-feature-row">
                <div class="mb-wph-feature-icon" style="background:#dcfce7;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 11.4 19.79 19.79 0 0 1 1.61 2.82 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.54a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <div class="mb-wph-feature-info">
                    <span class="mb-wph-feature-name"><?php esc_html_e('Lọc đơn hàng theo SĐT', 'whp'); ?></span>
                    <span class="mb-wph-feature-desc"><?php esc_html_e('Thêm bộ lọc tìm kiếm đơn hàng theo số điện thoại trong WooCommerce.', 'whp'); ?></span>
                </div>
                <div class="mb-wph-feature-toggle">
                    <span class="mb-wph-toggle-label <?php echo $whp_extention_filter_order_by_phone_check === 'checked' ? 'active' : ''; ?>">
                        <?php echo $whp_extention_filter_order_by_phone_check === 'checked' ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                    </span>
                    <label class="mb-wph-switch">
                        <input type="checkbox" name="whp_extention_filter_order_by_phone"
                            value="1"
                            <?php echo esc_attr($whp_extention_filter_order_by_phone_check); ?>>
                        <span class="mb-wph-slider"></span>
                    </label>
                </div>
            </div>

        </div>
    </div>

    <!-- Card 2: Tùy chỉnh code -->
    <div class="mb-wph-card mb-wph-section-card accent-purple">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-adv-section-num">2</div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e('Tùy chỉnh code', 'whp'); ?></h3>
                    <p><?php esc_html_e('Thêm các đoạn code tùy chỉnh vào website của bạn.', 'whp'); ?></p>
                </div>
            </div>
            <div class="mb-code-nav-grid">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-code')); ?>" class="mb-code-nav-item">
                    <div class="mb-code-nav-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </div>
                    <div class="mb-code-nav-text">
                        <strong>Header Scripts</strong>
                        <span class="mb-code-nav-badge">&lt;head&gt;</span>
                    </div>
                    <span class="mb-code-nav-arrow dashicons dashicons-arrow-right-alt2" style="font-size:14px;width:14px;height:14px;line-height:14px;"></span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-code')); ?>" class="mb-code-nav-item">
                    <div class="mb-code-nav-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </div>
                    <div class="mb-code-nav-text">
                        <strong>Body Scripts - Top</strong>
                        <span class="mb-code-nav-badge">&lt;body&gt;</span>
                    </div>
                    <span class="mb-code-nav-arrow dashicons dashicons-arrow-right-alt2" style="font-size:14px;width:14px;height:14px;line-height:14px;"></span>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-code')); ?>" class="mb-code-nav-item">
                    <div class="mb-code-nav-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </div>
                    <div class="mb-code-nav-text">
                        <strong>Footer Scripts</strong>
                        <span class="mb-code-nav-badge">&lt;/body&gt;</span>
                    </div>
                    <span class="mb-code-nav-arrow dashicons dashicons-arrow-right-alt2" style="font-size:14px;width:14px;height:14px;line-height:14px;"></span>
                </a>
            </div>
        </div>
    </div>

    </div><!-- /mb-adv-main -->

    <!-- Sidebar -->
    <div class="mb-adv-sidebar">

        <!-- Hướng dẫn & Mẹo sử dụng card -->
        <div class="mb-adv-sidebar-card">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h4 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;"><?php esc_html_e('Hướng dẫn & Mẹo sử dụng', 'whp'); ?></h4>
                    <p style="margin:0;font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Tối ưu tính năng cửa hàng', 'whp'); ?></p>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:8px;">

                <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                    <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div>
                        <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Thông báo mua hàng', 'whp'); ?></strong>
                        <span style="font-size:11.5px;color:#16a34a;line-height:1.4;display:block;"><?php esc_html_e('Bật để hiển thị popup "vừa có người mua" — tăng tâm lý xã hội và tỉ lệ chuyển đổi.', 'whp'); ?></span>
                    </div>
                </div>

                <div style="display:flex;gap:10px;padding:10px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;align-items:flex-start;">
                    <span style="width:18px;height:18px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="#fff" stroke-width="2.5"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <strong style="font-size:12px;color:#1e3a8a;display:block;margin-bottom:1px;"><?php esc_html_e('Hóa đơn VAT', 'whp'); ?></strong>
                        <span style="font-size:11.5px;color:#2563eb;line-height:1.4;display:block;"><?php esc_html_e('Thêm trường xuất VAT vào checkout để phục vụ khách hàng doanh nghiệp.', 'whp'); ?></span>
                    </div>
                </div>

                <div style="display:flex;gap:10px;padding:10px 12px;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;align-items:flex-start;">
                    <span style="width:18px;height:18px;border-radius:50%;background:#a855f7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 10h16M4 14h8" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <strong style="font-size:12px;color:#581c87;display:block;margin-bottom:1px;"><?php esc_html_e('Rút gọn mô tả', 'whp'); ?></strong>
                        <span style="font-size:11.5px;color:#7c3aed;line-height:1.4;display:block;"><?php esc_html_e('Ẩn bớt phần mô tả dài — trang gọn, nút "Xem thêm" giúp trải nghiệm tốt hơn.', 'whp'); ?></span>
                    </div>
                </div>

                <div style="display:flex;gap:10px;padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;align-items:flex-start;">
                    <span style="width:18px;height:18px;border-radius:50%;background:#f97316;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 11.4 19.79 19.79 0 0 1 1.61 2.82 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.54a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" stroke="#fff" stroke-width="2"/></svg>
                    </span>
                    <div>
                        <strong style="font-size:12px;color:#7c2d12;display:block;margin-bottom:1px;"><?php esc_html_e('Lọc đơn theo SĐT', 'whp'); ?></strong>
                        <span style="font-size:11.5px;color:#c2410c;line-height:1.4;display:block;"><?php esc_html_e('Tra cứu nhanh đơn hàng theo số điện thoại — hỗ trợ khách hàng hiệu quả hơn.', 'whp'); ?></span>
                    </div>
                </div>

            </div>
        </div>

    </div>

    </div><!-- /mb-adv-layout -->

    <!-- Save bar -->
    <div class="mb-wph-save-bar">
        <span class="mb-wph-save-note">
            <svg width="18" height="18" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10" fill="#eff2fe"/><path d="M12 8v4m0 4h.01" stroke="#3858e9" stroke-width="2" stroke-linecap="round"/></svg>
            <div>
                <strong style="display:block;font-size:13px;font-weight:600;color:#334155;margin-bottom:2px;"><?php esc_html_e('Lưu ý', 'whp'); ?></strong>
                <span style="font-size:12.5px;color:#64748b;"><?php esc_html_e('Các thay đổi sẽ được áp dụng ngay lập tức. Bạn có thể bật/tắt từng tính năng theo nhu cầu.', 'whp'); ?></span>
            </div>
        </span>
        <button type="submit" name="submit" class="mb-wph-save-btn">
            <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:15px;"></span>
            <?php esc_html_e('Lưu thông tin', 'whp'); ?>
        </button>
    </div>

</div>
</form>

<script>
(function() {
    'use strict';
    var whpWooI18n={on:'<?php echo esc_js(__("Bật","whp")); ?>',off:'<?php echo esc_js(__("Tắt","whp")); ?>'};

    // Toggle feature rows — update "Bật/Tắt" label
    document.querySelectorAll('.mb-wph-feature-row .mb-wph-switch input[type="checkbox"]').forEach(function(input) {
        var label = input.closest('.mb-wph-feature-toggle').querySelector('.mb-wph-toggle-label');
        input.addEventListener('change', function() {
            if (label) {
                label.textContent = this.checked ? whpWooI18n.on : whpWooI18n.off;
                label.classList.toggle('active', this.checked);
            }
        });
    });

})();
</script>
