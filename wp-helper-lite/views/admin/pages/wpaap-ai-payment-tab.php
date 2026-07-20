<?php
defined('ABSPATH') || exit;

function wpaap_ai_payment_tab_layout()
{
    /* whp-deploy-v2026070207 */
    $active_tab = isset($_GET['aipay_tab']) ? sanitize_key($_GET['aipay_tab']) : 'verify';

    // ── Settings ─────────────────────────────────────────────────────────
    $aipay_enable   = whp_get_setting('whp_aipay_enable');
    $ocr_enable     = whp_get_setting('whp_aipay_ocr_enable');
    $fraud_enable   = whp_get_setting('whp_aipay_fraud_enable');
    $copilot_enable = whp_get_setting('whp_aipay_copilot_enable');
    $is_active      = ($aipay_enable === '1');

    // Thank You page dependency: AI Payment requires Thank You page to be active
    $thankyou_active            = (whp_get_setting('whp_woo_thankyou_enable') === '1');
    $aipay_blocked_by_thankyou  = $is_active && !$thankyou_active;
    if ($aipay_blocked_by_thankyou) { $is_active = false; }

    // ── AI Providers ──────────────────────────────────────────────────────
    $prov_list = [
        'google'    => ['label' => 'Google Gemini',    'connected' => false],
        'anthropic' => ['label' => 'Anthropic Claude', 'connected' => false],
        'openai'    => ['label' => 'OpenAI GPT',       'connected' => false],
    ];
    foreach ($prov_list as $p => $_) {
        $prov_list[$p]['connected'] = function_exists('wpaap_is_provider_connected') && wpaap_is_provider_connected($p);
    }
    $is_connected = false;
    foreach ($prov_list as $pd) { if ($pd['connected']) { $is_connected = true; break; } }
    // AI Payment cannot be active without an AI connection
    if (!$is_connected) { $is_active = false; }

    // ── Stats ─────────────────────────────────────────────────────────────
    $s_pending = 0; $s_verified = 0; $s_risk = 0; $s_total = 0.0; $s_total_count = 0;
    $y_verified = 0; $y_risk = 0; $y_total = 0.0; $y_total_count = 0;
    if (function_exists('wc_get_orders')) {
        $s_pending = count(wc_get_orders([
            'status'     => ['on-hold'],
            'meta_query' => [['key' => '_whp_transfer_confirmed_at', 'compare' => 'EXISTS']],
            'limit' => -1, 'return' => 'ids',
        ]));
        // Today orders
        $today_orders = wc_get_orders([
            'date_created' => date('Y-m-d'),
            'meta_query'   => [['key' => '_whp_transfer_confirmed_at', 'compare' => 'EXISTS']],
            'limit' => 500, 'return' => 'objects',
        ]);
        $s_total_count = count($today_orders);
        foreach ($today_orders as $ord) {
            $s_total += floatval($ord->get_total());
            $ai = $ord->get_meta('_whp_ai_verify_result');
            if (is_array($ai) && !empty($ai['verdict'])) {
                if ($ai['verdict'] === 'valid') $s_verified++;
                elseif (in_array($ai['verdict'], ['suspicious', 'invalid'])) $s_risk++;
            }
        }
        // Yesterday orders (for trend comparison)
        $yesterday_orders = wc_get_orders([
            'date_created' => date('Y-m-d', strtotime('-1 day')),
            'meta_query'   => [['key' => '_whp_transfer_confirmed_at', 'compare' => 'EXISTS']],
            'limit' => 500, 'return' => 'objects',
        ]);
        $y_total_count = count($yesterday_orders);
        foreach ($yesterday_orders as $ord) {
            $y_total += floatval($ord->get_total());
            $ai = $ord->get_meta('_whp_ai_verify_result');
            if (is_array($ai) && !empty($ai['verdict'])) {
                if ($ai['verdict'] === 'valid') $y_verified++;
                elseif (in_array($ai['verdict'], ['suspicious', 'invalid'])) $y_risk++;
            }
        }
    }
    // Verify rate % (today vs yesterday)
    $s_verify_rate = $s_total_count > 0 ? round($s_verified / $s_total_count * 100) : 0;
    $y_verify_rate = $y_total_count > 0 ? round($y_verified / $y_total_count * 100) : 0;
    // Trend helper: returns ['pct'=>int,'dir'=>'up'|'down'] or null
    $stat_trend = static function($now, $prev) {
        if ($prev <= 0) return null;
        return ['pct' => round(abs($now - $prev) / $prev * 100), 'dir' => ($now >= $prev) ? 'up' : 'down'];
    };

    // ── Tab navigation ────────────────────────────────────────────────────
    $nav_tabs = [
        'config' => [
            'label' => __('Cấu hình', 'whp'),
            'icon'  => '<path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00.73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="3"/>',
        ],
        'notifications' => [
            'label' => __('Thông báo đa kênh', 'whp'),
            'icon'  => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        ],
        'verify' => [
            'label' => __('Xác minh thanh toán', 'whp'),
            'icon'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
        ],
        'queue' => [
            'label' => __('Đơn chờ xử lý', 'whp'),
            'icon'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            'badge' => $s_pending,
        ],
        'verified' => [
            'label' => __('Xác minh thành công', 'whp'),
            'icon'  => '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
            'badge' => $s_verified,
            'badge_green' => true,
        ],
        'risk' => [
            'label' => __('Giao dịch rủi ro', 'whp'),
            'icon'  => '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'badge' => $s_risk,
            'badge_red' => $s_risk > 0,
        ],
        'logs' => [
            'label' => __('Nhật ký', 'whp'),
            'icon'  => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        ],
    ];

    $base_url    = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=');
    $has_form    = in_array($active_tab, ['config', 'notifications']);
    $sub_base    = MB_WHP_PATH . 'views/admin/pages/wpaap-ai-payment/';
    ?>
<style>
.wpaap-aip-wrap { max-width:1200px; margin:0 auto; padding:0 0 88px; font-family:inherit; }

/* ── Header ─── */
.wpaap-aip-header {
    position:relative; border-radius:20px; overflow:hidden; margin-bottom:18px;
    background:
        radial-gradient(ellipse at 88% 15%, rgba(225,29,72,.09) 0%, transparent 52%),
        radial-gradient(ellipse at 94% 88%, rgba(190,18,60,.07) 0%, transparent 46%),
        linear-gradient(110deg,#ffffff 0%,#fff8f9 38%,#ffe4e6 100%);
    box-shadow:0 4px 28px rgba(225,29,72,.12),0 0 0 1px #fecdd3;
    min-height:168px; display:flex; align-items:stretch;
}
.wpaap-aip-header-inner {
    position:relative;z-index:1;display:flex;align-items:stretch;
    justify-content:space-between;width:100%;
}
.wpaap-aip-header-left {
    position:relative;z-index:2;padding:32px 36px;
    display:flex;flex-direction:column;justify-content:center;gap:12px;
    max-width:480px;flex-shrink:0;
}
.wpaap-aip-header-title-row{display:flex;align-items:center;gap:14px;}
.wpaap-aip-header-icon-box{
    width:44px;height:44px;border-radius:12px;
    background:linear-gradient(135deg,#e11d48,#be123c);
    display:flex;align-items:center;justify-content:center;
    flex-shrink:0;box-shadow:0 4px 12px rgba(225,29,72,.3);
}
.wpaap-aip-header h2{margin:0;font-size:24px;font-weight:700;color:#0f172a;letter-spacing:-.4px;}
.wpaap-aip-header p {margin:0;font-size:13.5px;color:#64748b;line-height:1.6;max-width:400px;padding-left:58px;}
.wpaap-aip-header-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding-left:58px;}
/* Header decoration */
.wpaap-aip-header-deco{position:absolute;inset:0 0 0 40%;pointer-events:none;overflow:hidden;}

/* ── Stats ─── */
.wpaap-aip-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;}
.wpaap-aip-stat {
    background:#fff;border:1px solid #e2e8f0;border-radius:14px;
    padding:16px 18px;box-shadow:0 1px 4px rgba(0,0,0,.04);
    display:flex;align-items:center;gap:14px;
}
.wpaap-aip-stat-icon{
    flex-shrink:0;width:48px;height:48px;border-radius:13px;
    display:flex;align-items:center;justify-content:center;
}
.wpaap-aip-stat-icon.rose{background:#fff1f2;} .wpaap-aip-stat-icon.blue{background:#eff6ff;}
.wpaap-aip-stat-icon.amber{background:#fffbeb;} .wpaap-aip-stat-icon.red{background:#fef2f2;}
.wpaap-aip-stat-body{flex:1;min-width:0;}
.wpaap-aip-stat-lbl{font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.wpaap-aip-stat-val{font-size:22px;font-weight:800;color:#0f172a;line-height:1.1;margin-bottom:2px;}
.wpaap-aip-stat-val.sm{font-size:14px;}
.wpaap-aip-stat-trend{font-size:11px;font-weight:600;margin-top:3px;}
.wpaap-aip-stat-trend.trend-good{color:#e11d48;}
.wpaap-aip-stat-trend.trend-bad{color:#dc2626;}

/* ── Sub-nav ─── */
.wpaap-aip-subnav{
    display:flex; gap:2px; padding:4px;
    background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;
    margin-bottom:18px; overflow-x:auto; scrollbar-width:thin;
}
.wpaap-aip-subnav::-webkit-scrollbar{height:4px;}
.wpaap-aip-subnav::-webkit-scrollbar-thumb{background:#fda4af;border-radius:4px;}
.wpaap-aip-nav-item{
    flex:1; display:inline-flex; align-items:center; justify-content:center;
    gap:7px; padding:7px 12px; border-radius:7px;
    font-size:13px; font-weight:500; color:#64748b;
    text-decoration:none; white-space:nowrap; text-align:center;
    transition:all .15s; background:transparent; flex-shrink:0;
}
.wpaap-aip-nav-item.active{background:#fff;color:#e11d48;font-weight:700;box-shadow:0 1px 4px rgba(0,0,0,.08);}
.wpaap-aip-nav-item:not(.active):hover{color:#334155;background:rgba(255,255,255,.6);}
.wpaap-aip-nav-badge{font-size:10px;font-weight:700;padding:1px 7px;border-radius:20px;background:#e2e8f0;color:#64748b;}
.wpaap-aip-nav-badge.red{background:#fee2e2;color:#991b1b;}
.wpaap-aip-nav-badge.green{background:#dcfce7;color:#15803d;}
.wpaap-aip-nav-item.active .wpaap-aip-nav-badge{background:rgba(225,29,72,.12);color:#e11d48;}
.wpaap-aip-nav-item.active .wpaap-aip-nav-badge.red{background:#fee2e2;color:#991b1b;}

/* ── Header toggle ─── */
.whp-htoggle-wrap{display:inline-flex;align-items:center;gap:10px;}
.whp-htoggle{position:relative;display:inline-block;width:46px;height:26px;flex-shrink:0;cursor:pointer;}
.whp-htoggle input{opacity:0;width:0;height:0;position:absolute;}
.whp-htoggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#cbd5e1;border-radius:26px;border:1.5px solid #b0b8c4;transition:.3s;}
.whp-htoggle-slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s;box-shadow:0 1px 4px rgba(0,0,0,.15);}
.whp-htoggle input:checked + .whp-htoggle-slider{background:#22c55e;border-color:#16a34a;}
.whp-htoggle input:checked + .whp-htoggle-slider:before{transform:translateX(20px);}
.whp-htoggle-lbl{font-size:13px;font-weight:700;color:#94a3b8;letter-spacing:.01em;}
.whp-htoggle-lbl-active{color:#16a34a;}

/* ── Main grid ─── */
.wpaap-aip-grid{display:grid;grid-template-columns:1fr 268px;gap:20px;align-items:start;}
.wpaap-aip-grid.no-sidebar{grid-template-columns:1fr;}

/* Neutralise verify.php's own wrapper inside the content col */
.wpaap-aip-content .aipv-wrap{max-width:none;margin:0;padding:0;}

/* ── Sidebar ─── */
.wpaap-aip-sidebar{display:flex;flex-direction:column;gap:14px;}
.wpaap-aip-scard{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04);}
.wpaap-aip-scard-hd{
    display:flex;align-items:center;justify-content:space-between;
    padding:11px 15px 10px;border-bottom:1px solid #f1f5f9;background:#fafafa;
}
.wpaap-aip-scard-title{font-size:11px;font-weight:700;color:#0f172a;text-transform:uppercase;letter-spacing:.06em;}
.wpaap-aip-scard-link{font-size:11px;color:#e11d48;text-decoration:none;font-weight:600;}
.wpaap-aip-scard-link:hover{text-decoration:underline;}
.wpaap-aip-scard-body{padding:12px 15px;}

.wpaap-aip-feat-row{display:flex;align-items:center;gap:9px;padding:7px 0;border-bottom:1px solid #f8fafc;font-size:12.5px;}
.wpaap-aip-feat-row:last-child{border-bottom:none;padding-bottom:0;}
.wpaap-aip-feat-name{flex:1;color:#334155;font-weight:500;}
.wpaap-aip-feat-tag{font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:20px;}
.wpaap-aip-feat-tag.on{background:#dcfce7;color:#15803d;}
.wpaap-aip-feat-tag.off{background:#f1f5f9;color:#94a3b8;}

.wpaap-aip-prov-row{display:flex;align-items:center;gap:9px;padding:8px 0;border-bottom:1px solid #f8fafc;}
.wpaap-aip-prov-row:last-child{border-bottom:none;padding-bottom:0;}
.wpaap-aip-prov-logo{width:26px;height:26px;border-radius:7px;display:flex;align-items:center;justify-content:center;background:#f8fafc;flex-shrink:0;}
.wpaap-aip-prov-name{flex:1;font-size:12px;font-weight:500;color:#334155;}
.wpaap-aip-prov-status{font-size:11px;font-weight:700;}
.wpaap-aip-prov-status.ok{color:#e11d48;} .wpaap-aip-prov-status.no{color:#94a3b8;}

.wpaap-aip-qrow{display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f8fafc;font-size:12.5px;}
.wpaap-aip-qrow:last-child{border-bottom:none;}
.wpaap-aip-qrow-lbl{color:#64748b;}
.wpaap-aip-qrow-val{font-weight:700;color:#0f172a;}
.wpaap-aip-qrow-val.risk{color:#ef4444;}
.wpaap-aip-qrow-val.ok{color:#e11d48;}

@media(max-width:960px){
    .wpaap-aip-stats{grid-template-columns:repeat(2,1fr);}
    .wpaap-aip-grid{grid-template-columns:1fr;}
}

/* ── Disabled notice ─── */
.wpaap-aip-disabled-notice {
    display:flex; align-items:center; gap:14px;
    background:#fffbeb; border:1px solid #fde68a;
    border-left:4px solid #f59e0b;
    border-radius:10px; padding:14px 18px; margin-bottom:16px;
}
.wpaap-aip-disabled-notice-icon {
    flex-shrink:0; width:36px; height:36px; border-radius:9px;
    background:#fef3c7; display:flex; align-items:center; justify-content:center;
}
.wpaap-aip-disabled-notice-body { flex:1; min-width:0; }
.wpaap-aip-disabled-notice-title { font-size:13.5px; font-weight:700; color:#92400e; margin:0 0 2px; }
.wpaap-aip-disabled-notice-desc  { font-size:12.5px; color:#a16207; margin:0; line-height:1.55; }
.wpaap-aip-disabled-notice-btn {
    display:inline-flex; align-items:center; gap:6px;
    background:#f59e0b; color:#fff; border:none; cursor:pointer;
    padding:8px 16px; border-radius:8px; font-size:13px; font-weight:700;
    text-decoration:none; white-space:nowrap; flex-shrink:0; transition:background .15s;
    font-family:inherit;
}
.wpaap-aip-disabled-notice-btn:hover { background:#d97706; color:#fff; }
</style>

<div class="wpaap-aip-wrap">

<?php if (!$is_active): ?>
<div class="wpaap-aip-disabled-notice">
    <div class="wpaap-aip-disabled-notice-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </div>
    <div class="wpaap-aip-disabled-notice-body">
        <?php if ($aipay_blocked_by_thankyou): ?>
        <p class="wpaap-aip-disabled-notice-title"><?php esc_html_e('AI Thanh Toán tạm tắt — trang đơn hàng thành công chưa bật', 'whp'); ?></p>
        <p class="wpaap-aip-disabled-notice-desc"><?php esc_html_e('AI Thanh Toán phụ thuộc vào tính năng Trang đơn hàng thành công. Bật tính năng đó trước để mở khóa AI Thanh Toán.', 'whp'); ?></p>
        <?php elseif (!$is_connected): ?>
        <p class="wpaap-aip-disabled-notice-title"><?php esc_html_e('AI Thanh Toán chưa thể bật — cần kết nối AI trước', 'whp'); ?></p>
        <p class="wpaap-aip-disabled-notice-desc"><?php esc_html_e('Cấu hình API Key trong phần Kết nối AI để kích hoạt tính năng này.', 'whp'); ?></p>
        <?php else: ?>
        <p class="wpaap-aip-disabled-notice-title"><?php esc_html_e('AI Thanh Toán đang tắt — các tính năng xác minh không hoạt động', 'whp'); ?></p>
        <p class="wpaap-aip-disabled-notice-desc"><?php esc_html_e('Bật AI Thanh Toán để kích hoạt OCR biên lai, phát hiện gian lận và xác minh tự động cho đơn hàng.', 'whp'); ?></p>
        <?php endif; ?>
    </div>
    <?php if ($aipay_blocked_by_thankyou): ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-woocommerce-advance&subtab=thankyou')); ?>" class="wpaap-aip-disabled-notice-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        <?php esc_html_e('Sang Trang đơn hàng thành công', 'whp'); ?>
    </a>
    <?php elseif (!$is_connected): ?>
    <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=connection')); ?>" class="wpaap-aip-disabled-notice-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        <?php esc_html_e('Sang trang Kết nối AI', 'whp'); ?>
    </a>
    <?php else: ?>
    <button type="button" class="wpaap-aip-disabled-notice-btn" id="wpaap-notice-enable-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        <?php esc_html_e('Bật ngay', 'whp'); ?>
    </button>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ══ HEADER ══════════════════════════════════════════════════ -->
<div class="wpaap-aip-header">
    <div class="wpaap-aip-header-inner">
        <div class="wpaap-aip-header-left">
            <div class="wpaap-aip-header-title-row">
                <div class="wpaap-aip-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M15 14h2"/>
                    </svg>
                </div>
                <h2><?php esc_html_e('AI Thanh Toán', 'whp'); ?></h2>
            </div>
            <p><?php esc_html_e('Tự động xác minh chuyển khoản bằng AI OCR, phát hiện gian lận, cảnh báo rủi ro và xử lý thanh toán thông minh.', 'whp'); ?></p>
            <div class="wpaap-aip-header-actions">
                <div class="whp-htoggle-wrap"<?php if (!$is_connected): ?> title="<?php esc_attr_e('Cần kết nối AI trước khi bật tính năng này', 'whp'); ?>" style="opacity:0.5;cursor:not-allowed;"<?php endif; ?>>
                    <label class="whp-htoggle" title="<?php echo !$is_connected ? esc_attr__('Cần kết nối AI trước', 'whp') : ($is_active ? esc_attr__('Tắt AI Thanh Toán', 'whp') : esc_attr__('Bật AI Thanh Toán', 'whp')); ?>" style="<?php echo !$is_connected ? 'pointer-events:none;' : ''; ?>">
                        <input type="checkbox" id="wpaap-header-toggle" value="1"
                               <?php checked($is_active); ?><?php disabled(!$is_connected); ?>>
                        <span class="whp-htoggle-slider"></span>
                    </label>
                    <span class="whp-htoggle-lbl<?php echo $is_active ? ' whp-htoggle-lbl-active' : ''; ?>" id="wpaap-header-toggle-lbl"><?php echo $is_active ? esc_html__('Đang bật', 'whp') : esc_html__('Đang tắt', 'whp'); ?></span>
                </div>
            </div>
        </div>

        <!-- Decorative illustration -->
        <div class="wpaap-aip-header-deco" aria-hidden="true">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="aipHg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#ffe4e6" stop-opacity="0"/>
                        <stop offset="40%" stop-color="#fecdd3" stop-opacity="0.5"/>
                        <stop offset="100%" stop-color="#fda4af" stop-opacity="0.7"/>
                    </linearGradient>
                    <filter id="aipSh"><feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(225,29,72,0.12)"/></filter>
                </defs>
                <rect width="680" height="168" fill="url(#aipHg)"/>
                <!-- Card 1: payment card -->
                <g filter="url(#aipSh)">
                    <rect x="310" y="22" width="130" height="80" rx="12" fill="#fff"/>
                    <rect x="310" y="22" width="130" height="28" rx="12" fill="#fff1f2"/>
                    <rect x="310" y="38" width="130" height="12" fill="#ffe4e6"/>
                    <circle cx="434" cy="32" r="8" fill="#fecdd3"/>
                    <circle cx="420" cy="32" r="8" fill="#fda4af" fill-opacity="0.6"/>
                    <rect x="324" y="62" width="40" height="6" rx="3" fill="#fecdd3"/>
                    <rect x="324" y="74" width="60" height="6" rx="3" fill="#f1f5f9"/>
                    <rect x="376" y="62" width="50" height="18" rx="5" fill="#e11d48"/>
                    <text x="401" y="75" text-anchor="middle" font-size="8" font-weight="700" fill="#fff" font-family="sans-serif">PAY</text>
                </g>
                <!-- Card 2: AI verify -->
                <g filter="url(#aipSh)">
                    <rect x="460" y="30" width="110" height="108" rx="12" fill="#fff"/>
                    <rect x="474" y="44" width="82" height="30" rx="8" fill="#fff1f2"/>
                    <text x="515" y="64" text-anchor="middle" font-size="13" font-weight="800" fill="#e11d48" font-family="sans-serif">AI</text>
                    <rect x="474" y="82" width="38" height="22" rx="6" fill="#f0fdf4"/>
                    <text x="493" y="97" text-anchor="middle" font-size="9" font-weight="700" fill="#16a34a" font-family="sans-serif">✓ OK</text>
                    <rect x="518" y="82" width="38" height="22" rx="6" fill="#fff1f2"/>
                    <text x="537" y="97" text-anchor="middle" font-size="9" font-weight="700" fill="#e11d48" font-family="sans-serif">⚠ Risk</text>
                    <rect x="474" y="112" width="82" height="18" rx="5" fill="#fff1f2" stroke="#fecdd3" stroke-width="1"/>
                    <text x="515" y="124" text-anchor="middle" font-size="8" font-weight="600" fill="#881337" font-family="sans-serif">Xác minh tự động</text>
                </g>
                <!-- Badge: verified -->
                <g filter="url(#aipSh)">
                    <rect x="590" y="44" width="68" height="24" rx="12" fill="#dcfce7"/>
                    <text x="624" y="60" text-anchor="middle" font-size="9" font-weight="700" fill="#166534" font-family="sans-serif">● Đã xác minh</text>
                </g>
                <!-- Dots -->
                <circle cx="300" cy="30" r="5" fill="rgba(225,29,72,0.12)"/>
                <circle cx="460" cy="148" r="7" fill="rgba(225,29,72,0.1)"/>
                <circle cx="630" cy="140" r="4" fill="rgba(225,29,72,0.15)"/>
            </svg>
        </div>
    </div>
</div>

<!-- ══ CONTENT WRAP (dims when master toggle is OFF) ══════════ -->
<div id="wpaap-content-wrap" class="<?php echo !$_ai_enable ? 'aip-disabled' : ''; ?>">

<?php if ($is_connected): ?>
<!-- ══ TAB NAV ══════════════════════════════════════════════════ -->
<nav class="wpaap-aip-subnav">
    <?php foreach ($nav_tabs as $key => $tab):
        $is_act = ($active_tab === $key);
    ?>
    <a href="<?php echo esc_url($base_url . $key); ?>"
       class="wpaap-aip-nav-item<?php echo $is_act ? ' active' : ''; ?>"
       <?php echo $is_act ? 'aria-current="page"' : ''; ?>>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $tab['icon']; ?></svg>
        <?php echo esc_html($tab['label']); ?>
        <?php if (!empty($tab['badge']) && $tab['badge'] > 0):
            $bc = !empty($tab['badge_red']) ? 'red' : (!empty($tab['badge_green']) ? 'green' : '');
        ?>
        <span class="wpaap-aip-nav-badge <?php echo $bc; ?>"><?php echo intval($tab['badge']); ?></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</nav>

<!-- ══ STATS ════════════════════════════════════════════════════ -->
<div class="wpaap-aip-stats">
    <?php
    $stat_cards = [
        [
            'icon'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
            'c'=>'rose','s'=>'#e11d48',
            'v'      => $s_pending,
            'lbl'    => __('Đơn chờ xác minh', 'whp'),
            'trend'  => $stat_trend($s_total_count, $y_total_count),
            'compare'=> __('hôm qua', 'whp'),
        ],
        [
            'icon'   => '<path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
            'c'=>'blue','s'=>'#2563eb',
            'v'      => $s_verify_rate . '%',
            'lbl'    => __('Tỷ lệ xác minh tự động', 'whp'),
            'trend'  => $stat_trend($s_verify_rate, $y_verify_rate),
            'compare'=> __('hôm qua', 'whp'),
        ],
        [
            'icon'   => '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            'c'      => ($s_risk > 0 ? 'red' : 'amber'),
            's'      => ($s_risk > 0 ? '#ef4444' : '#f59e0b'),
            'v'      => $s_risk,
            'lbl'    => __('Giao dịch gian lận', 'whp'),
            'trend'  => $stat_trend($s_risk, $y_risk),
            'compare'=> __('hôm qua', 'whp'),
            'invert' => true,
        ],
        [
            'icon'   => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><path d="M15 14h2"/>',
            'c'=>'rose','s'=>'#e11d48',
            'v'      => number_format($s_total, 0, ',', '.') . ' VND',
            'lbl'    => __('Tổng tiền hôm nay', 'whp'),
            'sm'     => true,
            'trend'  => $stat_trend($s_total, $y_total),
            'compare'=> __('hôm qua', 'whp'),
        ],
    ];
    foreach ($stat_cards as $sc):
        $tr      = !empty($sc['trend']) ? $sc['trend'] : null;
        $t_up    = $tr && $tr['dir'] === 'up';
        $invert  = !empty($sc['invert']);
        $t_good  = $tr ? ($invert ? !$t_up : $t_up) : true;
        $t_cls   = $t_good ? 'trend-good' : 'trend-bad';
        $t_arrow = $t_up ? '↑' : '↓';
    ?>
    <div class="wpaap-aip-stat">
        <div class="wpaap-aip-stat-icon <?php echo $sc['c']; ?>">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="<?php echo $sc['s']; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $sc['icon']; ?></svg>
        </div>
        <div class="wpaap-aip-stat-body">
            <div class="wpaap-aip-stat-lbl"><?php echo esc_html($sc['lbl']); ?></div>
            <div class="wpaap-aip-stat-val<?php echo !empty($sc['sm']) ? ' sm' : ''; ?>">
                <?php echo is_numeric($sc['v']) ? number_format($sc['v']) : esc_html($sc['v']); ?>
            </div>
            <?php if ($tr): ?>
            <div class="wpaap-aip-stat-trend <?php echo $t_cls; ?>">
                <?php echo $t_arrow; ?> <?php echo $tr['pct']; ?>% <?php esc_html_e('so với', 'whp'); ?> <?php echo esc_html($sc['compare']); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($active_tab === 'verify'):
    include $sub_base . 'verify.php';
elseif ($active_tab === 'config'):
    include $sub_base . 'config.php';
elseif ($active_tab === 'notifications'):
    include $sub_base . 'notifications.php';
elseif ($active_tab === 'logs'):
    include $sub_base . 'logs.php';
endif; ?>
<?php endif; ?>

<!-- ══ MAIN GRID: content + sidebar ════════════════════════════ -->
<div class="wpaap-aip-grid<?php echo in_array($active_tab, ['config', 'notifications', 'verify', 'queue', 'risk', 'logs', 'verified']) ? ' no-sidebar' : ''; ?>">

    <!-- Content column -->
    <div class="wpaap-aip-content">
        <?php if (!$is_connected): ?>
        <div style="background:#fff;border:1px solid #fecaca;border-top:4px solid #d63638;border-radius:14px;padding:32px 24px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.04);">
            <span class="dashicons dashicons-warning" style="font-size:48px;width:48px;height:48px;color:#d63638;margin-bottom:12px;display:block;margin-left:auto;margin-right:auto;"></span>
            <h2 style="margin:0 0 10px 0;color:#d63638;font-size:18px;"><?php esc_html_e('Chưa kết nối AI', 'whp'); ?></h2>
            <p style="font-size:13.5px;color:#646970;max-width:480px;margin:0 auto 20px auto;line-height:1.6;">
                <?php esc_html_e('Bạn cần cấu hình mã khóa API trong phần Kết nối AI trước khi sử dụng AI Thanh Toán.', 'whp'); ?>
            </p>
            <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=connection')); ?>" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;background:linear-gradient(135deg,#e11d48,#be123c);color:#fff;font-size:13px;font-weight:700;">
                <span class="dashicons dashicons-admin-plugins" style="font-size:16px;width:16px;height:16px;margin-top:1px;"></span> <?php esc_html_e('Sang trang Kết nối AI', 'whp'); ?>
            </a>
        </div>
        <?php else: switch ($active_tab) {
            case 'verify':
                wpaap_aipay_verify_layout();
                break;
            case 'queue':
                if (file_exists($sub_base . 'queue.php')) { include $sub_base . 'queue.php'; wpaap_aipay_queue_layout(); }
                break;
            case 'verified':
                if (file_exists($sub_base . 'verified.php')) { include $sub_base . 'verified.php'; wpaap_aipay_verified_layout(); }
                break;
            case 'risk':
                if (file_exists($sub_base . 'risk.php')) { include $sub_base . 'risk.php'; wpaap_aipay_risk_layout(); }
                break;
            case 'logs':
                wpaap_aipay_logs_layout();
                break;
            case 'notifications':
                wpaap_aipay_notifications_layout();
                break;
            case 'config':
            default:
                wpaap_aipay_config_layout();
                break;
        } endif; ?>
    </div>

    <!-- ── Sidebar (ẩn trên config và notifications vì chúng có sidebar riêng) ── -->
    <?php if (!in_array($active_tab, ['config', 'notifications', 'verify', 'queue', 'risk', 'logs', 'verified'])): ?>
    <div class="wpaap-aip-sidebar">

        <!-- AI Status -->
        <div class="wpaap-aip-scard">
            <div class="wpaap-aip-scard-hd">
                <span class="wpaap-aip-scard-title"><?php esc_html_e('Trạng thái AI Payment', 'whp'); ?></span>
                <a href="<?php echo esc_url($base_url . 'config'); ?>" class="wpaap-aip-scard-link"><?php esc_html_e('Cài đặt tính năng', 'whp'); ?></a>
            </div>
            <div class="wpaap-aip-scard-body">
                <?php
                $features = [
                    __('AI OCR Biên Lai', 'whp')    => ($ocr_enable === '1'),
                    'AI Fraud Detection' => ($fraud_enable === '1'),
                    'AI Copilot'        => ($copilot_enable === '1'),
                ];
                foreach ($features as $fn => $fon):
                ?>
                <div class="wpaap-aip-feat-row">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="<?php echo $fon ? '#059669' : '#94a3b8'; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <?php echo $fon ? '<circle cx="12" cy="12" r="10"/><polyline points="8 12 11 15 16 9"/>' : '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'; ?>
                    </svg>
                    <span class="wpaap-aip-feat-name"><?php echo esc_html($fn); ?></span>
                    <span class="wpaap-aip-feat-tag <?php echo $fon ? 'on' : 'off'; ?>"><?php echo $fon ? esc_html__('Đang hoạt động', 'whp') : esc_html__('Đang tắt', 'whp'); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Providers -->
        <div class="wpaap-aip-scard">
            <div class="wpaap-aip-scard-hd">
                <span class="wpaap-aip-scard-title"><?php esc_html_e('Nhà cung cấp AI', 'whp'); ?></span>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-connector')); ?>" class="wpaap-aip-scard-link"><?php esc_html_e('Quản lý API Keys', 'whp'); ?></a>
            </div>
            <div class="wpaap-aip-scard-body">
                <?php foreach ($prov_list as $pid => $prov): ?>
                <div class="wpaap-aip-prov-row">
                    <div class="wpaap-aip-prov-logo">
                        <?php if ($pid === 'google'): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21.8 12.2c0-.7-.1-1.3-.2-1.9H12v3.7h5.5c-.2 1.2-1 2.3-2.1 3v2.5h3.4c2-1.8 3-4.5 3-7.3z" fill="#4285F4"/><path d="M12 22c2.7 0 5-.9 6.7-2.4l-3.4-2.5c-.9.6-2.1 1-3.3 1-2.5 0-4.7-1.7-5.5-4H3v2.6C4.8 19.9 8.1 22 12 22z" fill="#34A853"/><path d="M6.5 14.1c-.2-.6-.3-1.3-.3-2s.1-1.4.3-2V7.5H3c-.7 1.4-1 2.9-1 4.5s.3 3.1 1 4.5l3.5-2.4z" fill="#FBBC05"/><path d="M12 5.8c1.4 0 2.7.5 3.7 1.4l2.7-2.7C16.9 2.9 14.6 2 12 2 8.1 2 4.8 4.1 3 7.5l3.5 2.6C7.3 7.5 9.5 5.8 12 5.8z" fill="#EA4335"/></svg>
                        <?php elseif ($pid === 'anthropic'): ?>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#c97c4a"><path d="M13.827 3.52L20.8 20H17.2l-1.4-3.56H8.2L6.8 20H3.2L10.173 3.52h3.654zM12 7.58L9.4 14H14.6L12 7.58z"/></svg>
                        <?php else: ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="#10a37f"><path d="M22.282 9.821a5.985 5.985 0 00-.516-4.91 6.046 6.046 0 00-6.51-2.9A6.065 6.065 0 0010.005 0a5.987 5.987 0 00-5.705 4.14 6.032 6.032 0 00-4.035 2.92 6.065 6.065 0 00.747 7.097 5.996 5.996 0 00.517 4.91 6.046 6.046 0 006.511 2.9A5.992 5.992 0 0013.5 24a5.987 5.987 0 005.703-4.14 6.03 6.03 0 004.038-2.92 6.061 6.061 0 00-.959-7.12zM13.5 22.485a4.477 4.477 0 01-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 00.392-.681v-6.737l2.02 1.168a.071.071 0 01.038.052v5.583a4.504 4.504 0 01-4.494 4.494z"/></svg>
                        <?php endif; ?>
                    </div>
                    <span class="wpaap-aip-prov-name"><?php echo esc_html($prov['label']); ?></span>
                    <span class="wpaap-aip-prov-status <?php echo $prov['connected'] ? 'ok' : 'no'; ?>">
                        <?php echo $prov['connected'] ? '✓ ' . esc_html__('Đã kết nối', 'whp') : esc_html__('Chưa kết nối', 'whp'); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick stats -->
        <div class="wpaap-aip-scard">
            <div class="wpaap-aip-scard-hd">
                <span class="wpaap-aip-scard-title"><?php esc_html_e('Thống kê nhanh', 'whp'); ?></span>
                <span style="font-size:10.5px;color:#94a3b8;"><?php esc_html_e('Hôm nay', 'whp'); ?></span>
            </div>
            <div class="wpaap-aip-scard-body">
                <?php
                $qrows = [
                    [__('Tổng đơn hàng', 'whp'),     $s_total_count,                    ''],
                    [__('Đã xác minh', 'whp'),       $s_verified,                       'ok'],
                    [__('Đang chờ', 'whp'),          $s_pending,                        ''],
                    [__('Rủi ro / Gian lận', 'whp'), $s_risk,                           $s_risk > 0 ? 'risk' : ''],
                ];
                foreach ($qrows as [$lbl,$val,$cls]): ?>
                <div class="wpaap-aip-qrow">
                    <span class="wpaap-aip-qrow-lbl"><?php echo esc_html($lbl); ?></span>
                    <span class="wpaap-aip-qrow-val <?php echo $cls; ?>"><?php echo number_format($val); ?></span>
                </div>
                <?php endforeach; ?>
                <!-- Mini trend chart -->
                <div style="margin-top:10px;">
                    <svg viewBox="0 0 240 45" style="width:100%;height:45px;" fill="none">
                        <polyline points="0,38 30,30 60,33 90,18 120,26 150,14 180,20 210,10 240,16"
                            stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="0,38 30,30 60,33 90,18 120,26 150,14 180,20 210,10 240,16 240,45 0,45"
                            fill="rgba(16,185,129,.08)"/>
                    </svg>
                </div>
            </div>
        </div>

    </div><!-- /sidebar -->
    <?php endif; ?>

</div><!-- /main-grid -->

</div><!-- #wpaap-content-wrap -->

<?php if ($has_form): ?>
<style>
.wpaap-aip-savebar {
    margin: 20px 0 0;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
}
.wpaap-aip-savebar-note {
    display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0;
}
.wpaap-aip-savebar-note-icon {
    width: 28px; height: 28px; border-radius: 8px; background: #eff6ff;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.wpaap-aip-savebar-note-text { font-size: 13px; color: #475569; }
.wpaap-aip-savebar-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 9px 22px; border-radius: 9px;
    background: #2563eb;
    color: #fff; font-size: 13px; font-weight: 700;
    border: none; cursor: pointer; white-space: nowrap;
    transition: background 0.15s; font-family: inherit;
}
.wpaap-aip-savebar-btn:hover { background: #1d4ed8; }
.wpaap-aip-savebar-btn:active { background: #1e40af; }
/* Disabled state when master toggle is OFF */
#wpaap-content-wrap.aip-disabled { opacity:0.4; pointer-events:none; user-select:none; transition:opacity 0.3s; }
</style>
<div class="wpaap-aip-savebar" id="wpaap-save-bar">
    <div class="wpaap-aip-savebar-note">
        <div class="wpaap-aip-savebar-note-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="wpaap-aip-savebar-note-text"><?php esc_html_e('Các thay đổi sẽ áp dụng ngay sau khi lưu', 'whp'); ?></div>
    </div>
    <button type="submit"
            name="<?php echo $active_tab === 'config' ? 'submit' : 'whp_aipay_notif_save'; ?>"
            form="<?php echo $active_tab === 'config' ? 'wpaap-config-form' : 'wpaap-notif-form'; ?>"
            class="wpaap-aip-savebar-btn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <?php esc_html_e('Lưu thông tin', 'whp'); ?>
    </button>
</div>
<?php endif; ?>

</div><!-- /wrap -->

<script>
window.whpI18n = Object.assign(window.whpI18n || {}, <?php echo wp_json_encode([
    'disabling'       => __( 'Đang tắt', 'whp' ),
    'enabling'        => __( 'Đang bật', 'whp' ),
    'saved'           => __( 'Đã lưu', 'whp' ),
    'saveFailed'      => __( 'Lưu thất bại', 'whp' ),
    'connectionError' => __( 'Lỗi kết nối, vui lòng thử lại', 'whp' ),
]); ?>);
(function(){
    // "Bật ngay" button in disabled notice → trigger header toggle
    var noticeBtn = document.getElementById('wpaap-notice-enable-btn');
    if (noticeBtn) {
        noticeBtn.addEventListener('click', function() {
            var ht = document.getElementById('wpaap-header-toggle');
            if (ht && !ht.checked) { ht.checked = true; ht.dispatchEvent(new Event('change', {bubbles:true})); }
            var notice = noticeBtn.closest('.wpaap-aip-disabled-notice');
            if (notice) { notice.style.display = 'none'; }
        });
    }

    // Header toggle: update label + sync hidden input (Kiểu 1 — no auto-submit)
    var headerToggle  = document.getElementById('wpaap-header-toggle');
    var headerLbl     = document.getElementById('wpaap-header-toggle-lbl');
    var aipContentWrap = document.getElementById('wpaap-content-wrap');
    var aipSaveBar    = document.getElementById('wpaap-save-bar');
    var aiConnected       = <?php echo json_encode($is_connected); ?>;
    var aiConnUrl         = <?php echo wp_json_encode(admin_url('admin.php?page=mb-wphelper-ai&subtab=connection')); ?>;
    var thankyouActive    = <?php echo json_encode($thankyou_active); ?>;
    var thankyouUrl       = <?php echo wp_json_encode(admin_url('admin.php?page=mb-wphelper-woocommerce-advance&subtab=thankyou')); ?>;
    var _aipayToggleNonce = '<?php echo wp_create_nonce('wpaap_generate_nonce'); ?>';
    function updateAipayToggleUI(isOn) {
        if (aipContentWrap) { aipContentWrap.classList.toggle('aip-disabled', !isOn); }
        if (aipSaveBar)     { aipSaveBar.style.display = isOn ? '' : 'none'; }
    }
    // Set initial state on page load
    if (headerToggle) { updateAipayToggleUI(headerToggle.checked); }
    if (headerToggle) {
        headerToggle.addEventListener('change', function(e) {
            e.stopPropagation();
            // Block enabling when AI is not connected
            if (this.checked && !aiConnected) {
                this.checked = false;
                if (headerLbl) { headerLbl.textContent = whpI18n.disabling; headerLbl.classList.remove('whp-htoggle-lbl-active'); }
                updateAipayToggleUI(false);
                setTimeout(function(){
                    if (typeof window.wpaapConfirm === 'function') {
                        window.wpaapConfirm(
                            <?php echo wp_json_encode(__('Cần kết nối AI trước khi bật AI Thanh Toán. Đến trang kết nối để cấu hình API Key.', 'whp')); ?>,
                            function(){ window.location.href = aiConnUrl; },
                            { title: <?php echo wp_json_encode(__('Chưa kết nối AI', 'whp')); ?>, okLabel: <?php echo wp_json_encode(__('Đến AI Connection', 'whp')); ?> }
                        );
                    }
                }, 0);
                return;
            }
            // Block enabling when Thank You page is off
            if (this.checked && !thankyouActive) {
                this.checked = false;
                if (headerLbl) { headerLbl.textContent = whpI18n.disabling; headerLbl.classList.remove('whp-htoggle-lbl-active'); }
                var si = document.getElementById('wpaap-enable-sync');
                if (si) { si.value = '0'; si.dispatchEvent(new Event('change', {bubbles:false})); }
                updateAipayToggleUI(false);
                // Use confirm dialog — wpaapConfirm is defined later in the page
                setTimeout(function(){
                    if (typeof window.wpaapConfirm === 'function') {
                        window.wpaapConfirm(
                            'AI Thanh Toán phụ thuộc vào tính năng Trang đơn hàng thành công. Bật tính năng đó trước rồi quay lại đây.',
                            function(){ window.location.href = thankyouUrl; },
                            { title: 'Chưa đáp ứng điều kiện', okLabel: 'Sang cài đặt' }
                        );
                    }
                }, 0);
                return;
            }
            if (headerLbl) {
                headerLbl.textContent = this.checked ? whpI18n.enabling : whpI18n.disabling;
                headerLbl.classList.toggle('whp-htoggle-lbl-active', this.checked);
            }
            updateAipayToggleUI(this.checked);
            var syncInput = document.getElementById('wpaap-enable-sync');
            if (syncInput) {
                syncInput.value = this.checked ? '1' : '0';
                // Prevent the value change from bubbling as an input/change event on the form
                syncInput.dispatchEvent(new Event('change', {bubbles: false}));
            }
            // Auto-save enable state via AJAX — no form submit needed
            var _enableVal = this.checked ? '1' : '0';
            var _fd = new FormData();
            _fd.append('action', 'wpaap_aipay_toggle_enable');
            _fd.append('nonce', _aipayToggleNonce);
            _fd.append('value', _enableVal);
            fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: _fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (typeof wpaapToast === 'function') {
                        var msg = (res.data && res.data.message) ? res.data.message : (res.success ? whpI18n.saved : whpI18n.saveFailed);
                        wpaapToast(msg, res.success ? 'success' : 'error');
                    }
                })
                .catch(function() {
                    if (typeof wpaapToast === 'function') {
                        wpaapToast(whpI18n.connectionError, 'error');
                    }
                });
        });
    }

    // Guard: config form only submits when savebar button clicked
    var configForm = document.getElementById('wpaap-config-form');
    if (configForm) {
        configForm.addEventListener('submit', function(e) {
            var fromSavebar = e.submitter && e.submitter.classList.contains('wpaap-aip-savebar-btn');
            if (!fromSavebar) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });
    }
})();
</script>

<!-- ═══ WPAAP NOTIFICATION SYSTEM (shared across all tabs) ═══ -->
<style>
#wpaap-toast-wrap{position:fixed;top:52px;left:50%;transform:translateX(-50%);z-index:99999999;display:flex;flex-direction:column;align-items:center;gap:8px;pointer-events:none;}
.wpaap-toast{display:flex;align-items:center;gap:10px;padding:12px 20px 12px 16px;border-radius:12px;font-size:13.5px;font-weight:600;color:#fff;box-shadow:0 8px 28px rgba(0,0,0,.18);pointer-events:all;min-width:260px;max-width:440px;animation:wt-in .28s cubic-bezier(.34,1.56,.64,1);transition:opacity .25s,transform .25s;}
.wpaap-toast.wt-out{opacity:0;transform:translateY(-14px) scale(.96);}
.wpaap-toast.wt-success{background:linear-gradient(135deg,#059669,#047857);}
.wpaap-toast.wt-error{background:linear-gradient(135deg,#dc2626,#b91c1c);}
.wpaap-toast.wt-warning{background:linear-gradient(135deg,#d97706,#b45309);}
.wpaap-toast.wt-info{background:linear-gradient(135deg,#1e293b,#0f172a);}
.wpaap-toast-icon{width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
.wpaap-toast-msg{flex:1;line-height:1.4;}
.wpaap-toast-close{background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;font-size:16px;padding:0;line-height:1;flex-shrink:0;}
.wpaap-toast-close:hover{color:#fff;}
@keyframes wt-in{from{opacity:0;transform:translateY(-18px) scale(.94);}to{opacity:1;transform:translateY(0) scale(1);}}

#wpaap-confirm-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:9999998;align-items:center;justify-content:center;}
#wpaap-confirm-overlay.wc-open{display:flex;}
.wpaap-confirm-box{background:#fff;border-radius:18px;padding:30px 28px 24px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:center;animation:wt-in .22s ease;}
.wpaap-confirm-icon{width:52px;height:52px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:24px;}
.wpaap-confirm-icon.warn{background:#fff7ed;}
.wpaap-confirm-icon.danger{background:#fff1f2;}
.wpaap-confirm-title{font-size:16px;font-weight:700;color:#0f172a;margin-bottom:8px;}
.wpaap-confirm-msg{font-size:13px;color:#64748b;line-height:1.6;margin-bottom:24px;}
.wpaap-confirm-btns{display:flex;gap:10px;}
.wpaap-confirm-cancel{flex:1;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;color:#475569;font-size:13px;font-weight:600;cursor:pointer;transition:background .12s;}
.wpaap-confirm-cancel:hover{background:#f8fafc;}
.wpaap-confirm-ok{flex:1;padding:10px 16px;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;transition:opacity .12s;}
.wpaap-confirm-ok.warn{background:#e11d48;}
.wpaap-confirm-ok.danger{background:#dc2626;}
.wpaap-confirm-ok:hover{opacity:.88;}
</style>
<div id="wpaap-toast-wrap"></div>
<div id="wpaap-confirm-overlay">
  <div class="wpaap-confirm-box">
    <div class="wpaap-confirm-icon warn" id="wpaap-confirm-icon">⚠️</div>
    <div class="wpaap-confirm-title" id="wpaap-confirm-title"><?php esc_html_e('Xác nhận', 'whp'); ?></div>
    <div class="wpaap-confirm-msg" id="wpaap-confirm-msg"></div>
    <div class="wpaap-confirm-btns">
      <button class="wpaap-confirm-cancel" id="wpaap-confirm-cancel"><?php esc_html_e('Huỷ', 'whp'); ?></button>
      <button class="wpaap-confirm-ok warn" id="wpaap-confirm-ok"><?php esc_html_e('Xác nhận', 'whp'); ?></button>
    </div>
  </div>
</div>
<script>
(function(){
function wpaapToast(msg, type) {
    var wrap = document.getElementById('wpaap-toast-wrap');
    if (!wrap) return;
    type = type || 'info';
    var icons = {success:'✓', error:'✗', warning:'⚠', info:'ℹ'};
    var t = document.createElement('div');
    t.className = 'wpaap-toast wt-' + type;
    t.innerHTML = '<div class="wpaap-toast-icon">' + (icons[type]||'ℹ') + '</div>'
                + '<span class="wpaap-toast-msg">' + msg + '</span>'
                + '<button class="wpaap-toast-close" onclick="this.closest(\'.wpaap-toast\').remove()">×</button>';
    wrap.appendChild(t);
    setTimeout(function(){ t.classList.add('wt-out'); setTimeout(function(){ t.remove(); }, 280); }, 3800);
}
var _wcCb = null;
function wpaapConfirm(msg, onOk, opts) {
    opts = opts || {};
    var isDanger = opts.danger;
    var icon = document.getElementById('wpaap-confirm-icon');
    icon.textContent = isDanger ? '🗑️' : '⚠️';
    icon.className = 'wpaap-confirm-icon ' + (isDanger ? 'danger' : 'warn');
    document.getElementById('wpaap-confirm-title').textContent = opts.title || 'Xác nhận';
    document.getElementById('wpaap-confirm-msg').textContent  = msg;
    var okBtn = document.getElementById('wpaap-confirm-ok');
    okBtn.textContent  = opts.okLabel || 'Xác nhận';
    okBtn.className    = 'wpaap-confirm-ok ' + (isDanger ? 'danger' : 'warn');
    document.getElementById('wpaap-confirm-overlay').classList.add('wc-open');
    _wcCb = onOk;
}
document.addEventListener('click', function(e){
    if (e.target.id === 'wpaap-confirm-ok') {
        document.getElementById('wpaap-confirm-overlay').classList.remove('wc-open');
        if (_wcCb) { var cb = _wcCb; _wcCb = null; cb(); }
    }
    if (e.target.id === 'wpaap-confirm-cancel' || e.target.id === 'wpaap-confirm-overlay') {
        document.getElementById('wpaap-confirm-overlay').classList.remove('wc-open');
        _wcCb = null;
    }
});
window.wpaapToast   = wpaapToast;
window.wpaapConfirm = wpaapConfirm;
})();
</script>
<?php
}
