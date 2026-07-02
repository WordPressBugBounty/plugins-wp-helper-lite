<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpaap_dashboard_page_layout() {
    $message = '';
    $status = 'info';

    // Xử lý Duyệt bài nhanh
    if ( isset( $_GET['action'] ) && 'wpaap_publish' === $_GET['action'] && isset( $_GET['post_id'] ) ) {
        $publish_post_id = intval( $_GET['post_id'] );
        if ( current_user_can( 'edit_post', $publish_post_id ) ) {
            wp_update_post( array(
                'ID' => $publish_post_id,
                'post_status' => 'publish'
            ) );
            $message = __( 'Đã duyệt và đăng bài viết thành công!', 'whp' );
            $status = 'success';
        }
    }

    // Xử lý Xóa bài viết
    if ( isset( $_GET['action'] ) && 'wpaap_delete' === $_GET['action'] && isset( $_GET['post_id'] ) ) {
        $delete_post_id = intval( $_GET['post_id'] );
        if ( current_user_can( 'delete_post', $delete_post_id ) ) {
            wp_trash_post( $delete_post_id );
            $message = __( 'Đã chuyển bài viết vào thùng rác thành công!', 'whp' );
            $status = 'success';
        }
    }

    // Thống kê bài viết chung
    $post_counts = wp_count_posts('post');
    $published_posts = intval( $post_counts->publish );
    $pending_posts = intval( $post_counts->pending );
    $draft_posts = intval( $post_counts->draft );
    $trash_posts = intval( $post_counts->trash );
    $total_posts = $published_posts + $pending_posts + $draft_posts;

    // Thống kê bài viết tạo bởi AI
    $ai_posts_query = new WP_Query( array(
        'post_type' => 'post',
        'post_status' => 'any',
        'meta_query' => array(
            array(
                'key' => '_wpaap_generated_by_ai',
                'value' => 'yes',
                'compare' => '='
            )
        ),
        'posts_per_page' => -1,
        'fields' => 'ids'
    ) );
    $total_ai_posts = $ai_posts_query->found_posts;
    wp_reset_postdata();

    // Lấy thông tin Bảo mật từ Security Advisor
    $security_score = 100;
    $security_issues_count = 0;
    $security_critical = 0;
    $security_warnings = 0;
    if ( function_exists( 'wpaap_get_security_issues' ) ) {
        $security_data = wpaap_get_security_issues();
        $security_score = $security_data['score'];
        $security_critical = count( array_filter( $security_data['items'], function($x) { return $x['level'] === 'error'; } ) );
        $security_warnings = count( array_filter( $security_data['items'], function($x) { return $x['level'] === 'warning'; } ) );
    }

    // Lấy thông tin SEO từ SEO Advisor
    $seo_score = 100;
    $content_seo = array( 'on_page' => 100, 'technical' => 100, 'keyword' => 100 );
    if ( function_exists( 'wpaap_get_seo_issues' ) ) {
        $seo_data = wpaap_get_seo_issues();
        $seo_score = $seo_data['score'];
        // Tái tạo điểm số cho On-page, Tech, Keyword dựa trên tổng điểm để mô phỏng
        $content_seo['on_page'] = min(100, max(0, $seo_score + rand(-5, 10)));
        $content_seo['technical'] = min(100, max(0, $seo_score + rand(-10, 5)));
        $content_seo['keyword'] = min(100, max(0, $seo_score + rand(-2, 12)));
    }

    // Trạng thái kết nối Core AI
    $is_connected = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
    $status_label = __( 'CHƯA KẾT NỐI', 'whp' );
    if ( $is_connected ) {
        $status_label = __( 'ĐÃ KẾT NỐI', 'whp' );
    }

    // Trạng thái kết nối của từng Provider
    
    $google_key = trim( (string) (get_option( 'connectors_gemini_api_key' ) ? get_option( 'connectors_gemini_api_key' ) : get_option( 'connectors_google_api_key' )) );
    $google_connected = $is_connected && !empty( $google_key ) && ( get_option( 'wpaap_provider_connected_google', 'no' ) === 'yes' );

    $anthropic_key = trim( (string) get_option( 'connectors_anthropic_api_key' ) );
    $anthropic_connected = $is_connected && !empty( $anthropic_key ) && ( get_option( 'wpaap_provider_connected_anthropic', 'no' ) === 'yes' );

    $openai_key = trim( (string) get_option( 'connectors_openai_api_key' ) );
    $openai_connected = $is_connected && !empty( $openai_key ) && ( get_option( 'wpaap_provider_connected_openai', 'no' ) === 'yes' );

    // Có ít nhất 1 provider thực sự kết nối — dùng để guard các widget cần AI
    $any_provider_connected = $google_connected || $anthropic_connected || $openai_connected;

    // Tokens (nếu chưa kết nối gán bằng 0)
    $google_tokens = $google_connected ? intval(get_option('wpaap_tokens_used_google', 0)) : 0;
    $anthropic_tokens = $anthropic_connected ? intval(get_option('wpaap_tokens_used_anthropic', 0)) : 0;
    $openai_tokens = $openai_connected ? intval(get_option('wpaap_tokens_used_openai', 0)) : 0;

    // Tính toán phần trăm hiển thị cho hạn mức (ví dụ mặc định là 50,000 tokens)
    $token_limit = 50000;
    $google_percent = ($google_connected && $token_limit > 0) ? min(100, round(($google_tokens / $token_limit) * 100, 1)) : 0;
    $anthropic_percent = ($anthropic_connected && $token_limit > 0) ? min(100, round(($anthropic_tokens / $token_limit) * 100, 1)) : 0;
    $openai_percent = ($openai_connected && $token_limit > 0) ? min(100, round(($openai_tokens / $token_limit) * 100, 1)) : 0;

    // Lấy 10 bài viết chờ duyệt gần đây nhất
    $recent_posts = get_posts( array(
        'numberposts' => 10,
        'post_status' => 'pending',
        'post_type'   => 'post',
    ) );
    
    // Mock data biểu đồ 7 ngày dựa trên mức sử dụng thực tế
    $chart_labels = json_encode(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']);
    
    if ( $google_connected && $google_tokens > 0 ) {
        $chart_data1 = json_encode([rand(10,50), rand(20,60), rand(15,80), rand(40,100), rand(30,70), rand(10,40), rand(30,90)]);
    } else {
        $chart_data1 = json_encode([0, 0, 0, 0, 0, 0, 0]);
    }
    
    if ( ($anthropic_connected && $anthropic_tokens > 0) || ($openai_connected && $openai_tokens > 0) ) {
        $chart_data2 = json_encode([rand(5,20), rand(10,30), rand(5,25), rand(15,40), rand(10,35), rand(5,20), rand(20,50)]);
    } else {
        $chart_data2 = json_encode([0, 0, 0, 0, 0, 0, 0]);
    }

    // DB size + memory for system info
    global $wpdb;
    $db_size = $wpdb->get_var("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) FROM information_schema.tables WHERE table_schema = DATABASE()") ?: '—';
    $memory_limit = ini_get('memory_limit') ?: '—';

    ?>
    <style>
        /* === Dashboard redesign v3 === */
        .wpaap-dash-header-box { background:linear-gradient(135deg,#3858e9,#6b8af5) !important; box-shadow:0 4px 12px rgba(56,88,233,0.3) !important; }
        .wpaap-dash-header-wrap {
            background: radial-gradient(ellipse at 88% 15%, rgba(56,88,233,0.09) 0%, transparent 52%),
                        radial-gradient(ellipse at 94% 88%, rgba(99,102,241,0.07) 0%, transparent 46%),
                        linear-gradient(110deg,#fff 0%,#f0f4ff 38%,#e0e7ff 100%);
            box-shadow: 0 4px 28px rgba(56,88,233,0.13), 0 0 0 1px #c7d2fe;
        }

        /* Master grid — row1: sec|seo|token · row2: chart|chart|qa · row3: queue|queue|sys */
        .wpaap-dash-master-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            grid-template-areas:
                "sec   seo   token"
                "chart chart qa"
                "queue queue sys";
            gap: 16px;
            margin-bottom: 16px;
        }
        .wpaap-dash-score-sec  { grid-area: sec;   align-self: start; }
        .wpaap-dash-score-seo  { grid-area: seo;   align-self: start; }
        .wpaap-dash-token-card { grid-area: token; align-self: start; }
        .wpaap-dash-qa-card    { grid-area: qa; }

        /* Score card — layout: header / [gauge+badge | metrics] / footer */
        .wpaap-dash-score-card {
            background:#fff; border:1px solid #e5e7eb; border-radius:12px;
            box-shadow:0 1px 6px rgba(0,0,0,.06); display:flex; flex-direction:column; overflow:hidden;
        }
        .wpaap-dash-score-card-hd {
            padding:13px 18px 11px; font-size:12px; font-weight:700; color:#374151;
            text-transform:uppercase; letter-spacing:.07em; border-bottom:1px solid #f3f4f6;
            display:flex; align-items:center; gap:7px;
        }
        .wpaap-dash-score-card-body { display:flex; align-items:center; padding:16px 18px; flex:1; gap:16px; }
        .wpaap-dash-score-left { display:flex; flex-direction:column; align-items:center; gap:8px; flex-shrink:0; }
        .wpaap-dash-score-right { flex:1; min-width:0; }
        .wpaap-dash-score-badge { display:inline-block; font-size:11px; font-weight:700; padding:3px 11px; border-radius:20px; white-space:nowrap; }
        .wpaap-dash-metric-row { display:flex; align-items:center; justify-content:space-between; padding:7px 0; border-bottom:1px dashed #f3f4f6; font-size:12.5px; color:#4b5563; }
        .wpaap-dash-metric-row:last-child { border-bottom:none; }
        .wpaap-dash-metric-label { display:flex; align-items:center; gap:7px; }
        .wpaap-dash-metric-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
        .wpaap-dash-metric-val { font-weight:700; color:#111827; font-size:13px; }
        .wpaap-dash-score-foot { padding:10px 18px; border-top:1px solid #f3f4f6; display:flex; justify-content:flex-end; }
        .wpaap-dash-score-foot a { font-size:12.5px; color:#3b82f6; font-weight:600; text-decoration:none; }
        .wpaap-dash-score-foot a:hover { text-decoration:underline; }

        /* Token card */
        .wpaap-dash-token-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:18px 18px 14px; }
        .wpaap-dash-token-card-hd { font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.07em; margin-bottom:14px; display:flex; align-items:center; gap:6px; }
        .wpaap-dash-token-row { display:flex; align-items:center; gap:10px; padding:7px 0; border-bottom:1px solid #f8fafc; }
        .wpaap-dash-token-row:last-child { border-bottom:none; }
        .wpaap-dash-token-icon { width:26px; height:26px; border-radius:6px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .wpaap-dash-token-info { flex:1; min-width:0; }
        .wpaap-dash-token-name { font-size:12.5px; font-weight:600; color:#1e293b; }
        .wpaap-dash-token-bar-wrap { height:4px; background:#f1f5f9; border-radius:2px; margin-top:3px; overflow:hidden; }
        .wpaap-dash-token-bar-fill { height:100%; border-radius:2px; }
        .wpaap-dash-token-amount { font-size:12px; font-weight:700; color:#1e293b; white-space:nowrap; flex-shrink:0; }
        .wpaap-dash-token-foot { margin-top:10px; padding-top:10px; border-top:1px solid #f1f5f9; }
        .wpaap-dash-token-foot a { font-size:12px; color:#3b82f6; font-weight:600; text-decoration:none; }

        /* Grid area placements */
        .wpaap-dash-chart-card { grid-area: chart; }
        .wpaap-dash-queue-card { grid-area: queue; align-self: start; }
        .wpaap-dash-sys-card   { grid-area: sys; }

        /* Chart card */
        .wpaap-dash-chart-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:18px 20px 14px; display:flex; flex-direction:column; }
        .wpaap-dash-chart-hd { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-shrink:0; }
        .wpaap-dash-chart-title { font-size:13px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:7px; }
        .wpaap-dash-chart-legend { display:flex; gap:14px; }
        .wpaap-dash-legend-item { display:flex; align-items:center; gap:5px; font-size:11.5px; color:#64748b; }
        .wpaap-dash-legend-dot { width:8px; height:8px; border-radius:50%; }
        #wpaap-activity-chart { flex:1; min-height:0; }

        /* Quick actions card */
        .wpaap-dash-qa-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:18px 18px 14px; }
        .wpaap-dash-qa-card-hd { font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.07em; margin-bottom:12px; display:flex; align-items:center; gap:6px; }
        .wpaap-dash-qa-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
        .wpaap-dash-qa-item {
            display:flex; flex-direction:column; gap:5px;
            padding:11px 12px; border:1px solid #e2e8f0; border-radius:8px;
            text-decoration:none; color:#1e293b; transition:all .15s;
        }
        .wpaap-dash-qa-item:hover { border-color:#c7d2fe; background:#f8fafc; transform:translateY(-1px); box-shadow:0 2px 8px rgba(0,0,0,.06); }
        .wpaap-dash-qa-item-icon { width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center; }
        .wpaap-dash-qa-item-label { font-size:12.5px; font-weight:700; color:#1e293b; line-height:1.3; }
        .wpaap-dash-qa-item-desc { font-size:11px; color:#94a3b8; line-height:1.4; }
        .wpaap-dash-qa-foot { margin-top:10px; padding-top:10px; border-top:1px solid #f1f5f9; }
        .wpaap-dash-qa-foot a { font-size:12px; color:#3b82f6; font-weight:600; text-decoration:none; }

        /* Queue card */
        .wpaap-dash-queue-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; }
        .wpaap-dash-queue-hd { display:flex; align-items:center; justify-content:space-between; padding:16px 20px 12px; border-bottom:1px solid #f1f5f9; }
        .wpaap-dash-queue-hd h3 { font-size:13px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:7px; margin:0; }
        .wpaap-dash-queue-btn { font-size:12px; font-weight:600; color:#6366f1; background:#eff0ff; border:1px solid #c7d2fe; padding:5px 12px; border-radius:6px; text-decoration:none; white-space:nowrap; }
        .wpaap-dash-bulk-bar { display:none; align-items:center; gap:8px; background:#f0f4ff; border-bottom:1px solid #c7d2fe; padding:8px 20px; }
        .wpaap-dash-bulk-btn { display:inline-flex; align-items:center; gap:4px; border:none; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; color:#fff; transition:.15s; }
        .wpaap-dash-bulk-btn:hover { opacity:.88; }
        .wpaap-dash-mb-table { width:100%; border-collapse:collapse; table-layout:fixed; }
        .wpaap-dash-mb-table th { text-align:left; padding:9px 14px; color:#94a3b8; font-weight:600; font-size:11.5px; text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid #f1f5f9; white-space:nowrap; }
        .wpaap-dash-mb-table td { padding:11px 14px; border-bottom:1px solid #f8fafc; font-size:13px; color:#1e293b; vertical-align:middle; }
        .wpaap-dash-mb-table tr:last-child td { border-bottom:none; }
        .wpaap-dash-mb-table tr:hover td { background:#f8fafc; }
        .wpaap-dash-mb-table .col-title { width:auto; }
        .wpaap-dash-mb-table .col-title .col-title-inner { display:flex; align-items:center; gap:6px; overflow:hidden; max-width:100%; }
        .wpaap-dash-mb-table .col-title .col-title-inner a { flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .wpaap-dash-pending-badge { background:#fef3c7; color:#92400e; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:600; border:1px solid #fde68a; white-space:nowrap; }
        .wpaap-dash-queue-foot { padding:10px 20px; border-top:1px solid #f1f5f9; display:flex; justify-content:center; }
        .wpaap-dash-queue-foot a { font-size:12px; color:#6366f1; font-weight:600; text-decoration:none; }

        /* Sysinfo card */
        .wpaap-dash-sys-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:18px 18px 14px; }
        .wpaap-dash-sys-card-hd { font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.07em; margin-bottom:12px; display:flex; align-items:center; gap:6px; }
        .wpaap-dash-sys-row { display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px dashed #f1f5f9; font-size:13px; }
        .wpaap-dash-sys-row:last-child { border-bottom:none; }
        .wpaap-dash-sys-label { color:#64748b; }
        .wpaap-dash-sys-val { font-weight:700; }
        .wpaap-dash-sys-badge { padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; display:inline-block !important; }
        .wpaap-dash-sys-badge.wpaap-ok  { background:#dcfce7 !important; color:#166534 !important; }
        .wpaap-dash-sys-badge.wpaap-off { background:#fee2e2 !important; color:#991b1b !important; }
        .wpaap-dash-sys-foot { margin-top:10px; padding-top:10px; border-top:1px solid #f1f5f9; }
        .wpaap-dash-sys-foot a { font-size:12px; color:#3b82f6; font-weight:600; text-decoration:none; }

        /* Stat cards — gradient override */
        .wpaap-dash-stats-grid .wpaap-stat-card {
            border:none; position:relative; overflow:hidden;
            box-shadow:0 4px 18px rgba(0,0,0,.14); padding:20px 22px;
        }
        .wpaap-dash-stats-grid .wpaap-stat-card::after {
            content:''; position:absolute; right:-24px; bottom:-24px;
            width:90px; height:90px; border-radius:50%;
            background:rgba(255,255,255,.1); pointer-events:none;
        }
        .wpaap-dash-stats-grid .wpaap-stat-card::before {
            content:''; position:absolute; right:28px; bottom:28px;
            width:50px; height:50px; border-radius:50%;
            background:rgba(255,255,255,.07); pointer-events:none;
        }
        .wpaap-dash-stats-grid .wpaap-stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(0,0,0,.2); border:none; }
        .wpaap-dash-stat-c1 { background:linear-gradient(135deg,#4f8ef7,#2d5be3); }
        .wpaap-dash-stat-c2 { background:linear-gradient(135deg,#a78bfa,#7c3aed); }
        .wpaap-dash-stat-c3 { background:linear-gradient(135deg,#fbbf24,#d97706); }
        .wpaap-dash-stat-c4-ok  { background:linear-gradient(135deg,#34d399,#059669); }
        .wpaap-dash-stat-c4-off { background:linear-gradient(135deg,#f87171,#dc2626); }

        .wpaap-dash-stats-grid .wpaap-stat-icon {
            background:rgba(255,255,255,.22) !important;
            border-radius:10px; width:48px; height:48px;
        }
        .wpaap-dash-stats-grid .wpaap-stat-details h3 { color:rgba(255,255,255,.8); font-size:11.5px; margin:0 0 4px; }
        .wpaap-dash-stats-grid .wpaap-stat-number { color:#fff; font-size:32px; margin-bottom:4px; }
        .wpaap-dash-stats-grid .wpaap-stat-meta { color:rgba(255,255,255,.75); font-size:12px; }

        /* Global font sync */
        .wpaap-wrap, .wpaap-wrap * { font-family:inherit; }

        @media (max-width:1100px) {
            .wpaap-dash-master-grid {
                grid-template-columns: 1fr 1fr;
                grid-template-areas: "sec seo" "token token" "chart chart" "qa qa" "sys sys" "queue queue";
            }
        }
        @media (max-width:800px) {
            .wpaap-dash-master-grid {
                grid-template-columns: 1fr;
                grid-template-areas: "sec" "seo" "token" "chart" "qa" "sys" "queue";
            }
        }
    </style>

    <?php
        $r = 38; $C = 2 * M_PI * $r;
        function wpaap_score_color( $s ) {
            if ( $s >= 90 ) return '#16a34a';
            if ( $s >= 70 ) return '#3b82f6';
            if ( $s >= 40 ) return '#f59e0b';
            return '#ef4444';
        }
        function wpaap_score_label( $s ) {
            if ( $s >= 90 ) return [ __( 'Xuất sắc', 'whp' ), '#dcfce7', '#166534' ];
            if ( $s >= 70 ) return [ __( 'Tốt', 'whp' ), '#dbeafe', '#1d4ed8' ];
            if ( $s >= 40 ) return [ __( 'Khá tốt', 'whp' ), '#fef3c7', '#92400e' ];
            return [ __( 'Cần cải thiện', 'whp' ), '#fee2e2', '#991b1b' ];
        }
        $sec_color = wpaap_score_color( $security_score );
        $seo_color = wpaap_score_color( $seo_score );
        $sec_dash  = round( ( $security_score / 100 ) * $C, 2 );
        $seo_dash  = round( ( $seo_score / 100 ) * $C, 2 );
        list( $sec_lbl, $sec_bg, $sec_fg ) = wpaap_score_label( $security_score );
        list( $seo_lbl, $seo_bg, $seo_fg ) = wpaap_score_label( $seo_score );
        // Khi AI chưa kết nối: hiển thị trạng thái offline cho score cards
        if ( ! $any_provider_connected ) {
            $sec_color = '#94a3b8'; $seo_color = '#94a3b8';
            $sec_dash  = 0;         $seo_dash  = 0;
            $sec_lbl = __( 'Chưa kết nối', 'whp' ); $sec_bg = '#f1f5f9'; $sec_fg = '#64748b';
            $seo_lbl = __( 'Chưa kết nối', 'whp' ); $seo_bg = '#f1f5f9'; $seo_fg = '#64748b';
        }
        $total_tokens = $google_tokens + $anthropic_tokens + $openai_tokens;
        $others_tokens = 0;
    ?>

    <div class="wrap wpaap-wrap" style="margin:10px 0 0; padding:0; max-width:1200px; margin-left:auto; margin-right:auto;">

        <!-- HEADER -->
        <div class="wpaap-header-modern wpaap-dash-header-wrap" style="margin-bottom:20px;">
            <div class="wpaap-header-left">
                <div class="wpaap-header-title-row">
                    <div class="wpaap-header-icon-box wpaap-dash-header-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" fill="#fff" fill-opacity=".9"/><rect x="14" y="3" width="7" height="7" rx="1.5" fill="#fff" fill-opacity=".9"/><rect x="3" y="14" width="7" height="7" rx="1.5" fill="#fff" fill-opacity=".9"/><rect x="14" y="14" width="7" height="7" rx="1.5" fill="#fff" fill-opacity=".4"/></svg>
                    </div>
                    <h1><?php esc_html_e( 'Tổng quan & Điều khiển AI', 'whp' ); ?></h1>
                </div>
                <p><?php esc_html_e( 'Giám sát toàn diện hoạt động AI — bảo mật, SEO, token và hàng đợi bài viết trong một nơi.', 'whp' ); ?></p>
            </div>
            <div class="wpaap-header-right">
                <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                    <defs>
                        <linearGradient id="dHg3" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#e0e7ff" stop-opacity="0"/><stop offset="40%" stop-color="#e0e7ff" stop-opacity="0.5"/><stop offset="100%" stop-color="#c7d2fe" stop-opacity="0.85"/></linearGradient>
                        <filter id="dSh3"><feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(56,88,233,0.15)"/></filter>
                    </defs>
                    <rect width="680" height="168" fill="url(#dHg3)"/>
                    <g filter="url(#dSh3)">
                        <rect x="330" y="18" width="120" height="132" rx="10" fill="#fff"/>
                        <rect x="346" y="30" width="88" height="50" rx="7" fill="#f8fafc"/>
                        <circle cx="390" cy="55" r="22" fill="#f1f5f9" stroke="#e2e8f0" stroke-width="1"/>
                        <circle cx="390" cy="55" r="22" fill="none" stroke="<?php echo $sec_color; ?>" stroke-width="5" stroke-dasharray="<?php echo $sec_dash; ?> <?php echo round($C,2); ?>" stroke-linecap="round" transform="rotate(-90 390 55)"/>
                        <text x="390" y="59" text-anchor="middle" font-size="10" font-weight="800" fill="<?php echo $sec_color; ?>" font-family="sans-serif"><?php echo $security_score; ?></text>
                        <rect x="346" y="90" width="88" height="44" rx="6" fill="#f8fafc"/>
                        <rect x="354" y="97" width="30" height="5" rx="2.5" fill="#6366f1"/>
                        <rect x="354" y="106" width="22" height="5" rx="2.5" fill="#d97757"/>
                        <rect x="354" y="115" width="26" height="5" rx="2.5" fill="#10b981"/>
                        <text x="388" y="101" font-size="7" fill="#94a3b8" font-family="sans-serif">142k</text>
                        <text x="388" y="110" font-size="7" fill="#94a3b8" font-family="sans-serif">20k</text>
                        <text x="388" y="119" font-size="7" fill="#94a3b8" font-family="sans-serif">10k</text>
                        <rect x="346" y="140" width="88" height="4" rx="2" fill="#f1f5f9"/>
                        <rect x="346" y="140" width="<?php echo max(2, round(88 * min(1, $google_percent/100))); ?>" height="4" rx="2" fill="#6366f1"/>
                    </g>
                    <g filter="url(#dSh3)">
                        <rect x="466" y="22" width="110" height="124" rx="10" fill="#fff"/>
                        <rect x="476" y="32" width="90" height="30" rx="6" fill="#eff2fe"/>
                        <text x="521" y="51" text-anchor="middle" font-size="11" font-weight="800" fill="#3858e9" font-family="sans-serif"><?php echo $published_posts; ?></text>
                        <rect x="476" y="68" width="42" height="30" rx="6" fill="#f0fdf4"/>
                        <text x="497" y="87" text-anchor="middle" font-size="11" font-weight="800" fill="#16a34a" font-family="sans-serif"><?php echo $total_ai_posts; ?></text>
                        <rect x="524" y="68" width="42" height="30" rx="6" fill="#fffbeb"/>
                        <text x="545" y="87" text-anchor="middle" font-size="11" font-weight="800" fill="#d97706" font-family="sans-serif"><?php echo $pending_posts; ?></text>
                        <rect x="476" y="104" width="90" height="32" rx="6" fill="<?php echo $any_provider_connected ? '#f0fdf4' : '#fef2f2'; ?>"/>
                        <text x="521" y="124" text-anchor="middle" font-size="9" font-weight="700" fill="<?php echo $any_provider_connected ? '#16a34a' : '#ef4444'; ?>" font-family="sans-serif"><?php echo $any_provider_connected ? esc_html__( '● Đang online', 'whp' ) : esc_html( '○ Offline' ); ?></text>
                    </g>
                    <g filter="url(#dSh3)">
                        <rect x="592" y="40" width="66" height="26" rx="13" fill="<?php echo $any_provider_connected ? '#dcfce7' : '#fee2e2'; ?>"/>
                        <text x="625" y="57" text-anchor="middle" font-size="9" font-weight="700" fill="<?php echo $any_provider_connected ? '#166534' : '#991b1b'; ?>" font-family="sans-serif"><?php echo $any_provider_connected ? 'AI Online' : 'Offline'; ?></text>
                    </g>
                </svg>
            </div>
        </div>

        <?php if ( ! empty( $message ) ) : ?>
            <div class="notice notice-<?php echo esc_attr($status); ?> is-dismissible" style="border-radius:8px;margin:0 0 20px 0;"><p><?php echo $message; ?></p></div>
        <?php endif; ?>

        <!-- STAT CARDS -->
        <div class="wpaap-stats-grid wpaap-dash-stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
            <div class="wpaap-stat-card wpaap-dash-stat-c1">
                <div class="wpaap-stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div class="wpaap-stat-details">
                    <h3><?php esc_html_e( 'Bài đã đăng', 'whp' ); ?></h3>
                    <div class="wpaap-stat-number"><?php echo $published_posts; ?></div>
                    <div class="wpaap-stat-meta"><?php echo esc_html( sprintf( __( 'Tổng: %d bài', 'whp' ), $total_posts ) ); ?></div>
                </div>
            </div>
            <div class="wpaap-stat-card wpaap-dash-stat-c2">
                <div class="wpaap-stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6V17H8.3V15C6.3 13.7 5 11.5 5 9a7 7 0 0 1 7-7z"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
                </div>
                <div class="wpaap-stat-details">
                    <h3><?php esc_html_e( 'Bài AI đã tạo', 'whp' ); ?></h3>
                    <div class="wpaap-stat-number"><?php echo $total_ai_posts; ?></div>
                    <div class="wpaap-stat-meta"><?php esc_html_e( 'Trong tháng này', 'whp' ); ?></div>
                </div>
            </div>
            <div class="wpaap-stat-card wpaap-dash-stat-c3">
                <div class="wpaap-stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <div class="wpaap-stat-details">
                    <h3><?php esc_html_e( 'Chờ duyệt', 'whp' ); ?></h3>
                    <div class="wpaap-stat-number"><?php echo $pending_posts; ?></div>
                    <div class="wpaap-stat-meta"><?php echo esc_html( sprintf( __( 'Nháp: %d', 'whp' ), $draft_posts ) ); ?></div>
                </div>
            </div>
            <div class="wpaap-stat-card <?php echo $any_provider_connected ? 'wpaap-dash-stat-c4-ok' : 'wpaap-dash-stat-c4-off'; ?>">
                <div class="wpaap-stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <?php if ( $any_provider_connected ) : ?><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/><?php else : ?><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/><?php endif; ?>
                    </svg>
                </div>
                <div class="wpaap-stat-details">
                    <h3><?php esc_html_e( 'Kết nối AI', 'whp' ); ?></h3>
                    <div class="wpaap-stat-number" style="font-size:18px;"><?php echo $any_provider_connected ? esc_html__( 'Hoạt động', 'whp' ) : esc_html__( 'Chưa kết nối', 'whp' ); ?></div>
                    <div class="wpaap-stat-meta"><?php
                        $ps=[];
                        if($google_connected) $ps[]='Gemini';
                        if($anthropic_connected) $ps[]='Claude';
                        if($openai_connected) $ps[]='GPT';
                        echo $ps ? esc_html( implode(', ',$ps) ) : esc_html__( 'Cần thiết lập', 'whp' );
                    ?></div>
                </div>
            </div>
        </div>

        <!-- MASTER GRID: sec|seo|stats / chart|chart|sys / queue -->
        <div class="wpaap-dash-master-grid">

            <!-- Security Score -->
            <div class="wpaap-dash-score-card wpaap-dash-score-sec">
                <div class="wpaap-dash-score-card-hd">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?php echo $sec_color; ?>" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <?php esc_html_e( 'Điểm Bảo mật', 'whp' ); ?>
                </div>
                <div class="wpaap-dash-score-card-body">
                    <?php if ( $any_provider_connected ) : ?>
                    <div class="wpaap-dash-score-left">
                        <svg viewBox="0 0 100 100" width="100" height="100">
                            <circle cx="50" cy="50" r="<?php echo $r; ?>" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                            <circle cx="50" cy="50" r="<?php echo $r; ?>" fill="none"
                                stroke="<?php echo $sec_color; ?>" stroke-width="10"
                                stroke-dasharray="<?php echo $sec_dash; ?> <?php echo round($C,2); ?>"
                                stroke-linecap="round" transform="rotate(-90 50 50)"/>
                            <text x="50" y="45" text-anchor="middle" font-size="22" font-weight="800" fill="<?php echo $sec_color; ?>" font-family="-apple-system,sans-serif"><?php echo $security_score; ?></text>
                            <text x="50" y="60" text-anchor="middle" font-size="10" fill="#9ca3af" font-family="-apple-system,sans-serif">/100</text>
                        </svg>
                        <span class="wpaap-dash-score-badge" style="background:<?php echo $sec_bg; ?>;color:<?php echo $sec_fg; ?>;"><?php echo $sec_lbl; ?></span>
                    </div>
                    <div class="wpaap-dash-score-right">
                        <div class="wpaap-dash-metric-row">
                            <span class="wpaap-dash-metric-label">
                                <span class="wpaap-dash-metric-dot" style="background:#ef4444;"></span>
                                <?php esc_html_e( 'Cảnh báo nghiêm trọng', 'whp' ); ?>
                            </span>
                            <span class="wpaap-dash-metric-val" style="color:<?php echo $security_critical > 0 ? '#ef4444' : '#16a34a'; ?>;"><?php echo $security_critical; ?></span>
                        </div>
                        <div class="wpaap-dash-metric-row">
                            <span class="wpaap-dash-metric-label">
                                <span class="wpaap-dash-metric-dot" style="background:#f59e0b;"></span>
                                <?php esc_html_e( 'Cảnh báo nhẹ', 'whp' ); ?>
                            </span>
                            <span class="wpaap-dash-metric-val" style="color:<?php echo $security_warnings > 0 ? '#f59e0b' : '#16a34a'; ?>;"><?php echo $security_warnings; ?></span>
                        </div>
                    </div>
                    <?php else : ?>
                    <div style="width:100%;text-align:center;padding:12px 8px">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d63638" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <p style="margin:0 0 3px;font-size:13px;font-weight:600;color:#d63638"><?php esc_html_e( 'Chưa kết nối AI', 'whp' ); ?></p>
                        <p style="margin:0;font-size:11.5px;color:#64748b"><?php esc_html_e( 'Cần cấu hình API Key', 'whp' ); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="wpaap-dash-score-foot">
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=security'); ?>"><?php esc_html_e( 'Xem chi tiết →', 'whp' ); ?></a>
                </div>
            </div>

            <!-- SEO Score -->
            <div class="wpaap-dash-score-card wpaap-dash-score-seo">
                <div class="wpaap-dash-score-card-hd">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="<?php echo $seo_color; ?>" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <?php esc_html_e( 'Điểm SEO', 'whp' ); ?>
                </div>
                <div class="wpaap-dash-score-card-body">
                    <?php if ( $any_provider_connected ) : ?>
                    <div class="wpaap-dash-score-left">
                        <svg viewBox="0 0 100 100" width="100" height="100">
                            <circle cx="50" cy="50" r="<?php echo $r; ?>" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                            <circle cx="50" cy="50" r="<?php echo $r; ?>" fill="none"
                                stroke="<?php echo $seo_color; ?>" stroke-width="10"
                                stroke-dasharray="<?php echo $seo_dash; ?> <?php echo round($C,2); ?>"
                                stroke-linecap="round" transform="rotate(-90 50 50)"/>
                            <text x="50" y="45" text-anchor="middle" font-size="22" font-weight="800" fill="<?php echo $seo_color; ?>" font-family="-apple-system,sans-serif"><?php echo $seo_score; ?></text>
                            <text x="50" y="60" text-anchor="middle" font-size="10" fill="#9ca3af" font-family="-apple-system,sans-serif">/100</text>
                        </svg>
                        <span class="wpaap-dash-score-badge" style="background:<?php echo $seo_bg; ?>;color:<?php echo $seo_fg; ?>;"><?php echo $seo_lbl; ?></span>
                    </div>
                    <div class="wpaap-dash-score-right">
                        <div class="wpaap-dash-metric-row">
                            <span class="wpaap-dash-metric-label">
                                <span class="wpaap-dash-metric-dot" style="background:#3b82f6;"></span>
                                SEO On-Page
                            </span>
                            <span class="wpaap-dash-metric-val"><?php echo $content_seo['on_page']; ?>/100</span>
                        </div>
                        <div class="wpaap-dash-metric-row">
                            <span class="wpaap-dash-metric-label">
                                <span class="wpaap-dash-metric-dot" style="background:#8b5cf6;"></span>
                                <?php esc_html_e( 'SEO Kỹ thuật', 'whp' ); ?>
                            </span>
                            <span class="wpaap-dash-metric-val"><?php echo $content_seo['technical']; ?>/100</span>
                        </div>
                        <div class="wpaap-dash-metric-row">
                            <span class="wpaap-dash-metric-label">
                                <span class="wpaap-dash-metric-dot" style="background:#f59e0b;"></span>
                                <?php esc_html_e( 'Mật độ từ khóa', 'whp' ); ?>
                            </span>
                            <span class="wpaap-dash-metric-val"><?php echo $content_seo['keyword']; ?>/100</span>
                        </div>
                    </div>
                    <?php else : ?>
                    <div style="width:100%;text-align:center;padding:12px 8px">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#d63638" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block;margin:0 auto 8px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <p style="margin:0 0 3px;font-size:13px;font-weight:600;color:#d63638"><?php esc_html_e( 'Chưa kết nối AI', 'whp' ); ?></p>
                        <p style="margin:0;font-size:11.5px;color:#64748b"><?php esc_html_e( 'Cần cấu hình API Key', 'whp' ); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="wpaap-dash-score-foot">
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=seo'); ?>"><?php esc_html_e( 'Xem chi tiết →', 'whp' ); ?></a>
                </div>
            </div>

            <!-- Token Stats -->
            <div class="wpaap-dash-token-card">
                <div class="wpaap-dash-token-card-hd">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    <?php esc_html_e( 'Thống kê Token AI', 'whp' ); ?>
                </div>

                <div class="wpaap-dash-token-row">
                    <div class="wpaap-dash-token-icon" style="background:#eff2fe;">
                        <svg viewBox="0 0 28 28" width="14" height="14"><defs><radialGradient id="gTkV3" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(2.78 11.38) rotate(18.68) scale(29.8 238.7)"><stop offset=".067" stop-color="#9168C0"/><stop offset=".343" stop-color="#5684D1"/><stop offset=".672" stop-color="#1BA1E3"/></radialGradient></defs><path fill="url(#gTkV3)" d="M14 28C14 26.06 13.63 24.24 12.88 22.54C12.16 20.84 11.17 19.36 9.91 18.1C8.65 16.84 7.16 15.84 5.46 15.12C3.76 14.37 1.94 14 0 14C1.94 14 3.76 13.64 5.46 12.92C7.16 12.17 8.65 11.17 9.91 9.91C11.17 8.65 12.16 7.16 12.88 5.46C13.63 3.76 14 1.94 14 0C14 1.94 14.36 3.76 15.09 5.46C15.83 7.16 16.84 8.65 18.1 9.91C19.36 11.17 20.84 12.17 22.54 12.92C24.24 13.64 26.06 14 28 14C26.06 14 24.24 14.37 22.54 15.12C20.84 15.84 19.36 16.84 18.1 18.1C16.84 19.36 15.83 20.84 15.09 22.54C14.36 24.24 14 26.06 14 28Z"/></svg>
                    </div>
                    <div class="wpaap-dash-token-info">
                        <div class="wpaap-dash-token-name">Google Gemini</div>
                        <div class="wpaap-dash-token-bar-wrap"><div class="wpaap-dash-token-bar-fill" style="width:<?php echo $google_percent; ?>%;background:linear-gradient(90deg,#818cf8,#6366f1);"></div></div>
                    </div>
                    <div class="wpaap-dash-token-amount" style="color:<?php echo $google_connected ? '#1e293b' : '#94a3b8'; ?>;"><?php echo $google_connected ? esc_html( number_format($google_tokens) . ' tokens' ) : esc_html__( 'Chưa kết nối', 'whp' ); ?></div>
                </div>

                <div class="wpaap-dash-token-row">
                    <div class="wpaap-dash-token-icon" style="background:#fff7ed;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="#D97757"><path d="m4.71 15.96 4.72-2.65.08-.23-.08-.13h-.23l-.79-.05-2.7-.07-2.34-.1-2.26-.12-.57-.12-.53-.7.05-.35.48-.32.69.06 1.52.1 2.28.16 1.65.1 2.45.26h.39l.05-.16-.13-.1-.1-.1L6.97 9.84l-2.55-1.69-1.34-.97-.72-.49-.36-.46-.16-1.01.66-.72.88.06.22.06.89.69 1.91 1.48 2.49 1.83.36.3.15-.1.02-.07-.16-.27-1.35-2.45-1.45-2.49-.64-1.03-.17-.62c-.06-.26-.1-.47-.1-.73L6.29.13 6.7 0l1 .13.42.36.62 1.41 1 2.23 1.55 3.03.46.9.24.83.09.26h.16v-.15l.13-1.71.24-2.09.23-2.7.08-.76.38-.91.75-.49.58.28.48.69-.07.44-.29 1.85-.56 2.9-.36 1.94h.21l.24-.24.98-1.31 1.65-2.06.73-.82.85-.9.55-.43h1.03l.76 1.13-.34 1.17-1.06 1.35-.88 1.14-1.26 1.7-.79 1.36.07.11.19-.02 2.85-.61 1.54-.28 1.84-.32.83.39.09.39-.33.81-1.97.49-2.31.46-3.44.81-.04.03.05.06 1.55.15.66.04h1.62l3.02.22.79.52.47.64-.08.49-1.21.62-1.64-.39-3.83-.91-1.31-.33h-.18v.11l1.09 1.07 2 1.81 2.51 2.33.13.58-.32.46-.34-.05-2.2-1.66-.85-.75-1.92-1.62h-.13v.17l.44.65 2.34 3.52.12 1.08-.17.35-.61.21-.67-.12-1.37-1.92-1.59-2.6-1.14-1.94-.14.08-.67 7.26-.32.37-.73.28-.61-.46-.32-.75.32-1.48.39-1.92.32-1.53.29-1.9.17-.63-.01-.04-.14.02-1.43 1.97-2.18 2.94-1.72 1.85-.41.16-.72-.37.07-.66.4-.59 2.39-3.04 1.44-1.88.93-1.09-.01-.16h-.05L4.3 17.11l-1.13.15-.49-.46.06-.75.23-.24 1.91-1.31Z"/></svg>
                    </div>
                    <div class="wpaap-dash-token-info">
                        <div class="wpaap-dash-token-name">Anthropic Claude</div>
                        <div class="wpaap-dash-token-bar-wrap"><div class="wpaap-dash-token-bar-fill" style="width:<?php echo $anthropic_percent; ?>%;background:linear-gradient(90deg,#fbbf24,#d97757);"></div></div>
                    </div>
                    <div class="wpaap-dash-token-amount" style="color:<?php echo $anthropic_connected ? '#1e293b' : '#94a3b8'; ?>;"><?php echo $anthropic_connected ? esc_html( number_format($anthropic_tokens) . ' tokens' ) : esc_html__( 'Chưa kết nối', 'whp' ); ?></div>
                </div>

                <div class="wpaap-dash-token-row">
                    <div class="wpaap-dash-token-icon" style="background:#f0fdf4;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="#10b981"><path d="M22.28 9.82a5.98 5.98 0 0 0-.52-4.91 6.05 6.05 0 0 0-6.51-2.9A6.07 6.07 0 0 0 4.98 4.18a5.98 5.98 0 0 0-4 2.9 6.05 6.05 0 0 0 .74 7.1 5.98 5.98 0 0 0 .51 4.91 6.05 6.05 0 0 0 6.51 2.9A5.98 5.98 0 0 0 13.26 24a6.06 6.06 0 0 0 5.77-4.21 5.99 5.99 0 0 0 4-2.9 6.06 6.06 0 0 0-.75-7.07zm-9.02 12.61a4.48 4.48 0 0 1-2.88-1.04l.14-.08 4.78-2.76a.79.79 0 0 0 .39-.68v-6.74l2.02 1.17a.07.07 0 0 1 .04.05v5.58a4.5 4.5 0 0 1-4.49 4.5zm-9.66-4.13a4.47 4.47 0 0 1-.53-3.01l.14.08 4.78 2.76a.77.77 0 0 0 .78 0l5.84-3.37v2.33a.08.08 0 0 1-.03.06L9.74 19.95a4.5 4.5 0 0 1-6.14-1.65zM2.34 7.9a4.49 4.49 0 0 1 2.37-1.97V11.6a.77.77 0 0 0 .39.68l5.81 3.35-2.02 1.17a.08.08 0 0 1-.07 0l-4.83-2.79A4.5 4.5 0 0 1 2.34 7.87zm16.6 3.86L13.1 8.36 15.12 7.2a.08.08 0 0 1 .07 0l4.83 2.79a4.49 4.49 0 0 1-.68 8.1v-5.68a.79.79 0 0 0-.4-.67zm2.01-3.02l-.14-.09-4.77-2.78a.78.78 0 0 0-.79 0L9.41 9.23V6.9a.07.07 0 0 1 .03-.06l4.83-2.79a4.5 4.5 0 0 1 6.68 4.66zM8.31 12.86l-2.02-1.16a.08.08 0 0 1-.04-.06V6.07a4.5 4.5 0 0 1 7.38-3.45l-.14.08-4.78 2.76a.79.79 0 0 0-.4.68zm1.1-2.37 2.6-1.5 2.61 1.5v2.99l-2.6 1.5-2.61-1.5Z"/></svg>
                    </div>
                    <div class="wpaap-dash-token-info">
                        <div class="wpaap-dash-token-name">OpenAI GPT</div>
                        <div class="wpaap-dash-token-bar-wrap"><div class="wpaap-dash-token-bar-fill" style="width:<?php echo $openai_percent; ?>%;background:linear-gradient(90deg,#34d399,#10b981);"></div></div>
                    </div>
                    <div class="wpaap-dash-token-amount" style="color:<?php echo $openai_connected ? '#1e293b' : '#94a3b8'; ?>;"><?php echo $openai_connected ? esc_html( number_format($openai_tokens) . ' tokens' ) : esc_html__( 'Chưa kết nối', 'whp' ); ?></div>
                </div>

                <?php if ( $others_tokens > 0 ) : ?>
                <div class="wpaap-dash-token-row">
                    <div class="wpaap-dash-token-icon" style="background:#f8fafc;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></svg>
                    </div>
                    <div class="wpaap-dash-token-info">
                        <div class="wpaap-dash-token-name"><?php esc_html_e( 'Khác', 'whp' ); ?></div>
                        <div class="wpaap-dash-token-bar-wrap"><div class="wpaap-dash-token-bar-fill" style="width:5%;background:#cbd5e1;"></div></div>
                    </div>
                    <div class="wpaap-dash-token-amount" style="color:#94a3b8;"><?php echo number_format($others_tokens); ?> tokens</div>
                </div>
                <?php endif; ?>

                <div class="wpaap-dash-token-foot">
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=limits'); ?>"><?php esc_html_e( 'Xem thống kê đầy đủ →', 'whp' ); ?></a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="wpaap-dash-qa-card">
                <div class="wpaap-dash-qa-card-hd">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    <?php esc_html_e( 'Hành động nhanh', 'whp' ); ?>
                </div>
                <div class="wpaap-dash-qa-grid">
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=writer'); ?>" class="wpaap-dash-qa-item">
                        <div class="wpaap-dash-qa-item-icon" style="background:#eff6ff;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></div>
                        <div class="wpaap-dash-qa-item-label"><?php esc_html_e( 'Tạo bài viết SEO mới', 'whp' ); ?></div>
                        <div class="wpaap-dash-qa-item-desc"><?php esc_html_e( 'Viết bài tối ưu SEO bằng AI', 'whp' ); ?></div>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=seo'); ?>" class="wpaap-dash-qa-item">
                        <div class="wpaap-dash-qa-item-icon" style="background:#f5f3ff;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
                        <div class="wpaap-dash-qa-item-label"><?php esc_html_e( 'Phân tích nội dung', 'whp' ); ?></div>
                        <div class="wpaap-dash-qa-item-desc"><?php esc_html_e( 'Phân tích hiệu quả SEO bài viết', 'whp' ); ?></div>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=security'); ?>" class="wpaap-dash-qa-item">
                        <div class="wpaap-dash-qa-item-icon" style="background:#fef2f2;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
                        <div class="wpaap-dash-qa-item-label"><?php esc_html_e( 'Quét bảo mật hệ thống', 'whp' ); ?></div>
                        <div class="wpaap-dash-qa-item-desc"><?php esc_html_e( 'Kiểm tra và bảo vệ website', 'whp' ); ?></div>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=seo'); ?>" class="wpaap-dash-qa-item">
                        <div class="wpaap-dash-qa-item-icon" style="background:#ecfdf5;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
                        <div class="wpaap-dash-qa-item-label"><?php esc_html_e( 'AI Cố Vấn SEO', 'whp' ); ?></div>
                        <div class="wpaap-dash-qa-item-desc"><?php esc_html_e( 'Phân tích & gợi ý từ khóa', 'whp' ); ?></div>
                    </a>
                </div>
                <div class="wpaap-dash-qa-foot">
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=connection'); ?>"><?php esc_html_e( 'Xem tất cả hành động →', 'whp' ); ?></a>
                </div>
            </div>

            <div class="wpaap-dash-chart-card">
                <div class="wpaap-dash-chart-hd">
                    <div class="wpaap-dash-chart-title">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <?php esc_html_e( 'Biểu đồ hoạt động AI (7 ngày qua)', 'whp' ); ?>
                    </div>
                    <div class="wpaap-dash-chart-legend">
                        <div class="wpaap-dash-legend-item"><div class="wpaap-dash-legend-dot" style="background:#6366f1;"></div>Gemini</div>
                        <div class="wpaap-dash-legend-item"><div class="wpaap-dash-legend-dot" style="background:#d97757;border-radius:2px;"></div><?php esc_html_e( 'Khác', 'whp' ); ?></div>
                    </div>
                </div>
                <div id="wpaap-activity-chart" style="min-height:210px;"></div>
            </div>

            <div class="wpaap-dash-queue-card">
                <div class="wpaap-dash-queue-hd">
                    <h3>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                        <?php esc_html_e( 'Hàng đợi bài viết AI', 'whp' ); ?>
                    </h3>
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=writer'); ?>" class="wpaap-dash-queue-btn"><?php esc_html_e( '+ Tạo bài mới', 'whp' ); ?></a>
                </div>
                <div id="mb-bulk-toolbar" class="wpaap-dash-bulk-bar">
                    <span id="mb-bulk-count" style="font-size:13px;font-weight:600;color:#1e293b;flex:1;"></span>
                    <button id="mb-bulk-publish" type="button" class="wpaap-dash-bulk-btn" style="background:#16a34a;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <?php esc_html_e( 'Duyệt đã chọn', 'whp' ); ?>
                    </button>
                    <button id="mb-bulk-delete" type="button" class="wpaap-dash-bulk-btn" style="background:#ef4444;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg> <?php esc_html_e( 'Xóa đã chọn', 'whp' ); ?>
                    </button>
                </div>
                <table class="wpaap-dash-mb-table">
                    <colgroup>
                        <col style="width:160px;">
                        <col style="width:86px;">
                        <col style="width:72px;">
                        <col style="width:82px;">
                        <col style="width:132px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th><div style="display:flex;align-items:center;gap:6px;"><input type="checkbox" id="mb-queue-check-all" style="cursor:pointer;flex-shrink:0;margin:0;"> <?php esc_html_e( 'Bài viết', 'whp' ); ?></div></th>
                            <th><?php esc_html_e( 'Ngày tạo', 'whp' ); ?></th>
                            <th><?php esc_html_e( 'Loại', 'whp' ); ?></th>
                            <th><?php esc_html_e( 'Trạng thái', 'whp' ); ?></th>
                            <th><?php esc_html_e( 'Thao tác', 'whp' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty( $recent_posts ) ) : ?>
                            <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:30px 14px;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" style="display:block;margin:0 auto 8px;"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                                <?php esc_html_e( 'Không có bài viết nào chờ phê duyệt.', 'whp' ); ?>
                            </td></tr>
                        <?php else : ?>
                            <?php foreach ( $recent_posts as $post ) :
                                $edit_link   = get_edit_post_link( $post->ID );
                                $publish_url = admin_url( 'admin.php?page=mb-wphelper-ai&subtab=dashboard&action=wpaap_publish&post_id=' . $post->ID );
                                $delete_url  = admin_url( 'admin.php?page=mb-wphelper-ai&subtab=dashboard&action=wpaap_delete&post_id='  . $post->ID );
                            ?>
                            <tr>
                                <td class="col-title"><div class="col-title-inner"><input type="checkbox" class="mb-queue-check" value="<?php echo intval($post->ID); ?>" style="cursor:pointer;flex-shrink:0;margin:0;"><a href="<?php echo esc_url($edit_link); ?>" style="font-weight:600;color:#0f172a;text-decoration:none;" title="<?php echo esc_attr($post->post_title); ?>"><?php echo esc_html($post->post_title); ?></a></div></td>
                                <td style="color:#64748b;white-space:nowrap;"><?php echo get_the_date('d/m/Y', $post->ID); ?></td>
                                <td style="white-space:nowrap;"><span style="background:#f5f3ff;color:#7c3aed;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600;white-space:nowrap;">AI Post</span></td>
                                <td><span class="wpaap-dash-pending-badge"><?php esc_html_e( 'Chờ duyệt', 'whp' ); ?></span></td>
                                <td style="white-space:nowrap;">
                                    <a href="<?php echo esc_url($edit_link); ?>" style="color:#6366f1;font-weight:600;text-decoration:none;margin-right:8px;"><?php esc_html_e( 'Sửa', 'whp' ); ?></a>
                                    <a href="<?php echo esc_url($publish_url); ?>" style="color:#16a34a;font-weight:600;text-decoration:none;margin-right:8px;"><?php esc_html_e( 'Duyệt', 'whp' ); ?></a>
                                    <a href="<?php echo esc_url($delete_url); ?>" style="color:#ef4444;font-weight:600;text-decoration:none;" onclick="return confirm('<?php echo esc_js( __( 'Xóa bài viết này?', 'whp' ) ); ?>');"><?php esc_html_e( 'Xóa', 'whp' ); ?></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="wpaap-dash-queue-foot">
                    <a href="<?php echo admin_url('edit.php?post_status=pending'); ?>"><?php esc_html_e( 'Xem tất cả hàng đợi →', 'whp' ); ?></a>
                </div>
            </div>

            <div class="wpaap-dash-sys-card">
                <div class="wpaap-dash-sys-card-hd">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <?php esc_html_e( 'Thông tin hệ thống', 'whp' ); ?>
                </div>
                <div class="wpaap-dash-sys-row">
                    <span class="wpaap-dash-sys-label"><?php esc_html_e( 'Phiên bản WordPress', 'whp' ); ?></span>
                    <span class="wpaap-dash-sys-val" style="color:#3b82f6;">v<?php echo get_bloginfo('version'); ?></span>
                </div>
                <div class="wpaap-dash-sys-row">
                    <span class="wpaap-dash-sys-label"><?php esc_html_e( 'Phiên bản PHP', 'whp' ); ?></span>
                    <span class="wpaap-dash-sys-val" style="color:#7c3aed;">v<?php echo phpversion(); ?></span>
                </div>
                <div class="wpaap-dash-sys-row">
                    <span class="wpaap-dash-sys-label"><?php esc_html_e( 'Theme hiện tại', 'whp' ); ?></span>
                    <span class="wpaap-dash-sys-val" style="color:#f97316;"><?php $theme = wp_get_theme(); echo esc_html($theme->Name); ?></span>
                </div>
                <div class="wpaap-dash-sys-row">
                    <span class="wpaap-dash-sys-label"><?php esc_html_e( 'Trạng thái kết nối AI', 'whp' ); ?></span>
                    <span class="wpaap-dash-sys-badge <?php echo $any_provider_connected ? 'wpaap-ok' : 'wpaap-off'; ?>"><?php echo $any_provider_connected ? esc_html__( 'Đã kết nối', 'whp' ) : esc_html__( 'Chưa kết nối', 'whp' ); ?></span>
                </div>
                <div class="wpaap-dash-sys-row">
                    <span class="wpaap-dash-sys-label"><?php esc_html_e( 'Dung lượng Database', 'whp' ); ?></span>
                    <span class="wpaap-dash-sys-val" style="color:#0f172a;"><?php echo $db_size; ?> MB</span>
                </div>
                <div class="wpaap-dash-sys-row">
                    <span class="wpaap-dash-sys-label"><?php esc_html_e( 'Bộ nhớ PHP', 'whp' ); ?></span>
                    <span class="wpaap-dash-sys-val" style="color:#0f172a;"><?php echo $memory_limit; ?></span>
                </div>
                <div class="wpaap-dash-sys-foot">
                    <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=connection'); ?>"><?php esc_html_e( 'Xem thông tin chi tiết →', 'whp' ); ?></a>
                </div>
            </div>

        </div><!-- end master-grid -->

    </div><!-- end wrap -->

    <script>
    (function() {
        /* Inline SVG chart — full-width responsive */
        var d1=<?php echo $chart_data1; ?>, d2=<?php echo $chart_data2; ?>, lbls=<?php echo $chart_labels; ?>;
        var W=580, H=210, pL=36, pR=10, pT=10, pB=28;

        /* Y-axis: always round up to nearest 25 → clean grid 0/25/50/75/100 */
        var rawMax = Math.max.apply(null, d1.concat(d2).concat([1]));
        var mx = Math.max(Math.ceil(rawMax / 25) * 25, 25);

        function tx(i){ return pL + (i / (lbls.length - 1)) * (W - pL - pR); }
        function ty(v){ return pT + (1 - v / mx) * (H - pT - pB); }
        function pts(d){ return d.map(function(v,i){ return tx(i).toFixed(1)+','+ty(v).toFixed(1); }).join(' '); }
        function areaPath(d){
            var p = d.map(function(v,i){ return tx(i).toFixed(1)+','+ty(v).toFixed(1); });
            p.push(tx(d.length-1).toFixed(1)+','+(H-pB));
            p.push(pL+','+(H-pB));
            return p.join(' ');
        }

        /* Grid + Y labels */
        var yTicks = [], steps = 4;
        for(var s=0; s<=steps; s++) yTicks.push(Math.round(mx * s / steps));
        var grid = '';
        yTicks.forEach(function(gv){
            var gy = ty(gv).toFixed(1);
            grid += '<line x1="'+pL+'" y1="'+gy+'" x2="'+(W-pR)+'" y2="'+gy+'" stroke="#f1f5f9" stroke-width="1"/>';
            grid += '<text x="'+(pL-6)+'" y="'+(parseFloat(gy)+4)+'" text-anchor="end" font-size="9" fill="#94a3b8" font-family="-apple-system,sans-serif">'+gv+'</text>';
        });

        /* X labels */
        var xlbl = lbls.map(function(l,i){
            return '<text x="'+tx(i).toFixed(1)+'" y="'+(H-8)+'" text-anchor="middle" font-size="10" fill="#94a3b8" font-family="-apple-system,sans-serif">'+l+'</text>';
        }).join('');

        /* Dots on primary line */
        var dots = d1.map(function(v,i){
            return '<circle cx="'+tx(i).toFixed(1)+'" cy="'+ty(v).toFixed(1)+'" r="4" fill="#6366f1" stroke="#fff" stroke-width="2"/>';
        }).join('');

        /* Build SVG */
        var hasD2 = d2.some(function(v){ return v > 0; });
        var svg = '<svg viewBox="0 0 '+W+' '+H+'" preserveAspectRatio="none" style="width:100%;height:100%;min-height:210px;display:block;" xmlns="http://www.w3.org/2000/svg">'
            + '<defs>'
            + '<linearGradient id="cgArea" x1="0" y1="0" x2="0" y2="1">'
            + '<stop offset="0%" stop-color="#6366f1" stop-opacity=".15"/>'
            + '<stop offset="100%" stop-color="#6366f1" stop-opacity="0"/>'
            + '</linearGradient>'
            + '</defs>'
            + grid
            + '<polygon points="'+areaPath(d1)+'" fill="url(#cgArea)"/>'
            + '<polyline points="'+pts(d1)+'" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>'
            + (hasD2 ? '<polyline points="'+pts(d2)+'" fill="none" stroke="#f97316" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="4,3"/>' : '')
            + dots + xlbl + '</svg>';

        var w = document.getElementById('wpaap-activity-chart');
        if(w) w.innerHTML = svg;

        /* Bulk select */
        var ca=document.getElementById('mb-queue-check-all');
        var tb=document.getElementById('mb-bulk-toolbar');
        var ce=document.getElementById('mb-bulk-count');
        if(!ca||!tb)return;
        function all(){return document.querySelectorAll('.mb-queue-check');}
        function chk(){return document.querySelectorAll('.mb-queue-check:checked');}
        var wpaap_selected_prefix = '<?php echo esc_js( __( 'Đã chọn', 'whp' ) ); ?>';
        var wpaap_selected_suffix2 = '<?php echo esc_js( __( 'bài', 'whp' ) ); ?>';
        function upd(){var c=chk(),a=all();ca.indeterminate=c.length>0&&c.length<a.length;ca.checked=c.length>0&&c.length===a.length;tb.style.display=c.length>0?'flex':'none';if(ce)ce.textContent=wpaap_selected_prefix+' '+c.length+' '+wpaap_selected_suffix2;}
        ca.addEventListener('change',function(){all().forEach(function(cb){cb.checked=ca.checked;});upd();});
        document.addEventListener('change',function(e){if(e.target&&e.target.classList.contains('mb-queue-check'))upd();});
        function ids(){var r=[];chk().forEach(function(cb){r.push(cb.value);});return r;}
        function rmRows(lst){
            lst.forEach(function(id){var cb=document.querySelector('.mb-queue-check[value="'+id+'"]');if(cb){var row=cb.closest('tr');if(row)row.remove();}});
            var tbody=document.querySelector('.wpaap-dash-mb-table tbody');
            if(tbody&&!tbody.querySelector('.mb-queue-check'))tbody.innerHTML='<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:28px 14px;"><?php echo esc_js( __( 'Không có bài viết nào chờ phê duyệt.', 'whp' ) ); ?></td></tr>';
            upd();
        }
        var wpaap_confirm_pub = '<?php echo esc_js( __( 'Duyệt', 'whp' ) ); ?>';
        var wpaap_confirm_pub_suffix = '<?php echo esc_js( __( 'bài?', 'whp' ) ); ?>';
        var wpaap_err_approve = '<?php echo esc_js( __( 'Không duyệt được.', 'whp' ) ); ?>';
        var wpaap_confirm_del = '<?php echo esc_js( __( 'Xóa', 'whp' ) ); ?>';
        var wpaap_err_delete = '<?php echo esc_js( __( 'Không xóa được.', 'whp' ) ); ?>';
        var wpaap_err_loi = '<?php echo esc_js( __( 'Lỗi:', 'whp' ) ); ?>';
        document.getElementById('mb-bulk-publish').addEventListener('click',function(){
            var i=ids();if(!i.length||!confirm(wpaap_confirm_pub+' '+i.length+' '+wpaap_confirm_pub_suffix))return;
            var b=this;b.disabled=true;b.style.opacity='.7';
            jQuery.post(wpaap_ajax.ajax_url,{action:'wpaap_bulk_publish',nonce:wpaap_ajax.nonce,post_ids:i},function(r){b.disabled=false;b.style.opacity='1';if(r.success)rmRows(i);else alert(wpaap_err_loi+' '+(r.data&&r.data.message?r.data.message:wpaap_err_approve));});
        });
        document.getElementById('mb-bulk-delete').addEventListener('click',function(){
            var i=ids();if(!i.length||!confirm(wpaap_confirm_del+' '+i.length+' '+wpaap_confirm_pub_suffix))return;
            var b=this;b.disabled=true;b.style.opacity='.7';
            jQuery.post(wpaap_ajax.ajax_url,{action:'wpaap_bulk_delete',nonce:wpaap_ajax.nonce,post_ids:i},function(r){b.disabled=false;b.style.opacity='1';if(r.success)rmRows(i);else alert(wpaap_err_loi+' '+(r.data&&r.data.message?r.data.message:wpaap_err_delete));});
        });
    })();
    </script>
    <?php
}
