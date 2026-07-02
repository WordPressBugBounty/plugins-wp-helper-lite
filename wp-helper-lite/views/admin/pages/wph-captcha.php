<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function wph_captcha_page_layout() {
    $nonce        = wp_create_nonce( 'wph_cap_nonce' );
    $ajax_url     = admin_url( 'admin-ajax.php' );
    $settings     = get_option( 'wph_captcha_settings', array() );
    $cap_log_settings = get_option( 'wph_captcha_log_settings', array() );
    $provider = wph_cap_active_provider();

    $master_active = ! empty( $settings['active'] ) && $settings['active'] === '1';
    $active_prov   = $settings['active_provider'] ?? 'recaptcha_v2';

    // Per-provider settings
    $rv2 = $settings['recaptcha_v2'] ?? array();
    $rv3 = $settings['recaptcha_v3'] ?? array();
    $ts  = $settings['turnstile']    ?? array();
    $hc  = $settings['hcaptcha']     ?? array();
    $apl = $settings['apply']        ?? array();

    // Detect 3rd-party form plugins installed
    $has_wpforms   = function_exists( 'wpforms' ) || class_exists( 'WPForms' );
    $has_gf        = class_exists( 'GFForms' ) || function_exists( 'gravity_form' );
    $has_ninja      = class_exists( 'Ninja_Forms' );
    $has_fluent     = defined( 'FLUENTFORM' ) || class_exists( 'FluentForm\App\Modules\Form\Form' );
    $has_elementor  = defined( 'ELEMENTOR_PRO_VERSION' );

    // Helper: render "Áp dụng cho" checkboxes for a provider suffix ('' | '-v3' | '-ts' | '-hc')
    $render_apply = function( $suffix ) use ( $apl, $has_wpforms, $has_gf, $has_ninja, $has_fluent, $has_elementor ) {
        $s   = $suffix;
        $chk = function( $key ) use ( $apl ) {
            return ! empty( $apl[ $key ] ) && $apl[ $key ] === '1';
        };

        $items = array(
            'cf7'      => array(
                'name' => 'Contact Form 7', 'desc' => __( 'Form liên hệ phổ biến', 'whp' ),
                'inst' => true, 'bg' => '#ecfeff', 'c' => '#0891b2',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
            ),
            'wpforms'  => array(
                'name' => 'WPForms', 'desc' => __( 'Form builder mạnh mẽ', 'whp' ),
                'inst' => $has_wpforms, 'bg' => '#dbeafe', 'c' => '#2563eb',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
            ),
            'gf'       => array(
                'name' => 'Gravity Forms', 'desc' => __( 'Form cao cấp doanh nghiệp', 'whp' ),
                'inst' => $has_gf, 'bg' => '#fff7ed', 'c' => '#ea580c',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
            ),
            'ninja'    => array(
                'name' => 'Ninja Forms', 'desc' => __( 'Form linh hoạt drag & drop', 'whp' ),
                'inst' => $has_ninja, 'bg' => '#fef2f2', 'c' => '#dc2626',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
            ),
            'fluent'   => array(
                'name' => 'Fluent Forms', 'desc' => __( 'Form nhanh & nhẹ', 'whp' ),
                'inst' => $has_fluent, 'bg' => '#f0fdfa', 'c' => '#0d9488',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>',
            ),
            'elementor' => array(
                'name' => 'Elementor Forms', 'desc' => __( 'Form trong page builder', 'whp' ),
                'inst' => $has_elementor, 'bg' => '#fff1f2', 'c' => '#e11d48',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
            ),
            'login'    => array(
                'name' => __( 'Đăng nhập WP', 'whp' ), 'desc' => __( 'Form login WordPress', 'whp' ),
                'inst' => true, 'bg' => '#f0fdf4', 'c' => '#16a34a',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
            ),
            'register' => array(
                'name' => __( 'Đăng ký WP', 'whp' ), 'desc' => __( 'Form tạo tài khoản mới', 'whp' ),
                'inst' => true, 'bg' => '#eff6ff', 'c' => '#4f46e5',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="16" y1="11" x2="16" y2="17"/><line x1="13" y1="14" x2="19" y2="14"/></svg>',
            ),
            'comment'  => array(
                'name' => __( 'Bình luận', 'whp' ), 'desc' => __( 'Form comment bài viết', 'whp' ),
                'inst' => true, 'bg' => '#f8fafc', 'c' => '#475569',
                'svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
            ),
        );

        echo '<div class="wph-cap2-apply-cards">';
        foreach ( $items as $key => $p ) {
            $is_checked = $chk( $key );
            $cls = 'wph-cap2-apply-card' . ( $is_checked ? ' is-active' : '' ) . ( ! $p['inst'] ? ' is-disabled' : '' );
            echo '<div class="' . esc_attr( $cls ) . '">';
            echo '<div class="wph-cap2-apply-card-icon" style="background:' . $p['bg'] . ';color:' . $p['c'] . ';">' . $p['svg'] . '</div>';
            echo '<div class="wph-cap2-apply-card-body">';
            echo '<div class="wph-cap2-apply-card-name">' . esc_html( $p['name'] ) . '</div>';
            echo '<div class="wph-cap2-apply-card-desc">' . esc_html( $p['desc'] ) . '</div>';
            echo '</div>';
            if ( $p['inst'] ) {
                echo '<label class="wph-cap2-toggle" style="flex-shrink:0;">';
                echo '<input type="checkbox" id="cap2-apply-' . esc_attr( $key . $s ) . '" data-apply="' . esc_attr( $key ) . '" ' . ( $is_checked ? 'checked' : '' ) . ' onchange="wphCap2SyncApply(this)">';
                echo '<span class="wph-cap2-toggle-slider"></span>';
                echo '</label>';
            } else {
                echo '<span class="wph-cap2-apply-card-badge">' . esc_html__( 'Chưa cài', 'whp' ) . '</span>';
            }
            echo '</div>';
        }
        echo '</div>';
    };

    // Stats & trends
    global $wpdb;
    $lt = $wpdb->prefix . 'wph_captcha_logs';

    $today_str  = current_time( 'Y-m-d' );
    $yesterday  = date( 'Y-m-d', strtotime( '-1 day' ) );
    $w7_start   = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
    $w14_start  = date( 'Y-m-d H:i:s', strtotime( '-14 days' ) );

    $total_all   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$lt}" );
    $total_fail  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$lt} WHERE status='failed'" );
    $total_pass  = $total_all - $total_fail;
    $today_cnt   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE DATE(created_at)=%s", $today_str ) );
    $yest_cnt    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE DATE(created_at)=%s", $yesterday ) );

    $fail_cur7   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE status='failed' AND created_at>=%s", $w7_start ) );
    $fail_prev7  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE status='failed' AND created_at>=%s AND created_at<%s", $w14_start, $w7_start ) );
    $pass_cur7   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE status='passed' AND created_at>=%s", $w7_start ) );
    $pass_prev7  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE status='passed' AND created_at>=%s AND created_at<%s", $w14_start, $w7_start ) );
    $total_cur7  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE created_at>=%s", $w7_start ) );
    $total_prev7 = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE created_at>=%s AND created_at<%s", $w14_start, $w7_start ) );

    $calc_trend = function( $cur, $prev ) {
        if ( $prev <= 0 && $cur > 0 ) return array( 'pct' => null, 'dir' => 'new' );
        if ( $prev <= 0 ) return array( 'pct' => null, 'dir' => 'none' );
        $pct = round( abs( $cur - $prev ) / $prev * 100 );
        return array( 'pct' => $pct, 'dir' => $cur >= $prev ? 'up' : 'down' );
    };

    $trends = array(
        $calc_trend( $total_cur7,  $total_prev7 ),
        $calc_trend( $fail_cur7,   $fail_prev7 ),
        $calc_trend( $pass_cur7,   $pass_prev7 ),
        $calc_trend( $today_cnt,   $yest_cnt ),
    );

    // Mini log for sidebar (latest 5 rows)
    $mini_logs = $wpdb->get_results( "SELECT * FROM {$lt} ORDER BY created_at DESC LIMIT 5" );

    $prov_names = array(
        'recaptcha_v2' => 'reCAPTCHA v2',
        'recaptcha_v3' => 'reCAPTCHA v3',
        'turnstile'    => 'Turnstile',
        'hcaptcha'     => 'hCaptcha',
    );
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
/* ── Wrapper ── */
.wph-cap2-wrap{max-width:1200px;margin:0 auto;padding:0 0 40px;font-family:inherit;}

