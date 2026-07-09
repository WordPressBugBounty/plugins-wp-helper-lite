<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function wph_spam_filter_page_layout() {
    $nonce       = wp_create_nonce( 'wph_sf_nonce' );
    $settings    = get_option( 'wph_spam_filter_settings', array() );
    $log_settings = get_option( 'wph_spam_log_settings', array() );
    $stats       = wph_sf_get_stats();

    global $wpdb;
    $table         = $wpdb->prefix . 'wph_spam_logs';
    $total_blocked = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

    // Extract settings
    $active       = ! empty( $settings['active'] ) && $settings['active'] === '1';
    $hp_active    = ! empty( $settings['honeypot']['active'] ) && $settings['honeypot']['active'] === '1';
    $hp_field     = $settings['honeypot']['field'] ?? 'wph_hp_field';
    $rl_active    = ! empty( $settings['rate_limit']['active'] ) && $settings['rate_limit']['active'] === '1';
    $rl_max       = absint( $settings['rate_limit']['max'] ?? 3 );
    $rl_min       = absint( $settings['rate_limit']['minutes'] ?? 5 );
    $temp_active  = ! empty( $settings['email_block']['temp_active'] ) && $settings['email_block']['temp_active'] === '1';
    $proxy_active   = ! empty( $settings['proxy_vpn']['active'] ) && $settings['proxy_vpn']['active'] === '1';
    $dnsbl_level    = $settings['dnsbl_level'] ?? 'off';
    $hide_error          = ! empty( $settings['hide_error'] ) && $settings['hide_error'] === '1';
    $monitor_mode        = ! empty( $settings['monitor_mode'] ) && $settings['monitor_mode'] === '1';
    $code_detect_active  = ! empty( $settings['code_detect']['active'] ) && $settings['code_detect']['active'] === '1';
    $code_detect_level   = $settings['code_detect']['level'] ?? 'basic';

    // Quick-block lists
    $bl_ips       = array_values( array_filter( array_map( 'trim', explode( "\n", $settings['ip_block']['blacklist'] ?? '' ) ) ) );
    $bl_emails    = array_values( array_filter( array_map( 'trim', explode( "\n", $settings['email_block']['emails'] ?? '' ) ) ) );
    $bl_domains   = array_values( array_filter( array_map( 'trim', explode( "\n", $settings['email_block']['domains'] ?? '' ) ) ) );
    $bl_keywords  = array_values( array_filter( array_map( 'trim', explode( "\n", $settings['keyword_block']['list'] ?? '' ) ) ) );
    $bl_countries = array_values( array_filter( $settings['country_block']['countries'] ?? array() ) );

    // Initial log load
    $lpage     = max( 1, intval( $_GET['sfp'] ?? 1 ) );
    $lpp       = 25;
    $log_rows  = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $lpp, ( $lpage - 1 ) * $lpp
    ) );
    $log_total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    $log_pages = max( 1, ceil( $log_total / $lpp ) );

    // Trend data for stat cards
    $yesterday     = date( 'Y-m-d', strtotime( '-1 day' ) );
    $today_str     = current_time( 'Y-m-d' );
    $w7_start      = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
    $w14_start     = date( 'Y-m-d H:i:s', strtotime( '-14 days' ) );
    $spam_yest     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE DATE(created_at)=%s", $yesterday ) );
    $spam_prev7    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s AND created_at < %s", $w14_start, $w7_start ) );
    $ip_prev7      = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT ip_address) FROM {$table} WHERE created_at >= %s AND created_at < %s", $w14_start, $w7_start ) );
    $ip_cur7       = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT ip_address) FROM {$table} WHERE created_at >= %s", $w7_start ) );
    $em_prev7      = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT email) FROM {$table} WHERE email!='' AND created_at >= %s AND created_at < %s", $w14_start, $w7_start ) );
    $em_cur7       = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT email) FROM {$table} WHERE email!='' AND created_at >= %s", $w7_start ) );
    $total_prev7   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s AND created_at < %s", $w14_start, $w7_start ) );
    $total_cur7    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s", $w7_start ) );

    $calc_trend = function( $cur, $prev ) {
        if ( $prev <= 0 && $cur > 0 ) return array( 'pct' => null, 'dir' => 'new' );
        if ( $prev <= 0 ) return array( 'pct' => null, 'dir' => 'none' );
        $pct = round( abs( $cur - $prev ) / $prev * 100 );
        return array( 'pct' => $pct, 'dir' => $cur >= $prev ? 'up' : 'down' );
    };
    $trends = array(
        $calc_trend( $stats['today'], $spam_yest ),
        $calc_trend( $stats['week'],  $spam_prev7 ),
        $calc_trend( $ip_cur7,  $ip_prev7 ),
        $calc_trend( $em_cur7,  $em_prev7 ),
        $calc_trend( $total_cur7, $total_prev7 ),
    );

    // Donut chart
    $reason_stats  = wph_sf_get_reason_stats();
    $chart_colors  = array(
        'Honeypot'     => '#ef4444',
        'Rate Limit'   => '#f97316',
        'Bot / UA'     => '#eab308',
        'IP / Country' => '#3b82f6',
        'Email'        => '#8b5cf6',
        'Keyword'      => '#22c55e',
        'Khác'         => '#94a3b8',
    );
    $chart_total = array_sum( $reason_stats );
    $gradient_parts = array();
    $cumulative = 0;
    foreach ( $chart_colors as $cat => $color ) {
        $count = $reason_stats[ $cat ] ?? 0;
        if ( $count <= 0 || $chart_total <= 0 ) continue;
        $pct  = round( $count / $chart_total * 100, 2 );
        $end  = round( $cumulative + $pct, 2 );
        if ( $end > 100 ) $end = 100;
        $gradient_parts[] = "{$color} {$cumulative}% {$end}%";
        $cumulative = $end;
    }
    $donut_gradient = ! empty( $gradient_parts )
        ? 'conic-gradient(' . implode( ', ', $gradient_parts ) . ')'
        : 'conic-gradient(#e2e8f0 0% 100%)';

    $ajax_url = admin_url( 'admin-ajax.php' );
    ?>

