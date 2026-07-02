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

/* ── Header ── */
.mb-wph-header-card {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #eef2ff 45%, #e0e7ff 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(79,70,229,0.1), 0 0 0 1px #c7d2fe;
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
    background: linear-gradient(135deg, #4f46e5, #818cf8);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 12px rgba(79,70,229,0.3);
}
.mb-wph-header-right {
    position: absolute; inset: 0 0 0 38%;
    overflow: hidden; pointer-events: none;
}
.mb-wph-page-subtitle { color: #64748b; font-size: 13.5px; line-height: 1.6; margin: 0; padding-left: 58px; max-width: 400px; }
.mb-wph-page-subtitle p { margin: 0; color: inherit; font-size: inherit; line-height: inherit; }
.mb-wph-header-illus { display: none; }

/* ── 2-column layout ── */
.mb-pay-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 820px) {
    .mb-pay-layout { grid-template-columns: 1fr; }
    .mb-pay-sidebar { display: none; }
}
.mb-pay-sidebar { position: relative; }

/* ── Card ── */
.mb-wph-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 16px; overflow: hidden;
}
.mb-wph-card-inner { padding: 20px 22px; }
.mb-wph-section-card { border-left: 4px solid #3858e9; }

/* ── Section header ── */
.mb-wph-section-header {
    display: flex; align-items: flex-start; gap: 12px;
    margin-bottom: 14px; padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-wph-section-icon {
    width: 36px; height: 36px; border-radius: 9px;
    background: #eff2fe;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mb-wph-section-header-text h3 { margin: 0 0 3px; font-size: 15px; font-weight: 700; color: #0f172a; }
.mb-wph-section-header-text p  { margin: 0; font-size: 13px; color: #64748b; line-height: 1.5; }

/* ── Payment field rows ── */
.mb-pay-field-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f8fafc;
    cursor: default;
}
.mb-pay-field-row:last-child { border-bottom: none; padding-bottom: 0; }
.mb-pay-field-row:first-child { padding-top: 0; }

.mb-pay-drag-handle {
    color: #cbd5e1; cursor: grab; flex-shrink: 0;
    display: flex; align-items: center; padding: 4px 2px;
}
.mb-pay-drag-handle:active { cursor: grabbing; }

.mb-pay-field-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: #eff2fe;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.mb-pay-field-info { flex: 1; min-width: 0; }
.mb-pay-field-name { display: block; font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 3px; }
.mb-pay-field-desc { display: block; font-size: 12.5px; color: #64748b; line-height: 1.5; }
.mb-pay-field-hint { display: flex; align-items: center; gap: 4px; font-size: 11.5px; color: #4f46e5; margin-top: 4px; line-height: 1.4; }

.mb-pay-field-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* Toggle */
.mb-wph-switch {
    position: relative; display: inline-block; width: 48px; height: 26px;
}
.mb-wph-switch input { opacity: 0; width: 0; height: 0; }
.mb-wph-slider {
    position: absolute; inset: 0;
    background: #cbd5e1; border-radius: 26px;
    cursor: pointer; transition: background 0.25s;
}
.mb-wph-slider::after {
    content: ''; position: absolute;
    width: 18px; height: 18px; background: #fff; border-radius: 50%;
    left: 4px; top: 4px;
    transition: transform 0.25s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.18);
}
.mb-wph-switch input:checked + .mb-wph-slider { background: #22c55e; }
.mb-wph-switch input:checked + .mb-wph-slider::after { transform: translateX(22px); }

/* Toggle label */
.mb-pay-toggle-label {
    font-size: 12.5px; font-weight: 700; color: #94a3b8;
    transition: color 0.2s; min-width: 22px; text-align: right;
}
.mb-pay-toggle-label.active { color: #22c55e; }

/* ── Sidebar cards ── */
.mb-pay-sidebar-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 18px 20px; margin-bottom: 16px;
}
.mb-pay-sidebar-card h4 {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 10px;
}
.mb-pay-guide-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: #eff2fe; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mb-pay-tip-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: #fef9c3; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mb-pay-guide-text { font-size: 12.5px; color: #64748b; line-height: 1.6; }
.mb-pay-tips-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.mb-pay-tips-list li {
    display: flex; align-items: flex-start; gap: 8px;
    font-size: 12.5px; color: #475569; line-height: 1.5;
}
.mb-pay-tips-list li::before {
    content: '✓'; color: #22c55e; font-weight: 700;
    flex-shrink: 0; margin-top: 1px;
}

/* ── Save bar ── */
.mb-wph-save-bar {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 16px 24px; display: flex;
    align-items: center; justify-content: space-between; margin-top: 4px;
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

<form method="post" id="mb-woo-payment-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-header-left">
            <div class="mb-wph-header-title-row">
                <div class="mb-wph-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <rect x="2" y="4" width="20" height="16" rx="2.5" fill="#fff" fill-opacity=".9"/>
                        <rect x="2" y="8" width="20" height="3" fill="#4f46e5" fill-opacity=".3"/>
                        <rect x="5" y="13" width="6" height="1.5" rx=".75" fill="#818cf8" fill-opacity=".8"/>
                        <rect x="5" y="16" width="4" height="1.5" rx=".75" fill="#818cf8" fill-opacity=".6"/>
                        <circle cx="17.5" cy="14.5" r="3" fill="#22c55e" fill-opacity=".9"/>
                        <path d="M16 14.5l1 1.2 2.5-2" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Tùy chỉnh thanh toán', 'whp'); ?></h1>
            </div>
            <div class="mb-wph-page-subtitle"><?php echo wp_kses_post($itemInfo['desc'] ?? 'Điều chỉnh mẫu thông tin để việc quản lý CRM hiệu quả hơn.'); ?></div>
        </div>
        <div class="mb-wph-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="pay_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#eef2ff" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#e0e7ff" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#c7d2fe" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="pay_sh" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(79,70,229,0.18)"/>
                    </filter>
                    <filter id="pay_shSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(79,70,229,0.12)"/>
                    </filter>
                </defs>
                <rect width="680" height="168" fill="url(#pay_hbg)"/>
                <circle cx="605" cy="22" r="68" fill="#4f46e5" fill-opacity=".04"/>
                <circle cx="652" cy="150" r="44" fill="#818cf8" fill-opacity=".05"/>
                <!-- Checkout form window -->
                <g filter="url(#pay_sh)">
                    <rect x="348" y="14" width="160" height="132" rx="12" fill="#fff"/>
                    <rect x="348" y="14" width="160" height="32" rx="12" fill="#4f46e5" fill-opacity=".08"/>
                    <rect x="348" y="36" width="160" height="10" fill="#4f46e5" fill-opacity=".08"/>
                    <!-- Window dots -->
                    <circle cx="362" cy="30" r="4" fill="#ef4444" fill-opacity=".7"/>
                    <circle cx="374" cy="30" r="4" fill="#f59e0b" fill-opacity=".7"/>
                    <circle cx="386" cy="30" r="4" fill="#22c55e" fill-opacity=".7"/>
                    <!-- URL bar -->
                    <rect x="398" y="24" width="96" height="12" rx="6" fill="#e2e8f0"/>
                    <rect x="402" y="27" width="60" height="6" rx="3" fill="#c7d2fe" fill-opacity=".7"/>
                    <!-- Form field: Name -->
                    <rect x="358" y="54" width="140" height="9" rx="3" fill="#e0e7ff" fill-opacity=".7"/>
                    <rect x="358" y="51" width="36" height="4" rx="2" fill="#94a3b8" fill-opacity=".5"/>
                    <!-- Form field: Address -->
                    <rect x="358" y="74" width="140" height="9" rx="3" fill="#e0e7ff" fill-opacity=".7"/>
                    <rect x="358" y="71" width="30" height="4" rx="2" fill="#94a3b8" fill-opacity=".5"/>
                    <!-- Form field: Phone -->
                    <rect x="358" y="94" width="80" height="9" rx="3" fill="#e0e7ff" fill-opacity=".7"/>
                    <rect x="358" y="91" width="40" height="4" rx="2" fill="#94a3b8" fill-opacity=".5"/>
                    <!-- Pay button -->
                    <rect x="358" y="114" width="140" height="22" rx="7" fill="#4f46e5"/>
                    <rect x="370" y="120" width="80" height="10" rx="5" fill="#fff" fill-opacity=".8"/>
                    <circle cx="480" cy="125" r="4" fill="#fff" fill-opacity=".4"/>
                </g>
                <!-- Credit card floating -->
                <g filter="url(#pay_shSm)">
                    <rect x="520" y="22" width="100" height="62" rx="10" fill="#fff"/>
                    <rect x="520" y="22" width="100" height="62" rx="10" stroke="#e0e7ff" stroke-width="1.5"/>
                    <rect x="520" y="22" width="100" height="26" rx="10" fill="#4f46e5" fill-opacity=".85"/>
                    <rect x="520" y="38" width="100" height="10" fill="#4f46e5" fill-opacity=".85"/>
                    <rect x="530" y="56" width="36" height="6" rx="3" fill="#c7d2fe"/>
                    <circle cx="598" cy="59" r="8" fill="#f59e0b" fill-opacity=".7"/>
                    <circle cx="608" cy="59" r="8" fill="#ef4444" fill-opacity=".5"/>
                    <rect x="530" y="30" width="20" height="12" rx="3" fill="#fef9c3" fill-opacity=".8"/>
                </g>
                <!-- Bank transfer badge -->
                <g filter="url(#pay_shSm)">
                    <rect x="528" y="102" width="84" height="44" rx="10" fill="#fff"/>
                    <rect x="534" y="108" width="28" height="28" rx="7" fill="#4f46e5" fill-opacity=".1"/>
                    <path d="M542 118h12M546 114v12" stroke="#4f46e5" stroke-width="1.8" stroke-linecap="round"/>
                    <rect x="568" y="112" width="36" height="5" rx="2.5" fill="#c7d2fe" fill-opacity=".8"/>
                    <rect x="568" y="120" width="28" height="4" rx="2" fill="#e0e7ff" fill-opacity=".9"/>
                    <rect x="568" y="127" width="22" height="4" rx="2" fill="#e0e7ff" fill-opacity=".7"/>
                </g>
                <!-- Checkmark success badge -->
                <g filter="url(#pay_shSm)">
                    <circle cx="640" cy="58" r="22" fill="#22c55e"/>
                    <path d="M630 58l7 7 13-14" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <!-- Floating decorations -->
                <circle cx="618" cy="128" r="7" fill="#c7d2fe" fill-opacity=".6"/>
                <circle cx="598" cy="148" r="5" fill="#e0e7ff" fill-opacity=".8"/>
                <circle cx="655" cy="22" r="6" fill="#c7d2fe" fill-opacity=".5"/>
            </svg>
        </div>
    </div>

    <!-- 2-column layout -->
    <div class="mb-pay-layout">

        <!-- Left: main card -->
        <div class="mb-pay-main">
            <div class="mb-wph-card mb-wph-section-card">
                <div class="mb-wph-card-inner">
                    <div class="mb-wph-section-header">
                        <div class="mb-wph-section-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        </div>
                        <div class="mb-wph-section-header-text">
                            <h3><?php esc_html_e('Tùy chỉnh form thanh toán', 'whp'); ?></h3>
                            <p><?php esc_html_e('Bật các trường bạn muốn hiển thị trong form thanh toán.', 'whp'); ?></p>
                        </div>
                    </div>

                    <?php
                    $fields_data = [
                        [
                            'name'  => 'whp_woocommerce_payment_fullname',
                            'check' => $whp_woocommerce_payment_fullname_check,
                            'label' => __('Gộp họ và tên', 'whp'),
                            'desc'  => __('Gộp 2 ô "Họ" + "Tên" riêng biệt thành 1 ô nhập họ và tên đầy đủ.', 'whp'),
                            'hint'  => __('Giúp form ngắn gọn hơn, đặc biệt hữu ích trên thiết bị di động.', 'whp'),
                            'icon'  => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                        ],
                        [
                            'name'  => 'whp_woocommerce_payment_address',
                            'check' => $whp_woocommerce_payment_address_check,
                            'label' => __('Gộp địa chỉ', 'whp'),
                            'desc'  => __('Gộp 2 ô địa chỉ (Dòng 1 + Dòng 2) thành 1 ô nhập liên tục.', 'whp'),
                            'hint'  => __('Phù hợp với shop giao hàng nội địa, địa chỉ Việt Nam không cần 2 dòng.', 'whp'),
                            'icon'  => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
                        ],
                        [
                            'name'  => 'whp_woocommerce_payment_country',
                            'check' => $whp_woocommerce_payment_country_check,
                            'label' => __('Ẩn quốc gia / Khu vực', 'whp'),
                            'desc'  => __('Ẩn trường Quốc gia/Khu vực khỏi form thanh toán.', 'whp'),
                            'hint'  => __('Giảm số bước checkout, tránh khách bị phân tâm bởi trường không liên quan.', 'whp'),
                            'icon'  => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
                        ],
                        [
                            'name'  => 'whp_woocommerce_payment_company',
                            'check' => $whp_woocommerce_payment_company_check,
                            'label' => __('Ẩn tên công ty', 'whp'),
                            'desc'  => __('Ẩn trường Tên công ty khỏi form thanh toán.', 'whp'),
                            'hint'  => __('Form càng ít trường không cần thiết → tỉ lệ hoàn thành đơn càng cao.', 'whp'),
                            'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
                        ],
                        [
                            'name'  => 'whp_woocommerce_payment_zipcode',
                            'check' => $whp_woocommerce_payment_zipcode_check,
                            'label' => __('Ẩn mã bưu điện', 'whp'),
                            'desc'  => __('Ẩn trường Mã bưu điện (ZIP code) khỏi form.', 'whp'),
                            'hint'  => __('Bỏ trường gây friction là cách đơn giản nhất tăng tỉ lệ đặt hàng thành công.', 'whp'),
                            'icon'  => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
                        ],
                        [
                            'name'  => 'whp_woocommerce_payment_province',
                            'check' => $whp_woocommerce_payment_province_check,
                            'label' => __('Ẩn tỉnh / Thành phố', 'whp'),
                            'desc'  => __('Ẩn trường Tỉnh/Thành phố khỏi form thanh toán.', 'whp'),
                            'hint'  => __('Lưu ý: nếu đơn vị giao hàng cần tỉnh để định tuyến, hãy giữ trường này.', 'whp'),
                            'icon'  => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
                        ],
                    ];
                    foreach ($fields_data as $field) :
                        $checked = $field['check'] === 'checked';
                    ?>
                    <div class="mb-pay-field-row">
                        <div class="mb-pay-field-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $field['icon']; ?></svg>
                        </div>
                        <div class="mb-pay-field-info">
                            <span class="mb-pay-field-name"><?php echo esc_html($field['label']); ?></span>
                            <span class="mb-pay-field-desc"><?php echo esc_html($field['desc']); ?></span>
                            <?php if (!empty($field['hint'])) : ?>
                            <span class="mb-pay-field-hint">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                <?php echo esc_html($field['hint']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="mb-pay-field-actions">
                            <span class="mb-pay-toggle-label <?php echo $checked ? 'active' : ''; ?>">
                                <?php echo $checked ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                            </span>
                            <label class="mb-wph-switch">
                                <input type="checkbox" name="<?php echo esc_attr($field['name']); ?>" value="1" <?php echo $checked ? 'checked' : ''; ?>>
                                <span class="mb-wph-slider"></span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

        <!-- Right: sidebar -->
        <div class="mb-pay-sidebar">

            <!-- Hướng dẫn & Mẹo sử dụng -->
            <div class="mb-pay-sidebar-card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h4 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;">Hướng dẫn &amp; Mẹo sử dụng</h4>
                        <p style="margin:0;font-size:11.5px;color:#94a3b8;">Tối ưu form thanh toán</p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Giữ trường quan trọng', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#16a34a;line-height:1.4;display:block;"><?php esc_html_e('Họ tên, Địa chỉ phải giữ lại để đơn hàng có đủ thông tin giao nhận.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M17 3H7a2 2 0 0 0-2 2v16l7-3 7 3V5a2 2 0 0 0-2-2z" stroke="#fff" stroke-width="2.5"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#1e3a8a;display:block;margin-bottom:1px;"><?php esc_html_e('Ẩn trường không cần', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#2563eb;line-height:1.4;display:block;"><?php esc_html_e('Form càng ngắn càng tăng tỉ lệ hoàn thành — ẩn Mã bưu điện, Tên công ty nếu không dùng.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#a855f7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#581c87;display:block;margin-bottom:1px;"><?php esc_html_e('Gộp Họ & Tên', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#7c3aed;line-height:1.4;display:block;"><?php esc_html_e('Gộp 2 ô thành 1 giúp khách hàng điền nhanh hơn, đặc biệt trên mobile.', 'whp'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div><!-- /.mb-pay-layout -->

    <!-- Save bar -->
    <div class="mb-wph-save-bar">
        <span class="mb-wph-save-note">
            <svg width="16" height="16" viewBox="0 0 24 24" style="flex-shrink:0;"><circle cx="12" cy="12" r="10" fill="#eff2fe"/><path d="M12 8v4m0 4h.01" stroke="#3858e9" stroke-width="2" stroke-linecap="round"/></svg>
            <?php esc_html_e('Các thay đổi sẽ được áp dụng ngay sau khi lưu', 'whp'); ?>
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
    var whpWooI18n={on:'<?php echo esc_js(__("Bật","whp")); ?>',off:'<?php echo esc_js(__("Tắt","whp")); ?>'};
    document.querySelectorAll('.mb-pay-field-actions .mb-wph-switch input').forEach(function(input) {
        var label = input.closest('.mb-pay-field-actions').querySelector('.mb-pay-toggle-label');
        input.addEventListener('change', function() {
            if (label) {
                label.textContent = this.checked ? whpWooI18n.on : whpWooI18n.off;
                label.classList.toggle('active', this.checked);
            }
        });
    });
})();
(function() {
    var sb = document.querySelector('.mb-pay-sidebar');
    var lyt = document.querySelector('.mb-pay-layout');
    if (!sb || !lyt) return;
    var TOP = 40;
    function update() {
        var lt = lyt.getBoundingClientRect().top;
        var shift = Math.max(0, TOP - lt);
        var max = Math.max(0, lyt.offsetHeight - sb.offsetHeight);
        sb.style.transform = shift > 0 ? 'translateY(' + Math.min(shift, max) + 'px)' : '';
    }
    window.addEventListener('scroll', update, {passive: true});
    window.addEventListener('resize', update);
})();
</script>
