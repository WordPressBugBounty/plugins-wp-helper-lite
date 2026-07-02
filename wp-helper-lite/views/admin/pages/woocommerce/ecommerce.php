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
    background: linear-gradient(100deg, #ffffff 0%, #fff7ed 45%, #ffedd5 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(234,88,12,0.1), 0 0 0 1px #fed7aa;
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
    background: linear-gradient(135deg, #ea580c, #f97316);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 12px rgba(234,88,12,0.3);
}
.mb-wph-header-right {
    position: absolute; inset: 0 0 0 38%;
    overflow: hidden; pointer-events: none;
}
.mb-wph-page-subtitle { color: #64748b; font-size: 13.5px; line-height: 1.6; margin: 0; padding-left: 58px; max-width: 400px; }
.mb-wph-page-subtitle p { margin: 0; color: inherit; font-size: inherit; line-height: inherit; }
.mb-wph-header-illus-right { display: none; }

/* ── 2-column layout ── */
.mb-eco-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px; align-items: start;
}
@media (max-width: 820px) {
    .mb-eco-layout { grid-template-columns: 1fr; }
    .mb-eco-sidebar { display: none; }
}
.mb-eco-sidebar { position: relative; }

/* ── Card ── */
.mb-wph-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 16px; overflow: hidden;
}
.mb-wph-card-inner { padding: 20px 22px; }
.mb-wph-section-card.accent-orange { border-left: 4px solid #f97316; }

/* ── Section header ── */
.mb-wph-section-header {
    display: flex; align-items: flex-start; gap: 12px;
    margin-bottom: 14px; padding-bottom: 12px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-wph-section-icon {
    width: 36px; height: 36px; border-radius: 9px;
    background: #fff4ed;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mb-wph-section-header-text h3 { margin: 0 0 3px; font-size: 15px; font-weight: 700; color: #0f172a; }
.mb-wph-section-header-text p  { margin: 0; font-size: 13px; color: #64748b; line-height: 1.5; }

/* ── Platform rows ── */
.mb-eco-item {
    display: flex; align-items: center; gap: 12px;
    padding: 13px 0; border-bottom: 1px solid #f8fafc;
}
.mb-eco-item:last-child { border-bottom: none; padding-bottom: 0; }
.mb-eco-item:first-child { padding-top: 0; }

.mb-eco-drag {
    color: #cbd5e1; cursor: grab; flex-shrink: 0;
    display: flex; align-items: center; padding: 4px 2px;
}
.mb-eco-drag:active { cursor: grabbing; }

.mb-eco-logo {
    width: 56px; height: 56px; object-fit: contain;
    border-radius: 12px; border: 1px solid #e2e8f0;
    background: #fff; padding: 5px; flex-shrink: 0;
    box-sizing: border-box; box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}
.mb-eco-info { flex: 1; min-width: 0; }
.mb-eco-name { display: block; font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
.mb-eco-desc { display: block; font-size: 12.5px; color: #64748b; line-height: 1.4; }

.mb-eco-actions { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

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
    left: 4px; top: 4px; transition: transform 0.25s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.18);
}
.mb-wph-switch input:checked + .mb-wph-slider { background: #22c55e; }
.mb-wph-switch input:checked + .mb-wph-slider::after { transform: translateX(22px); }

.mb-eco-toggle-label {
    font-size: 12.5px; font-weight: 700; color: #94a3b8;
    transition: color 0.2s; min-width: 22px;
}
.mb-eco-toggle-label.active { color: #22c55e; }

/* ── Sidebar ── */
.mb-eco-sidebar-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 18px 20px; margin-bottom: 16px;
}
.mb-eco-sidebar-card h4 {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 10px;
}
.mb-eco-guide-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: #eff2fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mb-eco-tip-icon {
    width: 28px; height: 28px; border-radius: 7px;
    background: #fef9c3; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mb-eco-guide-text { font-size: 12.5px; color: #64748b; line-height: 1.6; }
.mb-eco-tips-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.mb-eco-tips-list li {
    display: flex; align-items: flex-start; gap: 8px;
    font-size: 12.5px; color: #475569; line-height: 1.5;
}
.mb-eco-tips-list li::before { content: '✓'; color: #22c55e; font-weight: 700; flex-shrink: 0; margin-top: 1px; }

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

<form method="post" id="mb-woo-ecommerce-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-header-left">
            <div class="mb-wph-header-title-row">
                <div class="mb-wph-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M10 13a5 5 0 0 0 7.54.54l2-2a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="#fff" stroke-width="2" stroke-linecap="round" fill="none"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-2 2a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="#fff" stroke-width="2" stroke-linecap="round" fill="none"/>
                    </svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Sàn thương mại điện tử', 'whp'); ?></h1>
            </div>
            <div class="mb-wph-page-subtitle"><?php echo wp_kses_post($itemInfo['desc'] ?? 'Tính năng này cho phép bạn tạo đường dẫn cho sản phẩm đã được đăng ở các sàn thương mại điện tử: Shopee, Lazada, Tiki, Sendo.'); ?></div>
        </div>
        <div class="mb-wph-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="eco_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#fff7ed" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#ffedd5" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#fed7aa" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="eco_sh" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(234,88,12,0.18)"/>
                    </filter>
                    <filter id="eco_shSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(234,88,12,0.12)"/>
                    </filter>
                </defs>
                <rect width="680" height="168" fill="url(#eco_hbg)"/>
                <circle cx="602" cy="18" r="66" fill="#ea580c" fill-opacity=".04"/>
                <circle cx="648" cy="148" r="42" fill="#f97316" fill-opacity=".05"/>
                <!-- Store front center -->
                <g filter="url(#eco_sh)">
                    <rect x="382" y="20" width="120" height="128" rx="12" fill="#fff"/>
                    <rect x="382" y="20" width="120" height="36" rx="12" fill="#ea580c" fill-opacity=".9"/>
                    <rect x="382" y="44" width="120" height="12" fill="#ea580c" fill-opacity=".9"/>
                    <text x="442" y="44" font-size="12" font-weight="700" fill="#fff" text-anchor="middle" font-family="sans-serif">SHOP</text>
                    <!-- Storefront shelves -->
                    <rect x="392" y="64" width="30" height="30" rx="5" fill="#fff7ed" stroke="#fed7aa" stroke-width="1"/>
                    <rect x="428" y="64" width="30" height="30" rx="5" fill="#fff7ed" stroke="#fed7aa" stroke-width="1"/>
                    <rect x="464" y="64" width="30" height="30" rx="5" fill="#fff7ed" stroke="#fed7aa" stroke-width="1"/>
                    <!-- Product icons on shelves -->
                    <rect x="398" y="70" width="18" height="18" rx="3" fill="#fdba74" fill-opacity=".7"/>
                    <rect x="434" y="70" width="18" height="18" rx="3" fill="#f97316" fill-opacity=".5"/>
                    <rect x="470" y="70" width="18" height="18" rx="3" fill="#ea580c" fill-opacity=".4"/>
                    <!-- Bottom shelves -->
                    <rect x="392" y="100" width="100" height="3" rx="1.5" fill="#fed7aa"/>
                    <rect x="392" y="110" width="70" height="8" rx="4" fill="#ffedd5"/>
                    <rect x="392" y="122" width="100" height="3" rx="1.5" fill="#fed7aa"/>
                    <rect x="420" y="130" width="44" height="14" rx="4" fill="#ea580c" fill-opacity=".85"/>
                    <rect x="427" y="134" width="30" height="6" rx="3" fill="#fff" fill-opacity=".8"/>
                </g>
                <!-- Shopee badge -->
                <g filter="url(#eco_shSm)">
                    <rect x="518" y="18" width="60" height="60" rx="14" fill="#fff"/>
                    <rect x="522" y="22" width="52" height="52" rx="12" fill="#ee4d2d" fill-opacity=".9"/>
                    <!-- Shopee bag icon -->
                    <path d="M534 46c0-5.5 4.5-10 10-10s10 4.5 10 10" stroke="#fff" stroke-width="2.2" fill="none" stroke-linecap="round"/>
                    <rect x="528" y="46" width="24" height="18" rx="4" fill="#fff" fill-opacity=".25" stroke="#fff" stroke-width="1.5"/>
                    <circle cx="534" cy="55" r="2" fill="#fff" fill-opacity=".7"/>
                    <circle cx="546" cy="55" r="2" fill="#fff" fill-opacity=".7"/>
                    <text x="548" y="65" font-size="9" font-weight="700" fill="#fff" text-anchor="middle" font-family="sans-serif">S</text>
                </g>
                <!-- Lazada badge -->
                <g filter="url(#eco_shSm)">
                    <rect x="518" y="90" width="60" height="56" rx="14" fill="#fff"/>
                    <rect x="522" y="94" width="52" height="48" rx="12" fill="#f57224" fill-opacity=".9"/>
                    <text x="548" y="124" font-size="22" font-weight="900" fill="#fff" text-anchor="middle" font-family="sans-serif">L</text>
                </g>
                <!-- Tiki badge -->
                <g filter="url(#eco_shSm)">
                    <rect x="594" y="18" width="54" height="54" rx="14" fill="#fff"/>
                    <rect x="598" y="22" width="46" height="46" rx="12" fill="#189eff" fill-opacity=".9"/>
                    <text x="621" y="51" font-size="20" font-weight="900" fill="#fff" text-anchor="middle" font-family="sans-serif">Ti</text>
                </g>
                <!-- Sendo badge -->
                <g filter="url(#eco_shSm)">
                    <rect x="594" y="86" width="54" height="50" rx="14" fill="#fff"/>
                    <rect x="598" y="90" width="46" height="42" rx="12" fill="#d0021b" fill-opacity=".9"/>
                    <text x="621" y="117" font-size="16" font-weight="900" fill="#fff" text-anchor="middle" font-family="sans-serif">Se</text>
                </g>
                <!-- Link arrows -->
                <path d="M506 46 Q512 46 516 46" stroke="#fed7aa" stroke-width="2" fill="none" marker-end="url(#eco_arrow)"/>
                <path d="M506 118 Q512 118 516 118" stroke="#fed7aa" stroke-width="2" fill="none"/>
                <!-- Decoration dots -->
                <circle cx="655" cy="40" r="7" fill="#fed7aa" fill-opacity=".6"/>
                <circle cx="670" cy="90" r="5" fill="#ffedd5" fill-opacity=".8"/>
                <circle cx="645" cy="148" r="8" fill="#fed7aa" fill-opacity=".4"/>
            </svg>
        </div>
    </div>

    <!-- 2-column layout -->
    <div class="mb-eco-layout">

        <!-- Left: main card -->
        <div>
            <div class="mb-wph-card mb-wph-section-card accent-orange">
                <div class="mb-wph-card-inner">
                    <div class="mb-wph-section-header">
                        <div class="mb-wph-section-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </div>
                        <div class="mb-wph-section-header-text">
                            <h3><?php esc_html_e('Liên kết sàn thương mại', 'whp'); ?></h3>
                            <p><?php esc_html_e('Thêm liên kết dẫn khách hàng tới trang sản phẩm của bạn trên các sàn TMĐT.', 'whp'); ?></p>
                        </div>
                    </div>

                    <?php
                    $ecommerce_meta = [
                        'tiki'   => ['name' => 'Tiki',   'desc' => __('Hiển thị nút liên kết dẫn khách hàng tới trang sản phẩm của bạn trên Tiki.', 'whp')],
                        'shopee' => ['name' => 'Shopee', 'desc' => __('Hiển thị nút liên kết dẫn khách hàng tới trang sản phẩm của bạn trên Shopee.', 'whp')],
                        'lazada' => ['name' => 'Lazada', 'desc' => __('Hiển thị nút liên kết dẫn khách hàng tới trang sản phẩm của bạn trên Lazada.', 'whp')],
                        'sendo'  => ['name' => 'Sendo',  'desc' => __('Hiển thị nút liên kết dẫn khách hàng tới trang sản phẩm của bạn trên Sendo.', 'whp')],
                    ];
                    $listEcommerce = $data['listEcommerce'] ?? whp_get_list_ecommerce();
                    foreach ($listEcommerce as $keyList => $itemList) :
                        $itemId    = "whp_woocommerce_ecommerce_{$keyList}";
                        $imgUrl    = $itemList['url'] ?? '';
                        $meta      = $ecommerce_meta[$keyList] ?? ['name' => ucfirst($keyList), 'desc' => ''];
                        $isChecked = $$itemId == '1';
                    ?>
                    <div class="mb-eco-item">
                        <div class="mb-eco-drag">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#cbd5e1"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg>
                        </div>
                        <img src="<?php echo esc_url($imgUrl); ?>" alt="<?php echo esc_attr($meta['name']); ?>" class="mb-eco-logo">
                        <div class="mb-eco-info">
                            <span class="mb-eco-name"><?php echo esc_html($meta['name']); ?></span>
                            <span class="mb-eco-desc"><?php echo esc_html($meta['desc']); ?></span>
                        </div>
                        <div class="mb-eco-actions">
                            <span class="mb-eco-toggle-label <?php echo $isChecked ? 'active' : ''; ?>">
                                <?php echo $isChecked ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?>
                            </span>
                            <label class="mb-wph-switch">
                                <input type="checkbox" name="<?php echo esc_attr($itemId); ?>" value="1" <?php echo $isChecked ? 'checked' : ''; ?>>
                                <span class="mb-wph-slider"></span>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>

        <!-- Right: sidebar -->
        <div class="mb-eco-sidebar">

            <!-- Hướng dẫn & Mẹo sử dụng -->
            <div class="mb-eco-sidebar-card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h4 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;">Hướng dẫn &amp; Mẹo sử dụng</h4>
                        <p style="margin:0;font-size:11.5px;color:#94a3b8;">Mở rộng kênh bán hàng</p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Bật nhiều sàn cùng lúc', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#16a34a;line-height:1.4;display:block;"><?php esc_html_e('Bật Shopee + Lazada + Tiki + Sendo để hiển thị liên kết đa sàn trên từng sản phẩm.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M10 13a5 5 0 0 0 7.54.54l2-2a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="#fff" stroke-width="2" stroke-linecap="round"/><path d="M14 11a5 5 0 0 0-7.54-.54l-2 2a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#1e3a8a;display:block;margin-bottom:1px;"><?php esc_html_e('Gắn link từng sản phẩm', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#2563eb;line-height:1.4;display:block;"><?php esc_html_e('Vào từng sản phẩm → điền URL liên kết tới trang sản phẩm tương ứng trên sàn.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#f97316;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="#fff" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="#fff" stroke-width="2"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#7c2d12;display:block;margin-bottom:1px;"><?php esc_html_e('Hiển thị trên trang sản phẩm', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#c2410c;line-height:1.4;display:block;"><?php esc_html_e('Nút liên kết xuất hiện ngay dưới thông tin sản phẩm, giúp khách mua nhanh trên sàn.', 'whp'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

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
    document.querySelectorAll('.mb-eco-actions .mb-wph-switch input').forEach(function(input) {
        var label = input.closest('.mb-eco-actions').querySelector('.mb-eco-toggle-label');
        input.addEventListener('change', function() {
            if (label) {
                label.textContent = this.checked ? whpWooI18n.on : whpWooI18n.off;
                label.classList.toggle('active', this.checked);
            }
        });
    });
})();
(function() {
    var sb = document.querySelector('.mb-eco-sidebar');
    var lyt = document.querySelector('.mb-eco-layout');
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