<style>
#whp-toast-wrap{position:fixed;top:52px;left:50%;transform:translateX(-50%);z-index:99999999;display:flex;flex-direction:column;align-items:center;gap:8px;pointer-events:none;}
.whp-toast{display:flex;align-items:center;gap:10px;padding:12px 20px 12px 16px;border-radius:12px;font-size:13.5px;font-weight:600;color:#fff;box-shadow:0 8px 28px rgba(0,0,0,.18);pointer-events:all;min-width:260px;max-width:440px;animation:wt-in .28s cubic-bezier(.34,1.56,.64,1);transition:opacity .25s,transform .25s;}
.whp-toast.wt-out{opacity:0;transform:translateY(-14px) scale(.96);}
.whp-toast.wt-success{background:linear-gradient(135deg,#059669,#047857);}
.whp-toast.wt-error{background:linear-gradient(135deg,#dc2626,#b91c1c);}
.whp-toast-icon{width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
.whp-toast-msg{flex:1;line-height:1.4;}
.whp-toast-close{background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;font-size:16px;padding:0;line-height:1;flex-shrink:0;}
.whp-toast-close:hover{color:#fff;}
@keyframes wt-in{from{opacity:0;transform:translateY(-10px) scale(.95)}to{opacity:1;transform:none}}
/* Header — matches SMTP pattern with red accent */
.wph-sf2-header{position:relative;background:linear-gradient(100deg,#ffffff 0%,#fff5f5 45%,#ffe8e8 100%);border-radius:20px;box-shadow:0 4px 24px rgba(220,38,38,.1),0 0 0 1px #fecaca;margin-bottom:20px;overflow:hidden;min-height:168px;display:flex;align-items:stretch;}
.wph-sf2-header-left{position:relative;z-index:2;padding:32px 36px;display:flex;flex-direction:column;justify-content:center;gap:14px;max-width:480px;flex-shrink:0;}
.wph-sf2-header-title-row{display:flex;align-items:center;gap:14px;}
.wph-sf2-header-icon-box{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#dc2626,#f87171);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(220,38,38,.3);}
.wph-sf2-header-right{position:absolute;inset:0 0 0 38%;overflow:hidden;pointer-events:none;}
/* SMTP-style toggle reused for spam header */
.wph-sf2-master-switch{position:relative;display:inline-block;width:52px;height:28px;}
.wph-sf2-master-switch input{opacity:0;width:0;height:0;}
.wph-sf2-master-slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:28px;transition:.3s ease;}
.wph-sf2-master-slider:before{position:absolute;content:"";height:20px;width:20px;left:4px;bottom:4px;background:#fff;border-radius:50%;transition:.3s ease;box-shadow:0 1px 4px rgba(15,23,42,.15);}
.wph-sf2-master-switch input:checked+.wph-sf2-master-slider{background:#22c55e;}
.wph-sf2-master-switch input:checked+.wph-sf2-master-slider:before{transform:translateX(24px);}
/* Row toggles (settings list) */
.wph-sf2-toggle{position:relative;width:40px;height:22px;display:inline-block;vertical-align:middle;}
.wph-sf2-toggle input{opacity:0;width:0;height:0;}
.wph-sf2-toggle-slider{position:absolute;top:0;left:0;right:0;bottom:0;background:#e2e8f0;border-radius:22px;cursor:pointer;transition:.2s;}
.wph-sf2-toggle input:checked+.wph-sf2-toggle-slider{background:#22c55e;}
.wph-sf2-toggle-slider::before{position:absolute;content:'';height:16px;width:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.wph-sf2-toggle input:checked+.wph-sf2-toggle-slider::before{transform:translateX(18px);}
.wph-sf2-srow{display:flex;align-items:center;gap:16px;padding:16px 22px;}
.wph-sf2-srow+.wph-sf2-srow{border-top:1px solid #f1f5f9;}
.wph-sf2-srow-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.wph-sf2-srow-body{flex:1;min-width:0;}
.wph-sf2-srow-title{font-size:13.5px;font-weight:700;color:#1e293b;line-height:1.3;}
.wph-sf2-srow-desc{font-size:12px;color:#94a3b8;margin-top:3px;line-height:1.4;}
.wph-sf2-srow-ctrl{flex-shrink:0;display:flex;align-items:center;gap:10px;}
/* Rate Limit inputs */
.wph-sf2-rl-inputs{display:inline-flex;align-items:center;gap:0;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:8px;padding:3px 10px;transition:border-color .15s;}
.wph-sf2-rl-inputs:focus-within{border-color:#3b82f6;background:#fff;}
.wph-sf2-rl-input{width:40px!important;min-height:unset!important;border:none!important;outline:none!important;box-shadow:none!important;background:transparent!important;font-size:13px!important;font-weight:700!important;text-align:center!important;color:#1e293b!important;padding:3px 2px!important;}
.wph-sf2-rl-input::-webkit-inner-spin-button,.wph-sf2-rl-input::-webkit-outer-spin-button{-webkit-appearance:none!important;margin:0!important;}
.wph-sf2-rl-input[type=number]{-moz-appearance:textfield!important;}
.wph-sf2-rl-sep{font-size:11.5px;color:#94a3b8;font-weight:500;padding:0 5px;white-space:nowrap;}
/* Custom DNSBL select */
.wph-sf2-cs{position:relative;display:inline-block;min-width:148px;}
.wph-sf2-cs-trigger{display:flex;align-items:center;gap:8px;padding:7px 12px;border:1.5px solid #e2e8f0;border-radius:8px;background:#f8fafc;cursor:pointer;font-size:12.5px;font-weight:600;color:#334155;transition:border-color .15s,background .15s;user-select:none;}
.wph-sf2-cs-trigger:hover{border-color:#3b82f6;background:#fff;}
.wph-sf2-cs-trigger.open{border-color:#3b82f6;background:#fff;}
.wph-sf2-cs-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;display:inline-block;}
.wph-sf2-cs-chevron{margin-left:auto;color:#94a3b8;transition:transform .2s;}
.wph-sf2-cs-trigger.open .wph-sf2-cs-chevron{transform:rotate(180deg);}
.wph-sf2-cs-menu{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.1);z-index:999;overflow:hidden;display:none;}
.wph-sf2-cs-menu.open{display:block;}
.wph-sf2-cs-menu-up{top:auto;bottom:calc(100% + 4px);}
.wph-sf2-cs-opt{display:flex;align-items:center;gap:9px;padding:9px 14px;font-size:12.5px;font-weight:600;color:#334155;cursor:pointer;transition:background .12s;}
.wph-sf2-cs-opt:hover{background:#f1f5f9;}
.wph-sf2-cs-opt.selected{background:#eff6ff;color:#2563eb;}
.wph-sf2-qb-tab{flex:1;padding:10px 8px;border:none;background:transparent;font-size:13px;font-weight:600;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;border-radius:0;transition:color .15s,border-color .15s;text-align:center;}
.wph-sf2-qb-tab:hover{color:#2563eb;}
.wph-sf2-qb-tab.active{color:#2563eb;border-bottom-color:#2563eb;}
.wph-sf2-qb-item{display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f8fafc;}
.wph-sf2-qb-item:last-child{border-bottom:none;}
.wph-sf2-qb-del{background:none;border:none;cursor:pointer;color:#cbd5e1;padding:4px;line-height:1;flex-shrink:0;transition:color .15s;display:flex;align-items:center;}
.wph-sf2-qb-del:hover{color:#ef4444;}
.wph-sf2-logtd{padding:10px 14px;vertical-align:middle;}
.wph-sf2-logth{padding:9px 14px;text-align:left;font-weight:700;font-size:11px;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap;}
/* Stat cards — horizontal: icon left, content right */
.wph-sf2-stat-card{background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px 20px 14px;box-shadow:0 1px 4px rgba(0,0,0,.05);display:flex;flex-direction:row;align-items:flex-start;gap:14px;transition:box-shadow .15s;}
.wph-sf2-stat-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.09);}
.wph-sf2-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;}
.wph-sf2-stat-body{flex:1;min-width:0;}
.wph-sf2-stat-num{font-size:30px;font-weight:800;line-height:1;letter-spacing:-.5px;}
.wph-sf2-stat-lbl{font-size:12.5px;color:#64748b;margin-top:4px;font-weight:500;}
.wph-sf2-stat-trend{display:flex;align-items:center;gap:4px;margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9;font-size:11.5px;font-weight:600;}
.wph-sf2-trend-up{color:#16a34a;}
.wph-sf2-trend-down{color:#dc2626;}
.wph-sf2-trend-none{color:#94a3b8;}
.wph-sf2-trend-cmp{font-weight:400;color:#94a3b8;margin-left:2px;}
@media(max-width:900px){.wph-sf2-body{grid-template-columns:1fr!important;}.wph-sf2-stats{grid-template-columns:repeat(3,1fr)!important;}.wph-sf2-header-right{display:none;}}
@media(max-width:600px){.wph-sf2-stats{grid-template-columns:repeat(2,1fr)!important;}}
.wph-sf2-wrap{max-width:1200px;margin:0 auto;padding:0 0 40px;font-family:inherit;}
/* Flat custom select — embedded in filter bar (no border/bg) */
.wph-sf2-cs-flat{position:relative;display:inline-block;}
.wph-sf2-cs-flat-trigger{display:flex;align-items:center;gap:7px;padding:9px 12px;border:none;background:transparent;cursor:pointer;font-size:12.5px;font-weight:600;color:#334155;transition:background .12s;user-select:none;white-space:nowrap;}
.wph-sf2-cs-flat-trigger:hover{background:#f8fafc;}
.wph-sf2-cs-flat-trigger.open{background:#f1f5f9;}
.wph-sf2-cs-flat-trigger.open .wph-sf2-cs-chevron{transform:rotate(180deg);}
@keyframes wphSlideIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
#sf2-content-wrap.sf2-disabled{opacity:0.4;pointer-events:none;user-select:none;transition:opacity 0.3s;}
.wph-sf2-save-bar{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:20px;box-shadow:0 4px 20px -2px rgba(15,23,42,.03);}
.wph-sf2-save-bar-hint{font-size:12.5px;color:#64748b;display:flex;align-items:center;gap:6px;}
.wph-sf2-save-btn{background:linear-gradient(135deg,#3858e9,#2563eb);color:#fff;border:none;border-radius:9px;padding:10px 22px;font-size:13.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;box-shadow:0 2px 8px rgba(37,99,235,.25);transition:opacity .15s;flex-shrink:0;}
.wph-sf2-save-btn:hover{opacity:.88;}
</style>

<div id="whp-toast-wrap"></div>
<div class="wph-sf2-wrap">
<div id="wph-sf2-notice" style="display:none;"></div>
<?php if ( ! $active ) : ?>
<div id="sf2-master-warning" style="display:flex;align-items:center;gap:10px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#9a3412;font-weight:600;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <?php esc_html_e( 'Chống Spam đang', 'whp' ); ?> <strong style="color:#dc2626;"><?php esc_html_e( 'TẮT', 'whp' ); ?></strong> — <?php esc_html_e( 'Toàn bộ tính năng bảo vệ form bị vô hiệu hóa. Bật công tắc bên trên để kích hoạt.', 'whp' ); ?>
</div>
<?php else : ?>
<div id="sf2-master-warning" style="display:none;align-items:center;gap:10px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#9a3412;font-weight:600;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <?php esc_html_e( 'Chống Spam đang', 'whp' ); ?> <strong style="color:#dc2626;"><?php esc_html_e( 'TẮT', 'whp' ); ?></strong> — <?php esc_html_e( 'Toàn bộ tính năng bảo vệ form bị vô hiệu hóa. Bật công tắc bên trên để kích hoạt.', 'whp' ); ?>
</div>
<?php endif; ?>

<!-- ── HEADER ────────────────────────────────────────────────────────────── -->
<div class="wph-sf2-header">
    <div class="wph-sf2-header-left">
        <div class="wph-sf2-header-title-row">
            <div class="wph-sf2-header-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.3px;"><?php esc_html_e( 'Chống Spam', 'whp' ); ?></h1>
        </div>
        <p style="margin:0;font-size:13.5px;color:#64748b;line-height:1.6;padding-left:58px;max-width:420px;"><?php esc_html_e( 'Bảo vệ form liên hệ khỏi bot và tin nhắn rác tự động.', 'whp' ); ?> <strong><?php esc_html_e( 'Tắt công tắc này sẽ vô hiệu hóa toàn bộ tính năng chặn spam', 'whp' ); ?></strong>, <?php esc_html_e( 'kể cả khi các mục bên dưới đang bật.', 'whp' ); ?></p>
        <div style="display:inline-flex;align-items:center;gap:10px;padding-left:58px;margin-top:6px;">
            <label class="wph-sf2-master-switch">
                <input type="checkbox" id="sf2-active" autocomplete="off" <?php echo $active ? 'checked' : ''; ?> onchange="wphSf2ActiveChange(this)">
                <span class="wph-sf2-master-slider"></span>
            </label>
            <span id="sf2-active-label" style="font-size:13px;font-weight:700;color:<?php echo $active ? '#22c55e' : '#ef4444'; ?>;"><?php echo $active ? esc_html__( 'Đang bật', 'whp' ) : esc_html__( 'Đang tắt', 'whp' ); ?></span>
        </div>
    </div>
    <!-- Right: security illustration -->
    <div class="wph-sf2-header-right">
        <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
            <defs>
                <linearGradient id="sf2_hbg" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#fff5f5" stop-opacity="0"/>
                    <stop offset="30%" stop-color="#fff0f0" stop-opacity="0.7"/>
                    <stop offset="100%" stop-color="#ffe0e0" stop-opacity="1"/>
                </linearGradient>
                <filter id="sf2_shadow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(220,38,38,0.15)"/>
                </filter>
                <filter id="sf2_shadowSm" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(220,38,38,0.10)"/>
                </filter>
            </defs>
            <!-- bg wash -->
            <rect width="680" height="168" fill="url(#sf2_hbg)"/>
            <!-- decorative circles -->
            <circle cx="580" cy="15" r="65" fill="#ef4444" fill-opacity=".05"/>
            <circle cx="645" cy="148" r="45" fill="#dc2626" fill-opacity=".05"/>
            <circle cx="310" cy="84" r="90" fill="#fca5a5" fill-opacity=".04"/>

            <!-- Main shield (center) -->
            <g filter="url(#sf2_shadow)">
                <path d="M415 28 L455 42 L455 86 Q455 112 415 126 Q375 112 375 86 L375 42 Z" fill="#fff" stroke="#fca5a5" stroke-width="1.5"/>
                <path d="M415 30 L451 43 L451 86 Q451 110 415 123 Q379 110 379 86 L379 43 Z" fill="#fff5f5"/>
                <!-- Checkmark inside shield -->
                <polyline points="397,80 410,93 433,66" stroke="#22c55e" stroke-width="5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </g>

            <!-- Blocked spam envelope (right) -->
            <g filter="url(#sf2_shadowSm)">
                <rect x="560" y="38" width="82" height="58" rx="6" fill="#fff"/>
                <path d="M560 50l41 27 41-27" stroke="#fca5a5" stroke-width="1.8" stroke-linecap="round"/>
                <line x1="564" y1="42" x2="638" y2="92" stroke="#ef4444" stroke-width="3" stroke-linecap="round"/>
                <line x1="638" y1="42" x2="564" y2="92" stroke="#ef4444" stroke-width="3" stroke-linecap="round"/>
            </g>

            <!-- Small bot icon (left of shield) -->
            <g filter="url(#sf2_shadowSm)" transform="translate(310,54)">
                <rect width="52" height="46" rx="9" fill="#1e293b"/>
                <circle cx="18" cy="17" r="5" fill="#ef4444"/>
                <circle cx="34" cy="17" r="5" fill="#ef4444"/>
                <rect x="14" y="29" width="24" height="4" rx="2" fill="#475569"/>
                <!-- antenna -->
                <rect x="24" y="-6" width="4" height="10" rx="2" fill="#64748b"/>
                <circle cx="26" cy="-7" r="3" fill="#ef4444" fill-opacity=".7"/>
                <!-- legs -->
                <rect x="16" y="46" width="6" height="8" rx="2" fill="#475569"/>
                <rect x="30" y="46" width="6" height="8" rx="2" fill="#475569"/>
            </g>

            <!-- Warning triangle -->
            <g filter="url(#sf2_shadowSm)" transform="translate(484,96)">
                <path d="M28 4 L54 48 L2 48 Z" fill="#fff7ed" stroke="#fca5a5" stroke-width="1.5"/>
                <rect x="26" y="18" width="4" height="16" rx="2" fill="#f97316"/>
                <circle cx="28" cy="41" r="2.5" fill="#f97316"/>
            </g>

            <!-- Padlock -->
            <g filter="url(#sf2_shadowSm)" transform="translate(345,100)">
                <rect x="0" y="10" width="26" height="20" rx="4" fill="#fff" stroke="#fca5a5" stroke-width="1"/>
                <path d="M5 10V7a8 8 0 0 1 16 0v3" stroke="#ef4444" stroke-width="2.2" fill="none" stroke-linecap="round"/>
                <circle cx="13" cy="20" r="3" fill="#ef4444"/>
                <rect x="12" y="20" width="2" height="5" rx="1" fill="#ef4444"/>
            </g>

            <!-- Flying mini envelope (top-right) -->
            <g filter="url(#sf2_shadowSm)" transform="translate(480,22) rotate(-10)">
                <rect width="44" height="32" rx="4" fill="#fff0f0"/>
                <path d="M0 6l22 14 22-14" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round"/>
            </g>

            <!-- Dotted lines connecting elements -->
            <line x1="362" y1="84" x2="375" y2="84" stroke="#fca5a5" stroke-width="1.5" stroke-dasharray="4 3"/>
            <line x1="456" y1="84" x2="560" y2="67" stroke="#fca5a5" stroke-width="1.5" stroke-dasharray="4 3"/>
            <circle cx="362" cy="84" r="3" fill="#ef4444" fill-opacity=".4"/>
            <circle cx="456" cy="84" r="3" fill="#ef4444" fill-opacity=".4"/>

            <!-- Floating dots -->
            <circle cx="348" cy="28" r="4" fill="#ef4444" fill-opacity=".2"/>
            <circle cx="360" cy="135" r="3" fill="#dc2626" fill-opacity=".2"/>
            <circle cx="546" cy="32" r="5" fill="#f87171" fill-opacity=".18"/>
            <circle cx="428" cy="148" r="3.5" fill="#ef4444" fill-opacity=".15"/>
            <circle cx="500" cy="145" r="2.5" fill="#dc2626" fill-opacity=".12"/>
        </svg>
    </div>
</div>

<!-- ── STATS ─────────────────────────────────────────────────────────────── -->
<div class="wph-sf2-stats" style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;">
<?php
$scards = array(
    array(
        'val' => $stats['today'], 'lbl' => __( 'Spam hôm nay', 'whp' ), 'cmp' => __( 'so với hôm qua', 'whp' ),
        'ic' => '#f97316', 'bg' => '#fff7ed',
        'path' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'trend' => $trends[0],
    ),
    array(
        'val' => $stats['week'], 'lbl' => __( 'Spam 7 ngày', 'whp' ), 'cmp' => __( 'so với 7 ngày trước', 'whp' ),
        'ic' => '#f97316', 'bg' => '#fff7ed',
        'path' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'trend' => $trends[1],
    ),
    array(
        'val' => $stats['by_ip'], 'lbl' => __( 'IP đã chặn', 'whp' ), 'cmp' => __( 'so với 7 ngày trước', 'whp' ),
        'ic' => '#3b82f6', 'bg' => '#eff6ff',
        'path' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'trend' => $trends[2],
    ),
    array(
        'val' => $stats['by_email'], 'lbl' => __( 'Email đã chặn', 'whp' ), 'cmp' => __( 'so với 7 ngày trước', 'whp' ),
        'ic' => '#8b5cf6', 'bg' => '#f5f3ff',
        'path' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'trend' => $trends[3],
    ),
    array(
        'val' => $total_blocked, 'lbl' => __( 'Tổng bị chặn', 'whp' ), 'cmp' => __( 'so với 7 ngày trước', 'whp' ),
        'ic' => '#22c55e', 'bg' => '#f0fdf4',
        'path' => '<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>',
        'trend' => $trends[4],
    ),
);
foreach ( $scards as $c ) :
    $t   = $c['trend'];
    $dir = $t['dir'];
    $pct = $t['pct'];
    if ( $dir === 'new' ) {
        $trend_cls  = 'wph-sf2-trend-up';
        $trend_html = '<span>✦ ' . esc_html__( 'Mới', 'whp' ) . '</span><span class="wph-sf2-trend-cmp">' . esc_html( $c['cmp'] ) . '</span>';
    } elseif ( $pct === null ) {
        $trend_cls  = 'wph-sf2-trend-none';
        $trend_html = '<span>—</span><span class="wph-sf2-trend-cmp">' . esc_html( $c['cmp'] ) . '</span>';
    } else {
        $trend_cls  = $dir === 'up' ? 'wph-sf2-trend-up' : 'wph-sf2-trend-down';
        $arrow      = $dir === 'up' ? '↑' : '↓';
        $trend_html = '<span>' . $arrow . ' ' . $pct . '%</span><span class="wph-sf2-trend-cmp">' . esc_html( $c['cmp'] ) . '</span>';
    }
?>
<div class="wph-sf2-stat-card">
    <div class="wph-sf2-stat-icon" style="background:<?php echo esc_attr( $c['bg'] ); ?>;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $c['ic'] ); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $c['path']; ?></svg>
    </div>
    <div class="wph-sf2-stat-body">
        <div class="wph-sf2-stat-num" style="color:<?php echo esc_attr( $c['ic'] ); ?>;"><?php echo esc_html( $c['val'] ); ?></div>
        <div class="wph-sf2-stat-lbl"><?php echo esc_html( $c['lbl'] ); ?></div>
        <div class="wph-sf2-stat-trend <?php echo esc_attr( $trend_cls ); ?>"><?php echo $trend_html; ?></div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- ── BODY: 2 columns ───────────────────────────────────────────────────── -->
<div id="sf2-content-wrap" class="wph-sf2-body<?php echo !$active ? ' sf2-disabled' : ''; ?>" style="display:grid;grid-template-columns:1fr 360px;gap:20px;margin-bottom:20px;align-items:start;">

    <!-- LEFT: SETTINGS CARD -->
    <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;">
            <div style="width:32px;height:32px;background:#fef2f2;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
            </div>
            <h3 style="margin:0;font-size:16px;font-weight:700;color:#1e293b;"><?php esc_html_e( 'Cài đặt bảo vệ', 'whp' ); ?></h3>
        </div>

        <?php
        $sf_rows = array(
            array(
                'id'    => 'sf2-honeypot',
                'ibg'   => '#fef9c3', 'isc' => '#ca8a04',
                'svg'   => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
                'title' => 'Honeypot Protection',
                'desc'  => __( 'Ẩn trường honeypot để phát hiện bot tự động.', 'whp' ),
                'ctrl'  => 'toggle', 'checked' => $hp_active,
            ),
            array(
                'id'    => 'sf2-ratelimit',
                'ibg'   => '#eff6ff', 'isc' => '#3b82f6',
                'svg'   => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                'title' => __( 'Giới hạn gửi (Rate Limit)', 'whp' ),
                'desc'  => __( 'Giới hạn số lần gửi từ một IP trong khoảng thời gian nhất định.', 'whp' ),
                'ctrl'  => 'ratelimit', 'checked' => $rl_active,
                'rl_max' => $rl_max, 'rl_min' => $rl_min,
            ),
            array(
                'id'    => 'sf2-temp-email',
                'ibg'   => '#fdf4ff', 'isc' => '#a855f7',
                'svg'   => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
                'title' => __( 'Chặn Email tạm thời', 'whp' ),
                'desc'  => __( 'Chặn các email tạm thời và dùng một lần.', 'whp' ),
                'ctrl'  => 'toggle', 'checked' => $temp_active,
            ),
            array(
                'id'    => 'sf2-proxy-vpn',
                'ibg'   => '#fff7ed', 'isc' => '#f97316',
                'svg'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
                'title' => __( 'Chặn Proxy / VPN', 'whp' ),
                'desc'  => __( 'Phát hiện và chặn các IP sử dụng Proxy hoặc VPN.', 'whp' ),
                'ctrl'  => 'toggle', 'checked' => $proxy_active,
            ),
            array(
                'id'    => 'sf2-dnsbl',
                'ibg'   => '#eff6ff', 'isc' => '#2563eb',
                'svg'   => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
                'title' => __( 'Kiểm tra DNSBL', 'whp' ),
                'desc'  => __( 'Kiểm tra IP trong danh sách DNSBL (Spamhaus, Barracuda...).', 'whp' ),
                'ctrl'  => 'dnsbl', 'dnsbl' => $dnsbl_level,
            ),
            array(
                'id'    => 'sf2-hide-error',
                'ibg'   => '#f8fafc', 'isc' => '#64748b',
                'svg'   => '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>',
                'title' => __( 'Ẩn thông báo lỗi', 'whp' ),
                'desc'  => __( 'Ẩn thông báo lỗi chi tiết với người dùng khi bị chặn.', 'whp' ),
                'ctrl'  => 'toggle', 'checked' => $hide_error,
            ),
            array(
                'id'    => 'sf2-monitor-mode',
                'ibg'   => '#fefce8', 'isc' => '#ca8a04',
                'svg'   => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="2.01"/><line x1="12" y1="22" x2="12" y2="22.01"/>',
                'title' => __( 'Chế độ Giám sát', 'whp' ),
                'desc'  => __( 'Ghi log nhưng KHÔNG chặn — dùng để học và kiểm tra trước khi bật chặn thật.', 'whp' ),
                'ctrl'  => 'toggle', 'checked' => $monitor_mode,
            ),
            array(
                'id'    => 'sf2-code-detect',
                'ibg'   => '#fef2f2', 'isc' => '#dc2626',
                'svg'   => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
                'title' => __( 'Phát hiện nội dung code/script', 'whp' ),
                'desc'  => __( 'Tự động chặn khi phát hiện JSON, HTML injection, SQL injection hoặc link spam trong form.', 'whp' ),
                'ctrl'  => 'code_detect', 'checked' => $code_detect_active, 'code_level' => $code_detect_level,
            ),
        );
        foreach ( $sf_rows as $row ) :
        ?>
        <div class="wph-sf2-srow">
            <div class="wph-sf2-srow-icon" style="background:<?php echo esc_attr( $row['ibg'] ); ?>;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $row['isc'] ); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $row['svg']; ?></svg>
            </div>
            <div class="wph-sf2-srow-body">
                <div class="wph-sf2-srow-title"><?php echo esc_html( $row['title'] ); ?></div>
                <div class="wph-sf2-srow-desc"><?php echo esc_html( $row['desc'] ); ?></div>
            </div>
            <div class="wph-sf2-srow-ctrl">
                <?php if ( $row['ctrl'] === 'ratelimit' ) : ?>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                    <div class="wph-sf2-rl-inputs">
                        <input type="number" id="sf2-rl-max" class="wph-sf2-rl-input" value="<?php echo esc_attr( $row['rl_max'] ); ?>" min="1" max="100" title="<?php esc_attr_e( 'Số lần gửi tối đa', 'whp' ); ?>">
                        <span class="wph-sf2-rl-sep"><?php esc_html_e( 'lần /', 'whp' ); ?></span>
                        <input type="number" id="sf2-rl-minutes" class="wph-sf2-rl-input" value="<?php echo esc_attr( $row['rl_min'] ); ?>" min="1" max="1440" title="<?php esc_attr_e( 'Trong khoảng thời gian (phút)', 'whp' ); ?>">
                        <span class="wph-sf2-rl-sep"><?php esc_html_e( 'phút', 'whp' ); ?></span>
                    </div>
                    <span style="font-size:11px;color:#94a3b8;"><?php esc_html_e( 'Gợi ý: 3 lần / 5 phút', 'whp' ); ?></span>
                </div>
                <?php endif; ?>
                <?php if ( $row['ctrl'] === 'toggle' || $row['ctrl'] === 'ratelimit' ) :
                    $is_on = ! empty( $row['checked'] );
                    $lbl_id = esc_attr( $row['id'] ) . '-lbl';
                ?>
                <span id="<?php echo $lbl_id; ?>" style="font-size:12.5px;font-weight:700;color:<?php echo $is_on ? '#22c55e' : '#94a3b8'; ?>;"><?php echo $is_on ? esc_html__( 'Bật', 'whp' ) : esc_html__( 'Tắt', 'whp' ); ?></span>
                <label class="wph-sf2-toggle">
                    <input type="checkbox" id="<?php echo esc_attr( $row['id'] ); ?>" autocomplete="off" <?php echo $is_on ? 'checked' : ''; ?> onchange="(function(cb){var l=document.getElementById('<?php echo $lbl_id; ?>');l.textContent=cb.checked?whpSfI18n.on:whpSfI18n.off;l.style.color=cb.checked?'#22c55e':'#94a3b8';})(this)">
                    <span class="wph-sf2-toggle-slider"></span>
                </label>
                <?php elseif ( $row['ctrl'] === 'dnsbl' ) :
                    $dnsbl_opts = array(
                        'off'    => array( 'label' => __( 'Tắt',        'whp' ), 'color' => '#ef4444' ),
                        'light'  => array( 'label' => __( 'Nhẹ',        'whp' ), 'color' => '#f97316' ),
                        'medium' => array( 'label' => __( 'Trung bình',  'whp' ), 'color' => '#3b82f6' ),
                        'heavy'  => array( 'label' => __( 'Mạnh',        'whp' ), 'color' => '#22c55e' ),
                    );
                    $cur_dnsbl = $row['dnsbl'] ?? 'off';
                    $cur_opt   = $dnsbl_opts[ $cur_dnsbl ] ?? $dnsbl_opts['off'];
                ?>
                <input type="hidden" id="sf2-dnsbl-level" value="<?php echo esc_attr( $cur_dnsbl ); ?>">
                <div class="wph-sf2-cs" id="sf2-dnsbl-cs">
                    <div class="wph-sf2-cs-trigger" id="sf2-dnsbl-trigger" onclick="wphSf2CsToggle('sf2-dnsbl-cs')">
                        <span class="wph-sf2-cs-dot" id="sf2-dnsbl-dot" style="background:<?php echo esc_attr( $cur_opt['color'] ); ?>;"></span>
                        <span id="sf2-dnsbl-lbl"><?php echo esc_html( $cur_opt['label'] ); ?></span>
                        <svg class="wph-sf2-cs-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="wph-sf2-cs-menu" id="sf2-dnsbl-menu">
                        <?php foreach ( $dnsbl_opts as $val => $opt ) : ?>
                        <div class="wph-sf2-cs-opt <?php echo $cur_dnsbl === $val ? 'selected' : ''; ?>"
                             onclick="wphSf2CsSelect(this,'sf2-dnsbl-cs','sf2-dnsbl-level','sf2-dnsbl-dot','sf2-dnsbl-lbl','<?php echo esc_js($val); ?>','<?php echo esc_js($opt['label']); ?>','<?php echo esc_js($opt['color']); ?>')">
                            <span class="wph-sf2-cs-dot" style="background:<?php echo esc_attr( $opt['color'] ); ?>;"></span>
                            <?php echo esc_html( $opt['label'] ); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php elseif ( $row['ctrl'] === 'code_detect' ) :
                    $cd_opts = array(
                        'basic'  => array( 'label' => __( 'Cơ bản', 'whp' ), 'color' => '#3b82f6' ),
                        'strong' => array( 'label' => __( 'Mạnh',   'whp' ), 'color' => '#dc2626' ),
                    );
                    $cur_cd     = $row['code_level'] ?? 'basic';
                    $cur_cd_opt = $cd_opts[ $cur_cd ] ?? $cd_opts['basic'];
                    $cd_on      = ! empty( $row['checked'] );
                    $cd_lbl_id  = esc_attr( $row['id'] ) . '-lbl';
                ?>
                <input type="hidden" id="sf2-code-detect-level" value="<?php echo esc_attr( $cur_cd ); ?>">
                <div class="wph-sf2-cs" id="sf2-cd-cs">
                    <div class="wph-sf2-cs-trigger" id="sf2-cd-trigger" onclick="wphSf2CsToggle('sf2-cd-cs')">
                        <span class="wph-sf2-cs-dot" id="sf2-cd-dot" style="background:<?php echo esc_attr( $cur_cd_opt['color'] ); ?>;"></span>
                        <span id="sf2-cd-lbl"><?php echo esc_html( $cur_cd_opt['label'] ); ?></span>
                        <svg class="wph-sf2-cs-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="wph-sf2-cs-menu wph-sf2-cs-menu-up" id="sf2-cd-menu">
                        <?php foreach ( $cd_opts as $cd_val => $cd_opt ) : ?>
                        <div class="wph-sf2-cs-opt <?php echo $cur_cd === $cd_val ? 'selected' : ''; ?>"
                             onclick="wphSf2CsSelect(this,'sf2-cd-cs','sf2-code-detect-level','sf2-cd-dot','sf2-cd-lbl','<?php echo esc_js($cd_val); ?>','<?php echo esc_js($cd_opt['label']); ?>','<?php echo esc_js($cd_opt['color']); ?>')">
                            <span class="wph-sf2-cs-dot" style="background:<?php echo esc_attr( $cd_opt['color'] ); ?>;"></span>
                            <?php echo esc_html( $cd_opt['label'] ); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <span id="<?php echo $cd_lbl_id; ?>" style="font-size:12.5px;font-weight:700;color:<?php echo $cd_on ? '#22c55e' : '#94a3b8'; ?>;"><?php echo $cd_on ? esc_html__( 'Bật', 'whp' ) : esc_html__( 'Tắt', 'whp' ); ?></span>
                <label class="wph-sf2-toggle">
                    <input type="checkbox" id="<?php echo esc_attr( $row['id'] ); ?>" autocomplete="off" <?php echo $cd_on ? 'checked' : ''; ?>
                        onchange="(function(cb){var l=document.getElementById('<?php echo $cd_lbl_id; ?>');l.textContent=cb.checked?whpSfI18n.on:whpSfI18n.off;l.style.color=cb.checked?'#22c55e':'#94a3b8';})(this)">
                    <span class="wph-sf2-toggle-slider"></span>
                </label>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- RIGHT: QUICK BLOCK + DONUT CHART -->
    <div style="display:flex;flex-direction:column;gap:16px;position:sticky;top:52px;align-self:start;">

        <!-- Quick Block -->
        <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">
            <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;">
                <h3 style="margin:0;font-size:14px;font-weight:700;color:#1e293b;"><?php esc_html_e( 'Danh sách chặn nhanh', 'whp' ); ?></h3>
            </div>

            <!-- Tabs -->
            <div style="display:flex;border-bottom:1px solid #f1f5f9;padding:0 20px;">
                <?php foreach ( array( 'ip' => 'IP', 'email' => 'Email', 'keyword' => 'Keyword', 'country' => __( 'Quốc gia', 'whp' ) ) as $k => $lbl ) : ?>
                <button class="wph-sf2-qb-tab <?php echo $k === 'ip' ? 'active' : ''; ?>" data-tab="<?php echo esc_attr( $k ); ?>" onclick="wphSf2QbTab('<?php echo esc_js( $k ); ?>')"><?php echo esc_html( $lbl ); ?></button>
                <?php endforeach; ?>
            </div>

            <div style="padding:16px 20px;">
                <?php
                $qb_tabs = array(
                    'ip'      => array( 'ph' => __( 'Nhập IP và nhấn Enter...', 'whp' ),       'items' => $bl_ips ),
                    'email'   => array( 'ph' => __( 'Nhập email và nhấn Enter...', 'whp' ),    'items' => $bl_emails ),
                    'keyword' => array( 'ph' => __( 'Nhập từ khóa và nhấn Enter...', 'whp' ),  'items' => $bl_keywords ),
                    'country' => array( 'ph' => __( 'Nhập tên hoặc mã quốc gia (VD: Trung Quốc, CN)...', 'whp' ), 'items' => $bl_countries ),
                );
                foreach ( $qb_tabs as $type => $tab ) :
                    $show_max = 5;
                    $overflow = count( $tab['items'] ) > $show_max;
                    $is_country = $type === 'country';
                ?>
                <div id="wph-sf2-qb-tab-<?php echo esc_attr( $type ); ?>" class="wph-sf2-qb-panel" style="display:<?php echo $type === 'ip' ? 'block' : 'none'; ?>;">
                    <!-- Input row -->
                    <div style="display:flex;gap:8px;margin-bottom:14px;<?php echo $is_country ? 'position:relative;' : ''; ?>">
                        <input type="text" id="sf2-qb-input-<?php echo esc_attr( $type ); ?>" placeholder="<?php echo esc_attr( $tab['ph'] ); ?>" autocomplete="off"
                            style="flex:1;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 13px;font-size:13px;color:#1e293b;outline:none;transition:border-color .15s;background:#fff;"
                            onkeydown="if(event.key==='Enter'){<?php echo $is_country ? 'wphSf2QbAddCountry()' : "wphSf2QbAdd('{$type}')"; ?>;return false;}"
                            <?php if ( $is_country ) : ?>
                            oninput="wphSf2CountryAc(this.value)"
                            onblur="setTimeout(function(){var d=document.getElementById('sf2-country-ac');if(d)d.style.display='none';},200)"
                            <?php endif; ?>
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                        <?php if ( $is_country ) : ?>
                        <input type="hidden" id="sf2-qb-country-code">
                        <div id="sf2-country-ac" style="display:none;position:absolute;top:100%;left:0;right:60px;background:#fff;border:1.5px solid #3b82f6;border-top:none;border-radius:0 0 8px 8px;z-index:999;max-height:200px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,.08);"></div>
                        <?php endif; ?>
                        <button onclick="<?php echo $is_country ? 'wphSf2QbAddCountry()' : "wphSf2QbAdd('" . esc_js( $type ) . "')"; ?>" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;transition:opacity .15s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'"><?php esc_html_e( 'Thêm', 'whp' ); ?></button>
                        <?php if ( $type === 'keyword' ) : ?>
                        <button onclick="wphSf2KwOpenImport()" title="<?php esc_attr_e( 'Import từ khóa (danh sách / CSV / Excel)', 'whp' ); ?>" style="background:#f0fdf4;color:#16a34a;border:1.5px solid #86efac;border-radius:8px;padding:9px 10px;cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center;transition:opacity .15s;" onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php if ( $is_country ) : ?>
                    <div style="display:flex;align-items:flex-start;gap:6px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:8px 11px;margin-bottom:12px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="#f97316" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13" stroke="#fff" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        <span style="font-size:11.5px;color:#9a3412;line-height:1.5;"><?php esc_html_e( 'Dùng API', 'whp' ); ?> <strong>ip-api.com</strong> <?php esc_html_e( 'tra cứu IP theo quốc gia — giới hạn', 'whp' ); ?> <strong>45 requests/phút</strong> (free). <?php esc_html_e( 'IP mới tra 1 lần, kết quả cache 24h. Dùng', 'whp' ); ?> <a href="https://cloudflare.com" target="_blank" style="color:#2563eb;text-decoration:none;font-weight:600;">Cloudflare</a> <?php esc_html_e( 'nếu cần không giới hạn.', 'whp' ); ?></span>
                    </div>
                    <?php endif; ?>
                    <!-- List -->
                    <div id="sf2-qb-list-<?php echo esc_attr( $type ); ?>">
                        <?php if ( empty( $tab['items'] ) ) : ?>
                        <p id="sf2-qb-empty-<?php echo esc_attr( $type ); ?>" style="text-align:center;color:#94a3b8;font-size:12.5px;padding:20px 0;margin:0;"><?php esc_html_e( 'Chưa có mục nào', 'whp' ); ?></p>
                        <?php else :
                            foreach ( array_slice( $tab['items'], 0, $show_max ) as $item ) : ?>
                        <div class="wph-sf2-qb-item">
                            <?php if ( $is_country ) : ?>
                            <span class="wph-sf2-country-item" data-code="<?php echo esc_attr( $item ); ?>" style="font-size:13px;color:#334155;"><?php echo esc_html( $item ); ?></span>
                            <?php else : ?>
                            <span style="font-size:13px;color:#334155;word-break:break-all;"><?php echo esc_html( $item ); ?></span>
                            <?php endif; ?>
                            <button class="wph-sf2-qb-del" data-type="<?php echo esc_attr($type); ?>" data-val="<?php echo esc_attr($item); ?>" onclick="wphSf2QbRemove(this.dataset.type,this.dataset.val,this.closest('.wph-sf2-qb-item'))" title="<?php esc_attr_e( 'Xóa', 'whp' ); ?>">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6m4-6v6"/><path d="M9 6V4h6v2"/></svg>
                            </button>
                        </div>
                            <?php endforeach;
                            if ( $overflow ) : ?>
                        <div style="padding-top:10px;border-top:1px solid #f1f5f9;margin-top:4px;">
                            <a href="#" data-type="<?php echo esc_attr( $type ); ?>" data-items="<?php echo esc_attr( wp_json_encode( $tab['items'] ) ); ?>" onclick="wphSf2QbShowAll(this);return false;" style="font-size:12.5px;color:#2563eb;text-decoration:none;font-weight:600;"><?php echo esc_html__( 'Xem tất cả', 'whp' ) . ' (' . count( $tab['items'] ) . ') &rsaquo;'; ?></a>
                        </div>
                            <?php endif;
                        endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Donut Chart -->
        <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,.05);padding:18px 20px;">
            <h3 style="margin:0 0 16px;font-size:14px;font-weight:700;color:#1e293b;"><?php esc_html_e( 'Top lý do bị chặn', 'whp' ); ?></h3>
            <?php if ( $chart_total > 0 ) : ?>
            <div style="display:flex;align-items:center;gap:20px;">
                <!-- Donut -->
                <div style="position:relative;width:120px;height:120px;flex-shrink:0;">
                    <div style="width:120px;height:120px;border-radius:50%;background:<?php echo esc_attr( $donut_gradient ); ?>;"></div>
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                        <div style="width:74px;height:74px;background:#fff;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:0 0 0 1px #f1f5f9;">
                            <span style="font-size:20px;font-weight:800;color:#1e293b;line-height:1;"><?php echo number_format( $chart_total ); ?></span>
                            <span style="font-size:9.5px;color:#94a3b8;margin-top:2px;white-space:nowrap;"><?php esc_html_e( 'Spam bị chặn', 'whp' ); ?></span>
                        </div>
                    </div>
                </div>
                <!-- Legend -->
                <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ( $chart_colors as $cat => $color ) :
                        $cnt = $reason_stats[ $cat ] ?? 0;
                        if ( $cnt <= 0 ) continue;
                        $pct = round( $cnt / $chart_total * 100 );
                    ?>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:9px;height:9px;border-radius:50%;background:<?php echo esc_attr( $color ); ?>;flex-shrink:0;"></div>
                        <span style="flex:1;font-size:12px;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html( $cat ); ?></span>
                        <span style="font-size:12px;font-weight:700;color:#1e293b;flex-shrink:0;"><?php echo $pct; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else : ?>
            <div style="text-align:center;padding:30px 0;color:#94a3b8;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 10px;opacity:.35;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                <p style="margin:0;font-size:12.5px;"><?php esc_html_e( 'Chưa có dữ liệu spam', 'whp' ); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Log settings card -->
        <div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;">
            <div style="padding:14px 18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:9px;">
                <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#eff6ff,#dbeafe);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                </div>
                <h3 style="margin:0;font-size:13.5px;font-weight:700;color:#1e293b;"><?php esc_html_e( 'Cài đặt lưu log', 'whp' ); ?></h3>
            </div>
            <div style="padding:14px 18px;display:flex;flex-direction:column;gap:10px;">
                <div style="border:1.5px solid #f1f5f9;border-radius:10px;padding:12px 13px;background:#fafcff;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12.5px;font-weight:600;color:#1e293b;"><?php esc_html_e( 'Thời gian lưu log', 'whp' ); ?></div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:1px;"><?php esc_html_e( 'Tự động xóa log cũ hơn mốc này', 'whp' ); ?></div>
                        </div>
                        <select id="sf-log-retention" style="min-width:120px;border:1.5px solid #e2e8f0;border-radius:7px;padding:6px 8px;font-size:12px;background:#fff;">
                            <?php foreach ( array( 0 => __( 'Không giới hạn', 'whp' ), 30 => __( '30 ngày', 'whp' ), 60 => __( '60 ngày', 'whp' ), 90 => __( '90 ngày', 'whp' ), 180 => __( '180 ngày', 'whp' ), 365 => __( '365 ngày', 'whp' ) ) as $v => $l ) : ?>
                            <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $log_settings['retention'] ?? 0, $v ); ?>><?php echo esc_html( $l ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="border:1.5px solid #f1f5f9;border-radius:10px;padding:12px 13px;background:#fafcff;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#fffbeb,#fef3c7);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12.5px;font-weight:600;color:#1e293b;"><?php esc_html_e( 'Giới hạn tối đa', 'whp' ); ?></div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:1px;"><?php esc_html_e( 'Số bản ghi tối đa được lưu', 'whp' ); ?></div>
                        </div>
                        <select id="sf-log-maxlogs" style="min-width:120px;border:1.5px solid #e2e8f0;border-radius:7px;padding:6px 8px;font-size:12px;background:#fff;">
                            <?php foreach ( array( 10000 => '10.000', 25000 => '25.000', 50000 => '50.000', 100000 => '100.000', 0 => __( 'Không giới hạn', 'whp' ) ) as $v => $l ) : ?>
                            <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $log_settings['max_logs'] ?? 0, $v ); ?>><?php echo esc_html( $l ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button onclick="wphSf2SaveLogSettings()" style="width:100%;background:linear-gradient(135deg,#3858e9,#2563eb);color:#fff;border:none;border-radius:9px;padding:9px 0;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:opacity .15s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <?php esc_html_e( 'Lưu cài đặt', 'whp' ); ?>
                </button>
            </div>
        </div>

    </div><!-- /right -->
</div><!-- /body grid -->

<!-- ── KEYWORD IMPORT MODAL ────────────────────────────────────────────────── -->
<div id="sf2-kw-import-overlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:99999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.22);width:min(520px,95vw);overflow:hidden;">
        <!-- Header -->
        <div style="padding:18px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;background:#f0fdf4;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:#0f172a;"><?php esc_html_e( 'Import danh sách từ khóa', 'whp' ); ?></div>
                    <div style="font-size:11.5px;color:#94a3b8;margin-top:1px;"><?php esc_html_e( 'Hỗ trợ dán text, file CSV hoặc Excel (.xlsx)', 'whp' ); ?></div>
                </div>
            </div>
            <button onclick="wphSf2KwCloseImport()" style="background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px;border-radius:6px;display:flex;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <!-- Tabs -->
        <div style="display:flex;border-bottom:1px solid #f1f5f9;padding:0 22px;">
            <button id="sf2-kw-tab-text" onclick="wphSf2KwTab('text')" style="padding:10px 16px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;color:#3b82f6;border-bottom:2px solid #3b82f6;margin-bottom:-1px;"><?php esc_html_e( 'Dán danh sách', 'whp' ); ?></button>
            <button id="sf2-kw-tab-file" onclick="wphSf2KwTab('file')" style="padding:10px 16px;font-size:13px;font-weight:600;border:none;background:none;cursor:pointer;color:#94a3b8;border-bottom:2px solid transparent;margin-bottom:-1px;"><?php esc_html_e( 'Upload CSV / Excel', 'whp' ); ?></button>
        </div>
        <!-- Tab: paste text -->
        <div id="sf2-kw-panel-text" style="padding:18px 22px;">
            <textarea id="sf2-kw-import-text" rows="9" placeholder="xem phim miễn phí&#10;casino trực tuyến&#10;vay nhanh không cần thế chấp&#10;click here, buy now, free money" style="width:100%;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 13px;font-size:13px;color:#1e293b;outline:none;resize:vertical;line-height:1.6;box-sizing:border-box;transition:border-color .15s;" onfocus="this.style.borderColor='#16a34a'" onblur="this.style.borderColor='#e2e8f0'" oninput="wphSf2KwDetectCode(this)"></textarea>
            <div id="sf2-kw-code-warn" style="display:none;margin-top:7px;padding:9px 12px;background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;font-size:12.5px;color:#92400e;line-height:1.5;">
                <strong>⚠ <?php esc_html_e( 'Phát hiện nội dung code/JSON.', 'whp' ); ?></strong> <?php esc_html_e( 'Bạn đang dán code vào danh sách từ khóa thay vì từ khóa thông thường. Muốn import từ file thì chuyển sang tab "Upload CSV / Excel". Nếu muốn thêm từ khóa chứa ký tự đặc biệt, bạn vẫn có thể nhấn Import.', 'whp' ); ?>
            </div>
            <p style="margin:6px 0 0;font-size:11.5px;color:#94a3b8;"><?php esc_html_e( 'Mỗi dòng 1 từ khóa, hoặc phân cách bằng dấu phẩy. Từ trùng sẽ được bỏ qua tự động.', 'whp' ); ?></p>
        </div>
        <!-- Tab: file upload -->
        <div id="sf2-kw-panel-file" style="display:none;padding:18px 22px;">
            <div id="sf2-kw-dropzone" onclick="document.getElementById('sf2-kw-file-input').click()" ondragover="event.preventDefault();this.style.borderColor='#16a34a';this.style.background='#f0fdf4';" ondragleave="this.style.borderColor='#e2e8f0';this.style.background='#fafcff';" ondrop="wphSf2KwFileDrop(event)" style="border:2px dashed #e2e8f0;border-radius:12px;padding:32px 20px;text-align:center;cursor:pointer;background:#fafcff;transition:all .2s;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 10px;display:block;"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                <div style="font-size:13.5px;font-weight:600;color:#475569;margin-bottom:4px;"><?php esc_html_e( 'Kéo thả hoặc bấm để chọn file', 'whp' ); ?></div>
                <div style="font-size:12px;color:#94a3b8;"><?php esc_html_e( 'Hỗ trợ:', 'whp' ); ?> <strong>.csv</strong>, <strong>.xlsx</strong>, <strong>.xls</strong></div>
            </div>
            <input type="file" id="sf2-kw-file-input" accept=".csv,.xlsx,.xls" style="display:none;" onchange="wphSf2KwFileSelect(this)">
            <div id="sf2-kw-file-preview" style="display:none;margin-top:12px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12.5px;color:#166534;"></div>
            <div id="sf2-kw-file-error" style="display:none;margin-top:12px;padding:10px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:12.5px;color:#991b1b;"></div>
        </div>
        <!-- Notice: full-width, trên footer -->
        <div id="sf2-kw-import-notice" style="display:none;margin:0 22px 10px;padding:10px 14px;border-radius:8px;font-size:12.5px;font-weight:600;line-height:1.5;"></div>
        <!-- Footer: buttons only -->
        <div style="padding:0 22px 18px;display:flex;gap:10px;justify-content:flex-end;">
            <button onclick="wphSf2KwCloseImport()" style="background:#f8fafc;color:#64748b;border:1.5px solid #e2e8f0;border-radius:9px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer;"><?php esc_html_e( 'Hủy', 'whp' ); ?></button>
            <button onclick="wphSf2KwDoImport()" id="sf2-kw-import-btn" style="background:linear-gradient(135deg,#16a34a,#22c55e);color:#fff;border:none;border-radius:9px;padding:9px 22px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:7px;transition:opacity .15s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?php esc_html_e( 'Import từ khóa', 'whp' ); ?>
            </button>
        </div>
    </div>
</div>

<div class="wph-sf2-save-bar" id="sf2-save-bar"<?php echo !$active ? ' style="display:none;"' : ''; ?>>
    <span class="wph-sf2-save-bar-hint">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
        <?php esc_html_e( 'Các thay đổi được áp dụng ngay sau khi lưu.', 'whp' ); ?>
    </span>
    <button class="wph-sf2-save-btn" onclick="wphSf2Save()">
        <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:15px;"></span>
        <?php esc_html_e( 'Lưu cấu hình', 'whp' ); ?>
    </button>
</div>

<!-- ── LOG TABLE ─────────────────────────────────────────────────────────── -->
<div style="background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,.05);">
    <!-- Title row -->
    <div style="padding:16px 20px 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <div>
            <h3 style="margin:0;font-size:16px;font-weight:700;color:#1e293b;"><?php esc_html_e( 'Nhật ký Spam', 'whp' ); ?></h3>
            <p style="margin:2px 0 0;font-size:12px;color:#94a3b8;"><?php echo number_format( $log_total ); ?> <?php esc_html_e( 'bản ghi', 'whp' ); ?></p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <button onclick="wphSf2ExportCsv()" style="background:#f8fafc;color:#475569;border:1.5px solid #e2e8f0;border-radius:8px;padding:7px 14px;font-size:12.5px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;transition:all .15s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#f8fafc'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <?php esc_html_e( 'Xuất CSV', 'whp' ); ?>
            </button>
            <button onclick="wphSf2ClearLogs()" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;padding:7px 12px;font-size:12.5px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;white-space:nowrap;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                <?php esc_html_e( 'Xóa tất cả', 'whp' ); ?>
            </button>
        </div>
    </div>

    <!-- Unified filter bar -->
    <div style="padding:12px 20px 14px;">
        <div style="display:flex;align-items:center;width:100%;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;overflow:visible;transition:border-color .15s;" id="sf2-filterbar">
            <!-- Search -->
            <input type="text" id="sf2-log-search" placeholder="<?php esc_attr_e( 'Tìm kiếm IP, email, lý do...', 'whp' ); ?>"
                style="flex:1;min-width:120px;border:none;outline:none;padding:9px 14px;font-size:13px;color:#1e293b;background:transparent;"
                onkeydown="if(event.key==='Enter')wphSf2LogLoad(1)"
                onfocus="document.getElementById('sf2-filterbar').style.borderColor='#3b82f6'"
                onblur="document.getElementById('sf2-filterbar').style.borderColor='#e2e8f0'">
            <!-- Divider -->
            <div style="width:1px;height:22px;background:#e2e8f0;flex-shrink:0;"></div>
            <!-- Reason custom select -->
            <?php
            $log_reason_opts = array(
                array( 'val' => '',        'lbl' => __( 'Tất cả lý do',        'whp' ), 'color' => '#94a3b8' ),
                array( 'val' => 'Honeypot','lbl' => 'Honeypot',                          'color' => '#ca8a04' ),
                array( 'val' => 'Rate',    'lbl' => __( 'Giới hạn gửi',        'whp' ), 'color' => '#ea580c' ),
                array( 'val' => 'Bot',     'lbl' => __( 'Bot / User-Agent',     'whp' ), 'color' => '#d97706' ),
                array( 'val' => 'Email',   'lbl' => __( 'Email tạm thời',       'whp' ), 'color' => '#9333ea' ),
                array( 'val' => 'Keyword', 'lbl' => __( 'Từ khóa spam',         'whp' ), 'color' => '#16a34a' ),
                array( 'val' => 'proxy',   'lbl' => 'Proxy / VPN',                       'color' => '#dc2626' ),
                array( 'val' => 'IP',      'lbl' => __( 'IP / Danh sách chặn',  'whp' ), 'color' => '#2563eb' ),
            );
            ?>
            <input type="hidden" id="sf2-log-reason" value="">
            <div class="wph-sf2-cs wph-sf2-cs-flat" id="sf2-reason-cs" style="min-width:150px;">
                <div class="wph-sf2-cs-trigger wph-sf2-cs-flat-trigger" id="sf2-reason-trigger" onclick="wphSf2CsToggle('sf2-reason-cs')">
                    <span class="wph-sf2-cs-dot" id="sf2-reason-dot" style="background:#94a3b8;"></span>
                    <span id="sf2-reason-lbl"><?php esc_html_e( 'Tất cả lý do', 'whp' ); ?></span>
                    <svg class="wph-sf2-cs-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="wph-sf2-cs-menu" id="sf2-reason-menu" style="min-width:180px;">
                    <?php foreach ( $log_reason_opts as $i => $o ) : ?>
                    <div class="wph-sf2-cs-opt <?php echo $i === 0 ? 'selected' : ''; ?>"
                         onclick="wphSf2CsSelect(this,'sf2-reason-cs','sf2-log-reason','sf2-reason-dot','sf2-reason-lbl','<?php echo esc_js($o['val']); ?>','<?php echo esc_js($o['lbl']); ?>','<?php echo esc_js($o['color']); ?>');wphSf2LogLoad(1)">
                        <span class="wph-sf2-cs-dot" style="background:<?php echo esc_attr($o['color']); ?>;"></span>
                        <?php echo esc_html( $o['lbl'] ); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Divider -->
            <div style="width:1px;height:22px;background:#e2e8f0;flex-shrink:0;"></div>
            <!-- Status custom select -->
            <?php
            $log_status_opts = array(
                array( 'val' => '',            'lbl' => __( 'Tất cả trạng thái', 'whp' ), 'color' => '#94a3b8' ),
                array( 'val' => 'blocked',     'lbl' => __( 'Đã chặn',           'whp' ), 'color' => '#dc2626' ),
                array( 'val' => 'whitelisted', 'lbl' => __( 'Đã bỏ chặn',        'whp' ), 'color' => '#16a34a' ),
                array( 'val' => 'monitor',     'lbl' => __( 'Giám sát',           'whp' ), 'color' => '#ca8a04' ),
            );
            ?>
            <input type="hidden" id="sf2-log-status" value="">
            <div class="wph-sf2-cs wph-sf2-cs-flat" id="sf2-status-cs" style="min-width:148px;">
                <div class="wph-sf2-cs-trigger wph-sf2-cs-flat-trigger" id="sf2-status-trigger" onclick="wphSf2CsToggle('sf2-status-cs')">
                    <span class="wph-sf2-cs-dot" id="sf2-status-dot" style="background:#94a3b8;"></span>
                    <span id="sf2-status-lbl"><?php esc_html_e( 'Tất cả trạng thái', 'whp' ); ?></span>
                    <svg class="wph-sf2-cs-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="wph-sf2-cs-menu" id="sf2-status-menu" style="min-width:160px;">
                    <?php foreach ( $log_status_opts as $i => $o ) : ?>
                    <div class="wph-sf2-cs-opt <?php echo $i === 0 ? 'selected' : ''; ?>"
                         onclick="wphSf2CsSelect(this,'sf2-status-cs','sf2-log-status','sf2-status-dot','sf2-status-lbl','<?php echo esc_js($o['val']); ?>','<?php echo esc_js($o['lbl']); ?>','<?php echo esc_js($o['color']); ?>');wphSf2LogLoad(1)">
                        <span class="wph-sf2-cs-dot" style="background:<?php echo esc_attr($o['color']); ?>;"></span>
                        <?php echo esc_html( $o['lbl'] ); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Divider -->
            <div style="width:1px;height:22px;background:#e2e8f0;flex-shrink:0;"></div>
            <!-- Date range -->
            <div style="display:flex;align-items:center;gap:4px;padding:0 10px;flex-shrink:0;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" id="sf2-log-date-from" style="border:none;outline:none;font-size:12px;color:#475569;background:transparent;width:130px;" onchange="wphSf2LogLoad(1)">
                <span style="color:#94a3b8;font-size:12px;padding:0 2px;flex-shrink:0;">—</span>
                <input type="date" id="sf2-log-date-to" style="border:none;outline:none;font-size:12px;color:#475569;background:transparent;width:130px;" onchange="wphSf2LogLoad(1)">
            </div>
            <!-- Search btn -->
            <button onclick="wphSf2LogLoad(1)" style="margin:4px 6px 4px 0;background:#2563eb;color:#fff;border:none;border-radius:8px;width:36px;height:34px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:opacity .15s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'" title="<?php esc_attr_e( 'Tìm kiếm', 'whp' ); ?>">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
        </div>
    </div>

    <!-- Bulk action bar -->
    <div id="sf2-bulk-bar" style="display:none;align-items:center;gap:10px;background:#eff6ff;border-top:1px solid #bfdbfe;border-bottom:1px solid #bfdbfe;padding:8px 16px;font-size:13px;">
        <span style="color:#1d4ed8;font-weight:700;" id="sf2-bulk-count">0 <?php esc_html_e( 'đã chọn', 'whp' ); ?></span>
        <button onclick="wphSf2BulkDelete()" style="background:#dc2626;color:#fff;border:none;border-radius:7px;padding:5px 14px;font-size:12.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:opacity .15s;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
            <?php esc_html_e( 'Xóa đã chọn', 'whp' ); ?>
        </button>
        <button onclick="wphSf2DeselectAll()" style="background:none;border:none;color:#64748b;font-size:12px;cursor:pointer;padding:4px 8px;transition:color .12s;" onmouseover="this.style.color='#1d4ed8'" onmouseout="this.style.color='#64748b'"><?php esc_html_e( 'Bỏ chọn', 'whp' ); ?></button>
    </div>

    <div id="sf2-log-container" style="overflow-x:auto;">
        <?php wph_sf2_render_log_table( $log_rows, $log_total, $log_pages, $lpage ); ?>
    </div>
</div>

<script>
(function(){
window.whpSfI18n = {
    enabled:           '<?php echo esc_js( __( 'Đang bật', 'whp' ) ); ?>',
    disabled:          '<?php echo esc_js( __( 'Đang tắt', 'whp' ) ); ?>',
    on:                '<?php echo esc_js( __( 'Bật', 'whp' ) ); ?>',
    off:               '<?php echo esc_js( __( 'Tắt', 'whp' ) ); ?>',
    selected:          '<?php echo esc_js( __( 'đã chọn', 'whp' ) ); ?>',
    confirmBulkDelete: '<?php echo esc_js( __( 'bản ghi đã chọn? Không thể hoàn tác.', 'whp' ) ); ?>',
    invalidIp:         '<?php echo esc_js( __( 'IP không hợp lệ. Ví dụ: 192.168.1.1 hoặc 10.0.0.0/8', 'whp' ) ); ?>',
    invalidEmail:      '<?php echo esc_js( __( 'Định dạng không hợp lệ. Ví dụ: spam@gmail.com hoặc @tempmail.com', 'whp' ) ); ?>',
    keywordCodeError:  '<?php echo esc_js( __( 'Từ khóa chứa code/JSON — không thể lưu', 'whp' ) ); ?>',
    addedItem:         '<?php echo esc_js( __( 'Đã thêm', 'whp' ) ); ?>',
    removed:           '<?php echo esc_js( __( 'Đã xóa', 'whp' ) ); ?>',
    toastOn:           '<?php echo esc_js( __( 'Đã bật Chống Spam', 'whp' ) ); ?>',
    toastOff:          '<?php echo esc_js( __( 'Đã tắt Chống Spam', 'whp' ) ); ?>'
};
var nonce='<?php echo esc_js($nonce); ?>';
var ajaxUrl='<?php echo esc_js($ajax_url); ?>';
var logPage=<?php echo $lpage; ?>;

function post(action,data,cb){
    var fd=new FormData();fd.append('action',action);fd.append('nonce',nonce);
    for(var k in data)fd.append(k,typeof data[k]==='object'?JSON.stringify(data[k]):data[k]);
    fetch(ajaxUrl,{method:'POST',body:fd}).then(function(r){return r.json();}).then(cb)
    .catch(function(){showNotice('Lỗi kết nối','error');});
}

/* ── Bulk selection ── */
window.wphSf2SelAll=function(cb){
    var ctr=document.getElementById('sf2-log-container');
    if(!ctr)return;
    ctr.querySelectorAll('.sf2-row-cb').forEach(function(c){c.checked=cb.checked;});
    document.querySelectorAll('[id="sf2-sel-all"]').forEach(function(c){c.checked=cb.checked;c.indeterminate=false;});
    wphSf2UpdateBulkBar();
};
window.wphSf2DeselectAll=function(){
    var ctr=document.getElementById('sf2-log-container');
    if(ctr)ctr.querySelectorAll('.sf2-row-cb').forEach(function(c){c.checked=false;});
    document.querySelectorAll('[id="sf2-sel-all"]').forEach(function(c){c.checked=false;c.indeterminate=false;});
    wphSf2UpdateBulkBar();
};
window.wphSf2UpdateBulkBar=function(){
    var ctr=document.getElementById('sf2-log-container');
    var bar=document.getElementById('sf2-bulk-bar');
    var cntEl=document.getElementById('sf2-bulk-count');
    if(!ctr||!bar)return;
    var checked=ctr.querySelectorAll('.sf2-row-cb:checked');
    var all=ctr.querySelectorAll('.sf2-row-cb');
    var n=checked.length;
    bar.style.display=n>0?'flex':'none';
    if(cntEl)cntEl.textContent=n+' '+whpSfI18n.selected;
    document.querySelectorAll('[id="sf2-sel-all"]').forEach(function(selAll){
        if(n===0){selAll.checked=false;selAll.indeterminate=false;}
        else if(n===all.length){selAll.checked=true;selAll.indeterminate=false;}
        else{selAll.checked=false;selAll.indeterminate=true;}
    });
};
window.wphSf2BulkDelete=function(){
    var ctr=document.getElementById('sf2-log-container');
    if(!ctr)return;
    var ids=Array.from(ctr.querySelectorAll('.sf2-row-cb:checked')).map(function(c){return c.getAttribute('data-id');});
    if(!ids.length)return;
    if(!confirm(ids.length+' '+whpSfI18n.confirmBulkDelete))return;
    var fd=new FormData();
    fd.append('action','wph_sf_bulk_delete');fd.append('nonce',nonce);
    ids.forEach(function(id){fd.append('ids[]',id);});
    fetch(ajaxUrl,{method:'POST',body:fd})
        .then(function(r){return r.json();})
        .then(function(r){
            if(r.success){showNotice(r.data&&r.data.message?r.data.message:'Đã xóa','success');wphSf2LogLoad(logPage);}
            else showNotice((r.data&&r.data.message)||'Lỗi xóa','error');
        })
        .catch(function(){showNotice('Lỗi kết nối','error');});
};

function whpToast(msg,type){
    var wrap=document.getElementById('whp-toast-wrap');if(!wrap)return;
    type=type||'success';
    var icons={success:'✓',error:'✗'};
    var t=document.createElement('div');
    t.className='whp-toast wt-'+type;
    t.innerHTML='<div class="whp-toast-icon">'+(icons[type]||'✓')+'</div>'
               +'<span class="whp-toast-msg">'+msg+'</span>'
               +'<button class="whp-toast-close" onclick="this.closest(\'.whp-toast\').remove()">×</button>';
    wrap.appendChild(t);
    setTimeout(function(){t.classList.add('wt-out');setTimeout(function(){t.remove();},280);},3800);
}
function showNotice(msg,type){
    var el=document.getElementById('wph-sf2-notice');if(!el)return;
    var cfg={
        success:{bg:'#f0fdf4',c:'#166534',b:'#bbf7d0',lb:'#16a34a',icon:'✓'},
        error:  {bg:'#fef2f2',c:'#991b1b',b:'#fecaca',lb:'#dc2626',icon:'✕'},
        warning:{bg:'#fffbeb',c:'#92400e',b:'#fde68a',lb:'#d97706',icon:'⚠'},
    };
    var t=cfg[type]||cfg.success;
    el.innerHTML='<span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:'+t.lb+';color:#fff;font-size:11px;font-weight:700;flex-shrink:0;">'+t.icon+'</span><span>'+msg+'</span>';
    el.style.cssText='display:flex;align-items:center;gap:12px;color:'+t.c+';background:'+t.bg+';border:1px solid '+t.b+';border-left:5px solid '+t.lb+';border-radius:8px;padding:14px 20px;font-size:13.5px;font-weight:500;margin-bottom:20px;box-shadow:0 4px 12px rgba(0,0,0,.04);animation:wphSlideIn .35s cubic-bezier(.16,1,.3,1) forwards;';
    el.scrollIntoView({behavior:'smooth',block:'start'});
    clearTimeout(el._nt);
    el._nt=setTimeout(function(){el.style.display='none';},type==='warning'?6000:4000);
}

function h(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function fdate(s){var d=new Date(s.replace(' ','T'));return ('0'+d.getDate()).slice(-2)+'/'+(('0'+(d.getMonth()+1)).slice(-2))+'/'+d.getFullYear()+' '+('0'+d.getHours()).slice(-2)+':'+('0'+d.getMinutes()).slice(-2);}

window.wphSf2ActiveChange=function(cb,isInit){
    var lbl=document.getElementById('sf2-active-label');
    if(lbl){lbl.textContent=cb.checked?whpSfI18n.enabled:whpSfI18n.disabled;lbl.style.color=cb.checked?'#22c55e':'#ef4444';}
    var warn=document.getElementById('sf2-master-warning');
    if(warn){warn.style.display=cb.checked?'none':'flex';}
    var wrap=document.getElementById('sf2-content-wrap');
    if(wrap){wrap.classList.toggle('sf2-disabled',!cb.checked);}
    var bar=document.getElementById('sf2-save-bar');
    if(bar){bar.style.display=cb.checked?'':'none';}
    if(!isInit){
        var _isActive=cb.checked;
        wphSf2Save(function(){ whpToast(_isActive?whpSfI18n.toastOn:whpSfI18n.toastOff,'success'); },true);
    }
};
document.addEventListener('DOMContentLoaded',function(){
    var cb=document.getElementById('sf2-active');
    if(cb){wphSf2ActiveChange(cb,true);}
});

window.wphSf2Save=function(onDone,skipNotice){
    var s={
        active: document.getElementById('sf2-active').checked?'1':'0',
        monitor_mode: document.getElementById('sf2-monitor-mode').checked?'1':'0',
        dnsbl_level: document.getElementById('sf2-dnsbl-level').value,
        hide_error: document.getElementById('sf2-hide-error').checked?'1':'0',
        honeypot:{active:document.getElementById('sf2-honeypot').checked?'1':'0'},
        rate_limit:{active:document.getElementById('sf2-ratelimit').checked?'1':'0',max:parseInt(document.getElementById('sf2-rl-max').value)||3,minutes:parseInt(document.getElementById('sf2-rl-minutes').value)||5},
        proxy_vpn:{active:document.getElementById('sf2-proxy-vpn').checked?'1':'0'},
        email_block:{temp_active:document.getElementById('sf2-temp-email').checked?'1':'0'},
        code_detect:{
            active:document.getElementById('sf2-code-detect')&&document.getElementById('sf2-code-detect').checked?'1':'0',
            level:document.getElementById('sf2-code-detect-level')?document.getElementById('sf2-code-detect-level').value:'basic'
        },
    };
    post('wph_sf_save_settings',{settings:JSON.stringify(s)},function(r){
        if(r.success){
            if(!skipNotice){
                showNotice(r.data.message||'Đã lưu cài đặt thành công!','success');
                var wrap=document.querySelector('.wph-sf2-wrap');
                if(wrap){wrap.scrollIntoView({behavior:'smooth',block:'start'});}
                else{window.scrollTo({top:0,behavior:'smooth'});}
            }
        } else {
            if(!skipNotice) showNotice((r.data&&r.data.message)||'Lỗi lưu cài đặt','error');
        }
        if(typeof onDone==='function'){onDone();}
    });
};

window.wphSf2SaveLogSettings=function(){
    var r=document.getElementById('sf-log-retention');
    var m=document.getElementById('sf-log-maxlogs');
    post('wph_sf_save_log_settings',{retention:r?r.value:0,max_logs:m?m.value:0},function(res){
        if(res.success) showNotice(res.data&&res.data.message?res.data.message:'Đã lưu cài đặt','success');
        else showNotice((res.data&&res.data.message)||'Lỗi lưu cài đặt','error');
    });
};

// Custom select (DNSBL)
window.wphSf2CsToggle=function(csId){
    var cs=document.getElementById(csId);
    var trigger=cs.querySelector('.wph-sf2-cs-trigger');
    var menu=cs.querySelector('.wph-sf2-cs-menu');
    var isOpen=menu.classList.contains('open');
    // Close all others
    document.querySelectorAll('.wph-sf2-cs-menu.open').forEach(function(m){
        m.classList.remove('open');
        m.previousElementSibling&&m.previousElementSibling.classList.remove('open');
    });
    if(!isOpen){menu.classList.add('open');trigger.classList.add('open');}
};
window.wphSf2CsSelect=function(el,csId,inputId,dotId,lblId,val,label,color){
    document.getElementById(inputId).value=val;
    document.getElementById(dotId).style.background=color;
    document.getElementById(lblId).textContent=label;
    var cs=document.getElementById(csId);
    cs.querySelectorAll('.wph-sf2-cs-opt').forEach(function(o){o.classList.remove('selected');});
    if(el)el.classList.add('selected');
    cs.querySelector('.wph-sf2-cs-menu').classList.remove('open');
    cs.querySelector('.wph-sf2-cs-trigger').classList.remove('open');
};
document.addEventListener('click',function(e){
    if(!e.target.closest('.wph-sf2-cs')){
        document.querySelectorAll('.wph-sf2-cs-menu.open').forEach(function(m){
            m.classList.remove('open');
            var t=m.parentElement.querySelector('.wph-sf2-cs-trigger');if(t)t.classList.remove('open');
        });
    }
});

// Quick block tabs
window.wphSf2QbTab=function(type){
    document.querySelectorAll('.wph-sf2-qb-panel').forEach(function(p){p.style.display='none';});
    document.querySelectorAll('.wph-sf2-qb-tab').forEach(function(t){
        var active=t.dataset.tab===type;
        t.classList.toggle('active',active);
    });
    var panel=document.getElementById('wph-sf2-qb-tab-'+type);if(panel)panel.style.display='block';
};

window.wphSf2QbAdd=function(type){
    var inp=document.getElementById('sf2-qb-input-'+type);
    var val=inp.value.trim();if(!val){inp.style.borderColor='#ef4444';return;}
    // ── Format validation per type ────────────────────────────────────────────
    var err='';
    if(type==='ip'){
        var v4=/^(\d{1,3}\.){3}\d{1,3}(\/(\d|[12]\d|3[012]))?$/;
        var v6=/^[0-9a-fA-F]{0,4}(:[0-9a-fA-F]{0,4}){2,7}(\/\d{1,3})?$/;
        if(!v4.test(val)&&!v6.test(val)) err=whpSfI18n.invalidIp;
    }else if(type==='email'){
        var eRe=/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        var dRe=/^@?[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/;
        if(!eRe.test(val)&&!dRe.test(val)) err=whpSfI18n.invalidEmail;
    }else if(type==='keyword'){
        var kCode=/\{\s*"[a-zA-Z_][^"]{0,60}"\s*:/.test(val)
            ||/<\s*(script|iframe|object|embed|svg|base)[^>]*>/i.test(val)
            ||/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION)\b.{0,40}\b(FROM|INTO|WHERE|TABLE)\b/i.test(val)
            ||((val.match(/\{/g)||[]).length>=2&&(val.match(/"/g)||[]).length>=4);
        if(kCode) err=whpSfI18n.keywordCodeError;
    }
    if(err){inp.style.borderColor='#ef4444';showNotice(err,'error');return;}
    inp.style.borderColor='#e2e8f0';
    post('wph_sf_quick_block_add',{type:type,value:val},function(r){
        if(r.success){
            inp.value='';
            var list=document.getElementById('sf2-qb-list-'+type);
            var empty=document.getElementById('sf2-qb-empty-'+type);if(empty)empty.remove();
            var safeVal=r.data.value||val;
            var item=document.createElement('div');
            item.className='wph-sf2-qb-item';
            item.innerHTML='<span style="font-size:13px;color:#334155;word-break:break-all;">'+h(safeVal)+'</span>'
                +'<button class="wph-sf2-qb-del" data-type="'+h(type)+'" data-val="'+h(safeVal)+'"'
                +' onclick="wphSf2QbRemove(this.dataset.type,this.dataset.val,this.closest(\'.wph-sf2-qb-item\'))" title="Xóa">'
                +'<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6m4-6v6"/><path d="M9 6V4h6v2"/></svg></button>';
            list.insertBefore(item,list.firstChild);
            showNotice(whpSfI18n.addedItem+': '+val,'success');
        }else showNotice((r.data&&r.data.message)||'Lỗi','error');
    });
};

window.wphSf2QbRemove=function(type,val,itemEl){
    post('wph_sf_quick_block_remove',{type:type,value:val},function(r){
        if(r.success){if(itemEl)itemEl.remove();showNotice(whpSfI18n.removed,'success');}
        else showNotice((r.data&&r.data.message)||'Lỗi','error');
    });
};

// ── Real-time code detection in keyword import textarea ───────────────────────
window.wphSf2KwDetectCode=function(ta){
    var val=ta.value;
    var warn=document.getElementById('sf2-kw-code-warn');
    if(!warn)return;
    var isCode=/\{\s*"[a-zA-Z_][^"]{0,60}"\s*:/.test(val)
        ||/<\s*(script|iframe|object|embed|svg|base)[^>]*>/i.test(val)
        ||/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION)\b.{0,40}\b(FROM|INTO|WHERE|TABLE)\b/i.test(val)
        ||(val.match(/\{/g)||[]).length>=2&&(val.match(/"/g)||[]).length>=4;
    warn.style.display=isCode?'block':'none';
    ta.style.borderColor=isCode?'#f59e0b':(document.activeElement===ta?'#16a34a':'#e2e8f0');
};

// ── Keyword bulk import ───────────────────────────────────────────────────────
var _kwImportTab='text';
var _kwImportFileKeywords=[];

window.wphSf2KwOpenImport=function(){
    var overlay=document.getElementById('sf2-kw-import-overlay');
    var notice=document.getElementById('sf2-kw-import-notice');
    var ta=document.getElementById('sf2-kw-import-text');
    var fprev=document.getElementById('sf2-kw-file-preview');
    var ferr=document.getElementById('sf2-kw-file-error');
    var fi=document.getElementById('sf2-kw-file-input');
    if(overlay)overlay.style.display='flex';
    if(notice)notice.style.display='none';
    if(ta){ta.value='';ta.style.borderColor='#e2e8f0';}
    if(fprev)fprev.style.display='none';
    if(ferr)ferr.style.display='none';
    if(fi)fi.value='';
    var cw=document.getElementById('sf2-kw-code-warn');if(cw)cw.style.display='none';
    _kwImportFileKeywords=[];
    wphSf2KwTab('text');
    setTimeout(function(){var t=document.getElementById('sf2-kw-import-text');if(t)t.focus();},80);
};
window.wphSf2KwCloseImport=function(){
    var overlay=document.getElementById('sf2-kw-import-overlay');
    if(overlay)overlay.style.display='none';
};
window.wphSf2KwTab=function(tab){
    _kwImportTab=tab;
    var tabs=['text','file'];
    tabs.forEach(function(t){
        var btn=document.getElementById('sf2-kw-tab-'+t);
        var panel=document.getElementById('sf2-kw-panel-'+t);
        var active=t===tab;
        if(btn){btn.style.color=active?'#3b82f6':'#94a3b8';btn.style.borderBottomColor=active?'#3b82f6':'transparent';}
        if(panel)panel.style.display=active?'block':'none';
    });
    var notice=document.getElementById('sf2-kw-import-notice');
    if(notice)notice.style.display='none';
};

// ── File handling ─────────────────────────────────────────────────────────────
window.wphSf2KwFileDrop=function(e){
    e.preventDefault();
    var dz=document.getElementById('sf2-kw-dropzone');
    if(dz){dz.style.borderColor='#e2e8f0';dz.style.background='#fafcff';}
    var file=e.dataTransfer.files[0];
    if(file)wphSf2KwParseFile(file);
};
window.wphSf2KwFileSelect=function(inp){
    if(inp.files&&inp.files[0])wphSf2KwParseFile(inp.files[0]);
};
function wphSf2KwShowFileErr(msg){
    var el=document.getElementById('sf2-kw-file-error');
    var prev=document.getElementById('sf2-kw-file-preview');
    if(prev)prev.style.display='none';
    if(el){el.textContent=msg;el.style.display='block';}
    _kwImportFileKeywords=[];
}
function wphSf2KwShowFileOk(keywords,filename){
    var prev=document.getElementById('sf2-kw-file-preview');
    var err=document.getElementById('sf2-kw-file-error');
    if(err)err.style.display='none';
    _kwImportFileKeywords=keywords;
    if(prev){
        prev.style.display='block';
        prev.innerHTML='<strong>'+h(filename)+'</strong> — Tìm thấy <strong>'+keywords.length+'</strong> từ khóa sẵn sàng import.'
            +'<div style="margin-top:6px;max-height:60px;overflow-y:auto;font-family:monospace;font-size:11px;color:#166534;line-height:1.6;">'
            +keywords.slice(0,15).map(function(k){return h(k);}).join(' · ')+(keywords.length>15?' · ...':'')+'</div>';
    }
}
window.wphSf2KwParseFile=function(file){
    var name=file.name.toLowerCase();
    var prev=document.getElementById('sf2-kw-file-preview');
    var err=document.getElementById('sf2-kw-file-error');
    if(prev)prev.style.display='none';
    if(err)err.style.display='none';
    _kwImportFileKeywords=[];

    if(name.endsWith('.csv')){
        // CSV — read as text
        var reader=new FileReader();
        reader.onload=function(e){
            var text=e.target.result;
            var kws=wphSf2KwParseText(text);
            if(kws.length===0){wphSf2KwShowFileErr('File CSV không có từ khóa nào hợp lệ.');return;}
            wphSf2KwShowFileOk(kws,file.name);
        };
        reader.onerror=function(){wphSf2KwShowFileErr('Không đọc được file CSV.');};
        reader.readAsText(file,'UTF-8');

    }else if(name.endsWith('.xlsx')||name.endsWith('.xls')){
        // Excel — lazy-load SheetJS from CDN
        if(typeof XLSX!=='undefined'){
            wphSf2KwParseXlsx(file);
        }else{
            var script=document.createElement('script');
            script.src='https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js';
            script.onload=function(){wphSf2KwParseXlsx(file);};
            script.onerror=function(){wphSf2KwShowFileErr('Không tải được thư viện đọc Excel. Vui lòng kiểm tra kết nối mạng.');};
            document.head.appendChild(script);
        }
    }else{
        wphSf2KwShowFileErr('Định dạng không hỗ trợ. Chỉ nhận .csv, .xlsx, .xls');
    }
};
function wphSf2KwParseXlsx(file){
    var reader=new FileReader();
    reader.onload=function(e){
        try{
            var wb=XLSX.read(e.target.result,{type:'array'});
            var kws=[];
            wb.SheetNames.forEach(function(sn){
                var ws=wb.Sheets[sn];
                var data=XLSX.utils.sheet_to_json(ws,{header:1,defval:''});
                data.forEach(function(row){
                    row.forEach(function(cell){
                        var val=String(cell||'').trim();
                        if(val&&val.length>0&&val.length<200)kws.push(val);
                    });
                });
            });
            kws=kws.filter(function(v,i,a){return a.indexOf(v)===i;});
            if(kws.length===0){wphSf2KwShowFileErr('File Excel không có giá trị nào hợp lệ.');return;}
            wphSf2KwShowFileOk(kws,file.name);
        }catch(ex){
            wphSf2KwShowFileErr('Lỗi đọc file Excel: '+ex.message);
        }
    };
    reader.onerror=function(){wphSf2KwShowFileErr('Không đọc được file Excel.');};
    reader.readAsArrayBuffer(file);
}
function wphSf2KwParseText(raw){
    var lines=raw.split(/[\n,;]+/);
    return lines.map(function(l){return l.trim();}).filter(function(l){return l.length>0&&l.length<200;});
}

window.wphSf2KwDoImport=function(){
    var notice=document.getElementById('sf2-kw-import-notice');
    var btn=document.getElementById('sf2-kw-import-btn');
    var raw='';

    if(_kwImportTab==='text'){
        var ta=document.getElementById('sf2-kw-import-text');
        raw=ta?ta.value.trim():'';
        if(!raw){
            if(notice){notice.style.cssText='display:block;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;padding:9px 12px;';notice.textContent='Vui lòng nhập ít nhất 1 từ khóa.';}
            return;
        }
        // ── Code/JSON gate: block before sending AJAX ─────────────────────────
        var hasCode=/\{\s*"[a-zA-Z_][^"]{0,60}"\s*:/.test(raw)
            ||/<\s*(script|iframe|object|embed|svg|base)[^>]*>/i.test(raw)
            ||/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION)\b.{0,40}\b(FROM|INTO|WHERE|TABLE)\b/i.test(raw)
            ||((raw.match(/\{/g)||[]).length>=2&&(raw.match(/"/g)||[]).length>=4);
        if(hasCode){
            if(notice){notice.style.cssText='display:block;background:#fef2f2;color:#dc2626;border:1.5px solid #fecaca;border-radius:8px;padding:9px 12px;';notice.textContent='Phát hiện code/JSON — xóa nội dung đó trước khi import từ khóa.';}
            var cw=document.getElementById('sf2-kw-code-warn');
            if(cw){cw.style.display='block';cw.style.outline='2px solid #ef4444';setTimeout(function(){if(cw)cw.style.outline='';},1800);}
            if(ta)ta.style.borderColor='#ef4444';
            return;
        }
    }else{
        if(_kwImportFileKeywords.length===0){
            if(notice){notice.style.cssText='display:block;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;';notice.textContent='Chưa có file nào được đọc thành công.';}
            return;
        }
        raw=_kwImportFileKeywords.join('\n');
    }

    if(btn)btn.disabled=true;
    post('wph_sf_bulk_keyword_import',{keywords:raw},function(r){
        if(btn)btn.disabled=false;
        if(r.success){
            var added=r.data.added||0;
            var total=r.data.total||0;
            if(notice){
                notice.style.cssText='display:block;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;';
                notice.textContent='Đã thêm '+added+' từ khóa mới. Tổng: '+total+' từ khóa.';
            }
            var newKws=r.data.keywords||[];
            var list=document.getElementById('sf2-qb-list-keyword');
            if(list&&newKws.length>0){
                var empty=document.getElementById('sf2-qb-empty-keyword');if(empty)empty.remove();
                newKws.forEach(function(kw){
                    var item=document.createElement('div');
                    item.className='wph-sf2-qb-item';
                    item.innerHTML='<span style="font-size:13px;color:#334155;word-break:break-all;">'+h(kw)+'</span>'
                        +'<button class="wph-sf2-qb-del" data-type="keyword" data-val="'+h(kw)+'"'
                        +' onclick="wphSf2QbRemove(this.dataset.type,this.dataset.val,this.closest(\'.wph-sf2-qb-item\'))" title="Xóa">'
                        +'<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6m4-6v6"/><path d="M9 6V4h6v2"/></svg></button>';
                    list.insertBefore(item,list.firstChild);
                });
            }
            _kwImportFileKeywords=[];
            setTimeout(function(){wphSf2KwCloseImport();showNotice('Import thành công: +'+added+' từ khóa','success');},700);
        }else{
            if(notice){notice.style.cssText='display:block;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;';notice.textContent=(r.data&&r.data.message)||'Lỗi import';}
        }
    });
};
document.getElementById('sf2-kw-import-overlay')&&document.getElementById('sf2-kw-import-overlay').addEventListener('click',function(e){
    if(e.target===this)wphSf2KwCloseImport();
});

window.wphSf2QbShowAll=function(el){
    var type=el.dataset.type;
    var items;
    try{items=JSON.parse(el.dataset.items||'[]');}catch(e){return;}
    var TAB_LABELS={'ip':'IP','email':'Email','keyword':'Keyword','country':'Quốc gia'};
    var overlay=document.createElement('div');
    overlay.id='sf2-showall-overlay';
    overlay.style.cssText='position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;';
    var delBtn=function(code,val){
        return '<button class="wph-sf2-qb-del" data-type="'+h(type)+'" data-val="'+h(val)+'"'
            +' onclick="wphSf2QbRemove(this.dataset.type,this.dataset.val,this.closest(\'.wph-sf2-qb-item\'));this.closest(\'.wph-sf2-qb-item\').remove();"'
            +' onmouseover="this.style.color=\'#ef4444\'" onmouseout="this.style.color=\'#cbd5e1\'" title="Xóa">'
            +'<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
    };
    var rows='';
    items.forEach(function(item){
        var label=type==='country'?wphCountryBadgeHtml(item):'<span style="font-size:13px;font-family:monospace;color:#334155;word-break:break-all;">'+h(item)+'</span>';
        rows+='<div class="wph-sf2-qb-item" style="padding:10px 0;">'+label+delBtn(item,item)+'</div>';
    });
    overlay.innerHTML='<div style="background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.2);width:100%;max-width:420px;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;">'
        +'<div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9;flex-shrink:0;">'
        +'<h3 style="margin:0;font-size:15px;font-weight:700;color:#1e293b;">Danh sách chặn — '+(TAB_LABELS[type]||type)+' ('+items.length+')</h3>'
        +'<button onclick="document.getElementById(\'sf2-showall-overlay\').remove()" style="background:#f1f5f9;border:none;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:18px;color:#64748b;line-height:1;">&times;</button>'
        +'</div>'
        +'<div style="overflow-y:auto;padding:4px 20px 16px;flex:1;">'
        +rows
        +'</div>'
        +'<div style="padding:12px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;flex-shrink:0;">'
        +'<button onclick="document.getElementById(\'sf2-showall-overlay\').remove()" style="background:#f1f5f9;color:#475569;border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:600;cursor:pointer;">Đóng</button>'
        +'</div>'
        +'</div>';
    overlay.addEventListener('click',function(e){if(e.target===overlay)overlay.remove();});
    document.body.appendChild(overlay);
};

// ── Country autocomplete ──────────────────────────────────────────────────────
var COUNTRIES={
'AD':{vi:'Andorra',en:'Andorra'},'AE':{vi:'UAE',en:'United Arab Emirates'},'AF':{vi:'Afghanistan',en:'Afghanistan'},
'AL':{vi:'Albania',en:'Albania'},'AM':{vi:'Armenia',en:'Armenia'},'AO':{vi:'Angola',en:'Angola'},
'AR':{vi:'Argentina',en:'Argentina'},'AT':{vi:'Áo',en:'Austria'},'AU':{vi:'Úc',en:'Australia'},
'AZ':{vi:'Azerbaijan',en:'Azerbaijan'},'BA':{vi:'Bosnia',en:'Bosnia'},'BD':{vi:'Bangladesh',en:'Bangladesh'},
'BE':{vi:'Bỉ',en:'Belgium'},'BF':{vi:'Burkina Faso',en:'Burkina Faso'},'BG':{vi:'Bulgaria',en:'Bulgaria'},
'BH':{vi:'Bahrain',en:'Bahrain'},'BI':{vi:'Burundi',en:'Burundi'},'BJ':{vi:'Benin',en:'Benin'},
'BN':{vi:'Brunei',en:'Brunei'},'BO':{vi:'Bolivia',en:'Bolivia'},'BR':{vi:'Brazil',en:'Brazil'},
'BT':{vi:'Bhutan',en:'Bhutan'},'BW':{vi:'Botswana',en:'Botswana'},'BY':{vi:'Belarus',en:'Belarus'},
'BZ':{vi:'Belize',en:'Belize'},'CA':{vi:'Canada',en:'Canada'},'CD':{vi:'Congo',en:'Congo (DRC)'},
'CF':{vi:'Trung Phi',en:'Central African Republic'},'CG':{vi:'Congo',en:'Congo'},'CH':{vi:'Thụy Sĩ',en:'Switzerland'},
'CI':{vi:'Côte d\'Ivoire',en:'Ivory Coast'},'CL':{vi:'Chile',en:'Chile'},'CM':{vi:'Cameroon',en:'Cameroon'},
'CN':{vi:'Trung Quốc',en:'China'},'CO':{vi:'Colombia',en:'Colombia'},'CR':{vi:'Costa Rica',en:'Costa Rica'},
'CU':{vi:'Cuba',en:'Cuba'},'CV':{vi:'Cape Verde',en:'Cape Verde'},'CY':{vi:'Síp',en:'Cyprus'},
'CZ':{vi:'Séc',en:'Czech Republic'},'DE':{vi:'Đức',en:'Germany'},'DJ':{vi:'Djibouti',en:'Djibouti'},
'DK':{vi:'Đan Mạch',en:'Denmark'},'DZ':{vi:'Algeria',en:'Algeria'},'EC':{vi:'Ecuador',en:'Ecuador'},
'EE':{vi:'Estonia',en:'Estonia'},'EG':{vi:'Ai Cập',en:'Egypt'},'ER':{vi:'Eritrea',en:'Eritrea'},
'ES':{vi:'Tây Ban Nha',en:'Spain'},'ET':{vi:'Ethiopia',en:'Ethiopia'},'FI':{vi:'Phần Lan',en:'Finland'},
'FJ':{vi:'Fiji',en:'Fiji'},'FR':{vi:'Pháp',en:'France'},'GA':{vi:'Gabon',en:'Gabon'},
'GB':{vi:'Anh',en:'United Kingdom'},'GE':{vi:'Georgia',en:'Georgia'},'GH':{vi:'Ghana',en:'Ghana'},
'GM':{vi:'Gambia',en:'Gambia'},'GN':{vi:'Guinea',en:'Guinea'},'GQ':{vi:'Guinea Xích Đạo',en:'Equatorial Guinea'},
'GR':{vi:'Hy Lạp',en:'Greece'},'GT':{vi:'Guatemala',en:'Guatemala'},'GW':{vi:'Guinea-Bissau',en:'Guinea-Bissau'},
'GY':{vi:'Guyana',en:'Guyana'},'HN':{vi:'Honduras',en:'Honduras'},'HR':{vi:'Croatia',en:'Croatia'},
'HT':{vi:'Haiti',en:'Haiti'},'HU':{vi:'Hungary',en:'Hungary'},'ID':{vi:'Indonesia',en:'Indonesia'},
'IE':{vi:'Ireland',en:'Ireland'},'IL':{vi:'Israel',en:'Israel'},'IN':{vi:'Ấn Độ',en:'India'},
'IQ':{vi:'Iraq',en:'Iraq'},'IR':{vi:'Iran',en:'Iran'},'IS':{vi:'Iceland',en:'Iceland'},
'IT':{vi:'Ý',en:'Italy'},'JM':{vi:'Jamaica',en:'Jamaica'},'JO':{vi:'Jordan',en:'Jordan'},
'JP':{vi:'Nhật Bản',en:'Japan'},'KE':{vi:'Kenya',en:'Kenya'},'KG':{vi:'Kyrgyzstan',en:'Kyrgyzstan'},
'KH':{vi:'Campuchia',en:'Cambodia'},'KI':{vi:'Kiribati',en:'Kiribati'},'KM':{vi:'Comoros',en:'Comoros'},
'KP':{vi:'Triều Tiên',en:'North Korea'},'KR':{vi:'Hàn Quốc',en:'South Korea'},'KW':{vi:'Kuwait',en:'Kuwait'},
'KZ':{vi:'Kazakhstan',en:'Kazakhstan'},'LA':{vi:'Lào',en:'Laos'},'LB':{vi:'Lebanon',en:'Lebanon'},
'LK':{vi:'Sri Lanka',en:'Sri Lanka'},'LR':{vi:'Liberia',en:'Liberia'},'LS':{vi:'Lesotho',en:'Lesotho'},
'LT':{vi:'Lithuania',en:'Lithuania'},'LU':{vi:'Luxembourg',en:'Luxembourg'},'LV':{vi:'Latvia',en:'Latvia'},
'LY':{vi:'Libya',en:'Libya'},'MA':{vi:'Maroc',en:'Morocco'},'MD':{vi:'Moldova',en:'Moldova'},
'ME':{vi:'Montenegro',en:'Montenegro'},'MG':{vi:'Madagascar',en:'Madagascar'},'MK':{vi:'Macedonia',en:'North Macedonia'},
'ML':{vi:'Mali',en:'Mali'},'MM':{vi:'Myanmar',en:'Myanmar'},'MN':{vi:'Mông Cổ',en:'Mongolia'},
'MR':{vi:'Mauritania',en:'Mauritania'},'MT':{vi:'Malta',en:'Malta'},'MU':{vi:'Mauritius',en:'Mauritius'},
'MV':{vi:'Maldives',en:'Maldives'},'MW':{vi:'Malawi',en:'Malawi'},'MX':{vi:'Mexico',en:'Mexico'},
'MY':{vi:'Malaysia',en:'Malaysia'},'MZ':{vi:'Mozambique',en:'Mozambique'},'NA':{vi:'Namibia',en:'Namibia'},
'NE':{vi:'Niger',en:'Niger'},'NG':{vi:'Nigeria',en:'Nigeria'},'NI':{vi:'Nicaragua',en:'Nicaragua'},
'NL':{vi:'Hà Lan',en:'Netherlands'},'NO':{vi:'Na Uy',en:'Norway'},'NP':{vi:'Nepal',en:'Nepal'},
'NR':{vi:'Nauru',en:'Nauru'},'NZ':{vi:'New Zealand',en:'New Zealand'},'OM':{vi:'Oman',en:'Oman'},
'PA':{vi:'Panama',en:'Panama'},'PE':{vi:'Peru',en:'Peru'},'PG':{vi:'Papua New Guinea',en:'Papua New Guinea'},
'PH':{vi:'Philippines',en:'Philippines'},'PK':{vi:'Pakistan',en:'Pakistan'},'PL':{vi:'Ba Lan',en:'Poland'},
'PT':{vi:'Bồ Đào Nha',en:'Portugal'},'PW':{vi:'Palau',en:'Palau'},'PY':{vi:'Paraguay',en:'Paraguay'},
'QA':{vi:'Qatar',en:'Qatar'},'RO':{vi:'Romania',en:'Romania'},'RS':{vi:'Serbia',en:'Serbia'},
'RU':{vi:'Nga',en:'Russia'},'RW':{vi:'Rwanda',en:'Rwanda'},'SA':{vi:'Ả Rập Xê Út',en:'Saudi Arabia'},
'SB':{vi:'Solomon Islands',en:'Solomon Islands'},'SC':{vi:'Seychelles',en:'Seychelles'},'SD':{vi:'Sudan',en:'Sudan'},
'SE':{vi:'Thụy Điển',en:'Sweden'},'SG':{vi:'Singapore',en:'Singapore'},'SI':{vi:'Slovenia',en:'Slovenia'},
'SK':{vi:'Slovakia',en:'Slovakia'},'SL':{vi:'Sierra Leone',en:'Sierra Leone'},'SM':{vi:'San Marino',en:'San Marino'},
'SN':{vi:'Senegal',en:'Senegal'},'SO':{vi:'Somalia',en:'Somalia'},'SR':{vi:'Suriname',en:'Suriname'},
'SS':{vi:'Nam Sudan',en:'South Sudan'},'ST':{vi:'Sao Tome',en:'Sao Tome'},'SV':{vi:'El Salvador',en:'El Salvador'},
'SY':{vi:'Syria',en:'Syria'},'SZ':{vi:'Eswatini',en:'Eswatini'},'TD':{vi:'Chad',en:'Chad'},
'TG':{vi:'Togo',en:'Togo'},'TH':{vi:'Thái Lan',en:'Thailand'},'TJ':{vi:'Tajikistan',en:'Tajikistan'},
'TL':{vi:'Timor-Leste',en:'Timor-Leste'},'TM':{vi:'Turkmenistan',en:'Turkmenistan'},'TN':{vi:'Tunisia',en:'Tunisia'},
'TO':{vi:'Tonga',en:'Tonga'},'TR':{vi:'Thổ Nhĩ Kỳ',en:'Turkey'},'TT':{vi:'Trinidad',en:'Trinidad and Tobago'},
'TV':{vi:'Tuvalu',en:'Tuvalu'},'TZ':{vi:'Tanzania',en:'Tanzania'},'UA':{vi:'Ukraine',en:'Ukraine'},
'UG':{vi:'Uganda',en:'Uganda'},'US':{vi:'Mỹ',en:'United States'},'UY':{vi:'Uruguay',en:'Uruguay'},
'UZ':{vi:'Uzbekistan',en:'Uzbekistan'},'VA':{vi:'Vatican',en:'Vatican'},'VE':{vi:'Venezuela',en:'Venezuela'},
'VN':{vi:'Việt Nam',en:'Vietnam'},'VU':{vi:'Vanuatu',en:'Vanuatu'},'WS':{vi:'Samoa',en:'Samoa'},
'YE':{vi:'Yemen',en:'Yemen'},'ZA':{vi:'Nam Phi',en:'South Africa'},'ZM':{vi:'Zambia',en:'Zambia'},
'ZW':{vi:'Zimbabwe',en:'Zimbabwe'}
};

function wphCountryName(code){var c=COUNTRIES[code];return c?(c.vi+(c.vi!==c.en?' / '+c.en:'')):(code);}
function wphCountryBadgeHtml(code){
    var c=COUNTRIES[code]||{};
    var name=c.vi||(c.en||code);
    return '<span style="display:flex;align-items:center;gap:6px;">'
        +'<span style="background:#eff6ff;border-radius:4px;padding:1px 6px;font-size:11px;font-weight:700;color:#2563eb;letter-spacing:.5px;">'+h(code)+'</span>'
        +'<span style="font-size:13px;color:#334155;">'+h(name)+'</span>'
        +'</span>';
}

// Render country items on page load (initial PHP render has raw codes)
document.querySelectorAll('.wph-sf2-country-item').forEach(function(el){
    var code=el.dataset.code||el.textContent.trim();
    el.innerHTML=wphCountryBadgeHtml(code);
});

window.wphSf2CountryAc=function(q){
    var ac=document.getElementById('sf2-country-ac');
    if(!q||q.length<1){ac.style.display='none';return;}
    var qu=q.toUpperCase().trim();
    var ql=q.toLowerCase().trim();
    var matches=[];
    Object.keys(COUNTRIES).forEach(function(code){
        var c=COUNTRIES[code];
        if(code===qu||c.vi.toLowerCase().indexOf(ql)!==-1||c.en.toLowerCase().indexOf(ql)!==-1){
            matches.push({code:code,vi:c.vi,en:c.en});
        }
    });
    matches=matches.slice(0,6);
    if(!matches.length){ac.style.display='none';return;}
    ac.innerHTML='';
    matches.forEach(function(m){
        var row=document.createElement('div');
        row.style.cssText='padding:8px 13px;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:13px;transition:background .1s;';
        row.innerHTML='<span style="background:#eff6ff;border-radius:4px;padding:1px 6px;font-size:11px;font-weight:700;color:#2563eb;min-width:28px;text-align:center;">'+h(m.code)+'</span>'
            +'<span style="color:#1e293b;">'+h(m.vi)+'</span>'
            +(m.vi!==m.en?'<span style="color:#94a3b8;font-size:12px;">'+h(m.en)+'</span>':'');
        row.onmouseover=function(){this.style.background='#f1f5f9';};
        row.onmouseout=function(){this.style.background='';};
        row.onclick=function(){
            document.getElementById('sf2-qb-input-country').value=m.vi+' ('+m.code+')';
            document.getElementById('sf2-qb-country-code').value=m.code;
            ac.style.display='none';
        };
        ac.appendChild(row);
    });
    ac.style.display='block';
};

window.wphSf2QbAddCountry=function(){
    var code=document.getElementById('sf2-qb-country-code').value.trim().toUpperCase();
    var inp=document.getElementById('sf2-qb-input-country');
    if(!code){
        // Try to resolve raw input as code
        var raw=inp.value.trim().toUpperCase();
        if(raw.length===2&&COUNTRIES[raw]) code=raw;
    }
    if(!code||!COUNTRIES[code]){
        inp.style.borderColor='#ef4444';
        showNotice('Không tìm thấy quốc gia. Hãy chọn từ danh sách gợi ý.','error');
        return;
    }
    inp.style.borderColor='#e2e8f0';
    post('wph_sf_quick_block_add',{type:'country',value:code},function(r){
        if(r.success){
            inp.value='';
            document.getElementById('sf2-qb-country-code').value='';
            document.getElementById('sf2-country-ac').style.display='none';
            var list=document.getElementById('sf2-qb-list-country');
            var empty=document.getElementById('sf2-qb-empty-country');if(empty)empty.remove();
            var item=document.createElement('div');
            item.className='wph-sf2-qb-item';
            item.innerHTML=wphCountryBadgeHtml(code)
                +'<button class="wph-sf2-qb-del" data-type="country" data-val="'+h(code)+'"'
                +' onclick="wphSf2QbRemove(this.dataset.type,this.dataset.val,this.closest(\'.wph-sf2-qb-item\'))"'
                +' onmouseover="this.style.color=\'#ef4444\'" onmouseout="this.style.color=\'#cbd5e1\'" title="Xóa">'
                +'<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';
            list.insertBefore(item,list.firstChild);
            var cname=(COUNTRIES[code]||{}).vi||code;
            showNotice('Đã chặn: '+cname+' ('+code+')','success');
        }else showNotice((r.data&&r.data.message)||'Lỗi','error');
    });
};

// Reason badge style
function reasonBadge(reason){
    var r=reason.toLowerCase();
    if(r.indexOf('honeypot')!==-1) return {bg:'#fef9c3',cl:'#ca8a04'};
    if(r.indexOf('rate')!==-1)     return {bg:'#fff7ed',cl:'#ea580c'};
    if(r.indexOf('bot')!==-1||r.indexOf('user-agent')!==-1) return {bg:'#fef3c7',cl:'#d97706'};
    if(r.indexOf('email')!==-1||r.indexOf('temp')!==-1)     return {bg:'#faf5ff',cl:'#9333ea'};
    if(r.indexOf('keyword')!==-1)  return {bg:'#f0fdf4',cl:'#16a34a'};
    if(r.indexOf('proxy')!==-1||r.indexOf('vpn')!==-1) return {bg:'#fef2f2',cl:'#dc2626'};
    if(r.indexOf('ip')!==-1||r.indexOf('blacklist')!==-1||r.indexOf('dnsbl')!==-1||r.indexOf('country')!==-1) return {bg:'#eff6ff',cl:'#2563eb'};
    return {bg:'#f1f5f9',cl:'#475569'};
}
function statusBadge(status){
    var cfg={
        blocked:    {bg:'#fef2f2',cl:'#dc2626',lbl:'Đã chặn'},
        whitelisted:{bg:'#f0fdf4',cl:'#16a34a',lbl:'Đã bỏ chặn'},
        monitor:    {bg:'#fefce8',cl:'#ca8a04',lbl:'Giám sát'},
    };
    var c=cfg[status]||cfg['blocked'];
    return '<td class="wph-sf2-logtd"><span style="background:'+c.bg+';color:'+c.cl+';padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;white-space:nowrap;">'+c.lbl+'</span></td>';
}
function countryFlag(code){
    if(!code||code.length!==2)return '';
    code=code.toUpperCase();
    try{return String.fromCodePoint(0x1F1E6+code.charCodeAt(0)-65)+String.fromCodePoint(0x1F1E6+code.charCodeAt(1)-65);}
    catch(e){return '';}
}

// Log table
window.wphSf2LogLoad=function(page){
    logPage=page||1;
    var search=document.getElementById('sf2-log-search').value;
    var reason=document.getElementById('sf2-log-reason').value;
    var status=document.getElementById('sf2-log-status').value;
    var dateFrom=document.getElementById('sf2-log-date-from').value;
    var dateTo=document.getElementById('sf2-log-date-to').value;
    post('wph_sf_get_logs_ajax',{page:logPage,search:search,reason:reason,status:status,date_from:dateFrom,date_to:dateTo},function(r){
        if(!r.success)return;
        var d=r.data;var html='';
        if(!d.rows||!d.rows.length){
            html='<div style="text-align:center;padding:50px;color:#94a3b8;"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 10px;opacity:.4;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><p style="margin:0;font-size:13px;">Chưa có log spam nào.</p></div>';
        }else{
            var TH='style="padding:9px 14px;text-align:left;font-weight:700;font-size:11px;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap;"';
            var THCB='style="width:40px;padding-left:16px;padding-right:6px;background:#f8fafc;border-bottom:1px solid #e2e8f0;"';
            html='<table style="width:100%;border-collapse:collapse;font-size:12.5px;min-width:700px;"><thead><tr style="background:#f8fafc;">'
                +'<th '+THCB+'><input type="checkbox" id="sf2-sel-all" style="accent-color:#2563eb;cursor:pointer;width:14px;height:14px;" onchange="wphSf2SelAll(this)"></th>'
                +'<th '+TH+'>Thời gian</th><th '+TH+'>IP</th><th '+TH+'>Email / Người gửi</th>'
                +'<th '+TH+'>Lý do</th><th '+TH+'>Form</th><th '+TH+'>Trạng thái</th>'
                +'<th '+TH+'>Quốc gia</th><th '+TH+'>Thao tác</th></tr></thead><tbody>';
            d.rows.forEach(function(row){
                var rb=reasonBadge(row.reason);
                var cc=(row.country||'').toUpperCase();
                var dateStr=fdate(row.created_at);
                html+='<tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background=\'#fafafa\'" onmouseout="this.style.background=\'\'">'
                    +'<td class="wph-sf2-logtd" style="width:40px;padding-left:16px;"><input type="checkbox" class="sf2-row-cb" data-id="'+h(String(row.id))+'" style="accent-color:#2563eb;cursor:pointer;width:14px;height:14px;" onchange="wphSf2UpdateBulkBar()"></td>'
                    +'<td class="wph-sf2-logtd" style="color:#64748b;white-space:nowrap;font-size:12px;">'+h(dateStr)+'</td>'
                    +'<td class="wph-sf2-logtd" style="font-family:monospace;color:#334155;font-size:12px;">'+h(row.ip_address)+'</td>'
                    +'<td class="wph-sf2-logtd" style="color:#475569;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+h(row.email||'—')+'</td>'
                    +'<td class="wph-sf2-logtd"><span style="background:'+rb.bg+';color:'+rb.cl+';padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;white-space:nowrap;">'+h(row.reason)+'</span></td>'
                    +'<td class="wph-sf2-logtd" style="color:#94a3b8;font-size:12px;">'+h(row.form_plugin||'—')+'</td>'
                    +statusBadge(row.status)
                    +'<td class="wph-sf2-logtd">'+(cc?'<span style="background:#f1f5f9;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:600;color:#475569;letter-spacing:.5px;display:inline-block;">'+h(cc)+'</span>':'<span style="color:#cbd5e1;">—</span>')+'</td>'
                    +'<td class="wph-sf2-logtd"><div style="display:flex;gap:3px;"><button data-row="'+h(JSON.stringify(row))+'" onclick="wphSf2ViewDetail(this)" style="background:#f1f5f9;border:none;border-radius:6px;padding:5px 8px;cursor:pointer;color:#64748b;display:inline-flex;align-items:center;" onmouseover="this.style.background=\'#e2e8f0\'" onmouseout="this.style.background=\'#f1f5f9\'" title="Xem chi tiết"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>'
                    +(row.status==='blocked'||row.status==='monitor'?'<button data-row="'+h(JSON.stringify(row))+'" onclick="wphSf2UnblockPrompt(this)" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:5px 8px;cursor:pointer;color:#16a34a;display:inline-flex;align-items:center;font-size:11px;font-weight:700;gap:3px;white-space:nowrap;" title="Bỏ chặn"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>Bỏ chặn</button>':'')
                    +(row.status==='whitelisted'?'<button data-row="'+h(JSON.stringify(row))+'" onclick="wphSf2ReblockLog(this)" style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:5px 8px;cursor:pointer;color:#dc2626;display:inline-flex;align-items:center;font-size:11px;font-weight:700;gap:3px;white-space:nowrap;" title="Chặn lại"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>Chặn lại</button>':'')
                    +'</div></td>'
                    +'</tr>';
            });
            html+='</tbody></table>';
            // Pagination
            var perPage=25,from=(d.current_page-1)*perPage+1,to=Math.min(d.current_page*perPage,d.total);
            html+='<div style="padding:12px 18px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">'
                +'<span style="font-size:12.5px;color:#64748b;">Hiển thị '+from+' – '+to+' của '+d.total.toLocaleString()+'</span>';
            if(d.pages>1){
                var pHtml='<div style="display:flex;align-items:center;gap:3px;">';
                if(d.current_page>1) pHtml+='<a href="#" onclick="wphSf2LogLoad('+(d.current_page-1)+');return false;" style="padding:4px 8px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;text-decoration:none;color:#475569;">&#8249;</a>';
                var prev=null;
                var range=2,pts=[1];
                for(var pi=Math.max(2,d.current_page-range);pi<=Math.min(d.pages-1,d.current_page+range);pi++)pts.push(pi);
                if(d.pages>1)pts.push(d.pages);
                pts=pts.filter(function(v,i,a){return a.indexOf(v)===i;}).sort(function(a,b){return a-b;});
                pts.forEach(function(p){
                    if(prev!==null&&p-prev>1)pHtml+='<span style="padding:4px 6px;font-size:12px;color:#94a3b8;">…</span>';
                    if(p===d.current_page)pHtml+='<span style="background:#2563eb;color:#fff;padding:4px 9px;border-radius:6px;font-size:12px;font-weight:700;min-width:28px;text-align:center;display:inline-block;">'+p+'</span>';
                    else pHtml+='<a href="#" onclick="wphSf2LogLoad('+p+');return false;" style="padding:4px 9px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;text-decoration:none;color:#475569;min-width:28px;text-align:center;display:inline-block;">'+p+'</a>';
                    prev=p;
                });
                if(d.current_page<d.pages)pHtml+='<a href="#" onclick="wphSf2LogLoad('+(d.current_page+1)+');return false;" style="padding:4px 8px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;text-decoration:none;color:#475569;">&#8250;</a>';
                pHtml+='</div>';html+=pHtml;
            }
            html+='</div>';
        }
        document.getElementById('sf2-log-container').innerHTML=html;
    });
};

window.wphSf2ViewDetail=function(btn){
    var row;
    try{ row=JSON.parse(btn.getAttribute('data-row')); }catch(e){ return; }
    if(!row) return;
    var cc=(row.country||'').toUpperCase();
    var overlay=document.createElement('div');
    overlay.id='sf2-detail-overlay';
    overlay.style.cssText='position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML='<div style="background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.2);width:100%;max-width:420px;overflow:hidden;">'
        +'<div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f1f5f9;">'
        +'<h3 style="margin:0;font-size:15px;font-weight:700;color:#1e293b;">Chi tiết Log #'+h(row.id)+'</h3>'
        +'<button onclick="document.getElementById(\'sf2-detail-overlay\').remove()" style="background:#f1f5f9;border:none;border-radius:8px;padding:5px 10px;cursor:pointer;font-size:18px;color:#64748b;line-height:1;">&times;</button>'
        +'</div>'
        +'<div style="padding:18px 20px;display:flex;flex-direction:column;gap:11px;">'
        +detailRow('Thời gian', h(row.created_at||'—'))
        +detailRow('IP', '<span style="font-family:monospace;font-size:13px;color:#334155;">'+h(row.ip_address||'—')+'</span>')
        +detailRow('Email', h(row.email||'—'))
        +detailRow('Lý do', '<span style="'+reasonInline(row.reason)+'padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;">'+h(row.reason||'—')+'</span>')
        +detailRow('Trạng thái', (function(s){var cfg={blocked:{bg:'#fef2f2',cl:'#dc2626',lbl:'Đã chặn'},whitelisted:{bg:'#f0fdf4',cl:'#16a34a',lbl:'Đã bỏ chặn'},monitor:{bg:'#fefce8',cl:'#ca8a04',lbl:'Giám sát'}};var c=cfg[s]||cfg['blocked'];return '<span style="background:'+c.bg+';color:'+c.cl+';padding:2px 10px;border-radius:20px;font-size:12px;font-weight:700;">'+c.lbl+'</span>';})(row.status||'blocked'))
        +detailRow('Form', h(row.form_plugin||'—'))
        +detailRow('Quốc gia', cc?'<span style="background:#f1f5f9;border-radius:4px;padding:2px 7px;font-size:12px;font-weight:600;color:#475569;letter-spacing:.5px;">'+h(cc)+'</span>':'—')
        +'</div>'
        +'<div style="padding:12px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;">'
        +'<button onclick="document.getElementById(\'sf2-detail-overlay\').remove()" style="background:#f1f5f9;color:#475569;border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:600;cursor:pointer;">Đóng</button>'
        +'</div>'
        +'</div>';
    overlay.addEventListener('click',function(e){ if(e.target===overlay) overlay.remove(); });
    document.body.appendChild(overlay);
};
function detailRow(label,val){
    return '<div style="display:flex;gap:10px;align-items:baseline;">'
        +'<span style="min-width:90px;font-size:12px;color:#94a3b8;font-weight:600;flex-shrink:0;">'+label+'</span>'
        +'<span style="font-size:13px;color:#1e293b;">'+val+'</span>'
        +'</div>';
}
function reasonInline(reason){
    var rb=reasonBadge(reason||'');
    return 'background:'+rb.bg+';color:'+rb.cl+';';
}

window.wphSf2UnblockPrompt=function(btn){
    var row;
    try{ row=JSON.parse(btn.getAttribute('data-row')); }catch(e){ return; }
    var ip=row.ip_address||'';
    var overlay=document.createElement('div');
    overlay.style.cssText='position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;';
    overlay.innerHTML='<div style="background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.2);width:100%;max-width:400px;overflow:hidden;">'
        +'<div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;">'
        +'<div style="width:32px;height:32px;background:#f0fdf4;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg></div>'
        +'<h3 style="margin:0;font-size:15px;font-weight:700;color:#1e293b;">Bỏ chặn</h3>'
        +'</div>'
        +'<div style="padding:18px 20px;">'
        +'<p style="margin:0 0 14px;font-size:13.5px;color:#334155;">Đổi trạng thái log này sang <strong>Đã bỏ chặn</strong>.</p>'
        +(ip?'<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#475569;cursor:pointer;">'
            +'<input type="checkbox" id="sf2-unblock-wl" checked style="accent-color:#16a34a;width:15px;height:15px;">'
            +'Thêm IP <code style="background:#f1f5f9;padding:1px 6px;border-radius:4px;font-size:12px;">'+h(ip)+'</code> vào danh sách trắng (tự động cho qua)'
            +'</label>':'')
        +'</div>'
        +'<div style="padding:12px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:8px;">'
        +'<button onclick="this.closest(\'div[style*=fixed]\').remove()" style="background:#f1f5f9;color:#475569;border:none;border-radius:8px;padding:8px 18px;font-size:13px;font-weight:600;cursor:pointer;">Huỷ</button>'
        +'<button id="sf2-unblock-confirm" style="background:#16a34a;color:#fff;border:none;border-radius:8px;padding:8px 18px;font-size:13px;font-weight:700;cursor:pointer;">Bỏ chặn</button>'
        +'</div></div>';
    overlay.addEventListener('click',function(e){ if(e.target===overlay) overlay.remove(); });
    document.body.appendChild(overlay);
    document.getElementById('sf2-unblock-confirm').addEventListener('click',function(){
        var addWl=ip&&document.getElementById('sf2-unblock-wl')&&document.getElementById('sf2-unblock-wl').checked?'1':'0';
        overlay.remove();
        post('wph_sf_update_log_status',{id:row.id,new_status:'whitelisted',add_to_whitelist:addWl},function(r){
            if(r.success){ showNotice('Đã bỏ chặn'+( addWl==='1'?' + thêm IP vào danh sách trắng':'')); wphSf2LogLoad(logPage); }
            else showNotice(r.data&&r.data.message?r.data.message:'Lỗi','error');
        });
    });
};

window.wphSf2ReblockLog=function(btn){
    var row;
    try{ row=JSON.parse(btn.getAttribute('data-row')); }catch(e){ return; }
    if(!confirm('Đổi trạng thái log #'+row.id+' về Đã chặn?'))return;
    post('wph_sf_update_log_status',{id:row.id,new_status:'blocked',add_to_whitelist:'0'},function(r){
        if(r.success){ showNotice('Đã đặt lại trạng thái chặn'); wphSf2LogLoad(logPage); }
        else showNotice(r.data&&r.data.message?r.data.message:'Lỗi','error');
    });
};

window.wphSf2ClearLogs=function(){
    if(!confirm('Xóa toàn bộ nhật ký spam?'))return;
    post('wph_sf_clear_logs',{},function(r){
        if(r.success){
            showNotice(r.data.message,'success');
            document.getElementById('sf2-log-container').innerHTML='<div style="text-align:center;padding:50px;color:#94a3b8;font-size:13px;">Chưa có log spam nào.</div>';
        }else showNotice('Lỗi','error');
    });
};

window.wphSf2ExportCsv=function(){
    post('wph_sf_export_logs',{},function(r){
        if(r.success&&r.data.csv){
            var blob=new Blob(["﻿"+r.data.csv],{type:'text/csv;charset=utf-8;'});
            var url=URL.createObjectURL(blob);var a=document.createElement('a');
            a.href=url;a.download='spam-logs-'+(new Date().toISOString().slice(0,10))+'.csv';
            document.body.appendChild(a);a.click();document.body.removeChild(a);URL.revokeObjectURL(url);
        }
    });
};


})();
</script>
</div><!-- /.wph-sf2-wrap -->
    <?php
}

function wph_sf2_reason_badge_style( $reason ) {
    $r = strtolower( $reason );
    if ( strpos( $r, 'honeypot' ) !== false ) return array( '#fef9c3', '#ca8a04' );
    if ( strpos( $r, 'rate' ) !== false )      return array( '#fff7ed', '#ea580c' );
    if ( strpos( $r, 'bot' ) !== false || strpos( $r, 'user-agent' ) !== false ) return array( '#fef3c7', '#d97706' );
    if ( strpos( $r, 'email' ) !== false || strpos( $r, 'temp' ) !== false )     return array( '#faf5ff', '#9333ea' );
    if ( strpos( $r, 'keyword' ) !== false )   return array( '#f0fdf4', '#16a34a' );
    if ( strpos( $r, 'proxy' ) !== false || strpos( $r, 'vpn' ) !== false )      return array( '#fef2f2', '#dc2626' );
    if ( strpos( $r, 'ip' ) !== false || strpos( $r, 'blacklist' ) !== false || strpos( $r, 'dnsbl' ) !== false || strpos( $r, 'country' ) !== false ) return array( '#eff6ff', '#2563eb' );
    return array( '#f1f5f9', '#475569' );
}

function wph_sf2_country_flag( $code ) {
    if ( ! $code || strlen( $code ) !== 2 ) return '';
    $code = strtoupper( $code );
    $flag = mb_convert_encoding( '&#' . ( 0x1F1E6 + ord( $code[0] ) - ord('A') ) . ';', 'UTF-8', 'HTML-ENTITIES' )
          . mb_convert_encoding( '&#' . ( 0x1F1E6 + ord( $code[1] ) - ord('A') ) . ';', 'UTF-8', 'HTML-ENTITIES' );
    return $flag;
}

function wph_sf2_render_log_table( $rows, $total, $pages, $current_page ) {
    if ( empty( $rows ) ) : ?>
    <div style="text-align:center;padding:50px;color:#94a3b8;">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 10px;opacity:.4;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <p style="margin:0;font-size:13px;"><?php esc_html_e( 'Chưa có log spam nào.', 'whp' ); ?></p>
    </div>
    <?php return; endif; ?>
    <table style="width:100%;border-collapse:collapse;font-size:12.5px;min-width:700px;">
        <thead>
            <tr style="background:#f8fafc;">
                <th class="wph-sf2-logth" style="width:40px;padding-left:16px;"><input type="checkbox" id="sf2-sel-all" style="accent-color:#2563eb;cursor:pointer;width:14px;height:14px;" onchange="wphSf2SelAll(this)"></th>
                <th class="wph-sf2-logth"><?php esc_html_e( 'Thời gian', 'whp' ); ?></th>
                <th class="wph-sf2-logth">IP</th>
                <th class="wph-sf2-logth"><?php esc_html_e( 'Email / Người gửi', 'whp' ); ?></th>
                <th class="wph-sf2-logth"><?php esc_html_e( 'Lý do', 'whp' ); ?></th>
                <th class="wph-sf2-logth">Form</th>
                <th class="wph-sf2-logth"><?php esc_html_e( 'Trạng thái', 'whp' ); ?></th>
                <th class="wph-sf2-logth"><?php esc_html_e( 'Quốc gia', 'whp' ); ?></th>
                <th class="wph-sf2-logth"><?php esc_html_e( 'Thao tác', 'whp' ); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $rows as $r ) :
            list( $rbg, $rclr ) = wph_sf2_reason_badge_style( $r->reason );
            $flag = wph_sf2_country_flag( $r->country ?? '' );
            $country_code = esc_html( strtoupper( $r->country ?? '' ) );
        ?>
        <tr style="border-bottom:1px solid #f8fafc;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
            <td class="wph-sf2-logtd" style="width:40px;padding-left:16px;"><input type="checkbox" class="sf2-row-cb" data-id="<?php echo (int)$r->id; ?>" style="accent-color:#2563eb;cursor:pointer;width:14px;height:14px;" onchange="wphSf2UpdateBulkBar()"></td>
            <td class="wph-sf2-logtd" style="color:#64748b;white-space:nowrap;font-size:12px;"><?php echo date_i18n( 'd/m/Y H:i', strtotime( $r->created_at ) ); ?></td>
            <td class="wph-sf2-logtd" style="font-family:monospace;color:#334155;font-size:12px;"><?php echo esc_html( $r->ip_address ); ?></td>
            <td class="wph-sf2-logtd" style="color:#475569;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html( $r->email ?: '—' ); ?></td>
            <td class="wph-sf2-logtd">
                <span style="background:<?php echo esc_attr($rbg); ?>;color:<?php echo esc_attr($rclr); ?>;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;white-space:nowrap;"><?php echo esc_html( $r->reason ); ?></span>
            </td>
            <td class="wph-sf2-logtd" style="color:#94a3b8;font-size:12px;"><?php echo esc_html( $r->form_plugin ?: '—' ); ?></td>
            <?php
            $row_status = $r->status ?? 'blocked';
            $status_cfg = array(
                'blocked'     => array( 'bg' => '#fef2f2', 'cl' => '#dc2626', 'lbl' => __( 'Đã chặn',    'whp' ) ),
                'whitelisted' => array( 'bg' => '#f0fdf4', 'cl' => '#16a34a', 'lbl' => __( 'Đã bỏ chặn', 'whp' ) ),
                'monitor'     => array( 'bg' => '#fefce8', 'cl' => '#ca8a04', 'lbl' => __( 'Giám sát',   'whp' ) ),
            );
            $sc = $status_cfg[ $row_status ] ?? $status_cfg['blocked'];
            ?>
            <td class="wph-sf2-logtd">
                <span style="background:<?php echo esc_attr($sc['bg']); ?>;color:<?php echo esc_attr($sc['cl']); ?>;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;white-space:nowrap;"><?php echo esc_html($sc['lbl']); ?></span>
            </td>
            <td class="wph-sf2-logtd">
                <?php if ( $country_code ) : ?>
                <span style="background:#f1f5f9;border-radius:4px;padding:2px 7px;font-size:11px;font-weight:600;color:#475569;letter-spacing:.5px;display:inline-block;"><?php echo $country_code; ?></span>
                <?php else : ?>
                <span style="color:#cbd5e1;font-size:12px;">—</span>
                <?php endif; ?>
            </td>
            <td class="wph-sf2-logtd">
                <?php $row_json = esc_attr( wp_json_encode( array(
                    'id'          => (int)$r->id,
                    'ip_address'  => $r->ip_address,
                    'email'       => $r->email,
                    'reason'      => $r->reason,
                    'form_plugin' => $r->form_plugin,
                    'country'     => strtoupper( $r->country ?? '' ),
                    'status'      => $row_status,
                    'created_at'  => $r->created_at,
                ) ) ); ?>
                <div style="display:flex;gap:3px;">
                <button data-row="<?php echo $row_json; ?>" onclick="wphSf2ViewDetail(this)" style="background:#f1f5f9;border:none;border-radius:6px;padding:5px 8px;cursor:pointer;color:#64748b;display:inline-flex;align-items:center;transition:background .15s;" title="<?php esc_attr_e( 'Xem chi tiết', 'whp' ); ?>" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
                <?php if ( $row_status === 'blocked' || $row_status === 'monitor' ) : ?>
                <button data-row="<?php echo $row_json; ?>" onclick="wphSf2UnblockPrompt(this)" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:6px;padding:5px 8px;cursor:pointer;color:#16a34a;display:inline-flex;align-items:center;transition:background .15s;font-size:11px;font-weight:700;gap:3px;white-space:nowrap;" title="<?php esc_attr_e( 'Bỏ chặn IP này', 'whp' ); ?>" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                    <?php esc_html_e( 'Bỏ chặn', 'whp' ); ?>
                </button>
                <?php elseif ( $row_status === 'whitelisted' ) : ?>
                <button data-row="<?php echo $row_json; ?>" onclick="wphSf2ReblockLog(this)" style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:5px 8px;cursor:pointer;color:#dc2626;display:inline-flex;align-items:center;transition:background .15s;font-size:11px;font-weight:700;gap:3px;white-space:nowrap;" title="<?php esc_attr_e( 'Chặn lại', 'whp' ); ?>" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                    <?php esc_html_e( 'Chặn lại', 'whp' ); ?>
                </button>
                <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    $per_page = 25;
    $from = ( $current_page - 1 ) * $per_page + 1;
    $to   = min( $current_page * $per_page, $total );
    ?>
    <div style="padding:12px 18px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <span style="font-size:12.5px;color:#64748b;"><?php echo esc_html__( 'Hiển thị', 'whp' ) . ' ' . $from . ' – ' . $to . ' ' . esc_html__( 'của', 'whp' ) . ' ' . number_format( $total ); ?></span>
        <?php if ( $pages > 1 ) : ?>
        <div style="display:flex;align-items:center;gap:3px;">
            <?php if ( $current_page > 1 ) : ?>
            <a href="#" onclick="wphSf2LogLoad(<?php echo $current_page - 1; ?>);return false;" style="padding:4px 8px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;text-decoration:none;color:#475569;display:flex;align-items:center;">&#8249;</a>
            <?php endif; ?>
            <?php
            $range   = 2;
            $shown   = array();
            $pages_to_show = array_unique( array_merge(
                array( 1 ),
                range( max( 2, $current_page - $range ), min( $pages - 1, $current_page + $range ) ),
                array( $pages )
            ) );
            sort( $pages_to_show );
            $prev = null;
            foreach ( $pages_to_show as $p ) :
                if ( $prev !== null && $p - $prev > 1 ) : ?>
            <span style="padding:4px 6px;font-size:12px;color:#94a3b8;">…</span>
                <?php endif; ?>
            <?php if ( $p === $current_page ) : ?>
            <span style="background:#2563eb;color:#fff;padding:4px 9px;border-radius:6px;font-size:12px;font-weight:700;min-width:28px;text-align:center;display:inline-block;"><?php echo $p; ?></span>
            <?php else : ?>
            <a href="#" onclick="wphSf2LogLoad(<?php echo $p; ?>);return false;" style="padding:4px 9px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;text-decoration:none;color:#475569;min-width:28px;text-align:center;display:inline-block;"><?php echo $p; ?></a>
            <?php endif;
                $prev = $p;
            endforeach; ?>
            <?php if ( $current_page < $pages ) : ?>
            <a href="#" onclick="wphSf2LogLoad(<?php echo $current_page + 1; ?>);return false;" style="padding:4px 8px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;text-decoration:none;color:#475569;display:flex;align-items:center;">&#8250;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php
}