/* ── Header hero ── */
.wph-cap2-header{position:relative;background:linear-gradient(100deg,#ffffff 0%,#ecfeff 45%,#cffafe 100%);border-radius:20px;box-shadow:0 4px 24px rgba(8,145,178,.12),0 0 0 1px #a5f3fc;margin-bottom:20px;overflow:hidden;min-height:168px;display:flex;align-items:stretch;}
.wph-cap2-header-left{position:relative;z-index:2;padding:32px 36px;display:flex;flex-direction:column;justify-content:center;gap:14px;max-width:500px;flex-shrink:0;}
.wph-cap2-header-title-row{display:flex;align-items:center;gap:14px;}
.wph-cap2-header-icon-box{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#0891b2,#0e7490);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(8,145,178,.35);}
.wph-cap2-header-right{position:absolute;inset:0 0 0 38%;overflow:hidden;pointer-events:none;}

/* ── Master toggle ── */
.wph-cap2-master-switch{position:relative;display:inline-block;width:52px;height:28px;}
.wph-cap2-master-switch input{opacity:0;width:0;height:0;}
.wph-cap2-master-slider{position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:28px;transition:.3s ease;}
.wph-cap2-master-slider:before{position:absolute;content:"";height:20px;width:20px;left:4px;bottom:4px;background:#fff;border-radius:50%;transition:.3s ease;box-shadow:0 1px 4px rgba(15,23,42,.15);}
.wph-cap2-master-switch input:checked+.wph-cap2-master-slider{background:#22c55e;}
.wph-cap2-master-switch input:checked+.wph-cap2-master-slider:before{transform:translateX(24px);}

/* ── Stats ── */
.wph-cap2-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
@media(max-width:900px){.wph-cap2-stats{grid-template-columns:repeat(2,1fr);}.wph-cap2-body{grid-template-columns:1fr!important;}.wph-cap2-header-right{display:none;}}
@media(max-width:600px){.wph-cap2-stats{grid-template-columns:repeat(2,1fr);}}
.wph-cap2-stat-card{background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px 20px 14px;box-shadow:0 1px 4px rgba(0,0,0,.05);display:flex;flex-direction:row;align-items:flex-start;gap:14px;transition:box-shadow .15s;}
.wph-cap2-stat-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.09);}
.wph-cap2-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;}
.wph-cap2-stat-body{flex:1;min-width:0;}
.wph-cap2-stat-num{font-size:30px;font-weight:800;line-height:1;letter-spacing:-.5px;}
.wph-cap2-stat-lbl{font-size:12.5px;color:#64748b;margin-top:4px;font-weight:500;}
.wph-cap2-stat-trend{display:flex;align-items:center;gap:4px;margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9;font-size:11.5px;font-weight:600;}
.wph-cap2-trend-up{color:#16a34a;}
.wph-cap2-trend-down{color:#dc2626;}
.wph-cap2-trend-none{color:#94a3b8;}
.wph-cap2-trend-cmp{font-weight:400;color:#94a3b8;margin-left:2px;}

/* ── Body 2-col ── */
.wph-cap2-body{display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;}
.wph-cap2-sidebar{position:sticky;top:32px;}

/* ── Card base ── */
.wph-cap2-card{background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px;}
.wph-cap2-card:last-child{margin-bottom:0;}
.wph-cap2-card-head{padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:10px;}
.wph-cap2-card-head h3{margin:0;font-size:15px;font-weight:700;color:#1e293b;}
.wph-cap2-card-body{padding:20px;}

/* ── Provider tabs ── */
.wph-cap2-prov-tabs{display:flex;gap:0;border-bottom:2px solid #f1f5f9;overflow-x:auto;padding:0 20px;}
.wph-cap2-prov-tab{display:inline-flex;align-items:center;gap:7px;padding:12px 16px;font-size:13px;font-weight:600;color:#64748b;background:none;border:none;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;transition:color .15s,border-color .15s;white-space:nowrap;flex-shrink:0;}
.wph-cap2-prov-tab:hover{color:#0891b2;}
.wph-cap2-prov-tab.active{color:#0891b2;border-bottom-color:#0891b2;}
.wph-cap2-prov-badge{background:#cffafe;color:#0891b2;padding:2px 7px;border-radius:20px;font-size:10px;font-weight:700;letter-spacing:.3px;}
.wph-cap2-prov-badge.on{background:#0891b2;color:#fff;}

/* ── Panel icon circles ── */
.wph-cap2-prov-icon{width:22px;height:22px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0;}

/* ── Form fields ── */
.wph-cap2-field{margin-bottom:16px;}
.wph-cap2-field label{display:block;font-size:11.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;}
.wph-cap2-field input[type=text],.wph-cap2-field input[type=password],.wph-cap2-field select{width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:9px 13px;font-size:13px;font-family:inherit;color:#1e293b;background:#fff;transition:border-color .15s,box-shadow .15s;box-sizing:border-box;}
.wph-cap2-save-btn,.wph-cap2-prov-tab,.wph-cap2-modal-close{font-family:inherit;}
.wph-cap2-field input[type=text]:focus,.wph-cap2-field input[type=password]:focus,.wph-cap2-field select:focus{outline:none;border-color:#0891b2;box-shadow:0 0 0 3px rgba(8,145,178,.1);}
.wph-cap2-field-hint{font-size:11.5px;color:#94a3b8;margin-top:4px;line-height:1.4;}
.wph-cap2-field-hint a{color:#0891b2;text-decoration:none;}
.wph-cap2-field-hint a:hover{text-decoration:underline;}

/* ── Row toggle (small) ── */
.wph-cap2-toggle{position:relative;width:40px;height:22px;display:inline-block;vertical-align:middle;}
.wph-cap2-toggle input{opacity:0;width:0;height:0;}
.wph-cap2-toggle-slider{position:absolute;top:0;left:0;right:0;bottom:0;background:#e2e8f0;border-radius:22px;cursor:pointer;transition:.2s;}
.wph-cap2-toggle input:checked+.wph-cap2-toggle-slider{background:#22c55e;}
.wph-cap2-toggle-slider::before{position:absolute;content:'';height:16px;width:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
.wph-cap2-toggle input:checked+.wph-cap2-toggle-slider::before{transform:translateX(18px);}

/* ── Apply-to cards ── */
.wph-cap2-apply-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:8px;}
.wph-cap2-apply-card{border:1.5px solid #e2e8f0;border-radius:12px;padding:11px 12px;display:flex;align-items:center;gap:10px;background:#fff;transition:border-color .15s,background .15s;}
.wph-cap2-apply-card:not(.is-disabled):hover{border-color:#a5f3fc;background:#f0fdff;}
.wph-cap2-apply-card.is-active{border-color:#0891b2!important;background:#ecfeff!important;}
.wph-cap2-apply-card.is-disabled{opacity:.5;pointer-events:none;}
.wph-cap2-apply-card-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.wph-cap2-apply-card-body{flex:1;min-width:0;}
.wph-cap2-apply-card-name{font-size:12.5px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.wph-cap2-apply-card-desc{font-size:10.5px;color:#94a3b8;margin-top:1px;}
.wph-cap2-apply-card-badge{font-size:10px;background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:5px;font-weight:600;flex-shrink:0;white-space:nowrap;}
/* ── Display settings cards ── */
.wph-cap2-display-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:8px;}
.wph-cap2-display-card{border:1.5px solid #e2e8f0;border-radius:12px;padding:14px 16px;background:#fff;display:flex;flex-direction:column;gap:10px;}
.wph-cap2-display-hd{display:flex;align-items:center;gap:8px;}
.wph-cap2-display-hd-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.wph-cap2-display-hd-label{font-size:11.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.4px;}
/* ── Segmented control ── */
.wph-cap2-seg{display:flex;border:1.5px solid #e2e8f0;border-radius:9px;overflow:hidden;}
.wph-cap2-seg-opt{flex:1;}
.wph-cap2-seg-opt input[type=radio]{display:none;}
.wph-cap2-seg-opt label{display:flex;align-items:center;justify-content:center;gap:5px;padding:8px 4px;font-size:12px;font-weight:600;color:#64748b;cursor:pointer;transition:background .15s,color .15s;width:100%;box-sizing:border-box;}
.wph-cap2-seg-opt input:checked+label{background:#0891b2;color:#fff;}
.wph-cap2-seg-opt:not(:first-child){border-left:1.5px solid #e2e8f0;}

/* ── Save bar ── */
.wph-cap2-save-bar{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:20px;box-shadow:0 4px 20px -2px rgba(15,23,42,.03);}
.wph-cap2-save-bar-hint{font-size:12.5px;color:#64748b;display:flex;align-items:center;gap:6px;}
.wph-cap2-save-btn{background:linear-gradient(135deg,#3858e9 0%,#2563eb 100%);color:#fff;border:none;border-radius:9px;padding:11px 32px;font-size:13.5px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(56,88,233,.35);transition:all .2s;letter-spacing:.2px;flex-shrink:0;}
.wph-cap2-save-btn:hover{background:linear-gradient(135deg,#2563eb,#1d4ed8);transform:translateY(-1px);box-shadow:0 6px 20px rgba(56,88,233,.4);}

/* ── Guide steps ── */
.wph-cap2-guide-step{display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid #f8fafc;}
.wph-cap2-guide-step:last-child{border-bottom:none;}
.wph-cap2-guide-num{width:24px;height:24px;border-radius:50%;background:#ecfeff;color:#0891b2;font-size:12px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;}
.wph-cap2-guide-text{font-size:13px;color:#334155;line-height:1.5;}
.wph-cap2-guide-text a{color:#0891b2;text-decoration:none;}
.wph-cap2-guide-text a:hover{text-decoration:underline;}

/* ── Mini log table ── */
.wph-cap2-mini-table{width:100%;border-collapse:collapse;font-size:12px;}
.wph-cap2-mini-table th{background:#f8fafc;padding:7px 12px;text-align:left;font-weight:700;font-size:10.5px;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap;}
.wph-cap2-mini-table td{padding:8px 12px;border-bottom:1px solid #f8fafc;vertical-align:middle;color:#334155;}
.wph-cap2-mini-table tr:last-child td{border-bottom:none;}
.wph-cap2-status-badge{padding:2px 8px;border-radius:20px;font-size:10.5px;font-weight:700;display:inline-block;}

/* ── Log modal ── */
.wph-cap2-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;}
.wph-cap2-modal{background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.22);width:100%;max-width:760px;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;}
.wph-cap2-modal-head{display:flex;align-items:center;justify-content:space-between;padding:16px 22px;border-bottom:1px solid #f1f5f9;flex-shrink:0;}
.wph-cap2-modal-head h3{margin:0;font-size:16px;font-weight:700;color:#1e293b;}
.wph-cap2-modal-close{background:#f1f5f9;border:none;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:20px;color:#64748b;line-height:1;display:flex;align-items:center;justify-content:center;transition:background .15s;}
.wph-cap2-modal-close:hover{background:#e2e8f0;}
.wph-cap2-modal-body{overflow-y:auto;flex:1;}
.wph-cap2-modal-foot{padding:12px 22px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;}

/* ── Full log table ── */
.wph-cap2-log-table{width:100%;border-collapse:collapse;font-size:12.5px;}
.wph-cap2-log-table th{background:#f8fafc;padding:10px 16px;text-align:left;font-weight:700;font-size:11px;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap;position:sticky;top:0;}
.wph-cap2-log-table td{padding:10px 16px;border-bottom:1px solid #f8fafc;vertical-align:middle;}
.wph-cap2-log-table tr:last-child td{border-bottom:none;}

/* ── Pagination ── */
.wph-cap2-pager{display:flex;gap:4px;align-items:center;}
.wph-cap2-pager-btn{padding:4px 10px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;cursor:pointer;background:#fff;color:#475569;transition:all .12s;}
.wph-cap2-pager-btn:hover{background:#f1f5f9;}
.wph-cap2-pager-btn.active{background:#0891b2;color:#fff;border-color:#0891b2;}

/* ── Notice ── */
#wph-cap2-notice{display:none;}
@keyframes wphSlideIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}

/* ── Provider panel ── */
.wph-cap2-prov-panel{padding:20px 22px;display:none;}
.wph-cap2-prov-panel.active{display:block;}
.wph-cap2-prov-panel-head{display:flex;align-items:center;gap:12px;margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid #f8fafc;}

/* ── Section divider ── */
.wph-cap2-section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin:18px 0 10px;}

/* ── Warning banner ── */
.wph-cap2-warn{display:flex;align-items:center;gap:10px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#9a3412;font-weight:600;}

/* ── Field validation error ── */
.wph-cap2-field-err input{border-color:#dc2626!important;box-shadow:0 0 0 3px rgba(220,38,38,.1)!important;}
.wph-cap2-field-err-msg{font-size:11.5px;color:#dc2626;font-weight:600;margin-top:4px;display:flex;align-items:center;gap:4px;}
/* ── Eye-toggle secret key ── */
.wph-cap2-pw-wrap{position:relative;display:flex;align-items:center;}
.wph-cap2-pw-wrap input{flex:1;padding-right:38px!important;}
.wph-cap2-pw-eye{position:absolute;right:10px;background:none;border:none;cursor:pointer;padding:0;display:flex;align-items:center;color:#94a3b8;transition:color .15s;}
.wph-cap2-pw-eye:hover{color:#0891b2;}
#cap2-content-wrap.cap2-disabled{opacity:0.4;pointer-events:none;user-select:none;transition:opacity 0.3s;}
</style>

<div id="whp-toast-wrap"></div>
<div class="wph-cap2-wrap">

<div id="wph-cap2-notice"></div>

<?php if ( ! $master_active ) : ?>
<div id="cap2-master-warning" class="wph-cap2-warn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;color:#f97316;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <?php esc_html_e( 'CAPTCHA đang', 'whp' ); ?> <strong style="color:#dc2626;"><?php esc_html_e( 'TẮT', 'whp' ); ?></strong> — <?php esc_html_e( 'Bảo vệ biểu mẫu bị vô hiệu hóa. Bật công tắc bên trên để kích hoạt.', 'whp' ); ?>
</div>
<?php else : ?>
<div id="cap2-master-warning" style="display:none;" class="wph-cap2-warn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;color:#f97316;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <?php esc_html_e( 'CAPTCHA đang', 'whp' ); ?> <strong style="color:#dc2626;"><?php esc_html_e( 'TẮT', 'whp' ); ?></strong> — <?php esc_html_e( 'Bảo vệ biểu mẫu bị vô hiệu hóa. Bật công tắc bên trên để kích hoạt.', 'whp' ); ?>
</div>
<?php endif; ?>

<!-- ── HEADER HERO ─────────────────────────────────────────────────────────── -->
<div class="wph-cap2-header">
    <div class="wph-cap2-header-left">
        <div class="wph-cap2-header-title-row">
            <div class="wph-cap2-header-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#0c4a6e;margin:0;letter-spacing:-.3px;">CAPTCHA</h1>
        </div>
        <p style="margin:0;font-size:13.5px;color:#0e7490;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e( 'Bảo vệ biểu mẫu khỏi bot và spam tự động.', 'whp' ); ?> <?php echo $master_active ? esc_html__( 'Hệ thống đang bảo vệ biểu mẫu của bạn.', 'whp' ) : esc_html__( 'Bật công tắc để kích hoạt.', 'whp' ); ?></p>
        <div style="display:inline-flex;align-items:center;gap:10px;padding-left:58px;">
            <label class="wph-cap2-master-switch">
                <input type="checkbox" id="cap2-active" <?php echo $master_active ? 'checked' : ''; ?> onchange="wphCap2ActiveChange(this)">
                <span class="wph-cap2-master-slider"></span>
            </label>
            <span id="cap2-active-label" style="font-size:13px;font-weight:700;color:<?php echo $master_active ? '#22c55e' : '#ef4444'; ?>;"><?php echo $master_active ? esc_html__( 'Đang bật', 'whp' ) : esc_html__( 'Đang tắt', 'whp' ); ?></span>
        </div>
    </div>
    <!-- Decorative illustration -->
    <div class="wph-cap2-header-right">
        <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
            <defs>
                <linearGradient id="cap2_hbg" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#ecfeff" stop-opacity="0"/>
                    <stop offset="30%" stop-color="#cffafe" stop-opacity=".7"/>
                    <stop offset="100%" stop-color="#a5f3fc" stop-opacity="1"/>
                </linearGradient>
                <filter id="cap2_shadow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(8,145,178,0.18)"/>
                </filter>
                <filter id="cap2_shadowSm" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(8,145,178,0.12)"/>
                </filter>
            </defs>
            <rect width="680" height="168" fill="url(#cap2_hbg)"/>
            <!-- bg circles -->
            <circle cx="580" cy="15" r="65" fill="#0891b2" fill-opacity=".05"/>
            <circle cx="645" cy="148" r="45" fill="#0e7490" fill-opacity=".06"/>
            <circle cx="310" cy="84" r="90" fill="#67e8f9" fill-opacity=".08"/>

            <!-- Main shield -->
            <g filter="url(#cap2_shadow)">
                <path d="M415 26L457 42L457 88Q457 116 415 130Q373 116 373 88L373 42Z" fill="#fff" stroke="#67e8f9" stroke-width="1.5"/>
                <path d="M415 28L453 43L453 88Q453 113 415 126Q377 113 377 88L377 43Z" fill="#ecfeff"/>
                <!-- Checkmark -->
                <polyline points="397,82 411,96 433,68" stroke="#0891b2" stroke-width="4.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </g>

            <!-- CAPTCHA grid (image selection grid) -->
            <g filter="url(#cap2_shadowSm)" transform="translate(558,34)">
                <rect width="76" height="76" rx="10" fill="#fff" stroke="#a5f3fc" stroke-width="1.2"/>
                <!-- 3x3 image cells — highlighted cells in cyan -->
                <?php
                $dots = array(
                    array(18,18), array(38,18), array(58,18),
                    array(18,38), array(38,38), array(58,38),
                    array(18,58), array(38,58), array(58,58),
                );
                foreach ( $dots as $i => $d ) :
                    $fill = in_array($i, array(1,3,5,7)) ? '#0891b2' : '#cffafe';
                ?>
                <rect x="<?php echo $d[0]-9; ?>" y="<?php echo $d[1]-9; ?>" width="16" height="16" rx="4" fill="<?php echo $fill; ?>" fill-opacity="<?php echo $fill === '#0891b2' ? '.7' : '1'; ?>"/>
                <?php endforeach; ?>
                <!-- "I'm not a robot" check row -->
                <rect x="6" y="64" width="12" height="12" rx="3" fill="#0891b2" fill-opacity=".15" stroke="#0891b2" stroke-width="1"/>
                <polyline points="8,70 11,73 16,67" stroke="#0891b2" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </g>

            <!-- Robot bot blocked -->
            <g filter="url(#cap2_shadowSm)" transform="translate(310,48)">
                <rect width="56" height="50" rx="10" fill="#0c4a6e"/>
                <circle cx="18" cy="18" r="6" fill="#67e8f9"/>
                <circle cx="38" cy="18" r="6" fill="#67e8f9"/>
                <rect x="16" y="31" width="24" height="5" rx="2.5" fill="#0e7490"/>
                <!-- antenna -->
                <rect x="25" y="-8" width="5" height="13" rx="2.5" fill="#0e7490"/>
                <circle cx="27" cy="-9" r="4" fill="#0891b2" fill-opacity=".9"/>
                <!-- legs -->
                <rect x="16" y="50" width="8" height="9" rx="2.5" fill="#0e7490"/>
                <rect x="32" y="50" width="8" height="9" rx="2.5" fill="#0e7490"/>
                <!-- X overlay = blocked -->
                <line x1="5" y1="5" x2="51" y2="53" stroke="#ef4444" stroke-width="3.5" stroke-linecap="round"/>
                <line x1="51" y1="5" x2="5" y2="53" stroke="#ef4444" stroke-width="3.5" stroke-linecap="round"/>
            </g>

            <!-- Lock icon -->
            <g filter="url(#cap2_shadowSm)" transform="translate(348,108)">
                <rect x="0" y="11" width="30" height="22" rx="5" fill="#fff" stroke="#a5f3fc" stroke-width="1.2"/>
                <path d="M5 11V8a10 10 0 0 1 20 0v3" stroke="#0891b2" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <circle cx="15" cy="22" r="3.5" fill="#0891b2"/>
                <rect x="13.5" y="22" width="3" height="6" rx="1.5" fill="#0891b2"/>
            </g>

            <!-- Verified user badge -->
            <g filter="url(#cap2_shadowSm)" transform="translate(484,18) rotate(-6)">
                <path d="M28 4L52 14L52 36Q52 54 28 64Q4 54 4 36L4 14Z" fill="#ecfeff" stroke="#67e8f9" stroke-width="1.2"/>
                <polyline points="17,36 24,43 39,26" stroke="#0891b2" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </g>

            <!-- Fingerprint arcs (human verification symbol) -->
            <g transform="translate(470,92)" opacity=".55">
                <path d="M0 28 Q0 0 22 0 Q44 0 44 28" stroke="#0891b2" stroke-width="2" fill="none" stroke-linecap="round"/>
                <path d="M6 28 Q6 6 22 6 Q38 6 38 28" stroke="#0891b2" stroke-width="2" fill="none" stroke-linecap="round"/>
                <path d="M12 28 Q12 12 22 12 Q32 12 32 28" stroke="#0891b2" stroke-width="2" fill="none" stroke-linecap="round"/>
            </g>

            <!-- Connecting lines -->
            <line x1="366" y1="84" x2="373" y2="84" stroke="#67e8f9" stroke-width="1.5" stroke-dasharray="4 3"/>
            <line x1="458" y1="84" x2="558" y2="72" stroke="#67e8f9" stroke-width="1.5" stroke-dasharray="4 3"/>
            <circle cx="366" cy="84" r="3" fill="#0891b2" fill-opacity=".4"/>
            <circle cx="458" cy="84" r="3" fill="#0891b2" fill-opacity=".4"/>

            <!-- Floating dots -->
            <circle cx="350" cy="26" r="4" fill="#0891b2" fill-opacity=".18"/>
            <circle cx="362" cy="138" r="3" fill="#0e7490" fill-opacity=".16"/>
            <circle cx="546" cy="28" r="5" fill="#67e8f9" fill-opacity=".3"/>
            <circle cx="430" cy="152" r="3.5" fill="#0891b2" fill-opacity=".14"/>
            <circle cx="504" cy="150" r="2.5" fill="#0e7490" fill-opacity=".12"/>
        </svg>
    </div>
</div>

<!-- ── STATS ─────────────────────────────────────────────────────────────── -->
<div class="wph-cap2-stats">
<?php
$scards = array(
    array(
        'val'   => $total_all,
        'lbl'   => __( 'Tổng xác thực', 'whp' ),
        'cmp'   => __( 'so với 7 ngày trước', 'whp' ),
        'ic'    => '#0891b2',
        'bg'    => '#ecfeff',
        'path'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'trend' => $trends[0],
    ),
    array(
        'val'   => $total_fail,
        'lbl'   => __( 'Thất bại', 'whp' ),
        'cmp'   => __( 'so với 7 ngày trước', 'whp' ),
        'ic'    => '#dc2626',
        'bg'    => '#fef2f2',
        'path'  => '<circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>',
        'trend' => $trends[1],
    ),
    array(
        'val'   => $total_pass,
        'lbl'   => __( 'Thành công', 'whp' ),
        'cmp'   => __( 'so với 7 ngày trước', 'whp' ),
        'ic'    => '#16a34a',
        'bg'    => '#f0fdf4',
        'path'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
        'trend' => $trends[2],
    ),
    array(
        'val'   => $today_cnt,
        'lbl'   => __( 'Hôm nay', 'whp' ),
        'cmp'   => __( 'so với hôm qua', 'whp' ),
        'ic'    => '#d97706',
        'bg'    => '#fffbeb',
        'path'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'trend' => $trends[3],
    ),
);
foreach ( $scards as $c ) :
    $t   = $c['trend'];
    $dir = $t['dir'];
    $pct = $t['pct'];
    if ( $dir === 'new' ) {
        $trend_cls  = 'wph-cap2-trend-up';
        $trend_html = '<span>✦ ' . esc_html__( 'Mới', 'whp' ) . '</span><span class="wph-cap2-trend-cmp">' . esc_html( $c['cmp'] ) . '</span>';
    } elseif ( $pct === null ) {
        $trend_cls  = 'wph-cap2-trend-none';
        $trend_html = '<span>—</span><span class="wph-cap2-trend-cmp">' . esc_html( $c['cmp'] ) . '</span>';
    } else {
        $trend_cls  = $dir === 'up' ? 'wph-cap2-trend-up' : 'wph-cap2-trend-down';
        $arrow      = $dir === 'up' ? '↑' : '↓';
        $trend_html = '<span>' . $arrow . ' ' . $pct . '%</span><span class="wph-cap2-trend-cmp">' . esc_html( $c['cmp'] ) . '</span>';
    }
?>
<div class="wph-cap2-stat-card">
    <div class="wph-cap2-stat-icon" style="background:<?php echo esc_attr( $c['bg'] ); ?>;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $c['ic'] ); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $c['path']; ?></svg>
    </div>
    <div class="wph-cap2-stat-body">
        <div class="wph-cap2-stat-num" style="color:<?php echo esc_attr( $c['ic'] ); ?>;"><?php echo esc_html( $c['val'] ); ?></div>
        <div class="wph-cap2-stat-lbl"><?php echo esc_html( $c['lbl'] ); ?></div>
        <div class="wph-cap2-stat-trend <?php echo esc_attr( $trend_cls ); ?>"><?php echo $trend_html; ?></div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- ── BODY 2-COL ─────────────────────────────────────────────────────────── -->
<div id="cap2-content-wrap" class="wph-cap2-body">

    <!-- ── LEFT: CONFIG ── -->
    <div>
        <div class="wph-cap2-card">
            <!-- Card header -->
            <div class="wph-cap2-card-head">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:32px;height:32px;background:#ecfeff;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <h3><?php esc_html_e( 'Cấu hình CAPTCHA', 'whp' ); ?></h3>
                </div>
            </div>

            <!-- Provider tabs -->
            <div class="wph-cap2-prov-tabs">
                <?php
                $prov_tab_defs = array(
                    'recaptcha_v2' => array(
                        'label'  => 'reCAPTCHA v2',
                        'ibg'    => '#eff6ff',
                        'ic'     => '#2563eb',
                        'badge'  => 'v2',
                    ),
                    'recaptcha_v3' => array(
                        'label'  => 'reCAPTCHA v3',
                        'ibg'    => '#eff6ff',
                        'ic'     => '#2563eb',
                        'badge'  => 'v3',
                    ),
                    'turnstile'    => array(
                        'label'  => 'Turnstile',
                        'ibg'    => '#fff7ed',
                        'ic'     => '#ea580c',
                        'badge'  => 'CF',
                    ),
                    'hcaptcha'     => array(
                        'label'  => 'hCaptcha',
                        'ibg'    => '#f0fdf4',
                        'ic'     => '#16a34a',
                        'badge'  => 'hC',
                    ),
                );
                foreach ( $prov_tab_defs as $pkey => $pdef ) :
                    $is_active_tab = ( $active_prov === $pkey );
                    $is_using      = ( $provider === $pkey );
                ?>
                <button class="wph-cap2-prov-tab <?php echo $is_active_tab ? 'active' : ''; ?>"
                        onclick="wphCap2Tab('<?php echo esc_js( $pkey ); ?>')"
                        data-prov="<?php echo esc_attr( $pkey ); ?>">
                    <span class="wph-cap2-prov-icon" style="background:<?php echo esc_attr( $pdef['ibg'] ); ?>;color:<?php echo esc_attr( $pdef['ic'] ); ?>;">
                        <?php echo esc_html( $pdef['badge'] ); ?>
                    </span>
                    <?php echo esc_html( $pdef['label'] ); ?>
                    <?php if ( $is_using ) : ?>
                    <span class="wph-cap2-prov-badge on"><?php esc_html_e( 'Đang dùng', 'whp' ); ?></span>
                    <?php endif; ?>
                </button>
                <?php endforeach; ?>
            </div>

            <!-- Hidden field: active_provider -->
            <input type="hidden" id="cap2-active-provider" value="<?php echo esc_attr( $active_prov ); ?>">

            <!-- ── reCAPTCHA v2 panel ── -->
            <div class="wph-cap2-prov-panel <?php echo $active_prov === 'recaptcha_v2' ? 'active' : ''; ?>" id="cap2-panel-recaptcha_v2">
                <div class="wph-cap2-prov-panel-head">
                    <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:14px;font-weight:800;color:#2563eb;">G</span>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1e293b;">reCAPTCHA v2</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;"><?php esc_html_e( 'Checkbox "Tôi không phải người máy" — Google', 'whp' ); ?></div>
                    </div>
                    <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
                        <span style="font-size:12px;font-weight:700;color:#475569;"><?php esc_html_e( 'Bật', 'whp' ); ?></span>
                        <label class="wph-cap2-toggle">
                            <input type="checkbox" id="cap2-rv2-enabled" <?php checked( ! empty( $rv2['enabled'] ) && $rv2['enabled'] === '1' ); ?>>
                            <span class="wph-cap2-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="wph-cap2-field">
                    <label>Site Key (Public Key)</label>
                    <input type="text" id="cap2-rv2-site-key" value="<?php echo esc_attr( $rv2['site_key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Nhập Site Key...', 'whp' ); ?>">
                    <div class="wph-cap2-field-hint">
                        <a href="https://www.google.com/recaptcha/admin" target="_blank"><?php esc_html_e( 'Lấy Site Key tại đây ↗', 'whp' ); ?></a>
                    </div>
                </div>
                <div class="wph-cap2-field">
                    <label>Secret Key (Private Key)</label>
                    <div class="wph-cap2-pw-wrap">
                        <input type="password" id="cap2-rv2-secret-key" value="<?php echo esc_attr( $rv2['secret_key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Nhập Secret Key...', 'whp' ); ?>">
                        <button type="button" class="wph-cap2-pw-eye" onclick="wphCapTogglePw('cap2-rv2-secret-key',this)" title="<?php esc_attr_e( 'Hiện/Ẩn', 'whp' ); ?>"><?php echo wph_cap_eye_icon(); ?></button>
                    </div>
                    <div class="wph-cap2-field-hint">
                        <a href="https://www.google.com/recaptcha/admin" target="_blank"><?php esc_html_e( 'Lấy Secret Key tại đây ↗', 'whp' ); ?></a>
                    </div>
                </div>

                <div class="wph-cap2-section-title"><?php esc_html_e( 'Tùy chỉnh hiển thị', 'whp' ); ?></div>
                <div class="wph-cap2-display-grid">
                    <!-- Ngôn ngữ -->
                    <div class="wph-cap2-display-card">
                        <div class="wph-cap2-display-hd">
                            <div class="wph-cap2-display-hd-icon" style="background:#f0fdf4;color:#16a34a;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            </div>
                            <span class="wph-cap2-display-hd-label"><?php esc_html_e( 'Ngôn ngữ', 'whp' ); ?></span>
                        </div>
                        <select id="cap2-rv2-lang" style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;font-family:inherit;color:#1e293b;background:#fff;cursor:pointer;">
                            <option value="" <?php selected( $rv2['lang'] ?? '', '' ); ?>>🌐 <?php esc_html_e( 'Tự động', 'whp' ); ?></option>
                            <option value="vi" <?php selected( $rv2['lang'] ?? '', 'vi' ); ?>>🇻🇳 <?php esc_html_e( 'Tiếng Việt', 'whp' ); ?></option>
                            <option value="en" <?php selected( $rv2['lang'] ?? '', 'en' ); ?>>🇬🇧 English</option>
                            <option value="zh-CN" <?php selected( $rv2['lang'] ?? '', 'zh-CN' ); ?>>🇨🇳 中文</option>
                            <option value="ja" <?php selected( $rv2['lang'] ?? '', 'ja' ); ?>>🇯🇵 日本語</option>
                        </select>
                    </div>
                    <!-- Kích thước -->
                    <div class="wph-cap2-display-card">
                        <div class="wph-cap2-display-hd">
                            <div class="wph-cap2-display-hd-icon" style="background:#eff6ff;color:#2563eb;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                            </div>
                            <span class="wph-cap2-display-hd-label"><?php esc_html_e( 'Kích thước', 'whp' ); ?></span>
                        </div>
                        <div class="wph-cap2-seg">
                            <div class="wph-cap2-seg-opt">
                                <input type="radio" name="cap2_rv2_size" id="cap2-rv2-size-normal" value="normal" <?php checked( ($rv2['size'] ?? 'normal'), 'normal' ); ?>>
                                <label for="cap2-rv2-size-normal"><?php esc_html_e( 'Bình thường', 'whp' ); ?></label>
                            </div>
                            <div class="wph-cap2-seg-opt">
                                <input type="radio" name="cap2_rv2_size" id="cap2-rv2-size-compact" value="compact" <?php checked( ($rv2['size'] ?? 'normal'), 'compact' ); ?>>
                                <label for="cap2-rv2-size-compact"><?php esc_html_e( 'Gọn nhẹ', 'whp' ); ?></label>
                            </div>
                        </div>
                    </div>
                    <!-- Chủ đề -->
                    <div class="wph-cap2-display-card">
                        <div class="wph-cap2-display-hd">
                            <div class="wph-cap2-display-hd-icon" style="background:#f0fdff;color:#0891b2;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                            </div>
                            <span class="wph-cap2-display-hd-label"><?php esc_html_e( 'Chủ đề', 'whp' ); ?></span>
                        </div>
                        <div class="wph-cap2-seg">
                            <div class="wph-cap2-seg-opt">
                                <input type="radio" name="cap2_rv2_theme" id="cap2-rv2-theme-light" value="light" <?php checked( ($rv2['theme'] ?? 'light'), 'light' ); ?>>
                                <label for="cap2-rv2-theme-light">☀️ <?php esc_html_e( 'Sáng', 'whp' ); ?></label>
                            </div>
                            <div class="wph-cap2-seg-opt">
                                <input type="radio" name="cap2_rv2_theme" id="cap2-rv2-theme-dark" value="dark" <?php checked( ($rv2['theme'] ?? 'light'), 'dark' ); ?>>
                                <label for="cap2-rv2-theme-dark">🌙 <?php esc_html_e( 'Tối', 'whp' ); ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wph-cap2-section-title" style="margin-top:22px;"><?php esc_html_e( 'Áp dụng cho', 'whp' ); ?></div>
                <?php $render_apply(''); ?>
            </div>

            <!-- ── reCAPTCHA v3 panel ── -->
            <div class="wph-cap2-prov-panel <?php echo $active_prov === 'recaptcha_v3' ? 'active' : ''; ?>" id="cap2-panel-recaptcha_v3">
                <div class="wph-cap2-prov-panel-head">
                    <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:11px;font-weight:800;color:#2563eb;">Gv3</span>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1e293b;">reCAPTCHA v3</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;"><?php esc_html_e( 'Ẩn, dựa trên điểm hành vi — Google', 'whp' ); ?></div>
                    </div>
                    <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
                        <span style="font-size:12px;font-weight:700;color:#475569;"><?php esc_html_e( 'Bật', 'whp' ); ?></span>
                        <label class="wph-cap2-toggle">
                            <input type="checkbox" id="cap2-rv3-enabled" <?php checked( ! empty( $rv3['enabled'] ) && $rv3['enabled'] === '1' ); ?>>
                            <span class="wph-cap2-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="wph-cap2-field">
                    <label>Site Key (Public Key)</label>
                    <input type="text" id="cap2-rv3-site-key" value="<?php echo esc_attr( $rv3['site_key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Nhập Site Key...', 'whp' ); ?>">
                    <div class="wph-cap2-field-hint"><a href="https://www.google.com/recaptcha/admin" target="_blank"><?php esc_html_e( 'Lấy Site Key tại đây ↗', 'whp' ); ?></a></div>
                </div>
                <div class="wph-cap2-field">
                    <label>Secret Key (Private Key)</label>
                    <div class="wph-cap2-pw-wrap">
                        <input type="password" id="cap2-rv3-secret-key" value="<?php echo esc_attr( $rv3['secret_key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Nhập Secret Key...', 'whp' ); ?>">
                        <button type="button" class="wph-cap2-pw-eye" onclick="wphCapTogglePw('cap2-rv3-secret-key',this)" title="<?php esc_attr_e( 'Hiện/Ẩn', 'whp' ); ?>"><?php echo wph_cap_eye_icon(); ?></button>
                    </div>
                    <div class="wph-cap2-field-hint"><a href="https://www.google.com/recaptcha/admin" target="_blank"><?php esc_html_e( 'Lấy Secret Key tại đây ↗', 'whp' ); ?></a></div>
                </div>
                <div class="wph-cap2-field">
                    <label><?php esc_html_e( 'Ngưỡng điểm tối thiểu (0.0 – 1.0)', 'whp' ); ?></label>
                    <input type="text" id="cap2-rv3-score" value="<?php echo esc_attr( $rv3['score'] ?? '0.5' ); ?>" placeholder="0.5" style="max-width:140px;">
                    <div class="wph-cap2-field-hint"><?php esc_html_e( 'Dưới ngưỡng sẽ bị chặn. Mặc định:', 'whp' ); ?> <strong>0.5</strong>. <?php esc_html_e( 'Giá trị cao hơn = bảo mật hơn nhưng có thể chặn nhầm.', 'whp' ); ?></div>
                </div>

                <div class="wph-cap2-section-title" style="margin-top:8px;"><?php esc_html_e( 'Áp dụng cho', 'whp' ); ?></div>
                <?php $render_apply('-v3'); ?>
            </div>

            <!-- ── Turnstile panel ── -->
            <div class="wph-cap2-prov-panel <?php echo $active_prov === 'turnstile' ? 'active' : ''; ?>" id="cap2-panel-turnstile">
                <div class="wph-cap2-prov-panel-head">
                    <div style="width:36px;height:36px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1e293b;">Cloudflare Turnstile</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;"><?php esc_html_e( 'CAPTCHA ẩn mượt của Cloudflare', 'whp' ); ?></div>
                    </div>
                    <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
                        <span style="font-size:12px;font-weight:700;color:#475569;"><?php esc_html_e( 'Bật', 'whp' ); ?></span>
                        <label class="wph-cap2-toggle">
                            <input type="checkbox" id="cap2-ts-enabled" <?php checked( ! empty( $ts['enabled'] ) && $ts['enabled'] === '1' ); ?>>
                            <span class="wph-cap2-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="wph-cap2-field">
                    <label>Site Key (Public Key)</label>
                    <input type="text" id="cap2-ts-site-key" value="<?php echo esc_attr( $ts['site_key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Nhập Site Key...', 'whp' ); ?>">
                    <div class="wph-cap2-field-hint"><a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank"><?php esc_html_e( 'Lấy Site Key tại Cloudflare Dashboard ↗', 'whp' ); ?></a></div>
                </div>
                <div class="wph-cap2-field">
                    <label>Secret Key (Private Key)</label>
                    <div class="wph-cap2-pw-wrap">
                        <input type="password" id="cap2-ts-secret-key" value="<?php echo esc_attr( $ts['secret_key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Nhập Secret Key...', 'whp' ); ?>">
                        <button type="button" class="wph-cap2-pw-eye" onclick="wphCapTogglePw('cap2-ts-secret-key',this)" title="<?php esc_attr_e( 'Hiện/Ẩn', 'whp' ); ?>"><?php echo wph_cap_eye_icon(); ?></button>
                    </div>
                    <div class="wph-cap2-field-hint"><a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank"><?php esc_html_e( 'Lấy Secret Key tại Cloudflare Dashboard ↗', 'whp' ); ?></a></div>
                </div>

                <div class="wph-cap2-section-title" style="margin-top:8px;"><?php esc_html_e( 'Áp dụng cho', 'whp' ); ?></div>
                <?php $render_apply('-ts'); ?>
            </div>

            <!-- ── hCaptcha panel ── -->
            <div class="wph-cap2-prov-panel <?php echo $active_prov === 'hcaptcha' ? 'active' : ''; ?>" id="cap2-panel-hcaptcha">
                <div class="wph-cap2-prov-panel-head">
                    <div style="width:36px;height:36px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1e293b;">hCaptcha</div>
                        <div style="font-size:12px;color:#64748b;margin-top:2px;"><?php esc_html_e( 'Thay thế reCAPTCHA, bảo mật cao, tập trung quyền riêng tư', 'whp' ); ?></div>
                    </div>
                    <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
                        <span style="font-size:12px;font-weight:700;color:#475569;"><?php esc_html_e( 'Bật', 'whp' ); ?></span>
                        <label class="wph-cap2-toggle">
                            <input type="checkbox" id="cap2-hc-enabled" <?php checked( ! empty( $hc['enabled'] ) && $hc['enabled'] === '1' ); ?>>
                            <span class="wph-cap2-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="wph-cap2-field">
                    <label>Site Key (Public Key)</label>
                    <input type="text" id="cap2-hc-site-key" value="<?php echo esc_attr( $hc['site_key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Nhập Site Key...', 'whp' ); ?>">
                    <div class="wph-cap2-field-hint"><a href="https://dashboard.hcaptcha.com/" target="_blank"><?php esc_html_e( 'Lấy Site Key tại hCaptcha Dashboard ↗', 'whp' ); ?></a></div>
                </div>
                <div class="wph-cap2-field">
                    <label>Secret Key (Private Key)</label>
                    <div class="wph-cap2-pw-wrap">
                        <input type="password" id="cap2-hc-secret-key" value="<?php echo esc_attr( $hc['secret_key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Nhập Secret Key...', 'whp' ); ?>">
                        <button type="button" class="wph-cap2-pw-eye" onclick="wphCapTogglePw('cap2-hc-secret-key',this)" title="<?php esc_attr_e( 'Hiện/Ẩn', 'whp' ); ?>"><?php echo wph_cap_eye_icon(); ?></button>
                    </div>
                    <div class="wph-cap2-field-hint"><a href="https://dashboard.hcaptcha.com/" target="_blank"><?php esc_html_e( 'Lấy Secret Key tại hCaptcha Dashboard ↗', 'whp' ); ?></a></div>
                </div>

                <div class="wph-cap2-section-title" style="margin-top:8px;"><?php esc_html_e( 'Áp dụng cho', 'whp' ); ?></div>
                <?php $render_apply('-hc'); ?>
            </div>

        </div>

    </div>

    <!-- ── RIGHT COLUMN ── -->
    <div class="wph-cap2-sidebar">

        <!-- Guide card -->
        <div class="wph-cap2-card">
            <div class="wph-cap2-card-head">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:28px;height:28px;background:#ecfeff;border-radius:7px;display:flex;align-items:center;justify-content:center;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                    </div>
                    <h3><?php esc_html_e( 'Hướng dẫn', 'whp' ); ?></h3>
                </div>
            </div>
            <div class="wph-cap2-card-body" style="padding:14px 20px;">
                <div class="wph-cap2-guide-step">
                    <div class="wph-cap2-guide-num">1</div>
                    <div class="wph-cap2-guide-text">
                        <?php esc_html_e( 'Đăng ký và lấy Key tại', 'whp' ); ?> <a href="https://www.google.com/recaptcha/admin" target="_blank">Google reCAPTCHA Admin ↗</a>
                    </div>
                </div>
                <div class="wph-cap2-guide-step">
                    <div class="wph-cap2-guide-num">2</div>
                    <div class="wph-cap2-guide-text"><?php esc_html_e( 'Thêm domain website vào danh sách cho phép', 'whp' ); ?></div>
                </div>
                <div class="wph-cap2-guide-step">
                    <div class="wph-cap2-guide-num">3</div>
                    <div class="wph-cap2-guide-text"><?php esc_html_e( 'Điền Site Key và Secret Key vào form, chọn provider muốn dùng, bấm', 'whp' ); ?> <strong><?php esc_html_e( 'Lưu cài đặt', 'whp' ); ?></strong></div>
                </div>
                <div class="wph-cap2-guide-step">
                    <div class="wph-cap2-guide-num">4</div>
                    <div class="wph-cap2-guide-text"><?php esc_html_e( 'Kiểm tra hoạt động bằng cách gửi thử biểu mẫu và xem nhật ký bên dưới', 'whp' ); ?></div>
                </div>
            </div>
        </div>

        <!-- Mini log card -->
        <div class="wph-cap2-card">
            <div class="wph-cap2-card-head">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:28px;height:28px;background:#f8fafc;border-radius:7px;display:flex;align-items:center;justify-content:center;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                    <h3><?php esc_html_e( 'Nhật ký xác thực', 'whp' ); ?></h3>
                </div>
                <button onclick="wphCap2OpenLogs()" style="background:#ecfeff;color:#0891b2;border:none;border-radius:7px;padding:5px 12px;font-size:12px;font-weight:700;cursor:pointer;transition:background .12s;" onmouseover="this.style.background='#cffafe'" onmouseout="this.style.background='#ecfeff'"><?php esc_html_e( 'Xem tất cả', 'whp' ); ?></button>
            </div>
            <?php if ( empty( $mini_logs ) ) : ?>
            <div style="text-align:center;padding:32px 20px;color:#94a3b8;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block;margin:0 auto 10px;opacity:.3;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <p style="margin:0;font-size:12.5px;"><?php esc_html_e( 'Chưa có nhật ký', 'whp' ); ?></p>
            </div>
            <?php else : ?>
            <div style="overflow-x:auto;">
                <table class="wph-cap2-mini-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Thời gian', 'whp' ); ?></th>
                            <th>IP</th>
                            <th><?php esc_html_e( 'Kết quả', 'whp' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $mini_logs as $row ) :
                        $is_pass = $row->status === 'passed';
                    ?>
                    <tr>
                        <td style="white-space:nowrap;color:#64748b;"><?php echo date_i18n( 'd/m H:i', strtotime( $row->created_at ) ); ?></td>
                        <td style="font-family:monospace;font-size:11px;"><?php echo esc_html( $row->ip_address ); ?></td>
                        <td>
                            <?php if ( $is_pass ) : ?>
                            <span class="wph-cap2-status-badge" style="background:#f0fdf4;color:#16a34a;"><?php esc_html_e( 'Thành công', 'whp' ); ?></span>
                            <?php else : ?>
                            <span class="wph-cap2-status-badge" style="background:#fef2f2;color:#dc2626;"><?php esc_html_e( 'Thất bại', 'whp' ); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Clear logs button -->
        <div style="text-align:right;margin-top:8px;">
            <button onclick="wphCap2ClearLogs()" style="background:none;border:none;color:#94a3b8;font-size:12px;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:color .15s;padding:4px 0;" onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#94a3b8'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
                <?php esc_html_e( 'Xóa tất cả nhật ký', 'whp' ); ?>
            </button>
        </div>

        <!-- Log settings card -->
        <div class="wph-cap2-card">
            <div class="wph-cap2-card-head">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:28px;height:28px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:7px;display:flex;align-items:center;justify-content:center;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                    </div>
                    <h3><?php esc_html_e( 'Cài đặt lưu log', 'whp' ); ?></h3>
                </div>
            </div>
            <div style="padding:0 16px 14px;display:flex;flex-direction:column;gap:10px;">
                <div style="border:1.5px solid #f1f5f9;border-radius:10px;padding:11px 12px;background:#fafcff;">
                    <div style="display:flex;align-items:center;gap:9px;">
                        <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12px;font-weight:600;color:#1e293b;"><?php esc_html_e( 'Thời gian lưu log', 'whp' ); ?></div>
                            <div style="font-size:11px;color:#94a3b8;"><?php esc_html_e( 'Tự động xóa log cũ hơn mốc này', 'whp' ); ?></div>
                        </div>
                        <select id="cap-log-retention" style="min-width:115px;border:1.5px solid #e2e8f0;border-radius:7px;padding:5px 7px;font-size:12px;background:#fff;">
                            <?php foreach ( array( 0 => __( 'Không giới hạn', 'whp' ), 30 => __( '30 ngày', 'whp' ), 60 => __( '60 ngày', 'whp' ), 90 => __( '90 ngày', 'whp' ), 180 => __( '180 ngày', 'whp' ), 365 => __( '365 ngày', 'whp' ) ) as $v => $l ) : ?>
                            <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $cap_log_settings['retention'] ?? 0, $v ); ?>><?php echo esc_html( $l ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="border:1.5px solid #f1f5f9;border-radius:10px;padding:11px 12px;background:#fafcff;">
                    <div style="display:flex;align-items:center;gap:9px;">
                        <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#fffbeb,#fef3c7);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:12px;font-weight:600;color:#1e293b;"><?php esc_html_e( 'Giới hạn tối đa', 'whp' ); ?></div>
                            <div style="font-size:11px;color:#94a3b8;"><?php esc_html_e( 'Số bản ghi tối đa được lưu', 'whp' ); ?></div>
                        </div>
                        <select id="cap-log-maxlogs" style="min-width:115px;border:1.5px solid #e2e8f0;border-radius:7px;padding:5px 7px;font-size:12px;background:#fff;">
                            <?php foreach ( array( 10000 => '10.000', 25000 => '25.000', 50000 => '50.000', 100000 => '100.000', 0 => __( 'Không giới hạn', 'whp' ) ) as $v => $l ) : ?>
                            <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $cap_log_settings['max_logs'] ?? 0, $v ); ?>><?php echo esc_html( $l ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <button onclick="wphCap2SaveLogSettings()" style="width:100%;background:linear-gradient(135deg,#3858e9,#2563eb);color:#fff;border:none;border-radius:8px;padding:9px 0;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:opacity .15s;" onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <?php esc_html_e( 'Lưu cài đặt', 'whp' ); ?>
                </button>
            </div>
        </div>

    </div><!-- /right -->
</div><!-- /body -->

<!-- ── INACTIVE WARN + SAVE BAR (full width, like SMTP) ── -->
<div id="cap2-inactive-warn" style="display:<?php echo ( empty( $settings['active'] ) || $settings['active'] !== '1' ) ? 'flex' : 'none'; ?>;align-items:center;gap:8px;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:10px 22px;font-size:12.5px;color:#92400e;font-weight:600;margin-top:20px;">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <?php esc_html_e( 'Cấu hình CAPTCHA đang', 'whp' ); ?> <span style="text-decoration:underline;margin:0 3px;"><?php esc_html_e( 'tắt', 'whp' ); ?></span> — <?php esc_html_e( 'CAPTCHA sẽ không hoạt động trên form cho đến khi bật lại.', 'whp' ); ?>
</div>
<div class="wph-cap2-save-bar" id="cap2-save-bar-wrap">
    <span class="wph-cap2-save-bar-hint">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
        <?php esc_html_e( 'Các thay đổi được áp dụng ngay sau khi lưu.', 'whp' ); ?>
    </span>
    <button class="wph-cap2-save-btn" onclick="wphCap2Save()">
        <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:15px;"></span>
        <?php esc_html_e( 'Lưu cấu hình', 'whp' ); ?>
    </button>
</div>

<!-- ── FULL LOG MODAL ─────────────────────────────────────────────────────── -->
<div id="cap2-log-modal" style="display:none;" class="wph-cap2-modal-overlay" onclick="if(event.target===this)wphCap2CloseLogs()">
    <div class="wph-cap2-modal">
        <div class="wph-cap2-modal-head">
            <h3><?php esc_html_e( 'Nhật ký xác thực CAPTCHA', 'whp' ); ?></h3>
            <button class="wph-cap2-modal-close" onclick="wphCap2CloseLogs()">&times;</button>
        </div>
        <div class="wph-cap2-modal-body" id="cap2-log-modal-body">
            <div style="text-align:center;padding:40px;color:#94a3b8;"><?php esc_html_e( 'Đang tải...', 'whp' ); ?></div>
        </div>
        <div class="wph-cap2-modal-foot">
            <div id="cap2-log-modal-pager" class="wph-cap2-pager"></div>
            <button onclick="wphCap2CloseLogs()" style="background:#f1f5f9;color:#475569;border:none;border-radius:8px;padding:8px 20px;font-size:13px;font-weight:600;cursor:pointer;"><?php esc_html_e( 'Đóng', 'whp' ); ?></button>
        </div>
    </div>
</div>

</div><!-- /wrap -->

<script>
(function(){
var whpCapI18n = {
    enabled:            '<?php echo esc_js( __( 'Đang bật', 'whp' ) ); ?>',
    disabled:           '<?php echo esc_js( __( 'Đang tắt', 'whp' ) ); ?>',
    networkError:       '<?php echo esc_js( __( 'Lỗi kết nối', 'whp' ) ); ?>',
    captchaEnabled:     '<?php echo esc_js( __( 'Đã bật CAPTCHA', 'whp' ) ); ?>',
    captchaDisabled:    '<?php echo esc_js( __( 'Đã tắt CAPTCHA', 'whp' ) ); ?>',
    selectProviderError:'<?php echo esc_js( __( 'Vui lòng chọn ít nhất 1 CAPTCHA provider (bật toggle) trước khi lưu', 'whp' ) ); ?>',
    siteKeyRequired:    '<?php echo esc_js( __( 'Site Key là bắt buộc', 'whp' ) ); ?>',
    secretKeyRequired:  '<?php echo esc_js( __( 'Secret Key là bắt buộc', 'whp' ) ); ?>',
    keysRequired:       '<?php echo esc_js( __( 'Vui lòng điền đầy đủ Site Key và Secret Key trước khi lưu', 'whp' ) ); ?>',
    confirmClearLogs:   '<?php echo esc_js( __( 'Xóa toàn bộ nhật ký CAPTCHA? Hành động này không thể hoàn tác.', 'whp' ) ); ?>',
    loading:            '<?php echo esc_js( __( 'Đang tải...', 'whp' ) ); ?>',
    loadError:          '<?php echo esc_js( __( 'Lỗi tải dữ liệu.', 'whp' ) ); ?>',
    noLogs:             '<?php echo esc_js( __( 'Chưa có nhật ký nào.', 'whp' ) ); ?>',
    colTime:            '<?php echo esc_js( __( 'Thời gian', 'whp' ) ); ?>',
    colResult:          '<?php echo esc_js( __( 'Kết quả', 'whp' ) ); ?>',
    colScore:           '<?php echo esc_js( __( 'Điểm', 'whp' ) ); ?>',
    statusPassed:       '<?php echo esc_js( __( 'Thành công', 'whp' ) ); ?>',
    statusFailed:       '<?php echo esc_js( __( 'Thất bại', 'whp' ) ); ?>'
};
var nonce='<?php echo esc_js($nonce); ?>';
var ajaxUrl='<?php echo esc_js($ajax_url); ?>';
var logPage=1;

/* ── helpers ── */
function post(action,data,cb){
    var fd=new FormData();
    fd.append('action',action);fd.append('nonce',nonce);
    for(var k in data)fd.append(k,typeof data[k]==='object'?JSON.stringify(data[k]):data[k]);
    fetch(ajaxUrl,{method:'POST',body:fd})
        .then(function(r){return r.json();})
        .then(cb)
        .catch(function(){showNotice(whpCapI18n.networkError,'error');});
}
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
    var el=document.getElementById('wph-cap2-notice');if(!el)return;
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
function fdate(s){var d=new Date(s.replace(' ','T'));return ('0'+d.getDate()).slice(-2)+'/'+(('0'+(d.getMonth()+1)).slice(-2))+' '+('0'+d.getHours()).slice(-2)+':'+('0'+d.getMinutes()).slice(-2);}

/* ── sync apply-to checkboxes across all provider panels ── */
window.wphCap2SyncApply=function(cb){
    var key=cb.getAttribute('data-apply');
    document.querySelectorAll('[data-apply="'+key+'"]').forEach(function(c){
        c.checked=cb.checked;
        var card=c.closest('.wph-cap2-apply-card');
        if(card) card.classList.toggle('is-active',cb.checked);
    });
};

/* ── master toggle ── */
window.wphCap2ActiveChange=function(cb,isInit){
    var lbl=document.getElementById('cap2-active-label');
    if(lbl){lbl.textContent=cb.checked?whpCapI18n.enabled:whpCapI18n.disabled;lbl.style.color=cb.checked?'#22c55e':'#ef4444';}
    var warn=document.getElementById('cap2-master-warning');
    if(warn){warn.style.display=cb.checked?'none':'flex';}
    var inlineWarn=document.getElementById('cap2-inactive-warn');
    if(inlineWarn){inlineWarn.style.display=cb.checked?'none':'flex';}
    var wrap=document.getElementById('cap2-content-wrap');
    if(wrap){wrap.classList.toggle('cap2-disabled',!cb.checked);}
    var bar=document.getElementById('cap2-save-bar-wrap');
    if(bar){bar.style.display=cb.checked?'':'none';}
    if(!isInit){
        var _isActive=cb.checked;
        wphCap2Save(function(){ whpToast(_isActive?whpCapI18n.captchaEnabled:whpCapI18n.captchaDisabled,'success'); },true);
    }
};
document.addEventListener('DOMContentLoaded',function(){
    var cb=document.getElementById('cap2-active');
    if(cb){wphCap2ActiveChange(cb,true);}
});

/* ── provider tab switching ── */
window.wphCap2Tab=function(prov){
    // update hidden input
    document.getElementById('cap2-active-provider').value=prov;
    // update tab styles
    document.querySelectorAll('.wph-cap2-prov-tab').forEach(function(t){
        t.classList.toggle('active',t.dataset.prov===prov);
    });
    // show/hide panels
    document.querySelectorAll('.wph-cap2-prov-panel').forEach(function(p){
        p.classList.toggle('active',p.id==='cap2-panel-'+prov);
    });
};

/* ── provider enabled toggles — radio behavior (chỉ 1 provider active tại 1 thời điểm) ── */
var CAP2_TOGGLE_MAP={
    'cap2-rv2-enabled':'recaptcha_v2',
    'cap2-rv3-enabled':'recaptcha_v3',
    'cap2-ts-enabled':'turnstile',
    'cap2-hc-enabled':'hcaptcha'
};
Object.keys(CAP2_TOGGLE_MAP).forEach(function(cbId){
    var cb=document.getElementById(cbId);
    if(!cb)return;
    cb.addEventListener('change',function(){
        if(this.checked){
            // Uncheck all other providers (radio behavior)
            Object.keys(CAP2_TOGGLE_MAP).forEach(function(otherId){
                if(otherId!==cbId){
                    var other=document.getElementById(otherId);
                    if(other)other.checked=false;
                }
            });
            // Set this as active_provider and switch tab
            var prov=CAP2_TOGGLE_MAP[cbId];
            document.getElementById('cap2-active-provider').value=prov;
            wphCap2Tab(prov);
        }
        // If unchecking: don't auto-select another — user must manually choose
    });
});
/* Sync on page load: if multiple somehow got saved as enabled, keep only active_provider's one */
(function(){
    var activeProv=document.getElementById('cap2-active-provider').value;
    Object.keys(CAP2_TOGGLE_MAP).forEach(function(cbId){
        var cb=document.getElementById(cbId);
        if(!cb)return;
        if(CAP2_TOGGLE_MAP[cbId]!==activeProv && cb.checked){
            cb.checked=false;
        }
    });
})();

/* ── field validation helpers ── */
var PROV_FIELDS={
    recaptcha_v2:{site:'cap2-rv2-site-key',secret:'cap2-rv2-secret-key'},
    recaptcha_v3:{site:'cap2-rv3-site-key',secret:'cap2-rv3-secret-key'},
    turnstile:   {site:'cap2-ts-site-key', secret:'cap2-ts-secret-key'},
    hcaptcha:    {site:'cap2-hc-site-key', secret:'cap2-hc-secret-key'},
};
function cap2SetFieldErr(el,msg){
    var wrap=el.closest('.wph-cap2-field');
    if(wrap){wrap.classList.add('wph-cap2-field-err');
        var existing=wrap.querySelector('.wph-cap2-field-err-msg');
        if(!existing){var em=document.createElement('div');em.className='wph-cap2-field-err-msg';em.innerHTML='<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'+msg;wrap.appendChild(em);}
    }
    el.addEventListener('input',function once(){
        if(wrap){wrap.classList.remove('wph-cap2-field-err');var em=wrap.querySelector('.wph-cap2-field-err-msg');if(em)em.remove();}
        el.removeEventListener('input',once);
    });
}
function cap2ClearAllErr(){
    document.querySelectorAll('.wph-cap2-field-err').forEach(function(w){
        w.classList.remove('wph-cap2-field-err');
        var em=w.querySelector('.wph-cap2-field-err-msg');if(em)em.remove();
    });
}

/* ── save ── */
window.wphCap2Save=function(onDone,skipNotice){
    cap2ClearAllErr();
    var isActive=document.getElementById('cap2-active').checked;
    var prov=document.getElementById('cap2-active-provider').value;

    // Validate: nếu master đang bật thì phải chọn đúng 1 provider và đủ key
    if(isActive){
        // Kiểm tra có provider nào được enable không
        var enabledProv=null;
        Object.keys(CAP2_TOGGLE_MAP).forEach(function(cbId){
            var cb=document.getElementById(cbId);
            if(cb&&cb.checked)enabledProv=CAP2_TOGGLE_MAP[cbId];
        });
        if(!enabledProv){
            showNotice(whpCapI18n.selectProviderError,'error');
            return;
        }
        // Sync active_provider với provider được enable
        prov=enabledProv;
        document.getElementById('cap2-active-provider').value=prov;
        wphCap2Tab(prov);
        // Validate keys của provider được chọn
        var fids=PROV_FIELDS[prov]||{};
        var siteEl=fids.site?document.getElementById(fids.site):null;
        var secretEl=fids.secret?document.getElementById(fids.secret):null;
        var errs=[];
        if(siteEl&&!siteEl.value.trim()){cap2SetFieldErr(siteEl,whpCapI18n.siteKeyRequired);errs.push(siteEl);}
        if(secretEl&&!secretEl.value.trim()){cap2SetFieldErr(secretEl,whpCapI18n.secretKeyRequired);errs.push(secretEl);}
        if(errs.length){
            showNotice(whpCapI18n.keysRequired,'error');
            setTimeout(function(){if(errs[0])errs[0].focus();},120);
            return;
        }
    }

    var s={
        active: isActive?'1':'0',
        active_provider: prov,
        recaptcha_v2:{
            enabled: document.getElementById('cap2-rv2-enabled').checked?'1':'0',
            site_key: document.getElementById('cap2-rv2-site-key').value,
            secret_key: document.getElementById('cap2-rv2-secret-key').value,
            lang: document.getElementById('cap2-rv2-lang').value,
            size: document.querySelector('[name=cap2_rv2_size]:checked')?document.querySelector('[name=cap2_rv2_size]:checked').value:'normal',
            theme: document.querySelector('[name=cap2_rv2_theme]:checked')?document.querySelector('[name=cap2_rv2_theme]:checked').value:'light',
        },
        recaptcha_v3:{
            enabled: document.getElementById('cap2-rv3-enabled').checked?'1':'0',
            site_key: document.getElementById('cap2-rv3-site-key').value,
            secret_key: document.getElementById('cap2-rv3-secret-key').value,
            score: document.getElementById('cap2-rv3-score').value,
        },
        turnstile:{
            enabled: document.getElementById('cap2-ts-enabled').checked?'1':'0',
            site_key: document.getElementById('cap2-ts-site-key').value,
            secret_key: document.getElementById('cap2-ts-secret-key').value,
        },
        hcaptcha:{
            enabled: document.getElementById('cap2-hc-enabled').checked?'1':'0',
            site_key: document.getElementById('cap2-hc-site-key').value,
            secret_key: document.getElementById('cap2-hc-secret-key').value,
        },
        apply:(function(){
            var g=function(id){var el=document.getElementById(id);return el&&el.checked?'1':'0';};
            return{
                cf7:g('cap2-apply-cf7'),
                wpforms:g('cap2-apply-wpforms'),
                gf:g('cap2-apply-gf'),
                ninja:g('cap2-apply-ninja'),
                fluent:g('cap2-apply-fluent'),
                elementor:g('cap2-apply-elementor'),
                login:g('cap2-apply-login'),
                register:g('cap2-apply-register'),
                comment:g('cap2-apply-comment'),
            };
        })()
    };
    var btn=document.querySelector('.wph-cap2-save-btn');
    if(btn){btn.style.opacity='.6';btn.disabled=true;}
    post('wph_cap_save',{settings:JSON.stringify(s)},function(r){
        if(btn){btn.style.opacity='1';btn.disabled=false;}
        if(r.success){
            if(!skipNotice){
                showNotice(r.data&&r.data.message?r.data.message:'Đã lưu cài đặt CAPTCHA','success');
                if(!isActive){
                    setTimeout(function(){
                        showNotice('⚠️ Lưu ý: Cấu hình CAPTCHA đang <strong>tắt</strong> — CAPTCHA sẽ không hoạt động trên bất kỳ form nào. Hãy bật "Kích hoạt CAPTCHA" để áp dụng.','warning');
                    },900);
                }
            }
        } else {
            if(!skipNotice) showNotice((r.data&&r.data.message)||'Lỗi lưu cài đặt','error');
        }
        if(typeof onDone==='function'){onDone();}
    });
};

/* ── clear logs ── */
window.wphCap2ClearLogs=function(){
    if(!confirm(whpCapI18n.confirmClearLogs))return;
    post('wph_cap_clear_logs',{},function(r){
        if(r.success){showNotice(r.data&&r.data.message?r.data.message:'Đã xóa nhật ký','success');setTimeout(function(){location.reload();},800);}
        else showNotice((r.data&&r.data.message)||'Lỗi','error');
    });
};

window.wphCap2SaveLogSettings=function(){
    var r=document.getElementById('cap-log-retention');
    var m=document.getElementById('cap-log-maxlogs');
    post('wph_cap_save_log_settings',{retention:r?r.value:0,max_logs:m?m.value:0},function(res){
        if(res.success) showNotice(res.data&&res.data.message?res.data.message:'Đã lưu cài đặt','success');
        else showNotice((res.data&&res.data.message)||'Lỗi lưu cài đặt','error');
    });
};

/* ── open logs modal ── */
window.wphCap2OpenLogs=function(){
    var modal=document.getElementById('cap2-log-modal');
    if(modal)modal.style.display='flex';
    document.body.style.overflow='hidden';
    wphCap2LoadLogs(1);
};
window.wphCap2CloseLogs=function(){
    var modal=document.getElementById('cap2-log-modal');
    if(modal)modal.style.display='none';
    document.body.style.overflow='';
};

function wphCap2LoadLogs(page){
    logPage=page||1;
    var body=document.getElementById('cap2-log-modal-body');
    if(body)body.innerHTML='<div style="text-align:center;padding:40px;color:#94a3b8;">'+whpCapI18n.loading+'</div>';
    post('wph_cap_get_logs_ajax',{page:logPage},function(r){
        if(!r.success){if(body)body.innerHTML='<div style="text-align:center;padding:40px;color:#dc2626;">'+whpCapI18n.loadError+'</div>';return;}
        var data=r.data;
        var rows=data.rows||[];
        var total=data.total||0;
        var per=20;
        var pages=Math.max(1,Math.ceil(total/per));

        // Build table
        var prov_names={'recaptcha_v2':'reCAPTCHA v2','recaptcha_v3':'reCAPTCHA v3','turnstile':'Turnstile','hcaptcha':'hCaptcha'};
        var html='';
        if(!rows.length){
            html='<div style="text-align:center;padding:50px;color:#94a3b8;">'+whpCapI18n.noLogs+'</div>';
        } else {
            html='<table class="wph-cap2-log-table"><thead><tr>'
                +'<th>'+whpCapI18n.colTime+'</th><th>IP</th><th>Provider</th><th>'+whpCapI18n.colResult+'</th><th>'+whpCapI18n.colScore+'</th>'
                +'</tr></thead><tbody>';
            rows.forEach(function(row){
                var isPass=row.status==='passed';
                var badge=isPass
                    ?'<span class="wph-cap2-status-badge" style="background:#f0fdf4;color:#16a34a;">'+whpCapI18n.statusPassed+'</span>'
                    :'<span class="wph-cap2-status-badge" style="background:#fef2f2;color:#dc2626;">'+whpCapI18n.statusFailed+'</span>';
                var provLabel=prov_names[row.provider]||row.provider||'—';
                var score=row.score!==null&&row.score!==''&&row.score!==undefined?parseFloat(row.score).toFixed(2):'—';
                html+='<tr>'
                    +'<td style="white-space:nowrap;color:#64748b;">'+h(fdate(row.created_at))+'</td>'
                    +'<td style="font-family:monospace;font-size:12px;">'+h(row.ip_address)+'</td>'
                    +'<td style="color:#64748b;">'+h(provLabel)+'</td>'
                    +'<td>'+badge+'</td>'
                    +'<td style="color:#94a3b8;font-family:monospace;">'+h(score)+'</td>'
                    +'</tr>';
            });
            html+='</tbody></table>';
        }
        if(body)body.innerHTML=html;

        // Pagination
        var pager=document.getElementById('cap2-log-modal-pager');
        if(pager){
            var phtml='<span style="font-size:12px;color:#64748b;margin-right:8px;">'+total+' bản ghi</span>';
            var start=Math.max(1,logPage-2);
            var end=Math.min(pages,logPage+2);
            if(logPage>1)phtml+='<button class="wph-cap2-pager-btn" onclick="wphCap2LoadLogs('+(logPage-1)+')">‹</button>';
            for(var p=start;p<=end;p++){
                phtml+='<button class="wph-cap2-pager-btn'+(p===logPage?' active':'')+'" onclick="wphCap2LoadLogs('+p+')">'+p+'</button>';
            }
            if(logPage<pages)phtml+='<button class="wph-cap2-pager-btn" onclick="wphCap2LoadLogs('+(logPage+1)+')">›</button>';
            pager.innerHTML=phtml;
        }
    });
}
window.wphCap2LoadLogs=wphCap2LoadLogs;

/* ── keyboard close modal ── */
document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){wphCap2CloseLogs();}
});

/* ── Secret key eye toggle ── */
function wphCapTogglePw(inputId,btn){
    var inp=document.getElementById(inputId);
    if(!inp)return;
    var show=inp.type==='password';
    inp.type=show?'text':'password';
    btn.innerHTML=show
        ?'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        :'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
window.wphCapTogglePw=wphCapTogglePw;

})();
</script>
    <?php
}
