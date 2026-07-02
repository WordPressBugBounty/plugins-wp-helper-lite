<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function wph_email_log_page_layout() {
    $nonce    = wp_create_nonce( 'wph_el_nonce' );
    $ajax_url = admin_url( 'admin-ajax.php' );
    $settings = get_option( 'wph_el_settings', array() );

    // SMTP settings — detect from built-in plugin + popular 3rd-party plugins
    $smtp_info   = function_exists( 'wph_el_detect_smtp' ) ? wph_el_detect_smtp() : array( 'active' => false );
    $smtp_active = ! empty( $smtp_info['active'] );
    $smtp_host   = $smtp_info['host']      ?? '';
    $smtp_port   = $smtp_info['port']      ?? '';
    $smtp_enc    = $smtp_info['security']  ?? '';
    $smtp_from   = $smtp_info['from']      ?? '';
    $smtp_source = $smtp_info['source']    ?? '';

    // Stats with trends
    global $wpdb;
    $today_str = current_time( 'Y-m-d' );
    $yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
    $w7_start  = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
    $w14_start = date( 'Y-m-d H:i:s', strtotime( '-14 days' ) );

    $total_all   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs" );
    $total_cur7  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE created_at>=%s", $w7_start ) );
    $total_prev7 = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE created_at>=%s AND created_at<%s", $w14_start, $w7_start ) );
    $sent_all    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE status='sent'" );
    $sent_cur7   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE status='sent' AND created_at>=%s", $w7_start ) );
    $sent_prev7  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE status='sent' AND created_at>=%s AND created_at<%s", $w14_start, $w7_start ) );
    $fail_all    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE status='failed'" );
    $fail_cur7   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE status='failed' AND created_at>=%s", $w7_start ) );
    $fail_prev7  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE status='failed' AND created_at>=%s AND created_at<%s", $w14_start, $w7_start ) );
    $today_cnt   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE DATE(created_at)=%s", $today_str ) );
    $yest_cnt    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE DATE(created_at)=%s", $yesterday ) );
    $pending_cnt = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE status='pending'" );

    $calc_trend = function ( $cur, $prev ) {
        if ( $prev <= 0 && $cur > 0 ) return array( 'pct' => null, 'dir' => 'new' );
        if ( $prev <= 0 ) return array( 'pct' => null, 'dir' => 'none' );
        $pct = round( abs( $cur - $prev ) / $prev * 100 );
        return array( 'pct' => $pct, 'dir' => $cur >= $prev ? 'up' : 'down' );
    };

    // Chart data: last 7 days
    $chart_data = array();
    for ( $i = 6; $i >= 0; $i-- ) {
        $d            = date( 'Y-m-d', strtotime( "-{$i} days" ) );
        $chart_data[] = array(
            'date'   => date( 'd/m', strtotime( $d ) ),
            'sent'   => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE DATE(created_at)=%s AND status='sent'", $d ) ),
            'failed' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE DATE(created_at)=%s AND status='failed'", $d ) ),
        );
    }

    // Type stats for donut — also check headers for plugin markers (CF7, WooCommerce hooks, etc.)
    $all_rows_for_type = $wpdb->get_results( "SELECT subject, headers FROM {$wpdb->prefix}wph_email_logs ORDER BY created_at DESC LIMIT 3000" );
    $type_counts = array( 'Form liên hệ' => 0, 'WooCommerce' => 0, 'Đăng ký TK' => 0, 'Khác' => 0 );
    foreach ( $all_rows_for_type as $row ) {
        $s = mb_strtolower( $row->subject );
        $h = mb_strtolower( $row->headers ?? '' );
        if ( strpos( $s, 'woocommerce' ) !== false || strpos( $s, 'đơn hàng' ) !== false || strpos( $s, 'order' ) !== false
             || strpos( $h, 'woocommerce' ) !== false ) {
            $type_counts['WooCommerce']++;
        } elseif ( strpos( $s, 'đăng ký' ) !== false || strpos( $s, 'register' ) !== false || strpos( $s, 'confirm' ) !== false || strpos( $s, 'verify' ) !== false ) {
            $type_counts['Đăng ký TK']++;
        } elseif ( strpos( $s, 'form' ) !== false || strpos( $s, 'liên hệ' ) !== false || strpos( $s, 'contact' ) !== false
                   || strpos( $h, 'contact form 7' ) !== false || strpos( $h, 'contact-form-7' ) !== false || strpos( $h, 'wpcf7' ) !== false ) {
            $type_counts['Form liên hệ']++;
        } else {
            $type_counts['Khác']++;
        }
    }
    $type_total = max( 1, array_sum( $type_counts ) );

    // Recent errors
    $recent_errors = $wpdb->get_results( "SELECT id, subject, smtp_response, created_at FROM {$wpdb->prefix}wph_email_logs WHERE status='failed' ORDER BY created_at DESC LIMIT 3" );

    // Log list with filters
    $filter_status    = isset( $_GET['el_status'] )    ? sanitize_key( $_GET['el_status'] )          : '';
    $filter_search    = isset( $_GET['el_search'] )    ? sanitize_text_field( $_GET['el_search'] )    : '';
    $filter_date_from = isset( $_GET['el_date_from'] ) ? sanitize_text_field( $_GET['el_date_from'] ) : '';
    $filter_date_to   = isset( $_GET['el_date_to'] )   ? sanitize_text_field( $_GET['el_date_to'] )   : '';
    $filter_type      = isset( $_GET['el_type'] )      ? sanitize_key( $_GET['el_type'] )             : '';
    $current_page     = max( 1, absint( $_GET['el_page'] ?? 1 ) );

    // Type → LIKE patterns on subject
    $type_patterns = array(
        'woocommerce' => array( 'woocommerce', 'đơn hàng', 'order' ),
        'form'        => array( 'form', 'liên hệ', 'contact' ),
        'register'    => array( 'đăng ký', 'register', 'verify', 'confirm' ),
        'reset'       => array( 'đặt lại', 'reset', 'password' ),
        'newsletter'  => array( 'bản tin', 'newsletter' ),
        'system'      => array(),
    );
    $per_page         = 10;

    // Build where
    $where = array( '1=1' ); $params = array();
    if ( $filter_status ) { $where[] = 'status=%s'; $params[] = $filter_status; }
    if ( $filter_search ) {
        $like     = '%' . $wpdb->esc_like( $filter_search ) . '%';
        $where[]  = '(to_email LIKE %s OR subject LIKE %s)';
        $params[] = $like; $params[] = $like;
    }
    if ( $filter_date_from ) { $where[] = 'DATE(created_at)>=%s'; $params[] = $filter_date_from; }
    if ( $filter_date_to )   { $where[] = 'DATE(created_at)<=%s'; $params[] = $filter_date_to; }
    if ( $filter_type && isset( $type_patterns[ $filter_type ] ) ) {
        $pats = $type_patterns[ $filter_type ];
        if ( $filter_type === 'system' ) {
            // System = no match for any other type keywords
            $excl = array( 'woocommerce', 'đơn hàng', 'order', 'form', 'liên hệ', 'contact',
                           'đăng ký', 'register', 'verify', 'confirm', 'đặt lại', 'reset',
                           'password', 'bản tin', 'newsletter' );
            $not_likes = array();
            foreach ( $excl as $e ) {
                $not_likes[] = 'subject NOT LIKE %s';
                $params[]    = '%' . $wpdb->esc_like( $e ) . '%';
            }
            $where[] = '(' . implode( ' AND ', $not_likes ) . ')';
        } elseif ( ! empty( $pats ) ) {
            $or_likes = array();
            foreach ( $pats as $p ) {
                $or_likes[] = 'subject LIKE %s';
                $params[]   = '%' . $wpdb->esc_like( $p ) . '%';
            }
            $where[] = '(' . implode( ' OR ', $or_likes ) . ')';
        }
    }

    $where_sql = implode( ' AND ', $where );
    $offset    = ( $current_page - 1 ) * $per_page;
    $count_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}wph_email_logs WHERE {$where_sql}";
    $log_total = empty( $params ) ? (int) $wpdb->get_var( $count_sql ) : (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );
    $log_pages = (int) ceil( $log_total / $per_page );
    $data_sql  = "SELECT id, to_email, subject, status, created_at FROM {$wpdb->prefix}wph_email_logs WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
    $dp        = array_merge( $params, array( $per_page, $offset ) );
    $data_sql  = "SELECT id, to_email, subject, status, headers, created_at FROM {$wpdb->prefix}wph_email_logs WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
    $log_rows  = empty( $params )
        ? $wpdb->get_results( $wpdb->prepare( "SELECT id,to_email,subject,status,headers,created_at FROM {$wpdb->prefix}wph_email_logs ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset ) )
        : $wpdb->get_results( $wpdb->prepare( $data_sql, $dp ) );

    $base_url = admin_url( 'admin.php?page=mb-wphelper-smtp&subtab=email-log' );

    $guess_type = function ( $subject, $headers = '' ) {
        $s = mb_strtolower( $subject );
        $h = mb_strtolower( $headers );
        if ( strpos( $s, 'woocommerce' ) !== false || strpos( $s, 'đơn hàng' ) !== false || strpos( $s, 'order' ) !== false
             || strpos( $h, 'woocommerce' ) !== false ) return 'WooCommerce';
        if ( strpos( $s, 'đặt lại' ) !== false || strpos( $s, 'reset' ) !== false || strpos( $s, 'password' ) !== false ) return 'Đặt lại MK';
        if ( strpos( $s, 'đăng ký' ) !== false || strpos( $s, 'register' ) !== false || strpos( $s, 'verify' ) !== false || strpos( $s, 'confirm' ) !== false ) return 'Đăng ký';
        if ( strpos( $s, 'bản tin' ) !== false || strpos( $s, 'newsletter' ) !== false ) return 'Bản tin';
        if ( strpos( $s, 'form' ) !== false || strpos( $s, 'liên hệ' ) !== false || strpos( $s, 'contact' ) !== false
             || strpos( $h, 'contact form 7' ) !== false || strpos( $h, 'contact-form-7' ) !== false || strpos( $h, 'wpcf7' ) !== false ) return 'Form liên hệ';
        if ( strpos( $s, 'bảo mật' ) !== false || strpos( $s, 'security' ) !== false ) return 'Bảo mật';
        return 'Hệ thống';
    };

    // Type badge colors
    $type_colors = array(
        'WooCommerce'   => array( '#f0fdf4', '#16a34a' ),
        'Form liên hệ' => array( '#eff6ff', '#2563eb' ),
        'Đăng ký'      => array( '#fffbeb', '#d97706' ),
        'Đặt lại MK'   => array( '#fef2f2', '#dc2626' ),
        'Bản tin'       => array( '#f5f3ff', '#7c3aed' ),
        'Bảo mật'       => array( '#fff7ed', '#ea580c' ),
        'Hệ thống'      => array( '#f8fafc', '#64748b' ),
    );

    $type_display_names = array(
        'WooCommerce'  => 'WooCommerce',
        'Form liên hệ' => __( 'Form liên hệ', 'whp' ),
        'Đăng ký TK'  => __( 'Đăng ký TK', 'whp' ),
        'Khác'         => __( 'Khác', 'whp' ),
        'Đăng ký'      => __( 'Đăng ký', 'whp' ),
        'Đặt lại MK'   => __( 'Đặt lại MK', 'whp' ),
        'Bản tin'       => __( 'Bản tin', 'whp' ),
        'Bảo mật'       => __( 'Bảo mật', 'whp' ),
        'Hệ thống'      => __( 'Hệ thống', 'whp' ),
    );

    $get_type_badge = function ( $type ) use ( $type_colors, $type_display_names ) {
        $c    = $type_colors[ $type ] ?? array( '#f8fafc', '#64748b' );
        $label = $type_display_names[ $type ] ?? $type;
        return sprintf(
            '<span style="background:%s;color:%s;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;">%s</span>',
            esc_attr( $c[0] ), esc_attr( $c[1] ), esc_html( $label )
        );
    };

    $render_trend = function ( $t, $cmp ) {
        if ( $t['dir'] === 'none' ) {
            echo '<span class="wph-el2-trend-none">— <span class="wph-el2-trend-cmp">' . esc_html( $cmp ) . '</span></span>';
            return;
        }
        if ( $t['dir'] === 'new' ) {
            echo '<span class="wph-el2-trend-up">✦ ' . esc_html__( 'Mới', 'whp' ) . ' <span class="wph-el2-trend-cmp">' . esc_html( $cmp ) . '</span></span>';
            return;
        }
        $arrow = $t['dir'] === 'up' ? '↑' : '↓';
        $cls   = $t['dir'] === 'up' ? 'wph-el2-trend-up' : 'wph-el2-trend-down';
        echo '<span class="' . $cls . '">' . $arrow . $t['pct'] . '% <span class="wph-el2-trend-cmp">' . esc_html( $cmp ) . '</span></span>';
    };

    // Donut chart (conic-gradient)
    $donut_segments = array(
        'Form liên hệ' => '#2563eb',
        'WooCommerce'  => '#16a34a',
        'Đăng ký TK'  => '#d97706',
        'Khác'         => '#7c3aed',
    );
    $donut_gradient_parts = array();
    $donut_cumulative     = 0;
    foreach ( $donut_segments as $type_name => $color ) {
        $count = $type_counts[ $type_name ] ?? 0;
        if ( $count <= 0 || $type_total <= 0 ) continue;
        $end_pct = $donut_cumulative + round( $count / $type_total * 100 );
        $donut_gradient_parts[] = "{$color} {$donut_cumulative}% {$end_pct}%";
        $donut_cumulative = $end_pct;
    }
    $donut_gradient = ! empty( $donut_gradient_parts )
        ? 'conic-gradient(' . implode( ', ', $donut_gradient_parts ) . ')'
        : 'conic-gradient(#e2e8f0 0% 100%)';

    // Filter dropdown states
    $status_dd_cfg = array(
        'sent'    => array( '#16a34a', __( 'Thành công', 'whp' ) ),
        'failed'  => array( '#dc2626', __( 'Thất bại', 'whp' ) ),
        'pending' => array( '#d97706', __( 'Chờ gửi', 'whp' ) ),
    );
    $status_dd_dot   = '#dc2626';
    $status_dd_label = __( 'Tất cả trạng thái', 'whp' );
    if ( $filter_status && isset( $status_dd_cfg[ $filter_status ] ) ) {
        $status_dd_dot   = $status_dd_cfg[ $filter_status ][0];
        $status_dd_label = $status_dd_cfg[ $filter_status ][1];
    }

    $type_dd_cfg = array(
        'woocommerce' => array( '#16a34a', 'WooCommerce' ),
        'form'        => array( '#2563eb', __( 'Form liên hệ', 'whp' ) ),
        'register'    => array( '#d97706', __( 'Đăng ký', 'whp' ) ),
        'reset'       => array( '#dc2626', __( 'Đặt lại MK', 'whp' ) ),
        'newsletter'  => array( '#7c3aed', __( 'Bản tin', 'whp' ) ),
        'system'      => array( '#64748b', __( 'Hệ thống', 'whp' ) ),
    );
    $type_dd_dot   = '#dc2626';
    $type_dd_label = __( 'Tất cả loại email', 'whp' );
    if ( $filter_type && isset( $type_dd_cfg[ $filter_type ] ) ) {
        $type_dd_dot   = $type_dd_cfg[ $filter_type ][0];
        $type_dd_label = $type_dd_cfg[ $filter_type ][1];
    }
    ?>

<style>
/* ── Wrapper ── */
.wph-el2-wrap{max-width:1200px;margin:0 auto;padding:0 0 40px;font-family:inherit;}
.wph-el2-sidebar{min-width:0;}

/* ── Notice ── */
.wph-el2-notice{padding:14px 20px;border-radius:8px;font-size:13.5px;font-weight:500;margin-bottom:16px;display:none;align-items:center;gap:12px;border-left:5px solid transparent;box-shadow:0 4px 12px rgba(0,0,0,.04);}
.wph-el2-notice.success{background:#f0fdf4;color:#166534;border-color:#bbf7d0;border-left-color:#16a34a;}
.wph-el2-notice.error{background:#fef2f2;color:#991b1b;border-color:#fecaca;border-left-color:#dc2626;}
.wph-el2-notice.info{background:#f0fdf4;color:#166534;border-color:#bbf7d0;border-left-color:#16a34a;}
@keyframes el2SlideIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}

/* ── Header hero ── */
.wph-el2-header{position:relative;background:linear-gradient(100deg,#ffffff 0%,#f0fdf4 45%,#dcfce7 100%);border-radius:20px;box-shadow:0 4px 24px rgba(22,163,74,.12),0 0 0 1px #bbf7d0;margin-bottom:20px;overflow:hidden;min-height:168px;display:flex;align-items:stretch;}
.wph-el2-header-left{position:relative;z-index:2;padding:32px 36px;display:flex;flex-direction:column;justify-content:center;gap:14px;max-width:500px;flex-shrink:0;}
.wph-el2-header-title-row{display:flex;align-items:center;gap:14px;}
.wph-el2-header-icon-box{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#16a34a,#22c55e);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(22,163,74,.35);}
.wph-el2-header-right{position:absolute;inset:0 0 0 38%;overflow:hidden;pointer-events:none;}
@media(max-width:900px){.wph-el2-header-right{display:none;}.wph-el2-body{grid-template-columns:1fr!important;}.wph-el2-stats{grid-template-columns:repeat(3,1fr)!important;}}
@media(max-width:600px){.wph-el2-stats{grid-template-columns:repeat(2,1fr)!important;}}

/* ── Stats grid ── */
.wph-el2-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;}
.wph-el2-stat-card{background:#fff;border-radius:14px;border:1px solid #f1f5f9;padding:18px 20px 14px;box-shadow:0 1px 4px rgba(0,0,0,.05);display:flex;flex-direction:row;align-items:flex-start;gap:14px;transition:box-shadow .15s;}
.wph-el2-stat-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.09);}
.wph-el2-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;}
.wph-el2-stat-body{flex:1;min-width:0;}
.wph-el2-stat-num{font-size:28px;font-weight:800;line-height:1;letter-spacing:-.5px;}
.wph-el2-stat-lbl{font-size:12px;color:#64748b;margin-top:4px;font-weight:500;}
.wph-el2-stat-trend{display:flex;align-items:center;gap:4px;margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9;font-size:11.5px;font-weight:600;flex-wrap:wrap;}
.wph-el2-trend-up{color:#16a34a;}
.wph-el2-trend-down{color:#dc2626;}
.wph-el2-trend-none{color:#94a3b8;}
.wph-el2-trend-cmp{font-weight:400;color:#94a3b8;margin-left:2px;}

/* ── Body 2-col ── */
.wph-el2-body{display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;}
.wph-el2-main{min-width:0;}
.wph-el2-save-bar{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:20px;box-shadow:0 4px 20px -2px rgba(15,23,42,.03);}
.wph-el2-save-bar-hint{font-size:12.5px;color:#64748b;display:flex;align-items:center;gap:6px;}
.wph-el2-save-bar-btn{background:linear-gradient(135deg,#3858e9 0%,#2563eb 100%);color:#fff;border:none;border-radius:9px;padding:11px 32px;font-size:13.5px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:7px;box-shadow:0 4px 14px rgba(56,88,233,.35);transition:all .2s;letter-spacing:.2px;flex-shrink:0;}
.wph-el2-save-bar-btn:hover{background:linear-gradient(135deg,#2563eb,#1d4ed8);transform:translateY(-1px);box-shadow:0 6px 20px rgba(56,88,233,.4);}

/* ── Cards ── */
.wph-el2-card{background:#fff;border-radius:14px;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px;}
.wph-el2-card:last-child{margin-bottom:0;}
.wph-el2-card-head{padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:10px;}
.wph-el2-card-head h3{margin:0;font-size:15px;font-weight:700;color:#1e293b;}

/* ── Filter card ── */
.wph-el2-filter-row{display:flex;flex-wrap:nowrap;gap:8px;align-items:center;padding:12px 14px;overflow-x:auto;}
.wph-el2-filter-row-2{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;padding:12px 20px;}
.wph-el2-fg label{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:4px;}
.wph-el2-input{border:1px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:13px;height:36px;font-family:inherit;background:#fff;box-shadow:none;outline:none;color:#0f172a;transition:border-color .2s,box-shadow .2s;box-sizing:border-box;}
.wph-el2-input:focus{border-color:#16a34a;box-shadow:0 0 0 2px rgba(22,163,74,.12);}
/* Override WP admin high-specificity input styles */
.wph-el2-filter-row .wph-el2-input{height:36px!important;padding:0 12px!important;box-shadow:none!important;min-height:unset!important;border-radius:8px!important;border:1px solid #e2e8f0!important;color:#0f172a!important;}
.wph-el2-select{border:1.5px solid #e2e8f0!important;border-radius:8px!important;padding:7px 10px;font-size:13px;height:36px;font-family:inherit;background:#fff!important;color:#374151!important;outline:none;cursor:pointer;transition:border-color .15s,box-shadow .15s;box-shadow:none!important;}
.wph-el2-select:focus{border-color:#16a34a!important;box-shadow:0 0 0 2px rgba(22,163,74,.12)!important;outline:none!important;}
.wph-el2-select:focus{border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.1);}
/* custom dropdown */
.wph-el2-dd{position:relative;user-select:none;flex-shrink:0;}
.wph-el2-dd-trigger{display:flex;align-items:center;gap:7px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:0 10px;height:36px;cursor:pointer;font-size:13px;color:#374151;transition:border-color .2s,box-shadow .2s;min-width:140px;}
.wph-el2-dd-trigger:hover{border-color:#94a3b8;}
.wph-el2-dd.open .wph-el2-dd-trigger{border-color:#16a34a;box-shadow:0 0 0 2px rgba(22,163,74,.12);}
.wph-el2-dd-trigger .el2-dd-label{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.wph-el2-dd-trigger .el2-dd-chevron{color:#94a3b8;transition:transform .2s;flex-shrink:0;}
.wph-el2-dd.open .el2-dd-chevron{transform:rotate(180deg);}
.wph-el2-dd-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;display:inline-block;transition:background .2s;}
.wph-el2-dd-menu{display:none;position:fixed;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 4px 6px -1px rgba(0,0,0,.08),0 10px 24px rgba(0,0,0,.12);z-index:999999;min-width:170px;overflow:hidden;}
.wph-el2-dd.open .wph-el2-dd-menu{display:block;}
.wph-el2-dd-opt{display:flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer;font-size:13px;color:#374151;transition:background .12s;}
.wph-el2-dd-opt:hover{background:#f8fafc;}
.wph-el2-dd-opt.selected{background:#f0fdf4;color:#16a34a;font-weight:600;}
.wph-el2-dd-opt.selected .wph-el2-dd-dot{box-shadow:0 0 0 2px rgba(22,163,74,.2);}
/* date range popover */
.wph-el2-daterange-wrap{position:relative;}
.wph-el2-daterange{display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;color:#374151;cursor:pointer;white-space:nowrap;transition:border-color .15s,color .15s;height:36px;box-sizing:border-box;}
.wph-el2-daterange:hover,.wph-el2-daterange.open{border-color:#16a34a;color:#16a34a;}
.wph-el2-daterange.is-active{border-color:#16a34a;color:#16a34a;}
.wph-el2-date-popover{display:none;position:fixed;z-index:100000;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 8px 28px rgba(0,0,0,.13);padding:14px 16px;min-width:230px;}
.wph-el2-date-popover.open{display:block;}
.wph-el2-date-row{display:flex;flex-direction:column;gap:4px;margin-bottom:10px;}
.wph-el2-date-row label{font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;}
.wph-el2-date-row input[type="date"]{width:100%;height:32px;border:1px solid #e2e8f0!important;border-radius:7px!important;padding:0 8px!important;font-size:12px!important;color:#374151!important;background:#f8fafc!important;box-shadow:none!important;cursor:pointer;}
.wph-el2-date-row input[type="date"]:focus{border-color:#16a34a!important;outline:none!important;}
.wph-el2-date-actions{display:flex;gap:7px;margin-top:6px;}
.wph-el2-date-apply{flex:1;padding:7px 12px;border:none;border-radius:7px;background:#16a34a;color:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;}
.wph-el2-date-apply:hover{background:#15803d;}
.wph-el2-date-clear{padding:7px 10px;border:1px solid #e2e8f0;border-radius:7px;background:#fff;color:#64748b;font-size:12px;cursor:pointer;font-family:inherit;}
.wph-el2-date-clear:hover{background:#f8fafc;}
/* Chart segmented */
.wph-el2-chart-seg{display:flex;border:1.5px solid #e2e8f0;border-radius:8px;overflow:hidden;}
.wph-el2-chart-seg-btn{padding:4px 12px;font-size:12px;font-weight:600;color:#64748b;background:#fff;border:none;cursor:pointer;transition:background .15s,color .15s;line-height:22px;font-family:inherit;}
.wph-el2-chart-seg-btn.active{background:#16a34a;color:#fff;}
.wph-el2-chart-seg-btn:not(:first-child){border-left:1.5px solid #e2e8f0;}
/* Settings card rows */
.wph-el2-setting-row{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid #f8fafc;}
.wph-el2-setting-row:last-child{border-bottom:none;}
.wph-el2-setting-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.wph-el2-setting-info{flex:1;min-width:0;}
.wph-el2-setting-lbl{font-size:13px;font-weight:600;color:#1e293b;}
.wph-el2-setting-desc{font-size:11px;color:#94a3b8;margin-top:2px;}

/* ── Buttons ── */
.wph-el2-btn{padding:8px 14px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:600;height:36px;display:inline-flex;align-items:center;gap:5px;font-family:inherit;transition:background .15s,box-shadow .15s;}
.wph-el2-btn-primary{background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;box-shadow:0 2px 8px rgba(22,163,74,.3);}
.wph-el2-btn-primary:hover{background:linear-gradient(135deg,#15803d,#166534);transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,.4);}
.wph-el2-btn-outline{background:#fff;color:#475569;border:1px solid #e2e8f0;}
.wph-el2-btn-outline:hover{background:#f8fafc;}
.wph-el2-btn-blue-outline{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;}
.wph-el2-btn-blue-outline:hover{background:#dcfce7;}
.wph-el2-btn-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
.wph-el2-btn-danger:hover{background:#fee2e2;}
.wph-el2-btn-sm{padding:5px 10px;font-size:12px;height:28px;border-radius:6px;}
.wph-el2-btn-icon{width:28px;height:28px;padding:0;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;border:none;cursor:pointer;font-size:12px;transition:background .15s;}
.wph-el2-btn-icon-view{background:#f1f5f9;color:#475569;}
.wph-el2-btn-icon-view:hover{background:#e2e8f0;}
.wph-el2-btn-icon-resend{background:#f0fdf4;color:#16a34a;}
.wph-el2-btn-icon-resend:hover{background:#dcfce7;}
.wph-el2-btn-icon-delete{background:#fef2f2;color:#dc2626;}
.wph-el2-btn-icon-delete:hover{background:#fee2e2;}

/* ── Table ── */
.wph-el2-table{width:100%;border-collapse:collapse;font-size:13px;}
.wph-el2-table th{background:#f8fafc;padding:10px 14px;text-align:left;font-weight:700;font-size:10.5px;text-transform:uppercase;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap;letter-spacing:.4px;}
.wph-el2-table td{padding:11px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.wph-el2-table tr:last-child td{border-bottom:none;}
.wph-el2-table tr:hover td{background:#fafafa;}
.wph-el2-table input[type=checkbox]{accent-color:#16a34a;width:14px;height:14px;cursor:pointer;}

/* ── Status badges ── */
.wph-el2-badge-sent{background:#f0fdf4;color:#16a34a;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;}
.wph-el2-badge-failed{background:#fef2f2;color:#dc2626;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;}
.wph-el2-badge-pending{background:#fffbeb;color:#d97706;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;}

/* ── Bulk bar ── */
.wph-el2-bulk-bar{display:none;align-items:center;gap:12px;padding:10px 20px;background:#f0fdf4;border-bottom:1px solid #bbf7d0;font-size:13px;color:#166534;font-weight:600;}
.wph-el2-bulk-bar.visible{display:flex;}

/* ── Pagination ── */
.wph-el2-pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f1f5f9;font-size:13px;color:#64748b;flex-wrap:wrap;gap:8px;}
.wph-el2-page-links{display:flex;gap:4px;flex-wrap:wrap;}
.wph-el2-page-links a,.wph-el2-page-links span{padding:5px 10px;border-radius:6px;border:1px solid #e2e8f0;text-decoration:none;color:#475569;font-size:13px;}
.wph-el2-page-links a:hover{background:#f8fafc;}
.wph-el2-page-links span.current{background:#16a34a;color:#fff;border-color:#16a34a;font-weight:700;}
.wph-el2-page-links span.dots{border:none;color:#94a3b8;}

/* ── SMTP sidebar card ── */
.wph-el2-info-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f8fafc;font-size:13px;}
.wph-el2-info-row:last-child{border-bottom:none;}
.wph-el2-info-label{color:#64748b;font-weight:500;flex-shrink:0;}
.wph-el2-info-val{color:#1e293b;font-weight:600;text-align:right;word-break:break-all;overflow-wrap:anywhere;min-width:0;}

/* ── Donut chart ── */
.wph-el2-donut-wrap{display:flex;gap:16px;align-items:center;padding:14px 20px;}
.wph-el2-donut-legend{flex:1;min-width:0;display:flex;flex-direction:column;gap:7px;}
.wph-el2-legend-item{display:flex;align-items:center;gap:8px;}
.wph-el2-legend-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}

/* ── Error list ── */
.wph-el2-err-item{display:flex;align-items:center;gap:10px;padding:10px 20px;border-bottom:1px solid #f8fafc;font-size:13px;}
.wph-el2-err-item:last-child{border-bottom:none;}
.wph-el2-err-dot{width:8px;height:8px;border-radius:50%;background:#dc2626;flex-shrink:0;}
.wph-el2-err-msg{flex:1;color:#1e293b;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.wph-el2-err-time{color:#94a3b8;font-size:11.5px;flex-shrink:0;}

/* ── Log settings card ── */
.wph-el2-settings-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:16px 20px 12px;}
.wph-el2-settings-label{font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;}

/* ── Modal overlay ── */
.wph-el2-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px);}
.wph-el2-modal{background:#fff;border-radius:16px;width:100%;max-width:860px;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.wph-el2-modal-head{padding:18px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;}
.wph-el2-modal-head h2{margin:0;font-size:16px;font-weight:700;color:#1e293b;}
.wph-el2-modal-body{padding:20px 24px;overflow-y:auto;display:grid;grid-template-columns:1fr 280px;gap:20px;}
.wph-el2-modal-foot{padding:14px 24px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;flex-shrink:0;}
.wph-el2-modal-close{background:none;border:none;cursor:pointer;padding:4px;color:#64748b;line-height:1;display:flex;align-items:center;border-radius:6px;}
.wph-el2-modal-close:hover{background:#f1f5f9;}
.wph-el2-email-body{background:#f8fafc;border-radius:8px;overflow:hidden;max-height:500px;overflow-y:auto;}
.wph-el2-meta-row{display:flex;gap:8px;padding:6px 0;border-bottom:1px solid #f8fafc;font-size:13px;}
.wph-el2-meta-row:last-child{border-bottom:none;}
.wph-el2-meta-dt{color:#64748b;min-width:110px;flex-shrink:0;font-weight:500;font-size:12px;}
.wph-el2-meta-dd{margin:0;word-break:break-all;color:#1e293b;}
@media(max-width:600px){.wph-el2-modal-body{grid-template-columns:1fr;}}
</style>

<div class="wph-el2-wrap">
<div id="wph-el2-notice" class="wph-el2-notice"></div>

<!-- ── HEADER HERO ─────────────────────────────────────────────────────────── -->
<div class="wph-el2-header">
    <div class="wph-el2-header-left">
        <div class="wph-el2-header-title-row">
            <div class="wph-el2-header-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.3px;"><?php esc_html_e( 'Nhật ký Email', 'whp' ); ?></h1>
        </div>
        <p style="margin:0;font-size:13.5px;color:#475569;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e( 'Theo dõi toàn bộ email gửi đi từ website của bạn. Kiểm tra trạng thái, lỗi và phân tích hiệu suất gửi email.', 'whp' ); ?></p>
        <div style="padding-left:58px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <?php if ( $smtp_active ) : ?>
            <span style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                <span style="width:7px;height:7px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                <?php esc_html_e( 'Đang hoạt động', 'whp' ); ?>
            </span>
            <span style="font-size:13px;color:#475569;">
                <?php echo esc_html( $smtp_source ); ?> — <?php esc_html_e( 'SMTP hoạt động bình thường.', 'whp' ); ?>
            </span>
            <?php else : ?>
            <span style="display:inline-flex;align-items:center;gap:5px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                <span style="width:7px;height:7px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
                <?php esc_html_e( 'Chưa phát hiện SMTP', 'whp' ); ?>
            </span>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mb-wphelper-smtp&subtab=smtp' ) ); ?>" style="font-size:13px;color:#16a34a;"><?php esc_html_e( 'Cấu hình ngay →', 'whp' ); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <!-- Decorative SVG right -->
    <div class="wph-el2-header-right">
        <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
            <defs>
                <linearGradient id="el2_hbg" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#f0fdf4" stop-opacity="0"/>
                    <stop offset="30%" stop-color="#dcfce7" stop-opacity="0.6"/>
                    <stop offset="100%" stop-color="#bbf7d0" stop-opacity="1"/>
                </linearGradient>
                <filter id="el2_shadow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(22,163,74,0.15)"/>
                </filter>
                <filter id="el2_shadowSm" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(22,163,74,0.10)"/>
                </filter>
            </defs>
            <rect width="680" height="168" fill="url(#el2_hbg)"/>
            <!-- Decorative circles -->
            <circle cx="580" cy="20" r="70" fill="#16a34a" fill-opacity=".05"/>
            <circle cx="650" cy="148" r="50" fill="#3b82f6" fill-opacity=".05"/>
            <circle cx="320" cy="84" r="95" fill="#93c5fd" fill-opacity=".04"/>
            <!-- Main envelope (center) -->
            <g filter="url(#el2_shadow)">
                <rect x="370" y="34" width="110" height="80" rx="8" fill="#fff" stroke="#bbf7d0" stroke-width="1.5"/>
                <path d="M370 48 L425 82 L480 48" stroke="#93c5fd" stroke-width="2" stroke-linecap="round" fill="none"/>
                <!-- Success check overlay -->
                <circle cx="480" cy="34" r="16" fill="#f0fdf4" stroke="#bbf7d0" stroke-width="1.5"/>
                <polyline points="473,34 479,40 488,27" stroke="#16a34a" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </g>
            <!-- Small envelope flying (upper right) -->
            <g filter="url(#el2_shadowSm)" transform="translate(555,22)">
                <rect width="68" height="48" rx="6" fill="#fff" stroke="#bbf7d0" stroke-width="1.2"/>
                <path d="M0 12L34 32L68 12" stroke="#93c5fd" stroke-width="1.8" stroke-linecap="round" fill="none"/>
            </g>
            <!-- Chart bars (left) -->
            <g filter="url(#el2_shadowSm)" transform="translate(304,60)">
                <rect width="50" height="80" rx="6" fill="#fff" stroke="#bbf7d0" stroke-width="1"/>
                <rect x="7" y="50" width="8" height="22" rx="2" fill="#93c5fd"/>
                <rect x="21" y="35" width="8" height="37" rx="2" fill="#16a34a"/>
                <rect x="35" y="42" width="8" height="30" rx="2" fill="#60a5fa"/>
            </g>
            <!-- Small padlock / security icon -->
            <g filter="url(#el2_shadowSm)" transform="translate(500,95)">
                <rect x="0" y="12" width="30" height="22" rx="5" fill="#fff" stroke="#bbf7d0" stroke-width="1.2"/>
                <path d="M6 12V8a9 9 0 0 1 18 0v4" stroke="#93c5fd" stroke-width="2.2" fill="none" stroke-linecap="round"/>
                <circle cx="15" cy="23" r="3.5" fill="#16a34a"/>
                <rect x="14" y="23" width="2" height="5" rx="1" fill="#16a34a"/>
            </g>
            <!-- Notification dots -->
            <circle cx="345" cy="50" r="5" fill="#3b82f6" fill-opacity=".3"/>
            <circle cx="332" cy="55" r="3" fill="#60a5fa" fill-opacity=".4"/>
            <circle cx="558" cy="145" r="4" fill="#16a34a" fill-opacity=".2"/>
        </svg>
    </div>
</div>

<!-- ── STATS ───────────────────────────────────────────────────────────────── -->
<div class="wph-el2-stats">
    <?php
    $stat_cards = array(
        array(
            'val'   => number_format( $total_all ),
            'lbl'   => __( 'Tổng Email', 'whp' ),
            'bg'    => '#f0fdf4',
            'color' => '#16a34a',
            'trend' => $calc_trend( $total_cur7, $total_prev7 ),
            'cmp'   => __( 'so với 7 ngày trước', 'whp' ),
            'icon'  => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        ),
        array(
            'val'   => number_format( $sent_all ),
            'lbl'   => __( 'Thành công', 'whp' ),
            'bg'    => '#f0fdf4',
            'color' => '#16a34a',
            'trend' => $calc_trend( $sent_cur7, $sent_prev7 ),
            'cmp'   => __( 'so với 7 ngày trước', 'whp' ),
            'icon'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/>',
        ),
        array(
            'val'   => number_format( $fail_all ),
            'lbl'   => __( 'Thất bại', 'whp' ),
            'bg'    => '#fef2f2',
            'color' => '#dc2626',
            'trend' => $calc_trend( $fail_cur7, $fail_prev7 ),
            'cmp'   => __( 'so với 7 ngày trước', 'whp' ),
            'icon'  => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
        ),
        array(
            'val'   => number_format( $pending_cnt ),
            'lbl'   => __( 'Chờ gửi', 'whp' ),
            'bg'    => '#fffbeb',
            'color' => '#d97706',
            'trend' => array( 'pct' => null, 'dir' => 'none' ),
            'cmp'   => __( 'so với 7 ngày trước', 'whp' ),
            'icon'  => '<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>',
        ),
        array(
            'val'   => number_format( $today_cnt ),
            'lbl'   => __( 'Hôm nay', 'whp' ),
            'bg'    => '#f0f9ff',
            'color' => '#0284c7',
            'trend' => $calc_trend( $today_cnt, $yest_cnt ),
            'cmp'   => __( 'so với hôm qua', 'whp' ),
            'icon'  => '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>',
        ),
    );
    foreach ( $stat_cards as $c ) : ?>
    <div class="wph-el2-stat-card">
        <div class="wph-el2-stat-icon" style="background:<?php echo esc_attr( $c['bg'] ); ?>;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr( $c['color'] ); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $c['icon']; ?></svg>
        </div>
        <div class="wph-el2-stat-body">
            <div class="wph-el2-stat-num" style="color:<?php echo esc_attr( $c['color'] ); ?>;"><?php echo esc_html( $c['val'] ); ?></div>
            <div class="wph-el2-stat-lbl"><?php echo esc_html( $c['lbl'] ); ?></div>
            <div class="wph-el2-stat-trend"><?php $render_trend( $c['trend'], $c['cmp'] ); ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── FILTER (full-width, above 2-col grid) ─────────────────────────────── -->
<div class="wph-el2-card" style="margin-bottom:16px;">
    <form method="get" action="<?php echo admin_url( 'admin.php' ); ?>" id="wph-el2-filter-form">
        <input type="hidden" name="page" value="mb-wphelper-smtp">
        <input type="hidden" name="subtab" value="email-log">
        <div class="wph-el2-filter-row">

            <!-- Search -->
            <input type="text" name="el_search" class="wph-el2-input" placeholder="<?php esc_attr_e( 'Tìm kiếm email, tiêu đề, nội dung...', 'whp' ); ?>" value="<?php echo esc_attr( $filter_search ); ?>" style="flex:1;min-width:160px;" onchange="document.getElementById('wph-el2-filter-form').submit()">

            <!-- Status custom dropdown -->
            <input type="hidden" name="el_status" id="el2-inp-status" value="<?php echo esc_attr( $filter_status ); ?>">
            <div class="wph-el2-dd" id="el2-dd-status">
                <div class="wph-el2-dd-trigger" onclick="wphEl2DdToggle('status',this)">
                    <span class="wph-el2-dd-dot" id="el2-dot-status" style="background:<?php echo esc_attr( $status_dd_dot ); ?>;"></span>
                    <span class="el2-dd-label" id="el2-lbl-status"><?php echo esc_html( $status_dd_label ); ?></span>
                    <svg class="el2-dd-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="wph-el2-dd-menu" id="el2-menu-status">
                    <div class="wph-el2-dd-opt <?php echo !$filter_status ? 'selected' : ''; ?>" data-value="" data-dot="#dc2626" onclick="wphEl2DdSelect('status',this)">
                        <span class="wph-el2-dd-dot" style="background:#dc2626;"></span><?php esc_html_e( 'Tất cả trạng thái', 'whp' ); ?>
                    </div>
                    <?php foreach ( $status_dd_cfg as $v => $sc ) : ?>
                    <div class="wph-el2-dd-opt <?php echo $filter_status === $v ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $v ); ?>" data-dot="<?php echo esc_attr( $sc[0] ); ?>" onclick="wphEl2DdSelect('status',this)">
                        <span class="wph-el2-dd-dot" style="background:<?php echo esc_attr( $sc[0] ); ?>;"></span><?php echo esc_html( $sc[1] ); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Type custom dropdown -->
            <input type="hidden" name="el_type" id="el2-inp-type" value="<?php echo esc_attr( $filter_type ); ?>">
            <div class="wph-el2-dd" id="el2-dd-type">
                <div class="wph-el2-dd-trigger" onclick="wphEl2DdToggle('type',this)">
                    <span class="wph-el2-dd-dot" id="el2-dot-type" style="background:<?php echo esc_attr( $type_dd_dot ); ?>;"></span>
                    <span class="el2-dd-label" id="el2-lbl-type"><?php echo esc_html( $type_dd_label ); ?></span>
                    <svg class="el2-dd-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="wph-el2-dd-menu" id="el2-menu-type">
                    <div class="wph-el2-dd-opt <?php echo !$filter_type ? 'selected' : ''; ?>" data-value="" data-dot="#dc2626" onclick="wphEl2DdSelect('type',this)">
                        <span class="wph-el2-dd-dot" style="background:#dc2626;"></span><?php esc_html_e( 'Tất cả loại email', 'whp' ); ?>
                    </div>
                    <?php foreach ( $type_dd_cfg as $v => $tc ) : ?>
                    <div class="wph-el2-dd-opt <?php echo $filter_type === $v ? 'selected' : ''; ?>" data-value="<?php echo esc_attr( $v ); ?>" data-dot="<?php echo esc_attr( $tc[0] ); ?>" onclick="wphEl2DdSelect('type',this)">
                        <span class="wph-el2-dd-dot" style="background:<?php echo esc_attr( $tc[0] ); ?>;"></span><?php echo esc_html( $tc[1] ); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Date range popover -->
            <?php
            function _wph_el2_fmt_date($d){ if(!$d) return ''; $p=explode('-',$d); return isset($p[2])?($p[2].'/'.$p[1].'/'.$p[0]):''; }
            $el2_lbl_from = _wph_el2_fmt_date($filter_date_from);
            $el2_lbl_to   = _wph_el2_fmt_date($filter_date_to);
            $el2_lbl      = ($filter_date_from || $filter_date_to)
                ? (($el2_lbl_from ?: '…') . ' – ' . ($el2_lbl_to ?: '…'))
                : __( 'Tất cả ngày', 'whp' );
            ?>
            <div class="wph-el2-daterange-wrap">
                <div class="wph-el2-daterange<?php echo ($filter_date_from || $filter_date_to) ? ' is-active' : ''; ?>" id="el2-daterange-trigger">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span id="el2-date-label"><?php echo esc_html($el2_lbl); ?></span>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="wph-el2-date-popover" id="el2-date-popover">
                    <div class="wph-el2-date-row">
                        <label><?php esc_html_e( 'Từ ngày', 'whp' ); ?></label>
                        <input type="date" name="el_date_from" id="el2-inp-from" value="<?php echo esc_attr($filter_date_from); ?>">
                    </div>
                    <div class="wph-el2-date-row">
                        <label><?php esc_html_e( 'Đến ngày', 'whp' ); ?></label>
                        <input type="date" name="el_date_to" id="el2-inp-to" value="<?php echo esc_attr($filter_date_to); ?>">
                    </div>
                    <div class="wph-el2-date-actions">
                        <button type="button" class="wph-el2-date-clear" id="el2-date-clear"><?php esc_html_e( 'Xóa', 'whp' ); ?></button>
                        <button type="submit" class="wph-el2-date-apply"><?php esc_html_e( 'Áp dụng', 'whp' ); ?></button>
                    </div>
                </div>
            </div>

            <!-- Search button -->
            <button type="submit" class="wph-el2-btn" style="background:#16a34a;color:#fff;border:none;border-radius:8px;width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;padding:0;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>

            <?php if ( $filter_search || $filter_status || $filter_type || $filter_date_from || $filter_date_to ) : ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mb-wphelper-smtp&subtab=email-log' ) ); ?>" style="display:inline-flex;align-items:center;width:36px;height:36px;justify-content:center;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#94a3b8;text-decoration:none;" title="<?php esc_attr_e( 'Xóa bộ lọc', 'whp' ); ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </a>
            <?php endif; ?>

            <!-- CSV button -->
            <button type="button" onclick="wphEl2ExportCsv()" style="display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:36px;border:1.5px solid #16a34a;border-radius:8px;background:#f0fdf4;color:#16a34a;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:inherit;transition:background .15s;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <?php esc_html_e( 'Xuất CSV', 'whp' ); ?>
            </button>

        </div>
    </form>
</div>

<!-- ── BODY 2-COL ────────────────────────────────────────────────────────── -->
<div class="wph-el2-body">

    <!-- LEFT COLUMN -->
    <div class="wph-el2-main">

        <!-- Table card -->
        <div class="wph-el2-card">
            <div class="wph-el2-card-head">
                <h3>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:5px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?php esc_html_e( 'Nhật ký Email', 'whp' ); ?>
                    <?php if ( $log_total > 0 ) : ?>
                    <span style="background:#f0fdf4;color:#16a34a;padding:1px 8px;border-radius:20px;font-size:11px;font-weight:700;margin-left:6px;"><?php echo number_format( $log_total ); ?></span>
                    <?php endif; ?>
                </h3>
                <div style="display:flex;gap:8px;">
                    <button id="wph-el2-bulk-delete-top" class="wph-el2-btn wph-el2-btn-danger wph-el2-btn-sm" onclick="wphEl2BulkDelete()" style="display:none;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        <?php esc_html_e( 'Xóa đã chọn', 'whp' ); ?>
                    </button>
                </div>
            </div>

            <!-- Bulk bar -->
            <div class="wph-el2-bulk-bar" id="wph-el2-bulk-bar">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span id="wph-el2-bulk-count">0 <?php esc_html_e( 'đã chọn', 'whp' ); ?></span>
                <button class="wph-el2-btn wph-el2-btn-danger wph-el2-btn-sm" onclick="wphEl2BulkDelete()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                    <?php esc_html_e( 'Xóa đã chọn', 'whp' ); ?>
                </button>
                <button class="wph-el2-btn wph-el2-btn-outline wph-el2-btn-sm" onclick="wphEl2DeselectAll()"><?php esc_html_e( 'Bỏ chọn', 'whp' ); ?></button>
            </div>

            <div style="overflow-x:auto;">
            <?php if ( empty( $log_rows ) ) : ?>
            <div style="text-align:center;padding:60px 20px;color:#94a3b8;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 14px;">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <p style="margin:0;font-size:14px;font-weight:600;"><?php esc_html_e( 'Chưa có email nào được ghi lại.', 'whp' ); ?></p>
                <p style="margin:6px 0 0;font-size:13px;"><?php esc_html_e( 'Email log sẽ xuất hiện ở đây khi website gửi email.', 'whp' ); ?></p>
            </div>
            <?php else : ?>
            <table class="wph-el2-table">
                <thead>
                    <tr>
                        <th style="width:36px;padding-left:18px;"><input type="checkbox" id="wph-el2-check-all" onchange="wphEl2SelAll(this)" title="<?php esc_attr_e( 'Chọn tất cả', 'whp' ); ?>"></th>
                        <th style="width:65px;">ID</th>
                        <th><?php esc_html_e( 'Người nhận', 'whp' ); ?></th>
                        <th><?php esc_html_e( 'Tiêu đề', 'whp' ); ?></th>
                        <th style="width:110px;"><?php esc_html_e( 'Loại', 'whp' ); ?></th>
                        <th style="width:100px;"><?php esc_html_e( 'Trạng thái', 'whp' ); ?></th>
                        <th style="width:130px;"><?php esc_html_e( 'Thời gian gửi', 'whp' ); ?></th>
                        <th style="width:90px;"><?php esc_html_e( 'Thao tác', 'whp' ); ?></th>
                    </tr>
                </thead>
                <tbody id="wph-el2-tbody">
                <?php foreach ( $log_rows as $r ) :
                    $type        = $guess_type( $r->subject, $r->headers ?? '' );
                    $type_badge  = $get_type_badge( $type );
                    $subj_short  = mb_strlen( $r->subject ) > 58 ? mb_substr( $r->subject, 0, 55 ) . '…' : $r->subject;
                    $dt          = date_i18n( 'd/m/Y H:i', strtotime( $r->created_at ) );
                ?>
                <tr id="el2-row-<?php echo esc_attr( $r->id ); ?>">
                    <td style="padding-left:18px;"><input type="checkbox" class="wph-el2-row-cb" value="<?php echo esc_attr( $r->id ); ?>" onchange="wphEl2UpdateBulkBar()"></td>
                    <td style="color:#94a3b8;font-size:12px;">#<?php echo esc_html( $r->id ); ?></td>
                    <td style="color:#16a34a;font-weight:500;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html( $r->to_email ); ?></td>
                    <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#374151;"><?php echo esc_html( $subj_short ); ?></td>
                    <td><?php echo $type_badge; ?></td>
                    <td>
                        <?php if ( $r->status === 'sent' ) : ?>
                        <span class="wph-el2-badge-sent"><?php esc_html_e( 'Thành công', 'whp' ); ?></span>
                        <?php elseif ( $r->status === 'failed' ) : ?>
                        <span class="wph-el2-badge-failed"><?php esc_html_e( 'Thất bại', 'whp' ); ?></span>
                        <?php else : ?>
                        <span class="wph-el2-badge-pending"><?php esc_html_e( 'Chờ gửi', 'whp' ); ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:#64748b;"><?php echo esc_html( $dt ); ?></td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <button class="wph-el2-btn-icon wph-el2-btn-icon-view" onclick="wphEl2ViewDetail(<?php echo esc_js( $r->id ); ?>)" title="<?php esc_attr_e( 'Xem chi tiết', 'whp' ); ?>">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="wph-el2-btn-icon wph-el2-btn-icon-resend" onclick="wphEl2Resend(<?php echo esc_js( $r->id ); ?>)" title="<?php esc_attr_e( 'Gửi lại', 'whp' ); ?>">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1,4 1,10 7,10"/><path d="M3.51 15a9 9 0 1 0 .49-4"/></svg>
                            </button>
                            <button class="wph-el2-btn-icon wph-el2-btn-icon-delete" onclick="wphEl2Delete(<?php echo esc_js( $r->id ); ?>, this.closest('tr'))" title="<?php esc_attr_e( 'Xóa', 'whp' ); ?>">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            </div>

            <?php if ( $log_total > 0 ) :
                $from_item = ( $current_page - 1 ) * $per_page + 1;
                $to_item   = min( $current_page * $per_page, $log_total );
                $base_paginate = add_query_arg( array(
                    'el_status'    => $filter_status,
                    'el_search'    => urlencode( $filter_search ),
                    'el_date_from' => $filter_date_from,
                    'el_date_to'   => $filter_date_to,
                    'el_type'      => $filter_type,
                ), $base_url );
            ?>
            <div class="wph-el2-pagination">
                <span><?php printf( esc_html__( 'Hiển thị %1$s–%2$s của %3$s', 'whp' ), esc_html( $from_item ), esc_html( $to_item ), number_format( $log_total ) ); ?></span>
                <div class="wph-el2-page-links">
                    <?php if ( $current_page > 1 ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'el_page', $current_page - 1, $base_paginate ) ); ?>">‹</a>
                    <?php endif; ?>
                    <?php
                    $range     = 2;
                    $show_dots = false;
                    for ( $p = 1; $p <= $log_pages; $p++ ) :
                        if ( $p === 1 || $p === $log_pages || ( $p >= $current_page - $range && $p <= $current_page + $range ) ) :
                            $show_dots = false;
                            if ( $p === $current_page ) :
                    ?>
                    <span class="current"><?php echo $p; ?></span>
                    <?php       else : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'el_page', $p, $base_paginate ) ); ?>"><?php echo $p; ?></a>
                    <?php       endif;
                        elseif ( ! $show_dots ) :
                            $show_dots = true;
                    ?>
                    <span class="dots">…</span>
                    <?php   endif;
                    endfor; ?>
                    <?php if ( $current_page < $log_pages ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'el_page', $current_page + 1, $base_paginate ) ); ?>">›</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Chart card -->
        <div class="wph-el2-card" style="margin-top:16px;">
            <div class="wph-el2-card-head">
                <h3>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:5px;"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/></svg>
                    <?php esc_html_e( 'Biểu đồ gửi email', 'whp' ); ?>
                </h3>
                <div class="wph-el2-chart-seg" id="el2-chart-period-seg">
                    <button type="button" class="wph-el2-chart-seg-btn active" onclick="wphEl2SetPeriod(7,this)"><?php esc_html_e( '7 ngày', 'whp' ); ?></button>
                    <button type="button" class="wph-el2-chart-seg-btn" onclick="wphEl2SetPeriod(14,this)"><?php esc_html_e( '14 ngày', 'whp' ); ?></button>
                    <button type="button" class="wph-el2-chart-seg-btn" onclick="wphEl2SetPeriod(30,this)"><?php esc_html_e( '30 ngày', 'whp' ); ?></button>
                </div>
            </div>
            <div style="padding:10px 20px 6px;display:flex;gap:16px;align-items:center;font-size:12px;">
                <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:3px;background:#16a34a;border-radius:2px;display:inline-block;"></span> <?php esc_html_e( 'Thành công', 'whp' ); ?></span>
                <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:3px;background:#dc2626;border-radius:2px;display:inline-block;"></span> <?php esc_html_e( 'Thất bại', 'whp' ); ?></span>
            </div>
            <div id="el2-chart-container" style="height:220px;position:relative;padding:0;"></div>
        </div>

    </div><!-- /main -->

    <!-- RIGHT COLUMN / SIDEBAR -->
    <div class="wph-el2-sidebar">

        <!-- SMTP card -->
        <div class="wph-el2-card">
            <div class="wph-el2-card-head">
                <h3><?php esc_html_e( 'SMTP đang sử dụng', 'whp' ); ?></h3>
                <?php if ( $smtp_active ) : ?>
                <span style="background:#f0fdf4;color:#16a34a;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;display:flex;align-items:center;gap:4px;">
                    <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span><?php esc_html_e( 'Hoạt động', 'whp' ); ?>
                </span>
                <?php else : ?>
                <span style="background:#f8fafc;color:#64748b;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;"><?php esc_html_e( 'Tắt', 'whp' ); ?></span>
                <?php endif; ?>
            </div>
            <div style="padding:4px 20px 16px;">
                <?php if ( $smtp_active ) : ?>
                <?php
                $smtp_rows = array(
                    'Plugin'                        => $smtp_source ?: 'WP Helper Lite',
                    __( 'Máy chủ', 'whp' )          => $smtp_host ?: '—',
                    __( 'Cổng', 'whp' )             => $smtp_port ?: '—',
                    __( 'Bảo mật', 'whp' )          => $smtp_enc  ?: '—',
                    __( 'Người gửi', 'whp' )        => $smtp_from ?: '—',
                );
                foreach ( $smtp_rows as $label => $val ) : ?>
                <div class="wph-el2-info-row">
                    <span class="wph-el2-info-label"><?php echo esc_html( $label ); ?></span>
                    <span class="wph-el2-info-val"><?php echo esc_html( $val ); ?></span>
                </div>
                <?php endforeach; ?>
                <?php else : ?>
                <div style="padding:12px 0 8px;color:#94a3b8;font-size:13px;text-align:center;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="display:block;margin:0 auto 8px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?php esc_html_e( 'Chưa phát hiện SMTP plugin nào.', 'whp' ); ?><br>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=mb-wphelper-smtp&subtab=smtp' ) ); ?>" style="color:#16a34a;font-size:12px;"><?php esc_html_e( 'Cấu hình SMTP tại đây →', 'whp' ); ?></a>
                </div>
                <?php endif; ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=mb-wphelper-smtp&subtab=smtp#mb_smtp_test_card' ) ); ?>" style="display:flex;align-items:center;justify-content:center;gap:6px;width:100%;box-sizing:border-box;margin-top:14px;padding:8px 14px;height:36px;background:#f0fdf4;color:#16a34a;border:1.5px solid #bbf7d0;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;font-family:inherit;cursor:pointer;transition:background .15s;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>
                    <?php esc_html_e( 'Kiểm tra SMTP', 'whp' ); ?>
                </a>
            </div>
        </div>

        <!-- Donut chart card -->
        <div class="wph-el2-card">
            <div class="wph-el2-card-head">
                <h3><?php esc_html_e( 'Thống kê loại email', 'whp' ); ?></h3>
                <button onclick="wphEl2ShowTypePopup()" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:4px 10px;font-size:12px;font-weight:600;color:#16a34a;cursor:pointer;display:flex;align-items:center;gap:5px;font-family:inherit;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <?php esc_html_e( 'Chi tiết', 'whp' ); ?>
                </button>
            </div>
            <div class="wph-el2-donut-wrap">
                <!-- Donut -->
                <div style="position:relative;width:120px;height:120px;flex-shrink:0;">
                    <div style="width:120px;height:120px;border-radius:50%;background:<?php echo esc_attr( $donut_gradient ); ?>;"></div>
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                        <div style="width:74px;height:74px;background:#fff;border-radius:50%;display:flex;flex-direction:column;align-items:center;justify-content:center;box-shadow:0 0 0 1px #f1f5f9;">
                            <span style="font-size:20px;font-weight:800;color:#1e293b;line-height:1;"><?php echo number_format( $type_total ); ?></span>
                            <span style="font-size:9.5px;color:#94a3b8;margin-top:2px;white-space:nowrap;"><?php esc_html_e( 'Tổng email', 'whp' ); ?></span>
                        </div>
                    </div>
                </div>
                <!-- Legend — all types -->
                <div class="wph-el2-donut-legend">
                    <?php foreach ( $donut_segments as $lname => $lcolor ) :
                        $cnt = $type_counts[ $lname ] ?? 0;
                        $pct = $type_total > 0 ? round( $cnt / $type_total * 100 ) : 0;
                    ?>
                    <div class="wph-el2-legend-item">
                        <span class="wph-el2-legend-dot" style="background:<?php echo esc_attr( $lcolor ); ?>;opacity:<?php echo $cnt > 0 ? '1' : '0.35'; ?>;"></span>
                        <span style="flex:1;font-size:12px;color:<?php echo $cnt > 0 ? '#475569' : '#94a3b8'; ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html( $type_display_names[ $lname ] ?? $lname ); ?></span>
                        <span style="font-size:12px;font-weight:700;color:<?php echo $cnt > 0 ? '#1e293b' : '#cbd5e1'; ?>;flex-shrink:0;"><?php echo $pct; ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent errors card -->
        <div class="wph-el2-card">
            <div class="wph-el2-card-head">
                <h3 style="color:#dc2626;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:5px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?php esc_html_e( 'Lỗi gần nhất', 'whp' ); ?>
                </h3>
                <?php if ( ! empty( $recent_errors ) ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'el_status', 'failed', $base_url ) ); ?>" style="font-size:12px;color:#16a34a;text-decoration:none;font-weight:600;"><?php esc_html_e( 'Xem tất cả', 'whp' ); ?></a>
                <?php endif; ?>
            </div>
            <?php if ( empty( $recent_errors ) ) : ?>
            <div style="padding:20px;text-align:center;color:#94a3b8;font-size:13px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/></svg>
                <?php esc_html_e( 'Không có lỗi gần đây', 'whp' ); ?>
            </div>
            <?php else : ?>
            <?php foreach ( $recent_errors as $err ) :
                $err_msg  = $err->smtp_response ? mb_substr( $err->smtp_response, 0, 40 ) : ( $err->subject ? mb_substr( $err->subject, 0, 35 ) : __( 'Email thất bại', 'whp' ) );
                $err_time = date_i18n( 'd/m H:i', strtotime( $err->created_at ) );
            ?>
            <div class="wph-el2-err-item">
                <span class="wph-el2-err-dot"></span>
                <span class="wph-el2-err-msg" title="<?php echo esc_attr( $err->smtp_response ); ?>"><?php echo esc_html( $err_msg ); ?></span>
                <span class="wph-el2-err-time"><?php echo esc_html( $err_time ); ?></span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;cursor:pointer;" onclick="wphEl2ViewDetail(<?php echo esc_js( $err->id ); ?>)"><polyline points="9,18 15,12 9,6"/></svg>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Log settings card -->
        <div class="wph-el2-card">
            <div class="wph-el2-card-head">
                <div style="display:flex;align-items:center;gap:9px;">
                    <div style="width:30px;height:30px;border-radius:9px;background:linear-gradient(135deg,#eff6ff,#dbeafe);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                    </div>
                    <h3 style="margin:0;font-size:14px;font-weight:700;color:#1e293b;"><?php esc_html_e( 'Cài đặt lưu log', 'whp' ); ?></h3>
                </div>
            </div>
            <div style="padding:4px 16px 16px;display:flex;flex-direction:column;gap:10px;">

                <!-- Retention -->
                <div style="border:1.5px solid #f1f5f9;border-radius:11px;padding:13px 14px;background:#fafcff;transition:border-color .15s,box-shadow .15s;" onmouseover="this.style.borderColor='#bfdbfe';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.06)'" onmouseout="this.style.borderColor='#f1f5f9';this.style.boxShadow='none'">
                    <div style="display:flex;align-items:center;gap:11px;">
                        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 1px 4px rgba(22,163,74,.12);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span id="el2-dot-retention" style="width:8px;height:8px;border-radius:50%;flex-shrink:0;display:inline-block;background:#16a34a;transition:background .2s;"></span>
                                <div style="font-size:13px;font-weight:600;color:#1e293b;"><?php esc_html_e( 'Thời gian lưu log', 'whp' ); ?></div>
                            </div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:2px;"><?php esc_html_e( 'Tự động xóa log cũ hơn mốc này', 'whp' ); ?></div>
                        </div>
                        <select id="el2-retention" class="wph-el2-select" style="min-width:128px;flex-shrink:0;border-radius:8px;font-size:12.5px;" onchange="wphEl2UpdateSelectDot('retention',this.value,'0')">
                            <?php foreach ( array( 0 => __( 'Không giới hạn', 'whp' ), 30 => __( '30 ngày', 'whp' ), 60 => __( '60 ngày', 'whp' ), 90 => __( '90 ngày', 'whp' ), 180 => __( '180 ngày', 'whp' ), 365 => __( '365 ngày', 'whp' ) ) as $v => $l ) : ?>
                            <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $settings['retention'] ?? 0, $v ); ?>><?php echo esc_html( $l ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Max logs -->
                <div style="border:1.5px solid #f1f5f9;border-radius:11px;padding:13px 14px;background:#fafcff;transition:border-color .15s,box-shadow .15s;" onmouseover="this.style.borderColor='#fde68a';this.style.boxShadow='0 0 0 3px rgba(217,119,6,.06)'" onmouseout="this.style.borderColor='#f1f5f9';this.style.boxShadow='none'">
                    <div style="display:flex;align-items:center;gap:11px;">
                        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#fffbeb,#fef3c7);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 1px 4px rgba(217,119,6,.12);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span id="el2-dot-maxlogs" style="width:8px;height:8px;border-radius:50%;flex-shrink:0;display:inline-block;background:#16a34a;transition:background .2s;"></span>
                                <div style="font-size:13px;font-weight:600;color:#1e293b;"><?php esc_html_e( 'Giới hạn tối đa', 'whp' ); ?></div>
                            </div>
                            <div style="font-size:11.5px;color:#94a3b8;margin-top:2px;"><?php esc_html_e( 'Số lượng log tối đa được lưu', 'whp' ); ?></div>
                        </div>
                        <select id="el2-max-logs" class="wph-el2-select" style="min-width:128px;flex-shrink:0;border-radius:8px;font-size:12.5px;" onchange="wphEl2UpdateSelectDot('maxlogs',this.value,'0')">
                            <?php foreach ( array( 10000 => '10.000', 25000 => '25.000', 50000 => '50.000', 100000 => '100.000', 0 => __( 'Không giới hạn', 'whp' ) ) as $v => $l ) : ?>
                            <option value="<?php echo esc_attr( $v ); ?>" <?php selected( $settings['max_logs'] ?? 50000, $v ); ?>><?php echo esc_html( $l ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            </div>
        </div>

    </div><!-- /sidebar -->

</div><!-- /body -->

<!-- ── SAVE BAR (full width, like CAPTCHA/SMTP) ── -->
<div class="wph-el2-save-bar">
    <span class="wph-el2-save-bar-hint">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
        <?php esc_html_e( 'Các thay đổi được áp dụng ngay sau khi lưu.', 'whp' ); ?>
    </span>
    <button class="wph-el2-save-bar-btn" onclick="wphEl2SaveSettings()">
        <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:15px;"></span>
        <?php esc_html_e( 'Lưu cài đặt', 'whp' ); ?>
    </button>
</div>

<!-- ── EMAIL DETAIL MODAL (hidden) ────────────────────────────────────────── -->
<div id="wph-el2-modal" class="wph-el2-modal-overlay" style="display:none;" onclick="if(event.target===this)wphEl2CloseModal()">
    <div class="wph-el2-modal">
        <div class="wph-el2-modal-head">
            <h2 id="wph-el2-modal-title"><?php esc_html_e( 'Chi tiết Email', 'whp' ); ?></h2>
            <button class="wph-el2-modal-close" onclick="wphEl2CloseModal()" aria-label="<?php esc_attr_e( 'Đóng', 'whp' ); ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="wph-el2-modal-body" id="wph-el2-modal-body">
            <div style="text-align:center;padding:40px;color:#94a3b8;grid-column:1/-1;"><?php esc_html_e( 'Đang tải...', 'whp' ); ?></div>
        </div>
        <div class="wph-el2-modal-foot">
            <button id="wph-el2-modal-resend" class="wph-el2-btn wph-el2-btn-blue-outline" style="display:none;" onclick="wphEl2ModalResend()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1,4 1,10 7,10"/><path d="M3.51 15a9 9 0 1 0 .49-4"/></svg>
                <?php esc_html_e( 'Gửi lại', 'whp' ); ?>
            </button>
            <button id="wph-el2-modal-delete" class="wph-el2-btn wph-el2-btn-danger" style="display:none;" onclick="wphEl2ModalDelete()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                <?php esc_html_e( 'Xóa', 'whp' ); ?>
            </button>
            <button class="wph-el2-btn wph-el2-btn-outline" onclick="wphEl2CloseModal()"><?php esc_html_e( 'Đóng', 'whp' ); ?></button>
        </div>
    </div>
</div>

</div><!-- /wrap -->

<script>
(function(){
var whpElI18n = {
    detailTitle:    '<?php echo esc_js( __( 'Chi tiết Email', 'whp' ) ); ?> #',
    loading:        '<?php echo esc_js( __( 'Đang tải...', 'whp' ) ); ?>',
    notFound:       '<?php echo esc_js( __( 'Không tìm thấy', 'whp' ) ); ?>',
    networkError:   '<?php echo esc_js( __( 'Lỗi kết nối mạng', 'whp' ) ); ?>',
    noData:         '<?php echo esc_js( __( 'Chưa có dữ liệu', 'whp' ) ); ?>',
    chartLoadError: '<?php echo esc_js( __( 'Không thể tải dữ liệu biểu đồ', 'whp' ) ); ?>',
    statusSent:     '<?php echo esc_js( __( 'Thành công', 'whp' ) ); ?>',
    statusFailed:   '<?php echo esc_js( __( 'Thất bại', 'whp' ) ); ?>',
    statusPending:  '<?php echo esc_js( __( 'Chờ gửi', 'whp' ) ); ?>',
    labelTo:        '<?php echo esc_js( __( 'Người nhận', 'whp' ) ); ?>',
    labelSubject:   '<?php echo esc_js( __( 'Tiêu đề', 'whp' ) ); ?>',
    labelStatus:    '<?php echo esc_js( __( 'Trạng thái', 'whp' ) ); ?>',
    labelTime:      '<?php echo esc_js( __( 'Thời gian', 'whp' ) ); ?>',
    labelContent:   '<?php echo esc_js( __( 'Nội dung', 'whp' ) ); ?>'
};
    var nonce   = '<?php echo esc_js( $nonce ); ?>';
    var ajaxUrl = '<?php echo esc_js( $ajax_url ); ?>';
    var currentModalId = null;

    // Initial chart data from PHP
    var chartData = <?php echo wp_json_encode( $chart_data ); ?>;

    // ── Helpers ──────────────────────────────────────────────────────────────

    function post(action, data, cb) {
        var fd = new FormData();
        fd.append('action', action);
        fd.append('nonce', nonce);
        for (var k in data) fd.append(k, data[k]);
        fetch(ajaxUrl, { method: 'POST', body: fd })
            .then(function(r){ return r.json(); })
            .then(cb)
            .catch(function(){ showNotice(whpElI18n.networkError, 'error'); });
    }

    function showNotice(msg, type) {
        var el = document.getElementById('wph-el2-notice');
        if (!el) return;
        var t = type || 'success';
        var isErr = t === 'error';
        var iColor = isErr ? '#dc2626' : '#16a34a';
        var icon = isErr
            ? '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
            : '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
        el.className = 'wph-el2-notice ' + t;
        el.innerHTML = '<span style="width:20px;height:20px;flex-shrink:0;border-radius:50%;background:' + iColor + ';display:inline-flex;align-items:center;justify-content:center;">' + icon + '</span>' + msg;
        el.style.display = 'flex';
        el.style.animation = 'none';
        el.offsetHeight; // reflow
        el.style.animation = 'el2SlideIn .3s cubic-bezier(.16,1,.3,1) forwards';
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(function(){ el.style.display = 'none'; }, 5000);
    }

    // ── Select dot indicator ──────────────────────────────────────────────────
    window.wphEl2UpdateSelectDot = function(key, val, emptyVal) {
        var dot = document.getElementById('el2-dot-' + key);
        if (!dot) return;
        dot.style.background = (val === emptyVal || val === '') ? '#dc2626' : '#16a34a';
    };
    // Init dots on load
    (function() {
        var ret = document.getElementById('el2-retention');
        var mx  = document.getElementById('el2-max-logs');
        if (ret) wphEl2UpdateSelectDot('retention', ret.value, '0');
        if (mx)  wphEl2UpdateSelectDot('maxlogs', mx.value, '0');
    })();

    // ── SVG Line Chart ────────────────────────────────────────────────────────

    window.wphEl2RenderChart = function(data) {
        var container = document.getElementById('el2-chart-container');
        if (!container) return;
        if (!data || !data.length) {
            container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;">' + whpElI18n.noData + '</div>';
            return;
        }
        var W = Math.max(300, container.offsetWidth || 700), H = 220;
        var pad = { top: 20, right: 24, bottom: 36, left: 48 };
        var cW = W - pad.left - pad.right;
        var cH = H - pad.top - pad.bottom;
        var maxVal = 0;
        data.forEach(function(d){ maxVal = Math.max(maxVal, d.sent, d.failed, 1); });
        // Nice round Y max
        var raw = maxVal * 1.15;
        var mag = Math.pow(10, Math.floor(Math.log(raw) / Math.LN10));
        var maxY = Math.ceil(raw / mag) * mag || 10;
        var yTicks = 4;
        var yStep = maxY / yTicks;
        var n = data.length;
        function gx(i){ return pad.left + (n <= 1 ? cW/2 : i * cW / (n-1)); }
        function gy(v){ return pad.top + cH - Math.min(v, maxY) / maxY * cH; }

        // Smooth bezier path helper (catmull-rom → cubic bezier)
        function smoothPath(pts) {
            if (pts.length < 2) return '';
            var d = 'M' + pts[0][0].toFixed(1) + ',' + pts[0][1].toFixed(1);
            for (var k = 0; k < pts.length - 1; k++) {
                var p0 = pts[Math.max(0, k-1)], p1 = pts[k], p2 = pts[k+1], p3 = pts[Math.min(pts.length-1, k+2)];
                var cp1x = p1[0] + (p2[0] - p0[0]) / 6;
                var cp1y = p1[1] + (p2[1] - p0[1]) / 6;
                var cp2x = p2[0] - (p3[0] - p1[0]) / 6;
                var cp2y = p2[1] - (p3[1] - p1[1]) / 6;
                d += ' C' + cp1x.toFixed(1) + ',' + cp1y.toFixed(1) + ' ' + cp2x.toFixed(1) + ',' + cp2y.toFixed(1) + ' ' + p2[0].toFixed(1) + ',' + p2[1].toFixed(1);
            }
            return d;
        }

        var sentPts = [], failPts = [];
        data.forEach(function(d, i){ sentPts.push([gx(i), gy(d.sent)]); failPts.push([gx(i), gy(d.failed)]); });
        var sentPath = smoothPath(sentPts);
        var failPath = smoothPath(failPts);
        var baseY = (pad.top + cH).toFixed(1);
        var sentArea = sentPath + ' L' + gx(n-1).toFixed(1) + ',' + baseY + ' L' + gx(0).toFixed(1) + ',' + baseY + ' Z';

        // Grid + Y labels
        var gridLines = '';
        for (var gi = 0; gi <= yTicks; gi++) {
            var yv = gi * yStep;
            var ypos = gy(yv);
            var lbl = yv >= 1000 ? (yv/1000).toFixed(1).replace('.0','') + 'k' : yv;
            gridLines += '<line x1="' + pad.left + '" y1="' + ypos.toFixed(1) + '" x2="' + (W - pad.right) + '" y2="' + ypos.toFixed(1) + '" stroke="#f0f4f8" stroke-width="1" stroke-dasharray="' + (gi === 0 ? '0' : '4,3') + '"/>';
            if (gi > 0) gridLines += '<text x="' + (pad.left - 8) + '" y="' + (ypos + 4).toFixed(1) + '" text-anchor="end" font-size="10.5" fill="#b0bec5">' + lbl + '</text>';
        }
        // X-axis baseline
        gridLines += '<line x1="' + pad.left + '" y1="' + (pad.top+cH) + '" x2="' + (W-pad.right) + '" y2="' + (pad.top+cH) + '" stroke="#e2e8f0" stroke-width="1"/>';

        // X labels
        var xLabels = '';
        var showEvery = Math.max(1, Math.ceil(n / 8));
        data.forEach(function(d, i){
            if (i % showEvery === 0 || i === n-1) {
                xLabels += '<text x="' + gx(i).toFixed(1) + '" y="' + (H - 8) + '" text-anchor="middle" font-size="10.5" fill="#b0bec5">' + d.date + '</text>';
            }
        });

        // End-point dots only
        var dots = '';
        if (sentPts.length) {
            var lp = sentPts[sentPts.length-1];
            dots += '<circle cx="' + lp[0].toFixed(1) + '" cy="' + lp[1].toFixed(1) + '" r="4" fill="#16a34a" stroke="#fff" stroke-width="2"/>';
        }
        if (failPts.length) {
            var lfp = failPts[failPts.length-1];
            dots += '<circle cx="' + lfp[0].toFixed(1) + '" cy="' + lfp[1].toFixed(1) + '" r="4" fill="#dc2626" stroke="#fff" stroke-width="2"/>';
        }

        var defs = '<defs>'
            + '<linearGradient id="el2GradSent" x1="0" y1="0" x2="0" y2="1">'
            + '<stop offset="0%" stop-color="#16a34a" stop-opacity="0.18"/>'
            + '<stop offset="100%" stop-color="#16a34a" stop-opacity="0"/>'
            + '</linearGradient></defs>';

        var svg = '<svg viewBox="0 0 ' + W + ' ' + H + '" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;display:block;" font-family="inherit">'
            + defs + gridLines + xLabels
            + '<path d="' + sentArea + '" fill="url(#el2GradSent)"/>'
            + '<path d="' + sentPath + '" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>'
            + '<path d="' + failPath + '" fill="none" stroke="#ef4444" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" stroke-dasharray="5,3"/>'
            + dots + '</svg>';

        container.innerHTML = svg;
    };

    window.wphEl2LoadChart = function(days) {
        post('wph_el_chart_data', { days: days }, function(r){
            if (r.success) wphEl2RenderChart(r.data);
            else showNotice(whpElI18n.chartLoadError, 'error');
        });
    };

    // Segmented period buttons
    window.wphEl2SetPeriod = function(days, btn) {
        document.querySelectorAll('.wph-el2-chart-seg-btn').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        wphEl2LoadChart(days);
    };

    // Custom dropdown functions
    window.wphEl2DdPosition = function(triggerEl, menu) {
        var rect = triggerEl.getBoundingClientRect();
        var mw   = menu.offsetWidth || 200;
        var mh   = menu.offsetHeight || 150;
        var top  = rect.bottom + 4;
        var left = rect.left;
        if (left + mw > window.innerWidth - 8) left = window.innerWidth - mw - 8;
        if (left < 8) left = 8;
        if (top + mh > window.innerHeight) top = rect.top - mh - 4;
        menu.style.top  = top + 'px';
        menu.style.left = left + 'px';
    };
    window.wphEl2DdToggle = function(key, triggerEl) {
        var dd   = document.getElementById('el2-dd-' + key);
        var menu = document.getElementById('el2-menu-' + key);
        var isOpen = dd.classList.contains('open');
        document.querySelectorAll('.wph-el2-dd').forEach(function(d){ d.classList.remove('open'); });
        if (!isOpen) { dd.classList.add('open'); wphEl2DdPosition(triggerEl, menu); }
    };
    window.wphEl2DdSelect = function(key, el) {
        var val = el.dataset.value;
        var dot = el.dataset.dot;
        var label = el.textContent.trim();
        document.getElementById('el2-inp-' + key).value = val;
        document.getElementById('el2-dot-' + key).style.background = dot;
        document.getElementById('el2-lbl-' + key).textContent = label;
        el.closest('.wph-el2-dd-menu').querySelectorAll('.wph-el2-dd-opt').forEach(function(o){ o.classList.remove('selected'); });
        el.classList.add('selected');
        el.closest('.wph-el2-dd').classList.remove('open');
        document.getElementById('wph-el2-filter-form').submit();
    };
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.wph-el2-dd')) {
            document.querySelectorAll('.wph-el2-dd').forEach(function(d){ d.classList.remove('open'); });
        }
    });

    // ── Detail modal ──────────────────────────────────────────────────────────

    window.wphEl2ViewDetail = function(id) {
        currentModalId = id;
        var modal = document.getElementById('wph-el2-modal');
        var body  = document.getElementById('wph-el2-modal-body');
        var title = document.getElementById('wph-el2-modal-title');
        title.textContent = whpElI18n.detailTitle + id;
        body.innerHTML = '<div style="text-align:center;padding:40px;color:#94a3b8;grid-column:1/-1;">' + whpElI18n.loading + '</div>';
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.getElementById('wph-el2-modal-resend').style.display = 'none';
        document.getElementById('wph-el2-modal-delete').style.display = 'none';

        post('wph_el_get_detail', { id: id }, function(r) {
            if (!r.success) {
                body.innerHTML = '<div style="text-align:center;padding:40px;color:#dc2626;grid-column:1/-1;">' + (r.data && r.data.message ? r.data.message : whpElI18n.notFound) + '</div>';
                return;
            }
            var d = r.data;
            var statusBadge = d.status === 'sent'
                ? '<span class="wph-el2-badge-sent">' + whpElI18n.statusSent + '</span>'
                : d.status === 'failed'
                    ? '<span class="wph-el2-badge-failed">' + whpElI18n.statusFailed + '</span>'
                    : '<span class="wph-el2-badge-pending">' + whpElI18n.statusPending + '</span>';

            var metaRows = [
                [whpElI18n.labelTo, '<span style="color:#16a34a;font-weight:600;">' + d.to_email + '</span>'],
                [whpElI18n.labelSubject, d.subject],
                [whpElI18n.labelStatus, statusBadge],
                [whpElI18n.labelTime,  d.created_at],
            ];
            if (d.smtp_response && d.smtp_response !== '—') {
                metaRows.push(['SMTP', '<span style="color:#64748b;font-size:12px;">' + d.smtp_response + '</span>']);
            }
            var metaHtml = '';
            metaRows.forEach(function(row){
                metaHtml += '<div class="wph-el2-meta-row"><dt class="wph-el2-meta-dt">' + row[0] + '</dt><dd class="wph-el2-meta-dd">' + row[1] + '</dd></div>';
            });

            var bodyLeft = document.createElement('div');
            bodyLeft.innerHTML = '<h4 style="font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin:0 0 10px;">' + whpElI18n.labelContent + '</h4>';
            var emailBodyWrap = document.createElement('div');
            emailBodyWrap.className = 'wph-el2-email-body';
            emailBodyWrap.style.cssText = 'padding:0;overflow:hidden;background:#f8fafc;min-height:200px;';
            if (d.message) {
                var iframe = document.createElement('iframe');
                iframe.sandbox = 'allow-same-origin';
                iframe.style.cssText = 'width:100%;min-height:300px;border:none;border-radius:8px;display:block;';
                iframe.srcdoc = d.message;
                iframe.onload = function() {
                    try {
                        var doc = iframe.contentDocument || iframe.contentWindow.document;
                        var h = doc.body ? doc.body.scrollHeight : 300;
                        iframe.style.height = Math.max(200, h + 20) + 'px';
                    } catch(e) {}
                };
                emailBodyWrap.appendChild(iframe);
            } else {
                emailBodyWrap.innerHTML = '<div style="padding:20px;"><em style="color:#94a3b8">Không có nội dung</em></div>';
            }
            bodyLeft.appendChild(emailBodyWrap);

            var bodyRight = document.createElement('div');
            bodyRight.innerHTML = '<h4 style="font-size:13px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin:0 0 10px;">Thông tin</h4>'
                + '<dl style="margin:0;">' + metaHtml + '</dl>';

            body.innerHTML = '';
            body.appendChild(bodyLeft);
            body.appendChild(bodyRight);

            document.getElementById('wph-el2-modal-resend').style.display = 'inline-flex';
            document.getElementById('wph-el2-modal-delete').style.display = 'inline-flex';
        });
    };

    window.wphEl2CloseModal = function() {
        document.getElementById('wph-el2-modal').style.display = 'none';
        document.body.style.overflow = '';
        currentModalId = null;
    };

    window.wphEl2ModalResend = function() {
        if (!currentModalId) return;
        wphEl2Resend(currentModalId); // will show confirm popup
    };

    window.wphEl2ModalDelete = function() {
        if (!currentModalId) return;
        var id  = currentModalId;
        var row = document.getElementById('el2-row-' + id);
        wphEl2Delete(id, row, true);
    };

    // ESC key closes modal
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') wphEl2CloseModal();
    });

    // ── Actions ───────────────────────────────────────────────────────────────

    // Shared confirm modal (matches xem chi tiết modal style)
    var _el2ConfirmCbs = {};
    window.wphEl2ConfirmRun = function(key) {
        if (_el2ConfirmCbs[key]) { _el2ConfirmCbs[key](); delete _el2ConfirmCbs[key]; }
    };
    window.wphEl2ShowConfirm = function(opts) {
        var cid = 'el2-confirm-' + Date.now();
        var cbKey = 'cb_' + cid;
        _el2ConfirmCbs[cbKey] = opts.onConfirm || function(){};
        var iconBg   = opts.iconBg   || '#eff6ff';
        var iconSvg  = opts.iconSvg  || '';
        var title    = opts.title    || 'Xác nhận';
        var message  = opts.message  || '';
        var warning  = opts.warning  || '';
        var btnLabel = opts.btnLabel || 'Xác nhận';
        var btnStyle = opts.btnStyle || 'background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;box-shadow:0 4px 12px rgba(37,99,235,.3);';

        var h = '<div id="' + cid + '" class="wph-el2-modal-overlay" onclick="if(event.target===this){document.getElementById(\'' + cid + '\').remove();delete _el2ConfirmCbs[\'' + cbKey + '\'];}">';
        h += '<div class="wph-el2-modal" style="max-width:440px;">';
        // Head
        h += '<div class="wph-el2-modal-head">';
        h += '<div style="display:flex;align-items:center;gap:12px;">';
        h += '<div style="width:36px;height:36px;border-radius:10px;background:' + iconBg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">' + iconSvg + '</div>';
        h += '<h2 style="margin:0;font-size:16px;font-weight:700;color:#1e293b;">' + title + '</h2>';
        h += '</div>';
        h += '<button class="wph-el2-modal-close" onclick="document.getElementById(\'' + cid + '\').remove()">'
            + '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
            + '</button>';
        h += '</div>';
        // Body
        h += '<div style="padding:20px 24px;">';
        h += '<div style="font-size:13.5px;color:#475569;line-height:1.7;">' + message + '</div>';
        if (warning) {
            h += '<div style="margin-top:14px;background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:10px 14px;display:flex;align-items:flex-start;gap:8px;">';
            h += '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
            h += '<span style="font-size:12.5px;color:#854d0e;">' + warning + '</span>';
            h += '</div>';
        }
        h += '</div>';
        // Foot
        h += '<div class="wph-el2-modal-foot">';
        h += '<button onclick="document.getElementById(\'' + cid + '\').remove()" style="padding:9px 22px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">Hủy</button>';
        h += '<button onclick="document.getElementById(\'' + cid + '\').remove();wphEl2ConfirmRun(\'' + cbKey + '\')" style="padding:9px 22px;border:none;border-radius:8px;' + btnStyle + 'font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;">' + btnLabel + '</button>';
        h += '</div>';
        h += '</div></div>';
        document.body.insertAdjacentHTML('beforeend', h);
    };

    window.wphEl2Resend = function(id) {
        wphEl2ShowConfirm({
            iconBg:   '#eff6ff',
            iconSvg:  '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>',
            title:    'Xác nhận gửi lại',
            message:  'Email <strong style="color:#1e293b;">#' + id + '</strong> sẽ được gửi lại <strong style="color:#dc2626;">thật sự</strong> tới địa chỉ người nhận gốc qua SMTP đang cấu hình.<br><br>Khách hàng sẽ nhận được email này trong inbox của họ.',
            warning:  'Hành động này không thể hoàn tác. Hãy chắc chắn bạn muốn gửi lại email này.',
            btnLabel: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg> Gửi lại',
            btnStyle: 'background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;box-shadow:0 4px 12px rgba(37,99,235,.3);',
            onConfirm: function() { wphEl2DoResend(id); }
        });
    };

    window.wphEl2DoResend = function(id) {
        post('wph_el_resend', { id: id }, function(r){
            if (r.success) showNotice(r.data.message || 'Đã gửi lại email #' + id, 'success');
            else showNotice(r.data && r.data.message ? r.data.message : 'Gửi lại thất bại', 'error');
        });
    };

    window.wphEl2Delete = function(id, rowEl, closeModal) {
        wphEl2ShowConfirm({
            iconBg:   '#fef2f2',
            iconSvg:  '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg>',
            title:    'Xác nhận xóa',
            message:  'Bạn có chắc muốn xóa email <strong style="color:#1e293b;">#' + id + '</strong>?<br>Hành động này sẽ xóa vĩnh viễn bản ghi khỏi nhật ký.',
            warning:  'Không thể khôi phục sau khi xóa.',
            btnLabel: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg> Xóa',
            btnStyle: 'background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;box-shadow:0 4px 12px rgba(220,38,38,.3);',
            onConfirm: function() {
                post('wph_el_delete', { id: id }, function(r){
                    if (r.success) {
                        showNotice('Đã xóa email #' + id);
                        if (rowEl) rowEl.remove();
                        if (closeModal) wphEl2CloseModal();
                        wphEl2UpdateBulkBar();
                    } else {
                        showNotice(r.data && r.data.message ? r.data.message : 'Xóa thất bại', 'error');
                    }
                });
            }
        });
    };

    // ── Bulk actions ──────────────────────────────────────────────────────────

    window.wphEl2SelAll = function(cb) {
        var cbs = document.querySelectorAll('.wph-el2-row-cb');
        cbs.forEach(function(c){ c.checked = cb.checked; });
        wphEl2UpdateBulkBar();
    };

    window.wphEl2DeselectAll = function() {
        var allCb = document.getElementById('wph-el2-check-all');
        if (allCb) allCb.checked = false;
        document.querySelectorAll('.wph-el2-row-cb').forEach(function(c){ c.checked = false; });
        wphEl2UpdateBulkBar();
    };

    window.wphEl2UpdateBulkBar = function() {
        var checked = document.querySelectorAll('.wph-el2-row-cb:checked');
        var bar     = document.getElementById('wph-el2-bulk-bar');
        var count   = document.getElementById('wph-el2-bulk-count');
        var topBtn  = document.getElementById('wph-el2-bulk-delete-top');
        var n = checked.length;
        if (n > 0) {
            bar.classList.add('visible');
            count.textContent = n + ' đã chọn';
            if (topBtn) topBtn.style.display = 'inline-flex';
        } else {
            bar.classList.remove('visible');
            if (topBtn) topBtn.style.display = 'none';
        }
    };

    window.wphEl2BulkDelete = function() {
        var checked = document.querySelectorAll('.wph-el2-row-cb:checked');
        var ids = Array.from(checked).map(function(c){ return c.value; });
        if (!ids.length) return;
        var n = ids.length;
        wphEl2ShowConfirm({
            iconBg:   '#fef2f2',
            iconSvg:  '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg>',
            title:    'Xác nhận xóa hàng loạt',
            message:  'Bạn sắp xóa <strong style="color:#1e293b;">' + n + ' email</strong> đã chọn.<br>Toàn bộ bản ghi sẽ bị xóa vĩnh viễn khỏi nhật ký.',
            warning:  'Không thể khôi phục sau khi xóa.',
            btnLabel: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg> Xóa ' + n + ' email',
            btnStyle: 'background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;box-shadow:0 4px 12px rgba(220,38,38,.3);',
            onConfirm: function() {
                var done = 0;
                ids.forEach(function(id){
                    post('wph_el_delete', { id: id }, function(r){
                        if (r.success) {
                            var row = document.getElementById('el2-row-' + id);
                            if (row) row.remove();
                        }
                        done++;
                        if (done === ids.length) {
                            showNotice('Đã xóa ' + ids.length + ' email');
                            wphEl2DeselectAll();
                        }
                    });
                });
            }
        });
    };

    // ── CSV Export ────────────────────────────────────────────────────────────

    window.wphEl2ExportCsv = function() {
        var form = document.getElementById('wph-el2-filter-form');
        var tmp  = document.createElement('form');
        tmp.method = 'POST';
        tmp.action = ajaxUrl;
        tmp.style.display = 'none';
        var fields = {
            action:  'wph_el_export_csv',
            nonce:   nonce,
            status:  (form.querySelector('[name="el_status"]') || {}).value || '',
            search:  (form.querySelector('[name="el_search"]') || {}).value || '',
        };
        Object.keys(fields).forEach(function(k){
            var inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = k; inp.value = fields[k];
            tmp.appendChild(inp);
        });
        document.body.appendChild(tmp);
        tmp.submit();
        document.body.removeChild(tmp);
    };

    // ── SMTP Test ─────────────────────────────────────────────────────────────

    // ── Type stats popup ─────────────────────────────────────────────────────
    var wphEl2TypeData = <?php
        $td = array();
        foreach ( $donut_segments as $lname => $lcolor ) {
            $cnt = $type_counts[ $lname ] ?? 0;
            $pct = $type_total > 0 ? round( $cnt / $type_total * 100 ) : 0;
            $td[] = array( 'name' => $lname, 'color' => $lcolor, 'count' => $cnt, 'pct' => $pct );
        }
        echo wp_json_encode( $td );
    ?>;
    var wphEl2TypeTotal = <?php echo (int) $type_total; ?>;

    window.wphEl2ShowTypePopup = function() {
        if (document.getElementById('el2-type-overlay')) return;
        var h = '<div id="el2-type-overlay" style="position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:999999;display:flex;align-items:center;justify-content:center;padding:16px;" onclick="if(event.target===this)this.remove()">';
        h += '<div style="background:#fff;border-radius:16px;width:420px;max-width:100%;box-shadow:0 24px 64px rgba(0,0,0,.22);overflow:hidden;">';
        // header
        h += '<div style="padding:18px 22px 15px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">';
        h += '<div style="display:flex;align-items:center;gap:9px;">';
        h += '<div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);display:flex;align-items:center;justify-content:center;">';
        h += '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg></div>';
        h += '<span style="font-size:15px;font-weight:700;color:#1e293b;">Thống kê loại email</span></div>';
        h += '<button onclick="document.getElementById(\'el2-type-overlay\').remove()" style="width:28px;height:28px;border-radius:8px;border:1px solid #e2e8f0;background:#f8fafc;cursor:pointer;font-size:17px;line-height:1;color:#64748b;display:flex;align-items:center;justify-content:center;font-family:inherit;">×</button>';
        h += '</div>';
        // total badge
        h += '<div style="padding:14px 22px 0;">';
        h += '<div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border-radius:10px;padding:11px 16px;display:flex;align-items:center;justify-content:space-between;">';
        h += '<span style="font-size:13px;color:#15803d;font-weight:600;">Tổng số email đã ghi</span>';
        h += '<span style="font-size:22px;font-weight:800;color:#15803d;">' + wphEl2TypeTotal.toLocaleString() + '</span>';
        h += '</div></div>';
        // list
        h += '<div style="padding:14px 22px 20px;display:flex;flex-direction:column;gap:11px;">';
        for (var i = 0; i < wphEl2TypeData.length; i++) {
            var t = wphEl2TypeData[i];
            var barW = t.pct > 0 ? t.pct : (t.count > 0 ? 1 : 0);
            var active = t.count > 0;
            h += '<div>';
            h += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">';
            h += '<div style="display:flex;align-items:center;gap:7px;">';
            h += '<span style="width:10px;height:10px;border-radius:50%;background:' + t.color + ';display:inline-block;flex-shrink:0;opacity:' + (active ? '1' : '0.3') + ';"></span>';
            h += '<span style="font-size:13px;font-weight:' + (active ? '600' : '400') + ';color:' + (active ? '#374151' : '#94a3b8') + ';">' + t.name + '</span>';
            h += '</div>';
            h += '<div style="display:flex;align-items:center;gap:10px;">';
            h += '<span style="font-size:12px;color:' + (active ? '#64748b' : '#cbd5e1') + ';">' + t.count.toLocaleString() + ' email</span>';
            h += '<span style="font-size:13px;font-weight:700;color:' + (active ? '#1e293b' : '#cbd5e1') + ';min-width:36px;text-align:right;">' + t.pct + '%</span>';
            h += '</div></div>';
            h += '<div style="height:7px;background:#f1f5f9;border-radius:4px;overflow:hidden;">';
            h += '<div style="height:100%;width:' + barW + '%;background:' + t.color + ';border-radius:4px;opacity:' + (active ? '1' : '0.2') + ';"></div>';
            h += '</div></div>';
        }
        h += '</div>';
        // footer
        h += '<div style="padding:12px 22px 16px;border-top:1px solid #f1f5f9;text-align:right;">';
        h += '<a href="<?php echo esc_js( $base_url ); ?>" style="font-size:13px;color:#16a34a;text-decoration:none;font-weight:600;">Xem nhật ký đầy đủ →</a>';
        h += '</div>';
        h += '</div></div>';
        document.body.insertAdjacentHTML('beforeend', h);
    };

    window.wphEl2TestSmtp = function() {
        showNotice('Đang kiểm tra SMTP...', 'info');
        post('wph_smtp_test', {}, function(r){
            if (r.success) showNotice(r.data.message || 'SMTP hoạt động bình thường', 'success');
            else showNotice(r.data && r.data.message ? r.data.message : 'SMTP có lỗi', 'error');
        });
    };

    // ── Log settings save ─────────────────────────────────────────────────────

    window.wphEl2SaveSettings = function() {
        var retention = document.getElementById('el2-retention');
        var maxLogs   = document.getElementById('el2-max-logs');
        post('wph_el_save_settings', {
            active:    '1',
            retention: retention ? retention.value : 0,
            max_logs:  maxLogs   ? maxLogs.value   : 50000,
        }, function(r){
            if (r.success) showNotice(r.data.message || 'Đã lưu cài đặt', 'success');
            else showNotice(r.data && r.data.message ? r.data.message : 'Lỗi lưu cài đặt', 'error');
        });
    };

    // ── Auto-submit filter on Enter key in search ─────────────────────────────

    (function(){
        var form = document.getElementById('wph-el2-filter-form');
        if (!form) return;
        // Submit on Enter key in search
        var searchInput = form.querySelector('input[name="el_search"]');
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e){
                if (e.key === 'Enter') { e.preventDefault(); form.submit(); }
            });
        }
    })();

    // ── Init chart on load ────────────────────────────────────────────────────

    if (chartData && chartData.length) {
        wphEl2RenderChart(chartData);
    }

    // ── Date range popover ────────────────────────────────────────────────
    (function(){
        var trigger = document.getElementById('el2-daterange-trigger');
        var popover = document.getElementById('el2-date-popover');
        if (!trigger || !popover) return;
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            var isOpen = popover.classList.contains('open');
            popover.classList.toggle('open', !isOpen);
            trigger.classList.toggle('open', !isOpen);
            if (!isOpen) { reposDrPopover(); }
        });
        document.addEventListener('click', function(e){
            if (!trigger.closest('.wph-el2-daterange-wrap').contains(e.target)) {
                popover.classList.remove('open');
                trigger.classList.remove('open');
            }
        });
        function reposDrPopover(){
            if (!popover.classList.contains('open')) return;
            var rect = trigger.getBoundingClientRect();
            var pw = 240;
            var left = rect.right - pw;
            if (left < 8) left = 8;
            if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
            var adminBar = document.getElementById('wpadminbar');
            var minTop = adminBar ? adminBar.offsetHeight + 4 : 36;
            var top = rect.bottom + 7;
            if (top < minTop) top = minTop;
            popover.style.left = left + 'px';
            popover.style.top  = top + 'px';
        }
        document.addEventListener('scroll', reposDrPopover, { passive: true, capture: true });
        var clearBtn = document.getElementById('el2-date-clear');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(){
                document.getElementById('el2-inp-from').value = '';
                document.getElementById('el2-inp-to').value = '';
                document.getElementById('wph-el2-filter-form').submit();
            });
        }
        var inpFrom = document.getElementById('el2-inp-from');
        var inpTo   = document.getElementById('el2-inp-to');
        var label   = document.getElementById('el2-date-label');
        function updateLabel(){
            var f = inpFrom ? inpFrom.value : '', t = inpTo ? inpTo.value : '';
            if (!f && !t) { label.textContent = 'Tất cả ngày'; return; }
            function fmt(d){ if(!d) return ''; var p=d.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }
            label.textContent = (f ? fmt(f) : '…') + ' – ' + (t ? fmt(t) : '…');
        }
        if (inpFrom) inpFrom.addEventListener('change', updateLabel);
        if (inpTo)   inpTo.addEventListener('change', updateLabel);
    })();

})();
</script>
    <?php
}
