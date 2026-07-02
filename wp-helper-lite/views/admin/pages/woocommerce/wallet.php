<?php if (!defined('ABSPATH')) exit; ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo __('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<?php
$gateway_sections = [
    'momo'      => 'mb_whp_wallet_momo',
    'zalopay'   => 'mb_whp_wallet_zalopay',
    'vnpay'     => 'mb_whp_wallet_vnpay',
    'shopeepay' => 'mb_whp_wallet_shopeepay',
];
?>

<style>
.mb-wph-page {
    font-family: inherit;
    max-width: 1200px;
    margin: 20px auto 40px;
    padding: 0 15px 40px;
    box-sizing: border-box;
}
.mb-wallet-layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
@media (max-width: 820px) { .mb-wallet-layout { grid-template-columns: 1fr; } .mb-wallet-sidebar { display: none; } }
.mb-wallet-sidebar { position: relative; }
.mb-wallet-sidebar-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 18px 20px; margin-bottom: 16px;
}
.mb-wallet-sidebar-card h4 { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 10px; }
.mb-wlt-guide-icon { width: 28px; height: 28px; border-radius: 7px; background: #eff2fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mb-wlt-tip-icon  { width: 28px; height: 28px; border-radius: 7px; background: #fef9c3; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mb-wlt-guide-text { font-size: 12.5px; color: #64748b; line-height: 1.6; }
.mb-wlt-tips-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.mb-wlt-tips-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; color: #475569; line-height: 1.5; }
.mb-wlt-tips-list li::before { content: '✓'; color: #22c55e; font-weight: 700; flex-shrink: 0; margin-top: 1px; }

/* ── Header ── */
.mb-wph-header-card {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #f0fdf4 45%, #dcfce7 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(22,163,74,0.1), 0 0 0 1px #bbf7d0;
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
    background: linear-gradient(135deg, #16a34a, #22c55e);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 12px rgba(22,163,74,0.3);
}
.mb-wph-header-right {
    position: absolute; inset: 0 0 0 38%;
    overflow: hidden; pointer-events: none;
}
.mb-wph-page-subtitle { color: #64748b; font-size: 13.5px; line-height: 1.6; margin: 0; padding-left: 58px; max-width: 400px; }
.mb-wph-page-subtitle p { margin: 0; color: inherit; font-size: inherit; line-height: inherit; }
.mb-wallet-header-illus { display: none; }

/* ── Card ── */
.mb-wph-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 20px; overflow: hidden;
}
.mb-wph-card-inner { padding: 22px 24px; }
.mb-wph-section-card { border-left: 4px solid transparent; }
.mb-wph-section-card.accent-blue { border-left-color: #3858e9; }

/* ── Section header ── */
.mb-wph-section-header {
    display: flex; align-items: flex-start; gap: 12px;
    margin-bottom: 16px; padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-wph-section-icon {
    width: 38px; height: 38px; border-radius: 10px;
    background: #eff2fe;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mb-wph-section-header-text h3 { margin: 0 0 3px; font-size: 15.5px; font-weight: 700; color: #0f172a; }
.mb-wph-section-header-text p  { margin: 0; font-size: 13px; color: #64748b; line-height: 1.6; }

/* ── Wallet rows ── */
.mb-wph-wallet-item {
    display: flex; align-items: center; gap: 16px;
    padding: 14px 0;
    border-bottom: 1px solid #f8fafc;
}
.mb-wph-wallet-item:last-child { border-bottom: none; padding-bottom: 0; }
.mb-wph-wallet-item:first-child { padding-top: 0; }
.mb-wph-wallet-logo {
    width: 62px; height: 62px; object-fit: contain;
    border-radius: 12px; border: 1px solid #e2e8f0;
    background: #fff; padding: 5px; flex-shrink: 0;
    box-sizing: border-box;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.mb-wph-wallet-info { flex: 1; min-width: 0; }
.mb-wph-wallet-name { display: block; font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
.mb-wph-wallet-desc { display: block; font-size: 13px; color: #64748b; line-height: 1.5; }
.mb-wph-wallet-desc a { color: #3858e9; text-decoration: none; }
.mb-wph-wallet-desc a:hover { text-decoration: underline; }

/* ── Toggle ── */
.mb-wph-wallet-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

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

.mb-wph-toggle-label {
    font-size: 12.5px; font-weight: 700; color: #94a3b8;
    transition: color 0.2s; white-space: nowrap; min-width: 22px; text-align: right;
}
.mb-wph-toggle-label.active { color: #22c55e; }

/* ── Cài đặt button ── */
.mb-wph-wallet-settings-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px;
    border: 1px solid #e2e8f0; border-radius: 8px;
    background: #f8fafc; color: #334155;
    font-size: 13px; font-weight: 600;
    text-decoration: none; white-space: nowrap;
    transition: all 0.18s;
    flex-shrink: 0;
}
.mb-wph-wallet-settings-btn:hover {
    background: #f1f5f9; border-color: #cbd5e1; color: #0f172a;
}
.mb-wph-wallet-settings-btn .dashicons {
    font-size: 14px; width: 14px; height: 14px; line-height: 14px;
}

/* ── Save bar ── */
.mb-wph-save-bar {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 16px 24px; display: flex;
    align-items: center; justify-content: space-between; margin-top: 8px;
}
.mb-wph-save-note { font-size: 12.5px; color: #64748b; display: flex; align-items: flex-start; gap: 8px; }
.mb-wph-save-note-text strong { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 2px; }
.mb-wph-save-btn {
    background: linear-gradient(135deg, #3858e9 0%, #2563eb 100%);
    color: #fff; border: none; border-radius: 9px;
    padding: 11px 32px; font-size: 13.5px; font-weight: 600; cursor: pointer;
    box-shadow: 0 4px 14px rgba(56,88,233,0.35); transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 7px;
}
.mb-wph-save-btn:hover { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); transform: translateY(-1px); }
</style>

<form method="post" id="mb-woo-wallet-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-header-left">
            <div class="mb-wph-header-title-row">
                <div class="mb-wph-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <rect x="2" y="6" width="20" height="14" rx="2.5" fill="#fff" fill-opacity=".9"/>
                        <path d="M2 10h20" stroke="#16a34a" stroke-width="1.8"/>
                        <rect x="2" y="6" width="20" height="4" rx="2.5" fill="#fff" fill-opacity=".4"/>
                        <circle cx="17" cy="15" r="2.5" fill="#22c55e" fill-opacity=".85"/>
                        <rect x="5" y="13.5" width="7" height="1.5" rx=".75" fill="#16a34a" fill-opacity=".5"/>
                    </svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Ví điện tử', 'whp'); ?></h1>
            </div>
            <div class="mb-wph-page-subtitle"><?php echo wp_kses_post($itemInfo['desc'] ?? __('Tính năng cho phép bạn cài đặt thêm phương thức thanh toán bằng ví điện tử (Momo, ZaloPay, VNPAY, ShopeePay...) một cách đơn giản và nhanh chóng.', 'whp')); ?></div>
        </div>
        <div class="mb-wph-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="wallet_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f0fdf4" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#dcfce7" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#bbf7d0" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="wallet_sh" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(22,163,74,0.18)"/>
                    </filter>
                    <filter id="wallet_shSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(22,163,74,0.12)"/>
                    </filter>
                </defs>
                <rect width="680" height="168" fill="url(#wallet_hbg)"/>
                <circle cx="600" cy="20" r="70" fill="#16a34a" fill-opacity=".04"/>
                <circle cx="650" cy="148" r="45" fill="#22c55e" fill-opacity=".05"/>
                <!-- Smartphone with wallet app -->
                <g filter="url(#wallet_sh)">
                    <rect x="355" y="18" width="72" height="130" rx="12" fill="#fff"/>
                    <rect x="355" y="18" width="72" height="130" rx="12" stroke="#d1fae5" stroke-width="1.5"/>
                    <rect x="359" y="22" width="64" height="122" rx="10" fill="#f0fdf4"/>
                    <!-- Status bar -->
                    <rect x="363" y="26" width="56" height="8" rx="3" fill="#bbf7d0" fill-opacity=".6"/>
                    <!-- Wallet card in app -->
                    <rect x="363" y="40" width="56" height="34" rx="7" fill="#16a34a"/>
                    <rect x="363" y="40" width="56" height="34" rx="7" fill="url(#wallet_hbg)" fill-opacity=".3"/>
                    <rect x="367" y="47" width="30" height="4" rx="2" fill="#fff" fill-opacity=".7"/>
                    <rect x="367" y="55" width="20" height="3" rx="1.5" fill="#fff" fill-opacity=".5"/>
                    <circle cx="408" cy="53" r="7" fill="#22c55e" fill-opacity=".5"/>
                    <circle cx="413" cy="53" r="7" fill="#fff" fill-opacity=".3"/>
                    <!-- QR code area -->
                    <rect x="371" y="82" width="40" height="40" rx="5" fill="#fff"/>
                    <rect x="374" y="85" width="10" height="10" rx="1.5" fill="#16a34a" fill-opacity=".8"/>
                    <rect x="387" y="85" width="10" height="10" rx="1.5" fill="#16a34a" fill-opacity=".8"/>
                    <rect x="374" y="98" width="10" height="10" rx="1.5" fill="#16a34a" fill-opacity=".8"/>
                    <rect x="376" y="87" width="6" height="6" rx=".8" fill="#fff"/>
                    <rect x="389" y="87" width="6" height="6" rx=".8" fill="#fff"/>
                    <rect x="376" y="100" width="6" height="6" rx=".8" fill="#fff"/>
                    <rect x="387" y="98" width="4" height="4" rx=".5" fill="#16a34a" fill-opacity=".6"/>
                    <rect x="393" y="98" width="4" height="4" rx=".5" fill="#16a34a" fill-opacity=".6"/>
                    <rect x="387" y="104" width="4" height="4" rx=".5" fill="#16a34a" fill-opacity=".6"/>
                    <!-- Bottom nav -->
                    <rect x="363" y="128" width="56" height="10" rx="3" fill="#d1fae5" fill-opacity=".8"/>
                    <circle cx="380" cy="133" r="3" fill="#16a34a" fill-opacity=".5"/>
                    <circle cx="391" cy="133" r="3" fill="#22c55e" fill-opacity=".4"/>
                    <circle cx="402" cy="133" r="3" fill="#16a34a" fill-opacity=".5"/>
                </g>
                <!-- Momo circle -->
                <g filter="url(#wallet_shSm)">
                    <circle cx="468" cy="52" r="30" fill="#fff"/>
                    <circle cx="468" cy="52" r="24" fill="#ae2070"/>
                    <text x="468" y="58" font-size="16" font-weight="900" fill="#fff" text-anchor="middle" font-family="sans-serif">M</text>
                </g>
                <!-- ZaloPay circle -->
                <g filter="url(#wallet_shSm)">
                    <circle cx="468" cy="118" r="28" fill="#fff"/>
                    <circle cx="468" cy="118" r="22" fill="#0068ff"/>
                    <text x="468" y="124" font-size="14" font-weight="900" fill="#fff" text-anchor="middle" font-family="sans-serif">Z</text>
                </g>
                <!-- Coin floating top-right -->
                <g filter="url(#wallet_shSm)">
                    <circle cx="548" cy="38" r="24" fill="#fef9c3"/>
                    <circle cx="548" cy="38" r="18" fill="#fde68a" fill-opacity=".8"/>
                    <text x="548" y="44" font-size="14" font-weight="700" fill="#d97706" text-anchor="middle" font-family="sans-serif">₫</text>
                </g>
                <!-- ShopeePay badge -->
                <g filter="url(#wallet_shSm)">
                    <rect x="520" y="76" width="56" height="30" rx="9" fill="#fff"/>
                    <rect x="524" y="80" width="48" height="22" rx="7" fill="#ee4d2d" fill-opacity=".9"/>
                    <text x="548" y="95" font-size="10" font-weight="700" fill="#fff" text-anchor="middle" font-family="sans-serif">SPay</text>
                </g>
                <!-- VNPAY badge -->
                <g filter="url(#wallet_shSm)">
                    <rect x="520" y="118" width="56" height="28" rx="8" fill="#fff"/>
                    <rect x="524" y="122" width="48" height="20" rx="6" fill="#0066b3" fill-opacity=".9"/>
                    <text x="548" y="136" font-size="9" font-weight="700" fill="#fff" text-anchor="middle" font-family="sans-serif">VNPAY</text>
                </g>
                <!-- Curved arrow connecting phone to badges -->
                <path d="M430 84 Q450 84 460 65" stroke="#bbf7d0" stroke-width="2" fill="none" stroke-dasharray="4 3"/>
                <path d="M430 84 Q450 84 460 100" stroke="#bbf7d0" stroke-width="2" fill="none" stroke-dasharray="4 3"/>
                <!-- Floating coins decoration -->
                <circle cx="620" cy="55" r="8" fill="#d1fae5" fill-opacity=".7"/>
                <circle cx="640" cy="100" r="6" fill="#bbf7d0" fill-opacity=".6"/>
                <circle cx="595" cy="130" r="5" fill="#d1fae5" fill-opacity=".8"/>
            </svg>
        </div>
    </div>

    <div class="mb-wallet-layout">
    <div>

    <!-- Card: Cổng thanh toán -->
    <div class="mb-wph-card mb-wph-section-card accent-blue">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e('Cổng thanh toán ví điện tử', 'whp'); ?></h3>
                    <p><?php esc_html_e('Bật tích hợp để khách hàng thanh toán bằng ví điện tử.', 'whp'); ?></p>
                </div>
            </div>

            <?php
            $wallet_descs = [
                'momo'      => __('Thanh toán nhanh chóng bằng ví điện tử MoMo, giao dịch an toàn và bảo mật.', 'whp'),
                'zalopay'   => __('Quét mã QR hoặc thanh toán trực tiếp qua ứng dụng ZaloPay.', 'whp'),
                'vnpay'     => __('Thanh toán qua QR Code hoặc thẻ ATM, Internet Banking của các ngân hàng hỗ trợ.', 'whp'),
                'shopeepay' => __('Thanh toán tiện lợi bằng ví ShopeePay với nhiều ưu đãi hấp dẫn.', 'whp'),
            ];
            ?>
            <?php foreach ($data['listWallet'] as $keyList => $itemList) :
                $itemId      = "whp_woocommerce_wallet_{$keyList}";
                $itemImgUrl  = $itemList['url'] ?? '';
                $itemTitle   = $itemList['title'] ?? '';
                $itemDesc    = $wallet_descs[$keyList] ?? ($itemList['desc'] ?? '');
                $isChecked   = $$itemId == '1';
                $sectionId   = $gateway_sections[$keyList] ?? '';
                $settingsUrl = admin_url('admin.php?page=wc-settings&tab=checkout' . ($sectionId ? '&section=' . $sectionId : ''));
            ?>
            <div class="mb-wph-wallet-item">
                <img src="<?php echo esc_url($itemImgUrl); ?>" alt="<?php echo esc_attr($itemTitle); ?>" class="mb-wph-wallet-logo">
                <div class="mb-wph-wallet-info">
                    <span class="mb-wph-wallet-name"><?php echo esc_html($itemTitle); ?></span>
                    <span class="mb-wph-wallet-desc"><?php echo esc_html($itemDesc); ?></span>
                </div>
                <div class="mb-wph-wallet-actions">
                    <span class="mb-wph-toggle-label <?php echo $isChecked ? 'active' : ''; ?>">
                        <?php echo $isChecked ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                    </span>
                    <label class="mb-wph-switch">
                        <input type="checkbox" name="<?php echo esc_attr($itemId); ?>" value="1" <?php echo $isChecked ? 'checked' : ''; ?>>
                        <span class="mb-wph-slider"></span>
                    </label>
                    <a href="<?php echo esc_url($settingsUrl); ?>" class="mb-wph-wallet-settings-btn">
                        <?php esc_html_e('Cài đặt', 'whp'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>

    </div><!-- /main -->

    <!-- Sidebar -->
    <div class="mb-wallet-sidebar">

        <!-- Hướng dẫn & Mẹo sử dụng -->
        <div class="mb-wallet-sidebar-card">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <div>
                    <h4 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;"><?php esc_html_e('Hướng dẫn & Mẹo sử dụng', 'whp'); ?></h4>
                    <p style="margin:0;font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Tối ưu cổng thanh toán', 'whp'); ?></p>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                    <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <div>
                        <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Tích hợp nhiều ví', 'whp'); ?></strong>
                        <span style="font-size:11.5px;color:#16a34a;line-height:1.4;display:block;"><?php esc_html_e('Cung cấp nhiều lựa chọn thanh toán giúp khách hàng hoàn thành đơn hàng dễ hơn.', 'whp'); ?></span>
                    </div>
                </div>
                <div style="display:flex;gap:10px;padding:10px 12px;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;align-items:flex-start;">
                    <span style="width:18px;height:18px;border-radius:50%;background:#a855f7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <div>
                        <strong style="font-size:12px;color:#581c87;display:block;margin-bottom:1px;"><?php esc_html_e('MoMo & ZaloPay ưu tiên', 'whp'); ?></strong>
                        <span style="font-size:11.5px;color:#7c3aed;line-height:1.4;display:block;"><?php esc_html_e('Đây là 2 ví điện tử phổ biến nhất tại Việt Nam — bật trước để phục vụ đa số khách.', 'whp'); ?></span>
                    </div>
                </div>
                <div style="display:flex;gap:10px;padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;align-items:flex-start;">
                    <span style="width:18px;height:18px;border-radius:50%;background:#f97316;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="#fff" stroke-width="2"/></svg>
                    </span>
                    <div>
                        <strong style="font-size:12px;color:#7c2d12;display:block;margin-bottom:1px;"><?php esc_html_e('Kiểm tra sau cài đặt', 'whp'); ?></strong>
                        <span style="font-size:11.5px;color:#c2410c;line-height:1.4;display:block;"><?php esc_html_e('Sau khi nhập API key, hãy thử thanh toán thật để xác nhận kết nối hoạt động đúng.', 'whp'); ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    </div><!-- /mb-wallet-layout -->

    <!-- Save bar -->
    <div class="mb-wph-save-bar">
        <span class="mb-wph-save-note">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="#3858e9" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10" fill="#eff2fe"/><path d="M12 8v4m0 4h.01" stroke="#3858e9" stroke-width="2" stroke-linecap="round"/></svg>
            <div class="mb-wph-save-note-text">
                <strong><?php esc_html_e('Lưu ý', 'whp'); ?></strong>
                <?php esc_html_e('Các thay đổi sẽ được áp dụng ngay sau khi lưu.', 'whp'); ?>
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
    var whpWooI18n={on:'<?php echo esc_js(__("Bật","whp")); ?>',off:'<?php echo esc_js(__("Tắt","whp")); ?>'};
    document.querySelectorAll('.mb-wph-wallet-actions .mb-wph-switch input').forEach(function(input) {
        var label = input.closest('.mb-wph-wallet-actions').querySelector('.mb-wph-toggle-label');
        input.addEventListener('change', function() {
            if (label) {
                label.textContent = this.checked ? whpWooI18n.on : whpWooI18n.off;
                label.classList.toggle('active', this.checked);
            }
        });
    });
})();
(function() {
    var sb = document.querySelector('.mb-wallet-sidebar');
    var lyt = document.querySelector('.mb-wallet-layout');
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
