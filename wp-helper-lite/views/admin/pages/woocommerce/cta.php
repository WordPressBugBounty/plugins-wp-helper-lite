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
    background: linear-gradient(100deg, #ffffff 0%, #f5f3ff 45%, #ede9fe 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(124,58,237,0.1), 0 0 0 1px #ddd6fe;
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
    background: linear-gradient(135deg, #7c3aed, #a78bfa);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 12px rgba(124,58,237,0.3);
}
.mb-wph-header-right {
    position: absolute; inset: 0 0 0 38%;
    overflow: hidden; pointer-events: none;
}
.mb-wph-page-subtitle { color: #64748b; font-size: 13.5px; line-height: 1.6; margin: 0; padding-left: 58px; max-width: 400px; }
.mb-wph-page-subtitle p { margin: 0; color: inherit; font-size: inherit; line-height: inherit; }
.mb-cta-header-illus { display: none; }

/* ── 2-column layout ── */
.mb-cta-layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
@media (max-width: 820px) { .mb-cta-layout { grid-template-columns: 1fr; } .mb-cta-sidebar { display: none; } }
.mb-cta-sidebar { position: relative; }

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
    margin-bottom: 16px; padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-wph-section-icon {
    width: 36px; height: 36px; border-radius: 9px;
    background: #eff2fe;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mb-wph-section-header-text h3 { margin: 0 0 3px; font-size: 15px; font-weight: 700; color: #0f172a; }
.mb-wph-section-header-text p  { margin: 0; font-size: 13px; color: #64748b; line-height: 1.5; }

/* ── Text field ── */
.mb-cta-field { margin-bottom: 20px; }
.mb-cta-hint {
    font-size: 12px; color: #64748b; margin-top: 6px;
    display: flex; align-items: center; gap: 5px;
}

/* ── Toggle rows ── */
.mb-cta-toggle-row {
    padding: 14px 0; border-bottom: 1px solid #f8fafc;
}
.mb-cta-toggle-row:last-child { border-bottom: none; padding-bottom: 0; }
.mb-cta-toggle-top {
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    margin-bottom: 4px;
}
.mb-cta-toggle-name { font-size: 14px; font-weight: 600; color: #0f172a; }
.mb-cta-toggle-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; }
.mb-cta-toggle-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* Toggle switch */
.mb-wph-switch { position: relative; display: inline-block; width: 48px; height: 26px; }
.mb-wph-switch input { opacity: 0; width: 0; height: 0; }
.mb-wph-slider {
    position: absolute; inset: 0;
    background: #cbd5e1; border-radius: 26px;
    cursor: pointer; transition: background 0.25s;
}
.mb-wph-slider::after {
    content: ''; position: absolute;
    width: 18px; height: 18px; background: #fff; border-radius: 50%;
    left: 4px; top: 4px; transition: transform 0.25s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.18);
}
.mb-wph-switch input:checked + .mb-wph-slider { background: #22c55e; }
.mb-wph-switch input:checked + .mb-wph-slider::after { transform: translateX(22px); }

.mb-cta-toggle-label {
    font-size: 12.5px; font-weight: 700; color: #94a3b8;
    transition: color 0.2s; min-width: 22px; text-align: right;
}
.mb-cta-toggle-label.active { color: #22c55e; }

/* ── Sidebar ── */
.mb-cta-sidebar-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 18px 20px; margin-bottom: 16px;
}
.mb-cta-sidebar-card h4 { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 10px; }
.mb-cta-guide-icon { width: 28px; height: 28px; border-radius: 7px; background: #eff2fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mb-cta-tip-icon  { width: 28px; height: 28px; border-radius: 7px; background: #fef9c3; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mb-cta-guide-text { font-size: 12.5px; color: #64748b; line-height: 1.6; }
.mb-cta-tips-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.mb-cta-tips-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; color: #475569; line-height: 1.5; }
.mb-cta-tips-list li::before { content: '✓'; color: #22c55e; font-weight: 700; flex-shrink: 0; margin-top: 1px; }

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

<form method="post" id="mb-woo-cta-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-header-left">
            <div class="mb-wph-header-title-row">
                <div class="mb-wph-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="10" width="18" height="10" rx="3" fill="#fff" fill-opacity=".9"/>
                        <rect x="3" y="10" width="18" height="10" rx="3" stroke="#a78bfa" stroke-width=".5" fill="none"/>
                        <rect x="7" y="13" width="10" height="4" rx="2" fill="#7c3aed" fill-opacity=".6"/>
                        <!-- Cursor -->
                        <path d="M14 6l3 8-2.5-1.5-1.5 3-1.5-1.5 1.5-3L11 10z" fill="#fff" fill-opacity=".85"/>
                    </svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Nút mua hàng (CTA)', 'whp'); ?></h1>
            </div>
            <div class="mb-wph-page-subtitle"><?php echo wp_kses_post($itemInfo['desc'] ?? 'Bằng việc thay đổi nội dung và cài đặt khác của nút mua hàng sẽ thu hút khách hàng tốt hơn dẫn đến việc bán hàng của bạn sẽ hiệu quả hơn.'); ?></div>
        </div>
        <div class="mb-wph-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="cta_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f5f3ff" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#ede9fe" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#ddd6fe" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="cta_sh" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(124,58,237,0.18)"/>
                    </filter>
                    <filter id="cta_shSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(124,58,237,0.12)"/>
                    </filter>
                </defs>
                <rect width="680" height="168" fill="url(#cta_hbg)"/>
                <circle cx="608" cy="20" r="70" fill="#7c3aed" fill-opacity=".04"/>
                <circle cx="655" cy="148" r="46" fill="#a78bfa" fill-opacity=".05"/>
                <!-- Product page mockup -->
                <g filter="url(#cta_sh)">
                    <rect x="345" y="12" width="170" height="144" rx="14" fill="#fff"/>
                    <rect x="345" y="12" width="170" height="34" rx="14" fill="#7c3aed" fill-opacity=".07"/>
                    <rect x="345" y="36" width="170" height="10" fill="#7c3aed" fill-opacity=".07"/>
                    <!-- Window dots -->
                    <circle cx="360" cy="29" r="4" fill="#ef4444" fill-opacity=".6"/>
                    <circle cx="372" cy="29" r="4" fill="#f59e0b" fill-opacity=".6"/>
                    <circle cx="384" cy="29" r="4" fill="#22c55e" fill-opacity=".6"/>
                    <!-- URL bar -->
                    <rect x="396" y="23" width="106" height="12" rx="6" fill="#ede9fe"/>
                    <rect x="400" y="26" width="70" height="6" rx="3" fill="#c4b5fd" fill-opacity=".7"/>
                    <!-- Product image -->
                    <rect x="355" y="52" width="68" height="68" rx="10" fill="#f5f3ff"/>
                    <rect x="363" y="60" width="52" height="52" rx="8" fill="#ede9fe"/>
                    <rect x="370" y="68" width="38" height="28" rx="5" fill="#7c3aed" fill-opacity=".2"/>
                    <rect x="374" y="72" width="30" height="20" rx="4" fill="#a78bfa" fill-opacity=".5"/>
                    <!-- Star rating -->
                    <text x="357" y="128" fill="#f59e0b" font-size="10">★★★★★</text>
                    <!-- Product info -->
                    <rect x="431" y="52" width="76" height="8" rx="4" fill="#ddd6fe"/>
                    <rect x="431" y="64" width="58" height="6" rx="3" fill="#ede9fe"/>
                    <rect x="431" y="74" width="40" height="10" rx="5" fill="#7c3aed" fill-opacity=".8"/>
                    <rect x="436" y="77" width="30" height="4" rx="2" fill="#fff" fill-opacity=".8"/>
                    <!-- CTA button BIG - "Mua ngay" -->
                    <rect x="431" y="92" width="76" height="28" rx="8" fill="#7c3aed"/>
                    <rect x="431" y="92" width="76" height="28" rx="8" fill="url(#cta_hbg)" fill-opacity=".2"/>
                    <text x="469" y="110" font-size="11" font-weight="700" fill="#fff" text-anchor="middle" font-family="sans-serif">Mua ngay</text>
                    <!-- Add to cart button -->
                    <rect x="431" y="126" width="76" height="24" rx="8" fill="#ddd6fe"/>
                    <text x="469" y="142" font-size="10" font-weight="600" fill="#7c3aed" text-anchor="middle" font-family="sans-serif">Thêm giỏ</text>
                </g>
                <!-- Mouse cursor clicking CTA -->
                <g filter="url(#cta_shSm)">
                    <path d="M524 90l8 22-6-4-4 8-4-4 4-8-6-2z" fill="#7c3aed" fill-opacity=".9"/>
                    <circle cx="526" cy="88" r="6" fill="#7c3aed" fill-opacity=".15"/>
                </g>
                <!-- Conversion funnel -->
                <g filter="url(#cta_shSm)">
                    <rect x="548" y="18" width="68" height="128" rx="12" fill="#fff"/>
                    <!-- Funnel steps -->
                    <rect x="555" y="28" width="54" height="20" rx="5" fill="#7c3aed" fill-opacity=".15"/>
                    <text x="582" y="42" font-size="8" fill="#7c3aed" text-anchor="middle" font-family="sans-serif" font-weight="600">Khách thăm</text>
                    <path d="M565 48l17 8 17-8" fill="#ddd6fe" fill-opacity=".6"/>
                    <rect x="560" y="56" width="44" height="18" rx="5" fill="#7c3aed" fill-opacity=".25"/>
                    <text x="582" y="69" font-size="8" fill="#7c3aed" text-anchor="middle" font-family="sans-serif" font-weight="600">Xem SP</text>
                    <path d="M568 74l14 7 14-7" fill="#c4b5fd" fill-opacity=".6"/>
                    <rect x="564" y="81" width="36" height="16" rx="5" fill="#7c3aed" fill-opacity=".4"/>
                    <text x="582" y="93" font-size="8" fill="#fff" text-anchor="middle" font-family="sans-serif" font-weight="600">Click CTA</text>
                    <path d="M570 97l12 6 12-6" fill="#a78bfa" fill-opacity=".7"/>
                    <rect x="568" y="103" width="28" height="16" rx="5" fill="#7c3aed" fill-opacity=".7"/>
                    <text x="582" y="115" font-size="8" fill="#fff" text-anchor="middle" font-family="sans-serif" font-weight="600">Đặt hàng</text>
                    <path d="M572 119l10 5 10-5" fill="#7c3aed" fill-opacity=".7"/>
                    <rect x="570" y="124" width="24" height="16" rx="5" fill="#7c3aed"/>
                    <text x="582" y="136" font-size="8" fill="#fff" text-anchor="middle" font-family="sans-serif" font-weight="600">Thành công</text>
                    <!-- Check at bottom -->
                    <circle cx="582" cy="140" r="0" fill="#22c55e"/>
                </g>
                <!-- Success badge -->
                <g filter="url(#cta_shSm)">
                    <circle cx="643" cy="52" r="24" fill="#22c55e"/>
                    <path d="M633 52l7 7 12-14" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <!-- Star ratings floating -->
                <text x="628" y="100" fill="#f59e0b" font-size="14" font-family="sans-serif">★</text>
                <text x="648" y="118" fill="#f59e0b" font-size="11" font-family="sans-serif" fill-opacity=".7">★</text>
                <text x="625" y="130" fill="#f59e0b" font-size="10" font-family="sans-serif" fill-opacity=".5">★</text>
                <!-- Decorations -->
                <circle cx="660" cy="145" r="7" fill="#ddd6fe" fill-opacity=".7"/>
                <circle cx="672" cy="80" r="5" fill="#ede9fe" fill-opacity=".8"/>
            </svg>
        </div>
    </div>

    <!-- 2-column layout -->
    <div class="mb-cta-layout">

        <!-- Left: main card -->
        <div>
            <div class="mb-wph-card mb-wph-section-card">
                <div class="mb-wph-card-inner">
                    <div class="mb-wph-section-header">
                        <div class="mb-wph-section-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <div class="mb-wph-section-header-text">
                            <h3><?php esc_html_e('Tùy chỉnh nút CTA', 'whp'); ?></h3>
                            <p><?php esc_html_e('Tối ưu nút mua hàng để tăng tỷ lệ chuyển đổi.', 'whp'); ?></p>
                        </div>
                    </div>

                    <!-- Nội dung nút -->
                    <div class="mb-wph-field mb-cta-field">
                        <label for="whp_woocommerce_cta_text"><?php esc_html_e('Nội dung nút mua hàng', 'whp'); ?></label>
                        <input type="text" id="whp_woocommerce_cta_text" name="whp_woocommerce_cta_text"
                            class="mb-wph-input" placeholder="<?php esc_attr_e('Vd: Thêm vào giỏ hàng, Mua ngay...', 'whp'); ?>"
                            value="<?php echo esc_attr($whp_woocommerce_cta_text); ?>">
                        <p class="mb-wph-hint">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="#64748b"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                            <?php esc_html_e('Bằng việc thay đổi nội dung nút mua hàng sẽ thu hút khách hàng tốt hơn.', 'whp'); ?>
                        </p>
                    </div>

                    <!-- Toggle 1 -->
                    <?php $c1 = $whp_woocommerce_cta_convert_zero_to_contact_check === 'checked'; ?>
                    <div class="mb-cta-toggle-row">
                        <div class="mb-cta-toggle-top">
                            <span class="mb-cta-toggle-name"><?php esc_html_e('Chuyển giá 0đ thành liên hệ', 'whp'); ?></span>
                            <div class="mb-cta-toggle-actions">
                                <span class="mb-cta-toggle-label <?php echo $c1 ? 'active' : ''; ?>" id="lbl_cta1">
                                    <?php echo $c1 ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                                </span>
                                <label class="mb-wph-switch">
                                    <input type="checkbox" id="inp_cta1" name="whp_woocommerce_cta_convert_zero_to_contact" value="1" <?php echo $c1 ? 'checked' : ''; ?>>
                                    <span class="mb-wph-slider"></span>
                                </label>
                            </div>
                        </div>
                        <p class="mb-cta-toggle-desc"><?php esc_html_e('Sản phẩm giá 0đ sẽ hiển thị nút liên hệ thay vì Mua hàng.', 'whp'); ?></p>
                    </div>

                    <!-- Toggle 2 -->
                    <?php $c2 = $whp_woocommerce_cta_show_buynow_button_check === 'checked'; ?>
                    <div class="mb-cta-toggle-row">
                        <div class="mb-cta-toggle-top">
                            <span class="mb-cta-toggle-name"><?php esc_html_e('Thêm nút mua hàng ngay', 'whp'); ?></span>
                            <div class="mb-cta-toggle-actions">
                                <span class="mb-cta-toggle-label <?php echo $c2 ? 'active' : ''; ?>" id="lbl_cta2">
                                    <?php echo $c2 ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                                </span>
                                <label class="mb-wph-switch">
                                    <input type="checkbox" id="inp_cta2" name="whp_woocommerce_cta_show_buynow_button" value="1" <?php echo $c2 ? 'checked' : ''; ?>>
                                    <span class="mb-wph-slider"></span>
                                </label>
                            </div>
                        </div>
                        <p class="mb-cta-toggle-desc"><?php esc_html_e('Hiển thị nút Mua ngay tại trang chi tiết sản phẩm.', 'whp'); ?></p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Right: sidebar -->
        <div class="mb-cta-sidebar">

            <!-- Hướng dẫn & Mẹo sử dụng -->
            <div class="mb-cta-sidebar-card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h4 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;"><?php esc_html_e('Hướng dẫn & Mẹo sử dụng', 'whp'); ?></h4>
                        <p style="margin:0;font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Tăng tỉ lệ chuyển đổi', 'whp'); ?></p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Nội dung ngắn gọn', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#16a34a;line-height:1.4;display:block;"><?php esc_html_e('Text CTA càng ngắn càng tốt — "Mua ngay", "Thêm vào giỏ" rõ ràng hơn câu dài.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#1e3a8a;display:block;margin-bottom:1px;"><?php esc_html_e('Bật "Mua ngay"', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#2563eb;line-height:1.4;display:block;"><?php esc_html_e('Nút "Mua ngay" bỏ qua giỏ hàng, giảm bước thanh toán và tăng tỉ lệ hoàn thành đơn.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#a855f7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#581c87;display:block;margin-bottom:1px;"><?php esc_html_e('Sản phẩm giá 0đ', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#7c3aed;line-height:1.4;display:block;"><?php esc_html_e('Dùng nút "Liên hệ" hoặc "Nhận báo giá" thay cho "Mua ngay" để tránh nhầm lẫn.', 'whp'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div><!-- /mb-cta-layout -->

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
    [['inp_cta1','lbl_cta1'], ['inp_cta2','lbl_cta2']].forEach(function(pair) {
        var inp = document.getElementById(pair[0]);
        var lbl = document.getElementById(pair[1]);
        if (!inp || !lbl) return;
        inp.addEventListener('change', function() {
            lbl.textContent = this.checked ? whpWooI18n.on : whpWooI18n.off;
            lbl.classList.toggle('active', this.checked);
        });
    });
})();
(function() {
    var sb = document.querySelector('.mb-cta-sidebar');
    var lyt = document.querySelector('.mb-cta-layout');
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
